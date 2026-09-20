@section('title', 'Input Nilai Ujian')
<x-app-layout>
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('nilai-ujian.menu')
        </div>

        <!-- Area Form Pencarian Toolbar -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET" id="formSelector"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                @php
                    $isLengkap = request('ruangan_id') && request('ujian_id');
                @endphp

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[170px] group/select">
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
                <div class="relative w-full sm:w-[170px] group/select">
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

                <!-- Filter Mapel (Muncul jika lengkap) -->
                @if ($isLengkap)
                    <div class="relative w-full sm:w-[220px] group/select animate-[modalFadeIn_0.2s_ease-out]">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                            <i class="bi bi-book text-sm"></i>
                        </div>
                        <select name="jadwal_ujian_id" onchange="document.getElementById('formSelector').submit()"
                            class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                            <option value="">-- Pilih Mapel --</option>
                            @if (isset($jadwals) && $jadwals->count() > 0)
                                @foreach ($jadwals as $jadwal)
                                    @php
                                        $namaMapel = $jadwal->mata_pelajaran_id
                                            ? $jadwal->mataPelajaran->nama_mapel ?? '-'
                                            : $jadwal->nama_mata_pelajaran_custom;
                                        $tgl = \Carbon\Carbon::parse($jadwal->tanggal_ujian)->format('d/m/y');
                                    @endphp
                                    <option value="{{ $jadwal->id }}"
                                        {{ request('jadwal_ujian_id') == $jadwal->id ? 'selected' : '' }}>
                                        {{ $namaMapel }} ({{ $tgl }})
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>Belum ada jadwal</option>
                            @endif
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                @endif

                <!-- Tombol Submit -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-4 group/btn">
                        <i class="bi bi-search text-sm"></i>
                        <span class="sm:hidden xl:inline">Cari</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AREA LIST FLOATING CARDS -->
    @if (request('ruangan_id') && request('ujian_id') && request('jadwal_ujian_id'))
        <div class="relative z-10 animate-[modalFadeIn_0.2s_ease-out]">

            <form action="{{ route('nilai-ujian.store') }}" method="POST" class="relative z-10 flex flex-col gap-4"
                id="formNilaiUjian">
                @csrf
                <input type="hidden" name="ujian_id" value="{{ request('ujian_id') }}">
                <input type="hidden" name="ruangan_id" value="{{ request('ruangan_id') }}">
                <input type="hidden" name="jadwal_ujian_id" value="{{ request('jadwal_ujian_id') }}">

                <!-- 1. KARTU HEADER -->
                <div class="m3-glass-card px-5 py-4 flex flex-col md:flex-row justify-between md:items-center gap-3">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200/80 dark:border-sky-800/40 flex items-center justify-center shrink-0 hidden sm:flex shadow-2xs">
                            <i class="bi bi-clipboard2-data-fill text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-snug">
                                Lembar Koreksi Nilai Murid
                            </h3>
                            @php
                                $jadwalTerpilih = $jadwals->firstWhere('id', request('jadwal_ujian_id'));
                                $namaMapelTerpilih = $jadwalTerpilih
                                    ? ($jadwalTerpilih->mata_pelajaran_id
                                        ? $jadwalTerpilih->mataPelajaran->nama_mapel ?? '-'
                                        : $jadwalTerpilih->nama_mata_pelajaran_custom)
                                    : '-';
                            @endphp
                            <p class="text-xs font-bold text-sky-600 dark:text-sky-400 flex items-center mt-0.5">
                                <i class="bi bi-journal-text mr-1.5 opacity-80"></i> Mapel:
                                <span class="ml-1 text-zinc-700 dark:text-zinc-300 font-extrabold">
                                    {{ $namaMapelTerpilih }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. AREA LIST FLOATING CARDS -->
                <div class="flex flex-col gap-2.5">

                    <!-- HEADER BARIS (Desktop) -->
                    <div
                        class="hidden lg:grid grid-cols-[1fr_120px_250px] gap-6 px-5 py-1 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        <div>Data Murid</div>
                        <div class="text-center">Nilai Angka</div>
                        <div>Status & Syarat Admin</div>
                    </div>

                    @foreach ($muridsWithStatus as $murid)
                        @php
                            $itemNilai = $nilaiExisting->get($murid->id);
                            $currentScore = $itemNilai ? $itemNilai->nilai : '';
                            $isPublished = $itemNilai ? $itemNilai->is_published : false;
                            $isTidakIkut = $murid->tidak_ikut_ujian ?? false;
                            $isLocked = $murid->is_locked;
                        @endphp

                        <!-- FLOATING CARD ITEM -->
                        <div
                            class="m3-glass-card p-3.5 sm:p-4 transition-all duration-200 {{ $isTidakIkut ? 'border-zinc-300 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/30 opacity-80' : ($isLocked ? 'border-rose-200/80 dark:border-rose-900/50 bg-rose-50/30 dark:bg-rose-950/20' : 'hover:border-primary/40 dark:hover:border-primary-dark/40') }} grid grid-cols-1 lg:grid-cols-[1fr_120px_250px] items-start lg:items-center gap-3.5 lg:gap-6">

                            <!-- A. KIRI: Info Murid -->
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-9 h-9 rounded-xl {{ $isTidakIkut ? 'bg-zinc-200/80 dark:bg-zinc-800 text-zinc-500' : 'bg-zinc-100/80 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400' }} border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-center text-xs font-black shrink-0">
                                    {{ $loop->iteration }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <h4
                                        class="font-black text-sm {{ $isTidakIkut ? 'text-zinc-600 dark:text-zinc-400 line-through' : 'text-zinc-900 dark:text-white' }} tracking-tight leading-tight truncate">
                                        {{ $murid->nama_lengkap }}
                                    </h4>
                                    <div
                                        class="inline-flex items-center gap-1.5 text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mt-0.5">
                                        <i class="bi bi-person-badge text-xs"></i>
                                        {{ $murid->nism ?? 'NISM KOSONG' }}
                                    </div>
                                </div>
                            </div>

                            <!-- B. TENGAH: Input Nilai -->
                            <div
                                class="flex items-center justify-between lg:justify-center border-t lg:border-t-0 border-zinc-200/80 dark:border-zinc-800 pt-2.5 lg:pt-0 relative w-full">
                                <span
                                    class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider lg:hidden">Nilai:</span>

                                <input type="number" name="nilai[{{ $murid->id }}]" value="{{ $currentScore }}"
                                    min="0" max="100" placeholder="{{ $isTidakIkut ? 'Tdk Ikut' : '-' }}"
                                    {{ $isLocked ? 'disabled' : '' }}
                                    class="w-20 lg:w-[84px] h-9.5 text-center text-base font-black rounded-xl outline-none transition-all disabled:cursor-not-allowed
                                {{ $isTidakIkut ? 'bg-zinc-200/50 dark:bg-zinc-800/40 border border-zinc-300/80 dark:border-zinc-700/40 text-zinc-400 text-xs' : ($isLocked ? 'bg-rose-100/50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 text-rose-400' : 'm3-input-glass') }}">
                            </div>

                            <!-- C. KANAN: Status & Syarat Admin -->
                            <div
                                class="flex flex-col gap-1.5 border-t lg:border-t-0 border-zinc-200/80 dark:border-zinc-800 pt-2.5 lg:pt-0 relative">
                                <div class="flex flex-wrap items-center gap-1">
                                    @if ($isTidakIkut)
                                        <span
                                            class="inline-flex items-center gap-1 bg-zinc-200/80 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                            <i class="bi bi-person-x-fill text-xs text-rose-500"></i> Tidak Ikut Ujian
                                        </span>
                                    @elseif ($isLocked)
                                        <span
                                            class="inline-flex items-center gap-1 bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/40 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                            <i class="bi bi-lock-fill text-xs"></i> Belum Terpenuhi
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                            <i class="bi bi-unlock-fill text-xs"></i> Terpenuhi
                                        </span>
                                    @endif

                                    @if ($itemNilai)
                                        @if ($isPublished)
                                            <span
                                                class="inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200/80 text-emerald-600 dark:bg-emerald-950/40 dark:border-emerald-800/40 dark:text-emerald-400 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                                <i class="bi bi-check-circle-fill text-xs"></i> Publish
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 bg-amber-50 border border-amber-200/80 text-amber-600 dark:bg-amber-950/40 dark:border-amber-800/40 dark:text-amber-400 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                                <i class="bi bi-pencil-fill text-[9px]"></i> Draft
                                            </span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 bg-zinc-100/80 border border-zinc-200/80 text-zinc-500 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-400 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                            <i class="bi bi-dash-circle-fill text-xs"></i> Kosong
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    @if ($isTidakIkut)
                                        <div
                                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 leading-snug">
                                            {{ $murid->alasan_tidak_ikut ?? 'Tidak mengikuti ujian' }}
                                        </div>
                                    @elseif ($isLocked)
                                        <div class="text-[10px] font-bold text-rose-600 dark:text-rose-400 leading-snug line-clamp-2"
                                            title="{{ $murid->lock_reason }}">
                                            {{ $murid->lock_reason ?? 'Belum melunasi administrasi' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- 3. KARTU FOOTER STICKY -->
                <div
                    class="sticky bottom-3 z-40 m3-glass-card p-3.5 sm:p-4 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-3 backdrop-blur-xl">

                    <!-- BAGIAN KIRI: Teks Info -->
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div
                            class="hidden sm:flex w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-500 items-center justify-center border border-blue-200/80 dark:border-blue-800/40 shrink-0">
                            <i class="bi bi-info-circle-fill text-sm"></i>
                        </div>
                        <p
                            class="text-xs text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-wider leading-relaxed">
                            @hasanyrole('administrator|staff')
                                Pastikan nilai <span class="text-emerald-500 font-black">telah dicek</span> sebelum
                                dipublikasikan ke rapor.
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
    @else
        <!-- STATE AWAL / KOSONG -->
        <x-empty-state icon="bi-people" title="Pilih Ruangan/Kelas dan Mata Pelajaran"
            message="Tentukan ruangan, ujian, dan mata pelajaran pada filter di atas untuk memunculkan lembar input nilai murid." />
    @endif

</x-app-layout>
