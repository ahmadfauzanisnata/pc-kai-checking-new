<?php

namespace App\Enums;

enum NoteType: string
{
    case GENERAL = 'GENERAL';
    case FINDING = 'FINDING';
    case ADMIN_REVISION = 'ADMIN_REVISION';
    case SUPER_ADMIN_REVISION = 'SUPER_ADMIN_REVISION';
    case APPROVAL = 'APPROVAL';
    case UNLOCK = 'UNLOCK';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'Catatan Umum',
            self::FINDING => 'Catatan Temuan',
            self::ADMIN_REVISION => 'Revisi Admin',
            self::SUPER_ADMIN_REVISION => 'Revisi Super Admin',
            self::APPROVAL => 'Catatan Persetujuan',
            self::UNLOCK => 'Alasan Buka Kunci',
        };
    }
}
