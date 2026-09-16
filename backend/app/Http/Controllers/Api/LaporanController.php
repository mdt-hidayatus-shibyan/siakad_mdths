<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\JadwalPelajaran;
use App\Models\Level;
use App\Models\PelanggaranMurid;
use App\Models\PengaturanAkademik;
use App\Models\PresensiMurid;
use App\Models\PresensiUstadz;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\RiwayatKenaikan;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Helper to get active academic year and accessible rooms for logged-in Ustadz
     */
    private function getContextRuangans(Request $request)
    {
        $user = $request->user();
        $ustadzId = $user->ustadz->id ?? null;
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 1;

        // Ambil daftar ruangan yang diampu ustadz:
        // 1. Ustadz sebagai Wali Ruangan (ruangans.ustadz_id)
        // 2. Ustadz sebagai Pengajar Mata Pelajaran (jadwal_pelajarans.ustadz_id)
        $ruanganIdsWali = Ruangan::where('ustadz_id', $ustadzId)->pluck('id')->toArray();
        $ruanganIdsJadwal = JadwalPelajaran::where('ustadz_id', $ustadzId)->pluck('ruangan_id')->toArray();
        $accessibleIds = array_values(array_unique(array_filter(array_merge($ruanganIdsWali, $ruanganIdsJadwal))));

        $accessibleRuangans = collect();

        if (!empty($accessibleIds)) {
            $accessibleRuangans = Ruangan::with('level')
                ->whereIn('id', $accessibleIds)
                ->when($tahunAktif, function ($q) use ($tahunAktif) {
                    $q->where(function ($sub) use ($tahunAktif) {
                        $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                            ->orWhereNull('tahun_pelajaran_id');
                    });
                })
                ->orderBy('level_id')
                ->orderBy('nama_ruangan')
                ->get();

            if ($accessibleRuangans->isEmpty()) {
                $accessibleRuangans = Ruangan::with('level')
                    ->whereIn('id', $accessibleIds)
                    ->orderBy('level_id')
                    ->orderBy('nama_ruangan')
                    ->get();
            }
        }

        // Fallback jika ustadz belum memiliki penugasan ruangan/jadwal (misal akun baru atau admin)
        if ($accessibleRuangans->isEmpty()) {
            $accessibleRuangans = Ruangan::with('level')
                ->when($tahunAktif, function ($q) use ($tahunAktif) {
                    $q->where('tahun_pelajaran_id', $tahunAktif->id);
                })
                ->orderBy('level_id')
                ->orderBy('nama_ruangan')
                ->get();

            if ($accessibleRuangans->isEmpty()) {
                $accessibleRuangans = Ruangan::with('level')->orderBy('level_id')->orderBy('nama_ruangan')->get();
            }
        }

        if ($request->filled('ruangan_id')) {
            $ruangan = $accessibleRuangans->firstWhere('id', (int) $request->ruangan_id) ?? Ruangan::with('level')->find($request->ruangan_id);
        } else {
            $ruangan = $accessibleRuangans->first();
        }

        if (!$ruangan) {
            $ruangan = Ruangan::with('level')->where('tahun_pelajaran_id', $tahunId)->first() ?? Ruangan::with('level')->first();
        }

        return [$tahunAktif, $tahunId, $accessibleRuangans, $ruangan, $ustadzId];
    }

    // =========================================================================
    // 1. LAPORAN PRESENSI MURID
    // =========================================================================
    public function getLaporanPresensiMurid(Request $request)
    {
        [$tahunAktif, $tahunId, $accessibleRuangans, $ruangan, $ustadzId] = $this->getContextRuangans($request);

        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');
        $muridIds = $murids->pluck('id');

        $queryAll = PresensiMurid::whereIn('murid_id', $muridIds);

        // Ambil daftar semester pada tahun pelajaran aktif
        $semesterList = Semester::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('id', 'asc')
            ->get();

        $selectedSemesterId = $request->filled('semester_id') ? (int) $request->semester_id : null;
        if (!$selectedSemesterId && !$request->filled('bulan_hijriyah_id')) {
            $semesterAktif = $semesterList->firstWhere('is_active', true) ?? $semesterList->first();
            $selectedSemesterId = $semesterAktif ? $semesterAktif->id : null;
        }

        // Filter Bulan Hijriyah / Semester / Rentang Tanggal jika ada
        $bulanHijriyahList = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->get();

        if ($request->filled('bulan_hijriyah_id')) {
            $bulan = $bulanHijriyahList->firstWhere('id', (int) $request->bulan_hijriyah_id);
            if ($bulan && $bulan->tanggal_mulai_masehi && $bulan->tanggal_selesai_masehi) {
                $queryAll->whereBetween('tanggal', [$bulan->tanggal_mulai_masehi, $bulan->tanggal_selesai_masehi]);
            }
        } elseif ($selectedSemesterId) {
            $sem = $semesterList->firstWhere('id', $selectedSemesterId);
            if ($sem) {
                $queryAll->where(function ($q) use ($sem) {
                    $q->where('semester_id', $sem->id);
                    if ($sem->tanggal_mulai && $sem->tanggal_selesai) {
                        $q->orWhereBetween('tanggal', [$sem->tanggal_mulai, $sem->tanggal_selesai]);
                    }
                });
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $queryAll->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        // 1. Ambil seluruh Jadwal Pelajaran di Ruangan ini (semua mapel jika wali, atau mapel yang diajarkan jika ustadz pengampu)
        $isWaliOfThisRoom = ($ruangan->ustadz_id == $ustadzId);

        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran', 'ustadz'])
            ->where('ruangan_id', $ruangan->id);

        if (!$isWaliOfThisRoom && !empty($ustadzId)) {
            $hasJadwalInRoom = (clone $jadwalQuery)->where('ustadz_id', $ustadzId)->exists();
            if ($hasJadwalInRoom) {
                $jadwalQuery->where('ustadz_id', $ustadzId);
            }
        }

        $jadwalList = $jadwalQuery
            ->orderByRaw("FIELD(hari, 'Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis')")
            ->orderBy('jam_ke')
            ->get();

        if ($jadwalList->isNotEmpty()) {
            $queryAll->whereIn('jadwal_pelajaran_id', $jadwalList->pluck('id'));
        }

        $konfig = PengaturanAkademik::first();
        $poinAlphaRate = (float) ($konfig->poin_alpha ?? 1.0);
        $poinIzinRate = (float) ($konfig->poin_izin ?? 0.16);

        $allPresensiInPeriod = $queryAll->get();

        $rekapJadwalList = $jadwalList->map(function ($j) use ($allPresensiInPeriod) {
            $pJadwal = $allPresensiInPeriod->where('jadwal_pelajaran_id', $j->id);
            $pertemuan = $pJadwal->pluck('tanggal')->unique()->count();
            $h = $pJadwal->where('status', 'Hadir')->count();
            $s = $pJadwal->where('status', 'Sakit')->count();
            $i = $pJadwal->where('status', 'Izin')->count();
            $a = $pJadwal->where('status', 'Alpha')->count();
            $d = $pJadwal->where('status', 'Dispensasi')->count();
            $tot = $h + $s + $i + $a + $d;
            $persen = $tot > 0 ? round(($h / $tot) * 100, 1) : 0;

            $jamText = match ($j->jam_ke) {
                'Nadzoman' => '13:45 - 14:00 WIB',
                '1' => '14:00 - 14:45 WIB',
                '2' => '15:30 - 16:15 WIB',
                'Ekstra' => '20:00 - 21:00 WIB',
                default => 'Jam Ke-' . $j->jam_ke,
            };

            return [
                'id' => $j->id,
                'hari' => $j->hari,
                'jam_ke' => $j->jam_ke,
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'jam_text' => $jamText,
                'mata_pelajaran_id' => $j->mata_pelajaran_id,
                'nama_mapel' => $j->mataPelajaran->nama_mapel ?? 'Mata Pelajaran',
                'kode_mapel' => $j->mataPelajaran->kode_mapel ?? '-',
                'ustadz_id' => $j->ustadz_id,
                'nama_ustadz' => $j->ustadz->nama_lengkap ?? ($j->ustadz->nama ?? '-'),
                'total_pertemuan' => $pertemuan,
                'total_hadir' => $h,
                'total_sakit' => $s,
                'total_izin' => $i,
                'total_alpha' => $a,
                'total_dispensasi' => $d,
                'total_presensi' => $tot,
                'persentase_kehadiran' => $persen,
            ];
        });

        // 2. Filter data presensi jika memilih jadwal spesifik
        $selectedJadwalId = $request->filled('jadwal_pelajaran_id') ? (int) $request->jadwal_pelajaran_id : null;
        $selectedJadwal = null;

        if ($selectedJadwalId) {
            $presensiData = $allPresensiInPeriod->where('jadwal_pelajaran_id', $selectedJadwalId);
            $selectedJadwal = $rekapJadwalList->firstWhere('id', $selectedJadwalId);
        } else {
            $presensiData = $allPresensiInPeriod;
        }

        $totalPertemuan = $presensiData->pluck('tanggal')->unique()->count();
        $totalHadir = $presensiData->where('status', 'Hadir')->count();
        $totalSakit = $presensiData->where('status', 'Sakit')->count();
        $totalIzin = $presensiData->where('status', 'Izin')->count();
        $totalAlpha = $presensiData->where('status', 'Alpha')->count();
        $totalDispensasi = $presensiData->where('status', 'Dispensasi')->count();
        $totalSemua = $totalHadir + $totalSakit + $totalIzin + $totalAlpha + $totalDispensasi;
        $persentaseKelas = $totalSemua > 0 ? round(($totalHadir / $totalSemua) * 100, 1) : 0;

        // 3. Riwayat Pertemuan per Tanggal (Khusus jika jadwal tertentu dipilih)
        $riwayatPertemuan = [];
        if ($selectedJadwalId && $presensiData->isNotEmpty()) {
            $groupedByDate = $presensiData->groupBy('tanggal')->sortKeysDesc();
            foreach ($groupedByDate as $tgl => $items) {
                $tglHadir = $items->where('status', 'Hadir')->count();
                $tglSakit = $items->where('status', 'Sakit')->count();
                $tglIzin = $items->where('status', 'Izin')->count();
                $tglAlpha = $items->where('status', 'Alpha')->count();
                $tglDispen = $items->where('status', 'Dispensasi')->count();

                $hariTanggal = null;
                try {
                    $hariTanggal = Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM YYYY');
                } catch (\Exception $e) {
                    $hariTanggal = $tgl;
                }

                $muridAbsen = $items->whereIn('status', ['Alpha', 'Sakit', 'Izin', 'Dispensasi'])->map(function ($it) use ($murids) {
                    $m = $murids->firstWhere('id', $it->murid_id);
                    return [
                        'murid_id' => $it->murid_id,
                        'nama' => $m->nama_lengkap ?? $m->nama ?? 'Murid',
                        'nism' => $m->nism ?? '',
                        'status' => $it->status,
                    ];
                })->values();

                $riwayatPertemuan[] = [
                    'tanggal' => $tgl,
                    'hari_tanggal' => $hariTanggal,
                    'total_hadir' => $tglHadir,
                    'total_sakit' => $tglSakit,
                    'total_izin' => $tglIzin,
                    'total_alpha' => $tglAlpha,
                    'total_dispensasi' => $tglDispen,
                    'total_absen' => $tglIzin + $tglSakit + $tglAlpha + $tglDispen,
                    'murid_absen' => $muridAbsen,
                ];
            }
        }

        // 4. Rekap Presensi per Murid (Perhitungan persis seperti presensi-murid.rekap)
        $rekapMurid = [];
        $totalPoinKelas = 0;

        foreach ($murids as $m) {
            $pMurid = $presensiData->where('murid_id', $m->id);
            $h = $pMurid->where('status', 'Hadir')->count();
            $s = $pMurid->where('status', 'Sakit')->count();
            $i = $pMurid->where('status', 'Izin')->count();
            $a = $pMurid->where('status', 'Alpha')->count();
            $d = $pMurid->where('status', 'Dispensasi')->count();
            $tot = $h + $s + $i + $a + $d;
            $persen = $tot > 0 ? round(($h / $tot) * 100, 1) : 0;

            $poinAlpha = $a * $poinAlphaRate;
            $poinIzin = $i * $poinIzinRate;
            $totalPoin = round($poinAlpha + $poinIzin, 2);
            $totalPoinKelas += $totalPoin;

            $predikat = 'Sangat Baik';
            if ($persen < 60 || $a >= 5) {
                $predikat = 'Kurang';
            } elseif ($persen < 75 || $a >= 3) {
                $predikat = 'Cukup';
            } elseif ($persen < 90) {
                $predikat = 'Baik';
            }

            $rekapMurid[] = [
                'murid_id' => $m->id,
                'nama' => $m->nama_lengkap ?? $m->nama,
                'nism' => $m->nism ?? '',
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'foto' => $m->foto ? asset('storage/' . $m->foto) : null,
                'wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'hadir_count' => $h,
                'sakit_count' => $s,
                'izin_count' => $i,
                'alpha_count' => $a,
                'dispensasi_count' => $d,
                'total_presensi' => $tot,
                'persentase_kehadiran' => $persen,
                'akumulasi_poin' => $totalPoin,
                'predikat' => $predikat,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ruangan_id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'is_wali_ruangan' => (bool) $isWaliOfThisRoom,
                'wali_ruangan_nama' => $ruangan->waliRuangan->nama_lengkap ?? ($ruangan->waliRuangan->nama ?? '-'),
                'tahun_pelajaran' => $tahunAktif->nama_lengkap ?? ($tahunAktif->nama_masehi ?? 'Tahun Aktif'),
                'selected_jadwal_id' => $selectedJadwalId,
                'selected_jadwal' => $selectedJadwal,
                'poin_alpha_rate' => $poinAlphaRate,
                'poin_izin_rate' => $poinIzinRate,
                'total_murid' => $murids->count(),
                'total_hari_efektif' => $totalPertemuan,
                'total_hadir' => $totalHadir,
                'total_sakit' => $totalSakit,
                'total_izin' => $totalIzin,
                'total_alpha' => $totalAlpha,
                'total_dispensasi' => $totalDispensasi,
                'total_poin_kelas' => round($totalPoinKelas, 2),
                'persentase_kehadiran_kelas' => $persentaseKelas,
                'selected_semester_id' => $selectedSemesterId,
                'semester_list' => $semesterList->map(fn($s) => [
                    'id' => $s->id,
                    'nama_semester' => $s->nama_semester,
                    'is_active' => (bool) $s->is_active,
                ]),
                'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_nama' => $r->level->nama_level ?? '-',
                ]),
                'bulan_hijriyah_list' => $bulanHijriyahList->map(function ($b) use ($semesterList) {
                    $matchingSem = $semesterList->first(function ($s) use ($b) {
                        if ($s->tanggal_mulai && $s->tanggal_selesai && $b->tanggal_mulai_masehi && $b->tanggal_selesai_masehi) {
                            return $b->tanggal_selesai_masehi >= $s->tanggal_mulai && $b->tanggal_mulai_masehi <= $s->tanggal_selesai;
                        }
                        return false;
                    });
                    if (!$matchingSem && $semesterList->count() >= 2) {
                        $matchingSem = $b->urutan <= 5 ? $semesterList[0] : $semesterList[1];
                    }

                    return [
                        'id' => $b->id,
                        'nama_bulan' => $b->nama_bulan,
                        'tahun_hijriyah' => $b->tahun_hijriyah,
                        'urutan' => $b->urutan,
                        'semester_id' => $matchingSem ? $matchingSem->id : ($b->semester_id ?? null),
                        'semester_nama' => $matchingSem ? $matchingSem->nama_semester : $b->semester,
                    ];
                }),
                'jadwal_pelajaran_list' => $rekapJadwalList,
                'riwayat_pertemuan' => $riwayatPertemuan,
                'rekap_murid' => $rekapMurid,
            ]
        ], 200);
    }

    // =========================================================================
    // 2. LAPORAN PRESENSI USTADZ
    // =========================================================================
    public function getLaporanPresensiUstadz(Request $request)
    {
        $user = $request->user();
        $currentUstadz = $user->ustadz;
        [$tahunAktif, $tahunId, $accessibleRuangans, $ruangan, $ustadzId] = $this->getContextRuangans($request);

        if (!$ruangan && !$currentUstadz) {
            return response()->json([
                'success' => false,
                'message' => 'Data ustadz / ruangan tidak ditemukan.'
            ], 404);
        }

        $isWaliOfThisRoom = ($ruangan && $ruangan->ustadz_id == $ustadzId);
        $isPribadi = $request->has('is_pribadi')
            ? filter_var($request->is_pribadi, FILTER_VALIDATE_BOOLEAN)
            : !$isWaliOfThisRoom;

        $semesterList = Semester::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('id', 'asc')
            ->get();

        $selectedSemesterId = $request->filled('semester_id') ? (int) $request->semester_id : null;
        if (!$selectedSemesterId && !$request->filled('bulan_hijriyah_id')) {
            $semesterAktif = $semesterList->firstWhere('is_active', true) ?? $semesterList->first();
            $selectedSemesterId = $semesterAktif ? $semesterAktif->id : null;
        }

        $bulanList = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->get();

        if ($isPribadi) {
            // === MODE PRIBADI (PRESENSI MENGAJAR SAYA - GURU PENGAMPU) ===
            $queryAll = PresensiUstadz::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.ruangan', 'ustadz'])
                ->where('ustadz_id', $ustadzId);

            if ($request->filled('ruangan_id') && $ruangan) {
                $queryAll->whereHas('jadwalPelajaran', function ($q) use ($ruangan) {
                    $q->where('ruangan_id', $ruangan->id);
                });
            }

            if ($request->filled('bulan_hijriyah_id')) {
                $bulan = $bulanList->firstWhere('id', (int) $request->bulan_hijriyah_id);
                if ($bulan && $bulan->tanggal_mulai_masehi && $bulan->tanggal_selesai_masehi) {
                    $queryAll->whereBetween('tanggal', [$bulan->tanggal_mulai_masehi, $bulan->tanggal_selesai_masehi]);
                }
            } elseif ($selectedSemesterId) {
                $sem = $semesterList->firstWhere('id', $selectedSemesterId);
                if ($sem && $sem->tanggal_mulai && $sem->tanggal_selesai) {
                    $queryAll->whereBetween('tanggal', [$sem->tanggal_mulai, $sem->tanggal_selesai]);
                }
            } elseif ($request->filled('start_date') && $request->filled('end_date')) {
                $queryAll->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            }

            if ($request->filled('status') && $request->status !== 'Semua') {
                $queryAll->where('status', $request->status);
            }

            $allPresensiInPeriod = $queryAll->orderBy('tanggal', 'desc')->get();
            $filteredPresensi = $allPresensiInPeriod;

            $h = $filteredPresensi->where('status', 'Hadir')->count();
            $i = $filteredPresensi->where('status', 'Izin')->count();
            $s = $filteredPresensi->where('status', 'Sakit')->count();
            $t = $filteredPresensi->where('status', 'Tugas')->count();
            $a = $filteredPresensi->where('status', 'Alpha')->count();
            $totalSesi = $filteredPresensi->count();
            $persen = $totalSesi > 0 ? round((($h + $t) / $totalSesi) * 100, 1) : 0;

            // Mapel yang diampu ustadz ini
            $jadwalUstadz = JadwalPelajaran::with('mataPelajaran')
                ->where('ustadz_id', $ustadzId)
                ->when($request->filled('ruangan_id') && $ruangan, function ($q) use ($ruangan) {
                    $q->where('ruangan_id', $ruangan->id);
                })
                ->get();

            $mapelList = $jadwalUstadz->pluck('mataPelajaran.nama_mapel')->filter()->unique()->values()->toArray();

            $rekapUstadz = collect([
                [
                    'ustadz_id' => $currentUstadz->id ?? $ustadzId,
                    'nama' => $currentUstadz->nama_lengkap ?? ($currentUstadz->nama ?? ($user->name ?? 'Ustadz')),
                    'niup' => $currentUstadz->niup ?? '-',
                    'foto' => $currentUstadz?->foto ? asset('storage/' . $currentUstadz->foto) : null,
                    'mapel_list' => $mapelList,
                    'total_sesi' => $totalSesi,
                    'total_hadir' => $h,
                    'total_tugas' => $t,
                    'total_izin' => $i,
                    'total_sakit' => $s,
                    'total_alpha' => $a,
                    'persentase_kehadiran' => $persen,
                ]
            ]);

            $riwayat = $filteredPresensi->map(function ($p) use ($ruangan) {
                $hariTgl = null;
                try {
                    $hariTgl = Carbon::parse($p->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
                } catch (\Exception $e) {
                }

                return [
                    'id' => $p->id,
                    'ustadz_id' => $p->ustadz_id,
                    'nama_ustadz' => $p->ustadz->nama_lengkap ?? ($p->ustadz->nama ?? 'Ustadz'),
                    'niup_ustadz' => $p->ustadz->niup ?? '-',
                    'foto_ustadz' => $p->ustadz?->foto ? asset('storage/' . $p->ustadz->foto) : null,
                    'tanggal' => (string) $p->tanggal,
                    'hari_tanggal' => $hariTgl,
                    'status' => $p->status,
                    'jam_masuk' => $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-',
                    'jam_keluar' => $p->jam_keluar ? substr($p->jam_keluar, 0, 5) : null,
                    'mapel' => $p->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-',
                    'nama_ruangan' => $p->jadwalPelajaran->ruangan->nama_ruangan ?? ($ruangan->nama_ruangan ?? '-'),
                    'keterangan' => $p->keterangan ?? '-',
                    'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
                ];
            })->values();

            $daftarUstadz = $rekapUstadz->map(fn($u) => [
                'id' => $u['ustadz_id'],
                'nama' => $u['nama'],
                'niup' => $u['niup'],
                'foto' => $u['foto'],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'ruangan_id' => $ruangan->id ?? null,
                    'nama_ruangan' => $ruangan->nama_ruangan ?? 'Semua Kelas',
                    'level_nama' => $ruangan->level->nama_level ?? '-',
                    'is_wali_ruangan' => false,
                    'selected_semester_id' => $selectedSemesterId,
                    'selected_ustadz_id' => $ustadzId,
                    'semester_list' => $semesterList->map(fn($s) => [
                        'id' => $s->id,
                        'nama_semester' => $s->nama_semester,
                        'is_active' => (bool) $s->is_active,
                    ]),
                    'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                        'id' => $r->id,
                        'nama_ruangan' => $r->nama_ruangan,
                        'level_nama' => $r->level->nama_level ?? '-',
                    ]),
                    'daftar_ustadz' => $daftarUstadz,
                    'tahun_pelajaran' => $tahunAktif->nama_lengkap ?? ($tahunAktif->nama_masehi ?? 'Tahun Aktif'),
                    'total_sesi' => $totalSesi,
                    'total_hadir' => $h,
                    'total_tugas' => $t,
                    'total_izin' => $i,
                    'total_sakit' => $s,
                    'total_alpha' => $a,
                    'persentase_kehadiran' => $persen,
                    'bulan_hijriyah_list' => $bulanList->map(function ($b) use ($semesterList) {
                        $matchingSem = $semesterList->first(function ($s) use ($b) {
                            if ($s->tanggal_mulai && $s->tanggal_selesai && $b->tanggal_mulai_masehi && $b->tanggal_selesai_masehi) {
                                return $b->tanggal_selesai_masehi >= $s->tanggal_mulai && $b->tanggal_mulai_masehi <= $s->tanggal_selesai;
                            }
                            return false;
                        });
                        if (!$matchingSem && $semesterList->count() >= 2) {
                            $matchingSem = $b->urutan <= 5 ? $semesterList[0] : $semesterList[1];
                        }

                        return [
                            'id' => $b->id,
                            'nama_bulan' => $b->nama_bulan,
                            'tahun_hijriyah' => $b->tahun_hijriyah,
                            'urutan' => $b->urutan,
                            'semester_id' => $matchingSem ? $matchingSem->id : ($b->semester_id ?? null),
                            'semester_nama' => $matchingSem ? $matchingSem->nama_semester : $b->semester,
                        ];
                    }),
                    'rekap_ustadz' => $rekapUstadz,
                    'riwayat' => $riwayat,
                ]
            ], 200);
        } else {
            // === MODE WALI RUANGAN (LAPORAN PRESENSI USTADZ PENGAJAR KELAS BINAAN) ===
            $jadwalRuangan = JadwalPelajaran::with(['mataPelajaran', 'ustadz'])
                ->where('ruangan_id', $ruangan->id)
                ->get();

            $teacherIdsFromJadwal = $jadwalRuangan->whereNotNull('ustadz_id')->pluck('ustadz_id')->toArray();
            $presensiTeacherIds = PresensiUstadz::whereHas('jadwalPelajaran', function ($q) use ($ruangan) {
                $q->where('ruangan_id', $ruangan->id);
            })->pluck('ustadz_id')->toArray();

            $allTeacherIds = array_unique(array_filter(array_merge($teacherIdsFromJadwal, $presensiTeacherIds, [$ruangan->ustadz_id])));

            if (!empty($allTeacherIds)) {
                $daftarUstadzQuery = Ustadz::whereIn('id', $allTeacherIds)->where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();
            } else {
                $daftarUstadzQuery = Ustadz::where('id', $ruangan->ustadz_id ?? ($currentUstadz->id ?? 0))->get();
            }

            if ($daftarUstadzQuery->isEmpty() && $currentUstadz) {
                $daftarUstadzQuery = collect([$currentUstadz]);
            }

            $queryAll = PresensiUstadz::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.ruangan', 'ustadz'])
                ->whereHas('jadwalPelajaran', function ($q) use ($ruangan) {
                    $q->where('ruangan_id', $ruangan->id);
                });

            if ($request->filled('bulan_hijriyah_id')) {
                $bulan = $bulanList->firstWhere('id', (int) $request->bulan_hijriyah_id);
                if ($bulan && $bulan->tanggal_mulai_masehi && $bulan->tanggal_selesai_masehi) {
                    $queryAll->whereBetween('tanggal', [$bulan->tanggal_mulai_masehi, $bulan->tanggal_selesai_masehi]);
                }
            } elseif ($selectedSemesterId) {
                $sem = $semesterList->firstWhere('id', $selectedSemesterId);
                if ($sem && $sem->tanggal_mulai && $sem->tanggal_selesai) {
                    $queryAll->whereBetween('tanggal', [$sem->tanggal_mulai, $sem->tanggal_selesai]);
                }
            } elseif ($request->filled('start_date') && $request->filled('end_date')) {
                $queryAll->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            }

            if ($request->filled('status') && $request->status !== 'Semua') {
                $queryAll->where('status', $request->status);
            }

            $allPresensiInPeriod = $queryAll->orderBy('tanggal', 'desc')->get();

            $selectedUstadzId = $request->filled('ustadz_id') ? (int) $request->ustadz_id : null;
            $filteredPresensi = $selectedUstadzId ? $allPresensiInPeriod->where('ustadz_id', $selectedUstadzId) : $allPresensiInPeriod;

            $h = $filteredPresensi->where('status', 'Hadir')->count();
            $i = $filteredPresensi->where('status', 'Izin')->count();
            $s = $filteredPresensi->where('status', 'Sakit')->count();
            $t = $filteredPresensi->where('status', 'Tugas')->count();
            $a = $filteredPresensi->where('status', 'Alpha')->count();
            $totalSesi = $filteredPresensi->count();
            $persen = $totalSesi > 0 ? round((($h + $t) / $totalSesi) * 100, 1) : 0;

            $rekapUstadz = $daftarUstadzQuery->map(function ($u) use ($allPresensiInPeriod, $jadwalRuangan) {
                $pU = $allPresensiInPeriod->where('ustadz_id', $u->id);
                $uTotal = $pU->count();
                $uH = $pU->where('status', 'Hadir')->count();
                $uT = $pU->where('status', 'Tugas')->count();
                $uI = $pU->where('status', 'Izin')->count();
                $uS = $pU->where('status', 'Sakit')->count();
                $uA = $pU->where('status', 'Alpha')->count();
                $uPersen = $uTotal > 0 ? round((($uH + $uT) / $uTotal) * 100, 1) : 0;

                $mapelList = $jadwalRuangan->where('ustadz_id', $u->id)
                    ->pluck('mataPelajaran.nama_mapel')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                return [
                    'ustadz_id' => $u->id,
                    'nama' => $u->nama_lengkap,
                    'niup' => $u->niup ?? '-',
                    'foto' => $u->foto ? asset('storage/' . $u->foto) : null,
                    'mapel_list' => $mapelList,
                    'total_sesi' => $uTotal,
                    'total_hadir' => $uH,
                    'total_tugas' => $uT,
                    'total_izin' => $uI,
                    'total_sakit' => $uS,
                    'total_alpha' => $uA,
                    'persentase_kehadiran' => $uPersen,
                ];
            })->values();

            $riwayat = $filteredPresensi->map(function ($p) use ($ruangan) {
                $hariTgl = null;
                try {
                    $hariTgl = Carbon::parse($p->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
                } catch (\Exception $e) {
                }

                return [
                    'id' => $p->id,
                    'ustadz_id' => $p->ustadz_id,
                    'nama_ustadz' => $p->ustadz->nama_lengkap ?? ($p->ustadz->nama ?? 'Ustadz'),
                    'niup_ustadz' => $p->ustadz->niup ?? '-',
                    'foto_ustadz' => $p->ustadz?->foto ? asset('storage/' . $p->ustadz->foto) : null,
                    'tanggal' => (string) $p->tanggal,
                    'hari_tanggal' => $hariTgl,
                    'status' => $p->status,
                    'jam_masuk' => $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-',
                    'jam_keluar' => $p->jam_keluar ? substr($p->jam_keluar, 0, 5) : null,
                    'mapel' => $p->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-',
                    'nama_ruangan' => $p->jadwalPelajaran->ruangan->nama_ruangan ?? $ruangan->nama_ruangan,
                    'keterangan' => $p->keterangan ?? '-',
                    'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
                ];
            })->values();

            $daftarUstadz = $daftarUstadzQuery->map(fn($u) => [
                'id' => $u->id,
                'nama' => $u->nama_lengkap,
                'niup' => $u->niup ?? '-',
                'foto' => $u->foto ? asset('storage/' . $u->foto) : null,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'ruangan_id' => $ruangan->id,
                    'nama_ruangan' => $ruangan->nama_ruangan,
                    'level_nama' => $ruangan->level->nama_level ?? '-',
                    'is_wali_ruangan' => true,
                    'selected_semester_id' => $selectedSemesterId,
                    'selected_ustadz_id' => $selectedUstadzId,
                    'semester_list' => $semesterList->map(fn($s) => [
                        'id' => $s->id,
                        'nama_semester' => $s->nama_semester,
                        'is_active' => (bool) $s->is_active,
                    ]),
                    'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                        'id' => $r->id,
                        'nama_ruangan' => $r->nama_ruangan,
                        'level_nama' => $r->level->nama_level ?? '-',
                    ]),
                    'daftar_ustadz' => $daftarUstadz,
                    'tahun_pelajaran' => $tahunAktif->nama_lengkap ?? ($tahunAktif->nama_masehi ?? 'Tahun Aktif'),
                    'total_sesi' => $totalSesi,
                    'total_hadir' => $h,
                    'total_tugas' => $t,
                    'total_izin' => $i,
                    'total_sakit' => $s,
                    'total_alpha' => $a,
                    'persentase_kehadiran' => $persen,
                    'bulan_hijriyah_list' => $bulanList->map(function ($b) use ($semesterList) {
                        $matchingSem = $semesterList->first(function ($s) use ($b) {
                            if ($s->tanggal_mulai && $s->tanggal_selesai && $b->tanggal_mulai_masehi && $b->tanggal_selesai_masehi) {
                                return $b->tanggal_selesai_masehi >= $s->tanggal_mulai && $b->tanggal_mulai_masehi <= $s->tanggal_selesai;
                            }
                            return false;
                        });
                        if (!$matchingSem && $semesterList->count() >= 2) {
                            $matchingSem = $b->urutan <= 5 ? $semesterList[0] : $semesterList[1];
                        }

                        return [
                            'id' => $b->id,
                            'nama_bulan' => $b->nama_bulan,
                            'tahun_hijriyah' => $b->tahun_hijriyah,
                            'urutan' => $b->urutan,
                            'semester_id' => $matchingSem ? $matchingSem->id : ($b->semester_id ?? null),
                            'semester_nama' => $matchingSem ? $matchingSem->nama_semester : $b->semester,
                        ];
                    }),
                    'rekap_ustadz' => $rekapUstadz,
                    'riwayat' => $riwayat,
                ]
            ], 200);
        }
    }

    // =========================================================================
    // 3. LAPORAN PELANGGARAN MURID (BUKU KASUS KELAS)
    // =========================================================================
    public function getLaporanPelanggaranMurid(Request $request)
    {
        [$tahunAktif, $tahunId, $accessibleRuangans, $ruangan] = $this->getContextRuangans($request);

        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');
        $muridIds = $murids->pluck('id');

        $pelanggaranQuery = PelanggaranMurid::with(['murid', 'referensiPelanggaran', 'penginput'])
            ->where(function ($q) use ($ruangan, $muridIds) {
                $q->where('ruangan_id', $ruangan->id)
                    ->orWhereIn('murid_id', $muridIds);
            })
            ->where('tahun_pelajaran_id', $tahunId);

        if ($request->filled('bulan_hijriyah_id')) {
            $bulan = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)->find($request->bulan_hijriyah_id);
            if ($bulan && $bulan->tanggal_mulai_masehi && $bulan->tanggal_selesai_masehi) {
                $pelanggaranQuery->whereBetween('tanggal', [$bulan->tanggal_mulai_masehi, $bulan->tanggal_selesai_masehi]);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $pelanggaranQuery->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('kategori') && $request->kategori !== 'Semua') {
            $kategori = $request->kategori;
            $pelanggaranQuery->whereHas('referensiPelanggaran', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        $pelanggaranList = $pelanggaranQuery->orderBy('tanggal', 'desc')->get();

        $totalKasus = $pelanggaranList->count();
        $totalPoin = $pelanggaranList->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));
        $kasusSelesai = $totalKasus;
        $kasusDiproses = 0;

        // Breakdown per Kategori
        $kasusRingan = $pelanggaranList->where('referensiPelanggaran.kategori', 'Ringan')->count();
        $kasusSedang = $pelanggaranList->where('referensiPelanggaran.kategori', 'Sedang')->count();
        $kasusBerat = $pelanggaranList->where('referensiPelanggaran.kategori', 'Berat')->count();

        // Rekap Poin per Murid (Leaderboard Pelanggaran)
        $rekapMurid = [];
        foreach ($murids as $m) {
            $pMurid = $pelanggaranList->where('murid_id', $m->id);
            $poinMurid = $pMurid->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));
            $jmlKasus = $pMurid->count();

            $rekapMurid[] = [
                'murid_id' => $m->id,
                'nama' => $m->nama_lengkap ?? $m->nama,
                'nism' => $m->nism ?? '',
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'foto' => $m->foto ? asset('storage/' . $m->foto) : null,
                'wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'total_kasus' => $jmlKasus,
                'total_poin' => (float)round($poinMurid, 2),
                'status_kedisiplinan' => $poinMurid == 0 ? 'Disiplin' : ($poinMurid <= 10 ? 'Perhatian' : ($poinMurid <= 25 ? 'Peringatan' : 'Kritis')),
            ];
        }

        // Urutkan murid berdasarkan poin tertinggi
        usort($rekapMurid, fn($a, $b) => $b['total_poin'] <=> $a['total_poin']);

        $riwayatLog = $pelanggaranList->map(function ($p) {
            $hariTgl = null;
            try {
                $hariTgl = Carbon::parse($p->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
            } catch (\Exception $e) {
            }

            return [
                'id' => $p->id,
                'murid_id' => $p->murid_id,
                'nama_murid' => $p->murid->nama_lengkap ?? $p->murid->nama ?? '-',
                'nism' => $p->murid->nism ?? '-',
                'tanggal' => (string)$p->tanggal,
                'hari_tanggal' => $hariTgl,
                'nama_pelanggaran' => $p->referensiPelanggaran->nama_pelanggaran ?? '-',
                'kategori' => $p->referensiPelanggaran->kategori ?? 'Ringan',
                'poin' => (float)($p->referensiPelanggaran->poin ?? 0),
                'keterangan' => $p->keterangan ?? '-',
                'pencatat' => $p->penginput->name ?? 'Ustadz',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'ruangan_id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'tahun_pelajaran' => $tahunAktif->nama_lengkap ?? ($tahunAktif->nama_masehi ?? 'Tahun Aktif'),
                'total_murid' => $murids->count(),
                'total_kasus' => $totalKasus,
                'total_poin' => (float)round($totalPoin, 2),
                'kasus_selesai' => $kasusSelesai,
                'kasus_diproses' => $kasusDiproses,
                'kasus_ringan' => $kasusRingan,
                'kasus_sedang' => $kasusSedang,
                'kasus_berat' => $kasusBerat,
                'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_nama' => $r->level->nama_level ?? '-',
                ]),
                'rekap_murid' => $rekapMurid,
                'riwayat_log' => $riwayatLog,
            ]
        ], 200);
    }

    // =========================================================================
    // 4. LAPORAN NILAI & UJIAN MURID
    // =========================================================================
    public function getLaporanUjian(Request $request)
    {
        [$tahunAktif, $tahunId, $accessibleRuangans, $ruangan] = $this->getContextRuangans($request);

        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        $levelNama = $ruangan->level->nama_level ?? '';
        $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
        $allowedTipe = $isKelasAkhir ? ['IMDA 1', 'IMNI'] : ['IMDA 1', 'IMDA 2'];

        $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('tipe_ujian', $allowedTipe)
            ->orderBy('id', 'asc')
            ->get();

        if ($daftarUjian->isEmpty()) {
            $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunId)
                ->orderBy('id', 'asc')
                ->get();
        }

        $selectedUjianId = $request->ujian_id;
        $ujian = ($selectedUjianId ? $daftarUjian->firstWhere('id', $selectedUjianId) : null) ?? $daftarUjian->first();

        if (!$ujian) {
            return response()->json([
                'success' => false,
                'message' => 'Data Ujian tidak ditemukan pada tahun pelajaran ini.'
            ], 404);
        }

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');
        $muridIds = $murids->pluck('id');

        $nilaiList = NilaiUjian::with(['jadwalUjian.mataPelajaran', 'mataPelajaran'])
            ->where('ruangan_id', $ruangan->id)
            ->where('ujian_id', $ujian->id)
            ->get();

        $mapelList = $nilaiList->map(function ($n) {
            $mpl = $n->jadwalUjian?->mataPelajaran ?? $n->mataPelajaran;
            if ($mpl) {
                return [
                    'id' => $mpl->id,
                    'nama_mapel' => $mpl->nama_mapel,
                ];
            }
            if ($n->jadwalUjian?->nama_mata_pelajaran_custom) {
                return [
                    'id' => $n->jadwal_ujian_id,
                    'nama_mapel' => $n->jadwalUjian->nama_mata_pelajaran_custom,
                ];
            }
            return null;
        })->filter()->unique('id')->values();

        $rekapMurid = [];
        $semuaRataRata = [];

        foreach ($murids as $m) {
            $nMurid = $nilaiList->where('murid_id', $m->id);
            $totalNilai = $nMurid->sum('nilai');
            $mapelCount = $nMurid->count();
            $rata = $mapelCount > 0 ? round($totalNilai / $mapelCount, 2) : 0;
            $semuaRataRata[] = $rata;

            $mapelNilai = [];
            foreach ($mapelList as $mpl) {
                $item = $nMurid->first(function ($n) use ($mpl) {
                    return ($n->jadwalUjian?->mata_pelajaran_id == $mpl['id']) ||
                        ($n->mata_pelajaran_id == $mpl['id']) ||
                        ($n->jadwal_ujian_id == $mpl['id']);
                });
                $mapelNilai[] = [
                    'mapel_id' => $mpl['id'],
                    'nama_mapel' => $mpl['nama_mapel'],
                    'nilai' => $item ? (float)$item->nilai : 0,
                ];
            }

            $rekapMurid[] = [
                'murid_id' => $m->id,
                'nama' => $m->nama_lengkap ?? $m->nama,
                'nism' => $m->nism ?? '',
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'foto' => $m->foto ? asset('storage/' . $m->foto) : null,
                'wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'total_nilai' => (float)round($totalNilai, 2),
                'rata_rata' => $rata,
                'jumlah_mapel_diikuti' => $mapelCount,
                'status_tuntas' => $rata >= 60 ? 'Tuntas' : 'Belum Tuntas',
                'mapel_nilai' => $mapelNilai,
            ];
        }

        // Urutkan untuk menentukan Peringkat Bintang Pelajar
        usort($rekapMurid, fn($a, $b) => $b['rata_rata'] <=> $a['rata_rata']);
        foreach ($rekapMurid as $idx => &$item) {
            $item['ranking'] = $idx + 1;
        }
        unset($item);

        $rataKelas = count($semuaRataRata) > 0 ? round(array_sum($semuaRataRata) / count($semuaRataRata), 2) : 0;
        $nilaiTertinggi = count($semuaRataRata) > 0 ? max($semuaRataRata) : 0;
        $nilaiTerendah = count($semuaRataRata) > 0 ? min($semuaRataRata) : 0;
        $tuntasCount = count(array_filter($semuaRataRata, fn($r) => $r >= 60));
        $persenTuntas = count($semuaRataRata) > 0 ? round(($tuntasCount / count($semuaRataRata)) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'ruangan_id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'is_kelas_akhir' => $isKelasAkhir,
                'ujian' => [
                    'id' => $ujian->id,
                    'nama_ujian' => $ujian->nama_ujian,
                    'tipe_ujian' => $ujian->tipe_ujian,
                    'semester' => $ujian->semester ?? '-',
                ],
                'tahun_pelajaran' => $tahunAktif->nama_lengkap ?? ($tahunAktif->nama_masehi ?? 'Tahun Aktif'),
                'total_murid' => $murids->count(),
                'rata_rata_kelas' => $rataKelas,
                'nilai_tertinggi' => $nilaiTertinggi,
                'nilai_terendah' => $nilaiTerendah,
                'persentase_tuntas' => $persenTuntas,
                'jumlah_tuntas' => $tuntasCount,
                'jumlah_belum_tuntas' => count($semuaRataRata) - $tuntasCount,
                'daftar_ujian' => $daftarUjian->map(fn($u) => [
                    'id' => $u->id,
                    'nama_ujian' => $u->nama_ujian,
                    'tipe_ujian' => $u->tipe_ujian,
                ]),
                'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_nama' => $r->level->nama_level ?? '-',
                ]),
                'mapel_header' => $mapelList->map(fn($m) => [
                    'id' => $m['id'],
                    'nama_mapel' => $m['nama_mapel'],
                ]),
                'rekap_murid' => $rekapMurid,
            ]
        ], 200);
    }

    // =========================================================================
    // 5. LAPORAN KENAIKAN KELAS & KELULUSAN
    // =========================================================================
    public function getLaporanKenaikanKelas(Request $request)
    {
        [$tahunAktif, $tahunId, $accessibleRuangans, $ruangan] = $this->getContextRuangans($request);

        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        $levelNama = $ruangan->level->nama_level ?? '';
        $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

        $config = PengaturanAkademik::where('tahun_pelajaran_id', $tahunId)->first();
        $bobotUjian = ($config->bobot_imda ?? 60) / 100;
        $bobotHadir = ($config->bobot_presensi ?? 24) / 100;
        $bobotPelanggaran = ($config->bobot_pelanggaran ?? 16) / 100;
        $tarifAlpha = (float)($config->poin_alpha ?? 1.00);
        $tarifIzin = (float)($config->poin_izin ?? 0.16);

        $daftarLevel = Level::where('id', '>=', $ruangan->level_id ?? 1)->orderBy('id', 'asc')->get();
        $levelNaik = $daftarLevel->count() > 1 ? $daftarLevel[1] : $ruangan->level;

        // Ujian IDs
        $semuaUjianTahunIni = Ujian::where('tahun_pelajaran_id', $tahunId)->get();
        $idDauri1 = $semuaUjianTahunIni->where('tipe_ujian', 'IMDA 1')->pluck('id')->toArray();
        $idUjianSem2 = $isKelasAkhir
            ? $semuaUjianTahunIni->where('tipe_ujian', 'IMNI')->pluck('id')->toArray()
            : $semuaUjianTahunIni->where('tipe_ujian', 'IMDA 2')->pluck('id')->toArray();

        $semuaBulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)->get();

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');
        $muridIds = $murids->pluck('id');

        $semuaPresensi = PresensiMurid::whereIn('murid_id', $muridIds)->get();
        $semuaNilai = NilaiUjian::whereIn('murid_id', $muridIds)->where('ruangan_id', $ruangan->id)->get();
        $semuaPelanggaran = PelanggaranMurid::with('referensiPelanggaran')
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('ruangan_id', $ruangan->id)
            ->get();

        $riwayatExisting = RiwayatKenaikan::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('murid_id', $muridIds)
            ->get()
            ->keyBy('murid_id');

        $dataMurid = [];
        $countNaik = 0;
        $countTinggal = 0;
        $countLulus = 0;

        foreach ($murids as $murid) {
            $nilaiMurid = $semuaNilai->where('murid_id', $murid->id);
            $pelanggaranMuridIni = $semuaPelanggaran->where('murid_id', $murid->id);
            $presensiMuridIni = $semuaPresensi->where('murid_id', $murid->id);

            // Semester 1
            $rataUjian1 = $nilaiMurid->whereIn('ujian_id', $idDauri1)->avg('nilai') ?? 0;
            $presensiSem1 = $presensiMuridIni->filter(function ($p) use ($semuaBulanHijriyah) {
                $bulan = $semuaBulanHijriyah->first(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi);
                return $bulan && in_array((string)$bulan->semester, ['1', 'Ganjil', 'Semester 1']);
            });
            $poinKehadiran1 = ($presensiSem1->where('status', 'Alpha')->count() * $tarifAlpha) + ($presensiSem1->where('status', 'Izin')->count() * $tarifIzin);
            $poinPelanggaran1 = $pelanggaranMuridIni->filter(function ($p) use ($semuaBulanHijriyah) {
                $bulan = $semuaBulanHijriyah->first(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi);
                return $bulan && in_array((string)$bulan->semester, ['1', 'Ganjil', 'Semester 1']);
            })->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));

            $nilaiHadir1 = max(0, ((15 - $poinKehadiran1) / 15) * 100);
            $nilaiPelanggaran1 = max(0, ((30 - $poinPelanggaran1) / 30) * 100);
            $skorSem1 = ($rataUjian1 * $bobotUjian) + ($nilaiHadir1 * $bobotHadir) + ($nilaiPelanggaran1 * $bobotPelanggaran);

            // Semester 2
            $rataUjian2 = $nilaiMurid->whereIn('ujian_id', $idUjianSem2)->avg('nilai') ?? 0;
            $presensiSem2 = $presensiMuridIni->filter(function ($p) use ($semuaBulanHijriyah) {
                $bulan = $semuaBulanHijriyah->first(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi);
                return $bulan && in_array((string)$bulan->semester, ['2', 'Genap', 'Semester 2']);
            });
            $poinKehadiran2 = ($presensiSem2->where('status', 'Alpha')->count() * $tarifAlpha) + ($presensiSem2->where('status', 'Izin')->count() * $tarifIzin);
            $poinPelanggaran2 = $pelanggaranMuridIni->filter(function ($p) use ($semuaBulanHijriyah) {
                $bulan = $semuaBulanHijriyah->first(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi);
                return $bulan && in_array((string)$bulan->semester, ['2', 'Genap', 'Semester 2']);
            })->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));

            $nilaiHadir2 = max(0, ((15 - $poinKehadiran2) / 15) * 100);
            $nilaiPelanggaran2 = max(0, ((30 - $poinPelanggaran2) / 30) * 100);
            $skorSem2 = ($rataUjian2 * $bobotUjian) + ($nilaiHadir2 * $bobotHadir) + ($nilaiPelanggaran2 * $bobotPelanggaran);

            // Akumulasi Final
            $nilaiAkhir = round(($skorSem1 + $skorSem2) / 2, 2);
            $rekomendasi = 'Tinggal Kelas';
            if ($nilaiAkhir > 55) {
                $rekomendasi = $isKelasAkhir ? 'Lulus' : 'Naik Kelas';
            }

            $riwayat = $riwayatExisting->get($murid->id);
            $keputusanFinal = $riwayat ? $riwayat->status_keputusan : $rekomendasi;

            if ($keputusanFinal === 'Naik Kelas') {
                $countNaik++;
            } elseif ($keputusanFinal === 'Lulus') {
                $countLulus++;
            } else {
                $countTinggal++;
            }

            $dataMurid[] = [
                'murid_id' => $murid->id,
                'nama' => $murid->nama_lengkap ?? $murid->nama,
                'nism' => $murid->nism ?? '',
                'jenis_kelamin' => $murid->jenis_kelamin ?? 'L',
                'foto' => $murid->foto ? asset('storage/' . $murid->foto) : null,
                'wali' => $murid->nama_ayah ?? $murid->waliMurid->nama_kepala_keluarga ?? '-',
                'skor_sem1' => round($skorSem1, 2),
                'skor_sem2' => round($skorSem2, 2),
                'nilai_akumulasi' => $nilaiAkhir,
                'rekomendasi' => $rekomendasi,
                'keputusan_final' => $keputusanFinal,
                'level_tujuan_nama' => $keputusanFinal === 'Naik Kelas' ? ($levelNaik->nama_level ?? '-') : ($keputusanFinal === 'Lulus' ? 'Alumni (Lulus)' : ($ruangan->level->nama_level ?? '-')),
                'catatan' => $riwayat ? ($riwayat->catatan_wali_kelas ?? '') : '',
                'sudah_dikunci' => (bool)$riwayat,
                'detail_perhitungan' => [
                    'bobot_ujian' => (int)($bobotUjian * 100),
                    'bobot_presensi' => (int)($bobotHadir * 100),
                    'bobot_pelanggaran' => (int)($bobotPelanggaran * 100),
                    'rata_ujian_sem1' => round($rataUjian1, 2),
                    'rata_ujian_sem2' => round($rataUjian2, 2),
                    'poin_pelanggaran_sem1' => round($poinPelanggaran1, 2),
                    'poin_pelanggaran_sem2' => round($poinPelanggaran2, 2),
                ]
            ];
        }

        // Urutkan nilai akumulasi tertinggi
        usort($dataMurid, fn($a, $b) => $b['nilai_akumulasi'] <=> $a['nilai_akumulasi']);

        return response()->json([
            'success' => true,
            'data' => [
                'ruangan_id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'is_kelas_akhir' => $isKelasAkhir,
                'tahun_pelajaran' => $tahunAktif->nama_lengkap ?? ($tahunAktif->nama_masehi ?? 'Tahun Aktif'),
                'total_murid' => $murids->count(),
                'total_naik_kelas' => $countNaik,
                'total_lulus' => $countLulus,
                'total_tinggal_kelas' => $countTinggal,
                'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_nama' => $r->level->nama_level ?? '-',
                ]),
                'bobot_konfigurasi' => [
                    'bobot_ujian' => (int)($bobotUjian * 100),
                    'bobot_presensi' => (int)($bobotHadir * 100),
                    'bobot_pelanggaran' => (int)($bobotPelanggaran * 100),
                ],
                'data_kenaikan' => $dataMurid,
            ]
        ], 200);
    }
}
