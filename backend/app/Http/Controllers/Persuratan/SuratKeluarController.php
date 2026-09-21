<?php

namespace App\Http\Controllers\Persuratan;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Murid;
use App\Models\Persuratan\SuratKeluar;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use App\Repositories\MuridRuanganRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratKeluarController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }
    /**
     * Tampilkan daftar riwayat surat keluar resmi
     */
    public function index(Request $request)
    {
        $jenisFilter = $request->query('jenis', 'all');
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('q');
        $tahunId = $request->query('tahun_pelajaran_id');

        $query = SuratKeluar::with([
            'tahunPelajaran',
            'creator',
            'waliMurid.kampung'
        ]);

        if ($jenisFilter && $jenisFilter !== 'all') {
            $query->where('jenis_surat', $jenisFilter);
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($tahunId) {
            $query->where('tahun_pelajaran_id', $tahunId);
        }

        if ($search) {
            $matchedMuridIds = Murid::where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('nism', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($search, $matchedMuridIds) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('perihal', 'like', "%{$search}%")
                    ->orWhere('tujuan_surat', 'like', "%{$search}%")
                    ->orWhere('penandatangan_nama', 'like', "%{$search}%");

                if (!empty($matchedMuridIds)) {
                    $q->orWhere(function ($subQ) use ($matchedMuridIds) {
                        foreach ($matchedMuridIds as $mId) {
                            $subQ->orWhereJsonContains('murid_ids', (int) $mId);
                        }
                    });
                }
            });
        }

        $surats = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Statistik
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $totalSurat = SuratKeluar::count();
        $panggilanCount = SuratKeluar::where('jenis_surat', 'surat_panggilan')->count();
        $spCount = SuratKeluar::where('jenis_surat', 'surat_peringatan')->count();
        $pemberitahuanCount = SuratKeluar::where('jenis_surat', 'surat_pemberitahuan')->count();
        $edaranCount = SuratKeluar::where('jenis_surat', 'surat_edaran')->count();
        $izinCount = SuratKeluar::where('jenis_surat', 'surat_permohonan_izin')->count();
        $dispensasiCount = SuratKeluar::where('jenis_surat', 'surat_dispensasi')->count();
        $undanganCount = SuratKeluar::where('jenis_surat', 'surat_undangan')->count();

        $bulanIniCount = SuratKeluar::whereYear('tanggal_surat', Carbon::now()->year)
            ->whereMonth('tanggal_surat', Carbon::now()->month)
            ->count();

        $daftarJenis = SuratKeluar::DAFTAR_JENIS_SURAT;
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'desc')->get();

        return view('persuratan.surat-keluar.index', compact(
            'surats',
            'jenisFilter',
            'statusFilter',
            'search',
            'tahunId',
            'totalSurat',
            'panggilanCount',
            'spCount',
            'pemberitahuanCount',
            'edaranCount',
            'izinCount',
            'dispensasiCount',
            'undanganCount',
            'bulanIniCount',
            'daftarJenis',
            'tahunPelajarans',
            'tahunAktif'
        ));
    }

    /**
     * Tampilkan formulir pembuatan surat baru
     */
    public function create(Request $request)
    {
        $selectedJenis = $request->query('jenis', 'surat_panggilan');
        if (!array_key_exists($selectedJenis, SuratKeluar::DAFTAR_JENIS_SURAT)) {
            $selectedJenis = 'surat_panggilan';
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first()
            ?? TahunPelajaran::latest()->first();

        // Ambil data murid aktif dari pivot MuridRuanganRepository
        $murids = $this->muridRuanganRepo->getAllMuridAktifWithRuanganByTahun($tahunAktif?->id);

        // Ambil data Ruangan untuk filter massal & tunggal, diurutkan berdasarkan level->urutan_level
        $ruangans = Ruangan::leftJoin('levels', 'ruangans.level_id', '=', 'levels.id')
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('ruangans.nama_ruangan', 'asc')
            ->select('ruangans.*')
            ->get();

        // Data jika menduplikasi surat lama
        $duplicateSurat = null;
        if ($request->filled('duplicate_from')) {
            $duplicateSurat = SuratKeluar::with(['tahunPelajaran', 'waliMurid.kampung'])->find($request->query('duplicate_from'));
            if ($duplicateSurat) {
                $selectedJenis = $duplicateSurat->jenis_surat;
            }
        }

        // Pejabat Penandatangan Config (4 Custom Signers: Pengasuh, Sekjen, Kabid, Admin)
        $penandatanganConfig = $this->getDefaultPenandatanganConfig($duplicateSurat);
        $penandatanganList = $penandatanganConfig['signers'];

        // Info tanggal hari ini (Masehi & Hijriyah)
        $todayInfo = getTodayDateInfo();

        // Generate nomor surat default
        $nomorInfo = SuratKeluar::generateNomorSurat($selectedJenis, date('Y-m-d'));

        $daftarJenis = SuratKeluar::DAFTAR_JENIS_SURAT;

        return view('persuratan.surat-keluar.create', compact(
            'selectedJenis',
            'tahunAktif',
            'murids',
            'ruangans',
            'penandatanganList',
            'penandatanganConfig',
            'todayInfo',
            'nomorInfo',
            'daftarJenis',
            'duplicateSurat'
        ));
    }

    /**
     * Duplikasi surat yang sudah ada untuk pembuatan cepat
     */
    public function duplicate($id)
    {
        $surat = SuratKeluar::findOrFail($id);
        return redirect()->route('surat-keluar.create', [
            'jenis' => $surat->jenis_surat,
            'duplicate_from' => $surat->id
        ])->with('info', "Menyalin isian dari surat nomor {$surat->nomor_surat}. Silakan sesuaikan data penerima.");
    }

    /**
     * Simpan data pembuatan surat keluar baru (Mendukung mode Tunggal & Massal)
     */
    public function store(Request $request)
    {
        $targetMode = $request->input('target_mode', 'single');
        $jenisSurat = $request->input('jenis_surat');

        // ==========================================
        // 1. MODE MASSAL (BANYAK MURID SEKALIGUS)
        // ==========================================
        if ($targetMode === 'bulk') {
            $request->validate([
                'jenis_surat'           => 'required|string|in:' . implode(',', array_keys(SuratKeluar::DAFTAR_JENIS_SURAT)),
                'murid_ids'             => 'required|array|min:1',
                'murid_ids.*'           => 'required|integer|exists:murids,id',
                'perihal'               => 'required|string|max:255',
                'tanggal_surat'         => 'required|date',
                'penandatangan_nama'    => 'required|string|max:255',
                'penandatangan_jabatan' => 'required|string|max:255',
            ], [
                'murid_ids.required'             => 'Paling sedikit 1 murid harus dipilih untuk pembuatan massal.',
                'murid_ids.min'                  => 'Paling sedikit 1 murid harus dipilih untuk pembuatan massal.',
                'perihal.required'               => 'Perihal surat wajib diisi.',
                'tanggal_surat.required'         => 'Tanggal surat wajib diisi.',
                'penandatangan_nama.required'    => 'Nama penandatangan wajib diisi.',
                'penandatangan_jabatan.required' => 'Jabatan penandatangan wajib diisi.',
            ]);

            $tanggalSurat = $request->input('tanggal_surat');
            $dateInfo = getTodayDateInfo($tanggalSurat);
            $tanggalHijriyah = $request->input('tanggal_hijriyah') ?: ($dateInfo['hijri'] ?? null);

            $muridIds = $request->input('murid_ids');
            $createdSurats = [];

            DB::beginTransaction();
            try {
                $perihal = trim($request->input('perihal'));
                $hasLampiran = $request->boolean('has_lampiran') || $request->input('has_lampiran') === '1' || $request->input('has_lampiran') === 'true';
                $lampiran = $request->input('lampiran');
                if (empty($lampiran) || $lampiran === '-') {
                    $lampiran = $hasLampiran ? '1 Lembar' : '-';
                }
                $sifatSurat = $request->input('sifat_surat', 'Biasa');
                $tempatTerbit = $request->input('tempat_terbit', 'Bangkalan');
                $tahunPelajaranId = $request->input('tahun_pelajaran_id')
                    ?: (TahunPelajaran::where('is_active', true)->value('id') ?? 1);
                $isiSurat = $request->input('isi_surat');
                $tembusan = $request->input('tembusan');
                $status = $request->input('status', 'terbit');

                // Parse penandatangan list
                $penandatanganList = $request->input('penandatangan_list');
                if (is_string($penandatanganList)) {
                    $penandatanganList = json_decode($penandatanganList, true);
                }
                if (!is_array($penandatanganList) || empty($penandatanganList)) {
                    $penandatanganConfig = $this->getDefaultPenandatanganConfig();
                    $penandatanganList = $penandatanganConfig['signers'];
                }

                $firstActive = collect($penandatanganList)->first(function ($s) {
                    return !isset($s['is_active']) || $s['is_active'] === true || $s['is_active'] === '1' || $s['is_active'] === 1 || $s['is_active'] === 'true';
                });

                $penandatanganNama = $firstActive['nama'] ?? trim($request->input('penandatangan_nama', 'Mikyal Adly'));
                $penandatanganJabatan = $firstActive['jabatan'] ?? trim($request->input('penandatangan_jabatan', 'Pengasuh MDT Hidayatus Shibyan'));
                $penandatanganNip = $firstActive['nip'] ?? $request->input('penandatangan_nip', '-');

                // JIKA DISPENSASI MASSAL: Dibuat sebagai 1 Surat Permohonan Kolektif dengan Lampiran Tabel Murid
                if ($jenisSurat === 'surat_dispensasi') {
                    $isiSpesifik = $this->buildIsiSpesifik($request, $jenisSurat);
                    $isiSpesifik['has_lampiran'] = true;

                    $nomorSurat = trim($request->input('nomor_surat'));
                    $nomorAgenda = $request->input('nomor_agenda');
                    if (empty($nomorSurat) || empty($nomorAgenda)) {
                        $gen = SuratKeluar::generateNomorSurat($jenisSurat, $tanggalSurat);
                        if (empty($nomorSurat)) $nomorSurat = $gen['nomor_surat'];
                        if (empty($nomorAgenda)) $nomorAgenda = $gen['nomor_agenda'];
                    }

                    $tujuanSurat = trim($request->input('nama_sekolah_tujuan') ?: $request->input('tujuan_surat', 'Yth. Kepala Sekolah'));
                    $alamatTujuan = $request->input('alamat_tujuan') ?: 'Di Tempat';
                    $cleanMuridIds = array_values(array_filter(array_map('intval', (array)$muridIds)));

                    $newSurat = SuratKeluar::create([
                        'nomor_surat'           => $nomorSurat,
                        'nomor_agenda'          => $nomorAgenda,
                        'jenis_surat'           => $jenisSurat,
                        'perihal'               => $perihal,
                        'lampiran'              => '1 (Satu) Lembar',
                        'sifat_surat'           => $sifatSurat,
                        'tujuan_surat'          => $tujuanSurat,
                        'alamat_tujuan'         => $alamatTujuan,
                        'kategori_penerima'     => 'lembaga_luar',
                        'murid_ids'             => !empty($cleanMuridIds) ? $cleanMuridIds : null,
                        'wali_murid_id'         => null,
                        'tahun_pelajaran_id'    => $tahunPelajaranId,
                        'tanggal_surat'         => $tanggalSurat,
                        'tanggal_hijriyah'      => $tanggalHijriyah,
                        'tempat_terbit'         => $tempatTerbit,
                        'isi_spesifik'          => $isiSpesifik,
                        'isi_surat'             => $isiSurat,
                        'tembusan'              => $tembusan,
                        'penandatangan_list'    => $penandatanganList,
                        'penandatangan_nama'    => $penandatanganNama,
                        'penandatangan_jabatan' => $penandatanganJabatan,
                        'penandatangan_nip'     => $penandatanganNip,
                        'status'                => $status,
                        'qr_token'              => SuratKeluar::generateQrToken(),
                        'created_by'            => Auth::id(),
                    ]);

                    DB::commit();

                    return redirect()->route('surat-keluar.show', $newSurat->id)
                        ->with('success', "Alhamdulillah! Berhasil menerbitkan Surat Permohonan Dispensasi Kolektif (1 Nomor Surat) beserta Lembar Lampiran Tabel Murid.");
                }

                foreach ($muridIds as $mId) {
                    $murid = Murid::with(['waliMurid.kampung', 'ruangans', 'ruanganMasuk'])->find($mId);
                    if (!$murid) continue;

                    $isiSpesifik = $this->buildIsiSpesifik($request, $jenisSurat, $murid);

                    // Auto-increment nomor surat berurutan
                    $gen = SuratKeluar::generateNomorSurat($jenisSurat, $tanggalSurat);
                    $nomorSurat = $gen['nomor_surat'];
                    $nomorAgenda = $gen['nomor_agenda'];

                    // Penentuan tujuan surat otomatis (Mengambil data wali dari tabel murid->nama_ayah)
                    $wali = $murid->waliMurid;
                    $namaWali = $murid->nama_ayah ?: ($wali?->nama_kepala_keluarga ?: ($murid->nama_ibu ?: ''));
                    $kampung = $wali?->kampung?->nama_kampung ?? $murid->alamat ?? 'Ds. Somorkoneng';

                    if (in_array($jenisSurat, ['surat_panggilan', 'surat_pemberitahuan', 'surat_undangan'])) {
                        $tujuanSurat = $namaWali ? "Bpk/Ibu/Wali dari {$murid->nama_lengkap} ({$namaWali})" : "Bpk/Ibu/Wali dari {$murid->nama_lengkap}";
                        $kategoriPenerima = 'wali_murid';
                    } elseif ($jenisSurat === 'surat_peringatan') {
                        $tujuanSurat = "Murid: {$murid->nama_lengkap} (NISM: {$murid->nism})";
                        $kategoriPenerima = 'murid';
                    } elseif ($jenisSurat === 'surat_dispensasi') {
                        $tujuanSurat = trim($request->input('nama_sekolah_tujuan') ?: $request->input('tujuan_surat', 'Pihak Terkait'));
                        $kategoriPenerima = 'lembaga';
                    } else {
                        $tujuanSurat = trim($request->input('tujuan_surat') ?: $murid->nama_lengkap);
                        $kategoriPenerima = $request->input('kategori_penerima', 'umum');
                    }

                    $alamatTujuan = $kampung ?: ($request->input('alamat_tujuan') ?: 'Di Tempat');

                    $newSurat = SuratKeluar::create([
                        'nomor_surat'           => $nomorSurat,
                        'nomor_agenda'          => $nomorAgenda,
                        'jenis_surat'           => $jenisSurat,
                        'perihal'               => $perihal,
                        'lampiran'              => $lampiran,
                        'sifat_surat'           => $sifatSurat,
                        'tujuan_surat'          => $tujuanSurat,
                        'alamat_tujuan'         => $alamatTujuan,
                        'kategori_penerima'     => $kategoriPenerima,
                        'murid_ids'             => [(int) $murid->id],
                        'wali_murid_id'         => $wali?->id,
                        'tahun_pelajaran_id'    => $tahunPelajaranId,
                        'tanggal_surat'         => $tanggalSurat,
                        'tanggal_hijriyah'      => $tanggalHijriyah,
                        'tempat_terbit'         => $tempatTerbit,
                        'isi_spesifik'          => $isiSpesifik,
                        'isi_surat'             => $isiSurat,
                        'tembusan'              => $tembusan,
                        'penandatangan_list'    => $penandatanganList,
                        'penandatangan_nama'    => $penandatanganNama,
                        'penandatangan_jabatan' => $penandatanganJabatan,
                        'penandatangan_nip'     => $penandatanganNip,
                        'status'                => $status,
                        'qr_token'              => SuratKeluar::generateQrToken(),
                        'created_by'            => Auth::id(),
                    ]);

                    $createdSurats[] = $newSurat;
                }

                DB::commit();

                $ids = collect($createdSurats)->pluck('id')->toArray();
                $count = count($createdSurats);
                return redirect()->route('surat-keluar.index', ['jenis' => $jenisSurat])
                    ->with('success', "Alhamdulillah! Berhasil menerbitkan {$count} surat resmi sekaligus secara berurutan.")
                    ->with('bulk_created_ids', $ids);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Gagal membuat surat massal: ' . $e->getMessage());
            }
        }

        // ==========================================
        // 2. MODE TUNGGAL (1 SURAT)
        // ==========================================
        $request->validate([
            'jenis_surat'           => 'required|string|in:' . implode(',', array_keys(SuratKeluar::DAFTAR_JENIS_SURAT)),
            'nomor_surat'           => 'required|string|max:100',
            'perihal'               => 'required|string|max:255',
            'tujuan_surat'          => 'required|string|max:255',
            'tanggal_surat'         => 'required|date',
        ], [
            'jenis_surat.required'           => 'Jenis surat wajib dipilih.',
            'nomor_surat.required'           => 'Nomor surat wajib diisi.',
            'perihal.required'               => 'Perihal surat wajib diisi.',
            'tujuan_surat.required'          => 'Tujuan / Penerima surat wajib diisi.',
            'tanggal_surat.required'         => 'Tanggal surat wajib diisi.',
        ]);

        $isiSpesifik = $this->buildIsiSpesifik($request, $jenisSurat);

        // Ambil ID Murid
        $muridIds = [];
        if ($request->filled('murid_ids')) {
            $muridIds = array_values(array_filter(array_map('intval', (array)$request->input('murid_ids'))));
        } elseif ($request->filled('murid_id')) {
            $muridIds = [(int)$request->input('murid_id')];
        }

        $waliMuridId = null;
        if (!empty($muridIds)) {
            $firstMurid = Murid::find($muridIds[0]);
            $waliMuridId = $firstMurid?->wali_murid_id;
        }

        $tanggalSurat = $request->input('tanggal_surat');
        $dateInfo = getTodayDateInfo($tanggalSurat);
        $tanggalHijriyah = $request->input('tanggal_hijriyah') ?: ($dateInfo['hijri'] ?? null);

        // Cari nomor agenda jika tidak diinput
        $nomorAgenda = $request->input('nomor_agenda');
        if (empty($nomorAgenda)) {
            $gen = SuratKeluar::generateNomorSurat($jenisSurat, $tanggalSurat);
            $nomorAgenda = $gen['nomor_agenda'];
        }

        // Parse penandatangan list
        $penandatanganList = $request->input('penandatangan_list');
        if (is_string($penandatanganList)) {
            $penandatanganList = json_decode($penandatanganList, true);
        }
        if (!is_array($penandatanganList) || empty($penandatanganList)) {
            $penandatanganConfig = $this->getDefaultPenandatanganConfig();
            $penandatanganList = $penandatanganConfig['signers'];
        }

        $firstActive = collect($penandatanganList)->first(function ($s) {
            return !isset($s['is_active']) || $s['is_active'] === true || $s['is_active'] === '1' || $s['is_active'] === 1 || $s['is_active'] === 'true';
        });

        $penandatanganNama = $firstActive['nama'] ?? trim($request->input('penandatangan_nama', 'Mikyal Adly'));
        $penandatanganJabatan = $firstActive['jabatan'] ?? trim($request->input('penandatangan_jabatan', 'Pengasuh MDT Hidayatus Shibyan'));
        $penandatanganNip = $firstActive['nip'] ?? $request->input('penandatangan_nip', '-');

        $hasLampiran = $request->boolean('has_lampiran') || $request->input('has_lampiran') === '1' || $request->input('has_lampiran') === 'true';
        $lampiran = $request->input('lampiran');
        if (empty($lampiran) || $lampiran === '-') {
            $lampiran = $hasLampiran ? '1 Lembar' : '-';
        }

        $surat = SuratKeluar::create([
            'nomor_surat'           => trim($request->input('nomor_surat')),
            'nomor_agenda'          => $nomorAgenda,
            'jenis_surat'           => $jenisSurat,
            'perihal'               => trim($request->input('perihal')),
            'lampiran'              => $lampiran,
            'sifat_surat'           => $request->input('sifat_surat', 'Biasa'),
            'tujuan_surat'          => trim($request->input('tujuan_surat')),
            'alamat_tujuan'         => $request->input('alamat_tujuan'),
            'kategori_penerima'     => $request->input('kategori_penerima', 'umum'),
            'murid_ids'             => !empty($muridIds) ? $muridIds : null,
            'wali_murid_id'         => $waliMuridId,
            'tahun_pelajaran_id'    => $request->input('tahun_pelajaran_id'),
            'tanggal_surat'         => $tanggalSurat,
            'tanggal_hijriyah'      => $tanggalHijriyah,
            'tempat_terbit'         => $request->input('tempat_terbit', 'Bangkalan'),
            'isi_spesifik'          => $isiSpesifik,
            'isi_surat'             => $request->input('isi_surat'),
            'tembusan'              => $request->input('tembusan'),
            'penandatangan_list'    => $penandatanganList,
            'penandatangan_nama'    => $penandatanganNama,
            'penandatangan_jabatan' => $penandatanganJabatan,
            'penandatangan_nip'     => $penandatanganNip,
            'status'                => $request->input('status', 'terbit'),
            'qr_token'              => SuratKeluar::generateQrToken(),
            'created_by'            => Auth::id(),
        ]);

        return redirect()->route('surat-keluar.show', $surat->id)
            ->with('success', "Surat {$surat->nama_jenis} dengan nomor {$surat->nomor_surat} berhasil dibuat dan diterbitkan!");
    }

    /**
     * Cetak Banyak Surat Sekaligus (Batch / Bulk Print)
     */
    public function cetakMassal(Request $request)
    {
        $ids = [];
        if ($request->filled('ids')) {
            if (is_array($request->input('ids'))) {
                $ids = $request->input('ids');
            } else {
                $ids = explode(',', $request->input('ids'));
            }
        }

        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return redirect()->route('surat-keluar.index')->with('error', 'Pilih setidaknya satu surat untuk dicetak.');
        }

        $surats = SuratKeluar::with([
            'tahunPelajaran',
            'creator',
            'waliMurid.kampung'
        ])
            ->whereIn('id', $ids)
            ->orderBy('id', 'asc')
            ->get();

        if ($surats->isEmpty()) {
            return redirect()->route('surat-keluar.index')->with('error', 'Data surat tidak ditemukan.');
        }

        return view('persuratan.surat-keluar.cetak-massal', compact('surats'));
    }

    /**
     * Hapus Banyak Surat Sekaligus (Bulk Destroy)
     */
    public function destroyMassal(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_filter(array_map('intval', (array) $ids));

        if (!empty($ids)) {
            $count = SuratKeluar::whereIn('id', $ids)->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$count} data surat terpilih."
                ]);
            }

            return redirect()->route('surat-keluar.index')->with('success', "Berhasil menghapus {$count} data surat terpilih.");
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada surat yang dipilih untuk dihapus.'
            ], 422);
        }

        return redirect()->route('surat-keluar.index')->with('error', 'Tidak ada surat yang dipilih untuk dihapus.');
    }

    /**
     * Tampilkan detail surat / pratinjau
     */
    public function show($id)
    {
        $surat = SuratKeluar::with([
            'tahunPelajaran',
            'creator',
            'waliMurid.kampung'
        ])->findOrFail($id);

        return view('persuratan.surat-keluar.show', compact('surat'));
    }

    /**
     * Tampilkan form edit surat
     */
    public function edit($id)
    {
        $surat = SuratKeluar::with([
            'tahunPelajaran',
            'creator',
            'waliMurid.kampung'
        ])->findOrFail($id);
        $selectedJenis = $surat->jenis_surat;

        $tahunAktif = $surat->tahunPelajaran
            ?? TahunPelajaran::where('is_active', true)->first();

        // Ambil data murid aktif dari pivot MuridRuanganRepository
        $murids = $this->muridRuanganRepo->getAllMuridAktifWithRuanganByTahun($tahunAktif?->id);

        // Ambil data Ruangan untuk filter, diurutkan berdasarkan level->urutan_level
        $ruangans = Ruangan::leftJoin('levels', 'ruangans.level_id', '=', 'levels.id')
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('ruangans.nama_ruangan', 'asc')
            ->select('ruangans.*')
            ->get();
        $penandatanganConfig = $this->getDefaultPenandatanganConfig($surat);
        $penandatanganList = $penandatanganConfig['signers'];
        $daftarJenis = SuratKeluar::DAFTAR_JENIS_SURAT;

        return view('persuratan.surat-keluar.edit', compact(
            'surat',
            'selectedJenis',
            'tahunAktif',
            'murids',
            'ruangans',
            'penandatanganList',
            'penandatanganConfig',
            'daftarJenis'
        ));
    }

    /**
     * Perbarui data surat keluar
     */
    public function update(Request $request, $id)
    {
        $surat = SuratKeluar::findOrFail($id);

        $request->validate([
            'nomor_surat'           => 'required|string|max:100|unique:surat_keluars,nomor_surat,' . $surat->id,
            'perihal'               => 'required|string|max:255',
            'tujuan_surat'          => 'required|string|max:255',
            'tanggal_surat'         => 'required|date',
        ]);

        $isiSpesifik = $this->buildIsiSpesifik($request, $surat->jenis_surat);

        $muridIds = [];
        if ($request->filled('murid_ids')) {
            $muridIds = array_values(array_filter(array_map('intval', (array)$request->input('murid_ids'))));
        } elseif ($request->filled('murid_id')) {
            $muridIds = [(int)$request->input('murid_id')];
        }

        $waliMuridId = $surat->wali_murid_id;
        if (!empty($muridIds)) {
            $firstMurid = Murid::find($muridIds[0]);
            $waliMuridId = $firstMurid?->wali_murid_id;
        }

        // Parse penandatangan list
        $penandatanganList = $request->input('penandatangan_list');
        if (is_string($penandatanganList)) {
            $penandatanganList = json_decode($penandatanganList, true);
        }
        if (!is_array($penandatanganList) || empty($penandatanganList)) {
            $penandatanganConfig = $this->getDefaultPenandatanganConfig($surat);
            $penandatanganList = $penandatanganConfig['signers'];
        }

        $firstActive = collect($penandatanganList)->first(function ($s) {
            return !isset($s['is_active']) || $s['is_active'] === true || $s['is_active'] === '1' || $s['is_active'] === 1 || $s['is_active'] === 'true';
        });

        $penandatanganNama = $firstActive['nama'] ?? trim($request->input('penandatangan_nama', $surat->penandatangan_nama));
        $penandatanganJabatan = $firstActive['jabatan'] ?? trim($request->input('penandatangan_jabatan', $surat->penandatangan_jabatan));
        $penandatanganNip = $firstActive['nip'] ?? $request->input('penandatangan_nip', $surat->penandatangan_nip);

        $hasLampiran = $request->boolean('has_lampiran') || $request->input('has_lampiran') === '1' || $request->input('has_lampiran') === 'true';
        $lampiran = $request->input('lampiran');
        if (empty($lampiran) || $lampiran === '-') {
            $lampiran = $hasLampiran ? '1 Lembar' : '-';
        }

        $surat->update([
            'nomor_surat'           => trim($request->input('nomor_surat')),
            'perihal'               => trim($request->input('perihal')),
            'lampiran'              => $lampiran,
            'sifat_surat'           => $request->input('sifat_surat', 'Biasa'),
            'tujuan_surat'          => trim($request->input('tujuan_surat')),
            'alamat_tujuan'         => $request->input('alamat_tujuan'),
            'kategori_penerima'     => $request->input('kategori_penerima', $surat->kategori_penerima),
            'murid_ids'             => !empty($muridIds) ? $muridIds : null,
            'wali_murid_id'         => $waliMuridId,
            'tahun_pelajaran_id'    => $request->input('tahun_pelajaran_id', $surat->tahun_pelajaran_id),
            'tanggal_surat'         => $request->input('tanggal_surat'),
            'tanggal_hijriyah'      => $request->input('tanggal_hijriyah'),
            'tempat_terbit'         => $request->input('tempat_terbit', 'Bangkalan'),
            'isi_spesifik'          => $isiSpesifik,
            'isi_surat'             => $request->input('isi_surat'),
            'tembusan'              => $request->input('tembusan'),
            'penandatangan_list'    => $penandatanganList,
            'penandatangan_nama'    => $penandatanganNama,
            'penandatangan_jabatan' => $penandatanganJabatan,
            'penandatangan_nip'     => $penandatanganNip,
            'status'                => $request->input('status', $surat->status),
        ]);

        return redirect()->route('surat-keluar.show', $surat->id)
            ->with('success', "Surat nomor {$surat->nomor_surat} berhasil diperbarui.");
    }

    /**
     * Hapus data surat keluar
     */
    public function destroy($id)
    {
        $surat = SuratKeluar::findOrFail($id);
        $nomor = $surat->nomor_surat;
        $surat->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Surat nomor {$nomor} berhasil dihapus."
            ]);
        }

        return redirect()->route('surat-keluar.index')
            ->with('success', "Surat nomor {$nomor} berhasil dihapus.");
    }

    /**
     * Halaman cetak resmi dokumen surat ber-KOP MDTHS
     */
    public function cetak($id)
    {
        $surat = SuratKeluar::with([
            'tahunPelajaran',
            'creator',
            'waliMurid.kampung'
        ])->findOrFail($id);

        return view('persuratan.surat-keluar.cetak', compact('surat'));
    }

    /**
     * AJAX Endpoint: Ambil detail data murid untuk autofill
     */
    public function apiGetMuridDetail($id)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $murid = Murid::with([
            'waliMurid.kampung',
            'ruangans' => function ($q) use ($tahunAktif) {
                if ($tahunAktif) {
                    $q->where('murid_ruangans.tahun_pelajaran_id', $tahunAktif->id);
                }
            },
            'ruanganMasuk'
        ])->find($id);

        if (!$murid) {
            return response()->json(['success' => false, 'message' => 'Data murid tidak ditemukan'], 404);
        }

        $activeRuangan = $murid->ruangans->first() ?? $murid->ruanganMasuk;
        $namaRuangan = $activeRuangan?->nama_ruangan ?? ($murid->nama_ruangan_aktif ?? '-');
        $wali = $murid->waliMurid;
        $namaWali = $murid->nama_ayah ?: ($wali?->nama_kepala_keluarga ?: ($murid->nama_ibu ?: '-'));
        $kampung = $wali?->kampung?->nama_kampung ?? ($murid->alamat ?? '-');

        return response()->json([
            'success'       => true,
            'data'          => [
                'id'            => $murid->id,
                'nism'          => $murid->nism,
                'nama_lengkap'  => $murid->nama_lengkap,
                'jenis_kelamin' => $murid->jenis_kelamin,
                'tempat_lahir'  => $murid->tempat_lahir,
                'tanggal_lahir' => $murid->tanggal_lahir ? Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-',
                'ttl'           => trim(($murid->tempat_lahir ? $murid->tempat_lahir . ', ' : '') . ($murid->tanggal_lahir ? Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-')),
                'ruangan'       => $namaRuangan,
                'ruangan_id'    => $activeRuangan?->id ?? null,
                'nama_wali'     => $namaWali,
                'nama_ayah'     => $murid->nama_ayah ?: '-',
                'nama_ibu'      => $murid->nama_ibu ?: '-',
                'alamat'        => $kampung,
                'no_hp'         => $wali?->no_hp ?? '-',
            ]
        ]);
    }

    /**
     * AJAX Endpoint: Ambil daftar murid aktif berdasarkan ruangan dari MuridRuanganRepository
     */
    public function apiGetMuridByRuangan(Request $request, $ruangan_id = null)
    {
        $ruanganId = $ruangan_id ?: $request->query('ruangan_id');
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $request->query('tahun_pelajaran_id') ?: ($tahunAktif?->id ?? 1);

        if ($ruanganId) {
            $murids = $this->muridRuanganRepo->getMuridAktifByRuanganAndTahun($ruanganId, $tahunId, ['waliMurid.kampung', 'ruangans']);
        } else {
            $murids = $this->muridRuanganRepo->getAllMuridAktifWithRuanganByTahun($tahunId);
        }

        $data = $murids->map(function ($m) {
            $wali = $m->waliMurid;
            $namaWali = $m->nama_ayah ?: ($wali?->nama_kepala_keluarga ?: ($m->nama_ibu ?: ($wali?->nama_lengkap ?: '-')));
            $kampung = $wali?->kampung?->nama_kampung ?? ($m->alamat ?? 'Somorkoneng');
            $activeRuangan = $m->ruangans->first() ?? $m->ruanganMasuk;
            return [
                'id'            => $m->id,
                'nama_lengkap'  => $m->nama_lengkap,
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'nism'          => $m->nism ?? '-',
                'ruangan_id'    => $activeRuangan?->id ?? null,
                'ruangan_nama'  => $activeRuangan?->nama_ruangan ?? ($m->nama_ruangan_aktif ?? '-'),
                'nama_wali'     => $namaWali,
                'alamat'        => $kampung,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * AJAX Endpoint: Ambil detail data ustadz untuk autofill
     */
    public function apiGetUstadzDetail($id)
    {
        $ustadz = Ustadz::find($id);

        if (!$ustadz) {
            return response()->json(['success' => false, 'message' => 'Data ustadz tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $ustadz->id,
                'nip'           => $ustadz->nigm ?? $ustadz->nip ?? $ustadz->kode_ustadz ?? '-',
                'nama_lengkap'  => $ustadz->nama_lengkap,
                'telepon'       => $ustadz->no_hp ?? $ustadz->telepon ?? '-',
                'alamat'        => $ustadz->alamat ?? '-',
                'jenis_kelamin' => $ustadz->jenis_kelamin ?? 'Laki-laki',
                'tempat_lahir'  => $ustadz->tempat_lahir ?? '-',
                'tanggal_lahir' => $ustadz->tanggal_lahir ? Carbon::parse($ustadz->tanggal_lahir)->translatedFormat('d F Y') : '-',
            ]
        ]);
    }

    /**
     * AJAX Endpoint: Generate nomor surat secara dinamis
     */
    public function apiGenerateNomor(Request $request)
    {
        $jenis = $request->query('jenis', 'surat_panggilan');
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        $gen = SuratKeluar::generateNomorSurat($jenis, $tanggal);
        return response()->json($gen);
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Olah payload data spesifik berdasarkan 7 jenis surat
     */
    private function buildIsiSpesifik(Request $request, string $jenisSurat): array
    {
        $data = [];

        switch ($jenisSurat) {
            case 'surat_panggilan':
                $data = [
                    'hari_panggilan'      => $request->input('hari_panggilan'),
                    'tanggal_panggilan'   => $request->input('tanggal_panggilan'),
                    'waktu_panggilan'     => $request->input('waktu_panggilan', '14.00 WIB s/d Selesai'),
                    'tempat_menghadap'    => $request->input('tempat_menghadap', 'Kantor TU / Ruang Guru MDT Hidayatus Shibyan'),
                    'menghadap_kepada'    => $request->input('menghadap_kepada', 'Kepala Madrasah & Tim Kesiswaan'),
                    'alasan_panggilan'    => $request->input('alasan_panggilan'),
                    'keterangan_tambahan' => $request->input('keterangan_tambahan'),
                ];
                break;

            case 'surat_peringatan':
                $data = [
                    'tingkat_sp'             => $request->input('tingkat_sp', 'Surat Peringatan I (SP 1)'),
                    'alasan_sp'              => $request->input('alasan_sp'),
                    'poin_tatib_dilanggar'   => $request->input('poin_tatib_dilanggar'),
                    'bentuk_pelanggaran'     => $request->input('bentuk_pelanggaran'),
                    'tindakan_pembinaan'     => $request->input('tindakan_pembinaan'),
                    'batas_waktu_pembinaan'  => $request->input('batas_waktu_pembinaan'),
                    'konsekuensi_berikutnya' => $request->input('konsekuensi_berikutnya'),
                ];
                break;

            case 'surat_pemberitahuan':
                $data = [
                    'kategori_pemberitahuan' => $request->input('kategori_pemberitahuan', 'Akademik & KBM'),
                    'pokok_pemberitahuan'    => $request->input('pokok_pemberitahuan'),
                    'jadwal_terkait'         => $request->input('jadwal_terkait'),
                    'ketentuan_tambahan'     => $request->input('ketentuan_tambahan'),
                ];
                break;

            case 'surat_edaran':
                $data = [
                    'nomor_edaran_internal' => $request->input('nomor_edaran_internal'),
                    'pokok_maklumat'        => $request->input('pokok_maklumat'),
                    'instruksi_poin'        => $request->input('instruksi_poin'), // Array or text
                    'berlaku_mulai'         => $request->input('berlaku_mulai'),
                    'ketentuan_penutup'     => $request->input('ketentuan_penutup'),
                ];
                break;

            case 'surat_permohonan_izin':
                $data = [
                    'nama_kegiatan'         => $request->input('nama_kegiatan'),
                    'hari_kegiatan'         => $request->input('hari_kegiatan'),
                    'tanggal_kegiatan'      => $request->input('tanggal_kegiatan'),
                    'waktu_kegiatan'        => $request->input('waktu_kegiatan'),
                    'tempat_kegiatan'       => $request->input('tempat_kegiatan'),
                    'fasilitas_dimohonkan'  => $request->input('fasilitas_dimohonkan'),
                    'penanggung_jawab'      => $request->input('penanggung_jawab'),
                ];
                break;

            case 'surat_dispensasi':
                $rawMuridIds = $request->input('murid_ids', []);
                if (empty($rawMuridIds) && $request->filled('murid_id')) {
                    $rawMuridIds = [$request->input('murid_id')];
                }
                $muridIds = array_values(array_unique(array_filter(array_map('intval', (array)$rawMuridIds))));
                $kelasLembagaInputs = $request->input('kelas_lembaga', []);
                $muridDispensasiList = [];
                if (!empty($muridIds)) {
                    foreach ($muridIds as $mId) {
                        $m = Murid::with(['ruangans', 'ruanganMasuk'])->find($mId);
                        if ($m) {
                            $kl = $kelasLembagaInputs[$mId] ?? ($request->input("kelas_lembaga_{$mId}") ?: '-');
                            $muridDispensasiList[] = [
                                'murid_id'      => $m->id,
                                'nama_lengkap'  => $m->nama_lengkap,
                                'nism'          => $m->nism,
                                'ruangan'       => $m->nama_ruangan_aktif ?? '-',
                                'kelas_lembaga' => $kl ?: '-'
                            ];
                        }
                    }
                }

                $data = [
                    'nama_kegiatan_dispensasi'     => $request->input('nama_kegiatan_dispensasi', 'Ujian Ulangan ke 1 (Imtihan Dauri 1)'),
                    'hari_kegiatan_dispensasi'     => $request->input('hari_kegiatan_dispensasi', 'Sabtu s.d. Kamis'),
                    'tanggal_mulai_dispensasi'     => $request->input('tanggal_mulai_dispensasi'),
                    'tanggal_selesai_dispensasi'    => $request->input('tanggal_selesai_dispensasi'),
                    'waktu_kegiatan_dispensasi'    => $request->input('waktu_kegiatan_dispensasi', '13.00 s.d. 16.00 WIB'),
                    'tempat_kegiatan_dispensasi'   => $request->input('tempat_kegiatan_dispensasi', 'MDT Hidayatus Shibyan'),
                    'permohonan_dispensasi_khusus' => $request->input('permohonan_dispensasi_khusus', 'dipulangkan pukul 12.00 WIB agar Murid dapat mempersiapkan diri untuk mengikuti IMDA 1'),
                    'nama_sekolah_tujuan'          => $request->input('nama_sekolah_tujuan'),
                    'jumlah_hari'                  => $request->input('jumlah_hari'),
                    'alasan_kegiatan'              => $request->input('alasan_kegiatan'),
                    'murid_dispensasi_list'        => $muridDispensasiList,
                ];
                break;

            case 'surat_undangan':
                $data = [
                    'nama_acara'          => $request->input('nama_acara'),
                    'hari_acara'          => $request->input('hari_acara'),
                    'tanggal_acara'       => $request->input('tanggal_acara'),
                    'waktu_acara'         => $request->input('waktu_acara', '19.30 WIB (Ba\'da Isya) s/d Selesai'),
                    'tempat_acara'        => $request->input('tempat_acara', 'Aula MDT Hidayatus Shibyan'),
                    'pakaian_dresscode'   => $request->input('pakaian_dresscode', 'Busana Muslim Rapi & Berpeci'),
                    'agenda_acara'        => $request->input('agenda_acara'),
                ];
                break;
        }

        // Lampiran Resmi Tambahan (Halaman 2)
        $hasLampiran = $request->boolean('has_lampiran') || $request->input('has_lampiran') === '1' || $request->input('has_lampiran') === 'true';
        $data['has_lampiran'] = $hasLampiran;

        $lampiranJudul = $request->input('lampiran_judul');
        if ($jenisSurat === 'surat_dispensasi') {
            $namaKeg = !empty($data['nama_kegiatan_dispensasi']) ? strtoupper(trim((string)$data['nama_kegiatan_dispensasi'])) : 'KEGIATAN';
            if (empty($lampiranJudul) || in_array($lampiranJudul, ['SUSUNAN ACARA & JADWAL KEGIATAN', 'RINCIAN LAMPIRAN SURAT', 'Surat Keluar', 'KETENTUAN & TATA TERTIB RESMI'])) {
                $lampiranJudul = "NAMA-NAMA MURID YANG MENGIKUTI {$namaKeg}";
            }
        }
        $data['lampiran_judul'] = $lampiranJudul;
        $data['lampiran_konten'] = $request->input('lampiran_konten');

        return $data;
    }

    /**
     * Dapatkan konfigurasi default 4 pejabat penandatangan madrasah
     * (Pengasuh, Sekretaris Jenderal, Kepala Bidang, Administrator)
     */
    private function getDefaultPenandatanganConfig(?SuratKeluar $surat = null): array
    {
        // 1. Pengasuh
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh') ?? Pengurus::getAktifByJabatan('Ketua');
        $pengasuhNama = $pengasuh?->anggota?->nama_lengkap ?? $pengasuh?->anggota?->ustadz?->nama_lengkap ?? 'K.H. ABDUL FATTAH';
        $pengasuhNip = $pengasuh?->anggota?->ustadz?->nip ?? '-';
        $pengasuhId = $pengasuh?->id ?? 2;

        // 2. Sekretaris Jenderal
        $sekretaris = Pengurus::getAktifByJabatan('Sekretaris Jenderal') ?? Pengurus::getAktifByJabatan('Sekretaris');
        $sekretarisNama = $sekretaris?->anggota?->nama_lengkap ?? $sekretaris?->anggota?->ustadz?->nama_lengkap ?? 'MIKYAL ADLY';
        $sekretarisNip = $sekretaris?->anggota?->ustadz?->nip ?? '-';
        $sekretarisId = $sekretaris?->id ?? 3;

        // 3. Kepala Bidang (Ambil semua opsi kabid dari pengurus)
        $kabidList = Pengurus::whereHas('jabatan', function ($q) {
            $q->where('nama_jabatan', 'LIKE', '%Kepala Bidang%');
        })->with(['anggota.ustadz', 'jabatan', 'tingkat'])->get();

        $defaultKabid = $kabidList->first();
        $kabidNama = $defaultKabid?->anggota?->nama_lengkap ?? $defaultKabid?->anggota?->ustadz?->nama_lengkap ?? 'KHOIRUS SHOLEH';
        $kabidJabatan = $defaultKabid?->jabatan?->nama_jabatan ?? 'Kepala Bidang Pendidikan';
        $kabidNip = $defaultKabid?->anggota?->ustadz?->nip ?? '-';
        $kabidId = $defaultKabid?->id ?? 4;

        // 4. Administrator (Ambil semua opsi admin)
        $adminList = Administrator::where('is_active', true)->get();
        $defaultAdmin = Administrator::getTandaTanganAdmin(null) ?? $adminList->first();
        $adminNama = $defaultAdmin?->nama_lengkap ?? 'MIKYAL ADLY';
        $adminJabatan = $defaultAdmin?->jabatan ?: 'Administrator';
        $adminNip = $defaultAdmin?->nip ?? '-';
        $adminId = $defaultAdmin?->id ?? 1;

        $defaultSigners = [
            [
                'key'         => 'pengasuh',
                'title'       => 'Pengasuh Madrasah',
                'is_active'   => true,
                'label_atas'  => 'Mengesahkan,',
                'jabatan'     => 'Pengasuh MDT Hidayatus Shibyan',
                'nama'        => $pengasuhNama,
                'nip'         => $pengasuhNip,
                'tipe_relasi' => 'pengurus',
                'id_relasi'   => $pengasuhId,
            ],
            [
                'key'         => 'sekretaris',
                'title'       => 'Sekretaris Jenderal',
                'is_active'   => true,
                'label_atas'  => 'Mengetahui,',
                'jabatan'     => 'Sekretaris Jenderal',
                'nama'        => $sekretarisNama,
                'nip'         => $sekretarisNip,
                'tipe_relasi' => 'pengurus',
                'id_relasi'   => $sekretarisId,
            ],
            [
                'key'         => 'kabid',
                'title'       => 'Kepala Bidang',
                'is_active'   => false,
                'label_atas'  => 'Mengetahui,',
                'jabatan'     => $kabidJabatan,
                'nama'        => $kabidNama,
                'nip'         => $kabidNip,
                'tipe_relasi' => 'pengurus',
                'id_relasi'   => $kabidId,
            ],
            [
                'key'         => 'admin',
                'title'       => 'Administrator',
                'is_active'   => false,
                'label_atas'  => 'Ditetapkan di : Bangkalan',
                'jabatan'     => $adminJabatan,
                'nama'        => $adminNama,
                'nip'         => $adminNip,
                'tipe_relasi' => 'administrator',
                'id_relasi'   => $adminId,
            ],
        ];

        // Jika surat sudah memiliki konfigurasi penandatangan kustom, sesuaikan
        if ($surat && is_array($surat->penandatangan_list) && count($surat->penandatangan_list) > 0) {
            $existing = collect($surat->penandatangan_list)->keyBy('key');
            $mergedSigners = [];
            foreach ($defaultSigners as $def) {
                if ($existing->has($def['key'])) {
                    $item = $existing->get($def['key']);
                    $mergedSigners[] = array_merge($def, $item);
                } else {
                    $mergedSigners[] = $def;
                }
            }
            $defaultSigners = $mergedSigners;
        }

        return [
            'signers' => $defaultSigners,
            'kabid_options' => $kabidList->map(function ($k) {
                return [
                    'id'      => $k->id,
                    'nama'    => $k->anggota?->nama_lengkap ?? $k->anggota?->ustadz?->nama_lengkap ?? '-',
                    'jabatan' => $k->jabatan?->nama_jabatan ?? 'Kepala Bidang',
                    'nip'     => $k->anggota?->ustadz?->nip ?? '-',
                    'tingkat' => $k->tingkat?->nama_tingkat ?? null,
                ];
            })->values()->toArray(),
            'admin_options' => $adminList->map(function ($a) {
                return [
                    'id'      => $a->id,
                    'nama'    => $a->nama_lengkap,
                    'jabatan' => $a->jabatan ?: 'Administrator',
                    'nip'     => $a->nip ?? '-',
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Dapatkan daftar pejabat penandatangan madrasah (Legacy Helper)
     */
    private function getPejabatPenandatanganList(): array
    {
        $config = $this->getDefaultPenandatanganConfig();
        return $config['signers'];
    }
}
