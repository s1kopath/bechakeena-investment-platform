<?php

namespace App\Enums;

enum KycDocumentType: string
{
    case Nid = 'nid';
    case Passport = 'passport';

    public function label(): string
    {
        return match ($this) {
            self::Nid => 'National ID',
            self::Passport => 'Passport',
        };
    }
}
