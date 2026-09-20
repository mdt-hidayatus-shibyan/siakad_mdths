@section('title', 'Input Nilai Massal (Leger)')

<x-app-layout>
    <!-- HEADER & FILTER -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <div class="w-full xl:w-auto shrink-0">
            @include('nilai-ujian.menu')
        </div>

        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('nilai-ujian.input-leger') }}" method="GET" id="formSelector"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[190px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-sm"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formSelector').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}" {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Ujian -->
                <div class="relative w-full sm:w-[190px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-file-earmark-check text-sm"></i>
                    </div>
                    <select name="ujian_id" {{ $daftarUjian->isEmpty() ? 'disabled' : '' }}
                        onchange="document.getElementById('formSelector').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer disabled:opacity-50">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach ($daftarUjian as $uj)
                            <option value="{{ $uj->id }}" {{ request('ujian_id') == $uj->id ? 'selected' : '' }}>
                                {{ $uj->nama_ujian }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-4 group/btn">
                        <i class="bi bi-arrow-repeat text-sm"></i>
                        <span>Load Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AREA KONTEN UTAMA -->
    @if (request('ruangan_id') && request('ujian_id'))

        @if ($jadwals->isEmpty() || $murids->isEmpty())
            <!-- STATE TIDAK MEMENUHI SYARAT -->
            <div class="m3-glass-card py-12 text-center border-rose-200/80 dark:border-rose-900/50">
                <div
                    class="w-12 h-12 bg-rose-50 dark:bg-rose-950/40 text-rose-500 border border-rose-200/60 dark:border-rose-800/40 rounded-xl flex items-center justify-center text-xl mb-3 mx-auto shadow-2xs">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h3 class="text-base font-black text-rose-700 dark:text-rose-400 tracking-tight">Data Tidak Memenuhi
                    Syarat</h3>
                <p class="text-xs font-bold text-rose-600/80 dark:text-rose-400/80 mt-1 max-w-md mx-auto">
                    @if ($murids->isEmpty())
                        Tidak ada data murid aktif di ruangan kelas ini.
                    @elseif ($jadwals->isEmpty())
                        Jadwal mata pelajaran untuk ujian ini belum diatur.
                    @endif
                </p>
                <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mt-1">
                    Silakan lengkapi data master terlebih dahulu agar matriks leger dapat dibuat.
                </p>
            </div>
        @else
            <div class="relative z-10 animate-[modalFadeIn_0.2s_ease-out]">

                <form action="{{ route('nilai-ujian.input-leger.store') }}" method="POST"
                    class="relative z-10 flex flex-col gap-4" id="formNilaiLeger">
                    @csrf
                    <input type="hidden" name="ruangan_id" value="{{ $ruanganTerpilih->id }}">
                    <input type="hidden" name="ujian_id" value="{{ $ujianTerpilih->id }}">

                    <!-- 1. KARTU HEADER -->
                    <div
                        class="m3-glass-card px-5 py-4 flex flex-col md:flex-row justify-between md:items-center gap-3">
                        <div class="flex items-center gap-3.5">
                            <div
                                class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40 flex items-center justify-center shrink-0 hidden sm:flex shadow-2xs">
                                <i class="bi bi-grid-3x3-gap-fill text-lg"></i>
                            </div>
                            <div>
                                <h3
                                    class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-snug">
                                    Matriks Leger Nilai Massal
                                </h3>
                                <p
                                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center mt-0.5">
                                    <i class="bi bi-door-open mr-1.5 opacity-80"></i> Kelas:
                                    <span class="ml-1 text-zinc-800 dark:text-zinc-200 font-black">
                                        {{ $ruanganTerpilih->nama_ruangan }}
                                    </span>
                                    <span class="mx-2 text-zinc-300 dark:text-zinc-700">•</span>
                                    <i class="bi bi-file-earmark-text mr-1.5 opacity-80"></i> Ujian:
                                    <span class="ml-1 text-zinc-800 dark:text-zinc-200 font-black">
                                        {{ $ujianTerpilih->nama_ujian }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. AREA SPREADSHEET TABEL -->
                    <div class="m3-glass-card overflow-hidden relative">
                        <div class="overflow-auto max-h-[600px] custom-scrollbar relative z-0">
                            <table class="w-full text-left border-separate border-spacing-0 min-w-max">
                                <!-- HEADER TABEL -->
                                <thead>
                                    <tr
                                        class="bg-zinc-50/95 dark:bg-zinc-950/95 border-b border-zinc-200/80 dark:border-zinc-800">
                                        <!-- Sticky Number -->
                                        <th
                                            class="p-0 align-middle text-center text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider sticky top-0 left-0 bg-zinc-50/95 dark:bg-zinc-950/95 backdrop-blur-md border-r border-b border-zinc-200/80 dark:border-zinc-800 z-40 w-12 min-w-[3rem] max-w-[3rem] md:w-16 md:min-w-[4rem] md:max-w-[4rem]">
                                            No
                                        </th>

                                        <!-- Sticky Nama Murid -->
                                        <th
                                            class="px-3 py-3 align-middle text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider sticky top-0 left-12 md:left-16 bg-zinc-50/95 dark:bg-zinc-950/95 backdrop-blur-md border-r border-b border-zinc-200/80 dark:border-zinc-800 z-40 w-[180px] min-w-[180px] max-w-[180px] md:w-[260px] md:min-w-[260px] md:max-w-[260px]">
                                            Nama Murid
                                        </th>

                                        <!-- Iterasi Header Kolom Mapel -->
                                        @foreach ($jadwals as $jadwal)
                                            @php $namaMapel = $jadwal->mata_pelajaran_id ? ($jadwal->mataPelajaran->nama_mapel ?? '-') : $jadwal->nama_mata_pelajaran_custom; @endphp
                                            <th
                                                class="p-2 text-center sticky top-0 z-30 border-r border-b border-zinc-200/80 dark:border-zinc-800 min-w-[75px] bg-zinc-100/80 dark:bg-zinc-900/90 text-zinc-700 dark:text-zinc-300">
                                                <div
                                                    class="text-[9px] uppercase font-bold text-zinc-400 dark:text-zinc-500">
                                                    Mapel</div>
                                                <div class="text-xs font-black truncate max-w-[80px] mx-auto mt-0.5"
                                                    title="{{ $namaMapel }}">
                                                    {{ strlen($namaMapel) > 12 ? substr($namaMapel, 0, 10) . '...' : $namaMapel }}
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <!-- BODY TABEL -->
                                <tbody>
                                    @foreach ($murids as $murid)
                                        @php
                                            $isTidakIkut = $murid->tidak_ikut_ujian ?? false;
                                            $isLocked = $murid->is_locked;

                                            $rowBg = $isTidakIkut
                                                ? 'bg-zinc-100/40 dark:bg-zinc-800/30 opacity-75'
                                                : ($isLocked
                                                    ? 'bg-rose-50/20 dark:bg-rose-950/20'
                                                    : 'hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors');

                                            $stickyBg = $isTidakIkut
                                                ? 'bg-zinc-100 dark:bg-zinc-800'
                                                : ($isLocked
                                                    ? 'bg-rose-50 dark:bg-rose-950/30'
                                                    : 'bg-white dark:bg-zinc-900 group-hover/row:bg-zinc-50 dark:group-hover/row:bg-zinc-800/80');
                                        @endphp
                                        <tr class="{{ $rowBg }} group/row">

                                            <!-- No -->
                                            <td
                                                class="p-0 text-center sticky left-0 {{ $stickyBg }} z-20 border-r border-b border-zinc-200/80 dark:border-zinc-800 transition-colors w-12 min-w-[3rem] max-w-[3rem] md:w-16 md:min-w-[4rem] md:max-w-[4rem] align-middle">
                                                <div
                                                    class="w-7 h-7 md:w-8 md:h-8 mx-auto rounded-lg {{ $isTidakIkut ? 'bg-zinc-200/80 dark:bg-zinc-800 text-zinc-500' : ($isLocked ? 'bg-rose-100 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 text-rose-500' : 'bg-zinc-100/80 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200/80 dark:border-zinc-700') }} flex items-center justify-center font-bold shrink-0 text-[10px] md:text-xs">
                                                    @if ($isTidakIkut)
                                                        <i class="bi bi-person-x-fill text-rose-500"></i>
                                                    @elseif ($isLocked)
                                                        <i class="bi bi-lock-fill"></i>
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Nama Murid -->
                                            <td
                                                class="px-3 py-2 text-xs sticky left-12 md:left-16 {{ $stickyBg }} z-20 border-r border-b border-zinc-200/80 dark:border-zinc-800 transition-colors w-[180px] min-w-[180px] max-w-[180px] md:w-[260px] md:min-w-[260px] md:max-w-[260px] align-middle">
                                                <div class="font-bold {{ $isTidakIkut ? 'text-zinc-500 dark:text-zinc-400 line-through' : ($isLocked ? 'text-rose-700 dark:text-rose-400' : 'text-zinc-900 dark:text-zinc-100') }} truncate max-w-[150px] md:max-w-[230px]"
                                                    title="{{ $murid->nama_lengkap }}">
                                                    {{ $murid->nama_lengkap }}
                                                </div>
                                                @if ($isTidakIkut)
                                                    <div class="text-[9px] font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 tracking-wider uppercase truncate max-w-[150px] md:max-w-[230px]"
                                                        title="{{ $murid->alasan_tidak_ikut ?? 'Tidak mengikuti ujian' }}">
                                                        {{ $murid->alasan_tidak_ikut ?? 'Tidak Ikut Ujian' }}
                                                    </div>
                                                @elseif ($isLocked)
                                                    <div class="text-[9px] font-bold text-rose-500 dark:text-rose-400 mt-0.5 tracking-wider uppercase truncate max-w-[150px] md:max-w-[230px]"
                                                        title="{{ $murid->lock_reason }}">
                                                        {{ $murid->lock_reason }}
                                                    </div>
                                                @else
                                                    <div
                                                        class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 mt-0.5 tracking-wider uppercase">
                                                        {{ $murid->nism ?? 'NISM KOSONG' }}
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Input Matrix Nilai -->
                                            @foreach ($jadwals as $jadwal)
                                                @php $existingNilai = $nilaiMatrix[$murid->id][$jadwal->id] ?? ''; @endphp
                                                <td
                                                    class="p-1 text-center border-r border-b border-zinc-200/80 dark:border-zinc-800 align-middle {{ $isTidakIkut ? 'bg-zinc-100/30 dark:bg-zinc-800/20' : ($isLocked ? 'bg-rose-50/20 dark:bg-rose-950/20' : '') }}">
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        name="nilai[{{ $murid->id }}][{{ $jadwal->id }}]"
                                                        value="{{ $existingNilai }}"
                                                        placeholder="{{ $isTidakIkut ? 'Tdk Ikut' : '-' }}"
                                                        {{ $isLocked ? 'disabled readonly' : '' }}
                                                        title="{{ $isLocked ? $murid->lock_reason : 'Input Nilai' }}"
                                                        class="w-14 h-8.5 p-0 mx-auto text-center rounded-lg text-xs font-black outline-none transition-all hide-arrows
                                                        {{ $isTidakIkut
                                                            ? 'bg-zinc-200/50 dark:bg-zinc-800/40 border border-zinc-300/80 dark:border-zinc-700/40 text-zinc-400 text-[10px] cursor-not-allowed'
                                                            : ($isLocked
                                                                ? 'bg-rose-100/50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 text-rose-400 cursor-not-allowed placeholder:text-rose-300 dark:placeholder:text-rose-800'
                                                                : 'm3-input-glass text-center !px-0') }}">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. KARTU FOOTER STICKY -->
                    <div
                        class="sticky bottom-3 z-40 m3-glass-card p-3.5 sm:p-4 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-3 backdrop-blur-xl">

                        <!-- BAGIAN KIRI: Teks Info Dinamis -->
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div
                                class="hidden sm:flex w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-500 items-center justify-center border border-blue-200/80 dark:border-blue-800/40 shrink-0">
                                <i class="bi bi-info-circle-fill text-sm"></i>
                            </div>
                            <p
                                class="text-xs text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-wider leading-relaxed">
                                @hasanyrole('administrator|staff')
                                    Pastikan nilai <span class="text-emerald-500 font-black">telah dicek ulang</span>
                                    sebelum dipublikasikan.
                                @else
                                    Nilai akan berstatus <span class="text-amber-500 font-black">DRAFT</span> dan menunggu
                                    rilis Admin.
                                @endhasanyrole
                            </p>
                        </div>

                        <!-- BAGIAN KANAN: Tombol Aksi Utama -->
                        <div class="flex flex-col sm:flex-row gap-2.5 w-full md:w-auto shrink-0">
                            @hasanyrole('administrator|staff')
                                <!-- TOMBOL DRAFT -->
                                <button type="submit" name="action" value="draft"
                                    class="h-10 inline-flex items-center justify-center px-4 rounded-xl bg-zinc-100/80 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200/80 dark:hover:bg-zinc-800 text-xs font-black transition-all hover:scale-[1.02] active:scale-95 outline-none border border-zinc-200/80 dark:border-zinc-800 shadow-2xs gap-1.5">
                                    <i class="bi bi-journal-bookmark-fill text-sm"></i>
                                    <span>Simpan Draft</span>
                                </button>

                                <!-- TOMBOL PUBLISH -->
                                <button type="submit" name="action" value="publish"
                                    class="m3-btn-primary h-10 px-5 group/btn">
                                    <i class="bi bi-send-check-fill text-sm"></i>
                                    <span>Publish Rapor</span>
                                </button>
                            @else
                                <!-- TOMBOL DRAFT SAJA (Ustadz) -->
                                <button type="submit" name="action" value="draft"
                                    class="m3-btn-primary h-10 px-5 group/btn">
                                    <i class="bi bi-save2-fill text-sm"></i>
                                    <span>Simpan Nilai</span>
                                </button>
                            @endhasanyrole
                        </div>
                    </div>
                </form>
            </div>
        @endif
    @else
        <!-- STATE AWAL PANDUAN PENGGUNAAN -->
        <x-empty-state icon="bi-grid-3x3-gap" title="Pilih Ruangan Kelas & Pelaksanaan Ujian" @endif

</x-app-layout>
