<?php

namespace App\Models;

use App\Enums\RecurrenceType;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'account_id',
        'category_id',
        'credit_card_id',
        'credit_card_invoice_id',
        'type',
        'description',
        'fitid',
        'amount',
        'date',
        'due_date',
        'paid_at',
        'is_paid',
        'is_recurring',
        'recurrence_type',
        'recurrence_end',
        'installment_number',
        'installment_total',
        'notes',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'is_paid' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_type' => RecurrenceType::class,
            'recurrence_end' => 'date',
            'installment_number' => 'integer',
            'installment_total' => 'integer',
            'tags' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoice::class, 'credit_card_invoice_id');
    }
}
