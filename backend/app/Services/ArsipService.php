<?php

namespace App\Services;

use App\Models\Arsip\ArsipDokumen;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\PresensiMurid;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\RiwayatKenaikan;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArsipService
{
    /**
     * Memproses Pengarsipan Rapor (Serta SK & Ijazah jika memenuhi syarat)
     */
    public function prosesPengarsipan($murid_id, $ujian_id)
    {
        return DB::transaction(function () use ($murid_id, $ujian_id) {
            // 1. AMBIL DATA UTAMA
            $ujian = Ujian::with(['tahunPelajaran', 'semester_relasi'])->findOrFail($ujian_id);
            $murid = Murid::with('waliMurid')->findOrFail($murid_id);
            $ruangan = $murid->ruangans()->with('level.tingkat')->where('ruangans.tahun_pelajaran_id', $ujian->tahun_pelajaran_id)->first();

            $tingkat_id = $ruangan->level->tingkat_id ?? null;
            $ruangan_id = $ruangan->id ?? null;

            // 2. AMBIL NILAI (Wajib Publish)
            $nilaiMapel = NilaiUjian::with('jadwalUjian.mataPelajaran')
                ->where('ujian_id', $ujian->id)
                ->where('murid_id', $murid->id)
                ->where('is_published', true)
                ->get();

            if ($nilaiMapel->isEmpty()) {
                throw new \Exception('Belum ada nilai yang dipublikasikan untuk murid ini.');
            }

            // 3. OLAH DATA PENDUKUNG (Ranking, Absen, Pejabat)
            $peringkat = $this->hitungPeringkat($ujian->id, $ruangan_id, $murid->id);
            $absensi = $this->hitungAbsensi($ujian->semester_id, $murid->id);
            $riwayatKenaikan = $this->getRiwayatKenaikan($ujian, $murid->id); // Diperbarui
            $pejabat = $this->getPejabatPenandatangan($tingkat_id, $ruangan_id);

            // 4. SUSUN JSON RAPOR
            $snapshotRapor = $this->susunJsonRapor($ujian, $murid, $ruangan, $nilaiMapel, $peringkat, $absensi, $riwayatKenaikan, $pejabat);

            // Simpan Rapor
            ArsipDokumen::updateOrCreate(
                [
                    'tipe_dokumen' => 'rapor_murid',
                    'referensi_tipe' => get_class($murid),
                    'referensi_id' => $murid->id,
                    'snapshot_data->nama_ujian' => $ujian->nama_ujian
                ],
                [
                    'dicetak_oleh' => Auth::id(),
                    'snapshot_data' => $snapshotRapor
                ]
            );

            return 'Rapor milik ' . $murid->nama_lengkap . ' berhasil disahkan!';
        });
    }

    // =========================================================================
    // PRIVATE METHODS (Fungsi Bantuan Agar Kode Terbaca Rapi)
    // =========================================================================

    private function hitungPeringkat($ujian_id, $ruangan_id, $murid_id)
    {
        $semuaNilai = NilaiUjian::where('ujian_id', $ujian_id)->where('ruangan_id', $ruangan_id)->where('is_published', true)->get()->groupBy('murid_id');
        $rankingData = [];
        foreach ($semuaNilai as $mId => $nilais) {
            $rankingData[$mId] = $nilais->sum('nilai');
        }
        arsort($rankingData);

        $rank = 1;
        foreach ($rankingData as $mId => $tot) {
            if ($mId == $murid_id) return ['rank' => $rank, 'total_murid' => count($rankingData)];
            $rank++;
        }
        return ['rank' => '-', 'total_murid' => count($rankingData)];
    }

    private function hitungAbsensi($semester_id, $murid_id)
    {
        return [
            'sakit' => PresensiMurid::where('murid_id', $murid_id)->where('semester_id', $semester_id)->where('status', 'Sakit')->distinct('tanggal')->count('tanggal'),
            'izin' => PresensiMurid::where('murid_id', $murid_id)->where('semester_id', $semester_id)->where('status', 'Izin')->distinct('tanggal')->count('tanggal'),
            'alpha' => PresensiMurid::where('murid_id', $murid_id)->where('semester_id', $semester_id)->where('status', 'Alpha')->distinct('tanggal')->count('tanggal'),
            'pelanggaran' => PelanggaranMurid::where('murid_id', $murid_id)->where('semester_id', $semester_id)->count()
        ];
    }



    private function getPejabatPenandatangan($tingkat_id, $ruangan_id)
    {
        return [
            'pengasuh' => Pengurus::getAktifByJabatan('Pengasuh'),
            'kabid' => Pengurus::getAktifByJabatan('Kepala Bidang Pendidikan', $tingkat_id),
            'wali_ruangan' => $ruangan_id ? Ustadz::getTandaTanganByWaliRuangan($ruangan_id) : null
        ];
    }

    // Pembuat Array JSON
    private function susunJsonRapor($ujian, $murid, $ruangan, $nilaiMapel, $peringkat, $absensi, $riwayatKenaikan, $pejabat)
    {
        $matriksNilai = [];
        $total = 0;
        foreach ($nilaiMapel as $n) {
            $matriksNilai[] = [
                'mapel' => $n->jadwalUjian->mata_pelajaran_id ? ($n->jadwalUjian->mataPelajaran->nama_mapel ?? '-') : $n->jadwalUjian->nama_mata_pelajaran_custom,
                'nilai' => $n->nilai
            ];
            $total += $n->nilai;
        }

        return [
            'nomor_dokumen' => 'RAPOR/MDT-HS/' . now()->format('Y/m') . '/' . $murid->nism,
            'nama_ujian' => $ujian->nama_ujian,
            'tahun_pelajaran' => $ujian->tahunPelajaran->nama_hijriyah . ' H - ' . $ujian->tahunPelajaran->nama_masehi . ' M',
            'semester' => $ujian->semester_relasi->nama_semester ?? '-',
            'nama_ruangan' => $ruangan->nama_ruangan ?? '-',

            // DITAMBAHKAN KHUSUS UNTUK KEBUTUHAN CETAK BLADE:
            'nama_level'   => $ruangan->level->nama_level ?? '-',
            'nama_tingkat' => $ruangan->level->tingkat->nama_tingkat ?? 'MADRASAH',

            // DITAMBAHKAN: Membekukan Data Riwayat Kenaikan (Jika ada)
            'riwayat_kenaikan' => $riwayatKenaikan ? [
                // Menggunakan fallback (??) jika nama kolomnya beda (status_keputusan ATAU status_kenaikan)
                'status_keputusan'   => $riwayatKenaikan->status_keputusan ?? $riwayatKenaikan->status_kenaikan ?? null,
                'catatan_wali_kelas' => $riwayatKenaikan->catatan_wali_kelas ?? null,
                'nama_level_tujuan'  => $riwayatKenaikan->levelTujuan->nama_level ?? null,
            ] : null,

            'nism' => $murid->nism ?? '-',
            'nama_murid' => $murid->nama_lengkap,
            'nama_wali' => $murid->nama_ayah ?? '-',
            'nama_kampung' => $murid->waliMurid->kampung->nama_kampung ?? '-',
            'matriks_nilai' => $matriksNilai,
            'total_nilai' => $total,
            'rata_rata' => count($matriksNilai) > 0 ? round($total / count($matriksNilai), 2) : 0,
            'peringkat' => $peringkat['rank'],
            'dari_jumlah_murid' => $peringkat['total_murid'],
            'sakit' => $absensi['sakit'],
            'izin' => $absensi['izin'],
            'alpha' => $absensi['alpha'],
            'total_pelanggaran' => $absensi['pelanggaran'],

            'pengasuh_nama' => $pejabat['pengasuh']?->anggota?->nama_lengkap ?? '-',
            'pengasuh_id' => $pejabat['pengasuh']->id ?? null,
            'kabid_nama' => $pejabat['kabid']?->anggota?->nama_lengkap ?? '-',
            'kabid_id' => $pejabat['kabid']->id ?? null,
            'kabid_tingkat' => $pejabat['kabid']->tingkat->nama_tingkat ?? ($ruangan->level->tingkat->nama_tingkat ?? null),
            'wali_ruangan_nama' => $pejabat['wali_ruangan']['nama_lengkap'] ?? '-',
            'wali_ruangan_id' => $pejabat['wali_ruangan']['id'] ?? null,
            'tanggal_disahkan' => now()->format('Y-m-d')
        ];
    }

    private function getRiwayatKenaikan($ujian, $murid_id)
    {
        $namaSemester = strtolower($ujian->semester_relasi->nama_semester ?? '');
        $namaUjian    = strtolower($ujian->nama_ujian ?? '');

        // Samakan logikanya dengan file Blade (Cek kata 2, genap, ii, imda 2, imni)
        $isSemesterGenap = str_contains($namaSemester, '2') ||
            str_contains($namaSemester, 'genap') ||
            str_contains($namaSemester, 'ii') ||
            str_contains($namaUjian, 'imda 2') ||
            str_contains($namaUjian, 'imni');

        if ($isSemesterGenap) {
            return RiwayatKenaikan::with('levelTujuan')
                ->where('murid_id', $murid_id)
                ->where('tahun_pelajaran_id', $ujian->tahun_pelajaran_id)
                ->first();
        }

        return null;
    }

    /**
     * Membekukan data Ijazah Kelulusan Al-Qur'an ke Arsip Dokumen
     */
    public function arsipkanIjazahAlquran(\App\Models\UjianAlquran\PesertaUjianAlquran $peserta, $pengasuh = null, $juriPj = null, $administrator = null)
    {
        $peserta->loadMissing([
            'ujianAlquran.tahunPelajaran',
            'ujianAlquran.juris.ustadz',
            'murid.waliMurid.kampung',
            'ruangan.level.tingkat'
        ]);

        $ujian = $peserta->ujianAlquran;
        $murid = $peserta->murid;

        if (!$pengasuh) {
            $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        }

        if (!$juriPj) {
            $juriPj = $ujian->juris->firstWhere('is_penanggung_jawab', true)
                ?? $ujian->juris->firstWhere('peran_juri', 'Juri 1')
                ?? $ujian->juris->first();
        }

        $thn = $ujian->tahunPelajaran->nama_masehi ?? date('Y');
        $noIjazah = $peserta->no_ijazah ?? ("IJZ.QURAN/MDT-HS/{$thn}/" . str_pad($peserta->id, 4, '0', STR_PAD_LEFT));
        $noSk = $peserta->no_sk ?? (str_pad($peserta->id, 3, '0', STR_PAD_LEFT) . "/SK.QURAN/IBT/MDT-HS/" . date('m/Y'));

        if (empty($peserta->no_ijazah)) {
            $peserta->update(['no_ijazah' => $noIjazah, 'no_sk' => $noSk]);
        }

        $snapshotIjazah = [
            'tipe_ijazah'          => 'ijazah_alquran',
            'nomor_dokumen'        => $noIjazah,
            'nomor_ijazah_alquran' => $noIjazah,
            'nomor_sk_alquran'     => $noSk,
            'peserta_id'           => $peserta->id,
            'ujian_alquran_id'     => $ujian->id,
            'nama_ujian'           => $ujian->nama_ujian,
            'tahun_pelajaran'      => ($ujian->tahunPelajaran->nama_hijriyah ?? '-') . ' H - ' . ($ujian->tahunPelajaran->nama_masehi ?? '-') . ' M',
            'tahun_masehi'         => $ujian->tahunPelajaran->nama_masehi ?? date('Y'),
            'tahun_hijriyah'       => $ujian->tahunPelajaran->nama_hijriyah ?? '-',
            'nama_ruangan'         => $peserta->ruangan->nama_ruangan ?? '-',
            'nama_level'           => $peserta->ruangan->level->nama_level ?? '5 IBT',
            'lulus_dari_tingkat'   => 'IBTIDAIYAH (AL-QUR\'AN)',
            'nomor_peserta'        => $peserta->nomor_peserta ?? '-',
            'nism'                 => $murid->nism ?? '-',
            'nama_murid'           => $murid->nama_lengkap ?? '-',
            'tempat_lahir'         => $murid->tempat_lahir ?? '-',
            'tanggal_lahir'        => $murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->format('Y-m-d') : null,
            'tempat_tgl_lahir'     => ($murid->tempat_lahir ?? '-') . ', ' . ($murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-'),
            'nama_wali'            => $murid->waliMurid->nama_lengkap ?? ($murid->nama_ayah ?? '-'),
            'nilai_akhir'          => (float) $peserta->nilai_akhir,
            'predikat'             => $peserta->predikat,
            'predikat_arab'        => $peserta->predikat_arab,
            'jumlah_khoto_jali'    => (int) $peserta->jumlah_khoto_jali,
            'jumlah_khoto_khofi'   => (int) $peserta->jumlah_khoto_khofi,
            'total_pengurangan'    => (float) $peserta->total_pengurangan,
            'pengasuh_id'          => $pengasuh?->id,
            'pengasuh_nama'        => $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah'),
            'juri_pj_id'           => $juriPj?->ustadz_id,
            'juri_pj_nama'         => $juriPj?->ustadz?->nama_lengkap ?? 'Dewan Penguji',
            'juri_pj_peran'        => $juriPj ? $juriPj->peran_juri . ' • ' . $juriPj->kategori_juri : 'Dewan Penguji Al-Qur\'an',
            'tanggal_ujian'        => $ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : ($ujian->tanggal_pelaksanaan ? \Carbon\Carbon::parse($ujian->tanggal_pelaksanaan)->format('Y-m-d') : ($peserta->tanggal_lulus ? \Carbon\Carbon::parse($peserta->tanggal_lulus)->format('Y-m-d') : now()->format('Y-m-d'))),
            'tanggal_pelaksanaan'  => $ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : ($ujian->tanggal_pelaksanaan ? \Carbon\Carbon::parse($ujian->tanggal_pelaksanaan)->format('Y-m-d') : null),
            'tanggal_lulus'        => $peserta->tanggal_lulus ? \Carbon\Carbon::parse($peserta->tanggal_lulus)->format('Y-m-d') : ($ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : now()->format('Y-m-d')),
            'tanggal_disahkan'     => $peserta->tanggal_lulus ? \Carbon\Carbon::parse($peserta->tanggal_lulus)->format('Y-m-d') : ($ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : now()->format('Y-m-d')),
        ];

        return ArsipDokumen::updateOrCreate(
            [
                'tipe_dokumen'   => 'ijazah',
                'referensi_tipe' => get_class($murid),
                'referensi_id'   => $murid->id,
                'snapshot_data->tipe_ijazah' => 'ijazah_alquran',
            ],
            [
                'dicetak_oleh'  => Auth::id() ?? 1,
                'snapshot_data' => $snapshotIjazah,
            ]
        );
    }

    /**
     * Membekukan data Surat Keputusan (SK) Kelulusan Al-Qur'an ke Arsip Dokumen
     */
    public function arsipkanSkAlquran(\App\Models\UjianAlquran\PesertaUjianAlquran $peserta, $pengasuh = null, $kabid = null, $juriPj = null, $administrator = null)
    {
        $peserta->loadMissing([
            'ujianAlquran.tahunPelajaran',
            'ujianAlquran.juris.ustadz',
            'murid.waliMurid.kampung',
            'ruangan.level.tingkat'
        ]);

        $ujian = $peserta->ujianAlquran;
        $murid = $peserta->murid;

        if (!$pengasuh) {
            $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        }

        if (!$kabid) {
            $kabid = Pengurus::getAktifByJabatan('Kepala Bidang Pendidikan', $peserta->ruangan->level->tingkat_id ?? null)
                ?? Pengurus::getAktifByJabatan('Kepala Bidang Ibtidaiyah')
                ?? Pengurus::getAktifByJabatan('Kepala Bidang');
        }

        if (!$juriPj) {
            $juriPj = $ujian->juris->firstWhere('is_penanggung_jawab', true)
                ?? $ujian->juris->firstWhere('peran_juri', 'Juri 1')
                ?? $ujian->juris->first();
        }

        $thn = $ujian->tahunPelajaran->nama_masehi ?? date('Y');
        $noIjazah = $peserta->no_ijazah ?? ("IJZ.QURAN/MDT-HS/{$thn}/" . str_pad($peserta->id, 4, '0', STR_PAD_LEFT));
        $noSk = $peserta->no_sk ?? (str_pad($peserta->id, 3, '0', STR_PAD_LEFT) . "/SK.QURAN/IBT/MDT-HS/" . date('m/Y'));

        if (empty($peserta->no_sk)) {
            $peserta->update(['no_sk' => $noSk, 'no_ijazah' => $noIjazah]);
        }

        $snapshotSK = [
            'tipe_sk'              => 'sk_alquran',
            'nomor_dokumen'        => $noSk,
            'nomor_sk_alquran'     => $noSk,
            'nomor_ijazah_alquran' => $noIjazah,
            'peserta_id'           => $peserta->id,
            'ujian_alquran_id'     => $ujian->id,
            'nama_ujian'           => $ujian->nama_ujian,
            'tahun_pelajaran'      => ($ujian->tahunPelajaran->nama_hijriyah ?? '-') . ' H - ' . ($ujian->tahunPelajaran->nama_masehi ?? '-') . ' M',
            'tahun_masehi'         => $ujian->tahunPelajaran->nama_masehi ?? date('Y'),
            'tahun_hijriyah'       => $ujian->tahunPelajaran->nama_hijriyah ?? '-',
            'nama_ruangan'         => $peserta->ruangan->nama_ruangan ?? '-',
            'nama_level'           => $peserta->ruangan->level->nama_level ?? '5 IBT',
            'nomor_peserta'        => $peserta->nomor_peserta ?? '-',
            'nism'                 => $murid->nism ?? '-',
            'nama_murid'           => $murid->nama_lengkap ?? '-',
            'tempat_lahir'         => $murid->tempat_lahir ?? '-',
            'tanggal_lahir'        => $murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->format('Y-m-d') : null,
            'tempat_tgl_lahir'     => ($murid->tempat_lahir ?? '-') . ', ' . ($murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-'),
            'nama_wali'            => $murid->waliMurid->nama_lengkap ?? ($murid->nama_ayah ?? '-'),
            'nilai_akhir'          => (float) $peserta->nilai_akhir,
            'predikat'             => $peserta->predikat,
            'predikat_arab'        => $peserta->predikat_arab,
            'status_kelulusan'     => $peserta->status_kelulusan,
            'jumlah_khoto_jali'    => (int) $peserta->jumlah_khoto_jali,
            'jumlah_khoto_khofi'   => (int) $peserta->jumlah_khoto_khofi,
            'poin_pengurangan_jali'  => (float) $peserta->poin_pengurangan_jali,
            'poin_pengurangan_khofi' => (float) $peserta->poin_pengurangan_khofi,
            'total_pengurangan'    => (float) $peserta->total_pengurangan,
            'pengasuh_id'          => $pengasuh?->id,
            'pengasuh_nama'        => $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah'),
            'kabid_id'             => $kabid?->id,
            'kabid_nama'           => $kabid?->anggota?->nama_lengkap ?? ($kabid?->nama ?? 'Kepala Bidang'),
            'juri_pj_id'           => $juriPj?->ustadz_id,
            'juri_pj_nama'         => $juriPj?->ustadz?->nama_lengkap ?? 'Dewan Penguji',
            'admin_id'             => $administrator?->id,
            'admin_nama'           => $administrator?->nama_lengkap ?? 'Administrator',
            'tanggal_ujian'        => $ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : ($ujian->tanggal_pelaksanaan ? \Carbon\Carbon::parse($ujian->tanggal_pelaksanaan)->format('Y-m-d') : ($peserta->tanggal_lulus ? \Carbon\Carbon::parse($peserta->tanggal_lulus)->format('Y-m-d') : now()->format('Y-m-d'))),
            'tanggal_pelaksanaan'  => $ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : ($ujian->tanggal_pelaksanaan ? \Carbon\Carbon::parse($ujian->tanggal_pelaksanaan)->format('Y-m-d') : null),
            'tanggal_lulus'        => $peserta->tanggal_lulus ? \Carbon\Carbon::parse($peserta->tanggal_lulus)->format('Y-m-d') : ($ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : now()->format('Y-m-d')),
            'tanggal_disahkan'     => $peserta->tanggal_lulus ? \Carbon\Carbon::parse($peserta->tanggal_lulus)->format('Y-m-d') : ($ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : now()->format('Y-m-d')),
        ];

        return ArsipDokumen::updateOrCreate(
            [
                'tipe_dokumen'   => 'sk_keputusan',
                'referensi_tipe' => get_class($murid),
                'referensi_id'   => $murid->id,
                'snapshot_data->tipe_sk' => 'sk_alquran',
            ],
            [
                'dicetak_oleh'  => Auth::id() ?? 1,
                'snapshot_data' => $snapshotSK,
            ]
        );
    }
}
