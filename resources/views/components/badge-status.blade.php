@props(['status'])

@php
    $normalized = strtolower(trim((string) $status));
    $classes = match($normalized) {
        'menunggu_verifikasi', 'menunggu', 'pending' => 'bg-slate-100 text-slate-700 border-slate-300 ring-slate-400/20',
        'ditugaskan' => 'bg-sky-100 text-sky-900 border-sky-300 ring-sky-400/20',
        'diproses', 'proses', 'in_progress', 'sedang_dikerjakan' => 'bg-amber-100 text-amber-900 border-amber-300 ring-amber-400/20',
        'selesai', 'resolved', 'completed' => 'bg-emerald-100 text-emerald-900 border-emerald-300 ring-emerald-400/20',
        'ditolak', 'rejected' => 'bg-rose-100 text-rose-900 border-rose-300 ring-rose-400/20',
        default => 'bg-slate-100 text-slate-700 border-slate-300 ring-slate-400/20',
    };

    $dotColor = match($normalized) {
        'menunggu_verifikasi', 'menunggu', 'pending' => 'bg-slate-400',
        'ditugaskan' => 'bg-sky-500',
        'diproses', 'proses', 'in_progress', 'sedang_dikerjakan' => 'bg-amber-500',
        'selesai', 'resolved', 'completed' => 'bg-emerald-500',
        'ditolak', 'rejected' => 'bg-rose-500',
        default => 'bg-slate-400',
    };

    $label = match($normalized) {
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'menunggu', 'pending' => 'Menunggu',
        'ditugaskan' => 'Ditugaskan',
        'diproses', 'proses', 'in_progress' => 'Diproses',
        'sedang_dikerjakan' => 'Sedang Dikerjakan',
        'selesai', 'resolved', 'completed' => 'Selesai',
        'ditolak', 'rejected' => 'Ditolak',
        default => ucwords(str_replace('_', ' ', $normalized)),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
    {{ $label }}
</span>
