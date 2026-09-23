<?php

namespace Tests\Feature;

use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BintangTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_bintang_pelajar_page()
    {
        $role = Role::firstOrCreate(['name' => 'administrator']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $tahun = TahunPelajaran::create([
            'nama_hijriyah' => '1447-1448 H',
            'nama_masehi' => '2025-2026 M',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('bintang-pelajar.index'));

        $response->assertStatus(200);
        $response->assertSee('Bintang Pelajar', false);
        $response->assertSee('Hasil Ujian', false);
    }

    public function test_administrator_can_view_bintang_madrasah_page()
    {
        $role = Role::firstOrCreate(['name' => 'administrator']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $tahun = TahunPelajaran::create([
            'nama_hijriyah' => '1447-1448 H',
            'nama_masehi' => '2025-2026 M',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('bintang-madrasah.index'));

        $response->assertStatus(200);
        $response->assertSee('Bintang Madrasah', false);
        $response->assertSee('Best of The Best', false);
    }

    public function test_staff_with_permission_can_access_both_pages()
    {
        $role = Role::firstOrCreate(['name' => 'staff']);
        $permPelajar = Permission::firstOrCreate(['name' => 'read bintang-pelajar.index']);
        $permMadrasah = Permission::firstOrCreate(['name' => 'read bintang-madrasah.index']);
        $role->givePermissionTo([$permPelajar, $permMadrasah]);

        $staff = User::factory()->create();
        $staff->assignRole($role);

        $tahun = TahunPelajaran::create([
            'nama_hijriyah' => '1447-1448 H',
            'nama_masehi' => '2025-2026 M',
            'is_active' => true,
        ]);

        $resPelajar = $this->actingAs($staff)->get(route('bintang-pelajar.index'));
        $resPelajar->assertStatus(200);

        $resMadrasah = $this->actingAs($staff)->get(route('bintang-madrasah.index'));
        $resMadrasah->assertStatus(200);
    }
}
