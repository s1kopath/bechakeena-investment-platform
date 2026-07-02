<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Flagged = 'flagged';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Flagged => 'Flagged',
            self::Deactivated => 'Deactivated',
        };
    }

    /**
     * Whether an account in this state may authenticate and use the platform.
     */
    public function canAccess(): bool
    {
        return $this === self::Active;
    }
}
