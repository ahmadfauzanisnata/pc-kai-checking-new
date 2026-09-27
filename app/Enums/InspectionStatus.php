<?php

namespace App\Enums;

enum InspectionStatus: string
{
    case DRAFT = 'DRAFT';
    case ASSIGNED = 'ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case PENDING_ADMIN_VERIFICATION = 'PENDING_ADMIN_VERIFICATION';
    case REVISION_REQUIRED = 'REVISION_REQUIRED';
    case RESUBMITTED = 'RESUBMITTED';
    case ADMIN_VERIFIED = 'ADMIN_VERIFIED';
    case PENDING_SUPER_ADMIN_APPROVAL = 'PENDING_SUPER_ADMIN_APPROVAL';
    case ADMIN_REVISION_REQUIRED = 'ADMIN_REVISION_REQUIRED';
    case APPROVED = 'APPROVED';
    case LOCKED = 'LOCKED';
    case ARCHIVED = 'ARCHIVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ASSIGNED => 'Ditugaskan',
            self::IN_PROGRESS => 'Sedang Diperiksa',
            self::PENDING_ADMIN_VERIFICATION => 'Menunggu Verifikasi Admin',
            self::REVISION_REQUIRED => 'Perlu Perbaikan Pemeriksa',
            self::RESUBMITTED => 'Dikirim Ulang',
            self::ADMIN_VERIFIED => 'Terverifikasi Admin',
            self::PENDING_SUPER_ADMIN_APPROVAL => 'Menunggu Persetujuan Super Admin',
            self::ADMIN_REVISION_REQUIRED => 'Perlu Perbaikan Admin',
            self::APPROVED => 'Disetujui',
            self::LOCKED => 'Dikunci',
            self::ARCHIVED => 'Diarsipkan',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
            self::ASSIGNED => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-900/40 dark:text-sky-300',
            self::IN_PROGRESS => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/40 dark:text-blue-300',
            self::PENDING_ADMIN_VERIFICATION => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300',
            self::REVISION_REQUIRED => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300',
            self::RESUBMITTED => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-900/40 dark:text-sky-300',
            self::ADMIN_VERIFIED => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300',
            self::PENDING_SUPER_ADMIN_APPROVAL => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300',
            self::ADMIN_REVISION_REQUIRED => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300',
            self::APPROVED => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300',
            self::LOCKED => 'bg-gray-800 text-gray-100 border-gray-700 dark:bg-gray-950 dark:text-gray-200',
            self::ARCHIVED => 'bg-slate-200 text-slate-700 border-slate-400 dark:bg-slate-800 dark:text-slate-300',
            self::CANCELLED => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/40 dark:text-red-300',
        };
    }
}
