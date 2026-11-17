<?php

namespace App\Services;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WilayahService
{
    /**
     * Mengambil daftar kecamatan dengan pencarian dan paginasi.
     */
    public function getPaginatedKecamatan(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Kecamatan::withCount('kelurahan')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kecamatan', 'like', "%{$search}%")
                        ->orWhere('kode_kecamatan', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_kecamatan')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil semua kecamatan untuk dropdown.
     */
    public function getAllKecamatan(): Collection
    {
        return Kecamatan::orderBy('nama_kecamatan')->get();
    }

    public function findKecamatan(int $id): Kecamatan
    {
        return Kecamatan::with('kelurahan')->findOrFail($id);
    }

    public function createKecamatan(array $data): Kecamatan
    {
        return Kecamatan::create($data);
    }

    public function updateKecamatan(int $id, array $data): Kecamatan
    {
        $kecamatan = Kecamatan::findOrFail($id);
        $kecamatan->update($data);

        return $kecamatan;
    }

    public function deleteKecamatan(int $id): bool
    {
        $kecamatan = Kecamatan::findOrFail($id);

        if ($kecamatan->kelurahan()->exists()) {
            throw new \RuntimeException('Kecamatan tidak dapat dihapus karena masih memiliki data kelurahan.');
        }

        return $kecamatan->delete();
    }

    /**
     * Mengambil daftar kelurahan dengan filter kecamatan, pencarian, dan paginasi.
     */
    public function getPaginatedKelurahan(?int $kecamatanId = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Kelurahan::with('kecamatan')
            ->when($kecamatanId, function ($query, $kecamatanId) {
                $query->where('kecamatan_id', $kecamatanId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kelurahan', 'like', "%{$search}%")
                        ->orWhere('kode_kelurahan', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_kelurahan')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findKelurahan(int $id): Kelurahan
    {
        return Kelurahan::with('kecamatan')->findOrFail($id);
    }

    public function createKelurahan(array $data): Kelurahan
    {
        return Kelurahan::create($data);
    }

    public function updateKelurahan(int $id, array $data): Kelurahan
    {
        $kelurahan = Kelurahan::findOrFail($id);
        $kelurahan->update($data);

        return $kelurahan;
    }

    public function deleteKelurahan(int $id): bool
    {
        $kelurahan = Kelurahan::findOrFail($id);

        if ($kelurahan->laporan()->exists()) {
            throw new \RuntimeException('Kelurahan tidak dapat dihapus karena masih digunakan dalam laporan kerusakan.');
        }

        return $kelurahan->delete();
    }

    /**
     * Mendapatkan daftar kelurahan berdasarkan kecamatan untuk chained dropdown.
     */
    public function getKelurahanByKecamatan(int $kecamatanId): Collection
    {
        return Kelurahan::where('kecamatan_id', $kecamatanId)
            ->orderBy('nama_kelurahan')
            ->get(['id', 'kode_kelurahan', 'nama_kelurahan', 'kecamatan_id']);
    }
}
