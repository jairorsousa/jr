<?php

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Investment = 'investment';
    case Wallet = 'wallet';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Conta Corrente',
            self::Savings => 'Poupanca',
            self::Investment => 'Investimento',
            self::Wallet => 'Carteira',
            self::Other => 'Outro',
        };
    }
}
