<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\User;
use App\Services\LaporanService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_report_photo_extension_is_derived_from_server_mime(): void
    {
        Storage::fake('public');

        $kecamatan = Kecamatan::create(['kode_kecamatan' => 'BDG-01', 'nama_kecamatan' => 'Coblong']);
        $kelurahan = Kelurahan::create(['kecamatan_id' => $kecamatan->id, 'kode_kelurahan' => 'BDG-01-01', 'nama_kelurahan' => 'Dago']);
        $kategori = KategoriInfrastruktur::create(['nama_kategori' => 'Jalan', 'deskripsi' => 'Tes', 'bobot_prioritas' => 2]);
        $warga = User::factory()->create(['role' => 'warga']);

        $service = app(LaporanService::class);
        $fakeFile = UploadedFile::fake()->create('exploit.php', 100, 'image/jpeg');

        $laporan = $service->createLaporan(
            $warga->id,
            [
                'kelurahan_id' => $kelurahan->id,
                'kategori_id' => $kategori->id,
                'judul' => 'Jalan Rusak',
                'deskripsi_kondisi' => 'Kondisi parah',
                'latitude' => -6.90000000,
                'longitude' => 107.61000000,
            ],
            $fakeFile
        );

        $this->assertStringEndsWith('.jpg', $laporan->foto_bukti_sebelum);
        $this->assertStringNotContainsString('.php', $laporan->foto_bukti_sebelum);
        Storage::disk('public')->assertExists($laporan->foto_bukti_sebelum);
    }

    public function test_uploaded_avatar_extension_is_derived_from_server_mime(): void
    {
        Storage::fake('public');

        $service = app(UserService::class);
        $fakeAvatar = UploadedFile::fake()->create('avatar.php', 100, 'image/png');

        $user = $service->createUser(
            [
                'name' => 'Budi Petugas',
                'email' => 'budi@test.com',
                'role' => 'petugas',
                'password' => 'password123',
            ],
            $fakeAvatar
        );

        $this->assertNotNull($user->avatar);
        $this->assertStringEndsWith('.png', $user->avatar);
        $this->assertStringNotContainsString('.php', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }
}
