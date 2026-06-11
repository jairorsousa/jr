<?php

namespace App\Services;

use App\Enums\BetTransactionType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\BetTransaction;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BetFinanceIntegrationService
{
    public function sync(BetTransaction $betTransaction, ?string $financeAccountId): ?Transaction
    {
        $financeAccountId ??= $betTransaction->financeTransaction?->account_id;

        if (!$betTransaction->type->affectsFinance() || !$financeAccountId) {
            return null;
        }

        return DB::transaction(function () use ($betTransaction, $financeAccountId) {
            $financeAccount = Account::findOrFail($financeAccountId);
            $financeType = $betTransaction->type === BetTransactionType::Deposit
                ? TransactionType::Expense
                : TransactionType::Income;

            $category = $this->getOrCreateCategory($financeType);
            $description = $this->buildDescription($betTransaction);

            $transaction = $betTransaction->financeTransaction;

            if ($transaction) {
                $oldAccount = $transaction->account;
                $transaction->update([
                    'account_id' => $financeAccount->id,
                    'category_id' => $category->id,
                    'type' => $financeType,
                    'description' => $description,
                    'amount' => $betTransaction->amount,
                    'date' => $betTransaction->occurred_at->format('Y-m-d'),
                    'due_date' => $betTransaction->occurred_at->format('Y-m-d'),
                    'is_paid' => true,
                    'paid_at' => $betTransaction->confirmed_at ?? now(),
                    'notes' => 'Gerada pelo modulo Bets.',
                ]);

                app(BalanceService::class)->recalculate($oldAccount);
            } else {
                $transaction = Transaction::create([
                    'account_id' => $financeAccount->id,
                    'category_id' => $category->id,
                    'type' => $financeType,
                    'description' => $description,
                    'amount' => $betTransaction->amount,
                    'date' => $betTransaction->occurred_at->format('Y-m-d'),
                    'due_date' => $betTransaction->occurred_at->format('Y-m-d'),
                    'is_paid' => true,
                    'paid_at' => $betTransaction->confirmed_at ?? now(),
                    'notes' => 'Gerada pelo modulo Bets.',
                ]);

                $betTransaction->forceFill(['finance_transaction_id' => $transaction->id])->saveQuietly();
            }

            app(BalanceService::class)->recalculate($financeAccount);

            return $transaction;
        });
    }

    private function getOrCreateCategory(TransactionType $type): Category
    {
        $name = $type === TransactionType::Expense ? 'Bets - Depositos' : 'Bets - Saques';
        $color = $type === TransactionType::Expense ? '#e43b3b' : '#15a96f';
        $icon = $type === TransactionType::Expense ? 'sports_soccer' : 'payments';

        return Category::firstOrCreate(
            ['name' => $name, 'type' => $type],
            ['color' => $color, 'icon' => $icon],
        );
    }

    private function buildDescription(BetTransaction $betTransaction): string
    {
        $account = $betTransaction->betAccount()->with(['bettingHouse', 'betUser'])->first();
        $house = $account?->bettingHouse?->name ?? 'Casa de apostas';
        $user = $account?->betUser?->name ?? 'Usuario';

        return $betTransaction->type === BetTransactionType::Deposit
            ? "Deposito Bets - {$house} ({$user})"
            : "Saque Bets - {$house} ({$user})";
    }
}
