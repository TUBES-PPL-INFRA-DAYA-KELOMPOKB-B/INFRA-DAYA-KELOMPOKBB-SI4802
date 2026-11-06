<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Panel Instansi' }} - INFRA-DAYA Kota Bandung</title>

    <!-- Favicon Resmi INFRA-DAYA -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS (untuk peta geospasial) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex bg-[#EEF3F6]">
        <!-- Mobile Sidebar Backdrop -->
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
            style="display: none;"
        ></div>

        <!-- Sidebar (Harmonized Command Deck Theme: #070B14 + Electric Blue + Amber) -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-[#070B14] text-white transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:inset-auto flex flex-col shadow-2xl border-r border-white/10"
        >
            <!-- Logo & Brand Header in Sidebar -->
            <div class="flex h-20 items-center justify-between px-6 border-b border-white/10 bg-[#070B14]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="bg-white px-2.5 py-1.5 rounded-xl shadow-xs inline-flex items-center transition-transform group-hover:scale-102">
                        <x-application-logo variant="full" class="h-7 sm:h-8 w-auto max-w-[155px] object-contain" />
                    </div>
                    <span class="sr-only">INFRA-DAYA</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Role Badge in Sidebar -->
            <div class="px-6 py-4 border-b border-white/10 bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white text-sm shadow">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs text-slate-200 font-semibold truncate">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">
                                {{ auth()->user()->role ?? 'Admin' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nav Links in Sidebar -->
            <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-1.5 text-sm font-medium">
                @php
                    $role = auth()->user()->role ?? 'admin';
                @endphp

                <!-- Menu Umum / Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard', 'pimpinan.dashboard') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard Utama</span>
                </a>

                @if ($role === 'admin')
                    <!-- ADMIN MENU -->
                    <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Master Data</div>
                    <a href="{{ route('admin.kecamatan.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.kecamatan.*', 'admin.kelurahan.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Wilayah (Kec/Kel)</span>
                    </a>
                    <a href="{{ route('admin.users.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="{{ route('admin.kategori.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.kategori.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Kategori Kerusakan</span>
                    </a>

                    <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Operasional</div>
                    <a href="{{ route('admin.verifikasi.index', [], false) }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.verifikasi.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Verifikasi Laporan</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.disposisi.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.disposisi.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Disposisi Petugas</span>
                    </a>
                    <a href="{{ route('admin.rekap.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.rekap.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Rekap &amp; Ekspor Data</span>
                    </a>
                @elseif ($role === 'petugas')
                    <!-- PETUGAS MENU -->
                    <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Tugas Lapangan</div>
                    <a href="{{ route('petugas.tugas.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('petugas.tugas.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Daftar Tugas Saya</span>
                    </a>
                @elseif ($role === 'pimpinan')
                    <!-- PIMPINAN MENU -->
                    <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Eksekutif &amp; Analitik</div>
                    <a href="{{ route('pimpinan.sla.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('pimpinan.sla.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Dashboard Kinerja &amp; SLA</span>
                    </a>
                    <a href="{{ route('pimpinan.peta.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('pimpinan.peta.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>Heatmap &amp; Peta Sebaran</span>
                    </a>
                    <a href="{{ route('pimpinan.rekap.index', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('pimpinan.rekap.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Unduh Laporan Eksekutif</span>
                    </a>
                @endif

                <div class="pt-6 pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Aplikasi Publik</div>
                <a href="{{ route('peta.publik', [], false) }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition-all">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Peta Publik</span>
                </a>
                <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition-all">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Halaman Beranda</span>
                </a>
            </nav>

            <!-- Bottom Logout in Sidebar -->
            <div class="p-4 border-t border-white/10 bg-[#070B14]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-rose-300 hover:bg-rose-500/20 hover:text-white transition-colors">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between shadow-xs sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-none">{{ $header ?? $title ?? 'Dashboard' }}</h1>
                        <p class="text-xs text-slate-500 mt-1">Sistem Tanggap Kerusakan Infrastruktur Publik Kota Bandung</p>
                    </div>
                </div>

                <!-- Top Right Items -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2.5 p-1.5 sm:px-3 sm:py-2 rounded-xl hover:bg-slate-100 transition-colors">
                            <div class="h-8 w-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="text-left hidden sm:block">
                                <span class="text-xs font-semibold text-slate-700 block leading-tight">{{ auth()->user()->name ?? 'User' }}</span>
                                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block leading-tight">{{ auth()->user()->role ?? 'Admin' }}</span>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute right-0 mt-2 w-56 rounded-2xl bg-[#070B14] border border-white/10 p-2 shadow-2xl text-white z-50">
                            <div class="px-3 py-2 border-b border-white/10">
                                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    Role: {{ strtoupper(auth()->user()->role ?? 'Admin') }}
                                </span>
                            </div>
                            <a href="{{ url('/') }}" target="_blank" class="block rounded-lg px-3 py-2 text-xs font-medium text-slate-300 hover:bg-white/10">
                                Halaman Beranda
                            </a>
                            <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-xs font-medium text-slate-300 hover:bg-white/10">
                                Profil Saya
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left rounded-lg px-3 py-2 text-xs font-medium text-rose-400 hover:bg-rose-500/10">
                                    Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                <div class="max-w-7xl mx-auto">
                    <x-flash />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>
</html>
