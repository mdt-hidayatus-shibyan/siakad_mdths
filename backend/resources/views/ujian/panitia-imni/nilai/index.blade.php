@section('title', 'Input Nilai Mapel IMNI')

<x-app-layout>
    <div x-data="nilaiImniManager({
        kkmDefault: {{ $kkmDefault ?? 65 }},
        totalPeserta: {{ $totalPeserta ?? 0 }}
    })">
        <!-- 1. HEADER SECTION -->
        <div
            class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Input Nilai Mapel IMNI
                </h2>
                <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Entri nilai ujian teori/tulis murid kelas akhir per ruangan & jadwal mata pelajaran beserta
                    rekapitulasi leger nilai.
                </p>
            </div>

            <!-- Toolbar Aksi Utama -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('nilai-imni.index') }}" method="GET" id="formTahunNilai" class="m-0">
                    <div class="relative">
                        <select name="tahun_id" onchange="document.getElementById('formTahunNilai').submit()"
                            class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                    {{ $th->nama_hijriyah }} H | {{ $th->nama_masehi }} M
                                    {{ $th->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                    </div>
                </form>

                <!-- Tombol Cetak Leger Ruangan -->
                @if ($selectedRuangan)
                    <a href="{{ route('nilai-imni.cetak-leger', ['ruangan_ujian_id' => $selectedRuangan->id, 'tahun_id' => $selectedTahunId]) }}"
                        target="_blank"
                        class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                        <i class="bi bi-printer-fill text-sm text-violet-600"></i>
                        <span>Cetak Leger Ruangan</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 2. ALERTS -->
        @if (session('success'))
            <div
                class="mb-5 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-xs font-bold shadow-xs">
                <i class="bi bi-check-circle-fill text-lg text-emerald-600 dark:text-emerald-400"></i>
                <div class="flex-1">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-5 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 flex items-center gap-3 text-rose-800 dark:text-rose-300 text-xs font-bold shadow-xs">
                <i class="bi bi-exclamation-triangle-fill text-lg text-rose-600 dark:text-rose-400"></i>
                <div class="flex-1">{{ session('error') }}</div>
            </div>
        @endif

        <div id="data-table-container">
            <!-- 3. METRIC CARDS -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 mb-6">
                <!-- Total Peserta Ruangan -->
                <div
                    class="p-4 rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between text-zinc-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Murid Ruangan</span>
                        <span
                            class="w-7 h-7 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center">
                            <i class="bi bi-people-fill text-xs"></i>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-zinc-900 dark:text-white">{{ $totalPeserta }}</span>
                        <span class="text-[11px] font-bold text-zinc-400">Murid</span>
                    </div>
                </div>

                <!-- Progres Input Nilai -->
                <div
                    class="p-4 rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between text-zinc-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Terinput</span>
                        <span class="w-7 h-7 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                            <i class="bi bi-check2-circle text-xs"></i>
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="flex items-baseline justify-between">
                            <span class="text-2xl font-black text-zinc-900 dark:text-white">{{ $countTerinput }}</span>
                            <span
                                class="text-[11px] font-black text-blue-600 dark:text-blue-400">{{ $persenTerinput }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full mt-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500"
                                style="width: {{ $persenTerinput }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Rata-rata Nilai -->
                <div
                    class="p-4 rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between text-zinc-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Rata-rata</span>
                        <span
                            class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i class="bi bi-calculator text-xs"></i>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $rataRata }}</span>
                        <span class="text-[11px] font-bold text-zinc-400">/ 100</span>
                    </div>
                </div>

                <!-- Nilai Tertinggi / Terendah -->
                <div
                    class="p-4 rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between text-zinc-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Maks / Min</span>
                        <span class="w-7 h-7 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center">
                            <i class="bi bi-bar-chart-fill text-xs"></i>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-xl font-black text-emerald-600">{{ $nilaiTertinggi }}</span>
                        <span class="text-xs text-zinc-400 font-bold">/</span>
                        <span class="text-xl font-black text-rose-600">{{ $nilaiTerendah }}</span>
                    </div>
                </div>

                <!-- Status KKM Default (65) -->
                <div
                    class="col-span-2 lg:col-span-1 p-4 rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between text-zinc-500">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Tuntas KKM (&ge;
                            {{ $kkmDefault }})</span>
                        <span
                            class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i class="bi bi-award-fill text-xs"></i>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-black text-emerald-600">{{ $countLulusKkm }}</span>
                        <span class="text-[11px] font-bold text-rose-600">Remidi: {{ $countDiBawahKkm }}</span>
                    </div>
                </div>
            </div>

            <!-- 4. FILTER RUANGAN & JADWAL MAPEL -->
            <div
                class="p-5 md:p-6 rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs mb-6">
                <form action="{{ route('nilai-imni.index') }}" method="GET" id="formFilterRuanganJadwal"
                    class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <input type="hidden" name="tahun_id" value="{{ $selectedTahunId }}">

                    <!-- Filter Level / Kelas -->
                    <div class="md:col-span-3">
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                            <i class="bi bi-mortarboard-fill text-violet-600 mr-1"></i> Filter Level
                        </label>
                        <select name="level_id" onchange="document.getElementById('formFilterRuanganJadwal').submit()"
                            class="w-full py-2.5 px-3.5 text-xs font-bold bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-xs focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 cursor-pointer">
                            <option value="">Semua Level IMNI</option>
                            @foreach ($daftarLevelAkhir as $lvl)
                                <option value="{{ $lvl->id }}"
                                    {{ ($levelId ?? '') == $lvl->id ? 'selected' : '' }}>
                                    {{ $lvl->nama_level }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Ruangan Ujian IMNI -->
                    <div class="md:col-span-4">
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                            <i class="bi bi-door-open-fill text-violet-600 mr-1"></i> Pilih Ruangan Ujian
                        </label>
                        <select name="ruangan_ujian_id"
                            onchange="document.getElementById('formFilterRuanganJadwal').submit()"
                            class="w-full py-2.5 px-3.5 text-xs font-bold bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-xs focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 cursor-pointer">
                            @forelse ($daftarRuanganUjian as $ruang)
                                <option value="{{ $ruang->id }}"
                                    {{ $selectedRuanganId == $ruang->id ? 'selected' : '' }}>
                                    {{ $ruang->nama_ruangan }} ({{ $ruang->level?->nama_level ?? 'Semua Level' }})
                                </option>
                            @empty
                                <option value="">Belum Ada Ruangan Ujian</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Pilih Jadwal Mata Pelajaran -->
                    <div class="md:col-span-5">
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                            <i class="bi bi-journal-bookmark-fill text-violet-600 mr-1"></i> Pilih Mata Pelajaran
                        </label>
                        <select name="jadwal_ujian_id"
                            onchange="document.getElementById('formFilterRuanganJadwal').submit()"
                            class="w-full py-2.5 px-3.5 text-xs font-bold bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-xs focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 cursor-pointer"
                            {{ $jadwalUjians->isEmpty() ? 'disabled' : '' }}>
                            @forelse ($jadwalUjians as $jdw)
                                <option value="{{ $jdw->id }}"
                                    {{ $selectedJadwalId == $jdw->id ? 'selected' : '' }}>
                                    {{ $jdw->nama_mapel }} [{{ $jdw->level?->nama_level ?? 'Level' }}] &mdash;
                                    {{ \Carbon\Carbon::parse($jdw->tanggal_ujian)->translatedFormat('d M Y') }}
                                    ({{ substr($jdw->waktu_mulai, 0, 5) }}-{{ substr($jdw->waktu_selesai, 0, 5) }})
                                </option>
                            @empty
                                <option value="">Jadwal ujian belum di-set untuk level ini</option>
                            @endforelse
                        </select>
                    </div>
                </form>
            </div>

            <!-- 5. TABEL INPUT NILAI MAPEL -->
            @if ($selectedRuangan && $selectedJadwal)
                <form action="{{ route('nilai-imni.store') }}" method="POST" id="formSimpanNilai"
                    class="ajax-post" data-refresh-target="#data-table-container">
                    @csrf
                    <input type="hidden" name="ruangan_ujian_id" value="{{ $selectedRuangan->id }}">
                    <input type="hidden" name="jadwal_ujian_id" value="{{ $selectedJadwal->id }}">

                    <div
                        class="rounded-3xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs overflow-hidden mb-20">
                        <!-- Top Action Bar Inside Card -->
                        <div
                            class="p-4 md:p-5 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-zinc-50/50 dark:bg-zinc-800/30">
                            <div>
                                <h3 class="text-sm font-black text-zinc-900 dark:text-white flex items-center gap-2">
                                    <span>Entri Nilai: {{ $selectedJadwal->nama_mapel }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300">
                                        {{ $selectedRuangan->nama_ruangan }}
                                    </span>
                                </h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    Masukkan skor rentang 0 s.d 100. Standar KKM Ketercapaian:
                                    <strong>{{ $kkmDefault }}</strong>.
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="fillDefaultScore(75)"
                                    class="px-3 py-1.5 rounded-xl bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 text-zinc-700 dark:text-zinc-200 text-xs font-bold transition-all">
                                    <i class="bi bi-magic mr-1"></i> Set Cepat (75)
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-black shadow-md shadow-violet-600/20 transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                    <i class="bi bi-save-fill"></i>
                                    <span>Simpan Nilai</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tabel Peserta & Input -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr
                                        class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-100/70 dark:bg-zinc-800/60 text-zinc-600 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider">
                                        <th class="py-3 px-4 text-center w-12">No</th>
                                        <th class="py-3 px-3 text-center w-16">Meja</th>
                                        <th class="py-3 px-4">No. Peserta</th>
                                        <th class="py-3 px-4">Nama Murid</th>
                                        <th class="py-3 px-4">Kelas Asal</th>
                                        <th class="py-3 px-4 w-36 text-center">Skor Nilai (0-100)</th>
                                        <th class="py-3 px-4 text-center w-28">Status KKM</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 text-zinc-800 dark:text-zinc-200 font-medium">
                                    @forelse ($pesertas as $index => $p)
                                        @php
                                            $nilaiObj = $nilaiExisting->get($p->murid_id);
                                            $scoreVal = $nilaiObj ? $nilaiObj->nilai : '';
                                        @endphp
                                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors">
                                            <td class="py-3 px-4 text-center font-bold text-zinc-400">
                                                {{ $index + 1 }}</td>
                                            <td class="py-3 px-3 text-center">
                                                <span
                                                    class="inline-block px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 font-mono font-black text-zinc-700 dark:text-zinc-300">
                                                    {{ $p->nomor_meja ?? '-' }}
                                                </span>
                                            </td>
                                            <td
                                                class="py-3 px-4 font-mono font-bold text-violet-600 dark:text-violet-400">
                                                {{ $p->nomor_peserta }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="font-black text-zinc-900 dark:text-white">
                                                    {{ $p->murid?->nama_lengkap }}</div>
                                                <div class="text-[10px] text-zinc-400 font-mono">NISM:
                                                    {{ $p->murid?->nism ?? '-' }} </div>
                                            </td>
                                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">
                                                {{ $p->ruanganAsal?->nama_ruangan ?? '-' }}
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                <div class="relative max-w-[120px] mx-auto">
                                                    <input type="number" step="0.5" min="0"
                                                        max="100" name="nilai[{{ $p->murid_id }}]"
                                                        value="{{ $scoreVal }}"
                                                        x-model="scores[{{ $p->murid_id }}]"
                                                        @input="updateStatus({{ $p->murid_id }})"
                                                        placeholder="0 - 100"
                                                        class="w-full text-center py-1.5 px-2.5 text-xs font-black bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 shadow-2xs font-mono">
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <template
                                                    x-if="scores[{{ $p->murid_id }}] !== '' && scores[{{ $p->murid_id }}] !== undefined">
                                                    <div>
                                                        <span
                                                            x-show="Number(scores[{{ $p->murid_id }}]) >= kkmDefault"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                            <i class="bi bi-check-circle-fill text-[9px]"></i> Tuntas
                                                        </span>
                                                        <span
                                                            x-show="Number(scores[{{ $p->murid_id }}]) < kkmDefault"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                            <i class="bi bi-exclamation-circle-fill text-[9px]"></i>
                                                            Remidi
                                                        </span>
                                                    </div>
                                                </template>
                                                <template
                                                    x-if="scores[{{ $p->murid_id }}] === '' || scores[{{ $p->murid_id }}] === undefined">
                                                    <span class="text-zinc-400 text-[10px] italic">Kosong</span>
                                                </template>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-12 text-center text-zinc-400">
                                                <i class="bi bi-inbox text-3xl block mb-2 opacity-50"></i>
                                                Tidak ada data peserta IMNI di ruangan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>


                </form>
            @else
                @if ($daftarRuanganUjian->isEmpty())
                    <div
                        class="p-12 text-center rounded-3xl bg-white/80 dark:bg-zinc-900/80 border border-zinc-200/80 dark:border-zinc-800 text-zinc-400">
                        <i class="bi bi-door-closed text-4xl block mb-2 opacity-40"></i>
                        Belum ada data Ruangan Ujian IMNI.
                    </div>
                @elseif ($jadwalUjians->isEmpty())
                    <div
                        class="p-12 text-center rounded-3xl bg-white/80 dark:bg-zinc-900/80 border border-amber-500/20 bg-amber-500/5 text-zinc-400">
                        <div
                            class="w-16 h-16 rounded-3xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mx-auto mb-4 border border-amber-500/20">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white">Jadwal Ujian Belum Di-set</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-md mx-auto">
                            Jadwal ujian untuk tipe <strong>{{ $tipeUjian }}</strong> pada level ini belum
                            dikonfigurasi di Master Jadwal Ujian. Silakan buat jadwal ujian terlebih dahulu.
                        </p>
                        <div class="mt-5">
                            <x-button :href="route('jadwal-ujian.index', [
                                'tahun_id' => $selectedTahunId,
                                'tipe_ujian' => $tipeUjian,
                            ])" variant="primary" size="sm" icon="bi-calendar-plus">
                                <span>Atur Jadwal Ujian</span>
                            </x-button>
                        </div>
                    </div>
                @else
                    <div
                        class="p-12 text-center rounded-3xl bg-white/80 dark:bg-zinc-900/80 border border-zinc-200/80 dark:border-zinc-800 text-zinc-400">
                        <i class="bi bi-journal-x text-4xl block mb-2 opacity-40"></i>
                        Silakan pilih Ruangan Ujian dan Jadwal Mapel IMNI untuk memulai input nilai.
                    </div>
                @endif
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function nilaiImniManager(config) {
                return {
                    kkmDefault: config.kkmDefault,
                    totalPeserta: config.totalPeserta,
                    scores: {
                        @foreach ($pesertas as $p)
                            @php $n = $nilaiExisting->get($p->murid_id); @endphp
                            {{ $p->murid_id }}: '{{ $n ? $n->nilai : '' }}',
                        @endforeach
                    },
                    fillDefaultScore(defaultVal) {
                        for (let key in this.scores) {
                            if (this.scores[key] === '' || this.scores[key] === null) {
                                this.scores[key] = defaultVal;
                            }
                        }
                    },
                    updateStatus(muridId) {
                        // Re-evaluated dynamically by Alpine
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
