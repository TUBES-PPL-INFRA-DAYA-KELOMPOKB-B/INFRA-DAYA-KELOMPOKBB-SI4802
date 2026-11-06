<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmpanBalik extends Model
{
    use HasFactory;

    protected $table = 'umpan_balik';

    protected $fillable = [
        'laporan_id',
        'warga_id',
        'rating',
        'komentar',
        'kecepatan_penanganan',
        'kualitas_perbaikan',
        'is_tampil_publik',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'kecepatan_penanganan' => 'integer',
            'kualitas_perbaikan' => 'integer',
            'is_tampil_publik' => 'boolean',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKerusakan::class, 'laporan_id');
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }
}
