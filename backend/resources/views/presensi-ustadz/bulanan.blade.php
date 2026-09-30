@section('title', 'Bulanan - Presensi Ustadz')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-start justify-between gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-ustadz.menu')
        </div>

        <!-- Area Form Pencarian (Sejajar dengan Menu) -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-ustadz.bulanan') }}" method="GET"
                class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto" id="formFilter">

                <!-- Filter Bulan -->
                <div class="w-full sm:flex-1">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-moon-stars-fill text-xs"></i>
                        </div>
                        <select name="bulan_id" required
                            class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="text-zinc-500">-- Pilih Bulan --</option>
                            @foreach ($bulans as $b)
                                <option value="{{ $b->id }}" {{ $bulan_id == $b->id ? 'selected' : '' }}>
                                    {{ $b->nama_bulan }} {{ $b->tahun_hijriyah }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="w-full sm:flex-1">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-door-open-fill text-xs"></i>
                        </div>
                        <select name="ruangan_id" required
                            class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="text-zinc-500">-- Pilih Ruangan --</option>
                            @foreach ($ruangans as $ruangan)
                                <option value="{{ $ruangan->id }}" {{ $ruangan_id == $ruangan->id ? 'selected' : '' }}>
                                    {{ $ruangan->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit"
                        class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5">
                        <i class="bi bi-search"></i> <span>Tampilkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AREA HASIL TABEL -->
    @if ($bulan_id && $ruangan_id)
        <div id="data-grid-container">
            <div class="m3-glass-card overflow-hidden relative z-10 shadow-2xs">

                <!-- Header Leger -->
                <div
                    class="bg-zinc-100/50 dark:bg-zinc-800/40 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-3.5 flex flex-col md:flex-row justify-between md:items-center gap-2 relative z-10">
                    <div>
                        <h3
                            class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-tight uppercase mb-0.5">
                            Bulan: <span
                                class="text-primary dark:text-primary-dark">{{ $bulanTerpilih->nama_bulan }}</span>
                        </h3>
                        <p
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider flex items-center">
                            <i class="bi bi-info-circle-fill mr-1.5 opacity-70"></i> Menampilkan jadwal & presensi
                            dewan asatidz harian.
                        </p>
                    </div>
                </div>

                @php
                    $eventDatesInMonth = collect($dates)->where('is_event', true);
                    $examDatesInMonth = collect($dates)->where('is_ujian', true);
                @endphp

                @if ($eventDatesInMonth->isNotEmpty())
                    <div
                        class="m-4 p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 text-indigo-800 dark:text-indigo-300">
                            <div class="w-8 h-8 rounded-xl bg-indigo-500/20 flex items-center justify-center shrink-0">
                                <i class="bi bi-calendar-event-fill text-sm text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div class="text-xs font-bold leading-relaxed">
                                Terdapat agenda <strong>Hari Efektif Non-KBM / Event Kegiatan</strong> pada bulan ini.
                                Presensi dewan asatidz tercatat per sesi agenda kegiatan.
                            </div>
                        </div>
                        <a href="{{ route('kalendar-pendidikan.index') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-xs transition-colors shrink-0">
                            <i class="bi bi-calendar3"></i>
                            <span>Lihat Agenda Kalender</span>
                        </a>
                    </div>
                @endif

                @if ($examDatesInMonth->isNotEmpty())
                    <div
                        class="m-4 p-3.5 rounded-2xl bg-violet-500/10 border border-violet-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 text-violet-800 dark:text-violet-300">
                            <div class="w-8 h-8 rounded-xl bg-violet-500/20 flex items-center justify-center shrink-0">
                                <i class="bi bi-card-checklist text-sm text-violet-600 dark:text-violet-400"></i>
                            </div>
                            <div class="text-xs font-bold leading-relaxed">
                                Terdapat tanggal pelaksanaan ujian madrasah pada bulan ini. Presensi mengajar reguler
                                dialihkan ke <strong>Presensi Pengawas Ujian</strong>.
                            </div>
                        </div>
                        @can('create presensi-ujian')
                            <a href="{{ route('presensi-ujian.input') }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-black text-xs shadow-xs transition-colors shrink-0">
                                <i class="bi bi-arrow-right-circle"></i>
                                <span>Buka Presensi Ujian</span>
                            </a>
                        @endcan
                    </div>
                @endif

                <!-- Tabel Data -->
                <div class="overflow-x-auto relative z-10 custom-scrollbar p-0">
                    <table class="w-full text-left text-xs border-collapse min-w-[1000px]">
                        <thead
                            class="bg-zinc-100/70 dark:bg-zinc-800/50 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                            <tr
                                class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                <th
                                    class="py-3 px-4 w-16 text-center border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    Tgl</th>
                                <th
                                    class="py-3 px-4 w-28 text-center border-r border-zinc-200/60 dark:border-zinc-800/60">
                                    Hari</th>
                                <th class="py-3 px-5 w-1/3 border-r border-zinc-200/60 dark:border-zinc-800/60">Jam
                                    Nadzoman
                                </th>
                                <th class="py-3 px-5 w-1/3 border-r border-zinc-200/60 dark:border-zinc-800/60">Jam Ke-1
                                </th>
                                <th class="py-3 px-5 w-1/3 border-r border-zinc-200/60 dark:border-zinc-800/60">Jam Ke-2
                                </th>
                                <th class="py-3 px-5 w-1/3">Jam Ekstra</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/60">
                            @php $noTgl = 1; @endphp
                            @foreach ($dates as $tglMasehi => $info)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group/row">

                                    <!-- Tanggal -->
                                    <td
                                        class="py-3 px-4 text-center border-r border-zinc-200/60 dark:border-zinc-800/60 align-middle">
                                        <div class="font-black text-zinc-800 dark:text-zinc-200 text-sm">
                                            {{ $noTgl++ }}</div>
                                        <div class="text-[9px] text-zinc-400 dark:text-zinc-500 mt-0.5 font-bold">
                                            {{ date('d/m', strtotime($tglMasehi)) }}</div>
                                    </td>

                                    <!-- Hari -->
                                    <td
                                        class="py-3 px-4 text-center border-r border-zinc-200/60 dark:border-zinc-800/60 align-middle font-black text-zinc-500 dark:text-zinc-400 text-[10px] uppercase tracking-wider">
                                        {{ substr($info['hari'], 0, 3) }}
                                    </td>

                                    <!-- Data Jadwal (3 Kolom Jam) -->
                                    @if ($info['is_libur'])
                                        <!-- HARI LIBUR -->
                                        <td colspan="4" class="py-3 px-5 bg-rose-500/5 align-middle">
                                            <div
                                                class="flex items-center justify-center gap-2 text-rose-500 dark:text-rose-400 font-black text-[11px] uppercase tracking-wider">
                                                <i class="bi bi-brightness-high-fill"></i>
                                                {{ $info['keterangan_libur'] }}
                                            </div>
                                        </td>
                                    @elseif (!empty($info['is_ujian']))
                                        <!-- MASA UJIAN -->
                                        <td colspan="4"
                                            class="py-3 px-5 bg-violet-500/5 dark:bg-violet-500/10 align-middle">
                                            <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5">
                                                <div
                                                    class="flex items-center gap-2 text-violet-600 dark:text-violet-400 font-black text-[11px] uppercase tracking-wider">
                                                    <i class="bi bi-card-checklist text-sm"></i>
                                                    <span>Masa Ujian: {{ $info['nama_ujian'] }} (Dialihkan ke Presensi
                                                        Ujian)</span>
                                                </div>
                                                @can('create presensi-ujian')
                                                    <a href="{{ route('presensi-ujian.input') }}"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-black text-[10px] tracking-wide shadow-2xs transition-colors shrink-0">
                                                        <i class="bi bi-arrow-right-circle"></i>
                                                        <span>Buka Presensi Ujian</span>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    @elseif (!empty($info['is_event']))
                                        <!-- AGENDA EVENT / HARI EFEKTIF NON-KBM -->
                                        <td colspan="4"
                                            class="py-3.5 px-5 bg-indigo-500/[0.04] dark:bg-indigo-500/[0.08] align-top">
                                            <div class="flex flex-col gap-3">
                                                <!-- Event Header -->
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-indigo-500/20">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="w-6 h-6 rounded-lg bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black shrink-0">
                                                            <i class="bi bi-calendar-event-fill"></i>
                                                        </div>
                                                        <span
                                                            class="font-black text-xs text-indigo-950 dark:text-indigo-200 uppercase tracking-tight">
                                                            {{ $info['nama_event'] }}
                                                        </span>
                                                        <span
                                                            class="px-2 py-0.5 rounded-md bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-extrabold text-[9px] uppercase">
                                                            {{ $info['tipe_presensi'] === 'multi_sesi' ? 'Kegiatan Multi-Sesi' : 'Hari Efektif Non-KBM' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Grid Sesi Kegiatan -->
                                                @php
                                                    $eventData = $matrix[$tglMasehi]['event'] ?? null;
                                                    $sesiData = $eventData['sesi_data'] ?? [];
                                                @endphp

                                                <div
                                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ max(1, min(count($sesiData), 3)) }} gap-3">
                                                    @foreach ($sesiData as $sesiName => $sData)
                                                        <div
                                                            class="p-2.5 rounded-xl border border-indigo-500/20 bg-white/60 dark:bg-zinc-900/60 shadow-2xs space-y-2">
                                                            <div
                                                                class="flex items-center justify-between gap-1 pb-1.5 border-b border-zinc-200/60 dark:border-zinc-800">
                                                                <span
                                                                    class="text-[10px] font-black text-indigo-700 dark:text-indigo-300 uppercase tracking-wider flex items-center gap-1">
                                                                    <i class="bi bi-clock-fill text-[9px]"></i> Sesi
                                                                    {{ $sesiName }}
                                                                </span>
                                                                <span class="text-[9px] font-bold text-zinc-400">
                                                                    {{ count(array_filter($sData['daftar_pengampu'], fn($item) => !empty($item['presensi']))) }}
                                                                    / {{ count($sData['daftar_pengampu']) }} Hadir
                                                                </span>
                                                            </div>

                                                            <!-- List Guru di Sesi Ini -->
                                                            <div
                                                                class="space-y-1.5 max-h-48 overflow-y-auto custom-scrollbar pr-1">
                                                                @foreach ($sData['daftar_pengampu'] as $pUstadz)
                                                                    @php
                                                                        $p = $pUstadz['presensi'];
                                                                        $uId = $pUstadz['ustadz_id'];
                                                                        $uNama = $pUstadz['nama_lengkap'];
                                                                    @endphp
                                                                    <div
                                                                        class="p-1.5 rounded-lg bg-zinc-50/80 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800 relative group/subcell flex items-center justify-between gap-2">
                                                                        <div class="truncate min-w-0">
                                                                            <p class="text-[10px] font-bold text-zinc-800 dark:text-zinc-200 truncate"
                                                                                title="{{ $uNama }}">
                                                                                {{ $uNama }}
                                                                            </p>
                                                                        </div>

                                                                        <div
                                                                            class="flex items-center gap-1.5 shrink-0">
                                                                            @if ($p)
                                                                                @php
                                                                                    $badgeColor = match ($p->status) {
                                                                                        'Hadir'
                                                                                            => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                                                                        'Sakit'
                                                                                            => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                                                                        'Izin'
                                                                                            => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                                                        'Alpha'
                                                                                            => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                                                                        default
                                                                                            => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                                                                                    };
                                                                                @endphp
                                                                                <span
                                                                                    class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase tracking-wider border {{ $badgeColor }}">
                                                                                    {{ $p->status }}
                                                                                </span>
                                                                                @can('hapus presensi-ustadz')
                                                                                    <form
                                                                                        action="{{ route('presensi-ustadz.destroyHarian', $p->id) }}"
                                                                                        method="POST"
                                                                                        class="delete-ajax inline-block"
                                                                                        data-refresh-target="#data-grid-container">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit"
                                                                                            class="w-4 h-4 flex items-center justify-center rounded bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-500 transition-colors outline-none"
                                                                                            title="Batal / Hapus">
                                                                                            <i
                                                                                                class="bi bi-trash3-fill text-[7px]"></i>
                                                                                        </button>
                                                                                    </form>
                                                                                @endcan
                                                                            @else
                                                                                <span
                                                                                    class="px-1.5 py-0.2 rounded text-[8px] font-bold uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-400 border border-transparent">
                                                                                    Belum
                                                                                </span>
                                                                            @endif

                                                                            @can('create presensi-ustadz')
                                                                                <a href="{{ route('presensi-ustadz.modalInput', ['tanggal' => $tglMasehi, 'kalendar_id' => $info['event_info']->id, 'ustadz_id' => $uId, 'sesi' => $sesiName]) }}"
                                                                                    data-refresh-target="#data-grid-container"
                                                                                    class="action-modal opacity-0 group-hover/subcell:opacity-100 transition-all duration-200 w-4 h-4 flex items-center justify-center bg-white dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 rounded shadow-2xs border border-zinc-200 dark:border-zinc-700 active:scale-95 outline-none"
                                                                                    title="Isi / Edit Presensi {{ $uNama }}">
                                                                                    <i
                                                                                        class="bi bi-pencil-square text-[8px]"></i>
                                                                                </a>
                                                                            @endcan
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        @foreach ($jamList as $jam)
                                            <td
                                                class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60 align-top relative group/cell hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 transition-colors last:border-r-0">
                                                @php $selData = $matrix[$tglMasehi][$jam]; @endphp

                                                @if (!$selData['is_jadwal'])
                                                    <!-- KOSONG -->
                                                    <div class="flex items-center justify-center h-full min-h-[40px]">
                                                        <span
                                                            class="text-zinc-300 dark:text-zinc-700 font-black text-sm opacity-50">-</span>
                                                    </div>
                                                @else
                                                    <!-- ADA JADWAL -->
                                                    <div class="flex flex-col gap-2 relative">
                                                        <!-- Judul Mapel -->
                                                        <div class="text-xs font-black text-zinc-900 dark:text-white leading-tight tracking-tight truncate pb-1 border-b border-zinc-200/60 dark:border-zinc-800"
                                                            title="{{ $selData['mapel'] }}">
                                                            {{ $selData['mapel'] }}
                                                        </div>

                                                        <!-- Daftar Pengampu (Utama & Pendamping) -->
                                                        <div class="flex flex-col gap-1.5">
                                                            @foreach ($selData['daftar_pengampu'] as $pUstadz)
                                                                @php
                                                                    $p = $pUstadz['presensi'];
                                                                    $uId = $pUstadz['ustadz_id'];
                                                                    $uNama = $pUstadz['nama_lengkap'];
                                                                    $isUtama = $pUstadz['is_utama'];
                                                                @endphp
                                                                <div
                                                                    class="p-1.5 rounded-lg bg-zinc-50/80 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800 relative group/subcell">
                                                                    <div
                                                                        class="flex items-center justify-between gap-1 pr-5">
                                                                        <div class="text-[9px] font-bold text-zinc-700 dark:text-zinc-300 truncate flex items-center gap-1"
                                                                            title="{{ $uNama }}">
                                                                            @if ($isUtama)
                                                                                <i class="bi bi-star-fill text-amber-500 text-[8px]"
                                                                                    title="Guru Utama"></i>
                                                                            @else
                                                                                <span
                                                                                    class="px-1 py-0.2 text-[7px] font-black rounded bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20"
                                                                                    title="Guru Pendamping">P</span>
                                                                            @endif
                                                                            <span
                                                                                class="truncate">{{ $uNama }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Tombol Edit Presensi Ustadz ini -->
                                                                    @can('create presensi-ustadz')
                                                                        @if (empty($pUstadz['is_bebas_kbm']) && empty($selData['is_bebas_kbm']))
                                                                            <a href="{{ route('presensi-ustadz.modalInput', ['tanggal' => $tglMasehi, 'jadwal_id' => $selData['jadwal_id'], 'ustadz_id' => $uId]) }}"
                                                                                data-refresh-target="#data-grid-container"
                                                                                class="action-modal opacity-0 group-hover/subcell:opacity-100 transition-all duration-200 absolute right-1 top-1 w-5 h-5 flex items-center justify-center bg-white dark:bg-zinc-800 text-primary dark:text-primary-dark rounded shadow-2xs border border-zinc-200 dark:border-zinc-700 active:scale-95 outline-none"
                                                                                title="Isi / Edit Presensi {{ $uNama }}">
                                                                                <i
                                                                                    class="bi bi-pencil-square text-[9px]"></i>
                                                                            </a>
                                                                        @endif
                                                                    @endcan

                                                                    <!-- Status Presensi Ustadz ini -->
                                                                    <div
                                                                        class="mt-1 pt-1 border-t border-zinc-200/40 dark:border-zinc-800/60 border-dashed flex items-center justify-between gap-1">
                                                                        @if ($p)
                                                                            @php
                                                                                $badgeColor = match ($p->status) {
                                                                                    'Hadir'
                                                                                        => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                                                                    'Sakit'
                                                                                        => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                                                                    'Izin'
                                                                                        => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                                                    'Alpha'
                                                                                        => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                                                                    'Kosong'
                                                                                        => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                                                                    default
                                                                                        => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                                                                                };
                                                                            @endphp
                                                                            <div
                                                                                class="flex items-center gap-1 w-full overflow-hidden">
                                                                                <span
                                                                                    class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider border shadow-2xs {{ $badgeColor }}">
                                                                                    {{ $p->status }}
                                                                                </span>
                                                                                @if ($p->guruPengganti)
                                                                                    <span
                                                                                        class="text-[8px] text-amber-600 dark:text-amber-400 truncate font-semibold"
                                                                                        title="Badal: {{ $p->guruPengganti->nama_lengkap }}">
                                                                                        (Badal:
                                                                                        {{ $p->guruPengganti->nama_lengkap }})
                                                                                    </span>
                                                                                @elseif($p->keterangan)
                                                                                    <span
                                                                                        class="text-[8px] text-zinc-400 dark:text-zinc-500 italic truncate"
                                                                                        title="{{ $p->keterangan }}">
                                                                                        {{ $p->keterangan }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>

                                                                            @can('hapus presensi-ustadz')
                                                                                <form
                                                                                    action="{{ route('presensi-ustadz.destroyHarian', $p->id) }}"
                                                                                    method="POST"
                                                                                    class="delete-ajax inline-block flex-shrink-0"
                                                                                    data-refresh-target="#data-grid-container">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit"
                                                                                        class="w-4 h-4 flex items-center justify-center rounded bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-500 transition-colors outline-none"
                                                                                        title="Batal / Hapus">
                                                                                        <i
                                                                                            class="bi bi-trash3-fill text-[8px]"></i>
                                                                                    </button>
                                                                                </form>
                                                                            @endcan
                                                                        @elseif (!empty($pUstadz['is_bebas_kbm']))
                                                                            <span
                                                                                class="px-1.5 py-0.5 inline-block rounded text-[8px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20"
                                                                                title="{{ $pUstadz['keterangan_bebas_kbm'] ?? 'Bebas KBM' }}">
                                                                                Bebas KBM
                                                                            </span>
                                                                        @else
                                                                            <span
                                                                                class="px-1.5 py-0.5 inline-block rounded text-[8px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 border border-transparent">
                                                                                Belum Absen
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- State Awal saat halaman baru dibuka -->
        <div class="col-span-full">
            <x-empty-state icon="bi-door-open" title="Pilih Parameter"
                message="Tentukan Bulan dan Ruangan pada filter di atas untuk melihat rekapitulasi kehadiran dan jadwal Badal Asatidz." />
        </div>
    @endif
</x-app-layout>
