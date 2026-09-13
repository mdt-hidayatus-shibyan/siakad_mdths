<?php

namespace App\Http\Controllers\UjianAlquran;

use App\Http\Controllers\Controller;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Level;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\UjianAlquran\PesertaUjianAlquran;
use App\Models\UjianAlquran\UjianAlquran;
use App\Services\UjianAlquranService;
use Illuminate\Http\Request;

class PenilaianAlquranController extends Controller
{
    protected UjianAlquranService $service;

    public function __construct(UjianAlquranService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Input Nilai Ujian Al-Qur'an (Admin Data-Entry)
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $tahunPelajaranId = $request->tahun_id ?? $tahunAktif?->id;

        $ujian = UjianAlquran::with(['juris.ustadz', 'tahunPelajaran'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->first();

        // Urutan Ruangan: Level 5 IBT (5-A, 5-B, 5-C) terlebih dahulu, lalu Level 6 IBT (6-A, 6-B)
        $level5Ids = Level::where('nama_level', 'LIKE', '%5%IBT%')->orWhere('urutan_level', 8)->pluck('id');
        $level6Ids = Level::where('nama_level', 'LIKE', '%6%IBT%')->orWhere('urutan_level', 9)->pluck('id');

        $ruangans5 = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('level_id', $level5Ids)
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $ruangans6 = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('level_id', $level6Ids)
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $daftarRuangan = $ruangans5->concat($ruangans6);

        // Default: Ruangan pertama Level 5 IBT jika user belum memilih filter
        $defaultRuanganId = $daftarRuangan->first()?->id;
        $selectedRuanganId = $request->has('ruangan_id') ? $request->ruangan_id : $defaultRuanganId;

        $pesertas = collect();
        $statistik = (object) [
            'total' => 0,
            'lulus' => 0,
            'tidak_lulus' => 0,
            'belum_diuji' => 0,
            'persen_lulus' => 0,
        ];

        if ($ujian) {
            $query = PesertaUjianAlquran::with(['murid.waliMurid.kampung', 'ruangan.level'])
                ->where('ujian_alquran_id', $ujian->id);

            if (!empty($selectedRuanganId)) {
                $query->where('ruangan_id', $selectedRuanganId);
            }

            if ($request->filled('status_kelulusan')) {
                $query->where('status_kelulusan', $request->status_kelulusan);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_peserta', 'LIKE', "%{$search}%")
                        ->orWhereHas('murid', function ($mq) use ($search) {
                            $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                ->orWhere('nism', 'LIKE', "%{$search}%");
                        });
                });
            }

            $pesertas = $query->orderBy('id', 'asc')->paginate(25)->withQueryString();
            $statistik = $ujian->statistik;
        }

        return view('ujian-alquran.penilaian', compact(
            'ujian',
            'pesertas',
            'daftarRuangan',
            'daftarTahun',
            'tahunPelajaranId',
            'selectedRuanganId',
            'statistik'
        ));
    }

    /**
     * Simpan Penilaian Skor & Kalkulasi Kelulusan (Single & Batch)
     */
    public function simpanNilai(Request $request)
    {
        $expectsJson = $request->ajax()
            || $request->wantsJson()
            || $request->isJson()
            || $request->expectsJson()
            || str_contains($request->header('Accept', ''), 'application/json')
            || str_contains($request->header('Content-Type', ''), 'application/json');

        // Mendukung input batch jika dikirim dalam array 'nilai'
        if ($request->has('nilai') && is_array($request->nilai)) {
            $updatedCount = 0;
            foreach ($request->nilai as $pesertaId => $data) {
                if (!isset($data['jumlah_khoto_jali']) || !isset($data['jumlah_khoto_khofi'])) {
                    continue;
                }
                $this->service->hitungDanSimpanNilai(
                    (int) $pesertaId,
                    (int) $data['jumlah_khoto_jali'],
                    (int) $data['jumlah_khoto_khofi'],
                    $data['catatan_juri'] ?? null,
                    auth()->id()
                );
                $updatedCount++;
            }

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menyimpan penilaian untuk {$updatedCount} murid.",
                ]);
            }

            return redirect()->back()->with('success', "Berhasil menyimpan penilaian untuk {$updatedCount} murid.");
        }

        // Single save
        $request->validate([
            'peserta_id'         => 'required|exists:peserta_ujian_alqurans,id',
            'jumlah_khoto_jali'  => 'required|integer|min:0',
            'jumlah_khoto_khofi' => 'required|integer|min:0',
            'catatan_juri'       => 'nullable|string',
        ]);

        try {
            $peserta = $this->service->hitungDanSimpanNilai(
                (int) $request->peserta_id,
                (int) $request->jumlah_khoto_jali,
                (int) $request->jumlah_khoto_khofi,
                $request->catatan_juri,
                auth()->id()
            );

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => "Nilai untuk murid {$peserta->murid->nama_lengkap} berhasil disimpan. Status: {$peserta->status_kelulusan} (Nilai: " . number_format($peserta->nilai_akhir, 0) . ")",
                    'data'    => $peserta,
                ]);
            }

            return redirect()->back()->with(
                'success',
                "Nilai murid {$peserta->murid->nama_lengkap} berhasil disimpan. Status: {$peserta->status_kelulusan} (Nilai: " . number_format($peserta->nilai_akhir, 0) . ")"
            );
        } catch (\Exception $e) {
            if ($expectsJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Format Lembar Penilaian Kosong untuk Dewan Juri (2 Lembar: Juri 1 & Juri 2)
     */
    public function cetakFormatPenilaian(Request $request)
    {
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $ujian = UjianAlquran::with(['juris.ustadz', 'tahunPelajaran'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->firstOrFail();

        $query = PesertaUjianAlquran::with(['murid', 'ruangan.level'])
            ->where('ujian_alquran_id', $ujian->id);

        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        $pesertas = $query->orderBy('nomor_peserta', 'asc')->get();
        $ruanganAktif = $request->filled('ruangan_id') ? Ruangan::find($request->ruangan_id) : null;
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');

        $juri1 = $ujian->juris->firstWhere('peran_juri', 'Juri 1')
            ?? $ujian->juris->firstWhere('kategori_juri', 'Khotho Jali')
            ?? $ujian->juris->first();

        $juri2 = $ujian->juris->firstWhere('peran_juri', 'Juri 2')
            ?? $ujian->juris->firstWhere('kategori_juri', 'Khotho Khofi')
            ?? $ujian->juris->skip(1)->first();

        $filterJuri = $request->juri; // 'juri1', 'juri2', or null for both

        return view('cetak-baru.cetak_format_penilaian_alquran', compact(
            'ujian',
            'pesertas',
            'ruanganAktif',
            'juri1',
            'juri2',
            'pengasuh',
            'filterJuri'
        ));
    }
}
