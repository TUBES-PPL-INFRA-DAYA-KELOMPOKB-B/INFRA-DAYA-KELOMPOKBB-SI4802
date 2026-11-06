<x-admin-layout title="Master Data Kecamatan" header="Kelola Data Kecamatan">
    <div class="space-y-6">
        <!-- Top Tab Bar -->
        <div class="flex items-center gap-3 border-b border-slate-200 pb-3">
            <a href="{{ route('admin.kecamatan.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white shadow-sm">
                <span>Data Kecamatan</span>
            </a>
            <a href="{{ route('admin.kelurahan.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-brand-navy transition-colors border border-slate-200">
                <span>Data Kelurahan</span>
            </a>
        </div>

        <!-- Filter & Action Card -->
        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Search Form -->
                <form action="{{ route('admin.kecamatan.index') }}" method="GET" class="flex items-center gap-2 max-w-md w-full">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari nama atau kode kecamatan..."
                            class="w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20"
                        >
                    </div>
                    <x-button variant="secondary" size="md" type="submit">
                        Cari
                    </x-button>
                    @if ($search)
                        <a href="{{ route('admin.kecamatan.index') }}" class="text-xs text-slate-500 hover:text-rose-600 underline">Reset</a>
                    @endif
                </form>

                <!-- Add Button -->
                <x-button variant="amber" :href="route('admin.kecamatan.create')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Kecamatan</span>
                </x-button>
            </div>
        </x-card>

        <!-- Table Card -->
        <x-card>
            @if ($kecamatanList->isEmpty())
                <x-empty-state
                    title="Tidak Ada Data Kecamatan"
                    description="{{ $search ? 'Tidak ditemukan kecamatan dengan kata kunci pencarian Anda.' : 'Belum ada data kecamatan yang tersimpan.' }}"
                    action-text="Tambah Kecamatan Baru"
                    :action-url="route('admin.kecamatan.create')"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3.5 w-12 text-center">#</th>
                                <th class="px-4 py-3.5">Kode Kecamatan</th>
                                <th class="px-4 py-3.5">Nama Kecamatan</th>
                                <th class="px-4 py-3.5 text-center">Kelurahan</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($kecamatanList as $index => $kecamatan)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-4 py-3.5 text-center font-semibold text-slate-400">
                                        {{ $kecamatanList->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-xs font-bold text-brand-navy">
                                        {{ $kecamatan->kode_kecamatan }}
                                    </td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-900">
                                        {{ $kecamatan->nama_kecamatan }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <a href="{{ route('admin.kelurahan.index', ['kecamatan_id' => $kecamatan->id]) }}" class="inline-flex items-center gap-1 rounded-full bg-brand-bg px-2.5 py-1 text-xs font-semibold text-brand-teal hover:bg-brand-teal hover:text-white transition-colors">
                                            <span>{{ $kecamatan->kelurahan_count }}</span>
                                            <span>Kelurahan &rarr;</span>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.kecamatan.edit', $kecamatan->id) }}" class="rounded-lg p-1.5 text-slate-600 hover:bg-slate-100 hover:text-brand-navy" title="Ubah">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <!-- Delete Button triggers modal -->
                                            <button
                                                type="button"
                                                @click="$dispatch('open-modal', 'delete-kecamatan-{{ $kecamatan->id }}')"
                                                class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                                title="Hapus"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>

                                        <!-- Modal Konfirmasi Hapus -->
                                        <x-modal-confirm
                                            id="delete-kecamatan-{{ $kecamatan->id }}"
                                            title="Hapus Kecamatan {{ $kecamatan->nama_kecamatan }}"
                                            message="{{ $kecamatan->kelurahan_count > 0 ? 'Peringatan: Kecamatan ' . $kecamatan->nama_kecamatan . ' masih memiliki ' . $kecamatan->kelurahan_count . ' kelurahan. Penghapusan akan ditolak sistem sebelum seluruh kelurahan dihapus/dipindahkan.' : 'Apakah Anda yakin ingin menghapus data Kecamatan ' . $kecamatan->nama_kecamatan . ' (' . $kecamatan->kode_kecamatan . ')?' }}"
                                            confirm-text="Ya, Hapus Sekarang"
                                            confirm-variant="danger"
                                            method="DELETE"
                                            :action="route('admin.kecamatan.destroy', $kecamatan->id)"
                                        />
                                        {{-- Form tersembunyi untuk Dusk test --}}
                                        <form id="delete-form-{{ $kecamatan->id }}" method="POST" action="{{ route('admin.kecamatan.destroy', $kecamatan->id) }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-4">
                    {{ $kecamatanList->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-admin-layout>
