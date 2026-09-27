@extends('layouts.app')

@section('title', 'Konfigurasi Formulir')
@section('page_title', 'Konfigurasi Formulir Standar KAI')
@section('page_subtitle', 'Pengaturan metadata dokumen FORMULIR PC/LAPTOP CHECKING dan kop surat')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form method="POST" action="{{ route('super-admin.form-configuration.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas Perusahaan & Organisasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Informasi kop surat formulir pemeriksaan resmi</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Perusahaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name', $config->company_name) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Divisi / Bagian / Departemen <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="department_name" value="{{ old('department_name', $config->department_name) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div class="border-b border-slate-100 pb-4 pt-2">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Metadata Standar Dokumen ISO / Formulir</h3>
                <p class="text-xs text-slate-500 mt-0.5">Penomoran dan riwayat revisi dokumen mutu</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor Formulir <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="form_number" value="{{ old('form_number', $config->form_number) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Contoh: FR.SM/TI/017.002/10-2020</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Tanggal Terbit Dokumen <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="publication_date" value="{{ old('publication_date', $config->publication_date?->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Status / No. Revisi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="revision_status" value="{{ old('revision_status', $config->revision_status) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Contoh: 002-2020</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Label Halaman Default
                    </label>
                    <input type="text" name="page_label" value="{{ old('page_label', $config->page_label) }}" placeholder="1 dari 1"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                </div>
            </div>

            <div class="border-b border-slate-100 pb-4 pt-2">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Logo Perusahaan</h3>
            </div>

            <div class="flex items-center gap-6">
                <div class="w-40 h-20 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center p-2">
                    @if ($config->logo_path)
                        <img src="{{ Storage::url($config->logo_path) }}" alt="Logo" class="max-h-full object-contain">
                    @else
                        <span class="text-xs text-slate-400 italic">Logo KAI Default</span>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg"
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-kai-orange/10 file:text-kai-orange hover:file:bg-kai-orange/20">
                    <p class="text-[11px] text-slate-400 mt-1">PNG transparan direkomendasikan. Maks 2MB.</p>
                </div>
            </div>

            @if ($config->updatedBy)
                <div class="text-xs text-slate-500 pt-2 border-t border-slate-100">
                    Terakhir diperbarui oleh: <span class="font-semibold text-slate-700">{{ $config->updatedBy->name }}</span> pada {{ $config->updated_at->format('d M Y H:i') }}
                </div>
            @endif

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-save text-xs"></i> Simpan Konfigurasi Formulir
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
