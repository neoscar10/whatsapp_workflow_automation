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
        if ($user->company) {
            return $this->getSetupDataForCompany($user->company);
        }

        return $this->getSetupDataForCompany(new \App\Models\Company(['id' => 0]));
    }

    public function getSetupDataForCompany(\App\Models\Company $company): array
    {
        $account = WhatsAppAccount::where('company_id', $company->id)->first();

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
        if ($user->company) {
            return $this->saveSetupForCompany($user->company, $data);
        }
        throw new \Exception("User has no associated company.");
    }

    public function saveSetupForCompany(\App\Models\Company $company, array $data): array
    {
        $existingAccount = WhatsAppAccount::where('company_id', $company->id)->first();

        $updateData = [];

        if (array_key_exists('waba_id', $data)) {
            $updateData['waba_id'] = $data['waba_id'];
        } elseif ($existingAccount) {
            $updateData['waba_id'] = $existingAccount->waba_id;
        }

        if (array_key_exists('business_id', $data)) {
            $updateData['business_id'] = $data['business_id'];
        } elseif ($existingAccount) {
            $updateData['business_id'] = $existingAccount->business_id;
        }

        if (!empty($data['access_token'])) {
            $updateData['access_token'] = trim($data['access_token']);
        }

        if (array_key_exists('webhook_callback_url', $data)) {
            $updateData['webhook_callback_url'] = $data['webhook_callback_url'];
            $updateData['webhook_status'] = 'configured';
        }

        if (empty($existingAccount) || (isset($data['waba_id']) && $data['waba_id'] !== $existingAccount->waba_id)) {
            $updateData['connection_status'] = 'pending-sync';
        }

        $account = WhatsAppAccount::updateOrCreate(
            ['company_id' => $company->id],
            $updateData
        );

        // Perform immediate sync if token & waba_id are set
        if (!empty($account->access_token) && !empty($account->waba_id)) {
            $this->syncService->syncForAccount($account);
        }

        return $this->getSetupDataForCompany($company);
    }

    public function resetDataForUser(User $user): array
    {
        return $this->getSetupDataForUser($user);
    }
}
