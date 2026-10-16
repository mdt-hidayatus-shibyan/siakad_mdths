<?php

namespace App\Http\Controllers\Ikhtibar;

use App\Http\Controllers\Controller;
use App\Models\Ikhtibar\DivisiIkhtibar;
use App\Models\Ikhtibar\KegiatanIkhtibar;
use App\Models\PengaturanTagihan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterIkhtibarController extends Controller
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
            ->withCount('panitias')
            ->orderBy('urutan')
            ->get();

        // Opsi Pengaturan Tagihan khusus sasaran wali_murid (per KK)
        $pengaturanTagihans = PengaturanTagihan::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('sasaran', 'wali_murid')
            ->get();

        if ($request->ajax()) {
            return view('ikhtibar.master.list-divisi', compact('divisis'));
        }

        return view('ikhtibar.master.index', compact(
            'kegiatan',
            'divisis',
            'pengaturanTagihans',
            'tahunPelajarans',
            'tahunAktif',
            'selectedTahunId'
        ));
    }

    /**
     * Update Identitas Kegiatan Ikhtibar
     */
    public function updateIdentitas(Request $request, $id)
    {
        $kegiatan = KegiatanIkhtibar::findOrFail($id);

        $validated = $request->validate([
            'nama_kegiatan'         => 'required|string|max:255',
            'ikhtibar_ke'           => 'nullable|string|max:50',
            'tema_kegiatan'         => 'nullable|string|max:255',
            'tanggal_mulai'         => 'nullable|date',
            'tanggal_selesai'       => 'nullable|date|after_or_equal:tanggal_mulai',
            'tanggal_puncak_acara'  => 'nullable|date',
            'tanggal_hijriyah'      => 'nullable|string|max:100',
            'lokasi_kegiatan'       => 'nullable|string|max:255',
            'no_sk_panitia'         => 'nullable|string|max:100',
            'tanggal_penetapan_sk'  => 'nullable|date',
            'status_kegiatan'       => 'required|in:Persiapan,Berjalan,Selesai,Dikunci',
            'pengaturan_tagihan_id' => 'nullable|exists:pengaturan_tagihans,id',
            'keterangan'            => 'nullable|string',
            'logo_kegiatan'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'kop_logo_ikhtibar'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stempel_kegiatan'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo_kegiatan')) {
            if ($kegiatan->logo_kegiatan && Storage::disk('public')->exists($kegiatan->logo_kegiatan)) {
                Storage::disk('public')->delete($kegiatan->logo_kegiatan);
            }
            $validated['logo_kegiatan'] = $request->file('logo_kegiatan')->store('ikhtibar/logo', 'public');
        }

        if ($request->hasFile('kop_logo_ikhtibar')) {
            if ($kegiatan->kop_logo_ikhtibar && Storage::disk('public')->exists($kegiatan->kop_logo_ikhtibar)) {
                Storage::disk('public')->delete($kegiatan->kop_logo_ikhtibar);
            }
            $validated['kop_logo_ikhtibar'] = $request->file('kop_logo_ikhtibar')->store('ikhtibar/kop', 'public');
        }

        if ($request->hasFile('stempel_kegiatan')) {
            if ($kegiatan->stempel_kegiatan && Storage::disk('public')->exists($kegiatan->stempel_kegiatan)) {
                Storage::disk('public')->delete($kegiatan->stempel_kegiatan);
            }
            $validated['stempel_kegiatan'] = $request->file('stempel_kegiatan')->store('ikhtibar/stempel', 'public');
        }

        $kegiatan->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Identitas Kegiatan Ikhtibar berhasil disimpan!',
                'redirect' => route('master-ikhtibar.index', ['tahun_pelajaran_id' => $kegiatan->tahun_pelajaran_id]),
            ], 200);
        }

        return redirect()->route('master-ikhtibar.index', ['tahun_pelajaran_id' => $kegiatan->tahun_pelajaran_id])
            ->with('success', 'Identitas Kegiatan Ikhtibar berhasil disimpan!');
    }

    /**
     * Modal Form Tambah Divisi (AJAX)
     */
    public function createDivisi(Request $request, $kegiatanId)
    {
        if ($request->ajax()) {
            $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);
            $urutanBaru = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)->max('urutan') + 1;
            return view('ikhtibar.master.form-divisi', compact('kegiatan', 'urutanBaru'));
        }
        $kegiatan = KegiatanIkhtibar::find($kegiatanId);
        return redirect()->route('master-ikhtibar.index', array_filter(['tahun_pelajaran_id' => $kegiatan?->tahun_pelajaran_id]));
    }

    /**
     * Simpan Divisi Baru
     */
    public function storeDivisi(Request $request, $kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);

        $validated = $request->validate([
            'kode_divisi'     => 'required|string|max:20',
            'nama_divisi'     => 'required|string|max:100',
            'nominal_pagu'    => 'required|numeric|min:0',
            'deskripsi_tugas' => 'nullable|string',
            'urutan'          => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
        ]);

        $validated['kegiatan_ikhtibar_id'] = $kegiatan->id;
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;
        $validated['status_pagu'] = 'Draft';

        DivisiIkhtibar::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Divisi Ikhtibar berhasil ditambahkan!',
                'redirect' => route('master-ikhtibar.index', ['tahun_pelajaran_id' => $kegiatan->tahun_pelajaran_id]),
            ], 200);
        }

        return redirect()->route('master-ikhtibar.index', ['tahun_pelajaran_id' => $kegiatan->tahun_pelajaran_id])
            ->with('success', 'Divisi Ikhtibar berhasil ditambahkan!');
    }

    /**
     * Modal Form Edit Divisi (AJAX)
     */
    public function editDivisi($id, Request $request)
    {
        if ($request->ajax()) {
            $divisi = DivisiIkhtibar::findOrFail($id);
            $kegiatan = $divisi->kegiatanIkhtibar;
            return view('ikhtibar.master.form-divisi', compact('divisi', 'kegiatan'));
        }
        $divisi = DivisiIkhtibar::find($id);
        return redirect()->route('master-ikhtibar.index', array_filter(['tahun_pelajaran_id' => $divisi?->kegiatanIkhtibar?->tahun_pelajaran_id]));
    }

    /**
     * Update Data Divisi
     */
    public function updateDivisi(Request $request, $id)
    {
        $divisi = DivisiIkhtibar::findOrFail($id);

        $validated = $request->validate([
            'kode_divisi'     => 'required|string|max:20',
            'nama_divisi'     => 'required|string|max:100',
            'nominal_pagu'    => 'required|numeric|min:0',
            'deskripsi_tugas' => 'nullable|string',
            'urutan'          => 'required|integer|min:1',
            'status_pagu'     => 'required|in:Draft,Disetujui,Dikunci',
            'is_active'       => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        $divisi->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Divisi Ikhtibar berhasil diperbarui!',
                'redirect' => route('master-ikhtibar.index', ['tahun_pelajaran_id' => $divisi->kegiatanIkhtibar?->tahun_pelajaran_id]),
            ], 200);
        }

        return redirect()->route('master-ikhtibar.index', array_filter(['tahun_pelajaran_id' => $divisi->kegiatanIkhtibar?->tahun_pelajaran_id]))
            ->with('success', 'Divisi Ikhtibar berhasil diperbarui!');
    }

    /**
     * Hapus Divisi (AJAX)
     */
    public function destroyDivisi($id)
    {
        $divisi = DivisiIkhtibar::findOrFail($id);
        $tahunId = $divisi->kegiatanIkhtibar?->tahun_pelajaran_id;
        $divisi->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Divisi berhasil dihapus!',
                'redirect' => route('master-ikhtibar.index', array_filter(['tahun_pelajaran_id' => $tahunId])),
            ], 200);
        }

        return redirect()->route('master-ikhtibar.index', array_filter(['tahun_pelajaran_id' => $tahunId]))
            ->with('success', 'Divisi berhasil dihapus!');
    }

    /**
     * Generate Divisi Standar Otomatis
     */
    public function generateDivisiStandar(Request $request, $kegiatanId)
    {
        $kegiatan = KegiatanIkhtibar::findOrFail($kegiatanId);

        $standar = [
            ['kode' => 'KETUA', 'nama' => 'Ketua', 'nama_inti' => 'pimpinan', 'pagu' => 2000000, 'urutan' => 1, 'desc' => 'Operasional pimpinan, kebijakan taktis, rapat pleno, & tali asih tamu kehormatan'],
            ['kode' => 'SEKRETARIS', 'nama' => 'Sekretaris', 'nama_inti' => 'kesekretariatan', 'pagu' => 1500000, 'urutan' => 2, 'desc' => 'Pengadaan ATK, cetak SK, map, formulir pendaftaran, surat undangan, & ID card'],
            ['kode' => 'BENDAHARA', 'nama' => 'Bendahara', 'nama_inti' => 'kebendaharaan', 'pagu' => 1500000, 'urutan' => 3, 'desc' => 'Operasional kasir penagihan, buku kas, cetak kwitansi, kartu tagihan KK, materai, & perbankan'],
            ['kode' => 'ACARA', 'nama' => 'Divisi Acara', 'nama_inti' => 'acara', 'pagu' => 4000000, 'urutan' => 4, 'desc' => 'Susunan mata acara panggung, protokoler, MC, tata tertib, dan seremonial Haflah'],
            ['kode' => 'LOMBA', 'nama' => 'Divisi Lomba', 'nama_inti' => 'lomba', 'pagu' => 3500000, 'urutan' => 5, 'desc' => 'Koordinator teknis cabang perlombaan murid pra-Ikhtibar dan penyiapan hadiah/trofi'],
            ['kode' => 'JURI', 'nama' => 'Divisi Juri', 'nama_inti' => 'juri', 'pagu' => 2000000, 'urutan' => 6, 'desc' => 'Pengelolaan dewan juri, format penilaian, tabulasi skor, dan penetapan juara lomba'],
            ['kode' => 'GERJAL', 'nama' => 'Divisi Gerak Jalan', 'nama_inti' => 'gerak jalan', 'pagu' => 2500000, 'urutan' => 7, 'desc' => 'Rute jalan santai/gerak jalan santri, nomor dada, pos penilaian, dan pengawalan rute'],
            ['kode' => 'KARNAVAL', 'nama' => 'Divisi Karnaval', 'nama_inti' => 'karnaval', 'pagu' => 3500000, 'urutan' => 8, 'desc' => 'Pawai ta\'aruf santri, rute karnaval, kostum, mobil hias, dan pengawalan rombongan'],
            ['kode' => 'DOKMULTIMEDIA', 'nama' => 'Divisi Dokumentasi & Multimedia', 'nama_inti' => 'dokumentasi', 'pagu' => 2000000, 'urutan' => 9, 'desc' => 'Foto & video shooting, live streaming, proyektor panggung, dan konten publikasi media sosial'],
            ['kode' => 'KEAMANAN', 'nama' => 'Divisi Keamanan', 'nama_inti' => 'keamanan', 'pagu' => 1500000, 'urutan' => 10, 'desc' => 'Ketertiban lokasi acara, perizinan aparat, pengamanan panggung, dan penataan parkir kendaraan'],
            ['kode' => 'PERLENGKAPAN', 'nama' => 'Divisi Perlengkapan', 'nama_inti' => 'perlengkapan', 'pagu' => 4000000, 'urutan' => 11, 'desc' => 'Panggung utama, tenda/tratak, meja-kursi undangan, kelistrikan, dan inventaris madrasah'],
            ['kode' => 'DEKORASI', 'nama' => 'Divisi Dekorasi', 'nama_inti' => 'dekorasi', 'pagu' => 2500000, 'urutan' => 12, 'desc' => 'Penataan panggung utama, backdrop, photobooth, dekorasi gebyar taman, dan bunga panggung'],
            ['kode' => 'KONSUMSI', 'nama' => 'Divisi Konsumsi', 'nama_inti' => 'konsumsi', 'pagu' => 4500000, 'urutan' => 13, 'desc' => 'Penyediaan konsumsi dewan asatidz, juri, peserta lomba, tamu undangan, dan panitia'],
            ['kode' => 'SOUND', 'nama' => 'Divisi Sound System', 'nama_inti' => 'sound', 'pagu' => 3000000, 'urutan' => 14, 'desc' => 'Tata suara panggung utama, microphone, sound lapangan, genset kelistrikan, dan operator audio'],
            ['kode' => 'HUMAS', 'nama' => 'Divisi Humas', 'nama_inti' => 'humas', 'pagu' => 1000000, 'urutan' => 16, 'desc' => 'Penyebaran surat undangan wali murid/tokoh, kemitraan sponsor, dan sosialisasi masyarakat'],
        ];

        foreach ($standar as $item) {
            $existing = DivisiIkhtibar::where('kegiatan_ikhtibar_id', $kegiatan->id)
                ->where(function ($q) use ($item) {
                    $q->where('kode_divisi', $item['kode'])
                        ->orWhere('nama_divisi', $item['nama'])
                        ->orWhere('nama_divisi', 'LIKE', '%' . $item['nama_inti'] . '%');
                })
                ->first();

            if ($existing) {
                $existing->update([
                    'urutan'          => $item['urutan'],
                    'deskripsi_tugas' => $existing->deskripsi_tugas ?: $item['desc'],
                ]);
            } else {
                DivisiIkhtibar::create([
                    'kegiatan_ikhtibar_id' => $kegiatan->id,
                    'kode_divisi'          => $item['kode'],
                    'nama_divisi'          => $item['nama'],
                    'nominal_pagu'         => $item['pagu'],
                    'deskripsi_tugas'      => $item['desc'],
                    'status_pagu'          => 'Draft',
                    'urutan'               => $item['urutan'],
                    'is_active'            => true,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Struktur Standar BPH (Ketua, Sekretaris, Bendahara) & 13 Divisi Operasional berhasil dibuat otomatis!',
                'redirect' => route('master-ikhtibar.index', ['tahun_pelajaran_id' => $kegiatan->tahun_pelajaran_id]),
            ], 200);
        }

        return redirect()->route('master-ikhtibar.index', ['tahun_pelajaran_id' => $kegiatan->tahun_pelajaran_id])
            ->with('success', 'Struktur Standar BPH & 13 Divisi Operasional berhasil dibuat otomatis!');
    }

    /**
     * Toggle Status Divisi (AJAX)
     */
    public function toggleStatusDivisi(Request $request, $id)
    {
        $divisi = DivisiIkhtibar::findOrFail($id);
        $divisi->is_active = $request->has('is_active') ? (bool) $request->input('is_active') : !$divisi->is_active;
        $divisi->save();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Status divisi berhasil diubah!',
            'is_active' => $divisi->is_active
        ], 200);
    }
}
