<?php

namespace App\Http\Controllers\Persuratan;

use App\Http\Controllers\Controller;
use App\Models\Persuratan\SuratKeluar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VerifikasiSuratController extends Controller
{
    /**
     * Halaman Utama Portal Verifikasi & Pengesahan Surat Resmi MDTHS
     */
    public function index(Request $request)
    {
        $keyword = trim($request->query('q') ?? $request->query('nomor_surat') ?? '');

        if (!empty($keyword)) {
            return $this->cariSurat($keyword);
        }

        return view('persuratan.surat-keluar.verifikasi-index');
    }

    /**
     * Proses pencarian nomor surat atau kode token QR
     */
    public function cari(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:150',
        ], [
            'keyword.required' => 'Silakan masukkan nomor surat resmi atau kode verifikasi QR.',
        ]);

        $keyword = trim($request->input('keyword'));
        return $this->cariSurat($keyword);
    }

    /**
     * Tampilkan Halaman Pengesahan / Verifikasi Surat Resmi
     */
    public function show(Request $request, $token)
    {
        $token = trim(urldecode($token));
        return $this->cariSurat($token);
    }

    /**
     * Helper internal untuk mencari dokumen surat keluar
     */
    private function cariSurat(string $identifier)
    {
        // 1. Cari berdasarkan QR Token, Nomor Surat, atau ID
        $surat = SuratKeluar::with([
            'tahunPelajaran',
            'creator',
            'waliMurid.kampung',
        ])
            ->where('qr_token', $identifier)
            ->orWhere('nomor_surat', $identifier)
            ->orWhere('nomor_surat', str_replace('-', '/', $identifier))
            ->orWhere('id', is_numeric($identifier) ? (int)$identifier : 0)
            ->first();

        // 2. Jika tidak ditemukan, tampilkan halaman Dokumen Tidak Sah / Belum Terdaftar
        if (!$surat) {
            return response()->view('persuratan.surat-keluar.verifikasi-not-found', [
                'identifier' => $identifier,
                'waktuCek'   => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm:ss') . ' WIB',
            ], 404);
        }

        // 3. Pastikan surat memiliki qr_token jika surat terbitan lama belum memilikinya
        if (empty($surat->qr_token)) {
            $surat->update(['qr_token' => SuratKeluar::generateQrToken()]);
        }

        $metaJenis = $surat->meta_jenis;
        $signers = $surat->penandatangan_aktif;
        $waktuCek = Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm:ss') . ' WIB';

        return view('persuratan.surat-keluar.verifikasi-publik', compact(
            'surat',
            'metaJenis',
            'signers',
            'waktuCek'
        ));
    }
}
