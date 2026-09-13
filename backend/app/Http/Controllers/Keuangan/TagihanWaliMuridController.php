<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Kampung;
use App\Models\Kepengurusan\Pengurus;
use App\Models\PembayaranTagihan;
use App\Models\PengaturanTagihan;
use App\Models\TagihanWaliMurid;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use App\Models\Tabungan\Tabungan;
use App\Models\Tabungan\TransaksiTabungan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagihanWaliMuridController extends Controller
{
    /**
     * Redirect Index ke Kasir
     */
    public function index(Request $request)
    {
        return redirect()->route('tagihan-wali.kasir', $request->query());
    }

    /**
     * Submenu 1: Penerbitan Tagihan Wali Murid (KK Aktif)
     */
    public function terbitkanIndex(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $selectedKampungId = $request->kampung_id ?? ($daftarKampung->first()?->id ?? null);

        $masterTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('sasaran', 'wali_murid')
            ->orderBy('id', 'asc')
            ->get();

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($masterTagihans->first()?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId);

        $queryWali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahunPelajaranId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    })
                    ->with(['ruangans' => function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    }]);
            },
            'tagihanWaliMurids' => function ($q) use ($tahunPelajaranId, $selectedMasterId) {
                $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->when($selectedMasterId, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMasterId))
                    ->with('pembayaranTagihan');
            }
        ])
            ->where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunPelajaranId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    });
            });

        if ($selectedKampungId) {
            $queryWali->where('kampung_id', $selectedKampungId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $queryWali->where(function ($q) use ($search) {
                $q->where('nama_kepala_keluarga', 'LIKE', "%{$search}%")
                    ->orWhere('no_registrasi', 'LIKE', "%{$search}%")
                    ->orWhere('no_kk', 'LIKE', "%{$search}%")
                    ->orWhere('no_hp', 'LIKE', "%{$search}%")
                    ->orWhereHas('murids', function ($mq) use ($search) {
                        $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                            ->orWhere('nism', 'LIKE', "%{$search}%");
                    });
            });
        }

        $walis = $queryWali->get();

        $nominalTarif = $selectedMaster?->nominal ?? 0;
        $totalWaliAktif = $walis->count();
        $totalSudahTerbitCount = 0;
        $totalBelumTerbitCount = 0;

        foreach ($walis as $wali) {
            $tagihan = $wali->tagihanWaliMurids->first();
            $wali->current_tagihan = $tagihan;
            if ($tagihan) {
                $totalSudahTerbitCount++;
            } else {
                $totalBelumTerbitCount++;
            }
        }

        // Filter Status Terbit
        if ($request->filled('status_terbit')) {
            if ($request->status_terbit === 'Belum Terbit') {
                $walis = $walis->filter(fn($w) => !$w->current_tagihan);
            } elseif ($request->status_terbit === 'Sudah Terbit') {
                $walis = $walis->filter(fn($w) => (bool) $w->current_tagihan);
            }
        }

        return view('tagihan-wali.terbitkan', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarKampung',
            'selectedKampungId',
            'masterTagihans',
            'selectedMasterId',
            'selectedMaster',
            'walis',
            'totalWaliAktif',
            'totalSudahTerbitCount',
            'totalBelumTerbitCount',
            'nominalTarif'
        ));
    }

    /**
     * Submenu 2A: Kasir Reguler / Scan No Registrasi & No KK
     */
    public function kasir(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $waliTerpilih = null;
        $semuaTagihan = collect();
        $availableTabungans = collect();
        $searchKey = trim($request->search_wali ?? $request->search ?? $request->search_nism ?? '');

        if (!empty($searchKey)) {
            $waliTerpilih = WaliMurid::with([
                'kampung',
                'murids' => function ($q) use ($tahunPelajaranId) {
                    $q->where('status', 'Aktif')
                        ->with([
                            'ruangans' => function ($rq) use ($tahunPelajaranId) {
                                if ($tahunPelajaranId) {
                                    $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                                }
                            },
                            'tabungans' => function ($tq) {
                                $tq->where('status', 'Aktif');
                            }
                        ]);
                },
                'tagihanWaliMurids' => function ($q) use ($tahunPelajaranId) {
                    $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                        ->with(['pengaturanTagihan', 'pembayaranTagihan']);
                }
            ])
                ->where('is_active', true)
                ->where(function ($q) use ($searchKey) {
                    $q->where('no_registrasi', $searchKey)
                        ->orWhere('no_kk', $searchKey)
                        ->orWhere('nama_kepala_keluarga', 'LIKE', "%{$searchKey}%")
                        ->orWhere('no_hp', $searchKey)
                        ->orWhereHas('murids', function ($mq) use ($searchKey) {
                            $mq->where('nism', $searchKey)
                                ->orWhere('nama_lengkap', 'LIKE', "%{$searchKey}%");
                        });
                })
                ->first();

            if ($waliTerpilih) {
                // Tarik semua tagihan KK tahun ini
                $semuaTagihan = $waliTerpilih->tagihanWaliMurids;

                // Rekap daftar rekening tabungan santri dari KK tersebut
                $availableTabungans = $waliTerpilih->murids->flatMap(function ($anak) {
                    return $anak->tabungans->map(function ($tab) use ($anak) {
                        return [
                            'id'             => $tab->id,
                            'nomor_rekening' => $tab->nomor_rekening,
                            'nama_santri'    => $anak->nama_lengkap,
                            'nama_rekening'  => $tab->nama_rekening,
                            'saldo'          => (float) $tab->saldo,
                            'saldo_format'   => 'Rp ' . number_format($tab->saldo, 0, ',', '.'),
                        ];
                    });
                })->values();
            } else {
                session()->now('error', 'Wali Murid / No. Registrasi / No. KK "' . $searchKey . '" tidak ditemukan.');
            }
        }

        // Riwayat Transaksi Hari Ini untuk Kasir
        $riwayatHariIni = PembayaranTagihan::whereDate('tanggal_bayar', Carbon::today())
            ->where('tipe_pembayar', 'Wali Murid')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('tagihan-wali.kasir', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'waliTerpilih',
            'semuaTagihan',
            'availableTabungans',
            'riwayatHariIni',
            'searchKey'
        ));
    }

    /**
     * Submenu 2B: Kasir Leger Matriks Per Dusun
     */
    public function kasirLeger(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $selectedKampungId = $request->kampung_id ?? ($daftarKampung->first()?->id ?? null);
        $kampungTerpilih = $selectedKampungId ? Kampung::find($selectedKampungId) : null;

        $masterTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('sasaran', 'wali_murid')
            ->orderBy('id', 'asc')
            ->get();

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($masterTagihans->first()?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId);

        $walis = collect();

        if ($selectedKampungId) {
            $queryWali = WaliMurid::with([
                'kampung',
                'murids' => function ($q) use ($tahunPelajaranId) {
                    $q->where('status', 'Aktif')
                        ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                            if ($tahunPelajaranId) {
                                $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                            }
                        })
                        ->with([
                            'ruangans' => function ($rq) use ($tahunPelajaranId) {
                                if ($tahunPelajaranId) {
                                    $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                                }
                            },
                            'tabungans' => function ($tq) {
                                $tq->where('status', 'Aktif');
                            }
                        ]);
                },
                'tagihanWaliMurids' => function ($q) use ($tahunPelajaranId, $selectedMasterId) {
                    $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                        ->when($selectedMasterId, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMasterId))
                        ->with('pembayaranTagihan');
                }
            ])
                ->where('is_active', true)
                ->where('kampung_id', $selectedKampungId)
                ->whereHas('murids', function ($q) use ($tahunPelajaranId) {
                    $q->where('status', 'Aktif')
                        ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                            if ($tahunPelajaranId) {
                                $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                            }
                        });
                });

            if ($request->filled('search')) {
                $search = trim($request->search);
                $queryWali->where(function ($q) use ($search) {
                    $q->where('nama_kepala_keluarga', 'LIKE', "%{$search}%")
                        ->orWhere('no_registrasi', 'LIKE', "%{$search}%")
                        ->orWhere('no_kk', 'LIKE', "%{$search}%")
                        ->orWhere('no_hp', 'LIKE', "%{$search}%")
                        ->orWhereHas('murids', function ($mq) use ($search) {
                            $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                ->orWhere('nism', 'LIKE', "%{$search}%");
                        });
                });
            }

            $walis = $queryWali->get();

            foreach ($walis as $wali) {
                $tagihan = $wali->tagihanWaliMurids->first();
                $wali->current_tagihan = $tagihan;

                $wali->available_tabungans = $wali->murids->flatMap(function ($anak) {
                    return $anak->tabungans->map(function ($tab) use ($anak) {
                        return [
                            'id'             => $tab->id,
                            'nomor_rekening' => $tab->nomor_rekening,
                            'nama_santri'    => $anak->nama_lengkap,
                            'nama_rekening'  => $tab->nama_rekening,
                            'saldo'          => (float) $tab->saldo,
                            'saldo_format'   => 'Rp ' . number_format($tab->saldo, 0, ',', '.'),
                        ];
                    });
                })->values();
            }
        }

        $nominalTarif = $selectedMaster?->nominal ?? 0;
        $totalLunasCount = $walis->filter(fn($w) => $w->current_tagihan && $w->current_tagihan->status_bayar === 'Lunas')->count();
        $totalBelumLunasCount = $walis->filter(fn($w) => $w->current_tagihan && $w->current_tagihan->status_bayar === 'Belum Lunas')->count();

        return view('tagihan-wali.kasir-leger', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarKampung',
            'selectedKampungId',
            'kampungTerpilih',
            'masterTagihans',
            'selectedMasterId',
            'selectedMaster',
            'walis',
            'nominalTarif',
            'totalLunasCount',
            'totalBelumLunasCount'
        ));
    }

    /**
     * Pelunasan Massal Tagihan Wali Murid via Leger
     */
    public function prosesLeger(Request $request)
    {
        $request->validate([
            'tagihan_ids'       => 'required|array|min:1',
            'tagihan_ids.*'     => 'exists:tagihan_wali_murids,id',
            'metode_pembayaran' => 'nullable|string',
            'tanggal_bayar'     => 'nullable|date',
            'catatan'           => 'nullable|string',
        ]);

        $metode = $request->metode_pembayaran ?? 'Tunai';
        $tanggalBayar = $request->tanggal_bayar ?? now();

        DB::beginTransaction();
        try {
            $tagihans = TagihanWaliMurid::with(['waliMurid', 'pengaturanTagihan'])
                ->whereIn('id', $request->tagihan_ids)
                ->where('status_bayar', '!=', 'Lunas')
                ->lockForUpdate()
                ->get();

            if ($tagihans->isEmpty()) {
                throw new \Exception("Semua tagihan yang dipilih sudah lunas atau tidak ditemukan.");
            }

            $totalProcessed = 0;
            $totalNominal = 0;

            foreach ($tagihans as $tgh) {
                $w = $tgh->waliMurid;
                $master = $tgh->pengaturanTagihan;

                $hari = Carbon::parse($tanggalBayar)->format('d');
                $bulan = Carbon::parse($tanggalBayar)->format('m');
                $tahun = Carbon::parse($tanggalBayar)->format('Y');

                $kodeTagihan = $master->kode_tagihan ?? 'WLI';
                $noReg = $w->no_registrasi ?? '0000';
                $randomCode = mt_rand(10000, 99999);
                $noKwitansi = 'TRX/' . $kodeTagihan . '/WLI/' . $noReg . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode;

                $pembayaran = PembayaranTagihan::create([
                    'no_transaksi'      => $noKwitansi,
                    'tanggal_bayar'     => $tanggalBayar,
                    'tipe_pembayar'     => 'Wali Murid',
                    'nama_pembayar'     => $w->nama_kepala_keluarga ?? 'Wali Murid',
                    'metode_pembayaran' => $metode,
                    'total_nominal'     => $tgh->nominal_tagihan,
                    'catatan'           => $request->catatan ?? "Pelunasan Kasir Leger {$master->nama_tagihan} a.n. {$w->nama_kepala_keluarga} (Reg: {$w->no_registrasi})"
                ]);

                $tgh->update([
                    'status_bayar'          => 'Lunas',
                    'pembayaran_tagihan_id' => $pembayaran->id,
                    'updated_at'            => now(),
                ]);

                $totalProcessed++;
                $totalNominal += (float) $tgh->nominal_tagihan;
            }

            DB::commit();

            return redirect()->back()->with('success', "Sukses! Sebanyak {$totalProcessed} tagihan Wali Murid (Total Rp " . number_format($totalNominal, 0, ',', '.') . ") berhasil dilunasi via Kasir Leger.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pelunasan kasir leger: ' . $e->getMessage());
        }
    }

    /**
     * Submenu 3: Laporan & Rekapitulasi Tagihan Wali Murid (KK)
     */
    public function laporan(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $selectedKampungId = $request->filled('kampung_id') && $request->kampung_id !== 'semua' ? $request->kampung_id : null;

        $masterTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('sasaran', 'wali_murid')
            ->orderBy('id', 'asc')
            ->get();

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($masterTagihans->first()?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId);

        $queryWali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahunPelajaranId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    })
                    ->with(['ruangans' => function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    }]);
            },
            'tagihanWaliMurids' => function ($q) use ($tahunPelajaranId, $selectedMasterId) {
                $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->when($selectedMasterId, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMasterId))
                    ->with('pembayaranTagihan');
            }
        ])
            ->where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunPelajaranId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    });
            })
            ->whereHas('tagihanWaliMurids', function ($q) use ($tahunPelajaranId, $selectedMasterId) {
                $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->when($selectedMasterId, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMasterId));
            });

        if ($selectedKampungId) {
            $queryWali->where('kampung_id', $selectedKampungId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $queryWali->where(function ($q) use ($search) {
                $q->where('nama_kepala_keluarga', 'LIKE', "%{$search}%")
                    ->orWhere('no_registrasi', 'LIKE', "%{$search}%")
                    ->orWhere('no_kk', 'LIKE', "%{$search}%")
                    ->orWhere('no_hp', 'LIKE', "%{$search}%")
                    ->orWhereHas('murids', function ($mq) use ($search) {
                        $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                            ->orWhere('nism', 'LIKE', "%{$search}%");
                    });
            });
        }

        $allWalis = $queryWali->get();

        $nominalTarif = $selectedMaster?->nominal ?? 0;
        $totalWaliAktif = $allWalis->count();
        $totalTargetNominal = $allWalis->sum(fn($w) => (float) ($w->tagihanWaliMurids->first()?->nominal_tagihan ?? $nominalTarif));
        $totalLunasNominal = 0;
        $totalLunasCount = 0;
        $totalBelumLunasCount = 0;

        foreach ($allWalis as $w) {
            $tagihan = $w->tagihanWaliMurids->first();
            $w->current_tagihan = $tagihan;
            if ($tagihan && $tagihan->status_bayar === 'Lunas') {
                $totalLunasNominal += (float) $tagihan->nominal_tagihan;
                $totalLunasCount++;
            } else {
                $totalBelumLunasCount++;
            }
        }

        $totalTunggakanNominal = max(0, $totalTargetNominal - $totalLunasNominal);
        $persenLunas = $totalTargetNominal > 0 ? round(($totalLunasNominal / $totalTargetNominal) * 100, 1) : 0;

        // Rekapitulasi Per Kampung / Dusun
        $rekapPerKampung = $daftarKampung->map(function ($k) use ($allWalis, $nominalTarif) {
            $walisInKampung = $allWalis->where('kampung_id', $k->id);
            $totalKK = $walisInKampung->count();
            $lunasCount = $walisInKampung->filter(fn($w) => $w->current_tagihan && $w->current_tagihan->status_bayar === 'Lunas')->count();
            $belumLunasCount = $totalKK - $lunasCount;
            $targetNominal = $walisInKampung->sum(fn($w) => (float) ($w->current_tagihan?->nominal_tagihan ?? $nominalTarif));
            $lunasNominal = $walisInKampung->reduce(function ($acc, $w) {
                return $acc + ($w->current_tagihan && $w->current_tagihan->status_bayar === 'Lunas' ? (float) $w->current_tagihan->nominal_tagihan : 0);
            }, 0);
            $tunggakanNominal = max(0, $targetNominal - $lunasNominal);
            $persen = $targetNominal > 0 ? round(($lunasNominal / $targetNominal) * 100, 1) : 0;

            return (object) [
                'kampung'          => $k,
                'total_kk'         => $totalKK,
                'lunas_count'      => $lunasCount,
                'belum_lunas_count' => $belumLunasCount,
                'target_nominal'   => $targetNominal,
                'lunas_nominal'    => $lunasNominal,
                'tunggakan_nominal' => $tunggakanNominal,
                'persen'           => $persen,
            ];
        })->filter(fn($rk) => $rk->total_kk > 0)->values();

        $walis = $allWalis;
        if ($request->filled('status_bayar')) {
            if ($request->status_bayar === 'Lunas') {
                $walis = $walis->filter(fn($w) => $w->current_tagihan && $w->current_tagihan->status_bayar === 'Lunas');
            } elseif ($request->status_bayar === 'Belum Lunas') {
                $walis = $walis->filter(fn($w) => !$w->current_tagihan || $w->current_tagihan->status_bayar !== 'Lunas');
            }
        }

        return view('tagihan-wali.laporan', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarKampung',
            'selectedKampungId',
            'masterTagihans',
            'selectedMasterId',
            'selectedMaster',
            'walis',
            'totalWaliAktif',
            'totalTargetNominal',
            'totalLunasNominal',
            'totalTunggakanNominal',
            'totalLunasCount',
            'totalBelumLunasCount',
            'persenLunas',
            'rekapPerKampung'
        ));
    }

    /**
     * Terbitkan Tagihan Massal ke Seluruh / Pilihan Wali Murid Aktif
     */
    public function terbitkan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id'    => 'required|exists:tahun_pelajarans,id',
            'pengaturan_tagihan_id' => 'required|exists:pengaturan_tagihans,id',
            'kampung_id'            => 'nullable|exists:kampungs,id',
            'wali_ids'              => 'nullable|array',
            'wali_ids.*'            => 'exists:wali_murids,id',
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $master = PengaturanTagihan::where('sasaran', 'wali_murid')->findOrFail($request->pengaturan_tagihan_id);

        // Ambil daftar wali aktif
        $queryWali = WaliMurid::where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    });
            });

        if (!empty($request->wali_ids)) {
            $queryWali->whereIn('id', $request->wali_ids);
        } elseif ($request->filled('kampung_id')) {
            $queryWali->where('kampung_id', $request->kampung_id);
        }

        $walis = $queryWali->get();

        if ($walis->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data Wali Murid Aktif yang ditemukan untuk diterbitkan tagihan.');
        }

        $terbuat = 0;
        DB::beginTransaction();
        try {
            $existingTagihanWali = TagihanWaliMurid::where('tahun_pelajaran_id', $tahunId)
                ->where('pengaturan_tagihan_id', $master->id)
                ->pluck('id', 'wali_murid_id');

            foreach ($walis as $wali) {
                if (!$existingTagihanWali->has($wali->id)) {
                    TagihanWaliMurid::create([
                        'wali_murid_id'         => $wali->id,
                        'tahun_pelajaran_id'    => $tahunId,
                        'pengaturan_tagihan_id' => $master->id,
                        'nama_tagihan_spesifik' => $master->nama_tagihan,
                        'nominal_tagihan'       => $master->nominal,
                        'status_bayar'          => 'Belum Lunas',
                    ]);
                    $terbuat++;
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menerbitkan {$terbuat} tagihan baru untuk Wali Murid Aktif!"
                ]);
            }

            return redirect()->back()->with('success', "Sukses! Sebanyak {$terbuat} tagihan \"{$master->nama_tagihan}\" telah diterbitkan untuk Wali Murid Aktif.");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menerbitkan tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Proses Kasir Pembayaran Tagihan Per Wali Murid (Single / Massal)
     */
    public function bayar(Request $request)
    {
        $request->validate([
            'tagihan_ids'       => 'required|array|min:1',
            'tagihan_ids.*'     => 'exists:tagihan_wali_murids,id',
            'metode_pembayaran' => 'nullable|string',
            'tabungan_id'       => 'nullable|exists:tabungans,id',
            'tanggal_bayar'     => 'nullable|date',
            'catatan'           => 'nullable|string',
        ]);

        $tagihanIds = $request->tagihan_ids;
        $metode = $request->metode_pembayaran ?? 'Tunai';
        $tanggalBayar = $request->tanggal_bayar ?? now();

        DB::beginTransaction();
        try {
            $tagihans = TagihanWaliMurid::with(['waliMurid.murids.tabungans', 'pengaturanTagihan'])
                ->whereIn('id', $tagihanIds)
                ->lockForUpdate()
                ->get();

            if ($tagihans->isEmpty()) {
                throw new \Exception('Tagihan tidak ditemukan.');
            }

            $alreadyPaid = $tagihans->where('status_bayar', 'Lunas');
            if ($alreadyPaid->isNotEmpty()) {
                throw new \Exception('Satu atau lebih tagihan yang dipilih sudah berstatus Lunas.');
            }

            $firstTagihan = $tagihans->first();
            $wali = $firstTagihan->waliMurid;
            $master = $firstTagihan->pengaturanTagihan;

            $totalNominal = $tagihans->sum('nominal_tagihan');

            $hari = Carbon::parse($tanggalBayar)->format('d');
            $bulan = Carbon::parse($tanggalBayar)->format('m');
            $tahun = Carbon::parse($tanggalBayar)->format('Y');

            $kodeTagihan = $master->kode_tagihan ?? 'WLI';
            $noReg = $wali->no_registrasi ?? '0000';
            $randomCode = mt_rand(10000, 99999);

            $noKwitansi = 'TRX/' . $kodeTagihan . '/WLI/' . $noReg . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode;

            $catatanFinal = $request->catatan ?? (
                $tagihans->count() > 1
                ? "Pembayaran Tagihan KK ({$tagihans->count()} Tagihan) a.n. {$wali->nama_kepala_keluarga} (Reg: {$wali->no_registrasi})"
                : "Pembayaran Tagihan {$master->nama_tagihan} a.n. {$wali->nama_kepala_keluarga} (Reg: {$wali->no_registrasi})"
            );

            $rekeningDipakai = [];

            // JIKA METODE ADALAH POTONG TABUNGAN MURID (AUTO DEBET DARI TABUNGAN SANTRI)
            if (in_array($metode, ['Tabungan Murid', 'Potong Tabungan Murid', 'Tabungan Santri', 'Potong Tabungan'])) {
                foreach ($tagihans as $tgh) {
                    $w = $tgh->waliMurid;
                    $muridIds = $w->murids->pluck('id')->toArray();

                    // Jika single tagihan dan kasir memilih rekening tabungan tertentu
                    if ($tagihans->count() === 1 && $request->filled('tabungan_id')) {
                        $tabungan = Tabungan::with('murid')
                            ->where('id', $request->tabungan_id)
                            ->whereIn('murid_id', $muridIds)
                            ->where('status', 'Aktif')
                            ->first();

                        if (!$tabungan) {
                            throw new \Exception("Rekening Tabungan yang dipilih tidak valid atau bukan milik santri dari KK {$w->nama_kepala_keluarga}.");
                        }
                    } else {
                        // Cari rekening santri dengan saldo terbesar
                        $tabungan = Tabungan::with('murid')
                            ->whereIn('murid_id', $muridIds)
                            ->where('status', 'Aktif')
                            ->orderBy('saldo', 'desc')
                            ->first();
                    }

                    if (!$tabungan) {
                        throw new \Exception("Santri dari Wali Murid '{$w->nama_kepala_keluarga}' belum memiliki rekening Tabungan Santri yang aktif.");
                    }

                    $nominalDebet = (float) $tgh->nominal_tagihan;
                    if ((float) $tabungan->saldo < $nominalDebet) {
                        $saldoFmt = number_format($tabungan->saldo, 0, ',', '.');
                        $tghFmt = number_format($nominalDebet, 0, ',', '.');
                        throw new \Exception("Saldo tabungan santri {$tabungan->murid->nama_lengkap} (No. Rek: {$tabungan->nomor_rekening}, Saldo: Rp {$saldoFmt}) tidak mencukupi untuk tagihan (Rp {$tghFmt}).");
                    }

                    $saldoAwal = (float) $tabungan->saldo;
                    $saldoAkhir = $saldoAwal - $nominalDebet;
                    $kodeTrxTab = 'TRX-TAB-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                    TransaksiTabungan::create([
                        'kode_transaksi'      => $kodeTrxTab,
                        'tabungan_id'         => $tabungan->id,
                        'jenis_transaksi'     => 'Tarik',
                        'nominal_kotor'       => $nominalDebet,
                        'persentase_potongan' => 0.00,
                        'nominal_potongan'    => 0.00,
                        'nominal_bersih'      => $nominalDebet,
                        'saldo_awal'          => $saldoAwal,
                        'saldo_akhir'         => $saldoAkhir,
                        'tanggal'             => $tanggalBayar,
                        'ruangan_id'          => $tabungan->ruangan_id,
                        'petugas_id'          => auth()->id(),
                        'metode'              => 'Auto_Debet',
                        'keterangan'          => "Potong Tabungan [No. Rek: {$tabungan->nomor_rekening}] untuk {$tgh->nama_tagihan_spesifik} a.n. KK {$w->nama_kepala_keluarga} ({$noKwitansi})",
                    ]);

                    $tabungan->update([
                        'saldo'       => $saldoAkhir,
                        'total_tarik' => (float) $tabungan->total_tarik + $nominalDebet,
                    ]);

                    $rekeningDipakai[] = "{$tabungan->nomor_rekening} ({$tabungan->murid->nama_lengkap})";
                }

                if (!empty($rekeningDipakai)) {
                    $catatanFinal .= " [Potong Tabungan: " . implode(', ', array_unique($rekeningDipakai)) . "]";
                }
            }

            $pembayaran = PembayaranTagihan::create([
                'no_transaksi'      => $noKwitansi,
                'tanggal_bayar'     => $tanggalBayar,
                'tipe_pembayar'     => 'Wali Murid',
                'nama_pembayar'     => $wali->nama_kepala_keluarga,
                'alamat_pembayar'   => $wali->alamat_detail ?? ($wali->kampung->nama_kampung ?? '-'),
                'metode_pembayaran' => $metode,
                'rekening_penerima' => !empty($rekeningDipakai) ? implode(', ', array_unique($rekeningDipakai)) : null,
                'total_nominal'     => $totalNominal,
                'catatan'           => $catatanFinal,
            ]);

            TagihanWaliMurid::whereIn('id', $tagihans->pluck('id'))->update([
                'status_bayar'          => 'Lunas',
                'pembayaran_tagihan_id' => $pembayaran->id,
                'updated_at'            => now(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pembayaran sebesar Rp " . number_format($totalNominal, 0, ',', '.') . " berhasil dicatat dengan No Kwitansi: {$noKwitansi}",
                    'data' => [
                        'no_transaksi'  => $noKwitansi,
                        'pembayaran_id' => $pembayaran->id
                    ]
                ]);
            }

            return redirect()->back()->with('success', "Pembayaran sebesar Rp " . number_format($totalNominal, 0, ',', '.') . " dari {$wali->nama_kepala_keluarga} berhasil dicatat dengan No Kwitansi: {$noKwitansi}");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }

    /**
     * Batal Transaksi Pembayaran / Refund Tagihan Wali Murid
     */
    public function batalBayar($id)
    {
        DB::beginTransaction();
        try {
            $tagihan = TagihanWaliMurid::with('waliMurid.murids')->findOrFail($id);
            $pembayaranId = $tagihan->pembayaran_tagihan_id;

            if ($pembayaranId) {
                $pembayaran = PembayaranTagihan::find($pembayaranId);
                if ($pembayaran) {
                    // JIKA PEMBAYARAN MENGGUNAKAN TABUNGAN MURID, KEMBALIKAN SALDO TABUNGAN
                    if (in_array($pembayaran->metode_pembayaran, ['Tabungan Murid', 'Potong Tabungan Murid', 'Tabungan Santri', 'Potong Tabungan'])) {
                        $muridIds = $tagihan->waliMurid->murids->pluck('id')->toArray();
                        $tabungan = null;

                        // Cari rekening berdasarkan rekening_penerima jika ada nomor rekening tersimpan
                        if (!empty($pembayaran->rekening_penerima)) {
                            // Ekstrak no rekening dari format "NOMOR (Nama)" atau "NOMOR"
                            $noRekClean = trim(explode('(', $pembayaran->rekening_penerima)[0]);
                            $tabungan = Tabungan::where('nomor_rekening', $noRekClean)->first();
                        }

                        if (!$tabungan) {
                            $tabungan = Tabungan::whereIn('murid_id', $muridIds)->first();
                        }

                        if ($tabungan) {
                            $nominalRefund = (float) $tagihan->nominal_tagihan;
                            $saldoAwal = (float) $tabungan->saldo;
                            $saldoAkhir = $saldoAwal + $nominalRefund;
                            $kodeTrxTab = 'TRX-REFUND-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                            TransaksiTabungan::create([
                                'kode_transaksi'      => $kodeTrxTab,
                                'tabungan_id'         => $tabungan->id,
                                'jenis_transaksi'     => 'Setor',
                                'nominal_kotor'       => $nominalRefund,
                                'persentase_potongan' => 0.00,
                                'nominal_potongan'    => 0.00,
                                'nominal_bersih'      => $nominalRefund,
                                'saldo_awal'          => $saldoAwal,
                                'saldo_akhir'         => $saldoAkhir,
                                'tanggal'             => now()->format('Y-m-d'),
                                'ruangan_id'          => $tabungan->ruangan_id,
                                'petugas_id'          => auth()->id(),
                                'metode'              => 'Auto_Debet',
                                'keterangan'          => "Refund Pembatalan Kwitansi {$pembayaran->no_transaksi} ({$tagihan->nama_tagihan_spesifik}) ke No. Rek {$tabungan->nomor_rekening}",
                            ]);

                            $tabungan->update([
                                'saldo'       => $saldoAkhir,
                                'total_tarik' => max(0, (float) $tabungan->total_tarik - $nominalRefund),
                            ]);
                        }
                    }

                    $sisaTagihanWali = TagihanWaliMurid::where('pembayaran_tagihan_id', $pembayaranId)
                        ->where('id', '!=', $tagihan->id)
                        ->count();

                    if ($sisaTagihanWali == 0) {
                        $pembayaran->delete();
                    } else {
                        $pembayaran->decrement('total_nominal', $tagihan->nominal_tagihan);
                    }
                }
            }

            $tagihan->update([
                'status_bayar'          => 'Belum Lunas',
                'pembayaran_tagihan_id' => null,
            ]);

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi pembayaran tagihan berhasil dibatalkan.'
                ]);
            }

            return redirect()->back()->with('success', 'Transaksi pembayaran berhasil dibatalkan dan status kembali menjadi Belum Lunas.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal membatalkan pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Tagihan Perorangan Wali Murid (Hanya yang Belum Lunas)
     */
    public function destroy($id)
    {
        $tagihan = TagihanWaliMurid::with('waliMurid')->findOrFail($id);

        if ($tagihan->status_bayar === 'Lunas') {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tagihan tidak dapat dihapus karena sudah LUNAS. Batalkan pembayaran terlebih dahulu.'], 422);
            }
            return redirect()->back()->with('error', 'Tagihan tidak dapat dihapus karena sudah LUNAS. Batalkan pembayaran terlebih dahulu jika ingin menghapus tagihan.');
        }

        $namaWali = $tagihan->waliMurid->nama_kepala_keluarga ?? 'Wali Murid';
        $namaTagihan = $tagihan->nama_tagihan_spesifik;

        $tagihan->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Tagihan '{$namaTagihan}' untuk {$namaWali} berhasil dihapus."
            ]);
        }

        return redirect()->back()->with('success', "Tagihan \"{$namaTagihan}\" untuk {$namaWali} berhasil dihapus.");
    }

    /**
     * Hapus Tagihan Terpilih / Tercentang (Hanya yang Belum Lunas)
     */
    public function hapusMassal(Request $request)
    {
        $request->validate([
            'tagihan_ids'   => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihan_wali_murids,id',
        ]);

        $tagihans = TagihanWaliMurid::whereIn('id', $request->tagihan_ids)
            ->where('status_bayar', '!=', 'Lunas')
            ->get();

        if ($tagihans->isEmpty()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada tagihan Belum Lunas yang dapat dihapus dari daftar pilihan.'], 422);
            }
            return redirect()->back()->with('error', 'Tidak ada tagihan Belum Lunas yang dapat dihapus dari daftar pilihan.');
        }

        $count = $tagihans->count();
        TagihanWaliMurid::whereIn('id', $tagihans->pluck('id'))->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sebanyak {$count} tagihan berhasil dihapus."
            ]);
        }

        return redirect()->back()->with('success', "Sukses! Sebanyak {$count} tagihan Belum Lunas berhasil dihapus.");
    }

    /**
     * Hapus Seluruh Tagihan Belum Lunas pada Jenis Tagihan & Tahun Ini
     */
    public function hapusSemua(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id'    => 'required|exists:tahun_pelajarans,id',
            'pengaturan_tagihan_id' => 'required|exists:pengaturan_tagihans,id',
            'kampung_id'            => 'nullable|exists:kampungs,id',
        ]);

        $master = PengaturanTagihan::where('sasaran', 'wali_murid')->findOrFail($request->pengaturan_tagihan_id);

        $query = TagihanWaliMurid::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('pengaturan_tagihan_id', $master->id)
            ->where('status_bayar', '!=', 'Lunas');

        if ($request->filled('kampung_id')) {
            $query->whereHas('waliMurid', fn($q) => $q->where('kampung_id', $request->kampung_id));
        }

        $deleted = $query->delete();

        if ($deleted === 0) {
            return redirect()->back()->with('error', "Tidak ada tagihan 'Belum Lunas' untuk jenis tagihan {$master->nama_tagihan} yang dapat dihapus.");
        }

        return redirect()->back()->with('success', "Sukses! Sebanyak {$deleted} tagihan \"{$master->nama_tagihan}\" (Belum Lunas) berhasil dihapus.");
    }

    /**
     * Modal Detail Tanggungan KK & Riwayat Tagihan
     */
    public function detail($id, Request $request)
    {
        $tahunId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $wali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->with(['ruangans' => function ($rq) use ($tahunId) {
                        if ($tahunId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                        }
                    }]);
            },
            'tagihanWaliMurids' => function ($q) use ($tahunId) {
                $q->where('tahun_pelajaran_id', $tahunId)
                    ->with(['pengaturanTagihan', 'pembayaranTagihan']);
            }
        ])->findOrFail($id);

        if ($request->ajax()) {
            return view('tagihan-wali.modal-detail', compact('wali', 'tahunId'));
        }

        return redirect()->route('tagihan-wali.index');
    }

    /**
     * Cetak Kwitansi Resmi Pembayaran Tagihan Wali Murid
     */
    public function cetakKwitansi($id)
    {
        $tagihan = TagihanWaliMurid::with([
            'waliMurid.kampung',
            'waliMurid.murids' => function ($q) {
                $q->where('status', 'Aktif')->with('ruangans');
            },
            'tahunPelajaran',
            'pengaturanTagihan',
            'pembayaranTagihan'
        ])->findOrFail($id);

        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $bendahara = Pengurus::getAktifByJabatan('Bendahara') ?? Pengurus::getAktifByJabatan('Sekretaris Jenderal');
        $administrator = Administrator::where('user_id', auth()->id())->first()
            ?? Administrator::where('is_active', true)->whereNull('tingkat_id')->first()
            ?? Administrator::where('is_active', true)->first();

        return view('cetak-baru.cetak_kwitansi_wali', compact('tagihan', 'pengasuh', 'bendahara', 'administrator'));
    }

    /**
     * Cetak Lembar Rekapitulasi Tagihan Per Wali Murid Aktif
     */
    public function cetakRekap(Request $request)
    {
        $tahunId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $tahunPelajaran = TahunPelajaran::findOrFail($tahunId);

        $selectedMasterId = $request->pengaturan_tagihan_id;
        $selectedMaster = PengaturanTagihan::find($selectedMasterId);

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $kampungId = $request->filled('kampung_id') && $request->kampung_id !== 'semua' ? $request->kampung_id : null;
        $kampung = $kampungId ? Kampung::find($kampungId) : null;

        $queryWali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    })
                    ->with(['ruangans' => function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    }]);
            },
            'tagihanWaliMurids' => function ($q) use ($tahunId, $selectedMasterId) {
                $q->where('tahun_pelajaran_id', $tahunId)
                    ->when($selectedMasterId, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMasterId))
                    ->with('pembayaranTagihan');
            }
        ])
            ->where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    });
            })
            ->whereHas('tagihanWaliMurids', function ($q) use ($tahunId, $selectedMasterId) {
                $q->where('tahun_pelajaran_id', $tahunId)
                    ->when($selectedMasterId, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMasterId));
            });

        if ($kampungId) {
            $queryWali->where('kampung_id', $kampungId);
        }

        $walis = $queryWali->get()->sortBy(function ($w) {
            $kode = $w->kampung ? str_pad($w->kampung->kode, 4, '0', STR_PAD_LEFT) : '9999';
            return $kode . '_' . ($w->nama_kepala_keluarga ?? '');
        });

        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $bendahara = Pengurus::getAktifByJabatan('Bendahara') ?? Pengurus::getAktifByJabatan('Sekretaris Jenderal');
        $administrator = Administrator::where('user_id', auth()->id())->first()
            ?? Administrator::where('is_active', true)->whereNull('tingkat_id')->first()
            ?? Administrator::where('is_active', true)->first();

        return view('cetak-baru.cetak_rekap_tagihan_wali', compact(
            'walis',
            'tahunPelajaran',
            'selectedMaster',
            'kampung',
            'pengasuh',
            'bendahara',
            'administrator'
        ));
    }
}
