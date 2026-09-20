@section('title', 'Loket Cetak Dokumen')

<x-app-layout>
    <div class="max-w-5xl mx-auto pb-12 relative z-10">

        <!-- HEADER LOKET -->
        <div class="text-center mb-6 pt-2">
            <div
                class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 mb-3 shadow-2xs">
                <i class="bi bi-upc-scan text-2xl"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Loket Cetak Cepat
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Arahkan kursor ke kolom di bawah, lalu scan barcode NISM murid.
            </p>
        </div>

        <!-- FORM SCAN BARCODE -->
        <div class="m3-glass-card rounded-3xl p-3 mb-8 mx-auto max-w-2xl shadow-2xs">
            <form action="{{ route('petugas-cetak.index') }}" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-search text-lg"></i>
                </div>
                <input type="text" name="nism" value="{{ request('nism') }}" autofocus autocomplete="off"
                    placeholder="Scan Barcode / Ketik NISM lalu tekan Enter..."
                    class="m3-input-glass w-full !h-12 !pl-12 !pr-4 text-sm md:text-base font-black">
            </form>
        </div>

        <!-- HASIL PENCARIAN -->
        @if (request()->filled('nism'))
            @if ($murid)
                <!-- Identitas murid -->
                <div
                    class="m3-glass-card rounded-3xl p-5 md:p-6 mb-6 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                        <div
                            class="w-14 h-14 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl font-black shrink-0 shadow-2xs">
                            {{ substr($murid->nama_lengkap, 0, 1) }}
                        </div>
                        <div>
                            <h3
                                class="text-lg md:text-xl font-black uppercase tracking-tight text-zinc-900 dark:text-white">
                                {{ $murid->nama_lengkap }}</h3>
                            <div
                                class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-1.5 text-xs">
                                <span
                                    class="px-2.5 py-0.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-mono font-bold">
                                    NISM: {{ $murid->nism }}
                                </span>
                                <span class="text-zinc-400">&bull;</span>
                                <span class="text-zinc-500 font-semibold">
                                    Wali: <b class="text-zinc-700 dark:text-zinc-300">{{ $murid->nama_ayah ?? '-' }}</b>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Cepat Cetak Biodata Rapor -->
                    <div class="shrink-0">
                        <a href="{{ route('petugas-cetak.biodata-rapor', $murid->id) }}" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-purple-600/30 transition-all active:scale-95 cursor-pointer">
                            <i class="bi bi-file-earmark-person-fill text-base"></i>
                            <span>Cetak Biodata Rapor (Cover)</span>
                        </a>
                    </div>
                </div>

                <!-- Daftar Dokumen (Dikelompokkan per Tahun & Ruang) -->
                @if (empty($arsipDikelompokkan))
                    <x-empty-state icon="bi-folder-x" title="Belum Ada Dokumen"
                        message="Murid ini belum memiliki satupun arsip dokumen yang disahkan. Silakan hubungi Administrator Madrasah." />
                @else
                    <div class="space-y-4">
                        @foreach ($arsipDikelompokkan as $tahun => $ruangans)
                            <div class="m3-glass-card rounded-3xl overflow-hidden shadow-2xs">
                                <div
                                    class="bg-zinc-100/60 dark:bg-zinc-800/60 px-5 py-3 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                                    <h4
                                        class="font-black text-xs md:text-sm text-zinc-900 dark:text-white flex items-center gap-2">
                                        <i class="bi bi-calendar-check text-emerald-600 dark:text-emerald-400"></i>
                                        Tahun Pelajaran: {{ $tahun }}
                                    </h4>
                                </div>

                                <div class="p-4 sm:p-5 space-y-3">
                                    @foreach ($ruangans as $ruangan => $dokumens)
                                        <div
                                            class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-3.5 rounded-2xl border border-zinc-200/60 dark:border-zinc-800 bg-white/40 dark:bg-black/40 shadow-2xs">
                                            <div>
                                                <p class="font-black text-xs md:text-sm text-zinc-900 dark:text-white">
                                                    {{ $ruangan }}</p>
                                                <p class="text-[10px] text-zinc-400 font-semibold mt-0.5">Pilih dokumen
                                                    yang ingin dicetak</p>
                                            </div>

                                            <div class="flex flex-wrap gap-2">

                                                <!-- TOMBOL BIODATA / COVER RAPOR -->
                                                <a href="{{ route('petugas-cetak.biodata-rapor', $murid->id) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 h-8 bg-purple-500/10 hover:bg-purple-600 text-purple-600 hover:text-white dark:text-purple-400 dark:hover:text-white border border-purple-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                    <i class="bi bi-file-earmark-person-fill text-xs"></i> <span>Biodata
                                                        Rapor</span>
                                                </a>

                                                <!-- TOMBOL RAPOR SMT 1 -->
                                                @if (isset($dokumens['rapor_smt_1']))
                                                    <a href="{{ route('arsip.cetak', $dokumens['rapor_smt_1']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-sky-500/10 hover:bg-sky-600 text-sky-600 hover:text-white dark:text-sky-400 dark:hover:text-white border border-sky-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-printer-fill text-xs"></i> <span>Rapor
                                                            Ganjil</span>
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-zinc-100/60 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600 border border-zinc-200/40 dark:border-zinc-800 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed opacity-60">
                                                        <span>Rapor Ganjil</span> <i
                                                            class="bi bi-lock-fill text-xs"></i>
                                                    </span>
                                                @endif

                                                <!-- TOMBOL RAPOR SMT 2 -->
                                                @if (isset($dokumens['rapor_smt_2']))
                                                    <a href="{{ route('arsip.cetak', $dokumens['rapor_smt_2']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-indigo-500/10 hover:bg-indigo-600 text-indigo-600 hover:text-white dark:text-indigo-400 dark:hover:text-white border border-indigo-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-printer-fill text-xs"></i> <span>Rapor
                                                            Genap</span>
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-zinc-100/60 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600 border border-zinc-200/40 dark:border-zinc-800 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed opacity-60">
                                                        <span>Rapor Genap</span> <i class="bi bi-lock-fill text-xs"></i>
                                                    </span>
                                                @endif

                                                <!-- TOMBOL SK KENAIKAN/LULUS -->
                                                @if (isset($dokumens['sk_keputusan']))
                                                    <a href="{{ route('arsip.cetak', $dokumens['sk_keputusan']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white dark:text-emerald-400 dark:hover:text-white border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-printer-fill text-xs"></i> <span>SK</span>
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-zinc-100/60 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600 border border-zinc-200/40 dark:border-zinc-800 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed opacity-60">
                                                        <span>SK</span> <i class="bi bi-lock-fill text-xs"></i>
                                                    </span>
                                                @endif

                                                <!-- TOMBOL IJAZAH MADRASAH -->
                                                @if (isset($dokumens['ijazah']))
                                                    <a href="{{ route('arsip.cetak', $dokumens['ijazah']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-amber-500/10 hover:bg-amber-600 text-amber-600 hover:text-white dark:text-amber-400 dark:hover:text-white border border-amber-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-printer-fill text-xs"></i> <span>Ijazah</span>
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-zinc-100/60 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600 border border-zinc-200/40 dark:border-zinc-800 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed opacity-60">
                                                        <span>Ijazah</span> <i class="bi bi-lock-fill text-xs"></i>
                                                    </span>
                                                @endif

                                                <!-- TOMBOL SK AL-QUR'AN -->
                                                @if (isset($dokumens['sk_alquran']))
                                                    <a href="{{ route('arsip.cetak', $dokumens['sk_alquran']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-teal-500/10 hover:bg-teal-600 text-teal-600 hover:text-white dark:text-teal-400 dark:hover:text-white border border-teal-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-patch-check-fill text-xs"></i> <span>SK
                                                            Al-Qur'an</span>
                                                    </a>
                                                @elseif (isset($dokumens['peserta_alquran']))
                                                    <a href="{{ route('penilaian-ujian-alquran.cetak-sk', $dokumens['peserta_alquran']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-teal-500/10 hover:bg-teal-600 text-teal-600 hover:text-white dark:text-teal-400 dark:hover:text-white border border-teal-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-patch-check-fill text-xs"></i> <span>SK
                                                            Al-Qur'an</span>
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-zinc-100/60 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600 border border-zinc-200/40 dark:border-zinc-800 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed opacity-60">
                                                        <span>SK Al-Qur'an</span> <i
                                                            class="bi bi-lock-fill text-xs"></i>
                                                    </span>
                                                @endif

                                                <!-- TOMBOL IJAZAH AL-QUR'AN -->
                                                @if (isset($dokumens['ijazah_alquran']))
                                                    <a href="{{ route('arsip.cetak', $dokumens['ijazah_alquran']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white dark:text-emerald-400 dark:hover:text-white border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-book-half text-xs"></i> <span>Ijazah
                                                            Al-Qur'an</span>
                                                    </a>
                                                @elseif (isset($dokumens['peserta_alquran']) && $dokumens['peserta_alquran']->status_kelulusan === 'Lulus')
                                                    <a href="{{ route('penilaian-ujian-alquran.cetak-ijazah', $dokumens['peserta_alquran']->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white dark:text-emerald-400 dark:hover:text-white border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-2xs">
                                                        <i class="bi bi-book-half text-xs"></i> <span>Ijazah
                                                            Al-Qur'an</span>
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 h-8 bg-zinc-100/60 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600 border border-zinc-200/40 dark:border-zinc-800 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed opacity-60">
                                                        <span>Ijazah Al-Qur'an</span> <i
                                                            class="bi bi-lock-fill text-xs"></i>
                                                    </span>
                                                @endif

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <!-- Jika NISM Tidak Ditemukan -->
                <div class="m3-glass-card !border-rose-500/30 rounded-3xl p-6 text-center max-w-lg mx-auto shadow-2xs">
                    <div
                        class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h3 class="text-base font-black text-rose-600 dark:text-rose-400">Murid Tidak Ditemukan</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Pastikan barcode yang di-scan benar atau
                        NISM telah terdaftar di database sistem.</p>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
