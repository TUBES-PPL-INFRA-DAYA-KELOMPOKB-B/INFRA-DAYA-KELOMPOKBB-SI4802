<x-warga-layout title="Laporan Kerusakan Saya">
    <div class="space-y-6">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Riwayat Laporan Saya</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau perkembangan tindak lanjut laporan kerusakan infrastruktur yang Anda ajukan.</p>
            </div>
            <div>
                <a href="{{ route('warga.laporan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#F2A93B] hover:bg-[#d9932b] text-slate-900 font-bold text-sm transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Laporan Baru
                </a>
            </div>
        </div>

        <!-- Filter Status Tab -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-sm font-medium">
            <a href="{{ route('warga.laporan.saya') }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ !$status ? 'bg-[#1F3A5F] text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Semua Laporan
            </a>
            <a href="{{ route('warga.laporan.saya', ['status' => 'menunggu_verifikasi']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'menunggu_verifikasi' ? 'bg-[#1F3A5F] text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Menunggu Verifikasi
            </a>
            <a href="{{ route('warga.laporan.saya', ['status' => 'diproses']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'diproses' ? 'bg-[#1F3A5F] text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Sedang Dikerjakan
            </a>
            <a href="{{ route('warga.laporan.saya', ['status' => 'selesai']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'selesai' ? 'bg-[#1F3A5F] text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Tuntas / Selesai
            </a>
            <a href="{{ route('warga.laporan.saya', ['status' => 'ditolak']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'ditolak' ? 'bg-[#1F3A5F] text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Ditolak
            </a>
            <a href="{{ route('warga.laporan.saya', ['status' => 'dibatalkan']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'dibatalkan' ? 'bg-[#1F3A5F] text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Dibatalkan
            </a>
        </div>

        <!-- List Laporan -->
        @if($laporanList->isEmpty())
            <x-card class="p-12 text-center">
                <div class="h-16 w-16 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Laporan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Anda belum memiliki riwayat pengajuan laporan kerusakan infrastruktur pada kategori atau status ini.</p>
                <div class="mt-5">
                    <a href="{{ route('warga.laporan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white font-medium text-sm transition-all shadow-sm">
                        Ajukan Laporan Pertama
                    </a>
                </div>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($laporanList as $laporan)
                    <x-card class="p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <span class="font-mono text-xs font-bold text-[#0E7C86] bg-teal-50 px-2.5 py-1 rounded-md border border-teal-100">
                                    {{ $laporan->nomor_tiket }}
                                </span>
                                @if($laporan->status === 'menunggu_verifikasi')
                                    <span dusk="status-badge-{{ $laporan->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        Menunggu Verifikasi
                                    </span>
                                @elseif($laporan->status === 'diproses')
                                    <span dusk="status-badge-{{ $laporan->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                        Sedang Diproses
                                    </span>
                                @elseif($laporan->status === 'selesai')
                                    <span dusk="status-badge-{{ $laporan->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        Selesai
                                    </span>
                                @elseif($laporan->status === 'dibatalkan')
                                    <span dusk="status-badge-{{ $laporan->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span dusk="status-badge-{{ $laporan->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">
                                        Ditolak
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-slate-900 line-clamp-2">
                                <a href="{{ route('warga.laporan.show', $laporan->id) }}" class="hover:text-[#0E7C86] transition-colors">
                                    {{ $laporan->judul }}
                                </a>
                            </h3>

                            <div class="text-xs text-slate-500 space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>{{ $laporan->kategori->nama_kategori }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <span>Kel. {{ $laporan->kelurahan->nama_kelurahan }}, Kec. {{ $laporan->kelurahan->kecamatan->nama_kecamatan }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">
                                {{ $laporan->created_at->translatedFormat('d M Y, H:i') }}
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('warga.laporan.show', $laporan->id) }}" class="text-xs font-semibold text-[#0E7C86] hover:underline">
                                    Lihat Detail &rarr;
                                </a>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>

            @if($laporanList->hasPages())
                <div class="pt-4">
                    {{ $laporanList->links() }}
                </div>
            @endif
        @endif
    </div>
</x-warga-layout>
