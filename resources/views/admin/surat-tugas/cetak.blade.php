<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Tugas - {{ $disposisi->nomor_surat_tugas }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4">
    <!-- Action Toolbar (Hidden during print) (AC18-15) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <button
                type="button"
                onclick="window.close()"
                dusk="btn-tutup-cetak"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition"
            >
                &larr; Tutup / Batal
            </button>
            <div>
                <h4 class="text-sm font-bold text-slate-800">Cetak Dokumen Resmi SPT</h4>
                <p class="text-xs text-slate-500">Gunakan dialog cetak browser untuk memilih printer atau menyimpan sebagai PDF.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                dusk="btn-trigger-print"
                class="inline-flex items-center gap-2 px-5 py-2 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-bold rounded-xl shadow-sm transition"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Buka Dialog Cetak</span>
            </button>
        </div>
    </div>

    <!-- Printable Paper Sheet (AC18-12, AC18-14) -->
    <div class="page-sheet max-w-4xl mx-auto bg-white p-10 sm:p-14 shadow-md rounded-2xl border border-slate-200" dusk="printable-sheet">
        @include('admin.surat-tugas.document', ['disposisi' => $disposisi, 'waktuDokumen' => $waktuDokumen])
    </div>

    <!-- Auto Print Script (AC18-15) -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Berikan sedikit jeda render sebelum membuka dialog print otomatis
            setTimeout(() => {
                if (window.location.search.indexOf('noprint') === -1) {
                    window.print();
                }
            }, 300);
        });
    </script>
</body>
</html>

