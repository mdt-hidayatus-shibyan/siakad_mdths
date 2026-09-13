@section('title', 'Ujian Al-Qur\'an (Syarat Kelulusan Ibtidaiyah)')

<x-app-layout>
    <!-- HEADER TOOLBAR -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="px-2.5 py-1 rounded-xl bg-primary/10 text-primary text-[11px] font-black uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-calendar2-week-fill"></i>
                    <span>Akademik</span>
                </span>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Master Agenda Ujian Al-Qur'an
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Kelola agenda pelaksanaan, KKM kelulusan, dan bobot minus kesalahan ujian.
            </p>
        </div>

        <!-- Filter Tahun Pelajaran & Tombol Tambah Event -->
        <div class="w-full md:w-auto flex flex-wrap items-center gap-2 md:justify-end">
            <form action="{{ route('ujian-alquran.index') }}" method="GET" id="formTahun"
                class="m-0 relative group h-10">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahun').submit()"
                    class="m3-input-glass w-full md:w-56 !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
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

            <a href="{{ route('ujian-alquran.create', ['tahun_id' => $tahunPelajaranId]) }}"
                class="action-modal px-4 h-10 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs font-black flex items-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer">
                <i class="bi bi-plus-lg text-sm"></i>
                <span>Buat Event Ujian</span>
            </a>
        </div>
    </div>

    <!-- DAFTAR EVENT UJIAN AL-QUR'AN (AJAX DATA GRID) -->
    <div id="data-grid-container" class="space-y-4">
        @forelse ($ujians as $ujian)
            @php
                $stat = $ujian->statistik;
            @endphp
            <div
                class="m3-glass-card p-5 md:p-6 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs space-y-4">

                <div
                    class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-4 border-b border-zinc-200/60 dark:border-zinc-800">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $ujian->status === 'Berjalan' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : ($ujian->status === 'Selesai' ? 'bg-blue-500/10 text-blue-600 border border-blue-500/20' : 'bg-zinc-200 text-zinc-700') }}">
                                {{ $ujian->status }}
                            </span>
                            <span class="text-xs text-zinc-400 font-mono">
                                <i class="bi bi-calendar-event mr-1"></i>
                                {{ $ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->translatedFormat('d F Y') : 'Jadwal 1 Hari' }}
                            </span>
                        </div>
                        <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white">
                            {{ $ujian->nama_ujian }}
                        </h3>
                        <p class="text-xs text-zinc-400">
                            Tahun Pelajaran: <strong
                                class="text-zinc-700 dark:text-zinc-300">{{ $ujian->tahunPelajaran->nama_hijriyah }}
                                ({{ $ujian->tahunPelajaran->nama_masehi }})
                            </strong>
                            • KKM Lulus: <strong class="text-emerald-600">&gt;
                                {{ (int) $ujian->kkm_kelulusan }}</strong>
                            • Bobot Pengurangan: <span class="text-rose-500 font-semibold">Jali
                                (-{{ (int) $ujian->bobot_jali }})</span> & <span
                                class="text-amber-500 font-semibold">Khofi (-{{ (int) $ujian->bobot_khofi }})</span>
                        </p>
                    </div>

                    <!-- Tombol Aksi Masuk Sesi -->
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('penilaian-ujian-alquran.index', ['tahun_id' => $ujian->tahun_pelajaran_id]) }}"
                            class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-1.5 shadow-2xs transition-all active:scale-95">
                            <i class="bi bi-patch-check-fill"></i>
                            <span>Input Nilai</span>
                        </a>

                        <a href="{{ route('peserta-ujian-alquran.index', ['tahun_id' => $ujian->tahun_pelajaran_id]) }}"
                            class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center gap-1.5 transition-all shadow-2xs border border-zinc-200/80 dark:border-zinc-700">
                            <i class="bi bi-people-fill"></i>
                            <span>Peserta ({{ $ujian->pesertas_count }})</span>
                        </a>

                        <a href="{{ route('rekap-ujian-alquran.index', ['tahun_id' => $ujian->tahun_pelajaran_id]) }}"
                            class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center gap-1.5 transition-all shadow-2xs border border-zinc-200/80 dark:border-zinc-700">
                            <i class="bi bi-journal-check"></i>
                            <span>Rekap</span>
                        </a>

                        <!-- Dropdown Opsi Edit / Hapus via Component M3 & AJAX -->
                        <div class="relative inline-block text-left" x-data="{ open: false }"
                            @click.outside="open = false">
                            <button type="button" @click="open = !open"
                                class="p-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 text-xs transition-all border border-zinc-200/80 dark:border-zinc-700 cursor-pointer flex items-center justify-center shadow-2xs"
                                title="Opsi Lainnya">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <div x-show="open" x-cloak style="display: none;"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                                class="absolute z-50 w-48 origin-top-right right-0 top-full mt-2 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-zinc-200/90 dark:border-zinc-800/90 rounded-2xl shadow-xl dark:shadow-none overflow-hidden p-1.5 space-y-0.5"
                                @click="open = false">

                                <a href="{{ route('ujian-alquran.edit', $ujian->id) }}"
                                    class="action-modal m3-dropdown-item w-full !text-zinc-700 dark:!text-zinc-300 hover:!text-amber-600 dark:hover:!text-amber-400 hover:!bg-amber-50 dark:hover:!bg-amber-950/30 cursor-pointer">
                                    <i class="bi bi-pencil-square text-amber-500 text-sm"></i>
                                    <span>Edit Konfigurasi</span>
                                </a>

                                <a href="{{ route('juri-ujian-alquran.index', ['tahun_id' => $ujian->tahun_pelajaran_id]) }}"
                                    class="m3-dropdown-item hover:!text-primary dark:hover:!text-primary-dark hover:!bg-primary/10">
                                    <i class="bi bi-person-badge text-primary text-sm"></i>
                                    <span>Atur Dewan Juri</span>
                                </a>

                                <div class="m3-dropdown-divider"></div>

                                <form action="{{ route('ujian-alquran.destroy', $ujian->id) }}" method="POST"
                                    class="delete-ajax" data-refresh-target="#data-grid-container">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="m3-dropdown-item w-full !text-rose-600 dark:!text-rose-400 hover:!bg-rose-50 dark:hover:!bg-rose-950/30 cursor-pointer">
                                        <i class="bi bi-trash3 text-sm"></i>
                                        <span>Hapus Agenda</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STATISTIK RINGKAS PER EVENT -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1 text-xs">
                    <div
                        class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-[10px] font-black uppercase text-zinc-400 block mb-0.5">Total Peserta</span>
                        <span
                            class="font-mono font-black text-base text-zinc-800 dark:text-zinc-200">{{ $stat->total }}
                            Murid</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20">
                        <span
                            class="text-[10px] font-black uppercase text-emerald-600 dark:text-emerald-400 block mb-0.5">Lulus
                            Ujian</span>
                        <span
                            class="font-mono font-black text-base text-emerald-600 dark:text-emerald-400">{{ $stat->lulus }}
                            Murid ({{ $stat->persen_lulus }}%)</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20">
                        <span
                            class="text-[10px] font-black uppercase text-rose-600 dark:text-rose-400 block mb-0.5">Tidak
                            Lulus / Remidi</span>
                        <span
                            class="font-mono font-black text-base text-rose-600 dark:text-rose-400">{{ $stat->tidak_lulus }}
                            Murid</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20">
                        <span
                            class="text-[10px] font-black uppercase text-amber-600 dark:text-amber-400 block mb-0.5">Belum
                            Diuji</span>
                        <span
                            class="font-mono font-black text-base text-amber-600 dark:text-amber-400">{{ $stat->belum_diuji }}
                            Murid</span>
                    </div>
                </div>

                <!-- Dewan Juri Ringkas -->
                @if ($ujian->juris->isNotEmpty())
                    <div class="pt-2 flex flex-wrap items-center gap-2 text-[11px] text-zinc-500">
                        <span class="font-bold text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
                            <i class="bi bi-person-badge"></i> Dewan Juri:
                        </span>
                        @foreach ($ujian->juris as $j)
                            <span
                                class="px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold border border-zinc-200 dark:border-zinc-700">
                                {{ $j->ustadz->nama_lengkap ?? '-' }}
                                <em>({{ $j->peran_juri === 'juri_jali' ? 'Jali' : ($j->peran_juri === 'juri_khofi' ? 'Khofi' : 'Penguji') }})</em>
                            </span>
                        @endforeach
                    </div>
                @endif

            </div>
        @empty
            <div
                class="m3-glass-card p-12 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs text-center space-y-3">
                <div
                    class="w-16 h-16 rounded-3xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-3xl mx-auto border border-emerald-500/20 shadow-2xs">
                    <i class="bi bi-book-half"></i>
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white">Belum Ada Agenda Ujian Al-Qur'an</h3>
                <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                    Belum ada agenda Ujian Al-Qur'an yang dibuat untuk Tahun Pelajaran terpilih. Klik tombol di bawah
                    untuk membuat agenda baru.
                </p>
                <div class="pt-2">
                    <a href="{{ route('ujian-alquran.create', ['tahun_id' => $tahunPelajaranId]) }}"
                        class="action-modal px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-2xs transition-all active:scale-95 inline-flex items-center">
                        <i class="bi bi-plus-lg mr-1"></i> Buat Agenda Ujian Al-Qur'an
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-app-layout>
