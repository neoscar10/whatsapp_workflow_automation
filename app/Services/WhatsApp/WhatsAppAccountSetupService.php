<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Support\Facades\Auth;

class WhatsAppAccountSetupService
{
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
        ];
    }

    public function saveSetupForUser(User $user, array $data): array
    {
        $company = $user->company;
        
        $updateData = [
            'waba_id' => $data['waba_id'],
            'business_id' => $data['business_id'],
            'connection_status' => 'connected',
            'connected_at' => now(),
        ];

        if (!empty($data['access_token'])) {
            $updateData['access_token'] = trim($data['access_token']);
        }

        $account = WhatsAppAccount::updateOrCreate(
            ['company_id' => $company->id],
            $updateData
        );

        return $this->getSetupDataForUser($user);
    }

    public function resetDataForUser(User $user): array
    {
        return $this->getSetupDataForUser($user);
    }
}
