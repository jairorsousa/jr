<?php

namespace App\Enums;

enum BetSettlementMethod: string
{
    case Bank = 'bank';
    case Crypto = 'crypto';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Bank => 'Banco / Financeiro',
            self::Crypto => 'Cripto',
            self::Manual => 'Manual',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Bank => 'info',
            self::Crypto => 'primary',
            self::Manual => 'neutral',
        };
    }
}
