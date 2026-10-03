@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('page_title', 'Master Pegawai')
@section('page_subtitle', 'Kelola data pegawai penanggung jawab perangkat PC dan Laptop')

@section('content')
<div class="space-y-6" x-data="{ importModal: false }">
    <!-- Header with Action & Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian Pegawai</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIPP, atau jabatan..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Unit Kerja</label>
                <select name="unit_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Unit</option>
                    @if ($units->pluck('business_area_id')->unique()->count() > 1)
                        @foreach ($units->groupBy(fn($u) => $u->businessArea?->name ?? 'Lainnya') as $areaName => $areaUnits)
                            <optgroup label="{{ $areaName }}">
                                @foreach ($areaUnits as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }}) - {{ $unit->businessArea?->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @else
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Keaktifan</label>
                <select name="status" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('admin.employees.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-800">Daftar Pegawai</h2>
                <p class="text-xs text-slate-500">Total data: {{ $employees->total() }} pegawai</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.employees.export', request()->all()) }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i> Export Excel
                </a>

                <button type="button" @click="importModal = true"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-import text-kai-blue"></i> Import Excel
                </button>

                <a href="{{ route('admin.employees.create') }}"
                    class="btn-kai px-4 py-2 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-user-plus"></i> Tambah Pegawai
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">Nama Pegawai</th>
                        <th class="py-3.5 px-4">NIPP</th>
                        <th class="py-3.5 px-4">Unit Kerja</th>
                        <th class="py-3.5 px-4">Area</th>
                        <th class="py-3.5 px-4">Jabatan</th>
                        <th class="py-3.5 px-4">Kontak Email</th>
                        <th class="py-3.5 px-4 text-center">Perangkat</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="font-bold text-slate-900">{{ $employee->name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-sm text-blue-600 font-mono font-semibold">{{ $employee->nipp }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $employee->unit?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-sm text-slate-600">{{ $employee->unit?->businessArea?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $employee->position ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $employee->email ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $employee->devices_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $employee->devices_count }} Unit
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($employee->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.employees.edit', $employee) }}"
                                        class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Pegawai">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" id="delete-emp-{{ $employee->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmAction({
                                            title: 'Hapus Pegawai?',
                                            text: 'Data pegawai {{ $employee->name }} akan dihapus.',
                                            icon: 'warning',
                                            formId: 'delete-emp-{{ $employee->id }}'
                                        })" class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Pegawai">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-id-badge text-3xl mb-2"></i>
                                <p class="text-sm">Tidak ditemukan data pegawai yang sesuai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Import Excel -->
    <div x-show="importModal" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative" @click.outside="importModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Import Data Pegawai dari Excel</h3>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mt-4 mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500 text-sm"></i>
                        <span class="text-xs font-semibold text-blue-800">Unduh template terlebih dahulu agar format data sesuai.</span>
                    </div>
                    <a href="{{ route('admin.employees.template') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                        <i class="fa-solid fa-download text-[10px]"></i> Download Template
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.employees.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Pilih Berkas Excel (.xlsx, .xls, .csv)
                    </label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-slate-400 mt-1">Kolom format: nipp, nama_pegawai, kode_unit, email, jabatan</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Unit Kerja Default (Jika kolom kode_unit kosong)
                    </label>
                    <select name="unit_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none">
                        <option value="">Pilih Unit</option>
                        @if ($units->pluck('business_area_id')->unique()->count() > 1)
                            @foreach ($units->groupBy(fn($u) => $u->businessArea?->name ?? 'Lainnya') as $areaName => $areaUnits)
                                <optgroup label="{{ $areaName }}">
                                    @foreach ($areaUnits as $unit)
                                        <option value="{{ $unit->id }}">
                                            {{ $unit->name }} ({{ $unit->code }}) - {{ $unit->businessArea?->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @else
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->code }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="importModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-xs font-semibold text-white hover:bg-blue-700">
                        Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
