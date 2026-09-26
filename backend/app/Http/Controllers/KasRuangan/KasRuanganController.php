<?php

namespace App\Http\Controllers\KasRuangan;

use App\Http\Controllers\Controller;
use App\Models\KasRuangan\PembayaranKasRuangan;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasRuanganController extends Controller
{

    public function indexKasRuangan(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::select('ruangans.*')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->berdasarkanHakAkses()
            ->where('ruangans.tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('ruangans.nama_ruangan', 'asc')
            ->get();

        // 2. Tangkap ID ruangan jika difilter
        $ruanganId = $request->ruangan_id;

        // 3. Query utama (ditambah Join dan Select agar tidak ada ID yang bentrok)
        $query = Ruangan::with(['pengaturanKas', 'level', 'waliRuangan'])
            ->select('ruangans.*') // Wajib agar hasil select tidak tertimpa ID dari tabel levels
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->berdasarkanHakAkses()
            ->where('ruangans.tahun_pelajaran_id', $tahunPelajaranId)
            ->withSum('pembayaranKas as total_terkumpul', 'jumlah_bayar')
            ->withSum(['setoranKas as total_disetor' => fn($q) => $q->where('status', 'Diterima')], 'jumlah_setor')
            ->withCount('murids');

        // 4. Terapkan filter ruangan jika user memilihnya
        if ($ruanganId) {
            $query->where('ruangans.id', $ruanganId); // Tambahkan prefix ruangans.
        }

        // 5. Tampilkan hasil akhir
        $ruangans = $query->orderBy('levels.urutan_level', 'asc')
            ->orderBy('ruangans.nama_ruangan', 'asc')
            ->get();

        // 6. Ringkasan Statistik Global untuk Banner Header
        $totalKasTerkumpul = $ruangans->sum('total_terkumpul');
        $totalKasDisetor = $ruangans->sum('total_disetor');
        $totalSisaKas = $totalKasTerkumpul - $totalKasDisetor;
        $totalMurid = $ruangans->sum('murids_count');
        $totalRuangan = $ruangans->count();

        return view('kas-ruangan.kas-ruangan.index', compact(
            'ruangans',
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganId',
            'totalKasTerkumpul',
            'totalKasDisetor',
            'totalSisaKas',
            'totalMurid',
            'totalRuangan'
        ));
    }

    public function showKasRuangan($ruangan_id)
    {
        $ruangan = Ruangan::with(['pengaturanKas', 'level', 'waliRuangan'])
            ->withSum('pembayaranKas as total_terkumpul', 'jumlah_bayar')
            ->withSum(['setoranKas as total_disetor' => fn($q) => $q->where('status', 'Diterima')], 'jumlah_setor')
            ->findOrFail($ruangan_id);

        $murids = $ruangan->murids()->with(['pembayaranKas' => function ($query) use ($ruangan_id) {
            $query->where('ruangan_id', $ruangan_id);
        }])->get();

        return view('kas-ruangan.kas-ruangan.show', compact('ruangan', 'murids'));
    }

    public function simpanPembayaran(Request $request)
    {
        $request->validate([
            'ruangan_id'   => 'required|exists:ruangans,id',
            'murid_id'     => 'required|exists:murids,id',
            'jumlah_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
        ]);

        PembayaranKasRuangan::create([
            'ruangan_id'    => $request->ruangan_id,
            'murid_id'      => $request->murid_id,
            'jumlah_bayar'  => $request->jumlah_bayar,
            'tanggal_bayar' => $request->tanggal_bayar,
            'diinput_oleh'  => Auth::id(),
            'is_disetor'    => false, // Default false, nanti disetor lewat menu "Setoran"
        ]);

        return redirect()->back()->with('success', 'Pembayaran kas berhasil dicatat!');
    }

    /**
     * Menampilkan Halaman Riwayat Cicilan Murid Tertentu
     */
    public function riwayat($ruangan_id, $murid_id)
    {
        $ruangan = Ruangan::findOrFail($ruangan_id);
        $murid = Murid::findOrFail($murid_id);

        // Ambil riwayat pembayaran milik murid ini
        $riwayats = PembayaranKasRuangan::where('murid_id', $murid_id)
            ->where('ruangan_id', $ruangan_id) // Opsional, untuk memastikan data akurat
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('kas-ruangan.kas-ruangan.riwayat', compact('ruangan', 'murid', 'riwayats'));
    }


    public function updatePembayaran(Request $request, $id)
    {
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
        ]);

        $pembayaran = PembayaranKasRuangan::findOrFail($id);

        // Validasi Keamanan: Jangan izinkan edit jika uang sudah disetor ke Madrasah
        if ($pembayaran->is_disetor) {
            return redirect()->back()->with('error', 'Ditolak! Uang ini sudah disetorkan ke Bendahara.');
        }
        $pembayaran->update([
            'jumlah_bayar'  => $request->jumlah_bayar,
            'tanggal_bayar' => $request->tanggal_bayar,
            'diinput_oleh' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Riwayat pembayaran berhasil diperbarui!');
    }

    public function destroyPembayaran($id)
    {
        $pembayaran = PembayaranKasRuangan::findOrFail($id);

        if ($pembayaran->is_disetor) {
            return redirect()->back()->with('error', 'Ditolak! Uang ini sudah disetorkan ke Bendahara.');
        }

        $pembayaran->delete();

        return redirect()->back()->with('success', 'Data cicilan berhasil dihapus!');
    }
}
