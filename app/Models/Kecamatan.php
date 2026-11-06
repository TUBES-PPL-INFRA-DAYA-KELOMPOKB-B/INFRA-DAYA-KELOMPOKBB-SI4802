<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';

    protected $fillable = [
        'kode_kecamatan',
        'nama_kecamatan',
    ];

    /**
     * Relasi ke seluruh kelurahan di kecamatan ini.
     */
    public function kelurahan(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id');
    }

    /**
     * Relasi ke seluruh laporan kerusakan melalui kelurahan.
     */
    public function laporan(): HasManyThrough
    {
        return $this->hasManyThrough(
            LaporanKerusakan::class,
            Kelurahan::class,
            'kecamatan_id',
            'kelurahan_id',
            'id',
            'id'
        );
    }
}
