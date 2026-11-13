@extends('layouts.guest')

@section('title', 'Masuk — Sistem PC/Laptop Checking · PT Kereta Api Indonesia')
@section('body_class', 'bg-white')

@section('content')
@php
    $formConfig = null;
    try {
        $formConfig = \App\Models\FormConfiguration::first();
    } catch (\Throwable $e) {
        $formConfig = null;
    }

    $logoUrl = null;
    if (file_exists(public_path('images/kai-logo.png'))) {
        $logoUrl = asset('images/kai-logo.png');
    } elseif ($formConfig && $formConfig->logo_path && file_exists(public_path('storage/' . $formConfig->logo_path))) {
        $logoUrl = asset('storage/' . $formConfig->logo_path);
    } elseif (file_exists(public_path('storage/logos/kai_symbol_only.png'))) {
        $logoUrl = asset('storage/logos/kai_symbol_only.png');
    }
@endphp

<div class="min-h-screen w-full flex flex-col lg:flex-row bg-white relative overflow-x-hidden selection:bg-kai-orange/20 selection:text-kai-navy-dark">

    {{-- ============================================================ --}}
    {{-- KIRI: FOTO KERETA DENGAN POTONGAN DIAGONAL LEBIH KE KANAN    --}}
    {{-- ============================================================ --}}
    <div class="relative w-full lg:w-[60%] xl:w-[64%] 2xl:w-[66%] h-[300px] sm:h-[360px] lg:h-auto lg:min-h-screen shrink-0 overflow-hidden z-10 select-none
                [clip-path:polygon(0_0,100%_0,100%_88%,0_100%)]
                lg:[clip-path:polygon(0_0,100%_0,88%_100%,0_100%)]">

        {{-- Foto Kereta KAI (Full View, Posisi Fokus Kereta Utuh) --}}
        <img src="{{ asset('images/train-kai.webp') }}"
             alt="Kereta Api Indonesia"
             style="object-position: 20% center;"
             class="absolute inset-0 w-full h-full object-cover filter brightness-[0.92] contrast-[1.08] pointer-events-none">

        {{-- Gradient Overlay untuk Kedalaman Kontras & Mood Dramatis --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/25 to-slate-950/40"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-slate-950/20"></div>

        {{-- Top-Left Badge (Gaya Branding di Sudut Kiri Atas seperti Foto Referensi) --}}
        <div class="absolute top-6 left-6 z-20 flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-950/50 backdrop-blur-md border border-white/15 text-white shadow-lg">
            <span class="w-2 h-2 rounded-full bg-kai-orange animate-pulse"></span>
            <span class="text-[11px] sm:text-xs font-semibold tracking-wider uppercase text-slate-100">PT Kereta Api Indonesia</span>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KANAN: FORM LOGIN & LOGO KAI DI ATASNYA                      --}}
    {{-- ============================================================ --}}
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-8 sm:px-10 lg:px-12 xl:px-14 min-h-full -mt-8 sm:-mt-10 lg:mt-0 z-20 bg-white">
        <div class="w-full max-w-[390px] sm:max-w-[410px]">

            {{-- Logo KAI di Atas Form Login (Langsung Menyatu Tanpa Kotak/Border) --}}
            <div class="flex flex-col items-center mb-8 text-center">
                <div class="mb-4 flex items-center justify-center">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}"
                             alt="KAI"
                             class="h-12 sm:h-14 w-auto object-contain select-none pointer-events-none">
                    @else
                        <span class="text-3xl sm:text-4xl font-black text-kai-navy tracking-tight select-none">
                            K<span class="text-kai-orange">A</span>I
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-[26px] font-extrabold text-slate-900 tracking-tight leading-snug">
                    Sistem PC/Laptop Checking
                </h1>
            </div>

            {{-- Alerts / Pesan Status --}}
            @if (session('status'))
                <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200/80 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5 rounded-2xl bg-blue-50 border border-blue-200/80 px-4 py-3 text-sm text-blue-700 flex items-center gap-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-info text-blue-500 text-sm shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-5 rounded-2xl bg-red-50 border border-red-200/80 px-4 py-3.5 text-sm text-red-700 shadow-sm">
                    <div class="flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-sm shrink-0"></i>
                        <span>Terdapat kesalahan:</span>
                    </div>
                    <ul class="mt-1.5 list-disc list-inside text-xs space-y-0.5 text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Login --}}
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4 sm:space-y-5">
                @csrf

                {{-- Input: Email / NIPP --}}
                <div>
                    <label for="login" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Email atau NIPP
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-user text-sm"></i>
                        </div>
                        <input
                            id="login"
                            name="login"
                            type="text"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            placeholder="contoh: SA001 atau email@kai.id"
                            class="w-full pl-11 pr-4 py-3.5 text-sm rounded-2xl bg-[#F1F4F9] border border-slate-200/70 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-kai-navy focus:ring-4 focus:ring-kai-navy/10 transition duration-150 @error('login') border-red-400 focus:ring-red-200 @enderror"
                        >
                    </div>
                </div>

                {{-- Input: Kata Sandi dengan Show/Hide Toggle --}}
                <div>
                    <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input
                            id="password"
                            name="password"
                            :type="show ? 'text' : 'password'"
                            required
                            placeholder="••••••••"
                            class="w-full pl-11 pr-11 py-3.5 text-sm rounded-2xl bg-[#F1F4F9] border border-slate-200/70 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-kai-navy focus:ring-4 focus:ring-kai-navy/10 transition duration-150 @error('password') border-red-400 focus:ring-red-200 @enderror"
                        >
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition cursor-pointer"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                        >
                            <i :class="show ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash'" class="text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Checkbox: Ingat Saya --}}
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="w-4 h-4 rounded-md border-slate-300 text-kai-navy focus:ring-2 focus:ring-kai-navy/20 cursor-pointer transition"
                        >
                        <span class="text-xs sm:text-sm text-slate-600 group-hover:text-slate-900 transition">
                            Ingat saya
                        </span>
                    </label>
                </div>

                {{-- Tombol Submit Gradien dengan Ikon Panah Kanan Lingkaran (Sesuai Tombol di Foto 1) --}}
                <div class="pt-2">
                    <button
                        type="submit"
                        class="group relative w-full py-2.5 pl-6 pr-2.5 bg-gradient-to-r from-kai-navy-dark via-kai-navy to-kai-blue hover:from-[#0b1b30] hover:via-[#152e4a] hover:to-[#175892] text-white font-semibold rounded-2xl flex items-center justify-between shadow-[0_10px_25px_-5px_rgba(15,36,64,0.35)] hover:shadow-[0_14px_30px_-5px_rgba(15,36,64,0.45)] hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 cursor-pointer"
                    >
                        <span class="text-sm sm:text-base font-semibold tracking-wide ml-auto mr-auto pl-6">
                            Masuk
                        </span>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white text-kai-navy-dark flex items-center justify-center shadow-md shrink-0 transition-transform duration-200 group-hover:translate-x-1">
                            <i class="fa-solid fa-arrow-right text-xs sm:text-sm"></i>
                        </div>
                    </button>
                </div>
            </form>

            {{-- Footer Hak Cipta --}}
            <p class="text-center text-xs text-slate-400 mt-8 font-normal">
                &copy; {{ date('Y') }} PT Kereta Api Indonesia (Persero) · v1.0
            </p>

        </div>
    </div>

</div>
@endsection
