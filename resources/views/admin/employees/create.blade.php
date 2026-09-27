@extends('layouts.app')

@section('title', 'Tambah Pegawai')
@section('page_title', 'Tambah Pegawai Baru')
@section('page_subtitle', 'Masukkan data pegawai baru penanggung jawab perangkat')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Unit Kerja <span class="text-rose-500">*</span>
                </label>
                <select name="unit_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Pilih Unit Kerja</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }} ({{ $unit->code }}) - {{ $unit->businessArea?->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor NIPP <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nipp" value="{{ old('nipp') }}" required placeholder="contoh: KAI011"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="contoh: Hendra Setiawan"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alamat Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="hendra@example.com"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Jabatan / Posisi
                    </label>
                    <input type="text" name="position" value="{{ old('position') }}" placeholder="contoh: Staff Pelayanan"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-kai-orange focus:ring-kai-orange border-slate-300 rounded">
                    <span class="text-xs font-medium text-slate-700">Pegawai Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.employees.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-save text-xs"></i> Simpan Pegawai
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
