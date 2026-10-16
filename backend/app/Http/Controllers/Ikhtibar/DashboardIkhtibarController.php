<?php

namespace App\Http\Controllers\Ikhtibar;

use App\Http\Controllers\Controller;
use App\Models\Ikhtibar\DivisiIkhtibar;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\Ikhtibar\PanitiaIkhtibar;
use App\Models\Ikhtibar\PembayaranTagihanIkhtibar;
use App\Models\Ikhtibar\PengeluaranIkhtibar;
use App\Models\Ikhtibar\PenyaluranDanaIkhtibar;
use App\Models\Kampung;
use App\Models\PengaturanTagihan;
use App\Models\TagihanWaliMurid;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardIkhtibarController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->input('tahun_pelajaran_id', $tahunAktif ? $tahunAktif->id : null);

        // Cari atau siapkan kegiatan ikhtibar di tahun terpilih
        $kegiatan = KegiatanIkhtibar::firstOrCreate(
            ['tahun_pelajaran_id' => $selectedTahunId],
            [
                'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                'ikhtibar_ke'     => '52 Th',
                'status_kegiatan' => 'Persiapan',
            ]
        );

        // 1. Data Sumber Tagihan Per KK
        $pengaturanTagihan = $kegiatan->pengaturanTagihan;
        if (!$pengaturanTagihan) {
            $pengaturanTagihan = PengaturanTagihan::where('tahun_pelajaran_id', $selectedTahunId)
                ->where('sasaran', 'wali_murid')
                ->where(function ($q) {
                    $q->where('kode_tagihan', 'like', '%IKH%')
                        ->orWhere('nama_tagihan', 'like', '%Ikhtibar%');
                })->first();

            if ($pengaturanTagihan && !$kegiatan->pengaturan_tagihan_id) {
                $kegiatan->pengaturan_tagihan_id = $pengaturanTagihan->id;
                $kegiatan->save();
            }
        }

        // Jumlah KK Aktif di Tahun ini
        $jumlahKkAktif = WaliMurid::aktifDiTahun($selectedTahunId)->count();
        $tarifTagihanKk = $pengaturanTagihan ? (float) $pengaturanTagihan->nominal : 0;
        $targetPenerimaanKk = $jumlahKkAktif * $tarifTagihanKk;

        // Tagihan KK Realisasi
        $tagihanKkQuery = TagihanWaliMurid::where('tahun_pelajaran_id', $selectedTahunId);
        if ($pengaturanTagihan) {
            $tagihanKkQuery->where('pengaturan_tagihan_id', $pengaturanTagihan->id);
        }

        $totalTagihanKkTerbit = (clone $tagihanKkQuery)->count();

        // 1. Identifikasi Transaksi Potong Tabungan yang BELUM Terkonfirmasi (is_verified = false)
        $pendingTabunganQuery = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $selectedTahunId)
            ->whereIn('metode_pembayaran', ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri'])
            ->where('is_verified', false);

        $totalPendingTabunganNominal = (float) (clone $pendingTabunganQuery)->sum('nominal_bayar');
        $totalPendingTabunganCount = (clone $pendingTabunganQuery)->count();
        $tagihanPendingIds = (clone $pendingTabunganQuery)->pluck('tagihan_wali_murid_id')->unique();

        // 2. Perhitungan Dana Masuk KK yang SAH (HANYA Tunai atau Potong Tabungan Terverifikasi)
        $pembayaranSahQuery = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $selectedTahunId)
            ->where(function ($q) {
                $q->whereNotIn('metode_pembayaran', ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri'])
                  ->orWhere('is_verified', true);
            });
        $pembayaranSahNominal = (float) (clone $pembayaranSahQuery)->sum('nominal_bayar');

        // Fallback untuk tagihan lunas lama yang belum tercatat di pembayaran_tagihan_ikhtibars
        $tagihanLunasIdsDiIkhtibar = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $selectedTahunId)
            ->pluck('tagihan_wali_murid_id')
            ->unique();

        $nominalLunasLama = (float) (clone $tagihanKkQuery)
            ->where('status_bayar', 'Lunas')
            ->whereNotIn('id', $tagihanLunasIdsDiIkhtibar)
            ->sum('nominal_tagihan');

        // Total Kas Penerimaan Sah Masuk (Uang Riil)
        $totalPenerimaanKk = $pembayaranSahNominal + $nominalLunasLama;

        // 3. Tagihan KK Lunas Sah (Tagihan Lunas tanpa ada transaksi tabungan yang pending verifikasi)
        $totalTagihanKkLunas = (clone $tagihanKkQuery)
            ->where('status_bayar', 'Lunas')
            ->whereNotIn('id', $tagihanPendingIds)
            ->count();

        $totalKkPendingVerifikasi = $tagihanPendingIds->count();
        $totalTagihanKkBelumLunas = $totalTagihanKkTerbit - $totalTagihanKkLunas;
        $sisaPiutangKk = max(0, $targetPenerimaanKk - $totalPenerimaanKk);
        $persenLunas = $targetPenerimaanKk > 0 ? round(($totalPenerimaanKk / $targetPenerimaanKk) * 100, 1) : 0;

        // 2. Data Pagu & Serapan Dana Divisi
        $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)->orderBy('urutan')->get();
        $totalPaguAnggaran = (float) $divisis->sum('nominal_pagu');

        $totalDanaTersalurkan = (float) PenyaluranDanaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jenis_transaksi', 'Penyaluran Pagu')
            ->sum('nominal');

        $totalDanaDitarik = (float) PenyaluranDanaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jenis_transaksi', 'Penarikan Sisa Kas')
            ->sum('nominal');

        $totalRealisasiSpj = (float) PengeluaranIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->sum('nominal');

        $totalSisaKasFisik = max(0, ($totalDanaTersalurkan - $totalRealisasiSpj) - $totalDanaDitarik);
        $totalSisaPaguBelumCair = max(0, $totalPaguAnggaran - $totalDanaTersalurkan);
        $saldoKasBersihBph = max(0, ($totalPenerimaanKk - $totalDanaTersalurkan) + $totalDanaDitarik);

        // 3. Susunan Pengurus Harian (BPH)
        $bphList = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('kelompok_jabatan', 'BPH')
            ->with(['ustadz'])
            ->get();

        $ketua = $bphList->firstWhere('jabatan', 'Ketua');
        $wakilKetua = $bphList->firstWhere('jabatan', 'Wakil Ketua');
        $sekretaris = $bphList->firstWhere('jabatan', 'Sekretaris');
        $wakilSekretaris = $bphList->firstWhere('jabatan', 'Wakil Sekretaris');
        $bendahara = $bphList->firstWhere('jabatan', 'Bendahara');
        $wakilBendahara = $bphList->firstWhere('jabatan', 'Wakil Bendahara');

        // 4. Rekap Per Dusun / Kampung
        $kampungs = Kampung::withCount([
            'waliMurids as total_kk' => function ($q) use ($selectedTahunId) {
                $q->aktifDiTahun($selectedTahunId);
            }
        ])->get();

        $kampungStatistik = [];
        foreach ($kampungs as $kp) {
            $waliKkIds = WaliMurid::where('kampung_id', $kp->id)->aktifDiTahun($selectedTahunId)->pluck('id');
            $totalKkKampung = $waliKkIds->count();

            // Tagihan lunas sah di kampung ini (tanpa yang tabungannya pending)
            $tagihanLunasKampung = TagihanWaliMurid::where('tahun_pelajaran_id', $selectedTahunId)
                ->whereIn('wali_murid_id', $waliKkIds)
                ->where('status_bayar', 'Lunas')
                ->whereNotIn('id', $tagihanPendingIds);

            if ($pengaturanTagihan) {
                $tagihanLunasKampung->where('pengaturan_tagihan_id', $pengaturanTagihan->id);
            }

            $countLunas = $tagihanLunasKampung->count();

            // Nominal uang masuk sah dari KK kampung ini
            $nominalLunasKampung = (float) PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $selectedTahunId)
                ->whereIn('wali_murid_id', $waliKkIds)
                ->where(function ($q) {
                    $q->whereNotIn('metode_pembayaran', ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri'])
                      ->orWhere('is_verified', true);
                })
                ->sum('nominal_bayar');

            $nominalLunasLamaKampung = (float) (clone $tagihanLunasKampung)
                ->whereNotIn('id', $tagihanLunasIdsDiIkhtibar)
                ->sum('nominal_tagihan');

            $nominalLunas = $nominalLunasKampung + $nominalLunasLamaKampung;
            $persen = $totalKkKampung > 0 ? round(($countLunas / $totalKkKampung) * 100) : 0;

            $kampungStatistik[] = [
                'kode'          => $kp->kode,
                'nama'          => $kp->nama_kampung,
                'total'         => $totalKkKampung,
                'lunas'         => $countLunas,
                'nominal_lunas' => $nominalLunas,
                'persen'        => $persen,
            ];
        }

        // 5. Data Transaksi Terbaru
        $pengeluaranTerbaru = PengeluaranIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['divisi', 'diinputOleh.ustadz'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $transaksiPenyaluranTerbaru = PenyaluranDanaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['divisi'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // 6. Rekapitulasi Pembayaran via Potong Tabungan
        $tabunganQuery = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $selectedTahunId)
            ->whereIn('metode_pembayaran', ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri']);

        $rekapPotongTabungan = [
            'total_nominal'    => (float) (clone $tabunganQuery)->sum('nominal_bayar'),
            'total_transaksi'  => (clone $tabunganQuery)->count(),
            'verified_nominal' => (float) (clone $tabunganQuery)->where('is_verified', true)->sum('nominal_bayar'),
            'verified_count'   => (clone $tabunganQuery)->where('is_verified', true)->count(),
            'pending_nominal'  => (float) (clone $tabunganQuery)->where('is_verified', false)->sum('nominal_bayar'),
            'pending_count'    => (clone $tabunganQuery)->where('is_verified', false)->count(),
        ];

        // 7. Rekapitulasi Penerimaan Kasir per Petugas BPH
        // Aturan: Pembayaran via potongan tabungan manual dihitung ke Administrator meskipun diinput oleh Bendahara atau BPH lain
        $bphUserMap = [];
        foreach ($bphList as $bp) {
            if ($bp->ustadz?->user_id) {
                $bphUserMap[$bp->ustadz->user_id] = $bp->jabatan;
            }
        }

        $adminUser = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'administrator');
        })->first() ?? \App\Models\User::first();
        $adminUserId = $adminUser?->id;

        $semuaPembayaran = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $selectedTahunId)
            ->with(['diterimaOleh.ustadz'])
            ->get();

        $rekapBphGrouped = [];

        foreach ($semuaPembayaran as $p) {
            $isTabungan = in_array($p->metode_pembayaran, ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri', 'Potong Tabungan Manual'])
                || !empty($p->nomor_tabungan);

            // Jika potong tabungan, selalu diatribusikan ke Administrator
            $effectiveUserId = $isTabungan ? $adminUserId : $p->diterima_oleh_id;
            $effectiveKey = $effectiveUserId ?? 0;

            if (!isset($rekapBphGrouped[$effectiveKey])) {
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

                $rekapBphGrouped[$effectiveKey] = [
                    'user_id'          => $effectiveUserId,
                    'nama'             => $nama,
                    'jabatan'          => $jabatan,
                    'total_transaksi'  => 0,
                    'total_nominal'    => 0,
                    'nominal_tunai'    => 0,
                    'nominal_tabungan' => 0,
                ];
            }

            $rekapBphGrouped[$effectiveKey]['total_transaksi'] += 1;
            $rekapBphGrouped[$effectiveKey]['total_nominal'] += (float) $p->nominal_bayar;
            if ($isTabungan) {
                $rekapBphGrouped[$effectiveKey]['nominal_tabungan'] += (float) $p->nominal_bayar;
            } else {
                $rekapBphGrouped[$effectiveKey]['nominal_tunai'] += (float) $p->nominal_bayar;
            }
        }

        $rekapPenerimaanBph = collect(array_values($rekapBphGrouped))->sortBy(function ($item) {
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

        return view('ikhtibar.dashboard.index', compact(
            'kegiatan',
            'tahunPelajarans',
            'tahunAktif',
            'selectedTahunId',
            'pengaturanTagihan',
            'jumlahKkAktif',
            'tarifTagihanKk',
            'targetPenerimaanKk',
            'totalTagihanKkTerbit',
            'totalTagihanKkLunas',
            'totalPenerimaanKk',
            'sisaPiutangKk',
            'persenLunas',
            'divisis',
            'totalPaguAnggaran',
            'totalDanaTersalurkan',
            'totalRealisasiSpj',
            'totalSisaKasFisik',
            'totalSisaPaguBelumCair',
            'totalDanaDitarik',
            'saldoKasBersihBph',
            'bphList',
            'ketua',
            'wakilKetua',
            'sekretaris',
            'wakilSekretaris',
            'bendahara',
            'wakilBendahara',
            'kampungs',
            'kampungStatistik',
            'totalPendingTabunganNominal',
            'totalPendingTabunganCount',
            'totalKkPendingVerifikasi',
            'pengeluaranTerbaru',
            'transaksiPenyaluranTerbaru',
            'rekapPotongTabungan',
            'rekapPenerimaanBph'
        ));
    }
}
