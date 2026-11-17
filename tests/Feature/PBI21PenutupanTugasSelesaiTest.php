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
use App\Services\PetugasService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pengujian Fitur PBI-21: Aksi Tutup Tugas (Selesai).
 * Mengacu pada PRD INFRA-DAYA Bagian 4.3 (FR-21.1 - FR-21.8) & Bagian 6 (Tabel hasil_perbaikan).
 */
class PBI21PenutupanTugasSelesaiTest extends TestCase
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
        $this->petugasA = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Baso Petugas']);
        $this->petugasB = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Rian Petugas']);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $this->kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-006',
            'nama_kecamatan' => 'Coblong',
        ]);
        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => 'BDG-006-01',
            'nama_kelurahan' => 'Sadang Serang',
        ]);
        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Berlubang',
            'deskripsi' => 'Perbaikan jalan aspal',
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
            'judul' => 'Perbaikan jalan aspal amblas',
            'deskripsi_kondisi' => 'Aspal amblas sedalam 10 cm mengganggu arus lalu lintas.',
            'latitude' => -6.8920,
            'longitude' => 107.6140,
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
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Lakukan pengurukan dan perataan aspal hotmix.',
            'status_penugasan' => $statusPenugasan,
        ]);
    }

    /**
     * TC-21-01 (FR-21.1, FR-21.4): Menutup tugas dengan foto baru mengubah status disposisi, laporan, tanggal_selesai, dan hasil_perbaikan dalam 1 transaksi.
     */
    public function test_complete_task_with_new_photo_updates_all_statuses_and_timestamps(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $file = UploadedFile::fake()->image('hasil_selesai.jpg')->size(1500);

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'foto_bukti_sesudah' => $file,
                'catatan_perbaikan' => 'Pengaspalan hotmix tuntas dengan ketebalan 5 cm, jalan aman dilalui kendaraan.',
            ]);

        $response->assertRedirect(route('petugas.tugas.show', $disposisi->id));
        $response->assertSessionHas('success');

        $disposisi->refresh();
        $this->assertEquals('selesai', $disposisi->status_penugasan);
        $this->assertEquals(now()->toDateString(), $disposisi->tanggal_selesai->toDateString());
        $this->assertEquals('selesai', $disposisi->laporan->status);
        $this->assertNotNull($disposisi->laporan->selesai_at);

        // Verifikasi tabel hasil_perbaikan (FR-21.4 & Bagian 6)
        $this->assertDatabaseHas('hasil_perbaikan', [
            'disposisi_id' => $disposisi->id,
            'petugas_id' => $this->petugasA->id,
            'catatan_perbaikan' => 'Pengaspalan hotmix tuntas dengan ketebalan 5 cm, jalan aman dilalui kendaraan.',
            'tanggal_selesai' => now()->toDateString(),
        ]);

        $hasilPerbaikan = HasilPerbaikan::where('disposisi_id', $disposisi->id)->first();
        $this->assertNotNull($hasilPerbaikan);
        Storage::disk('public')->assertExists($hasilPerbaikan->foto_bukti_sesudah);
    }

    /**
     * TC-21-02 (FR-21.2): Menutup tugas menggunakan foto yang sudah tersimpan sebelumnya saat progres pengerjaan (tanpa perlu unggah ulang).
     */
    public function test_complete_task_using_existing_interim_photo(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');

        // Simpan foto awal pada progres
        HasilPerbaikan::create([
            'disposisi_id' => $disposisi->id,
            'petugas_id' => $this->petugasA->id,
            'foto_bukti_sesudah' => 'laporan/sesudah/sample_interim.jpg',
            'catatan_perbaikan' => 'Foto progres awal',
        ]);
        $disposisi->update(['foto_bukti_sesudah' => 'laporan/sesudah/sample_interim.jpg']);

        // Tutup tugas hanya dengan catatan tanpa mengirim file foto baru
        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'catatan_perbaikan' => 'Pekerjaan perbaikan jalan telah diselesaikan dengan standar teknis dinas.',
            ]);

        $response->assertRedirect(route('petugas.tugas.show', $disposisi->id));
        $disposisi->refresh();
        $this->assertEquals('selesai', $disposisi->status_penugasan);

        $hasilPerbaikan = HasilPerbaikan::where('disposisi_id', $disposisi->id)->first();
        $this->assertEquals('laporan/sesudah/sample_interim.jpg', $hasilPerbaikan->foto_bukti_sesudah);
        $this->assertEquals('Pekerjaan perbaikan jalan telah diselesaikan dengan standar teknis dinas.', $hasilPerbaikan->catatan_perbaikan);
        $this->assertEquals(now()->toDateString(), $hasilPerbaikan->tanggal_selesai->toDateString());
    }

    /**
     * TC-21-03 (FR-21.2): Penutupan tugas ditolak jika tidak ada foto hasil perbaikan (baik baru maupun lama).
     */
    public function test_complete_task_without_any_photo_is_rejected(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');

        // Coba tutup tanpa file dan belum ada foto di hasil_perbaikan
        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'catatan_perbaikan' => 'Mencoba menyelesaikan tanpa menyertakan foto bukti.',
            ]);

        $response->assertSessionHasErrors('foto_bukti_sesudah');
        $disposisi->refresh();
        $this->assertEquals('sedang_dikerjakan', $disposisi->status_penugasan);
    }

    /**
     * TC-21-04 (FR-21.5): Penutupan tugas dicatat pada audit log riwayat status dengan timestamp dan nama petugas.
     */
    public function test_complete_task_records_audit_log(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $file = UploadedFile::fake()->image('audit_test.jpg')->size(500);

        $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'foto_bukti_sesudah' => $file,
                'catatan_perbaikan' => 'Pekerjaan saluran rampung 100 persen.',
            ]);

        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $disposisi->laporan_id,
            'user_id' => $this->petugasA->id,
            'status' => 'selesai',
        ]);

        $log = RiwayatStatus::where('laporan_id', $disposisi->laporan_id)
            ->where('status', 'selesai')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Baso Petugas', $log->keterangan);
        $this->assertStringContainsString('Pekerjaan saluran rampung 100 persen.', $log->keterangan);
    }

    /**
     * TC-21-05 (FR-21.7): Pencegahan penutupan ganda (double submission) dan memastikan relasi 1:1 tetap terjaga.
     */
    public function test_double_completion_is_rejected_and_maintains_single_hasil_perbaikan(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'selesai', 'selesai');
        HasilPerbaikan::create([
            'disposisi_id' => $disposisi->id,
            'petugas_id' => $this->petugasA->id,
            'foto_bukti_sesudah' => 'laporan/sesudah/first.jpg',
            'catatan_perbaikan' => 'Sudah selesai pertama kali',
            'tanggal_selesai' => now()->toDateString(),
        ]);

        // Coba tutup lagi
        $file = UploadedFile::fake()->image('duplicate.jpg')->size(500);
        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'foto_bukti_sesudah' => $file,
                'catatan_perbaikan' => 'Mencoba menutup ulang tugas yang sudah selesai.',
            ]);

        $response->assertStatus(422);

        // Pastikan hanya tetap ada 1 record hasil perbaikan (1:1)
        $this->assertEquals(1, HasilPerbaikan::where('disposisi_id', $disposisi->id)->count());
    }

    /**
     * TC-21-06 (FR-21.4): Rollback transaksi jika terjadi kesalahan sistem di tengah proses.
     */
    public function test_transaction_rolls_back_cleanly_on_failure(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $file = UploadedFile::fake()->image('rollback.jpg')->size(500);

        // Pasang hook exception pada event saving RiwayatStatus untuk memicu error di tengah transaksi
        RiwayatStatus::saving(function () {
            throw new Exception('Simulasi kegagalan database di tengah transaksi penutupan.');
        });

        $petugasService = app(PetugasService::class);

        try {
            $petugasService->completeTask(
                $disposisi->id,
                $this->petugasA->id,
                $file,
                'Catatan yang seharusnya di-rollback.'
            );
            $this->fail('Harusnya melempar exception.');
        } catch (Exception $e) {
            $this->assertEquals('Simulasi kegagalan database di tengah transaksi penutupan.', $e->getMessage());
        }

        // Hapus event listener agar tidak mempengaruhi test lain
        RiwayatStatus::flushEventListeners();

        // Verifikasi tidak ada perubahan parsial pada database (rollback sukses)
        $disposisi->refresh();
        $this->assertEquals('sedang_dikerjakan', $disposisi->status_penugasan);
        $this->assertEquals('diproses', $disposisi->laporan->status);
        $this->assertNull($disposisi->tanggal_selesai);
        $this->assertDatabaseMissing('hasil_perbaikan', [
            'disposisi_id' => $disposisi->id,
        ]);
    }

    /**
     * TC-21-07 (FR-21.6): Tugas yang sudah selesai bersifat hanya-baca (tidak dapat diubah lagi progresnya).
     */
    public function test_completed_task_is_read_only(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'selesai', 'selesai');

        $response = $this->actingAs($this->petugasA)
            ->post(route('petugas.tugas.progres', $disposisi->id), [
                'catatan_perbaikan' => 'Mencoba mengedit catatan pada tugas yang telah ditutup.',
            ]);

        $response->assertStatus(422);
    }

    /**
     * TC-21-08 (FR-21.8): Tugas yang sudah selesai tetap muncul pada filter status 'selesai'.
     */
    public function test_completed_tasks_appear_in_filter_selesai(): void
    {
        $disposisiSelesai = $this->createDisposisi($this->petugasA, 'selesai', 'selesai');
        $disposisiAktif = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan', 'diproses');

        $response = $this->actingAs($this->petugasA)
            ->get(route('petugas.tugas.index', ['status' => 'selesai']));

        $response->assertOk();
        $response->assertSee($disposisiSelesai->nomor_surat_tugas);
        $response->assertDontSee($disposisiAktif->nomor_surat_tugas);
    }

    /**
     * TC-21-09: Petugas lain dilarang menutup tugas milik petugas lain (IDOR -> 403).
     */
    public function test_other_petugas_cannot_complete_task_idor(): void
    {
        $disposisi = $this->createDisposisi($this->petugasA, 'sedang_dikerjakan');
        $file = UploadedFile::fake()->image('test.jpg')->size(500);

        $response = $this->actingAs($this->petugasB)
            ->post(route('petugas.tugas.selesai', $disposisi->id), [
                'foto_bukti_sesudah' => $file,
                'catatan_perbaikan' => 'Petugas B mencoba menutup tugas Petugas A.',
            ]);

        $response->assertStatus(403);
    }
}

