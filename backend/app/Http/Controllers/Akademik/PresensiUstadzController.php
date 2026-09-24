<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\PresensiUstadz;
use App\Models\Ruangan;
use App\Models\Ustadz;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiUstadzController extends Controller
{
    /**
     * Monitoring Progres Harian Presensi Seluruh Ustadz Pengampu
     */
    public function progresHarian(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;
        $status_filter = $request->status;

        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();

        $nama_hari_inggris = Carbon::parse($tanggal)->format('l');
        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $hari_ini = $mapHari[$nama_hari_inggris] ?? 'Senin';

        // Cek Libur
        $libur = HariLibur::where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();

        $isLibur = false;
        $keteranganLibur = null;
        if ($libur) {
            $isLibur = true;
            $keteranganLibur = $libur->keterangan;
        } elseif ($hari_ini === 'Jumat') {
            $isLibur = true;
            $keteranganLibur = 'Libur Rutin (Jumat)';
        }

        // Cek Ujian
        $ujian = Ujian::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();
        if (!$ujian) {
            $jadwalUjianAda = JadwalUjian::whereDate('tanggal_ujian', $tanggal)->first();
            if ($jadwalUjianAda) {
                $ujian = $jadwalUjianAda->ujian;
            }
        }
        $isUjian = ($ujian != null);
        $namaUjian = $ujian ? $ujian->nama_ujian : null;
        $ujianId = $ujian ? $ujian->id : null;

        // Ambil Jadwal Hari Ini
        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran', 'ruangan.level', 'ustadz', 'ustadzs'])
            ->where('hari', $hari_ini);

        if ($ruangan_id) {
            $jadwalQuery->where('ruangan_id', $ruangan_id);
        } else {
            $jadwalQuery->whereIn('ruangan_id', $ruangans->pluck('id'));
        }

        $jadwals = $jadwalQuery->get()->sortBy([
            fn($a, $b) => ($a->ruangan?->level?->urutan_level ?? 99) <=> ($b->ruangan?->level?->urutan_level ?? 99),
            fn($a, $b) => strnatcasecmp($a->ruangan?->nama_ruangan ?? '', $b->ruangan?->nama_ruangan ?? ''),
            fn($a, $b) => (match ($a->jam_ke) {
                'Nadzoman' => 1,
                '1' => 2,
                '2' => 3,
                'Ekstra' => 4,
                default => 5
            }) <=> (match ($b->jam_ke) {
                'Nadzoman' => 1,
                '1' => 2,
                '2' => 3,
                'Ekstra' => 4,
                default => 5
            }),
        ])->values();

        // Ambil Data Presensi Ustadz pada tanggal ini
        $presensiUstadzDb = PresensiUstadz::with(['ustadz', 'guruPengganti', 'penginput'])
            ->where('tanggal', $tanggal)
            ->whereIn('jadwal_pelajaran_id', $jadwals->pluck('id'))
            ->get();

        $presensiMap = [];
        foreach ($presensiUstadzDb as $p) {
            $presensiMap[$p->jadwal_pelajaran_id . '_' . $p->ustadz_id] = $p;
        }

        $totalPengampu = 0;
        $totalHadir = 0;
        $totalSakit = 0;
        $totalIzin = 0;
        $totalAlpha = 0;
        $totalKosong = 0;
        $totalBadal = 0;
        $totalBelum = 0;

        $detailProgres = [];

        foreach ($jadwals as $j) {
            foreach ($j->daftar_ustadz as $u) {
                $p = $presensiMap[$j->id . '_' . $u->id] ?? null;
                $isUtama = ($u->pivot->is_utama ?? false) || ($u->id == $j->ustadz_id);
                $status = $p ? $p->status : 'Belum Absen';

                $totalPengampu++;

                if ($p) {
                    if ($p->status === 'Hadir') $totalHadir++;
                    elseif ($p->status === 'Sakit') $totalSakit++;
                    elseif ($p->status === 'Izin') $totalIzin++;
                    elseif ($p->status === 'Alpha') $totalAlpha++;
                    elseif ($p->status === 'Kosong') $totalKosong++;

                    if ($p->ustadz_pengganti_id && $p->guruPengganti) {
                        $totalBadal++;
                    }
                } else {
                    $totalBelum++;
                }

                // Filter status jika dipilih
                if ($status_filter) {
                    if ($status_filter === 'Belum' && $p !== null) continue;
                    if ($status_filter === 'Badal' && (!$p || !$p->ustadz_pengganti_id)) continue;
                    if (in_array($status_filter, ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Kosong']) && ($status !== $status_filter)) continue;
                }

                $detailProgres[] = [
                    'jadwal' => $j,
                    'ustadz' => $u,
                    'is_utama' => $isUtama,
                    'presensi' => $p,
                    'status' => $status,
                    'guru_pengganti' => $p?->guruPengganti,
                    'keterangan' => $p?->keterangan,
                    'diinput_oleh' => $p?->penginput?->name,
                    'waktu_input' => $p?->updated_at ? Carbon::parse($p->updated_at)->format('H:i') : null,
                ];
            }
        }

        $persenHadir = $totalPengampu > 0 ? round((($totalHadir + $totalBadal) / $totalPengampu) * 100, 1) : 0;
        $semuaGuru = Ustadz::orderBy('nama_lengkap')->where('is_active', true)->get();

        return view('presensi-ustadz.progres', compact(
            'ruangans',
            'tanggal',
            'ruangan_id',
            'status_filter',
            'hari_ini',
            'isLibur',
            'keteranganLibur',
            'isUjian',
            'namaUjian',
            'ujianId',
            'totalPengampu',
            'totalHadir',
            'totalSakit',
            'totalIzin',
            'totalAlpha',
            'totalKosong',
            'totalBadal',
            'totalBelum',
            'persenHadir',
            'detailProgres',
            'semuaGuru'
        ));
    }
    public function index(Request $request)
    {
        // 1. Tangkap parameter atau gunakan default hari ini
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $ruangan_id = $request->ruangan_id;

        // 2. Siapkan data master untuk dropdown
        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $semuaGuru = Ustadz::orderBy('nama_lengkap')->where('is_active', true)->get(); // Untuk daftar guru pengganti

        $jadwals = collect();
        $riwayatPresensi = collect();

        // Variabel penanda libur
        $isLibur = false;
        $keteranganLibur = null;

        // 3. Jika ruangan dan tanggal sudah dipilih, eksekusi pencarian
        if ($tanggal && $ruangan_id) {

            // LOGIKA HARI: Terjemahkan ke Bahasa Indonesia
            $nama_hari_inggris = \Carbon\Carbon::parse($tanggal)->format('l');
            $mapHari = [
                'Sunday' => 'Ahad',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $hariIndo = $mapHari[$nama_hari_inggris];

            // ==========================================================
            // CEK HARI LIBUR & JUMAT
            // ==========================================================
            $libur = HariLibur::where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->first();

            if ($libur) {
                // Jika masuk rentang kalender libur madrasah
                $isLibur = true;
                $keteranganLibur = $libur->keterangan;
            } elseif ($hariIndo === 'Jumat') {
                // Jika hari Jumat (Libur rutin madrasah)
                $isLibur = true;
                $keteranganLibur = 'Libur Rutin (Jumat)';
            }
            // ==========================================================

            // ==========================================================
            // CEK TANGGAL UJIAN MADRASAH
            // ==========================================================
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
            // ==========================================================

            // Jika TIDAK LIBUR, baru kita cari jadwal dan riwayat presensinya
            if (!$isLibur) {
                // Ambil jadwal pelajaran khusus ruangan dan hari tersebut
                $jadwals = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ustadzs'])
                    ->where('ruangan_id', $ruangan_id)
                    ->where('hari', $hariIndo)
                    ->orderBy('jam_ke')
                    ->get();

                // Jika ada jadwal, cari riwayat presensi yang sudah diinput
                if ($jadwals->isNotEmpty()) {
                    $riwayatPresensi = PresensiUstadz::with(['ustadz', 'guruPengganti', 'penginput'])
                        ->where('tanggal', $tanggal)
                        ->whereIn('jadwal_pelajaran_id', $jadwals->pluck('id'))
                        ->get()
                        ->keyBy(function ($p) {
                            return $p->jadwal_pelajaran_id . '_' . $p->ustadz_id;
                        });
                }
            }
        } else {
            // Cek ujian jika ruangan belum dipilih tapi tanggal sudah ada
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
        }

        return view('presensi-ustadz.harian', compact(
            'tanggal',
            'ruangan_id',
            'ruangans',
            'semuaGuru',
            'jadwals',
            'riwayatPresensi',
            'isLibur',
            'keteranganLibur',
            'isUjian',
            'namaUjian',
            'ujianId'
        ));
    }


    /**
     * Modal Form AJAX Presensi Ustadz Cepat
     */
    public function modalInput(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $jadwal_id = $request->jadwal_id;
        $ustadz_id = $request->ustadz_id;

        $jadwal = JadwalPelajaran::with(['mataPelajaran', 'ruangan.level', 'ustadz', 'ustadzs'])->findOrFail($jadwal_id);
        $ustadz = Ustadz::findOrFail($ustadz_id);

        $presensi = PresensiUstadz::with('guruPengganti')
            ->where('tanggal', $tanggal)
            ->where('jadwal_pelajaran_id', $jadwal->id)
            ->where('ustadz_id', $ustadz->id)
            ->first();

        $isUtama = ($jadwal->ustadz_id == $ustadz->id) || ($jadwal->ustadzs()->where('ustadz_id', $ustadz->id)->wherePivot('is_utama', true)->exists());

        $semuaGuru = Ustadz::where('is_active', true)->where('id', '!=', $ustadz->id)->orderBy('nama_lengkap')->get();

        return view('presensi-ustadz.modal_input', compact(
            'jadwal',
            'ustadz',
            'tanggal',
            'presensi',
            'isUtama',
            'semuaGuru'
        ));
    }

    public function storeHarian(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'ruangan_id' => 'required|exists:ruangans,id',
            'presensi' => 'required|array'
        ]);

        $tanggal = $request->tanggal;

        // Looping semua data presensi yang dikirim dari tabel form
        foreach ($request->presensi as $jadwal_id => $ustadzDataList) {
            // Support format multi-ustadz [jadwal_id][ustadz_id] maupun flat array [jadwal_id]
            if (isset($ustadzDataList['status'])) {
                $ustadzDataList = [($ustadzDataList['ustadz_id'] ?? null) => $ustadzDataList];
            }

            foreach ($ustadzDataList as $ustadz_id => $data) {
                $finalUstadzId = !empty($ustadz_id) ? $ustadz_id : ($data['ustadz_id'] ?? null);

                if (!empty($data['status']) && !empty($finalUstadzId)) {
                    PresensiUstadz::updateOrCreate(
                        [
                            'tanggal' => $tanggal,
                            'jadwal_pelajaran_id' => $jadwal_id,
                            'ustadz_id' => $finalUstadzId,
                        ],
                        [
                            'status' => $data['status'],
                            'ustadz_pengganti_id' => in_array($data['status'], ['Izin', 'Sakit', 'Alpha']) ? ($data['ustadz_pengganti_id'] ?? null) : null,
                            'keterangan' => $data['keterangan'] ?? null,
                            'diinput_oleh_id' => Auth::id(),
                        ]
                    );
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data presensi guru berhasil disimpan!'
            ], 200);
        }

        return back()->with('success', 'Data presensi guru berhasil disimpan!');
    }

    public function destroyHarian($id)
    {
        $presensi = PresensiUstadz::findOrFail($id);

        // Hapus data dari database
        $presensi->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data presensi berhasil dihapus (dibatalkan)!'
            ], 200);
        }

        return back()->with('success', 'Data presensi berhasil dihapus (dibatalkan)!');
    }


    public function bulanan(Request $request)
    {
        $ruangans = Ruangan::with('level')->berdasarkanHakAkses()->orderBy('level_id')->orderBy('nama_ruangan')->get();
        $bulans = BulanHijriyah::orderBy('urutan')->get();
        $jamList = ['Nadzoman', '1', '2', 'Ekstra'];

        $semuaGuru = Ustadz::orderBy('nama_lengkap')->get();

        $bulan_id = $request->bulan_id;
        $ruangan_id = $request->ruangan_id;

        $dates = [];
        $matrix = [];
        $bulanTerpilih = null;

        if ($bulan_id && $ruangan_id) {
            $bulanTerpilih = BulanHijriyah::findOrFail($bulan_id);

            $start = Carbon::parse($bulanTerpilih->tanggal_mulai_masehi);
            $end = Carbon::parse($bulanTerpilih->tanggal_selesai_masehi);
            $jumlahHari = $start->diffInDays($end) + 1;

            // Ambil semua jadwal di ruangan ini lalu kelompokkan per hari
            $jadwals = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ustadzs'])
                ->where('ruangan_id', $ruangan_id)
                ->get()
                ->groupBy('hari');

            // Ambil kalender libur
            $hariLiburs = HariLibur::where(function ($q) use ($bulanTerpilih) {
                $q->whereBetween('tanggal_mulai', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                    ->orWhereBetween('tanggal_selesai', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                    ->orWhere(function ($sub) use ($bulanTerpilih) {
                        $sub->where('tanggal_mulai', '<=', $bulanTerpilih->tanggal_mulai_masehi)
                            ->where('tanggal_selesai', '>=', $bulanTerpilih->tanggal_selesai_masehi);
                    });
            })->get();

            // Ambil data Ujian pada bulan ini
            $ujians = \App\Models\Ujian\Ujian::where(function ($q) use ($bulanTerpilih) {
                $q->whereBetween('tanggal_mulai', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                    ->orWhereBetween('tanggal_selesai', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                    ->orWhere(function ($sub) use ($bulanTerpilih) {
                        $sub->where('tanggal_mulai', '<=', $bulanTerpilih->tanggal_mulai_masehi)
                            ->where('tanggal_selesai', '>=', $bulanTerpilih->tanggal_selesai_masehi);
                    });
            })->get();

            $jadwalUjians = \App\Models\Ujian\JadwalUjian::with('ujian')
                ->whereBetween('tanggal_ujian', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                ->get();

            // Ambil presensi guru bulan ini (Riwayat inputan)
            $presensiDb = PresensiUstadz::with(['ustadz', 'guruPengganti'])
                ->whereBetween('tanggal', [$bulanTerpilih->tanggal_mulai_masehi, $bulanTerpilih->tanggal_selesai_masehi])
                ->whereIn('jadwal_pelajaran_id', $jadwals->flatten()->pluck('id'))
                ->get();

            // Format data presensi agar mudah dicari di tabel: [$tanggal][$jadwal_id][$ustadz_id]
            $presensiFormatted = [];
            foreach ($presensiDb as $p) {
                $presensiFormatted[$p->tanggal][$p->jadwal_pelajaran_id][$p->ustadz_id] = $p;
            }

            $mapHari = [
                'Sunday' => 'Ahad',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];

            // RAKIT MATRIKS VERTIKAL
            for ($i = 0; $i < $jumlahHari; $i++) {
                $currentDate = $start->copy()->addDays($i);
                $nama_hari_inggris = $currentDate->format('l');
                $hariIndo = $mapHari[$nama_hari_inggris];
                $tglMasehi = $currentDate->format('Y-m-d');

                // Cek Libur
                $isLibur = false;
                $keteranganLibur = null;

                if ($hariIndo === 'Jumat') {
                    $isLibur = true;
                    $keteranganLibur = 'Libur Rutin (Jumat)';
                } else {
                    foreach ($hariLiburs as $libur) {
                        $liburMulaiStr = Carbon::parse($libur->tanggal_mulai)->format('Y-m-d');
                        $liburSelesaiStr = Carbon::parse($libur->tanggal_selesai)->format('Y-m-d');
                        if ($tglMasehi >= $liburMulaiStr && $tglMasehi <= $liburSelesaiStr) {
                            $isLibur = true;
                            $keteranganLibur = $libur->keterangan;
                            break;
                        }
                    }
                }

                // Cek Ujian
                $isUjian = false;
                $namaUjian = null;
                $ujianId = null;

                if (!$isLibur) {
                    foreach ($ujians as $u) {
                        $uMulai = Carbon::parse($u->tanggal_mulai)->format('Y-m-d');
                        $uSelesai = Carbon::parse($u->tanggal_selesai)->format('Y-m-d');
                        if ($tglMasehi >= $uMulai && $tglMasehi <= $uSelesai) {
                            $isUjian = true;
                            $namaUjian = $u->nama_ujian;
                            $ujianId = $u->id;
                            break;
                        }
                    }

                    if (!$isUjian) {
                        $jUjian = $jadwalUjians->firstWhere('tanggal_ujian', $tglMasehi);
                        if ($jUjian) {
                            $isUjian = true;
                            $namaUjian = $jUjian->ujian->nama_ujian ?? 'Ujian Madrasah';
                            $ujianId = $jUjian->ujian_id;
                        }
                    }
                }

                $dates[$tglMasehi] = [
                    'hari' => $hariIndo,
                    'is_libur' => $isLibur,
                    'keterangan_libur' => $keteranganLibur,
                    'is_ujian' => $isUjian,
                    'nama_ujian' => $namaUjian,
                    'ujian_id' => $ujianId,
                ];

                // Susun matriks per jam jika tidak libur & tidak ujian
                if (!$isLibur && !$isUjian) {
                    $jadwalHariIni = $jadwals->get($hariIndo);

                    foreach ($jamList as $jam) {
                        $jadwalJamIni = $jadwalHariIni ? $jadwalHariIni->firstWhere('jam_ke', $jam) : null;

                        if ($jadwalJamIni) {
                            $allPengampu = $jadwalJamIni->daftar_ustadz;
                            $ustadzPresensiList = [];
                            foreach ($allPengampu as $u) {
                                $p = $presensiFormatted[$tglMasehi][$jadwalJamIni->id][$u->id] ?? null;
                                $isUtama = ($u->pivot->is_utama ?? false) || ($u->id == $jadwalJamIni->ustadz_id);
                                $ustadzPresensiList[] = [
                                    'ustadz_id' => $u->id,
                                    'nama_lengkap' => $u->nama_lengkap,
                                    'is_utama' => (bool) $isUtama,
                                    'presensi' => $p,
                                ];
                            }

                            $primaryPresensi = $presensiFormatted[$tglMasehi][$jadwalJamIni->id][$jadwalJamIni->ustadz_id]
                                ?? (!empty($ustadzPresensiList[0]['presensi']) ? $ustadzPresensiList[0]['presensi'] : null);

                            $matrix[$tglMasehi][$jam] = [
                                'is_jadwal' => true,
                                'jadwal_id' => $jadwalJamIni->id,
                                'ustadz_id' => $jadwalJamIni->ustadz_id,
                                'mapel' => $jadwalJamIni->mataPelajaran->nama_mapel,
                                'guru_utama' => $jadwalJamIni->ustadz->nama_lengkap ?? ($allPengampu->first()?->nama_lengkap ?? '-'),
                                'presensi' => $primaryPresensi,
                                'daftar_pengampu' => $ustadzPresensiList,
                                'is_team' => count($ustadzPresensiList) > 1,
                            ];
                        } else {
                            $matrix[$tglMasehi][$jam] = ['is_jadwal' => false];
                        }
                    }
                }
            }
        }

        return view('presensi-ustadz.bulanan', compact('ruangans', 'bulans', 'jamList', 'bulan_id', 'ruangan_id', 'dates', 'matrix', 'bulanTerpilih', 'semuaGuru'));
    }


    public function storeBulanan(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jadwal_pelajaran_id' => 'required',
            'ustadz_id' => 'required',
            'status' => 'required'
        ]);

        PresensiUstadz::updateOrCreate(
            [
                'tanggal' => $request->tanggal,
                'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
                'ustadz_id' => $request->ustadz_id,
            ],
            [
                'status' => $request->status,
                'ustadz_pengganti_id' => in_array($request->status, ['Izin', 'Sakit', 'Alpha']) ? $request->ustadz_pengganti_id : null,
                'keterangan' => $request->keterangan,
                'diinput_oleh_id' => Auth::id(),
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data presensi ustadz berhasil disimpan!'
            ], 200);
        }

        return back()->with('success', 'Data presensi berhasil diperbarui langsung dari matriks bulanan!');
    }


    public function rekapSemua(Request $request)
    {
        $bulans = BulanHijriyah::orderBy('urutan')->get();
        $bulan_id = $request->bulan_id;
        $bulanTerpilih = null;

        // Ambil data Master Ruangan untuk dijadikan Kolom Tabel
        $ruangans = Ruangan::orderBy('level_id')->orderBy('nama_ruangan')->get();
        $rekap = [];

        if ($bulan_id) {
            $bulanTerpilih = BulanHijriyah::findOrFail($bulan_id);
            $start = $bulanTerpilih->tanggal_mulai_masehi;
            $end = $bulanTerpilih->tanggal_selesai_masehi;

            // 1. Siapkan Wadah Array (Baris = Guru, Kolom = Ruangan)
            $asatidzs = Ustadz::orderBy('nama_lengkap')->get();
            foreach ($asatidzs as $guru) {
                $rekap[$guru->id] = [
                    'nama' => $guru->nama_lengkap,
                    'ruangan' => [],
                    'total' => 0
                ];
                // Buat nilai awal 0 untuk setiap ruangan
                foreach ($ruangans as $ruangan) {
                    $rekap[$guru->id]['ruangan'][$ruangan->id] = 0;
                }
            }

            // 2. Ambil data presensi berserta relasi ke jadwal (untuk mengetahui ruangan)
            $presensis = PresensiUstadz::with('jadwalPelajaran')
                ->whereBetween('tanggal', [$start, $end])
                ->get();

            // 3. Masukkan jumlah kehadiran ke ruangan yang tepat
            foreach ($presensis as $p) {
                // Pastikan jadwalnya valid
                if ($p->jadwalPelajaran) {
                    $ruangan_id = $p->jadwalPelajaran->ruangan_id;

                    // Jika Guru Utama HADIR
                    if ($p->status === 'Hadir' && isset($rekap[$p->ustadz_id])) {
                        $rekap[$p->ustadz_id]['ruangan'][$ruangan_id]++;
                        $rekap[$p->ustadz_id]['total']++;
                    }

                    // Jika digantikan, maka yang dihitung "Hadir" adalah Guru Pengganti (Badal)
                    if (in_array($p->status, ['Sakit', 'Izin', 'Alpha']) && $p->ustadz_pengganti_id && isset($rekap[$p->ustadz_pengganti_id])) {
                        $rekap[$p->ustadz_pengganti_id]['ruangan'][$ruangan_id]++;
                        $rekap[$p->ustadz_pengganti_id]['total']++;
                    }
                }
            }
        }

        return view('presensi-ustadz.rekap_semua', compact('bulans', 'bulan_id', 'bulanTerpilih', 'rekap', 'ruangans'));
    }


    public function cetakRekap(Request $request)
    {
        $bulan_id = $request->bulan_id;

        if (!$bulan_id) {
            return back()->with('error', 'Silakan pilih bulan terlebih dahulu.');
        }

        $bulanTerpilih = BulanHijriyah::with('semester.tahunPelajaran')->findOrFail($bulan_id);
        $start = $bulanTerpilih->tanggal_mulai_masehi;
        $end = $bulanTerpilih->tanggal_selesai_masehi;
        $ruangans = Ruangan::orderBy('level_id')->orderBy('nama_ruangan')->get();

        // Ambil data ustadz
        $semuaGuru = Ustadz::orderBy('nama_lengkap', 'asc')->get();

        // 1. Siapkan struktur array dasar (Semuanya bernilai 0 di awal)
        $rekap = [];
        foreach ($semuaGuru as $guru) {
            $rekap[$guru->id] = [
                'nama' => $guru->nama_lengkap,
                'ruangan' => [],
                'total' => 0
            ];

            foreach ($ruangans as $ruangan) {
                $rekap[$guru->id]['ruangan'][$ruangan->id] = 0;
            }
        }

        // 2. Ambil seluruh presensi dalam bulan tersebut
        $presensi = PresensiUstadz::with('jadwalPelajaran')
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->get();

        // ================================================================
        // 3. PERHITUNGAN BARU (Super Cepat) MENGGUNAKAN LOGIKA ANDA
        // ================================================================
        foreach ($presensi as $p) {
            if ($p->jadwalPelajaran) {
                $ruangan_id = $p->jadwalPelajaran->ruangan_id;
                $idUtama = $p->ustadz_id;
                $idPengganti = $p->ustadz_pengganti_id;

                // A. Tambah poin untuk Guru Utama yang HADIR
                if ($p->status === 'Hadir' && isset($rekap[$idUtama])) {
                    if (isset($rekap[$idUtama]['ruangan'][$ruangan_id])) {
                        $rekap[$idUtama]['ruangan'][$ruangan_id]++;
                    }
                    $rekap[$idUtama]['total']++;
                }

                // B. Tambah poin untuk Guru Pengganti / Badal (Jika Utama tidak hadir)
                if (in_array($p->status, ['Sakit', 'Izin', 'Alpha']) && $idPengganti && isset($rekap[$idPengganti])) {
                    if (isset($rekap[$idPengganti]['ruangan'][$ruangan_id])) {
                        $rekap[$idPengganti]['ruangan'][$ruangan_id]++;
                    }
                    $rekap[$idPengganti]['total']++;
                }
            }
        }

        return view('cetak-baru.cetak_rekap_presensi_ustadz', compact('bulanTerpilih', 'ruangans', 'rekap'));
    }

    public function exportExcel(Request $request)
    {
        $bulan_id = $request->bulan_id;
        if (!$bulan_id) return back()->with('error', 'Pilih bulan terlebih dahulu!');

        $bulanTerpilih = BulanHijriyah::findOrFail($bulan_id);
        $start = $bulanTerpilih->tanggal_mulai_masehi;
        $end = $bulanTerpilih->tanggal_selesai_masehi;

        $ruangans = Ruangan::orderBy('level_id')->orderBy('nama_ruangan')->get();
        $rekap = [];

        $asatidzs = Ustadz::orderBy('nama_lengkap')->get();
        foreach ($asatidzs as $guru) {
            $rekap[$guru->id] = ['nama' => $guru->nama_lengkap, 'ruangan' => [], 'total' => 0];
            foreach ($ruangans as $ruangan) {
                $rekap[$guru->id]['ruangan'][$ruangan->id] = 0;
            }
        }

        $presensis = PresensiUstadz::with('jadwalPelajaran')->whereBetween('tanggal', [$start, $end])->get();
        foreach ($presensis as $p) {
            if ($p->jadwalPelajaran) {
                $ruangan_id = $p->jadwalPelajaran->ruangan_id;
                if ($p->status === 'Hadir' && isset($rekap[$p->ustadz_id])) {
                    $rekap[$p->ustadz_id]['ruangan'][$ruangan_id]++;
                    $rekap[$p->ustadz_id]['total']++;
                }
                if (in_array($p->status, ['Sakit', 'Izin', 'Alpha']) && $p->ustadz_pengganti_id && isset($rekap[$p->ustadz_pengganti_id])) {
                    $rekap[$p->ustadz_pengganti_id]['ruangan'][$ruangan_id]++;
                    $rekap[$p->ustadz_pengganti_id]['total']++;
                }
            }
        }

        // ==========================================
        // PROSES PEMBUATAN EXCEL (CSV)
        // ==========================================
        $fileName = "Rekap_Kehadiran_Per_Ruangan_" . str_replace(' ', '_', $bulanTerpilih->nama_bulan) . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($rekap, $ruangans) {
            $file = fopen('php://output', 'w');

            // Tulis Header Kolom
            $headerRow = ['No', 'Nama Guru'];
            foreach ($ruangans as $r) {
                $headerRow[] = $r->nama_ruangan;
            }
            $headerRow[] = 'Total Hadir';
            fputcsv($file, $headerRow);

            // Tulis Data Baris per Baris
            $no = 1;
            foreach ($rekap as $row) {
                $dataRow = [$no++, $row['nama']];
                foreach ($ruangans as $r) {
                    $dataRow[] = $row['ruangan'][$r->id];
                }
                $dataRow[] = $row['total'];
                fputcsv($file, $dataRow);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
