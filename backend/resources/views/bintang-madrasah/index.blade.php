@section('title', 'Bintang Madrasah')

<x-app-layout>
    <!-- 1. HEADER & TOOLBAR SECTION -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-10 print:hidden">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 inline-flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-stars text-xs"></i>
                    <span>Akademik & Prestasi</span>
                </span>
                <span class="text-xs text-zinc-400">•</span>
                <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                    Best of The Best
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Bintang Madrasah
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Penghargaan prestasi tertinggi murid teladan madrasah berdasarkan akumulasi komprehensif IMDA 1 & IMDA 2
                sepanjang tahun pelajaran.
            </p>
        </div>

        <!-- Toolbar Filter & Cetak -->
        <div class="w-full xl:w-auto flex flex-col sm:flex-row items-center gap-2.5">
            <form action="{{ route('bintang-madrasah.index') }}" method="GET" id="formFilter"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-[240px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-amber-500 transition-colors">
                        <i class="bi bi-calendar-range text-sm"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        @foreach ($daftarTahun as $t)
                            <option value="{{ $t->id }}" {{ $tahunPelajaranId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_hijriyah }} | {{ $t->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Tombol Cetak -->
                @if ($bintangMadrasah->count() > 0)
                    <div
                        class="w-full sm:w-auto shrink-0 border-t sm:border-t-0 sm:border-l border-zinc-200/80 dark:border-zinc-800 pt-2.5 sm:pt-0 sm:pl-2.5">
                        <button type="button" onclick="window.print()"
                            class="m3-btn-secondary w-full sm:w-auto h-10 px-4 group/btn inline-flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-printer text-sm"></i>
                            <span>Cetak Dokumen</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- 2. AREA KONTEN -->
    @if ($bintangMadrasah->count() > 0)

        <!-- Banner Kriteria Seleksi -->
        <div class="mb-6 animate-[modalFadeIn_0.2s_ease-out]">
            <div
                class="m3-glass-card p-5 border-amber-300/40 dark:border-amber-700/40 bg-linear-to-r from-amber-500/5 to-amber-500/10 flex flex-col lg:flex-row gap-4 items-center justify-between shadow-2xs">
                <div>
                    <h3
                        class="text-base md:text-lg font-black text-amber-700 dark:text-amber-400 tracking-tight uppercase flex items-center gap-2">
                        <i class="bi bi-stars text-lg"></i>
                        <span>Kriteria Seleksi Bintang Madrasah</span>
                    </h3>
                    <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400 mt-1">
                        Dianugerahkan secara bertingkat kepada murid teladan yang memenuhi 4 parameter keunggulan
                        akademik dan akhlak.
                    </p>
                </div>
                <div
                    class="flex flex-wrap items-center gap-2 text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">
                    <span
                        class="bg-white/90 dark:bg-zinc-900/90 px-2.5 py-1.5 rounded-xl border border-amber-300/60 dark:border-amber-700/60 shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle text-amber-600 dark:text-amber-400 text-xs"></i> 1. Juara 1 (IMDA
                        1 & 2)
                    </span>
                    <span
                        class="bg-white/90 dark:bg-zinc-900/90 px-2.5 py-1.5 rounded-xl border border-amber-300/60 dark:border-amber-700/60 shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle text-amber-600 dark:text-amber-400 text-xs"></i> 2. Nilai Rata²
                        Tertinggi
                    </span>
                    <span
                        class="bg-white/90 dark:bg-zinc-900/90 px-2.5 py-1.5 rounded-xl border border-amber-300/60 dark:border-amber-700/60 shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle text-amber-600 dark:text-amber-400 text-xs"></i> 3. Alpa Terendah
                    </span>
                    <span
                        class="bg-white/90 dark:bg-zinc-900/90 px-2.5 py-1.5 rounded-xl border border-amber-300/60 dark:border-amber-700/60 shadow-2xs flex items-center gap-1.5">
                        <i class="bi bi-check2-circle text-amber-600 dark:text-amber-400 text-xs"></i> 4. Poin Terendah
                    </span>
                </div>
            </div>
        </div>

        <!-- PODIUM TOP 3 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 animate-[modalFadeIn_0.3s_ease-out]">
            @foreach ($bintangMadrasah as $index => $bintang)
                @php
                    $colors = [
                        0 => [
                            'bg' => 'bg-amber-50 dark:bg-amber-950/40',
                            'border' => 'border-amber-400 dark:border-amber-600/60',
                            'icon' => 'text-amber-600 dark:text-amber-400',
                            'badge' => 'bg-amber-500 text-white',
                            'label' => 'BINTANG 1 MADRASAH',
                        ],
                        1 => [
                            'bg' => 'bg-slate-50 dark:bg-slate-900/40',
                            'border' => 'border-slate-300 dark:border-slate-600/50',
                            'icon' => 'text-slate-500 dark:text-slate-400',
                            'badge' => 'bg-slate-500 text-white',
                            'label' => 'BINTANG 2 MADRASAH',
                        ],
                        2 => [
                            'bg' => 'bg-orange-50 dark:bg-orange-950/40',
                            'border' => 'border-orange-300 dark:border-orange-600/50',
                            'icon' => 'text-orange-600 dark:text-orange-400',
                            'badge' => 'bg-orange-600 text-white',
                            'label' => 'BINTANG 3 MADRASAH',
                        ],
                    ];
                    $c = $colors[$index] ?? $colors[0];
                @endphp

                <div
                    class="m3-glass-card border-2 {{ $c['border'] }} relative overflow-hidden flex flex-col group transition-all duration-300 {{ $index == 0 ? 'lg:-translate-y-2.5 shadow-xl ring-2 ring-amber-400/20' : '' }}">

                    <!-- Header Kartu -->
                    <div class="p-6 text-center relative z-10 flex flex-col items-center">
                        <div
                            class="w-16 h-16 rounded-3xl {{ $c['bg'] }} border {{ $c['border'] }} {{ $c['icon'] }} flex items-center justify-center text-3xl shadow-2xs mb-3.5 rotate-3 group-hover:rotate-0 transition-transform">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                        <span
                            class="{{ $c['badge'] }} px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-2xs mb-2.5">
                            {{ $c['label'] }}
                        </span>
                        <h4 class="font-black text-lg text-zinc-900 dark:text-white leading-tight mb-1 truncate w-full"
                            title="{{ $bintang->murid->nama_lengkap }}">
                            {{ $bintang->murid->nama_lengkap }}
                        </h4>
                        <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                            {{ $bintang->level_nama }} • {{ $bintang->ruangan_nama }}
                        </p>
                    </div>

                    <!-- Statistik Rincian -->
                    <div class="px-6 pb-6 flex-1 flex flex-col justify-end relative z-10">
                        <div
                            class="bg-zinc-50/90 dark:bg-zinc-950/80 rounded-2xl p-4 border border-zinc-200/80 dark:border-zinc-800 flex flex-col gap-3">

                            <!-- Rata-rata -->
                            <div
                                class="flex justify-between items-center border-b border-zinc-200/80 dark:border-zinc-800 pb-2.5">
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-wider">
                                    <i class="bi bi-calculator mr-1"></i> Rata² Gabungan
                                </div>
                                <div class="font-black text-base {{ $c['icon'] }}">{{ $bintang->rata_rata }}</div>
                            </div>

                            <!-- Alpa -->
                            <div
                                class="flex justify-between items-center border-b border-zinc-200/80 dark:border-zinc-800 pb-2.5">
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-wider">
                                    <i class="bi bi-calendar-x mr-1"></i> Total Alpa
                                </div>
                                <div class="font-black text-xs text-zinc-800 dark:text-zinc-200">
                                    {{ $bintang->jumlah_alpa }} <span
                                        class="text-[9px] font-bold text-zinc-400">Hari</span>
                                </div>
                            </div>

                            <!-- Poin Pelanggaran -->
                            <div class="flex justify-between items-center">
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-wider">
                                    <i class="bi bi-exclamation-triangle mr-1"></i> Poin Pelanggaran
                                </div>
                                <div class="font-black text-xs text-zinc-800 dark:text-zinc-200">
                                    {{ $bintang->poin_pelanggaran }} <span
                                        class="text-[9px] font-bold text-zinc-400">Poin</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- STATE KOSONG -->
        <x-empty-state icon="bi-shield-lock" title="Bintang Madrasah Belum Tersedia"
            message="Kandidat Bintang Madrasah belum dapat diakumulasikan. Pastikan agenda ujian IMDA 1 dan IMDA 2 pada tahun pelajaran ini telah selesai dilaksanakan beserta seluruh nilai murid yang telah diinput." />
    @endif

</x-app-layout>
