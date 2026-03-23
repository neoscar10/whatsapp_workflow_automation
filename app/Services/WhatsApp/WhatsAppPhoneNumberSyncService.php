<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Support\Facades\Log;

class WhatsAppPhoneNumberSyncService
{
    public function __construct(
        protected WhatsAppGraphClient $graphClient
    ) {}

    /**
     * Sync phone numbers from Meta for a given account.
     */
    public function syncForAccount(WhatsAppAccount $account): array
    {
        Log::info("Starting WhatsApp Phone Number Sync for Company {$account->company_id}", [
            'waba_id' => $account->waba_id
        ]);

        $response = $this->graphClient->getPhoneNumbers($account->waba_id, $account->access_token);

        if (!$response['success']) {
            $account->update([
                'last_sync_error' => $response['error'],
                'last_synced_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => $response['error']
            ];
        }

        $metaNumbers = $response['data'];
        $results = $this->matchAndProcessNumbers($account, $metaNumbers);

        $account->update([
            'last_sync_error' => null,
            'last_synced_at' => now(),
            'connection_status' => 'connected',
        ]);

        return [
            'success' => true,
            'message' => "Successfully synced " . count($metaNumbers) . " phone numbers from Meta.",
            'stats' => $results
        ];
    }

    /**
     * Match Meta results against local database and update/create.
     */
    protected function matchAndProcessNumbers(WhatsAppAccount $account, array $metaNumbers): array
    {
        $updatedCount = 0;
        $createdCount = 0;
        $matchedIds = [];

        foreach ($metaNumbers as $metaData) {
            $phoneNumberId = $metaData['id'];
            
            // 1. Try to find by phone_number_id
            $localNumber = WhatsAppPhoneNumber::where('whatsapp_account_id', $account->id)
                ->where('phone_number_id', $phoneNumberId)
                ->first();

            // 2. Fallback: match by display phone number if id is not yet stored
            if (!$localNumber && !empty($metaData['display_phone_number'])) {
                $cleanNumber = preg_replace('/[^0-9]/', '', $metaData['display_phone_number']);
                $localNumber = WhatsAppPhoneNumber::where('whatsapp_account_id', $account->id)
                    ->where(function ($q) use ($cleanNumber) {
                        $q->where('phone_number', $cleanNumber)
                          ->orWhere('phone_number', '+' . $cleanNumber);
                    })
                    ->first();
            }

            if ($localNumber) {
                $this->updateLocalNumber($localNumber, $metaData);
                $updatedCount++;
                $matchedIds[] = $localNumber->id;
            } else {
                // Optionally auto-create if it doesn't exist locally at all
                $newNumber = $this->createNewNumber($account, $metaData);
                $createdCount++;
                $matchedIds[] = $newNumber->id;
            }
        }

        // Mark local numbers that are NOT in Meta as disconnected/defunct?
        // For now, just log them or mark with error if they were previously active
        $unmatched = WhatsAppPhoneNumber::where('whatsapp_account_id', $account->id)
            ->whereNotIn('id', $matchedIds)
            ->get();

        foreach ($unmatched as $number) {
            $number->update([
                'last_sync_error' => 'Phone number not found in Meta response for this WABA ID.',
                'status' => 'inactive'
            ]);
        }

        return [
            'updated' => $updatedCount,
            'created' => $createdCount,
            'unmatched' => $unmatched->count(),
        ];
    }

    protected function updateLocalNumber(WhatsAppPhoneNumber $number, array $metaData): void
    {
        $updateData = [
            'phone_number_id' => $metaData['id'], // Ensure ID is correct
            'display_name' => $metaData['verified_name'] ?? $number->display_name,
            'verified_name' => $metaData['verified_name'] ?? null,
            'phone_number' => $metaData['display_phone_number'] ?? $number->phone_number,
            'quality_rating' => $metaData['quality_rating'] ?? null,
            'code_verification_status' => $metaData['code_verification_status'] ?? null,
            'status' => strtolower($metaData['status'] ?? 'active'),
            'synced_at' => now(),
            'last_sync_error' => null,
        ];
        
        Log::info("Updating Phone Number {$number->id}", $updateData);
        $number->update($updateData);
    }

    protected function createNewNumber(WhatsAppAccount $account, array $metaData): WhatsAppPhoneNumber
    {
        $createData = [
            'company_id' => $account->company_id,
            'whatsapp_account_id' => $account->id,
            'display_name' => $metaData['verified_name'] ?? $metaData['display_phone_number'],
            'verified_name' => $metaData['verified_name'] ?? null,
            'phone_number_id' => $metaData['id'],
            'phone_number' => $metaData['display_phone_number'] ?? null,
            'quality_rating' => $metaData['quality_rating'] ?? null,
            'code_verification_status' => $metaData['code_verification_status'] ?? null,
            'status' => strtolower($metaData['status'] ?? 'active'),
            'synced_at' => now(),
        ];

        Log::info("Creating New Phone Number", $createData);
        return WhatsAppPhoneNumber::create($createData);
    }
}
