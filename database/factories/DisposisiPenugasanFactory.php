<?php

namespace Database\Factories;

use App\Models\DisposisiPenugasan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisposisiPenugasanFactory extends Factory
{
    protected $model = DisposisiPenugasan::class;

    public function definition(): array
    {
        return [
            'nomor_surat_tugas' => 'SPT-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'laporan_id' => LaporanKerusakan::factory(),
            'admin_id' => User::factory()->state(['role' => 'admin']),
            'petugas_id' => User::factory()->state(['role' => 'petugas']),
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(3)->toDateString(),
            'catatan_instruksi' => fake()->sentence(8),
            'status_penugasan' => 'ditugaskan',
        ];
    }
}
