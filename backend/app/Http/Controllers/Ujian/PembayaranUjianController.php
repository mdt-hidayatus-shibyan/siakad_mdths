<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
// use App\Models\Kepengurusan\Pengurus;
use App\Models\PembayaranTagihan;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranUjianController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;
        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();
        $ruanganTerpilih = null;
        $jenisTagihanTerpilih = null;
        $masterBiayas = collect();
        $murids = collect();
        $tagihanExisting = collect();
        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);

            if ($ruanganTerpilih) {
                // Hanya ambil tagihan Ujian Reguler (IMDA), kecualikan tagihan IMNI & SPP
                $masterBiayas = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where(function ($q) use ($ruanganTerpilih) {
                        $q->where('level_id', $ruanganTerpilih->level_id);
                    })
                    ->where('kode_tagihan', '!=', 'IMNI')
                    ->where('kode_tagihan', 'NOT LIKE', '%IMNI%')
                    ->where('nama_tagihan', 'NOT LIKE', '%IMNI%')
                    ->where('kode_tagihan', '!=', 'SPP')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($request->pengaturan_tagihan_id) {
                    $jenisTagihanTerpilih = $masterBiayas->firstWhere('id', $request->pengaturan_tagihan_id);
                    if ($jenisTagihanTerpilih) {
                        $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
                        $ruanganTerpilih->setRelation('murids', $murids);

                        $tagihanExisting = TagihanMurid::whereIn('murid_id', $murids->pluck('id'))
                            ->where('pengaturan_tagihan_id', $jenisTagihanTerpilih->id)
                            ->get()
                            ->groupBy('murid_id');
                    }
                }
            }
        }

        return view('pembayaran-ujian.pembayaran-ujian-leger', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'masterBiayas',
            'ruanganTerpilih',
            'jenisTagihanTerpilih',
            'murids',
            'tagihanExisting',
        ));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'tagihan_ids'   => 'required|array',
            'tagihan_ids.*' => 'exists:tagihan_murids,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Tarik tagihan & KELOMPOKKAN berdasarkan murid_id
            $tagihans = TagihanMurid::with(['murid.waliMurid', 'pengaturanTagihan'])
                ->whereIn('id', $request->tagihan_ids)
                ->get()
                ->groupBy('murid_id');

            // 2. SETUP PENOMORAN KWITANSI (Query cukup 1x di luar loop)
            $bulan = now()->format('m');
            $tahun = now()->format('Y');

            // Ambil kode tagihan dari item pertama yang dilunasi
            $kodeTagihan = $tagihans->first()->first()->pengaturanTagihan->kode_tagihan ?? 'TGH';

            $randomCode = mt_rand(10000, 99999);

            // 3. LOOPING PEMBUATAN KWITANSI PER MURID
            foreach ($tagihans as $muridId => $tagihanGroup) {
                $murid = $tagihanGroup->first()->murid;
                $namaWali = $murid->waliMurid->nama_wali ?? $murid->waliMurid->nama_ayah ?? $murid->nama_lengkap;
                $totalNominal = $tagihanGroup->sum('nominal_tagihan');
                $nism = $murid->nism ?? '0000';

                $noKwitansi = 'TRX/' . $kodeTagihan . '/' . $nism . '/' . $tahun . '/' . $bulan . '/' . str_pad($randomCode, 3, '0', STR_PAD_LEFT);

                // Buat Kwitansi Induk
                $pembayaran = PembayaranTagihan::create([
                    'no_transaksi'      => $noKwitansi,
                    'tanggal_bayar'     => now(),
                    'tipe_pembayar'     => 'Wali Murid',
                    'nama_pembayar'     => $namaWali,
                    'metode_pembayaran' => 'Tunai',
                    'total_nominal'     => $totalNominal,
                    'catatan'           => 'Pelunasan Massal (Leger) a.n. Murid: ' . $murid->nama_lengkap,
                ]);

                // Update Tagihan Murid
                TagihanMurid::whereIn('id', $tagihanGroup->pluck('id'))->update([
                    'status_bayar'          => 'Lunas',
                    'pembayaran_tagihan_id' => $pembayaran->id,
                    'updated_at'            => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pelunasan massal berhasil diproses! Sebanyak ' . count($request->tagihan_ids) . ' tagihan telah dilunasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }
    public function batalTransaksi($id)
    {
        DB::beginTransaction();
        try {
            $tagihan = TagihanMurid::findOrFail($id);

            // 1. Simpan ID pembayaran sebelum dihilangkan
            // (Pastikan nama kolom foreign key-nya sesuai: pembayaran_tagihan_id atau pembayaran_id)
            $pembayaranId = $tagihan->pembayaran_tagihan_id;

            $statusBaru = 'Belum Lunas';

            // 2. Periksa Kwitansi Induknya
            if ($pembayaranId) {
                $pembayaran = PembayaranTagihan::find($pembayaranId);

                if ($pembayaran) {
                    // Jika dulu yang bayar adalah Donatur, kembalikan statusnya ke antrean Donatur
                    if ($pembayaran->tipe_pembayar === 'Donatur') {
                        $statusBaru = 'Ditanggung Donatur';
                    }

                    // Cek apakah masih ada tagihan lain yang terikat dengan kwitansi ini
                    $sisaTagihan = TagihanMurid::where('pembayaran_tagihan_id', $pembayaranId)
                        ->where('id', '!=', $tagihan->id)
                        ->count();

                    if ($sisaTagihan == 0) {
                        // Jika ini adalah satu-satunya tagihan di kwitansi itu, Hapus sekalian kwitansinya
                        $pembayaran->delete();
                    } else {
                        // Jika wali murid bayar 3 bulan sekaligus, lalu dibatalkan 1 bulan saja:
                        // Cukup kurangi total nominal di kwitansinya
                        $pembayaran->decrement('total_nominal', $tagihan->nominal_tagihan);
                    }
                }
            }

            // 3. Eksekusi pengembalian status & putus relasi kwitansi
            $tagihan->update([
                'status_bayar' => $statusBaru,
                'pembayaran_tagihan_id' => null
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Refund berhasil! Transaksi ' . $tagihan->nama_tagihan_spesifik . ' telah dibatalkan dan pencatatan kas telah disesuaikan otomatis.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    // 6. Laporan Penerimaan Pembayaran (Versi Filter Harian, Ruangan & Kode Tagihan)
    public function laporan(Request $request)
    {
        // 1. Setup Data Filter (Tahun & Ruangan)
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();
        $ruanganTerpilih = null;

        // Daftar Kode Tagihan Ujian Reguler di Tahun Pelajaran terpilih
        $daftarKodeTagihan = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where(function ($q) {
                $q->whereNull('sasaran')->orWhere('sasaran', 'murid');
            })
            ->where('kode_tagihan', '!=', 'IMNI')
            ->where('kode_tagihan', 'NOT LIKE', '%IMNI%')
            ->where('nama_tagihan', 'NOT LIKE', '%IMNI%')
            ->where('kode_tagihan', '!=', 'SPP')
            ->where('nama_tagihan', 'NOT LIKE', '%SPP%')
            ->where('nama_tagihan', 'NOT LIKE', '%Syahriyah%')
            ->select('kode_tagihan')
            ->distinct()
            ->orderBy('kode_tagihan', 'asc')
            ->pluck('kode_tagihan');

        // 2. Setup Parameter Default (Tanggal, Jenis, & Kode Tagihan)
        $startDate = $request->start_date ?? now()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $jenisBiaya = $request->jenis_biaya ?? 'Lainnya';
        $kodeTagihanFilter = $request->input('kode_tagihan');

        // 3. Query Dasar Buku Kas (Transaksi Pembayaran)
        $query = PembayaranTagihan::whereDate('tanggal_bayar', '>=', $startDate)
            ->whereDate('tanggal_bayar', '<=', $endDate);

        // ==========================================
        // FILTER 1: Kategori Selain SPP (Ujian / Lainnya)
        // ==========================================
        if ($jenisBiaya === 'Lainnya') {
            $query->whereHas('tagihanMurids', function ($q) {
                $q->where('nama_tagihan_spesifik', 'NOT LIKE', '%SPP%')
                    ->where('nama_tagihan_spesifik', 'NOT LIKE', '%Syahriyah%')
                    ->where('nama_tagihan_spesifik', 'NOT LIKE', '%IMNI%');
            });
        }

        // ==========================================
        // FILTER 2: Berdasarkan Kode Tagihan
        // ==========================================
        if (!empty($kodeTagihanFilter)) {
            $query->whereHas('tagihanMurids.pengaturanTagihan', function ($q) use ($kodeTagihanFilter, $tahunPelajaranId) {
                $q->where('kode_tagihan', $kodeTagihanFilter)
                    ->where('tahun_pelajaran_id', $tahunPelajaranId);
            });
        }

        // ==========================================
        // FILTER 3: Berdasarkan Ruangan
        // ==========================================
        if ($request->filled('ruangan_id')) {
            $ruanganTerpilih = Ruangan::find($request->ruangan_id);

            $query->whereHas('tagihanMurids', function ($q) use ($request) {
                $q->where('ruangan_id', $request->ruangan_id);
            });
        }

        // 4. Kalkulasi Ringkasan Kas
        $totalPendapatan = (clone $query)->sum('total_nominal');
        $totalTransaksi = (clone $query)->count();

        // 5. Eksekusi Ambil Data
        $laporans = $query->orderBy('tanggal_bayar', 'desc')->orderBy('id', 'desc')->get();

        // 6. Kembalikan ke View dengan Semua Variabel yang Dibutuhkan Frontend
        return view('pembayaran-ujian.laporan-pembayaran', compact(
            'laporans',
            'totalPendapatan',
            'totalTransaksi',
            'startDate',
            'endDate',
            'jenisBiaya',
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganTerpilih',
            'daftarKodeTagihan',
            'kodeTagihanFilter'
        ));
    }

    /**
     * Dashboard Laporan Pembayaran Ujian Berdasarkan Kode Tagihan
     */
    public function dashboard(Request $request)
    {
        $payload = $this->buildDashboardData($request);
        return view('pembayaran-ujian.dashboard-kode-tagihan', $payload);
    }

    /**
     * Cetak Rekap Dashboard Pembayaran Ujian Berdasarkan Kode Tagihan (A4)
     */
    public function cetakDashboard(Request $request)
    {
        $payload = $this->buildDashboardData($request);
        $payload['pengasuh'] = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $payload['bendahara'] = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Bendahara')
            ?? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris Jenderal');

        return view('cetak-baru.cetak_laporan_pembayaran_ujian_kode', $payload);
    }

    /**
     * Helper penyusun data agregasi pembayaran ujian berdasarkan kode_tagihan
     */
    private function buildDashboardData(Request $request): array
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;
        $tahunPelajaran = $daftarTahun->firstWhere('id', $tahunPelajaranId);

        $daftarRuangan = Ruangan::with(['level.tingkat', 'waliRuangan'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $allowedRuanganIds = $daftarRuangan->pluck('id')->toArray();
        $selectedRuanganId = $request->filled('ruangan_id') ? (int) $request->ruangan_id : null;
        $ruanganTerpilih = $selectedRuanganId ? $daftarRuangan->firstWhere('id', $selectedRuanganId) : null;

        // Ambil seluruh master pengaturan tagihan ujian reguler (exclude IMNI, SPP, Syahriyah, wali_murid)
        $masterTagihans = PengaturanTagihan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where(function ($q) {
                $q->whereNull('sasaran')->orWhere('sasaran', 'murid');
            })
            ->where('kode_tagihan', '!=', 'IMNI')
            ->where('kode_tagihan', 'NOT LIKE', '%IMNI%')
            ->where('nama_tagihan', 'NOT LIKE', '%IMNI%')
            ->where('kode_tagihan', '!=', 'SPP')
            ->where('nama_tagihan', 'NOT LIKE', '%SPP%')
            ->where('nama_tagihan', 'NOT LIKE', '%Syahriyah%')
            ->orderBy('kode_tagihan', 'asc')
            ->orderBy('level_id', 'asc')
            ->get();

        // Daftar kode tagihan unik beserta nama dasar (menghapus akhiran " - <Nama Level>")
        $daftarKodeTagihan = $masterTagihans->groupBy('kode_tagihan')->map(function ($group, $kode) {
            $first = $group->first();
            $namaDasar = $first->nama_tagihan ?? $kode;
            if ($first->level && !empty($first->level->nama_level)) {
                $suffix = ' - ' . $first->level->nama_level;
                if (str_ends_with($namaDasar, $suffix)) {
                    $namaDasar = trim(substr($namaDasar, 0, -strlen($suffix)));
                }
            }

            return (object) [
                'kode_tagihan'     => $kode,
                'nama_tagihan'     => $namaDasar,
                'jumlah_level'     => $group->pluck('level_id')->filter()->unique()->count(),
                'pengaturan_ids'   => $group->pluck('id')->toArray(),
                'nominal_min'      => (float) $group->min('nominal'),
                'nominal_max'      => (float) $group->max('nominal'),
                'pengaturan_by_lv' => $group->keyBy('level_id'),
            ];
        })->values();

        $selectedKode = $request->input('kode_tagihan');
        if ($selectedKode && !$daftarKodeTagihan->contains('kode_tagihan', $selectedKode)) {
            $selectedKode = null;
        }
        $kodeTerpilihObj = $selectedKode ? $daftarKodeTagihan->firstWhere('kode_tagihan', $selectedKode) : null;

        // Ambil seluruh tagihan murid untuk master ujian di tahun pelajaran & ruangan yang diizinkan
        $masterIdsAll = $masterTagihans->pluck('id')->toArray();
        $targetRuanganIds = $selectedRuanganId && in_array($selectedRuanganId, $allowedRuanganIds)
            ? [$selectedRuanganId]
            : $allowedRuanganIds;

        $allTagihans = collect();
        if (!empty($masterIdsAll) && !empty($targetRuanganIds)) {
            $allTagihans = TagihanMurid::with(['pengaturanTagihan', 'ruangan.level.tingkat'])
                ->whereIn('pengaturan_tagihan_id', $masterIdsAll)
                ->whereIn('ruangan_id', $targetRuanganIds)
                ->where(function ($q) {
                    $q->where('status_bayar', 'Lunas')
                        ->orWhereHas('murid', function ($mq) {
                            $mq->where('status', 'Aktif');
                        });
                })
                ->get();
        }

        // 1. Rekapitulasi Per Kode Tagihan (selalu tampilkan semua kode agar mudah dibandingkan)
        $rekapPerKode = $daftarKodeTagihan->map(function ($kodeObj) use ($allTagihans) {
            $items = $allTagihans->whereIn('pengaturan_tagihan_id', $kodeObj->pengaturan_ids);

            $totalCount = $items->count();
            $lunasItems = $items->where('status_bayar', 'Lunas');
            $belumItems = $items->where('status_bayar', 'Belum Lunas');
            $donaturItems = $items->where('status_bayar', 'Ditanggung Donatur');

            $lunasCount = $lunasItems->count();
            $belumCount = $belumItems->count();
            $donaturCount = $donaturItems->count();

            $targetNominal = (float) $items->sum('nominal_tagihan');
            $terbayarNominal = (float) $lunasItems->sum('nominal_tagihan');
            $tunggakanNominal = (float) $belumItems->sum('nominal_tagihan');
            $donaturNominal = (float) $donaturItems->sum('nominal_tagihan');

            $persenLunas = $totalCount > 0 ? round(($lunasCount / $totalCount) * 100, 1) : 0;
            $persenNominal = $targetNominal > 0 ? round(($terbayarNominal / $targetNominal) * 100, 1) : 0;

            return (object) [
                'kode_tagihan'          => $kodeObj->kode_tagihan,
                'nama_tagihan'          => $kodeObj->nama_tagihan,
                'jumlah_level'          => $kodeObj->jumlah_level,
                'nominal_min'           => $kodeObj->nominal_min,
                'nominal_max'           => $kodeObj->nominal_max,
                'total_count'           => $totalCount,
                'lunas_count'           => $lunasCount,
                'belum_lunas_count'     => $belumCount,
                'donatur_count'         => $donaturCount,
                'target_nominal'        => $targetNominal,
                'terbayar_nominal'      => $terbayarNominal,
                'tunggakan_nominal'     => $tunggakanNominal,
                'donatur_nominal'       => $donaturNominal,
                'persen_lunas'          => $persenLunas,
                'persen_nominal'        => $persenNominal,
            ];
        });

        // 2. Data Terfilter (untuk KPI Utama, Breakdown Per Ruangan, dan Breakdown Per Level)
        $filteredTagihans = $selectedKode && $kodeTerpilihObj
            ? $allTagihans->whereIn('pengaturan_tagihan_id', $kodeTerpilihObj->pengaturan_ids)
            : $allTagihans;

        $totalCount = $filteredTagihans->count();
        $lunasItems = $filteredTagihans->where('status_bayar', 'Lunas');
        $belumItems = $filteredTagihans->where('status_bayar', 'Belum Lunas');
        $donaturItems = $filteredTagihans->where('status_bayar', 'Ditanggung Donatur');

        $targetNominal = (float) $filteredTagihans->sum('nominal_tagihan');
        $terbayarNominal = (float) $lunasItems->sum('nominal_tagihan');
        $tunggakanNominal = (float) $belumItems->sum('nominal_tagihan');
        $donaturNominal = (float) $donaturItems->sum('nominal_tagihan');

        $summary = (object) [
            'total_kode'        => $selectedKode ? 1 : $daftarKodeTagihan->count(),
            'total_count'       => $totalCount,
            'lunas_count'       => $lunasItems->count(),
            'belum_lunas_count' => $belumItems->count(),
            'donatur_count'     => $donaturItems->count(),
            'target_nominal'    => $targetNominal,
            'terbayar_nominal'  => $terbayarNominal,
            'tunggakan_nominal' => $tunggakanNominal,
            'donatur_nominal'   => $donaturNominal,
            'persen_lunas'      => $totalCount > 0 ? round(($lunasItems->count() / $totalCount) * 100, 1) : 0,
            'persen_nominal'    => $targetNominal > 0 ? round(($terbayarNominal / $targetNominal) * 100, 1) : 0,
        ];

        // 3. Rincian Per Ruangan
        $ruangansForBreakdown = $selectedRuanganId
            ? $daftarRuangan->where('id', $selectedRuanganId)
            : $daftarRuangan;

        $rekapPerRuangan = $ruangansForBreakdown->map(function ($ruangan) use ($filteredTagihans, $kodeTerpilihObj) {
            $items = $filteredTagihans->where('ruangan_id', $ruangan->id);
            if ($items->isEmpty()) {
                return null;
            }

            $total = $items->count();
            $lunas = $items->where('status_bayar', 'Lunas');
            $belum = $items->where('status_bayar', 'Belum Lunas');
            $donatur = $items->where('status_bayar', 'Ditanggung Donatur');

            $targetNom = (float) $items->sum('nominal_tagihan');
            $terbayarNom = (float) $lunas->sum('nominal_tagihan');
            $tunggakanNom = (float) $belum->sum('nominal_tagihan');
            $donaturNom = (float) $donatur->sum('nominal_tagihan');

            // Cari pengaturan_tagihan_id agar tombol cepat Kasir langsung membuka tagihan yang sesuai
            $pengaturanIdForLink = $kodeTerpilihObj?->pengaturan_by_lv?->get($ruangan->level_id)?->id
                ?? $items->first()?->pengaturan_tagihan_id;

            return (object) [
                'ruangan_id'            => $ruangan->id,
                'nama_ruangan'          => $ruangan->nama_ruangan,
                'level_id'              => $ruangan->level_id,
                'nama_level'            => $ruangan->level->nama_level ?? '-',
                'tingkat_id'            => $ruangan->level->tingkat_id ?? null,
                'nama_tingkat'          => $ruangan->level->tingkat->nama_tingkat ?? '-',
                'wali_ruangan'          => $ruangan->waliRuangan->nama_lengkap ?? '-',
                'pengaturan_tagihan_id' => $pengaturanIdForLink,
                'total_count'           => $total,
                'lunas_count'           => $lunas->count(),
                'belum_lunas_count'     => $belum->count(),
                'donatur_count'         => $donatur->count(),
                'target_nominal'        => $targetNom,
                'terbayar_nominal'      => $terbayarNom,
                'tunggakan_nominal'     => $tunggakanNom,
                'donatur_nominal'       => $donaturNom,
                'persen_lunas'          => $total > 0 ? round(($lunas->count() / $total) * 100, 1) : 0,
                'persen_nominal'        => $targetNom > 0 ? round(($terbayarNom / $targetNom) * 100, 1) : 0,
            ];
        })->filter()->values();

        // 4. Rincian Per Level
        $rekapPerLevel = $rekapPerRuangan->groupBy('level_id')->map(function ($rows) {
            $first = $rows->first();
            $total = $rows->sum('total_count');
            $lunas = $rows->sum('lunas_count');
            $belum = $rows->sum('belum_lunas_count');
            $donatur = $rows->sum('donatur_count');
            $targetNom = (float) $rows->sum('target_nominal');
            $terbayarNom = (float) $rows->sum('terbayar_nominal');
            $tunggakanNom = (float) $rows->sum('tunggakan_nominal');
            $donaturNom = (float) $rows->sum('donatur_nominal');

            return (object) [
                'level_id'          => $first->level_id,
                'nama_level'        => $first->nama_level,
                'nama_tingkat'      => $first->nama_tingkat,
                'jumlah_ruangan'    => $rows->count(),
                'total_count'       => $total,
                'lunas_count'       => $lunas,
                'belum_lunas_count' => $belum,
                'donatur_count'     => $donatur,
                'target_nominal'    => $targetNom,
                'terbayar_nominal'  => $terbayarNom,
                'tunggakan_nominal' => $tunggakanNom,
                'donatur_nominal'   => $donaturNom,
                'persen_lunas'      => $total > 0 ? round(($lunas / $total) * 100, 1) : 0,
                'persen_nominal'    => $targetNom > 0 ? round(($terbayarNom / $targetNom) * 100, 1) : 0,
            ];
        })->values();

        return compact(
            'daftarTahun',
            'tahunPelajaranId',
            'tahunPelajaran',
            'daftarRuangan',
            'selectedRuanganId',
            'ruanganTerpilih',
            'daftarKodeTagihan',
            'selectedKode',
            'kodeTerpilihObj',
            'rekapPerKode',
            'summary',
            'rekapPerRuangan',
            'rekapPerLevel'
        );
    }

    public function cetakKwitansi($id)
    {
        $pembayaran = PembayaranTagihan::with([
            'tagihanMurids.murid.waliMurid.kampung',
            'tagihanMurids.ruangan.tahunPelajaran',
            'tagihanMurids.bulanHijriyah',
            'tagihanMurids.semester',
        ])->findOrFail($id);

        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $bendahara = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Bendahara') ?? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris Jenderal');

        return view('cetak-baru.cetak_kwitansi', compact('pembayaran', 'pengasuh', 'bendahara'));
    }
}
