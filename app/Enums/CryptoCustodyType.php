<?php

namespace App\Enums;

enum CryptoCustodyType: string
{
    case Exchange = 'exchange';
    case SelfCustody = 'self_custody';
    case Shared = 'shared';

    public function label(): string
    {
        return match ($this) {
            self::Exchange => 'Corretora',
            self::SelfCustody => 'Carteira propria',
            self::Shared => 'Compartilhada',
        };
    }
}
