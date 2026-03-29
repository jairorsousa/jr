<?php

namespace App\Enums;

enum DealStage: string
{
    case Lead = 'lead';
    case ContactMade = 'contact_made';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::ContactMade => 'Contato Feito',
            self::Proposal => 'Proposta',
            self::Negotiation => 'Negociacao',
            self::Won => 'Ganho',
            self::Lost => 'Perdido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lead => 'neutral',
            self::ContactMade => 'info',
            self::Proposal => 'primary',
            self::Negotiation => 'primary',
            self::Won => 'success',
            self::Lost => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Lead => 'person_add',
            self::ContactMade => 'phone_in_talk',
            self::Proposal => 'description',
            self::Negotiation => 'handshake',
            self::Won => 'emoji_events',
            self::Lost => 'block',
        };
    }

    /** Active pipeline stages (excluding terminal states) */
    public static function pipelineStages(): array
    {
        return [self::Lead, self::ContactMade, self::Proposal, self::Negotiation];
    }
}
