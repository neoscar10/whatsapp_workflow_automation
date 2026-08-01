<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatConversationResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\Webhooks\CompanyWebhookDispatcherService;

class WhatsAppWebhookEventService
{
    public function __construct(
        protected ChatConversationResolverService $resolverService,
        protected CompanyWebhookDispatcherService $webhookDispatcher
    ) {}
    /**
     * Process the incoming POST webhook payload from Meta.
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        try {
            // Log raw webhook if requested by admin/config or insert into DB for debugging
            $this->storeRawEvent($payload);

            // Entry structure according to Meta docs
            $entries = $payload['entry'] ?? [];

            Log::info('WEBHOOK_STAGE_2: Parsing entries in EventService', ['entries_count' => count($entries)]);

            foreach ($entries as $entry) {
                // Meta sends the waba_id at the entry level sometimes, and id
                $wabaId = $entry['id'] ?? null;
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    $field = $change['field'] ?? null;
                    $value = $change['value'] ?? [];

                    Log::info('WEBHOOK_STAGE_3: Processing change field', ['field' => $field, 'waba_id' => $wabaId, 'phone_number_id' => $value['metadata']['phone_number_id'] ?? null]);

                    // Identify account
                    $account = $this->identifyAccountFromPayload($wabaId, $value);
                    
                    if (!$account) {
                        Log::warning('WEBHOOK_STAGE_3_FAIL: Could not identify local WhatsAppAccount from payload', [
                            'waba_id' => $wabaId,
                            'phone_number_id' => $value['metadata']['phone_number_id'] ?? null,
                        ]);
                        return;
                    }

                    // Dispatch specific processing based on field
                    if ($field === 'messages') {
                        $this->processMessagesEvent($account, $value);
                    } elseif ($field === 'message_template_status_update') {
                        $this->processTemplateStatusEvent($account, $value);
                    } else {
                        $this->logUnhandledEvent($account, $field, $value);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error parsing payload: ' . $e->getMessage(), ['payload' => $payload]);
            // Re-throw or silently handle depending on queue strategy
        }
    }

    /**
     * Identify the local WhatsAppAccount based on WABA ID or Phone Number ID.
     */
    protected function identifyAccountFromPayload(?string $wabaId, array $value): ?WhatsAppAccount
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        $query = WhatsAppAccount::query();

        if ($phoneNumberId) {
            // Find account that natively owns this phone number ID (assuming relationships)
            // Or look up via waba_id
            $query->whereHas('phoneNumbers', function ($q) use ($phoneNumberId) {
                $q->where('phone_number_id', $phoneNumberId);
            });
        } elseif ($wabaId) {
            $query->where('waba_id', $wabaId);
        }

        $account = $query->orderBy('id', 'desc')->first();
        
        Log::info('WEBHOOK_ACCOUNT_LOOKUP', [
            'phone_number_id' => $phoneNumberId,
            'waba_id' => $wabaId,
            'found_account_id' => $account?->id,
        ]);

        return $account;
    }

    protected function processMessagesEvent(WhatsAppAccount $account, array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        if (!$phoneNumberId) {
            Log::error("WhatsApp Webhook: Missing phone_number_id in metadata", ['value' => $value]);
            return;
        }

        // 1. Try to find local number for this specific account first
        $localNumber = WhatsAppPhoneNumber::with('account')
            ->where('whatsapp_account_id', $account->id)
            ->where('phone_number_id', $phoneNumberId)
            ->first();

        // 2. Fallback: find by company_id and phone_number_id
        if (!$localNumber) {
            $localNumber = WhatsAppPhoneNumber::with('account')
                ->where('company_id', $account->company_id)
                ->where('phone_number_id', $phoneNumberId)
                ->first();
        }

        // 3. Last fallback: global search by phone_number_id
        if (!$localNumber) {
            $localNumber = WhatsAppPhoneNumber::with('account')
                ->where('phone_number_id', $phoneNumberId)
                ->first();
        }

        if (!$localNumber) {
            Log::error("WhatsApp Webhook: Local number not found in DB", [
                'phone_number_id' => $phoneNumberId,
                'account_id' => $account->id
            ]);
            return;
        }

        // Consolidation: If duplicate records exist for this phone_number_id in company, merge them into $localNumber
        try {
            $otherNumberIds = WhatsAppPhoneNumber::where('company_id', $account->company_id)
                ->where('phone_number_id', $phoneNumberId)
                ->where('id', '!=', $localNumber->id)
                ->pluck('id');

            if ($otherNumberIds->isNotEmpty()) {
                \App\Models\Chat\Conversation::whereIn('whatsapp_phone_number_id', $otherNumberIds)
                    ->update(['whatsapp_phone_number_id' => $localNumber->id]);

                WhatsAppPhoneNumber::whereIn('id', $otherNumberIds)->delete();
                
                Log::info("WEBHOOK_CONSOLIDATION: Merged duplicate phone numbers " . implode(',', $otherNumberIds->toArray()) . " into active number {$localNumber->id}");
            }
        } catch (\Exception $e) {
            Log::warning("WEBHOOK_CONSOLIDATION: Exception during consolidation", ['error' => $e->getMessage()]);
        }

        // Failsafe: Ensure local number's company_id is synchronized with its parent WhatsAppAccount's company_id
        if ($account && $localNumber->company_id !== $account->company_id) {
            try {
                Log::info("WEBHOOK_AUTO_REPAIR: Updating local number {$localNumber->id} company_id from {$localNumber->company_id} to {$account->company_id}");
                $localNumber->update(['company_id' => $account->company_id]);
                $localNumber->refresh();
            } catch (\Exception $e) {
                Log::warning("WEBHOOK_AUTO_REPAIR: Could not update company_id due to existing record", ['error' => $e->getMessage()]);
            }
        }

        Log::info('WEBHOOK_STAGE_4: Local number matched successfully', [
            'phone_number_id' => $phoneNumberId,
            'local_number_id' => $localNumber->id,
            'company_id' => $localNumber->company_id,
            'messages_count' => count($value['messages'] ?? []),
        ]);

        // Handle incoming messages
        if (!empty($value['messages'])) {
            $contacts = $value['contacts'] ?? [];
            
            foreach ($value['messages'] as $index => $message) {
                // Find contact profile if available (Meta sends it once in the payload)
                $contact = null;
                $from = $message['from'] ?? null;
                foreach ($contacts as $c) {
                    if (($c['wa_id'] ?? null) === $from) {
                        $contact = $c;
                        break;
                    }
                }

                $savedMessage = $this->resolverService->resolveAndProcessInboundMessage($localNumber, $message, $contact ?? []);

                if ($account->company && $savedMessage) {
                    // Dispatch mobile push notification job
                    \App\Jobs\Notifications\SendMobilePushNotificationJob::dispatch(
                        $account->company->id,
                        $savedMessage->conversation_id,
                        $savedMessage->id,
                        $savedMessage->conversation?->contact_name ?? $from ?? 'Contact',
                        $savedMessage->body ?? 'New media message'
                    );

                    $this->webhookDispatcher->dispatch($account->company, 'message.received', [
                        'message' => $message,
                        'contact' => $contact ?? [],
                        'phone_number_id' => $phoneNumberId,
                    ]);
                }
            }
        }

        if (!empty($value['statuses'])) {
            $this->processStatusUpdates($account, $value['statuses']);
        }
    }

    protected function processStatusUpdates(WhatsAppAccount $account, array $statuses): void
    {
        foreach ($statuses as $statusData) {
            $externalId = $statusData['id'] ?? null;
            $newStatus = $statusData['status'] ?? null;
            $timestamp = $statusData['timestamp'] ?? null;

            if (!$externalId || !$newStatus) continue;

            $message = \App\Models\Chat\ConversationMessage::where('external_message_id', $externalId)->first();
            
            if (!$message) continue;

            // Status Progression: pending -> sent -> delivered -> read
            $statusOrder = ['pending' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3];
            $currentStatusRank = $statusOrder[$message->status] ?? 0;
            $newStatusRank = $statusOrder[$newStatus] ?? ($newStatus === 'failed' ? 99 : 0);

            // Don't regress from read back to delivered, but allow 'failed' anytime
            if ($newStatusRank <= $currentStatusRank && $newStatus !== 'failed') {
                continue;
            }

            $updateData = ['status' => $newStatus];
            $dt = $timestamp ? \Illuminate\Support\Carbon::createFromTimestamp($timestamp) : now();

            if ($newStatus === 'sent') {
                $updateData['sent_at'] = $message->sent_at ?? $dt;
            } elseif ($newStatus === 'delivered') {
                $updateData['delivered_at'] = $message->delivered_at ?? $dt;
                $updateData['sent_at'] = $message->sent_at ?? $dt; // Ensure sent_at exists
            } elseif ($newStatus === 'read') {
                $updateData['read_at'] = $message->read_at ?? $dt;
                $updateData['delivered_at'] = $message->delivered_at ?? $dt;
                $updateData['sent_at'] = $message->sent_at ?? $dt;
            } elseif ($newStatus === 'failed') {
                $errors = $statusData['errors'] ?? [];
                $firstError = $errors[0] ?? [];
                $updateData['failed_at'] = $dt;
                $updateData['failure_code'] = $firstError['code'] ?? 'unknown';
                $updateData['failure_message'] = $firstError['message'] ?? 'Unknown WhatsApp error';
                $updateData['meta_payload'] = array_merge($message->meta_payload ?? [], ['errors' => $errors]);
            }

            $message->update($updateData);

            // Sync with CampaignRecipient if this message is part of a campaign
            $recipient = \App\Models\Campaign\CampaignRecipient::where('conversation_message_id', $message->id)->first();
            if ($recipient) {
                $recipientUpdate = ['status' => $newStatus];
                if ($newStatus === 'delivered') {
                    $recipientUpdate['delivered_at'] = $updateData['delivered_at'] ?? now();
                    $recipientUpdate['sent_at'] = $updateData['sent_at'] ?? now();
                } elseif ($newStatus === 'read') {
                    $recipientUpdate['read_at'] = $updateData['read_at'] ?? now();
                    $recipientUpdate['delivered_at'] = $updateData['delivered_at'] ?? now();
                    $recipientUpdate['sent_at'] = $updateData['sent_at'] ?? now();
                } elseif ($newStatus === 'failed') {
                    $recipientUpdate['failed_at'] = $updateData['failed_at'] ?? now();
                    $recipientUpdate['meta_error_code'] = $updateData['failure_code'] ?? 'unknown';
                    $recipientUpdate['meta_error_message'] = $updateData['failure_message'] ?? 'Unknown error';
                    $recipientUpdate['meta_error_payload'] = $updateData['meta_payload'] ?? [];
                }
                $recipient->update($recipientUpdate);
                
                if ($recipient->campaign) {
                    app(\App\Services\Campaign\CampaignService::class)->recalculateStats($recipient->campaign);
                }
            }

            // Update conversation last message timestamp if this is the newest
            $conversation = $message->conversation;
            if ($conversation && ($conversation->last_message_at ?? now()->subYear())->lte($message->created_at)) {
                $conversation->update(['last_message_at' => $message->created_at]);
            }

            // Broadcast events to update UI
            broadcast(new \App\Events\Chat\ChatMessageReceived($message));
            broadcast(new \App\Events\Chat\ChatConversationUpdated($conversation));

            if ($account->company) {
                $this->webhookDispatcher->dispatch($account->company, 'message.status_update', [
                    'message_id' => $message->id,
                    'external_message_id' => $externalId,
                    'status' => $newStatus,
                    'timestamp' => $timestamp,
                ]);
            }
        }
    }

    protected function processTemplateStatusEvent(WhatsAppAccount $account, array $value): void
    {
        Log::info('Received WhatsApp template status update', ['account_id' => $account->id]);

        if ($account->company) {
            $this->webhookDispatcher->dispatch($account->company, 'template.status_update', $value);
        }
    }

    protected function logUnhandledEvent(WhatsAppAccount $account, ?string $field, array $value): void
    {
        Log::info("WhatsApp Webhook: Ignoring unhandled field [{$field}]", ['account_id' => $account->id]);
    }

    /**
     * Store the raw webhook event into the database for audit/debugging logic.
     */
    protected function storeRawEvent(array $payload): void
    {
        // Insert directly using DB facade to avoid overhead if model doesn't exist yet, 
        // or create Model. Assuming standard DB structure matching migration.
        DB::table('whatsapp_webhook_events')->insert([
            'event_type' => $payload['object'] ?? 'unknown',
            'payload' => json_encode($payload),
            'processing_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
