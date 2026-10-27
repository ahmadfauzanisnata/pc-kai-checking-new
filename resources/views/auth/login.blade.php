@extends('layouts.guest')

@section('title', 'Masuk — Sistem PC/Laptop Checking · PT Kereta Api Indonesia')
@section('body_class', 'bg-white')

@section('content')
@php
    $formConfig = \App\Models\FormConfiguration::first();
    $logoUrl = null;
    if ($formConfig && $formConfig->logo_path) {
        $logoUrl = asset('storage/' . $formConfig->logo_path);
    } else {
        $logoUrl = asset('storage/logos/kai_symbol_only.png');
    }
@endphp

<div class="min-h-screen flex flex-col lg:flex-row">

    {{-- ============================================================ --}}
    {{-- KIRI: BRANDING PANEL (SESUAI REFERENSI FOTO USER)            --}}
    {{-- ============================================================ --}}
    <div class="hidden lg:flex lg:w-1/2 min-h-screen flex-col items-center justify-center relative overflow-hidden select-none p-8"
         style="background: linear-gradient(165deg, #0d2238 0%, #0a1828 50%, #071322 100%);">

        {{-- Center Content --}}
        <div class="relative z-10 flex flex-col items-center justify-center px-8 py-10 text-center my-auto">

            {{-- White Squircle Logo Card --}}
            <div class="bg-white flex items-center justify-center shadow-2xl transition-transform hover:scale-105 duration-300"
                 style="width: 180px; height: 180px; border-radius: 36px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45); padding: 24px;">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="KAI" class="select-none pointer-events-none"
                         style="width: 100%; height: auto; max-height: 80px; object-fit: contain;">
                @else
                    <span class="text-4xl font-black text-kai-navy-dark tracking-tight">K<span class="text-kai-orange">A</span>I</span>
                @endif
            </div>

            {{-- Title: Sistem PC/Laptop Checking --}}
            <h1 class="text-white font-extrabold tracking-tight"
                style="font-size: 2rem; line-height: 1.2; margin-top: 2rem;">
                Sistem PC/Laptop<br>Checking
            </h1>

            {{-- Subtitle: Platform monitoring dan pemeriksaan... --}}
            <p class="text-slate-300 font-normal"
                style="font-size: 0.925rem; line-height: 1.6; margin-top: 1rem; max-width: 22rem; margin-left: auto; margin-right: auto; opacity: 0.85;">
                Platform monitoring dan pemeriksaan<br>
                perangkat IT di seluruh wilayah operasional<br>
                PT Kereta Api Indonesia
            </p>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KANAN: FORM LOGIN                                            --}}
    {{-- ============================================================ --}}
    <div class="flex-1 flex items-center justify-center px-6 py-10 sm:px-12 lg:px-16 min-h-screen bg-slate-50">
        <div class="w-full max-w-[430px]">

            {{-- Mobile branding --}}
            <div class="lg:hidden mb-8 text-center flex flex-col items-center">
                <div class="bg-white flex items-center justify-center shadow-md border border-slate-100 mb-4"
                     style="width: 100px; height: 100px; border-radius: 22px; padding: 14px;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="KAI" style="width: 100%; height: auto; max-height: 46px; object-fit: contain;">
                    @else
                        <span class="text-2xl font-black text-kai-navy-dark">K<span class="text-kai-orange">A</span>I</span>
                    @endif
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 leading-tight">
                    Sistem PC/Laptop<br>Checking
                </h1>
                <p class="text-xs text-slate-500 mt-2 max-w-xs leading-relaxed">
                    Platform monitoring dan pemeriksaan perangkat IT di seluruh wilayah operasional PT Kereta Api Indonesia
                </p>
            </div>

            {{-- Card --}}
            <div class="bg-white shadow-[0_10px_40px_-15px_rgba(0,0,0,0.07)] border border-slate-100 p-8 sm:p-10"
                 style="border-radius: 24px;">

                {{-- Header --}}
                <div class="mb-7">
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Masuk ke Sistem</h2>
                    <p class="text-sm text-slate-500 mt-1">Gunakan NIPP atau email yang terdaftar.</p>
                </div>

                {{-- Alerts --}}
                @if (session('status'))
                    <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-5 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500 text-xs"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        <div class="flex items-center gap-2 font-medium">
                            <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                            <span>Terdapat kesalahan:</span>
                        </div>
                        <ul class="mt-1.5 list-disc list-inside text-xs space-y-0.5 text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    {{-- Email / NIPP --}}
                    <div>
                        <label for="login" class="block text-sm font-semibold text-slate-800 mb-2">
                            Email atau NIPP
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope text-sm"></i>
                            </div>
                            <input
                                id="login"
                                name="login"
                                type="text"
                                value="{{ old('login') }}"
                                required
                                autofocus
                                placeholder="contoh: SA001 atau email@kai.id"
                                class="w-full pl-10 pr-4 py-3 text-sm rounded-xl bg-[#f8fafc] border border-slate-200/90 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0f2440]/15 focus:border-[#0f2440] transition duration-150 @error('login') border-red-400 focus:ring-red-200 @enderror"
                            >
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-800 mb-2">
                            Kata Sandi
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </div>
                            <input
                                id="password"
                                name="password"
                                :type="show ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-11 py-3 text-sm rounded-xl bg-[#f8fafc] border border-slate-200/90 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0f2440]/15 focus:border-[#0f2440] transition duration-150 @error('password') border-red-400 focus:ring-red-200 @enderror"
                            >
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition cursor-pointer">
                                <i :class="show ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash'" class="text-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center pt-0.5">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-[#0f2440] focus:ring-[#0f2440]/20 cursor-pointer">
                        <label for="remember" class="ml-2.5 text-sm text-slate-600 font-normal cursor-pointer select-none">
                            Ingat saya
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="w-full py-3.5 px-4 bg-[#0f2440] hover:bg-[#18365c] text-white text-sm font-semibold rounded-xl transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#0f2440]/30 focus:ring-offset-2 flex items-center justify-center gap-2.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
                        <span>Masuk</span>
                    </button>
                </form>

            </div>

            {{-- Footer --}}
            <p class="text-center text-[11px] text-slate-400 mt-5">
                &copy; {{ date('Y') }} PT Kereta Api Indonesia (Persero) · v1.0
            </p>

        </div>
    </div>

</div>
@endsection
