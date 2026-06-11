<?php

namespace App\Services;

use App\Enums\BetTransactionStatus;
use App\Models\BetAccount;
use App\Models\BetTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BetBalanceService
{
    public function recalculate(BetAccount $account): void
    {
        DB::transaction(function () use ($account) {
            $balance = (float) $account->initial_balance;

            $transactions = $account->transactions()
                ->where('status', BetTransactionStatus::Confirmed)
                ->orderBy('occurred_at')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            foreach ($transactions as $transaction) {
                $before = $balance;
                $balance += $transaction->signedAmount();

                $transaction->forceFill([
                    'balance_before' => $before,
                    'balance_after' => $balance,
                    'confirmed_at' => $transaction->confirmed_at ?? $transaction->occurred_at,
                ])->saveQuietly();
            }

            $account->forceFill(['current_balance' => $balance])->saveQuietly();
        });
    }

    public function totalBalance(): float
    {
        return (float) BetAccount::where('is_active', true)->sum('current_balance');
    }

    public function balanceByHouse(): Collection
    {
        return BetAccount::query()
            ->select('betting_house_id', DB::raw('SUM(current_balance) as total_balance'))
            ->with('bettingHouse')
            ->where('is_active', true)
            ->groupBy('betting_house_id')
            ->orderByDesc('total_balance')
            ->get();
    }

    public function balanceByUser(): Collection
    {
        return BetAccount::query()
            ->select('bet_user_id', DB::raw('SUM(current_balance) as total_balance'))
            ->with('betUser')
            ->where('is_active', true)
            ->groupBy('bet_user_id')
            ->orderByDesc('total_balance')
            ->get();
    }

    public function periodTotals(string $from, string $to): array
    {
        $transactions = BetTransaction::query()
            ->where('status', BetTransactionStatus::Confirmed)
            ->whereBetween('occurred_at', [$from, $to])
            ->get();

        $stake = $transactions->filter(fn (BetTransaction $transaction) => $transaction->type->value === 'bet_stake')->sum('amount');
        $payout = $transactions->filter(fn (BetTransaction $transaction) => $transaction->type->value === 'bet_payout')->sum('amount');
        $deposits = $transactions->filter(fn (BetTransaction $transaction) => $transaction->type->value === 'deposit')->sum('amount');
        $withdrawals = $transactions->filter(fn (BetTransaction $transaction) => $transaction->type->value === 'withdrawal')->sum('amount');

        $operationalIn = $transactions
            ->filter(fn (BetTransaction $transaction) => $transaction->type->isIn() && !$transaction->type->affectsFinance())
            ->sum('amount');

        $operationalOut = $transactions
            ->filter(fn (BetTransaction $transaction) => $transaction->type->isOut() && !$transaction->type->affectsFinance())
            ->sum('amount');

        $profit = $operationalIn - $operationalOut;

        return [
            'stake' => (float) $stake,
            'payout' => (float) $payout,
            'deposits' => (float) $deposits,
            'withdrawals' => (float) $withdrawals,
            'profit' => (float) $profit,
            'roi' => $stake > 0 ? (float) (($profit / $stake) * 100) : 0.0,
        ];
    }
}
