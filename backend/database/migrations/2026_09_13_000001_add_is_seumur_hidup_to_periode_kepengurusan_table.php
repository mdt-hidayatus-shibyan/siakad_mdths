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
        if (Schema::hasTable('periode_kepengurusan') && !Schema::hasColumn('periode_kepengurusan', 'is_seumur_hidup')) {
            Schema::table('periode_kepengurusan', function (Blueprint $table) {
                $table->boolean('is_seumur_hidup')->default(false)->after('tanggal_selesai');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('periode_kepengurusan') && Schema::hasColumn('periode_kepengurusan', 'is_seumur_hidup')) {
            Schema::table('periode_kepengurusan', function (Blueprint $table) {
                $table->dropColumn('is_seumur_hidup');
            });
        }
    }
};
