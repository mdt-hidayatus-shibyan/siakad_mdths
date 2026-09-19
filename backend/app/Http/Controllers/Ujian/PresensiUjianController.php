<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\PresensiPengawasUjian;
use App\Models\Ujian\PresensiUjian;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;
use App\Repositories\MuridRuanganRepository;
use App\Services\NilaiUjianService;
use App\Services\PresensiUjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiUjianController extends Controller
{
    protected $presensiUjianService;
    protected $nilaiUjianService;
    protected $muridRuanganRepo;

    public function __construct(
        PresensiUjianService $presensiUjianService,
        NilaiUjianService $nilaiUjianService,
        MuridRuanganRepository $muridRuanganRepo
    ) {
        $this->presensiUjianService = $presensiUjianService;
        $this->nilaiUjianService = $nilaiUjianService;
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Dashboard Pantau Progres Presensi Ujian per Ruangan Kelas
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->withCount(['murids' => fn($q) => $q->where('status', 'Aktif')])
            ->orderBy('level_id', 'asc')
            ->get();

        $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
        $ujianTerpilih = null;
        $dataProgres = collect();

        if ($request->ujian_id) {
            $ujianTerpilih = Ujian::find($request->ujian_id);
            if ($ujianTerpilih) {
                $dataProgres = $this->presensiUjianService->hitungProgresPresensiRuangan($ujianTerpilih->id, $daftarRuangan);
            }
        }

        return view('presensi-ujian.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ujianTerpilih',
            'dataProgres'
        ));
    }

    /**
     * Form Input Presensi Ujian per Jadwal dan Ruangan Kelas
     */
    public function inputPresensi(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->orderBy('level_id', 'asc')
            ->get();

        $ruanganTerpilih = null;
        $daftarUjian = collect();
        $jadwals = collect();
        $jadwalTerpilih = null;
        $muridsWithStatus = collect();
        $presensiExisting = collect();
        $presensiPengawas = null;
        $daftarUstadz = Ustadz::orderBy('nama_lengkap')->get();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);

            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId, ['waliMurid']);
                $ruanganTerpilih->setRelation('murids', $murids);

                $levelNama = $ruanganTerpilih->level->nama_level ?? '';
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId);
                if ($isKelasAkhir) {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }
                $daftarUjian = $queryUjian->get();

                if ($request->ujian_id) {
                    $ujian = Ujian::find($request->ujian_id);

                    if ($ujian) {
                        $jadwals = JadwalUjian::with('mataPelajaran', 'pengawas')
                            ->where('ujian_id', $ujian->id)
                            ->where('level_id', $ruanganTerpilih->level_id)
                            ->orderBy('tanggal_ujian', 'asc')
                            ->orderBy('waktu_mulai', 'asc')
                            ->get();

                        // Evaluasi syarat administrasi murid (Lunas / Terkunci / Dispensasi)
                        $muridsWithStatus = $this->nilaiUjianService->evaluasiSyaratAdmin($ujian, $ruanganTerpilih, $murids);

                        if ($request->jadwal_ujian_id) {
                            $jadwalTerpilih = JadwalUjian::with(['mataPelajaran', 'pengawas'])->find($request->jadwal_ujian_id);

                            $presensiExisting = PresensiUjian::where('ujian_id', $ujian->id)
                                ->where('jadwal_ujian_id', $request->jadwal_ujian_id)
                                ->where('ruangan_id', $ruanganTerpilih->id)
                                ->get()
                                ->keyBy('murid_id');

                            $presensiPengawas = PresensiPengawasUjian::with(['ustadz', 'ustadzPengganti'])
                                ->where('jadwal_ujian_id', $request->jadwal_ujian_id)
                                ->where('ruangan_id', $ruanganTerpilih->id)
                                ->first();
                        }
                    }
                }
            }
        }

        return view('presensi-ujian.input', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganTerpilih',
            'daftarUjian',
            'jadwals',
            'jadwalTerpilih',
            'muridsWithStatus',
            'presensiExisting',
            'presensiPengawas',
            'daftarUstadz'
        ));
    }

    /**
     * Menyimpan data presensi murid dan pengawas ujian
     */
    public function store(Request $request)
    {
        $request->validate([
            'ujian_id'        => 'required|exists:ujians,id',
            'ruangan_id'      => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
            'presensi'        => 'required|array',
            'pengawas'        => 'nullable|array',
        ]);

        try {
            $pengawasData = $request->input('pengawas', []);

            $disimpan = $this->presensiUjianService->simpanPresensiMassal(
                $request->presensi,
                $request->ujian_id,
                $request->jadwal_ujian_id,
                $request->ruangan_id,
                $pengawasData,
                Auth::id()
            );

            return back()->with('success', "Berhasil menyimpan {$disimpan} data presensi murid dan kehadiran pengawas ujian!");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses presensi ujian: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Rekapitulasi Presensi Ujian Satu Kelas
     */
    public function rekap(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->orderBy('level_id', 'asc')
            ->get();

        $ruanganTerpilih = null;
        $ujianTerpilih = null;
        $daftarUjian = collect();
        $jadwals = collect();
        $dataRekap = collect();
        $totalSesi = 0;

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);

            if ($ruanganTerpilih) {
                $levelNama = $ruanganTerpilih->level->nama_level ?? '';
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

                    if ($ujianTerpilih) {
                        $rekapResult = $this->presensiUjianService->hitungMatriksRekapPresensi($ujianTerpilih->id, $ruanganTerpilih->id);
                        $jadwals = $rekapResult['jadwals'];
                        $dataRekap = $rekapResult['dataRekap'];
                        $totalSesi = $rekapResult['totalSesi'];
                    }
                }
            }
        }

        return view('presensi-ujian.rekap', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ruanganTerpilih',
            'ujianTerpilih',
            'jadwals',
            'dataRekap',
            'totalSesi'
        ));
    }

    /**
     * Pusat Menu Cetak Dokumen Ujian
     */
    public function cetakMenu(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->orderBy('level_id', 'asc')
            ->get();

        $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();

        $ruanganTerpilih = null;
        $ujianTerpilih = null;
        $jadwals = collect();

        if ($request->ruangan_id && $request->ujian_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            $ujianTerpilih = Ujian::find($request->ujian_id);

            if ($ruanganTerpilih && $ujianTerpilih) {
                $jadwals = JadwalUjian::with(['mataPelajaran', 'pengawas'])
                    ->where('ujian_id', $ujianTerpilih->id)
                    ->where('level_id', $ruanganTerpilih->level_id)
                    ->orderBy('tanggal_ujian', 'asc')
                    ->orderBy('waktu_mulai', 'asc')
                    ->get();
            }
        }

        return view('presensi-ujian.cetak', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ruanganTerpilih',
            'ujianTerpilih',
            'jadwals'
        ));
    }

    /**
     * Cetak Lembar DHPU (Daftar Hadir Peserta Ujian)
     */
    public function cetakDhpu(Request $request)
    {
        $request->validate([
            'ujian_id'        => 'required|exists:ujians,id',
            'ruangan_id'      => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
        ]);

        $mode = $request->input('mode', 'kosong'); // 'kosong' (untuk paraf basah) atau 'terisi'

        $data = $this->presensiUjianService->ambilDataCetakDhpu(
            $request->ujian_id,
            $request->ruangan_id,
            $request->jadwal_ujian_id
        );

        return view('cetak-baru.cetak_dhpu', array_merge($data, ['mode' => $mode]));
    }

    /**
     * Cetak Berita Acara Pelaksanaan Ujian
     */
    public function cetakBeritaAcara(Request $request)
    {
        $request->validate([
            'ujian_id'        => 'required|exists:ujians,id',
            'ruangan_id'      => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
        ]);

        $data = $this->presensiUjianService->ambilDataCetakDhpu(
            $request->ujian_id,
            $request->ruangan_id,
            $request->jadwal_ujian_id
        );

        return view('cetak-baru.cetak_berita_acara_ujian', $data);
    }

    /**
     * Cetak Rekapitulasi Presensi Ujian Per Ruangan Kelas
     */
    public function cetakRekap(Request $request)
    {
        $request->validate([
            'ujian_id'   => 'required|exists:ujians,id',
            'ruangan_id' => 'required|exists:ruangans,id',
        ]);

        $data = $this->presensiUjianService->hitungMatriksRekapPresensi(
            $request->ujian_id,
            $request->ruangan_id
        );

        return view('cetak-baru.cetak_rekap_presensi_ujian', $data);
    }
}
