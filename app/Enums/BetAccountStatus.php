<?php

namespace App\Enums;

enum BetAccountStatus: string
{
    case Active = 'active';
    case Limited = 'limited';
    case Suspended = 'suspended';
    case Blocked = 'blocked';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Limited => 'Limitada',
            self::Suspended => 'Suspensa',
            self::Blocked => 'Bloqueada',
            self::Closed => 'Encerrada',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Limited => 'warning',
            self::Suspended => 'neutral',
            self::Blocked => 'error',
            self::Closed => 'neutral',
        };
    }
}
