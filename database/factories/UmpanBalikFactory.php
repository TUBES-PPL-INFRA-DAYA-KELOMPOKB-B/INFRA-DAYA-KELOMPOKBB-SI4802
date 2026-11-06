<?php

namespace Database\Factories;

use App\Models\LaporanKerusakan;
use App\Models\UmpanBalik;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UmpanBalikFactory extends Factory
{
    protected $model = UmpanBalik::class;

    public function definition(): array
    {
        return [
            'laporan_id' => LaporanKerusakan::factory()->state(['status' => 'selesai']),
            'warga_id' => User::factory()->state(['role' => 'warga']),
            'rating' => fake()->numberBetween(1, 5),
            'komentar' => fake()->sentence(10),
            'kecepatan_penanganan' => fake()->numberBetween(1, 5),
            'kualitas_perbaikan' => fake()->numberBetween(1, 5),
            'is_tampil_publik' => true,
        ];
    }
}
