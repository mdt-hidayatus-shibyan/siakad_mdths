@section('title', 'Presensi Ujian IMNI - ' . ($kategori === 'tpq' ? 'Kelas 3 TPQ' : $selectedRuangan?->nama_ruangan_imni
    ?? 'IMNI'))

    <x-app-layout>
        <div class="space-y-6">
            <!-- 1. HEADER SECTION & TAB SWITCHER -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10 print:hidden">
                <div>

                    <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                        {{ $kategori === 'tpq' ? 'Presensi Ujian Kelas 3 TPQ' : 'Presensi Ujian Ruangan IMNI' }}
                    </h2>
                    <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ $kategori === 'tpq' ? 'Penginputan kehadiran murid kelas 3 TPQ per ruangan kelas & mata pelajaran ujian.' : 'Penginputan kehadiran santri gabungan 6 IBT & 3 TSA per ruangan ujian IMNI.' }}
                    </p>
                </div>

                <!-- Toolbar Kanan & Filter Tahun -->
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Filter Tahun Pelajaran -->
                    <form action="{{ route('presensi-imni.index') }}" method="GET" id="formTahunPresensi" class="m-0">
                        <input type="hidden" name="kategori" value="{{ $kategori }}">
                        <div class="relative">
                            <select name="tahun_id" onchange="document.getElementById('formTahunPresensi').submit()"
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
                </div>
            </div>

            <!-- 2. TAB SWITCHER JENJANG (IMNI vs KELAS 3 TPQ) -->
            <div
                class="flex items-center gap-2 p-1.5 bg-zinc-100 dark:bg-zinc-900 rounded-2xl w-fit border border-zinc-200/80 dark:border-zinc-800">

                <!-- Tab 3 TPQ -->
                <a href="{{ route('presensi-imni.index', ['tahun_id' => $selectedTahunId, 'kategori' => 'tpq']) }}"
                    class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $kategori === 'tpq' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-book-fill"></i>
                    <span>Kelas 3 TPQ</span>
                </a>

                <!-- Tab IMNI (6 IBT & 3 TSA) -->
                <a href="{{ route('presensi-imni.index', ['tahun_id' => $selectedTahunId, 'kategori' => 'imni']) }}"
                    class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $kategori !== 'tpq' ? 'bg-indigo-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Kelas 6 IBT & 3 TSA (Ruangan IMNI)</span>
                </a>


            </div>

            <!-- ========================================================================= -->
            <!-- VIEW BAGIAN 1: KELAS 3 TPQ (RUANGAN FISIK 3 TPQ & UJIAN IMNI TPQ) -->
            <!-- ========================================================================= -->
            @if ($kategori === 'tpq')
                <!-- Filter Bar TPQ -->
                <div
                    class="m3-glass-card rounded-2xl md:rounded-3xl p-4 md:p-5 border border-zinc-200/80 dark:border-zinc-800">
                    <form action="{{ route('presensi-imni.index') }}" method="GET" id="formFilterTpq"
                        class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <input type="hidden" name="tahun_id" value="{{ $selectedTahunId }}">
                        <input type="hidden" name="kategori" value="tpq">

                        <!-- Pilih Ruangan Kelas 3 TPQ -->
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                                Pilih Ruangan Kelas 3 TPQ <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="ruangan_id" onchange="document.getElementById('formFilterTpq').submit()"
                                    class="w-full pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer">
                                    @forelse ($daftarRuanganTpq as $r)
                                        <option value="{{ $r->id }}"
                                            {{ $selectedRuanganTpqId == $r->id ? 'selected' : '' }}>
                                            {{ $r->nama_ruangan }} ({{ $r->level?->nama_level ?? '3 TPQ' }})
                                        </option>
                                    @empty
                                        <option value="">-- Belum ada ruangan kelas 3 TPQ --</option>
                                    @endforelse
                                </select>
                                <i
                                    class="bi bi-door-open text-zinc-400 text-xs absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Pilih Pelaksanaan Ujian TPQ -->
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                                Pilih Pelaksanaan Ujian IMNI <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="ujian_id" onchange="document.getElementById('formFilterTpq').submit()"
                                    class="w-full pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer">
                                    @forelse ($daftarUjianTpq as $uj)
                                        <option value="{{ $uj->id }}"
                                            {{ $selectedUjianTpqId == $uj->id ? 'selected' : '' }}>
                                            {{ $uj->nama_ujian }}
                                        </option>
                                    @empty
                                        <option value="">-- Belum ada ujian IMNI TPQ --</option>
                                    @endforelse
                                </select>
                                <i
                                    class="bi bi-file-earmark-check text-zinc-400 text-xs absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Pilih Mata Pelajaran -->
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                                Pilih Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="jadwal_ujian_id" onchange="document.getElementById('formFilterTpq').submit()"
                                    {{ $jadwalTpqList->isEmpty() ? 'disabled' : '' }}
                                    class="w-full pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer disabled:opacity-50">
                                    @foreach ($jadwalTpqList as $jdw)
                                        <option value="{{ $jdw->id }}"
                                            {{ $selectedJadwalTpqId == $jdw->id ? 'selected' : '' }}>
                                            [{{ $jdw->tanggal_ujian ? \Carbon\Carbon::parse($jdw->tanggal_ujian)->format('d/m') : '-' }}]
                                            {{ $jdw->mataPelajaran?->nama_mapel ?? ($jdw->nama_mata_pelajaran_custom ?? '-') }}
                                        </option>
                                    @endforeach
                                </select>
                                <i
                                    class="bi bi-journal-bookmark text-zinc-400 text-xs absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Area Input Presensi TPQ -->
                @if ($selectedRuanganTpq && $selectedUjianTpq && $selectedJadwalTpq)
                    <form action="{{ route('presensi-imni.store') }}" method="POST" id="formPresensiTpq"
                        class="space-y-5 m-0">
                        @csrf
                        <input type="hidden" name="kategori" value="tpq">
                        <input type="hidden" name="ujian_id" value="{{ $selectedUjianTpq->id }}">
                        <input type="hidden" name="ruangan_id" value="{{ $selectedRuanganTpq->id }}">
                        <input type="hidden" name="jadwal_ujian_id" value="{{ $selectedJadwalTpq->id }}">

                        <!-- Card Header Mapel & Statistik Ringkas TPQ -->
                        <div
                            class="m3-glass-card p-5 md:p-6 overflow-hidden rounded-2xl md:rounded-3xl border border-emerald-500/20">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                                        <i class="bi bi-journal-check"></i>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span
                                                class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                                {{ $selectedRuanganTpq->nama_ruangan }}
                                            </span>
                                            <span
                                                class="px-2.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                                <i class="bi bi-calendar-event mr-1"></i>
                                                {{ $selectedJadwalTpq->tanggal_ujian ? \Carbon\Carbon::parse($selectedJadwalTpq->tanggal_ujian)->translatedFormat('l, d F Y') : '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                                <i class="bi bi-clock mr-1"></i>
                                                {{ $selectedJadwalTpq->waktu_mulai ? substr($selectedJadwalTpq->waktu_mulai, 0, 5) : '' }}
                                                -
                                                {{ $selectedJadwalTpq->waktu_selesai ? substr($selectedJadwalTpq->waktu_selesai, 0, 5) : '' }}
                                                WIB
                                            </span>
                                        </div>
                                        <h2
                                            class="font-black text-xl text-zinc-900 dark:text-white uppercase tracking-tight">
                                            {{ $selectedJadwalTpq->mataPelajaran?->nama_mapel ?? ($selectedJadwalTpq->nama_mata_pelajaran_custom ?? '-') }}
                                        </h2>
                                    </div>
                                </div>

                                <!-- Statistik Kehadiran Ringkas TPQ -->
                                <div class="flex items-center gap-1.5 text-xs font-mono font-bold flex-wrap">
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-700">
                                        Total: <strong id="statTotalTpq">{{ $totalTpq }}</strong>
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Hadir: <strong id="statHadirTpq">{{ $hadirTpq }}</strong>
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        Sakit: <strong id="statSakitTpq">{{ $sakitTpq }}</strong>
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                        Izin: <strong id="statIzinTpq">{{ $izinTpq }}</strong>
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        Alpha: <strong id="statAlphaTpq">{{ $alphaTpq }}</strong>
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200/80 dark:border-zinc-700">
                                        Belum: <strong id="statBelumTpq">{{ $belumTpq }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Presensi Santri Kelas 3 TPQ -->
                        <div
                            class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                            <div
                                class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <h4
                                        class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                        Daftar Murid Kelas 3 TPQ ({{ $muridsTpq->count() }} Murid)
                                    </h4>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="setSemuaHadir()"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-black hover:bg-emerald-500/20 transition-all border border-emerald-500/20 cursor-pointer flex items-center gap-1">
                                        <i class="bi bi-check-all text-sm"></i>
                                        <span>Semua Hadir</span>
                                    </button>
                                    <button type="button" onclick="kosongkanSemua()"
                                        class="px-2.5 py-1.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 text-xs font-bold transition-all border border-zinc-200/80 dark:border-zinc-700 cursor-pointer flex items-center gap-1"
                                        title="Kosongkan semua pilihan presensi">
                                        <i class="bi bi-slash-circle text-xs text-rose-500"></i>
                                        <span>Kosongkan</span>
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead
                                        class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60">
                                        <tr>
                                            <th class="py-3 px-4 text-center w-12">No</th>
                                            <th class="py-3 px-4 w-28">NISM</th>
                                            <th class="py-3 px-4">Nama Lengkap Murid</th>
                                            <th class="py-3 px-4 text-center w-14">L/P</th>
                                            <th class="py-3 px-4 text-center w-72">Status Kehadiran</th>
                                            <th class="py-3 px-4 w-48">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                        @forelse ($muridsTpq as $index => $m)
                                            @php
                                                $curSt = $presensiExistingTpq->get($m->id)?->status;
                                                $curCat = $presensiExistingTpq->get($m->id)?->catatan ?? '';
                                            @endphp
                                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors">
                                                <td class="py-3 px-4 text-center font-mono font-bold">{{ $index + 1 }}
                                                </td>
                                                <td class="py-3 px-4 font-mono text-[11px] text-zinc-500">
                                                    {{ $m->nism ?? '-' }}</td>
                                                <td class="py-3 px-4 font-bold text-xs text-zinc-900 dark:text-white">
                                                    {{ $m->nama_lengkap }}</td>
                                                <td class="py-3 px-4 text-center font-bold text-[10.5px]">
                                                    {{ $m->jenis_kelamin ?? '-' }}</td>
                                                <td class="py-3 px-4">
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        @foreach (['Hadir', 'Sakit', 'Izin', 'Alpha'] as $stOption)
                                                            @php
                                                                $isChecked = $curSt === $stOption;
                                                                $badgeStyle = match ($stOption) {
                                                                    'Hadir'
                                                                        => 'peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600',
                                                                    'Sakit'
                                                                        => 'peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-amber-600',
                                                                    'Izin'
                                                                        => 'peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600',
                                                                    'Alpha'
                                                                        => 'peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600',
                                                                    default
                                                                        => 'peer-checked:bg-emerald-600 peer-checked:text-white',
                                                                };
                                                            @endphp
                                                            <label class="cursor-pointer">
                                                                <input type="radio"
                                                                    name="presensi[{{ $m->id }}][status]"
                                                                    value="{{ $stOption }}"
                                                                    class="sr-only peer radio-status-tpq"
                                                                    {{ $isChecked ? 'checked' : '' }}
                                                                    onchange="updateStatsTpq()">
                                                                <span
                                                                    class="px-2.5 py-1 rounded-lg text-[10.5px] font-black border transition-all select-none block {{ $badgeStyle }} bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                                                    {{ $stOption }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <input type="text" name="presensi[{{ $m->id }}][catatan]"
                                                        value="{{ $curCat }}" placeholder="Keterangan..."
                                                        class="m3-input-glass w-full text-[11px] py-1 px-2">
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-8 text-center text-zinc-400 italic">
                                                    Belum ada murid aktif di ruangan kelas ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer Simpan TPQ -->
                            @if ($muridsTpq->isNotEmpty())
                                <div
                                    class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                                    <div class="text-xs text-zinc-500">
                                        Total: <strong
                                            class="text-zinc-800 dark:text-zinc-200">{{ $muridsTpq->count() }}</strong>
                                        murid Kelas 3 TPQ.
                                    </div>
                                    <button type="submit" id="btnSimpanPresensiTpq"
                                        class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-black flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                                        <i class="bi bi-save2"></i>
                                        <span>Simpan Presensi TPQ</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="p-12 text-center text-zinc-400 m3-glass-card rounded-2xl">
                        <i class="bi bi-journal-x text-3xl mb-2 block text-zinc-300"></i>
                        Silakan pilih Ruangan, Pelaksanaan Ujian IMNI, dan Mata Pelajaran Kelas 3 TPQ untuk memulai input
                        presensi.
                    </div>
                @endif

                <!-- ========================================================================= -->
                <!-- VIEW BAGIAN 2: KELAS 6 IBT & 3 TSA (FORMAT RUANGAN IMNI GABUNGAN) -->
                <!-- ========================================================================= -->
            @else
                <!-- Filter Bar IMNI (Hari & Ruangan IMNI) -->
                <div
                    class="m3-glass-card rounded-2xl md:rounded-3xl p-4 md:p-5 border border-zinc-200/80 dark:border-zinc-800">
                    <form action="{{ route('presensi-imni.index') }}" method="GET" id="formFilterImni"
                        class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <input type="hidden" name="tahun_id" value="{{ $selectedTahunId }}">
                        <input type="hidden" name="kategori" value="imni">

                        <!-- Pilih Hari / Tanggal Ujian -->
                        <div class="sm:col-span-6">
                            <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                                Pilih Hari & Tanggal Pelaksanaan Ujian <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="tanggal_ujian" onchange="document.getElementById('formFilterImni').submit()"
                                    class="w-full pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
                                    @foreach ($daftarHariUjian as $h)
                                        <option value="{{ $h->tanggal }}"
                                            {{ $selectedTanggal == $h->tanggal ? 'selected' : '' }}>
                                            Hari Ke-{{ $h->hari_ke }} : {{ $h->nama_hari }}, {{ $h->tanggal_format }}
                                        </option>
                                    @endforeach
                                </select>
                                <i
                                    class="bi bi-calendar-check absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Pilih Ruangan IMNI -->
                        <div class="sm:col-span-6">
                            <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                                Pilih Ruangan Ujian IMNI <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="ruangan_imni_id"
                                    onchange="document.getElementById('formFilterImni').submit()"
                                    class="w-full pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer font-mono">
                                    @foreach ($daftarRuanganImni as $rg)
                                        <option value="{{ $rg->id }}"
                                            {{ $selectedRuanganId == $rg->id ? 'selected' : '' }}>
                                            {{ $rg->nama_ruangan_imni ?: $rg->nama_ruangan }}
                                            ({{ $rg->ruanganFisik?->nama_ruangan ?? 'Ruang Fisik' }})
                                        </option>
                                    @endforeach
                                </select>
                                <i
                                    class="bi bi-door-open-fill absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Banner Info Jadwal Hari Ini & Statistik Ruangan -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Info Jadwal 6 IBT & 3 TSA Hari Ini -->
                    <div
                        class="lg:col-span-7 m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-indigo-500/20 bg-indigo-500/5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-600 text-white shadow-2xs font-mono">
                                    {{ $selectedRuangan?->nama_ruangan_imni ?: 'R1' }}
                                </span>
                                <span
                                    class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1">
                                    <i class="bi bi-calendar3"></i>
                                    Hari Ke-{{ $selectedHariInfo->hari_ke }} ({{ $selectedHariInfo->nama_hari }},
                                    {{ $selectedHariInfo->tanggal_format }})
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mt-3">
                                <!-- 6 IBT -->
                                <div
                                    class="p-3 bg-white dark:bg-zinc-900 rounded-2xl border border-orange-500/20 shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-orange-500/10 text-orange-600 font-black text-[10.5px] border border-orange-500/20">
                                            Kelas 6 IBT
                                        </span>
                                        <span class="text-[10px] text-zinc-400 font-mono">Mapel Ujian</span>
                                    </div>
                                    @if ($jadwalIbtList->isNotEmpty())
                                        @foreach ($jadwalIbtList as $jb)
                                            <h5 class="text-xs font-black text-zinc-900 dark:text-white mt-1">
                                                {{ $jb->mataPelajaran?->nama_mapel ?? ($jb->nama_mata_pelajaran_custom ?? '-') }}
                                            </h5>
                                            <p class="text-[10.5px] font-mono text-zinc-500">
                                                {{ $jb->waktu_mulai ? substr($jb->waktu_mulai, 0, 5) : '' }} -
                                                {{ $jb->waktu_selesai ? substr($jb->waktu_selesai, 0, 5) : '' }} WIB
                                            </p>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Tidak ada jadwal 6 IBT</span>
                                    @endif
                                </div>

                                <!-- 3 TSA -->
                                <div
                                    class="p-3 bg-white dark:bg-zinc-900 rounded-2xl border border-blue-500/20 shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 font-black text-[10.5px] border border-blue-500/20">
                                            Kelas 3 TSA
                                        </span>
                                        <span class="text-[10px] text-zinc-400 font-mono">Mapel Ujian</span>
                                    </div>
                                    @if ($jadwalTsaList->isNotEmpty())
                                        @foreach ($jadwalTsaList as $jt)
                                            <h5 class="text-xs font-black text-zinc-900 dark:text-white mt-1">
                                                {{ $jt->mataPelajaran?->nama_mapel ?? ($jt->nama_mata_pelajaran_custom ?? '-') }}
                                            </h5>
                                            <p class="text-[10.5px] font-mono text-zinc-500">
                                                {{ $jt->waktu_mulai ? substr($jt->waktu_mulai, 0, 5) : '' }} -
                                                {{ $jt->waktu_selesai ? substr($jt->waktu_selesai, 0, 5) : '' }} WIB
                                            </p>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Tidak ada jadwal 3 TSA</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-indigo-500/10 text-[11px] text-zinc-500">
                            <span class="flex items-center gap-1.5">
                                <i class="bi bi-geo-alt-fill text-indigo-600"></i>
                                Lokasi: <strong
                                    class="text-zinc-700 dark:text-zinc-300">{{ $selectedRuangan?->ruanganFisik?->nama_ruangan ?? 'Kelas Fisik' }}</strong>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="bi bi-shield-shaded text-indigo-600"></i>
                                PJ: <strong
                                    class="text-zinc-700 dark:text-zinc-300">{{ $selectedRuangan?->penanggungJawabRuangan?->ustadz?->nama_lengkap ?? 'Panitia' }}</strong>
                            </span>
                        </div>
                    </div>

                    <!-- Kartu Statistik Presensi Ruangan Ini -->
                    <div
                        class="lg:col-span-5 m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-zinc-200/80 dark:border-zinc-800 flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <h4 class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    Statistik Presensi Ruangan
                                </h4>
                            </div>
                            <span
                                class="text-xs font-black font-mono {{ $persenHadir >= 90 ? 'text-emerald-600' : 'text-amber-600' }}"
                                id="statPersen">
                                {{ $persenHadir }}% Hadir
                            </span>
                        </div>

                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 text-center">
                            <div
                                class="p-2 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200/60 dark:border-zinc-800">
                                <span class="text-[9.5px] font-black text-zinc-400 block uppercase">Total</span>
                                <span class="text-sm font-black text-zinc-900 dark:text-white font-mono"
                                    id="statTotal">{{ $totalPeserta }}</span>
                            </div>
                            <div class="p-2 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                                <span class="text-[9.5px] font-black text-emerald-600 block uppercase">Hadir</span>
                                <span class="text-sm font-black text-emerald-700 dark:text-emerald-400 font-mono"
                                    id="statHadir">{{ $countHadir }}</span>
                            </div>
                            <div class="p-2 bg-amber-500/10 rounded-xl border border-amber-500/20">
                                <span class="text-[9.5px] font-black text-amber-600 block uppercase">Sakit</span>
                                <span class="text-sm font-black text-amber-700 dark:text-amber-400 font-mono"
                                    id="statSakit">{{ $countSakit }}</span>
                            </div>
                            <div class="p-2 bg-blue-500/10 rounded-xl border border-blue-500/20">
                                <span class="text-[9.5px] font-black text-blue-600 block uppercase">Izin</span>
                                <span class="text-sm font-black text-blue-700 dark:text-blue-400 font-mono"
                                    id="statIzin">{{ $countIzin }}</span>
                            </div>
                            <div class="p-2 bg-rose-500/10 rounded-xl border border-rose-500/20">
                                <span class="text-[9.5px] font-black text-rose-600 block uppercase">Alpha</span>
                                <span class="text-sm font-black text-rose-700 dark:text-rose-400 font-mono"
                                    id="statAlpha">{{ $countAlpha }}</span>
                            </div>
                            <div
                                class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-xl border border-zinc-200/60 dark:border-zinc-700">
                                <span class="text-[9.5px] font-black text-zinc-500 block uppercase">Belum</span>
                                <span class="text-sm font-black text-zinc-700 dark:text-zinc-300 font-mono"
                                    id="statBelum">{{ $countBelumDiisi }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Input Presensi IMNI -->
                <form action="{{ route('presensi-imni.store') }}" method="POST" id="formPresensiImni"
                    class="space-y-4 m-0">
                    @csrf
                    <input type="hidden" name="kategori" value="imni">
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunId }}">
                    <input type="hidden" name="tanggal_ujian" value="{{ $selectedTanggal }}">
                    <input type="hidden" name="ruangan_imni_id" value="{{ $selectedRuanganId }}">

                    <!-- Daftar Presensi Santri Per Meja (1..N) -->
                    <div
                        class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                        <div
                            class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                <h4 class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    Daftar Hadir Peserta Ruangan {{ $selectedRuangan?->nama_ruangan_imni ?: 'R1' }} (Urutan
                                    Meja 1..N)
                                </h4>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="setSemuaHadir()"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-black hover:bg-emerald-500/20 transition-all border border-emerald-500/20 cursor-pointer flex items-center gap-1">
                                    <i class="bi bi-check-all text-sm"></i>
                                    <span>Semua Hadir</span>
                                </button>
                                <button type="button" onclick="kosongkanSemua()"
                                    class="px-2.5 py-1.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 text-xs font-bold transition-all border border-zinc-200/80 dark:border-zinc-700 cursor-pointer flex items-center gap-1"
                                    title="Kosongkan semua pilihan presensi">
                                    <i class="bi bi-slash-circle text-xs text-rose-500"></i>
                                    <span>Kosongkan</span>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60">
                                    <tr>
                                        <th class="py-3 px-4 text-center w-14">Meja</th>
                                        <th class="py-3 px-4 w-28">NISM</th>
                                        <th class="py-3 px-4">Nama Lengkap Santri</th>
                                        <th class="py-3 px-4 text-center w-24">Tingkat</th>
                                        <th class="py-3 px-4 text-center w-72">Status Kehadiran</th>
                                        <th class="py-3 px-4 w-48">Catatan Khusus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                    @forelse ($pesertaPlotted as $p)
                                        @php
                                            $muridId = $p->murid_id ?? $p->pesertaImni?->murid_id;
                                            $curStatus = $presensiExisting->get($muridId)?->status;
                                            $curCatatan = $presensiExisting->get($muridId)?->catatan ?? '';
                                            $tingkatId = $p->pesertaImni?->tingkat_id ?? 2;
                                        @endphp
                                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors">
                                            <td
                                                class="py-3 px-4 text-center font-mono font-black text-indigo-600 dark:text-indigo-400 text-xs">
                                                <span
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-indigo-500/10 border border-indigo-500/20">
                                                    {{ $p->nomor_meja ?? '-' }}
                                                </span>
                                            </td>
                                            <td
                                                class="py-3 px-4 font-mono font-bold text-zinc-500 dark:text-zinc-400 text-[11px]">
                                                {{ $p->murid?->nism ?? ($p->pesertaImni?->murid?->nism ?? '-') }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-xs text-zinc-900 dark:text-white">
                                                    {{ $p->murid?->nama_lengkap ?? ($p->pesertaImni?->murid?->nama_lengkap ?? '-') }}
                                                </div>
                                                <div class="text-[10.5px] text-zinc-400">
                                                    {{ $p->murid?->waliMurid?->kampung?->nama_kampung ?? ($p->pesertaImni?->murid?->waliMurid?->kampung?->nama_kampung ?? 'Santri MDT') }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <input type="hidden" name="presensi[{{ $muridId }}][tingkat_id]"
                                                    value="{{ $tingkatId }}">
                                                @if ($tingkatId == 2)
                                                    <span
                                                        class="inline-block px-2 py-0.5 rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400 font-black text-[10px] border border-orange-500/20">
                                                        6 IBT
                                                    </span>
                                                @elseif ($tingkatId == 3)
                                                    <span
                                                        class="inline-block px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black text-[10px] border border-blue-500/20">
                                                        3 TSA
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-block px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 font-black text-[10px]">
                                                        TPQ
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @foreach (['Hadir', 'Sakit', 'Izin', 'Alpha'] as $stOption)
                                                        @php
                                                            $isChecked = $curStatus === $stOption;
                                                            $badgeStyle = match ($stOption) {
                                                                'Hadir'
                                                                    => 'peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600',
                                                                'Sakit'
                                                                    => 'peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-amber-600',
                                                                'Izin'
                                                                    => 'peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600',
                                                                'Alpha'
                                                                    => 'peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600',
                                                                default
                                                                    => 'peer-checked:bg-emerald-600 peer-checked:text-white',
                                                            };
                                                        @endphp
                                                        <label class="cursor-pointer">
                                                            <input type="radio"
                                                                name="presensi[{{ $muridId }}][status]"
                                                                value="{{ $stOption }}"
                                                                class="sr-only peer radio-status-imni"
                                                                {{ $isChecked ? 'checked' : '' }}
                                                                onchange="updateStatsImni()">
                                                            <span
                                                                class="px-2.5 py-1 rounded-lg text-[10.5px] font-black border transition-all select-none block {{ $badgeStyle }} bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                                                {{ $stOption }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <input type="text" name="presensi[{{ $muridId }}][catatan]"
                                                    value="{{ $curCatatan }}" placeholder="Catatan..."
                                                    class="m3-input-glass w-full text-[11px] py-1 px-2">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-12 text-center text-zinc-400 italic">
                                                Belum ada peserta yang diplot pada Ruangan
                                                {{ $selectedRuangan?->nama_ruangan_imni }} untuk tanggal ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($pesertaPlotted->isNotEmpty())
                            <div
                                class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                                <div class="text-xs text-zinc-500">
                                    Total: <strong
                                        class="text-zinc-800 dark:text-zinc-200">{{ $pesertaPlotted->count() }}</strong>
                                    santri di ruangan ini.
                                </div>
                                <button type="submit" id="btnSimpanPresensiImni"
                                    class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-black flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                                    <i class="bi bi-save2"></i>
                                    <span>Simpan Presensi Ruangan</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            @endif
        </div>

        <!-- SCRIPT JAVASCRIPT AJAX & INTERAKTIF -->
        <script>
            function setSemuaHadir() {
                @if ($kategori === 'tpq')
                    document.querySelectorAll('input.radio-status-tpq[value="Hadir"]').forEach(r => r.checked = true);
                    updateStatsTpq();
                @else
                    document.querySelectorAll('input.radio-status-imni[value="Hadir"]').forEach(r => r.checked = true);
                    updateStatsImni();
                @endif
            }

            function kosongkanSemua() {
                @if ($kategori === 'tpq')
                    document.querySelectorAll('input.radio-status-tpq').forEach(r => r.checked = false);
                    updateStatsTpq();
                @else
                    document.querySelectorAll('input.radio-status-imni').forEach(r => r.checked = false);
                    updateStatsImni();
                @endif
            }

            function updateStatsTpq() {
                const total = {{ $totalTpq ?? 0 }};
                const allRadios = document.querySelectorAll('input.radio-status-tpq:checked');
                let hadir = 0,
                    sakit = 0,
                    izin = 0,
                    alpha = 0;
                allRadios.forEach(r => {
                    if (r.value === 'Hadir' || r.value === 'Dispensasi') hadir++;
                    else if (r.value === 'Sakit') sakit++;
                    else if (r.value === 'Izin') izin++;
                    else if (r.value === 'Alpha') alpha++;
                });
                const terisi = hadir + sakit + izin + alpha;
                const belum = Math.max(0, total - terisi);

                const statHadirTpq = document.getElementById('statHadirTpq');
                const statSakitTpq = document.getElementById('statSakitTpq');
                const statIzinTpq = document.getElementById('statIzinTpq');
                const statAlphaTpq = document.getElementById('statAlphaTpq');
                const statBelumTpq = document.getElementById('statBelumTpq');

                if (statHadirTpq) statHadirTpq.textContent = hadir;
                if (statSakitTpq) statSakitTpq.textContent = sakit;
                if (statIzinTpq) statIzinTpq.textContent = izin;
                if (statAlphaTpq) statAlphaTpq.textContent = alpha;
                if (statBelumTpq) statBelumTpq.textContent = belum;
            }

            function updateStatsImni() {
                const total = {{ $totalPeserta ?? 0 }};
                const allRadios = document.querySelectorAll('input.radio-status-imni:checked');
                let hadir = 0,
                    sakit = 0,
                    izin = 0,
                    alpha = 0;
                allRadios.forEach(r => {
                    if (r.value === 'Hadir' || r.value === 'Dispensasi') hadir++;
                    else if (r.value === 'Sakit') sakit++;
                    else if (r.value === 'Izin') izin++;
                    else if (r.value === 'Alpha') alpha++;
                });

                const terisi = hadir + sakit + izin + alpha;
                const belum = Math.max(0, total - terisi);
                const persen = total > 0 ? ((hadir / total) * 100).toFixed(1) : 0;

                const statHadir = document.getElementById('statHadir');
                const statSakit = document.getElementById('statSakit');
                const statIzin = document.getElementById('statIzin');
                const statAlpha = document.getElementById('statAlpha');
                const statBelum = document.getElementById('statBelum');
                const statPersen = document.getElementById('statPersen');

                if (statHadir) statHadir.textContent = hadir;
                if (statSakit) statSakit.textContent = sakit;
                if (statIzin) statIzin.textContent = izin;
                if (statAlpha) statAlpha.textContent = alpha;
                if (statBelum) statBelum.textContent = belum;
                if (statPersen) statPersen.textContent = persen + '% Hadir';
            }

            // Form Submit Handler AJAX untuk TPQ dan IMNI
            const activeForm = document.getElementById('formPresensiTpq') || document.getElementById('formPresensiImni');
            if (activeForm) {
                activeForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...';
                    }

                    const formData = new FormData(form);

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnHtml;
                            }

                            if (data.status === 'success') {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Presensi Disimpan!',
                                        text: data.message,
                                        timer: 1400,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    alert(data.message);
                                    window.location.reload();
                                }
                            } else {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire('Gagal!', data.message ||
                                        'Terjadi kesalahan saat menyimpan presensi.', 'error');
                                } else {
                                    alert(data.message || 'Terjadi kesalahan saat menyimpan presensi.');
                                }
                            }
                        })
                        .catch(err => {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnHtml;
                            }
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error!', 'Gagal menghubungi server: ' + err, 'error');
                            } else {
                                alert('Gagal menghubungi server.');
                            }
                        });
                });
            }
        </script>
    </x-app-layout>
