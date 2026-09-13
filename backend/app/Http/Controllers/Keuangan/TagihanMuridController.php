<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;

use App\Models\BulanHijriyah;
use App\Models\Kepengurusan\Pengurus;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use App\Repositories\MuridRuanganRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanMuridController extends Controller
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

        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('id', 'asc')->get();

        $ruanganTerpilih = null;
        $jenisTagihanTerpilih = null;
        $masterBiayas = collect();
        $murids = collect();
        $tagihanExisting = collect();

        // PERUBAHAN: Tarik bulan dari database sesuai Tahun Pelajaran terpilih, diurutkan dengan benar
        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('urutan', 'asc')
            ->get();

        if ($request->ruangan_id) {
            $ruanganTerpilih = Ruangan::with(['level', 'murids.waliMurid'])->find($request->ruangan_id);

            if ($ruanganTerpilih) {
                // A. Ambil biaya khusus murid ...
                $masterBiayas = PengaturanTagihan::where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where(function ($q) {
                        $q->whereNull('sasaran')->orWhere('sasaran', 'murid');
                    })
                    ->where(function ($q) use ($ruanganTerpilih) {
                        $q->whereNull('level_id')->orWhere('level_id', $ruanganTerpilih->level_id);
                    })->get();

                $jumlahMurid = $ruanganTerpilih->murids->count();

                // B. Cek kepenuhan tagihan (Optimasi: Single aggregate query)
                $countsTerbit = TagihanMurid::where('ruangan_id', $ruanganTerpilih->id)
                    ->whereIn('pengaturan_tagihan_id', $masterBiayas->pluck('id'))
                    ->selectRaw('pengaturan_tagihan_id, count(*) as total')
                    ->groupBy('pengaturan_tagihan_id')
                    ->pluck('total', 'pengaturan_tagihan_id');

                foreach ($masterBiayas as $biaya) {
                    $countTerbit = $countsTerbit->get($biaya->id, 0);

                    // Gunakan jumlah bulan riil yang ada di database untuk tahun ini (bukan fixed 11 lagi)
                    $target = ($biaya->tipe === 'bulanan') ? ($jumlahMurid * $bulanHijriyah->count()) : $jumlahMurid;
                    $biaya->is_completed = ($jumlahMurid > 0 && $countTerbit >= $target);
                }

                // C. JIKA BIAYA JUGA SUDAH DIPILIH ...
                if ($request->pengaturan_tagihan_id) {
                    $jenisTagihanTerpilih = PengaturanTagihan::find($request->pengaturan_tagihan_id);
                    $murids = $ruanganTerpilih->murids()
                        ->orderBy('jenis_kelamin', 'asc')
                        ->orderBy('nama_lengkap', 'asc')
                        ->get();
                    $tagihanExisting = TagihanMurid::whereIn('murid_id', $murids->pluck('id'))
                        ->where('pengaturan_tagihan_id', $jenisTagihanTerpilih->id)
                        ->get()
                        ->groupBy('murid_id');
                }
            }
        }

        return view('tagihan-murid.index', compact(
            'daftarTahun',
            'tahunPelajaranId',
            'daftarRuangan',
            'masterBiayas',
            'ruanganTerpilih',
            'jenisTagihanTerpilih',
            'murids',
            'tagihanExisting',
            'bulanHijriyah'
        ));
    }

    public function cetakKartuSpp(Request $request, $murid_id, $tahun_id)
    {
        $ruanganTerpilih = null;
        $murid = null;

        // PERUBAHAN: Tarik dinamis
        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahun_id)->orderBy('urutan')->get();

        if ($request->filled('ruangan_id')) {
            $ruanganTerpilih = Ruangan::with('level')->berdasarkanHakAkses()->find($request->ruangan_id);
            if ($ruanganTerpilih) {
                $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruanganTerpilih->id, $tahun_id);
                $murid = $murids->firstWhere('id', $murid_id);
            }
        }

        if (!$murid) abort(404, 'Data murid tidak ditemukan.');

        $murid->kelas = $ruanganTerpilih->nama_ruangan ?? '-';
        $murid->wali = $murid->waliMurid->nama_wali ?? $murid->waliMurid->nama_ayah ?? '-';
        $murid->alamat_lengkap = $murid->alamat ?? $murid->waliMurid->alamat ?? '-';
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');

        return view('cetak-baru.cetak-kartu-tagihan', compact('murid', 'pengasuh', 'tahun_id', 'bulanHijriyah'));
    }

    public function cetakKartuSppMassal(Request $request, $ruangan_id, $tahun_id)
    {
        $ruanganTerpilih = Ruangan::with('level')->berdasarkanHakAkses()->find($ruangan_id);
        if (!$ruanganTerpilih) abort(404, 'Ruangan tidak ditemukan.');

        $murids = $this->muridRuanganRepo->getMuridByRuanganAndTahun($ruanganTerpilih->id, $tahun_id);

        // PERUBAHAN: Tarik dinamis
        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $tahun_id)->orderBy('urutan')->get();

        if ($request->has('murid_ids') && is_array($request->murid_ids)) {
            $murids = $murids->whereIn('id', $request->murid_ids);
        }

        if ($murids->isEmpty()) abort(404, 'Tidak ada data murid.');

        foreach ($murids as $murid) {
            $murid->kelas = $ruanganTerpilih->nama_ruangan ?? '-';
            $murid->wali = $murid->waliMurid->nama_wali ?? $murid->waliMurid->nama_ayah ?? '-';
            $murid->alamat_lengkap = $murid->alamat ?? $murid->waliMurid->alamat ?? '-';
        }

        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        return view('cetak-baru.cetak-kartu-tagihan-massal', compact('murids', 'pengasuh', 'tahun_id', 'bulanHijriyah'));
    }

    public function prosesTagihanPilihan(Request $request)
    {
        $ruanganId = $request->ruangan_id;
        $pengaturanTagihanId = $request->pengaturan_tagihan_id;
        $tagihanData = $request->tagihan ?? [];

        $ruang = Ruangan::with('level', 'murids')->findOrFail($ruanganId);
        $biaya = PengaturanTagihan::findOrFail($pengaturanTagihanId);

        $bulanHijriyah = BulanHijriyah::where('tahun_pelajaran_id', $biaya->tahun_pelajaran_id)->orderBy('urutan')->get();

        $isKelasAkhir = in_array($ruang->level->nama_level ?? '', ['3 TPQ', '6 IBT', '3 TSA']);
        $isKelasRendahYatim = ($ruang->level->urutan_level ?? 0) <= 7;
        $jumlahTerbuat = 0;

        DB::beginTransaction();
        try {
            $muridIds = $ruang->murids->pluck('id')->toArray();
            $existingTagihans = !empty($muridIds)
                ? TagihanMurid::whereIn('murid_id', $muridIds)
                ->where('pengaturan_tagihan_id', $biaya->id)
                ->get()
                : collect();

            $existingBulananMap = $existingTagihans->keyBy(fn($item) => $item->murid_id . '_' . $item->bulan_hijriyah_id);
            $existingNonBulananMap = $existingTagihans->keyBy(fn($item) => $item->murid_id . '_' . ($item->semester_id ?? 'null'));

            foreach ($ruang->murids as $murid) {
                $umur = $murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->age : 16;
                $wali = $murid->waliMurid ?? $murid->wali_murid;
                $statusAyah = $murid ? strtolower($murid->status_ayah ?? '') : '';

                $isYatimLayak = ($statusAyah === 'meninggal' && $umur <= 15 && $isKelasRendahYatim);

                $namaTagihanClean = trim(strtolower($biaya->nama_tagihan));
                $isSPP = in_array($namaTagihanClean, ['spp', 'syahriyah', 'spp/syahriyah']) || str_contains($namaTagihanClean, 'spp') || str_contains($namaTagihanClean, 'syahriyah');

                // LOGIKA 1: TIPE BULANAN
                if ($biaya->tipe === 'bulanan') {
                    $bulanYgDibuat = isset($tagihanData[$murid->id]) ? $tagihanData[$murid->id] : [];

                    foreach ($bulanHijriyah as $bln) {
                        $nominal = $biaya->nominal;
                        $status = 'Belum Lunas';
                        $namaSpesifik = $biaya->nama_tagihan . " " . $bln->nama_bulan . " " . $bln->tahun_hijriyah;

                        // KHUSUS YATIM (Ditanggung Donatur)
                        if ($isYatimLayak && $isSPP) {
                            $status = 'Ditanggung Donatur';
                            $namaSpesifik .= ' (Dibayarkan donatur jika ada)';
                        }

                        // KITA HAPUS PENGECEKAN '$isKeluargaAsatidz' DI SINI
                        // Karena jika data dikirim dari frontend, berarti sengaja ingin ditagih normal!

                        $existingRecord = $existingBulananMap->get($murid->id . '_' . $bln->id);
                        $isDicentang = in_array($bln->id, $bulanYgDibuat) || in_array($bln->nama_bulan, $bulanYgDibuat);

                        if ($isDicentang) {
                            if (!$existingRecord) {
                                TagihanMurid::create([
                                    'murid_id' => $murid->id,
                                    'ruangan_id' => $ruang->id,
                                    'pengaturan_tagihan_id' => $biaya->id,
                                    'bulan_hijriyah_id' => $bln->id,
                                    'semester_id' => null,
                                    'nama_tagihan_spesifik' => trim($namaSpesifik),
                                    'nominal_tagihan' => $nominal, // Tetap gunakan nominal asli dari $biaya
                                    'status_bayar' => $status      // Tetap gunakan 'Belum Lunas'
                                ]);
                                $jumlahTerbuat++;
                            }
                        } else {
                            // Hapus tagihan jika centang dihilangkan (Kecuali jika sudah Lunas)
                            if ($existingRecord && $existingRecord->status_bayar !== 'Lunas') {
                                $existingRecord->delete();
                            }
                        }
                    }
                }
                // LOGIKA 2: TIPE SEMESTER / INSIDENTAL
                else {
                    $namaSpesifik = $biaya->nama_tagihan;
                    $nominal = $biaya->nominal;
                    $status = 'Belum Lunas';

                    if ($biaya->tipe === 'semester' && $isKelasAkhir && strtolower($namaSpesifik) === 'iuran imda 2') {
                        $namaSpesifik = 'Iuran IMNI';
                    }

                    if ($isYatimLayak && $isSPP) {
                        $status = 'Ditanggung Donatur';
                        $namaSpesifik .= ' (Dibayarkan donatur jika ada)';
                    }

                    // SAMA SEPERTI DI ATAS: PENGECEKAN '$isKeluargaAsatidz' DIHAPUS

                    $semesterAktif = Semester::where('tahun_pelajaran_id', $biaya->tahun_pelajaran_id)
                        ->where('is_active', true)
                        ->first();
                    $semesterIdToSave = ($biaya->tipe === 'semester' && $semesterAktif) ? $semesterAktif->id : null;

                    $existingRecord = $existingNonBulananMap->get($murid->id . '_' . ($semesterIdToSave ?? 'null'));

                    if (isset($tagihanData[$murid->id])) {
                        if (!$existingRecord) {
                            TagihanMurid::create([
                                'murid_id' => $murid->id,
                                'ruangan_id' => $ruang->id,
                                'pengaturan_tagihan_id' => $biaya->id,
                                'bulan_hijriyah_id' => null,
                                'semester_id' => $semesterIdToSave,
                                'nama_tagihan_spesifik' => trim($namaSpesifik),
                                'nominal_tagihan' => $nominal,
                                'status_bayar' => $status
                            ]);
                            $jumlahTerbuat++;
                        }
                    } else {
                        if ($existingRecord && $existingRecord->status_bayar !== 'Lunas') {
                            $existingRecord->delete();
                        }
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', "Sukses! $jumlahTerbuat faktur tagihan baru telah diterbitkan untuk kelas ini.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}
