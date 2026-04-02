<?php

namespace App\Enums;

enum MessageStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Sent => 'Enviado',
            self::Delivered => 'Entregue',
            self::Read => 'Lido',
            self::Failed => 'Falhou',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'schedule',
            self::Sent => 'check',
            self::Delivered => 'done_all',
            self::Read => 'done_all',
            self::Failed => 'error',
        };
    }
}
