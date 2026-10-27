@extends('layouts.app')

@section('title', 'Semua Pemeriksaan - Super Admin KAI')
@section('page_title', 'Semua Data Pemeriksaan PC / Laptop')
@section('page_subtitle', 'Daftar menyeluruh pemeriksaan keamanan perangkat di seluruh wilayah kerja PT KAI')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Semua Data Pemeriksaan PC / Laptop</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar menyeluruh pemeriksaan keamanan perangkat di seluruh wilayah kerja PT KAI.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.index') }}" class="btn-kai px-4 py-2.5 rounded-xl text-white text-xs sm:text-sm font-semibold shadow-sm transition inline-flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-xs"></i> Pusat Laporan & Ekspor &rarr;
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('super-admin.inspections.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Keyword</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Ref, Hostname, Pegawai..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pemeriksaan</label>
                <select name="status" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Status</option>
                    @foreach(\App\Enums\InspectionStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Business Area</label>
                <select name="business_area_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Business Area</option>
                    @foreach($businessAreas as $ba)
                        <option value="{{ $ba->id }}" {{ request('business_area_id') == $ba->id ? 'selected' : '' }}>
                            {{ $ba->name }} ({{ $ba->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter Data
                </button>
                <a href="{{ route('super-admin.inspections.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Daftar Data Pemeriksaan</h2>
            <span class="text-xs text-slate-500">Total data: {{ $inspections->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">No. Referensi</th>
                        <th class="py-3.5 px-4">Area & Unit</th>
                        <th class="py-3.5 px-4">Pegawai</th>
                        <th class="py-3.5 px-4">Perangkat</th>
                        <th class="py-3.5 px-4">Pemeriksa</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inspections as $insp)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap font-mono font-bold text-kai-navy">
                            {{ $insp->reference_number }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-bold text-slate-800">{{ $insp->inspectionPeriod?->businessArea?->code ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $insp->employee?->unit?->name ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900">{{ $insp->employee?->name }}</div>
                            <div class="text-slate-400 text-[11px]">NIPP: {{ $insp->employee?->nipp }}</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $insp->device?->hostname }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $insp->device?->asset_number }}</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="text-slate-700 font-medium">{{ $insp->inspector?->name }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <x-status-badge :status="$insp->status" />
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('super-admin.inspections.show-approval', $insp) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 transition shadow-2xs"
                                    title="Review Pemeriksaan" aria-label="Review Pemeriksaan">
                                    <i class="fa-regular fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('reports.pdf', $insp) }}" target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-kai-orange/10 text-kai-orange border border-kai-orange/30 hover:bg-kai-orange hover:text-white transition shadow-2xs"
                                    title="Cetak PDF" aria-label="Cetak PDF">
                                    <i class="fa-solid fa-print text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300"></i>
                            <p class="text-sm">Tidak ada data pemeriksaan ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $inspections->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
