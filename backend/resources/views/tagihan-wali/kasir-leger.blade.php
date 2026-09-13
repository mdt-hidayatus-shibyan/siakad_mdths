@section('title', 'Pembayaran Leger KK')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-20 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('tagihan-wali.menu-kasir')
        </div>

        <!-- Filter Dusun & Tagihan -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET" id="formFilterLeger"
                class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto m3-glass-card p-2 shadow-2xs">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- 1. Filter Dusun / Kampung (Tanpa opsi Semua Dusun) -->
                <div class="relative w-full sm:w-[220px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-geo-alt-fill text-xs"></i>
                    </div>
                    <select name="kampung_id" onchange="document.getElementById('formFilterLeger').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarKampung as $kp)
                            <option value="{{ $kp->id }}"
                                {{ (string) $selectedKampungId === (string) $kp->id ? 'selected' : '' }}>
                                Dusun {{ $kp->nama_kampung }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-5 bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>

                <!-- 2. Filter Tagihan KK -->
                <div class="relative w-full sm:w-[240px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-tags-fill text-xs"></i>
                    </div>
                    <select name="pengaturan_tagihan_id" onchange="document.getElementById('formFilterLeger').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @forelse ($masterTagihans as $m)
                            <option value="{{ $m->id }}"
                                {{ (string) $selectedMasterId === (string) $m->id ? 'selected' : '' }}>
                                {{ $m->nama_tagihan }}
                            </option>
                        @empty
                            <option value="">-- Belum Ada Tarif Tagihan KK --</option>
                        @endforelse
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
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

    @if ($selectedKampungId && $selectedMaster)
        <!-- MATRIKS LEGER: ACTION BAR & TABEL -->
        <div class="space-y-4">
            <!-- ACTION BAR & COUNTER -->
            <div
                class="m3-glass-card p-4 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs flex flex-col lg:flex-row justify-between lg:items-center gap-4">

                <!-- Info Leger Terpilih & Statistik -->
                <div class="flex flex-wrap items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-none">
                                Dusun {{ $kampungTerpilih->nama_kampung ?? '-' }}
                            </h3>
                            <span
                                class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                {{ $selectedMaster->nama_tagihan }}
                            </span>
                        </div>
                        <div
                            class="flex items-center gap-2 mt-1 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                            <span>Tarif: <strong class="text-zinc-800 dark:text-zinc-200 font-mono">Rp
                                    {{ number_format($nominalTarif, 0, ',', '.') }}</strong></span>
                            <span>•</span>
                            <span class="text-emerald-600">{{ $totalLunasCount }} Lunas</span>
                            <span>•</span>
                            <span class="text-rose-600">{{ $totalBelumLunasCount }} Belum Lunas</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Massal & Counter Terpilih -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <!-- Tombol Centang Semua -->
                    <button type="button" id="btnCentangGlobal" data-state="none" onclick="toggleCentangSemuaLeger()"
                        class="h-10 px-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 text-zinc-700 dark:text-zinc-300 rounded-2xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center justify-center gap-1.5 shadow-2xs outline-none">
                        <i class="bi bi-check-all text-base"></i>
                        <span>Centang Semua</span>
                    </button>

                    <!-- Display Total Nominal Terpilih -->
                    <div
                        class="text-right hidden sm:flex flex-col justify-center px-3 py-1 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-[9px] font-black text-zinc-400 uppercase tracking-wider">TOTAL
                            TERPILIH</span>
                        <span class="text-sm font-black text-emerald-600 leading-none mt-0.5" id="teksTotalLeger">
                            Rp 0
                        </span>
                    </div>

                    <!-- Tombol Proses Pelunasan Massal -->
                    <button type="button" id="btnProsesLeger" disabled onclick="bukaModalBayarMassal()"
                        class="m3-btn-primary h-10 px-5 rounded-2xl text-xs font-black shadow-2xs flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed active:scale-95 transition-all">
                        <i class="bi bi-wallet2 text-xs"></i>
                        <span>Pelunasan Massal</span>
                    </button>
                </div>
            </div>

            <!-- TABEL MATRIKS LEGER KK PER DUSUN -->
            <div
                class="m3-glass-card rounded-3xl overflow-hidden border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs border-collapse min-w-[900px]">
                        <thead>
                            <tr
                                class="text-[10px] uppercase font-black tracking-wider text-zinc-400 bg-zinc-50/80 dark:bg-zinc-800/40 border-b border-zinc-200/80 dark:border-zinc-800">
                                <th class="py-3 px-3 text-center w-12">
                                    <i class="bi bi-ui-checks text-xs" title="Centang Per Baris"></i>
                                </th>
                                <th class="py-3 px-3 w-10 text-center">No</th>
                                <th class="py-3 px-4">No. Registrasi / KK</th>
                                <th class="py-3 px-4">Kepala Keluarga</th>
                                <th class="py-3 px-4">Santri Aktif & Rekening Tabungan</th>
                                <th class="py-3 px-4">Nominal</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                            @forelse ($walis as $wali)
                                @php
                                    $tagihan = $wali->current_tagihan;
                                    $isLunas = $tagihan && $tagihan->status_bayar === 'Lunas';
                                    $isBelumLunas = $tagihan && $tagihan->status_bayar === 'Belum Lunas';
                                    $isAsatidz = (bool) ($wali->is_asatidz ?? ($wali->is_ustadz ?? false));
                                @endphp
                                <tr
                                    class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/30 transition-colors {{ $isLunas ? 'bg-emerald-500/5' : '' }}">
                                    <!-- 1. Checkbox Baris -->
                                    <td class="py-3.5 px-3 text-center align-middle">
                                        @if ($isBelumLunas)
                                            <label
                                                class="relative inline-flex items-center justify-center cursor-pointer group/circle w-5 h-5">
                                                <input type="checkbox" name="selected_tagihan_ids[]"
                                                    value="{{ $tagihan->id }}"
                                                    data-nominal="{{ $tagihan->nominal_tagihan }}"
                                                    data-row="{{ $wali->id }}" class="sr-only peer chk-leger-item"
                                                    onchange="hitungTotalLeger()">
                                                <div
                                                    class="absolute inset-0 rounded-lg transition-all border bg-rose-500/10 border-rose-500/30 peer-checked:bg-amber-600 peer-checked:border-amber-700 group-hover/circle:scale-105 active:scale-95 shadow-2xs">
                                                </div>
                                                <i
                                                    class="bi bi-dash absolute text-rose-500 opacity-100 peer-checked:opacity-0 transition-opacity text-sm leading-none pointer-events-none"></i>
                                                <i
                                                    class="bi bi-check-lg absolute text-white opacity-0 peer-checked:opacity-100 transition-opacity text-xs leading-none pointer-events-none font-black"></i>
                                            </label>
                                        @elseif ($isLunas)
                                            <div class="w-5 h-5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto shadow-2xs"
                                                title="Sudah Lunas">
                                                <i class="bi bi-check-lg text-xs font-black leading-none"></i>
                                            </div>
                                        @else
                                            <span
                                                class="text-zinc-300 dark:text-zinc-700 font-black text-xs select-none">—</span>
                                        @endif
                                    </td>

                                    <!-- 2. No Index -->
                                    <td class="py-3.5 px-3 text-center text-[11px] font-bold text-zinc-400">
                                        {{ $loop->iteration }}
                                    </td>

                                    <!-- 3. No Registrasi & No KK -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                                            {{ $wali->no_registrasi ?? '-' }}
                                        </div>
                                        <div class="text-[10px] text-zinc-400 font-mono mt-0.5">
                                            KK: {{ $wali->no_kk ?? '-' }}
                                        </div>
                                    </td>

                                    <!-- 4. Kepala Keluarga -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-black text-zinc-900 dark:text-white text-xs tracking-tight">
                                            {{ $wali->nama_kepala_keluarga }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[10px] text-zinc-400 font-mono">
                                                {{ $wali->no_hp ?? '-' }}
                                            </span>
                                            @if ($isAsatidz)
                                                <span
                                                    class="px-1.5 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                                    ASATIDZ
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- 5. Santri Aktif & Rekening Tabungan -->
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-1 max-w-xs">
                                            @forelse ($wali->murids as $anak)
                                                @php
                                                    $ruanganAnak = $anak->ruangans->first();
                                                    $tabunganAnak = $anak->tabungans->first();
                                                @endphp
                                                <div
                                                    class="flex items-center justify-between text-[11px] bg-zinc-50 dark:bg-zinc-800/40 px-2 py-1 rounded-xl border border-zinc-200/50 dark:border-zinc-800">
                                                    <div class="truncate mr-2">
                                                        <span
                                                            class="font-bold text-zinc-800 dark:text-zinc-200">{{ $anak->nama_lengkap }}</span>
                                                        <span class="text-[9px] text-zinc-400">
                                                            ({{ $ruanganAnak?->nama_ruangan ?? 'Ruangan -' }})</span>
                                                    </div>
                                                    @if ($tabunganAnak)
                                                        <span
                                                            class="font-mono text-[9px] font-bold text-emerald-600 dark:text-emerald-400 shrink-0"
                                                            title="Saldo Tabungan">
                                                            Rp
                                                            {{ number_format($tabunganAnak->saldo, 0, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="text-[9px] text-zinc-400 italic shrink-0">No
                                                            Rek -</span>
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-[10px] text-zinc-400 italic">Tidak ada santri
                                                    aktif</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- 6. Nominal Tagihan -->
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-white">
                                        @if ($tagihan)
                                            Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                                        @else
                                            <span class="text-zinc-400 text-xs italic">Rp
                                                {{ number_format($nominalTarif, 0, ',', '.') }}</span>
                                        @endif
                                    </td>

                                    <!-- 7. Status Pembayaran -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($isLunas)
                                            <div class="inline-flex flex-col items-center">
                                                <span
                                                    class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1 shadow-2xs">
                                                    <i class="bi bi-check-circle-fill text-xs"></i> Lunas
                                                </span>
                                                @if ($tagihan->pembayaranTagihan)
                                                    <span class="text-[9px] font-mono text-zinc-400 mt-0.5">
                                                        {{ $tagihan->pembayaranTagihan->metode_pembayaran }} •
                                                        {{ date('d/m/y', strtotime($tagihan->pembayaranTagihan->tanggal_bayar)) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif ($isBelumLunas)
                                            <span
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 inline-flex items-center gap-1 shadow-2xs">
                                                <i class="bi bi-hourglass-split text-xs"></i> Belum Lunas
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700">
                                                Belum Terbit
                                            </span>
                                        @endif
                                    </td>

                                    <!-- 8. Aksi -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($isBelumLunas)
                                                <!-- Tombol Bayar Baris KK -->
                                                <button type="button"
                                                    onclick="bukaModalBayarRow({{ $tagihan->id }}, '{{ addslashes($wali->nama_kepala_keluarga) }}', '{{ addslashes($selectedMaster->nama_tagihan) }}', {{ $tagihan->nominal_tagihan }}, {{ json_encode($wali->available_tabungans) }})"
                                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95">
                                                    <i class="bi bi-wallet2"></i>
                                                    <span>Bayar</span>
                                                </button>
                                            @elseif ($isLunas)
                                                <!-- Tombol Cetak Kwitansi -->
                                                <a href="{{ route('tagihan-wali.cetak-kwitansi', $tagihan->id) }}"
                                                    target="_blank"
                                                    class="px-2.5 py-1.5 rounded-xl bg-sky-500/10 hover:bg-sky-500 text-sky-600 hover:text-white border border-sky-500/20 font-bold text-xs flex items-center gap-1 transition-all"
                                                    title="Cetak Kwitansi Pembayaran">
                                                    <i class="bi bi-printer-fill"></i>
                                                    <span>Kwitansi</span>
                                                </a>

                                                <!-- Tombol Batal Transaksi -->
                                                <form action="{{ route('tagihan-wali.batal', $tagihan->id) }}"
                                                    method="POST" class="m-0"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin MEMBATALKAN pembayaran tagihan KK ini?\n\nJika pembayaran menggunakan potong tabungan murid, saldo akan otomatis dikembalikan.')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white border border-rose-500/20 text-xs font-bold transition-all"
                                                        title="Batalkan Pembayaran Tagihan">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-zinc-400 text-[10px] italic">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-zinc-400 italic">
                                        <i class="bi bi-house-door text-3xl block mb-2 opacity-40"></i>
                                        Tidak ada data Kepala Keluarga aktif yang ditemukan pada Dusun
                                        {{ $kampungTerpilih->nama_kampung ?? '' }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER INFO & LEGEND -->
                <div
                    class="p-4 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div
                        class="flex items-center gap-4 flex-wrap text-[10px] font-black uppercase tracking-wider text-zinc-400">
                        <span class="flex items-center gap-1.5">
                            <span
                                class="w-3 h-3 rounded-md bg-emerald-500/20 border border-emerald-500/40 inline-block"></span>
                            Lunas
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span
                                class="w-3 h-3 rounded-md bg-rose-500/20 border border-rose-500/40 inline-block"></span>
                            Belum Lunas (Centang untuk Bayar Massal)
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-zinc-200 dark:bg-zinc-700 inline-block"></span>
                            Belum Terbit
                        </span>
                    </div>

                    <span class="text-zinc-400 text-xs font-bold">
                        Total KK di Dusun Ini: <strong>{{ $walis->count() }}</strong>
                    </span>
                </div>
            </div>
        </div>
    @else
        <!-- STATE KOSONG -->
        <x-empty-state icon="bi-grid-1x2" title="Menunggu Parameter Leger"
            message="Silakan pilih Dusun dan Jenis Tagihan pada filter di atas untuk menampilkan matriks kasir leger." />
    @endif

    <!-- 1. MODAL PELUNASAN MASSAL LEGER (BATCH PAYMENT) -->
    <div id="modalBayarMassalLeger"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="m3-glass-card w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl bg-white dark:bg-[#0c0c0e] border border-zinc-200/80 dark:border-zinc-800 animate-in fade-in zoom-in-95 duration-200">
            <form action="{{ route('tagihan-wali.kasir-leger.proses') }}" method="POST"
                id="formPelunasanMassalLeger">
                @csrf
                <div id="containerMassalTagihanIds"></div>

                <!-- Header Modal -->
                <div class="p-5 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-500/20 flex items-center justify-center text-lg">
                            <i class="bi bi-stack"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                                Pelunasan Massal Kasir Leger
                            </h3>
                            <p class="text-[10px] font-bold text-zinc-400">
                                Dusun {{ $kampungTerpilih->nama_kampung ?? '' }} •
                                {{ $selectedMaster->nama_tagihan ?? '' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="tutupModalBayarMassal()"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar text-xs">
                    <!-- Total Ringkasan -->
                    <div
                        class="p-4 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 text-center">
                        <span
                            class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400"
                            id="labelSummaryMassal">
                            Total Pelunasan 0 KK Terpilih
                        </span>
                        <h2 class="text-2xl font-black text-amber-700 dark:text-amber-400 tracking-tight mt-1"
                            id="nominalSummaryMassal">
                            Rp 0
                        </h2>
                    </div>

                    <!-- Pilihan Metode Pembayaran Massal -->
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-2">
                            Metode Pembayaran Massal
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Tunai" checked
                                    class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-cash text-base block mb-1"></i>
                                    <span>Tunai</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Transfer Bank"
                                    class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-bank text-base block mb-1"></i>
                                    <span>Transfer</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Cicilan" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-pie-chart text-base block mb-1"></i>
                                    <span>Cicilan</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Tanggal Bayar & Catatan -->
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
                            <input type="text" name="catatan" placeholder="Catatan transaksi..."
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div
                    class="p-4 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-end gap-2 bg-zinc-50/50 dark:bg-zinc-800/20">
                    <button type="button" onclick="tutupModalBayarMassal()"
                        class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="m3-btn-primary px-5 py-2 rounded-xl text-xs font-black shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle"></i>
                        <span>Proses Pelunasan Massal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. MODAL PEMBAYARAN PER BARIS KK (SINGLE ROW) -->
    <div id="modalBayarSingleRow"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="m3-glass-card w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl bg-white dark:bg-[#0c0c0e] border border-zinc-200/80 dark:border-zinc-800 animate-in fade-in zoom-in-95 duration-200">
            <form action="{{ route('tagihan-wali.bayar') }}" method="POST" id="formBayarSingleRow">
                @csrf
                <input type="hidden" name="tagihan_ids[]" id="inputSingleTagihanId">

                <!-- Modal Header -->
                <div class="p-5 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center text-lg">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight"
                                id="modalSingleTitleNamaKK">
                                Transaksi Kasir Pembayaran KK
                            </h3>
                            <p class="text-[10px] font-bold text-zinc-400" id="modalSingleSubTitle">
                                Pelunasan Tagihan
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="tutupModalBayarRow()"
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
                            Nominal Tagihan</span>
                        <h2 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-1"
                            id="modalSingleDisplayNominal">
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
                                    onchange="toggleMetodeSingle(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-cash text-base block mb-1"></i>
                                    <span>Tunai / Cash</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Potong Tabungan Murid"
                                    onchange="toggleMetodeSingle(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-wallet2 text-base block mb-1"></i>
                                    <span>Potong Tabungan</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Transfer Bank"
                                    onchange="toggleMetodeSingle(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-bank text-base block mb-1"></i>
                                    <span>Transfer Bank</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="Cicilan"
                                    onchange="toggleMetodeSingle(this.value)" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-600 text-center font-bold text-xs transition-all">
                                    <i class="bi bi-pie-chart text-base block mb-1"></i>
                                    <span>Cicilan / Tempo</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Panel Pilih Rekening Tabungan Santri (Jika metode Potong Tabungan) -->
                    <div id="panelSingleTabungan"
                        class="hidden p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-2">
                        <label
                            class="block text-[10px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">
                            Pilih Rekening Tabungan Santri Yang Dipotong
                        </label>
                        <select name="tabungan_id" id="modalSingleSelectTabungan"
                            class="m3-input-glass w-full text-xs font-bold">
                            <!-- Populated via JavaScript -->
                        </select>
                        <p class="text-[10px] text-amber-600/80 dark:text-amber-400/80">
                            * Saldo rekening tabungan santri terpilih akan otomatis didebet sejumlah nominal tagihan.
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
                    <button type="button" onclick="tutupModalBayarRow()"
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
            function hitungTotalLeger() {
                let total = 0;
                let checkedCount = 0;
                const checkboxes = document.querySelectorAll('.chk-leger-item');
                const checkedBoxes = document.querySelectorAll('.chk-leger-item:checked');

                checkboxes.forEach(chk => {
                    if (chk.checked) {
                        total += parseInt(chk.dataset.nominal || 0);
                        checkedCount++;
                    }
                });

                const formatted = 'Rp ' + total.toLocaleString('id-ID');
                document.getElementById('teksTotalLeger').innerText = formatted;

                const btn = document.getElementById('btnProsesLeger');
                if (btn) btn.disabled = (checkedCount === 0);

                // Sinkronisasi status tombol Centang Semua
                const btnGlobal = document.getElementById('btnCentangGlobal');
                if (btnGlobal && checkboxes.length > 0) {
                    const icon = btnGlobal.querySelector('i');
                    const text = btnGlobal.querySelector('span');

                    if (checkboxes.length === checkedBoxes.length) {
                        btnGlobal.dataset.state = 'all';
                        icon.className = 'bi bi-x-lg text-xs font-black';
                        text.innerText = 'Hapus Centang';
                        btnGlobal.classList.replace('text-zinc-700', 'text-rose-600');
                        btnGlobal.classList.replace('dark:text-zinc-300', 'dark:text-rose-400');
                    } else {
                        btnGlobal.dataset.state = 'none';
                        icon.className = 'bi bi-check-all text-base';
                        text.innerText = 'Centang Semua';
                        btnGlobal.classList.replace('text-rose-600', 'text-zinc-700');
                        btnGlobal.classList.replace('dark:text-rose-400', 'dark:text-zinc-300');
                    }
                }
            }

            function toggleCentangSemuaLeger() {
                const btn = document.getElementById('btnCentangGlobal');
                const isAll = btn.dataset.state === 'all';
                document.querySelectorAll('.chk-leger-item').forEach(chk => {
                    chk.checked = !isAll;
                });
                hitungTotalLeger();
            }

            function bukaModalBayarMassal() {
                const checkedBoxes = document.querySelectorAll('.chk-leger-item:checked');
                if (checkedBoxes.length === 0) return;

                const container = document.getElementById('containerMassalTagihanIds');
                const labelSummary = document.getElementById('labelSummaryMassal');
                const nominalSummary = document.getElementById('nominalSummaryMassal');
                const modal = document.getElementById('modalBayarMassalLeger');

                container.innerHTML = '';
                let total = 0;

                checkedBoxes.forEach(chk => {
                    container.innerHTML += `<input type="hidden" name="tagihan_ids[]" value="${chk.value}">`;
                    total += parseInt(chk.dataset.nominal || 0);
                });

                labelSummary.textContent = `Total Pelunasan ${checkedBoxes.length} KK Terpilih`;
                nominalSummary.textContent = 'Rp ' + total.toLocaleString('id-ID');

                modal.classList.remove('hidden');
            }

            function tutupModalBayarMassal() {
                document.getElementById('modalBayarMassalLeger').classList.add('hidden');
            }

            function bukaModalBayarRow(tagihanId, namaKK, namaTagihan, nominal, tabungans) {
                const modal = document.getElementById('modalBayarSingleRow');
                const inputId = document.getElementById('inputSingleTagihanId');
                const titleKK = document.getElementById('modalSingleTitleNamaKK');
                const subTitle = document.getElementById('modalSingleSubTitle');
                const displayNominal = document.getElementById('modalSingleDisplayNominal');
                const selectTabungan = document.getElementById('modalSingleSelectTabungan');

                inputId.value = tagihanId;
                titleKK.textContent = namaKK;
                subTitle.textContent = `Tagihan: ${namaTagihan}`;
                displayNominal.textContent = 'Rp ' + parseInt(nominal).toLocaleString('id-ID');

                // Populate tabungan options
                selectTabungan.innerHTML = '';
                if (tabungans && tabungans.length > 0) {
                    tabungans.forEach(tab => {
                        const opt = document.createElement('option');
                        opt.value = tab.id;
                        opt.textContent =
                            `${tab.nama_santri} — No. Rek: ${tab.nomor_rekening} (Saldo: ${tab.saldo_format})`;
                        selectTabungan.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = '(Tidak ada rekening tabungan aktif santri pada KK ini)';
                    selectTabungan.appendChild(opt);
                }

                modal.classList.remove('hidden');
            }

            function tutupModalBayarRow() {
                document.getElementById('modalBayarSingleRow').classList.add('hidden');
            }

            function toggleMetodeSingle(metode) {
                const panel = document.getElementById('panelSingleTabungan');
                if (metode === 'Potong Tabungan Murid') {
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                hitungTotalLeger();
            });
        </script>
    @endpush
</x-app-layout>
