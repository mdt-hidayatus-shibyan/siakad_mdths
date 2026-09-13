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
        if (Schema::hasTable('tabungans')) {
            Schema::table('tabungans', function (Blueprint $table) {
                if (!Schema::hasColumn('tabungans', 'buku_tabungan_ada')) {
                    $table->boolean('buku_tabungan_ada')->default(true)->after('status_verifikasi');
                }
                if (Schema::hasColumn('tabungans', 'status_verifikasi')) {
                    $table->string('status_verifikasi', 50)->default('Belum Diverifikasi')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tabungans') && Schema::hasColumn('tabungans', 'buku_tabungan_ada')) {
            Schema::table('tabungans', function (Blueprint $table) {
                $table->dropColumn('buku_tabungan_ada');
            });
        }
    }
};
