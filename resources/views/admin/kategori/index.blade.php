<x-admin-layout title="Kelola Kategori Infrastruktur">
    <div class="space-y-6">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Kategori Infrastruktur</h1>
                <p class="text-sm text-slate-500 mt-1">Master kategori pelaporan kerusakan publik dan bobot prioritas penanganan (SDG 9).</p>
            </div>
            <div>
                <a href="{{ route('admin.kategori.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white font-medium text-sm transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kategori
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar (PBI-12) -->
        <x-card class="p-4">
            <x-filter-kategori
                :action="route('admin.kategori.index')"
                :search="$search"
                :keparahan="$keparahan"
            />
        </x-card>

        <!-- Tabel Kategori -->
        <x-card class="overflow-hidden p-0">
            @if($kategoriList->isEmpty())
                <div class="py-12 text-center">
                    <svg class="h-12 w-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Data kategori tidak ditemukan.</p>
                    @if($search || $keparahan)
                        <p class="text-xs text-slate-500 mt-1">Tidak ada data yang sesuai dengan kriteria pencarian atau filter yang dipilih.</p>
                        <div class="mt-4">
                            <a href="{{ route('admin.kategori.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                                Reset Seluruh Filter
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Nama Kategori</th>
                                <th class="px-6 py-3.5">Deskripsi Masalah</th>
                                <th class="px-6 py-3.5">Tingkat Keparahan Default</th>
                                <th class="px-6 py-3.5 text-center">Jumlah Laporan</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($kategoriList as $kategori)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $kategori->nama_kategori }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500 max-w-md">
                                        {{ $kategori->deskripsi ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $tingkat = strtolower($kategori->tingkat_keparahan_default ?? $kategori->prioritas_label);
                                        @endphp
                                        @if($tingkat === 'kritis' || $kategori->bobot_prioritas === 4)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                                <span class="h-2 w-2 rounded-full bg-rose-600 animate-pulse"></span>
                                                Kritis (Bobot 4)
                                            </span>
                                        @elseif($tingkat === 'tinggi' || $kategori->bobot_prioritas === 3)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                Tinggi (Bobot 3)
                                            </span>
                                        @elseif($tingkat === 'sedang' || $kategori->bobot_prioritas === 2)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Sedang (Bobot 2)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                Rendah (Bobot 1)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ ($kategori->laporan_count ?? 0) > 0 ? 'bg-teal-50 text-[#0E7C86] border border-teal-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                            {{ $kategori->laporan_count ?? 0 }} Laporan
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.kategori.edit', $kategori->id) }}" class="p-1.5 text-slate-400 hover:text-[#0E7C86] rounded-lg hover:bg-slate-100 transition-colors" title="Ubah Kategori">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button
                                                type="button"
                                                onclick="confirmDelete('{{ route('admin.kategori.destroy', $kategori->id) }}', '{{ $kategori->nama_kategori }}')"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition-colors"
                                                title="Hapus Kategori"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($kategoriList->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $kategoriList->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
            <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Konfirmasi Hapus Kategori</h3>
                <p class="text-sm text-slate-500 mt-1">Apakah Anda yakin ingin menghapus kategori <span id="deleteKategoriName" class="font-semibold text-slate-800"></span>? Pastikan tidak ada laporan yang bergantung pada kategori ini.</p>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDelete(url, name) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteKategoriName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</x-admin-layout>
