<?php

use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\KecamatanController;
use App\Http\Controllers\Admin\KelurahanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Api\WilayahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Warga\LaporanController;
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
    Route::middleware('role:warga')->prefix('warga')->name('warga.')->group(function () {
        Route::get('/laporan-saya', [LaporanController::class, 'index'])->name('laporan.saya');
        Route::get('/lapor', [LaporanController::class, 'create'])->name('laporan.create');
        Route::post('/lapor', [LaporanController::class, 'store'])->name('laporan.store');
        Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
        Route::get('/laporan/{laporan}/edit', [LaporanController::class, 'edit'])->name('laporan.edit');
        Route::put('/laporan/{laporan}', [LaporanController::class, 'update'])->name('laporan.update');
        Route::delete('/laporan/{laporan}', [LaporanController::class, 'destroy'])->name('laporan.destroy');
    });

    // Route Admin DSDABM
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('kecamatan', KecamatanController::class)->except(['show']);
        Route::resource('kelurahan', KelurahanController::class)->except(['show']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('kategori', KategoriController::class)->except(['show']);
        // PBI-13: Daftar Aduan & Filter Laporan Masuk
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/disposisi', fn () => redirect()->route('dashboard'))->name('disposisi.index');
        Route::get('/rekap', fn () => redirect()->route('dashboard'))->name('rekap.index');
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

require __DIR__.'/auth.php';
