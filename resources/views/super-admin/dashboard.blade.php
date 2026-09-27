@extends('layouts.app')

@section('title', 'Dashboard Super Admin - KAI PC Checking')
@section('page_title', 'Dashboard Eksekutif')
@section('page_subtitle', 'Pemantauan kepatuhan keamanan dan status kelayakan perangkat PC/Laptop PT KAI se-Indonesia')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-kai-navy-dark via-kai-navy to-kai-navy-light rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-kai-orange/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-[11px] font-semibold text-kai-orange-light mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Sistem Aktif & Terhubung
                </div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-white">Dashboard Eksekutif Super Admin</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Pusat komando pemantauan pemeriksaan kelayakan PC Desktop & Laptop di seluruh Daop, Divre, dan Kantor Pusat PT Kereta Api Indonesia (Persero).
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-xs transition inline-flex items-center gap-2 border border-white/10">
                    <i class="fa-solid fa-file-invoice text-xs"></i> Laporan & Audit
                </a>
                <a href="{{ route('super-admin.inspections.pending') }}" class="btn-kai px-4 py-2.5 rounded-xl text-white text-xs font-semibold transition inline-flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-clipboard-check text-xs"></i> Antrean Approval ({{ $stats['pending_approval'] }})
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Menunggu Approval -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-kai-orange"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Menunggu Approval</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-clock"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['pending_approval'] }}</span>
                <a href="{{ route('super-admin.inspections.pending') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                    Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="mt-2 text-xs text-amber-700 font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Perlu persetujuan akhir Super Admin
            </div>
        </div>

        <!-- Card 2: Telah Disetujui -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Telah Disetujui</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['approved'] }}</span>
                <a href="{{ route('super-admin.inspections.approved') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                    Lihat <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="mt-2 text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Pemeriksaan sah & memenuhi standar
            </div>
        </div>

        <!-- Card 3: Total Perangkat Terdata -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-kai-navy"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Perangkat Terdata</span>
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-kai-blue flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-laptop"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_devices'] }}</span>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">{{ $stats['total_business_areas'] }} Area</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Tersebar di seluruh unit KAI
            </div>
        </div>

        <!-- Card 4: Total Pengguna -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-600"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Pengguna</span>
                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_users'] }}</span>
                <a href="{{ route('super-admin.users.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                    Kelola <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Admin & Pemeriksa Aktif
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Chart -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-kai-orange text-sm"></i>
                        Distribusi Status Pemeriksaan
                    </h2>
                    <p class="text-xs text-slate-400">Proporsi status alur pemeriksaan di seluruh sistem</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">Realtime</span>
            </div>
            <div class="h-64 relative">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Business Area Devices Chart -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-kai-blue text-sm"></i>
                        Perangkat per Business Area
                    </h2>
                    <p class="text-xs text-slate-400">Distribusi perangkat terdaftar (DAOP / Divre / Kantor Pusat)</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">Inventaris</span>
            </div>
            <div class="h-64 relative">
                <canvas id="baChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row: Pending Approvals & Activity Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Approvals (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-signature text-kai-orange text-sm"></i>
                        Pemeriksaan Menunggu Persetujuan Anda
                    </h2>
                    <p class="text-xs text-slate-500">Telah diverifikasi Admin Wilayah dan siap disahkan Super Admin.</p>
                </div>
                <a href="{{ route('super-admin.inspections.pending') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition">
                    Semua ({{ $stats['pending_approval'] }}) &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                            <th class="py-3 px-4">No. Referensi</th>
                            <th class="py-3 px-4">Pegawai & Unit</th>
                            <th class="py-3 px-4">Perangkat</th>
                            <th class="py-3 px-4">Area</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingInspections as $insp)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-mono font-bold text-kai-navy whitespace-nowrap">
                                {{ $insp->reference_number }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $insp->employee?->name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $insp->employee?->unit?->name }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $insp->device?->hostname }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $insp->device?->asset_number }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-bold">
                                    {{ $insp->inspectionPeriod?->businessArea?->code }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('super-admin.inspections.show-approval', $insp) }}" class="btn-kai inline-flex items-center px-3 py-1.5 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                    Review &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mb-2">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-600">Semua pemeriksaan telah disahkan.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pemeriksaan yang menunggu persetujuan Super Admin saat ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity Logs (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-kai-navy text-sm"></i>
                        Audit Trail Terkini
                    </h2>
                    <p class="text-xs text-slate-400">Aktivitas sistem terbaru</p>
                </div>
                <a href="{{ route('super-admin.activity-logs.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition">Semua &rarr;</a>
            </div>
            <div class="p-4 flex-1 overflow-y-auto space-y-3 max-h-96 custom-scrollbar">
                @forelse($recentActivities as $log)
                <div class="flex items-start gap-3 text-xs border-b border-slate-50 pb-2.5 last:border-0 last:pb-0">
                    <span class="w-2 h-2 mt-1.5 rounded-full bg-kai-orange shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-slate-800 font-medium truncate">{{ $log->description }}</p>
                        <div class="flex items-center justify-between text-slate-400 text-[11px] mt-0.5">
                            <span class="font-medium text-slate-600">{{ $log->user?->name ?? 'Sistem' }}</span>
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-shield-cat text-2xl mb-1 text-slate-300"></i>
                    <p>Belum ada aktivitas audit tercatat.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusLabels) !!},
                datasets: [{
                    data: {!! json_encode($statusData) !!},
                    backgroundColor: [
                        '#1B3A5C', // KAI Navy
                        '#F47920', // KAI Orange
                        '#10B981', // Emerald
                        '#0284C7', // Sky
                        '#F59E0B', // Amber
                        '#EF4444', // Rose
                        '#8B5CF6', // Purple
                        '#64748B', // Slate
                        '#0D9488', // Teal
                        '#3B82F6'  // Blue
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { family: 'Inter', size: 11 } }
                    }
                }
            }
        });
    }

    // Business Area Chart
    const baCtx = document.getElementById('baChart');
    if (baCtx) {
        new Chart(baCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($baLabels) !!},
                datasets: [{
                    label: 'Perangkat',
                    data: {!! json_encode($baDevices) !!},
                    backgroundColor: '#1B3A5C',
                    hoverBackgroundColor: '#F47920',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, font: { family: 'Inter' } }, grid: { color: '#F1F5F9' } },
                    x: { ticks: { font: { family: 'Inter', size: 11 } }, grid: { display: false } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
