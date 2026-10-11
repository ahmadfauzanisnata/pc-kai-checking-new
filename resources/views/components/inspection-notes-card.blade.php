@props(['inspection'])

@php
    $notes = $inspection->notes ? $inspection->notes->sortByDesc('id') : collect();
    $historiesWithReasons = $inspection->statusHistories ? $inspection->statusHistories->whereNotNull('reason')->sortByDesc('id') : collect();
    
    // Find latest resubmission note from inspector
    $lastResubmitNote = $notes->first(function($n) {
        return str_starts_with($n->note, 'Perbaikan telah dilakukan:') || $n->note_type === \App\Enums\NoteType::GENERAL;
    });

    // Find latest revision note from admin/super admin
    $lastRevisionNote = $notes->first(function($n) {
        return in_array($n->note_type, [\App\Enums\NoteType::ADMIN_REVISION, \App\Enums\NoteType::SUPER_ADMIN_REVISION]);
    });
@endphp

<div class="space-y-4">
    {{-- Highlight: Catatan Perbaikan dari Pemeriksa (if resubmitted) --}}
    @if($lastResubmitNote && in_array($inspection->status, [\App\Enums\InspectionStatus::RESUBMITTED, \App\Enums\InspectionStatus::PENDING_ADMIN_VERIFICATION, \App\Enums\InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL]))
        <div class="bg-emerald-50/90 border border-emerald-300 rounded-2xl p-5 shadow-xs">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 text-base">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-200/70 px-2 py-0.5 rounded">
                                Catatan Perbaikan Pemeriksa
                            </span>
                            <span class="text-xs font-bold text-slate-800">{{ $lastResubmitNote->user?->name ?? 'Pemeriksa' }}</span>
                        </div>
                        <span class="text-[11px] text-emerald-700 font-mono">
                            {{ $lastResubmitNote->created_at?->format('d M Y H:i') }} WIB
                        </span>
                    </div>
                    <div class="mt-2.5 bg-white p-3.5 rounded-xl border border-emerald-200 text-sm text-slate-900 font-medium whitespace-pre-wrap leading-relaxed shadow-2xs">
                        {{ $lastResubmitNote->note }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Highlight: Catatan Permintaan Revisi / Pengembalian Super Admin --}}
    @if($lastRevisionNote && in_array($inspection->status, [\App\Enums\InspectionStatus::REVISION_REQUIRED, \App\Enums\InspectionStatus::ADMIN_REVISION_REQUIRED]))
        @php
            $isSuperAdminReturn = $lastRevisionNote->note_type === \App\Enums\NoteType::SUPER_ADMIN_REVISION || $inspection->status === \App\Enums\InspectionStatus::ADMIN_REVISION_REQUIRED;
        @endphp
        <div class="{{ $isSuperAdminReturn ? 'bg-amber-50/90 border-amber-300' : 'bg-rose-50 border-rose-300' }} border rounded-2xl p-5 shadow-xs">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl {{ $isSuperAdminReturn ? 'bg-amber-100 border-amber-200 text-amber-800' : 'bg-rose-100 border-rose-200 text-rose-700' }} border flex items-center justify-center shrink-0 text-base">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider {{ $isSuperAdminReturn ? 'text-amber-900 bg-amber-200/80' : 'text-rose-800 bg-rose-200/70' }} px-2 py-0.5 rounded">
                                {{ $isSuperAdminReturn ? 'Catatan Pengembalian Super Admin' : 'Catatan Permintaan Revisi' }}
                            </span>
                            <span class="text-xs font-bold text-slate-800">{{ $lastRevisionNote->user?->name ?? ($isSuperAdminReturn ? 'Super Admin' : 'Admin') }}</span>
                        </div>
                        <span class="text-[11px] {{ $isSuperAdminReturn ? 'text-amber-800' : 'text-rose-700' }} font-mono">
                            {{ $lastRevisionNote->created_at?->format('d M Y H:i') }} WIB
                        </span>
                    </div>
                    <div class="mt-2.5 bg-white p-3.5 rounded-xl border {{ $isSuperAdminReturn ? 'border-amber-200' : 'border-rose-200' }} text-sm text-slate-900 font-medium whitespace-pre-wrap leading-relaxed shadow-2xs">
                        {{ $lastRevisionNote->note }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Full Communication & Notes History Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-kai-navy/10 text-kai-navy flex items-center justify-center text-sm">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Catatan & Komunikasi</h3>
                    <p class="text-[11px] text-slate-400">Pesan dan catatan yang ditulis oleh Admin, Super Admin, atau Pemeriksa</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">
                {{ $notes->count() }} Catatan
            </span>
        </div>

        <div class="p-5 space-y-4">
            @forelse($notes as $n)
                @php
                    $isInspector = $n->user?->isInspector() ?? false;
                    $isAdmin = $n->user?->isAdmin() ?? false;
                    $isSuper = $n->user?->isSuperAdmin() ?? false;
                @endphp
                <div class="p-4 rounded-xl border {{ $isInspector ? 'bg-amber-50/30 border-amber-200/80' : ($isAdmin ? 'bg-sky-50/30 border-sky-200/80' : 'bg-indigo-50/30 border-indigo-200/80') }} transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-2 border-b border-slate-200/60">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-xs text-slate-900">{{ $n->user?->name ?? 'Pengguna' }}</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $isInspector ? 'bg-amber-100 text-amber-800' : ($isAdmin ? 'bg-sky-100 text-sky-800' : 'bg-indigo-100 text-indigo-800') }}">
                                {{ $n->user?->role_display_name ?? 'Pengguna' }}
                            </span>
                            @if($n->note_type)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded border border-slate-200 text-slate-600 bg-white">
                                    {{ $n->note_type->label() }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">
                            {{ $n->created_at?->format('d M Y H:i') }} WIB
                        </span>
                    </div>
                    <div class="mt-2.5 text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                        {{ $n->note }}
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-xs text-slate-400 italic">
                    <i class="fa-regular fa-comment-dots text-slate-300 text-2xl block mb-1.5"></i>
                    Belum ada riwayat catatan atau pesan komunikasi khusus pada pemeriksaan ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
