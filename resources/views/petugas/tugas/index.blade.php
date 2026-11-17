<x-admin-layout title="Daftar Tugas Lapangan Petugas">
    <div class="space-y-6">
        <!-- Header & Identitas Petugas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 border border-blue-200 text-blue-700">
                        <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                        Petugas Lapangan DSDABM
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Daftar Tugas Lapangan
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau instruksi kerja perbaikan infrastruktur publik, buka rute navigasi lokasi, dan laporkan bukti penyelesaian.
                </p>
            </div>
        </div>

        <!-- Flash Messages -->
        <x-flash />

        <!-- Quick Stat Cards (Responsive Grid 2 to 5 cols) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- Total Tugas -->
            <a href="{{ route('petugas.tugas.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-[#1F3A5F]/40 shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Tugas</span>
                    <div class="h-8 w-8 rounded-xl bg-[#1F3A5F]/10 text-[#1F3A5F] flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-black text-[#1F3A5F]">{{ $stats['total'] }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400">Semua tugas saya</p>
            </a>

            <!-- Ditugaskan -->
            <a href="{{ route('petugas.tugas.index', ['status' => 'ditugaskan']) }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-sky-400 shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700">Ditugaskan</span>
                    <div class="h-8 w-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-black text-sky-800">{{ $stats['ditugaskan'] }}</p>
                <p class="mt-0.5 text-[10px] text-sky-600 font-medium">Perlu Diterima</p>
            </a>

            <!-- Diterima -->
            <a href="{{ route('petugas.tugas.index', ['status' => 'diterima']) }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-indigo-400 shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Diterima</span>
                    <div class="h-8 w-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-black text-indigo-800">{{ $stats['diterima'] }}</p>
                <p class="mt-0.5 text-[10px] text-indigo-600 font-medium">Siap Dikerjakan</p>
            </a>

            <!-- Sedang Dikerjakan -->
            <a href="{{ route('petugas.tugas.index', ['status' => 'sedang_dikerjakan']) }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-amber-400 shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Pengerjaan</span>
                    <div class="h-8 w-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-black text-amber-800">{{ $stats['sedang_dikerjakan'] }}</p>
                <p class="mt-0.5 text-[10px] text-amber-600 font-medium">Dalam Proses Fisik</p>
            </a>

            <!-- Selesai -->
            <a href="{{ route('petugas.tugas.index', ['status' => 'selesai']) }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-400 shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Selesai</span>
                    <div class="h-8 w-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <p class="mt-2.5 text-2xl font-black text-emerald-800">{{ $stats['selesai'] }}</p>
                <p class="mt-0.5 text-[10px] text-emerald-600 font-medium">Tuntas Diperbaiki</p>
            </a>
        </div>

        <!-- Filter, Pengurutan & Pencarian Card (FR-19.3, FR-19.4) -->
        <x-card class="p-4 sm:p-5">
            <div class="flex flex-col gap-4">
                <!-- Status Tab Pill Filters -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                    <a
                        href="{{ route('petugas.tugas.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ empty($status) || $status === 'semua' ? 'bg-[#1F3A5F] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Semua Tugas ({{ $stats['total'] }})
                    </a>
                    <a
                        href="{{ route('petugas.tugas.index', array_filter(['status' => 'ditugaskan', 'search' => $search, 'sort' => $sort])) }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $status === 'ditugaskan' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Ditugaskan ({{ $stats['ditugaskan'] }})
                    </a>
                    <a
                        href="{{ route('petugas.tugas.index', array_filter(['status' => 'diterima', 'search' => $search, 'sort' => $sort])) }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $status === 'diterima' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Diterima ({{ $stats['diterima'] }})
                    </a>
                    <a
                        href="{{ route('petugas.tugas.index', array_filter(['status' => 'sedang_dikerjakan', 'search' => $search, 'sort' => $sort])) }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $status === 'sedang_dikerjakan' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Sedang Dikerjakan ({{ $stats['sedang_dikerjakan'] }})
                    </a>
                    <a
                        href="{{ route('petugas.tugas.index', array_filter(['status' => 'selesai', 'search' => $search, 'sort' => $sort])) }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $status === 'selesai' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Selesai ({{ $stats['selesai'] }})
                    </a>
                </div>

                <!-- Form Baris Bawah: Pengurutan & Pencarian -->
                <form action="{{ route('petugas.tugas.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif

                    <!-- Dropdown Pengurutan (FR-19.3) -->
                    <div class="flex items-center gap-2">
                        <label for="sort" class="text-xs font-bold text-slate-500 whitespace-nowrap">Urutkan:</label>
                        <select
                            id="sort"
                            name="sort"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-300 py-1.5 pl-3 pr-8 text-xs text-slate-700 bg-white focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 font-medium"
                        >
                            <option value="" {{ empty($sort) ? 'selected' : '' }}>Prioritas Tertinggi (Default)</option>
                            <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Tanggal Disposisi Terlama</option>
                            <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Tanggal Disposisi Terbaru</option>
                            <option value="tenggat" {{ $sort === 'tenggat' ? 'selected' : '' }}>Tenggat Waktu Terdekat</option>
                        </select>
                    </div>

                    <!-- Search Input Form (FR-19.4) -->
                    <div class="flex items-center gap-2 max-w-md w-full">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari tiket, judul, nomor SPT, lokasi..."
                                class="w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20"
                            >
                        </div>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white font-bold text-xs sm:text-sm shadow-sm transition"
                        >
                            Cari
                        </button>
                        @if ($search || $sort)
                            <a href="{{ route('petugas.tugas.index', array_filter(['status' => $status])) }}" class="text-xs text-slate-500 hover:text-rose-600 underline whitespace-nowrap">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </x-card>

        <!-- Daftar Kartu Tugas Lapangan (Responsive Mobile-First Grid - FR-19.1, FR-19.2, FR-19.10) -->
        @if ($tugasList->isEmpty())
            <x-card class="p-8 text-center">
                <x-empty-state
                    title="Tidak Ada Tugas Lapangan"
                    description="{{ $search ? 'Tidak ditemukan tugas yang cocok dengan kata kunci pencarian Anda.' : 'Saat ini belum ada tugas lapangan yang didelegasikan kepada Anda untuk kategori status ini.' }}"
                />
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($tugasList as $tugas)
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                        <!-- Card Header & Information (FR-19.2) -->
                        <div class="p-5 pb-3">
                            <!-- Nomor SPT & Status Penugasan -->
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="font-mono text-xs font-extrabold text-[#1F3A5F] bg-slate-100 px-2.5 py-1 rounded-lg">
                                    {{ $tugas->nomor_surat_tugas }}
                                </span>
                                <x-badge-status :status="$tugas->status_penugasan" />
                            </div>

                            <!-- Nomor Tiket Laporan Aduan (FR-19.2) -->
                            <div class="mb-2">
                                <span class="font-mono text-[11px] font-bold text-slate-500">
                                    Tiket: {{ $tugas->laporan->nomor_tiket }}
                                </span>
                            </div>

                            <!-- Prioritas & Kategori (FR-19.2) -->
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $tugas->laporan->prioritas === 'tinggi' ? 'bg-rose-100 text-rose-700' : ($tugas->laporan->prioritas === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    Prioritas {{ ucfirst($tugas->laporan->prioritas) }}
                                </span>
                                <span class="text-xs text-slate-400">&bull;</span>
                                <span class="text-xs font-semibold text-slate-500 truncate">
                                    {{ $tugas->laporan->kategori->nama_kategori ?? '-' }}
                                </span>
                            </div>

                            <!-- Judul Kerusakan (FR-19.2) -->
                            <h2 class="text-base font-bold text-slate-900 group-hover:text-[#0E7C86] transition line-clamp-2">
                                {{ $tugas->laporan->judul }}
                            </h2>

                            <!-- Ringkasan Kelurahan / Kecamatan (FR-19.2) -->
                            <div class="mt-2 flex items-start gap-1.5 text-xs text-slate-600">
                                <svg class="h-4 w-4 text-[#0E7C86] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="line-clamp-1">
                                    Kel. {{ $tugas->laporan->kelurahan->nama_kelurahan ?? '-' }}, Kec. {{ $tugas->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                                </span>
                            </div>

                            <!-- Foto Preview Mini & Cuplikan Instruksi -->
                            <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
                                @if($tugas->laporan->foto_bukti_sebelum)
                                    <img
                                        src="{{ asset('storage/' . $tugas->laporan->foto_bukti_sebelum) }}"
                                        alt="Bukti Sebelum"
                                        class="h-12 w-12 rounded-lg object-cover flex-shrink-0 border border-slate-200"
                                    >
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Instruksi Kerja</p>
                                    <p class="text-xs text-slate-600 truncate italic">
                                        "{{ $tugas->catatan_instruksi }}"
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer: Tanggal Disposisi & Aksi Navigasi (FR-19.2, FR-19.7, FR-19.8) -->
                        <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-3">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">
                                    Disposisi: {{ $tugas->tanggal_disposisi->translatedFormat('d M Y') }}
                                </span>
                                <div class="flex items-center gap-1.5 text-xs font-bold {{ $tugas->isOverdue() ? 'text-rose-600' : 'text-slate-700' }}">
                                    <span>Tenggat: {{ $tugas->tenggat_waktu->translatedFormat('d M Y') }}</span>
                                    @if($tugas->isOverdue())
                                        <span class="text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.2 rounded font-extrabold">Lewat Waktu</span>
                                    @elseif($tugas->isSelesai())
                                        <span class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.2 rounded font-extrabold">Selesai</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($tugas->status_penugasan === 'ditugaskan')
                                    <form method="POST" action="{{ route('petugas.tugas.terima', $tugas->id) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            dusk="btn-terima-tugas-card-{{ $tugas->id }}"
                                            title="Terima Tugas Ini"
                                            class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Terima</span>
                                        </button>
                                    </form>
                                @endif

                                <a
                                    href="{{ route('petugas.tugas.show', $tugas->id) }}"
                                    dusk="btn-detail-tugas-{{ $tugas->id }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white font-bold text-xs shadow-sm transition"
                                >
                                    <span>Buka Tugas</span>
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tugasList->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
