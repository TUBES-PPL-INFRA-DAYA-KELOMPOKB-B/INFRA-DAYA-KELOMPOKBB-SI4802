<x-admin-layout title="Detail & Verifikasi Laporan">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Back Button & Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.verifikasi.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 font-medium transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar Aduan
                </a>
                <span class="text-slate-300">|</span>
                <span class="font-mono text-sm font-bold text-[#1F3A5F]">{{ $laporan->nomor_tiket }}</span>
            </div>
            <x-badge-status :status="$laporan->status" />
        </div>

        <!-- Flash Message & Error Notification -->
        <x-flash />

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Terjadi Kesalahan:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kolom Kiri: Informasi Detail Laporan & Bukti (PBI-14) -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Kartu Informasi Detail Laporan -->
                <x-card class="p-6 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <h2 class="text-lg font-bold text-[#1F3A5F]">{{ $laporan->judul }}</h2>
                        <span class="text-xs font-mono font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ $laporan->nomor_tiket }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Identitas Pelapor</p>
                            <p class="font-semibold text-slate-800">{{ $laporan->warga->name ?? '-' }}</p>
                            <p class="text-slate-500 text-xs">{{ $laporan->warga->email ?? '' }}</p>
                            <p class="text-slate-500 text-xs">{{ $laporan->warga->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Tanggal Laporan Dibuat</p>
                            <p class="font-semibold text-slate-800">{{ $laporan->created_at->format('d F Y') }}</p>
                            <p class="text-slate-500 text-xs">Pukul {{ $laporan->created_at->format('H:i') }} WIB</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Kategori Infrastruktur</p>
                            <p class="font-semibold text-slate-800">{{ $laporan->kategori->nama_kategori ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Wilayah Lokasi</p>
                            <p class="font-semibold text-slate-800">Kel. {{ $laporan->kelurahan->nama_kelurahan ?? '-' }}</p>
                            <p class="text-xs text-slate-500">Kec. {{ $laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1.5">Deskripsi Kondisi Kerusakan</p>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $laporan->deskripsi_kondisi }}</div>
                    </div>

                    <!-- Koordinat Titik Kerusakan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Latitude</p>
                            <p class="font-mono text-sm text-slate-700">{{ number_format($laporan->latitude, 8) }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Longitude</p>
                            <p class="font-mono text-sm text-slate-700">{{ number_format($laporan->longitude, 8) }}</p>
                        </div>
                    </div>

                    @if($laporan->alasan_penolakan)
                        <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-rose-700 uppercase tracking-wide mb-1">Catatan Alasan Penolakan</p>
                            <p class="text-sm text-rose-800">{{ $laporan->alasan_penolakan }}</p>
                        </div>
                    @endif
                </x-card>

                <!-- Foto Bukti Kerusakan Awal -->
                @if($laporan->foto_bukti_sebelum)
                    <x-card class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-slate-700">Foto Bukti Fisik Kerusakan</h3>
                            <a href="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Buka Resolusi Penuh</span>
                            </a>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 relative group cursor-zoom-in">
                            <a href="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img
                                    src="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}"
                                    alt="Foto bukti kerusakan laporan {{ $laporan->nomor_tiket }}"
                                    class="w-full object-cover max-h-96 transition-transform duration-300 group-hover:scale-102"
                                    dusk="foto-bukti"
                                >
                            </a>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">Foto bukti kondisi fisik diunggah oleh warga pelapor.</p>
                    </x-card>
                @endif

                <!-- Peta Titik Kerusakan -->
                <x-card class="p-5">
                    <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <svg class="h-4 w-4 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Titik Lokasi Kerusakan
                    </h3>
                    <div id="detailMap" class="w-full h-64 rounded-xl border border-slate-200 overflow-hidden bg-slate-100"></div>
                    <a
                        href="https://www.google.com/maps?q={{ $laporan->latitude }},{{ $laporan->longitude }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-2.5 inline-flex items-center gap-1.5 text-xs text-[#0E7C86] hover:underline font-semibold"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka di Google Maps
                    </a>
                </x-card>

                <!-- Riwayat Perubahan Status (Audit Trail) -->
                <x-card class="p-5">
                    <h3 class="text-sm font-bold text-slate-700 mb-4">Riwayat Perubahan Status Laporan</h3>
                    <div class="relative space-y-0">
                        @foreach($laporan->riwayatStatus as $riwayat)
                            <div class="flex gap-3 pb-4 relative" dusk="riwayat-{{ $loop->iteration }}">
                                <div class="flex flex-col items-center">
                                    <div class="h-8 w-8 rounded-full bg-[#0E7C86]/10 border-2 border-[#0E7C86]/30 flex items-center justify-center flex-shrink-0">
                                        <svg class="h-3.5 w-3.5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="w-px flex-1 bg-slate-200 mt-1"></div>
                                    @endif
                                </div>
                                <div class="pt-1 pb-2 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <x-badge-status :status="$riwayat->status" />
                                        <span class="text-xs text-slate-400">{{ $riwayat->created_at->format('d M Y, H:i') }} WIB</span>
                                    </div>
                                    <p class="text-sm text-slate-700">{{ $riwayat->keterangan }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">oleh: {{ $riwayat->user->name ?? 'Sistem' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <!-- Kolom Kanan: Aksi Terima & Tolak Aduan (PBI-14) -->
            <div class="space-y-5">
                <!-- Info Status Saat Ini -->
                <x-card class="p-5">
                    <h3 class="text-sm font-bold text-slate-700 mb-3">Status Laporan</h3>
                    <div class="space-y-2.5 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Status Saat Ini</span>
                            <x-badge-status :status="$laporan->status" />
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Prioritas</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                {{ ucfirst($laporan->prioritas ?? 'Belum ditentukan') }}
                            </span>
                        </div>
                    </div>
                </x-card>

                @if($laporan->status === 'menunggu_verifikasi')
                    <!-- Form Terima Aduan (PBI-14) -->
                    <x-card class="p-5 border-l-4 border-l-emerald-500">
                        <h3 class="text-sm font-bold text-emerald-800 mb-2 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Terima Aduan
                        </h3>
                        <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                            Laporan dinyatakan valid setelah pemeriksaan foto bukti, kesesuaian kategori, dan titik lokasi. Laporan akan diubah ke status <strong>Diproses</strong>.
                        </p>
                        <form method="POST" action="{{ route('admin.verifikasi.setujui', $laporan->id) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="catatan_verifikator" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pemeriksaan <span class="text-slate-400">(opsional)</span></label>
                                <textarea
                                    name="catatan_verifikator"
                                    id="catatan_verifikator"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                    placeholder="Catatan validasi atau instruksi tambahan..."
                                >{{ old('catatan_verifikator') }}</textarea>
                            </div>
                            <button
                                type="submit"
                                dusk="btn-setujui"
                                onclick="return confirm('Apakah Anda yakin menerima dan menyetujui laporan {{ $laporan->nomor_tiket }}?')"
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-sm"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Terima Aduan
                            </button>
                        </form>
                    </x-card>

                    <!-- Form Tolak Aduan (PBI-14) -->
                    <x-card class="p-5 border-l-4 border-l-rose-500">
                        <h3 class="text-sm font-bold text-rose-800 mb-2 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Tolak Aduan
                        </h3>
                        <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                            Tolak laporan apabila terindikasi palsu, spam, foto tidak relevan, atau di luar wilayah kewenangan. Alasan wajib diisi (minimal 10 karakter).
                        </p>
                        <form method="POST" action="{{ route('admin.verifikasi.tolak', $laporan->id) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="alasan_penolakan" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Alasan Penolakan <span class="text-rose-500">*</span>
                                </label>
                                <textarea
                                    name="alasan_penolakan"
                                    id="alasan_penolakan"
                                    rows="3"
                                    required
                                    dusk="textarea-alasan"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('alasan_penolakan') border-rose-300 @else border-slate-300 @enderror"
                                    placeholder="Jelaskan alasan penolakan secara jelas (min. 10 karakter)..."
                                >{{ old('alasan_penolakan') }}</textarea>
                                @error('alasan_penolakan')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                type="submit"
                                dusk="btn-tolak"
                                onclick="return confirm('Tolak laporan {{ $laporan->nomor_tiket }}? Tindakan penolakan tidak dapat dibatalkan.')"
                                class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-sm"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak Aduan
                            </button>
                        </form>
                    </x-card>
                @else
                    <!-- Notifikasi Laporan Sudah Diverifikasi (AC02-16) -->
                    <x-card class="p-5 bg-slate-50 border border-slate-200">
                        <div class="flex items-center gap-2.5 text-slate-700 font-bold text-sm mb-1">
                            <svg class="h-4 w-4 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Laporan Telah Diverifikasi</span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Laporan ini telah diproses dengan status <strong>{{ ucfirst($laporan->status) }}</strong> sehingga tidak dapat dilakukan verifikasi ulang melalui aksi Terima/Tolak.
                        </p>
                    </x-card>
                @endif
            </div>
        </div>
    </div>

    <!-- Leaflet Script -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ $laporan->latitude ?? -6.9175 }};
            const lng = {{ $laporan->longitude ?? 107.6191 }};
            const map = L.map('detailMap').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup('<b>{{ $laporan->nomor_tiket }}</b><br>{{ $laporan->judul }}')
                .openPopup();
        });
    </script>
</x-admin-layout>
