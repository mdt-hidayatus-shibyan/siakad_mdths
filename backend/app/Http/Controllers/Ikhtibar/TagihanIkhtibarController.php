<?php

namespace App\Http\Controllers\Ikhtibar;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\Ikhtibar\PanitiaIkhtibar;
use App\Models\Ikhtibar\PembayaranTagihanIkhtibar;
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

class TagihanIkhtibarController extends Controller
{
    /**
     * Redirect Index ke Kasir Reguler Ikhtibar
     */
    public function index(Request $request)
    {
        return redirect()->route('tagihan-ikhtibar.kasir', $request->query());
    }

    /**
     * Helper private untuk mengambil KegiatanIkhtibar dan Master Tagihan
     */
    private function getKegiatanAndMaster($tahunPelajaranId)
    {
        $kegiatan = KegiatanIkhtibar::with('pengaturanTagihan')
            ->firstOrCreate(
                ['tahun_pelajaran_id' => $tahunPelajaranId],
                [
                    'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                    'ikhtibar_ke'     => '52 Th',
                    'status_kegiatan' => 'Persiapan',
                ]
            );

        $masterTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('sasaran', 'wali_murid')
            ->orderBy('id', 'asc')
            ->get();

        $selectedMaster = null;
        if ($kegiatan->pengaturan_tagihan_id) {
            $selectedMaster = $masterTagihans->firstWhere('id', $kegiatan->pengaturan_tagihan_id);
        }
        if (!$selectedMaster) {
            $selectedMaster = $masterTagihans->first();
        }

        return [$kegiatan, $masterTagihans, $selectedMaster];
    }

    /**
     * Submenu 1: Penerbitan Tagihan Ikhtibar Wali Murid (KK Aktif)
     */
    public function terbitkanIndex(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $request->tahun_id 
            ?? $request->tahun_pelajaran_id 
            ?? TahunPelajaran::where('is_active', true)->value('id') 
            ?? $daftarTahun->first()?->id;

        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunPelajaranId);

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($defaultMaster?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId) ?? $defaultMaster;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $selectedKampungId = $request->kampung_id;

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

        if ($selectedKampungId && $selectedKampungId !== 'semua') {
            $queryWali->where('kampung_id', $selectedKampungId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchHash = function_exists('hash_sensitive') ? hash_sensitive($search) : null;
            $queryWali->where(function ($q) use ($search, $searchHash) {
                $q->where('nama_kepala_keluarga', 'LIKE', "%{$search}%")
                    ->orWhere('no_registrasi', 'LIKE', "%{$search}%")
                    ->orWhere('no_hp', 'LIKE', "%{$search}%");
                if ($searchHash) {
                    $q->orWhere('no_kk_hash', $searchHash);
                }
                $q->orWhereHas('murids', function ($mq) use ($search) {
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

        return view('ikhtibar.tagihan.terbitkan', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'kegiatan',
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
     * Eksekusi Terbitkan Tagihan Ikhtibar (Massal / Terpilih)
     */
    public function terbitkan(Request $request)
    {
        $request->validate([
            'tahun_id'              => 'required|exists:tahun_pelajarans,id',
            'pengaturan_tagihan_id' => 'required|exists:pengaturan_tagihans,id',
        ]);

        $tahunId = $request->tahun_id;
        $master = PengaturanTagihan::findOrFail($request->pengaturan_tagihan_id);

        $queryWali = WaliMurid::where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    });
            });

        if ($request->filled('wali_ids') && is_array($request->wali_ids)) {
            $queryWali->whereIn('id', $request->wali_ids);
        } elseif ($request->filled('kampung_id') && $request->kampung_id !== 'semua') {
            $queryWali->where('kampung_id', $request->kampung_id);
        }

        $walis = $queryWali->get();

        if ($walis->isEmpty()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data Wali Murid yang dipilih untuk diterbitkan tagihan.'], 422);
            }
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

            // Update pengaturan_tagihan_id di KegiatanIkhtibar jika belum terhubung
            $kegiatan = KegiatanIkhtibar::firstOrCreate(['tahun_pelajaran_id' => $tahunId], [
                'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                'ikhtibar_ke'     => '52 Th',
                'status_kegiatan' => 'Persiapan',
            ]);
            if (!$kegiatan->pengaturan_tagihan_id) {
                $kegiatan->update(['pengaturan_tagihan_id' => $master->id]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menerbitkan {$terbuat} tagihan baru Iuran Ikhtibar untuk Wali Murid Aktif!"
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
     * Hapus Massal Tagihan Belum Lunas
     */
    public function hapusMassal(Request $request)
    {
        $request->validate([
            'tagihan_ids'   => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihan_wali_murids,id',
        ]);

        try {
            $deletedCount = TagihanWaliMurid::whereIn('id', $request->tagihan_ids)
                ->where('status_bayar', 'Belum Lunas')
                ->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$deletedCount} data tagihan yang belum lunas."
                ]);
            }

            return redirect()->back()->with('success', "Sukses menghapus {$deletedCount} data tagihan Ikhtibar yang belum lunas.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Semua Tagihan Belum Lunas di Tahun/Master Ini
     */
    public function hapusSemua(Request $request)
    {
        $request->validate([
            'tahun_id'              => 'required|exists:tahun_pelajarans,id',
            'pengaturan_tagihan_id' => 'required|exists:pengaturan_tagihans,id',
        ]);

        try {
            $deletedCount = TagihanWaliMurid::where('tahun_pelajaran_id', $request->tahun_id)
                ->where('pengaturan_tagihan_id', $request->pengaturan_tagihan_id)
                ->where('status_bayar', 'Belum Lunas')
                ->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil membatalkan penerbitan {$deletedCount} tagihan yang belum lunas."
                ]);
            }

            return redirect()->back()->with('success', "Berhasil menghapus seluruh ({$deletedCount}) tagihan Ikhtibar yang belum dibayar.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Satu Tagihan Belum Lunas
     */
    public function destroy($id)
    {
        $tagihan = TagihanWaliMurid::with('waliMurid')->findOrFail($id);

        if ($tagihan->status_bayar === 'Lunas') {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tagihan tidak dapat dihapus karena sudah LUNAS. Batalkan pembayaran terlebih dahulu.'], 422);
            }
            return redirect()->back()->with('error', 'Tagihan yang sudah LUNAS tidak dapat dihapus.');
        }

        $namaWali = $tagihan->waliMurid->nama_kepala_keluarga ?? 'Wali Murid';
        $tagihan->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Tagihan atas nama {$namaWali} berhasil dihapus."
            ]);
        }

        return redirect()->back()->with('success', "Tagihan atas nama {$namaWali} berhasil dihapus.");
    }

    /**
     * Submenu 2A: Kasir Reguler / Scan No Registrasi & No KK
     */
    public function kasir(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $request->tahun_id 
            ?? $request->tahun_pelajaran_id 
            ?? TahunPelajaran::where('is_active', true)->value('id') 
            ?? $daftarTahun->first()?->id;

        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunPelajaranId);

        $waliTerpilih = null;
        $semuaTagihan = collect();
        $availableTabungans = collect();
        $daftarAnakKk = collect();
        $searchKey = trim($request->search_wali ?? $request->search ?? $request->search_nism ?? '');

        if (!empty($searchKey)) {
            $searchHash = function_exists('hash_sensitive') ? hash_sensitive($searchKey) : null;
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
                'tagihanWaliMurids' => function ($q) use ($tahunPelajaranId, $defaultMaster) {
                    $q->where('tahun_pelajaran_id', $tahunPelajaranId);
                    if ($defaultMaster) {
                        $q->where('pengaturan_tagihan_id', $defaultMaster->id);
                    }
                    $q->with(['pengaturanTagihan', 'pembayaranTagihan', 'pembayaranIkhtibars.diterimaOleh']);
                }
            ])
                ->where('is_active', true)
                ->where(function ($q) use ($searchKey, $searchHash) {
                    $q->where('no_registrasi', $searchKey)
                        ->orWhere('nama_kepala_keluarga', 'LIKE', "%{$searchKey}%")
                        ->orWhere('no_hp', $searchKey);
                    if ($searchHash) {
                        $q->orWhere('no_kk_hash', $searchHash);
                    }
                    $q->orWhereHas('murids', function ($mq) use ($searchKey) {
                        $mq->where('nism', $searchKey)
                            ->orWhere('nama_lengkap', 'LIKE', "%{$searchKey}%");
                    });
                })
                ->first();

            if ($waliTerpilih) {
                // Tarik semua tagihan Ikhtibar KK tahun ini
                $semuaTagihan = $waliTerpilih->tagihanWaliMurids;

                // Siapkan daftar anak untuk kemudahan input potong tabungan manual
                $daftarAnakKk = $waliTerpilih->murids->map(function ($anak) {
                    $namaRuangan = $anak->ruangans->first()?->nama_ruangan ?? '-';
                    $noTab = $anak->tabungans->first()?->nomor_rekening ?? ('TAB-' . ($anak->nism ?: $anak->id));
                    return [
                        'id'             => $anak->id,
                        'nama'           => $anak->nama_lengkap,
                        'nism'           => $anak->nism,
                        'ruangan'        => $namaRuangan,
                        'nomor_tabungan' => $noTab,
                    ];
                });

                // Rekap daftar rekening tabungan santri dari KK tersebut (jika ada)
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

        // Riwayat Transaksi Hari Ini untuk Kasir Ikhtibar
        $riwayatHariIni = PembayaranTagihan::whereDate('tanggal_bayar', Carbon::today())
            ->where('tipe_pembayar', 'Wali Murid')
            ->where(function ($q) use ($defaultMaster) {
                $q->where('no_transaksi', 'LIKE', 'TRX/IKH/%')
                  ->orWhere('no_transaksi', 'LIKE', '%IKH%')
                  ->orWhere('catatan', 'LIKE', '%Ikhtibar%');
                if ($defaultMaster) {
                    $q->orWhere('catatan', 'LIKE', "%{$defaultMaster->nama_tagihan}%");
                }
            })
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('ikhtibar.tagihan.kasir', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'kegiatan',
            'defaultMaster',
            'waliTerpilih',
            'semuaTagihan',
            'daftarAnakKk',
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
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $request->tahun_id 
            ?? $request->tahun_pelajaran_id 
            ?? TahunPelajaran::where('is_active', true)->value('id') 
            ?? $daftarTahun->first()?->id;

        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunPelajaranId);

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($defaultMaster?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId) ?? $defaultMaster;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $selectedKampungId = $request->kampung_id ?? ($daftarKampung->first()?->id ?? null);
        $kampungTerpilih = $selectedKampungId ? Kampung::find($selectedKampungId) : null;

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
                $searchHash = function_exists('hash_sensitive') ? hash_sensitive($search) : null;
                $queryWali->where(function ($q) use ($search, $searchHash) {
                    $q->where('nama_kepala_keluarga', 'LIKE', "%{$search}%")
                        ->orWhere('no_registrasi', 'LIKE', "%{$search}%")
                        ->orWhere('no_hp', 'LIKE', "%{$search}%");
                    if ($searchHash) {
                        $q->orWhere('no_kk_hash', $searchHash);
                    }
                    $q->orWhereHas('murids', function ($mq) use ($search) {
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

        return view('ikhtibar.tagihan.kasir-leger', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'kegiatan',
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
     * Pelunasan Massal Tagihan Ikhtibar via Leger
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

                $noReg = $w->no_registrasi ?? '0000';
                $randomCode = mt_rand(10000, 99999);
                $noKwitansi = 'TRX/IKH/WLI/' . $noReg . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode;

                $pembayaran = PembayaranTagihan::create([
                    'no_transaksi'      => $noKwitansi,
                    'tanggal_bayar'     => $tanggalBayar,
                    'tipe_pembayar'     => 'Wali Murid',
                    'nama_pembayar'     => $w->nama_kepala_keluarga ?? 'Wali Murid',
                    'metode_pembayaran' => $metode,
                    'total_nominal'     => $tgh->nominal_tagihan,
                    'catatan'           => $request->catatan ?? "Pelunasan Kasir Leger Ikhtibar ({$master->nama_tagihan}) a.n. {$w->nama_kepala_keluarga} (Reg: {$w->no_registrasi})"
                ]);

                // Simpan Riwayat Pembayaran Ikhtibar
                PembayaranTagihanIkhtibar::create([
                    'tagihan_wali_murid_id' => $tgh->id,
                    'kegiatan_ikhtibar_id'   => $kegiatan?->id,
                    'tahun_pelajaran_id'    => $tgh->tahun_pelajaran_id,
                    'wali_murid_id'         => $w->id,
                    'no_transaksi'          => $noKwitansi,
                    'metode_pembayaran'     => $metode,
                    'tipe_pembayaran'       => 'Lunas',
                    'termin_ke'             => 1,
                    'nominal_tagihan'       => $tgh->nominal_tagihan,
                    'nominal_bayar'         => $tgh->nominal_tagihan,
                    'sisa_tagihan'          => 0,
                    'tanggal_bayar'         => $tanggalBayar,
                    'pembayaran_tagihan_id' => $pembayaran->id,
                    'diterima_oleh_id'      => auth()->id(),
                    'nama_penyetor'         => $w->nama_kepala_keluarga,
                    'catatan'               => $request->catatan ?? 'Pelunasan Kasir Leger',
                ]);

                $tgh->update([
                    'total_terbayar'        => $tgh->nominal_tagihan,
                    'status_bayar'          => 'Lunas',
                    'pembayaran_tagihan_id' => $pembayaran->id,
                    'updated_at'            => now(),
                ]);

                $totalProcessed++;
                $totalNominal += (float) $tgh->nominal_tagihan;
            }

            DB::commit();

            return redirect()->back()->with('success', "Sukses! Sebanyak {$totalProcessed} tagihan Wali Murid (Total Rp " . number_format($totalNominal, 0, ',', '.') . ") berhasil dilunasi via Kasir Leger Ikhtibar.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pelunasan kasir leger: ' . $e->getMessage());
        }
    }

    /**
     * Proses Pembayaran Kasir Reguler:
     * 1. Tunai (Cash): Bayar lunas seketika / sisa tagihan.
     * 2. Potong Tabungan: Manual input nomor_tabungan, nama_nasabah, ruangan_nasabah, is_verified.
     * 3. Cicilan (Maks. 3x): Mendukung pembayaran bertahap termin 1, 2, 3 (Termin 3 wajib melunasi sisa).
     */
    public function bayar(Request $request)
    {
        $request->validate([
            'tagihan_ids'        => 'required|array|min:1',
            'tagihan_ids.*'      => 'exists:tagihan_wali_murids,id',
            'metode_pembayaran'  => 'required|string', // 'Tunai', 'Potong Tabungan', 'Cicilan'
            'tanggal_bayar'      => 'nullable|date',
            'catatan'            => 'nullable|string',
            // Field Potong Tabungan Manual
            'nomor_tabungan'     => 'nullable|string',
            'nama_nasabah'       => 'nullable|string',
            'ruangan_nasabah'    => 'nullable|string',
            'is_verified'        => 'nullable',
            // Field Cicilan
            'sub_metode_cicilan' => 'nullable|string',
            'nominal_cicilan'    => 'nullable|numeric|min:1',
        ]);

        $tagihanIds = $request->tagihan_ids;
        $metodeInput = $request->metode_pembayaran;
        $tanggalBayar = $request->tanggal_bayar ?? now();

        DB::beginTransaction();
        try {
            $tagihans = TagihanWaliMurid::with(['waliMurid.murids.tabungans', 'pengaturanTagihan', 'pembayaranIkhtibars'])
                ->whereIn('id', $tagihanIds)
                ->lockForUpdate()
                ->get();

            if ($tagihans->isEmpty()) {
                throw new \Exception('Tagihan tidak ditemukan.');
            }

            // Jika metode Cicilan, hanya boleh 1 tagihan
            if ($metodeInput === 'Cicilan' && $tagihans->count() > 1) {
                throw new \Exception('Metode pembayaran Cicilan hanya dapat diproses untuk 1 tagihan KK sekaligus.');
            }

            $firstTagihan = $tagihans->first();
            $wali = $firstTagihan->waliMurid;
            $master = $firstTagihan->pengaturanTagihan;
            $tahunId = $firstTagihan->tahun_pelajaran_id;
            [$kegiatan] = $this->getKegiatanAndMaster($tahunId);

            $noReg = $wali->no_registrasi ?? '0000';
            $hari = Carbon::parse($tanggalBayar)->format('d');
            $bulan = Carbon::parse($tanggalBayar)->format('m');
            $tahun = Carbon::parse($tanggalBayar)->format('Y');

            $totalNominalTransaksi = 0;
            $kwitansiList = [];

            // ============================================================
            // KASUS 1: METODE CICILAN (MAKSIMAL 3x CICILAN)
            // ============================================================
            if ($metodeInput === 'Cicilan') {
                $tgh = $firstTagihan;
                if ($tgh->status_bayar === 'Lunas') {
                    throw new \Exception('Tagihan ini sudah berstatus Lunas, tidak dapat dicicil lagi.');
                }

                $jumlahCicilanLalu = $tgh->pembayaranIkhtibars->count();
                if ($jumlahCicilanLalu >= 3) {
                    throw new \Exception("Tagihan ini sudah mencapai batas maksimal 3 kali cicilan. Silakan lakukan pelunasan untuk menyelesaikan sisa tagihan.");
                }

                $terminKe = $jumlahCicilanLalu + 1;
                $sisaSebelumnya = $tgh->sisa_tagihan;

                if ($sisaSebelumnya <= 0) {
                    throw new \Exception('Sisa tagihan KK ini adalah Rp 0 (sudah lunas).');
                }

                $nominalBayar = (int) $request->nominal_cicilan;
                if ($nominalBayar <= 0) {
                    throw new \Exception('Nominal cicilan harus lebih besar dari Rp 0.');
                }

                if ($nominalBayar > $sisaSebelumnya) {
                    throw new \Exception('Nominal cicilan (Rp ' . number_format($nominalBayar, 0, ',', '.') . ') melebihi sisa tagihan (Rp ' . number_format($sisaSebelumnya, 0, ',', '.') . ').');
                }

                // Jika termin ke-3 (Termin Terakhir), wajib melunasi seluruh sisa
                if ($terminKe === 3 && $nominalBayar < $sisaSebelumnya) {
                    throw new \Exception("Karena ini adalah Cicilan ke-3 (Termin Terakhir / Maksimal 3x), nominal pembayaran harus melunasi seluruh sisa tagihan sebesar Rp " . number_format($sisaSebelumnya, 0, ',', '.') . ".");
                }

                $subMetode = $request->sub_metode_cicilan ?? 'Tunai';
                $isPotongTabungan = in_array($subMetode, ['Potong Tabungan', 'Tabungan Murid']);

                $nomorTabungan = $isPotongTabungan ? ($request->nomor_tabungan ?: ('TAB-' . $wali->no_registrasi)) : null;
                $namaNasabah = $isPotongTabungan ? ($request->nama_nasabah ?: $wali->nama_kepala_keluarga) : null;
                $ruanganNasabah = $isPotongTabungan ? ($request->ruangan_nasabah ?: '-') : null;
                $isVerified = $isPotongTabungan ? $request->boolean('is_verified') : true;

                $totalTerbayarBaru = ($tgh->total_terbayar ?? 0) + $nominalBayar;
                $sisaBaru = max(0, $tgh->nominal_tagihan - $totalTerbayarBaru);
                $statusBaru = ($sisaBaru <= 0) ? 'Lunas' : 'Cicilan';

                $randomCode = mt_rand(10000, 99999);
                $noKwitansi = 'TRX/IKH/WLI/' . $noReg . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode . '-T' . $terminKe;

                $rekeningInfo = $isPotongTabungan ? "Tabungan Manual: {$nomorTabungan} ({$namaNasabah} - {$ruanganNasabah})" : null;
                $catatanTrx = $request->catatan ?: "Cicilan ke-{$terminKe} (Maks. 3x) via {$subMetode} a.n. {$wali->nama_kepala_keluarga} (Sisa: Rp " . number_format($sisaBaru, 0, ',', '.') . ")";

                // 1. Simpan ke pembayaran_tagihans (standar kwitansi)
                $pembayaran = PembayaranTagihan::create([
                    'no_transaksi'      => $noKwitansi,
                    'tanggal_bayar'     => $tanggalBayar,
                    'tipe_pembayar'     => 'Wali Murid',
                    'nama_pembayar'     => $wali->nama_kepala_keluarga ?? 'Wali Murid',
                    'metode_pembayaran' => $subMetode,
                    'rekening_penerima' => $rekeningInfo,
                    'total_nominal'     => $nominalBayar,
                    'catatan'           => $catatanTrx,
                ]);

                // 2. Simpan ke pembayaran_tagihan_ikhtibars
                $bayarIkhtibar = PembayaranTagihanIkhtibar::create([
                    'tagihan_wali_murid_id' => $tgh->id,
                    'kegiatan_ikhtibar_id'   => $kegiatan?->id,
                    'tahun_pelajaran_id'    => $tgh->tahun_pelajaran_id,
                    'wali_murid_id'         => $wali->id,
                    'no_transaksi'          => $noKwitansi,
                    'metode_pembayaran'     => $subMetode,
                    'tipe_pembayaran'       => ($sisaBaru <= 0) ? 'Lunas' : 'Cicilan',
                    'termin_ke'             => $terminKe,
                    'nominal_tagihan'       => $tgh->nominal_tagihan,
                    'nominal_bayar'         => $nominalBayar,
                    'sisa_tagihan'          => $sisaBaru,
                    'tanggal_bayar'         => $tanggalBayar,
                    'nomor_tabungan'        => $nomorTabungan,
                    'nama_nasabah'          => $namaNasabah,
                    'ruangan_nasabah'       => $ruanganNasabah,
                    'is_verified'           => $isVerified,
                    'verified_at'           => $isVerified ? now() : null,
                    'verified_by_id'        => $isVerified ? auth()->id() : null,
                    'pembayaran_tagihan_id' => $pembayaran->id,
                    'diterima_oleh_id'      => auth()->id(),
                    'nama_penyetor'         => $wali->nama_kepala_keluarga,
                    'catatan'               => $catatanTrx,
                ]);

                // 3. Update tagihan_wali_murids
                $tgh->update([
                    'total_terbayar'        => $totalTerbayarBaru,
                    'status_bayar'          => $statusBaru,
                    'pembayaran_tagihan_id' => $pembayaran->id,
                    'updated_at'            => now(),
                ]);

                $totalNominalTransaksi = $nominalBayar;
                $kwitansiList[] = $noKwitansi;

                $pesanSukses = "Cicilan ke-{$terminKe} sebesar Rp " . number_format($nominalBayar, 0, ',', '.') . " berhasil dicatat dengan No Kwitansi {$noKwitansi}. Status tagihan: {$statusBaru} (Sisa: Rp " . number_format($sisaBaru, 0, ',', '.') . ").";
            }
            // ============================================================
            // KASUS 2: METODE TUNAI (CASH) ATAU POTONG TABUNGAN MANUAL PENUH
            // ============================================================
            else {
                $alreadyPaid = $tagihans->where('status_bayar', 'Lunas');
                if ($alreadyPaid->isNotEmpty()) {
                    throw new \Exception('Satu atau lebih tagihan yang dipilih sudah berstatus Lunas.');
                }

                $isPotongTabungan = in_array($metodeInput, ['Potong Tabungan', 'Tabungan Murid', 'Potong Tabungan Murid']);
                $metodeNyata = $isPotongTabungan ? 'Potong Tabungan' : 'Tunai';

                $nomorTabungan = $isPotongTabungan ? ($request->nomor_tabungan ?: ('TAB-' . $wali->no_registrasi)) : null;
                $namaNasabah = $isPotongTabungan ? ($request->nama_nasabah ?: $wali->nama_kepala_keluarga) : null;
                $ruanganNasabah = $isPotongTabungan ? ($request->ruangan_nasabah ?: '-') : null;
                $isVerified = $isPotongTabungan ? $request->boolean('is_verified') : true;

                $rekeningInfo = $isPotongTabungan ? "Tabungan Manual: {$nomorTabungan} ({$namaNasabah} - {$ruanganNasabah})" : null;

                foreach ($tagihans as $tgh) {
                    $nominalBayar = $tgh->sisa_tagihan > 0 ? $tgh->sisa_tagihan : $tgh->nominal_tagihan;
                    $randomCode = mt_rand(10000, 99999);
                    $terminKe = $tgh->pembayaranIkhtibars->count() + 1;
                    $noKwitansi = 'TRX/IKH/WLI/' . $noReg . '/' . $tahun . '/' . $hari . $bulan . '/' . $randomCode;

                    $catatanTrx = $request->catatan ?: (
                        $isPotongTabungan
                        ? "Pelunasan Potong Tabungan Manual ({$nomorTabungan} a.n. {$namaNasabah}) untuk {$master->nama_tagihan}"
                        : "Pelunasan Tunai Kasir untuk {$master->nama_tagihan} a.n. {$wali->nama_kepala_keluarga}"
                    );

                    // 1. Standar Kwitansi
                    $pembayaran = PembayaranTagihan::create([
                        'no_transaksi'      => $noKwitansi,
                        'tanggal_bayar'     => $tanggalBayar,
                        'tipe_pembayar'     => 'Wali Murid',
                        'nama_pembayar'     => $wali->nama_kepala_keluarga ?? 'Wali Murid',
                        'metode_pembayaran' => $metodeNyata,
                        'rekening_penerima' => $rekeningInfo,
                        'total_nominal'     => $nominalBayar,
                        'catatan'           => $catatanTrx,
                    ]);

                    // 2. Simpan Riwayat Ikhtibar
                    PembayaranTagihanIkhtibar::create([
                        'tagihan_wali_murid_id' => $tgh->id,
                        'kegiatan_ikhtibar_id'   => $kegiatan?->id,
                        'tahun_pelajaran_id'    => $tgh->tahun_pelajaran_id,
                        'wali_murid_id'         => $wali->id,
                        'no_transaksi'          => $noKwitansi,
                        'metode_pembayaran'     => $metodeNyata,
                        'tipe_pembayaran'       => 'Lunas',
                        'termin_ke'             => $terminKe,
                        'nominal_tagihan'       => $tgh->nominal_tagihan,
                        'nominal_bayar'         => $nominalBayar,
                        'sisa_tagihan'          => 0,
                        'tanggal_bayar'         => $tanggalBayar,
                        'nomor_tabungan'        => $nomorTabungan,
                        'nama_nasabah'          => $namaNasabah,
                        'ruangan_nasabah'       => $ruanganNasabah,
                        'is_verified'           => $isVerified,
                        'verified_at'           => $isVerified ? now() : null,
                        'verified_by_id'        => $isVerified ? auth()->id() : null,
                        'pembayaran_tagihan_id' => $pembayaran->id,
                        'diterima_oleh_id'      => auth()->id(),
                        'nama_penyetor'         => $isPotongTabungan ? $namaNasabah : $wali->nama_kepala_keluarga,
                        'catatan'               => $catatanTrx,
                    ]);

                    // 3. Update Status Tagihan ke Lunas
                    $tgh->update([
                        'total_terbayar'        => $tgh->nominal_tagihan,
                        'status_bayar'          => 'Lunas',
                        'pembayaran_tagihan_id' => $pembayaran->id,
                        'updated_at'            => now(),
                    ]);

                    $totalNominalTransaksi += $nominalBayar;
                    $kwitansiList[] = $noKwitansi;
                }

                $pesanSukses = "Pembayaran Iuran Ikhtibar (" . count($tagihans) . " Tagihan) sebesar Rp " . number_format($totalNominalTransaksi, 0, ',', '.') . " a.n. {$wali->nama_kepala_keluarga} berhasil dicatat Lunas via {$metodeNyata}.";
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success'       => true,
                    'message'       => $pesanSukses,
                    'data'          => [
                        'no_transaksi'   => implode(', ', $kwitansiList),
                        'total_nominal'  => $totalNominalTransaksi,
                    ]
                ]);
            }

            return redirect()->back()->with('success', $pesanSukses);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Konfirmasi / Verifikasi Potong Tabungan Manual
     */
    public function toggleVerifikasiTabungan($id)
    {
        $pembayaran = PembayaranTagihanIkhtibar::findOrFail($id);
        $pembayaran->is_verified = !$pembayaran->is_verified;
        $pembayaran->verified_at = $pembayaran->is_verified ? now() : null;
        $pembayaran->verified_by_id = $pembayaran->is_verified ? auth()->id() : null;
        $pembayaran->save();

        if (request()->ajax()) {
            return response()->json([
                'success'     => true,
                'is_verified' => $pembayaran->is_verified,
                'message'     => $pembayaran->is_verified 
                    ? 'Status potongan tabungan BERHASIL diverifikasi/dikonfirmasi.' 
                    : 'Verifikasi potongan tabungan dibatalkan.'
            ]);
        }

        return redirect()->back()->with('success', 'Status verifikasi potongan tabungan berhasil diperbarui.');
    }

    /**
     * Batal Transaksi Pembayaran / Refund Tagihan Ikhtibar (Mendukung Pembatalan Bertahap Cicilan)
     */
    public function batalBayar($id)
    {
        DB::beginTransaction();
        try {
            $tagihan = TagihanWaliMurid::with(['pembayaranIkhtibars', 'waliMurid'])->findOrFail($id);

            // JIKA TAGIHAN MEMILIKI RIWAYAT DI pembayaran_tagihan_ikhtibars
            if ($tagihan->pembayaranIkhtibars->isNotEmpty()) {
                // Ambil pembayaran/termin terakhir untuk dibatalkan
                $lastBayar = $tagihan->pembayaranIkhtibars->last();
                $nominalDibatalkan = $lastBayar->nominal_bayar;
                $terminKe = $lastBayar->termin_ke;

                if ($lastBayar->pembayaran_tagihan_id) {
                    PembayaranTagihan::where('id', $lastBayar->pembayaran_tagihan_id)->delete();
                }
                $lastBayar->delete();

                // Cek sisa pembayaran yang masih tersisa
                $remainingBayars = $tagihan->pembayaranIkhtibars()->get();

                if ($remainingBayars->isEmpty()) {
                    $tagihan->update([
                        'total_terbayar'        => 0,
                        'status_bayar'          => 'Belum Lunas',
                        'pembayaran_tagihan_id' => null,
                        'updated_at'            => now(),
                    ]);
                    $pesan = "Seluruh riwayat pembayaran tagihan a.n. {$tagihan->waliMurid?->nama_kepala_keluarga} berhasil dibatalkan. Status tagihan kini Belum Lunas.";
                } else {
                    $sumTerbayar = $remainingBayars->sum('nominal_bayar');
                    $lastRemaining = $remainingBayars->last();

                    $tagihan->update([
                        'total_terbayar'        => $sumTerbayar,
                        'status_bayar'          => 'Cicilan',
                        'pembayaran_tagihan_id' => $lastRemaining->pembayaran_tagihan_id,
                        'updated_at'            => now(),
                    ]);
                    $sisaBaru = max(0, $tagihan->nominal_tagihan - $sumTerbayar);
                    $pesan = "Cicilan ke-{$terminKe} (Rp " . number_format($nominalDibatalkan, 0, ',', '.') . ") berhasil dibatalkan. Tagihan kembali ke status Cicilan (Terbayar: Rp " . number_format($sumTerbayar, 0, ',', '.') . ", Sisa: Rp " . number_format($sisaBaru, 0, ',', '.') . ").";
                }
            } else {
                // Fallback untuk transaksi lama (legacy single payment)
                $pembayaranId = $tagihan->pembayaran_tagihan_id;
                if ($pembayaranId) {
                    $pembayaran = PembayaranTagihan::find($pembayaranId);
                    if ($pembayaran) {
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
                    'total_terbayar'        => 0,
                    'status_bayar'          => 'Belum Lunas',
                    'pembayaran_tagihan_id' => null,
                    'updated_at'            => now(),
                ]);
                $pesan = "Pembayaran tagihan a.n. {$tagihan->waliMurid?->nama_kepala_keluarga} berhasil dibatalkan.";
            }

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $pesan,
                ]);
            }

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Submenu 3: Laporan & Rekapitulasi Tagihan Ikhtibar (Per Dusun)
     */
    public function laporan(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $request->tahun_id 
            ?? $request->tahun_pelajaran_id 
            ?? TahunPelajaran::where('is_active', true)->value('id') 
            ?? $daftarTahun->first()?->id;

        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunPelajaranId);

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($defaultMaster?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId) ?? $defaultMaster;

        $daftarKampung = Kampung::orderBy('kode', 'asc')->get();
        $selectedKampungId = $request->filled('kampung_id') && $request->kampung_id !== 'semua' ? $request->kampung_id : null;

        $kampungTerpilih = $selectedKampungId ? Kampung::find($selectedKampungId) : null;

        // Rekapitulasi per kampung
        $rekapKampung = [];
        $totalSemuaKk = 0;
        $totalSemuaLunas = 0;
        $totalSemuaBelumLunas = 0;
        $totalNominalTarget = 0;
        $totalNominalMasuk = 0;
        $totalNominalPiutang = 0;

        // Ambil ID tagihan yang memiliki potongan tabungan belum terkonfirmasi
        $tagihanPendingIds = PembayaranTagihanIkhtibar::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('metode_pembayaran', ['Potong Tabungan', 'Tabungan Murid', 'Tabungan Santri'])
            ->where('is_verified', false)
            ->pluck('tagihan_wali_murid_id')
            ->unique();

        $targetKampungs = $selectedKampungId ? Kampung::where('id', $selectedKampungId)->get() : $daftarKampung;

        foreach ($targetKampungs as $kmp) {
            $waliIds = WaliMurid::where('is_active', true)
                ->where('kampung_id', $kmp->id)
                ->whereHas('murids', function ($q) use ($tahunPelajaranId) {
                    $q->where('status', 'Aktif')
                        ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        });
                })
                ->pluck('id');

            $countKk = $waliIds->count();
            if ($countKk === 0) continue;

            $tagihans = TagihanWaliMurid::where('tahun_pelajaran_id', $tahunPelajaranId)
                ->when($selectedMasterId, fn($q) => $q->where('pengaturan_tagihan_id', $selectedMasterId))
                ->whereIn('wali_murid_id', $waliIds)
                ->get();

            $lunasCount = $tagihans->where('status_bayar', 'Lunas')->whereNotIn('id', $tagihanPendingIds)->count();
            $belumLunasCount = $countKk - $lunasCount;
            $nominalLunas = (float) $tagihans->where('status_bayar', 'Lunas')->whereNotIn('id', $tagihanPendingIds)->sum('nominal_tagihan');
            $nominalTarget = $countKk * ($selectedMaster?->nominal ?? 0);
            $nominalSisa = max(0, $nominalTarget - $nominalLunas);
            $persen = $nominalTarget > 0 ? round(($nominalLunas / $nominalTarget) * 100, 1) : 0;

            $rekapKampung[] = [
                'id'            => $kmp->id,
                'kode'          => $kmp->kode,
                'nama'          => $kmp->nama_kampung,
                'total_kk'      => $countKk,
                'lunas'         => $lunasCount,
                'belum_lunas'   => $belumLunasCount,
                'persentase'    => $persen,
                'target_dana'   => $nominalTarget,
                'realisasi'     => $nominalLunas,
                'sisa_piutang'  => $nominalSisa,
            ];

            $totalSemuaKk += $countKk;
            $totalSemuaLunas += $lunasCount;
            $totalSemuaBelumLunas += $belumLunasCount;
            $totalNominalTarget += $nominalTarget;
            $totalNominalMasuk += $nominalLunas;
            $totalNominalPiutang += $nominalSisa;
        }

        $persenTotal = $totalNominalTarget > 0 ? round(($totalNominalMasuk / $totalNominalTarget) * 100, 1) : 0;

        return view('ikhtibar.tagihan.laporan', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'kegiatan',
            'daftarKampung',
            'selectedKampungId',
            'kampungTerpilih',
            'masterTagihans',
            'selectedMasterId',
            'selectedMaster',
            'rekapKampung',
            'totalSemuaKk',
            'totalSemuaLunas',
            'totalSemuaBelumLunas',
            'totalNominalTarget',
            'totalNominalMasuk',
            'totalNominalPiutang',
            'persenTotal'
        ));
    }

    /**
     * Modal Detail AJAX Tagihan Wali Murid Ikhtibar
     */
    public function detail($id)
    {
        $wali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) {
                $q->where('status', 'Aktif')->with('ruangans');
            },
            'tagihanWaliMurids' => function ($q) {
                $q->with(['pengaturanTagihan', 'pembayaranTagihan', 'pembayaranIkhtibars.diterimaOleh']);
            }
        ])->findOrFail($id);

        return view('ikhtibar.tagihan.modal-detail', compact('wali'));
    }

    /**
     * Cetak Kwitansi Pembayaran Ikhtibar Resmi
     */
    public function cetakKwitansi($id)
    {
        $tagihan = TagihanWaliMurid::with([
            'waliMurid.kampung',
            'waliMurid.murids' => function ($q) {
                $q->where('status', 'Aktif')->with('ruangans');
            },
            'pengaturanTagihan',
            'pembayaranTagihan',
            'pembayaranIkhtibars.diterimaOleh',
            'tahunPelajaran'
        ])->findOrFail($id);

        $kegiatan = KegiatanIkhtibar::firstOrCreate(
            ['tahun_pelajaran_id' => $tagihan->tahun_pelajaran_id],
            [
                'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                'ikhtibar_ke'     => '52 Th',
                'status_kegiatan' => 'Persiapan',
            ]
        );

        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Ketua%')
            ->where('is_active', true)
            ->first();

        $bendahara = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Bendahara%')
            ->where('is_active', true)
            ->first();

        return view('ikhtibar.tagihan.cetak-kwitansi', compact('tagihan', 'kegiatan', 'ketua', 'bendahara'));
    }

    /**
     * Cetak Kwitansi Cicilan Ikhtibar Spesifik Termin
     */
    public function cetakKwitansiCicilan($pembayaran_ikhtibar_id)
    {
        $pembayaranIkhtibar = PembayaranTagihanIkhtibar::with([
            'tagihanWaliMurid.waliMurid.kampung',
            'tagihanWaliMurid.waliMurid.murids.ruangans',
            'tagihanWaliMurid.pengaturanTagihan',
            'tagihanWaliMurid.pembayaranIkhtibars.diterimaOleh',
            'tahunPelajaran',
            'kegiatanIkhtibar',
            'diterimaOleh'
        ])->findOrFail($pembayaran_ikhtibar_id);

        $tagihan = $pembayaranIkhtibar->tagihanWaliMurid;
        $kegiatan = $pembayaranIkhtibar->kegiatanIkhtibar ?: KegiatanIkhtibar::firstOrCreate(
            ['tahun_pelajaran_id' => $tagihan->tahun_pelajaran_id],
            [
                'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                'ikhtibar_ke'     => '52 Th',
                'status_kegiatan' => 'Persiapan',
            ]
        );

        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Ketua%')
            ->where('is_active', true)
            ->first();

        $bendahara = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Bendahara%')
            ->where('is_active', true)
            ->first();

        return view('ikhtibar.tagihan.cetak-kwitansi', compact('tagihan', 'kegiatan', 'ketua', 'bendahara', 'pembayaranIkhtibar'));
    }

    /**
     * Cetak Kwitansi Ikhtibar Publik (Signed URL)
     */
    public function cetakKwitansiPublik($id)
    {
        return $this->cetakKwitansi($id);
    }

    /**
     * Cetak Kwitansi Cicilan Ikhtibar Publik (Signed URL)
     */
    public function cetakKwitansiCicilanPublik($pembayaran_ikhtibar_id)
    {
        return $this->cetakKwitansiCicilan($pembayaran_ikhtibar_id);
    }

    /**
     * Cetak Lembar Rekapitulasi Tagihan Ikhtibar Format A4
     */
    public function cetakRekap(Request $request)
    {
        $tahunId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $tahunPelajaran = TahunPelajaran::findOrFail($tahunId);

        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunId);
        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($defaultMaster?->id ?? null);
        $selectedMaster = $masterTagihans->firstWhere('id', $selectedMasterId) ?? $defaultMaster;

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
            });

        if ($kampungId) {
            $queryWali->where('kampung_id', $kampungId);
        }

        $walis = $queryWali->get()->sortBy(function ($w) {
            $kode = $w->kampung ? str_pad($w->kampung->kode, 4, '0', STR_PAD_LEFT) : '9999';
            return $kode . '_' . ($w->nama_kepala_keluarga ?? '');
        });

        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Ketua%')
            ->where('is_active', true)
            ->first();

        $bendahara = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Bendahara%')
            ->where('is_active', true)
            ->first();

        return view('ikhtibar.tagihan.cetak-rekap', compact(
            'walis',
            'tahunPelajaran',
            'kegiatan',
            'selectedMaster',
            'kampung',
            'ketua',
            'bendahara'
        ));
    }

    /**
     * Cetak Buku Leger Kendali Tagihan Ikhtibar Per Dusun (A4 Landscape)
     */
    public function cetakLeger(Request $request, $kegiatanId = null)
    {
        if ($kegiatanId) {
            $kegiatan = KegiatanIkhtibar::with(['tahunPelajaran', 'pengaturanTagihan'])->findOrFail($kegiatanId);
            $tahunId = $kegiatan->tahun_pelajaran_id;
        } else {
            $tahunId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
            [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunId);
        }

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($kegiatan->pengaturan_tagihan_id ?? null);
        $selectedMaster = PengaturanTagihan::find($selectedMasterId) ?? $kegiatan->pengaturanTagihan;

        $kampungId = $request->filled('kampung_id') && $request->kampung_id !== 'semua' ? $request->kampung_id : null;
        $kampung = $kampungId ? Kampung::find($kampungId) : null;

        $queryWali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    })
                    ->with('ruangans');
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
            });

        if ($kampungId) {
            $queryWali->where('kampung_id', $kampungId);
        }

        $walis = $queryWali->get()->sortBy(function ($w) {
            $kode = $w->kampung ? str_pad($w->kampung->kode, 4, '0', STR_PAD_LEFT) : '9999';
            return $kode . '_' . ($w->nama_kepala_keluarga ?? '');
        });

        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Ketua%')
            ->where('is_active', true)
            ->first();

        $bendahara = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Bendahara%')
            ->where('is_active', true)
            ->first();

        return view('ikhtibar.tagihan.cetak-leger', compact(
            'walis',
            'kegiatan',
            'selectedMaster',
            'kampung',
            'ketua',
            'bendahara'
        ));
    }

    /**
     * Cetak Kartu Penarapan / Tagihan Ikhtibar Wali Murid (Single KK)
     */
    public function cetakKartu(Request $request, $wali_id, $tahun_id = null)
    {
        $tahun_id = $tahun_id ?: ($request->tahun_id ?: TahunPelajaran::where('is_active', true)->value('id'));
        $tahunPelajaran = TahunPelajaran::findOrFail($tahun_id);
        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahun_id);

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($kegiatan->pengaturan_tagihan_id ?? null);
        $selectedMaster = PengaturanTagihan::find($selectedMasterId) ?? $defaultMaster;

        $wali = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahun_id) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahun_id) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahun_id);
                    })
                    ->with(['ruangans' => function ($rq) use ($tahun_id) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahun_id);
                    }]);
            },
            'tagihanWaliMurids' => function ($q) use ($tahun_id, $selectedMaster) {
                $q->where('tahun_pelajaran_id', $tahun_id)
                    ->when($selectedMaster, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMaster->id))
                    ->with(['pembayaranTagihan', 'pembayaranIkhtibars.diterimaOleh']);
            }
        ])->findOrFail($wali_id);

        $wali->current_tagihan = $wali->tagihanWaliMurids->first();
        $walis = collect([$wali]);
        $filterKampung = $wali->kampung;

        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Ketua%')
            ->where('is_active', true)
            ->first();

        $sekretaris = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Sekretaris%')
            ->where('is_active', true)
            ->first();

        $bendahara = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Bendahara%')
            ->where('is_active', true)
            ->first();

        return view('ikhtibar.tagihan.cetak-kartu', compact(
            'walis',
            'tahunPelajaran',
            'kegiatan',
            'selectedMaster',
            'filterKampung',
            'ketua',
            'sekretaris',
            'bendahara'
        ));
    }

    /**
     * Cetak Kartu Penarapan / Tagihan Ikhtibar Wali Murid (Massal / Per Dusun / Per Kampung)
     */
    public function cetakKartuMassal(Request $request)
    {
        $tahunId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $tahunPelajaran = TahunPelajaran::findOrFail($tahunId);

        [$kegiatan, $masterTagihans, $defaultMaster] = $this->getKegiatanAndMaster($tahunId);

        $selectedMasterId = $request->pengaturan_tagihan_id ?? ($kegiatan->pengaturan_tagihan_id ?? null);
        $selectedMaster = PengaturanTagihan::find($selectedMasterId) ?? $defaultMaster;

        $kampungId = $request->filled('kampung_id') && $request->kampung_id !== 'semua' ? $request->kampung_id : null;
        $filterKampung = $kampungId ? Kampung::find($kampungId) : null;

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
            'tagihanWaliMurids' => function ($q) use ($tahunId, $selectedMaster) {
                $q->where('tahun_pelajaran_id', $tahunId)
                    ->when($selectedMaster, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMaster->id))
                    ->with(['pembayaranTagihan', 'pembayaranIkhtibars.diterimaOleh']);
            }
        ])
            ->where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunId) {
                        $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                    });
            });

        if ($kampungId) {
            $queryWali->where('kampung_id', $kampungId);
        }

        // Filter jika memilih spesifik wali_ids (dari centang tabel)
        if ($request->filled('wali_ids') && is_array($request->wali_ids)) {
            $queryWali->whereIn('id', $request->wali_ids);
        }

        // Filter status penerbitan jika ada
        if ($request->filled('status_terbit')) {
            if ($request->status_terbit === 'Belum Terbit') {
                $queryWali->whereDoesntHave('tagihanWaliMurids', function ($q) use ($tahunId, $selectedMaster) {
                    $q->where('tahun_pelajaran_id', $tahunId)
                        ->when($selectedMaster, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMaster->id));
                });
            } elseif ($request->status_terbit === 'Terbit (Belum Lunas)') {
                $queryWali->whereHas('tagihanWaliMurids', function ($q) use ($tahunId, $selectedMaster) {
                    $q->where('tahun_pelajaran_id', $tahunId)
                        ->when($selectedMaster, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMaster->id))
                        ->where('status_bayar', '!=', 'Lunas');
                });
            } elseif ($request->status_terbit === 'Terbit & Lunas') {
                $queryWali->whereHas('tagihanWaliMurids', function ($q) use ($tahunId, $selectedMaster) {
                    $q->where('tahun_pelajaran_id', $tahunId)
                        ->when($selectedMaster, fn($sq) => $sq->where('pengaturan_tagihan_id', $selectedMaster->id))
                        ->where('status_bayar', 'Lunas');
                });
            }
        }

        $walis = $queryWali->get()->sortBy(function ($w) {
            $kode = $w->kampung ? str_pad($w->kampung->kode, 4, '0', STR_PAD_LEFT) : '9999';
            return $kode . '_' . ($w->nama_kepala_keluarga ?? '');
        });

        foreach ($walis as $wali) {
            $wali->current_tagihan = $wali->tagihanWaliMurids->first();
        }

        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Ketua%')
            ->where('is_active', true)
            ->first();

        $sekretaris = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Sekretaris%')
            ->where('is_active', true)
            ->first();

        $bendahara = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('jabatan', 'LIKE', '%Bendahara%')
            ->where('is_active', true)
            ->first();

        return view('ikhtibar.tagihan.cetak-kartu', compact(
            'walis',
            'tahunPelajaran',
            'kegiatan',
            'selectedMaster',
            'filterKampung',
            'ketua',
            'sekretaris',
            'bendahara'
        ));
    }

    /**
     * Legacy Method: Terbitkan Massal
     */
    public function terbitkanMassalLegacy($kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);
        $request = new Request([
            'tahun_id'              => $kegiatan->tahun_pelajaran_id,
            'pengaturan_tagihan_id' => $kegiatan->pengaturan_tagihan_id,
        ]);
        return $this->terbitkan($request);
    }

    /**
     * Legacy Method: Modal Bayar
     */
    public function modalBayar($tagihanId)
    {
        $tagihan = TagihanWaliMurid::with(['waliMurid.kampung', 'pengaturanTagihan', 'waliMurid.murids.tabungans'])->findOrFail($tagihanId);
        return view('ikhtibar.tagihan.modal-bayar', compact('tagihan'));
    }

    /**
     * Legacy Method: Proses Bayar
     */
    public function prosesBayar(Request $request, $tagihanId)
    {
        $request->merge(['tagihan_ids' => [$tagihanId]]);
        return $this->bayar($request);
    }
}
