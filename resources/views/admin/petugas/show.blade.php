<x-admin-layout title="Detail Petugas — {{ $petugas->name }}">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('admin.petugas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0E7C86] hover:text-[#0b636b] mb-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Daftar Petugas</span>
                </a>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Detail Profil Petugas Lapangan</h1>
                <p class="text-sm text-slate-500 mt-1">Tinjau informasi identitas, status operasional, dan riwayat penugasan lapangan.</p>
            </div>
            <div class="flex items-center gap-2.5" x-data="{ openModal: false }">
                <!-- Edit Button -->
                <a
                    href="{{ route('admin.petugas.edit', $petugas->id) }}"
                    dusk="btn-edit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-sm transition-all"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Identitas</span>
                </a>

                <!-- Toggle Status Button with Modal (AC17-13, AC17-14) -->
                <button
                    type="button"
                    @click="openModal = true"
                    dusk="btn-toggle-status"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl shadow-sm transition-all text-white {{ $petugas->is_active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                >
                    @if($petugas->is_active)
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>Nonaktifkan Petugas</span>
                    @else
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Aktifkan Petugas</span>
                    @endif
                </button>

                <!-- Modal Konfirmasi Status -->
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
                                        {{ $petugas->is_active ? 'Konfirmasi Nonaktivasi Akun' : 'Konfirmasi Aktivasi Akun' }}
                                    </h3>
                                    <p class="text-xs text-slate-500">{{ $petugas->name }}</p>
                                </div>
                            </div>

                            <div class="text-sm text-slate-600 mb-6 space-y-2">
                                @if($petugas->is_active)
                                    <p>Apakah Anda yakin ingin menonaktifkan akun petugas ini? Petugas nonaktif tidak dapat melakukan login dan tidak dapat menerima disposisi tugas baru.</p>
                                    <p class="text-xs text-slate-500 italic">Catatan: Sistem akan menolak nonaktivasi jika petugas masih memiliki tugas yang belum selesai.</p>
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
                                    dusk="btn-confirm-toggle"
                                    class="px-4 py-2 text-sm font-bold text-white rounded-xl shadow-sm transition {{ $petugas->is_active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                                >
                                    {{ $petugas->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan Kembali' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Message & Blocking Tasks -->
        <x-flash />

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

        <!-- Identity Card (AC17-21: No password or hash) -->
        <x-card class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-[#0E7C86]/10 text-[#0E7C86] flex items-center justify-center font-extrabold text-xl border border-[#0E7C86]/20">
                        {{ strtoupper(substr($petugas->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-xl font-bold text-[#1F3A5F]" dusk="detail-name">{{ $petugas->name }}</h2>
                            @if ($petugas->is_active)
                                <span dusk="detail-status" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span dusk="detail-status" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Petugas Teknis Lapangan DSDABM Kota Bandung</p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">ID Petugas</span>
                    <p class="text-base font-mono font-bold text-slate-700">#{{ $petugas->id }}</p>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Alamat Email</span>
                    <p class="text-sm font-semibold text-slate-800 mt-1" dusk="detail-email">{{ $petugas->email }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Digunakan untuk login ke sistem</p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor Telepon / WhatsApp</span>
                    <p class="text-sm font-semibold font-mono text-slate-800 mt-1" dusk="detail-phone">{{ $petugas->phone ?: '-' }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kontak penugasan operasional</p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terdaftar Sejak</span>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $petugas->created_at ? $petugas->created_at->translatedFormat('d F Y, H:i') : '-' }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pembaruan terakhir: {{ $petugas->updated_at ? $petugas->updated_at->diffForHumans() : '-' }}</p>
                </div>
            </div>
        </x-card>

        <!-- Assignment History Section (AC17-18) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-[#1F3A5F]">Riwayat Penugasan Lapangan</h3>
                    <p class="text-xs text-slate-500">Seluruh riwayat penugasan dan Surat Perintah Tugas (SPT) yang pernah ditugaskan kepada petugas ini.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                    Total: {{ $petugas->penugasanPetugas->count() }} SPT
                </span>
            </div>

            <x-card class="overflow-hidden border border-slate-200/80">
                @if ($petugas->penugasanPetugas->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" dusk="table-riwayat-penugasan">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-4">Nomor SPT</th>
                                    <th class="py-3 px-4">Laporan Kerusakan</th>
                                    <th class="py-3 px-4">Wilayah & Kategori</th>
                                    <th class="py-3 px-4">Tanggal Disposisi</th>
                                    <th class="py-3 px-4 text-center">Status Tugas</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                @foreach ($petugas->penugasanPetugas as $penugasan)
                                    <tr class="hover:bg-slate-50/50 transition-colors" dusk="row-penugasan-{{ $penugasan->id }}">
                                        <!-- SPT Number -->
                                        <td class="py-3.5 px-4 font-mono font-bold text-xs text-[#0E7C86]">
                                            {{ $penugasan->nomor_surat_tugas }}
                                        </td>

                                        <!-- Report Info -->
                                        <td class="py-3.5 px-4">
                                            @if($penugasan->laporan)
                                                <div class="font-bold text-[#1F3A5F]">
                                                    {{ $penugasan->laporan->judul }}
                                                </div>
                                                <div class="text-xs font-mono text-slate-400 mt-0.5">
                                                    Tiket: {{ $penugasan->laporan->nomor_tiket }}
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic">Data laporan tidak ditemukan</span>
                                            @endif
                                        </td>

                                        <!-- Location & Category -->
                                        <td class="py-3.5 px-4 text-xs text-slate-600">
                                            <div>{{ $penugasan->laporan?->kelurahan?->name ?? '-' }}, {{ $penugasan->laporan?->kelurahan?->kecamatan?->name ?? '-' }}</div>
                                            <div class="text-slate-400 mt-0.5">{{ $penugasan->laporan?->kategori?->nama_kategori ?? '-' }}</div>
                                        </td>

                                        <!-- Date -->
                                        <td class="py-3.5 px-4 text-xs text-slate-600">
                                            {{ $penugasan->tanggal_disposisi ? \Illuminate\Support\Carbon::parse($penugasan->tanggal_disposisi)->translatedFormat('d M Y') : '-' }}
                                        </td>

                                        <!-- Task Status -->
                                        <td class="py-3.5 px-4 text-center">
                                            @php
                                                $statusClasses = [
                                                    'ditugaskan' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                    'diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                ];
                                                $badgeClass = $statusClasses[$penugasan->status_penugasan] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }} uppercase">
                                                {{ $penugasan->status_penugasan }}
                                            </span>
                                        </td>

                                        <!-- Action -->
                                        <td class="py-3.5 px-4 text-right">
                                            <a
                                                href="{{ route('admin.disposisi.show', $penugasan->id) }}"
                                                class="text-xs font-bold text-[#0E7C86] hover:text-[#0b636b] hover:underline"
                                            >
                                                Lihat SPT &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center" dusk="empty-riwayat">
                        <p class="text-sm text-slate-500">Petugas ini belum memiliki riwayat penugasan lapangan.</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-admin-layout>

