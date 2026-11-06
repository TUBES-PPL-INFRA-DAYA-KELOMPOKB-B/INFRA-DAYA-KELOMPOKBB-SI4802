<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetugasLapanganTest extends TestCase
{
    use RefreshDatabase;

    private User $petugasA;

    private User $petugasB;

    private User $admin;

    private User $warga;

    private LaporanKerusakan $laporan;

    private DisposisiPenugasan $disposisiA;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugasA = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Budi Santoso']);
        $this->petugasB = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Agus Prakoso']);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-003',
            'nama_kecamatan' => 'Coblong',
        ]);
        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-003-01',
            'nama_kelurahan' => 'Dago',
        ]);
        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Raya',
            'deskripsi' => 'Kerusakan jalan aspal',
            'bobot_prioritas' => 3,
        ]);

        $this->laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0701',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Rusak Berlubang Parah Dago',
            'deskripsi_kondisi' => 'Aspal amblas sedalam 20 cm mengancam pengguna jalan.',
            'latitude' => -6.88500000,
            'longitude' => 107.61300000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);

        $this->disposisiA = DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261014-7001',
            'laporan_id' => $this->laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugasA->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Lakukan perataan dan penambalan hotmix secepatnya.',
            'status_penugasan' => 'ditugaskan',
        ]);
    }

    public function test_petugas_can_view_tugas_index(): void
    {
        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Tugas Lapangan');
        $response->assertSee('SPT-20261014-7001');
        $response->assertSee('Jalan Rusak Berlubang Parah Dago');
    }

    public function test_petugas_only_sees_own_tasks_in_index(): void
    {
        // Tugas untuk Petugas B
        $laporanB = LaporanKerusakan::factory()->create([
            'judul' => 'Saluran Mampet di Sukajadi',
            'status' => 'diproses',
        ]);
        DisposisiPenugasan::factory()->create([
            'nomor_surat_tugas' => 'SPT-20261014-7002',
            'laporan_id' => $laporanB->id,
            'petugas_id' => $this->petugasB->id,
            'admin_id' => $this->admin->id,
        ]);

        // Petugas A login
        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index'));
        $response->assertStatus(200);
        $response->assertSee('SPT-20261014-7001');
        $response->assertDontSee('SPT-20261014-7002');
    }

    public function test_petugas_can_filter_tasks_by_status(): void
    {
        $responseDitugaskan = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', [
            'status' => 'ditugaskan',
        ]));
        $responseDitugaskan->assertStatus(200);
        $responseDitugaskan->assertSee('SPT-20261014-7001');

        $responseSelesai = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', [
            'status' => 'selesai',
        ]));
        $responseSelesai->assertStatus(200);
        $responseSelesai->assertDontSee('SPT-20261014-7001');
    }

    public function test_petugas_can_search_tasks(): void
    {
        $responseMatch = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', [
            'search' => 'SPT-20261014-7001',
        ]));
        $responseMatch->assertStatus(200);
        $responseMatch->assertSee('Jalan Rusak Berlubang Parah Dago');

        $responseNoMatch = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', [
            'search' => 'KeywordTidakAdaSamaSekali',
        ]));
        $responseNoMatch->assertStatus(200);
        $responseNoMatch->assertSee('Tidak Ada Tugas Lapangan');
    }

    public function test_non_petugas_cannot_access_petugas_routes(): void
    {
        $guestResponse = $this->get(route('petugas.tugas.index'));
        $guestResponse->assertRedirect(route('login'));

        $wargaResponse = $this->actingAs($this->warga)->get(route('petugas.tugas.index'));
        $wargaResponse->assertStatus(403);
    }

    public function test_petugas_can_view_tugas_detail_with_gps_links(): void
    {
        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.show', $this->disposisiA->id));

        $response->assertStatus(200);
        $response->assertSee('Lembar Kerja Lapangan');
        $response->assertSee($this->disposisiA->nomor_surat_tugas);
        $response->assertSee('Google Maps');
        $response->assertSee('Waze');
        $response->assertSee('Lakukan perataan dan penambalan hotmix secepatnya.');
        $response->assertSee('Mulai Pengerjaan Sekarang');
    }

    public function test_petugas_cannot_view_other_petugas_task_detail(): void
    {
        // Petugas B mencoba akses tugas milik Petugas A -> IDOR prevention
        $response = $this->actingAs($this->petugasB)->get(route('petugas.tugas.show', $this->disposisiA->id));

        $response->assertStatus(403);
    }

    public function test_petugas_can_start_task_execution(): void
    {
        $response = $this->actingAs($this->petugasA)->post(route('petugas.tugas.mulai', $this->disposisiA->id));

        $response->assertRedirect(route('petugas.tugas.show', $this->disposisiA->id));
        $response->assertSessionHas('success');

        $this->disposisiA->refresh();
        $this->assertEquals('sedang_dikerjakan', $this->disposisiA->status_penugasan);

        // Riwayat status audit tercatat
        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugasA->id,
            'status' => 'diproses',
        ]);
    }

    public function test_petugas_cannot_start_other_petugas_task(): void
    {
        $response = $this->actingAs($this->petugasB)->post(route('petugas.tugas.mulai', $this->disposisiA->id));

        $response->assertStatus(403);
        $this->disposisiA->refresh();
        $this->assertEquals('ditugaskan', $this->disposisiA->status_penugasan);
    }

    public function test_petugas_can_complete_task_with_after_photo_and_notes(): void
    {
        // Ubah dulu ke sedang dikerjakan
        $this->disposisiA->update(['status_penugasan' => 'sedang_dikerjakan']);

        $file = UploadedFile::fake()->image('bukti_sesudah.jpg', 800, 600);

        $payload = [
            'foto_bukti_sesudah' => $file,
            'catatan_perbaikan' => 'Pekerjaan penambalan hotmix telah selesai 100% dan lalu lintas kembali normal.',
        ];

        $response = $this->actingAs($this->petugasA)->post(route('petugas.tugas.selesai', $this->disposisiA->id), $payload);

        $response->assertRedirect(route('petugas.tugas.show', $this->disposisiA->id));
        $response->assertSessionHas('success');

        $this->disposisiA->refresh();
        $this->laporan->refresh();

        $this->assertEquals('selesai', $this->disposisiA->status_penugasan);
        $this->assertEquals('selesai', $this->laporan->status);
        $this->assertNotNull($this->disposisiA->foto_bukti_sesudah);
        $this->assertNotNull($this->laporan->foto_bukti_sesudah);
        $this->assertNotNull($this->laporan->selesai_at);
        $this->assertEquals('Pekerjaan penambalan hotmix telah selesai 100% dan lalu lintas kembali normal.', $this->disposisiA->catatan_perbaikan);

        // Verifikasi file tersimpan di storage public
        Storage::disk('public')->assertExists($this->disposisiA->foto_bukti_sesudah);

        // Riwayat status audit selesai tercatat
        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugasA->id,
            'status' => 'selesai',
        ]);
    }

    public function test_complete_task_validates_required_fields(): void
    {
        $this->disposisiA->update(['status_penugasan' => 'sedang_dikerjakan']);

        $response = $this->actingAs($this->petugasA)->post(route('petugas.tugas.selesai', $this->disposisiA->id), [
            'foto_bukti_sesudah' => '',
            'catatan_perbaikan' => '',
        ]);

        $response->assertSessionHasErrors(['foto_bukti_sesudah', 'catatan_perbaikan']);
    }

    public function test_complete_task_validates_note_minimum_length(): void
    {
        $this->disposisiA->update(['status_penugasan' => 'sedang_dikerjakan']);
        $file = UploadedFile::fake()->image('bukti_sesudah.jpg');

        $response = $this->actingAs($this->petugasA)->post(route('petugas.tugas.selesai', $this->disposisiA->id), [
            'foto_bukti_sesudah' => $file,
            'catatan_perbaikan' => 'Pendek',
        ]);

        $response->assertSessionHasErrors(['catatan_perbaikan']);
    }

    public function test_petugas_cannot_complete_other_petugas_task(): void
    {
        $file = UploadedFile::fake()->image('bukti_sesudah.jpg');

        $response = $this->actingAs($this->petugasB)->post(route('petugas.tugas.selesai', $this->disposisiA->id), [
            'foto_bukti_sesudah' => $file,
            'catatan_perbaikan' => 'Mencoba menyelesaikan tugas orang lain secara ilegal.',
        ]);

        $response->assertStatus(403);
    }
}
