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
            $table->index(['status', 'created_at'], 'laporan_status_created_idx');
            $table->index(['kategori_id', 'status'], 'laporan_kategori_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('laporan_kerusakan', function (Blueprint $table) {
                $table->dropIndex('laporan_status_created_idx');
            });
        } catch (Throwable $e) {
        }

        try {
            Schema::table('laporan_kerusakan', function (Blueprint $table) {
                $table->dropIndex('laporan_kategori_status_idx');
            });
        } catch (Throwable $e) {
        }
    }
};
