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
        if (!Schema::hasTable('tagihan_wali_murids')) {
            Schema::create('tagihan_wali_murids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wali_murid_id')->constrained('wali_murids')->cascadeOnDelete();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('pengaturan_tagihan_id')->constrained('pengaturan_tagihans')->cascadeOnDelete();
                $table->string('nama_tagihan_spesifik');
                $table->bigInteger('nominal_tagihan');
                $table->enum('status_bayar', ['Belum Lunas', 'Lunas', 'Bebas/Gratis', 'Ditanggung Donatur'])->default('Belum Lunas');
                $table->foreignId('pembayaran_tagihan_id')->nullable()->constrained('pembayaran_tagihans')->nullOnDelete();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                // Performance Indexes
                $table->index(['wali_murid_id', 'status_bayar'], 'idx_tagihan_wali_status');
                $table->index(['tahun_pelajaran_id', 'status_bayar'], 'idx_tagihan_wali_tahun_status');
                $table->index(['pengaturan_tagihan_id', 'status_bayar'], 'idx_tagihan_wali_pengaturan_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_wali_murids');
    }
};
