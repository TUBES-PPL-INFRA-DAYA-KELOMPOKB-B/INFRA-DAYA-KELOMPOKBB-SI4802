<?php

namespace App\Http\Controllers;

use App\Models\StoredFile;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class StorageFileController extends Controller
{
    public function show(string $path): Response
    {
        // Sanitize path against traversal attacks
        $path = str_replace(['..', "\0"], '', $path);
        $path = ltrim($path, '/');

        // 1. Check local storage disk (/tmp in serverless)
        if (Storage::disk('public')->exists($path)) {
            $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
            $content = Storage::disk('public')->get($path);

            return response($content, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // 2. Check database stored_files table
        $file = StoredFile::where('path', $path)->first();
        if ($file) {
            $binary = base64_decode($file->content_base64);

            // Re-cache locally in /tmp for fast subsequent reads
            try {
                Storage::disk('public')->put($path, $binary);
            } catch (\Throwable $e) {}

            return response($binary, 200, [
                'Content-Type' => $file->mime_type ?: 'image/jpeg',
                'Content-Length' => strlen($binary),
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // 3. Fallback graceful placeholder SVG so users never get a broken image box
        return $this->servePlaceholder($path);
    }

    protected function servePlaceholder(string $path): Response
    {
        $label = 'Foto Dokumentasi';
        $sub = 'Infrastruktur Kota Bandung';
        if (str_contains($path, 'avatar')) {
            $label = 'Foto Profil';
            $sub = 'Pengguna INFRA-DAYA';
        } elseif (str_contains($path, 'sesudah')) {
            $label = 'Bukti Perbaikan';
            $sub = 'Pekerjaan Selesai';
        } elseif (str_contains($path, 'sebelum')) {
            $label = 'Bukti Kerusakan';
            $sub = 'Laporan Warga';
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" width="800" height="600">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#070B14" />
            <stop offset="100%" stop-color="#0F172A" />
        </linearGradient>
    </defs>
    <rect width="800" height="600" fill="url(#bg)"/>
    <g transform="translate(400, 270)" text-anchor="middle">
        <circle cx="0" cy="0" r="54" fill="#1E293B" stroke="#334155" stroke-width="2"/>
        <path d="M-20 -10 L20 -10 L20 18 L-20 18 Z M-12 -16 L12 -16 L8 -10 L-8 -10 Z" fill="none" stroke="#60A5FA" stroke-width="3" stroke-linejoin="round" />
        <circle cx="0" cy="4" r="7" fill="#60A5FA" />
        <text y="90" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="bold" fill="#F8FAFC">{$label}</text>
        <text y="120" font-family="system-ui, -apple-system, sans-serif" font-size="14" font-weight="500" fill="#94A3B8">{$sub}</text>
        <text y="145" font-family="system-ui, -apple-system, sans-serif" font-size="11" font-weight="600" fill="#3B82F6" letter-spacing="1">DSDABM PEMERINTAH KOTA BANDUNG</text>
    </g>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
