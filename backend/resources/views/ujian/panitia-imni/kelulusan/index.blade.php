@section('title', 'Putusan Kelulusan Akhir (IMNI)')

<x-app-layout>

    <!-- HEADER -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Putusan Kelulusan Akhir (IMNI)
            </h2>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 uppercase tracking-wider">
                Kalkulasi algoritma sistem, pertimbangan panitia/wali kelas, dan pengesahan SK & Ijazah akhir (3 TPQ, 6 IBT, 3 TSA)
            </p>
        </div>
        <!-- Dropdown Cetak Berkas Massal Sidang -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="relative" x-data="{ openCetak: false }">
                <button type="button" @click="openCetak = !openCetak" @click.outside="openCetak = false"
                    class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                    <i class="bi bi-printer-fill text-sm text-rose-600"></i>
                    <span>Cetak Berkas Sidang</span>
                    <i class="bi bi-chevron-down text-[10px]"></i>
                </button>

                <div x-show="openCetak" x-transition
                    class="absolute right-0 mt-2 w-64 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200/80 dark:border-zinc-800 py-1.5 z-50">
                    <a href="{{ route('putusan-imni.cetak-sk-panitia', ['tahun_id' => $tahunPelajaranId]) }}"
                        target="_blank"
                        class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-primary/10 hover:text-primary transition-colors">
                        <i class="bi bi-file-earmark-text text-indigo-600"></i>
                        <span>Cetak SK Kelulusan Panitia</span>
                    </a>
                    <a href="{{ route('putusan-imni.cetak-leger', ['tahun_id' => $tahunPelajaranId]) }}"
                        target="_blank"
                        class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-primary/10 hover:text-primary transition-colors">
                        <i class="bi bi-table text-emerald-600"></i>
                        <span>Cetak Leger Yudisium Akhir</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ALERTS -->
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

            <!-- Filter Ruangan Kelas Akhir -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Pilih
                    Kelas Akhir (3 TPQ / 6 IBT / 3 TSA)</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-sm"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formKenaikan').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        <option value="">-- Silakan Pilih Ruangan Kelas Akhir --</option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}" {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }} ({{ $r->level->nama_level ?? '' }})
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
    @if (request('ruangan_id') && $ruanganTerpilih)
        <div class="m3-glass-card overflow-hidden relative group animate-[modalFadeIn_0.2s_ease-out]">

            <form action="{{ route('putusan-imni.simpan') }}" method="POST" id="formSimpanKeputusan"
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
                            <span>Tabel Pertimbangan Algoritma Kelulusan Akhir ({{ $ruanganTerpilih->nama_ruangan }})</span>
                        </h3>
                        <p
                            class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 uppercase tracking-wider flex items-center flex-wrap gap-1.5">
                            <span>Rasio Bobot:</span>
                            <span
                                class="text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200/80 dark:border-amber-800/40 font-black">
                                Ujian ({{ number_format($config->bobot_imda ?? 60, 0) }}%) +
                                Presensi ({{ number_format($config->bobot_presensi ?? 24, 0) }}%) +
                                Disiplin ({{ number_format($config->bobot_pelanggaran ?? 16, 0) }}%)
                            </span>
                        </p>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-2 shrink-0">
                        <span
                            class="bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 px-2.5 py-1 rounded-md border border-emerald-200/80 dark:border-emerald-800/40 text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-arrow-up-right-circle-fill"></i> KKM > 55 (Lulus)
                        </span>
                        <span
                            class="bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 px-2.5 py-1 rounded-md border border-rose-200/80 dark:border-rose-800/40 text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-arrow-down-right-circle-fill"></i> ≤ 55 (Tinggal Kelas)
                        </span>
                    </div>
                </div>

                <!-- SCROLLABLE TABEL -->
                <div class="overflow-x-auto custom-scrollbar p-0">
                    <table class="w-full text-left border-collapse min-w-[950px] text-xs">
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
                                    class="py-3 px-4 border-r border-zinc-200/80 dark:border-zinc-800 sticky left-12 z-30 bg-zinc-100/90 dark:bg-zinc-900/90 backdrop-blur-md shadow-[2px_0_5px_rgba(0,0,0,0.03)] w-56">
                                    Nama Murid</th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center w-28">
                                    Tot. Sem 1<br><span class="text-[9px] opacity-70">(IMDA 1)</span></th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center w-28">
                                    Tot. Sem 2<br><span class="text-[9px] opacity-70">(IMNI)</span>
                                </th>
                                <th
                                    class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center bg-primary/5 dark:bg-primary-dark/10 text-primary dark:text-primary-dark w-28">
                                    Final Akumulasi</th>
                                <th class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 w-32 text-center">
                                    Rekomendasi
                                </th>
                                <th class="py-3 px-3 border-r border-zinc-200/80 dark:border-zinc-800 w-40 text-center">
                                    Keputusan</th>
                                <th
                                    class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 w-52 text-center">
                                    Catatan Khusus</th>
                                <th class="py-3 px-3 text-center w-36">Aksi & Berkas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/80 bg-white dark:bg-zinc-900">
                            @forelse ($dataKenaikan as $row)
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
                                        class="py-2.5 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle text-center">
                                        @if ($row->rekomendasi == 'Tinggal Kelas')
                                            <span
                                                class="inline-flex items-center gap-1 text-[9px] font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="bi bi-arrow-down-right-circle-fill text-xs"></i> Tinggal
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 text-[9px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="bi bi-mortarboard-fill text-xs"></i> Lulus
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Sel Kunci Keputusan -->
                                    <td
                                        class="py-2.5 px-2.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        <select name="keputusan[{{ $row->murid->id }}]"
                                            onchange="ubahKeputusan(this)"
                                            {{ $row->sudah_dikunci ? 'disabled' : '' }}
                                            class="w-full h-8.5 rounded-lg px-2 text-[10px] font-black uppercase tracking-wider outline-none transition-all cursor-pointer text-center disabled:opacity-60 {{ $row->keputusan_final == 'Tinggal Kelas' ? 'text-rose-600 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/40' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40' }}">
                                            <option value="Lulus"
                                                {{ $row->keputusan_final == 'Lulus' ? 'selected' : '' }}
                                                class="text-zinc-900 dark:text-white font-bold">LULUS</option>
                                            <option value="Tinggal Kelas"
                                                {{ $row->keputusan_final == 'Tinggal Kelas' ? 'selected' : '' }}
                                                class="text-zinc-900 dark:text-white font-bold">TINGGAL KELAS</option>
                                        </select>
                                    </td>

                                    <!-- Sel Catatan -->
                                    <td
                                        class="py-2.5 px-2.5 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        <input type="text" name="catatan[{{ $row->murid->id }}]"
                                            value="{{ $row->catatan }}" placeholder="Catatan kelulusan..."
                                            {{ $row->sudah_dikunci ? 'disabled' : '' }}
                                            class="m3-input-glass w-full h-8.5 px-2.5 text-xs font-semibold disabled:opacity-60">
                                    </td>

                                    <!-- Sel Aksi Cetak & Simpan -->
                                    <td class="py-2.5 px-2.5 text-center align-middle">
                                        @if ($row->sudah_dikunci)
                                            <div class="flex items-center justify-center gap-1.5">
                                                <!-- Cetak SK Arsip -->
                                                <a href="{{ route('putusan-imni.cetak-sk', [$tahunPelajaranId, request('ruangan_id'), $row->murid->id]) }}"
                                                    target="_blank"
                                                    class="m3-btn-secondary w-7 h-7 !p-0 inline-flex items-center justify-center shadow-2xs"
                                                    title="Cetak SK Arsip">
                                                    <i class="bi bi-printer text-xs"></i>
                                                </a>

                                                @if ($row->keputusan_final === 'Lulus')
                                                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-800 mx-0.5"></div>
                                                    <!-- Cetak Ijazah Arsip -->
                                                    <a href="{{ route('putusan-imni.cetak-ijazah', [$tahunPelajaranId, request('ruangan_id'), $row->murid->id]) }}"
                                                        target="_blank"
                                                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-all shadow-2xs"
                                                        title="Cetak Ijazah Arsip">
                                                        <i class="bi bi-mortarboard text-xs"></i>
                                                    </a>
                                                @endif

                                                @if ($row->peserta_imni)
                                                    <a href="{{ route('putusan-imni.cetak-transkrip', ['peserta_id' => $row->peserta_imni->id]) }}"
                                                        target="_blank"
                                                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 transition-all shadow-2xs"
                                                        title="Cetak Transkrip">
                                                        <i class="bi bi-file-earmark-text text-xs"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <!-- Tombol Simpan Individu -->
                                            <button type="button" onclick="simpanIndividu({{ $row->murid->id }})"
                                                class="m3-btn-primary h-7.5 px-2.5 text-[10px] w-full justify-center uppercase tracking-wider font-black">
                                                <i class="bi bi-shield-check text-xs"></i>
                                                <span>Sahkan</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-zinc-400 font-bold text-xs">
                                        Tidak ada murid aktif ditemukan pada ruangan kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER / TOMBOL SIMPAN MASSAL -->
                <div
                    class="px-5 py-3.5 border-t border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-950/60 flex justify-end shrink-0">
                    <button type="button" onclick="konfirmasiSimpan('bulk')"
                        class="m3-btn-primary h-10 px-5 group/btn">
                        <i class="bi bi-shield-check text-lg leading-none"></i>
                        <span>Sahkan Terpilih</span>
                    </button>
                </div>
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

            function ubahKeputusan(selectObj) {
                const status = selectObj.value;
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
                        html: '<p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Silakan centang minimal satu murid yang ingin disahkan kelulusannya.</p>',
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

                const titleText = mode === 'individu' ? 'Sahkan Kelulusan Murid Ini?' :
                    `Sahkan ${selectedCount} Kelulusan Terpilih?`;

                Swal.fire({
                    title: `<span class="text-base font-black text-zinc-900 dark:text-white">${titleText}</span>`,
                    html: '<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1 mb-2 leading-relaxed">Pastikan pilihan <b class="text-emerald-500">Lulus</b> atau <b class="text-rose-500">Tinggal Kelas</b> sudah tepat.<br><br>Data ini akan dikunci sebagai riwayat kelulusan permanen dan menerbitkan arsip E-Document SK & Ijazah.</p>',
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
                            title: '<span class="text-sm font-bold text-zinc-900 dark:text-white">Menyimpan & Menerbitkan Arsip...</span>',
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

                        document.getElementById('formSimpanKeputusan').submit();
                    }
                });
            }
        </script>
    @else
        <!-- STATE AWAL PANDUAN PENGGUNAAN -->
        <x-empty-state icon="bi-mortarboard" title="Putusan Kelulusan Akhir (IMNI)"
            message="Tentukan Tahun Pelajaran dan Ruangan Kelas Akhir pada filter di atas untuk meninjau kalkulasi rekomendasi sistem dan mengesahkan keputusan kelulusan murid (3 TPQ, 6 IBT, 3 TSA)." />
    @endif

</x-app-layout>
