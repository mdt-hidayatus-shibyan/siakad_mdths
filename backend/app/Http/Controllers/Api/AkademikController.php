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

        // 1. Jadwal Mengajar Pribadi Ustadz
        $jadwals = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ruangan.level', 'ruangan.gedung'])
            ->where('ustadz_id', $ustadzId)
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
                        'id'          => $j->id,
                        'jam_ke'      => $j->jam_ke,
                        'jam'         => $jamText,
                        'mapel'       => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                        'ustadz'      => $j->ustadz->nama_lengkap ?? 'Pengajar',
                        'kode_ustadz' => $j->ustadz->kode_ustadz ?? '-',
                        'ustadz_foto' => $j->ustadz && $j->ustadz->foto ? asset('storage/' . $j->ustadz->foto) : null,
                        'ruangan'     => $j->ruangan->nama_ruangan ?? '-',
                        'nama_gedung' => $j->ruangan->gedung->nama_gedung ?? null,
                        'nama_kamar'  => $j->ruangan->nama_kamar ?? null,
                        'level'       => $j->ruangan->level->nama_level ?? '-',
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

                $jadwalRuangan = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ruangan.level', 'ruangan.gedung'])
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
                                'id'          => $j->id,
                                'jam_ke'      => $j->jam_ke,
                                'jam'         => $jamText,
                                'mapel'       => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                                'ustadz'      => $j->ustadz->nama_lengkap ?? 'Pengajar',
                                'kode_ustadz' => $j->ustadz->kode_ustadz ?? '-',
                                'ustadz_foto' => $j->ustadz && $j->ustadz->foto ? asset('storage/' . $j->ustadz->foto) : null,
                                'ruangan'     => $j->ruangan->nama_ruangan ?? '-',
                                'nama_gedung' => $j->ruangan->gedung->nama_gedung ?? null,
                                'nama_kamar'  => $j->ruangan->nama_kamar ?? null,
                                'level'       => $j->ruangan->level->nama_level ?? '-',
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
}
