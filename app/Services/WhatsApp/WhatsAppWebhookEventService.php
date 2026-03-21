<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookEventService
{
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
                        Log::warning('WhatsApp Webhook: Could not match incoming payload to a local account.', [
                            'waba_id' => $wabaId,
                            'phone_number_id' => $value['metadata']['phone_number_id'] ?? null,
                        ]);
                        continue;
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
        // Placeholders for future Phase
        if (isset($value['messages'])) {
            // Incoming message
            Log::info('Received incoming WhatsApp message', ['account_id' => $account->id]);
        }

        if (isset($value['statuses'])) {
            // Message status update (sent, delivered, read, failed)
            Log::info('Received WhatsApp message status update', ['account_id' => $account->id]);
        }
    }

    protected function processTemplateStatusEvent(WhatsAppAccount $account, array $value): void
    {
        Log::info('Received WhatsApp template status update', ['account_id' => $account->id]);
    }

    protected function logUnhandledEvent(WhatsAppAccount $account, ?string $field, array $value): void
    {
        Log::debug("WhatsApp Webhook: unhandled field [{$field}]", ['account_id' => $account->id]);
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
