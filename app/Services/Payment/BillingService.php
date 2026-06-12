<?php

namespace App\Services\Payment;

use App\Models\Company;
use App\Models\CompanyPackage;
use App\Models\SystemSetting;
use App\Services\Wallet\WalletService;
use App\Enums\WalletTransactionCategory;
use Illuminate\Support\Facades\Log;

class BillingService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Get the billing rate for a specific activity type for a company.
     *
     * @param Company $company
     * @param string $type E.g., 'text', 'template_utility', 'template_auth', 'template_marketing', 'automation'
     * @return float
     */
    public function getActiveRate(Company $company, string $type): float
    {
        if ($company->status === 'demo') {
            $defaults = [
                'text' => 0.1000,
                'template_utility' => 0.3000,
                'template_auth' => 0.1500,
                'template_marketing' => 0.5000,
                'automation' => 0.0500,
            ];
            $default = $defaults[$type] ?? 0.0000;
            return (float) SystemSetting::get("demo_{$type}_rate", $default);
        }

        $package = CompanyPackage::where('company_id', $company->id)->where('status', 'active')->first();
        if (!$package) {
            Log::warning("Company {$company->id} has no active package, fallback to free.");
            return 0.00;
        }

        return (float) $package->getAttribute("{$type}_rate");
    }

    /**
     * Check if the company has sufficient balance for a specific activity.
     */
    public function canAffordActivity(Company $company, string $type): bool
    {
        $rate = $this->getActiveRate($company, $type);
        
        if ($rate <= 0) {
            return true;
        }

        $wallet = $this->walletService->getOrCreateWallet($company->owner);
        return $this->walletService->hasSufficientBalance($wallet, $rate);
    }

    /**
     * Debit the wallet for a specific activity.
     */
    public function debitForActivity(Company $company, string $type, string $description, ?array $metadata = null): bool
    {
        try {
            $rate = $this->getActiveRate($company, $type);
            
            if ($rate <= 0) {
                return true; // Free action or 0 rate
            }

            // Ensure wallet exists for the owner
            $wallet = $this->walletService->getOrCreateWallet($company->owner);

            $this->walletService->debit(
                $wallet, 
                $rate, 
                WalletTransactionCategory::USAGE, 
                $description, 
                null, 
                $metadata
            );

            return true;
        } catch (\App\Exceptions\InsufficientWalletBalanceException $e) {
            Log::info("Insufficient balance for {$type} action", ['company_id' => $company->id]);
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to debit for {$type} action", [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
