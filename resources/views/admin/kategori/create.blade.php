<x-admin-layout title="Tambah Kategori Baru">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Tambah Kategori Baru</h1>
                <p class="text-sm text-slate-500 mt-1">Daftarkan kategori infrastruktur publik dan bobot prioritas penanganan.</p>
            </div>
            <a href="{{ route('admin.kategori.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Kembali
            </a>
        </div>

        <!-- Form Card -->
        <x-card class="p-6">
            <form method="POST" action="{{ route('admin.kategori.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="nama_kategori" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="nama_kategori"
                        id="nama_kategori"
                        value="{{ old('nama_kategori') }}"
                        required
                        class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('nama_kategori') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                        placeholder="Contoh: Penerangan Jalan Umum (PJU)"
                    >
                    @error('nama_kategori')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tingkat_keparahan_default" class="block text-sm font-semibold text-slate-700 mb-1.5">Tingkat Keparahan Default <span class="text-rose-500">*</span></label>
                    <p class="text-xs text-slate-500 mb-2">Pilihan tingkat keparahan otomatis menentukan bobot prioritas (1 - 4) laporan baru.</p>
                    <select
                        name="tingkat_keparahan_default"
                        id="tingkat_keparahan_default"
                        required
                        class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('tingkat_keparahan_default') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                    >
                        <option value="" disabled {{ old('tingkat_keparahan_default') ? '' : 'selected' }}>-- Pilih Tingkat Keparahan Default --</option>
                        <option value="kritis" {{ old('tingkat_keparahan_default') == 'kritis' ? 'selected' : '' }}>🔴 Kritis (Bobot 4 - Ancaman keselamatan langsung / potensi bencana)</option>
                        <option value="tinggi" {{ old('tingkat_keparahan_default') == 'tinggi' ? 'selected' : '' }}>🟠 Tinggi (Bobot 3 - Berpotensi membahayakan pengguna atau memicu kemacetan)</option>
                        <option value="sedang" {{ old('tingkat_keparahan_default', 'sedang') == 'sedang' ? 'selected' : '' }}>🟡 Sedang (Bobot 2 - Mengganggu kelancaran / kenyamanan belum membahayakan)</option>
                        <option value="rendah" {{ old('tingkat_keparahan_default') == 'rendah' ? 'selected' : '' }}>🟢 Rendah (Bobot 1 - Kerusakan kecil yang tidak mengganggu keselamatan)</option>
                    </select>
                    @error('tingkat_keparahan_default')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Cakupan Kerusakan</label>
                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        rows="4"
                        class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('deskripsi') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                        placeholder="Jelaskan jenis-jenis kerusakan yang termasuk dalam kategori ini..."
                    >{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.kategori.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit" dusk="submit-btn" class="px-5 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white font-medium text-sm rounded-xl transition-all shadow-sm">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-admin-layout>
