<?php

namespace App\Services;

use App\Models\Level;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\UjianAlquran\PesertaUjianAlquran;
use App\Models\UjianAlquran\PenilaianJuriAlquran;
use App\Models\UjianAlquran\UjianAlquran;
use Illuminate\Support\Facades\DB;

class UjianAlquranService
{
    /**
     * Ambil kandidat murid per ruangan untuk Ujian Al-Qur'an.
     * Aturan:
     * - Murid berstatus 'Aktif' di ruangan & tahun pelajaran bersangkutan.
     * - JANGAN TAMPILKAN jika murid SUDAH TERDAFTAR di ujian ini.
     * - JANGAN TAMPILKAN jika murid SUDAH LULUS di ujian Al-Qur'an manapun (riwayat kelulusan).
     */
    public function getKandidatMuridByRuangan(int $ujianId, int $ruanganId, ?int $tahunId = null): \Illuminate\Support\Collection
    {
        $ujian = UjianAlquran::findOrFail($ujianId);
        $tahunId = $tahunId ?? $ujian->tahun_pelajaran_id;

        // Ambil seluruh murid aktif di ruangan ini pada tahun pelajaran bersangkutan
        $murids = Murid::where('status', 'Aktif')
            ->whereHas('ruangans', function ($q) use ($ruanganId, $tahunId) {
                $q->where('ruangans.id', $ruanganId)
                    ->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
            })
            ->orderBy('jenis_kelamin', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        // 1. ID murid yang sudah terdaftar di agenda ujian Al-Qur'an ini
        $sudahTerdaftarIds = PesertaUjianAlquran::where('ujian_alquran_id', $ujianId)
            ->pluck('murid_id')
            ->toArray();

        // 2. ID murid yang sudah LULUS di ujian Al-Qur'an manapun
        $sudahLulusIds = PesertaUjianAlquran::where('status_kelulusan', 'Lulus')
            ->pluck('murid_id')
            ->unique()
            ->toArray();

        $excludedIds = array_unique(array_merge($sudahTerdaftarIds, $sudahLulusIds));

        // Filter keluar murid yang sudah terdaftar atau sudah lulus
        return $murids->whereNotIn('id', $excludedIds)->map(function ($m) {
            return [
                'id'            => $m->id,
                'nama_lengkap'  => $m->nama_lengkap,
                'nism'          => $m->nism ?? '-',
                'nisn'          => $m->nisn ?? '-',
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
            ];
        })->values();
    }

    /**
     * Generate Nomor Peserta Ujian Al-Qur'an.
     * Format: UAQ<2 digit keikutsertaan><NISM>
     * Contoh:
     * - Keikutsertaan ke-1: UAQ011779
     * - Keikutsertaan ke-2 (jika belum lulus dan daftar lagi): UAQ021779
     */
    public function generateNomorPeserta(Murid $murid, ?int $currentUjianId = null): string
    {
        $query = PesertaUjianAlquran::where('murid_id', $murid->id);
        if ($currentUjianId) {
            $query->where('ujian_alquran_id', '!=', $currentUjianId);
        }
        $riwayatCount = $query->count();
        $keikutsertaan = $riwayatCount + 1;
        $attemptStr = str_pad($keikutsertaan, 2, '0', STR_PAD_LEFT);
        $nism = !empty($murid->nism) ? trim($murid->nism) : str_pad($murid->id, 4, '0', STR_PAD_LEFT);

        return "UAQ{$attemptStr}{$nism}";
    }

    /**
     * Tambahkan murid-murid terpilih sebagai peserta Ujian Al-Qur'an
     */
    public function tambahPeserta(int $ujianId, int $ruanganId, array $muridIds): array
    {
        $ujian = UjianAlquran::with('tahunPelajaran')->findOrFail($ujianId);

        // Filter validasi exclusion
        $sudahTerdaftarIds = PesertaUjianAlquran::where('ujian_alquran_id', $ujianId)
            ->pluck('murid_id')
            ->toArray();

        $sudahLulusIds = PesertaUjianAlquran::where('status_kelulusan', 'Lulus')
            ->pluck('murid_id')
            ->unique()
            ->toArray();

        $excludedIds = array_unique(array_merge($sudahTerdaftarIds, $sudahLulusIds));

        $validMurids = Murid::whereIn('id', $muridIds)
            ->whereNotIn('id', $excludedIds)
            ->where('status', 'Aktif')
            ->orderBy('jenis_kelamin', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        if ($validMurids->isEmpty()) {
            return ['ditambahkan' => 0];
        }

        $jumlahDitambahkan = 0;
        DB::beginTransaction();
        try {
            foreach ($validMurids as $m) {
                $nomorPeserta = $this->generateNomorPeserta($m, $ujianId);

                PesertaUjianAlquran::create([
                    'ujian_alquran_id'      => $ujianId,
                    'murid_id'              => $m->id,
                    'ruangan_id'            => $ruanganId,
                    'nomor_peserta'         => $nomorPeserta,
                    'jumlah_khoto_jali'     => 0,
                    'jumlah_khoto_khofi'    => 0,
                    'poin_pengurangan_jali' => 0,
                    'poin_pengurangan_khofi' => 0,
                    'total_pengurangan'     => 0,
                    'nilai_akhir'           => 100,
                    'status_kelulusan'      => 'Belum Diuji',
                ]);

                $jumlahDitambahkan++;
            }

            DB::commit();

            return [
                'ditambahkan' => $jumlahDitambahkan,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Menarik peserta otomatis untuk Ujian Al-Qur'an:
     * 1. Seluruh Murid aktif di Kelas 5 IBT pada tahun pelajaran ini.
     * 2. Murid aktif di Kelas 6 IBT pada tahun pelajaran ini yang BELUM LULUS ujian Al-Qur'an.
     */
    public function tarikPesertaOtomatis(int $ujianId): array
    {
        $ujian = UjianAlquran::with('tahunPelajaran')->findOrFail($ujianId);
        $tahunId = $ujian->tahun_pelajaran_id;

        // Cari level 5 IBT dan 6 IBT
        $level5Ibt = Level::where('nama_level', 'LIKE', '%5%IBT%')
            ->orWhere('urutan_level', 8)
            ->pluck('id');

        $level6Ibt = Level::where('nama_level', 'LIKE', '%6%IBT%')
            ->orWhere('urutan_level', 9)
            ->pluck('id');

        // 1. Ambil ruangan kelas 5 IBT di tahun pelajaran ini
        $ruanganKelas5 = Ruangan::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('level_id', $level5Ibt)
            ->pluck('id');

        // Murid aktif di kelas 5 IBT (seluruh murid aktif)
        $muridKelas5 = Murid::where('status', 'Aktif')
            ->whereHas('ruangans', function ($q) use ($ruanganKelas5, $tahunId) {
                $q->whereIn('ruangan_id', $ruanganKelas5)
                    ->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
            })
            ->with(['ruangans' => fn($q) => $q->whereIn('ruangan_id', $ruanganKelas5)->where('murid_ruangans.tahun_pelajaran_id', $tahunId)])
            ->get();

        // 2. Ambil ruangan kelas 6 IBT di tahun pelajaran ini
        $ruanganKelas6 = Ruangan::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('level_id', $level6Ibt)
            ->pluck('id');

        // Daftar ID Murid yang PERNAH LULUS di ujian Al-Qur'an manapun
        $muridSudahLulusIds = PesertaUjianAlquran::where('status_kelulusan', 'Lulus')
            ->pluck('murid_id')
            ->unique()
            ->toArray();

        // Murid aktif di kelas 6 IBT yang BELUM LULUS ujian Al-Qur'an
        $muridKelas6BelumLulus = Murid::where('status', 'Aktif')
            ->whereHas('ruangans', function ($q) use ($ruanganKelas6, $tahunId) {
                $q->whereIn('ruangan_id', $ruanganKelas6)
                    ->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
            })
            ->whereNotIn('id', $muridSudahLulusIds)
            ->with(['ruangans' => fn($q) => $q->whereIn('ruangan_id', $ruanganKelas6)->where('murid_ruangans.tahun_pelajaran_id', $tahunId)])
            ->get();

        $jumlahDitambahkan = 0;
        $jumlahSudahAda = 0;

        DB::beginTransaction();
        try {
            // Gabungkan kandidat peserta
            $kandidat = $muridKelas5->concat($muridKelas6BelumLulus);

            foreach ($kandidat as $m) {
                $exists = PesertaUjianAlquran::where('ujian_alquran_id', $ujianId)
                    ->where('murid_id', $m->id)
                    ->exists();

                if ($exists) {
                    $jumlahSudahAda++;
                    continue;
                }

                $nomorPeserta = $this->generateNomorPeserta($m, $ujianId);
                $ruanganId = $m->ruangans->first()?->id ?? null;

                PesertaUjianAlquran::create([
                    'ujian_alquran_id'      => $ujianId,
                    'murid_id'              => $m->id,
                    'ruangan_id'            => $ruanganId,
                    'nomor_peserta'         => $nomorPeserta,
                    'jumlah_khoto_jali'     => 0,
                    'jumlah_khoto_khofi'    => 0,
                    'poin_pengurangan_jali' => 0,
                    'poin_pengurangan_khofi' => 0,
                    'total_pengurangan'     => 0,
                    'nilai_akhir'           => 100,
                    'status_kelulusan'      => 'Belum Diuji',
                ]);

                $jumlahDitambahkan++;
            }

            DB::commit();

            return [
                'ditambahkan' => $jumlahDitambahkan,
                'sudah_ada'   => $jumlahSudahAda,
                'total'       => $kandidat->count(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hitung & simpan penilaian ujian Al-Qur'an berdasarkan algoritma:
     * - Khotho' Jali: bobot pengurangan per kesalahan (default 5 poin)
     * - Khotho' Khofi: bobot pengurangan per kesalahan (default 3 poin)
     * - Total Pengurangan = (bobot_jali * Jali) + (bobot_khofi * Khofi)
     * - Nilai Akhir = 100 - Total Pengurangan (minimal 0)
     * - Nilai Akhir >= KKM (default 55) -> Lulus, Nilai Akhir < KKM -> Tidak Lulus
     */
    public function hitungDanSimpanNilai(
        int $pesertaId,
        int $jumlahJali,
        int $jumlahKhofi,
        ?string $catatan = null,
        ?int $diujiOlehUserId = null
    ): PesertaUjianAlquran {
        $peserta = PesertaUjianAlquran::with('ujianAlquran.tahunPelajaran', 'murid')->findOrFail($pesertaId);
        $ujian = $peserta->ujianAlquran;

        $bobotJali = (float) ($ujian->bobot_jali ?? 5.00);
        $bobotKhofi = (float) ($ujian->bobot_khofi ?? 3.00);
        $kkm = (float) ($ujian->kkm_kelulusan ?? 55.00);

        // Perhitungan Pengurangan Poin
        $poinJali = $jumlahJali * $bobotJali;
        $poinKhofi = $jumlahKhofi * $bobotKhofi;
        $totalPengurangan = $poinJali + $poinKhofi;
        $nilaiAkhir = max(0.00, 100.00 - $totalPengurangan);

        // Penentuan Status Kelulusan (Nilai >= KKM = Lulus)
        $status = $nilaiAkhir >= $kkm ? 'Lulus' : 'Tidak Lulus';

        $bulanRomawi = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI', '7' => 'VII', '8' => 'VIII', '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
        $bln = $bulanRomawi[date('n')];
        $thn = date('Y');

        $noIjazah = $peserta->no_ijazah;
        $noSk = $peserta->no_sk;
        $tanggalLulus = $peserta->tanggal_lulus;

        if ($status === 'Lulus') {
            if (!$noIjazah) {
                $noIjazah = "IJZ.QURAN/MDT-HS/{$thn}/" . str_pad($peserta->id, 4, '0', STR_PAD_LEFT);
            }
            if (!$noSk) {
                $noSk = str_pad($peserta->id, 3, '0', STR_PAD_LEFT) . "/SK.QURAN/IBT/MDT-HS/{$bln}/{$thn}";
            }
            if (!$tanggalLulus) {
                $tanggalLulus = $ujian->tanggal_ujian ?? now();
            }
        }

        $peserta->update([
            'jumlah_khoto_jali'      => $jumlahJali,
            'jumlah_khoto_khofi'     => $jumlahKhofi,
            'poin_pengurangan_jali'  => $poinJali,
            'poin_pengurangan_khofi' => $poinKhofi,
            'total_pengurangan'      => $totalPengurangan,
            'nilai_akhir'            => $nilaiAkhir,
            'status_kelulusan'       => $status,
            'catatan_juri'           => $catatan,
            'no_ijazah'              => $noIjazah,
            'no_sk'                  => $noSk,
            'tanggal_lulus'          => $tanggalLulus,
            'diuji_oleh'             => $diujiOlehUserId ?? auth()->id(),
        ]);

        if ($status === 'Lulus') {
            try {
                app(\App\Services\ArsipService::class)->arsipkanIjazahAlquran($peserta);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal auto-arsip Ijazah Al-Qur\'an: ' . $e->getMessage());
            }
        }

        return $peserta;
    }
}
