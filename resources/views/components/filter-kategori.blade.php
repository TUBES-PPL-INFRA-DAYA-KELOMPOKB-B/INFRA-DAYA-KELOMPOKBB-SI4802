@props([
    'action' => route('admin.kategori.index'),
    'search' => request('search'),
    'keparahan' => request('keparahan') ?? request('tingkat_keparahan'),
    'placeholder' => 'Cari berdasarkan nama kategori atau deskripsi...',
])

@php
    $hasFilter = !empty($search) || (!empty($keparahan) && strtolower($keparahan) !== 'semua');
    $currentKeparahan = strtolower((string) $keparahan);
@endphp

<form method="GET" action="{{ $action }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
    <!-- Kolom Pencarian Berdasarkan Nama / Deskripsi Kategori (KF-12.1) -->
    <div class="flex-1 relative">
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="{{ $placeholder }}"
            class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent transition-all"
        >
        <svg class="h-5 w-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>

    <!-- Filter Dropdown Tingkat Keparahan Default (KF-12.2) -->
    <div class="w-full md:w-56">
        <select
            name="keparahan"
            onchange="this.form.submit()"
            class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent bg-white text-slate-700 transition-all"
        >
            <option value="" {{ empty($currentKeparahan) || $currentKeparahan === 'semua' ? 'selected' : '' }}>Semua Tingkat Keparahan</option>
            <option value="rendah" {{ $currentKeparahan === 'rendah' ? 'selected' : '' }}>🟢 Rendah (Bobot 1)</option>
            <option value="sedang" {{ $currentKeparahan === 'sedang' ? 'selected' : '' }}>🟡 Sedang (Bobot 2)</option>
            <option value="tinggi" {{ $currentKeparahan === 'tinggi' ? 'selected' : '' }}>🟠 Tinggi (Bobot 3)</option>
            <option value="kritis" {{ $currentKeparahan === 'kritis' ? 'selected' : '' }}>🔴 Kritis (Bobot 4)</option>
        </select>
    </div>

    <!-- Tombol Cari & Tombol Reset (KF-12.3 & KF-12.5) -->
    <div class="flex gap-2 items-center">
        <button type="submit" class="px-4 py-2 bg-[#1F3A5F] hover:bg-[#162a45] text-white rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1.5 shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cari
        </button>
        @if($hasFilter)
            <a href="{{ $action }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset
            </a>
        @endif
    </div>
</form>

