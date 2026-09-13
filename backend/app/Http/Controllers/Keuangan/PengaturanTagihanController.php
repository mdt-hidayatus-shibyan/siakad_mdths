<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;

use App\Http\Requests\Keuangan\PengaturanTagihanRequest;
use App\Models\Level;
use App\Models\PengaturanTagihan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;


class PengaturanTagihanController extends Controller
{


    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'asc')->get();
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id') ?? $daftarTahun->first()->id;

        $jenisTagihans = PengaturanTagihan::with('level')->where('tahun_pelajaran_id', $tahunPelajaranId)->get();

        // 2. Ambil daftar semua level untuk pilihan di Form Input Tarif
        $daftarLevel = Level::orderBy('tingkat_id', 'asc')->get();

        return view('pengaturan-tagihan.index', compact('daftarTahun', 'tahunPelajaranId', 'jenisTagihans', 'daftarLevel'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'tahun_pelajaran_id' => 'required',
            'level_ids' => 'nullable|array',
            'level_ids.*' => 'exists:levels,id',
            'kode_tagihan' => 'required|string|max:10',
            'nama_tagihan' => 'required|string|max:100',
            'tipe' => 'required|in:bulanan,semester,insidental',
            'sasaran' => 'nullable|in:murid,wali_murid',
            'nominal' => 'required|numeric|min:0',
        ]);

        // Ambil data dasar tagihan
        $dataDasar = $request->only(['tahun_pelajaran_id', 'kode_tagihan', 'nama_tagihan', 'tipe', 'sasaran', 'nominal']);
        $dataDasar['sasaran'] = $request->sasaran ?? 'murid';

        // JIKA SASARAN ADALAH PER WALI MURID (KK): Selalu tanpa level_id (berlaku tingkat keluarga)
        if ($dataDasar['sasaran'] === 'wali_murid') {
            PengaturanTagihan::create(array_merge($dataDasar, ['level_id' => null]));
        }
        // JIKA TIDAK ADA KELAS YANG DICENTANG: Berlaku untuk semua kelas (level_id = null)
        elseif (!$request->has('level_ids') || empty($request->level_ids)) {
            PengaturanTagihan::create(array_merge($dataDasar, ['level_id' => null]));
        }
        // JIKA ADA KELAS YANG DICENTANG: Lakukan perulangan otomatis
        else {
            // Tarik data level sekaligus untuk efisiensi query database
            $levels = Level::whereIn('id', $request->level_ids)->get()->keyBy('id');

            foreach ($request->level_ids as $levelId) {
                // Tarik nama level (Sesuaikan 'nama_level' dengan nama kolom di tabel levels Anda)
                $namaLevel = $levels[$levelId]->nama_level ?? '';

                // Gabungkan nama tagihan dengan nama level
                $namaTagihanKhusus = $dataDasar['nama_tagihan'] . ' - ' . $namaLevel;

                PengaturanTagihan::create(array_merge($dataDasar, [
                    'level_id'     => $levelId,
                    'nama_tagihan' => $namaTagihanKhusus // Menimpa nama tagihan dasar
                ]));
            }
        }

        return redirect()->back()->with('success', 'Kriteria biaya berhasil diterapkan!');
    }

    public function edit(Request $request, $id)
    {
        $biaya = PengaturanTagihan::findOrFail($id);

        // Ambil data level untuk opsi pilihan di select form
        $daftarLevel = Level::all(); // Sesuaikan jika Anda menggunakan scope tertentu

        if ($request->ajax()) {
            return view('pengaturan-tagihan.form-tagihan', compact('biaya', 'daftarLevel'));
        }

        // Fallback jika diakses langsung tanpa AJAX
        return redirect()->route('pengaturan-tagihan.index')->with('error', 'Gunakan tombol edit pada tabel.');
    }



    public function update(PengaturanTagihanRequest $request, $id)
    {
        $dataUpdate = $request->only(['level_id', 'kode_tagihan', 'nama_tagihan', 'tipe', 'sasaran', 'nominal']);
        $dataUpdate['sasaran'] = $request->sasaran ?? 'murid';

        if ($request->filled('level_id')) {
            $level = Level::find($request->level_id);

            if ($level) {
                $namaLevel = $level->nama_level ?? '';
                $namaDasar = trim(str_replace(' - ' . $namaLevel, '', $dataUpdate['nama_tagihan']));
                $dataUpdate['nama_tagihan'] = $namaDasar . ' - ' . $namaLevel;
            }
        }

        $biaya = PengaturanTagihan::findOrFail($id);
        $biaya->update($dataUpdate);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kriteria Tarif Tagihan berhasil diperbarui!'
            ]);
        }

        return redirect()->back()->with('success', 'Kriteria tarif berhasil diperbarui!');
    }

    public function destroy(Request $request, $id)
    {
        $biaya = PengaturanTagihan::findOrFail($id);
        $biaya->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kriteria tarif berhasil dihapus dari sistem!'
            ]);
        }

        return redirect()->back()->with('success', 'Kriteria tarif berhasil dihapus dari sistem!');
    }
}
