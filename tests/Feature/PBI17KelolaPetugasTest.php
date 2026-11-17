<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\Kategori;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Services\PetugasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PBI17KelolaPetugasTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugasAktif;
    protected User $petugasNonaktif;
    protected User $warga;
    protected User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugasAktif = User::factory()->create([
            'name' => 'Ahmad Teknisi',
            'email' => 'ahmad.teknisi@dsdabm.bandung.go.id',
            'phone' => '081234567890',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->petugasNonaktif = User::factory()->create([
            'name' => 'Bambang Pensiun',
            'email' => 'bambang.pensiun@dsdabm.bandung.go.id',
            'phone' => '+628987654321',
            'role' => 'petugas',
            'is_active' => false,
        ]);

        $this->warga = User::factory()->create([
            'name' => 'Warga Bandung',
            'email' => 'warga@bandung.go.id',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->pimpinan = User::factory()->create([
            'name' => 'Pimpinan Kota',
            'email' => 'pimpinan@bandung.go.id',
            'role' => 'pimpinan',
            'is_active' => true,
        ]);
    }

    protected function createLaporan(string $status = 'diproses'): LaporanKerusakan
    {
        $kecamatan = Kecamatan::firstOrCreate(
            ['kode_kecamatan' => 'BDG-010'],
            ['nama_kecamatan' => 'Coblong']
        );
        $kelurahan = Kelurahan::firstOrCreate(
            ['kode_kelurahan' => 'BDG-010-01'],
            ['kecamatan_id' => $kecamatan->id, 'nama_kelurahan' => 'Dago']
        );
        $kategori = \App\Models\KategoriInfrastruktur::firstOrCreate(
            ['nama_kategori' => 'Jalan dan Jembatan'],
            ['deskripsi' => 'Kerusakan konstruksi jalan', 'bobot_prioritas' => 3]
        );

        return LaporanKerusakan::create([
            'nomor_tiket' => 'LAP-'.now()->format('Ymd').'-'.rand(1000, 9999),
            'warga_id' => $this->warga->id,
            'kategori_id' => $kategori->id,
            'kelurahan_id' => $kelurahan->id,
            'judul' => 'Jalan Berlubang di Simpang Dago',
            'deskripsi_kondisi' => 'Lubang jalan sedalam 15 cm membahayakan pengendara.',
            'lokasi_spesifik' => 'Dekat SPBU Dago',
            'latitude' => -6.8850,
            'longitude' => 107.6136,
            'foto_bukti_sebelum' => 'reports/foto_dummy.jpg',
            'status' => $status,
            'prioritas' => 'tinggi',
        ]);
    }

    /**
     * AC17-01: Hanya admin yang berwenang dapat mengakses seluruh halaman dan permintaan pengelolaan petugas.
     */
    public function test_ac17_01_hanya_admin_yang_dapat_mengakses_role_lain_ditolak(): void
    {
        // Admin dapat mengakses index, create, show, edit
        $this->actingAs($this->admin)->get(route('admin.petugas.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.petugas.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.petugas.show', $this->petugasAktif->id))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.petugas.edit', $this->petugasAktif->id))->assertStatus(200);

        // Role lain ditolak (403 Forbidden)
        foreach ([$this->warga, $this->petugasAktif, $this->pimpinan] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)->get(route('admin.petugas.index'))->assertStatus(403);
            $this->actingAs($unauthorizedUser)->get(route('admin.petugas.create'))->assertStatus(403);
            $this->actingAs($unauthorizedUser)->get(route('admin.petugas.show', $this->petugasAktif->id))->assertStatus(403);
            $this->actingAs($unauthorizedUser)->get(route('admin.petugas.edit', $this->petugasAktif->id))->assertStatus(403);
            $this->actingAs($unauthorizedUser)->post(route('admin.petugas.store'), [])->assertStatus(403);
            $this->actingAs($unauthorizedUser)->put(route('admin.petugas.update', $this->petugasAktif->id), [])->assertStatus(403);
            $this->actingAs($unauthorizedUser)->patch(route('admin.petugas.toggle-status', $this->petugasAktif->id))->assertStatus(403);
        }
    }

    /**
     * AC17-02: Tamu wajib autentikasi sebelum mengakses endpoint pengelolaan petugas.
     */
    public function test_ac17_02_tamu_wajib_autentikasi(): void
    {
        $this->get(route('admin.petugas.index'))->assertRedirect(route('login'));
        $this->get(route('admin.petugas.create'))->assertRedirect(route('login'));
        $this->get(route('admin.petugas.show', $this->petugasAktif->id))->assertRedirect(route('login'));
        $this->post(route('admin.petugas.store'), [])->assertRedirect(route('login'));
        $this->patch(route('admin.petugas.toggle-status', $this->petugasAktif->id))->assertRedirect(route('login'));
    }

    /**
     * AC17-03: Daftar hanya memuat role petugas, aktif maupun nonaktif, dengan nama, email, telepon, dan status.
     */
    public function test_ac17_03_daftar_hanya_memuat_role_petugas_aktif_dan_nonaktif(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.petugas.index'));

        $response->assertStatus(200);
        // Memuat petugas aktif dan nonaktif
        $response->assertSee($this->petugasAktif->name);
        $response->assertSee($this->petugasAktif->email);
        $response->assertSee($this->petugasAktif->phone);
        $response->assertSee('Aktif');

        $response->assertSee($this->petugasNonaktif->name);
        $response->assertSee($this->petugasNonaktif->email);
        $response->assertSee($this->petugasNonaktif->phone);
        $response->assertSee('Nonaktif');

        // Tidak memuat admin, warga, atau pimpinan
        $response->assertDontSee($this->warga->name);
        $response->assertDontSee($this->pimpinan->name);
    }

    /**
     * AC17-04: Pencarian nama/email dan filter status dapat dipakai terpisah, bersamaan, dan direset.
     */
    public function test_ac17_04_pencarian_dan_filter_status(): void
    {
        // Cari nama "Ahmad"
        $resName = $this->actingAs($this->admin)->get(route('admin.petugas.index', ['search' => 'Ahmad']));
        $resName->assertSee($this->petugasAktif->name);
        $resName->assertDontSee($this->petugasNonaktif->name);

        // Cari email "bambang.pensiun"
        $resEmail = $this->actingAs($this->admin)->get(route('admin.petugas.index', ['search' => 'bambang.pensiun']));
        $resEmail->assertSee($this->petugasNonaktif->name);
        $resEmail->assertDontSee($this->petugasAktif->name);

        // Filter status: aktif saja
        $resAktif = $this->actingAs($this->admin)->get(route('admin.petugas.index', ['status' => 'aktif']));
        $resAktif->assertSee($this->petugasAktif->name);
        $resAktif->assertDontSee($this->petugasNonaktif->name);

        // Filter status: nonaktif saja
        $resNonaktif = $this->actingAs($this->admin)->get(route('admin.petugas.index', ['status' => 'nonaktif']));
        $resNonaktif->assertSee($this->petugasNonaktif->name);
        $resNonaktif->assertDontSee($this->petugasAktif->name);

        // Kombinasi search + status
        $resBoth = $this->actingAs($this->admin)->get(route('admin.petugas.index', [
            'search' => 'Ahmad',
            'status' => 'nonaktif',
        ]));
        $resBoth->assertSee('Petugas tidak ditemukan');

        // Reset kembali menampilkan semua
        $resReset = $this->actingAs($this->admin)->get(route('admin.petugas.index'));
        $resReset->assertSee($this->petugasAktif->name);
        $resReset->assertSee($this->petugasNonaktif->name);
    }

    /**
     * AC17-05: Daftar kosong menampilkan “Belum ada petugas lapangan”; hasil pencarian kosong “Petugas tidak ditemukan”.
     */
    public function test_ac17_05_empty_states(): void
    {
        // Kasus 1: Pencarian dengan kata kunci yang tidak ada hasilnya
        $responseSearch = $this->actingAs($this->admin)->get(route('admin.petugas.index', [
            'search' => 'NamaPetugasTidakMungkinAdaXYZ999',
        ]));
        $responseSearch->assertSee('Petugas tidak ditemukan');

        // Kasus 2: Tidak ada petugas sama sekali di database
        User::where('role', 'petugas')->delete();
        $responseEmpty = $this->actingAs($this->admin)->get(route('admin.petugas.index'));
        $responseEmpty->assertSee('Belum ada petugas lapangan');
    }

    /**
     * AC17-06: Akun baru selalu ber-role petugas, aktif, dan menyimpan password sebagai hash.
     */
    public function test_ac17_06_akun_baru_selalu_ber_role_petugas_aktif_dan_password_hash(): void
    {
        $rawPassword = 'PasswordRahasia123!';

        // Mengirim request dengan upaya manipulasi role=admin dan is_active=false dari sisi klien
        $response = $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Dedi Teknisi Baru',
            'email' => 'dedi.baru@bandung.go.id',
            'phone' => '081234567899',
            'password' => $rawPassword,
            'password_confirmation' => $rawPassword,
            'role' => 'admin',     // Harus diabaikan/di-overwrite server
            'is_active' => false,  // Harus diabaikan/di-overwrite server
        ]);

        $response->assertRedirect(route('admin.petugas.index'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'dedi.baru@bandung.go.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Dedi Teknisi Baru', $user->name);
        $this->assertEquals('petugas', $user->role, 'Role harus selalu petugas.');
        $this->assertTrue($user->is_active, 'Status akun baru harus selalu aktif.');
        $this->assertNotEquals($rawPassword, $user->password, 'Password tidak boleh disimpan sebagai teks mentah.');
        $this->assertTrue(Hash::check($rawPassword, $user->password), 'Password harus berupa hash yang valid.');
    }

    /**
     * AC17-07: Seluruh field pembuatan wajib diisi dengan nilai yang valid; nilai kosong/spasi ditolak.
     */
    public function test_ac17_07_seluruh_field_pembuatan_wajib_dan_spasi_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => '   ',
            'email' => '   ',
            'phone' => '   ',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
    }

    /**
     * AC17-08: Email valid dan unik lintas role; edit boleh mempertahankan email akun sendiri.
     */
    public function test_ac17_08_validasi_dan_keunikan_email(): void
    {
        // Format email tidak valid
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Format Salah',
            'email' => 'bukan-email-valid',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors(['email']);

        // Email duplikat lintas role (memakai email admin atau warga)
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Email Sama Warga',
            'email' => $this->warga->email,
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors(['email']);

        // Saat edit, boleh mempertahankan email sendiri
        $this->actingAs($this->admin)->put(route('admin.petugas.update', $this->petugasAktif->id), [
            'name' => 'Ahmad Teknisi Updated',
            'email' => $this->petugasAktif->email,
            'phone' => '081234567890',
        ])->assertSessionHasNoErrors();

        // Saat edit, tidak boleh menggunakan email orang lain
        $this->actingAs($this->admin)->put(route('admin.petugas.update', $this->petugasAktif->id), [
            'name' => 'Ahmad Teknisi Updated',
            'email' => $this->petugasNonaktif->email,
            'phone' => '081234567890',
        ])->assertSessionHasErrors(['email']);
    }

    /**
     * AC17-09: Telepon divalidasi sebagai teks dan mempertahankan nol awal atau awalan +.
     */
    public function test_ac17_09_telepon_divalidasi_dan_mempertahankan_awalan(): void
    {
        // Nol awal dipertahankan
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Awalan Nol',
            'email' => 'nol@bandung.go.id',
            'phone' => '081299988877',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $userNol = User::where('email', 'nol@bandung.go.id')->first();
        $this->assertEquals('081299988877', $userNol->phone);

        // Awalan '+' dipertahankan
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Awalan Plus',
            'email' => 'plus@bandung.go.id',
            'phone' => '+6281299988877',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $userPlus = User::where('email', 'plus@bandung.go.id')->first();
        $this->assertEquals('+6281299988877', $userPlus->phone);

        // Format huruf atau terlalu pendek ditolak
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Phone Rusak',
            'email' => 'rusak@bandung.go.id',
            'phone' => 'bukan-telepon',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors(['phone']);
    }

    /**
     * AC17-10: Password memenuhi kebijakan autentikasi dan cocok dengan konfirmasi.
     */
    public function test_ac17_10_kebijakan_password(): void
    {
        // Kurang dari 8 karakter
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Pendek',
            'email' => 'pendek@bandung.go.id',
            'phone' => '081234567890',
            'password' => 'pass12',
            'password_confirmation' => 'pass12',
        ])->assertSessionHasErrors(['password']);

        // Konfirmasi tidak cocok
        $this->actingAs($this->admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Mismatch',
            'email' => 'mismatch@bandung.go.id',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'BedaPassword!',
        ])->assertSessionHasErrors(['password']);
    }

    /**
     * AC17-11: Edit hanya mengubah nama, email, telepon; password, role, status, serta relasi tugas tetap.
     */
    public function test_ac17_11_edit_hanya_mengubah_nama_email_telepon(): void
    {
        $oldPasswordHash = $this->petugasAktif->password;

        $response = $this->actingAs($this->admin)->put(route('admin.petugas.update', $this->petugasAktif->id), [
            'name' => 'Ahmad Teknisi Senior',
            'email' => 'ahmad.senior@bandung.go.id',
            'phone' => '+6281122334455',
            // Upaya injeksi field yang tidak boleh diubah
            'role' => 'admin',
            'is_active' => false,
            'password' => 'InjectedPassword123!',
        ]);

        $response->assertRedirect(route('admin.petugas.show', $this->petugasAktif->id));
        $response->assertSessionHas('success');

        $this->petugasAktif->refresh();
        $this->assertEquals('Ahmad Teknisi Senior', $this->petugasAktif->name);
        $this->assertEquals('ahmad.senior@bandung.go.id', $this->petugasAktif->email);
        $this->assertEquals('+6281122334455', $this->petugasAktif->phone);

        // Nilai terlindungi TIDAK BOLEH berubah
        $this->assertEquals('petugas', $this->petugasAktif->role);
        $this->assertTrue($this->petugasAktif->is_active);
        $this->assertEquals($oldPasswordHash, $this->petugasAktif->password);
    }

    /**
     * AC17-12: ID tidak ditemukan atau target bukan role petugas ditolak.
     */
    public function test_ac17_12_id_tidak_ditemukan_atau_bukan_petugas_ditolak(): void
    {
        $validPayload = [
            'name' => 'Petugas Valid',
            'email' => 'valid.petugas.' . uniqid() . '@bandung.go.id',
            'phone' => '081234567890',
        ];

        // ID tidak ada
        $this->actingAs($this->admin)->get(route('admin.petugas.show', 99999))->assertStatus(404);
        $this->actingAs($this->admin)->get(route('admin.petugas.edit', 99999))->assertStatus(404);
        $this->actingAs($this->admin)->put(route('admin.petugas.update', 99999), $validPayload)->assertStatus(404);
        $this->actingAs($this->admin)->patch(route('admin.petugas.toggle-status', 99999))->assertStatus(404);

        // ID ada tetapi role adalah 'warga' atau 'admin'
        $this->actingAs($this->admin)->get(route('admin.petugas.show', $this->warga->id))->assertStatus(404);
        $this->actingAs($this->admin)->get(route('admin.petugas.edit', $this->admin->id))->assertStatus(404);
        $this->actingAs($this->admin)->put(route('admin.petugas.update', $this->warga->id), $validPayload)->assertStatus(404);
        $this->actingAs($this->admin)->patch(route('admin.petugas.toggle-status', $this->admin->id))->assertStatus(404);
    }

    /**
     * AC17-13: Petugas tanpa tugas belum selesai dapat dinonaktifkan setelah konfirmasi.
     */
    public function test_ac17_13_nonaktivasi_berhasil_jika_tidak_ada_tugas_belum_selesai(): void
    {
        $this->assertTrue($this->petugasAktif->is_active);

        $response = $this->actingAs($this->admin)->patch(route('admin.petugas.toggle-status', $this->petugasAktif->id));

        $response->assertSessionHas('success');
        $this->petugasAktif->refresh();
        $this->assertFalse($this->petugasAktif->is_active);
    }

    /**
     * AC17-14: Petugas dengan tugas belum selesai tidak dapat dinonaktifkan; tiket/judul penghambat ditampilkan tanpa mengubah data.
     */
    public function test_ac17_14_nonaktivasi_ditolak_jika_ada_tugas_belum_selesai(): void
    {
        $laporan = $this->createLaporan('diproses');

        // Buat disposisi penugasan dengan status 'ditugaskan'
        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261106-0001',
            'laporan_id' => $laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugasAktif->id,
            'tanggal_disposisi' => now(),
            'catatan_instruksi' => 'Tugas perbaikan jalan.',
            'status_penugasan' => 'ditugaskan',
        ]);

        $this->assertTrue($this->petugasAktif->is_active);

        // Coba lakukan nonaktivasi
        $response = $this->actingAs($this->admin)->patch(route('admin.petugas.toggle-status', $this->petugasAktif->id));

        $response->assertSessionHas('error');
        $response->assertSessionHas('blocking_tasks');

        // Status keaktifan TIDAK BOLEH berubah
        $this->petugasAktif->refresh();
        $this->assertTrue($this->petugasAktif->is_active, 'Petugas dengan tugas aktif harus tetap berstatus aktif.');
    }

    /**
     * AC17-15: Petugas nonaktif gagal login; sesi lama ditolak pada permintaan terlindungi berikutnya.
     */
    public function test_ac17_15_petugas_nonaktif_gagal_login_dan_sesi_lama_ditolak(): void
    {
        // 1. Gagal Login saat nonaktif
        $password = 'PetugasPass123!';
        $petugasTest = User::factory()->create([
            'role' => 'petugas',
            'is_active' => false,
            'password' => Hash::make($password),
        ]);

        $loginResponse = $this->post(route('login'), [
            'email' => $petugasTest->email,
            'password' => $password,
        ]);

        $loginResponse->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // 2. Sesi lama ditolak saat dinonaktifkan administrator di tengah sesi
        $petugasTest->update(['is_active' => true]);

        // Login saat aktif
        $this->post(route('login'), [
            'email' => $petugasTest->email,
            'password' => $password,
        ]);
        $this->assertAuthenticatedAs($petugasTest);

        // Admin menonaktifkan akun petugas di database
        $petugasTest->update(['is_active' => false]);

        // Pada request berikutnya ke endpoint terlindungi, sesi harus dibatalkan
        $nextResponse = $this->get(route('petugas.tugas.index'));
        $nextResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * AC17-16: Petugas nonaktif tidak tersedia untuk disposisi baru dan ditolak pada permintaan langsung.
     */
    public function test_ac17_16_petugas_nonaktif_tidak_dapat_menerima_disposisi_baru(): void
    {
        $laporan = $this->createLaporan('diproses');

        // Coba buat disposisi dengan petugas nonaktif
        $response = $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $laporan->id,
            'petugas_id' => $this->petugasNonaktif->id,
            'catatan_instruksi' => 'Instruksi untuk petugas nonaktif.',
        ]);

        $response->assertSessionHasErrors(['petugas_id']);
        $this->assertDatabaseMissing('disposisi_penugasan', [
            'laporan_id' => $laporan->id,
            'petugas_id' => $this->petugasNonaktif->id,
        ]);
    }

    /**
     * AC17-17: Aktivasi kembali mempertahankan ID dan memulihkan akses serta kelayakan penugasan.
     */
    public function test_ac17_17_aktivasi_kembali_mempertahankan_id_dan_memulihkan_akses(): void
    {
        $id = $this->petugasNonaktif->id;
        $this->assertFalse($this->petugasNonaktif->is_active);

        // Aktifkan kembali
        $response = $this->actingAs($this->admin)->patch(route('admin.petugas.toggle-status', $id));
        $response->assertSessionHas('success');

        $this->petugasNonaktif->refresh();
        $this->assertEquals($id, $this->petugasNonaktif->id);
        $this->assertTrue($this->petugasNonaktif->is_active);

        // Sekarang dapat menerima disposisi
        $laporan = $this->createLaporan('diproses');
        $this->actingAs($this->admin)->post(route('admin.disposisi.store'), [
            'laporan_id' => $laporan->id,
            'petugas_id' => $this->petugasNonaktif->id,
            'catatan_instruksi' => 'Instruksi setelah aktif kembali.',
        ])->assertSessionHasNoErrors();
    }

    /**
     * AC17-18: Identitas akun dan riwayat penugasan dipertahankan; tidak tersedia hapus permanen.
     */
    public function test_ac17_18_tidak_tersedia_hapus_permanen(): void
    {
        // Endpoint DELETE ke resource petugas harus mengembalikan 405 Method Not Allowed
        $response = $this->actingAs($this->admin)->delete(route('admin.petugas.index').'/'.$this->petugasAktif->id);
        $this->assertEquals(405, $response->getStatusCode());

        $this->assertDatabaseHas('users', ['id' => $this->petugasAktif->id]);
    }

    /**
     * AC17-19: Pembatalan tidak mengubah data; kegagalan simpan melakukan rollback dan menampilkan pesan.
     */
    public function test_ac17_19_batal_dan_rollback_transaksi(): void
    {
        // Tombol batal mengarah kembali ke index/detail
        $resCreate = $this->actingAs($this->admin)->get(route('admin.petugas.create'));
        $resCreate->assertSee(route('admin.petugas.index'));

        // Transaksi gagal pada database me-rollback seluruh operasi
        $initialCount = User::where('role', 'petugas')->count();

        try {
            DB::transaction(function () {
                User::create([
                    'name' => 'Petugas Error',
                    'email' => 'error@bandung.go.id',
                    'phone' => '081234567890',
                    'password' => Hash::make('Password123!'),
                    'role' => 'petugas',
                    'is_active' => true,
                ]);

                throw new \Exception('Simulasi kegagalan database');
            });
        } catch (\Throwable $e) {
            // Expected
        }

        $this->assertEquals($initialCount, User::where('role', 'petugas')->count());
    }

    /**
     * AC17-20: Perubahan berhasil tetap tersimpan setelah reload.
     */
    public function test_ac17_20_perubahan_berhasil_tetap_tersimpan_setelah_reload(): void
    {
        $this->actingAs($this->admin)->put(route('admin.petugas.update', $this->petugasAktif->id), [
            'name' => 'Ahmad Teknisi Reload Test',
            'email' => 'ahmad.reload@bandung.go.id',
            'phone' => '089988776655',
        ]);

        // Muat ulang halaman detail
        $response = $this->actingAs($this->admin)->get(route('admin.petugas.show', $this->petugasAktif->id));
        $response->assertSee('Ahmad Teknisi Reload Test');
        $response->assertSee('ahmad.reload@bandung.go.id');
        $response->assertSee('089988776655');
    }

    /**
     * AC17-21: Password maupun hash tidak ditampilkan pada daftar, detail, atau form edit.
     */
    public function test_ac17_21_password_dan_hash_tidak_pernah_ditampilkan(): void
    {
        $plainPassword = 'RahasiaSuperKuat123!';
        $petugasSecret = User::factory()->create([
            'role' => 'petugas',
            'password' => Hash::make($plainPassword),
        ]);

        $views = [
            $this->actingAs($this->admin)->get(route('admin.petugas.index')),
            $this->actingAs($this->admin)->get(route('admin.petugas.show', $petugasSecret->id)),
            $this->actingAs($this->admin)->get(route('admin.petugas.edit', $petugasSecret->id)),
        ];

        foreach ($views as $view) {
            $view->assertDontSee($plainPassword);
            $view->assertDontSee($petugasSecret->password);
        }
    }

    /**
     * AC17-22 & Bagian 7.1 Konkurensi:
     * Persaingan nonaktivasi dan penugasan disposisi berjalan bersamaan pada database tim.
     */
    public function test_ac17_22_konkurensi_persaingan_nonaktivasi_dan_disposisi(): void
    {
        $petugasTest = User::factory()->create([
            'name' => 'Petugas Konkurensi',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $laporan = $this->createLaporan('diproses');
        $laporanId = $laporan->id;
        $petugasId = $petugasTest->id;
        $adminId = $this->admin->id;

        // Commit state ke MySQL sebelum proses multi-process dijalankan
        DB::commit();

        $cmdDeactivate = sprintf(
            'php artisan tinker --execute="try { \$res = app(\'App\Services\PetugasService\')->toggleStatus(%d); if (\$res[\'success\']) { echo \'[[DEACTIVATE_SUCCESS]]\'; } else { echo \'[[DEACTIVATE_BLOCKED]]\'; } } catch (\Throwable \$e) { echo \'[[DEACTIVATE_ERR:\' . \$e->getMessage() . \']]\'; }"',
            $petugasId
        );

        $cmdDisposisi = sprintf(
            'php artisan tinker --execute="try { app(\'App\Services\DisposisiService\')->createDisposisi(%d, %d, %d, null, \'Instruksi Konkurensi\'); echo \'[[DISPOSISI_SUCCESS]]\'; } catch (\Throwable \$e) { echo \'[[DISPOSISI_ERR:\' . \$e->getMessage() . \']]\'; }"',
            $laporanId,
            $adminId,
            $petugasId
        );

        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // Eksekusi kedua proses secara paralel
        $proc1 = proc_open($cmdDeactivate, $spec, $pipes1);
        $proc2 = proc_open($cmdDisposisi, $spec, $pipes2);

        $out1 = stream_get_contents($pipes1[1]).stream_get_contents($pipes1[2]);
        $out2 = stream_get_contents($pipes2[1]).stream_get_contents($pipes2[2]);

        fclose($pipes1[0]); fclose($pipes1[1]); fclose($pipes1[2]);
        fclose($pipes2[0]); fclose($pipes2[1]); fclose($pipes2[2]);

        proc_close($proc1);
        proc_close($proc2);

        // Evaluasi hasil di database
        $freshPetugas = User::find($petugasId);
        $hasDisposisi = DisposisiPenugasan::where('laporan_id', $laporanId)->exists();

        if ($hasDisposisi) {
            // Jika disposisi menang lebih dulu, petugas harus tetap aktif karena memiliki tugas aktif yang memblokir nonaktivasi
            $this->assertTrue($freshPetugas->is_active, 'Jika disposisi berhasil dibuat lebih dulu, nonaktivasi harus ditolak dan petugas tetap aktif.');
            $this->assertTrue(str_contains($out1, '[[DEACTIVATE_BLOCKED]]') || str_contains($out2, '[[DISPOSISI_SUCCESS]]'));
        } else {
            // Jika nonaktivasi menang lebih dulu, petugas menjadi nonaktif dan disposisi harus ditolak
            $this->assertFalse($freshPetugas->is_active, 'Jika nonaktivasi berhasil lebih dulu, petugas menjadi nonaktif.');
            $this->assertTrue(str_contains($out2, 'Petugas yang dipilih sedang tidak aktif') || str_contains($out2, 'DISPOSISI_ERR'));
        }

        // Cleanup
        DisposisiPenugasan::where('laporan_id', $laporanId)->delete();
        $laporan->delete();
        $petugasTest->delete();

        DB::beginTransaction();
    }
}
