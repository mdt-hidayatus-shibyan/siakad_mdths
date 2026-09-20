<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\DispensasiUjian;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\PengecualianUjian;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use App\Services\NilaiUjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersyaratanUjianController extends Controller
{
    protected $muridRuanganRepo;
    protected $nilaiUjianService;

    public function __construct(MuridRuanganRepository $muridRuanganRepo, NilaiUjianService $nilaiUjianService)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
        $this->nilaiUjianService = $nilaiUjianService;
    }

    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $daftarUjian = collect();
        $muridsWithStatus = collect();

        // WAJIB dideklarasikan di awal agar tidak error saat view di-load pertama kali
        $jadwals = collect();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId, ['waliMurid']);
                $ruanganTerpilih->setRelation('murids', $murids);
                $levelNama = $ruanganTerpilih->level->nama_level ?? '';

                // FILTER UJIAN
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId);

                if ($isKelasAkhir) {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }
                $daftarUjian = $queryUjian->get();

                // JIKA AGENDA UJIAN DIKLIK
                if ($request->ujian_id) {
                    $ujian = Ujian::find($request->ujian_id);

                    // Mengambil jadwal
                    $jadwals = JadwalUjian::with('mataPelajaran')
                        ->where('ujian_id', $ujian->id)
                        ->where('level_id', $ruanganTerpilih->level_id)
                        ->orderBy('tanggal_ujian', 'asc')
                        ->get();

                    // Evaluasi Syarat Admin & Pengecualian melalui NilaiUjianService
                    $muridsWithStatus = $this->nilaiUjianService->evaluasiSyaratAdmin($ujian, $ruanganTerpilih, $ruanganTerpilih->murids);
                }
            }
        }

        return view('persyaratan-ujian.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganTerpilih',
            'daftarUjian',
            'jadwals',
            'muridsWithStatus',
        ));
    }

    /**
     * Beri dispensasi administrasi keuangan murid
     */
    public function beriDispensasi(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required',
            'murid_id' => 'required',
            'alasan_izin' => 'required|string|max:200'
        ]);

        DispensasiUjian::firstOrCreate(
            [
                'ujian_id' => $request->ujian_id,
                'murid_id' => $request->murid_id,
            ],
            [
                'alasan_izin' => $request->alasan_izin ?: 'Izin Orang Tua Keadaan Tidak Mampu',
                'diizinkan_oleh' => Auth::id()
            ]
        );

        // Jika murid sebelumnya berstatus tidak ikut ujian, batalkan pengecualian
        PengecualianUjian::where('ujian_id', $request->ujian_id)
            ->where('murid_id', $request->murid_id)
            ->delete();

        return redirect()->back()->with('success', 'Akses input nilai murid berhasil dibuka via kebijakan dispensasi administrator!');
    }

    /**
     * Tandai murid tidak mengikuti agenda ujian ini
     */
    public function tandaiTidakIkut(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required',
            'murid_id' => 'required',
            'alasan'   => 'nullable|string|max:200'
        ]);

        PengecualianUjian::updateOrCreate(
            [
                'ujian_id' => $request->ujian_id,
                'murid_id' => $request->murid_id,
            ],
            [
                'alasan'        => $request->alasan ?: 'Tidak Mengikuti Ujian',
                'ditandai_oleh' => Auth::id()
            ]
        );

        return redirect()->back()->with('success', 'Status murid berhasil ditandai sebagai TIDAK MENGIKUTI UJIAN pada agenda ini.');
    }

    /**
     * Batalkan status tidak mengikuti ujian (kembalikan murid menjadi peserta ujian)
     */
    public function batalkanTidakIkut(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required',
            'murid_id' => 'required',
        ]);

        PengecualianUjian::where('ujian_id', $request->ujian_id)
            ->where('murid_id', $request->murid_id)
            ->delete();

        return redirect()->back()->with('success', 'Status tidak mengikuti ujian berhasil dibatalkan. Murid kembali menjadi peserta ujian.');
    }
}
