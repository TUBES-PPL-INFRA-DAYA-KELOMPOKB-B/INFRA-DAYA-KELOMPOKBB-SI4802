<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-[#EEF3F6]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'INFRA-DAYA') }} - Kota Bandung</title>

        <!-- Favicon Resmi INFRA-DAYA -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

        <!-- Fonts Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#EEF3F6] font-sans text-slate-800 antialiased flex flex-col py-8 sm:py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto my-auto space-y-6">
            <!-- Brand Logo & Header -->
            <div class="text-center pt-2">
                <a href="{{ url('/') }}" class="inline-block transition-transform hover:scale-105 duration-200 focus:outline-none">
                    <x-application-logo variant="full" class="h-20 sm:h-22 w-auto max-w-[260px] mx-auto drop-shadow-xs" />
                </a>
                <div class="mt-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                    DSDABM Pemerintah Kota Bandung
                </div>
            </div>

            <!-- Card Box -->
            <div class="bg-white py-7 px-6 sm:px-9 shadow-sm rounded-xl border border-slate-200">
                {{ $slot }}
            </div>

            <!-- Civic Footer Note -->
            <div class="text-center text-xs text-slate-400 pb-2">
                &copy; {{ date('Y') }} INFRA-DAYA &bull; Sistem Pelaporan Kerusakan Infrastruktur Kota Bandung
            </div>
        </div>
    </body>
</html>
