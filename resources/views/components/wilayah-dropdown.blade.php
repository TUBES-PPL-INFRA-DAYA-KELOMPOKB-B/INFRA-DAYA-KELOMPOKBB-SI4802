@props([
    'kecamatanList',
    'selectedKecamatan' => null,
    'selectedKelurahan' => null,
    'kecamatanName' => 'kecamatan_id',
    'kelurahanName' => 'kelurahan_id',
    'kecamatanIdAttr' => 'kecamatan_select',
    'kelurahanIdAttr' => 'kelurahan_id',
    'required' => true,
    'showLabels' => true,
])

<div
    x-data="wilayahDropdown({
        initialKecamatan: '{{ old($kecamatanName, $selectedKecamatan) }}',
        initialKelurahan: '{{ old($kelurahanName, $selectedKelurahan) }}'
    })"
    class="space-y-4"
>
    <!-- Dropdown Kecamatan -->
    <div class="space-y-1.5">
        @if ($showLabels)
            <label for="{{ $kecamatanIdAttr }}" class="block text-xs font-semibold text-slate-700">
                Kecamatan @if($required)<span class="text-rose-500">*</span>@endif
            </label>
        @endif
        <select
            id="{{ $kecamatanIdAttr }}"
            name="{{ $kecamatanName }}"
            x-model="selectedKecamatan"
            @change="onKecamatanChange()"
            @if($required) required @endif
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all bg-white"
        >
            <option value="">-- Pilih Kecamatan Kota Bandung --</option>
            @foreach($kecamatanList as $kec)
                <option value="{{ $kec->id }}" data-nama="{{ $kec->nama_kecamatan }}">
                    {{ $kec->nama_kecamatan }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Dropdown Kelurahan -->
    <div class="space-y-1.5">
        @if ($showLabels)
            <label for="{{ $kelurahanIdAttr }}" class="block text-xs font-semibold text-slate-700">
                Kelurahan @if($required)<span class="text-rose-500">*</span>@endif
            </label>
        @endif
        <select
            id="{{ $kelurahanIdAttr }}"
            name="{{ $kelurahanName }}"
            x-model="selectedKelurahan"
            @change="onKelurahanChange()"
            :disabled="!selectedKecamatan || loading"
            @if($required) required @endif
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all bg-white disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
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
        <p x-show="loading" class="text-[11px] text-blue-600 font-medium">Memuat kelurahan...</p>
    </div>
</div>

@once
@push('scripts')
<script>
    function wilayahDropdown(config) {
        return {
            selectedKecamatan: config.initialKecamatan || '',
            selectedKelurahan: config.initialKelurahan || '',
            kelurahanList: [],
            loading: false,

            init() {
                if (this.selectedKecamatan) {
                    this.fetchKelurahan(this.selectedKecamatan, this.selectedKelurahan);
                }
            },

            onKecamatanChange() {
                this.selectedKelurahan = '';
                this.kelurahanList = [];
                if (this.selectedKecamatan) {
                    this.fetchKelurahan(this.selectedKecamatan);
                }
                this.dispatchChange();
            },

            onKelurahanChange() {
                this.dispatchChange();
            },

            fetchKelurahan(kecamatanId, preselectId = null) {
                this.loading = true;
                fetch(`/api/wilayah/kecamatan/${kecamatanId}/kelurahan`)
                    .then(res => res.json())
                    .then(res => {
                        this.kelurahanList = res.data || [];
                        if (preselectId) {
                            this.selectedKelurahan = preselectId;
                        }
                        this.loading = false;
                        this.dispatchChange();
                    })
                    .catch(() => {
                        this.kelurahanList = [];
                        this.loading = false;
                    });
            },

            dispatchChange() {
                const kecOption = document.querySelector(`#{{ $kecamatanIdAttr }} option[value="${this.selectedKecamatan}"]`);
                const kecName = kecOption ? kecOption.textContent.trim() : '';

                const selectedKel = this.kelurahanList.find(k => k.id == this.selectedKelurahan);
                const kelName = selectedKel ? selectedKel.nama_kelurahan : '';

                window.dispatchEvent(new CustomEvent('wilayah-changed', {
                    detail: {
                        kecamatanId: this.selectedKecamatan,
                        kecamatanName: kecName,
                        kelurahanId: this.selectedKelurahan,
                        kelurahanName: kelName
                    }
                }));
            }
        };
    }
</script>
@endpush
@endonce

