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

            $company = Company::create([
                'name' => $companyName,
                'slug' => $companySlug,
                'primary_email' => $email,
                'status' => 'trial',
                'trial_starts_at' => now(),
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'company_id' => $company->id,
                // Using company name as user name for now as requested
                'name' => $companyName,
                'email' => $email,
                'password' => Hash::make($password),
                'is_company_owner' => true,
            ]);

            return [
                'company' => $company,
                'user' => $user,
            ];
        });
    }
}
