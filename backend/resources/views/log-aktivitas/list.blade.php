<div
    class="m3-glass-card rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr
                    class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-100/50 dark:bg-zinc-800/40 text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    <th class="py-3.5 px-4">Pengguna</th>
                    <th class="py-3.5 px-4">Platform</th>
                    <th class="py-3.5 px-4">Alamat IP</th>
                    <th class="py-3.5 px-4">Perangkat & Klien</th>
                    <th class="py-3.5 px-4">Aktivitas / Event</th>
                    <th class="py-3.5 px-4">Waktu Akses</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                @forelse ($logs as $log)
                    @php
                        $user = $log->user;
                        $roleName = $user?->roles->first()?->name ?? ($log->properties['role'] ?? 'Tamu / Anonim');
                        $eventBadge = $log->event_badge;
                    @endphp
                    <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors">

                        <!-- 1. IDENTITAS PENGGUNA -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                @php
                                    $userPhoto = $user?->foto_url;
                                    if (!$userPhoto) {
                                        if ($user?->administrator && $user->administrator->foto) {
                                            $userPhoto = asset('storage/' . $user->administrator->foto);
                                        } elseif ($user?->ustadz && $user->ustadz->foto) {
                                            $userPhoto =
                                                $user->ustadz->foto_url ?? asset('storage/' . $user->ustadz->foto);
                                        }
                                    }
                                @endphp
                                @if ($userPhoto)
                                    <img src="{{ $userPhoto }}" alt="{{ $user->name ?? 'Pengguna' }}"
                                        class="w-8 h-8 rounded-xl object-cover border border-zinc-200 dark:border-zinc-700 shadow-2xs flex-shrink-0">
                                @else
                                    <div
                                        class="w-8 h-8 rounded-xl bg-zinc-200/80 dark:bg-zinc-800 flex items-center justify-center font-black text-xs text-zinc-600 dark:text-zinc-300 flex-shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($user->name ?? ($log->properties['attempted_login_id'] ?? 'U'), 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-zinc-900 dark:text-white truncate max-w-[180px]"
                                        title="{{ $user->name ?? 'Anonim' }}">
                                        {{ $user->name ?? ($log->properties['attempted_login_id'] ?? 'Percobaan Anonim') }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-mono text-zinc-500 dark:text-zinc-400 truncate">
                                            {{ $user->username ?? ($user->email ?? ($log->properties['attempted_login_id'] ?? '-')) }}
                                        </span>
                                        <span class="text-zinc-300 dark:text-zinc-600">•</span>
                                        <span
                                            class="px-1.5 py-0.2 rounded-md text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            {{ strtoupper(str_replace('-', ' ', $roleName)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. PLATFORM BADGE -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if ($log->platform === 'app_ustadz')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-2xs">
                                    <i class="bi bi-phone-fill text-xs"></i>
                                    <span>App Ustadz</span>
                                </span>
                            @elseif ($log->platform === 'app_murid')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-black bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 shadow-2xs">
                                    <i class="bi bi-mortarboard-fill text-xs"></i>
                                    <span>App Murid</span>
                                </span>
                            @elseif ($log->platform === 'web')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 shadow-2xs">
                                    <i class="bi bi-laptop text-xs"></i>
                                    <span>Web Admin</span>
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-black bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border border-zinc-500/20">
                                    <i class="bi bi-hdd-network text-xs"></i>
                                    <span>{{ ucfirst($log->platform) }}</span>
                                </span>
                            @endif
                        </td>

                        <!-- 3. ALAMAT IP DENGAN TOMBOL COPY -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200/80 dark:border-zinc-700/80">
                                <span class="font-mono font-bold text-xs text-zinc-800 dark:text-zinc-200 select-all">
                                    {{ $log->ip_address ?: '127.0.0.1' }}
                                </span>
                                @if ($log->ip_address)
                                    <button type="button" onclick="copyIpAddress('{{ $log->ip_address }}', this)"
                                        title="Salin Alamat IP"
                                        class="w-5 h-5 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                                        <i class="bi bi-clipboard text-[10px]"></i>
                                    </button>
                                @endif
                            </div>
                        </td>

                        <!-- 4. PERANGKAT & BROWSER -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="text-base flex-shrink-0">
                                    <i class="bi {{ $log->device_icon }}"></i>
                                </div>
                                <div class="min-w-0 max-w-[170px]">
                                    <div class="text-xs font-bold text-zinc-900 dark:text-white truncate"
                                        title="{{ $log->device ?? 'Perangkat Standar' }}">
                                        {{ $log->device ?? 'Perangkat Standar' }}
                                    </div>
                                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate"
                                        title="{{ $log->browser }}">
                                        {{ $log->os ? $log->os . ' • ' : '' }}{{ $log->browser ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 5. AKTIVITAS / EVENT BADGE -->
                        <td class="py-3 px-4">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border w-fit {{ $eventBadge['class'] }}">
                                    <i class="bi {{ $eventBadge['icon'] }}"></i>
                                    <span>{{ $eventBadge['label'] }}</span>
                                </span>
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate max-w-[220px]"
                                    title="{{ $log->description }}">
                                    {{ $log->description }}
                                </span>
                            </div>
                        </td>

                        <!-- 6. WAKTU AKSES -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="text-xs font-bold text-zinc-900 dark:text-white">
                                {{ $log->created_at->translatedFormat('d M Y, H:i') }} <span
                                    class="text-[10px] text-zinc-400 font-medium">WIB</span>
                            </div>
                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </td>

                        <!-- 7. AKSI (MENGGUNAKAN ACTION-MODAL DAN DELETE-AJAX DARI CUSTOM-SCRIPT.JS) -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Show (action-modal dari custom-script.js) -->
                                <a href="{{ route('log-aktivitas.show', $log->id) }}"
                                    class="action-modal w-8 h-8 rounded-xl flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 transition-all hover:scale-105 active:scale-95 shadow-2xs cursor-pointer"
                                    title="Lihat Detail Log">
                                    <i class="bi bi-eye-fill text-xs"></i>
                                </a>

                                <!-- Form Delete (delete-ajax dari custom-script.js) -->
                                <form action="{{ route('log-aktivitas.destroy', $log->id) }}" method="POST"
                                    class="delete-ajax inline" data-refresh-target="#data-grid-container">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Rekaman Log"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-all hover:scale-105 active:scale-95 shadow-2xs cursor-pointer">
                                        <i class="bi bi-trash3 text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 px-4 text-center">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div
                                    class="w-16 h-16 rounded-3xl bg-zinc-100 dark:bg-zinc-800/80 flex items-center justify-center text-2xl text-zinc-400 mb-3 border border-zinc-200/80 dark:border-zinc-700/80 shadow-2xs">
                                    <i class="bi bi-shield-check text-3xl"></i>
                                </div>
                                <h4 class="text-sm font-black text-zinc-900 dark:text-white">Tidak Ada Rekaman Log</h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                    Belum ada aktivitas atau data tidak ditemukan berdasarkan kriteria filter yang
                                    dipilih.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
</div>
@if ($logs->hasPages())
    <div class="mt-4 m3-glass-card p-4 relative z-10">
        {{ $logs->links('vendor.pagination.custom') }}
    </div>
@endif
