<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoAsset extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'symbol',
        'name',
        'decimals',
        'is_stablecoin',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'decimals' => 'integer',
            'is_stablecoin' => 'boolean',
            'is_active' => 'boolean',
        ];
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
