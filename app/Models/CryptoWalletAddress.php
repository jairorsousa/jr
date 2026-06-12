<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoWalletAddress extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'crypto_account_id',
        'crypto_asset_id',
        'crypto_network_id',
        'address',
        'label',
        'is_deposit_address',
        'is_withdrawal_address',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_deposit_address' => 'boolean',
            'is_withdrawal_address' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function cryptoAccount(): BelongsTo
    {
        return $this->belongsTo(CryptoAccount::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class, 'crypto_asset_id');
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(CryptoNetwork::class, 'crypto_network_id');
    }
}
