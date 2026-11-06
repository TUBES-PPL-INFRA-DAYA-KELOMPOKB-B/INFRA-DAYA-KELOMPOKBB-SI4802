<?php

use App\Http\Controllers\Admin\DisposisiController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\KecamatanController;
use App\Http\Controllers\Admin\KelurahanController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Api\WilayahController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Petugas\TugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Publik\PetaPublikController;
use App\Http\Controllers\Publik\TrackingController;
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

Route::get('/peta-publik', [PetaPublikController::class, 'index'])->name('peta.publik');
Route::get('/api/peta-publik/markers', [PetaPublikController::class, 'markers'])->name('api.peta.markers');

Route::get('/lacak-tiket/{nomor_tiket?}', [TrackingController::class, 'index'])->name('tracking.publik');

// Dashboard Utama Terotentikasi
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// API Endpoint Publik / Web
Route::get('/api/wilayah/kecamatan/{id}/kelurahan', [WilayahController::class, 'kelurahanByKecamatan'])->name('api.wilayah.kelurahan');

// Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Warga
    Route::middleware('role:warga')->prefix('warga')->name('warga.')->group(function () {
        Route::get('/laporan-saya', [LaporanController::class, 'index'])->name('laporan.saya');
        Route::get('/lapor', [LaporanController::class, 'create'])->name('laporan.create');
        Route::post('/lapor', [LaporanController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('laporan.store');
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
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('kategori', KategoriController::class)->except(['show']);
        // Fase 5: Verifikasi & Penilaian Prioritas
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{id}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{id}/setujui', [VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
        Route::post('/verifikasi/{id}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');
        // Fase 6: Disposisi Penugasan & Surat Tugas PDF
        Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi.index');
        Route::get('/disposisi/create', [DisposisiController::class, 'create'])->name('disposisi.create');
        Route::post('/disposisi', [DisposisiController::class, 'store'])->name('disposisi.store');
        Route::get('/disposisi/{id}', [DisposisiController::class, 'show'])->name('disposisi.show');
        Route::get('/disposisi/{id}/pdf', [DisposisiController::class, 'cetakPdf'])->name('disposisi.pdf');
        // Fase 11: Rekapitulasi Data & Ekspor
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/excel', [RekapController::class, 'exportExcel'])->name('rekap.excel');
        Route::get('/rekap/pdf', [RekapController::class, 'exportPdf'])->name('rekap.pdf');
    });

    // Route Petugas Lapangan
    Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/tugas', [TugasController::class, 'index'])->name('tugas.index');
        Route::get('/tugas/{id}', [TugasController::class, 'show'])->name('tugas.show');
        Route::post('/tugas/{id}/mulai', [TugasController::class, 'mulai'])->name('tugas.mulai');
        Route::post('/tugas/{id}/selesai', [TugasController::class, 'selesai'])->name('tugas.selesai');
    });

    // Route Pimpinan Eksekutif
    Route::middleware('role:pimpinan,admin')->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/sla', [DashboardController::class, 'index'])->name('sla.index');
        Route::get('/peta', [PetaPublikController::class, 'index'])->name('peta.index');
        // Fase 11: Rekapitulasi Eksekutif
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/excel', [RekapController::class, 'exportExcel'])->name('rekap.excel');
        Route::get('/rekap/pdf', [RekapController::class, 'exportPdf'])->name('rekap.pdf');
    });
});

// Dynamic file server for uploads (ensures uploaded images work seamlessly in serverless/Vercel)
Route::get('/storage/{path}', [App\Http\Controllers\StorageFileController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.file');

require __DIR__.'/auth.php';
