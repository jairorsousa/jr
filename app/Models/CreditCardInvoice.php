<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCardInvoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'credit_card_id',
        'reference_month',
        'total_amount',
        'due_date',
        'paid_at',
        'is_paid',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'reference_month' => 'date',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'is_paid' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'credit_card_invoice_id');
    }
}
