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

class PBI19DaftarTugasNavigasiPetaTest extends TestCase
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

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugasA = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Asep Petugas']);
        $this->petugasB = User::factory()->create(['role' => 'petugas', 'is_active' => true, 'name' => 'Cecep Petugas']);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $this->kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-003',
            'nama_kecamatan' => 'Coblong',
        ]);
        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => 'BDG-003-01',
            'nama_kelurahan' => 'Dago',
        ]);
        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Raya/Trotoar',
            'deskripsi' => 'Kerusakan fisik jalan',
            'bobot_prioritas' => 3,
        ]);
    }

    private function createDisposisi(
        User $petugas,
        string $statusPenugasan = 'ditugaskan',
        string $prioritas = 'tinggi',
        ?string $tanggalDisposisi = null,
        string $judul = 'Laporan Kerusakan'
    ): DisposisiPenugasan {
        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => $judul,
            'deskripsi_kondisi' => 'Kondisi fisik kerusakan yang perlu segera ditangani.',
            'latitude' => -6.88500000,
            'longitude' => 107.61300000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'diproses',
            'prioritas' => $prioritas,
        ]);

        return DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'laporan_id' => $laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $petugas->id,
            'tanggal_disposisi' => $tanggalDisposisi ?? now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Instruksi pengerjaan perbaikan fisik di lokasi.',
            'status_penugasan' => $statusPenugasan,
        ]);
    }

    /**
     * FR-19.1: Sistem menampilkan daftar disposisi/tugas hanya milik petugas yang sedang login.
     */
    public function test_fr_19_1_petugas_only_sees_own_assigned_tasks(): void
    {
        $tugasA1 = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', null, 'Tugas Petugas A Nomor 1');
        $tugasA2 = $this->createDisposisi($this->petugasA, 'diterima', 'sedang', null, 'Tugas Petugas A Nomor 2');
        $tugasB = $this->createDisposisi($this->petugasB, 'ditugaskan', 'tinggi', null, 'Tugas Petugas B Rahasia');

        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index'));

        $response->assertStatus(200);
        $response->assertSee($tugasA1->nomor_surat_tugas);
        $response->assertSee($tugasA2->nomor_surat_tugas);
        $response->assertSee('Tugas Petugas A Nomor 1');
        $response->assertSee('Tugas Petugas A Nomor 2');
        $response->assertDontSee($tugasB->nomor_surat_tugas);
        $response->assertDontSee('Tugas Petugas B Rahasia');
    }

    /**
     * FR-19.2: Setiap item daftar menampilkan informasi lengkap:
     * nomor tiket, judul laporan, kategori, kelurahan/kecamatan, prioritas, tanggal disposisi, dan status.
     */
    public function test_fr_19_2_task_card_displays_all_required_information(): void
    {
        $tugas = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', '2026-11-05', 'Aspal Amblas Parah');

        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index'));

        $response->assertStatus(200);
        $response->assertSee($tugas->laporan->nomor_tiket);
        $response->assertSee('Aspal Amblas Parah');
        $response->assertSee('Jalan Raya/Trotoar');
        $response->assertSee('Kel. Dago, Kec. Coblong');
        $response->assertSee('Prioritas Tinggi');
        $response->assertSee('05 Nov 2026');
        $response->assertSee('Ditugaskan');
    }

    /**
     * FR-19.3: Filter status penugasan (ditugaskan, diterima, sedang_dikerjakan, selesai).
     */
    public function test_fr_19_3_filter_tasks_by_status(): void
    {
        $tugasDitugaskan = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', null, 'Tugas Baru Masuk');
        $tugasDiterima = $this->createDisposisi($this->petugasA, 'diterima', 'sedang', null, 'Tugas Sudah Diterima');
        $tugasSelesai = $this->createDisposisi($this->petugasA, 'selesai', 'rendah', null, 'Tugas Selesai Tuntas');

        // Filter: ditugaskan
        $resDitugaskan = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', ['status' => 'ditugaskan']));
        $resDitugaskan->assertStatus(200);
        $resDitugaskan->assertSee('Tugas Baru Masuk');
        $resDitugaskan->assertDontSee('Tugas Sudah Diterima');
        $resDitugaskan->assertDontSee('Tugas Selesai Tuntas');

        // Filter: diterima
        $resDiterima = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', ['status' => 'diterima']));
        $resDiterima->assertStatus(200);
        $resDiterima->assertSee('Tugas Sudah Diterima');
        $resDiterima->assertDontSee('Tugas Baru Masuk');
        $resDiterima->assertDontSee('Tugas Selesai Tuntas');

        // Filter: selesai
        $resSelesai = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', ['status' => 'selesai']));
        $resSelesai->assertStatus(200);
        $resSelesai->assertSee('Tugas Selesai Tuntas');
        $resSelesai->assertDontSee('Tugas Baru Masuk');
        $resSelesai->assertDontSee('Tugas Sudah Diterima');
    }

    /**
     * FR-19.3: Pengurutan default (prioritas tertinggi lalu tanggal disposisi terlama).
     */
    public function test_fr_19_3_default_sorting_by_highest_priority_then_oldest_date(): void
    {
        // Rendah, tapi tanggal lama (3 hari lalu)
        $tugasRendah = $this->createDisposisi($this->petugasA, 'ditugaskan', 'rendah', now()->subDays(3)->toDateString(), 'Tugas Prioritas Rendah');
        // Tinggi, tanggal lebih baru (1 hari lalu)
        $tugasTinggiBaru = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', now()->subDays(1)->toDateString(), 'Tugas Tinggi Baru');
        // Tinggi, tanggal lebih lama (4 hari lalu) -> harus nomor 1
        $tugasTinggiLama = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', now()->subDays(4)->toDateString(), 'Tugas Tinggi Terlama');

        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $posTinggiLama = strpos($content, 'Tugas Tinggi Terlama');
        $posTinggiBaru = strpos($content, 'Tugas Tinggi Baru');
        $posRendah = strpos($content, 'Tugas Prioritas Rendah');

        // Tugas prioritas tinggi harus muncul lebih awal dibanding prioritas rendah
        $this->assertTrue($posTinggiLama < $posTinggiBaru, 'Tinggi lama harus sebelum tinggi baru');
        $this->assertTrue($posTinggiBaru < $posRendah, 'Tinggi baru harus sebelum rendah');
    }

    /**
     * FR-19.4: Pencarian berdasarkan nomor tiket atau judul laporan.
     */
    public function test_fr_19_4_search_tasks_by_ticket_or_title(): void
    {
        $tugas1 = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', null, 'Gorong-gorong Mampet');
        $tugas2 = $this->createDisposisi($this->petugasA, 'ditugaskan', 'sedang', null, 'Lampu Penerangan Padam');

        // Cari berdasarkan judul
        $resJudul = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', ['search' => 'Gorong-gorong']));
        $resJudul->assertStatus(200);
        $resJudul->assertSee('Gorong-gorong Mampet');
        $resJudul->assertDontSee('Lampu Penerangan Padam');

        // Cari berdasarkan nomor tiket
        $resTiket = $this->actingAs($this->petugasA)->get(route('petugas.tugas.index', ['search' => $tugas2->laporan->nomor_tiket]));
        $resTiket->assertStatus(200);
        $resTiket->assertSee('Lampu Penerangan Padam');
        $resTiket->assertDontSee('Gorong-gorong Mampet');
    }

    /**
     * FR-19.10: Kondisi daftar kosong menampilkan tampilan informatif.
     */
    public function test_fr_19_10_empty_state_displayed_when_no_tasks(): void
    {
        // Petugas B belum punya tugas
        $response = $this->actingAs($this->petugasB)->get(route('petugas.tugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Tugas Lapangan');
        $response->assertSee('Saat ini belum ada tugas lapangan');
    }

    /**
     * FR-19.5, FR-19.6, FR-19.7: Halaman detail menampilkan info lengkap, peta Leaflet, dan tombol navigasi GPS.
     */
    public function test_fr_19_5_to_19_7_task_detail_with_leaflet_map_and_navigation_links(): void
    {
        $tugas = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', null, 'Kerusakan Trotoar Dago');

        $response = $this->actingAs($this->petugasA)->get(route('petugas.tugas.show', $tugas->id));

        $response->assertStatus(200);
        // FR-19.5 Informasi detail
        $response->assertSee('Kerusakan Trotoar Dago');
        $response->assertSee($tugas->laporan->deskripsi_kondisi);
        $response->assertSee($tugas->catatan_instruksi);
        $response->assertSee('Dago');
        $response->assertSee('Coblong');
        $response->assertSee('-6.885');
        $response->assertSee('107.613');

        // FR-19.6 Kontainer Peta Leaflet
        $response->assertSee('id="petugasMap"', false);
        $response->assertSee('L.map(\'petugasMap\')', false);

        // FR-19.7 Tombol Navigasi Layanan Peta Eksternal
        $response->assertSee('Google Maps');
        $response->assertSee('google.com/maps/dir/?api=1', false);
        $response->assertSee('Waze');
        $response->assertSee('waze.com/ul?ll=', false);
        $response->assertSee('OpenStreetMap');
    }

    /**
     * FR-19.8: Aksi acceptTask mengubah status dari 'ditugaskan' menjadi 'diterima' dan mencatat audit log.
     */
    public function test_fr_19_8_petugas_can_accept_task_and_logs_audit_trail(): void
    {
        $tugas = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi', null, 'Tugas Baru Segera Diterima');

        $response = $this->actingAs($this->petugasA)->post(route('petugas.tugas.terima', $tugas->id));

        $response->assertRedirect(route('petugas.tugas.show', $tugas->id));
        $response->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('diterima', $tugas->status_penugasan);

        // Audit log riwayat status tercatat
        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $tugas->laporan_id,
            'user_id' => $this->petugasA->id,
            'status' => 'diproses',
        ]);
    }

    /**
     * FR-19.8: Menolak penerimaan tugas yang sudah bukan berstatus 'ditugaskan'.
     */
    public function test_fr_19_8_cannot_accept_already_accepted_or_completed_task(): void
    {
        $tugas = $this->createDisposisi($this->petugasA, 'diterima', 'tinggi');

        $response = $this->actingAs($this->petugasA)->post(route('petugas.tugas.terima', $tugas->id));

        $response->assertStatus(422);
    }

    /**
     * FR-19.9: Petugas lain dilarang melihat atau menerima tugas yang bukan miliknya (IDOR prevention).
     */
    public function test_fr_19_9_petugas_cannot_access_or_accept_other_petugas_task(): void
    {
        $tugasA = $this->createDisposisi($this->petugasA, 'ditugaskan', 'tinggi');

        // Petugas B mencoba melihat detail tugas Petugas A
        $responseView = $this->actingAs($this->petugasB)->get(route('petugas.tugas.show', $tugasA->id));
        $responseView->assertStatus(403);

        // Petugas B mencoba menerima tugas Petugas A
        $responseAccept = $this->actingAs($this->petugasB)->post(route('petugas.tugas.terima', $tugasA->id));
        $responseAccept->assertStatus(403);
    }
}
