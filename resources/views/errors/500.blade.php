<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Kesalahan Server | INFRA-DAYA Kota Bandung</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800">
    <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
        <!-- Badge & Icon -->
        <div class="inline-flex items-center justify-center h-20 w-20 rounded-2xl bg-rose-50 text-rose-600 mb-6 border border-rose-200">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <div class="text-xs font-bold uppercase tracking-widest text-rose-600 mb-2">500 • Gangguan Server</div>
        <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight mb-3">Terjadi Kendala Teknis</h1>
        <p class="text-sm text-slate-600 leading-relaxed mb-6">
            Sistem INFRA-DAYA sedang mengalami gangguan sementara saat memproses permintaan Anda. Tim pengelola teknis DSDABM telah mencatat insiden ini untuk pemulihan cepat.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a
                href="javascript:window.location.reload();"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#0E7C86] hover:bg-[#0c6b74] shadow-sm transition-all"
            >
                Muat Ulang Halaman
            </a>
            <a
                href="{{ url('/') }}"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors"
            >
                Halaman Utama
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-400">
            INFRA-DAYA • DSDABM Pemerintah Kota Bandung
        </div>
    </div>
</body>
</html>
