<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan_ikhtibars', function (Blueprint $table) {
            $table->foreignId('kalendar_pendidikan_id')
                ->nullable()
                ->after('pengaturan_tagihan_id')
                ->constrained('kalendar_pendidikans')
                ->nullOnDelete();
        });

        // Pastikan kategori Ikhtibar tersedia di kategori_kegiatans
        $kategoriId = DB::table('kategori_kegiatans')
            ->where('nama_kategori', 'Ikhtibar')
            ->value('id');

        if (!$kategoriId) {
            $kategoriId = DB::table('kategori_kegiatans')->insertGetId([
                'nama_kategori' => 'Ikhtibar',
                'kode_warna'    => '#0284c7',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Sinkronkan kegiatan ikhtibar yang sudah memiliki tanggal_mulai dan tanggal_selesai
        $ikhtibars = DB::table('kegiatan_ikhtibars')
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_selesai')
            ->get();

        foreach ($ikhtibars as $ikhtibar) {
            $namaKegiatan = $ikhtibar->nama_kegiatan;
            if ($ikhtibar->ikhtibar_ke && !str_contains($namaKegiatan, $ikhtibar->ikhtibar_ke)) {
                $namaKegiatan .= ' (' . $ikhtibar->ikhtibar_ke . ')';
            }

            $kalendarId = DB::table('kalendar_pendidikans')->insertGetId([
                'tahun_pelajaran_id'   => $ikhtibar->tahun_pelajaran_id,
                'kategori_kegiatan_id' => $kategoriId,
                'nama_kegiatan'        => $namaKegiatan,
                'tipe_presensi'        => 'tidak_ada',
                'sesi_kegiatan'        => null,
                'keterangan'           => 'Kegiatan Ikhtibar & Haflah Akhirussanah' . ($ikhtibar->ikhtibar_ke ? ' (' . $ikhtibar->ikhtibar_ke . ')' : ''),
                'tanggal_mulai'        => $ikhtibar->tanggal_mulai,
                'tanggal_selesai'      => $ikhtibar->tanggal_selesai,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            DB::table('kegiatan_ikhtibars')
                ->where('id', $ikhtibar->id)
                ->update(['kalendar_pendidikan_id' => $kalendarId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_ikhtibars', function (Blueprint $table) {
            $table->dropForeign(['kalendar_pendidikan_id']);
            $table->dropColumn('kalendar_pendidikan_id');
        });
    }
};

