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
        Schema::table('hari_liburs', function (Blueprint $table) {
            $table->string('tipe_libur')->default('Seharian')->after('keterangan'); // 'Seharian', 'Sebagian Jam'
            $table->json('jam_ke')->nullable()->after('tipe_libur'); // ['Nadzoman', '1', '2', 'Ekstra']
            $table->foreignId('ruangan_id')->nullable()->after('jam_ke')->constrained('ruangans')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->after('ruangan_id')->constrained('levels')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hari_liburs', function (Blueprint $table) {
            $table->dropForeign(['ruangan_id']);
            $table->dropForeign(['level_id']);
            $table->dropColumn(['tipe_libur', 'jam_ke', 'ruangan_id', 'level_id']);
        });
    }
};
