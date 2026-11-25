<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Murid; // Sesuaikan namespace model Murid Anda
use App\Models\Arsip\ArsipDokumen;
use Illuminate\Http\Request;

class PetugasCetakController extends Controller
{
    public function index(Request $request)
    {
        $murid = null;
        $arsipDikelompokkan = [];

        if ($request->filled('nism')) {
            $nism = trim($request->nism);

            // 1. Cari data murid berdasarkan NISM
            $murid = Murid::where('nism', $nism)->first();

            if ($murid) {
                // 2. Tarik semua arsip milik murid ini yang telah disahkan dari database
                $arsips = ArsipDokumen::where(function ($q) use ($murid) {
                    $q->where(function ($sub) use ($murid) {
                        $sub->where('referensi_tipe', Murid::class)
                            ->where('referensi_id', $murid->id);
                    })->orWhere('snapshot_data->nism', $murid->nism);
                })
                    ->orderBy('created_at', 'desc')
                    ->get();

                // 3. Kelompokkan arsip berdasarkan Tahun Pelajaran -> Ruangan -> Tipe Dokumen
                foreach ($arsips as $arsip) {
                    $data = $arsip->snapshot_data;
                    $thn = $data['tahun_pelajaran'] ?? 'Tahun Tidak Diketahui';
                    $rng = $data['nama_ruangan'] ?? 'Ruangan Tidak Diketahui';
                    $tipe = $arsip->tipe_dokumen;

                    // PENGECUALIAN KHUSUS RAPOR: Pisahkan antara Semester 1 dan Semester 2
                    if ($tipe === 'rapor_murid') {
                        // Cek string semester (misal: "Semester 1 (Ganjil)")
                        $semester = strtolower($data['semester'] ?? $data['nama_semester'] ?? '');

                        if (str_contains($semester, '1') || str_contains($semester, 'ganjil')) {
                            $tipe = 'rapor_smt_1';
                        } elseif (str_contains($semester, '2') || str_contains($semester, 'genap')) {
                            $tipe = 'rapor_smt_2';
                        }
                    }

                    // PENGECUALIAN KHUSUS IJAZAH AL-QUR'AN
                    if ($tipe === 'ijazah' && (($data['tipe_ijazah'] ?? '') === 'ijazah_alquran' || isset($data['nomor_ijazah_alquran']))) {
                        $tipe = 'ijazah_alquran';
                    }

                    // PENGECUALIAN KHUSUS SK AL-QUR'AN
                    if (($tipe === 'sk_keputusan' || $tipe === 'sk_alquran') && (($data['tipe_sk'] ?? '') === 'sk_alquran' || isset($data['nomor_sk_alquran']))) {
                        $tipe = 'sk_alquran';
                    }

                    // Menyusun array multi-dimensi agar mudah di-looping di Blade
                    if (!isset($arsipDikelompokkan[$thn][$rng])) {
                        $arsipDikelompokkan[$thn][$rng] = [];
                    }

                    // Simpan objek arsip ke dalam slot yang sudah spesifik (ambil yang terbaru)
                    if (!isset($arsipDikelompokkan[$thn][$rng][$tipe])) {
                        $arsipDikelompokkan[$thn][$rng][$tipe] = $arsip;
                    }
                }
            }
        }

        return view('petugas-cetak.index', compact('murid', 'arsipDikelompokkan'));
    }

    /**
     * Cetak Halaman Depan Rapor (Biodata Murid & Tanda Tangan Pengasuh)
     */
    public function cetakBiodataRapor($id)
    {
        $murid = Murid::with([
            'waliMurid.kampung',
            'tahunMasuk',
            'levelMasuk',
            'ruanganMasuk.level',
            'ruangans.level',
        ])->findOrFail($id);

        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');

        return view('cetak-baru.cetak_biodata_rapor', compact('murid', 'pengasuh'));
    }
}
