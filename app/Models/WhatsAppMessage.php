<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'type',
        'body',
        'media_url',
        'media_mimetype',
        'media_filename',
        'from_me',
        'status',
        'raw_data',
        'message_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
            'status' => MessageStatus::class,
            'from_me' => 'boolean',
            'raw_data' => 'array',
            'message_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function isText(): bool
    {
        return $this->type === MessageType::Text;
    }

    public function isMedia(): bool
    {
        return in_array($this->type, [MessageType::Image, MessageType::Audio, MessageType::Video, MessageType::Document]);
    }
}
