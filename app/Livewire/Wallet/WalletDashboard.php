<?php

namespace App\Livewire\Wallet;

use App\Models\WalletTransaction;
use App\Models\PaymentTransaction;
use App\Services\Wallet\WalletService;
use App\Services\Payment\PaymentService;
use App\Enums\WalletTransactionCategory;
use App\Enums\WalletTransactionStatus;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class WalletDashboard extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $type = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'type' => ['except' => ''],
    ];

    /**
     * Intercept and auto-verify payment on mount (e.g. from Cashfree redirects).
     */
    public function mount(PaymentService $paymentService)
    {
        $orderId = request()->query('order_id');
        
        if ($orderId) {
            try {
                // Find matching payment transaction
                $paymentTransaction = PaymentTransaction::where('gateway_order_id', $orderId)
                    ->orWhere('id', $orderId)
                    ->first();

                if ($paymentTransaction) {
                    if ($paymentTransaction->status === PaymentTransactionStatus::PENDING ||
                        $paymentTransaction->status === PaymentTransactionStatus::PROCESSING) {
                        
                        $paymentService->verifyWalletFunding($paymentTransaction->id, [
                            'cf_payment_id' => request()->query('cf_payment_id') ?? '',
                            'cf_signature' => request()->query('cf_signature') ?? '',
                        ]);

                        session()->flash('success', 'Wallet funded successfully!');
                    } elseif ($paymentTransaction->status === PaymentTransactionStatus::SUCCESSFUL) {
                        session()->flash('success', 'Wallet funded successfully!');
                    } else {
                        session()->flash('error', 'Payment verification failed or was cancelled.');
                    }
                } else {
                    session()->flash('error', 'Payment transaction not found.');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Cashfree dashboard mount verification error", [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'Failed to verify payment: ' . $e->getMessage());
            }

            // Redirect back to wallet dashboard to clear the query parameters
            return redirect()->route('wallet.index');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedType()
    {
        $this->resetPage();
    }

    #[On('wallet-updated')]
    public function refreshDashboard()
    {
        // Livewire will automatically reload render properties
    }

    public function render(WalletService $walletService)
    {
        $user = Auth::user();
        $wallet = $walletService->getOrCreateWallet($user);

        // Calculate statistics based on successful funding categories
        $totalFunded = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('status', WalletTransactionStatus::SUCCESSFUL)
            ->where('category', WalletTransactionCategory::FUNDING)
            ->sum('amount');

        $latestFundingDate = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('status', WalletTransactionStatus::SUCCESSFUL)
            ->where('category', WalletTransactionCategory::FUNDING)
            ->latest()
            ->first()?->created_at;

        $recentTransactionsCount = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Transaction list query
        $query = WalletTransaction::where('wallet_id', $wallet->id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        $transactions = $query->latest()->paginate(10);

        // Fetch recent external payment attempts for retry capabilities
        $paymentAttempts = PaymentTransaction::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.wallet.wallet-dashboard', [
            'wallet' => $wallet,
            'totalFunded' => $totalFunded,
            'latestFundingDate' => $latestFundingDate,
            'recentTransactionsCount' => $recentTransactionsCount,
            'transactions' => $transactions,
            'paymentAttempts' => $paymentAttempts,
        ])->layout('layouts.panel', ['title' => 'My Wallet', 'activeNav' => 'wallet']);
    }
}
