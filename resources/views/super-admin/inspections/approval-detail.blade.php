@extends('layouts.app')

@section('title', 'Persetujuan Super Admin: ' . $inspection->reference_number)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('super-admin.inspections.index') }}" class="inline-flex items-center text-sm font-semibold text-kai-navy hover:text-kai-orange transition">
                &larr; Kembali ke Daftar Pemeriksaan
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">Review & Persetujuan Super Admin</h1>
            <p class="text-xs sm:text-sm text-slate-500">No. Ref: <span class="font-mono font-bold text-kai-navy">{{ $inspection->reference_number }}</span></p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-status-badge :status="$inspection->status" />
            <a href="{{ route('reports.preview', $inspection) }}" target="_blank" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition shadow-2xs">
                <i class="fa-regular fa-eye mr-1 text-slate-500"></i> Preview PDF
            </a>
            <a href="{{ route('reports.pdf', $inspection) }}" target="_blank" class="btn-kai px-3.5 py-2 text-white text-xs font-bold rounded-xl transition shadow-xs active:scale-[0.98]">
                <i class="fa-solid fa-file-pdf mr-1"></i> Unduh PDF
            </a>
        </div>
    </div>

    <!-- Metadata Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Device Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                <span>Perangkat</span>
                <span class="text-kai-orange font-semibold">{{ $inspection->device?->device_type?->label() }}</span>
            </div>
            <div class="text-base font-bold text-slate-900">{{ $inspection->device?->hostname }}</div>
            <div class="text-xs text-slate-600 mt-1 space-y-1">
                <div>No. Aset: <span class="font-mono font-medium">{{ $inspection->device?->asset_number }}</span></div>
                <div>Serial: <span class="font-mono text-slate-500">{{ $inspection->device?->serial_number ?? '-' }}</span></div>
                <div>OS: <span class="text-slate-700">{{ $inspection->device?->os_name }} {{ $inspection->device?->os_version }}</span></div>
                <div>IP: <span class="font-mono text-[11px]">{{ $inspection->device?->ip_address ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                Pegawai Pemegang
            </div>
            <div class="text-base font-bold text-slate-900">{{ $inspection->employee?->name }}</div>
            <div class="text-xs text-slate-600 mt-1 space-y-1">
                <div>NIPP: <span class="font-mono font-semibold text-kai-navy">{{ $inspection->employee?->nipp }}</span></div>
                <div>Jabatan: <span class="text-slate-700">{{ $inspection->employee?->position ?? '-' }}</span></div>
                <div>Unit: <span class="text-slate-800 font-medium">{{ $inspection->employee?->unit?->name }}</span></div>
                <div>Business Area: <span class="text-kai-navy font-semibold">{{ $inspection->inspectionPeriod?->businessArea?->name }}</span></div>
            </div>
        </div>

        <!-- Flow Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                Riwayat & Pelaksana
            </div>
            <div class="text-xs text-slate-600 space-y-1.5">
                <div>Pemeriksa: <strong class="text-slate-800">{{ $inspection->inspector?->name }}</strong></div>
                <div>Periode: <strong class="text-kai-navy">{{ $inspection->inspectionPeriod?->name }}</strong></div>
                <div>Tanggal Submit: <span class="text-slate-800">{{ $inspection->submitted_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
                <div>Tanggal Disetujui: <span class="text-emerald-700 font-bold">{{ $inspection->approved_at?->format('d/m/Y H:i') ?? 'Belum' }}</span>
                    @if($inspection->approved_at)
                        @php
                            $cardApprovalRecord = $inspection->approvals->where('decision', \App\Enums\ApprovalDecision::APPROVED)->last();
                        @endphp
                        @if($cardApprovalRecord && $cardApprovalRecord->signature_type === 'manual')
                            <span class="text-[10px] bg-sky-50 text-sky-700 font-semibold px-1.5 py-0.5 rounded border border-sky-200 ml-1">Manual</span>
                        @elseif($cardApprovalRecord)
                            <span class="text-[10px] bg-emerald-50 text-emerald-700 font-semibold px-1.5 py-0.5 rounded border border-emerald-200 ml-1">Digital</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 12 Criteria Checklist Result Review -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Hasil Pemeriksaan 12 Kriteria Keamanan</h2>
                <p class="text-xs text-gray-500">Standar kepatuhan FR.SM/TI/017.002/10-2020</p>
            </div>
            @if($inspection->checklist)
                @php $negCount = $inspection->checklist->getNegativeFindingsCount(); @endphp
                @if($negCount > 0)
                    <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">
                        {{ $negCount }} Temuan Kepatuhan
                    </span>
                @else
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                        100% Memenuhi Standar
                    </span>
                @endif
            @endif
        </div>

        @if($inspection->checklist)
        @php $c = $inspection->checklist; @endphp
        <div class="divide-y divide-gray-100 text-xs">
            <div class="p-4 flex items-center justify-between {{ $c->nda_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>1. Formulir NDA</strong> @if($c->nda_notes)<span class="text-gray-500">({{ $c->nda_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->nda_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->nda_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->strong_password_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>2. Strong Password (Kombinasi 8 Karakter)</strong> @if($c->strong_password_notes)<span class="text-gray-500">({{ $c->strong_password_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->strong_password_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->strong_password_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->screensaver_lock_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>3. Screensaver Lock Otomatis (Maks 15 Menit)</strong> @if($c->screensaver_lock_notes)<span class="text-gray-500">({{ $c->screensaver_lock_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->screensaver_lock_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->screensaver_lock_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->cleardesk_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>4. Clear Desk & Clear Screen</strong> @if($c->cleardesk_notes)<span class="text-gray-500">({{ $c->cleardesk_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->cleardesk_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->cleardesk_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->prohibited_files_status === 'ADA' ? 'bg-rose-50/50' : '' }}">
                <div><strong>5. File Terlarang / Crack / Torrent</strong> @if($c->prohibited_files_notes)<span class="text-gray-500">({{ $c->prohibited_files_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->prohibited_files_status === 'TIDAK_ADA' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->prohibited_files_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->antivirus_install_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>6. Antivirus Resmi Terinstal</strong> <span class="text-gray-500">({{ $c->antivirus_name ?? 'Tidak ada' }})</span></div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->antivirus_install_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->antivirus_install_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->antivirus_update_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>7. Definisi Antivirus Terupdate</strong> @if($c->antivirus_update_notes)<span class="text-gray-500">({{ $c->antivirus_update_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->antivirus_update_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->antivirus_update_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->full_scan_schedule_status === 'BELUM' ? 'bg-rose-50/50' : '' }}">
                <div><strong>8. Jadwal Full Scan Mingguan Terpasang</strong> @if($c->full_scan_schedule_notes)<span class="text-gray-500">({{ $c->full_scan_schedule_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->full_scan_schedule_status === 'SUDAH' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->full_scan_schedule_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->os_license_status === 'TIDAK' ? 'bg-rose-50/50' : '' }}">
                <div><strong>9. Lisensi OS Resmi Legal</strong> @if($c->os_license_notes)<span class="text-gray-500">({{ $c->os_license_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->os_license_status === 'YA' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->os_license_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->ntp_sync_status === 'TIDAK' ? 'bg-rose-50/50' : '' }}">
                <div><strong>10. Sinkronisasi NTP Server KAI</strong> @if($c->ntp_sync_notes)<span class="text-gray-500">({{ $c->ntp_sync_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->ntp_sync_status === 'YA' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->ntp_sync_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->pc_label_status === 'TIDAK' ? 'bg-rose-50/50' : '' }}">
                <div><strong>11. Stiker Label Fisik PC / Aset</strong> @if($c->pc_label_notes)<span class="text-gray-500">({{ $c->pc_label_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->pc_label_status === 'YA' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $c->pc_label_status }}</span>
            </div>
            <div class="p-4 flex items-center justify-between {{ $c->special_access?->value === 'DOMAIN_ADMIN' ? 'bg-rose-50/50' : '' }}">
                <div><strong>12. Hak Akses Khusus</strong> @if($c->special_access_notes)<span class="text-gray-500">({{ $c->special_access_notes }})</span>@endif</div>
                <span class="px-2 py-0.5 rounded font-bold {{ $c->special_access?->value === 'DOMAIN_ADMIN' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-800' }}">{{ $c->special_access?->label() ?? 'NONE' }}</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Attachments Gallery -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Berkas Bukti / Lampiran ({{ $inspection->attachments->count() }})</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @forelse($inspection->attachments as $att)
            <div class="border border-gray-200 rounded-lg p-3 text-xs flex items-center justify-between">
                <div>
                    <span class="font-semibold text-gray-800 block">{{ $att->file_name }}</span>
                    <span class="text-[11px] text-gray-400">{{ $att->attachment_type->label() }}</span>
                </div>
                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="text-kai-blue font-bold hover:underline">
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

    <!-- Action Section -->
    @if($inspection->status === \App\Enums\InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL)
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h2 class="text-lg font-bold text-slate-900">Keputusan Pengesahan Super Admin</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih metode pengesahan resmi atau kembalikan ke Admin Wilayah untuk perbaikan data.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Approve with Digital Signature Form -->
            <form action="{{ route('super-admin.inspections.handle-approval', $inspection) }}" method="POST" class="p-5 border border-emerald-200 rounded-2xl bg-emerald-50/30 flex flex-col justify-between">
                @csrf
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="signature_type" value="digital">
                <div>
                    <h3 class="font-bold text-emerald-900 text-sm flex items-center mb-1">
                        <svg class="w-5 h-5 mr-1.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Setujui Resmi (Approve & ttd digital)
                    </h3>
                    <p class="text-xs text-emerald-700 mb-2.5 leading-relaxed">Menyatakan pemeriksaan ini sah dan menyertakan tanda tangan digital resmi pada laporan PDF.</p>
                    @if(Auth::user()->signature_path)
                        <div class="text-[11px] text-emerald-700/80 mb-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Tanda tangan digital akun Anda aktif.
                        </div>
                    @else
                        <div class="text-[11px] text-amber-600 mb-2 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Belum ada tanda tangan di profil.
                        </div>
                    @endif
                    <textarea name="comment" rows="2" placeholder="Catatan persetujuan digital (opsional)..." class="w-full text-xs border border-emerald-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 bg-white"></textarea>
                </div>
                <button type="submit" class="mt-4 w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-[0.98] flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Setujui Resmi (Approve & ttd digital)
                </button>
            </form>

            <!-- Approve with Manual Signature Form -->
            <form action="{{ route('super-admin.inspections.handle-approval', $inspection) }}" method="POST" class="p-5 border border-sky-200 rounded-2xl bg-sky-50/30 flex flex-col justify-between">
                @csrf
                <input type="hidden" name="action" value="approve_manual">
                <input type="hidden" name="signature_type" value="manual">
                <div>
                    <h3 class="font-bold text-sky-900 text-sm flex items-center mb-1">
                        <svg class="w-5 h-5 mr-1.5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Setujui Resmi (Approve & ttd manual)
                    </h3>
                    <p class="text-xs text-sky-700 mb-2.5 leading-relaxed">Menyatakan pemeriksaan sah tanpa tanda tangan digital pada laporan (disiapkan untuk tanda tangan basah/manual).</p>
                    <div class="text-[11px] text-sky-700/80 mb-2 flex items-center gap-1">
                        <i class="fa-solid fa-file-lines text-sky-600"></i> Kolom tanda tangan pada laporan akan dikosongkan.
                    </div>
                    <textarea name="comment" rows="2" placeholder="Catatan persetujuan manual (opsional)..." class="w-full text-xs border border-sky-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500/20 bg-white"></textarea>
                </div>
                <button type="submit" class="mt-4 w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-[0.98] flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Setujui Resmi (Approve & ttd manual)
                </button>
            </form>

            <!-- Return to Admin Form -->
            <form action="{{ route('super-admin.inspections.handle-approval', $inspection) }}" method="POST" class="p-5 border border-amber-200 rounded-2xl bg-amber-50/30 flex flex-col justify-between">
                @csrf
                <input type="hidden" name="action" value="return">
                <div>
                    <h3 class="font-bold text-amber-900 text-sm flex items-center mb-1">
                        <svg class="w-5 h-5 mr-1.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Kembalikan ke Admin (Return)
                    </h3>
                    <p class="text-xs text-amber-700 mb-2.5 leading-relaxed">Jika terdapat keraguan atau ketidaksesuaian data, kembalikan ke Admin Wilayah beserta catatan perbaikan.</p>
                    <textarea name="reason" rows="2" required placeholder="Alasan pengembalian ke Admin (wajib)..." class="w-full text-xs border border-amber-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-amber-500/20 bg-white"></textarea>
                </div>
                <button type="submit" class="mt-4 w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-[0.98] flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembalikan ke Admin (Return)
                </button>
            </form>
        </div>
    </div>
    @elseif($inspection->status === \App\Enums\InspectionStatus::APPROVED)
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @php
                $approvalRecord = $inspection->approvals->where('decision', \App\Enums\ApprovalDecision::APPROVED)->last();
            @endphp
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-emerald-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Pemeriksaan ini telah disetujui resmi.
                </span>
                @if($approvalRecord && $approvalRecord->signature_type === 'manual')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                        <i class="fa-solid fa-pen-nib mr-1 text-sky-600"></i> TTD Manual
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i class="fa-solid fa-signature mr-1 text-emerald-600"></i> TTD Digital
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Anda dapat mengunci data untuk mencegah manipulasi, atau mengarsipkan berkas.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('super-admin.inspections.lock', $inspection) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs active:scale-[0.98]">
                    <i class="fa-solid fa-lock mr-1"></i> Kunci (Lock)
                </button>
            </form>
            <form action="{{ route('super-admin.inspections.archive', $inspection) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-xs active:scale-[0.98]">
                    <i class="fa-solid fa-box-archive mr-1"></i> Arsipkan
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
