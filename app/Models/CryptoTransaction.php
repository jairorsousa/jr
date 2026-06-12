<?php

namespace App\Models;

use App\Enums\CryptoTransactionStatus;
use App\Enums\CryptoTransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'crypto_account_id',
        'finance_transaction_id',
        'bet_transaction_id',
        'crypto_asset_id',
        'crypto_network_id',
        'type',
        'status',
        'amount_brl',
        'balance_before_brl',
        'balance_after_brl',
        'crypto_amount',
        'exchange_rate_brl',
        'fee_brl',
        'fee_crypto_amount',
        'tx_hash',
        'from_address',
        'to_address',
        'occurred_at',
        'confirmed_at',
        'description',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => CryptoTransactionType::class,
            'status' => CryptoTransactionStatus::class,
            'amount_brl' => 'decimal:2',
            'balance_before_brl' => 'decimal:2',
            'balance_after_brl' => 'decimal:2',
            'crypto_amount' => 'decimal:10',
            'exchange_rate_brl' => 'decimal:8',
            'fee_brl' => 'decimal:2',
            'fee_crypto_amount' => 'decimal:10',
            'occurred_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function cryptoAccount(): BelongsTo
    {
        return $this->belongsTo(CryptoAccount::class);
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'finance_transaction_id');
    }

    public function betTransaction(): BelongsTo
    {
        return $this->belongsTo(BetTransaction::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class, 'crypto_asset_id');
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(CryptoNetwork::class, 'crypto_network_id');
    }

    public function isConfirmed(): bool
    {
        return $this->status === CryptoTransactionStatus::Confirmed;
    }

    public function signedAmountBrl(): float
    {
        if ($this->type->direction() === 'neutral') {
            return 0;
        }

        $amount = (float) $this->amount_brl + (float) $this->fee_brl;

        return $this->type->isIn() ? (float) $this->amount_brl : -$amount;
    }
}
