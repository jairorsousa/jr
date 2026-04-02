<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppConversation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'instance_id',
        'contact_id',
        'remote_jid',
        'contact_name',
        'contact_phone',
        'profile_pic_url',
        'last_message',
        'last_message_at',
        'unread_count',
        'is_group',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_group' => 'boolean',
            'unread_count' => 'integer',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WhatsAppInstance::class, 'instance_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function displayName(): string
    {
        return $this->contact_name ?? $this->contact_phone;
    }

    public function initials(): string
    {
        $name = $this->displayName();
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}
