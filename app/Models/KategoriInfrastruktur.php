<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriInfrastruktur extends Model
{
    use HasFactory;

    protected $table = 'kategori_infrastruktur';

    public const TINGKAT_KEPARAHAN = [
        'rendah' => 1,
        'sedang' => 2,
        'tinggi' => 3,
        'kritis' => 4,
    ];

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'tingkat_keparahan_default',
        'bobot_prioritas',
    ];

    protected function casts(): array
    {
        return [
            'bobot_prioritas' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (KategoriInfrastruktur $kategori) {
            if ($kategori->tingkat_keparahan_default) {
                $kategori->bobot_prioritas = self::getBobotFromTingkat($kategori->tingkat_keparahan_default);
            } elseif ($kategori->bobot_prioritas) {
                $kategori->tingkat_keparahan_default = self::getTingkatFromBobot($kategori->bobot_prioritas);
            }
        });
    }

    public static function getBobotFromTingkat(?string $tingkat): int
    {
        return self::TINGKAT_KEPARAHAN[strtolower((string) $tingkat)] ?? 2;
    }

    public static function getTingkatFromBobot(?int $bobot): string
    {
        return match ($bobot) {
            1 => 'rendah',
            2 => 'sedang',
            3 => 'tinggi',
            4 => 'kritis',
            default => 'sedang',
        };
    }

    /**
     * Label representasi teks tingkat keparahan default.
     */
    public function getTingkatKeparahanLabelAttribute(): string
    {
        $tingkat = $this->tingkat_keparahan_default ?? self::getTingkatFromBobot($this->bobot_prioritas);

        return match (strtolower((string) $tingkat)) {
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            'kritis' => 'Kritis',
            default => ucfirst((string) $tingkat),
        };
    }

    /**
     * Label representasi teks bobot prioritas.
     */
    public function getPrioritasLabelAttribute(): string
    {
        return match ($this->bobot_prioritas) {
            1 => 'Rendah',
            2 => 'Sedang',
            3 => 'Tinggi',
            4 => 'Kritis',
            default => 'Sedang',
        };
    }

    /**
     * Relasi ke seluruh laporan dalam kategori ini.
     */
    public function laporan(): HasMany
    {
        return $this->hasMany(LaporanKerusakan::class, 'kategori_id');
    }
}
