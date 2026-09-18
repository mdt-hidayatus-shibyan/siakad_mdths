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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('app_type', 30)->default('ustadz'); // ustadz, murid, all
            $table->string('app_title')->default('Ustadz - MDTHS');
            $table->string('app_subtitle')->default('MDT Hidayatus Shibyan');
            $table->string('version', 30)->default('1.0.0');
            $table->string('build_number', 30)->default('2026.09');
            $table->string('release_date', 50)->default('September 2026');
            $table->string('release_subtitle')->default('Rilis Perdana • September 2026');
            $table->string('release_badge', 50)->default('Rilis Saat Ini');
            $table->string('status_badge', 50)->default('Versi Terbaru');
            $table->boolean('is_latest')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('new_features')->nullable();
            $table->json('improvements')->nullable();
            $table->string('dev_name')->default('Mikyal Adly Ghoffar Hasin');
            $table->string('dev_role')->default('Lead Developer & Tim IT');
            $table->string('dev_institution')->default('MDT Hidayatus Shibyan');
            $table->text('dev_description')->nullable();
            $table->json('dev_details')->nullable();
            $table->json('tech_stacks')->nullable();
            $table->string('copyright_year', 10)->default('2026');
            $table->string('copyright_owner')->default('MDT Hidayatus Shibyan');
            $table->string('copyright_subtitle')->default('All Rights Reserved • SIAKAD MDTHS Mobile');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
