<?php

namespace App\Enums;

enum CryptoInstitutionType: string
{
    case Exchange = 'exchange';
    case Wallet = 'wallet';
    case Broker = 'broker';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Exchange => 'Corretora',
            self::Wallet => 'Carteira',
            self::Broker => 'Broker',
            self::Other => 'Outro',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Exchange => 'currency_exchange',
            self::Wallet => 'account_balance_wallet',
            self::Broker => 'storefront',
            self::Other => 'apps',
        };
    }
}
