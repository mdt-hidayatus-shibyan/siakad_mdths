<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\KalendarPendidikan;
use App\Models\PresensiKegiatanUstadz;
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

        // 1. Cek Hari Libur / Bebas KBM Seharian Penuh & Libur Rutin Jumat
        $checkLiburSeharian = HariLibur::checkBebasKbm($tanggal, null);
        $isLibur = $checkLiburSeharian['is_libur'] && $checkLiburSeharian['is_seharian'];
        $keteranganLibur = $isLibur ? $checkLiburSeharian['keterangan'] : null;

        // Jika hari libur seharian penuh, sesi presensi ustadz dikosongkan
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
            fn($a, $b) => (match ((string) $a->jam_ke) {
                'Nadzoman' => 1,
                '1' => 2,
                '2' => 3,
                'Ekstra' => 4,
                default => is_numeric($a->jam_ke) ? (int)$a->jam_ke + 10 : 99
            })
                <=> (match ((string) $b->jam_ke) {
                    'Nadzoman' => 1,
                    '1' => 2,
                    '2' => 3,
                    'Ekstra' => 4,
                    default => is_numeric($b->jam_ke) ? (int)$b->jam_ke + 10 : 99
                }),
            fn($a, $b) => strnatcasecmp($a->ruangan?->nama_ruangan ?? '', $b->ruangan?->nama_ruangan ?? ''),
        ])->values();

        // 4. Cek apakah tanggal bertepatan dengan Event / Kegiatan Khusus (Non-KBM / Haflah / Multi-Sesi)
        $eventPresensi = KalendarPendidikan::getActiveEventPresensi($tanggal);
        if ($eventPresensi) {
            $sesiList = $eventPresensi->sesi_kegiatan ?? ($eventPresensi->tipe_presensi === 'harian' ? ['Harian'] : ['Siang', 'Malam']);
            if (empty($sesiList)) {
                $sesiList = ['Harian'];
            }

            $eventUstadzData = [];
            foreach ($sesiList as $idx => $namaSesi) {
                $presUstadz = PresensiKegiatanUstadz::where('kalendar_pendidikan_id', $eventPresensi->id)
                    ->where('tanggal', $tanggal)
                    ->where('sesi', $namaSesi)
                    ->where('ustadz_id', $ustadzId)
                    ->first();

                $jamText = match ($namaSesi) {
                    'Siang'  => '13:30 - 17:00 WIB',
                    'Malam'  => '19:30 - 23:00 WIB',
                    'Pagi'   => '08:00 - 11:30 WIB',
                    'Harian' => 'Hari Efektif Kegiatan',
                    default  => 'Sesi ' . $namaSesi,
                };

                $eventUstadzData[] = [
                    'id'                     => ($idx + 1) * 1000 + $eventPresensi->id,
                    'kalendar_pendidikan_id' => $eventPresensi->id,
                    'sesi'                   => $namaSesi,
                    'jam'                    => $jamText,
                    'pelajaran'              => $eventPresensi->nama_kegiatan . ' (' . $namaSesi . ')',
                    'nama_kegiatan'          => $eventPresensi->nama_kegiatan,
                    'kelas'                  => $ruanganWaliNama ?: 'Semua Ruangan',
                    'guru'                   => $ustadz->nama_lengkap ?? 'Ustadz',
                    'is_milik_wali'          => false,
                    'is_pengampu_pribadi'    => true,
                    'is_event'               => true,
                    'status_kehadiran'       => $presUstadz?->status,
                    'keterangan'             => $presUstadz?->keterangan,
                    'waktu_checkin'          => $presUstadz?->waktu_checkin?->format('H:i') . ' WIB',
                    'sudah_checkin'          => $presUstadz != null,
                    'diinput_oleh'           => $presUstadz?->diinputOleh?->name,
                ];
            }

            return response()->json([
                'success'          => true,
                'is_libur'         => false,
                'keterangan_libur' => null,
                'is_event'         => true,
                'event_info'       => [
                    'id'              => $eventPresensi->id,
                    'nama_kegiatan'   => $eventPresensi->nama_kegiatan,
                    'kategori'        => $eventPresensi->kategoriKegiatan?->nama_kategori ?? 'Kegiatan',
                    'tipe_presensi'   => $eventPresensi->tipe_presensi,
                    'sesi_list'       => $sesiList,
                    'tanggal_mulai'   => $eventPresensi->tanggal_mulai->format('Y-m-d'),
                    'tanggal_selesai' => $eventPresensi->tanggal_selesai->format('Y-m-d'),
                ],
                'ruangan_wali'     => $ruanganWaliNama ?: null,
                'data'             => $eventUstadzData
            ], 200);
        }

        // 5. Cek apakah tanggal bertepatan dengan masa / jadwal Ujian Madrasah
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
            ->get();

        $data = $jadwals->map(function ($j) use ($presensiTersimpan, $ruanganWaliIds, $ustadzId, $tanggal) {
            $checkSesi = HariLibur::checkBebasKbm($tanggal, $j->jam_ke, $j->ruangan_id, $j->ruangan?->level_id);
            $isBebasKbm = $checkSesi['is_libur'];
            $keteranganBebasKbm = $checkSesi['keterangan'];

            $isPengampuJadwal = $j->daftar_ustadz->contains('id', $ustadzId);
            $isMilikWali = in_array($j->ruangan_id, $ruanganWaliIds) && !$isPengampuJadwal;

            // Jika ustadz login adalah salah satu pengampu jadwal, cari presensi miliknya sendiri
            // Jika wali ruangan melihat jadwal ustadz lain, cari presensi ustadz utama jadwal
            $existing = $isPengampuJadwal
                ? $presensiTersimpan->first(fn($p) => $p->jadwal_pelajaran_id == $j->id && $p->ustadz_id == $ustadzId)
                : $presensiTersimpan->firstWhere('jadwal_pelajaran_id', $j->id);

            $jamText = match ($j->jam_ke) {
                'Nadzoman' => '13:45 - 14:00 WIB',
                '1' => '14:00 - 14:45 WIB',
                '2' => '15:30 - 16:15 WIB',
                'Ekstra' => '20:00 - 21:00 WIB',
                default => 'Jam Ke-' . $j->jam_ke,
            };

            $daftarUstadzStatus = $j->daftar_ustadz->map(function ($u) use ($presensiTersimpan, $j) {
                $p = $presensiTersimpan->first(fn($item) => $item->jadwal_pelajaran_id == $j->id && $item->ustadz_id == $u->id);
                return [
                    'id'            => $u->id,
                    'nama'          => $u->nama_lengkap,
                    'is_utama'      => (bool) ($u->pivot->is_utama ?? false),
                    'sudah_checkin' => $p != null,
                    'status'        => $p ? $p->status : 'Belum Absen',
                ];
            })->values();

            return [
                'jadwal_id'             => $j->id,
                'jam_ke'                => $j->jam_ke,
                'jam'                   => $jamText,
                'mapel'                 => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'ruangan'               => $j->ruangan->nama_ruangan ?? '-',
                'guru_pengajar'         => $j->daftar_nama_pengampu,
                'is_milik_wali'         => $isMilikWali,
                'is_team_teaching'      => $j->daftar_ustadz->count() > 1,
                'daftar_ustadz'         => $daftarUstadzStatus,
                'sudah_checkin'         => $existing != null,
                'status'                => $isBebasKbm && !$existing ? 'Bebas KBM' : ($existing ? $existing->status : 'Belum Absen'),
                'is_bebas_kbm'          => $isBebasKbm,
                'keterangan_bebas_kbm'  => $keteranganBebasKbm,
                'ustadz_pengganti_id'   => $existing ? $existing->ustadz_pengganti_id : null,
                'ustadz_pengganti_nama' => $existing && $existing->guruPengganti ? $existing->guruPengganti->nama_lengkap : null,
                'keterangan'            => $existing ? $existing->keterangan : ($isBebasKbm ? $keteranganBebasKbm : null),
                'waktu_checkin'         => $existing && $existing->updated_at ? $existing->updated_at->format('H:i') : null,
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
            'ustadz_id' => 'nullable|exists:ustadzs,id',
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

        $jadwal = JadwalPelajaran::with(['ustadz', 'ustadzs', 'ruangan'])->findOrFail($request->jadwal_id);

        // Cek Bebas KBM / Hari Libur untuk sesi ini
        $checkBebas = HariLibur::checkBebasKbm($tanggal, $jadwal->jam_ke, $jadwal->ruangan_id, $jadwal->ruangan?->level_id);
        if ($checkBebas['is_libur']) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melakukan check-in mengajar karena sesi KBM ini bebas KBM / libur (' . $checkBebas['keterangan'] . ').'
            ], 422);
        }

        // Cek hak akses: Wali Ruangan dari kelas jadwal tersebut ATAU Guru Pengajar Pribadi / Pengampu
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
                'message' => 'Anda tidak memiliki wewenang untuk mengisi presensi jadwal ini.'
            ], 403);
        }

        // Tentukan ustadz_id yang dicatat presensinya:
        // Jika Ustadz yang login adalah pengampu jadwal ini, catat presensi diri sendiri ($ustadzId).
        // Jika Ustadz login adalah Wali Ruangan dan BUKAN pengampu jadwal ini, catat untuk ustadz yang dipilih atau ustadz utama jadwal ($jadwal->ustadz_id).
        $targetUstadzId = $isGuruPengajar ? $ustadzId : ($request->input('ustadz_id') ?? $jadwal->ustadz_id);
        if (!$jadwal->daftar_ustadz->contains('id', $targetUstadzId)) {
            $targetUstadzId = $jadwal->ustadz_id;
        }

        try {
            $presensi = PresensiUstadz::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'jadwal_pelajaran_id' => $jadwal->id,
                    'ustadz_id' => $targetUstadzId,
                ],
                [
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

    /**
     * Check-in kehadiran Ustadz pada sesi kegiatan / event khusus
     */
    public function checkinKegiatan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kalendar_pendidikan_id' => 'required|exists:kalendar_pendidikans,id',
            'tanggal'                => 'required|date',
            'sesi'                   => 'required|string',
            'status'                 => 'required|in:Hadir,Izin,Sakit,Alpha',
            'keterangan'             => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors'  => $validator->errors()
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
        $kalendarId = $request->kalendar_pendidikan_id;
        $sesi = $request->sesi;
        $status = $request->status;
        $keterangan = $request->keterangan;

        try {
            $presensi = PresensiKegiatanUstadz::updateOrCreate(
                [
                    'kalendar_pendidikan_id' => $kalendarId,
                    'tanggal'                => $tanggal,
                    'sesi'                   => $sesi,
                    'ustadz_id'              => $ustadzId,
                ],
                [
                    'status'          => $status,
                    'waktu_checkin'   => now(),
                    'keterangan'      => $keterangan,
                    'diinput_oleh_id' => $user->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in kegiatan ustadz berhasil disimpan!',
                'data'    => $presensi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan check-in ustadz: ' . $e->getMessage()
            ], 500);
        }
    }
}
