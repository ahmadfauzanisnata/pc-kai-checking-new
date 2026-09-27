@extends('layouts.app')

@section('title', 'Data Perangkat')
@section('page_title', 'Master Perangkat')
@section('page_subtitle', 'Kelola inventaris PC Desktop dan Laptop pegawai')

@section('content')
<div class="space-y-6" x-data="{ importModal: false, assignModal: false, selectedDevice: null, selectedEmployeeId: '' }">
    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('admin.devices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor aset, host, serial, IP, pegawai..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Perangkat</label>
                <select name="device_type" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Jenis</option>
                    <option value="PC" {{ request('device_type') === 'PC' ? 'selected' : '' }}>PC Desktop</option>
                    <option value="LAPTOP" {{ request('device_type') === 'LAPTOP' ? 'selected' : '' }}>Laptop</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Operasional</label>
                <select name="device_status" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE" {{ request('device_status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="INACTIVE" {{ request('device_status') === 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                    <option value="MAINTENANCE" {{ request('device_status') === 'MAINTENANCE' ? 'selected' : '' }}>Pemeliharaan</option>
                    <option value="RETIRED" {{ request('device_status') === 'RETIRED' ? 'selected' : '' }}>Pensiun</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Unit Kerja</label>
                <select name="unit_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }} ({{ $unit->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('admin.devices.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-800">Daftar Perangkat</h2>
                <p class="text-xs text-slate-500">Total data: {{ $devices->total() }} perangkat</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.devices.export', request()->all()) }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i> Export Excel
                </a>

                <button type="button" @click="importModal = true"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-import text-kai-blue"></i> Import Excel
                </button>

                <a href="{{ route('admin.devices.create') }}"
                    class="btn-kai px-4 py-2 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah Perangkat
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">Nomor Aset & Hostname</th>
                        <th class="py-3.5 px-4">Jenis & Spesifikasi</th>
                        <th class="py-3.5 px-4">Sistem Operasi</th>
                        <th class="py-3.5 px-4">Pegawai Pengguna</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($devices as $device)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="font-bold text-slate-900 font-mono">{{ $device->asset_number }}</div>
                                <div class="text-xs text-blue-600 font-mono">{{ $device->hostname ?? '-' }}</div>
                                @if ($device->ip_address)
                                    <div class="text-[11px] text-slate-400 font-mono">IP: {{ $device->ip_address }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $device->device_type->value === 'LAPTOP' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        <i class="fa-solid {{ $device->device_type->value === 'LAPTOP' ? 'fa-laptop' : 'fa-desktop' }} mr-1"></i>
                                        {{ $device->device_type->label() }}
                                    </span>
                                    <span class="font-semibold text-slate-800">{{ $device->brand }} {{ $device->model }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">SN: {{ $device->serial_number ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $device->operating_system ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $device->os_version ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($device->employee)
                                    <div class="font-bold text-slate-900">{{ $device->employee->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $device->employee->unit?->name }} (NIPP: {{ $device->employee->nipp }})</div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 italic">
                                        Belum Ada Pegawai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $device->device_status->badgeClass() }}">
                                    {{ $device->device_status->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.devices.edit', $device) }}"
                                        class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Perangkat">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.devices.destroy', $device) }}" id="delete-dev-{{ $device->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmAction({
                                            title: 'Hapus Perangkat?',
                                            text: 'Perangkat {{ $device->asset_number }} akan dihapus.',
                                            icon: 'warning',
                                            formId: 'delete-dev-{{ $device->id }}'
                                        })" class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Perangkat">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-laptop text-3xl mb-2"></i>
                                <p class="text-sm">Tidak ditemukan data perangkat yang sesuai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($devices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Import Excel -->
    <div x-show="importModal" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative" @click.outside="importModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Import Data Perangkat dari Excel</h3>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.devices.import') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Pilih Berkas Excel (.xlsx, .xls, .csv)
                    </label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-slate-400 mt-1">Kolom format: nomor_aset, hostname, jenis_perangkat, merk, model, serial_number, ip_address, sistem_operasi, versi_os, nipp_pegawai, keterangan</p>
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
