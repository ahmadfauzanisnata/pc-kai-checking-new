@extends('layouts.app')

@section('title', 'Pusat Laporan & Ekspor - KAI PC Checking')
@section('page_title', 'Pusat Laporan & Ekspor')
@section('page_subtitle', 'Unduh rekapitulasi data pemeriksaan dalam format Excel (XLSX)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pusat Laporan Pemeriksaan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Unduh rekapitulasi data dalam format Excel (XLSX) berstandar resmi PT Kereta Api Indonesia (Persero).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.excel', request()->all()) }}"
                class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm active:scale-[0.98]">
                <i class="fa-solid fa-file-excel mr-2 text-sm"></i>
                Ekspor ke Excel (XLSX)
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3.5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Keyword</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Ref, Hostname, Pegawai..."
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            @if(auth()->user()->isSuperAdmin())
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Business Area</label>
                <select name="business_area_id"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Business Area</option>
                    @foreach($businessAreas as $ba)
                        <option value="{{ $ba->id }}" {{ request('business_area_id') == $ba->id ? 'selected' : '' }}>
                            {{ $ba->name }} ({{ $ba->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Periode</label>
                <select name="period_id"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Status</option>
                    @foreach(\App\Enums\InspectionStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-kai-navy hover:bg-kai-navy-dark text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('reports.index') }}" class="py-2 px-3 border border-slate-300 text-slate-600 rounded-xl text-xs font-medium hover:bg-slate-50 transition" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">No. Referensi</th>
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-5 py-3.5">Unit</th>
                        <th class="px-5 py-3.5">Perangkat</th>
                        <th class="px-5 py-3.5">Temuan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi Unduh / Preview</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($inspections as $insp)
                    <tr class="table-row-hover transition">
                        <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-kai-navy">
                            {{ $insp->reference_number }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900">{{ $insp->employee?->name ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $insp->employee?->unit?->name ?? '-' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $insp->inspectionPeriod?->businessArea?->code ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $insp->device?->hostname }}</div>
                            <div class="text-slate-400 font-mono text-[11px]">{{ $insp->device?->asset_number }}</div>
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
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <x-status-badge :status="$insp->status" />
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('reports.preview', $insp) }}" target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 transition shadow-2xs"
                                    title="Form Preview" aria-label="Form Preview">
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
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                            Tidak ada data pemeriksaan ditemukan.
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
