<?php

namespace App\Models;

use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BetTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'bet_account_id',
        'finance_transaction_id',
        'type',
        'status',
        'amount',
        'balance_before',
        'balance_after',
        'occurred_at',
        'confirmed_at',
        'description',
        'external_reference',
        'event_name',
        'market_name',
        'selection_name',
        'odd',
        'strategy',
        'tags',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => BetTransactionType::class,
            'status' => BetTransactionStatus::class,
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'occurred_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'odd' => 'decimal:4',
            'tags' => 'array',
            'metadata' => 'array',
        ];
    }

    public function betAccount(): BelongsTo
    {
        return $this->belongsTo(BetAccount::class);
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'finance_transaction_id');
    }

    public function isConfirmed(): bool
    {
        return $this->status === BetTransactionStatus::Confirmed;
    }

    public function signedAmount(): float
    {
        $amount = (float) $this->amount;

        return $this->type->isIn() ? $amount : -$amount;
    }
}
