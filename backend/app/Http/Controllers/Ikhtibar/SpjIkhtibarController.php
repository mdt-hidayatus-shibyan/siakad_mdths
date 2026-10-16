<?php

namespace App\Http\Controllers\Ikhtibar;

use App\Http\Controllers\Controller;
use App\Models\Ikhtibar\DivisiIkhtibar;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\Ikhtibar\PanitiaIkhtibar;
use App\Models\Ikhtibar\PengeluaranIkhtibar;
use App\Models\Ikhtibar\PenyaluranDanaIkhtibar;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpjIkhtibarController extends Controller
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

        $query = PengeluaranIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['divisi', 'diinputOleh.ustadz', 'diverifikasiOleh.ustadz']);

        if ($request->filled('divisi_id')) {
            $query->where('divisi_ikhtibar_id', $request->divisi_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_pengeluaran', 'like', "%{$search}%")
                  ->orWhere('kode_spj', 'like', "%{$search}%")
                  ->orWhere('nama_toko_penerima', 'like', "%{$search}%");
            });
        }

        $spjList = $query->orderBy('tanggal_nota', 'desc')->orderBy('id', 'desc')->get();

        $totalNominalSpj = $spjList->sum('nominal');

        if ($request->ajax()) {
            return view('ikhtibar.spj.list', compact('spjList', 'kegiatan'));
        }

        return view('ikhtibar.spj.index', compact(
            'kegiatan',
            'divisis',
            'spjList',
            'totalNominalSpj',
            'tahunPelajarans',
            'tahunAktif',
            'selectedTahunId'
        ));
    }

    /**
     * Modal Form Tambah SPJ (AJAX)
     */
    public function create(Request $request, $kegiatanId)
    {
        if ($request->ajax()) {
            $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);
            $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)->where('is_active', true)->orderBy('urutan')->get();
            $panitias = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)->where('is_active', true)->with('ustadz')->get();
            $kodeOtomatis = PengeluaranIkhtibar::generateKodeSpj('BPH');

            return view('ikhtibar.spj.form', compact('kegiatan', 'divisis', 'panitias', 'kodeOtomatis'));
        }
        return redirect()->route('spj-ikhtibar.index');
    }

    /**
     * Simpan SPJ Baru
     */
    public function store(Request $request, $kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);

        $validated = $request->validate([
            'divisi_ikhtibar_id'  => 'nullable|exists:divisi_ikhtibars,id',
            'kode_spj'            => 'required|string|unique:pengeluaran_ikhtibars,kode_spj',
            'judul_pengeluaran'   => 'required|string|max:255',
            'nominal'             => 'required|numeric|min:1',
            'tanggal_nota'        => 'required|date',
            'nama_toko_penerima'  => 'nullable|string|max:255',
            'metode_pembayaran'   => 'required|in:Tunai,Transfer',
            'diinput_oleh_id'     => 'nullable|exists:panitia_ikhtibars,id',
            'rincian_item'        => 'nullable|string',
            'bukti_nota'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $validated['kegiatan_ikhtibar_id'] = $kegiatan->id;
        $validated['status_verifikasi']    = 'Disetujui';

        if ($request->hasFile('bukti_nota')) {
            $validated['bukti_nota'] = $request->file('bukti_nota')->store('ikhtibar/spj_nota', 'public');
        }

        PengeluaranIkhtibar::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Laporan Belanja (SPJ) berhasil dicatat!'
        ], 200);
    }

    /**
     * Modal Form Edit SPJ (AJAX)
     */
    public function edit($id, Request $request)
    {
        if ($request->ajax()) {
            $spj = PengeluaranIkhtibar::findOrFail($id);
            $kegiatan = $spj->kegiatanIkhtibar;
            $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)->where('is_active', true)->orderBy('urutan')->get();
            $panitias = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)->where('is_active', true)->with('ustadz')->get();

            return view('ikhtibar.spj.form', compact('spj', 'kegiatan', 'divisis', 'panitias'));
        }
        return redirect()->route('spj-ikhtibar.index');
    }

    /**
     * Update Data SPJ
     */
    public function update(Request $request, $id)
    {
        $spj = PengeluaranIkhtibar::findOrFail($id);

        $validated = $request->validate([
            'divisi_ikhtibar_id'  => 'nullable|exists:divisi_ikhtibars,id',
            'judul_pengeluaran'   => 'required|string|max:255',
            'nominal'             => 'required|numeric|min:1',
            'tanggal_nota'        => 'required|date',
            'nama_toko_penerima'  => 'nullable|string|max:255',
            'metode_pembayaran'   => 'required|in:Tunai,Transfer',
            'diinput_oleh_id'     => 'nullable|exists:panitia_ikhtibars,id',
            'rincian_item'        => 'nullable|string',
            'bukti_nota'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if ($request->hasFile('bukti_nota')) {
            if ($spj->bukti_nota && Storage::disk('public')->exists($spj->bukti_nota)) {
                Storage::disk('public')->delete($spj->bukti_nota);
            }
            $validated['bukti_nota'] = $request->file('bukti_nota')->store('ikhtibar/spj_nota', 'public');
        }

        $spj->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Laporan Belanja (SPJ) berhasil diperbarui!'
        ], 200);
    }

    /**
     * Verifikasi SPJ oleh BPH (AJAX)
     */
    public function verifikasi(Request $request, $id)
    {
        $spj = PengeluaranIkhtibar::findOrFail($id);

        $request->validate([
            'status_verifikasi'    => 'required|in:Disetujui,Ditolak,Pending',
            'diverifikasi_oleh_id' => 'nullable|exists:panitia_ikhtibars,id',
            'catatan_verifikasi'   => 'nullable|string',
        ]);

        $verifikatorId = $request->diverifikasi_oleh_id;
        if (!$verifikatorId) {
            $bph = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $spj->kegiatan_ikhtibar_id)
                ->where('kelompok_jabatan', 'BPH')
                ->where('is_active', true)
                ->first();
            $verifikatorId = $bph?->id;
        }

        $spj->update([
            'status_verifikasi'    => $request->status_verifikasi,
            'diverifikasi_oleh_id' => $verifikatorId,
            'catatan_verifikasi'   => $request->catatan_verifikasi,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status verifikasi SPJ ' . $spj->kode_spj . ' berhasil diubah menjadi ' . $request->status_verifikasi . '!'
        ], 200);
    }

    /**
     * Hapus SPJ (AJAX)
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            $spj = PengeluaranIkhtibar::findOrFail($id);
            if ($spj->bukti_nota && Storage::disk('public')->exists($spj->bukti_nota)) {
                Storage::disk('public')->delete($spj->bukti_nota);
            }
            $spj->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data SPJ berhasil dihapus!'
            ], 200);
        }
        return redirect()->route('spj-ikhtibar.index');
    }

    /**
     * Cetak Laporan Pertanggungjawaban (LPJ) Terpadu
     */
    public function cetakLpj($kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::with('tahunPelajaran')->findOrFail($kegiatanId);
        $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
            ->with(['penyaluranDanas', 'pengeluarans.diinputOleh.ustadz'])
            ->orderBy('urutan')
            ->get();

        $pengeluaranList = PengeluaranIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
            ->with(['divisi', 'diinputOleh.ustadz'])
            ->orderBy('tanggal_nota')
            ->get();

        // Pimpinan BPH untuk tanda tangan pengesahan
        $bph = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
            ->where('kelompok_jabatan', 'BPH')
            ->where('is_active', true)
            ->with('ustadz')
            ->get();

        $ketua = $bph->firstWhere('jabatan', 'Ketua');
        $sekretaris = $bph->firstWhere('jabatan', 'Sekretaris');
        $bendahara = $bph->firstWhere('jabatan', 'Bendahara');

        $pengasuh = class_exists(\App\Models\Kepengurusan\Pengurus::class) 
            ? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh') 
            : null;

        return view('ikhtibar.spj.cetak-lpj', compact(
            'kegiatan',
            'divisis',
            'pengeluaranList',
            'ketua',
            'sekretaris',
            'bendahara',
            'pengasuh'
        ));
    }
}

