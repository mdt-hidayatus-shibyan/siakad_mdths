@section('title', 'Kepanitiaan IMNI')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>

            <h2
                class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300 mt-1">
                Kepanitiaan IMNI
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                Kelola susunan kepanitiaan IMNI (Ketua, Bendahara, dan Anggota) untuk pelaksanaan ujian murid tingkat
                akhir.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            <!-- Form Filter Tahun & Jabatan -->
            <form action="{{ route('panitia-imni.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2.5 w-full sm:w-auto">

                <!-- Filter Dropdown Tahun Pelajaran -->
                <div class="relative group/select w-full sm:w-48">
                    <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} H | {{ $tp->nama_masehi }} M
                                {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Dropdown Jabatan -->
                <div class="relative group/select w-full sm:w-36">
                    <select name="jabatan" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        <option value="">-- Semua Jabatan --</option>
                        <option value="Ketua" {{ request('jabatan') == 'Ketua' ? 'selected' : '' }}>Ketua</option>
                        <option value="Bendahara" {{ request('jabatan') == 'Bendahara' ? 'selected' : '' }}>Bendahara
                        </option>
                        <option value="Anggota" {{ request('jabatan') == 'Anggota' ? 'selected' : '' }}>Anggota</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Input Pencarian Ustadz -->
                <div class="relative w-full sm:w-48 group/search">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-300 text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark">
                        <i class="bi bi-search text-sm"></i>
                    </div>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Ustadz..."
                        class="m3-input-glass w-full !pl-10 !pr-10">

                    @if (request('search') || request('jabatan'))
                        <a href="{{ route('panitia-imni.index', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                            class="absolute inset-y-0 right-0 w-10 h-10 flex items-center justify-center text-zinc-400 hover:text-red-600 dark:text-zinc-500 dark:hover:text-red-400 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 rounded-xl transition-colors duration-200 outline-none"
                            title="Reset Filter">
                            <i class="bi bi-x-lg text-xs font-bold"></i>
                        </a>
                    @endif
                </div>
            </form>



            @can('create ujian')
                <a href="{{ route('panitia-imni.create', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                    class="m3-btn-primary w-full sm:w-auto action-modal group/btn">
                    <i
                        class="bi bi-person-plus-fill text-base transition-transform duration-300 group-hover/btn:scale-110"></i>
                    <span>Tambah Panitia</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Ringkasan Kartu Kepengurusan IMNI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 relative z-10">
        <!-- Kartu Ketua Panitia -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-amber-500/30 shadow-sm relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl -mr-8 -mt-8 pointer-events-none">
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold border border-amber-500/20 shrink-0">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[11px] font-extrabold text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Ketua
                        Panitia</span>
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate"
                        title="{{ $ketua?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}">
                        {{ $ketua?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $ketua?->ustadz?->nigm ? 'NIGM: ' . $ketua->ustadz->nigm : 'Jadwal, Ruangan & Pengawas' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Kartu Bendahara Panitia -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-emerald-500/30 shadow-sm relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl -mr-8 -mt-8 pointer-events-none">
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold border border-emerald-500/20 shrink-0">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Bendahara
                        Panitia</span>
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate"
                        title="{{ $bendahara?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}">
                        {{ $bendahara?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $bendahara?->ustadz?->nigm ? 'NIGM: ' . $bendahara->ustadz->nigm : 'Tagihan & Kas Ujian' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Kartu Total Anggota -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-sky-500/30 shadow-sm relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-sky-500/10 rounded-full blur-2xl -mr-8 -mt-8 pointer-events-none">
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl font-bold border border-sky-500/20 shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[11px] font-extrabold text-sky-600 dark:text-sky-400 uppercase tracking-wider block">Anggota
                        Panitia</span>
                    <h4 class="text-lg font-black text-zinc-900 dark:text-white">
                        {{ $anggotaList->count() }} <span class="text-xs font-semibold text-zinc-500">Ustadz</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Nilai & Pelaksanaan
                    </p>
                </div>
            </div>
        </div>

        <!-- Kartu Total Kepanitiaan -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-violet-500/30 shadow-sm relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-violet-500/10 rounded-full blur-2xl -mr-8 -mt-8 pointer-events-none">
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-violet-500/15 text-violet-600 dark:text-violet-400 flex items-center justify-center text-xl font-bold border border-violet-500/20 shrink-0">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[11px] font-extrabold text-violet-600 dark:text-violet-400 uppercase tracking-wider block">Total
                        Panitia IMNI</span>
                    <h4 class="text-lg font-black text-zinc-900 dark:text-white">
                        {{ $panitiaList->count() }} <span class="text-xs font-semibold text-zinc-500">Personel</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Tahun Ajaran Aktif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="flex flex-col gap-3 relative z-10">
        @include('ujian.panitia-imni.list', ['panitiaList' => $panitiaList])
    </div>
</x-app-layout>
