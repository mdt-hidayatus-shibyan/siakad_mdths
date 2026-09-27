@section('title', 'Kenaikan Kelas')

<x-app-layout>

    <!-- HEADER -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Kenaikan & Kelulusan
            </h2>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 uppercase tracking-wider">
                Kalkulasi algoritma sistem, pertimbangan wali kelas, dan penetapan SK akhir tahun
            </p>
        </div>
    </div>

    <!-- FILTER AREA -->
    <div class="m3-glass-card p-4 sm:p-5 mb-6 relative z-10 animate-[modalFadeIn_0.2s_ease-out]">
        <form action="{{ request()->url() }}" method="GET" id="formKenaikan"
            class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-3.5 items-end">

            <!-- Filter Tahun Pelajaran -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Tahun
                    Pelajaran</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-range text-sm"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formKenaikan').submit()"
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
            </div>

            <!-- Filter Ruangan -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Pilih
                    Kelas</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-sm"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formKenaikan').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        <option value="">-- Silakan Pilih Ruangan --</option>
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
            </div>

        </form>
    </div>

    <!-- AREA KONTEN UTAMA -->
    @if (request('ruangan_id'))
        @php
            $levelNama = $ruanganTerpilih->level->nama_level ?? '';
            $isTerminal = $levelNama === '3 TSA';
            $levelSekarangId = $ruanganTerpilih->level_id ?? null;
            $levelNaikId = isset($daftarLevel) && $daftarLevel->count() > 1 ? $daftarLevel[1]->id : $levelSekarangId;
        @endphp

        <div class="m3-glass-card overflow-hidden relative group animate-[modalFadeIn_0.2s_ease-out]">

            <form action="{{ route('kenaikan-kelas.simpan') }}" method="POST" id="formSimpanKeputusan"
                class="relative z-10 flex flex-col h-full">
                @csrf
                <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">
                <input type="hidden" name="ruangan_asal_id" value="{{ request('ruangan_id') }}">

                <!-- HEADER TABEL -->
                <div
                    class="bg-zinc-50/90 dark:bg-zinc-950/90 border-b border-zinc-200/80 dark:border-zinc-800 px-5 py-4 flex flex-col xl:flex-row xl:items-center justify-between gap-3.5">
                    <div>
                        <h3
                            class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-snug flex items-center gap-2">
                            <div
                                class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs shadow-2xs shrink-0">
                                <i class="bi bi-robot"></i>
                            </div>
                            <span>Tabel Pertimbangan Algoritma Kenaikan</span>
                        </h3>
                        <p
                            class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 uppercase tracking-wider flex items-center flex-wrap gap-1.5">
                            <span>Rasio Bobot:</span>
                            <span
                                class="text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200/80 dark:border-amber-800/40 font-black">
                                Ujian ({{ number_format($config->bobot_imda ?? 60, 0) }}%) +
                                Presensi ({{ number_format($config->bobot_presensi ?? 36, 0) }}%) +
                                Disiplin ({{ number_format($config->bobot_pelanggaran ?? 24, 0) }}%)
                            </span>
                        </p>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-2 shrink-0">
                        <span
                            class="bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 px-2.5 py-1 rounded-md border border-emerald-200/80 dark:border-emerald-800/40 text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-arrow-up-right-circle-fill"></i> KKM > 55 (Naik/Lulus)
                        </span>
                        <span
                            class="bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 px-2.5 py-1 rounded-md border border-rose-200/80 dark:border-rose-800/40 text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-arrow-down-right-circle-fill"></i> ≤ 55 (Tinggal)
                        </span>
                    </div>
                </div>

                <!-- SCROLLABLE TABEL -->
                <div class="overflow-x-auto custom-scrollbar p-0">
                    <table class="w-full text-left border-collapse min-w-[1000px] text-xs">
                        <thead
                            class="bg-zinc-50/90 dark:bg-zinc-950/90 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                            <tr
                                class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                <!-- Header Checkbox Semua -->
                                <th
                                    class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 text-center w-12 sticky left-0 z-30 bg-zinc-100/90 dark:bg-zinc-900/90 backdrop-blur-md">
                                    <input type="checkbox" id="checkAll"
                                        class="rounded border-zinc-300 dark:border-zinc-700 text-primary dark:text-primary-dark focus:ring-primary dark:focus:ring-primary-dark dark:bg-zinc-800 cursor-pointer w-4 h-4">
                                </th>
                                <th
                                    class="py-3 px-4 border-r border-zinc-200/80 dark:border-zinc-800 sticky left-12 z-30 bg-zinc-100/90 dark:bg-zinc-900/90 backdrop-blur-md shadow-[2px_0_5px_rgba(0,0,0,0.03)] w-52">
                                    Nama Murid</th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center w-24">
                                    Tot. Sem 1<br><span class="text-[9px] opacity-70">(IMDA 1)</span></th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center w-24">
                                    Tot. Sem 2<br><span
                                        class="text-[9px] opacity-70">({{ $isKelasAkhir ? 'IMNI' : 'IMDA 2' }})</span>
                                </th>
                                <th
                                    class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center bg-primary/5 dark:bg-primary-dark/10 text-primary dark:text-primary-dark w-28">
                                    Final Akumulasi</th>
                                <th class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 w-32">
                                    Rekomendasi
                                </th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 w-36 text-center">
                                    Keputusan</th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 w-36 text-center">
                                    Level Tujuan</th>
                                <th
                                    class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 w-48 text-center">
                                    Catatan Khusus</th>
                                <th class="py-3 px-3 text-center w-32">Aksi & Berkas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/80 bg-white dark:bg-zinc-900">
                            @foreach ($dataKenaikan as $row)
                                <input type="hidden" name="nilai_akumulasi[{{ $row->murid->id }}]"
                                    value="{{ $row->nilai_akumulasi }}">

                                <tr
                                    class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group/row {{ $row->sudah_dikunci ? 'bg-emerald-50/10 dark:bg-emerald-950/10' : '' }}">

                                    <!-- Sel Checkbox Individual -->
                                    <td
                                        class="py-2.5 px-3.5 text-center sticky left-0 z-20 bg-white dark:bg-zinc-900 group-hover/row:bg-zinc-50 dark:group-hover/row:bg-zinc-800/80 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        @if (!$row->sudah_dikunci)
                                            <input type="checkbox" name="selected_murid[]"
                                                value="{{ $row->murid->id }}"
                                                class="row-checkbox rounded border-zinc-300 dark:border-zinc-700 text-primary dark:text-primary-dark focus:ring-primary dark:focus:ring-primary-dark dark:bg-zinc-800 cursor-pointer w-4 h-4">
                                        @else
                                            <i class="bi bi-check-circle-fill text-emerald-500 text-sm"
                                                title="Sudah Disahkan"></i>
                                        @endif
                                    </td>

                                    <!-- Sel Nama -->
                                    <td
                                        class="py-2.5 px-4 sticky left-12 z-20 bg-white dark:bg-zinc-900 group-hover/row:bg-zinc-50 dark:group-hover/row:bg-zinc-800/80 border-r border-zinc-200/80 dark:border-zinc-800 shadow-[2px_0_5px_rgba(0,0,0,0.02)] transition-colors align-middle">
                                        <div class="flex flex-col gap-0.5">
                                            <span
                                                class="font-black text-xs text-zinc-900 dark:text-zinc-100 tracking-tight">{{ $row->murid->nama_lengkap }}</span>
                                            <span
                                                class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider truncate max-w-[180px]">{{ $row->murid->nism ?? 'NISM KOSONG' }}</span>
                                        </div>
                                    </td>

                                    <!-- Sel Nilai Sem 1 -->
                                    <td class="py-2.5 px-3 text-center font-black text-zinc-700 dark:text-zinc-300 border-r border-zinc-200/80 dark:border-zinc-800 align-middle"
                                        title="Ujian: {{ $row->detail_sem1['rata_ujian'] ?? 0 }} ({{ $config->bobot_imda ?? 60 }}%) | Hadir: {{ $row->detail_sem1['nilai_hadir'] ?? 100 }} [A:{{ $row->detail_sem1['alpha'] ?? 0 }}, I:{{ $row->detail_sem1['izin'] ?? 0 }}] ({{ $config->bobot_presensi ?? 24 }}%) | Disiplin: {{ $row->detail_sem1['nilai_pelanggaran'] ?? 100 }} [Poin: {{ $row->detail_sem1['poin_pelanggaran'] ?? 0 }}] ({{ $config->bobot_pelanggaran ?? 16 }}%)">
                                        <div class="flex flex-col items-center">
                                            <span>{{ $row->skor_sem1 ?: '-' }}</span>
                                            @if (isset($row->detail_sem1))
                                                <span
                                                    class="text-[9px] font-medium text-zinc-400 dark:text-zinc-500 tracking-tight">
                                                    A:{{ $row->detail_sem1['alpha'] }}
                                                    I:{{ $row->detail_sem1['izin'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Sel Nilai Sem 2 -->
                                    <td class="py-2.5 px-3 text-center font-black text-zinc-700 dark:text-zinc-300 border-r border-zinc-200/80 dark:border-zinc-800 align-middle"
                                        title="Ujian: {{ $row->detail_sem2['rata_ujian'] ?? 0 }} ({{ $config->bobot_imda ?? 60 }}%) | Hadir: {{ $row->detail_sem2['nilai_hadir'] ?? 100 }} [A:{{ $row->detail_sem2['alpha'] ?? 0 }}, I:{{ $row->detail_sem2['izin'] ?? 0 }}] ({{ $config->bobot_presensi ?? 24 }}%) | Disiplin: {{ $row->detail_sem2['nilai_pelanggaran'] ?? 100 }} [Poin: {{ $row->detail_sem2['poin_pelanggaran'] ?? 0 }}] ({{ $config->bobot_pelanggaran ?? 16 }}%)">
                                        <div class="flex flex-col items-center">
                                            <span>{{ $row->skor_sem2 ?: '-' }}</span>
                                            @if (isset($row->detail_sem2))
                                                <span
                                                    class="text-[9px] font-medium text-zinc-400 dark:text-zinc-500 tracking-tight">
                                                    A:{{ $row->detail_sem2['alpha'] }}
                                                    I:{{ $row->detail_sem2['izin'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Sel Akumulasi Final -->
                                    <td
                                        class="py-2.5 px-3 text-center font-black text-sm border-r border-zinc-200/80 dark:border-zinc-800 bg-primary/5 dark:bg-primary-dark/5 align-middle {{ $row->nilai_akumulasi <= 55 ? 'text-rose-600 dark:text-rose-400' : 'text-primary dark:text-primary-dark' }}">
                                        {{ $row->nilai_akumulasi }}
                                    </td>

                                    <!-- Sel Rekomendasi -->
                                    <td
                                        class="py-2.5 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        @if ($row->rekomendasi == 'Tinggal Kelas')
                                            <span
                                                class="inline-flex items-center gap-1 text-[9px] font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="bi bi-arrow-down-right-circle-fill text-xs"></i> Tinggal
                                            </span>
                                        @elseif($row->rekomendasi == 'Lulus')
                                            <span
                                                class="inline-flex items-center gap-1 text-[9px] font-black text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="bi bi-mortarboard-fill text-xs"></i> Lulus
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 text-[9px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="bi bi-arrow-up-right-circle-fill text-xs"></i> Naik
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Sel Kunci Keputusan -->
                                    <td
                                        class="py-2.5 px-2.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        <select name="keputusan[{{ $row->murid->id }}]"
                                            onchange="ubahLevelTujuan(this, {{ $row->murid->id }}, {{ $levelSekarangId ?? 'null' }}, {{ $levelNaikId ?? 'null' }}, {{ $isTerminal ? 'true' : 'false' }})"
                                            {{ $row->sudah_dikunci ? 'disabled' : '' }}
                                            class="w-full h-8.5 rounded-lg px-2 text-[10px] font-black uppercase tracking-wider outline-none transition-all cursor-pointer text-center disabled:opacity-60 {{ $row->keputusan_final == 'Tinggal Kelas' ? 'text-rose-600 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/40' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40' }}">
                                            @if ($isKelasAkhir)
                                                <option value="Lulus"
                                                    {{ $row->keputusan_final == 'Lulus' ? 'selected' : '' }}
                                                    class="text-zinc-900 dark:text-white font-bold">LULUS</option>
                                            @else
                                                <option value="Naik Kelas"
                                                    {{ $row->keputusan_final == 'Naik Kelas' ? 'selected' : '' }}
                                                    class="text-zinc-900 dark:text-white font-bold">NAIK KELAS</option>
                                            @endif
                                            <option value="Tinggal Kelas"
                                                {{ $row->keputusan_final == 'Tinggal Kelas' ? 'selected' : '' }}
                                                class="text-zinc-900 dark:text-white font-bold">TINGGAL KELAS</option>
                                        </select>
                                    </td>

                                    <!-- Sel Level Tujuan -->
                                    <td
                                        class="py-2.5 px-2.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        <select name="level_tujuan[{{ $row->murid->id }}]"
                                            id="level_tujuan_{{ $row->murid->id }}"
                                            {{ ($row->keputusan_final == 'Lulus' && $isTerminal) || $row->sudah_dikunci ? 'disabled' : '' }}
                                            class="m3-input-glass w-full h-8.5 text-[10px] font-black uppercase tracking-wider text-center disabled:opacity-50 disabled:bg-zinc-100 dark:disabled:bg-zinc-800">
                                            <option value="">- PILIH -</option>
                                            @if (isset($daftarLevel))
                                                @foreach ($daftarLevel as $level)
                                                    <option value="{{ $level->id }}"
                                                        {{ $row->level_tujuan_id == $level->id || ($row->keputusan_final == 'Lulus' && !$isTerminal && $level->id == $levelNaikId) ? 'selected' : '' }}>
                                                        {{ strtoupper($level->nama_level) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </td>

                                    <!-- Sel Catatan -->
                                    <td
                                        class="py-2.5 px-2.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        <input type="text" name="catatan[{{ $row->murid->id }}]"
                                            value="{{ $row->catatan }}" placeholder="Catatan..."
                                            {{ $row->sudah_dikunci ? 'disabled' : '' }}
                                            class="m3-input-glass w-full h-8.5 px-2.5 text-xs font-semibold disabled:opacity-60">
                                    </td>

                                    <!-- Sel Aksi Cetak & Simpan -->
                                    <td class="py-2.5 px-2.5 text-center align-middle">
                                        @if ($row->sudah_dikunci)
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('kenaikan-kelas.cetak_sk', [$tahunPelajaranId, request('ruangan_id'), $row->murid->id]) }}"
                                                    target="_blank"
                                                    class="m3-btn-secondary w-7 h-7 !p-0 inline-flex items-center justify-center shadow-2xs"
                                                    title="Cetak SK">
                                                    <i class="bi bi-printer text-xs"></i>
                                                </a>
                                                @if ($row->keputusan_final === 'Lulus')
                                                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-800 mx-0.5"></div>
                                                    <a href="{{ route('kenaikan-kelas.cetak_ijazah', [$tahunPelajaranId, request('ruangan_id'), $row->murid->id]) }}"
                                                        target="_blank"
                                                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-all shadow-2xs"
                                                        title="Cetak Ijazah">
                                                        <i class="bi bi-mortarboard text-xs"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <!-- Tombol Simpan Individu -->
                                            <button type="button" onclick="simpanIndividu({{ $row->murid->id }})"
                                                class="m3-btn-primary h-7.5 px-2.5 text-[10px] w-full justify-center uppercase tracking-wider font-black">
                                                <i class="bi bi-save2-fill text-xs"></i>
                                                <span>Simpan</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @can('create kenaikan-kelas')
                    <!-- FOOTER / TOMBOL SIMPAN MASSAL -->
                    <div
                        class="px-5 py-3.5 border-t border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-950/60 flex justify-end shrink-0">
                        <button type="button" onclick="konfirmasiSimpan('bulk')"
                            class="m3-btn-primary h-10 px-5 group/btn">
                            <i class="bi bi-check-all text-lg leading-none"></i>
                            <span>Sahkan Terpilih</span>
                        </button>
                    </div>
                @endcan
            </form>
        </div>

        <!-- SCRIPT SWEETALERT & LOGIKA JS -->
        <script>
            // Logika Check All
            document.getElementById('checkAll')?.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });

            // Simpan Individu
            function simpanIndividu(muridId) {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);

                const targetCheckbox = document.querySelector(`.row-checkbox[value="${muridId}"]`);
                if (targetCheckbox) {
                    targetCheckbox.checked = true;
                }

                konfirmasiSimpan('individu');
            }

            function ubahLevelTujuan(selectObj, muridId, levelSekarangId, levelNaikId, isTerminal) {
                const targetSelect = document.getElementById('level_tujuan_' + muridId);
                const status = selectObj.value;

                if (status === 'Lulus') {
                    if (isTerminal) {
                        targetSelect.disabled = true;
                        targetSelect.value = '';
                    } else {
                        targetSelect.disabled = false;
                        targetSelect.value = levelNaikId;
                    }
                } else {
                    targetSelect.disabled = false;
                    if (status === 'Naik Kelas') {
                        targetSelect.value = levelNaikId;
                    } else if (status === 'Tinggal Kelas') {
                        targetSelect.value = levelSekarangId;
                    }
                }

                if (status === 'Tinggal Kelas') {
                    selectObj.classList.remove('text-emerald-600', 'dark:text-emerald-400', 'bg-emerald-50/50',
                        'dark:bg-emerald-950/30', 'border-emerald-200/80', 'dark:border-emerald-800/40');
                    selectObj.classList.add('text-rose-600', 'dark:text-rose-400', 'bg-rose-50/50', 'dark:bg-rose-950/30',
                        'border-rose-200/80', 'dark:border-rose-800/40');
                } else {
                    selectObj.classList.remove('text-rose-600', 'dark:text-rose-400', 'bg-rose-50/50', 'dark:bg-rose-950/30',
                        'border-rose-200/80', 'dark:border-rose-800/40');
                    selectObj.classList.add('text-emerald-600', 'dark:text-emerald-400', 'bg-emerald-50/50',
                        'dark:bg-emerald-950/30', 'border-emerald-200/80', 'dark:border-emerald-800/40');
                }
            }

            function konfirmasiSimpan(mode = 'bulk') {
                const isDark = document.documentElement.classList.contains('dark');

                const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (selectedCount === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: '<span class="text-base font-black text-zinc-900 dark:text-white">Pilih Data!</span>',
                        html: '<p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Silakan centang minimal satu murid yang ingin disahkan.</p>',
                        confirmButtonColor: '#059669',
                        heightAuto: false,
                        background: isDark ? '#09090b' : '#ffffff',
                        customClass: {
                            popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                            confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-xs'
                        }
                    });
                    return;
                }

                const titleText = mode === 'individu' ? 'Sahkan Keputusan Murid Ini?' :
                    `Sahkan ${selectedCount} Keputusan Terpilih?`;

                Swal.fire({
                    title: `<span class="text-base font-black text-zinc-900 dark:text-white">${titleText}</span>`,
                    html: '<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1 mb-2 leading-relaxed">Pastikan pilihan <b class="text-emerald-500">Lulus / Naik</b> atau <b class="text-rose-500">Tinggal Kelas</b> sudah tepat.<br><br>Data ini akan dikunci sebagai riwayat akademik permanen murid.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    heightAuto: false,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
                    confirmButtonText: '<i class="bi bi-shield-check mr-1"></i> Ya, Sahkan!',
                    cancelButtonText: '<span class="text-zinc-700 dark:text-zinc-300">Batal</span>',
                    background: isDark ? '#09090b' : '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-xs',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5 text-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: '<span class="text-sm font-bold text-zinc-900 dark:text-white">Menyimpan Data...</span>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            background: isDark ? '#09090b' : '#ffffff',
                            customClass: {
                                popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl'
                            },
                            didOpen: () => Swal.showLoading()
                        });

                        document.querySelectorAll('.row-checkbox:not(:checked)').forEach(cb => {
                            const tr = cb.closest('tr');
                            tr.querySelectorAll('select, input').forEach(input => {
                                input.disabled = true;
                            });
                        });

                        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                            const tr = cb.closest('tr');
                            const selectLevel = tr.querySelector('select[name^="level_tujuan"]');
                            if (selectLevel) selectLevel.disabled = false;
                        });

                        document.getElementById('formSimpanKeputusan').submit();
                    }
                });
            }
        </script>
    @else
        <!-- STATE AWAL PANDUAN PENGGUNAAN -->
        <x-empty-state icon="bi-mortarboard" title="Keputusan Akhir Tahun"
            message="Tentukan Tahun Pelajaran dan Ruangan Kelas pada filter di atas untuk meninjau kalkulasi rekomendasi sistem dan mengesahkan keputusan kenaikan/kelulusan murid." />
    @endif

</x-app-layout>
