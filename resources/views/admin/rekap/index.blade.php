<x-admin-layout :title="'Rekapitulasi & Ekspor Laporan'">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-[#0E7C86] mb-1">
                    <span>{{ $isPimpinan ? 'Pimpinan Eksekutif' : 'Admin DSDABM' }}</span>
                    <span>•</span>
                    <span>Fase 11</span>
                </div>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Rekapitulasi & Ekspor Laporan</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Saring seluruh pengaduan kerusakan infrastruktur Kota Bandung berdasarkan kriteria terperinci dan unduh laporan resmi Excel atau PDF.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Tombol Ekspor Excel -->
                <a
                    href="{{ $exportExcelRoute }}"
                    dusk="btn-export-excel"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-all focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6zm8.5-7.5l-2 3.5 2 3.5h-1.8l-1.2-2.3-1.2 2.3H8.5l2-3.5-2-3.5h1.8l1.2 2.3 1.2-2.3h1.8z"/>
                    </svg>
                    <span>Unduh Excel (.xlsx)</span>
                </a>

                <!-- Tombol Ekspor PDF -->
                <a
                    href="{{ $exportPdfRoute }}"
                    target="_blank"
                    dusk="btn-export-pdf"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm shadow-sm transition-all focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h4"/>
                    </svg>
                    <span>Cetak PDF (.pdf)</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Total -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="text-[11px] font-bold tracking-wider text-slate-500">Total Sesuai Filter</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-[#1F3A5F]">{{ number_format($stats['total']) }}</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Laporan</span>
                </div>
            </div>

            <!-- Selesai -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="text-[11px] font-bold tracking-wider text-emerald-600">Selesai Diperbaiki</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-emerald-700">{{ number_format($stats['selesai']) }}</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100) : 0 }}%
                    </span>
                </div>
            </div>

            <!-- Diproses -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="text-[11px] font-bold tracking-wider text-amber-600">Sedang Diproses</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-amber-700">{{ number_format($stats['diproses']) }}</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Aktif</span>
                </div>
            </div>

            <!-- Menunggu Verifikasi -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="text-[11px] font-bold tracking-wider text-sky-600">Menunggu Verifikasi</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-sky-700">{{ number_format($stats['menunggu']) }}</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200">Antrean</span>
                </div>
            </div>

            <!-- Kepuasan Warga -->
            <div class="col-span-2 md:col-span-1 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="text-[11px] font-bold tracking-wider text-amber-500">Kepuasan Warga</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-2xl font-extrabold text-slate-800">{{ $stats['avg_rating'] ?: '0.0' }}</span>
                        <svg class="h-5 w-5 text-amber-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">Skala 5</span>
                </div>
            </div>
        </div>

        <!-- Filter Multi-Kriteria Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h2 class="font-bold text-slate-800 text-base">Filter Kriteria Laporan</h2>
                </div>
                @if(array_filter($filters))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                        Filter Aktif
                    </span>
                @endif
            </div>

            <form method="GET" action="{{ url()->current() }}" dusk="form-filter-rekap" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Tanggal Mulai
                        </label>
                        <input
                            type="date"
                            id="tanggal_mulai"
                            name="tanggal_mulai"
                            value="{{ $filters['tanggal_mulai'] }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                    </div>

                    <!-- Tanggal Akhir -->
                    <div>
                        <label for="tanggal_akhir" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Tanggal Akhir
                        </label>
                        <input
                            type="date"
                            id="tanggal_akhir"
                            name="tanggal_akhir"
                            value="{{ $filters['tanggal_akhir'] }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                    </div>

                    <!-- Kecamatan -->
                    <div>
                        <label for="kecamatan_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Wilayah Kecamatan
                        </label>
                        <select
                            id="kecamatan_id"
                            name="kecamatan_id"
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                            <option value="">Semua Kecamatan</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec->id }}" {{ (string)$filters['kecamatan_id'] === (string)$kec->id ? 'selected' : '' }}>
                                    {{ $kec->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label for="kategori_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Kategori Infrastruktur
                        </label>
                        <select
                            id="kategori_id"
                            name="kategori_id"
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id }}" {{ (string)$filters['kategori_id'] === (string)$kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Status Penanganan
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                            <option value="">Semua Status</option>
                            <option value="menunggu_verifikasi" {{ $filters['status'] === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="diproses" {{ $filters['status'] === 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ $filters['status'] === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ $filters['status'] === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <!-- Prioritas -->
                    <div>
                        <label for="prioritas" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Tingkat Prioritas
                        </label>
                        <select
                            id="prioritas"
                            name="prioritas"
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                            <option value="">Semua Prioritas</option>
                            <option value="tinggi" {{ $filters['prioritas'] === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="sedang" {{ $filters['prioritas'] === 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="rendah" {{ $filters['prioritas'] === 'rendah' ? 'selected' : '' }}>Rendah</option>
                        </select>
                    </div>

                    <!-- Pencarian Kata Kunci -->
                    <div class="sm:col-span-2">
                        <label for="search" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Pencarian Kata Kunci
                        </label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Cari Nomor Tiket, Judul, Pelapor..."
                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a
                        href="{{ $resetRoute }}"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                    >
                        Reset Filter
                    </a>
                    <button
                        type="submit"
                        dusk="btn-terapkan-filter"
                        class="px-5 py-2.5 rounded-xl bg-[#0E7C86] hover:bg-[#0c6b74] text-white text-sm font-semibold shadow-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:ring-offset-2 flex items-center gap-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Pratinjau Laporan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Pratinjau Data Laporan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $laporanList->firstItem() ?? 0 }} - {{ $laporanList->lastItem() ?? 0 }} dari total {{ $laporanList->total() }} laporan</p>
                </div>
            </div>

            @if($laporanList->isEmpty())
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Tidak Ada Data Ditemukan</h4>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1">
                        Tidak ada laporan kerusakan yang cocok dengan kombinasi kriteria filter saat ini. Coba sesuaikan rentang tanggal atau kriteria lainnya.
                    </p>
                    <div class="mt-4">
                        <a href="{{ $resetRoute }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-[#0E7C86] bg-teal-50 hover:bg-teal-100">
                            Reset Seluruh Filter
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3">No. Tiket</th>
                                <th class="px-5 py-3">Tanggal Lapor</th>
                                <th class="px-5 py-3">Pelapor</th>
                                <th class="px-5 py-3">Kategori & Lokasi</th>
                                <th class="px-5 py-3">Prioritas</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Petugas</th>
                                <th class="px-5 py-3 text-center">Rating</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($laporanList as $item)
                                <tr class="hover:bg-slate-50/80 transition-colors" dusk="row-laporan-{{ $item->id }}">
                                    <!-- No Tiket -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="font-mono font-bold text-xs text-[#0E7C86] bg-teal-50 px-2.5 py-1 rounded-md border border-teal-200/60">
                                            {{ $item->nomor_tiket }}
                                        </span>
                                    </td>

                                    <!-- Tanggal Lapor -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                        {{ $item->created_at->format('d/m/Y') }}<br>
                                        <span class="text-[11px] text-slate-400">{{ $item->created_at->format('H:i') }} WIB</span>
                                    </td>

                                    <!-- Pelapor -->
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-800 text-xs">{{ $item->warga?->name ?? 'Anonim' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $item->warga?->phone ?? $item->warga?->email ?? '-' }}</div>
                                    </td>

                                    <!-- Kategori & Lokasi -->
                                    <td class="px-5 py-3.5">
                                        <div class="font-medium text-slate-800 text-xs truncate max-w-xs" title="{{ $item->judul }}">
                                            {{ $item->judul }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            <span class="font-medium text-slate-700">{{ $item->kategori?->nama_kategori }}</span> • 
                                            {{ $item->kelurahan?->nama_kelurahan }}, Kec. {{ $item->kelurahan?->kecamatan?->nama_kecamatan }}
                                        </div>
                                    </td>

                                    <!-- Prioritas -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if($item->prioritas === 'darurat')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-ping"></span>
                                                Darurat
                                            </span>
                                        @elseif($item->prioritas === 'tinggi')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                                Tinggi
                                            </span>
                                        @elseif($item->prioritas === 'sedang')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                Sedang
                                            </span>
                                        @elseif($item->prioritas === 'rendah')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                Rendah
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <x-badge-status :status="$item->status" />
                                    </td>

                                    <!-- Petugas Lapangan -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                        @if($item->disposisi && $item->disposisi->petugas)
                                            <div class="font-medium text-slate-700">{{ $item->disposisi->petugas->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $item->disposisi->nomor_surat_tugas }}</div>
                                        @else
                                            <span class="text-slate-400 italic">Belum ditugaskan</span>
                                        @endif
                                    </td>

                                    <!-- Rating -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                        @if($item->umpanBalik)
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold text-xs border border-amber-200">
                                                <span>{{ $item->umpanBalik->rating }}</span>
                                                <svg class="h-3 w-3 fill-current text-amber-400" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-300">-</span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                        <a
                                            href="{{ route('tracking.publik', $item->nomor_tiket) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-[#0E7C86] hover:bg-teal-50 transition-colors"
                                        >
                                            <span>Lihat</span>
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $laporanList->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
