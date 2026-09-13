@section('title', 'Penerbitan Tagihan Per Wali Murid')

<x-app-layout>
    <div class="space-y-6">
        <!-- HEADER PAGE -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="px-2.5 py-1 rounded-xl text-[10px] font-black tracking-wider uppercase bg-primary/10 text-primary dark:text-primary-dark border border-primary/20">
                        ADMINISTRASI KEUANGAN
                    </span>
                    <span class="text-xs font-semibold text-zinc-400">
                        {{ $masterTagihans->firstWhere('id', $selectedMasterId)?->nama_tagihan ?? 'Tagihan KK' }}
                    </span>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Penerbitan Tagihan Per Wali Murid (KK Aktif)
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Kelola dan terbitkan tagihan keluarga secara massal atau per Kepala Keluarga (KK) yang memiliki
                    santri aktif.
                </p>
            </div>

            <!-- Navigasi Cepat Antar Submenu -->
            <div class="flex items-center gap-2">
                <a href="{{ route('tagihan-wali.kasir') }}"
                    class="px-3 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs font-black flex items-center gap-1.5 transition-colors">
                    <i class="bi bi-wallet2 text-emerald-500"></i>
                    <span>Kasir Pembayaran</span>
                </a>
                <a href="{{ route('tagihan-wali.laporan') }}"
                    class="px-3 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs font-black flex items-center gap-1.5 transition-colors">
                    <i class="bi bi-file-earmark-bar-graph-fill text-sky-500"></i>
                    <span>Laporan & Rekap</span>
                </a>
            </div>
        </div>
        <!-- ALERT FEEDBACK -->
        @if (session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
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
                class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:opacity-70">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        @endif

        <!-- FILTER BAR -->
        <div
            class="m3-glass-card p-4 rounded-3xl space-y-3 bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800">
            <form method="GET" action="{{ route('tagihan-wali.terbitkan-index') }}" id="filterForm"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Tahun Pelajaran -->
                <div class="lg:col-span-3">
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
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Jenis Tagihan Sasaran Wali Murid -->
                <div class="lg:col-span-3">
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
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Zonasi Dusun / Kampung -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Zonasi Dusun / Kampung
                    </label>
                    <div class="relative">
                        <select name="kampung_id" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            @foreach ($daftarKampung as $kp)
                                <option value="{{ $kp->id }}"
                                    {{ (string) ($selectedKampungId ?? request('kampung_id')) === (string) $kp->id ? 'selected' : '' }}>
                                    {{ $kp->nama_kampung }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Status Penerbitan -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Status Terbit
                    </label>
                    <div class="relative">
                        <select name="status_terbit" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Status</option>
                            <option value="Belum Terbit"
                                {{ request('status_terbit') === 'Belum Terbit' ? 'selected' : '' }}>
                                ⏳ Belum Terbit
                            </option>
                            <option value="Sudah Terbit"
                                {{ request('status_terbit') === 'Sudah Terbit' ? 'selected' : '' }}>
                                ⚡ Sudah Terbit
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Pencarian Teks -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Cari Kepala KK / Santri
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Ketik nama / no reg..." class="m3-input-glass w-full !pl-8 text-xs font-bold">
                        <div
                            class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-search text-xs"></i>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- STATISTIK RINGKAS PENERBITAN -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            <!-- Total KK Aktif -->
            <div
                class="m3-glass-card p-4 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black tracking-wider uppercase text-zinc-400">Total KK Aktif</span>
                    <div
                        class="w-7 h-7 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">
                        <i class="bi bi-houses-fill"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1.5">
                    {{ number_format($totalWaliAktif, 0, ',', '.') }} <span
                        class="text-xs font-bold text-zinc-400">KK</span>
                </h3>
                <p class="text-[10px] text-zinc-400 mt-0.5">Memiliki anak santri aktif</p>
            </div>

            <!-- Belum Diterbitkan -->
            <div class="m3-glass-card p-4 rounded-3xl border border-amber-500/20 bg-amber-500/5 dark:bg-amber-500/10">
                <div class="flex items-center justify-between">
                    <span
                        class="text-[10px] font-black tracking-wider uppercase text-amber-600 dark:text-amber-400">Belum
                        Terbit</span>
                    <div
                        class="w-7 h-7 rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center text-xs font-bold">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight mt-1.5">
                    {{ number_format($totalBelumTerbitCount, 0, ',', '.') }} <span class="text-xs font-bold">KK</span>
                </h3>
                <p class="text-[10px] text-amber-600/80 dark:text-amber-400/80 mt-0.5">
                    Est: Rp {{ number_format($totalBelumTerbitCount * $nominalTarif, 0, ',', '.') }}
                </p>
            </div>

            <!-- Sudah Diterbitkan -->
            <div
                class="m3-glass-card p-4 rounded-3xl border border-emerald-500/20 bg-emerald-500/5 dark:bg-emerald-500/10">
                <div class="flex items-center justify-between">
                    <span
                        class="text-[10px] font-black tracking-wider uppercase text-emerald-600 dark:text-emerald-400">Sudah
                        Terbit</span>
                    <div
                        class="w-7 h-7 rounded-xl bg-emerald-500/15 text-emerald-600 flex items-center justify-center text-xs font-bold">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                </div>
                <h3
                    class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-1.5">
                    {{ number_format($totalSudahTerbitCount, 0, ',', '.') }} <span class="text-xs font-bold">KK</span>
                </h3>
                <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">
                    Rp {{ number_format($totalSudahTerbitCount * $nominalTarif, 0, ',', '.') }}
                </p>
            </div>

            <!-- Nominal Tarif Master -->
            <div
                class="m3-glass-card p-4 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black tracking-wider uppercase text-zinc-400">Tarif Tagihan /
                        KK</span>
                    <div
                        class="w-7 h-7 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 flex items-center justify-center text-xs font-bold">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-primary dark:text-primary-dark tracking-tight mt-1.5">
                    Rp {{ number_format($nominalTarif, 0, ',', '.') }}
                </h3>
                <p class="text-[10px] text-zinc-400 mt-0.5">
                    {{ $selectedMaster?->nama_tagihan ?? 'Pilih Tarif' }}
                </p>
            </div>
        </div>

        <!-- ACTION BAR & TOOLBAR MASSAL -->
        <div
            class="m3-glass-card p-4 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Tombol Check All Helper -->
                <button type="button" onclick="toggleCentangSemuaWali()"
                    class="h-9 px-3 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-2xs">
                    <i class="bi bi-check2-square text-sm"></i>
                    <span>Pilih Semua / Batal</span>
                </button>

                <!-- Badge Terpilih -->
                <span id="selectedCountBadge"
                    class="hidden items-center gap-1.5 px-3 py-1 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 text-xs font-black">
                    <i class="bi bi-check2-circle"></i>
                    <span id="selectedCountText">0 Terpilih</span>
                </span>
            </div>

            <!-- Grup Tombol Aksi Massal -->
            <div class="flex items-center gap-2 flex-wrap justify-end">
                @if ($selectedMaster)
                    <!-- Tombol Terbitkan ke Semua KK (Belum Terbit) -->
                    <form action="{{ route('tagihan-wali.terbitkan') }}" method="POST" class="m-0"
                        onsubmit="return confirm('Apakah Anda yakin ingin MENERBITKAN tagihan \'{{ addslashes($selectedMaster->nama_tagihan) }}\' ke SELURUH {{ $totalBelumTerbitCount }} Wali Murid yang belum memiliki tagihan?')">
                        @csrf
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
                        <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">
                        <input type="hidden" name="kampung_id" value="{{ $selectedKampungId }}">
                        <button type="submit"
                            class="m3-btn-primary h-9 px-3.5 rounded-2xl text-xs font-black shadow-2xs flex items-center gap-1.5 active:scale-95 transition-all">
                            <i class="bi bi-lightning-charge-fill text-xs"></i>
                            <span>Terbitkan Semua ({{ $totalBelumTerbitCount }})</span>
                        </button>
                    </form>

                    <!-- Tombol Terbitkan Tercentang -->
                    <button type="button" onclick="submitTerbitkanTercentang()"
                        class="h-9 px-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-2xs flex items-center gap-1.5 transition-all active:scale-95">
                        <i class="bi bi-check2-all text-sm"></i>
                        <span>Terbitkan Tercentang</span>
                    </button>

                    <!-- Tombol Hapus Tercentang -->
                    <button type="button" onclick="submitHapusTagihanTercentang()"
                        class="h-9 px-3 rounded-2xl bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white dark:text-rose-400 border border-rose-500/20 font-black text-xs shadow-2xs flex items-center gap-1.5 transition-all active:scale-95">
                        <i class="bi bi-trash3-fill text-xs"></i>
                        <span>Hapus Tercentang</span>
                    </button>

                    <!-- Tombol Hapus Semua Tagihan Belum Lunas -->
                    <form action="{{ route('tagihan-wali.hapus-semua') }}" method="POST" class="m-0"
                        onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA tagihan \'{{ addslashes($selectedMaster->nama_tagihan) }}\' yang BELUM LUNAS pada tahun pelajaran ini?\n\nTagihan yang sudah Lunas TIDAK akan terhapus.')">
                        @csrf
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
                        <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">
                        <input type="hidden" name="kampung_id" value="{{ $selectedKampungId }}">
                        <button type="submit"
                            class="h-9 px-3 rounded-2xl bg-zinc-100 hover:bg-rose-600 hover:text-white text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-rose-600 dark:hover:text-white font-bold text-xs flex items-center gap-1 transition-colors"
                            title="Hapus Semua Tagihan Belum Lunas">
                            <i class="bi bi-trash text-xs"></i>
                            <span>Hapus Semua Belum Lunas</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- FORM HIDDEN UNTUK SUBMIT TERCENTANG -->
        <form action="{{ route('tagihan-wali.terbitkan') }}" method="POST" id="formTerbitkanTercentang"
            class="hidden">
            @csrf
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
            <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMasterId }}">
            <div id="containerWaliIdsTercentang"></div>
        </form>

        <form action="{{ route('tagihan-wali.hapus-massal') }}" method="POST" id="formHapusTercentang"
            class="hidden">
            @csrf
            <div id="containerHapusTagihanIds"></div>
        </form>

        <!-- TABEL DATA WALI MURID AKTIF -->
        @if ($walis->isNotEmpty())
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-separate border-spacing-y-2.5">
                    <thead>
                        <tr
                            class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500 px-4">
                            <th class="py-2 px-3.5 text-center w-10">
                                <input type="checkbox" id="checkAllWali" onchange="toggleCheckAllCheckbox(this)"
                                    class="w-4 h-4 rounded-lg border-zinc-300 dark:border-zinc-600 text-primary focus:ring-primary/30 bg-white/50 dark:bg-black/50 cursor-pointer">
                            </th>
                            <th class="py-2 px-4">Kepala Keluarga & Zonasi</th>
                            <th class="py-2 px-4">Tanggungan Santri Aktif</th>
                            <th class="py-2 px-4 text-right">Tarif Tagihan</th>
                            <th class="py-2 px-4 text-center">Status Penerbitan</th>
                            <th class="py-2 px-4 text-center w-28">Aksi</th>
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
                                <!-- Checkbox -->
                                <td
                                    class="py-3.5 px-3.5 text-center align-middle rounded-l-2xl border-y border-l border-zinc-200/80 dark:border-zinc-800">
                                    <input type="checkbox" value="{{ $wali->id }}"
                                        data-wali-id="{{ $wali->id }}"
                                        data-tagihan-id="{{ $tagihan?->id ?? '' }}"
                                        data-status="{{ $isBelumTerbit ? 'belum_terbit' : ($isLunas ? 'lunas' : 'belum_lunas') }}"
                                        data-nama="{{ $wali->nama_kepala_keluarga }}"
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

                                <!-- Tarif Tagihan -->
                                <td
                                    class="py-3.5 px-4 text-right align-middle border-y border-zinc-200/80 dark:border-zinc-800">
                                    <p class="font-black text-sm text-zinc-900 dark:text-white leading-none">
                                        Rp
                                        {{ number_format($tagihan?->nominal_tagihan ?? $nominalTarif, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[9px] text-zinc-400 mt-1">Per KK Aktif</p>
                                </td>

                                <!-- Status Penerbitan -->
                                <td
                                    class="py-3.5 px-4 text-center align-middle border-y border-zinc-200/80 dark:border-zinc-800">
                                    @if ($isBelumTerbit)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">
                                            <i class="bi bi-clock-history text-[10px]"></i> Belum Terbit
                                        </span>
                                    @elseif ($isLunas)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider">
                                            <i class="bi bi-check-circle-fill text-[10px]"></i> Terbit & Lunas
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 text-[10px] font-black uppercase tracking-wider">
                                            <i class="bi bi-lightning-charge-fill text-[10px]"></i> Terbit (Belum
                                            Lunas)
                                        </span>
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
                                            <!-- Tombol Hapus Tagihan Single -->
                                            <form action="{{ route('tagihan-wali.destroy', $tagihan->id) }}"
                                                method="POST" class="m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan \'{{ addslashes($tagihan->nama_tagihan_spesifik) }}\' dari {{ addslashes($wali->nama_kepala_keluarga) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="h-8 px-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white dark:text-rose-400 border border-rose-500/20 font-black text-xs flex items-center gap-1 transition-all shadow-2xs active:scale-95"
                                                    title="Hapus Tagihan Ini">
                                                    <i class="bi bi-trash3 text-xs"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @elseif ($isLunas)
                                            <a href="{{ route('tagihan-wali.cetak-kwitansi', $tagihan->id) }}"
                                                target="_blank"
                                                class="w-8 h-8 rounded-xl bg-sky-500/10 hover:bg-sky-500 text-sky-600 hover:text-white dark:text-sky-400 border border-sky-500/20 flex items-center justify-center transition-all shadow-2xs active:scale-95"
                                                title="Cetak Kwitansi">
                                                <i class="bi bi-printer-fill text-xs"></i>
                                            </a>
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
            <x-empty-state icon="bi-houses" title="Belum Ada Data Wali Murid"
                message="Tidak ditemukan data Wali Murid Aktif untuk kriteria filter atau tahun ajaran yang dipilih." />
        @endif
    </div>

    <!-- MODAL DETAIL CONTAINER (AJAX) -->
    <div id="modalDetailWali"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="m3-glass-card w-full max-w-xl rounded-3xl overflow-hidden shadow-2xl bg-white dark:bg-[#0c0c0e] border border-zinc-200/80 dark:border-zinc-800 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col"
            id="modalDetailContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>

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
                        'Tidak ada tagihan "Belum Lunas" dari baris yang Anda centang yang dapat dihapus.\n\nCatatan: Tagihan yang sudah lunas tidak dapat dihapus.'
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
