@section('title', 'Bintang Pelajar')

<x-app-layout>

    <!-- 1. HEADER & TOOLBAR SECTION -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-10 print:hidden">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 inline-flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-star-fill text-xs"></i>
                    <span>Akademik & Prestasi</span>
                </span>
                <span class="text-xs text-zinc-400">•</span>
                <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                    Hasil Ujian
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Bintang Pelajar
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Peringkat murid berprestasi Top 3 per tingkatan jenjang dan per ruangan kelas pada agenda ujian.
            </p>
        </div>

        <!-- Area Form Pencarian & Tombol Aksi -->
        <div class="w-full xl:w-auto flex flex-col sm:flex-row items-center gap-2.5">
            <form action="{{ route('bintang-pelajar.index') }}" method="GET" id="formFilter"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">

                <!-- Filter Tahun -->
                <div class="relative w-full sm:w-[190px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-range text-sm"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        @foreach ($daftarTahun as $t)
                            <option value="{{ $t->id }}" {{ $tahunPelajaranId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_hijriyah }} | {{ $t->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Ujian -->
                <div class="relative w-full sm:w-[220px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-trophy text-sm"></i>
                    </div>
                    <select name="ujian_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        <option value="">-- Pilih Agenda Ujian --</option>
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

                <!-- Tombol Cetak Dokumen -->
                @if (request('ujian_id') && ($bintangLevel->count() > 0 || $bintangRuangan->count() > 0))
                    <div
                        class="w-full sm:w-auto shrink-0 border-t sm:border-t-0 sm:border-l border-zinc-200/80 dark:border-zinc-800 pt-2.5 sm:pt-0 sm:pl-2.5">
                        <a href="{{ route('bintang-pelajar.cetak', ['ujian_id' => request('ujian_id'), 'tahun_id' => $tahunPelajaranId]) }}"
                            target="_blank"
                            class="m3-btn-secondary w-full sm:w-auto h-10 px-4 group/btn inline-flex items-center justify-center gap-2">
                            <i class="bi bi-printer text-sm"></i>
                            <span>Cetak Bintang Pelajar</span>
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- AREA KONTEN -->
    @if (request('ujian_id'))

        <!-- 1. BINTANG LEVEL / TINGKAT -->
        <div class="mb-10 animate-[modalFadeIn_0.2s_ease-out]">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/40 border border-sky-200/80 dark:border-sky-800/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shadow-2xs">
                        <i class="bi bi-layers-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight uppercase">
                            Bintang Tingkat (Per Jenjang)
                        </h3>
                        <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Top 3 akumulasi nilai tertinggi lintas kelas per jenjang tingkatan
                        </p>
                    </div>
                </div>

                <a href="{{ route('bintang-pelajar.cetak', ['ujian_id' => request('ujian_id'), 'tahun_id' => $tahunPelajaranId, 'kategori' => 'level']) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all border border-zinc-200 dark:border-zinc-700 self-start sm:self-auto">
                    <i class="bi bi-printer text-xs"></i>
                    <span>Cetak Bintang Tingkat</span>
                </a>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                @forelse ($bintangLevel as $namaLevel => $murids)
                    <div class="m3-glass-card overflow-hidden flex flex-col">
                        <div
                            class="bg-zinc-50/90 dark:bg-zinc-950/70 px-4 py-3 border-b border-zinc-200/80 dark:border-zinc-800 flex justify-between items-center">
                            <h4
                                class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="bi bi-award text-primary dark:text-primary-dark"></i>
                                <span>Jenjang: {{ $namaLevel }}</span>
                            </h4>
                            <span
                                class="text-[9px] font-black bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 dark:border-primary-dark/30 px-2 py-0.5 rounded-md uppercase tracking-wider">
                                Top 3
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-zinc-100/60 dark:bg-zinc-900/50 text-[10px] font-black uppercase text-zinc-400 dark:text-zinc-500 border-b border-zinc-200/80 dark:border-zinc-800">
                                    <tr>
                                        <th class="py-2.5 px-3 text-center w-12">Rank</th>
                                        <th class="py-2.5 px-2.5">NISM</th>
                                        <th class="py-2.5 px-3">Nama Murid</th>
                                        <th class="py-2.5 px-2 text-center w-9">L/P</th>
                                        <th class="py-2.5 px-2.5">Kelas</th>
                                        <th class="py-2.5 px-3">Nama Ayah</th>
                                        <th class="py-2.5 px-3">Kampung</th>
                                        <th class="py-2.5 px-3 text-right">Total Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium">
                                    @foreach ($murids as $i => $s)
                                        @php
                                            $namaAyah =
                                                $s->murid->nama_ayah ??
                                                ($s->murid->waliMurid->nama_kepala_keluarga ?? '-');
                                            $kampung =
                                                $s->murid->waliMurid->kampung->nama_kampung ??
                                                ($s->murid->waliMurid->alamat_detail ?? '-');
                                        @endphp
                                        <tr
                                            class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors {{ $i == 0 ? 'bg-amber-50/30 dark:bg-amber-950/15' : '' }}">
                                            <td class="py-2.5 px-3 text-center">
                                                @if ($i == 0)
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-500 text-white font-black text-[11px] shadow-2xs">
                                                        1
                                                    </span>
                                                @elseif ($i == 1)
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-400 text-white font-black text-[11px] shadow-2xs">
                                                        2
                                                    </span>
                                                @elseif ($i == 2)
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700 text-white font-black text-[11px] shadow-2xs">
                                                        3
                                                    </span>
                                                @else
                                                    <span class="font-bold text-zinc-400">{{ $i + 1 }}</span>
                                                @endif
                                            </td>
                                            <td
                                                class="py-2.5 px-2.5 font-mono text-[11px] text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                                {{ $s->murid->nism ?? '-' }}
                                            </td>
                                            <td
                                                class="py-2.5 px-3 font-black text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                                {{ $s->murid->nama_lengkap }}
                                            </td>
                                            <td
                                                class="py-2.5 px-2 text-center text-zinc-500 dark:text-zinc-400 font-bold">
                                                {{ $s->murid->jenis_kelamin }}
                                            </td>
                                            <td
                                                class="py-2.5 px-2.5 whitespace-nowrap font-bold text-zinc-600 dark:text-zinc-300">
                                                <span
                                                    class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-[10px] font-black border border-zinc-200 dark:border-zinc-700 uppercase">
                                                    {{ $s->ruangan_nama }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
                                                {{ $namaAyah }}
                                            </td>
                                            <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
                                                {{ $kampung }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                <span
                                                    class="font-black text-xs px-2.5 py-1 rounded-lg bg-primary/10 text-primary dark:text-primary-dark border border-primary/20">
                                                    {{ number_format($s->total_nilai, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <x-empty-state icon="bi-layers" title="Belum Ada Data"
                            message="Belum ada data nilai yang diproses pada jenjang tingkatan untuk ujian ini." />
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. BINTANG RUANGAN -->
        <div class="animate-[modalFadeIn_0.3s_ease-out]">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shadow-2xs">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight uppercase">
                            Bintang Ruangan (Per Kelas)
                        </h3>
                        <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Peringkat 3 besar murid terbaik pada masing-masing ruangan kelas
                        </p>
                    </div>
                </div>

                <a href="{{ route('bintang-pelajar.cetak', ['ujian_id' => request('ujian_id'), 'tahun_id' => $tahunPelajaranId, 'kategori' => 'ruangan']) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all border border-zinc-200 dark:border-zinc-700 self-start sm:self-auto">
                    <i class="bi bi-printer text-xs"></i>
                    <span>Cetak Bintang Ruangan</span>
                </a>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                @forelse ($bintangRuangan as $namaRuangan => $murids)
                    <div class="m3-glass-card overflow-hidden flex flex-col">
                        <div
                            class="bg-zinc-50/90 dark:bg-zinc-950/70 px-4 py-3 border-b border-zinc-200/80 dark:border-zinc-800 flex justify-between items-center">
                            <h4
                                class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="bi bi-door-open text-emerald-600 dark:text-emerald-400"></i>
                                <span>Ruangan: {{ $namaRuangan }}</span>
                            </h4>
                            <span
                                class="text-[9px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded-md uppercase tracking-wider">
                                Top 3
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-zinc-100/60 dark:bg-zinc-900/50 text-[10px] font-black uppercase text-zinc-400 dark:text-zinc-500 border-b border-zinc-200/80 dark:border-zinc-800">
                                    <tr>
                                        <th class="py-2.5 px-3 text-center w-12">Rank</th>
                                        <th class="py-2.5 px-2.5">NISM</th>
                                        <th class="py-2.5 px-3">Nama Murid</th>
                                        <th class="py-2.5 px-2 text-center w-9">L/P</th>
                                        <th class="py-2.5 px-3">Nama Ayah</th>
                                        <th class="py-2.5 px-3">Kampung</th>
                                        <th class="py-2.5 px-3 text-right">Total Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium">
                                    @foreach ($murids as $i => $s)
                                        @php
                                            $namaAyah =
                                                $s->murid->nama_ayah ??
                                                ($s->murid->waliMurid->nama_kepala_keluarga ?? '-');
                                            $kampung =
                                                $s->murid->waliMurid->kampung->nama_kampung ??
                                                ($s->murid->waliMurid->alamat_detail ?? '-');
                                        @endphp
                                        <tr
                                            class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors {{ $i == 0 ? 'bg-amber-50/30 dark:bg-amber-950/15' : '' }}">
                                            <td class="py-2.5 px-3 text-center">
                                                @if ($i == 0)
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-500 text-white font-black text-[11px] shadow-2xs">
                                                        1
                                                    </span>
                                                @elseif ($i == 1)
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-400 text-white font-black text-[11px] shadow-2xs">
                                                        2
                                                    </span>
                                                @elseif ($i == 2)
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700 text-white font-black text-[11px] shadow-2xs">
                                                        3
                                                    </span>
                                                @else
                                                    <span class="font-bold text-zinc-400">{{ $i + 1 }}</span>
                                                @endif
                                            </td>
                                            <td
                                                class="py-2.5 px-2.5 font-mono text-[11px] text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                                {{ $s->murid->nism ?? '-' }}
                                            </td>
                                            <td
                                                class="py-2.5 px-3 font-black text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                                {{ $s->murid->nama_lengkap }}
                                            </td>
                                            <td
                                                class="py-2.5 px-2 text-center text-zinc-500 dark:text-zinc-400 font-bold">
                                                {{ $s->murid->jenis_kelamin }}
                                            </td>
                                            <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
                                                {{ $namaAyah }}
                                            </td>
                                            <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
                                                {{ $kampung }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                <span
                                                    class="font-black text-xs px-2.5 py-1 rounded-lg bg-primary/10 text-primary dark:text-primary-dark border border-primary/20">
                                                    {{ number_format($s->total_nilai, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <x-empty-state icon="bi-door-closed" title="Belum Ada Data"
                            message="Belum ada data nilai yang diproses pada ruangan kelas untuk ujian ini." />
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- STATE AWAL PANDUAN -->
        <x-empty-state icon="bi-award" title="Peringkat Bintang Pelajar"
            message="Pilih Agenda Ujian pada filter di atas untuk melihat peringkat Top 3 di setiap Ruangan Kelas dan Jenjang Tingkatan." />
    @endif

</x-app-layout>
