@section('title', 'Kas Ruangan: ' . ($ruangan->nama_ruangan ?? 'Ruangan'))

<x-app-layout>

    <!-- 1. HEADER & NAVIGASI -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-20">

        <!-- Sisi Kiri: Tombol Kembali & Identitas Ruangan -->
        <div class="flex items-center gap-3.5">
            <a href="{{ route('kas-ruangan.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-2xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0"
                title="Kembali ke Daftar Ruangan">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span
                        class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5 shadow-2xs">
                        <i class="bi bi-door-open-fill text-[10px]"></i>
                        <span>{{ $ruangan->level->nama_level ?? 'Madrasah' }}</span>
                    </span>
                    @if ($ruangan->waliRuangan)
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                            <i class="bi bi-person-badge text-emerald-500 text-xs"></i>
                            <span>{{ $ruangan->waliRuangan->nama }}</span>
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Kas {{ $ruangan->nama_ruangan }}
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Tabel pembukuan kas Murid, status cicilan, dan pencatatan pembayaran iuran.
                </p>
            </div>
        </div>

        <!-- Sisi Kanan: Action Shortcut -->
        <div class="flex items-center gap-2">
            <a href="{{ route('setoran-kas-ruangan.index') }}?ruangan_id={{ $ruangan->id }}"
                class="h-10 px-4 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center gap-2 border border-zinc-200/80 dark:border-zinc-700 shadow-2xs transition-all active:scale-95">
                <i class="bi bi-bank2 text-emerald-500"></i>
                <span>Setoran Kas</span>
            </a>
        </div>
    </div>

    <!-- 2. RINGKASAN FINANSIAL RUANGAN (4 KARTU ATAS) -->
    @php
        $terkumpul = $ruangan->total_terkumpul ?? ($ruangan->pembayaranKas->sum('jumlah_bayar') ?? 0);
        $disetor = $ruangan->total_disetor ?? 0;
        $sisaKas = max(0, $terkumpul - $disetor);
        $totalTarget = 0;
        $lunasCount = 0;

        foreach ($murids as $m) {
            $isL = strtolower($m->jenis_kelamin ?? 'l') === 'l';
            $t = $isL ? $ruangan->pengaturanKas->nominal_laki ?? 0 : $ruangan->pengaturanKas->nominal_perempuan ?? 0;
            $totalTarget += $t;

            $paid = $m->pembayaranKas->sum('jumlah_bayar');
            if ($paid >= $t && $t > 0) {
                $lunasCount++;
            }
        }
        $totalSisaKekurangan = max(0, $totalTarget - $terkumpul);
        $persenTerkumpulRuangan = $totalTarget > 0 ? min(100, round(($terkumpul / $totalTarget) * 100)) : 0;
    @endphp

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
                    Kas Terkumpul
                </p>
                <h4
                    class="text-base md:text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">
                    Rp {{ number_format($terkumpul, 0, ',', '.') }}
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
                    Rp {{ number_format($disetor, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 3. Sisa di Ruangan -->
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
                    Rp {{ number_format($sisaKas, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 4. Target Ruangan -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0 border border-indigo-500/20 shadow-2xs">
                <i class="bi bi-bullseye"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Target Ruangan
                </p>
                <h4 class="text-base md:text-xl font-black text-zinc-900 dark:text-white tracking-tight font-mono">
                    Rp {{ number_format($totalTarget, 0, ',', '.') }}
                </h4>
            </div>
        </div>

    </div>

    <!-- 3. TOOLBAR PENCARIAN & FILTER TABEL Murid -->
    <div class="mb-4 relative z-10 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">

        <!-- Input Search -->
        <div class="relative flex-1 max-w-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                <i class="bi bi-search text-xs"></i>
            </div>
            <input type="text" id="searchMuridInput" onkeyup="filterMuridTable()"
                placeholder="Cari nama Murid atau NIS..."
                class="m3-input-glass w-full h-10 !py-0 !pl-9 !pr-4 text-xs font-semibold rounded-2xl placeholder:text-zinc-400">
        </div>

        <!-- Filter Status Tab & Counter -->
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">

            <!-- Status Buttons -->
            <div
                class="flex items-center gap-1.5 bg-zinc-100/80 dark:bg-zinc-800/80 p-1 rounded-2xl border border-zinc-200/80 dark:border-zinc-700/80">
                <button type="button" onclick="filterStatus('all')" id="btnFilterAll"
                    class="filter-btn active px-3.5 h-8 rounded-xl text-xs font-black bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 shadow-2xs transition-all">
                    Semua ({{ count($murids) }})
                </button>
                <button type="button" onclick="filterStatus('belum_lunas')" id="btnFilterBelum"
                    class="filter-btn px-3.5 h-8 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all">
                    Belum Lunas ({{ count($murids) - $lunasCount }})
                </button>
                <button type="button" onclick="filterStatus('lunas')" id="btnFilterLunas"
                    class="filter-btn px-3.5 h-8 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all">
                    Lunas ({{ $lunasCount }})
                </button>
            </div>

            <!-- Counter Info -->
            <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500 hidden md:inline-block">
                <span id="visibleMuridCount"
                    class="text-zinc-900 dark:text-white font-black">{{ count($murids) }}</span> Murid
            </span>

        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 4. TABEL PEMBUKUAN KAS Murid (PRIMARY TABLE VIEW)        -->
    <!-- ========================================================= -->
    <div
        class="m3-glass-card rounded-3xl overflow-hidden shadow-2xs relative z-10 border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/60 backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr
                        class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-100/70 dark:bg-zinc-900/80 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500 font-sans">
                        <th class="py-3.5 pl-5 pr-2 w-12 text-center">No</th>
                        <th class="py-3.5 px-4 min-w-[220px]">Nama Murid</th>
                        <th class="py-3.5 px-3 text-center min-w-[90px]">NISM</th>
                        <th class="py-3.5 px-4 text-right min-w-[120px]">Target</th>
                        <th class="py-3.5 px-4 text-right min-w-[130px]">Dibayar</th>
                        <th class="py-3.5 px-4 text-right min-w-[130px]">Kekurangan</th>
                        <th class="py-3.5 px-4 text-center min-w-[120px]">Pelunasan</th>
                        <th class="py-3.5 px-3 text-center min-w-[90px]">Status</th>
                        <th class="py-3.5 pl-3 pr-5 text-center min-w-[160px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200/70 dark:divide-zinc-800/70 font-mono">
                    @forelse ($murids as $idx => $murid)
                        @php
                            $isLaki = strtolower($murid->jenis_kelamin ?? 'l') === 'l';
                            $target = $isLaki
                                ? $ruangan->pengaturanKas->nominal_laki ?? 0
                                : $ruangan->pengaturanKas->nominal_perempuan ?? 0;
                            $sudahBayar = $murid->pembayaranKas->sum('jumlah_bayar');
                            $sisa = max(0, $target - $sudahBayar);
                            $isLunas = $sisa <= 0 && $target > 0;
                            $persen = $target > 0 ? min(100, round(($sudahBayar / $target) * 100)) : 0;
                            $countTransaksi = $murid->pembayaranKas->count();
                        @endphp
                        <tr class="murid-table-row hover:bg-zinc-500/5 dark:hover:bg-zinc-800/40 transition-colors"
                            data-name="{{ strtolower($murid->nama_lengkap ?? ($murid->nama ?? '')) }}"
                            data-nis="{{ strtolower($murid->nism ?? '') }}"
                            data-status="{{ $isLunas ? 'lunas' : 'belum_lunas' }}">

                            <!-- 1. Nomor Urut -->
                            <td class="py-3.5 pl-5 pr-2 text-center text-zinc-400 font-sans font-bold text-[11px]">
                                {{ $idx + 1 }}
                            </td>

                            <!-- 2. Nama Murid & Gender Badge -->
                            <td class="py-3.5 px-4 font-sans">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 rounded-xl text-[10px] font-black flex items-center justify-center shrink-0 shadow-2xs {{ $isLaki ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' }}">
                                        {{ strtoupper($murid->jenis_kelamin ?? 'L') }}
                                    </span>
                                    <div class="min-w-0">
                                        <h4 class="font-black text-xs md:text-sm text-zinc-900 dark:text-white tracking-tight leading-snug truncate"
                                            title="{{ $murid->nama_lengkap ?? $murid->nama }}">
                                            {{ $murid->nama_lengkap ?? $murid->nama }}
                                        </h4>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. NIS -->
                            <td class="py-3.5 px-3 text-center text-zinc-500 dark:text-zinc-400 text-xs">
                                {{ $murid->nism ?? '-' }}
                            </td>

                            <!-- 4. Target -->
                            <td class="py-3.5 px-4 text-right font-black text-zinc-700 dark:text-zinc-300 text-xs">
                                Rp {{ number_format($target, 0, ',', '.') }}
                            </td>

                            <!-- 5. Dibayar -->
                            <td
                                class="py-3.5 px-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-xs">
                                Rp {{ number_format($sudahBayar, 0, ',', '.') }}
                            </td>

                            <!-- 6. Kekurangan -->
                            <td
                                class="py-3.5 px-4 text-right font-black text-xs {{ $sisa > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Rp {{ number_format($sisa, 0, ',', '.') }}
                            </td>

                            <!-- 7. Pelunasan Bar -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-zinc-200 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-300"
                                            style="width: {{ $persen }}%"></div>
                                    </div>
                                    <span
                                        class="text-[10px] font-black {{ $isLunas ? 'text-emerald-500' : 'text-zinc-400' }} min-w-[28px] text-right">
                                        {{ $persen }}%
                                    </span>
                                </div>
                            </td>

                            <!-- 8. Status Badge -->
                            <td class="py-3.5 px-3 text-center font-sans">
                                @if ($isLunas)
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[9px] font-black uppercase tracking-wider inline-flex items-center gap-1 shadow-2xs">
                                        <i class="bi bi-patch-check-fill text-[10px]"></i> Lunas
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[9px] font-black uppercase tracking-wider inline-flex items-center gap-1 shadow-2xs">
                                        <i class="bi bi-hourglass-split text-[10px]"></i> Belum
                                    </span>
                                @endif
                            </td>

                            <!-- 9. Tombol Aksi -->
                            <td class="py-3.5 pl-3 pr-5 text-center font-sans">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($sudahBayar > 0)
                                        <a href="{{ route('kas-ruangan.riwayat', ['ruangan' => $ruangan->id, 'murid' => $murid->id]) }}"
                                            class="h-8 px-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:text-emerald-600 dark:hover:text-emerald-400 text-xs font-bold flex items-center gap-1 transition-all border border-zinc-200/80 dark:border-zinc-700 shadow-2xs active:scale-95"
                                            title="Lihat Riwayat Transaksi ({{ $countTransaksi }})">
                                            <i class="bi bi-clock-history text-xs"></i>
                                            <span class="hidden sm:inline">Riwayat</span>
                                        </a>
                                    @endif

                                    @if ($sisa > 0)
                                        <button type="button"
                                            onclick="bukaModalBayar({{ $murid->id }}, '{{ addslashes($murid->nama_lengkap ?? $murid->nama) }}', {{ $sisa }}, '{{ date('Y-m-d') }}')"
                                            class="h-8 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-black text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 active:scale-95 transition-all">
                                            <i class="bi bi-wallet2 text-xs"></i>
                                            <span>Bayar</span>
                                        </button>
                                    @else
                                        <span
                                            class="h-8 px-2.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl flex items-center gap-1 text-[11px] font-black">
                                            <i class="bi bi-check2-all text-xs"></i> Lunas
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center font-sans">
                                <x-empty-state icon="bi-people" title="Belum Ada Murid"
                                    message="Belum ada Murid yang dialokasikan pada ruangan kelas ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Table Footer Ringkasan Akumulasi -->
                @if (count($murids) > 0)
                    <tfoot
                        class="border-t-2 border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-900/90 font-mono font-black text-xs">
                        <tr>
                            <td colspan="3"
                                class="py-4 pl-5 pr-3 text-left font-sans text-zinc-900 dark:text-white uppercase tracking-wider text-[11px]">
                                Total ({{ count($murids) }} Murid)
                            </td>
                            <td class="py-4 px-4 text-right text-zinc-900 dark:text-white">
                                Rp {{ number_format($totalTarget, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($terkumpul, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($totalSisaKekurangan, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-center font-sans text-[11px] text-zinc-500">
                                {{ $persenTerkumpulRuangan }}%
                            </td>
                            <td class="py-4 px-3 text-center font-sans">
                                <span
                                    class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black">
                                    {{ $lunasCount }}/{{ count($murids) }} Lunas
                                </span>
                            </td>
                            <td class="py-4 pl-3 pr-5"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Empty State Pencarian Murid -->
    <div id="noMuridSearchMatch" class="hidden col-span-full mt-6">
        <x-empty-state icon="bi-search" title="Murid Tidak Ditemukan"
            message="Tidak ada Murid yang cocok dengan kata kunci atau filter status." />
    </div>

    <!-- ========================================================= -->
    <!-- 5. MODAL CATAT PEMBAYARAN KAS                             -->
    <!-- ========================================================= -->
    <div id="modalBayar"
        class="fixed inset-0 bg-black/60 z-[99] flex items-center justify-center hidden backdrop-blur-sm p-4 transition-all">
        <div class="m3-glass-card !bg-white dark:!bg-[#0c0c0e] w-full max-w-md p-6 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 mx-auto relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="modalBayarContent">

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-1">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shadow-2xs">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight">Catat Pembayaran
                            Kas</h3>
                        <p id="modalNamaMurid" class="text-xs font-bold text-zinc-500 dark:text-zinc-400 truncate">
                        </p>
                    </div>
                </div>

                <form action="{{ route('kas-ruangan.bayar') }}" method="POST" class="space-y-4 mt-5">
                    @csrf
                    <input type="hidden" name="ruangan_id" value="{{ $ruangan->id }}">
                    <input type="hidden" name="murid_id" id="inputMuridId">

                    <!-- Tanggal Bayar -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Tanggal Bayar
                        </label>
                        <input type="date" name="tanggal_bayar" id="inputTanggal" required
                            class="m3-input-glass w-full text-xs font-bold rounded-2xl">
                    </div>

                    <!-- Jumlah Pembayaran -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Jumlah Pembayaran
                        </label>
                        <div class="relative group">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-black text-zinc-400 pointer-events-none text-xs font-mono">Rp</span>
                            <input type="number" name="jumlah_bayar" id="inputJumlah" required min="1"
                                class="m3-input-glass w-full !pl-10 font-mono font-black text-base text-zinc-900 dark:text-white rounded-2xl"
                                placeholder="0">
                        </div>

                        <!-- Quick Preset Buttons -->
                        <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                            <button type="button" onclick="setNominal(5000)"
                                class="px-3 py-1 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 transition-all active:scale-95">
                                5.000
                            </button>
                            <button type="button" onclick="setNominal(10000)"
                                class="px-3 py-1 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 transition-all active:scale-95">
                                10.000
                            </button>
                            <button type="button" onclick="setNominal(20000)"
                                class="px-3 py-1 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 transition-all active:scale-95">
                                20.000
                            </button>
                            <button type="button" onclick="setNominal(maxSisaNominal)"
                                class="px-3 py-1 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-xs font-black text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 transition-all active:scale-95">
                                Lunasi Semua
                            </button>
                        </div>

                        <p id="modalSisaTeks"
                            class="text-[10px] font-black text-amber-600 dark:text-amber-400 mt-2.5 ml-1 uppercase tracking-wider">
                        </p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex gap-2.5 pt-3">
                        <button type="button" onclick="tutupModalBayar()"
                            class="flex-1 h-11 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-2xl shadow-2xs transition-all outline-none active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                            class="m3-btn-primary flex-1 h-11 text-xs font-black shadow-md rounded-2xl">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 6. SCRIPTS -->
    <script>
        let currentStatusFilter = 'all';
        let maxSisaNominal = 0;

        function setNominal(val) {
            document.getElementById('inputJumlah').value = val;
        }

        function filterStatus(status) {
            currentStatusFilter = status;

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.className =
                    'filter-btn px-3.5 h-8 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all';
            });

            const activeBtn = status === 'all' ? document.getElementById('btnFilterAll') :
                (status === 'belum_lunas' ? document.getElementById('btnFilterBelum') : document.getElementById(
                    'btnFilterLunas'));
            if (activeBtn) {
                activeBtn.className =
                    'filter-btn active px-3.5 h-8 rounded-xl text-xs font-black bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 shadow-2xs transition-all';
            }

            filterMuridTable();
        }

        function filterMuridTable() {
            const query = (document.getElementById('searchMuridInput')?.value || '').toLowerCase().trim();
            const tableRows = document.querySelectorAll('.murid-table-row');
            let visibleCount = 0;

            tableRows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const nis = row.getAttribute('data-nis') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = !query || name.includes(query) || nis.includes(query);
                const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countEl = document.getElementById('visibleMuridCount');
            if (countEl) countEl.innerText = visibleCount;

            const emptyState = document.getElementById('noMuridSearchMatch');
            if (emptyState) {
                if (visibleCount === 0 && tableRows.length > 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
        }

        function bukaModalBayar(muridId, nama, sisa, tgl) {
            maxSisaNominal = sisa;
            document.getElementById('inputMuridId').value = muridId;
            document.getElementById('modalNamaMurid').innerText = nama;
            document.getElementById('inputJumlah').value = sisa;
            document.getElementById('inputTanggal').value = tgl;
            document.getElementById('modalSisaTeks').innerText = `* Sisa pelunasan: Rp ` + sisa.toLocaleString('id-ID');

            const modal = document.getElementById('modalBayar');
            const content = document.getElementById('modalBayarContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function tutupModalBayar() {
            const modal = document.getElementById('modalBayar');
            const content = document.getElementById('modalBayarContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</x-app-layout>
