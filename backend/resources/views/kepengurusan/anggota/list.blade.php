@forelse($anggota as $item)
    <div
        class="m3-glass-card p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group relative overflow-hidden transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40">

        <!-- Card Info Section -->
        <div class="flex items-center gap-3 md:gap-4 relative z-10 w-full sm:w-auto">
            <!-- Badge Nomor -->
            <span
                class="w-9 h-9 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200 dark:border-zinc-700 flex-shrink-0 shadow-2xs">
                {{ $loop->iteration }}
            </span>

            <!-- Foto Profil (Memanfaatkan Accessor $item->foto_utama) -->
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-xl overflow-hidden flex-shrink-0 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                @if ($item->foto_utama)
                    <img src="{{ asset('storage/' . $item->foto_utama) }}" alt="{{ $item->nama_lengkap }}"
                        class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-zinc-400 dark:text-zinc-500">
                        <i class="bi bi-person-fill text-lg"></i>
                    </div>
                @endif
            </div>

            <!-- Nama & Info Detail -->
            <div class="flex-1 overflow-hidden">
                <div class="flex items-center gap-2">
                    <h4
                        class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight truncate">
                        {{ $item->nama_lengkap }}
                    </h4>
                    <!-- Badge Gender -->
                    @if ($item->jenis_kelamin == 'L')
                        <span
                            class="px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 bg-blue-500/10 border border-blue-500/20 rounded shadow-2xs">L</span>
                    @else
                        <span
                            class="px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-pink-600 dark:text-pink-400 bg-pink-500/10 border border-pink-500/20 rounded shadow-2xs">P</span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 font-mono shadow-2xs">
                        <i class="bi bi-credit-card-2-front text-[9px]"></i>
                        {{ $item->nik ? mask_nik($item->nik) : 'Tanpa NIK' }}
                    </span>

                    <!-- Indikator apakah dia Ustadz atau Staf Umum -->
                    @if ($item->ustadz_id)
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider shadow-2xs">
                            <i class="bi bi-journal-check text-[9px]"></i> Data Ustadz
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider shadow-2xs">
                            <i class="bi bi-person-badge text-[9px]"></i> Staf / Umum
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div
            class="flex items-center justify-end gap-1.5 relative z-10 w-full sm:w-auto border-t sm:border-none border-zinc-200/60 dark:border-zinc-800 pt-3 sm:pt-0 mt-1 sm:mt-0">
            @can('update anggota')
                <a href="{{ route('anggota.edit', $item->id) }}"
                    class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-500/20 border border-blue-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none action-modal"
                    title="Edit Anggota">
                    <i class="bi bi-pencil-fill text-xs"></i>
                </a>
            @endcan

            @can('delete anggota')
                <form action="{{ route('anggota.destroy', $item->id) }}" method="POST" class="delete-ajax inline m-0 p-0">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center hover:bg-rose-500/20 border border-rose-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Hapus Anggota">
                        <i class="bi bi-trash-fill text-xs"></i>
                    </button>
                </form>
            @endcan
        </div>
    </div>
@empty
    <x-empty-state icon="bi-people" title="Data Anggota Kosong" message="Belum ada data personel / anggota yayasan." />
@endforelse
