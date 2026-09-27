@extends('layouts.app')

@section('title', 'Buat Penugasan Pemeriksaan')
@section('page_title', 'Buat Penugasan Pemeriksaan')
@section('page_subtitle', 'Pilih periode, tanggal, pemeriksa, dan perangkat untuk penugasan sekaligus (batch)')

@section('content')
<div class="space-y-6" x-data="{
    selectedDevices: [],
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.selectedDevices = Array.from(document.querySelectorAll('.device-checkbox')).map(el => el.value);
        } else {
            this.selectedDevices = [];
        }
    }
}">
    <form method="POST" action="{{ route('admin.assignments.store') }}">
        @csrf

        <!-- Config Card -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80 mb-6 space-y-5">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-kai-orange"></i> Konfigurasi Penugasan
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Periode Pemeriksaan <span class="text-rose-500">*</span>
                    </label>
                    <select name="inspection_period_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                        @forelse ($periods as $period)
                            <option value="{{ $period->id }}" {{ old('inspection_period_id', $selectedPeriodId) == $period->id ? 'selected' : '' }}>
                                {{ $period->name }} ({{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }})
                            </option>
                        @empty
                            <option value="">Tidak ada periode aktif</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Pemeriksa (Inspector) <span class="text-rose-500">*</span>
                    </label>
                    <select name="inspector_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                        <option value="">Pilih Pemeriksa</option>
                        @foreach ($inspectors as $insp)
                            <option value="{{ $insp->id }}" {{ old('inspector_id') == $insp->id ? 'selected' : '' }}>
                                {{ $insp->name }} (NIPP: {{ $insp->nipp ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Tanggal Dokumen <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="document_date" value="{{ old('document_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Rencana Tgl Pemeriksaan
                    </label>
                    <input type="date" name="inspection_date" value="{{ old('inspection_date') }}"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>
        </div>

        <!-- Filter Sub-Card for Devices -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        Pilih Perangkat untuk Ditugaskan
                    </h3>
                    <p class="text-xs text-slate-500">Centang perangkat yang akan diperiksa oleh pemeriksa terpilih</p>
                </div>

                <!-- Live Selected Count Badge -->
                <div class="flex items-center gap-2">
                    <span class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-kai-blue-light text-kai-navy border border-kai-blue/20">
                        <i class="fa-solid fa-check-circle mr-1 text-kai-orange"></i>
                        <span x-text="selectedDevices.length">0</span> Perangkat Terpilih
                    </span>
                    <button type="submit" :disabled="selectedDevices.length === 0"
                        class="btn-kai px-5 py-2.5 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Buat Penugasan Batch
                    </button>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                            <th class="py-3 px-4 w-12 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll"
                                    class="h-4 w-4 text-kai-orange focus:ring-kai-orange border-slate-300 rounded cursor-pointer">
                            </th>
                            <th class="py-3 px-4">Nomor Aset</th>
                            <th class="py-3 px-4">Hostname & Jenis</th>
                            <th class="py-3 px-4">Merk / Model</th>
                            <th class="py-3 px-4">Pegawai Penanggung Jawab</th>
                            <th class="py-3 px-4">Unit Kerja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($devices as $device)
                            <tr class="hover:bg-slate-50/60 transition cursor-pointer"
                                @click="
                                    const val = '{{ $device->id }}';
                                    if (selectedDevices.includes(val)) {
                                        selectedDevices = selectedDevices.filter(id => id !== val);
                                    } else {
                                        selectedDevices.push(val);
                                    }
                                ">
                                <td class="py-3 px-4 text-center" @click.stop>
                                    <input type="checkbox" name="device_ids[]" value="{{ $device->id }}"
                                        x-model="selectedDevices"
                                        class="device-checkbox h-4 w-4 text-kai-orange focus:ring-kai-orange border-slate-300 rounded cursor-pointer">
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-kai-navy">
                                    {{ $device->asset_number }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $device->device_type->value === 'LAPTOP' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ $device->device_type->label() }}
                                    </span>
                                    <span class="ml-1.5 font-mono text-slate-700">{{ $device->hostname ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ $device->brand }} {{ $device->model }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $device->employee?->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">NIPP: {{ $device->employee?->nipp }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium">
                                    {{ $device->employee?->unit?->name }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-laptop-code text-3xl mb-2"></i>
                                    <p class="text-sm">Semua perangkat dalam periode ini sudah ditugaskan atau belum ada perangkat berpenanggung jawab.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
@endsection
