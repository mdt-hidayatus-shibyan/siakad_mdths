@forelse($walis as $wali)
    <div
        class="m3-glass-card p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 md:gap-5 group relative overflow-hidden transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40">

        <!-- ================= KIRI: INFO KELUARGA ================= -->
        <div class="flex items-start sm:items-center gap-3 md:gap-4 relative z-10 flex-1 overflow-hidden">

            <span
                class="w-9 h-9 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200 dark:border-zinc-700 flex-shrink-0 shadow-2xs">
                {{ $loop->iteration }}
            </span>

            <!-- Avatar / Ikon Rumah -->
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-lg text-zinc-500 dark:text-zinc-400 shrink-0 relative group-hover:scale-105 transition-transform shadow-2xs">
                <i class="bi bi-person-fill"></i>

                <!-- Indikator Tidak Aktif -->
                @if (!$wali->is_active)
                    <span
                        class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-500 border-2 border-white dark:border-black shadow-2xs animate-pulse"
                        title="KK Tidak Aktif"></span>
                @endif
            </div>

            <!-- Detail Teks -->
            <div class="flex flex-col flex-1 overflow-hidden">
                <h4
                    class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight mb-1 truncate flex items-center gap-2 flex-wrap">
                    {{ $wali->nama_kepala_keluarga }}

                    <!-- Badge Status Keluarga -->
                    <span
                        class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                        {{ $wali->kepala_keluarga }}
                    </span>

                    <!-- Badge Khusus Asatidz -->
                    @if ($wali->is_ustadz)
                        <span
                            class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shadow-2xs"
                            title="Keluarga Asatidz">
                            Ustadz
                        </span>
                    @endif
                </h4>

                <!-- Deretan Info Lanjutan (Tags) -->
                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">

                    <!-- No Registrasi -->
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-primary/10 dark:bg-primary-dark/20 border border-primary/20 text-[10px] font-black text-primary dark:text-primary-dark uppercase tracking-wider shadow-2xs font-mono">
                        <i class="bi bi-hash text-xs"></i>{{ $wali->no_registrasi }}
                    </span>

                    <!-- No KK -->
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider shadow-2xs font-mono">
                        KK: <span
                            class="text-zinc-900 dark:text-zinc-200 ml-1">{{ $wali->no_kk ? mask_kk($wali->no_kk) : 'Belum Ada' }}</span>
                    </span>

                    <!-- No WhatsApp -->
                    @if ($wali->no_hp)
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 tracking-wider shadow-2xs font-mono">
                            <i class="bi bi-whatsapp text-[10px]"></i> {{ $wali->no_hp }}
                        </span>
                    @endif

                    <!-- Kampung -->
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider shadow-2xs">
                        <i class="bi bi-geo-alt text-zinc-400 text-[10px]"></i> {{ $wali->kampung->nama_kampung }}
                    </span>

                </div>
            </div>
        </div>

        <!-- ================= KANAN: AKSI & TOMBOL ================= -->
        <div
            class="flex items-center justify-between sm:justify-end gap-2 border-t sm:border-none border-zinc-200/60 dark:border-zinc-800 pt-3 sm:pt-0 relative z-10 w-full sm:w-auto mt-1 sm:mt-0">

            @can('lihat wali-murid')
                <!-- Tombol Lihat Anak (Menonjol jika ada anak) -->
                <a href="{{ route('wali-murid.show', $wali->id) }}"
                    class="h-9 px-3.5 rounded-xl flex items-center justify-center text-xs font-black transition-all outline-none shadow-2xs active:scale-95
                    {{ $wali->murids_count > 0
                        ? 'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 hover:bg-primary/20'
                        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                    title="Lihat Anggota Keluarga">
                    <i class="bi bi-people-fill mr-1.5 text-xs"></i> {{ $wali->murids_count }} Murid
                </a>
            @endcan

            <div class="flex items-center gap-1.5">
                @can('update wali-murid')
                    <a href="{{ route('wali-murid.edit', $wali->id) }}"
                        class="w-9 h-9 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Edit Data Keluarga">
                        <i class="bi bi-pencil-fill text-xs"></i>
                    </a>
                @endcan
            </div>

        </div>
    </div>
@empty
    <x-empty-state icon="bi-layers" title="Data Keluarga Masih Kosong" message="Anda belum mengatur Keluarga." />
@endforelse

@if ($walis->hasPages())
    <div class="mt-4 m3-glass-card p-4 sm:p-5 relative z-10">
        {{ $walis->links('vendor.pagination.custom') }}
    </div>
@endif
