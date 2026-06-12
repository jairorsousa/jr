<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoNetwork extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'native_asset_id',
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function nativeAsset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class, 'native_asset_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CryptoTransaction::class);
    }

    public function walletAddresses(): HasMany
    {
        return $this->hasMany(CryptoWalletAddress::class);
    }
}
