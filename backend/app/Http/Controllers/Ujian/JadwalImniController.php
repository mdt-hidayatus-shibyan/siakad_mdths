<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Level;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalImniController extends Controller
{
    /**
     * Halaman Utama Matriks Jadwal Ujian IMNI (Persis seperti Jadwal Ujian)
     */
    public function index(Request $request)
    {
        // 1. Tentukan Tahun Pelajaran Terpilih
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $tahunPelajaranId = $request->input('tahun_id', $tahunAktif?->id);
        $selectedTahun = $daftarTahun->firstWhere('id', $tahunPelajaranId) ?? $tahunAktif;

        // 2. Dapatkan Ujian IMNI
        $ujianImni = $this->getOrCreateUjianImni($tahunPelajaranId);

        // 3. Ambil Level Kelas Akhir (3 TPQ, 6 IBT, 3 TSA) dengan relasi Tingkat
        $levels = Level::with(['tingkat'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            })
            ->orderBy('urutan_level', 'asc')
            ->get();

        $levelIds = $levels->pluck('id')->toArray();

        // 4. Ambil Data Jadwal Ujian IMNI
        $jadwalRaw = JadwalUjian::with(['mataPelajaran', 'pengawas', 'level', 'ujian.tahunPelajaran'])
            ->where('ujian_id', $ujianImni->id)
            ->whereIn('level_id', $levelIds)
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // 5. Matriks & Deteksi Bentrok Pengawas
        $matrix = [];
        $checkBentrok = [];
        $bentrokJadwalIds = [];

        foreach ($jadwalRaw as $jadwal) {
            $tanggal = Carbon::parse($jadwal->tanggal_ujian)->format('Y-m-d');
            $waktu = Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i');

            if ($jadwal->level_id) {
                $matrix[$tanggal][$waktu][$jadwal->level_id] = $jadwal;
            }

            // Cek bentrok pengawas
            if ($jadwal->ustadz_id) {
                $checkBentrok[$tanggal][$waktu][$jadwal->ustadz_id][] = $jadwal->id;
            }
        }

        foreach ($checkBentrok as $tanggal => $waktuData) {
            foreach ($waktuData as $waktu => $asatidzData) {
                foreach ($asatidzData as $asatidzId => $jadwalIds) {
                    if (count($jadwalIds) > 1) {
                        foreach ($jadwalIds as $id) {
                            $bentrokJadwalIds[$id] = true;
                        }
                    }
                }
            }
        }

        return view('ujian.panitia-imni.jadwal.index', compact(
            'levels',
            'matrix',
            'bentrokJadwalIds',
            'daftarTahun',
            'tahunPelajaranId',
            'ujianImni',
            'selectedTahun'
        ));
    }

    /**
     * Tampilkan Modal Pengaturan Master Agenda Ujian IMNI (AJAX)
     */
    public function modalAgenda(Request $request)
    {
        $tahunId = $request->input('tahun_id', TahunPelajaran::where('is_active', true)->value('id'));
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $selectedTahun = $daftarTahun->firstWhere('id', $tahunId) ?? $daftarTahun->first();

        $ujianImni = $this->getOrCreateUjianImni($tahunId);
        $semesters = Semester::where('tahun_pelajaran_id', $tahunId)->get();
        if ($semesters->isEmpty()) {
            $semesters = Semester::all();
        }

        if ($request->ajax()) {
            return view('ujian.panitia-imni.jadwal.modal-agenda', compact(
                'tahunId',
                'selectedTahun',
                'ujianImni',
                'semesters'
            ));
        }

        return redirect()->route('jadwal-imni.index', ['tahun_id' => $tahunId]);
    }

    /**
     * Simpan Pengaturan Master Agenda Ujian IMNI
     */
    public function updateAgenda(Request $request)
    {
        $request->validate([
            'ujian_id'           => 'required|exists:ujians,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'nama_ujian'         => 'required|string|max:255',
            'semester_id'        => 'required|exists:semesters,id',
            'tanggal_mulai'      => 'required|date',
            'tanggal_selesai'    => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'         => 'nullable|string|max:500',
        ]);

        $ujian = Ujian::findOrFail($request->ujian_id);
        $ujian->update([
            'nama_ujian'      => $request->nama_ujian,
            'semester_id'     => $request->semester_id,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan'      => $request->keterangan,
        ]);

        $msg = "Master Agenda Ujian IMNI berhasil diperbarui. Rentang tanggal ujian telah disesuaikan.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'data'    => $ujian
            ], 200);
        }

        return redirect()->route('jadwal-imni.index', ['tahun_id' => $request->tahun_pelajaran_id])->with('success', $msg);
    }

    /**
     * Kertas Kerja Kelola Jadwal Ujian IMNI (Persis seperti Jadwal Ujian Create)
     */
    public function create(Request $request)
    {
        $tahun_pelajaran_id = $request->tahun_pelajaran_id;
        $level_id = $request->level_id;

        // Jika tidak ada filter tahun, cari Tahun Pelajaran yang statusnya aktif
        if (!$tahun_pelajaran_id) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahun_pelajaran_id = $tahunAktif ? $tahunAktif->id : null;
        }

        $tahunPelajarans = TahunPelajaran::orderBy('id', 'desc')->get();

        // Level Kelas Akhir (3 TPQ, 6 IBT, 3 TSA)
        $levels = Level::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            })
            ->orderBy('urutan_level', 'asc')
            ->get();

        $ujianImni = null;
        $dates = [];
        $existingJadwal = collect();
        $mapels = collect();

        if ($tahun_pelajaran_id) {
            $ujianImni = $this->getOrCreateUjianImni($tahun_pelajaran_id);

            if ($level_id) {
                if ($ujianImni && $ujianImni->tanggal_mulai && $ujianImni->tanggal_selesai) {
                    $start = Carbon::parse($ujianImni->tanggal_mulai);
                    $end = Carbon::parse($ujianImni->tanggal_selesai);

                    for ($d = $start; $d->lte($end); $d->addDay()) {
                        if ($d->isFriday()) continue; // Skip Jum'at
                        $dates[] = $d->format('Y-m-d');
                    }
                }

                // Tarik jadwal yang sudah ada berdasarkan LEVEL
                $existingJadwal = JadwalUjian::where('ujian_id', $ujianImni->id)
                    ->where('level_id', $level_id)
                    ->orderBy('waktu_mulai', 'asc')
                    ->get()
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->tanggal_ujian)->format('Y-m-d');
                    });

                // Ambil Mapel langsung dari level_id
                $mapels = MataPelajaran::where('level_id', $level_id)
                    ->orderBy('nama_mapel')
                    ->get();
            }
        }

        $pengawas = Ustadz::orderBy('nama_lengkap')->get();

        return view('ujian.panitia-imni.jadwal.create', compact(
            'tahun_pelajaran_id',
            'level_id',
            'tahunPelajarans',
            'levels',
            'ujianImni',
            'dates',
            'existingJadwal',
            'mapels',
            'pengawas'
        ));
    }

    /**
     * Simpan Massal Kertas Kerja Jadwal Ujian IMNI
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'level_id'           => 'required|exists:levels,id',
            'jadwal'             => 'required|array'
        ]);

        $ujianImni = $this->getOrCreateUjianImni($request->tahun_pelajaran_id);
        $level = Level::findOrFail($request->level_id);

        DB::transaction(function () use ($request, $ujianImni) {
            // Hapus jadwal lama untuk ujian & LEVEL ini
            JadwalUjian::where('ujian_id', $ujianImni->id)
                ->where('level_id', $request->level_id)
                ->delete();

            foreach ($request->jadwal as $tanggal => $sesions) {
                foreach ($sesions as $sesi => $data) {
                    if (!empty($data['waktu_mulai']) && (!empty($data['mata_pelajaran_id']) || !empty($data['nama_mata_pelajaran_custom']))) {
                        JadwalUjian::create([
                            'ujian_id'                   => $ujianImni->id,
                            'level_id'                   => $request->level_id,
                            'tanggal_ujian'              => $tanggal,
                            'waktu_mulai'                => $data['waktu_mulai'],
                            'waktu_selesai'              => $data['waktu_selesai'] ?? null,
                            'mata_pelajaran_id'          => $data['mata_pelajaran_id'] ?? null,
                            'nama_mata_pelajaran_custom' => $data['nama_mata_pelajaran_custom'] ?? null,
                            'ustadz_id'                  => $data['ustadz_id'] ?? null,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', "Kertas Kerja Jadwal Ujian IMNI Kelas {$level->nama_level} berhasil disimpan massal!");
    }

    /**
     * Cetak Lembar Dokumen Resmi Jadwal Pelaksanaan IMNI
     */
    public function cetak(Request $request)
    {
        $tahunId = $request->input('tahun_id') ?? $request->input('tahun_pelajaran_id');
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $selectedTahunId = $tahunId ?? $tahunAktif?->id;
        $selectedTahun = $daftarTahun->firstWhere('id', $selectedTahunId) ?? $tahunAktif;

        $ujianImni = $this->getOrCreateUjianImni($selectedTahunId);

        $levels = Level::with('tingkat')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_level', 'LIKE', '%3%TPQ%')
                    ->orWhere('nama_level', 'LIKE', '%6%IBT%')
                    ->orWhere('nama_level', 'LIKE', '%3%TSA%')
                    ->orWhereIn('urutan_level', [3, 9, 12]);
            })
            ->orderBy('urutan_level', 'asc')
            ->get();

        if ($request->filled('tingkat_id')) {
            $levels = $levels->where('tingkat_id', $request->tingkat_id)->values();
        }

        $levelIds = $levels->pluck('id')->toArray();

        $jadwals = JadwalUjian::with(['mataPelajaran', 'pengawas', 'level.tingkat'])
            ->where('ujian_id', $ujianImni->id)
            ->whereIn('level_id', $levelIds)
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Matriks Jadwal
        $matrix = [];
        $daftarTanggal = [];
        foreach ($jadwals as $j) {
            $tgl = Carbon::parse($j->tanggal_ujian)->format('Y-m-d');
            $waktu = Carbon::parse($j->waktu_mulai)->format('H:i') . ' - ' . ($j->waktu_selesai ? Carbon::parse($j->waktu_selesai)->format('H:i') : 'Selesai');
            $matrix[$tgl][$waktu][$j->level_id] = $j;
            if (!in_array($tgl, $daftarTanggal)) {
                $daftarTanggal[] = $tgl;
            }
        }
        sort($daftarTanggal);

        $ketuaPanitia = PanitiaImni::getKetua($selectedTahunId);
        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh') ?? Pengurus::getAktifByJabatan('Kepala Madrasah');

        return view('ujian.panitia-imni.jadwal.cetak-jadwal', compact(
            'selectedTahun',
            'ujianImni',
            'levels',
            'jadwals',
            'matrix',
            'daftarTanggal',
            'ketuaPanitia',
            'pengasuh'
        ));
    }

    /**
     * Helper: Dapatkan atau buat agenda Ujian bertipe IMNI untuk tahun ajaran terpilih
     */
    private function getOrCreateUjianImni($tahunPelajaranId)
    {
        $tp = TahunPelajaran::find($tahunPelajaranId) ?? TahunPelajaran::where('is_active', true)->first();
        $semesters = Semester::where('tahun_pelajaran_id', $tp?->id)->get();
        $sem2 = $semesters->first(fn($s) => str_contains($s->nama_semester, '2') || str_contains(strtolower($s->nama_semester), 'genap')) ?? $semesters->last();

        return Ujian::firstOrCreate(
            [
                'tahun_pelajaran_id' => $tp->id,
                'tipe_ujian'         => 'IMNI',
            ],
            [
                'nama_ujian'         => "Imtihan Niha'i (IMNI) TP. {$tp->nama_hijriyah}",
                'semester_id'        => $sem2?->id ?? 2,
                'tanggal_mulai'      => now()->format('Y-m-d'),
                'tanggal_selesai'    => now()->addDays(6)->format('Y-m-d'),
            ]
        );
    }
}
