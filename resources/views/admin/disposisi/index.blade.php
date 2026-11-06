<x-admin-layout title="Disposisi Penugasan Petugas">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#0E7C86] mb-1">
                    <span>Admin DSDABM</span>
                    <span>•</span>
                    <span>Fase 6</span>
                </div>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Disposisi Penugasan Lapangan</h1>
                <p class="text-sm text-slate-500 mt-1">Terbitkan Surat Perintah Tugas (SPT), tugaskan petugas teknis, dan pantau tenggat waktu perbaikan.</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.disposisi.create') }}"
                    dusk="btn-create-disposisi"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-bold rounded-xl shadow-sm transition-all"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Terbitkan Surat Tugas</span>
                </a>
            </div>
        </div>

        <!-- Flash Message -->
        <x-flash />

        <!-- Metrics Overview Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
            <x-card class="p-4 border-l-4 border-l-[#1F3A5F]">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total SPT</p>
                <p class="text-2xl font-extrabold text-[#1F3A5F] mt-1">{{ $totalDisposisi }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Surat tugas diterbitkan</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-sky-500">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ditugaskan</p>
                <p class="text-2xl font-extrabold text-sky-600 mt-1">{{ $totalDitugaskan }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Menunggu respon petugas</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dikerjakan</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $totalSedangDikerjakan }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Proses di lapangan</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Selesai</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $totalSelesai }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Tugas rampung</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-[#F2A93B] col-span-2 lg:col-span-1 bg-amber-50/40">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Siap Ditugaskan</p>
                        <p class="text-2xl font-extrabold text-amber-700 mt-1">{{ $totalSiapDisposisi }}</p>
                        <p class="text-[11px] text-amber-600 mt-0.5">Laporan belum ada SPT</p>
                    </div>
                    @if($totalSiapDisposisi > 0)
                        <a href="{{ route('admin.disposisi.create') }}" class="p-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition" title="Tugaskan sekarang">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Filter Panel -->
        <x-card class="p-4">
            <form method="GET" action="{{ route('admin.disposisi.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wide">Cari Disposisi</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="No. Surat Tugas / No. Tiket / Judul / Nama Petugas..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wide">Status Penugasan</label>
                    <select name="status_penugasan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent">
                        <option value="">Semua Status Penugasan</option>
                        <option value="ditugaskan" {{ $statusPenugasan === 'ditugaskan' ? 'selected' : '' }}>Ditugaskan</option>
                        <option value="sedang_dikerjakan" {{ $statusPenugasan === 'sedang_dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                        <option value="selesai" {{ $statusPenugasan === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wide">Petugas Lapangan</label>
                    <select name="petugas_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent">
                        <option value="">Semua Petugas</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas->id }}" {{ (string)$petugasId === (string)$petugas->id ? 'selected' : '' }}>
                                {{ $petugas->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-2">
                    <button type="submit" dusk="btn-filter-disposisi" class="px-4 py-2 bg-[#1F3A5F] text-white text-xs font-bold rounded-lg hover:bg-[#162a45] transition-colors">
                        Terapkan Filter
                    </button>
                    @if($search || $statusPenugasan || $petugasId)
                        <a href="{{ route('admin.disposisi.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-200 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Tabel Disposisi -->
        @if($disposisiList->isEmpty())
            <x-empty-state
                title="Tidak Ada Disposisi Penugasan"
                description="Belum ada data disposisi yang cocok dengan kriteria pencarian Anda."
            >
                @if($totalSiapDisposisi > 0)
                    <div class="mt-4">
                        <a
                            href="{{ route('admin.disposisi.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#0E7C86] text-white text-xs font-bold rounded-lg hover:bg-[#0b636b] transition"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tugaskan Laporan Sekarang ({{ $totalSiapDisposisi }} laporan)
                        </a>
                    </div>
                @endif
            </x-empty-state>
        @else
            <x-card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Surat Tugas</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Laporan Terkait</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Petugas Lapangan</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tenggat Waktu</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($disposisiList as $item)
                                <tr class="hover:bg-slate-50/60 transition-colors" dusk="disposisi-row-{{ $item->id }}">
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs font-extrabold text-[#1F3A5F] block">{{ $item->nomor_surat_tugas }}</span>
                                        <span class="text-[11px] text-slate-400">Terbit: {{ $item->tanggal_disposisi->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs">
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <span class="font-mono text-xs font-semibold text-[#0E7C86]">{{ $item->laporan->nomor_tiket }}</span>
                                            <span class="text-[10px] font-medium px-2 py-0.2 rounded-full bg-slate-100 text-slate-600">
                                                {{ $item->laporan->kategori->nama_kategori ?? 'Umum' }}
                                            </span>
                                        </div>
                                        <p class="font-medium text-slate-800 truncate">{{ $item->laporan->judul }}</p>
                                        <p class="text-xs text-slate-400 truncate">
                                            Kel. {{ $item->laporan->kelurahan->nama_kelurahan ?? '-' }}, Kec. {{ $item->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-8 w-8 rounded-full bg-[#1F3A5F]/10 text-[#1F3A5F] font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                {{ strtoupper(substr($item->petugas->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800">{{ $item->petugas->name }}</p>
                                                <p class="text-xs text-slate-400">{{ $item->petugas->phone ?: $item->petugas->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <p class="font-semibold text-xs text-slate-700">{{ $item->tenggat_waktu->format('d M Y') }}</p>
                                        @if($item->isSelesai())
                                            <span class="inline-block text-[10px] text-emerald-600 font-medium">Telah Selesai</span>
                                        @elseif($item->isOverdue())
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Lewat {{ abs($item->sisaHari()) }} hr
                                            </span>
                                        @elseif($item->sisaHari() === 0)
                                            <span class="inline-block text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                                Hari ini!
                                            </span>
                                        @else
                                            <span class="inline-block text-[10px] text-slate-500">
                                                Sisa {{ $item->sisaHari() }} hari
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <x-badge-status :status="$item->status_penugasan" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a
                                                href="{{ route('admin.disposisi.show', $item->id) }}"
                                                dusk="btn-detail-disposisi-{{ $item->id }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-white bg-[#1F3A5F] hover:bg-[#162a45] rounded-lg transition"
                                                title="Lihat Detail SPT"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Detail</span>
                                            </a>
                                            <a
                                                href="{{ route('admin.disposisi.pdf', $item->id) }}"
                                                dusk="btn-pdf-disposisi-{{ $item->id }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-[#0E7C86] bg-[#0E7C86]/10 hover:bg-[#0E7C86]/20 rounded-lg transition"
                                                title="Unduh SPT Format PDF"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>PDF</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($disposisiList->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">
                        {{ $disposisiList->withQueryString()->links() }}
                    </div>
                @endif
            </x-card>
        @endif
    </div>
</x-admin-layout>
