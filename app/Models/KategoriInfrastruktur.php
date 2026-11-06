<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriInfrastruktur extends Model
{
    use HasFactory;

    protected $table = 'kategori_infrastruktur';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'bobot_prioritas',
    ];

    protected function casts(): array
    {
        return [
            'bobot_prioritas' => 'integer',
        ];
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
