<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
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

class RuanganImniController extends Controller
{
    /**
     * Halaman Utama Pengaturan & Plotting Ruangan Ujian IMNI (Per Hari / Default)
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $daftarTingkat = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        // 1. Inisialisasi Otomatis Data Ruangan IMNI jika belum ada
        $this->ensureRuanganImniExists($selectedTahunId);

        // 2. Ambil Daftar Tanggal Ujian IMNI (Hari Pelaksanaan Ujian)
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        $jadwalUjianList = JadwalUjian::with('mataPelajaran', 'level')
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $daftarTanggalUjian = $jadwalUjianList->groupBy(function ($j) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
        })->map(function ($items, $tgl) {
            $cDate = Carbon::parse($tgl)->locale('id');
            return [
                'tanggal'        => $tgl,
                'nama_hari'      => $cDate->translatedFormat('l'),
                'tanggal_format' => $cDate->translatedFormat('d F Y'),
                'tanggal_singkat'=> $cDate->translatedFormat('D, d M Y'),
                'mapel_list'     => $items->pluck('mataPelajaran.nama_mapel')->filter()->unique()->values(),
                'jumlah_sesi'    => $items->count(),
            ];
        })->values();

        $selectedTanggal = $request->input('tanggal_ujian'); // null = default / semua hari

        // 3. Daftar Master Ruangan IMNI Tahun Ini
        $daftarRuanganImni = RuanganImni::with(['ruanganFisik.level.tingkat', 'penanggungJawabRuangan.ustadz'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        // 4. Query Peserta IMNI Tahun Ini
        $pesertaQuery = PesertaImni::with([
            'murid.waliMurid.kampung',
            'tingkat',
            'level',
            'ruanganAsal',
            'pesertaRuanganImnis' => function ($q) use ($selectedTanggal) {
                if ($selectedTanggal) {
                    $q->where('tanggal_ujian', $selectedTanggal);
                } else {
                    $q->whereNull('tanggal_ujian');
                }
            }
        ])->where('tahun_pelajaran_id', $selectedTahunId);

        // Filter Tingkat
        if ($request->filled('tingkat_id')) {
            $pesertaQuery->where('tingkat_id', $request->tingkat_id);
        }

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $pesertaQuery->where(function ($q) use ($search) {
                $q->where('nomor_peserta', 'like', "%{$search}%")
                  ->orWhereHas('murid', function ($qm) use ($search) {
                      $qm->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nism', 'like', "%{$search}%");
                  });
            });
        }

        $allPesertas = $pesertaQuery->get();

        // Map Ruangan Efektif per Peserta (Mendukung Plotting per Hari)
        $pesertas = $allPesertas->map(function ($p) use ($selectedTanggal, $daftarRuanganImni) {
            $efektifRuanganImniId = null;
            $efektifNomorMeja = $p->nomor_meja;
            $isCustomDayPlot = false;

            // Cek pivot peserta_ruangan_imnis
            if ($selectedTanggal) {
                $dailyPlot = $p->pesertaRuanganImnis->firstWhere('tanggal_ujian', $selectedTanggal);
                if ($dailyPlot && $dailyPlot->ruangan_imni_id) {
                    $efektifRuanganImniId = $dailyPlot->ruangan_imni_id;
                    $efektifNomorMeja = $dailyPlot->nomor_meja ?? $p->nomor_meja;
                    $isCustomDayPlot = true;
                }
            }

            // Fallback ke default (tanggal_ujian = NULL)
            if (!$efektifRuanganImniId) {
                $defaultPlot = PesertaRuanganImni::where('peserta_imni_id', $p->id)
                    ->whereNull('tanggal_ujian')
                    ->first();
                if ($defaultPlot && $defaultPlot->ruangan_imni_id) {
                    $efektifRuanganImniId = $defaultPlot->ruangan_imni_id;
                    $efektifNomorMeja = $defaultPlot->nomor_meja ?? $p->nomor_meja;
                }
            }

            // Fallback ke ruangan_ujian_id (jika cocok dengan ruangan_id pada ruangan_imnis)
            if (!$efektifRuanganImniId && $p->ruangan_ujian_id) {
                $matchedRuanganImni = $daftarRuanganImni->firstWhere('ruangan_id', $p->ruangan_ujian_id);
                if ($matchedRuanganImni) {
                    $efektifRuanganImniId = $matchedRuanganImni->id;
                }
            }

            $p->ruangan_imni_efektif_id = $efektifRuanganImniId;
            $p->ruangan_imni_efektif = $daftarRuanganImni->firstWhere('id', $efektifRuanganImniId);
            $p->nomor_meja_efektif = $efektifNomorMeja;
            $p->is_custom_day_plot = $isCustomDayPlot;

            return $p;
        });

        // Filter Ruangan IMNI
        if ($request->filled('ruangan_imni_id')) {
            $ruanganFilterId = (int) $request->ruangan_imni_id;
            $pesertas = $pesertas->where('ruangan_imni_efektif_id', $ruanganFilterId)->values();
        }

        // Urutkan peserta berdasarkan ruangan efektif dan nomor meja
        $pesertas = $pesertas->sortBy([
            ['ruangan_imni_efektif_id', 'asc'],
            ['tingkat_id', 'asc'],
            ['nomor_meja_efektif', 'asc'],
            ['nomor_peserta', 'asc']
        ])->values();

        // 5. Rekap Utilisasi per Ruangan IMNI
        $rekapRuangan = $daftarRuanganImni->map(function ($rg) use ($allPesertas, $selectedTanggal) {
            $muridDiRuangan = $allPesertas->filter(function ($p) use ($rg, $selectedTanggal) {
                if ($selectedTanggal) {
                    $dailyPlot = $p->pesertaRuanganImnis->firstWhere('tanggal_ujian', $selectedTanggal);
                    if ($dailyPlot && $dailyPlot->ruangan_imni_id) {
                        return $dailyPlot->ruangan_imni_id == $rg->id;
                    }
                }
                $defaultPlot = PesertaRuanganImni::where('peserta_imni_id', $p->id)->whereNull('tanggal_ujian')->first();
                if ($defaultPlot && $defaultPlot->ruangan_imni_id) {
                    return $defaultPlot->ruangan_imni_id == $rg->id;
                }
                return $p->ruangan_ujian_id == $rg->ruangan_id;
            });

            $totalTerisi = $muridDiRuangan->count();
            $totalTpq    = $muridDiRuangan->where('tingkat_id', 1)->count();
            $totalIbt    = $muridDiRuangan->where('tingkat_id', 2)->count();
            $totalTsa    = $muridDiRuangan->where('tingkat_id', 3)->count();
            $kapasitas   = (int) ($rg->kapasitas ?: 30);
            $persen      = $kapasitas > 0 ? round(($totalTerisi / $kapasitas) * 100) : 0;

            return [
                'ruangan_imni' => $rg,
                'total_terisi' => $totalTerisi,
                'total_tpq'    => $totalTpq,
                'total_ibt'    => $totalIbt,
                'total_tsa'    => $totalTsa,
                'kapasitas'    => $kapasitas,
                'persen'       => $persen,
                'is_active'    => $totalTerisi > 0,
            ];
        });

        // 6. Statistik Global & Master Ruangan Fisik
        $daftarRuanganFisik = Ruangan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $totalTerdaftar = $allPesertas->count();
        $totalTpqAll = $allPesertas->where('tingkat_id', 1)->count();
        $totalIbtAll = $allPesertas->where('tingkat_id', 2)->count();
        $totalTsaAll = $allPesertas->where('tingkat_id', 3)->count();
        $pesertaTpqCount = $totalTpqAll;
        $pesertaIbtCount = $totalIbtAll;
        $pesertaTsaCount = $totalTsaAll;
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;

        $totalTerplot = $allPesertas->filter(fn($p) => !is_null($p->ruangan_imni_efektif_id))->count();
        $totalBelumDiplot = max(0, $totalTerdaftar - $totalTerplot);
        $totalRuanganAktif = $rekapRuangan->where('is_active', true)->count();

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.ruangan.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'daftarTingkat',
            'daftarTanggalUjian',
            'selectedTanggal',
            'daftarRuanganImni',
            'daftarRuanganFisik',
            'rekapRuangan',
            'pesertas',
            'totalTerdaftar',
            'totalTpqAll',
            'totalIbtAll',
            'totalTsaAll',
            'pesertaTpqCount',
            'pesertaIbtCount',
            'pesertaTsaCount',
            'totalGabungan',
            'totalTerplot',
            'totalBelumDiplot',
            'totalRuanganAktif',
            'ketuaPanitia'
        ));
    }

    /**
     * Pastikan Data Ruangan IMNI Terinisialisasi dari Master Ruangan (Optional Fallback)
     */
    private function ensureRuanganImniExists($tahunId)
    {
        // Biarkan kosong jika belum ada agar panitia bisa membuat/generate ruangan R1, R2, dst sesuai kebutuhan rumus
    }

    /**
     * Modal Kelola Master Ruangan IMNI (R1, R2, dst) (AJAX)
     */
    public function modalKelolaRuangan(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);

        $daftarRuanganImni = RuanganImni::with(['ruanganFisik.level.tingkat', 'pesertaRuangans'])
            ->where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $daftarRuanganFisik = Ruangan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $tahunId)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $pesertaIbtCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 2)->count();
        $pesertaTsaCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 3)->count();
        $pesertaTpqCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 1)->count();
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;

        if ($request->ajax()) {
            return view('ujian.panitia-imni.ruangan.modal-kelola-ruangan', compact(
                'selectedTahun',
                'daftarRuanganImni',
                'daftarRuanganFisik',
                'pesertaIbtCount',
                'pesertaTsaCount',
                'pesertaTpqCount',
                'totalGabungan'
            ));
        }

        return redirect()->route('ruangan-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Modal Tambah Ruangan IMNI (AJAX)
     */
     public function modalTambahRuangan(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);

        $daftarRuanganFisik = Ruangan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $tahunId)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $daftarPanitia = PanitiaImni::with('ustadz')
            ->where('tahun_pelajaran_id', $tahunId)
            ->aktif()
            ->get();

        $existingCount = RuanganImni::where('tahun_pelajaran_id', $tahunId)->count();
        $saranNama = 'R' . ($existingCount + 1);

        $pesertaIbtCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 2)->count();
        $pesertaTsaCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 3)->count();
        $pesertaTpqCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 1)->count();
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;

        if ($request->ajax()) {
            return view('ujian.panitia-imni.ruangan.modal-tambah-ruangan', compact(
                'selectedTahun',
                'daftarRuanganFisik',
                'daftarPanitia',
                'saranNama',
                'pesertaIbtCount',
                'pesertaTsaCount',
                'pesertaTpqCount',
                'totalGabungan'
            ));
        }

        return redirect()->route('ruangan-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Modal Edit Ruangan IMNI (AJAX)
     */
    public function modalEditRuangan($id, Request $request)
    {
        $ruanganImni = RuanganImni::with(['ruanganFisik', 'penanggungJawabRuangan.ustadz'])->findOrFail($id);
        $selectedTahun = TahunPelajaran::findOrFail($ruanganImni->tahun_pelajaran_id);

        $daftarRuanganFisik = Ruangan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $ruanganImni->tahun_pelajaran_id)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $daftarPanitia = PanitiaImni::with('ustadz')
            ->where('tahun_pelajaran_id', $ruanganImni->tahun_pelajaran_id)
            ->aktif()
            ->get();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.ruangan.modal-edit-ruangan', compact(
                'ruanganImni',
                'selectedTahun',
                'daftarRuanganFisik',
                'daftarPanitia'
            ));
        }

        return redirect()->route('ruangan-imni.index', ['tahun_id' => $ruanganImni->tahun_pelajaran_id]);
    }

    /**
     * Simpan Tambah Ruangan IMNI Baru
     */
    public function storeRuangan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id'          => 'required|exists:tahun_pelajarans,id',
            'nama_ruangan_imni'           => 'required|string|max:50',
            'ruangan_id'                  => 'required|exists:ruangans,id',
            'penanggung_jawab_ruangan_id' => 'nullable|exists:panitia_imnis,id',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $namaRuangan = trim($request->nama_ruangan_imni);
        $ruanganFisik = Ruangan::find($request->ruangan_id);
        $maxUrutan = RuanganImni::where('tahun_pelajaran_id', $tahunId)->max('urutan') ?? 0;

        $ruanganImni = RuanganImni::create([
            'tahun_pelajaran_id'          => $tahunId,
            'nama_ruangan_imni'           => $namaRuangan,
            'nama_ruangan'                => $namaRuangan,
            'kode_ruangan'                => $namaRuangan,
            'ruangan_id'                  => $request->ruangan_id,
            'penanggung_jawab_ruangan_id' => $request->penanggung_jawab_ruangan_id ?: null,
            'kapasitas'                   => (int) ($ruanganFisik?->kapasitas ?: 20),
            'urutan'                      => $maxUrutan + 1,
            'is_active'                   => true,
        ]);

        $msg = "Ruangan IMNI {$ruanganImni->nama_ruangan_imni} berhasil ditambahkan.";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'data'    => $ruanganImni
            ], 200);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Update Ruangan IMNI
     */
    public function updateRuangan(Request $request, $id)
    {
        $ruanganImni = RuanganImni::findOrFail($id);

        $request->validate([
            'nama_ruangan_imni'           => 'required|string|max:50',
            'ruangan_id'                  => 'required|exists:ruangans,id',
            'penanggung_jawab_ruangan_id' => 'nullable|exists:panitia_imnis,id',
        ]);

        $namaRuangan = trim($request->nama_ruangan_imni);
        $ruanganFisik = Ruangan::find($request->ruangan_id);

        $ruanganImni->update([
            'nama_ruangan_imni'           => $namaRuangan,
            'nama_ruangan'                => $namaRuangan,
            'kode_ruangan'                => $namaRuangan,
            'ruangan_id'                  => $request->ruangan_id,
            'penanggung_jawab_ruangan_id' => $request->penanggung_jawab_ruangan_id ?: null,
            'kapasitas'                   => (int) ($ruanganFisik?->kapasitas ?: $ruanganImni->kapasitas ?: 20),
        ]);

        $msg = "Ruangan IMNI {$ruanganImni->nama_ruangan_imni} berhasil diperbarui.";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'data'    => $ruanganImni
            ], 200);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Hapus Ruangan IMNI
     */
    public function destroyRuangan(Request $request, $id)
    {
        $ruanganImni = RuanganImni::findOrFail($id);
        $nama = $ruanganImni->nama_ruangan;

        DB::beginTransaction();
        try {
            // Hapus pivot peserta di ruangan ini
            PesertaRuanganImni::where('ruangan_imni_id', $ruanganImni->id)->delete();
            $ruanganImni->delete();

            DB::commit();

            $msg = "Ruangan {$nama} berhasil dihapus.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ], 200);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus ruangan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menghapus ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Reset Semua Ruangan & Plotting IMNI
     */
    public function resetRuangan(Request $request)
    {
        $tahunId = $request->input('tahun_pelajaran_id');
        if (!$tahunId) {
            return response()->json(['status' => 'error', 'message' => 'Tahun Pelajaran wajib dipilih.'], 422);
        }

        DB::beginTransaction();
        try {
            PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)->delete();
            RuanganImni::where('tahun_pelajaran_id', $tahunId)->delete();
            
            DB::commit();

            $msg = "Master ruangan IMNI dan seluruh plotting peserta berhasil dikosongkan/reset.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ], 200);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal reset ruangan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal reset ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan Modal Form Auto-Plotting Distribusi Ruangan IMNI (AJAX)
     */
    public function modalAutoPlotting(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);
        $selectedTanggal = $request->input('tanggal_ujian');

        $this->ensureRuanganImniExists($tahunId);

        // Hitung Peserta Terdaftar
        $pesertaTpqCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 1)->count();
        $pesertaIbtCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 2)->count();
        $pesertaTsaCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 3)->count();
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;

        // Daftar Ruangan IMNI yang Tersedia
        $daftarRuanganImni = RuanganImni::with('ruanganFisik.level.tingkat')
            ->where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        // Daftar Tanggal Ujian IMNI
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)->where('tipe_ujian', 'IMNI')->pluck('id');
        $jadwalUjians = JadwalUjian::whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->get();

        $daftarTanggalUjian = $jadwalUjians->groupBy(function ($j) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
        })->map(function ($items, $tgl) {
            $cDate = Carbon::parse($tgl)->locale('id');
            return [
                'tanggal'        => $tgl,
                'nama_hari'      => $cDate->translatedFormat('l'),
                'tanggal_format' => $cDate->translatedFormat('d F Y'),
            ];
        })->values();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.ruangan.modal-auto-plotting', compact(
                'selectedTahun',
                'selectedTanggal',
                'pesertaTpqCount',
                'pesertaIbtCount',
                'pesertaTsaCount',
                'totalGabungan',
                'daftarRuanganImni',
                'daftarTanggalUjian'
            ));
        }

        return redirect()->route('ruangan-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Eksekusi Auto-Plotting Distribusi Ruangan IMNI (Per Hari / Default)
     */
    public function autoPlotting(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id'   => 'required|exists:tahun_pelajarans,id',
            'target_ruangan_ids'   => 'required|array|min:1',
            'target_ruangan_ids.*' => 'exists:ruangan_imnis,id',
            'target_hari'          => 'nullable|string', // 'all' atau 'rotasi'
            'tanggal_ujian'        => 'nullable|string',
            'rotasi_harian'        => 'nullable|boolean',
            'plot_tpq_mandiri'     => 'nullable|boolean',
            'metode_distribusi'    => 'nullable|in:round_robin,chunk',
            'reset_nomor_meja'     => 'nullable|boolean',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $targetRuanganImniIds = array_values(array_unique($request->target_ruangan_ids));
        $plotTpqMandiri = $request->has('plot_tpq_mandiri') ? $request->boolean('plot_tpq_mandiri') : true;
        $metode = $request->input('metode_distribusi', 'round_robin');
        $resetNomorMeja = $request->has('reset_nomor_meja') ? $request->boolean('reset_nomor_meja') : true;
        $isRotasiHarian = $request->boolean('rotasi_harian');
        $targetHari = $request->input('target_hari', 'all');
        $singleTanggal = $request->input('tanggal_ujian');

        $numRuangan = count($targetRuanganImniIds);
        if ($numRuangan === 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Silakan pilih minimal 1 ruangan ujian target untuk Kelas 6 IBT & 3 TSA.'
            ], 422);
        }

        // Ambil daftar tanggal ujian jika rotasi harian atau plotting per hari
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)->where('tipe_ujian', 'IMNI')->pluck('id');
        $allTanggalUjians = JadwalUjian::whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->pluck('tanggal_ujian')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        // Ambil map RuanganImni untuk mapping ke ruangan fisik
        $ruanganImniMap = RuanganImni::whereIn('id', $targetRuanganImniIds)->get()->keyBy('id');
        $allRuanganImni = RuanganImni::where('tahun_pelajaran_id', $tahunId)->get();

        DB::beginTransaction();
        try {
            // 1. DATA PESERTA TPQ
            $pesertaTpq = PesertaImni::with('murid')
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('tingkat_id', 1)
                ->get();

            // 2. DATA PESERTA IBT & TSA
            $pesertaIbt = PesertaImni::with('murid')
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('tingkat_id', 2)
                ->get()
                ->sortBy(fn($p) => ($p->ruangan_asal_id . '_' . ($p->murid?->nama_lengkap ?? '')))
                ->values();

            $pesertaTsa = PesertaImni::with('murid')
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('tingkat_id', 3)
                ->get()
                ->sortBy(fn($p) => ($p->ruangan_asal_id . '_' . ($p->murid?->nama_lengkap ?? '')))
                ->values();

            $totalTpqPlotted = 0;
            $totalIbtPlotted = 0;
            $totalTsaPlotted = 0;

            // HELPER FUNGSI ALOKASI RUANGAN IMNI (Dengan Shift Rotasi)
            $alokasikan = function ($shift = 0) use ($targetRuanganImniIds, $numRuangan, $pesertaIbt, $pesertaTsa, $metode) {
                $alokasi = [];
                foreach ($targetRuanganImniIds as $rId) {
                    $alokasi[$rId] = ['ibt' => [], 'tsa' => []];
                }

                // Distribusi 6 IBT
                if ($metode === 'chunk') {
                    $totalIbt = $pesertaIbt->count();
                    $chunkSize = $totalIbt > 0 ? (int) ceil($totalIbt / $numRuangan) : 1;
                    $chunks = $pesertaIbt->chunk($chunkSize);
                    $idx = 0;
                    foreach ($chunks as $chunk) {
                        $rId = $targetRuanganImniIds[($idx + $shift) % $numRuangan];
                        foreach ($chunk as $p) {
                            $alokasi[$rId]['ibt'][] = $p;
                        }
                        $idx++;
                    }
                } else {
                    foreach ($pesertaIbt as $idx => $p) {
                        $rId = $targetRuanganImniIds[($idx + $shift) % $numRuangan];
                        $alokasi[$rId]['ibt'][] = $p;
                    }
                }

                // Distribusi 3 TSA
                if ($metode === 'chunk') {
                    $totalTsa = $pesertaTsa->count();
                    $chunkSize = $totalTsa > 0 ? (int) ceil($totalTsa / $numRuangan) : 1;
                    $chunks = $pesertaTsa->chunk($chunkSize);
                    $idx = 0;
                    foreach ($chunks as $chunk) {
                        $rId = $targetRuanganImniIds[($idx + $shift) % $numRuangan];
                        foreach ($chunk as $p) {
                            $alokasi[$rId]['tsa'][] = $p;
                        }
                        $idx++;
                    }
                } else {
                    foreach ($pesertaTsa as $idx => $p) {
                        $rId = $targetRuanganImniIds[($idx + $shift) % $numRuangan];
                        $alokasi[$rId]['tsa'][] = $p;
                    }
                }

                return $alokasi;
            };

            // A. EKSEKUSI PLOTTING MASTER DEFAULT (Berlaku Semua Hari)
            if ($targetHari === 'all' || empty($singleTanggal)) {
                // Hapus data pivot default lama
                PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)
                    ->whereNull('tanggal_ujian')
                    ->delete();

                // TPQ Mandiri
                if ($plotTpqMandiri) {
                    $groupedTpq = $pesertaTpq->groupBy('ruangan_asal_id');
                    foreach ($groupedTpq as $ruangAsalId => $group) {
                        $noUrut = 1;
                        $sortedGroup = $group->sortBy(fn($p) => $p->murid?->nama_lengkap ?? '');
                        
                        // Cari atau buat RuanganImni untuk TPQ
                        $matchingRuanganImni = $allRuanganImni->firstWhere('ruangan_id', $ruangAsalId);
                        $ruanganImniId = $matchingRuanganImni?->id ?? $targetRuanganImniIds[0];

                        foreach ($sortedGroup as $p) {
                            $p->ruangan_ujian_id = $p->ruangan_asal_id;
                            if ($resetNomorMeja) {
                                $p->nomor_meja = $noUrut;
                            }
                            $p->save();

                            PesertaRuanganImni::create([
                                'tahun_pelajaran_id' => $tahunId,
                                'ruangan_imni_id'    => $ruanganImniId,
                                'peserta_imni_id'    => $p->id,
                                'murid_id'           => $p->murid_id,
                                'tanggal_ujian'      => null,
                                'nomor_meja'         => $p->nomor_meja,
                            ]);

                            $noUrut++;
                            $totalTpqPlotted++;
                        }
                    }
                }

                // IBT & TSA
                $alokasiDefault = $alokasikan(0);
                foreach ($alokasiDefault as $rImniId => $group) {
                    $noMejaRuang = 1;
                    $rFisikId = $ruanganImniMap->get($rImniId)?->ruangan_id;

                    foreach ($group['ibt'] as $p) {
                        $p->ruangan_ujian_id = $rFisikId ?? $p->ruangan_ujian_id;
                        if ($resetNomorMeja) {
                            $p->nomor_meja = $noMejaRuang;
                        }
                        $p->save();

                        PesertaRuanganImni::create([
                            'tahun_pelajaran_id' => $tahunId,
                            'ruangan_imni_id'    => $rImniId,
                            'peserta_imni_id'    => $p->id,
                            'murid_id'           => $p->murid_id,
                            'tanggal_ujian'      => null,
                            'nomor_meja'         => $p->nomor_meja,
                        ]);

                        $noMejaRuang++;
                        $totalIbtPlotted++;
                    }

                    foreach ($group['tsa'] as $p) {
                        $p->ruangan_ujian_id = $rFisikId ?? $p->ruangan_ujian_id;
                        if ($resetNomorMeja) {
                            $p->nomor_meja = $noMejaRuang;
                        }
                        $p->save();

                        PesertaRuanganImni::create([
                            'tahun_pelajaran_id' => $tahunId,
                            'ruangan_imni_id'    => $rImniId,
                            'peserta_imni_id'    => $p->id,
                            'murid_id'           => $p->murid_id,
                            'tanggal_ujian'      => null,
                            'nomor_meja'         => $p->nomor_meja,
                        ]);

                        $noMejaRuang++;
                        $totalTsaPlotted++;
                    }
                }

                // Hapus plotting custom tanggal lama jika re-plot semua hari
                PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)
                    ->whereNotNull('tanggal_ujian')
                    ->delete();
            }

            // B. EKSEKUSI PLOTTING PER HARI (ROTASI ATAU TANGGAL TERTENTU)
            if ($isRotasiHarian && !empty($allTanggalUjians)) {
                PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)
                    ->whereNotNull('tanggal_ujian')
                    ->delete();

                foreach ($allTanggalUjians as $dayIdx => $tgl) {
                    // TPQ tetap di kelas asal
                    if ($plotTpqMandiri) {
                        $groupedTpq = $pesertaTpq->groupBy('ruangan_asal_id');
                        foreach ($groupedTpq as $ruangAsalId => $group) {
                            $noUrut = 1;
                            $sortedGroup = $group->sortBy(fn($p) => $p->murid?->nama_lengkap ?? '');
                            $matchingRuanganImni = $allRuanganImni->firstWhere('ruangan_id', $ruangAsalId);
                            $ruanganImniId = $matchingRuanganImni?->id ?? $targetRuanganImniIds[0];

                            foreach ($sortedGroup as $p) {
                                PesertaRuanganImni::create([
                                    'tahun_pelajaran_id' => $tahunId,
                                    'ruangan_imni_id'    => $ruanganImniId,
                                    'peserta_imni_id'    => $p->id,
                                    'murid_id'           => $p->murid_id,
                                    'tanggal_ujian'      => $tgl,
                                    'nomor_meja'         => $resetNomorMeja ? $noUrut++ : $p->nomor_meja,
                                ]);
                            }
                        }
                    }

                    // IBT & TSA di-shift per hari
                    $alokasiHari = $alokasikan($dayIdx);
                    foreach ($alokasiHari as $rImniId => $group) {
                        $noMejaRuang = 1;
                        foreach ($group['ibt'] as $p) {
                            PesertaRuanganImni::create([
                                'tahun_pelajaran_id' => $tahunId,
                                'ruangan_imni_id'    => $rImniId,
                                'peserta_imni_id'    => $p->id,
                                'murid_id'           => $p->murid_id,
                                'tanggal_ujian'      => $tgl,
                                'nomor_meja'         => $resetNomorMeja ? $noMejaRuang++ : $p->nomor_meja,
                            ]);
                        }
                        foreach ($group['tsa'] as $p) {
                            PesertaRuanganImni::create([
                                'tahun_pelajaran_id' => $tahunId,
                                'ruangan_imni_id'    => $rImniId,
                                'peserta_imni_id'    => $p->id,
                                'murid_id'           => $p->murid_id,
                                'tanggal_ujian'      => $tgl,
                                'nomor_meja'         => $resetNomorMeja ? $noMejaRuang++ : $p->nomor_meja,
                            ]);
                        }
                    }
                }
            } elseif ($singleTanggal && $singleTanggal !== 'all') {
                // Plotting hanya untuk 1 tanggal spesifik
                PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)
                    ->where('tanggal_ujian', $singleTanggal)
                    ->delete();

                // TPQ
                if ($plotTpqMandiri) {
                    $groupedTpq = $pesertaTpq->groupBy('ruangan_asal_id');
                    foreach ($groupedTpq as $ruangAsalId => $group) {
                        $noUrut = 1;
                        $sortedGroup = $group->sortBy(fn($p) => $p->murid?->nama_lengkap ?? '');
                        $matchingRuanganImni = $allRuanganImni->firstWhere('ruangan_id', $ruangAsalId);
                        $ruanganImniId = $matchingRuanganImni?->id ?? $targetRuanganImniIds[0];

                        foreach ($sortedGroup as $p) {
                            PesertaRuanganImni::create([
                                'tahun_pelajaran_id' => $tahunId,
                                'ruangan_imni_id'    => $ruanganImniId,
                                'peserta_imni_id'    => $p->id,
                                'murid_id'           => $p->murid_id,
                                'tanggal_ujian'      => $singleTanggal,
                                'nomor_meja'         => $resetNomorMeja ? $noUrut++ : $p->nomor_meja,
                            ]);
                            $totalTpqPlotted++;
                        }
                    }
                }

                // IBT & TSA
                $alokasiHari = $alokasikan(0);
                foreach ($alokasiHari as $rImniId => $group) {
                    $noMejaRuang = 1;
                    foreach ($group['ibt'] as $p) {
                        PesertaRuanganImni::create([
                            'tahun_pelajaran_id' => $tahunId,
                            'ruangan_imni_id'    => $rImniId,
                            'peserta_imni_id'    => $p->id,
                            'murid_id'           => $p->murid_id,
                            'tanggal_ujian'      => $singleTanggal,
                            'nomor_meja'         => $resetNomorMeja ? $noMejaRuang++ : $p->nomor_meja,
                        ]);
                        $totalIbtPlotted++;
                    }
                    foreach ($group['tsa'] as $p) {
                        PesertaRuanganImni::create([
                            'tahun_pelajaran_id' => $tahunId,
                            'ruangan_imni_id'    => $rImniId,
                            'peserta_imni_id'    => $p->id,
                            'murid_id'           => $p->murid_id,
                            'tanggal_ujian'      => $singleTanggal,
                            'nomor_meja'         => $resetNomorMeja ? $noMejaRuang++ : $p->nomor_meja,
                        ]);
                        $totalTsaPlotted++;
                    }
                }
            }

            DB::commit();

            $pesan = "Plotting distribusi ruangan IMNI berhasil: {$totalIbtPlotted} murid 6 IBT & {$totalTsaPlotted} murid 3 TSA telah di-plot ke {$numRuangan} ruangan ujian IMNI.";
            if ($isRotasiHarian) {
                $pesan .= " Diterapkan rotasi ruangan otomatis untuk " . count($allTanggalUjians) . " hari jadwal ujian.";
            } elseif ($singleTanggal && $singleTanggal !== 'all') {
                $pesan .= " Diterapkan untuk tanggal ujian " . Carbon::parse($singleTanggal)->translatedFormat('d F Y') . ".";
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $pesan,
                ], 200);
            }

            return redirect()->back()->with('success', $pesan);

        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memproses auto-plotting: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memproses auto-plotting: ' . $e->getMessage());
        }
    }

    /**
     * Modal Form Pindah Ruangan Murid (AJAX)
     */
    public function modalPindah($id, Request $request)
    {
        $peserta = PesertaImni::with(['murid', 'level', 'tingkat', 'ruanganAsal', 'ruanganUjian'])->findOrFail($id);
        $selectedTanggal = $request->input('tanggal_ujian');

        $daftarRuanganImni = RuanganImni::with('ruanganFisik.level')
            ->where('tahun_pelajaran_id', $peserta->tahun_pelajaran_id)
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        // Ambil plotting saat ini
        $ruanganSaatIniId = null;
        $nomorMejaSaatIni = $peserta->nomor_meja;

        if ($selectedTanggal) {
            $daily = PesertaRuanganImni::where('peserta_imni_id', $peserta->id)
                ->where('tanggal_ujian', $selectedTanggal)
                ->first();
            if ($daily) {
                $ruanganSaatIniId = $daily->ruangan_imni_id;
                $nomorMejaSaatIni = $daily->nomor_meja;
            }
        }

        if (!$ruanganSaatIniId) {
            $default = PesertaRuanganImni::where('peserta_imni_id', $peserta->id)
                ->whereNull('tanggal_ujian')
                ->first();
            if ($default) {
                $ruanganSaatIniId = $default->ruangan_imni_id;
                $nomorMejaSaatIni = $default->nomor_meja;
            }
        }

        // Daftar Tanggal Ujian
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $peserta->tahun_pelajaran_id)->where('tipe_ujian', 'IMNI')->pluck('id');
        $jadwalUjians = JadwalUjian::whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->get();

        $daftarTanggalUjian = $jadwalUjians->groupBy(function ($j) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
        })->map(function ($items, $tgl) {
            $cDate = Carbon::parse($tgl)->locale('id');
            return [
                'tanggal'        => $tgl,
                'nama_hari'      => $cDate->translatedFormat('l'),
                'tanggal_format' => $cDate->translatedFormat('d F Y'),
            ];
        })->values();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.ruangan.modal-pindah', compact(
                'peserta',
                'daftarRuanganImni',
                'daftarTanggalUjian',
                'selectedTanggal',
                'ruanganSaatIniId',
                'nomorMejaSaatIni'
            ));
        }

        return redirect()->route('ruangan-imni.index', ['tahun_id' => $peserta->tahun_pelajaran_id]);
    }

    /**
     * Update Pindah Ruangan Murid (Per Hari / Semua Hari)
     */
    public function updatePindahRuangan(Request $request, $id)
    {
        $peserta = PesertaImni::findOrFail($id);

        $request->validate([
            'ruangan_imni_id' => 'required|exists:ruangan_imnis,id',
            'cakupan_hari'    => 'required|in:all,single',
            'tanggal_ujian'   => 'nullable|date',
            'nomor_meja'      => 'nullable|integer',
        ]);

        $ruanganImniId = $request->ruangan_imni_id;
        $cakupanHari = $request->cakupan_hari;
        $tanggalUjian = $request->tanggal_ujian;
        $nomorMeja = $request->nomor_meja;

        $targetRuanganImni = RuanganImni::find($ruanganImniId);

        DB::beginTransaction();
        try {
            if ($cakupanHari === 'all') {
                // Update default pivot
                PesertaRuanganImni::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $peserta->tahun_pelajaran_id,
                        'peserta_imni_id'    => $peserta->id,
                        'tanggal_ujian'      => null,
                    ],
                    [
                        'ruangan_imni_id' => $ruanganImniId,
                        'murid_id'        => $peserta->murid_id,
                        'nomor_meja'      => $nomorMeja ?? $peserta->nomor_meja,
                    ]
                );

                // Update master fallback
                if ($targetRuanganImni && $targetRuanganImni->ruangan_id) {
                    $peserta->ruangan_ujian_id = $targetRuanganImni->ruangan_id;
                }
                if (!is_null($nomorMeja)) {
                    $peserta->nomor_meja = $nomorMeja;
                }
                $peserta->save();

                // Hapus override tanggal khusus
                PesertaRuanganImni::where('peserta_imni_id', $peserta->id)
                    ->whereNotNull('tanggal_ujian')
                    ->delete();
            } else {
                // Update khusus tanggal tertentu
                PesertaRuanganImni::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $peserta->tahun_pelajaran_id,
                        'peserta_imni_id'    => $peserta->id,
                        'tanggal_ujian'      => $tanggalUjian,
                    ],
                    [
                        'ruangan_imni_id' => $ruanganImniId,
                        'murid_id'        => $peserta->murid_id,
                        'nomor_meja'      => $nomorMeja ?? $peserta->nomor_meja,
                    ]
                );
            }

            DB::commit();

            $msg = "Ruangan ujian murid {$peserta->murid?->nama_lengkap} berhasil diperbarui.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ], 200);
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memindahkan ruangan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memindahkan ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Pindah Ruangan Massal (Bulk Plotting)
     */
    public function bulkPindah(Request $request)
    {
        $request->validate([
            'peserta_ids'     => 'required|array|min:1',
            'peserta_ids.*'   => 'exists:peserta_imnis,id',
            'ruangan_imni_id' => 'required|exists:ruangan_imnis,id',
            'tanggal_ujian'   => 'nullable|string',
        ]);

        $pesertaIds = $request->peserta_ids;
        $ruanganImniId = $request->ruangan_imni_id;
        $tanggal = $request->tanggal_ujian;
        $count = count($pesertaIds);
        $targetRuanganImni = RuanganImni::find($ruanganImniId);

        DB::beginTransaction();
        try {
            $pesertas = PesertaImni::whereIn('id', $pesertaIds)->get();

            if (empty($tanggal) || $tanggal === 'all') {
                foreach ($pesertas as $p) {
                    PesertaRuanganImni::updateOrCreate(
                        [
                            'tahun_pelajaran_id' => $p->tahun_pelajaran_id,
                            'peserta_imni_id'    => $p->id,
                            'tanggal_ujian'      => null,
                        ],
                        [
                            'ruangan_imni_id' => $ruanganImniId,
                            'murid_id'        => $p->murid_id,
                            'nomor_meja'      => $p->nomor_meja,
                        ]
                    );

                    if ($targetRuanganImni && $targetRuanganImni->ruangan_id) {
                        $p->ruangan_ujian_id = $targetRuanganImni->ruangan_id;
                        $p->save();
                    }
                }

                PesertaRuanganImni::whereIn('peserta_imni_id', $pesertaIds)
                    ->whereNotNull('tanggal_ujian')
                    ->delete();
            } else {
                foreach ($pesertas as $p) {
                    PesertaRuanganImni::updateOrCreate(
                        [
                            'tahun_pelajaran_id' => $p->tahun_pelajaran_id,
                            'peserta_imni_id'    => $p->id,
                            'tanggal_ujian'      => $tanggal,
                        ],
                        [
                            'ruangan_imni_id' => $ruanganImniId,
                            'murid_id'        => $p->murid_id,
                            'nomor_meja'      => $p->nomor_meja,
                        ]
                    );
                }
            }

            DB::commit();

            $msg = "Sebanyak {$count} murid berhasil dipindahkan ke {$targetRuanganImni?->nama_ruangan}.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ], 200);
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memindahkan ruangan massal: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memindahkan ruangan massal: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Denah Tempat Duduk Ruangan Ujian (Per Tanggal / Default)
     */
    public function cetakDenah(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $ruanganImniId = $request->input('ruangan_imni_id') ?? $request->input('ruangan_ujian_id');
        $tanggal = $request->input('tanggal_ujian');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $ruanganImni = RuanganImni::with('ruanganFisik.level.tingkat')->find($ruanganImniId)
            ?? RuanganImni::where('tahun_pelajaran_id', $selectedTahunId)->first();

        $ruangan = $ruanganImni?->ruanganFisik;

        // Ambil murid yang menempati ruangan ini
        $pesertaRuangans = PesertaRuanganImni::with(['pesertaImni.murid', 'pesertaImni.level', 'pesertaImni.tingkat'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where('ruangan_imni_id', $ruanganImni?->id)
            ->when($tanggal && $tanggal !== 'all', function ($q) use ($tanggal) {
                $q->where('tanggal_ujian', $tanggal);
            }, function ($q) {
                $q->whereNull('tanggal_ujian');
            })
            ->orderBy('nomor_meja', 'asc')
            ->get();

        $pesertas = $pesertaRuangans->map(function ($pr) {
            $p = $pr->pesertaImni;
            if ($p) {
                $p->nomor_meja = $pr->nomor_meja ?? $p->nomor_meja;
            }
            return $p;
        })->filter()->values();

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.peserta.cetak-denah', compact(
            'selectedTahun',
            'ruangan',
            'pesertas',
            'ketuaPanitia'
        ));
    }

    /**
     * Cetak Label Nomor Meja
     */
    public function cetakLabelMeja(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $ruanganImniId = $request->input('ruangan_imni_id') ?? $request->input('ruangan_ujian_id');
        $tanggal = $request->input('tanggal_ujian');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $pesertaQuery = PesertaImni::with(['murid', 'level', 'tingkat', 'ruanganUjian'])
            ->where('tahun_pelajaran_id', $selectedTahunId);

        if ($ruanganImniId) {
            $ruanganImni = RuanganImni::find($ruanganImniId);
            if ($ruanganImni && $ruanganImni->ruangan_id) {
                $pesertaQuery->where('ruangan_ujian_id', $ruanganImni->ruangan_id);
            }
        }

        $pesertas = $pesertaQuery->orderByRaw('CAST(nomor_meja AS UNSIGNED) ASC')->get();
        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.peserta.cetak-label-meja', compact(
            'selectedTahun',
            'pesertas',
            'ketuaPanitia'
        ));
    }
}
