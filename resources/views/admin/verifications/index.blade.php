@extends('layouts.app')

@section('title', 'Verifikasi Hasil Pemeriksaan - KAI PC Checking')
@section('page_title', 'Verifikasi Pemeriksaan PC / Laptop')
@section('page_subtitle', 'Review dan verifikasi kepatuhan checklist hasil pemeriksaan dari tim teknisi/pemeriksa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Pemeriksaan PC / Laptop</h1>
            <p class="text-sm text-slate-500 mt-1">Review dan verifikasi kepatuhan checklist hasil pemeriksaan dari tim teknisi/pemeriksa.</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('admin.verifications.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
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
                <label class="block text-xs font-semibold text-slate-600 mb-1">Periode</label>
                <select name="period_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Temuan</label>
                <select name="has_findings" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Hasil</option>
                    <option value="yes" {{ request('has_findings') === 'yes' ? 'selected' : '' }}>Hanya yang Memiliki Temuan</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter Data
                </button>
                <a href="{{ route('admin.verifications.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Verifications Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Daftar Pemeriksaan untuk Diverifikasi</h2>
            <span class="text-xs text-slate-500">Total data: {{ $inspections->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">No. Referensi</th>
                        <th class="py-3.5 px-4">Pegawai / Unit</th>
                        <th class="py-3.5 px-4">Perangkat</th>
                        <th class="py-3.5 px-4">Pemeriksa</th>
                        <th class="py-3.5 px-4 text-center">Temuan Keamanan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inspections as $insp)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap font-mono">
                            <span class="font-bold text-kai-navy block">{{ $insp->reference_number }}</span>
                            <span class="text-[11px] text-slate-400">Sub: {{ $insp->submitted_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900">{{ $insp->employee?->name }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $insp->employee?->unit?->name }} (NIPP: {{ $insp->employee?->nipp }})</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $insp->device?->hostname }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $insp->device?->asset_number }} ({{ $insp->device?->device_type?->label() }})</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-medium text-slate-700">{{ $insp->inspector?->name }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($insp->checklist)
                                @php $negativeCount = $insp->checklist->getNegativeFindingsCount(); @endphp
                                @if($negativeCount > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-triangle-exclamation mr-1 text-[10px] text-rose-600"></i>
                                        {{ $negativeCount }} Temuan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-1 text-[10px] text-emerald-600"></i>
                                        Sesuai Standar
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 italic">Belum diisi</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <x-status-badge :status="$insp->status" />
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                            <a href="{{ route('admin.verifications.show', $insp) }}" class="btn-kai inline-flex items-center px-3 py-1.5 text-white rounded-xl text-xs font-semibold shadow-xs transition gap-1">
                                Verifikasi <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300"></i>
                            <p class="text-sm">Tidak ada pemeriksaan yang ditemukan dengan kriteria di atas.</p>
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
