<?php

namespace App\Enums;

enum TemplateCategory: string
{
    case General = 'general';
    case Marketing = 'marketing';
    case Sales = 'sales';
    case Support = 'support';
    case FollowUp = 'follow_up';
    case Greeting = 'greeting';
    case Reminder = 'reminder';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Geral',
            self::Marketing => 'Marketing',
            self::Sales => 'Vendas',
            self::Support => 'Suporte',
            self::FollowUp => 'Follow-up',
            self::Greeting => 'Saudacao',
            self::Reminder => 'Lembrete',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::General => 'neutral',
            self::Marketing => 'primary',
            self::Sales => 'success',
            self::Support => 'info',
            self::FollowUp => 'warning',
            self::Greeting => 'primary',
            self::Reminder => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::General => 'article',
            self::Marketing => 'campaign',
            self::Sales => 'sell',
            self::Support => 'support_agent',
            self::FollowUp => 'reply',
            self::Greeting => 'waving_hand',
            self::Reminder => 'notifications',
        };
    }
}
