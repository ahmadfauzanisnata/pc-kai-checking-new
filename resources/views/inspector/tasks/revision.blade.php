@extends('layouts.app')

@section('title', 'Perbaikan Pemeriksaan: ' . $inspection->reference_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header Back -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('inspector.tasks.show', $inspection) }}" class="inline-flex items-center text-sm font-semibold text-kai-navy hover:text-kai-orange transition">
                &larr; Kembali ke Detail Tugas
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Perbaikan Hasil Pemeriksaan</h1>
            <p class="text-sm text-gray-500">Ref: <span class="font-semibold text-gray-800">{{ $inspection->reference_number }}</span></p>
        </div>
        <x-status-badge :status="$inspection->status" />
    </div>

    <!-- Alert / Revision Reason Box -->
    <div class="bg-rose-50 border-l-4 border-rose-500 p-5 rounded-r-xl shadow-xs">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-rose-600 mt-0.5 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h3 class="text-base font-bold text-rose-900">Pemeriksaan Memerlukan Revisi oleh Admin</h3>
                <p class="text-sm text-rose-700 mt-1 font-medium">Catatan / Alasan Pengembalian dari Admin:</p>
                <div class="mt-2 bg-white/80 p-3 rounded-lg border border-rose-200 text-sm text-gray-800 italic">
                    "{{ $lastRevisionNote?->content ?? ($lastHistory?->comment ?? 'Mohon periksa dan lengkapi kembali checklist pemeriksaan serta bukti lampiran yang diminta.') }}"
                </div>
            </div>
        </div>
    </div>

    <!-- Resubmit Form -->
    <form action="{{ route('inspector.tasks.resubmit', $inspection) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Resubmit Explanation / Notes -->
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-xs">
            <h2 class="text-base font-bold text-gray-900 mb-2">Penjelasan Perbaikan dari Pemeriksa</h2>
            <p class="text-xs text-gray-500 mb-3">Tuliskan ringkasan perbaikan atau klarifikasi yang Anda lakukan untuk disampaikan kembali ke Admin.</p>
            <textarea name="notes" rows="3" required class="w-full text-sm border-slate-300 rounded-xl shadow-xs focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none" placeholder="Contoh: Lampiran screenshot antivirus telah ditambahkan dan status proteksi diperbarui..."></textarea>
        </div>

        <!-- Checklist Form Component / Pre-filled -->
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-xs space-y-6">
            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Formulir 12 Kriteria Pemeriksaan Keamanan</h2>

            <!-- 1. Form NDA -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">1. Formulir NDA (Non-Disclosure Agreement)</label>
                    <span class="text-xs text-gray-500">Kepatuhan Kerahasiaan Informasi</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="nda_status" value="SUDAH" {{ old('nda_status', $inspection->checklist?->nda_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="nda_status" value="BELUM" {{ old('nda_status', $inspection->checklist?->nda_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="nda_notes" value="{{ old('nda_notes', $inspection->checklist?->nda_notes) }}" placeholder="Catatan NDA..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 2. Strong Password -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">2. Password Kompleks (Strong Password)</label>
                    <span class="text-xs text-gray-500">Min 8 Karakter, Kombinasi Huruf, Angka & Simbol</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="strong_password_status" value="SUDAH" {{ old('strong_password_status', $inspection->checklist?->strong_password_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="strong_password_status" value="BELUM" {{ old('strong_password_status', $inspection->checklist?->strong_password_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="strong_password_notes" value="{{ old('strong_password_notes', $inspection->checklist?->strong_password_notes) }}" placeholder="Catatan Password..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 3. Screensaver Lock -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">3. Screensaver Lock Otomatis (Maks 15 Menit)</label>
                    <span class="text-xs text-gray-500">Kunci otomatis saat ditinggalkan</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="screensaver_lock_status" value="SUDAH" {{ old('screensaver_lock_status', $inspection->checklist?->screensaver_lock_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="screensaver_lock_status" value="BELUM" {{ old('screensaver_lock_status', $inspection->checklist?->screensaver_lock_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="screensaver_lock_notes" value="{{ old('screensaver_lock_notes', $inspection->checklist?->screensaver_lock_notes) }}" placeholder="Catatan Screensaver..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 4. Clear Desk & Clear Screen -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">4. Kepatuhan Clear Desk & Clear Screen</label>
                    <span class="text-xs text-gray-500">Tidak ada dokumen rahasia tergeletak</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="cleardesk_status" value="SUDAH" {{ old('cleardesk_status', $inspection->checklist?->cleardesk_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="cleardesk_status" value="BELUM" {{ old('cleardesk_status', $inspection->checklist?->cleardesk_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="cleardesk_notes" value="{{ old('cleardesk_notes', $inspection->checklist?->cleardesk_notes) }}" placeholder="Catatan Clear Desk..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 5. Prohibited Files -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">5. Keberadaan File Terlarang / Berbahaya</label>
                    <span class="text-xs text-gray-500">Keygen, Crack, Pirated Software, Torrent, dll</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="prohibited_files_status" value="TIDAK" {{ in_array(old('prohibited_files_status', $inspection->checklist?->prohibited_files_status), ['TIDAK', 'TIDAK_ADA']) ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">TIDAK ADA (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="prohibited_files_status" value="ADA" {{ old('prohibited_files_status', $inspection->checklist?->prohibited_files_status) === 'ADA' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">ADA (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="prohibited_files_notes" value="{{ old('prohibited_files_notes', $inspection->checklist?->prohibited_files_notes) }}" placeholder="Catatan file terlarang..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 6. Antivirus Install -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">6. Instalasi Antivirus Resmi KAI</label>
                    <span class="text-xs text-gray-500">Antivirus terinstal dan aktif</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="antivirus_install_status" value="SUDAH" {{ old('antivirus_install_status', $inspection->checklist?->antivirus_install_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="antivirus_install_status" value="BELUM" {{ old('antivirus_install_status', $inspection->checklist?->antivirus_install_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="antivirus_name" value="{{ old('antivirus_name', $inspection->checklist?->antivirus_name) }}" placeholder="Nama & Versi Antivirus..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 7. Antivirus Update -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">7. Pembaruan Definisi Antivirus</label>
                    <span class="text-xs text-gray-500">Signature virus up-to-date</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="antivirus_update_status" value="SUDAH" {{ old('antivirus_update_status', $inspection->checklist?->antivirus_update_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="antivirus_update_status" value="BELUM" {{ old('antivirus_update_status', $inspection->checklist?->antivirus_update_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="antivirus_update_notes" value="{{ old('antivirus_update_notes', $inspection->checklist?->antivirus_update_notes) }}" placeholder="Catatan update..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 8. Full Scan Schedule -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">8. Jadwal Full Scan Mingguan</label>
                    <span class="text-xs text-gray-500">Jadwal pemindaian rutin terpasang</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="full_scan_schedule_status" value="SUDAH" {{ old('full_scan_schedule_status', $inspection->checklist?->full_scan_schedule_status) === 'SUDAH' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">SUDAH (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="full_scan_schedule_status" value="BELUM" {{ old('full_scan_schedule_status', $inspection->checklist?->full_scan_schedule_status) === 'BELUM' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">BELUM (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="full_scan_schedule_notes" value="{{ old('full_scan_schedule_notes', $inspection->checklist?->full_scan_schedule_notes) }}" placeholder="Catatan jadwal scan..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 9. OS License -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">9. Lisensi Sistem Operasi Legal</label>
                    <span class="text-xs text-gray-500">Windows Resmi Teraktivasi</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="os_license_status" value="LICENSE" {{ in_array(old('os_license_status', $inspection->checklist?->os_license_status), ['LICENSE', 'YA']) ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">LICENSE (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="os_license_status" value="TIDAK" {{ old('os_license_status', $inspection->checklist?->os_license_status) === 'TIDAK' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">TIDAK (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="os_license_notes" value="{{ old('os_license_notes', $inspection->checklist?->os_license_notes) }}" placeholder="Catatan lisensi OS..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 10. NTP Sync -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">10. Sinkronisasi Jam ke NTP Server KAI</label>
                    <span class="text-xs text-gray-500">Waktu terhubung ke server waktu internal</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="ntp_sync_status" value="YA" {{ old('ntp_sync_status', $inspection->checklist?->ntp_sync_status) === 'YA' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">YA (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="ntp_sync_status" value="TIDAK" {{ old('ntp_sync_status', $inspection->checklist?->ntp_sync_status) === 'TIDAK' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">TIDAK (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="ntp_sync_notes" value="{{ old('ntp_sync_notes', $inspection->checklist?->ntp_sync_notes) }}" placeholder="Catatan NTP Server..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 11. Label PC / Aset -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">11. Stiker Label Fisik PC / Aset KAI</label>
                    <span class="text-xs text-gray-500">Stiker nomor aset tertempel rapi di fisik</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <label class="inline-flex items-center">
                        <input type="radio" name="pc_label_status" value="ADA" {{ in_array(old('pc_label_status', $inspection->checklist?->pc_label_status), ['ADA', 'YA']) ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-emerald-700">ADA (Sesuai)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="pc_label_status" value="TIDAK" {{ old('pc_label_status', $inspection->checklist?->pc_label_status) === 'TIDAK' ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 font-medium text-rose-700">TIDAK (Temuan)</span>
                    </label>
                </div>
                <input type="text" name="pc_label_notes" value="{{ old('pc_label_notes', $inspection->checklist?->pc_label_notes) }}" placeholder="Catatan label aset..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 12. Special Access -->
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-900">12. Hak Akses Khusus / Hak Administrator Lokal</label>
                    <span class="text-xs text-gray-500">Tingkat hak akses pegawai di perangkat</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <label class="inline-flex items-center p-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="special_access" value="ADMIN" {{ in_array(old('special_access', is_object($inspection->checklist?->special_access) ? $inspection->checklist->special_access->value : $inspection->checklist?->special_access), ['ADMIN', 'LOCAL_ADMIN', 'DOMAIN_ADMIN']) ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 text-slate-800 font-medium">Administrator (Admin)</span>
                    </label>
                    <label class="inline-flex items-center p-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="special_access" value="USER" {{ in_array(old('special_access', is_object($inspection->checklist?->special_access) ? $inspection->checklist->special_access->value : $inspection->checklist?->special_access), ['USER', 'NONE']) ? 'checked' : '' }} class="text-kai-orange focus:ring-kai-orange">
                        <span class="ml-2 text-slate-800 font-medium">Pengguna Standar (User)</span>
                    </label>
                </div>
                <input type="text" name="special_access_notes" value="{{ old('special_access_notes', $inspection->checklist?->special_access_notes) }}" placeholder="Catatan hak akses khusus..." class="w-full text-xs border-slate-300 rounded-lg focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('inspector.tasks.show', $inspection) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="btn-kai px-6 py-2.5 rounded-xl text-white text-sm font-bold shadow-md transition active:scale-[0.98]">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> Kirim Ulang Pemeriksaan (Resubmit) &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
