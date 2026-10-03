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
        // 1. Tabel Master Susunan Kepanitiaan IMNI
        if (!Schema::hasTable('panitia_imnis')) {
            Schema::create('panitia_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('ustadz_id')->constrained('ustadzs')->cascadeOnDelete();
                $table->enum('jabatan', ['Ketua', 'Bendahara', 'Anggota'])->default('Anggota');
                $table->string('no_sk')->nullable();
                $table->string('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['tahun_pelajaran_id', 'ustadz_id'], 'panitia_imni_tapel_ustadz_unique');
                $table->index(['tahun_pelajaran_id', 'jabatan'], 'panitia_imni_tapel_jabatan_index');
            });
        }

        // 2. Tabel Master Peserta Ujian IMNI (Khusus Kelas Akhir: 3 TPQ, 6 IBT, 3 TSA)
        if (!Schema::hasTable('peserta_imnis')) {
            Schema::create('peserta_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
                $table->foreignId('tingkat_id')->nullable()->constrained('tingkats')->nullOnDelete();
                $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
                $table->foreignId('ruangan_asal_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->foreignId('ruangan_ujian_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->string('nomor_peserta')->nullable();
                $table->integer('nomor_meja')->nullable();
                $table->string('status_kelayakan')->default('Layak');
                $table->boolean('is_active')->default(true);
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['tahun_pelajaran_id', 'murid_id'], 'uq_peserta_imni_tahun_murid');
                $table->index(['tahun_pelajaran_id', 'tingkat_id', 'ruangan_ujian_id'], 'idx_peserta_imni_lookup');
            });
        }

        // 3. Tabel Pembayaran / Tagihan Administrasi IMNI Murid
        if (!Schema::hasTable('pembayaran_imnis')) {
            Schema::create('pembayaran_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('peserta_imni_id')->constrained('peserta_imnis')->cascadeOnDelete();
                $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
                $table->string('no_kwitansi')->nullable()->unique();
                $table->decimal('nominal_tagihan', 12, 2)->default(0);
                $table->decimal('nominal_bayar', 12, 2)->default(0);
                $table->decimal('sisa_tagihan', 12, 2)->default(0);
                $table->date('tanggal_bayar')->nullable();
                $table->string('metode_pembayaran')->default('Tunai');
                $table->string('status_pembayaran')->default('Belum Lunas');
                $table->foreignId('diterima_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nama_penyetor')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->unique(['tahun_pelajaran_id', 'peserta_imni_id'], 'uq_pembayaran_imni_peserta');
                $table->index(['tahun_pelajaran_id', 'status_pembayaran'], 'idx_pembayaran_imni_status');
            });
        }

        // 4. Tabel Pengeluaran Kas Operasional Kepanitiaan IMNI
        if (!Schema::hasTable('pengeluaran_imnis')) {
            Schema::create('pengeluaran_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->string('kode_transaksi')->unique();
                $table->string('kategori');
                $table->string('judul_pengeluaran');
                $table->decimal('nominal', 12, 2);
                $table->date('tanggal_pengeluaran');
                $table->string('penerima_dana')->nullable();
                $table->string('metode_pembayaran')->default('Tunai');
                $table->string('bukti_nota')->nullable();
                $table->text('keterangan')->nullable();
                $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tahun_pelajaran_id', 'kategori'], 'idx_pengeluaran_imni_kategori');
                $table->index(['tahun_pelajaran_id', 'tanggal_pengeluaran'], 'idx_pengeluaran_imni_tanggal');
            });
        }

        // 5. Tabel Master Ruangan Ujian IMNI
        if (!Schema::hasTable('ruangan_imnis')) {
            Schema::create('ruangan_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->string('nama_ruangan_imni')->nullable();
                $table->string('nama_ruangan');
                $table->string('kode_ruangan')->nullable();
                $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->foreignId('penanggung_jawab_ruangan_id')->nullable()->constrained('panitia_imnis')->nullOnDelete();
                $table->integer('kapasitas')->default(30);
                $table->integer('urutan')->default(1);
                $table->text('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tahun_pelajaran_id', 'is_active'], 'idx_ruangan_imni_tahun');
            });
        }

        // 6. Tabel Pivot Peserta ke Ruangan IMNI (Plotting Peserta)
        if (!Schema::hasTable('peserta_ruangan_imnis')) {
            Schema::create('peserta_ruangan_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('ruangan_imni_id')->constrained('ruangan_imnis')->cascadeOnDelete();
                $table->foreignId('peserta_imni_id')->constrained('peserta_imnis')->cascadeOnDelete();
                $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
                $table->date('tanggal_ujian')->nullable();
                $table->integer('nomor_meja')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['tahun_pelajaran_id', 'tanggal_ujian', 'ruangan_imni_id'], 'idx_peserta_ruangan_imni_lookup');
                $table->unique(['tahun_pelajaran_id', 'peserta_imni_id', 'tanggal_ujian'], 'uq_peserta_ruangan_imni_tgl');
            });
        }

        // 7. Tabel Pengawas Ruangan Ujian IMNI
        if (!Schema::hasTable('pengawas_ruangan_imnis')) {
            Schema::create('pengawas_ruangan_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->date('tanggal_ujian');
                $table->foreignId('ruangan_imni_id')->constrained('ruangan_imnis')->cascadeOnDelete();
                $table->foreignId('ustadz_id')->nullable()->constrained('ustadzs')->nullOnDelete();
                $table->string('nama_pengawas')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['tahun_pelajaran_id', 'tanggal_ujian', 'ruangan_imni_id'], 'uq_pengawas_ruangan_tgl');
            });
        }

        // 8. Tabel Putusan Kelulusan / Yudisium Akhir IMNI
        if (!Schema::hasTable('kelulusan_imnis')) {
            Schema::create('kelulusan_imnis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('peserta_imni_id')->nullable()->constrained('peserta_imnis')->nullOnDelete();
                $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
                $table->foreignId('tingkat_id')->nullable()->constrained('tingkats')->nullOnDelete();
                $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();

                // Nilai & Algoritma Kalkulasi
                $table->decimal('rata_nilai_teori', 5, 2)->default(0.00);
                $table->decimal('rata_imda_1', 5, 2)->nullable();
                $table->decimal('nilai_hadir_1', 5, 2)->nullable();
                $table->decimal('nilai_pelanggaran_1', 5, 2)->nullable();
                $table->decimal('skor_sem1', 5, 2)->nullable();

                $table->decimal('rata_imni_2', 5, 2)->nullable();
                $table->decimal('nilai_hadir_2', 5, 2)->nullable();
                $table->decimal('nilai_pelanggaran_2', 5, 2)->nullable();
                $table->decimal('skor_sem2', 5, 2)->nullable();

                $table->decimal('nilai_akhir', 5, 2)->nullable();
                $table->json('detail_kalkulasi')->nullable();

                $table->string('status_alquran', 50)->default('Belum Diuji');
                $table->decimal('nilai_alquran', 5, 2)->nullable();
                $table->string('status_kelulusan', 50)->default('Ditunda');

                $table->string('nomor_ijazah', 100)->nullable();
                $table->string('nomor_sk_lulus', 100)->nullable();
                $table->text('catatan_yudisium')->nullable();

                $table->boolean('is_locked')->default(false);
                $table->timestamp('locked_at')->nullable();
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();

                $table->unique(['tahun_pelajaran_id', 'murid_id'], 'uq_kelulusan_imni_tahun_murid');
                $table->index(['tahun_pelajaran_id', 'status_kelulusan'], 'idx_kelulusan_imni_status');
            });
        }

        // 9. Tambahan Kolom Ruangan IMNI ke Tabel Presensi Ujian
        if (Schema::hasTable('presensi_ujians')) {
            Schema::table('presensi_ujians', function (Blueprint $table) {
                if (!Schema::hasColumn('presensi_ujians', 'ruangan_imni_id')) {
                    $table->foreignId('ruangan_imni_id')->nullable()->after('ruangan_id')->constrained('ruangan_imnis')->nullOnDelete();
                }
                if (!Schema::hasColumn('presensi_ujians', 'tanggal_ujian')) {
                    $table->date('tanggal_ujian')->nullable()->after('ruangan_imni_id');
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('presensi_ujians')) {
            Schema::table('presensi_ujians', function (Blueprint $table) {
                if (Schema::hasColumn('presensi_ujians', 'ruangan_imni_id')) {
                    $table->dropForeign(['ruangan_imni_id']);
                    $table->dropColumn(['ruangan_imni_id', 'tanggal_ujian']);
                }
            });
        }

        Schema::dropIfExists('kelulusan_imnis');
        Schema::dropIfExists('pengawas_ruangan_imnis');
        Schema::dropIfExists('peserta_ruangan_imnis');
        Schema::dropIfExists('ruangan_imnis');
        Schema::dropIfExists('pengeluaran_imnis');
        Schema::dropIfExists('pembayaran_imnis');
        Schema::dropIfExists('peserta_imnis');
        Schema::dropIfExists('panitia_imnis');
    }
};
