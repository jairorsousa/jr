<?php

namespace App\Enums;

enum InvestmentType: string
{
    case Crypto = 'crypto';
    case FixedIncome = 'fixed_income';
    case Stocks = 'stocks';
    case Funds = 'funds';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Crypto => 'Criptomoeda',
            self::FixedIncome => 'Renda Fixa',
            self::Stocks => 'Acoes',
            self::Funds => 'Fundos',
            self::Other => 'Outro',
        };
    }
}
