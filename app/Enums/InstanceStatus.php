<?php

namespace App\Enums;

enum InstanceStatus: string
{
    case Connected = 'connected';
    case Disconnected = 'disconnected';
    case Connecting = 'connecting';

    public function label(): string
    {
        return match ($this) {
            self::Connected => 'Conectado',
            self::Disconnected => 'Desconectado',
            self::Connecting => 'Conectando',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Connected => 'success',
            self::Disconnected => 'error',
            self::Connecting => 'warning',
        };
    }
}
