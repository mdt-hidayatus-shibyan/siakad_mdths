<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\PembayaranImni;
use App\Models\Ujian\PengeluaranImni;
use App\Models\Ujian\PesertaImni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImniDashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Terpadu Kepanitiaan IMNI
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        // 1. SK & Personel Kepanitiaan IMNI
        $panitiaAll = PanitiaImni::with('ustadz')
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->get();
        
        $ketua = $panitiaAll->where('jabatan', 'Ketua')->where('is_active', true)->first();
        $bendahara = $panitiaAll->where('jabatan', 'Bendahara')->where('is_active', true)->first();
        $anggota = $panitiaAll->where('jabatan', 'Anggota')->where('is_active', true);
        $totalPanitiaAktif = $panitiaAll->where('is_active', true)->count();

        // 2. Data Murid Kelas Akhir (Kandidat Peserta IMNI: 3 TPQ, 6 IBT, 3 TSA)
        $levelTpqIds = Level::where('urutan_level', 3)->orWhere('nama_level', 'LIKE', '%3%TPQ%')->pluck('id');
        $levelIbtIds = Level::where('urutan_level', 9)->orWhere('nama_level', 'LIKE', '%6%IBT%')->pluck('id');
        $levelTsaIds = Level::where('urutan_level', 12)->orWhere('nama_level', 'LIKE', '%3%TSA%')->pluck('id');

        // Query count murid aktif per tingkat akhir
        $countMuridTpq = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $selectedTahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelTpqIds)
            ->distinct('murids.id')
            ->count('murids.id');

        $countMuridIbt = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $selectedTahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelIbtIds)
            ->distinct('murids.id')
            ->count('murids.id');

        $countMuridTsa = DB::table('murid_ruangans')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $selectedTahunId)
            ->where('murids.status', 'Aktif')
            ->whereIn('ruangans.level_id', $levelTsaIds)
            ->distinct('murids.id')
            ->count('murids.id');

        $totalKandidatImni = $countMuridTpq + $countMuridIbt + $countMuridTsa;

        // Ruangan kelas akhir
        $ruangansAkhir = Ruangan::with(['level', 'ustadz'])
            ->where('tahun_pelajaran_id', $selectedTahunId)
            ->whereIn('level_id', $levelTpqIds->concat($levelIbtIds)->concat($levelTsaIds))
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        // 3. Statistik Peserta IMNI
        $totalPesertaTerdaftar = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->count();
        $totalPesertaLayak = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->where('status_kelayakan', 'Layak')->count();
        $totalPesertaTerplotting = PesertaImni::where('tahun_pelajaran_id', $selectedTahunId)->whereNotNull('ruangan_ujian_id')->count();

        // 4. Ringkasan Real Keuangan Kas Panitia IMNI
        $totalPemasukanReal = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal_bayar');
        $totalPengeluaranReal = PengeluaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal');
        $totalTagihanReal = PembayaranImni::where('tahun_pelajaran_id', $selectedTahunId)->sum('nominal_tagihan');
        $saldoKasReal = $totalPemasukanReal - $totalPengeluaranReal;
        $persenLunasReal = ($totalTagihanReal > 0) ? round(($totalPemasukanReal / $totalTagihanReal) * 100, 1) : 0;

        $keuangan = (object) [
            'total_pemasukan'   => $totalPemasukanReal,
            'total_pengeluaran' => $totalPengeluaranReal,
            'saldo_kas'         => $saldoKasReal,
            'total_tagihan'     => $totalTagihanReal,
            'persen_lunas'      => $persenLunasReal,
        ];

        // 5. Daftar 7 Modul Operasional Kepanitiaan IMNI
        $quickLinks = [
            [
                'title'       => 'Master IMNI',
                'description' => 'Master agenda pelaksanaan Imtihan Niha\'i per tahun pelajaran',
                'route'       => route('master-imni.index'),
                'icon'        => 'bi-journal-bookmark-fill',
                'color'       => 'purple',
                'badge'       => 'Agenda IMNI',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Susunan Kepanitiaan',
                'description' => 'Penetapan SK Ketua, Bendahara & Anggota Panitia IMNI',
                'route'       => route('panitia-imni.index'),
                'icon'        => 'bi-people-fill',
                'color'       => 'primary',
                'badge'       => $totalPanitiaAktif . ' Personel',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Peserta IMNI',
                'description' => 'Penarikan data murid kelas akhir, penetapan nomor peserta & cetak kartu',
                'route'       => route('peserta-imni.index'),
                'icon'        => 'bi-mortarboard-fill',
                'color'       => 'blue',
                'badge'       => ($totalPesertaTerdaftar > 0 ? $totalPesertaTerdaftar : $totalKandidatImni) . ' Murid Peserta',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Jadwal Ujian IMNI',
                'description' => 'Distribusi jadwal sesi ujian tertulis & pengawas ruangan kelas akhir',
                'route'       => route('jadwal-imni.index'),
                'icon'        => 'bi-calendar-week-fill',
                'color'       => 'sky',
                'badge'       => 'Jadwal Sesi',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Ruangan IMNI',
                'description' => 'Pengaturan ruangan ujian & plotting distribusi NISM murid per hari',
                'route'       => route('ruangan-imni.index'),
                'icon'        => 'bi-door-open-fill',
                'color'       => 'teal',
                'badge'       => $totalPesertaTerplotting . ' Murid Ter-plot',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Pembayaran IMNI',
                'description' => 'Loket kasir pembayaran biaya administrasi ujian & kwitansi',
                'route'       => route('pembayaran-imni.index'),
                'icon'        => 'bi-credit-card-fill',
                'color'       => 'emerald',
                'badge'       => 'Rp ' . number_format($totalPemasukanReal, 0, ',', '.'),
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Pengeluaran IMNI',
                'description' => 'Buku kas keluar, honor panitia/pengawas/juri & LPJ kas IMNI',
                'route'       => route('pengeluaran-imni.index'),
                'icon'        => 'bi-cash-coin',
                'color'       => 'amber',
                'badge'       => 'Rp ' . number_format($totalPengeluaranReal, 0, ',', '.'),
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Presensi IMNI',
                'description' => 'Input kehadiran santri per ruangan IMNI (6 IBT & 3 TSA) & kelas 3 TPQ',
                'route'       => route('presensi-imni.index'),
                'icon'        => 'bi-clipboard-check-fill',
                'color'       => 'indigo',
                'badge'       => 'Presensi Santri',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Input Nilai Mapel',
                'description' => 'Input nilai mata pelajaran teori/tulis & rekapitulasi leger',
                'route'       => route('nilai-imni.index'),
                'icon'        => 'bi-pencil-square',
                'color'       => 'violet',
                'badge'       => 'Leger Nilai',
                'status'      => 'Selesai',
            ],
            [
                'title'       => 'Putusan Kelulusan',
                'description' => 'Sidang yudisium kelulusan akhir, transkrip & syahadah/ijazah',
                'route'       => route('putusan-imni.index'),
                'icon'        => 'bi-file-earmark-check-fill',
                'color'       => 'rose',
                'badge'       => 'Yudisium & Ijazah',
                'status'      => 'Selesai',
            ],
        ];

        return view('ujian.panitia-imni.dashboard', compact(
            'daftarTahun',
            'selectedTahun',
            'selectedTahunId',
            'ketua',
            'bendahara',
            'anggota',
            'totalPanitiaAktif',
            'panitiaAll',
            'countMuridTpq',
            'countMuridIbt',
            'countMuridTsa',
            'totalKandidatImni',
            'totalPesertaTerdaftar',
            'totalPesertaLayak',
            'totalPesertaTerplotting',
            'ruangansAkhir',
            'keuangan',
            'quickLinks'
        ));
    }

    /**
     * Placeholder untuk rute yang akan dikerjakan pada fase berikutnya
     */
    public function placeholderPembayaran()
    {
        return redirect()->route('dashboard-imni.index')
            ->with('info', 'Modul Pembayaran IMNI sedang dalam tahap pengerjaan (Fase 3).');
    }

    public function placeholderPengeluaran()
    {
        return redirect()->route('dashboard-imni.index')
            ->with('info', 'Modul Pengeluaran IMNI sedang dalam tahap pengerjaan (Fase 3).');
    }

    public function placeholderPresensi()
    {
        return redirect()->route('dashboard-imni.index')
            ->with('info', 'Modul Presensi & Berita Acara IMNI sedang dalam tahap pengerjaan (Fase 4).');
    }

    public function placeholderNilai()
    {
        return redirect()->route('dashboard-imni.index')
            ->with('info', 'Modul Input Nilai Mapel IMNI sedang dalam tahap pengerjaan (Fase 5).');
    }

    public function placeholderKelulusan()
    {
        return redirect()->route('dashboard-imni.index')
            ->with('info', 'Modul Putusan Kelulusan IMNI sedang dalam tahap pengerjaan (Fase 5).');
    }
}
