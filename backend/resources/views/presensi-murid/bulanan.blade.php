@section('title', 'Bulanan - Presensi Murid')

<x-app-layout>
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10">
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-murid.menu')
        </div>

        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-murid.bulanan') }}" method="GET"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs">

                <!-- Filter Bulan -->
                <div class="w-full sm:w-52 group/select">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                            <i class="bi bi-moon-stars text-xs"></i>
                        </div>
                        <select name="bulan_id" required
                            class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Bulan --
                            </option>
                            @foreach ($bulans as $b)
                                <option value="{{ $b->id }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                    {{ $bulan_id == $b->id ? 'selected' : '' }}>
                                    {{ $b->urutan }}. {{ $b->nama_bulan }} {{ $b->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="w-full sm:w-48 group/select">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                            <i class="bi bi-door-open text-xs"></i>
                        </div>
                        <select name="ruangan_id" required
                            class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Ruangan --
                            </option>
                            @foreach ($ruangans as $r)
                                <option value="{{ $r->id }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                    {{ $ruangan_id == $r->id ? 'selected' : '' }}>
                                    {{ $r->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Jam -->
                <div class="w-full sm:w-36 group/select shrink-0">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                            <i class="bi bi-clock-history text-xs"></i>
                        </div>
                        <select name="jam_ke" required
                            class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Jam --</option>
                            @foreach ($jamList as $j)
                                <option value="{{ $j }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                    {{ $jam_ke == $j ? 'selected' : '' }}>
                                    Jam {{ $j }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <!-- Tombol Tampilkan -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs group/btn">
                        <i class="bi bi-search text-xs mr-1"></i>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($bulan_id && $ruangan_id && $jam_ke)
        @if ($murids->isEmpty())
            <!-- STATE KELAS KOSONG -->
            <div class="py-16 text-center m3-glass-card relative z-10">
                <div
                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Kelas Kosong</h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                    Tidak ada murid aktif di kelas ini pada periode bulan terpilih.
                </p>
            </div>
        @else
            <form action="{{ route('presensi-murid.store') }}" method="POST" class="relative">
                @csrf
                <input type="hidden" name="bulan_id" value="{{ $bulan_id }}">
                <input type="hidden" name="ruangan_id" value="{{ $ruangan_id }}">
                <input type="hidden" name="jam_ke" value="{{ $jam_ke }}">

                <!-- WRAPPER TABEL LEGER -->
                <div class="m3-glass-card overflow-hidden mb-6 relative">

                    <!-- Header Label -->
                    <div
                        class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-4 flex flex-col sm:flex-row justify-between sm:items-center gap-2 relative z-10">
                        <div>
                            <h3
                                class="font-black text-zinc-900 dark:text-white text-sm md:text-base uppercase tracking-tight">
                                Presensi Murid Jam {{ $jam_ke }} : Bulan {{ $bulanTerpilih->nama_bulan }}
                            </h3>
                            <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-0.5">
                                Ketik: <span class="text-emerald-600 dark:text-emerald-400 font-black">H</span> (Hadir),
                                <span class="text-blue-600 dark:text-blue-400 font-black">S</span> (Sakit),
                                <span class="text-amber-600 dark:text-amber-400 font-black">I</span> (Izin),
                                <span class="text-rose-600 dark:text-rose-400 font-black">A</span> (Alpha),
                                <span class="text-purple-600 dark:text-purple-400 font-black">D</span> (Dispensasi).
                                Kosongkan untuk menghapus data.
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
                                <div
                                    class="w-8 h-8 rounded-xl bg-indigo-500/20 flex items-center justify-center shrink-0">
                                    <i
                                        class="bi bi-calendar-event-fill text-sm text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div class="text-xs font-bold leading-relaxed">
                                    Terdapat agenda <strong>Hari Efektif Non-KBM / Event Kegiatan</strong> pada bulan
                                    ini. Kolom bertanda <span
                                        class="text-indigo-600 dark:text-indigo-400 font-black">Event</span> mencatat
                                    presensi kegiatan madrasah.
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
                                <div
                                    class="w-8 h-8 rounded-xl bg-violet-500/20 flex items-center justify-center shrink-0">
                                    <i class="bi bi-card-checklist text-sm text-violet-600 dark:text-violet-400"></i>
                                </div>
                                <div class="text-xs font-bold leading-relaxed">
                                    Terdapat tanggal pelaksanaan ujian madrasah pada bulan ini. Presensi pada tanggal
                                    tersebut dinonaktifkan di KBM reguler dan dialihkan ke <strong>Presensi
                                        Ujian</strong>.
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

                    <!-- AREA SCROLL TABEL -->
                    <div class="overflow-auto max-h-[600px] custom-scrollbar relative z-0">
                        <table class="w-full text-left border-collapse min-w-max">
                            <!-- HEADER TABEL -->
                            <thead>
                                <tr
                                    class="bg-zinc-100/80 dark:bg-zinc-900/90 border-b border-zinc-200/80 dark:border-zinc-800">
                                    <!-- Sticky Nama Murid -->
                                    <th
                                        class="p-2.5 text-center text-[11px] font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-wider sticky top-0 left-0 bg-zinc-100 dark:bg-zinc-900 border-r border-b border-zinc-200 dark:border-zinc-800 w-12 shadow-2xs z-30">
                                        No
                                    </th>
                                    <th
                                        class="p-2.5 text-[11px] font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-wider sticky top-0 left-12 bg-zinc-100 dark:bg-zinc-900 border-r border-b border-zinc-200 dark:border-zinc-800 w-56 md:w-64 shadow-2xs z-30">
                                        Nama Murid
                                    </th>

                                    <!-- Iterasi Tanggal -->
                                    @foreach ($dates as $tgl => $data)
                                        @if ($data['hari'] == 'Jumat' || $data['is_libur_madrasah'])
                                            <th
                                                class="p-2 text-center sticky top-0 z-20 border-r border-b border-zinc-200 dark:border-zinc-800 min-w-[48px] bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                                <div class="text-[9px] uppercase font-bold opacity-75">
                                                    {{ substr($data['hari'], 0, 3) }}</div>
                                                <div class="text-xs font-black">{{ $tgl }}</div>
                                                <div class="text-[8px] font-black text-rose-500 dark:text-rose-400 mt-0.5 uppercase tracking-wider truncate max-w-[45px] mx-auto"
                                                    title="{{ $data['is_libur_madrasah'] ? $data['keterangan_libur'] : 'Libur Jumat' }}">
                                                    {{ $data['is_libur_madrasah'] ? 'Libur' : 'Jum' }}
                                                </div>
                                            </th>
                                        @elseif (!empty($data['is_ujian']))
                                            <th class="p-2 text-center sticky top-0 z-20 border-r border-b border-zinc-200 dark:border-zinc-800 min-w-[48px] bg-violet-500/10 text-violet-600 dark:text-violet-400"
                                                title="Masa Ujian: {{ $data['nama_ujian'] }} (Dialihkan ke Presensi Ujian)">
                                                <div class="text-[9px] uppercase font-bold opacity-75">
                                                    {{ substr($data['hari'], 0, 3) }}</div>
                                                <div class="text-xs font-black">{{ $tgl }}</div>
                                                <div class="text-[8px] font-black text-violet-600 dark:text-violet-400 mt-0.5 uppercase tracking-wider truncate max-w-[45px] mx-auto"
                                                    title="{{ $data['nama_ujian'] }}">
                                                    Ujian
                                                </div>
                                            </th>
                                        @elseif (!empty($data['is_event']))
                                            <th class="p-2 text-center sticky top-0 z-20 border-r border-b border-zinc-200 dark:border-zinc-800 min-w-[48px] bg-indigo-500/10 text-indigo-600 dark:text-indigo-400"
                                                title="Event / Non-KBM: {{ $data['nama_event'] }}">
                                                <div class="text-[9px] uppercase font-bold opacity-75">
                                                    {{ substr($data['hari'], 0, 3) }}</div>
                                                <div class="text-xs font-black">{{ $tgl }}</div>
                                                <div class="text-[8px] font-black text-indigo-600 dark:text-indigo-400 mt-0.5 uppercase tracking-wider truncate max-w-[45px] mx-auto"
                                                    title="{{ $data['nama_event'] }}">
                                                    Event
                                                </div>
                                            </th>
                                        @else
                                            <th
                                                class="p-2 text-center sticky top-0 z-20 border-r border-b border-zinc-200 dark:border-zinc-800 min-w-[48px] {{ $data['is_jadwal'] ? 'bg-zinc-100/90 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300' : 'bg-zinc-200/40 dark:bg-zinc-950 text-zinc-400 dark:text-zinc-600' }}">
                                                <div class="text-[9px] uppercase font-bold opacity-75">
                                                    {{ substr($data['hari'], 0, 3) }}</div>
                                                <div class="text-xs font-black">{{ $tgl }}</div>
                                                @if ($data['is_jadwal'])
                                                    <div class="text-[8px] font-bold text-primary dark:text-primary-dark truncate max-w-[45px] mx-auto mt-0.5"
                                                        title="{{ $data['mapel'] }}">
                                                        {{ substr($data['mapel'], 0, 8) }}</div>
                                                @else
                                                    <div
                                                        class="text-[8px] font-medium text-zinc-400 dark:text-zinc-600 mt-0.5 italic">
                                                        Ksg</div>
                                                @endif
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>

                            <!-- BODY TABEL -->
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                @foreach ($murids as $murid)
                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                        <td
                                            class="p-2.5 text-center sticky left-0 bg-white dark:bg-zinc-900 z-10 border-r border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                                            <span
                                                class="w-6 h-6 mx-auto flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-md text-[11px] font-black">
                                                {{ $loop->iteration }}
                                            </span>
                                        </td>
                                        <!-- Sticky Kolom Nama -->
                                        <td
                                            class="p-2.5 sticky left-12 bg-white dark:bg-zinc-900 z-10 border-r border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                                            <div class="font-black text-xs text-zinc-900 dark:text-white truncate max-w-[200px] md:max-w-[240px]"
                                                title="{{ $murid->nama_lengkap }}">{{ $murid->nama_lengkap }}</div>
                                            <div
                                                class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 mt-0.5 tracking-wider uppercase font-mono">
                                                {{ $murid->nism ?? '-' }}</div>
                                        </td>

                                        <!-- Iterasi Input Matrix -->
                                        @foreach ($dates as $tgl => $data)
                                            @if ($data['hari'] == 'Jumat' || $data['is_libur_madrasah'])
                                                <!-- Sel Libur -->
                                                <td class="p-1 text-center border-r border-zinc-100 dark:border-zinc-800/60 align-middle bg-rose-500/5"
                                                    title="{{ $data['is_libur_madrasah'] ? $data['keterangan_libur'] : 'Libur Jumat' }}">
                                                    <div
                                                        class="w-8 h-7 mx-auto rounded flex items-center justify-center text-xs font-black text-rose-300 dark:text-rose-500/50 select-none">
                                                        <i class="bi bi-x"></i>
                                                    </div>
                                                </td>
                                            @elseif (!empty($data['is_ujian']))
                                                <!-- Sel Ujian (Dialihkan) -->
                                                <td class="p-1 text-center border-r border-zinc-100 dark:border-zinc-800/60 align-middle bg-violet-500/5 dark:bg-violet-500/10"
                                                    title="Masa Ujian: {{ $data['nama_ujian'] }} (Dialihkan ke Presensi Ujian)">
                                                    <div
                                                        class="w-8 h-7 mx-auto rounded flex items-center justify-center text-[10px] font-black text-violet-600 dark:text-violet-400 select-none bg-violet-500/10 dark:bg-violet-500/20">
                                                        <i class="bi bi-card-checklist text-xs"
                                                            title="Masa Ujian (Dialihkan ke Presensi Ujian)"></i>
                                                    </div>
                                                </td>
                                            @elseif (!empty($data['is_event']))
                                                <!-- Sel Event Khusus / Non-KBM -->
                                                @php
                                                    $status =
                                                        $matrix[$murid->id][$data['masehi']] ??
                                                        ($matrix[$murid->id][$tgl] ?? '');
                                                @endphp
                                                <td class="p-1 text-center border-r border-zinc-100 dark:border-zinc-800/60 align-middle bg-indigo-500/[0.03]"
                                                    title="Presensi Event: {{ $data['nama_event'] }}">
                                                    <input type="text"
                                                        name="presensi[{{ $data['masehi'] }}][{{ $murid->id }}]"
                                                        value="{{ $status }}" maxlength="1" placeholder="-"
                                                        oninput="this.value = this.value.toUpperCase()"
                                                        class="w-8 h-7 p-0 mx-auto text-center rounded-md bg-white dark:bg-zinc-900 border text-[11px] font-black outline-none transition-all uppercase
                                                        {{ $status == 'H' ? 'text-emerald-600 border-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30' : '' }}
                                                        {{ $status == 'S' ? 'text-blue-600 border-blue-300 bg-blue-50 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30' : '' }}
                                                        {{ $status == 'I' ? 'text-amber-600 border-amber-300 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30' : '' }}
                                                        {{ $status == 'A' ? 'text-rose-600 border-rose-300 bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30' : '' }}
                                                        {{ $status == 'D' ? 'text-purple-600 border-purple-300 bg-purple-50 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30' : '' }}
                                                        {{ $status == '' ? 'text-zinc-700 border-indigo-200 dark:text-zinc-300 dark:border-indigo-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20' : '' }}">
                                                </td>
                                            @else
                                                <!-- Sel Input KBM Reguler -->
                                                @php
                                                    $status =
                                                        $matrix[$murid->id][$data['masehi']] ??
                                                        ($matrix[$murid->id][$tgl] ?? '');
                                                @endphp
                                                <td
                                                    class="p-1 text-center border-r border-zinc-100 dark:border-zinc-800/60 align-middle {{ !$data['is_jadwal'] ? 'bg-zinc-100/30 dark:bg-zinc-950/40' : '' }}">
                                                    <input type="text"
                                                        name="presensi[{{ $data['masehi'] }}][{{ $murid->id }}]"
                                                        value="{{ $status }}" maxlength="1" placeholder="-"
                                                        oninput="this.value = this.value.toUpperCase()"
                                                        class="w-8 h-7 p-0 mx-auto text-center rounded-md bg-white dark:bg-zinc-900 border text-[11px] font-black outline-none transition-all uppercase
                                                        {{ $status == 'H' ? 'text-emerald-600 border-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30' : '' }}
                                                        {{ $status == 'S' ? 'text-blue-600 border-blue-300 bg-blue-50 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30' : '' }}
                                                        {{ $status == 'I' ? 'text-amber-600 border-amber-300 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30' : '' }}
                                                        {{ $status == 'A' ? 'text-rose-600 border-rose-300 bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30' : '' }}
                                                        {{ $status == 'D' ? 'text-purple-600 border-purple-300 bg-purple-50 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30' : '' }}
                                                        {{ $status == '' ? 'text-zinc-700 border-zinc-200 dark:text-zinc-300 dark:border-zinc-700 focus:border-primary focus:ring-2 focus:ring-primary/20' : '' }}
                                                        {{ !$data['is_jadwal'] ? 'border-dashed border-zinc-200 dark:border-zinc-700 bg-transparent opacity-60' : '' }}">
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Floating Action Button (FAB) -->
                @can('create presensi-murid')
                    <div class="fixed bottom-6 right-6 z-50">
                        <button type="submit"
                            class="m3-btn-primary h-12 px-6 rounded-2xl text-xs font-black shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2 group/fab">
                            <i class="bi bi-save2-fill text-sm group-hover/fab:scale-110 transition-transform"></i>
                            <span>Simpan Rekap Bulanan</span>
                        </button>
                    </div>
                @endcan
            </form>
        @endif
    @else
        <div class="py-16 text-center m3-glass-card relative z-10">
            <div
                class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                <i class="bi bi-calendar-month"></i>
            </div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Buka Rekap Matriks
                Bulanan</h3>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                Silakan pilih Bulan Hijriyah, Kelas, dan Jam Pelajaran di atas untuk menampilkan grid leger 1-30 hari.
            </p>
        </div>
    @endif
</x-app-layout>
