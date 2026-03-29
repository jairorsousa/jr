<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Receita',
            self::Expense => 'Despesa',
            self::Transfer => 'Transferencia',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Income => 'up',
            self::Expense => 'down',
            self::Transfer => 'info',
        };
    }
}
