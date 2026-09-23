<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\PengecualianUjian;
use App\Models\Ujian\Ujian;
use Illuminate\Http\Request;

class BintangPelajarController extends Controller
{
    /**
     * HALAMAN BINTANG PELAJAR (PER TINGKAT LEVEL & RUANGAN KELAS)
     */
    public function bintangPelajar(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id
            ?? TahunPelajaran::where('is_active', true)->value('id')
            ?? $daftarTahun->first()?->id;

        $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();

        $ujianTerpilih = null;
        $bintangLevel = collect();
        $bintangRuangan = collect();

        if ($request->filled('ujian_id')) {
            $data = $this->ambilDataBintangPelajar($request->ujian_id);
            $ujianTerpilih = $data['ujianTerpilih'];
            $bintangLevel = $data['bintangLevel'];
            $bintangRuangan = $data['bintangRuangan'];
        }

        return view('bintang-pelajar.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarUjian',
            'ujianTerpilih',
            'bintangLevel',
            'bintangRuangan'
        ));
    }

    /**
     * Cetak Dokumen Rekap Pemenang Bintang Pelajar
     */
    public function cetak(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required|exists:ujians,id',
        ]);

        $data = $this->ambilDataBintangPelajar($request->ujian_id);
        $kategori = $request->kategori ?? 'semua'; // 'semua', 'level', 'ruangan'

        return view('cetak-baru.cetak_bintang_pelajar', array_merge($data, [
            'kategori' => $kategori,
        ]));
    }

    /**
     * Helper untuk mengambil data Bintang Pelajar (Level & Ruangan) terurut berdasarkan urutan_level
     */
    private function ambilDataBintangPelajar($ujianId)
    {
        $ujianTerpilih = Ujian::with('tahunPelajaran')->find($ujianId);
        if (!$ujianTerpilih) {
            return [
                'ujianTerpilih' => null,
                'bintangLevel' => collect(),
                'bintangRuangan' => collect(),
            ];
        }

        $pengecualianMuridIds = PengecualianUjian::where('ujian_id', $ujianTerpilih->id)->pluck('murid_id');

        $semuaNilai = NilaiUjian::with([
            'murid.waliMurid.kampung',
            'ruangan.level'
        ])
            ->where('ujian_id', $ujianTerpilih->id)
            ->whereNotIn('murid_id', $pengecualianMuridIds)
            ->whereHas('murid', function ($q) {
                $q->where('status', 'Aktif');
            })
            ->get();

        $rekapMurid = collect();
        foreach ($semuaNilai->groupBy('murid_id') as $muridId => $nilais) {
            $first = $nilais->first();
            $ruangan = $first->ruangan;
            $level = $ruangan?->level;
            $murid = $first->murid;

            $rekapMurid->push((object)[
                'murid' => $murid,
                'ruangan_id' => $ruangan?->id,
                'ruangan_nama' => $ruangan->nama_ruangan ?? 'Tanpa Ruangan',
                'level_id' => $level?->id,
                'level_nama' => $level->nama_level ?? 'Tanpa Tingkat',
                'urutan_level' => $level->urutan_level ?? 999,
                'total_nilai' => $nilais->sum('nilai'),
                'rata_rata' => $nilais->count() > 0 ? round($nilais->sum('nilai') / $nilais->count(), 2) : 0,
            ]);
        }

        // A. Top 3 Per Tingkatan/Level (Semua murid se-level diadu, diurutkan berdasarkan urutan_level)
        $bintangLevel = $rekapMurid->groupBy('level_nama')->map(function ($group) {
            return $group->sortByDesc('total_nilai')->take(3)->values();
        })->sortBy(function ($group) {
            return $group->first()->urutan_level ?? 999;
        });

        // B. Top 3 Per Ruangan (Hanya diadu dengan teman sekelas, diurutkan berdasarkan urutan_level kemudian nama_ruangan)
        $bintangRuangan = $rekapMurid->groupBy('ruangan_nama')->map(function ($group) {
            return $group->sortByDesc('total_nilai')->take(3)->values();
        })->sortBy(function ($group) {
            $first = $group->first();
            return sprintf('%04d_%s', $first->urutan_level ?? 999, $first->ruangan_nama ?? '');
        });

        return [
            'ujianTerpilih' => $ujianTerpilih,
            'bintangLevel' => $bintangLevel,
            'bintangRuangan' => $bintangRuangan,
        ];
    }
}
