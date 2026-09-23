<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Models\Level;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\RiwayatKenaikan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class RombonganBelajarController extends Controller
{
    public function index(Request $request)
    {

        $search = $request->input('search');
        $filterTp = $request->input('tahun_pelajaran_id');

        if (!$filterTp) {
            $activeTp = TahunPelajaran::where('is_active', true)->first();
            $filterTp = $activeTp ? $activeTp->id : null;
        }

        $ruangans = Ruangan::with(['level.tingkat', 'waliRuangan', 'tahunPelajaran'])
            ->when($filterTp, function ($query, $filterTp) {
                return $query->where('tahun_pelajaran_id', $filterTp);
            })
            ->when($search, function ($query, $search) {
                return $query->where('nama_ruangan', 'like', '%' . $search . '%')
                    ->orWhereHas('waliRuangan', function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', '%' . $search . '%');
                    });
            })
            ->berdasarkanHakAkses()
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $tahunPelajarans = TahunPelajaran::orderBy('id', 'asc')->get();

        return view('rombongan-belajar.index', compact('ruangans', 'tahunPelajarans', 'filterTp'));
    }




    public function anggota($id, Request $request)
    {
        $tahunAktifId = TahunPelajaran::where('is_active', true)->value('id');
        $ruangan = Ruangan::with(['murids' => function ($q) use ($request) {
            if ($request->filled('search')) {
                $search = $request->search;
                $q->where(function ($query) use ($search) {
                    $query->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nism', 'like', "%{$search}%");
                });
            }
            $q->orderBy('jenis_kelamin', 'asc');
            $q->orderBy('nama_lengkap', 'asc');
        }])->findOrFail($id);

        $muridsTersedia = Murid::where('status', 'Aktif')
            ->where('level_masuk', $ruangan->level_id)
            ->whereNull('ruangan_masuk')
            ->where('tahun_masuk', $tahunAktifId)
            ->orderBy('jenis_kelamin', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $ruangansLain = Ruangan::where('tahun_pelajaran_id', $tahunAktifId)
            ->where('id', '!=', $ruangan->id)
            ->get();

        return view('rombongan-belajar.anggota', compact('ruangan', 'muridsTersedia', 'ruangansLain'));
    }

    public function attachAnggota(Request $request, $id)
    {
        $ruangan = \App\Models\Ruangan::findOrFail($id);

        $request->validate([
            'murid_ids' => 'required|array',
            'murid_ids.*' => 'exists:murids,id'
        ], [
            'murid_ids.required' => 'Pilih minimal satu murid untuk ditambahkan.'
        ]);

        $tahunAktif = TahunPelajaran::where('is_active', 1)->first();
        $tahun_pelajaran_id = $tahunAktif ? $tahunAktif->id : null;

        // Hitung kapasitas ruangan HANYA untuk tahun pelajaran yang sedang aktif
        $kapasitasSekarang = $ruangan->murids()
            ->wherePivot('tahun_pelajaran_id', $tahun_pelajaran_id)
            ->count();

        $tambahan = count($request->murid_ids);

        if (($kapasitasSekarang + $tambahan) > $ruangan->kapasitas) {
            return back()->with('error', "Gagal! Sisa kursi hanya " . ($ruangan->kapasitas - $kapasitasSekarang) . ".");
        }

        // Susun data array agar attach menyertakan tahun_pelajaran_id ke tabel pivot
        $attachData = [];
        foreach ($request->murid_ids as $murid_id) {
            $attachData[$murid_id] = [
                'tahun_pelajaran_id' => $tahun_pelajaran_id
            ];
        }

        // 1. Eksekusi insert massal ke tabel pivot murid_ruangans beserta foreign key tahun ajaran
        $ruangan->murids()->attach($attachData);

        // 2. UPDATE TABEL MURIDS (Hanya untuk mengunci sejarah kelas pertama)
        // Kunci ruangan_masuk HANYA untuk murid baru (yang kolom ruangan_masuk-nya masih kosong)
        // Data permanen lain seperti tingkat awal tidak akan disentuh
        Murid::whereIn('id', $request->murid_ids)
            ->whereNull('ruangan_masuk')
            ->update([
                'ruangan_masuk' => $ruangan->id
            ]);

        return back()->with('success', "Berhasil menambahkan {$tambahan} Murid ke ruangan.");
    }

    public function detachAnggota($id, $murid_id)
    {
        // $ruangan = \App\Models\Ruangan::findOrFail($id);

        // 1. Deteksi Tahun Pelajaran aktif saat ini
        // $semesterAktif = \App\Models\Semester::where('is_active', 1)->first();
        // $tahun_pelajaran_id = $semesterAktif ? $semesterAktif->tahun_pelajaran_id : null;
        $tahun_pelajaran = TahunPelajaran::where('is_active', 1)->first();
        $tahunAktifId = $tahun_pelajaran->id;

        // 2. Hapus baris data dari tabel pivot murid_ruangans secara spesifik 
        // Langkah ini menjamin rekam jejak kelas mereka di tahun-tahun sebelumnya tidak terhapus.
        DB::table('murid_ruangans')
            ->where('ruangan_id', $id)
            ->where('murid_id', $murid_id)
            ->where('tahun_pelajaran_id', $tahunAktifId)
            ->delete();

        // 3. PERBAIKAN: PERLINDUNGAN MASTER DATA
        // Kembalikan ruangan_masuk menjadi NULL hanya untuk murid baru yang salah plotting.
        // Jika dia murid naik kelas, 'ruangan_masuk' lamanya tidak akan tersentuh karena ID-nya berbeda.
        Murid::where('id', $murid_id)
            ->where('ruangan_masuk', $id) // <-- GEMBOK PENGAMAN: Pastikan ruangan yang dilepas adalah ruangan masuknya
            ->update([
                'ruangan_masuk' => null
            ]);

        return back()->with('success', 'Murid berhasil dikeluarkan dari ruangan dan dikembalikan ke antrean.');
    }

    public function plottingKenaikan($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $tahunPelajaranId = $ruangan->tahun_pelajaran_id;

        // Ambil tahun lalu dan level sebelumnya
        $tahunLalu = TahunPelajaran::where('id', '<', $tahunPelajaranId)->orderBy('id', 'desc')->first();
        $levelSebelumnya = Level::where('id', '<', $ruangan->level_id)->orderBy('id', 'desc')->first();

        // ID semua murid yang sudah punya ruangan di tahun ajaran ini
        $muridSudahPunyaRuanganIds = Murid::whereHas('ruangans', function ($q) use ($tahunPelajaranId) {
            $q->where('ruangans.tahun_pelajaran_id', $tahunPelajaranId);
        })->pluck('id')->toArray();

        $muridsKenaikan = collect();

        if ($tahunLalu) {
            // Query langsung dari model RiwayatKenaikan beserta relasinya
            $muridsKenaikan = RiwayatKenaikan::with(['murid', 'ruanganAsal'])
                ->where('tahun_pelajaran_id', $tahunLalu->id)
                ->where(function ($query) use ($ruangan, $levelSebelumnya) {
                    // Kasus 1: Tinggal kelas di level yang sama
                    $query->where('status_keputusan', 'Tinggal Kelas')
                        ->whereHas('ruanganAsal', function ($q) use ($ruangan) {
                            $q->where('level_id', $ruangan->level_id);
                        });

                    // Kasus 2: Naik kelas dari level di bawahnya
                    if ($levelSebelumnya) {
                        $query->orWhere(function ($qOr) use ($levelSebelumnya) {
                            $qOr->where('status_keputusan', 'Naik Kelas')
                                ->whereHas('ruanganAsal', function ($qSub) use ($levelSebelumnya) {
                                    $qSub->where('level_id', $levelSebelumnya->id);
                                });
                        });
                    }
                })
                ->whereNotIn('murid_id', $muridSudahPunyaRuanganIds)
                ->whereHas('murid', function ($q) {
                    $q->where('status', 'Aktif');
                })
                ->get()
                ->sortBy([
                    ['murid.jenis_kelamin', 'asc'],
                    ['murid.nama_lengkap', 'asc']
                ])
                ->values();
        }

        return view('rombongan-belajar.plotting-kenaikan', compact('ruangan', 'muridsKenaikan', 'tahunLalu'));
    }

    public function storePlotting(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $request->validate([
            'murid_ids' => 'required|array|min:1',
            'murid_ids.*' => 'exists:murids,id'
        ], [
            'murid_ids.required' => 'Pilih minimal satu murid untuk ditambahkan ke ruangan ini.',
            'murid_ids.min' => 'Pilih minimal satu murid untuk ditambahkan ke ruangan ini.',
            'murid_ids.*.exists' => 'Data murid yang dipilih tidak valid.'
        ]);

        $tahunPelajaranId = $ruangan->tahun_pelajaran_id;

        // 1. Cek kapasitas ruangan pada tahun ajaran terkait
        $kapasitasSekarang = $ruangan->murids()
            ->wherePivot('tahun_pelajaran_id', $tahunPelajaranId)
            ->count();

        $tambahan = count($request->murid_ids);

        if ($ruangan->kapasitas && ($kapasitasSekarang + $tambahan) > $ruangan->kapasitas) {
            $sisaKapasitas = max(0, $ruangan->kapasitas - $kapasitasSekarang);
            return back()->with('error', "Gagal! Sisa kapasitas ruangan hanya {$sisaKapasitas} murid, sedangkan Anda memilih {$tambahan} murid.");
        }

        // 2. Filter murid yang belum terdaftar di ruangan manapun pada tahun ajaran ini (mencegah dobel)
        $muridSudahAda = DB::table('murid_ruangans')
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('murid_id', $request->murid_ids)
            ->pluck('murid_id')
            ->toArray();

        $muridIdsToAttach = array_values(array_diff($request->murid_ids, $muridSudahAda));

        if (empty($muridIdsToAttach)) {
            return back()->with('error', 'Semua murid yang dipilih sudah memiliki ruangan pada tahun ajaran ini.');
        }

        DB::beginTransaction();
        try {
            // 3. Susun data attach dengan foreign key tahun_pelajaran_id ke tabel pivot
            $attachData = [];
            foreach ($muridIdsToAttach as $muridId) {
                $attachData[$muridId] = [
                    'tahun_pelajaran_id' => $tahunPelajaranId
                ];
            }

            // Eksekusi insert massal ke tabel pivot murid_ruangans
            $ruangan->murids()->attach($attachData);

            // Jaga-jaga jika ada murid yang ruangan_masuk nya masih null
            Murid::whereIn('id', $muridIdsToAttach)
                ->whereNull('ruangan_masuk')
                ->update([
                    'ruangan_masuk' => $ruangan->id
                ]);

            DB::commit();

            $totalSukses = count($muridIdsToAttach);
            return redirect()->route('ruangan.anggota', $ruangan->id)
                ->with('success', "Berhasil menambahkan {$totalSukses} murid ke ruangan {$ruangan->nama_ruangan}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan plotting kenaikan murid: ' . $e->getMessage());
        }
    }


    public function pindahAnggota(Request $request, $id)
    {
        $request->validate([
            'murid_id' => 'required|exists:murids,id',
            'ruangan_tujuan_id' => 'required|exists:ruangans,id'
        ]);

        $ruanganAsal = Ruangan::findOrFail($id);
        $ruanganTujuan = Ruangan::findOrFail($request->ruangan_tujuan_id);
        $muridId = $request->murid_id;

        $tahunAktif = TahunPelajaran::where('is_active', 1)->first();
        $tahun_pelajaran_id = $tahunAktif ? $tahunAktif->id : null;

        // 1. UPDATE TABEL PIVOT (Berlaku untuk semua murid: Baru maupun Kenaikan)
        // Pindahkan mereka di tahun ajaran aktif ini ke ruangan yang baru
        DB::table('murid_ruangans')
            ->where('ruangan_id', $ruanganAsal->id)
            ->where('murid_id', $muridId)
            ->where('tahun_pelajaran_id', $tahun_pelajaran_id)
            ->update([
                'ruangan_id' => $ruanganTujuan->id
            ]);

        // 2. KOREKSI MASTER DATA (HANYA JIKA DIA MURID BARU)
        // Pengecekan: Jika ruangan_masuk sama dengan ruangan asal, berarti dia anak baru yang sedang dikoreksi ruangannya.
        $murid = Murid::findOrFail($muridId);

        if ($murid->ruangan_masuk == $ruanganAsal->id) {
            $murid->update([
                'ruangan_masuk' => $ruanganTujuan->id,
                'level_masuk'   => $ruanganTujuan->level_id,
            ]);
        }

        return back()->with('success', "Berhasil memindahkan {$murid->nama_lengkap} ke {$ruanganTujuan->nama_ruangan}.");
    }

    /**
     * Halaman Print Data Anggota Ruangan
     */
    public function printAnggota($id)
    {
        $ruangan = Ruangan::with(['tahunPelajaran', 'waliRuangan', 'murids' => function ($q) {
            $q->orderBy('jenis_kelamin', 'asc');
            $q->orderBy('nama_lengkap', 'asc');
        }])->findOrFail($id);

        return view('cetak-baru.print-anggota', compact('ruangan'));
    }

    /**
     * Export Data Anggota Ruangan ke Excel (CSV)
     */
    public function exportAnggota($id)
    {
        $ruangan = Ruangan::with(['murids' => function ($q) {
            $q->orderBy('jenis_kelamin', 'asc');
            $q->orderBy('nama_lengkap', 'asc');
        }])->findOrFail($id);

        $fileName = 'Data_Rombongan_Belajar_' . str_replace(' ', '_', $ruangan->nama_ruangan) . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'NISM', 'NISN', 'Nama Lengkap', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Nama Ayah', 'Nama Ibu', 'Zonasi/Kampung'];

        $callback = function () use ($ruangan, $columns) {
            $file = fopen('php://output', 'w');
            // Agar file CSV bisa terbaca rapi di Microsoft Excel, kita gunakan separator titik koma (;)
            fputcsv($file, $columns, ';');

            $no = 1;
            foreach ($ruangan->murids as $murid) {
                fputcsv($file, [
                    $no++,
                    $murid->nism,
                    $murid->nisn,
                    $murid->nama_lengkap,
                    $murid->jenis_kelamin,
                    $murid->tempat_lahir,
                    $murid->tanggal_lahir ? Carbon::parse($murid->tanggal_lahir)->format('d-m-Y') : '-',
                    $murid->nama_ayah,
                    $murid->nama_ibu,
                    $murid->waliMurid->kampung->nama_kampung ?? '-'
                ], ';');
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Menampilkan Modal Form Upload Foto Murid (AJAX Partial)
     */
    public function modalUpload($id, $murid_id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $murid = Murid::with('waliMurid.kampung')->findOrFail($murid_id);

        return view('rombongan-belajar.modal-upload-foto', compact('ruangan', 'murid'));
    }
}
