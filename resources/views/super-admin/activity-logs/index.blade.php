@extends('layouts.app')

@section('title', 'Activity Log')
@section('page_title', 'Activity Log & Audit Trail')
@section('page_subtitle', 'Catatan seluruh aktivitas dan perubahan penting dalam sistem')

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('super-admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian Deskripsi/IP</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Modul</label>
                <select name="module" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pengguna</label>
                <select name="user_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Pengguna</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('super-admin.activity-logs.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Catatan Aktivitas</h2>
            <span class="text-xs text-slate-500">Total riwayat: {{ $logs->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">Waktu Kejadian</th>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Tindakan & Modul</th>
                        <th class="py-3.5 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3.5 px-4 sm:px-6">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap text-slate-500">
                                <div class="font-medium text-slate-800">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($log->user)
                                    <div class="font-semibold text-slate-900">{{ $log->user->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $log->user->role_display_name }}</div>
                                @else
                                    <span class="text-slate-400 italic">Sistem Otomatis</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-kai-navy/10 text-kai-navy border border-kai-navy/20">
                                    {{ $log->module }}
                                </span>
                                <div class="text-xs text-slate-600 font-medium mt-0.5 capitalize">{{ $log->action }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap text-slate-400 font-mono text-xs">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clock-rotate-left text-3xl mb-2"></i>
                                <p class="text-sm">Belum ada catatan aktivitas yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
