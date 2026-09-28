<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\PresensiMurid;
use App\Models\PresensiUstadz;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BadalController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Ambil daftar semua ruangan/kelas aktif untuk pilihan Guru Pengganti (Badal)
     */
    public function getRuanganList(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 0;

        $query = Ruangan::with(['level', 'waliRuangan'])
            ->where(function ($q) use ($tahunId) {
                if ($tahunId) {
                    $q->where('tahun_pelajaran_id', $tahunId)
                        ->orWhereNull('tahun_pelajaran_id');
                }
            });

        if ($request->filled('query')) {
            $search = $request->input('query');
            $query->where(function ($q) use ($search) {
                $q->where('nama_ruangan', 'like', "%{$search}%")
                    ->orWhereHas('level', function ($lq) use ($search) {
                        $lq->where('nama_level', 'like', "%{$search}%");
                    })
                    ->orWhereHas('waliRuangan', function ($wq) use ($search) {
                        $wq->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        $ruangans = $query->get()->sortBy([
            fn($a, $b) => ($a->level?->urutan_level ?? 999) <=> ($b->level?->urutan_level ?? 999),
            fn($a, $b) => strnatcasecmp($a->nama_ruangan ?? '', $b->nama_ruangan ?? ''),
        ])->values();

        $data = $ruangans->map(function ($r) use ($tahunId) {
            $totalMurid = $this->muridRuanganRepo->getMuridByRuanganAndTahun($r->id, $tahunId, 'Aktif')->count();
            return [
                'id' => $r->id,
                'nama_ruangan' => $r->nama_ruangan,
                'level' => $r->level->nama_level ?? '-',
                'urutan_level' => $r->level->urutan_level ?? 999,
                'wali_kelas' => $r->waliRuangan->nama_lengkap ?? 'Belum Diatur',
                'wali_kelas_id' => $r->ustadz_id,
                'total_murid' => $totalMurid,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Ambil jadwal pelajaran dan status presensi suatu ruangan pada tanggal tertentu
     */
    public function getJadwalRuangan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ruangan_id' => 'required|exists:ruangans,id',
            'tanggal' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $currentUstadz = $user->ustadz;
        $currentUstadzId = $currentUstadz->id ?? null;

        $ruangan = Ruangan::with(['level', 'waliRuangan'])->findOrFail($request->ruangan_id);
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

        // 1. Cek Hari Libur
        $libur = HariLibur::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        $isLibur = ($libur != null) || ($hari === 'Jumat');
        $keteranganLibur = $libur ? $libur->keterangan : ($hari === 'Jumat' ? 'Libur Rutin Mingguan (Hari Jumat)' : null);

        // 2. Cek Masa Ujian Madrasah
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

        // Tahun Pelajaran Aktif
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif->id ?? 0;
        $totalMuridRuangan = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruangan->id, $tahunId, 'Aktif')->count();

        // 3. Query Jadwal Pelajaran di Ruangan tersebut pada hari terpilih
        $jadwals = JadwalPelajaran::with(['mataPelajaran', 'ruangan', 'ustadz', 'ustadzs'])
            ->where('ruangan_id', $ruangan->id)
            ->where('hari', $hari)
            ->get()
            ->sortBy(function ($j) {
                return match ($j->jam_ke) {
                    'Nadzoman' => 1,
                    '1' => 2,
                    '2' => 3,
                    'Ekstra' => 4,
                    default => 5
                };
            })
            ->values();

        $jadwalIds = $jadwals->pluck('id')->toArray();

        // 4. Presensi Ustadz tersimpan pada jadwal-jadwal tersebut
        $presensiUstadzList = !empty($jadwalIds)
            ? PresensiUstadz::with(['guruPengganti'])
            ->where('tanggal', $tanggal)
            ->whereIn('jadwal_pelajaran_id', $jadwalIds)
            ->get()
            : collect();

        // 5. Presensi Murid tersimpan pada jadwal-jadwal tersebut
        $presensiMuridList = !empty($jadwalIds)
            ? PresensiMurid::where('tanggal', $tanggal)
            ->whereIn('jadwal_pelajaran_id', $jadwalIds)
            ->get()
            : collect();

        $data = $jadwals->map(function ($j) use ($presensiUstadzList, $presensiMuridList, $currentUstadzId, $totalMuridRuangan) {
            $presUstadz = $presensiUstadzList->firstWhere('jadwal_pelajaran_id', $j->id);
            $presMuridJadwal = $presensiMuridList->where('jadwal_pelajaran_id', $j->id);

            $isPengampuAsli = $j->daftar_ustadz->contains('id', $currentUstadzId);

            $jamText = match ($j->jam_ke) {
                'Nadzoman' => '13:45 - 14:00 WIB',
                '1' => '14:00 - 14:45 WIB',
                '2' => '15:30 - 16:15 WIB',
                'Ekstra' => '20:00 - 21:00 WIB',
                default => 'Jam Ke-' . $j->jam_ke,
            };

            // Summary presensi murid
            $sudahAbsenMurid = $presMuridJadwal->isNotEmpty();
            $totalHadir = $presMuridJadwal->where('status', 'Hadir')->count();
            $totalIzin = $presMuridJadwal->where('status', 'Izin')->count();
            $totalSakit = $presMuridJadwal->where('status', 'Sakit')->count();
            $totalAlpha = $presMuridJadwal->where('status', 'Alpha')->count();
            $totalDispensasi = $presMuridJadwal->where('status', 'Dispensasi')->count();
            $totalTerisi = $presMuridJadwal->count();

            // Ustadz pengganti info
            $ustadzPenggantiId = $presUstadz?->ustadz_pengganti_id;
            $ustadzPenggantiNama = $presUstadz?->guruPengganti?->nama_lengkap;
            $isSayaPengganti = ($ustadzPenggantiId && $ustadzPenggantiId == $currentUstadzId);

            return [
                'jadwal_id' => $j->id,
                'jam_ke' => $j->jam_ke,
                'jam' => $jamText,
                'mapel' => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'guru_pengampu' => $j->daftar_nama_pengampu,
                'guru_utama_id' => $j->ustadz_id,
                'is_pengampu_asli' => $isPengampuAsli,

                // Status Presensi Ustadz
                'ustadz_status' => $presUstadz ? $presUstadz->status : 'Belum Absen',
                'ustadz_keterangan' => $presUstadz?->keterangan,
                'ustadz_pengganti_id' => $ustadzPenggantiId,
                'ustadz_pengganti_nama' => $ustadzPenggantiNama,
                'is_saya_pengganti' => $isSayaPengganti,

                // Status Presensi Murid
                'sudah_absen_murid' => $sudahAbsenMurid,
                'total_murid' => $totalMuridRuangan,
                'total_terisi' => $totalTerisi,
                'total_hadir' => $totalHadir,
                'total_izin' => $totalIzin,
                'total_sakit' => $totalSakit,
                'total_alpha' => $totalAlpha,
                'total_dispensasi' => $totalDispensasi,
            ];
        });

        return response()->json([
            'success' => true,
            'ruangan' => [
                'id' => $ruangan->id,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'level' => $ruangan->level->nama_level ?? '-',
                'wali_kelas' => $ruangan->waliRuangan->nama_lengkap ?? 'Belum Diatur',
                'total_murid' => $totalMuridRuangan,
            ],
            'tanggal' => $tanggal,
            'hari' => $hari,
            'is_libur' => $isLibur,
            'keterangan_libur' => $keteranganLibur,
            'is_ujian' => $isUjian,
            'nama_ujian' => $namaUjian,
            'ujian_id' => $ujianId,
            'data' => $data
        ], 200);
    }
}
