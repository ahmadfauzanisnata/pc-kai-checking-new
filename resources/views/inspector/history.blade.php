@extends('layouts.app')

@section('title', 'Riwayat Pemeriksaan Selesai - KAI PC Checking')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Riwayat Pemeriksaan Selesai</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar pemeriksaan yang telah Anda selesaikan dan disetujui secara resmi.</p>
        </div>
        <a href="{{ route('inspector.tasks.index') }}" class="inline-flex items-center text-sm font-semibold text-kai-blue hover:underline">
            &larr; Kembali ke Tugas Aktif
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4 sm:px-6">No. Referensi</th>
                        <th class="py-3 px-4">Pegawai / Pemakai</th>
                        <th class="py-3 px-4">Perangkat</th>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $item)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-kai-navy whitespace-nowrap">
                            {{ $item->reference_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-800">{{ $item->employee?->name }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $item->employee?->unit?->name }} (NIPP: {{ $item->employee?->nipp }})</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $item->device?->hostname }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $item->device?->asset_number }}</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="text-slate-700 font-medium">{{ $item->inspectionPeriod?->name }}</span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <x-status-badge :status="$item->status" />
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap space-x-1.5">
                            <a href="{{ route('inspector.tasks.show', $item) }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 transition">
                                <i class="fa-solid fa-eye mr-1 text-[10px]"></i> Detail
                            </a>
                            <a href="{{ route('reports.pdf', $item) }}" target="_blank" class="btn-kai inline-flex items-center px-2.5 py-1.5 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                <i class="fa-solid fa-file-pdf mr-1 text-[10px]"></i> Unduh PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Belum ada riwayat pemeriksaan yang selesai disetujui.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $history->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
