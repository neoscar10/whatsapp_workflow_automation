<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyRegistrationService
{
    /**
     * Register a new company and its owner user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $companyName = $data['company_name'];
            $email = $data['email'];
            $password = $data['password'];

            $companySlug = Str::slug($companyName);
            $originalSlug = $companySlug;
            $count = 1;

            while (Company::where('slug', $companySlug)->exists()) {
                $companySlug = $originalSlug . '-' . $count;
                $count++;
            }

            $demoCompany = Company::where('slug', 'system-demo')->first();
            $demoPhoneNumberId = null;
            if ($demoCompany) {
                $demoPhoneNumberId = \App\Models\WhatsApp\WhatsAppPhoneNumber::where('company_id', $demoCompany->id)
                    ->where('status', 'active')
                    ->value('id');
            }
            if (!$demoPhoneNumberId) {
                $demoPhoneNumberId = \App\Models\WhatsApp\WhatsAppPhoneNumber::where('status', 'active')->value('id');
            }

            $company = Company::create([
                'name' => $companyName,
                'slug' => $companySlug,
                'primary_email' => $email,
                'status' => 'demo',
                'country' => $data['country'] ?? 'IN',
                'demo_credits' => $data['demo_credits'] ?? 100.0000,
                'demo_ends_at' => now()->addDays(14),
                'demo_whatsapp_phone_number_id' => $demoPhoneNumberId,
                'trial_starts_at' => now(),
                'trial_ends_at' => now()->addDays(14),
            ]);

            $userName = !empty($data['name']) ? $data['name'] : $companyName;

            $user = User::create([
                'company_id' => $company->id,
                'name' => $userName,
                'email' => $email,
                'password' => Hash::make($password),
                'is_company_owner' => true,
            ]);

            // Initialize wallet for company owner
            app(\App\Services\Wallet\WalletService::class)->getOrCreateWallet($user);

            return [
                'company' => $company,
                'user' => $user,
            ];
        });
    }
}
