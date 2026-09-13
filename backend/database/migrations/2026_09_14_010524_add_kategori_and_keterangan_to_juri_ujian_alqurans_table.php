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
        Schema::table('juri_ujian_alqurans', function (Blueprint $table) {
            $table->string('kategori_juri')->nullable()->after('peran_juri'); // Khotho Jali, Khotho Khofi
            $table->string('keterangan')->nullable()->after('kategori_juri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('juri_ujian_alqurans', function (Blueprint $table) {
            $table->dropColumn(['kategori_juri', 'keterangan']);
        });
    }
};
