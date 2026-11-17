<x-admin-layout title="Pratinjau Surat Tugas — {{ $disposisi->nomor_surat_tugas }}">
    <div class="space-y-6">
        <!-- Top Toolbar & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('admin.surat-tugas.index') }}" dusk="btn-kembali" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0E7C86] hover:text-[#0b636b] transition mb-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Daftar Surat Tugas</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Pratinjau Surat Perintah Tugas</h1>
                    <span class="font-mono text-sm font-bold px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-700">
                        {{ $disposisi->nomor_surat_tugas }}
                    </span>
                </div>
            </div>

            <!-- Action Toolbar (AC18-11, AC18-15) -->
            <div class="flex items-center gap-3">
                <!-- Cetak Dialog Browser -->
                <a
                    href="{{ route('admin.surat-tugas.cetak', $disposisi->id) }}"
                    target="_blank"
                    dusk="btn-cetak-surat"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl shadow-sm transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Surat (Browser)</span>
                </a>

                <!-- Unduh PDF A4 -->
                <a
                    href="{{ route('admin.surat-tugas.pdf', $disposisi->id) }}"
                    dusk="btn-unduh-pdf"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-bold rounded-xl shadow-sm transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Unduh PDF (A4)</span>
                </a>
            </div>
        </div>

        <x-flash />

        <!-- Historical Notice if Officer Inactive (AC18-22) -->
        @if($disposisi->petugas && ! $disposisi->petugas->is_active)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900 text-xs shadow-sm flex items-start gap-3">
                <svg class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <span class="font-bold">Surat Tugas Historis:</span>
                    Akun petugas yang ditugaskan pada surat ini (<span class="font-semibold">{{ $disposisi->petugas->name }}</span>) saat ini berstatus nonaktif. Sesuai ketentuan AC18-22, dokumen historis tetap dapat diakses, diunduh, dan dicetak oleh administrator tanpa harus mengaktifkan kembali akun petugas.
                </div>
            </div>
        @endif

        <!-- A4 Paper Preview Sheet (AC18-12, AC18-14) -->
        <div class="max-w-4xl mx-auto my-4 bg-white p-8 sm:p-14 shadow-lg rounded-2xl border border-slate-200" dusk="sheet-pratinjau">
            @include('admin.surat-tugas.document', ['disposisi' => $disposisi, 'waktuDokumen' => $waktuDokumen])
        </div>
    </div>
</x-admin-layout>

