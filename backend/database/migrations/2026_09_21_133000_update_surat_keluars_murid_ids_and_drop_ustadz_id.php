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
        // 1. Tambahkan kolom murid_ids (JSON) & Hapus ustadz_id jika ada
        Schema::table('surat_keluars', function (Blueprint $table) {
            if (Schema::hasColumn('surat_keluars', 'ustadz_id')) {
                try {
                    $table->dropForeign(['ustadz_id']);
                } catch (\Throwable $e) {
                    // ignore if foreign key doesn't exist
                }
                $table->dropColumn('ustadz_id');
            }

            if (!Schema::hasColumn('surat_keluars', 'murid_ids')) {
                $table->json('murid_ids')->nullable()->after('kategori_penerima');
            }
        });

        // 2. Migrasikan data lama dari murid_id dan isi_spesifik->murid_dispensasi_list ke murid_ids (JSON)
        $surats = DB::table('surat_keluars')->get();
        foreach ($surats as $s) {
            $ids = [];
            if (isset($s->murid_id) && !empty($s->murid_id)) {
                $ids[] = (int) $s->murid_id;
            }

            $isi = json_decode($s->isi_spesifik ?? '{}', true);
            if (!empty($isi['murid_dispensasi_list']) && is_array($isi['murid_dispensasi_list'])) {
                foreach ($isi['murid_dispensasi_list'] as $mItem) {
                    $mId = $mItem['murid_id'] ?? ($mItem['id'] ?? null);
                    if ($mId && !in_array((int)$mId, $ids)) {
                        $ids[] = (int) $mId;
                    }
                }
            }

            DB::table('surat_keluars')->where('id', $s->id)->update([
                'murid_ids' => count($ids) > 0 ? json_encode(array_values($ids)) : null
            ]);
        }

        // 3. Hapus kolom murid_id lama
        Schema::table('surat_keluars', function (Blueprint $table) {
            if (Schema::hasColumn('surat_keluars', 'murid_id')) {
                try {
                    $table->dropForeign(['murid_id']);
                } catch (\Throwable $e) {
                    // ignore if foreign key doesn't exist
                }
                $table->dropColumn('murid_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keluars', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_keluars', 'murid_id')) {
                $table->foreignId('murid_id')->nullable()->after('kategori_penerima')->constrained('murids')->nullOnDelete();
            }
            if (!Schema::hasColumn('surat_keluars', 'ustadz_id')) {
                $table->foreignId('ustadz_id')->nullable()->after('murid_id')->constrained('ustadzs')->nullOnDelete();
            }
            if (Schema::hasColumn('surat_keluars', 'murid_ids')) {
                $table->dropColumn('murid_ids');
            }
        });
    }
};
