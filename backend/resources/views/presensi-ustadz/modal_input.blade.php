<!-- Modal Input / Edit Presensi Ustadz (AJAX M3 Glass) -->
<form action="{{ route('presensi-ustadz.storeBulanan') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
    <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
    <input type="hidden" name="ustadz_id" value="{{ $ustadz->id }}">

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-b border-zinc-200/80 dark:border-zinc-800 px-6 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-lg shrink-0 shadow-2xs">
                <i class="bi bi-person-video3"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                    {{ $presensi ? 'Update Presensi Guru' : 'Absen Kehadiran Guru' }}
                </h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }} • Jam Ke-{{ $jadwal->jam_ke }}
                </p>
            </div>
        </div>
        <!-- Close Button -->
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="min-w-[38px] min-h-[38px] flex items-center justify-center rounded-2xl bg-zinc-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-zinc-800 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 text-zinc-400 transition-colors duration-200 outline-none active:scale-95 shadow-2xs">
            <i class="bi bi-x-lg text-xs font-black"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1">
        <!-- Detail Info Card -->
        <div
            class="bg-zinc-100/70 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/80 p-4 rounded-2xl shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h4 class="text-sm sm:text-base font-black text-zinc-900 dark:text-white tracking-tight">
                        {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}
                    </h4>
                    <span class="inline-block mt-0.5 text-xs font-bold text-zinc-500 dark:text-zinc-400">
                        Ruangan: {{ $jadwal->ruangan->nama_ruangan ?? '-' }}
                    </span>
                </div>

                <div
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-700 text-xs font-black shadow-2xs">
                    <i class="bi bi-person-fill text-primary dark:text-primary-dark"></i>
                    <span class="text-zinc-900 dark:text-zinc-200">{{ $ustadz->nama_lengkap }}</span>
                    @if ($isUtama)
                        <span
                            class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/25 text-[9px] font-black uppercase">
                            <i class="bi bi-star-fill text-[8px]"></i> Utama
                        </span>
                    @else
                        <span
                            class="px-1.5 py-0.5 rounded bg-sky-500/15 text-sky-700 dark:text-sky-400 border border-sky-500/25 text-[9px] font-black uppercase">
                            Pendamping
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Input Status (Custom Radio - Spacious & Clear) -->
        <div>
            <label class="block text-xs font-black text-zinc-600 dark:text-zinc-300 uppercase tracking-wider mb-2 ml-1">
                Status Kehadiran <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                @php
                    $curStatus = $presensi ? $presensi->status : 'Hadir';
                @endphp

                <!-- Opsi Hadir -->
                <label class="relative cursor-pointer">
                    <input type="radio" name="status" value="Hadir" class="modal-ustadz-status-radio sr-only"
                        {{ $curStatus == 'Hadir' ? 'checked' : '' }} onchange="togglePenggantiModal(this.value)">
                    <div
                        class="btn-presensi-opt opt-hadir flex flex-col items-center justify-center py-3 px-2 text-xs font-black rounded-2xl">
                        <i class="bi bi-check-circle-fill text-base mb-1"></i>
                        <span>Hadir</span>
                    </div>
                </label>

                <!-- Opsi Sakit -->
                <label class="relative cursor-pointer">
                    <input type="radio" name="status" value="Sakit" class="modal-ustadz-status-radio sr-only"
                        {{ $curStatus == 'Sakit' ? 'checked' : '' }} onchange="togglePenggantiModal(this.value)">
                    <div
                        class="btn-presensi-opt opt-sakit flex flex-col items-center justify-center py-3 px-2 text-xs font-black rounded-2xl">
                        <i class="bi bi-bandaid-fill text-base mb-1"></i>
                        <span>Sakit</span>
                    </div>
                </label>

                <!-- Opsi Izin -->
                <label class="relative cursor-pointer">
                    <input type="radio" name="status" value="Izin" class="modal-ustadz-status-radio sr-only"
                        {{ $curStatus == 'Izin' ? 'checked' : '' }} onchange="togglePenggantiModal(this.value)">
                    <div
                        class="btn-presensi-opt opt-izin flex flex-col items-center justify-center py-3 px-2 text-xs font-black rounded-2xl">
                        <i class="bi bi-info-circle-fill text-base mb-1"></i>
                        <span>Izin</span>
                    </div>
                </label>

                <!-- Opsi Alpha -->
                <label class="relative cursor-pointer">
                    <input type="radio" name="status" value="Alpha" class="modal-ustadz-status-radio sr-only"
                        {{ $curStatus == 'Alpha' ? 'checked' : '' }} onchange="togglePenggantiModal(this.value)">
                    <div
                        class="btn-presensi-opt opt-alpha flex flex-col items-center justify-center py-3 px-2 text-xs font-black rounded-2xl">
                        <i class="bi bi-exclamation-octagon-fill text-base mb-1"></i>
                        <span>Alpha</span>
                    </div>
                </label>

                <!-- Opsi Kosong -->
                <label class="relative cursor-pointer col-span-2 sm:col-span-1">
                    <input type="radio" name="status" value="Kosong" class="modal-ustadz-status-radio sr-only"
                        {{ $curStatus == 'Kosong' ? 'checked' : '' }} onchange="togglePenggantiModal(this.value)">
                    <div
                        class="btn-presensi-opt opt-kosong flex flex-col items-center justify-center py-3 px-2 text-xs font-black rounded-2xl">
                        <i class="bi bi-dash-circle-fill text-base mb-1"></i>
                        <span>Kosong</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Input Badal (Toggled via JS) -->
        <div id="modal_wrapper_pengganti"
            class="{{ in_array($curStatus, ['Sakit', 'Izin', 'Alpha']) ? '' : 'hidden' }} space-y-1.5 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20">
            <label
                class="block text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                <i class="bi bi-arrow-left-right"></i> Guru Pengganti (Badal)
            </label>
            <div class="relative">
                <select name="ustadz_pengganti_id" id="modal_m_pengganti"
                    class="m3-input-glass w-full !pr-8 text-xs font-bold text-amber-900 dark:text-amber-200 !border-amber-500/40 cursor-pointer appearance-none">
                    <option value="" class="text-zinc-500">-- Pilih Guru Pengganti (Badal) --</option>
                    @foreach ($semuaGuru as $guru)
                        <option value="{{ $guru->id }}"
                            {{ $presensi?->ustadz_pengganti_id == $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-amber-600">
                    <i class="bi bi-chevron-down text-xs font-black"></i>
                </div>
            </div>
        </div>

        <!-- Input Keterangan -->
        <div>
            <label
                class="block text-xs font-black text-zinc-600 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                Keterangan Tambahan
            </label>
            <input type="text" name="keterangan" value="{{ $presensi?->keterangan ?? '' }}"
                placeholder="Alasan izin / materi pembelajaran (Opsional)..."
                class="m3-input-glass w-full text-xs font-medium">
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-t border-zinc-200/80 dark:border-zinc-800 px-6 py-4 sm:flex sm:flex-row-reverse sm:items-center gap-3 transition-colors duration-300">
        <button type="submit"
            class="m3-btn-primary w-full sm:w-auto h-11 px-8 text-xs font-black shadow-md flex items-center justify-center gap-2">
            <i class="bi bi-save2-fill text-sm"></i>
            <span>Simpan Presensi Guru</span>
        </button>
    </div>
</form>

<script>
    function togglePenggantiModal(status) {
        const wrapper = document.getElementById('modal_wrapper_pengganti');
        if (['Sakit', 'Izin', 'Alpha'].includes(status)) {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
            const select = document.getElementById('modal_m_pengganti');
            if (select) select.value = '';
        }
    }
</script>
