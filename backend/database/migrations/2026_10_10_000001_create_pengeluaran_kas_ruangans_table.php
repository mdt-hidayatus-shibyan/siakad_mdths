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
        Schema::create('pengeluaran_kas_ruangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruangan_id')->constrained('ruangans')->cascadeOnDelete();
            $table->string('judul');
            $table->string('kategori')->nullable()->default('Operasional');
            $table->integer('nominal');
            $table->date('tanggal_pengeluaran');
            $table->text('keterangan')->nullable();
            $table->string('bukti_nota')->nullable();
            $table->foreignId('diinput_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['ruangan_id', 'tanggal_pengeluaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_kas_ruangans');
    }
};

