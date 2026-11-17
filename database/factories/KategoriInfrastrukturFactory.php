<?php

namespace Database\Factories;

use App\Models\KategoriInfrastruktur;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriInfrastrukturFactory extends Factory
{
    protected $model = KategoriInfrastruktur::class;

    public function definition(): array
    {
        $tingkat = fake()->randomElement(['rendah', 'sedang', 'tinggi', 'kritis']);

        return [
            'nama_kategori' => 'Kategori '.fake()->unique()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'tingkat_keparahan_default' => $tingkat,
            'bobot_prioritas' => KategoriInfrastruktur::getBobotFromTingkat($tingkat),
        ];
    }
}
