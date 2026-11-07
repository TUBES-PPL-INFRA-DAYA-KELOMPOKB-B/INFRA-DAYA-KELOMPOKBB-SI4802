<?php

namespace App\Services;

use App\Models\KategoriInfrastruktur;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class KategoriService
{
    /**
     * Mengambil daftar kategori dengan pencarian dan paginasi.
     */
    public function getPaginatedKategori(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return KategoriInfrastruktur::query()
            ->withCount('laporan')
            ->when($search, function ($query, $search) {
                $query->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->orderByDesc('bobot_prioritas')
            ->orderBy('nama_kategori')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil semua kategori aktif untuk dropdown formulir laporan warga.
     */
    public function getAllKategori(): Collection
    {
        return KategoriInfrastruktur::orderBy('nama_kategori')->get();
    }

    public function findKategori(int $id): KategoriInfrastruktur
    {
        return KategoriInfrastruktur::findOrFail($id);
    }

    public function createKategori(array $data): KategoriInfrastruktur
    {
        if (isset($data['tingkat_keparahan_default'])) {
            $data['bobot_prioritas'] = KategoriInfrastruktur::getBobotFromTingkat($data['tingkat_keparahan_default']);
        } elseif (isset($data['bobot_prioritas']) && ! isset($data['tingkat_keparahan_default'])) {
            $data['tingkat_keparahan_default'] = KategoriInfrastruktur::getTingkatFromBobot((int) $data['bobot_prioritas']);
        }

        return KategoriInfrastruktur::create($data);
    }

    public function updateKategori(int $id, array $data): KategoriInfrastruktur
    {
        $kategori = KategoriInfrastruktur::findOrFail($id);

        if (isset($data['tingkat_keparahan_default'])) {
            $data['bobot_prioritas'] = KategoriInfrastruktur::getBobotFromTingkat($data['tingkat_keparahan_default']);
        } elseif (isset($data['bobot_prioritas']) && ! isset($data['tingkat_keparahan_default'])) {
            $data['tingkat_keparahan_default'] = KategoriInfrastruktur::getTingkatFromBobot((int) $data['bobot_prioritas']);
        }

        $kategori->update($data);

        return $kategori;
    }

    public function deleteKategori(int $id): bool
    {
        $kategori = KategoriInfrastruktur::findOrFail($id);

        if ($kategori->laporan()->exists()) {
            throw new \DomainException('Kategori tidak dapat dihapus karena masih digunakan oleh laporan kerusakan.');
        }

        return (bool) $kategori->delete();
    }
}
