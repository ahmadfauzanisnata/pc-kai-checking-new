@extends('layouts.app')

@section('title', 'Form Pemeriksaan: ' . $inspection->reference_number)
@section('page_title', 'Form Checklist Pemeriksaan')
@section('page_subtitle', $inspection->reference_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    step: 2,
    answers: {
        nda_status: '{{ old('nda_status', $inspection->checklist?->nda_status ?? '') }}',
        strong_password_status: '{{ old('strong_password_status', $inspection->checklist?->strong_password_status ?? '') }}',
        screensaver_lock_status: '{{ old('screensaver_lock_status', $inspection->checklist?->screensaver_lock_status ?? '') }}',
        special_access: '{{ old('special_access', $inspection->checklist?->special_access ?? '') }}',
        cleardesk_status: '{{ old('cleardesk_status', $inspection->checklist?->cleardesk_status ?? '') }}',
        prohibited_files_status: '{{ old('prohibited_files_status', $inspection->checklist?->prohibited_files_status ?? '') }}',
        antivirus_install_status: '{{ old('antivirus_install_status', $inspection->checklist?->antivirus_install_status ?? '') }}',
        antivirus_update_status: '{{ old('antivirus_update_status', $inspection->checklist?->antivirus_update_status ?? '') }}',
        full_scan_schedule_status: '{{ old('full_scan_schedule_status', $inspection->checklist?->full_scan_schedule_status ?? '') }}',
        os_license_status: '{{ old('os_license_status', $inspection->checklist?->os_license_status ?? '') }}',
        ntp_sync_status: '{{ old('ntp_sync_status', $inspection->checklist?->ntp_sync_status ?? '') }}',
        pc_label_status: '{{ old('pc_label_status', $inspection->checklist?->pc_label_status ?? '') }}',
        finding_summary: '{{ old('finding_summary', $inspection->checklist?->finding_summary ?? '') }}',
        inspection_date: '{{ old('inspection_date', $inspection->inspection_date?->format('Y-m-d') ?? date('Y-m-d')) }}',
        statement: false
    },
    countFilled() {
        let count = 0;
        const fields = ['nda_status', 'strong_password_status', 'screensaver_lock_status', 'special_access', 'cleardesk_status', 'prohibited_files_status', 'antivirus_install_status', 'antivirus_update_status', 'full_scan_schedule_status', 'os_license_status', 'ntp_sync_status', 'pc_label_status'];
        fields.forEach(f => { if (this.answers[f]) count++; });
        return count;
    },
    countFindings() {
        let count = 0;
        if (this.answers.nda_status === 'BELUM') count++;
        if (this.answers.strong_password_status === 'BELUM') count++;
        if (this.answers.screensaver_lock_status === 'BELUM') count++;
        if (this.answers.cleardesk_status === 'BELUM') count++;
        if (this.answers.prohibited_files_status === 'ADA') count++;
        if (this.answers.antivirus_install_status === 'BELUM') count++;
        if (this.answers.antivirus_update_status === 'BELUM') count++;
        if (this.answers.full_scan_schedule_status === 'BELUM') count++;
        if (this.answers.os_license_status === 'TIDAK') count++;
        if (this.answers.ntp_sync_status === 'TIDAK') count++;
        if (this.answers.pc_label_status === 'TIDAK') count++;
        return count;
    },
    hasFindings() {
        return this.countFindings() > 0;
    }
}">
    <!-- Step Header Wizard -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200/80">
        <div class="flex items-center justify-between">
            <!-- Step 1 -->
            <button type="button" @click="step = 1" class="flex items-center gap-2.5 text-left focus:outline-none">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition"
                    :class="step === 1 ? 'sidebar-active text-white ring-4 ring-kai-orange/20' : 'bg-slate-100 text-slate-600'">
                    1
                </div>
                <div class="hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">Langkah 1</div>
                    <div class="text-[11px] text-slate-400">Info Pegawai & PC</div>
                </div>
            </button>

            <div class="h-0.5 w-12 sm:w-20 bg-slate-200"></div>

            <!-- Step 2 -->
            <button type="button" @click="step = 2" class="flex items-center gap-2.5 text-left focus:outline-none">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition"
                    :class="step === 2 ? 'sidebar-active text-white ring-4 ring-kai-orange/20' : 'bg-slate-100 text-slate-600'">
                    2
                </div>
                <div class="hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">Langkah 2</div>
                    <div class="text-[11px] text-slate-400">Pengisian Checklist</div>
                </div>
            </button>

            <div class="h-0.5 w-12 sm:w-20 bg-slate-200"></div>

            <!-- Step 3 -->
            <button type="button" @click="step = 3" class="flex items-center gap-2.5 text-left focus:outline-none">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition"
                    :class="step === 3 ? 'sidebar-active text-white ring-4 ring-kai-orange/20' : 'bg-slate-100 text-slate-600'">
                    3
                </div>
                <div class="hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">Langkah 3</div>
                    <div class="text-[11px] text-slate-400">Ringkasan & Kirim</div>
                </div>
            </button>
        </div>
    </div>

    <!-- Main Checklist Form -->
    <form id="inspection-form" method="POST" action="{{ route('inspector.tasks.submit', $inspection) }}">
        @csrf
        @method('PUT')

        <!-- Hidden input to switch between draft and submit -->
        <input type="hidden" name="is_draft" id="is_draft_input" value="0">

        <!-- ============================================== -->
        <!-- LANGKAH 1: INFO PEGAWAI & PERANGKAT            -->
        <!-- ============================================== -->
        <div x-show="step === 1" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
                <h3 class="text-base font-bold text-slate-800 pb-3 border-b border-slate-100">
                    Informasi Tugas & Penanggung Jawab
                </h3>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Nomor Referensi</span>
                        <span class="font-mono font-bold text-kai-navy text-sm">{{ $inspection->reference_number }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Periode Pemeriksaan</span>
                        <span class="font-semibold text-slate-800">{{ $inspection->inspectionPeriod?->name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Pegawai Penanggung Jawab</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $inspection->employee?->name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Nomor NIPP</span>
                        <span class="font-mono text-slate-700 font-semibold">{{ $inspection->employee?->nipp }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Unit Kerja</span>
                        <span class="font-medium text-slate-800">{{ $inspection->employee?->unit?->name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Business Area</span>
                        <span class="font-medium text-slate-800">{{ $inspection->employee?->unit?->businessArea?->name }}</span>
                    </div>
                </div>

                <h3 class="text-base font-bold text-slate-800 pb-3 border-b border-slate-100 mt-8">
                    Identitas & Spesifikasi Perangkat
                </h3>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Nomor Aset</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $inspection->device?->asset_number }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Jenis Perangkat</span>
                        <span class="font-semibold text-slate-800">{{ $inspection->device?->device_type?->label() }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Merk / Model</span>
                        <span class="font-medium text-slate-800">{{ $inspection->device?->brand }} {{ $inspection->device?->model }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Hostname</span>
                        <span class="font-mono text-slate-800 font-medium">{{ $inspection->device?->hostname ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Nomor Seri</span>
                        <span class="font-mono text-slate-600">{{ $inspection->device?->serial_number ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Sistem Operasi</span>
                        <span class="font-medium text-slate-800">{{ $inspection->device?->operating_system }} {{ $inspection->device?->os_version }}</span>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="button" @click="step = 2"
                        class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition flex items-center gap-1.5 active:scale-[0.98]">
                        Lanjut ke Checklist <i class="fa-solid fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- LANGKAH 2: 12 CHECKLIST ITEMS                  -->
        <!-- ============================================== -->
        <div x-show="step === 2" class="space-y-6">
            <!-- Inspection Date Input -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Tanggal Pelaksanaan Pemeriksaan <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-xs text-slate-400">Tanggal ketika pemeriksaan fisik dan audit perangkat dilakukan</p>
                </div>
                <input type="date" name="inspection_date" x-model="answers.inspection_date" required
                    class="px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <!-- 12 Items Cards -->
            <div class="space-y-4">
                <!-- 1. NDA -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.nda_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 1 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">NDA (Non-Disclosure Agreement)</h4>
                            <p class="text-xs text-slate-500">Pegawai telah menandatangani pakta kerahasiaan informasi perusahaan</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.nda_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="nda_status" value="SUDAH" x-model="answers.nda_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.nda_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="nda_status" value="BELUM" x-model="answers.nda_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. Strong Password -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.strong_password_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 2 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Login Strong Password</h4>
                            <p class="text-xs text-slate-500">Kata sandi akun Windows memenuhi kriteria kerumitan (huruf besar, kecil, angka, simbol)</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.strong_password_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="strong_password_status" value="SUDAH" x-model="answers.strong_password_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.strong_password_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="strong_password_status" value="BELUM" x-model="answers.strong_password_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Screensaver Lock -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.screensaver_lock_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 3 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Screensaver Lock Maksimal 5 Menit</h4>
                            <p class="text-xs text-slate-500">Perangkat terkunci otomatis ketika ditinggalkan maksimal selama 5 menit</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.screensaver_lock_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="screensaver_lock_status" value="SUDAH" x-model="answers.screensaver_lock_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.screensaver_lock_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="screensaver_lock_status" value="BELUM" x-model="answers.screensaver_lock_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 4. Special Access -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 4 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Hak Akses Khusus</h4>
                            <p class="text-xs text-slate-500">Tipe privilege login akun pengguna pada sistem operasi lokal</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.special_access === 'ADMIN' ? 'bg-amber-600 text-white border-amber-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="special_access" value="ADMIN" x-model="answers.special_access" class="hidden"> Admin
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.special_access === 'USER' ? 'bg-kai-navy text-white border-kai-navy' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="special_access" value="USER" x-model="answers.special_access" class="hidden"> User
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 5. Cleardesk -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.cleardesk_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 5 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Cleardesk Policy</h4>
                            <p class="text-xs text-slate-500">Meja kerja bersih dari dokumen konfidensial atau password tertulis</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.cleardesk_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="cleardesk_status" value="SUDAH" x-model="answers.cleardesk_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.cleardesk_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="cleardesk_status" value="BELUM" x-model="answers.cleardesk_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 6. Prohibited Files -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.prohibited_files_status === 'ADA' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 6 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">File MP3, Video, dan Sejenisnya</h4>
                            <p class="text-xs text-slate-500">Tidak ada file multimedia bajakan, game, atau file non-pekerjaan</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.prohibited_files_status === 'TIDAK' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="prohibited_files_status" value="TIDAK" x-model="answers.prohibited_files_status" class="hidden"> Tidak Ada
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.prohibited_files_status === 'ADA' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="prohibited_files_status" value="ADA" x-model="answers.prohibited_files_status" class="hidden"> Ada (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 7. Antivirus Install -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.antivirus_install_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 7 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Antivirus Status Install</h4>
                            <p class="text-xs text-slate-500">Antivirus korporat resmi telah terpasang dan berjalan aktif</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.antivirus_install_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="antivirus_install_status" value="SUDAH" x-model="answers.antivirus_install_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.antivirus_install_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="antivirus_install_status" value="BELUM" x-model="answers.antivirus_install_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 8. Antivirus Update -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.antivirus_update_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 8 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Antivirus Status Update</h4>
                            <p class="text-xs text-slate-500">Signature virus dan database perlindungan diperbarui versi terbaru</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.antivirus_update_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="antivirus_update_status" value="SUDAH" x-model="answers.antivirus_update_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.antivirus_update_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="antivirus_update_status" value="BELUM" x-model="answers.antivirus_update_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 9. Full Scan Schedule -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.full_scan_schedule_status === 'BELUM' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 9 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Antivirus Full Scan Auto Schedule</h4>
                            <p class="text-xs text-slate-500">Jadwal pemindaian menyeluruh secara otomatis telah dikonfigurasi rutin</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.full_scan_schedule_status === 'SUDAH' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="full_scan_schedule_status" value="SUDAH" x-model="answers.full_scan_schedule_status" class="hidden"> Sudah
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.full_scan_schedule_status === 'BELUM' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="full_scan_schedule_status" value="BELUM" x-model="answers.full_scan_schedule_status" class="hidden"> Belum (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 10. OS License -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.os_license_status === 'TIDAK' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 10 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Lisensi Sistem Operasi</h4>
                            <p class="text-xs text-slate-500">Sistem operasi Windows legal, berlisensi resmi, dan teraktivasi KMS/OEM</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.os_license_status === 'LICENSE' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="os_license_status" value="LICENSE" x-model="answers.os_license_status" class="hidden"> License
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.os_license_status === 'TIDAK' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="os_license_status" value="TIDAK" x-model="answers.os_license_status" class="hidden"> Tidak (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 11. NTP Sync -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.ntp_sync_status === 'TIDAK' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 11 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Sinkronisasi NTP Server</h4>
                            <p class="text-xs text-slate-500">Waktu komputer tersinkronisasi tepat dengan NTP Server KAI / Nasional</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.ntp_sync_status === 'YA' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="ntp_sync_status" value="YA" x-model="answers.ntp_sync_status" class="hidden"> Ya
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.ntp_sync_status === 'TIDAK' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="ntp_sync_status" value="TIDAK" x-model="answers.ntp_sync_status" class="hidden"> Tidak (Temuan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 12. PC Label -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition"
                    :class="answers.pc_label_status === 'TIDAK' ? 'border-rose-300 bg-rose-50/20' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item 12 dari 12</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-0.5">Label PC</h4>
                            <p class="text-xs text-slate-500">Stiker nomor aset dan barcode tertempel jelas pada casing CPU / bodi laptop</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.pc_label_status === 'ADA' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="pc_label_status" value="ADA" x-model="answers.pc_label_status" class="hidden"> Ada
                            </label>
                            <label class="px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition"
                                :class="answers.pc_label_status === 'TIDAK' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-300 text-slate-700 hover:bg-slate-50'">
                                <input type="radio" name="pc_label_status" value="TIDAK" x-model="answers.pc_label_status" class="hidden"> Tidak (Temuan)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finding Summary Text Area (Visible & Highlighted if Findings exist) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border transition"
                :class="hasFindings() ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80'">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider"
                        :class="hasFindings() ? 'text-amber-900' : 'text-slate-700'">
                        Catatan Ringkasan Temuan
                        <span x-show="hasFindings()" class="text-rose-600 font-bold ml-1">* (Wajib diisi karena ada temuan)</span>
                    </label>
                    <span x-show="hasFindings()" class="text-xs font-bold text-rose-600">
                        <span x-text="countFindings()"></span> Butir Temuan Ditemukan
                    </span>
                </div>
                <textarea name="finding_summary" x-model="answers.finding_summary" rows="3"
                    placeholder="Uraikan detail temuan ketidaksesuaian yang ditemukan pada perangkat..."
                    class="w-full px-3.5 py-2.5 text-sm border rounded-xl outline-none transition"
                    :class="hasFindings() ? 'border-amber-300 focus:ring-2 focus:ring-amber-500' : 'border-slate-300 focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange'"></textarea>
            </div>

            <!-- General Notes -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Catatan Umum Pemeriksa (Opsional)
                </label>
                <textarea name="general_notes" rows="2" placeholder="Catatan kondisi fisik umum, rekomendasi perawatan, dll..."
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">{{ old('general_notes', $inspection->general_notes) }}</textarea>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="step = 1"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                    &larr; Kembali ke Langkah 1
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="submitDraftForm()"
                        class="px-4 py-2.5 rounded-xl border border-kai-navy text-kai-navy hover:bg-kai-navy/5 font-semibold text-xs sm:text-sm transition">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Draft
                    </button>
                    <button type="button" @click="step = 3"
                        class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-sm transition active:scale-[0.98]">
                        Lanjut ke Ringkasan &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- LANGKAH 3: RINGKASAN, PERNYATAAN & SUBMIT      -->
        <!-- ============================================== -->
        <div x-show="step === 3" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Ringkasan Kelengkapan Jawaban</h3>
                        <p class="text-xs text-slate-500">Tinjau kembali seluruh jawaban sebelum mengirim ke Admin</p>
                    </div>

                    <!-- Progress badge -->
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-bold"
                            :class="countFilled() === 12 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                            <span x-text="countFilled()"></span> dari 12 Terisi
                        </span>
                    </div>
                </div>

                <!-- Findings Banner on Step 3 -->
                <div x-show="hasFindings()" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        <span>Terdapat <span x-text="countFindings()"></span> Butir Temuan Ketidaksesuaian</span>
                    </div>
                    <p class="text-rose-700">Pastikan ringkasan temuan telah diisi dengan jelas dan unggah bukti pendukung bila diperlukan.</p>
                </div>

                <div x-show="!hasFindings() && countFilled() === 12" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span class="font-medium">Seluruh butir checklist terpenuhi dan sesuai dengan standar keamanan KAI.</span>
                </div>

                <!-- Statement Checkbox -->
                <div class="pt-4 border-t border-slate-100">
                    <label class="flex items-start gap-3 cursor-pointer p-4 rounded-xl border border-kai-navy/20 bg-kai-navy/5">
                        <input type="checkbox" name="statement" value="1" x-model="answers.statement" required
                            class="h-4 w-4 mt-0.5 text-kai-orange focus:ring-kai-orange border-slate-300 rounded">
                        <div class="text-xs text-slate-700">
                            <span class="font-bold text-slate-900 block mb-0.5">Pernyataan Integritas Pemeriksa:</span>
                            Saya menyatakan bahwa data pemeriksaan ini telah diisi dengan sebenar-benarnya berdasarkan kondisi fisik dan konfigurasi aktual perangkat di lokasi kerja, serta siap dipertanggungjawabkan dalam audit mutu dan keamanan sistem informasi PT Kereta Api Indonesia (Persero).
                        </div>
                    </label>
                </div>

                <!-- Final Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" @click="step = 2"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                        &larr; Ubah Jawaban Checklist
                    </button>

                    <div class="flex items-center gap-2.5">
                        <button type="button" onclick="submitDraftForm()"
                            class="px-4 py-2.5 rounded-xl border border-kai-navy text-kai-navy hover:bg-kai-navy/5 font-semibold text-xs sm:text-sm transition">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Sebagai Draft
                        </button>

                        <button type="submit" :disabled="countFilled() < 12 || !answers.statement || (hasFindings() && !answers.finding_summary.trim())"
                            class="btn-kai px-6 py-2.5 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs sm:text-sm shadow-md transition active:scale-[0.98]">
                            <i class="fa-solid fa-paper-plane mr-1.5"></i> Kirim Hasil ke Admin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Attachments Box (Always accessible below checklist) -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Lampiran Bukti Pemeriksaan</h3>
                <p class="text-xs text-slate-500">Unggah foto perangkat, screenshot antivirus, atau dokumen bukti</p>
            </div>
        </div>

        <!-- Upload Form -->
        <form method="POST" action="{{ route('inspector.tasks.attachments.upload', $inspection) }}" enctype="multipart/form-data" class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Lampiran</label>
                    <select name="attachment_type" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                        <option value="SCREENSHOT">Tangkapan Layar (Screenshot)</option>
                        <option value="DEVICE_PHOTO">Foto Fisik Perangkat</option>
                        <option value="SUPPORTING_DOCUMENT">Dokumen Pendukung</option>
                        <option value="OTHER">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Berkas (JPG, PNG, PDF)</label>
                    <input type="file" name="file" required accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-kai-orange/10 file:text-kai-orange hover:file:bg-kai-orange/20 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan Singkat</label>
                    <div class="flex gap-2">
                        <input type="text" name="description" placeholder="contoh: Bukti signature antivirus"
                            class="flex-1 px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-kai-navy hover:bg-kai-navy-dark text-white text-xs font-semibold transition">
                            Unggah
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Existing Attachments List -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse ($inspection->attachments as $att)
                <div class="p-3 rounded-xl border border-slate-200 bg-white flex items-center justify-between gap-2 shadow-xs">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-kai-orange/10 text-kai-orange flex items-center justify-center text-sm shrink-0">
                            <i class="fa-solid {{ $att->isPdf() ? 'fa-file-pdf text-rose-500' : 'fa-image text-kai-orange' }}"></i>
                        </div>
                        <div class="overflow-hidden">
                            <a href="{{ $att->file_url }}" target="_blank" class="text-xs font-semibold text-slate-800 hover:text-kai-orange truncate block">
                                {{ $att->original_name }}
                            </a>
                            <span class="text-[10px] text-slate-400 block">{{ $att->attachment_type->label() }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('inspector.tasks.attachments.delete', $att) }}" onsubmit="return confirm('Hapus lampiran ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1" title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-xs text-slate-400 italic">
                    Belum ada berkas lampiran yang diunggah.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function submitDraftForm() {
        const form = document.getElementById('inspection-form');
        form.action = '{{ route('inspector.tasks.draft', $inspection) }}';
        form.submit();
    }
</script>
@endsection
