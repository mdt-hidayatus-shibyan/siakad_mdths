<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Models\BulanHijriyah;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\PengaturanAkademik;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Repositories\MuridRuanganRepository;
use App\Services\PresensiMuridService;
use Illuminate\Http\Request;


class PresensiMuridController extends Controller
{
    protected $muridRuanganRepo;
    protected $presensiService;

    public function __construct(MuridRuanganRepository $muridRuanganRepo, PresensiMuridService $presensiService)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
        $this->presensiService = $presensiService;
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

        $jadwal = null;
        $murids = collect();
        $presensiTersimpan = collect();

        // Variabel penanda libur
        $isLibur = false;
        $keteranganLibur = null;
        $hari_ini = null;

        // Jika Admin sudah memilih Ruangan dan Jam, kita cari data murid & jadwalnya
        if ($ruangan_id && $jam_ke) {

            // ==========================================================
            // PENERJEMAH HARI: Memastikan "Minggu" atau "Sunday" menjadi "Ahad"
            // ==========================================================
            $nama_hari_inggris = \Carbon\Carbon::parse($tanggal)->format('l'); // Menghasilkan: Sunday, Monday, dll

            $mapHari = [
                'Sunday'    => 'Ahad',
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu'
            ];

            // Timpa variabel $hari_ini dengan hasil terjemahan yang benar
            $hari_ini = $mapHari[$nama_hari_inggris];
            // ==========================================================

            // ==========================================================
            // ==========================================================
            // CEK HARI LIBUR & JUMAT
            // ==========================================================
            $libur = \App\Models\HariLibur::where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->first();

            if ($libur) {
                // Jika masuk rentang kalender libur madrasah
                $isLibur = true;
                $keteranganLibur = $libur->keterangan;
            } elseif ($hari_ini === 'Jumat') {
                // Jika hari Jumat (Libur rutin madrasah)
                $isLibur = true;
                $keteranganLibur = 'Libur Rutin (Jumat)';
            }
            // ==========================================================

            // ==========================================================
            // CEK TANGGAL UJIAN MADRASAH
            // ==========================================================
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
            // ==========================================================

            // JIKA TIDAK LIBUR, BARU EKSEKUSI PENCARIAN JADWAL DAN MURID
            if (!$isLibur) {
                // Cari jadwal spesifik di kelas tersebut, hari tersebut, dan jam tersebut
                $jadwal = JadwalPelajaran::with(['mataPelajaran', 'ustadz'])
                    ->where('ruangan_id', $ruangan_id)
                    ->where('hari', $hari_ini)
                    ->where('jam_ke', $jam_ke)
                    ->first();

                // Jika jadwalnya ada, panggil data murid kelas tersebut
                if ($jadwal) {

                    // ==========================================================
                    // KECERDASAN OTOMATIS: Cari Tahun Pelajaran dari Tanggal
                    // ==========================================================
                    // PERBAIKAN 1: Hapus ->with('semester')
                    $bulan = BulanHijriyah::where('tanggal_mulai_masehi', '<=', $tanggal)
                        ->where('tanggal_selesai_masehi', '>=', $tanggal)
                        ->first();

                    // PERBAIKAN 2: Langsung ambil tahun_pelajaran_id dari variabel $bulan
                    if ($bulan) {
                        $tahun_pelajaran_id = $bulan->tahun_pelajaran_id;
                    } else {
                        // Fallback: Jika tanggal di luar rentang, ambil dari semester aktif
                        $semesterAktif = Semester::where('is_active', 1)->first();
                        $tahun_pelajaran_id = $semesterAktif ? $semesterAktif->tahun_pelajaran_id : null;
                    }
                    // ==========================================================

                    // Panggil murid menggunakan relasi Many-to-Many ke tabel pivot
                    $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, 'Aktif');

                    // Ambil presensi yang mungkin sudah pernah diinput sebelumnya (agar bisa di-edit)
                    $presensiTersimpan = PresensiMurid::where('tanggal', $tanggal)
                        ->where('jadwal_pelajaran_id', $jadwal->id)
                        ->get()
                        ->keyBy('murid_id'); // Kunci array pakai ID murid agar mudah dicari di View
                }
            }
        } else {
            // Cek ujian jika ruangan / jam belum dipilih tapi tanggal sudah ada
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
            'isLibur', // Tambahan
            'keteranganLibur', // Tambahan
            'isUjian',
            'namaUjian',
            'ujianId'
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

        if (!$dataPresensi) {
            return back()->with('error', 'Tidak ada data presensi yang diproses.');
        }

        // Cari Semester Berdasarkan Tanggal
        $semester = Semester::where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();

        if (!$semester) {
            $semester = Semester::where('is_active', 1)->first() ?? Semester::latest('id')->first();
        }

        $semesterId = $semester ? $semester->id : null;

        foreach ($dataPresensi as $murid_id => $status) {
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

            return back()->with('success', 'Data rekap bulanan berhasil disimpan!');
        } catch (\Exception $e) {
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
