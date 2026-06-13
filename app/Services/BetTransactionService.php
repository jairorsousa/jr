<?php

namespace App\Services;

use App\Enums\BetSettlementMethod;
use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Models\BetAccount;
use App\Models\BetTransaction;
use App\Models\CryptoTransaction;
use Illuminate\Support\Facades\DB;

class BetTransactionService
{
    public function create(array $data, ?string $financeAccountId = null, ?array $cryptoData = null): BetTransaction
    {
        return DB::transaction(function () use ($data, $financeAccountId, $cryptoData) {
            $settlementMethod = $this->resolveSettlementMethod(
                $data['settlement_method'] ?? null,
                $financeAccountId,
                $cryptoData,
            );

            $data['settlement_method'] = $settlementMethod->value;
            $data['confirmed_at'] = ($data['status'] ?? null) === BetTransactionStatus::Confirmed->value
                ? ($data['confirmed_at'] ?? now())
                : null;

            $transaction = BetTransaction::create($data);

            app(BetBalanceService::class)->recalculate($transaction->betAccount);

            $this->syncSettlement($transaction->fresh(), $settlementMethod, $financeAccountId, $cryptoData);

            return $transaction->fresh();
        });
    }

    public function update(
        BetTransaction $transaction,
        array $data,
        ?string $financeAccountId = null,
        bool $syncFinance = true,
        ?array $cryptoData = null,
    ): BetTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $financeAccountId, $syncFinance, $cryptoData) {
            $oldAccountId = $transaction->bet_account_id;
            $settlementMethod = $this->resolveSettlementMethod(
                $data['settlement_method'] ?? null,
                $financeAccountId,
                $cryptoData,
                $syncFinance ? $transaction->settlement_method : BetSettlementMethod::Manual,
            );

            $data['settlement_method'] = $settlementMethod->value;

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

            $this->syncSettlement($transaction->fresh(), $settlementMethod, $financeAccountId, $cryptoData);

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
            $cryptoTransaction = $transaction->cryptoTransaction;

            if ($cryptoTransaction) {
                app(CryptoTransactionService::class)->delete($cryptoTransaction);
            }

            if ($financeTransaction) {
                $financeTransaction->delete();
            }

            $transaction->delete();

            app(BetBalanceService::class)->recalculate($betAccount);

            if ($financeAccount) {
                app(BalanceService::class)->recalculate($financeAccount);
            }
        });
    }

    public function typeOptions(): array
    {
        return BetTransactionType::selectableCases();
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

    private function unlinkCryptoTransaction(BetTransaction $transaction): void
    {
        if (!$transaction->cryptoTransaction) {
            return;
        }

        app(BetCryptoSettlementService::class)->unlink($transaction);
    }

    private function resolveSettlementMethod(
        BetSettlementMethod|string|null $requestedMethod,
        ?string $financeAccountId,
        ?array $cryptoData,
        BetSettlementMethod|string|null $fallback = null,
    ): BetSettlementMethod {
        if ($requestedMethod instanceof BetSettlementMethod) {
            return $requestedMethod;
        }

        if (is_string($requestedMethod) && $requestedMethod !== '') {
            return BetSettlementMethod::from($requestedMethod);
        }

        if ($financeAccountId) {
            return BetSettlementMethod::Bank;
        }

        if ($cryptoData) {
            return BetSettlementMethod::Crypto;
        }

        if ($fallback instanceof BetSettlementMethod) {
            return $fallback;
        }

        if (is_string($fallback) && $fallback !== '') {
            return BetSettlementMethod::from($fallback);
        }

        return BetSettlementMethod::Manual;
    }

    private function syncSettlement(
        BetTransaction $transaction,
        BetSettlementMethod $settlementMethod,
        ?string $financeAccountId,
        ?array $cryptoData,
    ): void {
        if (!$transaction->type->affectsFinance()) {
            $this->unlinkFinanceTransaction($transaction);
            $this->unlinkCryptoTransaction($transaction);
            $transaction->forceFill(['settlement_method' => BetSettlementMethod::Manual])->saveQuietly();
            return;
        }

        if ($settlementMethod === BetSettlementMethod::Bank) {
            $this->unlinkCryptoTransaction($transaction);
            $transaction->forceFill(['settlement_method' => BetSettlementMethod::Bank])->saveQuietly();

            if ($transaction->isConfirmed()) {
                app(BetFinanceIntegrationService::class)->sync($transaction, $financeAccountId);
            } else {
                $this->unlinkFinanceTransaction($transaction);
            }

            return;
        }

        if ($settlementMethod === BetSettlementMethod::Crypto) {
            $this->unlinkFinanceTransaction($transaction);

            $cryptoData ??= $this->cryptoDataFromExisting($transaction->cryptoTransaction);
            if ($cryptoData) {
                app(BetCryptoSettlementService::class)->sync($transaction, $cryptoData);
            } else {
                $transaction->forceFill(['settlement_method' => BetSettlementMethod::Crypto])->saveQuietly();
            }

            return;
        }

        $this->unlinkFinanceTransaction($transaction);
        $this->unlinkCryptoTransaction($transaction);
        $transaction->forceFill(['settlement_method' => BetSettlementMethod::Manual])->saveQuietly();
    }

    private function cryptoDataFromExisting(?CryptoTransaction $transaction): ?array
    {
        if (!$transaction) {
            return null;
        }

        return [
            'crypto_account_id' => $transaction->crypto_account_id,
            'crypto_asset_id' => $transaction->crypto_asset_id,
            'crypto_network_id' => $transaction->crypto_network_id,
            'crypto_amount' => $transaction->crypto_amount,
            'exchange_rate_brl' => $transaction->exchange_rate_brl,
            'fee_brl' => $transaction->fee_brl,
            'fee_crypto_amount' => $transaction->fee_crypto_amount,
            'tx_hash' => $transaction->tx_hash,
            'from_address' => $transaction->from_address,
            'to_address' => $transaction->to_address,
            'notes' => $transaction->notes,
        ];
    }
}
