<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;

use App\Models\Administrator;
use App\Models\BulanHijriyah;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Murid;
use App\Models\PembayaranTagihan;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Services\PembayaranTagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranTagihanController extends Controller
{
    protected $pembayaranService;

    public function __construct(PembayaranTagihanService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
    }

    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $muridTerpilih = null;
        $semuaTagihan = collect();

        if ($request->search_nism) {
            $muridTerpilih = Murid::where('nism', $request->search_nism)->with(['waliMurid'])->first();

            if ($muridTerpilih) {
                $ruanganTerpilih = $muridTerpilih->ruangans()->where('ruangans.tahun_pelajaran_id', $tahunPelajaranId)->first();

                if ($ruanganTerpilih) {
                    // Tarik SEMUA tagihan (Lunas maupun Belum Lunas) menjadi satu kesatuan
                    $semuaTagihan = TagihanMurid::with('pengaturanTagihan')
                        ->where('murid_id', $muridTerpilih->id)
                        ->where('ruangan_id', $ruanganTerpilih->id)
                        ->where(function ($query) {
                            // Hanya ambil yang mengandung kata SPP atau Syahriyah
                            $query->where('nama_tagihan_spesifik', 'LIKE', '%SPP%')
                                ->orWhere('nama_tagihan_spesifik', 'LIKE', '%Syahriyah%');
                        })
                        ->orderBy('id', 'asc') // Urutkan berdasarkan ID / Waktu dibuat
                        ->get();
                }
            } else {
                session()->now('error', 'NISM "' . $request->search_nism . '" tidak ditemukan di sistem.');
            }
        }

        return view('tagihan-murid.pembayaran-tagihan', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganTerpilih',
            'muridTerpilih',
            'semuaTagihan'
        ));
    }



    public function proses(Request $request)
    {
        $request->validate([
            'tagihan_ids' => 'required|array',
            'tagihan_ids.*' => 'exists:tagihan_murids,id',
            'murid_id' => 'required|exists:murids,id',
        ]);

        try {
            $pembayaran = $this->pembayaranService->prosesPembayaranWali(
                $request->murid_id,
                $request->tagihan_ids
            );

            return redirect()->back()->with('success', 'Transaksi berhasil! Pembayaran sebesar Rp ' . number_format($pembayaran->total_nominal, 0, ',', '.') . ' dari Wali Murid telah dicatat dengan No Kwitansi: ' . $pembayaran->no_transaksi);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }
    public function batalTransaksi($id)
    {
        DB::beginTransaction();
        try {
            $tagihan = TagihanMurid::findOrFail($id);
            $pembayaranId = $tagihan->pembayaran_tagihan_id;

            $statusBaru = 'Belum Lunas';

            // 2. Periksa Kwitansi Induknya
            if ($pembayaranId) {
                $pembayaran = \App\Models\PembayaranTagihan::find($pembayaranId);

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

    // =========================================================================
    // 2. KASIR MATRIKS / LEGER MASSAL
    // =========================================================================
    public function indexLeger(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $jenisTagihanTerpilih = null;
        $masterBiayas = collect();
        $murids = collect();
        $tagihanExisting = collect();

        // 1. PERUBAHAN: Tarik bulan hijriyah dinamis dari database
        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('urutan', 'asc')
            ->get();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with(['level', 'murids'])->find($request->ruangan_id);

            if ($ruanganTerpilih) {
                $masterBiayas = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where(function ($q) {
                        $q->whereNull('sasaran')->orWhere('sasaran', 'murid');
                    })
                    ->where(function ($q) use ($ruanganTerpilih) {
                        $q->whereNull('level_id');
                    })->get();

                if ($request->pengaturan_tagihan_id) {
                    $jenisTagihanTerpilih = PengaturanTagihan::find($request->pengaturan_tagihan_id);
                    $murids = $ruanganTerpilih->murids;

                    // 2. PERUBAHAN EAGER LOADING: Tarik juga relasi bulanHijriyah dan semester 
                    // agar di Blade nanti bisa dibaca lebih cepat.
                    $tagihanExisting = TagihanMurid::with(['bulanHijriyah', 'semester'])
                        ->whereIn('murid_id', $murids->pluck('id'))
                        ->where('pengaturan_tagihan_id', $jenisTagihanTerpilih->id)
                        ->get()
                        ->groupBy('murid_id');
                }
            }
        }

        return view('tagihan-murid.pembayaran-tagihan-leger', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'masterBiayas',
            'ruanganTerpilih',
            'jenisTagihanTerpilih',
            'murids',
            'tagihanExisting',
            'bulanHijriyah'
        ));
    }

    // 5. Eksekusi Pembayaran Leger Massal
    public function prosesLeger(Request $request)
    {
        $request->validate([
            'tagihan_ids'   => 'required|array',
            'tagihan_ids.*' => 'exists:tagihan_murids,id',
        ]);

        try {
            $totalCount = $this->pembayaranService->prosesLegerPembayaran($request->tagihan_ids);

            return redirect()->back()->with('success', 'Pelunasan massal metode leger berhasil diproses! Sebanyak ' . $totalCount . ' tagihan telah dilunasi dan kwitansi telah otomatis diterbitkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 3. KASIR KHUSUS DONATUR (YATIM) 🔥
    // =========================================================================
    public function indexDonatur(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        // Ini sekarang menangkap ID dari tabel BulanHijriyah, bukan lagi teks namanya
        $bulanTerpilih = $request->bulan;
        $tagihanPending = collect();

        // 1. Ambil daftar bulan (disamakan dengan metode indexLeger agar lebih sederhana & cepat)
        $daftarBulan = \App\Models\BulanHijriyah::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('urutan', 'asc')
            ->get();

        if ($bulanTerpilih) {
            $tagihanPending = TagihanMurid::with(['murid', 'ruangan', 'pembayaranTagihan'])
                // Memfilter berdasarkan tahun ajaran di kelas/ruangannya
                ->whereHas('ruangan', function ($query) use ($tahunPelajaranId) {
                    $query->where('tahun_pelajaran_id', $tahunPelajaranId);
                })
                // PERUBAHAN UTAMA: Memfilter langsung menggunakan ID bulan, pencarian 100% akurat!
                ->where('bulan_hijriyah_id', $bulanTerpilih)
                ->where(function ($query) {
                    // Ambil yang statusnya 'Ditanggung Donatur'
                    $query->where('status_bayar', 'Ditanggung Donatur')
                        // ATAU ambil yang statusnya sudah 'Lunas', tapi yang bayar adalah 'Donatur'
                        ->orWhere(function ($q2) {
                            $q2->where('status_bayar', 'Lunas')
                                ->whereHas('pembayaranTagihan', function ($q3) {
                                    $q3->where('tipe_pembayar', 'Donatur');
                                });
                        });
                })
                ->orderByRaw("FIELD(status_bayar, 'Ditanggung Donatur', 'Lunas')")
                ->get();
        }

        return view('tagihan-murid.pembayaran-tagihan-donatur', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarBulan',
            'tagihanPending',
            'bulanTerpilih'
        ));
    }

    public function prosesDonatur(Request $request)
    {
        $request->validate([
            'tagihan_ids'       => 'required|array|min:1',
            'tagihan_ids.*'     => 'exists:tagihan_murids,id',
            'nama_pembayar'     => 'required|string|max:255',
            'metode_pembayaran' => 'required|string',
            'tanggal_bayar'     => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            // 1. Kunci Baris Tagihan (Lock For Update) untuk mencegah dobel proses
            $tagihans = TagihanMurid::whereIn('id', $request->tagihan_ids)->lockForUpdate()->get();

            $adaYangLunas = $tagihans->where('status_bayar', 'Lunas')->count();
            if ($adaYangLunas > 0) {
                return redirect()->back()->with('error', 'Gagal memproses! Beberapa tagihan yang Anda centang ternyata sudah berstatus Lunas.');
            }

            $totalNominal = $tagihans->sum('nominal_tagihan');

            // 2. Generate Nomor Kwitansi Otomatis
            $bulan = \Carbon\Carbon::parse($request->tanggal_bayar)->format('m');
            $tahun = \Carbon\Carbon::parse($request->tanggal_bayar)->format('Y');

            $kodeTagihan = $tagihans->first()->pengaturanTagihan->kode_tagihan ?? 'TGH';

            // Donatur menggunakan kode khusus karena tidak memiliki NISM
            $randomCode = mt_rand(10000, 99999); // Angka acak 5 digit
            $noKwitansi = 'TRX/' . $kodeTagihan . '/DON/' . $tahun . '/' . $bulan . '/' . $randomCode;

            // 3. Catat ke Tabel Pembayarans
            $pembayaran = PembayaranTagihan::create([
                'no_transaksi'      => $noKwitansi,
                'tanggal_bayar'     => $request->tanggal_bayar,
                'tipe_pembayar'     => 'Donatur',
                'nama_pembayar'     => $request->nama_pembayar,
                'alamat_pembayar'   => $request->alamat_pembayar ?? null,
                'metode_pembayaran' => $request->metode_pembayaran,
                'rekening_penerima' => $request->rekening_penerima ?? null,
                'total_nominal'     => $totalNominal,
                'catatan'           => $request->catatan ?? null,
            ]);

            // 4. Update Status Tagihan
            TagihanMurid::whereIn('id', $request->tagihan_ids)->update([
                'status_bayar'          => 'Lunas',
                'pembayaran_tagihan_id' => $pembayaran->id,
                'updated_at'            => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Alhamdulillah! Donasi dari {$request->nama_pembayar} senilai Rp " . number_format($totalNominal, 0, ',', '.') . " berhasil diproses.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses donasi: ' . $e->getMessage());
        }
    }


    // 4. Cetak Rekap SPP 1 Tahun Full
    public function cetakRekapSpp($murid_id, $tahun_id)
    {
        $murid = Murid::with('waliMurid')->findOrFail($murid_id);
        $tahun = TahunPelajaran::findOrFail($tahun_id);

        // Tarik semua tagihan SPP milik anak ini di tahun terpilih yang sudah LUNAS
        $tagihanLunas = TagihanMurid::with('pembayaranTagihan')
            ->where('murid_id', $murid_id)
            ->where('status_bayar', 'Lunas')
            ->where(function ($query) {
                $query->where('nama_tagihan_spesifik', 'LIKE', '%SPP%')
                    ->orWhere('nama_tagihan_spesifik', 'LIKE', '%Syahriyah%');
            })
            // Tambahkan filter jika ada relasi ke tahun_pelajaran_id di tabel tagihan/ruangan
            ->orderBy('id', 'asc')
            ->get();

        // Hitung total uang yang sudah masuk
        $totalDibayar = $tagihanLunas->sum('nominal_tagihan');

        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $sekretaris = Pengurus::getAktifByJabatan('Sekretaris Jenderal');

        return view('cetak-baru.cetak_rekap_spp', compact('murid', 'tahun', 'tagihanLunas', 'totalDibayar', 'sekretaris', 'pengasuh'));
    }


    // 6. Laporan Penerimaan Pembayaran (Versi Filter Harian & Jenis Biaya)
    public function laporan(Request $request)
    {
        // 1. Default tanggal hari ini (Format: YYYY-MM-DD) untuk mempermudah laporan harian
        $startDate = $request->start_date ?? now()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        // Kunci parameter ke SPP (berguna jika variabel ini dipakai untuk judul/label di view)
        $jenisBiaya = 'SPP';

        // 2. Query dasar Buku Kas berdasarkan rentang tanggal
        $query = PembayaranTagihan::whereDate('tanggal_bayar', '>=', $startDate)
            ->whereDate('tanggal_bayar', '<=', $endDate);

        // 3. KUNCI FILTER: Wajib mengandung kata SPP atau Syahriyah di detail tagihannya
        $query->whereHas('tagihanMurids', function ($q) {
            $q->where('nama_tagihan_spesifik', 'LIKE', '%SPP%')
                ->orWhere('nama_tagihan_spesifik', 'LIKE', '%Syahriyah%');
        });

        // 4. Kalkulasi Ringkasan Kas (Otomatis hanya menghitung yang lolos filter di atas)
        $totalPendapatan = $query->sum('total_nominal');
        $totalTransaksi = $query->count();

        // 5. Eksekusi Query
        $laporans = $query->orderBy('tanggal_bayar', 'desc')->orderBy('id', 'desc')->get();

        return view('tagihan-murid.laporan-pembayaran', compact(
            'laporans',
            'totalPendapatan',
            'totalTransaksi',
            'startDate',
            'endDate',
            'jenisBiaya'
        ));
    }


    public function cetakDonatur($id)
    {
        $pembayaran = PembayaranTagihan::with(['tagihanMurids.murid', 'tagihanMurids.ruangan'])->findOrFail($id);

        // Ambil nama bulan dari salah satu tagihan (Contoh: "SPP Muharram" -> "Muharram")
        $sampleTagihan = $pembayaran->tagihanMurids->first();
        $bulanTagihan = $sampleTagihan ? str_replace(['SPP ', 'Syahriyah '], '', $sampleTagihan->nama_tagihan_spesifik) : 'Berbagai Bulan';
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $sekretaris = Pengurus::getAktifByJabatan('Sekretaris Jenderal');

        return view('cetak-baru.cetak_donatur', compact('pembayaran', 'bulanTagihan', 'sekretaris', 'pengasuh'));
    }

    // 7. Cetak Kwitansi Pembayaran Per Transaksi (SPP / Syahriyah / Tagihan Lainnya)
    public function cetakKwitansi($id)
    {
        $pembayaran = PembayaranTagihan::with([
            'tagihanMurids.murid.waliMurid.kampung',
            'tagihanMurids.ruangan.tahunPelajaran',
            'tagihanMurids.bulanHijriyah',
            'tagihanMurids.semester',
        ])->findOrFail($id);

        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $bendahara = Pengurus::getAktifByJabatan('Bendahara') ?? Pengurus::getAktifByJabatan('Sekretaris Jenderal');
        $administrator = Administrator::where('user_id', auth()->id())->first()
            ?? Administrator::where('is_active', true)->whereNull('tingkat_id')->first()
            ?? Administrator::where('is_active', true)->first();

        return view('cetak-baru.cetak_kwitansi', compact('pembayaran', 'pengasuh', 'bendahara', 'administrator'));
    }
}
