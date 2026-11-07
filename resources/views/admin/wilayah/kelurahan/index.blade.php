<x-admin-layout title="Kelola Data Kelurahan" header="Kelola data kelurahan">
    <div class="space-y-4">
        <!-- Top Title & Subtitle Section -->
        <div>
            <div class="text-xs font-bold text-blue-600 tracking-wider uppercase mb-1">
                SPRINT 1 / PBI 02 / ADMIN
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Kelola data kelurahan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelurahan selalu terkait dengan satu kecamatan induk.
            </p>
        </div>

        <!-- Two Columns Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start mt-4">
            <!-- Left Card: Daftar kelurahan (lg:col-span-7) -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <h2 class="text-base font-bold text-slate-900">Daftar kelurahan</h2>

                <!-- Filter form: Kecamatan dropdown + Cari kelurahan side by side -->
                <form action="{{ route('admin.kelurahan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Kecamatan</label>
                        <select
                            name="kecamatan_id"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
                        >
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach ($kecamatanList as $kec)
                                <option value="{{ $kec->id }}" {{ (string)$kecamatanId === (string)$kec->id ? 'selected' : '' }}>
                                    {{ $kec->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Cari kelurahan</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Nama atau kode"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
                            >
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto border-t border-slate-100 pt-2">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                            <tr>
                                <th class="px-3.5 py-2.5">KELURAHAN</th>
                                <th class="px-3.5 py-2.5">KECAMATAN</th>
                                <th class="px-3.5 py-2.5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($kelurahanList as $kelurahan)
                                <tr class="hover:bg-slate-50/70 transition-colors {{ (isset($editingKelurahan) && $editingKelurahan?->id === $kelurahan->id) ? 'bg-blue-50/50' : '' }}">
                                    <td class="px-3.5 py-3">
                                        <div class="font-semibold text-slate-900">{{ $kelurahan->nama_kelurahan }}</div>
                                        <div class="text-[11px] font-mono text-slate-400">{{ $kelurahan->kode_kelurahan }}</div>
                                    </td>
                                    <td class="px-3.5 py-3 text-sm text-slate-700">
                                        {{ $kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                                    </td>
                                    <td class="px-3.5 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('admin.kelurahan.index', ['edit_id' => $kelurahan->id, 'kecamatan_id' => $kecamatanId, 'search' => $search]) }}" class="text-xs font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                                                Ubah / Hapus
                                            </a>
                                            <!-- Form tersembunyi untuk Dusk test & programatik delete -->
                                            <form id="delete-form-kelurahan-{{ $kelurahan->id }}" method="POST" action="{{ route('admin.kelurahan.destroy', $kelurahan->id) }}" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3.5 py-6 text-center text-xs text-slate-400">
                                        Tidak ada data kelurahan ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Add Button under Table -->
                <div class="pt-2 flex items-center justify-between">
                    <a href="{{ route('admin.kelurahan.index', ['kecamatan_id' => $kecamatanId]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        <span>+ Tambah kelurahan</span>
                    </a>

                    @if ($kelurahanList->hasPages())
                        <div class="text-xs">
                            {{ $kelurahanList->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Card: Tambah / ubah kelurahan (lg:col-span-5) -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900">Tambah / ubah kelurahan</h2>

                <form
                    action="{{ $editingKelurahan ? route('admin.kelurahan.update', $editingKelurahan->id) : route('admin.kelurahan.store') }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf
                    @if ($editingKelurahan)
                        @method('PUT')
                    @endif

                    <!-- Kecamatan induk * -->
                    <div class="space-y-1">
                        <label for="kecamatan_id" class="block text-xs font-semibold text-slate-700">
                            Kecamatan induk *
                        </label>
                        <select
                            id="kecamatan_id"
                            name="kecamatan_id"
                            required
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 @error('kecamatan_id') border-rose-400 @enderror"
                        >
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach ($kecamatanList as $kec)
                                <option value="{{ $kec->id }}" {{ (string)old('kecamatan_id', $editingKelurahan?->kecamatan_id ?? $kecamatanId) === (string)$kec->id ? 'selected' : '' }}>
                                    {{ $kec->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                        @error('kecamatan_id')
                            <p class="text-[11px] font-medium text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kode kelurahan * -->
                    <div class="space-y-1">
                        <label for="kode_kelurahan" class="block text-xs font-semibold text-slate-700">
                            Kode kelurahan *
                        </label>
                        <input
                            type="text"
                            id="kode_kelurahan"
                            name="kode_kelurahan"
                            value="{{ old('kode_kelurahan', $editingKelurahan?->kode_kelurahan ?? '') }}"
                            placeholder="KEL-CONTOH-001"
                            maxlength="10"
                            required
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 @error('kode_kelurahan') border-rose-400 @enderror"
                        >
                        @error('kode_kelurahan')
                            <p class="text-[11px] font-medium text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama kelurahan * -->
                    <div class="space-y-1">
                        <label for="nama_kelurahan" class="block text-xs font-semibold text-slate-700">
                            Nama kelurahan *
                        </label>
                        <input
                            type="text"
                            id="nama_kelurahan"
                            name="nama_kelurahan"
                            value="{{ old('nama_kelurahan', $editingKelurahan?->nama_kelurahan ?? '') }}"
                            placeholder="Antapani Kidul"
                            maxlength="100"
                            required
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 @error('nama_kelurahan') border-rose-400 @enderror"
                        >
                        @error('nama_kelurahan')
                            <p class="text-[11px] font-medium text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            dusk="submit-btn"
                            class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors"
                        >
                            Simpan kelurahan
                        </button>
                    </div>
                </form>

                <!-- Warning box & Delete Action (Shown when editing) -->
                @if ($editingKelurahan)
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div class="rounded-xl bg-rose-50 border border-rose-100 p-3.5 text-xs text-rose-700 font-medium leading-relaxed">
                            Konfirmasi hapus: kelurahan dengan laporan terkait tidak dapat dihapus.
                        </div>

                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('admin.kelurahan.index', ['kecamatan_id' => $kecamatanId]) }}"
                                class="px-5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition-colors"
                            >
                                Batal
                            </a>
                            <form action="{{ route('admin.kelurahan.destroy', $editingKelurahan->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
                                >
                                    Hapus kelurahan
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="text-xs text-slate-400 pt-4">
            Data contoh • Mockup desain, bukan data operasional
        </div>
    </div>
</x-admin-layout>
