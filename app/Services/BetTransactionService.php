<?php

namespace App\Services;

use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Models\BetAccount;
use App\Models\BetTransaction;
use Illuminate\Support\Facades\DB;

class BetTransactionService
{
    public function create(array $data, ?string $financeAccountId = null): BetTransaction
    {
        return DB::transaction(function () use ($data, $financeAccountId) {
            $data['confirmed_at'] = ($data['status'] ?? null) === BetTransactionStatus::Confirmed->value
                ? ($data['confirmed_at'] ?? now())
                : null;

            $transaction = BetTransaction::create($data);

            app(BetBalanceService::class)->recalculate($transaction->betAccount);

            if ($transaction->isConfirmed()) {
                app(BetFinanceIntegrationService::class)->sync($transaction, $financeAccountId);
            }

            return $transaction;
        });
    }

    public function update(BetTransaction $transaction, array $data, ?string $financeAccountId = null, bool $syncFinance = true): BetTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $financeAccountId, $syncFinance) {
            $oldAccountId = $transaction->bet_account_id;
            if (($data['status'] ?? null) === BetTransactionStatus::Confirmed->value && !$transaction->confirmed_at) {
                $data['confirmed_at'] = now();
            }

            if (($data['status'] ?? null) !== BetTransactionStatus::Confirmed->value) {
                $data['confirmed_at'] = null;
            }

            $transaction->update($data);

            app(BetBalanceService::class)->recalculate($transaction->betAccount);

            if ($oldAccountId !== $transaction->bet_account_id) {
                $oldAccount = BetAccount::find($oldAccountId);
                if ($oldAccount) {
                    app(BetBalanceService::class)->recalculate($oldAccount);
                }
            }

            if ($transaction->isConfirmed() && $syncFinance && $transaction->type->affectsFinance()) {
                app(BetFinanceIntegrationService::class)->sync($transaction, $financeAccountId);
            }

            if (
                $transaction->financeTransaction
                && (
                    !$transaction->isConfirmed()
                    || !$syncFinance
                    || !$transaction->type->affectsFinance()
                )
            ) {
                $this->unlinkFinanceTransaction($transaction);
            }

            return $transaction->fresh();
        });
    }

    public function confirm(BetTransaction $transaction, ?string $financeAccountId = null): void
    {
        $this->update($transaction, [
            'status' => BetTransactionStatus::Confirmed->value,
            'confirmed_at' => now(),
        ], $financeAccountId);
    }

    public function cancel(BetTransaction $transaction): void
    {
        $this->update($transaction, [
            'status' => BetTransactionStatus::Cancelled->value,
            'confirmed_at' => null,
        ]);
    }

    public function delete(BetTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $betAccount = $transaction->betAccount;
            $financeTransaction = $transaction->financeTransaction;
            $financeAccount = $financeTransaction?->account;

            $transaction->delete();

            if ($financeTransaction) {
                $financeTransaction->delete();
            }

            app(BetBalanceService::class)->recalculate($betAccount);

            if ($financeAccount) {
                app(BalanceService::class)->recalculate($financeAccount);
            }
        });
    }

    public function typeOptions(): array
    {
        return BetTransactionType::cases();
    }

    private function unlinkFinanceTransaction(BetTransaction $transaction): void
    {
        $financeTransaction = $transaction->financeTransaction;

        if (!$financeTransaction) {
            return;
        }

        $financeAccount = $financeTransaction->account;

        $financeTransaction->delete();
        $transaction->forceFill(['finance_transaction_id' => null])->saveQuietly();

        if ($financeAccount) {
            app(BalanceService::class)->recalculate($financeAccount);
        }
    }
}
