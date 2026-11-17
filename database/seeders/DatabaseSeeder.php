<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        require_once __DIR__ . '/LaporanPbi13Seeder.php';

        $this->call([
            WilayahSeeder::class,
            UserSeeder::class,
            KategoriInfrastrukturSeeder::class,
            LaporanPbi13Seeder::class,
        ]);
    }
}
