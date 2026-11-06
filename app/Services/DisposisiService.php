<?php

namespace App\Services;

use App\Models\DisposisiPenugasan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DisposisiService
{
    /**
     * Generate format nomor surat perintah tugas (SPT) unik: SPT-YYYYMMDD-XXXX
     */
    public function generateNomorSuratTugas(): string
    {
        $datePrefix = Carbon::now()->format('Ymd');
        $prefix = "SPT-{$datePrefix}-";

        return DB::transaction(function () use ($prefix) {
            $lastSurat = DisposisiPenugasan::where('nomor_surat_tugas', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('nomor_surat_tugas', 'desc')
                ->value('nomor_surat_tugas');

            if ($lastSurat) {
                $lastNumber = (int) substr($lastSurat, -4);
                $newNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            return "{$prefix}{$newNumber}";
        });
    }

    /**
     * Mengambil daftar disposisi dengan filter.
     */
    public function getPaginatedDisposisi(
        ?string $search = null,
        ?string $statusPenugasan = null,
        ?int $petugasId = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return DisposisiPenugasan::with([
            'laporan.kategori',
            'laporan.kelurahan.kecamatan',
            'laporan.warga',
            'petugas',
            'admin',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_surat_tugas', 'like', "%{$search}%")
                        ->orWhereHas('laporan', function ($lq) use ($search) {
                            $lq->where('nomor_tiket', 'like', "%{$search}%")
                                ->orWhere('judul', 'like', "%{$search}%");
                        })
                        ->orWhereHas('petugas', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($statusPenugasan, function ($query, $statusPenugasan) {
                $query->where('status_penugasan', $statusPenugasan);
            })
            ->when($petugasId, function ($query, $petugasId) {
                $query->where('petugas_id', $petugasId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar laporan berstatus 'diproses' yang belum memiliki disposisi petugas.
     */
    public function getLaporanSiapDisposisi(): Collection
    {
        return LaporanKerusakan::with(['kelurahan.kecamatan', 'kategori', 'warga'])
            ->siapDisposisi()
            ->orderByRaw("CASE WHEN prioritas = 'tinggi' THEN 1 WHEN prioritas = 'sedang' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Mengambil daftar petugas aktif.
     */
    public function getPetugasAktif(): Collection
    {
        return User::petugasAktif()->orderBy('name')->get();
    }

    /**
     * Menerbitkan disposisi penugasan baru.
     */
    public function createDisposisi(
        int $laporanId,
        int $adminId,
        int $petugasId,
        string $tenggatWaktu,
        string $catatanInstruksi,
        ?string $tanggalDisposisi = null
    ): DisposisiPenugasan {
        return DB::transaction(function () use ($laporanId, $adminId, $petugasId, $tenggatWaktu, $catatanInstruksi, $tanggalDisposisi) {
            $laporan = LaporanKerusakan::lockForUpdate()->findOrFail($laporanId);

            if ($laporan->status !== 'diproses') {
                throw new \InvalidArgumentException('Hanya laporan yang telah disetujui (status diproses) yang dapat didisposisikan.');
            }

            if ($laporan->disposisi()->exists()) {
                throw new \InvalidArgumentException('Laporan ini sudah memiliki disposisi penugasan aktif.');
            }

            $petugas = User::where('id', $petugasId)
                ->where('role', 'petugas')
                ->where('is_active', true)
                ->firstOrFail();

            $tanggalDisposisi = $tanggalDisposisi ?: Carbon::now()->toDateString();

            if (Carbon::parse($tenggatWaktu)->lt(Carbon::parse($tanggalDisposisi))) {
                throw new \InvalidArgumentException('Tenggat waktu pelaksanaan tidak boleh lebih awal dari tanggal disposisi.');
            }

            $nomorSurat = $this->generateNomorSuratTugas();

            $disposisi = DisposisiPenugasan::create([
                'nomor_surat_tugas' => $nomorSurat,
                'laporan_id' => $laporan->id,
                'admin_id' => $adminId,
                'petugas_id' => $petugas->id,
                'tanggal_disposisi' => $tanggalDisposisi,
                'tenggat_waktu' => $tenggatWaktu,
                'catatan_instruksi' => $catatanInstruksi,
                'status_penugasan' => 'ditugaskan',
            ]);

            $tenggatFormat = Carbon::parse($tenggatWaktu)->translatedFormat('d F Y');
            RiwayatStatus::create([
                'laporan_id' => $laporan->id,
                'user_id' => $adminId,
                'status' => 'diproses',
                'keterangan' => "Surat Perintah Tugas ({$nomorSurat}) diterbitkan untuk petugas {$petugas->name}. Tenggat waktu: {$tenggatFormat}.",
            ]);

            return $disposisi;
        });
    }

    /**
     * Mengambil detail disposisi lengkap beserta relasinya.
     */
    public function findDisposisi(int $id): DisposisiPenugasan
    {
        return DisposisiPenugasan::with([
            'laporan.kategori',
            'laporan.kelurahan.kecamatan',
            'laporan.warga',
            'laporan.riwayatStatus.user',
            'petugas',
            'admin',
        ])->findOrFail($id);
    }
}
