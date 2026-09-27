<?php

namespace App\Enums;

enum AttachmentType: string
{
    case DEVICE_PHOTO = 'DEVICE_PHOTO';
    case SCREENSHOT = 'SCREENSHOT';
    case SUPPORTING_DOCUMENT = 'SUPPORTING_DOCUMENT';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::DEVICE_PHOTO => 'Foto Perangkat',
            self::SCREENSHOT => 'Tangkapan Layar (Screenshot)',
            self::SUPPORTING_DOCUMENT => 'Dokumen Pendukung',
            self::OTHER => 'Lainnya',
        };
    }
}
