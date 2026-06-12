<?php

namespace App\Services;

use App\Enums\CryptoTransactionStatus;
use App\Models\CryptoAccount;
use App\Models\CryptoTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CryptoBalanceService
{
    public function recalculate(CryptoAccount $account): void
    {
        DB::transaction(function () use ($account) {
            $balance = (float) $account->initial_balance_brl;

            $transactions = $account->transactions()
                ->where('status', CryptoTransactionStatus::Confirmed)
                ->orderBy('occurred_at')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            foreach ($transactions as $transaction) {
                $before = $balance;
                $balance += $transaction->signedAmountBrl();

                $transaction->forceFill([
                    'balance_before_brl' => $before,
                    'balance_after_brl' => $balance,
                    'confirmed_at' => $transaction->confirmed_at ?? $transaction->occurred_at,
                ])->saveQuietly();
            }

            $account->forceFill(['current_balance_brl' => $balance])->saveQuietly();
        });
    }

    public function totalBalanceBrl(): float
    {
        return (float) CryptoAccount::where('is_active', true)->sum('current_balance_brl');
    }

    public function balanceByInstitution(): Collection
    {
        return CryptoAccount::query()
            ->select('crypto_institution_id', DB::raw('SUM(current_balance_brl) as total_balance'))
            ->with('institution')
            ->where('is_active', true)
            ->groupBy('crypto_institution_id')
            ->orderByDesc('total_balance')
            ->get();
    }

    public function balanceByAsset(): Collection
    {
        return CryptoTransaction::query()
            ->select('crypto_asset_id', DB::raw("SUM(CASE WHEN type IN ('bank_deposit', 'buy_crypto', 'receive_from_bet', 'receive_from_wallet', 'adjustment_credit') THEN crypto_amount WHEN type IN ('bank_withdrawal', 'sell_crypto', 'send_to_bet', 'send_to_wallet', 'network_fee', 'exchange_fee', 'adjustment_debit') THEN -crypto_amount ELSE 0 END) as crypto_balance"))
            ->with('asset')
            ->where('status', CryptoTransactionStatus::Confirmed)
            ->whereNotNull('crypto_asset_id')
            ->whereNotNull('crypto_amount')
            ->groupBy('crypto_asset_id')
            ->get();
    }
}
