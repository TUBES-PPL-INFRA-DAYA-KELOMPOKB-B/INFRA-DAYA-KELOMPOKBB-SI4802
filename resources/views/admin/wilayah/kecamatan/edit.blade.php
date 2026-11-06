<x-admin-layout title="Ubah Kecamatan {{ $kecamatan->nama_kecamatan }}" header="Ubah Data Kecamatan">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('admin.kecamatan.index') }}" class="hover:text-brand-navy">Wilayah</a>
            <span>&rarr;</span>
            <a href="{{ route('admin.kecamatan.index') }}" class="hover:text-brand-navy">Kecamatan</a>
            <span>&rarr;</span>
            <span class="font-bold text-slate-700">Ubah Data</span>
        </div>

        <x-card title="Ubah Data Kecamatan" subtitle="Perbarui kode atau nama kecamatan">
            <form action="{{ route('admin.kecamatan.update', $kecamatan->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <x-form-input
                    name="kode_kecamatan"
                    label="Kode Kecamatan"
                    placeholder="Contoh: 327301"
                    :value="old('kode_kecamatan', $kecamatan->kode_kecamatan)"
                    maxlength="10"
                    required
                />

                <x-form-input
                    name="nama_kecamatan"
                    label="Nama Kecamatan"
                    placeholder="Contoh: Andir"
                    :value="old('nama_kecamatan', $kecamatan->nama_kecamatan)"
                    maxlength="100"
                    required
                />

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <x-button variant="secondary" :href="route('admin.kecamatan.index')">
                        Batal
                    </x-button>
                    <x-button variant="primary" type="submit" dusk="submit-btn">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-admin-layout>
