@section('title', 'Rombongan Belajar')

<x-app-layout>
    <!-- Header Section (Compact M3) -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative z-20">
        <div>
            <h2
                class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                Rombongan Belajar
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                Kelola data rombongan belajar, pembagian kelas, dan anggota murid.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">

            <!-- Search & Filter Form -->
            <form action="{{ route('rombongan-belajar.index') }}" method="GET"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-auto min-w-[170px] group/filter">
                    <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $filterTp == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} H | {{ $tp->nama_masehi }} M
                            </option>
                        @endforeach
                    </select>
                    <!-- Ikon Chevron Custom M3 -->
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400 group-focus-within/filter:text-primary dark:group-focus-within/filter:text-primary-dark transition-colors">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-72 group/search">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-300 text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark">
                        <i class="bi bi-search text-sm"></i>
                    </div>

                    <!-- Input Pencarian -->
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ruangan..."
                        class="m3-input-glass w-full !pl-10 !pr-10">

                    <!-- Tombol Reset -->
                    @if (request('search') || request('tahun_pelajaran_id'))
                        <a href="{{ route('rombongan-belajar.index') }}"
                            class="absolute inset-y-0 right-0 w-10 h-10 flex items-center justify-center text-zinc-400 hover:text-red-600 dark:text-zinc-500 dark:hover:text-red-400 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 rounded-xl transition-colors duration-200 outline-none"
                            title="Reset Filter">
                            <i class="bi bi-x-lg text-xs font-bold"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Dropdown Tombol Cetak Rincian Jumlah Murid Aktif -->
            <div class="relative w-full sm:w-auto" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open"
                    class="w-full sm:w-auto h-10 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer shrink-0">
                    <i class="bi bi-printer-fill text-sm"></i>
                    <span>Cetak Rincian</span>
                    <i class="bi bi-chevron-down text-[10px] transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak style="display: none;"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                    class="absolute z-50 w-64 origin-top-right right-0 top-full mt-2 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-zinc-200/90 dark:border-zinc-800/90 rounded-2xl shadow-xl dark:shadow-none overflow-hidden p-1.5 space-y-0.5"
                    @click="open = false">

                    <a href="{{ route('rombongan-belajar.cetak-rincian', ['tahun_pelajaran_id' => $filterTp, 'mode' => 'semua']) }}"
                        target="_blank"
                        class="m3-dropdown-item hover:!text-emerald-600 dark:hover:!text-emerald-400 hover:!bg-emerald-50 dark:hover:!bg-emerald-950/30">
                        <i class="bi bi-file-earmark-bar-graph-fill text-emerald-500 text-sm"></i>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs">Cetak Lengkap</span>
                            <span class="text-[10px] text-zinc-400 font-medium">Berdasarkan Ruangan &amp; Level</span>
                        </div>
                    </a>

                    <div class="m3-dropdown-divider"></div>

                    <a href="{{ route('rombongan-belajar.cetak-rincian', ['tahun_pelajaran_id' => $filterTp, 'mode' => 'ruangan']) }}"
                        target="_blank"
                        class="m3-dropdown-item hover:!text-sky-600 dark:hover:!text-sky-400 hover:!bg-sky-50 dark:hover:!bg-sky-950/30">
                        <i class="bi bi-door-open-fill text-sky-500 text-sm"></i>
                        <span class="font-bold text-xs">Berdasarkan Ruangan</span>
                    </a>

                    <a href="{{ route('rombongan-belajar.cetak-rincian', ['tahun_pelajaran_id' => $filterTp, 'mode' => 'level']) }}"
                        target="_blank"
                        class="m3-dropdown-item hover:!text-amber-600 dark:hover:!text-amber-400 hover:!bg-amber-50 dark:hover:!bg-amber-950/30">
                        <i class="bi bi-layers-fill text-amber-500 text-sm"></i>
                        <span class="font-bold text-xs">Berdasarkan Level</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="flex flex-col gap-3 relative z-10">
        @include('rombongan-belajar.list', ['ruangans' => $ruangans])
    </div>
</x-app-layout>
