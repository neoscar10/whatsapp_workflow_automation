<?php

namespace App\Services\Chat;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatConversationUpdated;
use App\Events\Chat\InboundMessageReceived;
use App\Services\Contact\ContactSyncService;
use Illuminate\Support\Facades\Log;

class ChatConversationResolverService
{
    public function __construct(
        protected ContactSyncService $contactSyncService
    ) {}

    /**
     * Resolve an inbound WhatsApp message to a local conversation and store the message.
     *
     * @param WhatsAppPhoneNumber $localNumber
     * @param array $messageData (The 'message' object from the 'value' change)
     * @param array $contactData (The 'contact' object if available)
     * @return void
     */
    public function resolveAndProcessInboundMessage(WhatsAppPhoneNumber $localNumber, array $messageData, array $contactData = []): ?ConversationMessage
    {
        $fromPhone = $messageData['from']; // Customer's phone number
        $messageId = $messageData['id'];
        $cleanPhone = preg_replace('/[^0-9]/', '', $fromPhone);
        $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        // 1. Find existing conversation by matching exact phone or last 10 digits
        $conversation = Conversation::where('company_id', $localNumber->company_id)
            ->where(function ($q) use ($fromPhone, $cleanPhone, $last10) {
                $q->where('contact_phone', $fromPhone)
                  ->orWhere('contact_phone', '+' . $cleanPhone)
                  ->orWhere('contact_phone', $cleanPhone)
                  ->orWhere('contact_phone', 'like', '%' . $last10);
            })
            ->orderBy('id', 'asc') // Pick earliest primary conversation (e.g. ID 14)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'company_id' => $localNumber->company_id,
                'whatsapp_phone_number_id' => $localNumber->id,
                'contact_phone' => '+' . $cleanPhone,
                'contact_name' => $contactData['profile']['name'] ?? ('+' . $cleanPhone),
                'assignment_status' => 'unassigned',
            ]);
        } else {
            // Update whatsapp_phone_number_id and ensure phone format is standardized
            $conversation->update([
                'whatsapp_phone_number_id' => $localNumber->id,
                'contact_phone' => '+' . $cleanPhone,
            ]);

            // Consolidate any duplicate conversations for this exact same phone number
            $duplicateConvIds = Conversation::where('company_id', $localNumber->company_id)
                ->where('id', '!=', $conversation->id)
                ->where(function ($q) use ($fromPhone, $cleanPhone, $last10) {
                    $q->where('contact_phone', $fromPhone)
                      ->orWhere('contact_phone', '+' . $cleanPhone)
                      ->orWhere('contact_phone', $cleanPhone)
                      ->orWhere('contact_phone', 'like', '%' . $last10);
                })
                ->pluck('id');

            if ($duplicateConvIds->isNotEmpty()) {
                ConversationMessage::whereIn('conversation_id', $duplicateConvIds)
                    ->update(['conversation_id' => $conversation->id]);

                Conversation::whereIn('id', $duplicateConvIds)->delete();
                Log::info("CONVERSATION_CONSOLIDATION: Merged duplicate conversations [" . implode(',', $duplicateConvIds->toArray()) . "] into conversation {$conversation->id}");
            }
        }

        // 2. Prepare message body based on type
        $type = $messageData['type'] ?? 'text';
        $body = '';
        $mediaUrl = null;
        $mediaMeta = [];

        if ($type === 'text') {
            $body = $messageData['text']['body'] ?? '';
        } elseif ($type === 'button') {
            $body = $messageData['button']['text'] ?? '[Button Clicked]';
        } elseif ($type === 'interactive') {
            $interactive = $messageData['interactive'];
            if ($interactive['type'] === 'button_reply') {
                $body = $interactive['button_reply']['title'] ?? '[Button Reply]';
            } elseif ($interactive['type'] === 'list_reply') {
                $body = $interactive['list_reply']['title'] ?? '[List Reply]';
            }
        } elseif (in_array($type, ['image', 'video', 'audio', 'document'])) {
            $mediaData = $messageData[$type] ?? [];
            $mediaId = $mediaData['id'] ?? null;
            $mimeType = $mediaData['mime_type'] ?? '';
            $caption = $mediaData['caption'] ?? ($mediaData['filename'] ?? null);

            $body = $caption ?: ucfirst($type);
            $mediaMeta = [
                'media_id' => $mediaId,
                'mime_type' => $mimeType,
                'filename' => $mediaData['filename'] ?? (time() . '.' . (explode('/', $mimeType)[1] ?? 'bin')),
            ];

            // Download and save to disk so it can be served directly (like outbound media)
            if ($mediaId) {
                $resolver = app(\App\Services\WhatsApp\Simulation\SimulatedWhatsAppMediaResolver::class);
                $isSimulated = $resolver->isSimulatedMediaId($mediaId);

                if ($isSimulated) {
                    try {
                        $simMedia = $resolver->getSimulatedMedia($mediaId);
                        if ($simMedia) {
                            $fileContents = $resolver->getMediaContents($mediaId);
                            if ($fileContents) {
                                $ext = $simMedia->extension;
                                $filename = time() . '_' . $mediaId . '.' . $ext;
                                $localPath = 'chat_media/' . $filename;

                                $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                if (!$disk->exists('chat_media')) {
                                    $disk->makeDirectory('chat_media');
                                }
                                if ($disk->put($localPath, $fileContents)) {
                                    $mediaUrl = $localPath;
                                    $mediaMeta['local_path'] = $localPath;
                                    $mediaMeta['size'] = $simMedia->file_size;
                                    Log::info("INBOUND_MEDIA (SIMULATED): Saved to disk", ['path' => $localPath, 'type' => $type]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("INBOUND_MEDIA (SIMULATED): Exception loading: " . $e->getMessage());
                    }
                } else {
                    $accessToken = $localNumber->account->access_token ?? null;
                    if ($accessToken) {
                        try {
                            $graphClient = app(\App\Services\WhatsApp\WhatsAppGraphClient::class);

                            // Step 1: Get the direct download URL from Meta
                            $mediaInfo = $graphClient->getMedia($mediaId, $accessToken);

                            if ($mediaInfo['success'] && !empty($mediaInfo['url'])) {
                                // Step 2: Download binary content
                                $fileContents = $graphClient->downloadMediaFile($mediaInfo['url'], $accessToken);

                                if ($fileContents) {
                                    // Step 3: Determine file extension from MIME type
                                    $detectedMime = $mediaInfo['mime_type'] ?? $mimeType;
                                    $ext = explode('/', $detectedMime)[1] ?? 'bin';
                                    if (str_contains($ext, ';')) {
                                        $ext = trim(explode(';', $ext)[0]);
                                    }
                                    $filename = time() . '_' . $mediaId . '.' . $ext;
                                    $localPath = 'chat_media/' . $filename;

                                    // Step 4: Save to public disk (same as outbound media)
                                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                    if (!$disk->exists('chat_media')) {
                                        $disk->makeDirectory('chat_media');
                                    }
                                    if ($disk->put($localPath, $fileContents)) {
                                        $mediaUrl = $localPath;
                                        $mediaMeta['local_path'] = $localPath;
                                        $mediaMeta['size'] = $mediaInfo['file_size'] ?? null;
                                        Log::info("INBOUND_MEDIA: Saved to disk", ['path' => $localPath, 'type' => $type]);
                                    } else {
                                        Log::error("INBOUND_MEDIA: Failed to write to disk", ['path' => $localPath]);
                                    }
                                } else {
                                    Log::error("INBOUND_MEDIA: Empty file download", ['media_id' => $mediaId]);
                                }
                            } else {
                                Log::error("INBOUND_MEDIA: getMedia failed", [
                                    'media_id' => $mediaId,
                                    'error' => $mediaInfo['error'] ?? 'unknown',
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error("INBOUND_MEDIA: Exception downloading media: " . $e->getMessage(), [
                                'media_id' => $mediaId,
                                'type' => $type,
                            ]);
                        }
                    } else {
                        Log::error("INBOUND_MEDIA: No access token for phone number", ['id' => $localNumber->id]);
                    }
                }
            }

        } else {
            $body = "[Unsupported Message Type: {$type}]";
        }

        // 3. Prevent duplicate message processing if already existing
        if (ConversationMessage::where('external_message_id', $messageId)->exists()) {
            return null;
        }

        // 4. Create the message
        $msg = $conversation->messages()->create([
            'external_message_id' => $messageId,
            'direction' => 'inbound',
            'message_type' => in_array($type, ['text', 'image', 'video', 'audio', 'document']) ? $type : 'other',
            'body' => $body,
            'status' => 'received',
            'media_url' => $mediaUrl,
            'media_meta' => $mediaMeta,
            'meta_payload' => $messageData,
            'sent_at' => now(), // Meta timestamp is in seconds, for now we use 'now'
        ]);

        // 5. Update conversation summary
        $conversation->update([
            'last_customer_message_at' => now(), // WhatsApp 24h window trigger
            // Unread count tracking could go here
        ]);

        // Sync Contact logic
        try {
            $this->contactSyncService->syncConversation($conversation);
        } catch (\Throwable $e) {
            Log::warning('Contact sync failed during message resolution', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::debug('Realtime: Inbound message saved', [
            'conversation_id' => $conversation->id,
            'message_id' => $msg->id,
            'company_id' => $conversation->company_id,
        ]);

        // Broadcast events
        broadcast(new ChatMessageReceived($msg));
        broadcast(new ChatConversationUpdated($conversation));
        
        Log::debug('Realtime: Dispatching InboundMessageReceived event', [
            'channel' => "company.{$conversation->company_id}.chats",
            'event' => 'chat.inbound.received'
        ]);

        Log::info('TRACE A: About to dispatch InboundMessageReceived', [
            'company_id' => $conversation->company_id,
        ]);

        broadcast(new InboundMessageReceived(
            companyId: $conversation->company_id,
            conversationId: $conversation->id,
            messageId: $msg->id,
            preview: $body,
            createdAt: $msg->created_at->toDateTimeString(),
            phoneNumber: $conversation->contact_phone,
            senderName: $conversation->contact_name ?? '',
            direction: 'inbound'
        ));

        Log::debug('Realtime: Broadcast call finished');

        return $msg;
    }
}
