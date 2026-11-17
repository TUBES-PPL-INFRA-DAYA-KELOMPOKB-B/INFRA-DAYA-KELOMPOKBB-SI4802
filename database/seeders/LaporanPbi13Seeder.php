<?php

namespace Database\Seeders;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LaporanPbi13Seeder extends Seeder
{
    /**
     * Run the database seeds for PBI-13 demo data.
     */
    public function run(): void
    {
        // 1. Pastikan User Admin dan Warga tersedia
        $admin = User::firstOrCreate(
            ['email' => 'admin@infradaya.bandung.go.id'],
            [
                'name' => 'Admin DSDABM',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
                'phone' => '081234567890',
            ]
        );

        $warga1 = User::firstOrCreate(
            ['email' => 'warga.dago@example.com'],
            [
                'name' => 'Ahmad Warga',
                'password' => bcrypt('password'),
                'role' => 'warga',
                'is_active' => true,
                'phone' => '081298765432',
            ]
        );

        $warga2 = User::firstOrCreate(
            ['email' => 'warga.antapani@example.com'],
            [
                'name' => 'Siti Warga',
                'password' => bcrypt('password'),
                'role' => 'warga',
                'is_active' => true,
                'phone' => '081311223344',
            ]
        );

        // 2. Pastikan Kecamatan & Kelurahan tersedia
        $kecCoblong = Kecamatan::firstOrCreate(
            ['kode_kecamatan' => 'BDG-001'],
            ['nama_kecamatan' => 'Coblong']
        );
        $kelDago = Kelurahan::firstOrCreate(
            ['kode_kelurahan' => 'BDG-001-01'],
            ['kecamatan_id' => $kecCoblong->id, 'nama_kelurahan' => 'Dago']
        );

        $kecAntapani = Kecamatan::firstOrCreate(
            ['kode_kecamatan' => 'BDG-002'],
            ['nama_kecamatan' => 'Antapani']
        );
        $kelAntapaniKulon = Kelurahan::firstOrCreate(
            ['kode_kelurahan' => 'BDG-002-01'],
            ['kecamatan_id' => $kecAntapani->id, 'nama_kelurahan' => 'Antapani Kulon']
        );

        $kecSukajadi = Kecamatan::firstOrCreate(
            ['kode_kecamatan' => 'BDG-003'],
            ['nama_kecamatan' => 'Sukajadi']
        );
        $kelSukajadi = Kelurahan::firstOrCreate(
            ['kode_kelurahan' => 'BDG-003-01'],
            ['kecamatan_id' => $kecSukajadi->id, 'nama_kelurahan' => 'Pasteur']
        );

        // 3. Pastikan Kategori Infrastruktur tersedia
        $katJalan = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Jalan'],
            ['deskripsi' => 'Kerusakan fisik jalan, aspal berlubang', 'bobot_prioritas' => 2]
        );

        $katDrainase = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Drainase'],
            ['deskripsi' => 'Saluran air meluap atau tersumbat', 'bobot_prioritas' => 2]
        );

        $katPJU = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'PJU'],
            ['deskripsi' => 'Lampu penerangan jalan umum mati', 'bobot_prioritas' => 1]
        );

        $katJembatan = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Jembatan'],
            ['deskripsi' => 'Kerusakan fisik konstruksi jembatan', 'bobot_prioritas' => 3]
        );

        // 4. Data Contoh Laporan Beragam Status, Kategori, dan Kecamatan
        $samples = [
            [
                'nomor_tiket' => 'INFRA-20261101-0001',
                'warga_id' => $warga1->id,
                'kelurahan_id' => $kelDago->id,
                'kategori_id' => $katJalan->id,
                'judul' => 'Lubang Aspal Parah Depan Pasar Simpang Dago',
                'deskripsi_kondisi' => 'Diameter lubang sekitar 70cm dengan kedalaman 15cm membahayakan pemotor.',
                'latitude' => -6.8850,
                'longitude' => 107.6150,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample1.jpg',
                'status' => 'menunggu_verifikasi',
                'prioritas' => 'sedang',
                'created_at' => Carbon::now()->subDays(6), // Terlama 1
            ],
            [
                'nomor_tiket' => 'INFRA-20261102-0002',
                'warga_id' => $warga2->id,
                'kelurahan_id' => $kelAntapaniKulon->id,
                'kategori_id' => $katDrainase->id,
                'judul' => 'Saluran Drainase Tersumbat Sampah di Jl. Purwakarta',
                'deskripsi_kondisi' => 'Saat hujan air meluap ke badan jalan setinggi mata kaki.',
                'latitude' => -6.9150,
                'longitude' => 107.6550,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample2.jpg',
                'status' => 'menunggu_verifikasi',
                'prioritas' => 'sedang',
                'created_at' => Carbon::now()->subDays(5), // Terlama 2
            ],
            [
                'nomor_tiket' => 'INFRA-20261103-0003',
                'warga_id' => $warga1->id,
                'kelurahan_id' => $kelSukajadi->id,
                'kategori_id' => $katPJU->id,
                'judul' => 'PJU Padam Sepanjang 200m Jalur Pasteur',
                'deskripsi_kondisi' => 'Tiga tiang lampu PJU mati berurutan sehingga area sangat gelap saat malam.',
                'latitude' => -6.8920,
                'longitude' => 107.5950,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample3.jpg',
                'status' => 'menunggu_verifikasi',
                'prioritas' => 'rendah',
                'created_at' => Carbon::now()->subDays(3), // Terlama 3
            ],
            [
                'nomor_tiket' => 'INFRA-20261104-0004',
                'warga_id' => $warga2->id,
                'kelurahan_id' => $kelAntapaniKulon->id,
                'kategori_id' => $katJalan->id,
                'judul' => 'Amblas Jalan Gang Subur Antapani',
                'deskripsi_kondisi' => 'Jalan paving amblas akibat gerusan air pipa PDAM.',
                'latitude' => -6.9180,
                'longitude' => 107.6580,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample4.jpg',
                'status' => 'diproses',
                'prioritas' => 'tinggi',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'nomor_tiket' => 'INFRA-20261105-0005',
                'warga_id' => $warga1->id,
                'kelurahan_id' => $kelDago->id,
                'kategori_id' => $katJembatan->id,
                'judul' => 'Pagar Pembatas Jembatan Cikapundung Retak',
                'deskripsi_kondisi' => 'Railing jembatan goyang dan retak setelah tertabrak kendaraan.',
                'latitude' => -6.8830,
                'longitude' => 107.6120,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample5.jpg',
                'status' => 'selesai',
                'prioritas' => 'tinggi',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'nomor_tiket' => 'INFRA-20261106-0006',
                'warga_id' => $warga2->id,
                'kelurahan_id' => $kelSukajadi->id,
                'kategori_id' => $katJalan->id,
                'judul' => 'Laporan Uji Coba Luar Wilayah',
                'deskripsi_kondisi' => 'Foto tidak jelas dan lokasi bukan di Kota Bandung.',
                'latitude' => -6.8900,
                'longitude' => 107.5900,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample6.jpg',
                'status' => 'ditolak',
                'prioritas' => 'rendah',
                'alasan_penolakan' => 'Lokasi berada di luar wilayah Kota Bandung dan foto tidak relevan.',
                'created_at' => Carbon::now()->subHours(5),
            ],
        ];

        foreach ($samples as $sample) {
            LaporanKerusakan::updateOrCreate(
                ['nomor_tiket' => $sample['nomor_tiket']],
                $sample
            );
        }
    }
}

