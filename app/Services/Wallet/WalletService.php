<?php

namespace App\Services\Wallet;

use App\Enums\WalletStatus;
use App\Enums\WalletTransactionCategory;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\WalletOperationException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Get or create a wallet for a user.
     *
     * @param User $user
     * @return Wallet
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return DB::transaction(function () use ($user) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => '0.0000',
                    'currency' => config('payment.currency', 'INR'),
                    'status' => WalletStatus::ACTIVE,
                ]);
            }

            return $wallet;
        });
    }

    /**
     * Credit funds to a wallet.
     *
     * @param Wallet $wallet
     * @param float|string $amount
     * @param WalletTransactionCategory $category
     * @param string|null $description
     * @param string|null $providerReference
     * @param array|null $metadata
     * @param User|null $createdBy
     * @return WalletTransaction
     * @throws WalletOperationException
     */
    public function credit(
        Wallet $wallet,
        float|string $amount,
        WalletTransactionCategory $category,
        ?string $description = null,
        ?string $providerReference = null,
        ?array $metadata = null,
        ?User $createdBy = null
    ): WalletTransaction {
        if (bccomp((string) $amount, '0', 4) <= 0) {
            throw new WalletOperationException("Credit amount must be greater than zero.");
        }

        return DB::transaction(function () use ($wallet, $amount, $category, $description, $providerReference, $metadata, $createdBy) {
            // Lock wallet row for update
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();
            $company = $wallet->user?->company;
            $isDemo = $company && $company->status === 'demo';

            if ($isDemo) {
                $balanceBefore = $company->demo_credits;
                $balanceAfter = bcadd((string) $balanceBefore, (string) $amount, 4);
                $company->update(['demo_credits' => $balanceAfter]);
            } else {
                if ($wallet->status !== WalletStatus::ACTIVE) {
                    throw new WalletOperationException("Cannot credit a wallet with status: " . $wallet->status->value);
                }
                $balanceBefore = $wallet->balance;
                $balanceAfter = bcadd((string) $balanceBefore, (string) $amount, 4);
            }

            // Generate unique reference
            $reference = 'TXN_' . Str::upper(Str::random(12));

            // Create ledger entry
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionType::CREDIT,
                'category' => $category,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'provider_reference' => $providerReference,
                'status' => WalletTransactionStatus::SUCCESSFUL,
                'metadata' => $metadata,
                'description' => $description . ($isDemo ? ' (Demo Mode)' : ''),
                'created_by' => $createdBy?->id,
            ]);

            // Update wallet balance and last transaction timestamp
            if (!$isDemo) {
                $wallet->update([
                    'balance' => $balanceAfter,
                    'last_transaction_at' => now(),
                ]);
            } else {
                $wallet->update([
                    'last_transaction_at' => now(),
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Debit funds from a wallet.
     *
     * @param Wallet $wallet
     * @param float|string $amount
     * @param WalletTransactionCategory $category
     * @param string|null $description
     * @param string|null $providerReference
     * @param array|null $metadata
     * @param User|null $createdBy
     * @return WalletTransaction
     * @throws WalletOperationException
     * @throws InsufficientWalletBalanceException
     */
    public function debit(
        Wallet $wallet,
        float|string $amount,
        WalletTransactionCategory $category,
        ?string $description = null,
        ?string $providerReference = null,
        ?array $metadata = null,
        ?User $createdBy = null
    ): WalletTransaction {
        if (bccomp((string) $amount, '0', 4) <= 0) {
            throw new WalletOperationException("Debit amount must be greater than zero.");
        }

        return DB::transaction(function () use ($wallet, $amount, $category, $description, $providerReference, $metadata, $createdBy) {
            // Lock wallet row for update
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();
            $company = $wallet->user?->company;
            $isDemo = $company && $company->status === 'demo';

            if ($isDemo) {
                if (!$this->hasSufficientBalance($wallet, $amount)) {
                    throw new InsufficientWalletBalanceException();
                }
                $balanceBefore = $company->demo_credits;
                $balanceAfter = bcsub((string) $balanceBefore, (string) $amount, 4);
                $company->update(['demo_credits' => $balanceAfter]);
            } else {
                if ($wallet->status !== WalletStatus::ACTIVE) {
                    throw new WalletOperationException("Cannot debit a wallet with status: " . $wallet->status->value);
                }
                if (!$this->hasSufficientBalance($wallet, $amount)) {
                    throw new InsufficientWalletBalanceException();
                }
                $balanceBefore = $wallet->balance;
                $balanceAfter = bcsub((string) $balanceBefore, (string) $amount, 4);
            }

            // Generate unique reference
            $reference = 'TXN_' . Str::upper(Str::random(12));

            // Create ledger entry
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionType::DEBIT,
                'category' => $category,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'provider_reference' => $providerReference,
                'status' => WalletTransactionStatus::SUCCESSFUL,
                'metadata' => $metadata,
                'description' => $description . ($isDemo ? ' (Demo Mode)' : ''),
                'created_by' => $createdBy?->id,
            ]);

            // Update wallet balance and last transaction timestamp
            if (!$isDemo) {
                $wallet->update([
                    'balance' => $balanceAfter,
                    'last_transaction_at' => now(),
                ]);
            } else {
                $wallet->update([
                    'last_transaction_at' => now(),
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Check if a wallet has sufficient balance.
     *
     * @param Wallet $wallet
     * @param float|string $amount
     * @return bool
     */
    public function hasSufficientBalance(Wallet $wallet, float|string $amount): bool
    {
        $company = $wallet->user?->company;
        if ($company && $company->status === 'demo') {
            return bccomp((string) $company->demo_credits, (string) $amount, 4) >= 0;
        }
        return bccomp((string) $wallet->balance, (string) $amount, 4) >= 0;
    }
}
