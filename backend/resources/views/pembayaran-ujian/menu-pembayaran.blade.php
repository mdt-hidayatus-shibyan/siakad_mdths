<div
    class="m3-glass-card p-1.5 flex flex-wrap sm:flex-nowrap gap-1.5 w-full md:w-max overflow-x-auto custom-scrollbar shadow-2xs">
    {{-- TAB 1: PEMBAYARAN LEGER --}}
    @php $isLeger = request()->routeIs('pembayaran-ujian.index'); @endphp
    <a href="{{ route('pembayaran-ujian.index', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isLeger ? 'bg-amber-600 text-white shadow-2xs font-black' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold' }} h-9 px-4 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 sm:flex-none shrink-0">
        <i class="bi bi-grid-1x2-fill text-xs"></i>
        <span>Pembayaran</span>
    </a>

    {{-- TAB 2: DASHBOARD KODE TAGIHAN --}}
    @php $isDashboard = request()->routeIs('pembayaran-ujian.dashboard*'); @endphp
    <a href="{{ route('pembayaran-ujian.dashboard', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isDashboard ? 'bg-emerald-600 text-white shadow-2xs font-black' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold' }} h-9 px-4 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 sm:flex-none shrink-0">
        <i class="bi bi-bar-chart-line-fill text-xs"></i>
        <span>Dashboard Kode Tagihan</span>
    </a>

    {{-- TAB 3: LAPORAN TRANSAKSI --}}
    @php $isLaporan = request()->routeIs('pembayaran-ujian.laporan'); @endphp
    <a href="{{ route('pembayaran-ujian.laporan', array_filter(['tahun_id' => request('tahun_id')])) }}"
        class="{{ $isLaporan ? 'bg-sky-600 text-white shadow-2xs font-black' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 font-bold' }} h-9 px-4 rounded-xl text-xs transition-all whitespace-nowrap flex items-center justify-center gap-1.5 flex-1 sm:flex-none shrink-0">
        <i class="bi bi-journal-check text-xs"></i>
        <span>Laporan Harian</span>
    </a>
</div>
