<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisposisiPenugasan extends Model
{
    use HasFactory;

    protected $table = 'disposisi_penugasan';

    protected $fillable = [
        'nomor_surat_tugas',
        'laporan_id',
        'admin_id',
        'petugas_id',
        'tanggal_disposisi',
        'tenggat_waktu',
        'catatan_instruksi',
        'foto_bukti_sesudah',
        'catatan_perbaikan',
        'tanggal_selesai',
        'status_penugasan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_disposisi' => 'date',
            'tenggat_waktu' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKerusakan::class, 'laporan_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function isDitugaskan(): bool
    {
        return $this->status_penugasan === 'ditugaskan';
    }

    public function isSedangDikerjakan(): bool
    {
        return $this->status_penugasan === 'sedang_dikerjakan';
    }

    public function isSelesai(): bool
    {
        return $this->status_penugasan === 'selesai';
    }

    public function canBeStarted(): bool
    {
        return $this->status_penugasan === 'ditugaskan';
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status_penugasan, ['ditugaskan', 'sedang_dikerjakan'], true);
    }

    public function isOverdue(): bool
    {
        return $this->status_penugasan !== 'selesai' && $this->tenggat_waktu->isPast() && ! $this->tenggat_waktu->isToday();
    }

    public function sisaHari(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->tenggat_waktu->startOfDay(), false);
    }
}
