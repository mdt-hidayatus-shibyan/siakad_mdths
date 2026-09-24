@section('title', 'Progres Harian - Presensi Ustadz')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-ustadz.menu')
        </div>

        <!-- Area Form Filter -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-ustadz.progres') }}" method="GET"
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

                <!-- Filter Status -->
                <div class="relative w-full sm:w-40 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-funnel text-xs"></i>
                    </div>
                    <select name="status" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" {{ empty($status_filter) ? 'selected' : '' }}>-- Semua Status --</option>
                        <option value="Hadir" {{ $status_filter === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="Sakit" {{ $status_filter === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Izin" {{ $status_filter === 'Izin' ? 'selected' : '' }}>Izin</option>
                        <option value="Alpha" {{ $status_filter === 'Alpha' ? 'selected' : '' }}>Alpha</option>
                        <option value="Badal" {{ $status_filter === 'Badal' ? 'selected' : '' }}>Digantikan (Badal)
                        </option>
                        <option value="Belum" {{ $status_filter === 'Belum' ? 'selected' : '' }}>Belum Absen</option>
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

    @if (!empty($isUjian))
        <!-- BANNER INFO JADWAL/MASA UJIAN -->
        <div
            class="mb-6 m3-glass-card p-4 md:p-5 border-violet-500/30 bg-violet-500/10 relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-2xl flex items-center justify-center text-xl shrink-0 border border-violet-500/30">
                    <i class="bi bi-card-checklist"></i>
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
                        Presensi mengajar reguler dialihkan ke Presensi Pengawas Ujian Madrasah.
                    </p>
                </div>
            </div>
            @can('create presensi-ujian')
                <a href="{{ route('presensi-ujian.input') }}"
                    class="m3-btn-primary h-9 px-4 text-xs font-black shadow-2xs whitespace-nowrap">
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
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-6 relative z-10">
            <!-- Card 1: Total Pengampu -->
            <div class="m3-glass-card p-3.5 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total
                        Jadwal</span>
                    <i class="bi bi-person-video3 text-zinc-400 text-sm"></i>
                </div>
                <h3 class="text-xl font-black text-zinc-900 dark:text-white leading-tight">{{ $totalPengampu }}</h3>
                <p class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500">Guru / Sesi</p>
            </div>

            <!-- Card 2: Hadir -->
            <div class="m3-glass-card p-3.5 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span
                        class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Hadir</span>
                    <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                </div>
                <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">{{ $totalHadir }}
                </h3>
                <p class="text-[9px] font-bold text-emerald-500/80">
                    {{ $totalPengampu > 0 ? round(($totalHadir / $totalPengampu) * 100, 1) : 0 }}% Masuk</p>
            </div>

            <!-- Card 3: Izin & Sakit -->
            <div class="m3-glass-card p-3.5 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span
                        class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider">Izin
                        /
                        Sakit</span>
                    <i class="bi bi-info-circle-fill text-amber-500 text-sm"></i>
                </div>
                <h3 class="text-xl font-black text-amber-600 dark:text-amber-400 leading-tight">
                    {{ $totalIzin + $totalSakit }}</h3>
                <p class="text-[9px] font-bold text-amber-500/80">{{ $totalSakit }} Sakit • {{ $totalIzin }} Izin
                </p>
            </div>

            <!-- Card 4: Badal / Pengganti -->
            <div class="m3-glass-card p-3.5 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black text-sky-600 dark:text-sky-400 uppercase tracking-wider">Badal
                        (Piket)</span>
                    <i class="bi bi-arrow-left-right text-sky-500 text-sm"></i>
                </div>
                <h3 class="text-xl font-black text-sky-600 dark:text-sky-400 leading-tight">{{ $totalBadal }}</h3>
                <p class="text-[9px] font-bold text-sky-500/80">Guru Pengganti</p>
            </div>

            <!-- Card 5: Alpha / Kosong -->
            <div class="m3-glass-card p-3.5 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider">Alpha
                        /
                        Kosong</span>
                    <i class="bi bi-exclamation-octagon-fill text-rose-500 text-sm"></i>
                </div>
                <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 leading-tight">
                    {{ $totalAlpha + $totalKosong }}</h3>
                <p class="text-[9px] font-bold text-rose-500/80">{{ $totalAlpha }} Alpha • {{ $totalKosong }}
                    Kosong
                </p>
            </div>

            <!-- Card 6: Belum Absen -->
            <div class="m3-glass-card p-3.5 flex flex-col justify-center shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Belum
                        Absen</span>
                    <i class="bi bi-hourglass-split text-zinc-400 text-sm"></i>
                </div>
                <h3 class="text-xl font-black text-zinc-700 dark:text-zinc-300 leading-tight">{{ $totalBelum }}</h3>
                <p class="text-[9px] font-bold text-zinc-400">Menunggu Check-In</p>
            </div>
        </div>

        <!-- TABEL DETAIL MONITORING PROGRES USTADZ -->
        <div class="m3-glass-card overflow-hidden relative z-10 shadow-2xs">
            <!-- Header Tabel -->
            <div
                class="bg-zinc-100/50 dark:bg-zinc-800/40 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-4 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                <div>
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-tight uppercase">
                        Monitoring Presensi Ustadz — <span
                            class="text-primary dark:text-primary-dark">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
                    </h3>
                    <p
                        class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider flex items-center mt-0.5">
                        <i class="bi bi-info-circle-fill mr-1.5 opacity-70"></i> Pantauan status check-in kehadiran
                        seluruh
                        dewan asatidz hari ini.
                    </p>
                </div>
            </div>

            @if (empty($detailProgres))
                <div class="p-8">
                    <x-empty-state icon="bi-calendar-x" title="Tidak Ada Jadwal"
                        message="Tidak ditemukan jadwal mengajar yang aktif pada hari {{ $hari_ini }} atau parameter filter yang Anda tentukan." />
                </div>
            @else
                <div class="overflow-x-auto relative z-10 custom-scrollbar">
                    <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                        <thead
                            class="bg-zinc-100/70 dark:bg-zinc-800/50 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                            <tr
                                class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                <th
                                    class="py-3 px-4 w-12 text-center border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    No</th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">Guru Pengampu
                                </th>
                                <th class="py-3 px-4 w-44 border-r border-zinc-200/60 dark:border-zinc-800/60">Ruangan
                                    &
                                    Jam</th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">Mata
                                    Pelajaran
                                </th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">Status
                                    Kehadiran
                                </th>
                                <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">Check-In /
                                    Badal
                                </th>
                                <th class="py-3 px-4 w-28 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/60">
                            @php $no = 1; @endphp
                            @foreach ($detailProgres as $item)
                                @php
                                    $j = $item['jadwal'];
                                    $u = $item['ustadz'];
                                    $p = $item['presensi'];
                                    $isUtama = $item['is_utama'];
                                    $status = $item['status'];
                                    $jamText = match ($j->jam_ke) {
                                        'Nadzoman' => '13:45 - 14:00',
                                        '1' => '14:00 - 14:45',
                                        '2' => '15:30 - 16:15',
                                        'Ekstra' => '20:00 - 21:00',
                                        default => 'Jam Ke-' . $j->jam_ke,
                                    };

                                    $badgeColor = match ($status) {
                                        'Hadir'
                                            => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                        'Sakit' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                        'Izin'
                                            => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                        'Alpha' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                        'Kosong'
                                            => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                        default
                                            => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 border-zinc-200/50',
                                    };
                                @endphp
                                <tr
                                    class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors {{ $p ? '' : 'bg-rose-500/[0.02]' }}">
                                    <!-- No -->
                                    <td
                                        class="py-3 px-4 text-center font-bold text-zinc-500 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        {{ $no++ }}
                                    </td>

                                    <!-- Guru Pengampu -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-primary/10 text-primary dark:text-primary-dark font-black text-xs flex items-center justify-center shrink-0 border border-primary/20">
                                                {{ strtoupper(substr($u->nama_lengkap, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div
                                                    class="font-black text-zinc-900 dark:text-white text-xs flex items-center gap-1.5">
                                                    <span>{{ $u->nama_lengkap }}</span>
                                                    @if ($isUtama)
                                                        <span
                                                            class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[8px] font-black uppercase"
                                                            title="Guru Utama">
                                                            <i class="bi bi-star-fill text-[7px]"></i> Utama
                                                        </span>
                                                    @else
                                                        <span
                                                            class="px-1.5 py-0.2 rounded bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 text-[8px] font-black uppercase"
                                                            title="Guru Pendamping">
                                                            Pendamping
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($u->no_hp)
                                                    <div
                                                        class="text-[10px] text-zinc-400 dark:text-zinc-500 font-medium">
                                                        <i class="bi bi-telephone mr-0.5"></i> {{ $u->no_hp }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Ruangan & Jam -->
                                    <td class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        <div class="font-black text-zinc-900 dark:text-white text-xs">
                                            {{ $j->ruangan->nama_ruangan ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[9px] font-black text-zinc-600 dark:text-zinc-400 uppercase">
                                                Jam {{ $j->jam_ke }}
                                            </span>
                                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold">
                                                {{ $jamText }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Mata Pelajaran -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        <div class="font-black text-zinc-900 dark:text-white text-xs">
                                            {{ $j->mataPelajaran->nama_mapel ?? '-' }}
                                        </div>
                                        @if ($j->ruangan?->level)
                                            <div class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 mt-0.5">
                                                Level: {{ $j->ruangan->level->nama_level }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Kehadiran -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border shadow-2xs {{ $badgeColor }}">
                                                {{ $status }}
                                            </span>
                                        </div>
                                        @if ($p && $p->keterangan)
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 italic mt-1 truncate max-w-[200px]"
                                                title="{{ $p->keterangan }}">
                                                "{{ $p->keterangan }}"
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Badal / Info Checkin -->
                                    <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">
                                        @if ($item['guru_pengganti'])
                                            <div
                                                class="flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-500/10 border border-sky-500/20 px-2.5 py-1 rounded-lg">
                                                <i class="bi bi-arrow-left-right text-[10px]"></i>
                                                <span>Badal: {{ $item['guru_pengganti']->nama_lengkap }}</span>
                                            </div>
                                        @elseif ($p)
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 font-medium">
                                                @if ($item['waktu_input'])
                                                    <div><i class="bi bi-clock mr-1"></i> {{ $item['waktu_input'] }}
                                                        WIB
                                                    </div>
                                                @endif
                                                @if ($item['diinput_oleh'])
                                                    <div class="text-zinc-400 dark:text-zinc-500 text-[9px] mt-0.5">
                                                        Oleh: {{ $item['diinput_oleh'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 italic">
                                                Belum ada data
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-center">
                                        @can('create presensi-ustadz')
                                            <a href="{{ route('presensi-ustadz.modalInput', ['tanggal' => $tanggal, 'jadwal_id' => $j->id, 'ustadz_id' => $u->id]) }}"
                                                data-refresh-target="#data-grid-container"
                                                class="action-modal {{ $p ? 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700' : 'bg-primary hover:bg-primary/90 text-white border-primary shadow-2xs' }} inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black border transition-all active:scale-95">
                                                <i class="bi {{ $p ? 'bi-pencil-square' : 'bi-check-lg' }} text-xs"></i>
                                                <span>{{ $p ? 'Edit' : 'Absen' }}</span>
                                            </a>
                                        @endcan
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
