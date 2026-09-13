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

        if (!app(\App\Services\Payment\BillingService::class)->canAffordActivity($conversation->company, 'text')) {
            throw new \App\Exceptions\InsufficientWalletBalanceException('Insufficient wallet balance to send this text message.');
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

        if (!app(\App\Services\Payment\BillingService::class)->canAffordActivity($conversation->company, 'text')) {
            throw new \App\Exceptions\InsufficientWalletBalanceException('Insufficient wallet balance to send this media message.');
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
            broadcast(new ChatConversationUpdated($conversation));
        }
    }

    /**
     * Inject an external message directly into local conversation database without sending via Meta WhatsApp API.
     */
    public function injectExternalMessage(User $user, array $data): ConversationMessage
    {
        $companyId = $user->company_id;
        $rawPhone = $data['phone_number'];
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
        $formattedPhone = '+' . $cleanPhone;

        // 1. Resolve or create Contact for Company
        $contact = \App\Models\Contact\Contact::where('company_id', $companyId)
            ->where(function ($q) use ($rawPhone, $cleanPhone, $last10, $formattedPhone) {
                $q->where('phone', $rawPhone)
                  ->orWhere('phone', $formattedPhone)
                  ->orWhere('phone', $cleanPhone)
                  ->orWhere('phone', 'like', '%' . $last10);
            })
            ->first();

        if (!$contact) {
            $contact = \App\Models\Contact\Contact::create([
                'company_id' => $companyId,
                'name' => $data['contact_name'] ?? $formattedPhone,
                'phone' => $formattedPhone,
                'status' => 'active',
            ]);
        }

        // 2. Resolve target WhatsApp Phone Number for Company
        $channelId = $data['whatsapp_phone_number_id'] ?? null;
        if ($channelId) {
            $channel = \App\Models\WhatsApp\WhatsAppPhoneNumber::where('company_id', $companyId)
                ->where('id', $channelId)
                ->first();
        } else {
            $channel = app(\App\Services\Chat\ChatChannelAvailabilityService::class)->getDefaultWhatsAppNumberForUser($user);
        }

        // 3. Resolve or create Conversation
        $conversation = \App\Models\Chat\Conversation::where('company_id', $companyId)
            ->where(function ($q) use ($rawPhone, $cleanPhone, $last10, $contact) {
                $q->where('contact_phone', $rawPhone)
                  ->orWhere('contact_phone', '+' . $cleanPhone)
                  ->orWhere('contact_phone', $cleanPhone)
                  ->orWhere('contact_phone', 'like', '%' . $last10)
                  ->orWhere('contact_id', $contact->id);
            })
            ->orderBy('id', 'asc')
            ->first();

        if (!$conversation) {
            $conversation = \App\Models\Chat\Conversation::create([
                'company_id' => $companyId,
                'whatsapp_phone_number_id' => $channel?->id,
                'contact_id' => $contact->id,
                'contact_phone' => $formattedPhone,
                'contact_name' => $contact->name,
                'assignment_status' => 'unassigned',
            ]);
        } else {
            $updateData = ['contact_id' => $contact->id];
            if ($channel && !$conversation->whatsapp_phone_number_id) {
                $updateData['whatsapp_phone_number_id'] = $channel->id;
            }
            $conversation->update($updateData);
        }

        // 4. Determine message attributes
        $direction = $data['direction'] ?? 'outbound';
        $messageType = $data['message_type'] ?? $data['type'] ?? 'text';
        $body = $data['body'] ?? $data['message'] ?? null;
        $externalId = $data['external_message_id'] ?? $data['external_id'] ?? ('ext_' . \Illuminate\Support\Str::uuid());
        
        $status = $data['status'] ?? ($direction === 'inbound' ? 'received' : 'delivered');

        // Build meta payload
        $metaPayload = $data['meta_payload'] ?? $data['metadata'] ?? [];
        if ($messageType === 'template') {
            if (!empty($data['template_name'])) {
                $metaPayload['template_name'] = $data['template_name'];
            }
            if (!empty($data['template_id'])) {
                $metaPayload['template_id'] = $data['template_id'];
            }
            if (!empty($data['components']) || !empty($data['parameters'])) {
                $metaPayload['components'] = $data['components'] ?? $data['parameters'];
            }
        }

        // Build media meta if media URL passed
        $mediaMeta = [];
        $mediaUrl = $data['media_url'] ?? null;
        if ($mediaUrl) {
            $mediaMeta = [
                'filename' => $data['media_filename'] ?? basename(parse_url($mediaUrl, PHP_URL_PATH) ?? 'file'),
                'mime_type' => $data['mime_type'] ?? 'application/octet-stream',
                'size' => $data['file_size'] ?? 0,
                'external_media_url' => $mediaUrl,
            ];
        }

        // 5. Create local ConversationMessage (DO NOT call Meta WhatsApp API)
        $message = $conversation->messages()->create([
            'external_message_id' => $externalId,
            'direction' => $direction,
            'message_type' => $messageType,
            'body' => $body,
            'status' => $status,
            'sent_by_user_id' => $user->id,
            'media_url' => $mediaUrl,
            'media_meta' => $mediaMeta,
            'meta_payload' => $metaPayload,
            'sent_at' => now(),
            'delivered_at' => $direction === 'outbound' ? now() : null,
            'read_at' => null,
        ]);

        // 6. Update conversation summary
        $conversationUpdate = [
            'last_message_preview' => $message->generatePreviewText(),
            'last_message_at' => now(),
            'updated_at' => now(),
        ];

        if ($direction === 'inbound') {
            $conversationUpdate['last_customer_message_at'] = now();
            $conversationUpdate['unread_count'] = ($conversation->unread_count ?? 0) + 1;
        } else {
            $conversationUpdate['unread_count'] = 0;
        }

        $conversation->update($conversationUpdate);

        // 7. Broadcast real-time websocket events for live UI updates
        broadcast(new ChatMessageReceived($message));
        broadcast(new ChatConversationUpdated($conversation));

        return $message;
    }
}
