<?php

namespace App\Models;

use App\Enums\CryptoCustodyType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoAccount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'crypto_institution_id',
        'bet_user_id',
        'name',
        'account_identifier',
        'custody_type',
        'initial_balance_brl',
        'current_balance_brl',
        'is_active',
        'last_checked_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'custody_type' => CryptoCustodyType::class,
            'initial_balance_brl' => 'decimal:2',
            'current_balance_brl' => 'decimal:2',
            'is_active' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(CryptoInstitution::class, 'crypto_institution_id');
    }

    public function betUser(): BelongsTo
    {
        return $this->belongsTo(BetUser::class);
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
