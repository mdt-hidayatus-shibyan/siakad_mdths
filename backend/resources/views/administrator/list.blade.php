@forelse($administrators as $administrator)
    <div
        class="m3-glass-card p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group relative overflow-hidden transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40">

        <!-- Card Info Section (Compact) -->
        <div class="flex items-center gap-3 md:gap-4 relative z-10 w-full sm:w-auto">

            <!-- Badge Nomor -->
            <span
                class="w-9 h-9 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-xl text-xs font-black border border-zinc-200 dark:border-zinc-700 flex-shrink-0 shadow-2xs">
                {{ $loop->iteration }}
            </span>

            <!-- Foto Administrator -->
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-xl overflow-hidden flex-shrink-0 border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-2xs">
                <img src="{{ $administrator->foto
                    ? asset('storage/' . $administrator->foto)
                    : asset($administrator->jenis_kelamin === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
                    alt="Foto {{ $administrator->nama_lengkap }}" class="w-full h-full object-cover">
            </div>

            <div class="flex-1 overflow-hidden">
                <h4
                    class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight truncate mb-1">
                    {{ $administrator->nama_lengkap }}
                </h4>

                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                    <!-- Role Badge -->
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider shadow-2xs">
                        <span class="text-zinc-900 dark:text-white font-black">
                            {{ $administrator->user?->roles?->first()?->name ?? '-' }}
                        </span>
                        @if ($administrator->tingkat)
                            <span class="ml-1 text-primary dark:text-primary-dark font-black">
                                ({{ $administrator->tingkat->kode_tingkat }})
                            </span>
                        @endif
                    </span>

                    <!-- Phone Badge -->
                    @if ($administrator->no_hp)
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-black text-emerald-600 dark:text-emerald-400 font-mono shadow-2xs">
                            <i class="bi bi-whatsapp text-[9px]"></i>
                            {{ $administrator->no_hp }}
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
                @if ($administrator->is_active)
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
                @if ($administrator->tanda_tangan)
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-2xs">
                        <i class="bi bi-pen-fill mr-1 text-[8px]"></i> TTD Ada
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 shadow-2xs">
                        Belum TTD
                    </span>
                @endif

                {{-- Badge Akun & Verifikasi Email --}}
                @if ($administrator->user_id)
                    @if ($administrator->user && !$administrator->user->hasVerifiedEmail())
                        <span
                            class="inline-flex items-center text-[9px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 rounded shadow-2xs">
                            <i class="bi bi-x-circle-fill mr-1 text-[8px]"></i> Belum Verifikasi
                        </span>
                    @else
                        <span
                            class="inline-flex items-center text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded shadow-2xs">
                            <i class="bi bi-check-circle-fill mr-1 text-[8px]"></i> Email Terverifikasi
                        </span>
                    @endif
                @else
                    <span
                        class="inline-flex items-center text-[9px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded shadow-2xs">
                        Tanpa Akun
                    </span>
                @endif
            </div>

            <!-- Divider -->
            <div class="hidden sm:block w-px h-8 bg-zinc-200/80 dark:border-zinc-800 mx-1"></div>

            <!-- Action Buttons Column -->
            <div class="flex items-center gap-1.5">

                {{-- Tombol Kirim Ulang Verifikasi --}}
                @if ($administrator->user && !$administrator->user->hasVerifiedEmail())
                    <form action="{{ route('administrator.resend-verification', $administrator->id) }}" method="POST"
                        class="inline m-0 p-0">
                        @csrf
                        <button type="submit"
                            class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center hover:bg-amber-500/20 border border-amber-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                            title="Kirim Ulang Email Verifikasi">
                            <i class="bi bi-envelope-paper-fill text-xs"></i>
                        </button>
                    </form>
                @endif

                @can('update administrator')
                    <a href="{{ route('administrator.edit', $administrator->id) }}"
                        class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-500/20 border border-blue-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Edit Administrator">
                        <i class="bi bi-pencil-fill text-xs"></i>
                    </a>
                @endcan

                @can('update administrator')
                    <a href="{{ route('administrator.signature', $administrator->id) }}"
                        class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center hover:bg-emerald-500/20 border border-emerald-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Buat TTD Digital">
                        <i class="bi bi-pen-fill text-xs"></i>
                    </a>
                @endcan

            </div>
        </div>
    </div>
@empty
    <!-- Custom Empty State for Administrator -->
    <x-empty-state icon="bi-person-vcard" title="Belum Ada Data Administrator"
        message="Silakan tambahkan data tenaga administrator dan staff madrasah." />
@endforelse

<!-- Pagination -->
@if ($administrators->hasPages())
    <div
        class="mt-4 bg-zinc-50/70 dark:bg-zinc-950/50 p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 relative z-10 transition-colors">
        {{ $administrators->links('vendor.pagination.custom') }}
    </div>
@endif
