<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppPhoneNumberService
{
    public function paginateForUser(User $user, array $filters): LengthAwarePaginator
    {
        $company = $user->company;
        if ($company && $company->status === 'demo') {
            if ($company->demo_whatsapp_phone_number_id) {
                return WhatsAppPhoneNumber::where('id', $company->demo_whatsapp_phone_number_id)
                    ->paginate($filters['per_page'] ?? 10);
            }
            return WhatsAppPhoneNumber::whereRaw('1 = 0')->paginate($filters['per_page'] ?? 10);
        }

        $query = WhatsAppPhoneNumber::where('company_id', $user->company_id);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('phone_number_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function getPageMetaForUser(User $user): array
    {
        $company = $user->company;
        if ($company && $company->status === 'demo') {
            $hasDemoNumber = (bool)$company->demo_whatsapp_phone_number_id;
            return [
                'all_count' => $hasDemoNumber ? 1 : 0,
                'active_count' => $hasDemoNumber ? 1 : 0,
                'inactive_count' => 0,
                'has_connected_account' => $hasDemoNumber,
                'connected_account_id' => null,
                'account_status' => $hasDemoNumber ? 'connected' : 'not_connected',
            ];
        }

        if (!$company) {
            return [
                'all_count' => 0,
                'active_count' => 0,
                'inactive_count' => 0,
                'has_connected_account' => false,
                'connected_account_id' => null,
                'account_status' => 'not_connected',
            ];
        }

        $account = $company->whatsappAccount;
        
        return [
            'all_count' => WhatsAppPhoneNumber::where('company_id', $company->id)->count(),
            'active_count' => WhatsAppPhoneNumber::where('company_id', $company->id)->where('status', 'active')->count(),
            'inactive_count' => WhatsAppPhoneNumber::where('company_id', $company->id)->where('status', 'inactive')->count(),
            'has_connected_account' => $account ? in_array($account->connection_status, ['connected', 'pending-sync', 'error']) : false,
            'connected_account_id' => $account->id ?? null,
            'account_status' => $account->connection_status ?? 'not_connected',
        ];
    }

    public function createNumberForUser(User $user, array $data): WhatsAppPhoneNumber
    {
        $company = $user->company;
        $account = $company->whatsappAccount;

        if (!$account || $account->connection_status !== 'connected') {
            throw new \Exception('Please connect your WhatsApp account before adding phone numbers.');
        }

        return WhatsAppPhoneNumber::create([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'display_name' => $data['display_name'],
            'phone_number_id' => $data['phone_number_id'],
            'phone_number' => $data['phone_number'] ?? null,
            'status' => 'active',
            'created_by_user_id' => $user->id,
        ]);
    }

    public function updateNumberForUser(User $user, int $numberId, array $data): WhatsAppPhoneNumber
    {
        $number = $this->findForUser($user, $numberId);
        
        $number->update([
            'display_name' => $data['display_name'],
            'phone_number_id' => $data['phone_number_id'],
            'phone_number' => $data['phone_number'] ?? $number->phone_number,
        ]);

        return $number;
    }

    public function toggleStatusForUser(User $user, int $numberId): WhatsAppPhoneNumber
    {
        $number = $this->findForUser($user, $numberId);
        
        $number->update([
            'status' => $number->status === 'active' ? 'inactive' : 'active',
        ]);

        return $number;
    }

    public function findForUser(User $user, int $numberId): WhatsAppPhoneNumber
    {
        $company = $user->company;
        if ($company && $company->status === 'demo' && $company->demo_whatsapp_phone_number_id == $numberId) {
            return WhatsAppPhoneNumber::findOrFail($numberId);
        }

        return WhatsAppPhoneNumber::where('company_id', $user->company_id)
            ->findOrFail($numberId);
    }
}
