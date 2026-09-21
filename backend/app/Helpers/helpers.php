<?php

use App\Models\Setting;
use App\Repositories\MenuRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;


if (!function_exists('getSetting')) {
    /**
     * Mengambil nilai pengaturan berdasarkan key.
     * Menggunakan Cache agar tidak membebani database.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function getSetting($key, $default = null)
    {
        // Menyimpan semua pengaturan ke dalam Cache selamanya (sampai diperbarui)
        $settings = Cache::rememberForever('app_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        // Kembalikan nilai jika ada, jika tidak kembalikan nilai default
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('getAppVersion')) {
    /**
     * Mengambil nomor versi aktif aplikasi dari tabel pengaturan versi (AppVersion).
     *
     * @param string $appType ('web', 'ustadz', 'murid', 'all')
     * @param string|null $default
     * @return string
     */
    function getAppVersion($appType = 'web', $default = null)
    {
        $defaultVersion = $default ?? config('app.version', '1.0.0');

        try {
            return Cache::remember('active_app_version_' . $appType, 86400, function () use ($appType, $defaultVersion) {
                $ver = \App\Models\AppVersion::getActiveVersion($appType);
                return $ver?->version ?? $defaultVersion;
            });
        } catch (\Throwable $e) {
            return $defaultVersion;
        }
    }
}



if (!function_exists('menus')) {
    /**
     * @return Collection
     */

    function menus()
    {
        \Illuminate\Support\Facades\Cache::forget('menus');
        \Illuminate\Support\Facades\Cache::forget('urlMenu');
        if (!Cache::has('menus')) {
            $menus = (new MenuRepository())->getMenus()->groupBy('category');

            Cache::forever('menus', $menus);
        } else {
            $menus = Cache::get('menus');
        }
        return $menus;
    }
}



if (!function_exists('urlMenu')) {
    function urlMenu()
    {
        if (!Cache::has('urlMenu')) {

            $menus = menus()->flatMap(fn($item) => $item);

            $url = [];
            foreach ($menus as $mm) {
                $url[] = $mm->url;
                foreach ($mm->subMenus as $sm) {
                    $url[] = $sm->url;
                }
            }

            Cache::forever('urlMenu', $url);
        } else {
            $url = Cache::get('urlMenu');
        }
        return $url;
    }
}


if (!function_exists('user')) {
    /**
     * @param string $id
     * @return \App\Models\User | string
     */
    function user($id = null)
    {
        if ($id) {
            return request()->user()->{$id};
        }
        return request()->user();
    }
}



if (!function_exists('canAccessMenu')) {
    /**
     * Memeriksa apakah user yang sedang login memiliki hak akses ke menu tertentu.
     *
     * @param \App\Models\KonfigurasiMenu\Menu $menu
     * @return bool
     */
    function canAccessMenu($menu)
    {
        $user = auth()->user();
        if (!$user) return false;

        // 1. Jika ini Menu Dropdown (URL berupa '#' atau berawalan '#') dan memiliki Sub-Menu
        $isDropdown = ($menu->url === '#' || str_starts_with($menu->url, '#')) && $menu->subMenus && $menu->subMenus->count() > 0;
        if ($isDropdown) {
            // Cek rekursif ke seluruh sub-menu: jika ada MINIMAL 1 yang bisa diakses, dropdown dibuka
            foreach ($menu->subMenus as $sub) {
                if (canAccessMenu($sub)) {
                    return true;
                }
            }

            // Cek juga jika role memiliki permission langsung untuk menu parent
            $menuPerms = $menu->permissions->pluck('name')->toArray();
            if (!empty($menuPerms) && $user->hasAnyPermission($menuPerms)) {
                return true;
            }

            return false;
        }

        // 2. Jika Menu Tunggal / Sub-Menu dengan URL rute aktif
        if ($user->can('read ' . $menu->url)) {
            return true;
        }

        // Fallback: Jika tidak punya 'read', tapi punya 'create' / 'update' / 'delete'
        $menuPerms = $menu->permissions->pluck('name')->toArray();
        if (!empty($menuPerms) && $user->hasAnyPermission($menuPerms)) {
            return true;
        }

        // 3. Jika menu bersifat publik tanpa konfigurasi permission apapun
        if ($menu->permissions->count() == 0 && (!isset($menu->subMenus) || $menu->subMenus->count() == 0)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getTodayDateInfo')) {
    /**
     * Mendapatkan informasi tanggal Masehi dan Hijriyah hari ini berdasarkan kalender pendidikan.
     *
     * @param string|null $tanggal
     * @return array
     */
    function getTodayDateInfo($tanggal = null)
    {
        $date = $tanggal ? \Carbon\Carbon::parse($tanggal) : \Carbon\Carbon::now();
        $dateString = $date->toDateString();

        // Cari data bulan hijriyah di database yang mencakup tanggal ini
        $bulan = \App\Models\BulanHijriyah::with('tahunPelajaran')
            ->where('tanggal_mulai_masehi', '<=', $dateString)
            ->where('tanggal_selesai_masehi', '>=', $dateString)
            ->first();

        // Jika tidak ditemukan rentang tanggal persis, fallback ke bulan hijriyah yang sedang aktif
        if (!$bulan) {
            $bulan = \App\Models\BulanHijriyah::with('tahunPelajaran')
                ->where('is_active', true)
                ->first();
        }

        $hijriString = null;
        if ($bulan) {
            $diffDays = (int) \Carbon\Carbon::parse($bulan->tanggal_mulai_masehi)->diffInDays($date, false);
            $hijriDay = $diffDays >= 0 ? $diffDays + 1 : 1;
            if ($hijriDay > 30) $hijriDay = 30;

            $tahunHijri = $bulan->tahun_hijriyah;
            if (!$tahunHijri && $bulan->tahunPelajaran) {
                $tahunHijri = explode('/', $bulan->tahunPelajaran->nama_hijriyah)[0] ?? '';
            }

            $hijriString = "{$hijriDay} {$bulan->nama_bulan}" . ($tahunHijri ? " {$tahunHijri} H" : ' H');
        } else {
            // Fallback tahun pelajaran aktif jika bulan belum di-set
            $tp = \App\Models\TahunPelajaran::where('is_active', true)->first();
            if ($tp) {
                $hijriString = "T.P. {$tp->nama_hijriyah} H";
            }
        }

        // Format nama hari dan tanggal masehi bahasa Indonesia
        $hariArray = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
        $bulanArray = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $namaHari = $hariArray[$date->dayOfWeek];
        $namaBulan = $bulanArray[$date->month];
        $masehiString = "{$namaHari}, {$date->day} {$namaBulan} {$date->year}";
        $masehiCompact = "{$date->day} " . substr($namaBulan, 0, 3) . " {$date->year}";

        return [
            'masehi'         => $masehiString,
            'masehi_compact' => $masehiCompact,
            'hijri'          => $hijriString,
            'nama_hari'      => $namaHari,
            'tanggal'        => $date->day,
            'bulan'          => $namaBulan,
            'tahun'          => $date->year,
        ];
    }
}

if (!function_exists('terbilang')) {
    /**
     * Mengubah angka menjadi teks terbilang bahasa Indonesia.
     *
     * @param int|float $angka
     * @return string
     */
    function terbilang($angka)
    {
        $angka = abs((float) $angka);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($angka < 12) {
            $temp = ' ' . ($huruf[(int) $angka] ?? '');
        } else if ($angka < 20) {
            $temp = terbilang($angka - 10) . ' Belas';
        } else if ($angka < 100) {
            $temp = terbilang((int) ($angka / 10)) . ' Puluh' . terbilang($angka % 10);
        } else if ($angka < 200) {
            $temp = ' Seratus' . terbilang($angka - 100);
        } else if ($angka < 1000) {
            $temp = terbilang((int) ($angka / 100)) . ' Ratus' . terbilang($angka % 100);
        } else if ($angka < 2000) {
            $temp = ' Seribu' . terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $temp = terbilang((int) ($angka / 1000)) . ' Ribu' . terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            $temp = terbilang((int) ($angka / 1000000)) . ' Juta' . terbilang($angka % 1000000);
        } else if ($angka < 1000000000000) {
            $temp = terbilang((int) ($angka / 1000000000)) . ' Miliar' . terbilang(fmod($angka, 1000000000));
        } else if ($angka < 1000000000000000) {
            $temp = terbilang((int) ($angka / 1000000000000)) . ' Triliun' . terbilang(fmod($angka, 1000000000000));
        }

        return trim($temp);
    }
}

if (!function_exists('getTahunPelajaranAktif')) {
    /**
     * Mengambil Tahun Pelajaran yang sedang aktif.
     *
     * @return \App\Models\TahunPelajaran|null
     */
    function getTahunPelajaranAktif()
    {
        return \App\Models\TahunPelajaran::where('is_active', true)->first()
            ?? \App\Models\TahunPelajaran::latest()->first();
    }
}

if (!function_exists('mask_nik')) {
    /**
     * Memformat NIK agar hanya sebagian digit yang tampil di antarmuka (contoh: 3201************).
     *
     * @param string|null $nik
     * @return string
     */
    function mask_nik($nik = null)
    {
        if (empty($nik)) {
            return '-';
        }
        $nik = (string) $nik;
        $len = strlen($nik);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($nik, 0, 4) . str_repeat('*', $len - 4);
    }
}

if (!function_exists('mask_kk')) {
    /**
     * Memformat Nomor Kartu Keluarga agar hanya sebagian digit yang tampil di antarmuka (contoh: 3201************).
     *
     * @param string|null $noKk
     * @return string
     */
    function mask_kk($noKk = null)
    {
        if (empty($noKk)) {
            return '-';
        }
        $noKk = (string) $noKk;
        $len = strlen($noKk);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($noKk, 0, 4) . str_repeat('*', $len - 4);
    }
}

if (!function_exists('hash_sensitive')) {
    /**
     * Menghitung hash HMAC-SHA256 blind index untuk pencarian dan validasi keunikan field terenkripsi.
     *
     * @param string|null $value
     * @return string|null
     */
    function hash_sensitive($value = null)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $key = config('app.key') ?: 'mdt_secret_hash_key';
        return hash_hmac('sha256', (string) $value, $key);
    }
}
