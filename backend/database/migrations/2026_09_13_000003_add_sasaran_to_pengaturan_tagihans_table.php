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
        if (Schema::hasTable('pengaturan_tagihans') && !Schema::hasColumn('pengaturan_tagihans', 'sasaran')) {
            Schema::table('pengaturan_tagihans', function (Blueprint $table) {
                $table->enum('sasaran', ['murid', 'wali_murid'])->default('murid')->after('tipe');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pengaturan_tagihans') && Schema::hasColumn('pengaturan_tagihans', 'sasaran')) {
            Schema::table('pengaturan_tagihans', function (Blueprint $table) {
                $table->dropColumn('sasaran');
            });
        }
    }
};
