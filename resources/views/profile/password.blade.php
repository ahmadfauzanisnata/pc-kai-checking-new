@extends('layouts.app')

@section('title', 'Ubah Kata Sandi - KAI PC Checking')
@section('page_title', 'Keamanan Akun')
@section('page_subtitle', 'Perbarui kata sandi Anda secara berkala untuk menjaga integritas dan keamanan data')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <div class="flex items-center gap-3.5 pb-6 border-b border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-kai-orange/10 text-kai-orange flex items-center justify-center text-xl border border-kai-orange/20">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800">Ubah Kata Sandi Akun</h2>
                <p class="text-xs text-slate-500">Pastikan menggunakan kombinasi huruf besar, angka, dan karakter khusus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.password.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Kata Sandi Saat Ini
                </label>
                <input type="password" name="current_password" required
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none @error('current_password') border-rose-400 @enderror">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Kata Sandi Baru
                </label>
                <input type="password" name="password" required
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none @error('password') border-rose-400 @enderror">
                <span class="text-[11px] text-slate-400 mt-1 block">Minimal 8 karakter.</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Konfirmasi Kata Sandi Baru
                </label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="btn-kai px-6 py-2.5 rounded-xl text-white font-semibold text-sm shadow-md transition active:scale-[0.98]">
                    <i class="fa-solid fa-key mr-1.5"></i> Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
