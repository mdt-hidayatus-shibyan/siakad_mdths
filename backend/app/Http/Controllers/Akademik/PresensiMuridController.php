<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\KalendarPendidikan;
use App\Models\MataPelajaran;
use App\Models\PengaturanAkademik;
use App\Models\PresensiKegiatanMurid;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use App\Services\PresensiMuridService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PresensiMuridController extends Controller
{
    protected $muridRuanganRepo;
    protected $presensiService;

    public function __construct(MuridRuanganRepository $muridRuanganRepo, PresensiMuridService $presensiService)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
        $this->presensiService = $presensiService;
    }

    /**
     * Monitoring Progres Harian Presensi Murid untuk Seluruh Kelas
     */
    public function progresHarian(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;
        $status_filter = $request->status; // 'sudah', 'belum'
        $sesi_filter = $request->sesi;

        $ruangansQuery = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan');
        if ($ruangan_id) {
            $ruangansQuery->where('id', $ruangan_id);
        }
        $ruangans = $ruangansQuery->get();
        $semuaRuangan = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();

        $nama_hari_inggris = Carbon::parse($tanggal)->format('l');
        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari_ini = $mapHari[$nama_hari_inggris] ?? 'Senin';

        // 1. Cek Event Khusus dengan Presensi Aktif
        $eventPresensi = KalendarPendidikan::getActiveEventPresensi($tanggal);
        $isEvent = ($eventPresensi != null);
        $eventInfo = $eventPresensi;

        // 2. Cek Libur / Bebas KBM Seharian
        $checkLibur = HariLibur::checkBebasKbm($tanggal, null);
        $isLibur = $checkLibur['is_libur'] && $checkLibur['is_seharian'];
        $keteranganLibur = $isLibur ? $checkLibur['keterangan'] : null;

        // 3. Cek Ujian
        $ujian = Ujian::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();
        if (!$ujian) {
            $jadwalUjianAda = JadwalUjian::whereDate('tanggal_ujian', $tanggal)->first();
            if ($jadwalUjianAda) {
                $ujian = $jadwalUjianAda->ujian;
            }
        }
        $isUjian = ($ujian != null);
        $namaUjian = $ujian ? $ujian->nama_ujian : null;
        $ujianId = $ujian ? $ujian->id : null;

        // === JIKA HARI EVENT PRESENSI KHUSUS ===
        if ($isEvent) {
            $sesiList = $eventPresensi->tipe_presensi === 'multi_sesi'
                ? ($eventPresensi->sesi_kegiatan ?? ['Siang', 'Malam'])
                : ['Harian'];

            $activeSesiList = ($sesi_filter && in_array($sesi_filter, $sesiList)) ? [$sesi_filter] : $sesiList;

            // Ambil Tahun Pelajaran dari Tanggal
            $bulan = BulanHijriyah::where('tanggal_mulai_masehi', '<=', $tanggal)
                ->where('tanggal_selesai_masehi', '>=', $tanggal)
                ->first();
            $tahun_pelajaran_id = $bulan ? $bulan->tahun_pelajaran_id : (Semester::where('is_active', 1)->first()?->tahun_pelajaran_id);

            // Ambil data presensi kegiatan murid pada tanggal ini
            $presensiDb = PresensiKegiatanMurid::where('kalendar_pendidikan_id', $eventPresensi->id)
                ->where('tanggal', $tanggal)
                ->whereIn('ruangan_id', $ruangans->pluck('id'))
                ->get()
                ->groupBy(fn($item) => $item->ruangan_id . '_' . $item->sesi);

            $totalSesi = $ruangans->count() * count($activeSesiList);
            $totalSudahAbsen = 0;
            $totalBelumAbsen = 0;
            $rekapStatus = [
                'Hadir' => 0,
                'Sakit' => 0,
                'Izin' => 0,
                'Alpha' => 0,
                'Dispensasi' => 0,
                'TotalMurid' => 0,
            ];

            $detailProgres = [];

            foreach ($ruangans as $r) {
                $totalMuridRuangan = $this->muridRuanganRepo->getMuridByRuanganAndTahun($r->id, $tahun_pelajaran_id, 'Aktif')->count();

                foreach ($activeSesiList as $sesi) {
                    $key = $r->id . '_' . $sesi;
                    $records = $presensiDb->get($key, collect());
                    $isSudah = $records->isNotEmpty();

                    if ($isSudah) {
                        $totalSudahAbsen++;
                    } else {
                        $totalBelumAbsen++;
                    }

                    $countHadir = $records->where('status', 'Hadir')->count();
                    $countSakit = $records->where('status', 'Sakit')->count();
                    $countIzin = $records->where('status', 'Izin')->count();
                    $countAlpha = $records->where('status', 'Alpha')->count();
                    $countDispensasi = $records->where('status', 'Dispensasi')->count();
                    $countTotal = $records->count() ?: $totalMuridRuangan;

                    $rekapStatus['Hadir'] += $countHadir;
                    $rekapStatus['Sakit'] += $countSakit;
                    $rekapStatus['Izin'] += $countIzin;
                    $rekapStatus['Alpha'] += $countAlpha;
                    $rekapStatus['Dispensasi'] += $countDispensasi;
                    $rekapStatus['TotalMurid'] += ($records->count() ?: $totalMuridRuangan);

                    $lastUpdated = $records->max('updated_at');

                    if ($status_filter === 'sudah' && !$isSudah) continue;
                    if ($status_filter === 'belum' && $isSudah) continue;

                    $detailProgres[] = [
                        'is_event' => true,
                        'kalendar_id' => $eventPresensi->id,
                        'nama_event' => $eventPresensi->nama_kegiatan,
                        'ruangan' => $r,
                        'sesi' => $sesi,
                        'is_sudah' => $isSudah,
                        'is_bebas_kbm' => false,
                        'keterangan_bebas_kbm' => null,
                        'hadir' => $countHadir,
                        'sakit' => $countSakit,
                        'izin' => $countIzin,
                        'alpha' => $countAlpha,
                        'dispensasi' => $countDispensasi,
                        'total_murid' => $countTotal,
                        'waktu_update' => $lastUpdated ? Carbon::parse($lastUpdated)->format('H:i') : null,
                    ];
                }
            }

            $persenSelesai = $totalSesi > 0 ? round(($totalSudahAbsen / $totalSesi) * 100, 1) : 0;

            return view('presensi-murid.progres', [
                'ruangans' => $semuaRuangan,
                'tanggal' => $tanggal,
                'ruangan_id' => $ruangan_id,
                'status_filter' => $status_filter,
                'sesi_filter' => $sesi_filter,
                'hari_ini' => $hari_ini,
                'isLibur' => false,
                'keteranganLibur' => null,
                'isUjian' => false,
                'namaUjian' => null,
                'ujianId' => null,
                'isEvent' => true,
                'eventInfo' => $eventPresensi,
                'sesiList' => $sesiList,
                'totalSesi' => $totalSesi,
                'totalSudahAbsen' => $totalSudahAbsen,
                'totalBelumAbsen' => $totalBelumAbsen,
                'persenSelesai' => $persenSelesai,
                'rekapStatus' => $rekapStatus,
                'detailProgres' => $detailProgres
            ]);
        }

        // === JIKA HARI KBM REGULER ===
        // Ambil Jadwal Hari Ini
        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran', 'ruangan.level', 'ustadz', 'ustadzs'])
            ->where('hari', $hari_ini);

        if ($ruangan_id) {
            $jadwalQuery->where('ruangan_id', $ruangan_id);
        } else {
            $jadwalQuery->whereIn('ruangan_id', $ruangans->pluck('id'));
        }

        $jadwals = $jadwalQuery->get()->sortBy([
            fn($a, $b) => ($a->ruangan?->level?->urutan_level ?? 99) <=> ($b->ruangan?->level?->urutan_level ?? 99),
            fn($a, $b) => strnatcasecmp($a->ruangan?->nama_ruangan ?? '', $b->ruangan?->nama_ruangan ?? ''),
            fn($a, $b) => (match ($a->jam_ke) {
                'Nadzoman' => 1,
                '1' => 2,
                '2' => 3,
                'Ekstra' => 4,
                default => 5
            }) <=> (match ($b->jam_ke) {
                'Nadzoman' => 1,
                '1' => 2,
                '2' => 3,
                'Ekstra' => 4,
                default => 5
            }),
        ])->values();

        // Ambil Presensi Murid pada tanggal ini
        $presensiDb = PresensiMurid::where('tanggal', $tanggal)
            ->whereIn('jadwal_pelajaran_id', $jadwals->pluck('id'))
            ->get()
            ->groupBy('jadwal_pelajaran_id');

        $totalSesi = $jadwals->count();
        $totalSudahAbsen = 0;
        $totalBelumAbsen = 0;
        $rekapStatus = [
            'Hadir' => 0,
            'Sakit' => 0,
            'Izin' => 0,
            'Alpha' => 0,
            'Dispensasi' => 0,
            'TotalMurid' => 0,
        ];

        $detailProgres = [];

        foreach ($jadwals as $j) {
            $checkSesi = HariLibur::checkBebasKbm($tanggal, $j->jam_ke, $j->ruangan_id, $j->ruangan?->level_id);
            $isBebasKbm = $checkSesi['is_libur'];
            $keteranganBebasKbm = $checkSesi['keterangan'];

            $records = $presensiDb->get($j->id, collect());
            $isSudah = $records->isNotEmpty();

            if ($isSudah) {
                $totalSudahAbsen++;
            } elseif (!$isBebasKbm) {
                $totalBelumAbsen++;
            }

            $countHadir = $records->where('status', 'Hadir')->count();
            $countSakit = $records->where('status', 'Sakit')->count();
            $countIzin = $records->where('status', 'Izin')->count();
            $countAlpha = $records->where('status', 'Alpha')->count();
            $countDispensasi = $records->where('status', 'Dispensasi')->count();
            $countTotal = $records->count();

            $rekapStatus['Hadir'] += $countHadir;
            $rekapStatus['Sakit'] += $countSakit;
            $rekapStatus['Izin'] += $countIzin;
            $rekapStatus['Alpha'] += $countAlpha;
            $rekapStatus['Dispensasi'] += $countDispensasi;
            $rekapStatus['TotalMurid'] += $countTotal;

            $lastUpdated = $records->max('updated_at');

            if ($status_filter === 'sudah' && !$isSudah) continue;
            if ($status_filter === 'belum' && $isSudah) continue;

            $detailProgres[] = [
                'jadwal' => $j,
                'ruangan' => $j->ruangan,
                'is_sudah' => $isSudah,
                'is_bebas_kbm' => $isBebasKbm,
                'keterangan_bebas_kbm' => $keteranganBebasKbm,
                'hadir' => $countHadir,
                'sakit' => $countSakit,
                'izin' => $countIzin,
                'alpha' => $countAlpha,
                'dispensasi' => $countDispensasi,
                'total_murid' => $countTotal,
                'waktu_update' => $lastUpdated ? Carbon::parse($lastUpdated)->format('H:i') : null,
            ];
        }

        $persenSelesai = $totalSesi > 0 ? round(($totalSudahAbsen / $totalSesi) * 100, 1) : 0;

        return view('presensi-murid.progres', compact(
            'ruangans',
            'tanggal',
            'ruangan_id',
            'status_filter',
            'hari_ini',
            'isLibur',
            'keteranganLibur',
            'isUjian',
            'namaUjian',
            'ujianId',
            'totalSesi',
            'totalSudahAbsen',
            'totalBelumAbsen',
            'persenSelesai',
            'rekapStatus',
            'detailProgres'
        ));
    }

    public function index(Request $request)
    {
        // Ambil data master untuk Dropdown
        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->get();
        $jamList = ['Nadzoman', '1', '2', 'Ekstra'];

        // Tangkap input pencarian dari Admin
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;
        $jam_ke = $request->jam_ke;
        $sesi = $request->sesi;

        $jadwal = null;
        $murids = collect();
        $presensiTersimpan = collect();

        // 1. Cek Event Khusus dengan Presensi Aktif
        $eventPresensi = KalendarPendidikan::getActiveEventPresensi($tanggal);
        $isEvent = ($eventPresensi != null);
        $eventInfo = $eventPresensi;

        // Variabel penanda libur & ujian
        $isLibur = false;
        $keteranganLibur = null;
        $hari_ini = null;

        $nama_hari_inggris = \Carbon\Carbon::parse($tanggal)->format('l');
        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari_ini = $mapHari[$nama_hari_inggris] ?? 'Senin';

        $ujian = \App\Models\Ujian\Ujian::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        if (!$ujian) {
            $jadwalUjianAda = \App\Models\Ujian\JadwalUjian::whereDate('tanggal_ujian', $tanggal)->first();
            if ($jadwalUjianAda) {
                $ujian = $jadwalUjianAda->ujian;
            }
        }

        $isUjian = ($ujian != null);
        $namaUjian = $ujian ? $ujian->nama_ujian : null;
        $ujianId = $ujian ? $ujian->id : null;

        // Cari Tahun Pelajaran dari Tanggal
        $bulan = BulanHijriyah::where('tanggal_mulai_masehi', '<=', $tanggal)
            ->where('tanggal_selesai_masehi', '>=', $tanggal)
            ->first();
        $tahun_pelajaran_id = $bulan ? $bulan->tahun_pelajaran_id : (Semester::where('is_active', 1)->first()?->tahun_pelajaran_id);

        // JIKA EVENT PRESENSI KHUSUS
        if ($isEvent) {
            $sesiList = $eventPresensi->tipe_presensi === 'multi_sesi'
                ? ($eventPresensi->sesi_kegiatan ?? ['Siang', 'Malam'])
                : ['Harian'];

            $sesi_dipilih = $sesi ?? ($sesiList[0] ?? 'Harian');

            if ($ruangan_id) {
                $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, 'Aktif');
                $presensiTersimpan = PresensiKegiatanMurid::where('kalendar_pendidikan_id', $eventPresensi->id)
                    ->where('ruangan_id', $ruangan_id)
                    ->where('tanggal', $tanggal)
                    ->where('sesi', $sesi_dipilih)
                    ->get()
                    ->keyBy('murid_id');
            }

            return view('presensi-murid.harian', compact(
                'ruangans',
                'jamList',
                'tanggal',
                'ruangan_id',
                'jam_ke',
                'hari_ini',
                'jadwal',
                'murids',
                'presensiTersimpan',
                'isLibur',
                'keteranganLibur',
                'isUjian',
                'namaUjian',
                'ujianId',
                'isEvent',
                'eventInfo',
                'sesiList',
                'sesi_dipilih'
            ));
        }

        // JIKA KBM REGULER
        if ($ruangan_id && $jam_ke) {
            $ruanganDipilih = Ruangan::find($ruangan_id);
            $checkBebas = \App\Models\HariLibur::checkBebasKbm($tanggal, $jam_ke, $ruangan_id, $ruanganDipilih?->level_id);
            $isLibur = $checkBebas['is_libur'];
            $keteranganLibur = $checkBebas['keterangan'];

            if (!$isLibur) {
                $jadwal = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ruangan.level'])
                    ->where('ruangan_id', $ruangan_id)
                    ->where('hari', $hari_ini)
                    ->where('jam_ke', $jam_ke)
                    ->first();

                if ($jadwal) {
                    $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, 'Aktif');
                    $presensiTersimpan = PresensiMurid::where('tanggal', $tanggal)
                        ->where('jadwal_pelajaran_id', $jadwal->id)
                        ->get()
                        ->keyBy('murid_id');
                }
            }
        }

        return view('presensi-murid.harian', compact(
            'ruangans',
            'jamList',
            'tanggal',
            'ruangan_id',
            'jam_ke',
            'hari_ini',
            'jadwal',
            'murids',
            'presensiTersimpan',
            'isLibur',
            'keteranganLibur',
            'isUjian',
            'namaUjian',
            'ujianId',
            'isEvent',
            'eventInfo'
        ));
    }

    /**
     * Modal Form AJAX Presensi Murid Cepat (Dense Form)
     */
    public function modalInput(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $jadwal_id = $request->jadwal_id;
        $kalendar_id = $request->kalendar_id;
        $ruangan_id = $request->ruangan_id;
        $sesi = $request->sesi;

        // Cari Tahun Pelajaran dari Tanggal
        $bulan = BulanHijriyah::where('tanggal_mulai_masehi', '<=', $tanggal)
            ->where('tanggal_selesai_masehi', '>=', $tanggal)
            ->first();
        $tahun_pelajaran_id = $bulan ? $bulan->tahun_pelajaran_id : (Semester::where('is_active', 1)->first()?->tahun_pelajaran_id);

        // Jika Event Khusus
        if ($kalendar_id && $ruangan_id) {
            $event = KalendarPendidikan::findOrFail($kalendar_id);
            $ruangan = Ruangan::with('level')->findOrFail($ruangan_id);
            $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahun_pelajaran_id, 'Aktif');
            $presensiTersimpan = PresensiKegiatanMurid::where('kalendar_pendidikan_id', $event->id)
                ->where('ruangan_id', $ruangan->id)
                ->where('tanggal', $tanggal)
                ->where('sesi', $sesi ?? 'Harian')
                ->get()
                ->keyBy('murid_id');

            return view('presensi-murid.modal_input', [
                'isEvent' => true,
                'event' => $event,
                'ruangan' => $ruangan,
                'sesi' => $sesi ?? 'Harian',
                'tanggal' => $tanggal,
                'murids' => $murids,
                'presensiTersimpan' => $presensiTersimpan,
                'isBebasKbm' => false,
                'keteranganBebasKbm' => null,
            ]);
        }

        // Regular KBM
        $jadwal = JadwalPelajaran::with(['mataPelajaran', 'ruangan.level', 'ustadz', 'ustadzs'])->findOrFail($jadwal_id);

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($jadwal->ruangan_id, $tahun_pelajaran_id, 'Aktif');

        $presensiTersimpan = PresensiMurid::where('tanggal', $tanggal)
            ->where('jadwal_pelajaran_id', $jadwal->id)
            ->get()
            ->keyBy('murid_id');

        $checkBebas = \App\Models\HariLibur::checkBebasKbm($tanggal, $jadwal->jam_ke, $jadwal->ruangan_id, $jadwal->ruangan?->level_id);
        $isBebasKbm = $checkBebas['is_libur'];
        $keteranganBebasKbm = $checkBebas['keterangan'];

        return view('presensi-murid.modal_input', compact(
            'jadwal',
            'tanggal',
            'murids',
            'presensiTersimpan',
            'isBebasKbm',
            'keteranganBebasKbm'
        ));
    }

    // ==========================================
    // FUNGSI SIMPAN KHUSUS HARIAN
    // ==========================================
    public function storeHarian(Request $request)
    {
        $jadwal_id = $request->jadwal_pelajaran_id;
        $tanggal = $request->tanggal;
        $dataPresensi = $request->presensi;
        $isEvent = $request->is_event;
        $kalendarId = $request->kalendar_pendidikan_id;
        $ruanganId = $request->ruangan_id;
        $sesi = $request->sesi;

        if (!$dataPresensi) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada data presensi yang diproses.'
                ], 422);
            }
            return back()->with('error', 'Tidak ada data presensi yang diproses.');
        }

        // JIKA EVENT PRESENSI
        if ($isEvent || $kalendarId) {
            foreach ($dataPresensi as $murid_id => $status) {
                if (!empty($status)) {
                    PresensiKegiatanMurid::updateOrCreate(
                        [
                            'kalendar_pendidikan_id' => $kalendarId,
                            'tanggal'                => $tanggal,
                            'sesi'                   => $sesi ?? 'Harian',
                            'ruangan_id'             => $ruanganId,
                            'murid_id'               => $murid_id,
                        ],
                        [
                            'status'       => $status,
                            'diinput_oleh' => Auth::id(),
                        ]
                    );
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data presensi kegiatan murid berhasil disimpan!'
                ], 200);
            }

            return back()->with('success', 'Data presensi kegiatan murid berhasil disimpan!');
        }

        // Cari Semester Berdasarkan Tanggal (Regular KBM)
        $semester = Semester::where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();

        if (!$semester) {
            $semester = Semester::where('is_active', 1)->first() ?? Semester::latest('id')->first();
        }

        $semesterId = $semester ? $semester->id : null;

        foreach ($dataPresensi as $murid_id => $status) {
            if (!empty($status)) {
                PresensiMurid::updateOrCreate(
                    [
                        'jadwal_pelajaran_id' => $jadwal_id,
                        'murid_id' => $murid_id,
                        'tanggal' => $tanggal
                    ],
                    [
                        'status' => $status,
                        'semester_id' => $semesterId
                    ]
                );
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data presensi murid berhasil disimpan!'
            ], 200);
        }

        return back()->with('success', 'Data presensi berhasil disimpan!');
    }

    // ==========================================
    // 2. OPSI BULANAN (Leger 1-30 Admin Mode)
    // ==========================================
    public function bulanan(Request $request)
    {
        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $bulans = BulanHijriyah::orderBy('urutan')->get();
        $jamList = ['Nadzoman', '1', '2', 'Ekstra'];

        $bulan_id = $request->bulan_id;
        $ruangan_id = $request->ruangan_id;
        $jam_ke = $request->jam_ke;

        $dates = [];
        $matrix = [];
        $murids = collect();
        $bulanTerpilih = null;

        if ($bulan_id && $ruangan_id && $jam_ke) {
            $dataBulanan = $this->presensiService->hitungMatriksBulanan($bulan_id, $ruangan_id, $jam_ke);
            $bulanTerpilih = $dataBulanan['bulanTerpilih'];
            $murids = $dataBulanan['murids'];
            $dates = $dataBulanan['dates'];
            $matrix = $dataBulanan['matrix'];
        }

        return view('presensi-murid.bulanan', compact(
            'ruangans',
            'bulans',
            'jamList',
            'bulan_id',
            'ruangan_id',
            'jam_ke',
            'dates',
            'matrix',
            'murids',
            'bulanTerpilih'
        ));
    }

    // ==========================================
    // UPDATE FUNGSI SIMPAN LEGER BULANAN
    // ==========================================
    public function storeBulanan(Request $request)
    {
        $dataPresensi = $request->input('presensi');
        $bulan_id = $request->bulan_id;
        $ruangan_id = $request->ruangan_id;
        $jam_ke = $request->jam_ke;

        if (!$dataPresensi || !$bulan_id || !$ruangan_id || !$jam_ke) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data presensi bulanan tidak lengkap.'
                ], 422);
            }
            return back()->with('error', 'Data presensi bulanan tidak lengkap.');
        }

        try {
            $this->presensiService->simpanPresensiBulanan(
                $dataPresensi,
                $bulan_id,
                $ruangan_id,
                $jam_ke,
                \Illuminate\Support\Facades\Auth::id()
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data rekap bulanan berhasil disimpan!'
                ], 200);
            }

            return back()->with('success', 'Data rekap bulanan berhasil disimpan!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan rekap bulanan: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Gagal menyimpan rekap bulanan: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 3. REKAPITULASI PRESENSI
    // ==========================================
    public function rekap(Request $request)
    {
        $semesters = Semester::with('tahunPelajaran')->orderBy('id', 'desc')->get();
        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();

        $semester_id = $request->semester_id;
        $ruangan_id = $request->ruangan_id;
        $bulan_id = $request->bulan_id;
        $jadwal_pelajaran_id = $request->jadwal_pelajaran_id;

        // Ambil daftar bulan yang bersinggungan dengan semester
        $bulans = collect();
        if ($semester_id) {
            $semesterDicari = Semester::find($semester_id);

            if ($semesterDicari && $semesterDicari->tanggal_mulai && $semesterDicari->tanggal_selesai) {
                $bulans = BulanHijriyah::where('tahun_pelajaran_id', $semesterDicari->tahun_pelajaran_id)
                    ->where('tanggal_selesai_masehi', '>=', $semesterDicari->tanggal_mulai)
                    ->where('tanggal_mulai_masehi', '<=', $semesterDicari->tanggal_selesai)
                    ->orderBy('urutan')
                    ->get();
            }
        }

        $murids = collect();
        $rekap = [];
        $rekapPerJadwal = [];
        $jadwalsRuangan = collect();
        $konfig = PengaturanAkademik::first();
        $semesterTerpilih = null;
        $bulanTerpilih = null;
        $ruanganTerpilih = null;
        $jadwalTerpilih = null;

        if ($ruangan_id) {
            $ruanganTerpilih = Ruangan::with(['level', 'waliRuangan'])->find($ruangan_id);
            $jadwalsRuangan = JadwalPelajaran::with(['mataPelajaran', 'ustadz'])
                ->where('ruangan_id', $ruangan_id)
                ->orderByRaw("FIELD(hari, 'Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis')")
                ->orderBy('jam_ke')
                ->get();
        }

        if ($semester_id && $ruangan_id) {
            $semesterTerpilih = Semester::findOrFail($semester_id);
            $tahun_pelajaran_id = $semesterTerpilih->tahun_pelajaran_id;

            if ($bulan_id) {
                $bulanTerpilih = BulanHijriyah::find($bulan_id);
            }

            if ($jadwal_pelajaran_id) {
                $jadwalTerpilih = JadwalPelajaran::with(['mataPelajaran', 'ustadz'])->find($jadwal_pelajaran_id);
            }

            // Ambil murid yang aktif di kelas & tahun ajaran tersebut
            $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, 'Aktif');

            // QUERY PRESENSI (Dinamis: Bisa se-semester, bisa per bulan, bisa per jadwal pelajaran)
            $presensiQuery = PresensiMurid::whereIn('murid_id', $murids->pluck('id'))
                ->where('semester_id', $semester_id);

            if ($bulan_id && $bulanTerpilih && isset($bulanTerpilih->tanggal_mulai_masehi) && isset($bulanTerpilih->tanggal_selesai_masehi)) {
                $presensiQuery->whereBetween('tanggal', [
                    $bulanTerpilih->tanggal_mulai_masehi,
                    $bulanTerpilih->tanggal_selesai_masehi
                ]);
            }

            if ($jadwal_pelajaran_id) {
                $presensiQuery->where('jadwal_pelajaran_id', $jadwal_pelajaran_id);
            }

            $presensiDb = $presensiQuery->get();

            foreach ($murids as $murid) {
                $pMurid = $presensiDb->where('murid_id', $murid->id);

                $h = $pMurid->where('status', 'Hadir')->count();
                $s = $pMurid->where('status', 'Sakit')->count();
                $i = $pMurid->where('status', 'Izin')->count();
                $a = $pMurid->where('status', 'Alpha')->count();
                $d = $pMurid->where('status', 'Dispensasi')->count();

                $poinAlpha = $a * ($konfig->poin_alpha ?? 1);
                $poinIzin = $i * ($konfig->poin_izin ?? 0.16);
                $totalPoin = $poinAlpha + $poinIzin;
                $totalPertemuan = $h + $s + $i + $a + $d;
                $persenHadir = $totalPertemuan > 0 ? round(($h / $totalPertemuan) * 100, 1) : 0;

                $rekap[$murid->id] = [
                    'H' => $h,
                    'S' => $s,
                    'I' => $i,
                    'A' => $a,
                    'D' => $d,
                    'total_pertemuan' => $totalPertemuan,
                    'persen_hadir' => $persenHadir,
                    'akumulasi_poin' => round($totalPoin, 2)
                ];
            }

            // Hitung Rekapitulasi Ringkasan Per Jadwal Pelajaran di Ruangan ini (jika melihat semua jadwal)
            if (!$jadwal_pelajaran_id && $jadwalsRuangan->isNotEmpty()) {
                $presensiSemuaQuery = PresensiMurid::whereIn('murid_id', $murids->pluck('id'))
                    ->where('semester_id', $semester_id)
                    ->whereIn('jadwal_pelajaran_id', $jadwalsRuangan->pluck('id'));

                if ($bulan_id && $bulanTerpilih && isset($bulanTerpilih->tanggal_mulai_masehi) && isset($bulanTerpilih->tanggal_selesai_masehi)) {
                    $presensiSemuaQuery->whereBetween('tanggal', [
                        $bulanTerpilih->tanggal_mulai_masehi,
                        $bulanTerpilih->tanggal_selesai_masehi
                    ]);
                }
                $presensiSemuaDb = $presensiSemuaQuery->get();

                foreach ($jadwalsRuangan as $j) {
                    $pJadwal = $presensiSemuaDb->where('jadwal_pelajaran_id', $j->id);

                    $hJadwal = $pJadwal->where('status', 'Hadir')->count();
                    $sJadwal = $pJadwal->where('status', 'Sakit')->count();
                    $iJadwal = $pJadwal->where('status', 'Izin')->count();
                    $aJadwal = $pJadwal->where('status', 'Alpha')->count();
                    $dJadwal = $pJadwal->where('status', 'Dispensasi')->count();
                    $totJadwal = $hJadwal + $sJadwal + $iJadwal + $aJadwal + $dJadwal;
                    $persenJadwal = $totJadwal > 0 ? round(($hJadwal / $totJadwal) * 100, 1) : 0;
                    $totalSesi = $pJadwal->pluck('tanggal')->unique()->count();

                    $rekapPerJadwal[] = [
                        'jadwal' => $j,
                        'nama_mapel' => $j->mataPelajaran->nama_mapel ?? '-',
                        'ustadz' => $j->ustadz->nama_lengkap ?? $j->ustadz->nama ?? '-',
                        'hari' => $j->hari,
                        'jam_ke' => $j->jam_ke,
                        'total_sesi' => $totalSesi,
                        'H' => $hJadwal,
                        'S' => $sJadwal,
                        'I' => $iJadwal,
                        'A' => $aJadwal,
                        'D' => $dJadwal,
                        'total_log' => $totJadwal,
                        'persen_hadir' => $persenJadwal
                    ];
                }
            }
        }

        return view('presensi-murid.rekap', compact(
            'semesters',
            'ruangans',
            'bulans',
            'jadwalsRuangan',
            'semester_id',
            'ruangan_id',
            'bulan_id',
            'jadwal_pelajaran_id',
            'murids',
            'rekap',
            'rekapPerJadwal',
            'semesterTerpilih',
            'bulanTerpilih',
            'ruanganTerpilih',
            'jadwalTerpilih',
            'konfig'
        ));
    }

    public function cetakRekap(Request $request)
    {
        $semester_id = $request->semester_id;
        $ruangan_id = $request->ruangan_id;
        $bulan_id = $request->bulan_id;
        $jadwal_pelajaran_id = $request->jadwal_pelajaran_id;

        // Jika tidak ada data yang dipilih, tendang balik
        if (!$semester_id || !$ruangan_id) {
            return back()->with('error', 'Pilih semester dan ruangan terlebih dahulu untuk mencetak.');
        }

        $semesterTerpilih = Semester::with('tahunPelajaran')->findOrFail($semester_id);
        $ruanganTerpilih = Ruangan::findOrFail($ruangan_id);
        $konfig = PengaturanAkademik::first();
        $bulanTerpilih = $bulan_id ? BulanHijriyah::find($bulan_id) : null;
        $jadwalTerpilih = $jadwal_pelajaran_id ? JadwalPelajaran::with(['mataPelajaran', 'ustadz'])->find($jadwal_pelajaran_id) : null;
        $tahun_pelajaran_id = $semesterTerpilih->tahun_pelajaran_id;

        // Ambil murid
        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id);

        // Query Presensi
        $presensiQuery = PresensiMurid::whereIn('murid_id', $murids->pluck('id'))
            ->where('semester_id', $semester_id);

        // Jika ada filter bulan
        if ($bulan_id && $bulanTerpilih && isset($bulanTerpilih->tanggal_mulai_masehi) && isset($bulanTerpilih->tanggal_selesai_masehi)) {
            $presensiQuery->whereBetween('tanggal', [
                $bulanTerpilih->tanggal_mulai_masehi,
                $bulanTerpilih->tanggal_selesai_masehi
            ]);
        }

        if ($jadwal_pelajaran_id) {
            $presensiQuery->where('jadwal_pelajaran_id', $jadwal_pelajaran_id);
        }

        $presensiDb = $presensiQuery->get();

        // Hitung Data
        $rekap = [];
        foreach ($murids as $murid) {
            $pMurid = $presensiDb->where('murid_id', $murid->id);

            $h = $pMurid->where('status', 'Hadir')->count();
            $s = $pMurid->where('status', 'Sakit')->count();
            $i = $pMurid->where('status', 'Izin')->count();
            $a = $pMurid->where('status', 'Alpha')->count();
            $d = $pMurid->where('status', 'Dispensasi')->count();

            $poinAlpha = $a * ($konfig->poin_alpha ?? 1);
            $poinIzin = $i * ($konfig->poin_izin ?? 0.16);
            $totalPertemuan = $h + $s + $i + $a + $d;
            $persenHadir = $totalPertemuan > 0 ? round(($h / $totalPertemuan) * 100, 1) : 0;

            $rekap[$murid->id] = [
                'H' => $h,
                'S' => $s,
                'I' => $i,
                'A' => $a,
                'D' => $d,
                'total_pertemuan' => $totalPertemuan,
                'persen_hadir' => $persenHadir,
                'akumulasi_poin' => round($poinAlpha + $poinIzin, 2)
            ];
        }

        return view('cetak-baru.cetak_rekap_presensi_murid', compact(
            'semesterTerpilih',
            'ruanganTerpilih',
            'bulanTerpilih',
            'jadwalTerpilih',
            'murids',
            'rekap',
            'konfig'
        ));
    }
}
