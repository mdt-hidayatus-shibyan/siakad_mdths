<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PesertaImni;
use App\Models\Ujian\Ujian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NilaiImniController extends Controller
{
    /**
     * Halaman Utama Input & Rekapitulasi Nilai Mapel IMNI
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $daftarTingkat = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();
        $daftarLevelAkhir = Level::where('is_active', true)->whereIn('urutan_level', [3, 9, 12])->orderBy('tingkat_id', 'asc')->get();

        $tipeUjian = $request->input('tipe_ujian', 'IMNI');
        $tingkatId = $request->input('tingkat_id');
        $levelId   = $request->input('level_id');

        // 1. Daftar Ruangan Ujian IMNI yang memiliki peserta terdaftar
        $ruanganQuery = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)
            ->whereNotNull('ruangan_ujian_id');

        if ($tingkatId) {
            $ruanganQuery->where('tingkat_id', $tingkatId);
        }
        if ($levelId) {
            $ruanganQuery->where('level_id', $levelId);
        }

        $ruanganIdsInImni = $ruanganQuery->distinct()->pluck('ruangan_ujian_id');

        $daftarRuanganUjian = Ruangan::with('level')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->whereIn('id', $ruanganIdsInImni)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        if ($daftarRuanganUjian->isEmpty()) {
            $daftarRuanganUjian = Ruangan::with('level')
                ->where('tahun_pelajaran_id', $selectedTahunId)
                ->when($levelId, fn($q) => $q->where('level_id', $levelId))
                ->orderBy('nama_ruangan', 'asc')
                ->get();
        }

        $selectedRuanganId = $request->input('ruangan_ujian_id', $daftarRuanganUjian->first()?->id);
        $selectedRuangan = $daftarRuanganUjian->firstWhere('id', $selectedRuanganId) ?? $daftarRuanganUjian->first();

        // 2. Peserta IMNI & Jadwal Mapel
        $pesertas = collect();
        $jadwalUjians = collect();
        $selectedJadwalId = null;
        $selectedJadwal = null;
        $nilaiExisting = collect();

        if ($selectedRuangan) {
            $pesertaQuery = PesertaImni::with([
                'murid.waliMurid',
                'tingkat',
                'level',
                'ruanganAsal',
                'ruanganUjian'
            ])->where('tahun_pelajaran_id', $selectedTahunId)
              ->where('ruangan_ujian_id', $selectedRuangan->id);

            if ($levelId) {
                $pesertaQuery->where('level_id', $levelId);
            }

            $pesertas = $pesertaQuery->orderByRaw('CAST(nomor_meja AS UNSIGNED) ASC')
                ->orderBy('nomor_peserta', 'asc')
                ->get();

            // Ambil target level ID
            $targetLevelIds = [];
            if ($levelId) {
                $targetLevelIds = [$levelId];
            } else {
                $targetLevelIds = $pesertas->pluck('level_id')->filter()->unique()->toArray();
                if (empty($targetLevelIds) && $selectedRuangan->level_id) {
                    $targetLevelIds = [$selectedRuangan->level_id];
                }
            }

            // Ambil jadwal sesuai tipe ujian dan level
            $ujianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
                ->when($tipeUjian, fn($q) => $q->where('tipe_ujian', $tipeUjian))
                ->when($tingkatId, fn($q) => $q->where(fn($qu) => $qu->whereNull('tingkat_id')->orWhere('tingkat_id', $tingkatId)))
                ->pluck('id');

            if (!empty($targetLevelIds) && $ujianIds->isNotEmpty()) {
                $jadwalUjians = JadwalUjian::with(['mataPelajaran', 'ujian.tahunPelajaran', 'level.tingkat'])
                    ->whereIn('ujian_id', $ujianIds)
                    ->whereIn('level_id', $targetLevelIds)
                    ->orderBy('tanggal_ujian', 'asc')
                    ->orderBy('waktu_mulai', 'asc')
                    ->get();
            } else {
                $jadwalUjians = collect();
            }

            $selectedJadwalId = $request->input('jadwal_ujian_id');
            $selectedJadwal = $jadwalUjians->firstWhere('id', $selectedJadwalId) ?? $jadwalUjians->first();
            $selectedJadwalId = $selectedJadwal?->id;

            // Filter peserta ke level sesuai jadwal ujian mapel tersebut jika ruangan berisi campuran jenjang
            if ($selectedJadwal && $selectedJadwal->level_id) {
                $pesertas = $pesertas->where('level_id', $selectedJadwal->level_id)->values();
            }

            if ($selectedJadwal) {
                $nilaiExisting = NilaiUjian::where('jadwal_ujian_id', $selectedJadwal->id)
                    ->where('ruangan_id', $selectedRuangan->id)
                    ->get()
                    ->keyBy('murid_id');
            }
        }

        // 3. Statistik Metrik Nilai
        $totalPeserta = $pesertas->count();
        $inputedScores = $nilaiExisting->pluck('nilai')->filter(fn($v) => !is_null($v) && is_numeric($v));
        $countTerinput = $inputedScores->count();
        $rataRata = $countTerinput > 0 ? round($inputedScores->avg(), 1) : 0;
        $nilaiTertinggi = $countTerinput > 0 ? $inputedScores->max() : 0;
        $nilaiTerendah  = $countTerinput > 0 ? $inputedScores->min() : 0;
        $persenTerinput = ($totalPeserta > 0) ? round(($countTerinput / $totalPeserta) * 100, 1) : 0;

        $kkmDefault = 65;
        $countLulusKkm = $inputedScores->filter(fn($v) => $v >= $kkmDefault)->count();
        $countDiBawahKkm = $inputedScores->filter(fn($v) => $v < $kkmDefault)->count();

        // SK Panitia
        $ketuaPanitia     = PanitiaImni::getKetua($selectedTahunId);
        $bendaharaPanitia = PanitiaImni::getBendahara($selectedTahunId);

        return view('ujian.panitia-imni.nilai.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'daftarTingkat',
            'daftarLevelAkhir',
            'tipeUjian',
            'tingkatId',
            'levelId',
            'daftarRuanganUjian',
            'selectedRuanganId',
            'selectedRuangan',
            'jadwalUjians',
            'selectedJadwalId',
            'selectedJadwal',
            'pesertas',
            'nilaiExisting',
            'totalPeserta',
            'countTerinput',
            'persenTerinput',
            'rataRata',
            'nilaiTertinggi',
            'nilaiTerendah',
            'kkmDefault',
            'countLulusKkm',
            'countDiBawahKkm',
            'ketuaPanitia',
            'bendaharaPanitia'
        ));
    }

    /**
     * Simpan Nilai Mata Pelajaran Ujian IMNI
     */
    public function store(Request $request)
    {
        $request->validate([
            'ruangan_ujian_id' => 'required|exists:ruangans,id',
            'jadwal_ujian_id'  => 'required|exists:jadwal_ujians,id',
            'nilai'            => 'required|array',
        ]);

        $ruanganId = $request->ruangan_ujian_id;
        $jadwalId = $request->jadwal_ujian_id;
        $jadwal = JadwalUjian::findOrFail($jadwalId);

        DB::beginTransaction();
        try {
            $savedCount = 0;
            foreach ($request->nilai as $muridId => $val) {
                if ($val !== '' && $val !== null) {
                    $score = max(0, min(100, (float) $val));

                    NilaiUjian::updateOrCreate(
                        [
                            'jadwal_ujian_id' => $jadwalId,
                            'ruangan_id'      => $ruanganId,
                            'murid_id'        => $muridId,
                        ],
                        [
                            'ujian_id'     => $jadwal->ujian_id,
                            'nilai'        => $score,
                            'diinput_oleh' => Auth::id(),
                            'is_published' => true,
                        ]
                    );
                    $savedCount++;
                }
            }

            DB::commit();

            $msg = "Berhasil menyimpan {$savedCount} nilai mata pelajaran {$jadwal->nama_mapel}.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Leger Nilai Mata Pelajaran IMNI Per Ruangan
     */
    public function cetakLeger(Request $request)
    {
        $request->validate([
            'ruangan_ujian_id' => 'required|exists:ruangans,id',
        ]);

        $ruanganId = $request->ruangan_ujian_id;
        $tahunId = $request->input('tahun_id');

        $ruangan = Ruangan::with('level.tingkat')->findOrFail($ruanganId);
        $selectedTahunId = $tahunId ?? $ruangan->tahun_pelajaran_id;
        $selectedTahun = TahunPelajaran::find($selectedTahunId) ?? TahunPelajaran::where('is_active', true)->first();

        $pesertas = PesertaImni::with(['murid', 'level', 'tingkat'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('ruangan_ujian_id', $ruanganId)
            ->orderByRaw('CAST(nomor_meja AS UNSIGNED) ASC')
            ->get();

        $levelIds = $pesertas->pluck('level_id')->filter()->unique()->toArray();
        if (empty($levelIds) && $ruangan->level_id) {
            $levelIds = [$ruangan->level_id];
        }

        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        $jadwals = JadwalUjian::with('mataPelajaran')
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereIn('level_id', $levelIds)
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $allNilai = NilaiUjian::whereIn('jadwal_ujian_id', $jadwals->pluck('id'))
            ->where('ruangan_id', $ruanganId)
            ->get();

        // Matriks [murid_id][jadwal_id] => nilai
        $matriksNilai = [];
        foreach ($allNilai as $n) {
            $matriksNilai[$n->murid_id][$n->jadwal_ujian_id] = $n->nilai;
        }

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.nilai.cetak-leger', compact(
            'ruangan',
            'selectedTahun',
            'pesertas',
            'jadwals',
            'matriksNilai',
            'ketuaPanitia'
        ));
    }
}
