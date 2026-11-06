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
        Schema::create('umpan_balik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->unique()->constrained('laporan_kerusakan')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('Nilai rating kepuasan 1 sampai 5 bintang');
            $table->text('komentar')->nullable()->comment('Ulasan atau saran warga');
            $table->unsignedTinyInteger('kecepatan_penanganan')->nullable()->comment('Nilai kecepatan respon 1-5');
            $table->unsignedTinyInteger('kualitas_perbaikan')->nullable()->comment('Nilai kualitas fisik perbaikan 1-5');
            $table->boolean('is_tampil_publik')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umpan_balik');
    }
};
