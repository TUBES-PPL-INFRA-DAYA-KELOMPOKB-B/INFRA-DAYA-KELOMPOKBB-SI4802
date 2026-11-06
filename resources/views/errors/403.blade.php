<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak | INFRA-DAYA Kota Bandung</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800">
    <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
        <!-- Badge & Icon -->
        <div class="inline-flex items-center justify-center h-20 w-20 rounded-2xl bg-amber-50 text-amber-600 mb-6 border border-amber-200">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <div class="text-xs font-bold uppercase tracking-widest text-amber-600 mb-2">403 • Akses Terbatas</div>
        <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight mb-3">Wewenang Tidak Mencukupi</h1>
        <p class="text-sm text-slate-600 leading-relaxed mb-6">
            Akun Anda tidak memiliki hak akses atau peran yang sesuai untuk membuka halaman ini. Silakan periksa kembali peran akun Anda atau hubungi Administrator DSDABM Kota Bandung.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a
                href="{{ url('/') }}"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors"
            >
                Halaman Utama
            </a>
            <a
                href="{{ route('dashboard') }}"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#0E7C86] hover:bg-[#0c6b74] shadow-sm transition-all"
            >
                Buka Dashboard
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-400">
            INFRA-DAYA • DSDABM Pemerintah Kota Bandung
        </div>
    </div>
</body>
</html>
