@extends('layouts.app')

@section('title', 'Pemeriksaan Disetujui (Approved) - KAI PC Checking')
@section('page_title', 'Pemeriksaan Disetujui')
@section('page_subtitle', 'Daftar pemeriksaan yang telah disetujui resmi oleh Super Admin dan siap diarsipkan atau dikunci')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pemeriksaan Disetujui (Approved)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar pemeriksaan yang telah disetujui resmi oleh Super Admin. Siap dikunci untuk pencegahan modifikasi atau diunduh laporannya.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-full flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                {{ $inspections->total() }} Telah Disetujui
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
                        <th class="px-5 py-3.5">Area & Unit</th>
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-5 py-3.5">Perangkat</th>
                        <th class="px-5 py-3.5">Tanggal Disetujui</th>
                        <th class="px-5 py-3.5 text-right">Aksi & Manajemen</th>
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
                            <div class="text-[11px] text-slate-400">{{ $insp->employee?->unit?->name ?? '-' }}</div>
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
                            <span class="text-slate-800 font-medium">{{ $insp->approved_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                            <a href="{{ route('reports.pdf', $insp) }}" target="_blank"
                                class="inline-flex items-center px-3 py-1.5 bg-kai-orange/10 text-kai-orange border border-kai-orange/30 rounded-lg text-xs font-bold hover:bg-kai-orange hover:text-white transition shadow-2xs">
                                <i class="fa-solid fa-file-pdf mr-1"></i> Unduh PDF
                            </a>
                            <form action="{{ route('super-admin.inspections.lock', $insp) }}" method="POST" class="inline-block" id="lock-form-{{ $insp->id }}">
                                @csrf
                                <button type="button" onclick="confirmAction({
                                    title: 'Kunci Pemeriksaan?',
                                    text: 'Kunci data pemeriksaan ini agar tidak dapat diubah lagi?',
                                    icon: 'warning',
                                    confirmButtonColor: '#d97706',
                                    confirmButtonText: 'Ya, Kunci Data',
                                    formId: 'lock-form-{{ $insp->id }}'
                                })" class="px-2.5 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-semibold hover:bg-amber-100 transition">
                                    <i class="fa-solid fa-lock mr-1"></i> Kunci
                                </button>
                            </form>
                            <form action="{{ route('super-admin.inspections.archive', $insp) }}" method="POST" class="inline-block" id="archive-form-{{ $insp->id }}">
                                @csrf
                                <button type="button" onclick="confirmAction({
                                    title: 'Arsipkan Pemeriksaan?',
                                    text: 'Arsipkan pemeriksaan ini? Status akan berubah menjadi Diarsipkan.',
                                    icon: 'info',
                                    confirmButtonColor: '#475569',
                                    confirmButtonText: 'Ya, Arsipkan',
                                    formId: 'archive-form-{{ $insp->id }}'
                                })" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold hover:bg-slate-200 transition">
                                    <i class="fa-solid fa-box-archive mr-1"></i> Arsipkan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                            Belum ada pemeriksaan dengan status disetujui.
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
