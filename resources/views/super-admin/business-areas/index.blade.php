@extends('layouts.app')

@section('title', 'Business Area')
@section('page_title', 'Master Business Area')
@section('page_subtitle', 'Kelola data wilayah kerja operasional KAI')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, isEdit: false, formAction: '', formData: { code: '', name: '', address: '', is_active: true } }">
    <!-- Header with Action -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('super-admin.business-areas.index') }}" class="flex-1 flex gap-2">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama area..."
                    class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>
            <button type="submit" class="py-2 px-4 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition shadow-xs">
                Cari
            </button>
        </form>

        <button type="button" @click="
            isEdit = false;
            formAction = '{{ route('super-admin.business-areas.store') }}';
            formData = { code: '', name: '', address: '', is_active: true };
            modalOpen = true;
        " class="btn-kai px-4 py-2.5 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Business Area
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">Kode Area</th>
                        <th class="py-3.5 px-4">Nama Business Area</th>
                        <th class="py-3.5 px-4">Alamat Kantor</th>
                        <th class="py-3.5 px-4 text-center">Unit</th>
                        <th class="py-3.5 px-4 text-center">Pengguna</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($businessAreas as $ba)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-blue-600">
                                {{ $ba->code }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $ba->name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $ba->address ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs">
                                    {{ $ba->units_count }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-semibold text-xs">
                                    {{ $ba->users_count }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($ba->is_active)
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
                                    <button type="button" @click="
                                        isEdit = true;
                                        formAction = '/super-admin/business-areas/{{ $ba->id }}';
                                        formData = {
                                            code: '{{ addslashes($ba->code) }}',
                                            name: '{{ addslashes($ba->name) }}',
                                            address: '{{ addslashes($ba->address ?? '') }}',
                                            is_active: {{ $ba->is_active ? 'true' : 'false' }}
                                        };
                                        modalOpen = true;
                                    " class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    @if ($ba->units_count == 0)
                                        <form method="POST" action="{{ route('super-admin.business-areas.destroy', $ba) }}" id="delete-ba-{{ $ba->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmAction({
                                                title: 'Hapus Business Area?',
                                                text: 'Area {{ $ba->name }} akan dihapus.',
                                                icon: 'warning',
                                                formId: 'delete-ba-{{ $ba->id }}'
                                            })" class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-building-circle-xmark text-3xl mb-2"></i>
                                <p class="text-sm">Belum ada data Business Area.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($businessAreas->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $businessAreas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah / Edit -->
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative" @click.outside="modalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Business Area' : 'Tambah Business Area Baru'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="mt-5 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Kode Area <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="code" x-model="formData.code" required placeholder="contoh: DAOP-2"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none uppercase font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Business Area <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="formData.name" required placeholder="contoh: Daop 2 Bandung"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alamat Kantor
                    </label>
                    <textarea name="address" x-model="formData.address" rows="3" placeholder="Alamat lengkap..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none"></textarea>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                            class="h-4 w-4 text-kai-orange focus:ring-kai-orange border-slate-300 rounded">
                        <span class="text-xs font-medium text-slate-700">Status Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn-kai px-5 py-2 rounded-xl text-white font-semibold text-sm shadow-sm transition">
                        <i class="fa-solid fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
