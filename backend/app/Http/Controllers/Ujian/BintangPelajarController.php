<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\PresensiMurid;
use App\Models\TahunPelajaran;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\Ujian;
use Illuminate\Http\Request;

class BintangPelajarController extends Controller
{
    // =======================================================
    // 1. HALAMAN BINTANG PELAJAR (KELAS & LEVEL)
    // =======================================================
    public function bintangPelajar(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();

        $bintangLevel = collect();
        $bintangRuangan = collect();

        if ($request->filled('ujian_id')) {
            $ujianTerpilih = Ujian::find($request->ujian_id);
            $semuaNilai = NilaiUjian::with(['murid', 'ruangan.level'])
                ->where('ujian_id', $ujianTerpilih->id)
                ->whereHas('murid', function ($q) {
                    $q->where('status', 'Aktif');
                })
                ->get();

            $rekapMurid = collect();
            foreach ($semuaNilai->groupBy('murid_id') as $muridId => $nilais) {
                $rekapMurid->push((object)[
                    'murid' => $nilais->first()->murid,
                    'ruangan_nama' => $nilais->first()->ruangan->nama_ruangan ?? 'Tanpa Ruangan',
                    'level_nama' => $nilais->first()->ruangan->level->nama_level ?? 'Tanpa Tingkat',
                    'total_nilai' => $nilais->sum('nilai'),
                    'rata_rata' => $nilais->count() > 0 ? round($nilais->sum('nilai') / $nilais->count(), 2) : 0,
                ]);
            }

            // A. Top 3 Per Tingkatan/Level (Semua murid se-level diadu)
            $bintangLevel = $rekapMurid->groupBy('level_nama')->map(function ($group) {
                return $group->sortByDesc('total_nilai')->take(3)->values();
            })->sortKeys();

            // B. Top 3 Per Ruangan (Hanya diadu dengan teman sekelas)
            $bintangRuangan = $rekapMurid->groupBy('ruangan_nama')->map(function ($group) {
                return $group->sortByDesc('total_nilai')->take(3)->values();
            })->sortKeys();
        }

        return view('bintang-pelajar.index', compact('daftarTahun', 'tahunPelajaranId', 'daftarUjian', 'bintangLevel', 'bintangRuangan'));
    }

    // =======================================================
    // 2. HALAMAN BINTANG MADRASAH (UMUM)
    // =======================================================
    // =======================================================
    // 2. HALAMAN BINTANG MADRASAH (BEST OF THE BEST)
    // =======================================================
    public function bintangMadrasah(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        // Ambil data ujian IMDA 1 dan IMDA 2 pada tahun terpilih
        $ujianImda1 = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->where('tipe_ujian', 'IMDA 1')->first();
        $ujianImda2 = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->where('tipe_ujian', 'IMDA 2')->first();

        $bintangMadrasah = collect();

        // Syarat Pertama: IMDA 1 dan IMDA 2 harus sudah terlaksana
        if ($ujianImda1 && $ujianImda2) {

            // FUNGSI HELPER: Mencari ID Murid yang Juara 1 di tiap ruangan pada suatu Ujian (hanya murid aktif)
            $getJuara1PerRuangan = function ($ujianId) {
                $semuaNilai = NilaiUjian::where('ujian_id', $ujianId)
                    ->whereHas('murid', function ($query) {
                        $query->where('status', 'Aktif');
                    })
                    ->get();
                $juara1Ids = collect();

                // Kelompokkan per ruangan
                foreach ($semuaNilai->groupBy('ruangan_id') as $ruanganId => $nilaiPerRuang) {
                    $rekapMurid = collect();
                    // Hitung total nilai per murid di ruangan tersebut
                    foreach ($nilaiPerRuang->groupBy('murid_id') as $muridId => $nilais) {
                        $rekapMurid->push((object)[
                            'murid_id' => $muridId,
                            'total_nilai' => $nilais->sum('nilai'),
                        ]);
                    }
                    // Ambil 1 orang dengan total nilai tertinggi (Juara 1)
                    $juara1 = $rekapMurid->sortByDesc('total_nilai')->first();
                    if ($juara1) {
                        $juara1Ids->push($juara1->murid_id);
                    }
                }
                return $juara1Ids;
            };

            // Dapatkan daftar ID murid Juara 1 di IMDA 1 dan IMDA 2
            $juara1Imda1 = $getJuara1PerRuangan($ujianImda1->id);
            $juara1Imda2 = $getJuara1PerRuangan($ujianImda2->id);

            // SYARAT 1: Irisan (Intersect) -> Murid yang Juara 1 terus di IMDA 1 DAN IMDA 2
            $kandidatMuridIds = $juara1Imda1->intersect($juara1Imda2);

            // Process Pengumpulan Syarat 2, 3, dan 4 untuk para Kandidat
            $kandidatArrayIds = $kandidatMuridIds->toArray();

            if (!empty($kandidatArrayIds)) {
                // 1. Pre-fetch Data Murid (hanya yang aktif)
                $muridsMap = \App\Models\Murid::whereIn('id', $kandidatArrayIds)->where('status', 'Aktif')->get()->keyBy('id');

                // 2. Pre-fetch Ruangan lewat tabel pivot
                $penempatansMap = \Illuminate\Support\Facades\DB::table('murid_ruangans')
                    ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
                    ->leftJoin('levels', 'ruangans.level_id', '=', 'levels.id')
                    ->whereIn('murid_ruangans.murid_id', $kandidatArrayIds)
                    ->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId)
                    ->select('murid_ruangans.murid_id', 'ruangans.nama_ruangan', 'levels.nama_level')
                    ->get()
                    ->keyBy('murid_id');

                // 3. Pre-fetch Nilai Rata-rata Gabungan (IMDA 1 + IMDA 2)
                $nilaiKandidatMap = NilaiUjian::whereIn('ujian_id', [$ujianImda1->id, $ujianImda2->id])
                    ->whereIn('murid_id', $kandidatArrayIds)
                    ->get()
                    ->groupBy('murid_id');

                // 4. Pre-fetch Jumlah Kealpaan
                $alpaMap = PresensiMurid::whereIn('murid_id', $kandidatArrayIds)
                    ->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('status_kehadiran', 'Alpa')
                    ->selectRaw('murid_id, count(*) as total')
                    ->groupBy('murid_id')
                    ->pluck('total', 'murid_id');

                // 5. Pre-fetch Poin Pelanggaran
                $poinPelanggaranMap = PelanggaranMurid::whereIn('murid_id', $kandidatArrayIds)
                    ->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->selectRaw('murid_id, sum(poin) as total_poin')
                    ->groupBy('murid_id')
                    ->pluck('total_poin', 'murid_id');

                foreach ($kandidatMuridIds as $muridId) {
                    $murid = $muridsMap->get($muridId);
                    if (!$murid) continue;

                    $penempatan = $penempatansMap->get($muridId);
                    $ruanganNama = $penempatan ? $penempatan->nama_ruangan : '-';
                    $levelNama = $penempatan ? $penempatan->nama_level : '-';

                    $nilaiKandidat = $nilaiKandidatMap->get($muridId, collect());
                    $rataRata = $nilaiKandidat->count() > 0 ? round($nilaiKandidat->sum('nilai') / $nilaiKandidat->count(), 2) : 0;

                    $jumlahAlpa = $alpaMap->get($muridId, 0);
                    $poinPelanggaran = $poinPelanggaranMap->get($muridId, 0);

                    $bintangMadrasah->push((object)[
                        'murid' => $murid,
                        'ruangan_nama' => $ruanganNama,
                        'level_nama' => $levelNama,
                        'rata_rata' => $rataRata,
                        'jumlah_alpa' => $jumlahAlpa,
                        'poin_pelanggaran' => $poinPelanggaran
                    ]);
                }
            }

            // ==========================================================
            // PENGURUTAN BERTINGKAT (MULTI-LEVEL SORTING)
            // ==========================================================
            $bintangMadrasah = $bintangMadrasah->sortBy([
                ['rata_rata', 'desc'],        // Prioritas 1: Rata-rata Tertinggi
                ['jumlah_alpa', 'asc'],       // Prioritas 2: Alpa Terendah (asc = terkecil)
                ['poin_pelanggaran', 'asc'],  // Prioritas 3: Poin Pelanggaran Terendah (asc = terkecil)
            ])->take(3)->values();
        }
        return view('bintang-pelajar.madrasah', compact('daftarTahun', 'tahunPelajaranId', 'bintangMadrasah'));
    }
}
