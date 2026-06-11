<?php

namespace App\Models;

use App\Enums\BetAccountStatus;
use App\Enums\BetVerificationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BetAccount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'betting_house_id',
        'bet_user_id',
        'name',
        'username',
        'account_code',
        'status',
        'verification_status',
        'initial_balance',
        'current_balance',
        'bonus_balance',
        'withdrawable_balance',
        'daily_deposit_limit',
        'monthly_deposit_limit',
        'opened_at',
        'last_checked_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BetAccountStatus::class,
            'verification_status' => BetVerificationStatus::class,
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'bonus_balance' => 'decimal:2',
            'withdrawable_balance' => 'decimal:2',
            'daily_deposit_limit' => 'decimal:2',
            'monthly_deposit_limit' => 'decimal:2',
            'opened_at' => 'date',
            'last_checked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function bettingHouse(): BelongsTo
    {
        return $this->belongsTo(BettingHouse::class);
    }

    public function betUser(): BelongsTo
    {
        return $this->belongsTo(BetUser::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BetTransaction::class);
    }

    public function isOperational(): bool
    {
        return $this->is_active && $this->status !== BetAccountStatus::Closed;
    }
}
