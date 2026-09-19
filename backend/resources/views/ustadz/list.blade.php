@forelse($ustadzs as $ustadz)
    <div
        class="m3-glass-card p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group relative overflow-hidden transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40">

        <!-- Card Info Section (Compact) -->
        <div class="flex items-center gap-3 md:gap-4 relative z-10 w-full sm:w-auto">

            <!-- Badge Nomor -->
            <span
                class="w-9 h-9 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200 dark:border-zinc-700 flex-shrink-0 shadow-2xs">
                {{ $loop->iteration }}
            </span>

            <!-- Foto Ustadz/Guru (Click to Upload/Change Foto) -->
            @can('update ustadz')
                <a href="{{ route('ustadz.upload-foto', $ustadz->id) }}"
                    class="action-modal w-11 h-11 md:w-12 md:h-12 rounded-xl overflow-hidden flex-shrink-0 border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-2xs relative cursor-pointer group/avatar block"
                    title="Ubah Foto {{ $ustadz->nama_lengkap }}">
                    <img src="{{ $ustadz->foto
                        ? asset('storage/' . $ustadz->foto)
                        : asset($ustadz->jenis_kelamin === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
                        alt="Foto {{ $ustadz->nama_lengkap }}" class="w-full h-full object-cover">
                    <!-- Overlay Ubah Foto -->
                    <div
                        class="absolute inset-0 rounded-xl bg-black/60 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-300">
                        <i class="bi bi-camera-fill text-white text-sm"></i>
                    </div>
                </a>
            @else
                <div
                    class="w-11 h-11 md:w-12 md:h-12 rounded-xl overflow-hidden flex-shrink-0 border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-2xs">
                    <img src="{{ $ustadz->foto
                        ? asset('storage/' . $ustadz->foto)
                        : asset($ustadz->jenis_kelamin === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
                        alt="Foto {{ $ustadz->nama_lengkap }}" class="w-full h-full object-cover">
                </div>
            @endcan

            <div class="flex-1 overflow-hidden">
                <h4
                    class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight mb-1 truncate">
                    {{ $ustadz->jenis_kelamin === 'L' ? 'Ust' : 'Ustd' }}.
                    {{ $ustadz->nama_lengkap }}
                </h4>

                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                    <!-- Kode Badge (Compact Pill) -->
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider shadow-2xs font-mono">
                        Kode: <span class="text-zinc-900 dark:text-white ml-1 font-black">
                            {{ $ustadz->kode_ustadz ?? '-' }}
                        </span>
                    </span>

                    <!-- Phone Badge -->
                    @if ($ustadz->no_hp)
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-primary/10 dark:bg-primary-dark/20 border border-primary/20 text-[10px] font-bold text-primary dark:text-primary-dark tracking-wider shadow-2xs font-mono">
                            <i class="bi bi-telephone-fill text-[9px]"></i>
                            {{ $ustadz->no_hp }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status & Action Buttons Section -->
        <div
            class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 relative z-10 w-full sm:w-auto border-t sm:border-none border-zinc-200/60 dark:border-zinc-800 pt-3 sm:pt-0 mt-1 sm:mt-0">

            <!-- Status Badges Column -->
            <div class="flex flex-col items-start sm:items-end gap-1">

                {{-- Badge Status Aktif/Tidak Aktif --}}
                @if ($ustadz->is_active)
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Aktif
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 shadow-2xs">
                        Tidak Aktif
                    </span>
                @endif

                {{-- Badge Tanda Tangan --}}
                @if ($ustadz->tanda_tangan)
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-2xs">
                        <i class="bi bi-pen-fill mr-1 text-[9px]"></i> TTD Ada
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 shadow-2xs">
                        TTD Belum Ada
                    </span>
                @endif

                {{-- Badge Akun & Verifikasi Email --}}
                @if ($ustadz->user_id)
                    @if ($ustadz->user && !$ustadz->user->hasVerifiedEmail())
                        <span
                            class="inline-flex items-center text-[9px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 rounded shadow-2xs">
                            <i class="bi bi-x-circle-fill mr-1 text-[9px]"></i> Belum Verifikasi
                        </span>
                    @else
                        <span
                            class="inline-flex items-center text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded shadow-2xs">
                            <i class="bi bi-check-circle-fill mr-1 text-[9px]"></i> Terverifikasi
                        </span>
                    @endif
                @else
                    <span
                        class="inline-flex items-center text-[9px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded shadow-2xs">
                        Tanpa Akun
                    </span>
                @endif
            </div>

            <!-- Divider -->
            <div class="hidden sm:block w-px h-10 bg-zinc-200/80 dark:border-zinc-800 mx-1">
            </div>

            <!-- Action Buttons Column -->
            <div class="flex items-center gap-1.5">

                {{-- Tombol Kirim Ulang Verifikasi --}}
                @if ($ustadz->user && !$ustadz->user->hasVerifiedEmail())
                    <form action="{{ route('ustadz.resend-verification', $ustadz->id) }}" method="POST"
                        class="inline m-0 p-0">
                        @csrf
                        <button type="submit"
                            class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center hover:bg-amber-500/20 border border-amber-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                            title="Kirim Ulang Email Verifikasi">
                            <i class="bi bi-envelope-paper-fill text-xs"></i>
                        </button>
                    </form>
                @endif

                @can('update ustadz')
                    <a href="{{ route('ustadz.edit', $ustadz->id) }}"
                        class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-500/20 border border-blue-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Edit Data Ustadz">
                        <i class="bi bi-pencil-fill text-xs"></i>
                    </a>
                @endcan

                @can('update ustadz')
                    <a href="{{ route('ustadz.upload-foto', $ustadz->id) }}"
                        class="action-modal w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center hover:bg-purple-500/20 border border-purple-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Upload / Ubah Foto Profil">
                        <i class="bi bi-camera-fill text-xs"></i>
                    </a>
                @endcan

                @can('update ustadz')
                    <a href="{{ route('ustadz.signature', $ustadz->id) }}"
                        class="action-modal w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center hover:bg-emerald-500/20 border border-emerald-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Buat TTD Digital">
                        <i class="bi bi-pen-fill text-xs"></i>
                    </a>
                @endcan

            </div>
        </div>
    </div>
@empty
    <!-- Custom Empty State -->
    <x-empty-state icon="bi-layers" title="Data Ustadz/Guru Masih Kosong" message="Anda belum mengatur Ustadz/Guru." />
@endforelse

<!-- Pagination -->
@if ($ustadzs->hasPages())
    <div class="mt-4 m3-glass-card p-4 rounded-2xl relative z-10 shadow-2xs">
        {{ $ustadzs->links('vendor.pagination.custom') }}
    </div>
@endif
