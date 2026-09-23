@section('title', 'Log Aktivitas & Monitoring Sesi')

<x-app-layout>
    <!-- 1. Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative z-10">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-shield-check text-xs"></i>
                    <span>Sistem & Keamanan</span>
                </span>
                <span class="text-xs text-zinc-400">•</span>
                <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                    Audit Trail & Sesi
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Log Aktivitas & Riwayat Login
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Monitoring real-time aktivitas autentikasi, alamat IP, jenis perangkat, dan sesi login pengguna sistem.
            </p>
        </div>

        <!-- Tombol Aksi Header -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <button type="button" onclick="refreshGridData()" title="Segarkan Data"
                class="h-10 px-3.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs transition-all flex items-center gap-1.5 border border-zinc-200/80 dark:border-zinc-700/80 active:scale-95">
                <i class="bi bi-arrow-clockwise text-sm"></i>
                <span class="hidden sm:inline">Segarkan</span>
            </button>

            <button type="button" onclick="openPruneModal()"
                class="h-10 px-4 rounded-2xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-black text-xs transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                <i class="bi bi-trash3-fill text-sm"></i>
                <span>Pembersihan Log</span>
            </button>
        </div>
    </div>

    <!-- 2. Ringkasan Metrik Cepat (4 Interactive Quick-Filter Cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4 mb-6 relative z-10">
        <!-- Card 1: Total Log (Reset Filter) -->
        <button type="button" onclick="filterByQuickStat('all')"
            class="stat-filter-card text-left m3-glass-card p-4 md:p-4.5 rounded-2xl md:rounded-3xl border transition-all duration-200 flex items-center gap-3.5 shadow-2xs hover:scale-[1.02] active:scale-[0.98] {{ !request('platform') && !request('status') ? 'border-indigo-500/50 dark:border-indigo-500/60 ring-2 ring-indigo-500/10' : 'border-zinc-200/80 dark:border-zinc-800 hover:border-indigo-500/30' }}">
            <div
                class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-black border border-indigo-500/20 flex-shrink-0 shadow-2xs">
                <i class="bi bi-activity"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span
                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block truncate">
                    Total Aktivitas
                </span>
                <span class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white" id="statTotalLogs">
                    {{ number_format($totalLogs) }}
                </span>
            </div>
        </button>

        <!-- Card 2: Login App Ustadz Hari Ini -->
        <button type="button" onclick="filterByQuickStat('app_ustadz')"
            class="stat-filter-card text-left m3-glass-card p-4 md:p-4.5 rounded-2xl md:rounded-3xl border transition-all duration-200 flex items-center gap-3.5 shadow-2xs hover:scale-[1.02] active:scale-[0.98] {{ request('platform') === 'app_ustadz' ? 'border-emerald-500/50 dark:border-emerald-500/60 ring-2 ring-emerald-500/10 bg-emerald-500/5' : 'border-zinc-200/80 dark:border-zinc-800 hover:border-emerald-500/30' }}">
            <div
                class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-black border border-emerald-500/20 flex-shrink-0 relative shadow-2xs">
                <i class="bi bi-phone-fill"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-emerald-500 rounded-full"></span>
            </div>
            <div class="min-w-0 flex-1">
                <span
                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block truncate">
                    App Ustadz (Hari Ini)
                </span>
                <span class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400"
                    id="statTodayUstadzLogs">
                    {{ number_format($todayUstadzLogs) }}
                </span>
            </div>
        </button>

        <!-- Card 3: Login Web Admin Hari Ini -->
        <button type="button" onclick="filterByQuickStat('web')"
            class="stat-filter-card text-left m3-glass-card p-4 md:p-4.5 rounded-2xl md:rounded-3xl border transition-all duration-200 flex items-center gap-3.5 shadow-2xs hover:scale-[1.02] active:scale-[0.98] {{ request('platform') === 'web' ? 'border-blue-500/50 dark:border-blue-500/60 ring-2 ring-blue-500/10 bg-blue-500/5' : 'border-zinc-200/80 dark:border-zinc-800 hover:border-blue-500/30' }}">
            <div
                class="w-11 h-11 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-black border border-blue-500/20 flex-shrink-0 shadow-2xs">
                <i class="bi bi-laptop"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span
                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block truncate">
                    Web Admin (Hari Ini)
                </span>
                <span class="text-xl md:text-2xl font-black text-blue-600 dark:text-blue-400" id="statTodayWebLogs">
                    {{ number_format($todayWebLogs) }}
                </span>
            </div>
        </button>

        <!-- Card 4: Percobaan Gagal Hari Ini -->
        <button type="button" onclick="filterByQuickStat('failed')"
            class="stat-filter-card text-left m3-glass-card p-4 md:p-4.5 rounded-2xl md:rounded-3xl border transition-all duration-200 flex items-center gap-3.5 shadow-2xs hover:scale-[1.02] active:scale-[0.98] {{ request('status') === 'failed' ? 'border-rose-500/50 dark:border-rose-500/60 ring-2 ring-rose-500/10 bg-rose-500/5' : 'border-zinc-200/80 dark:border-zinc-800 hover:border-rose-500/30' }}">
            <div
                class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl font-black border border-rose-500/20 flex-shrink-0 shadow-2xs">
                <i class="bi bi-shield-slash-fill"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span
                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block truncate">
                    Gagal (Hari Ini)
                </span>
                <span class="text-xl md:text-2xl font-black text-rose-600 dark:text-rose-400" id="statFailedLogs">
                    {{ number_format($failedLogs) }}
                </span>
            </div>
        </button>
    </div>

    <!-- 3. Toolbar & Filter Section -->
    <div
        class="m3-glass-card p-3.5 md:p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 mb-5 relative z-10 shadow-2xs">
        <form id="filterForm" action="{{ route('log-aktivitas.index') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

            <!-- Pencarian Teks (4 Kolom) -->
            <div class="relative lg:col-span-4">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                    placeholder="Cari user, IP Address, perangkat, deskripsi..."
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold">
                <button type="button" id="btnClearSearch" onclick="clearSearch()"
                    class="{{ request('search') ? '' : 'hidden' }} absolute inset-y-0 right-0 w-8 flex items-center justify-center text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Filter Platform (3 Kolom) -->
            <div class="lg:col-span-3">
                <select name="platform" id="platformFilter" onchange="applyFilters()"
                    class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                    <option value="">-- Semua Platform --</option>
                    <option value="app_ustadz" {{ request('platform') === 'app_ustadz' ? 'selected' : '' }}>📱 App
                        Ustadz (Mobile)</option>
                    <option value="web" {{ request('platform') === 'web' ? 'selected' : '' }}>💻 Web Admin</option>
                    <option value="app_murid" {{ request('platform') === 'app_murid' ? 'selected' : '' }}>🎓 App Wali
                        Murid</option>
                    <option value="system" {{ request('platform') === 'system' ? 'selected' : '' }}>⚙️ Server / Sistem
                    </option>
                </select>
            </div>

            <!-- Filter Event (2 Kolom) -->
            <div class="lg:col-span-2">
                <select name="event" id="eventFilter" onchange="applyFilters()"
                    class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                    <option value="">-- Semua Event --</option>
                    <option value="login" {{ request('event') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('event') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="failed_login" {{ request('event') === 'failed_login' ? 'selected' : '' }}>Login
                        Gagal</option>
                </select>
            </div>

            <!-- Filter Waktu & Quick Reset (3 Kolom) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <select name="date_range" id="dateRangeFilter" onchange="handleDateRangeChange()"
                    class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                    <option value="all" {{ request('date_range', 'all') === 'all' ? 'selected' : '' }}>Semua Waktu
                    </option>
                    <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Kemarin
                    </option>
                    <option value="7days" {{ request('date_range') === '7days' ? 'selected' : '' }}>7 Hari Terakhir
                    </option>
                    <option value="30days" {{ request('date_range') === '30days' ? 'selected' : '' }}>30 Hari Terakhir
                    </option>
                    <option value="custom" {{ request('date_range') === 'custom' ? 'selected' : '' }}>Rentang
                        Kustom...</option>
                </select>

                <!-- Hidden inputs untuk Status dan Peran jika diperlukan -->
                <input type="hidden" name="status" id="statusFilterHidden" value="{{ request('status') }}">
                <input type="hidden" name="role" id="roleFilterHidden" value="{{ request('role') }}">

                @php
                    $hasFilter = request()->hasAny([
                        'search',
                        'platform',
                        'event',
                        'status',
                        'date_range',
                        'role',
                        'start_date',
                    ]);
                @endphp
                <button type="button" id="btnResetFilter" onclick="resetAllFilters()" title="Reset Filter"
                    class="{{ $hasFilter ? '' : 'hidden' }} w-9 h-9 rounded-xl md:rounded-2xl flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-500 hover:text-rose-600 dark:hover:text-rose-400 transition-all flex-shrink-0 shadow-2xs active:scale-90">
                    <i class="bi bi-arrow-counterclockwise text-sm font-bold"></i>
                </button>
            </div>

            <!-- Custom Date Inputs (Conditional) -->
            <div id="customDateContainer"
                class="{{ request('date_range') === 'custom' ? '' : 'hidden' }} lg:col-span-12 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-zinc-200/80 dark:border-zinc-800">
                <div>
                    <label
                        class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">Tanggal
                        Awal</label>
                    <input type="date" name="start_date" id="startDateInput" value="{{ request('start_date') }}"
                        onchange="applyFilters()" class="m3-input-glass w-full text-xs font-bold">
                </div>
                <div>
                    <label
                        class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-1">Tanggal
                        Akhir</label>
                    <input type="date" name="end_date" id="endDateInput" value="{{ request('end_date') }}"
                        onchange="applyFilters()" class="m3-input-glass w-full text-xs font-bold">
                </div>
            </div>
        </form>
    </div>

    <!-- 4. Data Grid Container -->
    <div id="data-grid-container" class="relative z-10 transition-opacity duration-200">
        @include('log-aktivitas.list', ['logs' => $logs])
    </div>

    <!-- 5. Modal Containers -->
    <div id="modalContainer"></div>

    <!-- 6. Modal Prune Logs -->
    <div id="pruneLogsModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md transition-opacity duration-300">
        <div
            class="bg-white/95 dark:bg-zinc-900/95 backdrop-blur-2xl border border-zinc-200/80 dark:border-zinc-800/80 rounded-3xl w-full max-w-md flex flex-col shadow-2xl overflow-hidden transform transition-all animate-in zoom-in-95 duration-200">
            <div
                class="px-6 py-5 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-lg border border-rose-500/20 shadow-2xs">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">Pembersihan Log
                            Aktivitas</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">Hapus rekaman log lama untuk
                            optimasi database</p>
                    </div>
                </div>
                <button type="button" onclick="closePruneModal()"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    <i class="bi bi-x-lg text-xs font-bold"></i>
                </button>
            </div>

            <form id="pruneLogsForm" action="{{ route('log-aktivitas.clear-old') }}" method="POST"
                class="p-6 space-y-4">
                @csrf
                <div
                    class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-medium leading-relaxed flex items-start gap-2.5">
                    <i class="bi bi-exclamation-triangle-fill text-amber-600 mt-0.5 flex-shrink-0"></i>
                    <span>Tindakan ini permanen dan tidak dapat dibatalkan. Pastikan data log penting telah dibackup
                        jika diperlukan.</span>
                </div>

                <div>
                    <label
                        class="block text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2">Pilih
                        Retensi Waktu</label>
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-3 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 cursor-pointer hover:border-rose-500/40 transition-colors">
                            <input type="radio" name="period" value="30" checked
                                class="text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Hapus log lebih lama dari
                                30 hari</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 cursor-pointer hover:border-rose-500/40 transition-colors">
                            <input type="radio" name="period" value="60"
                                class="text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Hapus log lebih lama dari
                                60 hari</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 cursor-pointer hover:border-rose-500/40 transition-colors">
                            <input type="radio" name="period" value="90"
                                class="text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Hapus log lebih lama dari
                                90 hari</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-3 rounded-2xl border border-rose-500/20 bg-rose-500/5 cursor-pointer hover:border-rose-500/40 transition-colors">
                            <input type="radio" name="period" value="all"
                                class="text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-black text-rose-600 dark:text-rose-400">Kosongkan semua rekaman
                                log (Reset Total)</span>
                        </label>
                    </div>
                </div>

                <div
                    class="pt-4 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closePruneModal()"
                        class="h-10 px-5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-black text-zinc-700 dark:text-zinc-300 transition-all">Batal</button>
                    <button type="submit" id="btnSubmitPrune"
                        class="h-10 px-6 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-md flex items-center gap-2 transition-all active:scale-95">
                        <i class="bi bi-trash3-fill"></i> Eksekusi Pembersihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<!-- 7. Interactive JavaScript -->
<script>
    const csrfToken = '{{ csrf_token() }}';

    // MODAL DETAIL LOG (AJAX)
    function openDetailModal(logId) {
        fetch(`{{ url('log-aktivitas') }}/${logId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                document.getElementById('modalContainer').innerHTML = html;
            })
            .catch(err => {
                Swal.fire('Error', 'Gagal memuat detail log aktivitas: ' + err, 'error');
            });
    }

    function closeDetailModal() {
        const modal = document.getElementById('logDetailModal');
        if (modal) {
            modal.classList.add('opacity-0');
            setTimeout(() => modal.remove(), 200);
        }
    }

    // MODAL PRUNE LOGS
    function openPruneModal() {
        document.getElementById('pruneLogsModal').classList.remove('hidden');
    }

    function closePruneModal() {
        document.getElementById('pruneLogsModal').classList.add('hidden');
    }

    document.getElementById('pruneLogsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPrune');
        const origHtml = btn.innerHTML;

        Swal.fire({
            title: '<span class="text-base font-black text-rose-600">Konfirmasi Pembersihan Log</span>',
            html: '<p class="text-xs text-zinc-500 dark:text-zinc-400">Apakah Anda yakin ingin mengeksekusi pembersihan rekaman log ini?</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bersihkan!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: '!rounded-3xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-2xl p-6 !bg-white/90 dark:!bg-zinc-900/90 !backdrop-blur-2xl',
                confirmButton: 'h-10 px-5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl ml-2 shadow-md transition-all active:scale-95',
                cancelButton: 'h-10 px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl transition-all'
            },
            buttonsStyling: false
        }).then(res => {
            if (res.isConfirmed) {
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="inline-block animate-spin mr-1"><i class="bi bi-arrow-repeat"></i></span> Membersihkan...';

                fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new FormData(this)
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        closePruneModal();
                        showToast(data.message, 'success');
                        refreshGridData();
                    })
                    .catch(err => {
                        Swal.fire('Error', err.message || 'Gagal membersihkan log.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                    });
            }
        });
    });

    // COPY IP ADDRESS TO CLIPBOARD
    function copyIpAddress(ip, btn) {
        if (!navigator.clipboard) {
            const temp = document.createElement('input');
            temp.value = ip;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        } else {
            navigator.clipboard.writeText(ip);
        }

        const origHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 text-emerald-500"></i>';
        showToast('IP ' + ip + ' berhasil disalin!', 'info');
        setTimeout(() => {
            btn.innerHTML = origHtml;
        }, 1500);
    }

    // HAPUS 1 REKAMAN LOG
    function confirmDeleteLog(logId) {
        Swal.fire({
            title: '<span class="text-base font-black text-rose-600">Hapus Log Aktivitas?</span>',
            html: '<p class="text-xs text-zinc-500 dark:text-zinc-400">Rekaman log ini akan dihapus dari riwayat.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: '!rounded-3xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-2xl p-6 !bg-white/90 dark:!bg-zinc-900/90 !backdrop-blur-2xl',
                confirmButton: 'h-10 px-5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl ml-2 shadow-md transition-all active:scale-95',
                cancelButton: 'h-10 px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl transition-all'
            },
            buttonsStyling: false
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`{{ url('log-aktivitas') }}/${logId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        showToast(data.message, 'success');
                        refreshGridData();
                    })
                    .catch(err => {
                        Swal.fire('Error', err.message || 'Gagal menghapus log.', 'error');
                    });
            }
        });
    }

    // FILTER & SEARCH LOGIC
    let searchTimeout = null;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        const btnClear = document.getElementById('btnClearSearch');
        if (btnClear) {
            if (this.value) btnClear.classList.remove('hidden');
            else btnClear.classList.add('hidden');
        }
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 350);
    });

    function handleDateRangeChange() {
        const select = document.getElementById('dateRangeFilter');
        const customContainer = document.getElementById('customDateContainer');
        if (select.value === 'custom') {
            customContainer.classList.remove('hidden');
        } else {
            customContainer.classList.add('hidden');
            document.getElementById('startDateInput').value = '';
            document.getElementById('endDateInput').value = '';
            applyFilters();
        }
    }

    function filterByQuickStat(type) {
        const platformSelect = document.getElementById('platformFilter');
        const statusHidden = document.getElementById('statusFilterHidden');
        const searchInput = document.getElementById('searchInput');

        if (type === 'all') {
            platformSelect.value = '';
            statusHidden.value = '';
            searchInput.value = '';
        } else if (type === 'app_ustadz') {
            platformSelect.value = 'app_ustadz';
            statusHidden.value = '';
        } else if (type === 'web') {
            platformSelect.value = 'web';
            statusHidden.value = '';
        } else if (type === 'failed') {
            statusHidden.value = 'failed';
            platformSelect.value = '';
        }

        applyFilters();
    }

    function clearSearch() {
        const input = document.getElementById('searchInput');
        input.value = '';
        document.getElementById('btnClearSearch')?.classList.add('hidden');
        applyFilters();
    }

    function resetAllFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('platformFilter').value = '';
        document.getElementById('eventFilter').value = '';
        document.getElementById('dateRangeFilter').value = 'all';
        document.getElementById('statusFilterHidden').value = '';
        document.getElementById('roleFilterHidden').value = '';
        document.getElementById('customDateContainer').classList.add('hidden');
        document.getElementById('startDateInput').value = '';
        document.getElementById('endDateInput').value = '';
        applyFilters();
    }

    function applyFilters() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        const url = `${form.action}?${params}`;

        window.history.replaceState({}, '', url);

        const gridContainer = document.getElementById('data-grid-container');
        if (gridContainer) gridContainer.classList.add('opacity-50', 'pointer-events-none');

        // Toggle reset button
        const hasFilter = formData.get('search') || formData.get('platform') || formData.get('event') || (formData.get(
            'date_range') && formData.get('date_range') !== 'all') || formData.get('status');
        const btnReset = document.getElementById('btnResetFilter');
        if (btnReset) {
            if (hasFilter) btnReset.classList.remove('hidden');
            else btnReset.classList.add('hidden');
        }

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (gridContainer) {
                    gridContainer.innerHTML = data.html;
                    gridContainer.classList.remove('opacity-50', 'pointer-events-none');
                }
                if (data.metrics) {
                    document.getElementById('statTotalLogs').innerText = Number(data.metrics.total).toLocaleString(
                        'id-ID');
                    document.getElementById('statTodayUstadzLogs').innerText = Number(data.metrics.today_ustadz)
                        .toLocaleString('id-ID');
                    document.getElementById('statTodayWebLogs').innerText = Number(data.metrics.today_web)
                        .toLocaleString('id-ID');
                    document.getElementById('statFailedLogs').innerText = Number(data.metrics.failed)
                        .toLocaleString('id-ID');
                }
            })
            .catch(() => {
                if (gridContainer) gridContainer.classList.remove('opacity-50', 'pointer-events-none');
            });
    }

    function refreshGridData() {
        applyFilters();
    }

    function showToast(message, icon = 'success') {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: isDark ? '#18181b' : '#ffffff',
            color: isDark ? '#f4f4f5' : '#18181b',
        });
    }
</script>
