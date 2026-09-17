@forelse($users as $user)
    @php
        $roleName = $user->roles->first()->name ?? 'Tanpa Role';

        // Warna badge role (Material 3 tone)
        $roleColors = [
            'administrator' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
            'staff' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
            'petugas-tabungan' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            'petugas-koperasi' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
            'ustadz' => 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20',
            'wali-murid' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20',
        ];
        $roleBadgeClass =
            $roleColors[$roleName] ?? 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20';

        // Ikon role
        $roleIcons = [
            'administrator' => 'bi-shield-check',
            'staff' => 'bi-person-workspace',
            'petugas-tabungan' => 'bi-wallet2',
            'petugas-koperasi' => 'bi-shop',
            'ustadz' => 'bi-mortarboard-fill',
            'wali-murid' => 'bi-people-fill',
        ];
        $roleIcon = $roleIcons[$roleName] ?? 'bi-person';

        // Deteksi foto profil dari relasi Administrator atau Ustadz
        $userPhoto = null;
        if ($user->administrator && $user->administrator->foto) {
            $userPhoto = asset('storage/' . $user->administrator->foto);
        } elseif ($user->ustadz && $user->ustadz->foto) {
            $userPhoto = $user->ustadz->foto_url ?? asset('storage/' . $user->ustadz->foto);
        }

        $isCurrent = auth()->id() === $user->id;
    @endphp

    <div
        class="m3-glass-card p-4 md:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 group relative overflow-hidden transition-all duration-200 shadow-2xs hover:shadow-md hover:border-primary/40 dark:hover:border-primary-dark/40 {{ !$user->is_active ? 'opacity-65 bg-zinc-50/60 dark:bg-zinc-900/30' : '' }}">

        {{-- Kolom Kiri: Identitas Pengguna & Avatar Component --}}
        <div class="flex items-start sm:items-center gap-3.5 relative z-10 w-full lg:w-auto min-w-0">

            {{-- Komponen Avatar --}}
            <div class="relative flex-shrink-0">
                <x-avatar :src="$userPhoto" :name="$user->name" size="lg" shape="squircle" :status="$user->isOnline() ? 'online' : 'offline'"
                    alt="Foto {{ $user->name }}"
                    class="shadow-md border-2 border-primary/20 dark:border-primary-dark/30 transform transition-all duration-300 group-hover:scale-105" />

                {{-- Mini Role Badge Icon di Sudut Avatar --}}
                <span
                    class="absolute -top-1 -left-1 w-5 h-5 rounded-lg bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-zinc-200 dark:border-zinc-700 flex items-center justify-center shadow-xs text-[10px] {{ str_replace(['border-', 'bg-'], 'text-', $roleBadgeClass) }}"
                    title="Role: {{ strtoupper(str_replace('-', ' ', $roleName)) }}">
                    <i class="bi {{ $roleIcon }}"></i>
                </span>
            </div>

            {{-- Detail Informasi User --}}
            <div class="flex-1 min-w-0">
                {{-- Baris 1: Nama Pengguna & Status Badge --}}
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-0.5">
                    <h4
                        class="text-sm md:text-[15px] font-black text-zinc-900 dark:text-white tracking-tight leading-tight truncate max-w-[220px] md:max-w-none">
                        {{ $user->name }}
                    </h4>
                    @if ($isCurrent)
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 inline-flex items-center gap-0.5 shadow-2xs">
                            <i class="bi bi-check-circle-fill text-[8px]"></i> Anda
                        </span>
                    @endif
                    @if (!$user->is_active)
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 shadow-2xs">
                            Nonaktif
                        </span>
                    @endif
                </div>

                {{-- Baris 2: Monospace Username + Role Badge + Tingkat --}}
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    {{-- Username --}}
                    <span class="inline-flex items-center text-xs font-mono font-bold text-zinc-500 dark:text-zinc-400">
                        <span class="text-zinc-400 dark:text-zinc-500 mr-0.5">@</span>{{ $user->username }}
                    </span>

                    <span class="text-zinc-300 dark:text-zinc-700 text-[10px]">•</span>

                    {{-- Role Badge --}}
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border shadow-2xs {{ $roleBadgeClass }}">
                        <i class="bi {{ $roleIcon }} text-[9px]"></i>
                        {{ strtoupper(str_replace('-', ' ', $roleName)) }}
                    </span>

                    {{-- Tingkat Badge (Jika Ada) --}}
                    @if ($user->tingkat)
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shadow-2xs">
                            <i class="bi bi-layers text-[9px]"></i> {{ $user->tingkat->nama_tingkat }}
                        </span>
                    @endif
                </div>

                {{-- Baris 3: Email + Profile Relationship Link --}}
                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1">
                    @if ($user->email)
                        <span
                            class="text-[11px] text-zinc-400 dark:text-zinc-500 font-mono truncate inline-flex items-center gap-1">
                            <i class="bi bi-envelope text-[10px]"></i> {{ $user->email }}
                        </span>
                    @endif

                    @if ($user->administrator)
                        <span
                            class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 inline-flex items-center gap-1"
                            title="Terhubung ke Profil Administrator">
                            <i class="bi bi-link-45deg text-emerald-500 text-xs"></i> Biodata Admin
                        </span>
                    @elseif ($user->ustadz)
                        <span
                            class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 inline-flex items-center gap-1"
                            title="Terhubung ke Profil Ustadz/Guru">
                            <i class="bi bi-link-45deg text-teal-500 text-xs"></i> Biodata Guru
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Status Aktivitas & Tombol Aksi --}}
        <div
            class="flex flex-wrap sm:flex-nowrap items-center justify-between lg:justify-end gap-3 sm:gap-4 relative z-10 w-full lg:w-auto border-t lg:border-none border-zinc-200/60 dark:border-zinc-800 pt-3 lg:pt-0">

            {{-- Status Online & Switch Aktif --}}
            <div class="flex flex-col items-start lg:items-end gap-1 min-w-[110px]">
                @if ($user->isOnline())
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Online
                    </span>
                @else
                    <span
                        class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 inline-flex items-center gap-1 font-mono"
                        title="{{ $user->last_seen_at ? \Carbon\Carbon::parse($user->last_seen_at)->translatedFormat('d M Y H:i') : 'Belum pernah login' }}">
                        <i class="bi bi-clock-history text-[10px]"></i> {{ $user->lastSeenText() }}
                    </span>
                @endif

                {{-- Switch Status Aktif Menggunakan Komponen Toggle --}}
                @if (!$isCurrent)
                    <div class="mt-0.5">
                        <x-toggle :checked="$user->is_active" activeText="Aktif" inactiveText="Nonaktif"
                            onchange="toggleUserStatus({{ $user->id }}, this)" />
                    </div>
                @endif
            </div>

            {{-- Divider Vertikal --}}
            <div class="hidden sm:block w-px h-10 bg-zinc-200/80 dark:bg-zinc-800"></div>

            {{-- Action Buttons Group (M3 Touch Targets) --}}
            <div class="flex items-center gap-1.5">

                {{-- Hubungi WhatsApp --}}
                <a href="{{ route('pengguna.whatsapp', $user->id) }}" target="_blank"
                    class="w-9 h-9 rounded-xl md:rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center hover:bg-emerald-500/20 border border-emerald-500/20 transition-all duration-150 hover:scale-105 active:scale-90 shadow-2xs outline-none"
                    title="Hubungi via WhatsApp">
                    <i class="bi bi-whatsapp text-sm"></i>
                </a>

                {{-- Hak Akses & Role Pengguna (Direct Permissions) --}}
                <a href="{{ route('user-permissions.index', ['user_id' => $user->id]) }}"
                    class="w-9 h-9 rounded-xl md:rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center hover:bg-purple-500/20 border border-purple-500/20 transition-all duration-150 hover:scale-105 active:scale-90 shadow-2xs outline-none"
                    title="Atur Hak Akses Khusus & Role">
                    <i class="bi bi-person-lock text-sm"></i>
                </a>

                {{-- Reset Password --}}
                <button type="button"
                    onclick="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                    class="w-9 h-9 rounded-xl md:rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center hover:bg-amber-500/20 border border-amber-500/20 transition-all duration-150 hover:scale-105 active:scale-90 shadow-2xs outline-none"
                    title="Reset Password Akun">
                    <i class="bi bi-key-fill text-sm"></i>
                </button>

                {{-- Force Logout --}}
                @if (!$isCurrent)
                    <button type="button"
                        onclick="confirmForceLogout({{ $user->id }}, '{{ addslashes($user->name) }}')"
                        class="w-9 h-9 rounded-xl md:rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center hover:bg-orange-500/20 border border-orange-500/20 transition-all duration-150 hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Force Logout (Keluarkan Paksa Sesi)">
                        <i class="bi bi-box-arrow-right text-sm"></i>
                    </button>
                @endif

                {{-- Edit Pengguna --}}
                <button type="button" onclick="openEditUserModal({{ $user->id }})"
                    class="w-9 h-9 rounded-xl md:rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center hover:bg-sky-500/20 border border-sky-500/20 transition-all duration-150 hover:scale-105 active:scale-90 shadow-2xs outline-none"
                    title="Edit Data Pengguna">
                    <i class="bi bi-pencil-square text-sm"></i>
                </button>

                {{-- Hapus Pengguna --}}
                @if (!$isCurrent)
                    <button type="button"
                        onclick="confirmDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')"
                        class="w-9 h-9 rounded-xl md:rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center hover:bg-rose-500/20 border border-rose-500/20 transition-all duration-150 hover:scale-105 active:scale-90 shadow-2xs outline-none"
                        title="Hapus Akun Pengguna">
                        <i class="bi bi-trash3-fill text-sm"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@empty
    {{-- Empty State Component --}}
    <x-empty-state icon="bi-people-fill" title="Data Pengguna Tidak Ditemukan"
        message="Tidak ada akun pengguna yang cocok dengan kriteria pencarian atau filter yang dipilih.">
        <button type="button" onclick="resetAllFilters()"
            class="h-9 px-4 rounded-xl md:rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-black text-zinc-700 dark:text-zinc-300 transition-all inline-flex items-center gap-1.5 active:scale-95 shadow-2xs">
            <i class="bi bi-arrow-counterclockwise text-sm"></i> Reset Filter
        </button>
    </x-empty-state>
@endforelse

{{-- Pagination Links --}}
@if ($users->hasPages())
    <div
        class="mt-4 bg-zinc-50/70 dark:bg-zinc-950/50 p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 relative z-10 transition-colors">
        {{ $users->links() }}
    </div>
@endif
