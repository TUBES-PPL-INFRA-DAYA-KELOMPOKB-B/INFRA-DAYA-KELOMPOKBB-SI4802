<x-admin-layout title="Detail Surat Perintah Tugas (SPT)">
    <div class="space-y-6">
        <!-- Header & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('admin.disposisi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#0E7C86] transition mb-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar Disposisi</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Surat Perintah Tugas</h1>
                    <span class="font-mono text-sm font-bold px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-700">
                        {{ $disposisi->nomor_surat_tugas }}
                    </span>
                    <x-badge-status :status="$disposisi->status_penugasan" />
                </div>
            </div>

            <!-- Action Toolbar -->
            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('admin.disposisi.pdf', $disposisi->id) }}"
                    dusk="btn-unduh-pdf"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-xs font-bold rounded-xl shadow-sm transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Cetak SPT (PDF)</span>
                </a>
                <a
                    href="{{ route('admin.verifikasi.show', $disposisi->laporan->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-xl transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Detail Laporan</span>
                </a>
            </div>
        </div>

        <!-- Flash -->
        <x-flash />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Dokumen SPT & Penugasan (2 Kolom) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Kartu Identitas SPT & Penugasan -->
                <x-card class="p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-base font-bold text-[#1F3A5F] flex items-center gap-2">
                            <svg class="h-5 w-5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Lembar Penugasan Lapangan</span>
                        </h2>
                        <span class="text-xs text-slate-400">
                            Diterbitkan: <strong>{{ $disposisi->tanggal_disposisi->translatedFormat('d F Y') }}</strong>
                        </span>
                    </div>

                    <!-- Profil Petugas yang Ditugaskan -->
                    <div class="bg-gradient-to-r from-slate-50 to-teal-50/20 border border-slate-200 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Petugas Pelaksana yang Ditugaskan</p>
                        <div class="flex items-center gap-3.5">
                            <div class="h-12 w-12 rounded-xl bg-[#1F3A5F] text-white font-bold text-base flex items-center justify-center shadow-sm">
                                {{ strtoupper(substr($disposisi->petugas->name, 0, 2)) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-800 text-base">{{ $disposisi->petugas->name }}</h3>
                                <p class="text-xs text-slate-500">Jabatan: Petugas Lapangan DSDABM Kota Bandung</p>
                                <div class="flex items-center gap-4 text-xs text-slate-500 mt-1">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $disposisi->petugas->email }}
                                    </span>
                                    @if($disposisi->petugas->phone)
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            {{ $disposisi->petugas->phone }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal & Batas Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tanggal Mulai Disposisi</span>
                            <p class="font-bold text-slate-800 text-sm">{{ $disposisi->tanggal_disposisi->translatedFormat('l, d F Y') }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Ditetapkan oleh: {{ $disposisi->admin->name }}</p>
                        </div>

                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tenggat Waktu Selesai (SLA)</span>
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-slate-800 text-sm">{{ $disposisi->tenggat_waktu->translatedFormat('l, d F Y') }}</p>
                                @if($disposisi->isSelesai())
                                    <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        Rampung
                                    </span>
                                @elseif($disposisi->isOverdue())
                                    <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                        Terlambat {{ abs($disposisi->sisaHari()) }} hari
                                    </span>
                                @elseif($disposisi->sisaHari() === 0)
                                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                        Hari ini!
                                    </span>
                                @else
                                    <span class="text-[11px] font-bold text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-full">
                                        Sisa {{ $disposisi->sisaHari() }} hari
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Instruksi Teknis -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Instruksi Teknis & Arahan Pelaksanaan</span>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 leading-relaxed whitespace-pre-wrap font-sans">
                            {{ $disposisi->catatan_instruksi }}
                        </div>
                    </div>
                </x-card>

                <!-- Riwayat Perjalanan Status Laporan -->
                <x-card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-[#1F3A5F] border-b border-slate-100 pb-3 flex items-center gap-2">
                        <svg class="h-5 w-5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Audit Log & Timeline Status</span>
                    </h2>

                    <div class="relative pl-6 space-y-4 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        @foreach($disposisi->laporan->riwayatStatus as $riwayat)
                            <div class="relative">
                                <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-[#0E7C86] shadow-sm"></span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <x-badge-status :status="$riwayat->status" />
                                        <span class="text-xs font-semibold text-slate-700">{{ $riwayat->user->name ?? 'Sistem' }}</span>
                                        <span class="text-xs text-slate-400">• {{ $riwayat->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1">{{ $riwayat->keterangan }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <!-- Kolom Kanan: Rincian Kerusakan & Peta Lokasi (1 Kolom) -->
            <div class="space-y-6">
                <!-- Rincian Laporan -->
                <x-card class="p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#1F3A5F]">Informasi Laporan</h3>
                        <span class="font-mono text-xs font-extrabold text-[#0E7C86]">{{ $disposisi->laporan->nomor_tiket }}</span>
                    </div>

                    <div>
                        <h4 class="font-bold text-sm text-slate-800">{{ $disposisi->laporan->judul }}</h4>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                {{ $disposisi->laporan->kategori->nama_kategori ?? 'Umum' }}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $disposisi->laporan->prioritas === 'tinggi' ? 'bg-rose-100 text-rose-700' : ($disposisi->laporan->prioritas === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                Prioritas {{ ucfirst($disposisi->laporan->prioritas) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs border-t border-slate-100 pt-3">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Pelapor Warga</span>
                            <span class="font-medium text-slate-700">{{ $disposisi->laporan->warga->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Wilayah</span>
                            <span class="font-medium text-slate-700">
                                Kel. {{ $disposisi->laporan->kelurahan->nama_kelurahan ?? '-' }}, Kec. {{ $disposisi->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Titik Koordinat GPS</span>
                            <span class="font-mono text-slate-600">{{ $disposisi->laporan->latitude }}, {{ $disposisi->laporan->longitude }}</span>
                        </div>
                    </div>

                    @if($disposisi->laporan->foto_bukti_sebelum)
                        <div class="border-t border-slate-100 pt-3">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold mb-2">Foto Bukti Kerusakan Awal</span>
                            <div class="rounded-xl overflow-hidden border border-slate-200">
                                <img
                                    src="{{ asset('storage/' . $disposisi->laporan->foto_bukti_sebelum) }}"
                                    alt="Foto Bukti Sebelum"
                                    class="w-full h-44 object-cover"
                                >
                            </div>
                        </div>
                    @endif
                </x-card>

                <!-- Peta Lokasi Leaflet -->
                <x-card class="p-5 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#1F3A5F]">Peta Lokasi Penanganan</h3>
                    <div id="disposisiMap" class="w-full h-56 rounded-xl border border-slate-200 overflow-hidden"></div>
                    <p class="text-[11px] text-slate-400 text-center">
                        Petugas lapangan dapat memanfaatkan koordinat ini untuk navigasi GPS.
                    </p>
                </x-card>
            </div>
        </div>
    </div>

    <!-- Leaflet JS & Map Initialization -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ (float) $disposisi->laporan->latitude }};
            const lng = {{ (float) $disposisi->laporan->longitude }};

            const map = L.map('disposisiMap').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors | INFRA-DAYA'
            }).addTo(map);

            const redIcon = L.divIcon({
                className: '',
                html: '<div style="width:16px;height:16px;background:#E53E3E;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(229,62,62,0.6)"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8],
            });

            const popupContent = document.createElement('div');
            const strong = document.createElement('strong');
            strong.textContent = @js($disposisi->laporan->judul);
            popupContent.appendChild(strong);
            popupContent.appendChild(document.createElement('br'));
            const small = document.createElement('small');
            small.textContent = @js(($disposisi->laporan->kelurahan->nama_kelurahan ?? '') . ', ' . ($disposisi->laporan->kelurahan->kecamatan->nama_kecamatan ?? ''));
            popupContent.appendChild(small);

            L.marker([lat, lng], { icon: redIcon })
                .addTo(map)
                .bindPopup(popupContent)
                .openPopup();
        });
    </script>
</x-admin-layout>
