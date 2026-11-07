<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilPerbaikan extends Model
{
    use HasFactory;

    protected $table = 'hasil_perbaikan';

    protected $fillable = [
        'disposisi_id',
        'petugas_id',
        'foto_bukti_sesudah',
        'catatan_perbaikan',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_selesai' => 'date',
        ];
    }

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(DisposisiPenugasan::class, 'disposisi_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}

