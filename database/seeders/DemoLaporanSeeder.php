<?php

namespace Database\Seeders;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\UmpanBalik;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoLaporanSeeder extends Seeder
{
    public function run(): void
    {
        $warga = User::where('role', 'warga')->first() ?? User::factory()->create(['role' => 'warga', 'email' => 'warga1@gmail.com']);
        $petugas = User::where('role', 'petugas')->first() ?? User::factory()->create(['role' => 'petugas', 'email' => 'petugas1@dsdabm.bandung.go.id']);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'email' => 'admin@dsdabm.bandung.go.id']);

        $kategoriJalan = KategoriInfrastruktur::where('nama_kategori', 'like', '%Jalan%')->first() ?? KategoriInfrastruktur::first();
        $kategoriDrainase = KategoriInfrastruktur::where('nama_kategori', 'like', '%Drainase%')->first() ?? KategoriInfrastruktur::first();
        $kategoriPju = KategoriInfrastruktur::where('nama_kategori', 'like', '%PJU%')->first() ?? KategoriInfrastruktur::first();

        $kelurahanDago = Kelurahan::where('nama_kelurahan', 'like', '%Dago%')->first() ?? Kelurahan::first();
        $kelurahanCihapit = Kelurahan::where('nama_kelurahan', 'like', '%Cihapit%')->first() ?? Kelurahan::skip(1)->first() ?? Kelurahan::first();

        // 1. Laporan Menunggu Verifikasi
        $lap1 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.now()->format('Ymd').'-0001',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahanDago->id,
            'kategori_id' => $kategoriJalan->id,
            'judul' => 'Jalan Berlubang Parah di Simpang Dago Atas',
            'deskripsi_kondisi' => "Terdapat lubang berdiameter 60 cm dengan kedalaman 10 cm yang sangat membahayakan pengendara roda dua saat malam hari.\n[Patokan Lokasi: Depan Kafe Simpang Dago, 50m sebelum lampu merah]",
            'latitude' => -6.88500000,
            'longitude' => 107.61400000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'tinggi',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $lap1->id,
            'user_id' => $warga->id,
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Laporan kerusakan diajukan oleh pelapor.',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // 2. Laporan Drainase Menunggu Verifikasi
        $lap2 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.now()->format('Ymd').'-0002',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahanCihapit->id,
            'kategori_id' => $kategoriDrainase->id,
            'judul' => 'Saluran Drainase Meluap Tersumbat Sedimen',
            'deskripsi_kondisi' => "Gorong-gorong saluran pembuangan tersumbat tumpukan sedimen lumpur dan sampah sehingga air meluap ke badan jalan.\n[Patokan Lokasi: Samping Pasar Cihapit, RW 04]",
            'latitude' => -6.90800000,
            'longitude' => 107.62200000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'sedang',
            'created_at' => Carbon::now()->subHours(5),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $lap2->id,
            'user_id' => $warga->id,
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Laporan kerusakan diajukan oleh pelapor.',
            'created_at' => Carbon::now()->subHours(5),
        ]);

        // 3. Laporan Diproses / Sedang Dikerjakan Petugas
        $lap3 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.now()->subDay()->format('Ymd').'-0003',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahanDago->id,
            'kategori_id' => $kategoriPju->id,
            'judul' => 'Lampu PJU Padam di Sepanjang Jalur Juanda',
            'deskripsi_kondisi' => "Sebanyak 3 titik tiang PJU padam total sejak dua hari lalu sehingga jalanan gelap gulita.\n[Patokan Lokasi: Depan Taman Dago, seberang SPBU]",
            'latitude' => -6.89200000,
            'longitude' => 107.61600000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
            'created_at' => Carbon::now()->subDay(),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $lap3->id,
            'user_id' => $warga->id,
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Laporan diajukan oleh warga.',
            'created_at' => Carbon::now()->subDay(),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $lap3->id,
            'user_id' => $admin->id,
            'status' => 'diproses',
            'keterangan' => 'Laporan disetujui oleh Admin DSDABM dan diterbitkan surat perintah tugas.',
            'created_at' => Carbon::now()->subHours(18),
        ]);

        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-'.now()->format('Ymd').'-0001',
            'laporan_id' => $lap3->id,
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'catatan_instruksi' => 'Segera lakukan pengecekan trafo dan pergantian bohlam LED PJU 90 Watt.',
            'status_penugasan' => 'sedang_dikerjakan',
            'tanggal_disposisi' => Carbon::now()->subHours(18)->toDateString(),
            'tenggat_waktu' => Carbon::now()->addDays(2)->toDateString(),
            'created_at' => Carbon::now()->subHours(18),
        ]);

        // 4. Laporan Selesai & Berating
        $lap4 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.now()->subDays(3)->format('Ymd').'-0004',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahanCihapit->id,
            'kategori_id' => $kategoriJalan->id,
            'judul' => 'Perbaikan Aspal Amblas di Jl. Riau',
            'deskripsi_kondisi' => "Jalan amblas sedalam 15 cm di dekat perempatan.\n[Patokan Lokasi: Depan Heritage Outlet]",
            'latitude' => -6.90900000,
            'longitude' => 107.61800000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah/sample.jpg',
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'catatan_perbaikan' => 'Telah dilakukan penambalan hotmix aspal tebal 8 cm dan pemadatan roller mini. Jalan sudah mulus kembali.',
            'selesai_at' => Carbon::now()->subDay(),
            'created_at' => Carbon::now()->subDays(3),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $lap4->id,
            'user_id' => $petugas->id,
            'status' => 'selesai',
            'keterangan' => 'Pekerjaan perbaikan fisik di lapangan telah tuntas diselesaikan oleh petugas.',
            'created_at' => Carbon::now()->subDay(),
        ]);

        UmpanBalik::create([
            'laporan_id' => $lap4->id,
            'warga_id' => $warga->id,
            'rating' => 5,
            'kecepatan_penanganan' => 5,
            'kualitas_perbaikan' => 5,
            'komentar' => 'Respon dinas luar biasa cepat! Dalam 2 hari jalanan berlubang sudah rapi dan mulus kembali. Terima kasih DSDABM Kota Bandung!',
            'created_at' => Carbon::now()->subHours(12),
        ]);

        // 5. Laporan Dibatalkan Warga (PBI-9)
        $lap5 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.now()->subDays(2)->format('Ymd').'-0005',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahanDago->id,
            'kategori_id' => $kategoriJalan->id,
            'judul' => 'Pembersihan Kerikil Pasca Galian Kabel',
            'deskripsi_kondisi' => "Banyak kerikil berserakan sisa galian kabel.\n[Patokan Lokasi: Depan Toko Buku Dago]",
            'latitude' => -6.89000000,
            'longitude' => 107.61500000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'dibatalkan',
            'prioritas' => 'sedang',
            'alasan_pembatalan' => 'Kerikil dan puing galian telah dibersihkan secara gotong royong oleh warga setempat.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $lap5->id,
            'user_id' => $warga->id,
            'status' => 'dibatalkan',
            'keterangan' => 'Laporan dibatalkan oleh pelapor. Alasan: Kerikil dan puing galian telah dibersihkan secara gotong royong oleh warga setempat.',
            'created_at' => Carbon::now()->subDays(2)->addHours(3),
        ]);
    }
}
