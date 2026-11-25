<?php

namespace App\Http\Controllers\UjianAlquran;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Arsip\ArsipDokumen;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Level;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\UjianAlquran\PesertaUjianAlquran;
use App\Models\UjianAlquran\UjianAlquran;
use App\Services\ArsipService;
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
        $arsipIjazahMap = collect();
        $arsipSkMap = collect();
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

            // Ambil arsip Ijazah & SK Ujian Al-Qur'an yang sudah disahkan untuk peserta pada halaman ini
            $muridIds = $pesertas->pluck('murid_id')->filter()->unique()->values()->toArray();
            if (!empty($muridIds)) {
                $arsipAlquran = ArsipDokumen::where('referensi_tipe', Murid::class)
                    ->whereIn('referensi_id', $muridIds)
                    ->where(function ($q) {
                        $q->whereIn('tipe_dokumen', ['ijazah_alquran', 'sk_alquran'])
                            ->orWhere(function ($sub) {
                                $sub->where('tipe_dokumen', 'ijazah')
                                    ->where('snapshot_data->tipe_ijazah', 'ijazah_alquran');
                            })
                            ->orWhere(function ($sub) {
                                $sub->where('tipe_dokumen', 'sk_keputusan')
                                    ->where('snapshot_data->tipe_sk', 'sk_alquran');
                            });
                    })
                    ->orderBy('updated_at', 'desc')
                    ->get();

                foreach ($pesertas as $p) {
                    $ijz = $arsipAlquran->first(function ($a) use ($p, $ujian) {
                        $data = $a->snapshot_data ?? [];
                        $isIjz = $a->tipe_dokumen === 'ijazah_alquran' || ($data['tipe_ijazah'] ?? '') === 'ijazah_alquran';
                        if (!$isIjz || (int) $a->referensi_id !== (int) $p->murid_id) {
                            return false;
                        }
                        return isset($data['peserta_id'])
                            ? (int) $data['peserta_id'] === (int) $p->id
                            : (!isset($data['ujian_alquran_id']) || (int) $data['ujian_alquran_id'] === (int) $ujian->id);
                    });
                    if ($ijz) {
                        $arsipIjazahMap->put($p->id, $ijz);
                    }

                    $sk = $arsipAlquran->first(function ($a) use ($p, $ujian) {
                        $data = $a->snapshot_data ?? [];
                        $isSk = $a->tipe_dokumen === 'sk_alquran' || ($data['tipe_sk'] ?? '') === 'sk_alquran';
                        if (!$isSk || (int) $a->referensi_id !== (int) $p->murid_id) {
                            return false;
                        }
                        return isset($data['peserta_id'])
                            ? (int) $data['peserta_id'] === (int) $p->id
                            : (!isset($data['ujian_alquran_id']) || (int) $data['ujian_alquran_id'] === (int) $ujian->id);
                    });
                    if ($sk) {
                        $arsipSkMap->put($p->id, $sk);
                    }
                }
            }
        }

        return view('ujian-alquran.rekap', compact(
            'ujian',
            'pesertas',
            'arsipIjazahMap',
            'arsipSkMap',
            'daftarRuangan',
            'daftarTahun',
            'tahunPelajaranId',
            'selectedRuanganId',
            'statistik'
        ));
    }

    /**
     * Sahkan & Arsipkan Ijazah serta SK Ujian Al-Qur'an (Individu)
     */
    public function sahkan($pesertaId, ArsipService $arsipService)
    {
        try {
            $hasil = $arsipService->sahkanUjianAlquran((int) $pesertaId);
            $peserta = $hasil['peserta'];
            $nama = $peserta->murid->nama_lengkap ?? 'Murid';

            $pesan = $peserta->status_kelulusan === 'Lulus'
                ? "Ijazah & SK Ujian Al-Qur'an milik {$nama} berhasil disahkan dan ditambahkan ke Arsip Dokumen!"
                : "SK Hasil Ujian Al-Qur'an milik {$nama} berhasil disahkan dan ditambahkan ke Arsip Dokumen!";

            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengesahkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Sahkan & Arsipkan Ijazah serta SK Ujian Al-Qur'an secara Massal (Bulk)
     */
    public function sahkanBulk(Request $request, ArsipService $arsipService)
    {
        $request->validate([
            'selected_peserta' => 'required|array|min:1',
        ]);

        try {
            $berhasil = 0;
            $dilewati = 0;
            $jumlahIjazah = 0;
            $jumlahSk = 0;

            foreach ($request->selected_peserta as $pesertaId) {
                try {
                    $hasil = $arsipService->sahkanUjianAlquran((int) $pesertaId);
                    $berhasil++;
                    if (!empty($hasil['arsip_sk'])) {
                        $jumlahSk++;
                    }
                    if (!empty($hasil['arsip_ijazah'])) {
                        $jumlahIjazah++;
                    }
                } catch (\Exception $e) {
                    $dilewati++;
                }
            }

            $pesan = "Berhasil mengesahkan {$berhasil} peserta ke Arsip Dokumen ({$jumlahIjazah} Ijazah Al-Qur'an & {$jumlahSk} SK Al-Qur'an).";
            if ($dilewati > 0) {
                $pesan .= " ({$dilewati} peserta dilewati karena belum diuji).";
            }

            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengesahkan dokumen: ' . $e->getMessage());
        }
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

        // Jika dokumen sudah disahkan & masuk arsip, tampilkan dokumen beku dari Arsip Dokumen
        $arsip = ArsipDokumen::where('referensi_tipe', Murid::class)
            ->where('referensi_id', $peserta->murid_id)
            ->where(function ($q) {
                $q->where('tipe_dokumen', 'ijazah_alquran')
                    ->orWhere(function ($sub) {
                        $sub->where('tipe_dokumen', 'ijazah')
                            ->where('snapshot_data->tipe_ijazah', 'ijazah_alquran');
                    });
            })
            ->where(function ($q) use ($peserta) {
                $q->where('snapshot_data->peserta_id', $peserta->id)
                    ->orWhere('snapshot_data->ujian_alquran_id', $peserta->ujian_alquran_id);
            })
            ->first();

        if ($arsip) {
            return redirect()->route('arsip.cetak', $arsip->id);
        }

        $ujian = $peserta->ujianAlquran;
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $juriPj = $ujian->juris->firstWhere('is_penanggung_jawab', true)
            ?? $ujian->juris->firstWhere('peran_juri', 'Juri 1')
            ?? $ujian->juris->first();
        $administrator = auth()->user() ?? Administrator::first();

        // Pastikan nomor ijazah tersedia untuk pratinjau tanpa mengarsipkan sebelum disahkan
        if (empty($peserta->no_ijazah)) {
            $thn = $ujian->tahunPelajaran->nama_masehi ?? date('Y');
            $peserta->no_ijazah = "IJZ.QURAN/MDT-HS/{$thn}/" . str_pad($peserta->id, 4, '0', STR_PAD_LEFT);
            $peserta->save();
        }

        $data = [];

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

        // Jika dokumen sudah disahkan & masuk arsip, tampilkan dokumen beku dari Arsip Dokumen
        $arsip = ArsipDokumen::where('referensi_tipe', Murid::class)
            ->where('referensi_id', $peserta->murid_id)
            ->where(function ($q) {
                $q->where('tipe_dokumen', 'sk_alquran')
                    ->orWhere(function ($sub) {
                        $sub->where('tipe_dokumen', 'sk_keputusan')
                            ->where('snapshot_data->tipe_sk', 'sk_alquran');
                    });
            })
            ->where(function ($q) use ($peserta) {
                $q->where('snapshot_data->peserta_id', $peserta->id)
                    ->orWhere('snapshot_data->ujian_alquran_id', $peserta->ujian_alquran_id);
            })
            ->first();

        if ($arsip) {
            return redirect()->route('arsip.cetak', $arsip->id);
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
