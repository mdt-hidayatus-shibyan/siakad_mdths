@section('title', 'Laporan Pembayaran Ujian')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 mb-5 md:mb-6 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('pembayaran-ujian.menu-pembayaran')
        </div>

        <!-- Filter Tahun Pelajaran -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET"
                class="m-0 relative w-full md:w-auto xl:w-[240px] m3-glass-card p-1.5 shadow-2xs">

                @if (request('start_date'))
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                @endif
                @if (request('end_date'))
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                @endif
                @if (request('ruangan_id'))
                    <input type="hidden" name="ruangan_id" value="{{ request('ruangan_id') }}">
                @endif
                @if (request('jenis_biaya'))
                    <input type="hidden" name="jenis_biaya" value="{{ request('jenis_biaya') }}">
                @endif
                @if (request('kode_tagihan'))
                    <input type="hidden" name="kode_tagihan" value="{{ request('kode_tagihan') }}">
                @endif

                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 z-10">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>

                <select name="tahun_id" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    @foreach ($daftarTahun as $tahun)
                        <option value="{{ $tahun->id }}"
                            {{ request('tahun_id', $tahunPelajaranId ?? '') == $tahun->id ? 'selected' : '' }}>
                            {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                        </option>
                    @endforeach
                </select>

                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400 z-10">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- PANEL FILTER & RINGKASAN -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-5 mb-6 md:mb-8 relative z-10 print:hidden">

        <!-- Filter Form -->
        <div class="xl:col-span-2 m3-glass-card p-4 md:p-5 shadow-2xs flex flex-col justify-center">
            <form action="{{ route('pembayaran-ujian.laporan') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end w-full">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">
                <input type="hidden" name="jenis_biaya" value="{{ $jenisBiaya }}">

                <!-- Tanggal Mulai -->
                <div class="lg:col-span-3 sm:col-span-1">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                        Tanggal Mulai
                    </label>
                    <div class="relative h-10">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-500">
                            <i class="bi bi-calendar-check-fill text-xs"></i>
                        </div>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="m3-input-glass w-full !pl-9 text-xs font-bold cursor-pointer">
                    </div>
                </div>

                <!-- Tanggal Akhir -->
                <div class="lg:col-span-3 sm:col-span-1">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                        Tanggal Akhir
                    </label>
                    <div class="relative h-10">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-500">
                            <i class="bi bi-calendar-x-fill text-xs"></i>
                        </div>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="m3-input-glass w-full !pl-9 text-xs font-bold cursor-pointer">
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="lg:col-span-3 sm:col-span-1">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                        Ruangan
                    </label>
                    <div class="relative h-10">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-500">
                            <i class="bi bi-door-open-fill text-xs"></i>
                        </div>
                        <select name="ruangan_id"
                            class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Ruangan</option>
                            @foreach ($daftarRuangan ?? [] as $r)
                                <option value="{{ $r->id }}"
                                    {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Kode Tagihan -->
                <div class="lg:col-span-3 sm:col-span-1">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                        Kode Tagihan
                    </label>
                    <div class="relative h-10">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-500">
                            <i class="bi bi-tags-fill text-xs"></i>
                        </div>
                        <select name="kode_tagihan"
                            class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Kode Tagihan</option>
                            @foreach ($daftarKodeTagihan ?? [] as $kd)
                                <option value="{{ $kd }}"
                                    {{ ($kodeTagihanFilter ?? '') === $kd ? 'selected' : '' }}>
                                    {{ $kd }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- Tombol Action -->
                <div class="lg:col-span-12 sm:col-span-2 mt-1">
                    <button type="submit"
                        class="m3-btn-primary w-full h-10 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5">
                        <i class="bi bi-funnel-fill text-xs"></i> <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Kartu Ringkasan Pendapatan -->
        <div
            class="bg-gradient-to-br from-sky-600 to-blue-700 dark:from-sky-700 dark:to-blue-900 rounded-2xl p-5 shadow-2xs flex flex-col justify-center text-white relative overflow-hidden group">
            <div
                class="absolute -top-12 -right-12 w-32 h-32 bg-white/10 rounded-2xl pointer-events-none group-hover:scale-110 transition-transform duration-500">
            </div>

            <div class="relative z-10">
                <p class="text-[10px] font-black text-sky-100 uppercase tracking-wider mb-1 flex items-center">
                    <i class="bi bi-wallet2 mr-1.5 text-xs"></i> Pendapatan Ujian & Lainnya
                </p>
                <h3 class="text-2xl md:text-3xl font-black mb-3 tracking-tight leading-none">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </h3>
                <div
                    class="inline-flex items-center gap-1.5 text-[9px] font-black text-sky-900 bg-white/90 px-2.5 py-1 rounded-lg shadow-2xs uppercase tracking-wider">
                    <i class="bi bi-receipt text-[10px]"></i> {{ $totalTransaksi }} Kwitansi
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL DATA LAPORAN -->
    <div class="m3-glass-card rounded-2xl shadow-2xs overflow-hidden relative z-10">
        <div class="overflow-x-auto relative z-10 custom-scrollbar p-0">
            <table class="w-full text-left border-collapse min-w-[1000px] text-xs">
                <thead
                    class="bg-zinc-100/80 dark:bg-zinc-800/80 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                    <tr class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3 border-r border-zinc-200/80 dark:border-zinc-800 w-44">Kwitansi & Tgl</th>
                        <th class="px-4 py-3 border-r border-zinc-200/80 dark:border-zinc-800 w-56">Murid (NISM)</th>
                        <th
                            class="px-4 py-3 border-r border-zinc-200/80 dark:border-zinc-800 w-64 text-sky-600 dark:text-sky-400">
                            Rincian Pembayaran Ujian</th>
                        <th class="px-4 py-3 border-r border-zinc-200/80 dark:border-zinc-800 w-28 text-center">Sumber
                        </th>
                        <th class="px-4 py-3 text-right w-40">Nominal Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800">
                    @forelse($laporans as $transaksi)
                        @php
                            $rincianTagihan = \App\Models\TagihanMurid::with('murid', 'ruangan')
                                ->where('pembayaran_tagihan_id', $transaksi->id)
                                ->get();

                            $rincianLain = collect();

                            foreach ($rincianTagihan as $item) {
                                $namaClean = strtolower(trim($item->nama_tagihan_spesifik));
                                if (!str_contains($namaClean, 'spp') && !str_contains($namaClean, 'syahriyah')) {
                                    $rincianLain->push($item);
                                }
                            }

                            $murids = $rincianLain->pluck('murid')->filter()->unique('id');
                        @endphp

                        @if ($rincianLain->count() > 0)
                            <tr
                                class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors duration-150 group/row">

                                <!-- 1. Kwitansi -->
                                <td class="px-4 py-3 align-top border-r border-zinc-200/60 dark:border-zinc-800">
                                    <div class="font-black text-zinc-900 dark:text-white tracking-tight mb-1 truncate"
                                        title="{{ $transaksi->no_transaksi }}">
                                        {{ $transaksi->no_transaksi }}
                                    </div>
                                    <div
                                        class="inline-flex items-center gap-1 text-[9px] font-black text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-1.5 py-0.5 rounded-md shadow-2xs tracking-wider uppercase">
                                        <i class="bi bi-calendar2-check-fill opacity-70"></i>
                                        {{ \Carbon\Carbon::parse($transaksi->tanggal_bayar)->translatedFormat('d M Y') }}
                                    </div>
                                </td>

                                <!-- 2. Nama Murid & NISM -->
                                <td class="px-4 py-3 align-top border-r border-zinc-200/60 dark:border-zinc-800">
                                    @if ($murids->count() > 0)
                                        <div class="space-y-2">
                                            @foreach ($murids as $murid)
                                                <div>
                                                    <div class="font-black text-zinc-900 dark:text-white tracking-tight leading-tight mb-0.5 truncate"
                                                        title="{{ $murid->nama_lengkap }}">
                                                        &bull; {{ $murid->nama_lengkap }}
                                                    </div>
                                                    <div class="flex gap-1.5 items-center">
                                                        <div
                                                            class="text-[8px] font-black text-zinc-400 tracking-wider uppercase truncate bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">
                                                            NISM: {{ $murid->nism ?? '-' }}
                                                        </div>
                                                        @php
                                                            $ruangannya =
                                                                $rincianLain->where('murid_id', $murid->id)->first()
                                                                    ->ruangan ?? null;
                                                        @endphp
                                                        <div
                                                            class="text-[8px] font-black text-zinc-400 tracking-wider uppercase truncate bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">
                                                            R: {{ $ruangannya->nama_ruangan ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div
                                            class="font-black text-zinc-700 dark:text-zinc-300 tracking-tight leading-tight mb-1">
                                            {{ $transaksi->nama_pembayar }}
                                        </div>
                                        <span
                                            class="text-[9px] font-black text-sky-700 dark:text-sky-400 bg-sky-500/10 border border-sky-500/20 px-1.5 py-0.5 rounded uppercase tracking-wider">
                                            Umum
                                        </span>
                                    @endif
                                </td>

                                <!-- 3. Rincian Tagihan Lainnya (Ujian) -->
                                <td class="px-4 py-3 align-top border-r border-zinc-200/60 dark:border-zinc-800">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($rincianLain as $lain)
                                            <span
                                                class="bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-500/20 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider truncate max-w-[200px]"
                                                title="{{ $lain->murid->nama_lengkap ?? '' }} - {{ $lain->nama_tagihan_spesifik }}">
                                                {{ $lain->nama_tagihan_spesifik }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- 4. Tipe Pembayar -->
                                <td
                                    class="px-4 py-3 align-middle text-center border-r border-zinc-200/60 dark:border-zinc-800">
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $transaksi->tipe_pembayar == 'Donatur' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700' }}">
                                        {{ $transaksi->tipe_pembayar }}
                                    </span>
                                </td>

                                <!-- 5. Nominal -->
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="inline-flex flex-col items-end">
                                        <span
                                            class="text-[9px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Total
                                            Kwitansi</span>
                                        <span class="text-xs font-black text-zinc-900 dark:text-white tracking-tight">
                                            <span
                                                class="text-[9px] font-bold mr-0.5 text-zinc-400">Rp</span>{{ number_format($transaksi->total_nominal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center">
                                <x-empty-state icon="bi-inboxes" title="Tidak Ada Transaksi Ujian"
                                    message="Coba ubah rentang tanggal pada filter di atas." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
