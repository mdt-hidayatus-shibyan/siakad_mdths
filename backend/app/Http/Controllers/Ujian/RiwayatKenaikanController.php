<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Arsip\ArsipDokumen;
use App\Models\BulanHijriyah;
use App\Models\Level;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\PengaturanAkademik;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\RiwayatKenaikan;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use App\Services\KenaikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiwayatKenaikanController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;
        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->berdasarkanHakAkses()->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $dataKenaikan = collect();
        $isKelasAkhir = false;

        $daftarLevel = collect();

        // --- 1. AMBIL MASTER KONFIGURASI DARI DATABASE ---
        $config = PengaturanAkademik::where('tahun_pelajaran_id', $tahunPelajaranId)->first();

        $bobotUjian = ($config->bobot_imda ?? 60) / 100;
        $bobotHadir = ($config->bobot_presensi ?? 24) / 100;
        $bobotPelanggaran = ($config->bobot_pelanggaran ?? 16) / 100;

        $tarifAlpha = $config->poin_alpha ?? 1.00;
        $tarifIzin = $config->poin_izin ?? 0.16;

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
                $ruanganTerpilih->setRelation('murids', $murids);

                $levelNama = $ruanganTerpilih->level->nama_level ?? '';
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

                $levelSekarangId = null;
                $levelNaikId = null;

                if ($ruanganTerpilih->level_id) {
                    $levelSekarangId = $ruanganTerpilih->level_id;

                    // Ambil level saat ini dan setelahnya
                    $daftarLevel = Level::where('id', '>=', $levelSekarangId)
                        ->orderBy('id', 'asc')
                        ->get();

                    // Jika daftar level lebih dari 1, ambil index ke-1 (level berikutnya). 
                    // Jika sudah mentok, gunakan level sekarang.
                    $levelNaikId = $daftarLevel->count() > 1 ? $daftarLevel[1]->id : $levelSekarangId;
                }

                // Filter Ujian
                $semuaUjianTahunIni = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
                $idDauri1 = $semuaUjianTahunIni->where('tipe_ujian', 'IMDA 1')->pluck('id')->toArray();

                $idUjianSem2 = $isKelasAkhir
                    ? $semuaUjianTahunIni->where('tipe_ujian', 'IMNI')->pluck('id')->toArray()
                    : $semuaUjianTahunIni->where('tipe_ujian', 'IMDA 2')->pluck('id')->toArray();

                $semesters = \App\Models\Semester::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
                $sem1 = $semesters->first(fn($s) => str_contains($s->nama_semester, '1') || str_contains(strtolower($s->nama_semester), 'ganjil'));
                $sem2 = $semesters->first(fn($s) => str_contains($s->nama_semester, '2') || str_contains(strtolower($s->nama_semester), 'genap'));

                $semuaBulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('urutan', 'asc')->get();
                $bulanSem1 = $semuaBulanHijriyah->filter(fn($b) => $b->urutan <= 5);
                $bulanSem2 = $semuaBulanHijriyah->filter(fn($b) => $b->urutan > 5);

                $muridIds = $ruanganTerpilih->murids->pluck('id');
                $semuaPresensiKamar = PresensiMurid::whereIn('murid_id', $muridIds)->get();

                // 2. Ambil nilai & pelanggaran kamar sekaligus di luar perulangan
                $semuaNilaiKamar = NilaiUjian::whereIn('murid_id', $muridIds)
                    ->where('ruangan_id', $ruanganTerpilih->id)
                    ->get();

                $semuaPelanggaranKamar = PelanggaranMurid::with('referensiPelanggaran')
                    ->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('ruangan_id', $ruanganTerpilih->id)
                    ->get();

                foreach ($ruanganTerpilih->murids as $murid) {
                    $nilaiMurid = $semuaNilaiKamar->where('murid_id', $murid->id);

                    // Filter pelanggaran milik murid ini saja
                    $pelanggaranMuridIni = $semuaPelanggaranKamar->where('murid_id', $murid->id);
                    $presensiMuridIni = $semuaPresensiKamar->where('murid_id', $murid->id);

                    // =========================================================
                    // KALKULASI SEMESTER 1
                    // =========================================================
                    $rataUjian1 = $nilaiMurid->whereIn('ujian_id', $idDauri1)->avg('nilai') ?? 0;

                    $presensiSem1 = $presensiMuridIni->filter(function ($p) use ($sem1, $bulanSem1) {
                        return ($sem1 && $p->semester_id == $sem1->id)
                            || ($sem1 && $sem1->tanggal_mulai && $sem1->tanggal_selesai && $p->tanggal >= substr($sem1->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem1->tanggal_selesai, 0, 10))
                            || ($bulanSem1->isNotEmpty() && $bulanSem1->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });

                    // Hitung riil jumlah Alpha dan Izin Semester 1
                    $jumlahAlpha1 = $presensiSem1->where('status', 'Alpha')->count();
                    $jumlahIzin1 = $presensiSem1->where('status', 'Izin')->count();

                    $poinKehadiran1 = ($jumlahAlpha1 * $tarifAlpha) + ($jumlahIzin1 * $tarifIzin);

                    $pelanggaranSem1 = $pelanggaranMuridIni->filter(function ($p) use ($sem1, $bulanSem1) {
                        return ($sem1 && $sem1->tanggal_mulai && $sem1->tanggal_selesai && $p->tanggal >= substr($sem1->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem1->tanggal_selesai, 0, 10))
                            || ($bulanSem1->isNotEmpty() && $bulanSem1->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });
                    $poinPelanggaran1 = $pelanggaranSem1->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));

                    $nilaiHadir1 = max(0, ((15 - $poinKehadiran1) / 15) * 100);
                    $nilaiPelanggaran1 = max(0, ((30 - $poinPelanggaran1) / 30) * 100);
                    $skorSem1 = ($rataUjian1 * $bobotUjian) + ($nilaiHadir1 * $bobotHadir) + ($nilaiPelanggaran1 * $bobotPelanggaran);

                    // =========================================================
                    // KALKULASI SEMESTER 2
                    // =========================================================
                    $rataUjian2 = $nilaiMurid->whereIn('ujian_id', $idUjianSem2)->avg('nilai') ?? 0;

                    $presensiSem2 = $presensiMuridIni->filter(function ($p) use ($sem2, $bulanSem2) {
                        return ($sem2 && $p->semester_id == $sem2->id)
                            || ($sem2 && $sem2->tanggal_mulai && $sem2->tanggal_selesai && $p->tanggal >= substr($sem2->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem2->tanggal_selesai, 0, 10))
                            || ($bulanSem2->isNotEmpty() && $bulanSem2->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });

                    $jumlahAlpha2 = $presensiSem2->where('status', 'Alpha')->count();
                    $jumlahIzin2 = $presensiSem2->where('status', 'Izin')->count();

                    $poinKehadiran2 = ($jumlahAlpha2 * $tarifAlpha) + ($jumlahIzin2 * $tarifIzin);

                    $pelanggaranSem2 = $pelanggaranMuridIni->filter(function ($p) use ($sem2, $bulanSem2) {
                        return ($sem2 && $sem2->tanggal_mulai && $sem2->tanggal_selesai && $p->tanggal >= substr($sem2->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem2->tanggal_selesai, 0, 10))
                            || ($bulanSem2->isNotEmpty() && $bulanSem2->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });
                    $poinPelanggaran2 = $pelanggaranSem2->sum(fn($p) => (float)($p->referensiPelanggaran->poin ?? 0));

                    $nilaiHadir2 = max(0, ((15 - $poinKehadiran2) / 15) * 100);
                    $nilaiPelanggaran2 = max(0, ((30 - $poinPelanggaran2) / 30) * 100);
                    $skorSem2 = ($rataUjian2 * $bobotUjian) + ($nilaiHadir2 * $bobotHadir) + ($nilaiPelanggaran2 * $bobotPelanggaran);

                    // =========================================================
                    // KEPUTUSAN FINAL (MUTLAK DIBAGI 2 SEMESTER)
                    // =========================================================
                    $nilaiAkhir = round(($skorSem1 + $skorSem2) / 2, 2);

                    $rekomendasi = 'Tinggal Kelas';
                    if ($nilaiAkhir > 55) {
                        $rekomendasi = $isKelasAkhir ? 'Lulus' : 'Naik Kelas';
                    }

                    $riwayatExisting = RiwayatKenaikan::where('tahun_pelajaran_id', $tahunPelajaranId)
                        ->where('murid_id', $murid->id)->first();

                    $keputusanFinal = $riwayatExisting ? $riwayatExisting->status_keputusan : $rekomendasi;

                    if ($riwayatExisting) {
                        $levelTujuanId = $riwayatExisting->level_tujuan_id;
                    } else {
                        if ($keputusanFinal == 'Naik Kelas') {
                            $levelTujuanId = $levelNaikId;
                        } elseif ($keputusanFinal == 'Tinggal Kelas') {
                            $levelTujuanId = $levelSekarangId;
                        } else {
                            $levelTujuanId = null; // Jika Lulus
                        }
                    }

                    $dataKenaikan->push((object)[
                        'murid' => $murid,
                        'skor_sem1' => round($skorSem1, 2),
                        'skor_sem2' => round($skorSem2, 2),
                        'nilai_akumulasi' => $nilaiAkhir,
                        'rekomendasi' => $rekomendasi,
                        'keputusan_final' => $keputusanFinal,
                        'level_tujuan_id' => $levelTujuanId,
                        'catatan' => $riwayatExisting ? $riwayatExisting->catatan_wali_kelas : '',
                        'sudah_dikunci' => $riwayatExisting ? true : false,
                        'detail' => "Sem 1 (Ujian: " . round($rataUjian1, 1) . ", Hadir: " . round($nilaiHadir1, 1) . ", Lgg: " . round($poinPelanggaran1, 1) . ") | Sem 2 (Ujian: " . round($rataUjian2, 1) . ", Hadir: " . round($nilaiHadir2, 1) . ", Lgg: " . round($poinPelanggaran2, 1) . ")",
                        'detail_sem1' => [
                            'rata_ujian' => round($rataUjian1, 2),
                            'alpha' => $jumlahAlpha1,
                            'izin' => $jumlahIzin1,
                            'poin_hadir' => round($poinKehadiran1, 2),
                            'nilai_hadir' => round($nilaiHadir1, 2),
                            'poin_pelanggaran' => round($poinPelanggaran1, 2),
                            'nilai_pelanggaran' => round($nilaiPelanggaran1, 2),
                        ],
                        'detail_sem2' => [
                            'rata_ujian' => round($rataUjian2, 2),
                            'alpha' => $jumlahAlpha2,
                            'izin' => $jumlahIzin2,
                            'poin_hadir' => round($poinKehadiran2, 2),
                            'nilai_hadir' => round($nilaiHadir2, 2),
                            'poin_pelanggaran' => round($poinPelanggaran2, 2),
                            'nilai_pelanggaran' => round($nilaiPelanggaran2, 2),
                        ],
                    ]);
                }
            }
        }

        return view('kenaikan-kelas.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'daftarLevel',
            'ruanganTerpilih',
            'dataKenaikan',
            'isKelasAkhir',
            'config'
        ));
    }

    public function simpan(Request $request, KenaikanService $kenaikanService)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required',
            'ruangan_asal_id' => 'required',
            'keputusan' => 'required|array',
            'catatan' => 'nullable|array',
            'level_tujuan' => 'nullable|array'
        ]);

        $bulanRomawi = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI', '7' => 'VII', '8' => 'VIII', '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
        $bulanSekarang = $bulanRomawi[date('n')];
        $tahunSekarang = date('Y');

        $ruangan = Ruangan::with('level.tingkat')->find($request->ruangan_asal_id);
        $kodeTingkat = $ruangan && $ruangan->level && $ruangan->level->tingkat
            ? strtoupper($ruangan->level->tingkat->kode_tingkat)
            : 'MDT';

        DB::beginTransaction();
        try {
            $jumlahDiproses = 0;
            $jumlahLulus = 0; // Tambahan untuk menghitung yang lulus

            foreach ($request->keputusan as $muridId => $status) {

                $riwayatLama = RiwayatKenaikan::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                    ->where('murid_id', $muridId)
                    ->first();

                if ($riwayatLama && $riwayatLama->no_sk) {
                    $no_sk = $riwayatLama->no_sk;
                } else {
                    $noUrut = str_pad($muridId, 3, '0', STR_PAD_LEFT);
                    $no_sk = "{$noUrut}/SK/KEN-KEL/{$kodeTingkat}/MDT-HS/{$bulanSekarang}/{$tahunSekarang}";
                }

                $levelTujuan = ($status === 'Lulus') ? null : ($request->level_tujuan[$muridId] ?? null);

                // 1. Simpan ke Master Riwayat
                RiwayatKenaikan::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'murid_id' => $muridId,
                    ],
                    [
                        'ruangan_asal_id' => $request->ruangan_asal_id,
                        'level_tujuan_id' => $levelTujuan,
                        'no_sk' => $no_sk,
                        'nilai_akumulasi' => $request->nilai_akumulasi[$muridId] ?? 0,
                        'status_keputusan' => $status,
                        'catatan_wali_kelas' => $request->catatan[$muridId] ?? null,
                        'diputuskan_oleh' => Auth::id(),
                    ]
                );

                // 2. OTOMATISASI PENERBITAN E-DOCUMENT (SK & IJAZAH)
                // Layanan ini hanya akan membuat arsip jika syarat terpenuhi (Lulus & Kelas Akhir)
                $kenaikanService->terbitkanArsipSKdanIjazah(
                    $request->tahun_pelajaran_id,
                    $request->ruangan_asal_id,
                    $muridId,
                    $status,
                    $no_sk,
                    $request->nilai_akumulasi[$muridId] ?? 0 // 🔴 INI PARAMETER KE-6 YANG KURANG
                );

                if ($status === 'Lulus') $jumlahLulus++;
                $jumlahDiproses++;
            }

            DB::commit();

            // Buat pesan dinamis
            $pesan = "$jumlahDiproses status kelulusan/kenaikan murid berhasil dikunci.";
            if ($jumlahLulus > 0) {
                $pesan .= " (Berhasil menerbitkan arsip E-Document untuk $jumlahLulus murid lulus).";
            }

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan keputusan: ' . $e->getMessage());
        }
    }

    public function cetak_sk($tahun_id, $ruangan_id, $murid_id)
    {
        // Cari dokumen arsip SK yang sudah dibekukan untuk anak ini
        $arsip = ArsipDokumen::where('tipe_dokumen', 'sk_keputusan')
            ->where('referensi_tipe', Murid::class)
            ->where('referensi_id', $murid_id)
            ->first();

        if (!$arsip) {
            return back()->with('error', 'Dokumen SK belum disahkan/dibekukan. Silakan Sahkan Keputusan terlebih dahulu.');
        }

        $data = $arsip->snapshot_data;

        // dd($data);

        // Buka tampilan arsip SK statis
        return view('cetak-baru.cetak_sk_arsip', compact('arsip', 'data'));
    }

    public function cetak_ijazah($tahun_id, $ruangan_id, $murid_id)
    {
        // Cari dokumen arsip Ijazah yang sudah dibekukan untuk anak ini
        $arsip = ArsipDokumen::where('tipe_dokumen', 'ijazah')
            ->where('referensi_tipe', Murid::class)
            ->where('referensi_id', $murid_id)
            ->first();

        if (!$arsip) {
            return back()->with('error', 'Dokumen Ijazah belum disahkan atau murid tidak dinyatakan Lulus.');
        }

        $data = $arsip->snapshot_data;

        // Buka tampilan arsip Ijazah statis
        return view('cetak-baru.cetak_ijazah_arsip', compact('arsip', 'data'));
    }
}
