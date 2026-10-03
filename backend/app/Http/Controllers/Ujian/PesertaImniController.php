<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PembayaranImni;
use App\Models\Ujian\PesertaImni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesertaImniController extends Controller
{
    /**
     * Halaman Utama Manajemen Peserta & Ruangan Ujian IMNI
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // Daftar Tingkat & Level Kelas Akhir (3 TPQ, 6 IBT, 3 TSA)
        $daftarTingkat = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        $levelAkhirQuery = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            });
        $daftarLevelAkhir = $levelAkhirQuery->orderBy('urutan_level', 'asc')->get();
        $levelAkhirIds = $daftarLevelAkhir->pluck('id')->toArray();

        // Daftar Ruangan Ujian (Hanya yang mengikuti IMNI: 3 TPQ, 6 IBT, 3 TSA)
        $ruanganUjianQuery = Ruangan::with('level')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->whereIn('level_id', $levelAkhirIds);

        if ($request->filled('tingkat_id')) {
            $ruanganUjianQuery->whereHas('level', function ($q) use ($request) {
                $q->where('tingkat_id', $request->tingkat_id);
            });
        }

        $daftarRuanganUjian = $ruanganUjianQuery
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        // 1. Query Data Peserta IMNI Terdaftar
        $query = PesertaImni::with([
            'murid.waliMurid.kampung',
            'tingkat',
            'level',
            'ruanganAsal',
            'ruanganUjian',
            'pembayaran',
        ])
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $selectedTahunId);

        // Filter Tingkat
        if ($request->filled('tingkat_id')) {
            $query->where('peserta_imnis.tingkat_id', $request->tingkat_id);
        }

        // Filter Level
        if ($request->filled('level_id')) {
            $query->where('peserta_imnis.level_id', $request->level_id);
        }

        // Filter Ruangan Ujian
        if ($request->filled('ruangan_ujian_id')) {
            $query->where('peserta_imnis.ruangan_ujian_id', $request->ruangan_ujian_id);
        }

        // Filter Status Administrasi / Kelayakan
        if ($request->filled('status_administrasi')) {
            $statusAdmin = $request->status_administrasi;
            if ($statusAdmin === 'Lunas') {
                $query->whereHas('pembayaran', function ($q) {
                    $q->where('status_pembayaran', 'Lunas');
                });
            } elseif ($statusAdmin === 'Belum Lunas') {
                $query->where(function ($q) {
                    $q->whereHas('pembayaran', function ($pq) {
                        $pq->where('status_pembayaran', '!=', 'Lunas');
                    })->orWhereDoesntHave('pembayaran');
                })->where('peserta_imnis.status_kelayakan', '!=', 'Dispensasi');
            } elseif ($statusAdmin === 'Dispensasi') {
                $query->where('peserta_imnis.status_kelayakan', 'Dispensasi');
            }
        }

        // Filter Pencarian Murid (Nama / NISM / Nomor Peserta)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('peserta_imnis.nomor_peserta', 'like', "%{$search}%")
                    ->orWhere('murids.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('murids.nism', 'like', "%{$search}%")
                    ->orWhere('murids.nisn', 'like', "%{$search}%");
            });
        }

        $pesertas = $query
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        // 2. Statistik Metrik Peserta IMNI
        $allPesertaTahunIni = PesertaImni::with('pembayaran')->where('tahun_pelajaran_id', $selectedTahunId)->get();
        $totalTerdaftar = $allPesertaTahunIni->count();
        $totalTpq = $allPesertaTahunIni->where('tingkat_id', 1)->count();
        $totalIbt = $allPesertaTahunIni->where('tingkat_id', 2)->count();
        $totalTsa = $allPesertaTahunIni->where('tingkat_id', 3)->count();

        // Metrik Administrasi Keuangan & Dispensasi
        $totalLunas = $allPesertaTahunIni->filter(fn($p) => $p->pembayaran?->status_pembayaran === 'Lunas')->count();
        $totalDispensasi = $allPesertaTahunIni->where('status_kelayakan', 'Dispensasi')->count();
        $totalBelumLunas = $allPesertaTahunIni->filter(fn($p) => ($p->pembayaran?->status_pembayaran ?? 'Belum Lunas') !== 'Lunas' && $p->status_kelayakan !== 'Dispensasi')->count();

        // Hitung Potensi Total Kandidat Murid Aktif di Kelas Akhir
        $kandidatTotal = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $selectedTahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelAkhirIds)
            ->distinct('murids.id')
            ->count('murids.id');

        $belumDitarik = max(0, $kandidatTotal - $totalTerdaftar);

        // SK Panitia Ketua untuk Tanda Tangan
        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.peserta.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'daftarTingkat',
            'daftarLevelAkhir',
            'daftarRuanganUjian',
            'pesertas',
            'totalTerdaftar',
            'totalTpq',
            'totalIbt',
            'totalTsa',
            'totalLunas',
            'totalBelumLunas',
            'totalDispensasi',
            'kandidatTotal',
            'belumDitarik',
            'ketuaPanitia'
        ));
    }

    /**
     * Ambil Data Kandidat Murid Kelas Akhir yang Belum Ditarik (AJAX)
     */
    public function getKandidat(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $tingkatId = $request->input('tingkat_id');

        $levelAkhirQuery = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            });

        if ($tingkatId) {
            $levelAkhirQuery->where('tingkat_id', $tingkatId);
        }

        $levelAkhirIds = $levelAkhirQuery->pluck('id')->toArray();

        // Murid ID yang sudah terdaftar di IMNI tahun ini
        $registeredMuridIds = PesertaImni::where('tahun_pelajaran_id', $tahunId)->pluck('murid_id')->toArray();

        // Ambil murid aktif di kelas akhir
        $kandidats = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->join('tingkats', 'levels.tingkat_id', '=', 'tingkats.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $tahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelAkhirIds)
            ->whereNotIn('murids.id', $registeredMuridIds)
            ->select(
                'murids.id as murid_id',
                'murids.nism',
                'murids.nisn',
                'murids.nama_lengkap',
                'murids.jenis_kelamin',
                'ruangans.id as ruangan_id',
                'ruangans.nama_ruangan',
                'levels.id as level_id',
                'levels.nama_level',
                'tingkats.id as tingkat_id',
                'tingkats.kode_tingkat',
                'tingkats.nama_tingkat'
            )
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'count'  => $kandidats->count(),
            'data'   => $kandidats,
        ]);
    }

    /**
     * Tampilkan Modal Form Tarik Murid Kelas Akhir (AJAX)
     */
    public function modalTarik(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);
        $daftarTingkat = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        $levelAkhirIds = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            })
            ->pluck('id')
            ->toArray();

        $kandidatTotal = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $tahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelAkhirIds)
            ->distinct('murids.id')
            ->count('murids.id');

        $totalTerdaftar = PesertaImni::where('tahun_pelajaran_id', $tahunId)->count();
        $belumDitarik = max(0, $kandidatTotal - $totalTerdaftar);

        if ($request->ajax()) {
            return view('ujian.panitia-imni.peserta.modal-tarik', compact(
                'selectedTahun',
                'daftarTingkat',
                'belumDitarik',
                'kandidatTotal'
            ));
        }

        return redirect()->route('peserta-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Tarik Otomatis Seluruh Murid Kelas Akhir ke Tabel Peserta IMNI
     */
    public function tarikPeserta(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tingkat_id'         => 'nullable|exists:tingkats,id',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tingkatId = $request->tingkat_id;
        $tahunPelajaran = TahunPelajaran::findOrFail($tahunId);

        $levelAkhirQuery = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            });

        if ($tingkatId) {
            $levelAkhirQuery->where('tingkat_id', $tingkatId);
        }

        $levelAkhirIds = $levelAkhirQuery->pluck('id')->toArray();

        // Ambil murid kelas akhir yang aktif
        $kandidats = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->join('tingkats', 'levels.tingkat_id', '=', 'tingkats.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $tahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelAkhirIds)
            ->select(
                'murids.id as murid_id',
                'murids.nism',
                'murids.nama_lengkap',
                'murids.jenis_kelamin',
                'ruangans.id as ruangan_asal_id',
                'levels.id as level_id',
                'tingkats.id as tingkat_id',
                'tingkats.kode_tingkat'
            )
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        if ($kandidats->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Tidak ditemukan data murid kelas akhir aktif untuk ditarik.'
                ], 200);
            }
            return redirect()->back()->with('warning', 'Tidak ditemukan data murid kelas akhir aktif untuk ditarik.');
        }

        $insertedCount = 0;

        DB::beginTransaction();
        try {
            // Ambil daftar pengaturan tarif tagihan IMNI untuk tahun ini
            $pengaturanTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
                ->where(function ($q) {
                    $q->where('kode_tagihan', 'IMNI')
                        ->orWhere('nama_tagihan', 'LIKE', '%IMNI%');
                })
                ->get();

            foreach ($kandidats as $k) {
                // Cek apakah sudah ada
                $exists = PesertaImni::where('tahun_pelajaran_id', $tahunId)
                    ->where('murid_id', $k->murid_id)
                    ->exists();

                if (!$exists) {
                    $nomorPeserta = PesertaImni::generateNomorPeserta($tahunPelajaran, $k->kode_tingkat, $k->nism);

                    $pesertaBaru = PesertaImni::create([
                        'tahun_pelajaran_id' => $tahunId,
                        'murid_id'           => $k->murid_id,
                        'tingkat_id'         => $k->tingkat_id,
                        'level_id'           => $k->level_id,
                        'ruangan_asal_id'    => $k->ruangan_asal_id,
                        'ruangan_ujian_id'   => $k->ruangan_asal_id, // Default ke ruang asal
                        'nomor_peserta'      => $nomorPeserta,
                        'nomor_meja'         => null,
                        'status_kelayakan'   => 'Layak',
                        'is_active'          => true,
                    ]);

                    // Otomatis buat data tagihan pembayaran sesuai level dari Pengaturan Tagihan
                    $tagihanLevel = $pengaturanTagihans->firstWhere('level_id', $k->level_id)
                        ?? $pengaturanTagihans->firstWhere('level_id', null);
                    $nominalTagihan = $tagihanLevel ? (float) $tagihanLevel->nominal : 0;

                    PembayaranImni::create([
                        'tahun_pelajaran_id' => $tahunId,
                        'peserta_imni_id'    => $pesertaBaru->id,
                        'murid_id'           => $k->murid_id,
                        'nominal_tagihan'    => $nominalTagihan,
                        'nominal_bayar'      => 0,
                        'sisa_tagihan'       => $nominalTagihan,
                        'status_pembayaran'  => ($nominalTagihan > 0 ? 'Belum Lunas' : 'Lunas'),
                        'metode_pembayaran'  => 'Tunai',
                    ]);

                    $insertedCount++;
                }
            }
            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil menarik {$insertedCount} murid ke daftar peserta IMNI."
                ], 200);
            }

            return redirect()->back()->with('success', "Berhasil menarik {$insertedCount} murid ke daftar peserta IMNI.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menarik peserta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menarik peserta: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan Modal Form Edit Peserta IMNI (AJAX)
     */
    public function edit($id, Request $request)
    {
        $peserta = PesertaImni::with(['murid', 'level', 'tingkat', 'ruanganAsal', 'ruanganUjian'])->findOrFail($id);

        if ($request->ajax()) {
            $levelAkhirIds = Level::where('is_active', true)
                ->where(function ($q) {
                    $q->where('nama_level', 'LIKE', '%3%TPQ%')
                        ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                        ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                        ->orWhereIn('urutan_level', [3, 9, 12]);
                })
                ->pluck('id')
                ->toArray();

            $daftarRuanganUjian = Ruangan::with('level')
                ->where('tahun_pelajaran_id', $peserta->tahun_pelajaran_id)
                ->whereIn('level_id', $levelAkhirIds)
                ->orderBy('nama_ruangan', 'asc')
                ->get();

            return view('ujian.panitia-imni.peserta.form', compact('peserta', 'daftarRuanganUjian'));
        }

        return redirect()->route('peserta-imni.index', ['tahun_id' => $peserta->tahun_pelajaran_id]);
    }

    /**
     * Update Detail Data Peserta (Nomor Peserta, Ruang Ujian, Catatan)
     */
    public function update(Request $request, $id)
    {
        $peserta = PesertaImni::findOrFail($id);

        $request->validate([
            'nomor_peserta'    => 'required|string|max:50',
            'ruangan_ujian_id' => 'nullable|exists:ruangans,id',
            'catatan'          => 'nullable|string|max:500',
        ]);

        $peserta->update([
            'nomor_peserta'    => $request->nomor_peserta,
            'ruangan_ujian_id' => $request->ruangan_ujian_id,
            'catatan'          => $request->catatan,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => 'Data peserta IMNI berhasil diperbarui.'
            ], 200);
        }

        return redirect()->back()->with('success', 'Data peserta IMNI berhasil diperbarui.');
    }

    /**
     * Tampilkan Modal Form Auto-Plotting / Distribusi Ruangan IMNI (AJAX)
     */
    public function modalAutoPlotting(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);

        // Hitung Peserta Terdaftar per Tingkat
        $pesertaTpqCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 1)->count();
        $pesertaIbtCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 2)->count();
        $pesertaTsaCount = PesertaImni::where('tahun_pelajaran_id', $tahunId)->where('tingkat_id', 3)->count();
        $totalGabungan = $pesertaIbtCount + $pesertaTsaCount;

        $levelAkhirIds = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            })
            ->pluck('id')
            ->toArray();

        // Daftar Ruangan Ujian yang Tersedia untuk Tahun Ini (Hanya Ruangan Kelas Akhir / IMNI)
        $daftarRuangan = Ruangan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $tahunId)
            ->whereIn('level_id', $levelAkhirIds)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.peserta.modal-auto-plotting', compact(
                'selectedTahun',
                'pesertaTpqCount',
                'pesertaIbtCount',
                'pesertaTsaCount',
                'totalGabungan',
                'daftarRuangan'
            ));
        }

        return redirect()->route('peserta-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Eksekusi Auto-Plotting & Distribusi Ruangan Ujian IMNI
     */
    public function autoPlottingRuangan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'target_ruangan_ids' => 'required|array|min:1',
            'target_ruangan_ids.*' => 'exists:ruangans,id',
            'plot_tpq_mandiri'   => 'nullable|boolean',
            'metode_distribusi'  => 'nullable|in:round_robin,chunk',
            'reset_nomor_meja'   => 'nullable|boolean',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $targetRuanganIds = array_values(array_unique($request->target_ruangan_ids));
        $plotTpqMandiri = $request->has('plot_tpq_mandiri') ? $request->boolean('plot_tpq_mandiri') : true;
        $metode = $request->input('metode_distribusi', 'round_robin');
        $resetNomorMeja = $request->has('reset_nomor_meja') ? $request->boolean('reset_nomor_meja') : true;

        $numRuangan = count($targetRuanganIds);
        if ($numRuangan === 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Silakan pilih minimal 1 ruangan ujian target untuk Kelas 6 IBT & 3 TSA.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Silakan pilih minimal 1 ruangan ujian target untuk Kelas 6 IBT & 3 TSA.');
        }

        DB::beginTransaction();
        try {
            $totalTpqPlotted = 0;
            $totalIbtPlotted = 0;
            $totalTsaPlotted = 0;

            // 1. PLOTTING KELAS 3 TPQ (Mengikuti IMDA 2: Ruangan Ujian = Ruangan Asal Kelas)
            if ($plotTpqMandiri) {
                $pesertaTpq = PesertaImni::with('murid')
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->where('tingkat_id', 1)
                    ->get();

                // Kelompokkan per ruangan asal
                $groupedTpq = $pesertaTpq->groupBy('ruangan_asal_id');
                foreach ($groupedTpq as $ruangAsalId => $group) {
                    $noUrut = 1;
                    $sortedGroup = $group->sortBy(fn($p) => $p->murid?->nama_lengkap ?? '');
                    foreach ($sortedGroup as $p) {
                        $p->ruangan_ujian_id = $p->ruangan_asal_id;
                        if ($resetNomorMeja) {
                            $p->nomor_meja = $noUrut++;
                        }
                        $p->save();
                        $totalTpqPlotted++;
                    }
                }
            }

            // 2. PLOTTING KELAS 6 IBT & 3 TSA (Distribusi Gabungan Proporsional ke Ruangan Terpilih)
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

            // Wadah penampung peserta per ruangan target
            $alokasi = [];
            foreach ($targetRuanganIds as $rId) {
                $alokasi[$rId] = [
                    'ibt' => [],
                    'tsa' => []
                ];
            }

            // Distribusi 6 IBT
            if ($metode === 'chunk') {
                $totalIbt = $pesertaIbt->count();
                $chunkSize = $totalIbt > 0 ? (int) ceil($totalIbt / $numRuangan) : 1;
                $chunks = $pesertaIbt->chunk($chunkSize);
                $idx = 0;
                foreach ($chunks as $chunk) {
                    $rId = $targetRuanganIds[$idx % $numRuangan];
                    foreach ($chunk as $p) {
                        $alokasi[$rId]['ibt'][] = $p;
                    }
                    $idx++;
                }
            } else {
                // Round-robin
                foreach ($pesertaIbt as $idx => $p) {
                    $rId = $targetRuanganIds[$idx % $numRuangan];
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
                    $rId = $targetRuanganIds[$idx % $numRuangan];
                    foreach ($chunk as $p) {
                        $alokasi[$rId]['tsa'][] = $p;
                    }
                    $idx++;
                }
            } else {
                // Round-robin
                foreach ($pesertaTsa as $idx => $p) {
                    $rId = $targetRuanganIds[$idx % $numRuangan];
                    $alokasi[$rId]['tsa'][] = $p;
                }
            }

            // Simpan plotting ke database untuk setiap ruangan
            foreach ($alokasi as $rId => $pesertaGroup) {
                $noMejaRuang = 1;

                // Simpan Murid 6 IBT
                foreach ($pesertaGroup['ibt'] as $p) {
                    $p->ruangan_ujian_id = $rId;
                    if ($resetNomorMeja) {
                        $p->nomor_meja = $noMejaRuang++;
                    }
                    $p->save();
                    $totalIbtPlotted++;
                }

                // Simpan Murid 3 TSA
                foreach ($pesertaGroup['tsa'] as $p) {
                    $p->ruangan_ujian_id = $rId;
                    if ($resetNomorMeja) {
                        $p->nomor_meja = $noMejaRuang++;
                    }
                    $p->save();
                    $totalTsaPlotted++;
                }
            }

            DB::commit();

            $pesan = "Distribusi ruangan IMNI berhasil: {$totalIbtPlotted} murid 6 IBT & {$totalTsaPlotted} murid 3 TSA telah di-plot ke " . count($targetRuanganIds) . " ruangan ujian terpilih.";
            if ($plotTpqMandiri && $totalTpqPlotted > 0) {
                $pesan .= " Sebanyak {$totalTpqPlotted} murid 3 TPQ otomatis di-plot ke ruang kelas masing-masing.";
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $pesan,
                    'data'    => [
                        'tpq_plotted'   => $totalTpqPlotted,
                        'ibt_plotted'   => $totalIbtPlotted,
                        'tsa_plotted'   => $totalTsaPlotted,
                        'ruangan_count' => count($targetRuanganIds)
                    ]
                ], 200);
            }

            return redirect()->back()->with('success', $pesan);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memproses distribusi ruangan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memproses distribusi ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Plotting Ruangan Ujian Massal & Penomoran Meja
     */
    public function bulkUpdatePlotting(Request $request)
    {
        $request->validate([
            'peserta_ids'        => 'required|array|min:1',
            'peserta_ids.*'      => 'exists:peserta_imnis,id',
            'ruangan_ujian_id'   => 'required|exists:ruangans,id',
            'nomor_meja_mulai'   => 'nullable|integer|min:1',
            'auto_nomor_meja'    => 'nullable|boolean',
        ]);

        $pesertaIds = $request->peserta_ids;
        $ruanganUjianId = $request->ruangan_ujian_id;
        $startNomorMeja = $request->nomor_meja_mulai ?? 1;
        $autoNumber = $request->has('auto_nomor_meja') || $request->auto_nomor_meja;

        DB::beginTransaction();
        try {
            $pesertas = PesertaImni::whereIn('id', $pesertaIds)
                ->orderBy('nomor_peserta', 'asc')
                ->get();

            $currentNo = $startNomorMeja;
            foreach ($pesertas as $p) {
                $p->ruangan_ujian_id = $ruanganUjianId;
                if ($autoNumber) {
                    $p->nomor_meja = $currentNo++;
                }
                $p->save();
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Plotting ruangan ujian untuk ' . count($pesertaIds) . ' murid berhasil disimpan.'
                ], 200);
            }

            return redirect()->back()->with('success', 'Plotting ruangan ujian untuk ' . count($pesertaIds) . ' murid berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memproses plotting: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memproses plotting: ' . $e->getMessage());
        }
    }

    /**
     * Regenerasi Nomor Peserta & Nomor Meja Berurutan
     */
    public function regenerateNomorPeserta(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tingkat_id'         => 'nullable|exists:tingkats,id',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tingkatId = $request->tingkat_id;
        $tahunPelajaran = TahunPelajaran::findOrFail($tahunId);

        $query = PesertaImni::with(['murid', 'tingkat', 'level'])
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $tahunId);

        if ($tingkatId) {
            $query->where('peserta_imnis.tingkat_id', $tingkatId);
        }

        $pesertas = $query
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        if ($pesertas->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Tidak ada data peserta untuk diregenerasi nomornya.'
                ], 200);
            }
            return redirect()->back()->with('warning', 'Tidak ada data peserta untuk diregenerasi nomornya.');
        }

        DB::beginTransaction();
        try {
            foreach ($pesertas as $p) {
                $kodeTingkat = $p->tingkat?->kode_tingkat ?? 'IBT';
                $nism = $p->murid?->nism ?? '000';

                $p->nomor_peserta = PesertaImni::generateNomorPeserta($tahunPelajaran, $kodeTingkat, $nism);
                $p->nomor_meja = null;
                $p->save();
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Nomor peserta IMNI berhasil diregenerasi sesuai format IMNI-Tahun-Tingkat-NISM.'
                ], 200);
            }

            return redirect()->back()->with('success', 'Nomor peserta IMNI berhasil diregenerasi sesuai format IMNI-Tahun-Tingkat-NISM.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal meregenerasi nomor peserta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal meregenerasi nomor peserta: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Peserta IMNI
     */
    public function destroy($id)
    {
        $peserta = PesertaImni::findOrFail($id);
        $namaMurid = $peserta->murid?->nama_lengkap ?? 'Murid';
        $peserta->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Peserta {$namaMurid} berhasil dihapus dari kepesertaan IMNI."
            ], 200);
        }

        return redirect()->back()->with('success', "Peserta {$namaMurid} berhasil dihapus dari kepesertaan IMNI.");
    }

    /**
     * Hapus Peserta IMNI Massal
     */
    public function destroyBulk(Request $request)
    {
        $request->validate([
            'peserta_ids'   => 'required|array|min:1',
            'peserta_ids.*' => 'exists:peserta_imnis,id',
        ]);

        $count = PesertaImni::whereIn('id', $request->peserta_ids)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil menghapus {$count} peserta dari daftar IMNI."
            ], 200);
        }

        return redirect()->back()->with('success', "Berhasil menghapus {$count} peserta dari daftar IMNI.");
    }

    /**
     * Tampilkan Modal Beri Dispensasi Ujian Panitia IMNI (AJAX)
     */
    public function modalDispensasi($id, Request $request)
    {
        $peserta = PesertaImni::with(['murid', 'level', 'tingkat', 'pembayaran'])->findOrFail($id);

        if ($request->ajax()) {
            return view('ujian.panitia-imni.peserta.modal-dispensasi', compact('peserta'));
        }

        return redirect()->route('peserta-imni.index', ['tahun_id' => $peserta->tahun_pelajaran_id]);
    }

    /**
     * Simpan Dispensasi Panitia IMNI
     */
    public function beriDispensasi(Request $request, $id)
    {
        $peserta = PesertaImni::findOrFail($id);

        $request->validate([
            'alasan_dispensasi' => 'required|string|max:500',
        ]);

        $peserta->status_kelayakan = 'Dispensasi';
        $peserta->catatan = $request->alasan_dispensasi;
        $peserta->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Dispensasi ujian berhasil diberikan kepada {$peserta->murid?->nama_lengkap}."
            ], 200);
        }

        return redirect()->back()->with('success', "Dispensasi ujian berhasil diberikan kepada {$peserta->murid?->nama_lengkap}.");
    }

    /**
     * Cabut Dispensasi Panitia IMNI
     */
    public function cabutDispensasi(Request $request, $id)
    {
        $peserta = PesertaImni::findOrFail($id);

        $peserta->status_kelayakan = 'Layak';
        $peserta->catatan = null;
        $peserta->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Dispensasi ujian untuk {$peserta->murid?->nama_lengkap} telah dicabut."
            ], 200);
        }

        return redirect()->back()->with('success', "Dispensasi ujian untuk {$peserta->murid?->nama_lengkap} telah dicabut.");
    }

    /**
     * Toggle Status Kelayakan Peserta IMNI
     */
    public function toggleKelayakan(Request $request, $id)
    {
        $peserta = PesertaImni::findOrFail($id);
        $statuses = ['Layak', 'Dispensasi', 'Ditunda'];
        $currentIdx = array_search($peserta->status_kelayakan, $statuses);
        $nextStatus = $statuses[($currentIdx + 1) % count($statuses)];

        $peserta->status_kelayakan = $nextStatus;
        $peserta->save();

        return response()->json([
            'status'           => 'success',
            'new_status'       => $nextStatus,
            'message'          => "Status kelayakan diubah menjadi {$nextStatus}.",
        ]);
    }

    // =========================================================================
    // FITUR CETAK DOKUMEN PESERTA IMNI
    // =========================================================================

    /**
     * Cetak Kartu Peserta Ujian IMNI (Single / Bulk)
     */
    public function cetakKartu(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $query = PesertaImni::with([
            'murid.waliMurid.kampung',
            'tingkat',
            'level',
            'ruanganAsal',
            'ruanganUjian',
            'pembayaran',
        ])
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $selectedTahunId);

        if ($request->filled('peserta_id')) {
            $query->where('peserta_imnis.id', $request->peserta_id);
            $peserta = $query->first();

            if (!$peserta) {
                return redirect()->back()->with('error', 'Peserta IMNI tidak ditemukan.');
            }

            // Validasi Kelayakan Administrasi
            $isLunas = $peserta->pembayaran && $peserta->pembayaran->status_pembayaran === 'Lunas';
            $isDispensasi = $peserta->status_kelayakan === 'Dispensasi';

            if (!$isLunas && !$isDispensasi) {
                return redirect()->back()->with('warning', "Kartu ujian untuk {$peserta->murid?->nama_lengkap} tidak dapat dicetak karena status pembayaran administrasi Belum Lunas dan belum memiliki dispensasi panitia.");
            }

            $pesertas = collect([$peserta]);
        } else {
            if ($request->filled('tingkat_id')) {
                $query->where('peserta_imnis.tingkat_id', $request->tingkat_id);
            }

            if ($request->filled('ruangan_ujian_id')) {
                $query->where('peserta_imnis.ruangan_ujian_id', $request->ruangan_ujian_id);
            }

            $allPesertas = $query
                ->orderBy('levels.urutan_level', 'asc')
                ->orderBy('murids.jenis_kelamin', 'asc')
                ->orderBy('murids.nama_lengkap', 'asc')
                ->get();

            // Filter hanya peserta yang memenuhi syarat (Lunas atau Dispensasi)
            $pesertas = $allPesertas->filter(function ($p) {
                $isLunas = $p->pembayaran && $p->pembayaran->status_pembayaran === 'Lunas';
                $isDispensasi = $p->status_kelayakan === 'Dispensasi';
                return $isLunas || $isDispensasi;
            });

            if ($pesertas->isEmpty()) {
                return redirect()->back()->with('warning', 'Tidak ada peserta ujian yang memenuhi syarat administrasi (Lunas / Dispensasi) pada filter yang dipilih.');
            }
        }

        $pesertas = $query
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.peserta.cetak-kartu', compact(
            'pesertas',
            'selectedTahun',
            'ketuaPanitia'
        ));
    }

    /**
     * Cetak Denah Tempat Duduk Ruangan Ujian IMNI
     */
    public function cetakDenah(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $ruanganId = $request->input('ruangan_ujian_id');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $ruangan = Ruangan::with('level')->findOrFail($ruanganId);

        $pesertas = PesertaImni::with(['murid', 'level'])
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $selectedTahunId)
            ->where('peserta_imnis.ruangan_ujian_id', $ruanganId)
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.peserta.cetak-denah', compact(
            'pesertas',
            'ruangan',
            'selectedTahun',
            'ketuaPanitia'
        ));
    }

    /**
     * Cetak Label Nomor Meja Ujian IMNI (Stiker / Badge Meja)
     */
    public function cetakLabelMeja(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $ruanganId = $request->input('ruangan_ujian_id');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $query = PesertaImni::with(['murid', 'level', 'ruanganUjian'])
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $selectedTahunId);

        if ($ruanganId) {
            $query->where('peserta_imnis.ruangan_ujian_id', $ruanganId);
        }

        $pesertas = $query
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        return view('ujian.panitia-imni.peserta.cetak-label-meja', compact(
            'pesertas',
            'selectedTahun'
        ));
    }

    /**
     * Cetak Daftar Nominatif Peserta (DNT) Ujian IMNI
     */
    public function cetakDaftar(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $tingkatId = $request->input('tingkat_id');
        $ruanganId = $request->input('ruangan_ujian_id');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $query = PesertaImni::with([
            'murid.waliMurid.kampung',
            'tingkat',
            'level',
            'ruanganAsal',
            'ruanganUjian'
        ])
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $selectedTahunId);

        if ($tingkatId) {
            $query->where('peserta_imnis.tingkat_id', $tingkatId);
        }

        if ($ruanganId) {
            $query->where('peserta_imnis.ruangan_ujian_id', $ruanganId);
        }

        $pesertas = $query
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.peserta.cetak-daftar-peserta', compact(
            'pesertas',
            'selectedTahun',
            'ketuaPanitia'
        ));
    }
}
