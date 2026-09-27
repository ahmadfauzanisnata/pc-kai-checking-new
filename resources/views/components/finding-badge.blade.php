@props(['hasFindings' => false, 'count' => 0])

@if ($hasFindings || $count > 0)
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
        <i class="fa-solid fa-triangle-exclamation mr-1 text-[10px]"></i>
        Ada Temuan ({{ $count }})
    </span>
@else
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-300">
        <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i>
        Sesuai Standar
    </span>
@endif
