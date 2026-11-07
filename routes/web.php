<?php

use App\Http\Controllers\Admin\DisposisiController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\KecamatanController;
use App\Http\Controllers\Admin\KelurahanController;
use App\Http\Controllers\Admin\PetugasController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Admin\SuratTugasController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserLogController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Api\WilayahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Publik\PetaPublikController;
use App\Http\Controllers\Publik\TrackingController;
use App\Http\Controllers\StorageFileController;
use App\Http\Controllers\Warga\LaporanController;
use App\Http\Controllers\Warga\UmpanBalikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - INFRA-DAYA Kota Bandung
|--------------------------------------------------------------------------
*/

// Halaman Publik Warga
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/peta-publik', function () {
    return view('welcome');
})->name('peta.publik');

Route::get('/lacak-tiket', function () {
    return view('welcome');
})->name('tracking.publik');

// Dashboard Utama Terotentikasi
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// API Endpoint Publik / Web
Route::get('/api/wilayah/kecamatan/{id}/kelurahan', [WilayahController::class, 'kelurahanByKecamatan'])->name('api.wilayah.kelurahan');

// Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Warga
    Route::middleware('role:warga,admin')->prefix('warga')->name('warga.')->group(function () {
        Route::get('/pilih-wilayah', [LaporanController::class, 'pilihWilayah'])->name('laporan.pilih-wilayah');
        Route::get('/laporan-saya', [LaporanController::class, 'index'])->name('laporan.saya');
        Route::get('/lapor', [LaporanController::class, 'create'])->name('laporan.create');
        Route::post('/lapor', [LaporanController::class, 'store'])->name('laporan.store');
        Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
        Route::get('/laporan/{laporan}/edit', [LaporanController::class, 'edit'])->name('laporan.edit');
        Route::put('/laporan/{laporan}', [LaporanController::class, 'update'])->name('laporan.update');
        Route::delete('/laporan/{laporan}', [LaporanController::class, 'destroy'])->name('laporan.destroy');
        Route::post('/laporan/{laporan}/batal', [LaporanController::class, 'destroy'])->name('laporan.batal');
        Route::post('/laporan/{laporan}/umpan-balik', [UmpanBalikController::class, 'store'])->name('laporan.umpan-balik');
    });

    // Route Admin DSDABM
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('kecamatan', KecamatanController::class)->except(['show']);
        Route::resource('kelurahan', KelurahanController::class)->except(['show']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::get('user-logs', [UserLogController::class, 'index'])->name('user-logs.index');
        Route::resource('users', UserController::class);
        Route::resource('kategori', KategoriController::class)->except(['show']);

        // PBI-17: Kelola Petugas Lapangan (IFD-18)
        Route::get('/petugas', [PetugasController::class, 'index'])->name('petugas.index');
        Route::get('/petugas/create', [PetugasController::class, 'create'])->name('petugas.create');
        Route::post('/petugas', [PetugasController::class, 'store'])->name('petugas.store');
        Route::get('/petugas/{id}', [PetugasController::class, 'show'])->name('petugas.show');
        Route::get('/petugas/{id}/edit', [PetugasController::class, 'edit'])->name('petugas.edit');
        Route::put('/petugas/{id}', [PetugasController::class, 'update'])->name('petugas.update');
        Route::patch('/petugas/{id}/toggle-status', [PetugasController::class, 'toggleStatus'])->name('petugas.toggle-status');

        // PBI-13: Daftar Aduan & Filter Laporan Masuk
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        // PBI-14: Verifikasi Detail Aduan, Terima & Tolak Laporan
        Route::get('/verifikasi/{id}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{id}/setujui', [VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
        Route::post('/verifikasi/{id}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');

        // PBI-15: Penetapan & Pembaruan Skala Prioritas Penanganan
        Route::patch('/verifikasi/{id}/prioritas', [VerifikasiController::class, 'updatePrioritas'])->name('verifikasi.prioritas');

        // Fase 6: Disposisi Penugasan & Surat Tugas PDF
        Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi.index');
        Route::get('/disposisi/create', [DisposisiController::class, 'create'])->name('disposisi.create');
        Route::post('/disposisi', [DisposisiController::class, 'store'])->name('disposisi.store');
        Route::get('/disposisi/{id}', [DisposisiController::class, 'show'])->name('disposisi.show');
        Route::get('/disposisi/{id}/pdf', [DisposisiController::class, 'cetakPdf'])->name('disposisi.pdf');

        // PBI-18: Pencetakan Surat Tugas (IFD-17)
        Route::get('/surat-tugas', [SuratTugasController::class, 'index'])->name('surat-tugas.index');
        Route::get('/surat-tugas/{id}', [SuratTugasController::class, 'show'])->name('surat-tugas.show');
        Route::get('/surat-tugas/{id}/pdf', [SuratTugasController::class, 'pdf'])->name('surat-tugas.pdf');
        Route::get('/surat-tugas/{id}/cetak', [SuratTugasController::class, 'cetak'])->name('surat-tugas.cetak');

        // Fase 11: Rekapitulasi Data & Ekspor
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/excel', [RekapController::class, 'exportExcel'])->name('rekap.excel');
        Route::get('/rekap/pdf', [RekapController::class, 'exportPdf'])->name('rekap.pdf');
    });

    // Route Petugas Lapangan
    Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/tugas', fn () => redirect()->route('dashboard'))->name('tugas.index');
    });

    // Route Pimpinan Eksekutif
    Route::middleware('role:pimpinan')->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/sla', fn () => redirect()->route('dashboard'))->name('sla.index');
        Route::get('/peta', fn () => redirect()->route('dashboard'))->name('peta.index');
        Route::get('/rekap', fn () => redirect()->route('dashboard'))->name('rekap.index');
    });
});

// Dynamic file server for uploads (ensures uploaded images work seamlessly in serverless/Vercel)
Route::get('/storage/{path}', [StorageFileController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.file');

require __DIR__.'/auth.php';