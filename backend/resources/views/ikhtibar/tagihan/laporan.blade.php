@section('title', 'Laporan Pembayaran Iuran Ikhtibar')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div class="mb-5 md:mb-6 flex flex-col xl:flex-row xl:items-start justify-between gap-4 relative z-20 print:hidden">
        <div class="w-full xl:w-auto shrink-0 mt-1 md:mt-2">
            @include('ikhtibar.tagihan.menu-kasir')
        </div>

        @if ($selectedMaster)
            <div class="w-full xl:w-auto shrink-0 mt-1 md:mt-2 flex items-center gap-2 flex-wrap justify-end">
                <x-dropdown align="right" width="w-64"
                    contentClasses="p-1.5 divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    <x-slot name="trigger">
                        <button type="button"
                            class="px-4 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-black flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer">
                            <i class="bi bi-printer-fill"></i>
                            <span>Cetak Dokumen</span>
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </button>
                    </x-slot>

                    <div class="py-1 space-y-0.5 text-xs font-bold">
                        <a href="{{ route('tagihan-ikhtibar.cetak-kartu-massal', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $selectedKampungId]) }}"
                            target="_blank"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                            <i class="bi bi-person-vcard text-emerald-500 text-sm"></i>
                            <span>{{ $selectedKampungId ? 'Cetak Kartu Dusun Terpilih' : 'Cetak Semua Kartu (Per Dusun)' }}</span>
                        </a>
                        <a href="{{ route('tagihan-ikhtibar.cetak-rekap', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $selectedKampungId]) }}"
                            target="_blank"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                            <i class="bi bi-file-earmark-text text-sky-500 text-sm"></i>
                            <span>Cetak Rekapitulasi (A4)</span>
                        </a>
                        <a href="{{ route('tagihan-ikhtibar.cetak-leger', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $selectedKampungId]) }}"
                            target="_blank"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                            <i class="bi bi-grid-1x2 text-amber-500 text-sm"></i>
                            <span>Cetak Buku Leger Dusun</span>
                        </a>
                    </div>
                </x-dropdown>
            </div>
        @endif
    </div>

    <!-- FILTER BAR LAPORAN -->
    <div
        class="m3-glass-card p-4 rounded-3xl space-y-3 bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs mb-6">
        <form method="GET" action="{{ route('tagihan-ikhtibar.laporan') }}" id="filterFormLaporan"
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

            <!-- 2. Master Tagihan Ikhtibar -->
            <div class="lg:col-span-4">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                    Jenis Tagihan KK (Ikhtibar)
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

            <!-- 3. Filter Zonasi Dusun -->
            <div class="lg:col-span-4">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                    Zonasi Dusun / Kampung
                </label>
                <div class="relative">
                    <select name="kampung_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" {{ empty($selectedKampungId) ? 'selected' : '' }}>
                            Semua Dusun / Zonasi
                        </option>
                        @foreach ($daftarKampung as $kp)
                            <option value="{{ $kp->id }}"
                                {{ (string) $selectedKampungId === (string) $kp->id ? 'selected' : '' }}>
                                {{ $kp->kode }} - {{ $kp->nama_kampung }}
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

    @if ($selectedMaster)
        <!-- KPI CARDS REKAPITULASI -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-5 mb-6">
            <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Target KK Terdaftar
                </span>
                <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white">
                    {{ number_format($totalSemuaKk, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-zinc-400">Kepala Keluarga</span>
            </div>

            <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Realisasi Kas Masuk
                </span>
                <h3 class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($totalNominalMasuk, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-zinc-400 font-bold">
                    {{ number_format($totalSemuaLunas, 0, ',', '.') }} KK Lunas
                </span>
            </div>

            <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Sisa Piutang Iuran
                </span>
                <h3 class="text-xl md:text-2xl font-black text-rose-600 dark:text-rose-400">
                    Rp {{ number_format($totalNominalPiutang, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-zinc-400 font-bold">
                    {{ number_format($totalSemuaBelumLunas, 0, ',', '.') }} KK Belum Lunas
                </span>
            </div>

            <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Persentase Capaian
                </span>
                <h3 class="text-xl md:text-2xl font-black text-primary">
                    {{ $persenTotal }}%
                </h3>
                <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-primary h-1.5 rounded-full transition-all duration-500"
                        style="width: {{ min(100, $persenTotal) }}%"></div>
                </div>
            </div>
        </div>

        <!-- TABEL REKAPITULASI PER DUSUN -->
        <div class="m3-glass-card p-5 rounded-3xl shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                <h3 class="text-sm font-black text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-primary"></i>
                    <span>Tabel Rincian Capaian Pelunasan Iuran Ikhtibar Per Dusun</span>
                </h3>
                <span class="text-xs font-bold text-zinc-400">
                    Tarif: Rp {{ number_format($selectedMaster->nominal, 0, ',', '.') }}/KK
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 uppercase tracking-wider text-[10px] font-black">
                            <th class="py-2.5 px-3">Zonasi Dusun / Kampung</th>
                            <th class="py-2.5 px-3 text-center">Target KK</th>
                            <th class="py-2.5 px-3 text-center">Lunas</th>
                            <th class="py-2.5 px-3 text-center">Belum</th>
                            <th class="py-2.5 px-3 w-40">Progres Capaian</th>
                            <th class="py-2.5 px-3 text-right">Target Dana</th>
                            <th class="py-2.5 px-3 text-right">Uang Masuk</th>
                            <th class="py-2.5 px-3 text-right">Sisa Piutang</th>
                            <th class="py-2.5 px-3 text-center w-36">Aksi / Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse ($rekapKampung as $rk)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 px-3">
                                    <span class="font-black text-zinc-900 dark:text-white block">
                                        {{ $rk['nama'] }}
                                    </span>
                                    <span class="text-[10px] text-zinc-400 font-mono">Kode: {{ $rk['kode'] }}</span>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $rk['total_kk'] }} KK
                                </td>
                                <td class="py-3 px-3 text-center font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $rk['lunas'] }}
                                </td>
                                <td class="py-3 px-3 text-center font-black text-rose-600 dark:text-rose-400">
                                    {{ $rk['belum_lunas'] }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                        <span class="text-zinc-500">{{ $rk['persentase'] }}%</span>
                                    </div>
                                    <div
                                        class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-emerald-500 h-1.5 rounded-full"
                                            style="width: {{ min(100, $rk['persentase']) }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-zinc-600 dark:text-zinc-400">
                                    Rp {{ number_format($rk['target_dana'], 0, ',', '.') }}
                                </td>
                                <td
                                    class="py-3 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($rk['realisasi'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                                    Rp {{ number_format($rk['sisa_piutang'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('tagihan-ikhtibar.cetak-kartu-massal', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $rk['id']]) }}"
                                            target="_blank"
                                            class="px-2 py-1 rounded-xl bg-amber-500/10 hover:bg-amber-500 text-amber-700 hover:text-white dark:text-amber-400 dark:hover:text-white border border-amber-500/20 font-bold text-[11px] inline-flex items-center gap-1 transition-all shadow-2xs"
                                            title="Cetak Kartu Tagihan Dusun {{ $rk['nama'] }}">
                                            <i class="bi bi-person-vcard text-[11px]"></i>
                                            <span>Kartu</span>
                                        </a>
                                        <a href="{{ route('tagihan-ikhtibar.kasir-leger', ['tahun_id' => $tahunPelajaranId, 'kampung_id' => $rk['id'], 'pengaturan_tagihan_id' => $selectedMaster->id]) }}"
                                            class="px-2 py-1 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-[11px] inline-flex items-center gap-1 transition-all"
                                            title="Buka Kasir Leger Dusun {{ $rk['nama'] }}">
                                            <i class="bi bi-grid-1x2 text-[10px]"></i>
                                            <span>Leger</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-zinc-400 italic">
                                    Tidak ada data rekapitulasi untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr
                            class="bg-zinc-100/60 dark:bg-zinc-800/40 font-black text-xs text-zinc-900 dark:text-white border-t-2 border-zinc-300 dark:border-zinc-700">
                            <td class="py-3 px-3">TOTAL KESELURUHAN</td>
                            <td class="py-3 px-3 text-center">{{ number_format($totalSemuaKk, 0, ',', '.') }} KK</td>
                            <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400">
                                {{ number_format($totalSemuaLunas, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-rose-600 dark:text-rose-400">
                                {{ number_format($totalSemuaBelumLunas, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-primary text-center">{{ $persenTotal }}% Capaian</td>
                            <td class="py-3 px-3 text-right font-mono">Rp
                                {{ number_format($totalNominalTarget, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400">Rp
                                {{ number_format($totalNominalMasuk, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-mono text-rose-600 dark:text-rose-400">Rp
                                {{ number_format($totalNominalPiutang, 0, ',', '.') }}</td>
                            <td class="py-3 px-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
