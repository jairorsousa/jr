<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BetUser extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'nickname',
        'document',
        'email',
        'phone',
        'pix_key',
        'color',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(BetAccount::class);
    }

    public function displayName(): string
    {
        return $this->nickname ? "{$this->name} ({$this->nickname})" : $this->name;
    }
}
