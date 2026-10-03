@section('title', 'Master Ujian IMNI')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>

            <h2
                class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                Master Ujian IMNI
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                Kelola master agenda pelaksanaan Imtihan Niha'i kelas akhir (3 TPQ, 6 IBT, 3 TSA).
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">

            <!-- Form Gabungan (Filter Tahun & Pencarian) -->
            <form action="{{ route('master-imni.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2.5 w-full sm:w-auto">

                <!-- Filter Dropdown Tahun Pelajaran -->
                <div class="relative group/select w-full sm:w-48">
                    <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        <option value="">-- Semua Tahun --</option>
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} H | {{ $tp->nama_masehi }} M
                                ({{ $tp->is_active ? 'Aktif' : '' }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Dropdown Tingkat -->
                <div class="relative group/select w-full sm:w-40">
                    <select name="tingkat_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        <option value="">-- Semua Tingkat --</option>
                        @foreach ($tingkats as $tkt)
                            <option value="{{ $tkt->id }}" {{ $selectedTingkatId == $tkt->id ? 'selected' : '' }}>
                                {{ $tkt->nama_tingkat }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-full sm:w-56 group/search">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-300 text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark">
                        <i class="bi bi-search text-sm"></i>
                    </div>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari Ujian IMNI..." class="m3-input-glass w-full !pl-10 !pr-10">

                    <!-- Tombol Reset Search -->
                    @if (request('search'))
                        <a href="{{ route('master-imni.index', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                            class="absolute inset-y-0 right-0 w-10 h-10 flex items-center justify-center text-zinc-400 hover:text-red-600 dark:text-zinc-500 dark:hover:text-red-400 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 rounded-xl transition-colors duration-200 outline-none"
                            title="Hapus Pencarian">
                            <i class="bi bi-x-lg text-xs font-bold"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Tombol Tambah Data -->
            @can('create ujian')
                <a href="{{ route('master-imni.create') }}" class="m3-btn-primary w-full sm:w-auto action-modal group/btn">
                    <i class="bi bi-journal-plus text-base transition-transform duration-300 group-hover/btn:scale-110"></i>
                    <span>Tambah Agenda IMNI</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="flex flex-col gap-3 relative z-10">
        @include('ujian.panitia-imni.master.list', ['ujians' => $ujians])
    </div>
</x-app-layout>
