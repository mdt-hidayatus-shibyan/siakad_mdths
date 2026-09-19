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
                $masterBiayas = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where(function ($q) use ($ruanganTerpilih) {
                        $q->where('level_id', $ruanganTerpilih->level_id);
                    })->get();

                if ($request->pengaturan_tagihan_id) {
                    $jenisTagihanTerpilih = PengaturanTagihan::find($request->pengaturan_tagihan_id);
                    $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
                    $ruanganTerpilih->setRelation('murids', $murids);

                    $tagihanExisting = TagihanMurid::whereIn('murid_id', $murids->pluck('id'))
                        ->where('pengaturan_tagihan_id', $jenisTagihanTerpilih->id)
                        ->get()
                        ->groupBy('murid_id');
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

    // 6. Laporan Penerimaan Pembayaran (Versi Filter Harian & Jenis Biaya)
    public function laporan(Request $request)
    {
        // 1. Setup Data Filter (Tahun & Ruangan)
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();
        $ruanganTerpilih = null;

        // 2. Setup Parameter Default (Tanggal & Jenis)
        $startDate = $request->start_date ?? now()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $jenisBiaya = $request->jenis_biaya ?? 'Lainnya'; // Default langsung arahkan ke 'Lainnya' jika untuk ujian

        // 3. Query Dasar Buku Kas (Transaksi Pembayaran)
        $query = PembayaranTagihan::whereDate('tanggal_bayar', '>=', $startDate)
            ->whereDate('tanggal_bayar', '<=', $endDate);

        // ==========================================
        // FILTER 1: Kategori Selain SPP (Ujian / Lainnya)
        // ==========================================
        if ($jenisBiaya === 'Lainnya') {
            $query->whereHas('tagihanMurids', function ($q) {
                $q->where('nama_tagihan_spesifik', 'NOT LIKE', '%SPP%')
                    ->where('nama_tagihan_spesifik', 'NOT LIKE', '%Syahriyah%');
            });
        }

        // ==========================================
        // FILTER 2: Berdasarkan Ruangan (Baru)
        // ==========================================
        if ($request->filled('ruangan_id')) {
            $ruanganTerpilih = Ruangan::find($request->ruangan_id);

            // Memastikan transaksi tersebut dimiliki oleh murid di ruangan yang dipilih
            $query->whereHas('tagihanMurids.murid', function ($q) use ($request) {
                $q->where('ruangan_id', $request->ruangan_id);
            });
        }

        // 4. Kalkulasi Ringkasan Kas
        $totalPendapatan = $query->sum('total_nominal');
        $totalTransaksi = $query->count();

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
            'daftarTahun',       // <- Wajib dikirim untuk Filter Tahun Pelajaran
            'tahunPelajaranId',  // <- Wajib dikirim
            'daftarRuangan',     // <- Wajib dikirim untuk Filter Ruangan
            'ruanganTerpilih'
        ));
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
