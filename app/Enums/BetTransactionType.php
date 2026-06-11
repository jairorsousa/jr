<?php

namespace App\Enums;

enum BetTransactionType: string
{
    case Deposit = 'deposit';
    case BetPayout = 'bet_payout';
    case BonusCredit = 'bonus_credit';
    case BonusRelease = 'bonus_release';
    case Cashback = 'cashback';
    case AdjustmentCredit = 'adjustment_credit';
    case TransferIn = 'transfer_in';
    case Withdrawal = 'withdrawal';
    case BetStake = 'bet_stake';
    case Fee = 'fee';
    case BonusExpired = 'bonus_expired';
    case AdjustmentDebit = 'adjustment_debit';
    case TransferOut = 'transfer_out';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Deposito',
            self::BetPayout => 'Retorno de aposta',
            self::BonusCredit => 'Bonus creditado',
            self::BonusRelease => 'Bonus liberado',
            self::Cashback => 'Cashback',
            self::AdjustmentCredit => 'Ajuste de credito',
            self::TransferIn => 'Transferencia recebida',
            self::Withdrawal => 'Saque',
            self::BetStake => 'Aposta realizada',
            self::Fee => 'Taxa',
            self::BonusExpired => 'Bonus expirado',
            self::AdjustmentDebit => 'Ajuste de debito',
            self::TransferOut => 'Transferencia enviada',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::Deposit,
            self::BetPayout,
            self::BonusCredit,
            self::BonusRelease,
            self::Cashback,
            self::AdjustmentCredit,
            self::TransferIn => 'in',
            self::Withdrawal,
            self::BetStake,
            self::Fee,
            self::BonusExpired,
            self::AdjustmentDebit,
            self::TransferOut => 'out',
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
        return in_array($this, [self::Deposit, self::Withdrawal], true);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Deposit,
            self::BetPayout,
            self::BonusCredit,
            self::BonusRelease,
            self::Cashback,
            self::AdjustmentCredit,
            self::TransferIn => 'success',
            self::Withdrawal,
            self::BetStake,
            self::Fee,
            self::BonusExpired,
            self::AdjustmentDebit,
            self::TransferOut => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Deposit => 'add_card',
            self::Withdrawal => 'payments',
            self::BetStake => 'sports_soccer',
            self::BetPayout => 'emoji_events',
            self::BonusCredit,
            self::BonusRelease,
            self::BonusExpired => 'redeem',
            self::Cashback => 'currency_exchange',
            self::Fee => 'receipt_long',
            self::AdjustmentCredit,
            self::AdjustmentDebit => 'tune',
            self::TransferIn,
            self::TransferOut => 'swap_horiz',
        };
    }
}
