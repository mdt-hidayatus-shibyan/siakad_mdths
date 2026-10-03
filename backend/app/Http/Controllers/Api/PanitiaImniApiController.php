<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Level;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PembayaranImni;
use App\Models\Ujian\PengawasRuanganImni;
use App\Models\Ujian\PengeluaranImni;
use App\Models\Ujian\PesertaImni;
use App\Models\Ujian\PesertaRuanganImni;
use App\Models\Ujian\PresensiPengawasUjian;
use App\Models\Ujian\PresensiUjian;
use App\Models\Ujian\RuanganImni;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PanitiaImniApiController extends Controller
{
    /**
     * Helper: Validasi Akses Panitia IMNI / Administrator
     */
    protected function checkPanitiaAccess($user, $tahunId)
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['administrator', 'staff'])) {
            return true;
        }

        if (!$user->ustadz) {
            return false;
        }

        return PanitiaImni::where('ustadz_id', $user->ustadz->id)
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Helper: Dapatkan Tahun Pelajaran Target
     */
    protected function getTahunId(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        return $request->tahun_id ?? $tahunAktif?->id ?? TahunPelajaran::orderBy('id', 'asc')->value('id');
    }

    /**
     * Helper: Dapatkan Daftar Ruangan Fisik yang Mengikuti IMNI
     */
    protected function getRuanganPesertaImni($tahunId)
    {
        $ruanganIds = PesertaImni::where('tahun_pelajaran_id', $tahunId)
            ->whereNotNull('ruangan_asal_id')
            ->pluck('ruangan_asal_id')
            ->unique()
            ->toArray();

        if (empty($ruanganIds)) {
            $ruanganIds = PesertaImni::where('tahun_pelajaran_id', $tahunId)
                ->whereNotNull('ruangan_ujian_id')
                ->pluck('ruangan_ujian_id')
                ->unique()
                ->toArray();
        }

        if (empty($ruanganIds)) {
            $ruanganIds = Ruangan::where('tahun_pelajaran_id', $tahunId)
                ->whereHas('level', function ($q) {
                    $q->where('nama_level', 'like', '%3 TPQ%')
                        ->orWhere('nama_level', 'like', '%6%')
                        ->orWhere('nama_level', 'like', '%3 TSA%');
                })
                ->pluck('id')
                ->toArray();
        }

        return Ruangan::whereIn('id', $ruanganIds)
            ->with('level.tingkat')
            ->orderBy('level_id', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_id' => $r->level_id,
                    'nama_level' => $r->level?->nama_level ?? '-',
                    'tingkat_id' => $r->level?->tingkat_id,
                    'kode_tingkat' => $r->level?->tingkat?->kode_tingkat ?? '-',
                ];
            });
    }

    // =========================================================================
    // 1. MODUL PEMBAYARAN IMNI (PENERIMAAN KAS PANITIA)
    // =========================================================================

    /**
     * Ringkasan Keuangan Penerimaan Tagihan IMNI
     * GET /api/panitia-imni/pembayaran/ringkasan
     */
    public function getPembayaranRingkasan(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $allPeserta = PesertaImni::with('pembayaran')
            ->where('tahun_pelajaran_id', $tahunId)
            ->get();

        $totalPeserta = $allPeserta->count();
        $totalTagihan = 0;
        $totalTerbayar = 0;
        $sisaPiutang = 0;
        $totalLunas = 0;
        $totalBelumLunas = 0;
        $totalDispensasi = 0;

        foreach ($allPeserta as $p) {
            $pem = $p->pembayaran;
            $nomTagihan = $pem ? (float) $pem->nominal_tagihan : 0;
            $nomBayar = $pem ? (float) $pem->nominal_bayar : 0;
            $sisa = $pem ? (float) $pem->sisa_tagihan : $nomTagihan;

            $totalTagihan += $nomTagihan;
            $totalTerbayar += $nomBayar;
            $sisaPiutang += $sisa;

            if ($pem && $pem->status_pembayaran === 'Lunas') {
                $totalLunas++;
            } elseif ($p->status_kelayakan === 'Dispensasi') {
                $totalDispensasi++;
            } else {
                $totalBelumLunas++;
            }
        }

        $persentase = $totalTagihan > 0 ? round(($totalTerbayar / $totalTagihan) * 100, 1) : 0;

        // Breakdown per Tingkat (TPQ, IBT, TSA)
        $tingkats = Tingkat::where('is_active', true)->orderBy('urutan_tingkat')->get();
        $breakdownTingkat = [];
        foreach ($tingkats as $t) {
            $pesertaTingkat = $allPeserta->where('tingkat_id', $t->id);
            $tagihanT = 0;
            $bayarT = 0;
            $lunasT = 0;
            foreach ($pesertaTingkat as $pt) {
                $tagihanT += (float) ($pt->pembayaran?->nominal_tagihan ?? 0);
                $bayarT += (float) ($pt->pembayaran?->nominal_bayar ?? 0);
                if ($pt->pembayaran?->status_pembayaran === 'Lunas') $lunasT++;
            }
            $breakdownTingkat[] = [
                'tingkat_id' => $t->id,
                'kode_tingkat' => $t->kode_tingkat,
                'nama_tingkat' => $t->nama_tingkat,
                'total_peserta' => $pesertaTingkat->count(),
                'total_lunas' => $lunasT,
                'total_tagihan' => $tagihanT,
                'total_terbayar' => $bayarT,
                'sisa_piutang' => max(0, $tagihanT - $bayarT),
            ];
        }

        // Daftar Ruangan Asal yang mengikuti IMNI
        $ruanganAsalIds = PesertaImni::where('tahun_pelajaran_id', $tahunId)
            ->whereNotNull('ruangan_asal_id')
            ->pluck('ruangan_asal_id')
            ->unique()
            ->toArray();

        $daftarRuangan = Ruangan::whereIn('id', $ruanganAsalIds)
            ->with('level.tingkat')
            ->orderBy('level_id', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_id' => $r->level_id,
                    'nama_level' => $r->level?->nama_level ?? '-',
                    'tingkat_id' => $r->level?->tingkat_id,
                    'kode_tingkat' => $r->level?->tingkat?->kode_tingkat ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_peserta' => $totalPeserta,
                'total_tagihan' => $totalTagihan,
                'total_terbayar' => $totalTerbayar,
                'sisa_piutang' => $sisaPiutang,
                'persentase_terkumpul' => $persentase,
                'total_lunas' => $totalLunas,
                'total_belum_lunas' => $totalBelumLunas,
                'total_dispensasi' => $totalDispensasi,
                'breakdown_tingkat' => $breakdownTingkat,
                'daftar_ruangan' => $daftarRuangan,
            ]
        ], 200);
    }

    /**
     * Daftar Peserta & Status Tagihan IMNI
     * GET /api/panitia-imni/pembayaran/peserta-list
     */
    public function getPembayaranPesertaList(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $query = PesertaImni::with([
            'murid',
            'tingkat',
            'level',
            'ruanganAsal',
            'ruanganUjian',
            'pembayaran.penerima',
        ])
            ->select('peserta_imnis.*')
            ->join('murids', 'peserta_imnis.murid_id', '=', 'murids.id')
            ->where('peserta_imnis.tahun_pelajaran_id', $tahunId);

        if ($request->filled('tingkat_id')) {
            $query->where('peserta_imnis.tingkat_id', $request->tingkat_id);
        }

        if ($request->filled('ruangan_id')) {
            $ruangId = $request->ruangan_id;
            $query->where(function ($q) use ($ruangId) {
                $q->where('peserta_imnis.ruangan_asal_id', $ruangId)
                    ->orWhere('peserta_imnis.ruangan_ujian_id', $ruangId);
            });
        }

        if ($request->filled('status_pembayaran')) {
            $status = $request->status_pembayaran;
            if ($status === 'Lunas') {
                $query->whereHas('pembayaran', fn($q) => $q->where('status_pembayaran', 'Lunas'));
            } elseif ($status === 'Dispensasi') {
                $query->where('peserta_imnis.status_kelayakan', 'Dispensasi');
            } elseif ($status === 'Belum Lunas') {
                $query->where(function ($q) {
                    $q->whereHas('pembayaran', fn($pq) => $pq->where('status_pembayaran', '!=', 'Lunas'))
                        ->orWhereDoesntHave('pembayaran');
                })->where('peserta_imnis.status_kelayakan', '!=', 'Dispensasi');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('murids.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('murids.nism', 'like', "%{$search}%")
                    ->orWhere('peserta_imnis.nomor_peserta', 'like', "%{$search}%");
            });
        }

        $pesertas = $query
            ->orderBy('peserta_imnis.tingkat_id', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $data = $pesertas->map(function ($p) {
            $pem = $p->pembayaran;
            $isLunas = $pem && $pem->status_pembayaran === 'Lunas';
            $isDispensasi = $p->status_kelayakan === 'Dispensasi';

            return [
                'id' => $p->id,
                'nomor_peserta' => $p->nomor_peserta ?? '-',
                'nomor_meja' => $p->nomor_meja,
                'murid_id' => $p->murid_id,
                'nama_lengkap' => $p->murid?->nama_lengkap ?? '-',
                'nism' => $p->murid?->nism ?? '-',
                'jenis_kelamin' => $p->murid?->jenis_kelamin ?? 'L',
                'foto' => $p->murid && $p->murid->foto ? asset('storage/' . $p->murid->foto) : null,
                'tingkat_id' => $p->tingkat_id,
                'nama_tingkat' => $p->tingkat?->nama_tingkat ?? '-',
                'kode_tingkat' => $p->tingkat?->kode_tingkat ?? '-',
                'nama_level' => $p->level?->nama_level ?? '-',
                'ruangan_asal' => $p->ruanganAsal?->nama_ruangan ?? '-',
                'ruangan_ujian' => $p->ruanganUjian?->nama_ruangan ?? 'Belum Diplot',
                'status_kelayakan' => $p->status_kelayakan,
                'catatan_dispensasi' => $p->catatan,
                'pembayaran' => [
                    'id' => $pem?->id,
                    'no_kwitansi' => $pem?->no_kwitansi ?? '-',
                    'nominal_tagihan' => (float) ($pem?->nominal_tagihan ?? 0),
                    'nominal_bayar' => (float) ($pem?->nominal_bayar ?? 0),
                    'sisa_tagihan' => (float) ($pem?->sisa_tagihan ?? 0),
                    'status_pembayaran' => $isLunas ? 'Lunas' : ($isDispensasi ? 'Dispensasi' : 'Belum Lunas'),
                    'tanggal_bayar' => $pem?->tanggal_bayar ? $pem->tanggal_bayar->format('Y-m-d') : null,
                    'tanggal_bayar_format' => $pem?->tanggal_bayar ? $pem->tanggal_bayar->translatedFormat('d M Y') : null,
                    'metode_pembayaran' => $pem?->metode_pembayaran ?? 'Tunai',
                    'nama_penyetor' => $pem?->nama_penyetor,
                    'penerima_nama' => $pem?->penerima?->name ?? 'Bendahara',
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    /**
     * Input Pembayaran Tagihan IMNI
     * POST /api/panitia-imni/pembayaran/simpan-bayar
     */
    public function simpanPembayaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:peserta_imnis,id',
            'nominal_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'nullable|date',
            'nama_penyetor' => 'nullable|string|max:100',
            'metode_pembayaran' => 'nullable|in:Tunai,Transfer,Lainnya',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $peserta = PesertaImni::with(['murid', 'pembayaran'])->findOrFail($request->peserta_id);
        $user = $request->user();
        $tahunId = $peserta->tahun_pelajaran_id;

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $tahunPelajaran = TahunPelajaran::find($tahunId);
        $tanggalBayar = $request->tanggal_bayar ? Carbon::parse($request->tanggal_bayar) : Carbon::now();
        $nominalBayar = (float) $request->nominal_bayar;

        DB::beginTransaction();
        try {
            $pem = $peserta->pembayaran;
            if (!$pem) {
                $pem = new PembayaranImni([
                    'tahun_pelajaran_id' => $tahunId,
                    'peserta_imni_id' => $peserta->id,
                    'murid_id' => $peserta->murid_id,
                    'nominal_tagihan' => $nominalBayar,
                    'nominal_bayar' => 0,
                    'sisa_tagihan' => $nominalBayar,
                ]);
            }

            $totalBayarBaru = (float) $pem->nominal_bayar + $nominalBayar;
            $sisaBaru = max(0, (float) $pem->nominal_tagihan - $totalBayarBaru);
            $statusBayar = ($sisaBaru <= 0) ? 'Lunas' : 'Belum Lunas';

            if (empty($pem->no_kwitansi)) {
                $pem->no_kwitansi = PembayaranImni::generateNoKwitansi($tahunPelajaran, $tanggalBayar);
            }

            $pem->nominal_bayar = $totalBayarBaru;
            $pem->sisa_tagihan = $sisaBaru;
            $pem->status_pembayaran = $statusBayar;
            $pem->tanggal_bayar = $tanggalBayar;
            $pem->metode_pembayaran = $request->metode_pembayaran ?? 'Tunai';
            $pem->nama_penyetor = $request->nama_penyetor ?? ($peserta->murid?->nama_lengkap ?? 'Wali Murid');
            $pem->keterangan = $request->keterangan;
            $pem->diterima_oleh = $user->id;
            $pem->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pembayaran sebesar Rp " . number_format($nominalBayar, 0, ',', '.') . " untuk {$peserta->murid?->nama_lengkap} berhasil disimpan.",
                'data' => [
                    'no_kwitansi' => $pem->no_kwitansi,
                    'nominal_bayar' => $pem->nominal_bayar,
                    'sisa_tagihan' => $pem->sisa_tagihan,
                    'status_pembayaran' => $pem->status_pembayaran,
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan / Reset Pembayaran IMNI
     * POST /api/panitia-imni/pembayaran/batal-bayar/{id}
     */
    public function batalPembayaran(Request $request, $id)
    {
        $pem = PembayaranImni::with('murid')->findOrFail($id);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $pem->tahun_pelajaran_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $pem->nominal_bayar = 0;
        $pem->sisa_tagihan = $pem->nominal_tagihan;
        $pem->status_pembayaran = 'Belum Lunas';
        $pem->tanggal_bayar = null;
        $pem->diterima_oleh = null;
        $pem->save();

        return response()->json([
            'success' => true,
            'message' => "Pembayaran untuk {$pem->murid?->nama_lengkap} berhasil dibatalkan."
        ], 200);
    }

    // =========================================================================
    // 2. MODUL PENGELUARAN IMNI (ANGGARAN & REALISASI BIAYA)
    // =========================================================================

    /**
     * Ringkasan Keuangan Pengeluaran & Saldo Kas IMNI
     * GET /api/panitia-imni/pengeluaran/ringkasan
     */
    public function getPengeluaranRingkasan(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $totalPemasukan = (float) PembayaranImni::where('tahun_pelajaran_id', $tahunId)->sum('nominal_bayar');
        $totalPengeluaran = (float) PengeluaranImni::where('tahun_pelajaran_id', $tahunId)->sum('nominal');
        $sisaSaldo = $totalPemasukan - $totalPengeluaran;

        $pengeluaranList = PengeluaranImni::where('tahun_pelajaran_id', $tahunId)->get();

        $kategoris = [
            'Pra IMNI',
            'Saat IMNI',
            'Pasca IMNI (Wisuda)',
        ];

        $breakdownKategori = [];
        foreach ($kategoris as $kat) {
            $totalKat = (float) $pengeluaranList->where('kategori', $kat)->sum('nominal');
            $breakdownKategori[] = [
                'kategori' => $kat,
                'total' => $totalKat,
                'persentase' => $totalPengeluaran > 0 ? round(($totalKat / $totalPengeluaran) * 100, 1) : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'sisa_saldo' => $sisaSaldo,
                'breakdown_kategori' => $breakdownKategori,
            ]
        ], 200);
    }

    /**
     * Daftar Riwayat Pengeluaran IMNI
     * GET /api/panitia-imni/pengeluaran/list
     */
    public function getPengeluaranList(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $query = PengeluaranImni::with('pencatat')
            ->where('tahun_pelajaran_id', $tahunId);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_pengeluaran', 'like', "%{$search}%")
                    ->orWhere('kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('penerima_dana', 'like', "%{$search}%");
            });
        }

        $pengeluaran = $query->orderBy('tanggal_pengeluaran', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $data = $pengeluaran->map(function ($p) {
            return [
                'id' => $p->id,
                'kode_transaksi' => $p->kode_transaksi,
                'kategori' => $p->kategori,
                'judul_pengeluaran' => $p->judul_pengeluaran,
                'nominal' => (float) $p->nominal,
                'tanggal_pengeluaran' => $p->tanggal_pengeluaran ? $p->tanggal_pengeluaran->format('Y-m-d') : null,
                'tanggal_format' => $p->tanggal_pengeluaran ? $p->tanggal_pengeluaran->translatedFormat('d M Y') : null,
                'penerima_dana' => $p->penerima_dana,
                'metode_pembayaran' => $p->metode_pembayaran ?? 'Tunai',
                'bukti_nota_url' => $p->bukti_nota ? asset('storage/' . $p->bukti_nota) : null,
                'keterangan' => $p->keterangan,
                'pencatat_nama' => $p->pencatat?->name ?? 'Panitia',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    /**
     * Simpan Pengeluaran IMNI Baru
     * POST /api/panitia-imni/pengeluaran/simpan
     */
    public function simpanPengeluaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori' => 'required|string|max:100',
            'judul_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'tanggal_pengeluaran' => 'required|date',
            'penerima_dana' => 'nullable|string|max:150',
            'metode_pembayaran' => 'nullable|in:Tunai,Transfer,Lainnya',
            'keterangan' => 'nullable|string|max:500',
            'bukti_nota' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $tahunPelajaran = TahunPelajaran::find($tahunId);
        $countExisting = PengeluaranImni::where('tahun_pelajaran_id', $tahunId)->count() + 1;
        $kodeTransaksi = PengeluaranImni::generateKodeTransaksi($tahunPelajaran, $countExisting);

        $buktiNotaPath = null;
        if ($request->hasFile('bukti_nota')) {
            $buktiNotaPath = $request->file('bukti_nota')->store('pengeluaran_imni', 'public');
        }

        $pengeluaran = PengeluaranImni::create([
            'tahun_pelajaran_id' => $tahunId,
            'kode_transaksi' => $kodeTransaksi,
            'kategori' => $request->kategori,
            'judul_pengeluaran' => $request->judul_pengeluaran,
            'nominal' => $request->nominal,
            'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
            'penerima_dana' => $request->penerima_dana,
            'metode_pembayaran' => $request->metode_pembayaran ?? 'Tunai',
            'bukti_nota' => $buktiNotaPath,
            'keterangan' => $request->keterangan,
            'dicatat_oleh' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran IMNI berhasil dicatat.',
            'data' => $pengeluaran,
        ], 200);
    }

    /**
     * Update Pengeluaran IMNI
     * POST /api/panitia-imni/pengeluaran/update/{id}
     */
    public function updatePengeluaran(Request $request, $id)
    {
        $pengeluaran = PengeluaranImni::findOrFail($id);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $pengeluaran->tahun_pelajaran_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'kategori' => 'required|string|max:100',
            'judul_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'tanggal_pengeluaran' => 'required|date',
            'penerima_dana' => 'nullable|string|max:150',
            'metode_pembayaran' => 'nullable|in:Tunai,Transfer,Lainnya',
            'keterangan' => 'nullable|string|max:500',
            'bukti_nota' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('bukti_nota')) {
            if ($pengeluaran->bukti_nota) {
                Storage::disk('public')->delete($pengeluaran->bukti_nota);
            }
            $pengeluaran->bukti_nota = $request->file('bukti_nota')->store('pengeluaran_imni', 'public');
        }

        $pengeluaran->update([
            'kategori' => $request->kategori,
            'judul_pengeluaran' => $request->judul_pengeluaran,
            'nominal' => $request->nominal,
            'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
            'penerima_dana' => $request->penerima_dana,
            'metode_pembayaran' => $request->metode_pembayaran ?? 'Tunai',
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran IMNI berhasil diperbarui.',
            'data' => $pengeluaran,
        ], 200);
    }

    /**
     * Hapus Pengeluaran IMNI
     * DELETE /api/panitia-imni/pengeluaran/{id}
     */
    public function hapusPengeluaran(Request $request, $id)
    {
        $pengeluaran = PengeluaranImni::findOrFail($id);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $pengeluaran->tahun_pelajaran_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        if ($pengeluaran->bukti_nota) {
            Storage::disk('public')->delete($pengeluaran->bukti_nota);
        }

        $pengeluaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil dihapus.',
        ], 200);
    }

    // =========================================================================
    // 3. MODUL PRESENSI UJIAN IMNI (PANITIA & PENGAWAS)
    // =========================================================================

    /**
     * Ambil Data Presensi Ujian IMNI
     * GET /api/panitia-imni/presensi/data
     */
    public function getPresensiData(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $kategori = $request->input('kategori', 'imni'); // 'imni' (6 IBT & 3 TSA) atau 'tpq' (3 TPQ)

        // =========================================================================
        // MODE A: KELAS 3 TPQ (Ruangan Fisik & Ujian IMNI TPQ)
        // =========================================================================
        if ($kategori === 'tpq') {
            // 1. Ambil Ujian IMNI TPQ
            $ujianTpq = Ujian::where('tahun_pelajaran_id', $tahunId)
                ->where('tipe_ujian', 'IMNI')
                ->where(function ($q) {
                    $q->where('tingkat_id', 1)
                        ->orWhere('nama_ujian', 'like', '%TPQ%');
                })
                ->first() ?? Ujian::where('tahun_pelajaran_id', $tahunId)->where('tipe_ujian', 'IMNI')->first();

            // 2. Daftar Ruangan Fisik Khusus Kelas 3 TPQ
            $daftarRuanganTpq = Ruangan::with('level.tingkat')
                ->where('tahun_pelajaran_id', $tahunId)
                ->whereHas('level', function ($q) {
                    $q->where('nama_level', '3 TPQ')
                        ->orWhere('id', 3);
                })
                ->orderBy('nama_ruangan', 'asc')
                ->get();

            if ($daftarRuanganTpq->isEmpty()) {
                $daftarRuanganTpq = Ruangan::with('level.tingkat')
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->whereHas('level.tingkat', fn($q) => $q->where('kode_tingkat', 'TPQ')->orWhere('id', 1))
                    ->orderBy('nama_ruangan', 'asc')
                    ->get();
            }

            $selectedRuanganId = $request->filled('ruangan_id') ? (int) $request->ruangan_id : ($daftarRuanganTpq->first()?->id);
            $selectedRuangan = $daftarRuanganTpq->firstWhere('id', $selectedRuanganId) ?? $daftarRuanganTpq->first();
            $selectedRuanganId = $selectedRuangan?->id;

            // Pre-fetch pemetaan guru mapel per ruangan KBM (persis seperti PresensiUjianController)
            $jadwalKbmList = JadwalPelajaran::with(['ustadz', 'ustadzs', 'ruangan'])
                ->whereHas('ruangan', fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
                ->get();

            $guruMapelRuanganMap = [];
            $guruMapelLevelMap = [];
            foreach ($jadwalKbmList as $jk) {
                if ($jk->mata_pelajaran_id) {
                    $daftarPengampu = $jk->daftar_ustadz;
                    if ($daftarPengampu->isNotEmpty()) {
                        $primary = $daftarPengampu->first();
                        $rKey = $jk->mata_pelajaran_id . '_' . $jk->ruangan_id;
                        if (!isset($guruMapelRuanganMap[$rKey])) {
                            $guruMapelRuanganMap[$rKey] = $primary;
                        }
                        if ($jk->ruangan && $jk->ruangan->level_id) {
                            $lKey = $jk->mata_pelajaran_id . '_' . $jk->ruangan->level_id;
                            if (!isset($guruMapelLevelMap[$lKey])) {
                                $guruMapelLevelMap[$lKey] = $primary;
                            }
                        }
                    }
                }
            }

            // 3. Jadwal Ujian TPQ
            $jadwalList = collect();
            $selectedJadwalId = null;
            $jadwals = collect();

            if ($ujianTpq) {
                $jadwalQuery = JadwalUjian::with(['mataPelajaran', 'pengawas'])
                    ->where('ujian_id', $ujianTpq->id);

                if ($selectedRuangan && $selectedRuangan->level_id) {
                    $jadwalQuery->where('level_id', $selectedRuangan->level_id);
                }

                $jadwals = $jadwalQuery->orderBy('tanggal_ujian', 'asc')
                    ->orderBy('waktu_mulai', 'asc')
                    ->get();

                $jadwalList = $jadwals->map(function ($j) use ($selectedRuangan, $guruMapelRuanganMap, $guruMapelLevelMap) {
                    $resolvedPengawas = $j->resolvePengawasForRuangan($selectedRuangan, $guruMapelRuanganMap, $guruMapelLevelMap);

                    return [
                        'id' => $j->id,
                        'mata_pelajaran_id' => $j->mata_pelajaran_id,
                        'nama_mapel' => $j->nama_mapel,
                        'kode_mapel' => $j->mataPelajaran?->kode_mapel ?? '-',
                        'hari_tanggal' => $j->hari_tanggal,
                        'hari_tanggal_singkat' => $j->hari_tanggal_singkat,
                        'tanggal_ujian' => $j->hari_tanggal_singkat,
                        'tanggal_ujian_raw' => $j->getRawOriginal('tanggal_ujian') ?? date('Y-m-d'),
                        'waktu_mulai' => $j->jam_mulai_format,
                        'waktu_selesai' => $j->jam_selesai_format,
                        'pengawas_id' => $resolvedPengawas?->id ?? $j->ustadz_id,
                        'pengawas_nama' => $resolvedPengawas?->nama_lengkap ?? $j->pengawas?->nama_lengkap ?? 'Belum Ditentukan',
                    ];
                });

                $selectedJadwalId = $request->filled('jadwal_ujian_id') ? (int) $request->jadwal_ujian_id : ($jadwalList->first()['id'] ?? null);
            }

            // 4. Santri Aktif Kelas 3 TPQ di Ruangan Terpilih
            $muridList = collect();
            $pengawasData = null;
            $hadirCount = 0;
            $izinCount = 0;
            $sakitCount = 0;
            $alphaCount = 0;
            $dispensasiCount = 0;
            $belumCount = 0;

            if ($selectedRuanganId && $selectedJadwalId) {
                $jadwalTerpilih = $jadwals->firstWhere('id', $selectedJadwalId);

                // Pengawas Presensi
                $presensiPengawas = PresensiPengawasUjian::with(['ustadz', 'ustadzPengganti'])
                    ->where('jadwal_ujian_id', $selectedJadwalId)
                    ->where('ruangan_id', $selectedRuanganId)
                    ->first();

                $defaultPengawas = $jadwalTerpilih ? $jadwalTerpilih->resolvePengawasForRuangan($selectedRuangan, $guruMapelRuanganMap, $guruMapelLevelMap) : null;

                $pengawasData = [
                    'ustadz_id' => $presensiPengawas?->ustadz_id ?? $defaultPengawas?->id ?? $jadwalTerpilih?->ustadz_id,
                    'ustadz_nama' => $presensiPengawas?->ustadz?->nama_lengkap ?? $defaultPengawas?->nama_lengkap ?? $jadwalTerpilih?->pengawas?->nama_lengkap ?? 'Ustadz Pengawas',
                    'ustadz_pengganti_id' => $presensiPengawas?->ustadz_pengganti_id,
                    'ustadz_pengganti_nama' => $presensiPengawas?->ustadzPengganti?->nama_lengkap,
                    'status' => $presensiPengawas?->status ?? 'Hadir',
                    'catatan_berita_acara' => $presensiPengawas?->catatan_berita_acara,
                    'is_plotted' => ($presensiPengawas !== null || $defaultPengawas !== null || $jadwalTerpilih?->ustadz_id !== null),
                ];

                // Ambil Santri Aktif
                $muridRepo = app(MuridRuanganRepository::class);
                $santriList = $muridRepo->getMuridAktifByRuanganAndTahun($selectedRuanganId, $tahunId, ['waliMurid.kampung']);

                // Order by: Jenis Kelamin (L = 0, P = 1), Nama ASC
                $santriList = $santriList->sort(function ($a, $b) {
                    $jkA = ($a->jenis_kelamin === 'L') ? 0 : 1;
                    $jkB = ($b->jenis_kelamin === 'L') ? 0 : 1;
                    if ($jkA !== $jkB) return $jkA <=> $jkB;

                    return strcasecmp($a->nama_lengkap ?? '', $b->nama_lengkap ?? '');
                })->values();

                // Ambil info pembayaran/peserta IMNI untuk dispensasi / lock status
                $pesertaMap = PesertaImni::with('pembayaran')
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->whereIn('murid_id', $santriList->pluck('id'))
                    ->get()
                    ->keyBy('murid_id');

                $presensiExisting = PresensiUjian::where('jadwal_ujian_id', $selectedJadwalId)
                    ->where('ruangan_id', $selectedRuanganId)
                    ->get()
                    ->keyBy('murid_id');

                $muridList = $santriList->map(function ($m, $index) use ($selectedRuangan, $pesertaMap, $presensiExisting, &$hadirCount, &$izinCount, &$sakitCount, &$alphaCount, &$dispensasiCount, &$belumCount) {
                    $p = $pesertaMap->get($m->id);

                    $existing = $presensiExisting->get($m->id);
                    $status = $existing?->status ?? null;
                    $catatan = $existing?->catatan;

                    if ($status) {
                        match ($status) {
                            'Hadir' => $hadirCount++,
                            'Izin' => $izinCount++,
                            'Sakit' => $sakitCount++,
                            'Alpha' => $alphaCount++,
                            'Dispensasi' => $dispensasiCount++,
                            default => null,
                        };
                    } else {
                        $belumCount++;
                    }

                    return [
                        'peserta_id' => $p?->id ?? 0,
                        'murid_id' => $m->id,
                        'nomor_peserta' => $p?->nomor_peserta ?? ('TPQ-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT)),
                        'nomor_meja' => $index + 1,
                        'nama' => $m->nama_lengkap ?? '-',
                        'nism' => $m->nism ?? '-',
                        'nama_level' => '3 TPQ',
                        'ruangan_asal' => $selectedRuangan?->nama_ruangan ?? '3 TPQ',
                        'kode_tingkat' => 'TPQ',
                        'tingkat_id' => 1,
                        'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                        'is_locked' => false,
                        'lock_reason' => null,
                        'status' => $status,
                        'catatan' => $catatan,
                    ];
                });
            }

            $summary = [
                'total' => $muridList->count(),
                'hadir' => $hadirCount,
                'izin' => $izinCount,
                'sakit' => $sakitCount,
                'alpha' => $alphaCount,
                'dispensasi' => $dispensasiCount,
                'belum' => $belumCount,
            ];

            // Daftar Badal
            $daftarBadal = Ustadz::where('is_active', true)
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'kode_ustadz'])
                ->map(fn($u) => ['id' => $u->id, 'nama' => $u->nama_lengkap, 'kode' => $u->kode_ustadz]);

            $daftarRuanganFormatted = $daftarRuanganTpq->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_id' => $r->level_id,
                    'nama_level' => $r->level?->nama_level ?? '-',
                    'tingkat_id' => $r->level?->tingkat_id,
                    'kode_tingkat' => $r->level?->tingkat?->kode_tingkat ?? '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'kategori' => 'tpq',
                    'ujian' => $ujianTpq ? [
                        'id' => $ujianTpq->id,
                        'nama_ujian' => $ujianTpq->nama_ujian,
                        'tipe_ujian' => 'IMNI',
                    ] : null,
                    'daftar_ruangan' => $daftarRuanganFormatted,
                    'selected_ruangan_id' => $selectedRuanganId,
                    'selected_ruangan_nama' => $selectedRuangan?->nama_ruangan ?? '',
                    'nama_level' => $selectedRuangan?->level?->nama_level ?? '3 TPQ',
                    'jadwal_list' => $jadwalList,
                    'selected_jadwal_id' => $selectedJadwalId,
                    'pengawas' => $pengawasData,
                    'daftar_badal' => $daftarBadal,
                    'murid_list' => $muridList,
                    'summary' => $summary,
                ]
            ], 200);
        }

        // =========================================================================
        // MODE B: KELAS 6 IBT & 3 TSA (Ruangan IMNI R1..Rn)
        // =========================================================================
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        $ujianImni = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe_ujian', 'IMNI')
            ->first();

        // 1. Jadwal Ujian IMNI (6 IBT & 3 TSA)
        $jadwalUjianList = JadwalUjian::with(['mataPelajaran', 'level.tingkat'])
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // 2. Daftar Hari Ujian Unik
        $daftarHariUjian = $jadwalUjianList->groupBy(function ($j) {
            return Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
        })->values()->map(function ($items, $index) {
            $tgl = Carbon::parse($items->first()->tanggal_ujian)->format('Y-m-d');
            $cDate = Carbon::parse($tgl)->locale('id');

            $jadwalIbt = $items->filter(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'))->values()->map(fn($j) => [
                'id' => $j->id,
                'nama_mapel' => $j->nama_mapel,
                'jam' => $j->jam_mulai_format . ' - ' . $j->jam_selesai_format,
            ]);
            $jadwalTsa = $items->filter(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'))->values()->map(fn($j) => [
                'id' => $j->id,
                'nama_mapel' => $j->nama_mapel,
                'jam' => $j->jam_mulai_format . ' - ' . $j->jam_selesai_format,
            ]);

            return [
                'hari_ke' => $index + 1,
                'tanggal' => $tgl,
                'nama_hari' => $cDate->translatedFormat('l'),
                'tanggal_format' => $cDate->translatedFormat('d M Y'),
                'tanggal_lengkap' => $cDate->translatedFormat('l, d F Y'),
                'jadwal_ibt' => $jadwalIbt,
                'jadwal_tsa' => $jadwalTsa,
            ];
        });

        // Fallback jika belum ada jadwal ujian di input
        if ($daftarHariUjian->isEmpty()) {
            $datesFromPeserta = PesertaRuanganImni::where('tahun_pelajaran_id', $tahunId)
                ->whereNotNull('tanggal_ujian')
                ->pluck('tanggal_ujian')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->unique()
                ->values();

            if ($datesFromPeserta->isEmpty()) {
                $datesFromPeserta = collect([Carbon::today()->format('Y-m-d')]);
            }

            $daftarHariUjian = $datesFromPeserta->map(function ($tgl, $index) {
                $cDate = Carbon::parse($tgl)->locale('id');
                return [
                    'hari_ke' => $index + 1,
                    'tanggal' => $tgl,
                    'nama_hari' => $cDate->translatedFormat('l'),
                    'tanggal_format' => $cDate->translatedFormat('d M Y'),
                    'tanggal_lengkap' => $cDate->translatedFormat('l, d F Y'),
                    'jadwal_ibt' => [],
                    'jadwal_tsa' => [],
                ];
            });
        }

        // Tanggal Terpilih
        $selectedTanggal = $request->input('tanggal_ujian');
        if (!$selectedTanggal || !$daftarHariUjian->contains('tanggal', $selectedTanggal)) {
            $selectedTanggal = $daftarHariUjian->first()['tanggal'] ?? Carbon::today()->format('Y-m-d');
        }

        $selectedHariInfo = $daftarHariUjian->firstWhere('tanggal', $selectedTanggal) ?? $daftarHariUjian->first();

        // 3. Daftar Master Ruangan IMNI
        $daftarRuanganImni = RuanganImni::with(['ruanganFisik.level.tingkat', 'penanggungJawabRuangan.ustadz'])
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get();

        $selectedRuanganImniId = $request->filled('ruangan_imni_id') ? (int) $request->ruangan_imni_id : ($daftarRuanganImni->first()?->id);
        $selectedRuanganImni = $daftarRuanganImni->firstWhere('id', $selectedRuanganImniId) ?? $daftarRuanganImni->first();
        $selectedRuanganImniId = $selectedRuanganImni?->id;

        // 4. Ambil Peserta Plotted di Ruangan IMNI
        $pesertaPlotted = collect();
        $presensiExisting = collect();

        if ($selectedRuanganImni) {
            $pesertaPlotted = PesertaRuanganImni::with([
                'pesertaImni.murid.waliMurid.kampung',
                'pesertaImni.tingkat',
                'pesertaImni.level',
                'pesertaImni.ruanganAsal',
                'pesertaImni.pembayaran',
                'murid',
            ])
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('ruangan_imni_id', $selectedRuanganImni->id)
                ->where('tanggal_ujian', $selectedTanggal)
                ->get();

            // Presensi Existing
            $presensiExisting = PresensiUjian::where('ruangan_imni_id', $selectedRuanganImni->id)
                ->where('tanggal_ujian', $selectedTanggal)
                ->get()
                ->keyBy('murid_id');
        }

        // Pengawas Plotted dari Master / Plotting Pengawas IMNI Harian
        $pengawasPlotted = null;
        if ($selectedRuanganImni) {
            $pengawasPlotted = PengawasRuanganImni::with('ustadz')
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('ruangan_imni_id', $selectedRuanganImni->id)
                ->whereDate('tanggal_ujian', $selectedTanggal)
                ->first();
        }

        // Pengawas Presensi yang tersimpan di presensi_pengawas_ujians
        $presensiPengawas = null;
        if ($selectedRuanganImni?->ruangan_id) {
            $presensiPengawas = PresensiPengawasUjian::with(['ustadz', 'ustadzPengganti'])
                ->where('ruangan_id', $selectedRuanganImni->ruangan_id)
                ->first();
        }

        $pengawasNama = $presensiPengawas?->ustadz?->nama_lengkap
            ?? $pengawasPlotted?->nama_pengawas_efektif
            ?? ($pengawasPlotted?->ustadz?->nama_lengkap ?? null);

        $pengawasData = [
            'ustadz_id' => $presensiPengawas?->ustadz_id ?? $pengawasPlotted?->ustadz_id,
            'ustadz_nama' => $pengawasNama ?? 'Belum Ditentukan',
            'ustadz_pengganti_id' => $presensiPengawas?->ustadz_pengganti_id,
            'ustadz_pengganti_nama' => $presensiPengawas?->ustadzPengganti?->nama_lengkap,
            'status' => $presensiPengawas?->status ?? 'Hadir',
            'catatan_berita_acara' => $presensiPengawas?->catatan_berita_acara,
            'is_plotted' => ($pengawasPlotted !== null || $presensiPengawas !== null),
        ];

        $hadirCount = 0;
        $izinCount = 0;
        $sakitCount = 0;
        $alphaCount = 0;
        $dispensasiCount = 0;
        $belumCount = 0;

        // Order Murid Presensi:
        // 1. Ruangan Asal Rank (3 TSA -> 6-A -> 6-B -> lainnya)
        // 2. Ruangan Asal Nama
        // 3. Jenis Kelamin (Laki-laki 'L' = 0, Perempuan 'P' = 1)
        // 4. Nama Lengkap ASC
        $getRank = function ($ruang, $tingkatId) {
            if (str_contains($ruang, 'TSA') || $tingkatId == 3) return 1;
            if (str_contains($ruang, '6-A') || str_contains($ruang, '6A') || str_contains($ruang, '6 A')) return 2;
            if (str_contains($ruang, '6-B') || str_contains($ruang, '6B') || str_contains($ruang, '6 B')) return 3;
            if (str_contains($ruang, '6-C') || str_contains($ruang, '6C') || str_contains($ruang, '6 C')) return 4;
            return 5;
        };

        $pesertaPlotted = $pesertaPlotted->sort(function ($a, $b) use ($getRank) {
            $pesertaA = $a->pesertaImni;
            $pesertaB = $b->pesertaImni;
            $muridA = $a->murid ?? $pesertaA?->murid;
            $muridB = $b->murid ?? $pesertaB?->murid;

            // 1. Ruangan Asal / Level Asal
            $ruangA = strtoupper(trim($pesertaA?->ruanganAsal?->nama_ruangan ?? $pesertaA?->level?->nama_level ?? ''));
            $ruangB = strtoupper(trim($pesertaB?->ruanganAsal?->nama_ruangan ?? $pesertaB?->level?->nama_level ?? ''));

            $rankA = $getRank($ruangA, $pesertaA?->tingkat_id);
            $rankB = $getRank($ruangB, $pesertaB?->tingkat_id);
            if ($rankA !== $rankB) return $rankA <=> $rankB;

            $cmpRuang = strcasecmp($ruangA, $ruangB);
            if ($cmpRuang !== 0) return $cmpRuang;

            // 2. Jenis Kelamin (L = 0, P = 1)
            $jkA = ($muridA?->jenis_kelamin === 'L') ? 0 : 1;
            $jkB = ($muridB?->jenis_kelamin === 'L') ? 0 : 1;
            if ($jkA !== $jkB) return $jkA <=> $jkB;

            // 3. Nama Lengkap ASC
            return strcasecmp($muridA?->nama_lengkap ?? '', $muridB?->nama_lengkap ?? '');
        })->values();

        $muridList = $pesertaPlotted->map(function ($p) use ($presensiExisting, &$hadirCount, &$izinCount, &$sakitCount, &$alphaCount, &$dispensasiCount, &$belumCount) {
            $m = $p->murid ?? $p->pesertaImni?->murid;
            $peserta = $p->pesertaImni;

            $muridId = $m?->id;
            $existing = $presensiExisting->get($muridId);
            $status = $existing?->status ?? null;
            $catatan = $existing?->catatan;

            if ($status) {
                match ($status) {
                    'Hadir' => $hadirCount++,
                    'Izin' => $izinCount++,
                    'Sakit' => $sakitCount++,
                    'Alpha' => $alphaCount++,
                    'Dispensasi' => $dispensasiCount++,
                    default => null,
                };
            } else {
                $belumCount++;
            }

            return [
                'peserta_id' => $peserta?->id ?? 0,
                'murid_id' => $muridId ?? 0,
                'nomor_peserta' => $peserta?->nomor_peserta ?? '-',
                'nomor_meja' => $p->nomor_meja,
                'nama' => $m?->nama_lengkap ?? '-',
                'nism' => $m?->nism ?? '-',
                'nama_level' => $peserta?->level?->nama_level ?? ($peserta?->tingkat?->kode_tingkat ?? '-'),
                'ruangan_asal' => $peserta?->ruanganAsal?->nama_ruangan ?? $peserta?->level?->nama_level ?? '-',
                'kode_tingkat' => $peserta?->tingkat?->kode_tingkat ?? '-',
                'tingkat_id' => $peserta?->tingkat_id ?? 2,
                'jenis_kelamin' => $m?->jenis_kelamin ?? 'L',
                'is_locked' => false,
                'lock_reason' => null,
                'status' => $status,
                'catatan' => $catatan,
            ];
        });

        $summary = [
            'total' => $muridList->count(),
            'hadir' => $hadirCount,
            'izin' => $izinCount,
            'sakit' => $sakitCount,
            'alpha' => $alphaCount,
            'dispensasi' => $dispensasiCount,
            'belum' => $belumCount,
        ];

        // Daftar Badal
        $daftarBadal = Ustadz::where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'kode_ustadz'])
            ->map(fn($u) => ['id' => $u->id, 'nama' => $u->nama_lengkap, 'kode' => $u->kode_ustadz]);

        $daftarRuanganImniFormatted = $daftarRuanganImni->map(function ($r) {
            return [
                'id' => $r->id,
                'nama_ruangan_imni' => $r->nama_ruangan_imni,
                'ruangan_fisik_nama' => $r->ruanganFisik?->nama_ruangan ?? '-',
                'kapasitas' => $r->kapasitas,
                'urutan' => $r->urutan,
                'penanggung_jawab' => $r->penanggungJawabRuangan?->ustadz?->nama_lengkap ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'kategori' => 'imni',
                'ujian' => $ujianImni ? [
                    'id' => $ujianImni->id,
                    'nama_ujian' => $ujianImni->nama_ujian,
                    'tipe_ujian' => 'IMNI',
                ] : null,
                'daftar_hari_ujian' => $daftarHariUjian,
                'selected_tanggal' => $selectedTanggal,
                'selected_hari_info' => $selectedHariInfo,
                'daftar_ruangan_imni' => $daftarRuanganImniFormatted,
                'selected_ruangan_imni_id' => $selectedRuanganImniId,
                'selected_ruangan_imni' => $selectedRuanganImni ? [
                    'id' => $selectedRuanganImni->id,
                    'nama_ruangan_imni' => $selectedRuanganImni->nama_ruangan_imni,
                    'ruangan_fisik_nama' => $selectedRuanganImni->ruanganFisik?->nama_ruangan ?? '-',
                    'kapasitas' => $selectedRuanganImni->kapasitas,
                    'penanggung_jawab' => $selectedRuanganImni->penanggungJawabRuangan?->ustadz?->nama_lengkap ?? '-',
                ] : null,
                'pengawas' => $pengawasData,
                'daftar_badal' => $daftarBadal,
                'murid_list' => $muridList,
                'summary' => $summary,
            ]
        ], 200);
    }

    /**
     * Simpan Presensi Ujian IMNI (Mendukung Tab 3 TPQ & Tab 6 IBT & 3 TSA)
     * POST /api/panitia-imni/presensi/simpan
     */
    public function simpanPresensi(Request $request)
    {
        $kategori = $request->input('kategori', 'imni');
        $user = $request->user();

        // =====================================================================
        // 1. PRESENSI KELAS 3 TPQ (Ruangan Fisik & Jadwal Ujian)
        // =====================================================================
        if ($kategori === 'tpq' || ($request->filled('jadwal_ujian_id') && $request->filled('ruangan_id') && !$request->filled('ruangan_imni_id'))) {
            $validator = Validator::make($request->all(), [
                'ruangan_id' => 'required|exists:ruangans,id',
                'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
                'presensi' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $jadwal = JadwalUjian::with('ujian')->findOrFail($request->jadwal_ujian_id);
            $tahunId = $jadwal->ujian?->tahun_pelajaran_id ?? $this->getTahunId($request);

            if (!$this->checkPanitiaAccess($user, $tahunId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
                ], 403);
            }

            DB::beginTransaction();
            try {
                $ujianId = $jadwal->ujian_id;
                $jadwalUjianId = $request->jadwal_ujian_id;
                $ruanganId = $request->ruangan_id;
                $disimpan = 0;

                foreach ($request->presensi as $muridId => $item) {
                    $status = is_array($item) ? ($item['status'] ?? null) : $item;
                    $catatan = is_array($item) ? ($item['catatan'] ?? null) : null;

                    if ($status) {
                        PresensiUjian::updateOrCreate(
                            [
                                'jadwal_ujian_id' => $jadwalUjianId,
                                'ruangan_id'      => $ruanganId,
                                'murid_id'        => $muridId,
                            ],
                            [
                                'ujian_id'     => $ujianId,
                                'status'       => $status,
                                'catatan'      => $catatan,
                                'diinput_oleh' => $user->id,
                            ]
                        );
                        $disimpan++;
                    } else {
                        PresensiUjian::where([
                            'jadwal_ujian_id' => $jadwalUjianId,
                            'ruangan_id'      => $ruanganId,
                            'murid_id'        => $muridId,
                        ])->delete();
                    }
                }

                if ($request->has('pengawas')) {
                    $pData = $request->pengawas;
                    PresensiPengawasUjian::updateOrCreate(
                        [
                            'jadwal_ujian_id' => $jadwalUjianId,
                            'ruangan_id'      => $ruanganId,
                        ],
                        [
                            'ustadz_id'            => $pData['ustadz_id'] ?? null,
                            'ustadz_pengganti_id'  => $pData['ustadz_pengganti_id'] ?? null,
                            'status'               => $pData['status'] ?? 'Hadir',
                            'catatan_berita_acara' => $pData['catatan_berita_acara'] ?? null,
                        ]
                    );
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Presensi {$disimpan} santri Kelas 3 TPQ berhasil disimpan.",
                ], 200);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan presensi: ' . $e->getMessage(),
                ], 500);
            }
        }

        // =====================================================================
        // 2. PRESENSI KELAS 6 IBT & 3 TSA (Ruangan IMNI R1..Rn)
        // =====================================================================
        $validator = Validator::make($request->all(), [
            'ruangan_imni_id' => 'required|exists:ruangan_imnis,id',
            'tanggal_ujian' => 'required|date',
            'presensi' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $tahunId = $this->getTahunId($request);
        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $ruanganImni = RuanganImni::findOrFail($request->ruangan_imni_id);
        $tanggal = Carbon::parse($request->tanggal_ujian)->format('Y-m-d');

        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        $jadwalHariIni = JadwalUjian::with('level.tingkat')
            ->whereIn('ujian_id', $imniUjianIds)
            ->whereDate('tanggal_ujian', $tanggal)
            ->get();

        $jadwalIbt = $jadwalHariIni->firstWhere(fn($j) => $j->level?->tingkat_id == 2 || str_contains($j->level?->nama_level ?? '', '6'));
        $jadwalTsa = $jadwalHariIni->firstWhere(fn($j) => $j->level?->tingkat_id == 3 || str_contains($j->level?->nama_level ?? '', '3 TSA'));
        $defaultUjianId = $imniUjianIds->first() ?? 1;

        DB::beginTransaction();
        try {
            $disimpan = 0;

            foreach ($request->presensi as $muridId => $item) {
                $status = is_array($item) ? ($item['status'] ?? null) : $item;
                $catatan = is_array($item) ? ($item['catatan'] ?? null) : null;
                $tingkatId = is_array($item) ? ($item['tingkat_id'] ?? null) : null;

                if ($status) {
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
                            'ruangan_imni_id' => $ruanganImni->id,
                            'tanggal_ujian'   => $tanggal,
                            'status'          => $status,
                            'catatan'         => $catatan,
                            'diinput_oleh'    => $user->id,
                        ]
                    );
                    $disimpan++;
                } else {
                    PresensiUjian::where([
                        'ruangan_imni_id' => $ruanganImni->id,
                        'tanggal_ujian'   => $tanggal,
                        'murid_id'        => $muridId,
                    ])->delete();
                }
            }

            if ($request->has('pengawas')) {
                $pData = $request->pengawas;
                if ($ruanganImni->ruangan_id) {
                    PresensiPengawasUjian::updateOrCreate(
                        [
                            'ruangan_id' => $ruanganImni->ruangan_id,
                        ],
                        [
                            'ustadz_id'            => $pData['ustadz_id'] ?? null,
                            'ustadz_pengganti_id'  => $pData['ustadz_pengganti_id'] ?? null,
                            'status'               => $pData['status'] ?? 'Hadir',
                            'catatan_berita_acara' => $pData['catatan_berita_acara'] ?? null,
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Presensi {$disimpan} santri Ruangan {$ruanganImni->nama_ruangan_imni} berhasil disimpan.",
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan presensi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // 4. MODUL INPUT NILAI & LEGER IMNI
    // =========================================================================

    /**
     * Ambil Data Form Input Nilai IMNI
     * GET /api/panitia-imni/nilai/data
     */
    public function getNilaiData(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $daftarRuangan = $this->getRuanganPesertaImni($tahunId);

        if ($daftarRuangan->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'ujian' => null,
                    'daftar_ruangan' => [],
                    'selected_ruangan_id' => null,
                    'selected_ruangan_nama' => '',
                    'nama_level' => '',
                    'jadwal_list' => [],
                    'selected_jadwal_id' => null,
                    'murids' => [],
                ]
            ], 200);
        }

        $selectedRuanganId = $request->ruangan_id ? (int) $request->ruangan_id : ($daftarRuangan->first()['id'] ?? null);
        $ruangan = $selectedRuanganId ? Ruangan::with('level.tingkat')->find($selectedRuanganId) : null;

        // Cari Ujian IMNI yang sesuai dengan tingkat ruangan terpilih
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->where(fn($q) => $q->where('tipe_ujian', 'IMNI')->orWhere('nama_ujian', 'like', '%IMNI%'))
            ->pluck('id');

        $ujianImni = null;
        if ($ruangan && $ruangan->level && $ruangan->level->tingkat_id) {
            $ujianImni = Ujian::where('tahun_pelajaran_id', $tahunId)
                ->where('tingkat_id', $ruangan->level->tingkat_id)
                ->whereIn('id', $imniUjianIds)
                ->first();
        }

        if (!$ujianImni) {
            $ujianImni = Ujian::whereIn('id', $imniUjianIds)->first();
        }

        // Jadwal Mapel IMNI
        $jadwalQuery = JadwalUjian::with('mataPelajaran')->whereIn('ujian_id', $imniUjianIds);
        if ($ruangan && $ruangan->level_id) {
            $jadwalQuery->where('level_id', $ruangan->level_id);
        }

        $jadwals = $jadwalQuery->orderBy('tanggal_ujian', 'asc')->orderBy('waktu_mulai', 'asc')->orderBy('id', 'asc')->get();

        $jadwalList = $jadwals->map(function ($j) use ($imniUjianIds, $selectedRuanganId) {
            $dinilaiCount = NilaiUjian::whereIn('ujian_id', $imniUjianIds)
                ->where('ruangan_id', $selectedRuanganId)
                ->where('jadwal_ujian_id', $j->id)
                ->whereNotNull('nilai')
                ->count();

            $isPublished = NilaiUjian::whereIn('ujian_id', $imniUjianIds)
                ->where('ruangan_id', $selectedRuanganId)
                ->where('jadwal_ujian_id', $j->id)
                ->where('is_published', true)
                ->exists();

            return [
                'id' => $j->id,
                'mata_pelajaran_id' => $j->mata_pelajaran_id,
                'nama_mapel' => $j->nama_mapel,
                'tanggal_ujian' => $j->getRawOriginal('tanggal_ujian'),
                'hari_tanggal' => $j->hari_tanggal,
                'hari_tanggal_singkat' => $j->hari_tanggal_singkat,
                'waktu_mulai' => $j->jam_mulai_format,
                'waktu_selesai' => $j->jam_selesai_format,
                'jumlah_dinilai' => $dinilaiCount,
                'is_published' => $isPublished,
            ];
        });

        $selectedJadwalId = $request->jadwal_ujian_id ? (int) $request->jadwal_ujian_id : ($jadwalList->first()['id'] ?? null);

        $muridList = collect();
        if ($selectedRuanganId) {
            $pesertaList = PesertaImni::with(['murid', 'pembayaran'])
                ->where('tahun_pelajaran_id', $tahunId)
                ->where(function ($q) use ($selectedRuanganId) {
                    $q->where('ruangan_asal_id', $selectedRuanganId)
                      ->orWhere('ruangan_ujian_id', $selectedRuanganId);
                })
                ->get()
                ->sort(function ($a, $b) {
                    $muridA = $a->murid;
                    $muridB = $b->murid;

                    $jkA = ($muridA?->jenis_kelamin === 'L') ? 0 : 1;
                    $jkB = ($muridB?->jenis_kelamin === 'L') ? 0 : 1;
                    if ($jkA !== $jkB) return $jkA <=> $jkB;

                    return strcasecmp($muridA?->nama_lengkap ?? '', $muridB?->nama_lengkap ?? '');
                })
                ->values();

            $nilaiExisting = collect();
            if ($selectedJadwalId) {
                $nilaiExisting = NilaiUjian::whereIn('ujian_id', $imniUjianIds)
                    ->where('ruangan_id', $selectedRuanganId)
                    ->where('jadwal_ujian_id', $selectedJadwalId)
                    ->get()
                    ->keyBy('murid_id');
            }

            $muridList = $pesertaList->map(function ($p) use ($nilaiExisting) {
                $m = $p->murid;
                $isLunas = $p->pembayaran && $p->pembayaran->status_pembayaran === 'Lunas';
                $isDispensasi = $p->status_kelayakan === 'Dispensasi';
                $isLocked = !$isLunas && !$isDispensasi;

                $existing = $nilaiExisting->get($m?->id);

                return [
                    'peserta_id' => $p->id,
                    'murid_id' => $m?->id,
                    'nomor_peserta' => $p->nomor_peserta ?? '-',
                    'nomor_meja' => $p->nomor_meja,
                    'nama' => $m?->nama_lengkap ?? '-',
                    'nism' => $m?->nism ?? '-',
                    'jenis_kelamin' => $m?->jenis_kelamin ?? 'L',
                    'is_locked' => $isLocked,
                    'lock_reason' => $isLocked ? 'Belum lunas tagihan IMNI' : null,
                    'nilai' => $existing ? (float) $existing->nilai : null,
                    'is_published' => $existing ? (bool) $existing->is_published : false,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => $ujianImni ? [
                    'id' => $ujianImni->id,
                    'nama_ujian' => $ujianImni->nama_ujian,
                    'tipe_ujian' => 'IMNI',
                ] : null,
                'daftar_ruangan' => $daftarRuangan,
                'selected_ruangan_id' => $selectedRuanganId,
                'selected_ruangan_nama' => $ruangan?->nama_ruangan ?? '',
                'nama_level' => $ruangan?->level?->nama_level ?? '',
                'jadwal_list' => $jadwalList,
                'selected_jadwal_id' => $selectedJadwalId,
                'murids' => $muridList,
            ]
        ], 200);
    }

    /**
     * Simpan Nilai Ujian IMNI (Draf / Publikasikan)
     * POST /api/panitia-imni/nilai/simpan
     */
    public function simpanNilai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
            'action' => 'required|in:draft,publish',
            'nilai' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $ujian = Ujian::findOrFail($request->ujian_id);

        if (!$this->checkPanitiaAccess($user, $ujian->tahun_pelajaran_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $jadwalUjian = JadwalUjian::find($request->jadwal_ujian_id);
        $ujianId = $jadwalUjian ? $jadwalUjian->ujian_id : $request->ujian_id;
        $ruanganId = $request->ruangan_id;
        $jadwalUjianId = $request->jadwal_ujian_id;
        $isPublished = ($request->action === 'publish');

        DB::beginTransaction();
        try {
            $tersimpanCount = 0;
            foreach ($request->nilai as $muridId => $score) {
                if ($score !== null && $score !== '') {
                    NilaiUjian::updateOrCreate(
                        [
                            'ujian_id' => $ujianId,
                            'ruangan_id' => $ruanganId,
                            'jadwal_ujian_id' => $jadwalUjianId,
                            'murid_id' => $muridId,
                        ],
                        [
                            'nilai' => (float) $score,
                            'is_published' => $isPublished,
                            'diinput_oleh' => $user->id,
                        ]
                    );
                    $tersimpanCount++;
                } else {
                    NilaiUjian::where([
                        'ujian_id' => $ujianId,
                        'ruangan_id' => $ruanganId,
                        'jadwal_ujian_id' => $jadwalUjianId,
                        'murid_id' => $muridId,
                    ])->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isPublished ? "Nilai IMNI {$tersimpanCount} peserta resmi dipublikasikan!" : "Draf nilai IMNI {$tersimpanCount} peserta berhasil disimpan.",
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai IMNI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leger Nilai & Ranking Peserta IMNI
     * GET /api/panitia-imni/nilai/leger
     */
    public function getLegerNilai(Request $request)
    {
        $tahunId = $this->getTahunId($request);
        $user = $request->user();

        if (!$this->checkPanitiaAccess($user, $tahunId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses sebagai Panitia IMNI.',
            ], 403);
        }

        $daftarRuangan = $this->getRuanganPesertaImni($tahunId);

        $ruanganId = $request->filled('ruangan_id') ? (int) $request->ruangan_id : ($daftarRuangan->first()['id'] ?? null);
        $ruangan = $ruanganId ? Ruangan::with('level.tingkat')->find($ruanganId) : null;

        // Cari Ujian IMNI yang sesuai jika ruangan dipilih, atau semua Ujian IMNI
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $tahunId)
            ->where(fn($q) => $q->where('tipe_ujian', 'IMNI')->orWhere('nama_ujian', 'like', '%IMNI%'))
            ->pluck('id');

        $ujianImni = null;
        if ($ruangan && $ruangan->level && $ruangan->level->tingkat_id) {
            $ujianImni = Ujian::where('tahun_pelajaran_id', $tahunId)
                ->where('tingkat_id', $ruangan->level->tingkat_id)
                ->whereIn('id', $imniUjianIds)
                ->first();
        }

        if (!$ujianImni) {
            $ujianImni = Ujian::whereIn('id', $imniUjianIds)->first();
        }

        // Query Jadwal Ujian IMNI
        $jadwalQuery = JadwalUjian::with('mataPelajaran')->whereIn('ujian_id', $imniUjianIds);
        if ($ruangan && $ruangan->level_id) {
            $jadwalQuery->where('level_id', $ruangan->level_id);
        }

        $jadwals = $jadwalQuery->orderBy('tanggal_ujian', 'asc')->orderBy('id', 'asc')->get();

        $kolomMapel = [];
        $mapelKeys = [];
        foreach ($jadwals as $j) {
            if (!in_array($j->nama_mapel, $mapelKeys)) {
                $mapelKeys[] = $j->nama_mapel;
                $kolomMapel[] = [
                    'id' => $j->id,
                    'nama_mapel' => $j->nama_mapel,
                    'kode_mapel' => $j->mataPelajaran->kode_mapel ?? null,
                ];
            }
        }

        // Fallback jika belum ada jadwal ujian khusus untuk level ruangan ini
        if (empty($kolomMapel) && $ruangan && $ruangan->level_id) {
            $masterMapels = MataPelajaran::where('level_id', $ruangan->level_id)->orderBy('id')->get();
            foreach ($masterMapels as $mp) {
                if (!in_array($mp->nama_mapel, $mapelKeys)) {
                    $mapelKeys[] = $mp->nama_mapel;
                    $kolomMapel[] = [
                        'id' => $mp->id,
                        'nama_mapel' => $mp->nama_mapel,
                        'kode_mapel' => $mp->kode_mapel,
                    ];
                }
            }
        }

        // Query Peserta IMNI
        $imniRuanganIds = $daftarRuangan->pluck('id')->toArray();
        $pesertaQuery = PesertaImni::with(['murid', 'level'])->where('tahun_pelajaran_id', $tahunId);
        if ($ruanganId) {
            $pesertaQuery->where(function ($q) use ($ruanganId) {
                $q->where('ruangan_asal_id', $ruanganId)
                    ->orWhere('ruangan_ujian_id', $ruanganId);
            });
        } else {
            $pesertaQuery->where(function ($q) use ($imniRuanganIds) {
                $q->whereIn('ruangan_asal_id', $imniRuanganIds)
                    ->orWhereIn('ruangan_ujian_id', $imniRuanganIds);
            });
        }

        $pesertas = $pesertaQuery->get()->sort(function ($a, $b) {
            $muridA = $a->murid;
            $muridB = $b->murid;

            $jkA = ($muridA?->jenis_kelamin === 'L') ? 0 : 1;
            $jkB = ($muridB?->jenis_kelamin === 'L') ? 0 : 1;
            if ($jkA !== $jkB) return $jkA <=> $jkB;

            return strcasecmp($muridA?->nama_lengkap ?? '', $muridB?->nama_lengkap ?? '');
        })->values();

        // Query Nilai Ujian
        $allNilai = NilaiUjian::whereIn('ujian_id', $imniUjianIds)
            ->where(function ($q) use ($ruanganId, $imniRuanganIds, $pesertas) {
                if ($ruanganId) {
                    $q->where('ruangan_id', $ruanganId)
                        ->orWhereIn('murid_id', $pesertas->pluck('murid_id'));
                } else {
                    $q->whereIn('ruangan_id', $imniRuanganIds);
                }
            })
            ->get();

        $rows = $pesertas->map(function ($p) use ($allNilai, $jadwals, $kolomMapel) {
            $m = $p->murid;
            $nilaiMurid = $allNilai->where('murid_id', $m?->id);
            $total = 0;
            $mapelScores = [];
            $berisiCount = 0;

            foreach ($kolomMapel as $col) {
                $matchingJadwalIds = $jadwals->where('nama_mapel', $col['nama_mapel'])->pluck('id');
                $n = null;
                if ($matchingJadwalIds->isNotEmpty()) {
                    $n = $nilaiMurid->firstWhere(fn($val) => in_array($val->jadwal_ujian_id, $matchingJadwalIds->toArray()));
                }
                $angka = $n ? (float) $n->nilai : null;
                $mapelScores[$col['nama_mapel']] = $angka;
                if ($angka !== null) {
                    $total += $angka;
                    $berisiCount++;
                }
            }

            $rataRata = $berisiCount > 0 ? round($total / $berisiCount, 2) : 0;

            $predikat = 'E';
            if ($rataRata >= 90) $predikat = 'A+';
            elseif ($rataRata >= 85) $predikat = 'A';
            elseif ($rataRata >= 80) $predikat = 'B+';
            elseif ($rataRata >= 75) $predikat = 'B';
            elseif ($rataRata >= 70) $predikat = 'C+';
            elseif ($rataRata >= 65) $predikat = 'C';
            elseif ($rataRata >= 60) $predikat = 'D';

            return [
                'peserta_id' => $p->id,
                'murid_id' => $m?->id,
                'nomor_peserta' => $p->nomor_peserta ?? '-',
                'nism' => $m?->nism ?? '-',
                'nama' => $m?->nama_lengkap ?? '-',
                'jenis_kelamin' => $m?->jenis_kelamin ?? 'L',
                'nilai_mapel' => (object) $mapelScores,
                'total' => round($total, 1),
                'rata_rata' => $rataRata,
                'predikat' => $predikat,
                'jumlah_terisi' => $berisiCount,
                'total_mapel' => count($kolomMapel),
            ];
        });

        $sortedRows = $rows->sortByDesc('total')->values();
        $rankedLeger = $sortedRows->map(function ($row, $index) {
            $row['ranking'] = $index + 1;
            return $row;
        });

        $rataRataKelas = $rankedLeger->isNotEmpty() ? round($rankedLeger->avg('rata_rata'), 2) : 0;
        $nilaiTertinggi = $rankedLeger->isNotEmpty() ? $rankedLeger->max('total') : 0;
        $nilaiTerendah = $rankedLeger->isNotEmpty() ? $rankedLeger->min('total') : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => $ujianImni ? [
                    'id' => $ujianImni->id,
                    'nama_ujian' => $ujianImni->nama_ujian,
                    'tipe_ujian' => 'IMNI',
                ] : null,
                'daftar_ruangan' => $daftarRuangan,
                'selected_ruangan_id' => $ruanganId,
                'selected_ruangan_nama' => $ruangan?->nama_ruangan ?? ($daftarRuangan->first()['nama_ruangan'] ?? '-'),
                'kolom_mapel' => $kolomMapel,
                'statistik' => [
                    'total_murid' => count($rankedLeger),
                    'rata_rata_kelas' => $rataRataKelas,
                    'nilai_tertinggi' => $nilaiTertinggi,
                    'nilai_terendah' => $nilaiTerendah,
                ],
                'leger' => $rankedLeger,
            ]
        ], 200);
    }
}
