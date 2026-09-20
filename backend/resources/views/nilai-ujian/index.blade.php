@section('title', 'Progres Input Nilai')

<x-app-layout>
    <!-- HEADER & FILTER -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <div class="w-full xl:w-auto shrink-0">
            @include('nilai-ujian.menu')
        </div>

        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('nilai-ujian.index') }}" method="GET" id="formSelector"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- Filter Ujian -->
                <div class="relative w-full sm:w-[220px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-file-earmark-check text-sm"></i>
                    </div>
                    <select name="ujian_id" onchange="document.getElementById('formSelector').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        <option value="">-- Pilih Pelaksanaan Ujian --</option>
                        @foreach ($daftarUjian as $uj)
                            <option value="{{ $uj->id }}" {{ request('ujian_id') == $uj->id ? 'selected' : '' }}>
                                {{ $uj->nama_ujian }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <div class="w-full sm:w-auto shrink-0 flex flex-col sm:flex-row items-center gap-2">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-4 group/btn">
                        <i class="bi bi-search text-sm"></i>
                        <span>Cek Progres</span>
                    </button>

                    @if (request('ujian_id') && $dataProgres->count() > 0)
                        <a href="{{ route('nilai-ujian.cetak-progres', ['ujian_id' => request('ujian_id'), 'tahun_id' => $tahunPelajaranId]) }}"
                            target="_blank"
                            class="m3-btn-secondary w-full sm:w-auto h-10 px-4 group/btn inline-flex items-center justify-center gap-2 shadow-2xs">
                            <i class="bi bi-printer text-sm"></i>
                            <span>Cetak Progres</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- AREA KONTEN UTAMA -->
    @if (request('ujian_id'))

        <!-- Grid Kartu Progres -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-5 animate-[modalFadeIn_0.2s_ease-out]">
            @foreach ($dataProgres as $progres)
                @php
                    $barColor = 'bg-zinc-300 dark:bg-zinc-700';
                    if ($progres->persentase > 0 && $progres->persentase < 100) {
                        $barColor = 'bg-primary dark:bg-primary-dark';
                    } elseif ($progres->persentase == 100) {
                        $barColor =
                            $progres->total_diinput == $progres->total_dipublish ? 'bg-emerald-500' : 'bg-amber-500';
                    }
                @endphp

                <div
                    class="m3-glass-card p-4 sm:p-5 flex flex-col justify-between gap-4 relative overflow-hidden group hover:scale-[1.01] transition-all duration-300">

                    <div>
                        <!-- Header Kartu -->
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-black text-base text-zinc-900 dark:text-white uppercase tracking-tight">
                                    {{ $progres->ruangan->nama_ruangan }}
                                </h3>
                                <p
                                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 tracking-wider uppercase">
                                    {{ $progres->jumlah_murid }} Murid • {{ $progres->jumlah_mapel }} Mapel
                                </p>
                            </div>

                            <div class="text-right">
                                <span
                                    class="font-black text-2xl tracking-tight {{ $progres->persentase == 100 ? 'text-primary dark:text-primary-dark' : 'text-zinc-800 dark:text-zinc-200' }}">
                                    {{ $progres->persentase }}%
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar Component -->
                        <div
                            class="w-full bg-zinc-100 dark:bg-zinc-800/80 rounded-full h-2 overflow-hidden shadow-inner mb-3.5">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-700 ease-out"
                                style="width: {{ $progres->persentase }}%"></div>
                        </div>

                        <!-- Statistik Bawah -->
                        <div class="grid grid-cols-2 gap-2 border-t border-zinc-100 dark:border-zinc-800/80 pt-3">
                            <div class="flex flex-col">
                                <span
                                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Terkumpul</span>
                                <span class="text-xs font-black text-zinc-800 dark:text-zinc-200 mt-0.5">
                                    {{ $progres->total_diinput }} <span
                                        class="text-zinc-400 text-[10px] font-semibold">/
                                        {{ $progres->target_nilai }}</span>
                                </span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span
                                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Status</span>
                                @if ($progres->target_nilai == 0)
                                    <span class="text-xs font-black text-rose-500 mt-0.5">Master Kosong</span>
                                @elseif ($progres->persentase == 0)
                                    <span class="text-xs font-black text-zinc-400 mt-0.5">Belum Mulai</span>
                                @elseif ($progres->persentase < 100)
                                    <span class="text-xs font-black text-blue-500 mt-0.5">Proses Input</span>
                                @else
                                    @if ($progres->total_diinput == $progres->total_dipublish)
                                        <span
                                            class="text-xs font-black text-emerald-500 mt-0.5 flex items-center justify-end gap-1"><i
                                                class="bi bi-check2-all text-sm"></i> Selesai</span>
                                    @else
                                        <span
                                            class="text-xs font-black text-amber-500 mt-0.5 flex items-center justify-end gap-1"><i
                                                class="bi bi-pencil-fill text-[10px]"></i> Draft</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- INFO MAPEL BELUM LENGKAP -->
                        @if (count($progres->mapel_kurang) > 0)
                            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80">
                                <span
                                    class="text-[10px] font-black text-rose-500 uppercase tracking-wider flex items-center gap-1 mb-1.5">
                                    <i class="bi bi-exclamation-circle-fill"></i> Mapel Belum Lengkap:
                                </span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($progres->mapel_kurang as $mk)
                                        <span
                                            class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/40 rounded-md text-[9px] font-extrabold uppercase truncate max-w-[100px]"
                                            title="{{ $mk }}">
                                            {{ $mk }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- TOMBOL MENUJU INPUT LEGER -->
                    <div class="border-t border-zinc-100 dark:border-zinc-800/80 pt-3">
                        <a href="{{ route('nilai-ujian.input-leger', ['ujian_id' => $ujianTerpilih->id, 'ruangan_id' => $progres->ruangan->id]) }}"
                            class="w-full h-9 bg-zinc-100/80 hover:bg-primary hover:text-white dark:bg-zinc-800/60 dark:hover:bg-primary-dark dark:hover:text-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-800 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all duration-200 outline-none group/btn shadow-2xs">
                            <i class="bi bi-pencil-square text-xs"></i>
                            <span>Buka Form Leger</span>
                            <i class="bi bi-arrow-right transition-transform group-hover/btn:translate-x-1 text-xs"></i>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <!-- STATE AWAL PANDUAN -->
        <x-empty-state icon="bi-speedometer" title="Dashboard Pemantauan Nilai"
            message="Pilih pelaksanaan ujian di atas untuk memantau kelengkapan pengisian nilai dari seluruh ruangan kelas secara real-time." />
    @endif
</x-app-layout>
