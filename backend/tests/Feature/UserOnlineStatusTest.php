<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ustadz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserOnlineStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_ustadz_becomes_online_upon_mobile_app_login()
    {
        $role = Role::firstOrCreate(['name' => 'ustadz']);
        $user = User::factory()->create([
            'username' => 'ustadz_ahmad',
            'password' => Hash::make('password123'),
            'last_seen_at' => null,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        Ustadz::create([
            'user_id' => $user->id,
            'kode_ustadz' => 'UST01',
            'nama_lengkap' => 'Ustadz Ahmad',
            'jenis_kelamin' => 'L',
            'is_active' => true,
        ]);

        $this->assertFalse($user->isOnline());

        // 1. Kirim request login dari App Ustadz
        $response = $this->postJson('/api/login', [
            'login_id' => 'ustadz_ahmad',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);

        // 2. Periksa apakah user sekarang terdeteksi Online
        $user->refresh();
        $this->assertNotNull($user->last_seen_at);
        $this->assertTrue($user->isOnline());
        $this->assertEquals('Online', $user->lastSeenText());
    }

    public function test_api_requests_update_last_seen_at_via_middleware()
    {
        $role = Role::firstOrCreate(['name' => 'ustadz']);
        $user = User::factory()->create([
            'username' => 'ustadz_ali',
            'password' => Hash::make('password123'),
            'last_seen_at' => now()->subMinutes(2),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        Ustadz::create([
            'user_id' => $user->id,
            'kode_ustadz' => 'UST02',
            'nama_lengkap' => 'Ustadz Ali',
            'jenis_kelamin' => 'L',
            'is_active' => true,
        ]);

        $token = $user->createToken('TestMobileToken')->plainTextToken;

        $oldLastSeen = $user->last_seen_at;

        // Kirim request ke endpoint API terproteksi
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile');

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue($user->isOnline());
    }

    public function test_ustadz_becomes_offline_upon_mobile_app_logout()
    {
        $role = Role::firstOrCreate(['name' => 'ustadz']);
        $user = User::factory()->create([
            'username' => 'ustadz_budi',
            'password' => Hash::make('password123'),
            'last_seen_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        $token = $user->createToken('TestMobileToken')->plainTextToken;

        $this->assertTrue($user->isOnline());

        // Logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);

        $user->refresh();
        $this->assertFalse($user->isOnline());
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
