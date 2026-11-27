<div
    class="flex gap-1.5 p-1 h-11 bg-zinc-100/80 dark:bg-zinc-900/80 rounded-xl w-full xl:w-max overflow-x-auto custom-scrollbar border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">

    {{-- TAB 1: PANTAU PROGRES --}}
    @php $isProgres = request()->routeIs('nilai-ujian.index'); @endphp
    <a href="{{ route('nilai-ujian.index') }}"
        class="{{ $isProgres ? 'bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 shadow-2xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50' }} h-full px-3.5 md:px-4 rounded-lg text-xs font-black transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-speedometer text-sm"></i>
        <span>Pantau Progres</span>
    </a>

    {{-- TAB 2: INPUT NILAI SATUAN --}}
    @php $isInputNilai = request()->routeIs('nilai-ujian.input-nilai'); @endphp
    <a href="{{ route('nilai-ujian.input-nilai') }}"
        class="{{ $isInputNilai ? 'bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 shadow-2xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50' }} h-full px-3.5 md:px-4 rounded-lg text-xs font-black transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-input-cursor-text text-sm"></i>
        <span>Input Satuan</span>
    </a>

    {{-- TAB 3: INPUT LEGER MASSAL --}}
    @php $isInputLeger = request()->routeIs('nilai-ujian.input-leger'); @endphp
    <a href="{{ route('nilai-ujian.input-leger') }}"
        class="{{ $isInputLeger ? 'bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 shadow-2xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50' }} h-full px-3.5 md:px-4 rounded-lg text-xs font-black transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-file-spreadsheet text-sm"></i>
        <span>Input Leger Massal</span>
    </a>

    {{-- TAB 4: LAPORAN LEGER RANKING --}}
    @php $isLeger = request()->routeIs('nilai-ujian.laporan-leger'); @endphp
    <a href="{{ route('nilai-ujian.laporan-leger') }}"
        class="{{ $isLeger ? 'bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 shadow-2xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50' }} h-full px-3.5 md:px-4 rounded-lg text-xs font-black transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-file-earmark-bar-graph text-sm"></i>
        <span>Laporan Leger</span>
    </a>

    {{-- TAB 5: REKAP PER MATA PELAJARAN --}}
    @php $isRekapMapel = request()->routeIs('nilai-ujian.rekap-mapel*'); @endphp
    <a href="{{ route('nilai-ujian.rekap-mapel') }}"
        class="{{ $isRekapMapel ? 'bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 shadow-2xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50' }} h-full px-3.5 md:px-4 rounded-lg text-xs font-black transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-journal-bookmark-fill text-sm"></i>
        <span>Rekap Per Mapel</span>
    </a>
</div>

