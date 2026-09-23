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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 50)->default('login'); // login, logout, failed_login, password_change, dll.
            $table->string('platform', 30)->default('web'); // web, app_ustadz, app_murid, system
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->text('user_agent')->nullable();
            $table->string('device', 100)->nullable(); // e.g. Samsung Galaxy A54, Windows 11, iOS iPhone
            $table->string('browser', 50)->nullable(); // e.g. Chrome 128, Flutter Mobile App, Safari
            $table->string('os', 50)->nullable(); // e.g. Android 14, Windows 11, iOS 17
            $table->string('status', 20)->default('success'); // success, failed, blocked
            $table->string('description', 255)->nullable();
            $table->json('properties')->nullable(); // Metadata ekstra (login_id, guard, token, reason, dll.)
            $table->timestamps();

            // Indexes untuk performa query filter & pagination cepat
            $table->index('user_id');
            $table->index('event');
            $table->index('platform');
            $table->index('ip_address');
            $table->index('status');
            $table->index('created_at');
            $table->index(['platform', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
