<?php

namespace App\Services;

use App\Models\PengaturanTagihan;
use App\Models\TagihanMurid;
use App\Models\Ujian\DispensasiUjian;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\NilaiUjian;
use Illuminate\Support\Facades\DB;

class NilaiUjianService
{
    /**
     * Hitung progres nilai masuk per ruangan
     */
    public function hitungProgresRuangan($ujianId, $daftarRuangan)
    {
        $semuaJadwal = JadwalUjian::where('ujian_id', $ujianId)
            ->get()
            ->groupBy('level_id');

        $semuaNilaiMasuk = NilaiUjian::where('ujian_id', $ujianId)
            ->get()
            ->groupBy('ruangan_id');

        $dataProgres = collect();

        foreach ($daftarRuangan as $ruangan) {
            $jumlahMurid = $ruangan->murids_count;

            $jadwalLevelIni = $semuaJadwal->get($ruangan->level_id);
            $jumlahMapel = $jadwalLevelIni ? $jadwalLevelIni->count() : 0;

            $targetNilai = $jumlahMurid * $jumlahMapel;

            $nilaiRuanganIni = $semuaNilaiMasuk->get($ruangan->id);
            $totalDiinput = $nilaiRuanganIni ? $nilaiRuanganIni->count() : 0;
            $totalDipublish = $nilaiRuanganIni ? $nilaiRuanganIni->where('is_published', true)->count() : 0;

            $mapelKurang = [];
            if ($jadwalLevelIni && $jumlahMurid > 0) {
                foreach ($jadwalLevelIni as $jadwal) {
                    $nilaiMapelIni = $nilaiRuanganIni ? $nilaiRuanganIni->where('jadwal_ujian_id', $jadwal->id)->count() : 0;

                    if ($nilaiMapelIni < $jumlahMurid) {
                        $namaMapel = $jadwal->mata_pelajaran_id ? ($jadwal->mataPelajaran->nama_mapel ?? '-') : $jadwal->nama_mata_pelajaran_custom;
                        $mapelKurang[] = $namaMapel;
                    }
                }
            }

            $persentase = $targetNilai > 0 ? round(($totalDiinput / $targetNilai) * 100, 1) : 0;
            if ($persentase > 100) $persentase = 100;

            $dataProgres->push((object)[
                'ruangan'         => $ruangan,
                'jumlah_murid'    => $jumlahMurid,
                'jumlah_mapel'    => $jumlahMapel,
                'target_nilai'    => $targetNilai,
                'total_diinput'   => $totalDiinput,
                'total_dipublish' => $totalDipublish,
                'persentase'      => $persentase,
                'mapel_kurang'    => $mapelKurang
            ]);
        }

        return $dataProgres;
    }

    /**
     * Evaluasi syarat administrasi (tunggakan & dispensasi) untuk murid
     */
    public function evaluasiSyaratAdmin($ujianTerpilih, $ruanganTerpilih, $murids)
    {
        $semesterUjian = $ujianTerpilih->semester_relasi;
        $bulanIds = [];

        if ($semesterUjian && $semesterUjian->tanggal_mulai && $semesterUjian->tanggal_selesai) {
            $bulanIds = \App\Models\BulanHijriyah::where('tahun_pelajaran_id', $ujianTerpilih->tahun_pelajaran_id)
                ->where('tanggal_selesai_masehi', '>=', $semesterUjian->tanggal_mulai)
                ->where('tanggal_mulai_masehi', '<=', $semesterUjian->tanggal_selesai)
                ->pluck('id')
                ->toArray();
        } else {
            $bulanIds = \App\Models\BulanHijriyah::where('tahun_pelajaran_id', $ujianTerpilih->tahun_pelajaran_id)
                ->pluck('id')
                ->toArray();
        }

        $muridIds = $murids->pluck('id')->toArray();

        $dispensasiMuridIds = DispensasiUjian::where('ujian_id', $ujianTerpilih->id)
            ->whereIn('murid_id', $muridIds)
            ->pluck('murid_id')
            ->toArray();

        $tipeUjian = $ujianTerpilih->tipe_ujian ?? 'IMDA';
        $jenis_tagihan_id = PengaturanTagihan::where('tahun_pelajaran_id', $ujianTerpilih->tahun_pelajaran_id)
            ->where('level_id', $ruanganTerpilih->level_id)
            ->where('nama_tagihan', 'LIKE', '%' . $tipeUjian . '%')
            ->value('id');

        $imdaLunasMuridIds = TagihanMurid::whereIn('murid_id', $muridIds)
            ->whereIn('status_bayar', ['Lunas', 'Bebas/Gratis', 'Ditanggung Donatur'])
            ->where(function ($q) use ($ruanganTerpilih, $jenis_tagihan_id) {
                $q->where('ruangan_id', $ruanganTerpilih->id)
                    ->where('pengaturan_tagihan_id', $jenis_tagihan_id);
            })
            ->pluck('murid_id')
            ->toArray();

        $sppQuery = TagihanMurid::whereIn('murid_id', $muridIds)
            ->where('ruangan_id', $ruanganTerpilih->id)
            ->where('status_bayar', 'Belum Lunas');

        if (!empty($bulanIds)) {
            $sppQuery->whereNotNull('bulan_hijriyah_id')
                ->whereIn('bulan_hijriyah_id', $bulanIds);
        } else {
            $namaSemester = $semesterUjian->nama_semester ?? '';
            $bulanSemester = (str_contains($namaSemester, '1') || str_contains(strtolower($namaSemester), 'ganjil'))
                ? ['Syawal', 'Dzul Qadah', 'Dzul Hijjah', 'Muharram', 'Shafar']
                : ['Rabiul Awal', 'Rabiul Tsani', 'Jumadal Ula', 'Jumadal Akhir', 'Rajab'];
            $sppQuery->where(function ($q) use ($bulanSemester) {
                foreach ($bulanSemester as $bulan) {
                    $q->orWhere('nama_tagihan_spesifik', 'like', "%SPP $bulan%")
                        ->orWhere('nama_tagihan_spesifik', 'like', "%Syahriyah $bulan%");
                }
            });
        }

        $sppMenunggakMuridIds = $sppQuery->pluck('murid_id')->toArray();

        foreach ($murids as $murid) {
            $hasDispensasi = in_array($murid->id, $dispensasiMuridIds);
            $imdaLunas     = in_array($murid->id, $imdaLunasMuridIds);
            $sppMenunggak  = in_array($murid->id, $sppMenunggakMuridIds);

            if ($hasDispensasi) {
                $murid->is_locked = false;
                $murid->lock_reason = 'Mendapat Dispensasi / Izin';
            } else {
                if (!$imdaLunas || $sppMenunggak) {
                    $murid->is_locked = true;
                    $alasan = [];
                    if (!$imdaLunas) $alasan[] = 'Iuran Ujian (' . $tipeUjian . ')';
                    if ($sppMenunggak) $alasan[] = 'SPP Semester';
                    $murid->lock_reason = 'Tunggakan: ' . implode(' & ', $alasan);
                } else {
                    $murid->is_locked = false;
                    $murid->lock_reason = 'Lunas Administrasi';
                }
            }
        }

        return $murids;
    }

    /**
     * Simpan / update nilai leger secara massal
     */
    public function simpanNilaiLeger($ruanganId, $ujianId, array $dataNilai, $aksi, $userId)
    {
        $isPublished = ($aksi === 'publish');

        return DB::transaction(function () use ($ruanganId, $ujianId, $dataNilai, $isPublished, $userId) {
            foreach ($dataNilai as $muridId => $jadwalData) {
                foreach ($jadwalData as $jadwalId => $nilai) {
                    if ($nilai !== null && $nilai !== '') {
                        NilaiUjian::updateOrCreate(
                            [
                                'ujian_id'        => $ujianId,
                                'ruangan_id'      => $ruanganId,
                                'jadwal_ujian_id' => $jadwalId,
                                'murid_id'        => $muridId,
                            ],
                            [
                                'nilai'        => $nilai,
                                'is_published' => $isPublished,
                                'diinput_oleh' => $userId,
                            ]
                        );
                    }
                }
            }

            return $isPublished;
        });
    }
}
