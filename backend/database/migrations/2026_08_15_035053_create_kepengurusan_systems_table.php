<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('jabatan_pengurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->string('level')->nullable();
            $table->timestamps();
        });

        Schema::create('periode_kepengurusan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_seumur_hidup')->default(false);
            $table->boolean('status_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->string('tanda_tangan')->nullable();

            $table->foreignId('ustadz_id')->nullable()->constrained('ustadzs')->onDelete('cascade');

            $table->timestamps();
        });


        Schema::create('pengurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('jabatan_pengurus')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periode_kepengurusan')->onDelete('cascade');
            $table->string('no_sk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop harus dibalik urutannya (tabel transaksi dulu, baru master)
        Schema::dropIfExists('pengurus');
        Schema::dropIfExists('anggota');
        Schema::dropIfExists('periode_kepengurusan');
        Schema::dropIfExists('jabatan_pengurus');
    }
};
