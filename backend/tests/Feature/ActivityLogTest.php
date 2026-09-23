<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_activity_logs()
    {
        $role = Role::firstOrCreate(['name' => 'administrator']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $log = ActivityLog::create([
            'user_id' => $admin->id,
            'event' => 'login',
            'platform' => 'app_ustadz',
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Dart/3.0 (dart:io) MDT-App-Ustadz/1.0.0 (Android 14; SM-S918B)',
            'device' => 'Samsung SM-S918B',
            'os' => 'Android 14',
            'browser' => 'Flutter App (Dart)',
            'status' => 'success',
            'description' => 'Login berhasil via App Ustadz',
            'properties' => ['platform' => 'app_ustadz'],
        ]);

        $response = $this->actingAs($admin)->get(route('log-aktivitas.index'));
        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas', false);
        $response->assertSee('192.168.1.10');
        $response->assertSee('App Ustadz');
    }

    public function test_can_show_detail_modal_via_ajax()
    {
        $role = Role::firstOrCreate(['name' => 'administrator']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $log = ActivityLog::create([
            'user_id' => $admin->id,
            'event' => 'login',
            'platform' => 'app_ustadz',
            'ip_address' => '192.168.1.55',
            'user_agent' => 'Dart/3.0 MDT-App-Ustadz/1.0.0',
            'status' => 'success',
            'description' => 'Login berhasil via App Ustadz',
        ]);

        $response = $this->actingAs($admin)->get(route('log-aktivitas.show', $log->id), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Rincian Log Aktivitas');
        $response->assertSee('192.168.1.55');
    }

    public function test_can_delete_log_via_ajax()
    {
        $role = Role::firstOrCreate(['name' => 'administrator']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $log = ActivityLog::create([
            'user_id' => $admin->id,
            'event' => 'login',
            'platform' => 'web',
            'ip_address' => '127.0.0.1',
            'status' => 'success',
            'description' => 'Web login',
        ]);

        $response = $this->actingAs($admin)->delete(route('log-aktivitas.destroy', $log->id), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('activity_logs', ['id' => $log->id]);
    }
}
