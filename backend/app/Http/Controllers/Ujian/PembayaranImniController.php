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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranImniController extends Controller
{
    /**
     * Halaman Utama Loket Kasir & Administrasi Pembayaran IMNI
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // Master Tingkat IMNI (Hanya TPQ, IBT, TSA)
        $daftarTingkat = Tingkat::where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('kode_tingkat', ['TPQ', 'IBT', 'TSA'])
                  ->orWhere('nama_tingkat', 'LIKE', '%TPQ%')
                  ->orWhere('nama_tingkat', 'LIKE', '%Ibtidaiyah%')
                  ->orWhere('nama_tingkat', 'LIKE', '%Tsanawiyah%');
            })
            ->orderBy('urutan_tingkat', 'asc')
            ->get();

        // Tingkat Terpilih (Default ke tingkat pertama, tidak ada opsi Semua Tingkat)
        $selectedTingkatId = $request->input('tingkat_id', $daftarTingkat->first()?->id);
        $selectedTingkat = $daftarTingkat->firstWhere('id', $selectedTingkatId) ?? $daftarTingkat->first();

        // Level Akhir yang Mengikuti IMNI untuk tingkat terpilih
        $levelAkhirQuery = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                  ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                  ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                  ->orWhereIn('urutan_level', [3, 9, 12]);
            });

        if ($selectedTingkatId) {
            $levelAkhirQuery->where('tingkat_id', $selectedTingkatId);
        }

        $levelAkhirIds = $levelAkhirQuery->pluck('id')->toArray();

        // Ruangan Ujian yang Mengikuti IMNI saja untuk tingkat terpilih
        $daftarRuangan = Ruangan::with('level')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->whereIn('level_id', $levelAkhirIds)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        // 1. Query Peserta IMNI beserta Data Pembayaran
        $query = PesertaImni::with([
            'murid.waliMurid',
            'tingkat',
            'level',
            'ruanganAsal',
            'ruanganUjian',
            'pembayaran.penerima'
        ])
        ->where('tahun_pelajaran_id', $selectedTahunId);

        // Filter Tingkat (Wajib terfilter per tingkat)
        if ($selectedTingkatId) {
            $query->where('peserta_imnis.tingkat_id', $selectedTingkatId);
        }

        // Filter Ruangan Ujian
        if ($request->filled('ruangan_ujian_id')) {
            $query->where('ruangan_ujian_id', $request->ruangan_ujian_id);
        }

        // Filter Status Pembayaran
        if ($request->filled('status_pembayaran')) {
            $status = $request->status_pembayaran;
            if ($status === 'Belum Ada Tagihan') {
                $query->whereDoesntHave('pembayaran');
            } else {
                $query->whereHas('pembayaran', function ($q) use ($status) {
                    $q->where('status_pembayaran', $status);
                });
            }
        }

        // Filter Search (Nama Murid / NISM / No Peserta / No Kwitansi)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_peserta', 'like', "%{$search}%")
                  ->orWhereHas('murid', function ($qm) use ($search) {
                      $qm->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nism', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pembayaran', function ($qp) use ($search) {
                      $qp->where('no_kwitansi', 'like', "%{$search}%")
                         ->orWhere('nama_penyetor', 'like', "%{$search}%");
                  });
            });
        }

        $pesertas = $query
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->paginate(30)
            ->withQueryString();

        // 2. Metrik Statistik Keuangan IMNI Tahun Ini untuk Tingkat Terpilih
        $allPesertaCount = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)
            ->when($selectedTingkatId, fn($q) => $q->where('tingkat_id', $selectedTingkatId))
            ->count();

        $pembayaranQuery = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)
            ->when($selectedTingkatId, function ($q) use ($selectedTingkatId) {
                $q->whereHas('peserta', fn($qp) => $qp->where('tingkat_id', $selectedTingkatId));
            });

        $totalTagihan  = (clone $pembayaranQuery)->sum('nominal_tagihan');
        $totalTerbayar = (clone $pembayaranQuery)->sum('nominal_bayar');
        $totalSisa     = (clone $pembayaranQuery)->sum('sisa_tagihan');

        $countLunas      = (clone $pembayaranQuery)->where('status_pembayaran', 'Lunas')->count();
        $countBelumLunas = (clone $pembayaranQuery)->where('status_pembayaran', 'Belum Lunas')->count();
        $countDispensasi = (clone $pembayaranQuery)->where('status_pembayaran', 'Dispensasi')->count();
        $countSudahAdaTagihan = (clone $pembayaranQuery)->count();
        $countBelumAdaTagihan = max(0, $allPesertaCount - $countSudahAdaTagihan);

        $persenLunas = ($totalTagihan > 0) ? round(($totalTerbayar / $totalTagihan) * 100, 1) : 0;

        // Pengaturan Tarif Tagihan IMNI Terkonfigurasi untuk Tahun Ini
        $daftarPengaturanTagihan = PengaturanTagihan::with('level')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->where(function ($q) {
                $q->where('kode_tagihan', 'IMNI')
                  ->orWhere('nama_tagihan', 'LIKE', '%IMNI%');
            })
            ->get();

        // SK Panitia Bendahara & Ketua
        $bendaharaPanitia = PanitiaImni::getBendahara($selectedTahunId);
        $ketuaPanitia     = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.pembayaran.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'daftarTingkat',
            'selectedTingkatId',
            'selectedTingkat',
            'daftarRuangan',
            'daftarPengaturanTagihan',
            'pesertas',
            'allPesertaCount',
            'totalTagihan',
            'totalTerbayar',
            'totalSisa',
            'countLunas',
            'countBelumLunas',
            'countDispensasi',
            'countBelumAdaTagihan',
            'persenLunas',
            'bendaharaPanitia',
            'ketuaPanitia'
        ));
    }

    /**
     * Tampilkan Modal Set Tarif Massal (AJAX)
     */
    public function modalTarifMassal(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);
        $daftarTingkat = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.pembayaran.modal-tarif-massal', compact(
                'selectedTahun',
                'daftarTingkat'
            ));
        }

        return redirect()->route('pembayaran-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Tampilkan Modal Kasir Pembayaran Murid (AJAX)
     */
    public function modalBayar($pesertaId, Request $request)
    {
        $peserta = PesertaImni::with(['murid', 'tingkat', 'level', 'ruanganUjian', 'pembayaran'])->findOrFail($pesertaId);
        $pembayaran = $peserta->pembayaran;

        $tagihan = (float) ($pembayaran?->nominal_tagihan ?? 0);
        $terbayar = (float) ($pembayaran?->nominal_bayar ?? 0);
        $sisa = (float) ($pembayaran?->sisa_tagihan ?? 0);
        $status = $pembayaran?->status_pembayaran ?? 'Belum Ada Tagihan';

        if ($request->ajax()) {
            return view('ujian.panitia-imni.pembayaran.modal-bayar', compact(
                'peserta',
                'pembayaran',
                'tagihan',
                'terbayar',
                'sisa',
                'status'
            ));
        }

        return redirect()->route('pembayaran-imni.index', ['tahun_id' => $peserta->tahun_pelajaran_id]);
    }

    /**
     * Tampilkan Modal Ubah Tagihan Individual (AJAX)
     */
    public function modalEditTagihan($pesertaId, Request $request)
    {
        $peserta = PesertaImni::with(['murid', 'tingkat', 'level', 'pembayaran'])->findOrFail($pesertaId);
        $pembayaran = $peserta->pembayaran;
        $tagihan = (float) ($pembayaran?->nominal_tagihan ?? 0);

        if ($request->ajax()) {
            return view('ujian.panitia-imni.pembayaran.modal-edit-tagihan', compact(
                'peserta',
                'pembayaran',
                'tagihan'
            ));
        }

        return redirect()->route('pembayaran-imni.index', ['tahun_id' => $peserta->tahun_pelajaran_id]);
    }

    /**
     * Terapkan / Sinkronkan Tarif Biaya Tagihan IMNI dari Pengaturan Tagihan Sesuai Level Murid
     */
    public function terapkanDariPengaturanTagihan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tingkat_id'         => 'nullable|exists:tingkats,id',
            'level_id'           => 'nullable|exists:levels,id',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tingkatId = $request->tingkat_id;
        $levelId = $request->level_id;

        // Ambil daftar pengaturan tarif tagihan IMNI untuk tahun ini
        $pengaturanTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
            ->where(function ($q) {
                $q->where('kode_tagihan', 'IMNI')
                  ->orWhere('nama_tagihan', 'LIKE', '%IMNI%');
            })
            ->get();

        if ($pengaturanTagihans->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Belum ada data Pengaturan Tagihan dengan kode/nama "IMNI" untuk tahun pelajaran ini. Silakan atur terlebih dahulu di menu Pengaturan Tagihan.'
                ], 200);
            }
            return redirect()->back()->with('warning', 'Belum ada data Pengaturan Tagihan dengan kode/nama "IMNI" untuk tahun pelajaran ini.');
        }

        $query = PesertaImni::where('tahun_pelajaran_id', $tahunId);
        if ($tingkatId) {
            $query->where('tingkat_id', $tingkatId);
        }
        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $pesertas = $query->get();

        if ($pesertas->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Tidak ada data peserta IMNI yang sesuai kriteria.'
                ], 200);
            }
            return redirect()->back()->with('warning', 'Tidak ada data peserta IMNI yang sesuai kriteria.');
        }

        DB::beginTransaction();
        try {
            $affected = 0;
            foreach ($pesertas as $p) {
                $tagihanLevel = $pengaturanTagihans->firstWhere('level_id', $p->level_id)
                    ?? $pengaturanTagihans->firstWhere('level_id', null);

                $nominal = $tagihanLevel ? (float) $tagihanLevel->nominal : 0;

                $pembayaran = PembayaranImni::where('peserta_imni_id', $p->id)->first();

                if (!$pembayaran) {
                    PembayaranImni::create([
                        'tahun_pelajaran_id' => $tahunId,
                        'peserta_imni_id'    => $p->id,
                        'murid_id'           => $p->murid_id,
                        'nominal_tagihan'    => $nominal,
                        'nominal_bayar'      => 0,
                        'sisa_tagihan'       => $nominal,
                        'status_pembayaran'  => ($nominal > 0 ? 'Belum Lunas' : 'Lunas'),
                        'metode_pembayaran'  => 'Tunai',
                    ]);
                } else {
                    $nominalBayar = (float) $pembayaran->nominal_bayar;
                    $sisaBaru = max(0, $nominal - $nominalBayar);

                    $status = $pembayaran->status_pembayaran;
                    if ($status !== 'Dispensasi') {
                        $status = ($sisaBaru <= 0) ? 'Lunas' : 'Belum Lunas';
                    }

                    $pembayaran->update([
                        'nominal_tagihan'   => $nominal,
                        'sisa_tagihan'      => $sisaBaru,
                        'status_pembayaran' => $status,
                    ]);
                }
                $affected++;
            }

            DB::commit();

            $msg = "Tarif tagihan IMNI berhasil disinkronkan dari Pengaturan Tagihan sesuai level murid untuk {$affected} peserta.";
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
                    'message' => 'Gagal menerapkan tarif dari Pengaturan Tagihan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menerapkan tarif dari Pengaturan Tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Penetapan Tarif Biaya Administrasi IMNI Secara Massal
     */
    public function setTarifMassal(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'tingkat_id'         => 'nullable|exists:tingkats,id',
            'nominal_tagihan'    => 'required|numeric|min:0',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tingkatId = $request->tingkat_id;
        $nominal = (float) $request->nominal_tagihan;

        $query = PesertaImni::where('tahun_pelajaran_id', $tahunId);
        if ($tingkatId) {
            $query->where('tingkat_id', $tingkatId);
        }

        $pesertas = $query->get();

        if ($pesertas->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Tidak ada data peserta IMNI untuk ditetapkan tagihannya.'
                ], 200);
            }
            return redirect()->back()->with('warning', 'Tidak ada data peserta IMNI untuk ditetapkan tagihannya.');
        }

        DB::beginTransaction();
        try {
            $affected = 0;
            foreach ($pesertas as $p) {
                $pembayaran = PembayaranImni::where('peserta_imni_id', $p->id)->first();

                if (!$pembayaran) {
                    // Buat baru
                    PembayaranImni::create([
                        'tahun_pelajaran_id' => $tahunId,
                        'peserta_imni_id'    => $p->id,
                        'murid_id'           => $p->murid_id,
                        'nominal_tagihan'    => $nominal,
                        'nominal_bayar'      => 0,
                        'sisa_tagihan'       => $nominal,
                        'status_pembayaran'  => ($nominal > 0 ? 'Belum Lunas' : 'Lunas'),
                        'metode_pembayaran'  => 'Tunai',
                    ]);
                } else {
                    // Update tagihan & kalkulasi ulang sisa
                    $nominalBayar = (float) $pembayaran->nominal_bayar;
                    $sisaBaru = max(0, $nominal - $nominalBayar);
                    
                    $status = $pembayaran->status_pembayaran;
                    if ($status !== 'Dispensasi') {
                        $status = ($sisaBaru <= 0) ? 'Lunas' : 'Belum Lunas';
                    }

                    $pembayaran->update([
                        'nominal_tagihan'   => $nominal,
                        'sisa_tagihan'      => $sisaBaru,
                        'status_pembayaran' => $status,
                    ]);
                }
                $affected++;
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Tarif administrasi IMNI sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil diterapkan untuk {$affected} murid."
                ], 200);
            }

            return redirect()->back()->with('success', "Tarif administrasi IMNI sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil diterapkan untuk {$affected} murid.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menetapkan tarif massal: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menetapkan tarif massal: ' . $e->getMessage());
        }
    }

    /**
     * Penetapan / Perubahan Tarif Biaya IMNI Individual Murid
     */
    public function setTarifIndividual(Request $request, $pesertaId)
    {
        $request->validate([
            'nominal_tagihan' => 'required|numeric|min:0',
        ]);

        $peserta = PesertaImni::findOrFail($pesertaId);
        $nominal = (float) $request->nominal_tagihan;

        DB::beginTransaction();
        try {
            $pembayaran = PembayaranImni::where('peserta_imni_id', $peserta->id)->first();

            if (!$pembayaran) {
                PembayaranImni::create([
                    'tahun_pelajaran_id' => $peserta->tahun_pelajaran_id,
                    'peserta_imni_id'    => $peserta->id,
                    'murid_id'           => $peserta->murid_id,
                    'nominal_tagihan'    => $nominal,
                    'nominal_bayar'      => 0,
                    'sisa_tagihan'       => $nominal,
                    'status_pembayaran'  => ($nominal > 0 ? 'Belum Lunas' : 'Lunas'),
                    'metode_pembayaran'  => 'Tunai',
                ]);
            } else {
                $nominalBayar = (float) $pembayaran->nominal_bayar;
                $sisaBaru = max(0, $nominal - $nominalBayar);
                $status = $pembayaran->status_pembayaran;
                if ($status !== 'Dispensasi') {
                    $status = ($sisaBaru <= 0) ? 'Lunas' : 'Belum Lunas';
                }

                $pembayaran->update([
                    'nominal_tagihan'   => $nominal,
                    'sisa_tagihan'      => $sisaBaru,
                    'status_pembayaran' => $status,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tagihan murid berhasil diperbarui.'
                ], 200);
            }

            return redirect()->back()->with('success', 'Tagihan murid berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal mengubah tagihan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal mengubah tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Transaksi Kasir Pembayaran Biaya IMNI
     */
    public function bayar(Request $request)
    {
        $request->validate([
            'peserta_imni_id'   => 'required|exists:peserta_imnis,id',
            'nominal_bayar'     => 'required|numeric|min:0',
            'tanggal_bayar'     => 'required|date',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,Lainnya',
            'nama_penyetor'     => 'nullable|string|max:100',
            'status_override'   => 'nullable|in:Auto,Dispensasi,Lunas,Belum Lunas',
            'keterangan'        => 'nullable|string|max:255',
            'mode_input'        => 'nullable|in:tambah,total',
        ]);

        $peserta = PesertaImni::with('murid')->findOrFail($request->peserta_imni_id);
        $tahunPelajaran = TahunPelajaran::find($peserta->tahun_pelajaran_id);
        $inputBayar = (float) $request->nominal_bayar;
        $modeInput = $request->input('mode_input', 'tambah');

        DB::beginTransaction();
        try {
            $pembayaran = PembayaranImni::where('peserta_imni_id', $peserta->id)->first();

            if (!$pembayaran) {
                // Jika belum ada tagihan tapi langsung dibayar, tetapkan tagihan awal = bayar
                $tagihanAwal = $inputBayar;
                $pembayaran = PembayaranImni::create([
                    'tahun_pelajaran_id' => $peserta->tahun_pelajaran_id,
                    'peserta_imni_id'    => $peserta->id,
                    'murid_id'           => $peserta->murid_id,
                    'nominal_tagihan'    => $tagihanAwal,
                    'nominal_bayar'      => 0,
                    'sisa_tagihan'       => $tagihanAwal,
                    'status_pembayaran'  => 'Belum Lunas',
                    'metode_pembayaran'  => $request->metode_pembayaran,
                ]);
            }

            // Hitung akumulasi pembayaran
            if ($modeInput === 'total') {
                $newTotalBayar = $inputBayar;
            } else {
                $newTotalBayar = (float) $pembayaran->nominal_bayar + $inputBayar;
            }

            $tagihan = (float) $pembayaran->nominal_tagihan;
            $sisa = max(0, $tagihan - $newTotalBayar);

            // Tentukan status pembayaran
            $statusFinal = 'Belum Lunas';
            if ($request->status_override && $request->status_override !== 'Auto') {
                $statusFinal = $request->status_override;
            } else {
                $statusFinal = ($sisa <= 0) ? 'Lunas' : 'Belum Lunas';
            }

            // Generate nomor kwitansi jika belum ada
            $noKwitansi = $pembayaran->no_kwitansi;
            if (empty($noKwitansi) && $newTotalBayar > 0) {
                $noKwitansi = PembayaranImni::generateNoKwitansi($tahunPelajaran, $request->tanggal_bayar);
            }

            $pembayaran->update([
                'nominal_bayar'     => $newTotalBayar,
                'sisa_tagihan'      => $sisa,
                'tanggal_bayar'     => $request->tanggal_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusFinal,
                'no_kwitansi'       => $noKwitansi,
                'nama_penyetor'     => $request->nama_penyetor ?? $peserta->murid?->nama_lengkap,
                'keterangan'        => $request->keterangan,
                'diterima_oleh'     => Auth::id(),
            ]);

            DB::commit();

            $namaMurid = $peserta->murid?->nama_lengkap ?? 'Murid';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Pembayaran IMNI untuk {$namaMurid} sebesar Rp " . number_format($inputBayar, 0, ',', '.') . " berhasil diproses ({$statusFinal})."
                ], 200);
            }

            return redirect()->back()->with('success', "Pembayaran IMNI untuk {$namaMurid} sebesar Rp " . number_format($inputBayar, 0, ',', '.') . " berhasil diproses ({$statusFinal}).");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan Transaksi Pembayaran
     */
    public function batal(Request $request, $id)
    {
        $pembayaran = PembayaranImni::with('murid')->findOrFail($id);

        DB::beginTransaction();
        try {
            $tagihan = (float) $pembayaran->nominal_tagihan;

            $pembayaran->update([
                'nominal_bayar'     => 0,
                'sisa_tagihan'      => $tagihan,
                'status_pembayaran' => ($tagihan > 0 ? 'Belum Lunas' : 'Lunas'),
                'tanggal_bayar'     => null,
                'no_kwitansi'       => null,
                'nama_penyetor'     => null,
                'diterima_oleh'     => null,
                'keterangan'        => 'Pembayaran dibatalkan/reset pada ' . date('d/m/Y H:i'),
            ]);

            DB::commit();

            $namaMurid = $pembayaran->murid?->nama_lengkap ?? 'Murid';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Transaksi pembayaran untuk {$namaMurid} telah dibatalkan & direset."
                ], 200);
            }

            return redirect()->back()->with('success', "Transaksi pembayaran untuk {$namaMurid} telah dibatalkan & direset.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal membatalkan pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Lembar Kwitansi Tanda Terima Resmi IMNI
     */
    public function cetakKwitansi($id)
    {
        $pembayaran = PembayaranImni::with([
            'peserta.murid.waliMurid.kampung',
            'peserta.tingkat',
            'peserta.level',
            'peserta.ruanganUjian',
            'tahunPelajaran',
            'penerima'
        ])->findOrFail($id);

        $selectedTahun = $pembayaran->tahunPelajaran;
        $bendaharaPanitia = PanitiaImni::getBendahara($pembayaran->tahun_pelajaran_id);
        $ketuaPanitia     = PanitiaImni::getKetua($pembayaran->tahun_pelajaran_id);

        return view('ujian.panitia-imni.pembayaran.cetak-kwitansi', compact(
            'pembayaran',
            'selectedTahun',
            'bendaharaPanitia',
            'ketuaPanitia'
        ));
    }

    /**
     * Cetak Rekapitulasi Laporan Pembayaran IMNI
     */
    public function cetakRekap(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $daftarTingkat = Tingkat::where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('kode_tingkat', ['TPQ', 'IBT', 'TSA'])
                  ->orWhere('nama_tingkat', 'LIKE', '%TPQ%')
                  ->orWhere('nama_tingkat', 'LIKE', '%Ibtidaiyah%')
                  ->orWhere('nama_tingkat', 'LIKE', '%Tsanawiyah%');
            })
            ->orderBy('urutan_tingkat', 'asc')
            ->get();

        $selectedTingkatId = $request->input('tingkat_id', $daftarTingkat->first()?->id);
        $selectedTingkat = $daftarTingkat->firstWhere('id', $selectedTingkatId) ?? $daftarTingkat->first();

        $query = PesertaImni::with([
            'murid',
            'tingkat',
            'level',
            'ruanganUjian',
            'pembayaran.penerima'
        ])->where('tahun_pelajaran_id', $selectedTahunId);

        if ($selectedTingkatId) {
            $query->where('tingkat_id', $selectedTingkatId);
        }

        if ($request->filled('ruangan_ujian_id')) {
            $query->where('ruangan_ujian_id', $request->ruangan_ujian_id);
        }

        if ($request->filled('status_pembayaran')) {
            $status = $request->status_pembayaran;
            $query->whereHas('pembayaran', function ($q) use ($status) {
                $q->where('status_pembayaran', $status);
            });
        }

        $pesertas = $query
            ->select('peserta_imnis.*')
            ->join('levels', 'peserta_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $pembayaranQuery = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)
            ->when($selectedTingkatId, function ($q) use ($selectedTingkatId) {
                $q->whereHas('peserta', fn($qp) => $qp->where('tingkat_id', $selectedTingkatId));
            });

        $totalTagihan  = (clone $pembayaranQuery)->sum('nominal_tagihan');
        $totalTerbayar = (clone $pembayaranQuery)->sum('nominal_bayar');
        $totalSisa     = (clone $pembayaranQuery)->sum('sisa_tagihan');

        $bendaharaPanitia = PanitiaImni::getBendahara($selectedTahunId);
        $ketuaPanitia     = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.pembayaran.cetak-rekap', compact(
            'pesertas',
            'selectedTahun',
            'selectedTingkat',
            'totalTagihan',
            'totalTerbayar',
            'totalSisa',
            'bendaharaPanitia',
            'ketuaPanitia'
        ));
    }
}
