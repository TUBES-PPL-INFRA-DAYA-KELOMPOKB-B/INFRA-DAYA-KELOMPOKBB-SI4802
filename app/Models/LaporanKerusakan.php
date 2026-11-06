<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanKerusakan extends Model
{
    use HasFactory;

    protected $table = 'laporan_kerusakan';

    protected $fillable = [
        'nomor_tiket',
        'warga_id',
        'kelurahan_id',
        'kategori_id',
        'judul',
        'deskripsi_kondisi',
        'latitude',
        'longitude',
        'foto_bukti_sebelum',
        'foto_bukti_sesudah',
        'status',
        'prioritas',
        'alasan_penolakan',
        'catatan_perbaikan',
        'selesai_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'selesai_at' => 'datetime',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriInfrastruktur::class, 'kategori_id');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class, 'laporan_id')->orderBy('created_at', 'desc');
    }

    public function disposisi(): HasOne
    {
        return $this->hasOne(DisposisiPenugasan::class, 'laporan_id');
    }

    public function umpanBalik(): HasOne
    {
        return $this->hasOne(UmpanBalik::class, 'laporan_id');
    }

    /**
     * Cek apakah laporan masih dapat diubah atau dibatalkan oleh warga.
     */
    public function canBeModifiedByWarga(): bool
    {
        return $this->status === 'menunggu_verifikasi';
    }

    public function hasDisposisi(): bool
    {
        return $this->disposisi !== null;
    }

    public function scopeSiapDisposisi($query)
    {
        return $query->where('status', 'diproses')->doesntHave('disposisi');
    }
}
