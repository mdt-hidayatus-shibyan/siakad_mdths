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
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->unsignedInteger('nomor_agenda')->nullable()->index();
            $table->string('jenis_surat', 60)->index(); // surat_panggilan, surat_peringatan, surat_pemberitahuan, surat_edaran, surat_permohonan_izin, surat_dispensasi, surat_undangan
            $table->string('perihal');
            $table->string('lampiran')->default('-');
            $table->string('sifat_surat', 30)->default('Biasa'); // Biasa, Penting, Segera, Rahasia
            $table->string('tujuan_surat');
            $table->string('alamat_tujuan')->nullable();
            $table->string('kategori_penerima', 30)->default('umum'); // murid, wali_murid, ustadz, lembaga_luar, umum

            $table->foreignId('murid_id')->nullable()->constrained('murids')->nullOnDelete();
            $table->foreignId('ustadz_id')->nullable()->constrained('ustadzs')->nullOnDelete();
            $table->foreignId('wali_murid_id')->nullable()->constrained('wali_murids')->nullOnDelete();
            $table->foreignId('tahun_pelajaran_id')->nullable()->constrained('tahun_pelajarans')->nullOnDelete();

            $table->date('tanggal_surat');
            $table->string('tanggal_hijriyah', 100)->nullable();
            $table->string('tempat_terbit', 100)->default('Bangkalan');

            $table->json('isi_spesifik')->nullable();
            $table->longText('isi_surat')->nullable();
            $table->text('tembusan')->nullable();

            $table->string('penandatangan_nama');
            $table->string('penandatangan_jabatan');
            $table->string('penandatangan_nip')->nullable();

            $table->string('status', 20)->default('terbit'); // draft, terbit, arsip
            $table->string('qr_token', 64)->unique();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};
