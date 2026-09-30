@section('title', 'Progres Harian - Presensi Murid')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-murid.menu')
        </div>

        <!-- Area Form Filter -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-murid.progres') }}" method="GET"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs"
                id="formFilter">

                <!-- Filter Tanggal -->
                <div class="relative w-full sm:w-44 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-date text-xs"></i>
                    </div>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" required
                        onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-3 text-xs font-bold cursor-pointer">
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-48 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
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

                <!-- Filter Sesi (Khusus Event Multi-Sesi) -->
                @if (!empty($isEvent) && !empty($sesiList) && count($sesiList) > 1)
                    <div class="relative w-full sm:w-40 group/select">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-500 transition-colors">
                            <i class="bi bi-clock-history text-xs"></i>
                        </div>
                        <select name="sesi" onchange="document.getElementById('formFilter').submit()"
                            class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none border-indigo-300/60 dark:border-indigo-700/60 text-indigo-900 dark:text-indigo-200">
                            <option value="">-- Semua Sesi --</option>
                            @foreach ($sesiList as $s)
                                <option value="{{ $s }}" {{ ($sesi_filter ?? '') === $s ? 'selected' : '' }}>
                                    Sesi {{ $s }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                @endif

                <!-- Filter Status -->
                <div class="relative w-full sm:w-40 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-funnel text-xs"></i>
                    </div>
                    <select name="status" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" {{ empty($status_filter) ? 'selected' : '' }}>-- Semua Status --</option>
                        <option value="sudah" {{ $status_filter === 'sudah' ? 'selected' : '' }}>✓ Sudah Absen</option>
                        <option value="belum" {{ $status_filter === 'belum' ? 'selected' : '' }}>● Belum Absen
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Tombol Refresh / Submit -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-4 text-xs group/btn">
                        <i class="bi bi-arrow-repeat text-xs mr-1"></i>
                        <span>Muat</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    @if (!empty($isEvent))
        <!-- BANNER INFO EVENT KHUSUS DENGAN PRESENSI AKTIF -->
        <div
            class="mb-6 m3-glass-card p-4 md:p-5 border-indigo-500/30 bg-linear-to-r from-indigo-500/10 via-indigo-500/5 to-transparent relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center text-xl shrink-0 border border-indigo-500/30 shadow-2xs">
                    <i class="bi bi-stars"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-extrabold text-[10px] uppercase tracking-wider">
                            {{ $eventInfo->kategoriKegiatan?->nama_kategori ?? 'Kegiatan Khusus' }}
                        </span>
                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $eventInfo->tipe_presensi === 'multi_sesi' ? 'Presensi Multi-Sesi' : 'Presensi 1 Sesi Harian' }}
                        </span>
                    </div>
                    <h4 class="text-sm font-black text-zinc-900 dark:text-white mt-0.5">
                        {{ $eventInfo->nama_kegiatan }}
                    </h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        KBM reguler dibebaskan dan dialihkan ke presensi kegiatan murid per ruangan.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('presensi-murid.index', ['tanggal' => $tanggal]) }}"
                    class="m3-btn-primary !bg-indigo-600 hover:!bg-indigo-700 h-9 px-4 text-xs font-black shadow-2xs whitespace-nowrap">
                    <i class="bi bi-pencil-square mr-1.5"></i> Buka Form Presensi
                </a>
            </div>
        </div>
    @elseif (!empty($isUjian))
        <!-- BANNER INFO JADWAL/MASA UJIAN -->
        <div
            class="mb-6 m3-glass-card p-4 md:p-5 border-violet-500/30 bg-violet-500/10 relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-2xl flex items-center justify-center text-xl shrink-0 border border-violet-500/30">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-md bg-violet-500/20 text-violet-700 dark:text-violet-300 font-extrabold text-[10px] uppercase tracking-wider">
                            Masa Ujian Madrasah
                        </span>
                    </div>
                    <h4 class="text-sm font-black text-zinc-900 dark:text-white mt-0.5">
                        Tanggal ini bertepatan dengan {{ $namaUjian ?? 'Ujian Madrasah' }}
                    </h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        KBM reguler dialihkan ke pelaksanaan Ujian Madrasah.
                    </p>
                </div>
            </div>
            @can('create presensi-ujian')
                <a href="{{ route('presensi-ujian.input') }}"
                    class="m3-btn-primary h-9 px-4 text-xs font-black shadow-2xs whitespace-nowrap" target="_blank">
                    <i class="bi bi-arrow-right-circle mr-1.5"></i> Buka Presensi Ujian
                </a>
            @endcan
        </div>
    @elseif (!empty($isLibur))
        <!-- BANNER HARI LIBUR -->
        <div
            class="mb-6 m3-glass-card p-4 md:p-5 border-rose-500/30 bg-rose-500/10 relative z-10 flex items-center gap-3.5">
            <div
                class="w-11 h-11 bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center text-xl shrink-0 border border-rose-500/30">
                <i class="bi bi-brightness-high-fill"></i>
            </div>
            <div>
                <span
                    class="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-700 dark:text-rose-300 font-extrabold text-[10px] uppercase tracking-wider">
                    Hari Libur Madrasah
                </span>
                <h4 class="text-sm font-black text-zinc-900 dark:text-white mt-0.5">
                    {{ $keteranganLibur ?? 'Kegiatan Belajar Mengajar Diliburkan' }}
                </h4>
            </div>
        </div>
    @endif

    <div id="data-grid-container">
        <!-- METRIC CARDS & PROGRES BAR -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 relative z-10">
            <!-- Card 1: Total Sesi -->
            <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
                <div
                    class="w-11 h-11 rounded-2xl {{ !empty($isEvent) ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700' }} flex items-center justify-center text-lg shrink-0 border">
                    <i class="bi {{ !empty($isEvent) ? 'bi-calendar-event-fill' : 'bi-calendar-check-fill' }}"></i>
                </div>
                <div class="min-w-0">
                    <p
                        class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider truncate">
                        {{ !empty($isEvent) ? 'Total Sesi Kegiatan' : 'Total Sesi KBM' }}
                    </p>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-white leading-tight">{{ $totalSesi }}</h3>
                    <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500">
                        {{ !empty($isEvent) ? $eventInfo->nama_kegiatan ?? 'Event' : 'Hari ' . $hari_ini }}
                    </p>
                </div>
            </div>

            <!-- Card 2: Sudah Absen -->
            <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
                <div
                    class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-500/20">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="min-w-0">
                    <p
                        class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider truncate">
                        Sudah Diabsen</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">
                        {{ $totalSudahAbsen }}</h3>
                    <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500">{{ $persenSelesai }}% Selesai</p>
                </div>
            </div>

            <!-- Card 3: Belum Absen -->
            <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
                <div
                    class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0 border border-rose-500/20">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="min-w-0">
                    <p
                        class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider truncate">
                        Belum Diabsen</p>
                    <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 leading-tight">
                        {{ $totalBelumAbsen }}
                    </h3>
                    <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500">Menunggu Input</p>
                </div>
            </div>

            <!-- Card 4: Rekap Murid -->
            <div class="m3-glass-card p-4 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Progres
                        Input</p>
                    <span
                        class="text-xs font-black {{ !empty($isEvent) ? 'text-indigo-600 dark:text-indigo-400' : 'text-primary dark:text-primary-dark' }}">{{ $persenSelesai }}%</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-2 overflow-hidden mb-2">
                    <div class="{{ !empty($isEvent) ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-primary dark:bg-primary-dark' }} h-2 rounded-full transition-all duration-500"
                        style="width: {{ $persenSelesai }}%"></div>
                </div>
                <div class="flex items-center gap-2 text-[9px] font-bold text-zinc-500 dark:text-zinc-400">
                    <span class="text-emerald-600 dark:text-emerald-400">H: {{ $rekapStatus['Hadir'] }}</span>
                    <span class="text-blue-600 dark:text-blue-400">S: {{ $rekapStatus['Sakit'] }}</span>
                    <span class="text-amber-600 dark:text-amber-400">I: {{ $rekapStatus['Izin'] }}</span>
                    <span class="text-rose-600 dark:text-rose-400">A: {{ $rekapStatus['Alpha'] }}</span>
                    @if ($rekapStatus['Dispensasi'] > 0)
                        <span class="text-purple-600 dark:text-purple-400">D: {{ $rekapStatus['Dispensasi'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- TABEL DETAIL MONITORING PROGRES HARIAN -->
        <div class="m3-glass-card overflow-hidden relative z-10 shadow-2xs">
            <!-- Header Tabel -->
            <div
                class="bg-zinc-100/50 dark:bg-zinc-800/40 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-4 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                <div>
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-tight uppercase">
                        Monitoring Presensi Murid — <span
                            class="{{ !empty($isEvent) ? 'text-indigo-600 dark:text-indigo-400' : 'text-primary dark:text-primary-dark' }}">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
                    </h3>
                    <p
                        class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider flex items-center mt-0.5">
                        <i class="bi bi-info-circle-fill mr-1.5 opacity-70"></i>
                        {{ !empty($isEvent) ? 'Pantauan presensi murid kegiatan (' . $eventInfo->nama_kegiatan . ') per ruangan dan sesi.' : 'Pantauan status penginputan presensi murid seluruh kelas secara real-time.' }}
                    </p>
                </div>
            </div>

            @if (empty($detailProgres))
                <div class="p-8">
                    <x-empty-state icon="bi-calendar-x" title="Tidak Ada Jadwal / Sesi"
                        message="Tidak ditemukan sesi presensi yang aktif pada tanggal atau filter yang Anda pilih." />
                </div>
            @else
                <div class="overflow-x-auto relative z-10 custom-scrollbar">
                    <table class="w-full text-left text-xs border-collapse min-w-[850px]">
                        <thead
                            class="bg-zinc-100/70 dark:bg-zinc-800/50 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                            <tr
                                class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                <th
                                    class="py-3 px-4 w-12 text-center border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    No</th>
                                <th class="py-3 px-4 w-48 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    {{ !empty($isEvent) ? 'Ruangan & Sesi' : 'Ruangan & Jam' }}
                                </th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    {{ !empty($isEvent) ? 'Agenda Kegiatan' : 'Mata Pelajaran' }}
                                </th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    {{ !empty($isEvent) ? 'Target Murid' : 'Guru Pengampu' }}
                                </th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">Status
                                    Presensi
                                </th>
                                <th class="py-3 px-4 w-36 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/60">
                            @php $no = 1; @endphp
                            @foreach ($detailProgres as $item)
                                @php
                                    $isEventItem = $item['is_event'] ?? false;
                                    $isSudah = $item['is_sudah'];
                                @endphp
                                <tr
                                    class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors {{ $isSudah ? '' : 'bg-rose-500/[0.02]' }}">
                                    <!-- No -->
                                    <td
                                        class="py-3 px-4 text-center font-bold text-zinc-500 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        {{ $no++ }}
                                    </td>

                                    <!-- Ruangan & Sesi / Jam -->
                                    <td class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        <div class="font-black text-zinc-900 dark:text-white text-xs">
                                            {{ ($item['ruangan'] ?? ($item['jadwal']->ruangan ?? null))?->nama_ruangan ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            @if ($isEventItem)
                                                <span
                                                    class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20 text-[9px] font-black uppercase">
                                                    Sesi {{ $item['sesi'] }}
                                                </span>
                                            @else
                                                @php
                                                    $j = $item['jadwal'];
                                                    $jamText = match ($j->jam_ke) {
                                                        'Nadzoman' => '13:45 - 14:00',
                                                        '1' => '14:00 - 14:45',
                                                        '2' => '15:30 - 16:15',
                                                        'Ekstra' => '20:00 - 21:00',
                                                        default => 'Jam Ke-' . $j->jam_ke,
                                                    };
                                                @endphp
                                                <span
                                                    class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[9px] font-black text-zinc-600 dark:text-zinc-400 uppercase">
                                                    Jam {{ $j->jam_ke }}
                                                </span>
                                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold">
                                                    {{ $jamText }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Mata Pelajaran / Agenda Kegiatan -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        @if ($isEventItem)
                                            <div class="font-black text-zinc-900 dark:text-white text-xs">
                                                {{ $item['nama_event'] }}
                                            </div>
                                            <div
                                                class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">
                                                Event Madrasah • Sesi {{ $item['sesi'] }}
                                            </div>
                                        @else
                                            <div class="font-black text-zinc-900 dark:text-white text-xs">
                                                {{ $item['jadwal']->mataPelajaran->nama_mapel ?? '-' }}
                                            </div>
                                            @if ($item['jadwal']->ruangan?->level)
                                                <div
                                                    class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 mt-0.5">
                                                    Level: {{ $item['jadwal']->ruangan->level->nama_level }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    <!-- Guru Pengampu / Target Murid -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        @if ($isEventItem)
                                            <div class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                                Ruangan: <span
                                                    class="font-black text-zinc-900 dark:text-white">{{ $item['ruangan']->nama_ruangan }}</span>
                                            </div>
                                            <div class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 mt-0.5">
                                                Level {{ $item['ruangan']->level?->nama_level ?? '-' }}
                                            </div>
                                        @else
                                            <div class="flex flex-col gap-1">
                                                @foreach ($item['jadwal']->daftar_ustadz as $u)
                                                    @php
                                                        $isUtama =
                                                            ($u->pivot->is_utama ?? false) ||
                                                            $u->id == $item['jadwal']->ustadz_id;
                                                    @endphp
                                                    <div
                                                        class="flex items-center gap-1.5 text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                                        @if ($isUtama)
                                                            <i class="bi bi-star-fill text-amber-500 text-[10px]"
                                                                title="Guru Utama"></i>
                                                        @else
                                                            <span
                                                                class="px-1 py-0.2 text-[8px] font-black rounded bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20"
                                                                title="Guru Pendamping">P</span>
                                                        @endif
                                                        <span>{{ $u->nama_lengkap }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Presensi Murid -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        @if ($isSudah)
                                            <div class="flex flex-col gap-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                                        <i class="bi bi-check-circle-fill text-[9px]"></i> Sudah Absen
                                                    </span>
                                                    @if ($item['waktu_update'])
                                                        <span
                                                            class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold">
                                                            <i class="bi bi-clock mr-0.5"></i>
                                                            {{ $item['waktu_update'] }} WIB
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Rincian Jumlah Murid -->
                                                <div class="flex flex-wrap items-center gap-1 text-[9px] font-bold">
                                                    <span
                                                        class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
                                                        title="Hadir">
                                                        Hadir: {{ $item['hadir'] }}
                                                    </span>
                                                    @if ($item['sakit'] > 0)
                                                        <span
                                                            class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20"
                                                            title="Sakit">
                                                            Sakit: {{ $item['sakit'] }}
                                                        </span>
                                                    @endif
                                                    @if ($item['izin'] > 0)
                                                        <span
                                                            class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20"
                                                            title="Izin">
                                                            Izin: {{ $item['izin'] }}
                                                        </span>
                                                    @endif
                                                    @if ($item['alpha'] > 0)
                                                        <span
                                                            class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20"
                                                            title="Alpha">
                                                            Alpha: {{ $item['alpha'] }}
                                                        </span>
                                                    @endif
                                                    @if ($item['dispensasi'] > 0)
                                                        <span
                                                            class="px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20"
                                                            title="Dispensasi">
                                                            Disp: {{ $item['dispensasi'] }}
                                                        </span>
                                                    @endif
                                                    <span class="text-zinc-400 dark:text-zinc-500 ml-1">
                                                        (Total: {{ $item['total_murid'] }} Murid)
                                                    </span>
                                                </div>
                                            </div>
                                        @elseif ($item['is_bebas_kbm'])
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                                        <i class="bi bi-pause-circle-fill text-[9px]"></i> Bebas KBM
                                                    </span>
                                                    <span
                                                        class="text-[10px] text-amber-600/80 dark:text-amber-400/80 font-medium">
                                                        {{ $item['keterangan_bebas_kbm'] ?? 'Tidak ada presensi' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                                    <i class="bi bi-x-circle-fill text-[9px]"></i> Belum Absen
                                                </span>
                                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 italic">
                                                    Menunggu input guru
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-center">
                                        @if ($item['is_bebas_kbm'])
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 text-[10px] font-bold">
                                                <i class="bi bi-dash-circle"></i> Bebas KBM
                                            </span>
                                        @else
                                            @can('create presensi-murid')
                                                <div class="flex items-center justify-center gap-1">
                                                    @if ($isEventItem)
                                                        <a href="{{ route('presensi-murid.modalInput', ['tanggal' => $tanggal, 'kalendar_id' => $item['kalendar_id'], 'ruangan_id' => $item['ruangan']->id, 'sesi' => $item['sesi']]) }}"
                                                            data-refresh-target="#data-grid-container"
                                                            class="action-modal {{ $isSudah ? 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700' : 'm3-btn-primary !h-8 !px-3 !text-xs !bg-indigo-600 hover:!bg-indigo-700' }} inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black border transition-all active:scale-95 shadow-2xs">
                                                            <i
                                                                class="bi {{ $isSudah ? 'bi-pencil-square' : 'bi-plus-circle-fill' }} text-xs"></i>
                                                            <span>{{ $isSudah ? 'Edit' : 'Input' }}</span>
                                                        </a>
                                                        <a href="{{ route('presensi-murid.index', ['tanggal' => $tanggal, 'ruangan_id' => $item['ruangan']->id, 'sesi' => $item['sesi']]) }}"
                                                            class="w-7 h-7 flex items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 border border-zinc-200 dark:border-zinc-700 transition-colors"
                                                            title="Buka Halaman Form Penuh" target="_blank">
                                                            <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('presensi-murid.modalInput', ['tanggal' => $tanggal, 'jadwal_id' => $item['jadwal']->id]) }}"
                                                            data-refresh-target="#data-grid-container"
                                                            class="action-modal {{ $isSudah ? 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700' : 'm3-btn-primary !h-8 !px-3 !text-xs' }} inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black border transition-all active:scale-95 shadow-2xs">
                                                            <i
                                                                class="bi {{ $isSudah ? 'bi-pencil-square' : 'bi-plus-circle-fill' }} text-xs"></i>
                                                            <span>{{ $isSudah ? 'Edit' : 'Input' }}</span>
                                                        </a>
                                                        <a href="{{ route('presensi-murid.index', ['tanggal' => $tanggal, 'ruangan_id' => $item['jadwal']->ruangan_id, 'jam_ke' => $item['jadwal']->jam_ke]) }}"
                                                            class="w-7 h-7 flex items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:text-primary dark:hover:text-primary-dark border border-zinc-200 dark:border-zinc-700 transition-colors"
                                                            title="Buka Halaman Form Penuh" target="_blank">
                                                            <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</x-app-layout>
