<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('disposisi_penugasan', function (Blueprint $table) {
            $table->string('status_penugasan', 50)->default('ditugaskan')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('disposisi_penugasan')
            ->whereNotIn('status_penugasan', ['ditugaskan', 'sedang_dikerjakan', 'selesai'])
            ->update(['status_penugasan' => 'ditugaskan']);

        Schema::table('disposisi_penugasan', function (Blueprint $table) {
            $table->enum('status_penugasan', ['ditugaskan', 'sedang_dikerjakan', 'selesai'])->default('ditugaskan')->change();
        });
    }
};
