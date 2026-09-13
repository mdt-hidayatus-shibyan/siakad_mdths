<?php

namespace App\Http\Controllers\UjianAlquran;

use App\Http\Controllers\Controller;
use App\Http\Requests\UjianAlquran\UjianAlquranRequest;
use App\Models\TahunPelajaran;
use App\Models\UjianAlquran\UjianAlquran;
use Illuminate\Http\Request;

class PengaturanUjianAlquranController extends Controller
{
    /**
     * Dashboard & Daftar Master Agenda Ujian Al-Qur'an
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;

        $ujians = UjianAlquran::with(['tahunPelajaran', 'juris.ustadz'])
            ->withCount([
                'pesertas',
                'pesertas as pesertas_lulus_count' => fn($q) => $q->where('status_kelulusan', 'Lulus'),
                'pesertas as pesertas_tidak_lulus_count' => fn($q) => $q->where('status_kelulusan', 'Tidak Lulus'),
                'pesertas as pesertas_belum_count' => fn($q) => $q->where('status_kelulusan', 'Belum Diuji'),
            ])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->get();

        $tahunAktif = $daftarTahun->firstWhere('id', $tahunPelajaranId);

        return view('ujian-alquran.index', compact('daftarTahun', 'tahunPelajaranId', 'ujians', 'tahunAktif'));
    }

    /**
     * Tampilkan Modal Form Tambah Event Ujian Al-Qur'an (AJAX)
     */
    public function create(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()?->id;
        $tahunAktif = $daftarTahun->firstWhere('id', $tahunPelajaranId);

        return view('ujian-alquran.form', compact('daftarTahun', 'tahunPelajaranId', 'tahunAktif'));
    }

    /**
     * Simpan Event Ujian Al-Qur'an Baru
     */
    public function store(UjianAlquranRequest $request)
    {
        $ujian = UjianAlquran::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agenda Ujian Al-Qur\'an berhasil dibuat.',
                'data'    => $ujian,
            ]);
        }

        return redirect()->route('peserta-ujian-alquran.index', ['tahun_id' => $ujian->tahun_pelajaran_id])
            ->with('success', 'Agenda Ujian Al-Qur\'an berhasil dibuat. Silakan tarik peserta murid.');
    }

    /**
     * Tampilkan Modal Form Edit Event Ujian Al-Qur'an (AJAX)
     */
    public function edit($id)
    {
        $ujian = UjianAlquran::with('tahunPelajaran')->findOrFail($id);
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunPelajaranId = $ujian->tahun_pelajaran_id;
        $tahunAktif = $ujian->tahunPelajaran;

        return view('ujian-alquran.form', compact('ujian', 'daftarTahun', 'tahunPelajaranId', 'tahunAktif'));
    }

    /**
     * Update Pengaturan Event Ujian Al-Qur'an
     */
    public function update(UjianAlquranRequest $request, $id)
    {
        $ujian = UjianAlquran::findOrFail($id);
        $ujian->update($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi Agenda Ujian Al-Qur\'an berhasil diperbarui.',
                'data'    => $ujian,
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan Ujian Al-Qur\'an berhasil diperbarui.');
    }

    /**
     * Hapus Event Ujian Al-Qur'an
     */
    public function destroy($id)
    {
        $ujian = UjianAlquran::findOrFail($id);
        $ujian->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agenda Ujian Al-Qur\'an berhasil dihapus.',
            ]);
        }

        return redirect()->route('ujian-alquran.index')->with('success', 'Agenda Ujian Al-Qur\'an berhasil dihapus.');
    }
}
