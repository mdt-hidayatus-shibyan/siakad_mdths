<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\Murid;
use App\Models\PembayaranTagihan;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Ringkasan SPP Ruangan Binaan (Monitoring Read-Only)
     */
    public function getSppRingkasan(Request $request)
    {
        $user = $request->user();
        $ustadzId = $user->ustadz->id ?? null;
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 1;

        $accessibleRuangans = Ruangan::with('level')
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('ustadz_id', $ustadzId)
            ->get();

        if ($accessibleRuangans->isEmpty()) {
            $accessibleRuangans = Ruangan::with('level')
                ->where('ustadz_id', $ustadzId)
                ->get();
        }

        if ($request->filled('ruangan_id')) {
            $ruangan = $accessibleRuangans->firstWhere('id', $request->ruangan_id) ?? Ruangan::with('level')->find($request->ruangan_id);
        } else {
            $ruangan = $accessibleRuangans->first();
        }

        if (!$ruangan) {
            $ruangan = Ruangan::with('level')->first();
        }

        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->get();

        $masterSpp = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe', 'bulanan')
            ->first();

        $nominalSpp = $masterSpp->nominal ?? 0;

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');
        $totalMurid = $murids->count();
        $totalBulan = $bulanHijriyah->count() > 0 ? $bulanHijriyah->count() : 11;

        // Ambil data tagihan SPP di ruangan ini yang benar-benar diterbitkan
        $tagihans = TagihanMurid::where('ruangan_id', $ruangan->id)
            ->where(function ($q) use ($masterSpp) {
                if ($masterSpp) {
                    $q->where('pengaturan_tagihan_id', $masterSpp->id);
                } else {
                    $q->where('nama_tagihan_spesifik', 'LIKE', '%SPP%')
                        ->orWhere('nama_tagihan_spesifik', 'LIKE', '%Syahriyah%');
                }
            })
            ->get();

        $isTerbit = $tagihans->isNotEmpty();

        if (!$isTerbit) {
            $totalTargetNominal = 0;
            $totalLunasNominal = 0;
            $totalTunggakanNominal = 0;
            $muridLunasSemua = 0;
            $muridBelumLunas = 0;
            $muridBebasDonatur = 0;
        } else {
            $totalLunasNominal = $tagihans->where('status_bayar', 'Lunas')->sum('nominal_tagihan');
            $totalTargetNominal = $tagihans->sum('nominal_tagihan');
            $totalTunggakanNominal = max(0, $totalTargetNominal - $totalLunasNominal);

            // Hitung per murid
            $grouped = $tagihans->groupBy('murid_id');
            $muridLunasSemua = 0;
            $muridBelumLunas = 0;
            $muridBebasDonatur = 0;

            foreach ($murids as $m) {
                $mTags = $grouped->get($m->id, collect());
                if ($mTags->isEmpty()) continue;
                $lunasCount = $mTags->where('status_bayar', 'Lunas')->count();
                $donaturCount = $mTags->whereIn('status_bayar', ['Ditanggung Donatur', 'Bebas SPP', 'Gratis'])->count();
                $mTotalBulan = $mTags->count();

                if ($donaturCount >= $mTotalBulan && $mTotalBulan > 0) {
                    $muridBebasDonatur++;
                } elseif ($lunasCount >= $mTotalBulan && $mTotalBulan > 0) {
                    $muridLunasSemua++;
                } else {
                    $muridBelumLunas++;
                }
            }
        }

        $ruanganList = $accessibleRuangans->map(fn($r) => [
            'id' => $r->id,
            'nama_ruangan' => $r->nama_ruangan,
            'level_nama' => $r->level->nama_level ?? '-',
        ]);

        $bulanList = $bulanHijriyah->map(fn($b) => [
            'id' => $b->id,
            'nama_bulan' => $b->nama_bulan,
            'tahun_hijriyah' => $b->tahun_hijriyah ?? '',
            'urutan' => (int) $b->urutan,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'ruangan_id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'nominal_spp_bulanan' => (int) $nominalSpp,
                'total_murid' => $totalMurid,
                'total_bulan' => $totalBulan,
                'total_target_spp' => (int) $totalTargetNominal,
                'total_lunas_nominal' => (int) $totalLunasNominal,
                'total_tunggakan_nominal' => (int) $totalTunggakanNominal,
                'total_murid_lunas_semua' => $muridLunasSemua,
                'total_murid_belum_lunas' => $muridBelumLunas,
                'total_murid_bebas_donatur' => $muridBebasDonatur,
                'ruangan_list' => $ruanganList,
                'bulan_list' => $bulanList,
            ]
        ], 200);
    }

    /**
     * Daftar Murid dan Status SPP (11 Bulan Hijriyah)
     */
    public function getSppMuridList(Request $request)
    {
        $ruanganId = $request->ruangan_id;
        if (!$ruanganId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter ruangan_id wajib diisi.'
            ], 422);
        }

        $ruangan = Ruangan::with('level')->findOrFail($ruanganId);
        $tahunId = $ruangan->tahun_pelajaran_id;

        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->get();

        $masterSpp = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe', 'bulanan')
            ->first();

        $nominalSpp = $masterSpp->nominal ?? 0;
        $totalBulan = $bulanHijriyah->count() > 0 ? $bulanHijriyah->count() : 11;

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');

        $tagihansQuery = TagihanMurid::with('pembayaranTagihan')
            ->where('ruangan_id', $ruangan->id);

        if ($masterSpp) {
            $tagihansQuery->where('pengaturan_tagihan_id', $masterSpp->id);
        } else {
            $tagihansQuery->where(function ($q) {
                $q->where('nama_tagihan_spesifik', 'LIKE', '%SPP%')
                    ->orWhere('nama_tagihan_spesifik', 'LIKE', '%Syahriyah%');
            });
        }

        $allTagihans = $tagihansQuery->get();

        // JIKA BELUM ADA TAGIHAN SPP YANG DITERBITKAN DI RUANGAN INI
        if ($allTagihans->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => []
            ], 200);
        }

        $tagihans = $allTagihans->groupBy('murid_id');

        $filterStatus = $request->status ?? 'Semua';
        $filterBulanId = $request->bulan_hijriyah_id;
        $search = strtolower(trim($request->search ?? ''));

        $data = [];

        foreach ($murids as $m) {
            $nama = $m->nama_lengkap ?? $m->nama;
            $nism = $m->nism ?? '';

            if ($search !== '') {
                if (!str_contains(strtolower($nama), $search) && !str_contains($nism, $search)) {
                    continue;
                }
            }

            $mTags = $tagihans->get($m->id, collect());
            if ($mTags->isEmpty()) {
                continue;
            }

            $mTagsByBulan = $mTags->keyBy('bulan_hijriyah_id');

            $bulanItems = [];
            $lunasCount = 0;
            $donaturCount = 0;
            $belumLunasCount = 0;
            $totalDibayar = 0;

            foreach ($bulanHijriyah as $b) {
                $t = $mTagsByBulan->get($b->id);
                if (!$t) continue;

                $status = $t->status_bayar;
                $nominal = (int) $t->nominal_tagihan;

                $noKwitansi = $t->pembayaranTagihan?->no_transaksi;
                $tglBayar = $t->pembayaranTagihan?->tanggal_bayar;
                $hariTanggalBayar = null;
                if ($tglBayar) {
                    try {
                        $hariTanggalBayar = Carbon::parse($tglBayar)->locale('id')->isoFormat('D MMMM YYYY');
                    } catch (\Exception $e) {
                    }
                }

                if ($status === 'Lunas') {
                    $lunasCount++;
                    $totalDibayar += $nominal;
                } elseif (in_array($status, ['Ditanggung Donatur', 'Bebas SPP', 'Gratis'])) {
                    $donaturCount++;
                } else {
                    $belumLunasCount++;
                }

                $bulanItems[] = [
                    'tagihan_id' => $t->id,
                    'bulan_hijriyah_id' => $b->id,
                    'nama_bulan' => $b->nama_bulan,
                    'tahun_hijriyah' => $b->tahun_hijriyah ?? '',
                    'nominal' => $nominal,
                    'status_bayar' => $status,
                    'no_kwitansi' => $noKwitansi,
                    'tanggal_bayar' => $tglBayar ? (string) $tglBayar : null,
                    'hari_tanggal_bayar' => $hariTanggalBayar,
                ];
            }

            $targetMurid = $mTags->sum('nominal_tagihan');
            $sisaTunggakan = max(0, $targetMurid - $totalDibayar);

            $statusKeseluruhan = 'Belum Lunas';
            if ($donaturCount >= count($bulanItems) && count($bulanItems) > 0) {
                $statusKeseluruhan = 'Ditanggung Donatur';
            } elseif ($lunasCount >= count($bulanItems) && count($bulanItems) > 0) {
                $statusKeseluruhan = 'Lunas';
            }

            // Filter status
            if ($filterStatus === 'Lunas' && $statusKeseluruhan !== 'Lunas') {
                continue;
            } elseif ($filterStatus === 'Belum Lunas' && $statusKeseluruhan !== 'Belum Lunas') {
                continue;
            } elseif ($filterStatus === 'Ditanggung Donatur' && $statusKeseluruhan !== 'Ditanggung Donatur') {
                continue;
            }

            // Filter bulan spesifik jika dipilih
            if ($filterBulanId) {
                $targetBulan = collect($bulanItems)->firstWhere('bulan_hijriyah_id', (int) $filterBulanId);
                if ($filterStatus === 'Lunas' && ($targetBulan['status_bayar'] ?? '') !== 'Lunas') {
                    continue;
                } elseif ($filterStatus === 'Belum Lunas' && ($targetBulan['status_bayar'] ?? '') !== 'Belum Lunas') {
                    continue;
                }
            }

            $data[] = [
                'murid_id' => $m->id,
                'nama' => $nama,
                'nism' => $nism,
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'foto' => $m->foto ? asset('storage/' . $m->foto) : null,
                'wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'nama_wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'total_bulan' => count($bulanItems),
                'bulan_lunas_count' => $lunasCount,
                'bulan_belum_lunas_count' => $belumLunasCount,
                'bulan_bebas_count' => $donaturCount,
                'total_target' => $targetMurid,
                'total_dibayar' => $totalDibayar,
                'sisa_tunggakan' => $sisaTunggakan,
                'status_keseluruhan' => $statusKeseluruhan,
                'bulan_items' => $bulanItems,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Detail Kartu SPP Murid (11 Bulan Hijriyah Lengkap)
     */
    public function getKartuSppMurid(Request $request, $muridId)
    {
        $murid = Murid::with(['waliMurid'])->findOrFail($muridId);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 1;

        $ruangan = $murid->ruangans()->where('ruangans.tahun_pelajaran_id', $tahunId)->first()
            ?? $murid->ruangans()->first();

        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan', 'asc')
            ->get();

        $masterSpp = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe', 'bulanan')
            ->first();

        $nominalSpp = $masterSpp->nominal ?? 0;

        $tagihansQuery = TagihanMurid::with('pembayaranTagihan')
            ->where('murid_id', $murid->id);

        if ($masterSpp) {
            $tagihansQuery->where('pengaturan_tagihan_id', $masterSpp->id);
        } else {
            $tagihansQuery->where(function ($q) {
                $q->where('nama_tagihan_spesifik', 'LIKE', '%SPP%')
                    ->orWhere('nama_tagihan_spesifik', 'LIKE', '%Syahriyah%');
            });
        }

        $tagihans = $tagihansQuery->get()->keyBy('bulan_hijriyah_id');

        if ($tagihans->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'murid_id' => $murid->id,
                    'nama' => $murid->nama_lengkap ?? $murid->nama,
                    'nism' => $murid->nism ?? '-',
                    'jenis_kelamin' => $murid->jenis_kelamin ?? 'L',
                    'foto' => $murid->foto ? asset('storage/' . $murid->foto) : null,
                    'nama_ruangan' => $ruangan->nama_ruangan ?? '-',
                    'level_nama' => $ruangan->level->nama_level ?? '-',
                    'nama_wali' => $murid->nama_ayah ?? $murid->waliMurid->nama_kepala_keluarga ?? '-',
                    'wali' => $murid->nama_ayah ?? $murid->waliMurid->nama_kepala_keluarga ?? '-',
                    'alamat' => $murid->waliMurid->alamat_detail ?? $murid->waliMurid->kampung->nama_kampung ?? '-',
                    'total_bulan' => 0,
                    'bulan_lunas_count' => 0,
                    'bulan_belum_lunas_count' => 0,
                    'bulan_bebas_count' => 0,
                    'total_target' => 0,
                    'total_dibayar' => 0,
                    'sisa_tunggakan' => 0,
                    'status_keseluruhan' => 'Belum Diterbitkan',
                    'bulan_items' => [],
                ]
            ], 200);
        }

        $bulanItems = [];
        $lunasCount = 0;
        $donaturCount = 0;
        $belumLunasCount = 0;
        $totalDibayar = 0;

        foreach ($bulanHijriyah as $b) {
            $t = $tagihans->get($b->id);
            if (!$t) continue;

            $status = $t->status_bayar;
            $nominal = (int) $t->nominal_tagihan;

            $noKwitansi = $t->pembayaranTagihan?->no_transaksi;
            $tglBayar = $t->pembayaranTagihan?->tanggal_bayar;
            $hariTanggalBayar = null;
            if ($tglBayar) {
                try {
                    $hariTanggalBayar = Carbon::parse($tglBayar)->locale('id')->isoFormat('dddd, D MMMM YYYY');
                } catch (\Exception $e) {
                }
            }

            if ($status === 'Lunas') {
                $lunasCount++;
                $totalDibayar += $nominal;
            } elseif (in_array($status, ['Ditanggung Donatur', 'Bebas SPP', 'Gratis'])) {
                $donaturCount++;
            } else {
                $belumLunasCount++;
            }

            $bulanItems[] = [
                'tagihan_id' => $t->id,
                'bulan_hijriyah_id' => $b->id,
                'nama_bulan' => $b->nama_bulan,
                'tahun_hijriyah' => $b->tahun_hijriyah ?? '',
                'nominal' => $nominal,
                'status_bayar' => $status,
                'no_kwitansi' => $noKwitansi,
                'tanggal_bayar' => $tglBayar ? (string) $tglBayar : null,
                'hari_tanggal_bayar' => $hariTanggalBayar,
            ];
        }

        $targetMurid = $tagihans->sum('nominal_tagihan');
        $sisaTunggakan = max(0, $targetMurid - $totalDibayar);

        $statusKeseluruhan = 'Belum Lunas';
        if ($donaturCount >= count($bulanItems) && count($bulanItems) > 0) {
            $statusKeseluruhan = 'Ditanggung Donatur';
        } elseif ($lunasCount >= count($bulanItems) && count($bulanItems) > 0) {
            $statusKeseluruhan = 'Lunas';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'murid_id' => $murid->id,
                'nama' => $murid->nama_lengkap ?? $murid->nama,
                'nism' => $murid->nism ?? '-',
                'jenis_kelamin' => $murid->jenis_kelamin ?? 'L',
                'foto' => $murid->foto ? asset('storage/' . $murid->foto) : null,
                'nama_ruangan' => $ruangan->nama_ruangan ?? '-',
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'nama_wali' => $murid->nama_ayah ?? $murid->waliMurid->nama_kepala_keluarga ?? '-',
                'wali' => $murid->nama_ayah ?? $murid->waliMurid->nama_kepala_keluarga ?? '-',
                'alamat' => $murid->waliMurid->alamat_detail ?? $murid->waliMurid->kampung->nama_kampung ?? '-',
                'total_bulan' => count($bulanItems),
                'bulan_lunas_count' => $lunasCount,
                'bulan_belum_lunas_count' => $belumLunasCount,
                'bulan_bebas_count' => $donaturCount,
                'total_target' => $targetMurid,
                'total_dibayar' => $totalDibayar,
                'sisa_tunggakan' => $sisaTunggakan,
                'status_keseluruhan' => $statusKeseluruhan,
                'bulan_items' => $bulanItems,
            ]
        ], 200);
    }

    // =========================================================================
    // TAGIHAN NON-SPP (SEMESTER & INSIDENTAL)
    // =========================================================================

    /**
     * Master List Tagihan Non-SPP (Semester & Insidental)
     */
    public function getNonSppMasterList(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 1;

        $masters = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe', '!=', 'bulanan')
            ->orderBy('id', 'asc')
            ->get();

        $data = $masters->map(fn($m) => [
            'id' => $m->id,
            'kode_tagihan' => $m->kode_tagihan,
            'nama_tagihan' => $m->nama_tagihan,
            'tipe' => $m->tipe,
            'nominal' => (int) $m->nominal,
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Ringkasan Tagihan Non-SPP Ruangan Binaan
     */
    public function getNonSppRingkasan(Request $request)
    {
        $user = $request->user();
        $ustadzId = $user->ustadz->id ?? null;
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 1;

        $accessibleRuangans = Ruangan::with('level')
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('ustadz_id', $ustadzId)
            ->get();

        if ($accessibleRuangans->isEmpty()) {
            $accessibleRuangans = Ruangan::with('level')
                ->where('ustadz_id', $ustadzId)
                ->get();
        }

        if ($request->filled('ruangan_id')) {
            $ruangan = $accessibleRuangans->firstWhere('id', $request->ruangan_id) ?? Ruangan::with('level')->find($request->ruangan_id);
        } else {
            $ruangan = $accessibleRuangans->first();
        }

        if (!$ruangan) {
            $ruangan = Ruangan::with('level')->first();
        }

        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        $masters = PengaturanTagihan::where('tahun_pelajaran_id', $tahunId)
            ->where('tipe', '!=', 'bulanan')
            ->orderBy('id', 'asc')
            ->get();

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($masters->first()->id ?? null);
        $selectedMaster = $masters->firstWhere('id', $selectedMasterId) ?? $masters->first();

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');
        $totalMurid = $murids->count();

        $tagihans = collect();
        if ($selectedMaster) {
            $tagihans = TagihanMurid::where('ruangan_id', $ruangan->id)
                ->where('pengaturan_tagihan_id', $selectedMaster->id)
                ->get();
        }

        $nominalPerMurid = $selectedMaster->nominal ?? 0;
        $isTerbit = $tagihans->isNotEmpty();

        if (!$isTerbit) {
            $totalTarget = 0;
            $totalLunas = 0;
            $totalTunggakan = 0;
            $totalMuridLunas = 0;
            $totalMuridBelumLunas = 0;
        } else {
            $totalTarget = $tagihans->sum('nominal_tagihan');
            $totalLunas = $tagihans->where('status_bayar', 'Lunas')->sum('nominal_tagihan');
            $totalTunggakan = max(0, $totalTarget - $totalLunas);
            $totalMuridLunas = $tagihans->where('status_bayar', 'Lunas')->count();
            $totalMuridBelumLunas = max(0, $tagihans->count() - $totalMuridLunas);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ruangan_id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level_nama' => $ruangan->level->nama_level ?? '-',
                'pengaturan_tagihan_id' => $selectedMaster->id ?? null,
                'nama_tagihan' => $selectedMaster->nama_tagihan ?? '-',
                'kode_tagihan' => $selectedMaster->kode_tagihan ?? '-',
                'tipe_tagihan' => $selectedMaster->tipe ?? '-',
                'nominal' => (int) $nominalPerMurid,
                'total_murid' => $totalMurid,
                'total_target_nominal' => (int) $totalTarget,
                'total_lunas_nominal' => (int) $totalLunas,
                'total_tunggakan_nominal' => (int) $totalTunggakan,
                'total_murid_lunas' => $totalMuridLunas,
                'total_murid_belum_lunas' => $totalMuridBelumLunas,
                'ruangan_list' => $accessibleRuangans->map(fn($r) => [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_nama' => $r->level->nama_level ?? '-',
                ]),
                'master_tagihan_list' => $masters->map(fn($m) => [
                    'id' => $m->id,
                    'kode_tagihan' => $m->kode_tagihan,
                    'nama_tagihan' => $m->nama_tagihan,
                    'tipe' => $m->tipe,
                    'nominal' => (int) $m->nominal,
                ]),
            ]
        ], 200);
    }

    /**
     * Daftar Murid dan Status Tagihan Non-SPP Tertentu
     */
    public function getNonSppMuridList(Request $request)
    {
        $ruanganId = $request->ruangan_id;
        $pengaturanTagihanId = $request->pengaturan_tagihan_id;

        if (!$ruanganId || !$pengaturanTagihanId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter ruangan_id dan pengaturan_tagihan_id wajib diisi.'
            ], 422);
        }

        $ruangan = Ruangan::findOrFail($ruanganId);
        $tahunId = $ruangan->tahun_pelajaran_id;
        $master = PengaturanTagihan::findOrFail($pengaturanTagihanId);

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif');

        $allTagihans = TagihanMurid::with('pembayaranTagihan')
            ->where('ruangan_id', $ruangan->id)
            ->where('pengaturan_tagihan_id', $master->id)
            ->get();

        if ($allTagihans->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => []
            ], 200);
        }

        $tagihans = $allTagihans->keyBy('murid_id');

        $filterStatus = $request->status ?? 'Semua';
        $search = strtolower(trim($request->search ?? ''));

        $data = [];
        foreach ($murids as $m) {
            $t = $tagihans->get($m->id);
            if (!$t) {
                continue;
            }

            $nama = $m->nama_lengkap ?? $m->nama;
            $nism = $m->nism ?? '';

            if ($search !== '') {
                if (!str_contains(strtolower($nama), $search) && !str_contains($nism, $search)) {
                    continue;
                }
            }

            $status = ($t && $t->status_bayar === 'Lunas') ? 'Lunas' : 'Belum Lunas';
            $nominal = $t ? (int) $t->nominal_tagihan : (int) $master->nominal;

            if ($filterStatus === 'Lunas' && $status !== 'Lunas') {
                continue;
            } elseif ($filterStatus === 'Belum Lunas' && $status !== 'Belum Lunas') {
                continue;
            }

            $noKwitansi = $t?->pembayaranTagihan?->no_transaksi;
            $tglBayar = $t?->pembayaranTagihan?->tanggal_bayar;
            $metodeBayar = $t?->pembayaranTagihan?->metode_pembayaran;
            $tipePembayar = $t?->pembayaranTagihan?->tipe_pembayar;
            $catatan = $t?->pembayaranTagihan?->catatan;

            $hariTanggalBayar = null;
            if ($tglBayar) {
                try {
                    $hariTanggalBayar = Carbon::parse($tglBayar)->locale('id')->isoFormat('D MMMM YYYY');
                } catch (\Exception $e) {
                }
            }

            $data[] = [
                'tagihan_id' => $t?->id,
                'murid_id' => $m->id,
                'nama' => $nama,
                'nism' => $nism,
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'foto' => $m->foto ? asset('storage/' . $m->foto) : null,
                'wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'nama_wali' => $m->nama_ayah ?? $m->waliMurid->nama_kepala_keluarga ?? '-',
                'nominal' => $nominal,
                'status_bayar' => $status,
                'no_kwitansi' => $noKwitansi,
                'tanggal_bayar' => $tglBayar ? (string) $tglBayar : null,
                'hari_tanggal_bayar' => $hariTanggalBayar,
                'metode_pembayaran' => $metodeBayar,
                'tipe_pembayar' => $tipePembayar,
                'catatan' => $catatan,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Proses Pembayaran Tagihan Non-SPP (Single atau Massal)
     */
    public function prosesBayarNonSpp(Request $request)
    {
        $request->validate([
            'tagihan_ids' => 'required|array',
            'tagihan_ids.*' => 'exists:tagihan_murids,id',
            'tipe_pembayar' => 'nullable|string',
            'metode_pembayaran' => 'nullable|string',
            'tanggal_bayar' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        $tagihanIds = $request->tagihan_ids;
        $tipePembayar = $request->tipe_pembayar ?? 'Wali Murid';
        $metodePembayaran = $request->metode_pembayaran ?? 'Tunai';
        $tanggalBayar = $request->tanggal_bayar ?? now();
        $catatanInput = $request->catatan;

        DB::beginTransaction();
        try {
            $tagihans = TagihanMurid::with(['murid.waliMurid', 'pengaturanTagihan'])
                ->whereIn('id', $tagihanIds)
                ->get();

            if ($tagihans->isEmpty()) {
                throw new \Exception('Tidak ada tagihan yang valid.');
            }

            $totalNominal = $tagihans->sum('nominal_tagihan');
            $firstMurid = $tagihans->first()->murid;
            $firstMaster = $tagihans->first()->pengaturanTagihan;

            $hari = Carbon::parse($tanggalBayar)->format('d');
            $bulan = Carbon::parse($tanggalBayar)->format('m');
            $tahun = Carbon::parse($tanggalBayar)->format('Y');
            $kodeTagihan = $firstMaster->kode_tagihan ?? 'TGH';
            $nism = $firstMurid->nism ?? '0000';
            $randomCode = mt_rand(10000, 99999);

            $noKwitansi = 'TRX/' . $kodeTagihan . '/' . $nism . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode;

            $namaPembayar = $firstMurid->nama_ayah ?? $firstMurid->waliMurid->nama_wali ?? $firstMurid->nama_lengkap;
            if ($tipePembayar === 'Donatur') {
                $namaPembayar = 'Donatur MDT Hidayatus Shibyan';
            }

            $catatanFinal = $catatanInput ?? ($tagihans->count() > 1
                ? "Pembayaran Tagihan {$firstMaster->nama_tagihan} ({$tagihans->count()} Murid)"
                : "Pembayaran Tagihan {$firstMaster->nama_tagihan} a.n. {$firstMurid->nama_lengkap} ({$firstMurid->nism})");

            $pembayaran = PembayaranTagihan::create([
                'no_transaksi'      => $noKwitansi,
                'tanggal_bayar'     => $tanggalBayar,
                'tipe_pembayar'     => $tipePembayar,
                'nama_pembayar'     => $namaPembayar,
                'metode_pembayaran' => $metodePembayaran,
                'total_nominal'     => $totalNominal,
                'catatan'           => $catatanFinal,
            ]);

            $statusBaru = ($tipePembayar === 'Donatur') ? 'Ditanggung Donatur' : 'Lunas';

            TagihanMurid::whereIn('id', $tagihanIds)->update([
                'status_bayar'          => $statusBaru,
                'pembayaran_tagihan_id' => $pembayaran->id,
                'updated_at'            => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tagihan berhasil dicatat!',
                'data' => [
                    'no_transaksi' => $noKwitansi,
                    'total_nominal' => $totalNominal,
                    'tanggal_bayar' => (string) $tanggalBayar,
                    'total_murid_terbayar' => $tagihans->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan Transaksi / Refund Tagihan Non-SPP
     */
    public function batalBayarNonSpp(Request $request, $tagihanId)
    {
        DB::beginTransaction();
        try {
            $tagihan = TagihanMurid::findOrFail($tagihanId);
            $pembayaranId = $tagihan->pembayaran_tagihan_id;

            if ($pembayaranId) {
                $pembayaran = PembayaranTagihan::find($pembayaranId);
                if ($pembayaran) {
                    $sisaTagihan = TagihanMurid::where('pembayaran_tagihan_id', $pembayaranId)
                        ->where('id', '!=', $tagihan->id)
                        ->count();

                    if ($sisaTagihan == 0) {
                        $pembayaran->delete();
                    } else {
                        $pembayaran->decrement('total_nominal', $tagihan->nominal_tagihan);
                    }
                }
            }

            $tagihan->update([
                'status_bayar' => 'Belum Lunas',
                'pembayaran_tagihan_id' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tagihan berhasil dibatalkan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
