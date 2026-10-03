<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Arsip\ArsipDokumen;
use App\Models\BulanHijriyah;
use App\Models\Level;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\PengaturanAkademik;
use App\Models\PresensiKegiatanMurid;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\KelulusanImni;
use App\Models\Ujian\NilaiUjian;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PesertaImni;
use App\Models\Ujian\RiwayatKenaikan;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use App\Services\KenaikanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KelulusanImniController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    /**
     * Halaman Utama Sidang Yudisium & Putusan Kelulusan Akhir (IMNI)
     * Khusus Kelas Akhir: 3 TPQ, 6 IBT, 3 TSA
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $tahunPelajaranId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $tahunPelajaranId) ?? $tahunAktif;

        // Tampilkan hanya ruangan khusus kelas akhir (3 TPQ, 6 IBT, 3 TSA)
        $daftarRuangan = Ruangan::with('level.tingkat')
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereHas('level', function ($q) {
                $q->whereIn('nama_level', ['3 TPQ', '6 IBT', '3 TSA']);
            })
            ->berdasarkanHakAkses()
            ->orderBy('id', 'asc')
            ->get();

        if ($daftarRuangan->isEmpty()) {
            $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
                ->whereHas('pesertaImnis')
                ->berdasarkanHakAkses()
                ->orderBy('id', 'asc')
                ->get();
        }

        $ruanganTerpilih = null;
        $dataKenaikan = collect();
        $isKelasAkhir = true; // Selalu kelas akhir di modul IMNI

        // --- 1. AMBIL MASTER KONFIGURASI DARI DATABASE ---
        $config = PengaturanAkademik::where('tahun_pelajaran_id', $tahunPelajaranId)->first();

        $bobotUjian = ($config->bobot_imda ?? 60) / 100;
        $bobotHadir = ($config->bobot_presensi ?? 24) / 100;
        $bobotPelanggaran = ($config->bobot_pelanggaran ?? 16) / 100;

        $tarifAlpha = (float) ($config->poin_alpha ?? 1.00);
        $tarifIzin = (float) ($config->poin_izin ?? 0.16);

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with(['level.tingkat', 'tahunPelajaran'])->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId);
                $ruanganTerpilih->setRelation('murids', $murids);

                // Filter Ujian: Semester 1 (IMDA 1) dan Semester 2 (IMNI)
                $semuaUjianTahunIni = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
                $idDauri1 = $semuaUjianTahunIni->where('tipe_ujian', 'IMDA 1')->pluck('id')->toArray();
                $idUjianSem2 = $semuaUjianTahunIni->where('tipe_ujian', 'IMNI')->pluck('id')->toArray();

                $semesters = Semester::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
                $sem1 = $semesters->first(fn($s) => str_contains($s->nama_semester, '1') || str_contains(strtolower($s->nama_semester), 'ganjil'));
                $sem2 = $semesters->first(fn($s) => str_contains($s->nama_semester, '2') || str_contains(strtolower($s->nama_semester), 'genap'));

                $semuaBulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('urutan', 'asc')->get();
                $bulanSem1 = $semuaBulanHijriyah->filter(fn($b) => $b->urutan <= 5);
                $bulanSem2 = $semuaBulanHijriyah->filter(fn($b) => $b->urutan > 5);

                $muridIds = $ruanganTerpilih->murids->pluck('id');
                $semuaPresensiKBM = PresensiMurid::whereIn('murid_id', $muridIds)->get();
                $semuaPresensiKegiatan = PresensiKegiatanMurid::whereIn('murid_id', $muridIds)
                    ->where('ruangan_id', $ruanganTerpilih->id)
                    ->get();

                $semuaNilaiKamar = NilaiUjian::whereIn('murid_id', $muridIds)
                    ->where('ruangan_id', $ruanganTerpilih->id)
                    ->get();

                $semuaPelanggaranKamar = PelanggaranMurid::with('referensiPelanggaran')
                    ->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('ruangan_id', $ruanganTerpilih->id)
                    ->get();

                $semuaKelulusan = KelulusanImni::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->whereIn('murid_id', $muridIds)
                    ->get()
                    ->keyBy('murid_id');

                $semuaRiwayat = RiwayatKenaikan::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->whereIn('murid_id', $muridIds)
                    ->get()
                    ->keyBy('murid_id');

                $semuaPesertaImni = PesertaImni::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->whereIn('murid_id', $muridIds)
                    ->get()
                    ->keyBy('murid_id');

                $semuaArsipSK = ArsipDokumen::where('tipe_dokumen', 'sk_keputusan')
                    ->where('referensi_tipe', Murid::class)
                    ->whereIn('referensi_id', $muridIds)
                    ->get()
                    ->keyBy('referensi_id');

                $semuaArsipIjazah = ArsipDokumen::where('tipe_dokumen', 'ijazah')
                    ->where('referensi_tipe', Murid::class)
                    ->whereIn('referensi_id', $muridIds)
                    ->get()
                    ->keyBy('referensi_id');

                foreach ($ruanganTerpilih->murids as $murid) {
                    $nilaiMurid = $semuaNilaiKamar->where('murid_id', $murid->id);
                    $pelanggaranMuridIni = $semuaPelanggaranKamar->where('murid_id', $murid->id);
                    $presensiMuridIni = $semuaPresensiKBM->where('murid_id', $murid->id);
                    $presensiKegiatanMuridIni = $semuaPresensiKegiatan->where('murid_id', $murid->id);

                    // =========================================================
                    // KALKULASI SEMESTER 1 (IMDA 1)
                    // =========================================================
                    $rataUjian1 = $nilaiMurid->whereIn('ujian_id', $idDauri1)->avg('nilai') ?? 0;

                    $presensiSem1 = $presensiMuridIni->filter(function ($p) use ($sem1, $bulanSem1) {
                        return ($sem1 && $p->semester_id == $sem1->id)
                            || ($sem1 && $sem1->tanggal_mulai && $sem1->tanggal_selesai && $p->tanggal >= substr($sem1->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem1->tanggal_selesai, 0, 10))
                            || ($bulanSem1->isNotEmpty() && $bulanSem1->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });

                    $presensiKegiatanSem1 = $presensiKegiatanMuridIni->filter(function ($p) use ($sem1, $bulanSem1) {
                        return ($sem1 && $sem1->tanggal_mulai && $sem1->tanggal_selesai && $p->tanggal >= substr($sem1->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem1->tanggal_selesai, 0, 10))
                            || ($bulanSem1->isNotEmpty() && $bulanSem1->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });

                    $jumlahAlpha1 = $presensiSem1->where('status', 'Alpha')->count() + $presensiKegiatanSem1->where('status', 'Alpha')->count();
                    $jumlahIzin1 = $presensiSem1->where('status', 'Izin')->count() + $presensiKegiatanSem1->where('status', 'Izin')->count();
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
                    // KALKULASI SEMESTER 2 (IMNI)
                    // =========================================================
                    $rataUjian2 = $nilaiMurid->whereIn('ujian_id', $idUjianSem2)->avg('nilai') ?? 0;

                    $presensiSem2 = $presensiMuridIni->filter(function ($p) use ($sem2, $bulanSem2) {
                        return ($sem2 && $p->semester_id == $sem2->id)
                            || ($sem2 && $sem2->tanggal_mulai && $sem2->tanggal_selesai && $p->tanggal >= substr($sem2->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem2->tanggal_selesai, 0, 10))
                            || ($bulanSem2->isNotEmpty() && $bulanSem2->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });

                    $presensiKegiatanSem2 = $presensiKegiatanMuridIni->filter(function ($p) use ($sem2, $bulanSem2) {
                        return ($sem2 && $sem2->tanggal_mulai && $sem2->tanggal_selesai && $p->tanggal >= substr($sem2->tanggal_mulai, 0, 10) && $p->tanggal <= substr($sem2->tanggal_selesai, 0, 10))
                            || ($bulanSem2->isNotEmpty() && $bulanSem2->contains(fn($b) => $p->tanggal >= $b->tanggal_mulai_masehi && $p->tanggal <= $b->tanggal_selesai_masehi));
                    });

                    $jumlahAlpha2 = $presensiSem2->where('status', 'Alpha')->count() + $presensiKegiatanSem2->where('status', 'Alpha')->count();
                    $jumlahIzin2 = $presensiSem2->where('status', 'Izin')->count() + $presensiKegiatanSem2->where('status', 'Izin')->count();
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
                    // KEPUTUSAN FINAL (NILAI AKHIR AKUMULASI 2 SEMESTER)
                    // =========================================================
                    $nilaiAkhir = round(($skorSem1 + $skorSem2) / 2, 2);
                    $rekomendasi = ($nilaiAkhir > 55) ? 'Lulus' : 'Tinggal Kelas';

                    $kelExisting = $semuaKelulusan->get($murid->id);
                    $riwayatExisting = $semuaRiwayat->get($murid->id);
                    $pesertaExisting = $semuaPesertaImni->get($murid->id);

                    $isLocked = ($kelExisting && $kelExisting->is_locked) || ($riwayatExisting != null);
                    $keputusanFinal = $kelExisting?->status_kelulusan ?? ($riwayatExisting?->status_keputusan ?? $rekomendasi);
                    if (!in_array($keputusanFinal, ['Lulus', 'Tinggal Kelas'])) {
                        $keputusanFinal = ($keputusanFinal === 'Tidak Lulus') ? 'Tinggal Kelas' : 'Lulus';
                    }

                    $catatan = $kelExisting?->catatan_yudisium ?? ($riwayatExisting?->catatan_wali_kelas ?? '');

                    $hasArsipSK = $semuaArsipSK->has($murid->id);
                    $hasArsipIjazah = $semuaArsipIjazah->has($murid->id);

                    $dataKenaikan->push((object)[
                        'murid' => $murid,
                        'peserta_imni' => $pesertaExisting,
                        'skor_sem1' => round($skorSem1, 2),
                        'skor_sem2' => round($skorSem2, 2),
                        'nilai_akumulasi' => $nilaiAkhir,
                        'rekomendasi' => $rekomendasi,
                        'keputusan_final' => $keputusanFinal,
                        'catatan' => $catatan,
                        'sudah_dikunci' => $isLocked,
                        'has_arsip_sk' => $hasArsipSK,
                        'has_arsip_ijazah' => $hasArsipIjazah,
                        'kelulusan' => $kelExisting,
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

        return view('ujian.panitia-imni.kelulusan.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'selectedTahun',
            'daftarRuangan',
            'ruanganTerpilih',
            'dataKenaikan',
            'isKelasAkhir',
            'config'
        ));
    }

    /**
     * Sahkan & Simpan Keputusan Kelulusan Akhir (IMNI)
     * Mengunci data akademik permanen serta menerbitkan Arsip E-Document (SK & Ijazah)
     */
    public function simpan(Request $request, KenaikanService $kenaikanService)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required',
            'ruangan_asal_id'    => 'required',
            'keputusan'          => 'required|array',
            'catatan'            => 'nullable|array',
            'nilai_akumulasi'    => 'required|array',
        ]);

        $bulanRomawi = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI', '7' => 'VII', '8' => 'VIII', '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
        $bulanSekarang = $bulanRomawi[date('n')];
        $tahunSekarang = date('Y');

        $ruangan = Ruangan::with(['level.tingkat', 'tahunPelajaran'])->findOrFail($request->ruangan_asal_id);
        $kodeTingkat = $ruangan && $ruangan->level && $ruangan->level->tingkat
            ? strtoupper($ruangan->level->tingkat->kode_tingkat)
            : 'MDT';

        DB::beginTransaction();
        try {
            $jumlahDiproses = 0;
            $jumlahLulus = 0;

            foreach ($request->keputusan as $muridId => $status) {
                $statusKeputusan = ($status === 'Lulus') ? 'Lulus' : 'Tinggal Kelas';

                // Check existing numbers
                $riwayatLama = RiwayatKenaikan::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                    ->where('murid_id', $muridId)
                    ->first();

                $kelulusanLama = KelulusanImni::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                    ->where('murid_id', $muridId)
                    ->first();

                if ($riwayatLama && $riwayatLama->no_sk) {
                    $no_sk = $riwayatLama->no_sk;
                } elseif ($kelulusanLama && $kelulusanLama->nomor_sk_lulus) {
                    $no_sk = $kelulusanLama->nomor_sk_lulus;
                } else {
                    $noUrut = str_pad($muridId, 3, '0', STR_PAD_LEFT);
                    $no_sk = "{$noUrut}/SK/KEL-IMNI/{$kodeTingkat}/MDT-HS/{$bulanSekarang}/{$tahunSekarang}";
                }

                $nilaiAkumulasi = (float) ($request->nilai_akumulasi[$muridId] ?? 0);
                $catatan = $request->catatan[$muridId] ?? null;

                // 1. Simpan ke Riwayat Kenaikan (Master Akademik Madrasah)
                RiwayatKenaikan::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'murid_id'           => $muridId,
                    ],
                    [
                        'ruangan_asal_id'    => $request->ruangan_asal_id,
                        'level_tujuan_id'    => ($statusKeputusan === 'Lulus') ? null : $ruangan->level_id,
                        'no_sk'              => $no_sk,
                        'nilai_akumulasi'    => $nilaiAkumulasi,
                        'status_keputusan'   => $statusKeputusan,
                        'catatan_wali_kelas' => $catatan,
                        'diputuskan_oleh'    => Auth::id(),
                    ]
                );

                // 2. Simpan ke Kelulusan IMNI
                $pesertaImni = PesertaImni::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                    ->where('murid_id', $muridId)
                    ->first();

                $nomorIjazah = null;
                if ($statusKeputusan === 'Lulus') {
                    $nomorIjazah = $kelulusanLama?->nomor_ijazah ?? ('IJZ/MDT-HS/' . date('Y/m') . '/' . str_pad($muridId, 4, '0', STR_PAD_LEFT));
                }

                KelulusanImni::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'murid_id'           => $muridId,
                    ],
                    [
                        'peserta_imni_id'    => $pesertaImni?->id,
                        'tingkat_id'         => $ruangan->level->tingkat_id ?? null,
                        'level_id'           => $ruangan->level_id,
                        'nilai_akhir'        => $nilaiAkumulasi,
                        'status_kelulusan'   => $statusKeputusan,
                        'nomor_sk_lulus'     => $no_sk,
                        'nomor_ijazah'       => $nomorIjazah,
                        'catatan_yudisium'   => $catatan,
                        'is_locked'          => true,
                        'locked_at'          => now(),
                        'locked_by'          => Auth::id(),
                    ]
                );

                // 3. Terbitkan Arsip E-Document SK & Ijazah
                $kenaikanService->terbitkanArsipSKdanIjazah(
                    $request->tahun_pelajaran_id,
                    $request->ruangan_asal_id,
                    $muridId,
                    $statusKeputusan,
                    $no_sk,
                    $nilaiAkumulasi
                );

                if ($statusKeputusan === 'Lulus') $jumlahLulus++;
                $jumlahDiproses++;
            }

            DB::commit();

            $pesan = "{$jumlahDiproses} keputusan kelulusan murid berhasil disahkan dan dikunci.";
            if ($jumlahLulus > 0) {
                $pesan .= " (Berhasil menerbitkan arsip SK & Ijazah untuk {$jumlahLulus} murid lulus).";
            }

            return redirect()->back()->with('success', $pesan);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengesahkan keputusan: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Arsip Surat Keputusan (SK) Kelulusan
     */
    public function cetak_sk(Request $request, $tahun_id = null, $ruangan_id = null, $murid_id = null)
    {
        $tahunId = $tahun_id ?? $request->input('tahun_id');
        $ruanganId = $ruangan_id ?? $request->input('ruangan_id');
        $muridId = $murid_id ?? $request->input('murid_id');

        if ($muridId) {
            $arsip = ArsipDokumen::where('tipe_dokumen', 'sk_keputusan')
                ->where('referensi_tipe', Murid::class)
                ->where('referensi_id', $muridId)
                ->first();

            if (!$arsip) {
                $murid = Murid::with('waliMurid.kampung')->find($muridId);
                $kel = KelulusanImni::where('murid_id', $muridId)->first();
                $ruangan = $ruanganId ? Ruangan::with(['level.tingkat', 'tahunPelajaran'])->find($ruanganId) : null;

                if (!$murid) {
                    return back()->with('error', 'Data murid tidak ditemukan.');
                }

                $data = [
                    'nomor_dokumen'      => $kel?->nomor_sk_lulus ?? '001/SK/KEL-IMNI/MDT-HS/' . date('Y'),
                    'tahun_pelajaran'    => $ruangan?->tahunPelajaran ? ($ruangan->tahunPelajaran->nama_hijriyah . ' H - ' . $ruangan->tahunPelajaran->nama_masehi . ' M') : date('Y'),
                    'nama_murid'         => $murid->nama_lengkap,
                    'nism'               => $murid->nism ?? '-',
                    'nama_ruangan'       => $ruangan?->nama_ruangan ?? '-',
                    'nilai_akumulasi'    => $kel?->nilai_akhir ?? '-',
                    'tempat_tgl_lahir'   => ($murid->tempat_lahir ?? '-') . ', ' . ($murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-'),
                    'nama_wali'          => $murid->waliMurid->nama_ayah ?? ($murid->nama_ayah ?? '-'),
                    'lulus_dari_tingkat' => $ruangan?->level?->tingkat?->nama_tingkat ?? 'MADRASAH',
                    'status_keputusan'   => $kel?->status_kelulusan ?? 'Lulus',
                    'status_kelulusan'   => $kel?->status_kelulusan ?? 'Lulus',
                    'tanggal_disahkan'   => now()->format('Y-m-d')
                ];
                $arsip = null;
            } else {
                $data = $arsip->snapshot_data;
            }

            return view('cetak-baru.cetak_sk_arsip', compact('arsip', 'data'));
        }

        // Cetak SK Massal Panitia
        return $this->cetakSkPanitia($request);
    }

    /**
     * Cetak Arsip Ijazah Kelulusan Akhir
     */
    public function cetak_ijazah(Request $request, $tahun_id = null, $ruangan_id = null, $murid_id = null)
    {
        $tahunId = $tahun_id ?? $request->input('tahun_id');
        $ruanganId = $ruangan_id ?? $request->input('ruangan_id');
        $muridId = $murid_id ?? $request->input('murid_id');

        if ($muridId) {
            $arsip = ArsipDokumen::where('tipe_dokumen', 'ijazah')
                ->where('referensi_tipe', Murid::class)
                ->where('referensi_id', $muridId)
                ->first();

            if (!$arsip) {
                $murid = Murid::with('waliMurid.kampung')->find($muridId);
                $kel = KelulusanImni::where('murid_id', $muridId)->first();
                $ruangan = $ruanganId ? Ruangan::with(['level.tingkat', 'tahunPelajaran'])->find($ruanganId) : null;

                if (!$murid) {
                    return back()->with('error', 'Data murid tidak ditemukan.');
                }

                $nomorIjazah = $kel?->nomor_ijazah ?? ('IJZ/MDT-HS/' . date('Y/m') . '/' . str_pad($muridId, 4, '0', STR_PAD_LEFT));

                $data = [
                    'nomor_dokumen'      => $nomorIjazah,
                    'tahun_pelajaran'    => $ruangan?->tahunPelajaran ? ($ruangan->tahunPelajaran->nama_hijriyah . ' H - ' . $ruangan->tahunPelajaran->nama_masehi . ' M') : date('Y'),
                    'nama_murid'         => $murid->nama_lengkap,
                    'nism'               => $murid->nism ?? '-',
                    'nama_ruangan'       => $ruangan?->nama_ruangan ?? '-',
                    'nilai_akumulasi'    => $kel?->nilai_akhir ?? '-',
                    'tempat_tgl_lahir'   => ($murid->tempat_lahir ?? '-') . ', ' . ($murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-'),
                    'nama_wali'          => $murid->waliMurid->nama_ayah ?? ($murid->nama_ayah ?? '-'),
                    'lulus_dari_tingkat' => $ruangan?->level?->tingkat?->nama_tingkat ?? 'MADRASAH',
                    'status_keputusan'   => $kel?->status_kelulusan ?? 'Lulus',
                    'status_kelulusan'   => $kel?->status_kelulusan ?? 'Lulus',
                    'tanggal_disahkan'   => now()->format('Y-m-d')
                ];
                $arsip = null;
            } else {
                $data = $arsip->snapshot_data;
            }

            return view('cetak-baru.cetak_ijazah_arsip', compact('arsip', 'data'));
        }

        return back()->with('error', 'Murid ID tidak ditentukan.');
    }

    /**
     * Cetak Transkrip Nilai Gabungan IMNI (Mapel Teori + Akumulasi)
     */
    public function cetakTranskrip(Request $request, $pesertaId = null)
    {
        $pId = $pesertaId ?? $request->input('peserta_id');
        $peserta = PesertaImni::with([
            'murid.waliMurid.kampung',
            'tingkat',
            'level',
            'tahunPelajaran',
            'kelulusan',
        ])->findOrFail($pId);

        $selectedTahun = $peserta->tahunPelajaran;

        // Ambil seluruh mapel IMNI untuk level murid
        $imniUjianIds = Ujian::where('tahun_pelajaran_id', $selectedTahun->id)
            ->where('tipe_ujian', 'IMNI')
            ->pluck('id');

        $jadwals = JadwalUjian::with('mataPelajaran')
            ->whereIn('ujian_id', $imniUjianIds)
            ->where('level_id', $peserta->level_id)
            ->get();

        // Nilai teori per mapel
        $nilaiTeori = NilaiUjian::whereIn('jadwal_ujian_id', $jadwals->pluck('id'))
            ->where('murid_id', $peserta->murid_id)
            ->get()
            ->keyBy('jadwal_ujian_id');

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahun->id);

        return view('ujian.panitia-imni.kelulusan.cetak-transkrip', compact(
            'peserta',
            'selectedTahun',
            'jadwals',
            'nilaiTeori',
            'ketuaPanitia'
        ));
    }

    /**
     * Cetak Leger Yudisium Kelulusan Akhir
     */
    public function cetakLeger(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $tingkatId = $request->input('tingkat_id');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $query = KelulusanImni::with([
            'murid',
            'peserta.ruanganUjian',
            'tingkat',
            'level'
        ])
            ->join('levels', 'kelulusan_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'kelulusan_imnis.murid_id', '=', 'murids.id')
            ->where('kelulusan_imnis.tahun_pelajaran_id', $selectedTahunId)
            ->select('kelulusan_imnis.*');

        if ($tingkatId) {
            $query->where('kelulusan_imnis.tingkat_id', $tingkatId);
        }

        $kelulusans = $query->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.kelulusan.cetak-leger', compact(
            'selectedTahun',
            'kelulusans',
            'ketuaPanitia'
        ));
    }

    /**
     * Cetak SK Panitia Massal Lampiran
     */
    public function cetakSkPanitia(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $tingkatId = $request->input('tingkat_id');

        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $query = KelulusanImni::with([
            'murid.waliMurid',
            'peserta',
            'tingkat',
            'level'
        ])
            ->join('levels', 'kelulusan_imnis.level_id', '=', 'levels.id')
            ->join('murids', 'kelulusan_imnis.murid_id', '=', 'murids.id')
            ->where('kelulusan_imnis.tahun_pelajaran_id', $selectedTahunId)
            ->select('kelulusan_imnis.*');

        if ($tingkatId) {
            $query->where('kelulusan_imnis.tingkat_id', $tingkatId);
        }

        $kelulusans = $query->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.jenis_kelamin', 'asc')
            ->orderBy('murids.nama_lengkap', 'asc')
            ->get();

        $lulusList = $kelulusans->whereIn('status_kelulusan', ['Lulus', 'Lulus Murni', 'Lulus Bersyarat']);
        $tidakLulusList = $kelulusans->where('status_kelulusan', 'Tidak Lulus');

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);

        return view('ujian.panitia-imni.kelulusan.cetak-sk', compact(
            'selectedTahun',
            'kelulusans',
            'lulusList',
            'tidakLulusList',
            'ketuaPanitia'
        ));
    }
}
