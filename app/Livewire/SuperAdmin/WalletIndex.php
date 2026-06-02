<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class WalletIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showDetailsModal = false;
    public $selectedWallet = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function viewDetails($id)
    {
        $this->selectedWallet = Wallet::with(['user.company'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function closeModal()
    {
        $this->showDetailsModal = false;
        $this->selectedWallet = null;
    }

    public function render()
    {
        $query = Wallet::with(['user.company']);

        if (!empty($this->search)) {
            $search = $this->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($qc) use ($search) {
                      $qc->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $wallets = $query->latest('last_transaction_at')->paginate(10);

        $selectedTransactions = collect();
        if ($this->selectedWallet) {
            $selectedTransactions = WalletTransaction::where('wallet_id', $this->selectedWallet->id)
                ->latest()
                ->paginate(10, ['*'], 'txPage');
        }

        return view('livewire.super-admin.wallet-index', [
            'wallets' => $wallets,
            'selectedTransactions' => $selectedTransactions,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Wallet Monitoring',
            'activeNav' => 'wallet-monitoring',
        ]);
    }
}
