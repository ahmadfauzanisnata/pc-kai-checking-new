@props(['status'])

@php
    use App\Enums\InspectionStatus;
    $statusEnum = $status instanceof InspectionStatus ? $status : InspectionStatus::tryFrom($status);
@endphp

@if ($statusEnum)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusEnum->badgeClass() }}">
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-current opacity-70"></span>
        {{ $statusEnum->label() }}
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-300">
        {{ $status }}
    </span>
@endif
