@extends('layouts.app')

@section('title', 'Pemeriksaan Diarsipkan (Archived) - KAI PC Checking')
@section('page_title', 'Arsip Pemeriksaan')
@section('page_subtitle', 'Daftar pemeriksaan masa lalu yang telah diarsipkan untuk kepatuhan audit jangka panjang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Arsip Pemeriksaan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar pemeriksaan masa lalu yang telah diarsipkan untuk kepatuhan audit jangka panjang.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold rounded-full flex items-center gap-1.5">
                <i class="fa-solid fa-box-archive text-slate-500"></i>
                {{ $inspections->total() }} Diarsipkan
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">No. Referensi</th>
                        <th class="px-5 py-3.5">Wilayah</th>
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-5 py-3.5">Perangkat</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($inspections as $insp)
                    <tr class="table-row-hover transition">
                        <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-kai-navy">
                            {{ $insp->reference_number }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="font-bold text-slate-800">{{ $insp->inspectionPeriod?->businessArea?->code ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900">{{ $insp->employee?->name }}</div>
                            <div class="text-slate-400 text-[11px] font-mono">NIPP: {{ $insp->employee?->nipp }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $insp->device?->hostname }}</div>
                            <div class="text-slate-400 font-mono text-[11px]">{{ $insp->device?->asset_number }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <x-status-badge :status="$insp->status" />
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('super-admin.inspections.show-approval', $insp) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 transition shadow-2xs"
                                    title="Detail Pemeriksaan" aria-label="Detail Pemeriksaan">
                                    <i class="fa-regular fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('reports.pdf', $insp) }}" target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-kai-orange/10 text-kai-orange border border-kai-orange/30 hover:bg-kai-orange hover:text-white transition shadow-2xs"
                                    title="Unduh PDF" aria-label="Unduh PDF">
                                    <i class="fa-solid fa-print text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-archive text-3xl mb-2 text-slate-300 block"></i>
                            Belum ada pemeriksaan yang diarsipkan.
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
