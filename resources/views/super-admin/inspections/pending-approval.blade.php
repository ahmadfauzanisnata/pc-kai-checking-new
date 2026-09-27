@extends('layouts.app')

@section('title', 'Menunggu Persetujuan Super Admin - KAI PC Checking')
@section('page_title', 'Persetujuan Super Admin')
@section('page_subtitle', 'Daftar pemeriksaan yang telah diverifikasi Admin Wilayah dan siap disahkan secara resmi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Menunggu Persetujuan Super Admin</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar pemeriksaan yang telah lolos verifikasi Admin Wilayah dan siap disahkan dengan tanda tangan digital resmi PT KAI.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold rounded-full flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 pulse-dot"></span>
                {{ $inspections->total() }} Menunggu Approval
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
                        <th class="px-5 py-3.5">Wilayah & Unit</th>
                        <th class="px-5 py-3.5">Pegawai Pemakai</th>
                        <th class="px-5 py-3.5">Perangkat</th>
                        <th class="px-5 py-3.5">Pemeriksa</th>
                        <th class="px-5 py-3.5">Temuan</th>
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
                            <span class="font-medium text-slate-800">{{ $insp->inspector?->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            @if($insp->checklist)
                                @php $negCount = $insp->checklist->getNegativeFindingsCount(); @endphp
                                @if($negCount > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1"></span> {{ $negCount }} Temuan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check mr-1 text-[10px]"></i> Sesuai
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('super-admin.inspections.show-approval', $insp) }}"
                                class="btn-kai inline-flex items-center px-3.5 py-1.5 text-white rounded-lg text-xs font-bold transition shadow-xs active:scale-[0.98]">
                                Review & Pengesahan &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-500 block"></i>
                            Tidak ada pemeriksaan yang menunggu persetujuan Super Admin.
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
