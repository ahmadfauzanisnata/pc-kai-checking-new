<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'PC/Laptop Checking System') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-[#F0F2F5] text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- SIDEBAR                                        -->
        <!-- ═══════════════════════════════════════════════ -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-[270px] bg-kai-navy-dark text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-2xl">

            <!-- Sidebar Header: KAI Logo -->
            <div class="h-[72px] flex items-center justify-between px-5 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    @php
                        $formConfig = \App\Models\FormConfiguration::first();
                        $logoUrl = null;
                        if ($formConfig && $formConfig->logo_path) {
                            $logoUrl = asset('storage/' . $formConfig->logo_path);
                        }
                    @endphp
                    <!-- Card Logo (Photo 2 style) -->
                    <div class="w-11 h-11 rounded-2xl bg-white shadow-md shadow-black/25 flex items-center justify-center p-1.5 shrink-0 group-hover:scale-105 transition-transform duration-200">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="KAI" class="max-h-full max-w-full object-contain">
                        @else
                            <div class="text-sm font-black tracking-tight">
                                <span class="text-kai-navy">K</span><span class="text-kai-orange">A</span><span class="text-kai-navy">I</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col min-w-0">
                        <div class="text-sm font-bold text-white tracking-wide leading-tight group-hover:text-kai-orange-light transition-colors">PT KAI</div>
                        <div class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase">PC/LAPTOP CHECKING</div>
                    </div>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- User Info -->
            <div class="px-5 py-3.5 border-b border-white/8 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-kai-orange/20 border border-kai-orange/30 flex items-center justify-center text-kai-orange font-bold text-xs shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ Auth::user()->role_display_name }}</div>
                    @if (Auth::user()->businessArea)
                        <div class="text-[10px] text-kai-orange/80 truncate">{{ Auth::user()->businessArea->name }}</div>
                    @endif
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5 custom-scrollbar text-[13px]">
                @php
                    $user = Auth::user();
                @endphp

                @if ($user->isSuperAdmin())
                    <!-- SUPER ADMIN MENU -->
                    <div class="px-3 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Utama</div>
                    <a href="{{ route('super-admin.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.dashboard') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Master Data</div>
                    <a href="{{ route('super-admin.users.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.users.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-users-gear w-5 text-center text-sm"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="{{ route('super-admin.business-areas.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.business-areas.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-building-flag w-5 text-center text-sm"></i>
                        <span>Business Area</span>
                    </a>
                    <a href="{{ route('super-admin.units.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.units.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-sitemap w-5 text-center text-sm"></i>
                        <span>Unit Kerja</span>
                    </a>
                    <a href="{{ route('admin.employees.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.employees.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-id-badge w-5 text-center text-sm"></i>
                        <span>Pegawai</span>
                    </a>
                    <a href="{{ route('admin.devices.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.devices.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-laptop w-5 text-center text-sm"></i>
                        <span>Perangkat</span>
                    </a>

                    <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pemeriksaan & Workflow</div>
                    <a href="{{ route('admin.inspection-periods.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.inspection-periods.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-calendar-check w-5 text-center text-sm"></i>
                        <span>Periode Pemeriksaan</span>
                    </a>
                    <a href="{{ route('super-admin.inspections.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.inspections.index') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-list-check w-5 text-center text-sm"></i>
                        <span>Semua Pemeriksaan</span>
                    </a>
                    <a href="{{ route('super-admin.inspections.pending') }}"
                        class="flex items-center justify-between px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.inspections.pending') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-clock-rotate-left w-5 text-center text-sm {{ request()->routeIs('super-admin.inspections.pending') ? '' : 'text-amber-400' }}"></i>
                            <span>Menunggu Persetujuan</span>
                        </div>
                    </a>
                    <a href="{{ route('super-admin.inspections.approved') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.inspections.approved') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-circle-check w-5 text-center text-sm {{ request()->routeIs('super-admin.inspections.approved') ? '' : 'text-emerald-400' }}"></i>
                        <span>Pemeriksaan Disetujui</span>
                    </a>
                    <a href="{{ route('super-admin.inspections.locked') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.inspections.locked') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-lock w-5 text-center text-sm"></i>
                        <span>Pemeriksaan Dikunci</span>
                    </a>
                    <a href="{{ route('super-admin.inspections.archived') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.inspections.archived') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-box-archive w-5 text-center text-sm"></i>
                        <span>Arsip</span>
                    </a>

                    <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Laporan & Audit</div>
                    <a href="{{ route('reports.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('reports.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice w-5 text-center text-sm"></i>
                        <span>Laporan Excel / PDF</span>
                    </a>
                    <a href="{{ route('super-admin.form-configuration.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.form-configuration.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-sliders w-5 text-center text-sm"></i>
                        <span>Konfigurasi Formulir</span>
                    </a>
                    <a href="{{ route('super-admin.activity-logs.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('super-admin.activity-logs.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center text-sm"></i>
                        <span>Activity Log</span>
                    </a>

                @elseif ($user->isAdmin())
                    <!-- ADMIN MENU -->
                    <div class="px-3 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Utama</div>
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.dashboard') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Master Data Area</div>
                    <a href="{{ route('admin.business-areas.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.business-areas.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-building-flag w-5 text-center text-sm"></i>
                        <span>Business Area</span>
                    </a>
                    <a href="{{ route('admin.units.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.units.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-sitemap w-5 text-center text-sm"></i>
                        <span>Unit Kerja</span>
                    </a>
                    <a href="{{ route('admin.employees.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.employees.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-id-badge w-5 text-center text-sm"></i>
                        <span>Pegawai</span>
                    </a>
                    <a href="{{ route('admin.devices.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.devices.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-laptop w-5 text-center text-sm"></i>
                        <span>Perangkat</span>
                    </a>

                    <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pemeriksaan</div>
                    <a href="{{ route('admin.inspection-periods.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.inspection-periods.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-calendar-check w-5 text-center text-sm"></i>
                        <span>Periode Pemeriksaan</span>
                    </a>
                    <a href="{{ route('admin.assignments.create') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.assignments.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-user-plus w-5 text-center text-sm"></i>
                        <span>Buat Penugasan</span>
                    </a>
                    <a href="{{ route('admin.verifications.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('admin.verifications.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-clipboard-check w-5 text-center text-sm"></i>
                        <span>Verifikasi Pemeriksaan</span>
                    </a>
                    <a href="{{ route('reports.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('reports.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice w-5 text-center text-sm"></i>
                        <span>Laporan</span>
                    </a>

                @else
                    <!-- INSPECTOR MENU -->
                    <div class="px-3 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Utama</div>
                    <a href="{{ route('inspector.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('inspector.dashboard') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Tugas Pemeriksaan</div>
                    <a href="{{ route('inspector.tasks.index', ['status' => 'ASSIGNED']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request('status') === 'ASSIGNED' ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-bell w-5 text-center text-sm {{ request('status') === 'ASSIGNED' ? '' : 'text-sky-400' }}"></i>
                        <span>Tugas Baru</span>
                    </a>
                    <a href="{{ route('inspector.tasks.index', ['status' => 'IN_PROGRESS']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request('status') === 'IN_PROGRESS' ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-spinner w-5 text-center text-sm {{ request('status') === 'IN_PROGRESS' ? '' : 'text-kai-orange' }}"></i>
                        <span>Sedang Dikerjakan</span>
                    </a>
                    <a href="{{ route('inspector.tasks.index', ['status' => 'REVISION_REQUIRED']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request('status') === 'REVISION_REQUIRED' ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-triangle-exclamation w-5 text-center text-sm {{ request('status') === 'REVISION_REQUIRED' ? '' : 'text-rose-400' }}"></i>
                        <span>Perlu Perbaikan</span>
                    </a>
                    <a href="{{ route('inspector.tasks.index', ['status' => 'submitted']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request('status') === 'submitted' ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-paper-plane w-5 text-center text-sm {{ request('status') === 'submitted' ? '' : 'text-amber-400' }}"></i>
                        <span>Sudah Dikirim</span>
                    </a>
                    <a href="{{ route('inspector.history') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('inspector.history') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center text-sm"></i>
                        <span>Riwayat Pemeriksaan</span>
                    </a>
                @endif

                <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pengaturan</div>
                <a href="{{ route('profile.show') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg transition font-medium {{ request()->routeIs('profile.*') ? 'sidebar-active text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-user-circle w-5 text-center text-sm"></i>
                    <span>Profil & Password</span>
                </a>
            </div>

            <!-- Sidebar Footer / Logout -->
            <div class="p-3 border-t border-white/10">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-rose-300 bg-rose-500/10 border border-rose-500/15 hover:bg-rose-500/20 transition active:scale-[0.98]">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- MAIN CONTENT                                   -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header class="h-[68px] bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-kai-navy leading-tight">
                            @yield('page_title', 'Dashboard')
                        </h1>
                        <p class="text-[11px] text-slate-500 hidden sm:block">
                            @yield('page_subtitle', 'PC/Laptop Security & Compliance Checking System')
                        </p>
                    </div>
                </div>

                <!-- Topbar Actions & User Dropdown -->
                <div class="flex items-center gap-3">
                    <!-- Business Area Tag -->
                    @if (Auth::user()->businessArea)
                        <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-kai-blue-light border border-kai-blue/20 text-[11px] text-kai-navy font-semibold">
                            <i class="fa-solid fa-map-pin text-[10px] text-kai-orange"></i>
                            <span>{{ Auth::user()->businessArea->name }}</span>
                        </div>
                    @endif

                    <!-- User Role Badge -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-kai-navy/5 text-kai-navy border border-kai-navy/10">
                        {{ Auth::user()->role_display_name }}
                    </span>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                        <button type="button" @click="dropdownOpen = !dropdownOpen"
                            class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-50 transition focus:outline-none">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-kai-orange to-kai-orange-dark text-white font-bold text-xs flex items-center justify-center shadow-md shadow-kai-orange/20">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div x-show="dropdownOpen" x-transition
                            class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-xl border border-slate-100 py-1.5 z-50 divide-y divide-slate-100" style="display: none;">
                            <div class="px-4 py-2.5">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</p>
                                @if (Auth::user()->nipp)
                                    <p class="text-[10px] text-kai-orange font-mono mt-0.5">NIPP: {{ Auth::user()->nipp }}</p>
                                @endif
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile.show') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                                    <i class="fa-solid fa-user w-4 text-slate-400"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="{{ route('profile.password') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                                    <i class="fa-solid fa-key w-4 text-slate-400"></i>
                                    <span>Ubah Password</span>
                                </a>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition font-medium">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-rose-400"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 animate-fadeInUp">
                <!-- Flash Alerts Component -->
                <x-alerts />

                <!-- Page Content -->
                @yield('content')
            </main>

            <!-- Global Footer -->
            <footer class="bg-white border-t border-slate-200/80 px-6 py-3 text-[11px] text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-1">
                <div>
                    &copy; {{ date('Y') }} <span class="font-semibold text-kai-navy">PT Kereta Api Indonesia (Persero)</span>. Hak cipta dilindungi.
                </div>
                <div class="text-slate-400">
                    Sistem Pemeriksaan Keamanan Perangkat v1.0
                </div>
            </footer>
        </div>
    </div>

    <!-- SweetAlert2 confirmation helper -->
    <script>
        function confirmAction(options) {
            Swal.fire({
                title: options.title || 'Apakah Anda yakin?',
                text: options.text || 'Tindakan ini tidak dapat dibatalkan.',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmButtonColor || '#F47920',
                cancelButtonColor: '#64748b',
                confirmButtonText: options.confirmButtonText || 'Ya, Lanjutkan',
                cancelButtonText: options.cancelButtonText || 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (options.formId) {
                        document.getElementById(options.formId).submit();
                    } else if (typeof options.callback === 'function') {
                        options.callback();
                    }
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
