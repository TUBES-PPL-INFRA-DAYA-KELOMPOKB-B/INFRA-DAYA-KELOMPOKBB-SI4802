<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PBI-11: Menambahkan kolom tingkat_keparahan_default pada kategori_infrastruktur
     * dan memastikan kolom prioritas pada laporan_kerusakan mendukung nilai 'kritis'.
     */
    public function up(): void
    {
        Schema::table('kategori_infrastruktur', function (Blueprint $table) {
            $table->enum('tingkat_keparahan_default', ['rendah', 'sedang', 'tinggi', 'kritis'])
                ->default('sedang')
                ->after('deskripsi');
        });

        // Sinkronisasi data awal kategori berdasarkan bobot_prioritas yang ada
        DB::table('kategori_infrastruktur')->where('bobot_prioritas', 1)->update(['tingkat_keparahan_default' => 'rendah']);
        DB::table('kategori_infrastruktur')->where('bobot_prioritas', 2)->update(['tingkat_keparahan_default' => 'sedang']);
        DB::table('kategori_infrastruktur')->where('bobot_prioritas', 3)->update(['tingkat_keparahan_default' => 'tinggi']);
        DB::table('kategori_infrastruktur')->where('bobot_prioritas', '>=', 4)->update(['tingkat_keparahan_default' => 'kritis']);

        // Pastikan kolom prioritas pada laporan_kerusakan dapat menampung 'kritis' (KF-11.4)
        if (Schema::hasTable('laporan_kerusakan')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE laporan_kerusakan MODIFY COLUMN prioritas ENUM('rendah', 'sedang', 'tinggi', 'kritis') NOT NULL DEFAULT 'sedang'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('laporan_kerusakan')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE laporan_kerusakan MODIFY COLUMN prioritas ENUM('rendah', 'sedang', 'tinggi') NOT NULL DEFAULT 'sedang'");
            }
        }

        Schema::table('kategori_infrastruktur', function (Blueprint $table) {
            $table->dropColumn('tingkat_keparahan_default');
        });
    }
};

