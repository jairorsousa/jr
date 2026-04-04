<?php

namespace App\Events;

use App\Models\WhatsAppInstance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppConnectionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $instance;

    public function __construct(
        public WhatsAppInstance $whatsappInstance,
    ) {
        $this->instance = [
            'id' => $whatsappInstance->id,
            'instance_name' => $whatsappInstance->instance_name,
            'status' => $whatsappInstance->status->value,
            'status_label' => $whatsappInstance->status->label(),
            'status_color' => $whatsappInstance->status->color(),
            'qrcode' => $whatsappInstance->qrcode,
            'connected_at' => $whatsappInstance->connected_at?->toISOString(),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('whatsapp.instance.' . $this->whatsappInstance->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'connection.updated';
    }
}
