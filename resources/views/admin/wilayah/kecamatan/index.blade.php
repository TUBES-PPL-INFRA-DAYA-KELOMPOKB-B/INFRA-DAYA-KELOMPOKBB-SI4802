<x-admin-layout title="Kelola Data Kecamatan" header="Kelola data kecamatan">
    <div class="space-y-4">
        <!-- Top Title & Subtitle Section -->
        <div>
            <div class="text-xs font-bold text-blue-600 tracking-wider uppercase mb-1">
                SPRINT 1 / PBI 01 / ADMIN
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Kelola data kecamatan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Data wilayah administratif Kota Bandung.
            </p>
        </div>

        <!-- Two Columns Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start mt-4">
            <!-- Left Card: Daftar kecamatan (lg:col-span-7) -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <h2 class="text-base font-bold text-slate-900">Daftar kecamatan</h2>

                <!-- Cari kecamatan form -->
                <form action="{{ route('admin.kecamatan.index') }}" method="GET" class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700">Cari kecamatan</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Ketik nama atau kode"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
                        >
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                            Cari
                        </button>
                        @if ($search)
                            <a href="{{ route('admin.kecamatan.index') }}" class="text-xs text-slate-500 hover:text-rose-600 underline">Reset</a>
                        @endif
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto border-t border-slate-100 pt-2">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                            <tr>
                                <th class="px-3.5 py-2.5">KODE</th>
                                <th class="px-3.5 py-2.5">KECAMATAN</th>
                                <th class="px-3.5 py-2.5 text-center">KELURAHAN</th>
                                <th class="px-3.5 py-2.5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($kecamatanList as $kecamatan)
                                <tr class="hover:bg-slate-50/70 transition-colors {{ (isset($editingKecamatan) && $editingKecamatan?->id === $kecamatan->id) ? 'bg-blue-50/50' : '' }}">
                                    <td class="px-3.5 py-3 font-mono text-xs font-semibold text-slate-700">
                                        {{ $kecamatan->kode_kecamatan }}
                                    </td>
                                    <td class="px-3.5 py-3 font-semibold text-slate-900">
                                        {{ $kecamatan->nama_kecamatan }}
                                    </td>
                                    <td class="px-3.5 py-3 text-center text-sm font-medium text-slate-600">
                                        {{ $kecamatan->kelurahan_count ?? $kecamatan->kelurahan()->count() }}
                                    </td>
                                    <td class="px-3.5 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('admin.kecamatan.index', ['edit_id' => $kecamatan->id, 'search' => $search]) }}" class="text-xs font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                                                Ubah / Hapus
                                            </a>
                                            <!-- Form tersembunyi untuk Dusk test & programatik delete -->
                                            <form id="delete-form-{{ $kecamatan->id }}" method="POST" action="{{ route('admin.kecamatan.destroy', $kecamatan->id) }}" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3.5 py-6 text-center text-xs text-slate-400">
                                        Tidak ada data kecamatan ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Add Button under Table -->
                <div class="pt-2 flex items-center justify-between">
                    <a href="{{ route('admin.kecamatan.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        <span>+ Tambah kecamatan</span>
                    </a>

                    @if ($kecamatanList->hasPages())
                        <div class="text-xs">
                            {{ $kecamatanList->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Card: Tambah / ubah kecamatan (lg:col-span-5) -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900">Tambah / ubah kecamatan</h2>

                <form
                    action="{{ $editingKecamatan ? route('admin.kecamatan.update', $editingKecamatan->id) : route('admin.kecamatan.store') }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf
                    @if ($editingKecamatan)
                        @method('PUT')
                    @endif

                    <div class="space-y-1">
                        <label for="kode_kecamatan" class="block text-xs font-semibold text-slate-700">
                            Kode kecamatan *
                        </label>
                        <input
                            type="text"
                            id="kode_kecamatan"
                            name="kode_kecamatan"
                            value="{{ old('kode_kecamatan', $editingKecamatan?->kode_kecamatan ?? '') }}"
                            placeholder="32.73.03"
                            maxlength="10"
                            required
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 @error('kode_kecamatan') border-rose-400 @enderror"
                        >
                        @error('kode_kecamatan')
                            <p class="text-[11px] font-medium text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="nama_kecamatan" class="block text-xs font-semibold text-slate-700">
                            Nama kecamatan *
                        </label>
                        <input
                            type="text"
                            id="nama_kecamatan"
                            name="nama_kecamatan"
                            value="{{ old('nama_kecamatan', $editingKecamatan?->nama_kecamatan ?? '') }}"
                            placeholder="Antapani"
                            maxlength="100"
                            required
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 @error('nama_kecamatan') border-rose-400 @enderror"
                        >
                        @error('nama_kecamatan')
                            <p class="text-[11px] font-medium text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            dusk="submit-btn"
                            class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors"
                        >
                            Simpan kecamatan
                        </button>
                    </div>
                </form>

                <!-- Warning box & Delete Action (Shown when editing or available) -->
                @if ($editingKecamatan)
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div class="rounded-xl bg-rose-50 border border-rose-100 p-3.5 text-xs text-rose-700 font-medium leading-relaxed">
                            Hapus kecamatan? Data yang masih memiliki kelurahan atau laporan tidak dapat dihapus.
                        </div>

                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('admin.kecamatan.index') }}"
                                class="px-5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition-colors"
                            >
                                Batal
                            </a>
                            <form action="{{ route('admin.kecamatan.destroy', $editingKecamatan->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
                                >
                                    Hapus
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
