<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel tagihan_wali_murids untuk mendukung status 'Cicilan' dan 'total_terbayar'
        if (Schema::hasTable('tagihan_wali_murids')) {
            // Ubah tipe ENUM status_bayar agar memuat 'Cicilan'
            DB::statement("ALTER TABLE `tagihan_wali_murids` MODIFY COLUMN `status_bayar` ENUM('Belum Lunas', 'Cicilan', 'Lunas', 'Bebas/Gratis', 'Ditanggung Donatur') NOT NULL DEFAULT 'Belum Lunas'");

            Schema::table('tagihan_wali_murids', function (Blueprint $table) {
                if (!Schema::hasColumn('tagihan_wali_murids', 'total_terbayar')) {
                    $table->bigInteger('total_terbayar')->default(0)->after('nominal_tagihan');
                }
            });

            // Set total_terbayar = nominal_tagihan untuk tagihan yang sudah Lunas
            DB::table('tagihan_wali_murids')
                ->where('status_bayar', 'Lunas')
                ->where('total_terbayar', 0)
                ->update(['total_terbayar' => DB::raw('nominal_tagihan')]);
        }

        // 2. Buat tabel pembayaran_tagihan_ikhtibars untuk mencatat transaksi detail (Tunai, Potong Tabungan Manual, dan Cicilan Maks 3x)
        if (!Schema::hasTable('pembayaran_tagihan_ikhtibars')) {
            Schema::create('pembayaran_tagihan_ikhtibars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tagihan_wali_murid_id')->constrained('tagihan_wali_murids')->cascadeOnDelete();
                $table->foreignId('kegiatan_ikhtibar_id')->nullable()->constrained('kegiatan_ikhtibars')->nullOnDelete();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('wali_murid_id')->constrained('wali_murids')->cascadeOnDelete();
                
                $table->string('no_transaksi')->unique();
                $table->enum('metode_pembayaran', ['Tunai', 'Potong Tabungan', 'Transfer', 'Lainnya'])->default('Tunai');
                $table->enum('tipe_pembayaran', ['Lunas', 'Cicilan'])->default('Lunas');
                $table->tinyInteger('termin_ke')->default(1)->comment('Termin ke-1, ke-2, atau ke-3 (Maks 3x)');
                
                $table->bigInteger('nominal_tagihan');
                $table->bigInteger('nominal_bayar');
                $table->bigInteger('sisa_tagihan')->default(0);
                $table->date('tanggal_bayar');
                
                // Kolom Spesifik Potong Tabungan Manual
                $table->string('nomor_tabungan')->nullable()->comment('No rekening / no buku tabungan murid');
                $table->string('nama_nasabah')->nullable()->comment('Nama murid nasabah tabungan');
                $table->string('ruangan_nasabah')->nullable()->comment('Kelas / ruangan murid nasabah');
                $table->boolean('is_verified')->default(true)->comment('Status konfirmasi verifikasi admin tabungan');
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();

                // Sinkronisasi dan Audit
                $table->foreignId('pembayaran_tagihan_id')->nullable()->constrained('pembayaran_tagihans')->nullOnDelete();
                $table->foreignId('diterima_oleh_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nama_penyetor')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['tagihan_wali_murid_id', 'termin_ke'], 'idx_bayar_ikhtibar_tagihan_termin');
                $table->index(['wali_murid_id', 'tahun_pelajaran_id'], 'idx_bayar_ikhtibar_wali_tahun');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tagihan_ikhtibars');

        if (Schema::hasTable('tagihan_wali_murids')) {
            if (Schema::hasColumn('tagihan_wali_murids', 'total_terbayar')) {
                Schema::table('tagihan_wali_murids', function (Blueprint $table) {
                    $table->dropColumn('total_terbayar');
                });
            }
            DB::statement("ALTER TABLE `tagihan_wali_murids` MODIFY COLUMN `status_bayar` ENUM('Belum Lunas', 'Lunas', 'Bebas/Gratis', 'Ditanggung Donatur') NOT NULL DEFAULT 'Belum Lunas'");
        }
    }
};

