<?php

namespace App\Enums;

enum ApprovalDecision: string
{
    case SUBMITTED = 'SUBMITTED';
    case VERIFIED = 'VERIFIED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case ACKNOWLEDGED = 'ACKNOWLEDGED';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Diajukan',
            self::VERIFIED => 'Diverifikasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak / Dikembalikan',
            self::ACKNOWLEDGED => 'Mengetahui',
        };
    }
}
