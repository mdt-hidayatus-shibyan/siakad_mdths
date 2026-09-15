<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Http\Requests\MasterData\WaliMuridRequest;
use App\Models\Kampung;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use App\Repositories\MuridRuanganRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaliMuridController extends Controller
{
    protected $muridRuanganRepo;

    public function __construct(MuridRuanganRepository $muridRuanganRepo)
    {
        $this->muridRuanganRepo = $muridRuanganRepo;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterKampung = $request->input('kampung_id');

        $walis = WaliMurid::with('kampung')->withCount('murids')
            ->when($filterKampung, function ($query, $filterKampung) {
                return $query->where('kampung_id', $filterKampung);
            })

            ->when($search, function ($query, $search) {
                $searchHash = hash_sensitive($search);
                return $query->where('nama_kepala_keluarga', 'like', "%{$search}%")
                    ->orWhere('no_kk_hash', $searchHash)
                    ->orWhere('no_registrasi', 'like', "%{$search}%");
            })
            ->orderBy('no_registrasi', 'desc') // Diubah dari nama_ayah
            ->paginate(12)
            ->withQueryString();

        $kampungs = Kampung::orderBy('kode', 'asc')->get();

        return view('wali-murid.index', compact('walis', 'kampungs', 'filterKampung'));
    }

    public function show($id)
    {
        $wali = WaliMurid::with(['kampung', 'murids'])->findOrFail($id);
        return view('wali-murid.show', compact('wali'));
    }

    public function linkAnak(Request $request, $id)
    {
        $request->validate([
            'nism' => 'required|string'
        ]);

        $wali = WaliMurid::findOrFail($id);
        $murid = \App\Models\Murid::with('waliMurid')->where('nism', $request->nism)->first();

        if (!$murid) {
            return back()->with('error', 'NISM "' . $request->nism . '" tidak ditemukan. Pastikan data murid sudah diinput di menu Murid.');
        }

        if ($murid->wali_murid_id == $wali->id) {
            return back()->with('warning', 'Murid atas nama ' . $murid->nama_lengkap . ' sudah berada di dalam daftar Keluarga Anda ini.');
        }

        if ($murid->wali_murid_id != null && $murid->wali_murid_id != $wali->id) {
            // Lebih ringkas karena sudah pakai nama_kepala_keluarga
            $namaWaliLama = $murid->waliMurid->nama_kepala_keluarga;

            return back()->with('error', 'Gagal! Murid ' . $murid->nama_lengkap . ' sudah terdaftar di KK Keluarga Bpk/Ibu ' . ($namaWaliLama ?: 'Lainnya') . '. Silakan ubah langsung di Data Murid jika ingin memindahkannya.');
        }

        $murid->wali_murid_id = $wali->id;
        $murid->save();

        return back()->with('success', 'Murid atas nama ' . $murid->nama_lengkap . ' berhasil ditautkan ke keluarga ini!');
    }

    public function unlinkAnak($id, $murid_id)
    {
        $murid = \App\Models\Murid::findOrFail($murid_id);

        if ($murid->wali_murid_id == $id) {
            $murid->wali_murid_id = null;
            $murid->save();

            return back()->with('success', 'Tautan murid atas nama ' . $murid->nama_lengkap . ' berhasil dilepas dari Kartu Keluarga ini.');
        }

        return back()->with('error', 'Validasi gagal: Murid tidak terdaftar di KK ini.');
    }

    public function create()
    {
        $kampungs = Kampung::orderBy('nama_kampung', 'asc')->get();
        return view('wali-murid.form', compact('kampungs'));
    }

    public function store(WaliMuridRequest $request)
    {
        $data = $request->validated();
        $data['is_asatidz'] = $request->has('is_asatidz');
        $data['is_active']  = $request->has('is_active');

        WaliMurid::create($data);
        return redirect()->route('wali-murid.index')->with('success', 'Data Keluarga berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $wali = WaliMurid::findOrFail($id);
        $kampungs = Kampung::orderBy('nama_kampung', 'asc')->get();
        return view('wali-murid.form', compact('wali', 'kampungs'));
    }

    public function update(WaliMuridRequest $request, $id)
    {
        $wali = WaliMurid::findOrFail($id);

        $data = $request->validated();
        $data['is_asatidz'] = $request->has('is_asatidz');
        $data['is_active']  = $request->has('is_active');

        $wali->update($data);
        return redirect()->route('wali-murid.index')->with('success', 'Data Keluarga berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $wali = WaliMurid::findOrFail($id);
        $wali->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Wali Murid berhasil dihapus!'], 200);
    }

    public function searchKk(Request $request)
    {
        $no_kk = $request->query('no_kk');
        $wali = WaliMurid::with('kampung')->whereNoKk($no_kk)->first();

        if ($wali) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id'          => $wali->id,
                    'no_kk'       => $wali->no_kk,
                    'nama_kepala' => $wali->nama_kepala_keluarga, // Diubah
                    'kampung'     => $wali->kampung->nama_kampung ?? '-'
                ]
            ]);
        }

        return response()->json(['status' => 'not_found']);
    }

    public function modalImport()
    {
        return view('wali-murid.import');
    }

    public function template()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_wali_murid.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Struktur CSV yang jauh lebih ringkas
        $columns = [
            'id',
            'no_kk',
            'kepala_keluarga',
            'nama_kepala_keluarga',
            'no_hp',
            'alamat_detail',
            'kampung_id',
            'is_asatidz',
            'is_active'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, [
                '',
                '3501234567890123',
                'Ayah',
                'Ahmad Fulan',
                '08123456789',
                'Jl. Mawar No. 2 RT 01',
                '10',
                '0',
                '1'
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
        $gagal_kampung = 0;

        DB::beginTransaction();
        try {
            $cleanNull = function ($value) {
                $trimmed = trim($value);
                if ($trimmed === '' || strtolower($trimmed) === 'null' || $trimmed === '-') {
                    return null;
                }
                return $trimmed;
            };

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

                $row = array_filter($row, function ($value) {
                    return $value !== null;
                });

                // Syarat kolom berkurang karena data biologis dihapus (sekarang 9 kolom)
                if (count($row) < 9) {
                    $gagal_kolom++;
                    continue;
                }

                $id                   = $cleanNull($row[0]);
                $no_kk                = $cleanNull($row[1]);

                $inputKepala = strtolower(trim($row[2]));
                if (in_array($inputKepala, ['wali', 'wali lainnya', 'wali lain'])) {
                    $kepala_keluarga = 'Wali';
                } elseif ($inputKepala === 'ibu') {
                    $kepala_keluarga = 'Ibu';
                } else {
                    $kepala_keluarga = 'Ayah';
                }

                $nama_kepala_keluarga = $cleanNull($row[3]);
                $no_hp                = $cleanNull($row[4]);
                $alamat_detail        = $cleanNull($row[5]);
                $kampung_id           = trim($row[6]);

                $is_asatidz           = in_array(strtolower(trim($row[7])), ['1', 'true', 'ya', 'y']) ? true : false;
                $is_active            = in_array(strtolower(trim($row[8])), ['0', 'false', 'tidak', 't', 'n']) ? false : true;

                $kampungEksis = \App\Models\Kampung::where('id', $kampung_id)->exists();
                if (!$kampungEksis) {
                    $gagal_kampung++;
                    continue;
                }

                $wali = null;

                if (!empty($id)) {
                    $wali = WaliMurid::find($id);
                    if (!$wali) {
                        $wali = new WaliMurid();
                        $wali->id = $id;
                    }
                } else {
                    if (!empty($no_kk)) {
                        $wali = WaliMurid::whereNoKk($no_kk)->first();
                    }

                    if (!$wali) {
                        $wali = new WaliMurid();
                    }
                }

                $wali->no_kk                = $no_kk;
                $wali->kepala_keluarga      = $kepala_keluarga;
                $wali->nama_kepala_keluarga = $nama_kepala_keluarga;
                $wali->no_hp                = $no_hp;
                $wali->alamat_detail        = $alamat_detail;
                $wali->kampung_id           = $kampung_id;
                $wali->is_asatidz           = $is_asatidz;
                $wali->is_active            = $is_active;

                $wali->save();
                $berhasil++;
            }

            DB::commit();
            fclose($handle);

            $pesan = "Import Selesai! Berhasil: $berhasil data.";
            if ($gagal_kolom > 0) $pesan .= " Gagal/Format Kolom Rusak: $gagal_kolom baris.";
            if ($gagal_kampung > 0) $pesan .= " Gagal/ID Kampung Tidak Ditemukan: $gagal_kampung baris.";

            return redirect()->route('wali-murid.index')->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->route('wali-murid.index')->withErrors(['file_import' => 'Terjadi kesalahan sistem saat import. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Cetak Data Wali Murid Aktif per Kode Kampung beserta Putra-Putri & Ruangannya
     */
    public function cetak(Request $request)
    {
        $kampung_id = $request->query('kampung_id');
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $groupedWalis = $this->muridRuanganRepo->getWaliMuridAktifGroupedByKampung($tahunAktif?->id, $kampung_id);
        $filterKampung = $kampung_id ? Kampung::find($kampung_id) : null;

        return view('cetak-baru.cetak-wali-murid', compact('groupedWalis', 'tahunAktif', 'filterKampung'));
    }

    /**
     * Export Excel Data Wali Murid Aktif per Kode Kampung beserta Putra-Putri & Ruangannya
     */
    public function exportExcel(Request $request)
    {
        $kampung_id = $request->query('kampung_id');
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $groupedWalis = $this->muridRuanganRepo->getWaliMuridAktifGroupedByKampung($tahunAktif?->id, $kampung_id);
        $filterKampung = $kampung_id ? Kampung::find($kampung_id) : null;

        $namaFileSuffix = $filterKampung ? str_replace(' ', '_', $filterKampung->nama_kampung) : 'Semua_Kampung';
        $filename = "Data_Wali_Murid_Aktif_{$namaFileSuffix}_" . date('Ymd_His') . '.xls';

        $content = view('cetak-baru.export-wali-murid-excel', compact('groupedWalis', 'tahunAktif', 'filterKampung'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
