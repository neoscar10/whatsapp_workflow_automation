<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$billingService = app(App\Services\Payment\BillingService::class);
$walletService = app(App\Services\Wallet\WalletService::class);

foreach(App\Models\Company::all() as $company) {
    echo "Company " . $company->id . " ('" . $company->name . "'):\n";
    if ($company->owner) {
        $wallet = $walletService->getOrCreateWallet($company->owner);
        echo "- Balance: " . $wallet->balance . "\n";
        echo "- Affords text: " . ($billingService->canAffordActivity($company, 'text') ? 'Yes' : 'No') . "\n";
    }
}
