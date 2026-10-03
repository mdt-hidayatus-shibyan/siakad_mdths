<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\KalendarPendidikan;
use App\Models\PresensiKegiatanMurid;
use App\Models\PresensiMurid;
use App\Models\PresensiUstadz;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresensiMuridController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Ambil daftar sesi KBM murid untuk presensi kelas harian
     */
    public function getSesi(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan data Ustadz/Pengajar.',
                'data' => []
            ], 403);
        }

        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal)->format('Y-m-d') : date('Y-m-d');

        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari = $mapHari[Carbon::parse($tanggal)->format('l')];

        // 1. Cek Hari Libur / Bebas KBM Seharian Penuh & Libur Rutin Jumat
        $checkLiburSeharian = HariLibur::checkBebasKbm($tanggal, null);
        $isLibur = $checkLiburSeharian['is_libur'] && $checkLiburSeharian['is_seharian'];
        $keteranganLibur = $isLibur ? $checkLiburSeharian['keterangan'] : null;

        // Jika hari libur seharian penuh, sesi presensi murid TIDAK DITAMPILKAN (kosong)
        if ($isLibur) {
            return response()->json([
                'success' => true,
                'is_libur' => true,
                'keterangan_libur' => $keteranganLibur,
                'data' => []
            ], 200);
        }

        // 2. Cek apakah ustadz yang login adalah Wali Ruangan
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $ruanganWaliList = Ruangan::where('ustadz_id', $ustadzId)
            ->when($tahunAktif, function ($q) use ($tahunAktif) {
                $q->where(function ($sub) use ($tahunAktif) {
                    $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                        ->orWhereNull('tahun_pelajaran_id');
                });
            })
            ->get();

        $ruanganWaliIds = $ruanganWaliList->pluck('id')->toArray();
        $ruanganWaliNama = $ruanganWaliList->pluck('nama_ruangan')->join(', ');

        // 3. Query Jadwal Hari Aktif:
        // - Mengambil jadwal mengajar pribadi ustadz (forUstadz: utama maupun multi-pengampu)
        // - ATAU jika dia adalah Wali Ruangan, mencakup juga seluruh jadwal di ruangan binaannya
        $query = JadwalPelajaran::with(['mataPelajaran', 'ruangan', 'ustadz', 'ustadzs'])
            ->where('hari', $hari);

        if (!empty($ruanganWaliIds)) {
            $query->where(function ($q) use ($ustadzId, $ruanganWaliIds) {
                $q->forUstadz($ustadzId)
                    ->orWhereIn('ruangan_id', $ruanganWaliIds);
            });
        } else {
            $query->forUstadz($ustadzId);
        }

        $jadwals = $query->get()->sortBy([
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

        // 4. Cek apakah tanggal bertepatan dengan Event / Kegiatan Khusus (Non-KBM / Haflah / Multi-Sesi)
        $eventPresensi = KalendarPendidikan::getActiveEventPresensi($tanggal);
        if ($eventPresensi) {
            $sesiList = $eventPresensi->sesi_kegiatan ?? ($eventPresensi->tipe_presensi === 'harian' ? ['Harian'] : ['Siang', 'Malam']);
            if (empty($sesiList)) {
                $sesiList = ['Harian'];
            }

            // Ambil ruangan binaan ustadz (Wali Ruangan) atau seluruh ruangan jika dia bukan wali
            $ruangans = !empty($ruanganWaliList) && $ruanganWaliList->isNotEmpty()
                ? $ruanganWaliList
                : Ruangan::with('level')->orderBy('nama_ruangan')->get();

            $eventData = [];
            foreach ($sesiList as $namaSesi) {
                foreach ($ruangans as $r) {
                    $muridAktifList = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($r->id, $tahunAktif?->id ?? 0);
                    $totalMurid = $muridAktifList->count();

                    $existingPresensi = PresensiKegiatanMurid::where('kalendar_pendidikan_id', $eventPresensi->id)
                        ->where('tanggal', $tanggal)
                        ->where('sesi', $namaSesi)
                        ->where('ruangan_id', $r->id)
                        ->get();

                    $sudahAbsen = $existingPresensi->isNotEmpty();
                    $hadirCount = $existingPresensi->where('status', 'Hadir')->count();
                    $sakitCount = $existingPresensi->where('status', 'Sakit')->count();
                    $izinCount = $existingPresensi->where('status', 'Izin')->count();
                    $alphaCount = $existingPresensi->where('status', 'Alpha')->count();

                    $isMilikWali = in_array($r->id, $ruanganWaliIds);

                    $jamText = match ($namaSesi) {
                        'Siang'  => '13:30 - 17:00 WIB',
                        'Malam'  => '19:30 - 23:00 WIB',
                        'Pagi'   => '08:00 - 11:30 WIB',
                        'Harian' => 'Hari Efektif Kegiatan',
                        default  => 'Sesi ' . $namaSesi,
                    };

                    $eventData[] = [
                        'id'                     => $r->id,
                        'kalendar_pendidikan_id' => $eventPresensi->id,
                        'sesi'                   => $namaSesi,
                        'jam'                    => $jamText,
                        'pelajaran'              => $eventPresensi->nama_kegiatan,
                        'nama_kegiatan'          => $eventPresensi->nama_kegiatan,
                        'kategori'               => $eventPresensi->kategoriKegiatan?->nama_kategori ?? 'Kegiatan',
                        'ruangan_id'             => $r->id,
                        'kelas'                  => $r->nama_ruangan,
                        'level'                  => $r->level?->nama_level ?? '-',
                        'total_murid'            => $totalMurid,
                        'sudah_absen'            => $sudahAbsen,
                        'hadir_count'            => $hadirCount,
                        'sakit_count'            => $sakitCount,
                        'izin_count'             => $izinCount,
                        'alpha_count'            => $alphaCount,
                        'is_milik_wali'          => $isMilikWali,
                        'is_event'               => true,
                    ];
                }
            }

            return response()->json([
                'success'          => true,
                'is_libur'         => false,
                'keterangan_libur' => null,
                'is_ujian'         => false,
                'nama_ujian'       => null,
                'ujian_id'         => null,
                'is_event'         => true,
                'event_info'       => [
                    'id'              => $eventPresensi->id,
                    'nama_kegiatan'   => $eventPresensi->nama_kegiatan,
                    'kategori'        => $eventPresensi->kategoriKegiatan?->nama_kategori ?? 'Kegiatan',
                    'tipe_presensi'   => $eventPresensi->tipe_presensi,
                    'sesi_list'       => $sesiList,
                    'tanggal_mulai'   => $eventPresensi->tanggal_mulai->format('Y-m-d'),
                    'tanggal_selesai' => $eventPresensi->tanggal_selesai->format('Y-m-d'),
                ],
                'ruangan_wali'     => $ruanganWaliNama ?: null,
                'data'             => $eventData
            ], 200);
        }

        // 5. Cek apakah tanggal bertepatan dengan masa / jadwal Ujian Madrasah yang berlaku untuk kelas yang diajar
        $activeUjians = \App\Models\Ujian\Ujian::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->get();

        $ujian = null;
        if ($jadwals->isNotEmpty() && $activeUjians->isNotEmpty()) {
            $firstJadwal = $jadwals->first();
            if ($firstJadwal && $firstJadwal->ruangan && $firstJadwal->ruangan->level) {
                $ujian = $activeUjians->first(function ($u) use ($firstJadwal) {
                    return $u->isBerlakuUntukLevel($firstJadwal->ruangan->level);
                });
            }
        } elseif ($activeUjians->isNotEmpty()) {
            $ujian = $activeUjians->first();
        }

        if (!$ujian) {
            $ruanganIdsJadwal = $jadwals->pluck('ruangan_id')->unique()->filter()->toArray();
            $jadwalUjianQuery = \App\Models\Ujian\JadwalUjian::whereDate('tanggal_ujian', $tanggal);
            if (!empty($ruanganIdsJadwal)) {
                $levelIds = \App\Models\Ruangan::whereIn('id', $ruanganIdsJadwal)->pluck('level_id')->unique()->toArray();
                $jadwalUjianQuery->whereIn('level_id', $levelIds);
            }
            $jadwalUjianAda = $jadwalUjianQuery->first();
            if ($jadwalUjianAda) {
                $ujian = $jadwalUjianAda->ujian;
            }
        }

        $isUjian = ($ujian != null);
        $namaUjian = $ujian ? $ujian->nama_ujian : null;
        $ujianId = $ujian ? $ujian->id : null;

        // Jika hari bertepatan dengan Ujian Madrasah, sesi KBM reguler TIDAK DITAMPILKAN (kosong) dan dialihkan
        if ($isUjian) {
            return response()->json([
                'success' => true,
                'is_libur' => false,
                'keterangan_libur' => null,
                'is_ujian' => true,
                'nama_ujian' => $namaUjian,
                'ujian_id' => $ujianId,
                'ruangan_wali' => $ruanganWaliNama ?: null,
                'data' => []
            ], 200);
        }

        // Bulk query status presensi jadwal pada tanggal terpilih untuk eliminasi N+1
        $jadwalIds = $jadwals->pluck('id')->toArray();
        $sudahAbsenMap = !empty($jadwalIds)
            ? PresensiMurid::whereIn('jadwal_pelajaran_id', $jadwalIds)
            ->where('tanggal', $tanggal)
            ->pluck('jadwal_pelajaran_id')
            ->flip()
            ->toArray()
            : [];

        $data = $jadwals->map(function ($j) use ($sudahAbsenMap, $ruanganWaliIds, $ustadzId, $tanggal) {
            $checkSesi = HariLibur::checkBebasKbm($tanggal, $j->jam_ke, $j->ruangan_id, $j->ruangan?->level_id);
            $isBebasKbm = $checkSesi['is_libur'];
            $keteranganBebasKbm = $checkSesi['keterangan'];

            $sudahAbsen = isset($sudahAbsenMap[$j->id]);
            $isPengampuJadwal = $j->daftar_ustadz->contains('id', $ustadzId);
            $isMilikWali = in_array($j->ruangan_id, $ruanganWaliIds) && !$isPengampuJadwal;

            $jamText = match ($j->jam_ke) {
                'Nadzoman' => '13:45 - 14:00 WIB',
                '1' => '14:00 - 14:45 WIB',
                '2' => '15:30 - 16:15 WIB',
                'Ekstra' => '20:00 - 21:00 WIB',
                default => 'Jam Ke-' . $j->jam_ke,
            };

            return [
                'id'                   => $j->id,
                'jam_ke'               => $j->jam_ke,
                'jam'                  => $jamText,
                'pelajaran'            => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'kelas'                => $j->ruangan->nama_ruangan ?? '-',
                'guru'                 => $j->daftar_nama_pengampu,
                'is_milik_wali'        => $isMilikWali,
                'sudah_absen'          => $sudahAbsen,
                'is_bebas_kbm'         => $isBebasKbm,
                'keterangan_bebas_kbm' => $keteranganBebasKbm,
            ];
        });

        return response()->json([
            'success' => true,
            'is_libur' => false,
            'keterangan_libur' => null,
            'is_ujian' => false,
            'nama_ujian' => null,
            'ujian_id' => null,
            'ruangan_wali' => $ruanganWaliNama ?: null,
            'data' => $data
        ], 200);
    }

    /**
     * Ambil daftar murid pada sesi KBM untuk diisi presensinya
     */
    public function getMurid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil Ustadz.'
            ], 403);
        }

        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

        $jadwal = JadwalPelajaran::with(['ruangan', 'ustadz', 'ustadzs'])->findOrFail($request->jadwal_id);

        // Cek Bebas KBM / Hari Libur untuk sesi ini
        $checkBebas = HariLibur::checkBebasKbm($tanggal, $jadwal->jam_ke, $jadwal->ruangan_id, $jadwal->ruangan?->level_id);
        if ($checkBebas['is_libur']) {
            return response()->json([
                'success' => false,
                'is_libur' => true,
                'message' => 'Sesi KBM ini bebas KBM / libur (' . $checkBebas['keterangan'] . '). Presensi murid ditiadakan.',
                'data' => []
            ], 422);
        }

        // Validasi Otorisasi: Guru Pengajar (Utama atau Team Teaching) ATAU Wali Ruangan dari kelas terkait
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $ruanganWaliIds = Ruangan::where('ustadz_id', $ustadzId)
            ->when($tahunAktif, function ($q) use ($tahunAktif) {
                $q->where(function ($sub) use ($tahunAktif) {
                    $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                        ->orWhereNull('tahun_pelajaran_id');
                });
            })
            ->pluck('id')
            ->toArray();

        $isGuruPengajar = $jadwal->daftar_ustadz->contains('id', $ustadzId);
        $isWaliRuangan = in_array($jadwal->ruangan_id, $ruanganWaliIds);
        $isBadal = !$isGuruPengajar && !$isWaliRuangan;

        // Cari Tahun Pelajaran dari Tanggal / Bulan Hijriyah
        $bulan = BulanHijriyah::whereDate('tanggal_mulai_masehi', '<=', $tanggal)
            ->whereDate('tanggal_selesai_masehi', '>=', $tanggal)
            ->first();

        $tahunPelajaranId = $bulan ? $bulan->tahun_pelajaran_id : null;
        if (!$tahunPelajaranId) {
            $sem = Semester::where('is_active', true)->first();
            $tahunPelajaranId = $sem ? $sem->tahun_pelajaran_id : null;
        }
        if (!$tahunPelajaranId) {
            $tahunPelajaranId = $jadwal->ruangan->tahun_pelajaran_id ?? ($tahunAktif->id ?? null);
        }

        // Ambil data murid kelas
        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($jadwal->ruangan_id, $tahunPelajaranId, 'Aktif');

        // Ambil presensi tersimpan jika sudah pernah diinput
        $presensiTersimpan = PresensiMurid::where('tanggal', $tanggal)
            ->where('jadwal_pelajaran_id', $jadwal->id)
            ->get()
            ->keyBy('murid_id');

        $data = $murids->map(function ($m) use ($presensiTersimpan) {
            $existing = $presensiTersimpan->get($m->id);
            return [
                'murid_id' => $m->id,
                'nama' => $m->nama_lengkap ?? $m->nama,
                'nism' => $m->nism,
                'jenis_kelamin' => $m->jenis_kelamin,
                'status' => $existing ? $existing->status : null,
            ];
        });

        return response()->json([
            'success' => true,
            'is_badal' => $isBadal,
            'data' => $data
        ], 200);
    }

    /**
     * Simpan / Perbarui Presensi Murid KBM
     */
    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
            'is_badal' => 'nullable|boolean',
            'status_ustadz' => 'nullable|in:Hadir,Sakit,Izin,Alpha,Kosong',
            'alasan_badal' => 'nullable|string|max:255',
            'presensi' => 'required|array',
            'presensi.*.murid_id' => 'required|exists:murids,id',
            'presensi.*.status' => 'nullable|in:Hadir,Sakit,Izin,Alpha,Dispensasi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data presensi tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil Ustadz.'
            ], 403);
        }

        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

        $jadwal = JadwalPelajaran::with(['ustadz', 'ustadzs', 'ruangan'])->findOrFail($request->jadwal_id);

        // Cek Bebas KBM / Hari Libur untuk sesi ini
        $checkBebas = HariLibur::checkBebasKbm($tanggal, $jadwal->jam_ke, $jadwal->ruangan_id, $jadwal->ruangan?->level_id);
        if ($checkBebas['is_libur']) {
            return response()->json([
                'success' => false,
                'message' => 'Pencatatan presensi ditolak karena sesi KBM ini bebas KBM / libur (' . $checkBebas['keterangan'] . ').'
            ], 422);
        }

        // Otorisasi: Pengajar Pribadi, Wali Ruangan, atau Guru Pengganti (Badal)
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $ruanganWaliIds = Ruangan::where('ustadz_id', $ustadzId)
            ->when($tahunAktif, function ($q) use ($tahunAktif) {
                $q->where(function ($sub) use ($tahunAktif) {
                    $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                        ->orWhereNull('tahun_pelajaran_id');
                });
            })
            ->pluck('id')
            ->toArray();

        $isGuruPengajar = $jadwal->daftar_ustadz->contains('id', $ustadzId);
        $isWaliRuangan = in_array($jadwal->ruangan_id, $ruanganWaliIds);
        $isBadal = !$isGuruPengajar && !$isWaliRuangan;

        // Cari semester berdasarkan tanggal
        $bulan = BulanHijriyah::whereDate('tanggal_mulai_masehi', '<=', $tanggal)
            ->whereDate('tanggal_selesai_masehi', '>=', $tanggal)
            ->first();

        $semesterId = $bulan ? $bulan->semester_id : null;
        if (!$semesterId) {
            $sem = Semester::where('is_active', true)->first();
            $semesterId = $sem ? $sem->id : null;
        }

        DB::beginTransaction();
        try {
            // Jika disimpan oleh Guru Pengganti (Badal), catat otomatis pada presensi ustadz
            if ($isBadal || $request->boolean('is_badal')) {
                $primaryUstadzId = $jadwal->ustadz_id ?? $jadwal->daftar_ustadz->first()?->id;
                if ($primaryUstadzId) {
                    $presUstadz = PresensiUstadz::where('tanggal', $tanggal)
                        ->where('jadwal_pelajaran_id', $jadwal->id)
                        ->where('ustadz_id', $primaryUstadzId)
                        ->first();

                    $statusUstadz = $request->input('status_ustadz', 'Izin');
                    if (!in_array($statusUstadz, ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Kosong'])) {
                        $statusUstadz = 'Izin';
                    }

                    $alasanBadal = $request->input('alasan_badal') ?? $request->input('keterangan_badal') ?? $request->input('keterangan');

                    if ($presUstadz) {
                        $presUstadz->ustadz_pengganti_id = $ustadzId;
                        if ($statusUstadz) {
                            $presUstadz->status = $statusUstadz;
                        }
                        if ($alasanBadal) {
                            $presUstadz->keterangan = $alasanBadal;
                        }
                        $presUstadz->diinput_oleh_id = $user->id;
                        $presUstadz->save();
                    } else {
                        PresensiUstadz::create([
                            'tanggal' => $tanggal,
                            'jadwal_pelajaran_id' => $jadwal->id,
                            'ustadz_id' => $primaryUstadzId,
                            'status' => $statusUstadz,
                            'ustadz_pengganti_id' => $ustadzId,
                            'keterangan' => $alasanBadal ?: ('Digantikan oleh ' . ($ustadz->nama_lengkap ?? 'Guru Pengganti')),
                            'diinput_oleh_id' => $user->id,
                        ]);
                    }
                }
            }

            foreach ($request->presensi as $item) {
                if (!empty($item['status'])) {
                    PresensiMurid::updateOrCreate(
                        [
                            'jadwal_pelajaran_id' => $jadwal->id,
                            'murid_id' => $item['murid_id'],
                            'tanggal' => $tanggal,
                        ],
                        [
                            'status' => $item['status'],
                            'semester_id' => $semesterId,
                        ]
                    );
                } else {
                    // Jika status kosong/null, hapus data presensi jika sebelumnya ada
                    PresensiMurid::where('jadwal_pelajaran_id', $jadwal->id)
                        ->where('murid_id', $item['murid_id'])
                        ->where('tanggal', $tanggal)
                        ->delete();
                }
            }

            DB::commit();

            $pesan = $isBadal
                ? 'Presensi murid berhasil disimpan sebagai Guru Pengganti (Badal)!'
                : 'Presensi murid berhasil disimpan ke sistem!';

            return response()->json([
                'success' => true,
                'message' => $pesan,
                'is_badal' => $isBadal,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil daftar murid pada sesi kegiatan / event khusus untuk diisi presensinya
     */
    public function getMuridKegiatan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kalendar_pendidikan_id' => 'required|exists:kalendar_pendidikans,id',
            'ruangan_id'             => 'required|exists:ruangans,id',
            'tanggal'                => 'required|date',
            'sesi'                   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil Ustadz.'
            ], 403);
        }

        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
        $kalendarId = $request->kalendar_pendidikan_id;
        $ruanganId = $request->ruangan_id;
        $sesi = $request->sesi;

        $event = KalendarPendidikan::with(['kategoriKegiatan'])->findOrFail($kalendarId);
        $ruangan = Ruangan::with('level')->findOrFail($ruanganId);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $muridList = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganId, $tahunAktif?->id ?? 0);

        // Ambil presensi kegiatan yang sudah tercatat
        $presensiExisting = PresensiKegiatanMurid::where('kalendar_pendidikan_id', $kalendarId)
            ->where('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->where('ruangan_id', $ruanganId)
            ->get()
            ->keyBy('murid_id');

        $dataMurid = $muridList->map(function ($m) use ($presensiExisting) {
            $p = $presensiExisting->get($m->id);
            return [
                'id'            => $m->id,
                'nis'           => $m->nis ?? '-',
                'nama_lengkap'  => $m->nama_lengkap,
                'jenis_kelamin' => $m->jenis_kelamin,
                'status'        => $p ? $p->status : 'Hadir',
                'catatan'       => $p ? $p->catatan : null,
                'foto'          => $m->foto ? asset('storage/' . $m->foto) : null,
            ];
        });

        $rekap = [
            'total' => $dataMurid->count(),
            'hadir' => $dataMurid->where('status', 'Hadir')->count(),
            'sakit' => $dataMurid->where('status', 'Sakit')->count(),
            'izin'  => $dataMurid->where('status', 'Izin')->count(),
            'alpha' => $dataMurid->where('status', 'Alpha')->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'event' => [
                    'id'            => $event->id,
                    'nama_kegiatan' => $event->nama_kegiatan,
                    'kategori'      => $event->kategoriKegiatan?->nama_kategori ?? 'Kegiatan',
                    'sesi'          => $sesi,
                    'tanggal'       => $tanggal,
                ],
                'ruangan' => [
                    'id'           => $ruangan->id,
                    'nama_ruangan' => $ruangan->nama_ruangan,
                    'level'        => $ruangan->level?->nama_level ?? '-',
                ],
                'rekap' => $rekap,
                'murid' => $dataMurid,
            ]
        ], 200);
    }

    /**
     * Simpan presensi murid untuk sesi kegiatan / event khusus
     */
    public function simpanPresensiKegiatan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kalendar_pendidikan_id' => 'required|exists:kalendar_pendidikans,id',
            'ruangan_id'             => 'required|exists:ruangans,id',
            'tanggal'                => 'required|date',
            'sesi'                   => 'required|string',
            'presensi'               => 'required|array',
            'presensi.*.murid_id'    => 'required|exists:murids,id',
            'presensi.*.status'      => 'required|in:Hadir,Sakit,Izin,Alpha,Dispensasi',
            'presensi.*.catatan'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data presensi tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
        $kalendarId = $request->kalendar_pendidikan_id;
        $ruanganId = $request->ruangan_id;
        $sesi = $request->sesi;

        DB::beginTransaction();
        try {
            foreach ($request->presensi as $item) {
                PresensiKegiatanMurid::updateOrCreate(
                    [
                        'kalendar_pendidikan_id' => $kalendarId,
                        'tanggal'                => $tanggal,
                        'sesi'                   => $sesi,
                        'ruangan_id'             => $ruanganId,
                        'murid_id'               => $item['murid_id'],
                    ],
                    [
                        'status'       => $item['status'],
                        'catatan'      => $item['catatan'] ?? null,
                        'diinput_oleh' => $user->id,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presensi kegiatan murid berhasil disimpan!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan presensi kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }
}
