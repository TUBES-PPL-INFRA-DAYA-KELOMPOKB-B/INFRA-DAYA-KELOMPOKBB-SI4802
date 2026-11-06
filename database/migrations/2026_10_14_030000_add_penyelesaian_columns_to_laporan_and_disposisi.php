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
        Schema::table('laporan_kerusakan', function (Blueprint $table) {
            $table->string('foto_bukti_sesudah')->nullable()->after('foto_bukti_sebelum');
            $table->text('catatan_perbaikan')->nullable()->after('alasan_penolakan');
            $table->timestamp('selesai_at')->nullable()->after('catatan_perbaikan');
        });

        Schema::table('disposisi_penugasan', function (Blueprint $table) {
            $table->string('foto_bukti_sesudah')->nullable()->after('catatan_instruksi');
            $table->text('catatan_perbaikan')->nullable()->after('foto_bukti_sesudah');
            $table->date('tanggal_selesai')->nullable()->after('catatan_perbaikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposisi_penugasan', function (Blueprint $table) {
            $table->dropColumn(['foto_bukti_sesudah', 'catatan_perbaikan', 'tanggal_selesai']);
        });

        Schema::table('laporan_kerusakan', function (Blueprint $table) {
            $table->dropColumn(['foto_bukti_sesudah', 'catatan_perbaikan', 'selesai_at']);
        });
    }
};
