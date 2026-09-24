@section('title', 'Jadwal Induk Pelajaran')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('jadwal-pelajaran.index') }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    Jadwal Induk Madrasah
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Matriks distribusi mata pelajaran dan asatidz seluruh ruangan.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2.5 w-full md:w-auto items-center">
            <!-- Form Filter Tahun -->
            <form action="{{ request()->url() }}" method="GET" class="w-full md:w-auto">
                <div class="relative group/select">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-range text-sm"></i>
                    </div>

                    <select name="tahun_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full md:w-auto !pl-9 !pr-9 appearance-none cursor-pointer min-w-[200px]">
                        <option value="">-- Tahun Aktif --</option>
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_hijriyah }} - {{ $tahun->nama_masehi }}
                            </option>
                        @endforeach
                    </select>

                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </form>

            <!-- Tombol Cetak -->
            <a href="{{ route('jadwal-pelajaran.cetak-leger') }}" target="_blank"
                class="m3-btn-primary h-10 px-4.5 w-full md:w-auto group/btn shrink-0">
                <i class="bi bi-printer text-sm"></i>
                <span>Cetak Jadwal</span>
            </a>
        </div>
    </div>

    @php
        $hariList = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $jamList = ['Nadzoman', '1', '2', 'Ekstra'];

        // Mengambil daftar TINGKAT unik
        $tingkats = $ruangans
            ->map(function ($r) {
                return $r->level ? $r->level->tingkat : null;
            })
            ->filter()
            ->unique('id')
            ->values();
    @endphp

    <!-- TABS NAVIGASI TINGKAT -->
    <div
        class="flex gap-2 overflow-x-auto pb-3 mb-5 border-b border-zinc-200/80 dark:border-zinc-800 print:hidden custom-scrollbar relative z-10">
        @foreach ($tingkats as $index => $tingkat)
            <button type="button" onclick="switchTab({{ $tingkat->id }})" id="btn-tab-{{ $tingkat->id }}"
                class="tab-btn px-5 py-2 rounded-xl font-black text-xs whitespace-nowrap transition-all duration-200 shadow-2xs active:scale-95 border
                {{ $index == 0 ? 'bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 border-transparent' : 'bg-white/80 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border-zinc-200/80 dark:border-zinc-800 hover:bg-zinc-100/80 dark:hover:bg-zinc-800/60' }}">
                {{ $tingkat->nama_tingkat ?? $tingkat->nama }}
            </button>
        @endforeach
    </div>

    <!-- KONTEN TAB TINGKAT -->
    <div class="relative z-10">
        @foreach ($tingkats as $index => $tingkat)
            @php
                $ruangansTingkat = $ruangans->filter(function ($r) use ($tingkat) {
                    return $r->level && $r->level->tingkat_id == $tingkat->id;
                });
            @endphp

            <div id="content-tab-{{ $tingkat->id }}"
                class="tab-content {{ $index == 0 ? '' : 'hidden' }} space-y-6 animate-[modalFadeIn_0.2s_ease-out]">

                <h3 class="text-xl font-black text-zinc-900 dark:text-white mb-4 hidden print:block">
                    Jadwal Tingkat: {{ $tingkat->nama_tingkat ?? $tingkat->nama }}
                </h3>

                @foreach ($hariList as $hari)
                    <!-- KARTU TABEL HARI -->
                    <div class="m3-glass-card overflow-hidden relative">

                        <!-- Header Hari -->
                        <div
                            class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center gap-3 relative z-10">
                            <div
                                class="w-8 h-8 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center text-sm shrink-0">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <h3 class="font-black text-base text-zinc-900 dark:text-white uppercase tracking-wider">
                                {{ $hari }}
                            </h3>
                        </div>

                        <!-- Tabel Matrix -->
                        <div class="overflow-x-auto print:overflow-visible relative z-10 custom-scrollbar">
                            <table class="m3-table w-full text-left whitespace-nowrap min-w-max">
                                <thead>
                                    <tr>
                                        <!-- Header Waktu (Sticky) -->
                                        <th scope="col"
                                            class="text-center w-24 border-r border-zinc-200/80 dark:border-zinc-800 sticky left-0 bg-zinc-50/90 dark:bg-zinc-950/90 z-20 backdrop-blur-md">
                                            Waktu
                                        </th>
                                        <!-- Header Ruangan -->
                                        @foreach ($ruangansTingkat as $ruangan)
                                            <th scope="col"
                                                class="text-center border-r border-zinc-200/80 dark:border-zinc-800 min-w-[200px] last:border-r-0">
                                                <div class="text-xs font-black text-zinc-900 dark:text-zinc-100">
                                                    {{ $ruangan->nama_ruangan }}</div>
                                                <span
                                                    class="block text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5">({{ $ruangan->waliRuangan->nama_lengkap ?? 'Tanpa Wali' }})</span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jamList as $jam)
                                        <tr
                                            class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors duration-200">

                                            <!-- Kolom Jam (Sticky) -->
                                            <td
                                                class="text-center border-r border-zinc-200/80 dark:border-zinc-800 align-middle sticky left-0 bg-white/95 dark:bg-zinc-950/95 z-20 backdrop-blur-md">
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $jam == 'Ekstra' || $jam == 'Nadzoman' ? 'bg-purple-50 text-purple-600 border-purple-200/80 dark:bg-purple-950/40 dark:text-purple-400 dark:border-purple-800/40' : 'bg-zinc-100/80 text-zinc-700 border-zinc-200/80 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-800' }}">
                                                    Jam {{ $jam }}
                                                </span>
                                            </td>

                                            <!-- Kolom Isi Matrix per Ruangan -->
                                            @foreach ($ruangansTingkat as $ruangan)
                                                <td
                                                    class="p-2.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle last:border-r-0">
                                                    @if (isset($matrix[$hari][$jam][$ruangan->id]))
                                                        @php
                                                            $jadwal = $matrix[$hari][$jam][$ruangan->id];
                                                            $isBentrok = isset($bentrokJadwalIds[$jadwal->id]);
                                                        @endphp

                                                        @php
                                                            $allUstadz = $jadwal->daftar_ustadz;
                                                            $primaryUstadz =
                                                                $allUstadz->firstWhere('pivot.is_utama', 1) ??
                                                                ($allUstadz->firstWhere('pivot.is_utama', true) ??
                                                                    ($allUstadz->first() ?? $jadwal->ustadz));
                                                            $pendampingList = $allUstadz->where(
                                                                'id',
                                                                '!=',
                                                                $primaryUstadz?->id,
                                                            );
                                                            $isTeam = $pendampingList->isNotEmpty();
                                                        @endphp

                                                        @if ($isBentrok)
                                                            <!-- JADWAL BENTROK -->
                                                            <div
                                                                class="p-2.5 rounded-xl bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/50 relative overflow-hidden">
                                                                <div class="absolute -right-2 -top-2 w-7 h-7 bg-rose-500 rounded-lg flex items-end justify-start pl-1.5 pb-1 text-white animate-pulse"
                                                                    title="Jadwal Bentrok!">
                                                                    <i
                                                                        class="bi bi-exclamation-triangle-fill text-[8px]"></i>
                                                                </div>
                                                                <h4
                                                                    class="text-xs font-black text-rose-700 dark:text-rose-400 leading-tight mb-1 pr-3 truncate">
                                                                    {{ $jadwal->mataPelajaran->nama_mapel }}
                                                                </h4>
                                                                <div class="space-y-0.5">
                                                                    <p
                                                                        class="text-[10px] font-bold text-rose-600/90 dark:text-rose-400/90 truncate flex items-center gap-1">
                                                                        <i
                                                                            class="bi bi-star-fill text-[8px] text-amber-500"></i>
                                                                        <span>{{ $primaryUstadz?->nama_lengkap ?? '-' }}</span>
                                                                    </p>
                                                                    @if ($isTeam)
                                                                        @foreach ($pendampingList as $p)
                                                                            <p
                                                                                class="text-[9px] font-semibold text-rose-500/80 dark:text-rose-300/80 truncate flex items-center gap-1 pl-2.5">
                                                                                <i class="bi bi-person text-[8px]"></i>
                                                                                <span>{{ $p->nama_lengkap }}</span>
                                                                            </p>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @else
                                                            <!-- JADWAL NORMAL -->
                                                            <div
                                                                class="p-2.5 rounded-xl bg-zinc-50/80 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/80">
                                                                <h4
                                                                    class="text-xs font-black text-zinc-900 dark:text-white leading-tight mb-1 truncate">
                                                                    {{ $jadwal->mataPelajaran->nama_mapel }}
                                                                </h4>
                                                                <div class="space-y-0.5">
                                                                    <p
                                                                        class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 truncate flex items-center gap-1">
                                                                        @if ($isTeam)
                                                                            <i
                                                                                class="bi bi-star-fill text-[8px] text-amber-500"></i>
                                                                        @else
                                                                            <i
                                                                                class="bi bi-person-fill text-[9px] opacity-70"></i>
                                                                        @endif
                                                                        <span>{{ $primaryUstadz?->nama_lengkap ?? '-' }}</span>
                                                                    </p>
                                                                    @if ($isTeam)
                                                                        @foreach ($pendampingList as $p)
                                                                            <p class="text-[9px] font-semibold text-sky-600 dark:text-sky-400 truncate flex items-center gap-1 pl-2.5"
                                                                                title="Guru Pendamping">
                                                                                <i class="bi bi-people text-[8px]"></i>
                                                                                <span>{{ $p->nama_lengkap }}</span>
                                                                            </p>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <!-- JADWAL KOSONG -->
                                                        <div
                                                            class="flex flex-col items-center justify-center py-2 text-zinc-300 dark:text-zinc-700">
                                                            <i class="bi bi-dash text-base"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <!-- SCRIPT TABS ZINC MATERIAL 3 -->
    @push('script')
        <script>
            function switchTab(tingkatId) {
                // Sembunyikan semua konten tab
                document.querySelectorAll('.tab-content').forEach(el => {
                    el.classList.add('hidden');
                });

                // Reset semua tombol ke gaya Inactive
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('bg-primary', 'dark:bg-primary-dark', 'text-white', 'dark:text-zinc-900',
                        'border-transparent');
                    btn.classList.add('bg-white/80', 'dark:bg-zinc-900', 'text-zinc-600', 'dark:text-zinc-400',
                        'border-zinc-200/80',
                        'dark:border-zinc-800', 'hover:bg-zinc-100/80', 'dark:hover:bg-zinc-800/60');
                });

                // Tampilkan konten tab yang dipilih
                document.getElementById('content-tab-' + tingkatId).classList.remove('hidden');

                // Set gaya tombol yang aktif
                const activeBtn = document.getElementById('btn-tab-' + tingkatId);
                activeBtn.classList.remove('bg-white/80', 'dark:bg-zinc-900', 'text-zinc-600', 'dark:text-zinc-400',
                    'border-zinc-200/80',
                    'dark:border-zinc-800', 'hover:bg-zinc-100/80', 'dark:hover:bg-zinc-800/60');
                activeBtn.classList.add('bg-primary', 'dark:bg-primary-dark', 'text-white', 'dark:text-zinc-900',
                    'border-transparent');
            }
        </script>
    @endpush
</x-app-layout>
