<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Pilih wilayah kerusakan' }} - INFRA-DAYA Kota Bandung</title>

    <!-- Favicon Resmi INFRA-DAYA -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
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

        <!-- Sidebar Portal Warga -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-[#070B14] text-white transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:inset-auto flex flex-col shadow-2xl border-r border-white/10"
        >
            <!-- Logo & Brand Header in Sidebar -->
            <div class="flex h-20 items-center justify-between px-6 border-b border-white/10 bg-[#070B14]">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="bg-white px-2.5 py-1.5 rounded-xl shadow-xs inline-flex items-center transition-transform group-hover:scale-102">
                        <x-application-logo variant="full" class="h-7 sm:h-8 w-auto max-w-[155px] object-contain" />
                    </div>
                    <span class="sr-only">INFRA-DAYA</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Portal Warga Badge in Sidebar -->
            <div class="px-6 py-4 border-b border-white/10 bg-white/[0.02]">
                <div class="text-sm font-bold text-white tracking-tight">Portal Warga</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">KOTA BANDUNG</div>
            </div>

            <!-- Nav Links in Sidebar -->
            <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-1.5 text-sm font-medium">
                <!-- Beranda -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all text-slate-300 hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Beranda</span>
                </a>

                <!-- Buat Laporan (Active Pill) -->
                <a href="{{ route('warga.laporan.pilih-wilayah', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('warga.laporan.pilih-wilayah', 'warga.laporan.create') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Buat Laporan</span>
                </a>

                <!-- Laporan Saya -->
                <a href="{{ route('warga.laporan.saya', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('warga.laporan.saya') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan Saya</span>
                </a>

                <!-- Notifikasi -->
                <a href="#notifikasi" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all text-slate-300 hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Notifikasi</span>
                </a>

                <!-- Peta Publik -->
                <a href="{{ route('peta.publik', [], false) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all text-slate-300 hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Peta Publik</span>
                </a>
            </nav>

            <!-- Bottom Brand Info in Sidebar -->
            <div class="p-4 border-t border-white/10 bg-white/[0.02]">
                <div class="text-xs font-bold text-slate-300">INFRA-DAYA</div>
                <div class="text-[11px] text-slate-400">Infrastruktur &amp; Daya Tanggap</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-300 hover:bg-rose-500/20 hover:text-white transition-colors">
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar Header -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between shadow-xs sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-none">{{ $header ?? $title ?? 'Pilih wilayah kerusakan' }}</h1>
                    </div>
                </div>

                <!-- Top Right Items -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <span class="text-xs font-medium text-slate-600 hidden sm:inline">
                        Warga &bull; {{ auth()->user()->name ?? 'Akun contoh' }}
                    </span>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2.5 p-1.5 sm:px-3 sm:py-2 rounded-xl hover:bg-slate-100 transition-colors">
                            <div class="h-8 w-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'W', 0, 1)) }}
                            </div>
                            <div class="text-left hidden sm:block">
                                <span class="text-xs font-semibold text-slate-700 block leading-tight">{{ auth()->user()->name ?? 'Akun contoh' }}</span>
                                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block leading-tight">Warga</span>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute right-0 mt-2 w-56 rounded-2xl bg-[#070B14] border border-white/10 p-2 shadow-2xl text-white z-50">
                            <div class="px-3 py-2 border-b border-white/10">
                                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Warga' }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    Role: WARGA
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

