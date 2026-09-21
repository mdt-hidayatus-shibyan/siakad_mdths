<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppVersion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_latest'     => 'boolean',
        'is_active'     => 'boolean',
        'new_features'  => 'array',
        'improvements'  => 'array',
        'dev_details'   => 'array',
        'tech_stacks'   => 'array',
    ];

    /**
     * Booted model events untuk manajemen cache versi
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget('active_app_version_' . $model->app_type);
            Cache::forget('active_app_version_web');
            Cache::forget('active_app_version_ustadz');
            Cache::forget('active_app_version_murid');
            Cache::forget('active_app_version_all');
        });

        static::deleted(function ($model) {
            Cache::forget('active_app_version_' . $model->app_type);
            Cache::forget('active_app_version_web');
            Cache::forget('active_app_version_ustadz');
            Cache::forget('active_app_version_murid');
            Cache::forget('active_app_version_all');
        });
    }

    /**
     * Ambil versi aktif berdasarkan tipe aplikasi (ustadz / murid)
     */
    public static function getActiveVersion(string $appType = 'ustadz'): ?self
    {
        return self::where('is_active', true)
            ->where(function ($q) use ($appType) {
                $q->where('app_type', $appType)
                    ->orWhere('app_type', 'all');
            })
            ->latest('id')
            ->first();
    }

    /**
     * Data default awal aplikasi Ustadz
     */
    public static function defaultUstadzData(): array
    {
        return [
            'app_type'          => 'ustadz',
            'app_title'         => 'Ustadz - MDTHS',
            'app_subtitle'      => 'MDT Hidayatus Shibyan',
            'version'           => '1.0.0',
            'build_number'      => '2026.09',
            'release_date'      => 'September 2026',
            'release_subtitle'  => 'Rilis Perdana • September 2026',
            'release_badge'     => 'Rilis Saat Ini',
            'status_badge'      => 'Versi Terbaru',
            'is_latest'         => true,
            'is_active'         => true,
            'new_features'      => [
                [
                    'title'       => 'Modul Catatan & Keluhan Ustadz',
                    'description' => 'Fitur pelaporan perkembangan murid, fasilitas madrasah, dan evaluasi umum dengan lampiran bukti foto kamera/galeri serta pilihan form select ruangan kelas.',
                ],
                [
                    'title'       => 'Menu Cepat & Pengumuman',
                    'description' => 'Akses cepat pada beranda untuk Kalender Akademik, Referensi Sanksi, Mapel, Jadwal Pelajaran, Catatan, Pengumuman, Laporan Pengampu, dan Tabungan.',
                ],
                [
                    'title'       => 'Presensi & Jurnal Mengajar Harian',
                    'description' => 'Pencatatan kehadiran murid dan ustadz secara realtime dengan verifikasi status dan jurnal materi KBM.',
                ],
                [
                    'title'       => 'Penilaian & Rekap Leger Nilai',
                    'description' => 'Pengelolaan nilai tugas, harian, UTS, UAS, dan rekapitulasi leger nilai murid per mata pelajaran.',
                ],
                [
                    'title'       => 'Buku Kasus & Disiplin Murid',
                    'description' => 'Pencatatan pelanggaran kedisiplinan dan katalog referensi pedoman sanksi pembinaan murid.',
                ],
                [
                    'title'       => 'Kas Ruangan & Tabungan',
                    'description' => 'Transparansi pembukuan kas kelas bagi wali ruangan dan monitoring tabungan pribadi ustadz/murid.',
                ],
                [
                    'title'       => 'Kustomisasi Tema & Tampilan',
                    'description' => 'Dukungan Mode Gelap Super AMOLED, Mode Terang, dan pilihan palet warna aksen aplikasi.',
                ],
            ],
            'improvements'      => [
                [
                    'title'       => 'Penyempurnaan Form Pemilihan Ruangan',
                    'description' => 'Dropdown ruangan kelas kini diurutkan berdasarkan urutan level secara rapi dan terhubung otomatis dengan ID ruangan murid.',
                ],
                [
                    'title'       => 'Desain Tombol AppBar Melingkar',
                    'description' => 'Standardisasi tombol navigasi dan aksi pada AppBar menjadi Circular Button dengan pewarnaan tema konsisten.',
                ],
                [
                    'title'       => 'Standardisasi Istilah Baku',
                    'description' => 'Penggunaan istilah "Murid" secara konsisten di seluruh antarmuka dan basis data aplikasi.',
                ],
                [
                    'title'       => 'Optimasi Jaringan & Keamanan API',
                    'description' => 'Peningkatan kecepatan pengambilan data REST API dan keamanan sesi login menggunakan token Sanctum.',
                ],
            ],
            'dev_name'          => 'Mikyal Adly Ghoffar Hasin',
            'dev_role'          => 'Lead Developer & Tim IT',
            'dev_institution'   => 'MDT Hidayatus Shibyan',
            'dev_description'   => 'Aplikasi ini dirancang dan dikembangkan untuk mendukung digitalisasi tata kelola madrasah, presensi KBM, evaluasi catatan murid, serta transparansi pelaporan terpadu.',
            'dev_details'       => [
                [
                    'icon'  => 'developer_mode',
                    'label' => 'Framework & Bahasa',
                    'value' => 'Flutter (Dart)',
                ],
                [
                    'icon'  => 'dns',
                    'label' => 'Backend & Server',
                    'value' => 'Laravel REST API & MySQL',
                ],
                [
                    'icon'  => 'security',
                    'label' => 'Keamanan Autentikasi',
                    'value' => 'Laravel Sanctum Token',
                ],
                [
                    'icon'  => 'domain',
                    'label' => 'Lembaga',
                    'value' => 'MDT Hidayatus Shibyan',
                ],
            ],
            'tech_stacks'       => ['Flutter', 'Dart', 'Laravel', 'REST API', 'MySQL', 'Provider'],
            'copyright_year'    => '2026',
            'copyright_owner'   => 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => 'All Rights Reserved • SIAKAD MDTHS Mobile',
        ];
    }

    /**
     * Data default awal aplikasi Murid / Wali
     */
    public static function defaultMuridData(): array
    {
        return [
            'app_type'          => 'murid',
            'app_title'         => 'Murid - MDTHS',
            'app_subtitle'      => 'MDT Hidayatus Shibyan',
            'version'           => '1.0.0',
            'build_number'      => '2026.09',
            'release_date'      => 'September 2026',
            'release_subtitle'  => 'Rilis Perdana Wali & Murid',
            'release_badge'     => 'Rilis Saat Ini',
            'status_badge'      => 'Versi Terbaru',
            'is_latest'         => true,
            'is_active'         => true,
            'new_features'      => [
                [
                    'title'       => 'Portal Murid & Wali Santri Terpadu',
                    'description' => 'Akses informasi akademik, rekap presensi kehadiran harian, riwayat nilai rapor, dan monitoring kedisiplinan murid.',
                ],
                [
                    'title'       => 'Monitoring Tabungan & Tagihan Syahriah',
                    'description' => 'Pantau mutasi saldo tabungan murid serta status tagihan bulanan dan pembayaran madrasah.',
                ],
                [
                    'title'       => 'Jadwal Pelajaran & Kalender Akademik',
                    'description' => 'Melihat agenda KBM mingguan dan kalender kegiatan madrasah secara praktis dari genggaman.',
                ],
            ],
            'improvements'      => [
                [
                    'title'       => 'Sinkronisasi Data Realtime',
                    'description' => 'Pembaruan data nilai dan absensi terintegrasi langsung dengan input ustadz pengampu.',
                ],
            ],
            'dev_name'          => 'Mikyal Adly Ghoffar Hasin',
            'dev_role'          => 'Lead Developer & Tim IT',
            'dev_institution'   => 'MDT Hidayatus Shibyan',
            'dev_description'   => 'Aplikasi ini dirancang untuk memberikan kemudahan bagi wali murid dalam memantau perkembangan belajar putra-putrinya di MDT Hidayatus Shibyan.',
            'dev_details'       => [
                ['icon' => 'developer_mode', 'label' => 'Framework & Bahasa', 'value' => 'Flutter (Dart)'],
                ['icon' => 'dns', 'label' => 'Backend & Server', 'value' => 'Laravel REST API & MySQL'],
            ],
            'tech_stacks'       => ['Flutter', 'Dart', 'Laravel', 'REST API', 'MySQL', 'Provider'],
            'copyright_year'    => '2026',
            'copyright_owner'   => 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => 'All Rights Reserved • SIAKAD MDTHS Mobile',
        ];
    }

    /**
     * Data default awal Web SIAKAD
     */
    public static function defaultWebData(): array
    {
        return [
            'app_type'          => 'web',
            'app_title'         => 'SIAKAD MDTHS (Web)',
            'app_subtitle'      => 'Sistem Informasi Akademik MDT Hidayatus Shibyan',
            'version'           => '1.0.0',
            'build_number'      => '2026.09',
            'release_date'      => 'September 2026',
            'release_subtitle'  => 'Sistem Administrasi Terpadu MDTHS',
            'release_badge'     => 'Rilis Stabil',
            'status_badge'      => 'Versi Utama',
            'is_latest'         => true,
            'is_active'         => true,
            'new_features'      => [
                [
                    'title'       => 'Manajemen Akademik & Kesiswaan Komprehensif',
                    'description' => 'Pengelolaan data murid, guru/ustadz, kelas/ruangan, kurikulum, leger nilai, dan percetakan rapor.',
                ],
                [
                    'title'       => 'Penerimaan Santri Baru (SPMB Online)',
                    'description' => 'Pendaftaran online mandiri dengan verifikasi KK, cetak bukti registrasi, dan integrasi data.',
                ],
                [
                    'title'       => 'Keuangan, Tabungan, & Koperasi Madrasah',
                    'description' => 'Sistem penagihan syahriah, pembukuan tabungan siswa/guru, dan kasir POS koperasi.',
                ],
                [
                    'title'       => 'Riwayat Versi & Manajemen Rilis Mobile/Web',
                    'description' => 'Halaman changelog timeline modern untuk mendokumentasikan setiap pembaruan sistem.',
                ],
            ],
            'improvements'      => [
                [
                    'title'       => 'Optimalisasi Antarmuka Material 3',
                    'description' => 'Tampilan responsif, Glassmorphism, dukungan tema Gelap/Terang, dan navigasi ergonomis.',
                ],
            ],
            'dev_name'          => 'Mikyal Adly Ghoffar Hasin',
            'dev_role'          => 'Lead Developer & Tim IT',
            'dev_institution'   => 'MDT Hidayatus Shibyan',
            'dev_description'   => 'Sistem Informasi Akademik berbasis web untuk mempermudah operasional dan manajemen pendidikan madrasah secara terintegrasi.',
            'dev_details'       => [
                ['icon' => 'dns', 'label' => 'Web Engine', 'value' => 'Laravel 11 & PHP 8.2+'],
                ['icon' => 'database', 'label' => 'Database', 'value' => 'MySQL / MariaDB'],
            ],
            'tech_stacks'       => ['Laravel', 'PHP', 'Blade', 'Tailwind CSS', 'Alpine.js', 'MySQL'],
            'copyright_year'    => '2026',
            'copyright_owner'   => 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => 'All Rights Reserved • SIAKAD MDTHS Web',
        ];
    }
}
