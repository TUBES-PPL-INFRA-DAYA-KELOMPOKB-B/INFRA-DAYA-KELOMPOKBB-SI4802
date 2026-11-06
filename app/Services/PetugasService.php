<?php

namespace App\Services;

use App\Models\DisposisiPenugasan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PetugasService
{
    /**
     * Mengambil daftar tugas penugasan lapangan milik petugas tertentu secara terpaginasi.
     */
    public function getPaginatedTugas(
        int $petugasId,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 9
    ): LengthAwarePaginator {
        $query = DisposisiPenugasan::with([
            'laporan.kelurahan.kecamatan',
            'laporan.kategori',
            'laporan.warga',
            'admin',
        ])
            ->where('petugas_id', $petugasId);

        if ($status && in_array($status, ['ditugaskan', 'sedang_dikerjakan', 'selesai'], true)) {
            $query->where('status_penugasan', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat_tugas', 'like', "%{$search}%")
                    ->orWhereHas('laporan', function ($lq) use ($search) {
                        $lq->where('nomor_tiket', 'like', "%{$search}%")
                            ->orWhere('judul', 'like', "%{$search}%")
                            ->orWhere('deskripsi_kondisi', 'like', "%{$search}%")
                            ->orWhereHas('kelurahan', function ($kq) use ($search) {
                                $kq->where('nama_kelurahan', 'like', "%{$search}%")
                                    ->orWhereHas('kecamatan', function ($kcq) use ($search) {
                                        $kcq->where('nama_kecamatan', 'like', "%{$search}%");
                                    });
                            });
                    });
            });
        }

        return $query->orderByRaw("
            CASE status_penugasan
                WHEN 'sedang_dikerjakan' THEN 1
                WHEN 'ditugaskan' THEN 2
                WHEN 'selesai' THEN 3
                ELSE 4
            END ASC
        ")
            ->orderBy('tenggat_waktu', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil detail tugas tertentu dan memverifikasi kepemilikan petugas (IDOR prevention).
     */
    public function getTugasDetail(int $disposisiId, int $petugasId): DisposisiPenugasan
    {
        $disposisi = DisposisiPenugasan::with([
            'laporan.kelurahan.kecamatan',
            'laporan.kategori',
            'laporan.warga',
            'laporan.riwayatStatus.user',
            'admin',
            'petugas',
        ])->findOrFail($disposisiId);

        if ($disposisi->petugas_id !== $petugasId) {
            throw new HttpException(403, 'Anda tidak memiliki hak akses terhadap tugas penugasan ini.');
        }

        return $disposisi;
    }

    /**
     * Memulai pelaksanaan pengerjaan lapangan oleh petugas.
     */
    public function mulaiPengerjaan(int $disposisiId, int $petugasId): DisposisiPenugasan
    {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        if (! $disposisi->canBeStarted()) {
            throw new HttpException(422, 'Tugas ini tidak dalam status siap dikerjakan.');
        }

        return DB::transaction(function () use ($disposisi, $petugasId) {
            $disposisi->update([
                'status_penugasan' => 'sedang_dikerjakan',
            ]);

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'diproses',
                'keterangan' => "Petugas {$namaPetugas} telah memulai penanganan teknis di lokasi.",
            ]);

            return $disposisi->fresh();
        });
    }

    /**
     * Menyelesaikan tugas pengerjaan dengan unggahan foto bukti sesudah dan catatan teknis.
     */
    public function selesaikanTugas(
        int $disposisiId,
        int $petugasId,
        UploadedFile $fotoSesudah,
        string $catatanPerbaikan
    ): DisposisiPenugasan {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        if (! $disposisi->canBeCompleted()) {
            throw new HttpException(422, 'Tugas ini sudah selesai atau tidak dapat diselesaikan.');
        }

        // Simpan foto bukti sesudah dengan ekstensi aman server MIME (SEC-02)
        $path = FileUploadService::storePublicFile($fotoSesudah, 'laporan/sesudah');

        return DB::transaction(function () use ($disposisi, $petugasId, $path, $catatanPerbaikan) {
            $now = now();

            // Perbarui data disposisi
            $disposisi->update([
                'status_penugasan' => 'selesai',
                'foto_bukti_sesudah' => $path,
                'catatan_perbaikan' => $catatanPerbaikan,
                'tanggal_selesai' => $now->toDateString(),
            ]);

            // Perbarui data laporan
            $disposisi->laporan->update([
                'status' => 'selesai',
                'foto_bukti_sesudah' => $path,
                'catatan_perbaikan' => $catatanPerbaikan,
                'selesai_at' => $now,
            ]);

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            // Catat riwayat status audit
            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'selesai',
                'keterangan' => "Perbaikan tuntas oleh {$namaPetugas}. Catatan: {$catatanPerbaikan}",
            ]);

            return $disposisi->fresh();
        });
    }

    /**
     * Mengambil statistik ringkasan tugas untuk kartu metrik petugas.
     */
    public function getStatistikTugas(int $petugasId): array
    {
        $baseQuery = DisposisiPenugasan::where('petugas_id', $petugasId);

        return [
            'total' => (clone $baseQuery)->count(),
            'ditugaskan' => (clone $baseQuery)->where('status_penugasan', 'ditugaskan')->count(),
            'sedang_dikerjakan' => (clone $baseQuery)->where('status_penugasan', 'sedang_dikerjakan')->count(),
            'selesai' => (clone $baseQuery)->where('status_penugasan', 'selesai')->count(),
        ];
    }
}
