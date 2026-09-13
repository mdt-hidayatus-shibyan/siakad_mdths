@section('title', 'Laporan Pembayaran KK')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div class="mb-5 md:mb-6 flex flex-col xl:flex-row xl:items-start justify-between gap-4 relative z-20 print:hidden">
        <div class="w-full xl:w-auto shrink-0 mt-1 md:mt-2">
            @include('tagihan-wali.menu-kasir')
        </div>

        @if ($selectedMaster)
            <div class="w-full xl:w-auto shrink-0 mt-1 md:mt-2">
                <a href="{{ route('tagihan-wali.cetak-rekap', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $selectedKampungId]) }}"
                    target="_blank"
                    class="px-4 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-black flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95">
                    <i class="bi bi-printer-fill"></i>
                    <span>Cetak Rekapitulasi (A4)</span>
                </a>
            </div>
        @endif
    </div>

    <!-- FILTER BAR LAPORAN -->
    <div
        class="m3-glass-card p-4 rounded-3xl space-y-3 bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs mb-6">
        <form method="GET" action="{{ route('tagihan-wali.laporan') }}" id="filterFormLaporan"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

            <!-- 1. Tahun Pelajaran -->
            <div class="lg:col-span-4">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                    Tahun Pelajaran
                </label>
                <div class="relative">
                    <select name="tahun_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarTahun as $tp)
                            <option value="{{ $tp->id }}"
                                {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} ({{ $tp->nama_masehi }})
                                {{ $tp->is_active ? '★ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Jenis Tagihan Sasaran Wali Murid -->
            <div class="lg:col-span-4">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                    Jenis Tagihan KK (Wali Murid)
                </label>
                <div class="relative">
                    <select name="pengaturan_tagihan_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @forelse ($masterTagihans as $mt)
                            <option value="{{ $mt->id }}"
                                {{ (string) $selectedMasterId === (string) $mt->id ? 'selected' : '' }}>
                                {{ $mt->nama_tagihan }} — Rp {{ number_format($mt->nominal, 0, ',', '.') }}
                            </option>
                        @empty
                            <option value="">(Belum ada master tarif sasaran Wali Murid)</option>
                        @endforelse
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Filter Zonasi Dusun / Kampung (DENGAN PILIHAN SEMUA DUSUN) -->
            <div class="lg:col-span-4">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                    Zonasi Dusun / Kampung
                </label>
                <div class="relative">
                    <select name="kampung_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" {{ empty($selectedKampungId) ? 'selected' : '' }}>
                            Semua Dusun / Wilayah
                        </option>
                        @foreach ($daftarKampung as $kp)
                            <option value="{{ $kp->id }}"
                                {{ (string) $selectedKampungId === (string) $kp->id ? 'selected' : '' }}>
                                Dusun {{ $kp->nama_kampung }} ({{ $kp->kode }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- STATISTIK & METRIK KEUANGAN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
        <!-- Total Target -->
        <div
            class="m3-glass-card p-4 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black tracking-wider uppercase text-zinc-400">Total Target Keuangan</span>
                <div
                    class="w-7 h-7 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">
                    <i class="bi bi-calculator"></i>
                </div>
            </div>
            <h3 class="text-lg md:text-xl font-black text-zinc-900 dark:text-white tracking-tight mt-1.5">
                Rp {{ number_format($totalTargetNominal, 0, ',', '.') }}
            </h3>
            <p class="text-[10px] text-zinc-400 mt-0.5">{{ $totalWaliAktif }} KK Terbit Tagihan</p>
        </div>

        <!-- Total Terbayar (Lunas) -->
        <div
            class="m3-glass-card p-4 rounded-3xl border border-emerald-500/20 bg-emerald-500/5 dark:bg-emerald-500/10 shadow-2xs">
            <div class="flex items-center justify-between">
                <span
                    class="text-[10px] font-black tracking-wider uppercase text-emerald-600 dark:text-emerald-400">Realisasi
                    Masuk</span>
                <div
                    class="w-7 h-7 rounded-xl bg-emerald-500/15 text-emerald-600 flex items-center justify-center text-xs font-bold">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <h3 class="text-lg md:text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-1.5">
                Rp {{ number_format($totalLunasNominal, 0, ',', '.') }}
            </h3>
            <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">
                {{ $totalLunasCount }} KK ({{ $persenLunas }}% Lunas)
            </p>
        </div>

        <!-- Sisa Tunggakan (Piutang) -->
        <div
            class="m3-glass-card p-4 rounded-3xl border border-rose-500/20 bg-rose-500/5 dark:bg-rose-500/10 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black tracking-wider uppercase text-rose-600 dark:text-rose-400">Sisa
                    Tunggakan</span>
                <div
                    class="w-7 h-7 rounded-xl bg-rose-500/15 text-rose-600 flex items-center justify-center text-xs font-bold">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <h3 class="text-lg md:text-xl font-black text-rose-600 dark:text-rose-400 tracking-tight mt-1.5">
                Rp {{ number_format($totalTunggakanNominal, 0, ',', '.') }}
            </h3>
            <p class="text-[10px] text-rose-600/80 dark:text-rose-400/80 mt-0.5">
                {{ $totalBelumLunasCount }} KK Belum Lunas
            </p>
        </div>

        <!-- Progress Pelunasan Global -->
        <div
            class="m3-glass-card p-4 rounded-3xl border border-sky-500/20 bg-sky-500/5 dark:bg-sky-500/10 flex flex-col justify-between shadow-2xs">
            <div>
                <div class="flex items-center justify-between">
                    <span
                        class="text-[10px] font-black tracking-wider uppercase text-sky-600 dark:text-sky-400">Persentase
                        Pelunasan</span>
                    <span class="text-xs font-black text-sky-600 dark:text-sky-400">{{ $persenLunas }}%</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-zinc-200 dark:bg-zinc-800 overflow-hidden mt-2">
                    <div class="h-full bg-sky-500 rounded-full transition-all duration-500"
                        style="width: {{ $persenLunas }}%"></div>
                </div>
            </div>
            <p class="text-[10px] text-sky-600/80 dark:text-sky-400/80 mt-1 font-semibold">
                {{ $totalLunasCount }} dari {{ $totalWaliAktif }} KK tagihan terbit telah lunas
            </p>
        </div>
    </div>

    <!-- REKAPITULASI PELUNASAN PER DUSUN / KAMPUNG -->
    <div
        class="m3-glass-card p-6 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-geo-alt-fill text-amber-500"></i>
                    <span>Rekapitulasi Pelunasan Tagihan Per Zonasi Dusun</span>
                </h3>
                <p class="text-xs text-zinc-400 mt-0.5">
                    Data rekapitulasi target, realisasi kas masuk, sisa piutang, dan persentase pelunasan per wilayah.
                </p>
            </div>

            <div class="text-right">
                <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block">Wilayah
                    Ditampilkan</span>
                <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                    {{ $selectedKampungId ? $daftarKampung->firstWhere('id', $selectedKampungId)?->nama_kampung ?? 'Dusun Terpilih' : 'Semua Dusun (' . $rekapPerKampung->count() . ' Dusun)' }}
                </span>
            </div>
        </div>

        <!-- TABEL MATRIKS REKAPITULASI PER DUSUN -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[750px]">
                <thead>
                    <tr
                        class="border-b border-zinc-200 dark:border-zinc-800 text-[10px] font-black uppercase tracking-wider text-zinc-400 bg-zinc-50/60 dark:bg-zinc-800/40">
                        <th class="py-3 px-3 text-center w-10">No</th>
                        <th class="py-3 px-4">Dusun / Kampung</th>
                        <th class="py-3 px-4 text-center">Total KK</th>
                        <th class="py-3 px-4 text-right">Target Tagihan</th>
                        <th class="py-3 px-4 text-right">Realisasi Lunas</th>
                        <th class="py-3 px-4 text-right">Sisa Tunggakan</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Progres Pelunasan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 text-xs font-semibold">
                    @forelse ($rekapPerKampung as $index => $rk)
                        <tr
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors {{ $rk->persen >= 100 ? 'bg-emerald-500/5' : '' }}">
                            <!-- No -->
                            <td class="py-3.5 px-3 text-center text-zinc-400 text-[11px]">
                                {{ $index + 1 }}
                            </td>

                            <!-- Nama Dusun -->
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-2 h-2 rounded-full {{ $rk->persen >= 100 ? 'bg-emerald-500' : ($rk->persen > 0 ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                    <span>Dusun {{ $rk->kampung->nama_kampung }}</span>
                                </div>
                                <span class="text-[9px] text-zinc-400 font-mono ml-4">Kode:
                                    {{ $rk->kampung->kode }}</span>
                            </td>

                            <!-- Total KK -->
                            <td class="py-3.5 px-4 text-center font-bold text-zinc-700 dark:text-zinc-300">
                                {{ $rk->total_kk }} KK
                            </td>

                            <!-- Target Tagihan -->
                            <td class="py-3.5 px-4 text-right font-mono text-zinc-800 dark:text-zinc-200">
                                Rp {{ number_format($rk->target_nominal, 0, ',', '.') }}
                            </td>

                            <!-- Realisasi Lunas -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($rk->lunas_nominal, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-zinc-400 mt-0.5">
                                    {{ $rk->lunas_count }} KK Lunas
                                </div>
                            </td>

                            <!-- Sisa Tunggakan -->
                            <td class="py-3.5 px-4 text-right">
                                <div
                                    class="font-mono font-bold {{ $rk->tunggakan_nominal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                    Rp {{ number_format($rk->tunggakan_nominal, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-zinc-400 mt-0.5">
                                    {{ $rk->belum_lunas_count }} KK Belum
                                </div>
                            </td>

                            <!-- Progres Pelunasan -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                    <span class="text-zinc-500">{{ $rk->lunas_count }}/{{ $rk->total_kk }}</span>
                                    <span
                                        class="{{ $rk->persen >= 100 ? 'text-emerald-600' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $rk->persen }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                                    <div class="h-full {{ $rk->persen >= 100 ? 'bg-emerald-500' : ($rk->persen > 0 ? 'bg-amber-500' : 'bg-rose-500') }} rounded-full transition-all duration-300"
                                        style="width: {{ $rk->persen }}%"></div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($rk->persen >= 100)
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1 shadow-2xs">
                                        <i class="bi bi-check-circle-fill text-xs"></i> Lunas 100%
                                    </span>
                                @elseif ($rk->persen > 0)
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 inline-flex items-center gap-1 shadow-2xs">
                                        <i class="bi bi-hourglass-split text-xs"></i> Berjalan
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 inline-flex items-center gap-1 shadow-2xs">
                                        <i class="bi bi-x-circle-fill text-xs"></i> 0% Lunas
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-zinc-400 italic">
                                <i class="bi bi-info-circle text-2xl block mb-2 opacity-50"></i>
                                Tidak ada data rekapitulasi untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($rekapPerKampung->isNotEmpty())
                    <tfoot
                        class="border-t-2 border-zinc-300 dark:border-zinc-700 bg-zinc-50/80 dark:bg-zinc-800/60 font-black text-xs">
                        <tr>
                            <td colspan="2"
                                class="py-3.5 px-4 text-zinc-900 dark:text-white uppercase tracking-wider">
                                TOTAL REKAPITULASI
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-zinc-900 dark:text-white">
                                {{ $totalWaliAktif }} KK
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-zinc-900 dark:text-white">
                                Rp {{ number_format($totalTargetNominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                <div>Rp {{ number_format($totalLunasNominal, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-zinc-400 font-normal">{{ $totalLunasCount }} KK</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-rose-600 dark:text-rose-400">
                                <div>Rp {{ number_format($totalTunggakanNominal, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-zinc-400 font-normal">{{ $totalBelumLunasCount }} KK
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-sky-600">
                                {{ $persenLunas }}% Terbayar
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($persenLunas >= 100)
                                    <span class="text-emerald-600 font-black text-[11px]">LUNAS TOTAL</span>
                                @else
                                    <span class="text-amber-600 font-black text-[11px]">{{ $persenLunas }}%
                                        REALISASI</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- GRID KARTU VISUALISASI DUSUN -->
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 pt-4 border-t border-zinc-200/60 dark:border-zinc-800">
            @foreach ($rekapPerKampung as $rk)
                <div
                    class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-800 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <h4 class="font-black text-xs text-zinc-900 dark:text-white flex items-center gap-1.5">
                            <i class="bi bi-geo-alt text-primary"></i>
                            Dusun {{ $rk->kampung->nama_kampung }}
                        </h4>
                        <span
                            class="px-2 py-0.5 rounded-lg text-[10px] font-black {{ $rk->persen >= 100 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-primary/10 text-primary' }}">
                            {{ $rk->persen }}% Lunas
                        </span>
                    </div>

                    <!-- Progress Bar Dusun -->
                    <div class="w-full h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <div class="h-full {{ $rk->persen >= 100 ? 'bg-emerald-500' : ($rk->persen > 0 ? 'bg-amber-500' : 'bg-rose-500') }} rounded-full transition-all duration-300"
                            style="width: {{ $rk->persen }}%"></div>
                    </div>

                    <!-- Data Grid Dusun -->
                    <div
                        class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-zinc-200/60 dark:border-zinc-700/60">
                        <div>
                            <p class="text-[9px] text-zinc-400 font-bold uppercase">Terbayar</p>
                            <p class="font-black text-emerald-600 dark:text-emerald-400">Rp
                                {{ number_format($rk->lunas_nominal, 0, ',', '.') }}</p>
                            <p class="text-[9px] text-zinc-400 font-medium">{{ $rk->lunas_count }} dari
                                {{ $rk->total_kk }} KK</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] text-zinc-400 font-bold uppercase">Tunggakan</p>
                            <p
                                class="font-black {{ $rk->tunggakan_nominal > 0 ? 'text-rose-500' : 'text-zinc-400' }}">
                                Rp
                                {{ number_format($rk->tunggakan_nominal, 0, ',', '.') }}</p>
                            <p class="text-[9px] text-zinc-400 font-medium">{{ $rk->belum_lunas_count }} KK Belum</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
