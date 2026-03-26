<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatConversationResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookEventService
{
    public function __construct(
        protected ChatConversationResolverService $resolverService
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

            foreach ($entries as $entry) {
                // Meta sends the waba_id at the entry level sometimes, and id
                $wabaId = $entry['id'] ?? null;
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    $field = $change['field'] ?? null;
                    $value = $change['value'] ?? [];

                    // Identify account
                    $account = $this->identifyAccountFromPayload($wabaId, $value);
                    
                    if (!$account) {
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

        return $query->first();
    }

    protected function processMessagesEvent(WhatsAppAccount $account, array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        if (!$phoneNumberId) {
            Log::error("WhatsApp Webhook: Missing phone_number_id in metadata", ['value' => $value]);
            return;
        }

        $localNumber = WhatsAppPhoneNumber::where('phone_number_id', $phoneNumberId)->first();
        if (!$localNumber) {
            Log::error("WhatsApp Webhook: Local number not found", [
                'phone_number_id' => $phoneNumberId,
                'account_id' => $account->id
            ]);
            return;
        }

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

                $this->resolverService->resolveAndProcessInboundMessage($localNumber, $message, $contact ?? []);
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

            // Update conversation last message timestamp if this is the newest
            $conversation = $message->conversation;
            if ($conversation && ($conversation->last_message_at ?? now()->subYear())->lte($message->created_at)) {
                $conversation->update(['last_message_at' => $message->created_at]);
            }

            // Broadcast events to update UI
            broadcast(new \App\Events\Chat\ChatMessageReceived($message));
            broadcast(new \App\Events\Chat\ChatConversationUpdated($conversation));
        }
    }

    protected function processTemplateStatusEvent(WhatsAppAccount $account, array $value): void
    {
        Log::info('Received WhatsApp template status update', ['account_id' => $account->id]);
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
