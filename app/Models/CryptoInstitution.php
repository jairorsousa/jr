<?php

namespace App\Models;

use App\Enums\CryptoInstitutionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoInstitution extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'website',
        'color',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => CryptoInstitutionType::class,
            'is_active' => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(CryptoAccount::class);
    }
}
