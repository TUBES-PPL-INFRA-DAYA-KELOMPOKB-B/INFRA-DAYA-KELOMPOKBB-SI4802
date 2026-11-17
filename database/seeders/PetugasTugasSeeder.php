<?php

namespace Database\Seeders;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class PetugasTugasSeeder extends Seeder
{
    /**
     * Run the database seeds for testing PBI-19 Petugas Lapangan.
     */
    public function run(): void
    {
        // 1. Ambil atau buat akun Petugas 1 & 2 serta Admin dan Warga
        $admin = User::firstOrCreate(
            ['email' => 'admin@dsdabm.bandung.go.id'],
            [
                'name' => 'Admin DSDABM Kota Bandung',
                'phone' => '081122334455',
                'role' => 'admin',
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );

        $petugas1 = User::firstOrCreate(
            ['email' => 'petugas1@dsdabm.bandung.go.id'],
            [
                'name' => 'Asep Sunandar (Petugas Jalan & Trotoar)',
                'phone' => '081234567891',
                'role' => 'petugas',
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );

        $petugas2 = User::firstOrCreate(
            ['email' => 'petugas2@dsdabm.bandung.go.id'],
            [
                'name' => 'Cecep Supriatna (Petugas Drainase & PJU)',
                'phone' => '081234567892',
                'role' => 'petugas',
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );

        $warga = User::firstOrCreate(
            ['email' => 'warga1@gmail.com'],
            [
                'name' => 'Daffa Pratama (Warga Coblong)',
                'phone' => '085612341111',
                'role' => 'warga',
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );

        // 2. Pastikan Kategori & Wilayah Ada
        $kecamatanCoblong = Kecamatan::firstOrCreate(
            ['nama_kecamatan' => 'Coblong'],
            ['kode_kecamatan' => 'BDG-003']
        );
        $kelurahanDago = Kelurahan::firstOrCreate(
            ['nama_kelurahan' => 'Dago', 'kecamatan_id' => $kecamatanCoblong->id],
            ['kode_kelurahan' => 'BDG-003-01']
        );
        $kelurahanLebak = Kelurahan::firstOrCreate(
            ['nama_kelurahan' => 'Lebak Siliwangi', 'kecamatan_id' => $kecamatanCoblong->id],
            ['kode_kelurahan' => 'BDG-003-02']
        );

        $kecamatanSukajadi = Kecamatan::firstOrCreate(
            ['nama_kecamatan' => 'Sukajadi'],
            ['kode_kecamatan' => 'BDG-005']
        );
        $kelurahanPasteur = Kelurahan::firstOrCreate(
            ['nama_kelurahan' => 'Pasteur', 'kecamatan_id' => $kecamatanSukajadi->id],
            ['kode_kelurahan' => 'BDG-005-01']
        );

        $kecamatanSumur = Kecamatan::firstOrCreate(
            ['nama_kecamatan' => 'Sumur Bandung'],
            ['kode_kecamatan' => 'BDG-008']
        );
        $kelurahanBraga = Kelurahan::firstOrCreate(
            ['nama_kelurahan' => 'Braga', 'kecamatan_id' => $kecamatanSumur->id],
            ['kode_kelurahan' => 'BDG-008-01']
        );

        $katJalan = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Jalan Raya/Trotoar'],
            ['bobot_prioritas' => 3, 'deskripsi' => 'Kerusakan fisik jalan, aspal berlubang, dan trotoar']
        );
        $katDrainase = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Saluran Drainase/Gorong-gorong'],
            ['bobot_prioritas' => 3, 'deskripsi' => 'Saluran mampet, gorong-gorong jebol atau banjir']
        );
        $katPju = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Penerangan Jalan Umum (PJU)'],
            ['bobot_prioritas' => 2, 'deskripsi' => 'Lampu PJU mati atau instalasi kabel rusak']
        );
        $katJembatan = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Jembatan/Tanggul Air'],
            ['bobot_prioritas' => 3, 'deskripsi' => 'Kerusakan fisik jembatan atau tanggul sungai']
        );

        // 3. Tugas 1 Petugas 1: Ditugaskan (Prioritas Tinggi) - FR-19.1 s/d FR-19.8
        $lap1 = LaporanKerusakan::firstOrCreate(
            ['nomor_tiket' => 'INFRA-20261107-0101'],
            [
                'warga_id' => $warga->id,
                'kelurahan_id' => $kelurahanDago->id,
                'kategori_id' => $katJalan->id,
                'judul' => 'Jalan Berlubang Parah di Simpang Dago',
                'deskripsi_kondisi' => 'Terdapat lubang sedalam 15 cm di jalur utama yang berpotensi menyebabkan kecelakaan roda dua saat hujan.',
                'latitude' => -6.88510000,
                'longitude' => 107.61350000,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
                'status' => 'diproses',
                'prioritas' => 'tinggi',
            ]
        );

        DisposisiPenugasan::firstOrCreate(
            ['nomor_surat_tugas' => 'SPT-20261107-1001'],
            [
                'laporan_id' => $lap1->id,
                'admin_id' => $admin->id,
                'petugas_id' => $petugas1->id,
                'tanggal_disposisi' => now()->subDays(2)->toDateString(),
                'tenggat_waktu' => now()->addDays(1)->toDateString(),
                'catatan_instruksi' => 'Segera lakukan perataan dan penambalan hotmix aspal di lokasi.',
                'status_penugasan' => 'ditugaskan',
            ]
        );

        RiwayatStatus::firstOrCreate(
            ['laporan_id' => $lap1->id, 'keterangan' => 'Laporan diverifikasi dan diterbitkan surat tugas ke Petugas Lapangan.'],
            [
                'user_id' => $admin->id,
                'status' => 'diproses',
                'created_at' => now()->subDays(2),
            ]
        );

        // 4. Tugas 2 Petugas 1: Diterima (Prioritas Tinggi) - FR-19.8
        $lap2 = LaporanKerusakan::firstOrCreate(
            ['nomor_tiket' => 'INFRA-20261107-0102'],
            [
                'warga_id' => $warga->id,
                'kelurahan_id' => $kelurahanPasteur->id,
                'kategori_id' => $katDrainase->id,
                'judul' => 'Saluran Drainase Mampet Meluap di Pasteur',
                'deskripsi_kondisi' => 'Air meluap ke badan jalan raya saat hujan deras karena sumbatan sampah dan endapan pasir tebal.',
                'latitude' => -6.89420000,
                'longitude' => 107.59540000,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
                'status' => 'diproses',
                'prioritas' => 'tinggi',
            ]
        );

        DisposisiPenugasan::firstOrCreate(
            ['nomor_surat_tugas' => 'SPT-20261107-1002'],
            [
                'laporan_id' => $lap2->id,
                'admin_id' => $admin->id,
                'petugas_id' => $petugas1->id,
                'tanggal_disposisi' => now()->subDays(1)->toDateString(),
                'tenggat_waktu' => now()->addDays(2)->toDateString(),
                'catatan_instruksi' => 'Bawa armada pengeruk dan lakukan pembersihan sedimen gorong-gorong.',
                'status_penugasan' => 'diterima',
            ]
        );

        RiwayatStatus::firstOrCreate(
            ['laporan_id' => $lap2->id, 'keterangan' => 'Tugas penanganan laporan diterima oleh petugas Asep Sunandar.'],
            [
                'user_id' => $petugas1->id,
                'status' => 'diproses',
                'created_at' => now()->subHours(12),
            ]
        );

        // 5. Tugas 3 Petugas 1: Sedang Dikerjakan (Prioritas Sedang)
        $lap3 = LaporanKerusakan::firstOrCreate(
            ['nomor_tiket' => 'INFRA-20261107-0103'],
            [
                'warga_id' => $warga->id,
                'kelurahan_id' => $kelurahanBraga->id,
                'kategori_id' => $katPju->id,
                'judul' => 'Lampu PJU Padam di Sepanjang Kawasan Braga',
                'deskripsi_kondisi' => 'Tiga tiang lampu penerangan jalan umum mati total, jalanan gelap di malam hari.',
                'latitude' => -6.91750000,
                'longitude' => 107.60970000,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
                'status' => 'diproses',
                'prioritas' => 'sedang',
            ]
        );

        DisposisiPenugasan::firstOrCreate(
            ['nomor_surat_tugas' => 'SPT-20261107-1003'],
            [
                'laporan_id' => $lap3->id,
                'admin_id' => $admin->id,
                'petugas_id' => $petugas1->id,
                'tanggal_disposisi' => now()->subDays(3)->toDateString(),
                'tenggat_waktu' => now()->addDays(2)->toDateString(),
                'catatan_instruksi' => 'Periksa box sekring dan ganti lampu LED yang putus.',
                'status_penugasan' => 'sedang_dikerjakan',
            ]
        );

        RiwayatStatus::firstOrCreate(
            ['laporan_id' => $lap3->id, 'keterangan' => 'Petugas Asep Sunandar telah memulai penanganan teknis di lokasi.'],
            [
                'user_id' => $petugas1->id,
                'status' => 'diproses',
                'created_at' => now()->subHours(4),
            ]
        );

        // 6. Tugas 4 Petugas 1: Selesai (Prioritas Rendah)
        $lap4 = LaporanKerusakan::firstOrCreate(
            ['nomor_tiket' => 'INFRA-20261107-0104'],
            [
                'warga_id' => $warga->id,
                'kelurahan_id' => $kelurahanLebak->id,
                'kategori_id' => $katJembatan->id,
                'judul' => 'Baut Pengaman Pembatas Jembatan Longgar',
                'deskripsi_kondisi' => 'Baut railing jembatan penyeberangan longgar sehingga pagar sedikit bergoyang.',
                'latitude' => -6.88930000,
                'longitude' => 107.60800000,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
                'status' => 'selesai',
                'prioritas' => 'rendah',
                'selesai_at' => now()->subDay(),
            ]
        );

        DisposisiPenugasan::firstOrCreate(
            ['nomor_surat_tugas' => 'SPT-20261107-1004'],
            [
                'laporan_id' => $lap4->id,
                'admin_id' => $admin->id,
                'petugas_id' => $petugas1->id,
                'tanggal_disposisi' => now()->subDays(5)->toDateString(),
                'tenggat_waktu' => now()->subDays(1)->toDateString(),
                'catatan_instruksi' => 'Kencangkan baut baja dan berikan plat penguat tambahan.',
                'status_penugasan' => 'selesai',
                'foto_bukti_sesudah' => 'laporan/sesudah/sample.jpg',
                'catatan_perbaikan' => 'Pengencangan dan pengelasan plat baja pengaman jembatan selesai 100%.',
                'tanggal_selesai' => now()->subDay()->toDateString(),
            ]
        );

        RiwayatStatus::firstOrCreate(
            ['laporan_id' => $lap4->id, 'keterangan' => 'Perbaikan tuntas oleh Asep Sunandar.'],
            [
                'user_id' => $petugas1->id,
                'status' => 'selesai',
                'created_at' => now()->subDay(),
            ]
        );

        // 7. Tugas Petugas 2 (Cecep) untuk menguji isolasi hak akses (FR-19.1 & FR-19.9)
        $lapPetugas2 = LaporanKerusakan::firstOrCreate(
            ['nomor_tiket' => 'INFRA-20261107-0201'],
            [
                'warga_id' => $warga->id,
                'kelurahan_id' => $kelurahanPasteur->id,
                'kategori_id' => $katPju->id,
                'judul' => 'Tiang Lampu Miring di Tikungan Sukajadi',
                'deskripsi_kondisi' => 'Tiang lampu miring 30 derajat membahayakan pengendara yang melintas.',
                'latitude' => -6.89000000,
                'longitude' => 107.59000000,
                'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
                'status' => 'diproses',
                'prioritas' => 'tinggi',
            ]
        );

        DisposisiPenugasan::firstOrCreate(
            ['nomor_surat_tugas' => 'SPT-20261107-2001'],
            [
                'laporan_id' => $lapPetugas2->id,
                'admin_id' => $admin->id,
                'petugas_id' => $petugas2->id,
                'tanggal_disposisi' => now()->toDateString(),
                'tenggat_waktu' => now()->addDays(2)->toDateString(),
                'catatan_instruksi' => 'Tegakkan kembali tiang lampu dan cor ulang pondasinya.',
                'status_penugasan' => 'ditugaskan',
            ]
        );
    }
}

