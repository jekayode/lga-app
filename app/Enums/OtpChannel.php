<?php

namespace App\Enums;

enum OtpChannel: string
{
    case Sms = 'sms';
    case Email = 'email';
    case WhatsApp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::Sms => 'SMS',
            self::Email => 'Email',
            self::WhatsApp => 'WhatsApp',
        };
    }
}
