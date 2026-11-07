<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_kerusakan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 30)->unique();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelurahan_id')->constrained('kelurahan')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_infrastruktur')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->text('deskripsi_kondisi');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('foto_bukti_sebelum');
            $table->enum('status', ['menunggu_verifikasi', 'ditolak', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu_verifikasi');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->text('alasan_penolakan')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            $table->timestamps();

            // Indexes sesuai spesifikasi
            $table->index('status');
            $table->index('kelurahan_id');
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan_kerusakan')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['menunggu_verifikasi', 'ditolak', 'diproses', 'selesai', 'dibatalkan']);
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
        Schema::dropIfExists('laporan_kerusakan');
    }
};
