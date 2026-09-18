<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppPhoneNumberService
{
    public function getPageMetaForUser(User $user): array
    {
        if ($user->company) {
            return $this->getPageMetaForCompany($user->company);
        }

        return [
            'has_connected_account' => false,
            'account_status' => 'not_connected',
            'company_status' => 'demo',
        ];
    }

    public function getPageMetaForCompany(\App\Models\Company $company): array
    {
        $account = $company->whatsappAccount;
        return [
            'has_connected_account' => $account && $account->connection_status === 'connected',
            'account_status' => $account?->connection_status ?? 'not_connected',
            'company_status' => $company->status ?? 'demo',
        ];
    }

    public function paginateForUser(User $user, array $filters): LengthAwarePaginator
    {
        if ($user->company) {
            return $this->paginateForCompany($user->company, $filters);
        }
        return WhatsAppPhoneNumber::whereRaw('1 = 0')->paginate($filters['per_page'] ?? 10);
    }

    public function paginateForCompany(\App\Models\Company $company, array $filters): LengthAwarePaginator
    {
        if ($company->status === 'demo') {
            $demoNumberId = $this->resolveDemoPhoneNumberId($company);
            if ($demoNumberId) {
                return WhatsAppPhoneNumber::where('id', $demoNumberId)
                    ->paginate($filters['per_page'] ?? 10);
            }
            return WhatsAppPhoneNumber::whereRaw('1 = 0')->paginate($filters['per_page'] ?? 10);
        }

        $query = WhatsAppPhoneNumber::where('company_id', $company->id);

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

    public function createNumberForUser(User $user, array $data): WhatsAppPhoneNumber
    {
        if ($user->company) {
            return $this->createNumberForCompany($user->company, $data, $user);
        }
        throw new \Exception("User has no associated company.");
    }

    public function createNumberForCompany(\App\Models\Company $company, array $data, ?User $user = null): WhatsAppPhoneNumber
    {
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
            'created_by_user_id' => $user->id ?? null,
        ]);
    }

    public function updateNumberForUser(User $user, int $numberId, array $data): WhatsAppPhoneNumber
    {
        $number = $this->findForUser($user, $numberId);
        return $this->updateNumberForCompany($number->company, $numberId, $data);
    }

    public function updateNumberForCompany(\App\Models\Company $company, int $numberId, array $data): WhatsAppPhoneNumber
    {
        $number = WhatsAppPhoneNumber::where('company_id', $company->id)->findOrFail($numberId);

        $updateData = [];
        if (array_key_exists('display_name', $data)) {
            $updateData['display_name'] = $data['display_name'];
        }
        if (array_key_exists('phone_number_id', $data)) {
            $updateData['phone_number_id'] = $data['phone_number_id'];
        }
        if (array_key_exists('phone_number', $data)) {
            $updateData['phone_number'] = $data['phone_number'];
        }

        if (!empty($updateData)) {
            $number->update($updateData);
        }

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

    public function toggleStatusForCompany(\App\Models\Company $company, int $numberId): WhatsAppPhoneNumber
    {
        $number = WhatsAppPhoneNumber::where('company_id', $company->id)->findOrFail($numberId);

        $number->update([
            'status' => $number->status === 'active' ? 'inactive' : 'active',
        ]);

        return $number;
    }

    public function findForUser(User $user, int $numberId): WhatsAppPhoneNumber
    {
        $company = $user->company;
        if ($company && $company->status === 'demo') {
            $demoNumberId = $this->resolveDemoPhoneNumberId($company);
            if ($demoNumberId && $demoNumberId == $numberId) {
                return WhatsAppPhoneNumber::findOrFail($numberId);
            }
        }

        return WhatsAppPhoneNumber::where('company_id', $user->company_id)
            ->findOrFail($numberId);
    }

    protected function resolveDemoPhoneNumberId(\App\Models\Company $company): ?int
    {
        if ($company->demo_whatsapp_phone_number_id) {
            return $company->demo_whatsapp_phone_number_id;
        }

        $systemDemoCompany = \App\Models\Company::where('slug', 'system-demo')->first();
        if ($systemDemoCompany) {
            $id = WhatsAppPhoneNumber::where('company_id', $systemDemoCompany->id)
                ->where('status', 'active')
                ->value('id');
            if ($id) {
                return $id;
            }
        }

        return WhatsAppPhoneNumber::where('status', 'active')->value('id');
    }
}
