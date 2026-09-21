<aside id="sidebarAside" aria-label="Sidebar Navigasi"
    class="fixed inset-y-0 left-0 w-[275px] max-w-[85vw] md:w-[260px] h-[100dvh] md:h-screen bg-white/90 dark:bg-black text-zinc-900 dark:text-zinc-200 flex flex-col z-[50] transform -translate-x-full md:translate-x-0 md:relative md:flex flex-shrink-0 transition-all duration-300 ease-in-out border-r border-zinc-200/80 dark:border-zinc-800/80 shadow-2xl md:shadow-none dark:shadow-none backdrop-blur-xl">

    <!-- Header Sidebar (Compact) -->
    <div
        class="p-3.5 flex items-center justify-between relative z-[50] shrink-0 border-b border-zinc-100 dark:border-zinc-900/60">
        <div class="flex items-center space-x-2.5 overflow-hidden">
            <!-- Logo M3 dengan ukuran Compact -->
            <div
                class="w-10 h-10 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 flex items-center justify-center flex-shrink-0 p-1.5 shadow-sm dark:shadow-none">
                <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo MDT Hidayatus Shibyan"
                    class="w-full h-full object-contain" />
            </div>
            <div class="overflow-hidden flex flex-col justify-center">
                <div class="flex items-center gap-1.5">
                    <h1
                        class="font-black text-zinc-900 dark:text-white leading-tight tracking-tight text-[13px] md:text-sm truncate">
                        HIDAYATUS SHIBYAN
                    </h1>
                </div>

                <span
                    class="text-[9px] md:text-[10px] text-primary dark:text-primary-dark uppercase font-extrabold tracking-widest flex items-center gap-1 mt-0.5">
                    @if (session('is_waliruangan'))
                        Wali Ruangan ({{ session('wali_ruangan') }})
                    @else
                        {{ session('akses_sebagai') ?? (auth()->user()?->roles->first()->name ?? 'Admin') }}
                        {{ auth()->user()?->tingkat->kode_tingkat ?? '' }}
                    @endif
                </span>
                <a href="{{ route('pengaturan-versi.riwayat') }}" title="Lihat Riwayat Versi & Catatan Rilis"
                    class="text-zinc-400 hover:text-primary dark:text-zinc-500 dark:hover:text-primary-dark leading-tight tracking-tight text-[11px] md:text-[11px] truncate font-semibold transition-colors inline-flex items-center gap-1 group">
                    <span>v.{{ getAppVersion('web') }}</span>
                    <i class="bi bi-clock-history opacity-0 group-hover:opacity-100 transition-opacity text-[9px]"></i>
                </a>
            </div>
        </div>

        <!-- Touch Target 40px untuk Mobile -->
        <button type="button" onclick="toggleSidebar()" aria-label="Tutup sidebar"
            class="md:hidden min-w-[40px] min-h-[40px] flex items-center justify-center text-zinc-500 dark:text-zinc-400 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-900 dark:hover:bg-zinc-800 rounded-2xl transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-base font-bold" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Navigation Area -->
    <div class="relative z-[50] flex-1 overflow-hidden flex flex-col">
        @include('layouts.partials.navigation')
    </div>
</aside>
