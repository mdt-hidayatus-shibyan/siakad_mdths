@section('title', 'Kelola Tagihan Per Wali Murid')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20 print:hidden">
        <div>
            <h2
                class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span
                    class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="bi bi-houses-fill"></i>
                </span>
                <span>Tagihan Per Wali Murid (KK)</span>
            </h2>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1">
                Kelola penerbitan, monitoring, dan pembayaran tagihan tingkat Kepala Keluarga / Wali Murid aktif.
            </p>
        </div>

        <!-- Filter Tahun Pelajaran -->
        <div class="w-full md:w-auto shrink-0 flex items-center gap-2.5">
            <form action="{{ request()->url() }}" method="GET" id="formTahun"
                class="m-0 relative m3-glass-card p-1.5 shadow-2xs w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahun').submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 cursor-pointer appearance-none text-xs font-bold">
                    @foreach ($daftarTahun as $tahun)
                        <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                            {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI STATS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-5 mb-6 md:mb-8 relative z-10">
        <!-- 1. Total KK Aktif -->
        <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider">Wali Murid
                    Aktif</span>
                <div
                    class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center text-sm shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ number_format($totalWaliAktif, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] font-bold text-zinc-400">Kepala Keluarga</span>
            </div>
        </div>

        <!-- 2. Total Target Tagihan -->
        <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider">Total
                    Target</span>
                <div
                    class="w-8 h-8 rounded-xl bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 flex items-center justify-center text-sm shrink-0">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-xs font-bold text-zinc-400">Rp</span>
                <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ number_format($totalTargetNominal, 0, ',', '.') }}
                </h3>
            </div>
        </div>

        <!-- 3. Total Terbayar / Lunas -->
        <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider">Total
                    Lunas</span>
                <div
                    class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm shrink-0">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Rp</span>
                <h3 class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                    {{ number_format($totalLunasNominal, 0, ',', '.') }}
                </h3>
            </div>
            <div class="flex items-center gap-1.5 mt-1.5">
                <span class="text-[10px] font-bold text-zinc-400">
                    {{ $totalLunasCount }} KK Lunas ({{ $persenLunas }}%)
                </span>
            </div>
        </div>

        <!-- 4. Total Sisa Tunggakan -->
        <div class="m3-glass-card p-4 md:p-5 rounded-3xl shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[10px] md:text-xs font-black text-zinc-400 uppercase tracking-wider">Sisa
                    Tunggakan</span>
                <div
                    class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center text-sm shrink-0">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Rp</span>
                <h3 class="text-xl md:text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                    {{ number_format($totalTunggakanNominal, 0, ',', '.') }}
                </h3>
            </div>
            <div class="flex items-center gap-1.5 mt-1.5">
                <span class="text-[10px] font-bold text-zinc-400">
                    {{ $totalBelumLunasCount }} KK Belum Lunas
                </span>
            </div>
        </div>
    </div>

    <!-- FILTER & ACTION TOOLBAR -->
    <div class="m3-glass-card p-4 md:p-5 mb-6 md:mb-8 relative z-10 shadow-2xs">
        <form action="{{ request()->url() }}" method="GET" id="formFilterWali" class="space-y-3">
            <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- 1. Pilihan Master Tagihan -->
                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">
                        Jenis Tagihan Per KK
                    </label>
                    <div class="relative">
                        <select name="pengaturan_tagihan_id"
                            onchange="document.getElementById('formFilterWali').submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            @forelse ($masterTagihans as $m)
                                <option value="{{ $m->id }}"
                                    {{ $selectedMasterId == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_tagihan }} (Rp {{ number_format($m->nominal, 0, ',', '.') }})
                                </option>
                            @empty
                                <option value="">-- Belum Ada Tarif Tagihan KK --</option>
                            @endforelse
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- 2. Pilihan Kampung -->
                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">
                        Zonasi Kampung
                    </label>
                    <div class="relative">
                        <select name="kampung_id" onchange="document.getElementById('formFilterWali').submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Kampung / Dusun</option>
                            @foreach ($daftarKampung as $k)
                                <option value="{{ $k->id }}"
                                    {{ request('kampung_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->kode }} - {{ $k->nama_kampung }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- 3. Status Bayar -->
                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">
                        Status Pembayaran
                    </label>
                    <div class="relative">
                        <select name="status_bayar" onchange="document.getElementById('formFilterWali').submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Status</option>
                            <option value="Belum Lunas"
                                {{ request('status_bayar') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="Lunas" {{ request('status_bayar') == 'Lunas' ? 'selected' : '' }}>Lunas
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- 4. Search Input -->
                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">
                        Pencarian KK / Santri
                    </label>
                    <div class="relative flex items-center">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama KK / No KK / Santri..."
                            class="m3-input-glass w-full !pr-10 text-xs font-bold">
                        <button type="submit"
                            class="absolute right-0 inset-y-0 px-3 text-zinc-400 hover:text-primary transition-colors">
                            <i class="bi bi-search text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div
                class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-zinc-200/80 dark:border-zinc-800">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="toggleCentangSemuaWali()"
                        class="h-9 px-3.5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none">
                        <i class="bi bi-check-all text-sm"></i> <span>Centang Semua</span>
                    </button>

                    <span id="selectedCountBadge"
                        class="hidden text-xs font-black text-primary dark:text-primary-dark px-3 py-1.5 rounded-xl bg-primary/10 border border-primary/20 items-center gap-1.5 shadow-2xs">
                        <i class="bi bi-check2-circle text-xs"></i>
                        <span id="selectedCountText">0 Terpilih</span>
                    </span>

                    <button type="button" onclick="bukaModalBayarTerpilih()" id="btnBayarMassal"
                        class="h-9 px-3.5 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500 hover:text-white dark:hover:bg-emerald-500 dark:hover:text-white text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none">
                        <i class="bi bi-cash-coin text-xs"></i> <span>Bayar Tercentang</span>
                    </button>

                    @if ($selectedMaster)
                        <button type="button" onclick="submitTerbitkanTercentang()" id="btnTerbitkanTercentang"
                            class="h-9 px-3.5 bg-indigo-500/10 border border-indigo-500/20 hover:bg-indigo-500 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none">
                            <i class="bi bi-lightning-charge text-xs"></i> <span>Terbitkan Tercentang</span>
                        </button>

                        <button type="button" onclick="submitHapusTagihanTercentang()" id="btnHapusTercentang"
                            class="h-9 px-3.5 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-500 dark:hover:text-white text-rose-600 dark:text-rose-400 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none"
                            title="Hapus tagihan belum lunas dari baris tercentang">
                            <i class="bi bi-trash3 text-xs"></i> <span>Hapus Tercentang</span>
                        </button>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($selectedMaster)
                        <!-- Tombol Terbitkan Tagihan Massal (Seluruh Wali Aktif) -->
                        <form action="{{ route('tagihan-wali.terbitkan') }}" method="POST" class="m-0"
                            onsubmit="return confirm('Apakah Anda yakin ingin menerbitkan tagihan \'{{ $selectedMaster->nama_tagihan }}\' untuk seluruh Wali Murid Aktif di tahun pelajaran ini?')">
                            @csrf
                            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
                            <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">
                            <button type="submit"
                                class="h-9 px-4 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-wider hover:opacity-90 transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none">
                                <i class="bi bi-lightning-charge-fill text-xs"></i>
                                <span>Terbitkan Semua Massal</span>
                            </button>
                        </form>

                        <!-- Tombol Hapus Semua Tagihan Belum Lunas -->
                        <form action="{{ route('tagihan-wali.hapus-semua') }}" method="POST" class="m-0"
                            onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA tagihan \'{{ $selectedMaster->nama_tagihan }}\' yang BELUM LUNAS di tahun pelajaran ini?')">
                            @csrf
                            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
                            <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">
                            <button type="submit"
                                class="h-9 px-3.5 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-500 dark:hover:text-white text-rose-600 dark:text-rose-400 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none"
                                title="Hapus seluruh tagihan belum lunas untuk jenis tarif ini">
                                <i class="bi bi-trash text-xs"></i>
                                <span>Hapus Semua Tagihan</span>
                            </button>
                        </form>

                        <!-- Tombol Cetak Rekap -->
                        <a href="{{ route('tagihan-wali.cetak-rekap', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => request('kampung_id')]) }}"
                            target="_blank"
                            class="h-9 px-3.5 bg-sky-500/10 border border-sky-500/20 hover:bg-sky-500 hover:text-white dark:hover:bg-sky-500 dark:hover:text-white text-sky-600 dark:text-sky-400 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center gap-1.5 shadow-2xs outline-none">
                            <i class="bi bi-printer-fill text-xs"></i> <span>Cetak Rekap</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if ($selectedMaster)
        <!-- Hidden Form untuk Terbitkan Tercentang -->
        <form action="{{ route('tagihan-wali.terbitkan') }}" method="POST" id="formTerbitkanTercentang"
            class="hidden">
            @csrf
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
            <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">
            <div id="containerWaliIdsTercentang"></div>
        </form>

        <!-- Hidden Form untuk Hapus Tercentang -->
        <form action="{{ route('tagihan-wali.hapus-massal') }}" method="POST" id="formHapusTercentang"
            class="hidden">
            @csrf
            <div id="containerHapusTagihanIds"></div>
        </form>
    @endif

    <!-- AREA TABEL MATRIKS WALI MURID -->
    <div class="relative z-10" id="data-table-container">
        @if ($walis->isNotEmpty())
            <div class="overflow-x-auto custom-scrollbar pb-4">
                <table class="w-full text-left text-xs border-separate border-spacing-y-2.5 min-w-[1000px]">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <!-- Checkbox Header -->
                            <th
                                class="py-3 px-3.5 text-center bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-l border-zinc-200/80 dark:border-zinc-800 rounded-l-2xl w-[48px] shadow-2xs">
                                <input type="checkbox" id="checkAllWali" onchange="toggleCheckAllCheckbox(this)"
                                    class="w-4 h-4 rounded-lg border-zinc-300 dark:border-zinc-600 text-primary focus:ring-primary/30 bg-white/50 dark:bg-black/50 cursor-pointer"
                                    title="Centang Semua Baris">
                            </th>
                            <!-- Info Wali Murid -->
                            <th
                                class="py-3 px-4 bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-zinc-200/80 dark:border-zinc-800 w-[280px] shadow-2xs">
                                Kepala Keluarga / Wali
                            </th>
                            <!-- Tanggungan Santri Aktif -->
                            <th
                                class="py-3 px-4 bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                                Tanggungan Santri Aktif
                            </th>
                            <!-- Nominal & Status -->
                            <th
                                class="py-3 px-4 text-right bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-zinc-200/80 dark:border-zinc-800 w-[180px] shadow-2xs">
                                Tagihan & Status
                            </th>
                            <!-- Aksi -->
                            <th
                                class="py-3 px-4 text-center bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-r border-zinc-200/80 dark:border-zinc-800 rounded-r-2xl w-[160px] shadow-2xs">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($walis as $wali)
                            @php
                                $tagihan = $wali->current_tagihan;
                                $isLunas = $tagihan && $tagihan->status_bayar === 'Lunas';
                                $isBelumTerbit = !$tagihan;
                            @endphp
                            <tr class="m3-glass-card group hover:border-primary/40 transition-all duration-200">
                                <!-- Checkbox Selalu Tersedia di Setiap Baris -->
                                <td
                                    class="py-3.5 px-3.5 text-center align-middle rounded-l-2xl border-y border-l border-zinc-200/80 dark:border-zinc-800">
                                    <input type="checkbox" value="{{ $wali->id }}"
                                        data-wali-id="{{ $wali->id }}"
                                        data-tagihan-id="{{ $tagihan?->id ?? '' }}"
                                        data-status="{{ $isBelumTerbit ? 'belum_terbit' : ($isLunas ? 'lunas' : 'belum_lunas') }}"
                                        data-nama="{{ $wali->nama_kepala_keluarga }}"
                                        data-nominal="{{ $tagihan?->nominal_tagihan ?? ($selectedMaster?->nominal ?? 0) }}"
                                        onchange="updateSelectedState()"
                                        class="wali-checkbox w-4 h-4 rounded-lg border-zinc-300 dark:border-zinc-600 text-primary focus:ring-primary/30 bg-white/50 dark:bg-black/50 cursor-pointer">
                                </td>

                                <!-- Info Wali -->
                                <td class="py-3.5 px-4 align-middle border-y border-zinc-200/80 dark:border-zinc-800">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-sm font-bold text-zinc-600 dark:text-zinc-300 shrink-0 shadow-2xs">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <h4
                                                    class="font-black text-zinc-900 dark:text-white text-xs md:text-sm tracking-tight truncate">
                                                    {{ $wali->nama_kepala_keluarga }}
                                                </h4>
                                                @if ($wali->is_ustadz)
                                                    <span
                                                        class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                        Ustadz
                                                    </span>
                                                @endif
                                            </div>
                                            <div
                                                class="flex items-center gap-2 mt-0.5 text-[10px] text-zinc-400 font-medium">
                                                <span class="font-mono font-bold text-primary dark:text-primary-dark">
                                                    #{{ $wali->no_registrasi }}
                                                </span>
                                                <span>•</span>
                                                <span class="flex items-center gap-1">
                                                    <i class="bi bi-geo-alt text-[9px]"></i>
                                                    {{ $wali->kampung->nama_kampung ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tanggungan Santri Aktif -->
                                <td class="py-3.5 px-4 align-middle border-y border-zinc-200/80 dark:border-zinc-800">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach ($wali->murids as $anak)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/70 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-700 dark:text-zinc-300 shadow-2xs">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full {{ $anak->jenis_kelamin === 'L' ? 'bg-blue-500' : 'bg-pink-500' }}"></span>
                                                <span class="truncate max-w-[120px]">{{ $anak->nama_lengkap }}</span>
                                                <span
                                                    class="text-[9px] text-zinc-400 font-semibold">({{ $anak->ruangans->first()?->nama_ruangan ?? '-' }})</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Nominal & Status -->
                                <td
                                    class="py-3.5 px-4 text-right align-middle border-y border-zinc-200/80 dark:border-zinc-800">
                                    @if ($isBelumTerbit)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black uppercase tracking-wider">
                                            <i class="bi bi-clock text-[10px]"></i> Belum Diterbitkan
                                        </span>
                                    @elseif ($isLunas)
                                        <div>
                                            <p
                                                class="font-black text-sm text-emerald-600 dark:text-emerald-400 leading-none">
                                                Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                                            </p>
                                            <div class="flex items-center justify-end gap-1.5 mt-1">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[9px] font-black uppercase tracking-wider">
                                                    <i class="bi bi-check-circle-fill text-[8px]"></i> Lunas
                                                </span>
                                            </div>
                                            <p class="text-[9px] text-zinc-400 font-mono mt-0.5" title="No Kwitansi">
                                                {{ $tagihan->pembayaranTagihan->no_transaksi ?? '-' }}
                                            </p>
                                        </div>
                                    @else
                                        <div>
                                            <p class="font-black text-sm text-zinc-900 dark:text-white leading-none">
                                                Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                                            </p>
                                            <div class="flex items-center justify-end gap-1.5 mt-1">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[9px] font-black uppercase tracking-wider">
                                                    <i class="bi bi-exclamation-circle-fill text-[8px]"></i> Belum
                                                    Lunas
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td
                                    class="py-3.5 px-4 text-center align-middle rounded-r-2xl border-y border-r border-zinc-200/80 dark:border-zinc-800">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if ($isBelumTerbit && $selectedMaster)
                                            <!-- Tombol Terbitkan Single KK -->
                                            <form action="{{ route('tagihan-wali.terbitkan') }}" method="POST"
                                                class="m-0">
                                                @csrf
                                                <input type="hidden" name="tahun_pelajaran_id"
                                                    value="{{ $tahunPelajaranId }}">
                                                <input type="hidden" name="pengaturan_tagihan_id"
                                                    value="{{ $selectedMaster->id }}">
                                                <input type="hidden" name="wali_ids[]" value="{{ $wali->id }}">
                                                <button type="submit"
                                                    class="h-8 px-2.5 rounded-xl bg-primary/10 hover:bg-primary text-primary hover:text-white dark:text-primary-dark dark:hover:text-white border border-primary/20 transition-all font-black text-xs flex items-center gap-1 shadow-2xs active:scale-95"
                                                    title="Terbitkan Tagihan KK Ini">
                                                    <i class="bi bi-lightning-charge-fill text-xs"></i>
                                                    <span>Terbitkan</span>
                                                </button>
                                            </form>
                                        @elseif ($tagihan && !$isLunas)
                                            <!-- Tombol Bayar Cepat -->
                                            <button type="button"
                                                onclick="bukaModalBayarSingle({{ $tagihan->id }}, '{{ addslashes($wali->nama_kepala_keluarga) }}', {{ $tagihan->nominal_tagihan }}, '{{ addslashes($tagihan->nama_tagihan_spesifik) }}', {{ json_encode($wali->available_tabungans ?? []) }})"
                                                class="h-8 px-2.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500 text-emerald-600 hover:text-white dark:text-emerald-400 border border-emerald-500/20 transition-all font-black text-xs flex items-center gap-1 shadow-2xs active:scale-95"
                                                title="Bayar Tagihan">
                                                <i class="bi bi-wallet2 text-xs"></i> <span>Bayar</span>
                                            </button>

                                            <!-- Tombol Hapus Tagihan Single -->
                                            <form action="{{ route('tagihan-wali.destroy', $tagihan->id) }}"
                                                method="POST" class="m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan \'{{ addslashes($tagihan->nama_tagihan_spesifik) }}\' dari {{ addslashes($wali->nama_kepala_keluarga) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white dark:text-rose-400 border border-rose-500/20 flex items-center justify-center transition-all shadow-2xs active:scale-95"
                                                    title="Hapus Tagihan Ini">
                                                    <i class="bi bi-trash3 text-xs"></i>
                                                </button>
                                            </form>
                                        @elseif ($isLunas)
                                            <!-- Tombol Cetak Kwitansi -->
                                            <a href="{{ route('tagihan-wali.cetak-kwitansi', $tagihan->id) }}"
                                                target="_blank"
                                                class="w-8 h-8 rounded-xl bg-sky-500/10 hover:bg-sky-500 text-sky-600 hover:text-white dark:text-sky-400 border border-sky-500/20 flex items-center justify-center transition-all shadow-2xs active:scale-95"
                                                title="Cetak Kwitansi">
                                                <i class="bi bi-printer-fill text-xs"></i>
                                            </a>

                                            <!-- Tombol Batal Transaksi -->
                                            <form action="{{ route('tagihan-wali.batal', $tagihan->id) }}"
                                                method="POST" class="m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi pembayaran ini?')">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white dark:text-rose-400 border border-rose-500/20 flex items-center justify-center transition-all shadow-2xs active:scale-95"
                                                    title="Batal / Refund Transaksi">
                                                    <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Tombol Detail Modal -->
                                        <button type="button" onclick="bukaModalDetailWali({{ $wali->id }})"
                                            class="w-8 h-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center transition-all shadow-2xs active:scale-95"
                                            title="Detail KK">
                                            <i class="bi bi-info-circle text-xs font-bold"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state icon="bi-houses" title="Belum Ada Data Tagihan Wali Murid"
                message="Tidak ditemukan data Wali Murid Aktif untuk kriteria filter atau tahun ajaran yang dipilih." />
        @endif
    </div>

    <!-- MODAL PEMBAYARAN KASIR -->
    <div id="modalBayarWali"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="m3-glass-card w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl bg-white dark:bg-[#0c0c0e] border border-zinc-200/80 dark:border-zinc-800 animate-in fade-in zoom-in-95 duration-200">
            <form action="{{ route('tagihan-wali.bayar') }}" method="POST" id="formBayarWali">
                @csrf
                <div id="containerHiddenTagihanIds"></div>

                <div
                    class="px-6 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <h3 class="font-black text-sm text-zinc-900 dark:text-white">Proses Pembayaran Tagihan KK</h3>
                    </div>
                    <button type="button" onclick="tutupModalBayar()"
                        class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-400 hover:text-rose-500">
                        <i class="bi bi-x-lg text-xs font-black"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div
                        class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800 space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Rincian Pembayaran</p>
                        <h4 class="text-sm font-black text-zinc-900 dark:text-white" id="bayarWaliNama"></h4>
                        <p class="text-xs font-semibold text-zinc-500" id="bayarTagihanNama"></p>
                        <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-1"
                            id="bayarNominalDisplay"></p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Metode Pembayaran
                        </label>
                        <div class="relative">
                            <select name="metode_pembayaran" id="selectMetodeBayar"
                                onchange="onMetodeBayarChange(this.value)"
                                class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                                <option value="Tunai">Tunai / Cash</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Tabungan Murid">Potong Tabungan Murid (Auto Debet)</option>
                                <option value="Cicilan">Cicilan / Angsuran</option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                <i class="bi bi-chevron-down text-[10px] font-black"></i>
                            </div>
                        </div>
                        <p id="metodeNotice" class="text-[10px] text-zinc-400 mt-1 ml-1 font-semibold"></p>
                    </div>

                    <!-- SECTION PILIH REKENING TABUNGAN SANTRI -->
                    <div id="sectionPilihTabungan"
                        class="hidden p-3.5 rounded-2xl bg-primary/5 dark:bg-primary/10 border border-primary/20 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label
                                class="block text-[10px] font-black text-primary dark:text-primary-dark uppercase tracking-wider">
                                <i class="bi bi-credit-card-2-front-fill mr-1"></i> Rekening Tabungan Santri
                            </label>
                            <span id="tabunganCountBadge"
                                class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary"></span>
                        </div>

                        <!-- Single Mode: Select Account -->
                        <div id="containerSelectTabungan" class="space-y-2">
                            <div class="relative">
                                <select name="tabungan_id" id="selectTabunganId" onchange="onTabunganAccountChange()"
                                    class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none bg-white dark:bg-zinc-800">
                                    <!-- Options dynamically loaded -->
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                                </div>
                            </div>

                            <!-- Saldo Realtime & Perhitungan Sisa -->
                            <div id="tabunganSaldoInfo"
                                class="text-[11px] p-2.5 rounded-xl bg-white/80 dark:bg-black/40 border border-zinc-200/80 dark:border-zinc-800 space-y-1">
                                <div class="flex justify-between items-center text-[10px]">
                                    <span class="text-zinc-500">Saldo Rekening:</span>
                                    <span id="displaySaldoTabungan"
                                        class="font-bold text-zinc-800 dark:text-zinc-200">-</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px]">
                                    <span class="text-zinc-500">Nominal Tagihan:</span>
                                    <span id="displayNominalDebet" class="font-bold text-rose-500">-</span>
                                </div>
                                <div
                                    class="flex justify-between items-center border-t border-zinc-200/60 dark:border-zinc-700/60 pt-1 text-[10px]">
                                    <span class="text-zinc-500">Estimasi Sisa Saldo:</span>
                                    <span id="displaySisaSaldoTabungan"
                                        class="font-black text-emerald-600 dark:text-emerald-400">-</span>
                                </div>
                            </div>

                            <div id="tabunganWarningNotice"
                                class="hidden p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-[10px] font-bold flex items-center gap-1.5">
                                <i class="bi bi-exclamation-triangle-fill shrink-0"></i>
                                <span>Saldo rekening tidak mencukupi untuk melunasi tagihan ini.</span>
                            </div>
                        </div>

                        <!-- Massal Mode Notice -->
                        <div id="containerMassalTabunganNotice"
                            class="hidden text-[10px] text-zinc-600 dark:text-zinc-300 leading-relaxed">
                            <p class="flex items-start gap-1.5">
                                <i class="bi bi-info-circle-fill text-primary mt-0.5 shrink-0"></i>
                                <span>Sistem akan otomatis mendebet dari nomor rekening tabungan santri aktif dari
                                    masing-masing KK. Nomor rekening yang dipotong akan dicatat pada kwitansi.</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Tanggal Pembayaran
                        </label>
                        <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}"
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Catatan Tambahan (Opsional)
                        </label>
                        <input type="text" name="catatan" placeholder="Ketik keterangan atau catatan..."
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>
                </div>

                <div
                    class="px-6 py-4 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200/80 dark:border-zinc-800 flex justify-end gap-2.5">
                    <button type="button" onclick="tutupModalBayar()"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs">
                        Batal
                    </button>
                    <button type="submit"
                        class="m3-btn-primary px-5 py-2 rounded-xl text-xs font-black shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle text-xs"></i> <span>Konfirmasi Bayar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETAIL CONTAINER (AJAX) -->
    <div id="modalDetailWali"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="m3-glass-card w-full max-w-xl rounded-3xl overflow-hidden shadow-2xl bg-white dark:bg-[#0c0c0e] border border-zinc-200/80 dark:border-zinc-800 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col"
            id="modalDetailContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    @push('scripts')
        <script>
            function toggleCheckAllCheckbox(source) {
                const checkboxes = document.querySelectorAll('.wali-checkbox');
                checkboxes.forEach(cb => cb.checked = source.checked);
                updateSelectedState();
            }

            function toggleCentangSemuaWali() {
                const checkAll = document.getElementById('checkAllWali');
                if (checkAll) {
                    checkAll.checked = !checkAll.checked;
                    toggleCheckAllCheckbox(checkAll);
                }
            }

            function updateSelectedState() {
                const all = document.querySelectorAll('.wali-checkbox');
                const checked = document.querySelectorAll('.wali-checkbox:checked');
                const checkAll = document.getElementById('checkAllWali');

                if (checkAll) {
                    checkAll.checked = all.length > 0 && checked.length === all.length;
                    checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
                }

                const badge = document.getElementById('selectedCountBadge');
                const badgeText = document.getElementById('selectedCountText');
                if (badge && badgeText) {
                    if (checked.length > 0) {
                        badgeText.textContent = `${checked.length} Terpilih`;
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                }
            }

            function submitTerbitkanTercentang() {
                const checkedBoxes = Array.from(document.querySelectorAll('.wali-checkbox:checked'));
                if (checkedBoxes.length === 0) {
                    alert('Silakan centang minimal 1 Wali Murid untuk menerbitkan tagihan.');
                    return;
                }

                const belumTerbitBoxes = checkedBoxes.filter(cb => cb.getAttribute('data-status') === 'belum_terbit');
                if (belumTerbitBoxes.length === 0) {
                    alert('Semua Wali Murid yang Anda centang sudah memiliki tagihan yang diterbitkan.');
                    return;
                }

                if (!confirm(`Terbitkan tagihan untuk ${belumTerbitBoxes.length} Wali Murid yang dipilih?`)) {
                    return;
                }

                const form = document.getElementById('formTerbitkanTercentang');
                const container = document.getElementById('containerWaliIdsTercentang');
                if (!form || !container) return;

                container.innerHTML = '';
                belumTerbitBoxes.forEach(cb => {
                    container.innerHTML +=
                        `<input type="hidden" name="wali_ids[]" value="${cb.getAttribute('data-wali-id')}">`;
                });

                form.submit();
            }

            function submitHapusTagihanTercentang() {
                const checkedBoxes = Array.from(document.querySelectorAll('.wali-checkbox:checked'));
                if (checkedBoxes.length === 0) {
                    alert('Silakan centang minimal 1 baris tagihan yang ingin dihapus.');
                    return;
                }

                const unpaidBoxes = checkedBoxes.filter(cb => cb.getAttribute('data-status') === 'belum_lunas' && cb
                    .getAttribute('data-tagihan-id'));
                if (unpaidBoxes.length === 0) {
                    alert(
                        'Tidak ada tagihan "Belum Lunas" dari baris yang Anda centang yang dapat dihapus.\n\nCatatan: Tagihan yang sudah lunas tidak dapat dihapus (harus dibatalkan pembayarannya terlebih dahulu).'
                    );
                    return;
                }

                if (!confirm(`Apakah Anda yakin ingin MENGHAPUS ${unpaidBoxes.length} tagihan Belum Lunas yang dicentang?`)) {
                    return;
                }

                const form = document.getElementById('formHapusTercentang');
                const container = document.getElementById('containerHapusTagihanIds');
                if (!form || !container) return;

                container.innerHTML = '';
                unpaidBoxes.forEach(cb => {
                    container.innerHTML +=
                        `<input type="hidden" name="tagihan_ids[]" value="${cb.getAttribute('data-tagihan-id')}">`;
                });

                form.submit();
            }

            let currentTabungans = [];
            let currentTagihanNominal = 0;
            let isSingleBayar = true;

            function bukaModalBayarSingle(tagihanId, namaWali, nominal, namaTagihan, tabungans) {
                isSingleBayar = true;
                currentTabungans = tabungans || [];
                currentTagihanNominal = parseFloat(nominal) || 0;

                const container = document.getElementById('containerHiddenTagihanIds');
                container.innerHTML = `<input type="hidden" name="tagihan_ids[]" value="${tagihanId}">`;

                document.getElementById('bayarWaliNama').textContent = `Wali Murid: ${namaWali}`;
                document.getElementById('bayarTagihanNama').textContent = namaTagihan;
                document.getElementById('bayarNominalDisplay').textContent = `Rp ${Number(nominal).toLocaleString('id-ID')}`;

                const selectMetode = document.getElementById('selectMetodeBayar');
                if (selectMetode) selectMetode.value = 'Tunai';

                setupTabunganDropdown();
                onMetodeBayarChange('Tunai');

                document.getElementById('modalBayarWali').classList.remove('hidden');
            }

            function bukaModalBayarTerpilih() {
                isSingleBayar = false;
                currentTabungans = [];

                const checkedBoxes = Array.from(document.querySelectorAll('.wali-checkbox:checked'));
                if (checkedBoxes.length === 0) {
                    alert('Silakan centang minimal 1 baris Wali Murid.');
                    return;
                }

                const unpaidBoxes = checkedBoxes.filter(cb => cb.getAttribute('data-status') === 'belum_lunas' && cb
                    .getAttribute('data-tagihan-id'));

                if (unpaidBoxes.length === 0) {
                    alert(
                        'Tidak ada tagihan "Belum Lunas" dari baris yang Anda centang.\n\nCatatan: Jika statusnya "Belum Diterbitkan", silakan klik tombol "Terbitkan Tercentang" terlebih dahulu.'
                    );
                    return;
                }

                const container = document.getElementById('containerHiddenTagihanIds');
                container.innerHTML = '';

                let totalNominal = 0;
                unpaidBoxes.forEach(cb => {
                    const tagihanId = cb.getAttribute('data-tagihan-id');
                    const nominal = parseFloat(cb.getAttribute('data-nominal')) || 0;
                    totalNominal += nominal;
                    container.innerHTML += `<input type="hidden" name="tagihan_ids[]" value="${tagihanId}">`;
                });

                currentTagihanNominal = totalNominal;

                document.getElementById('bayarWaliNama').textContent = `Pelunasan Massal (${unpaidBoxes.length} Tagihan KK)`;
                document.getElementById('bayarTagihanNama').textContent =
                    `Total Nominal: Rp ${Number(totalNominal).toLocaleString('id-ID')}`;
                document.getElementById('bayarNominalDisplay').textContent = `${unpaidBoxes.length} KK Diproses`;

                const selectMetode = document.getElementById('selectMetodeBayar');
                if (selectMetode) selectMetode.value = 'Tunai';

                setupTabunganDropdown();
                onMetodeBayarChange('Tunai');

                document.getElementById('modalBayarWali').classList.remove('hidden');
            }

            function setupTabunganDropdown() {
                const select = document.getElementById('selectTabunganId');
                const badge = document.getElementById('tabunganCountBadge');
                if (!select) return;

                select.innerHTML = '';
                if (isSingleBayar) {
                    document.getElementById('containerSelectTabungan')?.classList.remove('hidden');
                    document.getElementById('containerMassalTabunganNotice')?.classList.add('hidden');

                    if (currentTabungans.length === 0) {
                        select.innerHTML = '<option value="">-- Tidak ada rekening tabungan aktif --</option>';
                        if (badge) badge.textContent = '0 Rekening';
                    } else {
                        if (badge) badge.textContent = `${currentTabungans.length} Rekening Santri`;
                        currentTabungans.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.setAttribute('data-saldo', t.saldo);
                            opt.setAttribute('data-rek', t.nomor_rekening);
                            opt.setAttribute('data-santri', t.nama_santri);
                            opt.textContent =
                                `No. Rek: ${t.nomor_rekening} - ${t.nama_santri} (Saldo: ${t.saldo_format})`;
                            select.appendChild(opt);
                        });
                    }
                    onTabunganAccountChange();
                } else {
                    document.getElementById('containerSelectTabungan')?.classList.add('hidden');
                    document.getElementById('containerMassalTabunganNotice')?.classList.remove('hidden');
                    if (badge) badge.textContent = 'Auto-Debet Massal';
                }
            }

            function onTabunganAccountChange() {
                if (!isSingleBayar) return;

                const select = document.getElementById('selectTabunganId');
                const selectedOption = select?.options[select.selectedIndex];
                const warn = document.getElementById('tabunganWarningNotice');
                const displaySaldo = document.getElementById('displaySaldoTabungan');
                const displayNominal = document.getElementById('displayNominalDebet');
                const displaySisa = document.getElementById('displaySisaSaldoTabungan');

                if (!selectedOption || !selectedOption.value) {
                    if (displaySaldo) displaySaldo.textContent = 'Rp 0';
                    if (displayNominal) displayNominal.textContent =
                        `Rp ${Number(currentTagihanNominal).toLocaleString('id-ID')}`;
                    if (displaySisa) displaySisa.textContent = '-';
                    if (warn) warn.classList.remove('hidden');
                    return;
                }

                const saldo = parseFloat(selectedOption.getAttribute('data-saldo')) || 0;
                const sisa = saldo - currentTagihanNominal;

                if (displaySaldo) displaySaldo.textContent = `Rp ${Number(saldo).toLocaleString('id-ID')}`;
                if (displayNominal) displayNominal.textContent =
                    `- Rp ${Number(currentTagihanNominal).toLocaleString('id-ID')}`;
                if (displaySisa) {
                    displaySisa.textContent = `Rp ${Number(sisa).toLocaleString('id-ID')}`;
                    if (sisa < 0) {
                        displaySisa.className = 'font-black text-rose-500';
                    } else {
                        displaySisa.className = 'font-black text-emerald-600 dark:text-emerald-400';
                    }
                }

                if (sisa < 0) {
                    if (warn) warn.classList.remove('hidden');
                } else {
                    if (warn) warn.classList.add('hidden');
                }
            }

            function onMetodeBayarChange(val) {
                const notice = document.getElementById('metodeNotice');
                const sectionTabungan = document.getElementById('sectionPilihTabungan');

                if (val === 'Tabungan Murid') {
                    if (sectionTabungan) sectionTabungan.classList.remove('hidden');
                    if (notice) notice.innerHTML = '';
                    if (isSingleBayar) {
                        onTabunganAccountChange();
                    }
                } else {
                    if (sectionTabungan) sectionTabungan.classList.add('hidden');
                    if (notice) {
                        if (val === 'Cicilan') {
                            notice.innerHTML =
                                '<i class="bi bi-info-circle text-amber-500"></i> <span class="text-amber-500 font-bold">Pembayaran dicatat dengan metode Cicilan / Angsuran.</span>';
                        } else {
                            notice.innerHTML = '';
                        }
                    }
                }
            }

            function tutupModalBayar() {
                document.getElementById('modalBayarWali').classList.add('hidden');
                onMetodeBayarChange('Tunai');
            }

            function bukaModalDetailWali(waliId) {
                const modal = document.getElementById('modalDetailWali');
                const content = document.getElementById('modalDetailContent');

                content.innerHTML = `
                    <div class="p-8 text-center text-zinc-400">
                        <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p class="text-xs font-bold">Memuat data detail...</p>
                    </div>
                `;
                modal.classList.remove('hidden');

                fetch(`/tagihan-wali/detail/${waliId}?tahun_id={{ $tahunPelajaranId }}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        content.innerHTML = html;
                    })
                    .catch(err => {
                        content.innerHTML = `
                            <div class="p-6 text-center text-rose-500">
                                <p class="text-xs font-bold">Gagal memuat detail: ${err.message}</p>
                                <button type="button" onclick="tutupModalDetail()" class="mt-3 px-4 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-xs font-bold">Tutup</button>
                            </div>
                        `;
                    });
            }

            function tutupModalDetail() {
                document.getElementById('modalDetailWali').classList.add('hidden');
            }
        </script>
    @endpush
</x-app-layout>
