<?php

namespace App\Models;

use App\Enums\DealStage;
use App\Enums\DealStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'contact_id',
        'product_id',
        'stage',
        'status',
        'value',
        'expected_close_date',
        'closed_at',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'stage' => DealStage::class,
            'status' => DealStatus::class,
            'value' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class);
    }

    public function whatsappConversations(): HasMany
    {
        return $this->hasMany(WhatsAppConversation::class);
    }

    public function isOpen(): bool
    {
        return $this->status === DealStatus::Open;
    }
}
