<?php

namespace App\Events;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewWhatsAppMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $message;
    public array $conversation;

    public function __construct(
        public WhatsAppMessage $whatsappMessage,
        public WhatsAppConversation $whatsappConversation,
    ) {
        $this->message = [
            'id' => $whatsappMessage->id,
            'conversation_id' => $whatsappMessage->conversation_id,
            'message_id' => $whatsappMessage->message_id,
            'type' => $whatsappMessage->type->value ?? $whatsappMessage->type,
            'body' => $whatsappMessage->body,
            'media_url' => $whatsappMessage->media_url,
            'media_filename' => $whatsappMessage->media_filename,
            'from_me' => $whatsappMessage->from_me,
            'status' => $whatsappMessage->status->value ?? $whatsappMessage->status,
            'message_at' => $whatsappMessage->message_at->toISOString(),
            'time' => $whatsappMessage->message_at->format('H:i'),
        ];

        $this->conversation = [
            'id' => $whatsappConversation->id,
            'instance_id' => $whatsappConversation->instance_id,
            'contact_name' => $whatsappConversation->contact_name,
            'contact_phone' => $whatsappConversation->contact_phone,
            'last_message' => $whatsappConversation->last_message,
            'last_message_at' => $whatsappConversation->last_message_at?->toISOString(),
            'unread_count' => $whatsappConversation->unread_count,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('whatsapp.chat.' . $this->whatsappConversation->id),
            new Channel('whatsapp.instance.' . $this->whatsappConversation->instance_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.new';
    }
}
