<?php

namespace App\Enums;

enum Status: string
{
    case PENDING = 'Pending';
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case CANCELLED = 'Cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}