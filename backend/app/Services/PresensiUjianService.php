<?php

namespace App\Services;

use App\Models\Ruangan;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\PresensiPengawasUjian;
use App\Models\Ujian\PresensiUjian;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use Illuminate\Support\Facades\DB;

class PresensiUjianService
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Menghitung progres kelengkapan presensi ujian per ruangan kelas
     */
    public function hitungProgresPresensiRuangan($ujianId, $daftarRuangan)
    {
        $semuaJadwal = JadwalUjian::where('ujian_id', $ujianId)
            ->with('mataPelajaran')
            ->get()
            ->groupBy('level_id');

        $semuaPresensiMasuk = PresensiUjian::where('ujian_id', $ujianId)
            ->get()
            ->groupBy('ruangan_id');

        $semuaPengecualianMuridIds = \App\Models\Ujian\PengecualianUjian::where('ujian_id', $ujianId)
            ->pluck('murid_id')
            ->toArray();

        $dataProgres = collect();

        foreach ($daftarRuangan as $ruangan) {
            $totalMurid = $ruangan->murids_count ?? $ruangan->murids()->where('murids.status', 'Aktif')->count();
            $muridIdsRuangan = $ruangan->murids()->where('murids.status', 'Aktif')->pluck('murids.id')->toArray();
            $jumlahTidakIkut = count(array_intersect($muridIdsRuangan, $semuaPengecualianMuridIds));
            $jumlahPeserta = max(0, $totalMurid - $jumlahTidakIkut);

            $jadwalLevelIni = $semuaJadwal->get($ruangan->level_id, collect());
            $jumlahMapel = $jadwalLevelIni->count();

            $targetPresensi = $jumlahPeserta * $jumlahMapel;

            $presensiRuanganIni = $semuaPresensiMasuk->get($ruangan->id, collect());
            $totalDiinput = $presensiRuanganIni->count();

            $mapelKurang = [];
            if ($jadwalLevelIni->isNotEmpty() && $jumlahPeserta > 0) {
                foreach ($jadwalLevelIni as $jadwal) {
                    $presensiMapelIni = $presensiRuanganIni->where('jadwal_ujian_id', $jadwal->id)->count();

                    if ($presensiMapelIni < $jumlahPeserta) {
                        $namaMapel = $jadwal->mata_pelajaran_id
                            ? ($jadwal->mataPelajaran->nama_mapel ?? '-')
                            : $jadwal->nama_mata_pelajaran_custom;
                        $mapelKurang[] = $namaMapel;
                    }
                }
            }

            $persentase = $targetPresensi > 0 ? round(($totalDiinput / $targetPresensi) * 100, 1) : ($jumlahMapel == 0 ? 0 : 100);
            if ($persentase > 100) {
                $persentase = 100;
            }

            $dataProgres->push((object)[
                'ruangan'           => $ruangan,
                'jumlah_murid'      => $totalMurid,
                'jumlah_peserta'    => $jumlahPeserta,
                'jumlah_tidak_ikut' => $jumlahTidakIkut,
                'jumlah_mapel'      => $jumlahMapel,
                'target_presensi'   => $targetPresensi,
                'total_diinput'     => $totalDiinput,
                'persentase'        => $persentase,
                'mapel_kurang'      => $mapelKurang,
            ]);
        }

        return $dataProgres;
    }

    /**
     * Menyimpan data presensi murid dan pengawas secara massal
     */
    public function simpanPresensiMassal(array $dataPresensi, $ujianId, $jadwalId, $ruanganId, array $pengawasData = [], $userId = null)
    {
        return DB::transaction(function () use ($dataPresensi, $ujianId, $jadwalId, $ruanganId, $pengawasData, $userId) {
            $jumlahDisimpan = 0;

            // 1. Simpan Presensi Murid
            foreach ($dataPresensi as $muridId => $item) {
                $status = is_array($item) ? ($item['status'] ?? null) : $item;
                $catatan = is_array($item) ? ($item['catatan'] ?? null) : null;

                if (!empty($status)) {
                    PresensiUjian::updateOrCreate(
                        [
                            'ujian_id'        => $ujianId,
                            'jadwal_ujian_id' => $jadwalId,
                            'ruangan_id'      => $ruanganId,
                            'murid_id'        => $muridId,
                        ],
                        [
                            'status'       => $status,
                            'catatan'      => $catatan,
                            'diinput_oleh' => $userId,
                        ]
                    );
                    $jumlahDisimpan++;
                } else {
                    PresensiUjian::where('ujian_id', $ujianId)
                        ->where('jadwal_ujian_id', $jadwalId)
                        ->where('ruangan_id', $ruanganId)
                        ->where('murid_id', $muridId)
                        ->delete();
                }
            }

            // 2. Simpan Presensi Pengawas Ujian & Berita Acara (jika disertakan)
            if (!empty($pengawasData)) {
                PresensiPengawasUjian::updateOrCreate(
                    [
                        'jadwal_ujian_id' => $jadwalId,
                        'ruangan_id'      => $ruanganId,
                    ],
                    [
                        'ustadz_id'             => $pengawasData['ustadz_id'] ?? null,
                        'ustadz_pengganti_id'   => $pengawasData['ustadz_pengganti_id'] ?? null,
                        'status'                => $pengawasData['status'] ?? 'Hadir',
                        'catatan_berita_acara'  => $pengawasData['catatan_berita_acara'] ?? null,
                        'diinput_oleh'          => $userId,
                    ]
                );
            }

            return $jumlahDisimpan;
        });
    }

    /**
     * Menyusun matriks rekapitulasi kehadiran ujian untuk satu kelas / ruangan
     */
    public function hitungMatriksRekapPresensi($ujianId, $ruanganId)
    {
        $ujian = Ujian::with('tahunPelajaran')->findOrFail($ujianId);
        $ruangan = Ruangan::with('level')->findOrFail($ruanganId);

        // Ambil murid yang aktif di ruangan ini
        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruanganId, $ujian->tahun_pelajaran_id, 'Aktif');

        // Ambil jadwal ujian untuk level kelas ini
        $jadwals = JadwalUjian::with('mataPelajaran', 'pengawas')
            ->where('ujian_id', $ujianId)
            ->where('level_id', $ruangan->level_id)
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Ambil semua rekaman presensi
        $presensiDb = PresensiUjian::where('ujian_id', $ujianId)
            ->where('ruangan_id', $ruanganId)
            ->get();

        // Ambil data pengecualian ujian
        $pengecualianMap = \App\Models\Ujian\PengecualianUjian::where('ujian_id', $ujianId)
            ->pluck('alasan', 'murid_id');

        $dataRekap = collect();
        $totalSesi = $jadwals->count();

        foreach ($murids as $murid) {
            $isTidakIkut = $pengecualianMap->has($murid->id);
            $alasanTidakIkut = $pengecualianMap->get($murid->id);
            $presensiMurid = $presensiDb->where('murid_id', $murid->id);

            $detailPerMapel = [];
            $hadir = 0;
            $sakit = 0;
            $izin = 0;
            $alpha = 0;
            $dispensasi = 0;

            foreach ($jadwals as $jadwal) {
                $p = $presensiMurid->firstWhere('jadwal_ujian_id', $jadwal->id);
                $status = $p ? $p->status : ($isTidakIkut ? 'Tidak Ikut' : '-');

                $detailPerMapel[$jadwal->id] = [
                    'jadwal_id' => $jadwal->id,
                    'status'    => $status,
                    'catatan'   => $p ? $p->catatan : ($isTidakIkut ? $alasanTidakIkut : null),
                ];

                match ($status) {
                    'Hadir'      => $hadir++,
                    'Sakit'      => $sakit++,
                    'Izin'       => $izin++,
                    'Alpha'      => $alpha++,
                    'Dispensasi' => $dispensasi++,
                    default      => null
                };
            }

            $totalTerisi = $hadir + $sakit + $izin + $alpha + $dispensasi;
            $persentaseKehadiran = $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 1) : 0;

            $dataRekap->push((object)[
                'murid'                => $murid,
                'is_tidak_ikut'        => $isTidakIkut,
                'alasan_tidak_ikut'    => $alasanTidakIkut,
                'detail_per_mapel'     => $detailPerMapel,
                'hadir'                => $hadir,
                'sakit'                => $sakit,
                'izin'                 => $izin,
                'alpha'                => $alpha,
                'dispensasi'           => $dispensasi,
                'total_terisi'         => $totalTerisi,
                'total_sesi'           => $totalSesi,
                'persentase_kehadiran' => $persentaseKehadiran,
            ]);
        }

        return [
            'ujian'     => $ujian,
            'ruangan'   => $ruangan,
            'jadwals'   => $jadwals,
            'dataRekap' => $dataRekap,
            'totalSesi' => $totalSesi,
        ];
    }

    /**
     * Ambil data lengkap untuk cetak DHPU (Daftar Hadir Peserta Ujian)
     */
    public function ambilDataCetakDhpu($ujianId, $ruanganId, $jadwalId)
    {
        $ujian = Ujian::with('tahunPelajaran')->findOrFail($ujianId);
        $ruangan = Ruangan::with(['level', 'waliRuangan'])->findOrFail($ruanganId);
        $jadwal = JadwalUjian::with(['mataPelajaran', 'pengawas'])->findOrFail($jadwalId);

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruanganId, $ujian->tahun_pelajaran_id, 'Aktif');

        $pengecualianMap = \App\Models\Ujian\PengecualianUjian::where('ujian_id', $ujianId)
            ->pluck('alasan', 'murid_id');

        $presensiTersimpan = PresensiUjian::where('ujian_id', $ujianId)
            ->where('jadwal_ujian_id', $jadwalId)
            ->where('ruangan_id', $ruanganId)
            ->get()
            ->keyBy('murid_id');

        $presensiPengawas = PresensiPengawasUjian::with(['ustadz', 'ustadzPengganti'])
            ->where('jadwal_ujian_id', $jadwalId)
            ->where('ruangan_id', $ruanganId)
            ->first();

        return [
            'ujian'             => $ujian,
            'ruangan'           => $ruangan,
            'jadwal'            => $jadwal,
            'murids'            => $murids,
            'pengecualianMap'   => $pengecualianMap,
            'presensiTersimpan' => $presensiTersimpan,
            'presensiPengawas'  => $presensiPengawas,
        ];
    }
}
