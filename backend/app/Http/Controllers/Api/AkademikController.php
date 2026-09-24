<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\KalendarPendidikan;
use App\Models\Level;
use App\Models\MataPelajaran;
use App\Models\ReferensiPelanggaran;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AkademikController extends Controller
{
    /**
     * Ambil data Kalender Pendidikan (Agenda Kegiatan, Ujian, Hari Libur, dan Bulan Hijriyah)
     */
    public function getKalendar(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $request->tahun_id ?? ($tahunAktif->id ?? null);

        if (!$tahunId) {
            $firstTahun = TahunPelajaran::first();
            $tahunId = $firstTahun ? $firstTahun->id : null;
        }

        $events = [];

        if ($tahunId) {
            // 1. Kegiatan Kalender Pendidikan
            $kegiatans = KalendarPendidikan::with(['kategoriKegiatan'])
                ->where('tahun_pelajaran_id', $tahunId)
                ->orderBy('tanggal_mulai', 'asc')
                ->get();

            foreach ($kegiatans as $keg) {
                $hex = $keg->kategoriKegiatan->kode_warna ?? '#10b981';
                $startStr = $keg->tanggal_mulai ? \Carbon\Carbon::parse($keg->tanggal_mulai)->format('Y-m-d') : '';
                $endStr = $keg->tanggal_selesai ? \Carbon\Carbon::parse($keg->tanggal_selesai)->format('Y-m-d') : $startStr;
                $events[] = [
                    'id'          => 'kegiatan_' . $keg->id,
                    'title'       => $keg->nama_kegiatan,
                    'start'       => $startStr,
                    'end'         => $endStr,
                    'kategori'    => $keg->kategoriKegiatan->nama_kategori ?? 'Kegiatan',
                    'tipe'        => 'kegiatan',
                    'hex_color'   => $hex,
                ];
            }

            // 2. Hari Libur
            $liburs = HariLibur::where('tahun_pelajaran_id', $tahunId)
                ->orWhereNull('tahun_pelajaran_id')
                ->get();

            foreach ($liburs as $libur) {
                $startStr = $libur->tanggal_mulai ? \Carbon\Carbon::parse($libur->tanggal_mulai)->format('Y-m-d') : '';
                $endStr = $libur->tanggal_selesai ? \Carbon\Carbon::parse($libur->tanggal_selesai)->format('Y-m-d') : $startStr;
                $events[] = [
                    'id'          => 'libur_' . $libur->id,
                    'title'       => 'Libur: ' . $libur->keterangan,
                    'start'       => $startStr,
                    'end'         => $endStr,
                    'kategori'    => 'Hari Libur',
                    'tipe'        => 'libur',
                    'hex_color'   => '#f43f5e',
                ];
            }

            // 3. Ujian Madrasah
            $ujians = Ujian::where('tahun_pelajaran_id', $tahunId)
                ->whereNotNull('tanggal_mulai')
                ->get();

            foreach ($ujians as $ujian) {
                $startStr = $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai)->format('Y-m-d') : '';
                $endStr = $ujian->tanggal_selesai ? \Carbon\Carbon::parse($ujian->tanggal_selesai)->format('Y-m-d') : $startStr;
                $events[] = [
                    'id'          => 'ujian_' . $ujian->id,
                    'title'       => 'Ujian: ' . $ujian->nama_ujian,
                    'start'       => $startStr,
                    'end'         => $endStr,
                    'kategori'    => 'Akademik / Ujian',
                    'tipe'        => 'ujian',
                    'hex_color'   => '#f59e0b',
                ];
            }

            usort($events, function ($a, $b) {
                return strtotime($a['start']) - strtotime($b['start']);
            });
        }

        // 4. Data Bulan Hijriyah
        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->get()
            ->map(function ($bh) {
                return [
                    'id' => $bh->id,
                    'nama_bulan' => $bh->nama_bulan,
                    'tahun_hijriyah' => $bh->tahun_hijriyah,
                    'urutan' => $bh->urutan,
                    'tanggal_mulai' => $bh->tanggal_mulai_masehi,
                    'tanggal_selesai' => $bh->tanggal_selesai_masehi,
                    'is_active' => (bool)$bh->is_active,
                ];
            });

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get(['id', 'nama_hijriyah', 'nama_masehi', 'is_active']);

        return response()->json([
            'success' => true,
            'data' => [
                'tahun_aktif_id' => $tahunId,
                'daftar_tahun' => $daftarTahun,
                'bulan_hijriyah' => $bulanHijriyah,
                'events' => $events,
            ]
        ], 200);
    }

    /**
     * Ambil Master Referensi Pelanggaran Murid
     */
    public function getReferensiPelanggaran(Request $request)
    {
        $query = ReferensiPelanggaran::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $query->where('nama_pelanggaran', 'like', '%' . $request->search . '%');
        }

        $pelanggaran = $query->orderBy('id')->orderBy('poin')->get();

        $kategoriSummary = [
            'total' => ReferensiPelanggaran::count(),
            'ringan' => ReferensiPelanggaran::where('kategori', 'Ringan')->count(),
            'sedang' => ReferensiPelanggaran::where('kategori', 'Sedang')->count(),
            'berat' => ReferensiPelanggaran::where('kategori', 'Berat')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $kategoriSummary,
                'list' => $pelanggaran->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'nama_pelanggaran' => $p->nama_pelanggaran,
                        'kategori' => $p->kategori,
                        'poin' => (float)$p->poin,
                    ];
                })
            ]
        ], 200);
    }

    /**
     * Ambil Master Mata Pelajaran (Katalog Kurikulum Berdasarkan Level/Kelas)
     */
    public function getMataPelajaran(Request $request)
    {
        $levels = Level::orderBy('id')->get(['id', 'nama_level']);

        $query = MataPelajaran::with('level')->where('is_active', true);

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_mapel', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_mapel', 'like', '%' . $request->search . '%')
                    ->orWhere('referensi', 'like', '%' . $request->search . '%');
            });
        }

        $mapels = $query->orderBy('level_id')->orderBy('nama_mapel')->get();

        $data = $mapels->map(function ($m) {
            return [
                'id' => $m->id,
                'level_id' => $m->level_id,
                'level_nama' => $m->level->nama_level ?? '-',
                'kode_mapel' => $m->kode_mapel,
                'nama_mapel' => $m->nama_mapel,
                'kelompok' => $m->kelompok ?? 'Wajib',
                'referensi' => $m->referensi,
                'pengarang' => $m->pengarang,
                'penerbit' => $m->penerbit,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'levels' => $levels,
                'mata_pelajaran' => $data,
            ]
        ], 200);
    }

    /**
     * Ambil Jadwal Mengajar Mingguan Ustadz yang Login & Jadwal Ruangan (jika Wali Ruangan)
     */
    public function getJadwalPelajaran(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil Ustadz.',
                'data' => []
            ], 403);
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        // 1. Jadwal Mengajar Pribadi Ustadz (Mendukung Multi-Pengampu / Team Teaching)
        $jadwals = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ustadzs', 'ruangan.level', 'ruangan.gedung'])
            ->forUstadz($ustadzId)
            ->get();

        $hariOrder = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Sabtu'];
        $grouped = [];

        foreach ($hariOrder as $hari) {
            $hariJadwals = $jadwals->where('hari', $hari)->sortBy('jam_ke')->values();

            $grouped[] = [
                'hari' => $hari,
                'total_sesi' => $hariJadwals->count(),
                'sesi' => $hariJadwals->map(function ($j) {
                    $jamText = match ($j->jam_ke) {
                        'Nadzoman' => '13:45 - 14:00 WIB',
                        '1' => '14:00 - 14:45 WIB',
                        '2' => '15:30 - 16:15 WIB',
                        'Ekstra' => '20:00 - 21:00 WIB',
                        default => 'Jam Ke-' . $j->jam_ke,
                    };

                    return [
                        'id'            => $j->id,
                        'jam_ke'        => $j->jam_ke,
                        'jam'           => $jamText,
                        'mapel'         => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                        'ustadz'        => $j->daftar_nama_pengampu,
                        'kode_ustadz'   => $j->ustadz->kode_ustadz ?? '-',
                        'ustadz_foto'   => $j->ustadz && $j->ustadz->foto ? asset('storage/' . $j->ustadz->foto) : null,
                        'daftar_ustadz' => $j->daftar_ustadz->map(function ($u) {
                            return [
                                'id'       => $u->id,
                                'nama'     => $u->nama_lengkap,
                                'kode'     => $u->kode_ustadz ?? '-',
                                'foto'     => $u->foto ? asset('storage/' . $u->foto) : null,
                                'is_utama' => (bool) ($u->pivot->is_utama ?? false),
                                'urutan'   => (int) ($u->pivot->urutan ?? 1),
                            ];
                        })->values(),
                        'ruangan'       => $j->ruangan->nama_ruangan ?? '-',
                        'nama_gedung'   => $j->ruangan->gedung->nama_gedung ?? null,
                        'nama_kamar'    => $j->ruangan->nama_kamar ?? null,
                        'level'         => $j->ruangan->level->nama_level ?? '-',
                    ];
                })
            ];
        }

        // 2. Deteksi Apakah Ustadz adalah Wali Ruangan
        $isWaliRuangan = false;
        $ruanganWaliNama = null;
        $ruanganWaliId = null;
        $levelWaliNama = null;
        $totalJadwalRuanganMingguan = 0;
        $jadwalRuanganGrouped = [];

        if ($tahunAktif) {
            $ruanganWali = Ruangan::with(['level', 'gedung'])
                ->where('tahun_pelajaran_id', $tahunAktif->id)
                ->where('ustadz_id', $ustadz->id)
                ->first();

            if ($ruanganWali) {
                $isWaliRuangan = true;
                $ruanganWaliNama = $ruanganWali->nama_ruangan;
                $ruanganWaliId = $ruanganWali->id;
                $levelWaliNama = $ruanganWali->level->nama_level ?? '-';

                $jadwalRuangan = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ustadzs', 'ruangan.level', 'ruangan.gedung'])
                    ->where('ruangan_id', $ruanganWali->id)
                    ->get();

                $totalJadwalRuanganMingguan = $jadwalRuangan->count();

                foreach ($hariOrder as $hari) {
                    $hariJadwals = $jadwalRuangan->where('hari', $hari)->sortBy('jam_ke')->values();

                    $jadwalRuanganGrouped[] = [
                        'hari' => $hari,
                        'total_sesi' => $hariJadwals->count(),
                        'sesi' => $hariJadwals->map(function ($j) {
                            $jamText = match ($j->jam_ke) {
                                'Nadzoman' => '13:45 - 14:00 WIB',
                                '1' => '14:00 - 14:45 WIB',
                                '2' => '15:30 - 16:15 WIB',
                                'Ekstra' => '20:00 - 21:00 WIB',
                                default => 'Jam Ke-' . $j->jam_ke,
                            };

                            return [
                                'id'            => $j->id,
                                'jam_ke'        => $j->jam_ke,
                                'jam'           => $jamText,
                                'mapel'         => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                                'ustadz'        => $j->daftar_nama_pengampu,
                                'kode_ustadz'   => $j->ustadz->kode_ustadz ?? '-',
                                'ustadz_foto'   => $j->ustadz && $j->ustadz->foto ? asset('storage/' . $j->ustadz->foto) : null,
                                'daftar_ustadz' => $j->daftar_ustadz->map(function ($u) {
                                    return [
                                        'id'       => $u->id,
                                        'nama'     => $u->nama_lengkap,
                                        'kode'     => $u->kode_ustadz ?? '-',
                                        'foto'     => $u->foto ? asset('storage/' . $u->foto) : null,
                                        'is_utama' => (bool) ($u->pivot->is_utama ?? false),
                                        'urutan'   => (int) ($u->pivot->urutan ?? 1),
                                    ];
                                })->values(),
                                'ruangan'       => $j->ruangan->nama_ruangan ?? '-',
                                'nama_gedung'   => $j->ruangan->gedung->nama_gedung ?? null,
                                'nama_kamar'    => $j->ruangan->nama_kamar ?? null,
                                'level'         => $j->ruangan->level->nama_level ?? '-',
                            ];
                        })
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ustadz_nama'                   => $ustadz->nama_lengkap,
                'total_jadwal_mingguan'         => $jadwals->count(),
                'jadwal_per_hari'               => $grouped,
                'is_wali_ruangan'               => $isWaliRuangan,
                'ruangan_wali_id'               => $ruanganWaliId,
                'ruangan_wali_nama'             => $ruanganWaliNama,
                'level_wali_nama'               => $levelWaliNama,
                'total_jadwal_ruangan_mingguan' => $totalJadwalRuanganMingguan,
                'jadwal_ruangan_per_hari'       => $jadwalRuanganGrouped,
            ]
        ], 200);
    }

    /**
     * Ambil Jadwal Ujian Madrasah dengan filter Tipe Ujian (IMDA 1, IMDA 2, IMNI)
     * Algoritma:
     * - Jika login sebagai Wali Ruangan: tampilkan jadwal ujian level/kelas binaannya.
    /**
     * Jadwal Ujian Madrasah Mobile (Pilih Ruangan Kelas & Agenda Ujian)
     * GET /api/jadwal-ujian
     */
    public function getJadwalUjian(Request $request)
    {
        $user = $request->user();
        $ustadz = $user ? $user->ustadz : null;
        $ustadzId = $ustadz->id ?? null;

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $request->tahun_id ?? ($tahunAktif->id ?? null);

        if (!$tahunId) {
            $firstTahun = TahunPelajaran::orderBy('id', 'desc')->first();
            $tahunId = $firstTahun ? $firstTahun->id : null;
        }

        // 1. Daftar Ruangan yang diampu Ustadz Login (Wali Ruangan & Guru Pengampu KBM)
        $accessibleRuanganIds = [];
        if ($ustadzId) {
            $ruanganWaliIds = Ruangan::where('ustadz_id', $ustadzId)
                ->where('tahun_pelajaran_id', $tahunId)
                ->pluck('id')
                ->toArray();

            $ruanganMengajarIds = JadwalPelajaran::forUstadz($ustadzId)
                ->whereHas('ruangan', fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
                ->pluck('ruangan_id')
                ->toArray();

            $accessibleRuanganIds = array_values(array_unique(array_merge($ruanganWaliIds, $ruanganMengajarIds)));
        }

        if (empty($accessibleRuanganIds)) {
            $accessibleRuanganIds = Ruangan::where('tahun_pelajaran_id', $tahunId)->pluck('id')->toArray();
        }

        $daftarRuangan = Ruangan::whereIn('id', $accessibleRuanganIds)
            ->with(['level', 'waliRuangan'])
            ->orderBy('level_id', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_id' => $r->level_id,
                    'nama_level' => $r->level->nama_level ?? '-',
                    'wali_ruangan_nama' => $r->waliRuangan?->nama_lengkap ?? '-',
                ];
            });

        // Deteksi Ruangan Binaan Ustadz Login
        $ruanganWali = null;
        if ($ustadzId && $tahunId) {
            $ruanganWali = Ruangan::with('level')
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('ustadz_id', $ustadzId)
                ->first();
        }

        // Cek apakah memilih mode "Semua Tugas Saya" (Lintas Ruangan / ruangan_id = 0)
        $isAllTasksMode = false;
        if ($request->has('ruangan_id') && ($request->ruangan_id === 0 || $request->ruangan_id === '0' || $request->ruangan_id === 'tugas_saya')) {
            $isAllTasksMode = true;
            $selectedRuanganId = 0;
            $selectedRuanganNama = 'Semua Tugas Mengawas Saya';
            $namaLevel = 'Lintas Ruangan';
            $isWaliRuangan = false;
            $waliRuanganNama = '-';
            $ruangan = null;
        } else {
            $selectedRuanganId = $request->ruangan_id
                ? (int) $request->ruangan_id
                : ($ruanganWali ? $ruanganWali->id : ($daftarRuangan->first()['id'] ?? null));

            if ($selectedRuanganId !== 0 && !$daftarRuangan->contains('id', $selectedRuanganId)) {
                $selectedRuanganId = $daftarRuangan->first()['id'] ?? null;
            }

            if ($selectedRuanganId === 0) {
                $isAllTasksMode = true;
                $selectedRuanganNama = 'Semua Tugas Mengawas Saya';
                $namaLevel = 'Lintas Ruangan';
                $isWaliRuangan = false;
                $waliRuanganNama = '-';
                $ruangan = null;
            } else {
                $ruangan = null;
                if ($selectedRuanganId) {
                    $ruangan = Ruangan::with(['level', 'waliRuangan'])->find($selectedRuanganId);
                }
                $selectedRuanganNama = $ruangan?->nama_ruangan ?? '';
                $namaLevel = $ruangan?->level?->nama_level ?? '';
                $isWaliRuangan = ($ustadzId && $ruangan && $ruangan->ustadz_id == $ustadzId);
                $waliRuanganNama = $ruangan?->waliRuangan?->nama_lengkap ?? '-';
            }
        }

        // Tambahkan opsi "Semua Tugas Mengawas Saya" ke daftar ruangan untuk dropdown
        $daftarRuanganList = collect([
            [
                'id' => 0,
                'nama_ruangan' => '⭐ Semua Tugas Mengawas Saya',
                'level_id' => 0,
                'nama_level' => 'Semua Ruangan',
                'wali_ruangan_nama' => '-',
            ]
        ])->concat($daftarRuangan)->values();

        // 2. Daftar Agenda Ujian berdasarkan Level Ruangan (Sesuai Aturan Madrasah)
        // Jika mode Lintas Ruangan (Semua Tugas): tampilkan seluruh agenda ujian di tahun aktif
        // Kelas Akhir (3 TPQ, 6 IBT, 3 TSA) -> IMDA 1 & IMNI
        // Kelas Reguler (1-2 TPQ, 1-5 IBT, 1-2 TSA) -> IMDA 1 & IMDA 2
        $queryUjian = \App\Models\Ujian\Ujian::with('semester')->where('tahun_pelajaran_id', $tahunId);

        if (!$isAllTasksMode && $ruangan && $ruangan->level) {
            $levelNama = $ruangan->level->nama_level ?? '';
            $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

            if ($isKelasAkhir) {
                $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
            } else {
                $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
            }
        }

        $daftarUjian = $queryUjian->orderBy('id', 'asc')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'nama_ujian' => $u->nama_ujian,
                    'tipe_ujian' => $u->tipe_ujian ?? $u->jenis_ujian ?? 'IMDA 1',
                    'semester' => $u->semester->nama_semester ?? ($u->semester_id == 9 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)'),
                    'tanggal_mulai' => $u->tanggal_mulai ? (is_string($u->tanggal_mulai) ? $u->tanggal_mulai : $u->tanggal_mulai->format('Y-m-d')) : null,
                    'tanggal_selesai' => $u->tanggal_selesai ? (is_string($u->tanggal_selesai) ? $u->tanggal_selesai : $u->tanggal_selesai->format('Y-m-d')) : null,
                ];
            });

        $selectedUjianId = $request->ujian_id ? (int) $request->ujian_id : ($daftarUjian->first()['id'] ?? null);
        if ($selectedUjianId !== null && !$daftarUjian->contains('id', $selectedUjianId)) {
            $selectedUjianId = $daftarUjian->first()['id'] ?? null;
        }

        // 3. Pre-fetch Data Pemetaan Pengawas Default untuk mencegah N+1 query
        // 3.1 Mapping Semua Ruangan di Tahun Aktif
        $allRuangans = Ruangan::with(['waliRuangan', 'level'])
            ->where('tahun_pelajaran_id', $tahunId)
            ->get();
        $ruangansByLevel = $allRuangans->groupBy('level_id');

        // 3.2 Mapping Guru Pengampu KBM per "mata_pelajaran_id_ruangan_id" & per "mata_pelajaran_id_level_id"
        $jadwalKbmList = JadwalPelajaran::with(['ustadz', 'ustadzs', 'ruangan'])
            ->whereHas('ruangan', fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->get();

        $guruMapelRuanganMap = [];
        $guruMapelLevelMap = [];
        $guruMapelGeneralMap = [];
        foreach ($jadwalKbmList as $jk) {
            if ($jk->mata_pelajaran_id) {
                $pengampus = $jk->daftar_ustadz;
                if ($pengampus->isNotEmpty()) {
                    // Key ruangan: mapel_id + ruangan_id (paling akurat)
                    $rKey = $jk->mata_pelajaran_id . '_' . $jk->ruangan_id;
                    if (!isset($guruMapelRuanganMap[$rKey])) {
                        $guruMapelRuanganMap[$rKey] = collect();
                    }
                    $guruMapelRuanganMap[$rKey] = $guruMapelRuanganMap[$rKey]->concat($pengampus)->unique('id')->values();

                    // Key level: mapel_id + level_id (fallback)
                    if ($jk->ruangan && $jk->ruangan->level_id) {
                        $lKey = $jk->mata_pelajaran_id . '_' . $jk->ruangan->level_id;
                        if (!isset($guruMapelLevelMap[$lKey])) {
                            $guruMapelLevelMap[$lKey] = collect();
                        }
                        $guruMapelLevelMap[$lKey] = $guruMapelLevelMap[$lKey]->concat($pengampus)->unique('id')->values();
                    }

                    if (!isset($guruMapelGeneralMap[$jk->mata_pelajaran_id])) {
                        $guruMapelGeneralMap[$jk->mata_pelajaran_id] = collect();
                    }
                    $guruMapelGeneralMap[$jk->mata_pelajaran_id] = $guruMapelGeneralMap[$jk->mata_pelajaran_id]->concat($pengampus)->unique('id')->values();
                }
            }
        }

        // 4. Query Jadwal Ujian
        $jadwalQuery = \App\Models\Ujian\JadwalUjian::with([
            'ujian.semester',
            'mataPelajaran',
            'level',
            'pengawas',
        ]);

        if ($selectedUjianId) {
            $jadwalQuery->where('ujian_id', $selectedUjianId);
        } else {
            $jadwalQuery->whereHas('ujian', fn($q) => $q->where('tahun_pelajaran_id', $tahunId));
        }

        if (!$isAllTasksMode && $ruangan && $ruangan->level_id) {
            $jadwalQuery->where('level_id', $ruangan->level_id);
        }

        $jadwalList = $jadwalQuery
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 5. Kelompokkan jadwal per tanggal ujian & resolusi pengawas default
        $groupedPerTanggal = [];
        $totalJadwalSaya = 0;
        $totalJadwalSemua = 0;

        foreach ($jadwalList as $j) {
            $tanggalRaw = $j->getRawOriginal('tanggal_ujian');
            $tanggalKey = $tanggalRaw ? Carbon::parse($tanggalRaw)->format('Y-m-d') : 'Tanpa Tanggal';

            if (!isset($groupedPerTanggal[$tanggalKey])) {
                $carbonDate = $tanggalRaw ? Carbon::parse($tanggalRaw)->locale('id') : null;
                $groupedPerTanggal[$tanggalKey] = [
                    'tanggal' => $tanggalKey,
                    'hari_tanggal' => $carbonDate ? $carbonDate->isoFormat('dddd, D MMMM YYYY') : 'Tanggal Belum Ditentukan',
                    'hari_tanggal_singkat' => $carbonDate ? $carbonDate->isoFormat('dddd, DD MMM YYYY') : 'Belum Ditentukan',
                    'total_sesi' => 0,
                    'sesi' => [],
                ];
            }

            $waktuMulaiStr = $j->jam_mulai_format;
            $waktuSelesaiStr = $j->jam_selesai_format;
            $jamText = ($waktuMulaiStr && $waktuSelesaiStr)
                ? "{$waktuMulaiStr} - {$waktuSelesaiStr} WIB"
                : ($waktuMulaiStr ? "{$waktuMulaiStr} WIB" : 'Waktu Belum Diatur');

            $isCustomMapel = !empty($j->nama_mata_pelajaran_custom) || empty($j->mata_pelajaran_id);

            // Mode "Semua Tugas Saya" (Lintas Ruangan): Iterasi setiap ruangan di level tsb
            if ($isAllTasksMode) {
                $targetRooms = $ruangansByLevel[$j->level_id] ?? collect();
                foreach ($targetRooms as $tr) {
                    $resolvedPengawas = collect();
                    if ($j->pengawas) {
                        $resolvedPengawas = collect([$j->pengawas]);
                    } else {
                        if ($isCustomMapel) {
                            $trWali = $tr->waliRuangan ?? ($tr->ustadz ?? null);
                            $resolvedPengawas = $trWali ? collect([$trWali]) : collect();
                        } else {
                            $rKey = $j->mata_pelajaran_id . '_' . $tr->id;
                            $lKey = $j->mata_pelajaran_id . '_' . $tr->level_id;
                            $resolvedPengawas = $guruMapelRuanganMap[$rKey]
                                ?? ($guruMapelLevelMap[$lKey]
                                    ?? ($guruMapelGeneralMap[$j->mata_pelajaran_id]
                                        ?? collect([$tr->waliRuangan ?? ($tr->ustadz ?? null)]->filter())));
                        }
                    }

                    $isMySchedule = ($ustadzId && $resolvedPengawas->contains('id', $ustadzId));

                    if (!$isMySchedule) {
                        continue;
                    }

                    $totalJadwalSaya++;
                    $totalJadwalSemua++;

                    $primaryPengawas = $resolvedPengawas->firstWhere('id', $ustadzId) ?? $resolvedPengawas->first();
                    $namaPengawas = $resolvedPengawas->pluck('nama_lengkap')->join(' • ') ?: 'Belum Ditentukan';

                    $groupedPerTanggal[$tanggalKey]['sesi'][] = [
                        'id' => $j->id,
                        'ujian_id' => $j->ujian_id,
                        'nama_ujian' => $j->ujian->nama_ujian ?? '-',
                        'tipe_ujian' => $j->ujian->tipe_ujian ?? '-',
                        'semester' => $j->ujian->semester->nama_semester ?? ($j->ujian && $j->ujian->semester_id == 9 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)'),
                        'mata_pelajaran_id' => $j->mata_pelajaran_id,
                        'is_custom_mapel' => $isCustomMapel,
                        'nama_mapel' => $j->nama_mapel,
                        'level_id' => $j->level_id,
                        'ruangan_id' => $tr->id,
                        'nama_ruangan' => $tr->nama_ruangan,
                        'nama_level' => $tr->nama_ruangan . ' (' . ($tr->level->nama_level ?? $j->level->nama_level ?? '-') . ')',
                        'waktu_mulai' => $waktuMulaiStr,
                        'waktu_selesai' => $waktuSelesaiStr,
                        'jam' => $jamText,
                        'ustadz_id' => $primaryPengawas?->id,
                        'nama_pengawas' => $namaPengawas,
                        'kode_pengawas' => $primaryPengawas?->kode_ustadz ?? null,
                        'pengawas_foto' => ($primaryPengawas && $primaryPengawas->foto) ? asset('storage/' . $primaryPengawas->foto) : null,
                        'is_my_schedule' => true,
                    ];
                    $groupedPerTanggal[$tanggalKey]['total_sesi']++;
                }
            } else {
                // Mode Per Ruangan Terpilih
                $resolvedPengawas = collect();
                if ($j->pengawas) {
                    $resolvedPengawas = collect([$j->pengawas]);
                } else {
                    if ($isCustomMapel) {
                        $rWali = $ruangan->waliRuangan ?? ($ruangan->ustadz ?? null);
                        $resolvedPengawas = $rWali ? collect([$rWali]) : collect();
                    } else {
                        $rKey = $j->mata_pelajaran_id . '_' . ($ruangan->id ?? 0);
                        $lKey = $j->mata_pelajaran_id . '_' . ($ruangan->level_id ?? 0);
                        $resolvedPengawas = $guruMapelRuanganMap[$rKey]
                            ?? ($guruMapelLevelMap[$lKey]
                                ?? ($guruMapelGeneralMap[$j->mata_pelajaran_id]
                                    ?? collect([$ruangan->waliRuangan ?? ($ruangan->ustadz ?? null)]->filter())));
                    }
                }

                $isMySchedule = ($ustadzId && $resolvedPengawas->contains('id', $ustadzId));

                if ($isMySchedule) {
                    $totalJadwalSaya++;
                }

                // Jika BUKAN ruangan binaannya ($isWaliRuangan == false) dan login sebagai Ustadz,
                // maka HANYA tampilkan tugas mengawas/menguji Ustadz tersebut
                if ($ustadzId && !$isWaliRuangan && !$isMySchedule) {
                    continue;
                }

                // Jika filter 'only_me' aktif dan bukan jadwal ustadz ini, skip
                if ($request->boolean('only_me') && !$isMySchedule) {
                    continue;
                }

                $primaryPengawas = $resolvedPengawas->firstWhere('id', $ustadzId) ?? $resolvedPengawas->first();
                $namaPengawas = $resolvedPengawas->pluck('nama_lengkap')->join(' • ') ?: 'Belum Ditentukan';

                $totalJadwalSemua++;
                $groupedPerTanggal[$tanggalKey]['sesi'][] = [
                    'id' => $j->id,
                    'ujian_id' => $j->ujian_id,
                    'nama_ujian' => $j->ujian->nama_ujian ?? '-',
                    'tipe_ujian' => $j->ujian->tipe_ujian ?? '-',
                    'semester' => $j->ujian->semester->nama_semester ?? ($j->ujian && $j->ujian->semester_id == 9 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)'),
                    'mata_pelajaran_id' => $j->mata_pelajaran_id,
                    'is_custom_mapel' => $isCustomMapel,
                    'nama_mapel' => $j->nama_mapel,
                    'level_id' => $j->level_id,
                    'ruangan_id' => $ruangan->id ?? 0,
                    'nama_ruangan' => $ruangan->nama_ruangan ?? '',
                    'nama_level' => $j->level->nama_level ?? '-',
                    'waktu_mulai' => $waktuMulaiStr,
                    'waktu_selesai' => $waktuSelesaiStr,
                    'jam' => $jamText,
                    'ustadz_id' => $primaryPengawas?->id,
                    'nama_pengawas' => $namaPengawas,
                    'kode_pengawas' => $primaryPengawas?->kode_ustadz ?? null,
                    'pengawas_foto' => ($primaryPengawas && $primaryPengawas->foto) ? asset('storage/' . $primaryPengawas->foto) : null,
                    'is_my_schedule' => $isMySchedule,
                ];
                $groupedPerTanggal[$tanggalKey]['total_sesi']++;
            }
        }

        // Filter tanggal yang tidak memiliki sesi (misal karena only_me / tugas saya)
        $filteredGrouped = array_values(array_filter($groupedPerTanggal, function ($tgl) {
            return count($tgl['sesi']) > 0;
        }));

        return response()->json([
            'success' => true,
            'data' => [
                'tahun_aktif' => [
                    'id' => $tahunId,
                    'nama_hijriyah' => $tahunAktif->nama_hijriyah ?? '-',
                    'nama_masehi' => $tahunAktif->nama_masehi ?? '-',
                ],
                'daftar_ruangan' => $daftarRuanganList,
                'selected_ruangan_id' => $selectedRuanganId,
                'selected_ruangan_nama' => $selectedRuanganNama,
                'nama_level' => $namaLevel,
                'is_wali_ruangan' => $isWaliRuangan,
                'wali_ruangan_nama' => $waliRuanganNama,
                'daftar_ujian' => $daftarUjian,
                'selected_ujian_id' => $selectedUjianId,
                'total_jadwal' => $totalJadwalSemua,
                'total_jadwal_saya' => $totalJadwalSaya,
                'jadwal_per_tanggal' => $filteredGrouped,
            ]
        ], 200);
    }
}
