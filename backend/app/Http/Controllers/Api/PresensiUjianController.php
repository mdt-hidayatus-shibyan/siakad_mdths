<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
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
use Illuminate\Support\Facades\Validator;

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
     * Helper: Ambil ID ruangan yang dapat diakses oleh ustadz yang sedang login
     */
    private function getAccessibleRuanganIds($user, $tahunPelajaranId)
    {
        $ustadz = $user->ustadz;
        if (!$ustadz) {
            return Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->pluck('id')->toArray();
        }

        // 1. Ruangan binaan sebagai Wali Ruangan
        $waliRuanganIds = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('ustadz_id', $ustadz->id)
            ->pluck('id')
            ->toArray();

        // 2. Ruangan tempat mengajar / jadwal ujian pengawas
        $jadwalRuanganIds = JadwalPelajaran::where('ustadz_id', $ustadz->id)
            ->whereHas('ruangan', function ($q) use ($tahunPelajaranId) {
                $q->where('tahun_pelajaran_id', $tahunPelajaranId);
            })
            ->pluck('ruangan_id')
            ->toArray();

        $pengawasRuanganIds = PresensiPengawasUjian::where('ustadz_id', $ustadz->id)
            ->orWhere('ustadz_pengganti_id', $ustadz->id)
            ->pluck('ruangan_id')
            ->toArray();

        $merged = array_unique(array_merge($waliRuanganIds, $jadwalRuanganIds, $pengawasRuanganIds));

        if (!empty($merged)) {
            return array_values($merged);
        }

        return Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->pluck('id')->toArray();
    }

    /**
     * Ambil data lengkap untuk form Presensi Ujian Mobile
     * GET /api/presensi-ujian/data
     */
    public function getData(Request $request)
    {
        $user = $request->user();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunPelajaranId = $request->tahun_id ?? $tahunAktif?->id ?? TahunPelajaran::orderBy('id', 'desc')->value('id');

        // 1. Daftar Ruangan yang dapat diakses Ustadz
        $accessibleRuanganIds = $this->getAccessibleRuanganIds($user, $tahunPelajaranId);
        $daftarRuangan = Ruangan::whereIn('id', $accessibleRuanganIds)
            ->with('level')
            ->orderBy('level_id', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama_ruangan' => $r->nama_ruangan,
                    'level_id' => $r->level_id,
                    'nama_level' => $r->level->nama_level ?? '-',
                ];
            });

        $selectedRuanganId = $request->ruangan_id ? (int) $request->ruangan_id : ($daftarRuangan->first()['id'] ?? null);

        $ruangan = null;
        if ($selectedRuanganId) {
            $ruangan = Ruangan::with(['level', 'waliRuangan'])->find($selectedRuanganId);
        }

        // 2. Daftar Ujian berdasarkan level ruangan (Kelas Akhir: IMDA 1 & IMNI, Selainnya: IMDA 1 & IMDA 2)
        $queryUjian = Ujian::with('semester')->where('tahun_pelajaran_id', $tahunPelajaranId);

        if ($ruangan && $ruangan->level) {
            $levelNama = $ruangan->level->nama_level ?? '';
            $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

            if ($isKelasAkhir) {
                $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
            } else {
                $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
            }
        }

        $daftarUjian = $queryUjian->orderBy('id', 'asc')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'nama_ujian' => $u->nama_ujian,
                    'tipe_ujian' => $u->tipe_ujian ?? $u->jenis_ujian ?? 'IMDA 1',
                    'semester' => $u->semester->nama_semester ?? ($u->semester_id == 9 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)'),
                    'tanggal_mulai' => $u->tanggal_mulai ? (is_string($u->tanggal_mulai) ? $u->tanggal_mulai : $u->tanggal_mulai->format('Y-m-d')) : date('Y-m-d'),
                    'tanggal_selesai' => $u->tanggal_selesai ? (is_string($u->tanggal_selesai) ? $u->tanggal_selesai : $u->tanggal_selesai->format('Y-m-d')) : date('Y-m-d'),
                ];
            });

        $selectedUjianId = $request->ujian_id ? (int) $request->ujian_id : ($daftarUjian->first()['id'] ?? null);
        if (!$daftarUjian->contains('id', $selectedUjianId)) {
            $selectedUjianId = $daftarUjian->first()['id'] ?? null;
        }

        $jadwalList = collect();
        $selectedJadwalId = null;
        $pengawasData = null;
        $muridList = collect();
        $summary = [
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'dispensasi' => 0,
        ];

        if ($selectedRuanganId) {
            $ruangan = Ruangan::with('level')->find($selectedRuanganId);
        }

        // 3. Ambil Jadwal Ujian untuk level ruangan ini
        if ($selectedUjianId && $ruangan) {
            $ustadz = $user->ustadz;

            $queryJadwals = JadwalUjian::with(['mataPelajaran', 'pengawas'])
                ->where('ujian_id', $selectedUjianId)
                ->where('level_id', $ruangan->level_id);

            // Filter:
            // Jika Ustadz adalah Wali Ruangan di ruangan ini: Tampilkan SELURUH mapel ujian di ruangan ini
            // Jika Ustadz BUKAN Wali Ruangan: Hanya mapel yang diampunya di ruangan ini ATAU jadwal di mana ia menjadi pengawas / badal
            if ($ustadz) {
                $isWaliRuangan = ($ruangan->ustadz_id == $ustadz->id);
                if (!$isWaliRuangan) {
                    $mapelDiampuIds = JadwalPelajaran::where('ruangan_id', $ruangan->id)
                        ->where('ustadz_id', $ustadz->id)
                        ->pluck('mata_pelajaran_id')
                        ->toArray();

                    $jadwalPengawasIds = PresensiPengawasUjian::where('ruangan_id', $ruangan->id)
                        ->where(function ($q) use ($ustadz) {
                            $q->where('ustadz_id', $ustadz->id)
                                ->orWhere('ustadz_pengganti_id', $ustadz->id);
                        })
                        ->pluck('jadwal_ujian_id')
                        ->toArray();

                    $queryJadwals->where(function ($q) use ($mapelDiampuIds, $jadwalPengawasIds, $ustadz) {
                        $q->whereIn('mata_pelajaran_id', $mapelDiampuIds)
                            ->orWhereIn('id', $jadwalPengawasIds)
                            ->orWhere('ustadz_id', $ustadz->id);
                    });
                }
            }

            $jadwals = $queryJadwals->orderBy('tanggal_ujian', 'asc')
                ->orderBy('waktu_mulai', 'asc')
                ->get();

            $jadwalList = $jadwals->map(function ($j) {
                return [
                    'id' => $j->id,
                    'mata_pelajaran_id' => $j->mata_pelajaran_id,
                    'nama_mapel' => $j->nama_mapel,
                    'kode_mapel' => $j->mataPelajaran->kode_mapel ?? '-',
                    'hari_tanggal' => $j->hari_tanggal,
                    'hari_tanggal_singkat' => $j->hari_tanggal_singkat,
                    'tanggal_ujian' => $j->hari_tanggal_singkat,
                    'tanggal_ujian_raw' => $j->getRawOriginal('tanggal_ujian') ?? date('Y-m-d'),
                    'waktu_mulai' => $j->jam_mulai_format,
                    'waktu_selesai' => $j->jam_selesai_format,
                    'pengawas_id' => $j->pengawas_id,
                    'pengawas_nama' => $j->pengawas->nama_lengkap ?? null,
                ];
            });

            $selectedJadwalId = $request->jadwal_ujian_id ? (int) $request->jadwal_ujian_id : ($jadwalList->first()['id'] ?? null);

            // 4. Ambil Data Presensi Murid & Pengawas jika Jadwal Ujian terpilih
            if ($selectedJadwalId) {
                $jadwalTerpilih = $jadwals->firstWhere('id', $selectedJadwalId);

                // Presensi Pengawas
                $presensiPengawas = PresensiPengawasUjian::with(['ustadz', 'ustadzPengganti'])
                    ->where('jadwal_ujian_id', $selectedJadwalId)
                    ->where('ruangan_id', $selectedRuanganId)
                    ->first();

                $pengawasData = [
                    'ustadz_id' => $presensiPengawas?->ustadz_id ?? $jadwalTerpilih?->pengawas_id,
                    'ustadz_nama' => $presensiPengawas?->ustadz?->nama_lengkap ?? $jadwalTerpilih?->pengawas?->nama_lengkap ?? 'Ustadz Pengawas',
                    'ustadz_pengganti_id' => $presensiPengawas?->ustadz_pengganti_id,
                    'ustadz_pengganti_nama' => $presensiPengawas?->ustadzPengganti?->nama_lengkap,
                    'status' => $presensiPengawas?->status ?? 'Hadir',
                    'catatan_berita_acara' => $presensiPengawas?->catatan_berita_acara,
                ];

                // Presensi Murid
                $ujianModel = Ujian::find($selectedUjianId);
                $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($selectedRuanganId, $tahunPelajaranId, 'Aktif');
                $muridsWithStatus = $this->nilaiUjianService->evaluasiSyaratAdmin($ujianModel, $ruangan, $murids);

                $presensiExisting = PresensiUjian::where('ujian_id', $selectedUjianId)
                    ->where('jadwal_ujian_id', $selectedJadwalId)
                    ->where('ruangan_id', $selectedRuanganId)
                    ->get()
                    ->keyBy('murid_id');

                $hadirCount = 0;
                $izinCount = 0;
                $sakitCount = 0;
                $alphaCount = 0;
                $dispensasiCount = 0;
                $belumCount = 0;

                $muridList = $muridsWithStatus->map(function ($item) use ($presensiExisting, &$hadirCount, &$izinCount, &$sakitCount, &$alphaCount, &$dispensasiCount, &$belumCount) {
                    $m = $item->murid ?? $item;
                    $existing = $presensiExisting->get($m->id);

                    $status = $existing?->status ?? null;
                    $catatan = $existing?->catatan;

                    if ($status) {
                        match ($status) {
                            'Hadir' => $hadirCount++,
                            'Izin' => $izinCount++,
                            'Sakit' => $sakitCount++,
                            'Alpha' => $alphaCount++,
                            'Dispensasi' => $dispensasiCount++,
                            default => null,
                        };
                    } else {
                        $belumCount++;
                    }

                    return [
                        'murid_id' => $m->id,
                        'nama' => $m->nama_lengkap ?? $m->nama,
                        'nism' => $m->nism ?? '-',
                        'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                        'is_locked' => $item->is_locked ?? false,
                        'lock_reason' => $item->lock_reason ?? null,
                        'status' => $status,
                        'catatan' => $catatan,
                    ];
                });

                $summary = [
                    'total' => $muridList->count(),
                    'belum' => $belumCount,
                    'hadir' => $hadirCount,
                    'izin' => $izinCount,
                    'sakit' => $sakitCount,
                    'alpha' => $alphaCount,
                    'dispensasi' => $dispensasiCount,
                ];
            }
        }

        // 5. Daftar Badal Ustadz untuk pilihan pengawas pengganti
        $daftarBadal = Ustadz::where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'kode_ustadz'])
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'nama' => $u->nama_lengkap,
                    'kode' => $u->kode_ustadz,
                ];
            });

        $isWaliRuangan = false;
        if ($ruangan && $user->ustadz) {
            $isWaliRuangan = ($ruangan->ustadz_id == $user->ustadz->id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'daftar_ujian' => $daftarUjian,
                'selected_ujian_id' => $selectedUjianId,
                'daftar_ruangan' => $daftarRuangan,
                'selected_ruangan_id' => $selectedRuanganId,
                'selected_ruangan_nama' => $ruangan?->nama_ruangan ?? '',
                'nama_level' => $ruangan?->level?->nama_level ?? '',
                'is_wali_ruangan' => $isWaliRuangan,
                'wali_ruangan_nama' => $ruangan?->waliRuangan?->nama_lengkap ?? '-',
                'jadwal_list' => $jadwalList,
                'selected_jadwal_id' => $selectedJadwalId,
                'pengawas' => $pengawasData,
                'daftar_badal' => $daftarBadal,
                'murid_list' => $muridList,
                'summary' => $summary,
            ]
        ], 200);
    }

    /**
     * Simpan data presensi murid massal & pengawas ujian
     * POST /api/presensi-ujian/simpan
     */
    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
            'presensi' => 'required|array',
            'presensi.*.status' => 'nullable|in:Hadir,Sakit,Izin,Alpha,Dispensasi',
            'presensi.*.catatan' => 'nullable|string|max:255',
            'pengawas' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

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

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan {$disimpan} data presensi murid dan kehadiran pengawas ujian!",
                'data' => [
                    'total_tersimpan' => $disimpan,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses presensi ujian: ' . $e->getMessage()
            ], 500);
        }
    }
}
