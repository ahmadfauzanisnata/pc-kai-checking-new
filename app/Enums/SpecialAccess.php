<?php

namespace App\Enums;

enum SpecialAccess: string
{
    case ADMIN = 'ADMIN';
    case USER = 'USER';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }
}
