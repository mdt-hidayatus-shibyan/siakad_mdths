<nav class="flex-1 overflow-y-auto py-2.5 px-2.5 pb-12 md:pb-4 space-y-1 custom-scrollbar">

    @php
        $isMenuActive = function ($url) {
            if (!$url || $url === '#') {
                return false;
            }

            // Explicit alias map for composite modules / redirect-based submenus
            $aliases = [
                // Tagihan Wali Murid Module
                'tagihan-wali.terbitkan-index' => [
                    'tagihan-wali.terbitkan-index',
                    'tagihan-wali.terbitkan',
                    'tagihan-wali.hapus-massal',
                    'tagihan-wali.hapus-semua',
                    'tagihan-wali.destroy',
                ],
                'tagihan-wali.kasir' => [
                    'tagihan-wali.kasir',
                    'tagihan-wali.kasir-leger',
                    'tagihan-wali.kasir-leger.proses',
                    'tagihan-wali.bayar',
                    'tagihan-wali.batal',
                    'tagihan-wali.cetak-kwitansi',
                    'tagihan-wali.detail',
                ],
                'tagihan-wali.laporan' => ['tagihan-wali.laporan', 'tagihan-wali.cetak-rekap'],
                'pengaturan-versi.index' => [
                    'pengaturan-versi.index',
                    'pengaturan-versi.riwayat',
                    'pengaturan-versi.create',
                    'pengaturan-versi.update',
                    'pengaturan-versi.*',
                    'riwayat-versi',
                ],
                'surat-keluar.index' => [
                    'surat-keluar.index',
                    'surat-keluar.create',
                    'surat-keluar.show',
                    'surat-keluar.edit',
                    'surat-keluar.cetak',
                    'surat-keluar.*',
                ],
            ];

            // If an explicit alias mapping is defined, it is strictly authoritative
            if (isset($aliases[$url])) {
                foreach ($aliases[$url] as $pattern) {
                    if (request()->routeIs($pattern)) {
                        return true;
                    }
                }
                return false;
            }

            // 1. Direct route exact match
            if (request()->routeIs($url)) {
                return true;
            }

            // 2. Suffix "-main" pattern matching
            if (str_ends_with($url, '-main')) {
                $base = substr($url, 0, -5);
                if (request()->routeIs($base) || request()->routeIs($base . '.*') || request()->routeIs($base . '-*')) {
                    return true;
                }
            }

            // 3. Match route prefix if URL ends with .index (e.g. 'tabungan.rekening.index' -> 'tabungan.rekening.*')
            if (str_ends_with($url, '.index')) {
                $prefix = substr($url, 0, -6);
                if (request()->routeIs($prefix . '.*')) {
                    return true;
                }
            }

            // 4. Direct URL path match
            $path = trim($url, '/');
            if ($path) {
                if (request()->is($path)) {
                    return true;
                }
                if (!str_contains($url, 'dashboard') && request()->is($path . '/*')) {
                    return true;
                }
            }

            // 5. Resolve Route URI
            if (\Illuminate\Support\Facades\Route::has($url)) {
                try {
                    $routeUri = \Illuminate\Support\Facades\Route::getRoutes()->getByName($url)?->uri();
                    if ($routeUri) {
                        $cleanUri = trim(preg_replace('/\{.*?\}/', '', $routeUri), '/');
                        if ($cleanUri) {
                            if (request()->is($cleanUri)) {
                                return true;
                            }
                            if (!str_contains($url, 'dashboard') && request()->is($cleanUri . '/*')) {
                                return true;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                }
            }

            return false;
        };
    @endphp

    {{-- Ambil data dari database yang digrup berdasarkan kategori --}}
    @foreach (menus() as $category => $mainMenus)
        @php
            // Cek apakah ada minimal 1 menu di kategori ini yang bisa diakses user
            $hasCategoryAccess = false;
            foreach ($mainMenus as $m) {
                if (canAccessMenu($m)) {
                    $hasCategoryAccess = true;
                    break;
                }
            }
        @endphp

        {{-- Jika punya akses, render kategorinya --}}
        @if ($hasCategoryAccess)
            {{-- TIPE 1: KATEGORI / TITLE --}}
            @if (!empty($category))
                <div
                    class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mt-1">
                    {{ $category }}
                </div>
            @endif

            {{-- Looping Menu Utama di dalam Kategori --}}
            @foreach ($mainMenus as $menu)
                {{-- Lewati (hide) jika tidak punya akses ke menu ini --}}
                @if (!canAccessMenu($menu))
                    @continue
                @endif

                @if ($menu->subMenus->count() == 0)
                    {{-- TIPE 2: LINK TUNGGAL --}}
                    @php
                        $isActive = $isMenuActive($menu->url);
                        $link = Route::has($menu->url) ? route($menu->url) : url($menu->url);
                    @endphp

                    <a href="{{ $menu->url == '#' ? '#' : $link }}"
                        class="flex items-center px-3 min-h-[40px] rounded-2xl transition-all duration-200 font-bold text-[13px] outline-none group
                        {{ $isActive
                            ? 'bg-primary/10 dark:bg-primary-dark/15 text-primary dark:text-primary-dark border border-primary/20 dark:border-primary-dark/25 shadow-xs'
                            : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100/80 dark:hover:bg-zinc-800/60 hover:text-zinc-900 dark:hover:text-white border border-transparent' }}">
                        <i
                            class="bi {{ $menu->icon ?? 'bi-circle' }} text-base mr-2.5 w-5 text-center transition-transform group-hover:scale-110"></i>
                        <span class="truncate">{{ $menu->name }}</span>
                    </a>
                @else
                    {{-- TIPE 3: DROPDOWN (MEMILIKI SUB-MENU) --}}
                    @php
                        $isActive = false;
                        foreach ($menu->subMenus as $sub) {
                            if ($isMenuActive($sub->url)) {
                                $isActive = true;
                                break;
                            }
                        }
                    @endphp

                    {{-- Menggunakan Alpine.js untuk fitur toggle dropdown --}}
                    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="space-y-0.5">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-3 min-h-[40px] rounded-2xl transition-all duration-200 font-bold text-[13px] outline-none group
                            {{ $isActive
                                ? 'bg-zinc-100 dark:bg-zinc-900/80 text-zinc-900 dark:text-white border border-zinc-200/60 dark:border-zinc-800/60'
                                : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100/80 dark:hover:bg-zinc-800/60 hover:text-zinc-900 dark:hover:text-white border border-transparent' }}">

                            <div class="flex items-center">
                                <i
                                    class="bi {{ $menu->icon ?? 'bi-folder' }} text-base mr-2.5 w-5 text-center transition-transform group-hover:scale-110"></i>
                                <span class="truncate">{{ $menu->name }}</span>
                            </div>

                            <i class="bi bi-chevron-down transition-transform duration-300 text-[10px] text-zinc-400 dark:text-zinc-500"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        {{-- Isi Dropdown / Children --}}
                        <div x-show="open" x-collapse class="space-y-0.5 pl-8 pr-1 pt-0.5"
                            style="display: {{ $isActive ? 'block' : 'none' }};">

                            @foreach ($menu->subMenus as $child)
                                {{-- Lewati sub-menu jika tidak ada akses --}}
                                @if (!canAccessMenu($child))
                                    @continue
                                @endif

                                @php
                                    $isChildActive = $isMenuActive($child->url);
                                    $childLink = Route::has($child->url) ? route($child->url) : url($child->url);
                                @endphp

                                <a href="{{ $child->url == '#' ? '#' : $childLink }}"
                                    class="flex items-center px-3 min-h-[38px] rounded-xl transition-all duration-200 text-[12px] font-semibold outline-none
                                    {{ $isChildActive
                                        ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark font-bold'
                                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/80 dark:hover:bg-zinc-800/40' }}">

                                    <span class="truncate">{{ $child->name }}</span>

                                    @if ($isChildActive)
                                        <!-- Indikator aktif M3 -->
                                        <div class="ml-auto w-1.5 h-1.5 rounded-full bg-primary dark:bg-primary-dark">
                                        </div>
                                    @endif
                                </a>
                            @endforeach

                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    @endforeach

    <!-- Spacer Bawah untuk HP (Safe Area) -->
    <div class="h-20 md:h-6 shrink-0 w-full pointer-events-none" aria-hidden="true"></div>
</nav>
