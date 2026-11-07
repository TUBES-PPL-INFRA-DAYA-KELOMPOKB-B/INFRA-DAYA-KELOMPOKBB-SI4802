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

                    <!-- Peta Leaflet Interaktif (PBI-8) -->
                    <div class="space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">
                                    Tandai Titik Lokasi Presisi di Peta (Geser Pin Merah) <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-xs text-slate-500">Klik peta atau seret penanda pin untuk menentukan koordinat lintang &amp; bujur secara presisi tanpa wajib GPS.</p>
                            </div>
                            <button
                                type="button"
                                id="btnUseMyLocation"
                                dusk="btn-use-my-location"
                                onclick="getCurrentLocation()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#0E7C86] font-semibold text-xs transition-colors border border-teal-200 shadow-2xs cursor-pointer self-start sm:self-auto"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Gunakan lokasi saya
                            </button>
                        </div>

                        <!-- Canvas Peta Leaflet (NFR-01 Render < 2s, NFR-03 Mobile Responsive) -->
                        <div id="leafletMap" dusk="leaflet-map" class="w-full h-72 sm:h-80 md:h-96 rounded-2xl border border-slate-300 overflow-hidden shadow-inner z-0 relative"></div>

                        <!-- Baris Kontrol Titik & Konfirmasi -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
                            <div class="flex items-center gap-2">
                                <span id="pinStatusDot" class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-medium text-slate-600">
                                    Koordinat terpilih: <span id="coordDisplay" dusk="coord-display" class="font-mono font-bold text-[#0E7C86]">{{ old('latitude', '-6.91746400') }}, {{ old('longitude', '107.61912300') }}</span>
                                </span>
                            </div>
                            <button
                                type="button"
                                id="btnKonfirmasiTitik"
                                dusk="konfirmasi-titik-btn"
                                onclick="konfirmasiTitik()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer self-start sm:self-auto"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Konfirmasi Titik Lokasi
                            </button>
                        </div>

                        <!-- Badge Status Konfirmasi -->
                        <div id="konfirmasiBadge" dusk="konfirmasi-badge" class="hidden p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-medium flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="konfirmasiBadgeText">Titik koordinat berhasil dikonfirmasi dan terkunci untuk laporan ini.</span>
                        </div>
                    </div>

                    <!-- Field Patokan Lokasi Terdekat -->
                    <div>
                        <label for="patokan_lokasi" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Patokan Lokasi Terdekat <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <input
                            type="text"
                            name="patokan_lokasi"
                            id="patokan_lokasi"
                            dusk="patokan-lokasi"
                            value="{{ old('patokan_lokasi') }}"
                            class="w-full px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('patokan_lokasi') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                            placeholder="Contoh: Depan Gapura RW 05, Seberang Toko Sumber Rezeki, Dekat Lampu Merah..."
                        >
                        @error('patokan_lokasi')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hidden / Locked Coordinate Inputs -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Latitude (Lintang Terkunci)</label>
                            <input
                                type="text"
                                name="latitude"
                                id="latitude"
                                dusk="latitude"
                                value="{{ old('latitude', '-6.91746400') }}"
                                readonly
                                class="w-full px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-lg text-xs font-mono text-slate-700 cursor-not-allowed select-all"
                            >
                            @error('latitude')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Longitude (Bujur Terkunci)</label>
                            <input
                                type="text"
                                name="longitude"
                                id="longitude"
                                dusk="longitude"
                                value="{{ old('longitude', '107.61912300') }}"
                                readonly
                                class="w-full px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-lg text-xs font-mono text-slate-700 cursor-not-allowed select-all"
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

    <!-- Leaflet JS Initialization (PBI-8) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let map;
        let marker;
        const defaultLat = parseFloat(document.getElementById('latitude').value) || -6.917464;
        const defaultLng = parseFloat(document.getElementById('longitude').value) || 107.619123;

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Peta (Default Pusat Kota Bandung: -6.9175, 107.6191)
            map = L.map('leafletMap', {
                zoomControl: true,
                fadeAnimation: false,
            }).setView([defaultLat, defaultLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors | INFRA-DAYA'
            }).addTo(map);

            // Draggable pinpoint marker (SKF-03)
            marker = L.marker([defaultLat, defaultLng], {
                draggable: true,
                autoPan: true
            }).addTo(map);

            marker.bindPopup('<b>Titik Kerusakan</b><br>Geser pin merah untuk menyesuaikan titik koordinat presisi.').openPopup();

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

            // Pastikan peta terender penuh responsif (NFR-01 & NFR-03)
            setTimeout(() => {
                map.invalidateSize();
            }, 100);

            // Chained Dropdown Wilayah (PBI-3 Listener tanpa mengubah logika dependensi)
            const kecSelect = document.getElementById('kecamatan_select');
            const kelSelect = document.getElementById('kelurahan_id');
            const initialKelurahanId = '{{ old('kelurahan_id', $selectedKelurahanId ?? '') }}';

            if (kecSelect && kelSelect) {
                kecSelect.addEventListener('change', function() {
                    const kecId = this.value;
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
                                    kelSelect.appendChild(opt);
                                });
                            }
                        })
                        .catch(() => {
                            kelSelect.innerHTML = '<option value="">Gagal memuat kelurahan</option>';
                        });
                });
            }
        });

        function updateCoordinates(lat, lng) {
            const latFixed = lat.toFixed(8);
            const lngFixed = lng.toFixed(8);

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            latInput.value = latFixed;
            lngInput.value = lngFixed;

            // Trigger events for reactive listeners or Dusk assertions
            latInput.dispatchEvent(new Event('input', { bubbles: true }));
            latInput.dispatchEvent(new Event('change', { bubbles: true }));
            lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            lngInput.dispatchEvent(new Event('change', { bubbles: true }));

            const coordDisplay = document.getElementById('coordDisplay');
            if (coordDisplay) {
                coordDisplay.textContent = `${latFixed}, ${lngFixed}`;
            }

            if (marker) {
                marker.setPopupContent(`<b>Titik Terpilih</b><br>Lat: ${latFixed}<br>Lng: ${lngFixed}`);
            }

            const badge = document.getElementById('konfirmasiBadge');
            if (badge && !badge.classList.contains('hidden')) {
                badge.classList.add('hidden');
            }
        }

        function konfirmasiTitik() {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;

            const badge = document.getElementById('konfirmasiBadge');
            const badgeText = document.getElementById('konfirmasiBadgeText');
            if (badge && badgeText) {
                badgeText.textContent = `Titik koordinat berhasil dikonfirmasi: (${lat}, ${lng})`;
                badge.classList.remove('hidden');
            }

            if (marker) {
                marker.bindPopup(`<b>Titik Terkonfirmasi</b><br>Lat: ${lat}<br>Lng: ${lng}`).openPopup();
            }
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                    konfirmasiTitik();
                }, function(err) {
                    alert('Gagal mendeteksi lokasi GPS perangkat Anda. Anda tetap dapat menggeser pin marker merah pada peta secara manual.');
                }, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
            } else {
                alert('Browser Anda tidak mendukung geolokasi.');
            }
        }
    </script>
</x-warga-layout>
