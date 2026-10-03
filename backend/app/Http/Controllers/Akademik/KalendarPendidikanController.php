<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Http\Requests\Akademik\AgendaRequest;
use App\Models\BulanHijriyah;
use App\Models\HariLibur;
use App\Models\KalendarPendidikan;
use App\Models\KategoriKegiatan;
use App\Models\Level;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ujian\Ujian;
use Illuminate\Http\Request;


class KalendarPendidikanController extends Controller
{

    public function index(Request $request)
    {
        $tahun_pelajarans = TahunPelajaran::orderBy('id', 'asc')->get();
        $kategoris = KategoriKegiatan::all();

        $firstTahun = $tahun_pelajarans->first();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? ($firstTahun ? $firstTahun->id : null);

        $events = [];

        if ($tahunPelajaranId) {
            $kegiatans = KalendarPendidikan::with(['kategoriKegiatan'])
                ->where('tahun_pelajaran_id', $tahunPelajaranId)
                ->orderBy('tanggal_mulai', 'asc')
                ->get();

            foreach ($kegiatans as $keg) {
                $hex = $keg->kategoriKegiatan->kode_warna ?? '#8b5cf6';

                $events[] = [
                    'id'          => 'kegiatan_' . $keg->id,
                    'title'       => $keg->nama_kegiatan,
                    'start'       => $keg->tanggal_mulai,
                    'end'         => $keg->tanggal_selesai,
                    'kategori'    => $keg->kategoriKegiatan->nama_kategori ?? 'Umum',
                    'tipe'        => 'kegiatan',
                    'hex_color'   => $hex,
                    'kategori_id' => $keg->kategori_kegiatan_id
                ];
            }

            $liburs = HariLibur::with(['ruangan', 'level'])->where('tahun_pelajaran_id', $tahunPelajaranId)->get();
            foreach ($liburs as $libur) {
                $detailInfo = '';
                if ($libur->tipe_libur === 'Sebagian Jam' && !empty($libur->jam_ke)) {
                    $detailInfo .= ' (Jam: ' . implode(', ', $libur->jam_ke) . ')';
                }
                if ($libur->ruangan) {
                    $detailInfo .= ' [' . $libur->ruangan->nama_ruangan . ']';
                } elseif ($libur->level) {
                    $detailInfo .= ' [Level ' . $libur->level->nama_level . ']';
                }

                $events[] = [
                    'id'          => 'libur_' . $libur->id,
                    'title'       => 'Libur/Bebas KBM: ' . $libur->keterangan . $detailInfo,
                    'start'       => $libur->tanggal_mulai,
                    'end'         => $libur->tanggal_selesai,
                    'kategori'    => $libur->tipe_libur === 'Sebagian Jam' ? 'Bebas KBM Sebagian' : 'Hari Libur / Bebas KBM',
                    'tipe'        => 'libur',
                    'hex_color'   => $libur->tipe_libur === 'Sebagian Jam' ? '#f59e0b' : '#f43f5e',
                ];
            }

            $ujians = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->whereNotNull('tanggal_mulai')->get();
            foreach ($ujians as $ujian) {
                $events[] = [
                    'id'          => 'ujian_' . $ujian->id,
                    'title'       => 'Ujian: ' . $ujian->nama_ujian,
                    'start'       => $ujian->tanggal_mulai,
                    'end'         => $ujian->tanggal_selesai,
                    'kategori'    => 'Akademik',
                    'tipe'        => 'ujian',
                    'hex_color'   => '#f59e0b',
                    'semester_id' => $ujian->semester_id,
                    'tipe_ujian'  => $ujian->tipe_ujian
                ];
            }

            usort($events, function ($a, $b) {
                return strtotime($a['start']) - strtotime($b['start']);
            });
        }

        return view('kalendar.index', compact('tahun_pelajarans', 'tahunPelajaranId', 'kategoris', 'events'));
    }

    public function create(Request $request)
    {
        if ($request->ajax()) {
            $tahun_pelajarans = TahunPelajaran::orderBy('id', 'asc')->get();
            $kategoris = KategoriKegiatan::all();
            $ruangans = Ruangan::with('level')
                ->orderBy(
                    Level::select('urutan_level')
                        ->whereColumn('levels.id', 'ruangans.level_id')
                        ->limit(1)
                )
                ->orderBy('nama_ruangan')
                ->get();
            $levels = \App\Models\Level::orderBy('urutan_level')->get();
            $tingkats = \App\Models\Tingkat::orderBy('urutan_tingkat')->get();

            return view('kalendar.form-kalendar', compact('tahun_pelajarans', 'kategoris', 'ruangans', 'levels', 'tingkats'));
        }

        return redirect()->route('kalendar-pendidikan.index')->with('error', 'Silakan gunakan tombol tambah data melalui antarmuka kalender.');
    }

    public function store(AgendaRequest $request)
    {
        $validated = $request->validated();
        $jenis = $validated['jenis_agenda'];

        if ($jenis === 'libur') {
            HariLibur::create([
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'keterangan'         => $validated['nama_agenda'],
                'tipe_libur'         => $request->tipe_libur ?? 'Seharian',
                'jam_ke'             => $request->tipe_libur === 'Sebagian Jam' ? ($request->jam_ke ?? []) : null,
                'ruangan_id'         => $request->ruangan_id ?: null,
                'level_id'           => $request->level_id ?: null,
                'tanggal_mulai'      => $validated['tanggal_mulai'],
                'tanggal_selesai'    => $validated['tanggal_selesai'],
            ]);
        } elseif ($jenis === 'ujian') {
            Ujian::create([
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'nama_ujian'         => $validated['nama_agenda'],
                'semester_id'        => $validated['semester_id'],
                'tingkat_id'         => $request->tingkat_id ?: null,
                'tipe_ujian'         => $validated['tipe_ujian'],
                'tanggal_mulai'      => $validated['tanggal_mulai'],
                'tanggal_selesai'    => $validated['tanggal_selesai'],
            ]);
        } else {
            $tipePresensi = $request->tipe_presensi ?? 'tidak_ada';
            $sesiKegiatan = null;
            if ($tipePresensi === 'harian') {
                $sesiKegiatan = ['Harian'];
            } elseif ($tipePresensi === 'multi_sesi') {
                $sesiKegiatan = !empty($request->sesi_kegiatan) ? $request->sesi_kegiatan : ['Siang', 'Malam'];
            }

            KalendarPendidikan::create([
                'tahun_pelajaran_id'   => $validated['tahun_pelajaran_id'],
                'nama_kegiatan'        => $validated['nama_agenda'],
                'kategori_kegiatan_id' => $validated['kategori_kegiatan_id'],
                'tipe_presensi'        => $tipePresensi,
                'sesi_kegiatan'        => $sesiKegiatan,
                'keterangan'           => $request->keterangan ?? null,
                'tanggal_mulai'        => $validated['tanggal_mulai'],
                'tanggal_selesai'      => $validated['tanggal_selesai'],
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Agenda berhasil ditambahkan!'
            ]);
        }
        return redirect()->back()->with('success', 'Agenda berhasil ditambahkan!');
    }

    public function edit(Request $request, $id)
    {
        if ($request->ajax()) {
            $tipe = $request->query('tipe', 'kegiatan');
            $tahun_pelajarans = TahunPelajaran::orderBy('id', 'asc')->get();
            $kategoris = KategoriKegiatan::all();
            $ruangans = Ruangan::with('level')
                ->orderBy(
                    Level::select('urutan_level')
                        ->whereColumn('levels.id', 'ruangans.level_id')
                        ->limit(1)
                )
                ->orderBy('nama_ruangan')
                ->get();
            $levels = Level::orderBy('urutan_level')->get();
            $tingkats = \App\Models\Tingkat::orderBy('urutan_tingkat')->get();

            if ($tipe === 'libur') {
                $kegiatan = HariLibur::findOrFail($id);
                $kegiatan->nama_kegiatan = $kegiatan->keterangan;
            } elseif ($tipe === 'ujian') {
                $kegiatan = Ujian::findOrFail($id);
                $kegiatan->nama_kegiatan = $kegiatan->nama_ujian;
            } else {
                $kegiatan = KalendarPendidikan::findOrFail($id);
            }
            $kegiatan->tipe_agenda = $tipe;
            return view('kalendar.form-kalendar', compact('kegiatan', 'tahun_pelajarans', 'kategoris', 'ruangans', 'levels', 'tingkats'));
        }
        return redirect()->route('kalendar-pendidikan.index')->with('error', 'Silakan gunakan tombol edit data.');
    }

    public function update(AgendaRequest $request, $id)
    {
        $validated = $request->validated();
        $jenis = $validated['jenis_agenda'];

        if ($jenis === 'libur') {
            HariLibur::findOrFail($id)->update([
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'keterangan'         => $validated['nama_agenda'],
                'tipe_libur'         => $request->tipe_libur ?? 'Seharian',
                'jam_ke'             => $request->tipe_libur === 'Sebagian Jam' ? ($request->jam_ke ?? []) : null,
                'ruangan_id'         => $request->ruangan_id ?: null,
                'level_id'           => $request->level_id ?: null,
                'tanggal_mulai'      => $validated['tanggal_mulai'],
                'tanggal_selesai'    => $validated['tanggal_selesai'],
            ]);
        } elseif ($jenis === 'ujian') {
            Ujian::findOrFail($id)->update([
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'nama_ujian'         => $validated['nama_agenda'],
                'semester_id'        => $validated['semester_id'],
                'tingkat_id'         => $request->tingkat_id ?: null,
                'tipe_ujian'         => $validated['tipe_ujian'],
                'tanggal_mulai'      => $validated['tanggal_mulai'],
                'tanggal_selesai'    => $validated['tanggal_selesai'],
            ]);
        } else {
            $tipePresensi = $request->tipe_presensi ?? 'tidak_ada';
            $sesiKegiatan = null;
            if ($tipePresensi === 'harian') {
                $sesiKegiatan = ['Harian'];
            } elseif ($tipePresensi === 'multi_sesi') {
                $sesiKegiatan = !empty($request->sesi_kegiatan) ? $request->sesi_kegiatan : ['Siang', 'Malam'];
            }

            KalendarPendidikan::findOrFail($id)->update([
                'tahun_pelajaran_id'   => $validated['tahun_pelajaran_id'],
                'nama_kegiatan'        => $validated['nama_agenda'],
                'kategori_kegiatan_id' => $validated['kategori_kegiatan_id'],
                'tipe_presensi'        => $tipePresensi,
                'sesi_kegiatan'        => $sesiKegiatan,
                'keterangan'           => $request->keterangan ?? null,
                'tanggal_mulai'        => $validated['tanggal_mulai'],
                'tanggal_selesai'      => $validated['tanggal_selesai'],
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Agenda berhasil diperbarui!'
        ], 200);
    }


    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            $jenis = $request->jenis_agenda;
            try {
                if ($jenis === 'libur') {
                    HariLibur::findOrFail($id)->delete();
                } elseif ($jenis === 'ujian') {
                    Ujian::findOrFail($id)->delete();
                } else {
                    KalendarPendidikan::findOrFail($id)->delete();
                }

                return redirect()->back()->with('success', 'Agenda berhasil dihapus!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal menghapus agenda!');
            }
        }
        return redirect()->route('kalendar-pendidikan.index')->with('error', 'Silakan gunakan tombol edit data.');
    }

    public function matriksKalender(Request $request)
    {
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $tp = TahunPelajaran::findOrFail($tahunPelajaranId);

        $liburs = HariLibur::with(['ruangan', 'level'])->where('tahun_pelajaran_id', $tahunPelajaranId)->get();
        $ujians = Ujian::where('tahun_pelajaran_id', $tahunPelajaranId)->get();

        $kegiatans = KalendarPendidikan::where('tahun_pelajaran_id', $tahunPelajaranId)->get();
        $kategoris = KategoriKegiatan::all();
        $bulanTerSet = BulanHijriyah::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->pluck('nama_bulan')
            ->toArray();

        $tahunHijriiahAwal = (int) explode('/', $tp->nama_hijriyah)[0];

        return view('kalendar.matriks', compact('tp', 'liburs', 'ujians', 'kegiatans', 'kategoris', 'tahunHijriiahAwal', 'bulanTerSet'));
    }

    public function setBulanMatriks(Request $request)
    {
        $namaBulan = $request->query('nama_bulan');
        $urutan = $request->query('urutan');
        $mulai = $request->query('mulai');
        $selesai = $request->query('selesai');

        $tp = TahunPelajaran::tahunAktif()->first();
        if ($request->ajax()) {
            return view('kalendar.form-set-bulan', compact('namaBulan', 'urutan', 'mulai', 'selesai', 'tp'));
        }

        return redirect()->back()->with('error', 'Akses tidak sah.');
    }

    public function createAgendaMatriks(Request $request)
    {
        $tanggal = $request->query('tanggal');
        $tp = TahunPelajaran::tahunAktif()->first();
        $tahun_pelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
        $kategoris = KategoriKegiatan::all();
        $ruangans = Ruangan::with('level')
            ->orderBy(
                Level::select('urutan_level')
                    ->whereColumn('levels.id', 'ruangans.level_id')
                    ->limit(1)
            )
            ->orderBy('nama_ruangan')
            ->get();
        $levels = Level::orderBy('urutan_level')->get();

        if ($request->ajax()) {
            return view('kalendar.form-matriks-agenda', compact(
                'tanggal',
                'tp',
                'tahun_pelajarans',
                'kategoris',
                'ruangans',
                'levels'
            ));
        }

        return redirect()->back()->with('error', 'Akses tidak sah. Gunakan antarmuka kalender.');
    }


    public function storeBulanByMatriks(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id'     => 'required|exists:tahun_pelajarans,id',
            'nama_bulan'             => 'required|string|max:255',
            'tahun_hijriyah'         => 'required|string|max:4',
            'urutan'                 => 'required|integer',
            'tanggal_mulai_masehi'   => 'required|date',
            'tanggal_selesai_masehi' => 'required|date|after_or_equal:tanggal_mulai_masehi',
            'is_active'              => 'nullable|in:1',
        ]);

        $isActive = $request->has('is_active') ? true : false;

        if ($isActive) {
            BulanHijriyah::where('is_active', true)->update(['is_active' => false]);
        }

        BulanHijriyah::updateOrCreate(
            [
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'nama_bulan'  => $request->nama_bulan,
                'tahun_hijriyah'     => $request->tahun_hijriyah,
            ],
            [
                'urutan'                 => $request->urutan,
                'tanggal_mulai_masehi'   => $request->tanggal_mulai_masehi,
                'tanggal_selesai_masehi' => $request->tanggal_selesai_masehi,
                'is_active'              => $isActive,
            ]
        );
        return response()->json([
            'status'  => 'success',
            'message' => 'Periode Bulan ' . $request->nama_bulan . ' berhasil didaftarkan ke database!'
        ], 200);
    }
}
