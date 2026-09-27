@extends('layouts.app')

@section('title', 'Profil Pengguna - KAI PC Checking')
@section('page_title', 'Profil Saya')
@section('page_subtitle', 'Informasi akun dan spesimen tanda tangan resmi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <div class="flex items-center gap-4 pb-6 border-b border-slate-100">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-kai-navy-dark to-kai-navy text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-kai-navy/20 border border-kai-orange/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-kai-orange/10 text-kai-orange-dark border border-kai-orange/20">
                        {{ $user->role_display_name }}
                    </span>
                    @if ($user->nipp)
                        <span class="text-xs text-slate-500 font-mono">NIPP: {{ $user->nipp }}</span>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Lengkap
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alamat Email (Akun)
                    </label>
                    <input type="email" value="{{ $user->email }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed">
                    <span class="text-[11px] text-slate-400 mt-1 block">Email akun dikelola oleh Super Administrator.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor NIPP
                    </label>
                    <input type="text" value="{{ $user->nipp ?? '-' }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="contoh: 08123456789"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Business Area
                    </label>
                    <input type="text" value="{{ $user->businessArea?->name ?? 'Akses Global (Seluruh Area)' }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Unit Kerja
                    </label>
                    <input type="text" value="{{ $user->unit?->name ?? '-' }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed">
                </div>
            </div>

            <!-- Signature Specimen Section (Digital Paint Pad) -->
            <div class="pt-5 border-t border-slate-100" x-data="signaturePadComponent()">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Spesimen Tanda Tangan / Paraf Digital
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Gunakan aplikasi kanvas digital untuk mencoret spesimen tanda tangan resmi Anda.
                        </p>
                    </div>
                    <template x-if="hasSignature">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Tanda Tangan Aktif
                        </span>
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <!-- Preview Box / Interactive Card -->
                    <div class="md:col-span-6 lg:col-span-5">
                        <div class="relative group border-2 rounded-2xl p-4 bg-slate-50/70 flex flex-col items-center justify-center min-h-[140px] transition duration-200"
                             :class="hasSignature ? 'border-slate-200 bg-white hover:border-kai-blue/40 shadow-xs' : 'border-dashed border-kai-orange/40 bg-orange-50/20 hover:border-kai-orange hover:bg-orange-50/40 cursor-pointer'"
                             @click="!hasSignature ? openModal() : null">
                            
                            <template x-if="hasSignature">
                                <div class="w-full flex flex-col items-center">
                                    <div class="w-full h-28 flex items-center justify-center p-2 rounded-xl bg-slate-50/80 border border-slate-100 relative overflow-hidden">
                                        <!-- Subtle checkered watermark pattern for transparency -->
                                        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 8px 8px;"></div>
                                        <img :src="signatureUrl" alt="Spesimen Tanda Tangan" class="max-h-full max-w-full object-contain relative z-10 transition group-hover:scale-105 duration-200">
                                    </div>
                                    <span class="text-[11px] text-slate-400 mt-2 font-mono">Format: Digital PNG Transparan</span>
                                </div>
                            </template>

                            <template x-if="!hasSignature">
                                <div class="text-center py-3">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-kai-orange/10 text-kai-orange flex items-center justify-center mb-2 group-hover:scale-110 transition duration-200">
                                        <i class="fa-solid fa-paintbrush text-lg"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 block">Belum ada tanda tangan</span>
                                    <span class="text-[11px] text-kai-orange font-semibold block mt-0.5">Klik untuk membuka kanvas coret &rarr;</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Action Controls -->
                    <div class="md:col-span-6 lg:col-span-7 flex flex-col justify-center space-y-3">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <button type="button" @click="openModal()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-kai-navy hover:bg-kai-navy-light text-white shadow-xs transition active:scale-[0.98]">
                                <i class="fa-solid fa-pen-nib text-amber-300"></i>
                                <span x-text="hasSignature ? 'Coret Ulang / Ubah Tanda Tangan' : 'Buka Kanvas Tanda Tangan (Paint App)'"></span>
                            </button>

                            <template x-if="hasSignature">
                                <button type="button" @click="deleteSignature()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold border border-rose-200 hover:bg-rose-50 text-rose-600 transition active:scale-[0.98]">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Coret tanda tangan langsung di layar menggunakan mouse, trackpad, atau smartphone/tablet. Tanda tangan otomatis tersimpan transparan untuk dokumen pengesahan.
                        </p>
                    </div>
                </div>

                <!-- Hidden Input for Signature Data in case form submit is used -->
                <input type="hidden" name="signature_data" id="profile_signature_data" :value="signatureDataInput">

                <!-- MODAL / CARD PAINT APP -->
                <div x-show="showModal" 
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
                     aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    
                    <!-- Backdrop -->
                    <div x-show="showModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                         @click="closeModal()"></div>

                    <!-- Modal Card Container -->
                    <div x-show="showModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative bg-white rounded-3xl shadow-2xl border border-slate-200/80 w-full max-w-2xl overflow-hidden z-10 flex flex-col">
                        
                        <!-- Header -->
                        <div class="px-6 py-4 bg-gradient-to-r from-kai-navy-dark to-kai-navy text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                                    <i class="fa-solid fa-paintbrush text-kai-orange text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold leading-tight" id="modal-title">Kanvas Tanda Tangan Digital</h3>
                                    <p class="text-xs text-slate-300 mt-0.5">Coret tanda tangan resmi Anda pada area kanvas di bawah</p>
                                </div>
                            </div>
                            <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <!-- Paint Toolbar -->
                        <div class="px-6 py-3 bg-slate-50 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <!-- Color Picker -->
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-600 mr-1"><i class="fa-solid fa-palette text-slate-400 mr-1"></i> Tinta:</span>
                                <button type="button" @click="setPenColor('#0f172a')" 
                                    class="w-6 h-6 rounded-full bg-[#0f172a] border-2 transition"
                                    :class="penColor === '#0f172a' ? 'ring-2 ring-kai-orange ring-offset-1 scale-110 border-white' : 'border-transparent opacity-80 hover:opacity-100'"
                                    title="Hitam (Standar Dokumen)"></button>
                                <button type="button" @click="setPenColor('#1E6DB5')" 
                                    class="w-6 h-6 rounded-full bg-[#1E6DB5] border-2 transition"
                                    :class="penColor === '#1E6DB5' ? 'ring-2 ring-kai-orange ring-offset-1 scale-110 border-white' : 'border-transparent opacity-80 hover:opacity-100'"
                                    title="Biru KAI"></button>
                                <button type="button" @click="setPenColor('#1B3A5C')" 
                                    class="w-6 h-6 rounded-full bg-[#1B3A5C] border-2 transition"
                                    :class="penColor === '#1B3A5C' ? 'ring-2 ring-kai-orange ring-offset-1 scale-110 border-white' : 'border-transparent opacity-80 hover:opacity-100'"
                                    title="Biru Dongker"></button>
                            </div>

                            <!-- Thickness Selector -->
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-600 mr-1"><i class="fa-solid fa-pen text-slate-400 mr-1"></i> Tebal:</span>
                                <button type="button" @click="setPenWidth(2)" 
                                    class="px-2.5 py-1 rounded-lg border font-semibold transition"
                                    :class="penWidth === 2 ? 'bg-kai-navy text-white border-kai-navy' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'">
                                    Tipis
                                </button>
                                <button type="button" @click="setPenWidth(3.5)" 
                                    class="px-2.5 py-1 rounded-lg border font-semibold transition"
                                    :class="penWidth === 3.5 ? 'bg-kai-navy text-white border-kai-navy' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'">
                                    Sedang
                                </button>
                                <button type="button" @click="setPenWidth(5)" 
                                    class="px-2.5 py-1 rounded-lg border font-semibold transition"
                                    :class="penWidth === 5 ? 'bg-kai-navy text-white border-kai-navy' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'">
                                    Tebal
                                </button>
                            </div>

                            <!-- Canvas Action Buttons -->
                            <div class="flex items-center gap-1.5 ml-auto">
                                <button type="button" @click="undo()" :disabled="history.length <= 1"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-rotate-left"></i> Undo
                                </button>
                                <button type="button" @click="clearCanvas()"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold transition">
                                    <i class="fa-solid fa-eraser"></i> Bersihkan
                                </button>
                            </div>
                        </div>

                        <!-- Canvas Drawing Body -->
                        <div class="p-6 bg-slate-100/60 flex flex-col items-center justify-center">
                            <div class="w-full relative rounded-2xl overflow-hidden border-2 border-slate-300 shadow-inner bg-white select-none touch-none">
                                <canvas id="signature-pad-canvas" 
                                    class="w-full h-64 cursor-crosshair block bg-white"
                                    style="touch-action: none;"></canvas>
                                
                                <!-- Guide Line (Garis lurus solid) -->
                                <div class="absolute bottom-10 inset-x-8 border-b border-slate-200 pointer-events-none flex justify-end">
                                    <span class="text-[10px] uppercase font-bold tracking-widest text-slate-300 bg-white/90 px-2 py-0.5 rounded">
                                        Area Tanda Tangan
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between w-full mt-2 text-[11px] text-slate-500">
                                <span class="flex items-center gap-1"><i class="fa-solid fa-hand-pointer text-slate-400"></i> Coretkan tanda tangan menggunakan mouse atau sentuhan jari</span>
                                <span class="font-mono text-[10px] text-slate-400">Transparansi PNG Otomatis</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between gap-3">
                            <div class="text-xs text-slate-500 hidden sm:block">
                                <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i> Digunakan resmi pada PDF & laporan
                            </div>
                            <div class="flex items-center gap-2.5 ml-auto">
                                <button type="button" @click="closeModal()" 
                                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/60 border border-slate-300 transition">
                                    Batal
                                </button>
                                <button type="button" @click="saveSignature()" :disabled="isSaving"
                                    class="btn-kai px-5 py-2 rounded-xl text-white font-bold text-xs shadow-md transition active:scale-[0.98] flex items-center gap-1.5 disabled:opacity-50">
                                    <template x-if="!isSaving">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-floppy-disk"></i> Simpan Tanda Tangan
                                        </span>
                                    </template>
                                    <template x-if="isSaving">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="btn-kai px-6 py-2.5 rounded-xl text-white font-semibold text-sm shadow-md transition active:scale-[0.98]">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function signaturePadComponent() {
    return {
        hasSignature: {{ $user->signature_path ? 'true' : 'false' }},
        signatureUrl: '{{ $user->signature_path ? Storage::url($user->signature_path) : "" }}',
        signatureDataInput: '',
        showModal: false,
        penColor: '#0f172a',
        penWidth: 3.5,
        isDrawing: false,
        hasDrawn: false,
        isSaving: false,
        canvas: null,
        ctx: null,
        history: [],
        lastX: 0,
        lastY: 0,

        init() {
            this.$watch('showModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        this.initCanvas();
                    });
                }
            });
        },

        openModal() {
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        setPenColor(color) {
            this.penColor = color;
            if (this.ctx) this.ctx.strokeStyle = color;
        },

        setPenWidth(width) {
            this.penWidth = width;
            if (this.ctx) this.ctx.lineWidth = width;
        },

        initCanvas() {
            this.canvas = document.getElementById('signature-pad-canvas');
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

            // Clear transparent
            this.ctx.clearRect(0, 0, rect.width, rect.height);
            this.history = [];
            this.saveState();
            this.hasDrawn = false;

            this.bindEvents();
        },

        getPos(e) {
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

        bindEvents() {
            if (this.canvas._eventsBound) return;
            this.canvas._eventsBound = true;

            const startDraw = (e) => {
                e.preventDefault();
                this.isDrawing = true;
                const pos = this.getPos(e);
                this.lastX = pos.x;
                this.lastY = pos.y;

                this.ctx.strokeStyle = this.penColor;
                this.ctx.fillStyle = this.penColor;
                this.ctx.lineWidth = this.penWidth;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';

                // Initial touch dot
                this.ctx.beginPath();
                this.ctx.arc(pos.x, pos.y, this.penWidth / 2, 0, Math.PI * 2);
                this.ctx.fill();

                this.hasDrawn = true;
            };

            const moveDraw = (e) => {
                if (!this.isDrawing) return;
                e.preventDefault();
                const pos = this.getPos(e);

                // Continuous unbroken line between previous sample and current sample
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
                this.saveState();
            };

            // Mouse events
            this.canvas.addEventListener('mousedown', startDraw);
            this.canvas.addEventListener('mousemove', moveDraw);
            window.addEventListener('mouseup', endDraw);

            // Touch events
            this.canvas.addEventListener('touchstart', startDraw, { passive: false });
            this.canvas.addEventListener('touchmove', moveDraw, { passive: false });
            window.addEventListener('touchend', endDraw);
            window.addEventListener('touchcancel', endDraw);
        },

        saveState() {
            if (!this.canvas || !this.ctx) return;
            const rect = this.canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const imageData = this.ctx.getImageData(0, 0, rect.width * dpr, rect.height * dpr);
            this.history.push(imageData);
            if (this.history.length > 20) {
                this.history.shift();
            }
        },

        undo() {
            if (this.history.length > 1) {
                this.history.pop();
                const prevState = this.history[this.history.length - 1];
                this.ctx.putImageData(prevState, 0, 0);
                this.hasDrawn = this.history.length > 1;
            }
        },

        clearCanvas() {
            if (!this.canvas || !this.ctx) return;
            const rect = this.canvas.getBoundingClientRect();
            this.ctx.clearRect(0, 0, rect.width, rect.height);
            this.history = [];
            this.saveState();
            this.hasDrawn = false;
        },

        cropCanvas(sourceCanvas) {
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

            // Add margin padding around cropped signature
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

        async saveSignature() {
            if (!this.hasDrawn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kanvas Masih Kosong',
                    text: 'Silakan goreskan tanda tangan Anda terlebih dahulu pada kanvas sebelum menyimpan.',
                    confirmButtonColor: '#F47920',
                });
                return;
            }

            this.isSaving = true;

            try {
                const dataUrl = this.cropCanvas(this.canvas);
                this.signatureDataInput = dataUrl;

                const response = await fetch('{{ route('profile.signature') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        signature_data: dataUrl
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.signatureUrl = result.signature_url;
                    this.hasSignature = true;
                    this.closeModal();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message || 'Spesimen tanda tangan digital berhasil disimpan.',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: result.message || 'Terjadi kesalahan saat menyimpan tanda tangan.',
                        confirmButtonColor: '#F47920',
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal menghubungi server. Silakan coba kembali.',
                    confirmButtonColor: '#F47920',
                });
            } finally {
                this.isSaving = false;
            }
        },

        async deleteSignature() {
            const confirmResult = await Swal.fire({
                title: 'Hapus Tanda Tangan?',
                text: 'Spesimen tanda tangan digital Anda akan dihapus dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (!confirmResult.isConfirmed) return;

            try {
                const response = await fetch('{{ route('profile.signature') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        signature_data: 'clear'
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.hasSignature = false;
                    this.signatureUrl = '';
                    this.signatureDataInput = 'clear';

                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus!',
                        text: result.message || 'Spesimen tanda tangan berhasil dihapus.',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message || 'Gagal menghapus tanda tangan.',
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Gagal memproses penghapusan.',
                });
            }
        }
    };
}
</script>
@endpush
@endsection
