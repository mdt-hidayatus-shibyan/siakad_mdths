<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Arsip\ArsipDokumen;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\RiwayatKenaikan;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;
use App\Repositories\MuridRuanganRepository;
use App\Services\ArsipService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RaporController extends Controller
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
        $ujianTerpilih = null;
        $daftarUjian = collect();
        $murids = collect();

        // Variabel tambahan untuk dikirim ke view
        $arsipRapor = collect();
        $riwayatKenaikans = collect();
        $isAkhirTahun = false;

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId, ['waliMurid']);
                $ruanganTerpilih->setRelation('murids', $murids);

                $levelNama = $ruanganTerpilih->level->nama_level ?? '';

                // Filter Ujian Berdasarkan Kelas
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId);

                if ($isKelasAkhir) {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }
                $daftarUjian = $queryUjian->get();

                if ($request->ujian_id) {
                    $ujianTerpilih = Ujian::find($request->ujian_id);

                    // Ekstrak semua ID murid untuk query massal
                    $muridIds = $murids->pluck('id')->toArray();

                    // 1. Ambil data Arsip secara massal lalu kelompokkan berdasarkan referensi_id (ID Murid)
                    $arsipRapor = \App\Models\Arsip\ArsipDokumen::where('tipe_dokumen', 'rapor_murid')
                        ->where('referensi_tipe', \App\Models\Murid::class)
                        ->whereIn('referensi_id', $muridIds)
                        ->whereJsonContains('snapshot_data->nama_ujian', $ujianTerpilih->nama_ujian)
                        ->get()
                        ->keyBy('referensi_id');

                    // 2. Cek apakah ini ujian akhir tahun MENGGUNAKAN tipe_ujian
                    $isAkhirTahun = in_array($ujianTerpilih->tipe_ujian, ['IMDA 2', 'IMNI']);

                    // 3. Jika ini ujian akhir tahun, tarik data Riwayat Kenaikan secara massal
                    if ($isAkhirTahun) {
                        $riwayatKenaikans = \App\Models\Ujian\RiwayatKenaikan::whereIn('murid_id', $muridIds)
                            ->where('tahun_pelajaran_id', $tahunPelajaranId)
                            ->get()
                            ->keyBy('murid_id');
                    }
                }
            }
        }

        return view('rapor.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ruanganTerpilih',
            'ujianTerpilih',
            'murids',
            'arsipRapor',
            'riwayatKenaikans',
            'isAkhirTahun'
        ));
    }

    // FITUR BARU: MENGESAHKAN DAN MEMBEKUKAN RAPOR (E-DOCUMENT)
    public function arsipkanRapor($murid_id, $ujian_id, ArsipService $arsipService)
    {
        try {
            // Kita serahkan seluruh beban pekerjaan ke ArsipService!
            $pesanSukses = $arsipService->prosesPengarsipan($murid_id, $ujian_id);

            return back()->with('success', $pesanSukses);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function arsipkanBulk(Request $request, ArsipService $arsipService)
    {
        $request->validate([
            'ujian_id' => 'required',
            'selected_murid' => 'required|array|min:1',
        ]);

        try {
            $berhasil = 0;
            // Looping dan sahkan rapor murid yang dicentang satu per satu
            foreach ($request->selected_murid as $murid_id) {
                $arsipService->prosesPengarsipan($murid_id, $request->ujian_id);
                $berhasil++;
            }

            return back()->with('success', "Berhasil mengesahkan $berhasil dokumen rapor murid secara permanen!");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }


    public function cetakArsip($id)
    {
        // Cari arsip berdasarkan UUID
        $arsip = ArsipDokumen::findOrFail($id);

        // Pastikan ini adalah dokumen rapor
        if ($arsip->tipe_dokumen === 'rapor_murid') {

            // Opsional: Ambil data snapshot ke dalam variabel agar lebih mudah diketik di Blade
            $data = $arsip->snapshot_data;

            // Panggil view khusus arsip rapor
            return view('cetak-baru.cetak_rapor_arsip', compact('arsip', 'data'));
        }

        return abort(404, 'Jenis dokumen tidak didukung.');
    }

    public function downloadArsip($id)
    {
        $arsip = ArsipDokumen::findOrFail($id);

        if ($arsip->tipe_dokumen === 'rapor_murid') {
            $data = $arsip->snapshot_data;

            // Render view khusus arsip ke dalam format PDF ukuran A4
            $pdf = Pdf::loadView('cetak-baru.cetak_rapor_arsip', compact('arsip', 'data'))
                ->setPaper('a4', 'portrait');

            // Format nama file unduhan (Contoh: Rapor-Fathur-Rosi.pdf)
            $namaFile = 'Rapor-' . Str::slug($data['nama_murid']) . '-' . Str::slug($data['nama_ujian']) . '.pdf';

            // Mengunduh file PDF langsung ke komputer
            return $pdf->download($namaFile);
        }

        return abort(404, 'Dokumen tidak ditemukan.');
    }
}
