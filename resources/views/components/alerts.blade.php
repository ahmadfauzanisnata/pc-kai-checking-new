@if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if (session('import_errors') && is_array(session('import_errors')))
    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5 font-semibold">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
                <span>{{ session('warning') ?? 'Catatan kesalahan saat import:' }}</span>
            </div>
            <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <ul class="mt-2.5 space-y-1 text-xs list-disc list-inside">
            @foreach (array_slice(session('import_errors'), 0, 15) as $err)
                <li>{{ $err }}</li>
            @endforeach
            @if (count(session('import_errors')) > 15)
                <li class="list-none text-slate-500 italic mt-1 font-medium">...dan {{ count(session('import_errors')) - 15 }} baris lainnya</li>
            @endif
        </ul>
    </div>
@elseif (session('warning'))
    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
            <span class="font-medium">{{ session('warning') }}</span>
        </div>
        <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if ($errors->any())
    <div x-data="{ show: true }" x-show="show" class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 font-semibold">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span>Terdapat kesalahan validasi:</span>
            </div>
            <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <ul class="mt-2 list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
