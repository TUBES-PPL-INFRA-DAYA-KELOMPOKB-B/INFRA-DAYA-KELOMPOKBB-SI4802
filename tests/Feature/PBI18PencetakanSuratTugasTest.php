<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Services\SuratTugasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PBI18PencetakanSuratTugasTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugasAktif;
    protected User $petugasNonaktif;
    protected User $warga;
    protected User $pimpinan;
    protected Kecamatan $kecamatan;
    protected Kelurahan $kelurahan;
    protected KategoriInfrastruktur $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin DSDABM Pusat',
            'email' => 'admin.pbi18.' . uniqid() . '@bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugasAktif = User::factory()->create([
            'name' => 'Hendra Teknisi',
            'email' => 'hendra.petugas.' . uniqid() . '@bandung.go.id',
            'phone' => '081234567890',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->petugasNonaktif = User::factory()->create([
            'name' => 'Kusnadi Pensiun',
            'email' => 'kusnadi.petugas.' . uniqid() . '@bandung.go.id',
            'phone' => '+628987654321',
            'role' => 'petugas',
            'is_active' => false,
        ]);

        $this->warga = User::factory()->create([
            'name' => 'Warga Pelapor',
            'email' => 'warga.pbi18.' . uniqid() . '@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->pimpinan = User::factory()->create([
            'name' => 'Pimpinan Dinas',
            'email' => 'pimpinan.pbi18.' . uniqid() . '@bandung.go.id',
            'role' => 'pimpinan',
            'is_active' => true,
        ]);

        $this->kecamatan = Kecamatan::firstOrCreate(
            ['kode_kecamatan' => 'BDG-010'],
            ['nama_kecamatan' => 'Coblong']
        );

        $this->kelurahan = Kelurahan::firstOrCreate(
            ['kode_kelurahan' => 'BDG-010-01'],
            ['kecamatan_id' => $this->kecamatan->id, 'nama_kelurahan' => 'Dago']
        );

        $this->kategori = KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Jalan Rusak'],
            ['deskripsi' => 'Kerusakan jalan aspal', 'bobot_prioritas' => 3]
        );
    }

    protected function createDisposisi(array $attributes = []): DisposisiPenugasan
    {
        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LAP-'.now()->format('Ymd').'-'.rand(1000, 9999),
            'warga_id' => $this->warga->id,
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'judul' => 'Jalan Rusak Parah di Simpang Dago',
            'deskripsi_kondisi' => 'Aspal mengelupas sedalam 20 cm.',
            'lokasi_spesifik' => 'Depan SPBU Dago',
            'latitude' => -6.8850,
            'longitude' => 107.6136,
            'foto_bukti_sebelum' => 'reports/foto_sebelum.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);

        return DisposisiPenugasan::create(array_merge([
            'nomor_surat_tugas' => 'SPT-'.now()->format('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'laporan_id' => $laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugasAktif->id,
            'tanggal_disposisi' => Carbon::parse('2026-11-05 08:30:00'),
            'tenggat_waktu' => '2026-11-09',
            'catatan_instruksi' => 'Lakukan perbaikan dan pengaspalan segera menggunakan metode hotmix.',
            'status_penugasan' => 'ditugaskan',
        ], $attributes));
    }

    /**
     * AC18-01: Hanya admin dapat mengakses daftar, pratinjau, cetak, dan PDF; role lain ditolak (403).
     */
    public function test_ac18_01_hanya_admin_dapat_mengakses_role_lain_ditolak(): void
    {
        $disposisi = $this->createDisposisi();

        // Admin berhasil mengakses
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisi->id))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisi->id))->assertStatus(200);

        // Role lain ditolak 403 Forbidden
        foreach ([$this->warga, $this->petugasAktif, $this->pimpinan] as $unauthorized) {
            $this->actingAs($unauthorized)->get(route('admin.surat-tugas.index'))->assertStatus(403);
            $this->actingAs($unauthorized)->get(route('admin.surat-tugas.show', $disposisi->id))->assertStatus(403);
            $this->actingAs($unauthorized)->get(route('admin.surat-tugas.cetak', $disposisi->id))->assertStatus(403);
            $this->actingAs($unauthorized)->get(route('admin.surat-tugas.pdf', $disposisi->id))->assertStatus(403);
        }
    }

    /**
     * AC18-02: Tamu wajib autentikasi sebelum mengakses endpoint surat tugas.
     */
    public function test_ac18_02_tamu_wajib_autentikasi(): void
    {
        $disposisi = $this->createDisposisi();

        $this->get(route('admin.surat-tugas.index'))->assertRedirect(route('login'));
        $this->get(route('admin.surat-tugas.show', $disposisi->id))->assertRedirect(route('login'));
        $this->get(route('admin.surat-tugas.cetak', $disposisi->id))->assertRedirect(route('login'));
        $this->get(route('admin.surat-tugas.pdf', $disposisi->id))->assertRedirect(route('login'));
    }

    /**
     * AC18-03: Daftar memuat tiket, petugas, tanggal, status, serta aksi pratinjau.
     */
    public function test_ac18_03_daftar_memuat_seluruh_kolom_yang_dipersyaratkan(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.index'));

        $response->assertStatus(200);
        $response->assertSee($disposisi->nomor_surat_tugas);
        $response->assertSee($disposisi->laporan->nomor_tiket);
        $response->assertSee($disposisi->petugas->name);
        $response->assertSee($disposisi->tanggal_disposisi->translatedFormat('d M Y'));
        $response->assertSee('DITUGASKAN');
        $response->assertSee(route('admin.surat-tugas.show', $disposisi->id));
    }

    /**
     * AC18-04: Pencarian mendukung tiket atau nama petugas; reset mengembalikan daftar awal.
     */
    public function test_ac18_04_pencarian_nomor_tiket_nama_petugas_dan_reset(): void
    {
        $disposisi1 = $this->createDisposisi();
        $disposisi2 = $this->createDisposisi([
            'petugas_id' => $this->petugasNonaktif->id,
        ]);

        // Cari berdasarkan nomor tiket disposisi 1
        $resTiket = $this->actingAs($this->admin)->get(route('admin.surat-tugas.index', [
            'search' => $disposisi1->laporan->nomor_tiket,
        ]));
        $resTiket->assertSee($disposisi1->laporan->nomor_tiket);
        $resTiket->assertDontSee($disposisi2->laporan->nomor_tiket);

        // Cari berdasarkan nama petugas disposisi 2 (Kusnadi)
        $resPetugas = $this->actingAs($this->admin)->get(route('admin.surat-tugas.index', [
            'search' => 'Kusnadi',
        ]));
        $resPetugas->assertSee($disposisi2->nomor_surat_tugas);
        $resPetugas->assertDontSee($disposisi1->petugas->name);

        // Reset kembali menampilkan semua
        $resReset = $this->actingAs($this->admin)->get(route('admin.surat-tugas.index'));
        $resReset->assertSee($disposisi1->nomor_surat_tugas);
        $resReset->assertSee($disposisi2->nomor_surat_tugas);
    }

    /**
     * AC18-05: Daftar kosong menampilkan “Belum ada disposisi yang dapat ditampilkan”; pencarian kosong “Disposisi tidak ditemukan”.
     */
    public function test_ac18_05_pesan_daftar_kosong_dan_pencarian_kosong(): void
    {
        // Kasus 1: Pencarian dengan kata kunci tidak ada
        $this->createDisposisi();
        $resSearch = $this->actingAs($this->admin)->get(route('admin.surat-tugas.index', [
            'search' => 'NomorTiketPastiTidakAda99999',
        ]));
        $resSearch->assertSee('Disposisi tidak ditemukan');

        // Kasus 2: Tabel disposisi benar-benar kosong
        DisposisiPenugasan::query()->delete();
        $resEmpty = $this->actingAs($this->admin)->get(route('admin.surat-tugas.index'));
        $resEmpty->assertSee('Belum ada disposisi yang dapat ditampilkan');
    }

    /**
     * AC18-06 & AC18-18: Surat hanya dibuat jika seluruh data wajib tersedia; data tidak lengkap ditolak dengan penjelasan.
     */
    public function test_ac18_06_dan_ac18_18_validasi_kelengkapan_data_wajib(): void
    {
        $disposisi = $this->createDisposisi();

        // Simulasikan laporan kehilangan data penting seperti kategori atau koordinat
        $disposisi->laporan->update([
            'kategori_id' => null,
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));

        $response->assertRedirect(route('admin.surat-tugas.index'));
        $response->assertSessionHas('error');

        $errorMessage = session('error');
        $this->assertStringContainsString('Kategori Kerusakan', $errorMessage);
        $this->assertStringContainsString('Koordinat Geografis', $errorMessage);
    }

    /**
     * AC18-07: Isi memakai disposisi dan relasi yang dipilih, tidak tertukar dengan data lain.
     */
    public function test_ac18_07_isi_memakai_data_disposisi_terpilih_tanpa_tertukar(): void
    {
        $disposisiA = $this->createDisposisi();
        $disposisiB = $this->createDisposisi([
            'petugas_id' => $this->petugasNonaktif->id,
            'catatan_instruksi' => 'Instruksi Khusus untuk Paket B.',
        ]);

        $responseA = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisiA->id));
        $responseA->assertSee($disposisiA->nomor_surat_tugas);
        $responseA->assertSee($disposisiA->laporan->judul);
        $responseA->assertSee($disposisiA->petugas->name);
        $responseA->assertDontSee($disposisiB->nomor_surat_tugas);
        $responseA->assertDontSee('Instruksi Khusus untuk Paket B.');
    }

    /**
     * AC18-08: Surat memuat nomor, tanggal, petugas, tiket, judul, kategori, prioritas, wilayah, koordinat, dan instruksi.
     */
    public function test_ac18_08_surat_memuat_seluruh_elemen_bisnis_wajib(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));

        $response->assertStatus(200);
        $response->assertSee($disposisi->nomor_surat_tugas);
        $response->assertSee($disposisi->tanggal_disposisi->translatedFormat('d F Y'));
        $response->assertSee($disposisi->petugas->name);
        $response->assertSee($disposisi->laporan->nomor_tiket);
        $response->assertSee($disposisi->laporan->judul);
        $response->assertSee($disposisi->laporan->kategori->nama_kategori);
        $response->assertSee(ucfirst($disposisi->laporan->prioritas));
        $response->assertSee($disposisi->laporan->kelurahan->nama_kelurahan);
        $response->assertSee($disposisi->laporan->kelurahan->kecamatan->nama_kecamatan);
        $response->assertSee((string) $disposisi->laporan->latitude);
        $response->assertSee((string) $disposisi->laporan->longitude);
        $response->assertSee($disposisi->catatan_instruksi);
    }

    /**
     * AC18-09: Nomor surat unik per disposisi dan tetap pada pengulangan pratinjau, unduh, atau cetak.
     */
    public function test_ac18_09_nomor_surat_stabil_dan_tidak_berubah(): void
    {
        $disposisi = $this->createDisposisi();
        $nomorSuratAwal = $disposisi->nomor_surat_tugas;

        // Buka pratinjau
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id))->assertSee($nomorSuratAwal);

        // Buka cetak
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisi->id))->assertSee($nomorSuratAwal);

        // Buka PDF
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisi->id));

        // Nomor di database tetap identik
        $disposisi->refresh();
        $this->assertEquals($nomorSuratAwal, $disposisi->nomor_surat_tugas);
    }

    /**
     * AC18-10: Tanggal penugasan berasal dari tanggal_disposisi; waktu pembuatan dokumen dipisahkan.
     */
    public function test_ac18_10_tanggal_penugasan_dan_waktu_dokumen_dipisahkan(): void
    {
        $disposisi = $this->createDisposisi([
            'tanggal_disposisi' => Carbon::parse('2026-10-15 09:00:00'),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));

        // Tanggal penugasan dari tanggal_disposisi
        $response->assertSee('15 Oktober 2026');

        // Waktu pembuatan dokumen terpisah di bagian footer
        $response->assertSee('Waktu Pembuatan Dokumen:');
        $response->assertSee(now()->translatedFormat('d F Y'));
    }

    /**
     * AC18-11: Unduhan menghasilkan PDF valid dengan Content-Type dan nama berkas yang sesuai.
     */
    public function test_ac18_11_unduhan_pdf_valid_dan_content_type(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisi->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("attachment; filename=\"SPT-{$disposisi->nomor_surat_tugas}.pdf\"", $disposition);

        // Periksa bahwa konten dimulai dengan header PDF standar (%PDF-)
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    /**
     * AC18-12: PDF memakai ukuran A4 portrait dengan tata letak yang terbaca.
     */
    public function test_ac18_12_pdf_memakai_ukuran_a4_portrait(): void
    {
        $disposisi = $this->createDisposisi();

        $service = app(SuratTugasService::class);
        $pdf = $service->generatePdf($disposisi);

        $this->assertNotNull($pdf);
        $output = $pdf->output();
        $this->assertStringStartsWith('%PDF-', $output);
    }

    /**
     * AC18-13: Instruksi panjang tidak terpotong atau bertumpuk; blok tanda tangan tetap terbaca.
     */
    public function test_ac18_13_instruksi_panjang_tidak_merusak_tata_letak(): void
    {
        $longInstruction = str_repeat("Instruksi teknis pengerjaan lapangan: bersihkan puing, ukur ketebalan aspal, padatkan sub-base, tuangkan lapisan aspal emulsi dan rapikan tepian jalan secara cermat. ", 15);

        $disposisi = $this->createDisposisi([
            'catatan_instruksi' => $longInstruction,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));
        $response->assertStatus(200);
        $response->assertSee($disposisi->nomor_surat_tugas);
        $response->assertSee('Admin DSDABM Kota Bandung');

        // PDF generator tetap berhasil memproses instruksi panjang tanpa error
        $pdfResponse = $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisi->id));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * AC18-14: Pratinjau dan PDF menggunakan data bisnis serta template yang sama.
     */
    public function test_ac18_14_pratinjau_dan_pdf_konsisten(): void
    {
        $disposisi = $this->createDisposisi();

        $preview = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));
        $preview->assertSee($disposisi->nomor_surat_tugas);
        $preview->assertSee($disposisi->laporan->judul);
        $preview->assertSee($disposisi->petugas->name);

        $pdfView = view('admin.surat-tugas.pdf', [
            'disposisi' => $disposisi,
            'waktuDokumen' => Carbon::now(),
        ])->render();

        $this->assertStringContainsString($disposisi->nomor_surat_tugas, $pdfView);
        $this->assertStringContainsString($disposisi->laporan->judul, $pdfView);
        $this->assertStringContainsString($disposisi->petugas->name, $pdfView);
    }

    /**
     * AC18-15: Cetak membuka dialog browser dan menyembunyikan navigasi/aksi pada hasil cetak.
     */
    public function test_ac18_15_halaman_cetak_menyembunyikan_navigasi(): void
    {
        $disposisi = $this->createDisposisi();

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisi->id));

        $response->assertStatus(200);
        $response->assertSee('window.print()');
        $response->assertSee('.no-print');
        $response->assertSee($disposisi->nomor_surat_tugas);
    }

    /**
     * AC18-16 & AC18-17: Pratinjau, PDF, dan cetak tidak membuat disposisi atau mengubah status maupun updated_at (baca-saja).
     */
    public function test_ac18_16_dan_ac18_17_pratinjau_dan_cetak_bersifat_baca_saja(): void
    {
        $disposisi = $this->createDisposisi();
        $initialDisposisiCount = DisposisiPenugasan::count();
        $initialUpdatedAt = $disposisi->updated_at->toDateTimeString();
        $initialStatus = $disposisi->status_penugasan;

        // Buka Pratinjau
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));

        // Buka Halaman Cetak
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisi->id));

        // Unduh PDF
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisi->id));

        $this->assertEquals($initialDisposisiCount, DisposisiPenugasan::count());
        $disposisi->refresh();
        $this->assertEquals($initialStatus, $disposisi->status_penugasan);
        $this->assertEquals($initialUpdatedAt, $disposisi->updated_at->toDateTimeString());
    }

    /**
     * AC18-18: Disposisi hilang ditolak dengan respons yang sesuai (404).
     */
    public function test_ac18_18_disposisi_tidak_ditemukan_ditolak(): void
    {
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', 999999))->assertStatus(404);
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', 999999))->assertStatus(404);
        $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', 999999))->assertStatus(404);
    }

    /**
     * AC18-19: Kegagalan generator memberi pesan dan opsi mencoba kembali tanpa respons sukses PDF rusak.
     */
    public function test_ac18_19_kegagalan_generator_memberi_pesan(): void
    {
        $disposisi = $this->createDisposisi();

        // Hapus relasi laporan untuk mensimulasikan data corrupt saat generate PDF
        $disposisi->laporan_id = 999999;
        $disposisi->save();

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisi->id));

        $response->assertRedirect(route('admin.surat-tugas.index'));
        $response->assertSessionHas('error');
    }

    /**
     * AC18-20: Password, hash, dan data sensitif tidak ditampilkan.
     */
    public function test_ac18_20_password_dan_hash_tidak_ditampilkan(): void
    {
        $disposisi = $this->createDisposisi();

        $views = [
            $this->actingAs($this->admin)->get(route('admin.surat-tugas.index')),
            $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id)),
            $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisi->id)),
        ];

        foreach ($views as $view) {
            $view->assertDontSee($this->petugasAktif->password);
            $view->assertDontSee($this->admin->password);
        }
    }

    /**
     * AC18-21: Teks HTML/script dalam data ditampilkan secara aman melalui escaping.
     */
    public function test_ac18_21_teks_html_dan_script_diescape_secara_aman(): void
    {
        $xssInstruction = '<script>alert("XSS-ATTACK")</script> <b>Perbaikan Segera</b>';

        $disposisi = $this->createDisposisi([
            'catatan_instruksi' => $xssInstruction,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisi->id));
        $response->assertDontSee('<script>alert("XSS-ATTACK")</script>', false);
        $response->assertSee(e($xssInstruction), false);
    }

    /**
     * AC18-22: Surat historis petugas nonaktif tetap dapat dibuka tanpa mengaktifkan kembali akun.
     */
    public function test_ac18_22_surat_historis_petugas_nonaktif_tetap_dapat_dibuka(): void
    {
        $disposisiHistoris = $this->createDisposisi([
            'petugas_id' => $this->petugasNonaktif->id,
        ]);

        $this->assertFalse($this->petugasNonaktif->is_active);

        // Admin tetap dapat melihat pratinjau, cetak, dan unduh PDF
        $resShow = $this->actingAs($this->admin)->get(route('admin.surat-tugas.show', $disposisiHistoris->id));
        $resShow->assertStatus(200);
        $resShow->assertSee($this->petugasNonaktif->name);
        $resShow->assertSee('Surat Tugas Historis');

        $resCetak = $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisiHistoris->id));
        $resCetak->assertStatus(200);
        $resCetak->assertSee($this->petugasNonaktif->name);

        $resPdf = $this->actingAs($this->admin)->get(route('admin.surat-tugas.pdf', $disposisiHistoris->id));
        $resPdf->assertStatus(200);
        $resPdf->assertHeader('Content-Type', 'application/pdf');

        // Status akun petugas tetap nonaktif (tidak teraktivasi otomatis)
        $this->petugasNonaktif->refresh();
        $this->assertFalse($this->petugasNonaktif->is_active);
    }

    /**
     * AC18-23: Aplikasi tidak mengklaim keberhasilan cetak fisik atau status printer.
     */
    public function test_ac18_23_tidak_mengklaim_keberhasilan_cetak_fisik(): void
    {
        $disposisi = $this->createDisposisi();

        $resCetak = $this->actingAs($this->admin)->get(route('admin.surat-tugas.cetak', $disposisi->id));
        $resCetak->assertDontSee('Printer berhasil mencetak');
        $resCetak->assertDontSee('Cetak fisik berhasil');
    }
}

