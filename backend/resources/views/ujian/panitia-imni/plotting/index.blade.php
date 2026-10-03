@section('title', 'Plotting Peserta & Pengawas IMNI')

<x-app-layout>
    <div>
        <!-- 1. HEADER SECTION -->
        <div
            class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Plotting Ruangan & Pengawas IMNI
                </h2>
                <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Daftar jadwal pelaksanaan ujian IMNI harian, acak nomor NISM per ruangan, pengaturan pengawas, dan
                    cetak mading.
                </p>
            </div>

            <!-- Toolbar Aksi -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('plotting-imni.index') }}" method="GET" id="formTahunPlotting" class="m-0">
                    <div class="relative">
                        <select name="tahun_id" onchange="document.getElementById('formTahunPlotting').submit()"
                            class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                    {{ $th->nama_hijriyah }} H | {{ $th->nama_masehi }} M
                                    {{ $th->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                    </div>
                </form>


            </div>
        </div>

        <!-- 2. SUMMARY CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4 mb-6">
            <div
                class="m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0 border border-indigo-500/20">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block">Hari Ujian</span>
                    <span
                        class="text-lg font-black text-zinc-900 dark:text-white font-mono">{{ $daftarHariUjian->count() }}</span>
                    <span class="text-[11px] font-bold text-zinc-500">Hari</span>
                </div>
            </div>

            <div
                class="m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-500/20">
                    <i class="bi bi-door-open-fill"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block">Ruangan
                        Ujian</span>
                    <span class="text-lg font-black text-zinc-900 dark:text-white font-mono">{{ $totalRuangan }}</span>
                    <span class="text-[11px] font-bold text-zinc-500">Ruangan (R1..R{{ $totalRuangan }})</span>
                </div>
            </div>

            <div
                class="m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg shrink-0 border border-orange-500/20">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block">Peserta 6
                        IBT</span>
                    <span
                        class="text-lg font-black text-zinc-900 dark:text-white font-mono">{{ $pesertaIbtCount }}</span>
                    <span class="text-[11px] font-bold text-zinc-500">Murid</span>
                </div>
            </div>

            <div
                class="m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shrink-0 border border-blue-500/20">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block">Peserta 3
                        TSA</span>
                    <span
                        class="text-lg font-black text-zinc-900 dark:text-white font-mono">{{ $pesertaTsaCount }}</span>
                    <span class="text-[11px] font-bold text-zinc-500">Murid</span>
                </div>
            </div>
        </div>

        <!-- 3. TABEL DAFTAR HARI PELAKSANAAN UJIAN IMNI -->
        <div
            class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
            <!-- Table Header -->
            <div
                class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <h4 class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                        Jadwal Ujian & Plotting Harian
                    </h4>
                </div>
                <div class="text-xs font-bold text-zinc-400 font-mono">
                    Total: <strong class="text-zinc-700 dark:text-zinc-200">{{ $daftarHariUjian->count() }}</strong>
                    Hari Pelaksanaan
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-20">Hari Ke-</th>
                            <th class="py-3.5 px-4 w-44">Tanggal Pelaksanaan</th>
                            <th class="py-3.5 px-4">Jadwal Ujian Kelas 6 IBT</th>
                            <th class="py-3.5 px-4">Jadwal Ujian Kelas 3 TSA</th>
                            <th class="py-3.5 px-4 text-center w-36">Status Plotting</th>
                            <th class="py-3.5 px-4 text-center w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse ($daftarHariUjian as $h)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors">
                                <!-- Hari Ke-n -->
                                <td class="py-3.5 px-4 text-center font-mono font-black">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        {{ $h->hari_ke }}
                                    </span>
                                </td>

                                <!-- Tanggal -->
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-xs text-zinc-900 dark:text-white">
                                        {{ $h->nama_hari }}
                                    </div>
                                    <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 font-semibold">
                                        {{ $h->tanggal_format }}
                                    </div>
                                </td>

                                <!-- Jadwal 6 IBT -->
                                <td class="py-3.5 px-4">
                                    @if ($h->jadwal_ibt->count() > 0)
                                        <div class="space-y-1">
                                            @foreach ($h->jadwal_ibt as $jb)
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span
                                                        class="px-2 py-0.5 rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400 font-black text-[11px] border border-orange-500/20">
                                                        {{ $jb['nama_mapel'] }}
                                                    </span>
                                                    @if ($jb['waktu'])
                                                        <span class="text-[10px] font-mono font-bold text-zinc-400">
                                                            ({{ $jb['waktu'] }})
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">- Tidak Ada Ujian -</span>
                                    @endif
                                </td>

                                <!-- Jadwal 3 TSA -->
                                <td class="py-3.5 px-4">
                                    @if ($h->jadwal_tsa->count() > 0)
                                        <div class="space-y-1">
                                            @foreach ($h->jadwal_tsa as $jt)
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span
                                                        class="px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black text-[11px] border border-blue-500/20">
                                                        {{ $jt['nama_mapel'] }}
                                                    </span>
                                                    @if ($jt['waktu'])
                                                        <span class="text-[10px] font-mono font-bold text-zinc-400">
                                                            ({{ $jt['waktu'] }})
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">- Tidak Ada Ujian -</span>
                                    @endif
                                </td>

                                <!-- Status Plotting & Pengawas -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="space-y-1.5 flex flex-col items-center">
                                        <!-- Status Plotting Santri -->
                                        @if ($h->total_terplot > 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[10.5px] border border-emerald-500/20">
                                                <i class="bi bi-check-circle-fill text-[10px]"></i>
                                                {{ $h->total_terplot }} Peserta
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-[10.5px] border border-amber-500/20">
                                                <i class="bi bi-exclamation-circle text-[10px]"></i>
                                                Belum Diplot
                                            </span>
                                        @endif

                                        <!-- Status Pengawas Ruangan -->
                                        @if ($h->total_ruangan > 0 && $h->total_pengawas >= $h->total_ruangan)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-black text-[10px] border border-indigo-500/20">
                                                <i class="bi bi-person-check-fill text-[10px] text-emerald-600"></i>
                                                Pengawas Lengkap ({{ $h->total_pengawas }}/{{ $h->total_ruangan }})
                                            </span>
                                        @elseif ($h->total_pengawas > 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-[10px] border border-amber-500/20">
                                                <i class="bi bi-person-fill-exclamation text-[10px]"></i>
                                                Pengawas Terisi {{ $h->total_pengawas }}/{{ $h->total_ruangan }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-zinc-500/10 text-zinc-500 dark:text-zinc-400 font-bold text-[10px] border border-zinc-500/20">
                                                <i class="bi bi-person-x-fill text-[10px]"></i>
                                                Pengawas Belum Diisi (0/{{ $h->total_ruangan }})
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <!-- Menuju Halaman Plotting Peserta -->
                                        <a href="{{ route('plotting-imni.harian', ['tanggal' => $h->tanggal, 'tahun_id' => $selectedTahunId]) }}"
                                            class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black transition-all shadow-2xs flex items-center gap-1 cursor-pointer">
                                            <i class="bi bi-shuffle text-xs"></i>
                                            <span>Plotting Peserta</span>
                                        </a>

                                        <!-- Cetak Mading -->
                                        <a href="{{ route('plotting-imni.cetak-mading', ['tanggal' => $h->tanggal, 'tahun_id' => $selectedTahunId]) }}"
                                            target="_blank"
                                            class="px-2.5 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                                            title="Cetak Format Mading">
                                            <i class="bi bi-printer text-xs"></i>
                                            <span>Mading</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-zinc-400">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-2 text-xl text-zinc-400">
                                        <i class="bi bi-calendar-x"></i>
                                    </div>
                                    <h5 class="font-black text-zinc-700 dark:text-zinc-300 text-sm">Belum Ada Jadwal
                                        Ujian IMNI</h5>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        Silakan buat jadwal ujian IMNI terlebih dahulu pada menu <strong>Jadwal
                                            Ujian</strong>.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
