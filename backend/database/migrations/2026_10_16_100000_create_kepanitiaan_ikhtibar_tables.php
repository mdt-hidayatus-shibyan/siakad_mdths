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
        // 1. Tabel Master Kegiatan & Identitas Ikhtibar Tahunan
        if (!Schema::hasTable('kegiatan_ikhtibars')) {
            Schema::create('kegiatan_ikhtibars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_pelajaran_id')->unique()->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->foreignId('pengaturan_tagihan_id')->nullable()->constrained('pengaturan_tagihans')->nullOnDelete();

                // Identitas Ikhtibar
                $table->string('logo_kegiatan')->nullable();
                $table->string('nama_kegiatan')->default('Haflah Ikhtibar');
                $table->string('ikhtibar_ke')->nullable(); // Contoh: "52 Th", "53 Th"
                $table->string('tema_kegiatan')->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->date('tanggal_puncak_acara')->nullable();
                $table->string('tanggal_hijriyah')->nullable();
                $table->string('lokasi_kegiatan')->nullable();
                $table->string('stempel_kegiatan')->nullable();

                // SK & Status
                $table->string('no_sk_panitia')->nullable();
                $table->date('tanggal_penetapan_sk')->nullable();
                $table->enum('status_kegiatan', ['Persiapan', 'Berjalan', 'Selesai', 'Dikunci'])->default('Persiapan');
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['tahun_pelajaran_id', 'status_kegiatan'], 'idx_kegiatan_ikhtibar_tapel_status');
            });
        }

        // 2. Tabel Divisi Ikhtibar & Alokasi Pagu Anggaran
        if (!Schema::hasTable('divisi_ikhtibars')) {
            Schema::create('divisi_ikhtibars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kegiatan_ikhtibar_id')->constrained('kegiatan_ikhtibars')->cascadeOnDelete();
                $table->string('kode_divisi', 20);
                $table->string('nama_divisi');
                $table->text('deskripsi_tugas')->nullable();
                $table->decimal('nominal_pagu', 15, 2)->default(0);
                $table->enum('status_pagu', ['Draft', 'Disetujui', 'Dikunci'])->default('Draft');
                $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->integer('urutan')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['kegiatan_ikhtibar_id', 'urutan'], 'idx_divisi_ikhtibar_urutan');
            });
        }

        // 3. Tabel Susunan Kepanitiaan Ikhtibar (Ustadz & Non-Ustadz)
        if (!Schema::hasTable('panitia_ikhtibars')) {
            Schema::create('panitia_ikhtibars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kegiatan_ikhtibar_id')->constrained('kegiatan_ikhtibars')->cascadeOnDelete();
                $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
                $table->enum('kelompok_jabatan', ['BPH', 'Divisi'])->default('Divisi');
                $table->string('jabatan');
                $table->foreignId('divisi_ikhtibar_id')->nullable()->constrained('divisi_ikhtibars')->nullOnDelete();

                // Unsur Personel
                $table->enum('tipe_personel', ['ustadz', 'eksternal'])->default('ustadz');
                $table->foreignId('ustadz_id')->nullable()->constrained('ustadzs')->nullOnDelete();

                // Identitas Mandiri Personel Eksternal
                $table->text('nik')->nullable();
                $table->string('nama_lengkap')->nullable();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->string('no_hp')->nullable();
                $table->string('unsur_asal')->nullable();
                $table->text('alamat')->nullable();
                $table->string('foto')->nullable();
                $table->string('tanda_tangan')->nullable();

                $table->boolean('is_active')->default(true);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['kegiatan_ikhtibar_id', 'kelompok_jabatan'], 'idx_panitia_ikhtibar_kelompok');
                $table->index(['tahun_pelajaran_id', 'is_active'], 'idx_panitia_ikhtibar_status');
            });
        }

        // 4. Tabel Penyaluran Dana & Penarikan Sisa Pagu Divisi
        if (!Schema::hasTable('penyaluran_dana_ikhtibars')) {
            Schema::create('penyaluran_dana_ikhtibars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kegiatan_ikhtibar_id')->constrained('kegiatan_ikhtibars')->cascadeOnDelete();
                $table->foreignId('divisi_ikhtibar_id')->constrained('divisi_ikhtibars')->cascadeOnDelete();
                $table->string('kode_penyaluran')->unique();
                $table->enum('jenis_transaksi', ['Penyaluran Pagu', 'Penarikan Sisa Kas', 'Penyesuaian Pagu'])->default('Penyaluran Pagu');
                $table->enum('kategori_penarikan', ['Sisa Kas Fisik', 'Tarik Sisa Alokasi', 'Realokasi Antar Divisi'])->nullable();
                $table->foreignId('divisi_tujuan_realokasi_id')->nullable()->constrained('divisi_ikhtibars')->nullOnDelete();
                $table->integer('termin_ke')->default(1);
                $table->decimal('nominal', 15, 2)->default(0);
                $table->date('tanggal_penyaluran');
                $table->string('metode_penyaluran')->default('Tunai');
                $table->foreignId('penerima_panitia_id')->nullable()->constrained('panitia_ikhtibars')->nullOnDelete();
                $table->foreignId('disalurkan_oleh_id')->nullable()->constrained('panitia_ikhtibars')->nullOnDelete();
                $table->foreignId('disetujui_oleh_id')->nullable()->constrained('panitia_ikhtibars')->nullOnDelete();
                $table->string('bukti_serah_terima')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['kegiatan_ikhtibar_id', 'divisi_ikhtibar_id'], 'idx_penyaluran_ikhtibar_divisi');
                $table->index(['tanggal_penyaluran', 'jenis_transaksi'], 'idx_penyaluran_ikhtibar_tanggal');
            });
        }

        // 5. Tabel Pengeluaran Realisasi Belanja (SPJ) Divisi Ikhtibar
        if (!Schema::hasTable('pengeluaran_ikhtibars')) {
            Schema::create('pengeluaran_ikhtibars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kegiatan_ikhtibar_id')->constrained('kegiatan_ikhtibars')->cascadeOnDelete();
                $table->foreignId('divisi_ikhtibar_id')->nullable()->constrained('divisi_ikhtibars')->nullOnDelete();
                $table->string('kode_spj')->unique();
                $table->string('judul_pengeluaran');
                $table->text('rincian_item')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->date('tanggal_nota');
                $table->string('nama_toko_penerima')->nullable();
                $table->string('metode_pembayaran')->default('Tunai');
                $table->string('bukti_nota')->nullable();
                $table->foreignId('diinput_oleh_id')->nullable()->constrained('panitia_ikhtibars')->nullOnDelete();
                $table->enum('status_verifikasi', ['Pending', 'Disetujui', 'Ditolak'])->default('Pending');
                $table->foreignId('diverifikasi_oleh_id')->nullable()->constrained('panitia_ikhtibars')->nullOnDelete();
                $table->text('catatan_verifikasi')->nullable();
                $table->timestamps();

                $table->index(['kegiatan_ikhtibar_id', 'divisi_ikhtibar_id'], 'idx_pengeluaran_ikhtibar_divisi');
                $table->index(['tanggal_nota', 'status_verifikasi'], 'idx_pengeluaran_ikhtibar_nota');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_ikhtibars');
        Schema::dropIfExists('penyaluran_dana_ikhtibars');
        Schema::dropIfExists('panitia_ikhtibars');
        Schema::dropIfExists('divisi_ikhtibars');
        Schema::dropIfExists('kegiatan_ikhtibars');
    }
};
