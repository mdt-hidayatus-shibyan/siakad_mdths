<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ustadz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UstadzUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Role 'ustadz' sudah terdaftar
        Role::firstOrCreate(['name' => 'ustadz', 'guard_name' => 'web']);

        // 2. Ambil seluruh data Ustadz yang aktif
        $activeUstadzs = Ustadz::where('is_active', true)->orderBy('id', 'asc')->get();

        if ($activeUstadzs->isEmpty()) {
            $this->command?->warn('Tidak ada data Ustadz aktif yang ditemukan di tabel ustadzs.');
            return;
        }

        $this->command?->info("Memproses pembuatan akun user untuk {$activeUstadzs->count()} Ustadz aktif...");

        $results = [];
        $createdCount = 0;
        $existingCount = 0;

        DB::beginTransaction();

        try {
            foreach ($activeUstadzs as $index => $ustadz) {
                $user = null;
                $isNew = false;

                // Cek apakah Ustadz sudah memiliki relasi user_id yang valid
                if ($ustadz->user_id) {
                    $user = User::find($ustadz->user_id);
                }

                if ($user) {
                    // Pastikan role ustadz terpasang
                    if (!$user->hasRole('ustadz')) {
                        $user->assignRole('ustadz');
                    }
                    $existingCount++;
                } else {
                    // Bersihkan nama dari gelar/tanda baca untuk username bersih
                    $cleanName = preg_replace('/[^A-Za-z0-9\s]/', '', $ustadz->nama_lengkap);
                    $baseUsername = Str::slug($cleanName, '.');
                    if (empty($baseUsername)) {
                        $baseUsername = 'ustadz.' . strtolower($ustadz->kode_ustadz);
                    }

                    $username = $baseUsername;
                    $counter = 1;

                    // Pastikan username unik di tabel users
                    while (User::where('username', $username)->exists()) {
                        $username = $baseUsername . $counter;
                        $counter++;
                    }

                    // Buat email default unik berbasis username
                    $baseEmail = $username . '@mdths.sch.id';
                    $email = $baseEmail;
                    $emailCounter = 1;

                    while (User::where('email', $email)->exists()) {
                        $email = $username . $emailCounter . '@mdths.sch.id';
                        $emailCounter++;
                    }

                    // Buat akun User baru
                    $user = User::create([
                        'name'              => $ustadz->nama_lengkap,
                        'username'          => $username,
                        'email'             => $email,
                        'password'          => Hash::make('madrasah123'),
                        'email_verified_at' => now(),
                        'is_active'         => true,
                    ]);

                    // Berikan role 'ustadz'
                    $user->assignRole('ustadz');

                    // Sambungkan user_id ke tabel ustadzs
                    $ustadz->update(['user_id' => $user->id]);

                    $isNew = true;
                    $createdCount++;
                }

                $results[] = [
                    'No'         => $index + 1,
                    'Kode'       => $ustadz->kode_ustadz,
                    'Nama'       => $ustadz->nama_lengkap,
                    'NIGM'       => $ustadz->nigm ?? '-',
                    'Username'   => $user->username,
                    'Email'      => $user->email,
                    'Password'   => $isNew ? 'madrasah123' : '(Tetap)',
                    'Status'     => $isNew ? 'Dibuat Baru' : 'Sudah Ada',
                ];
            }

            DB::commit();

            // Tampilkan tabel hasil di console jika dijalankan via artisan
            if ($this->command) {
                $this->command->table(
                    ['No', 'Kode', 'Nama', 'NIGM', 'Username', 'Email', 'Password Default', 'Status'],
                    $results
                );
                $this->command->info("Selesai! Akun baru dibuat: {$createdCount} | Akun sudah ada: {$existingCount} | Total: {$activeUstadzs->count()}");
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error("Terjadi kesalahan: " . $e->getMessage());
            throw $e;
        }
    }
}
