<?php

namespace Database\Seeders;

use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\Ruangan;
use App\Models\Ustadz;
use Illuminate\Database\Seeder;

class JadwalPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        $ruangans = Ruangan::with('level')->get();
        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $jams = [
            ['jam_ke' => 'Nadzoman', 'jam_mulai' => '13:45:00', 'jam_selesai' => '14:00:00'],
            ['jam_ke' => '1', 'jam_mulai' => '14:00:00', 'jam_selesai' => '14:45:00'],
            ['jam_ke' => '2', 'jam_mulai' => '15:30:00', 'jam_selesai' => '16:15:00'],
        ];

        $ustadzs = Ustadz::where('is_active', true)->pluck('id')->toArray();
        if (empty($ustadzs)) {
            $ustadzs = [1];
        }

        $count = 0;
        foreach ($ruangans as $r) {
            $mapels = MataPelajaran::where('level_id', $r->level_id)->where('is_active', 1)->get();
            if ($mapels->isEmpty()) {
                $mapels = MataPelajaran::where('is_active', 1)->take(10)->get();
            }
            if ($mapels->isEmpty()) continue;

            $mapelIndex = 0;
            foreach ($days as $hari) {
                foreach ($jams as $jInfo) {
                    $mapel = $mapels[$mapelIndex % $mapels->count()];
                    $uId = $r->ustadz_id ?? $ustadzs[$mapelIndex % count($ustadzs)];

                    JadwalPelajaran::create([
                        'ruangan_id' => $r->id,
                        'mata_pelajaran_id' => $mapel->id,
                        'ustadz_id' => $uId,
                        'hari' => $hari,
                        'jam_ke' => $jInfo['jam_ke'],
                        'jam_mulai' => $jInfo['jam_mulai'],
                        'jam_selesai' => $jInfo['jam_selesai'],
                    ]);
                    $mapelIndex++;
                    $count++;
                }
            }
        }

        $this->command->info("Created {$count} jadwal pelajarans.");
    }
}
