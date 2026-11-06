<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'role',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isWarga(): bool
    {
        return $this->role === 'warga';
    }

    public function penugasanPetugas(): HasMany
    {
        return $this->hasMany(DisposisiPenugasan::class, 'petugas_id');
    }

    public function laporanKerusakan(): HasMany
    {
        return $this->hasMany(LaporanKerusakan::class, 'warga_id');
    }

    public function disposisiDibuat(): HasMany
    {
        return $this->hasMany(DisposisiPenugasan::class, 'admin_id');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class, 'user_id');
    }

    public function hasRelatedData(): bool
    {
        return $this->laporanKerusakan()->exists()
            || $this->disposisiDibuat()->exists()
            || $this->penugasanPetugas()->exists()
            || $this->riwayatStatus()->exists();
    }

    public function scopePetugasAktif($query)
    {
        return $query->where('role', 'petugas')->where('is_active', true);
    }
}
