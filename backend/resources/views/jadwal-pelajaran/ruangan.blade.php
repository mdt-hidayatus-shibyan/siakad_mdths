@section('title', 'Jadwal Pelajaran ' . $ruangan->nama_ruangan)

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('jadwal-pelajaran.index') }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    Jadwal: {{ $ruangan->nama_ruangan }}
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Atur alokasi mata pelajaran dan asatidz pengajar pada setiap jam pelajaran.
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center w-full md:w-auto">
            @can('update jadwal-pelajaran')
                <form action="{{ route('jadwal-pelajaran.toggle-publikasi', $ruangan->id) }}" method="POST"
                    class="m-0 p-0 bg-white/80 dark:bg-zinc-900 px-4 py-2 rounded-xl shadow-sm border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between sm:justify-center">
                    @csrf @method('PATCH')

                    <label class="relative inline-flex items-center cursor-pointer group mb-0">
                        <input type="checkbox" name="is_jadwal_publik" class="sr-only peer" onchange="this.form.submit()"
                            {{ $ruangan->is_jadwal_publik ? 'checked' : '' }}>
                        <div
                            class="w-9 h-5 bg-zinc-300 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-primary dark:peer-checked:bg-primary-dark transition-colors">
                        </div>
                        <span
                            class="ml-2.5 text-xs font-black uppercase tracking-wider transition-colors {{ $ruangan->is_jadwal_publik ? 'text-primary dark:text-primary-dark' : 'text-zinc-500 dark:text-zinc-400' }}">
                            @if ($ruangan->is_jadwal_publik)
                                <i class="bi bi-eye-fill mr-1"></i> Publik
                            @else
                                <i class="bi bi-eye-slash-fill mr-1"></i> Draft
                            @endif
                        </span>
                    </label>
                </form>
            @endcan
        </div>
    </div>

    @php
        $hariList = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $jamList = [
            'Nadzoman' => '13:45 - 14:00',
            '1' => '14:00 - 14:45',
            '2' => '15:30 - 16:15',
            'Ekstra' => '20:00 - 21:00',
        ];

        // Pre-calculate asatidz display properties untuk performa optimal
        $asatidzData = $asatidzs->map(function ($a) {
            $words = explode(' ', trim($a->nama_lengkap));
            $initials =
                count($words) >= 2
                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                    : strtoupper(substr($a->nama_lengkap, 0, 2));
            return [
                'id' => $a->id,
                'nama' => $a->nama_lengkap,
                'nigm' => $a->nigm ?? '-',
                'kode' => $a->kode_ustadz ?? '-',
                'gender' => $a->jenis_kelamin,
                'foto' => $a->foto ? asset('storage/' . $a->foto) : '',
                'initials' => $initials,
            ];
        });
    @endphp

    <form action="{{ route('jadwal-pelajaran.mass-store', $ruangan->id) }}" method="POST" class="relative z-10"
        enctype="multipart/form-data">
        @csrf

        <div class="m3-glass-card p-4 sm:p-5 relative overflow-hidden">

            <div class="relative z-10 space-y-6 sm:space-y-7">
                @foreach ($hariList as $hari)
                    <div class="space-y-3">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 dark:border-primary-dark/30 shadow-2xs">
                            <i class="bi bi-calendar-event text-xs font-bold"></i>
                            <span class="font-black tracking-wider text-xs uppercase">{{ $hari }}</span>
                        </div>

                        <div class="flex flex-col gap-2.5">
                            @foreach ($jamList as $jam => $waktu)
                                @php
                                    $jamString = (string) $jam;
                                    $jadwal = isset($jadwals[$hari])
                                        ? $jadwals[$hari]->where('jam_ke', $jamString)->first()
                                        : null;
                                    $isFilled = !is_null($jadwal);

                                    $isEkstra = $jamString === 'Ekstra';
                                    $isNadzoman = $jamString === 'Nadzoman';

                                    if ($isEkstra || $isNadzoman) {
                                        $cardBgClass = $isFilled
                                            ? 'bg-purple-50/80 dark:bg-purple-950/20 border-purple-200/80 dark:border-purple-800/40'
                                            : 'bg-zinc-50/50 dark:bg-zinc-900/40 border-zinc-200/70 dark:border-zinc-800/80 border-dashed';
                                        $jamTextClass = 'text-purple-600 dark:text-purple-400';
                                        $iconBgClass =
                                            'bg-purple-100/80 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/40';
                                    } else {
                                        $cardBgClass = $isFilled
                                            ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-800/40'
                                            : 'bg-zinc-50/80 dark:bg-zinc-900/50 border-zinc-200/80 dark:border-zinc-800/80';
                                        $jamTextClass = 'text-primary dark:text-primary-dark';
                                        $iconBgClass =
                                            'bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 dark:border-primary-dark/30';
                                    }
                                @endphp

                                <div
                                    class="flex flex-col lg:flex-row lg:items-center gap-3 p-3 sm:p-3.5 rounded-xl border transition-all duration-200 {{ $cardBgClass }}">

                                    <div class="flex items-center justify-between lg:w-44 shrink-0">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center shrink-0 {{ $iconBgClass }}">
                                                <i class="bi bi-clock-fill text-xs"></i>
                                            </div>
                                            <div>
                                                <div
                                                    class="font-black text-xs sm:text-sm {{ $jamTextClass }} leading-none">
                                                    Jam {{ $jam }}</div>
                                                <div
                                                    class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 tracking-wider">
                                                    {{ $waktu }}</div>
                                            </div>
                                        </div>

                                        @can('hapus jadwal-pelajaran')
                                            <div class="lg:hidden">
                                                <button type="button"
                                                    onclick="resetBaris('{{ $hari }}', '{{ $jam }}')"
                                                    class="w-7.5 h-7.5 rounded-lg bg-zinc-100/80 dark:bg-zinc-800 hover:bg-rose-100 dark:hover:bg-rose-950/40 text-zinc-400 hover:text-rose-500 flex items-center justify-center transition-all active:scale-90 outline-none"
                                                    title="Kosongkan Baris">
                                                    <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                                </button>
                                            </div>
                                        @endcan
                                    </div>

                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-3">

                                        <!-- 1. Pilih Mapel (col-span-12 md:col-span-4) -->
                                        <div class="md:col-span-4">
                                            <label
                                                class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                                <i class="bi bi-book-half text-zinc-500"></i>
                                                <span>Mata Pelajaran</span>
                                            </label>
                                            <select id="mapel_{{ $hari }}_{{ $jam }}"
                                                name="jadwal[{{ $hari }}][{{ $jam }}][mata_pelajaran_id]"
                                                class="select2-jadwal w-full" data-placeholder="-- Pilih Mapel --">
                                                <option value=""></option>
                                                @foreach ($mataPelajarans as $mapel)
                                                    <option value="{{ $mapel->id }}"
                                                        data-kode="{{ $mapel->kode_mapel }}"
                                                        data-nama="{{ $mapel->nama_mapel }}"
                                                        {{ $jadwal && $jadwal->mata_pelajaran_id == $mapel->id ? 'selected' : '' }}>
                                                        ({{ $mapel->kode_mapel }})
                                                        - {{ $mapel->nama_mapel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @php
                                            $ustadzUtamaId = null;
                                            $ustadzPendampingIds = [];
                                            if ($jadwal) {
                                                $allUstadz = $jadwal->ustadzs;
                                                if ($allUstadz->isNotEmpty()) {
                                                    $primary =
                                                        $allUstadz->firstWhere('pivot.is_utama', 1) ??
                                                        ($allUstadz->firstWhere('pivot.is_utama', true) ??
                                                            $allUstadz->first());
                                                    $ustadzUtamaId = $primary ? $primary->id : $jadwal->ustadz_id;
                                                    $ustadzPendampingIds = $allUstadz
                                                        ->where('id', '!=', $ustadzUtamaId)
                                                        ->pluck('id')
                                                        ->toArray();
                                                } else {
                                                    $ustadzUtamaId = $jadwal->ustadz_id;
                                                }
                                            }
                                        @endphp

                                        <!-- 2. Guru Utama (col-span-12 md:col-span-4) -->
                                        <div class="md:col-span-4">
                                            <label
                                                class="block text-[11px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                                                <span class="flex items-center gap-1.5">
                                                    <i class="bi bi-star-fill text-amber-500 text-[10px]"></i>
                                                    <span>Guru Utama</span>
                                                </span>
                                                <span
                                                    class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                    Wajib
                                                </span>
                                            </label>
                                            <select id="guru_utama_{{ $hari }}_{{ $jam }}"
                                                name="jadwal[{{ $hari }}][{{ $jam }}][ustadz_utama_id]"
                                                class="select2-jadwal-utama w-full"
                                                data-placeholder="-- Pilih Guru Utama --">
                                                <option value=""></option>
                                                @foreach ($asatidzData as $u)
                                                    <option value="{{ $u['id'] }}"
                                                        data-nama="{{ $u['nama'] }}"
                                                        data-nigm="{{ $u['nigm'] }}"
                                                        data-kode="{{ $u['kode'] }}"
                                                        data-gender="{{ $u['gender'] }}"
                                                        data-foto="{{ $u['foto'] }}"
                                                        data-initials="{{ $u['initials'] }}"
                                                        {{ $ustadzUtamaId == $u['id'] ? 'selected' : '' }}>
                                                        ({{ $u['nigm'] }})
                                                        - {{ $u['nama'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- 3. Guru Pendamping / Team (col-span-12 md:col-span-4) -->
                                        <div class="md:col-span-4">
                                            <label
                                                class="block text-[11px] font-bold text-sky-800 dark:text-sky-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                                                <span class="flex items-center gap-1.5">
                                                    <i class="bi bi-people-fill text-sky-500 text-xs"></i>
                                                    <span>Guru Pendamping</span>
                                                </span>
                                                <span
                                                    class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60">
                                                    Opsional
                                                </span>
                                            </label>
                                            <select id="guru_pendamping_{{ $hari }}_{{ $jam }}"
                                                name="jadwal[{{ $hari }}][{{ $jam }}][ustadz_pendamping_ids][]"
                                                multiple="multiple" class="select2-jadwal-pendamping w-full"
                                                data-placeholder="-- Tambah Guru Pendamping --">
                                                @foreach ($asatidzData as $u)
                                                    <option value="{{ $u['id'] }}"
                                                        data-nama="{{ $u['nama'] }}"
                                                        data-nigm="{{ $u['nigm'] }}"
                                                        data-kode="{{ $u['kode'] }}"
                                                        data-gender="{{ $u['gender'] }}"
                                                        data-foto="{{ $u['foto'] }}"
                                                        data-initials="{{ $u['initials'] }}"
                                                        {{ in_array($u['id'], $ustadzPendampingIds) ? 'selected' : '' }}>
                                                        ({{ $u['nigm'] }})
                                                        - {{ $u['nama'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>

                                    @can('delete jadwal-pelajaran')
                                        <div class="hidden lg:block shrink-0 px-1">
                                            <button type="button"
                                                onclick="resetBaris('{{ $hari }}', '{{ $jam }}')"
                                                class="w-8.5 h-8.5 rounded-xl bg-zinc-100/80 dark:bg-zinc-800 hover:bg-rose-100 dark:hover:bg-rose-950/40 text-zinc-400 hover:text-rose-500 flex items-center justify-center transition-all active:scale-90 outline-none"
                                                title="Kosongkan Baris">
                                                <i class="bi bi-arrow-counterclockwise text-sm"></i>
                                            </button>
                                        </div>
                                    @endcan

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @can('update jadwal-pelajaran')
            <div class="mt-5 flex justify-end relative z-10">
                <button type="submit" class="m3-btn-primary w-full md:w-auto px-6 py-2.5 group/btn">
                    <i class="bi bi-save2-fill text-sm"></i>
                    <span>Simpan Jadwal</span>
                </button>
            </div>
        @endcan
    </form>

    <!-- STYLING KHUSUS UNTUK SELECT2 (M3 Glassmorphism Theme) -->
    @push('style')
        <style>
            /* ==========================================================================
                   Select2 Modern M3 Theme (Glassmorphism & OLED Dark Mode)
                   ========================================================================== */

            /* 1. Base Container & Single Selection */
            .select2-container--default .select2-selection--single {
                min-height: 38px !important;
                height: 38px !important;
                border-radius: 0.75rem !important;
                border: 1px solid rgba(228, 228, 231, 0.85) !important;
                background-color: #ffffff !important;
                display: flex !important;
                align-items: center !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #146c2e !important;
                box-shadow: 0 0 0 3px rgba(20, 108, 46, 0.15) !important;
                outline: none !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #18181b !important;
                padding-left: 0.75rem !important;
                padding-right: 2rem !important;
                line-height: normal !important;
                display: flex !important;
                align-items: center !important;
                width: 100% !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
                right: 0.5rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* 2. Multiple Selection Container (Guru Pendamping) */
            .select2-container--default .select2-selection--multiple {
                min-height: 38px !important;
                border-radius: 0.75rem !important;
                border: 1px solid rgba(228, 228, 231, 0.85) !important;
                background-color: #ffffff !important;
                display: flex !important;
                align-items: center !important;
                padding: 3px 6px !important;
                font-size: 12px !important;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            .select2-container--default.select2-container--focus .select2-selection--multiple,
            .select2-container--default.select2-container--open .select2-selection--multiple {
                border-color: #0284c7 !important;
                box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
                outline: none !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__rendered {
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: center !important;
                gap: 4px !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            /* 3. Chip / Tag Guru Pendamping (Sky Blue M3 Pill) */
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
                border: 1px solid #bae6fd !important;
                color: #0369a1 !important;
                border-radius: 9999px !important;
                padding: 2.5px 8px 2.5px 6px !important;
                margin: 1.5px 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 4px !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.15s ease !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
                border-color: #7dd3fc !important;
                box-shadow: 0 2px 5px rgba(2, 132, 199, 0.12) !important;
                transform: translateY(-0.5px);
            }

            /* Remove Button on Chips */
            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                position: static !important;
                border: none !important;
                background: rgba(3, 105, 161, 0.12) !important;
                color: #0284c7 !important;
                border-radius: 9999px !important;
                width: 15px !important;
                height: 15px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 12px !important;
                font-weight: 800 !important;
                margin-right: 3px !important;
                margin-left: 0 !important;
                padding: 0 !important;
                cursor: pointer !important;
                transition: all 0.15s ease !important;
                float: none !important;
                line-height: 1 !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
                background: #ef4444 !important;
                color: #ffffff !important;
                transform: scale(1.1);
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
                padding-left: 0 !important;
                padding-right: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
            }

            /* Search input inline */
            .select2-container--default .select2-selection--multiple .select2-search--inline {
                display: inline-flex !important;
                align-items: center !important;
                margin: 0 !important;
            }

            .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
                margin: 0 !important;
                padding: 2px 4px !important;
                font-size: 11.5px !important;
                font-weight: 600 !important;
                height: 24px !important;
                border: none !important;
                background: transparent !important;
                color: #18181b !important;
            }

            .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
                color: #a1a1aa !important;
                font-weight: 500 !important;
            }

            /* 4. Dropdown Container & Search */
            .select2-dropdown {
                border-radius: 0.875rem !important;
                border: 1px solid rgba(228, 228, 231, 0.9) !important;
                box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
                overflow: hidden !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                background-color: #ffffff !important;
                z-index: 9999 !important;
            }

            .select2-dropdown .select2-search--dropdown {
                padding: 6px 8px !important;
                background: #f8fafc;
                border-bottom: 1px solid #f1f5f9;
            }

            .select2-dropdown .select2-search__field {
                border-radius: 0.5rem !important;
                padding: 6px 10px !important;
                outline: none !important;
                border: 1px solid #e2e8f0 !important;
                font-size: 11.5px !important;
                background: #ffffff !important;
                width: 100% !important;
                box-sizing: border-box !important;
                transition: all 0.15s ease;
            }

            .select2-dropdown .select2-search__field:focus {
                border-color: #0284c7 !important;
                box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15) !important;
            }

            /* Results Option */
            .select2-results__options {
                max-height: 230px !important;
                padding: 5px !important;
            }

            .select2-results__option {
                padding: 6px 10px !important;
                border-radius: 0.5rem !important;
                margin-bottom: 2px !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                color: #27272a !important;
                transition: all 0.15s ease !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #0284c7 !important;
                color: #ffffff !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] .select2-text-name {
                color: #ffffff !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] .select2-badge-kode {
                background-color: rgba(255, 255, 255, 0.25) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.4) !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] .select2-badge-nigm {
                color: rgba(255, 255, 255, 0.85) !important;
            }

            .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #f0f9ff !important;
                color: #0369a1 !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected=true] {
                background-color: #0284c7 !important;
                color: #ffffff !important;
            }

            /* Clear Button */
            .select2-container--default .select2-selection--single .select2-selection__clear,
            .select2-container--default .select2-selection--multiple .select2-selection__clear {
                margin-right: 0.5rem !important;
                font-size: 14px !important;
                color: #a1a1aa !important;
                cursor: pointer !important;
                transition: color 0.15s ease !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__clear:hover,
            .select2-container--default .select2-selection--multiple .select2-selection__clear:hover {
                color: #ef4444 !important;
            }

            /* ==========================================================================
                   Dark Mode Overrides
                   ========================================================================== */
            .dark .select2-container--default .select2-selection--single,
            .dark .select2-container--default .select2-selection--multiple {
                background-color: #0c0c0e !important;
                border-color: #27272a !important;
            }

            .dark .select2-container--default.select2-container--focus .select2-selection--single,
            .dark .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #22c55e !important;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2) !important;
            }

            .dark .select2-container--default.select2-container--focus .select2-selection--multiple,
            .dark .select2-container--default.select2-container--open .select2-selection--multiple {
                border-color: #38bdf8 !important;
                box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
            }

            .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #f4f4f5 !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background: linear-gradient(135deg, rgba(8, 47, 73, 0.75) 0%, rgba(12, 74, 110, 0.55) 100%) !important;
                border-color: rgba(56, 189, 248, 0.35) !important;
                color: #bae6fd !important;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
                border-color: rgba(56, 189, 248, 0.6) !important;
                box-shadow: 0 2px 6px rgba(56, 189, 248, 0.2) !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                background: rgba(56, 189, 248, 0.15) !important;
                color: #7dd3fc !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
                background: #f43f5e !important;
                color: #ffffff !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
                color: #f4f4f5 !important;
            }

            .dark .select2-dropdown {
                background-color: #121215 !important;
                border-color: #27272a !important;
                box-shadow: 0 14px 35px -5px rgba(0, 0, 0, 0.6) !important;
            }

            .dark .select2-dropdown .select2-search--dropdown {
                background: #18181b !important;
                border-bottom: 1px solid #27272a !important;
            }

            .dark .select2-dropdown .select2-search__field {
                background-color: #0c0c0e !important;
                border-color: #3f3f46 !important;
                color: #ffffff !important;
            }

            .dark .select2-results__option {
                color: #e4e4e7 !important;
            }

            .dark .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #082f49 !important;
                color: #bae6fd !important;
            }

            .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #0284c7 !important;
                color: #ffffff !important;
            }
        </style>
    @endpush

    <!-- SCRIPT INITIALIZATION -->
    @push('script')
        <script>
            $(document).ready(function() {
                // Helper template formatter untuk option ustadz
                function formatUstadzOption(state) {
                    if (!state.id) return state.text;

                    var $elem = $(state.element);
                    var nama = $elem.data('nama') || state.text;
                    var nigm = $elem.data('nigm');
                    var kode = $elem.data('kode');
                    var foto = $elem.data('foto');
                    var initials = $elem.data('initials') || 'U';
                    var gender = $elem.data('gender');

                    var avatarHtml = '';
                    if (foto) {
                        avatarHtml =
                            '<img src="' + foto +
                            '" class="w-6 h-6 rounded-full object-cover border border-zinc-200 dark:border-zinc-700 shrink-0" onerror="this.style.display=\'none\'" />';
                    } else {
                        var bgClass = gender === 'P' ?
                            'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300' :
                            'bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-300';
                        avatarHtml = '<span class="w-6 h-6 rounded-full ' + bgClass +
                            ' text-[10px] font-black flex items-center justify-center shrink-0">' + initials +
                            '</span>';
                    }

                    var badgeHtml = '';
                    if (kode && kode !== '-') {
                        badgeHtml +=
                            '<span class="select2-badge-kode text-[10px] font-black px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shrink-0">Kode: ' +
                            kode + '</span>';
                    }
                    if (nigm && nigm !== '-') {
                        badgeHtml +=
                            '<span class="select2-badge-nigm text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 shrink-0">#' +
                            nigm + '</span>';
                    }

                    var html = '<div class="flex items-center justify-between gap-2 py-0.5 w-full">' +
                        '<div class="flex items-center gap-2 min-w-0">' +
                        avatarHtml +
                        '<span class="select2-text-name text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate">' +
                        nama + '</span>' +
                        '</div>' +
                        '<div class="flex items-center gap-1.5 shrink-0">' +
                        badgeHtml +
                        '</div>' +
                        '</div>';

                    return $(html);
                }

                // Helper template formatter untuk selection chip guru pendamping (Multiple)
                function formatUstadzPendampingSelection(state) {
                    if (!state.id) return state.text;

                    var $elem = $(state.element);
                    var nama = $elem.data('nama') || state.text;
                    var kode = $elem.data('kode');
                    var initials = $elem.data('initials') || '';
                    var foto = $elem.data('foto');
                    var gender = $elem.data('gender');

                    var avatarTag = '';
                    if (foto) {
                        avatarTag = '<img src="' + foto +
                            '" class="w-4 h-4 rounded-full object-cover shrink-0 inline-block align-middle" onerror="this.style.display=\'none\'" />';
                    } else if (initials) {
                        var bgClass = gender === 'P' ?
                            'bg-rose-200/80 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300' :
                            'bg-sky-200/80 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300';
                        avatarTag = '<span class="w-4 h-4 rounded-full ' + bgClass +
                            ' text-[8px] font-black inline-flex items-center justify-center shrink-0">' +
                            initials + '</span>';
                    }

                    var kodeTag = (kode && kode !== '-') ?
                        '<span class="text-[9px] font-black opacity-80">(' + kode + ')</span>' : '';

                    var html = '<span class="inline-flex items-center gap-1.5 leading-none">' +
                        avatarTag +
                        '<span class="font-bold text-[11px] leading-tight">' + nama + '</span>' +
                        kodeTag +
                        '</span>';

                    return $(html);
                }

                // Helper template formatter untuk selection guru utama (Single)
                function formatUstadzUtamaSelection(state) {
                    if (!state.id) return state.text;

                    var $elem = $(state.element);
                    var nama = $elem.data('nama') || state.text;
                    var kode = $elem.data('kode');
                    var initials = $elem.data('initials') || '';
                    var foto = $elem.data('foto');
                    var gender = $elem.data('gender');

                    var avatarTag = '';
                    if (foto) {
                        avatarTag = '<img src="' + foto +
                            '" class="w-4.5 h-4.5 rounded-full object-cover shrink-0 inline-block align-middle mr-1.5" onerror="this.style.display=\'none\'" />';
                    } else if (initials) {
                        var bgClass = gender === 'P' ?
                            'bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300' :
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300';
                        avatarTag = '<span class="w-4.5 h-4.5 rounded-full ' + bgClass +
                            ' text-[9px] font-black inline-flex items-center justify-center shrink-0 mr-1.5">' +
                            initials + '</span>';
                    }

                    var kodeTag = (kode && kode !== '-') ?
                        ' <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 ml-1 shrink-0">[' +
                        kode + ']</span>' : '';

                    var html = '<span class="inline-flex items-center truncate">' +
                        avatarTag +
                        '<span class="font-bold text-xs text-zinc-900 dark:text-zinc-100 truncate">' +
                        nama + '</span>' +
                        kodeTag +
                        '</span>';

                    return $(html);
                }

                // Helper template formatter untuk option mata pelajaran
                function formatMapelOption(state) {
                    if (!state.id) return state.text;

                    var $elem = $(state.element);
                    var kode = $elem.data('kode');
                    var nama = $elem.data('nama') || state.text;

                    var html = '<div class="flex items-center justify-between gap-2 py-0.5 w-full">' +
                        '<div class="flex items-center gap-2 min-w-0">' +
                        '<span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center justify-center text-xs shrink-0"><i class="bi bi-book-half text-[11px]"></i></span>' +
                        '<span class="select2-text-name text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate">' +
                        nama + '</span>' +
                        '</div>' +
                        (kode ?
                            '<span class="select2-badge-kode text-[10px] font-black px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shrink-0">' +
                            kode + '</span>' : '') +
                        '</div>';

                    return $(html);
                }

                // 1. Inisialisasi Mapel
                $('.select2-jadwal').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Pilih Mapel --',
                    templateResult: formatMapelOption,
                    escapeMarkup: function(m) {
                        return m;
                    }
                });

                // 2. Inisialisasi Guru Utama
                $('.select2-jadwal-utama').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Pilih Guru Utama --',
                    templateResult: formatUstadzOption,
                    templateSelection: formatUstadzUtamaSelection,
                    escapeMarkup: function(m) {
                        return m;
                    }
                });

                // 3. Inisialisasi Guru Pendamping (Multiple Select)
                $('.select2-jadwal-pendamping').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Tambah Guru Pendamping --',
                    templateResult: formatUstadzOption,
                    templateSelection: formatUstadzPendampingSelection,
                    escapeMarkup: function(m) {
                        return m;
                    }
                });
            });

            function resetBaris(hari, jam) {
                $(`#mapel_${hari}_${jam}`).val('').trigger('change');
                $(`#guru_utama_${hari}_${jam}`).val('').trigger('change');
                $(`#guru_pendamping_${hari}_${jam}`).val([]).trigger('change');
            }
        </script>
    @endpush
</x-app-layout>
