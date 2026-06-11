<?php

namespace App\Enums;

enum BetTransactionStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Confirmed => 'Confirmada',
            self::Cancelled => 'Cancelada',
            self::Failed => 'Falhou',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::Confirmed => 'success',
            self::Cancelled => 'neutral',
            self::Failed => 'error',
        };
    }
}
