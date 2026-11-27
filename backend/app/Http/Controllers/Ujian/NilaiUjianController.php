<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\PengecualianUjian;
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

        $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('tipe_ujian', '!=', 'IMNI');
        $user = auth()->user();
        if ($user && $user->hasRole('staff') && $user->tingkat_id) {
            $queryUjian->where(function ($q) use ($user) {
                $q->whereNull('tingkat_id')
                    ->orWhere('tingkat_id', $user->tingkat_id);
            });
        }
        $daftarUjian = $queryUjian->get();
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

                // FILTER UJIAN BERDASARKAN KELAS & SASARAN TINGKAT
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('tipe_ujian', '!=', 'IMNI');

                if ($isKelasAkhir) {
                    $queryUjian->where('tipe_ujian', 'IMDA 1');
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }

                $tingkatId = $ruanganTerpilih->level->tingkat_id ?? null;
                if ($tingkatId) {
                    $queryUjian->where(function ($q) use ($tingkatId) {
                        $q->whereNull('tingkat_id')
                            ->orWhere('tingkat_id', $tingkatId);
                    });
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
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('tipe_ujian', '!=', 'IMNI');
                if ($isKelasAkhir) {
                    $queryUjian->where('tipe_ujian', 'IMDA 1');
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
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('tipe_ujian', '!=', 'IMNI');

                if ($isKelasAkhir) {
                    $queryUjian->where('tipe_ujian', 'IMDA 1');
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

    /**
     * Rekapitulasi Nilai Berdasarkan Mata Pelajaran Jadwal Ujian (Berdasarkan Ruangan & Level)
     */
    public function rekapMapel(Request $request)
    {
        $payload = $this->buildRekapMapelData($request);
        return view('nilai-ujian.rekap-mapel', $payload);
    }

    /**
     * Cetak A4 Rekapitulasi Nilai Berdasarkan Mata Pelajaran Jadwal Ujian
     */
    public function cetakRekapMapel(Request $request)
    {
        $payload = $this->buildRekapMapelData($request);
        $payload['pengasuh'] = Pengurus::getAktifByJabatan('Pengasuh');
        $tingkatId = $payload['levelTerpilih']?->tingkat_id ?? null;
        $payload['kabid'] = Pengurus::getAktifByJabatan('Kepala Bidang Pendidikan', $tingkatId)
            ?? Pengurus::getAktifByJabatan('Kepala Bidang');

        return view('cetak-baru.cetak_rekap_nilai_mapel', $payload);
    }

    /**
     * Helper penyusun data rekapitulasi nilai berdasarkan mata pelajaran jadwal ujian per level & per ruangan
     */
    private function buildRekapMapelData(Request $request): array
    {
        $kkm = 60;
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;
        $tahunPelajaran = $daftarTahun->firstWhere('id', $tahunPelajaranId);

        // 1. Daftar Agenda Ujian Reguler di Tahun Pelajaran terpilih
        $queryUjian = Ujian::with(['tahunPelajaran', 'semester_relasi'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('tipe_ujian', '!=', 'IMNI');

        $user = auth()->user();
        if ($user && $user->hasRole('staff') && $user->tingkat_id) {
            $queryUjian->where(function ($q) use ($user) {
                $q->whereNull('tingkat_id')
                    ->orWhere('tingkat_id', $user->tingkat_id);
            });
        }
        $daftarUjian = $queryUjian->orderBy('id', 'asc')->get();
        $ujianTerpilih = $request->filled('ujian_id')
            ? $daftarUjian->firstWhere('id', $request->ujian_id)
            : null;

        // 2. Daftar Ruangan & Level sesuai Hak Akses dan Agenda Ujian
        $semuaRuangan = Ruangan::with(['level.tingkat', 'waliRuangan'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        if ($ujianTerpilih) {
            if ($ujianTerpilih->tingkat_id) {
                $semuaRuangan = $semuaRuangan->filter(fn($r) => $r->level && (int) $r->level->tingkat_id === (int) $ujianTerpilih->tingkat_id)->values();
            }
            if (in_array($ujianTerpilih->tipe_ujian, ['IMDA 2', 'IMDA 3'])) {
                $semuaRuangan = $semuaRuangan->reject(fn($r) => Ujian::isKelasAkhir($r->level->nama_level ?? ''))->values();
            }
        }

        // Ekstrak daftar Level unik dari ruangan yang dapat diakses
        $daftarLevel = $semuaRuangan->pluck('level')->filter()->unique('id')->sortBy('id')->values();

        // Tentukan Level & Ruangan terpilih
        $selectedLevelId = $request->filled('level_id') ? (int) $request->level_id : null;
        $selectedRuanganId = $request->filled('ruangan_id') ? (int) $request->ruangan_id : null;

        if (!$selectedLevelId && $selectedRuanganId) {
            $rTmp = $semuaRuangan->firstWhere('id', $selectedRuanganId);
            if ($rTmp) {
                $selectedLevelId = (int) $rTmp->level_id;
            }
        }

        if ($selectedLevelId && !$daftarLevel->contains('id', $selectedLevelId)) {
            $selectedLevelId = null;
        }

        $levelTerpilih = $selectedLevelId ? $daftarLevel->firstWhere('id', $selectedLevelId) : null;

        $daftarRuanganLevel = $selectedLevelId
            ? $semuaRuangan->where('level_id', $selectedLevelId)->values()
            : $semuaRuangan;

        if ($selectedRuanganId && !$daftarRuanganLevel->contains('id', $selectedRuanganId)) {
            $selectedRuanganId = null;
        }
        $ruanganTerpilih = $selectedRuanganId ? $daftarRuanganLevel->firstWhere('id', $selectedRuanganId) : null;

        // Variabel output default
        $jadwals = collect();
        $jadwalTerpilih = null;
        $selectedJadwalId = null;
        $targetRuangans = collect();
        $rekapPerMapel = collect();
        $rekapRuanganMapel = collect();
        $detailNilaiMurid = collect();
        $summary = (object) [
            'jumlah_mapel'      => 0,
            'jumlah_ruangan'    => 0,
            'jumlah_peserta'    => 0,
            'total_nilai_masuk' => 0,
            'target_nilai'      => 0,
            'persen_progres'    => 0,
            'rata_rata'         => 0,
            'tertinggi'         => null,
            'terendah'          => null,
            'tuntas_count'      => 0,
            'belum_tuntas_count' => 0,
            'persen_tuntas'     => 0,
        ];

        if ($ujianTerpilih && $levelTerpilih) {
            $targetRuangans = $ruanganTerpilih ? collect([$ruanganTerpilih]) : $daftarRuanganLevel;
            $targetRuanganIds = $targetRuangans->pluck('id')->toArray();

            // 3. Tarik Daftar Mata Pelajaran dari Jadwal Ujian untuk Ujian & Level ini
            $jadwals = JadwalUjian::with(['mataPelajaran', 'pengawas'])
                ->where('ujian_id', $ujianTerpilih->id)
                ->where('level_id', $levelTerpilih->id)
                ->orderBy('tanggal_ujian', 'asc')
                ->orderBy('waktu_mulai', 'asc')
                ->get();

            $selectedJadwalId = $request->filled('jadwal_ujian_id') && $jadwals->contains('id', (int) $request->jadwal_ujian_id)
                ? (int) $request->jadwal_ujian_id
                : null;
            $jadwalTerpilih = $selectedJadwalId ? $jadwals->firstWhere('id', $selectedJadwalId) : null;

            // 4. Tarik Daftar Murid Aktif per Ruangan & Pengecualian Ujian
            $pengecualianIds = PengecualianUjian::where('ujian_id', $ujianTerpilih->id)
                ->pluck('murid_id')
                ->toArray();

            $pesertaByRuangan = [];
            $totalPesertaWajib = 0;

            foreach ($targetRuangans as $rng) {
                $muridsAktif = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($rng->id, $tahunPelajaranId);
                $pesertaWajib = $muridsAktif->reject(fn($m) => in_array($m->id, $pengecualianIds))->values();
                $pesertaByRuangan[$rng->id] = $pesertaWajib;
                $totalPesertaWajib += $pesertaWajib->count();
            }

            // 5. Tarik Semua Nilai Ujian untuk jadwal & target ruangan (1 query massal)
            $semuaNilai = collect();
            if ($jadwals->isNotEmpty() && !empty($targetRuanganIds)) {
                $semuaNilai = NilaiUjian::with(['murid', 'ruangan'])
                    ->where('ujian_id', $ujianTerpilih->id)
                    ->whereIn('ruangan_id', $targetRuanganIds)
                    ->whereIn('jadwal_ujian_id', $jadwals->pluck('id'))
                    ->whereNotNull('nilai')
                    ->get();
            }

            // 6. Susun Rekapitulasi Per Mata Pelajaran Jadwal Ujian
            foreach ($jadwals as $jadwal) {
                $nilaiMapel = $semuaNilai->where('jadwal_ujian_id', $jadwal->id)->values();
                $dinilaiCount = $nilaiMapel->count();
                $publishCount = $nilaiMapel->where('is_published', true)->count();
                $draftCount = $nilaiMapel->where('is_published', false)->count();
                $tuntasCount = $nilaiMapel->filter(fn($n) => (float) $n->nilai >= $kkm)->count();
                $belumTuntasCount = $nilaiMapel->filter(fn($n) => (float) $n->nilai < $kkm)->count();

                $rataRata = $dinilaiCount > 0 ? round((float) $nilaiMapel->avg('nilai'), 2) : 0;
                $maxItem = $dinilaiCount > 0 ? $nilaiMapel->sortByDesc('nilai')->first() : null;
                $minVal = $dinilaiCount > 0 ? (float) $nilaiMapel->min('nilai') : null;

                // Rincian per ruangan di dalam level untuk mapel ini
                $perRuangan = [];
                foreach ($targetRuangans as $rng) {
                    $pCount = $pesertaByRuangan[$rng->id]->count();
                    $nRng = $nilaiMapel->where('ruangan_id', $rng->id);
                    $dCount = $nRng->count();
                    $tCount = $nRng->filter(fn($n) => (float) $n->nilai >= $kkm)->count();

                    $perRuangan[$rng->id] = (object) [
                        'ruangan_id'    => $rng->id,
                        'nama_ruangan'  => $rng->nama_ruangan,
                        'peserta'       => $pCount,
                        'dinilai'       => $dCount,
                        'rata_rata'     => $dCount > 0 ? round((float) $nRng->avg('nilai'), 2) : null,
                        'tertinggi'     => $dCount > 0 ? (float) $nRng->max('nilai') : null,
                        'terendah'      => $dCount > 0 ? (float) $nRng->min('nilai') : null,
                        'tuntas'        => $tCount,
                        'belum_tuntas'  => $dCount - $tCount,
                        'persen_tuntas' => $dCount > 0 ? round(($tCount / $dCount) * 100, 1) : 0,
                    ];
                }

                $rekapPerMapel->push((object) [
                    'jadwal'             => $jadwal,
                    'jadwal_id'          => $jadwal->id,
                    'nama_mapel'         => $jadwal->nama_mapel,
                    'hari_tanggal'       => $jadwal->hari_tanggal_singkat,
                    'jam'                => $jadwal->jam_mulai_format . ' - ' . $jadwal->jam_selesai_format,
                    'jumlah_peserta'     => $totalPesertaWajib,
                    'jumlah_dinilai'     => $dinilaiCount,
                    'jumlah_publish'     => $publishCount,
                    'jumlah_draft'       => $draftCount,
                    'belum_dinilai'      => max(0, $totalPesertaWajib - $dinilaiCount),
                    'rata_rata'          => $rataRata,
                    'tertinggi'          => $maxItem ? (float) $maxItem->nilai : null,
                    'peraih_tertinggi'   => $maxItem?->murid?->nama_lengkap,
                    'terendah'           => $minVal,
                    'tuntas_count'       => $tuntasCount,
                    'belum_tuntas_count' => $belumTuntasCount,
                    'persen_tuntas'      => $dinilaiCount > 0 ? round(($tuntasCount / $dinilaiCount) * 100, 1) : 0,
                    'persen_progres'     => $totalPesertaWajib > 0 ? min(100, round(($dinilaiCount / $totalPesertaWajib) * 100, 1)) : 0,
                    'per_ruangan'        => $perRuangan,
                ]);
            }

            // 7. Ringkasan KPI (mengikuti jadwalTerpilih jika 1 mapel dipilih, atau seluruh mapel)
            $nilaiScope = $jadwalTerpilih
                ? $semuaNilai->where('jadwal_ujian_id', $jadwalTerpilih->id)
                : $semuaNilai;

            $mapelScopeCount = $jadwalTerpilih ? 1 : $jadwals->count();
            $targetTotalNilai = $totalPesertaWajib * $mapelScopeCount;
            $masukCount = $nilaiScope->count();
            $tuntasScope = $nilaiScope->filter(fn($n) => (float) $n->nilai >= $kkm)->count();
            $belumTuntasScope = $nilaiScope->filter(fn($n) => (float) $n->nilai < $kkm)->count();

            $summary = (object) [
                'jumlah_mapel'       => $jadwals->count(),
                'jumlah_ruangan'     => $targetRuangans->count(),
                'jumlah_peserta'     => $totalPesertaWajib,
                'total_nilai_masuk'  => $masukCount,
                'target_nilai'       => $targetTotalNilai,
                'persen_progres'     => $targetTotalNilai > 0 ? min(100, round(($masukCount / $targetTotalNilai) * 100, 1)) : 0,
                'rata_rata'          => $masukCount > 0 ? round((float) $nilaiScope->avg('nilai'), 2) : 0,
                'tertinggi'          => $masukCount > 0 ? (float) $nilaiScope->max('nilai') : null,
                'terendah'           => $masukCount > 0 ? (float) $nilaiScope->min('nilai') : null,
                'tuntas_count'       => $tuntasScope,
                'belum_tuntas_count' => $belumTuntasScope,
                'persen_tuntas'      => $masukCount > 0 ? round(($tuntasScope / $masukCount) * 100, 1) : 0,
            ];

            // 8. Detail Nilai Murid & Breakdown Ruangan jika 1 Mata Pelajaran dipilih
            if ($jadwalTerpilih) {
                $rowMapel = $rekapPerMapel->firstWhere('jadwal_id', $jadwalTerpilih->id);
                if ($rowMapel) {
                    $rekapRuanganMapel = collect($rowMapel->per_ruangan)->values();
                }

                $nilaiByMurid = $semuaNilai->where('jadwal_ujian_id', $jadwalTerpilih->id)->keyBy('murid_id');

                foreach ($targetRuangans as $rng) {
                    foreach ($pesertaByRuangan[$rng->id] as $murid) {
                        $n = $nilaiByMurid->get($murid->id);
                        $angka = $n ? (float) $n->nilai : null;

                        $detailNilaiMurid->push((object) [
                            'murid'        => $murid,
                            'ruangan'      => $rng,
                            'nilai'        => $angka,
                            'predikat'     => $n ? $n->getPredikatHuruf() : '-',
                            'is_published' => $n ? (bool) $n->is_published : false,
                            'tuntas'       => $angka !== null ? $angka >= $kkm : null,
                        ]);
                    }
                }

                // Urutkan nilai tertinggi dulu, lalu belum dinilai di bawah, lalu nama A-Z
                $detailNilaiMurid = $detailNilaiMurid->sort(function ($a, $b) {
                    if ($a->nilai === null && $b->nilai === null) {
                        return strcmp($a->murid->nama_lengkap ?? '', $b->murid->nama_lengkap ?? '');
                    }
                    if ($a->nilai === null) return 1;
                    if ($b->nilai === null) return -1;
                    if ($a->nilai === $b->nilai) {
                        return strcmp($a->murid->nama_lengkap ?? '', $b->murid->nama_lengkap ?? '');
                    }
                    return $b->nilai <=> $a->nilai;
                })->values();
            }
        }

        return compact(
            'kkm',
            'daftarTahun',
            'tahunPelajaranId',
            'tahunPelajaran',
            'daftarUjian',
            'ujianTerpilih',
            'daftarLevel',
            'selectedLevelId',
            'levelTerpilih',
            'daftarRuanganLevel',
            'selectedRuanganId',
            'ruanganTerpilih',
            'targetRuangans',
            'jadwals',
            'selectedJadwalId',
            'jadwalTerpilih',
            'rekapPerMapel',
            'rekapRuanganMapel',
            'detailNilaiMurid',
            'summary'
        );
    }
}
