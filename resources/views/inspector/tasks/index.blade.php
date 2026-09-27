@extends('layouts.app')

@section('title', 'Daftar Tugas Pemeriksaan')
@section('page_title', 'Tugas Pemeriksaan')
@section('page_subtitle', 'Seluruh tugas pemeriksaan keamanan perangkat PC dan Laptop yang ditugaskan kepada Anda')

@section('content')
<div class="space-y-6">
    <!-- Filters Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('inspector.tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. referensi, nomor aset, atau nama pegawai..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Tugas</label>
                <select name="status" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Status</option>
                    <option value="ASSIGNED" {{ request('status') === 'ASSIGNED' ? 'selected' : '' }}>Tugas Baru (Ditugaskan)</option>
                    <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="REVISION_REQUIRED" {{ request('status') === 'REVISION_REQUIRED' ? 'selected' : '' }}>Perlu Perbaikan</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Sudah Dikirim</option>
                    <option value="ADMIN_VERIFIED" {{ request('status') === 'ADMIN_VERIFIED' ? 'selected' : '' }}>Terverifikasi Admin</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Disetujui</option>
                    <option value="LOCKED" {{ request('status') === 'LOCKED' ? 'selected' : '' }}>Dikunci</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('inspector.tasks.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tasks List Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Daftar Tugas</h2>
            <span class="text-xs text-slate-500">Total: {{ $tasks->total() }} pemeriksaan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3 px-4 sm:px-6">No. Referensi</th>
                        <th class="py-3 px-4">Pegawai Penanggung Jawab</th>
                        <th class="py-3 px-4">Perangkat & Spesifikasi</th>
                        <th class="py-3 px-4">Tanggal Dokumen</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Temuan</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tasks as $task)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="font-bold text-blue-700 font-mono">{{ $task->reference_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $task->inspectionPeriod?->name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $task->employee?->name }}</div>
                                <div class="text-xs text-slate-500">{{ $task->employee?->unit?->name }} (NIPP: {{ $task->employee?->nipp }})</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $task->device?->device_type->value === 'LAPTOP' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ $task->device?->device_type->label() }}
                                    </span>
                                    <span class="font-mono text-xs font-bold text-slate-800">{{ $task->device?->asset_number }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $task->device?->brand }} {{ $task->device?->model }} ({{ $task->device?->hostname }})</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $task->document_date->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <x-status-badge :status="$task->status" />
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($task->checklist)
                                    <x-finding-badge :hasFindings="$task->checklist->hasNegativeFindings()" :count="$task->checklist->countNegativeFindings()" />
                                @else
                                    <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                @if ($task->status->value === 'ASSIGNED')
                                    <form method="POST" action="{{ route('inspector.tasks.start', $task) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-kai px-3 py-1.5 rounded-lg text-white font-semibold text-xs transition inline-flex items-center gap-1 shadow-xs">
                                            <i class="fa-solid fa-play text-[10px]"></i> Mulai
                                        </button>
                                    </form>
                                @elseif ($task->status->value === 'IN_PROGRESS')
                                    <a href="{{ route('inspector.tasks.checklist', $task) }}"
                                        class="btn-kai px-3 py-1.5 rounded-lg text-white font-semibold text-xs transition inline-flex items-center gap-1 shadow-xs">
                                        <i class="fa-solid fa-list-check text-[10px]"></i> Isi Checklist
                                    </a>
                                @elseif ($task->status->value === 'REVISION_REQUIRED')
                                    <a href="{{ route('inspector.tasks.revision', $task) }}"
                                        class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition inline-flex items-center gap-1 shadow-xs">
                                        <i class="fa-solid fa-pen text-[10px]"></i> Perbaiki
                                    </a>
                                @else
                                    <a href="{{ route('inspector.tasks.show', $task) }}"
                                        class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition inline-flex items-center gap-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i> Lihat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-check text-3xl mb-2"></i>
                                <p class="text-sm">Tidak ada tugas pemeriksaan yang sesuai kriteria filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
