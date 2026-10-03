<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use App\Models\Ujian\JadwalUjian;
use App\Models\Ujian\Ujian;
use App\Models\Ustadz;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JadwalUjianController extends Controller
{



    public function index(Request $request)
    {
        // =========================================================================
        // 1. TENTUKAN TAHUN PELAJARAN & UJIAN YANG MAU DITAMPILKAN
        // =========================================================================
        $tahunPelajaranId = $request->input('tahun_id');
        $ujianId = $request->input('ujian_id'); // Tambahan filter spesifik ujian (opsional)

        // Jika tidak ada filter tahun, cari Tahun Pelajaran yang statusnya aktif
        if (!$tahunPelajaranId) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahunPelajaranId = $tahunAktif ? $tahunAktif->id : null;
        }

        // =========================================================================
        // 2. AMBIL DATA LEVEL / KELAS (Sebagai pengganti Ruangan)
        // =========================================================================
        // Ambil data level beserta tingkatnya untuk keperluan navigasi Tab Tingkat
        $levelsQuery = Level::with(['tingkat'])
            ->orderBy('tingkat_id')
            ->orderBy('nama_level');

        if ($ujianId) {
            $ujian = Ujian::find($ujianId);
            if ($ujian) {
                if (in_array($ujian->tipe_ujian, ['IMDA 2', 'IMDA 3'])) {
                    $levelsQuery->whereNotIn('nama_level', ['3 TPQ', '6 IBT', '3 TSA']);
                }
                if ($ujian->tingkat_id) {
                    $levelsQuery->where('tingkat_id', $ujian->tingkat_id);
                }
            }
        }

        $levels = $levelsQuery->get();

        // =========================================================================
        // 3. AMBIL JADWAL UJIAN (DIFILTER BERDASARKAN TAHUN / UJIAN)
        // =========================================================================
        $jadwalQuery = JadwalUjian::with(['mataPelajaran', 'pengawas', 'level', 'ujian.tahunPelajaran'])
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc');

        // Filter berdasarkan Tahun Pelajaran (melalui relasi Ujian)
        if ($tahunPelajaranId) {
            $jadwalQuery->whereHas('ujian', function ($query) use ($tahunPelajaranId) {
                $query->where('tahun_pelajaran_id', $tahunPelajaranId);
            });
        }

        // Filter spesifik jika user memilih jenis ujian tertentu (UTS/UAS)
        if ($ujianId) {
            $jadwalQuery->where('ujian_id', $ujianId);
        }

        $jadwalRaw = $jadwalQuery->get();

        // =========================================================================
        // 4. LOGIKA MATRIKS & DETEKSI BENTROK
        // =========================================================================
        $matrix = [];
        $checkBentrok = [];
        $bentrokJadwalIds = [];

        foreach ($jadwalRaw as $jadwal) {
            // Konversi format tanggal dan waktu untuk dijadikan Key Array
            $tanggal = Carbon::parse($jadwal->tanggal_ujian)->format('Y-m-d');
            $waktu = Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i');

            if ($jadwal->level_id) {
                // Mapping: matrix[Tanggal][SesiWaktu][Level_ID]
                $matrix[$tanggal][$waktu][$jadwal->level_id] = $jadwal;
            }

            // Cek bentrok: Jika Ustadz mengawas di hari dan waktu yang sama lebih dari 1 lokasi
            if ($jadwal->ustadz_id) {
                $checkBentrok[$tanggal][$waktu][$jadwal->ustadz_id][] = $jadwal->id;
            }
        }

        // Eksekusi penandaan ID Jadwal yang bentrok
        foreach ($checkBentrok as $tanggal => $waktuData) {
            foreach ($waktuData as $waktu => $asatidzData) {
                foreach ($asatidzData as $asatidzId => $jadwalIds) {
                    if (count($jadwalIds) > 1) { // Jika Ustadz ngawas > 1 kelas di waktu yg sama
                        foreach ($jadwalIds as $id) {
                            $bentrokJadwalIds[$id] = true;
                        }
                    }
                }
            }
        }

        // =========================================================================
        // 5. DATA DROPDOWN UNTUK VIEW
        // =========================================================================
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();

        // (Opsional) Kirim data daftar ujian untuk dropdown filter kedua (khusus IMDA)
        $daftarUjian = collect();
        if ($tahunPelajaranId) {
            $daftarUjian = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)
                ->where('tipe_ujian', '!=', 'IMNI')
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('jadwal-ujian.index', compact(
            'levels',           // Pengganti $ruangans
            'matrix',           // Berisi data 3D [Tanggal][Waktu][Level]
            'bentrokJadwalIds', // Data jadwal yang pengawasnya bentrok
            'daftarTahun',
            'daftarUjian',
            'tahunPelajaranId',
            'ujianId'
        ));
    }

    public function cetakLeger(Request $request)
    {
        // 1. Tangkap filter (opsional, berguna agar yang dicetak tidak menumpuk semua tahun)
        $tahun_pelajaran_id = $request->tahun_pelajaran_id;
        $ujian_id = $request->ujian_id;

        // Jika tidak ada filter, default ambil jadwal untuk tahun ajaran aktif
        if (!$tahun_pelajaran_id) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahun_pelajaran_id = $tahunAktif ? $tahunAktif->id : null;
        }

        // 2. Panggil Level (Kelas) beserta relasi tingkat (sebagai pengganti Ruangan)
        $levelsQuery = Level::with(['tingkat'])
            ->orderBy('tingkat_id')
            ->orderBy('nama_level');

        if ($ujian_id) {
            $ujian = Ujian::find($ujian_id);
            if ($ujian) {
                if (in_array($ujian->tipe_ujian, ['IMDA 2', 'IMDA 3'])) {
                    $levelsQuery->whereNotIn('nama_level', ['3 TPQ', '6 IBT', '3 TSA']);
                }
                if ($ujian->tingkat_id) {
                    $levelsQuery->where('tingkat_id', $ujian->tingkat_id);
                }
            }
        }

        $levels = $levelsQuery->get();

        // 3. Bangun query Jadwal Ujian
        $jadwalQuery = JadwalUjian::with(['mataPelajaran', 'pengawas', 'level', 'ujian'])
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('waktu_mulai', 'asc');

        // Filter jadwal berdasarkan tahun pelajaran
        if ($tahun_pelajaran_id) {
            $jadwalQuery->whereHas('ujian', function ($query) use ($tahun_pelajaran_id) {
                $query->where('tahun_pelajaran_id', $tahun_pelajaran_id)
                    ->where('tipe_ujian', '!=', 'IMNI');
            });
        }

        // Filter jadwal berdasarkan Ujian tertentu (misal: hanya UTS)
        if ($ujian_id) {
            $jadwalQuery->where('ujian_id', $ujian_id);
        }

        $jadwalRaw = $jadwalQuery->get();

        // 4. Transformasi ke format Matriks
        $matrix = [];
        foreach ($jadwalRaw as $jadwal) {
            // Konversi menjadi tipe string agar terhindar dari Error Carbon Object Array Offset
            $tanggal = Carbon::parse($jadwal->tanggal_ujian)->format('Y-m-d');
            $waktu = Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i');

            if ($jadwal->level_id) {
                $matrix[$tanggal][$waktu][$jadwal->level_id] = $jadwal;
            }
        }

        // 5. Lempar data ke view yang sudah kita buat tadi
        return view('cetak-baru.cetak-jadwal-ujian', compact('levels', 'matrix'));
    }

    public function create(Request $request)
    {
        $tahun_pelajaran_id = $request->tahun_pelajaran_id;
        $ujian_id = $request->ujian_id;
        $level_id = $request->level_id; // <-- UBAH KE LEVEL

        // Tarik Master Data
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'asc')->get();

        $levelsQuery = Level::orderBy('id');
        if ($ujian_id) {
            $ujian = Ujian::find($ujian_id);
            if ($ujian) {
                if (in_array($ujian->tipe_ujian, ['IMDA 2', 'IMDA 3'])) {
                    $levelsQuery->whereNotIn('nama_level', ['3 TPQ', '6 IBT', '3 TSA']);
                }
                if ($ujian->tingkat_id) {
                    $levelsQuery->where('tingkat_id', $ujian->tingkat_id);
                }
            }
        }
        $levels = $levelsQuery->get();

        $ujians = collect();
        if ($tahun_pelajaran_id) {
            $ujiansQuery = Ujian::where('tahun_pelajaran_id', $tahun_pelajaran_id)
                ->where('tipe_ujian', '!=', 'IMNI');
            if ($level_id) {
                $levelTerpilih = Level::find($level_id);
                if ($levelTerpilih) {
                    $ujiansQuery->berlakuUntukLevel($levelTerpilih);
                }
            }
            $ujians = $ujiansQuery->get();
        }

        $dates = [];
        $existingJadwal = collect();
        $mapels = collect();

        if ($ujian_id && $level_id) {
            $ujian = Ujian::find($ujian_id);
            $levelTerpilih = Level::find($level_id);

            // Validasi kecocokan level dengan tipe ujian
            $isValidForLevel = true;
            if ($ujian && $levelTerpilih) {
                $isKelasAkhir = Ujian::isKelasAkhir($levelTerpilih->nama_level);
                if ($ujian->tipe_ujian === 'IMNI' || ($isKelasAkhir && in_array($ujian->tipe_ujian, ['IMDA 2', 'IMDA 3']))) {
                    $isValidForLevel = false;
                }
                if ($ujian->tingkat_id && $levelTerpilih->tingkat_id && (int)$ujian->tingkat_id !== (int)$levelTerpilih->tingkat_id) {
                    $isValidForLevel = false;
                }
            }

            if ($isValidForLevel && $ujian && $ujian->tanggal_mulai && $ujian->tanggal_selesai) {
                $start = Carbon::parse($ujian->tanggal_mulai);
                $end = Carbon::parse($ujian->tanggal_selesai);

                for ($d = $start; $d->lte($end); $d->addDay()) {
                    if ($d->isFriday()) continue; // Skip Jum'at
                    $dates[] = $d->format('Y-m-d');
                }
            }

            if ($isValidForLevel) {
                // Tarik jadwal yang sudah ada berdasarkan LEVEL
                $existingJadwal = JadwalUjian::where('ujian_id', $ujian_id)
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

        return view('jadwal-ujian.create', compact(
            'tahun_pelajaran_id',
            'ujian_id',
            'level_id',
            'tahunPelajarans',
            'ujians',
            'levels',
            'dates',
            'existingJadwal',
            'mapels',
            'pengawas'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required',
            'level_id' => 'required', // <-- UBAH KE LEVEL
            'jadwal'   => 'required|array'
        ]);

        $ujian = Ujian::findOrFail($request->ujian_id);
        $level = Level::findOrFail($request->level_id);
        $isKelasAkhir = Ujian::isKelasAkhir($level->nama_level);

        if ($ujian->tipe_ujian === 'IMNI') {
            return back()->with('error', 'Ujian IMNI dikelola khusus melalui menu Panitia IMNI.');
        }
        if ($isKelasAkhir && in_array($ujian->tipe_ujian, ['IMDA 2', 'IMDA 3'])) {
            return back()->with('error', 'Kelas akhir (' . $level->nama_level . ') tidak mengikuti ujian IMDA 2/3.');
        }

        DB::transaction(function () use ($request) {
            // Hapus jadwal lama untuk ujian & LEVEL ini
            JadwalUjian::where('ujian_id', $request->ujian_id)
                ->where('level_id', $request->level_id)
                ->delete();

            foreach ($request->jadwal as $tanggal => $sesions) {
                foreach ($sesions as $sesi => $data) {
                    if (!empty($data['waktu_mulai']) && (!empty($data['mata_pelajaran_id']) || !empty($data['nama_mata_pelajaran_custom']))) {
                        JadwalUjian::create([
                            'ujian_id'                   => $request->ujian_id,
                            'level_id'                   => $request->level_id, // <-- UBAH KE LEVEL
                            'tanggal_ujian'              => $tanggal,
                            'waktu_mulai'                => $data['waktu_mulai'],
                            'waktu_selesai'              => $data['waktu_selesai'] ?? null,
                            'mata_pelajaran_id'          => $data['mata_pelajaran_id'],
                            'nama_mata_pelajaran_custom' => $data['nama_mata_pelajaran_custom'],
                            'ustadz_id'                  => $data['ustadz_id'] ?? null,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Leger Jadwal Ujian berhasil disimpan massal!');
    }
}
