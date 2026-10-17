<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Ikhtibar\DivisiIkhtibar;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\Ikhtibar\PanitiaIkhtibar;
use App\Models\Ikhtibar\PembayaranTagihanIkhtibar;
use App\Models\Ikhtibar\PengeluaranIkhtibar;
use App\Models\Ikhtibar\PenyaluranDanaIkhtibar;
use App\Models\Kampung;
use App\Models\PembayaranTagihan;
use App\Models\PengaturanTagihan;
use App\Models\TagihanWaliMurid;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PanitiaIkhtibarApiController extends Controller
{
    /**
     * Helper: Dapatkan Tahun Pelajaran Aktif / Ditargetkan
     */
    protected function getTahunId(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        return $request->tahun_id ?? $tahunAktif?->id ?? TahunPelajaran::orderBy('id', 'desc')->value('id');
    }

    /**
     * Helper: Dapatkan Kegiatan Ikhtibar pada Tahun Terpilih
     */
    protected function getKegiatanIkhtibar($tahunId)
    {
        return KegiatanIkhtibar::firstOrCreate(
            ['tahun_pelajaran_id' => $tahunId],
            [
                'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                'ikhtibar_ke'     => '52 Th',
                'status_kegiatan' => 'Persiapan',
            ]
        );
    }

    /**
     * Helper: Validasi Akses Panitia Ikhtibar
     */
    protected function getPanitiaContext($user, $tahunId)
    {
        $user = $user ?: \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return [
                'isAdmin'   => false,
                'panitia'   => null,
                'isBph'     => false,
                'isDivisi'  => false,
                'divisiId'  => null,
            ];
        }

        $isAdmin = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['administrator', 'staff']);
        
        $panitia = null;
        if ($user->ustadz) {
            $panitia = PanitiaIkhtibar::with(['divisi', 'kegiatanIkhtibar'])
                ->where('ustadz_id', $user->ustadz->id)
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('is_active', true)
                ->first();
        }

        $isBph = $isAdmin || ($panitia && strtoupper($panitia->kelompok_jabatan) === 'BPH');
        $isDivisi = ($panitia && strtoupper($panitia->kelompok_jabatan) === 'DIVISI');

        return [
            'isAdmin'   => $isAdmin,
            'panitia'   => $panitia,
            'isBph'     => $isBph,
            'isDivisi'  => $isDivisi,
            'divisiId'  => $panitia?->divisi_ikhtibar_id,
        ];
    }

    /**
     * Helper: Dapatkan Rekapitulasi Penerimaan per Petugas BPH
     */
    protected function getRekapBph($tahunId)
    {
        $bphPanitias = PanitiaIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->where('kelompok_jabatan', 'BPH')
            ->with(['ustadz.user'])
            ->get();

        $bphUserMap = [];
        foreach ($bphPanitias as $bp) {
            if ($bp->ustadz?->user_id) {
                $bphUserMap[$bp->ustadz->user_id] = $bp->jabatan;
            }
        }

        $adminUser = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'administrator');
        })->first() ?? \App\Models\User::first();
        $adminUserId = $adminUser?->id;

        $pembayarans = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->with(['diterimaOleh.ustadz'])
            ->get();

        $groups = [];

        foreach ($pembayarans as $p) {
            $isTabungan = in_array($p->metode_pembayaran, ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri', 'Potong Tabungan Manual'])
                || !empty($p->nomor_tabungan);

            // Sesuai aturan: jika potongan tabungan manual, terhitung ke Administrator meskipun diinput oleh Bendahara atau BPH lain
            $effectiveUserId = $isTabungan ? $adminUserId : $p->diterima_oleh_id;
            $effectiveKey = $effectiveUserId ?? 0;

            if (!isset($groups[$effectiveKey])) {
                $user = ($effectiveUserId && $effectiveUserId == $adminUserId)
                    ? $adminUser
                    : $p->diterimaOleh;

                $isUserAdmin = ($effectiveKey === $adminUserId) || ($effectiveKey === 0)
                    || ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['administrator', 'staff']));

                $jabatan = $isUserAdmin
                    ? 'Administrator'
                    : ($bphUserMap[$effectiveUserId] ?? 'BPH Panitia');

                $nama = $isUserAdmin
                    ? ($user?->name ?? 'Administrator')
                    : ($user?->ustadz?->nama_lengkap ?? ($user?->name ?? 'Kasir BPH'));

                $groups[$effectiveKey] = [
                    'user_id'          => $effectiveUserId,
                    'nama'             => $nama,
                    'jabatan'          => $jabatan,
                    'total_transaksi'  => 0,
                    'total_nominal'    => 0,
                    'nominal_tunai'    => 0,
                    'nominal_tabungan' => 0,
                ];
            }

            $groups[$effectiveKey]['total_transaksi'] += 1;
            $groups[$effectiveKey]['total_nominal'] += (int) $p->nominal_bayar;
            if ($isTabungan) {
                $groups[$effectiveKey]['nominal_tabungan'] += (int) $p->nominal_bayar;
            } else {
                $groups[$effectiveKey]['nominal_tunai'] += (int) $p->nominal_bayar;
            }
        }

        return collect(array_values($groups))->sortBy(function ($item) {
            return match ($item['jabatan']) {
                'Ketua'           => 1,
                'Wakil Ketua'     => 2,
                'Sekretaris'      => 3,
                'Wakil Sekretaris'=> 4,
                'Bendahara'       => 5,
                'Wakil Bendahara' => 6,
                'Administrator'   => 7,
                'Admin Kasir'     => 8,
                default           => 9,
            };
        })->values();
    }

    /**
     * Helper: Dapatkan Rekapitulasi Pembayaran via Potong Tabungan
     */
    protected function getRekapTabungan($tahunId)
    {
        $tabunganQuery = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('metode_pembayaran', ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri']);

        return [
            'total_nominal'     => (int) (clone $tabunganQuery)->sum('nominal_bayar'),
            'total_transaksi'   => (int) (clone $tabunganQuery)->count(),
            'verified_nominal'  => (int) (clone $tabunganQuery)->where('is_verified', true)->sum('nominal_bayar'),
            'verified_count'    => (int) (clone $tabunganQuery)->where('is_verified', true)->count(),
            'pending_nominal'   => (int) (clone $tabunganQuery)->where('is_verified', false)->sum('nominal_bayar'),
            'pending_count'     => (int) (clone $tabunganQuery)->where('is_verified', false)->count(),
        ];
    }

    // =========================================================================
    // 1. STATUS & PROFIL KEPANITIAAN IKHTIBAR
    // =========================================================================

    /**
     * Status Akses Panitia Ikhtibar Ustadz
     * GET /api/panitia-ikhtibar/profile-status
     */
    public function getProfileStatus(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $kegiatan = $this->getKegiatanIkhtibar($tahunId);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isAdmin'] && !$ctx['panitia']) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai Panitia Ikhtibar aktif pada tahun pelajaran ini.',
                'data'    => [
                    'is_panitia' => false,
                ]
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'is_panitia'          => true,
                'is_bph'              => $ctx['isBph'],
                'is_divisi'           => $ctx['isDivisi'],
                'kelompok_jabatan'    => $ctx['isBph'] ? 'BPH' : 'Divisi',
                'jabatan'             => $ctx['panitia']?->jabatan ?? ($ctx['isAdmin'] ? 'Administrator' : '-'),
                'nama_divisi'         => $ctx['panitia']?->divisi?->nama_divisi,
                'divisi_id'           => $ctx['divisiId'],
                'ikhtibar_ke'         => $kegiatan->ikhtibar_ke ?? '52 Th',
                'nama_kegiatan'       => $kegiatan->nama_kegiatan ?? 'Ikhtibar & Haflah Akhirussanah',
                'logo_url'            => $kegiatan->logo_url,
                'kop_logo_url'        => $kegiatan->kop_logo_url,
                'tahun_pelajaran_id'  => $tahunId,
                // Menu visibility flag persis sesuai ketentuan
                'can_access_kasir'    => $ctx['isBph'],
                'can_access_laporan'  => $ctx['isBph'],
                'can_access_pagu_spj' => true,
            ]
        ], 200);
    }

    // =========================================================================
    // 2. KASIR PEMBAYARAN TAGIHAN IKHTIBAR (KHUSUS BPH)
    // =========================================================================

    /**
     * Ringkasan Kasir Tagihan Ikhtibar
     * GET /api/panitia-ikhtibar/kasir/ringkasan
     */
    public function getKasirRingkasan(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph']) {
            return response()->json([
                'success' => false,
                'message' => 'Menu Kasir Pembayaran hanya dapat diakses oleh Panitia BPH.'
            ], 403);
        }

        $kegiatan = $this->getKegiatanIkhtibar($tahunId);

        // Ambil Tagihan Ikhtibar (Sasaran Wali Murid)
        $masterTagihan = $kegiatan->pengaturan_tagihan_id 
            ? PengaturanTagihan::find($kegiatan->pengaturan_tagihan_id)
            : PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)->where('sasaran', 'wali_murid')->first();

        $queryTagihan = TagihanWaliMurid::where('tahun_pelajaran_id', $tahunId);
        if ($masterTagihan) {
            $queryTagihan->where('pengaturan_tagihan_id', $masterTagihan->id);
        }

        $totalKk = (clone $queryTagihan)->count();
        $targetNominal = (clone $queryTagihan)->sum('nominal_tagihan');
        $totalLunas = (clone $queryTagihan)->where('status_bayar', 'Lunas')->count();
        $totalBelumLunas = (clone $queryTagihan)->where('status_bayar', 'Belum Lunas')->count();
        $totalCicilan = (clone $queryTagihan)->where('status_bayar', 'Cicilan')->count();

        // Kas masuk riil terverifikasi (Sesuai aturan: jika potong tabungan manual belum terkonfirmasi admin, tidak dihitung)
        $totalMasuk = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->where('is_verified', true)
            ->sum('nominal_bayar');

        // Jika data pembayaran ikhtibar belum ada, fallback ke total_terbayar pada tagihan
        if ($totalMasuk == 0) {
            $totalMasuk = (clone $queryTagihan)->sum('total_terbayar');
        }

        $totalSisa = max(0, $targetNominal - $totalMasuk);
        $persentaseKelunasan = $targetNominal > 0 ? round(($totalMasuk / $targetNominal) * 100, 1) : 0;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get(['id', 'kode', 'nama_kampung']);
        $rekapBph = $this->getRekapBph($tahunId);
        $rekapTabungan = $this->getRekapTabungan($tahunId);

        return response()->json([
            'success' => true,
            'data'    => [
                'target_nominal'       => (int) $targetNominal,
                'total_masuk'          => (int) $totalMasuk,
                'total_sisa'           => (int) $totalSisa,
                'total_kk'             => (int) $totalKk,
                'total_lunas'          => (int) $totalLunas,
                'total_belum_lunas'    => (int) $totalBelumLunas,
                'total_cicilan'        => (int) $totalCicilan,
                'persentase_kelunasan' => (float) $persentaseKelunasan,
                'daftar_kampung'       => $daftarKampung,
                'ikhtibar_ke'          => $kegiatan->ikhtibar_ke ?? '52 Th',
                'rekap_bph'            => $rekapBph,
                'rekap_tabungan'       => $rekapTabungan,
            ]
        ], 200);
    }

    /**
     * Daftar Tagihan Wali Murid / KK untuk Kasir
     * GET /api/panitia-ikhtibar/kasir/list
     */
    public function getKasirList(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph']) {
            return response()->json([
                'success' => false,
                'message' => 'Menu Kasir Pembayaran hanya dapat diakses oleh Panitia BPH.'
            ], 403);
        }

        $kegiatan = $this->getKegiatanIkhtibar($tahunId);
        $masterTagihan = $kegiatan->pengaturan_tagihan_id 
            ? PengaturanTagihan::find($kegiatan->pengaturan_tagihan_id)
            : PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)->where('sasaran', 'wali_murid')->first();

        $query = TagihanWaliMurid::where('tahun_pelajaran_id', $tahunId)
            ->with([
                'waliMurid.kampung',
                'waliMurid.murids' => function ($q) use ($tahunId) {
                    $q->where('status', 'Aktif')
                        ->whereHas('ruangans', fn($rq) => $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId))
                        ->with([
                            'ruangans' => fn($rq) => $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId),
                            'tabungans'
                        ]);
                },
                'pembayaranIkhtibars' => fn($pq) => $pq->orderBy('tanggal_bayar', 'desc')->orderBy('id', 'desc'),
            ]);

        if ($masterTagihan) {
            $query->where('pengaturan_tagihan_id', $masterTagihan->id);
        }

        if ($request->filled('kampung_id')) {
            $query->whereHas('waliMurid', fn($wq) => $wq->where('kampung_id', $request->kampung_id));
        }

        if ($request->filled('status_bayar') && $request->status_bayar !== 'Semua') {
            $query->where('status_bayar', $request->status_bayar);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchHash = hash_sensitive($search);
            $query->whereHas('waliMurid', function ($wq) use ($search, $searchHash) {
                $wq->where('no_registrasi', 'like', "%{$search}%")
                    ->orWhere('no_kk_hash', $searchHash)
                    ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                    ->orWhereHas('murids', fn($mq) => $mq->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $perPage = $request->input('limit', 20);
        $tagihanList = $query->paginate($perPage);

        $mapped = $tagihanList->getCollection()->map(function ($t) use ($request) {
            $wali = $t->waliMurid;
            $murids = $wali ? $wali->murids : collect([]);

            $daftarAnak = $murids->map(function ($anak) {
                return [
                    'id'      => $anak->id,
                    'nama'    => $anak->nama_lengkap,
                    'ruangan' => $anak->ruangans->first()?->nama_ruangan ?? '-',
                ];
            });

            $daftarTabungan = $murids->flatMap(function ($anak) {
                return $anak->tabungans->map(function ($tab) use ($anak) {
                    return [
                        'id'             => $tab->id,
                        'nomor_rekening' => $tab->nomor_rekening,
                        'nama_nasabah'   => $anak->nama_lengkap,
                        'ruangan'        => $anak->ruangans->first()?->nama_ruangan ?? '-',
                        'saldo'          => (int) ($tab->saldo ?? 0),
                    ];
                });
            })->values();

            $pembayaranTerakhir = $t->pembayaranIkhtibars->first();
            $hasPendingTabungan = $t->pembayaranIkhtibars
                ->where('metode_pembayaran', 'Potong Tabungan')
                ->where('is_verified', false)
                ->isNotEmpty();

            return [
                'tagihan_id'            => $t->id,
                'wali_murid_id'         => $t->wali_murid_id,
                'no_registrasi'         => $wali?->no_registrasi ?? '-',
                'no_kk'                 => $wali?->no_kk ?? '-',
                'nama_kepala_keluarga'  => $wali?->nama_kepala_keluarga ?? '-',
                'kampung'               => $wali?->kampung?->nama_kampung ?? '-',
                'nominal_tagihan'       => (int) $t->nominal_tagihan,
                'nominal_bayar'         => (int) ($t->total_terbayar ?? 0),
                'sisa_tagihan'          => (int) $t->sisa_tagihan,
                'status_bayar'          => $t->status_bayar,
                'has_pending_tabungan'  => $hasPendingTabungan,
                'daftar_anak'           => $daftarAnak,
                'daftar_tabungan'       => $daftarTabungan,
                'kwitansi_url'          => $request->root() . URL::signedRoute('ikhtibar.tagihan.cetak-kwitansi-publik', ['id' => $t->id], absolute: false),
                'pembayaran_terakhir'   => $pembayaranTerakhir ? [
                    'id'                => $pembayaranTerakhir->id,
                    'nominal'           => (int) $pembayaranTerakhir->nominal_bayar,
                    'metode'            => $pembayaranTerakhir->metode_pembayaran,
                    'tanggal'           => $pembayaranTerakhir->tanggal_bayar?->format('Y-m-d'),
                    'is_verified'       => (bool) $pembayaranTerakhir->is_verified,
                    'nomor_tabungan'    => $pembayaranTerakhir->nomor_tabungan,
                    'nama_nasabah'      => $pembayaranTerakhir->nama_nasabah,
                    'ruangan_nasabah'   => $pembayaranTerakhir->ruangan_nasabah,
                    'kwitansi_url'      => $request->root() . URL::signedRoute('ikhtibar.tagihan.cetak-kwitansi-cicilan-publik', ['id' => $pembayaranTerakhir->id], absolute: false),
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'items'        => $mapped,
                'current_page' => $tagihanList->currentPage(),
                'last_page'    => $tagihanList->lastPage(),
                'total'        => $tagihanList->total(),
            ]
        ], 200);
    }

    /**
     * Proses Simpan Pembayaran Tagihan Ikhtibar
     * POST /api/panitia-ikhtibar/kasir/bayar
     */
    public function simpanPembayaran(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph']) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi pembayaran hanya dapat dilakukan oleh Panitia BPH.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tagihan_id'        => 'required|exists:tagihan_wali_murids,id',
            'nominal_bayar'     => 'required|numeric|min:1000',
            'metode_pembayaran' => 'required|in:Cash,Tunai,Potong Tabungan Manual,Potong Tabungan,Cicilan',
            'tanggal_bayar'     => 'nullable|date',
            'nama_penyetor'     => 'nullable|string|max:100',
            'catatan'           => 'nullable|string|max:255',
            // Field khusus potong tabungan manual
            'nomor_tabungan'    => 'required_if:metode_pembayaran,Potong Tabungan Manual,Potong Tabungan|nullable|string',
            'nama_nasabah'      => 'required_if:metode_pembayaran,Potong Tabungan Manual,Potong Tabungan|nullable|string',
            'ruangan_nasabah'   => 'required_if:metode_pembayaran,Potong Tabungan Manual,Potong Tabungan|nullable|string',
            'is_verified'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input transaksi tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $tagihan = TagihanWaliMurid::with(['waliMurid', 'pengaturanTagihan', 'pembayaranIkhtibars'])->findOrFail($request->tagihan_id);
        $kegiatan = $this->getKegiatanIkhtibar($tagihan->tahun_pelajaran_id);

        $nominalBayar = (int) $request->nominal_bayar;
        if ($nominalBayar > $tagihan->sisa_tagihan) {
            return response()->json([
                'success' => false,
                'message' => "Nominal bayar (Rp " . number_format($nominalBayar, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ")."
            ], 422);
        }

        $metodeInput = $request->metode_pembayaran;
        if ($metodeInput === 'Potong Tabungan Manual' || $metodeInput === 'Potong Tabungan') {
            $metodeSimpan = 'Potong Tabungan';
        } elseif ($metodeInput === 'Cicilan') {
            $metodeSimpan = (!empty($request->nomor_tabungan)) ? 'Potong Tabungan' : 'Tunai';
        } elseif ($metodeInput === 'Cash') {
            $metodeSimpan = 'Tunai';
        } else {
            $metodeSimpan = in_array($metodeInput, ['Tunai', 'Transfer', 'Lainnya']) ? $metodeInput : 'Tunai';
        }

        return DB::transaction(function () use ($request, $tagihan, $kegiatan, $nominalBayar, $metodeInput, $metodeSimpan, $user) {
            $sisaBaru = max(0, $tagihan->sisa_tagihan - $nominalBayar);
            $totalBayarBaru = ($tagihan->total_terbayar ?? 0) + $nominalBayar;
            $statusBaru = $sisaBaru <= 0 ? 'Lunas' : 'Cicilan';

            // Hitung termin ke berapa
            $terminKe = $tagihan->pembayaranIkhtibars->count() + 1;

            $tanggalBayar = $request->tanggal_bayar ?? Carbon::now()->toDateString();
            $noReg = $tagihan->waliMurid?->no_registrasi ?? '0000';
            $hari = Carbon::parse($tanggalBayar)->format('d');
            $bulan = Carbon::parse($tanggalBayar)->format('m');
            $tahun = Carbon::parse($tanggalBayar)->format('Y');
            $randomCode = mt_rand(10000, 99999);
            $noTransaksi = 'TRX/IKH/WLI/' . $noReg . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode . ($statusBaru === 'Cicilan' ? '-T' . $terminKe : '');

            // Aturan Verifikasi: Potong tabungan membutuhkan konfirmasi admin di backend
            if ($metodeSimpan === 'Potong Tabungan') {
                $isVerified = false;
            } else {
                $isVerified = true;
            }

            $rekeningInfo = ($metodeSimpan === 'Potong Tabungan') 
                ? "Tabungan Manual: {$request->nomor_tabungan} ({$request->nama_nasabah} - {$request->ruangan_nasabah})" 
                : null;

            $namaPenyetor = $request->nama_penyetor ?? ($tagihan->waliMurid?->nama_kepala_keluarga ?? 'Wali Murid');
            $catatanTrx = $request->catatan ?: (
                $metodeSimpan === 'Potong Tabungan'
                    ? "Pelunasan Potong Tabungan Manual ({$request->nomor_tabungan} a.n. {$request->nama_nasabah}) untuk {$kegiatan->ikhtibar_ke}"
                    : "Pembayaran Kasir {$metodeSimpan} untuk {$kegiatan->ikhtibar_ke} a.n. {$namaPenyetor}"
            );

            // 1. Simpan Standar Kwitansi Master
            $pembayaranMaster = PembayaranTagihan::create([
                'no_transaksi'      => $noTransaksi,
                'tanggal_bayar'     => $tanggalBayar,
                'tipe_pembayar'     => 'Wali Murid',
                'nama_pembayar'     => $namaPenyetor,
                'metode_pembayaran' => $metodeSimpan,
                'rekening_penerima' => $rekeningInfo,
                'total_nominal'     => $nominalBayar,
                'catatan'           => $catatanTrx,
            ]);

            // 2. Simpan Riwayat Pembayaran Ikhtibar
            $pembayaran = PembayaranTagihanIkhtibar::create([
                'tagihan_wali_murid_id' => $tagihan->id,
                'kegiatan_ikhtibar_id'   => $kegiatan->id,
                'tahun_pelajaran_id'    => $tagihan->tahun_pelajaran_id,
                'wali_murid_id'         => $tagihan->wali_murid_id,
                'no_transaksi'          => $noTransaksi,
                'metode_pembayaran'     => $metodeSimpan,
                'tipe_pembayaran'       => $statusBaru,
                'termin_ke'             => $terminKe,
                'nominal_tagihan'       => $tagihan->nominal_tagihan,
                'nominal_bayar'         => $nominalBayar,
                'sisa_tagihan'          => $sisaBaru,
                'tanggal_bayar'         => $tanggalBayar,
                'nomor_tabungan'        => $request->nomor_tabungan,
                'nama_nasabah'          => $request->nama_nasabah,
                'ruangan_nasabah'       => $request->ruangan_nasabah,
                'is_verified'           => $isVerified,
                'verified_at'           => $isVerified ? Carbon::now() : null,
                'verified_by_id'        => $isVerified ? $user->id : null,
                'pembayaran_tagihan_id' => $pembayaranMaster->id,
                'diterima_oleh_id'      => $user->id,
                'nama_penyetor'         => $namaPenyetor,
                'catatan'               => $catatanTrx,
            ]);

            // 3. Update status & saldo tagihan
            $tagihan->update([
                'total_terbayar'        => $totalBayarBaru,
                'status_bayar'          => $statusBaru,
                'pembayaran_tagihan_id' => $pembayaranMaster->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tagihan Ikhtibar berhasil dicatat' . ($isVerified ? '!' : ' dan menunggu verifikasi admin di backend.'),
                'data'    => [
                    'id'                   => $pembayaran->id,
                    'tagihan_id'           => $tagihan->id,
                    'no_transaksi'         => $noTransaksi,
                    'nama_kepala_keluarga' => $tagihan->waliMurid?->nama_kepala_keluarga ?? '-',
                    'kampung'              => $tagihan->waliMurid?->kampung?->nama_kampung ?? '-',
                    'no_registrasi'        => $tagihan->waliMurid?->no_registrasi ?? '-',
                    'termin_ke'            => $terminKe,
                    'nominal_bayar'        => $nominalBayar,
                    'total_terbayar'       => $totalBayarBaru,
                    'nominal_tagihan'      => (int) $tagihan->nominal_tagihan,
                    'sisa_tagihan'         => $sisaBaru,
                    'status_bayar'         => $statusBaru,
                    'metode_pembayaran'    => $metodeSimpan,
                    'tanggal_bayar'        => $tanggalBayar,
                    'is_verified'          => $isVerified,
                    'kwitansi_url'         => $request->root() . URL::signedRoute('ikhtibar.tagihan.cetak-kwitansi-publik', ['id' => $tagihan->id], absolute: false),
                    'kwitansi_cicilan_url' => $request->root() . URL::signedRoute('ikhtibar.tagihan.cetak-kwitansi-cicilan-publik', ['id' => $pembayaran->id], absolute: false),
                ]
            ], 200);
        });
    }

    /**
     * Toggle Konfirmasi / Verifikasi Potong Tabungan Manual via API (Khusus Admin Backend)
     * POST /api/panitia-ikhtibar/kasir/verifikasi-tabungan/{id}
     */
    public function toggleVerifikasiTabungan(Request $request, $id)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isAdmin']) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi potongan tabungan hanya dapat dilakukan oleh Administrator di backend.'
            ], 403);
        }

        $pembayaran = PembayaranTagihanIkhtibar::findOrFail($id);
        $pembayaran->is_verified = !$pembayaran->is_verified;
        $pembayaran->verified_at = $pembayaran->is_verified ? Carbon::now() : null;
        $pembayaran->verified_by_id = $pembayaran->is_verified ? $user->id : null;
        $pembayaran->save();

        return response()->json([
            'success'     => true,
            'is_verified' => $pembayaran->is_verified,
            'message'     => $pembayaran->is_verified
                ? 'Status potongan tabungan BERHASIL diverifikasi/dikonfirmasi.'
                : 'Verifikasi potongan tabungan dibatalkan (Menunggu verifikasi).',
            'data'        => [
                'id'          => $pembayaran->id,
                'is_verified' => $pembayaran->is_verified,
                'verified_at' => $pembayaran->verified_at?->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    // =========================================================================
    // 3. LAPORAN PEMBAYARAN IKHTIBAR (KHUSUS BPH)
    // =========================================================================

    /**
     * Laporan & Rekapitulasi Tagihan Ikhtibar
     * GET /api/panitia-ikhtibar/laporan/ringkasan
     */
    public function getLaporanRingkasan(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph']) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan Pembayaran hanya dapat diakses oleh Panitia BPH.'
            ], 403);
        }

        $kegiatan = $this->getKegiatanIkhtibar($tahunId);
        $masterTagihan = $kegiatan->pengaturan_tagihan_id 
            ? PengaturanTagihan::find($kegiatan->pengaturan_tagihan_id)
            : PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)->where('sasaran', 'wali_murid')->first();

        $queryTagihan = TagihanWaliMurid::where('tahun_pelajaran_id', $tahunId);
        if ($masterTagihan) {
            $queryTagihan->where('pengaturan_tagihan_id', $masterTagihan->id);
        }

        $targetTotal = (clone $queryTagihan)->sum('nominal_tagihan');
        $masukTotal = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->where('is_verified', true)
            ->sum('nominal_bayar');

        if ($masukTotal == 0) {
            $masukTotal = (clone $queryTagihan)->sum('total_terbayar');
        }

        $sisaTotal = max(0, $targetTotal - $masukTotal);

        // Rekapitulasi per Kampung / Dusun
        $kampungs = Kampung::withCount(['waliMurids as total_kk' => function ($q) use ($tahunId, $masterTagihan) {
            $q->whereHas('tagihanWaliMurids', function ($tq) use ($tahunId, $masterTagihan) {
                $tq->where('tahun_pelajaran_id', $tahunId);
                if ($masterTagihan) $tq->where('pengaturan_tagihan_id', $masterTagihan->id);
            });
        }])->get();

        $rekapKampung = $kampungs->map(function ($k) use ($tahunId, $masterTagihan) {
            $tQuery = TagihanWaliMurid::where('tahun_pelajaran_id', $tahunId)
                ->whereHas('waliMurid', fn($wq) => $wq->where('kampung_id', $k->id));
            if ($masterTagihan) $tQuery->where('pengaturan_tagihan_id', $masterTagihan->id);

            $target = (clone $tQuery)->sum('nominal_tagihan');
            $lunasCount = (clone $tQuery)->where('status_bayar', 'Lunas')->count();
            $belumLunasCount = (clone $tQuery)->where('status_bayar', '!=', 'Lunas')->count();

            $masuk = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
                ->where('is_verified', true)
                ->whereHas('waliMurid', fn($wq) => $wq->where('kampung_id', $k->id))
                ->sum('nominal_bayar');

            if ($masuk == 0) {
                $masuk = (clone $tQuery)->sum('total_terbayar');
            }

            $sisa = max(0, $target - $masuk);
            $persen = $target > 0 ? round(($masuk / $target) * 100, 1) : 0;

            return [
                'kampung_id'   => $k->id,
                'kode'         => $k->kode,
                'nama_kampung' => $k->nama_kampung,
                'total_kk'     => $k->total_kk,
                'lunas_count'  => $lunasCount,
                'belum_lunas'  => $belumLunasCount,
                'target'       => (int) $target,
                'masuk'        => (int) $masuk,
                'sisa'         => (int) $sisa,
                'persentase'   => (float) $persen,
            ];
        });

        // Rekap per Metode Pembayaran
        $rekapMetode = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->where('is_verified', true)
            ->select('metode_pembayaran', DB::raw('SUM(nominal_bayar) as total_nominal'), DB::raw('COUNT(*) as total_transaksi'))
            ->groupBy('metode_pembayaran')
            ->get();

        $rekapBph = $this->getRekapBph($tahunId);
        $rekapTabungan = $this->getRekapTabungan($tahunId);

        return response()->json([
            'success' => true,
            'data'    => [
                'target_total'   => (int) $targetTotal,
                'masuk_total'    => (int) $masukTotal,
                'sisa_total'     => (int) $sisaTotal,
                'persentase'     => $targetTotal > 0 ? round(($masukTotal / $targetTotal) * 100, 1) : 0,
                'rekap_kampung'  => $rekapKampung,
                'rekap_metode'   => $rekapMetode,
                'rekap_bph'      => $rekapBph,
                'rekap_tabungan' => $rekapTabungan,
            ]
        ], 200);
    }

    /**
     * Riwayat Transaksi Pembayaran Tagihan Ikhtibar
     * GET /api/panitia-ikhtibar/laporan/riwayat
     */
    public function getLaporanRiwayat(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph']) {
            return response()->json([
                'success' => false,
                'message' => 'Riwayat Pembayaran hanya dapat diakses oleh Panitia BPH.'
            ], 403);
        }

        $query = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
            ->with(['waliMurid.kampung', 'diterimaOleh'])
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('metode_pembayaran') && $request->metode_pembayaran !== 'Semua') {
            $m = $request->metode_pembayaran;
            if (in_array($m, ['Potong Tabungan Manual', 'Potong Tabungan', 'Tabungan'])) {
                $query->where('metode_pembayaran', 'Potong Tabungan');
            } elseif ($m === 'Cicilan') {
                $query->where('tipe_pembayaran', 'Cicilan');
            } else {
                $query->where('metode_pembayaran', $m);
            }
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_bayar', $request->tanggal);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('kampung_id')) {
            $query->whereHas('waliMurid', fn($q) => $q->where('kampung_id', $request->kampung_id));
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                    ->orWhere('nama_penyetor', 'like', "%{$search}%")
                    ->orWhereHas('waliMurid', fn($wq) => $wq->where('nama_kepala_keluarga', 'like', "%{$search}%"));
            });
        }

        $riwayat = $query->paginate($request->input('limit', 20));

        $mapped = $riwayat->getCollection()->map(function ($r) use ($request) {
            return [
                'id'                   => $r->id,
                'tagihan_wali_murid_id'=> $r->tagihan_wali_murid_id,
                'no_transaksi'         => $r->no_transaksi,
                'nama_penyetor'        => $r->nama_penyetor ?? $r->waliMurid?->nama_kepala_keluarga,
                'kampung'              => $r->waliMurid?->kampung?->nama_kampung ?? '-',
                'nominal'              => (int) $r->nominal_bayar,
                'metode'               => $r->metode_pembayaran,
                'tipe_pembayaran'      => $r->tipe_pembayaran,
                'termin_ke'            => $r->termin_ke,
                'tanggal'              => $r->tanggal_bayar?->format('Y-m-d'),
                'is_verified'          => (bool) $r->is_verified,
                'nomor_tabungan'       => $r->nomor_tabungan,
                'nama_nasabah'         => $r->nama_nasabah,
                'ruangan_nasabah'      => $r->ruangan_nasabah,
                'diterima_oleh'        => $r->diterimaOleh?->name ?? 'Admin',
                'kwitansi_url'         => $request->root() . URL::signedRoute('ikhtibar.tagihan.cetak-kwitansi-cicilan-publik', ['id' => $r->id], absolute: false),
                'kwitansi_tagihan_url' => $request->root() . URL::signedRoute('ikhtibar.tagihan.cetak-kwitansi-publik', ['id' => $r->tagihan_wali_murid_id], absolute: false),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'items'        => $mapped,
                'current_page' => $riwayat->currentPage(),
                'last_page'    => $riwayat->lastPage(),
                'total'        => $riwayat->total(),
            ]
        ], 200);
    }

    // =========================================================================
    // 4. PENGELUARAN PAGU & REALISASI SPJ (BPH & DIVISI)
    // =========================================================================

    /**
     * Ringkasan Pagu & SPJ
     * GET /api/panitia-ikhtibar/pagu-spj/ringkasan
     */
    public function getPaguSpjRingkasan(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $kegiatan = $this->getKegiatanIkhtibar($tahunId);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph'] && !$ctx['isDivisi']) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke anggaran kegiatan Ikhtibar.'
            ], 403);
        }

        if ($ctx['isBph']) {
            // MODE BPH: Tampilkan seluruh plafon 16 pos dan akumulasi
            $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->orderBy('urutan')
                ->get();

            $totalPagu = $divisis->sum('nominal_pagu');
            $totalTersalurkan = PenyaluranDanaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('jenis_transaksi', 'Penyaluran Pagu')
                ->sum('nominal');

            $totalPenarikan = PenyaluranDanaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('jenis_transaksi', 'Penarikan Sisa Kas')
                ->sum('nominal');

            $totalSpj = PengeluaranIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->sum('nominal');

            // Kas Masuk Bersih dari Tagihan KK
            $totalKasMasuk = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunId)
                ->where('is_verified', true)
                ->sum('nominal_bayar');

            $sisaSaldoKasUtama = max(0, $totalKasMasuk - $totalTersalurkan + $totalPenarikan);

            $daftarDivisi = $divisis->map(function ($d) {
                return [
                    'id'               => $d->id,
                    'kode'             => $d->kode_divisi,
                    'nama_divisi'      => $d->nama_divisi,
                    'is_bph'           => $d->is_bph,
                    'urutan'           => $d->urutan,
                    'nominal_pagu'     => (int) $d->nominal_pagu,
                    'dana_tersalurkan' => (int) $d->total_tersalurkan,
                    'realisasi_spj'    => (int) $d->total_realisasi_spj,
                    'sisa_pagu'        => (int) $d->sisa_pagu,
                    'sisa_kas_divisi'  => (int) $d->sisa_kas_di_divisi,
                    'penanggung_jawab' => $d->koordinator_tampil?->nama_tampil ?? 'Belum Ditentukan',
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => [
                    'mode'                 => 'BPH',
                    'total_pagu'           => (int) $totalPagu,
                    'total_tersalurkan'    => (int) $totalTersalurkan,
                    'total_spj'            => (int) $totalSpj,
                    'sisa_kas_utama'       => (int) $sisaSaldoKasUtama,
                    'total_penarikan'      => (int) $totalPenarikan,
                    'daftar_divisi'        => $daftarDivisi,
                ]
            ], 200);
        } else {
            // MODE DIVISI: Tampilkan khusus pagu divisinya sendiri
            $divisi = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('id', $ctx['divisiId'])
                ->first();

            if (!$divisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data divisi Anda belum dialokasikan pada kegiatan Ikhtibar ini.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'mode'            => 'DIVISI',
                    'divisi'          => [
                        'id'               => $divisi->id,
                        'kode'             => $divisi->kode_divisi,
                        'nama_divisi'      => $divisi->nama_divisi,
                        'nominal_pagu'     => (int) $divisi->nominal_pagu,
                        'dana_tersalurkan' => (int) $divisi->total_tersalurkan,
                        'realisasi_spj'    => (int) $divisi->total_realisasi_spj,
                        'sisa_pagu'        => (int) $divisi->sisa_pagu,
                        'sisa_kas_divisi'  => (int) $divisi->sisa_kas_di_divisi,
                        'penanggung_jawab' => $divisi->koordinator_tampil?->nama_tampil ?? 'Koordinator Divisi',
                    ]
                ]
            ], 200);
        }
    }

    /**
     * Daftar Realisasi Belanja & SPJ
     * GET /api/panitia-ikhtibar/spj/list
     */
    public function getSpjList(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $kegiatan = $this->getKegiatanIkhtibar($tahunId);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph'] && !$ctx['isDivisi']) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat SPJ.'
            ], 403);
        }

        $query = PengeluaranIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['divisi', 'diinputOleh.ustadz', 'diverifikasiOleh.ustadz'])
            ->orderBy('tanggal_nota', 'desc')
            ->orderBy('id', 'desc');

        // Jika user adalah Divisi, batasi HANYA pada divisinya
        if ($ctx['isDivisi']) {
            $query->where('divisi_ikhtibar_id', $ctx['divisiId']);
        } else {
            // Jika BPH, boleh memfilter divisi tertentu jika diinginkan
            if ($request->filled('divisi_id')) {
                $query->where('divisi_ikhtibar_id', $request->divisi_id);
            }
        }

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul_pengeluaran', 'like', "%{$search}%")
                    ->orWhere('kode_spj', 'like', "%{$search}%")
                    ->orWhere('nama_toko_penerima', 'like', "%{$search}%");
            });
        }

        $spjList = $query->paginate($request->input('limit', 20));

        $mapped = $spjList->getCollection()->map(function ($s) {
            return [
                'id'                 => $s->id,
                'kode_spj'           => $s->kode_spj,
                'divisi_id'          => $s->divisi_ikhtibar_id,
                'nama_divisi'        => $s->divisi?->nama_divisi ?? 'Sekretariat BPH',
                'judul_pengeluaran'  => $s->judul_pengeluaran,
                'rincian_item'       => $s->rincian_item,
                'nominal'            => (int) $s->nominal,
                'tanggal_nota'       => $s->tanggal_nota?->format('Y-m-d'),
                'nama_toko_penerima' => $s->nama_toko_penerima ?? '-',
                'bukti_nota_url'     => $s->bukti_nota ? asset('storage/' . $s->bukti_nota) : null,
                'status_verifikasi'  => $s->status_verifikasi,
                'diinput_oleh'       => $s->diinputOleh?->nama_tampil ?? '-',
                'diverifikasi_oleh'  => $s->diverifikasiOleh?->nama_tampil ?? '-',
                'catatan_verifikasi' => $s->catatan_verifikasi,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'items'        => $mapped,
                'current_page' => $spjList->currentPage(),
                'last_page'    => $spjList->lastPage(),
                'total'        => $spjList->total(),
            ]
        ], 200);
    }

    /**
     * Simpan Realisasi SPJ Baru
     * POST /api/panitia-ikhtibar/spj/simpan
     */
    public function simpanSpj(Request $request)
    {
        $user = $request->user();
        $tahunId = $this->getTahunId($request);
        $kegiatan = $this->getKegiatanIkhtibar($tahunId);
        $ctx = $this->getPanitiaContext($user, $tahunId);

        if (!$ctx['isBph'] && !$ctx['isDivisi']) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menginput SPJ.'
            ], 403);
        }

        $rules = [
            'judul_pengeluaran'  => 'required|string|max:150',
            'nominal'            => 'required|numeric|min:500',
            'tanggal_nota'       => 'required|date',
            'nama_toko_penerima' => 'nullable|string|max:100',
            'metode_pembayaran'  => 'required|in:Tunai,Transfer',
            'rincian_item'       => 'nullable|string',
            'bukti_nota'         => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
        ];

        // Jika BPH, boleh pilih divisi atau pos BPH
        if ($ctx['isBph']) {
            $rules['divisi_id'] = 'required|exists:divisi_ikhtibars,id';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input SPJ tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $divisiId = $ctx['isDivisi'] ? $ctx['divisiId'] : $request->divisi_id;
        $divisi = DivisiIkhtibar::findOrFail($divisiId);

        // Upload bukti nota jika ada
        $buktiPath = null;
        if ($request->hasFile('bukti_nota')) {
            $file = $request->file('bukti_nota');
            $filename = 'SPJ_' . date('Ymd_His') . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $buktiPath = $file->storeAs('ikhtibar/spj', $filename, 'public');
        }

        $kodeSpj = PengeluaranIkhtibar::generateKodeSpj($divisi->kode_divisi);

        // Belanja divisi tidak perlu verifikasi panitia, langsung valid/Disetujui
        $statusVerifikasi = 'Disetujui';

        $spj = PengeluaranIkhtibar::create([
            'kegiatan_ikhtibar_id' => $kegiatan->id,
            'divisi_ikhtibar_id'   => $divisi->id,
            'kode_spj'             => $kodeSpj,
            'judul_pengeluaran'    => $request->judul_pengeluaran,
            'rincian_item'         => $request->rincian_item,
            'nominal'              => $request->nominal,
            'tanggal_nota'         => $request->tanggal_nota,
            'nama_toko_penerima'   => $request->nama_toko_penerima,
            'metode_pembayaran'    => $request->metode_pembayaran,
            'bukti_nota'           => $buktiPath,
            'diinput_oleh_id'      => $ctx['panitia']?->id,
            'status_verifikasi'    => $statusVerifikasi,
            'diverifikasi_oleh_id' => null,
            'catatan_verifikasi'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPJ pengeluaran berhasil disimpan!',
            'data'    => [
                'id'                => $spj->id,
                'kode_spj'          => $spj->kode_spj,
                'status_verifikasi' => $spj->status_verifikasi,
            ]
        ], 200);
    }
}
