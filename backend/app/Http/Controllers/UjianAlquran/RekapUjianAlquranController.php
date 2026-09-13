<?php

namespace App\Http\Controllers\UjianAlquran;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Level;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\UjianAlquran\PesertaUjianAlquran;
use App\Models\UjianAlquran\UjianAlquran;
use Illuminate\Http\Request;

class RekapUjianAlquranController extends Controller
{
    /**
     * Rekapitulasi Hasil Kelulusan Ujian Al-Qur'an
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $tahunPelajaranId = $request->tahun_id ?? $tahunAktif?->id;

        $ujian = UjianAlquran::with(['juris.ustadz', 'tahunPelajaran'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->first();

        // Urutan Ruangan: Level 5 IBT (5-A, 5-B, 5-C) terlebih dahulu, lalu Level 6 IBT (6-A, 6-B)
        $level5Ids = Level::where('nama_level', 'LIKE', '%5%IBT%')->orWhere('urutan_level', 8)->pluck('id');
        $level6Ids = Level::where('nama_level', 'LIKE', '%6%IBT%')->orWhere('urutan_level', 9)->pluck('id');

        $ruangans5 = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('level_id', $level5Ids)
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $ruangans6 = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('level_id', $level6Ids)
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $daftarRuangan = $ruangans5->concat($ruangans6);
        $selectedRuanganId = $request->ruangan_id;

        $pesertas = collect();
        $statistik = (object) [
            'total' => 0,
            'lulus' => 0,
            'tidak_lulus' => 0,
            'belum_diuji' => 0,
            'persen_lulus' => 0,
        ];

        if ($ujian) {
            $query = PesertaUjianAlquran::with(['murid.waliMurid.kampung', 'ruangan.level'])
                ->where('ujian_alquran_id', $ujian->id);

            if ($request->filled('ruangan_id')) {
                $query->where('ruangan_id', $request->ruangan_id);
            }

            if ($request->filled('status_kelulusan')) {
                $query->where('status_kelulusan', $request->status_kelulusan);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_peserta', 'LIKE', "%{$search}%")
                        ->orWhereHas('murid', function ($mq) use ($search) {
                            $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                ->orWhere('nism', 'LIKE', "%{$search}%");
                        });
                });
            }

            $pesertas = $query->orderBy('id', 'asc')->paginate(25)->withQueryString();
            $statistik = $ujian->statistik;
        }

        return view('ujian-alquran.rekap', compact(
            'ujian',
            'pesertas',
            'daftarRuangan',
            'daftarTahun',
            'tahunPelajaranId',
            'selectedRuanganId',
            'statistik'
        ));
    }

    /**
     * Cetak Lembar Ijazah Kelulusan Al-Qur'an (Per Murid)
     */
    public function cetakIjazah($pesertaId)
    {
        $peserta = PesertaUjianAlquran::with([
            'ujianAlquran.tahunPelajaran',
            'ujianAlquran.juris.ustadz',
            'murid.waliMurid.kampung',
            'ruangan.level'
        ])->findOrFail($pesertaId);

        if ($peserta->status_kelulusan !== 'Lulus') {
            return redirect()->back()->with('error', 'Ijazah hanya dapat dicetak untuk murid yang telah dinyatakan LULUS.');
        }

        $ujian = $peserta->ujianAlquran;
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $juriPj = $ujian->juris->firstWhere('is_penanggung_jawab', true)
            ?? $ujian->juris->firstWhere('peran_juri', 'Juri 1')
            ?? $ujian->juris->first();
        $administrator = auth()->user() ?? Administrator::first();

        // Pastikan nomor ijazah tersimpan di database
        if (empty($peserta->no_ijazah)) {
            $thn = $ujian->tahunPelajaran->nama_masehi ?? date('Y');
            $peserta->no_ijazah = "IJZ.QURAN/MDT-HS/{$thn}/" . str_pad($peserta->id, 4, '0', STR_PAD_LEFT);
            $peserta->save();
        }

        // Bekukan data ke Arsip Dokumen agar tidak berubah
        $arsip = app(\App\Services\ArsipService::class)->arsipkanIjazahAlquran($peserta, $pengasuh, $juriPj, $administrator);
        $data = $arsip->snapshot_data ?? [];

        return view('cetak-baru.cetak_ijazah_alquran', compact('peserta', 'ujian', 'pengasuh', 'juriPj', 'administrator', 'arsip', 'data'));
    }

    /**
     * Cetak Surat Keputusan (SK) Kelulusan Ujian Al-Qur'an (Per Murid)
     */
    public function cetakSk($pesertaId)
    {
        $peserta = PesertaUjianAlquran::with([
            'ujianAlquran.tahunPelajaran',
            'ujianAlquran.juris.ustadz',
            'murid.waliMurid.kampung',
            'ruangan.level'
        ])->findOrFail($pesertaId);

        if ($peserta->status_kelulusan === 'Belum Diuji') {
            return redirect()->back()->with('error', 'Surat Keputusan hanya dapat dicetak untuk murid yang telah diuji.');
        }

        $ujian = $peserta->ujianAlquran;
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $kabid = Pengurus::getAktifByJabatan('Kepala Bidang Pendidikan', $peserta->ruangan->level->tingkat_id ?? null)
            ?? Pengurus::getAktifByJabatan('Kepala Bidang Ibtidaiyah')
            ?? Pengurus::getAktifByJabatan('Kepala Bidang');
        $juriPj = $ujian->juris->firstWhere('is_penanggung_jawab', true)
            ?? $ujian->juris->firstWhere('peran_juri', 'Juri 1')
            ?? $ujian->juris->first();
        $administrator = auth()->user() ?? Administrator::first();

        return view('cetak-baru.cetak_sk_alquran', compact('peserta', 'ujian', 'pengasuh', 'kabid', 'juriPj', 'administrator'));
    }

    /**
     * Cetak Dokumen Rekapitulasi Hasil Ujian Al-Qur'an (Seluruh / Per Ruangan)
     */
    public function cetakRekap(Request $request)
    {
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $ujian = UjianAlquran::with(['juris.ustadz', 'tahunPelajaran'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->firstOrFail();

        $query = PesertaUjianAlquran::with(['murid.waliMurid.kampung', 'ruangan.level'])
            ->where('ujian_alquran_id', $ujian->id);

        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        if ($request->filled('status_kelulusan')) {
            $query->where('status_kelulusan', $request->status_kelulusan);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_peserta', 'LIKE', "%{$search}%")
                    ->orWhereHas('murid', function ($mq) use ($search) {
                        $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                            ->orWhere('nism', 'LIKE', "%{$search}%");
                    });
            });
        }

        $pesertas = $query->orderBy('nomor_peserta', 'asc')->get();
        $statistik = $ujian->statistik;
        $ruanganAktif = $request->filled('ruangan_id') ? Ruangan::find($request->ruangan_id) : null;
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $kabid = Pengurus::getAktifByJabatan('Kepala Bidang Pendidikan', null)
            ?? Pengurus::getAktifByJabatan('Kepala Bidang Ibtidaiyah');
        $juriPj = $ujian->juris->firstWhere('is_penanggung_jawab', true)
            ?? $ujian->juris->firstWhere('peran_juri', 'Juri 1')
            ?? $ujian->juris->first();
        $administrator = auth()->user() ?? Administrator::first();

        return view('cetak-baru.cetak_rekap_alquran', compact('ujian', 'pesertas', 'statistik', 'ruanganAktif', 'pengasuh', 'kabid', 'juriPj', 'administrator'));
    }
}
