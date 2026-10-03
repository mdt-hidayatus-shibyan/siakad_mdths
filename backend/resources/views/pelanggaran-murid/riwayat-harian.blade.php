@section('title', 'Riwayat Harian - Pelanggaran Murid')

<x-app-layout>
    <!-- HEADER & TOP ACTION BAR -->
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('pelanggaran-murid.menu')
        </div>

        <!-- Area Form Filter -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('pelanggaran-murid.riwayatHarian') }}" method="GET" id="formFilter"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs">

                <!-- Filter Tanggal -->
                <div class="relative w-full sm:w-44 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-rose-500 transition-colors">
                        <i class="bi bi-calendar-date text-xs"></i>
                    </div>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" required
                        onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-3 text-xs font-bold cursor-pointer">
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-48 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-rose-500 transition-colors">
                        <i class="bi bi-door-open text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Ruangan --
                        </option>
                        @foreach ($ruangans as $r)
                            <option value="{{ $r->id }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ $ruangan_id == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Kategori -->
                @if ($referensiKategoris->isNotEmpty())
                    <div class="relative w-full sm:w-44 group/select">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-rose-500 transition-colors">
                            <i class="bi bi-tag text-xs"></i>
                        </div>
                        <select name="kategori" onchange="document.getElementById('formFilter').submit()"
                            class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Kategori --
                            </option>
                            @foreach ($referensiKategoris as $kat)
                                <option value="{{ $kat }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                    {{ $kategori == $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                @endif

                <!-- Tombol Refresh / Submit -->
                <div class="w-full sm:w-auto shrink-0 flex items-center gap-1.5">
                    <button type="submit" class="m3-btn-primary !bg-rose-600 hover:!bg-rose-700 w-full sm:w-auto h-10 px-4 text-xs group/btn">
                        <i class="bi bi-arrow-repeat text-xs mr-1"></i>
                        <span>Muat</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- METRIC SUMMARY CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 relative z-10 print:hidden">
        <!-- Card 1: Total Kejadian -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Total Kasus
                </p>
                <div class="flex items-baseline gap-1.5">
                    <h4 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                        {{ $totalKasus }}
                    </h4>
                    <span class="text-[10px] font-bold text-zinc-500">kejadian</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Poin Sanksi -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Akumulasi Poin
                </p>
                <div class="flex items-baseline gap-1.5">
                    <h4 class="text-xl font-black text-amber-600 dark:text-amber-400 tracking-tight leading-none">
                        +{{ $totalPoin }}
                    </h4>
                    <span class="text-[10px] font-bold text-zinc-500">poin</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Murid Melanggar -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Murid Terlibat
                </p>
                <div class="flex items-baseline gap-1.5">
                    <h4 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                        {{ $totalMurid }}
                    </h4>
                    <span class="text-[10px] font-bold text-zinc-500">murid</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Ruangan Terlibat -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-door-open-fill"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Kelas Terlibat
                </p>
                <div class="flex items-baseline gap-1.5">
                    <h4 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                        {{ $totalRuangan }}
                    </h4>
                    <span class="text-[10px] font-bold text-zinc-500">ruangan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AREA TABEL UTAMA RIWAYAT HARIAN -->
    <div class="m3-glass-card overflow-hidden relative z-10 shadow-2xs">

        <!-- Header Tabel & Toolbar -->
        <div
            class="bg-zinc-100/60 dark:bg-zinc-900/60 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <h3
                    class="font-black text-zinc-900 dark:text-white text-base md:text-lg tracking-tight leading-tight flex items-center gap-2">
                    <i class="bi bi-shield-exclamation text-rose-500"></i>
                    <span>Riwayat Pelanggaran Murid Harian</span>
                </h3>
                <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5 flex items-center gap-1.5">
                    <i class="bi bi-calendar-event"></i>
                    <span>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
                    <span>&bull;</span>
                    <span>{{ $ruangan_id ? ($ruangans->firstWhere('id', $ruangan_id)?->nama_ruangan ?? 'Ruangan Terpilih') : 'Semua Ruangan / Seluruh Kelas' }}</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 print:hidden">
                <!-- Live Search Box -->
                <div class="relative w-full sm:w-60 group/search">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 group-focus-within/search:text-rose-500 transition-colors">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                    <input type="text" id="liveSearchInput" onkeyup="filterTableRows()"
                        placeholder="Cari Murid / NISM / Kasus..."
                        class="m3-input-glass w-full !pl-8 !pr-3 !py-1.5 text-xs font-bold">
                </div>

                <!-- Export CSV Button -->
                <a href="{{ route('pelanggaran-murid.exportRiwayatHarian', ['tanggal' => $tanggal, 'ruangan_id' => $ruangan_id, 'kategori' => $kategori]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-black transition-all shadow-2xs shrink-0">
                    <i class="bi bi-file-earmark-excel-fill text-xs"></i>
                    <span>Export CSV</span>
                </a>

                <!-- Print Button -->
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-xs font-black transition-all shadow-2xs shrink-0">
                    <i class="bi bi-printer-fill text-xs"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        @if ($riwayatPelanggaran->isEmpty())
            <!-- STATE KOSONG / ALHAMDULILLAH -->
            <div class="py-16 px-6 text-center">
                <div
                    class="w-14 h-14 bg-emerald-500/10 border border-emerald-500/20 rounded-3xl flex items-center justify-center text-emerald-500 text-3xl mx-auto mb-3 shadow-2xs">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-1">
                    Alhamdulillah, Nihil Kasus
                </h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-md mx-auto">
                    Tidak tercatat adanya pelanggaran murid pada tanggal
                    <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
                    {{ $ruangan_id ? 'di ' . ($ruangans->firstWhere('id', $ruangan_id)?->nama_ruangan ?? 'ruangan ini') : 'di seluruh ruangan' }}.
                </p>
                <div class="mt-4">
                    <a href="{{ route('pelanggaran-murid.index', ['tanggal' => $tanggal, 'ruangan_id' => $ruangan_id]) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary dark:text-primary-dark border border-primary/20 text-xs font-black transition-all">
                        <i class="bi bi-pencil-square"></i>
                        <span>Input Kasus Baru</span>
                    </a>
                </div>
            </div>
        @else
            <!-- TABEL DATA PELANGGARAN -->
            <div class="overflow-x-auto relative z-10 custom-scrollbar">
                <table class="w-full text-left text-xs border-collapse min-w-[900px]" id="tablePelanggaran">
                    <thead
                        class="bg-zinc-100/70 dark:bg-zinc-800/50 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                        <tr class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-12 text-center border-r border-zinc-200/60 dark:border-zinc-800/60">
                                No
                            </th>
                            <th class="py-3 px-4 w-32 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                Waktu
                            </th>
                            <th class="py-3 px-4 w-44 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                Ruangan / Level
                            </th>
                            <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                Identitas Murid
                            </th>
                            <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                Kasus Pelanggaran
                            </th>
                            <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                Keterangan / Detail
                            </th>
                            <th class="py-3 px-4 w-40 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                Petugas
                            </th>
                            <th class="py-3 px-3 w-20 text-center print:hidden">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/60 bg-white/40 dark:bg-zinc-900/40">
                        @foreach ($riwayatPelanggaran as $index => $item)
                            <tr
                                class="row-pelanggaran hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group">
                                <!-- No -->
                                <td
                                    class="py-3 px-3.5 text-center font-bold text-zinc-500 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    {{ $index + 1 }}
                                </td>

                                <!-- Waktu Input -->
                                <td class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    <div class="font-black text-zinc-900 dark:text-white text-xs flex items-center gap-1">
                                        <i class="bi bi-clock text-[10px] text-zinc-400"></i>
                                        <span>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }} WIB</span>
                                    </div>
                                    <div class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 mt-0.5">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                    </div>
                                </td>

                                <!-- Ruangan & Level -->
                                <td class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    <div class="font-black text-zinc-900 dark:text-white text-xs searchable-room">
                                        {{ $item->ruangan->nama_ruangan ?? '-' }}
                                    </div>
                                    @if ($item->ruangan?->level)
                                        <div class="mt-1">
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/20 text-[9px] font-black uppercase">
                                                {{ $item->ruangan->level->nama_level }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Identitas Murid -->
                                <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-7 h-7 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 font-black text-[11px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($item->murid->nama_lengkap ?? 'M', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4
                                                class="font-black text-xs text-zinc-900 dark:text-white truncate searchable-name">
                                                {{ $item->murid->nama_lengkap ?? '-' }}
                                            </h4>
                                            <p
                                                class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase font-mono tracking-wider searchable-nism mt-0.5">
                                                NISM: {{ $item->murid->nism ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kasus Pelanggaran -->
                                <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h4
                                                class="font-black text-xs text-zinc-900 dark:text-white leading-tight searchable-case">
                                                {{ $item->referensiPelanggaran->nama_pelanggaran ?? 'Pelanggaran Khusus' }}
                                            </h4>
                                            @if ($item->referensiPelanggaran?->kategori)
                                                <span
                                                    class="inline-block px-1.5 py-0.2 rounded text-[8px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 uppercase tracking-wider mt-1">
                                                    {{ $item->referensiPelanggaran->kategori }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="shrink-0">
                                            <span
                                                class="px-2 py-0.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-black text-[10px] whitespace-nowrap shadow-2xs">
                                                +{{ (float)($item->referensiPelanggaran->poin ?? 0) }} Poin
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Keterangan / Detail -->
                                <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300 leading-snug line-clamp-2 searchable-desc">
                                        {{ $item->keterangan ?: '-' }}
                                    </p>
                                </td>

                                <!-- Petugas Penginput -->
                                <td class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <i class="bi bi-person-badge text-zinc-400 text-xs"></i>
                                        <span class="truncate">{{ $item->penginput->name ?? 'Sistem' }}</span>
                                    </div>
                                </td>

                                <!-- Aksi (Hapus) -->
                                <td class="py-3 px-3 text-center print:hidden">
                                    @can('hapus pelanggaran-murid')
                                        <form action="{{ route('pelanggaran-murid.destroyHarian', $item->id) }}"
                                            method="POST" class="inline-block m-0"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pelanggaran murid ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center transition-all active:scale-95 shadow-2xs"
                                                title="Hapus Catatan Kasus">
                                                <i class="bi bi-trash3-fill text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel -->
            <div
                class="px-5 py-3.5 bg-zinc-50/70 dark:bg-zinc-950/60 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs font-bold text-zinc-500 dark:text-zinc-400">
                <div>
                    Menampilkan <span id="visibleCount">{{ $riwayatPelanggaran->count() }}</span> dari {{ $totalKasus }} catatan pelanggaran
                </div>
                <div class="flex items-center gap-3">
                    <span>Total Akumulasi Sanksi: <strong class="text-rose-600 dark:text-rose-400 font-black">+{{ $totalPoin }} Poin</strong></span>
                </div>
            </div>
        @endif
    </div>

    <!-- JAVASCRIPT LIVE SEARCH FILTER -->
    <script>
        function filterTableRows() {
            const input = document.getElementById('liveSearchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#tablePelanggaran tbody tr.row-pelanggaran');
            let count = 0;

            rows.forEach(row => {
                const name = row.querySelector('.searchable-name')?.textContent.toLowerCase() || '';
                const nism = row.querySelector('.searchable-nism')?.textContent.toLowerCase() || '';
                const room = row.querySelector('.searchable-room')?.textContent.toLowerCase() || '';
                const vCase = row.querySelector('.searchable-case')?.textContent.toLowerCase() || '';
                const desc = row.querySelector('.searchable-desc')?.textContent.toLowerCase() || '';

                if (name.includes(input) || nism.includes(input) || room.includes(input) || vCase.includes(input) || desc.includes(input)) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });

            const visibleCounter = document.getElementById('visibleCount');
            if (visibleCounter) {
                visibleCounter.textContent = count;
            }
        }
    </script>
</x-app-layout>
