<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Ujian\DispensasiUjian;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use App\Services\NilaiUjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NilaiUjianController extends Controller
{
    protected $muridRuanganRepo;
    protected $nilaiUjianService;

    public function __construct(MuridRuanganRepository $muridRuanganRepo, NilaiUjianService $nilaiUjianService)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
        $this->nilaiUjianService = $nilaiUjianService;
    }

    protected function getAccessibleRuanganIds($user, $tahunPelajaranId)
    {
        if (!$user->ustadz) {
            return Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->pluck('id')->toArray();
        }

        $ustadz = $user->ustadz;
        $ruanganWaliIds = Ruangan::where('ustadz_id', $ustadz->id)
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->pluck('id')
            ->toArray();

        $ruanganMengajarIds = JadwalPelajaran::where('ustadz_id', $ustadz->id)
            ->whereHas('ruangan', fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->pluck('ruangan_id')
            ->toArray();

        $accessibleIds = array_unique(array_merge($ruanganWaliIds, $ruanganMengajarIds));

        if (empty($accessibleIds)) {
            return Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->pluck('id')->toArray();
        }

        return $accessibleIds;
    }

    public function getUjianList(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunPelajaranId = $request->tahun_id ?? $tahunAktif?->id ?? TahunPelajaran::orderBy('id', 'desc')->value('id');

        $queryUjian = Ujian::with('semester')->where('tahun_pelajaran_id', $tahunPelajaranId);

        if ($request->ruangan_id) {
            $ruangan = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruangan && $ruangan->level) {
                $levelNama = $ruangan->level->nama_level ?? '';
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
                if ($isKelasAkhir) {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }
            }
        }

        $ujians = $queryUjian->orderBy('id', 'asc')->get();

        $data = $ujians->map(function ($u) {
            return [
                'id' => $u->id,
                'nama_ujian' => $u->nama_ujian,
                'tipe_ujian' => $u->tipe_ujian ?? $u->jenis_ujian ?? 'IMDA 1',
                'semester' => $u->semester->nama_semester ?? ($u->semester_id == 9 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)'),
                'tanggal_mulai' => $u->tanggal_mulai ? (is_string($u->tanggal_mulai) ? $u->tanggal_mulai : $u->tanggal_mulai->format('Y-m-d')) : date('Y-m-d'),
                'tanggal_selesai' => $u->tanggal_selesai ? (is_string($u->tanggal_selesai) ? $u->tanggal_selesai : $u->tanggal_selesai->format('Y-m-d')) : date('Y-m-d'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    public function getMapelJadwal(Request $request)
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
            $ruangan = Ruangan::with('level')->find($selectedRuanganId);
        }

        // 2. Daftar Agenda Ujian berdasarkan level ruangan (Kelas Akhir: IMDA 1 & IMNI, Selainnya: IMDA 1 & IMDA 2)
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

        // 3. Ambil Jadwal Mapel Ujian sesuai Ujian & Level Ruangan
        $jadwalList = collect();
        $totalMurid = 0;
        $isWaliRuangan = false;

        if ($ruangan) {
            $isWaliRuangan = ($user->ustadz && $ruangan->ustadz_id == $user->ustadz->id);
            $totalMurid = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunPelajaranId)->count();

            if ($selectedUjianId) {
                $queryJadwals = JadwalUjian::with(['mataPelajaran', 'pengawas'])
                    ->where('ujian_id', $selectedUjianId)
                    ->where('level_id', $ruangan->level_id);

                // Jika bukan wali ruangan, hanya tampilkan mata pelajaran yang diampu oleh ustadz tersebut
                if (!$isWaliRuangan && $user->ustadz) {
                    $mapelDiampuIds = JadwalPelajaran::where('ruangan_id', $ruangan->id)
                        ->where('ustadz_id', $user->ustadz->id)
                        ->pluck('mata_pelajaran_id')
                        ->toArray();

                    $queryJadwals->whereIn('mata_pelajaran_id', $mapelDiampuIds);
                }

                $jadwals = $queryJadwals->orderBy('tanggal_ujian', 'asc')
                    ->get();

                $jadwalList = $jadwals->map(function ($j) use ($selectedUjianId, $ruangan, $totalMurid) {
                    $jamMulai = $j->waktu_mulai ? (is_string($j->waktu_mulai) ? substr($j->waktu_mulai, 11, 5) : $j->waktu_mulai->format('H:i')) : '08:00';
                    $jamSelesai = $j->waktu_selesai ? (is_string($j->waktu_selesai) ? substr($j->waktu_selesai, 11, 5) : $j->waktu_selesai->format('H:i')) : '09:00';
                    $tanggalUjian = $j->tanggal_ujian ? (is_string($j->tanggal_ujian) ? substr($j->tanggal_ujian, 0, 10) : $j->tanggal_ujian->format('Y-m-d')) : date('Y-m-d');

                    // Hitung progres nilai di ruangan ini
                    $dinilaiCount = NilaiUjian::where('ujian_id', $selectedUjianId)
                        ->where('ruangan_id', $ruangan->id)
                        ->where('jadwal_ujian_id', $j->id)
                        ->whereNotNull('nilai')
                        ->count();

                    $isPublished = NilaiUjian::where('ujian_id', $selectedUjianId)
                        ->where('ruangan_id', $ruangan->id)
                        ->where('jadwal_ujian_id', $j->id)
                        ->where('is_published', true)
                        ->exists();

                    $statusInput = 'Belum Diisi';
                    if ($isPublished) {
                        $statusInput = 'Selesai';
                    } elseif ($dinilaiCount > 0) {
                        $statusInput = ($dinilaiCount >= $totalMurid && $totalMurid > 0) ? 'Lengkap (Draf)' : 'Draf';
                    }

                    return [
                        'id' => $j->id,
                        'mata_pelajaran_id' => $j->mata_pelajaran_id,
                        'nama_mapel' => $j->nama_mapel,
                        'hari_tanggal' => $j->hari_tanggal,
                        'hari_tanggal_singkat' => $j->hari_tanggal_singkat,
                        'tanggal_ujian' => $j->hari_tanggal_singkat,
                        'tanggal_ujian_raw' => $j->getRawOriginal('tanggal_ujian') ?? date('Y-m-d'),
                        'waktu_mulai' => $j->jam_mulai_format,
                        'waktu_selesai' => $j->jam_selesai_format,
                        'pengawas_nama' => $j->pengawas?->nama_lengkap ?? $j->pengawas?->nama ?? 'Belum Ditentukan',
                        'total_murid' => $totalMurid,
                        'jumlah_dinilai' => $dinilaiCount,
                        'status_input' => $statusInput,
                        'is_published' => $isPublished,
                    ];
                });
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'daftar_ruangan' => $daftarRuangan,
                'selected_ruangan_id' => $selectedRuanganId,
                'selected_ruangan_nama' => $ruangan?->nama_ruangan ?? '',
                'nama_level' => $ruangan?->level?->nama_level ?? '',
                'is_wali_ruangan' => $isWaliRuangan,
                'daftar_ujian' => $daftarUjian,
                'selected_ujian_id' => $selectedUjianId,
                'jadwal_list' => $jadwalList,
            ]
        ], 200);
    }

    public function getInputData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'nullable|exists:jadwal_ujians,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $ujian = Ujian::findOrFail($request->ujian_id);
        $ruangan = Ruangan::with('level')->findOrFail($request->ruangan_id);
        $jadwal = $request->jadwal_ujian_id ? JadwalUjian::with('mataPelajaran', 'pengawas')->find($request->jadwal_ujian_id) : null;

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? $ruangan->tahun_pelajaran_id;

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId);
        $muridsWithStatus = $this->nilaiUjianService->evaluasiSyaratAdmin($ujian, $ruangan, $murids);

        // Ambil nilai yang sudah tersimpan untuk jadwal_ujian_id ini
        $queryNilai = NilaiUjian::where('ujian_id', $ujian->id)
            ->where('ruangan_id', $ruangan->id);

        if ($jadwal) {
            $queryNilai->where('jadwal_ujian_id', $jadwal->id);
        }

        $nilaiTersimpan = $queryNilai->get()->keyBy('murid_id');

        $formattedMurids = $muridsWithStatus->map(function ($m) use ($nilaiTersimpan) {
            $muridModel = $m->murid ?? $m;
            $existing = $nilaiTersimpan->get($muridModel->id);

            return [
                'murid_id' => $muridModel->id,
                'nism' => $muridModel->nism ?? '-',
                'nama' => $muridModel->nama_lengkap ?? $muridModel->nama,
                'jenis_kelamin' => $muridModel->jenis_kelamin ?? 'L',
                'is_locked' => $m->is_locked ?? false,
                'lock_reason' => $m->lock_reason ?? null,
                'nilai' => $existing ? (float) $existing->nilai : null,
                'is_published' => $existing ? (bool) $existing->is_published : false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => [
                    'id' => $ujian->id,
                    'nama_ujian' => $ujian->nama_ujian,
                    'tipe_ujian' => $ujian->tipe_ujian ?? 'IMDA',
                ],
                'ruangan' => [
                    'id' => $ruangan->id,
                    'nama_ruangan' => $ruangan->nama_ruangan,
                    'nama_level' => $ruangan->level?->nama_level ?? '',
                ],
                'jadwal' => $jadwal ? [
                    'id' => $jadwal->id,
                    'nama_mapel' => $jadwal->nama_mapel,
                    'hari_tanggal' => $jadwal->hari_tanggal,
                    'hari_tanggal_singkat' => $jadwal->hari_tanggal_singkat,
                    'tanggal_ujian' => $jadwal->hari_tanggal_singkat,
                    'tanggal_ujian_raw' => $jadwal->getRawOriginal('tanggal_ujian') ?? date('Y-m-d'),
                    'waktu_mulai' => $jadwal->jam_mulai_format,
                    'waktu_selesai' => $jadwal->jam_selesai_format,
                    'pengawas_nama' => $jadwal->pengawas?->nama_lengkap ?? $jadwal->pengawas?->nama ?? 'Belum Ditentukan',
                ] : null,
                'murids' => $formattedMurids,
            ]
        ], 200);
    }

    public function simpanNilai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
            'action' => 'required|in:draft,publish',
            'nilai' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input nilai tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $ujianId = $request->ujian_id;
        $ruanganId = $request->ruangan_id;
        $jadwalUjianId = $request->jadwal_ujian_id;
        $isPublished = ($request->action === 'publish');
        $userId = $request->user()->id;

        DB::beginTransaction();
        try {
            foreach ($request->nilai as $muridId => $score) {
                if ($score !== null && $score !== '') {
                    NilaiUjian::updateOrCreate(
                        [
                            'ujian_id' => $ujianId,
                            'ruangan_id' => $ruanganId,
                            'jadwal_ujian_id' => $jadwalUjianId,
                            'murid_id' => $muridId,
                        ],
                        [
                            'nilai' => (float) $score,
                            'is_published' => $isPublished,
                            'diinput_oleh' => $userId,
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isPublished ? 'Nilai resmi berhasil dipublikasikan ke rapor!' : 'Draf nilai berhasil disimpan.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLeger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'ruangan_id' => 'required|exists:ruangans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $ujian = Ujian::with('semester')->findOrFail($request->ujian_id);
        $ruangan = Ruangan::with('level')->findOrFail($request->ruangan_id);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? $ruangan->tahun_pelajaran_id;

        // 1. Ekstrak Daftar Mapel dari Jadwal Ujian
        $jadwals = JadwalUjian::with('mataPelajaran')
            ->where('ujian_id', $ujian->id)
            ->where('level_id', $ruangan->level_id)
            ->orderBy('tanggal_ujian', 'asc')
            ->get();

        $kolomMapel = [];
        foreach ($jadwals as $j) {
            $kolomMapel[] = [
                'id' => $j->id,
                'nama_mapel' => $j->nama_mapel,
                'kode_mapel' => $j->mataPelajaran->kode_mapel ?? null,
            ];
        }

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId);

        $allNilai = NilaiUjian::where('ujian_id', $ujian->id)
            ->where('ruangan_id', $ruangan->id)
            ->get();

        $rows = $murids->map(function ($m) use ($allNilai, $jadwals) {
            $nilaiMurid = $allNilai->where('murid_id', $m->id);
            $total = 0;
            $mapelScores = [];
            $berisiCount = 0;

            foreach ($jadwals as $j) {
                $n = $nilaiMurid->firstWhere('jadwal_ujian_id', $j->id);
                $angka = $n ? (float) $n->nilai : null;
                $mapelScores[$j->nama_mapel] = $angka;
                if ($angka !== null) {
                    $total += $angka;
                    $berisiCount++;
                }
            }

            $rataRata = $berisiCount > 0 ? round($total / $berisiCount, 2) : 0;

            // Predikat Huruf
            $predikat = 'E';
            if ($rataRata >= 90) $predikat = 'A+';
            elseif ($rataRata >= 85) $predikat = 'A';
            elseif ($rataRata >= 80) $predikat = 'B+';
            elseif ($rataRata >= 75) $predikat = 'B';
            elseif ($rataRata >= 70) $predikat = 'C+';
            elseif ($rataRata >= 65) $predikat = 'C';
            elseif ($rataRata >= 60) $predikat = 'D';

            return [
                'murid_id' => $m->id,
                'nism' => $m->nism,
                'nama' => $m->nama_lengkap ?? $m->nama,
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'nilai_mapel' => $mapelScores,
                'total' => round($total, 1),
                'rata_rata' => $rataRata,
                'predikat' => $predikat,
                'jumlah_terisi' => $berisiCount,
                'total_mapel' => count($jadwals),
            ];
        });

        // Urutkan berdasarkan total nilai tertinggi untuk ranking kelas
        $sortedRows = $rows->sortByDesc('total')->values();

        $rankedLeger = $sortedRows->map(function ($row, $index) {
            $row['ranking'] = $index + 1;
            return $row;
        });

        // Hitung statistik kelas
        $rataRataKelas = $rankedLeger->isNotEmpty() ? round($rankedLeger->avg('rata_rata'), 2) : 0;
        $nilaiTertinggi = $rankedLeger->isNotEmpty() ? $rankedLeger->max('total') : 0;
        $nilaiTerendah = $rankedLeger->isNotEmpty() ? $rankedLeger->min('total') : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => [
                    'id' => $ujian->id,
                    'nama_ujian' => $ujian->nama_ujian,
                    'tipe_ujian' => $ujian->tipe_ujian ?? 'IMDA',
                    'semester' => $ujian->semester->nama_semester ?? '-',
                ],
                'ruangan' => [
                    'id' => $ruangan->id,
                    'nama_ruangan' => $ruangan->nama_ruangan,
                    'nama_level' => $ruangan->level?->nama_level ?? '',
                ],
                'kolom_mapel' => $kolomMapel,
                'statistik' => [
                    'total_murid' => count($rankedLeger),
                    'rata_rata_kelas' => $rataRataKelas,
                    'nilai_tertinggi' => $nilaiTertinggi,
                    'nilai_terendah' => $nilaiTerendah,
                ],
                'leger' => $rankedLeger,
            ]
        ], 200);
    }

    public function beriDispensasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'murid_id' => 'required|exists:murids,id',
            'alasan_izin' => 'nullable|string|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data dispensasi tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $dispensasi = DispensasiUjian::updateOrCreate(
            [
                'ujian_id' => $request->ujian_id,
                'murid_id' => $request->murid_id,
            ],
            [
                'alasan_izin' => $request->alasan_izin ?: 'Dispensasi Ujian dari Ustadz / Wali Ruangan',
                'diizinkan_oleh' => $request->user()?->id ?? 1
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Dispensasi ujian berhasil diberikan. Akses input nilai terbuka.',
            'data' => $dispensasi
        ], 200);
    }

    public function batalkanDispensasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ujian_id' => 'required|exists:ujians,id',
            'murid_id' => 'required|exists:murids,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        DispensasiUjian::where('ujian_id', $request->ujian_id)
            ->where('murid_id', $request->murid_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dispensasi ujian berhasil dibatalkan.'
        ], 200);
    }
}
