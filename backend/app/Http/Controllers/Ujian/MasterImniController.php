<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\Ujian\Ujian;
use Illuminate\Http\Request;

class MasterImniController extends Controller
{
    /**
     * Halaman Utama Master Ujian IMNI
     */
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $tahunPelajarans->first();
        $selectedTahunId = $request->input('tahun_pelajaran_id', $tahunAktif?->id);
        $selectedTingkatId = $request->input('tingkat_id');

        $tingkats = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        $query = Ujian::with(['tahunPelajaran', 'semester_relasi', 'tingkat'])
            ->where('tipe_ujian', 'IMNI');

        if ($selectedTahunId) {
            $query->where('tahun_pelajaran_id', $selectedTahunId);
        }

        if ($selectedTingkatId) {
            $query->where('tingkat_id', $selectedTingkatId);
        }

        if ($request->filled('search')) {
            $query->where('nama_ujian', 'like', '%' . $request->search . '%');
        }

        $ujians = $query->orderBy('id', 'desc')->get();

        return view('ujian.panitia-imni.master.index', compact(
            'ujians',
            'tahunPelajarans',
            'selectedTahunId',
            'tingkats',
            'selectedTingkatId'
        ));
    }

    /**
     * Modal Form Tambah Agenda Ujian IMNI (AJAX)
     */
    public function create(Request $request)
    {
        $tahun_pelajarans = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $tahun_pelajarans->first();
        $selectedTahunId = $request->input('tahun_pelajaran_id', $tahunAktif?->id);

        // Filter Semester: Ambil hanya Semester Akhir (Semester 2 / Genap)
        $semesters = Semester::where('tahun_pelajaran_id', $selectedTahunId)
            ->where(function ($q) {
                $q->where('nama_semester', 'like', '%2%')
                    ->orWhere('nama_semester', 'like', '%Genap%')
                    ->orWhere('nama_semester', 'like', '%Akhir%');
            })
            ->get();

        if ($semesters->isEmpty()) {
            $semesters = Semester::where('tahun_pelajaran_id', $selectedTahunId)
                ->orderBy('id', 'desc')
                ->take(1)
                ->get();
        }

        if ($semesters->isEmpty()) {
            $semesters = Semester::all();
        }

        $tingkats = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.master.form', compact('tahun_pelajarans', 'tahunAktif', 'semesters', 'tingkats', 'selectedTahunId'));
        }

        return redirect()->route('master-imni.index');
    }

    /**
     * Simpan Agenda Ujian IMNI Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'semester_id'        => 'required|exists:semesters,id',
            'tingkat_id'         => 'nullable|exists:tingkats,id',
            'nama_ujian'         => 'required|string|max:255',
            'tanggal_mulai'      => 'nullable|date',
            'tanggal_selesai'    => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan'         => 'nullable|string|max:500',
        ]);

        $data = $request->all();
        $data['tipe_ujian'] = 'IMNI';
        $data['tingkat_id'] = $request->filled('tingkat_id') ? $request->tingkat_id : null;

        Ujian::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Agenda Master Ujian IMNI berhasil ditambahkan!'
            ], 200);
        }

        return redirect()->route('master-imni.index', ['tahun_pelajaran_id' => $request->tahun_pelajaran_id])
            ->with('success', 'Agenda Master Ujian IMNI berhasil ditambahkan!');
    }

    /**
     * Modal Form Edit Agenda Ujian IMNI (AJAX)
     */
    public function edit($id, Request $request)
    {
        $ujian = Ujian::findOrFail($id);
        $tahun_pelajarans = TahunPelajaran::orderBy('id', 'desc')->get();

        // Filter Semester: Ambil Semester Akhir (Semester 2 / Genap)
        $semesters = Semester::where('tahun_pelajaran_id', $ujian->tahun_pelajaran_id)
            ->where(function ($q) {
                $q->where('nama_semester', 'like', '%2%')
                    ->orWhere('nama_semester', 'like', '%Genap%')
                    ->orWhere('nama_semester', 'like', '%Akhir%');
            })
            ->get();

        if ($semesters->isEmpty()) {
            $semesters = Semester::where('tahun_pelajaran_id', $ujian->tahun_pelajaran_id)
                ->orderBy('id', 'desc')
                ->take(1)
                ->get();
        }

        if ($semesters->isEmpty()) {
            $semesters = Semester::all();
        }

        $tingkats = Tingkat::where('is_active', true)->orderBy('urutan_tingkat', 'asc')->get();

        if ($request->ajax()) {
            return view('ujian.panitia-imni.master.form', compact('ujian', 'tahun_pelajarans', 'semesters', 'tingkats'));
        }

        return redirect()->route('master-imni.index');
    }

    /**
     * Update Agenda Ujian IMNI
     */
    public function update(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);

        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'semester_id'        => 'required|exists:semesters,id',
            'tingkat_id'         => 'nullable|exists:tingkats,id',
            'nama_ujian'         => 'required|string|max:255',
            'tanggal_mulai'      => 'nullable|date',
            'tanggal_selesai'    => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan'         => 'nullable|string|max:500',
        ]);

        $data = $request->all();
        $data['tipe_ujian'] = 'IMNI';
        $data['tingkat_id'] = $request->filled('tingkat_id') ? $request->tingkat_id : null;

        $ujian->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Agenda Master Ujian IMNI berhasil diperbarui!'
            ], 200);
        }

        return redirect()->route('master-imni.index', ['tahun_pelajaran_id' => $request->tahun_pelajaran_id])
            ->with('success', 'Agenda Master Ujian IMNI berhasil diperbarui!');
    }

    /**
     * Hapus Agenda Ujian IMNI
     */
    public function destroy($id)
    {
        $ujian = Ujian::findOrFail($id);
        $ujian->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Agenda Master Ujian IMNI berhasil dihapus!'
            ], 200);
        }

        return redirect()->back()->with('success', 'Agenda Master Ujian IMNI berhasil dihapus!');
    }
}
