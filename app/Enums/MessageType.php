<?php

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';
    case Image = 'image';
    case Audio = 'audio';
    case Video = 'video';
    case Document = 'document';
    case Sticker = 'sticker';
    case Location = 'location';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texto',
            self::Image => 'Imagem',
            self::Audio => 'Audio',
            self::Video => 'Video',
            self::Document => 'Documento',
            self::Sticker => 'Sticker',
            self::Location => 'Localizacao',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Text => 'chat',
            self::Image => 'image',
            self::Audio => 'mic',
            self::Video => 'videocam',
            self::Document => 'description',
            self::Sticker => 'emoji_emotions',
            self::Location => 'location_on',
        };
    }
}
