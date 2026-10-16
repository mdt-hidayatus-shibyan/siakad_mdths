@section('title', 'Laporan Pertanggungjawaban (SPJ) Belanja Ikhtibar')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>

            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Laporan Belanja & SPJ Divisi
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Pencatatan nota fisik/digital belanja operasional divisi menuju penyusunan Laporan Pertanggungjawaban (LPJ).
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            <!-- Filter Dropdown & Search -->
            <form action="{{ route('spj-ikhtibar.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <!-- Dropdown Tahun Pelajaran -->
                <div class="relative group/select w-full sm:w-44">
                    <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} H | {{ $tp->nama_masehi }} M
                                {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Divisi -->
                <div class="relative group/select w-full sm:w-40">
                    <select name="divisi_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisis as $d)
                            <option value="{{ $d->id }}" {{ request('divisi_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->nama_divisi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative w-full sm:w-44">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SPJ/toko..."
                        class="m3-input-glass w-full text-xs !pl-8">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Tombol Cetak LPJ Terpadu -->
                <a href="{{ route('spj-ikhtibar.cetak-lpj', $kegiatan->id) }}" target="_blank"
                    class="m3-btn-secondary text-xs flex items-center gap-1.5 py-2 px-3 shrink-0"
                    title="Cetak Laporan Pertanggungjawaban Lengkap">
                    <i class="bi bi-printer-fill text-sm"></i>
                    <span class="hidden sm:inline">Cetak LPJ Terpadu</span>
                </a>

                <!-- Tombol Input SPJ Baru -->
                <a href="{{ route('spj-ikhtibar.create', $kegiatan->id) }}"
                    class="m3-btn-primary text-xs action-modal flex items-center gap-1.5 py-2 px-3 shrink-0 !bg-rose-600 hover:!bg-rose-700"
                    data-modal-size="lg">
                    <i class="bi bi-receipt text-sm"></i>
                    <span>Input Bukti SPJ</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik SPJ (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 relative z-10">
        <!-- 1. Total Belanja SPJ -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-rose-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl font-bold border border-rose-500/20 shrink-0">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider block">Total
                        Belanja SPJ</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        Rp {{ number_format($totalNominalSpj, 0, ',', '.') }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $spjList->count() }} Transaksi Belanja
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Total Berkas Nota Fisik/Foto -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold border border-amber-500/20 shrink-0">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Lampiran
                        Nota</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        {{ $spjList->whereNotNull('bukti_nota')->count() }} <span class="text-xs font-semibold text-zinc-500">Berkas</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Dari {{ $spjList->count() }} Total SPJ
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Persentase Serapan Pagu -->
        @php
            $totalPagu = $divisis->sum('nominal_pagu');
            $persenSerap = $totalPagu > 0 ? round(($totalNominalSpj / $totalPagu) * 100) : 0;
        @endphp
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-blue-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-bold border border-blue-500/20 shrink-0">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-wider block">Serapan
                        Pagu</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        {{ $persenSerap }}% <span class="text-xs font-semibold text-zinc-500">Terserap</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Dari Rp {{ number_format($totalPagu, 0, ',', '.') }} Pagu
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Sisa Kas di Tangan Divisi -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-purple-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold border border-purple-500/20 shrink-0">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-wider block">Sisa
                        Kas di Divisi</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        Rp {{ number_format($divisis->sum('sisa_kas_di_divisi'), 0, ',', '.') }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Kas operasional tersisa di divisi
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="relative z-10">
        @include('ikhtibar.spj.list', ['spjList' => $spjList, 'kegiatan' => $kegiatan])
    </div>
</x-app-layout>
