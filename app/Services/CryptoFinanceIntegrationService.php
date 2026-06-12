<?php

namespace App\Services;

use App\Enums\CryptoTransactionType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\CryptoTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CryptoFinanceIntegrationService
{
    public function sync(CryptoTransaction $cryptoTransaction, ?string $financeAccountId): ?Transaction
    {
        $financeAccountId ??= $cryptoTransaction->financeTransaction?->account_id;

        if (!$cryptoTransaction->type->affectsFinance() || !$financeAccountId) {
            return null;
        }

        return DB::transaction(function () use ($cryptoTransaction, $financeAccountId) {
            $financeAccount = Account::findOrFail($financeAccountId);
            $financeType = $cryptoTransaction->type === CryptoTransactionType::BankDeposit
                ? TransactionType::Expense
                : TransactionType::Income;

            $category = $this->getOrCreateCategory($financeType);
            $description = $this->buildDescription($cryptoTransaction);
            $transaction = $cryptoTransaction->financeTransaction;

            if ($transaction) {
                $oldAccount = $transaction->account;
                $transaction->update([
                    'account_id' => $financeAccount->id,
                    'category_id' => $category->id,
                    'type' => $financeType,
                    'description' => $description,
                    'amount' => $cryptoTransaction->amount_brl,
                    'date' => $cryptoTransaction->occurred_at->format('Y-m-d'),
                    'due_date' => $cryptoTransaction->occurred_at->format('Y-m-d'),
                    'is_paid' => true,
                    'paid_at' => $cryptoTransaction->confirmed_at ?? now(),
                    'notes' => 'Gerada pelo modulo Cripto.',
                ]);

                app(BalanceService::class)->recalculate($oldAccount);
            } else {
                $transaction = Transaction::create([
                    'account_id' => $financeAccount->id,
                    'category_id' => $category->id,
                    'type' => $financeType,
                    'description' => $description,
                    'amount' => $cryptoTransaction->amount_brl,
                    'date' => $cryptoTransaction->occurred_at->format('Y-m-d'),
                    'due_date' => $cryptoTransaction->occurred_at->format('Y-m-d'),
                    'is_paid' => true,
                    'paid_at' => $cryptoTransaction->confirmed_at ?? now(),
                    'notes' => 'Gerada pelo modulo Cripto.',
                ]);

                $cryptoTransaction->forceFill(['finance_transaction_id' => $transaction->id])->saveQuietly();
            }

            app(BalanceService::class)->recalculate($financeAccount);

            return $transaction;
        });
    }

    private function getOrCreateCategory(TransactionType $type): Category
    {
        $name = $type === TransactionType::Expense ? 'Cripto - Aporte' : 'Cripto - Resgate';
        $color = $type === TransactionType::Expense ? '#1a73e8' : '#15a96f';
        $icon = $type === TransactionType::Expense ? 'currency_bitcoin' : 'payments';

        return Category::firstOrCreate(
            ['name' => $name, 'type' => $type],
            ['color' => $color, 'icon' => $icon],
        );
    }

    private function buildDescription(CryptoTransaction $cryptoTransaction): string
    {
        $account = $cryptoTransaction->cryptoAccount()->with('institution')->first();
        $institution = $account?->institution?->name ?? 'Conta cripto';

        return $cryptoTransaction->type === CryptoTransactionType::BankDeposit
            ? "Aporte Cripto - {$institution}"
            : "Resgate Cripto - {$institution}";
    }
}
