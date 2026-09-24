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
        if (!Schema::hasTable('jadwal_pelajaran_ustadz')) {
            Schema::create('jadwal_pelajaran_ustadz', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajarans')->onDelete('cascade');
                $table->foreignId('ustadz_id')->constrained('ustadzs')->onDelete('cascade');
                $table->boolean('is_utama')->default(true);
                $table->unsignedTinyInteger('urutan')->default(1);
                $table->string('peran')->default('Pengampu')->comment('Pengampu, Pendamping, dsb');
                $table->timestamps();

                $table->unique(['jadwal_pelajaran_id', 'ustadz_id'], 'jadwal_ustadz_unique');
            });

            // Migrasi data lama: Isi data pivot dari jadwal_pelajarans.ustadz_id yang sudah ada
            $existingJadwals = DB::table('jadwal_pelajarans')
                ->whereNotNull('ustadz_id')
                ->get();

            $now = now();
            $insertData = [];
            foreach ($existingJadwals as $j) {
                $insertData[] = [
                    'jadwal_pelajaran_id' => $j->id,
                    'ustadz_id' => $j->ustadz_id,
                    'is_utama' => true,
                    'urutan' => 1,
                    'peran' => 'Pengampu',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($insertData)) {
                // Chunk insert untuk keamanan performa
                foreach (array_chunk($insertData, 100) as $chunk) {
                    DB::table('jadwal_pelajaran_ustadz')->insertOrIgnore($chunk);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran_ustadz');
    }
};
