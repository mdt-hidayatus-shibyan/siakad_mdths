<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulanHijriyah;
use App\Models\KasRuangan\PembayaranKasRuangan;
use App\Models\KasRuangan\PengaturanKasRuangan;
use App\Models\KasRuangan\SetoranKasRuangan;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\PresensiMurid;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Models\Tabungan\Tabungan;
use App\Models\Tabungan\TabunganKomplain;
use App\Models\Tabungan\TransaksiTabungan;
use App\Models\Ujian\NilaiUjian;
use App\Models\WaliMurid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WaliMuridApiController extends Controller
{
    /**
     * Dapatkan data Wali Murid berdasarkan user session atau parameter (untuk Admin)
     */
    private function resolveWali(Request $request): ?WaliMurid
    {
        $user = $request->user();

        // 1. Akun Wali Murid yang sedang login via token
        if (str_starts_with($user->username ?? '', 'wali_')) {
            $noReg = substr($user->username, 5);
            $wali = WaliMurid::where('no_registrasi', $noReg)->first();
            if ($wali) return $wali;
        }

        // 2. Jika user adalah administrator / staff
        if ($user && $user->hasAnyRole(['administrator', 'staff'])) {
            if ($request->filled('wali_id')) {
                return WaliMurid::find($request->wali_id);
            }
            return WaliMurid::where('is_active', true)->first();
        }

        return null;
    }

    /**
     * Helper validasi otorisasi kepemilikan murid oleh Wali Murid yang sedang login (Pencegahan IDOR)
     */
    private function authorizeMuridForWali(Request $request, $muridId, array $with = []): Murid
    {
        $wali = $this->resolveWali($request);

        if (!$wali) {
            abort(response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Profil wali murid tidak ditemukan.'
            ], 403));
        }

        $query = Murid::where('id', $muridId);

        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['administrator', 'staff'])) {
            $query->where('wali_murid_id', $wali->id);
        }

        if (!empty($with)) {
            $query->with($with);
        }

        $murid = $query->first();

        if (!$murid) {
            abort(response()->json([
                'success' => false,
                'message' => 'Data santri/murid tidak ditemukan atau Anda tidak memiliki akses ke data murid ini.'
            ], 403));
        }

        return $murid;
    }

    /**
     * Dashboard Aplikasi Wali Murid (app_murid)
     */
    public function getDashboard(Request $request)
    {
        $wali = $this->resolveWali($request);

        if (!$wali) {
            return response()->json([
                'success' => false,
                'message' => 'Data Wali Murid tidak ditemukan.'
            ], 404);
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        // Ambil daftar anak aktif
        $anakList = Murid::with([
            'ruangans' => function ($q) use ($tahunId) {
                if ($tahunId) {
                    $q->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
                }
            },
            'levelMasuk',
            'ruanganMasuk'
        ])
            ->where('wali_murid_id', $wali->id)
            ->where('status', 'Aktif')
            ->get();

        $anakIds = $anakList->pluck('id')->toArray();

        // Ringkasan Tagihan Semua Anak
        $tagihanQuery = TagihanMurid::whereIn('murid_id', $anakIds);
        if ($tahunId) {
            $tagihanQuery->whereHas('ruangan', fn($q) => $q->where('tahun_pelajaran_id', $tahunId));
        }
        $totalTagihanAnak = (clone $tagihanQuery)->sum('nominal_tagihan');
        $totalLunasAnak = (clone $tagihanQuery)->where('status_bayar', 'Lunas')->sum('nominal_tagihan');

        // Ringkasan Tagihan Per Wali Murid (KK)
        $tagihanWaliList = \App\Models\TagihanWaliMurid::with(['pengaturanTagihan', 'pembayaranTagihan'])
            ->where('wali_murid_id', $wali->id)
            ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->get();

        $totalTagihanWali = $tagihanWaliList->sum('nominal_tagihan');
        $totalLunasWali = $tagihanWaliList->where('status_bayar', 'Lunas')->sum('nominal_tagihan');

        $totalTagihan = $totalTagihanAnak + $totalTagihanWali;
        $totalLunas = $totalLunasAnak + $totalLunasWali;
        $totalTunggakan = max(0, $totalTagihan - $totalLunas);

        // Data Ringkas per Anak
        $dataAnak = $anakList->map(function ($anak) {
            // Presensi hari ini
            $presensiHariIni = PresensiMurid::where('murid_id', $anak->id)
                ->whereDate('tanggal', date('Y-m-d'))
                ->orderBy('id', 'desc')
                ->first();

            return [
                'id'             => $anak->id,
                'nism'           => $anak->nism,
                'nisn'           => $anak->nisn,
                'nik'            => $anak->nik,
                'nama_lengkap'   => $anak->nama_lengkap,
                'nama_panggilan' => $anak->nama_panggilan,
                'jenis_kelamin'  => $anak->jenis_kelamin,
                'foto'           => $anak->foto_url,
                'ruangan'        => $anak->nama_ruangan_aktif,
                'status_hari_ini' => $presensiHariIni->status ?? 'Belum Ada Sesi',
            ];
        });

        $dataTagihanWali = $tagihanWaliList->map(function ($tw) {
            return [
                'id'            => $tw->id,
                'nama_tagihan'  => $tw->nama_tagihan_spesifik,
                'nominal'       => (int) $tw->nominal_tagihan,
                'status_bayar'  => $tw->status_bayar,
                'tanggal_bayar' => $tw->pembayaranTagihan ? Carbon::parse($tw->pembayaranTagihan->tanggal_bayar)->format('d-m-Y') : null,
                'no_transaksi'  => $tw->pembayaranTagihan->no_transaksi ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'wali' => [
                    'id'                   => $wali->id,
                    'nama_kepala_keluarga' => $wali->nama_kepala_keluarga,
                    'no_registrasi'        => $wali->no_registrasi,
                    'no_kk'                => $wali->no_kk,
                    'alamat'               => $wali->alamat_detail,
                    'kampung'              => $wali->kampung->nama_kampung ?? '-',
                ],
                'tahun_pelajaran' => [
                    'nama_hijriyah' => $tahunAktif->nama_hijriyah ?? '-',
                    'nama_masehi'   => $tahunAktif->nama_masehi ?? '-',
                ],
                'ringkasan_keuangan' => [
                    'total_tagihan'      => (int) $totalTagihan,
                    'total_lunas'        => (int) $totalLunas,
                    'total_tunggakan'    => (int) $totalTunggakan,
                    'total_tagihan_kk'   => (int) $totalTagihanWali,
                    'total_lunas_kk'     => (int) $totalLunasWali,
                ],
                'anak' => $dataAnak,
                'tagihan_wali' => $dataTagihanWali,
            ]
        ], 200);
    }

    /**
     * Detail Profil & Biodata Murid
     */
    public function getDetailAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id, ['waliMurid.kampung', 'ruangans', 'levelMasuk', 'tahunMasuk']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $murid->id,
                'nism'           => $murid->nism,
                'nisn'           => $murid->nisn,
                'nik'            => $murid->nik,
                'nama_lengkap'   => $murid->nama_lengkap,
                'nama_panggilan' => $murid->nama_panggilan,
                'jenis_kelamin'  => $murid->jenis_kelamin,
                'tempat_lahir'   => $murid->tempat_lahir,
                'tanggal_lahir'  => $murid->tanggal_lahir ? Carbon::parse($murid->tanggal_lahir)->format('d-m-Y') : null,
                'anak_ke'        => $murid->anak_ke,
                'hub_kel'        => $murid->hub_kel,
                'nama_ayah'      => $murid->nama_ayah,
                'status_ayah'    => $murid->status_ayah,
                'nama_ibu'       => $murid->nama_ibu,
                'status_ibu'     => $murid->status_ibu,
                'foto'           => $murid->foto_url,
                'status'         => $murid->status,
                'ruangan'        => $murid->nama_ruangan_aktif,
                'kampung'        => $murid->waliMurid->kampung->nama_kampung ?? '-',
            ]
        ], 200);
    }

    /**
     * Detail Tagihan & Riwayat Pembayaran Murid
     */
    public function getTagihanAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $tagihans = TagihanMurid::with(['pengaturanTagihan', 'bulanHijriyah', 'pembayaranTagihan', 'semester'])
            ->where('murid_id', $murid->id)
            ->when($tahunId, function ($q) use ($tahunId) {
                $q->whereHas('ruangan', fn($rq) => $rq->where('tahun_pelajaran_id', $tahunId));
            })
            ->orderBy('id', 'asc')
            ->get();

        // SPP Bulanan
        $sppList = $tagihans->where('pengaturanTagihan.tipe', 'bulanan')->values()->map(function ($t) {
            return [
                'id'            => $t->id,
                'bulan'         => $t->bulanHijriyah->nama_bulan ?? $t->nama_tagihan_spesifik,
                'nominal'       => (int) $t->nominal_tagihan,
                'status_bayar'  => $t->status_bayar,
                'tanggal_bayar' => $t->pembayaranTagihan ? Carbon::parse($t->pembayaranTagihan->tanggal_bayar)->format('d-m-Y') : null,
                'no_transaksi'  => $t->pembayaranTagihan->no_transaksi ?? null,
            ];
        });

        // Tagihan Non-SPP (Insidental / Semester)
        $nonSppList = $tagihans->where('pengaturanTagihan.tipe', '!=', 'bulanan')->values()->map(function ($t) {
            return [
                'id'            => $t->id,
                'nama_tagihan'  => $t->nama_tagihan_spesifik,
                'tipe'          => $t->pengaturanTagihan->tipe ?? 'insidental',
                'nominal'       => (int) $t->nominal_tagihan,
                'status_bayar'  => $t->status_bayar,
                'tanggal_bayar' => $t->pembayaranTagihan ? Carbon::parse($t->pembayaranTagihan->tanggal_bayar)->format('d-m-Y') : null,
                'no_transaksi'  => $t->pembayaranTagihan->no_transaksi ?? null,
            ];
        });

        $sppTagihans = $tagihans->where('pengaturanTagihan.tipe', 'bulanan');
        $nonSppTagihans = $tagihans->where('pengaturanTagihan.tipe', '!=', 'bulanan');

        $totalSpp = (int) $sppTagihans->sum('nominal_tagihan');
        $totalSppLunas = (int) $sppTagihans->where('status_bayar', 'Lunas')->sum('nominal_tagihan');

        $totalNonSpp = (int) $nonSppTagihans->sum('nominal_tagihan');
        $totalNonSppLunas = (int) $nonSppTagihans->where('status_bayar', 'Lunas')->sum('nominal_tagihan');

        $totalTagihan = $tagihans->sum('nominal_tagihan');
        $totalLunas = $tagihans->where('status_bayar', 'Lunas')->sum('nominal_tagihan');

        return response()->json([
            'success' => true,
            'data'    => [
                'murid' => [
                    'id'           => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism'         => $murid->nism,
                ],
                'summary' => [
                    'total_tagihan'           => (int) $totalTagihan,
                    'total_lunas'             => (int) $totalLunas,
                    'total_tunggakan'         => (int) max(0, $totalTagihan - $totalLunas),
                    'total_spp'               => $totalSpp,
                    'total_spp_lunas'         => $totalSppLunas,
                    'total_spp_tunggakan'     => (int) max(0, $totalSpp - $totalSppLunas),
                    'total_non_spp'           => $totalNonSpp,
                    'total_non_spp_lunas'     => $totalNonSppLunas,
                    'total_non_spp_tunggakan' => (int) max(0, $totalNonSpp - $totalNonSppLunas),
                ],
                'spp'     => $sppList,
                'non_spp' => $nonSppList,
            ]
        ], 200);
    }

    /**
     * Detail Tagihan & Riwayat Pembayaran Per Wali Murid (Kepala Keluarga / KK)
     */
    public function getTagihanWali(Request $request)
    {
        $wali = $this->resolveWali($request);

        if (!$wali) {
            return response()->json([
                'success' => false,
                'message' => 'Data Wali Murid tidak ditemukan atau sesi telah berakhir.'
            ], 403);
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $tagihanWalis = \App\Models\TagihanWaliMurid::with(['pengaturanTagihan', 'pembayaranTagihan', 'tahunPelajaran'])
            ->where('wali_murid_id', $wali->id)
            ->when($tahunId, function ($q) use ($tahunId) {
                $q->where('tahun_pelajaran_id', $tahunId);
            })
            ->orderBy('id', 'asc')
            ->get();

        $items = $tagihanWalis->map(function ($tw) {
            return [
                'id'                => $tw->id,
                'nama_tagihan'      => $tw->nama_tagihan_spesifik,
                'kategori'          => $tw->pengaturanTagihan->kategori_tagihan ?? 'Wali Murid',
                'nominal'           => (int) $tw->nominal_tagihan,
                'status_bayar'      => $tw->status_bayar,
                'tanggal_bayar'     => $tw->pembayaranTagihan ? Carbon::parse($tw->pembayaranTagihan->tanggal_bayar)->format('d-m-Y') : null,
                'no_transaksi'      => $tw->pembayaranTagihan->no_transaksi ?? null,
                'metode_pembayaran' => $tw->pembayaranTagihan->metode_pembayaran ?? null,
                'keterangan'        => $tw->keterangan ?? ($tw->pembayaranTagihan->catatan ?? null),
                'tahun_pelajaran'   => $tw->tahunPelajaran ? (($tw->tahunPelajaran->nama_hijriyah ?? '-') . ' H - ' . ($tw->tahunPelajaran->nama_masehi ?? '-') . ' M') : null,
            ];
        });

        $totalTagihan = (int) $tagihanWalis->sum('nominal_tagihan');
        $totalLunas = (int) $tagihanWalis->where('status_bayar', 'Lunas')->sum('nominal_tagihan');
        $totalTunggakan = (int) max(0, $totalTagihan - $totalLunas);

        return response()->json([
            'success' => true,
            'data'    => [
                'wali' => [
                    'id'                   => $wali->id,
                    'nama_kepala_keluarga' => $wali->nama_kepala_keluarga,
                    'no_registrasi'        => $wali->no_registrasi,
                    'no_kk'                => $wali->no_kk,
                    'no_hp'                => $wali->no_hp,
                    'kampung'              => $wali->kampung->nama_kampung ?? '-',
                ],
                'summary' => [
                    'total_tagihan'   => $totalTagihan,
                    'total_lunas'     => $totalLunas,
                    'total_tunggakan' => $totalTunggakan,
                ],
                'items' => $items,
            ]
        ], 200);
    }

    /**
     * Rekap Presensi / Kehadiran Murid
     */
    public function getPresensiAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);

        $presensiQuery = PresensiMurid::with(['jadwalPelajaran.mataPelajaran'])
            ->where('murid_id', $murid->id);

        if ($request->filled('tanggal')) {
            $tgl = Carbon::parse($request->tanggal)->format('Y-m-d');
            $presensiQuery->whereDate('tanggal', $tgl);
        }

        $presensis = $presensiQuery->orderBy('tanggal', 'desc')
            ->limit(100)
            ->get();

        $stats = [
            'hadir'      => PresensiMurid::where('murid_id', $murid->id)->where('status', 'Hadir')->count(),
            'sakit'      => PresensiMurid::where('murid_id', $murid->id)->where('status', 'Sakit')->count(),
            'izin'       => PresensiMurid::where('murid_id', $murid->id)->where('status', 'Izin')->count(),
            'alpha'      => PresensiMurid::where('murid_id', $murid->id)->where('status', 'Alpha')->count(),
            'dispensasi' => PresensiMurid::where('murid_id', $murid->id)->where('status', 'Dispensasi')->count(),
        ];

        $totalSesi = array_sum($stats);
        $persentaseHadir = $totalSesi > 0 ? round(($stats['hadir'] / $totalSesi) * 100, 1) : 0;

        $riwayat = $presensis->map(function ($p) {
            return [
                'id'        => $p->id,
                'tanggal'   => Carbon::parse($p->tanggal)->format('d-m-Y'),
                'hari'      => Carbon::parse($p->tanggal)->translatedFormat('l'),
                'mapel'     => $p->jadwalPelajaran->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'status'    => $p->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'murid' => [
                    'id'           => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism'         => $murid->nism,
                ],
                'statistik' => [
                    'total_sesi'       => $totalSesi,
                    'persentase_hadir' => $persentaseHadir,
                    'rincian'          => $stats,
                ],
                'riwayat' => $riwayat,
            ]
        ], 200);
    }

    /**
     * Catatan Pelanggaran & Poin Murid
     */
    public function getPelanggaranAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);

        $pelanggarans = PelanggaranMurid::with(['referensiPelanggaran', 'ruangan', 'penginput'])
            ->where('murid_id', $murid->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPoin = $pelanggarans->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));
        $totalRingan = $pelanggarans->filter(fn($p) => strtolower($p->referensiPelanggaran->kategori ?? '') === 'ringan')->count();
        $totalSedang = $pelanggarans->filter(fn($p) => strtolower($p->referensiPelanggaran->kategori ?? '') === 'sedang')->count();
        $totalBerat = $pelanggarans->filter(fn($p) => strtolower($p->referensiPelanggaran->kategori ?? '') === 'berat')->count();

        $riwayat = $pelanggarans->map(function ($p) {
            return [
                'id'           => $p->id,
                'tanggal'      => Carbon::parse($p->tanggal)->format('d-m-Y'),
                'hari'         => Carbon::parse($p->tanggal)->translatedFormat('l'),
                'kasus'        => $p->referensiPelanggaran->nama_pelanggaran ?? 'Pelanggaran',
                'kategori'     => $p->referensiPelanggaran->kategori ?? 'Ringan',
                'poin'         => (float)($p->referensiPelanggaran->poin ?? 0),
                'keterangan'   => $p->keterangan ?: '-',
                'ruangan_nama' => $p->ruangan->nama_ruangan ?? null,
                'diinput_oleh' => $p->penginput->name ?? 'Ustadz / Pengajar',
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'murid' => [
                    'id'           => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism'         => $murid->nism,
                ],
                'total_poin'  => (float) $totalPoin,
                'total_kasus' => $pelanggarans->count(),
                'rincian'     => [
                    'ringan' => $totalRingan,
                    'sedang' => $totalSedang,
                    'berat'  => $totalBerat,
                ],
                'riwayat'     => $riwayat,
            ]
        ], 200);
    }

    /**
     * Rapor Nilai & Hasil Ujian Murid
     */
    public function getNilaiAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $nilais = NilaiUjian::with([
            'ujian.semester',
            'jadwalUjian.mataPelajaran',
            'ruangan'
        ])
            ->where('murid_id', $murid->id)
            ->where('is_published', true)
            ->when($tahunId, function ($q) use ($tahunId) {
                $q->whereHas('ujian', fn($uq) => $uq->where('tahun_pelajaran_id', $tahunId));
            })
            ->get();

        // Group by Ujian
        $grouped = $nilais->groupBy('ujian_id')->map(function ($items) use ($murid) {
            $first = $items->first();
            $ujian = $first->ujian;
            $ruangan = $first->ruangan;

            $mapelList = $items->map(function ($n) {
                $kkm = 65;
                $angka = $n->nilai !== null ? (float) $n->nilai : 0.0;
                $huruf = $n->getPredikatHuruf();
                $namaMapel = $n->jadwalUjian?->nama_mapel ?: ($n->jadwalUjian?->nama_mata_pelajaran_custom ?: 'Mata Pelajaran');

                return [
                    'id'             => $n->id,
                    'mapel'          => $namaMapel,
                    'kkm'            => $kkm,
                    'nilai_angka'    => $angka,
                    'nilai_huruf'    => $huruf,
                    'is_lulus'       => $angka >= $kkm,
                    'catatan'        => $n->getCatatanGuru(),
                ];
            });

            $rataRata = $items->count() > 0 ? round($items->avg('nilai'), 2) : 0;
            $totalNilai = (float) $items->sum('nilai');

            return [
                'ujian_id'       => $ujian->id ?? null,
                'nama_ujian'     => $ujian->nama_ujian ?? 'Ujian Madrasah',
                'tipe_ujian'     => $ujian->tipe_ujian ?? $ujian->jenis_ujian ?? 'IMDA',
                'semester'       => $ujian->semester->nama_semester ?? 'Semester Aktif',
                'ruangan'        => $ruangan->nama_ruangan ?? ($murid->nama_ruangan_aktif ?? '-'),
                'total_mapel'    => $items->count(),
                'total_nilai'    => $totalNilai,
                'rata_rata'      => (float) $rataRata,
                'daftar_nilai'   => $mapelList,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'murid' => [
                    'id'           => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism'         => $murid->nism,
                    'ruangan'      => $murid->nama_ruangan_aktif,
                ],
                'daftar_ujian' => $grouped,
            ]
        ], 200);
    }

    /**
     * Jadwal Pelajaran Murid di Ruangan Aktif (Hari Ini, Ujian, & Mingguan)
     */
    public function getJadwalAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $ruanganAktif = $murid->ruangans()
            ->when($tahunId, fn($q) => $q->where('murid_ruangans.tahun_pelajaran_id', $tahunId))
            ->first();

        $mapHari = [
            'Sunday'    => 'Ahad',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $today = date('Y-m-d');
        $hariIni = $mapHari[Carbon::now()->format('l')];

        // Cek Libur Hari Ini
        $libur = \App\Models\HariLibur::where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
        $isLibur = ($libur != null) || ($hariIni === 'Jumat');
        $keteranganLibur = $libur ? $libur->keterangan : ($hariIni === 'Jumat' ? 'Libur Rutin Mingguan (Hari Jumat)' : null);

        if (!$ruanganAktif) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'murid' => [
                        'id'           => $murid->id,
                        'nama_lengkap' => $murid->nama_lengkap,
                        'nism'         => $murid->nism,
                    ],
                    'ruangan'             => '-',
                    'hari_ini'            => $hariIni,
                    'tanggal_hari_ini'    => Carbon::now()->format('d-m-Y'),
                    'tanggal_formatted'   => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                    'is_libur'            => $isLibur,
                    'keterangan_libur'    => $keteranganLibur,
                    'is_ujian'            => false,
                    'nama_ujian'          => null,
                    'jadwal_hari_ini'     => [],
                    'jadwal'              => [],
                    'jadwal_mingguan'     => [],
                ]
            ], 200);
        }

        // Cek Apakah Hari Ini Ada Jadwal Ujian untuk Tingkat/Level Ruangan Ini
        $isUjian = false;
        $namaUjian = null;
        $jadwalUjianHariIni = collect();

        $jadwalUjianList = \App\Models\Ujian\JadwalUjian::with(['ujian', 'mataPelajaran', 'pengawas'])
            ->whereDate('tanggal_ujian', $today)
            ->where(function ($q) use ($ruanganAktif) {
                if ($ruanganAktif->level_id) {
                    $q->where('level_id', $ruanganAktif->level_id)
                        ->orWhereNull('level_id');
                }
            })
            ->when($tahunId, function ($q) use ($tahunId) {
                $q->whereHas('ujian', fn($uq) => $uq->where('tahun_pelajaran_id', $tahunId));
            })
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        if ($jadwalUjianList->isNotEmpty()) {
            $isUjian = true;
            $namaUjian = $jadwalUjianList->first()->ujian->nama_ujian ?? 'Ujian Madrasah';
            $jadwalUjianHariIni = $jadwalUjianList->map(function ($ju) {
                return [
                    'id'       => $ju->id,
                    'hari'     => $ju->hari_tanggal_singkat,
                    'jam_ke'   => 'Ujian',
                    'waktu'    => ($ju->jam_mulai_format ?? '07:30') . ' - ' . ($ju->jam_selesai_format ?? '09:00') . ' WIB',
                    'mapel'    => $ju->nama_mapel,
                    'ustadz'   => $ju->pengawas->nama_lengkap ?? 'Ustadz Pengawas',
                    'is_ujian' => true,
                ];
            });
        }

        // Ambil Semua Jadwal Pelajaran Reguler di Ruangan
        $jadwals = \App\Models\JadwalPelajaran::with(['mataPelajaran', 'ustadz'])
            ->where('ruangan_id', $ruanganAktif->id)
            ->get();

        $hariOrder = ['Sabtu' => 1, 'Ahad' => 2, 'Senin' => 3, 'Selasa' => 4, 'Rabu' => 5, 'Kamis' => 6];

        $jadwalMingguan = $jadwals->sortBy(function ($j) use ($hariOrder) {
            return ($hariOrder[$j->hari] ?? 99) * 100 + (is_numeric($j->jam_ke) ? (int)$j->jam_ke : 10);
        })->values()->map(function ($j) {
            return [
                'id'       => $j->id,
                'hari'     => $j->hari,
                'jam_ke'   => $j->jam_ke ? "Jam Ke-{$j->jam_ke}" : 'Pelajaran',
                'waktu'    => $j->jam_mulai ? ($j->jam_mulai . ' - ' . $j->jam_selesai . ' WIB') : 'Sesuai Jadwal',
                'mapel'    => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                'ustadz'   => $j->ustadz->nama_lengkap ?? 'Ustadz Pengampu',
                'is_ujian' => false,
            ];
        });

        // Jadwal Hari Ini
        $jadwalHariIni = collect();
        if ($isUjian) {
            $jadwalHariIni = $jadwalUjianHariIni;
        } elseif (!$isLibur) {
            $jadwalHariIni = $jadwals->where('hari', $hariIni)->sortBy(function ($j) {
                return is_numeric($j->jam_ke) ? (int)$j->jam_ke : 10;
            })->values()->map(function ($j) {
                return [
                    'id'       => $j->id,
                    'hari'     => $j->hari,
                    'jam_ke'   => $j->jam_ke ? "Jam Ke-{$j->jam_ke}" : 'Pelajaran',
                    'waktu'    => $j->jam_mulai ? ($j->jam_mulai . ' - ' . $j->jam_selesai . ' WIB') : 'Sesuai Jadwal',
                    'mapel'    => $j->mataPelajaran->nama_mapel ?? 'Pelajaran',
                    'ustadz'   => $j->ustadz->nama_lengkap ?? 'Ustadz Pengampu',
                    'is_ujian' => false,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'murid' => [
                    'id'           => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism'         => $murid->nism,
                ],
                'ruangan'             => $ruanganAktif->nama_ruangan,
                'hari_ini'            => $hariIni,
                'tanggal_hari_ini'    => Carbon::now()->format('d-m-Y'),
                'tanggal_formatted'   => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                'is_libur'            => $isLibur,
                'keterangan_libur'    => $keteranganLibur,
                'is_ujian'            => $isUjian,
                'nama_ujian'          => $namaUjian,
                'jadwal_hari_ini'     => $jadwalHariIni,
                'jadwal'              => $jadwalMingguan,
                'jadwal_mingguan'     => $jadwalMingguan,
            ]
        ], 200);
    }

    /**
     * Informasi Kenaikan Kelas / Kelulusan Murid (Hasil Pleno & SK Resmi)
     */
    public function getKenaikanAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id, ['ruangans.level']);
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $ruanganAktif = $murid->ruangans()
            ->when($tahunId, fn($q) => $q->where('murid_ruangans.tahun_pelajaran_id', $tahunId))
            ->first();

        $levelNama = $ruanganAktif?->level?->nama_level ?? '';
        $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

        $riwayatKenaikan = \App\Models\Ujian\RiwayatKenaikan::with(['ruanganAsal.level', 'levelTujuan', 'murid'])
            ->where('murid_id', $murid->id)
            ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->orderBy('id', 'desc')
            ->first();

        if ($riwayatKenaikan) {
            $pengesah = \App\Models\User::find($riwayatKenaikan->diputuskan_oleh);
            $namaPengesah = $pengesah ? $pengesah->name : 'Pimpinan / Kepala Madrasah';

            return response()->json([
                'success' => true,
                'data' => [
                    'murid' => [
                        'id'             => $murid->id,
                        'nama_lengkap'   => $murid->nama_lengkap,
                        'nism'           => $murid->nism,
                        'is_kelas_akhir' => $isKelasAkhir,
                    ],
                    'is_disahkan'        => true,
                    'tahun_pelajaran'    => $tahunAktif ? ($tahunAktif->nama_hijriyah . ' (' . $tahunAktif->nama_masehi . ')') : '-',
                    'ruangan_asal'       => $riwayatKenaikan->ruanganAsal->nama_ruangan ?? $ruanganAktif?->nama_ruangan ?? '-',
                    'level_asal'         => $riwayatKenaikan->ruanganAsal->level->nama_level ?? $levelNama,
                    'level_tujuan'       => $riwayatKenaikan->levelTujuan->nama_level ?? ($riwayatKenaikan->status_keputusan === 'Lulus' ? 'LULUS MADRASAH' : ($riwayatKenaikan->ruanganAsal->level->nama_level ?? $levelNama)),
                    'status_keputusan'   => $riwayatKenaikan->status_keputusan, // Naik Kelas, Tinggal Kelas, Lulus
                    'no_sk'              => $riwayatKenaikan->no_sk ?? '-',
                    'nilai_akumulasi'    => (float) $riwayatKenaikan->nilai_akumulasi,
                    'catatan_wali_kelas' => $riwayatKenaikan->catatan_wali_kelas ?: 'Selamat dan tingkatkan terus semangat belajar serta akhlakul karimah.',
                    'tanggal_disahkan'   => $riwayatKenaikan->updated_at ? Carbon::parse($riwayatKenaikan->updated_at)->format('d-m-Y') : Carbon::now()->format('d-m-Y'),
                    'tanggal_disahkan_formatted' => $riwayatKenaikan->updated_at ? Carbon::parse($riwayatKenaikan->updated_at)->locale('id')->isoFormat('D MMMM YYYY') : Carbon::now()->locale('id')->isoFormat('D MMMM YYYY'),
                    'diputuskan_oleh'    => $namaPengesah,
                ]
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'murid' => [
                    'id'             => $murid->id,
                    'nama_lengkap'   => $murid->nama_lengkap,
                    'nism'           => $murid->nism,
                    'is_kelas_akhir' => $isKelasAkhir,
                ],
                'is_disahkan'      => false,
                'tahun_pelajaran'  => $tahunAktif ? ($tahunAktif->nama_hijriyah . ' (' . $tahunAktif->nama_masehi . ')') : '-',
                'ruangan_asal'     => $ruanganAktif?->nama_ruangan ?? '-',
                'level_asal'       => $levelNama,
                'level_tujuan'     => null,
                'status_keputusan' => null,
                'no_sk'            => null,
                'nilai_akumulasi'  => 0,
                'catatan_wali_kelas' => null,
                'tanggal_disahkan' => null,
                'pesan'            => 'Keputusan kenaikan kelas/kelulusan untuk tahun pelajaran ini belum disahkan oleh pihak madrasah. Informasi resmi akan ditampilkan di sini setelah rapat pleno dewan asatidz dan pengesahan pimpinan madrasah.',
            ]
        ], 200);
    }

    /**
     * Pengumuman Madrasah Khusus Wali Murid
     */
    public function getPengumuman(Request $request)
    {
        $today = date('Y-m-d');
        $pengumuman = \App\Models\Pengumuman::where('status', 'Terbit')
            ->whereIn('target_audience', ['Semua', 'Wali Murid'])
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')
                    ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->latest()
            ->get()
            ->map(function ($p) {
                return [
                    'id'                => $p->id,
                    'judul'             => $p->judul,
                    'konten_html'       => $p->konten,
                    'konten'            => strip_tags($p->konten ?? ''),
                    'tipe'              => $p->tipe ?? 'Informasi',
                    'target_audience'   => $p->target_audience ?? 'Wali Murid',
                    'lampiran_pdf_url'  => $p->lampiran_pdf_url,
                    'nama_file_pdf'     => $p->nama_file_pdf,
                    'tanggal_mulai'     => $p->tanggal_mulai ? $p->tanggal_mulai->format('d-m-Y') : ($p->created_at ? $p->created_at->format('d-m-Y') : date('d-m-Y')),
                    'tanggal_selesai'   => $p->tanggal_selesai ? $p->tanggal_selesai->format('d-m-Y') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $pengumuman
        ], 200);
    }

    /**
     * Dokumen Arsip Murid: Rapor (IMDA 1 & IMDA 2 / IMNI), SK Kelulusan, dan Ijazah
     */
    public function getDokumenAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id, ['ruangans.level']);
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $ruanganAktif = $murid->ruangans()
            ->when($tahunId, fn($q) => $q->where('murid_ruangans.tahun_pelajaran_id', $tahunId))
            ->first();

        $levelNama = $ruanganAktif?->level?->nama_level ?? '';
        $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

        // Ambil semua arsip dokumen murid ini
        $arsips = \App\Models\Arsip\ArsipDokumen::where('referensi_id', $murid->id)
            ->where('referensi_tipe', Murid::class)
            ->orderBy('created_at', 'desc')
            ->get();

        // 1. Rapor Murid (IMDA 1, IMDA 2, IMNI)
        $raporList = $arsips->where('tipe_dokumen', 'rapor_murid')->values()->map(function ($a) {
            $data = $a->snapshot_data ?? [];
            return [
                'id'               => $a->id,
                'nama_dokumen'     => $data['nama_ujian'] ?? 'Rapor Ujian',
                'tipe_ujian'       => $data['tipe_ujian'] ?? 'IMDA',
                'tahun_pelajaran'  => $data['tahun_pelajaran'] ?? '-',
                'nomor_dokumen'    => $data['nomor_dokumen'] ?? '-',
                'ruangan'          => $data['nama_ruangan'] ?? '-',
                'rata_rata'        => (float) ($data['rata_rata'] ?? 0),
                'total_mapel'      => is_array($data['matriks_nilai'] ?? null) ? count($data['matriks_nilai']) : 0,
                'tanggal_disahkan' => $data['tanggal_disahkan'] ?? $a->created_at->format('d-m-Y'),
                'download_url'     => url("/arsip-dokumen/{$a->id}/cetak?print=1"),
                'cetak_url'        => url("/arsip-dokumen/{$a->id}/cetak"),
            ];
        });

        // 2. Surat Keterangan Kelulusan / SKTB
        $skList = $arsips->where('tipe_dokumen', 'sk_keputusan')->values()->map(function ($a) {
            $data = $a->snapshot_data ?? [];
            return [
                'id'               => $a->id,
                'nama_dokumen'     => 'Surat Keterangan Kelulusan',
                'nomor_dokumen'    => $data['nomor_dokumen'] ?? '-',
                'tahun_pelajaran'  => $data['tahun_pelajaran'] ?? '-',
                'status_keputusan' => $data['status_keputusan'] ?? 'LULUS',
                'tanggal_disahkan' => $data['tanggal_disahkan'] ?? $a->created_at->format('d-m-Y'),
                'download_url'     => url("/arsip-dokumen/{$a->id}/cetak?print=1"),
                'cetak_url'        => url("/arsip-dokumen/{$a->id}/cetak"),
            ];
        });

        // 3. Ijazah Madrasah (Khusus Kelas Akhir)
        $ijazahList = $arsips->where('tipe_dokumen', 'ijazah')->values()->map(function ($a) {
            $data = $a->snapshot_data ?? [];
            return [
                'id'                 => $a->id,
                'nama_dokumen'       => 'Ijazah Madrasah ' . ($data['lulus_dari_tingkat'] ?? ''),
                'nomor_dokumen'      => $data['nomor_dokumen'] ?? '-',
                'tahun_pelajaran'    => $data['tahun_pelajaran'] ?? '-',
                'lulus_dari_tingkat' => $data['lulus_dari_tingkat'] ?? '-',
                'rata_rata'          => (float) ($data['rata_rata'] ?? 0),
                'tanggal_disahkan'   => $data['tanggal_disahkan'] ?? $a->created_at->format('d-m-Y'),
                'download_url'       => url("/arsip-dokumen/{$a->id}/cetak?print=1"),
                'cetak_url'          => url("/arsip-dokumen/{$a->id}/cetak"),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'murid' => [
                    'id'             => $murid->id,
                    'nama_lengkap'   => $murid->nama_lengkap,
                    'nism'           => $murid->nism,
                    'ruangan'        => $ruanganAktif?->nama_ruangan ?? '-',
                    'level'          => $levelNama,
                    'is_kelas_akhir' => $isKelasAkhir,
                ],
                'rapor'  => $raporList,
                'sk'     => $skList,
                'ijazah' => $ijazahList,
            ]
        ], 200);
    }

    /**
     * Detail Buku Tabungan Santri (app_murid)
     */
    public function getTabunganAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);
        $semuaTabungan = \App\Models\Tabungan\Tabungan::with(['periodeTabungan'])
            ->where('murid_id', $murid->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($semuaTabungan->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_tabungan' => false,
                    'murid' => [
                        'id' => $murid->id,
                        'nama_lengkap' => $murid->nama_lengkap,
                        'nism' => $murid->nism,
                    ],
                    'daftar_rekening' => [],
                    'message' => 'Santri belum memiliki rekening buku tabungan terdaftar.'
                ]
            ], 200);
        }

        if ($request->filled('tabungan_id')) {
            $tabungan = $semuaTabungan->firstWhere('id', $request->tabungan_id) ?? $semuaTabungan->first();
        } else {
            $tabungan = $semuaTabungan->first();
        }

        $tabunganService = app(\App\Services\Tabungan\TabunganService::class);
        $daftarRekening = $semuaTabungan->map(function ($tab) use ($tabunganService) {
            $k = $tabunganService->hitungPotongan($tab);
            return [
                'id' => $tab->id,
                'nomor_rekening' => $tab->nomor_rekening,
                'nama_rekening' => $tab->nama_rekening,
                'nama_nasabah' => $tab->nama_nasabah,
                'status' => $tab->status,
                'periode' => $tab->periodeTabungan->nama_periode ?? 'Tabungan Bebas',
                'saldo' => (int) $tab->saldo,
                'total_setor' => (int) $tab->total_setor,
                'total_tarik' => (int) $tab->total_tarik,
                'total_potongan' => (int) ($k['nominal_potongan'] ?? 0),
                'saldo_dapat_ditarik' => (int) ($k['saldo_dapat_ditarik'] ?? $tab->saldo),
            ];
        });

        $kalkulasi = $tabunganService->hitungPotongan($tabungan);
        $rekapMutasi = $tabunganService->getRekapMutasiBulanan($tabungan, $request->bulan);

        $daftarTransaksi = TransaksiTabungan::where('tabungan_id', $tabungan->id)
            ->with(['petugas', 'kategoriPenarikan', 'komplainAktif.diverifikasiOleh'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($t) {
                $kmp = $t->komplainAktif;
                return [
                    'id' => $t->id,
                    'kode_transaksi' => $t->kode_transaksi,
                    'tanggal' => Carbon::parse($t->tanggal)->format('d-m-Y'),
                    'jenis_transaksi' => $t->jenis_transaksi,
                    'nominal' => (int) $t->nominal_bersih,
                    'saldo_awal' => (int) $t->saldo_awal,
                    'saldo_akhir' => (int) $t->saldo_akhir,
                    'kategori' => $t->kategoriPenarikan->nama_kategori ?? ($t->jenis_transaksi === 'Setor' ? 'Setoran Tunai' : $t->jenis_transaksi),
                    'keterangan' => $t->keterangan ?: '-',
                    'petugas' => $t->petugas->name ?? 'Sistem',
                    'metode' => $t->metode,
                    'komplain' => $kmp ? [
                        'id' => $kmp->id,
                        'kode_komplain' => $kmp->kode_komplain,
                        'nominal_tercatat' => (int) $kmp->nominal_tercatat,
                        'nominal_klaim' => (int) $kmp->nominal_klaim,
                        'selisih' => (int) $kmp->selisih,
                        'alasan' => $kmp->alasan,
                        'status' => $kmp->status,
                        'catatan_verifikasi' => $kmp->catatan_verifikasi,
                        'diverifikasi_pada' => $kmp->diverifikasi_pada ? Carbon::parse($kmp->diverifikasi_pada)->format('d-m-Y H:i') : null,
                        'petugas_verifikasi' => $kmp->diverifikasiOleh->name ?? 'Admin / Bendahara',
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'has_tabungan' => true,
                'murid' => [
                    'id' => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism' => $murid->nism,
                ],
                'daftar_rekening' => $daftarRekening,
                'rekening' => [
                    'id' => $tabungan->id,
                    'nomor_rekening' => $tabungan->nomor_rekening,
                    'nama_rekening' => $tabungan->nama_rekening,
                    'nama_nasabah' => $tabungan->nama_nasabah,
                    'jenis_nasabah' => $tabungan->jenis_nasabah,
                    'status' => $tabungan->status,
                    'periode' => $tabungan->periodeTabungan->nama_periode ?? 'Tabungan Bebas',
                    'saldo' => (int) $tabungan->saldo,
                    'total_setor' => (int) $tabungan->total_setor,
                    'total_tarik' => (int) $tabungan->total_tarik,
                    'total_potongan' => (int) ($kalkulasi['nominal_potongan'] ?? 0),
                    'persentase_potongan' => (float) ($kalkulasi['persentase_potongan'] ?? 0),
                    'saldo_bersih_total' => (int) ($kalkulasi['saldo_bersih_total'] ?? $tabungan->saldo),
                    'saldo_dapat_ditarik' => (int) ($kalkulasi['saldo_dapat_ditarik'] ?? $tabungan->saldo),
                ],
                'rekap_bulanan' => $rekapMutasi['rekap_bulanan'] ?? [],
                'riwayat' => $daftarTransaksi,
            ]
        ], 200);
    }

    /**
     * Ajukan Komplain / Sanggahan Setor Tunai oleh Wali Murid
     */
    public function ajukanKomplain(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaksi_id' => 'required|exists:transaksi_tabungans,id',
            'nominal_klaim' => 'required|numeric|min:1000',
            'alasan' => 'required|string|min:5|max:500',
            'foto_bukti' => 'nullable|string',
        ], [
            'transaksi_id.required' => 'Transaksi setoran wajib dipilih.',
            'nominal_klaim.required' => 'Nominal setoran yang seharusnya wajib diisi.',
            'nominal_klaim.min' => 'Nominal minimal Rp 1.000.',
            'alasan.required' => 'Alasan atau kronologi komplain wajib diisi.',
            'alasan.min' => 'Alasan komplain minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $trx = TransaksiTabungan::with(['tabungan.murid'])->findOrFail($request->transaksi_id);

        if ($trx->jenis_transaksi !== 'Setor') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi setor tunai yang dapat diajukan komplain/sanggahan.'
            ], 400);
        }

        // Validasi Otorisasi Kepemilikan Rekening / Transaksi
        $wali = $this->resolveWali($request);
        $user = $request->user();
        if ((!$user || !$user->hasAnyRole(['administrator', 'staff'])) && (!$wali || $trx->tabungan?->murid?->wali_murid_id !== $wali->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki otorisasi untuk mengajukan komplain pada transaksi rekening ini.'
            ], 403);
        }

        if ((float) $request->nominal_klaim == (float) $trx->nominal_bersih) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal klaim yang Anda masukkan sama dengan nominal yang sudah tercatat di sistem (Rp ' . number_format($trx->nominal_bersih, 0, ',', '.') . ').'
            ], 422);
        }

        // Cek apakah sudah ada komplain aktif yang menunggu verifikasi pada transaksi ini
        $komplainAktif = TabunganKomplain::where('transaksi_tabungan_id', $trx->id)
            ->where('status', 'Menunggu_Verifikasi')
            ->first();

        if ($komplainAktif) {
            return response()->json([
                'success' => false,
                'message' => "Transaksi ini sedang dalam proses verifikasi komplain ({$komplainAktif->kode_komplain}). Mohon menunggu tanggapan dari pihak pengelola madrasah."
            ], 400);
        }

        $nominalTercatat = (float) $trx->nominal_bersih;
        $nominalKlaim = (float) $request->nominal_klaim;
        $selisih = $nominalKlaim - $nominalTercatat;

        $komplain = TabunganKomplain::create([
            'kode_komplain' => TabunganKomplain::generateKodeKomplain(),
            'transaksi_tabungan_id' => $trx->id,
            'tabungan_id' => $trx->tabungan_id,
            'murid_id' => $trx->tabungan->murid_id,
            'wali_id' => $wali?->id ?? $user?->id,
            'nominal_tercatat' => $nominalTercatat,
            'nominal_klaim' => $nominalKlaim,
            'selisih' => $selisih,
            'alasan' => $request->alasan,
            'foto_bukti' => $request->foto_bukti ?? null,
            'status' => 'Menunggu_Verifikasi',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Alhamdulillah, komplain ({$komplain->kode_komplain}) berhasil diajukan dan sedang menunggu verifikasi oleh Pengelola/Bendahara Madrasah.",
            'data' => [
                'id' => $komplain->id,
                'kode_komplain' => $komplain->kode_komplain,
                'transaksi_id' => $trx->id,
                'nominal_tercatat' => (int) $nominalTercatat,
                'nominal_klaim' => (int) $nominalKlaim,
                'selisih' => (int) $selisih,
                'alasan' => $komplain->alasan,
                'status' => $komplain->status,
                'tanggal' => Carbon::parse($komplain->created_at)->format('d-m-Y H:i'),
            ]
        ], 200);
    }

    /**
     * Dapatkan Riwayat Komplain Santri
     */
    public function getRiwayatKomplain($murid_id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $murid_id);
        $komplains = TabunganKomplain::with(['transaksiTabungan.petugas', 'diverifikasiOleh'])
            ->where('murid_id', $murid->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($kmp) {
                return [
                    'id' => $kmp->id,
                    'kode_komplain' => $kmp->kode_komplain,
                    'transaksi_id' => $kmp->transaksi_tabungan_id,
                    'kode_transaksi' => $kmp->transaksiTabungan->kode_transaksi ?? '-',
                    'tanggal_transaksi' => $kmp->transaksiTabungan ? Carbon::parse($kmp->transaksiTabungan->tanggal)->format('d-m-Y') : '-',
                    'nominal_tercatat' => (int) $kmp->nominal_tercatat,
                    'nominal_klaim' => (int) $kmp->nominal_klaim,
                    'selisih' => (int) $kmp->selisih,
                    'alasan' => $kmp->alasan,
                    'status' => $kmp->status,
                    'diverifikasi_pada' => $kmp->diverifikasi_pada ? Carbon::parse($kmp->diverifikasi_pada)->format('d-m-Y H:i') : null,
                    'petugas_verifikasi' => $kmp->diverifikasiOleh->name ?? 'Admin / Bendahara',
                    'catatan_verifikasi' => $kmp->catatan_verifikasi,
                    'created_at' => Carbon::parse($kmp->created_at)->format('d-m-Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'murid' => [
                    'id' => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism' => $murid->nism,
                ],
                'komplains' => $komplains,
            ]
        ], 200);
    }

    /**
     * Batalkan Pengajuan Komplain
     */
    public function batalkanKomplain($id, Request $request)
    {
        $komplain = TabunganKomplain::with(['tabungan.murid'])->findOrFail($id);

        // Validasi otorisasi pembatalan komplain
        $wali = $this->resolveWali($request);
        $user = $request->user();
        if ((!$user || !$user->hasAnyRole(['administrator', 'staff'])) && (!$wali || ($komplain->wali_id !== $wali->id && $komplain->tabungan?->murid?->wali_murid_id !== $wali->id))) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki otorisasi untuk membatalkan komplain ini.'
            ], 403);
        }

        if ($komplain->status !== 'Menunggu_Verifikasi') {
            return response()->json([
                'success' => false,
                'message' => "Komplain tidak dapat dibatalkan karena sudah berstatus {$komplain->status}."
            ], 400);
        }

        $komplain->update([
            'status' => 'Dibatalkan',
            'catatan_verifikasi' => 'Dibatalkan oleh wali murid pada ' . date('d-m-Y H:i'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengajuan komplain {$komplain->kode_komplain} berhasil dibatalkan."
        ], 200);
    }

    /**
     * Riwayat Pembelian Santri di Koperasi Madrasah (app_murid)
     */
    public function getKoperasiAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);

        $penjualans = \App\Models\Koperasi\PenjualanKoperasi::with(['details.produk', 'details.paket', 'petugas'])
            ->where('murid_id', $murid->id)
            ->where('status', 'Selesai')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalBelanja = $penjualans->sum('total_akhir');
        $totalTransaksi = $penjualans->count();
        $totalItem = $penjualans->sum('total_item');

        $riwayat = $penjualans->map(function ($p) {
            $items = $p->details->map(function ($d) {
                return [
                    'id' => $d->id,
                    'tipe_item' => $d->tipe_item,
                    'kode_item' => $d->kode_item,
                    'nama_item' => $d->nama_item,
                    'satuan' => $d->satuan,
                    'harga_satuan' => (int) $d->harga_jual,
                    'jumlah' => (int) $d->jumlah,
                    'diskon_item' => (int) $d->diskon_item,
                    'subtotal' => (int) $d->subtotal,
                ];
            });

            return [
                'id' => $p->id,
                'nomor_nota' => $p->nomor_nota,
                'tanggal' => Carbon::parse($p->tanggal)->format('d-m-Y H:i'),
                'tanggal_formatted' => Carbon::parse($p->tanggal)->translatedFormat('d F Y, H:i'),
                'total_item' => (int) $p->total_item,
                'subtotal' => (int) $p->subtotal,
                'diskon' => (int) $p->diskon,
                'total_akhir' => (int) $p->total_akhir,
                'metode_pembayaran' => $p->metode_pembayaran,
                'nominal_bayar' => (int) $p->nominal_bayar,
                'kembalian' => (int) $p->kembalian,
                'status' => $p->status,
                'catatan' => $p->catatan ?: '-',
                'kasir' => $p->petugas->name ?? 'Kasir Koperasi',
                'items' => $items,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'murid' => [
                    'id' => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism' => $murid->nism,
                ],
                'summary' => [
                    'total_belanja' => (int) $totalBelanja,
                    'total_transaksi' => $totalTransaksi,
                    'total_item' => (int) $totalItem,
                ],
                'riwayat' => $riwayat,
            ]
        ], 200);
    }

    /**
     * Informasi Kas Ruangan / Kelas per Santri (app_murid)
     * Menampilkan total kas yang telah terkumpul per anak dan riwayat pembayarannya.
     */
    public function getKasRuanganAnak($id, Request $request)
    {
        $murid = $this->authorizeMuridForWali($request, $id);
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $ruanganAktif = $murid->ruangans()
            ->with(['waliRuangan', 'level', 'pengaturanKas'])
            ->when($tahunId, fn($q) => $q->where('murid_ruangans.tahun_pelajaran_id', $tahunId))
            ->first();

        if (!$ruanganAktif) {
            $ruanganAktif = $murid->ruanganMasuk()
                ->with(['waliRuangan', 'level', 'pengaturanKas'])
                ->first();
        }

        if (!$ruanganAktif) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_kas' => false,
                    'pesan' => 'Santri belum ditempatkan pada ruangan kelas aktif.',
                    'murid' => [
                        'id' => $murid->id,
                        'nama_lengkap' => $murid->nama_lengkap,
                        'nism' => $murid->nism,
                        'jenis_kelamin' => $murid->jenis_kelamin,
                        'total_kas_terkumpul' => 0,
                        'total_transaksi' => 0,
                    ],
                    'ruangan' => null,
                    'riwayat_pembayaran' => [],
                ]
            ], 200);
        }

        $pengaturan = PengaturanKasRuangan::where('ruangan_id', $ruanganAktif->id)->first();
        $nominalLaki = (int) ($pengaturan->nominal_laki ?? 0);
        $nominalPerempuan = (int) ($pengaturan->nominal_perempuan ?? 0);
        $targetKas = ($murid->jenis_kelamin === 'P') ? $nominalPerempuan : $nominalLaki;
        $hasPengaturan = $pengaturan && (($nominalLaki > 0) || ($nominalPerempuan > 0));

        $pembayaranMurid = PembayaranKasRuangan::where('ruangan_id', $ruanganAktif->id)
            ->where('murid_id', $murid->id)
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalTerkumpul = (int) $pembayaranMurid->sum('jumlah_bayar');
        $kurangKas = max(0, $targetKas - $totalTerkumpul);

        $hasKas = $hasPengaturan || ($totalTerkumpul > 0) || PembayaranKasRuangan::where('ruangan_id', $ruanganAktif->id)->exists();

        if ($targetKas > 0) {
            if ($totalTerkumpul >= $targetKas) {
                $status = 'Lunas';
            } elseif ($totalTerkumpul > 0) {
                $status = 'Sebagian';
            } else {
                $status = 'Belum Bayar';
            }
        } else {
            $status = $totalTerkumpul > 0 ? 'Lunas' : 'Bebas Kas';
        }

        $riwayat = $pembayaranMurid->map(function ($p) {
            return [
                'id' => $p->id,
                'tanggal_bayar' => $p->tanggal_bayar ? Carbon::parse($p->tanggal_bayar)->format('d-m-Y') : '-',
                'tanggal_bayar_formatted' => $p->tanggal_bayar ? Carbon::parse($p->tanggal_bayar)->translatedFormat('d F Y') : '-',
                'jumlah_bayar' => (int) $p->jumlah_bayar,
                'is_disetor' => (bool) $p->is_disetor,
                'status_setor' => $p->is_disetor ? 'Telah Disetor ke Madrasah' : 'Tersimpan di Wali Ruangan',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'has_kas' => $hasKas,
                'pesan' => $hasKas ? null : 'Ruangan kelas santri saat ini belum memiliki catatan kas ruangan.',
                'murid' => [
                    'id' => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nism' => $murid->nism,
                    'jenis_kelamin' => $murid->jenis_kelamin,
                    'target_kas' => $targetKas,
                    'total_kas_terkumpul' => $totalTerkumpul,
                    'kurang_kas' => $kurangKas,
                    'status' => $status,
                    'total_transaksi' => $pembayaranMurid->count(),
                ],
                'ruangan' => [
                    'id' => $ruanganAktif->id,
                    'nama_ruangan' => $ruanganAktif->nama_ruangan,
                    'level' => $ruanganAktif->level->nama_level ?? '-',
                    'wali_ruangan' => $ruanganAktif->waliRuangan->nama_lengkap ?? $ruanganAktif->waliRuangan->nama ?? 'Ustadz Wali Ruangan',
                ],
                'riwayat_pembayaran' => $riwayat,
            ]
        ], 200);
    }
}
