<?php

namespace App\Enums;

enum BetVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case NotRequired = 'not_required';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Verified => 'Verificada',
            self::Rejected => 'Rejeitada',
            self::NotRequired => 'Nao exige',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::Verified => 'success',
            self::Rejected => 'error',
            self::NotRequired => 'neutral',
        };
    }
}
