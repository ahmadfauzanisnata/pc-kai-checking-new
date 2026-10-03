@extends('layouts.app')

@section('title', 'Periode Pemeriksaan')
@section('page_title', 'Periode Pemeriksaan')
@section('page_subtitle', 'Kelola siklus dan jadwal periode pemeriksaan PC & Laptop')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, isEdit: false, formAction: '', formData: { business_area_id: '', name: '', start_date: '', end_date: '' } }">
    <!-- Header with Action -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.inspection-periods.index') }}" class="flex-1 flex flex-wrap gap-2.5">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama periode..."
                    class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <select name="status" class="px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                <option value="">Semua Status</option>
                <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Ditutup</option>
                <option value="LOCKED" {{ request('status') === 'LOCKED' ? 'selected' : '' }}>Dikunci</option>
            </select>

            <button type="submit" class="py-2 px-4 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition shadow-xs">
                Filter
            </button>
        </form>

        <button type="button" @click="
            isEdit = false;
            formAction = '{{ route('admin.inspection-periods.store') }}';
            formData = { business_area_id: '', name: '', start_date: '', end_date: '' };
            modalOpen = true;
        " class="btn-kai px-4 py-2.5 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i> Tambah Periode
        </button>
    </div>

    <!-- Periods Grid / Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse ($periods as $period)
            @php
                $total = $period->inspections_count;
                $completed = $period->completed_count;
                $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
            @endphp
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between hover:border-blue-200 transition">
                <div>
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $period->status->badgeClass() }}">
                                {{ $period->status->label() }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 mt-2">{{ $period->name }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                <i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> {{ $period->businessArea?->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-semibold text-slate-500">Rentang Waktu</div>
                            <div class="text-xs font-medium text-slate-700 mt-0.5">
                                {{ $period->start_date->format('d M Y') }} - {{ $period->end_date->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="font-medium text-slate-600">Penyelesaian Selesai / Disetujui</span>
                            <span class="font-bold text-blue-700">{{ $completed }} / {{ $total }} ({{ $percent }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <!-- Stats mini grid -->
                    <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <div class="text-[10px] uppercase font-bold text-slate-400">Total Tugas</div>
                            <div class="text-base font-extrabold text-slate-800 mt-0.5">{{ $total }}</div>
                        </div>
                        <div class="bg-amber-50/60 p-2.5 rounded-xl border border-amber-100/60">
                            <div class="text-[10px] uppercase font-bold text-amber-600">Menunggu Verif</div>
                            <div class="text-base font-extrabold text-amber-700 mt-0.5">{{ $period->pending_count }}</div>
                        </div>
                        <div class="bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100/60">
                            <div class="text-[10px] uppercase font-bold text-emerald-600">Disetujui</div>
                            <div class="text-base font-extrabold text-emerald-700 mt-0.5">{{ $completed }}</div>
                        </div>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        @if ($period->status->value === 'DRAFT')
                            <form method="POST" action="{{ route('admin.inspection-periods.activate', $period) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                                    <i class="fa-solid fa-play mr-1"></i> Aktifkan
                                </button>
                            </form>
                        @elseif ($period->status->value === 'ACTIVE')
                            <form method="POST" action="{{ route('admin.inspection-periods.close', $period) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm transition">
                                    <i class="fa-solid fa-stop mr-1"></i> Tutup
                                </button>
                            </form>
                        @elseif ($period->status->value === 'CLOSED')
                            <form method="POST" action="{{ route('admin.inspection-periods.lock', $period) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-sm transition">
                                    <i class="fa-solid fa-lock mr-1"></i> Kunci Resmi
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5">
                        @if ($period->status->value !== 'LOCKED')
                            <button type="button" @click="
                                isEdit = true;
                                formAction = '/admin/inspection-periods/{{ $period->id }}';
                                formData = {
                                    business_area_id: '{{ $period->business_area_id }}',
                                    name: '{{ addslashes($period->name) }}',
                                    start_date: '{{ $period->start_date->format('Y-m-d') }}',
                                    end_date: '{{ $period->end_date->format('Y-m-d') }}'
                                };
                                modalOpen = true;
                            " class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Periode">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        @endif

                        @if ($total > 0)
                            <a href="{{ route('reports.period.zip', $period) }}"
                                class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Unduh Berkas Lengkap (.ZIP)">
                                <i class="fa-solid fa-file-zipper"></i>
                            </a>
                        @endif

                        <form method="POST" action="{{ route('admin.inspection-periods.destroy', $period) }}" id="delete-period-{{ $period->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmAction({
                                title: 'Hapus Periode Pemeriksaan?',
                                text: 'Periode {{ $period->name }} beserta seluruh data pemeriksaan ({{ $total }} data) di dalamnya akan dihapus permanen!',
                                icon: 'warning',
                                confirmButtonColor: '#e11d48',
                                confirmButtonText: 'Ya, Hapus Semua',
                                formId: 'delete-period-{{ $period->id }}'
                            })" class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Periode & Seluruh Data">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200">
                <i class="fa-solid fa-calendar-xmark text-4xl mb-2"></i>
                <p class="text-sm">Belum ada data periode pemeriksaan.</p>
            </div>
        @endforelse
    </div>

    @if ($periods->hasPages())
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
            {{ $periods->links() }}
        </div>
    @endif

    <!-- Modal Form Tambah / Edit Periode -->
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative" @click.outside="modalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Periode Pemeriksaan' : 'Tambah Periode Baru'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="mt-5 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                @if (Auth::user()->isSuperAdmin())
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Business Area <span class="text-rose-500">*</span>
                        </label>
                        <select name="business_area_id" x-model="formData.business_area_id" required
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                            <option value="">Pilih Area</option>
                            @foreach ($businessAreas as $ba)
                                <option value="{{ $ba->id }}">{{ $ba->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Periode Pemeriksaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="formData.name" required placeholder="contoh: Pemeriksaan Triwulan IV 2026"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="start_date" x-model="formData.start_date" required
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="end_date" x-model="formData.end_date" required
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn-kai px-5 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2">
                        <i class="fa-solid fa-save text-xs"></i> Simpan Periode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
