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
        Schema::create('disposisi_penugasan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat_tugas', 40)->unique();
            $table->foreignId('laporan_id')->unique()->constrained('laporan_kerusakan')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_disposisi');
            $table->date('tenggat_waktu');
            $table->text('catatan_instruksi');
            $table->enum('status_penugasan', ['ditugaskan', 'sedang_dikerjakan', 'selesai'])->default('ditugaskan');
            $table->timestamps();

            $table->index('petugas_id');
            $table->index('status_penugasan');
            $table->index('tanggal_disposisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi_penugasan');
    }
};
