<?php

namespace App\Enums;

enum InspectionPeriodStatus: string
{
    case DRAFT = 'DRAFT';
    case ACTIVE = 'ACTIVE';
    case CLOSED = 'CLOSED';
    case LOCKED = 'LOCKED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Aktif',
            self::CLOSED => 'Ditutup',
            self::LOCKED => 'Dikunci',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-800 border-slate-300',
            self::ACTIVE => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::CLOSED => 'bg-amber-100 text-amber-800 border-amber-300',
            self::LOCKED => 'bg-gray-800 text-gray-100 border-gray-700',
        };
    }
}
