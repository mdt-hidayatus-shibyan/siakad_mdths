<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\PresensiUstadz;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresensiUstadzController extends Controller
{
    /**
     * Ambil daftar jadwal mengajar harian ustadz & kelas binaan (Wali Ruangan) untuk Check-In Presensi
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

        // Jika hari libur, sesi presensi ustadz dikosongkan
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

        // 3. Query Jadwal Pelajaran:
        // - Mengambil jadwal mengajar pribadi ustadz (ustadz_id = $ustadzId)
        // - ATAU jika dia adalah Wali Ruangan, mencakup juga seluruh jadwal di ruangan binaannya
        $query = JadwalPelajaran::with(['mataPelajaran', 'ruangan', 'ustadz'])
            ->where('hari', $hari);

        if (!empty($ruanganWaliIds)) {
            $query->where(function ($q) use ($ustadzId, $ruanganWaliIds) {
                $q->where('ustadz_id', $ustadzId)
                    ->orWhereIn('ruangan_id', $ruanganWaliIds);
            });
        } else {
            $query->where('ustadz_id', $ustadzId);
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

        // Jika hari bertepatan dengan Ujian Madrasah, sesi KBM mengajar reguler TIDAK DITAMPILKAN (kosong) dan dialihkan
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

        // 5. Ambil riwayat presensi ustadz yang sudah tersimpan pada tanggal & jadwal tersebut
        $presensiTersimpan = PresensiUstadz::with(['guruPengganti'])
            ->where('tanggal', $tanggal)
            ->whereIn('jadwal_pelajaran_id', $jadwals->pluck('id'))
            ->get()
            ->keyBy('jadwal_pelajaran_id');

        $data = $jadwals->map(function ($j) use ($presensiTersimpan, $ruanganWaliIds, $ustadzId) {
            $existing = $presensiTersimpan->get($j->id);
            $isMilikWali = in_array($j->ruangan_id, $ruanganWaliIds) && ($j->ustadz_id != $ustadzId);

            $jamText = match ($j->jam_ke) {
                'Nadzoman' => '13:45 - 14:00 WIB',
                '1' => '14:00 - 14:45 WIB',
                '2' => '15:30 - 16:15 WIB',
                'Ekstra' => '20:00 - 21:00 WIB',
                default => 'Jam Ke-' . $j->jam_ke,
            };

            return [
                'jadwal_id' => $j->id,
                'jam_ke' => $j->jam_ke,
                'jam' => $jamText,
                'mapel' => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'ruangan' => $j->ruangan->nama_ruangan ?? '-',
                'guru_pengajar' => $j->ustadz->nama_lengkap ?? '-',
                'is_milik_wali' => $isMilikWali,
                'sudah_checkin' => $existing != null,
                'status' => $existing ? $existing->status : 'Belum Absen',
                'ustadz_pengganti_id' => $existing ? $existing->ustadz_pengganti_id : null,
                'ustadz_pengganti_nama' => $existing && $existing->guruPengganti ? $existing->guruPengganti->nama_lengkap : null,
                'keterangan' => $existing ? $existing->keterangan : null,
                'waktu_checkin' => $existing && $existing->updated_at ? $existing->updated_at->format('H:i') : null,
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
     * Check-In Kehadiran Ustadz per Jadwal Pelajaran (Oleh Pengajar atau Wali Ruangan)
     */
    public function checkin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Sakit,Izin,Alpha,Kosong',
            'ustadz_pengganti_id' => 'nullable|exists:ustadzs,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter check-in tidak valid.',
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
                'message' => 'Tidak dapat melakukan check-in mengajar pada hari libur (' . $ket . ').'
            ], 422);
        }

        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_id);

        // Cek hak akses: Wali Ruangan dari kelas jadwal tersebut ATAU Guru Pengajar Pribadi
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

        $isGuruPengajar = ($jadwal->ustadz_id == $ustadzId);
        $isWaliRuangan = in_array($jadwal->ruangan_id, $ruanganWaliIds);

        if (!$isGuruPengajar && !$isWaliRuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk mengisi presensi jadwal ini.'
            ], 403);
        }

        try {
            $presensi = PresensiUstadz::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'jadwal_pelajaran_id' => $jadwal->id,
                ],
                [
                    'ustadz_id' => $jadwal->ustadz_id,
                    'status' => $request->status,
                    'ustadz_pengganti_id' => ($request->status === 'Izin' || $request->status === 'Sakit') ? $request->ustadz_pengganti_id : null,
                    'keterangan' => $request->keterangan,
                    'diinput_oleh_id' => $user->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Presensi ustadz berhasil disimpan!',
                'data' => [
                    'id' => $presensi->id,
                    'jadwal_id' => $jadwal->id,
                    'tanggal' => $tanggal,
                    'status' => $presensi->status,
                    'keterangan' => $presensi->keterangan,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan check-in presensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil daftar ustadz aktif untuk pilihan Guru Badal / Pengganti
     */
    public function getDaftarUstadz(Request $request)
    {
        $user = $request->user();
        $ustadzId = $user->ustadz->id ?? null;

        $ustadzList = Ustadz::where('is_active', true)
            ->where('id', '!=', $ustadzId)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'jenis_kelamin', 'no_hp']);

        return response()->json([
            'success' => true,
            'data' => $ustadzList
        ], 200);
    }

    /**
     * Ambil akumulasi & riwayat presensi mengajar ustadz
     */
    public function getRiwayat(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;
        $ustadzId = $ustadz->id ?? null;

        if (!$ustadzId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_hadir' => 0,
                    'total_izin' => 0,
                    'total_sakit' => 0,
                    'total_alpha' => 0,
                    'riwayat' => [],
                ]
            ], 200);
        }

        $riwayat = PresensiUstadz::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.ruangan', 'guruPengganti'])
            ->where('ustadz_id', $ustadzId)
            ->latest('tanggal')
            ->take(50)
            ->get();

        $totalHadir = $riwayat->where('status', 'Hadir')->count();
        $totalIzin  = $riwayat->where('status', 'Izin')->count();
        $totalSakit = $riwayat->where('status', 'Sakit')->count();
        $totalAlpha = $riwayat->where('status', 'Alpha')->count();

        $formatted = $riwayat->map(function ($r) {
            return [
                'id' => $r->id,
                'tanggal' => $r->tanggal,
                'mapel' => $r->jadwalPelajaran->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'ruangan' => $r->jadwalPelajaran->ruangan->nama_ruangan ?? '-',
                'status' => $r->status,
                'ustadz_pengganti' => $r->guruPengganti->nama_lengkap ?? null,
                'keterangan' => $r->keterangan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_hadir' => $totalHadir,
                'total_izin' => $totalIzin,
                'total_sakit' => $totalSakit,
                'total_alpha' => $totalAlpha,
                'riwayat' => $formatted,
            ]
        ], 200);
    }
}
