<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\CatatanUstadz;
use App\Models\Ustadz;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class CatatanUstadzController extends Controller
{
    /**
     * Tampilkan Daftar Catatan Ustadz (Hanya Melihat / Read-Only untuk Admin)
     */
    public function index(Request $request)
    {
        $kategori = $request->query('kategori');
        $targetTipe = $request->query('target_tipe');
        $urgensi = $request->query('urgensi');
        $ustadzId = $request->query('ustadz_id');
        $search = $request->query('search');

        $query = CatatanUstadz::with(['ustadz', 'murid', 'ruangan', 'tahunPelajaran'])
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->when($targetTipe, fn($q) => $q->where('target_tipe', $targetTipe))
            ->when($urgensi, fn($q) => $q->where('tingkat_urgensi', $urgensi))
            ->when($ustadzId, fn($q) => $q->where('ustadz_id', $ustadzId))
            ->when($search, fn($q) => $q->search($search))
            ->latest();

        $catatans = $query->paginate(15)->withQueryString();

        // Statistik Ringkas
        $totalSemua = CatatanUstadz::count();
        $totalKeluhanMurid = CatatanUstadz::where('target_tipe', 'murid')->count();
        $totalMadrasah = CatatanUstadz::where('target_tipe', 'madrasah')->count();
        $totalUrgensiTinggi = CatatanUstadz::whereIn('tingkat_urgensi', ['Tinggi', 'Penting / Mendesak'])->count();

        $daftarUstadz = Ustadz::orderBy('nama_lengkap', 'asc')->get(['id', 'nama_lengkap', 'kode_ustadz']);

        $kategoriList = [
            'Keluhan Murid',
            'KBM & Perkembangan Akademik',
            'Fasilitas Madrasah',
            'Evaluasi & Saran',
            'Lainnya',
        ];

        return view('catatan-ustadz.index', compact(
            'catatans',
            'totalSemua',
            'totalKeluhanMurid',
            'totalMadrasah',
            'totalUrgensiTinggi',
            'daftarUstadz',
            'kategoriList',
            'kategori',
            'targetTipe',
            'urgensi',
            'ustadzId',
            'search'
        ));
    }

    /**
     * Tampilkan Detail Catatan Ustadz (Read-Only)
     */
    public function show($id)
    {
        $catatan = CatatanUstadz::with(['ustadz', 'murid', 'ruangan', 'tahunPelajaran'])->findOrFail($id);

        // Tandai otomatis waktu dibaca admin jika belum
        if (is_null($catatan->dibaca_admin_pada)) {
            $catatan->update(['dibaca_admin_pada' => now()]);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $catatan,
                'html'    => view('catatan-ustadz.detail-modal', compact('catatan'))->render(),
            ]);
        }

        return view('catatan-ustadz.show', compact('catatan'));
    }
}
