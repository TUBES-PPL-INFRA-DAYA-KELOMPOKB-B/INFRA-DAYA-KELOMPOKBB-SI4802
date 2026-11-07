<x-portal-warga-layout title="Pilih wilayah kerusakan" header="Pilih wilayah kerusakan">
    <div
        x-data="pilihWilayahKerusakan({
            initialKecamatan: '{{ $selectedKecamatanId ?? '' }}',
            initialKelurahan: '{{ $selectedKelurahanId ?? '' }}'
        })"
        class="space-y-6"
    >
        <!-- Header & Breadcrumbs -->
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">
                SPRINT 1 / PBI 03 / WARGA
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Pilih wilayah kerusakan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Tentukan kecamatan, kemudian kelurahan. Anda tidak harus berada di lokasi.
            </p>
        </div>

        <!-- 2-Card Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            <!-- 01 / Wilayah Administratif Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs flex flex-col justify-between">
                <div class="space-y-4">
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">
                        01 / Wilayah administratif
                    </h2>

                    <!-- Pilih Kecamatan -->
                    <div class="space-y-1.5">
                        <label for="kecamatan_select" class="block text-xs font-semibold text-slate-700">
                            Kecamatan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="kecamatan_select"
                            name="kecamatan_id"
                            x-model="selectedKecamatan"
                            @change="onKecamatanChange()"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all bg-white"
                        >
                            <option value="">-- Pilih Kecamatan Kota Bandung --</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec->id }}" data-nama="{{ $kec->nama_kecamatan }}">
                                    {{ $kec->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Kelurahan Input / Select -->
                    <div class="space-y-1.5">
                        <label for="kelurahan_id" class="block text-xs font-semibold text-slate-700">
                            Kelurahan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="kelurahan_id"
                            name="kelurahan_id"
                            x-model="selectedKelurahan"
                            @change="onKelurahanChange()"
                            :disabled="!selectedKecamatan || loading"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all bg-white disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed"
                        >
                            <template x-if="!selectedKecamatan">
                                <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                            </template>
                            <template x-if="selectedKecamatan && loading">
                                <option value="">Memuat daftar kelurahan...</option>
                            </template>
                            <template x-if="selectedKecamatan && !loading">
                                <option value="">-- Pilih Kelurahan --</option>
                            </template>
                            <template x-for="kel in kelurahanList" :key="kel.id">
                                <option :value="kel.id" x-text="kel.nama_kelurahan" :selected="kel.id == selectedKelurahan"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Interactive Kelurahan Selection Box (As shown in mockup) -->
                    <div class="mt-3 rounded-xl border border-slate-200 overflow-hidden bg-white shadow-xs">
                        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">PILIH KELURAHAN</span>
                            <span x-show="loading" class="text-[11px] text-blue-600 font-medium">Memuat...</span>
                        </div>

                        <div class="divide-y divide-slate-100 max-h-48 overflow-y-auto">
                            <!-- Empty State if no kecamatan -->
                            <div x-show="!selectedKecamatan" class="p-4 text-center text-xs text-slate-400">
                                Pilih kecamatan untuk melihat daftar kelurahan.
                            </div>

                            <!-- List of kelurahan in selected kecamatan -->
                            <template x-for="kel in kelurahanList" :key="kel.id">
                                <button
                                    type="button"
                                    @click="selectKelurahanItem(kel.id, kel.nama_kelurahan)"
                                    class="w-full px-4 py-2.5 text-left text-sm flex items-center justify-between transition-colors hover:bg-slate-50"
                                    :class="selectedKelurahan == kel.id ? 'bg-blue-50/50 text-blue-700 font-semibold' : 'text-slate-700'"
                                >
                                    <span x-text="kel.nama_kelurahan"></span>
                                    <span x-show="selectedKelurahan == kel.id" class="text-blue-600 font-bold">✓</span>
                                </button>
                            </template>

                            <div x-show="selectedKecamatan && !loading && kelurahanList.length === 0" class="p-4 text-center text-xs text-slate-400">
                                Tidak ada data kelurahan pada kecamatan ini.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Callout Box (Reset behavior info) -->
                <div class="mt-6 rounded-xl bg-blue-50/70 border border-blue-100 p-3 text-xs text-blue-700 leading-relaxed">
                    Ganti kecamatan akan mengosongkan pilihan kelurahan dan memusatkan ulang peta.
                </div>
            </div>

            <!-- 02 / Lokasi Pada Peta Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-bold text-slate-800 tracking-tight">
                            02 / Lokasi pada peta
                        </h2>
                        <span class="text-xs font-semibold text-slate-500" x-text="summaryLabel">
                            Kota Bandung
                        </span>
                    </div>

                    <!-- Schematic Leaflet Map Container -->
                    <div class="relative w-full h-72 rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                        <div id="petaWilayahKerusakan" class="w-full h-full z-0"></div>

                        <!-- Map Schematic Caption Overlay -->
                        <div class="absolute bottom-2 inset-x-2 bg-white/95 backdrop-blur-xs text-[11px] text-slate-600 px-3 py-1.5 rounded-xl border border-slate-200 text-center font-medium shadow-xs z-10">
                            Peta skematis • Kota Bandung • bukan koordinat operasional
                        </div>
                    </div>

                    <!-- Status Text -->
                    <div class="mt-3 text-xs text-slate-500">
                        <span x-show="!selectedKecamatan">Kelurahan dinonaktifkan hingga kecamatan dipilih.</span>
                        <span x-show="selectedKecamatan && !selectedKelurahan">Pilih salah satu kelurahan untuk melanjutkan.</span>
                        <span x-show="selectedKecamatan && selectedKelurahan" class="text-emerald-600 font-semibold">
                            Wilayah administratif terkonfirmasi: <span x-text="selectedKecamatanName"></span>, Kel. <span x-text="selectedKelurahanName"></span>.
                        </span>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="mt-6">
                    <button
                        type="button"
                        @click="lanjutDetail()"
                        :disabled="!selectedKelurahan"
                        class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-all shadow-md flex items-center justify-center gap-2"
                    >
                        <span>Lanjut ke detail laporan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Design Note -->
        <div class="pt-4 text-xs text-slate-400">
            Data contoh • Mockup desain, bukan data operasional
        </div>
    </div>

    @push('scripts')
    <script>
        function pilihWilayahKerusakan(config) {
            return {
                selectedKecamatan: config.initialKecamatan || '',
                selectedKelurahan: config.initialKelurahan || '',
                selectedKecamatanName: '',
                selectedKelurahanName: '',
                kelurahanList: [],
                loading: false,
                map: null,
                marker: null,

                init() {
                    this.initMap();

                    if (this.selectedKecamatan) {
                        const opt = document.querySelector(`#kecamatan_select option[value="${this.selectedKecamatan}"]`);
                        if (opt) {
                            this.selectedKecamatanName = opt.getAttribute('data-nama') || opt.textContent.trim();
                        }
                        this.fetchKelurahan(this.selectedKecamatan, this.selectedKelurahan);
                    }
                },

                initMap() {
                    const defaultLat = -6.917464;
                    const defaultLng = 107.619123;

                    this.map = L.map('petaWilayahKerusakan').setView([defaultLat, defaultLng], 12);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.marker = L.marker([defaultLat, defaultLng], {
                        draggable: true
                    }).addTo(this.map);

                    this.marker.bindPopup("Titik indikatif kerusakan").openPopup();

                    this.marker.on('dragend', () => {
                        const pos = this.marker.getLatLng();
                    });
                },

                get summaryLabel() {
                    if (this.selectedKecamatanName && this.selectedKelurahanName) {
                        return `${this.selectedKecamatanName} • ${this.selectedKelurahanName}`;
                    }
                    if (this.selectedKecamatanName) {
                        return this.selectedKecamatanName;
                    }
                    return 'Kota Bandung';
                },

                onKecamatanChange() {
                    this.selectedKelurahan = '';
                    this.selectedKelurahanName = '';
                    this.kelurahanList = [];

                    const opt = document.querySelector(`#kecamatan_select option[value="${this.selectedKecamatan}"]`);
                    this.selectedKecamatanName = opt && this.selectedKecamatan ? (opt.getAttribute('data-nama') || opt.textContent.trim()) : '';

                    if (this.selectedKecamatan) {
                        this.fetchKelurahan(this.selectedKecamatan);
                    }

                    // Pusatkan ulang peta ke titik acuan Bandung
                    if (this.map) {
                        this.map.setView([-6.917464, 107.619123], 12);
                        if (this.marker) {
                            this.marker.setLatLng([-6.917464, 107.619123]);
                        }
                    }
                },

                onKelurahanChange() {
                    const found = this.kelurahanList.find(k => k.id == this.selectedKelurahan);
                    this.selectedKelurahanName = found ? found.nama_kelurahan : '';
                },

                selectKelurahanItem(id, nama) {
                    this.selectedKelurahan = id;
                    this.selectedKelurahanName = nama;
                },

                fetchKelurahan(kecamatanId, preselectId = null) {
                    this.loading = true;
                    fetch(`/api/wilayah/kecamatan/${kecamatanId}/kelurahan`)
                        .then(res => res.json())
                        .then(res => {
                            this.kelurahanList = res.data || [];
                            if (preselectId) {
                                this.selectedKelurahan = preselectId;
                                const found = this.kelurahanList.find(k => k.id == preselectId);
                                if (found) {
                                    this.selectedKelurahanName = found.nama_kelurahan;
                                }
                            }
                            this.loading = false;
                        })
                        .catch(() => {
                            this.kelurahanList = [];
                            this.loading = false;
                        });
                },

                lanjutDetail() {
                    if (!this.selectedKelurahan) {
                        alert('Silakan pilih kelurahan terlebih dahulu.');
                        return;
                    }

                    const url = new URL('{{ route("warga.laporan.create") }}', window.location.origin);
                    url.searchParams.set('kecamatan_id', this.selectedKecamatan);
                    url.searchParams.set('kelurahan_id', this.selectedKelurahan);

                    window.location.href = url.toString();
                }
            };
        }
    </script>
    @endpush
</x-portal-warga-layout>

