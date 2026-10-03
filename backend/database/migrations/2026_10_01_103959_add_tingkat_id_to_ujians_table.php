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
        Schema::table('ujians', function (Blueprint $table) {
            $table->foreignId('tingkat_id')
                ->nullable()
                ->after('semester_id')
                ->constrained('tingkats')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropForeign(['tingkat_id']);
            $table->dropColumn('tingkat_id');
        });
    }
};
