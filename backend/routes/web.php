<?php

use Illuminate\Support\Facades\Route;

// 1. Dashboard & Core Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;

// 2. Master Data & Pengguna Controllers
use App\Http\Controllers\MasterData\AdministratorController;
use App\Http\Controllers\MasterData\UstadzController;
use App\Http\Controllers\MasterData\WaliMuridController;
use App\Http\Controllers\MasterData\MuridController;
use App\Http\Controllers\MasterData\KartuPelajarController;
use App\Http\Controllers\MasterData\KampungController;
use App\Http\Controllers\MasterData\TingkatController;
use App\Http\Controllers\MasterData\LevelController;
use App\Http\Controllers\MasterData\GedungController;
use App\Http\Controllers\MasterData\RuanganController;
use App\Http\Controllers\MasterData\SarprasController;
use App\Http\Controllers\MasterData\MataPelajaranController;
use App\Http\Controllers\MasterData\ReferensiPelanggaranController;

// 3. Akademik & Kesiswaan Controllers
use App\Http\Controllers\Akademik\RombonganBelajarController;
use App\Http\Controllers\Akademik\JadwalPelajaranController;
use App\Http\Controllers\Akademik\KalendarPendidikanController;
use App\Http\Controllers\Akademik\PengaturanAkademikController;
use App\Http\Controllers\Akademik\PresensiMuridController;
use App\Http\Controllers\Akademik\PresensiUstadzController;
use App\Http\Controllers\Akademik\PelanggaranMuridController;
use App\Http\Controllers\Akademik\KategoriKegiatanController;

// 4. SPMB Controllers
use App\Http\Controllers\Spmb\SpmbController;
use App\Http\Controllers\Spmb\SpmbAdminController;

// 5. Ujian, Evaluasi & Rapor Controllers
use App\Http\Controllers\Ujian\UjianController;
use App\Http\Controllers\Ujian\JadwalUjianController;
use App\Http\Controllers\Ujian\PersyaratanUjianController;
use App\Http\Controllers\Ujian\PresensiUjianController;
use App\Http\Controllers\Ujian\NilaiUjianController;
use App\Http\Controllers\Ujian\BintangPelajarController;
use App\Http\Controllers\Ujian\RiwayatKenaikanController;
use App\Http\Controllers\Ujian\RaporController;
use App\Http\Controllers\Ujian\PembayaranUjianController;
use App\Http\Controllers\PetugasCetakController;

// 6. Kesekretariatan & Arsip Controllers
use App\Http\Controllers\Kepengurusan\JabatanPengurusController;
use App\Http\Controllers\Kepengurusan\PeriodeKepengurusanController;
use App\Http\Controllers\Kepengurusan\AnggotaController;
use App\Http\Controllers\Kepengurusan\PengurusController;
use App\Http\Controllers\Arsip\ArsipDokumenController;
use App\Http\Controllers\Arsip\ArsipRaporController;
use App\Http\Controllers\Arsip\ArsipSKController;
use App\Http\Controllers\Arsip\ArsipIjazahController;

// 7. Keuangan Madrasah Controllers
use App\Http\Controllers\Keuangan\TagihanMuridController;
use App\Http\Controllers\Keuangan\TagihanWaliMuridController;
use App\Http\Controllers\Keuangan\PembayaranTagihanController;
use App\Http\Controllers\Keuangan\PengaturanTagihanController;
use App\Http\Controllers\Keuangan\AkunKeuanganController;
use App\Http\Controllers\Keuangan\BankController;
use App\Http\Controllers\Keuangan\KategoriKeuanganController;
use App\Http\Controllers\Keuangan\TransaksiKeuanganController;
use App\Http\Controllers\Keuangan\NasabahPinjamanController;
use App\Http\Controllers\Keuangan\PinjamanController;
use App\Http\Controllers\Keuangan\LaporanKeuanganMadrasahController;
use App\Http\Controllers\KasRuangan\KasRuanganController;
use App\Http\Controllers\KasRuangan\PengaturanKasRuanganController;
use App\Http\Controllers\KasRuangan\SetoranKasRuanganController;

// 8. Tabungan Madrasah Controllers
use App\Http\Controllers\Tabungan\TabunganMadrasahController;
use App\Http\Controllers\Tabungan\PengaturanPotonganController;
use App\Http\Controllers\Tabungan\PembagianTabunganController;
use App\Http\Controllers\Tabungan\RincianTabunganController;
use App\Http\Controllers\Tabungan\CekMutasiTabunganController;
use App\Http\Controllers\Tabungan\PecahanUangTabunganController;
use App\Http\Controllers\Tabungan\KomplainTabunganController;

// 9. Koperasi Madrasah (POS Kasir & Toko) Controllers
use App\Http\Controllers\Koperasi\KoperasiDashboardController;
use App\Http\Controllers\Koperasi\KategoriProdukController;
use App\Http\Controllers\Koperasi\ProdukKoperasiController;
use App\Http\Controllers\Koperasi\PaketKoperasiController;
use App\Http\Controllers\Koperasi\KasirKoperasiController;
use App\Http\Controllers\Koperasi\TransaksiKoperasiController;
use App\Http\Controllers\Koperasi\StokKoperasiController;
use App\Http\Controllers\Koperasi\PembelianKoperasiController;
use App\Http\Controllers\Koperasi\LaporanKoperasiController;

// 10. Pengaturan Sistem & RBAC Controllers
use App\Http\Controllers\PengaturanMenu\MenuController;
use App\Http\Controllers\PengaturanMenu\RoleController;
use App\Http\Controllers\PengaturanMenu\PermissionController;
use App\Http\Controllers\Pengaturan\UserController;
use App\Http\Controllers\Pengaturan\PengumumanController;
use App\Http\Controllers\Pengaturan\TahunPelajaranController;
use App\Http\Controllers\Pengaturan\SettingController;
use App\Http\Controllers\Pengaturan\BackupController;

// 10. Layanan & Bantuan Controllers
use App\Http\Controllers\Bantuan\LaporanKendalaAdminController;


/*
|--------------------------------------------------------------------------
| 1. PUBLIC / GUEST ROUTES (Tanpa Autentikasi)
|--------------------------------------------------------------------------
*/

// Root URL -> Login Page
Route::get('/', function () {
    return view('auth.login');
});

// Portal SPMB Online Publik (Protected with Rate Limiter)
Route::prefix('spmb')->name('spmb.')->middleware('throttle:30,1')->group(function () {
    Route::get('/', [SpmbController::class, 'index'])->name('index');
    Route::get('/form', [SpmbController::class, 'form'])->name('form');
    Route::post('/check-kk', [SpmbController::class, 'checkKk'])->name('check-kk');
    Route::get('/daftar-wali', [SpmbController::class, 'formDaftarWali'])->name('daftar-wali');
    Route::post('/daftar-wali', [SpmbController::class, 'storeWali'])->name('store-wali');
    Route::get('/daftar-murid/{wali_murid_id}', [SpmbController::class, 'formDaftarMurid'])->name('daftar-murid');
    Route::post('/daftar-murid', [SpmbController::class, 'storeMurid'])->name('store-murid');
    Route::post('/', [SpmbController::class, 'store'])->name('store');
    Route::get('/search-kk', [SpmbController::class, 'searchKk'])->name('search-kk');
    Route::get('/bukti/{nomor_pendaftaran}', [SpmbController::class, 'bukti'])->name('bukti');
    Route::get('/bukti/{nomor_pendaftaran}/cetak', [SpmbController::class, 'cetakBukti'])->name('cetak-bukti');
    Route::get('/status', [SpmbController::class, 'cekStatus'])->name('cek-status');
});

// Verifikasi Profil Publik (Signed URL)
Route::get('/verifikasi-profil/{tipe}/{id}', PublicProfileController::class)
    ->name('profil.publik')
    ->middleware('signed');

// Storage File Delivery Route (Secured against Directory Traversal)
Route::get('/storage/{path}', function ($path) {
    $basePath = realpath(storage_path('app/public'));
    $targetPath = storage_path('app/public/' . $path);
    $realPath = realpath($targetPath);

    // Pastikan path valid, berada di dalam direktori storage/app/public, dan file benar-benar ada
    if (!$realPath || !$basePath || !str_starts_with($realPath, $basePath) || !file_exists($realPath) || is_dir($realPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';
    return response()->file($realPath, [
        'Content-Type' => $mimeType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.local');

// Arsip Dokumen Publik (Cetak & Unduh PDF via ID UUID Dokumen)
Route::get('/arsip-dokumen/{id}/cetak', [ArsipDokumenController::class, 'cetak'])->name('arsip.cetak');
Route::get('/arsip-dokumen/{id}/download', [ArsipDokumenController::class, 'download'])->name('arsip.download');


/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED SYSTEM ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // --- 2.1 Dashboard & Notifikasi ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/petugas-cetak', [PetugasCetakController::class, 'index'])->name('petugas-cetak.index');

    Route::post('/notifikasi/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifikasi.markAllRead');
    Route::get('/notifikasi/{notification}', NotificationController::class)->name('notifikasi.show');

    // --- 2.2 Profil Pengguna ---
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
        Route::patch('/administrator', 'updateAdministrator')->name('administrator.update');
    });

    // ==========================================
    // 3. MASTER DATA & PENGGUNA
    // ==========================================

    // -- Administrator & Staff --
    Route::prefix('administrator')->name('administrator.')->group(function () {
        Route::post('/{id}/toggle-status', [AdministratorController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{administrator}/resend-verification', [AdministratorController::class, 'resendVerification'])->name('resend-verification');
        Route::get('/{id}/signature', [AdministratorController::class, 'signature'])->name('signature');
        Route::post('/{id}/signature', [AdministratorController::class, 'updateSignature'])->name('signature.update');
    });
    Route::resource('administrator', AdministratorController::class);

    // -- Ustadz / Dewan Guru --
    Route::prefix('ustadz')->name('ustadz.')->group(function () {
        Route::post('/{id}/toggle-status', [UstadzController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/import', [UstadzController::class, 'modalImport'])->name('import');
        Route::post('/import', [UstadzController::class, 'import'])->name('import.store');
        Route::get('/template-import', [UstadzController::class, 'template'])->name('template');
        Route::post('/{ustadz}/resend-verification', [UstadzController::class, 'resendVerification'])->name('resend-verification');
        Route::get('/{id}/signature', [UstadzController::class, 'signature'])->name('signature');
        Route::post('/{id}/signature', [UstadzController::class, 'updateSignature'])->name('signature.update');
    });
    Route::resource('ustadz', UstadzController::class);

    // -- Wali Murid --
    Route::prefix('wali-murid')->name('wali-murid.')->group(function () {
        Route::get('/cetak', [WaliMuridController::class, 'cetak'])->name('cetak');
        Route::get('/export-excel', [WaliMuridController::class, 'exportExcel'])->name('export-excel');
        Route::get('/import', [WaliMuridController::class, 'modalImport'])->name('import');
        Route::post('/import', [WaliMuridController::class, 'import'])->name('import.store');
        Route::get('/template-import', [WaliMuridController::class, 'template'])->name('template');
        Route::get('/search-kk', [WaliMuridController::class, 'searchKk'])->name('searchKk');
        Route::post('/{id}/link-anak', [WaliMuridController::class, 'linkAnak'])->name('link-anak');
        Route::post('/{id}/unlink-anak/{murid_id}', [WaliMuridController::class, 'unlinkAnak'])->name('unlink-anak');
    });
    Route::resource('wali-murid', WaliMuridController::class);

    // -- Murid --
    Route::prefix('murid')->name('murid.')->group(function () {
        Route::get('/yatim', [MuridController::class, 'filterYatim'])->name('yatim');
        Route::get('/yatim/download', [MuridController::class, 'downloadYatim'])->name('downloadYatim');
        Route::get('/import', [MuridController::class, 'modalImport'])->name('import');
        Route::post('/import', [MuridController::class, 'import'])->name('import.store');
        Route::get('/template-import', [MuridController::class, 'template'])->name('template');
        Route::patch('/{id}/status', [MuridController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{id}/update-foto', [MuridController::class, 'updateFoto'])->name('updateFoto');
    });
    Route::resource('murid', MuridController::class);

    // -- Kartu Pelajar --
    Route::prefix('kartu-pelajar')->name('kartu-pelajar.')->group(function () {
        Route::get('/', [KartuPelajarController::class, 'index'])->name('index');
        Route::post('/cetak', [KartuPelajarController::class, 'cetak'])->name('cetak');
        Route::get('/{ruangan_id}/upload-foto/{murid_id}', [KartuPelajarController::class, 'modalUpload'])->name('uploadFoto');
    });

    // -- Manajemen Akun Pengguna (Khusus Administrator) --
    Route::middleware('role:administrator')->group(function () {
        Route::prefix('pengguna')->name('pengguna.')->group(function () {
            Route::post('/{id}/force-logout', [UserController::class, 'forceLogout'])->name('force-logout');
            Route::get('/{id}/whatsapp', [UserController::class, 'hubungiWhatsApp'])->name('whatsapp');
            Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        });
        Route::resource('pengguna', UserController::class);
        Route::get('/user', fn() => redirect()->route('pengguna.index'))->name('user.index');
    });


    // ==========================================
    // 4. REFERENSI & DATA INDUK
    // ==========================================

    // -- Tingkat Madrasah --
    Route::post('/tingkat/{id}/toggle-status', [TingkatController::class, 'toggleStatus'])->name('tingkat.toggle-status');
    Route::resource('tingkat', TingkatController::class);

    // -- Level / Kelas --
    Route::post('/level/{id}/toggle-status', [LevelController::class, 'toggleStatus'])->name('level.toggle-status');
    Route::resource('level', LevelController::class)->except(['show']);

    // -- Gedung --
    Route::prefix('gedung')->name('gedung.')->group(function () {
        Route::post('/{id}/toggle-status', [GedungController::class, 'toggleStatus'])->name('toggle-status');
    });
    Route::resource('gedung', GedungController::class);

    // -- Ruangan Kelas --
    Route::prefix('ruangan')->name('ruangan.')->group(function () {
        Route::post('/{id}/toggle-status', [RuanganController::class, 'toggleStatus'])->name('toggle-status');
    });
    Route::resource('ruangan', RuanganController::class)->except(['show']);

    // -- Sarana & Prasarana --
    Route::prefix('sarpras')->name('sarpras.')->group(function () {
        Route::post('/{id}/toggle-status', [SarprasController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/get-ruangan/{gedung_id?}', [SarprasController::class, 'getRuanganByGedung'])->name('get-ruangan');
    });
    Route::resource('sarpras', SarprasController::class);

    // -- Mata Pelajaran --
    Route::prefix('mata-pelajaran')->name('mata-pelajaran.')->group(function () {
        Route::get('/level/{level_id}', [MataPelajaranController::class, 'levelShow'])->name('level');
        Route::get('/level/{level_id}/create', [MataPelajaranController::class, 'create'])->name('level.create');
        Route::get('/level/{level_id}/edit/{mapel_id}', [MataPelajaranController::class, 'edit'])->name('level.edit');
        Route::post('level/{id}/toggle-status', [MataPelajaranController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/import', [MataPelajaranController::class, 'modalImport'])->name('import');
        Route::get('/template/download', [MataPelajaranController::class, 'template'])->name('template');
        Route::post('/import', [MataPelajaranController::class, 'import'])->name('import.store');
    });
    Route::resource('mata-pelajaran', MataPelajaranController::class)->except(['create', 'show', 'edit']);

    // -- Referensi Pelanggaran --
    Route::prefix('referensi-pelanggaran')->name('referensi-pelanggaran.')->group(function () {
        Route::get('/template/download', [ReferensiPelanggaranController::class, 'template'])->name('template');
        Route::get('/import', [ReferensiPelanggaranController::class, 'modalImport'])->name('import');
        Route::post('/import', [ReferensiPelanggaranController::class, 'import'])->name('import.store');
    });
    Route::resource('referensi-pelanggaran', ReferensiPelanggaranController::class);

    // -- Kampung / Dusun --
    Route::resource('kampung', KampungController::class);

    // -- Pengumuman & Kategori Kegiatan --
    Route::resource('pengumuman', PengumumanController::class);
    Route::resource('kategori-kegiatan', KategoriKegiatanController::class)->except(['index', 'show']);


    // ==========================================
    // 5. AKADEMIK & KESISWAAN
    // ==========================================

    // -- Rombongan Belajar (Rombel) --
    Route::prefix('rombongan-belajar')->name('rombongan-belajar.')->group(function () {
        Route::get('/{id}/anggota', [RombonganBelajarController::class, 'anggota'])->name('anggota');
        Route::post('/{id}/anggota/attach', [RombonganBelajarController::class, 'attachAnggota'])->name('attach-anggota');
        Route::post('/{id}/anggota/detach/{murid_id}', [RombonganBelajarController::class, 'detachAnggota'])->name('detach-anggota');
        Route::get('/{id}/plotting-kenaikan', [RombonganBelajarController::class, 'plottingKenaikan'])->name('plotting-kenaikan');
        Route::post('/{id}/plotting-kenaikan', [RombonganBelajarController::class, 'storePlotting'])->name('store-plotting');
        Route::post('/{id}/pindah-anggota', [RombonganBelajarController::class, 'pindahAnggota'])->name('pindah-anggota');
        Route::get('/{id}/uploadFoto/{murid_id}', [RombonganBelajarController::class, 'modalUpload'])->name('uploadFoto');

        // Print & Export Rombel
        Route::get('/{id}/print-pembayaran-anggota', [RombonganBelajarController::class, 'printPembayaranAnggota'])->name('print-pembayaran-anggota');
        Route::get('/{id}/print-penilaian-anggota', [RombonganBelajarController::class, 'printPenilaianAnggota'])->name('print-penilaian-anggota');
        Route::get('/{id}/print-anggota', [RombonganBelajarController::class, 'printAnggota'])->name('print-anggota');
        Route::get('/{id}/export-anggota', [RombonganBelajarController::class, 'exportAnggota'])->name('export-anggota');
    });
    Route::resource('rombongan-belajar', RombonganBelajarController::class)->except(['show', 'create', 'edit', 'update', 'destroy']);

    // -- Jadwal Pelajaran --
    Route::prefix('jadwal-pelajaran')->name('jadwal-pelajaran.')->group(function () {
        Route::get('/ruangan/{ruangan_id}', [JadwalPelajaranController::class, 'ruanganShow'])->name('ruangan');
        Route::post('/ruangan/{id}/mass-store', [JadwalPelajaranController::class, 'massStore'])->name('mass-store');
        Route::get('/induk', [JadwalPelajaranController::class, 'jadwalInduk'])->name('induk');
        Route::patch('/ruangan/{ruangan_id}/toggle-publikasi', [JadwalPelajaranController::class, 'togglePublikasi'])->name('toggle-publikasi');
        Route::get('/cetak-leger', [JadwalPelajaranController::class, 'cetakLeger'])->name('cetak-leger');
    });
    Route::resource('jadwal-pelajaran', JadwalPelajaranController::class)->except(['create', 'edit', 'show']);

    // -- Kalendar Pendidikan --
    Route::prefix('kalendar-pendidikan')->name('kalendar-pendidikan.')->controller(KalendarPendidikanController::class)->group(function () {
        Route::get('/matriks', 'matriksKalender')->name('matriks');
        Route::get('/matriks/set-bulan', 'setBulanMatriks')->name('matriks.set-bulan');
        Route::get('/matriks/create-agenda', 'createAgendaMatriks')->name('matriks.create-agenda');
        Route::post('/storebymatriks', 'storeBulanByMatriks')->name('matriks.store-bulanbymatriks');

        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // -- Pengaturan Akademik (Semester & Bulan) --
    Route::prefix('pengaturan-akademik')->name('pengaturan-akademik.')->controller(PengaturanAkademikController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/konfig', 'updateKonfig')->name('update-konfig');

        Route::get('/semester/{tahun_id?}', 'createSemester')->name('create-semester');
        Route::post('/semester', 'storeSemester')->name('store-semester');
        Route::get('/semester/{id}/edit', 'editSemester')->name('edit-semester');
        Route::put('/semester/{id}/update', 'updateSemester')->name('update-semester');
        Route::post('/semester/{id}/aktif', 'activateSemester')->name('activate-semester');
        Route::delete('/semester/{id}/destroy', 'destroySemester')->name('destroy-semester');

        Route::get('/bulan/{tahun_id}', 'createBulan')->name('create-bulan');
        Route::post('/bulan', 'storeBulan')->name('store-bulan');
        Route::get('/bulan/{id}/edit', 'editBulan')->name('edit-bulan');
        Route::put('/bulan/{id}/update', 'updateBulan')->name('update-bulan');
        Route::post('/bulan/{id}/aktif', 'activateBulan')->name('activate-bulan');
        Route::delete('/bulan/{id}/destroy', 'destroyBulan')->name('destroy-bulan');
    });

    // -- Presensi Murid --
    Route::prefix('presensi-murid')->name('presensi-murid.')->group(function () {
        Route::get('/', [PresensiMuridController::class, 'index'])->name('index');
        Route::post('/store', [PresensiMuridController::class, 'storeHarian'])->name('storeHarian');
        Route::get('/bulanan', [PresensiMuridController::class, 'bulanan'])->name('bulanan');
        Route::post('/bulanan/store', [PresensiMuridController::class, 'storeBulanan'])->name('store');
        Route::get('/rekap', [PresensiMuridController::class, 'rekap'])->name('rekap');
        Route::get('/cetak-rekap', [PresensiMuridController::class, 'cetakRekap'])->name('cetak-rekap');
    });

    // -- Presensi Ustadz --
    Route::prefix('presensi-ustadz')->name('presensi-ustadz.')->group(function () {
        Route::get('/', [PresensiUstadzController::class, 'index'])->name('index');
        Route::post('/harian/store', [PresensiUstadzController::class, 'storeHarian'])->name('storeHarian');
        Route::delete('/harian/{id}/destroy', [PresensiUstadzController::class, 'destroyHarian'])->name('destroyHarian');
        Route::get('/bulanan', [PresensiUstadzController::class, 'bulanan'])->name('bulanan');
        Route::post('/bulanan/store', [PresensiUstadzController::class, 'storeBulanan'])->name('storeBulanan');
        Route::get('/rekap-semua', [PresensiUstadzController::class, 'rekapSemua'])->name('rekapSemua');
        Route::get('/cetak-rekap', [PresensiUstadzController::class, 'cetakRekap'])->name('cetak-rekap');
        Route::get('/rekap-semua/export', [PresensiUstadzController::class, 'exportExcel'])->name('exportExcel');
    });

    // -- Catatan Pelanggaran Murid --
    Route::prefix('pelanggaran-murid')->name('pelanggaran-murid.')->group(function () {
        Route::get('/', [PelanggaranMuridController::class, 'index'])->name('index');
        Route::post('/harian/store', [PelanggaranMuridController::class, 'storeHarian'])->name('storeHarian');
        Route::delete('/harian/{id}/destroy', [PelanggaranMuridController::class, 'destroyHarian'])->name('destroyHarian');
        Route::get('/massal', [PelanggaranMuridController::class, 'massal'])->name('massal');
        Route::post('/massal/store', [PelanggaranMuridController::class, 'storeMassal'])->name('storeMassal');
        Route::get('/admin-mode', [PelanggaranMuridController::class, 'adminMode'])->name('adminMode');
        Route::post('/admin-mode/sync', [PelanggaranMuridController::class, 'syncAdminMode'])->name('syncAdminMode');
        Route::get('/rekap', [PelanggaranMuridController::class, 'rekap'])->name('rekap');
        Route::get('/rekap/export', [PelanggaranMuridController::class, 'exportExcel'])->name('exportExcel');
    });

    // -- SPMB Admin Panel --
    Route::prefix('spmb-admin')->name('spmb-admin.')->controller(SpmbAdminController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export-excel', 'exportExcel')->name('export-excel');
        Route::get('/scan/{nomor}', 'scan')->name('scan');
        Route::get('/{id}/detail-json', 'getDetailJson')->name('detail-json');
        Route::post('/{id}/verifikasi', 'verifikasi')->name('verifikasi');
        Route::post('/{id}/tolak', 'tolak')->name('tolak');
        Route::get('/{id}/cetak-diterima', 'cetakBuktiPenerimaan')->name('cetak-diterima');
    });


    // ==========================================
    // 6. UJIAN, EVALUASI & RAPOR
    // ==========================================

    // -- Master Ujian --
    Route::resource('ujian', UjianController::class)->names('ujian');

    // -- Jadwal Ujian --
    Route::prefix('jadwal-ujian')->name('jadwal-ujian.')->group(function () {
        Route::get('/cetak-leger', [JadwalUjianController::class, 'cetakLeger'])->name('cetak-leger');
        Route::get('/create', [JadwalUjianController::class, 'create'])->name('create');
        Route::post('/store', [JadwalUjianController::class, 'store'])->name('store');
    });
    Route::resource('jadwal-ujian', JadwalUjianController::class)->names('jadwal-ujian')->except(['create', 'store']);

    // -- Persyaratan Ujian --
    Route::prefix('persyaratan-ujian')->name('persyaratan-ujian.')->group(function () {
        Route::get('/', [PersyaratanUjianController::class, 'index'])->name('index');
        Route::post('/dispensasi', [PersyaratanUjianController::class, 'beriDispensasi'])->name('dispensasi');
    });

    // -- Presensi Ujian --
    Route::prefix('presensi-ujian')->name('presensi-ujian.')->group(function () {
        Route::get('/', [PresensiUjianController::class, 'index'])->name('index');
        Route::get('/input', [PresensiUjianController::class, 'inputPresensi'])->name('input');
        Route::post('/store', [PresensiUjianController::class, 'store'])->name('store');
        Route::get('/rekap', [PresensiUjianController::class, 'rekap'])->name('rekap');
        Route::get('/cetak-menu', [PresensiUjianController::class, 'cetakMenu'])->name('cetak-menu');
        Route::get('/cetak-dhpu', [PresensiUjianController::class, 'cetakDhpu'])->name('cetak-dhpu');
        Route::get('/cetak-berita-acara', [PresensiUjianController::class, 'cetakBeritaAcara'])->name('cetak-berita-acara');
        Route::get('/cetak-rekap', [PresensiUjianController::class, 'cetakRekap'])->name('cetak-rekap');
    });

    // -- Nilai Ujian & Leger --
    Route::prefix('nilai-ujian')->name('nilai-ujian.')->group(function () {
        Route::get('/', [NilaiUjianController::class, 'index'])->name('index');
        Route::get('/input-nilai', [NilaiUjianController::class, 'inputNilai'])->name('input-nilai');
        Route::post('/store', [NilaiUjianController::class, 'simpanNilai'])->name('store');
        Route::get('/input-leger', [NilaiUjianController::class, 'inputLeger'])->name('input-leger');
        Route::post('/input-leger/store', [NilaiUjianController::class, 'storeLeger'])->name('input-leger.store');
        Route::get('/laporan-leger', [NilaiUjianController::class, 'laporanLeger'])->name('laporan-leger');
    });

    // -- Bintang Pelajar --
    Route::get('/bintang-pelajar', [BintangPelajarController::class, 'bintangPelajar'])->name('bintang-pelajar.index');
    Route::get('/bintang-madrasah', [BintangPelajarController::class, 'bintangMadrasah'])->name('bintang-madrasah.index');

    // -- Kenaikan Kelas --
    Route::prefix('kenaikan-kelas')->name('kenaikan-kelas.')->group(function () {
        Route::get('/', [RiwayatKenaikanController::class, 'index'])->name('index');
        Route::post('/simpan', [RiwayatKenaikanController::class, 'simpan'])->name('simpan');
        Route::get('/cetak-sk/{tahun_id}/{ruangan_id}/{murid_id}', [RiwayatKenaikanController::class, 'cetak_sk'])->name('cetak_sk');
        Route::get('/cetak-sk/{tahun_id}/{ruangan_id}/{murid_id}/pdf', [RiwayatKenaikanController::class, 'downloadSKPdf'])->name('cetak_sk_pdf');
        Route::get('/cetak-ijazah/{tahun_id}/{ruangan_id}/{murid_id}', [RiwayatKenaikanController::class, 'cetak_ijazah'])->name('cetak_ijazah');
    });

    // -- Cetak Rapor --
    Route::prefix('rapor')->name('rapor.')->group(function () {
        Route::get('/', [RaporController::class, 'index'])->name('index');
        Route::get('/cetak/{ujian_id}/{murid_id}', [RaporController::class, 'cetakRapor'])->name('cetak');
        Route::post('/{murid_id}/ujian/{ujian_id}/arsipkan', [RaporController::class, 'arsipkanRapor'])->name('arsipkan');
        Route::post('/arsipkan-bulk', [RaporController::class, 'arsipkanBulk'])->name('arsipkan_bulk');
    });

    // -- Pembayaran Ujian --
    Route::prefix('pembayaran-ujian')->name('pembayaran-ujian.')->controller(PembayaranUjianController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/proses', 'proses')->name('proses');
        Route::post('/batal/{id}', 'batalTransaksi')->name('batal');
        Route::get('/cetak-rekap-spp/{murid_id}/{tahun_id}', 'cetakRekapSpp')->name('cetak-rekap-spp');
        Route::get('/cetak/{id}', 'cetakKwitansi')->name('cetak');
        Route::get('/laporan', 'laporan')->name('laporan');
    });


    // ==========================================
    // 7. KESEKRETARIATAN & ARSIP
    // ==========================================

    // -- Kepengurusan Madrasah --
    Route::resource('jabatan-pengurus', JabatanPengurusController::class)->names('jabatan-pengurus');
    Route::resource('periode-pengurus', PeriodeKepengurusanController::class)->names('periode-pengurus');
    Route::controller(AnggotaController::class)->prefix('anggota')->name('anggota.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{anggota}/edit', 'edit')->name('edit');
        Route::put('/{anggota}', 'update')->name('update');
        Route::delete('/{anggota}', 'destroy')->name('destroy');
    });
    Route::resource('pengurus', PengurusController::class)->names('pengurus');

    // -- Arsip Dokumen --
    Route::get('/arsip-rapor', [ArsipRaporController::class, 'index'])->name('arsip-rapor.index');
    Route::get('/arsip-sk', [ArsipSKController::class, 'index'])->name('arsip-sk.index');
    Route::get('/arsip-ijazah', [ArsipIjazahController::class, 'index'])->name('arsip-ijazah.index');


    // ==========================================
    // 8. KEUANGAN MADRASAH
    // ==========================================

    // -- Tagihan Syahriyah / Murid --
    Route::prefix('tagihan-murid')->name('tagihan-murid.')->group(function () {
        Route::get('/cetak-kartu-spp/{murid_id}/{tahun_id}', [TagihanMuridController::class, 'cetakKartuSpp'])->name('cetak-kartu-spp');
        Route::get('/cetak-kartu-spp-massal/{ruangan_id}/{tahun_id}', [TagihanMuridController::class, 'cetakKartuSppMassal'])->name('cetak-kartu-spp-massal');
        Route::post('/proses', [TagihanMuridController::class, 'prosesTagihanPilihan'])->name('proses');
    });
    Route::resource('tagihan-murid', TagihanMuridController::class);

    // -- Tagihan Per Wali Murid (KK Aktif) --
    Route::prefix('tagihan-wali')->name('tagihan-wali.')->controller(TagihanWaliMuridController::class)->group(function () {
        Route::get('/', 'index')->name('index');

        // 1. Submenu: Terbitkan Tagihan
        Route::get('/terbitkan', 'terbitkanIndex')->name('terbitkan-index');
        Route::post('/terbitkan', 'terbitkan')->name('terbitkan');
        Route::post('/hapus-massal', 'hapusMassal')->name('hapus-massal');
        Route::post('/hapus-semua', 'hapusSemua')->name('hapus-semua');
        Route::delete('/destroy/{id}', 'destroy')->name('destroy');

        // 2. Submenu: Kasir Pembayaran
        Route::get('/kasir', 'kasir')->name('kasir');
        Route::get('/kasir/leger', 'kasirLeger')->name('kasir-leger');
        Route::post('/kasir/leger-proses', 'prosesLeger')->name('kasir-leger.proses');
        Route::post('/bayar', 'bayar')->name('bayar');
        Route::post('/batal/{id}', 'batalBayar')->name('batal');
        Route::get('/cetak-kwitansi/{id}', 'cetakKwitansi')->name('cetak-kwitansi');

        // 3. Submenu: Laporan & Rekap
        Route::get('/laporan', 'laporan')->name('laporan');
        Route::get('/cetak-rekap', 'cetakRekap')->name('cetak-rekap');

        // Detail AJAX Modal
        Route::get('/detail/{id}', 'detail')->name('detail');
    });

    // -- Pembayaran Tagihan / Syahriyah --
    Route::prefix('pembayaran-tagihan')->name('pembayaran-tagihan.')->controller(PembayaranTagihanController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/proses', 'proses')->name('proses');
        Route::post('/batal/{id}', 'batalTransaksi')->name('batal');
        Route::get('/cetak-rekap-spp/{murid_id}/{tahun_id}', 'cetakRekapSpp')->name('cetak-rekap-spp');
        Route::get('/cetak/{id}', 'cetakKwitansi')->name('cetak');

        Route::get('/leger', 'indexLeger')->name('leger');
        Route::post('/leger/proses', 'prosesLeger')->name('leger.proses');

        Route::get('/donatur', 'indexDonatur')->name('donatur');
        Route::post('/donatur/proses', 'prosesDonatur')->name('donatur.proses');
        Route::get('/cetak-donatur/{id}', 'cetakDonatur')->name('cetak-donatur');

        Route::get('/laporan', 'laporan')->name('laporan');
    });

    // -- Pengaturan Tagihan --
    Route::resource('pengaturan-tagihan', PengaturanTagihanController::class);

    // -- Kas Ruangan --
    Route::prefix('pengaturan-kas-ruangan')->name('pengaturan-kas-ruangan.')->group(function () {
        Route::get('/', [PengaturanKasRuanganController::class, 'indexPengaturan'])->name('index');
        Route::post('/auto-save', [PengaturanKasRuanganController::class, 'autoSavePengaturan'])->name('auto-save');
    });

    Route::prefix('kas-ruangan')->name('kas-ruangan.')->group(function () {
        Route::get('/', [KasRuanganController::class, 'indexKasRuangan'])->name('index');
        Route::get('/{ruangan_id}', [KasRuanganController::class, 'showKasRuangan'])->name('show');
        Route::post('/bayar', [KasRuanganController::class, 'simpanPembayaran'])->name('bayar');
        Route::get('/{ruangan}/murid/{murid}/riwayat', [KasRuanganController::class, 'riwayat'])->name('riwayat');
        Route::put('/bayar/{id}', [KasRuanganController::class, 'updatePembayaran'])->name('bayar.update');
        Route::delete('/bayar/{id}', [KasRuanganController::class, 'destroyPembayaran'])->name('bayar.destroy');
    });

    Route::prefix('setoran-kas-ruangan')->name('setoran-kas-ruangan.')->group(function () {
        Route::get('/', [SetoranKasRuanganController::class, 'indexSetoran'])->name('index');
        Route::get('/{ruangan}/riwayat', [SetoranKasRuanganController::class, 'riwayatSetoran'])->name('riwayat');
        Route::post('/', [SetoranKasRuanganController::class, 'simpanSetoran'])->name('simpan');
        Route::post('/{id}/verifikasi', [SetoranKasRuanganController::class, 'verifikasiSetoran'])->name('verifikasi');
        Route::put('/{id}', [SetoranKasRuanganController::class, 'updateSetoran'])->name('update');
        Route::delete('/{id}', [SetoranKasRuanganController::class, 'destroySetoran'])->name('destroy');
    });

    // -- Perbendaharaan & Keuangan Madrasah --
    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        // Master Akun Keuangan (Pos Kas)
        Route::post('/akun/{id}/toggle-status', [AkunKeuanganController::class, 'toggleStatus'])->name('akun.toggle-status');
        Route::resource('akun', AkunKeuanganController::class)->except(['show']);

        // Master Rekening Bank
        Route::post('/bank/{id}/toggle-status', [BankController::class, 'toggleStatus'])->name('bank.toggle-status');
        Route::resource('bank', BankController::class)->except(['show']);

        // Master Kategori & Subkategori
        Route::post('/kategori/{id}/toggle-status', [KategoriKeuanganController::class, 'toggleStatus'])->name('kategori.toggle-status');
        Route::resource('kategori', KategoriKeuanganController::class)->except(['show']);

        // Transaksi Keuangan (Pemasukan, Pengeluaran, Mutasi)
        Route::prefix('transaksi')->name('transaksi.')->group(function () {
            Route::get('/cetak-kwitansi/{id}', [TransaksiKeuanganController::class, 'cetakKwitansi'])->name('cetak-kwitansi');
            Route::post('/{id}/batal', [TransaksiKeuanganController::class, 'batalTransaksi'])->name('batal');
        });
        Route::resource('transaksi', TransaksiKeuanganController::class);

        // Data Nasabah Peminjam
        Route::get('/nasabah/ajax-lookup', [NasabahPinjamanController::class, 'ajaxLookup'])->name('nasabah.ajax-lookup');
        Route::resource('nasabah', NasabahPinjamanController::class);

        // Sistem Pinjaman & Agunan
        Route::prefix('pinjaman')->name('pinjaman.')->group(function () {
            Route::post('/{id}/approve', [PinjamanController::class, 'approve'])->name('approve');
            Route::post('/{id}/tolak', [PinjamanController::class, 'tolak'])->name('tolak');
            Route::post('/{id}/cairkan', [PinjamanController::class, 'cairkan'])->name('cairkan');
            Route::post('/{id}/bayar-angsuran', [PinjamanController::class, 'bayarAngsuran'])->name('bayar-angsuran');
            Route::post('/{id}/kembalikan-jaminan', [PinjamanController::class, 'kembalikanJaminan'])->name('kembalikan-jaminan');

            // Cetak Dokumen Pinjaman
            Route::get('/{id}/cetak-perjanjian', [PinjamanController::class, 'cetakSuratPerjanjian'])->name('cetak-perjanjian');
            Route::get('/{id}/cetak-tanda-terima-jaminan', [PinjamanController::class, 'cetakTandaTerimaJaminan'])->name('cetak-tanda-terima-jaminan');
            Route::get('/{id}/cetak-kartu-angsuran', [PinjamanController::class, 'cetakKartuAngsuran'])->name('cetak-kartu-angsuran');
        });
        Route::resource('pinjaman', PinjamanController::class);

        // Laporan Keuangan Madrasah
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [LaporanKeuanganMadrasahController::class, 'index'])->name('index');
            Route::get('/cetak-buku-kas', [LaporanKeuanganMadrasahController::class, 'cetakBukuKas'])->name('cetak-buku-kas');
            Route::get('/cetak-pinjaman', [LaporanKeuanganMadrasahController::class, 'cetakLaporanPinjaman'])->name('cetak-pinjaman');
        });
    });


    // ==========================================
    // 9. TABUNGAN MADRASAH
    // ==========================================
    Route::prefix('tabungan')->name('tabungan.')->group(function () {
        // Dashboard Tabungan
        Route::get('/', [TabunganMadrasahController::class, 'index'])->name('index');
        Route::get('/dashboard', [TabunganMadrasahController::class, 'index'])->name('dashboard');

        // Master Rekening & AJAX Lookup
        Route::get('/ajax/cari-murid', [TabunganMadrasahController::class, 'cariMuridByNism'])->name('ajax.cari-murid');
        Route::get('/ajax/cari-ustadz', [TabunganMadrasahController::class, 'cariUstadzByNigm'])->name('ajax.cari-ustadz');
        Route::get('/ajax/cari-rekening', [TabunganMadrasahController::class, 'cariRekeningByBarcode'])->name('ajax.cari-rekening');
        Route::get('/barcode/generator', [TabunganMadrasahController::class, 'generatorBarcode'])->name('barcode.generator');
        Route::get('/barcode/export', [TabunganMadrasahController::class, 'exportBarcode'])->name('barcode.export');

        Route::get('/rekening', [TabunganMadrasahController::class, 'rekening'])->name('rekening.index');
        Route::get('/rekening/create', [TabunganMadrasahController::class, 'createRekening'])->name('rekening.create');
        Route::post('/rekening', [TabunganMadrasahController::class, 'storeRekening'])->name('rekening.store');
        Route::get('/rekening/{id}', [TabunganMadrasahController::class, 'detailRekening'])->name('rekening.detail');
        Route::get('/rekening/{id}/ganti-buku', [TabunganMadrasahController::class, 'modalGantiBuku'])->name('rekening.ganti-buku');
        Route::post('/rekening/{id}/ganti-buku', [TabunganMadrasahController::class, 'prosesGantiBuku'])->name('rekening.ganti-buku.store');
        Route::get('/rekening/{id}/cetak', [TabunganMadrasahController::class, 'cetakBukuTabungan'])->name('rekening.cetak');

        // Transaksi Setor & Tarik Tunai
        Route::get('/setor', [TabunganMadrasahController::class, 'formSetorTunai'])->name('setor.index');
        Route::post('/setor', [TabunganMadrasahController::class, 'setorTunai'])->name('setor');
        Route::get('/setor/{id}/edit', [TabunganMadrasahController::class, 'editTransaksiSetor'])->name('setor.edit');
        Route::put('/setor/{id}', [TabunganMadrasahController::class, 'updateTransaksiSetor'])->name('setor.update');
        Route::delete('/setor/{id}', [TabunganMadrasahController::class, 'destroyTransaksiSetor'])->name('setor.destroy');

        Route::get('/tarik', [TabunganMadrasahController::class, 'formTarikTunai'])->name('tarik.index');
        Route::post('/tarik', [TabunganMadrasahController::class, 'tarikTunai'])->name('tarik');
        Route::get('/tarik/{id}/edit', [TabunganMadrasahController::class, 'editTransaksiTarik'])->name('tarik.edit');
        Route::put('/tarik/{id}', [TabunganMadrasahController::class, 'updateTransaksiTarik'])->name('tarik.update');
        Route::delete('/tarik/{id}', [TabunganMadrasahController::class, 'destroyTransaksiTarik'])->name('tarik.destroy');

        // Pengaturan Potongan, Periode, & Kategori
        Route::get('/pengaturan', [PengaturanPotonganController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan/potongan', [PengaturanPotonganController::class, 'updatePotongan'])->name('pengaturan.update');
        Route::get('/pengaturan/periode/create', [PengaturanPotonganController::class, 'createPeriode'])->name('pengaturan.periode.create');
        Route::post('/pengaturan/periode', [PengaturanPotonganController::class, 'storePeriode'])->name('pengaturan.periode.store');
        Route::get('/pengaturan/periode/{id}/edit', [PengaturanPotonganController::class, 'editPeriode'])->name('pengaturan.periode.edit');
        Route::put('/pengaturan/periode/{id}', [PengaturanPotonganController::class, 'updatePeriode'])->name('pengaturan.periode.update');
        Route::delete('/pengaturan/periode/{id}', [PengaturanPotonganController::class, 'destroyPeriode'])->name('pengaturan.periode.destroy');
        Route::get('/pengaturan/periode/{id}/potongan', [PengaturanPotonganController::class, 'editPotonganModal'])->name('pengaturan.periode.potongan.edit');
        Route::put('/pengaturan/periode/{id}/potongan', [PengaturanPotonganController::class, 'updatePotonganModal'])->name('pengaturan.periode.potongan.update');
        Route::post('/pengaturan/periode/{id}/toggle-status', [PengaturanPotonganController::class, 'toggleStatusPeriode'])->name('pengaturan.periode.toggle-status');
        Route::post('/pengaturan/periode/{id}/aktif', [PengaturanPotonganController::class, 'setPeriodeAktif'])->name('pengaturan.periode.aktif');

        Route::get('/pengaturan/kategori/create', [PengaturanPotonganController::class, 'createKategori'])->name('pengaturan.kategori.create');
        Route::post('/pengaturan/kategori', [PengaturanPotonganController::class, 'storeKategori'])->name('pengaturan.kategori.store');
        Route::get('/pengaturan/kategori/{id}/edit', [PengaturanPotonganController::class, 'editKategori'])->name('pengaturan.kategori.edit');
        Route::put('/pengaturan/kategori/{id}', [PengaturanPotonganController::class, 'updateKategori'])->name('pengaturan.kategori.update');
        Route::delete('/pengaturan/kategori/{id}', [PengaturanPotonganController::class, 'destroyKategori'])->name('pengaturan.kategori.destroy');
        Route::post('/pengaturan/kategori/{id}/toggle-status', [PengaturanPotonganController::class, 'toggleStatusKategori'])->name('pengaturan.kategori.toggle-status');

        // Pembagian Akhir Periode & Verifikasi
        Route::get('/pembagian', [PembagianTabunganController::class, 'index'])->name('pembagian.index');
        Route::post('/pembagian/verifikasi/{id}', [PembagianTabunganController::class, 'verifikasi'])->name('pembagian.verifikasi');
        Route::post('/pembagian/verifikasi-semua', [PembagianTabunganController::class, 'verifikasiSemua'])->name('pembagian.verifikasi-semua');
        Route::post('/pembagian/eksekusi', [PembagianTabunganController::class, 'eksekusi'])->name('pembagian.eksekusi');
        Route::get('/pembagian/cetak-laporan', [PembagianTabunganController::class, 'cetakLaporan'])->name('pembagian.cetak');

        // Rincian & Rekap Kas Tabungan
        Route::get('/rincian', [RincianTabunganController::class, 'index'])->name('rincian.index');
        Route::get('/rincian/cetak', [RincianTabunganController::class, 'cetak'])->name('rincian.cetak');

        // Cek Mutasi & Verifikasi Buku Tabungan Fisik
        Route::get('/cek-mutasi', [CekMutasiTabunganController::class, 'index'])->name('cek-mutasi.index');
        Route::post('/cek-mutasi/verifikasi/{id}', [CekMutasiTabunganController::class, 'verifikasi'])->name('cek-mutasi.verifikasi');
        Route::get('/cek-mutasi/cetak/{id}', [CekMutasiTabunganController::class, 'cetak'])->name('cek-mutasi.cetak');
        Route::get('/cek-mutasi/cetak-a6/{id}', [CekMutasiTabunganController::class, 'cetakA6'])->name('cek-mutasi.cetak-a6');

        // Kalkulator & Rekap Uang Pecahan Kas Fisik
        Route::get('/pecahan', [PecahanUangTabunganController::class, 'index'])->name('pecahan.index');
        Route::get('/pecahan/cetak', [PecahanUangTabunganController::class, 'cetak'])->name('pecahan.cetak');
        Route::get('/pecahan/slip/{id}', [PecahanUangTabunganController::class, 'cetakSlip'])->name('pecahan.cetak-slip');
        Route::get('/pecahan/slip-massal', [PecahanUangTabunganController::class, 'cetakSlipMassal'])->name('pecahan.cetak-slip-massal');

        // Verifikasi Komplain Setor Tunai Tabungan Santri
        Route::get('/komplain', [KomplainTabunganController::class, 'index'])->name('komplain.index');
        Route::get('/komplain/{id}', [KomplainTabunganController::class, 'show'])->name('komplain.show');
        Route::post('/komplain/{id}/verifikasi', [KomplainTabunganController::class, 'verifikasi'])->name('komplain.verifikasi');
    });


    // ==========================================
    // 9. KOPERASI MADRASAH (POS KASIR & TOKO)
    // ==========================================
    Route::prefix('koperasi')->name('koperasi.')->group(function () {
        // Dashboard Koperasi
        Route::get('/dashboard', [KoperasiDashboardController::class, 'index'])->name('dashboard');

        // Kasir POS
        Route::get('/pos', [KasirKoperasiController::class, 'index'])->name('pos.index');
        Route::get('/pos/cari-barcode', [KasirKoperasiController::class, 'cariBarcode'])->name('pos.cari-barcode');
        Route::get('/pos/cari-pelanggan', [KasirKoperasiController::class, 'cariPelanggan'])->name('pos.cari-pelanggan');
        Route::get('/pos/paket-rekomendasi', [KasirKoperasiController::class, 'getPaketRekomendasi'])->name('pos.paket-rekomendasi');
        Route::post('/pos/checkout', [KasirKoperasiController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/struk/{id}', [KasirKoperasiController::class, 'struk'])->name('kasir.struk');

        // Master Produk & Kategori
        Route::get('/produk/barcode/cetak-massal', [ProdukKoperasiController::class, 'cetakBarcodeMassal'])->name('produk.barcode-massal');
        Route::get('/produk/barcode/print-sheet', [ProdukKoperasiController::class, 'printSheet'])->name('produk.barcode-sheet');
        Route::get('/produk/{produk}/barcode/cetak', [ProdukKoperasiController::class, 'cetakBarcodeSingle'])->name('produk.barcode-single');
        Route::post('/produk/{produk}/toggle-status', [ProdukKoperasiController::class, 'toggleStatus'])->name('produk.toggle-status');
        Route::post('/kategori/{kategori}/toggle-status', [KategoriProdukController::class, 'toggleStatus'])->name('kategori.toggle-status');
        Route::resource('kategori', KategoriProdukController::class);
        Route::resource('produk', ProdukKoperasiController::class);

        // Paket Bundling (Kitab/Seragam per Level)
        Route::post('/paket/{paket}/toggle-status', [PaketKoperasiController::class, 'toggleStatus'])->name('paket.toggle-status');
        Route::resource('paket', PaketKoperasiController::class);

        // Stok & Kartu Mutasi
        Route::get('/stok/restock', [StokKoperasiController::class, 'restockModal'])->name('stok.restock');
        Route::get('/stok/opname', [StokKoperasiController::class, 'opnameModal'])->name('stok.opname');
        Route::get('/stok', [StokKoperasiController::class, 'index'])->name('stok.index');
        Route::post('/stok', [StokKoperasiController::class, 'store'])->name('stok.store');

        // Pembelian / Kulakan / Restock Grosir
        Route::get('/pembelian', [PembelianKoperasiController::class, 'index'])->name('pembelian.index');
        Route::get('/pembelian/create', [PembelianKoperasiController::class, 'create'])->name('pembelian.create');
        Route::post('/pembelian', [PembelianKoperasiController::class, 'store'])->name('pembelian.store');
        Route::get('/pembelian/{id}', [PembelianKoperasiController::class, 'show'])->name('pembelian.show');
        Route::get('/pembelian/{id}/cetak', [PembelianKoperasiController::class, 'cetak'])->name('pembelian.cetak');
        Route::post('/pembelian/{id}/lunasi', [PembelianKoperasiController::class, 'lunasi'])->name('pembelian.lunasi');
        Route::post('/pembelian/{id}/batal', [PembelianKoperasiController::class, 'batal'])->name('pembelian.batal');

        // Riwayat Transaksi & Detail
        Route::get('/transaksi', [TransaksiKoperasiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/{id}', [TransaksiKoperasiController::class, 'show'])->name('transaksi.show');
        Route::post('/transaksi/{id}/lunasi', [TransaksiKoperasiController::class, 'lunasiHutang'])->name('transaksi.lunasi');
        Route::post('/transaksi/{id}/batal', [TransaksiKoperasiController::class, 'batal'])->name('transaksi.batal');

        // Laporan Keuangan & Penjualan
        Route::get('/laporan', [LaporanKoperasiController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/cetak', [LaporanKoperasiController::class, 'cetak'])->name('laporan.cetak');
    });


    // ==========================================
    // 10. PENGATURAN SISTEM & RBAC (Khusus Administrator)
    // ==========================================
    Route::middleware('role:administrator')->group(function () {
        // -- Manajemen Menu Navigasi --
        Route::post('/menu/update-order', [MenuController::class, 'updateOrder'])->name('menu.update-order');
        Route::post('/menu/{id}/toggle-active', [MenuController::class, 'toggleActive'])->name('menu.toggle-active');
        Route::resource('menu', MenuController::class);

        // -- Role & Permissions (RBAC) --
        Route::post('roles/{id}/give-permissions', [RoleController::class, 'givePermissions'])->name('roles.give-permissions');
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class)->except(['show']);

        // -- Tahun Pelajaran --
        Route::post('/tahun-pelajaran/{id}/toggle-status', [TahunPelajaranController::class, 'toggleStatus'])->name('tahun-pelajaran.toggle-status');
        Route::resource('tahun-pelajaran', TahunPelajaranController::class);

        // -- Pengaturan Aplikasi --
        Route::get('/pengaturan-aplikasi', [SettingController::class, 'index'])->name('pengaturan-aplikasi.index');
        Route::post('/pengaturan-aplikasi', [SettingController::class, 'update'])->name('pengaturan-aplikasi.update');

        // -- Backup & Restore Database --
        Route::prefix('backup')->name('backup.')->controller(BackupController::class)->group(function () {
            Route::get('/', 'index')->name('database');
            Route::post('/process', 'process')->name('process');
            Route::post('/restore', 'restore')->name('restore');
        });
    });


    // ==========================================
    // 11. LAYANAN & BANTUAN
    // ==========================================
    Route::prefix('laporan-kendala-admin')->name('laporan-kendala-admin.')->group(function () {
        Route::get('/', [LaporanKendalaAdminController::class, 'index'])->name('index');
        Route::put('/{id}/status', [LaporanKendalaAdminController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [LaporanKendalaAdminController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/auth.php';
