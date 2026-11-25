<?php

namespace App\Http\Controllers\Arsip;

use App\Http\Controllers\Controller;
use App\Models\Arsip\ArsipDokumen;
use App\Models\TahunPelajaran;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class ArsipIjazahController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data untuk Filter Dropdown
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;
        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();

        // 2. Query Utama khusus Ijazah (Madrasah & Ujian Al-Qur'an)
        $query = ArsipDokumen::with('pencetak')
            ->whereIn('tipe_dokumen', ['ijazah', 'ijazah_alquran'])
            ->orderBy('created_at', 'desc');

        // 3. Terapkan Filter
        if ($request->filled('jenis')) {
            if ($request->jenis === 'ijazah_alquran') {
                $query->where(function ($q) {
                    $q->where('tipe_dokumen', 'ijazah_alquran')
                        ->orWhere('snapshot_data->tipe_ijazah', 'ijazah_alquran');
                });
            } elseif ($request->jenis === 'ijazah_madrasah') {
                $query->where('tipe_dokumen', 'ijazah')
                    ->where(function ($q) {
                        $q->whereNull('snapshot_data->tipe_ijazah')
                            ->orWhere('snapshot_data->tipe_ijazah', '!=', 'ijazah_alquran');
                    });
            }
        }

        if ($request->filled('tahun_pelajaran')) {
            $query->where('snapshot_data->tahun_pelajaran', $request->tahun_pelajaran);
        }

        if ($request->filled('ruangan')) {
            $query->where('snapshot_data->nama_ruangan', $request->ruangan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('snapshot_data->nama_murid', 'LIKE', "%{$search}%")
                    ->orWhere('snapshot_data->nomor_dokumen', 'LIKE', "%{$search}%")
                    ->orWhere('snapshot_data->nomor_ijazah_alquran', 'LIKE', "%{$search}%")
                    ->orWhere('snapshot_data->nism', 'LIKE', "%{$search}%");
            });
        }

        $arsips = $query->paginate(15)->withQueryString();

        return view('arsip-dokumen.ijazah.index', compact('arsips', 'daftarTahun', 'daftarRuangan'));
    }
}
