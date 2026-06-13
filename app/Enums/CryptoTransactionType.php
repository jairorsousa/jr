<?php

namespace App\Enums;

enum CryptoTransactionType: string
{
    case BankDeposit = 'bank_deposit';
    case BankWithdrawal = 'bank_withdrawal';
    case BuyCrypto = 'buy_crypto';
    case SellCrypto = 'sell_crypto';
    case SendToBet = 'send_to_bet';
    case ReceiveFromBet = 'receive_from_bet';
    case SendToWallet = 'send_to_wallet';
    case ReceiveFromWallet = 'receive_from_wallet';
    case NetworkFee = 'network_fee';
    case ExchangeFee = 'exchange_fee';
    case AdjustmentCredit = 'adjustment_credit';
    case AdjustmentDebit = 'adjustment_debit';

    public function label(): string
    {
        return match ($this) {
            self::BankDeposit => 'Aporte do banco',
            self::BankWithdrawal => 'Resgate para banco',
            self::BuyCrypto => 'Compra de cripto',
            self::SellCrypto => 'Venda de cripto',
            self::SendToBet => 'Envio para bet',
            self::ReceiveFromBet => 'Recebimento da bet',
            self::SendToWallet => 'Transferencia enviada',
            self::ReceiveFromWallet => 'Transferencia recebida',
            self::NetworkFee => 'Taxa de rede',
            self::ExchangeFee => 'Taxa da corretora',
            self::AdjustmentCredit => 'Ajuste de credito',
            self::AdjustmentDebit => 'Ajuste de debito',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::BankDeposit,
            self::ReceiveFromBet,
            self::ReceiveFromWallet,
            self::AdjustmentCredit => 'in',
            self::BankWithdrawal,
            self::SendToBet,
            self::SendToWallet,
            self::NetworkFee,
            self::ExchangeFee,
            self::AdjustmentDebit => 'out',
            self::BuyCrypto,
            self::SellCrypto => 'neutral',
        };
    }

    public function isIn(): bool
    {
        return $this->direction() === 'in';
    }

    public function isOut(): bool
    {
        return $this->direction() === 'out';
    }

    public function affectsFinance(): bool
    {
        return in_array($this, [self::BankDeposit, self::BankWithdrawal], true);
    }

    public function affectsBet(): bool
    {
        return in_array($this, [self::SendToBet, self::ReceiveFromBet], true);
    }

    public function badge(): string
    {
        return match ($this->direction()) {
            'in' => 'success',
            'out' => 'error',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BankDeposit => 'south_west',
            self::BankWithdrawal => 'north_east',
            self::BuyCrypto,
            self::SellCrypto => 'currency_exchange',
            self::SendToBet,
            self::ReceiveFromBet => 'sports_soccer',
            self::SendToWallet,
            self::ReceiveFromWallet => 'account_balance_wallet',
            self::NetworkFee,
            self::ExchangeFee => 'receipt_long',
            self::AdjustmentCredit,
            self::AdjustmentDebit => 'tune',
        };
    }
}
