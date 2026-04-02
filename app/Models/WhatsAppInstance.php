<?php

namespace App\Models;

use App\Enums\InstanceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppInstance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'name',
        'instance_name',
        'phone',
        'status',
        'qrcode',
        'settings',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InstanceStatus::class,
            'settings' => 'array',
            'connected_at' => 'datetime',
        ];
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(WhatsAppConversation::class, 'instance_id');
    }

    public function isConnected(): bool
    {
        return $this->status === InstanceStatus::Connected;
    }
}
