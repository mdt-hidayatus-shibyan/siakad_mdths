<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $tipe, $id)
    {
        // 1. Inisialisasi variabel untuk menampung data
        $profil = null;
        $roleName = '';
        $keteranganTambahan = 'MDT Hidayatus Shibyan'; // Opsional jika ingin melempar nama ruangan/tahun ke view

        // 2. Cari data berdasarkan tipe yang dilempar dari Route
        switch (strtolower($tipe)) {
            case 'ustadz':
                $profil = Ustadz::findOrFail($id);
                $roleName = 'Pendidik / Ustadz';
                $tahunAktif = TahunPelajaran::where('is_active', true)->first();

                if ($tahunAktif) {
                    $ruangan = Ruangan::where('tahun_pelajaran_id', $tahunAktif->id)
                        ->where('ustadz_id', $profil->id)
                        ->first();

                    if ($ruangan) {
                        $roleName = 'Wali Ruangan ' . $ruangan->nama_ruangan;
                        $keteranganTambahan = 'Tahun Pelajaran ' . $tahunAktif->nama_tahun;
                    }
                }
                break;

            case 'administrator':
                $profil = Administrator::with('tingkat')->findOrFail($id);
                $roleName = $profil->tingkat ? ('Kepala Administrasi ' . ($profil->tingkat->nama_tingkat ?? $profil->tingkat->nama)) : 'Administrator';
                break;

            case 'pengurus':
                $profil = Pengurus::findOrFail($id);
                $roleName = 'Pengurus Yayasan';
                break;

            default:
                abort(404, 'Tipe profil tidak dikenali.');
        }

        // 3. (Opsional) Keamanan Tambahan: Cek apakah profil sedang aktif
        if (isset($profil->is_active) && !$profil->is_active) {
            abort(403, 'Profil ini sedang dinonaktifkan.');
        }

        // 4. Lempar ke satu View Blade yang sama untuk semua tipe
        // Tambahkan variabel $keteranganTambahan agar view bisa lebih dinamis
        return view('profil-publik.index', compact('profil', 'tipe', 'roleName', 'keteranganTambahan'));
    }
}
