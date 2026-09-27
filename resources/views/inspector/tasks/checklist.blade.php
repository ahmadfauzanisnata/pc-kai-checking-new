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
    },
    hasEmployeeSig: {{ ($inspection->employee_signature_path || $inspection->employee?->signature_path) ? 'true' : 'false' }},
    employeeSigUrl: '{{ ($inspection->employee_signature_path ? Storage::url($inspection->employee_signature_path) : ($inspection->employee?->signature_path ? Storage::url($inspection->employee?->signature_path) : '')) }}',
    employeeSigData: '',
    showSigModal: false,
    penColor: '#0f172a',
    penWidth: 3.5,
    isDrawing: false,
    hasDrawn: false,
    isSavingSig: false,
    canvas: null,
    ctx: null,
    sigHistory: [],
    lastX: 0,
    lastY: 0,

    openEmployeePad() {
        this.showSigModal = true;
        this.$nextTick(() => {
            this.initSigCanvas();
        });
    },

    closeEmployeePad() {
        this.showSigModal = false;
    },

    setSigColor(color) {
        this.penColor = color;
        if (this.ctx) this.ctx.strokeStyle = color;
    },

    setSigWidth(width) {
        this.penWidth = width;
        if (this.ctx) this.ctx.lineWidth = width;
    },

    initSigCanvas() {
        this.canvas = document.getElementById('employee-sig-canvas');
        if (!this.canvas) return;

        const rect = this.canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;

        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;

        this.ctx = this.canvas.getContext('2d');
        this.ctx.scale(dpr, dpr);
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = this.penColor;
        this.ctx.lineWidth = this.penWidth;

        this.ctx.clearRect(0, 0, rect.width, rect.height);
        this.sigHistory = [];
        this.saveSigState();
        this.hasDrawn = false;

        this.bindSigEvents();
    },

    getSigPos(e) {
        const rect = this.canvas.getBoundingClientRect();
        let clientX = e.clientX;
        let clientY = e.clientY;

        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }

        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    },

    bindSigEvents() {
        if (this.canvas._eventsBound) return;
        this.canvas._eventsBound = true;

        const startDraw = (e) => {
            e.preventDefault();
            this.isDrawing = true;
            const pos = this.getSigPos(e);
            this.lastX = pos.x;
            this.lastY = pos.y;

            this.ctx.strokeStyle = this.penColor;
            this.ctx.fillStyle = this.penColor;
            this.ctx.lineWidth = this.penWidth;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';

            this.ctx.beginPath();
            this.ctx.arc(pos.x, pos.y, this.penWidth / 2, 0, Math.PI * 2);
            this.ctx.fill();

            this.hasDrawn = true;
        };

        const moveDraw = (e) => {
            if (!this.isDrawing) return;
            e.preventDefault();
            const pos = this.getSigPos(e);

            this.ctx.beginPath();
            this.ctx.strokeStyle = this.penColor;
            this.ctx.lineWidth = this.penWidth;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.moveTo(this.lastX, this.lastY);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();

            this.lastX = pos.x;
            this.lastY = pos.y;
        };

        const endDraw = (e) => {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            this.saveSigState();
        };

        this.canvas.addEventListener('mousedown', startDraw);
        this.canvas.addEventListener('mousemove', moveDraw);
        window.addEventListener('mouseup', endDraw);

        this.canvas.addEventListener('touchstart', startDraw, { passive: false });
        this.canvas.addEventListener('touchmove', moveDraw, { passive: false });
        window.addEventListener('touchend', endDraw);
        window.addEventListener('touchcancel', endDraw);
    },

    saveSigState() {
        if (!this.canvas || !this.ctx) return;
        const rect = this.canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        const imageData = this.ctx.getImageData(0, 0, rect.width * dpr, rect.height * dpr);
        this.sigHistory.push(imageData);
        if (this.sigHistory.length > 20) {
            this.sigHistory.shift();
        }
    },

    undoSig() {
        if (this.sigHistory.length > 1) {
            this.sigHistory.pop();
            const prevState = this.sigHistory[this.sigHistory.length - 1];
            this.ctx.putImageData(prevState, 0, 0);
            this.hasDrawn = this.sigHistory.length > 1;
        }
    },

    clearSig() {
        if (!this.canvas || !this.ctx) return;
        const rect = this.canvas.getBoundingClientRect();
        this.ctx.clearRect(0, 0, rect.width, rect.height);
        this.sigHistory = [];
        this.saveSigState();
        this.hasDrawn = false;
    },

    cropSigCanvas(sourceCanvas) {
        const ctx = sourceCanvas.getContext('2d');
        const width = sourceCanvas.width;
        const height = sourceCanvas.height;
        const imgData = ctx.getImageData(0, 0, width, height);
        const data = imgData.data;

        let minX = width, minY = height, maxX = 0, maxY = 0;
        let found = false;

        for (let y = 0; y < height; y++) {
            for (let x = 0; x < width; x++) {
                const alpha = data[(y * width + x) * 4 + 3];
                if (alpha > 10) {
                    found = true;
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }

        if (!found) return sourceCanvas.toDataURL('image/png');

        const padding = 16;
        minX = Math.max(0, minX - padding);
        minY = Math.max(0, minY - padding);
        maxX = Math.min(width, maxX + padding);
        maxY = Math.min(height, maxY + padding);

        const croppedWidth = maxX - minX;
        const croppedHeight = maxY - minY;

        const croppedCanvas = document.createElement('canvas');
        croppedCanvas.width = croppedWidth;
        croppedCanvas.height = croppedHeight;
        const croppedCtx = croppedCanvas.getContext('2d');
        croppedCtx.drawImage(sourceCanvas, minX, minY, croppedWidth, croppedHeight, 0, 0, croppedWidth, croppedHeight);

        return croppedCanvas.toDataURL('image/png');
    },

    async saveEmployeeSignature() {
        if (!this.hasDrawn) {
            Swal.fire({
                icon: 'warning',
                title: 'Kanvas Masih Kosong',
                text: 'Silakan minta pegawai membubuhkan coretan paraf terlebih dahulu sebelum menyimpan.',
                confirmButtonColor: '#F47920',
            });
            return;
        }

        this.isSavingSig = true;

        try {
            const dataUrl = this.cropSigCanvas(this.canvas);
            this.employeeSigData = dataUrl;
            this.employeeSigUrl = dataUrl;
            this.hasEmployeeSig = true;

            const response = await fetch('{{ route('inspector.tasks.employee-signature', $inspection) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    signature_data: dataUrl
                })
            });

            const result = await response.json();

            if (result.success) {
                if (result.signature_url) {
                    this.employeeSigUrl = result.signature_url;
                }
                this.closeEmployeePad();
                Swal.fire({
                    icon: 'success',
                    title: 'Paraf Berhasil Disimpan',
                    text: result.message || 'Spesimen paraf pegawai telah tersimpan.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                throw new Error(result.message || 'Gagal menyimpan paraf.');
            }
        } catch (error) {
            console.error('Error saving signature:', error);
            this.closeEmployeePad();
            Swal.fire({
                icon: 'info',
                title: 'Paraf Tersimpan di Formulir',
                text: 'Paraf pegawai akan disimpan otomatis ke sistem saat formulir disimpan.',
                confirmButtonColor: '#002B66'
            });
        } finally {
            this.isSavingSig = false;
        }
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
        <input type="hidden" name="employee_signature_data" id="employee_signature_data_input" :value="employeeSigData">

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

            <!-- Paraf Pegawai Yang Bersangkutan Card in Step 2 -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 transition hover:shadow-md">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-kai-orange/10 text-kai-orange flex items-center justify-center text-xl shrink-0 shadow-xs">
                            <i class="fa-solid fa-signature"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h4 class="text-sm font-bold text-slate-900">Paraf Pegawai Yang Bersangkutan</h4>
                                <span x-show="hasEmployeeSig" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check text-xs"></i> Sudah Diparaf
                                </span>
                                <span x-show="!hasEmployeeSig" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-clock text-xs"></i> Belum Diparaf
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Pegawai Penanggung Jawab: <span class="font-semibold text-slate-800">{{ $inspection->employee?->name }}</span>
                                (NIPP: <span class="font-mono font-medium">{{ $inspection->employee?->nipp }}</span>)
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Minta pegawai membubuhkan coret paraf langsung pada layar setelah pemeriksaan perangkat selesai.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end md:self-center shrink-0">
                        <template x-if="hasEmployeeSig && employeeSigUrl">
                            <div class="h-14 px-3.5 py-1.5 bg-slate-50/80 rounded-xl border border-slate-200/80 flex items-center justify-center shadow-2xs">
                                <img :src="employeeSigUrl" alt="Paraf Pegawai" class="max-h-11 max-w-[120px] object-contain">
                            </div>
                        </template>
                        <button type="button" @click="openEmployeePad()"
                            class="px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm transition flex items-center gap-2 shadow-xs active:scale-[0.98]"
                            :class="hasEmployeeSig ? 'border border-slate-300 text-slate-700 hover:bg-slate-50 bg-white' : 'btn-kai text-white'">
                            <i class="fa-solid fa-pen-nib"></i>
                            <span x-text="hasEmployeeSig ? 'Coret Ulang Paraf' : 'Buka Pad Paraf Pegawai'"></span>
                        </button>
                    </div>
                </div>
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

                <!-- Paraf Pegawai Verification Card in Step 3 -->
                <div class="p-4 sm:p-5 rounded-2xl border transition"
                    :class="hasEmployeeSig ? 'bg-emerald-50/40 border-emerald-200' : 'bg-amber-50/40 border-amber-200'">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-base"
                                :class="hasEmployeeSig ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                <i class="fa-solid" :class="hasEmployeeSig ? 'fa-signature' : 'fa-triangle-exclamation'"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                    <span>Paraf Pegawai: {{ $inspection->employee?->name }}</span>
                                    <span x-show="hasEmployeeSig" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Terisi</span>
                                    <span x-show="!hasEmployeeSig" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Perlu Diisi</span>
                                </div>
                                <div class="text-[11px] mt-0.5" :class="hasEmployeeSig ? 'text-emerald-700' : 'text-amber-700'">
                                    <span x-show="hasEmployeeSig">✓ Spesimen paraf pegawai telah dibubuhkan dan akan otomatis dicetak pada lembar formulir pemeriksaan.</span>
                                    <span x-show="!hasEmployeeSig">Pegawai ybs belum membubuhkan paraf. Anda dapat membukakan pad coret paraf sekarang.</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <template x-if="hasEmployeeSig && employeeSigUrl">
                                <div class="h-11 px-3 py-1 bg-white rounded-lg border border-slate-200 flex items-center justify-center shadow-2xs">
                                    <img :src="employeeSigUrl" alt="Paraf Pegawai" class="max-h-9 max-w-[100px] object-contain">
                                </div>
                            </template>
                            <button type="button" @click="openEmployeePad()"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs active:scale-[0.98]"
                                :class="hasEmployeeSig ? 'border border-slate-300 text-slate-700 hover:bg-white bg-white/90' : 'bg-amber-600 text-white hover:bg-amber-700'">
                                <i class="fa-solid fa-pen-nib"></i>
                                <span x-text="hasEmployeeSig ? 'Ubah Paraf' : 'Bubuhi Paraf Sekarang'"></span>
                            </button>
                        </div>
                    </div>
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

    <!-- Modal Pad Paraf Pegawai -->
    <div x-show="showSigModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop with blur -->
        <div x-show="showSigModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="closeEmployeePad()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showSigModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-kai-navy via-kai-navy to-kai-navy-dark px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-kai-orange text-lg">
                            <i class="fa-solid fa-signature"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white">Pad Paraf Digital Pegawai Ybs</h3>
                            <p class="text-xs text-slate-300">
                                {{ $inspection->employee?->name }} &middot; NIPP: {{ $inspection->employee?->nipp }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeEmployeePad()" class="text-white/70 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 bg-slate-50/50">
                    <!-- Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-2xs">
                        <!-- Color Picker -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-600">Tinta:</span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="setSigColor('#0f172a')"
                                    class="w-7 h-7 rounded-full border-2 transition cursor-pointer"
                                    :class="penColor === '#0f172a' ? 'border-kai-orange scale-110 shadow-xs' : 'border-slate-200 hover:scale-105'"
                                    style="background-color: #0f172a;" title="Hitam"></button>
                                <button type="button" @click="setSigColor('#002B66')"
                                    class="w-7 h-7 rounded-full border-2 transition cursor-pointer"
                                    :class="penColor === '#002B66' ? 'border-kai-orange scale-110 shadow-xs' : 'border-slate-200 hover:scale-105'"
                                    style="background-color: #002B66;" title="Biru KAI"></button>
                                <button type="button" @click="setSigColor('#1e40af')"
                                    class="w-7 h-7 rounded-full border-2 transition cursor-pointer"
                                    :class="penColor === '#1e40af' ? 'border-kai-orange scale-110 shadow-xs' : 'border-slate-200 hover:scale-105'"
                                    style="background-color: #1e40af;" title="Biru Tua"></button>
                            </div>
                        </div>

                        <!-- Thickness Picker -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-600">Ketebalan:</span>
                            <div class="flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg">
                                <button type="button" @click="setSigWidth(2)"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition cursor-pointer"
                                    :class="penWidth === 2 ? 'bg-white shadow-xs font-bold text-kai-navy' : 'text-slate-600 hover:text-slate-900'">
                                    Halus
                                </button>
                                <button type="button" @click="setSigWidth(3.5)"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition cursor-pointer"
                                    :class="penWidth === 3.5 ? 'bg-white shadow-xs font-bold text-kai-navy' : 'text-slate-600 hover:text-slate-900'">
                                    Sedang
                                </button>
                                <button type="button" @click="setSigWidth(5)"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition cursor-pointer"
                                    :class="penWidth === 5 ? 'bg-white shadow-xs font-bold text-kai-navy' : 'text-slate-600 hover:text-slate-900'">
                                    Tebal
                                </button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1.5 ml-auto">
                            <button type="button" @click="undoSig()"
                                class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition flex items-center gap-1 cursor-pointer"
                                title="Undo goresan terakhir">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span class="hidden sm:inline">Undo</span>
                            </button>
                            <button type="button" @click="clearSig()"
                                class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 transition flex items-center gap-1 cursor-pointer"
                                title="Bersihkan seluruh kanvas">
                                <i class="fa-solid fa-eraser"></i>
                                <span class="hidden sm:inline">Bersihkan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Canvas Area -->
                    <div class="relative bg-white rounded-2xl border-2 border-dashed border-slate-300 overflow-hidden shadow-inner group">
                        <canvas id="employee-sig-canvas" 
                            class="w-full h-64 cursor-crosshair touch-none select-none block"></canvas>
                        
                        <!-- Base Guideline (Solid subtle line) -->
                        <div class="absolute bottom-16 left-12 right-12 h-[1px] bg-slate-300/80 pointer-events-none flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 bg-white px-1 -translate-y-1/2 select-none">Garis Tanda Tangan / Paraf</span>
                            <span class="text-[10px] text-slate-400 bg-white px-1 -translate-y-1/2 select-none">PT KAI</span>
                        </div>

                        <!-- Watermark Instruction -->
                        <div x-show="!hasDrawn" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none text-slate-400">
                            <i class="fa-solid fa-pen-nib text-3xl mb-2 text-slate-300"></i>
                            <span class="text-xs font-medium">Bubuhi paraf pegawai di sini</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Gunakan mouse atau goresan layar sentuh</span>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5 px-1">
                        <i class="fa-solid fa-circle-info text-kai-navy"></i>
                        <span>Paraf akan otomatis dipotong secara presisi dan disimpan dengan latar belakang transparan ke lembar laporan.</span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-slate-100">
                    <button type="button" @click="closeEmployeePad()"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="saveEmployeeSignature()" :disabled="isSavingSig"
                        class="btn-kai px-5 py-2 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-md transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                        <i x-show="isSavingSig" class="fa-solid fa-spinner fa-spin"></i>
                        <i x-show="!isSavingSig" class="fa-solid fa-check"></i>
                        <span x-text="isSavingSig ? 'Menyimpan...' : 'Gunakan Paraf Ini'"></span>
                    </button>
                </div>
            </div>
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
