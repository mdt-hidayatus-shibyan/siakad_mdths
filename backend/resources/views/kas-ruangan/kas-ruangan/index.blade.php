<x-app-layout>

    <!-- 1. HEADER & FILTER SECTION -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <!-- Sisi Kiri: Judul Halaman -->
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-wallet2 text-xs"></i>
                    <span>Administrasi Kas Kelas</span>
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Kas Ruangan
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Monitoring perolehan kas kelas, setoran ke tabungan madrasah, dan status cicilan iuran murid.
            </p>
        </div>

        <!-- Sisi Kanan: Quick Actions & Filter -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full xl:w-auto shrink-0">

            <!-- Quick Action Links -->
            <div class="flex items-center gap-2">
                <a href="{{ route('setoran-kas-ruangan.index') }}"
                    class="h-10 px-4 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center gap-2 border border-zinc-200/80 dark:border-zinc-700 shadow-2xs transition-all active:scale-95">
                    <i class="bi bi-bank2 text-emerald-500"></i>
                    <span>Setoran Kas</span>
                </a>

                @can('update pengaturan kas')
                    <a href="{{ route('pengaturan-kas-ruangan.index') }}"
                        class="h-10 px-3.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center gap-1.5 border border-zinc-200/80 dark:border-zinc-700 shadow-2xs transition-all active:scale-95"
                        title="Atur Target Iuran Per Ruangan">
                        <i class="bi bi-gear-fill text-zinc-400"></i>
                        <span>Target</span>
                    </a>
                @endcan
            </div>

            <!-- Server Filter Form -->
            <form action="{{ request()->url() }}" method="GET" id="formFilter" class="flex items-center gap-2">

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-[200px] h-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-calendar3 text-xs"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full h-full !py-0 !pl-8 !pr-8 text-xs font-bold appearance-none cursor-pointer">
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[170px] h-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-door-open text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full h-full !py-0 !pl-8 !pr-8 text-xs font-bold appearance-none cursor-pointer">
                        <option value="">-- Semua Ruangan --</option>
                        @foreach ($daftarRuangan as $ruangItem)
                            <option value="{{ $ruangItem->id }}"
                                {{ isset($ruanganId) && $ruanganId == $ruangItem->id ? 'selected' : '' }}>
                                {{ $ruangItem->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- 2. STATISTIK GLOBAL (4 KARTU ATAS) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4 mb-6 relative z-10">

        <!-- 1. Total Kas Terkumpul -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0 border border-emerald-500/20 shadow-2xs">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Total Kas Terkumpul
                </p>
                <h4
                    class="text-base md:text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">
                    Rp {{ number_format($totalKasTerkumpul ?? 0, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 2. Disetor ke Madrasah -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl shrink-0 border border-sky-500/20 shadow-2xs">
                <i class="bi bi-bank2"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Disetor ke Madrasah
                </p>
                <h4 class="text-base md:text-xl font-black text-sky-600 dark:text-sky-400 tracking-tight font-mono">
                    Rp {{ number_format($totalKasDisetor ?? 0, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 3. Sisa Kas di Ruangan -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 border border-amber-500/20 shadow-2xs">
                <i class="bi bi-safe2"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Fisik di Wali
                </p>
                <h4 class="text-base md:text-xl font-black text-amber-600 dark:text-amber-400 tracking-tight font-mono">
                    Rp {{ number_format($totalSisaKas ?? 0, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 4. Total Ruangan & Murid -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0 border border-indigo-500/20 shadow-2xs">
                <i class="bi bi-door-open-fill"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Cakupan Kelas
                </p>
                <h4 class="text-base md:text-xl font-black text-zinc-900 dark:text-white tracking-tight font-mono">
                    {{ $totalRuangan ?? 0 }} <span class="text-xs font-bold text-zinc-400 font-sans">Ruangan</span>
                </h4>
            </div>
        </div>

    </div>

    <!-- 3. LIVE SEARCH & VIEW TOGGLE TOOLBAR -->
    <div class="mb-4 relative z-10 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                <i class="bi bi-search text-xs"></i>
            </div>
            <input type="text" id="liveSearchInput" onkeyup="filterRuangan()"
                placeholder="Cari nama ruangan, level, atau wali kelas..."
                class="m3-input-glass w-full h-10 !py-0 !pl-9 !pr-4 text-xs font-semibold rounded-2xl placeholder:text-zinc-400">
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Counter -->
            <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500 hidden sm:inline-block">
                <span id="visibleCardsCount"
                    class="text-zinc-900 dark:text-white font-black">{{ count($ruangans) }}</span>
                Ruangan
            </span>

            <!-- View Switcher (Table vs Grid) -->
            <div
                class="flex items-center bg-zinc-100/80 dark:bg-zinc-800/80 p-1 rounded-2xl border border-zinc-200/80 dark:border-zinc-700/80">
                <button type="button" onclick="switchIndexView('table')" id="btnViewTable"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-xs text-zinc-900 dark:text-white bg-white dark:bg-zinc-700 shadow-2xs transition-all"
                    title="Tampilan Tabel Ruangan">
                    <i class="bi bi-list-ul text-sm"></i>
                </button>
                <button type="button" onclick="switchIndexView('grid')" id="btnViewGrid"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-all"
                    title="Tampilan Grid Kartu">
                    <i class="bi bi-grid-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 4. TAMPILAN TABEL RUANGAN (PRIMARY / DEFAULT)             -->
    <!-- ========================================================= -->
    <div id="tableRuanganContainer"
        class="m3-glass-card rounded-3xl overflow-hidden shadow-2xs relative z-10 border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/60 backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr
                        class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-100/70 dark:bg-zinc-900/80 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500 font-sans">
                        <th class="py-3.5 pl-5 pr-2 w-12 text-center">No</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Ruangan & Jenjang</th>
                        <th class="py-3.5 px-4 min-w-[180px]">Wali Kelas</th>
                        <th class="py-3.5 px-3 text-center min-w-[90px]">Santri</th>
                        <th class="py-3.5 px-4 text-right min-w-[130px]">Kas Terkumpul</th>
                        <th class="py-3.5 px-4 text-right min-w-[130px]">Disetorkan</th>
                        <th class="py-3.5 px-4 text-right min-w-[130px]">Sisa di Wali</th>
                        <th class="py-3.5 px-3 text-center min-w-[120px]">Target (L/P)</th>
                        <th class="py-3.5 pl-3 pr-5 text-center min-w-[130px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200/70 dark:divide-zinc-800/70 font-mono">
                    @forelse ($ruangans as $idx => $ruang)
                        @php
                            $terkumpul = $ruang->total_terkumpul ?? ($ruang->pembayaran_kas_sum_jumlah_bayar ?? 0);
                            $disetor = $ruang->total_disetor ?? 0;
                            $sisaKas = max(0, $terkumpul - $disetor);
                            $targetLaki = $ruang->pengaturanKas->nominal_laki ?? 0;
                            $targetPerempuan = $ruang->pengaturanKas->nominal_perempuan ?? 0;
                            $waliNama = $ruang->waliRuangan->nama ?? 'Belum Ditentukan';
                            $levelNama = $ruang->level->nama_level ?? 'Madrasah';
                            $muridCount = $ruang->murids_count ?? 0;
                        @endphp
                        <tr class="ruangan-table-row hover:bg-zinc-500/5 dark:hover:bg-zinc-800/40 transition-colors"
                            data-keywords="{{ strtolower($ruang->nama_ruangan . ' ' . $levelNama . ' ' . $waliNama) }}">

                            <!-- 1. Nomor -->
                            <td class="py-3.5 pl-5 pr-2 text-center text-zinc-400 font-sans font-bold text-[11px]">
                                {{ $idx + 1 }}
                            </td>

                            <!-- 2. Ruangan & Level -->
                            <td class="py-3.5 px-4 font-sans">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0 text-xs shadow-2xs">
                                        <i class="bi bi-door-open-fill"></i>
                                    </span>
                                    <div>
                                        <h4
                                            class="font-black text-xs md:text-sm text-zinc-900 dark:text-white tracking-tight leading-snug">
                                            {{ $ruang->nama_ruangan }}
                                        </h4>
                                        <span
                                            class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                                            {{ $levelNama }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Wali Kelas -->
                            <td class="py-3.5 px-4 font-sans text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <div class="flex items-center gap-1.5 truncate">
                                    <i class="bi bi-person-badge text-emerald-500 text-xs"></i>
                                    <span class="truncate">{{ $waliNama }}</span>
                                </div>
                            </td>

                            <!-- 4. Jumlah Santri -->
                            <td class="py-3.5 px-3 text-center">
                                <span
                                    class="px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs border border-zinc-200/80 dark:border-zinc-700 shadow-2xs">
                                    {{ $muridCount }}
                                </span>
                            </td>

                            <!-- 5. Kas Terkumpul -->
                            <td
                                class="py-3.5 px-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-xs">
                                Rp {{ number_format($terkumpul, 0, ',', '.') }}
                            </td>

                            <!-- 6. Disetorkan -->
                            <td class="py-3.5 px-4 text-right font-black text-sky-600 dark:text-sky-400 text-xs">
                                Rp {{ number_format($disetor, 0, ',', '.') }}
                            </td>

                            <!-- 7. Sisa di Wali -->
                            <td class="py-3.5 px-4 text-right font-black text-amber-600 dark:text-amber-400 text-xs">
                                Rp {{ number_format($sisaKas, 0, ',', '.') }}
                            </td>

                            <!-- 8. Target (L/P) -->
                            <td class="py-3.5 px-3 text-center text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                                {{ number_format($targetLaki, 0, ',', '.') }} /
                                {{ number_format($targetPerempuan, 0, ',', '.') }}
                            </td>

                            <!-- 9. Aksi -->
                            <td class="py-3.5 pl-3 pr-5 text-center font-sans">
                                <a href="{{ route('kas-ruangan.show', $ruang->id) }}"
                                    class="h-8 px-3.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500 text-emerald-600 hover:text-white dark:text-emerald-400 dark:hover:text-zinc-900 font-bold text-xs inline-flex items-center gap-1.5 transition-all active:scale-95 border border-emerald-500/20 hover:border-emerald-500 shadow-2xs">
                                    <span>Kelola Kas</span>
                                    <i class="bi bi-arrow-right text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center font-sans">
                                <x-empty-state icon="bi-door-closed" title="Tidak Ada Data Ruangan"
                                    message="Tidak ditemukan data ruangan yang sesuai dengan filter tahun pelajaran." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Table Footer Total Ruangan -->
                @if (count($ruangans) > 0)
                    <tfoot
                        class="border-t-2 border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-900/90 font-mono font-black text-xs">
                        <tr>
                            <td colspan="4"
                                class="py-4 pl-5 pr-3 text-left font-sans text-zinc-900 dark:text-white uppercase tracking-wider text-[11px]">
                                Total ({{ count($ruangans) }} Ruangan)
                            </td>
                            <td class="py-4 px-4 text-right text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($totalKasTerkumpul ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right text-sky-600 dark:text-sky-400">
                                Rp {{ number_format($totalKasDisetor ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($totalSisaKas ?? 0, 0, ',', '.') }}
                            </td>
                            <td colspan="2" class="py-4 pl-3 pr-5"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 5. TAMPILAN GRID KARTU RUANGAN (ALTERNATIVE VIEW)         -->
    <!-- ========================================================= -->
    <div id="ruanganGridContainer"
        class="hidden grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5 relative z-10">
        @forelse ($ruangans as $ruang)
            @php
                $terkumpul = $ruang->total_terkumpul ?? ($ruang->pembayaran_kas_sum_jumlah_bayar ?? 0);
                $disetor = $ruang->total_disetor ?? 0;
                $sisaKas = max(0, $terkumpul - $disetor);
                $targetLaki = $ruang->pengaturanKas->nominal_laki ?? 0;
                $targetPerempuan = $ruang->pengaturanKas->nominal_perempuan ?? 0;
                $waliNama = $ruang->waliRuangan->nama ?? 'Belum Ditentukan';
                $levelNama = $ruang->level->nama_level ?? 'Madrasah';
                $muridCount = $ruang->murids_count ?? 0;
            @endphp

            <div class="ruangan-card m3-glass-card p-5 rounded-3xl flex flex-col justify-between gap-4 transition-all duration-300 shadow-2xs hover:shadow-lg hover:border-emerald-500/40 group relative overflow-hidden bg-white/70 dark:bg-zinc-900/60 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800"
                data-keywords="{{ strtolower($ruang->nama_ruangan . ' ' . $levelNama . ' ' . $waliNama) }}">

                <div>
                    <!-- Header Card: Nama Ruangan & Level -->
                    <div
                        class="flex items-start justify-between gap-3 pb-3.5 border-b border-zinc-200/80 dark:border-zinc-800">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-2xs">
                                    {{ $levelNama }}
                                </span>
                                <span class="text-[11px] font-bold text-zinc-400 flex items-center gap-1">
                                    <i class="bi bi-people-fill text-xs"></i> {{ $muridCount }} murid
                                </span>
                            </div>
                            <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"
                                title="{{ $ruang->nama_ruangan }}">
                                {{ $ruang->nama_ruangan }}
                            </h3>
                            <p
                                class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 truncate flex items-center gap-1.5">
                                <i class="bi bi-person-badge text-emerald-500 text-xs"></i>
                                <span>{{ $waliNama }}</span>
                            </p>
                        </div>

                        <div
                            class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 flex items-center justify-center text-lg shrink-0 border border-zinc-200 dark:border-zinc-700 shadow-2xs group-hover:scale-105 group-hover:text-emerald-500 transition-all">
                            <i class="bi bi-door-open-fill"></i>
                        </div>
                    </div>

                    <!-- Highlight Perolehan Kas Terkumpul -->
                    <div class="py-4">
                        <span
                            class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">
                            Kas Terkumpul
                        </span>
                        <div
                            class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight">
                            Rp {{ number_format($terkumpul, 0, ',', '.') }}
                        </div>

                        <!-- Mini Stats Row: Disetor & Sisa -->
                        <div
                            class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-dashed border-zinc-200/80 dark:border-zinc-800 text-xs">
                            <div
                                class="bg-zinc-50/80 dark:bg-zinc-950/50 p-2.5 rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70">
                                <span
                                    class="text-[9px] font-bold uppercase text-zinc-400 block mb-0.5">Disetorkan</span>
                                <span class="font-black text-sky-600 dark:text-sky-400 font-mono text-xs">
                                    Rp {{ number_format($disetor, 0, ',', '.') }}
                                </span>
                            </div>
                            <div
                                class="bg-zinc-50/80 dark:bg-zinc-950/50 p-2.5 rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70">
                                <span class="text-[9px] font-bold uppercase text-zinc-400 block mb-0.5">Sisa di
                                    Kelas</span>
                                <span class="font-black text-amber-600 dark:text-amber-400 font-mono text-xs">
                                    Rp {{ number_format($sisaKas, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Target & Tombol Kelola -->
                <div
                    class="pt-3 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                    <div class="text-[11px] font-medium text-zinc-400 leading-tight">
                        <span class="block text-[9px] font-bold uppercase text-zinc-500">Target (L/P)</span>
                        <span class="font-bold text-zinc-700 dark:text-zinc-300 font-mono text-xs">
                            {{ number_format($targetLaki, 0, ',', '.') }} /
                            {{ number_format($targetPerempuan, 0, ',', '.') }}
                        </span>
                    </div>

                    <a href="{{ route('kas-ruangan.show', $ruang->id) }}"
                        class="px-4 h-9 rounded-xl bg-emerald-500/10 hover:bg-emerald-500 text-emerald-600 hover:text-white dark:text-emerald-400 dark:hover:text-zinc-900 font-bold text-xs flex items-center gap-1.5 transition-all active:scale-95 border border-emerald-500/20 hover:border-emerald-500 shadow-2xs">
                        <span>Kelola Kas</span>
                        <i class="bi bi-arrow-right text-xs"></i>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state icon="bi-door-closed" title="Tidak Ada Data Ruangan"
                    message="Tidak ditemukan data ruangan yang sesuai dengan filter tahun pelajaran." />
            </div>
        @endforelse
    </div>

    <!-- Empty State Live Search -->
    <div id="noSearchMatchState" class="hidden col-span-full mt-6">
        <x-empty-state icon="bi-search" title="Ruangan Tidak Ditemukan"
            message="Tidak ada ruangan yang cocok dengan kata kunci pencarian Anda." />
    </div>

    <!-- SCRIPT LIVE FILTER & VIEW SWITCHER -->
    <script>
        let currentView = 'table';

        function switchIndexView(mode) {
            currentView = mode;
            const tableContainer = document.getElementById('tableRuanganContainer');
            const gridContainer = document.getElementById('ruanganGridContainer');
            const btnTable = document.getElementById('btnViewTable');
            const btnGrid = document.getElementById('btnViewGrid');

            if (mode === 'table') {
                tableContainer.classList.remove('hidden');
                gridContainer.classList.add('hidden');
                btnTable.className =
                    'w-8 h-8 rounded-xl flex items-center justify-center text-xs text-zinc-900 dark:text-white bg-white dark:bg-zinc-700 shadow-2xs transition-all';
                btnGrid.className =
                    'w-8 h-8 rounded-xl flex items-center justify-center text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-all';
            } else {
                tableContainer.classList.add('hidden');
                gridContainer.classList.remove('hidden');
                btnGrid.className =
                    'w-8 h-8 rounded-xl flex items-center justify-center text-xs text-zinc-900 dark:text-white bg-white dark:bg-zinc-700 shadow-2xs transition-all';
                btnTable.className =
                    'w-8 h-8 rounded-xl flex items-center justify-center text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-all';
            }
        }

        function filterRuangan() {
            const query = (document.getElementById('liveSearchInput')?.value || '').toLowerCase().trim();
            const cards = document.querySelectorAll('.ruangan-card');
            const rows = document.querySelectorAll('.ruangan-table-row');
            let visibleCount = 0;

            cards.forEach(card => {
                const keywords = card.getAttribute('data-keywords') || '';
                if (!query || keywords.includes(query)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            rows.forEach(row => {
                const keywords = row.getAttribute('data-keywords') || '';
                if (!query || keywords.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            const countEl = document.getElementById('visibleCardsCount');
            if (countEl) countEl.innerText = visibleCount;

            const emptyState = document.getElementById('noSearchMatchState');
            if (emptyState) {
                if (visibleCount === 0 && (cards.length > 0 || rows.length > 0)) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
        }
    </script>
</x-app-layout>
