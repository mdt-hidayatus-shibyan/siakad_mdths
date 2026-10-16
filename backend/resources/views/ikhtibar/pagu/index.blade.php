@section('title', 'Pagu Anggaran & Penarikan Sisa Divisi Ikhtibar')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>

            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Pagu Anggaran & Penarikan Sisa Divisi
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Pengawasan alokasi dana operasional, penyaluran bertahap, dan penarikan sisa kas/pagu yang tidak
                terserap.
            </p>
        </div>

        <div class="flex items-center gap-3 w-full lg:w-auto">
            <!-- Filter Dropdown Tahun Pelajaran -->
            <form action="{{ route('pagu-divisi-ikhtibar.index') }}" method="GET" class="w-full sm:w-auto">
                <div class="relative group/select w-full sm:w-64">
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
            </form>
        </div>
    </div>

    <!-- Ringkasan Finansial Pagu (6 Grid Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5 mb-6 relative z-10">
        <!-- 1. Total Pagu Disetujui -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-indigo-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Total
                    Pagu</span>
                <div
                    class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-layers-fill"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalPagu, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                {{ $divisis->count() }} Divisi Terdaftar
            </p>
        </div>

        <!-- 2. Dana Cair (Tersalurkan) -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-blue-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Dana
                    Cair</span>
                <div
                    class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-box-arrow-up-right"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalSalur, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                {{ $totalPagu > 0 ? round(($totalSalur / $totalPagu) * 100) : 0 }}% dari Pagu
            </p>
        </div>

        <!-- 3. Realisasi Belanja SPJ -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-rose-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Realisasi
                    SPJ</span>
                <div
                    class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalSpj, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Belanja Terverifikasi
            </p>
        </div>

        <!-- 4. Sisa Kas Fisik di Divisi (Peluang Tarik) -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-emerald-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Sisa
                    Kas Fisik</span>
                <div
                    class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-1">
                Rp {{ number_format($totalSisaKas, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Dipegang Divisi (Siap Ditarik)
            </p>
        </div>

        <!-- 5. Sisa Alokasi Belum Cair -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Belum
                    Cair</span>
                <div
                    class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalSisaPagu, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Sisa Pagu di BPH
            </p>
        </div>

        <!-- 6. Total Sisa Dana Ditarik / Refund -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-teal-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">Total
                    Ditarik</span>
                <div
                    class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-arrow-down-left-circle-fill"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-teal-600 dark:text-teal-400 mt-1">
                Rp {{ number_format($totalTarik, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Refund Kas ke BPH
            </p>
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="relative z-10">
        @include('ikhtibar.pagu.list', [
            'divisis' => $divisis,
            'riwayatTransaksi' => $riwayatTransaksi,
            'kegiatan' => $kegiatan,
        ])
    </div>
</x-app-layout>
