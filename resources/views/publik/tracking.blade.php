<x-warga-layout title="Lacak Progres Laporan Tiket Publik">
    <div class="space-y-8 max-w-5xl mx-auto">
        <!-- Hero Header & Formulir Pencarian Tiket -->
        <section class="relative overflow-hidden rounded-xl bg-[#0A2240] border border-[#16375E] text-white p-6 sm:p-10 shadow-sm">
            <!-- Subtle architectural grid texture -->
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>

            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1 text-xs font-semibold border border-white/15 text-[#F59E0B] mb-3">
                    <span class="h-2 w-2 rounded-full bg-[#F59E0B]"></span>
                    Transparansi Pelayanan Publik DSDABM &bull; INFRA-DAYA
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Lacak Status & Progres Tiket
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Masukkan nomor tiket resmi pengaduan Anda untuk memantau status verifikasi, lembar surat tugas petugas, dan dokumentasi foto hasil perbaikan di lapangan.
                </p>

                <!-- Search Input Form -->
                <form action="{{ route('tracking.publik') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input
                            type="text"
                            name="nomor_tiket"
                            dusk="input-lacak-tiket"
                            value="{{ $nomorTiket }}"
                            placeholder="Contoh: INFRA-20261014-0001"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0A2240] focus:ring-1 focus:ring-[#0A2240] uppercase font-mono font-bold"
                        >
                    </div>
                    <button
                        type="submit"
                        dusk="btn-submit-lacak"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#F59E0B] hover:bg-[#d97706] text-slate-950 px-6 py-3 text-sm font-bold shadow-sm transition-all cursor-pointer whitespace-nowrap"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Cari Laporan</span>
                    </button>
                </form>
            </div>
        </section>

        <!-- Hasil Pencarian -->
        @if ($searched)
            @if ($laporan)
                <!-- Laporan Ditemukan -->
                <div class="space-y-6">
                    <!-- Kartu Ringkasan Status & Identitas -->
                    <x-card class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="font-mono text-xs sm:text-sm font-extrabold px-3 py-1 bg-slate-100 text-[#1F3A5F] rounded-xl border border-slate-200">
                                        {{ $laporan->nomor_tiket }}
                                    </span>
                                    <x-badge-status :status="$laporan->status" />
                                    <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full {{ $laporan->prioritas === 'tinggi' ? 'bg-rose-100 text-rose-700' : ($laporan->prioritas === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        Prioritas {{ ucfirst($laporan->prioritas) }}
                                    </span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900">
                                    {{ $laporan->judul }}
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                    Diajukan pada {{ $laporan->created_at->translatedFormat('d F Y, H:i') }} WIB &bull; Kategori: <strong class="text-slate-700">{{ $laporan->kategori->nama_kategori ?? '-' }}</strong>
                                </p>
                            </div>

                            @if($laporan->disposisi)
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-right sm:min-w-[180px]">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Surat Tugas (SPT)</span>
                                    <span class="font-mono text-xs font-bold text-[#1F3A5F] block">{{ $laporan->disposisi->nomor_surat_tugas }}</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Petugas: <strong>{{ $laporan->disposisi->petugas->name ?? '-' }}</strong></span>
                                </div>
                            @endif
                        </div>

                        <!-- Progress Bar Stepper Visual -->
                        <div class="py-6">
                            @php
                                $statusRank = match($laporan->status) {
                                    'menunggu_verifikasi' => 1,
                                    'diproses' => $laporan->disposisi && $laporan->disposisi->status_penugasan === 'sedang_dikerjakan' ? 3 : 2,
                                    'selesai' => 4,
                                    'ditolak' => -1,
                                    default => 1,
                                };
                            @endphp

                            @if ($statusRank === -1)
                                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900">
                                    <div class="flex items-start gap-3">
                                        <svg class="h-6 w-6 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div>
                                            <h3 class="font-bold text-sm">Laporan Ditolak Verifikator</h3>
                                            <p class="text-xs text-rose-700 mt-1">Alasan Penolakan: <em>"{{ $laporan->alasan_penolakan }}"</em></p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-4 gap-2 text-center">
                                    <!-- Step 1 -->
                                    <div class="flex flex-col items-center">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition {{ $statusRank >= 1 ? 'bg-[#0E7C86] text-white' : 'bg-slate-100 text-slate-400' }}">
                                            1
                                        </div>
                                        <span class="text-[11px] sm:text-xs font-bold mt-2 text-slate-800">Diajukan</span>
                                        <span class="text-[10px] text-slate-400 hidden sm:inline">Laporan Masuk</span>
                                    </div>

                                    <!-- Step 2 -->
                                    <div class="flex flex-col items-center">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition {{ $statusRank >= 2 ? 'bg-[#0E7C86] text-white' : 'bg-slate-100 text-slate-400' }}">
                                            2
                                        </div>
                                        <span class="text-[11px] sm:text-xs font-bold mt-2 {{ $statusRank >= 2 ? 'text-slate-800' : 'text-slate-400' }}">Diverifikasi</span>
                                        <span class="text-[10px] text-slate-400 hidden sm:inline">SPT Terbit</span>
                                    </div>

                                    <!-- Step 3 -->
                                    <div class="flex flex-col items-center">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition {{ $statusRank >= 3 ? 'bg-amber-500 text-white ring-4 ring-amber-100' : 'bg-slate-100 text-slate-400' }}">
                                            3
                                        </div>
                                        <span class="text-[11px] sm:text-xs font-bold mt-2 {{ $statusRank >= 3 ? 'text-amber-800' : 'text-slate-400' }}">Pengerjaan</span>
                                        <span class="text-[10px] text-slate-400 hidden sm:inline">Tim di Lokasi</span>
                                    </div>

                                    <!-- Step 4 -->
                                    <div class="flex flex-col items-center">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition {{ $statusRank >= 4 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400' }}">
                                            4
                                        </div>
                                        <span class="text-[11px] sm:text-xs font-bold mt-2 {{ $statusRank >= 4 ? 'text-emerald-800' : 'text-slate-400' }}">Selesai</span>
                                        <span class="text-[10px] text-slate-400 hidden sm:inline">Tuntas Diperbaiki</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-card>

                    <!-- BEFORE / AFTER PHOTO COMPARISON -->
                    <x-card class="p-6 sm:p-8 space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#0E7C86]">Dokumentasi Lapangan</span>
                            <h2 class="text-lg font-black text-slate-900 mt-0.5">
                                Bukti Fisik Infrastruktur (Sebelum vs Sesudah)
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <!-- Foto Sebelum -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold tracking-wider text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                                        Sebelum Perbaikan
                                    </span>
                                    <span class="text-[11px] text-slate-400">{{ $laporan->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 h-64 sm:h-72">
                                    @if ($laporan->foto_bukti_sebelum)
                                        <a href="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full relative group cursor-zoom-in">
                                            <img
                                                src="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}"
                                                alt="Foto Kerusakan Awal"
                                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            >
                                            <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-black/75 text-white text-xs font-semibold backdrop-blur-sm shadow-md">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                    Lihat Foto Penuh
                                                </span>
                                            </div>
                                        </a>
                                    @else
                                        <div class="h-full flex items-center justify-center text-xs text-slate-400">
                                            Tidak ada foto awal
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 italic">
                                    "{{ $laporan->deskripsi_kondisi }}"
                                </p>
                            </div>

                            <!-- Foto Sesudah -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                        Setelah Perbaikan
                                    </span>
                                    @if ($laporan->selesai_at)
                                        <span class="text-[11px] text-slate-400">{{ $laporan->selesai_at->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 h-64 sm:h-72">
                                    @if ($laporan->foto_bukti_sesudah)
                                        <a href="{{ asset('storage/' . $laporan->foto_bukti_sesudah) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full relative group cursor-zoom-in">
                                            <img
                                                src="{{ asset('storage/' . $laporan->foto_bukti_sesudah) }}"
                                                alt="Foto Setelah Perbaikan"
                                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            >
                                            <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-black/75 text-white text-xs font-semibold backdrop-blur-sm shadow-md">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                    Lihat Foto Penuh
                                                </span>
                                            </div>
                                        </a>
                                    @else
                                        <div class="h-full flex flex-col items-center justify-center text-center p-6 bg-slate-50">
                                            <div class="h-12 w-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mb-2">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <p class="text-xs font-bold text-slate-700">Dalam Tahap Pengerjaan</p>
                                            <p class="text-[11px] text-slate-400 mt-1 max-w-xs">
                                                Foto bukti penyelesaian akan ditampilkan secara otomatis setelah petugas lapangan menuntaskan perbaikan fisik.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                @if ($laporan->catatan_perbaikan)
                                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-950">
                                        <strong class="block mb-0.5 text-emerald-800">Catatan Petugas Lapangan:</strong>
                                        {{ $laporan->catatan_perbaikan }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-card>

                    @if ($laporan->umpanBalik)
                        <!-- Kartu Evaluasi Kepuasan Pelapor (Publik) -->
                        <x-card class="p-5 sm:p-6 bg-gradient-to-r from-amber-50/70 via-white to-amber-50/40 border border-amber-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-amber-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-900">Ulasan &amp; Kepuasan Warga Pelapor</h3>
                                </div>
                                <div class="flex items-center gap-1.5 text-amber-500">
                                    @for($s = 1; $s <= 5; $s++)
                                        @if($s <= $laporan->umpanBalik->rating)
                                            <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @else
                                            <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        @endif
                                    @endfor
                                    <span class="text-xs font-black text-slate-800 ml-1">{{ $laporan->umpanBalik->rating }}/5</span>
                                </div>
                            </div>
                            @if ($laporan->umpanBalik->komentar)
                                <p class="text-xs text-slate-700 italic mt-2.5">
                                    "{{ $laporan->umpanBalik->komentar }}"
                                </p>
                            @endif
                        </x-card>
                    @endif

                    <!-- PETA LOKASI & AUDIT TIMELINE -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Peta Geospasial Titik Kerusakan -->
                        <x-card class="overflow-hidden p-0 flex flex-col justify-between">
                            <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Lokasi Infrastruktur</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-500">
                                    Kel. {{ $laporan->kelurahan->nama_kelurahan ?? '-' }}
                                </span>
                            </div>

                            <div id="trackingMap" class="w-full h-64 sm:h-72 bg-slate-100 z-0"></div>

                            <div class="p-3 bg-white border-t border-slate-100 text-xs flex items-center justify-between text-slate-500">
                                <span>Kecamatan {{ $laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}, Kota Bandung</span>
                                <a
                                    href="https://www.google.com/maps/dir/?api=1&destination={{ $laporan->latitude }},{{ $laporan->longitude }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-[#0E7C86] font-bold hover:underline"
                                >
                                    Buka Google Maps &rarr;
                                </a>
                            </div>
                        </x-card>

                        <!-- Riwayat Audit Timeline Lengkap -->
                        <x-card class="p-5 sm:p-6 space-y-4">
                            <h2 class="text-xs font-bold tracking-wider text-[#1F3A5F] border-b border-slate-100 pb-2">
                                Jejak Transparansi (Timeline Audit)
                            </h2>
                            <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                                @forelse ($laporan->riwayatStatus as $riwayat)
                                    <div class="flex items-start gap-3 text-xs">
                                        <div class="mt-1 h-2.5 w-2.5 rounded-full {{ $riwayat->status === 'selesai' ? 'bg-emerald-500 ring-4 ring-emerald-100' : ($riwayat->status === 'diproses' ? 'bg-amber-500 ring-4 ring-amber-100' : ($riwayat->status === 'ditolak' ? 'bg-rose-500' : 'bg-slate-400')) }} flex-shrink-0"></div>
                                        <div class="flex-1 pb-2 border-b border-slate-100 last:border-0">
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-slate-800 capitalize">{{ str_replace('_', ' ', $riwayat->status) }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }}</span>
                                            </div>
                                            <p class="text-slate-600 text-[11px] mt-0.5 leading-relaxed">
                                                {{ $riwayat->keterangan ?? 'Status laporan diperbarui oleh sistem.' }}
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Belum ada catatan riwayat status.</p>
                                @endforelse
                            </div>
                        </x-card>
                    </div>
                </div>
            @else
                <!-- Tiket Tidak Ditemukan -->
                <x-card class="p-10 text-center space-y-4">
                    <div class="h-16 w-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="max-w-md mx-auto">
                        <h2 class="text-lg font-extrabold text-slate-900">Tiket Tidak Ditemukan</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Nomor tiket <strong class="text-rose-600 font-mono">{{ $nomorTiket }}</strong> tidak terdaftar dalam database INFRA-DAYA. Pastikan nomor tiket yang Anda masukkan sudah benar (misalnya format <em>INFRA-YYYYMMDD-XXXX</em>).
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('tracking.publik') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            <span>Ulangi Pencarian</span>
                        </a>
                    </div>
                </x-card>
            @endif
        @else
            <!-- Tampilan Awal (Panduan Pelacakan) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-card class="p-6 space-y-3">
                    <div class="h-10 w-10 rounded-xl bg-[#1F3A5F]/10 text-[#1F3A5F] flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Format Nomor Tiket</h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Nomor tiket diberikan secara otomatis saat Anda mengirimkan pengaduan, berformat unik harian: <strong>INFRA-YYYYMMDD-XXXX</strong>.
                    </p>
                </x-card>

                <x-card class="p-6 space-y-3">
                    <div class="h-10 w-10 rounded-xl bg-[#0E7C86]/10 text-[#0E7C86] flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Akses Tanpa Login</h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Siapa pun dapat melacak progres perbaikan fasilitas umum tanpa perlu masuk akun, demi keterbukaan informasi publik.
                    </p>
                </x-card>

                <x-card class="p-6 space-y-3">
                    <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Lihat di Peta Sebaran</h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Ingin melihat kerusakan di sekitar Anda? Buka peta sebaran interaktif untuk mengecek titik masalah yang sedang ditangani.
                    </p>
                    <a href="{{ route('peta.publik') }}" class="text-xs font-bold text-[#0E7C86] hover:underline inline-block pt-1">
                        Buka Peta Publik &rarr;
                    </a>
                </x-card>
            </div>
        @endif
    </div>

    <!-- Leaflet Init Script jika ada laporan -->
    @if ($searched && $laporan)
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        @endpush

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lat = {{ (float) $laporan->latitude }};
                const lng = {{ (float) $laporan->longitude }};
                const mapContainer = document.getElementById('trackingMap');

                if (mapContainer && typeof L !== 'undefined') {
                    const map = L.map('trackingMap').setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    const marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(`
                        <div class="text-xs p-1">
                            <strong class="text-[#1F3A5F] block mb-0.5">${@json($laporan->judul)}</strong>
                            <span class="text-slate-500 block">Kel. ${@json($laporan->kelurahan->nama_kelurahan ?? '-')}, Kec. ${@json($laporan->kelurahan->kecamatan->nama_kecamatan ?? '-')}</span>
                        </div>
                    `).openPopup();
                }
            });
        </script>
    @endif
</x-warga-layout>
