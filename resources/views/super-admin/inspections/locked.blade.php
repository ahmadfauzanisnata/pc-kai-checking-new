@extends('layouts.app')

@section('title', 'Pemeriksaan Terkunci (Locked) - KAI PC Checking')
@section('page_title', 'Pemeriksaan Terkunci')
@section('page_subtitle', 'Data pemeriksaan yang dibekukan dari modifikasi dan hanya dapat dibuka kembali oleh Super Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pemeriksaan Terkunci (Locked)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Data pemeriksaan yang telah dibekukan dari segala perubahan. Hanya Super Admin yang berwenang membuka kunci dengan mencatat alasan resmi.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold rounded-xl flex items-center gap-1.5">
                <i class="fa-solid fa-lock text-amber-600"></i>
                {{ $inspections->total() }} Terkunci
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">No. Referensi</th>
                        <th class="px-5 py-3.5">Wilayah</th>
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-5 py-3.5">Perangkat</th>
                        <th class="px-5 py-3.5">Waktu Kunci</th>
                        <th class="px-5 py-3.5 text-right">Aksi Buka Kunci</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($inspections as $insp)
                    <tr class="table-row-hover transition" x-data="{ openUnlock: false }">
                        <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-kai-navy">
                            {{ $insp->reference_number }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="font-bold text-slate-800">{{ $insp->inspectionPeriod?->businessArea?->code ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900">{{ $insp->employee?->name }}</div>
                            <div class="text-slate-400 text-[11px] font-mono">NIPP: {{ $insp->employee?->nipp }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $insp->device?->hostname }}</div>
                            <div class="text-slate-400 font-mono text-[11px]">{{ $insp->device?->asset_number }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="text-slate-700">{{ $insp->locked_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('reports.pdf', $insp) }}" target="_blank"
                                    class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Unduh Formulir PDF Satuan">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <button @click="openUnlock = !openUnlock" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition shadow-xs active:scale-[0.98]">
                                    <i class="fa-solid fa-lock-open mr-1"></i> Buka Kunci
                                </button>
                            </div>

                            <!-- Modal Unlock -->
                            <div x-show="openUnlock" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                                <div class="bg-white rounded-2xl max-w-md w-full p-6 text-left shadow-2xl border border-slate-200/80" @click.outside="openUnlock = false">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                                            <i class="fa-solid fa-key"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-slate-900">Buka Kunci Pemeriksaan</h3>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $insp->reference_number }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-4">Membuka kunci ini akan mengembalikan hak pengeditan. Alasan pembukaan kunci wajib dicatat untuk kepatuhan audit trail PT KAI.</p>
                                    
                                    <form action="{{ route('super-admin.inspections.unlock', $insp) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Pembukaan Kunci *</label>
                                            <textarea name="reason" rows="3" required class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange" placeholder="Contoh: Permintaan perbaikan data NIPP oleh Admin Wilayah..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="openUnlock = false" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                                Batal
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs active:scale-[0.98]">
                                                Konfirmasi Buka Kunci
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-lock-open text-3xl mb-2 text-slate-300 block"></i>
                            Tidak ada pemeriksaan yang sedang terkunci.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $inspections->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
