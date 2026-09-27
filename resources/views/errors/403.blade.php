@extends('layouts.guest')

@section('title', '403 - Akses Ditolak | PT Kereta Api Indonesia')
@section('body_class', 'kai-login-bg')

@section('content')
<div class="min-h-full flex flex-col justify-center py-8 sm:px-6 lg:px-8 relative">
    {{-- Decorative corner elements --}}
    <div class="absolute top-0 left-0 w-32 h-32 border-l-2 border-t-2 border-kai-orange/20 rounded-tl-3xl m-6"></div>
    <div class="absolute bottom-0 right-0 w-32 h-32 border-r-2 border-b-2 border-kai-orange/20 rounded-br-3xl m-6"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
    <div class="bg-white/95 backdrop-blur-md rounded-2xl p-8 shadow-2xl border border-white/20 text-center animate-fadeInUp">
        <div class="w-18 h-18 mx-auto rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-3xl shadow-sm mb-4">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="inline-block px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full mb-2 uppercase tracking-wider">
            HTTP 403 Forbidden
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Akses Ditolak</h1>
        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses yang memadai untuk membuka halaman atau fitur ini.' }}
        </p>
        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ url()->previous() }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition inline-flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i> Kembali
            </a>
            <a href="{{ route('dashboard') }}" class="btn-kai px-5 py-2.5 rounded-xl text-xs font-semibold text-white transition inline-flex items-center justify-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-house text-xs"></i> Ke Dashboard Utama
            </a>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
            PT Kereta Api Indonesia (Persero) &bull; Keamanan Sistem Informasi
        </div>
    </div>
</div>
</div>
@endsection
