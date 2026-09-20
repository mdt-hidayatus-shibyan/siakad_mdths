@section('title', 'Riwayat Versi Aplikasi')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Changelog & Release Notes
                </span>
                <span class="text-xs text-zinc-400">•</span>
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                    Total {{ $totalVersions }} Rilis
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Riwayat Versi Aplikasi
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Dokumentasi perjalanan rilis, fitur baru, perbaikan bug, dan spesifikasi teknologi ekosistem MDTHS.
            </p>
        </div>

        <!-- Tombol Aksi Header -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('pengaturan-versi.index', ['app' => $appType === 'all' ? 'ustadz' : $appType]) }}"
                class="px-4 py-2.5 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs transition-all flex items-center gap-2 border border-zinc-200/80 dark:border-zinc-700/80">
                <i class="bi bi-sliders text-sm"></i>
                <span>Editor Versi</span>
            </a>
            <a href="{{ route('pengaturan-versi.create', ['app' => $appType === 'all' ? 'ustadz' : $appType]) }}"
                class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs transition-all shadow-lg shadow-emerald-600/30 flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-sm"></i>
                <span>Tambah Versi Baru</span>
            </a>
        </div>
    </div>

    <!-- NOTIFIKASI SUCCESS -->
    @if (session('success'))
        <div
            class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center gap-3 text-xs font-bold animate-fade-in">
            <i class="bi bi-check-circle-fill text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- STATISTIK RINGKASAN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4 mb-6 md:mb-8">
        <!-- Total Rilis -->
        <div
            class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span
                        class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Total
                        Rilis</span>
                    <span
                        class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white">{{ $totalVersions }}</span>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 flex items-center justify-center text-lg">
                    <i class="bi bi-archive-fill"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                <span>Seluruh platform</span>
            </div>
        </div>

        <!-- Ustadz Mobile -->
        <div
            class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span
                        class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-1">App
                        Ustadz</span>
                    <span
                        class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white">{{ $ustadzCount }}</span>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </div>
            <div
                class="mt-2 text-[11px] font-semibold text-emerald-600/80 dark:text-emerald-400/80 flex items-center gap-1">
                <span>Versi Mobile Ustadz</span>
            </div>
        </div>

        <!-- Murid Mobile -->
        <div
            class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span
                        class="text-[10px] font-black uppercase tracking-wider text-sky-600 dark:text-sky-400 block mb-1">App
                        Murid</span>
                    <span
                        class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white">{{ $muridCount }}</span>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] font-semibold text-sky-600/80 dark:text-sky-400/80 flex items-center gap-1">
                <span>Versi Wali & Murid</span>
            </div>
        </div>

        <!-- Web SIAKAD -->
        <div
            class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span
                        class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block mb-1">Web
                        SIAKAD</span>
                    <span
                        class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white">{{ $webCount }}</span>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg">
                    <i class="bi bi-globe2"></i>
                </div>
            </div>
            <div
                class="mt-2 text-[11px] font-semibold text-purple-600/80 dark:text-purple-400/80 flex items-center gap-1">
                <span>Backend & Portal</span>
            </div>
        </div>
    </div>

    <!-- FILTER & PENCARIAN -->
    <div class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 mb-6 md:mb-8">
        <form action="{{ route('pengaturan-versi.riwayat') }}" method="GET"
            class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Filter Platform Tabs -->
            <div
                class="flex flex-wrap items-center gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-900/90 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 w-full md:w-auto">
                <a href="{{ route('pengaturan-versi.riwayat', ['app' => 'all', 'q' => $search]) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $appType === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Semua ({{ $totalVersions }})</span>
                </a>
                <a href="{{ route('pengaturan-versi.riwayat', ['app' => 'ustadz', 'q' => $search]) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $appType === 'ustadz' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Ustadz ({{ $ustadzCount }})</span>
                </a>
                <a href="{{ route('pengaturan-versi.riwayat', ['app' => 'murid', 'q' => $search]) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $appType === 'murid' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Murid ({{ $muridCount }})</span>
                </a>
                <a href="{{ route('pengaturan-versi.riwayat', ['app' => 'web', 'q' => $search]) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $appType === 'web' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-globe2"></i>
                    <span>Web SIAKAD ({{ $webCount }})</span>
                </a>
            </div>

            <!-- Form Pencarian -->
            <div class="relative w-full md:w-80">
                <input type="hidden" name="app" value="{{ $appType }}">
                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Cari versi, build, atau fitur..."
                    class="m3-input-glass w-full pl-9 pr-8 text-xs font-semibold">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                @if ($search)
                    <a href="{{ route('pengaturan-versi.riwayat', ['app' => $appType]) }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TIMELINE LIST RIWAYAT VERSI -->
    @if ($versions->isEmpty())
        <div
            class="m3-glass-card p-10 sm:p-14 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 text-center flex flex-col items-center justify-center">
            <div
                class="w-16 h-16 rounded-3xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 flex items-center justify-center text-3xl mb-4">
                <i class="bi bi-journal-x"></i>
            </div>
            <h3 class="text-base font-black text-zinc-800 dark:text-zinc-200 mb-1">
                Tidak ada data riwayat versi
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-sm mb-5">
                @if ($search)
                    Pencarian dengan kata kunci "<b>{{ $search }}</b>" tidak menemukan hasil rilis.
                @else
                    Belum ada catatan rilis versi yang terdaftar pada kategori ini.
                @endif
            </p>
            <a href="{{ route('pengaturan-versi.create', ['app' => $appType === 'all' ? 'ustadz' : $appType]) }}"
                class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all flex items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Buat Catatan Rilis Pertama</span>
            </a>
        </div>
    @else
        <div
            class="space-y-6 md:space-y-8 relative before:absolute before:inset-0 before:left-4 sm:before:left-6 before:w-0.5 before:bg-gradient-to-b before:from-emerald-500 before:via-zinc-300 before:to-transparent dark:before:via-zinc-800 before:hidden md:before:block">
            @foreach ($versions as $v)
                @php
                    $isUstadz = $v->app_type === 'ustadz';
                    $isMurid = $v->app_type === 'murid';
                    $isWeb = $v->app_type === 'web';

                    $appBadgeClass = $isUstadz
                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
                        : ($isMurid
                            ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20'
                            : 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20');

                    $appBadgeIcon = $isUstadz
                        ? 'bi-person-badge-fill'
                        : ($isMurid
                            ? 'bi-mortarboard-fill'
                            : 'bi-globe2');

                    $appLabel = $isUstadz ? 'Aplikasi Ustadz' : ($isMurid ? 'Aplikasi Murid' : 'Web SIAKAD MDTHS');
                @endphp

                <div class="relative md:pl-12 group">
                    <!-- Point Indicator on Timeline (Desktop) -->
                    <div
                        class="hidden md:flex absolute left-4 sm:left-6 -translate-x-1/2 top-6 w-7 h-7 rounded-full items-center justify-center border-2 transition-all duration-300 z-10 {{ $v->is_latest ? 'bg-emerald-600 border-emerald-300 text-white ring-4 ring-emerald-500/20' : 'bg-white dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700 text-zinc-500 group-hover:border-emerald-500' }}">
                        @if ($v->is_latest)
                            <i class="bi bi-star-fill text-[11px]"></i>
                        @else
                            <i class="bi {{ $appBadgeIcon }} text-[11px]"></i>
                        @endif
                    </div>

                    <!-- CARD ITEM VERSI -->
                    <div
                        class="m3-glass-card rounded-3xl border {{ $v->is_latest ? 'border-emerald-500/40 shadow-lg shadow-emerald-600/5 ring-1 ring-emerald-500/20' : 'border-zinc-200/80 dark:border-zinc-800' }} p-5 sm:p-6 md:p-7 transition-all duration-200">

                        <!-- CARD HEADER -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 pb-4 border-b border-zinc-100 dark:border-zinc-800/80">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Platform Badge -->
                                <span
                                    class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border flex items-center gap-1.5 {{ $appBadgeClass }}">
                                    <i class="bi {{ $appBadgeIcon }}"></i>
                                    {{ $appLabel }}
                                </span>

                                <!-- Semantic Version Badge -->
                                <span
                                    class="px-3 py-1 rounded-xl text-xs font-black bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 flex items-center gap-1 shadow-xs">
                                    <span>v{{ $v->version }}</span>
                                    <span class="opacity-60 text-[10px]">#{{ $v->build_number }}</span>
                                </span>

                                <!-- Status Badge -->
                                @if ($v->is_latest)
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500 text-white shadow-xs flex items-center gap-1 animate-pulse">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Versi Utama Aktif
                                    </span>
                                @elseif ($v->status_badge)
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                        {{ $v->status_badge }}
                                    </span>
                                @endif
                            </div>

                            <!-- Tanggal & Periode Rilis -->
                            <div class="flex items-center gap-2 text-xs font-bold text-zinc-500 dark:text-zinc-400">
                                <i class="bi bi-calendar3 text-zinc-400"></i>
                                <span>{{ $v->release_date }}</span>
                            </div>
                        </div>

                        <!-- SUBTITLE & TITLE -->
                        <div class="mt-4 mb-5">
                            <h3
                                class="text-base sm:text-lg font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span>{{ $v->app_title ?? $appLabel }}</span>
                                @if ($v->release_subtitle)
                                    <span class="text-xs sm:text-sm font-semibold text-zinc-500 dark:text-zinc-400">•
                                        {{ $v->release_subtitle }}</span>
                                @endif
                            </h3>
                            @if ($v->app_subtitle)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $v->app_subtitle }}</p>
                            @endif
                        </div>

                        <!-- FITUR BARU & PERBAIKAN GRID -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 mb-5">

                            <!-- FITUR BARU (NEW FEATURES) -->
                            <div
                                class="p-4 sm:p-4.5 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/5 border border-emerald-500/20">
                                <div
                                    class="flex items-center justify-between mb-3 pb-2 border-b border-emerald-500/10">
                                    <span
                                        class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="bi bi-stars text-emerald-500"></i>
                                        Fitur Baru & Pembaruan
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                        {{ count($v->new_features ?? []) }} Item
                                    </span>
                                </div>

                                @if (!empty($v->new_features) && is_array($v->new_features))
                                    <ul class="space-y-2.5">
                                        @foreach ($v->new_features as $feat)
                                            <li class="text-xs flex items-start gap-2">
                                                <i
                                                    class="bi bi-check2-circle text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0 text-sm"></i>
                                                <div class="leading-relaxed">
                                                    <span
                                                        class="font-bold text-zinc-800 dark:text-zinc-200 block">{{ $feat['title'] ?? '-' }}</span>
                                                    @if (!empty($feat['description']))
                                                        <span
                                                            class="text-[11px] text-zinc-500 dark:text-zinc-400 block mt-0.5">{{ $feat['description'] }}</span>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs italic text-zinc-400 dark:text-zinc-500">Tidak ada penambahan
                                        modul fitur besar pada rilis ini.</p>
                                @endif
                            </div>

                            <!-- PERBAIKAN & PENYEMPURNAAN (IMPROVEMENTS) -->
                            <div
                                class="p-4 sm:p-4.5 rounded-2xl bg-sky-500/5 dark:bg-sky-500/5 border border-sky-500/20">
                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-sky-500/10">
                                    <span
                                        class="text-xs font-black text-sky-700 dark:text-sky-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="bi bi-wrench-adjustable text-sky-500"></i>
                                        Perbaikan & Optimasi
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-500/20">
                                        {{ count($v->improvements ?? []) }} Item
                                    </span>
                                </div>

                                @if (!empty($v->improvements) && is_array($v->improvements))
                                    <ul class="space-y-2.5">
                                        @foreach ($v->improvements as $imp)
                                            <li class="text-xs flex items-start gap-2">
                                                <i
                                                    class="bi bi-arrow-right-short text-sky-600 dark:text-sky-400 mt-0.5 shrink-0 text-sm"></i>
                                                <div class="leading-relaxed">
                                                    <span
                                                        class="font-bold text-zinc-800 dark:text-zinc-200 block">{{ $imp['title'] ?? '-' }}</span>
                                                    @if (!empty($imp['description']))
                                                        <span
                                                            class="text-[11px] text-zinc-500 dark:text-zinc-400 block mt-0.5">{{ $imp['description'] }}</span>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs italic text-zinc-400 dark:text-zinc-500">Rilis ini berfokus pada
                                        pembaruan arsitektur dan stabilitas sistem.</p>
                                @endif
                            </div>

                        </div>

                        <!-- TECH STACKS & PENGEMBANG FOOTER -->
                        <div
                            class="pt-4 border-t border-zinc-100 dark:border-zinc-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4">

                            <!-- Tech Stacks -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span
                                    class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 mr-1">Teknologi:</span>
                                @if (!empty($v->tech_stacks) && is_array($v->tech_stacks))
                                    @foreach ($v->tech_stacks as $tech)
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800/90 text-zinc-600 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-700/80">
                                            {{ $tech }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-xs text-zinc-400 font-medium">Flutter, Dart, Laravel,
                                        MySQL</span>
                                @endif
                            </div>

                            <!-- Info Pengembang & Tombol Aksi -->
                            <div class="flex flex-wrap items-center justify-between md:justify-end gap-3">
                                <div class="text-right">
                                    <span class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300 block">
                                        {{ $v->dev_name ?? 'Tim IT & Pengembang' }}
                                    </span>
                                    <span class="text-[10px] font-medium text-zinc-400 block">
                                        {{ $v->dev_role ?? 'Lead Developer' }} • {{ $v->dev_institution ?? 'MDTHS' }}
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="flex items-center gap-1.5 pl-2 border-l border-zinc-200 dark:border-zinc-800">
                                    @if (!$v->is_latest)
                                        <form action="{{ route('pengaturan-versi.set-active', $v->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Jadikan Versi Utama / Aktif"
                                                class="px-2.5 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-500/10 hover:text-emerald-600 text-zinc-600 dark:text-zinc-400 text-xs font-bold transition-all border border-zinc-200/60 dark:border-zinc-700/60 cursor-pointer flex items-center gap-1">
                                                <i class="bi bi-star"></i>
                                                <span class="hidden sm:inline">Set Utama</span>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('pengaturan-versi.index', ['id' => $v->id]) }}"
                                        title="Edit Catatan Versi"
                                        class="px-2.5 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-1">
                                        <i class="bi bi-pencil-square"></i>
                                        <span class="hidden sm:inline">Edit</span>
                                    </a>

                                    <form action="{{ route('pengaturan-versi.destroy', $v->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan rilis versi {{ $v->version }}?')"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Versi"
                                            class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all cursor-pointer">
                                            <i class="bi bi-trash-fill text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-app-layout>
