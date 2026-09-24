<!-- Modal Input / Edit Presensi Murid (AJAX M3 Glass) -->
<form action="{{ route('presensi-murid.storeHarian') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <!-- Header Modal -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-b border-zinc-200/80 dark:border-zinc-800 px-5 py-3.5 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5 min-w-0">
            <div
                class="w-9 h-9 rounded-xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-base shrink-0 shadow-2xs">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-black text-zinc-900 dark:text-white tracking-tight truncate">
                    Presensi Murid Kelas
                </h3>
                <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 truncate">
                    {{ $jadwal->ruangan->nama_ruangan ?? '-' }} • {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }} (Jam
                    {{ $jadwal->jam_ke }})
                </p>
            </div>
        </div>
        <!-- Close Button -->
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xl bg-zinc-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-zinc-800 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 text-zinc-400 transition-colors duration-200 outline-none active:scale-95 shadow-2xs">
            <i class="bi bi-x-lg text-xs font-black"></i>
        </button>
    </div>

    <!-- Info Banner & Quick Action Toolbar -->
    <div
        class="bg-zinc-100/70 dark:bg-zinc-800/50 px-5 py-2.5 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="text-xs font-black text-zinc-700 dark:text-zinc-300">
                <i class="bi bi-calendar2-date text-primary dark:text-primary-dark mr-1"></i>
                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}
            </span>
            <span class="text-zinc-300 dark:text-zinc-700">•</span>
            <span
                class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary dark:text-primary-dark font-black text-[10px]">
                {{ $murids->count() }} Murid
            </span>
        </div>

        <div class="flex items-center gap-1.5">
            <button type="button" onclick="setSemuaPresensiModal('Hadir')"
                class="px-2.5 py-1.5 bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 text-[11px] font-black rounded-xl transition-all flex items-center gap-1 active:scale-95 shadow-2xs">
                <i class="bi bi-check-all text-xs"></i>
                <span>Hadirkan Semua</span>
            </button>
            <button type="button" onclick="kosongkanSemuaPresensiModal()"
                class="px-2.5 py-1.5 bg-zinc-200/80 hover:bg-zinc-200 dark:bg-zinc-700/60 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-300/80 dark:border-zinc-600/60 text-[11px] font-black rounded-xl transition-all flex items-center gap-1 active:scale-95 shadow-2xs">
                <i class="bi bi-arrow-counterclockwise text-xs"></i>
                <span>Reset</span>
            </button>
        </div>
    </div>

    <!-- Modal Body: Student List with Spacious Full-Width Radio Grid -->
    <div class="p-4 sm:p-5 overflow-y-auto custom-scrollbar flex-1 space-y-3 max-h-[58vh]">
        @if ($murids->isEmpty())
            <div class="py-12 text-center">
                <div
                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-2 text-zinc-400 text-xl shadow-2xs">
                    <i class="bi bi-people"></i>
                </div>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Belum ada murid di ruangan ini.</p>
            </div>
        @else
            @foreach ($murids as $murid)
                @php
                    $statusSekarang = $presensiTersimpan->has($murid->id)
                        ? $presensiTersimpan[$murid->id]->status
                        : null;
                @endphp

                <div
                    class="p-3.5 rounded-2xl border border-zinc-200/90 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/60 shadow-2xs space-y-2.5">
                    <!-- Baris Atas: Nomor, Nama Murid & NISM -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div
                                class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200 font-black text-xs flex items-center justify-center shrink-0">
                                {{ $loop->iteration }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-black text-xs sm:text-sm text-zinc-900 dark:text-white truncate">
                                    {{ $murid->nama_lengkap }}
                                </h4>
                                <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 font-mono mt-0.5">
                                    NISM: {{ $murid->nism ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Badge Status Terpilih -->
                        <span id="badge-status-modal-{{ $murid->id }}"
                            class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider shrink-0 {{ $statusSekarang ? '' : 'hidden' }}
                            {{ $statusSekarang == 'Hadir' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30' : '' }}
                            {{ $statusSekarang == 'Sakit' ? 'bg-blue-500/15 text-blue-700 dark:text-blue-400 border border-blue-500/30' : '' }}
                            {{ $statusSekarang == 'Izin' ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30' : '' }}
                            {{ $statusSekarang == 'Alpha' ? 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30' : '' }}
                            {{ $statusSekarang == 'Dispensasi' ? 'bg-purple-500/15 text-purple-700 dark:text-purple-400 border border-purple-500/30' : '' }}
                        ">
                            {{ $statusSekarang }}
                        </span>
                    </div>

                    <!-- Baris Bawah: 5 Tombol Status H / S / I / A / D Full Width (Besar & Nyaman) -->
                    <div
                        class="grid grid-cols-5 gap-1.5 w-full bg-zinc-100/90 dark:bg-zinc-950/80 p-1.5 rounded-xl border border-zinc-200/90 dark:border-zinc-800">
                        @foreach (['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alpha' => 'A', 'Dispensasi' => 'D'] as $val => $label)
                            <label class="cursor-pointer relative block text-center" title="{{ $val }}">
                                <input type="radio" name="presensi[{{ $murid->id }}]" value="{{ $val }}"
                                    class="sr-only modal-presensi-radio-input" data-murid-id="{{ $murid->id }}"
                                    {{ $statusSekarang == $val ? 'checked' : '' }}
                                    onchange="updateModalStatusBadge('{{ $murid->id }}', '{{ $val }}')">
                                <div class="btn-presensi-opt opt-{{ strtolower($val) }}">
                                    <span>{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-t border-zinc-200/80 dark:border-zinc-800 px-5 py-3.5 sm:flex sm:flex-row-reverse sm:items-center sm:justify-between gap-3 transition-colors duration-300">
        <button type="submit"
            class="m3-btn-primary w-full sm:w-auto h-10 px-6 text-xs font-black shadow-md flex items-center justify-center gap-2"
            {{ $murids->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-check2-circle text-sm"></i>
            <span>Simpan Presensi</span>
        </button>

        <p class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 text-center sm:text-left mt-2 sm:mt-0">
            H: Hadir • S: Sakit • I: Izin • A: Alpha • D: Dispensasi
        </p>
    </div>
</form>

<script>
    function updateModalStatusBadge(muridId, status) {
        const badge = document.getElementById('badge-status-modal-' + muridId);
        if (!badge) return;

        const classMap = {
            'Hadir': 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30',
            'Sakit': 'bg-blue-500/15 text-blue-700 dark:text-blue-400 border border-blue-500/30',
            'Izin': 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30',
            'Alpha': 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30',
            'Dispensasi': 'bg-purple-500/15 text-purple-700 dark:text-purple-400 border border-purple-500/30'
        };

        badge.className = 'px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider shrink-0 ' + (classMap[
            status] || '');
        badge.textContent = status;
        badge.classList.remove('hidden');
    }

    function setSemuaPresensiModal(status) {
        document.querySelectorAll('#modal-content-wrapper input.modal-presensi-radio-input[value="' + status + '"]')
            .forEach(radio => {
                radio.checked = true;
                if (radio.dataset.muridId) {
                    updateModalStatusBadge(radio.dataset.muridId, status);
                }
            });
    }

    function kosongkanSemuaPresensiModal() {
        document.querySelectorAll('#modal-content-wrapper input.modal-presensi-radio-input').forEach(radio => {
            radio.checked = false;
            if (radio.dataset.muridId) {
                const badge = document.getElementById('badge-status-modal-' + radio.dataset.muridId);
                if (badge) {
                    badge.classList.add('hidden');
                    badge.textContent = '';
                }
            }
        });
    }
</script>
