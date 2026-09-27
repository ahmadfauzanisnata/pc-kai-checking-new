@extends('layouts.app')

@section('title', 'Dashboard Pemeriksa - KAI PC Checking')
@section('page_title', 'Dashboard Pemeriksa')
@section('page_subtitle', 'Ringkasan tugas pemeriksaan keamanan perangkat PC dan Laptop Anda')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-kai-navy-dark via-kai-navy to-kai-navy-light rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-kai-orange/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-[11px] font-semibold text-kai-orange-light mb-2">
                    <i class="fa-solid fa-user-check"></i> Petugas Pemeriksa Keamanan TI
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">
                    Selamat Datang, {{ auth()->user()->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                    Lakukan pemeriksaan fisik dan konfigurasi keamanan 12 kriteria standar TI pada PC Desktop dan Laptop pegawai.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('inspector.tasks.index') }}" class="btn-kai px-4 py-2.5 rounded-xl text-white text-xs font-semibold transition inline-flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-list-check text-xs"></i> Buka Daftar Tugas
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- New Tasks -->
        <a href="{{ route('inspector.tasks.index', ['status' => 'ASSIGNED']) }}"
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-sky-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tugas Baru</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['new_tasks'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Belum dimulai</p>
        </a>

        <!-- In Progress -->
        <a href="{{ route('inspector.tasks.index', ['status' => 'IN_PROGRESS']) }}"
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-kai-blue"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sedang Dikerjakan</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-kai-blue flex items-center justify-center text-sm group-hover:scale-110 transition">
                    <i class="fa-solid fa-spinner"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-kai-blue mt-2">{{ $stats['in_progress'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Draft tersimpan</p>
        </a>

        <!-- Revision Required -->
        <a href="{{ route('inspector.tasks.index', ['status' => 'REVISION_REQUIRED']) }}"
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-rose-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Perlu Perbaikan</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-rose-600 mt-2">{{ $stats['revision_required'] }}</div>
            <p class="text-[11px] text-rose-500 font-medium mt-1">Dikembalikan Admin</p>
        </a>

        <!-- Submitted -->
        <a href="{{ route('inspector.tasks.index', ['status' => 'submitted']) }}"
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sudah Dikirim</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['submitted'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Menunggu verifikasi</p>
        </a>

        <!-- Completed -->
        <a href="{{ route('inspector.history') }}"
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition stat-card group relative overflow-hidden col-span-2 lg:col-span-1">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Selesai</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-emerald-600 mt-2">{{ $stats['completed'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Disetujui / Selesai</p>
        </a>
    </div>

    <!-- Quick Action Banner for Revision -->
    @if ($stats['revision_required'] > 0)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-rose-900">Perhatian: Terdapat {{ $stats['revision_required'] }} Tugas yang Perlu Diperbaiki</h3>
                    <p class="text-xs text-rose-700 mt-0.5">Admin meminta klarifikasi atau perbaikan bukti pemeriksaan. Mohon segera ditindaklanjuti.</p>
                </div>
            </div>
            <a href="{{ route('inspector.tasks.index', ['status' => 'REVISION_REQUIRED']) }}"
                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition shrink-0 inline-flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-wrench text-xs"></i> Lihat Tugas Perbaikan
            </a>
        </div>
    @endif

    <!-- Recent Tasks Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-tasks text-kai-orange text-sm"></i>
                    Tugas Pemeriksaan Terbaru
                </h2>
                <p class="text-xs text-slate-500">Daftar tugas terakhir yang ditugaskan kepada Anda</p>
            </div>
            <a href="{{ route('inspector.tasks.index') }}" class="text-xs font-bold text-kai-blue hover:text-kai-navy transition inline-flex items-center gap-1">
                Lihat Semua Tugas <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3 px-4 sm:px-6">No. Referensi</th>
                        <th class="py-3 px-4">Pegawai & Unit</th>
                        <th class="py-3 px-4">Perangkat</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentTasks as $task)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="font-bold text-kai-navy font-mono">{{ $task->reference_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $task->inspectionPeriod?->name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $task->employee?->name }}</div>
                                <div class="text-xs text-slate-500">{{ $task->employee?->unit?->name }} (NIPP: {{ $task->employee?->nipp }})</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $task->device?->device_type->value === 'LAPTOP' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ $task->device?->device_type->label() }}
                                    </span>
                                    <span class="font-mono text-xs font-bold text-slate-800">{{ $task->device?->asset_number }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $task->device?->brand }} {{ $task->device?->model }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <x-status-badge :status="$task->status" />
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                @if ($task->status->value === 'ASSIGNED')
                                    <form method="POST" action="{{ route('inspector.tasks.start', $task) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-kai px-3 py-1.5 rounded-lg text-white font-semibold text-xs transition inline-flex items-center gap-1 shadow-xs">
                                            <i class="fa-solid fa-play text-[10px]"></i> Mulai Periksa
                                        </button>
                                    </form>
                                @elseif (in_array($task->status->value, ['IN_PROGRESS']))
                                    <a href="{{ route('inspector.tasks.checklist', $task) }}"
                                        class="btn-kai px-3 py-1.5 rounded-lg text-white font-semibold text-xs transition inline-flex items-center gap-1 shadow-xs">
                                        <i class="fa-solid fa-list-check text-[10px]"></i> Lanjutkan
                                    </a>
                                @elseif ($task->status->value === 'REVISION_REQUIRED')
                                    <a href="{{ route('inspector.tasks.revision', $task) }}"
                                        class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition inline-flex items-center gap-1 shadow-xs">
                                        <i class="fa-solid fa-pen text-[10px]"></i> Perbaiki
                                    </a>
                                @else
                                    <a href="{{ route('inspector.tasks.show', $task) }}"
                                        class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition inline-flex items-center gap-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i> Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-2">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-600">Belum ada tugas pemeriksaan</p>
                                <p class="text-xs text-slate-400 mt-0.5">Semua tugas yang diberikan oleh Admin Wilayah akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
