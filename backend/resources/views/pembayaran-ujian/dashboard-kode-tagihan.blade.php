@section('title', 'Dashboard Pembayaran Ujian per Kode Tagihan')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 mb-5 md:mb-6 relative z-20 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('pembayaran-ujian.menu-pembayaran')
        </div>

        <!-- Filter Tahun Pelajaran, Ruangan & Tombol Cetak -->
        <div class="w-full xl:w-auto flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2 xl:justify-end">
            <form action="{{ route('pembayaran-ujian.dashboard') }}" method="GET" id="formFilterDashboardUjian"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 m3-glass-card p-1.5 shadow-2xs w-full sm:w-auto">

                @if ($selectedKode)
                    <input type="hidden" name="kode_tagihan" value="{{ $selectedKode }}">
                @endif

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-[195px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-calendar-range text-xs"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formFilterDashboardUjian').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}"
                                {{ (string) $tahunPelajaranId === (string) $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

                <div class="hidden sm:block w-px h-5 bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[180px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-door-open-fill text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formFilterDashboardUjian').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value="">Semua Ruangan</option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}"
                                {{ (string) $selectedRuanganId === (string) $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </form>

            <!-- Tombol Cetak Rekap A4 -->
            <a href="{{ route('pembayaran-ujian.dashboard.cetak', array_filter(['tahun_id' => $tahunPelajaranId, 'kode_tagihan' => $selectedKode, 'ruangan_id' => $selectedRuanganId])) }}"
                target="_blank"
                class="h-11 sm:h-[52px] px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 shrink-0">
                <i class="bi bi-printer-fill text-sm"></i>
                <span>Cetak Rekap</span>
            </a>
        </div>
    </div>

    <!-- QUICK SELECTOR PIL KODE TAGIHAN -->
    @if ($daftarKodeTagihan->isNotEmpty())
        <div class="m3-glass-card p-3.5 md:p-4 mb-5 relative z-10 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <i class="bi bi-tags-fill text-emerald-500"></i>
                    Filter Berdasarkan Kode Tagihan
                </span>
                @if ($selectedKode || $selectedRuanganId)
                    <a href="{{ route('pembayaran-ujian.dashboard', ['tahun_id' => $tahunPelajaranId]) }}"
                        class="text-[11px] font-bold text-rose-500 hover:text-rose-600 inline-flex items-center gap-1">
                        <i class="bi bi-x-circle-fill"></i> Reset Filter
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <!-- Pill Semua Kode -->
                <a href="{{ route('pembayaran-ujian.dashboard', array_filter(['tahun_id' => $tahunPelajaranId, 'ruangan_id' => $selectedRuanganId])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black transition-all border {{ !$selectedKode ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-zinc-900 dark:border-white shadow-2xs' : 'bg-white/60 dark:bg-zinc-900/60 text-zinc-600 dark:text-zinc-300 border-zinc-200/80 dark:border-zinc-800 hover:border-emerald-500/40' }}">
                    <i class="bi bi-grid-fill text-[11px]"></i>
                    <span>Semua Kode ({{ $daftarKodeTagihan->count() }})</span>
                </a>

                @foreach ($rekapPerKode as $rk)
                    @php $isActiveKode = $selectedKode === $rk->kode_tagihan; @endphp
                    <a href="{{ route('pembayaran-ujian.dashboard', array_filter(['tahun_id' => $tahunPelajaranId, 'kode_tagihan' => $rk->kode_tagihan, 'ruangan_id' => $selectedRuanganId])) }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black transition-all border {{ $isActiveKode ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs' : 'bg-white/60 dark:bg-zinc-900/60 text-zinc-700 dark:text-zinc-200 border-zinc-200/80 dark:border-zinc-800 hover:border-emerald-500/40' }}">
                        <span class="font-mono uppercase">{{ $rk->kode_tagihan }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $isActiveKode ? 'bg-white/20 text-white' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }}">
                            {{ $rk->persen_lunas }}%
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($daftarKodeTagihan->isEmpty())
        <x-empty-state icon="bi-tags" title="Belum Ada Master Tagihan Ujian"
            message="Belum ada pengaturan tagihan ujian reguler pada tahun pelajaran ini. Silakan atur terlebih dahulu di menu Pengaturan Tagihan." />
    @else
        <!-- 4 KARTU KPI RINGKASAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 md:gap-4 mb-6 relative z-10">
            <!-- KPI 1: Total Tagihan Terbit -->
            <div
                class="m3-glass-card p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400">
                        Total Target Tagihan
                    </span>
                    <div
                        class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                        <i class="bi bi-receipt-cutoff text-sm"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                        Rp {{ number_format($summary->target_nominal, 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ number_format($summary->total_count) }} Tagihan Terbit
                        @if ($selectedKode)
                            &bull; Kode <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ $selectedKode }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- KPI 2: Total Terbayar (Lunas) -->
            <div
                class="bg-gradient-to-br from-emerald-600 to-teal-700 dark:from-emerald-700 dark:to-teal-900 p-5 rounded-3xl shadow-2xs text-white relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2 relative z-10">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-100">
                        Total Terbayar (Lunas)
                    </span>
                    <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center">
                        <i class="bi bi-check-circle-fill text-sm"></i>
                    </div>
                </div>
                <div class="relative z-10">
                    <h3 class="text-xl md:text-2xl font-black tracking-tight">
                        Rp {{ number_format($summary->terbayar_nominal, 0, ',', '.') }}
                    </h3>
                    <div class="flex items-center justify-between text-[11px] font-bold text-emerald-100 mt-1.5">
                        <span>{{ number_format($summary->lunas_count) }} Lunas</span>
                        <span>{{ $summary->persen_nominal }}% Nominal</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-white/20 mt-1.5 overflow-hidden">
                        <div class="h-full rounded-full bg-white transition-all duration-500"
                            style="width: {{ min(100, $summary->persen_nominal) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Sisa Tunggakan -->
            <div
                class="m3-glass-card p-5 rounded-3xl border border-rose-500/20 bg-rose-500/5 dark:bg-rose-500/10 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        Sisa Tunggakan
                    </span>
                    <div
                        class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle-fill text-sm"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl md:text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                        Rp {{ number_format($summary->tunggakan_nominal, 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] font-bold text-rose-600/80 dark:text-rose-300/80 mt-1">
                        {{ number_format($summary->belum_lunas_count) }} Tagihan Belum Lunas
                    </p>
                </div>
            </div>

            <!-- KPI 4: Capaian Pelunasan & Donatur -->
            <div
                class="m3-glass-card p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400">
                        Capaian Pelunasan
                    </span>
                    <div
                        class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="bi bi-pie-chart-fill text-sm"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                        {{ $summary->persen_lunas }}%
                        <span class="text-xs font-bold text-zinc-400">({{ $summary->lunas_count }}/{{ $summary->total_count }})</span>
                    </h3>
                    @if ($summary->donatur_count > 0)
                        <p class="text-[11px] font-bold text-purple-600 dark:text-purple-400 mt-1">
                            Donatur: {{ $summary->donatur_count }} (Rp {{ number_format($summary->donatur_nominal, 0, ',', '.') }})
                        </p>
                    @else
                        <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ $ruanganTerpilih ? 'Ruangan: ' . $ruanganTerpilih->nama_ruangan : 'Seluruh Ruangan Akses' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- TABEL 1: REKAPITULASI BERDASARKAN KODE TAGIHAN -->
        <div
            class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden mb-6 relative z-10 shadow-2xs">
            <div
                class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3
                        class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-tags-fill text-emerald-500"></i>
                        <span>Rekapitulasi Pembayaran Berdasarkan Kode Tagihan</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Perbandingan realisasi pembayaran dan tunggakan untuk setiap kode tagihan ujian pada tahun pelajaran
                        {{ $tahunPelajaran->nama_hijriyah ?? '' }} H / {{ $tahunPelajaran->nama_masehi ?? '' }} M.
                    </p>
                </div>
                <span
                    class="px-3 py-1 rounded-xl text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 self-start sm:self-auto">
                    {{ $rekapPerKode->count() }} Kode Tagihan
                </span>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs min-w-[960px]">
                    <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                        <tr class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <th class="py-3 px-4 w-32">Kode Tagihan</th>
                            <th class="py-3 px-4 min-w-[180px]">Nama Tagihan</th>
                            <th class="py-3 px-3 text-center w-20">Tagihan</th>
                            <th class="py-3 px-3 text-center w-20">Lunas</th>
                            <th class="py-3 px-3 text-center w-24">Tunggakan</th>
                            <th class="py-3 px-4 text-right w-36">Target (Rp)</th>
                            <th class="py-3 px-4 text-right w-36">Terbayar (Rp)</th>
                            <th class="py-3 px-4 text-right w-36">Sisa (Rp)</th>
                            <th class="py-3 px-4 w-36">Progres</th>
                            <th class="py-3 px-4 text-right w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                        @foreach ($rekapPerKode as $rk)
                            @php $isSelectedRow = $selectedKode === $rk->kode_tagihan; @endphp
                            <tr
                                class="transition-colors {{ $isSelectedRow ? 'bg-emerald-50/60 dark:bg-emerald-950/25' : 'hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30' }}">
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-mono font-black text-xs uppercase border {{ $isSelectedRow ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20' }}">
                                        {{ $rk->kode_tagihan }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-zinc-900 dark:text-white">{{ $rk->nama_tagihan }}</div>
                                    <div class="text-[10px] font-semibold text-zinc-400 mt-0.5">
                                        {{ $rk->jumlah_level }} Level &bull;
                                        @if ($rk->nominal_min == $rk->nominal_max)
                                            Tarif Rp {{ number_format($rk->nominal_min, 0, ',', '.') }}
                                        @else
                                            Tarif Rp {{ number_format($rk->nominal_min, 0, ',', '.') }} -
                                            {{ number_format($rk->nominal_max, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($rk->total_count) }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-mono font-black">
                                        {{ number_format($rk->lunas_count) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-lg {{ $rk->belum_lunas_count > 0 ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' }} font-mono font-black">
                                        {{ number_format($rk->belum_lunas_count) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($rk->target_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($rk->terbayar_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold {{ $rk->tunggakan_nominal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    {{ number_format($rk->tunggakan_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-between text-[10px] font-black mb-1">
                                        <span class="text-zinc-500">{{ $rk->persen_lunas }}%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $rk->persen_lunas >= 80 ? 'bg-emerald-500' : ($rk->persen_lunas >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                            style="width: {{ min(100, $rk->persen_lunas) }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('pembayaran-ujian.dashboard', array_filter(['tahun_id' => $tahunPelajaranId, 'kode_tagihan' => $rk->kode_tagihan, 'ruangan_id' => $selectedRuanganId])) }}"
                                            class="px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all {{ $isSelectedRow ? 'bg-emerald-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-600 hover:text-white text-zinc-700 dark:text-zinc-300' }}"
                                            title="Fokuskan Rincian Kode {{ $rk->kode_tagihan }}">
                                            Rincian
                                        </a>
                                        <a href="{{ route('pembayaran-ujian.laporan', array_filter(['tahun_id' => $tahunPelajaranId, 'kode_tagihan' => $rk->kode_tagihan, 'start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                                            class="px-2.5 py-1.5 rounded-xl bg-sky-500/10 hover:bg-sky-600 text-sky-600 hover:text-white border border-sky-500/20 text-[10px] font-black uppercase tracking-wider transition-all"
                                            title="Lihat Daftar Kwitansi Kode {{ $rk->kode_tagihan }}">
                                            Kas
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL 2: BREAKDOWN PER RUANGAN & PER LEVEL (ALPINE TAB) -->
        <div x-data="{ tab: 'ruangan' }"
            class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden relative z-10 shadow-2xs">
            <div
                class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3
                        class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-diagram-3-fill text-sky-500"></i>
                        <span>
                            Detail Sebaran Pembayaran
                            @if ($selectedKode)
                                — Kode <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ $selectedKode }}</span>
                            @else
                                — Semua Kode Tagihan
                            @endif
                        </span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Pantau progres pelunasan per ruangan maupun agregasi per level kelas.
                    </p>
                </div>

                <!-- Switcher Tab Per Ruangan / Per Level -->
                <div
                    class="inline-flex p-1 rounded-xl bg-zinc-100/80 dark:bg-zinc-800/80 border border-zinc-200/60 dark:border-zinc-700/60 self-start sm:self-auto">
                    <button type="button" @click="tab = 'ruangan'"
                        :class="tab === 'ruangan' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-2xs' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer flex items-center gap-1.5">
                        <i class="bi bi-door-open-fill"></i>
                        <span>Per Ruangan ({{ $rekapPerRuangan->count() }})</span>
                    </button>
                    <button type="button" @click="tab = 'level'"
                        :class="tab === 'level' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-2xs' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer flex items-center gap-1.5">
                        <i class="bi bi-layers-fill"></i>
                        <span>Per Level ({{ $rekapPerLevel->count() }})</span>
                    </button>
                </div>
            </div>

            <!-- TAB CONTENT 1: PER RUANGAN -->
            <div x-show="tab === 'ruangan'" class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs min-w-[920px]">
                    <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                        <tr class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Ruangan &amp; Wali</th>
                            <th class="py-3 px-3 w-28">Level</th>
                            <th class="py-3 px-3 text-center w-20">Tagihan</th>
                            <th class="py-3 px-3 text-center w-20">Lunas</th>
                            <th class="py-3 px-3 text-center w-24">Belum</th>
                            <th class="py-3 px-4 text-right w-36">Terbayar (Rp)</th>
                            <th class="py-3 px-4 text-right w-36">Tunggakan (Rp)</th>
                            <th class="py-3 px-4 w-32">Progres</th>
                            <th class="py-3 px-4 text-right w-24">Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                        @forelse ($rekapPerRuangan as $row)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 px-4 text-center font-mono text-zinc-400">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-black text-zinc-900 dark:text-white">{{ $row->nama_ruangan }}</div>
                                    <div class="text-[10px] font-semibold text-zinc-400">{{ $row->wali_ruangan }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[10px] font-bold">
                                        {{ $row->nama_level }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $row->total_count }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $row->lunas_count }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-black {{ $row->belum_lunas_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    {{ $row->belum_lunas_count }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($row->terbayar_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $row->tunggakan_nominal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    {{ number_format($row->tunggakan_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-[10px] font-black text-zinc-500 mb-1">{{ $row->persen_lunas }}%</div>
                                    <div class="w-full h-1.5 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden">
                                        <div class="h-full rounded-full {{ $row->persen_lunas >= 80 ? 'bg-emerald-500' : ($row->persen_lunas >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                            style="width: {{ min(100, $row->persen_lunas) }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('pembayaran-ujian.index', array_filter(['tahun_id' => $tahunPelajaranId, 'ruangan_id' => $row->ruangan_id, 'pengaturan_tagihan_id' => $row->pengaturan_tagihan_id])) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-600 text-amber-600 hover:text-white border border-amber-500/20 text-[10px] font-black uppercase tracking-wider transition-all"
                                        title="Buka Kasir Leger Ruangan Ini">
                                        <i class="bi bi-wallet2"></i> Bayar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-zinc-400 italic">
                                    Belum ada tagihan terbit untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TAB CONTENT 2: PER LEVEL -->
            <div x-show="tab === 'level'" x-cloak class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs min-w-[850px]">
                    <thead class="bg-zinc-50/90 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
                        <tr class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Level / Kelas</th>
                            <th class="py-3 px-3">Tingkat</th>
                            <th class="py-3 px-3 text-center w-24">Jml Ruang</th>
                            <th class="py-3 px-3 text-center w-20">Tagihan</th>
                            <th class="py-3 px-3 text-center w-20">Lunas</th>
                            <th class="py-3 px-3 text-center w-24">Belum</th>
                            <th class="py-3 px-4 text-right w-36">Terbayar (Rp)</th>
                            <th class="py-3 px-4 text-right w-36">Tunggakan (Rp)</th>
                            <th class="py-3 px-4 w-32">Progres</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                        @forelse ($rekapPerLevel as $lv)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 px-4 text-center font-mono text-zinc-400">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 font-black text-zinc-900 dark:text-white">{{ $lv->nama_level }}</td>
                                <td class="py-3 px-3 text-zinc-500 font-semibold">{{ $lv->nama_tingkat }}</td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-zinc-600 dark:text-zinc-300">
                                    {{ $lv->jumlah_ruangan }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $lv->total_count }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $lv->lunas_count }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-black {{ $lv->belum_lunas_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    {{ $lv->belum_lunas_count }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($lv->terbayar_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $lv->tunggakan_nominal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    {{ number_format($lv->tunggakan_nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-[10px] font-black text-zinc-500 mb-1">{{ $lv->persen_lunas }}%</div>
                                    <div class="w-full h-1.5 rounded-full bg-zinc-200/80 dark:bg-zinc-800 overflow-hidden">
                                        <div class="h-full rounded-full {{ $lv->persen_lunas >= 80 ? 'bg-emerald-500' : ($lv->persen_lunas >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                            style="width: {{ min(100, $lv->persen_lunas) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-zinc-400 italic">
                                    Belum ada tagihan terbit untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
