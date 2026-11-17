<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Services\DisposisiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PBI16BuatLembarDisposisiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugasAktif;
    private User $petugasNonaktif;
    private User $warga;
    private User $pimpinan;
    private LaporanKerusakan $laporan;
    private Kecamatan $kecamatan;
    private Kelurahan $kelurahan;
    private KategoriInfrastruktur $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin DSDABM Pusat',
            'email' => 'admin.dsdabm.' . uniqid() . '@bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugasAktif = User::factory()->create([
            'name' => 'Asep Sutisna',
            'email' => 'asep.petugas.' . uniqid() . '@dsdabm.bandung.go.id',
            'phone' => '081234567891',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->petugasNonaktif = User::factory()->create([
            'name' => 'Cecep Nonaktif',
            'email' => 'cecep.petugas.' . uniqid() . '@dsdabm.bandung.go.id',
            'phone' => '081234567892',
            'role' => 'petugas',
            'is_active' => false,
        ]);

        $this->warga = User::factory()->create([
            'name' => 'Budi Warga Bandung',
            'email' => 'budi.warga.' . uniqid() . '@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->pimpinan = User::factory()->create([
            'name' => 'Kadis DSDABM',
            'email' => 'kadis.' . uniqid() . '@bandung.go.id',
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
            ['nama_kategori' => 'Jalan dan Jembatan'],
            ['deskripsi' => 'Kerusakan konstruksi jalan dan jembatan kota', 'bobot_prioritas' => 3]
        );

        $this->laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-' . date('Ymd') . '-' . rand(1000, 9999),
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Jalan Ambles Dekat Simpang Dago',
            'deskripsi_kondisi' => 'Aspal ambles sedalam 20 cm membahayakan kendaraan roda dua.',
            'latitude' => -6.8850,
            'longitude' => 107.6130,
            'foto_bukti_sebelum' => 'laporan/sebelum/dago.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);
    }

    /**
     * AC16-01: Admin yang berwenang dapat mengakses daftar, form, detail, dan penyimpanan; role lain ditolak.
     */
    public function test_ac16_01_admin_berwenang_dapat_mengakses_dan_role_lain_ditolak(): void
    {
        // 1. Admin berwenang: Dapat mengakses seluruh endpoint disposisi
        $this->actingAs($this->admin)->get(route('admin.disposisi.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.disposisi.create', ['laporan_id' => $this->laporan->id]))->assertStatus(200);

        // 2. Warga ditolak (403)
        $this->actingAs($this->warga)->get(route('admin.disposisi.index'))->assertStatus(403);
        $this->actingAs($this->warga)->get(route('admin.disposisi.create'))->assertStatus(403);
        $this->actingAs($this->warga)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Instruksi tidak sah',
        ])->assertStatus(403);

        // 3. Petugas ditolak (403)
        $this->actingAs($this->petugasAktif)->get(route('admin.disposisi.index'))->assertStatus(403);
        $this->actingAs($this->petugasAktif)->get(route('admin.disposisi.create'))->assertStatus(403);

        // 4. Pimpinan ditolak (403)
        $this->actingAs($this->pimpinan)->get(route('admin.disposisi.index'))->assertStatus(403);
        $this->actingAs($this->pimpinan)->get(route('admin.disposisi.create'))->assertStatus(403);
    }

    /**
     * AC16-02: Tamu wajib autentikasi sebelum mengakses endpoint disposisi.
     */
    public function test_ac16_02_tamu_wajib_autentikasi_sebelum_mengakses(): void
    {
        $this->get(route('admin.disposisi.index'))->assertRedirect(route('login'));
        $this->get(route('admin.disposisi.create'))->assertRedirect(route('login'));
        $this->post(route('admin.disposisi.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.disposisi.show', 1))->assertRedirect(route('login'));
    }

    /**
     * AC16-03: Daftar hanya berisi laporan berstatus diproses yang belum memiliki disposisi.
     */
    public function test_ac16_03_daftar_hanya_berisi_laporan_diproses_yang_belum_memiliki_disposisi(): void
    {
        // Laporan 1: status menunggu_verifikasi (belum disetujui)
        $laporanBelumVerif = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261106-0002',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Laporan Menunggu Verifikasi',
            'deskripsi_kondisi' => 'Deskripsi laporan baru',
            'latitude' => -6.8851,
            'longitude' => 107.6131,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample1.jpg',
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'sedang',
        ]);

        // Laporan 2: status diproses tetapi SUDAH memiliki disposisi
        $laporanSudahDisposisi = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261106-0003',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Laporan Yang Sudah Ditugaskan',
            'deskripsi_kondisi' => 'Deskripsi laporan sudah ada disposisi',
            'latitude' => -6.8852,
            'longitude' => 107.6132,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample2.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);
        DisposisiPenugasan::factory()->create([
            'laporan_id' => $laporanSudahDisposisi->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugasAktif->id,
        ]);

        // Laporan 3: status ditolak
        $laporanDitolak = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261106-0004',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Laporan Yang Ditolak Admin',
            'deskripsi_kondisi' => 'Deskripsi laporan ditolak',
            'latitude' => -6.8853,
            'longitude' => 107.6133,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample3.jpg',
            'status' => 'ditolak',
            'prioritas' => 'rendah',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.index'));

        $response->assertStatus(200);
        // Laporan layak (status diproses, belum ada disposisi) HARUS tampil
        $response->assertSee($this->laporan->nomor_tiket);
        $response->assertSee($this->laporan->judul);

        // Laporan tidak layak TIDAK BOLEH tampil di daftar siap disposisi (tidak ada tombol buat disposisi)
        $response->assertDontSee($laporanBelumVerif->nomor_tiket);
        $response->assertDontSee($laporanDitolak->nomor_tiket);
        $response->assertDontSee('btn-buat-disposisi-' . $laporanSudahDisposisi->id);
        $this->assertFalse(LaporanKerusakan::siapDisposisi()->pluck('id')->contains($laporanSudahDisposisi->id));
        $this->assertTrue(LaporanKerusakan::siapDisposisi()->pluck('id')->contains($this->laporan->id));
    }

    /**
     * AC16-04: Daftar memuat tiket, judul, kategori, kelurahan/kecamatan, prioritas, status, dan aksi Buat Disposisi.
     */
    public function test_ac16_04_daftar_memuat_seluruh_kolom_yang_dipersyaratkan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.index'));

        $response->assertStatus(200);
        $response->assertSee($this->laporan->nomor_tiket);
        $response->assertSee($this->laporan->judul);
        $response->assertSee($this->kategori->nama_kategori);
        $response->assertSee('Kel. ' . $this->kelurahan->nama_kelurahan);
        $response->assertSee('Kec. ' . $this->kecamatan->nama_kecamatan);
        $response->assertSee('Prioritas ' . ucfirst($this->laporan->prioritas));
        $response->assertSee('diproses');
        $response->assertSee('Buat Disposisi');
        $response->assertSee(route('admin.disposisi.create', ['laporan_id' => $this->laporan->id]));
    }

    /**
     * AC16-05: Pencarian mendukung nomor tiket atau judul; menghapus pencarian mengembalikan daftar awal.
     */
    public function test_ac16_05_pencarian_nomor_tiket_judul_dan_reset(): void
    {
        $laporanKedua = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261106-0099',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Jembatan Penyeberangan Rusak',
            'deskripsi_kondisi' => 'Baut jembatan lepas membahayakan pejalan kaki.',
            'latitude' => -6.8854,
            'longitude' => 107.6134,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample4.jpg',
            'status' => 'diproses',
            'prioritas' => 'sedang',
        ]);

        // Cari berdasarkan nomor tiket
        $responseTiket = $this->actingAs($this->admin)->get(route('admin.disposisi.index', [
            'search_laporan' => $this->laporan->nomor_tiket,
        ]));
        $responseTiket->assertStatus(200);
        $responseTiket->assertSee($this->laporan->nomor_tiket);
        $responseTiket->assertDontSee($laporanKedua->nomor_tiket);

        // Cari berdasarkan judul
        $responseJudul = $this->actingAs($this->admin)->get(route('admin.disposisi.index', [
            'search_laporan' => 'Jembatan Penyeberangan',
        ]));
        $responseJudul->assertStatus(200);
        $responseJudul->assertSee($laporanKedua->nomor_tiket);
        $responseJudul->assertDontSee($this->laporan->judul);

        // Hapus pencarian (reset) mengembalikan seluruh daftar awal
        $responseReset = $this->actingAs($this->admin)->get(route('admin.disposisi.index'));
        $responseReset->assertStatus(200);
        $responseReset->assertSee($this->laporan->nomor_tiket);
        $responseReset->assertSee($laporanKedua->nomor_tiket);
    }

    /**
     * AC16-06: Daftar kosong menampilkan “Belum ada laporan yang dapat didisposisikan”; pencarian kosong “Laporan tidak ditemukan”.
     */
    public function test_ac16_06_pesan_daftar_kosong_dan_pencarian_kosong(): void
    {
        // 1. Kondisi pencarian tidak menemukan hasil
        $responseCariNihil = $this->actingAs($this->admin)->get(route('admin.disposisi.index', [
            'search_laporan' => 'TIKET-TIDAK-PERNAH-ADA-999',
        ]));
        $responseCariNihil->assertStatus(200);
        $responseCariNihil->assertSee('Laporan tidak ditemukan');

        // 2. Hapus seluruh laporan sehingga daftar benar-benar kosong
        LaporanKerusakan::query()->delete();

        $responseKosong = $this->actingAs($this->admin)->get(route('admin.disposisi.index'));
        $responseKosong->assertStatus(200);
        $responseKosong->assertSee('Belum ada laporan yang dapat didisposisikan');
    }

    /**
     * AC16-07: Form menampilkan detail, foto, lokasi, dan prioritas laporan secara baca-saja.
     */
    public function test_ac16_07_form_menampilkan_detail_foto_lokasi_prioritas_baca_saja(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.create', [
            'laporan_id' => $this->laporan->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($this->laporan->nomor_tiket);
        $response->assertSee($this->laporan->judul);
        $response->assertSee($this->laporan->deskripsi_kondisi);
        $response->assertSee((string) $this->laporan->latitude);
        $response->assertSee((string) $this->laporan->longitude);
        $response->assertSee('Prioritas ' . ucfirst($this->laporan->prioritas));
        $response->assertSee($this->laporan->foto_bukti_sebelum);
    }

    /**
     * AC16-08: Petugas wajib dipilih dari petugas aktif; ID tidak ada, role salah, atau akun nonaktif ditolak server.
     */
    public function test_ac16_08_petugas_wajib_aktif_dan_role_petugas(): void
    {
        // Kasus A: Petugas ID tidak ada
        $responseInvalidId = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => 999999,
            'catatan_instruksi' => 'Instruksi penanganan jalan',
        ]);
        $responseInvalidId->assertSessionHasErrors(['petugas_id']);

        // Kasus B: Petugas ber-role bukan petugas (misal: warga)
        $responseBukanPetugas = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->warga->id,
            'catatan_instruksi' => 'Instruksi penanganan jalan',
        ]);
        $responseBukanPetugas->assertSessionHasErrors(['petugas_id']);

        // Kasus C: Petugas nonaktif ditolak
        $responseNonaktif = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasNonaktif->id,
            'catatan_instruksi' => 'Instruksi penanganan jalan',
        ]);
        $responseNonaktif->assertSessionHasErrors(['petugas_id']);

        $this->assertDatabaseMissing('disposisi_penugasan', [
            'laporan_id' => $this->laporan->id,
        ]);
    }

    /**
     * AC16-09: Instruksi wajib berisi karakter nonspasi; instruksi satu karakter diterima.
     */
    public function test_ac16_09_instruksi_wajib_karakter_nonspasi_dan_satu_karakter_diterima(): void
    {
        // 1. Instruksi kosong ditolak
        $resKosong = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => '',
        ]);
        $resKosong->assertSessionHasErrors(['catatan_instruksi']);

        // 2. Instruksi hanya berisi spasi/whitespace ditolak
        $resHanyaSpasi = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => "   \t \n  ",
        ]);
        $resHanyaSpasi->assertSessionHasErrors(['catatan_instruksi']);

        // 3. Instruksi satu karakter (nonspasi) DITERIMA
        $resSatuKarakter = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'P',
        ]);
        $resSatuKarakter->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('disposisi_penugasan', [
            'laporan_id' => $this->laporan->id,
            'catatan_instruksi' => 'P',
        ]);
    }

    /**
     * AC16-10 & AC16-11:
     * Input bisnis yang dipakai hanya laporan_id, petugas_id, dan catatan_instruksi.
     * Admin, waktu, dan status awal ditentukan server; manipulasi nilai klien tidak menggantikannya.
     */
    public function test_ac16_10_dan_ac16_11_server_menetapkan_admin_waktu_status_dan_abaikan_manipulasi_klien(): void
    {
        $penyerang = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $payload = [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Instruksi resmi dari server.',
            // Manipulasi klien yang harus diabaikan/digantikan oleh server:
            'admin_id' => $penyerang->id,
            'tanggal_disposisi' => '1999-01-01 00:00:00',
            'status_penugasan' => 'selesai',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), $payload);
        $response->assertSessionDoesntHaveErrors();

        $disposisi = DisposisiPenugasan::where('laporan_id', $this->laporan->id)->first();
        $this->assertNotNull($disposisi);

        // Admin pembuat harus diambil dari sesi otentikasi ($this->admin), bukan manipulasi klien ($penyerang)
        $this->assertEquals($this->admin->id, $disposisi->admin_id);

        // Status awal harus selalu 'ditugaskan'
        $this->assertEquals('ditugaskan', $disposisi->status_penugasan);

        // Tanggal disposisi harus berupa waktu hari ini dari server, bukan 1999
        $this->assertEquals(now()->toDateString(), $disposisi->tanggal_disposisi->toDateString());
    }

    /**
     * AC16-12: Satu laporan hanya mempunyai satu disposisi, termasuk ketika permintaan diulang atau datang bersamaan.
     */
    public function test_ac16_12_satu_laporan_hanya_mempunyai_satu_disposisi_pada_permintaan_berulang(): void
    {
        // Permintaan pertama: Berhasil
        $res1 = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Penugasan pertama.',
        ]);
        $res1->assertSessionDoesntHaveErrors();

        // Permintaan kedua untuk laporan yang sama: Ditolak
        $res2 = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Penugasan kedua yang duplikat.',
        ]);
        $res2->assertSessionHasErrors(['laporan_id']);

        // Pastikan hanya ada tepat satu record di database
        $this->assertEquals(1, DisposisiPenugasan::where('laporan_id', $this->laporan->id)->count());
    }

    /**
     * AC16-13: Kelayakan laporan diperiksa kembali ketika menyimpan sehingga form lama tidak melewati aturan.
     */
    public function test_ac16_13_kelayakan_laporan_diperiksa_kembali_saat_menyimpan_form_lama(): void
    {
        // Simulasikan form dibuka saat laporan berstatus diproses,
        // namun sebelum admin menekan tombol simpan, laporan telah dibatalkan/ditolak oleh admin lain
        $this->laporan->update(['status' => 'ditolak']);

        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Mencoba simpan laporan yang statusnya sudah berubah.',
        ]);

        $response->assertSessionHasErrors(['laporan_id']);
        $this->assertDatabaseMissing('disposisi_penugasan', [
            'laporan_id' => $this->laporan->id,
        ]);
    }

    /**
     * AC16-14: Sukses mengarah ke detail; data tetap setelah reload dan laporan keluar dari daftar siap disposisi.
     */
    public function test_ac16_14_sukses_mengarah_ke_detail_data_persisten_dan_keluar_dari_daftar(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Lakukan perbaikan drainase.',
        ]);

        $disposisi = DisposisiPenugasan::where('laporan_id', $this->laporan->id)->firstOrFail();
        $response->assertRedirect(route('admin.disposisi.show', $disposisi->id));

        // Reload halaman detail: Data tetap utuh
        $detailResponse = $this->actingAs($this->admin)->get(route('admin.disposisi.show', $disposisi->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($disposisi->nomor_surat_tugas);
        $detailResponse->assertSee('Lakukan perbaikan drainase.');

        // Laporan keluar dari daftar siap disposisi
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.disposisi.index'));
        $indexResponse->assertStatus(200);
        // Di bagian daftar laporan siap disposisi, nomor tiket ini tidak boleh lagi ada
        $this->assertCount(0, LaporanKerusakan::siapDisposisi()->get());
    }

    /**
     * AC16-15: Pembuatan disposisi tidak mengubah isi, prioritas, status, atau kepemilikan laporan.
     */
    public function test_ac16_15_pembuatan_disposisi_tidak_mengubah_data_laporan(): void
    {
        $judulAwal = $this->laporan->judul;
        $deskripsiAwal = $this->laporan->deskripsi_kondisi;
        $prioritasAwal = $this->laporan->prioritas;
        $statusAwal = $this->laporan->status;
        $wargaIdAwal = $this->laporan->warga_id;

        $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $this->laporan->id,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Instruksi teknis.',
        ]);

        $this->laporan->refresh();

        $this->assertEquals($judulAwal, $this->laporan->judul);
        $this->assertEquals($deskripsiAwal, $this->laporan->deskripsi_kondisi);
        $this->assertEquals($prioritasAwal, $this->laporan->prioritas);
        $this->assertEquals($statusAwal, $this->laporan->status); // Tetap 'diproses'
        $this->assertEquals($wargaIdAwal, $this->laporan->warga_id); // Pemilik warga tidak berganti
    }

    /**
     * AC16-16: Batal kembali ke daftar tanpa menyimpan.
     */
    public function test_ac16_16_batal_kembali_ke_daftar_tanpa_menyimpan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.create', ['laporan_id' => $this->laporan->id]));
        $response->assertStatus(200);
        $response->assertSee(route('admin.disposisi.index'));

        // Tidak ada record disposisi yang dibuat
        $this->assertEquals(0, DisposisiPenugasan::count());
    }

    /**
     * AC16-17: Kegagalan penyimpanan membatalkan seluruh transaksi dan menampilkan pesan kegagalan.
     */
    public function test_ac16_17_kegagalan_penyimpanan_membatalkan_transaksi_secara_atomik(): void
    {
        // Kirim request yang gagal pada level service/database (misal laporan tidak ditemukan)
        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => 999999,
            'petugas_id' => $this->petugasAktif->id,
            'catatan_instruksi' => 'Instruksi gagal.',
        ]);

        $response->assertSessionHasErrors(['laporan_id']);
        $this->assertDatabaseCount('disposisi_penugasan', 0);
    }

    /**
     * AC16-18: Detail memuat laporan, petugas, admin, tanggal, instruksi, dan status penugasan.
     */
    public function test_ac16_18_detail_memuat_laporan_petugas_admin_tanggal_instruksi_status(): void
    {
        $disposisi = DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261106-9999',
            'laporan_id' => $this->laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugasAktif->id,
            'tanggal_disposisi' => now(),
            'catatan_instruksi' => 'Periksa kondisi gorong-gorong sedalam 2 meter.',
            'status_penugasan' => 'ditugaskan',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.disposisi.show', $disposisi->id));

        $response->assertStatus(200);
        $response->assertSee($this->laporan->nomor_tiket);
        $response->assertSee($this->laporan->judul);
        $response->assertSee($this->petugasAktif->name);
        $response->assertSee($this->admin->name);
        $response->assertSee($disposisi->tanggal_disposisi->translatedFormat('d F Y'));
        $response->assertSee('Periksa kondisi gorong-gorong sedalam 2 meter.');
        $response->assertSee('Ditugaskan');
    }

    /**
     * PBI-16 & Bagian 7.1 Konkurensi:
     * Uji pencegahan disposisi ganda dengan dua proses yang benar-benar berjalan bersamaan.
     */
    public function test_konkurensi_dua_permintaan_bersamaan_hanya_menghasilkan_satu_disposisi(): void
    {
        $laporanId = $this->laporan->id;
        $adminId = $this->admin->id;
        $petugasId = $this->petugasAktif->id;

        // Commit data setUp ke database MySQL agar dapat diakses oleh proses paralel independen
        DB::commit();

        $cmd1 = sprintf(
            'php artisan tinker --execute="try { app(\'App\Services\DisposisiService\')->createDisposisi(%d, %d, %d, null, \'Instruksi Paralel A\'); echo \'[[PROC_A_SUCCESS]]\'; } catch (\Throwable \$e) { echo \'[[PROC_A_FAILED:\' . \$e->getMessage() . \']]\'; }"',
            $laporanId,
            $adminId,
            $petugasId
        );
        $cmd2 = sprintf(
            'php artisan tinker --execute="try { app(\'App\Services\DisposisiService\')->createDisposisi(%d, %d, %d, null, \'Instruksi Paralel B\'); echo \'[[PROC_B_SUCCESS]]\'; } catch (\Throwable \$e) { echo \'[[PROC_B_FAILED:\' . \$e->getMessage() . \']]\'; }"',
            $laporanId,
            $adminId,
            $petugasId
        );

        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // Jalankan kedua proses bersamaan secara asynchronous
        $proc1 = proc_open($cmd1, $spec, $pipes1);
        $proc2 = proc_open($cmd2, $spec, $pipes2);

        $out1 = stream_get_contents($pipes1[1]) . stream_get_contents($pipes1[2]);
        $out2 = stream_get_contents($pipes2[1]) . stream_get_contents($pipes2[2]);

        fclose($pipes1[0]); fclose($pipes1[1]); fclose($pipes1[2]);
        fclose($pipes2[0]); fclose($pipes2[1]); fclose($pipes2[2]);

        proc_close($proc1);
        proc_close($proc2);

        $totalDisposisi = DisposisiPenugasan::where('laporan_id', $laporanId)->count();
        $this->assertEquals(1, $totalDisposisi, 'Tepat satu disposisi harus tersimpan di database saat dua permintaan berjalan bersamaan.');

        $successCount = (str_contains($out1, '[[PROC_A_SUCCESS]]') ? 1 : 0) + (str_contains($out2, '[[PROC_B_SUCCESS]]') ? 1 : 0);
        $failedCount = (str_contains($out1, '[[PROC_A_FAILED:') ? 1 : 0) + (str_contains($out2, '[[PROC_B_FAILED:') ? 1 : 0);

        $this->assertEquals(1, $successCount, 'Tepat satu proses harus berhasil.');
        $this->assertEquals(1, $failedCount, 'Tepat satu proses harus ditolak/gagal.');

        // Bersihkan data yang di-commit secara manual agar tidak mengotori test lain
        DisposisiPenugasan::where('laporan_id', $laporanId)->delete();
        $this->laporan->delete();

        // Buka transaksi kembali untuk kompatibilitas teardown RefreshDatabase
        DB::beginTransaction();
    }

    /**
     * PBI-16 & Bagian 7.1 Konkurensi:
     * Persaingan antara penugasan disposisi baru dan nonaktivasi petugas.
     */
    public function test_konkurensi_persaingan_penugasan_dan_nonaktivasi_petugas(): void
    {
        // Kasus 1: Petugas telah dinonaktifkan sebelum penugasan selesai diproses -> penugasan harus ditolak
        $petugasBaru = User::factory()->create(['role' => 'petugas', 'is_active' => true]);

        // Simulasikan transaksi nonaktivasi berhasil terlebih dahulu
        $petugasBaru->update(['is_active' => false]);

        $this->expectException(\InvalidArgumentException::class);
        $service = app(DisposisiService::class);
        $service->createDisposisi(
            $this->laporan->id,
            $this->admin->id,
            $petugasBaru->id,
            null,
            'Penugasan setelah petugas nonaktif'
        );
    }
}
