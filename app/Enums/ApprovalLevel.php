<?php

namespace App\Enums;

enum ApprovalLevel: string
{
    case INSPECTOR = 'INSPECTOR';
    case EMPLOYEE = 'EMPLOYEE';
    case ADMIN = 'ADMIN';
    case SUPER_ADMIN = 'SUPER_ADMIN';

    public function label(): string
    {
        return match ($this) {
            self::INSPECTOR => 'Pemeriksa',
            self::EMPLOYEE => 'Pegawai Terkait',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }
}
