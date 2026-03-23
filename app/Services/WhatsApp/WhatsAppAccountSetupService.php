<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Support\Facades\Auth;

class WhatsAppAccountSetupService
{
    public function __construct(
        protected WhatsAppPhoneNumberSyncService $syncService
    ) {}

    public function getSetupDataForUser(User $user): array
    {
        $account = WhatsAppAccount::where('company_id', $user->company_id)->first();

        return [
            'is_connected' => $account ? $account->connection_status === 'connected' : false,
            'connection_status' => $account->connection_status ?? 'not_connected',
            'has_saved_token' => $account && !empty($account->access_token),
            'waba_id' => $account->waba_id ?? '',
            'business_id' => $account->business_id ?? '',
            'webhook_status' => $account->webhook_status ?? 'not_configured',
            'last_sync_error' => $account->last_sync_error ?? null,
            'last_synced_at' => $account->last_synced_at ?? null,
        ];
    }

    public function saveSetupForUser(User $user, array $data): array
    {
        $company = $user->company;
        
        $updateData = [
            'waba_id' => $data['waba_id'],
            'business_id' => $data['business_id'],
            // Temporarily set to pending-sync until service verifies
            'connection_status' => 'pending-sync',
        ];

        if (!empty($data['access_token'])) {
            $updateData['access_token'] = trim($data['access_token']);
        }

        $account = WhatsAppAccount::updateOrCreate(
            ['company_id' => $company->id],
            $updateData
        );

        // Perform immediate sync
        $syncResult = $this->syncService->syncForAccount($account);

        return $this->getSetupDataForUser($user);
    }

    public function resetDataForUser(User $user): array
    {
        return $this->getSetupDataForUser($user);
    }
}
