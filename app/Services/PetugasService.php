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
     * Sesuai FR-19.1 - FR-19.4: filter status, pencarian, dan pengurutan default prioritas tertinggi lalu disposisi terlama.
     */
    public function getPaginatedTugas(
        int $petugasId,
        ?string $status = null,
        ?string $search = null,
        ?string $sort = null,
        int $perPage = 9
    ): LengthAwarePaginator {
        $query = DisposisiPenugasan::with([
            'laporan.kelurahan.kecamatan',
            'laporan.kategori',
            'laporan.warga',
            'admin',
        ])
            ->where('petugas_id', $petugasId);

        if ($status && in_array(strtolower($status), ['ditugaskan', 'diterima', 'sedang_dikerjakan', 'dalam_pengerjaan', 'selesai'], true)) {
            if (in_array(strtolower($status), ['sedang_dikerjakan', 'dalam_pengerjaan'], true)) {
                $query->whereIn('status_penugasan', ['sedang_dikerjakan', 'dalam_pengerjaan']);
            } else {
                $query->where('status_penugasan', strtolower($status));
            }
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

        $query->join('laporan_kerusakan', 'disposisi_penugasan.laporan_id', '=', 'laporan_kerusakan.id')
            ->select('disposisi_penugasan.*');

        if ($sort === 'terbaru') {
            $query->orderBy('disposisi_penugasan.tanggal_disposisi', 'desc')
                  ->orderBy('disposisi_penugasan.id', 'desc');
        } elseif ($sort === 'terlama') {
            $query->orderBy('disposisi_penugasan.tanggal_disposisi', 'asc')
                  ->orderBy('disposisi_penugasan.id', 'asc');
        } elseif ($sort === 'tenggat') {
            $query->orderBy('disposisi_penugasan.tenggat_waktu', 'asc');
        } else {
            // Default FR-19.3: Prioritas tertinggi lalu tanggal disposisi terlama
            $query->orderByRaw("
                CASE laporan_kerusakan.prioritas
                    WHEN 'tinggi' THEN 1
                    WHEN 'sedang' THEN 2
                    WHEN 'rendah' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderBy('disposisi_penugasan.tanggal_disposisi', 'asc')
            ->orderBy('disposisi_penugasan.id', 'asc');
        }

        return $query->paginate($perPage)->withQueryString();
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
     * Menerima tugas penugasan oleh petugas lapangan (FR-19.8 / PBI-19).
     * Mengubah status penugasan dari 'ditugaskan' menjadi 'diterima' dan mencatat riwayat log.
     */
    public function acceptTask(int $disposisiId, int $petugasId): DisposisiPenugasan
    {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        if (! $disposisi->canBeAccepted()) {
            throw new HttpException(422, 'Tugas ini tidak dalam status siap diterima (status saat ini: ' . $disposisi->status_penugasan . ').');
        }

        return DB::transaction(function () use ($disposisi, $petugasId) {
            $disposisi->update([
                'status_penugasan' => 'diterima',
            ]);

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'diproses',
                'keterangan' => "Tugas penanganan laporan diterima oleh petugas {$namaPetugas}.",
            ]);

            return $disposisi->fresh();
        });
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
            'diterima' => (clone $baseQuery)->where('status_penugasan', 'diterima')->count(),
            'sedang_dikerjakan' => (clone $baseQuery)->whereIn('status_penugasan', ['sedang_dikerjakan', 'dalam_pengerjaan'])->count(),
            'selesai' => (clone $baseQuery)->where('status_penugasan', 'selesai')->count(),
        ];
    }
}
