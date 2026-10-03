<div
    class="flex items-center gap-1 p-1 h-12 m3-glass-card rounded-2xl w-full xl:w-max overflow-x-auto custom-scrollbar shadow-2xs">

    {{-- TAB 1: INPUT HARIAN --}}
    @php $isHarian = request()->routeIs('pelanggaran-murid.index'); @endphp
    <a href="{{ route('pelanggaran-murid.index') }}"
        class="{{ $isHarian ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark shadow-xs border border-primary/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-pencil-square text-sm"></i>
        <span>Input Harian</span>
    </a>

    {{-- TAB 2: RIWAYAT HARIAN (SEMUA RUANGAN) --}}
    @php $isRiwayat = request()->routeIs('pelanggaran-murid.riwayatHarian'); @endphp
    <a href="{{ route('pelanggaran-murid.riwayatHarian') }}"
        class="{{ $isRiwayat ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 shadow-xs border border-rose-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-clock-history text-sm"></i>
        <span>Riwayat Harian</span>
    </a>

    {{-- TAB 3: KOLEKTIF --}}
    @php $isMassal = request()->routeIs('pelanggaran-murid.massal'); @endphp
    <a href="{{ route('pelanggaran-murid.massal') }}"
        class="{{ $isMassal ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 shadow-xs border border-amber-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-people{{ $isMassal ? '-fill' : '' }} text-sm"></i>
        <span>Kolektif</span>
    </a>

    {{-- TAB 4: KERTAS KERJA --}}
    @php $isAdminMode = request()->routeIs('pelanggaran-murid.adminMode'); @endphp
    <a href="{{ route('pelanggaran-murid.adminMode') }}"
        class="{{ $isAdminMode ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 shadow-xs border border-blue-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-grid-3x2-gap{{ $isAdminMode ? '-fill' : '' }} text-sm"></i>
        <span>Kertas Kerja</span>
    </a>

    {{-- TAB 5: REKAP PERINGKAT --}}
    @php $isRekap = request()->routeIs('pelanggaran-murid.rekap'); @endphp
    <a href="{{ route('pelanggaran-murid.rekap') }}"
        class="{{ $isRekap ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 shadow-xs border border-purple-500/20' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 border border-transparent' }} h-full px-3.5 md:px-4 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 xl:flex-none shrink-0">
        <i class="bi bi-trophy{{ $isRekap ? '-fill' : '' }} text-sm"></i>
        <span>Peringkat</span>
    </a>

</div>

