<x-warga-layout title="Form Pengajuan Laporan Kerusakan">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="border-b border-slate-200 pb-4">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#0E7C86] mb-1">
                <span>Partisipasi Warga</span>
                <span>•</span>
                <span>SDG 9: Infrastruktur Berkelanjutan</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1F3A5F] tracking-tight">Lapor Kerusakan Infrastruktur</h1>
            <p class="text-sm text-slate-500 mt-1">Sampaikan laporan kerusakan jalan, saluran air tersumbat, atau lampu jalan mati langsung ke Dinas SDABM Kota Bandung.</p>
        </div>

        <!-- Form Card -->
        <x-card class="p-6 sm:p-8">
            <form method="POST" action="{{ route('warga.laporan.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Informasi Utama -->
                <div class="space-y-4">
                    <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2">1. Rincian Masalah & Kategori</h2>

                    <div>
                        <label for="judul" class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Laporan Singkat <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="judul"
                            id="judul"
                            value="{{ old('judul') }}"
                            required
                            class="w-full px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('judul') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                            placeholder="Contoh: Lubang jalan berdiameter 50cm di depan SMP 1"
                        >
                        @error('judul')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kategori_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori Infrastruktur <span class="text-rose-500">*</span></label>
                            <select
                                name="kategori_id"
                                id="kategori_id"
                                required
                                class="w-full px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('kategori_id') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                            >
                                <option value="">Pilih Kategori Infrastruktur</option>
                                @foreach($kategoriList as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }} (Prioritas {{ $kategori->prioritas_label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="foto_bukti_sebelum" class="block text-sm font-semibold text-slate-700 mb-1.5">Foto Bukti Kerusakan <span class="text-rose-500">*</span></label>
                            <input
                                type="file"
                                name="foto_bukti_sebelum"
                                id="foto_bukti_sebelum"
                                required
                                accept="image/png,image/jpeg,image/jpg"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-[#1F3A5F] hover:file:bg-slate-200 cursor-pointer"
                            >
                            <p class="text-xs text-slate-400 mt-1">Maksimal 5MB (JPG, JPEG, PNG).</p>
                            @error('foto_bukti_sebelum')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="deskripsi_kondisi" class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Detail Kondisi Lapangan <span class="text-rose-500">*</span></label>
                        <textarea
                            name="deskripsi_kondisi"
                            id="deskripsi_kondisi"
                            rows="3"
                            required
                            class="w-full px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('deskripsi_kondisi') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                            placeholder="Jelaskan perkiraan kedalaman lubang, apakah menggenang saat hujan, atau patokan lokasi terdekat..."
                        >{{ old('deskripsi_kondisi') }}</textarea>
                        @error('deskripsi_kondisi')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Lokasi Wilayah & Peta Geospasial -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-slate-800">2. Titik Lokasi & Wilayah Administratif</h2>
                        <span class="text-xs text-slate-400">Peta otomatis sinkron saat wilayah dipilih</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kecamatan_select" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Kecamatan <span class="text-rose-500">*</span></label>
                            <select
                                id="kecamatan_select"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                            >
                                <option value="">Pilih Kecamatan Kota Bandung</option>
                                @foreach($kecamatanList as $kec)
                                    <option value="{{ $kec->id }}" {{ (old('kecamatan_id', $selectedKecamatanId ?? '') == $kec->id) ? 'selected' : '' }}>
                                        {{ $kec->nama_kecamatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="kelurahan_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Kelurahan <span class="text-rose-500">*</span></label>
                            <select
                                name="kelurahan_id"
                                id="kelurahan_id"
                                required
                                class="w-full px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('kelurahan_id') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                            >
                                <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                            </select>
                            @error('kelurahan_id')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Peta Leaflet Interaktif -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-slate-700">Tandai Lokasi Presisi di Peta (Geser Pin Merah):</label>
                            <button type="button" onclick="getCurrentLocation()" class="inline-flex items-center gap-1.5 text-xs text-[#0E7C86] hover:text-[#0b636b] font-medium">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Gunakan GPS Saya
                            </button>
                        </div>
                        <div id="leafletMap" class="w-full h-72 rounded-2xl border border-slate-300 overflow-hidden shadow-inner z-0"></div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Klik pada peta atau seret marker untuk menyesuaikan titik lokasi kerusakan.</p>
                    </div>

                    <!-- Hidden Coordinate Inputs -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Latitude</label>
                            <input
                                type="text"
                                name="latitude"
                                id="latitude"
                                value="{{ old('latitude', '-6.91746400') }}"
                                readonly
                                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-600"
                            >
                            @error('latitude')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Longitude</label>
                            <input
                                type="text"
                                name="longitude"
                                id="longitude"
                                value="{{ old('longitude', '107.61912300') }}"
                                readonly
                                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-600"
                            >
                            @error('longitude')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('warga.laporan.saya') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit" dusk="submit-btn" class="px-6 py-2.5 bg-[#F2A93B] hover:bg-[#d9932b] text-slate-900 font-bold text-sm rounded-xl transition-all shadow-md flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Laporan Kerusakan
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Leaflet JS Initialization -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let map;
        let marker;
        const defaultLat = parseFloat(document.getElementById('latitude').value) || -6.917464;
        const defaultLng = parseFloat(document.getElementById('longitude').value) || 107.619123;

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Peta (Default Alun-alun Kota Bandung)
            map = L.map('leafletMap').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors | INFRA-DAYA'
            }).addTo(map);

            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

            // Chained Dropdown Wilayah
            const kecSelect = document.getElementById('kecamatan_select');
            const kelSelect = document.getElementById('kelurahan_id');
            const initialKelurahanId = '{{ old('kelurahan_id', $selectedKelurahanId ?? '') }}';

            function loadKelurahan(kecId, preselectId = null) {
                kelSelect.innerHTML = '<option value="">Memuat kelurahan...</option>';

                if (!kecId) {
                    kelSelect.innerHTML = '<option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>';
                    return;
                }

                fetch(`/api/wilayah/kecamatan/${kecId}/kelurahan`)
                    .then(res => res.json())
                    .then(res => {
                        kelSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
                        if (res.data && res.data.length > 0) {
                            res.data.forEach(kel => {
                                const opt = document.createElement('option');
                                opt.value = kel.id;
                                opt.textContent = kel.nama_kelurahan;
                                if (preselectId && kel.id == preselectId) {
                                    opt.selected = true;
                                }
                                kelSelect.appendChild(opt);
                            });
                        }
                    })
                    .catch(() => {
                        kelSelect.innerHTML = '<option value="">Gagal memuat kelurahan</option>';
                    });
            }

            kecSelect.addEventListener('change', function() {
                loadKelurahan(this.value);
            });

            if (kecSelect.value) {
                loadKelurahan(kecSelect.value, initialKelurahanId);
            }
        });

        function updateCoordinates(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                }, function() {
                    alert('Gagal mendeteksi lokasi GPS perangkat Anda.');
                });
            } else {
                alert('Browser Anda tidak mendukung geolokasi.');
            }
        }
    </script>
</x-warga-layout>
