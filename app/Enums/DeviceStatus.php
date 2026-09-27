<?php

namespace App\Enums;

enum DeviceStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case MAINTENANCE = 'MAINTENANCE';
    case RETIRED = 'RETIRED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::MAINTENANCE => 'Pemeliharaan',
            self::RETIRED => 'Pensiun/Afkir',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::INACTIVE => 'bg-slate-100 text-slate-800 border-slate-300',
            self::MAINTENANCE => 'bg-amber-100 text-amber-800 border-amber-300',
            self::RETIRED => 'bg-rose-100 text-rose-800 border-rose-300',
        };
    }
}
