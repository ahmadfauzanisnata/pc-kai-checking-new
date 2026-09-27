@extends('layouts.app')

@section('title', 'Detail Tugas: ' . $inspection->reference_number)
@section('page_title', 'Detail Pemeriksaan')
@section('page_subtitle', $inspection->reference_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Summary Banner -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-kai-navy">{{ $inspection->reference_number }}</span>
                <x-status-badge :status="$inspection->status" />
                @if ($inspection->checklist)
                    <x-finding-badge :hasFindings="$inspection->checklist->hasNegativeFindings()" :count="$inspection->checklist->countNegativeFindings()" />
                @endif
            </div>
            <h2 class="text-xl font-bold text-slate-900 mt-2">
                Pemeriksaan: {{ $inspection->device?->brand }} {{ $inspection->device?->model }} ({{ $inspection->device?->asset_number }})
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Periode: <span class="font-semibold text-slate-700">{{ $inspection->inspectionPeriod?->name }}</span> |
                Tgl Dokumen: {{ $inspection->document_date->format('d M Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if ($inspection->status->value === 'ASSIGNED')
                <form method="POST" action="{{ route('inspector.tasks.start', $inspection) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition flex items-center gap-2 active:scale-[0.98]">
                        <i class="fa-solid fa-play"></i> Mulai Pemeriksaan
                    </button>
                </form>
            @elseif ($inspection->status->value === 'IN_PROGRESS')
                <a href="{{ route('inspector.tasks.checklist', $inspection) }}"
                    class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition flex items-center gap-2 active:scale-[0.98]">
                    <i class="fa-solid fa-list-check"></i> Lanjutkan Pengisian Checklist
                </a>
            @elseif ($inspection->status->value === 'REVISION_REQUIRED')
                <a href="{{ route('inspector.tasks.revision', $inspection) }}"
                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2 active:scale-[0.98]">
                    <i class="fa-solid fa-pen-to-square"></i> Perbaiki Pemeriksaan
                </a>
            @endif

            <a href="{{ route('reports.preview', $inspection->public_id) }}" target="_blank"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-pdf text-rose-600"></i> Preview Formulir
            </a>
        </div>
    </div>

    <!-- Two-column info: Employee & Device -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Employee Info -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-user text-kai-orange"></i> Informasi Pegawai Terkait
            </h3>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Nama Lengkap</span>
                    <span class="font-bold text-slate-800">{{ $inspection->employee?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Nomor NIPP</span>
                    <span class="font-mono text-kai-navy font-bold">{{ $inspection->employee?->nipp }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Jabatan</span>
                    <span class="text-slate-800 font-medium">{{ $inspection->employee?->position ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Unit Kerja</span>
                    <span class="text-slate-800 font-medium">{{ $inspection->employee?->unit?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Business Area</span>
                    <span class="text-slate-800 font-medium">{{ $inspection->employee?->unit?->businessArea?->name }}</span>
                </div>
            </div>
        </div>

        <!-- Device Info -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-laptop text-kai-orange"></i> Informasi Spesifikasi Perangkat
            </h3>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Nomor Aset</span>
                    <span class="font-mono font-bold text-slate-900">{{ $inspection->device?->asset_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Jenis Perangkat</span>
                    <span class="font-semibold text-slate-800">{{ $inspection->device?->device_type?->label() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Merk & Model</span>
                    <span class="text-slate-800 font-medium">{{ $inspection->device?->brand }} {{ $inspection->device?->model }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Hostname</span>
                    <span class="font-mono text-slate-800 font-medium">{{ $inspection->device?->hostname ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Nomor Seri</span>
                    <span class="font-mono text-slate-600 text-xs">{{ $inspection->device?->serial_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-xs">Sistem Operasi</span>
                    <span class="text-slate-800 font-medium">{{ $inspection->device?->operating_system }} {{ $inspection->device?->os_version }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklist Results Summary (if already filled) -->
    @if ($inspection->checklist)
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
            <h3 class="text-base font-bold text-slate-800 pb-4 border-b border-slate-100 flex items-center justify-between">
                <span>Hasil Pemeriksaan 12 Butir Keamanan</span>
                @if ($inspection->checklist->hasNegativeFindings())
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">
                        {{ $inspection->checklist->countNegativeFindings() }} Temuan Ditemukan
                    </span>
                @else
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        Seluruh Standar Terpenuhi
                    </span>
                @endif
            </h3>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs sm:text-sm">
                @foreach (\App\Models\InspectionChecklist::checklistFields() as $field => $cfg)
                    @php
                        $val = $inspection->checklist->{$field};
                        $isNegative = $inspection->checklist->isNegativeFinding($field);
                    @endphp
                    <div class="p-3 rounded-xl border flex items-center justify-between {{ $isNegative ? 'bg-rose-50/70 border-rose-200 text-rose-900' : 'bg-slate-50 border-slate-100 text-slate-700' }}">
                        <span class="font-medium">{{ $cfg['label'] }}</span>
                        <span class="font-bold font-mono px-2 py-0.5 rounded text-xs {{ $isNegative ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-800' }}">
                            {{ $val ?? 'BELUM DIISI' }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($inspection->checklist->finding_summary)
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs sm:text-sm">
                    <span class="font-bold text-amber-900 block mb-1">Ringkasan Catatan Temuan:</span>
                    <p class="text-amber-800">{{ $inspection->checklist->finding_summary }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Status History Timeline -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <h3 class="text-base font-bold text-slate-800 pb-4 border-b border-slate-100">
            Riwayat Status Pemeriksaan
        </h3>

        <div class="mt-4 relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
            @foreach ($inspection->statusHistories as $hist)
                <div class="relative">
                    <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-kai-orange border-2 border-white shadow"></div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                        <div class="font-bold text-slate-900">
                            {{ $hist->to_status?->label() }}
                            <span class="font-normal text-slate-500">oleh {{ $hist->changedBy?->name ?? 'Sistem' }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-mono">
                            {{ $hist->created_at->format('d M Y H:i') }} WIB
                        </div>
                    </div>
                    @if ($hist->reason)
                        <p class="text-xs text-slate-600 mt-1 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            {{ $hist->reason }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
