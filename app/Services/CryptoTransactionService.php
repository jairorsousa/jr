<?php

namespace App\Services;

use App\Enums\CryptoTransactionStatus;
use App\Models\CryptoAccount;
use App\Models\CryptoTransaction;
use Illuminate\Support\Facades\DB;

class CryptoTransactionService
{
    public function create(array $data, ?string $financeAccountId = null): CryptoTransaction
    {
        return DB::transaction(function () use ($data, $financeAccountId) {
            $data['confirmed_at'] = ($data['status'] ?? null) === CryptoTransactionStatus::Confirmed->value
                ? ($data['confirmed_at'] ?? now())
                : null;

            $transaction = CryptoTransaction::create($data);

            app(CryptoBalanceService::class)->recalculate($transaction->cryptoAccount);

            if ($transaction->isConfirmed()) {
                app(CryptoFinanceIntegrationService::class)->sync($transaction, $financeAccountId);
            }

            return $transaction;
        });
    }

    public function update(CryptoTransaction $transaction, array $data, ?string $financeAccountId = null, bool $syncFinance = true): CryptoTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $financeAccountId, $syncFinance) {
            $oldAccountId = $transaction->crypto_account_id;

            if (($data['status'] ?? null) === CryptoTransactionStatus::Confirmed->value && !$transaction->confirmed_at) {
                $data['confirmed_at'] = now();
            }

            if (($data['status'] ?? null) !== CryptoTransactionStatus::Confirmed->value) {
                $data['confirmed_at'] = null;
            }

            $transaction->update($data);

            app(CryptoBalanceService::class)->recalculate($transaction->cryptoAccount);

            if ($oldAccountId !== $transaction->crypto_account_id) {
                $oldAccount = CryptoAccount::find($oldAccountId);
                if ($oldAccount) {
                    app(CryptoBalanceService::class)->recalculate($oldAccount);
                }
            }

            if ($transaction->isConfirmed() && $syncFinance && $transaction->type->affectsFinance()) {
                app(CryptoFinanceIntegrationService::class)->sync($transaction, $financeAccountId);
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

    public function confirm(CryptoTransaction $transaction, ?string $financeAccountId = null): void
    {
        $this->update($transaction, [
            'status' => CryptoTransactionStatus::Confirmed->value,
            'confirmed_at' => now(),
        ], $financeAccountId);
    }

    public function cancel(CryptoTransaction $transaction): void
    {
        $this->update($transaction, [
            'status' => CryptoTransactionStatus::Cancelled->value,
            'confirmed_at' => null,
        ]);
    }

    public function delete(CryptoTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $cryptoAccount = $transaction->cryptoAccount;
            $financeTransaction = $transaction->financeTransaction;
            $financeAccount = $financeTransaction?->account;

            if ($transaction->betTransaction) {
                $transaction->betTransaction->forceFill([
                    'crypto_transaction_id' => null,
                    'settlement_method' => 'manual',
                ])->saveQuietly();
            }

            $transaction->delete();

            if ($financeTransaction) {
                $financeTransaction->delete();
            }

            app(CryptoBalanceService::class)->recalculate($cryptoAccount);

            if ($financeAccount) {
                app(BalanceService::class)->recalculate($financeAccount);
            }
        });
    }

    private function unlinkFinanceTransaction(CryptoTransaction $transaction): void
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
