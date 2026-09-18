<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
