<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BettingHouse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'website',
        'country',
        'logo_url',
        'color',
        'min_deposit',
        'min_withdrawal',
        'deposit_fee_percent',
        'withdrawal_fee_percent',
        'withdrawal_time_hours',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'min_deposit' => 'decimal:2',
            'min_withdrawal' => 'decimal:2',
            'deposit_fee_percent' => 'decimal:2',
            'withdrawal_fee_percent' => 'decimal:2',
            'withdrawal_time_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(BetAccount::class);
    }
}
