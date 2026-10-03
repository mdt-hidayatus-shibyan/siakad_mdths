<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ujian\PanitiaImniRequest;
use App\Models\TahunPelajaran;
use App\Models\Ujian\PanitiaImni;
use App\Models\Ustadz;
use Illuminate\Http\Request;

class PanitiaImniController extends Controller
{
    /**
     * Halaman Utama Kepanitiaan IMNI
     */
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->input('tahun_pelajaran_id', $tahunAktif ? $tahunAktif->id : null);

        $query = PanitiaImni::with(['ustadz', 'tahunPelajaran']);
        if ($selectedTahunId) {
            $query->where('tahun_pelajaran_id', $selectedTahunId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('ustadz', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nigm', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->jabatan);
        }

        $panitiaList = $query->orderByRaw("FIELD(jabatan, 'Ketua', 'Bendahara', 'Anggota') ASC")
            ->orderBy('id', 'asc')
            ->get();

        // Ringkasan Kepanitiaan
        $ketua = $panitiaList->firstWhere('jabatan', 'Ketua');
        $bendahara = $panitiaList->firstWhere('jabatan', 'Bendahara');
        $anggotaList = $panitiaList->where('jabatan', 'Anggota');

        // Ustadz aktif untuk dropdown pilihan
        $ustadzList = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();

        return view('ujian.panitia-imni.index', compact(
            'panitiaList',
            'tahunPelajarans',
            'tahunAktif',
            'selectedTahunId',
            'ketua',
            'bendahara',
            'anggotaList',
            'ustadzList'
        ));
    }

    /**
     * Tampilkan Modal Form Tambah Panitia (AJAX)
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            $tahun_pelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $selectedTahunId = $request->input('tahun_pelajaran_id', $tahunAktif ? $tahunAktif->id : null);
            
            // Filter ustadz yang belum masuk kepanitiaan di tahun terpilih
            $assignedUstadzIds = PanitiaImni::where('tahun_pelajaran_id', $selectedTahunId)->pluck('ustadz_id')->toArray();
            $ustadzs = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();

            return view('ujian.panitia-imni.form', compact(
                'tahun_pelajarans',
                'tahunAktif',
                'selectedTahunId',
                'ustadzs',
                'assignedUstadzIds'
            ));
        }

        return redirect()->route('panitia-imni.index')->with('error', 'Silakan gunakan tombol tambah data.');
    }

    /**
     * Simpan Data Panitia Baru
     */
    public function store(PanitiaImniRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        PanitiaImni::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Anggota Kepanitiaan IMNI berhasil ditambahkan!'
        ], 200);
    }

    /**
     * Tampilkan Modal Form Edit Panitia (AJAX)
     */
    public function edit($id, Request $request)
    {
        if ($request->ajax()) {
            $panitia = PanitiaImni::with('ustadz')->findOrFail($id);
            $tahun_pelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
            $ustadzs = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();

            return view('ujian.panitia-imni.form', compact(
                'panitia',
                'tahun_pelajarans',
                'ustadzs'
            ));
        }

        return redirect()->route('panitia-imni.index')->with('error', 'Silakan gunakan tombol edit data.');
    }

    /**
     * Update Data Panitia
     */
    public function update(PanitiaImniRequest $request, $id)
    {
        $panitia = PanitiaImni::findOrFail($id);
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        $panitia->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Kepanitiaan IMNI berhasil diperbarui!'
        ], 200);
    }

    /**
     * Hapus Data Panitia
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            $panitia = PanitiaImni::findOrFail($id);
            $panitia->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Anggota Kepanitiaan IMNI berhasil dihapus!'
            ], 200);
        }

        return redirect()->route('panitia-imni.index')->with('error', 'Silakan gunakan tombol hapus data.');
    }

    /**
     * Toggle Status Aktif Panitia
     */
    public function toggleStatus($id)
    {
        $panitia = PanitiaImni::findOrFail($id);
        $panitia->is_active = !$panitia->is_active;
        $panitia->save();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Status kepanitiaan berhasil diubah!',
            'is_active' => $panitia->is_active
        ], 200);
    }
}
