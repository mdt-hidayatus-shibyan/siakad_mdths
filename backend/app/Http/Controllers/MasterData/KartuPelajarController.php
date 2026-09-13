<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Kepengurusan\Pengurus;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Illuminate\Http\Request;

class KartuPelajarController extends Controller
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

        $daftarRuangan = Ruangan::berdasarkanHakAkses()->where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $murids = collect();

        if ($request->filled('ruangan_id')) {
            $ruanganTerpilih = Ruangan::with('level')->berdasarkanHakAkses()->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
            }
        }

        return view('kartu-pelajar.index', compact('daftarTahun', 'tahunPelajaranId', 'daftarRuangan', 'ruanganTerpilih', 'murids'));
    }

    public function cetak(Request $request)
    {
        $request->validate([
            'murid_ids' => 'required|array|min:1',
        ]);
        $murids = Murid::with(['ruangans' => function ($q) use ($request) {
            $q->where('ruangan_id', $request->ruangan_id_cetak);
        }])->whereIn('id', $request->murid_ids)->get();
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        return view('kartu-pelajar.cetak', compact('murids', 'pengasuh'));
    }

    /**
     * Menampilkan Modal Form Upload & Crop Foto Murid (AJAX Partial)
     */
    public function modalUpload($ruangan_id, $murid_id)
    {
        $ruangan = Ruangan::findOrFail($ruangan_id);
        $murid = Murid::with('waliMurid.kampung')->findOrFail($murid_id);

        return view('rombongan-belajar.modal-upload-foto', compact('ruangan', 'murid'));
    }
}
