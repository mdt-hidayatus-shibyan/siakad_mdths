<?php

namespace Database\Seeders;

use App\Models\BulanHijriyah;
use App\Models\JadwalPelajaran;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PresensiMuridSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 1;
        $semester = Semester::where('is_active', true)->first();
        $semesterId = $semester->id ?? 1;

        $muridRepo = app(MuridRuanganRepository::class);
        $ruangans = Ruangan::where('tahun_pelajaran_id', $tahunId)->get();

        $dates = [
            Carbon::now()->subDays(14)->format('Y-m-d'),
            Carbon::now()->subDays(10)->format('Y-m-d'),
            Carbon::now()->subDays(7)->format('Y-m-d'),
            Carbon::now()->subDays(3)->format('Y-m-d'),
            Carbon::now()->subDays(1)->format('Y-m-d'),
        ];

        $statuses = ['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpha'];

        $count = 0;
        foreach ($ruangans as $r) {
            $murids = $muridRepo->getMuridByRuanganAndTahun($r->id, $tahunId, 'Aktif');
            if ($murids->isEmpty()) continue;

            $jadwals = JadwalPelajaran::where('ruangan_id', $r->id)->get();
            if ($jadwals->isEmpty()) continue;

            foreach ($jadwals->take(6) as $j) {
                foreach ($dates as $dIndex => $tgl) {
                    foreach ($murids as $mIndex => $m) {
                        $randStatus = $statuses[($mIndex + $dIndex * 3) % count($statuses)];
                        PresensiMurid::updateOrCreate(
                            [
                                'jadwal_pelajaran_id' => $j->id,
                                'murid_id' => $m->id,
                                'tanggal' => $tgl,
                            ],
                            [
                                'status' => $randStatus,
                                'semester_id' => $semesterId,
                            ]
                        );
                        $count++;
                    }
                }
            }
        }

        $this->command->info("Created/updated {$count} presensi records.");
    }
}
