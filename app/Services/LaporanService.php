<?php

namespace App\Services;

use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * Generate format nomor tiket unik: INFRA-YYYYMMDD-XXXX
     */
    public function generateNomorTiket(): string
    {
        $datePrefix = Carbon::now()->format('Ymd');
        $prefix = "INFRA-{$datePrefix}-";

        return DB::transaction(function () use ($prefix) {
            $lastTicket = LaporanKerusakan::where('nomor_tiket', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('nomor_tiket', 'desc')
                ->value('nomor_tiket');

            if ($lastTicket) {
                $lastNumber = (int) substr($lastTicket, -4);
                $newNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            return "{$prefix}{$newNumber}";
        });
    }

    /**
     * Mengambil daftar laporan yang diajukan oleh seorang warga.
     */
    public function getPaginatedLaporanWarga(int $wargaId, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return LaporanKerusakan::with(['kelurahan.kecamatan', 'kategori'])
            ->where('warga_id', $wargaId)
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createLaporan(int $wargaId, array $data, UploadedFile $foto): LaporanKerusakan
    {
        return DB::transaction(function () use ($wargaId, $data, $foto) {
            $path = FileUploadService::storePublicFile($foto, 'laporan/sebelum');

            $kelurahanId = $data['kelurahan_id'] ?? Kelurahan::value('id');
            $latitude = $data['latitude'] ?? -6.91746400;
            $longitude = $data['longitude'] ?? 107.61912300;

            $laporan = LaporanKerusakan::create([
                'nomor_tiket' => $this->generateNomorTiket(),
                'warga_id' => $wargaId,
                'kelurahan_id' => $kelurahanId,
                'kategori_id' => $data['kategori_id'],
                'judul' => $data['judul'],
                'deskripsi_kondisi' => $data['deskripsi_kondisi'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'foto_bukti_sebelum' => $path,
                'status' => 'menunggu_verifikasi',
                'prioritas' => 'sedang',
            ]);

            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $wargaId,
                'status' => 'menunggu_verifikasi',
                'keterangan' => 'Laporan kerusakan berhasil diajukan oleh pelapor.',
            ]);

            return $laporan;
        });
    }

    public function updateLaporan(LaporanKerusakan $laporan, array $data, ?UploadedFile $foto = null): LaporanKerusakan
    {
        return DB::transaction(function () use ($laporan, $data, $foto) {
            if ($foto) {
                if ($laporan->foto_bukti_sebelum) {
                    FileUploadService::deletePublicFile($laporan->foto_bukti_sebelum);
                }
                $data['foto_bukti_sebelum'] = FileUploadService::storePublicFile($foto, 'laporan/sebelum');
            }

            $laporan->update([
                'kelurahan_id' => $data['kelurahan_id'],
                'kategori_id' => $data['kategori_id'],
                'judul' => $data['judul'],
                'deskripsi_kondisi' => $data['deskripsi_kondisi'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'foto_bukti_sebelum' => $data['foto_bukti_sebelum'] ?? $laporan->foto_bukti_sebelum,
            ]);

            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $laporan->warga_id,
                'status' => $laporan->status,
                'keterangan' => 'Data laporan dan titik koordinat diperbarui oleh pelapor.',
            ]);

            return $laporan;
        });
    }

    public function deleteLaporan(LaporanKerusakan $laporan): bool
    {
        return DB::transaction(function () use ($laporan) {
            if ($laporan->foto_bukti_sebelum) {
                FileUploadService::deletePublicFile($laporan->foto_bukti_sebelum);
            }

            return $laporan->delete();
        });
    }

    /**
     * Mengambil daftar laporan untuk antrean verifikasi admin dengan filter.
     */
    public function getPaginatedLaporanAdmin(
        ?string $search = null,
        ?string $status = null,
        ?int $kategoriId = null,
        ?int $kecamatanId = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return LaporanKerusakan::with(['warga', 'kategori', 'kelurahan.kecamatan'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_tiket', 'like', "%{$search}%")
                        ->orWhere('judul', 'like', "%{$search}%")
                        ->orWhereHas('warga', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->when($kecamatanId, function ($query, $kecamatanId) {
                $query->whereHas('kelurahan', fn ($kq) => $kq->where('kecamatan_id', $kecamatanId));
            })
            ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findLaporan(int $id): LaporanKerusakan
    {
        return LaporanKerusakan::with(['warga', 'kategori', 'kelurahan.kecamatan', 'riwayatStatus.user', 'disposisi.petugas'])->findOrFail($id);
    }

    /**
     * Menyetujui laporan kerusakan oleh Admin DSDABM.
     */
    public function setujuiLaporan(int $id, int $adminId, string $prioritas, ?string $catatan = null): LaporanKerusakan
    {
        return DB::transaction(function () use ($id, $adminId, $prioritas, $catatan) {
            $laporan = LaporanKerusakan::lockForUpdate()->findOrFail($id);

            if ($laporan->status !== 'menunggu_verifikasi') {
                throw new \InvalidArgumentException('Hanya laporan dengan status Menunggu Verifikasi yang dapat disetujui.');
            }

            $laporan->update([
                'status' => 'diproses',
                'prioritas' => $prioritas,
                'alasan_penolakan' => null,
            ]);

            $keterangan = 'Laporan disetujui oleh Admin DSDABM dengan prioritas: '.ucfirst($prioritas).'.';
            if ($catatan) {
                $keterangan .= " Catatan verifikator: {$catatan}";
            }

            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $adminId,
                'status' => 'diproses',
                'keterangan' => $keterangan,
            ]);

            return $laporan;
        });
    }

    /**
     * Menolak laporan kerusakan oleh Admin DSDABM dengan alasan konkret.
     */
    public function tolakLaporan(int $id, int $adminId, string $alasan): LaporanKerusakan
    {
        return DB::transaction(function () use ($id, $adminId, $alasan) {
            $laporan = LaporanKerusakan::lockForUpdate()->findOrFail($id);

            if ($laporan->status !== 'menunggu_verifikasi') {
                throw new \InvalidArgumentException('Hanya laporan dengan status Menunggu Verifikasi yang dapat ditolak.');
            }

            $laporan->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $alasan,
            ]);

            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $adminId,
                'status' => 'ditolak',
                'keterangan' => "Laporan ditolak oleh Admin DSDABM. Alasan: {$alasan}",
            ]);

            return $laporan;
        });
    }
}
