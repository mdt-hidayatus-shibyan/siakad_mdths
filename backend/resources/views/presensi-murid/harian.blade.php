@section('title', 'Harian - Presensi Murid')
<x-app-layout>

    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-murid.menu')
        </div>

        <!-- Area Form Pencarian -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-murid.index') }}" method="GET"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs">

                <!-- Filter Tanggal -->
                <div class="relative w-full sm:w-44 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-date text-xs"></i>
                    </div>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" required
                        class="m3-input-glass w-full !pl-9 !pr-3 text-xs font-bold cursor-pointer">
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-48 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-xs"></i>
                    </div>
                    <select name="ruangan_id" required
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Ruangan --</option>
                        @foreach ($ruangans as $r)
                            <option value="{{ $r->id }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ $ruangan_id == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Jam Ke -->
                <div class="relative w-full sm:w-36 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-clock-history text-xs"></i>
                    </div>
                    <select name="jam_ke" required
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Jam --</option>
                        @foreach ($jamList as $j)
                            <option value="{{ $j }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ $jam_ke == $j ? 'selected' : '' }}>
                                Jam {{ $j }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Tombol Submit Tampilkan -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs group/btn">
                        <i class="bi bi-search text-xs mr-1"></i>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    @if (!empty($isUjian))
        <!-- BANNER INFO JADWAL/MASA UJIAN -->
        <div
            class="mb-6 m3-glass-card p-4 md:p-5 border-violet-500/30 bg-violet-500/10 relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-2xl flex items-center justify-center text-xl shrink-0 border border-violet-500/30">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-md bg-violet-500/20 text-violet-700 dark:text-violet-300 font-extrabold text-[10px] uppercase tracking-wider">
                            Masa Ujian Madrasah
                        </span>
                    </div>
                    <h4 class="text-sm font-black text-zinc-900 dark:text-white mt-0.5">
                        Tanggal ini bertepatan dengan {{ $namaUjian ?? 'Ujian Madrasah' }}
                    </h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                        Presensi KBM reguler dialihkan ke Presensi Ujian murid dan pengawas ruangan.
                    </p>
                </div>
            </div>
            <a href="{{ route('presensi-ujian.input', ['ruangan_id' => $ruangan_id, 'ujian_id' => $ujianId]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs shadow-md transition-all shrink-0">
                <i class="bi bi-box-arrow-up-right text-xs"></i>
                <span>Buka Presensi Ujian</span>
            </a>
        </div>
    @endif

    @if ($ruangan_id && $jam_ke)
        @if ($isLibur)
            <!-- STATE HARI LIBUR -->
            <div class="m3-glass-card p-8 md:p-12 text-center relative z-10 border-rose-500/30 bg-rose-500/5">
                <div
                    class="w-14 h-14 bg-rose-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-rose-500/20 text-rose-500 text-2xl shadow-2xs">
                    <i class="bi bi-brightness-high-fill"></i>
                </div>
                <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 mb-1 tracking-tight">Hari Libur Madrasah
                </h3>
                <div
                    class="inline-block bg-white/80 dark:bg-black/40 py-1.5 px-4 rounded-xl border border-rose-500/20 mt-2">
                    <p class="text-xs font-black text-rose-600 dark:text-rose-400 tracking-wide uppercase">
                        Keterangan: {{ $keteranganLibur }}
                    </p>
                </div>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-3">Form presensi dinonaktifkan pada hari
                    libur.</p>
            </div>
        @elseif (!empty($isUjian))
            <!-- STATE MASA UJIAN MADRASAH (FORM KBM DINONAKTIFKAN & DIALIHKAN KE PRESENSI UJIAN) -->
            <div
                class="m3-glass-card p-8 md:p-12 text-center relative z-10 border-violet-500/30 bg-violet-500/5 shadow-2xs">
                <div
                    class="w-16 h-16 bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-violet-500/30 text-3xl shadow-sm">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <h3 class="text-xl font-black text-zinc-900 dark:text-white mb-1 tracking-tight">Masa Ujian Madrasah
                </h3>
                <div
                    class="inline-block bg-white/80 dark:bg-black/40 py-1.5 px-4 rounded-xl border border-violet-500/20 mt-2">
                    <p class="text-xs font-black text-violet-700 dark:text-violet-300 tracking-wide uppercase">
                        Sedang Berlangsung: {{ $namaUjian ?? 'Ujian Madrasah' }}
                    </p>
                </div>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-3 max-w-md mx-auto">
                    Presensi KBM reguler dinonaktifkan pada tanggal pelaksanaan ujian. Silakan gunakan modul Presensi
                    Ujian untuk mendata kehadiran murid.
                </p>
                <div class="mt-6">
                    <a href="{{ route('presensi-ujian.input', ['ruangan_id' => $ruangan_id, 'ujian_id' => $ujianId]) }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs shadow-md transition-all">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Buka Halaman Presensi Ujian</span>
                    </a>
                </div>
            </div>
        @elseif (!$jadwal)
            <!-- STATE TIDAK ADA JADWAL -->
            <div class="py-16 text-center m3-glass-card relative z-10">
                <div
                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Tidak Ada Jadwal
                </h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                    Kelas ini tidak memiliki jadwal pada hari {{ $hari_ini }} Jam ke-{{ $jam_ke }}.
                </p>
            </div>
        @elseif($murids->isEmpty())
            <!-- STATE KELAS KOSONG -->
            <div class="py-16 text-center m3-glass-card relative z-10">
                <div
                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Kelas Kosong</h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">Belum ada murid yang
                    terdaftar di ruangan ini.</p>
            </div>
        @else
            <!-- ============================================== -->
            <!-- FORM INPUT PRESENSI (DENSE LIST) -->
            <!-- ============================================== -->
            <div id="data-table-container">
                <form action="{{ route('presensi-murid.storeHarian') }}" method="POST" class="ajax-post relative z-10"
                    data-refresh-target="#data-table-container">
                    @csrf
                    <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    <div class="m3-glass-card overflow-hidden relative">

                        <!-- Header List Presensi -->
                        <div
                            class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-4 flex flex-col md:flex-row justify-between md:items-center gap-4 relative z-10">
                            <div class="flex items-center gap-3.5">
                                <div
                                    class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-lg shrink-0">
                                    <i class="bi bi-journal-bookmark-fill"></i>
                                </div>
                                <div>
                                    <h3
                                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-tight uppercase mb-0.5">
                                        {{ $jadwal->mataPelajaran->nama_mapel }}
                                    </h3>
                                    <p
                                        class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest flex items-center">
                                        <i class="bi bi-person-fill mr-1 opacity-70"></i> Pengajar: <span
                                            class="text-zinc-700 dark:text-zinc-300 ml-1">{{ $jadwal->ustadz->nama_lengkap }}</span>
                                    </p>
                                </div>
                            </div>

                            <div
                                class="text-left md:text-right flex items-center md:items-end gap-2 md:flex-col md:gap-1.5">
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="setSemuaPresensi('Hadir')"
                                        class="px-2.5 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black rounded-lg transition-colors flex items-center gap-1">
                                        <i class="bi bi-check-all"></i>
                                        <span>Hadirkan Semua</span>
                                    </button>
                                    <button type="button" onclick="kosongkanSemuaPresensi()"
                                        class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black rounded-lg transition-colors flex items-center gap-1">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        <span>Kosongkan</span>
                                    </button>
                                </div>
                                <span
                                    class="block text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
                                    <i class="bi bi-calendar-event mr-1"></i>
                                    {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }} • Jam
                                    Ke-{{ $jam_ke }}
                                </span>
                            </div>
                        </div>

                        <!-- Area List Murid (Padat / Dense) -->
                        <div class="p-0">
                            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                @foreach ($murids as $murid)
                                    @php
                                        $statusSekarang = $presensiTersimpan->has($murid->id)
                                            ? $presensiTersimpan[$murid->id]->status
                                            : null;
                                    @endphp

                                    <li
                                        class="px-5 py-3 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">

                                        <!-- Info Identitas -->
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div
                                                class="w-7 h-7 rounded-lg {{ $statusSekarang ? 'bg-primary/10 border-primary/20 text-primary dark:text-primary-dark font-black' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 font-bold' }} border flex items-center justify-center shrink-0 text-[11px]">
                                                {{ $loop->iteration }}
                                            </div>
                                            <div class="flex-1 min-w-0 pr-2">
                                                <div class="flex items-center gap-2">
                                                    <h4
                                                        class="font-black text-xs text-zinc-900 dark:text-white truncate">
                                                        {{ $murid->nama_lengkap }}
                                                    </h4>
                                                    @if (!$statusSekarang)
                                                        <span id="badge-status-{{ $murid->id }}"
                                                            class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                                                            Belum Diisi
                                                        </span>
                                                    @else
                                                        <span id="badge-status-{{ $murid->id }}"
                                                            class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                                                            {{ $statusSekarang }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <p
                                                    class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider truncate font-mono mt-0.5">
                                                    NISM: {{ $murid->nism ?? '-' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Pilihan Kehadiran Radio Buttons -->
                                        <div class="shrink-0 w-full md:w-auto">
                                            <!-- Container kotak pill radio -->
                                            <div
                                                class="grid grid-cols-5 gap-1 w-full md:w-[280px] bg-zinc-100/80 dark:bg-zinc-950/60 p-1 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                                                @foreach (['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alpha' => 'A', 'Dispen' => 'D'] as $val => $label)
                                                    <label class="cursor-pointer relative block w-full text-center">
                                                        <input type="radio" name="presensi[{{ $murid->id }}]"
                                                            value="{{ $val }}"
                                                            class="sr-only presensi-radio-{{ $murid->id }}"
                                                            {{ $statusSekarang == $val ? 'checked' : '' }}
                                                            onchange="updateStatusBadge('{{ $murid->id }}', '{{ $val }}')">
                                                        <div
                                                            class="btn-presensi-opt opt-{{ strtolower($val) }} !py-1.5 !text-[11px]">
                                                            {{ $label }}
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Footer / Tombol Simpan -->
                        @can('create presensi-murid')
                            <div
                                class="px-5 py-3.5 bg-zinc-50/80 dark:bg-zinc-950/70 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row justify-between sm:items-center gap-3 z-20 sticky bottom-0">
                                <div class="text-xs font-bold text-zinc-500 dark:text-zinc-400">
                                    Total: {{ $murids->count() }} Murid
                                </div>
                                <button type="submit"
                                    class="m3-btn-primary w-full md:w-auto h-10 px-6 text-xs group/btn">
                                    <i class="bi bi-check2-circle text-sm mr-1"></i>
                                    <span>Simpan Presensi Jam Ini</span>
                                </button>
                            </div>
                        @endcan
                    </div>
                </form>
            </div>

            <script>
                function updateStatusBadge(muridId, status) {
                    const badge = document.getElementById('badge-status-' + muridId);
                    if (badge) {
                        badge.textContent = status;
                        badge.className =
                            'px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider';
                    }
                }

                function setSemuaPresensi(status) {
                    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
                        radio.checked = true;
                        const match = radio.className.match(/presensi-radio-(\d+)/);
                        if (match && match[1]) {
                            updateStatusBadge(match[1], status);
                        }
                    });
                }

                function kosongkanSemuaPresensi() {
                    document.querySelectorAll('input[type="radio"]').forEach(radio => {
                        radio.checked = false;
                        const match = radio.className.match(/presensi-radio-(\d+)/);
                        if (match && match[1]) {
                            const badge = document.getElementById('badge-status-' + match[1]);
                            if (badge) {
                                badge.textContent = 'Belum Diisi';
                                badge.className =
                                    'px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider';
                            }
                        }
                    });
                }
            </script>
        @endif
    @else
        <div class="py-16 text-center m3-glass-card relative z-10">
            <div
                class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                <i class="bi bi-funnel"></i>
            </div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Pilih Filter Terlebih
                Dahulu</h3>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                Silakan pilih Tanggal, Ruangan, dan Jam Pelajaran di atas untuk mulai menginput data presensi.
            </p>
        </div>
    @endif
</x-app-layout>
