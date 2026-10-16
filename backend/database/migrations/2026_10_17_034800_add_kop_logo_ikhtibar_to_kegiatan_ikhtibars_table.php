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
        Schema::table('kegiatan_ikhtibars', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatan_ikhtibars', 'kop_logo_ikhtibar')) {
                $table->string('kop_logo_ikhtibar')->nullable()->after('logo_kegiatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_ikhtibars', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan_ikhtibars', 'kop_logo_ikhtibar')) {
                $table->dropColumn('kop_logo_ikhtibar');
            }
        });
    }
};

