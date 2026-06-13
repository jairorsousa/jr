<?php

namespace App\Services;

use App\Enums\CryptoTransactionStatus;
use App\Enums\CryptoTransactionType;
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

    public function createTransfer(array $data, string $targetCryptoAccountId): CryptoTransaction
    {
        return DB::transaction(function () use ($data, $targetCryptoAccountId) {
            $sourceAccount = CryptoAccount::findOrFail($data['crypto_account_id']);
            $targetAccount = CryptoAccount::findOrFail($targetCryptoAccountId);
            $description = $data['description'];

            $sourceTransaction = $this->create(array_merge($data, [
                'type' => CryptoTransactionType::SendToWallet->value,
                'description' => "Transferencia para {$targetAccount->name} - {$description}",
            ]));

            $targetTransaction = $this->create(array_merge($data, [
                'crypto_account_id' => $targetCryptoAccountId,
                'type' => CryptoTransactionType::ReceiveFromWallet->value,
                'fee_brl' => 0,
                'fee_crypto_amount' => null,
                'description' => "Transferencia de {$sourceAccount->name} - {$description}",
            ]));

            $sourceTransaction->forceFill(['related_crypto_transaction_id' => $targetTransaction->id])->saveQuietly();
            $targetTransaction->forceFill(['related_crypto_transaction_id' => $sourceTransaction->id])->saveQuietly();

            return $sourceTransaction->fresh();
        });
    }

    public function updateTransfer(CryptoTransaction $transaction, array $data, string $targetCryptoAccountId): CryptoTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $targetCryptoAccountId) {
            $sourceTransaction = $transaction->type === CryptoTransactionType::ReceiveFromWallet && $transaction->relatedTransaction
                ? $transaction->relatedTransaction
                : $transaction;

            $sourceAccount = CryptoAccount::findOrFail($data['crypto_account_id']);
            $targetAccount = CryptoAccount::findOrFail($targetCryptoAccountId);
            $targetTransaction = $sourceTransaction->relatedTransaction;
            $description = $data['description'];

            $sourceTransaction = $this->update($sourceTransaction, array_merge($data, [
                'type' => CryptoTransactionType::SendToWallet->value,
                'description' => "Transferencia para {$targetAccount->name} - {$description}",
            ]), null, false);

            $targetData = array_merge($data, [
                'crypto_account_id' => $targetCryptoAccountId,
                'type' => CryptoTransactionType::ReceiveFromWallet->value,
                'fee_brl' => 0,
                'fee_crypto_amount' => null,
                'description' => "Transferencia de {$sourceAccount->name} - {$description}",
            ]);

            if ($targetTransaction) {
                $targetTransaction = $this->update($targetTransaction, $targetData, null, false);
            } else {
                $targetTransaction = $this->create($targetData);
            }

            $sourceTransaction->forceFill(['related_crypto_transaction_id' => $targetTransaction->id])->saveQuietly();
            $targetTransaction->forceFill(['related_crypto_transaction_id' => $sourceTransaction->id])->saveQuietly();

            return $sourceTransaction->fresh();
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

    public function delete(CryptoTransaction $transaction, bool $deleteRelated = true): void
    {
        DB::transaction(function () use ($transaction, $deleteRelated) {
            $cryptoAccount = $transaction->cryptoAccount;
            $financeTransaction = $transaction->financeTransaction;
            $financeAccount = $financeTransaction?->account;
            $relatedTransaction = $transaction->relatedTransaction;

            if ($transaction->betTransaction) {
                $transaction->betTransaction->forceFill([
                    'crypto_transaction_id' => null,
                    'settlement_method' => 'manual',
                ])->saveQuietly();
            }

            if ($relatedTransaction && $deleteRelated) {
                $this->delete($relatedTransaction, false);
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
