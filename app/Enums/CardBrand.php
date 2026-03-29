<?php

namespace App\Enums;

enum CardBrand: string
{
    case Visa = 'visa';
    case Mastercard = 'mastercard';
    case Elo = 'elo';
    case Amex = 'amex';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Visa => 'Visa',
            self::Mastercard => 'Mastercard',
            self::Elo => 'Elo',
            self::Amex => 'American Express',
            self::Other => 'Outro',
        };
    }
}
