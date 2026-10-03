<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PengawasRuanganImni;
use App\Models\Ujian\PesertaImni;
use App\Models\Ujian\PesertaRuanganImni;
use App\Models\Ujian\RuanganImni;
use App\Models\Ujian\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlottingImniController extends Controller
{
    /**
     * Halaman Utama Plotting Jadwal Ujian IMNI (Daftar Hari Ke-n, Tanggal, Mapel 6 IBT, Mapel 3 TSA)
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // Ambil ID Ujian IMNI Tahun Ini
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        // Ambil seluruh jadwal ujian IMNI yang memiliki tanggal
        $jadwalUjianList = JadwalUjian::with(['mataPelajaran', 'level.tingkat'])
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Daftar Master Ruangan IMNI
        $daftarRuanganImni = RuanganImni::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get();

        $totalRuangan = $daftarRuanganImni->count();

        // Grouping per Tanggal Pelaksanaan
        $daftarHariUjian = $jadwalUjianList->groupBy(function ($j) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
        })->values()->map(function ($items, $index) use ($selectedTahunId, $totalRuangan) {
            $tgl = Carbon::parse($items->first()->tanggal_ujian)->format('Y-m-d');
            $cDate = Carbon::parse($tgl)->locale('id');

            // Jadwal Kelas 6 IBT
            $jadwalIbt = $items->filter(function ($j) {
                return $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6');
            })->map(function ($j) {
                return [
                    'nama_mapel' => $j->mataPelajaran?->nama_mapel ?? ($j->nama_mata_pelajaran_custom ?? '-'),
                    'waktu'      => ($j->waktu_mulai ? substr($j->waktu_mulai, 0, 5) : '') . ' - ' . ($j->waktu_selesai ? substr($j->waktu_selesai, 0, 5) : ''),
                ];
            })->values();

            // Jadwal Kelas 3 TSA
            $jadwalTsa = $items->filter(function ($j) {
                return $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA');
            })->map(function ($j) {
                return [
                    'nama_mapel' => $j->mataPelajaran?->nama_mapel ?? ($j->nama_mata_pelajaran_custom ?? '-'),
                    'waktu'      => ($j->waktu_mulai ? substr($j->waktu_mulai, 0, 5) : '') . ' - ' . ($j->waktu_selesai ? substr($j->waktu_selesai, 0, 5) : ''),
                ];
            })->values();

            // Jumlah peserta terplot pada hari ini
            $totalTerplot = PesertaRuanganImni::where('tahun_pelajaran_id', $selectedTahunId)
                ->where('tanggal_ujian', $tgl)
                ->count();

            // Jumlah pengawas terisi (memiliki ustadz_id atau nama_pengawas yang valid)
            $totalPengawas = PengawasRuanganImni::where('tahun_pelajaran_id', $selectedTahunId)
                ->where('tanggal_ujian', $tgl)
                ->where(function ($q) {
                    $q->whereNotNull('ustadz_id')
                      ->orWhere(function ($sub) {
                          $sub->whereNotNull('nama_pengawas')->where('nama_pengawas', '!=', '');
                      });
                })
                ->count();

            return (object) [
                'hari_ke'         => $index + 1,
                'tanggal'         => $tgl,
                'nama_hari'       => $cDate->translatedFormat('l'),
                'tanggal_format'  => $cDate->translatedFormat('d F Y'),
                'tanggal_singkat' => $cDate->translatedFormat('D, d M Y'),
                'jadwal_ibt'      => $jadwalIbt,
                'jadwal_tsa'      => $jadwalTsa,
                'total_terplot'   => $totalTerplot,
                'total_pengawas'  => $totalPengawas,
                'total_ruangan'   => $totalRuangan,
                'is_siap'         => ($totalTerplot > 0 && $totalRuangan > 0 && $totalPengawas >= $totalRuangan),
            ];
        });

        // Hitung total peserta 6 IBT & 3 TSA
        $pesertaIbtCount = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->where('tingkat_id', 2)->count();
        $pesertaTsaCount = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->where('tingkat_id', 3)->count();
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;

        return view('ujian.panitia-imni.plotting.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'daftarHariUjian',
            'totalRuangan',
            'pesertaIbtCount',
            'pesertaTsaCount',
            'totalGabungan'
        ));
    }

    /**
     * Halaman Detail Plotting Peserta, Pengawas & Acak NISM Per Hari Ujian
     */
    public function harian($tanggal, Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // Ambil ID Ujian IMNI Tahun Ini
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        // Jadwal Hari Ini
        $jadwalHariIni = JadwalUjian::with(['mataPelajaran', 'level.tingkat'])
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereDate('tanggal_ujian', $tanggal)
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Hitung Hari Ke-X
        $allDates = JadwalUjian::whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->pluck('tanggal_ujian')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        $hariKe = array_search($tanggal, $allDates);
        $hariKe = ($hariKe !== false) ? ($hariKe + 1) : 1;

        $cDate = Carbon::parse($tanggal)->locale('id');
        $namaHari = $cDate->translatedFormat('l');
        $tanggalFormat = $cDate->translatedFormat('d F Y');

        // Filter Jadwal 6 IBT & 3 TSA
        $jadwalIbt = $jadwalHariIni->filter(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'))->values();
        $jadwalTsa = $jadwalHariIni->filter(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'))->values();

        // Master Ruangan IMNI
        $daftarRuanganImni = RuanganImni::with(['ruanganFisik.level.tingkat', 'penanggungJawabRuangan.ustadz'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get();

        // Pengawas Ruangan Terjadwal pada Tanggal Ini
        $pengawasRuangan = PengawasRuanganImni::with('ustadz')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tanggal_ujian', $tanggal)
            ->get()
            ->keyBy('ruangan_imni_id');

        // Plotting Peserta pada Tanggal Ini
        $pesertaPlotted = PesertaRuanganImni::with(['pesertaImni.murid.waliMurid.kampung', 'pesertaImni.tingkat', 'pesertaImni.level'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tanggal_ujian', $tanggal)
            ->orderBy('nomor_meja', 'asc')
            ->get();

        // Data Ustadz untuk Opsi Pengawas
        $daftarUstadz = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();

        // Rekap Peserta Terdaftar
        $pesertaIbtCount = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->where('tingkat_id', 2)->count();
        $pesertaTsaCount = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->where('tingkat_id', 3)->count();
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;
        $totalTerplot = $pesertaPlotted->count();

        // Group Peserta per Ruangan
        $pesertaPerRuangan = $pesertaPlotted->groupBy('ruangan_imni_id');

        return view('ujian.panitia-imni.plotting.harian', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'tanggal',
            'hariKe',
            'namaHari',
            'tanggalFormat',
            'jadwalIbt',
            'jadwalTsa',
            'daftarRuanganImni',
            'pengawasRuangan',
            'pesertaPerRuangan',
            'daftarUstadz',
            'pesertaIbtCount',
            'pesertaTsaCount',
            'totalGabungan',
            'totalTerplot'
        ));
    }

    /**
     * Acak / Random Plotting Peserta NISM Per Ruangan pada Hari Tersebut
     */
    public function random(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tanggal_ujian'      => 'required|date',
            'target_ruangan_ids' => 'nullable|array',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tanggal = Carbon::parse($request->tanggal_ujian)->format('Y-m-d');

        // Target Ruangan IMNI
        if ($request->filled('target_ruangan_ids') && count($request->target_ruangan_ids) > 0) {
            $targetRuanganIds = $request->target_ruangan_ids;
        } else {
            $targetRuanganIds = RuanganImni::where('tahun_pelajaran_id', $tahunId)
                ->where('is_active', true)
                ->orderBy('urutan', 'asc')
                ->pluck('id')
                ->toArray();
        }

        $numRuangan = count($targetRuanganIds);
        if ($numRuangan === 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Belum ada master Ruangan IMNI yang aktif untuk tahun pelajaran ini.'
            ], 422);
        }

        // Ambil Murid 6 IBT dan 3 TSA lalu ACAK (Randomize / Shuffle)
        $pesertaIbt = PesertaImni::where('tahun_pelajaran_id', $tahunId)
            ->where('tingkat_id', 2)
            ->get()
            ->shuffle()
            ->values();

        $pesertaTsa = PesertaImni::where('tahun_pelajaran_id', $tahunId)
            ->where('tingkat_id', 3)
            ->get()
            ->shuffle()
            ->values();

        DB::beginTransaction();
        try {
            // Hapus plotting pada tanggal tersebut
            PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)
                ->where('tanggal_ujian', $tanggal)
                ->delete();

            // Siapkan bucket alokasi per ruangan
            $alokasiRuangan = [];
            foreach ($targetRuanganIds as $rId) {
                $alokasiRuangan[$rId] = [];
            }

            // Distribusi merata 6 IBT (Round Robin)
            foreach ($pesertaIbt as $idx => $p) {
                $rId = $targetRuanganIds[$idx % $numRuangan];
                $alokasiRuangan[$rId][] = $p;
            }

            // Distribusi merata 3 TSA (Round Robin)
            foreach ($pesertaTsa as $idx => $p) {
                $rId = $targetRuanganIds[$idx % $numRuangan];
                $alokasiRuangan[$rId][] = $p;
            }

            // Simpan ke database dengan nomor meja per ruangan (1..N)
            $totalInserted = 0;
            foreach ($alokasiRuangan as $rId => $listPeserta) {
                // Acak urutan kursi di dalam ruangan agar 6 IBT dan 3 TSA bercampur duduknya
                $shuffledSeat = collect($listPeserta)->shuffle()->values();
                $noMeja = 1;

                foreach ($shuffledSeat as $p) {
                    PesertaRuanganImni::create([
                        'tahun_pelajaran_id' => $tahunId,
                        'ruangan_imni_id'    => $rId,
                        'peserta_imni_id'    => $p->id,
                        'murid_id'           => $p->murid_id,
                        'tanggal_ujian'      => $tanggal,
                        'nomor_meja'         => $noMeja,
                    ]);
                    $noMeja++;
                    $totalInserted++;
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil mengacak dan mem-plot {$totalInserted} peserta ke dalam {$numRuangan} ruangan untuk tanggal " . Carbon::parse($tanggal)->translatedFormat('d F Y') . ".",
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengacak plotting peserta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan Pengawas Ruangan Ujian Per Hari
     */
    public function simpanPengawas(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tanggal_ujian'      => 'required|date',
            'pengawas'           => 'required|array',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tanggal = Carbon::parse($request->tanggal_ujian)->format('Y-m-d');

        DB::beginTransaction();
        try {
            $assignedCount = 0;

            foreach ($request->pengawas as $ruanganId => $val) {
                $ustadzId = null;
                $namaPengawas = null;

                if (is_numeric($val) && (int) $val > 0) {
                    $ustadzId = (int) $val;
                    $ust = Ustadz::find($ustadzId);
                    $namaPengawas = $ust?->nama_lengkap;
                } elseif (is_string($val) && trim($val) !== '') {
                    $namaPengawas = trim($val);
                }

                if ($ustadzId || $namaPengawas) {
                    PengawasRuanganImni::updateOrCreate(
                        [
                            'tahun_pelajaran_id' => $tahunId,
                            'tanggal_ujian'      => $tanggal,
                            'ruangan_imni_id'    => $ruanganId,
                        ],
                        [
                            'ustadz_id'     => $ustadzId,
                            'nama_pengawas' => $namaPengawas,
                        ]
                    );
                    $assignedCount++;
                } else {
                    // Jika dikosongkan, hapus data penugasan agar status terhitung akurat
                    PengawasRuanganImni::where('tahun_pelajaran_id', $tahunId)
                        ->where('tanggal_ujian', $tanggal)
                        ->where('ruangan_imni_id', $ruanganId)
                        ->delete();
                }
            }

            DB::commit();

            $msg = $assignedCount > 0
                ? "Penugasan pengawas untuk {$assignedCount} ruangan berhasil disimpan."
                : "Pengaturan pengawas berhasil diperbarui (tidak ada pengawas yang ditugaskan).";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'         => 'success',
                    'message'        => $msg,
                    'assigned_count' => $assignedCount,
                ], 200);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan pengawas: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan pengawas: ' . $e->getMessage());
        }
    }

    /**
     * Pindah Ruangan Manual Peserta Harian
     */
    public function pindahRuangan(Request $request)
    {
        $request->validate([
            'peserta_ruangan_id' => 'required|exists:peserta_ruangan_imnis,id',
            'target_ruangan_id'  => 'required|exists:ruangan_imnis,id',
        ]);

        $pivot = PesertaRuanganImni::findOrFail($request->peserta_ruangan_id);
        $maxMeja = PesertaRuanganImni::where('tahun_pelajaran_id', $pivot->tahun_pelajaran_id)
            ->where('tanggal_ujian', $pivot->tanggal_ujian)
            ->where('ruangan_imni_id', $request->target_ruangan_id)
            ->max('nomor_meja') ?? 0;

        $pivot->update([
            'ruangan_imni_id' => $request->target_ruangan_id,
            'nomor_meja'      => $maxMeja + 1,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Peserta berhasil dipindahkan ke ruangan baru.',
        ], 200);
    }

    /**
     * Cetak Lembaran Mading Pengumuman Denah & Plotting Ujian IMNI Hari Ini
     */
    public function cetakMading($tanggal, Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // Ambil ID Ujian IMNI Tahun Ini
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        // Jadwal Ujian Hari Ini
        $jadwalHariIni = JadwalUjian::with(['mataPelajaran', 'level.tingkat'])
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereDate('tanggal_ujian', $tanggal)
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Hitung Hari Ke-X
        $allDates = JadwalUjian::whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->pluck('tanggal_ujian')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        $hariKe = array_search($tanggal, $allDates);
        $hariKe = ($hariKe !== false) ? ($hariKe + 1) : 1;

        $cDate = Carbon::parse($tanggal)->locale('id');
        $namaHari = $cDate->translatedFormat('l');
        $tanggalFormat = $cDate->translatedFormat('d F Y');

        $jadwalIbt = $jadwalHariIni->filter(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'))->values();
        $jadwalTsa = $jadwalHariIni->filter(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'))->values();

        // Master Ruangan IMNI
        $daftarRuanganImni = RuanganImni::with(['ruanganFisik.level.tingkat', 'penanggungJawabRuangan.ustadz'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get();

        // Pengawas
        $pengawasMap = PengawasRuanganImni::with('ustadz')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tanggal_ujian', $tanggal)
            ->get()
            ->keyBy('ruangan_imni_id');

        // Peserta Plotted
        $pesertaPlotted = PesertaRuanganImni::with(['pesertaImni.murid.waliMurid.kampung', 'pesertaImni.tingkat', 'pesertaImni.level'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tanggal_ujian', $tanggal)
            ->orderBy('nomor_meja', 'asc')
            ->get();

        $pesertaPerRuangan = $pesertaPlotted->groupBy('ruangan_imni_id');

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.plotting.cetak-mading', compact(
            'selectedTahun',
            'tanggal',
            'hariKe',
            'namaHari',
            'tanggalFormat',
            'jadwalIbt',
            'jadwalTsa',
            'daftarRuanganImni',
            'pengawasMap',
            'pesertaPerRuangan',
            'ketuaPanitia'
        ));
    }
}
