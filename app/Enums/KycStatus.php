<?php

namespace App\Enums;

enum KycStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Not started',
            self::Submitted => 'Under review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
