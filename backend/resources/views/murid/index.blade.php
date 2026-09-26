@section('title', 'Murid')

<x-app-layout>

    <!-- Header Page & Actions -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-30">
        <!-- Titles -->
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Data Murid
            </h2>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 uppercase tracking-wider">
                Kelola data induk murid dan pantau status akademik mereka
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">

            @can('read murid')
                <!-- Tombol Yatim (Soft Purple Glass) -->
                <a href="{{ route('murid.yatim') }}"
                    class="h-10 inline-flex items-center justify-center px-4 rounded-xl md:rounded-2xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/20 text-xs font-black transition-all shadow-2xs active:scale-95 outline-none">
                    <i class="bi bi-heartbreak-fill mr-1.5 text-sm"></i>
                    <span>Data Yatim</span>
                </a>
            @endcan

            @can('create murid')
                <!-- Tombol Tambah (Primary) -->
                <a href="{{ route('murid.create') }}" class="m3-btn-primary h-10 px-5 group/btn">
                    <i class="bi bi-person-plus-fill mr-1 text-sm"></i>
                    <span>Tambah Murid</span>
                </a>
            @endcan

            <!-- Tombol Dropdown Opsi Data -->
            <div class="relative inline-block text-left dropdown-container z-10">

                <button type="button" data-dropdown-toggle="dropdownOpsiData"
                    class="m3-btn-secondary h-10 w-10 !p-0 inline-flex items-center justify-center shadow-2xs"
                    title="Opsi Lainnya">
                    <i class="bi bi-three-dots text-sm"></i>
                </button>

                <div id="dropdownOpsiData" class="m3-dropdown-menu hidden right-0 left-auto min-w-[180px]">

                    <a href="{{ route('kartu-pelajar.index') }}"
                        class="m3-dropdown-item hover:!text-primary dark:hover:!text-primary-dark">
                        <i class="bi bi-person-badge text-base text-primary dark:text-primary-dark"></i>
                        <span>Cetak Kartu Pelajar</span>
                    </a>

                    <a href="#" class="m3-dropdown-item hover:!text-emerald-600 dark:hover:!text-emerald-400">
                        <i class="bi bi-file-earmark-excel text-base text-emerald-600 dark:text-emerald-400"></i>
                        <span>Export Excel</span>
                    </a>

                    <a href="#" class="m3-dropdown-item hover:!text-blue-600 dark:hover:!text-blue-400">
                        <i class="bi bi-printer text-base text-blue-600 dark:text-blue-400"></i>
                        <span>Print Data</span>
                    </a>

                    @can('create murid')
                        <hr class="m3-dropdown-divider">

                        <a href="{{ route('murid.import') }}"
                            class="m3-dropdown-item !text-amber-600 dark:!text-amber-400 hover:!bg-amber-50 dark:hover:!bg-amber-900/20 action-modal">
                            <i class="bi bi-file-earmark-arrow-up text-base"></i>
                            <span>Import Data</span>
                        </a>
                    @endcan

                </div>
            </div>
        </div>
    </div>

    <!-- TABLE CONTAINER -->
    <div id="data-table-container" class="m3-glass-card overflow-hidden flex flex-col relative z-10">
        <!-- Toolbar: Title, Filter, Search -->
        <div
            class="p-4 sm:p-5 border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-950/40 flex flex-col sm:flex-row justify-between items-center gap-3.5">

            <div class="w-full sm:w-auto">
                <h3
                    class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                    <div
                        class="w-7 h-7 rounded-lg bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center border border-primary/20 shrink-0">
                        <i class="bi bi-person-lines-fill text-xs"></i>
                    </div>
                    <span>Daftar Murid</span>
                </h3>
            </div>

            <form action="{{ route('murid.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2.5 w-full sm:w-auto group/search">

                <!-- Filter Status -->
                <div class="relative w-full sm:w-40 group/filter">
                    <select name="status" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pr-9 text-xs font-bold appearance-none cursor-pointer">
                        <option value="Semua" {{ $status == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="Aktif" {{ $status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Lulus" {{ $status == 'Lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="Pindah" {{ $status == 'Pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="Berhenti" {{ $status == 'Berhenti' ? 'selected' : '' }}>Berhenti</option>
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400 group-focus-within/filter:text-primary dark:group-focus-within/filter:text-primary-dark transition-colors">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-full sm:w-64 group/search">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark transition-colors">
                        <i class="bi bi-search text-xs"></i>
                    </div>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari NISM, NIK, Nama..."
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold">

                    @if (request('search'))
                        <a href="{{ route('murid.index', ['status' => $status]) }}"
                            class="absolute inset-y-0 right-0 w-9 h-9 flex items-center justify-center text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors outline-none"
                            title="Reset Pencarian">
                            <i class="bi bi-x-lg text-xs font-bold"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Responsive Wrapper -->
        <div class="overflow-x-auto custom-scrollbar relative z-10">
            @include('murid.tabel', ['murids' => $murids])
        </div>
    </div>

    <!-- Pagination -->
    @if ($murids->hasPages())
        <div class="mt-4 m3-glass-card p-4 relative z-10">
            {{ $murids->links('vendor.pagination.custom') }}
        </div>
    @endif

</x-app-layout>
