<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PesertaRuanganImni;
use App\Models\Ujian\PresensiUjian;
use App\Models\Ujian\RuanganImni;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresensiImniController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Halaman Utama Presensi Ujian IMNI
     * - Tab 1: Kelas 6 IBT & 3 TSA (Berdasarkan Ruangan IMNI R1..Rn)
     * - Tab 2: Kelas 3 TPQ (Berdasarkan Ruangan Fisik Kelas 3 TPQ)
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $kategori = $request->input('kategori', 'imni'); // 'imni' (6 IBT & 3 TSA) atau 'tpq' (3 TPQ)

        // SK Panitia
        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        // =========================================================================
        // MODE A: KELAS 3 TPQ (Ruangan Fisik Khusus 3 TPQ & Ujian IMNI TPQ)
        // =========================================================================
        if ($kategori === 'tpq') {
            // Daftar Ruangan Fisik Khusus Kelas 3 TPQ
            $daftarRuanganTpq = Ruangan::with('level.tingkat')
                ->where('tahun_pelajaran_id', $selectedTahunId)
                ->whereHas('level', function ($q) {
                    $q->where('nama_level', '3 TPQ')
                      ->orWhere('id', 3);
                })
                ->orderBy('nama_ruangan', 'asc')
                ->get();

            $selectedRuanganTpqId = $request->input('ruangan_id', $daftarRuanganTpq->first()?->id);
            $selectedRuanganTpq = $daftarRuanganTpq->firstWhere('id', $selectedRuanganTpqId) ?? $daftarRuanganTpq->first();
            $selectedRuanganTpqId = $selectedRuanganTpq?->id;

            // Daftar Pelaksanaan Ujian Khusus IMNI Tingkat TPQ
            $daftarUjianTpq = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
                ->where('tipe_ujian', 'IMNI')
                ->where(function ($q) {
                    $q->where('tingkat_id', 1)
                      ->orWhere('nama_ujian', 'like', '%TPQ%');
                })
                ->orderBy('id', 'asc')
                ->get();

            $selectedUjianTpqId = $request->input('ujian_id', $daftarUjianTpq->first()?->id);
            $selectedUjianTpq = $daftarUjianTpq->firstWhere('id', $selectedUjianTpqId) ?? $daftarUjianTpq->first();
            $selectedUjianTpqId = $selectedUjianTpq?->id;

            // Jadwal Ujian TPQ
            $jadwalTpqList = collect();
            $selectedJadwalTpqId = null;
            $selectedJadwalTpq = null;

            if ($selectedUjianTpq && $selectedRuanganTpq) {
                $jadwalTpqList = JadwalUjian::with(['mataPelajaran', 'pengawas'])
                    ->where('ujian_id', $selectedUjianTpq->id)
                    ->where('level_id', $selectedRuanganTpq->level_id)
                    ->orderBy('tanggal_ujian', 'asc')
                    ->orderBy('waktu_mulai', 'asc')
                    ->get();

                $selectedJadwalTpqId = $request->input('jadwal_ujian_id', $jadwalTpqList->first()?->id);
                $selectedJadwalTpq = $jadwalTpqList->firstWhere('id', $selectedJadwalTpqId) ?? $jadwalTpqList->first();
                $selectedJadwalTpqId = $selectedJadwalTpq?->id;
            }

            // Ambil Santri Aktif Kelas 3 TPQ di Ruangan Terpilih
            $muridsTpq = collect();
            if ($selectedRuanganTpq) {
                $muridsTpq = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun(
                    $selectedRuanganTpq->id,
                    $selectedTahunId,
                    ['waliMurid.kampung']
                );
            }

            // Ambil Presensi Existing TPQ
            $presensiExistingTpq = collect();
            if ($selectedJadwalTpq && $selectedRuanganTpq) {
                $presensiExistingTpq = PresensiUjian::where('jadwal_ujian_id', $selectedJadwalTpq->id)
                    ->where('ruangan_id', $selectedRuanganTpq->id)
                    ->get()
                    ->keyBy('murid_id');
            }

            // Hitung Statistik Presensi TPQ
            $totalTpq = $muridsTpq->count();
            $hadirTpq = 0; $sakitTpq = 0; $izinTpq = 0; $alphaTpq = 0; $belumTpq = 0;
            foreach ($muridsTpq as $m) {
                $st = $presensiExistingTpq->get($m->id)?->status;
                if ($st === 'Hadir' || $st === 'Dispensasi') $hadirTpq++;
                elseif ($st === 'Sakit') $sakitTpq++;
                elseif ($st === 'Izin') $izinTpq++;
                elseif ($st === 'Alpha') $alphaTpq++;
                else $belumTpq++;
            }

            return view('ujian.panitia-imni.presensi.index', compact(
                'daftarTahun',
                'selectedTahun',
                'selectedTahunId',
                'kategori',
                'daftarRuanganTpq',
                'selectedRuanganTpqId',
                'selectedRuanganTpq',
                'daftarUjianTpq',
                'selectedUjianTpqId',
                'selectedUjianTpq',
                'jadwalTpqList',
                'selectedJadwalTpqId',
                'selectedJadwalTpq',
                'muridsTpq',
                'presensiExistingTpq',
                'totalTpq',
                'hadirTpq',
                'sakitTpq',
                'izinTpq',
                'alphaTpq',
                'belumTpq',
                'ketuaPanitia'
            ));
        }

        // =========================================================================
        // MODE B: KELAS 6 IBT & 3 TSA (Berdasarkan Ruangan IMNI R1..Rn)
        // =========================================================================

        // Ambil Ujian IMNI Tahun Ini
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        // Daftar Jadwal Ujian IMNI (6 IBT & 3 TSA)
        $jadwalUjianList = JadwalUjian::with(['mataPelajaran', 'level.tingkat'])
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Daftar Hari Ujian yang Unik
        $daftarHariUjian = $jadwalUjianList->groupBy(function ($j) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
        })->values()->map(function ($items, $index) {
            $tgl = Carbon::parse($items->first()->tanggal_ujian)->format('Y-m-d');
            $cDate = Carbon::parse($tgl)->locale('id');

            $jadwalIbt = $items->filter(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'))->values();
            $jadwalTsa = $items->filter(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'))->values();

            return (object) [
                'hari_ke'        => $index + 1,
                'tanggal'        => $tgl,
                'nama_hari'      => $cDate->translatedFormat('l'),
                'tanggal_format' => $cDate->translatedFormat('d F Y'),
                'jadwal_ibt'     => $jadwalIbt,
                'jadwal_tsa'     => $jadwalTsa,
            ];
        });

        // Tentukan Tanggal Ujian yang Terpilih
        $selectedTanggal = $request->input('tanggal_ujian');
        if (!$selectedTanggal || !$daftarHariUjian->contains('tanggal', $selectedTanggal)) {
            $selectedTanggal = $daftarHariUjian->first()?->tanggal ?? Carbon::today()->format('Y-m-d');
        }

        // Cari Info Hari Ke-X
        $selectedHariInfo = $daftarHariUjian->firstWhere('tanggal', $selectedTanggal) ?? (object) [
            'hari_ke'        => 1,
            'tanggal'        => $selectedTanggal,
            'nama_hari'      => Carbon::parse($selectedTanggal)->locale('id')->translatedFormat('l'),
            'tanggal_format' => Carbon::parse($selectedTanggal)->locale('id')->translatedFormat('d F Y'),
            'jadwal_ibt'     => collect(),
            'jadwal_tsa'     => collect(),
        ];

        // Daftar Master Ruangan IMNI
        $daftarRuanganImni = RuanganImni::with(['ruanganFisik.level.tingkat', 'penanggungJawabRuangan.ustadz'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get();

        $selectedRuanganId = $request->input('ruangan_imni_id', $daftarRuanganImni->first()?->id);
        $selectedRuangan = $daftarRuanganImni->firstWhere('id', $selectedRuanganId) ?? $daftarRuanganImni->first();
        $selectedRuanganId = $selectedRuangan?->id;

        // Ambil Jadwal Ujian pada Tanggal Terpilih (6 IBT & 3 TSA)
        $jadwalHariIni = $jadwalUjianList->filter(function ($j) use ($selectedTanggal) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d') === $selectedTanggal;
        })->values();

        $jadwalIbtList = $jadwalHariIni->filter(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'))->values();
        $jadwalTsaList = $jadwalHariIni->filter(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'))->values();

        // Ambil Peserta yang Diplot di Ruangan IMNI pada Tanggal Tersebut
        $pesertaPlotted = collect();
        $presensiExisting = collect();

        if ($selectedRuangan) {
            $pesertaPlotted = PesertaRuanganImni::with([
                'pesertaImni.murid.waliMurid.kampung',
                'pesertaImni.tingkat',
                'pesertaImni.level',
                'murid',
            ])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('ruangan_imni_id', $selectedRuangan->id)
            ->where('tanggal_ujian', $selectedTanggal)
            ->orderBy('nomor_meja', 'asc')
            ->get();

            if ($pesertaPlotted->isEmpty()) {
                $pesertaPlotted = PesertaRuanganImni::with([
                    'pesertaImni.murid.waliMurid.kampung',
                    'pesertaImni.tingkat',
                    'pesertaImni.level',
                    'murid',
                ])
                ->where('tahun_pelajaran_id', $selectedTahunId)
                ->where('ruangan_imni_id', $selectedRuangan->id)
                ->orderBy('nomor_meja', 'asc')
                ->get()
                ->unique('peserta_imni_id')
                ->values();
            }

            // Ambil Presensi Murid Terdaftar
            $targetJadwalIds = $jadwalHariIni->pluck('id')->toArray();
            $presensiExisting = PresensiUjian::where('ruangan_imni_id', $selectedRuangan->id)
                ->where('tanggal_ujian', $selectedTanggal)
                ->get()
                ->keyBy('murid_id');

            if ($presensiExisting->isEmpty() && !empty($targetJadwalIds)) {
                $presensiExisting = PresensiUjian::whereIn('jadwal_ujian_id', $targetJadwalIds)
                    ->where('ruangan_id', $selectedRuangan->ruangan_id)
                    ->get()
                    ->keyBy('murid_id');
            }
        }

        // Metrik Statistik Presensi Ruangan Terpilih
        $totalPeserta = $pesertaPlotted->count();
        $countHadir = 0; $countSakit = 0; $countIzin = 0; $countAlpha = 0; $countDispensasi = 0; $countBelumDiisi = 0;

        foreach ($pesertaPlotted as $p) {
            $muridId = $p->murid_id ?? $p->pesertaImni?->murid_id;
            $status = $presensiExisting->get($muridId)?->status;
            if ($status === 'Hadir') $countHadir++;
            elseif ($status === 'Sakit') $countSakit++;
            elseif ($status === 'Izin') $countIzin++;
            elseif ($status === 'Alpha') $countAlpha++;
            elseif ($status === 'Dispensasi') $countDispensasi++;
            else $countBelumDiisi++;
        }

        $persenHadir = ($totalPeserta > 0 && ($countHadir + $countDispensasi) > 0) ? round((($countHadir + $countDispensasi) / $totalPeserta) * 100, 1) : 0;

        return view('ujian.panitia-imni.presensi.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'kategori',
            'daftarHariUjian',
            'selectedTanggal',
            'selectedHariInfo',
            'daftarRuanganImni',
            'selectedRuanganId',
            'selectedRuangan',
            'jadwalIbtList',
            'jadwalTsaList',
            'pesertaPlotted',
            'presensiExisting',
            'totalPeserta',
            'countHadir',
            'countSakit',
            'countIzin',
            'countAlpha',
            'countDispensasi',
            'countBelumDiisi',
            'persenHadir',
            'ketuaPanitia'
        ));
    }

    /**
     * Simpan Presensi Santri (Mendukung Ruangan IMNI & Kelas 3 TPQ)
     */
    public function store(Request $request)
    {
        $kategori = $request->input('kategori', 'imni');

        // =========================================================================
        // SIMPAN PRESENSI KELAS 3 TPQ (Ruangan Fisik)
        // =========================================================================
        if ($kategori === 'tpq' || ($request->filled('jadwal_ujian_id') && $request->filled('ruangan_id') && !$request->filled('ruangan_imni_id'))) {
            $request->validate([
                'ujian_id'        => 'required|exists:ujians,id',
                'ruangan_id'      => 'required|exists:ruangans,id',
                'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
                'presensi'        => 'required|array',
            ]);

            $ujianId = $request->ujian_id;
            $ruanganId = $request->ruangan_id;
            $jadwalId = $request->jadwal_ujian_id;

            DB::beginTransaction();
            try {
                $savedCount = 0;
                // Simpan Presensi Santri TPQ
                foreach ($request->presensi as $muridId => $item) {
                    if (empty($item['status'])) {
                        continue;
                    }
                    $status = $item['status'];
                    $catatan = $item['catatan'] ?? null;

                    PresensiUjian::updateOrCreate(
                        [
                            'jadwal_ujian_id' => $jadwalId,
                            'ruangan_id'      => $ruanganId,
                            'murid_id'        => $muridId,
                        ],
                        [
                            'ujian_id'     => $ujianId,
                            'status'       => $status,
                            'catatan'      => $catatan,
                            'diinput_oleh' => Auth::id(),
                        ]
                    );
                    $savedCount++;
                }

                DB::commit();

                $msg = "Presensi {$savedCount} murid Kelas 3 TPQ berhasil disimpan.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'success', 'message' => $msg], 200);
                }
                return redirect()->back()->with('success', $msg);
            } catch (\Throwable $e) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
            }
        }

        // =========================================================================
        // SIMPAN PRESENSI KELAS 6 IBT & 3 TSA (Ruangan IMNI R1..Rn)
        // =========================================================================
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tanggal_ujian'      => 'required|date',
            'ruangan_imni_id'    => 'required|exists:ruangan_imnis,id',
            'presensi'           => 'required|array',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tanggal = Carbon::parse($request->tanggal_ujian)->format('Y-m-d');
        $ruanganImniId = $request->ruangan_imni_id;
        $ruanganImni = RuanganImni::findOrFail($ruanganImniId);

        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        $jadwalHariIni = JadwalUjian::with('level.tingkat')
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereDate('tanggal_ujian', $tanggal)
            ->get();

        $jadwalIbt = $jadwalHariIni->firstWhere(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'));
        $jadwalTsa = $jadwalHariIni->firstWhere(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'));

        $defaultUjianId = $imniUjianIds->first();

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $hadirCount = 0;
            $sakitCount = 0;
            $izinCount = 0;
            $alphaCount = 0;

            // Simpan Presensi Tiap Peserta Santri (6 IBT & 3 TSA)
            foreach ($request->presensi as $muridId => $item) {
                if (empty($item['status'])) {
                    continue;
                }
                $status = $item['status'];
                $catatan = $item['catatan'] ?? null;
                $tingkatId = $item['tingkat_id'] ?? null;

                $jadwalTarget = ($tingkatId == 3) ? ($jadwalTsa ?? $jadwalIbt) : ($jadwalIbt ?? $jadwalTsa);
                $jadwalId = $jadwalTarget?->id ?? 0;
                $ujianId = $jadwalTarget?->ujian_id ?? $defaultUjianId;

                PresensiUjian::updateOrCreate(
                    [
                        'jadwal_ujian_id' => $jadwalId ?: 1,
                        'ruangan_id'      => $ruanganImni->ruangan_id ?: 1,
                        'murid_id'        => $muridId,
                    ],
                    [
                        'ujian_id'        => $ujianId ?: 1,
                        'ruangan_imni_id' => $ruanganImniId,
                        'tanggal_ujian'   => $tanggal,
                        'status'          => $status,
                        'catatan'         => $catatan,
                        'diinput_oleh'    => Auth::id(),
                    ]
                );

                $savedCount++;
                if ($status === 'Hadir') $hadirCount++;
                elseif ($status === 'Sakit') $sakitCount++;
                elseif ($status === 'Izin') $izinCount++;
                elseif ($status === 'Alpha') $alphaCount++;
            }

            DB::commit();

            $msg = "Presensi {$savedCount} santri Ruangan " . ($ruanganImni->nama_ruangan_imni ?: 'IMNI') . " berhasil disimpan ({$hadirCount} Hadir).";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'success', 'message' => $msg], 200);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan presensi: ' . $e->getMessage());
        }
    }
}
