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
        // 1. Tabel Utama Event / Pelaksanaan Ujian Al-Qur'an
        Schema::create('ujian_alqurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
            $table->string('nama_ujian');
            $table->date('tanggal_ujian')->nullable();
            $table->decimal('kkm_kelulusan', 5, 2)->default(55.00);
            $table->decimal('bobot_jali', 5, 2)->default(5.00);
            $table->decimal('bobot_khofi', 5, 2)->default(3.00);
            $table->text('keterangan')->nullable();
            $table->string('status')->default('Berjalan'); // Draft, Berjalan, Selesai
            $table->timestamps();
        });

        // 2. Tabel Dewan Juri / Penguji Ujian Al-Qur'an
        Schema::create('juri_ujian_alqurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_alquran_id')->constrained('ujian_alqurans')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('ustadzs')->cascadeOnDelete();
            $table->string('peran_juri')->default('juri_jali'); // juri_jali, juri_khofi, juri_utama, anggota
            $table->integer('urutan')->default(1);
            $table->timestamps();
        });

        // 3. Tabel Murid Peserta Ujian Al-Qur'an
        Schema::create('peserta_ujian_alqurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_alquran_id')->constrained('ujian_alqurans')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
            $table->string('nomor_peserta')->nullable();
            $table->string('surat_makra')->nullable();
            $table->integer('jumlah_khoto_jali')->default(0);
            $table->integer('jumlah_khoto_khofi')->default(0);
            $table->decimal('poin_pengurangan_jali', 6, 2)->default(0.00);
            $table->decimal('poin_pengurangan_khofi', 6, 2)->default(0.00);
            $table->decimal('total_pengurangan', 6, 2)->default(0.00);
            $table->decimal('nilai_akhir', 6, 2)->default(100.00);
            $table->string('status_kelulusan')->default('Belum Diuji'); // Belum Diuji, Lulus, Tidak Lulus
            $table->text('catatan_juri')->nullable();
            $table->string('no_ijazah')->nullable();
            $table->string('no_sk')->nullable();
            $table->date('tanggal_lulus')->nullable();
            $table->foreignId('diuji_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Unique constraint agar murid tidak terdaftar ganda di 1 event ujian
            $table->unique(['ujian_alquran_id', 'murid_id'], 'uq_peserta_ujian_murid');
        });

        // 4. Tabel Detail Penilaian Realtime Per Juri
        Schema::create('penilaian_juri_alqurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('peserta_ujian_alqurans')->cascadeOnDelete();
            $table->foreignId('juri_id')->nullable()->constrained('juri_ujian_alqurans')->nullOnDelete();
            $table->foreignId('ustadz_id')->nullable()->constrained('ustadzs')->nullOnDelete();
            $table->string('kategori')->default('khoto_jali'); // khoto_jali, khoto_khofi, umum
            $table->integer('jumlah_kesalahan')->default(0);
            $table->decimal('poin_pengurangan', 6, 2)->default(0.00);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_juri_alqurans');
        Schema::dropIfExists('peserta_ujian_alqurans');
        Schema::dropIfExists('juri_ujian_alqurans');
        Schema::dropIfExists('ujian_alqurans');
    }
};
