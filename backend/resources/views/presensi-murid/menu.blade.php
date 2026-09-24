<div
    class="flex items-center gap-1 p-1 h-12 m3-glass-card rounded-2xl w-full xl:w-max overflow-x-auto custom-scrollbar shadow-2xs">

    {{-- TAB 1: HARIAN (INPUT) --}}
    @php $isharian = request()->routeIs('presensi-murid.index'); @endphp
    <a href="{{ route('presensi-murid.index') }}"
        class="{{ $isharian ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark shadow-xs border border-primary/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-person-check{{ $isharian ? '-fill' : '' }} text-sm"></i>
        <span>Harian</span>
    </a>

    {{-- TAB 2: PROGRES HARIAN (MONITORING) --}}
    @php $isProgres = request()->routeIs('presensi-murid.progres'); @endphp
    <a href="{{ route('presensi-murid.progres') }}"
        class="{{ $isProgres ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shadow-xs border border-emerald-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-activity text-sm"></i>
        <span>Progres Harian</span>
    </a>

    {{-- TAB 3: BULANAN --}}
    @php $isBulanan = request()->routeIs('presensi-murid.bulanan'); @endphp
    <a href="{{ route('presensi-murid.bulanan') }}"
        class="{{ $isBulanan ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 shadow-xs border border-amber-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-calendar-month{{ $isBulanan ? '-fill' : '' }} text-sm"></i>
        <span>Bulanan</span>
    </a>

    {{-- TAB 4: REKAP SEMESTER --}}
    @php $isRekap = request()->routeIs('presensi-murid.rekap') || request()->routeIs('presensi-murid.cetak-rekap'); @endphp
    <a href="{{ route('presensi-murid.rekap') }}"
        class="{{ $isRekap ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 shadow-xs border border-purple-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-file-earmark-bar-graph{{ $isRekap ? '-fill' : '' }} text-sm"></i>
        <span>Rekapitulasi</span>
    </a>

</div>
