<x-admin-layout title="Kelola Petugas Lapangan">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#0E7C86] mb-1">
                    <span>Admin DSDABM</span>
                    <span>•</span>
                    <span>PBI-17 (IFD-18)</span>
                </div>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Pengelolaan Petugas Lapangan</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola data identitas akun petugas teknis, pantau status keaktifan, dan tinjau riwayat penugasan.</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.petugas.create') }}"
                    dusk="btn-create-petugas"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-bold rounded-xl shadow-sm transition-all"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Petugas Baru</span>
                </a>
            </div>
        </div>

        <!-- Flash Message -->
        <x-flash />

        <!-- Blocking Tasks Alert (AC17-14) -->
        @if (session('blocking_tasks') && count(session('blocking_tasks')) > 0)
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm" dusk="alert-blocking-tasks">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="space-y-1.5 flex-1">
                        <h4 class="text-sm font-bold text-rose-900">Penonaktifan Ditolak — Petugas Masih Memiliki Tugas Berjalan</h4>
                        <p class="text-xs text-rose-700">Petugas tidak dapat dinonaktifkan sebelum seluruh tugas di bawah ini berstatus selesai:</p>
                        <ul class="list-disc list-inside text-xs font-medium space-y-1 mt-2 text-rose-800">
                            @foreach (session('blocking_tasks') as $blockingTask)
                                <li>
                                    <span class="font-bold">[{{ $blockingTask->laporan->nomor_tiket ?? ('SPT #'.$blockingTask->nomor_surat_tugas) }}]</span>
                                    <span>{{ $blockingTask->laporan->judul ?? 'Laporan Kerusakan' }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-200 text-rose-800 ml-1">
                                        {{ $blockingTask->status_penugasan }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <x-card class="p-4 border-l-4 border-l-[#1F3A5F]">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Petugas</p>
                <p class="text-2xl font-extrabold text-[#1F3A5F] mt-1">{{ $metrics['totalPetugas'] }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Petugas lapangan terdaftar</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Petugas Aktif</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $metrics['totalAktif'] }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Dapat menerima disposisi</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-slate-400">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Petugas Nonaktif</p>
                <p class="text-2xl font-extrabold text-slate-600 mt-1">{{ $metrics['totalNonaktif'] }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Akses masuk dinonaktifkan</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sedang Bertugas</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $metrics['totalSedangBertugas'] }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Memiliki SPT aktif</p>
            </x-card>
        </div>

        <!-- Filter & Search Bar (AC17-04) -->
        <x-card class="p-4">
            <form method="GET" action="{{ route('admin.petugas.index') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        dusk="input-search"
                        placeholder="Cari berdasarkan nama atau alamat email petugas..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-700 placeholder-slate-400"
                    >
                </div>

                <div class="w-full md:w-56">
                    <select
                        name="status"
                        dusk="select-status"
                        class="w-full py-2.5 px-3.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-700"
                    >
                        <option value="">Semua Status Akun</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Hanya Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Hanya Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        dusk="btn-filter"
                        class="px-5 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-semibold rounded-xl shadow-sm transition-all"
                    >
                        Terapkan
                    </button>

                    @if(request()->filled('search') || request()->filled('status'))
                        <a
                            href="{{ route('admin.petugas.index') }}"
                            dusk="btn-reset"
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-all"
                            title="Reset Pencarian"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Officer List Table -->
        <x-card class="overflow-hidden border border-slate-200/80">
            @if ($petugasList->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" dusk="table-petugas">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4">Petugas</th>
                                <th class="py-3.5 px-4">Kontak (Email / Telepon)</th>
                                <th class="py-3.5 px-4 text-center">Status Akun</th>
                                <th class="py-3.5 px-4 text-center">Tugas Berjalan</th>
                                <th class="py-3.5 px-4 text-center">Total Tugas</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @foreach ($petugasList as $petugas)
                                <tr class="hover:bg-slate-50/60 transition-colors" dusk="row-petugas-{{ $petugas->id }}">
                                    <!-- Petugas Name & ID -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center font-bold text-xs text-[#1F3A5F] border border-slate-200">
                                                {{ strtoupper(substr($petugas->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.petugas.show', $petugas->id) }}" class="font-bold text-[#1F3A5F] hover:text-[#0E7C86] transition-colors" dusk="link-petugas-{{ $petugas->id }}">
                                                    {{ $petugas->name }}
                                                </a>
                                                <p class="text-xs text-slate-400">ID Petugas: #{{ $petugas->id }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact Info (AC17-03, AC17-09) -->
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <span>{{ $petugas->email }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs font-mono text-slate-500">
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                <span dusk="phone-{{ $petugas->id }}">{{ $petugas->phone ?: '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status (AC17-03) -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($petugas->is_active)
                                            <span dusk="status-badge-{{ $petugas->id }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span dusk="status-badge-{{ $petugas->id }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tugas Berjalan -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($petugas->tugas_aktif > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $petugas->tugas_aktif }} tugas
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Total Tugas -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-xs font-bold text-slate-600">{{ $petugas->total_tugas }}</span>
                                    </td>

                                    <!-- Aksi (AC17-11, AC17-13, AC17-14, AC17-17, AC17-18: NO DELETE) -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5" x-data="{ openModal: false }">
                                            <!-- Detail Button -->
                                            <a
                                                href="{{ route('admin.petugas.show', $petugas->id) }}"
                                                dusk="btn-detail-{{ $petugas->id }}"
                                                class="p-2 text-slate-500 hover:text-[#0E7C86] hover:bg-slate-100 rounded-lg transition-colors"
                                                title="Lihat Detail & Riwayat"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>

                                            <!-- Edit Button -->
                                            <a
                                                href="{{ route('admin.petugas.edit', $petugas->id) }}"
                                                dusk="btn-edit-{{ $petugas->id }}"
                                                class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                                title="Edit Identitas"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>

                                            <!-- Toggle Status Button with Confirmation Modal (AC17-13, AC17-14) -->
                                            <button
                                                type="button"
                                                @click="openModal = true"
                                                dusk="btn-toggle-{{ $petugas->id }}"
                                                class="p-2 {{ $petugas->is_active ? 'text-slate-400 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition-colors"
                                                title="{{ $petugas->is_active ? 'Nonaktifkan Petugas' : 'Aktifkan Petugas' }}"
                                            >
                                                @if($petugas->is_active)
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>

                                            <!-- Confirmation Modal -->
                                            <div
                                                x-show="openModal"
                                                style="display: none;"
                                                class="fixed inset-0 z-50 overflow-y-auto"
                                                aria-modal="true"
                                                role="dialog"
                                            >
                                                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openModal = false"></div>
                                                <div class="flex min-h-screen items-center justify-center p-4">
                                                    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl text-left border border-slate-200">
                                                        <div class="flex items-center gap-3 mb-4">
                                                            <div class="h-10 w-10 rounded-full {{ $petugas->is_active ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }} flex items-center justify-center flex-shrink-0">
                                                                @if($petugas->is_active)
                                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                                @else
                                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h3 class="text-base font-bold text-slate-800">
                                                                    {{ $petugas->is_active ? 'Konfirmasi Nonaktivasi Petugas' : 'Konfirmasi Aktivasi Petugas' }}
                                                                </h3>
                                                                <p class="text-xs text-slate-500">{{ $petugas->name }} ({{ $petugas->email }})</p>
                                                            </div>
                                                        </div>

                                                        <div class="text-sm text-slate-600 mb-6 space-y-2">
                                                            @if($petugas->is_active)
                                                                <p>Apakah Anda yakin ingin menonaktifkan akun petugas ini? Petugas nonaktif tidak dapat melakukan login dan tidak dapat menerima disposisi penugasan baru.</p>
                                                                <p class="text-xs text-slate-500 italic">Catatan: Penonaktifan akan ditolak oleh sistem jika petugas masih memiliki penugasan aktif yang belum selesai.</p>
                                                            @else
                                                                <p>Apakah Anda yakin ingin mengaktifkan kembali akun petugas ini? Petugas akan dapat masuk kembali ke sistem dan siap menerima disposisi penugasan baru.</p>
                                                            @endif
                                                        </div>

                                                        <form method="POST" action="{{ route('admin.petugas.toggle-status', $petugas->id) }}" class="flex items-center justify-end gap-3">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button
                                                                type="button"
                                                                @click="openModal = false"
                                                                class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition"
                                                            >
                                                                Batal
                                                            </button>
                                                            <button
                                                                type="submit"
                                                                dusk="btn-confirm-toggle-{{ $petugas->id }}"
                                                                class="px-4 py-2 text-sm font-bold text-white rounded-xl shadow-sm transition {{ $petugas->is_active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                                                            >
                                                                {{ $petugas->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan Kembali' }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($petugasList->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $petugasList->links() }}
                    </div>
                @endif
            @else
                <!-- Empty States (AC17-05) -->
                <div class="p-12 text-center" dusk="empty-state">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>

                    @if (! $hasAnyPetugas)
                        <h3 class="text-base font-bold text-[#1F3A5F]">Belum ada petugas lapangan</h3>
                        <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1">Sistem belum memiliki data petugas lapangan yang terdaftar. Tambahkan akun petugas untuk memulai penugasan disposisi.</p>
                        <div class="mt-5">
                            <a href="{{ route('admin.petugas.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-semibold rounded-xl transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Petugas Sekarang</span>
                            </a>
                        </div>
                    @else
                        <h3 class="text-base font-bold text-[#1F3A5F]">Petugas tidak ditemukan</h3>
                        <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1">Tidak ada data petugas yang cocok dengan kata kunci atau filter status yang Anda pilih.</p>
                        <div class="mt-5">
                            <a href="{{ route('admin.petugas.index') }}" dusk="btn-empty-reset" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                                <span>Tampilkan Semua Petugas</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </x-card>
    </div>
</x-admin-layout>

