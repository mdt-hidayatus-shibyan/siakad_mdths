@section('title', 'Hak Akses & Role Pengguna')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 relative z-10">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 inline-flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-person-gear text-xs"></i>
                    <span>Sistem & Hak Akses Pengguna</span>
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Hak Akses & Role Pengguna
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Kelola penetapan peran (role) dan konfigurasi izin spesifik (direct permission) per akun pengguna
                sistem.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('roles.index') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all flex items-center gap-2 shadow-2xs">
                <i class="bi bi-key-fill text-xs text-primary dark:text-primary-dark"></i>
                <span>Matriks Role (RBAC)</span>
            </a>
            <a href="{{ route('pengguna.index') }}"
                class="m3-btn-primary px-3.5 py-2 text-xs font-black shadow-2xs flex items-center gap-2">
                <i class="bi bi-people-fill text-xs"></i>
                <span>Daftar Akun Pengguna</span>
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 md:gap-6 relative z-10">

        <!-- ================= SIDEBAR: DAFTAR PENGGUNA ================= -->
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="m3-glass-card p-4 md:p-5 sticky top-6 rounded-3xl shadow-2xs">

                <!-- Header Sidebar -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <div>
                        <h3 class="font-black text-zinc-900 dark:text-white text-base tracking-tight">
                            Pilih Pengguna
                        </h3>
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mt-0.5">
                            {{ $users->count() }} Akun Terdaftar
                        </p>
                    </div>
                </div>

                <!-- Filter & Search Toolbar -->
                <div class="space-y-2 mb-3.5">
                    <!-- Search Input -->
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
                        <input type="text" id="searchUserSidebar" placeholder="Cari nama/username..."
                            class="m3-input-glass w-full !pl-8 !pr-7 text-xs font-bold !py-1.5 rounded-xl">
                        <button type="button" id="btnClearUserSearch" onclick="clearUserSearch()"
                            class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-rose-500">
                            <i class="bi bi-x-lg text-[10px]"></i>
                        </button>
                    </div>

                    <!-- Role Filter -->
                    <select id="filterRoleSidebar" onchange="filterUserByRole(this.value)"
                        class="m3-input-glass w-full text-xs font-bold cursor-pointer !py-1.5 rounded-xl">
                        <option value="">-- Semua Peran (Role) --</option>
                        @foreach ($allRoles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                                {{ strtoupper(str_replace('-', ' ', $r->name)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- List Pengguna -->
                <div class="space-y-1.5 max-h-[62vh] overflow-y-auto custom-scrollbar pr-1" id="userSidebarList">
                    @forelse($users as $user)
                        @php
                            $isSelected = $activeUser && $activeUser->id == $user->id;
                            $primaryRole = $user->roles->first()->name ?? 'Tanpa Role';
                            $isSuper = $user->hasRole('administrator');

                            $userPhoto = null;
                            if ($user->administrator && $user->administrator->foto) {
                                $userPhoto = asset('storage/' . $user->administrator->foto);
                            } elseif ($user->ustadz && $user->ustadz->foto) {
                                $userPhoto = $user->ustadz->foto_url ?? asset('storage/' . $user->ustadz->foto);
                            }

                            // Hitung jumlah direct permissions yang dimiliki
                            $directCount = $user->permissions->count();
                        @endphp

                        <div class="user-sidebar-item relative flex items-center justify-between p-2.5 rounded-2xl border transition-all duration-200 group
                            {{ $isSelected
                                ? 'bg-primary/10 dark:bg-primary-dark/15 text-primary dark:text-primary-dark border-primary/30 dark:border-primary-dark/35 shadow-2xs ring-1 ring-primary/20'
                                : 'bg-white/40 dark:bg-zinc-900/40 text-zinc-600 dark:text-zinc-400 border-zinc-200/60 dark:border-zinc-800 hover:bg-white/70 dark:hover:bg-zinc-800/80 hover:text-zinc-900 dark:hover:text-white' }}"
                            data-name="{{ strtolower($user->name) }}" data-username="{{ strtolower($user->username) }}"
                            data-role="{{ $primaryRole }}">

                            <a href="{{ route('user-permissions.index', ['user_id' => $user->id, 'role' => request('role'), 'search' => request('search')]) }}"
                                class="flex-1 flex items-center gap-2.5 truncate outline-none">

                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <x-avatar :src="$userPhoto" :name="$user->name" size="sm" shape="squircle"
                                        :status="$user->isOnline() ? 'online' : 'offline'" class="shadow-xs border border-primary/20" />
                                </div>

                                <!-- User Info -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <h4
                                            class="text-xs font-black truncate {{ $isSelected ? 'text-primary dark:text-primary-dark' : 'text-zinc-900 dark:text-white' }}">
                                            {{ $user->name }}
                                        </h4>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] mt-0.5">
                                        <span class="font-mono text-zinc-400">@ {{ $user->username }}</span>
                                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                        <span
                                            class="font-bold uppercase text-[9px] text-zinc-500 dark:text-zinc-400 truncate">
                                            {{ str_replace('-', ' ', $primaryRole) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Direct Permission Badge / Super Badge -->
                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    @if ($isSuper)
                                        <span
                                            class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            SUPER
                                        </span>
                                    @elseif($directCount > 0)
                                        <span
                                            class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20"
                                            title="{{ $directCount }} Izin Khusus Aktif">
                                            +{{ $directCount }} Izin
                                        </span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @empty
                        <div
                            class="text-center py-8 bg-white/40 dark:bg-black/40 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800">
                            <i class="bi bi-people text-2xl text-zinc-400 mb-1.5 block"></i>
                            <p class="text-xs font-black text-zinc-400 uppercase tracking-wider">Tidak ada pengguna</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        <!-- ================= MAIN CONTENT: MATRIKS HAK AKSES USER ================= -->
        <div class="lg:col-span-8 xl:col-span-9">

            @if ($activeUser)
                @php
                    $isSuperAdminUser = $activeUser->hasRole('administrator');
                    $groupedMatrix = $matrixMenus->groupBy('category');

                    $activeUserPhoto = null;
                    if ($activeUser->administrator && $activeUser->administrator->foto) {
                        $activeUserPhoto = asset('storage/' . $activeUser->administrator->foto);
                    } elseif ($activeUser->ustadz && $activeUser->ustadz->foto) {
                        $activeUserPhoto =
                            $activeUser->ustadz->foto_url ?? asset('storage/' . $activeUser->ustadz->foto);
                    }
                @endphp

                <!-- 1. KARTU PROFIL & PENETAPAN PERAN (ROLE ASSIGNMENT) -->
                <div
                    class="m3-glass-card p-5 rounded-3xl mb-5 shadow-2xs border border-zinc-200/80 dark:border-zinc-800 space-y-4">

                    <!-- Baris Atas: Identitas Pengguna -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200/60 dark:border-zinc-800/80">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <x-avatar :src="$activeUserPhoto" :name="$activeUser->name" size="lg" shape="squircle"
                                :status="$activeUser->isOnline() ? 'online' : 'offline'" class="shadow-md border-2 border-primary/20 flex-shrink-0" />

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3
                                        class="text-base sm:text-lg font-black text-zinc-900 dark:text-white tracking-tight truncate">
                                        {{ $activeUser->name }}
                                    </h3>
                                    @if ($activeUser->isOnline())
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 shadow-2xs">
                                            Online
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400 bg-zinc-500/10 border border-zinc-500/20 shadow-2xs">
                                            Offline
                                        </span>
                                    @endif
                                    @if (!$activeUser->is_active)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20 shadow-2xs">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                    <span class="font-mono font-bold text-zinc-500 dark:text-zinc-400">
                                        <span class="text-zinc-400">@</span>{{ $activeUser->username }}
                                    </span>
                                    @if ($activeUser->email)
                                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                        <span class="font-mono text-zinc-400 text-[11px] truncate">
                                            {{ $activeUser->email }}
                                        </span>
                                    @endif
                                    @if ($activeUser->tingkat)
                                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                        <span
                                            class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                            {{ $activeUser->tingkat->nama_tingkat }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Pintas ke Manajemen Akun -->
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('pengguna.index', ['search' => $activeUser->username]) }}"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-all flex items-center gap-1.5 shadow-2xs"
                                title="Buka data profil di Manajemen Pengguna">
                                <i class="bi bi-pencil-square text-xs text-primary dark:text-primary-dark"></i>
                                <span>Edit Akun</span>
                            </a>
                        </div>
                    </div>

                    <!-- Baris Bawah: Penetapan Peran (Role Assignment Form) -->
                    <form id="formSyncRoles" action="{{ route('user-permissions.sync-roles', $activeUser->id) }}"
                        method="POST">
                        @csrf
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="bi bi-shield-check text-primary dark:text-primary-dark"></i>
                                    Peran (Role) Pengguna:
                                </span>
                                <span class="text-[10px] text-zinc-400 font-medium hidden sm:inline">
                                    (Centang role untuk menetapkan peran akun ini)
                                </span>
                            </div>
                            <button type="submit" id="btnSaveRoles"
                                class="px-3.5 py-1.5 text-xs font-black rounded-xl m3-btn-primary shadow-2xs transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
                                <i class="bi bi-check2-circle"></i> Simpan Role
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2">
                            @foreach ($allRoles as $roleOption)
                                @php
                                    $hasThisRole = in_array($roleOption->name, $userRoleNames);
                                @endphp
                                <label
                                    class="flex items-center gap-2 p-2 rounded-xl border transition-all cursor-pointer text-xs font-bold select-none
                                    {{ $hasThisRole
                                        ? 'bg-primary/10 text-primary dark:text-primary-dark border-primary/30 ring-1 ring-primary/20 shadow-2xs'
                                        : 'bg-zinc-50/70 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 border-zinc-200/70 dark:border-zinc-800 hover:bg-white dark:hover:bg-zinc-800/80 hover:text-zinc-900 dark:hover:text-white' }}">
                                    <input type="checkbox" name="roles[]" value="{{ $roleOption->name }}"
                                        class="rounded border-zinc-300 dark:border-zinc-700 text-primary focus:ring-primary h-3.5 w-3.5"
                                        {{ $hasThisRole ? 'checked' : '' }}>
                                    <span class="truncate uppercase text-[10px] tracking-tight">
                                        {{ str_replace('-', ' ', $roleOption->name) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </form>

                </div>

                <!-- 2. BANNER & LEGEND PENJELASAN HAK AKSES -->
                @if ($isSuperAdminUser)
                    <div
                        class="p-4 mb-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3 shadow-2xs">
                        <i class="bi bi-shield-fill-check text-amber-600 dark:text-amber-400 text-lg mt-0.5"></i>
                        <div>
                            <h4 class="text-xs font-black text-amber-800 dark:text-amber-300 uppercase tracking-wider">
                                Hak Akses Penuh Super Admin
                            </h4>
                            <p
                                class="text-[11px] font-medium text-amber-700 dark:text-amber-400/80 mt-0.5 leading-relaxed">
                                Pengguna dengan peran <span class="font-bold">administrator</span> memiliki wewenang
                                tak
                                terbatas ke seluruh fitur dan modul sistem secara otomatis melalui <em>Gate
                                    Authorization Bypass</em>.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Legend Petunjuk Visual Izin -->
                <div
                    class="m3-glass-card p-3.5 rounded-2xl mb-4 border border-zinc-200/80 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3 text-[11px] shadow-2xs">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-[10px]">
                            Indikator Status:
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span
                                class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                <i class="bi bi-shield-check mr-0.5"></i> Role
                            </span>
                            <span class="text-zinc-600 dark:text-zinc-300 font-semibold">Diwarisi dari Role</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span
                                class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                                <i class="bi bi-person-check-fill mr-0.5"></i> Khusus
                            </span>
                            <span class="text-zinc-600 dark:text-zinc-300 font-semibold">Izin Khusus Pengguna</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded-full bg-zinc-200 dark:bg-zinc-700 inline-block"></span>
                            <span class="text-zinc-400 font-medium">Nonaktif</span>
                        </div>
                    </div>

                    <!-- Indikator Total Izin -->
                    <div class="flex items-center gap-2 font-mono text-[11px] font-bold">
                        <span class="text-zinc-400">Total Akses Efektif:</span>
                        <span
                            class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary dark:text-primary-dark border border-primary/20"
                            id="effectivePermCount">
                            {{ count($effectivePermissions) }} Izin
                        </span>
                    </div>
                </div>

                <!-- 3. MATRIKS HAK AKSES CONTAINER -->
                <div
                    class="m3-glass-card rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">

                    <!-- Header Matriks & Toolbar Aksi Cepat -->
                    <div
                        class="px-5 py-4 bg-zinc-100/60 dark:bg-zinc-800/60 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3
                                    class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                                    Matriks Hak Akses Khusus:
                                </h3>
                                <span
                                    class="px-2.5 py-0.5 rounded-xl bg-primary/10 dark:bg-primary-dark/20 border border-primary/20 text-primary dark:text-primary-dark text-xs font-black">
                                    {{ $activeUser->name }}
                                </span>
                            </div>
                            <p class="text-[10px] font-bold text-zinc-400 mt-0.5 uppercase tracking-wider">
                                Centang sakelar untuk mengaktifkan izin langsung (Direct Permission) untuk pengguna ini.
                            </p>
                        </div>

                        <!-- Search Box in Matrix -->
                        <div class="relative w-full sm:w-56">
                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
                            <input type="text" id="filterMatrixInput" placeholder="Cari modul / izin..."
                                class="m3-input-glass w-full !pl-8 text-xs font-bold !py-1.5 rounded-xl">
                        </div>
                    </div>

                    <!-- Global Bulk Actions Toolbar -->
                    @if (!$isSuperAdminUser)
                        <div
                            class="px-5 py-2.5 bg-zinc-50/50 dark:bg-zinc-900/50 border-b border-zinc-200/60 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-2">
                            <span
                                class="text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Aksi Izin Khusus Masal:
                            </span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" onclick="bulkToggleAll(true)"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-primary/10 text-primary dark:text-primary-dark hover:bg-primary/20 border border-primary/20 transition-all shadow-2xs">
                                    <i class="bi bi-check-all mr-1"></i> Pilih Semua
                                </button>
                                <button type="button" onclick="bulkToggleReadOnly()"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all shadow-2xs">
                                    <i class="bi bi-eye mr-1"></i> Hanya Baca (Read)
                                </button>
                                <button type="button" onclick="copyFromRole()"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 transition-all shadow-2xs"
                                    title="Salin semua izin dari peran aktif ke izin khusus">
                                    <i class="bi bi-copy mr-1"></i> Salin Izin Role
                                </button>
                                <button type="button" onclick="resetToDefaultRole()"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition-all shadow-2xs"
                                    title="Hapus semua izin khusus dan gunakan izin default role">
                                    <i class="bi bi-arrow-counterclockwise mr-1"></i> Reset ke Default Role
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Form Matriks Akses -->
                    <form id="formMatriksAkses"
                        action="{{ route('user-permissions.give-permissions', $activeUser->id) }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left text-xs border-collapse" id="matrixTable">
                                <thead
                                    class="bg-zinc-100/70 dark:bg-zinc-800/70 border-b border-zinc-200/80 dark:border-zinc-800">
                                    <tr
                                        class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <th
                                            class="py-3 px-4 border-r border-zinc-200/80 dark:border-zinc-800 w-2/5 min-w-[220px]">
                                            Menu / Modul Sistem
                                        </th>
                                        <th class="py-3 px-4 min-w-[340px]">
                                            Tindakan & Hak Akses (Permissions)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800 bg-transparent">

                                    @forelse($groupedMatrix as $categoryName => $catMenus)
                                        <!-- HEADER KATEGORI -->
                                        <tr
                                            class="category-header-row bg-zinc-100/90 dark:bg-zinc-800/90 border-y border-zinc-200 dark:border-zinc-700">
                                            <td colspan="2" class="py-2.5 px-4">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-[11px] font-black text-zinc-700 dark:text-zinc-200 uppercase tracking-widest flex items-center gap-2">
                                                        <i
                                                            class="bi bi-folder2-open text-primary dark:text-primary-dark"></i>
                                                        {{ $categoryName ?: 'UMUM' }}
                                                    </span>
                                                    @if (!$isSuperAdminUser)
                                                        <div class="flex items-center gap-1">
                                                            <button type="button"
                                                                onclick="bulkCategoryToggle(this, true)"
                                                                class="px-2 py-0.5 text-[9px] font-bold rounded bg-primary/10 text-primary dark:text-primary-dark hover:bg-primary/20">
                                                                Semua
                                                            </button>
                                                            <button type="button"
                                                                onclick="bulkCategoryToggle(this, false)"
                                                                class="px-2 py-0.5 text-[9px] font-bold rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-300">
                                                                Batal
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        @foreach ($catMenus as $menu)
                                            <!-- BARIS MENU UTAMA -->
                                            <tr class="matrix-row bg-zinc-50/30 dark:bg-zinc-900/30 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50 transition-colors"
                                                data-search="{{ strtolower($menu->name) }}">
                                                <td
                                                    class="py-3 px-4 border-l-[3px] border-primary border-r border-zinc-200/80 dark:border-zinc-800 align-top">
                                                    <div class="flex items-center gap-2.5">
                                                        <div
                                                            class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary dark:text-primary-dark border border-primary/20 text-xs shrink-0">
                                                            <i class="bi {{ $menu->icon ?? 'bi-folder-fill' }}"></i>
                                                        </div>
                                                        <div class="overflow-hidden">
                                                            <span
                                                                class="font-black text-zinc-900 dark:text-white text-xs tracking-tight block truncate">
                                                                {{ $menu->name }}
                                                            </span>
                                                            @if ($menu->url && $menu->url !== '#')
                                                                <span
                                                                    class="text-[9px] font-mono text-zinc-400 block truncate">
                                                                    {{ $menu->url }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 align-top">
                                                    <div class="flex flex-wrap gap-2.5">
                                                        @forelse($menu->permissions as $perm)
                                                            @php
                                                                $isFromRole = in_array($perm->name, $rolePermissions);
                                                                $isDirect = in_array($perm->name, $directPermissions);
                                                                $isChecked = $isDirect || $isSuperAdminUser;
                                                            @endphp
                                                            <div
                                                                class="inline-flex items-center gap-2 p-1.5 px-2.5 rounded-xl border transition-all
                                                                {{ $isDirect
                                                                    ? 'bg-sky-500/10 dark:bg-sky-500/15 border-sky-500/30 shadow-2xs'
                                                                    : ($isFromRole
                                                                        ? 'bg-emerald-500/5 dark:bg-emerald-500/10 border-emerald-500/25'
                                                                        : 'bg-zinc-50/60 dark:bg-zinc-900/40 border-zinc-200/60 dark:border-zinc-800') }}">
                                                                <label
                                                                    class="relative inline-flex items-center cursor-pointer group"
                                                                    title="Izin: {{ $perm->name }} ({{ $isFromRole ? 'Aktif dari Role' : ($isDirect ? 'Izin Khusus' : 'Nonaktif') }})">
                                                                    <input type="checkbox" name="permissions[]"
                                                                        value="{{ $perm->name }}"
                                                                        class="sr-only peer perm-checkbox"
                                                                        data-perm="{{ $perm->name }}"
                                                                        {{ $isChecked ? 'checked' : '' }}
                                                                        {{ $isSuperAdminUser ? 'disabled' : '' }}>
                                                                    <div
                                                                        class="relative w-7 h-3.5 bg-zinc-200 dark:bg-zinc-700 rounded-full peer peer-checked:after:translate-x-3.5 after:content-[''] after:absolute after:top-[1.5px] after:left-[1.5px] after:bg-white after:rounded-full after:h-2.5 after:w-2.5 after:transition-all peer-checked:bg-primary dark:peer-checked:bg-primary-dark transition-colors">
                                                                    </div>
                                                                    <span
                                                                        class="ml-2 text-[10px] font-bold text-zinc-700 dark:text-zinc-300 peer-checked:text-primary dark:peer-checked:text-primary-dark transition-colors">
                                                                        {{ ucwords(str_replace(['-', '_', '.'], ' ', $perm->name)) }}
                                                                    </span>
                                                                </label>

                                                                @if ($isFromRole)
                                                                    <span
                                                                        class="px-1.5 py-0.5 rounded-md text-[8px] font-black bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-0.5"
                                                                        title="Aktif otomatis dari Role Pengguna">
                                                                        <i class="bi bi-shield-check text-[8px]"></i>
                                                                        Role
                                                                    </span>
                                                                @elseif ($isDirect)
                                                                    <span
                                                                        class="px-1.5 py-0.5 rounded-md text-[8px] font-black bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/20 inline-flex items-center gap-0.5"
                                                                        title="Izin khusus langsung pengguna">
                                                                        <i
                                                                            class="bi bi-person-check-fill text-[8px]"></i>
                                                                        Khusus
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @empty
                                                            <span
                                                                class="text-[9px] font-semibold text-zinc-400 italic">--
                                                                Sub-menu di bawah --</span>
                                                        @endforelse
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- BARIS SUB-MENU -->
                                            @foreach ($menu->subMenus as $subMenu)
                                                <tr class="matrix-row hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors"
                                                    data-search="{{ strtolower($menu->name . ' ' . $subMenu->name) }}">
                                                    <td
                                                        class="py-2.5 px-4 pl-10 relative border-r border-zinc-200/80 dark:border-zinc-800 align-top">
                                                        <div
                                                            class="absolute left-6 top-0 bottom-1/2 w-px bg-zinc-300 dark:bg-zinc-700">
                                                        </div>
                                                        <div
                                                            class="absolute left-6 top-1/2 w-3 h-px bg-zinc-300 dark:bg-zinc-700">
                                                        </div>

                                                        <div class="flex items-center gap-2 relative z-10">
                                                            <i
                                                                class="bi {{ $subMenu->icon ?? 'bi-circle' }} text-zinc-400 text-xs shrink-0"></i>
                                                            <div class="overflow-hidden">
                                                                <span
                                                                    class="font-bold text-zinc-800 dark:text-zinc-200 text-[11px] tracking-tight block truncate">
                                                                    {{ $subMenu->name }}
                                                                </span>
                                                                @if ($subMenu->url && $subMenu->url !== '#')
                                                                    <span
                                                                        class="text-[9px] font-mono text-zinc-400 block truncate">
                                                                        {{ $subMenu->url }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-2.5 px-4 align-top">
                                                        <div class="flex flex-wrap gap-2.5">
                                                            @forelse($subMenu->permissions as $subPerm)
                                                                @php
                                                                    $isFromRole = in_array(
                                                                        $subPerm->name,
                                                                        $rolePermissions,
                                                                    );
                                                                    $isDirect = in_array(
                                                                        $subPerm->name,
                                                                        $directPermissions,
                                                                    );
                                                                    $isChecked = $isDirect || $isSuperAdminUser;
                                                                @endphp
                                                                <div
                                                                    class="inline-flex items-center gap-2 p-1.5 px-2.5 rounded-xl border transition-all
                                                                    {{ $isDirect
                                                                        ? 'bg-sky-500/10 dark:bg-sky-500/15 border-sky-500/30 shadow-2xs'
                                                                        : ($isFromRole
                                                                            ? 'bg-emerald-500/5 dark:bg-emerald-500/10 border-emerald-500/25'
                                                                            : 'bg-zinc-50/60 dark:bg-zinc-900/40 border-zinc-200/60 dark:border-zinc-800') }}">
                                                                    <label
                                                                        class="relative inline-flex items-center cursor-pointer group"
                                                                        title="Izin: {{ $subPerm->name }} ({{ $isFromRole ? 'Aktif dari Role' : ($isDirect ? 'Izin Khusus' : 'Nonaktif') }})">
                                                                        <input type="checkbox" name="permissions[]"
                                                                            value="{{ $subPerm->name }}"
                                                                            class="sr-only peer perm-checkbox"
                                                                            data-perm="{{ $subPerm->name }}"
                                                                            {{ $isChecked ? 'checked' : '' }}
                                                                            {{ $isSuperAdminUser ? 'disabled' : '' }}>
                                                                        <div
                                                                            class="relative w-7 h-3.5 bg-zinc-200 dark:bg-zinc-700 rounded-full peer peer-checked:after:translate-x-3.5 after:content-[''] after:absolute after:top-[1.5px] after:left-[1.5px] after:bg-white after:rounded-full after:h-2.5 after:w-2.5 after:transition-all peer-checked:bg-primary dark:peer-checked:bg-primary-dark transition-colors">
                                                                        </div>
                                                                        <span
                                                                            class="ml-2 text-[10px] font-bold text-zinc-700 dark:text-zinc-300 peer-checked:text-primary dark:peer-checked:text-primary-dark transition-colors">
                                                                            {{ ucwords(str_replace(['-', '_', '.'], ' ', $subPerm->name)) }}
                                                                        </span>
                                                                    </label>

                                                                    @if ($isFromRole)
                                                                        <span
                                                                            class="px-1.5 py-0.5 rounded-md text-[8px] font-black bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-0.5"
                                                                            title="Aktif otomatis dari Role Pengguna">
                                                                            <i
                                                                                class="bi bi-shield-check text-[8px]"></i>
                                                                            Role
                                                                        </span>
                                                                    @elseif ($isDirect)
                                                                        <span
                                                                            class="px-1.5 py-0.5 rounded-md text-[8px] font-black bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/20 inline-flex items-center gap-0.5"
                                                                            title="Izin khusus langsung pengguna">
                                                                            <i
                                                                                class="bi bi-person-check-fill text-[8px]"></i>
                                                                            Khusus
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            @empty
                                                                <span
                                                                    class="text-[9px] font-semibold text-zinc-400 italic">--
                                                                    Tidak ada aksi --</span>
                                                            @endforelse
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-10">
                                                <x-empty-state icon="bi-menu-button-wide-fill" title="Belum Ada Menu"
                                                    message="Silakan buat menu terlebih dahulu." />
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                </div>
            @else
                <!-- State: Belum Pilih User -->
                <div
                    class="m3-glass-card rounded-3xl p-10 text-center flex flex-col items-center justify-center min-h-[400px] shadow-2xs">
                    <x-empty-state icon="bi-person-lock" title="Pilih Akun Pengguna"
                        message="Silakan pilih salah satu pengguna di daftar sebelah kiri untuk memuat konfigurasi hak akses khusus." />
                </div>
            @endif

        </div>
    </div>

    @push('script')
        <script>
            // 1. FILTER SIDEBAR USERS (SEARCH & ROLE)
            const searchUserSidebar = document.getElementById('searchUserSidebar');
            const btnClearUserSearch = document.getElementById('btnClearUserSearch');
            const filterRoleSidebar = document.getElementById('filterRoleSidebar');

            if (searchUserSidebar) {
                searchUserSidebar.addEventListener('input', function() {
                    const q = this.value.toLowerCase().trim();
                    if (btnClearUserSearch) {
                        btnClearUserSearch.classList.toggle('hidden', q.length === 0);
                    }
                    applySidebarFilter();
                });
            }

            function clearUserSearch() {
                if (searchUserSidebar) {
                    searchUserSidebar.value = '';
                    if (btnClearUserSearch) btnClearUserSearch.classList.add('hidden');
                    applySidebarFilter();
                }
            }

            function filterUserByRole(role) {
                applySidebarFilter();
            }

            function applySidebarFilter() {
                const q = searchUserSidebar ? searchUserSidebar.value.toLowerCase().trim() : '';
                const role = filterRoleSidebar ? filterRoleSidebar.value.toLowerCase().trim() : '';
                const items = document.querySelectorAll('.user-sidebar-item');

                items.forEach(item => {
                    const name = item.getAttribute('data-name') || '';
                    const username = item.getAttribute('data-username') || '';
                    const userRole = item.getAttribute('data-role') || '';

                    const matchText = !q || name.includes(q) || username.includes(q);
                    const matchRole = !role || userRole.toLowerCase() === role;

                    item.style.display = (matchText && matchRole) ? 'flex' : 'none';
                });
            }

            // 2. FILTER SEARCH DI MATRIKS MODUL
            const filterMatrixInput = document.getElementById('filterMatrixInput');
            if (filterMatrixInput) {
                filterMatrixInput.addEventListener('input', function() {
                    const q = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.matrix-row');
                    const categoryRows = document.querySelectorAll('.category-header-row');

                    if (!q) {
                        rows.forEach(r => r.style.display = '');
                        categoryRows.forEach(c => c.style.display = '');
                        return;
                    }

                    rows.forEach(r => {
                        const searchData = r.getAttribute('data-search') || '';
                        const perms = Array.from(r.querySelectorAll('.perm-checkbox')).map(cb => cb
                            .getAttribute('data-perm') || '').join(' ');
                        const matched = searchData.includes(q) || perms.includes(q);
                        r.style.display = matched ? '' : 'none';
                    });
                });
            }

            // 3. AUTO-SAVE VIA AJAX SAAT SAKELAR IZIN DIUBAH
            const formMatriksAkses = document.getElementById('formMatriksAkses');
            if (formMatriksAkses) {
                formMatriksAkses.addEventListener('change', function(e) {
                    if (e.target.classList.contains('perm-checkbox')) {
                        savePermissionsAjax();
                    }
                });
            }

            let saveTimeout = null;

            function savePermissionsAjax() {
                if (!formMatriksAkses) return;

                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    const formData = new FormData(formMatriksAkses);

                    fetch(formMatriksAkses.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showFloatingToast(data.message || 'Hak akses berhasil disimpan!', 'success');
                            } else {
                                showFloatingToast(data.message || 'Gagal menyimpan hak akses.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            showFloatingToast('Koneksi terputus saat menyimpan hak akses.', 'error');
                        });
                }, 250);
            }

            // 4. BULK ACTIONS: TOGGLE ALL / READ-ONLY / CATEGORY
            function bulkToggleAll(checked) {
                const checkboxes = document.querySelectorAll('.perm-checkbox:not(:disabled)');
                checkboxes.forEach(cb => cb.checked = checked);
                savePermissionsAjax();
            }

            function bulkToggleReadOnly() {
                const checkboxes = document.querySelectorAll('.perm-checkbox:not(:disabled)');
                checkboxes.forEach(cb => {
                    const permName = cb.getAttribute('data-perm') || '';
                    cb.checked = permName.startsWith('read ');
                });
                savePermissionsAjax();
            }

            function bulkCategoryToggle(btn, checked) {
                const tr = btn.closest('tr');
                let next = tr.nextElementSibling;
                const checkboxes = [];

                while (next && !next.classList.contains('category-header-row')) {
                    next.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => checkboxes.push(cb));
                    next = next.nextElementSibling;
                }

                checkboxes.forEach(cb => cb.checked = checked);
                savePermissionsAjax();
            }

            // 5. SALIN IZIN DARI ROLE
            function copyFromRole() {
                @if ($activeUser)
                    Swal.fire({
                        title: 'Salin Izin dari Role?',
                        text: 'Semua hak akses yang saat ini diwarisi dari peran pengguna akan dijadikan sebagai izin khusus (direct permissions).',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Salin Sekarang',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'm3-btn-primary px-4 py-2 text-xs font-black rounded-xl',
                            cancelButton: 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 px-4 py-2 text-xs font-bold rounded-xl mr-2'
                        },
                        buttonsStyling: false
                    }).then((res) => {
                        if (res.isConfirmed) {
                            fetch("{{ route('user-permissions.copy-role-permissions', $activeUser->id) }}", {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            .getAttribute('content'),
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    Swal.fire({
                                        title: 'Berhasil Disalin!',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => location.reload());
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                });
                        }
                    });
                @endif
            }

            // 6. RESET KE DEFAULT ROLE
            function resetToDefaultRole() {
                @if ($activeUser)
                    Swal.fire({
                        title: 'Reset ke Default Role?',
                        text: 'Semua hak akses khusus (direct permissions) pengguna ini akan dihapus. Pengguna akan kembali menggunakan wewenang murni dari role yang ditetapkan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Reset Hak Akses',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 text-xs font-black rounded-xl',
                            cancelButton: 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 px-4 py-2 text-xs font-bold rounded-xl mr-2'
                        },
                        buttonsStyling: false
                    }).then((res) => {
                        if (res.isConfirmed) {
                            fetch("{{ route('user-permissions.reset-direct', $activeUser->id) }}", {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            .getAttribute('content'),
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    Swal.fire({
                                        title: 'Berhasil Direset!',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => location.reload());
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                });
                        }
                    });
                @endif
            }

            // 7. FORM SYNC ROLES VIA AJAX
            const formSyncRoles = document.getElementById('formSyncRoles');
            if (formSyncRoles) {
                formSyncRoles.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = document.getElementById('btnSaveRoles');
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...';

                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Peran Diperbarui!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1200,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Perhatian', data.message || 'Gagal memperbarui peran.', 'error');
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kendala koneksi.', 'error');
                        });
                });
            }

            // 8. FLOATING TOAST NOTIFICATION
            function showFloatingToast(message, type = 'success') {
                let toast = document.getElementById('m3FloatingToast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'm3FloatingToast';
                    toast.className =
                        'fixed bottom-6 right-6 z-[9999] px-4 py-3 rounded-2xl shadow-xl border flex items-center gap-2.5 text-xs font-black transition-all duration-300 transform translate-y-12 opacity-0 pointer-events-none';
                    document.body.appendChild(toast);
                }

                if (type === 'success') {
                    toast.className =
                        'fixed bottom-6 right-6 z-[9999] px-4 py-3 rounded-2xl shadow-xl border flex items-center gap-2.5 text-xs font-black transition-all duration-300 transform translate-y-0 opacity-100 bg-emerald-600 text-white border-emerald-500';
                    toast.innerHTML = '<i class="bi bi-check-circle-fill text-sm"></i> <span>' + message + '</span>';
                } else {
                    toast.className =
                        'fixed bottom-6 right-6 z-[9999] px-4 py-3 rounded-2xl shadow-xl border flex items-center gap-2.5 text-xs font-black transition-all duration-300 transform translate-y-0 opacity-100 bg-rose-600 text-white border-rose-500';
                    toast.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-sm"></i> <span>' + message + '</span>';
                }

                setTimeout(() => {
                    toast.className += ' translate-y-12 opacity-0';
                }, 2200);
            }
        </script>
    @endpush
</x-app-layout>
