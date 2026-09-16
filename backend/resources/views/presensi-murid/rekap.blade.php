@section('title', 'Rekapitulasi - Presensi Murid')

<x-app-layout>

    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10">
        <div class="w-full xl:w-auto shrink-0">
            @include('presensi-murid.menu')
        </div>
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ route('presensi-murid.rekap') }}" method="GET" id="filterFormRekap"
                class="flex flex-col sm:flex-row flex-wrap items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs">

                <!-- Filter Semester -->
                <div class="relative w-full sm:w-52 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-book-half text-xs"></i>
                    </div>
                    <select name="semester_id" required
                        onchange="window.location.href='{{ route('presensi-murid.rekap') }}?semester_id=' + this.value"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Semester --
                        </option>
                        @foreach ($semesters as $s)
                            <option value="{{ $s->id }}"
                                class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                {{ $semester_id == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_semester }} - {{ $s->tahunPelajaran->nama_hijriyah ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-44 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-xs"></i>
                    </div>
                    <select name="ruangan_id" required
                        onchange="window.location.href='{{ route('presensi-murid.rekap') }}?semester_id={{ $semester_id }}&ruangan_id=' + this.value"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Ruangan --
                        </option>
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

                <!-- Filter Bulan (Opsional) -->
                <div class="relative w-full sm:w-40 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-moon-stars text-xs"></i>
                    </div>
                    <select name="bulan_id"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Bulan --
                        </option>
                        @if (isset($bulans))
                            @foreach ($bulans as $b)
                                <option value="{{ $b->id }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                    {{ isset($bulan_id) && $bulan_id == $b->id ? 'selected' : '' }}>
                                    {{ $b->urutan }}. {{ $b->nama_bulan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Jadwal Pelajaran (Opsional) -->
                <div class="relative w-full sm:w-64 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar2-week text-xs"></i>
                    </div>
                    <select name="jadwal_pelajaran_id"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Jadwal Pelajaran
                            --</option>
                        @if (isset($jadwalsRuangan))
                            @foreach ($jadwalsRuangan as $j)
                                <option value="{{ $j->id }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                                    {{ isset($jadwal_pelajaran_id) && $jadwal_pelajaran_id == $j->id ? 'selected' : '' }}>
                                    {{ $j->hari }} (Jam {{ $j->jam_ke }}):
                                    {{ $j->mataPelajaran->nama_mapel ?? '-' }}
                                    ({{ $j->ustadz->nama_lengkap ?? ($j->ustadz->nama ?? '-') }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Tombol Buat Rekap -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs group/btn">
                        <i class="bi bi-file-earmark-bar-graph text-xs mr-1"></i>
                        <span>Buat Rekap</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    @if ($semester_id && $ruangan_id)
        @if ($murids->isEmpty())
            <div class="py-16 text-center m3-glass-card relative z-10">
                <div
                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                    <i class="bi bi-folder-x"></i>
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Tidak Ada Data</h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                    Belum ada murid aktif di ruangan dan semester ini.
                </p>
            </div>
        @else
            <!-- JIKA TIDAK MEMILIH JADWAL SPESIFIK: TAMPILKAN RINGKASAN PER JADWAL PELAJARAN -->
            @if (!$jadwalTerpilih && !empty($rekapPerJadwal))
                <div class="m3-glass-card overflow-hidden mb-6 relative z-10">
                    <div
                        class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-3.5 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black">
                                <i class="bi bi-calendar-range"></i>
                            </div>
                            <div>
                                <h4
                                    class="font-black text-zinc-900 dark:text-white text-xs md:text-sm uppercase tracking-tight">
                                    Rekapitulasi Kehadiran Per Jadwal Pelajaran
                                </h4>
                                <p class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400">
                                    Ruangan: {{ $ruanganTerpilih->nama_ruangan }} • Klik jadwal untuk melihat rincian
                                    presensi murid pada sesi tersebut
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto relative z-10 custom-scrollbar">
                        <table class="m3-table min-w-max text-xs">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2.5 w-12 text-center">No</th>
                                    <th class="px-4 py-2.5">Hari & Jam</th>
                                    <th class="px-5 py-2.5">Mata Pelajaran</th>
                                    <th class="px-4 py-2.5">Guru Pengampu</th>
                                    <th class="px-4 py-2.5 text-center">Sesi</th>
                                    <th class="px-3 py-2.5 text-center text-emerald-600 dark:text-emerald-400">Hadir (H)
                                    </th>
                                    <th class="px-3 py-2.5 text-center text-blue-600 dark:text-blue-400">Sakit (S)</th>
                                    <th class="px-3 py-2.5 text-center text-amber-600 dark:text-amber-400">Izin (I)</th>
                                    <th class="px-3 py-2.5 text-center text-rose-600 dark:text-rose-400">Alpha (A)</th>
                                    <th class="px-3 py-2.5 text-center text-purple-600 dark:text-purple-400">Dispen (D)
                                    </th>
                                    <th class="px-4 py-2.5 text-center">% Kehadiran</th>
                                    <th class="px-4 py-2.5 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rekapPerJadwal as $idx => $rj)
                                    <tr class="group/tr hover:bg-zinc-500/5 transition-colors">
                                        <td class="px-4 py-2.5 text-center font-bold text-zinc-400">{{ $idx + 1 }}
                                        </td>
                                        <td
                                            class="px-4 py-2.5 font-bold text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-[11px]">
                                                <i class="bi bi-clock text-[10px]"></i>
                                                {{ $rj['hari'] }} (Jam {{ $rj['jam_ke'] }})
                                            </span>
                                        </td>
                                        <td class="px-5 py-2.5 font-black text-zinc-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-journal-text text-primary dark:text-primary-dark"></i>
                                                <span>{{ $rj['nama_mapel'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-zinc-600 dark:text-zinc-400 font-bold">
                                            {{ $rj['ustadz'] }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-black">
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-[11px]">
                                                {{ $rj['total_sesi'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-3 py-2.5 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ $rj['H'] }}</td>
                                        <td class="px-3 py-2.5 text-center font-bold text-blue-600 dark:text-blue-400">
                                            {{ $rj['S'] }}</td>
                                        <td
                                            class="px-3 py-2.5 text-center font-bold text-amber-600 dark:text-amber-400">
                                            {{ $rj['I'] }}</td>
                                        <td class="px-3 py-2.5 text-center font-bold text-rose-600 dark:text-rose-400">
                                            {{ $rj['A'] }}</td>
                                        <td
                                            class="px-3 py-2.5 text-center font-bold text-purple-600 dark:text-purple-400">
                                            {{ $rj['D'] }}</td>
                                        <td class="px-4 py-2.5 text-center align-middle">
                                            <div
                                                class="inline-flex items-center gap-1.5 font-black text-[11px] {{ $rj['persen_hadir'] >= 85 ? 'text-emerald-600 dark:text-emerald-400' : ($rj['persen_hadir'] >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                                <span>{{ $rj['persen_hadir'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-center align-middle">
                                            <a href="{{ route('presensi-murid.rekap', [
                                                'semester_id' => $semester_id,
                                                'ruangan_id' => $ruangan_id,
                                                'bulan_id' => $bulan_id,
                                                'jadwal_pelajaran_id' => $rj['jadwal']->id,
                                            ]) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-black bg-primary/10 text-primary dark:text-primary-dark hover:bg-primary hover:text-white dark:hover:bg-primary-dark dark:hover:text-zinc-900 transition-all">
                                                <span>Lihat Rekap</span>
                                                <i class="bi bi-arrow-right text-[10px]"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- WRAPPER TABEL REKAP MURID -->
            <div class="m3-glass-card overflow-hidden mb-6 relative z-10">

                <!-- Header Tabel -->
                <div
                    class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 px-5 md:px-6 py-4 flex flex-col md:flex-row justify-between md:items-center gap-4 relative z-10">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3
                                class="font-black text-zinc-900 dark:text-white text-sm md:text-base uppercase tracking-tight">
                                @if ($jadwalTerpilih)
                                    REKAP JADWAL:
                                    {{ $jadwalTerpilih->mataPelajaran->nama_mapel ?? 'JADWAL PELAJARAN' }}
                                    ({{ $jadwalTerpilih->hari }}, JAM {{ $jadwalTerpilih->jam_ke }})
                                @else
                                    REKAP PRESENSI MURID: {{ $semesterTerpilih->nama_semester }}
                                @endif
                            </h3>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-primary/10 text-primary dark:text-primary-dark border border-primary/20">
                                {{ $ruanganTerpilih->nama_ruangan }}
                            </span>
                        </div>
                        <p
                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest flex flex-wrap items-center gap-2">
                            @if ($jadwalTerpilih)
                                <span><i class="bi bi-person-badge mr-1 opacity-70"></i> Pengampu:
                                    <strong>{{ $jadwalTerpilih->ustadz->nama_lengkap ?? ($jadwalTerpilih->ustadz->nama ?? '-') }}</strong></span>
                                <span>•</span>
                                <span><i class="bi bi-calendar-event mr-1 opacity-70"></i>
                                    {{ $bulanTerpilih ? 'Bulan ' . $bulanTerpilih->nama_bulan : $semesterTerpilih->nama_semester }}</span>
                            @else
                                <span><i class="bi bi-person-badge mr-1 opacity-70"></i> Wali Ruangan:
                                    <strong>{{ $ruanganTerpilih->waliRuangan->nama_lengkap ?? ($ruanganTerpilih->waliRuangan->nama ?? '-') }}</strong></span>
                                <span>•</span>
                                <span><i class="bi bi-info-circle mr-1 opacity-70"></i> Akumulasi Seluruh Mapel</span>
                                <span>•</span>
                                <span>{{ $bulanTerpilih ? 'Bulan ' . $bulanTerpilih->nama_bulan : 'Satu Semester Penuh' }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if ($jadwalTerpilih)
                            <a href="{{ route('presensi-murid.rekap', [
                                'semester_id' => request('semester_id'),
                                'ruangan_id' => request('ruangan_id'),
                                'bulan_id' => request('bulan_id'),
                            ]) }}"
                                class="m3-btn-secondary h-10 px-3.5 text-xs font-bold print:hidden">
                                <i class="bi bi-arrow-left text-xs mr-1"></i>
                                <span>Semua Jadwal</span>
                            </a>
                        @endif

                        <a href="{{ route('presensi-murid.cetak-rekap', [
                            'semester_id' => request('semester_id'),
                            'ruangan_id' => request('ruangan_id'),
                            'bulan_id' => request('bulan_id'),
                            'jadwal_pelajaran_id' => request('jadwal_pelajaran_id'),
                        ]) }}"
                            target="_blank"
                            class="m3-btn-primary h-10 px-5 text-xs font-black uppercase tracking-wider print:hidden">
                            <i class="bi bi-printer-fill text-xs mr-1.5"></i>
                            <span>Cetak Laporan</span>
                        </a>
                    </div>
                </div>

                <!-- Area Tabel -->
                <div class="overflow-x-auto relative z-10 custom-scrollbar">
                    <table class="m3-table min-w-max text-xs">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 w-14 text-center">No</th>
                                <th class="px-5 py-3 w-64">Nama Murid</th>
                                <th class="px-4 py-3 text-center w-24">Pertemuan</th>
                                <th
                                    class="px-4 py-3 text-center bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black">
                                    Hadir (H)
                                </th>
                                <th
                                    class="px-4 py-3 text-center bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black">
                                    Sakit (S)
                                </th>
                                <th
                                    class="px-4 py-3 text-center bg-amber-500/10 text-amber-600 dark:text-amber-400 font-black">
                                    Izin (I)
                                </th>
                                <th
                                    class="px-4 py-3 text-center bg-rose-500/10 text-rose-600 dark:text-rose-400 font-black">
                                    Alpha (A)
                                </th>
                                <th
                                    class="px-4 py-3 text-center bg-purple-500/10 text-purple-600 dark:text-purple-400 font-black">
                                    Dispen (D)
                                </th>
                                <th class="px-4 py-3 text-center bg-zinc-100 dark:bg-zinc-900/80 font-black">
                                    % Hadir
                                </th>
                                <th class="px-4 py-3 text-center bg-zinc-100 dark:bg-zinc-900/80 font-black">
                                    Poin
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($murids as $murid)
                                @php
                                    $data = $rekap[$murid->id];
                                    $poin = $data['akumulasi_poin'];
                                    $persen = $data['persen_hadir'];
                                @endphp
                                <tr class="group/tr hover:bg-zinc-500/5 transition-colors">
                                    <td class="px-4 py-3 text-center align-middle">
                                        <span
                                            class="w-6 h-6 mx-auto flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-md text-[11px] font-black">
                                            {{ $loop->iteration }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-3 align-middle">
                                        <div class="font-black text-zinc-900 dark:text-white tracking-tight mb-0.5 truncate max-w-[220px]"
                                            title="{{ $murid->nama_lengkap }}">
                                            {{ $murid->nama_lengkap }}
                                        </div>
                                        <div
                                            class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider font-mono">
                                            NISM: {{ $murid->nism ?? '-' }}
                                        </div>
                                    </td>

                                    <td
                                        class="px-4 py-3 text-center font-bold text-zinc-600 dark:text-zinc-400 align-middle">
                                        <span class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-[11px]">
                                            {{ $data['total_pertemuan'] }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-4 py-3 text-center font-black bg-emerald-500/5 text-emerald-600 dark:text-emerald-400 align-middle">
                                        {{ $data['H'] }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center font-black bg-blue-500/5 text-blue-600 dark:text-blue-400 align-middle">
                                        {{ $data['S'] }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center font-black bg-amber-500/5 text-amber-600 dark:text-amber-400 align-middle">
                                        {{ $data['I'] }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center font-black bg-rose-500/5 text-rose-600 dark:text-rose-400 align-middle">
                                        {{ $data['A'] }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center font-black bg-purple-500/5 text-purple-600 dark:text-purple-400 align-middle">
                                        {{ $data['D'] }}
                                    </td>

                                    <td class="px-4 py-3 text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black {{ $persen >= 85 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : ($persen >= 70 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400') }}">
                                            {{ $persen }}%
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center align-middle bg-zinc-50/50 dark:bg-zinc-900/30">
                                        <div
                                            class="inline-flex items-center justify-center min-w-[3.5rem] px-2.5 py-1 rounded-lg text-[11px] font-black border transition-all
                                            {{ $poin == 0 ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700' : '' }}
                                            {{ $poin > 0 && $poin < 5 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30' : '' }}
                                            {{ $poin >= 5 ? 'bg-rose-500 text-white border-rose-600 shadow-2xs animate-pulse' : '' }}">
                                            {{ number_format($poin, 1) }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Keterangan Poin -->
            <div class="text-center px-4 mb-8 print:hidden">
                <p
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl m3-glass-card shadow-2xs text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                    <i class="bi bi-info-circle text-primary dark:text-primary-dark"></i>
                    <span>Rumus Poin:</span>
                    <span class="text-primary dark:text-primary-dark font-black ml-1">1 Alpha =
                        {{ $konfig->poin_alpha ?? 1 }} Poin</span>
                    <span class="text-zinc-300 dark:text-zinc-700">|</span>
                    <span class="text-primary dark:text-primary-dark font-black">1 Izin =
                        {{ $konfig->poin_izin ?? 0.16 }} Poin</span>
                </p>
            </div>
        @endif
    @else
        <div class="py-16 text-center m3-glass-card relative z-10 print:hidden">
            <div
                class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-zinc-400 dark:text-zinc-500 text-2xl shadow-2xs">
                <i class="bi bi-file-earmark-spreadsheet"></i>
            </div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Pusat Data Kehadiran
            </h3>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                Silakan pilih Semester dan Ruangan Kelas di atas untuk memuat laporan rekapitulasi presensi.
            </p>
        </div>
    @endif

    <style>
        /* Print Specific Styles */
        @media print {
            body {
                background: white !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .shadow-sm,
            .shadow-inner,
            .shadow-lg,
            .shadow-2xl,
            .shadow-2xs {
                box-shadow: none !important;
            }

            .bg-white,
            .bg-zinc-50,
            .dark\:bg-zinc-900,
            .dark\:bg-zinc-800 {
                background-color: transparent !important;
                border-color: #000 !important;
            }

            table th,
            table td {
                border-color: #000 !important;
                color: #000 !important;
            }

            .text-emerald-600,
            .text-blue-600,
            .text-amber-600,
            .text-rose-600,
            .text-purple-600,
            .text-primary {
                color: #000 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</x-app-layout>
