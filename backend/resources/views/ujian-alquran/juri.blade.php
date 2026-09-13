@section('title', 'Dewan Juri Ujian Al-Qur\'an' . ($ujian ? ' - ' . $ujian->nama_ujian : ''))

<x-app-layout>
    <!-- HEADER TOOLBAR -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="px-2.5 py-1 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[11px] font-black uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Dewan Juri</span>
                </span>
                <span class="text-xs text-zinc-400 font-mono">
                    {{ $ujian->tahunPelajaran->nama_hijriyah ?? ($daftarTahun->firstWhere('id', $tahunPelajaranId)?->nama_hijriyah ?? '') }}
                </span>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Dewan Juri: {{ $ujian->nama_ujian ?? 'Ujian Al-Qur\'an' }}
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Konfigurasi penugasan ustadz penguji untuk penilaian Khotho' Jali
                (-{{ (int) ($ujian->bobot_jali ?? 5) }}) dan Khotho' Khofi (-{{ (int) ($ujian->bobot_khofi ?? 3) }}).
            </p>
        </div>

        <!-- Filter Tahun Pelajaran & Tombol Aksi Tambah Juri -->
        <div class="w-full md:w-auto flex flex-wrap items-center gap-2 md:justify-end">
            <!-- Filter Tahun Pelajaran -->
            <form action="{{ route('juri-ujian-alquran.index') }}" method="GET" id="formTahunJuri"
                class="m-0 relative group h-10">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahunJuri').submit()"
                    class="m3-input-glass w-full md:w-56 !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none h-10">
                    @foreach ($daftarTahun as $tp)
                        <option value="{{ $tp->id }}"
                            {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }} {{ $tp->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>

            @if ($ujian)
                <!-- Tombol Tetapkan Juri Baru (Modal AJAX) -->
                <a href="{{ route('juri-ujian-alquran.create', ['tahun_id' => $tahunPelajaranId]) }}"
                    class="action-modal px-4 h-10 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs font-black flex items-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <i class="bi bi-person-plus-fill text-sm"></i>
                    <span>Tetapkan Dewan Juri</span>
                </a>

                <a href="{{ route('penilaian-ujian-alquran.index', ['tahun_id' => $tahunPelajaranId]) }}"
                    class="px-4 h-10 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black flex items-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <i class="bi bi-patch-check-fill text-sm"></i>
                    <span>Input Nilai</span>
                </a>
            @endif
        </div>
    </div>

    @if (!$ujian)
        <div
            class="m3-glass-card p-8 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 text-center space-y-4 my-6">
            <div
                class="w-16 h-16 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto text-2xl">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3 class="text-lg font-black text-zinc-800 dark:text-zinc-200">Belum Ada Agenda Ujian Al-Qur'an di Tahun
                Pelajaran Ini</h3>
            <p class="text-xs text-zinc-500 max-w-md mx-auto">
                Silakan buat Master Agenda Ujian Al-Qur'an terlebih dahulu pada tahun pelajaran ini sebelum menetapkan
                Dewan Juri.
            </p>
            <div>
                <a href="{{ route('ujian-alquran.create', ['tahun_id' => $tahunPelajaranId]) }}"
                    class="action-modal m3-btn-primary px-5 py-2.5 rounded-2xl text-xs font-black inline-flex items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Buat Agenda Ujian Sekarang
                </a>
            </div>
        </div>
    @else
        <!-- PANEL FILTER & PENCARIAN -->
        <div class="m3-glass-card p-3 md:p-3.5 mb-6 shadow-2xs relative z-10 print:hidden">
            <form action="{{ route('juri-ujian-alquran.index') }}" method="GET"
                class="w-full flex flex-col md:flex-row gap-2.5 items-center">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- Filter Kategori Juri (2 Kategori Saja) -->
                <div class="relative w-full md:w-64 h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-funnel-fill text-xs"></i>
                    </div>
                    <select name="kategori_juri" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs md:text-sm font-semibold cursor-pointer appearance-none h-10">
                        <option value="">Semua Kategori Bidang</option>
                        <option value="Khotho Jali" {{ request('kategori_juri') === 'Khotho Jali' ? 'selected' : '' }}>
                            Khotho' Jali (Tajwid Berat)
                        </option>
                        <option value="Khotho Khofi"
                            {{ request('kategori_juri') === 'Khotho Khofi' ? 'selected' : '' }}>
                            Khotho' Khofi (Tajwid Samar)
                        </option>
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-black"></i>
                    </div>
                </div>

                <!-- Input Search Keyword -->
                <div class="relative w-full flex-1 h-10 group/search">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                        <i class="bi bi-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Ketik Nama Ustadz / NIGM / Keterangan..." autocomplete="off"
                        class="m3-input-glass w-full !pl-9.5 !pr-9 text-xs md:text-sm font-semibold h-10">
                    @if (request('search'))
                        <a href="{{ route('juri-ujian-alquran.index', array_filter(['tahun_id' => $tahunPelajaranId, 'kategori_juri' => request('kategori_juri')])) }}"
                            class="absolute inset-y-0 right-0 w-9 h-full flex items-center justify-center text-zinc-400 hover:text-rose-500 transition-colors"
                            title="Hapus Pencarian">
                            <i class="bi bi-x-circle-fill text-sm"></i>
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="m3-btn-primary w-full md:w-auto px-5 h-10 text-xs md:text-sm font-black shadow-2xs flex items-center justify-center gap-1.5 shrink-0">
                    <i class="bi bi-search text-xs"></i> <span>Cari</span>
                </button>
            </form>
        </div>

        <!-- DAFTAR DEWAN JURI (AJAX REFRESH CONTAINER) -->
        <div id="data-table-container"
            class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 p-5 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800 mb-2">
                <div>
                    <h3
                        class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-person-badge-fill text-amber-500"></i>
                        <span>Daftar Dewan Juri Penguji</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Menampilkan {{ $juris->count() }} ustadz penguji terdaftar pada agenda ini.
                    </p>
                </div>
                <span
                    class="px-3 py-1 rounded-xl text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    {{ $juris->count() }} Juri
                </span>
            </div>

            <div class="space-y-3">
                @forelse ($juris as $index => $j)
                    <div
                        class="p-4 rounded-2xl {{ $j->is_penanggung_jawab ? 'bg-amber-500/[0.04] dark:bg-amber-500/[0.08] border-2 border-amber-500/30' : 'bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                        <div class="flex items-center gap-3.5">
                            <div
                                class="w-11 h-11 rounded-2xl {{ $j->is_penanggung_jawab ? 'bg-amber-500 text-white shadow-amber-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }} flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                                <span class="font-mono">{{ $j->peran_juri === 'Juri 1' ? 'J1' : 'J2' }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-bold text-sm text-zinc-900 dark:text-white">
                                        {{ $j->ustadz->nama_lengkap ?? '-' }}
                                    </h4>
                                    <span
                                        class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $j->kategori_juri === 'Khotho Jali' ? 'bg-rose-500/10 text-rose-600 border border-rose-500/20' : 'bg-amber-500/10 text-amber-600 border border-amber-500/20' }}">
                                        {{ $j->peran_juri }} • {{ $j->kategori_juri }}
                                    </span>
                                    @if ($j->is_penanggung_jawab)
                                        <span
                                            class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1 shadow-2xs">
                                            <i class="bi bi-star-fill text-amber-500"></i>
                                            <span>Penanggung Jawab (TTD SK & Ijazah)</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-zinc-400">
                                    <span class="font-mono text-[11px]">NIGM: {{ $j->ustadz->nigm ?? '-' }}</span>
                                    @if ($j->ustadz->no_hp)
                                        <span class="text-[11px]"><i
                                                class="bi bi-telephone mr-1"></i>{{ $j->ustadz->no_hp }}</span>
                                    @endif
                                    @if ($j->keterangan)
                                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400 italic">
                                            • "{{ $j->keterangan }}"
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi AJAX (Set PJ, Edit & Delete) -->
                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            @if (!$j->is_penanggung_jawab)
                                <!-- Tombol Cepat Jadikan PJ AJAX -->
                                <form action="{{ route('juri-ujian-alquran.set-pj', $j->id) }}" method="POST"
                                    class="ajax-post inline-block m-0" data-refresh-target="#data-table-container">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-xl bg-zinc-100 hover:bg-amber-500 hover:text-white dark:bg-zinc-800 dark:hover:bg-amber-500 text-zinc-700 dark:text-zinc-200 text-xs font-bold border border-zinc-200 dark:border-zinc-700 flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer shadow-2xs"
                                        title="Tetapkan Ustadz ini sebagai Penanggung Jawab Ujian & TTD Dokumen">
                                        <i class="bi bi-star text-amber-500"></i>
                                        <span class="hidden sm:inline">Set PJ</span>
                                    </button>
                                </form>
                            @endif

                            <!-- Edit Modal AJAX -->
                            <a href="{{ route('juri-ujian-alquran.edit', $j->id) }}"
                                class="action-modal p-2 rounded-xl bg-amber-500/10 hover:bg-amber-500 hover:text-white text-amber-600 border border-amber-500/20 transition-all active:scale-90 cursor-pointer shadow-2xs"
                                title="Edit Penugasan Juri">
                                <i class="bi bi-pencil-square text-sm"></i>
                            </a>

                            <!-- Hapus AJAX Form -->
                            <form action="{{ route('juri-ujian-alquran.destroy', $j->id) }}" method="POST"
                                class="delete-ajax inline-block m-0" data-refresh-target="#data-table-container">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-600 border border-rose-500/20 transition-all active:scale-90 cursor-pointer shadow-2xs"
                                    title="Hapus Juri">
                                    <i class="bi bi-trash3 text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div
                        class="text-center py-10 text-zinc-400 text-xs italic border border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl">
                        <i class="bi bi-person-x text-3xl block mb-2 opacity-50"></i>
                        Belum ada dewan juri yang ditetapkan. Silakan klik tombol "Tetapkan Dewan Juri" di atas.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- INFO BOX PANDUAN TUGAS DEWAN JURI -->
        <div
            class="mt-6 p-5 rounded-3xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 shadow-2xs space-y-2.5 text-xs">
            <h4 class="font-black text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 text-sm">
                <i class="bi bi-info-circle-fill"></i>
                <span>Panduan & Pembagian Tugas Dewan Juri</span>
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-zinc-600 dark:text-zinc-400 text-xs mt-2">
                <div
                    class="p-3 rounded-2xl bg-white/60 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800">
                    <strong class="text-rose-600 dark:text-rose-400 block mb-1">1. Juri 1 — Khotho' Jali (Pengurangan
                        -{{ (int) $ujian->bobot_jali }} pt/err)</strong>
                    <p class="text-[11px] leading-relaxed">
                        Mengamati dan menghitung kesalahan tajwid berat/nyata, seperti perubahan huruf hijaiyyah (ح jadi
                        هـ, ع jadi ء), harakat tertukar, atau kalimat/kata yang terlewat.
                    </p>
                </div>
                <div
                    class="p-3 rounded-2xl bg-white/60 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800">
                    <strong class="text-amber-600 dark:text-amber-400 block mb-1">2. Juri 2 — Khotho' Khofi
                        (Pengurangan -{{ (int) $ujian->bobot_khofi }} pt/err)</strong>
                    <p class="text-[11px] leading-relaxed">
                        Mengamati dan menghitung kesalahan tajwid ringan/samar, seperti panjang mad yang
                        kurang/berlebih, kesempurnaan ghunnah, ikhfa', iqlab, atau sifat huruf.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
