<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Scheduled => 'Agendada',
            self::Sending => 'Enviando',
            self::Paused => 'Pausada',
            self::Completed => 'Concluida',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Scheduled => 'info',
            self::Sending => 'warning',
            self::Paused => 'neutral',
            self::Completed => 'success',
            self::Cancelled => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'edit_note',
            self::Scheduled => 'schedule',
            self::Sending => 'send',
            self::Paused => 'pause_circle',
            self::Completed => 'check_circle',
            self::Cancelled => 'cancel',
        };
    }
}
