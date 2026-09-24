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
                                                @foreach ($asatidzs as $asatidz)
                                                    <option value="{{ $asatidz->id }}"
                                                        {{ $ustadzUtamaId == $asatidz->id ? 'selected' : '' }}>
                                                        ({{ $asatidz->nigm }})
                                                        - {{ $asatidz->nama_lengkap }}
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
                                                data-placeholder="-- Tambah Pendamping --">
                                                @foreach ($asatidzs as $asatidz)
                                                    <option value="{{ $asatidz->id }}"
                                                        {{ in_array($asatidz->id, $ustadzPendampingIds) ? 'selected' : '' }}>
                                                        ({{ $asatidz->nigm }})
                                                        - {{ $asatidz->nama_lengkap }}
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
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--default .select2-selection--single,
            .select2-container--default .select2-selection--multiple {
                min-height: 38px !important;
                border-radius: 0.75rem !important;
                border: 1px solid rgba(228, 228, 231, 0.8) !important;
                background-color: #ffffff;
                display: flex;
                align-items: center;
                font-size: 12px;
                font-weight: 600;
                transition: all 0.2s ease;
            }

            .select2-container--default .select2-selection--single:focus,
            .select2-container--default.select2-container--open .select2-selection--single,
            .select2-container--default .select2-selection--multiple:focus,
            .select2-container--default.select2-container--open .select2-selection--multiple {
                border-color: #146C2E !important;
                box-shadow: 0 0 0 3px rgba(20, 108, 46, 0.15);
                outline: none;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #18181b !important;
                padding-left: 0.75rem !important;
                padding-right: 2rem !important;
                line-height: normal !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__rendered {
                padding: 2px 6px !important;
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
            }

            /* Chip Pendamping - Sky Blue Theme */
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #f0f9ff !important;
                border: 1px solid #bae6fd !important;
                color: #0369a1 !important;
                border-radius: 0.5rem !important;
                padding: 1px 6px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                margin: 0 !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: #0369a1 !important;
                margin-right: 4px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
                right: 0.5rem !important;
            }

            .select2-dropdown {
                border-radius: 0.75rem !important;
                border: 1px solid rgba(228, 228, 231, 0.8) !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                font-size: 12px;
                font-weight: 600;
            }

            .select2-search__field {
                border-radius: 0.5rem !important;
                padding: 6px 10px !important;
                outline: none !important;
                border: 1px solid #d4d4d8 !important;
                font-size: 12px;
            }

            .select2-search__field:focus {
                border-color: #146C2E !important;
            }

            .select2-results__option {
                padding: 7px 12px !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #146C2E !important;
                color: white !important;
            }

            /* Dark Mode Select2 */
            .dark .select2-container--default .select2-selection--single,
            .dark .select2-container--default .select2-selection--multiple {
                background-color: #0c0c0e !important;
                border-color: #27272a !important;
            }

            .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #f4f4f5 !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #082f49 !important;
                border-color: #0369a1 !important;
                color: #bae6fd !important;
            }

            .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: #bae6fd !important;
            }

            .dark .select2-dropdown {
                background-color: #0c0c0e !important;
                border-color: #27272a !important;
            }

            .dark .select2-search__field {
                background-color: #18181b !important;
                border-color: #3f3f46 !important;
                color: #fff !important;
            }

            .dark .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #18181b !important;
            }

            .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #22C55E !important;
                color: #000000 !important;
            }
        </style>
    @endpush

    <!-- SCRIPT INITIALIZATION -->
    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                // Inisialisasi Select2
                $('.select2-jadwal').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Pilih Mapel --'
                });

                $('.select2-jadwal-utama').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Pilih Guru Utama --'
                });

                $('.select2-jadwal-pendamping').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Tambah Pendamping --'
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
