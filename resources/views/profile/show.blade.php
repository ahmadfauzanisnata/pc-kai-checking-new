@extends('layouts.app')

@section('title', 'Profil Pengguna - KAI PC Checking')
@section('page_title', 'Profil Saya')
@section('page_subtitle', 'Informasi akun dan spesimen tanda tangan resmi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <div class="flex items-center gap-4 pb-6 border-b border-slate-100">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-kai-navy-dark to-kai-navy text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-kai-navy/20 border border-kai-orange/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-kai-orange/10 text-kai-orange-dark border border-kai-orange/20">
                        {{ $user->role_display_name }}
                    </span>
                    @if ($user->nipp)
                        <span class="text-xs text-slate-500 font-mono">NIPP: {{ $user->nipp }}</span>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Lengkap
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alamat Email (Akun)
                    </label>
                    <input type="email" value="{{ $user->email }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed">
                    <span class="text-[11px] text-slate-400 mt-1 block">Email akun dikelola oleh Super Administrator.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor NIPP
                    </label>
                    <input type="text" value="{{ $user->nipp ?? '-' }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="contoh: 08123456789"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Business Area
                    </label>
                    <input type="text" value="{{ $user->businessArea?->name ?? 'Akses Global (Seluruh Area)' }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Unit Kerja
                    </label>
                    <input type="text" value="{{ $user->unit?->name ?? '-' }}" disabled
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 bg-slate-50 text-slate-500 rounded-xl cursor-not-allowed">
                </div>
            </div>

            <!-- Signature Upload & Preview -->
            <div class="pt-4 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Spesimen Tanda Tangan / Paraf Digital (PNG/JPG transparan)
                </label>
                <div class="flex items-center gap-6">
                    <div class="w-48 h-24 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 flex items-center justify-center p-2 overflow-hidden shadow-2xs">
                        @if ($user->signature_path)
                            <img src="{{ Storage::url($user->signature_path) }}" alt="Tanda Tangan" class="max-h-full object-contain">
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ada tanda tangan</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="signature" accept="image/png,image/jpeg,image/jpg"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-kai-orange/10 file:text-kai-orange hover:file:bg-kai-orange/20 transition">
                        <p class="text-[11px] text-slate-400 mt-1">Maksimal 2MB. Format PNG latar transparan disarankan untuk hasil dokumen cetak yang bersih.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="btn-kai px-6 py-2.5 rounded-xl text-white font-semibold text-sm shadow-md transition active:scale-[0.98]">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
