<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;

use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Illuminate\Http\Request;

class JadwalPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Ambil daftar tahun pelajaran
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::tahunAktif()->value('id') ?? $daftarTahun->first()->id;

        // 2. Ambil daftar ruangan untuk opsi Dropdown (Urut ID ASC)
        $daftarRuangan = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->berdasarkanHakAkses()
            ->orderBy('id', 'asc')
            ->get();

        // 3. Tangkap ID ruangan yang difilter (jika ada)
        $ruanganId = $request->ruangan_id;

        // 4. Query dasar daftar ruangan
        $query = Ruangan::with('level.tingkat')->berdasarkanHakAkses()->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->withCount('jadwalPelajarans'); // Pastikan relasinya benar

        // 5. Terapkan saringan jika salah satu ruangan dipilih
        if ($ruanganId) {
            $query->where('id', $ruanganId);
        }

        $ruangans = $query->orderBy('id', 'asc')->get();

        return view('jadwal-pelajaran.index', compact('ruangans', 'daftarTahun', 'tahunPelajaranId', 'daftarRuangan', 'ruanganId'));
    }

    public function ruanganShow($ruangan_id)
    {
        $ruangan = Ruangan::with('level')->berdasarkanHakAkses()->findOrFail($ruangan_id);

        // CANGGIH: Hanya tampilkan mata pelajaran yang sesuai dengan Level/Jilid Ruangan ini
        $mataPelajarans = MataPelajaran::where('level_id', $ruangan->level_id)->where('is_active', 1)->orderBy('nama_mapel')->get();

        $asatidzs = Ustadz::where('is_active', 1)->where('is_active', true)->orderBy('nama_lengkap')->get();

        // Ambil jadwal lalu kelompokkan berdasarkan Hari
        $jadwals = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ustadzs'])
            ->where('ruangan_id', $ruangan_id)
            ->orderByRaw("FIELD(hari, 'Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis')")
            ->orderByRaw("FIELD(jam_ke, 'Nadzoman', '1', '2', 'Ekstra')")
            ->get()
            ->groupBy('hari');

        return view('jadwal-pelajaran.ruangan', compact('ruangan', 'mataPelajarans', 'asatidzs', 'jadwals'));
    }

    private function setJamWaktu($data)
    {
        // Paksa jam_ke menjadi string agar === bisa bekerja
        $jam_ke = (string) $data['jam_ke'];

        if ($jam_ke === 'Nadzoman') {
            $data['jam_mulai'] = '13:45:00';
            $data['jam_selesai'] = '14:00:00';
        } elseif ($jam_ke === '1') {
            $data['jam_mulai'] = '14:00:00';
            $data['jam_selesai'] = '14:45:00';
        } elseif ($jam_ke === '2') {
            $data['jam_mulai'] = '15:30:00';
            $data['jam_selesai'] = '16:15:00';
        } else {
            // Default untuk 'Ekstra'
            $data['jam_mulai'] = '20:00:00';
            $data['jam_selesai'] = '21:00:00';
        }

        return $data;
    }


    public function massStore(Request $request, $ruangan_id)
    {
        $jadwalsInput = $request->input('jadwal');
        if (!$jadwalsInput) {
            return back()->with('error', 'Tidak ada data jadwal yang dikirim.');
        }
        $errorCount = 0;
        foreach ($jadwalsInput as $hari => $jams) {
            foreach ($jams as $jam_ke => $data) {
                $mapelId = $data['mata_pelajaran_id'] ?? null;

                // Guru Utama
                $ustadzUtamaId = $data['ustadz_utama_id'] ?? ($data['ustadz_id'] ?? null);

                // Guru Pendamping (bisa lebih dari 1)
                $pendampingIds = $data['ustadz_pendamping_ids'] ?? [];
                if (!is_array($pendampingIds)) {
                    $pendampingIds = !empty($pendampingIds) ? [$pendampingIds] : [];
                }

                // Filter pendamping agar tidak duplikat dengan guru utama
                $pendampingIds = array_values(array_filter($pendampingIds, function ($pId) use ($ustadzUtamaId) {
                    return !empty($pId) && $pId != $ustadzUtamaId;
                }));

                // Gabungkan seluruh pengampu: [0 => Utama, 1..n => Pendamping]
                $allUstadzIds = [];
                if (!empty($ustadzUtamaId)) {
                    $allUstadzIds[] = (int) $ustadzUtamaId;
                }
                foreach ($pendampingIds as $pId) {
                    $allUstadzIds[] = (int) $pId;
                }

                // Fallback jika dikirim via format array ustadz_ids
                if (empty($allUstadzIds) && !empty($data['ustadz_ids'])) {
                    $raw = is_array($data['ustadz_ids']) ? $data['ustadz_ids'] : [$data['ustadz_ids']];
                    $allUstadzIds = array_values(array_unique(array_filter($raw)));
                }

                if (!empty($mapelId) && !empty($allUstadzIds)) {
                    $waktu = $this->setJamWaktu(['jam_ke' => $jam_ke]);

                    $primaryId = $allUstadzIds[0];

                    $jadwal = JadwalPelajaran::updateOrCreate(
                        [
                            'ruangan_id' => $ruangan_id,
                            'hari'       => $hari,
                            'jam_ke'     => (string) $jam_ke,
                        ],
                        [
                            'mata_pelajaran_id' => $mapelId,
                            'ustadz_id'         => $primaryId,
                            'jam_mulai'         => $waktu['jam_mulai'] ?? null,
                            'jam_selesai'       => $waktu['jam_selesai'] ?? null,
                        ]
                    );

                    // Sync pivot dengan atribut is_utama & urutan
                    $syncData = [];
                    foreach ($allUstadzIds as $index => $uId) {
                        $syncData[$uId] = [
                            'is_utama' => ($index === 0),
                            'urutan'   => $index + 1,
                        ];
                    }
                    $jadwal->ustadzs()->sync($syncData);
                } elseif (empty($mapelId) && empty($allUstadzIds)) {
                    JadwalPelajaran::where('ruangan_id', $ruangan_id)
                        ->where('hari', $hari)
                        ->where('jam_ke', (string) $jam_ke)
                        ->delete();
                } else {
                    $errorCount++;
                }
            }
        }
        if ($errorCount > 0) {
            return back()->with('warning', "Tersimpan! Namun ada $errorCount jadwal yang diabaikan karena Anda lupa mengisi salah satu (Mapel atau Guru Utama).");
        }
        return back()->with('success', 'Semua jadwal berhasil diperbarui!');
    }


    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        $jadwalPelajaran->delete();
        return back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    public function jadwalInduk(Request $request)
    {
        // =========================================================================
        // 1. TENTUKAN TAHUN PELAJARAN YANG MAU DITAMPILKAN
        // =========================================================================
        $tahunPelajaranId = $request->input('tahun_id');

        // Jika tidak ada filter, cari Tahun Pelajaran yang statusnya aktif
        if (!$tahunPelajaranId) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahunPelajaranId = $tahunAktif ? $tahunAktif->id : null;
        }

        // =========================================================================
        // 2. AMBIL DATA RUANGAN (DIFILTER BERDASARKAN TAHUN)
        // =========================================================================
        $ruangansQuery = Ruangan::with(['level.tingkat'])
            ->berdasarkanHakAkses()
            ->orderBy('level_id')
            ->orderBy('nama_ruangan');

        // Filter agar ruangan yang tampil hanya ruangan di tahun ajaran tersebut
        if ($tahunPelajaranId) {
            $ruangansQuery->where('tahun_pelajaran_id', $tahunPelajaranId);
        }

        $ruangans = $ruangansQuery->get();

        // =========================================================================
        // 3. AMBIL JADWAL (DIFILTER BERDASARKAN RELASI RUANGAN)
        // =========================================================================
        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ustadzs', 'ruangan']);

        if ($tahunPelajaranId) {
            // Ini kuncinya: Filter jadwal yang ruangannya punya tahun_pelajaran_id yang dicari
            $jadwalQuery->whereHas('ruangan', function ($query) use ($tahunPelajaranId) {
                $query->where('tahun_pelajaran_id', $tahunPelajaranId);
            });
        }

        $jadwalRaw = $jadwalQuery->get();

        // =========================================================================
        // 4. LOGIKA MATRIKS & DETEKSI BENTROK (Multi-Ustadz Team Teaching)
        // =========================================================================
        $matrix = [];
        $checkBentrok = [];
        $bentrokJadwalIds = [];

        foreach ($jadwalRaw as $jadwal) {
            $matrix[$jadwal->hari][$jadwal->jam_ke][$jadwal->ruangan_id] = $jadwal;
            $allUstadz = $jadwal->daftar_ustadz;
            foreach ($allUstadz as $u) {
                $checkBentrok[$jadwal->hari][$jadwal->jam_ke][$u->id][] = $jadwal->id;
            }
        }

        foreach ($checkBentrok as $hari => $jamData) {
            foreach ($jamData as $jam => $asatidzData) {
                foreach ($asatidzData as $asatidzId => $jadwalIds) {
                    if (count($jadwalIds) > 1) {
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

        return view('jadwal-pelajaran.induk', compact(
            'ruangans',
            'matrix',
            'bentrokJadwalIds',
            'daftarTahun',
            'tahunPelajaranId'
        ));
    }

    public function togglePublikasi($ruangan_id)
    {
        $ruangan = Ruangan::findOrFail($ruangan_id);

        // Membalikkan status: Jika 0 jadi 1, jika 1 jadi 0
        $ruangan->is_jadwal_publik = !$ruangan->is_jadwal_publik;
        $ruangan->save();

        $status = $ruangan->is_jadwal_publik ? 'dipublikasikan' : 'disembunyikan (Draft)';

        return back()->with('success', "Jadwal kelas {$ruangan->nama_ruangan} berhasil {$status}.");
    }

    public function cetakLeger()
    {
        // Panggil ruangan beserta level dan tingkat
        $ruangans = Ruangan::with(['waliRuangan', 'level.tingkat'])->orderBy('level_id')->orderBy('nama_ruangan')->get();

        $jadwalRaw = JadwalPelajaran::with(['mataPelajaran', 'ustadz', 'ruangan'])->get();

        $matrix = [];
        foreach ($jadwalRaw as $jadwal) {
            $matrix[$jadwal->hari][$jadwal->jam_ke][$jadwal->ruangan_id] = $jadwal;
        }

        return view('cetak-baru.cetak_leger_jadwal_pelajaran', compact('ruangans', 'matrix'));
    }
}
