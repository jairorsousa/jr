<?php

namespace App\Enums;

enum ActivityType: string
{
    case Note = 'note';
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case StageChange = 'stage_change';
    case WhatsApp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::Note => 'Nota',
            self::Call => 'Ligacao',
            self::Email => 'E-mail',
            self::Meeting => 'Reuniao',
            self::StageChange => 'Mudanca de Etapa',
            self::WhatsApp => 'WhatsApp',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Note => 'neutral',
            self::Call => 'info',
            self::Email => 'info',
            self::Meeting => 'primary',
            self::StageChange => 'neutral',
            self::WhatsApp => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Note => 'sticky_note_2',
            self::Call => 'phone',
            self::Email => 'email',
            self::Meeting => 'groups',
            self::StageChange => 'swap_horiz',
            self::WhatsApp => 'chat',
        };
    }
}
