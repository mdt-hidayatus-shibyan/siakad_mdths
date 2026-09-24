<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresensiMuridController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Ambil daftar sesi KBM murid untuk presensi kelas harian
     */
    public function getSesi(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan data Ustadz/Pengajar.',
                'data' => []
            ], 403);
        }

        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal)->format('Y-m-d') : date('Y-m-d');

        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari = $mapHari[Carbon::parse($tanggal)->format('l')];

        // 1. Cek Hari Libur (HariLibur Kalender & Libur Rutin Jumat)
        $libur = HariLibur::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        $isLibur = ($libur != null) || ($hari === 'Jumat');
        $keteranganLibur = $libur ? $libur->keterangan : ($hari === 'Jumat' ? 'Libur Rutin Mingguan (Hari Jumat)' : null);

        // Jika hari libur, sesi presensi murid TIDAK DITAMPILKAN (kosong)
        if ($isLibur) {
            return response()->json([
                'success' => true,
                'is_libur' => true,
                'keterangan_libur' => $keteranganLibur,
                'data' => []
            ], 200);
        }

        // 2. Cek apakah ustadz yang login adalah Wali Ruangan
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $ruanganWaliList = Ruangan::where('ustadz_id', $ustadzId)
            ->when($tahunAktif, function ($q) use ($tahunAktif) {
                $q->where(function ($sub) use ($tahunAktif) {
                    $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                        ->orWhereNull('tahun_pelajaran_id');
                });
            })
            ->get();

        $ruanganWaliIds = $ruanganWaliList->pluck('id')->toArray();
        $ruanganWaliNama = $ruanganWaliList->pluck('nama_ruangan')->join(', ');

        // 3. Query Jadwal Hari Aktif:
        // - Mengambil jadwal mengajar pribadi ustadz (forUstadz: utama maupun multi-pengampu)
        // - ATAU jika dia adalah Wali Ruangan, mencakup juga seluruh jadwal di ruangan binaannya
        $query = JadwalPelajaran::with(['mataPelajaran', 'ruangan', 'ustadz', 'ustadzs'])
            ->where('hari', $hari);

        if (!empty($ruanganWaliIds)) {
            $query->where(function ($q) use ($ustadzId, $ruanganWaliIds) {
                $q->forUstadz($ustadzId)
                    ->orWhereIn('ruangan_id', $ruanganWaliIds);
            });
        } else {
            $query->forUstadz($ustadzId);
        }

        $jadwals = $query->get()->sortBy([
            fn($a, $b) => strnatcasecmp($a->ruangan?->nama_ruangan ?? '', $b->ruangan?->nama_ruangan ?? ''),
            fn($a, $b) => (match ($a->jam_ke) {
                'Nadzoman' => 1,
                '1' => 2,
                '2' => 3,
                'Ekstra' => 4,
                default => 5
            })
                <=> (match ($b->jam_ke) {
                    'Nadzoman' => 1,
                    '1' => 2,
                    '2' => 3,
                    'Ekstra' => 4,
                    default => 5
                }),
        ])->values();

        // 4. Cek apakah tanggal bertepatan dengan masa / jadwal Ujian Madrasah
        $ujian = \App\Models\Ujian\Ujian::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        if (!$ujian) {
            $jadwalUjianAda = \App\Models\Ujian\JadwalUjian::whereDate('tanggal_ujian', $tanggal)->first();
            if ($jadwalUjianAda) {
                $ujian = $jadwalUjianAda->ujian;
            }
        }

        $isUjian = ($ujian != null);
        $namaUjian = $ujian ? $ujian->nama_ujian : null;
        $ujianId = $ujian ? $ujian->id : null;

        // Jika hari bertepatan dengan Ujian Madrasah, sesi KBM reguler TIDAK DITAMPILKAN (kosong) dan dialihkan
        if ($isUjian) {
            return response()->json([
                'success' => true,
                'is_libur' => false,
                'keterangan_libur' => null,
                'is_ujian' => true,
                'nama_ujian' => $namaUjian,
                'ujian_id' => $ujianId,
                'ruangan_wali' => $ruanganWaliNama ?: null,
                'data' => []
            ], 200);
        }

        // Bulk query status presensi jadwal pada tanggal terpilih untuk eliminasi N+1
        $jadwalIds = $jadwals->pluck('id')->toArray();
        $sudahAbsenMap = !empty($jadwalIds)
            ? PresensiMurid::whereIn('jadwal_pelajaran_id', $jadwalIds)
            ->where('tanggal', $tanggal)
            ->pluck('jadwal_pelajaran_id')
            ->flip()
            ->toArray()
            : [];

        $data = $jadwals->map(function ($j) use ($sudahAbsenMap, $ruanganWaliIds, $ustadzId) {
            $sudahAbsen = isset($sudahAbsenMap[$j->id]);
            $isPengampuJadwal = $j->daftar_ustadz->contains('id', $ustadzId);
            $isMilikWali = in_array($j->ruangan_id, $ruanganWaliIds) && !$isPengampuJadwal;

            $jamText = match ($j->jam_ke) {
                'Nadzoman' => '13:45 - 14:00 WIB',
                '1' => '14:00 - 14:45 WIB',
                '2' => '15:30 - 16:15 WIB',
                'Ekstra' => '20:00 - 21:00 WIB',
                default => 'Jam Ke-' . $j->jam_ke,
            };

            return [
                'id' => $j->id,
                'jam' => $jamText,
                'pelajaran' => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'kelas' => $j->ruangan->nama_ruangan ?? '-',
                'guru' => $j->daftar_nama_pengampu,
                'is_milik_wali' => $isMilikWali,
                'sudah_absen' => $sudahAbsen,
            ];
        });

        return response()->json([
            'success' => true,
            'is_libur' => false,
            'keterangan_libur' => null,
            'is_ujian' => false,
            'nama_ujian' => null,
            'ujian_id' => null,
            'ruangan_wali' => $ruanganWaliNama ?: null,
            'data' => $data
        ], 200);
    }

    /**
     * Ambil daftar murid pada sesi KBM untuk diisi presensinya
     */
    public function getMurid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil Ustadz.'
            ], 403);
        }

        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

        // Cek apakah tanggal yang dipilih bertepatan dengan hari libur
        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari = $mapHari[Carbon::parse($tanggal)->format('l')];

        $libur = HariLibur::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        if ($libur || $hari === 'Jumat') {
            $ket = $libur ? $libur->keterangan : 'Libur Rutin Mingguan (Hari Jumat)';
            return response()->json([
                'success' => false,
                'is_libur' => true,
                'message' => 'Hari ini adalah hari libur (' . $ket . '). Presensi murid ditiadakan.',
                'data' => []
            ], 422);
        }

        $jadwal = JadwalPelajaran::with(['ruangan', 'ustadz', 'ustadzs'])->findOrFail($request->jadwal_id);

        // Validasi Otorisasi: Guru Pengajar (Utama atau Team Teaching) ATAU Wali Ruangan dari kelas terkait
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $ruanganWaliIds = Ruangan::where('ustadz_id', $ustadzId)
            ->when($tahunAktif, function ($q) use ($tahunAktif) {
                $q->where(function ($sub) use ($tahunAktif) {
                    $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                        ->orWhereNull('tahun_pelajaran_id');
                });
            })
            ->pluck('id')
            ->toArray();

        $isGuruPengajar = $jadwal->daftar_ustadz->contains('id', $ustadzId);
        $isWaliRuangan = in_array($jadwal->ruangan_id, $ruanganWaliIds);

        if (!$isGuruPengajar && !$isWaliRuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk mengakses presensi murid pada jadwal ini.'
            ], 403);
        }

        // Cari Tahun Pelajaran dari Tanggal / Bulan Hijriyah
        $bulan = BulanHijriyah::whereDate('tanggal_mulai_masehi', '<=', $tanggal)
            ->whereDate('tanggal_selesai_masehi', '>=', $tanggal)
            ->first();

        $tahunPelajaranId = $bulan ? $bulan->tahun_pelajaran_id : null;
        if (!$tahunPelajaranId) {
            $sem = Semester::where('is_active', true)->first();
            $tahunPelajaranId = $sem ? $sem->tahun_pelajaran_id : null;
        }
        if (!$tahunPelajaranId) {
            $tahunPelajaranId = $jadwal->ruangan->tahun_pelajaran_id ?? ($tahunAktif->id ?? null);
        }

        // Ambil data murid kelas
        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($jadwal->ruangan_id, $tahunPelajaranId, 'Aktif');

        // Ambil presensi tersimpan jika sudah pernah diinput
        $presensiTersimpan = PresensiMurid::where('tanggal', $tanggal)
            ->where('jadwal_pelajaran_id', $jadwal->id)
            ->get()
            ->keyBy('murid_id');

        $data = $murids->map(function ($m) use ($presensiTersimpan) {
            $existing = $presensiTersimpan->get($m->id);
            return [
                'murid_id' => $m->id,
                'nama' => $m->nama_lengkap ?? $m->nama,
                'nism' => $m->nism,
                'jenis_kelamin' => $m->jenis_kelamin,
                'status' => $existing ? $existing->status : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Simpan / Perbarui Presensi Murid KBM
     */
    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
            'presensi' => 'required|array',
            'presensi.*.murid_id' => 'required|exists:murids,id',
            'presensi.*.status' => 'nullable|in:Hadir,Sakit,Izin,Alpha,Dispensasi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data presensi tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil Ustadz.'
            ], 403);
        }

        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

        // Cek proteksi hari libur
        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari = $mapHari[Carbon::parse($tanggal)->format('l')];

        $libur = HariLibur::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        if ($libur || $hari === 'Jumat') {
            $ket = $libur ? $libur->keterangan : 'Libur Rutin Mingguan (Hari Jumat)';
            return response()->json([
                'success' => false,
                'message' => 'Pencatatan presensi ditolak karena bertepatan dengan hari libur (' . $ket . ').'
            ], 422);
        }

        $jadwal = JadwalPelajaran::with(['ustadz', 'ustadzs'])->findOrFail($request->jadwal_id);

        // Validasi Otorisasi: Guru Pengajar (Utama atau Team Teaching) ATAU Wali Ruangan dari kelas terkait
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $ruanganWaliIds = Ruangan::where('ustadz_id', $ustadzId)
            ->when($tahunAktif, function ($q) use ($tahunAktif) {
                $q->where(function ($sub) use ($tahunAktif) {
                    $sub->where('tahun_pelajaran_id', $tahunAktif->id)
                        ->orWhereNull('tahun_pelajaran_id');
                });
            })
            ->pluck('id')
            ->toArray();

        $isGuruPengajar = $jadwal->daftar_ustadz->contains('id', $ustadzId);
        $isWaliRuangan = in_array($jadwal->ruangan_id, $ruanganWaliIds);

        if (!$isGuruPengajar && !$isWaliRuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk menyimpan presensi murid pada jadwal ini.'
            ], 403);
        }

        // Cari semester berdasarkan tanggal
        $bulan = BulanHijriyah::whereDate('tanggal_mulai_masehi', '<=', $tanggal)
            ->whereDate('tanggal_selesai_masehi', '>=', $tanggal)
            ->first();

        $semesterId = $bulan ? $bulan->semester_id : null;
        if (!$semesterId) {
            $sem = Semester::where('is_active', true)->first();
            $semesterId = $sem ? $sem->id : null;
        }

        DB::beginTransaction();
        try {
            foreach ($request->presensi as $item) {
                if (!empty($item['status'])) {
                    PresensiMurid::updateOrCreate(
                        [
                            'jadwal_pelajaran_id' => $jadwal->id,
                            'murid_id' => $item['murid_id'],
                            'tanggal' => $tanggal,
                        ],
                        [
                            'status' => $item['status'],
                            'semester_id' => $semesterId,
                        ]
                    );
                } else {
                    // Jika status kosong/null, hapus data presensi jika sebelumnya ada
                    PresensiMurid::where('jadwal_pelajaran_id', $jadwal->id)
                        ->where('murid_id', $item['murid_id'])
                        ->where('tanggal', $tanggal)
                        ->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presensi murid berhasil disimpan ke sistem!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()
            ], 500);
        }
    }
}
