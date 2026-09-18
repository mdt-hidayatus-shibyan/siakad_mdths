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
        Schema::create('catatan_ustadzs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ustadz_id')->constrained('ustadzs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tahun_pelajaran_id')->nullable()->constrained('tahun_pelajarans')->nullOnDelete();

            // Kategori catatan
            $table->string('kategori', 100); // Keluhan Murid, KBM & Perkembangan Akademik, Fasilitas Madrasah, Evaluasi & Saran, Lainnya
            $table->enum('target_tipe', ['murid', 'madrasah', 'umum'])->default('murid');

            // Target spesifik murid / ruangan (jika terkait murid)
            $table->foreignId('murid_id')->nullable()->constrained('murids')->nullOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();

            $table->string('judul', 200);
            $table->longText('isi_catatan');
            $table->enum('tingkat_urgensi', ['Rendah', 'Sedang', 'Tinggi', 'Penting / Mendesak'])->default('Sedang');

            // Lampiran bukti/foto opsional
            $table->string('lampiran_foto')->nullable();

            // Status pembacaan oleh admin
            $table->timestamp('dibaca_admin_pada')->nullable();

            $table->timestamps();

            // Index performa query
            $table->index(['ustadz_id', 'created_at']);
            $table->index(['kategori', 'target_tipe']);
            $table->index('murid_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_ustadzs');
    }
};
