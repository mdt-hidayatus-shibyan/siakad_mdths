<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PembayaranImni;
use App\Models\Ujian\PengeluaranImni;
use App\Models\Ujian\PesertaImni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengeluaranImniController extends Controller
{
    /**
     * Daftar Kategori Pengeluaran Resmi Panitia IMNI
     */
    public const KATEGORI_LIST = [
        'Pra IMNI',
        'Saat IMNI',
        'Pasca IMNI (Wisuda)',
    ];

    /**
     * Halaman Utama Buku Kas Keluar & Operasional IMNI
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $kategoriList = self::KATEGORI_LIST;

        // 1. Query Pengeluaran IMNI
        $query = PengeluaranImni::with(['pencatat', 'tahunPelajaran'])
            ->where('tahun_pelajaran_id', $selectedTahunId);

        // Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_pengeluaran', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_pengeluaran', '<=', $request->tanggal_akhir);
        }

        // Filter Pencarian (Judul / Kode / Penerima)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_pengeluaran', 'like', "%{$search}%")
                  ->orWhere('kode_transaksi', 'like', "%{$search}%")
                  ->orWhere('penerima_dana', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $pengeluarans = $query->orderBy('tanggal_pengeluaran', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        // 2. Metrik Buku Kas IMNI
        $totalPemasukan = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal_bayar');
        $totalPengeluaran = PengeluaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // Breakdown per Kategori
        $kategoriTotals = PengeluaranImni::where('tahun_pelajaran_id', $selectedTahunId)
            ->select('kategori', DB::raw('SUM(nominal) as total'), DB::raw('COUNT(id) as count'))
            ->groupBy('kategori')
            ->pluck('total', 'kategori')
            ->toArray();

        // Data Personel SK Panitia
        $bendaharaPanitia = PanitiaImni::getBendahara($selectedTahunId);
        $ketuaPanitia     = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.pengeluaran.index', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'kategoriList',
            'pengeluarans',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoKas',
            'kategoriTotals',
            'bendaharaPanitia',
            'ketuaPanitia'
        ));
    }

    /**
     * Tampilkan Modal Tambah Pengeluaran (AJAX)
     */
    public function create(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id') ?? 1);
        $selectedTahun = TahunPelajaran::findOrFail($tahunId);
        $kategoriList = self::KATEGORI_LIST;

        if ($request->ajax()) {
            return view('ujian.panitia-imni.pengeluaran.form', compact('selectedTahun', 'kategoriList'));
        }

        return redirect()->route('pengeluaran-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Tampilkan Modal Edit Pengeluaran (AJAX)
     */
    public function edit($id, Request $request)
    {
        $pengeluaran = PengeluaranImni::with('tahunPelajaran')->findOrFail($id);
        $selectedTahun = $pengeluaran->tahunPelajaran;
        $kategoriList = self::KATEGORI_LIST;

        if ($request->ajax()) {
            return view('ujian.panitia-imni.pengeluaran.form', compact('pengeluaran', 'selectedTahun', 'kategoriList'));
        }

        return redirect()->route('pengeluaran-imni.index', ['tahun_id' => $pengeluaran->tahun_pelajaran_id]);
    }

    /**
     * Simpan Pengeluaran Kas Keluar IMNI Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id'  => 'required|exists:tahun_pelajarans,id',
            'kategori'            => 'required|in:' . implode(',', self::KATEGORI_LIST),
            'judul_pengeluaran'   => 'required|string|max:200',
            'nominal'             => 'required|numeric|min:1',
            'tanggal_pengeluaran' => 'required|date',
            'penerima_dana'       => 'nullable|string|max:100',
            'metode_pembayaran'   => 'required|in:Tunai,Transfer,Lainnya',
            'bukti_nota'          => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:3072',
            'keterangan'          => 'nullable|string|max:500',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $tahunPelajaran = TahunPelajaran::findOrFail($tahunId);

        DB::beginTransaction();
        try {
            $countExisting = PengeluaranImni::where('tahun_pelajaran_id', $tahunId)->count();
            $kodeTransaksi = PengeluaranImni::generateKodeTransaksi($tahunPelajaran, $countExisting + 1);

            $buktiPath = null;
            if ($request->hasFile('bukti_nota')) {
                $buktiPath = $request->file('bukti_nota')->store('pengeluaran_imni', 'public');
            }

            PengeluaranImni::create([
                'tahun_pelajaran_id'  => $tahunId,
                'kode_transaksi'      => $kodeTransaksi,
                'kategori'            => $request->kategori,
                'judul_pengeluaran'   => $request->judul_pengeluaran,
                'nominal'             => $request->nominal,
                'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
                'penerima_dana'       => $request->penerima_dana,
                'metode_pembayaran'   => $request->metode_pembayaran,
                'bukti_nota'          => $buktiPath,
                'keterangan'          => $request->keterangan,
                'dicatat_oleh'        => Auth::id(),
            ]);

            DB::commit();

            $msg = "Pengeluaran kas IMNI '{$request->judul_pengeluaran}' sebesar Rp " . number_format($request->nominal, 0, ',', '.') . " berhasil dicatat.";
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
                    'message' => 'Gagal mencatat pengeluaran: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Update Pengeluaran Kas Keluar IMNI
     */
    public function update(Request $request, $id)
    {
        $pengeluaran = PengeluaranImni::findOrFail($id);

        $request->validate([
            'kategori'            => 'required|in:' . implode(',', self::KATEGORI_LIST),
            'judul_pengeluaran'   => 'required|string|max:200',
            'nominal'             => 'required|numeric|min:1',
            'tanggal_pengeluaran' => 'required|date',
            'penerima_dana'       => 'nullable|string|max:100',
            'metode_pembayaran'   => 'required|in:Tunai,Transfer,Lainnya',
            'bukti_nota'          => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:3072',
            'keterangan'          => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $buktiPath = $pengeluaran->bukti_nota;
            if ($request->hasFile('bukti_nota')) {
                // Hapus file lama jika ada
                if ($pengeluaran->bukti_nota && Storage::disk('public')->exists($pengeluaran->bukti_nota)) {
                    Storage::disk('public')->delete($pengeluaran->bukti_nota);
                }
                $buktiPath = $request->file('bukti_nota')->store('pengeluaran_imni', 'public');
            }

            $pengeluaran->update([
                'kategori'            => $request->kategori,
                'judul_pengeluaran'   => $request->judul_pengeluaran,
                'nominal'             => $request->nominal,
                'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
                'penerima_dana'       => $request->penerima_dana,
                'metode_pembayaran'   => $request->metode_pembayaran,
                'bukti_nota'          => $buktiPath,
                'keterangan'          => $request->keterangan,
            ]);

            DB::commit();

            $msg = 'Data pengeluaran IMNI berhasil diperbarui.';
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
                    'message' => 'Gagal memperbarui pengeluaran: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Pengeluaran Kas Keluar IMNI
     */
    public function destroy(Request $request, $id)
    {
        $pengeluaran = PengeluaranImni::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($pengeluaran->bukti_nota && Storage::disk('public')->exists($pengeluaran->bukti_nota)) {
                Storage::disk('public')->delete($pengeluaran->bukti_nota);
            }

            $judul = $pengeluaran->judul_pengeluaran;
            $pengeluaran->delete();

            DB::commit();

            $msg = "Pengeluaran '{$judul}' berhasil dihapus.";
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
                    'message' => 'Gagal menghapus pengeluaran: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal menghapus pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Laporan Pertanggungjawaban (LPJ) Kas IMNI
     */
    public function cetakLpj(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // 1. Data Pemasukan
        $totalTagihan   = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal_tagihan');
        $totalPemasukan = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal_bayar');
        $totalPiutang   = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('sisa_tagihan');
        $countPeserta   = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->count();
        $countLunas     = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->where('status_pembayaran', 'Lunas')->count();

        // Pemasukan per Tingkat
        $pemasukanPerTingkat = DB::table('pembayaran_imnis')
            ->join('peserta_imnis', 'pembayaran_imnis.peserta_imni_id', '=', 'peserta_imnis.id')
            ->join('tingkats', 'peserta_imnis.tingkat_id', '=', 'tingkats.id')
            ->where('pembayaran_imnis.tahun_pelajaran_id', $selectedTahunId)
            ->select(
                'tingkats.nama_tingkat',
                'tingkats.kode_tingkat',
                DB::raw('COUNT(peserta_imnis.id) as total_peserta'),
                DB::raw('SUM(pembayaran_imnis.nominal_tagihan) as tagihan'),
                DB::raw('SUM(pembayaran_imnis.nominal_bayar) as bayar'),
                DB::raw('SUM(pembayaran_imnis.sisa_tagihan) as sisa')
            )
            ->groupBy('tingkats.id', 'tingkats.nama_tingkat', 'tingkats.kode_tingkat')
            ->get();

        // 2. Data Pengeluaran
        $pengeluarans = PengeluaranImni::with('pencatat')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->orderBy('tanggal_pengeluaran', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalPengeluaran = $pengeluarans->sum('nominal');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // Rekapitulasi per Kategori Pengeluaran
        $rekapKategori = $pengeluarans->groupBy('kategori')->map(function ($items, $key) {
            return (object) [
                'kategori'    => $key,
                'total'       => $items->sum('nominal'),
                'count'       => $items->count(),
            ];
        });

        // SK Panitia
        $bendaharaPanitia = PanitiaImni::getBendahara($selectedTahunId);
        $ketuaPanitia     = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.pengeluaran.cetak-lpj', compact(
            'selectedTahun',
            'countPeserta',
            'countLunas',
            'totalTagihan',
            'totalPemasukan',
            'totalPiutang',
            'pemasukanPerTingkat',
            'pengeluarans',
            'totalPengeluaran',
            'saldoAkhir',
            'rekapKategori',
            'bendaharaPanitia',
            'ketuaPanitia'
        ));
    }
}
