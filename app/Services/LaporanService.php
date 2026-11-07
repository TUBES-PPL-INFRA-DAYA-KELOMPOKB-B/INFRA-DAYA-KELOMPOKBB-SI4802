<?php

namespace App\Services;

use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $filename = Str::random(40).'.'.$foto->getClientOriginalExtension();
            $path = $foto->storeAs('laporan/sebelum', $filename, 'public');

            $kelurahanId = $data['kelurahan_id'] ?? Kelurahan::value('id');
            $latitude = (float) ($data['latitude'] ?? -6.91746400);
            $longitude = (float) ($data['longitude'] ?? 107.61912300);

            $deskripsi = $data['deskripsi_kondisi'];
            if (! empty($data['patokan_lokasi']) && ! str_contains($deskripsi, $data['patokan_lokasi'])) {
                $deskripsi .= "\n[Patokan Lokasi: ".trim($data['patokan_lokasi']).']';
            }

            // PBI-11 (KF-11.4): Nilai awal prioritas laporan diisi dengan tingkat keparahan default kategori
            $kategori = KategoriInfrastruktur::find($data['kategori_id']);
            $prioritasAwal = $kategori?->tingkat_keparahan_default ?? 'sedang';

            $laporan = LaporanKerusakan::create([
                'nomor_tiket' => $this->generateNomorTiket(),
                'warga_id' => $wargaId,
                'kelurahan_id' => $kelurahanId,
                'kategori_id' => $data['kategori_id'],
                'judul' => $data['judul'],
                'deskripsi_kondisi' => $deskripsi,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'foto_bukti_sebelum' => $path,
                'status' => 'menunggu_verifikasi',
                'prioritas' => $prioritasAwal,
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
        if (! $laporan->canBeModifiedByWarga()) {
            throw new \DomainException('Laporan kerusakan tidak dapat diubah karena status bukan menunggu verifikasi.');
        }

        return DB::transaction(function () use ($laporan, $data, $foto) {
            if ($foto) {
                if ($laporan->foto_bukti_sebelum) {
                    Storage::disk('public')->delete($laporan->foto_bukti_sebelum);
                }
                $filename = Str::random(40).'.'.$foto->getClientOriginalExtension();
                $data['foto_bukti_sebelum'] = $foto->storeAs('laporan/sebelum', $filename, 'public');
            }

            $deskripsi = $data['deskripsi_kondisi'];
            if (! empty($data['patokan_lokasi']) && ! str_contains($deskripsi, $data['patokan_lokasi'])) {
                $deskripsi .= "\n[Patokan Lokasi: ".trim($data['patokan_lokasi']).']';
            }

            $laporan->update([
                'kelurahan_id' => $data['kelurahan_id'] ?? $laporan->kelurahan_id,
                'kategori_id' => $data['kategori_id'],
                'judul' => $data['judul'],
                'deskripsi_kondisi' => $deskripsi,
                'latitude' => (float) $data['latitude'],
                'longitude' => (float) $data['longitude'],
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

    /**
     * Membatalkan pengajuan laporan oleh pelapor (PBI-9).
     */
    public function cancelLaporan(LaporanKerusakan $laporan, string $alasanPembatalan, ?int $userId = null): LaporanKerusakan
    {
        if (! $laporan->canBeCancelledByWarga()) {
            throw new \DomainException('Laporan kerusakan tidak dapat dibatalkan karena status bukan menunggu verifikasi.');
        }

        return DB::transaction(function () use ($laporan, $alasanPembatalan, $userId) {
            if ($laporan->foto_bukti_sebelum) {
                Storage::disk('public')->delete($laporan->foto_bukti_sebelum);
            }

            $laporan->update([
                'status' => 'dibatalkan',
                'alasan_pembatalan' => trim($alasanPembatalan),
            ]);

            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $userId ?? $laporan->warga_id,
                'status' => 'dibatalkan',
                'keterangan' => 'Laporan dibatalkan oleh pelapor. Alasan: '.trim($alasanPembatalan),
            ]);

            return $laporan;
        });
    }

    public function deleteLaporan(LaporanKerusakan $laporan, ?string $alasan = null, ?int $userId = null): bool
    {
        $this->cancelLaporan($laporan, $alasan ?? 'Dibatalkan oleh pelapor.', $userId);

        return true;
    }

    /**
     * Mengambil daftar laporan untuk antrean verifikasi admin dengan filter multi-kriteria (PBI-13).
     */
    public function getPaginatedLaporanAdmin(
        ?string $search = null,
        ?string $status = null,
        ?int $kategoriId = null,
        ?int $kecamatanId = null,
        ?string $tanggalMulai = null,
        ?string $tanggalAkhir = null,
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
            ->when($tanggalMulai, function ($query, $tanggalMulai) {
                $query->whereDate('created_at', '>=', $tanggalMulai);
            })
            ->when($tanggalAkhir, function ($query, $tanggalAkhir) {
                $query->whereDate('created_at', '<=', $tanggalAkhir);
            })
            ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil detail laporan kerusakan untuk keperluan verifikasi (PBI-14).
     */
    public function findLaporan(int $id): LaporanKerusakan
    {
        return LaporanKerusakan::with(['warga', 'kategori', 'kelurahan.kecamatan', 'riwayatStatus.user'])->findOrFail($id);
    }

    /**
     * Menerima / menyetujui laporan kerusakan oleh Admin DSDABM (PBI-14).
     */
    public function setujuiLaporan(int $id, int $adminId, ?string $prioritas = 'sedang', ?string $catatan = null): LaporanKerusakan
    {
        return DB::transaction(function () use ($id, $adminId, $prioritas, $catatan) {
            $laporan = LaporanKerusakan::lockForUpdate()->findOrFail($id);

            if ($laporan->status !== 'menunggu_verifikasi') {
                throw new \InvalidArgumentException('Hanya laporan dengan status Menunggu Verifikasi yang dapat disetujui.');
            }

            $prio = $prioritas ?: ($laporan->prioritas ?: 'sedang');

            $laporan->update([
                'status' => 'diproses',
                'prioritas' => $prio,
                'alasan_penolakan' => null,
            ]);

            $keterangan = 'Laporan disetujui oleh Admin DSDABM.';
            if ($prioritas) {
                $keterangan .= ' Prioritas ditetapkan: ' . ucfirst($prio) . '.';
            }
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
     * Menolak laporan kerusakan oleh Admin DSDABM dengan alasan konkret (PBI-14).
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

    /**
     * Memperbarui tingkat prioritas penanganan laporan kerusakan aktif (PBI-15).
     */
    public function updatePrioritasLaporan(int $id, int $adminId, string $prioritas): LaporanKerusakan
    {
        $validPrioritas = ['rendah', 'sedang', 'tinggi'];
        if (! in_array(strtolower($prioritas), $validPrioritas, true)) {
            throw new \InvalidArgumentException('Tingkat prioritas harus salah satu dari: Rendah, Sedang, atau Tinggi.');
        }

        return DB::transaction(function () use ($id, $adminId, $prioritas) {
            $laporan = LaporanKerusakan::lockForUpdate()->findOrFail($id);

            // Tolak perubahan prioritas jika laporan sudah selesai atau ditolak (AC02-11)
            if (in_array($laporan->status, ['selesai', 'ditolak'], true)) {
                throw new \InvalidArgumentException('Laporan yang sudah berstatus Selesai atau Ditolak tidak dapat diubah prioritasnya.');
            }

            $laporan->update([
                'prioritas' => strtolower($prioritas),
            ]);

            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $adminId,
                'status' => $laporan->status,
                'keterangan' => 'Tingkat prioritas penanganan diperbarui menjadi: ' . ucfirst($prioritas) . '.',
            ]);

            return $laporan;
        });
    }
}