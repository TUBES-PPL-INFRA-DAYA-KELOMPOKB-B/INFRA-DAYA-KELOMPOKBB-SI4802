<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    private User $warga;

    private LaporanKerusakan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Budi Petugas']);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-001',
            'nama_kecamatan' => 'Coblong',
        ]);
        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-001-01',
            'nama_kelurahan' => 'Dago',
        ]);
        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Raya',
            'deskripsi' => 'Kerusakan jalan aspal',
            'bobot_prioritas' => 3,
        ]);

        $this->laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0001',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Berlubang di Ir H Djuanda',
            'deskripsi_kondisi' => 'Lubang aspal sedalam 15 cm membahayakan pengendara roda dua.',
            'latitude' => -6.8850,
            'longitude' => 107.6130,
            'foto_bukti_sebelum' => 'laporan/sebelum/test.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);
    }

    public function test_admin_can_view_disposisi_index(): void
    {
        DisposisiPenugasan::factory()->create([
            'nomor_surat_tugas' => 'SPT-20261014-0001',
            'laporan_id' => $this->laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugas->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.index'));

        $response->assertStatus(200);
        $response->assertSee('Disposisi Penugasan Lapangan');
        $response->assertSee('SPT-20261014-0001');
        $response->assertSee('Budi Petugas');
    }

    public function test_warga_and_guest_cannot_access_disposisi_routes(): void
    {
        $guestResponse = $this->get(route('admin.disposisi.index'));
        $guestResponse->assertRedirect(route('login'));

        $wargaResponse = $this->actingAs($this->warga)->get(route('admin.disposisi.index'));
        $wargaResponse->assertStatus(403);
    }

    public function test_admin_can_filter_disposisi_by_status(): void
    {
        $disposisi = DisposisiPenugasan::factory()->create([
            'nomor_surat_tugas' => 'SPT-20261014-0001',
            'laporan_id' => $this->laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugas->id,
            'status_penugasan' => 'ditugaskan',
        ]);

        $responseMatched = $this->actingAs($this->admin)->get(route('admin.disposisi.index', [
            'status_penugasan' => 'ditugaskan',
        ]));
        $responseMatched->assertStatus(200);
        $responseMatched->assertSee('SPT-20261014-0001');

        $responseUnmatched = $this->actingAs($this->admin)->get(route('admin.disposisi.index', [
            'status_penugasan' => 'selesai',
        ]));
        $responseUnmatched->assertStatus(200);
        $responseUnmatched->assertDontSee('SPT-20261014-0001');
    }

    public function test_admin_can_view_create_disposisi_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.create'));

        $response->assertStatus(200);
        $response->assertSee('Terbitkan Surat Perintah Tugas (SPT)');
        $response->assertSee($this->laporan->nomor_tiket);
        $response->assertSee($this->petugas->name);
    }

    public function test_admin_can_view_create_page_with_preselected_laporan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.create', [
            'laporan_id' => $this->laporan->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Laporan Terpilih');
        $response->assertSee($this->laporan->judul);
    }

    public function test_admin_can_create_disposisi_for_approved_report(): void
    {
        $payload = [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Lakukan perataan aspal hotmix dan pasang garis pengaman kerja.',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), $payload);

        $this->assertDatabaseHas('disposisi_penugasan', [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'status_penugasan' => 'ditugaskan',
        ]);

        $disposisi = DisposisiPenugasan::where('laporan_id', $this->laporan->id)->first();
        $this->assertNotNull($disposisi);
        $this->assertStringStartsWith('SPT-', $disposisi->nomor_surat_tugas);

        $response->assertRedirect(route('admin.disposisi.show', $disposisi->id));

        // Audit log riwayat status harus tercatat
        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $this->laporan->id,
            'status' => 'diproses',
        ]);
    }

    public function test_create_disposisi_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => '',
            'petugas_id' => '',
            'tenggat_waktu' => '',
            'catatan_instruksi' => '',
        ]);

        $response->assertSessionHasErrors(['laporan_id', 'petugas_id', 'tenggat_waktu', 'catatan_instruksi']);
    }

    public function test_create_disposisi_validates_deadline_cannot_be_before_assignment_date(): void
    {
        $payload = [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->subDay()->toDateString(),
            'catatan_instruksi' => 'Instruksi perbaikan jalan berlubang',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), $payload);
        $response->assertSessionHasErrors(['tenggat_waktu']);
    }

    public function test_create_disposisi_fails_if_report_not_in_diproses_status(): void
    {
        $laporanBelumVerif = LaporanKerusakan::factory()->create([
            'status' => 'menunggu_verifikasi',
        ]);

        $payload = [
            'laporan_id' => $laporanBelumVerif->id,
            'petugas_id' => $this->petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(3)->toDateString(),
            'catatan_instruksi' => 'Instruksi perbaikan jalan berlubang',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), $payload);
        $response->assertSessionHasErrors(['laporan_id']);
    }

    public function test_create_disposisi_fails_if_report_already_has_disposisi(): void
    {
        DisposisiPenugasan::factory()->create([
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'admin_id' => $this->admin->id,
        ]);

        $payload = [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(3)->toDateString(),
            'catatan_instruksi' => 'Instruksi perbaikan kedua yang tidak valid',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), $payload);
        $response->assertSessionHasErrors(['laporan_id']);
    }

    public function test_admin_can_view_disposisi_detail_page(): void
    {
        $disposisi = DisposisiPenugasan::factory()->create([
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'admin_id' => $this->admin->id,
            'catatan_instruksi' => 'Instruksi detail penanganan jembatan',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.show', $disposisi->id));

        $response->assertStatus(200);
        $response->assertSee($disposisi->nomor_surat_tugas);
        $response->assertSee('Instruksi detail penanganan jembatan');
        $response->assertSee($this->petugas->name);
    }

    public function test_admin_can_generate_and_stream_spt_pdf(): void
    {
        $disposisi = DisposisiPenugasan::factory()->create([
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'admin_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.pdf', $disposisi->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_download_spt_pdf(): void
    {
        $disposisi = DisposisiPenugasan::factory()->create([
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugas->id,
            'admin_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.pdf', [
            'id' => $disposisi->id,
            'download' => '1',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', 'attachment'));
    }
}
