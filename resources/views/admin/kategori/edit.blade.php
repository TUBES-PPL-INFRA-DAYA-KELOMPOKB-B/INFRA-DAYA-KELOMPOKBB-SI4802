<x-admin-layout title="Ubah Kategori Infrastruktur">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Ubah Kategori Infrastruktur</h1>
                <p class="text-sm text-slate-500 mt-1">Perbarui nama kategori, cakupan masalah, atau bobot prioritas.</p>
            </div>
            <a href="{{ route('admin.kategori.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Kembali
            </a>
        </div>

        <!-- Form Card -->
        <x-card class="p-6">
            <form method="POST" action="{{ route('admin.kategori.update', $kategori->id) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama_kategori" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="nama_kategori"
                        id="nama_kategori"
                        value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                        required
                        class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('nama_kategori') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
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
                        @php
                            $currentTingkat = old('tingkat_keparahan_default', $kategori->tingkat_keparahan_default ?? \App\Models\KategoriInfrastruktur::getTingkatFromBobot($kategori->bobot_prioritas));
                        @endphp
                        <option value="kritis" {{ $currentTingkat == 'kritis' ? 'selected' : '' }}>🔴 Kritis (Bobot 4 - Ancaman keselamatan langsung / potensi bencana)</option>
                        <option value="tinggi" {{ $currentTingkat == 'tinggi' ? 'selected' : '' }}>🟠 Tinggi (Bobot 3 - Berpotensi membahayakan pengguna atau memicu kemacetan)</option>
                        <option value="sedang" {{ $currentTingkat == 'sedang' ? 'selected' : '' }}>🟡 Sedang (Bobot 2 - Mengganggu kelancaran / kenyamanan belum membahayakan)</option>
                        <option value="rendah" {{ $currentTingkat == 'rendah' ? 'selected' : '' }}>🟢 Rendah (Bobot 1 - Kerusakan kecil yang tidak mengganggu keselamatan)</option>
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
                    >{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.kategori.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit" dusk="submit-btn" class="px-5 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white font-medium text-sm rounded-xl transition-all shadow-sm">
                        Perbarui Kategori
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-admin-layout>
