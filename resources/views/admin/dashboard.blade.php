@extends('layouts.app')

@section('title', 'Dashboard Admin Wilayah - KAI PC Checking')
@section('page_title', 'Dashboard Admin Wilayah')
@section('page_subtitle', 'Pusat operasional dan verifikasi pemeriksaan perangkat di wilayah kerja Anda')

@section('content')
<div class="space-y-6">
    <!-- Header Area Info & Action -->
    <div class="bg-gradient-to-r from-kai-navy-dark via-kai-navy to-kai-navy-light rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-kai-orange/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-[11px] font-semibold text-kai-orange-light mb-2">
                    <i class="fa-solid fa-location-dot"></i> Wilayah Kerja: {{ auth()->user()->businessArea?->code ?? '-' }}
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">
                    {{ auth()->user()->businessArea?->name ?? 'Semua Wilayah Kerja' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                    Kelola penugasan pemeriksaan PC/Laptop tim pemeriksa, pantau kelayakan perangkat, dan lakukan verifikasi kepatuhan TI.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.assignments.create') }}" class="btn-kai px-4 py-2.5 rounded-xl text-white text-xs font-semibold transition inline-flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-plus-circle text-xs"></i> Buat Penugasan Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Active Period Banner -->
    @if($stats['active_period'])
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-kai-blue/10 text-kai-blue flex items-center justify-center text-xl shrink-0 mt-0.5">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                            Periode Berjalan
                        </span>
                        <span class="text-xs text-slate-400">
                            {{ $stats['active_period']->start_date->format('d M Y') }} &ndash; {{ $stats['active_period']->end_date->format('d M Y') }}
                        </span>
                    </div>
                    <h2 class="text-base font-bold text-slate-900 mt-1">{{ $stats['active_period']->name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $stats['active_period']->description ?? 'Periode pemeriksaan rutin berkala perangkat TI' }}</p>
                </div>
            </div>

            @if($periodProgress)
            <div class="w-full md:w-80 bg-slate-50 p-4 rounded-xl border border-slate-200/70">
                <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1.5">
                    <span>Progress Pemeriksaan</span>
                    <span class="font-bold text-kai-orange">{{ $periodProgress['completed'] }} / {{ $periodProgress['total'] }} ({{ $periodProgress['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-kai-orange to-kai-orange-dark h-2 rounded-full transition-all duration-500" style="width: {{ $periodProgress['percentage'] }}%"></div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-900">Belum Ada Periode Pemeriksaan Aktif</h3>
                <p class="text-xs text-amber-700 mt-0.5">Silakan buat atau aktifkan periode baru untuk dapat membuat penugasan pemeriksaan perangkat.</p>
            </div>
        </div>
        <a href="{{ route('admin.inspection-periods.index') }}" class="btn-kai px-4 py-2 rounded-xl text-white text-xs font-semibold transition shrink-0 inline-flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i> Buat Periode
        </a>
    </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Menunggu Verifikasi -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-kai-orange"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Menunggu Verifikasi</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-clipboard-check"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['pending_verifications'] }}</span>
                <a href="{{ route('admin.verifications.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                    Periksa <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="mt-2 text-xs text-amber-700 font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Hasil checklist dari pemeriksa
            </div>
        </div>

        <!-- Card 2: Sedang Revisi -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-600"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Sedang Revisi</span>
                <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-rotate-left"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['revision_count'] }}</span>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">Pemeriksa</span>
            </div>
            <div class="mt-2 text-xs text-rose-700 font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Dikembalikan untuk dilengkapi
            </div>
        </div>

        <!-- Card 3: Total Perangkat Area -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-kai-navy"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Perangkat Area</span>
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-kai-blue flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-laptop"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_devices'] }}</span>
                <a href="{{ route('admin.devices.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                    Kelola <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> PC & Laptop di Wilayah ini
            </div>
        </div>

        <!-- Card 4: Total Pegawai Area -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Pegawai Area</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_employees'] }}</span>
                <a href="{{ route('admin.employees.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                    Daftar <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Pengguna inventaris perangkat
            </div>
        </div>
    </div>

    <!-- Pending Verifications Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-inbox text-kai-orange text-sm"></i>
                    Pemeriksaan Masuk Memerlukan Verifikasi
                </h2>
                <p class="text-xs text-slate-500">Hasil inspeksi yang diserahkan Tim Pemeriksa dan menunggu verifikasi Anda.</p>
            </div>
            <a href="{{ route('admin.verifications.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                Lihat Semua Verifikasi <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3 px-4 sm:px-6">No. Referensi</th>
                        <th class="py-3 px-4">Pegawai / Pemakai</th>
                        <th class="py-3 px-4">Perangkat</th>
                        <th class="py-3 px-4">Pemeriksa</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingReviews as $rev)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-kai-navy whitespace-nowrap">
                            {{ $rev->reference_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-800">{{ $rev->employee?->name }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $rev->employee?->unit?->name }} (NIPP: {{ $rev->employee?->nipp }})</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $rev->device?->hostname }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $rev->device?->asset_number }}</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-medium text-slate-800">{{ $rev->inspector?->name }}</span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <x-status-badge :status="$rev->status" />
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                            <a href="{{ route('admin.verifications.show', $rev) }}" class="btn-kai inline-flex items-center px-3 py-1.5 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                Verifikasi &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mb-2">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <p class="text-xs font-medium text-slate-600">Semua pemeriksaan telah diverifikasi.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pemeriksaan yang membutuhkan verifikasi Admin saat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
