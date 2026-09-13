<?php

namespace App\Http\Controllers\Kepengurusan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kepengurusan\PeriodePengurusRequest;
use App\Models\Kepengurusan\PeriodeKepengurusan;
use Illuminate\Http\Request;

class PeriodeKepengurusanController extends Controller
{
    public function index()
    {
        $periode = PeriodeKepengurusan::latest()->get();
        return view('kepengurusan.periode.index', compact('periode'));
    }

    public function create(Request $request)
    {
        if ($request->ajax()) {
            return view('kepengurusan.periode.form');
        }
        return redirect()->route('periode-pengurus.index')->with('error', 'Silakan gunakan tombol tambah data.');
    }

    public function store(PeriodePengurusRequest $request)
    {
        $data = $request->validated();
        if (!empty($data['is_seumur_hidup'])) {
            $data['tanggal_selesai'] = null;
        }

        PeriodeKepengurusan::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Data Periode Pengurus berhasil ditambahkan!'
        ], 200);
    }

    public function edit(Request $request, $id)
    {
        if ($request->ajax()) {
            $periode = PeriodeKepengurusan::findOrFail($id);
            return view('kepengurusan.periode.form', compact('periode'));
        }
        return redirect()->route('periode-pengurus.index')->with('error', 'Silakan gunakan tombol edit data.');
    }

    public function update(PeriodePengurusRequest $request, $id)
    {
        $periodeKepengurusan = PeriodeKepengurusan::findOrFail($id);
        $data = $request->validated();
        if (!empty($data['is_seumur_hidup'])) {
            $data['tanggal_selesai'] = null;
        }

        $periodeKepengurusan->update($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Data Periode Pengurus berhasil dirubah!'
        ], 200);
    }

    public function destroy($id)
    {
        if (request()->ajax()) {
            PeriodeKepengurusan::findOrFail($id)->delete();
            return response()->json(['message' => 'periode berhasil dihapus!']);
        }
        return redirect()->route('periode-pengurus.index')->with('error', 'Silakan gunakan tombol hapus data.');
    }
}
