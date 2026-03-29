<?php

namespace App\Models;

use App\Enums\InvestmentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'broker',
        'invested_amount',
        'current_amount',
        'quantity',
        'purchase_date',
        'maturity_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => InvestmentType::class,
            'invested_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'quantity' => 'decimal:8',
            'purchase_date' => 'date',
            'maturity_date' => 'date',
        ];
    }

    public function profitAmount(): float
    {
        return (float) $this->current_amount - (float) $this->invested_amount;
    }

    public function profitPercentage(): float
    {
        if ((float) $this->invested_amount === 0.0) {
            return 0;
        }

        return (($this->current_amount - $this->invested_amount) / $this->invested_amount) * 100;
    }
}
