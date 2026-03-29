<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;

class BalanceService
{
    public function recalculate(Account $account): void
    {
        $incomes = $account->transactions()
            ->where('type', TransactionType::Income)
            ->where('is_paid', true)
            ->sum('amount');

        $expenses = $account->transactions()
            ->where('type', TransactionType::Expense)
            ->where('is_paid', true)
            ->sum('amount');

        $transfersIn = $account->transactions()
            ->where('type', TransactionType::Transfer)
            ->where('is_paid', true)
            ->whereColumn('amount', '>', \DB::raw('0'))
            ->sum('amount');

        $account->update([
            'current_balance' => $account->initial_balance + $incomes + $transfersIn - $expenses,
        ]);
    }

    public function totalBalance(): float
    {
        return Account::where('is_active', true)->sum('current_balance');
    }
}
