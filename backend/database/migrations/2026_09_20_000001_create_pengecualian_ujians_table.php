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
        Schema::create('pengecualian_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->foreignId('murid_id')->constrained('murids')->onDelete('cascade');
            $table->string('alasan')->nullable(); // Misal: Sakit, Cuti, Izin Khusus, dll.
            $table->foreignId('ditandai_oleh')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['ujian_id', 'murid_id'], 'unique_pengecualian_ujian_murid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengecualian_ujians');
    }
};
