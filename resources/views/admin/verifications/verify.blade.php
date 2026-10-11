@extends('layouts.app')

@section('title', 'Verifikasi: ' . $inspection->reference_number)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.verifications.index') }}" class="inline-flex items-center text-sm font-semibold text-kai-navy hover:text-kai-orange transition">
                &larr; Kembali ke Daftar Verifikasi
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">Verifikasi Pemeriksaan Keamanan PC</h1>
            <p class="text-xs sm:text-sm text-slate-500">No. Ref: <span class="font-mono font-bold text-kai-navy">{{ $inspection->reference_number }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <x-status-badge :status="$inspection->status" />
            <a href="{{ route('reports.preview', $inspection) }}" target="_blank" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition shadow-2xs">
                <i class="fa-regular fa-eye mr-1 text-slate-500"></i> Preview Formulir Cetak
            </a>
        </div>
    </div>

    @if($inspection->status === \App\Enums\InspectionStatus::ADMIN_REVISION_REQUIRED)
        @php
            $saNote = $inspection->notes->where('note_type', \App\Enums\NoteType::SUPER_ADMIN_REVISION)->sortByDesc('id')->first();
            $saHist = $inspection->statusHistories->where('action', 'return_to_admin')->sortByDesc('id')->first();
            $saReason = $saNote?->note ?: ($saHist?->reason ?: 'Perlu perbaikan atau peninjauan ulang oleh Admin Wilayah.');
            $saUser = $saNote?->user?->name ?? ($saHist?->changedBy?->name ?? 'Super Admin');
            $saTime = ($saNote?->created_at ?? $saHist?->created_at)?->format('d M Y H:i');
        @endphp
        <div class="bg-amber-50 border-2 border-amber-400 p-5 rounded-2xl shadow-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center shrink-0 text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-900 bg-amber-200/80 px-2 py-0.5 rounded">
                                Dikembalikan oleh Super Admin
                            </span>
                            <span class="text-xs font-bold text-slate-800">{{ $saUser }}</span>
                        </div>
                        @if($saTime)
                            <span class="text-[11px] text-amber-800 font-mono">{{ $saTime }} WIB</span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-amber-900 mt-2">Catatan / Alasan Pengembalian dari Super Admin:</p>
                    <div class="mt-1.5 bg-white p-3.5 rounded-xl border border-amber-300 text-sm text-slate-900 font-medium whitespace-pre-wrap leading-relaxed shadow-2xs">
                        {{ $saReason }}
                    </div>
                    <p class="text-xs text-amber-800 mt-2.5">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        <strong>Instruksi Tindak Lanjut:</strong> Periksa kembali checklist dan berkas. Anda dapat memverifikasi ulang dan langsung mengajukan kembali ke Super Admin di bawah, atau meneruskan pengembalian ini ke Pemeriksa jika diperlukan perbaikan fisik/lampiran oleh Pemeriksa.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Metadata Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Device Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                <span>Data Perangkat</span>
                <span class="text-kai-orange font-semibold">{{ $inspection->device?->device_type?->label() }}</span>
            </div>
            <div class="text-base font-bold text-slate-900">{{ $inspection->device?->hostname }}</div>
            <div class="text-xs text-slate-600 mt-1 space-y-1">
                <div>No. Aset: <span class="font-mono font-medium">{{ $inspection->device?->asset_number }}</span></div>
                <div>Serial: <span class="font-mono text-slate-500">{{ $inspection->device?->serial_number ?? '-' }}</span></div>
                <div>Sistem Operasi: <span class="text-slate-700">{{ $inspection->device?->os_name }} {{ $inspection->device?->os_version }}</span></div>
                <div>IP / MAC: <span class="font-mono text-[11px]">{{ $inspection->device?->ip_address ?? '-' }} / {{ $inspection->device?->mac_address ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                Pegawai Pemegang Perangkat
            </div>
            <div class="text-base font-bold text-slate-900">{{ $inspection->employee?->name }}</div>
            <div class="text-xs text-slate-600 mt-1 space-y-1">
                <div>NIPP: <span class="font-mono font-semibold text-kai-navy">{{ $inspection->employee?->nipp }}</span></div>
                <div>Jabatan: <span class="text-slate-700">{{ $inspection->employee?->position ?? '-' }}</span></div>
                <div>Unit: <span class="text-slate-800 font-medium">{{ $inspection->employee?->unit?->name }}</span></div>
                <div>Business Area: <span class="text-kai-navy font-semibold">{{ $inspection->employee?->unit?->businessArea?->name }}</span></div>
            </div>
        </div>

        <!-- Inspector & Period -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                Pemeriksa & Jadwal
            </div>
            <div class="text-base font-bold text-slate-900">{{ $inspection->inspector?->name }}</div>
            <div class="text-xs text-slate-600 mt-1 space-y-1">
                <div>NIPP Pemeriksa: <span class="font-mono font-medium text-slate-800">{{ $inspection->inspector?->nipp ?? '-' }}</span></div>
                <div>Periode: <span class="text-kai-navy font-semibold">{{ $inspection->inspectionPeriod?->name }}</span></div>
                <div>Tanggal Disubmit: <span class="text-slate-800 font-medium">{{ $inspection->submitted_at?->format('d F Y, H:i') ?? '-' }}</span></div>
                <div>Status Saat Ini: <span class="font-semibold">{{ $inspection->status->label() }}</span></div>
            </div>
        </div>
    </div>

    <!-- Communication & Notes History Component -->
    <x-inspection-notes-card :inspection="$inspection" />

    <!-- 12 Criteria Checklist Result Review -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Hasil Pemeriksaan 12 Kriteria Keamanan</h2>
                <p class="text-xs text-gray-500">Bandingkan hasil pemeriksaan di bawah ini dengan standar keamanan IT KAI.</p>
            </div>
            @if($inspection->checklist)
                @php $negCount = $inspection->checklist->getNegativeFindingsCount(); @endphp
                @if($negCount > 0)
                    <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">
                        {{ $negCount }} Temuan Tidak Sesuai
                    </span>
                @else
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                        Semua Kriteria Sesuai Standar
                    </span>
                @endif
            @endif
        </div>

        @if($inspection->checklist)
        @php $c = $inspection->checklist; @endphp
        <div class="divide-y divide-gray-100">
            <!-- 1. NDA -->
            <div class="p-4 flex items-center justify-between {{ $c->nda_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">1. Formulir NDA (Non-Disclosure Agreement)</span>
                    @if($c->nda_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->nda_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->nda_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 2. Strong Password -->
            <div class="p-4 flex items-center justify-between {{ $c->strong_password_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">2. Password Kompleks (Strong Password)</span>
                    @if($c->strong_password_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->strong_password_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->strong_password_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 3. Screensaver Lock -->
            <div class="p-4 flex items-center justify-between {{ $c->screensaver_lock_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">3. Screensaver Lock Otomatis (Maks 15 Menit)</span>
                    @if($c->screensaver_lock_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->screensaver_lock_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->screensaver_lock_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 4. Cleardesk -->
            <div class="p-4 flex items-center justify-between {{ $c->cleardesk_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">4. Kepatuhan Clear Desk & Clear Screen</span>
                    @if($c->cleardesk_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->cleardesk_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->cleardesk_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 5. Prohibited Files -->
            <div class="p-4 flex items-center justify-between {{ $c->prohibited_files_status === 'ADA' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">5. Keberadaan File Terlarang / Bajakan</span>
                    @if($c->prohibited_files_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->prohibited_files_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if(in_array($c->prohibited_files_status, ['TIDAK_ADA', 'TIDAK']))
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; TIDAK ADA</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; ADA (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 6. Antivirus Install -->
            <div class="p-4 flex items-center justify-between {{ $c->antivirus_install_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">6. Instalasi Antivirus Resmi</span>
                    <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                        <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                        <span>Nama Antivirus: <strong class="text-slate-900 font-semibold">{{ $c->antivirus_name ?? '-' }}</strong></span>
                    </div>
                </div>
                <div>
                    @if($c->antivirus_install_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 7. Antivirus Update -->
            <div class="p-4 flex items-center justify-between {{ $c->antivirus_update_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">7. Pembaruan Definisi Antivirus</span>
                    @if($c->antivirus_update_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->antivirus_update_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->antivirus_update_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 8. Full Scan Schedule -->
            <div class="p-4 flex items-center justify-between {{ $c->full_scan_schedule_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">8. Jadwal Full Scan Mingguan</span>
                    @if($c->full_scan_schedule_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->full_scan_schedule_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->full_scan_schedule_status === 'SUDAH')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; SUDAH</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; BELUM (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 9. OS License -->
            <div class="p-4 flex items-center justify-between {{ in_array($c->os_license_status, ['TIDAK', 'BELUM']) ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">9. Lisensi Sistem Operasi Legal</span>
                    @if($c->os_license_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->os_license_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if(in_array($c->os_license_status, ['YA', 'LICENSE']))
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; LICENSE</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; TIDAK (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 10. NTP Sync -->
            <div class="p-4 flex items-center justify-between {{ $c->ntp_sync_status === 'TIDAK' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">10. Sinkronisasi Jam ke NTP Server KAI</span>
                    @if($c->ntp_sync_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->ntp_sync_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($c->ntp_sync_status === 'YA')
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; YA</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; TIDAK (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 11. PC Label -->
            <div class="p-4 flex items-center justify-between {{ in_array($c->pc_label_status, ['TIDAK', 'BELUM']) ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">11. Stiker Label Fisik PC / Aset</span>
                    @if($c->pc_label_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->pc_label_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    @if(in_array($c->pc_label_status, ['YA', 'ADA']))
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">&check; ADA</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">&cross; TIDAK (Temuan)</span>
                    @endif
                </div>
            </div>

            <!-- 12. Special Access -->
            <div class="p-4 flex items-center justify-between {{ $c->special_access?->value === 'DOMAIN_ADMIN' ? 'bg-rose-50/50' : '' }}">
                <div class="space-y-0.5">
                    <span class="text-sm font-semibold text-gray-900">12. Hak Akses Khusus Pegawai</span>
                    @if($c->special_access_notes)
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-700 bg-amber-50/80 border border-amber-200/80 px-2.5 py-1 rounded-lg w-fit">
                            <i class="fa-regular fa-comment-dots text-kai-orange shrink-0"></i>
                            <span>Catatan Pemeriksa: <strong class="text-slate-900 font-semibold">{{ $c->special_access_notes }}</strong></span>
                        </div>
                    @endif
                </div>
                <div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold {{ $c->special_access?->value === 'DOMAIN_ADMIN' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $c->special_access?->label() ?? 'NONE' }}
                    </span>
                </div>
            </div>
        </div>
        @else
        <div class="p-8 text-center text-gray-400">
            Checklist belum diisi.
        </div>
        @endif
    </div>

    <!-- Attachments Evidence -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Berkas Bukti / Lampiran ({{ $inspection->attachments->count() }})</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @forelse($inspection->attachments as $att)
            <div class="border border-gray-200 rounded-lg p-3 text-xs flex items-center justify-between">
                <div>
                    <span class="font-semibold text-gray-800 block">{{ $att->file_name }}</span>
                    <span class="text-[11px] text-gray-400">{{ $att->attachment_type->label() }} ({{ round($att->file_size / 1024) }} KB)</span>
                </div>
                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="text-kai-orange font-bold hover:underline">
                    Buka &rarr;
                </a>
            </div>
            @empty
            <div class="col-span-3 text-xs text-gray-400 py-2">
                Tidak ada berkas bukti yang dilampirkan.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Actions by Admin -->
    @if(in_array($inspection->status, [
        \App\Enums\InspectionStatus::PENDING_ADMIN_VERIFICATION,
        \App\Enums\InspectionStatus::RESUBMITTED,
        \App\Enums\InspectionStatus::ADMIN_REVISION_REQUIRED,
    ]))
    @php
        $isReturned = $inspection->status === \App\Enums\InspectionStatus::ADMIN_REVISION_REQUIRED;
        $saNoteForTextarea = $isReturned ? ($inspection->notes->where('note_type', \App\Enums\NoteType::SUPER_ADMIN_REVISION)->sortByDesc('id')->first()?->note ?? '') : '';
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">
                    {{ $isReturned ? 'Tindak Lanjut & Keputusan Admin (Pengembalian Super Admin)' : 'Keputusan Verifikasi Admin' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $isReturned ? 'Pilih untuk memverifikasi ulang dan mengajukan kembali ke Super Admin, atau teruskan perbaikan ke Pemeriksa.' : 'Verifikasi kepatuhan checklist atau kembalikan ke pemeriksa bila ada temuan yang belum lengkap.' }}
                </p>
            </div>
            @if($isReturned)
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-xs font-bold">
                    Perlu Penanganan Admin
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Form 1: Approve / Verify and Submit to Super Admin -->
            <div class="border border-emerald-200 rounded-2xl p-5 bg-emerald-50/30 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-emerald-900 text-sm flex items-center">
                        <svg class="w-5 h-5 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $isReturned ? 'Verifikasi Ulang & Teruskan ke Super Admin' : 'Setujui Hasil Verifikasi' }}
                    </h3>
                    <p class="text-xs text-emerald-700 mt-1">
                        {{ $isReturned ? 'Data telah ditinjau/diperbaiki dan valid. Lakukan verifikasi ulang atau langsung ajukan kembali ke Super Admin.' : 'Data telah diperiksa lengkap dan valid. Lanjutkan status ke verifikasi Admin atau ajukan langsung ke Super Admin.' }}
                    </p>
                </div>

                <form action="{{ route('admin.verifications.verify', $inspection) }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="comment" placeholder="Catatan opsional verifikasi..." class="w-full text-xs border border-emerald-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 bg-white">
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-[0.98]">
                        &check; {{ $isReturned ? 'Verifikasi Ulang Pemeriksaan Ini' : 'Verifikasi Pemeriksaan Ini' }}
                    </button>
                    <button type="submit" formaction="{{ route('admin.verifications.submit-approval', $inspection) }}" class="btn-kai w-full py-2.5 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-[0.98]">
                        &uarr; {{ $isReturned ? 'Verifikasi Ulang & Ajukan Kembali ke Super Admin &rarr;' : 'Verifikasi & Teruskan ke Super Admin &rarr;' }}
                    </button>
                </form>
            </div>

            <!-- Form 2: Request Revision / Forward to Inspector -->
            <div class="border border-rose-200 rounded-2xl p-5 bg-rose-50/30 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-rose-900 text-sm flex items-center">
                        <svg class="w-5 h-5 mr-1.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        {{ $isReturned ? 'Teruskan Revisi ke Pemeriksa' : 'Minta Perbaikan / Revisi' }}
                    </h3>
                    <p class="text-xs text-rose-700 mt-1">
                        {{ $isReturned ? 'Teruskan catatan perbaikan dari Super Admin kepada Pemeriksa agar checklist atau bukti diperbaiki.' : 'Kembalikan ke Pemeriksa jika ada data yang kurang, belum diisi dengan benar, atau bukti foto belum jelas.' }}
                    </p>
                </div>

                <form action="{{ route('admin.verifications.request-revision', $inspection) }}" method="POST" class="mt-4">
                    @csrf
                    <textarea name="reason" rows="3" required placeholder="Tulis alasan revisi yang jelas untuk pemeriksa..." class="w-full text-xs border border-rose-300 rounded-xl mb-2 px-3 py-2 outline-none focus:ring-2 focus:ring-rose-500/20">{{ $isReturned && $saNoteForTextarea ? "Perbaikan berdasarkan catatan Super Admin: {$saNoteForTextarea}" : '' }}</textarea>
                    <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-[0.98]">
                        &larr; {{ $isReturned ? 'Kembalikan ke Pemeriksa untuk Diperbaiki' : 'Kembalikan untuk Revisi' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @elseif($inspection->status === \App\Enums\InspectionStatus::ADMIN_VERIFIED)
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> Pemeriksaan telah Anda verifikasi.
            </span>
            <p class="text-xs text-slate-500 mt-0.5">Silakan ajukan ke Super Admin untuk pengesahan tanda tangan akhir.</p>
        </div>
        <form action="{{ route('admin.verifications.submit-approval', $inspection) }}" method="POST">
            @csrf
            <button type="submit" class="btn-kai px-5 py-2.5 text-white rounded-xl text-xs font-bold transition shadow-sm active:scale-[0.98]">
                Ajukan ke Super Admin &rarr;
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
