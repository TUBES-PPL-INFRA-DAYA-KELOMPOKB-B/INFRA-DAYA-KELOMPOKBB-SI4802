<x-admin-layout title="Daftar Aduan & Antrean Verifikasi">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#0E7C86] mb-1">
                    <span>Admin DSDABM</span>
                    <span>•</span>
                    <span>PBI-13</span>
                </div>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Daftar Aduan Kerusakan Infrastruktur</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau, cari, dan saring seluruh laporan pengaduan kerusakan infrastruktur Kota Bandung.</p>
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <span class="px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                    Total: <strong class="text-[#1F3A5F]">{{ $laporanList->total() }}</strong> laporan
                </span>
            </div>
        </div>

        <!-- Flash Message & Error Alert -->
        <x-flash />

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Kesalahan Penyaringan Data:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter Multi-Kriteria (PBI-13) -->
        <x-card class="p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.verifikasi.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Pencarian -->
                    <div>
                        <label for="search" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Kata Kunci Pencarian</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="No. Tiket / Judul / Pelapor..."
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition"
                        >
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label for="status" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Status Laporan</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition"
                        >
                            <option value="">Semua Status</option>
                            <option value="menunggu_verifikasi" {{ $status === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="diproses" {{ $status === 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <!-- Filter Kategori -->
                    <div>
                        <label for="kategori_id" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Kategori Infrastruktur</label>
                        <select
                            id="kategori_id"
                            name="kategori_id"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id }}" {{ (string)$kategoriId === (string)$kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kecamatan -->
                    <div>
                        <label for="kecamatan_id" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Kecamatan Lokasi</label>
                        <select
                            id="kecamatan_id"
                            name="kecamatan_id"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition"
                        >
                            <option value="">Semua Kecamatan</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec->id }}" {{ (string)$kecamatanId === (string)$kec->id ? 'selected' : '' }}>
                                    {{ $kec->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Periode Tanggal Mulai -->
                    <div>
                        <label for="tanggal_mulai" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Periode Mulai</label>
                        <input
                            type="date"
                            id="tanggal_mulai"
                            name="tanggal_mulai"
                            value="{{ $tanggalMulai }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition"
                        >
                    </div>

                    <!-- Periode Tanggal Akhir -->
                    <div>
                        <label for="tanggal_akhir" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Periode Akhir</label>
                        <input
                            type="date"
                            id="tanggal_akhir"
                            name="tanggal_akhir"
                            value="{{ $tanggalAkhir }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition"
                        >
                    </div>

                    <!-- Tombol Aksi Filter -->
                    <div class="sm:col-span-2 flex items-end gap-2 pt-1">
                        <button
                            type="submit"
                            dusk="btn-filter"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1F3A5F] text-white text-xs font-bold rounded-lg hover:bg-[#162a45] shadow-sm transition"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Terapkan Filter
                        </button>
                        @if($search || $status || $kategoriId || $kecamatanId || $tanggalMulai || $tanggalAkhir)
                            <a
                                href="{{ route('admin.verifikasi.index') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-200 transition"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reset Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </x-card>

        <!-- Tabel Daftar Aduan -->
        @if($laporanList->isEmpty())
            <x-empty-state
                title="Data laporan tidak ditemukan"
                description="Tidak ada laporan kerusakan yang sesuai dengan kata kunci pencarian atau kriteria filter yang Anda pilih."
            />
        @else
            <x-card class="overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Tiket</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Judul Aduan</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pelapor</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lokasi Wilayah</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tanggal Lapor</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($laporanList as $laporan)
                                <tr class="hover:bg-slate-50/70 transition-colors {{ $laporan->status === 'menunggu_verifikasi' ? 'border-l-4 border-l-amber-400' : '' }}" dusk="laporan-row-{{ $laporan->id }}">
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold text-[#1F3A5F] bg-slate-100 px-2 py-1 rounded">{{ $laporan->nomor_tiket }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <p class="font-bold text-slate-800 truncate">{{ $laporan->judul }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ Str::limit($laporan->deskripsi_kondisi, 60) }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-medium text-slate-700">{{ $laporan->warga->name ?? '-' }}</p>
                                        <p class="text-xs text-slate-400">{{ $laporan->warga->email ?? '' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="text-xs font-medium text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                                            {{ $laporan->kategori->nama_kategori ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-slate-600">
                                        <p class="font-medium text-slate-700">Kel. {{ $laporan->kelurahan->nama_kelurahan ?? '-' }}</p>
                                        <p class="text-slate-400">Kec. {{ $laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <x-badge-status :status="$laporan->status" />
                                    </td>
                                    <td class="px-4 py-3.5 text-center text-xs text-slate-600 whitespace-nowrap">
                                        {{ $laporan->created_at->format('d M Y') }}
                                        <br>
                                        <span class="text-slate-400">{{ $laporan->created_at->format('H:i') }} WIB</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.verifikasi.show', $laporan->id, false) }}"
                                            dusk="btn-detail-{{ $laporan->id }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-[#0E7C86] hover:bg-[#0b636b] rounded-lg shadow-sm transition"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($laporanList->hasPages())
                    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                        {{ $laporanList->links() }}
                    </div>
                @endif
            </x-card>
        @endif
    </div>
</x-admin-layout>
