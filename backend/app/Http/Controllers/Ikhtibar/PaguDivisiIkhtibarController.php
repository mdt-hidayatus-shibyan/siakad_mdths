<?php

namespace App\Http\Controllers\Ikhtibar;

use App\Http\Controllers\Controller;
use App\Models\Ikhtibar\DivisiIkhtibar;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\Ikhtibar\PanitiaIkhtibar;
use App\Models\Ikhtibar\PenyaluranDanaIkhtibar;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaguDivisiIkhtibarController extends Controller
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

        $divisis = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['koordinator.ustadz', 'penyaluranDanas', 'pengeluarans'])
            ->orderBy('urutan')
            ->get();

        $totalPagu = $divisis->sum('nominal_pagu');
        $totalSalur = $divisis->sum(fn($d) => $d->total_tersalurkan);
        $totalTarik = $divisis->sum(fn($d) => $d->total_ditarik_kembali);
        $totalSpj = $divisis->sum(fn($d) => $d->total_realisasi_spj);
        $totalSisaPagu = $divisis->sum(fn($d) => $d->sisa_pagu_belum_cair);
        $totalSisaKas = $divisis->sum(fn($d) => $d->sisa_kas_di_divisi);

        // Riwayat Transaksi Distribusi & Penarikan
        $riwayatTransaksi = PenyaluranDanaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->with(['divisi', 'penerima.ustadz', 'disalurkanOleh.ustadz', 'divisiTujuanRealokasi'])
            ->orderBy('tanggal_penyaluran', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        if ($request->ajax()) {
            return view('ikhtibar.pagu.list', compact('divisis', 'riwayatTransaksi', 'kegiatan'));
        }

        return view('ikhtibar.pagu.index', compact(
            'kegiatan',
            'divisis',
            'riwayatTransaksi',
            'totalPagu',
            'totalSalur',
            'totalTarik',
            'totalSpj',
            'totalSisaPagu',
            'totalSisaKas',
            'tahunPelajarans',
            'tahunAktif',
            'selectedTahunId'
        ));
    }

    /**
     * Modal Form Update Pagu Anggaran (AJAX)
     */
    public function modalUpdatePagu($divisiId, Request $request)
    {
        if ($request->ajax()) {
            $divisi = DivisiIkhtibar::findOrFail($divisiId);
            return view('ikhtibar.pagu.modal-edit-pagu', compact('divisi'));
        }
        return redirect()->route('pagu-divisi-ikhtibar.index');
    }

    /**
     * Simpan Update Pagu Anggaran
     */
    public function simpanUpdatePagu(Request $request, $divisiId)
    {
        $divisi = DivisiIkhtibar::findOrFail($divisiId);

        $request->validate([
            'nominal_pagu' => 'required|numeric|min:0',
            'status_pagu'  => 'required|in:Draft,Disetujui,Dikunci',
        ]);

        $divisi->update([
            'nominal_pagu'   => $request->nominal_pagu,
            'status_pagu'    => $request->status_pagu,
            'disetujui_oleh' => auth()->id(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pagu Anggaran ' . $divisi->nama_divisi . ' berhasil diperbarui!'
        ], 200);
    }

    /**
     * Modal Form Penyaluran Dana Pagu ke Divisi (AJAX)
     */
    public function modalSalurDana($divisiId, Request $request)
    {
        if ($request->ajax()) {
            $divisi = DivisiIkhtibar::with(['koordinator.ustadz'])->findOrFail($divisiId);
            $kegiatan = $divisi->kegiatanIkhtibar;
            
            // Panitia BPH (Bendahara/Wakil Bendahara)
            $bendaharas = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('kelompok_jabatan', 'BPH')
                ->whereIn('jabatan', ['Bendahara', 'Wakil Bendahara', 'Ketua', 'Wakil Ketua'])
                ->where('is_active', true)
                ->with('ustadz')
                ->get();

            // Penerima Dana:
            // 1. Personel yang tercatat di divisi ini
            $penerimaDivisi = PanitiaIkhtibar::where('divisi_ikhtibar_id', $divisi->id)
                ->where('is_active', true)
                ->with('ustadz')
                ->get();

            // 2. Jajaran BPH (Ketua, Wakil Ketua, Sekretaris, Wakil Sekretaris, Bendahara, Wakil Bendahara)
            // agar Ketua, Sekretaris, dan Bendahara selalu dapat menerima alokasi pagu anggaran
            $penerimaBph = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('kelompok_jabatan', 'BPH')
                ->where('is_active', true)
                ->with('ustadz')
                ->get();

            $penerimas = $penerimaDivisi->merge($penerimaBph)->unique('id');
            $defaultPenerimaId = $divisi->koordinator_tampil?->id ?? $penerimas->first()?->id;

            $kodeOtomatis = PenyaluranDanaIkhtibar::generateKodePenyaluran();
            $terminKe = PenyaluranDanaIkhtibar::where('divisi_ikhtibar_id', $divisi->id)
                ->where('jenis_transaksi', 'Penyaluran Pagu')
                ->count() + 1;

            return view('ikhtibar.pagu.modal-salur-dana', compact(
                'divisi',
                'kegiatan',
                'bendaharas',
                'penerimas',
                'defaultPenerimaId',
                'kodeOtomatis',
                'terminKe'
            ));
        }
        return redirect()->route('pagu-divisi-ikhtibar.index');
    }

    /**
     * Simpan Transaksi Penyaluran Dana Pagu
     */
    public function simpanSalurDana(Request $request, $divisiId)
    {
        $divisi = DivisiIkhtibar::findOrFail($divisiId);

        $validated = $request->validate([
            'kode_penyaluran'     => 'required|string|unique:penyaluran_dana_ikhtibars,kode_penyaluran',
            'nominal'             => 'required|numeric|min:1',
            'tanggal_penyaluran'  => 'required|date',
            'metode_penyaluran'   => 'required|in:Tunai,Transfer',
            'penerima_panitia_id' => 'required|exists:panitia_ikhtibars,id',
            'disalurkan_oleh_id'  => 'required|exists:panitia_ikhtibars,id',
            'disetujui_oleh_id'   => 'nullable|exists:panitia_ikhtibars,id',
            'catatan'             => 'nullable|string',
            'bukti_serah_terima'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['kegiatan_ikhtibar_id'] = $divisi->kegiatan_ikhtibar_id;
        $validated['divisi_ikhtibar_id']   = $divisi->id;
        $validated['jenis_transaksi']      = 'Penyaluran Pagu';
        $validated['termin_ke']            = PenyaluranDanaIkhtibar::where('divisi_ikhtibar_id', $divisi->id)->where('jenis_transaksi', 'Penyaluran Pagu')->count() + 1;

        if ($request->hasFile('bukti_serah_terima')) {
            $validated['bukti_serah_terima'] = $request->file('bukti_serah_terima')->store('ikhtibar/serah_terima', 'public');
        }

        PenyaluranDanaIkhtibar::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Dana Pagu sebesar Rp ' . number_format($request->nominal, 0, ',', '.') . ' berhasil disalurkan ke ' . $divisi->nama_divisi . '!'
        ], 200);
    }

    /**
     * Modal Form Penarikan Sisa Pagu / Sisa Kas Divisi (AJAX)
     */
    public function modalTarikSisa($divisiId, Request $request)
    {
        if ($request->ajax()) {
            $divisi = DivisiIkhtibar::with(['koordinator.ustadz'])->findOrFail($divisiId);
            $kegiatan = $divisi->kegiatanIkhtibar;

            $sisaKasFisik = $divisi->sisa_kas_di_divisi;
            $sisaPaguBelumCair = $divisi->sisa_pagu_belum_cair;

            // Panitia BPH Penerima Pengembalian Kas
            $bendaharas = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('kelompok_jabatan', 'BPH')
                ->whereIn('jabatan', ['Bendahara', 'Wakil Bendahara', 'Ketua', 'Wakil Ketua'])
                ->where('is_active', true)
                ->with('ustadz')
                ->get();

            // Personel yang Menyerahkan Pengembalian Kas:
            // 1. Personel divisi
            $penyerahDivisi = PanitiaIkhtibar::where('divisi_ikhtibar_id', $divisi->id)
                ->where('is_active', true)
                ->with('ustadz')
                ->get();

            // 2. Jajaran BPH (Ketua, Sekretaris, Bendahara)
            $penyerahBph = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('kelompok_jabatan', 'BPH')
                ->where('is_active', true)
                ->with('ustadz')
                ->get();

            $penyerahs = $penyerahDivisi->merge($penyerahBph)->unique('id');
            $defaultPenyerahId = $divisi->koordinator_tampil?->id ?? $penyerahs->first()?->id;

            // Divisi lain untuk opsi realokasi
            $divisiLain = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where('id', '!=', $divisi->id)
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get();

            $kodeOtomatis = PenyaluranDanaIkhtibar::generateKodePenarikan();

            return view('ikhtibar.pagu.modal-tarik-sisa', compact(
                'divisi',
                'kegiatan',
                'sisaKasFisik',
                'sisaPaguBelumCair',
                'bendaharas',
                'penyerahs',
                'defaultPenyerahId',
                'divisiLain',
                'kodeOtomatis'
            ));
        }
        return redirect()->route('pagu-divisi-ikhtibar.index');
    }

    /**
     * Simpan Transaksi Penarikan Sisa Pagu / Sisa Kas Divisi
     */
    public function simpanTarikSisa(Request $request, $divisiId)
    {
        $divisi = DivisiIkhtibar::findOrFail($divisiId);
        $kegiatan = $divisi->kegiatanIkhtibar;

        $request->validate([
            'kategori_penarikan'         => 'required|in:Sisa Kas Fisik,Tarik Sisa Alokasi,Realokasi Antar Divisi',
            'nominal'                    => 'required|numeric|min:1',
            'tanggal_penyaluran'         => 'required|date',
            'metode_penyaluran'          => 'required|in:Tunai,Transfer',
            'penerima_panitia_id'        => 'required|exists:panitia_ikhtibars,id', // Bendahara/BPH penerima
            'disalurkan_oleh_id'         => 'required|exists:panitia_ikhtibars,id', // Penyerah di divisi
            'divisi_tujuan_realokasi_id' => 'nullable|required_if:kategori_penarikan,Realokasi Antar Divisi|exists:divisi_ikhtibars,id',
            'catatan'                    => 'nullable|string',
            'bukti_serah_terima'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $nominal = (float) $request->nominal;

        DB::transaction(function () use ($request, $divisi, $kegiatan, $nominal) {
            $buktiPath = null;
            if ($request->hasFile('bukti_serah_terima')) {
                $buktiPath = $request->file('bukti_serah_terima')->store('ikhtibar/pengembalian_sisa', 'public');
            }

            // Catat mutasi penarikan
            PenyaluranDanaIkhtibar::create([
                'kegiatan_ikhtibar_id'       => $kegiatan->id,
                'divisi_ikhtibar_id'         => $divisi->id,
                'kode_penyaluran'            => PenyaluranDanaIkhtibar::generateKodePenarikan(),
                'jenis_transaksi'            => $request->kategori_penarikan === 'Sisa Kas Fisik' ? 'Penarikan Sisa Kas' : 'Penyesuaian Pagu',
                'kategori_penarikan'         => $request->kategori_penarikan,
                'divisi_tujuan_realokasi_id' => $request->divisi_tujuan_realokasi_id,
                'nominal'                    => $nominal,
                'tanggal_penyaluran'         => $request->tanggal_penyaluran,
                'metode_penyaluran'          => $request->metode_penyaluran,
                'penerima_panitia_id'        => $request->penerima_panitia_id,
                'disalurkan_oleh_id'         => $request->disalurkan_oleh_id,
                'bukti_serah_terima'         => $buktiPath,
                'catatan'                    => $request->catatan,
            ]);

            // Jika Kategori Tarik Sisa Alokasi: kurangi nominal pagu divisi
            if ($request->kategori_penarikan === 'Tarik Sisa Alokasi') {
                $divisi->nominal_pagu = max(0, (float) $divisi->nominal_pagu - $nominal);
                $divisi->status_pagu = 'Dikunci';
                $divisi->save();
            }

            // Jika Kategori Realokasi Antar Divisi: kurangi dari divisi asal dan tambahkan ke divisi tujuan
            if ($request->kategori_penarikan === 'Realokasi Antar Divisi' && $request->divisi_tujuan_realokasi_id) {
                $divisiTujuan = DivisiIkhtibar::findOrFail($request->divisi_tujuan_realokasi_id);
                $divisi->nominal_pagu = max(0, (float) $divisi->nominal_pagu - $nominal);
                $divisi->save();

                $divisiTujuan->nominal_pagu = (float) $divisiTujuan->nominal_pagu + $nominal;
                $divisiTujuan->save();
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Penarikan sisa pagu / sisa kas dari ' . $divisi->nama_divisi . ' sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' berhasil dibukukan!'
        ], 200);
    }

    /**
     * Hapus Transaksi Penyaluran / Penarikan (AJAX)
     */
    public function destroyTransaksi($id)
    {
        if (request()->ajax()) {
            $transaksi = PenyaluranDanaIkhtibar::findOrFail($id);
            $transaksi->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data transaksi penyaluran/penarikan dana berhasil dihapus!'
            ], 200);
        }
        return redirect()->route('pagu-divisi-ikhtibar.index');
    }

    /**
     * Cetak Bukti Penyaluran Dana Pagu
     */
    public function cetakBuktiPenyaluran($id)
    {
        $transaksi = PenyaluranDanaIkhtibar::with(['divisi', 'penerima.ustadz', 'disalurkanOleh.ustadz', 'kegiatanIkhtibar.tahunPelajaran'])->findOrFail($id);
        $kegiatan = $transaksi->kegiatanIkhtibar;
        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('kelompok_jabatan', 'BPH')
            ->where('jabatan', 'Ketua')
            ->where('is_active', true)
            ->with('ustadz')
            ->first();

        return view('ikhtibar.pagu.cetak-bukti-salur', compact('transaksi', 'kegiatan', 'ketua'));
    }

    /**
     * Cetak Berita Acara & Kwitansi Penarikan Sisa Pagu
     */
    public function cetakBuktiPenarikan($id)
    {
        $transaksi = PenyaluranDanaIkhtibar::with(['divisi', 'penerima.ustadz', 'disalurkanOleh.ustadz', 'divisiTujuanRealokasi', 'kegiatanIkhtibar.tahunPelajaran'])->findOrFail($id);
        $kegiatan = $transaksi->kegiatanIkhtibar;
        $ketua = PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
            ->where('kelompok_jabatan', 'BPH')
            ->where('jabatan', 'Ketua')
            ->where('is_active', true)
            ->with('ustadz')
            ->first();

        return view('ikhtibar.pagu.cetak-bukti-tarik', compact('transaksi', 'kegiatan', 'ketua'));
    }
}

