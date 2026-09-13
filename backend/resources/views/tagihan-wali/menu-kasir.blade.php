<div
    class="flex items-center gap-1.5 p-1.5 h-11 m3-glass-card !rounded-2xl w-full md:w-max overflow-x-auto custom-scrollbar shadow-2xs">

    {{-- TAB 1: REGULER --}}
    @php
        $isReguler = request()->routeIs('tagihan-wali.kasir') && !request()->routeIs('tagihan-wali.kasir-leger');
    @endphp
    <a href="{{ route('tagihan-wali.kasir') }}"
        class="{{ $isReguler ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-person-bounding-box text-xs"></i>
        <span>Reguler</span>
    </a>

    {{-- TAB 2: LEGER --}}
    @php
        $isLeger = request()->routeIs('tagihan-wali.kasir-leger');
    @endphp
    <a href="{{ route('tagihan-wali.kasir-leger') }}"
        class="{{ $isLeger ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-grid-1x2-fill text-xs"></i>
        <span>Leger</span>
    </a>

    {{-- TAB 3: LAPORAN --}}
    @php
        $isLaporan = request()->routeIs('tagihan-wali.laporan');
    @endphp
    <a href="{{ route('tagihan-wali.laporan') }}"
        class="{{ $isLaporan ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-journal-check text-xs"></i>
        <span>Laporan</span>
    </a>
</div>
