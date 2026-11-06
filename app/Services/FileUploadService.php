<?php

namespace App\Services;

use App\Models\StoredFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Store uploaded file on public disk and persist to database for serverless resilience.
     */
    public static function storePublicFile(UploadedFile $file, string $directory): string
    {
        $extension = $file->guessExtension() ?: 'jpg';
        $filename = Str::random(40) . '.' . $extension;
        $path = $file->storeAs($directory, $filename, 'public');

        try {
            $content = file_get_contents($file->getRealPath());
            StoredFile::updateOrCreate(
                ['path' => $path],
                [
                    'mime_type' => $file->getClientMimeType() ?: 'image/jpeg',
                    'content_base64' => base64_encode($content),
                    'size' => strlen($content),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning("Failed to store file {$path} in database: " . $e->getMessage());
        }

        return $path;
    }

    /**
     * Delete public file from disk and database.
     */
    public static function deletePublicFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable $e) {}

        try {
            StoredFile::where('path', $path)->delete();
        } catch (\Throwable $e) {}
    }
}
