<?php

namespace App\Services\Chat;

use App\Models\Chat\ConversationMessage;
use App\Models\User;
use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatConversationUpdated;

class ChatMessageService
{
    public function __construct(
        protected ChatInboxService $inboxService,
        protected \App\Services\WhatsApp\WhatsAppOutboundMessageService $outboundService
    ) {}

    /**
     * Send a text message and persist local record.
     */
    public function sendTextMessage(User $user, int $conversationId, string $message): ?ConversationMessage
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return null;
        }

        // Persist local message
        $msg = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $message,
            'status' => 'pending',
            'sent_by_user_id' => $user->id,
            'sent_at' => now(),
        ]);

        // Update conversation summary
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => substr($message, 0, 50),
            'unread_count' => 0, // usually we clear unread if we reply
        ]);

        // Dispatch the WhatsApp outbound sending logic
        $this->outboundService->sendConversationMessage($msg);

        // Broadcast events
        broadcast(new ChatMessageReceived($msg));
        broadcast(new ChatConversationUpdated($conversation));

        return $msg;
    }

    /**
     * Send a media message (upload then send).
     */
    public function sendMediaMessage(User $user, int $conversationId, string $stagedPath, array $metadata, ?string $caption = null): ?ConversationMessage
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return null;
        }

        // 1. Identify media type
        $mime = $metadata['mime'] ?? 'application/octet-stream';
        $type = 'document';
        if (str_starts_with($mime, 'image/')) $type = 'image';
        elseif (str_starts_with($mime, 'video/')) $type = 'video';
        elseif (str_starts_with($mime, 'audio/')) $type = 'audio';

        // 2. Move from staging to permanent storage on the public disk
        $filename = time() . '_' . $metadata['name'];
        $permanentPath = 'chat_media/' . $filename;
        
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        // Ensure the permanent directory exists
        if (!$disk->exists('chat_media')) {
            $disk->makeDirectory('chat_media');
        }

        if (!$disk->move($stagedPath, $permanentPath)) {
            \Illuminate\Support\Facades\Log::error("Failed to move media from staged path", [
                'staged' => $stagedPath,
                'permanent' => $permanentPath
            ]);
            throw new \Exception("Could not persist media file to permanent storage.");
        }

        $publicUrl = $disk->url($permanentPath);

        // 3. Persist local message as pending
        $msg = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => $type,
            'body' => $caption,
            'status' => 'pending',
            'media_url' => $permanentPath, // Store the clean relative path
            'sent_by_user_id' => $user->id,
            'sent_at' => now(),
            'media_meta' => [
                'filename' => $metadata['name'],
                'mime_type' => $mime,
                'size' => $metadata['size'],
                'local_path' => $permanentPath,
            ]
        ]);

        \Illuminate\Support\Facades\Log::info("MEDIA_MESSAGE_PERSISTED", [
            'conversation_id' => $conversation->id,
            'message_id' => $msg->id,
            'type' => $type,
            'media_url' => $msg->media_url,
            'resolved_url' => $msg->resolved_media_url,
            'disk' => 'public',
            'mime' => $mime,
            'name' => $metadata['name']
        ]);

        // Update conversation summary
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => ucfirst($type) . ($caption ? ': ' . substr($caption, 0, 30) : ''),
            'unread_count' => 0,
        ]);

        // Broadcast early "pending" message
        broadcast(new ChatMessageReceived($msg));
        broadcast(new ChatConversationUpdated($conversation));

        // 3. Perform Upload & Send in background (synchronous here for reliability in this flow)
        try {
            $account = $conversation->whatsappPhoneNumber->account;
            $mediaService = app(\App\Services\WhatsApp\MetaMediaUploadService::class);
            
            $mediaId = $mediaService->uploadMessageMedia(
                $conversation->whatsappPhoneNumber->phone_number_id,
                $account->access_token,
                $permanentPath
            );

            if ($mediaId) {
                $msg->update([
                    'media_meta' => array_merge($msg->media_meta, ['media_id' => $mediaId])
                ]);

                // 4. Dispatch the WhatsApp outbound sending logic
                $this->outboundService->sendConversationMessage($msg);
            } else {
                throw new \Exception("Media upload failed to return an ID.");
            }

        } catch (\Exception $e) {
            \Log::error("Media Send Flow Failed", ['message' => $e->getMessage()]);
            $msg->update([
                'status' => 'failed',
                'meta_payload' => array_merge($msg->meta_payload ?? [], ['error' => $e->getMessage()])
            ]);
            broadcast(new ChatMessageReceived($msg));
        }

        return $msg;
    }

    /**
     * Mark conversation as read.
     */
    public function markConversationRead(User $user, int $conversationId): void
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if ($conversation) {
            $conversation->update(['unread_count' => 0]);
        }
    }
}
