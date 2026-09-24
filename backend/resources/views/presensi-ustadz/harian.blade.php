@section('title', 'Harian - Presensi Ustadz')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-ustadz.menu')
        </div>

        <!-- Area Form Pencarian -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-ustadz.index') }}" method="GET"
                class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto" id="formFilter">

                <!-- Filter Tanggal -->
                <div class="w-full sm:w-auto flex-1">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-calendar-date-fill text-xs"></i>
                        </div>
                        <input type="date" name="tanggal" value="{{ $tanggal }}" required
                            class="m3-input-glass w-full !pl-9 text-xs font-bold cursor-pointer">
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="w-full sm:w-auto flex-1">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-door-open-fill text-xs"></i>
                        </div>
                        <select name="ruangan_id" required
                            class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="" class="text-zinc-500">-- Pilih Ruangan --</option>
                            @foreach ($ruangans as $ruangan)
                                <option value="{{ $ruangan->id }}" {{ $ruangan_id == $ruangan->id ? 'selected' : '' }}>
                                    {{ $ruangan->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px] font-black"></i>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit"
                        class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5">
                        <i class="bi bi-search"></i> <span>Tampilkan</span>
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
                        Presensi mengajar reguler dialihkan ke Presensi Pengawas Ujian di ruang ujian.
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

    <!-- AREA HASIL PENCARIAN -->
    @if ($ruangan_id)
        @if ($isLibur)
            <!-- STATE HARI LIBUR -->
            <div
                class="m3-glass-card !bg-rose-500/10 !border-rose-500/20 p-8 md:p-12 text-center relative z-10 shadow-2xs">
                <div
                    class="w-14 h-14 bg-rose-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-rose-500/30 text-rose-500">
                    <i class="bi bi-brightness-high-fill text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 mb-1 tracking-tight">Hari Libur Madrasah
                </h3>
                <div
                    class="inline-block bg-white/60 dark:bg-black/40 py-1.5 px-4 rounded-xl border border-rose-500/20 mt-2">
                    <p class="text-xs font-black text-rose-600 dark:text-rose-400 tracking-wide uppercase">
                        Keterangan: {{ $keteranganLibur }}
                    </p>
                </div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-3">Form presensi dinonaktifkan pada
                    hari libur.</p>
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
                    Presensi mengajar reguler dinonaktifkan pada tanggal pelaksanaan ujian. Silakan gunakan modul
                    Presensi Pengawas Ujian untuk mencatat kehadiran dan berita acara.
                </p>
                <div class="mt-6">
                    <a href="{{ route('presensi-ujian.input', ['ruangan_id' => $ruangan_id, 'ujian_id' => $ujianId]) }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs shadow-md transition-all">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Buka Presensi Pengawas Ujian</span>
                    </a>
                </div>
            </div>
        @elseif ($jadwals->isEmpty())
            <!-- STATE TIDAK ADA JADWAL -->
            <div class="col-span-full">
                <x-empty-state icon="bi-calendar-x" title="Tidak Ada Jadwal"
                    message="Tidak ditemukan jadwal pelajaran di ruangan ini pada tanggal yang Anda pilih." />
            </div>
        @else
            <!-- FORM PENGISIAN PRESENSI (GRID KIRI-KANAN) -->
            <div id="data-grid-container">
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 md:gap-6 relative z-10">

                    <!-- KOLOM KIRI (FORM INPUT) -->
                    <div class="xl:col-span-8 m3-glass-card overflow-hidden relative flex flex-col shadow-2xs">

                        <!-- Header Kiri -->
                        <div
                            class="px-5 py-3.5 border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-100/50 dark:bg-zinc-800/40 relative z-10 flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-sm shrink-0">
                                <i class="bi bi-ui-checks-grid"></i>
                            </div>
                            <h3 class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider">
                                Form Input Presensi
                            </h3>
                        </div>

                        <form action="{{ route('presensi-ustadz.storeHarian') }}" method="POST"
                            class="ajax-post flex-1 flex flex-col relative z-10"
                            data-refresh-target="#data-grid-container">
                            @csrf
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                            <input type="hidden" name="ruangan_id" value="{{ $ruangan_id }}">

                            <!-- Daftar Jadwal -->
                            <div class="p-4 sm:p-5 space-y-4">
                                @foreach ($jadwals as $jadwal)
                                    @php
                                        $daftarPengampu = $jadwal->daftar_ustadz;
                                        if ($daftarPengampu->isEmpty()) {
                                            $daftarPengampu = $jadwal->ustadz ? collect([$jadwal->ustadz]) : collect();
                                        }
                                        $isTeam = $daftarPengampu->count() > 1;
                                    @endphp

                                    <!-- Solid Card Tiap Jadwal Slot -->
                                    <div
                                        class="p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/40 dark:bg-zinc-900/30 transition-all shadow-2xs space-y-3.5">

                                        <!-- Identitas Jadwal Header -->
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-zinc-200/80 dark:border-zinc-800 relative z-10">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="inline-block px-2 py-0.5 bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 text-[9px] font-black rounded-lg uppercase tracking-wider">
                                                        Jam Ke-{{ $jadwal->jam_ke }}
                                                    </span>
                                                    @if ($isTeam)
                                                        <span
                                                            class="inline-block px-2 py-0.5 bg-sky-500/10 text-sky-700 dark:text-sky-300 border border-sky-500/20 text-[9px] font-black rounded-lg uppercase tracking-wider">
                                                            <i class="bi bi-people-fill mr-1"></i> Team Teaching
                                                            ({{ $daftarPengampu->count() }} Guru)
                                                        </span>
                                                    @endif
                                                </div>
                                                <h4
                                                    class="text-sm font-black text-zinc-900 dark:text-white tracking-tight">
                                                    {{ $jadwal->mataPelajaran->nama_mapel }}
                                                </h4>
                                            </div>
                                        </div>

                                        <!-- Sub-Card per Ustadz Pengampu -->
                                        <div class="space-y-3">
                                            @foreach ($daftarPengampu as $u)
                                                @php
                                                    $riwayat = $riwayatPresensi->get($jadwal->id . '_' . $u->id);
                                                    $currentStatus = $riwayat ? $riwayat->status : 'Hadir';
                                                    $currentPengganti = $riwayat ? $riwayat->ustadz_pengganti_id : '';
                                                    $currentKeterangan = $riwayat ? $riwayat->keterangan : '';
                                                    $isUtama =
                                                        ($u->pivot->is_utama ?? false) || $u->id == $jadwal->ustadz_id;
                                                @endphp

                                                <div
                                                    class="p-3.5 rounded-xl border {{ $isUtama ? 'border-emerald-500/20 bg-emerald-50/20 dark:bg-emerald-950/10' : 'border-sky-500/20 bg-sky-50/20 dark:bg-sky-950/10' }} relative z-10 space-y-2.5">

                                                    <!-- Nama Guru & Badge Peran -->
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <div
                                                                class="w-7 h-7 rounded-lg {{ $isUtama ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-sky-500/15 text-sky-700 dark:text-sky-400' }} flex items-center justify-center text-xs shrink-0 font-black">
                                                                <i
                                                                    class="bi {{ $isUtama ? 'bi-person-fill' : 'bi-people-fill' }}"></i>
                                                            </div>
                                                            <div class="truncate">
                                                                <p
                                                                    class="text-xs font-black text-zinc-900 dark:text-white truncate">
                                                                    {{ $u->nama_lengkap }}
                                                                </p>
                                                                <p
                                                                    class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500">
                                                                    {{ $u->nigm ? 'NIGM: ' . $u->nigm : 'Ustadz' }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            @if ($isUtama)
                                                                <span
                                                                    class="inline-flex items-center gap-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300/40">
                                                                    <i
                                                                        class="bi bi-star-fill text-[8px] text-amber-500"></i>
                                                                    Guru Utama
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="inline-flex items-center gap-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-sky-100 dark:bg-sky-950 text-sky-800 dark:text-sky-300 border border-sky-300/40">
                                                                    <i class="bi bi-people text-[8px]"></i> Pendamping
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <input type="hidden"
                                                        name="presensi[{{ $jadwal->id }}][{{ $u->id }}][ustadz_id]"
                                                        value="{{ $u->id }}">

                                                    <!-- Form Inputs (Grid) -->
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 relative z-10">

                                                        <!-- Select Status (Menggunakan Radio Button) -->
                                                        <div class="col-span-1 md:col-span-2">
                                                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5">
                                                                <!-- Opsi Hadir -->
                                                                <label class="relative cursor-pointer">
                                                                    <input type="radio"
                                                                        name="presensi[{{ $jadwal->id }}][{{ $u->id }}][status]"
                                                                        value="Hadir"
                                                                        class="status-radio sr-only peer"
                                                                        {{ $currentStatus == 'Hadir' ? 'checked' : '' }}>
                                                                    <div
                                                                        class="w-full flex items-center justify-center py-1.5 px-1 text-[11px] font-black rounded-lg border transition-all shadow-2xs
                                                                    bg-white/60 dark:bg-black/40 border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400
                                                                    hover:bg-emerald-500/10
                                                                    peer-checked:bg-emerald-500/15 peer-checked:border-emerald-500 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400">
                                                                        Hadir
                                                                    </div>
                                                                </label>

                                                                <!-- Opsi Sakit -->
                                                                <label class="relative cursor-pointer">
                                                                    <input type="radio"
                                                                        name="presensi[{{ $jadwal->id }}][{{ $u->id }}][status]"
                                                                        value="Sakit"
                                                                        class="status-radio sr-only peer"
                                                                        {{ $currentStatus == 'Sakit' ? 'checked' : '' }}>
                                                                    <div
                                                                        class="w-full flex items-center justify-center py-1.5 px-1 text-[11px] font-black rounded-lg border transition-all shadow-2xs
                                                                    bg-white/60 dark:bg-black/40 border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400
                                                                    hover:bg-blue-500/10
                                                                    peer-checked:bg-blue-500/15 peer-checked:border-blue-500 peer-checked:text-blue-600 dark:peer-checked:text-blue-400">
                                                                        Sakit
                                                                    </div>
                                                                </label>

                                                                <!-- Opsi Izin -->
                                                                <label class="relative cursor-pointer">
                                                                    <input type="radio"
                                                                        name="presensi[{{ $jadwal->id }}][{{ $u->id }}][status]"
                                                                        value="Izin"
                                                                        class="status-radio sr-only peer"
                                                                        {{ $currentStatus == 'Izin' ? 'checked' : '' }}>
                                                                    <div
                                                                        class="w-full flex items-center justify-center py-1.5 px-1 text-[11px] font-black rounded-lg border transition-all shadow-2xs
                                                                    bg-white/60 dark:bg-black/40 border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400
                                                                    hover:bg-amber-500/10
                                                                    peer-checked:bg-amber-500/15 peer-checked:border-amber-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400">
                                                                        Izin
                                                                    </div>
                                                                </label>

                                                                <!-- Opsi Alpha -->
                                                                <label class="relative cursor-pointer">
                                                                    <input type="radio"
                                                                        name="presensi[{{ $jadwal->id }}][{{ $u->id }}][status]"
                                                                        value="Alpha"
                                                                        class="status-radio sr-only peer"
                                                                        {{ $currentStatus == 'Alpha' ? 'checked' : '' }}>
                                                                    <div
                                                                        class="w-full flex items-center justify-center py-1.5 px-1 text-[11px] font-black rounded-lg border transition-all shadow-2xs
                                                                    bg-white/60 dark:bg-black/40 border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400
                                                                    hover:bg-rose-500/10
                                                                    peer-checked:bg-rose-500/15 peer-checked:border-rose-500 peer-checked:text-rose-600 dark:peer-checked:text-rose-400">
                                                                        Alpha
                                                                    </div>
                                                                </label>

                                                                <!-- Opsi Kosong -->
                                                                <label
                                                                    class="relative cursor-pointer col-span-2 sm:col-span-1">
                                                                    <input type="radio"
                                                                        name="presensi[{{ $jadwal->id }}][{{ $u->id }}][status]"
                                                                        value="Kosong"
                                                                        class="status-radio sr-only peer"
                                                                        {{ $currentStatus == 'Kosong' ? 'checked' : '' }}>
                                                                    <div
                                                                        class="w-full flex items-center justify-center py-1.5 px-1 text-[11px] font-black rounded-lg border transition-all shadow-2xs
                                                                    bg-white/60 dark:bg-black/40 border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400
                                                                    hover:bg-zinc-100 dark:hover:bg-zinc-800
                                                                    peer-checked:bg-zinc-500/10 peer-checked:border-zinc-500 peer-checked:text-zinc-800 dark:peer-checked:text-white">
                                                                        Kosong
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <!-- Select Guru Pengganti (Toggled via JS) -->
                                                        <div
                                                            class="pengganti-wrapper col-span-1 md:col-span-2 {{ in_array($currentStatus, ['Sakit', 'Izin', 'Alpha']) ? 'block' : 'hidden' }}">
                                                            <label
                                                                class="block text-[10px] font-black text-amber-600 dark:text-amber-500 uppercase tracking-wider mb-1">
                                                                Digantikan Oleh (Badal)
                                                            </label>
                                                            <div class="relative">
                                                                <select
                                                                    name="presensi[{{ $jadwal->id }}][{{ $u->id }}][ustadz_pengganti_id]"
                                                                    class="m3-input-glass w-full !pr-8 text-xs font-bold text-amber-800 dark:text-amber-400 !border-amber-500/30 cursor-pointer appearance-none">
                                                                    <option value="" class="text-zinc-500">--
                                                                        Pilih
                                                                        Guru Pengganti --</option>
                                                                    @foreach ($semuaGuru as $guru)
                                                                        @if ($guru->id != $u->id)
                                                                            <option value="{{ $guru->id }}"
                                                                                {{ $currentPengganti == $guru->id ? 'selected' : '' }}>
                                                                                {{ $guru->nama_lengkap }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                <div
                                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-amber-500">
                                                                    <i
                                                                        class="bi bi-chevron-down text-[10px] font-black"></i>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Input Keterangan -->
                                                        <div class="col-span-1 md:col-span-2">
                                                            <input type="text"
                                                                name="presensi[{{ $jadwal->id }}][{{ $u->id }}][keterangan]"
                                                                value="{{ $currentKeterangan }}"
                                                                placeholder="Keterangan materi / alasan izin (Opsional)..."
                                                                class="m3-input-glass w-full text-xs font-medium">
                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                            <!-- Footer Kiri / Submit -->
                            <div
                                class="px-5 py-3.5 border-t border-zinc-200/80 dark:border-zinc-800 bg-zinc-100/50 dark:bg-zinc-800/40 flex justify-end mt-auto relative z-10">
                                @can('create presensi-ustadz')
                                    <button type="submit"
                                        class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5">
                                        <i class="bi bi-save2-fill text-xs"></i> <span>Simpan Data Presensi</span>
                                    </button>
                                @endcan
                            </div>
                        </form>
                    </div>

                    <!-- KOLOM KANAN (STATUS HARI INI) -->
                    <div class="xl:col-span-4">
                        <div class="m3-glass-card overflow-hidden sticky top-6 relative shadow-2xs">

                            <!-- Header Kanan -->
                            <div
                                class="px-5 py-3.5 border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-100/50 dark:bg-zinc-800/40 relative z-10 flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-sm shrink-0">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h3 class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider">
                                    Status Hari Ini
                                </h3>
                            </div>

                            <div class="p-4 space-y-3 relative z-10">
                                @foreach ($jadwals as $jadwal)
                                    @php
                                        $daftarPengampu = $jadwal->daftar_ustadz;
                                        if ($daftarPengampu->isEmpty()) {
                                            $daftarPengampu = $jadwal->ustadz ? collect([$jadwal->ustadz]) : collect();
                                        }
                                    @endphp

                                    <div
                                        class="p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/40 dark:bg-zinc-900/30 transition-all shadow-2xs space-y-2">
                                        <div class="flex justify-between items-start">
                                            <span
                                                class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                                                <i class="bi bi-clock mr-1"></i> Jam {{ $jadwal->jam_ke }}
                                            </span>
                                            <span
                                                class="text-[10px] font-black text-zinc-800 dark:text-zinc-200 truncate max-w-[140px]"
                                                title="{{ $jadwal->mataPelajaran->nama_mapel }}">
                                                {{ $jadwal->mataPelajaran->nama_mapel }}
                                            </span>
                                        </div>

                                        <div class="space-y-2 pt-1 border-t border-zinc-100 dark:border-zinc-800/60">
                                            @foreach ($daftarPengampu as $u)
                                                @php
                                                    $riwayat = $riwayatPresensi->get($jadwal->id . '_' . $u->id);
                                                    $isUtama =
                                                        ($u->pivot->is_utama ?? false) || $u->id == $jadwal->ustadz_id;

                                                    if ($riwayat) {
                                                        $badgeColor = match ($riwayat->status) {
                                                            'Hadir'
                                                                => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                                            'Sakit'
                                                                => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                                            'Izin'
                                                                => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                            'Alpha'
                                                                => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                                            'Kosong'
                                                                => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                                            default => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                                                        };
                                                    } else {
                                                        $badgeColor =
                                                            'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700';
                                                    }
                                                @endphp

                                                <div
                                                    class="p-2 rounded-lg bg-zinc-50/60 dark:bg-zinc-950/40 border border-zinc-200/60 dark:border-zinc-800/60 text-xs">
                                                    <div class="flex items-center justify-between gap-1 mb-1">
                                                        <div class="flex items-center gap-1 truncate min-w-0">
                                                            <i
                                                                class="bi {{ $isUtama ? 'bi-star-fill text-amber-500 text-[8px]' : 'bi-person text-[10px] text-sky-500' }}"></i>
                                                            <span
                                                                class="font-bold text-[11px] text-zinc-800 dark:text-zinc-200 truncate"
                                                                title="{{ $u->nama_lengkap }}">
                                                                {{ $u->nama_lengkap }}
                                                            </span>
                                                        </div>

                                                        @if ($riwayat)
                                                            <div class="flex items-center gap-1 shrink-0">
                                                                <span
                                                                    class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase tracking-wider border shadow-2xs {{ $badgeColor }}">
                                                                    {{ $riwayat->status }}
                                                                </span>

                                                                @can('hapus presensi-ustadz')
                                                                    <form
                                                                        action="{{ route('presensi-ustadz.destroyHarian', $riwayat->id) }}"
                                                                        method="POST" class="delete-ajax inline-block"
                                                                        data-refresh-target="#data-grid-container">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="w-4.5 h-4.5 flex items-center justify-center rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 transition-all outline-none"
                                                                            title="Batal / Hapus Presensi">
                                                                            <i class="bi bi-trash3 text-[8px]"></i>
                                                                        </button>
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        @else
                                                            <span
                                                                class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase tracking-wider border {{ $badgeColor }} shrink-0">
                                                                Belum Diisi
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($riwayat && $riwayat->guruPengganti)
                                                        <div
                                                            class="text-[9px] font-bold text-amber-600 dark:text-amber-400 mt-0.5 flex items-center gap-1">
                                                            <i class="bi bi-arrow-return-right text-[8px]"></i>
                                                            <span>Badal:
                                                                {{ $riwayat->guruPengganti->nama_lengkap }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    @else
        <!-- State Awal (Belum Pilih Filter) -->
        <div class="col-span-full">
            <x-empty-state icon="bi-door-open" title="Pilih Ruangan"
                message="Silakan tentukan ruangan/kelas pada filter di atas untuk memuat daftar jadwal guru yang mengajar hari ini." />
        </div>
    @endif

    <script>
        $(document).on('change', '.status-radio', function() {
            if (this.checked) {
                const container = this.closest('.space-y-2\\.5') || this.closest('.p-3\\.5') || this.closest(
                    '.relative.z-10');
                if (!container) return;

                const penggantiWrapper = container.querySelector('.pengganti-wrapper');
                if (!penggantiWrapper) return;
                const penggantiInput = penggantiWrapper.querySelector('select');
                const status = this.value;

                if (['Sakit', 'Izin', 'Alpha'].includes(status)) {
                    penggantiWrapper.classList.remove('hidden');
                    penggantiWrapper.classList.add('block');
                } else {
                    penggantiWrapper.classList.remove('block');
                    penggantiWrapper.classList.add('hidden');
                    if (penggantiInput) {
                        penggantiInput.value = '';
                    }
                }
            }
        });
    </script>
</x-app-layout>
