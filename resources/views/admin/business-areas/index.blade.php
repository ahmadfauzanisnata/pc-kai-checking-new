@extends('layouts.app')

@section('title', 'Business Area Saya')
@section('page_title', 'Business Area')
@section('page_subtitle', 'Informasi wilayah kerja yang menjadi kewenangan Admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    @forelse ($businessAreas as $ba)
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-kai-orange/10 text-kai-orange flex items-center justify-center text-2xl font-bold border border-kai-orange/20">
                        <i class="fa-solid fa-building-flag"></i>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-kai-navy text-white">
                            {{ $ba->code }}
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $ba->name }}</h2>
                    </div>
                </div>
                <div>
                    @if ($ba->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 mr-1.5"></span> Status Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                            <span class="w-2 h-2 rounded-full bg-rose-600 mr-1.5"></span> Status Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Unit Kerja</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $ba->units_count }}</div>
                    <p class="text-xs text-slate-400 mt-0.5">Unit dalam area ini</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengguna Terdaftar</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $ba->users_count }}</div>
                    <p class="text-xs text-slate-400 mt-0.5">Admin & Pemeriksa</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode Pemeriksaan</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $ba->inspection_periods_count }}</div>
                    <p class="text-xs text-slate-400 mt-0.5">Tercatat di sistem</p>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Alamat Kantor Wilayah</h3>
                <p class="text-sm text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    {{ $ba->address ?: 'Alamat belum dilengkapi.' }}
                </p>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-12 text-center text-slate-400 shadow-sm border border-slate-200">
            <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 text-amber-500"></i>
            <p class="text-sm">Akun Anda belum diasosiasikan dengan Business Area tertentu.</p>
        </div>
    @endforelse
</div>
@endsection
