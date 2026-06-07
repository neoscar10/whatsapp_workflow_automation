<?php

namespace App\Livewire\Web\Panel;

use Livewire\Component;

class TopbarWalletBalance extends Component
{
    public function refreshBalance()
    {
        // Triggers a re-render which fetches the latest balance
    }

    public function render()
    {
        $wallet = auth()->user()?->wallet;
        $balance = $wallet?->balance ?? 0;
        $threshold = (float) \App\Models\SystemSetting::get('wallet_threshold', 100.00);
        $isLowBalance = $balance < $threshold;

        return view('livewire.web.panel.topbar-wallet-balance', [
            'balance' => $balance,
            'currency' => $wallet?->currency ?? 'INR',
            'isLowBalance' => $isLowBalance,
            'threshold' => $threshold,
        ]);
    }
}
