<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\HasilPerbaikan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pengujian Fitur PBI-20: Pembaruan Progres & Unggah Foto Hasil Perbaikan Lapangan.
 * Mengacu pada PRD INFRA-DAYA Seksi 4.2 (FR-20.1 - FR-20.10) & Seksi 6 (Tabel hasil_perbaikan).
 */
class PBI20UpdateProgresFotoHasilTest extends TestCase
{
    use RefreshDatabase;

    private User $petugasA;

    private User $petugasB;

    private User $admin;

    private User $warga;

    private Kecamatan $kecamatan;

    private Kelurahan $kelurahan;

    private KategoriInfrastruktur $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugasA = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Bambang Petugas']);
        $this->petugasB = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Doni Petugas']);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $this->kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-005',
            'nama_kecamatan' => 'Coblong',
        ]);
        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => 'BDG-005-01',
            'nama_kelurahan' => 'Sekeloa',
        ]);
        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Drainase',
            'deskripsi' => 'Saluran drainase tersumbat',
            'bobot_prioritas' => 3,
        ]);
    }

    private function createDisposisi(
        User $petugas,
        string $statusPenugasan = 'sedang_dikerjakan',
        string $statusLaporan = 'diproses'
    ): DisposisiPenugasan {
        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.uniqid(),
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Pembersihan saluran drainase mampet',
            'deskripsi_kondisi' => 'Air meluap ke badan jalan saat hujan deras.',
            'latitude' => -6.8901,
            'longitude' => 107.6105,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => $statusLaporan,
            'prioritas' => 'tinggi',
        ]);

        return DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-'.uniqid(),
            'laporan_id' => $laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(3)->toDateString(),
            'catatan_instruksi' => 'Bersihkan sedimentasi lumpur sepanjang 20 meter.',
            'status_penugasan' => $statusPenugasan,
        ]);
    }

    /**
     * TC-20-01 (FR-20.1 & FR-20.2): Petugas dapat memperbarui catatan progres pengerjaan lapangan.
     */
    public function test_petugas_can_update_progress_notes(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'diterima');

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Sedang dilakukan pengerukan endapan lumpur tahap pertama sepanjang 10 meter.',
            ]);

        $response->assertRedirect(route('petugas.tugas.show', $disposisi->id));
        $response->assertSessionHas('success');

        $disposisi->refresh();
        $this->assertEquals('sedang_dikerjakan', $disposisi->status_penugasan);
        $this->assertEquals('diproses', $disposisi->laporan->status);

        // Verifikasi tersimpan di tabel hasil_perbaikan (PRD Bagian 6)
        $this->assertDatabaseHas('hasil_perbaikan', [
            'disposisi_id' => $disposisi->id,
            'petugas_id' => $this->petugasA->id,
            'catatan_perbaikan' => 'Sedang dilakukan pengerukan endapan lumpur tahap pertama sepanjang 10 meter.',
        ]);
    }

    /**
     * TC-20-02 (FR-20.3 & FR-20.5): Petugas dapat mengunggah foto progres/hasil dengan format valid (.jpg, .jpeg, .png).
     */
    public function test_petugas_can_upload_valid_photo_and_saved_to_hasil_perbaikan(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $file = UploadedFile::fake()->image('hasil_kerja.jpg', 800, 600)->size(2048); // 2 MB

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Progres pengerjaan 50%, dinding semen mulai diperbaiki.',
                'foto_bukti_sesudah' => $file,
            ]);

        $response->assertRedirect(route('petugas.tugas.show', $disposisi->id));

        $hasilPerbaikan = HasilPerbaikan::where('disposisi_id', $disposisi->id)->first();
        $this->assertNotNull($hasilPerbaikan);
        $this->assertNotNull($hasilPerbaikan->foto_bukti_sesudah);
        $this->assertStringContainsString('laporan/sesudah/', $hasilPerbaikan->foto_bukti_sesudah);

        // Verifikasi berkas fisik ada di storage
        Storage::disk('public')->assertExists($hasilPerbaikan->foto_bukti_sesudah);
    }

    /**
     * TC-20-03 (FR-20.4): Sistem menolak unggahan foto yang ukurannya melebihi 5 MB (5120 KB).
     */
    public function test_upload_photo_exceeding_5mb_is_rejected(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $oversizedFile = UploadedFile::fake()->image('large_foto.jpg')->size(5200); // 5.2 MB > 5120 KB

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Coba upload foto resolusi tinggi.',
                'foto_bukti_sesudah' => $oversizedFile,
            ]);

        $response->assertSessionHasErrors('foto_bukti_sesudah');
        $this->assertDatabaseMissing('hasil_perbaikan', [
            'disposisi_id' => $disposisi->id,
        ]);
    }

    /**
     * TC-20-04 (FR-20.3): Sistem menolak unggahan berkas non-gambar (misal .pdf atau .txt).
     */
    public function test_upload_non_image_file_is_rejected(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Coba upload dokumen PDF.',
                'foto_bukti_sesudah' => $pdfFile,
            ]);

        $response->assertSessionHasErrors('foto_bukti_sesudah');
    }

    /**
     * TC-20-05 (FR-20.6): Setiap perubahan progres atau status tercatat otomatis pada log riwayat status.
     */
    public function test_progress_update_logs_audit_history(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'diterima');

        $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Pemasangan rambu pengaman dan pembersihan saluran.',
            ]);

        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $disposisi->laporan_id,
            'user_id' => $this->petugasA->id,
            'status' => 'diproses',
        ]);

        $riwayat = RiwayatStatus::where('laporan_id', $disposisi->laporan_id)->latest('id')->first();
        $this->assertNotNull($riwayat);
        $this->assertStringContainsString('Bambang Petugas', $riwayat->keterangan);
        $this->assertStringContainsString('Pemasangan rambu pengaman', $riwayat->keterangan);
    }

    /**
     * TC-20-06 (FR-20.7): Status laporan kerusakan sinkron menjadi 'diproses' dan 'selesai'.
     */
    public function test_laporan_status_synchronization(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'diterima', 'diproses');

        // Update progres -> pastikan tetap 'diproses'
        $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Pembersihan 80%',
            ]);

        $disposisi->refresh();
        $this->assertEquals('diproses', $disposisi->laporan->status);

        // Selesaikan tugas -> status laporan otomatis menjadi 'selesai'
        $file = UploadedFile::fake()->image('final.png')->size(1000);
        $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'foto_bukti_sesudah' => $file,
                'catatan_perbaikan' => 'Pekerjaan drainase tuntas 100%, aliran air lancar.',
            ]);

        $disposisi->refresh();
        $this->assertEquals('selesai', $disposisi->status_penugasan);
        $this->assertEquals('selesai', $disposisi->laporan->status);
        $this->assertNotNull($disposisi->laporan->selesai_at);

        // Verifikasi data tabel hasil_perbaikan juga tersinkron dengan tanggal selesai
        $this->assertDatabaseHas('hasil_perbaikan', [
            'disposisi_id' => $disposisi->id,
            'petugas_id' => $this->petugasA->id,
            'catatan_perbaikan' => 'Pekerjaan drainase tuntas 100%, aliran air lancar.',
        ]);
    }

    /**
     * TC-20-07 (FR-20.8): Petugas lain dilarang memperbarui progres penugasan (IDOR prevention -> 403).
     */
    public function test_other_petugas_cannot_update_progress_idor_prevention(): void
    {
        $disposisiA = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');

        // Petugas B mencoba mengupdate tugas Petugas A
        $response = $this->actingAs($this->petugasB)
            ->post(route('petugas.tugas.progres', $disposisiA->id), [
                'catatan_perbaikan' => 'Pembaruan tidak sah dari petugas lain.',
            ]);

        $response->assertStatus(403);
    }

    /**
     * TC-20-08 (FR-20.8): Petugas tidak dapat memperbarui tugas yang sudah ditutup/selesai (422).
     */
    public function test_cannot_update_progress_on_completed_task(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'selesai', 'selesai');

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Mencoba mengubah catatan pada tugas selesai.',
            ]);

        $response->assertStatus(422);
    }

    /**
     * TC-20-09 (FR-20.9 & Bagian 6 PRD): Petugas dapat mengganti foto sebelum tugas ditutup dan menjaga integritas 1:1.
     */
    public function test_petugas_can_replace_photo_and_maintains_single_hasil_perbaikan_record(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');

        // Unggah foto awal
        $foto1 = UploadedFile::fake()->image('foto_awal.jpg')->size(1000);
        $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Foto dokumentasi awal penanganan.',
                'foto_bukti_sesudah' => $foto1,
            ]);

        $this->assertEquals(1, HasilPerbaikan::where('disposisi_id', $disposisi->id)->count());
        $initialPhotoPath = HasilPerbaikan::where('disposisi_id', $disposisi->id)->value('foto_bukti_sesudah');

        // Ganti foto dengan foto baru (FR-20.9)
        $foto2 = UploadedFile::fake()->image('foto_revisi.png')->size(1500);
        $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Foto diperbarui dengan hasil perbaikan yang lebih detail.',
                'foto_bukti_sesudah' => $foto2,
            ]);

        // Verifikasi tetap 1 record (1:1) dan path foto diperbarui
        $this->assertEquals(1, HasilPerbaikan::where('disposisi_id', $disposisi->id)->count());
        $updatedHasil = HasilPerbaikan::where('disposisi_id', $disposisi->id)->first();
        $this->assertNotEquals($initialPhotoPath, $updatedHasil->foto_bukti_sesudah);
        $this->assertEquals('Foto diperbarui dengan hasil perbaikan yang lebih detail.', $updatedHasil->catatan_perbaikan);
    }
}

