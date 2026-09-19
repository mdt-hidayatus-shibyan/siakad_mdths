<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Models\Ujian\DispensasiUjian;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\Ujian;
use App\Repositories\MuridRuanganRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersyaratanUjianController extends Controller
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
        $daftarUjian = collect();
        $muridsWithStatus = collect();

        // WAJIB dideklarasikan di awal agar tidak error saat view di-load pertama kali
        $jadwals = collect();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with('level')->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganTerpilih->id, $tahunPelajaranId, ['waliMurid']);
                $ruanganTerpilih->setRelation('murids', $murids);
                $levelNama = $ruanganTerpilih->level->nama_level ?? '';

                // FILTER UJIAN
                $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
                $queryUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId);

                if ($isKelasAkhir) {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMNI']);
                } else {
                    $queryUjian->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2']);
                }
                $daftarUjian = $queryUjian->get();

                // JIKA AGENDA UJIAN DIKLIK
                if ($request->ujian_id) {
                    $ujian = Ujian::find($request->ujian_id);

                    // Mengambil jadwal
                    $jadwals = JadwalUjian::with('mataPelajaran')
                        ->where('ujian_id', $ujian->id)
                        ->where('level_id', $ruanganTerpilih->level_id)
                        ->orderBy('tanggal_ujian', 'asc')
                        ->get();



                    // =========================================================================
                    // OPTIMASI: BULK QUERY (TARIK DATA MASSAL SEBELUM FOREACH)
                    // =========================================================================
                    $muridIds = $ruanganTerpilih->murids->pluck('id')->toArray();
                    $semesterUjian = $ujian->semester_relasi;
                    $bulanIds = [];

                    if ($semesterUjian && $semesterUjian->tanggal_mulai && $semesterUjian->tanggal_selesai) {
                        // MENCARI BULAN YANG BERSINGGUNGAN DENGAN SEMESTER (RUMUS OVERLAP)
                        $bulanIds = \App\Models\BulanHijriyah::where('tahun_pelajaran_id', $ujian->tahun_pelajaran_id)
                            ->where('tanggal_selesai_masehi', '>=', $semesterUjian->tanggal_mulai)
                            ->where('tanggal_mulai_masehi', '<=', $semesterUjian->tanggal_selesai)
                            ->pluck('id')
                            ->toArray();
                    } else {
                        // Fallback (Jaga-jaga jika admin lupa mengisi tanggal mulai/selesai semester)
                        $bulanIds = \App\Models\BulanHijriyah::where('tahun_pelajaran_id', $ujian->tahun_pelajaran_id)
                            ->pluck('id')
                            ->toArray();
                    }

                    // 1. Tarik Massal ID Siswa yang punya Dispensasi
                    $dispensasiMuridIds = DispensasiUjian::where('ujian_id', $ujian->id)
                        ->whereIn('murid_id', $muridIds)
                        ->pluck('murid_id')
                        ->toArray();

                    // 2. Tarik Massal ID Jenis Tagihan IMDA 1/IMNI
                    $jenis_tagihan_id = PengaturanTagihan::where('tahun_pelajaran_id', $ujian->tahun_pelajaran_id)
                        ->where('level_id', $ruanganTerpilih->level_id)
                        ->where('nama_tagihan', 'LIKE', '%' . $ujian->tipe_ujian . '%')
                        ->value('id');

                    // 3. Tarik Massal ID Siswa yang SUDAH LUNAS IMDA 1
                    $imdaLunasMuridIds = TagihanMurid::whereIn('murid_id', $muridIds)
                        ->whereIn('status_bayar', ['Lunas', 'Bebas/Gratis', 'Ditanggung Donatur'])
                        ->where(function ($q) use ($ruanganTerpilih, $jenis_tagihan_id) {
                            $q->where('ruangan_id', $ruanganTerpilih->id)
                                ->where('pengaturan_tagihan_id', $jenis_tagihan_id);
                        })
                        ->pluck('murid_id')
                        ->toArray();

                    // 4. (DIPERBARUI) Tarik Massal ID Siswa yang MENUNGGAK SPP di semester terkait
                    // Jauh lebih cepat dan akurat menggunakan bulan_hijriyah_id daripada LIKE %nama%
                    $sppMenunggakMuridIds = TagihanMurid::whereIn('murid_id', $muridIds)
                        ->where('ruangan_id', $ruanganTerpilih->id)
                        ->where('status_bayar', 'Belum Lunas')
                        ->whereNotNull('bulan_hijriyah_id') // Pastikan ini tagihan bulanan
                        ->whereIn('bulan_hijriyah_id', $bulanIds) // Cek apakah ID bulan masuk di semester ini
                        ->pluck('murid_id')
                        ->toArray();
                    // =========================================================================

                    foreach ($ruanganTerpilih->murids as $murid) {
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
                                if (!$imdaLunas) {
                                    $alasan[] = 'Iuran Ujian (' . $ujian->tipe_ujian . ')'; // Dinamis sesuai tipe ujian
                                }
                                if ($sppMenunggak) {
                                    $alasan[] = 'SPP Semester';
                                }

                                $murid->lock_reason = 'Tunggakan: ' . implode(' & ', $alasan);
                            } else {
                                $murid->is_locked = false;
                                $murid->lock_reason = 'Lunas Administrasi';
                            }
                        }
                    }

                    $muridsWithStatus = $ruanganTerpilih->murids;
                }
            }
        }

        return view('persyaratan-ujian.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'ruanganTerpilih',
            'daftarUjian',
            'jadwals',
            'muridsWithStatus',

        ));
    }

    public function beriDispensasi(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required',
            'murid_id' => 'required',
            'alasan_izin' => 'required|string|max:200'
        ]);

        DispensasiUjian::firstOrCreate(
            [
                'ujian_id' => $request->ujian_id,
                'murid_id' => $request->murid_id,
            ],
            [
                'alasan_izin' => $request->alasan_izin ?: 'Izin Orang Tua Keadaan Tidak Mampu',
                'diizinkan_oleh' => Auth::id()
            ]
        );

        return redirect()->back()->with('success', 'Akses input nilai murid berhasil dibuka via kebijakan dispensasi administrator!');
    }
}
