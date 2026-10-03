@forelse($ujians as $item)
    <div
        class="m3-glass-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group relative overflow-hidden hover:border-primary/40 dark:hover:border-primary-dark/40 hover:scale-[1.005] transition-all duration-300">

        <!-- Card Info Section -->
        <div class="flex items-center gap-3 md:gap-4 relative z-10 w-full sm:w-auto">

            <!-- Badge Nomor -->
            <span
                class="w-9 h-9 flex items-center justify-center bg-zinc-100/80 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200/80 dark:border-zinc-800 shrink-0">
                {{ $loop->iteration }}
            </span>

            <!-- Ikon Buku/Ujian -->
            <div
                class="w-11 h-11 rounded-xl shrink-0 flex items-center justify-center bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 dark:border-primary-dark/30 transition-transform duration-300 group-hover:scale-105 shadow-2xs">
                <i class="bi bi-mortarboard-fill text-lg"></i>
            </div>

            <!-- Detail Ujian -->
            <div class="flex-1 overflow-hidden">
                <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                    <h4
                        class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-snug transition-colors duration-300">
                        {{ $item->nama_ujian }}
                    </h4>



                    <!-- Badge Sasaran Tingkat -->
                    @if ($item->tingkat)
                        @php
                            $kode = strtoupper($item->tingkat->kode_tingkat);
                            $badgeColor = match ($kode) {
                                'TPQ'
                                    => 'text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-950/40 border-emerald-200/80 dark:border-emerald-800/40',
                                'IBT'
                                    => 'text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-950/40 border-amber-200/80 dark:border-amber-800/40',
                                'TSA'
                                    => 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-950/40 border-blue-200/80 dark:border-blue-800/40',
                                default
                                    => 'text-teal-600 bg-teal-50 dark:text-teal-400 dark:bg-teal-950/40 border-teal-200/80 dark:border-teal-800/40',
                            };
                            $labelKelas = match ($kode) {
                                'TPQ' => 'Kelas 3 TPQ',
                                'IBT' => 'Kelas 6 IBT',
                                'TSA' => 'Kelas 3 TSA',
                                default => 'Kelas Akhir ' . $item->tingkat->nama_tingkat,
                            };
                        @endphp
                        <span
                            class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md border flex items-center gap-1 {{ $badgeColor }}">
                            <i class="bi bi-people-fill text-[9px]"></i>
                            Khusus Tingkat {{ $item->tingkat->nama_tingkat }} ({{ $labelKelas }})
                        </span>
                    @else
                        <span
                            class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-950/40 rounded-md border border-emerald-200/80 dark:border-emerald-800/40 flex items-center gap-1">
                            <i class="bi bi-people-fill text-[9px]"></i>
                            Semua Tingkat (3 TPQ, 6 IBT, 3 TSA)
                        </span>
                    @endif
                </div>

                <p
                    class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 transition-colors duration-300">
                    <!-- Info Tahun Pelajaran -->
                    <span class="flex items-center gap-1 font-semibold">
                        <i class="bi bi-calendar-check text-xs"></i>
                        TP. {{ $item->tahunPelajaran->nama_hijriyah ?? '-' }} |
                        {{ $item->tahunPelajaran->nama_masehi ?? '-' }}
                    </span>

                    <span class="hidden sm:inline text-zinc-300 dark:text-zinc-700">•</span>

                    <!-- Info Waktu Pelaksanaan -->
                    <span class="flex items-center gap-1 font-semibold">
                        <i class="bi bi-clock-history text-xs"></i>
                        @if ($item->tanggal_mulai && $item->tanggal_selesai)
                            {{ $item->tanggal_mulai->translatedFormat('d M') }} -
                            {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                        @elseif($item->tanggal_mulai)
                            Mulai: {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                        @else
                            Waktu belum diatur
                        @endif
                    </span>
                </p>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div
            class="flex items-center justify-end gap-1.5 relative z-10 w-full sm:w-auto border-t sm:border-none border-zinc-100 dark:border-zinc-800/60 pt-2.5 sm:pt-0">



            @can('update ujian')
                <a href="{{ route('master-imni.edit', $item->id) }}"
                    class="min-w-[34px] min-h-[34px] w-8.5 h-8.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/40 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all hover:scale-105 active:scale-95 shadow-2xs action-modal outline-none"
                    title="Edit">
                    <i class="bi bi-pencil-fill text-xs"></i>
                </a>
            @endcan

            @can('delete ujian')
                <form action="{{ route('master-imni.destroy', $item->id) }}" method="POST"
                    class="delete-ajax inline m-0 p-0" data-refresh-target="#data-grid-container">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="min-w-[34px] min-h-[34px] w-8.5 h-8.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/60 dark:border-rose-800/40 text-rose-600 dark:text-rose-400 flex items-center justify-center hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-all hover:scale-105 active:scale-95 shadow-2xs outline-none"
                        title="Hapus">
                        <i class="bi bi-trash-fill text-xs"></i>
                    </button>
                </form>
            @endcan

        </div>
    </div>
@empty
    <!-- Empty State -->
    <x-empty-state icon="bi-journal-x" title="Agenda Ujian IMNI Belum Ada"
        message="Belum ada agenda pelaksanaan Imtihan Niha'i (IMNI) pada tahun pelajaran yang dipilih." />
@endforelse
