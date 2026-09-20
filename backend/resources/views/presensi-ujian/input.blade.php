@section('title', 'Input Presensi Ujian')

<x-app-layout>
    <!-- HEADER & FILTER -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-ujian.menu')
        </div>

        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-ujian.input') }}" method="GET" id="formSelector"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-48 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formSelector').submit()" required
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Ruangan --
                        </option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Ujian -->
                <div class="relative w-full sm:w-52 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-file-earmark-check text-xs"></i>
                    </div>
                    <select name="ujian_id" onchange="document.getElementById('formSelector').submit()" required
                        {{ !$ruanganTerpilih ? 'disabled' : '' }}
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none disabled:opacity-50">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Ujian --
                        </option>
                        @foreach ($daftarUjian as $uj)
                            <option value="{{ $uj->id }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ request('ujian_id') == $uj->id ? 'selected' : '' }}>
                                {{ $uj->nama_ujian }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Jadwal Mapel -->
                <div class="relative w-full sm:w-60 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-journal-bookmark text-xs"></i>
                    </div>
                    <select name="jadwal_ujian_id" onchange="document.getElementById('formSelector').submit()" required
                        {{ $jadwals->isEmpty() ? 'disabled' : '' }}
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none disabled:opacity-50">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Mata Pelajaran
                            --</option>
                        @foreach ($jadwals as $jdw)
                            @php
                                $mapelName = $jdw->mata_pelajaran_id
                                    ? $jdw->mataPelajaran->nama_mapel ?? '-'
                                    : $jdw->nama_mata_pelajaran_custom;
                                $tglFormat = \Carbon\Carbon::parse($jdw->tanggal_ujian)->format('d/m');
                            @endphp
                            <option value="{{ $jdw->id }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ request('jadwal_ujian_id') == $jdw->id ? 'selected' : '' }}>
                                [{{ $tglFormat }}] {{ $mapelName }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- AREA KONTEN UTAMA -->
    @if ($ruanganTerpilih && request('ujian_id') && $jadwalTerpilih)
        @if ($muridsWithStatus->isEmpty())
            <x-empty-state icon="bi-people" title="Ruangan Kosong"
                message="Belum ada murid aktif yang terdaftar di ruangan {{ $ruanganTerpilih->nama_ruangan }}." />
        @else
            @php
                $mapelNama = $jadwalTerpilih->mata_pelajaran_id
                    ? $jadwalTerpilih->mataPelajaran->nama_mapel ?? '-'
                    : $jadwalTerpilih->nama_mata_pelajaran_custom;
                $pengawasUtama = $jadwalTerpilih->pengawas;
                $statusPengawasSekarang = $presensiPengawas ? $presensiPengawas->status : 'Hadir';
                $badalIdSekarang = $presensiPengawas ? $presensiPengawas->ustadz_pengganti_id : null;
                $catatanBeritaAcara = $presensiPengawas ? $presensiPengawas->catatan_berita_acara : null;
            @endphp

            <form action="{{ route('presensi-ujian.store') }}" method="POST" class="relative z-10 space-y-5">
                @csrf
                <input type="hidden" name="ujian_id" value="{{ request('ujian_id') }}">
                <input type="hidden" name="ruangan_id" value="{{ $ruanganTerpilih->id }}">
                <input type="hidden" name="jadwal_ujian_id" value="{{ $jadwalTerpilih->id }}">

                <!-- CARD INFO SESI & PENGAWAS UJIAN -->
                <div class="m3-glass-card p-5 md:p-6 overflow-hidden">
                    <div
                        class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-zinc-200/80 dark:border-zinc-800">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span
                                        class="px-2.5 py-0.5 bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                        {{ $ruanganTerpilih->nama_ruangan }}
                                    </span>
                                    <span
                                        class="px-2.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                        <i class="bi bi-calendar-event mr-1"></i>
                                        {{ \Carbon\Carbon::parse($jadwalTerpilih->tanggal_ujian)->translatedFormat('l, d F Y') }}
                                    </span>
                                    <span
                                        class="px-2.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                        <i class="bi bi-clock mr-1"></i>
                                        {{ \Carbon\Carbon::parse($jadwalTerpilih->waktu_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($jadwalTerpilih->waktu_selesai)->format('H:i') }} WIB
                                    </span>
                                </div>
                                <h2 class="font-black text-xl text-zinc-900 dark:text-white uppercase tracking-tight">
                                    {{ $mapelNama }}
                                </h2>
                            </div>
                        </div>

                        <!-- Tombol Cepat Set Semua Hadir & Cetak -->
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="setSemuaHadir()"
                                class="h-9 px-3.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/50 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all shadow-2xs">
                                <i class="bi bi-check-all text-base"></i>
                                <span>Set Semua Hadir</span>
                            </button>

                            <a href="{{ route('presensi-ujian.cetak-dhpu', ['ujian_id' => request('ujian_id'), 'ruangan_id' => $ruanganTerpilih->id, 'jadwal_ujian_id' => $jadwalTerpilih->id, 'mode' => 'terisi']) }}"
                                target="_blank"
                                class="h-9 px-3.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all shadow-2xs">
                                <i class="bi bi-printer text-xs"></i>
                                <span>Cetak DHPU</span>
                            </a>
                        </div>
                    </div>

                    <!-- BLOK PENGAWAS & BERITA ACARA -->
                    <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="{ statusPengawas: '{{ $statusPengawasSekarang }}' }">
                        <input type="hidden" name="pengawas[ustadz_id]" value="{{ $pengawasUtama?->id }}">

                        <!-- Kolom 1: Info Pengawas Terjadwal & Status -->
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                                Pengawas Terjadwal:
                            </label>
                            <div
                                class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div class="truncate">
                                        <h4 class="font-black text-xs text-zinc-900 dark:text-white truncate">
                                            {{ $pengawasUtama?->nama_lengkap ?? 'Belum Ditentukan' }}
                                        </h4>
                                        <span class="text-[10px] font-bold text-zinc-400">Pengawas Utama</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2.5">
                                <label
                                    class="block text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-1">
                                    Status Kehadiran Pengawas:
                                </label>
                                <select name="pengawas[status]" x-model="statusPengawas"
                                    class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                                    <option value="Hadir">Hadir (Sesuai Jadwal)</option>
                                    <option value="Digantikan">Digantikan (Badal)</option>
                                    <option value="Izin">Izin Tidak Hadir</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Alpha">Alpha</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kolom 2: Ustadz Badal / Pengganti -->
                        <div x-show="statusPengawas === 'Digantikan'" x-transition>
                            <label
                                class="block text-[11px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                                Ustadz Pengganti (Badal):
                            </label>
                            <div class="relative group/select">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                                    <i class="bi bi-person-check text-xs"></i>
                                </div>
                                <select name="pengawas[ustadz_pengganti_id]"
                                    class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                                    <option value="">-- Pilih Ustadz Pengganti --</option>
                                    @foreach ($daftarUstadz as $ust)
                                        <option value="{{ $ust->id }}"
                                            {{ $badalIdSekarang == $ust->id ? 'selected' : '' }}>
                                            {{ $ust->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                                </div>
                            </div>
                            <p class="text-[10px] font-bold text-zinc-400 mt-1">
                                *Pilih ustadz yang menggantikan pengawasan di ruangan ini.
                            </p>
                        </div>

                        <!-- Kolom 3: Catatan Berita Acara -->
                        <div :class="statusPengawas === 'Digantikan' ? 'lg:col-span-1' : 'lg:col-span-2'">
                            <label
                                class="block text-[11px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                                Catatan Berita Acara Ujian:
                            </label>
                            <textarea name="pengawas[catatan_berita_acara]" rows="3"
                                placeholder="Contoh: Ujian berjalan tertib dan lancar. 1 murid izin karena sakit..."
                                class="m3-input-glass w-full text-xs font-semibold resize-none">{{ $catatanBeritaAcara }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- CARD LIST MURID (DENSE / COMPACT) -->
                <div class="m3-glass-card overflow-hidden">
                    <div
                        class="px-5 py-3.5 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                        <span
                            class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="bi bi-people-fill text-primary dark:text-primary-dark text-sm"></i>
                            Daftar Peserta Ujian ({{ $muridsWithStatus->count() }} Murid)
                        </span>
                        <div class="flex items-center gap-3 flex-wrap justify-between sm:justify-end">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="setSemuaPresensi('Hadir')"
                                    class="px-2.5 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black rounded-lg transition-colors flex items-center gap-1 cursor-pointer">
                                    <i class="bi bi-check-all"></i>
                                    <span>Hadirkan Semua</span>
                                </button>
                                <button type="button" onclick="kosongkanSemuaPresensi()"
                                    class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black rounded-lg transition-colors flex items-center gap-1 cursor-pointer">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    <span>Kosongkan</span>
                                </button>
                            </div>
                            <div class="hidden md:flex items-center gap-3 text-[11px] font-bold text-zinc-500">
                                <span class="flex items-center gap-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                    Hadir</span>
                                <span class="flex items-center gap-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Sakit</span>
                                <span class="flex items-center gap-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> Izin</span>
                                <span class="flex items-center gap-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Alpha</span>
                                <span class="flex items-center gap-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-purple-500 inline-block"></span>
                                    Dispen</span>
                            </div>
                        </div>
                    </div>

                    <ul class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @foreach ($muridsWithStatus as $murid)
                            @php
                                $pExisting = $presensiExisting->get($murid->id);
                                $statusSekarang = $pExisting ? $pExisting->status : null;
                                $catatanSekarang = $pExisting ? $pExisting->catatan : null;
                                $isTidakIkut = $murid->tidak_ikut_ujian ?? false;
                                $isLocked = $murid->is_locked ?? false;
                                $lockReason = $murid->lock_reason ?? 'Lunas Administrasi';
                            @endphp

                            <li
                                class="p-4 sm:px-5 sm:py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3.5 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors {{ $isTidakIkut ? 'bg-zinc-50/40 dark:bg-zinc-900/30 opacity-75' : '' }}">
                                <!-- Info Identitas Murid -->
                                <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center font-bold text-zinc-500 dark:text-zinc-400 shrink-0 text-xs">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-black text-sm text-zinc-900 dark:text-white truncate">
                                                {{ $murid->nama_lengkap }}
                                            </h4>

                                            @if ($isTidakIkut)
                                                <span
                                                    class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-300 dark:border-zinc-700"
                                                    title="{{ $murid->alasan_tidak_ikut }}">
                                                    <i class="bi bi-person-slash mr-0.5"></i> Tidak Ikut Ujian
                                                </span>
                                            @else
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

                                                @if ($isLocked)
                                                    <span
                                                        class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800"
                                                        title="{{ $lockReason }}">
                                                        <i class="bi bi-exclamation-triangle mr-0.5"></i>
                                                        {{ $lockReason }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                        <i class="bi bi-check-circle mr-0.5"></i> Terpenuhi
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <p
                                            class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider truncate font-mono mt-0.5">
                                            NISM: {{ $murid->nism ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Area Pilihan Presensi & Catatan -->
                                <div class="flex flex-col sm:flex-row items-center gap-2.5 shrink-0">
                                    @if ($isTidakIkut)
                                        <div
                                            class="w-full sm:w-[280px] py-1.5 px-3 rounded-xl bg-zinc-100/90 dark:bg-zinc-800/80 text-center text-xs font-bold text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center gap-1.5 shadow-2xs">
                                            <i class="bi bi-lock-fill text-zinc-400"></i>
                                            <span>Dikecualikan
                                                ({{ $murid->alasan_tidak_ikut ?? 'Tidak Ikut Ujian' }})</span>
                                        </div>
                                        <div class="w-full sm:w-44">
                                            <input type="text" disabled value="Tidak Ikut Ujian"
                                                class="m3-input-glass !py-1 !px-2.5 text-xs font-medium w-full opacity-60 cursor-not-allowed bg-zinc-100 dark:bg-zinc-800">
                                        </div>
                                    @else
                                        <!-- Radio Buttons Kehadiran -->
                                        <div
                                            class="grid grid-cols-5 gap-1 w-full sm:w-[280px] bg-zinc-100/80 dark:bg-zinc-950/60 p-1 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                                            @foreach (['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alpha' => 'A', 'Dispensasi' => 'D'] as $val => $label)
                                                <label class="cursor-pointer relative block w-full text-center">
                                                    <input type="radio"
                                                        name="presensi[{{ $murid->id }}][status]"
                                                        value="{{ $val }}"
                                                        class="peer sr-only presensi-radio-{{ $val }} presensi-radio-{{ $murid->id }}"
                                                        data-murid-id="{{ $murid->id }}"
                                                        {{ $statusSekarang == $val ? 'checked' : '' }}
                                                        onchange="updateStatusBadge('{{ $murid->id }}', '{{ $val }}')">

                                                    <div
                                                        class="w-full py-1.5 flex items-center justify-center text-[11px] font-black rounded-lg transition-all duration-200 border border-transparent text-zinc-400 dark:text-zinc-500 hover:bg-white dark:hover:bg-zinc-800
                                                    {{ $val == 'Hadir' ? 'peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-2xs dark:peer-checked:bg-emerald-600' : '' }}
                                                    {{ $val == 'Sakit' ? 'peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-2xs dark:peer-checked:bg-blue-600' : '' }}
                                                    {{ $val == 'Izin' ? 'peer-checked:bg-amber-500 peer-checked:text-white peer-checked:shadow-2xs dark:peer-checked:bg-amber-600' : '' }}
                                                    {{ $val == 'Alpha' ? 'peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-2xs dark:peer-checked:bg-rose-600' : '' }}
                                                    {{ $val == 'Dispensasi' ? 'peer-checked:bg-purple-500 peer-checked:text-white peer-checked:shadow-2xs dark:peer-checked:bg-purple-600' : '' }}
                                                    ">
                                                        {{ $label }}
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <!-- Catatan Murid (Opsional) -->
                                        <div class="w-full sm:w-44">
                                            <input type="text" name="presensi[{{ $murid->id }}][catatan]"
                                                value="{{ $catatanSekarang }}" placeholder="Catatan..."
                                                class="m3-input-glass !py-1 !px-2.5 text-xs font-medium w-full">
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <!-- STICKY FOOTER ACTION BUTTON -->
                    <div
                        class="px-5 py-4 bg-zinc-50/90 dark:bg-zinc-950/80 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row justify-between items-center gap-3 sticky bottom-0 z-30 backdrop-blur-md">
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">
                            Pastikan data kehadiran pengawas dan murid telah diperiksa sebelum disimpan.
                        </span>
                        <button type="submit"
                            class="m3-btn-primary w-full sm:w-auto h-10 px-6 text-xs group/btn shrink-0">
                            <i class="bi bi-check2-circle text-base mr-1"></i>
                            <span>Simpan Presensi Sesi Ini</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    @else
        <x-empty-state icon="bi-person-check" title="Pilih Sesi Ujian"
            message="Silakan pilih Ruangan, Pelaksanaan Ujian, dan Mata Pelajaran di atas untuk membuka formulir presensi ujian." />
    @endif

    @push('script')
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
                    const muridId = radio.getAttribute('data-murid-id');
                    if (muridId) {
                        updateStatusBadge(muridId, status);
                    }
                });
            }

            function kosongkanSemuaPresensi() {
                document.querySelectorAll('input[type="radio"][name^="presensi"]').forEach(radio => {
                    radio.checked = false;
                    const muridId = radio.getAttribute('data-murid-id');
                    if (muridId) {
                        const badge = document.getElementById('badge-status-' + muridId);
                        if (badge) {
                            badge.textContent = 'Belum Diisi';
                            badge.className =
                                'px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider';
                        }
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
