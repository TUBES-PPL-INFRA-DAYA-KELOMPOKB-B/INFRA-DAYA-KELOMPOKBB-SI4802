<x-warga-layout title="Detail Laporan Kerusakan">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs font-bold text-[#0E7C86] bg-teal-50 px-2.5 py-1 rounded-md border border-teal-100">
                        {{ $laporan->nomor_tiket }}
                    </span>
                    @if($laporan->status === 'menunggu_verifikasi')
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Menunggu Verifikasi</span>
                    @elseif($laporan->status === 'diproses')
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Sedang Diproses</span>
                    @elseif($laporan->status === 'selesai')
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Tuntas Selesai</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">Ditolak</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">{{ $laporan->judul }}</h1>
                <p class="text-xs text-slate-400 mt-1">Diajukan pada {{ $laporan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</p>
            </div>

            <!-- Action buttons (Ubah / Batal jika status menunggu) -->
            <div class="flex items-center gap-2">
                <a href="{{ route('warga.laporan.saya') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                    Kembali
                </a>
                @if($laporan->canBeModifiedByWarga())
                    <a href="{{ route('warga.laporan.edit', $laporan->id) }}" class="px-4 py-2 bg-[#0E7C86] hover:bg-[#0b636b] text-white rounded-xl text-sm font-medium transition-colors">
                        Ubah Laporan
                    </a>
                    <button type="button" onclick="confirmCancel()" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-sm font-medium transition-colors border border-rose-200">
                        Batalkan Laporan
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Rincian & Foto Bukti -->
            <div class="lg:col-span-2 space-y-6">
                <x-card class="p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h2 class="text-base font-bold text-slate-800">Foto Bukti Kerusakan</h2>
                        @if($laporan->foto_bukti_sebelum)
                            <a href="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Buka Ukuran Asli</span>
                            </a>
                        @endif
                    </div>
                    <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-900 flex items-center justify-center max-h-96 relative group cursor-zoom-in">
                        @if($laporan->foto_bukti_sebelum)
                            <a href="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="block w-full">
                                <img src="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" alt="{{ $laporan->judul }}" class="w-full h-auto object-contain max-h-96 transition-transform duration-300 group-hover:scale-101">
                            </a>
                        @else
                            <div class="py-12 text-slate-400 text-xs">Tidak ada foto bukti</div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Deskripsi Kondisi Lapangan</h3>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $laporan->deskripsi_kondisi }}</p>
                    </div>

                    @if($laporan->alasan_penolakan)
                        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl space-y-1">
                            <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider">Alasan Penolakan</h4>
                            <p class="text-sm text-rose-700">{{ $laporan->alasan_penolakan }}</p>
                        </div>
                    @endif
                </x-card>

                @if($laporan->status === 'selesai')
                    <!-- Dokumentasi Hasil Perbaikan Lapangan -->
                    <x-card class="p-6 space-y-4 border-2 border-emerald-100">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                <h2 class="text-base font-bold text-slate-800">Hasil Dokumentasi Perbaikan</h2>
                            </div>
                            @if($laporan->selesai_at)
                                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                                    Tuntas: {{ $laporan->selesai_at->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 block mb-1.5 w-fit">Sebelum</span>
                                <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 h-48">
                                    @if($laporan->foto_bukti_sebelum)
                                        <a href="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full cursor-zoom-in">
                                            <img src="{{ asset('storage/' . $laporan->foto_bukti_sebelum) }}" alt="Foto Sebelum" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 block mb-1.5 w-fit">Setelah Perbaikan</span>
                                <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 h-48">
                                    @if($laporan->foto_bukti_sesudah)
                                        <a href="{{ asset('storage/' . $laporan->foto_bukti_sesudah) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full cursor-zoom-in">
                                            <img src="{{ asset('storage/' . $laporan->foto_bukti_sesudah) }}" alt="Foto Sesudah" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                        </a>
                                    @else
                                        <div class="h-full flex items-center justify-center text-xs text-slate-400">Belum ada foto sesudah</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($laporan->catatan_perbaikan)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                <strong class="block text-slate-900 mb-1">Catatan Teknis Petugas:</strong>
                                {{ $laporan->catatan_perbaikan }}
                            </div>
                        @endif
                    </x-card>

                    <!-- MODUL EVALUASI & UMPAN BALIK WARGA -->
                    @if($laporan->umpanBalik)
                        <!-- Tampilan Umpan Balik yang Sudah Dikirim -->
                        <x-card class="p-6 space-y-4 border-2 border-amber-200 bg-gradient-to-br from-amber-50/40 via-white to-amber-50/20">
                            <div class="flex items-center justify-between border-b border-amber-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-amber-500 text-white text-xs font-black">&check;</span>
                                    <h2 class="text-base font-bold text-slate-900">Ulasan &amp; Evaluasi Anda</h2>
                                </div>
                                <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full">
                                    {{ $laporan->umpanBalik->created_at->translatedFormat('d M Y') }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center text-amber-400 text-lg">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $laporan->umpanBalik->rating)
                                                <svg class="h-6 w-6 fill-current text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @else
                                                <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-sm font-black text-slate-800">
                                        {{ $laporan->umpanBalik->rating }} dari 5 Bintang
                                    </span>
                                </div>

                                @if($laporan->umpanBalik->komentar)
                                    <div class="p-3 bg-white rounded-xl border border-amber-200/70 text-xs sm:text-sm text-slate-700 italic">
                                        "{{ $laporan->umpanBalik->komentar }}"
                                    </div>
                                @endif

                                @if($laporan->umpanBalik->kecepatan_penanganan || $laporan->umpanBalik->kualitas_perbaikan)
                                    <div class="grid grid-cols-2 gap-3 pt-2 text-xs">
                                        @if($laporan->umpanBalik->kecepatan_penanganan)
                                            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                                                <span class="text-slate-400 block text-[11px]">Kecepatan Respon</span>
                                                <span class="font-bold text-slate-800">{{ $laporan->umpanBalik->kecepatan_penanganan }} / 5 Bintang</span>
                                            </div>
                                        @endif
                                        @if($laporan->umpanBalik->kualitas_perbaikan)
                                            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                                                <span class="text-slate-400 block text-[11px]">Kualitas Perbaikan</span>
                                                <span class="font-bold text-slate-800">{{ $laporan->umpanBalik->kualitas_perbaikan }} / 5 Bintang</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-card>
                    @else
                        <!-- Form Input Evaluasi & Rating Baru -->
                        <x-card class="p-6 space-y-4 border-2 border-[#0E7C86]/30 bg-gradient-to-br from-teal-50/30 via-white to-slate-50">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-[#0E7C86] text-white text-xs font-bold">&starf;</span>
                                    <h2 class="text-base font-bold text-slate-900">Evaluasi Kepuasan Perbaikan</h2>
                                </div>
                                <p class="text-xs text-slate-500">
                                    Bagaimana kepuasan Anda terhadap hasil penanganan perbaikan fasilitas ini? Masukan Anda sangat berharga bagi peningkatan layanan DSDABM Kota Bandung.
                                </p>
                            </div>

                            <form action="{{ route('warga.laporan.umpan-balik', $laporan->id) }}" method="POST" class="space-y-4">
                                @csrf

                                <!-- Rating Bintang Utama (1 - 5) -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-2">
                                        Tingkat Kepuasan Keseluruhan <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        @for($star = 1; $star <= 5; $star++)
                                            <label for="star_{{ $star }}" dusk="rating-star-{{ $star }}" class="cursor-pointer group flex flex-col items-center">
                                                <input
                                                    type="radio"
                                                    id="star_{{ $star }}"
                                                    name="rating"
                                                    value="{{ $star }}"
                                                    {{ (old('rating') == $star || ($star === 5 && !old('rating'))) ? 'checked' : '' }}
                                                    class="peer sr-only"
                                                >
                                                <div class="h-10 w-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 peer-checked:bg-brand-amber peer-checked:border-amber-400 peer-checked:text-slate-950 peer-checked:shadow-md transition">
                                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-500 mt-1">{{ $star }} &starf;</span>
                                            </label>
                                        @endfor
                                    </div>
                                    @error('rating')
                                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Sub Aspek (Kecepatan & Kualitas) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label for="kecepatan_penanganan" class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kecepatan Penanganan (Opsional)
                                        </label>
                                        <select
                                            name="kecepatan_penanganan"
                                            id="kecepatan_penanganan"
                                            dusk="select-kecepatan"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] py-2 px-3"
                                        >
                                            <option value="">-- Pilih Nilai --</option>
                                            <option value="5" {{ old('kecepatan_penanganan') == 5 ? 'selected' : '' }}>5 - Sangat Cepat</option>
                                            <option value="4" {{ old('kecepatan_penanganan') == 4 ? 'selected' : '' }}>4 - Cepat</option>
                                            <option value="3" {{ old('kecepatan_penanganan') == 3 ? 'selected' : '' }}>3 - Cukup</option>
                                            <option value="2" {{ old('kecepatan_penanganan') == 2 ? 'selected' : '' }}>2 - Lambat</option>
                                            <option value="1" {{ old('kecepatan_penanganan') == 1 ? 'selected' : '' }}>1 - Sangat Lambat</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="kualitas_perbaikan" class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kualitas Fisik Hasil Perbaikan (Opsional)
                                        </label>
                                        <select
                                            name="kualitas_perbaikan"
                                            id="kualitas_perbaikan"
                                            dusk="select-kualitas"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] py-2 px-3"
                                        >
                                            <option value="">-- Pilih Nilai --</option>
                                            <option value="5" {{ old('kualitas_perbaikan') == 5 ? 'selected' : '' }}>5 - Sangat Rapi & Kokoh</option>
                                            <option value="4" {{ old('kualitas_perbaikan') == 4 ? 'selected' : '' }}>4 - Rapi</option>
                                            <option value="3" {{ old('kualitas_perbaikan') == 3 ? 'selected' : '' }}>3 - Cukup</option>
                                            <option value="2" {{ old('kualitas_perbaikan') == 2 ? 'selected' : '' }}>2 - Kurang Rapi</option>
                                            <option value="1" {{ old('kualitas_perbaikan') == 1 ? 'selected' : '' }}>1 - Sangat Kurang</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Textarea Ulasan / Saran -->
                                <div>
                                    <label for="komentar" class="block text-xs font-bold text-slate-700 mb-1">
                                        Komentar atau Saran (Opsional)
                                    </label>
                                    <textarea
                                        name="komentar"
                                        id="komentar"
                                        dusk="input-komentar"
                                        rows="3"
                                        placeholder="Tuliskan pengalaman atau saran Anda terhadap perbaikan fisik ini..."
                                        class="w-full text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-[#0E7C86] p-3"
                                    >{{ old('komentar') }}</textarea>
                                    @error('komentar')
                                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        dusk="btn-submit-umpan-balik"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white text-xs sm:text-sm font-bold shadow-md shadow-teal-700/20 transition cursor-pointer"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Kirim Evaluasi Kepuasan</span>
                                    </button>
                                </div>
                            </form>
                        </x-card>
                    @endif
                @endif


                <!-- Peta Lokasi -->
                <x-card class="p-6 space-y-3">
                    <h2 class="text-base font-bold text-slate-800">Titik Koordinat Lokasi</h2>
                    <div id="detailMap" class="w-full h-64 rounded-xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>
                    <div class="text-xs font-mono text-slate-500 flex items-center justify-between">
                        <span>Koordinat: {{ $laporan->latitude }}, {{ $laporan->longitude }}</span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $laporan->latitude }},{{ $laporan->longitude }}" target="_blank" class="text-[#0E7C86] font-sans font-semibold hover:underline">
                            Buka Google Maps &rarr;
                        </a>
                    </div>
                </x-card>
            </div>

            <!-- Kolom Kanan: Status Timeline & Metadata -->
            <div class="space-y-6">
                <x-card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2">Informasi Wilayah</h2>
                    <div class="text-xs space-y-2.5">
                        <div>
                            <span class="text-slate-400 block">Kategori Infrastruktur:</span>
                            <span class="font-semibold text-slate-800 text-sm">{{ $laporan->kategori->nama_kategori }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Kecamatan:</span>
                            <span class="font-semibold text-slate-800">{{ $laporan->kelurahan->kecamatan->nama_kecamatan }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Kelurahan:</span>
                            <span class="font-semibold text-slate-800">{{ $laporan->kelurahan->nama_kelurahan }}</span>
                        </div>
                    </div>
                </x-card>

                <!-- Timeline Riwayat Status -->
                <x-card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2">Jejak Audit Status</h2>
                    <div class="space-y-4">
                        @foreach($laporan->riwayatStatus as $riwayat)
                            <div class="relative pl-6 pb-4 border-l-2 border-[#0E7C86]/30 last:border-0 last:pb-0">
                                <span class="absolute -left-1.5 top-0.5 h-3 w-3 rounded-full bg-[#0E7C86] ring-4 ring-teal-50"></span>
                                <div class="text-xs font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $riwayat->status) }}</div>
                                <div class="text-xs text-slate-600 mt-0.5">{{ $riwayat->keterangan }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    @if($laporan->canBeModifiedByWarga())
        <!-- Modal Konfirmasi Batal -->
        <div id="cancelModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Batalkan Laporan Ini?</h3>
                    <p class="text-sm text-slate-500 mt-1">Laporan kerusakan <strong>{{ $laporan->nomor_tiket }}</strong> akan dihapus permanen dari sistem.</p>
                </div>
                <form method="POST" action="{{ route('warga.laporan.destroy', $laporan->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeCancelModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                            Kembali
                        </button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition-colors">
                            Ya, Batalkan Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function confirmCancel() {
                document.getElementById('cancelModal').classList.remove('hidden');
            }
            function closeCancelModal() {
                document.getElementById('cancelModal').classList.add('hidden');
            }
        </script>
    @endif

    <!-- Leaflet JS Map Preview -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const lat = {{ $laporan->latitude }};
            const lng = {{ $laporan->longitude }};
            const map = L.map('detailMap').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap | INFRA-DAYA'
            }).addTo(map);

            const popupContent = document.createElement('div');
            const b = document.createElement('b');
            b.textContent = @js($laporan->nomor_tiket);
            popupContent.appendChild(b);
            popupContent.appendChild(document.createElement('br'));
            popupContent.appendChild(document.createTextNode(@js($laporan->judul)));

            L.marker([lat, lng]).addTo(map)
                .bindPopup(popupContent)
                .openPopup();
        });
    </script>
</x-warga-layout>
