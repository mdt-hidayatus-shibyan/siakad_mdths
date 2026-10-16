<?php

namespace App\Http\Controllers\Ikhtibar;

use App\Http\Controllers\Controller;
use App\Models\Ikhtibar\DivisiIkhtibar;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\Ikhtibar\PanitiaIkhtibar;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PanitiaIkhtibarController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->input('tahun_pelajaran_id', $tahunAktif ? $tahunAktif->id : null);

        $kegiatan = KegiatanIkhtibar::firstOrCreate(
            ['tahun_pelajaran_id' => $selectedTahunId],
            [
                'nama_kegiatan'   => 'Ikhtibar & Haflah Akhirussanah',
                'ikhtibar_ke'     => '52 Th',
                'status_kegiatan' => 'Persiapan',
            ]
        );

        $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)->orderBy('urutan')->get();

        $query = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['ustadz', 'divisi']);

        if ($request->filled('kelompok_jabatan')) {
            $query->where('kelompok_jabatan', $request->kelompok_jabatan);
        }

        if ($request->filled('divisi_id')) {
            $query->where('divisi_ikhtibar_id', $request->divisi_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhereHas('ustadz', function ($qu) use ($search) {
                      $qu->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nigm', 'like', "%{$search}%");
                  });
            });
        }

        $panitiaList = $query->orderByRaw("FIELD(kelompok_jabatan, 'BPH', 'Divisi') ASC")
            ->orderByRaw("FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Wakil Sekretaris', 'Bendahara', 'Wakil Bendahara', 'Koordinator Divisi', 'Anggota Divisi') ASC")
            ->orderBy('id', 'asc')
            ->get();

        $bphCount = $panitiaList->where('kelompok_jabatan', 'BPH')->count();
        $divisiCount = $panitiaList->where('kelompok_jabatan', 'Divisi')->count();
        $ustadzCount = $panitiaList->where('tipe_personel', 'ustadz')->count();
        $eksternalCount = $panitiaList->where('tipe_personel', 'eksternal')->count();

        if ($request->ajax()) {
            return view('ikhtibar.panitia.list', compact('panitiaList'));
        }

        return view('ikhtibar.panitia.index', compact(
            'kegiatan',
            'panitiaList',
            'divisis',
            'tahunPelajarans',
            'tahunAktif',
            'selectedTahunId',
            'bphCount',
            'divisiCount',
            'ustadzCount',
            'eksternalCount'
        ));
    }

    /**
     * Modal Form Tambah Panitia (AJAX)
     */
    public function create(Request $request, $kegiatanId)
    {
        if ($request->ajax()) {
            $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);
            $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)->where('is_active', true)->orderBy('urutan')->get();
            $ustadzs = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();

            return view('ikhtibar.panitia.form', compact('kegiatan', 'divisis', 'ustadzs'));
        }
        return redirect()->route('panitia-ikhtibar.index');
    }

    /**
     * Simpan Panitia Baru
     */
    public function store(Request $request, $kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);

        $rules = [
            'kelompok_jabatan'   => 'required|in:BPH,Divisi',
            'jabatan'            => 'required|string|max:100',
            'divisi_ikhtibar_id' => 'nullable|required_if:kelompok_jabatan,Divisi|exists:divisi_ikhtibars,id',
            'tipe_personel'      => 'required|in:ustadz,eksternal',
            'is_active'          => 'nullable|boolean',
            'keterangan'         => 'nullable|string',
        ];

        if ($request->tipe_personel === 'ustadz') {
            $rules['ustadz_id'] = 'required|exists:ustadzs,id';
        } else {
            $rules['nama_lengkap']  = 'required|string|max:255';
            $rules['nik']           = 'nullable|string|max:30';
            $rules['jenis_kelamin'] = 'nullable|in:L,P';
            $rules['no_hp']         = 'nullable|string|max:30';
            $rules['unsur_asal']    = 'nullable|string|max:100';
            $rules['alamat']        = 'nullable|string';
            $rules['foto']          = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
            $rules['tanda_tangan']  = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
        }

        $validated = $request->validate($rules);
        $validated['kegiatan_ikhtibar_id'] = $kegiatan->id;
        $validated['tahun_pelajaran_id']   = $kegiatan->tahun_pelajaran_id;
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('ikhtibar/panitia/foto', 'public');
        }

        if ($request->hasFile('tanda_tangan')) {
            $validated['tanda_tangan'] = $request->file('tanda_tangan')->store('ikhtibar/panitia/ttd', 'public');
        }

        PanitiaIkhtibar::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Personel Panitia Ikhtibar berhasil ditambahkan!'
        ], 200);
    }

    /**
     * Modal Form Edit Panitia (AJAX)
     */
    public function edit($id, Request $request)
    {
        if ($request->ajax()) {
            $panitia = PanitiaIkhtibar::findOrFail($id);
            $kegiatan = $panitia->kegiatanIkhtibar;
            $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)->where('is_active', true)->orderBy('urutan')->get();
            $ustadzs = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();

            return view('ikhtibar.panitia.form', compact('panitia', 'kegiatan', 'divisis', 'ustadzs'));
        }
        return redirect()->route('panitia-ikhtibar.index');
    }

    /**
     * Update Panitia
     */
    public function update(Request $request, $id)
    {
        $panitia = PanitiaIkhtibar::findOrFail($id);

        $rules = [
            'kelompok_jabatan'   => 'required|in:BPH,Divisi',
            'jabatan'            => 'required|string|max:100',
            'divisi_ikhtibar_id' => 'nullable|required_if:kelompok_jabatan,Divisi|exists:divisi_ikhtibars,id',
            'tipe_personel'      => 'required|in:ustadz,eksternal',
            'is_active'          => 'nullable|boolean',
            'keterangan'         => 'nullable|string',
        ];

        if ($request->tipe_personel === 'ustadz') {
            $rules['ustadz_id'] = 'required|exists:ustadzs,id';
        } else {
            $rules['nama_lengkap']  = 'required|string|max:255';
            $rules['nik']           = 'nullable|string|max:30';
            $rules['jenis_kelamin'] = 'nullable|in:L,P';
            $rules['no_hp']         = 'nullable|string|max:30';
            $rules['unsur_asal']    = 'nullable|string|max:100';
            $rules['alamat']        = 'nullable|string';
            $rules['foto']          = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
            $rules['tanda_tangan']  = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        if ($request->hasFile('foto')) {
            if ($panitia->foto && Storage::disk('public')->exists($panitia->foto)) {
                Storage::disk('public')->delete($panitia->foto);
            }
            $validated['foto'] = $request->file('foto')->store('ikhtibar/panitia/foto', 'public');
        }

        if ($request->hasFile('tanda_tangan')) {
            if ($panitia->tanda_tangan && Storage::disk('public')->exists($panitia->tanda_tangan)) {
                Storage::disk('public')->delete($panitia->tanda_tangan);
            }
            $validated['tanda_tangan'] = $request->file('tanda_tangan')->store('ikhtibar/panitia/ttd', 'public');
        }

        $panitia->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Personel Panitia berhasil diperbarui!'
        ], 200);
    }

    /**
     * Hapus Panitia (AJAX)
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            $panitia = PanitiaIkhtibar::findOrFail($id);
            $panitia->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Panitia berhasil dihapus!'
            ], 200);
        }
        return redirect()->route('panitia-ikhtibar.index');
    }

    /**
     * Toggle Status Panitia (AJAX)
     */
    public function toggleStatus(Request $request, $id)
    {
        $panitia = PanitiaIkhtibar::findOrFail($id);
        $panitia->is_active = $request->has('is_active') ? (bool) $request->input('is_active') : !$panitia->is_active;
        $panitia->save();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Status aktif panitia berhasil diubah!',
            'is_active' => $panitia->is_active
        ], 200);
    }

    /**
     * Cetak Surat Keputusan (SK) Susunan Kepanitiaan
     */
    public function cetakSk($kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::with('tahunPelajaran')->findOrFail($kegiatanId);
        $bph = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
            ->where('kelompok_jabatan', 'BPH')
            ->where('is_active', true)
            ->with('ustadz')
            ->get();

        $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
            ->where('is_active', true)
            ->with(['panitias' => function ($q) {
                $q->where('is_active', true)->with('ustadz');
            }])
            ->orderBy('urutan')
            ->get();

        $pengasuh = class_exists(\App\Models\Kepengurusan\Pengurus::class) 
            ? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh') 
            : null;

        return view('ikhtibar.panitia.cetak-sk', compact('kegiatan', 'bph', 'divisis', 'pengasuh'));
    }

    /**
     * Cetak Kartu Tanda Panitia (ID Card)
     */
    public function cetakIdCard($kegiatanId, Request $request)
    {
        $kegiatan = KegiatanIkhtibar::with('tahunPelajaran')->findOrFail($kegiatanId);
        $panitias = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
            ->where('is_active', true)
            ->with(['ustadz', 'divisi'])
            ->get();

        return view('ikhtibar.panitia.cetak-id-card', compact('kegiatan', 'panitias'));
    }
}

