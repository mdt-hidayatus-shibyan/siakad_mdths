<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\KalendarPendidikan;
use App\Models\Pengumuman;
use App\Models\PresensiMurid;
use App\Models\PresensiUstadz;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $todayDate = date('Y-m-d');

        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hariIni = $mapHari[Carbon::now()->format('l')];

        // Total Jadwal Mingguan (Guru Utama maupun Pendamping)
        $totalJadwalMingguan = $ustadzId
            ? JadwalPelajaran::forUstadz($ustadzId)->count()
            : 0;

        // Cek Libur Hari Ini (Seharian Penuh atau Rutin Jumat)
        $checkLiburSeharian = HariLibur::checkBebasKbm($todayDate, null);
        $isLiburHariIni = $checkLiburSeharian['is_libur'] && $checkLiburSeharian['is_seharian'];
        $keteranganLiburHariIni = $isLiburHariIni ? $checkLiburSeharian['keterangan'] : null;

        // Jadwal Hari Ini (Hanya jika bukan hari libur seharian penuh)
        $jadwalHariIniList = collect();
        $jadwalHariIniCount = 0;
        $presensiSelesaiCount = 0;
        $formattedJadwal = collect();

        if (!$isLiburHariIni) {
            $jadwalHariIniQuery = JadwalPelajaran::with(['mataPelajaran', 'ruangan.level', 'ustadz', 'ustadzs'])
                ->where('hari', $hariIni);

            if ($ustadzId) {
                $jadwalHariIniQuery->forUstadz($ustadzId);
            }

            $jadwalHariIniList = $jadwalHariIniQuery->get()->sortBy([
                fn($a, $b) => (match ((string) $a->jam_ke) {
                    'Nadzoman' => 1,
                    '1' => 2,
                    '2' => 3,
                    'Ekstra' => 4,
                    default => is_numeric($a->jam_ke) ? (int)$a->jam_ke + 10 : 99
                })
                    <=> (match ((string) $b->jam_ke) {
                        'Nadzoman' => 1,
                        '1' => 2,
                        '2' => 3,
                        'Ekstra' => 4,
                        default => is_numeric($b->jam_ke) ? (int)$b->jam_ke + 10 : 99
                    }),
                fn($a, $b) => strnatcasecmp($a->ruangan?->nama_ruangan ?? '', $b->ruangan?->nama_ruangan ?? ''),
            ])->values();

            $jadwalHariIniCount = $jadwalHariIniList->count();

            // Bulk query status presensi hari ini untuk eliminasi N+1
            $jadwalIds = $jadwalHariIniList->pluck('id')->toArray();
            $sudahAbsenMuridMap = !empty($jadwalIds)
                ? PresensiMurid::whereIn('jadwal_pelajaran_id', $jadwalIds)
                ->where('tanggal', $todayDate)
                ->pluck('jadwal_pelajaran_id')
                ->flip()
                ->toArray()
                : [];

            $presensiUstadzList = !empty($jadwalIds)
                ? PresensiUstadz::whereIn('jadwal_pelajaran_id', $jadwalIds)
                ->where('tanggal', $todayDate)
                ->get()
                : collect();

            $formattedJadwal = $jadwalHariIniList->map(function ($j) use ($sudahAbsenMuridMap, $presensiUstadzList, &$presensiSelesaiCount, $ustadzId, $todayDate) {
                $checkSesi = HariLibur::checkBebasKbm($todayDate, $j->jam_ke, $j->ruangan_id, $j->ruangan?->level_id);
                $isBebasKbm = $checkSesi['is_libur'];
                $keteranganBebasKbm = $checkSesi['keterangan'];

                $sudahAbsenMurid = isset($sudahAbsenMuridMap[$j->id]);

                // Presensi Ustadz: Cari apakah ustadz ini (atau ustadz jadwal) sudah ada presensinya
                $presUstadz = $presensiUstadzList->first(function ($p) use ($j, $ustadzId) {
                    if ($p->jadwal_pelajaran_id != $j->id) return false;
                    if ($ustadzId) {
                        return $p->ustadz_id == $ustadzId || $p->ustadz_pengganti_id == $ustadzId;
                    }
                    return true;
                });
                $sudahAbsenUstadz = ($presUstadz != null);
                $statusPresensiUstadz = $presUstadz ? $presUstadz->status : 'Belum Absen';

                if (($sudahAbsenMurid && $sudahAbsenUstadz) || $isBebasKbm) {
                    $presensiSelesaiCount++;
                }

                $jamText = match ($j->jam_ke) {
                    'Nadzoman' => '13:45 - 14:00 WIB',
                    '1' => '14:00 - 14:45 WIB',
                    '2' => '15:30 - 16:15 WIB',
                    'Ekstra' => '20:00 - 21:00 WIB',
                    default => 'Jam Ke-' . $j->jam_ke,
                };

                // Periksa peran user: apakah Guru Utama atau Guru Pendamping
                $isUtama = ($j->ustadz_id == $ustadzId);
                $currentUserPivot = $j->ustadzs->firstWhere('id', $ustadzId);
                if ($currentUserPivot && isset($currentUserPivot->pivot->is_utama)) {
                    $isUtama = (bool) $currentUserPivot->pivot->is_utama;
                }

                return [
                    'id'                     => $j->id,
                    'jam_ke'                 => $j->jam_ke,
                    'jam'                    => $jamText,
                    'mapel'                  => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                    'kelas'                  => $j->ruangan->nama_ruangan ?? '-',
                    'guru'                   => $j->daftar_nama_pengampu,
                    'is_utama'               => $isUtama,
                    'peran'                  => $isUtama ? 'Guru Utama' : 'Guru Pendamping',
                    'is_team_teaching'       => $j->daftar_ustadz->count() > 1,
                    'sudah_absen'            => $sudahAbsenMurid, // backward compatibility
                    'sudah_absen_murid'      => $sudahAbsenMurid,
                    'sudah_absen_ustadz'     => $sudahAbsenUstadz,
                    'status_presensi_ustadz' => $statusPresensiUstadz,
                    'is_bebas_kbm'           => $isBebasKbm,
                    'keterangan_bebas_kbm'   => $keteranganBebasKbm,
                ];
            });
        }

        // Total Murid Wali
        $totalMuridWali = 0;
        if ($tahunAktif && $ustadzId) {
            $ruanganWali = Ruangan::where('tahun_pelajaran_id', $tahunAktif->id)
                ->where('ustadz_id', $ustadzId)
                ->first();

            if ($ruanganWali) {
                $totalMuridWali = $ruanganWali->murids()->count();
            }
        }

        // Pengumuman Aktif (Rentang tanggal terbit sampai tanggal selesai)
        $today = date('Y-m-d');
        $pengumuman = Pengumuman::with('user')
            ->where('status', 'Terbit')
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')
                    ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->where('target_audience', 'Ustadz')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($p) {
                $penulis = $p->user?->name ?? 'Administrator';
                $tglMulai = $p->tanggal_mulai ? $p->tanggal_mulai->translatedFormat('d M Y') : null;
                $tglSelesai = $p->tanggal_selesai ? $p->tanggal_selesai->translatedFormat('d M Y') : null;
                $periodeBerlaku = 'Selamanya (Tanpa Batas)';
                if ($tglMulai || $tglSelesai) {
                    $periodeBerlaku = ($tglMulai ?? 'Seterusnya') . ' - ' . ($tglSelesai ?? 'Seterusnya');
                }

                return [
                    'id'                => $p->id,
                    'judul'             => $p->judul,
                    'konten_html'       => $p->konten,
                    'konten'            => strip_tags($p->konten ?? ''),
                    'tipe'              => $p->tipe ?? 'Informasi',
                    'status'            => $p->status ?? 'Terbit',
                    'target_audience'   => $p->target_audience ?? 'Ustadz',
                    'penulis'           => $penulis,
                    'periode_berlaku'   => $periodeBerlaku,
                    'lampiran_pdf_url'  => $p->lampiran_pdf_url,
                    'nama_file_pdf'     => $p->nama_file_pdf,
                    'tanggal_mulai'     => $p->tanggal_mulai ? $p->tanggal_mulai->format('d-m-Y') : ($p->created_at ? $p->created_at->format('d-m-Y') : date('d-m-Y')),
                    'created_at_format' => $p->created_at ? $p->created_at->translatedFormat('d F Y, H:i') : null,
                ];
            });

        // Cek Kepanitiaan IMNI Ustadz pada Tahun Pelajaran Aktif
        $panitiaImni = $ustadzId
            ? \App\Models\Ujian\PanitiaImni::with('tahunPelajaran')
                ->where('ustadz_id', $ustadzId)
                ->whereHas('tahunPelajaran', fn($tp) => $tp->where('is_active', true))
                ->where('is_active', true)
                ->first()
            : null;

        $panitiaImniData = $panitiaImni ? [
            'is_panitia' => true,
            'jabatan' => $panitiaImni->jabatan,
            'no_sk' => $panitiaImni->no_sk,
            'keterangan' => $panitiaImni->keterangan,
            'tahun_pelajaran' => $panitiaImni->tahunPelajaran?->nama_hijriyah . ' H / ' . $panitiaImni->tahunPelajaran?->nama_masehi . ' M',
        ] : [
            'is_panitia' => false,
            'jabatan' => null,
            'no_sk' => null,
            'keterangan' => null,
            'tahun_pelajaran' => null,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'is_libur_hari_ini' => $isLiburHariIni,
                'keterangan_libur_hari_ini' => $keteranganLiburHariIni,
                'statistik' => [
                    'total_jadwal_mingguan' => $totalJadwalMingguan,
                    'jadwal_hari_ini' => $jadwalHariIniCount,
                    'presensi_selesai_hari_ini' => $presensiSelesaiCount,
                    'total_murid_wali' => $totalMuridWali,
                ],
                'jadwal_hari_ini' => $formattedJadwal,
                'pengumuman' => $pengumuman,
                'panitia_imni' => $panitiaImniData,
            ]
        ], 200);
    }

    public function pengumuman()
    {
        $today = date('Y-m-d');
        $pengumuman = Pengumuman::with('user')
            ->where('status', 'Terbit')
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')
                    ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->latest()
            ->get()
            ->map(function ($p) {
                $penulis = $p->user?->name ?? 'Administrator';
                $tglMulai = $p->tanggal_mulai ? $p->tanggal_mulai->translatedFormat('d M Y') : null;
                $tglSelesai = $p->tanggal_selesai ? $p->tanggal_selesai->translatedFormat('d M Y') : null;
                $periodeBerlaku = 'Selamanya (Tanpa Batas)';
                if ($tglMulai || $tglSelesai) {
                    $periodeBerlaku = ($tglMulai ?? 'Seterusnya') . ' - ' . ($tglSelesai ?? 'Seterusnya');
                }

                return [
                    'id'                => $p->id,
                    'judul'             => $p->judul,
                    'konten_html'       => $p->konten,
                    'konten'            => strip_tags($p->konten ?? ''),
                    'tipe'              => $p->tipe ?? 'Informasi',
                    'status'            => $p->status ?? 'Terbit',
                    'target_audience'   => $p->target_audience ?? 'Semua',
                    'penulis'           => $penulis,
                    'periode_berlaku'   => $periodeBerlaku,
                    'lampiran_pdf_url'  => $p->lampiran_pdf_url,
                    'nama_file_pdf'     => $p->nama_file_pdf,
                    'tanggal_mulai'     => $p->tanggal_mulai ? $p->tanggal_mulai->format('d-m-Y') : ($p->created_at ? $p->created_at->format('d-m-Y') : date('d-m-Y')),
                    'created_at_format' => $p->created_at ? $p->created_at->translatedFormat('d F Y, H:i') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pengumuman
        ], 200);
    }

    public function kalendar()
    {
        $kalendar = KalendarPendidikan::orderBy('tanggal_mulai')->get();
        return response()->json([
            'success' => true,
            'data' => $kalendar
        ], 200);
    }
}
