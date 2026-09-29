<?php

namespace App\Http\Controllers;

use App\Models\JadwalPelajaran;
use App\Models\KalendarPendidikan;
use App\Models\MataPelajaran;
use App\Models\Murid;
use App\Models\PelanggaranMurid;
use App\Models\Pengumuman;
use App\Models\PresensiMurid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use App\Models\WaliMurid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunAktif = TahunPelajaran::tahunAktif()->first();
        $selectedTahunId = $request->tahun_pelajaran_id ?? ($tahunAktif ? $tahunAktif->id : null);

        // 1. STATISTIK KESISWAAN & CIVITAS
        $queryMuridAktif = Murid::where('status', 'Aktif');
        $totalMurid = (clone $queryMuridAktif)->count();
        $totalLaki = (clone $queryMuridAktif)->where('jenis_kelamin', 'L')->count();
        $totalPerempuan = (clone $queryMuridAktif)->where('jenis_kelamin', 'P')->count();

        $totalWaliMurid = WaliMurid::where('is_active', 1)->count();
        $totalUstadz = Ustadz::count();
        $totalRombel = Ruangan::when($selectedTahunId, function ($q) use ($selectedTahunId) {
            $q->where('tahun_pelajaran_id', $selectedTahunId);
        })->count();

        $totalMapel = MataPelajaran::where('is_active', 1)->count();
        $totalJadwal = JadwalPelajaran::when($selectedTahunId, function ($q) use ($selectedTahunId) {
            $q->whereHas('ruangan', function ($rq) use ($selectedTahunId) {
                $rq->where('tahun_pelajaran_id', $selectedTahunId);
            });
        })->count();

        // 2. DISTRIBUSI PER LEVEL & RUANGAN
        $muridPerLevel = DB::table('murid_ruangans')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->join('tahun_pelajarans', 'murid_ruangans.tahun_pelajaran_id', '=', 'tahun_pelajarans.id')
            ->where('murids.status', 'Aktif')
            ->where('tahun_pelajarans.id', $selectedTahunId)
            ->select(
                'levels.nama_level',
                DB::raw('COUNT(murids.id) as total'),
                DB::raw('SUM(CASE WHEN murids.jenis_kelamin = "L" THEN 1 ELSE 0 END) as total_l'),
                DB::raw('SUM(CASE WHEN murids.jenis_kelamin = "P" THEN 1 ELSE 0 END) as total_p')
            )
            ->groupBy('levels.id', 'levels.nama_level')
            ->orderBy('levels.urutan_level', 'asc')
            ->get();

        $muridPerRuangan = DB::table('murid_ruangans')
            ->join('murids', 'murid_ruangans.murid_id', '=', 'murids.id')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('tahun_pelajarans', 'ruangans.tahun_pelajaran_id', '=', 'tahun_pelajarans.id')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->where('murids.status', 'Aktif')
            ->where('tahun_pelajarans.id', $selectedTahunId)
            ->select(
                'ruangans.nama_ruangan',
                DB::raw('COUNT(murids.id) as total'),
                DB::raw('SUM(CASE WHEN murids.jenis_kelamin = "L" THEN 1 ELSE 0 END) as total_l'),
                DB::raw('SUM(CASE WHEN murids.jenis_kelamin = "P" THEN 1 ELSE 0 END) as total_p')
            )
            ->groupBy('ruangans.id', 'ruangans.nama_ruangan')
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('ruangans.id', 'asc')
            ->get();

        // 3. STATISTIK AKADEMIK & TATA TERTIB (PELANGGARAN MURID)
        $totalPelanggaran = PelanggaranMurid::when($selectedTahunId, function ($q) use ($selectedTahunId) {
            $q->where('tahun_pelajaran_id', $selectedTahunId);
        })->count();

        $totalPoinPelanggaran = DB::table('pelanggaran_murids')
            ->join('referensi_pelanggarans', 'pelanggaran_murids.referensi_pelanggaran_id', '=', 'referensi_pelanggarans.id')
            ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                $q->where('pelanggaran_murids.tahun_pelajaran_id', $selectedTahunId);
            })
            ->sum('referensi_pelanggarans.poin') ?? 0;

        // Top Jenis Pelanggaran Terbanyak yang Dilakukan Murid
        $topPelanggaran = DB::table('pelanggaran_murids')
            ->join('referensi_pelanggarans', 'pelanggaran_murids.referensi_pelanggaran_id', '=', 'referensi_pelanggarans.id')
            ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                $q->where('pelanggaran_murids.tahun_pelajaran_id', $selectedTahunId);
            })
            ->select(
                'referensi_pelanggarans.id',
                'referensi_pelanggarans.nama_pelanggaran',
                'referensi_pelanggarans.kategori',
                'referensi_pelanggarans.poin',
                DB::raw('COUNT(pelanggaran_murids.id) as total_kasus'),
                DB::raw('SUM(referensi_pelanggarans.poin) as total_poin')
            )
            ->groupBy('referensi_pelanggarans.id', 'referensi_pelanggarans.nama_pelanggaran', 'referensi_pelanggarans.kategori', 'referensi_pelanggarans.poin')
            ->orderByDesc('total_kasus')
            ->take(5)
            ->get();

        // Distribusi Pelanggaran Berdasarkan Tingkat Keparahan / Kategori
        $pelanggaranPerKategoriRaw = DB::table('pelanggaran_murids')
            ->join('referensi_pelanggarans', 'pelanggaran_murids.referensi_pelanggaran_id', '=', 'referensi_pelanggarans.id')
            ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                $q->where('pelanggaran_murids.tahun_pelajaran_id', $selectedTahunId);
            })
            ->select('referensi_pelanggarans.kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('referensi_pelanggarans.kategori')
            ->pluck('total', 'kategori')
            ->toArray();

        $kategoriRingan = $pelanggaranPerKategoriRaw['Ringan'] ?? 0;
        $kategoriSedang = $pelanggaranPerKategoriRaw['Sedang'] ?? 0;
        $kategoriBerat = $pelanggaranPerKategoriRaw['Berat'] ?? 0;

        // Murid dengan Akumulasi Pelanggaran Tertinggi (Perlu Pembinaan)
        $muridPerluPembinaan = DB::table('pelanggaran_murids')
            ->join('murids', 'pelanggaran_murids.murid_id', '=', 'murids.id')
            ->join('referensi_pelanggarans', 'pelanggaran_murids.referensi_pelanggaran_id', '=', 'referensi_pelanggarans.id')
            ->leftJoin('ruangans', 'pelanggaran_murids.ruangan_id', '=', 'ruangans.id')
            ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                $q->where('pelanggaran_murids.tahun_pelajaran_id', $selectedTahunId);
            })
            ->select(
                'murids.id',
                'murids.nism',
                'murids.nama_lengkap',
                'murids.jenis_kelamin',
                'ruangans.nama_ruangan',
                DB::raw('COUNT(pelanggaran_murids.id) as total_kasus'),
                DB::raw('SUM(referensi_pelanggarans.poin) as total_poin')
            )
            ->groupBy('murids.id', 'murids.nism', 'murids.nama_lengkap', 'murids.jenis_kelamin', 'ruangans.nama_ruangan')
            ->orderByDesc('total_poin')
            ->orderByDesc('total_kasus')
            ->take(5)
            ->get();

        // 4. STATISTIK PRESENSI MURID
        $queryPresensi = PresensiMurid::query();
        if ($selectedTahunId) {
            $queryPresensi->whereHas('jadwalPelajaran.ruangan', function ($q) use ($selectedTahunId) {
                $q->where('tahun_pelajaran_id', $selectedTahunId);
            });
        }
        $presensiHadir = (clone $queryPresensi)->where('status', 'Hadir')->count();
        $presensiSakit = (clone $queryPresensi)->where('status', 'Sakit')->count();
        $presensiIzin = (clone $queryPresensi)->where('status', 'Izin')->count();
        $presensiAlpha = (clone $queryPresensi)->where('status', 'Alpha')->count();
        $totalPresensi = $presensiHadir + $presensiSakit + $presensiIzin + $presensiAlpha;
        $persenKehadiran = $totalPresensi > 0 ? round(($presensiHadir / $totalPresensi) * 100, 1) : 0;

        // 5. OPERATIONAL FEEDS: PENGUMUMAN, KALENDER, PELANGGARAN TERBARU
        $pengumumans = Pengumuman::whereIn('status', ['Terbit', 'Published'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $agendaKalender = KalendarPendidikan::with('kategoriKegiatan')
            ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                $q->where('tahun_pelajaran_id', $selectedTahunId);
            })
            ->orderBy('tanggal_mulai', 'asc')
            ->where('tanggal_selesai', '>=', now()->toDateString())
            ->take(5)
            ->get();

        if ($agendaKalender->isEmpty()) {
            $agendaKalender = KalendarPendidikan::with('kategoriKegiatan')
                ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                    $q->where('tahun_pelajaran_id', $selectedTahunId);
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->take(5)
                ->get();
        }

        $pelanggaranTerbaru = PelanggaranMurid::with(['murid', 'referensiPelanggaran', 'ruangan'])
            ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
                $q->where('tahun_pelajaran_id', $selectedTahunId);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'tahunPelajarans',
            'selectedTahunId',
            'totalMurid',
            'totalLaki',
            'totalPerempuan',
            'totalWaliMurid',
            'totalUstadz',
            'totalRombel',
            'totalMapel',
            'totalJadwal',
            'muridPerLevel',
            'muridPerRuangan',
            'totalPelanggaran',
            'totalPoinPelanggaran',
            'topPelanggaran',
            'kategoriRingan',
            'kategoriSedang',
            'kategoriBerat',
            'muridPerluPembinaan',
            'presensiHadir',
            'presensiSakit',
            'presensiIzin',
            'presensiAlpha',
            'totalPresensi',
            'persenKehadiran',
            'pengumumans',
            'agendaKalender',
            'pelanggaranTerbaru'
        ));
    }
}
