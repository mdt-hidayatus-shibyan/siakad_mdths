<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Models\BulanHijriyah;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\ReferensiPelanggaran;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Repositories\MuridRuanganRepository;
use App\Services\PelanggaranMuridService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PelanggaranMuridController extends Controller
{
    protected $muridRuanganRepo;
    protected $pelanggaranService;

    public function __construct(MuridRuanganRepository $muridRuanganRepo, PelanggaranMuridService $pelanggaranService)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
        $this->pelanggaranService = $pelanggaranService;
    }
    public function index(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;

        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $referensiPelanggarans = ReferensiPelanggaran::orderBy('id')->get();

        $murids = collect();
        $riwayatPelanggaran = collect();
        $tahun_pelajaran_id = null;
        $semester_id = null; // TAMBAHAN: Variabel semester

        if ($tanggal && $ruangan_id) {
            $bulan = BulanHijriyah::where('tanggal_mulai_masehi', '<=', $tanggal)
                ->where('tanggal_selesai_masehi', '>=', $tanggal)
                ->first();

            if ($bulan) {
                $tahun_pelajaran_id = $bulan->tahun_pelajaran_id;
                // Cari semester aktif di tahun tersebut
                $semesterAktif = Semester::where('tahun_pelajaran_id', $tahun_pelajaran_id)
                    ->where('is_active', true)->first();
                $semester_id = $semesterAktif ? $semesterAktif->id : null;
            } else {
                $semesterAktif = Semester::where('is_active', 1)->first();
                $tahun_pelajaran_id = $semesterAktif ? $semesterAktif->tahun_pelajaran_id : null;
                $semester_id = $semesterAktif ? $semesterAktif->id : null;
            }

            if ($tahun_pelajaran_id) {
                $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id);
                $riwayatPelanggaran = PelanggaranMurid::with(['murid', 'referensiPelanggaran', 'penginput'])
                    ->where('tanggal', $tanggal)
                    ->where('ruangan_id', $ruangan_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('pelanggaran-murid.index', compact(
            'tanggal',
            'ruangan_id',
            'ruangans',
            'murids',
            'referensiPelanggarans',
            'riwayatPelanggaran',
            'tahun_pelajaran_id',
            'semester_id' // Kirim ke View agar bisa diinput ke form hidden
        ));
    }

    /**
     * Riwayat Pelanggaran Murid Harian untuk Semua Ruangan / Filter Tertentu
     */
    public function riwayatHarian(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;
        $kategori = $request->kategori;

        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $referensiKategoris = ReferensiPelanggaran::select('kategori')->distinct()->whereNotNull('kategori')->pluck('kategori');

        // Query Pelanggaran pada tanggal tersebut
        $query = PelanggaranMurid::with([
            'murid',
            'ruangan.level',
            'referensiPelanggaran',
            'penginput',
            'tahunPelajaran',
            'semester'
        ])
            ->where('tanggal', $tanggal);

        if ($ruangan_id) {
            $query->where('ruangan_id', $ruangan_id);
        } else {
            $query->whereIn('ruangan_id', $ruangans->pluck('id'));
        }

        if ($kategori) {
            $query->whereHas('referensiPelanggaran', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        $riwayatPelanggaran = $query->orderBy('created_at', 'desc')->get();

        // Metrik Statistik Harian
        $totalKasus = $riwayatPelanggaran->count();
        $totalPoin = $riwayatPelanggaran->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));
        $totalMurid = $riwayatPelanggaran->pluck('murid_id')->unique()->count();
        $totalRuangan = $riwayatPelanggaran->pluck('ruangan_id')->unique()->count();

        // Rekap Kategori
        $rekapKategori = $riwayatPelanggaran->groupBy(fn($p) => $p->referensiPelanggaran->kategori ?? 'Umum')
            ->map(fn($group) => [
                'count' => $group->count(),
                'poin' => $group->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0))
            ]);

        return view('pelanggaran-murid.riwayat-harian', compact(
            'tanggal',
            'ruangan_id',
            'kategori',
            'ruangans',
            'referensiKategoris',
            'riwayatPelanggaran',
            'totalKasus',
            'totalPoin',
            'totalMurid',
            'totalRuangan',
            'rekapKategori'
        ));
    }

    /**
     * Export Riwayat Pelanggaran Harian ke CSV / Excel
     */
    public function exportRiwayatHarian(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;
        $kategori = $request->kategori;

        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();

        $query = PelanggaranMurid::with([
            'murid',
            'ruangan.level',
            'referensiPelanggaran',
            'penginput',
            'tahunPelajaran',
            'semester'
        ])
            ->where('tanggal', $tanggal);

        if ($ruangan_id) {
            $query->where('ruangan_id', $ruangan_id);
        } else {
            $query->whereIn('ruangan_id', $ruangans->pluck('id'));
        }

        if ($kategori) {
            $query->whereHas('referensiPelanggaran', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        $pelanggarans = $query->orderBy('created_at', 'desc')->get();

        $ruanganLabel = $ruangan_id ? (Ruangan::find($ruangan_id)?->nama_ruangan ?? 'Ruangan') : 'Semua_Ruangan';
        $fileName = "Riwayat_Pelanggaran_Murid_{$ruanganLabel}_{$tanggal}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($pelanggarans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'Waktu', 'Ruangan', 'Level', 'NISM', 'Nama Murid', 'Kategori Kasus', 'Nama Pelanggaran', 'Poin Sanksi', 'Keterangan', 'Petugas Penginput']);

            $no = 1;
            foreach ($pelanggarans as $p) {
                fputcsv($file, [
                    $no++,
                    $p->tanggal,
                    $p->created_at ? $p->created_at->format('H:i') : '-',
                    $p->ruangan->nama_ruangan ?? '-',
                    $p->ruangan?->level?->nama_level ?? '-',
                    $p->murid->nism ?? '-',
                    $p->murid->nama_lengkap ?? '-',
                    $p->referensiPelanggaran->kategori ?? '-',
                    $p->referensiPelanggaran->nama_pelanggaran ?? '-',
                    $p->referensiPelanggaran->poin ?? 0,
                    $p->keterangan ?: '-',
                    $p->penginput->name ?? 'Sistem'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function storeHarian(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'ruangan_id' => 'required|exists:ruangans,id',
            'tahun_pelajaran_id' => 'required',
            'semester_id' => 'required', // TAMBAHAN: Validasi semester
            'murid_id' => 'required|exists:murids,id',
            'referensi_pelanggaran_ids' => 'required|array',
        ]);

        foreach ($request->referensi_pelanggaran_ids as $ref_id) {
            PelanggaranMurid::create([
                'tanggal' => $request->tanggal,
                'ruangan_id' => $request->ruangan_id,
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'semester_id' => $request->semester_id, // TAMBAHAN: Simpan ke DB
                'murid_id' => $request->murid_id,
                'referensi_pelanggaran_id' => $ref_id,
                'keterangan' => $request->keterangan,
                'diinput_oleh_id' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Data pelanggaran berhasil dicatat ke dalam buku kasus!');
    }

    public function destroyHarian($id)
    {
        $pelanggaran = PelanggaranMurid::findOrFail($id);
        $pelanggaran->delete();

        return back()->with('success', 'Catatan pelanggaran berhasil dihapus!');
    }

    public function massal(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;

        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $referensiPelanggarans = ReferensiPelanggaran::orderBy('id')->get();

        $murids = collect();
        $tahun_pelajaran_id = null;
        $semester_id = null;

        if ($ruangan_id && $tanggal) {
            $bulan = BulanHijriyah::where('tanggal_mulai_masehi', '<=', $tanggal)
                ->where('tanggal_selesai_masehi', '>=', $tanggal)
                ->first();

            if ($bulan) {
                $tahun_pelajaran_id = $bulan->tahun_pelajaran_id;
                // Cari semester aktif di tahun tersebut
                $semesterAktif = Semester::where('tahun_pelajaran_id', $tahun_pelajaran_id)
                    ->where('is_active', true)->first();
                $semester_id = $semesterAktif ? $semesterAktif->id : null;
            } else {
                $semesterAktif = Semester::where('is_active', 1)->first();
                $tahun_pelajaran_id = $semesterAktif ? $semesterAktif->tahun_pelajaran_id : null;
                $semester_id = $semesterAktif ? $semesterAktif->id : null;
            }

            if ($tahun_pelajaran_id) {
                $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id);
            }
        }

        return view('pelanggaran-murid.massal', compact(
            'tanggal',
            'ruangan_id',
            'ruangans',
            'murids',
            'referensiPelanggarans',
            'tahun_pelajaran_id',
            'semester_id'
        ));
    }

    public function storeMassal(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'ruangan_id' => 'required|exists:ruangans,id',
            'tahun_pelajaran_id' => 'required',
            'semester_id' => 'required', // TAMBAHAN
            'murid_ids' => 'required|array|min:1',
            'referensi_pelanggaran_ids' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->murid_ids as $murid_id) {
                foreach ($request->referensi_pelanggaran_ids as $ref_id) {
                    PelanggaranMurid::create([
                        'tanggal' => $request->tanggal,
                        'ruangan_id' => $request->ruangan_id,
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'semester_id' => $request->semester_id,
                        'murid_id' => $murid_id,
                        'referensi_pelanggaran_id' => $ref_id,
                        'keterangan' => $request->keterangan,
                        'diinput_oleh_id' => Auth::id(),
                    ]);
                }
            }
        });

        return redirect()->route('pelanggaran-murid.index', ['ruangan_id' => $request->ruangan_id, 'tanggal' => $request->tanggal])
            ->with('success', count($request->murid_ids) . ' murid berhasil dijatuhi sanksi pelanggaran secara kolektif!');
    }

    public function adminMode(Request $request)
    {
        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $bulans = BulanHijriyah::orderBy('urutan')->get();

        $ruangan_id = $request->ruangan_id;
        $bulan_id = $request->bulan_id;

        $existingData = collect();
        $tahun_pelajaran_id = null;
        $semester_id = null;

        $semesterAktif = Semester::where('is_active', 1)->first();
        if ($semesterAktif) {
            $tahun_pelajaran_id = $semesterAktif->tahun_pelajaran_id;
            $semester_id = $semesterAktif->id;
        }

        $allMurids = collect();
        if ($ruangan_id && $tahun_pelajaran_id) {
            $allMurids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id)->map(function ($m) {
                $rActive = $m->ruangans->first();
                return [
                    'nism' => $m->nism,
                    'nama' => $m->nama_lengkap,
                    'ruangan_id' => $rActive ? $rActive->id : null,
                    'nama_ruangan' => $rActive ? $rActive->nama_ruangan : '-'
                ];
            });
        }

        $allPelanggarans = ReferensiPelanggaran::all()->map(function ($r) {
            return [
                'id' => $r->id,
                'kode' => $r->id,
                'let' => $r->nama_pelanggaran,
                'ket' => $r->nama_pelanggaran,
                'poin' => $r->poin
            ];
        });

        if ($ruangan_id && $bulan_id) {
            $bulanTerpilih = BulanHijriyah::findOrFail($bulan_id);
            $existingData = PelanggaranMurid::with(['murid', 'referensiPelanggaran'])
                ->where('ruangan_id', $ruangan_id)
                // Filter tanggal masehi sudah sangat spesifik untuk mengambil kasus di bulan itu
                ->whereBetween('tanggal', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'tanggal' => $p->tanggal,
                        'nism' => $p->murid->nism,
                        'nama_murid' => $p->murid->nama_lengkap,
                        'ruangan_id' => $p->ruangan_id,
                        'nama_ruangan' => $p->ruangan->nama_ruangan,
                        'referensi_pelanggaran_id' => $p->referensi_pelanggaran_id,
                        'nama_pelanggaran' => $p->referensiPelanggaran->nama_pelanggaran,
                        'poin' => $p->referensiPelanggaran->poin,
                        'keterangan' => $p->keterangan
                    ];
                });
        }

        return view('pelanggaran-murid.admin', compact(
            'ruangans',
            'bulans',
            'ruangan_id',
            'bulan_id',
            'existingData',
            'allMurids',
            'allPelanggarans',
            'tahun_pelajaran_id',
            'semester_id' // TAMBAHAN
        ));
    }

    public function syncAdminMode(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required',
            'tahun_pelajaran_id' => 'required',
            'semester_id' => 'required',
            'data_to_save' => 'nullable|array',
            'data_to_delete' => 'nullable|array',
        ]);

        try {
            if ($request->has('data_to_delete') && count($request->data_to_delete) > 0) {
                PelanggaranMurid::whereIn('id', $request->data_to_delete)->delete();
            }

            if ($request->has('data_to_save') && count($request->data_to_save) > 0) {
                $this->pelanggaranService->syncAdminMode(
                    $request->data_to_save,
                    $request->ruangan_id,
                    $request->tahun_pelajaran_id,
                    $request->semester_id,
                    Auth::id()
                );
            }

            return response()->json(['status' => 'success', 'message' => 'Sinkronisasi buku kasus berhasil diselesaikan!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }

    public function rekap(Request $request)
    {
        $ruangan_id = $request->ruangan_id;
        $semester_id = $request->semester_id;
        $bulan_id = $request->bulan_id;

        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $semesters = Semester::with('tahunPelajaran')->get();

        $bulans = collect();
        if ($semester_id) {
            $semesterDicari = Semester::find($semester_id);

            if ($semesterDicari && $semesterDicari->tanggal_mulai && $semesterDicari->tanggal_selesai) {
                $bulans = BulanHijriyah::where('tahun_pelajaran_id', $semesterDicari->tahun_pelajaran_id)
                    ->where('tanggal_selesai_masehi', '>=', $semesterDicari->tanggal_mulai)
                    ->where('tanggal_mulai_masehi', '<=', $semesterDicari->tanggal_selesai)
                    ->orderBy('urutan')
                    ->get();
            }
        }

        $rekap = [];
        $ruanganTerpilih = null;
        $bulanTerpilih = null;

        if ($ruangan_id && $semester_id) {
            $rekapData = $this->pelanggaranService->hitungRekapPelanggaran($ruangan_id, $semester_id, $bulan_id);
            $rekap = $rekapData['rekap'];
            $ruanganTerpilih = $rekapData['ruanganTerpilih'];
            $bulanTerpilih = $rekapData['bulanTerpilih'];

            usort($rekap, function ($a, $b) {
                return $b['total_poin'] <=> $a['total_poin'];
            });
        }

        return view('pelanggaran-murid.rekap', compact('ruangans', 'semesters', 'bulans', 'ruangan_id', 'semester_id', 'bulan_id', 'rekap', 'ruanganTerpilih', 'bulanTerpilih'));
    }

    public function exportExcel(Request $request)
    {
        $ruangan_id = $request->ruangan_id;
        $semester_id = $request->semester_id;
        $bulan_id = $request->bulan_id;

        if (!$ruangan_id || !$semester_id) {
            return back()->with('error', 'Pilih Ruangan dan Semester terlebih dahulu!');
        }

        $ruanganTerpilih = Ruangan::find($ruangan_id);
        $semesterTerpilih = Semester::find($semester_id);

        $rekap = [];
        $murids = Murid::whereHas('ruangans', function ($q) use ($ruangan_id, $semesterTerpilih) {
            $q->where('ruangans.id', $ruangan_id)->where('murid_ruangans.tahun_pelajaran_id', $semesterTerpilih->tahun_pelajaran_id);
        })->get();

        foreach ($murids as $m) {
            $rekap[$m->id] = ['nism' => $m->nism, 'nama' => $m->nama_lengkap, 'total_kasus' => 0, 'total_poin' => 0];
        }

        // OPTIMASI QUERY: Sama persis dengan rekap
        $query = PelanggaranMurid::with('referensiPelanggaran')
            ->where('ruangan_id', $ruangan_id)
            ->where('semester_id', $semester_id);

        $periode = "Semester_" . $semesterTerpilih->semester;

        if ($bulan_id) {
            $bulanTerpilih = BulanHijriyah::find($bulan_id);
            $query->whereBetween('tanggal', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi]);
            $periode = str_replace(' ', '_', $bulanTerpilih->nama_bulan);
        }
        // Blok pencarian $bulansInSemester dihapus karena sudah diwakili ->where('semester_id')

        $pelanggarans = $query->get();
        foreach ($pelanggarans as $p) {
            if (isset($rekap[$p->murid_id])) {
                $rekap[$p->murid_id]['total_kasus']++;
                $rekap[$p->murid_id]['total_poin'] += $p->referensiPelanggaran->poin;
            }
        }

        usort($rekap, function ($a, $b) {
            return $b['total_poin'] <=> $a['total_poin'];
        });

        $fileName = "Rekap_Poin_Pelanggaran_" . str_replace(' ', '_', $ruanganTerpilih->nama_ruangan) . "_{$periode}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($rekap) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Peringkat', 'NISM', 'Nama Murid', 'Total Kejadian/Kasus', 'Akumulasi Poin Sanksi']);

            $no = 1;
            foreach ($rekap as $row) {
                fputcsv($file, [$no++, $row['nism'], $row['nama'], $row['total_kasus'], $row['total_poin']]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
