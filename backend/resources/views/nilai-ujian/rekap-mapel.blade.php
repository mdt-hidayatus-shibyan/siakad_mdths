@section('title', 'Rekapitulasi Nilai Per Mata Pelajaran')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div class="mb-5 md:mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-20">
        <div class="w-full xl:w-auto shrink-0">
            @include('nilai-ujian.menu')
        </div>

        <div class="w-full xl:w-auto flex-1 flex items-center justify-end gap-2">
            @if ($ujianTerpilih && $levelTerpilih && $rekapPerMapel->isNotEmpty())
                <a href="{{ route('nilai-ujian.rekap-mapel.cetak', array_filter(['tahun_id' => $tahunPelajaranId, 'ujian_id' => $ujianTerpilih->id, 'level_id' => $levelTerpilih->id, 'ruangan_id' => $selectedRuanganId, 'jadwal_ujian_id' => $selectedJadwalId])) }}"
                    target="_blank"
                    class="w-full sm:w-auto h-10 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black inline-flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95">
                    <i class="bi bi-printer-fill text-sm"></i>
                    <span>Cetak Rekap (A4)</span>
                </a>
            @endif
        </div>
    </div>

    <!-- PANEL FILTER: TAHUN, AGENDA UJIAN, LEVEL, RUANGAN, & MATA PELAJARAN JADWAL -->
    <div class="m3-glass-card p-4 sm:p-5 mb-6 relative z-10 shadow-2xs">
        <form action="{{ route('nilai-ujian.rekap-mapel') }}" method="GET" id="formRekapMapel"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

            <!-- 1. Tahun Pelajaran -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                    Tahun Pelajaran
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-calendar-range text-xs"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formRekapMapel').submit()"
                        class="m3-input-glass w-full !pl-8 !pr-7 text-xs font-bold appearance-none cursor-pointer">
                        @foreach ($daftarTahun as $tp)
                            <option value="{{ $tp->id }}"
                                {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Agenda Ujian -->
            <div class="lg:col-span-3">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                    Agenda Ujian
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-file-earmark-check text-xs"></i>
                    </div>
                    <select name="ujian_id" onchange="document.getElementById('formRekapMapel').submit()"
                        class="m3-input-glass w-full !pl-8 !pr-7 text-xs font-bold appearance-none cursor-pointer">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach ($daftarUjian as $uj)
                            <option value="{{ $uj->id }}"
                                {{ (string) ($ujianTerpilih?->id) === (string) $uj->id ? 'selected' : '' }}>
                                {{ $uj->nama_ujian }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Level / Kelas -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                    Level / Kelas
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-layers-fill text-xs"></i>
                    </div>
                    <select name="level_id" {{ !$ujianTerpilih ? 'disabled' : '' }}
                        onchange="document.getElementById('selectRuangan').value=''; if(document.getElementById('selectJadwal')) document.getElementById('selectJadwal').value=''; document.getElementById('formRekapMapel').submit()"
                        class="m3-input-glass w-full !pl-8 !pr-7 text-xs font-bold appearance-none cursor-pointer disabled:opacity-50">
                        <option value="">-- Pilih Level --</option>
                        @foreach ($daftarLevel as $lv)
                            <option value="{{ $lv->id }}"
                                {{ (string) $selectedLevelId === (string) $lv->id ? 'selected' : '' }}>
                                {{ $lv->nama_level }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Ruangan (Opsional: Semua Ruangan = Rekap Per Level, atau 1 Ruangan Spesifik) -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                    Ruangan
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-door-open-fill text-xs"></i>
                    </div>
                    <select name="ruangan_id" id="selectRuangan" {{ !$levelTerpilih ? 'disabled' : '' }}
                        onchange="document.getElementById('formRekapMapel').submit()"
                        class="m3-input-glass w-full !pl-8 !pr-7 text-xs font-bold appearance-none cursor-pointer disabled:opacity-50">
                        <option value="">Semua Ruangan (Per Level)</option>
                        @foreach ($daftarRuanganLevel as $r)
                            <option value="{{ $r->id }}"
                                {{ (string) $selectedRuanganId === (string) $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <!-- 5. Mata Pelajaran Jadwal Ujian (Opsional) -->
            <div class="lg:col-span-3">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                    Mata Pelajaran Jadwal
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-journal-bookmark-fill text-xs"></i>
                    </div>
                    <select name="jadwal_ujian_id" id="selectJadwal" {{ $jadwals->isEmpty() ? 'disabled' : '' }}
                        onchange="document.getElementById('formRekapMapel').submit()"
                        class="m3-input-glass w-full !pl-8 !pr-7 text-xs font-bold appearance-none cursor-pointer disabled:opacity-50">
                        <option value="">Semua Mapel Jadwal ({{ $jadwals->count() }})</option>
                        @foreach ($jadwals as $j)
                            <option value="{{ $j->id }}"
                                {{ (string) $selectedJadwalId === (string) $j->id ? 'selected' : '' }}>
                                {{ $j->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if (!$ujianTerpilih || !$levelTerpilih)
        <x-empty-state icon="bi-journal-bookmark" title="Rekapitulasi Nilai Per Mata Pelajaran Jadwal Ujian"
            message="Pilih Agenda Ujian dan Level terlebih dahulu pada filter di atas. Anda dapat menampilkan rekap satu level penuh (semua ruangan) maupun menyaring ruangan serta mata pelajaran jadwal tertentu." />
    @elseif ($jadwals->isEmpty())
        <x-empty-state icon="bi-calendar-x" title="Jadwal Ujian Belum Diatur"
            message="Belum ada mata pelajaran pada Jadwal Ujian untuk {{ $ujianTerpilih->nama_ujian }} — Level {{ $levelTerpilih->nama_level }}." />
    @else
        <!-- KARTU KPI RINGKASAN -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4 mb-6 relative z-10">
            <!-- KPI 1: Rata-rata Nilai -->
            <div
                class="bg-gradient-to-br from-emerald-600 to-teal-700 dark:from-emerald-700 dark:to-teal-900 p-4 sm:p-5 rounded-3xl text-white shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-100 block">
                    Rata-Rata Nilai
                </span>
                <h3 class="text-2xl md:text-3xl font-black tracking-tight mt-1">
                    {{ number_format($summary->rata_rata, 2) }}
                </h3>
                <p class="text-[11px] font-bold text-emerald-100 mt-1">
                    {{ $ruanganTerpilih ? 'Ruangan ' . $ruanganTerpilih->nama_ruangan : 'Level ' . $levelTerpilih->nama_level . ' (' . $summary->jumlah_ruangan . ' Ruang)' }}
                </p>
            </div>

            <!-- KPI 2: Ketuntasan KKM -->
            <div
                class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block">
                    Ketuntasan (KKM &ge; {{ $kkm }})
                </span>
                <h3 class="text-2xl md:text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-1">
                    {{ $summary->persen_tuntas }}%
                </h3>
                <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-1">
                    <span class="text-emerald-600 dark:text-emerald-400">{{ $summary->tuntas_count }} Tuntas</span>
                    &bull;
                    <span class="text-rose-600 dark:text-rose-400">{{ $summary->belum_tuntas_count }} Remidi</span>
                </p>
            </div>

            <!-- KPI 3: Nilai Tertinggi & Terendah -->
            <div
                class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block">
                    Rentang Nilai Masuk
                </span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-sky-600 dark:text-sky-400">
                        {{ $summary->tertinggi !== null ? $summary->tertinggi : '-' }}
                    </span>
                    <span class="text-xs font-bold text-zinc-400">/</span>
                    <span class="text-xl font-black text-rose-600 dark:text-rose-400">
                        {{ $summary->terendah !== null ? $summary->terendah : '-' }}
                    </span>
                </div>
                <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-1">
                    Tertinggi / Terendah
                </p>
            </div>

            <!-- KPI 4: Progres Nilai Masuk -->
            <div
                class="m3-glass-card p-4 sm:p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block">
                    Kelengkapan Nilai
                </span>
                <h3 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    {{ $summary->persen_progres }}%
                </h3>
                <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ number_format($summary->total_nilai_masuk) }} / {{ number_format($summary->target_nilai) }} Nilai
                    ({{ $summary->jumlah_peserta }} Peserta)
                </p>
            </div>
        </div>

        <!-- TABEL 1: REKAPITULASI PER MATA PELAJARAN JADWAL UJIAN -->
        <div
            class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden mb-6 relative z-10 shadow-2xs">
            <div
                class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3
                        class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-journal-bookmark-fill text-emerald-500"></i>
                        <span>Rekapitulasi Per Mata Pelajaran Jadwal Ujian</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        {{ $ujianTerpilih->nama_ujian }} &bull; Level {{ $levelTerpilih->nama_level }}
                        {{ $ruanganTerpilih ? ' • Ruangan ' . $ruanganTerpilih->nama_ruangan : ' • Semua Ruangan (' . $targetRuangans->count() . ' Ruang)' }}
                    </p>
                </div>
                @if ($jadwalTerpilih)
                    <a href="{{ route('nilai-ujian.rekap-mapel', array_filter(['tahun_id' => $tahunPelajaranId, 'ujian_id' => $ujianTerpilih->id, 'level_id' => $levelTerpilih->id, 'ruangan_id' => $selectedRuanganId])) }}"
                        class="text-xs font-black text-rose-500 hover:text-rose-600 inline-flex items-center gap-1">
                        <i class="bi bi-x-circle-fill"></i> Tampilkan Semua Mapel
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-sm min-w-[980px]">
                    <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                        <tr class="text-xs font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <th class="py-3.5 px-3 text-center w-10">No</th>
                            <th class="py-3.5 px-4 min-w-[190px]">Mata Pelajaran &amp; Jadwal</th>
                            <th class="py-3.5 px-3 text-center w-24">Dinilai</th>
                            <th class="py-3.5 px-3 text-center w-24">Rata-Rata</th>
                            <th class="py-3.5 px-3 text-center w-24">Tertinggi</th>
                            <th class="py-3.5 px-3 text-center w-20">Terendah</th>
                            <th class="py-3.5 px-3 text-center w-24">Tuntas</th>
                            <th class="py-3.5 px-3 text-center w-24">Remidi</th>
                            <th class="py-3.5 px-4 w-32">% Ketuntasan</th>
                            <th class="py-3.5 px-4 text-right w-28">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                        @foreach ($rekapPerMapel as $row)
                            @php $isActiveRow = $selectedJadwalId === $row->jadwal_id; @endphp
                            <tr
                                class="transition-colors {{ $isActiveRow ? 'bg-emerald-50/60 dark:bg-emerald-950/25' : 'hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30' }}">
                                <td class="py-3.5 px-3 text-center font-mono text-zinc-400">{{ $loop->iteration }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-zinc-900 dark:text-white">{{ $row->nama_mapel }}</div>
                                    <div class="text-xs font-semibold text-zinc-400 mt-0.5">
                                        {{ $row->hari_tanggal }} &bull; {{ $row->jam }}
                                        @if ($row->jumlah_draft > 0)
                                            <span
                                                class="ml-1 px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-black">
                                                {{ $row->jumlah_draft }} Draft
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono">
                                    <span class="font-black text-zinc-800 dark:text-zinc-200">{{ $row->jumlah_dinilai }}</span>
                                    <span class="text-zinc-400">/ {{ $row->jumlah_peserta }}</span>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-xl font-mono font-black text-sm {{ $row->jumlah_dinilai === 0 ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' : ($row->rata_rata >= $kkm ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400') }}">
                                        {{ $row->jumlah_dinilai > 0 ? number_format($row->rata_rata, 2) : '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="font-mono font-black text-sky-600 dark:text-sky-400">
                                        {{ $row->tertinggi !== null ? $row->tertinggi : '-' }}
                                    </div>
                                    @if ($row->peraih_tertinggi)
                                        <div class="text-[11px] text-zinc-400 truncate max-w-[110px] mx-auto"
                                            title="{{ $row->peraih_tertinggi }}">
                                            {{ $row->peraih_tertinggi }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                                    {{ $row->terendah !== null ? $row->terendah : '-' }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $row->tuntas_count }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-black {{ $row->belum_tuntas_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    {{ $row->belum_tuntas_count }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-xs font-black text-zinc-500 mb-1">{{ $row->persen_tuntas }}%</div>
                                    <div class="w-full h-1.5 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden">
                                        <div class="h-full rounded-full {{ $row->persen_tuntas >= 80 ? 'bg-emerald-500' : ($row->persen_tuntas >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                            style="width: {{ min(100, $row->persen_tuntas) }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('nilai-ujian.rekap-mapel', array_filter(['tahun_id' => $tahunPelajaranId, 'ujian_id' => $ujianTerpilih->id, 'level_id' => $levelTerpilih->id, 'ruangan_id' => $selectedRuanganId, 'jadwal_ujian_id' => $row->jadwal_id])) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ $isActiveRow ? 'bg-emerald-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 hover:bg-primary hover:text-white text-zinc-700 dark:text-zinc-300' }}">
                                        <i class="bi bi-people-fill"></i> Murid
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL 2: MATRIKS PERBANDINGAN RATA-RATA MAPEL ANTAR RUANGAN (KHUSUS MODE PER LEVEL > 1 RUANGAN) -->
        @if (!$ruanganTerpilih && $targetRuangans->count() > 1 && !$jadwalTerpilih)
            <div
                class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden mb-6 relative z-10 shadow-2xs">
                <div
                    class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col lg:flex-row lg:items-center justify-between gap-2.5">
                    <div>
                        <h3
                            class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="bi bi-grid-3x3-gap-fill text-sky-500"></i>
                            <span>Perbandingan Rata-Rata Nilai Antar Ruangan — Level {{ $levelTerpilih->nama_level }}</span>
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            Membandingkan rata-rata nilai dan ketuntasan tiap mata pelajaran jadwal ujian pada setiap ruangan dalam level ini.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> &ge; 80
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> &ge; KKM ({{ $kkm }})
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> &lt; KKM
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block w-0.5 h-3 bg-rose-500/80"></span> Batas KKM
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-sm min-w-[760px]">
                        <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                            <tr class="text-xs font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                <th class="py-3.5 px-4 min-w-[180px]">Mata Pelajaran</th>
                                @foreach ($targetRuangans as $rng)
                                    <th class="py-3.5 px-3 text-center min-w-[135px]">
                                        <a href="{{ route('nilai-ujian.rekap-mapel', ['tahun_id' => $tahunPelajaranId, 'ujian_id' => $ujianTerpilih->id, 'level_id' => $levelTerpilih->id, 'ruangan_id' => $rng->id]) }}"
                                            class="hover:text-emerald-600 transition-colors underline decoration-dotted">
                                            {{ $rng->nama_ruangan }}
                                        </a>
                                    </th>
                                @endforeach
                                <th class="py-3.5 px-4 text-center min-w-[140px] bg-emerald-500/5 text-emerald-600 dark:text-emerald-400">
                                    Rata² Level
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                            @foreach ($rekapPerMapel as $row)
                                <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-3.5 px-4 font-black text-zinc-900 dark:text-white">
                                        {{ $row->nama_mapel }}
                                    </td>
                                    @foreach ($targetRuangans as $rng)
                                        @php
                                            $cell = $row->per_ruangan[$rng->id] ?? null;
                                            $barColor = ($cell && $cell->rata_rata !== null)
                                                ? ($cell->rata_rata >= 80 ? 'bg-emerald-500' : ($cell->rata_rata >= $kkm ? 'bg-sky-500' : 'bg-rose-500'))
                                                : 'bg-zinc-300';
                                        @endphp
                                        <td class="py-3.5 px-3">
                                            @if ($cell && $cell->rata_rata !== null)
                                                <div class="flex items-baseline justify-between gap-1.5">
                                                    <span
                                                        class="font-mono font-black {{ $cell->rata_rata >= $kkm ? 'text-zinc-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                                        {{ number_format($cell->rata_rata, 2) }}
                                                    </span>
                                                    <span class="text-[11px] font-bold text-zinc-400">
                                                        {{ $cell->persen_tuntas }}%
                                                    </span>
                                                </div>
                                                <div class="w-full h-2 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden relative my-1.5"
                                                    title="Rata-rata {{ number_format($cell->rata_rata, 2) }} (KKM {{ $kkm }})">
                                                    <div class="h-full rounded-full {{ $barColor }}"
                                                        style="width: {{ min(100, max(0, $cell->rata_rata)) }}%"></div>
                                                    <div class="absolute top-0 bottom-0 w-0.5 bg-rose-500/75"
                                                        style="left: {{ $kkm }}%"></div>
                                                </div>
                                                <div class="text-[11px] font-semibold text-zinc-400">
                                                    {{ $cell->dinilai }}/{{ $cell->peserta }} dinilai
                                                </div>
                                            @else
                                                <div class="text-center text-zinc-300 dark:text-zinc-700">-</div>
                                            @endif
                                        </td>
                                    @endforeach
                                    @php
                                        $levelBarColor = $row->jumlah_dinilai > 0
                                            ? ($row->rata_rata >= 80 ? 'bg-emerald-500' : ($row->rata_rata >= $kkm ? 'bg-sky-500' : 'bg-rose-500'))
                                            : 'bg-zinc-300';
                                    @endphp
                                    <td class="py-3.5 px-4 bg-emerald-500/5">
                                        @if ($row->jumlah_dinilai > 0)
                                            <div class="flex items-baseline justify-between gap-1.5">
                                                <span
                                                    class="font-mono font-black {{ $row->rata_rata >= $kkm ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                    {{ number_format($row->rata_rata, 2) }}
                                                </span>
                                                <span class="text-[11px] font-bold text-zinc-400">
                                                    {{ $row->persen_tuntas }}%
                                                </span>
                                            </div>
                                            <div class="w-full h-2 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden relative my-1.5"
                                                title="Rata-rata Level {{ number_format($row->rata_rata, 2) }} (KKM {{ $kkm }})">
                                                <div class="h-full rounded-full {{ $levelBarColor }}"
                                                    style="width: {{ min(100, max(0, $row->rata_rata)) }}%"></div>
                                                <div class="absolute top-0 bottom-0 w-0.5 bg-rose-500/75"
                                                    style="left: {{ $kkm }}%"></div>
                                            </div>
                                            <div class="text-[11px] font-semibold text-zinc-400">
                                                {{ $row->jumlah_dinilai }}/{{ $row->jumlah_peserta }} dinilai
                                            </div>
                                        @else
                                            <div class="text-center font-mono font-black text-zinc-400">-</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TABEL 3: DETAIL NILAI MURID & PERBANDINGAN RUANGAN SAAT 1 MAPEL DIPILIH -->
        @if ($jadwalTerpilih)
            @if ($rekapRuanganMapel->count() > 1)
                <div
                    class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden mb-6 relative z-10 shadow-2xs">
                    <div class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800">
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="bi bi-door-open-fill text-sky-500"></i>
                            <span>Perbandingan Per Ruangan — Mapel {{ $jadwalTerpilih->nama_mapel }}</span>
                        </h3>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-sm min-w-[760px]">
                            <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                                <tr class="text-xs font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <th class="py-3.5 px-4">Ruangan</th>
                                    <th class="py-3.5 px-3 text-center">Dinilai</th>
                                    <th class="py-3.5 px-4 min-w-[155px]">Rata-Rata</th>
                                    <th class="py-3.5 px-3 text-center">Tertinggi</th>
                                    <th class="py-3.5 px-3 text-center">Terendah</th>
                                    <th class="py-3.5 px-3 text-center">Tuntas</th>
                                    <th class="py-3.5 px-3 text-center">Remidi</th>
                                    <th class="py-3.5 px-4 min-w-[130px]">% Tuntas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                                @foreach ($rekapRuanganMapel as $rr)
                                    @php
                                        $rrBarColor = $rr->rata_rata !== null
                                            ? ($rr->rata_rata >= 80 ? 'bg-emerald-500' : ($rr->rata_rata >= $kkm ? 'bg-sky-500' : 'bg-rose-500'))
                                            : 'bg-zinc-300';
                                    @endphp
                                    <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30">
                                        <td class="py-3.5 px-4 font-black text-zinc-900 dark:text-white">{{ $rr->nama_ruangan }}</td>
                                        <td class="py-3.5 px-3 text-center font-mono">{{ $rr->dinilai }} / {{ $rr->peserta }}</td>
                                        <td class="py-3.5 px-4">
                                            @if ($rr->rata_rata !== null)
                                                <div class="font-mono font-black mb-1 {{ $rr->rata_rata >= $kkm ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                    {{ number_format($rr->rata_rata, 2) }}
                                                </div>
                                                <div class="w-full h-2 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden relative">
                                                    <div class="h-full rounded-full {{ $rrBarColor }}"
                                                        style="width: {{ min(100, max(0, $rr->rata_rata)) }}%"></div>
                                                    <div class="absolute top-0 bottom-0 w-0.5 bg-rose-500/75"
                                                        style="left: {{ $kkm }}%"></div>
                                                </div>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-mono font-bold text-sky-600 dark:text-sky-400">
                                            {{ $rr->tertinggi !== null ? $rr->tertinggi : '-' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                                            {{ $rr->terendah !== null ? $rr->terendah : '-' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-mono font-black text-emerald-600">{{ $rr->tuntas }}</td>
                                        <td class="py-3.5 px-3 text-center font-mono font-black text-rose-600">{{ $rr->belum_tuntas }}</td>
                                        <td class="py-3.5 px-4">
                                            <div class="text-xs font-black text-zinc-600 dark:text-zinc-300 mb-1">{{ $rr->persen_tuntas }}%</div>
                                            <div class="w-full h-2 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden">
                                                <div class="h-full rounded-full {{ $rr->persen_tuntas >= 80 ? 'bg-emerald-500' : ($rr->persen_tuntas >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                    style="width: {{ min(100, $rr->persen_tuntas) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Daftar Nilai Seluruh Murid pada Mapel Terpilih -->
            <div
                class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden relative z-10 shadow-2xs">
                <div
                    class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="bi bi-people-fill text-emerald-500"></i>
                            <span>Daftar Nilai Murid — {{ $jadwalTerpilih->nama_mapel }}</span>
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            Diurutkan berdasarkan perolehan nilai tertinggi ke terendah ({{ $detailNilaiMurid->count() }} Peserta).
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-sm min-w-[720px]">
                        <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                            <tr class="text-xs font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                <th class="py-3.5 px-3 text-center w-12">Rank</th>
                                <th class="py-3.5 px-3 w-32">NISM</th>
                                <th class="py-3.5 px-4">Nama Lengkap Murid</th>
                                <th class="py-3.5 px-3 w-32">Ruangan</th>
                                <th class="py-3.5 px-3 text-center w-20">Nilai</th>
                                <th class="py-3.5 px-3 text-center w-24">Predikat</th>
                                <th class="py-3.5 px-3 text-center w-32">Ketuntasan</th>
                                <th class="py-3.5 px-3 text-center w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                            @forelse ($detailNilaiMurid as $idx => $item)
                                <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30">
                                    <td class="py-3 px-3 text-center font-mono font-bold text-zinc-400">
                                        {{ $item->nilai !== null ? $idx + 1 : '-' }}
                                    </td>
                                    <td class="py-3 px-3 font-mono font-bold text-zinc-500 dark:text-zinc-400">
                                        {{ $item->murid->nism ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-black text-zinc-900 dark:text-white uppercase">
                                        {{ $item->murid->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span
                                            class="px-2.5 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold">
                                            {{ $item->ruangan->nama_ruangan ?? '-' }}
                                        </span>
                                    </td>
                                    <td
                                        class="py-3 px-3 text-center font-mono font-black text-base {{ $item->nilai === null ? 'text-zinc-400' : ($item->tuntas ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400') }}">
                                        {{ $item->nilai !== null ? $item->nilai : '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-black text-zinc-700 dark:text-zinc-300">
                                        {{ $item->predikat }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($item->nilai === null)
                                            <span
                                                class="px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase bg-zinc-100 dark:bg-zinc-800 text-zinc-500">
                                                Belum Dinilai
                                            </span>
                                        @elseif ($item->tuntas)
                                            <span
                                                class="px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                Tuntas
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                Remidi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($item->nilai === null)
                                            <span class="text-zinc-400">-</span>
                                        @elseif ($item->is_published)
                                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">Publish</span>
                                        @else
                                            <span class="text-xs font-black text-amber-500">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-zinc-400 italic">
                                        Belum ada peserta pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</x-app-layout>
