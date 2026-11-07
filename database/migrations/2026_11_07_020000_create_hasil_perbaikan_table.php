<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai PRD Bagian 6: Tabel hasil_perbaikan (1:1 dengan disposisi_penugasan).
     */
    public function up(): void
    {
        Schema::create('hasil_perbaikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_id')->unique()->constrained('disposisi_penugasan')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->string('foto_bukti_sesudah', 255)->nullable();
            $table->text('catatan_perbaikan')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->index('petugas_id');
            $table->index('disposisi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_perbaikan');
    }
};

