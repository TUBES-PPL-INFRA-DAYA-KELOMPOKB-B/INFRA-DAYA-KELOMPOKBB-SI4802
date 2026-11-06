<x-admin-layout title="Terbitkan Surat Perintah Tugas (SPT)">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('admin.disposisi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#0E7C86] transition mb-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar Disposisi</span>
                </a>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Terbitkan Surat Perintah Tugas (SPT)</h1>
                <p class="text-sm text-slate-500 mt-0.5">Berikan penugasan resmi kepada petugas teknis lapangan untuk perbaikan kerusakan.</p>
            </div>
        </div>

        <!-- Flash Message -->
        <x-flash />

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-bold mb-1">Terjadi kesalahan pada input data:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.disposisi.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf

            <!-- Form Utama (2 Kolom) -->
            <div class="lg:col-span-2 space-y-6">
                <x-card class="p-6 space-y-5">
                    <h2 class="text-base font-bold text-[#1F3A5F] border-b border-slate-100 pb-3 flex items-center gap-2">
                        <svg class="h-5 w-5 text-[#0E7C86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Parameter Penugasan Lapangan</span>
                    </h2>

                    <!-- Pilihan Laporan -->
                    <div>
                        <label for="laporan_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Laporan Kerusakan yang Didisposisikan <span class="text-rose-500">*</span>
                        </label>
                        @if($selectedLaporan)
                            <input type="hidden" name="laporan_id" value="{{ $selectedLaporan->id }}">
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-extrabold text-[#1F3A5F]">{{ $selectedLaporan->nomor_tiket }}</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $selectedLaporan->prioritas === 'tinggi' ? 'bg-rose-100 text-rose-700' : ($selectedLaporan->prioritas === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            Prioritas {{ ucfirst($selectedLaporan->prioritas) }}
                                        </span>
                                    </div>
                                    <p class="font-semibold text-sm text-slate-800">{{ $selectedLaporan->judul }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Kel. {{ $selectedLaporan->kelurahan->nama_kelurahan ?? '-' }}, Kec. {{ $selectedLaporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.disposisi.create') }}" class="text-xs font-semibold text-[#0E7C86] hover:underline">
                                    Ganti Laporan
                                </a>
                            </div>
                        @else
                            <select
                                name="laporan_id"
                                id="laporan_id"
                                dusk="select-laporan-id"
                                required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                            >
                                <option value="">-- Pilih Laporan Disetujui (Siap Ditugaskan) --</option>
                                @foreach($laporanList as $lap)
                                    <option value="{{ $lap->id }}" {{ old('laporan_id') == $lap->id ? 'selected' : '' }}>
                                        [{{ $lap->nomor_tiket }}] {{ $lap->judul }} — Kel. {{ $lap->kelurahan->nama_kelurahan ?? '-' }} (Prioritas: {{ ucfirst($lap->prioritas) }})
                                    </option>
                                @endforeach
                            </select>
                            @if($laporanList->isEmpty())
                                <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Saat ini belum ada laporan berstatus Disetujui (Diproses) yang menunggu penugasan. Verifikasi laporan terlebih dahulu di menu Verifikasi.
                                </p>
                            @endif
                        @endif
                        @error('laporan_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pilihan Petugas Lapangan -->
                    <div>
                        <label for="petugas_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Petugas Lapangan DSDABM <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="petugas_id"
                            id="petugas_id"
                            dusk="select-petugas-id"
                            required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >
                            <option value="">-- Pilih Petugas yang Ditugaskan --</option>
                            @foreach($petugasList as $petugas)
                                <option value="{{ $petugas->id }}" {{ old('petugas_id') == $petugas->id ? 'selected' : '' }}>
                                    {{ $petugas->name }} {{ $petugas->phone ? "({$petugas->phone})" : "- {$petugas->email}" }}
                                </option>
                            @endforeach
                        </select>
                        @error('petugas_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Disposisi & Tenggat Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_disposisi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Tanggal Disposisi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="tanggal_disposisi"
                                id="tanggal_disposisi"
                                dusk="input-tanggal-disposisi"
                                value="{{ old('tanggal_disposisi', now()->toDateString()) }}"
                                required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                            >
                            @error('tanggal_disposisi')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tenggat_waktu" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Tenggat Waktu Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="tenggat_waktu"
                                id="tenggat_waktu"
                                dusk="input-tenggat-waktu"
                                value="{{ old('tenggat_waktu', now()->addDays(3)->toDateString()) }}"
                                min="{{ now()->toDateString() }}"
                                required
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                            >
                            @error('tenggat_waktu')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Catatan Instruksi Teknis -->
                    <div>
                        <label for="catatan_instruksi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Catatan Instruksi Teknis / Arahan Lapangan <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="catatan_instruksi"
                            id="catatan_instruksi"
                            dusk="input-catatan-instruksi"
                            rows="4"
                            required
                            placeholder="Contoh: Lakukan survei kedalaman lubang jalan, pasang rambu peringatan keselamatan kerja, dan lakukan penambalan coldmix sebelum akhir pekan."
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                        >{{ old('catatan_instruksi') }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter. Instruksi ini akan tercetak pada Surat Perintah Tugas resmi (SPT).</p>
                        @error('catatan_instruksi')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a
                            href="{{ route('admin.disposisi.index') }}"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            dusk="btn-submit-disposisi"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white font-bold text-sm shadow-md transition"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Terbitkan Surat Tugas (SPT)</span>
                        </button>
                    </div>
                </x-card>
            </div>

            <!-- Panel Informasi Pendukung (1 Kolom) -->
            <div class="space-y-6">
                @if($selectedLaporan)
                    <x-card class="p-5 space-y-4">
                        <h3 class="text-xs font-bold tracking-wider text-[#1F3A5F] border-b border-slate-100 pb-2">
                            Ringkasan Laporan Terpilih
                        </h3>

                        @if($selectedLaporan->foto_bukti_sebelum)
                            <div class="rounded-xl overflow-hidden border border-slate-200">
                                <img
                                    src="{{ asset('storage/' . $selectedLaporan->foto_bukti_sebelum) }}"
                                    alt="Foto Bukti Kerusakan"
                                    class="w-full h-40 object-cover"
                                >
                            </div>
                        @endif

                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Kategori</span>
                                <span class="font-semibold text-slate-700">{{ $selectedLaporan->kategori->nama_kategori ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Deskripsi Kerusakan</span>
                                <p class="text-slate-600 line-clamp-3">{{ $selectedLaporan->deskripsi_kondisi }}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Koordinat Lokasi</span>
                                <span class="font-mono text-slate-700">{{ $selectedLaporan->latitude }}, {{ $selectedLaporan->longitude }}</span>
                            </div>
                        </div>
                    </x-card>
                @endif

                <x-card class="p-5 bg-gradient-to-br from-slate-50 to-teal-50/20 border-teal-200/50 space-y-3">
                    <div class="flex items-center gap-2 text-[#0E7C86] font-bold text-xs uppercase tracking-wider">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Alur Pasca Penugasan</span>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="h-5 w-5 rounded-full bg-[#0E7C86]/10 text-[#0E7C86] font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                            <span>Surat Perintah Tugas (SPT) digital otomatis diterbitkan dengan nomor seri unik harian.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="h-5 w-5 rounded-full bg-[#0E7C86]/10 text-[#0E7C86] font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                            <span>Dapat langsung dicetak ke format PDF resmi bertanda tangan DSDABM.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="h-5 w-5 rounded-full bg-[#0E7C86]/10 text-[#0E7C86] font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                            <span>Laporan akan masuk ke antrean tugas mobile petugas terkait untuk pelaksanaan lapangan.</span>
                        </li>
                    </ul>
                </x-card>
            </div>
        </form>
    </div>
</x-admin-layout>
