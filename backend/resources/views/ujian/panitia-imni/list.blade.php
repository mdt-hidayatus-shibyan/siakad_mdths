@forelse($panitiaList as $item)
    @php
        $jabatanBadge = match($item->jabatan) {
            'Ketua' => [
                'bg' => 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-500/30',
                'icon' => 'bi-award-fill',
                'border' => 'hover:border-amber-500/40',
            ],
            'Bendahara' => [
                'bg' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
                'icon' => 'bi-wallet2',
                'border' => 'hover:border-emerald-500/40',
            ],
            default => [
                'bg' => 'bg-sky-500/15 text-sky-700 dark:text-sky-400 border-sky-500/30',
                'icon' => 'bi-person-check-fill',
                'border' => 'hover:border-sky-500/40',
            ],
        };
    @endphp

    <div class="m3-glass-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group relative overflow-hidden {{ $jabatanBadge['border'] }} hover:scale-[1.005] transition-all duration-300">

        <!-- Info Personel Panitia -->
        <div class="flex items-center gap-3 md:gap-4 relative z-10 w-full sm:w-auto min-w-0">
            <!-- Badge Nomor Urut -->
            <span class="w-9 h-9 flex items-center justify-center bg-zinc-100/80 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200/80 dark:border-zinc-800 shrink-0">
                {{ $loop->iteration }}
            </span>

            <!-- Foto / Avatar Ustadz -->
            <div class="w-12 h-12 rounded-xl shrink-0 overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/60 shadow-2xs flex items-center justify-center relative">
                @if ($item->ustadz?->foto_url)
                    <img src="{{ $item->ustadz->foto_url }}" alt="{{ $item->ustadz->nama_lengkap }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-primary/10 text-primary dark:text-primary-dark font-black text-sm">
                        {{ strtoupper(substr($item->ustadz?->nama_lengkap ?? 'U', 0, 2)) }}
                    </div>
                @endif
            </div>

            <!-- Detail Jabatan & Nama -->
            <div class="flex-1 overflow-hidden min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <!-- Badge Jabatan -->
                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md border flex items-center gap-1 {{ $jabatanBadge['bg'] }}">
                        <i class="bi {{ $jabatanBadge['icon'] }} text-[10px]"></i>
                        {{ $item->jabatan }}
                    </span>

                    <!-- Status Aktif Badge -->
                    @if ($item->is_active)
                        <span class="px-2 py-0.5 text-[9px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                            Aktif
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-[9px] font-bold text-zinc-500 dark:text-zinc-400 bg-zinc-500/10 rounded-md border border-zinc-500/20">
                            Nonaktif
                        </span>
                    @endif

                    @if ($item->no_sk)
                        <span class="px-2 py-0.5 text-[9px] font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded-md border border-zinc-200/60 dark:border-zinc-700/60">
                            SK: {{ $item->no_sk }}
                        </span>
                    @endif
                </div>

                <h4 class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-snug transition-colors duration-300 truncate">
                    {{ $item->ustadz?->nama_lengkap ?? 'Ustadz Tidak Ditemukan' }}
                </h4>

                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="font-medium">
                        NIGM: {{ $item->ustadz?->nigm ?? '-' }}
                    </span>
                    @if ($item->ustadz?->no_hp)
                        <span class="hidden sm:inline text-zinc-300 dark:text-zinc-700">•</span>
                        <span class="flex items-center gap-1">
                            <i class="bi bi-telephone text-[10px]"></i>
                            {{ $item->ustadz->no_hp }}
                        </span>
                    @endif
                    @if ($item->keterangan)
                        <span class="hidden sm:inline text-zinc-300 dark:text-zinc-700">•</span>
                        <span class="italic text-zinc-400 dark:text-zinc-500">
                            "{{ $item->keterangan }}"
                        </span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="flex items-center justify-end gap-2.5 relative z-10 w-full sm:w-auto border-t sm:border-none border-zinc-100 dark:border-zinc-800/60 pt-2.5 sm:pt-0">

            @can('update ujian')
                <!-- Toggle Status Component -->
                <x-toggle
                    :checked="$item->is_active"
                    :url="route('panitia-imni.toggle-status', $item->id)"
                    activeText="Aktif"
                    inactiveText="Nonaktif"
                />

                <!-- Edit Button -->
                <a href="{{ route('panitia-imni.edit', $item->id) }}"
                    class="min-w-[34px] min-h-[34px] w-8.5 h-8.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/40 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all hover:scale-105 active:scale-95 shadow-2xs action-modal outline-none"
                    title="Edit Susunan Panitia">
                    <i class="bi bi-pencil-fill text-xs"></i>
                </a>
            @endcan

            @can('delete ujian')
                <!-- Delete Button -->
                <form action="{{ route('panitia-imni.destroy', $item->id) }}" method="POST" class="delete-ajax inline m-0 p-0">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="min-w-[34px] min-h-[34px] w-8.5 h-8.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/60 dark:border-rose-800/40 text-rose-600 dark:text-rose-400 flex items-center justify-center hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-all hover:scale-105 active:scale-95 shadow-2xs outline-none"
                        title="Hapus Dari Kepanitiaan">
                        <i class="bi bi-trash-fill text-xs"></i>
                    </button>
                </form>
            @endcan
        </div>
    </div>
@empty
    <div class="m3-glass-card p-8 text-center relative z-10 flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mb-3 border border-amber-500/20">
            <i class="bi bi-person-x"></i>
        </div>
        <h4 class="text-base font-black text-zinc-800 dark:text-zinc-200">Belum Ada Susunan Panitia IMNI</h4>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-md mt-1 mb-4">
            Susunan kepanitiaan IMNI (Ketua, Bendahara, Anggota) pada tahun pelajaran yang dipilih belum ditetapkan.
        </p>
        @can('create ujian')
            <a href="{{ route('panitia-imni.create', ['tahun_pelajaran_id' => $selectedTahunId]) }}" class="m3-btn-primary action-modal">
                <i class="bi bi-person-plus-fill"></i>
                <span>Tetapkan Panitia IMNI Sekarang</span>
            </a>
        @endcan
    </div>
@endforelse
