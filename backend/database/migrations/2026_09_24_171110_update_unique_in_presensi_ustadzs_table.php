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
        Schema::table('presensi_ustadzs', function (Blueprint $table) {
            $table->dropUnique(['tanggal', 'jadwal_pelajaran_id']);
            $table->unique(['tanggal', 'jadwal_pelajaran_id', 'ustadz_id'], 'presensi_ustadzs_tgl_jadwal_ustadz_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_ustadzs', function (Blueprint $table) {
            $table->dropUnique('presensi_ustadzs_tgl_jadwal_ustadz_unique');
            $table->unique(['tanggal', 'jadwal_pelajaran_id']);
        });
    }
};
