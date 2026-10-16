<div
    class="flex items-center gap-1.5 p-1.5 h-11 m3-glass-card !rounded-2xl w-full md:w-max overflow-x-auto custom-scrollbar shadow-2xs">

    {{-- TAB 1: KASIR REGULER --}}
    @php
        $isReguler = request()->routeIs('tagihan-ikhtibar.kasir') || (request()->routeIs('tagihan-ikhtibar.index') && !request()->routeIs('tagihan-ikhtibar.kasir-leger'));
    @endphp
    <a href="{{ route('tagihan-ikhtibar.kasir', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isReguler ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-person-bounding-box text-xs"></i>
        <span>Kasir Reguler</span>
    </a>

    {{-- TAB 2: KASIR LEGER --}}
    @php
        $isLeger = request()->routeIs('tagihan-ikhtibar.kasir-leger');
    @endphp
    <a href="{{ route('tagihan-ikhtibar.kasir-leger', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isLeger ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-grid-1x2-fill text-xs"></i>
        <span>Kasir Leger (Dusun)</span>
    </a>

    {{-- TAB 3: TERBITKAN TAGIHAN --}}
    @php
        $isTerbitkan = request()->routeIs('tagihan-ikhtibar.terbitkan-index');
    @endphp
    <a href="{{ route('tagihan-ikhtibar.terbitkan-index', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isTerbitkan ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-plus-circle-fill text-xs"></i>
        <span>Terbitkan Tagihan</span>
    </a>

    {{-- TAB 4: LAPORAN & REKAP --}}
    @php
        $isLaporan = request()->routeIs('tagihan-ikhtibar.laporan');
    @endphp
    <a href="{{ route('tagihan-ikhtibar.laporan', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isLaporan ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 shadow-2xs font-black' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold border border-transparent' }} h-full px-4 md:px-5 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0 outline-none">
        <i class="bi bi-journal-check text-xs"></i>
        <span>Laporan & Rekap</span>
    </a>
</div>

