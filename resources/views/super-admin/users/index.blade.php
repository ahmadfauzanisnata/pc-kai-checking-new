@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna')
@section('page_subtitle', 'Kelola akun Super Admin, Admin, dan Pemeriksa dalam sistem')

@section('content')
<div class="space-y-6">
    <!-- Action Header & Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('super-admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau NIPP..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Peran (Role)</label>
                <select name="role_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Akun</label>
                <select name="status" class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-kai-orange/20 focus:border-kai-orange outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-kai-navy text-white text-xs sm:text-sm font-semibold hover:bg-kai-navy-dark transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('super-admin.users.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition text-xs sm:text-sm" title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-800">Daftar Pengguna Sistem</h2>
                <p class="text-xs text-slate-500">Total terdaftar: {{ $users->total() }} pengguna</p>
            </div>
            <a href="{{ route('super-admin.users.create') }}"
                class="btn-kai px-4 py-2.5 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider font-semibold">
                        <th class="py-3.5 px-4 sm:px-6">Nama & Email</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4">Business Area & Unit</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Terakhir Masuk</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                @if ($user->nipp)
                                    <div class="text-[11px] text-blue-600 font-mono">NIPP: {{ $user->nipp }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $user->isSuperAdmin() ? 'bg-purple-100 text-purple-800' : ($user->isAdmin() ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $user->role_display_name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700 font-medium">{{ $user->businessArea?->name ?? 'Akses Global' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $user->unit?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 mr-1.5"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-500">
                                {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Belum pernah' }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('super-admin.users.edit', $user) }}"
                                        class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Pengguna">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" action="{{ route('super-admin.users.toggle-status', $user) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-2 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                            title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                            <i class="fa-solid {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </form>

                                    <!-- Reset Password Trigger -->
                                    <button type="button" onclick="promptResetPassword('{{ $user->id }}', '{{ $user->name }}')"
                                        class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Reset Kata Sandi">
                                        <i class="fa-solid fa-key"></i>
                                    </button>

                                    @if ($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}" id="delete-user-{{ $user->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmAction({
                                                title: 'Hapus Pengguna?',
                                                text: 'Pengguna {{ $user->name }} akan dihapus.',
                                                icon: 'warning',
                                                formId: 'delete-user-{{ $user->id }}'
                                            })" class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Pengguna">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-user-xmark text-3xl mb-2"></i>
                                <p class="text-sm">Tidak ditemukan data pengguna yang sesuai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Hidden Reset Password Form -->
<form id="reset-password-form" method="POST" action="" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="new_password" id="new_password_input">
</form>

<script>
    function promptResetPassword(userId, userName) {
        Swal.fire({
            title: 'Reset Kata Sandi',
            text: 'Masukkan kata sandi baru untuk ' + userName + ':',
            input: 'text',
            inputValue: 'password',
            showCancelButton: true,
            confirmButtonText: 'Reset Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            inputValidator: (value) => {
                if (!value || value.length < 6) {
                    return 'Kata sandi minimal 6 karakter!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('reset-password-form');
                form.action = `/super-admin/users/${userId}/reset-password`;
                document.getElementById('new_password_input').value = result.value;
                form.submit();
            }
        });
    }
</script>
@endsection
