<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Http\Requests\MasterData\MuridRequest;
use App\Models\Level;
use App\Models\Murid;
use App\Models\TahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MuridController extends Controller
{


    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'Aktif');

        $murids = Murid::with('waliMurid')
            ->when($status !== 'Semua', function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', '%' . $search . '%')
                        ->orWhere('nism', 'like', '%' . $search . '%')
                        ->orWhere('nik_hash', hash_sensitive($search));
                });
            })
            ->orderBy('nism', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('murid.index', compact('murids', 'status'));
    }

    public function create()
    {
        $tahunPelajaranMasuk = TahunPelajaran::orderBy('is_active', 'desc')->orderBy('id', 'desc')->get();
        $levelMasuk = Level::with('tingkat')
            ->berdasarkanHakAkses()->get();
        return view('murid.form', compact('tahunPelajaranMasuk', 'levelMasuk'));
    }

    public function store(MuridRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/murid', 'public');
        }

        Murid::create($data);
        return redirect()->route('murid.index')->with('success', 'Data Murid berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $tahunPelajaranMasuk = TahunPelajaran::orderBy('is_active', 'desc')->orderBy('id', 'desc')->get();
        $levelMasuk = Level::with('tingkat')
            ->berdasarkanHakAkses()->get();
        $murid = Murid::with('waliMurid.kampung')->findOrFail($id);
        return view('murid.form', compact('murid', 'tahunPelajaranMasuk', 'levelMasuk'));
    }

    public function update(MuridRequest $request, $id)
    {
        $murid = Murid::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($murid->foto) {
                Storage::disk('public')->delete($murid->foto);
            }
            $data['foto'] = $request->file('foto')->store('uploads/murid', 'public');
        }

        $murid->update($data);
        return redirect()->back()->with('success', 'Data Murid diperbarui!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Aktif,Lulus,Pindah,Berhenti,Meninggal'
        ]);

        $murid = Murid::findOrFail($id);
        $murid->status = $request->status;
        $murid->save();

        if ($murid->wali_murid_id) {
            if ($murid->status !== 'Aktif') {
                $anakAktifLainnya = Murid::where('wali_murid_id', $murid->wali_murid_id)
                    ->where('id', '!=', $murid->id)
                    ->where('status', 'Aktif')
                    ->exists();

                if (!$anakAktifLainnya) {
                    DB::table('wali_murids')
                        ->where('id', $murid->wali_murid_id)
                        ->update(['is_active' => 0]);
                }
            } else {
                DB::table('wali_murids')
                    ->where('id', $murid->wali_murid_id)
                    ->update(['is_active' => 1]);
            }
        }

        return redirect()->back()->with('success', 'Status keaktifan murid berhasil diperbarui!');
    }


    public function destroy(Request $request, Murid $murid)
    {
        DB::beginTransaction();

        try {
            if ($murid->foto && Storage::disk('public')->exists($murid->foto)) {
                Storage::disk('public')->delete($murid->foto);
            }
            $murid->delete();
            DB::commit();
            if ($request->wantsJson()) {
                return response()->json([
                    'status'   => 'success',
                    'message'  => 'Data Murid berhasil dihapus!',
                    'redirect' => route('murid.index')
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // IMPORT & TEMPLATE (Diupdate untuk Skema Baru)
    // =========================================================================

    public function modalImport()
    {
        return view('murid.import');
    }

    public function template()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_murid.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Ditambah 6 kolom data biologis orang tua menjadi total 23 Kolom
        $columns = [
            'id',
            'wali_murid_id',
            'nism',
            'nisn',
            'nik',
            'nama_lengkap',
            'nama_panggilan',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'anak_ke',
            'hub_kel',
            'nik_ayah',
            'nama_ayah',
            'status_ayah',
            'nik_ibu',
            'nama_ibu',
            'status_ibu',
            'foto',
            'status',
            'tahun_masuk',
            'level_masuk',
            'ruangan_masuk'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, [
                '',
                '1',
                '12345678',
                '0012345678',
                '3501234567890001',
                'Amran Malik',
                'Amran',
                'L',
                'Bangkalan',
                '2015-05-07',
                '1',
                'Anak Kandung',
                '3501234567890002',
                'Ahmad Fulan',
                'Hidup',
                '3501234567890003',
                'Siti Aminah',
                'Hidup',
                'amran.jpg',
                'Aktif',
                '1',
                '1',
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|mimes:csv,txt|max:2048'
        ], [
            'file_import.required' => 'Pilih file CSV terlebih dahulu.',
            'file_import.mimes'    => 'Format file harus berupa CSV.'
        ]);

        $file = $request->file('file_import');

        $barisPertama = fgets(fopen($file->path(), 'r'));
        $delimiter = strpos($barisPertama, ';') !== false ? ';' : ',';

        $handle = fopen($file->path(), 'r');
        $header = fgetcsv($handle, 1000, $delimiter);

        $berhasil = 0;
        $gagal_kolom = 0;
        $gagal_wali = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $row = array_filter($row, function ($value) {
                    return $value !== null;
                });

                // Syarat kolom bertambah menjadi 23 karena tambahan orang tua
                if (count($row) < 23) {
                    $gagal_kolom++;
                    continue;
                }

                $cleanNull = function ($value) {
                    $trimmed = trim($value);
                    if ($trimmed === '' || strtolower($trimmed) === 'null' || $trimmed === '-') {
                        return null;
                    }
                    return $trimmed;
                };

                $id             = $cleanNull($row[0]);
                $wali_murid_id  = trim($row[1]);
                $nism           = trim($row[2]);
                $nisn           = $cleanNull($row[3]);
                $nik            = $cleanNull($row[4]);
                $nama_lengkap   = $cleanNull($row[5]);
                $nama_panggilan = $cleanNull($row[6]);

                $inputJk = strtoupper(trim($row[7]));
                $jenis_kelamin = in_array($inputJk, ['L', 'LAKI-LAKI', 'PRIA']) ? 'L' : 'P';

                $tempat_lahir   = $cleanNull($row[8]);

                $rawTanggal = $cleanNull($row[9]);
                $tanggal_lahir = null;
                if ($rawTanggal) {
                    $rawTanggal = str_replace('/', '-', $rawTanggal);
                    $tanggal_lahir = date('Y-m-d', strtotime($rawTanggal));
                }

                $anak_ke        = $cleanNull($row[10]);

                $inputHub = strtolower(trim($row[11]));
                if (in_array($inputHub, ['anak tiri', 'tiri'])) {
                    $hub_kel = 'Anak Tiri';
                } elseif (in_array($inputHub, ['anak angkat', 'angkat'])) {
                    $hub_kel = 'Anak Angkat';
                } elseif ($inputHub == 'cucu') {
                    $hub_kel = 'Cucu';
                } elseif (in_array($inputHub, ['lainnya', 'lain'])) {
                    $hub_kel = 'Lainnya';
                } else {
                    $hub_kel = 'Anak Kandung';
                }

                // --- DATA ORANG TUA (INDEX 12 - 17) ---
                $nik_ayah    = $cleanNull($row[12]);
                $nama_ayah   = $cleanNull($row[13]);
                $status_ayah = in_array(strtolower(trim($row[14])), ['meninggal', 'mati', 'wafat', 'alm', 'almarhum']) ? 'Meninggal' : 'Hidup';

                $nik_ibu     = $cleanNull($row[15]);
                $nama_ibu    = $cleanNull($row[16]);
                $status_ibu  = in_array(strtolower(trim($row[17])), ['meninggal', 'mati', 'wafat', 'alm', 'almarhum']) ? 'Meninggal' : 'Hidup';

                // FOTO DI INDEX 18
                $rawFoto = $cleanNull($row[18]);
                $foto = null;
                if ($rawFoto) {
                    if (!str_contains($rawFoto, 'uploads/murid/')) {
                        $foto = 'uploads/murid/' . $rawFoto;
                    } else {
                        $foto = $rawFoto;
                    }
                }

                // STATUS DI INDEX 19
                $inputStatus = strtolower(trim($row[19]));
                if ($inputStatus == 'lulus') {
                    $status = 'Lulus';
                } elseif ($inputStatus == 'pindah') {
                    $status = 'Pindah';
                } elseif (in_array($inputStatus, ['berhenti', 'do', 'keluar'])) {
                    $status = 'Berhenti';
                } else {
                    $status = 'Aktif';
                }

                // RELASI DI INDEX 20, 21, 22
                $tahun_masuk   = $cleanNull($row[20]);
                $level_masuk   = $cleanNull($row[21]);
                $ruangan_masuk = $cleanNull($row[22]);

                if (empty($nism) || empty($nama_lengkap) || empty($wali_murid_id)) {
                    $gagal_kolom++;
                    continue;
                }

                $waliEksis = \App\Models\WaliMurid::where('id', $wali_murid_id)->exists();
                if (!$waliEksis) {
                    $gagal_wali++;
                    continue;
                }

                if (!empty($id)) {
                    $murid = \App\Models\Murid::find($id);
                    if (!$murid) {
                        $murid = new \App\Models\Murid();
                        $murid->id = $id;
                    }
                } else {
                    $murid = \App\Models\Murid::where('nism', $nism)->first();
                    if (!$murid) {
                        $murid = new \App\Models\Murid();
                    }
                }

                $murid->wali_murid_id  = $wali_murid_id;
                $murid->nism           = $nism;
                $murid->nisn           = $nisn;
                $murid->nik            = $nik;
                $murid->nama_lengkap   = $nama_lengkap;
                $murid->nama_panggilan = $nama_panggilan;
                $murid->jenis_kelamin  = $jenis_kelamin;
                $murid->tempat_lahir   = $tempat_lahir;
                $murid->tanggal_lahir  = $tanggal_lahir;
                $murid->anak_ke        = $anak_ke;
                $murid->hub_kel        = $hub_kel;

                // Masukkan data orang tua
                $murid->nik_ayah       = $nik_ayah;
                $murid->nama_ayah      = $nama_ayah;
                $murid->status_ayah    = $status_ayah;
                $murid->nik_ibu        = $nik_ibu;
                $murid->nama_ibu       = $nama_ibu;
                $murid->status_ibu     = $status_ibu;

                $murid->foto           = $foto;
                $murid->status         = $status;

                $murid->tahun_masuk    = $tahun_masuk;
                $murid->level_masuk    = $level_masuk;
                $murid->ruangan_masuk  = $ruangan_masuk;

                $murid->save();

                $berhasil++;
            }
            DB::commit();
            fclose($handle);

            $pesan = "Import Selesai! Berhasil: $berhasil data murid.";
            if ($gagal_kolom > 0) $pesan .= " Gagal format/kosong: $gagal_kolom baris.";
            if ($gagal_wali > 0) $pesan .= " Gagal karena ID Wali tidak cocok: $gagal_wali baris.";

            return redirect()->route('murid.index')->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->route('murid.index')->withErrors(['file_import' => 'Terjadi kesalahan sistem saat import. Error: ' . $e->getMessage()]);
        }
    }


    // =========================================================================
    // FITUR YATIM (Query Diperbarui karena status_ayah pindah ke tabel murids)
    // =========================================================================

    public function filterYatim(Request $request)
    {
        $batasUmur = \Carbon\Carbon::now()->subYears(15);
        $tahunAktif = \App\Models\TahunPelajaran::where('is_active', true)->first();

        if (!$tahunAktif) {
            return redirect()->back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $tahunAktifId = $tahunAktif->id;

        $murids = Murid::with(['waliMurid', 'ruangans'])
            ->where('murids.status', 'Aktif') // Spesifikkan nama tabel karena ada join
            ->where('murids.tanggal_lahir', '>', $batasUmur)

            // JAUH LEBIH CEPAT: Langsung cek di kolom murid, tidak perlu whereHas lagi!
            ->where('murids.status_ayah', 'Meninggal')

            ->whereHas('ruangans', function ($query) use ($tahunAktifId) {
                $query->where('murid_ruangans.tahun_pelajaran_id', $tahunAktifId);
            })
            ->join('murid_ruangans', 'murids.id', '=', 'murid_ruangans.murid_id')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $tahunAktifId)
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.nism', 'asc')
            ->select('murids.*')
            ->get();

        return view('murid.yatim', compact('murids', 'tahunAktif'));
    }

    public function downloadYatim()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        if (!$tahunAktif) {
            return redirect()->back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $batasUmur = \Carbon\Carbon::now()->subYears(15);

        $murids = Murid::with(['waliMurid.kampung', 'ruangans'])
            ->where('murids.status', 'Aktif')
            ->where('murids.tanggal_lahir', '>', $batasUmur)

            // Mengganti whereHas menjadi where langsung ke tabel
            ->where('murids.status_ayah', 'Meninggal')

            ->join('murid_ruangans', 'murids.id', '=', 'murid_ruangans.murid_id')
            ->join('ruangans', 'murid_ruangans.ruangan_id', '=', 'ruangans.id')
            ->join('levels', 'ruangans.level_id', '=', 'levels.id')
            ->where('murid_ruangans.tahun_pelajaran_id', $tahunAktif->id)
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('murids.nism', 'asc')
            ->select('murids.*')
            ->get()
            ->groupBy('wali_murid_id');

        $pdf = Pdf::loadView('cetak-baru.pdf_yatim', compact('murids', 'tahunAktif'))
            ->setPaper('a4');

        return $pdf->download('Data_Murid_Yatim_' . $tahunAktif->nama_hijriyah . '.pdf');
    }


    public function updateFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ], [
            'foto.required' => 'File foto wajib diunggah.',
            'foto.image'    => 'File yang diunggah harus berupa gambar.',
            'foto.mimes'    => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max'      => 'Ukuran foto maksimal 2MB.',
        ]);

        $murid = \App\Models\Murid::findOrFail($id);

        if ($murid->foto && Storage::disk('public')->exists($murid->foto)) {
            Storage::disk('public')->delete($murid->foto);
        }

        $murid->foto = $request->file('foto')->store('uploads/murid', 'public');
        $murid->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Foto santri ' . $murid->nama_lengkap . ' berhasil diperbarui!',
                'foto_url' => asset('storage/' . $murid->foto),
                'murid_id' => $murid->id,
            ]);
        }

        return back()->with('success', 'Foto berhasil diperbarui!');
    }
}
