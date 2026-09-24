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
        Schema::table('jadwal_pelajaran_ustadz', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_pelajaran_ustadz', 'is_utama')) {
                $table->boolean('is_utama')->default(true)->after('ustadz_id');
            }
            if (!Schema::hasColumn('jadwal_pelajaran_ustadz', 'urutan')) {
                $table->unsignedTinyInteger('urutan')->default(1)->after('is_utama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pelajaran_ustadz', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_pelajaran_ustadz', 'urutan')) {
                $table->dropColumn('urutan');
            }
            if (Schema::hasColumn('jadwal_pelajaran_ustadz', 'is_utama')) {
                $table->dropColumn('is_utama');
            }
        });
    }
};
