<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppVersion;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppVersion::firstOrCreate(
            ['app_type' => 'ustadz', 'version' => '1.0.0'],
            AppVersion::defaultUstadzData()
        );
    }
}
