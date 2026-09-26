<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Models\Ujian\DispensasiUjian;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use App\Services\NilaiUjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NilaiUjianController extends Controller
{
    protected $nilaiUjianService;
    protected $muridRuanganRepo;

    public function __construct(NilaiUjianService $nilaiUjianService, MuridRuanganRepository $muridRuanganRepo)
    {
        $this->nilaiUjianService = $nilaiUjianService;
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        // Ambil daftar ruangan beserta jumlah murid aktifnya
        $daftarRuangan = Ruangan::berdasarkanHakAkses()->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->withCount(['murids' => fn($q) => $q->where('status', 'Aktif')])
            ->orderBy('level_id', 'asc')
            ->get();

        $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
        $ujianTerpilih = null;
        $dataProgres = collect();

        if ($request->ujian_id) {
            $ujianTerpilih = Ujian::find($request->ujian_id);
            $dataProgres = $this->nilaiUjianService->hitungProgresRuangan($ujianTerpilih->id, $daftarRuangan);
        }

        return view('nilai-ujian.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ujianTerpilih',
            'dataProgres'
        ));
    }
    public function inputNilai(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $daftarUjian = collect();
        $muridsWithStatus = collect();
        $nilaiExisting = collect();

        // WAJIB dideklarasikan di awal agar tidak error saat view di-load pertama kali
        $jadwals = collect();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId, ['waliMurid']);
                $ruanganTerpilih->setRelation('murids', $murids);

                $levelNama = $ruanganTerpilih->level->nama_level ?? '';

                // (Blok pencarian "MATA PELAJARAN" yang lama DIBUANG karena membuang-buang query DB)

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

                    // Mengambil jadwal (sebagai pengganti filter mapel lama)
                    $jadwals = JadwalUjian::with('mataPelajaran')
                        ->where('ujian_id', $ujian->id)
                        ->where('level_id', $ruanganTerpilih->level_id)
                        ->orderBy('tanggal_ujian', 'asc')
                        ->get();


                    // Pembagian Bulan Tagihan per Semester Berjalan
                    $namaSemester = $ujian->semester_relasi->nama_semester ?? '';
                    $bulanSemester = (str_contains($namaSemester, '1') || str_contains(strtolower($namaSemester), 'ganjil'))
                        ? ['Syawal', 'Dzul Qadah', 'Dzul Hijjah', 'Muharram', 'Shafar']
                        : ['Rabiul Awal', 'Rabiul Tsani', 'Jumadal Ula', 'Jumadal Akhir', 'Rajab'];

                    // Ambil Nilai yang Sebelumnya Pernah Diinput (Sesuai Jadwal_id)
                    if ($request->jadwal_ujian_id) {
                        $nilaiExisting = NilaiUjian::where('ujian_id', $ujian->id)
                            ->where('jadwal_ujian_id', $request->jadwal_ujian_id)
                            ->where('ruangan_id', $ruanganTerpilih->id)
                            ->get()
                            ->keyBy('murid_id');
                    }

                    // Evaluasi Syarat Admin & Pengecualian via NilaiUjianService
                    $muridsWithStatus = $this->nilaiUjianService->evaluasiSyaratAdmin($ujian, $ruanganTerpilih, $ruanganTerpilih->murids);
                }
            }
        }

        return view('nilai-ujian.input-nilai', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganTerpilih',
            'daftarUjian',
            'jadwals',
            'muridsWithStatus',
            'nilaiExisting'
        ));
    }


    public function simpanNilai(Request $request)
    {
        // 1. Validasi cukup menggunakan jadwal_ujian_id
        $request->validate([
            'ujian_id' => 'required',
            'ruangan_id' => 'required',
            'jadwal_ujian_id' => 'required',
            'nilai' => 'required|array',
        ]);

        $isPublished = $request->action === 'publish';
        $jumlahDisimpan = 0;

        DB::beginTransaction();
        try {
            foreach ($request->nilai as $muridId => $angka) {
                if ($angka !== null && $angka !== '') {
                    NilaiUjian::updateOrCreate(
                        [
                            // Kunci Pencarian (Primary Identifiers)
                            'ujian_id' => $request->ujian_id,
                            'jadwal_ujian_id' => $request->jadwal_ujian_id,
                            'ruangan_id' => $request->ruangan_id,
                            'murid_id' => $muridId,
                        ],
                        [
                            // Data yang diperbarui
                            'nilai' => $angka,
                            'is_published' => $isPublished,
                            'diinput_oleh' => Auth::id() // <-- Wajib diisi sesuai skema Anda
                        ]
                    );
                    $jumlahDisimpan++;
                } else {
                    // Jika input dikosongkan, hapus nilai lama dari database
                    NilaiUjian::where([
                        'ujian_id' => $request->ujian_id,
                        'jadwal_ujian_id' => $request->jadwal_ujian_id,
                        'ruangan_id' => $request->ruangan_id,
                        'murid_id' => $muridId,
                    ])->delete();
                }
            }
            DB::commit();

            $pesan = $isPublished
                ? "Berhasil mempublikasikan $jumlahDisimpan nilai murid ke lembar rapor resmi!"
                : "Berhasil menyimpan $jumlahDisimpan data nilai ke dalam lembaran Draft sementara.";

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses nilai: ' . $e->getMessage());
        }
    }


    public function inputLeger(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $ujianTerpilih = null;
        $daftarUjian = collect();

        $jadwals = collect();
        $murids = collect();
        $nilaiMatrix = [];

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
                $ruanganTerpilih->setRelation('murids', $murids);

                $levelNama = $ruanganTerpilih->level->nama_level ?? '';
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

                // Filter Ujian
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId);
                if ($isKelasAkhir) {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }
                $daftarUjian = $queryUjian->get();

                // JIKA UJIAN DIPILIH, SUSUN MATRIKS FORM & CEK SYARAT ADMIN
                if ($request->ujian_id) {
                    $ujianTerpilih = Ujian::find($request->ujian_id);

                    $jadwals = JadwalUjian::with('mataPelajaran')
                        ->where('ujian_id', $ujianTerpilih->id)
                        ->where('level_id', $ruanganTerpilih->level_id)
                        ->orderBy('tanggal_ujian', 'asc')
                        ->get();

                    $semuaNilai = NilaiUjian::where('ujian_id', $ujianTerpilih->id)
                        ->where('ruangan_id', $ruanganTerpilih->id)
                        ->get();

                    foreach ($semuaNilai as $n) {
                        $nilaiMatrix[$n->murid_id][$n->jadwal_ujian_id] = $n->nilai;
                    }

                    // Delegate Evaluasi Syarat Admin ke Service
                    $murids = $this->nilaiUjianService->evaluasiSyaratAdmin($ujianTerpilih, $ruanganTerpilih, $murids);
                }
            }
        }

        return view('nilai-ujian.input-leger', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ruanganTerpilih',
            'ujianTerpilih',
            'jadwals',
            'murids',
            'nilaiMatrix'
        ));
    }

    public function storeLeger(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required|exists:ruangans,id',
            'ujian_id'   => 'required|exists:ujians,id',
            'nilai'      => 'required|array',
            'action'     => 'nullable|in:draft,publish'
        ]);

        $aksi = $request->action ?? 'draft';

        try {
            $isPublished = $this->nilaiUjianService->simpanNilaiLeger(
                $request->ruangan_id,
                $request->ujian_id,
                $request->nilai,
                $aksi,
                Auth::id()
            );

            $pesan = $isPublished
                ? 'Semua nilai berhasil disimpan dan dipublikasikan ke Leger/Rapor!'
                : 'Draf nilai berhasil disimpan sementara.';

            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }


    public function laporanLeger(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $ujianTerpilih = null;
        $daftarUjian = collect();

        // Variabel penampung hasil leger
        $kolomMapel = []; // Akan berisi ['id_jadwal' => 'Nama Mapel']
        $dataLeger = collect();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with([
                'level',
                'tahunPelajaran',
                'waliRuangan'
            ])->find($request->ruangan_id);

            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
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

                // JIKA UJIAN DIPILIH, MULAI KALKULASI LEGER RANKING
                if ($request->ujian_id) {
                    $ujianTerpilih = Ujian::find($request->ujian_id);
                    $murids = $ruanganTerpilih->murids;

                    // 1. Ekstrak Daftar Mata Pelajaran dari Jadwal Ujian
                    $jadwals = JadwalUjian::with('mataPelajaran')
                        ->where('ujian_id', $ujianTerpilih->id)
                        ->where('level_id', $ruanganTerpilih->level_id)
                        ->orderBy('tanggal_ujian', 'asc')
                        ->get();

                    foreach ($jadwals as $jadwal) {
                        $namaMapel = $jadwal->mataPelajaran->nama_mapel ??  $jadwal->nama_mata_pelajaran_custom;
                        $kolomMapel[$jadwal->id] = $namaMapel;
                    }

                    // 2. Ambil SEMUA nilai di kelas dan ujian ini
                    $semuaNilai = NilaiUjian::where('ujian_id', $ujianTerpilih->id)
                        ->where('ruangan_id', $ruanganTerpilih->id)
                        ->get();

                    // 3. Susun Data Per Murid & Kalkulasi Total
                    foreach ($murids as $murid) {
                        $nilaiMurid = $semuaNilai->where('murid_id', $murid->id);
                        $total = 0;
                        $jumlahMapelBerisiNilai = 0;
                        $mapelNilai = [];

                        foreach ($kolomMapel as $jadwalId => $namaMapel) {

                            $n = $nilaiMurid->firstWhere('jadwal_ujian_id', $jadwalId);

                            $angka = $n ? $n->nilai : null;
                            $isPub = $n ? $n->is_published : false;

                            // 🔴 PERBAIKAN: Gunakan $jadwalId sebagai Key agar tidak tumpang tindih
                            $mapelNilai[$jadwalId] = [
                                'nama_mapel'   => $namaMapel,
                                'nilai'        => $angka,
                                'is_published' => $isPub
                            ];

                            if ($angka !== null) {
                                $total += (float) $angka;
                                $jumlahMapelBerisiNilai++;
                            }
                        }

                        $dataLeger->push((object)[
                            'murid'           => $murid,
                            'nilai_per_mapel' => $mapelNilai,
                            'total'           => $total,
                            'rata_rata'       => $jumlahMapelBerisiNilai > 0 ? round($total / $jumlahMapelBerisiNilai, 2) : 0,
                        ]);
                    }

                    // 4. Urutkan berdasarkan Ranking (Total Tertinggi ke Terendah, jika sama urutkan Rata-rata lalu Nama)
                    $dataLeger = $dataLeger->sort(function ($a, $b) {
                        if ($a->total === $b->total) {
                            if ($a->rata_rata === $b->rata_rata) {
                                return strcmp($a->murid->nama_lengkap ?? '', $b->murid->nama_lengkap ?? '');
                            }
                            return $b->rata_rata <=> $a->rata_rata;
                        }
                        return $b->total <=> $a->total;
                    })->values();
                }
            }
        }

        if ($request->has('print')) {
            return view('cetak-baru.cetak_leger_ujian', compact(
                'ruanganTerpilih',
                'ujianTerpilih',
                'kolomMapel',
                'dataLeger'
            ));
        }

        return view('nilai-ujian.laporan-leger', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarUjian',
            'ruanganTerpilih',
            'ujianTerpilih',
            'kolomMapel',
            'dataLeger'
        ));
    }

    /**
     * Cetak Rekapitulasi Dokumen Progres Input Nilai Ujian
     */
    public function cetakProgres(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required|exists:ujians,id',
        ]);

        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $ujianTerpilih = Ujian::with(['tahunPelajaran', 'semester_relasi'])->findOrFail($request->ujian_id);

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $ujianTerpilih->tahun_pelajaran_id ?? $tahunPelajaranId)
            ->with(['level', 'waliRuangan'])
            ->withCount(['murids' => fn($q) => $q->where('status', 'Aktif')])
            ->orderBy('level_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $dataProgres = $this->nilaiUjianService->hitungProgresRuangan($ujianTerpilih->id, $daftarRuangan);

        return view('cetak-baru.cetak_progres_nilai_ujian', compact(
            'ujianTerpilih',
            'daftarRuangan',
            'dataProgres'
        ));
    }
}
