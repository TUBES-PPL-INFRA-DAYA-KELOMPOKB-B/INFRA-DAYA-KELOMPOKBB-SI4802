<?php

namespace App\Services;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\LaporanKerusakan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RekapService
{
    /**
     * Membangun Query Eloquent terfilter untuk laporan kerusakan.
     *
     * @param  array<string, mixed>  $filters
     */
    public function buildQuery(array $filters = []): Builder
    {
        $query = LaporanKerusakan::query()
            ->with([
                'warga:id,name,email,phone',
                'kelurahan.kecamatan',
                'kategori:id,nama_kategori',
                'disposisi.petugas:id,name',
                'umpanBalik:id,laporan_id,rating,komentar',
            ]);

        // Filter Rentang Tanggal Mulai
        if (! empty($filters['tanggal_mulai'])) {
            $query->whereDate('laporan_kerusakan.created_at', '>=', $filters['tanggal_mulai']);
        }

        // Filter Rentang Tanggal Akhir
        if (! empty($filters['tanggal_akhir'])) {
            $query->whereDate('laporan_kerusakan.created_at', '<=', $filters['tanggal_akhir']);
        }

        // Filter Kecamatan
        if (! empty($filters['kecamatan_id'])) {
            $query->whereHas('kelurahan', function (Builder $q) use ($filters) {
                $q->where('kecamatan_id', $filters['kecamatan_id']);
            });
        }

        // Filter Kelurahan (Opsional)
        if (! empty($filters['kelurahan_id'])) {
            $query->where('kelurahan_id', $filters['kelurahan_id']);
        }

        // Filter Kategori
        if (! empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }

        // Filter Status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter Prioritas
        if (! empty($filters['prioritas'])) {
            $query->where('prioritas', $filters['prioritas']);
        }

        // Filter Pencarian Teks Bebas
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('nomor_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('warga', function (Builder $w) use ($search) {
                        $w->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Mendapatkan data terpaginasi untuk preview di halaman web.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage)->withQueryString();
    }

    /**
     * Mendapatkan seluruh koleksi data terfilter untuk keperluan ekspor.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, LaporanKerusakan>
     */
    public function getCollection(array $filters = []): Collection
    {
        return $this->buildQuery($filters)->get();
    }

    /**
     * Menghitung statistik ringkasan berdasarkan filter yang aktif.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, int|float>
     */
    public function getSummaryStats(array $filters = []): array
    {
        // Hitung statistik dari query filter
        $baseQuery = $this->buildQuery($filters);

        $laporanList = $baseQuery->get();

        $total = $laporanList->count();
        $selesai = $laporanList->where('status', 'selesai')->count();
        $diproses = $laporanList->where('status', 'diproses')->count();
        $menunggu = $laporanList->where('status', 'menunggu_verifikasi')->count();
        $ditolak = $laporanList->where('status', 'ditolak')->count();

        // Hitung rata-rata rating warga
        $ratings = $laporanList->pluck('umpanBalik.rating')->filter();
        $avgRating = $ratings->isNotEmpty() ? round($ratings->avg(), 1) : 0.0;

        return [
            'total' => $total,
            'selesai' => $selesai,
            'diproses' => $diproses,
            'menunggu' => $menunggu,
            'ditolak' => $ditolak,
            'avg_rating' => $avgRating,
        ];
    }

    /**
     * Opsi dropdown filter (Kecamatan dan Kategori).
     *
     * @return array{kecamatanList: Collection, kategoriList: Collection}
     */
    public function getFilterOptions(): array
    {
        return [
            'kecamatanList' => Kecamatan::query()->orderBy('nama_kecamatan')->get(),
            'kategoriList' => KategoriInfrastruktur::query()->orderBy('nama_kategori')->get(),
        ];
    }
}
