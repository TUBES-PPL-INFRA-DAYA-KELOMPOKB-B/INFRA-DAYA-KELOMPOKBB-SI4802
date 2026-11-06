<x-warga-layout title="Peta Sebaran Kerusakan Infrastruktur Kota Bandung">
    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- Header & Deskripsi -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#0E7C86]/10 text-[#0E7C86]">
                        <span class="h-2 w-2 rounded-full bg-[#0E7C86] animate-pulse"></span>
                        Peta Geospasial Interaktif
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-500">Keterbukaan Data SDG 9</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1F3A5F] tracking-tight">
                    Peta Sebaran Kerusakan Infrastruktur
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau sebaran titik kerusakan fasilitas umum di Kota Bandung secara transparan dan terverifikasi.
                </p>
            </div>

            <!-- Tombol Lapor Cepat -->
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('tracking.publik') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs sm:text-sm font-bold shadow-sm transition"
                >
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lacak Tiket</span>
                </a>
                <a
                    href="{{ route('warga.laporan.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-amber hover:bg-amber-400 text-brand-navy text-xs sm:text-sm font-black shadow-md transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Lapor Baru</span>
                </a>
            </div>
        </div>

        <!-- 4 Quick Metric Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-xs font-bold tracking-wider text-slate-500">Total Masalah Dipetakan</span>
                <p class="mt-2 text-2xl font-black text-[#1F3A5F]">{{ $stats['total'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh laporan warga</p>
            </div>
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-xs font-bold tracking-wider text-slate-500">Menunggu Verifikasi</span>
                <p class="mt-2 text-2xl font-black text-slate-700">{{ $stats['menunggu'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Dalam antrean telaah admin</p>
            </div>
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-xs font-bold tracking-wider text-amber-600">Sedang Ditangani</span>
                <p class="mt-2 text-2xl font-black text-amber-600">{{ $stats['diproses'] }}</p>
                <p class="text-[11px] text-amber-700/70 mt-0.5">Penugasan / pengerjaan fisik</p>
            </div>
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-xs font-bold tracking-wider text-emerald-600">Tuntas Diperbaiki</span>
                <p class="mt-2 text-2xl font-black text-emerald-600">{{ $stats['selesai'] }}</p>
                <p class="text-[11px] text-emerald-700/70 mt-0.5">Hasil perbaikan tuntas</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Filter Kategori -->
                <div>
                    <label for="filter_kategori" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                        Kategori Fasilitas
                    </label>
                    <select
                        id="filter_kategori"
                        dusk="filter-kategori"
                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent py-2 px-3"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <label for="filter_status" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                        Status Penanganan
                    </label>
                    <select
                        id="filter_status"
                        dusk="filter-status"
                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent py-2 px-3"
                    >
                        <option value="">Semua Status</option>
                        <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                        <option value="diproses">Sedang Diproses / Dikerjakan</option>
                        <option value="selesai">Tuntas Diperbaiki</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <!-- Filter Kecamatan -->
                <div>
                    <label for="filter_kecamatan" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                        Kecamatan
                    </label>
                    <select
                        id="filter_kecamatan"
                        dusk="filter-kecamatan"
                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent py-2 px-3"
                    >
                        <option value="">Semua Kecamatan</option>
                        @foreach($kecamatanList as $kec)
                            <option value="{{ $kec->id }}">{{ $kec->nama_kecamatan }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div>
                    <label for="filter_search" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                        Kata Kunci
                    </label>
                    <input
                        type="text"
                        id="filter_search"
                        dusk="filter-search"
                        placeholder="Cari judul / tiket..."
                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent py-2 px-3"
                    >
                </div>

                <!-- Reset Button -->
                <div class="flex items-end">
                    <button
                        type="button"
                        id="btn_reset_filter"
                        class="w-full px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>
        </x-card>

        <!-- Leaflet Map Container & Info Header -->
        <x-card class="p-0 overflow-hidden relative shadow-lg">
            <!-- Top Map Header Overlay -->
            <div class="p-4 border-b border-slate-100 bg-white/95 backdrop-blur-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 z-10 relative">
                <div class="flex items-center gap-2">
                    <span id="marker_count" class="text-xs font-bold text-slate-700">
                        Memuat data titik sebaran...
                    </span>
                    <span id="loading_indicator" class="inline-block h-3 w-3 border-2 border-[#0E7C86] border-t-transparent rounded-full animate-spin"></span>
                </div>

                <!-- Legend Mini Bar -->
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-600">Selesai</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span class="text-slate-600">Diproses</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                        <span class="text-slate-600">Menunggu</span>
                    </span>
                </div>
            </div>

            <!-- The Map -->
            <div id="leafletPublicMap" class="w-full h-[540px] sm:h-[640px] bg-slate-100 z-0"></div>
        </x-card>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapContainer = document.getElementById('leafletPublicMap');
            if (!mapContainer || typeof L === 'undefined') return;

            // Inisialisasi peta berpusat di Alun-Alun Kota Bandung
            const defaultLat = -6.9175;
            const defaultLng = 107.6191;
            const map = L.map('leafletPublicMap').setView([defaultLat, defaultLng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const markerLayerGroup = L.layerGroup().addTo(map);

            // Icon Builder sesuai status
            function getMarkerIcon(status) {
                let colorClass = '#64748b'; // slate
                if (status === 'selesai') colorClass = '#10b981'; // emerald
                else if (status === 'diproses') colorClass = '#f59e0b'; // amber
                else if (status === 'ditolak') colorClass = '#ef4444'; // rose

                return L.divIcon({
                    className: 'custom-leaflet-marker',
                    html: `<div style="
                        background-color: ${colorClass};
                        width: 18px;
                        height: 18px;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                    "></div>`,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    popupAnchor: [0, -10]
                });
            }

            const kategoriInput = document.getElementById('filter_kategori');
            const statusInput = document.getElementById('filter_status');
            const kecamatanInput = document.getElementById('filter_kecamatan');
            const searchInput = document.getElementById('filter_search');
            const resetBtn = document.getElementById('btn_reset_filter');
            const markerCount = document.getElementById('marker_count');
            const loadingIndicator = document.getElementById('loading_indicator');

            let debounceTimer = null;

            function loadMarkers() {
                if (loadingIndicator) loadingIndicator.classList.remove('hidden');

                const params = new URLSearchParams();
                if (kategoriInput.value) params.append('kategori_id', kategoriInput.value);
                if (statusInput.value) params.append('status', statusInput.value);
                if (kecamatanInput.value) params.append('kecamatan_id', kecamatanInput.value);
                if (searchInput.value.trim()) params.append('search', searchInput.value.trim());

                fetch(`{{ route('api.peta.markers') }}?${params.toString()}`)
                    .then(res => res.json())
                    .then(json => {
                        if (loadingIndicator) loadingIndicator.classList.add('hidden');
                        markerLayerGroup.clearLayers();

                        const data = json.data || [];
                        if (markerCount) {
                            markerCount.textContent = `Menampilkan ${data.length} titik infrastruktur`;
                        }

                        if (data.length > 0) {
                            const bounds = [];
                            data.forEach(item => {
                                const marker = L.marker([item.latitude, item.longitude], {
                                    icon: getMarkerIcon(item.status)
                                });

                                const statusLabel = item.status === 'selesai' ? 'Selesai' : (item.status === 'diproses' ? 'Diproses' : (item.status === 'ditolak' ? 'Ditolak' : 'Menunggu'));
                                const statusColor = item.status === 'selesai' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : (item.status === 'diproses' ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-slate-700 bg-slate-50 border-slate-200');

                                const photoHtml = item.foto_sesudah ? `
                                    <div class="rounded-lg overflow-hidden my-2 max-h-32 border border-slate-200">
                                        <img src="${item.foto_sesudah}" alt="Bukti Sesudah" class="w-full h-24 object-cover">
                                    </div>
                                ` : (item.foto_sebelum ? `
                                    <div class="rounded-lg overflow-hidden my-2 max-h-32 border border-slate-200">
                                        <img src="${item.foto_sebelum}" alt="Bukti Sebelum" class="w-full h-24 object-cover">
                                    </div>
                                ` : '');

                                marker.bindPopup(`
                                    <div class="text-xs p-1 max-w-[240px]">
                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <span class="font-mono font-bold text-[10px] text-[#1F3A5F]">${item.nomor_tiket}</span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded border ${statusColor}">${statusLabel}</span>
                                        </div>
                                        <strong class="text-slate-900 block mb-1 text-sm leading-tight">${item.judul}</strong>
                                        <span class="text-slate-500 block text-[11px] mb-1">Kel. ${item.kelurahan}, Kec. ${item.kecamatan}</span>
                                        ${photoHtml}
                                        <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                                            <span class="text-[10px] text-slate-400">${item.tanggal}</span>
                                            <a href="${item.url_lacak}" class="text-[11px] font-bold text-[#0E7C86] hover:underline">
                                                Lacak Tiket &rarr;
                                            </a>
                                        </div>
                                    </div>
                                `);

                                marker.addTo(markerLayerGroup);
                                bounds.push([item.latitude, item.longitude]);
                            });

                            // Jika ada filter spesifik, sesuaikan zoom bounds
                            if ((kategoriInput.value || kecamatanInput.value || searchInput.value) && bounds.length > 0) {
                                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
                            }
                        }
                    })
                    .catch(err => {
                        if (loadingIndicator) loadingIndicator.classList.add('hidden');
                        if (markerCount) markerCount.textContent = 'Gagal memuat titik peta.';
                        console.error('Error fetching markers:', err);
                    });
            }

            // Event listener filter
            kategoriInput.addEventListener('change', loadMarkers);
            statusInput.addEventListener('change', loadMarkers);
            kecamatanInput.addEventListener('change', loadMarkers);

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(loadMarkers, 400);
            });

            resetBtn.addEventListener('click', function () {
                kategoriInput.value = '';
                statusInput.value = '';
                kecamatanInput.value = '';
                searchInput.value = '';
                map.setView([defaultLat, defaultLng], 12);
                loadMarkers();
            });

            // Load awal
            loadMarkers();
        });
    </script>
</x-warga-layout>
