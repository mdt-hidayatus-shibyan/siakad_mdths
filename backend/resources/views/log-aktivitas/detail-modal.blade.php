@php
    $user = $log->user;
    $roleName = $user?->roles->first()?->name ?? ($log->properties['role'] ?? 'Tamu / Anonim');
    $eventBadge = $log->event_badge;
@endphp

<div class="relative z-10 flex flex-col max-h-[90vh]">
    <!-- MODAL HEADER -->
    <div
        class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 px-5 py-4 flex items-center justify-between transition-colors">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-lg border border-emerald-500/20 shadow-2xs shrink-0">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                        Rincian Log Aktivitas
                    </h3>
                    <span class="text-xs font-mono font-bold text-zinc-400">#{{ $log->id }}</span>
                </div>
                <p class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    {{ $log->created_at->translatedFormat('l, d F Y - H:i:s') }} WIB
                    ({{ $log->created_at->diffForHumans() }})
                </p>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-400 transition-colors outline-none shadow-2xs cursor-pointer">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- MODAL BODY (SCROLLABLE) -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">

        <!-- SECTION 1: PROFIL PENGGUNA -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60">
            <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-2">Identitas
                Pengguna</span>
            <div class="flex items-center gap-3">
                @if ($user && $user->ustadz && $user->ustadz->foto)
                    <img src="{{ asset('storage/' . $user->ustadz->foto) }}" alt="{{ $user->name }}"
                        class="w-11 h-11 rounded-2xl object-cover border border-zinc-200 dark:border-zinc-700 shadow-2xs flex-shrink-0">
                @else
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-sm border border-emerald-500/20 shadow-2xs flex-shrink-0">
                        {{ strtoupper(substr($user->name ?? ($log->properties['attempted_login_id'] ?? 'U'), 0, 2)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <h4 class="text-sm font-black text-zinc-900 dark:text-white truncate">
                        {{ $user->name ?? ($log->properties['attempted_login_id'] ?? 'Pengguna Anonim / Tamu') }}
                    </h4>
                    <div class="flex flex-wrap items-center gap-2 mt-0.5">
                        <span class="text-xs font-mono font-bold text-zinc-600 dark:text-zinc-400">
                            {{ $user->username ?? ($user->email ?? ($log->properties['attempted_login_id'] ?? '-')) }}
                        </span>
                        <span class="text-zinc-300 dark:text-zinc-600">•</span>
                        <span
                            class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            {{ strtoupper(str_replace('-', ' ', $roleName)) }}
                        </span>
                        @if ($user && $user->tingkat)
                            <span
                                class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-zinc-200/80 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                {{ $user->tingkat->nama_tingkat }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: GRID INFORMASI AKSES & JARINGAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Platform -->
            <div
                class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60">
                <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">Platform
                    Aplikasi</span>
                <div class="flex items-center gap-2 mt-1">
                    @if ($log->platform === 'app_ustadz')
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <i class="bi bi-phone-fill"></i> App Ustadz (Mobile)
                        </span>
                    @elseif ($log->platform === 'app_murid')
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                            <i class="bi bi-mortarboard-fill"></i> App Wali Murid
                        </span>
                    @elseif ($log->platform === 'web')
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            <i class="bi bi-laptop"></i> Web Admin
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border border-zinc-500/20">
                            <i class="bi bi-hdd-network"></i> {{ ucfirst($log->platform) }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Alamat IP -->
            <div
                class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60">
                <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">Alamat IP
                    Client</span>
                <div class="flex items-center justify-between mt-1">
                    <span class="font-mono font-bold text-xs text-zinc-900 dark:text-white select-all">
                        {{ $log->ip_address ?: '127.0.0.1' }}
                    </span>
                    @if ($log->ip_address)
                        <button type="button" onclick="copyIpAddress('{{ $log->ip_address }}', this)"
                            class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-zinc-200/80 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-200 transition-colors flex items-center gap-1 cursor-pointer">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    @endif
                </div>
            </div>

            <!-- Perangkat & OS -->
            <div
                class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60">
                <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">Perangkat &
                    OS</span>
                <div class="flex items-center gap-2 mt-1">
                    <i class="bi {{ $log->device_icon }} text-base"></i>
                    <span class="text-xs font-bold text-zinc-900 dark:text-white">
                        {{ $log->device ?? 'Perangkat Standar' }} ({{ $log->os ?? '-' }})
                    </span>
                </div>
            </div>

            <!-- Event & Status -->
            <div
                class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60">
                <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">Status
                    Event</span>
                <div class="flex items-center gap-2 mt-1">
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-black uppercase tracking-wider border {{ $eventBadge['class'] }}">
                        <i class="bi {{ $eventBadge['icon'] }}"></i>
                        <span>{{ $eventBadge['label'] }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- SECTION 3: DESKRIPSI AKTIVITAS -->
        <div>
            <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">Deskripsi
                Aktivitas</span>
            <div
                class="p-3 rounded-2xl bg-zinc-100/80 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 text-xs font-medium text-zinc-800 dark:text-zinc-200 leading-relaxed">
                {{ $log->description ?? '-' }}
            </div>
        </div>

        <!-- SECTION 4: USER AGENT HEADER -->
        <div>
            <span class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">User Agent
                Header</span>
            <div
                class="p-3 rounded-xl bg-zinc-900 text-zinc-300 font-mono text-[10px] break-all select-all leading-relaxed">
                {{ $log->user_agent ?: 'Tidak tersedia' }}
            </div>
        </div>

        <!-- SECTION 5: METADATA PROPERTIES (JSON) -->
        @if (!empty($log->properties))
            <div>
                <div class="flex items-center justify-between mb-1 ml-1">
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider">Metadata Properties
                        (JSON)</span>
                    <button type="button" onclick="copyJsonMetadata(this)"
                        class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 cursor-pointer">
                        <i class="bi bi-code-square"></i> Salin JSON
                    </button>
                </div>
                <pre id="jsonMetadataBlock"
                    class="p-3 rounded-2xl bg-zinc-900 text-emerald-400 font-mono text-[10px] overflow-x-auto leading-relaxed border border-zinc-800">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>

    <!-- MODAL FOOTER -->
    <div
        class="bg-zinc-50/70 dark:bg-zinc-950/50 border-t border-zinc-200/80 dark:border-zinc-800 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="h-9 px-5 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-black text-zinc-700 dark:text-zinc-300 transition-all cursor-pointer">
            Tutup
        </button>
    </div>
</div>

<script>
    function copyJsonMetadata(btn) {
        const pre = document.getElementById('jsonMetadataBlock');
        if (!pre) return;
        navigator.clipboard.writeText(pre.innerText);
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin!';
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'info',
                title: 'JSON Metadata berhasil disalin!'
            });
        }
        setTimeout(() => {
            btn.innerHTML = origText;
        }, 1500);
    }
</script>
