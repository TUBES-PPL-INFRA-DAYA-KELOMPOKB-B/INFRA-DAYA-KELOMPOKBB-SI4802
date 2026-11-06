<?php

namespace App\Services;

use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\UmpanBalik;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UmpanBalikService
{
    /**
     * Simpan umpan balik dan rating warga untuk laporan berstatus selesai.
     *
     * @param  array{rating: int, komentar?: string|null, kecepatan_penanganan?: int|null, kualitas_perbaikan?: int|null}  $data
     *
     * @throws ValidationException
     */
    public function kirimUmpanBalik(LaporanKerusakan $laporan, User $warga, array $data): UmpanBalik
    {
        // 1. Otorisasi kepemilikan laporan
        if ((int) $laporan->warga_id !== (int) $warga->id) {
            throw ValidationException::withMessages([
                'laporan' => 'Anda tidak memiliki hak akses untuk memberikan evaluasi pada laporan ini.',
            ]);
        }

        // 2. Validasi status laporan harus selesai
        if ($laporan->status !== 'selesai') {
            throw ValidationException::withMessages([
                'status' => 'Evaluasi hanya dapat diberikan pada laporan perbaikan yang telah tuntas diselesaikan.',
            ]);
        }

        // 3. Validasi bahwa laporan belum pernah dinilai sebelumnya (1 kali evaluasi)
        if ($laporan->umpanBalik()->exists()) {
            throw ValidationException::withMessages([
                'rating' => 'Laporan ini sudah pernah Anda berikan penilaian kepuasan sebelumnya.',
            ]);
        }

        return DB::transaction(function () use ($laporan, $warga, $data) {
            $umpanBalik = UmpanBalik::create([
                'laporan_id' => $laporan->id,
                'warga_id' => $warga->id,
                'rating' => (int) $data['rating'],
                'komentar' => isset($data['komentar']) ? trim((string) $data['komentar']) : null,
                'kecepatan_penanganan' => isset($data['kecepatan_penanganan']) && $data['kecepatan_penanganan'] !== '' ? (int) $data['kecepatan_penanganan'] : null,
                'kualitas_perbaikan' => isset($data['kualitas_perbaikan']) && $data['kualitas_perbaikan'] !== '' ? (int) $data['kualitas_perbaikan'] : null,
                'is_tampil_publik' => true,
            ]);

            // Catat ke riwayat jejak audit
            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $warga->id,
                'status' => 'selesai',
                'keterangan' => 'Warga memberikan evaluasi kepuasan: '.$umpanBalik->rating.' bintang'.($umpanBalik->komentar ? ' ("'.$umpanBalik->komentar.'")' : ''),
                'created_at' => now(),
            ]);

            return $umpanBalik;
        });
    }
}
