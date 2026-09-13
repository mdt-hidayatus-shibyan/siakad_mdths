<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'administrator',
            'staff',
            'petugas-tabungan',
            'petugas-koperasi',
            'ustadz',
            'wali-murid',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $administrator = User::create([
            'name' => 'Mikyal Adly',
            'username' => 'mikyal_adly',
            'email' => 'mikyaladly7596@gmail.com',
            'password' => Hash::make('mdths123'),
            'is_active' => true
        ]);

        // 3. Assign Role ke User
        $administrator->assignRole('administrator');

        Administrator::firstOrCreate(
            ['user_id' => $administrator->id],
            [
                'nik'  => '3526110705960003',
                'nama_lengkap'  => $administrator->name,
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'BANGKALAN',
                'alamat' => 'DSN. MORKONENG 003/004 DESA SOMORKONENG KEC. KWANYAR',
                'no_hp' => '6285104044033',
                'is_active'     => true,
            ]
        );
    }
}
