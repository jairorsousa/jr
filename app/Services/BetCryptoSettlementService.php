<?php

namespace App\Services;

use App\Enums\BetSettlementMethod;
use App\Enums\BetTransactionType;
use App\Enums\CryptoTransactionStatus;
use App\Enums\CryptoTransactionType;
use App\Models\BetTransaction;
use App\Models\CryptoTransaction;
use Illuminate\Support\Facades\DB;

class BetCryptoSettlementService
{
    public function sync(BetTransaction $betTransaction, ?array $cryptoData): ?CryptoTransaction
    {
        if (!$cryptoData || !$this->canSettleWithCrypto($betTransaction)) {
            return null;
        }

        return DB::transaction(function () use ($betTransaction, $cryptoData) {
            $type = $betTransaction->type === BetTransactionType::Deposit
                ? CryptoTransactionType::SendToBet
                : CryptoTransactionType::ReceiveFromBet;

            $data = [
                'crypto_account_id' => $cryptoData['crypto_account_id'],
                'bet_transaction_id' => $betTransaction->id,
                'crypto_asset_id' => $cryptoData['crypto_asset_id'] ?? null,
                'crypto_network_id' => $cryptoData['crypto_network_id'] ?? null,
                'type' => $type->value,
                'status' => $betTransaction->isConfirmed()
                    ? CryptoTransactionStatus::Confirmed->value
                    : CryptoTransactionStatus::Pending->value,
                'amount_brl' => $betTransaction->amount,
                'crypto_amount' => $cryptoData['crypto_amount'] ?? null,
                'exchange_rate_brl' => $cryptoData['exchange_rate_brl'] ?? null,
                'fee_brl' => $cryptoData['fee_brl'] ?? 0,
                'fee_crypto_amount' => $cryptoData['fee_crypto_amount'] ?? null,
                'tx_hash' => $cryptoData['tx_hash'] ?? null,
                'from_address' => $cryptoData['from_address'] ?? null,
                'to_address' => $cryptoData['to_address'] ?? null,
                'occurred_at' => $betTransaction->occurred_at,
                'confirmed_at' => $betTransaction->confirmed_at,
                'description' => $this->buildDescription($betTransaction),
                'notes' => $cryptoData['notes'] ?? null,
            ];

            $cryptoTransaction = $betTransaction->cryptoTransaction;

            if ($cryptoTransaction) {
                app(CryptoTransactionService::class)->update($cryptoTransaction, $data, null, false);
                $cryptoTransaction = $cryptoTransaction->fresh();
            } else {
                $cryptoTransaction = app(CryptoTransactionService::class)->create($data);
            }

            $betTransaction->forceFill([
                'settlement_method' => BetSettlementMethod::Crypto,
                'crypto_transaction_id' => $cryptoTransaction->id,
                'finance_transaction_id' => null,
            ])->saveQuietly();

            return $cryptoTransaction;
        });
    }

    public function unlink(BetTransaction $betTransaction): void
    {
        DB::transaction(function () use ($betTransaction) {
            $cryptoTransaction = $betTransaction->cryptoTransaction;

            $betTransaction->forceFill([
                'crypto_transaction_id' => null,
                'settlement_method' => BetSettlementMethod::Manual,
            ])->saveQuietly();

            if ($cryptoTransaction) {
                app(CryptoTransactionService::class)->delete($cryptoTransaction);
            }
        });
    }

    private function canSettleWithCrypto(BetTransaction $betTransaction): bool
    {
        return in_array($betTransaction->type, [BetTransactionType::Deposit, BetTransactionType::Withdrawal], true);
    }

    private function buildDescription(BetTransaction $betTransaction): string
    {
        $account = $betTransaction->betAccount()->with(['bettingHouse', 'betUser'])->first();
        $house = $account?->bettingHouse?->name ?? 'Casa de apostas';
        $user = $account?->betUser?->name ?? 'Usuario';

        return $betTransaction->type === BetTransactionType::Deposit
            ? "Envio cripto para {$house} ({$user})"
            : "Recebimento cripto de {$house} ({$user})";
    }
}
