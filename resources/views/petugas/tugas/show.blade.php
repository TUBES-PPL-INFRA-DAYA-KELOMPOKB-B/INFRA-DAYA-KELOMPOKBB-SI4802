<x-admin-layout title="Detail Tugas Lapangan">
    <div class="space-y-6">
        <!-- Header & Navigasi Cepat -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('petugas.tugas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#0E7C86] transition mb-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar Tugas</span>
                </a>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#1F3A5F] tracking-tight">
                        Lembar Kerja Lapangan
                    </h1>
                    <span class="font-mono text-xs sm:text-sm font-bold px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-700">
                        {{ $disposisi->nomor_surat_tugas }}
                    </span>
                    <x-badge-status :status="$disposisi->status_penugasan" />
                    <span class="text-[11px] font-extrabold px-2 py-0.5 rounded-full {{ $disposisi->laporan->prioritas === 'tinggi' ? 'bg-rose-100 text-rose-700' : ($disposisi->laporan->prioritas === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                        Prioritas {{ ucfirst($disposisi->laporan->prioritas) }}
                    </span>
                </div>
            </div>

            <!-- Tombol Navigasi GPS Eksternal (Google Maps & Waze) -->
            <div class="flex items-center gap-2">
                <a
                    href="https://www.google.com/maps/dir/?api=1&destination={{ $disposisi->laporan->latitude }},{{ $disposisi->laporan->longitude }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    dusk="btn-navigasi-gmaps"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1F3A5F] hover:bg-[#162a45] text-white text-xs font-bold rounded-xl shadow-sm transition"
                    title="Buka rute navigasi di Google Maps"
                >
                    <svg class="h-4 w-4 text-brand-amber" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    <span>Google Maps</span>
                </a>
                <a
                    href="https://waze.com/ul?ll={{ $disposisi->laporan->latitude }},{{ $disposisi->laporan->longitude }}&navigate=yes"
                    target="_blank"
                    rel="noopener noreferrer"
                    dusk="btn-navigasi-waze"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm transition"
                    title="Buka navigasi di Waze"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Waze</span>
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <x-flash />

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
                <p class="font-bold mb-1">Periksa kembali data yang Anda kirimkan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri (2 Kolom): Peta & Detail Kerusakan Lapangan -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Peta Geospasial Interaktif -->
                <x-card class="overflow-hidden p-0">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Lokasi Kerusakan Lapangan</span>
                        </div>
                        <span class="font-mono text-[11px] text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">
                            {{ $disposisi->laporan->latitude }}, {{ $disposisi->laporan->longitude }}
                        </span>
                    </div>
                    <div id="petugasMap" class="w-full h-72 sm:h-80 z-0 bg-slate-100"></div>
                </x-card>

                <!-- Informasi Laporan & Foto Kerusakan Sebelum -->
                <x-card class="p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-base font-bold text-[#1F3A5F] flex items-center gap-2">
                            <svg class="h-5 w-5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Deskripsi Kerusakan Fisik</span>
                        </h2>
                        <span class="font-mono text-xs font-bold text-slate-500">
                            Tiket: {{ $disposisi->laporan->nomor_tiket }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 mb-1">
                            {{ $disposisi->laporan->judul }}
                        </h3>
                        <p class="text-xs text-slate-500 flex items-center gap-1.5">
                            <span>Kelurahan {{ $disposisi->laporan->kelurahan->nama_kelurahan ?? '-' }}, Kecamatan {{ $disposisi->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}</span>
                            <span>&bull;</span>
                            <span>Kategori: <strong>{{ $disposisi->laporan->kategori->nama_kategori ?? '-' }}</strong></span>
                        </p>
                    </div>

                    <!-- Foto Bukti Sebelum (Before) -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                            Foto Bukti Kerusakan Awal (Sebelum Perbaikan)
                        </p>
                        @if($disposisi->laporan->foto_bukti_sebelum)
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 max-h-96 relative group cursor-zoom-in">
                                <a href="{{ asset('storage/' . $disposisi->laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="block w-full">
                                    <img
                                        src="{{ asset('storage/' . $disposisi->laporan->foto_bukti_sebelum) }}"
                                        alt="Foto Sebelum Perbaikan"
                                        class="w-full h-auto object-cover max-h-96 hover:scale-101 transition-transform duration-200"
                                    >
                                </a>
                            </div>
                        @else
                            <div class="p-6 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-400 italic">
                                Tidak ada foto bukti kerusakan terunggah.
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan Kondisi dari Warga</p>
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            {{ $disposisi->laporan->deskripsi_kondisi }}
                        </p>
                    </div>
                </x-card>
            </div>

            <!-- Kolom Kanan (1 Kolom): Instruksi Admin & Panel Aksi Eksekusi -->
            <div class="space-y-6">
                <!-- Kartu Instruksi Kerja Admin -->
                <x-card class="p-5 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#1F3A5F] border-b border-slate-100 pb-2 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Instruksi Resmi Pengerjaan</span>
                    </h2>

                    <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800 mb-1">Catatan Verifikator DSDABM</p>
                        <p class="text-xs sm:text-sm text-amber-950 font-medium leading-relaxed italic">
                            "{{ $disposisi->catatan_instruksi }}"
                        </p>
                    </div>

                    <div class="space-y-2 text-xs border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Verifikator / Admin:</span>
                            <span class="font-bold text-slate-700">{{ $disposisi->admin->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Tanggal Disposisi:</span>
                            <span class="font-semibold text-slate-700">{{ $disposisi->tanggal_disposisi->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Tenggat Waktu (SLA):</span>
                            <span class="font-bold {{ $disposisi->isOverdue() ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $disposisi->tenggat_waktu->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </div>
                </x-card>

                <!-- Panel Aksi Pengerjaan (Sesuai Status) -->
                @if ($disposisi->status_penugasan === 'ditugaskan')
                    <!-- STATUS 1: BARU DITUGASKAN -> TOMBOL MULAI -->
                    <x-card class="p-5 border-l-4 border-l-sky-500 space-y-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-sky-500 animate-ping"></span>
                                <span>Mulai Pelaksanaan Lapangan</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Konfirmasikan ketika Anda tiba di lokasi dan mulai melaksanakan perbaikan fisik kerusakan.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('petugas.tugas.mulai', $disposisi->id) }}">
                            @csrf
                            <button
                                type="submit"
                                dusk="btn-mulai-pengerjaan"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-sm shadow-md transition"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Mulai Pengerjaan Sekarang</span>
                            </button>
                        </form>
                    </x-card>

                @elseif ($disposisi->status_penugasan === 'sedang_dikerjakan')
                    <!-- STATUS 2: SEDANG DIKERJAKAN -> FORM UNGGAH HASIL & SELESAI -->
                    <x-card class="p-5 border-l-4 border-l-amber-500 space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="text-xs font-bold tracking-wider text-amber-800">Pengerjaan Aktif</span>
                            </div>
                            <h2 class="text-base font-extrabold text-slate-900">
                                Lapor Penyelesaian Tugas
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Unggah foto bukti fisik hasil perbaikan (sesudah) dan tulis ringkasan teknis penanganan.
                            </p>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('petugas.tugas.selesai', $disposisi->id) }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            @csrf

                            <!-- Upload Foto Bukti Sesudah -->
                            <div>
                                <label for="foto_bukti_sesudah" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Foto Bukti Perbaikan (Sesudah) <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    name="foto_bukti_sesudah"
                                    id="foto_bukti_sesudah"
                                    dusk="input-foto-sesudah"
                                    accept="image/jpeg,image/png,image/jpg"
                                    required
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0E7C86] file:text-white hover:file:bg-[#0b636b] file:cursor-pointer border border-slate-200 rounded-xl bg-slate-50 p-2"
                                >
                                <p class="text-[11px] text-slate-400 mt-1">Format gambar: JPG, JPEG, PNG (Maks 5 MB).</p>
                                @error('foto_bukti_sesudah')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Catatan Teknis Perbaikan -->
                            <div>
                                <label for="catatan_perbaikan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Catatan Hasil Perbaikan <span class="text-rose-500">*</span>
                                </label>
                                <textarea
                                    name="catatan_perbaikan"
                                    id="catatan_perbaikan"
                                    dusk="input-catatan-perbaikan"
                                    rows="3"
                                    required
                                    placeholder="Contoh: Telah dilakukan pembersihan lumpur saluran drainase sepanjang 15 meter dan penambalan dinding semen yang jebol. Aliran air kembali lancar."
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                >{{ old('catatan_perbaikan') }}</textarea>
                                <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter.</p>
                                @error('catatan_perbaikan')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tombol Submit Selesai -->
                            <button
                                type="submit"
                                dusk="btn-submit-selesai"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-md transition"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Tuntaskan & Laporkan Selesai</span>
                            </button>
                        </form>
                    </x-card>

                @elseif ($disposisi->status_penugasan === 'selesai')
                    <!-- STATUS 3: SELESAI -> RINGKASAN BEFORE / AFTER -->
                    <x-card class="p-5 border-l-4 border-l-emerald-500 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-extrabold text-emerald-900">Perbaikan Tuntas</h2>
                                <p class="text-xs text-emerald-700">
                                    Diselesaikan: {{ $disposisi->tanggal_selesai ? $disposisi->tanggal_selesai->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Foto Sesudah -->
                        @if ($disposisi->foto_bukti_sesudah)
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Foto Bukti Hasil Perbaikan (Sesudah)
                                </p>
                                <div class="rounded-xl overflow-hidden border border-emerald-200 bg-slate-50 max-h-48">
                                    <img
                                        src="{{ asset('storage/' . $disposisi->foto_bukti_sesudah) }}"
                                        alt="Foto Bukti Sesudah"
                                        class="w-full h-auto object-cover max-h-48"
                                    >
                                </div>
                            </div>
                        @endif

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Catatan Perbaikan Anda</p>
                            <p class="text-xs text-slate-700 italic">
                                "{{ $disposisi->catatan_perbaikan ?? $disposisi->laporan->catatan_perbaikan }}"
                            </p>
                        </div>
                    </x-card>
                @endif

                <!-- Timeline Riwayat Status Laporan -->
                <x-card class="p-5 space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#1F3A5F] border-b border-slate-100 pb-2">
                        Riwayat Perjalanan Laporan
                    </h2>
                    <div class="space-y-3">
                        @foreach ($disposisi->laporan->riwayatStatus as $riwayat)
                            <div class="flex items-start gap-2.5 text-xs">
                                <div class="mt-1 h-2 w-2 rounded-full {{ $riwayat->status === 'selesai' ? 'bg-emerald-500' : ($riwayat->status === 'diproses' ? 'bg-amber-500' : 'bg-slate-400') }} flex-shrink-0"></div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $riwayat->status) }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $riwayat->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-slate-600 text-[11px] mt-0.5">{{ $riwayat->keterangan ?? 'Status diperbarui.' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    <!-- Leaflet JS Initialization -->
    @push('styles')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ (float) $disposisi->laporan->latitude }};
            const lng = {{ (float) $disposisi->laporan->longitude }};
            const mapContainer = document.getElementById('petugasMap');

            if (mapContainer && typeof L !== 'undefined') {
                const map = L.map('petugasMap').setView([lat, lng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`
                    <div class="text-xs p-1">
                        <strong class="text-[#1F3A5F] block mb-1">${@json($disposisi->laporan->judul)}</strong>
                        <span class="text-slate-500 block mb-1.5">Kel. ${@json($disposisi->laporan->kelurahan->nama_kelurahan ?? '-')}, Kec. ${@json($disposisi->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-')}</span>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="inline-block text-[#0E7C86] font-bold underline">Buka Navigasi Rute</a>
                    </div>
                `).openPopup();
            }
        });
    </script>
</x-admin-layout>
