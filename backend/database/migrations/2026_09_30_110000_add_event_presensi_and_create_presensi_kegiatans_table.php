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
        // 1. Tambah kolom konfigurasi presensi kegiatan pada kalendar_pendidikans
        Schema::table('kalendar_pendidikans', function (Blueprint $table) {
            $table->enum('tipe_presensi', ['tidak_ada', 'harian', 'multi_sesi'])
                ->default('tidak_ada')
                ->after('nama_kegiatan');
            $table->json('sesi_kegiatan')
                ->nullable()
                ->after('tipe_presensi'); // Contoh: ["Siang", "Malam"] atau ["Harian"]
            $table->text('keterangan')
                ->nullable()
                ->after('sesi_kegiatan');
        });

        // 2. Buat tabel presensi kegiatan / event khusus untuk murid
        Schema::create('presensi_kegiatan_murids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kalendar_pendidikan_id')->constrained('kalendar_pendidikans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('sesi', 50)->default('Harian'); // Contoh: 'Siang', 'Malam', 'Harian'
            $table->foreignId('ruangan_id')->constrained('ruangans')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi'])->default('Hadir');
            $table->string('catatan', 255)->nullable();
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['kalendar_pendidikan_id', 'tanggal', 'sesi', 'ruangan_id', 'murid_id'],
                'presensi_kegiatan_murid_unique'
            );
        });

        // 3. Buat tabel presensi kegiatan / event khusus untuk ustadz
        Schema::create('presensi_kegiatan_ustadzs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kalendar_pendidikan_id')->constrained('kalendar_pendidikans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('sesi', 50)->default('Harian'); // Contoh: 'Siang', 'Malam', 'Harian'
            $table->foreignId('ustadz_id')->constrained('ustadzs')->cascadeOnDelete();
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Hadir');
            $table->timestamp('waktu_checkin')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('diinput_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['kalendar_pendidikan_id', 'tanggal', 'sesi', 'ustadz_id'],
                'presensi_kegiatan_ustadz_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_kegiatan_ustadzs');
        Schema::dropIfExists('presensi_kegiatan_murids');

        Schema::table('kalendar_pendidikans', function (Blueprint $table) {
            $table->dropColumn(['tipe_presensi', 'sesi_kegiatan', 'keterangan']);
        });
    }
};
