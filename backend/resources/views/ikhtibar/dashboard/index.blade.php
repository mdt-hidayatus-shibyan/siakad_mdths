@section('title', 'Dashboard Kepanitiaan Ikhtibar')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>
            
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                {{ $kegiatan->judul_lengkap }}
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                {{ $kegiatan->tema_kegiatan ?: 'Kepanitiaan & Manajemen Anggaran Kegiatan Akhir Tahun Pelajaran' }}
            </p>
        </div>

        <!-- Filter Tahun Pelajaran -->
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <form action="{{ route('dashboard-ikhtibar.index') }}" method="GET" class="w-full sm:w-auto">
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

    <!-- BANNER PERINGATAN: POTONG TABUNGAN MANUAL BELUM TERKONFIRMASI -->
    @if (!empty($totalPendingTabunganNominal) && $totalPendingTabunganNominal > 0)
        <div class="mb-5 p-4 rounded-2xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs relative z-10">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h5 class="font-black text-amber-900 dark:text-amber-200">
                        {{ $totalPendingTabunganCount }} Transaksi Potong Tabungan (Rp {{ number_format($totalPendingTabunganNominal, 0, ',', '.') }}) Belum Dikonfirmasi Admin
                    </h5>
                    <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mt-0.5">
                        Sesuai protokol keuangan, transaksi tabungan yang belum terverifikasi <strong>tidak dihitung ke Kas Masuk Ikhtibar</strong> sampai disetujui oleh Admin Tabungan.
                    </p>
                </div>
            </div>
            <a href="{{ route('tagihan-ikhtibar.kasir', ['tahun_id' => $selectedTahunId]) }}"
                class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold shrink-0 flex items-center gap-1.5 transition-colors text-xs">
                <i class="bi bi-patch-check"></i>
                <span>Tinjau di Kasir</span>
            </a>
        </div>
    @endif

    <!-- Ringkasan Finansial Utama (6 Card Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5 mb-6 relative z-10">
        <!-- 1. Penerimaan KK -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-emerald-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Penerimaan
                    KK</span>
                <div
                    class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalPenerimaanKk, 0, ',', '.') }}
            </h4>
            <div class="flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">
                <span>{{ $totalTagihanKkLunas }} Lunas</span>
                <span
                    class="font-bold text-emerald-600">{{ $targetPenerimaanKk > 0 ? round(($totalPenerimaanKk / $targetPenerimaanKk) * 100) : 0 }}%</span>
            </div>
            @if (!empty($totalPendingTabunganNominal) && $totalPendingTabunganNominal > 0)
                <p class="text-[9px] font-semibold text-amber-600 dark:text-amber-400 mt-0.5 truncate" title="Nominal tabungan belum dikonfirmasi">
                    *+Rp {{ number_format($totalPendingTabunganNominal, 0, ',', '.') }} pending tabungan
                </p>
            @endif
            <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden mt-1.5">
                <div class="bg-emerald-500 h-full rounded-full transition-all duration-500"
                    style="width: {{ $targetPenerimaanKk > 0 ? min(100, round(($totalPenerimaanKk / $targetPenerimaanKk) * 100)) : 0 }}%">
                </div>
            </div>
        </div>

        <!-- 2. Sisa Piutang KK -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Piutang
                    KK</span>
                <div
                    class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($sisaPiutangKk, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                {{ $totalTagihanKkTerbit - $totalTagihanKkLunas }} KK Belum Lunas
                @if (!empty($totalKkPendingVerifikasi) && $totalKkPendingVerifikasi > 0)
                    <span class="text-amber-600 font-bold">({{ $totalKkPendingVerifikasi }} pending)</span>
                @endif
            </p>
        </div>

        <!-- 3. Total Pagu Disetujui -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-indigo-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Total
                    Pagu Divisi</span>
                <div
                    class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-layers-fill"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalPaguAnggaran, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                {{ $divisis->count() }} Divisi Kepanitiaan
            </p>
        </div>

        <!-- 4. Dana Tersalurkan -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-blue-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Dana Cair
                    ke Divisi</span>
                <div
                    class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-box-arrow-up-right"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalDanaTersalurkan, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Belum Cair: Rp {{ number_format($totalSisaPaguBelumCair, 0, ',', '.') }}
            </p>
        </div>

        <!-- 5. Realisasi SPJ Belanja -->
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
                Rp {{ number_format($totalRealisasiSpj, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Sisa di Kas Divisi: Rp {{ number_format($totalSisaKasFisik, 0, ',', '.') }}
            </p>
        </div>

        <!-- 6. Penarikan Sisa Pagu / Refund -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-teal-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">Sisa Pagu
                    Ditarik</span>
                <div
                    class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-arrow-down-left-circle-fill"></i>
                </div>
            </div>
            <h4 class="text-base font-black text-zinc-900 dark:text-white mt-1">
                Rp {{ number_format($totalDanaDitarik, 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 truncate">
                Refund Kas & Penyesuaian
            </p>
        </div>
    </div>

    <!-- SECTION: PENERIMAAN PER BPH & POTONGAN TABUNGAN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 relative z-10">
        <!-- 1. Rekapitulasi Penerimaan Kasir per Petugas BPH (8 Col) -->
        <div class="lg:col-span-8 p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800 gap-2">
                <div>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-people-fill text-primary"></i>
                        Penerimaan Kasir per Petugas BPH
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Rincian dana tunai dan potongan tabungan yang telah diterima oleh masing-masing petugas BPH & Kasir.
                    </p>
                </div>
                <a href="{{ route('tagihan-ikhtibar.laporan', ['tahun_id' => $selectedTahunId]) }}"
                    class="text-xs font-bold text-primary dark:text-primary-light hover:underline flex items-center gap-1 self-start sm:self-auto">
                    <span>Laporan Lengkap</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                            <th class="py-2.5 px-3">Petugas BPH / Kasir</th>
                            <th class="py-2.5 px-3 text-center">Transaksi</th>
                            <th class="py-2.5 px-3 text-right">Uang Tunai</th>
                            <th class="py-2.5 px-3 text-right">Potong Tabungan</th>
                            <th class="py-2.5 px-3 text-right font-black text-emerald-600 dark:text-emerald-400">Total Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                        @forelse ($rekapPenerimaanBph as $bph)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light flex items-center justify-center font-black text-xs shrink-0">
                                            {{ strtoupper(substr($bph['nama'], 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-zinc-900 dark:text-white">{{ $bph['nama'] }}</div>
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light uppercase">
                                                {{ $bph['jabatan'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    <span class="px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[11px]">
                                        {{ $bph['total_transaksi'] }} trx
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($bph['nominal_tunai'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-semibold text-purple-600 dark:text-purple-400">
                                    Rp {{ number_format($bph['nominal_tabungan'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($bph['total_nominal'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-400 text-xs">
                                    Belum ada transaksi pembayaran yang tercatat oleh petugas BPH pada tahun ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($rekapPenerimaanBph) > 0)
                        <tfoot>
                            <tr class="border-t-2 border-zinc-200 dark:border-zinc-700 font-black text-zinc-900 dark:text-white bg-zinc-50/50 dark:bg-zinc-800/40">
                                <td class="py-3 px-3 uppercase text-[10px] tracking-wider">Total Kasir BPH</td>
                                <td class="py-3 px-3 text-center">{{ $rekapPenerimaanBph->sum('total_transaksi') }} trx</td>
                                <td class="py-3 px-3 text-right">Rp {{ number_format($rekapPenerimaanBph->sum('nominal_tunai'), 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-purple-600 dark:text-purple-400">Rp {{ number_format($rekapPenerimaanBph->sum('nominal_tabungan'), 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">Rp {{ number_format($rekapPenerimaanBph->sum('total_nominal'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- 2. Ringkasan Pembayaran via Potongan Tabungan (4 Col) -->
        <div class="lg:col-span-4 p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-purple-500/20 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-wallet2 text-purple-600 dark:text-purple-400"></i>
                        Potongan Tabungan
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 uppercase">
                        Tabungan
                    </span>
                </div>

                <div class="mt-4 p-4 rounded-xl bg-purple-50/60 dark:bg-purple-950/20 border border-purple-200/60 dark:border-purple-800/40">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">
                        Total Dana Potongan Tabungan
                    </span>
                    <h4 class="text-xl font-black text-zinc-900 dark:text-white mt-0.5">
                        Rp {{ number_format($rekapPotongTabungan['total_nominal'], 0, ',', '.') }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">
                        Total <strong>{{ $rekapPotongTabungan['total_transaksi'] }}</strong> transaksi pemotongan saldo tabungan santri/wali.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <!-- Terverifikasi -->
                    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block uppercase">
                            Terverifikasi Admin
                        </span>
                        <div class="text-sm font-black text-emerald-700 dark:text-emerald-300 mt-0.5">
                            Rp {{ number_format($rekapPotongTabungan['verified_nominal'], 0, ',', '.') }}
                        </div>
                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400">
                            {{ $rekapPotongTabungan['verified_count'] }} transaksi sah
                        </span>
                    </div>

                    <!-- Pending Verifikasi -->
                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block uppercase">
                            Menunggu Verifikasi
                        </span>
                        <div class="text-sm font-black text-amber-700 dark:text-amber-300 mt-0.5">
                            Rp {{ number_format($rekapPotongTabungan['pending_nominal'], 0, ',', '.') }}
                        </div>
                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400">
                            {{ $rekapPotongTabungan['pending_count'] }} transaksi pending
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-right">
                <a href="{{ route('tagihan-ikhtibar.kasir', ['tahun_id' => $selectedTahunId]) }}"
                    class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center justify-end gap-1">
                    <span>Buka Kasir Tagihan</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Layout 2 Kolom: Monitoring Divisi & Mutasi Kas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-10">
        <!-- Kolom Kiri: Tabel Realisasi Pagu per Divisi (8 Col) -->
        <div class="lg:col-span-8 space-y-6">
            <div
                class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                            Monitoring Pagu & Sisa Kas Divisi
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Pantau pergerakan pencairan, realisasi belanja, dan sisa kas yang siap ditarik kembali oleh
                            Bendahara BPH.
                        </p>
                    </div>
                    <a href="{{ route('pagu-divisi-ikhtibar.index', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                        class="text-xs font-bold text-primary dark:text-primary-light hover:underline flex items-center gap-1">
                        <span>Detail Pagu</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                                <th class="py-2.5 px-3">Divisi</th>
                                <th class="py-2.5 px-3 text-right">Pagu Disetujui</th>
                                <th class="py-2.5 px-3 text-right">Tersalurkan</th>
                                <th class="py-2.5 px-3 text-right">SPJ Belanja</th>
                                <th class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400">Sisa Kas Fisik
                                </th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                            @forelse ($divisis as $div)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-zinc-900 dark:text-white">{{ $div->nama_divisi }}
                                        </div>
                                        <div class="text-[10px] text-zinc-400 font-normal">PJ:
                                            {{ $div->koordinator?->nama_tampil ?? 'Belum Ditentukan' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold">
                                        Rp {{ number_format($div->nominal_pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right text-blue-600 dark:text-blue-400 font-semibold">
                                        Rp {{ number_format($div->total_tersalurkan, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right text-rose-600 dark:text-rose-400 font-semibold">
                                        Rp {{ number_format($div->total_realisasi_spj, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($div->sisa_kas_di_divisi, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $div->status_pagu_badge_color }}">
                                            {{ $div->status_pagu }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-zinc-400 text-xs">
                                        Belum ada divisi terdaftar pada tahun ajaran ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Pengeluaran SPJ Terbaru -->
            <div
                class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        SPJ Belanja Terbaru
                    </h3>
                    <a href="{{ route('spj-ikhtibar.index', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                        class="text-xs font-bold text-primary dark:text-primary-light hover:underline">
                        Lihat Semua SPJ
                    </a>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800 mt-2">
                    @forelse ($pengeluaranTerbaru as $spj)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-zinc-900 dark:text-white truncate">
                                    {{ $spj->judul_pengeluaran }}</div>
                                <div class="text-[11px] text-zinc-400 flex items-center gap-2 mt-0.5">
                                    <span>{{ $spj->divisi?->nama_divisi ?? 'BPH Inti' }}</span>
                                    <span>•</span>
                                    <span>{{ $spj->tanggal_nota?->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span class="text-[10px] text-zinc-500 font-semibold">
                                        Diinput: {{ $spj->diinputOleh?->nama_tampil ?? 'Panitia' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right font-black text-zinc-900 dark:text-white shrink-0 ml-3">
                                Rp {{ number_format($spj->nominal, 0, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-zinc-400 text-xs">
                            Belum ada transaksi SPJ yang dicatat.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Posisi Kas BPH & Rekap Tagihan Per Kampung (4 Col) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Saldo Kas Bersih BPH -->
            <div
                class="p-5 rounded-2xl bg-gradient-to-br from-primary/10 via-primary/5 to-transparent border border-primary/20 shadow-sm relative overflow-hidden">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center text-lg font-bold shadow-md shadow-primary/20">
                        <i class="bi bi-safe2-fill"></i>
                    </div>
                    <div>
                        <span
                            class="text-[10px] font-black uppercase tracking-wider text-primary dark:text-primary-light block">Posisi
                            Kas BPH</span>
                        <h4 class="text-xl font-black text-zinc-900 dark:text-white mt-0.5">
                            Rp {{ number_format($saldoKasBersihBph, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-3 leading-relaxed">
                    Dana riil yang dipegang Bendahara BPH (Total Penerimaan KK + Penarikan Sisa Kas − Penyaluran ke
                    Divisi).
                </p>
                <div class="mt-4 pt-3 border-t border-primary/10 flex items-center justify-between text-xs font-bold">
                    <a href="{{ route('tagihan-ikhtibar.index', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                        class="text-primary hover:underline">
                        Loket Tagihan KK &rarr;
                    </a>
                    <a href="{{ route('pagu-divisi-ikhtibar.index', ['tahun_pelajaran_id' => $selectedTahunId]) }}"
                        class="text-primary hover:underline">
                        Tarik Sisa Kas &rarr;
                    </a>
                </div>
            </div>

            <!-- Rekap Realisasi Tagihan Per Dusun/Kampung -->
            <div
                class="p-5 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
                <h3
                    class="text-sm font-black text-zinc-900 dark:text-white tracking-tight flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <span>Realisasi KK per Dusun</span>
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                </h3>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800 mt-2 space-y-2">
                    @forelse ($kampungStatistik as $kStat)
                        <div class="pt-2 text-xs">
                            <div class="flex items-center justify-between font-bold text-zinc-900 dark:text-white">
                                <span>({{ $kStat['kode'] }}) {{ $kStat['nama'] }}</span>
                                <span class="text-primary font-black">{{ $kStat['persen'] }}%</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-0.5">
                                <span>{{ $kStat['lunas'] }} / {{ $kStat['total'] }} KK Lunas</span>
                                <span>Rp {{ number_format($kStat['nominal_lunas'], 0, ',', '.') }}</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden mt-1.5">
                                <div class="bg-primary h-full rounded-full transition-all duration-300"
                                    style="width: {{ $kStat['persen'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-zinc-400 text-xs">
                            Belum ada data kampung terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Riwayat Mutasi Penyaluran & Penarikan Terakhir -->
            <div
                class="p-5 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
                <h3
                    class="text-sm font-black text-zinc-900 dark:text-white tracking-tight flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <span>Mutasi Kas Divisi Terakhir</span>
                    <i class="bi bi-arrow-left-right text-primary"></i>
                </h3>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800 mt-2">
                    @forelse ($transaksiPenyaluranTerbaru as $trx)
                        <div class="py-2.5 text-xs flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="w-2 h-2 rounded-full {{ $trx->jenis_transaksi === 'Penyaluran Pagu' ? 'bg-blue-500' : 'bg-emerald-500' }}"></span>
                                    <span class="font-bold text-zinc-900 dark:text-white truncate">
                                        {{ $trx->divisi?->nama_divisi }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-zinc-400 mt-0.5">
                                    {{ $trx->jenis_transaksi }} • {{ $trx->tanggal_penyaluran?->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="text-right shrink-0 ml-2">
                                <div
                                    class="font-bold {{ $trx->jenis_transaksi === 'Penyaluran Pagu' ? 'text-blue-600 dark:text-blue-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $trx->jenis_transaksi === 'Penyaluran Pagu' ? '-' : '+' }} Rp
                                    {{ number_format($trx->nominal, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-zinc-400 text-xs">
                            Belum ada mutasi kas divisi.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
