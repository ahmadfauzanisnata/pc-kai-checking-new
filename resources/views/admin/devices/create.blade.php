@extends('layouts.app')

@section('title', 'Tambah Perangkat')
@section('page_title', 'Tambah Perangkat Baru')
@section('page_subtitle', 'Daftarkan inventaris PC Desktop atau Laptop baru ke sistem')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form method="POST" action="{{ route('admin.devices.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor Aset <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="asset_number" value="{{ old('asset_number') }}" required placeholder="contoh: AST-KAI-011"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Jenis Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <select name="device_type" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                        <option value="PC" {{ old('device_type') === 'PC' ? 'selected' : '' }}>PC Desktop</option>
                        <option value="LAPTOP" {{ old('device_type') === 'LAPTOP' ? 'selected' : '' }}>Laptop</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Hostname
                    </label>
                    <input type="text" name="hostname" value="{{ old('hostname') }}" placeholder="contoh: PC-TI-05"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Merk (Brand)
                    </label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="contoh: Lenovo, Dell, HP"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Model / Tipe
                    </label>
                    <input type="text" name="model" value="{{ old('model') }}" placeholder="contoh: ThinkPad L14 Gen 3"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor Seri (Serial Number)
                    </label>
                    <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="contoh: SN-LNV-011"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alamat IP (Static / DHCP)
                    </label>
                    <input type="text" name="ip_address" value="{{ old('ip_address') }}" placeholder="10.2.10.25"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Status Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <select name="device_status" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                        <option value="ACTIVE" {{ old('device_status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="INACTIVE" {{ old('device_status') === 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="MAINTENANCE" {{ old('device_status') === 'MAINTENANCE' ? 'selected' : '' }}>Pemeliharaan</option>
                        <option value="RETIRED" {{ old('device_status') === 'RETIRED' ? 'selected' : '' }}>Pensiun</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Sistem Operasi
                    </label>
                    <input type="text" name="operating_system" value="{{ old('operating_system', 'Windows 11 Pro') }}" placeholder="Windows 11 Pro"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Versi OS
                    </label>
                    <input type="text" name="os_version" value="{{ old('os_version', '23H2') }}" placeholder="23H2"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Pegawai Penanggung Jawab
                </label>
                <select name="employee_id" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Belum Ditugaskan ke Pegawai</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} (NIPP: {{ $emp->nipp }}) - {{ $emp->unit?->name }}
                        </option>
                    @endforeach
                </select>
                <span class="text-[11px] text-slate-400 mt-1 block">Perangkat wajib memiliki penanggung jawab agar dapat ditugaskan untuk pemeriksaan.</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Keterangan Tambahan
                </label>
                <textarea name="description" rows="3" placeholder="Lokasi penempatan, riwayat, atau catatan fisik..."
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.devices.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-save text-xs"></i> Simpan Perangkat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
