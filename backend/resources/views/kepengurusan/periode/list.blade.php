@forelse($periode as $item)
    <div
        class="m3-glass-card p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group relative overflow-hidden transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40">

        <!-- Card Info Section -->
        <div class="flex items-center gap-3 md:gap-4 relative z-10 w-full sm:w-auto">

            <!-- Badge Nomor -->
            <span
                class="w-9 h-9 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200 dark:border-zinc-700 flex-shrink-0 shadow-2xs">
                {{ $loop->iteration }}
            </span>

            <!-- Ikon Kalender -->
            <div
                class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center bg-primary/10 dark:bg-primary-dark/20 border border-primary/20 text-primary dark:text-primary-dark shadow-2xs">
                <i class="bi bi-calendar3 text-base"></i>
            </div>

            <!-- Nama Periode & Tanggal -->
            <div class="flex-1 overflow-hidden">
                <div class="flex flex-wrap items-center gap-2">
                    <h4
                        class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight truncate">
                        {{ $item->nama_periode }}
                    </h4>
                    <!-- Badge Seumur Hidup -->
                    @if ($item->is_seumur_hidup)
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-purple-500/10 border border-purple-500/20 text-purple-600 dark:text-purple-400 shadow-2xs">
                            <i class="bi bi-infinity mr-1 text-[10px]"></i>
                            Seumur Hidup
                        </span>
                    @endif

                    <!-- Badge Status Aktif -->
                    @if ($item->status_aktif)
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 shadow-2xs">
                            Riwayat
                        </span>
                    @endif
                </div>

                <p
                    class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 truncate flex items-center gap-1.5 font-mono">
                    <i class="bi bi-clock-history text-zinc-400"></i>
                    {{ $item->masa_bakti_text }}
                </p>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div
            class="flex items-center justify-end gap-1.5 relative z-10 w-full sm:w-auto border-t sm:border-none border-zinc-200/60 dark:border-zinc-800 pt-3 sm:pt-0 mt-1 sm:mt-0">

            @can('update periode-pengurus')
                <a href="{{ route('periode-pengurus.edit', $item->id) }}"
                    class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-500/20 border border-blue-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none action-modal"
                    title="Edit Periode">
                    <i class="bi bi-pencil-fill text-xs"></i>
                </a>
            @endcan

            @can('delete periode-pengurus')
                <form action="{{ route('periode-pengurus.destroy', $item->id) }}" method="POST"
                    class="delete-ajax inline m-0 p-0">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center hover:bg-rose-500/20 border border-rose-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Hapus Periode">
                        <i class="bi bi-trash-fill text-xs"></i>
                    </button>
                </form>
            @endcan

        </div>
    </div>
@empty
    <!-- Empty State -->
    <x-empty-state icon="bi-calendar-x" title="Data Periode Masih Kosong"
        message="Anda belum menambahkan masa bakti kepengurusan." />
@endforelse
