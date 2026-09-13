@section('title', 'Pembayaran Reguler KK')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div class="mb-6 flex flex-col xl:flex-row xl:items-start justify-between gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('tagihan-wali.menu-kasir')
        </div>

        <!-- Filter Tahun Pelajaran -->
        <div class="w-full xl:w-auto shrink-0">
            <form action="{{ request()->url() }}" method="GET" id="formTahun" class="m-0 relative group h-10">
                @if (request('search_wali'))
                    <input type="hidden" name="search_wali" value="{{ request('search_wali') }}">
                @endif
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahun').submit()"
                    class="m3-input-glass w-full xl:w-56 !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    @foreach ($daftarTahun as $tp)
                        <option value="{{ $tp->id }}"
                            {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- ALERT FEEDBACK -->
    @if (session('success'))
        <div
            class="p-4 mb-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:opacity-70">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div
            class="p-4 mb-6 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:opacity-70">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    <!-- PANEL PENCARIAN WALI MURID (BARCODE & KEYWORD) -->
    <div class="m3-glass-card p-3 md:p-3.5 mb-6 shadow-2xs relative z-10 print:hidden">
        <form action="{{ route('tagihan-wali.kasir') }}" method="GET"
            class="w-full flex flex-col sm:flex-row gap-2.5">
            <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

            <div class="relative w-full flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" name="search_wali" value="{{ request('search_wali') }}"
                    placeholder="Scan Barcode No. Registrasi / Ketik No. KK / NISM / Nama Kepala Keluarga..." required
                    autofocus autocomplete="off" class="m3-input-glass w-full !pl-9 text-xs font-bold">
                @if (request('search_wali'))
                    <a href="{{ route('tagihan-wali.kasir', ['tahun_id' => $tahunPelajaranId]) }}"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-rose-500"
                        title="Reset Pencarian">
                        <i class="bi bi-x-circle-fill text-sm"></i>
                    </a>
                @endif
            </div>

            <button type="submit"
                class="m3-btn-primary w-full sm:w-auto px-5 h-10 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5 shrink-0">
                <i class="bi bi-search"></i> <span>Cari</span>
            </button>
        </form>
    </div>

    @if ($waliTerpilih)
        <!-- AREA HASIL KASIR: PROFIL WALI MURID & TABEL TAGIHAN -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- KIRI: KARTU PROFIL WALI MURID / KK (COL-SPAN-4) -->
            <div class="lg:col-span-4 space-y-4">
                <div
                    class="m3-glass-card p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs sticky top-24">
                    <!-- Header Profil -->
                    <div class="flex items-center gap-3.5 pb-4 border-b border-zinc-200/60 dark:border-zinc-800">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl shrink-0 font-black shadow-2xs">
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span
                                class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                {{ $waliTerpilih->kampung?->nama_kampung ?? 'Dusun -' }}
                            </span>
                            <h3 class="text-base font-black text-zinc-900 dark:text-white truncate tracking-tight mt-1">
                                {{ $waliTerpilih->nama_kepala_keluarga }}
                            </h3>
                            <p class="text-[10px] text-zinc-400 font-mono">
                                Reg: <span
                                    class="font-bold text-zinc-700 dark:text-zinc-300">{{ $waliTerpilih->no_registrasi ?? '-' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Data Rincian KK -->
                    <div class="py-3.5 space-y-2.5 text-xs">
                        <div
                            class="flex justify-between items-center py-1 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-400 text-[11px] font-bold">Nomor KK</span>
                            <span
                                class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $waliTerpilih->no_kk ?? '-' }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-1 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-400 text-[11px] font-bold">No. WhatsApp / HP</span>
                            <span
                                class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $waliTerpilih->no_hp ?? '-' }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-1 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-400 text-[11px] font-bold">Status Keluarga</span>
                            <div>
                                @if ($waliTerpilih->is_asatidz)
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[9px] font-black bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                        Guru / Asatidz
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                        Wali Murid Reguler
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Santri Aktif di bawah KK -->
                    <div class="pt-3 border-t border-zinc-200/60 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-2.5">
                            <span
                                class="text-[10px] font-black uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span>Santri Aktif ({{ $waliTerpilih->murids->count() }})</span>
                            </span>
                        </div>

                        <div class="space-y-2">
                            @forelse ($waliTerpilih->murids as $anak)
                                <div
                                    class="p-2.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-bold text-zinc-800 dark:text-zinc-200 truncate">{{ $anak->nama_lengkap }}</span>
                                        <span
                                            class="text-[10px] font-mono text-zinc-400 font-bold">{{ $anak->nism }}</span>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 text-[10px] text-zinc-500">
                                        <span>
                                            <i class="bi bi-door-open text-zinc-400"></i>
                                            {{ $anak->ruangans->first()?->nama_ruangan ?? 'Belum ada kelas' }}
                                        </span>
                                        @php $tabungan = $anak->tabungans->first(); @endphp
                                        @if ($tabungan)
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400"
                                                title="Saldo Tabungan Santri">
                                                <i class="bi bi-wallet2"></i> Rp
                                                {{ number_format($tabungan->saldo, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-400 italic">Tidak ada santri aktif terdaftar.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: TABEL TAGIHAN WALI MURID & FORM PEMBAYARAN (COL-SPAN-8) -->
            <div class="lg:col-span-8 space-y-4">
                <div
                    class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 p-5 shadow-2xs">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-zinc-200/60 dark:border-zinc-800">
                        <div>
                            <h3
                                class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                                <i class="bi bi-receipt-cutoff text-emerald-500"></i>
                                <span>Daftar Tagihan Keluarga (Tahun Ajaran Ini)</span>
                            </h3>
                            <p class="text-xs text-zinc-400 mt-0.5">
                                Pilih tagihan yang ingin dibayar atau cetak kwitansi untuk tagihan yang telah lunas.
                            </p>
                        </div>

                        @php
                            $belumLunasTagihan = $semuaTagihan->filter(fn($t) => $t->status_bayar !== 'Lunas');
                        @endphp
                        @if ($belumLunasTagihan->isNotEmpty())
                            <button type="button" onclick="bukaModalBayarSemua()"
                                class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-1.5 shadow-2xs transition-all active:scale-95 shrink-0">
                                <i class="bi bi-cash-stack"></i>
                                <span>Bayar Semua Tagihan ({{ $belumLunasTagihan->count() }})</span>
                            </button>
                        @endif
                    </div>

                    <!-- TABEL RINCIAN TAGIHAN -->
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="border-b border-zinc-200/80 dark:border-zinc-800 text-[10px] uppercase font-black tracking-wider text-zinc-400 bg-zinc-50/50 dark:bg-zinc-800/20">
                                    <th class="py-3 px-3">No</th>
                                    <th class="py-3 px-3">Jenis Tagihan</th>
                                    <th class="py-3 px-3">Nominal Tarif</th>
                                    <th class="py-3 px-3 text-center">Status</th>
                                    <th class="py-3 px-3">Informasi Pembayaran</th>
                                    <th class="py-3 px-3 text-right">Aksi Kasir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                                @forelse ($semuaTagihan as $idx => $tagihan)
                                    @php
                                        $isLunas = $tagihan->status_bayar === 'Lunas';
                                    @endphp
                                    <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="py-3 px-3 text-zinc-400 font-mono text-[11px]">
                                            {{ $idx + 1 }}</td>
                                        <td class="py-3 px-3">
                                            <span class="font-bold text-zinc-900 dark:text-white block">
                                                {{ $tagihan->nama_tagihan_spesifik ?? $tagihan->pengaturanTagihan?->nama_tagihan }}
                                            </span>
                                            <span class="text-[10px] text-zinc-400">Sasaran: Per Kepala Keluarga
                                                (KK)
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-mono font-bold text-zinc-900 dark:text-white">
                                            Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if ($isLunas)
                                                <span
                                                    class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                                    <i class="bi bi-check-circle-fill text-xs"></i> Lunas
                                                </span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 inline-flex items-center gap-1">
                                                    <i class="bi bi-hourglass-split text-xs"></i> Belum Lunas
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-[11px]">
                                            @if ($isLunas && $tagihan->pembayaranTagihan)
                                                <div
                                                    class="font-mono text-zinc-700 dark:text-zinc-300 font-bold text-[10px]">
                                                    {{ $tagihan->pembayaranTagihan->no_transaksi }}
                                                </div>
                                                <div class="text-[10px] text-zinc-400 mt-0.5">
                                                    {{ date('d/m/Y', strtotime($tagihan->pembayaranTagihan->tanggal_bayar)) }}
                                                    •
                                                    <span
                                                        class="font-bold text-emerald-600">{{ $tagihan->pembayaranTagihan->metode_pembayaran }}</span>
                                                </div>
                                            @else
                                                <span class="text-zinc-400 italic text-[10px]">Menunggu pembayaran
                                                    kasir</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if (!$isLunas)
                                                    <!-- Tombol Bayar Single -->
                                                    <button type="button"
                                                        onclick="bukaModalBayarSingle({{ $tagihan->id }}, '{{ addslashes($tagihan->nama_tagihan_spesifik ?? $tagihan->pengaturanTagihan?->nama_tagihan) }}', {{ $tagihan->nominal_tagihan }})"
                                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95">
                                                        <i class="bi bi-wallet2"></i>
                                                        <span>Bayar</span>
                                                    </button>
                                                @else
                                                    <!-- Tombol Cetak Kwitansi -->
                                                    <a href="{{ route('tagihan-wali.cetak-kwitansi', $tagihan->id) }}"
                                                        target="_blank"
                                                        class="px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95"
                                                        title="Cetak Kwitansi Pelunasan">
                                                        <i class="bi bi-printer-fill"></i>
                                                        <span>Kwitansi</span>
                                                    </a>

                                                    <!-- Tombol Batal Transaksi -->
                                                    <form action="{{ route('tagihan-wali.batal', $tagihan->id) }}"
                                                        method="POST" class="m-0"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin MEMBATALKAN pembayaran tagihan ini?\n\nJika pembayaran menggunakan tabungan murid, saldo akan otomatis dikembalikan.')">
                                                        @csrf
                                                        <button type="submit"
                                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-600 border border-rose-500/20 text-xs font-bold transition-colors"
                                                            title="Batalkan Transaksi Pembayaran">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-zinc-400 italic">
                                            <i class="bi bi-info-circle text-2xl block mb-2 opacity-50"></i>
                                            Belum ada tagihan yang diterbitkan untuk Wali Murid ini pada tahun
                                            ajaran yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- PANDUAN KASIR & RIWAYAT TRANSAKSI HARI INI JIKA BELUM SEARCH -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Panduan Scan -->
            <div class="lg:col-span-5">
                <div
                    class="m3-glass-card p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs text-center">
                    <div
                        class="w-16 h-16 rounded-3xl bg-primary/10 text-primary flex items-center justify-center text-3xl mx-auto mb-4 border border-primary/20 shadow-2xs animate-pulse">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                        Siap Melakukan Scan KK / Santri
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                        Arahkan scanner barcode ke kartu identitas keluarga atau ketikkan nomor registrasi / nomor
                        KK / NISM santri pada bilah pencarian di atas.
                    </p>

                    <div
                        class="mt-6 pt-4 border-t border-zinc-200/60 dark:border-zinc-800 grid grid-cols-2 gap-3 text-left">
                        <div
                            class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800">
                            <span class="text-[10px] font-black uppercase text-zinc-400 block mb-1">Metode 1:
                                Reguler</span>
                            <p class="text-[11px] text-zinc-600 dark:text-zinc-300 font-bold">Input No. Registrasi
                                / No. KK / NISM</p>
                        </div>
                        <div
                            class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800">
                            <span class="text-[10px] font-black uppercase text-zinc-400 block mb-1">Metode 2:
                                Leger</span>
                            <a href="{{ route('tagihan-wali.kasir-leger') }}"
                                class="text-[11px] text-amber-600 dark:text-amber-400 font-bold hover:underline flex items-center gap-1">
                                <span>Buka Leger Dusun</span> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Transaksi Kasir Hari Ini -->
            <div class="lg:col-span-7">
                <div
                    class="m3-glass-card p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs">
                    <div
                        class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                        <div>
                            <h4
                                class="text-sm font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-1.5">
                                <i class="bi bi-clock-history text-emerald-500"></i>
                                <span>Riwayat Transaksi Pelunasan Hari Ini</span>
                            </h4>
                            <p class="text-[10px] text-zinc-400 mt-0.5">Daftar transaksi kasir yang berhasil
                                diproses pada {{ date('d F Y') }}</p>
                        </div>
                        <span
                            class="px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                            {{ $riwayatHariIni->count() }} Transaksi
                        </span>
                    </div>

                    <div class="overflow-x-auto mt-3">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr
                                    class="text-[10px] uppercase font-black text-zinc-400 border-b border-zinc-200/80 dark:border-zinc-800">
                                    <th class="py-2.5 px-2">No Kwitansi</th>
                                    <th class="py-2.5 px-2">Wali Murid</th>
                                    <th class="py-2.5 px-2">Nominal</th>
                                    <th class="py-2.5 px-2">Metode</th>
                                    <th class="py-2.5 px-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                                @forelse ($riwayatHariIni as $trx)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                        <td
                                            class="py-2.5 px-2 font-mono text-[10px] font-bold text-zinc-700 dark:text-zinc-300">
                                            {{ $trx->no_transaksi }}
                                        </td>
                                        <td class="py-2.5 px-2 font-bold text-zinc-800 dark:text-zinc-200">
                                            {{ $trx->nama_pembayar }}
                                        </td>
                                        <td class="py-2.5 px-2 font-mono font-bold text-emerald-600">
                                            Rp {{ number_format($trx->total_nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-2">
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                                                {{ $trx->metode_pembayaran }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-2 text-right">
                                            @php
                                                $sampleTagihan = \App\Models\TagihanWaliMurid::where(
                                                    'pembayaran_tagihan_id',
                                                    $trx->id,
                                                )->first();
                                            @endphp
                                            @if ($sampleTagihan)
                                                <a href="{{ route('tagihan-wali.cetak-kwitansi', $sampleTagihan->id) }}"
                                                    target="_blank"
                                                    class="px-2 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500 text-sky-600 hover:text-white border border-sky-500/20 text-[10px] font-bold transition-all">
                                                    <i class="bi bi-printer"></i> Kwitansi
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-zinc-400 italic text-[11px]">
                                            Belum ada transaksi pembayaran kasir yang dicatat hari ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL TRANSAKSI KASIR PEMBAYARAN INTERAKTIF -->
    <div id="modalBayarKasir"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="m3-glass-card w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl bg-white dark:bg-[#0c0c0e] border border-zinc-200/80 dark:border-zinc-800 animate-in fade-in zoom-in-95 duration-200">
            <form action="{{ route('tagihan-wali.bayar') }}" method="POST" id="formBayarKasir">
                @csrf
                <div id="containerModalTagihanIds"></div>

                <!-- Modal Header -->
                <div class="p-5 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center text-lg">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                                Transaksi Kasir Pembayaran
                            </h3>
                            <p class="text-[10px] font-bold text-zinc-400" id="modalSubTitleTagihan">
                                Pelunasan Tagihan Wali Murid
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="tutupModalBayar()"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">
                    <!-- Total Tagihan Display -->
                    <div
                        class="p-4 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 text-center">
                        <span
                            class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total
                            Nominal Pembayaran</span>
                        <h2 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-1"
                            id="modalDisplayTotalNominal">
                            Rp 0
                        </h2>
                    </div>

                    <!-- Pilihan Metode Pembayaran -->
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-2">
                            Metode Pembayaran
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Tunai" checked
                                    onchange="toggleMetodeKasir(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-cash text-base block mb-1"></i>
                                    <span>Tunai / Cash</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Potong Tabungan Murid"
                                    onchange="toggleMetodeKasir(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-wallet2 text-base block mb-1"></i>
                                    <span>Potong Tabungan</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Transfer Bank"
                                    onchange="toggleMetodeKasir(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-bank text-base block mb-1"></i>
                                    <span>Transfer Bank</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Cicilan"
                                    onchange="toggleMetodeKasir(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-pie-chart text-base block mb-1"></i>
                                    <span>Cicilan / Tempo</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Opsi Tambahan: Pilih Rekening Tabungan Santri (Jika metode Potong Tabungan) -->
                    <div id="panelPilihTabungan"
                        class="hidden p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-2">
                        <label
                            class="block text-[10px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">
                            Pilih Rekening Tabungan Santri Yang Dipotong
                        </label>
                        <select name="tabungan_id" id="selectTabunganId"
                            class="m3-input-glass w-full text-xs font-bold">
                            @if (isset($availableTabungans) && $availableTabungans->isNotEmpty())
                                @foreach ($availableTabungans as $tab)
                                    <option value="{{ $tab['id'] }}" data-saldo="{{ $tab['saldo'] }}">
                                        {{ $tab['nama_santri'] }} — No. Rek: {{ $tab['nomor_rekening'] }} (Saldo:
                                        {{ $tab['saldo_format'] }})
                                    </option>
                                @endforeach
                            @else
                                <option value="">(Tidak ada rekening tabungan aktif santri pada KK ini)</option>
                            @endif
                        </select>
                        <p class="text-[10px] text-amber-600/80 dark:text-amber-400/80">
                            * Saldo rekening santri terpilih akan otomatis didebet sejumlah nominal tagihan.
                        </p>
                    </div>

                    <!-- Tanggal Pembayaran & Catatan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                                Tanggal Bayar
                            </label>
                            <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}"
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                                Catatan Kasir (Opsional)
                            </label>
                            <input type="text" name="catatan" placeholder="Keterangan transaksi..."
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="p-4 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-end gap-2 bg-zinc-50/50 dark:bg-zinc-800/20">
                    <button type="button" onclick="tutupModalBayar()"
                        class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="m3-btn-primary px-5 py-2 rounded-xl text-xs font-black shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle"></i>
                        <span>Konfirmasi Pelunasan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentNominal = 0;

            function bukaModalBayarSingle(tagihanId, namaTagihan, nominal) {
                const modal = document.getElementById('modalBayarKasir');
                const container = document.getElementById('containerModalTagihanIds');
                const title = document.getElementById('modalSubTitleTagihan');
                const totalDisplay = document.getElementById('modalDisplayTotalNominal');

                currentNominal = nominal;
                container.innerHTML = `<input type="hidden" name="tagihan_ids[]" value="${tagihanId}">`;
                title.textContent = `Tagihan: ${namaTagihan}`;
                totalDisplay.textContent = 'Rp ' + nominal.toLocaleString('id-ID');

                modal.classList.remove('hidden');
            }

            function bukaModalBayarSemua() {
                @if (isset($belumLunasTagihan) && $belumLunasTagihan->isNotEmpty())
                    const modal = document.getElementById('modalBayarKasir');
                    const container = document.getElementById('containerModalTagihanIds');
                    const title = document.getElementById('modalSubTitleTagihan');
                    const totalDisplay = document.getElementById('modalDisplayTotalNominal');

                    container.innerHTML = '';
                    let totalNominal = 0;

                    @foreach ($belumLunasTagihan as $t)
                        container.innerHTML += `<input type="hidden" name="tagihan_ids[]" value="{{ $t->id }}">`;
                        totalNominal += {{ (float) $t->nominal_tagihan }};
                    @endforeach

                    currentNominal = totalNominal;
                    title.textContent = `Pelunasan {{ $belumLunasTagihan->count() }} Tagihan Sekaligus`;
                    totalDisplay.textContent = 'Rp ' + totalNominal.toLocaleString('id-ID');

                    modal.classList.remove('hidden');
                @endif
            }

            function tutupModalBayar() {
                document.getElementById('modalBayarKasir').classList.add('hidden');
            }

            function toggleMetodeKasir(metode) {
                const panelTabungan = document.getElementById('panelPilihTabungan');
                if (metode === 'Potong Tabungan Murid') {
                    panelTabungan.classList.remove('hidden');
                } else {
                    panelTabungan.classList.add('hidden');
                }
            }
        </script>
    @endpush
</x-app-layout>
