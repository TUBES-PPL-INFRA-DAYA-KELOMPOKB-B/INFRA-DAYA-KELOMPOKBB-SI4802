<?php

namespace Database\Seeders;

use App\Models\KategoriInfrastruktur;
use Illuminate\Database\Seeder;

class KategoriInfrastrukturSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriList = [
            [
                'nama_kategori' => 'Jalan Raya / Trotoar',
                'deskripsi' => 'Kerusakan aspal jalan berlubang, amblas, retak parah, trotoar pejalan kaki rusak atau hilang penutup.',
                'tingkat_keparahan_default' => 'tinggi',
                'bobot_prioritas' => 3, // Tinggi
            ],
            [
                'nama_kategori' => 'Saluran Drainase / Gorong-gorong',
                'deskripsi' => 'Gorong-gorong tersumbat, sedimentasi parah, tanggul saluran jebol, atau penutup drainase patah yang berisiko banjir.',
                'tingkat_keparahan_default' => 'tinggi',
                'bobot_prioritas' => 3, // Tinggi
            ],
            [
                'nama_kategori' => 'Penerangan Jalan Umum (PJU)',
                'deskripsi' => 'Lampu jalan mati, tiang PJU miring/rawan roboh, instalasi kabel putus yang mengganggu keselamatan pengguna jalan.',
                'tingkat_keparahan_default' => 'sedang',
                'bobot_prioritas' => 2, // Sedang
            ],
            [
                'nama_kategori' => 'Jembatan / Tanggul Air',
                'deskripsi' => 'Retakan struktur jembatan, railing pelindung rusak, tanggul penahan air sungai longsor atau mengikis jalan.',
                'tingkat_keparahan_default' => 'kritis',
                'bobot_prioritas' => 4, // Kritis (PBI-11 AC 2)
            ],
        ];

        foreach ($kategoriList as $kategori) {
            KategoriInfrastruktur::updateOrCreate(
                ['nama_kategori' => $kategori['nama_kategori']],
                $kategori
            );
        }
    }
}
