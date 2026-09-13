@section('title', 'Input Nilai Ujian Al-Qur\'an' . ($ujian ? ' - ' . $ujian->nama_ujian : ''))

<x-app-layout>
    <!-- HEADER TOOLBAR -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[11px] font-black uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Input Nilai Admin</span>
                </span>
                <span class="text-xs text-zinc-400 font-mono">
                    {{ $ujian->tahunPelajaran->nama_hijriyah ?? ($daftarTahun->firstWhere('id', $tahunPelajaranId)?->nama_hijriyah ?? '') }}
                </span>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Input Nilai: {{ $ujian->nama_ujian ?? 'Ujian Al-Qur\'an' }}
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Input hasil penilaian Dewan Juri (Khotho' Jali & Khotho' Khofi). Nilai akhir dan kelulusan dihitung
                otomatis.
            </p>
        </div>

        <!-- Filter Tahun Pelajaran & Tombol Aksi Cetak / Navigasi -->
        <div class="w-full md:w-auto flex flex-wrap items-center gap-2 md:justify-end">
            <!-- Filter Tahun Pelajaran -->
            <form action="{{ route('penilaian-ujian-alquran.index') }}" method="GET" id="formTahunPenilaian"
                class="m-0 relative group h-10">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahunPenilaian').submit()"
                    class="m3-input-glass w-full md:w-56 !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    @foreach ($daftarTahun as $tp)
                        <option value="{{ $tp->id }}"
                            {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }} {{ $tp->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>

            @if ($ujian)
                <a href="{{ route('penilaian-ujian-alquran.cetak-format', ['tahun_id' => $tahunPelajaranId, 'ruangan_id' => $selectedRuanganId]) }}"
                    target="_blank"
                    class="px-4 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-bold flex items-center gap-2 transition-all active:scale-95 cursor-pointer shadow-2xs border border-zinc-200/80 dark:border-zinc-700">
                    <i class="bi bi-printer-fill text-zinc-500"></i>
                    <span>Cetak Lembar Nilai Juri</span>
                </a>

                <a href="{{ route('rekap-ujian-alquran.index', ['tahun_id' => $tahunPelajaranId, 'ruangan_id' => $selectedRuanganId]) }}"
                    class="px-4 h-10 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all active:scale-95">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Lihat Rekapitulasi</span>
                </a>
            @endif
        </div>
    </div>


    @if (!$ujian)
        <div
            class="m3-glass-card p-8 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 text-center space-y-4 my-6">
            <div
                class="w-16 h-16 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto text-2xl">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3 class="text-lg font-black text-zinc-800 dark:text-zinc-200">Belum Ada Agenda Ujian Al-Qur'an di Tahun
                Pelajaran Ini</h3>
            <p class="text-xs text-zinc-500 max-w-md mx-auto">
                Silakan buat Master Agenda Ujian Al-Qur'an terlebih dahulu pada tahun pelajaran ini.
            </p>
            <div>
                <a href="{{ route('ujian-alquran.create', ['tahun_id' => $tahunPelajaranId]) }}"
                    class="m3-btn-primary px-5 py-2.5 rounded-2xl text-xs font-black inline-flex items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Buat Agenda Ujian Sekarang
                </a>
            </div>
        </div>
    @else
        <!-- STATISTIK RINGKASAN NILAI -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div
                class="m3-glass-card p-4 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Total Peserta</span>
                <h3 class="text-lg md:text-xl font-black text-zinc-900 dark:text-white mt-1">{{ $statistik->total }}
                    Murid</h3>
            </div>
            <div
                class="m3-glass-card p-4 rounded-3xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 shadow-2xs">
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Lulus
                    Ujian (Nilai &ge; {{ (int) $ujian->kkm_kelulusan }})</span>
                <h3 class="text-lg md:text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                    {{ $statistik->lulus }} Murid ({{ $statistik->persen_lulus }}%)
                </h3>
            </div>
            <div
                class="m3-glass-card p-4 rounded-3xl bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Tidak
                    Lulus (Nilai &lt; {{ (int) $ujian->kkm_kelulusan }})</span>
                <h3 class="text-lg md:text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    {{ $statistik->tidak_lulus }} Murid
                </h3>
            </div>
            <div
                class="m3-glass-card p-4 rounded-3xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Belum
                    Diuji / Belum Dinilai</span>
                <h3 class="text-lg md:text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                    {{ $statistik->belum_diuji }} Murid
                </h3>
            </div>
        </div>

        <!-- PANEL PENCARIAN & FILTER -->
        <div class="m3-glass-card p-3 md:p-3.5 mb-6 shadow-2xs relative z-10 print:hidden">
            <form action="{{ route('penilaian-ujian-alquran.index') }}" method="GET"
                class="w-full flex flex-col lg:flex-row gap-2.5 items-center">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- Filter Ruangan (Urutan Level 5 IBT duluan) -->
                <div class="relative w-full lg:w-48 h-10 shrink-0">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-door-open-fill text-sm"></i>
                    </div>
                    <select name="ruangan_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs md:text-sm font-semibold cursor-pointer appearance-none h-10">
                        <option value="">Semua Ruangan</option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}"
                                {{ (string) $selectedRuanganId === (string) $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }} ({{ $r->level->nama_level ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-black"></i>
                    </div>
                </div>

                <!-- Filter Status Kelulusan -->
                <div class="relative w-full lg:w-44 h-10 shrink-0">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-funnel-fill text-sm"></i>
                    </div>
                    <select name="status_kelulusan" onchange="this.form.submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs md:text-sm font-semibold cursor-pointer appearance-none h-10">
                        <option value="">Semua Status</option>
                        <option value="Belum Diuji"
                            {{ request('status_kelulusan') === 'Belum Diuji' ? 'selected' : '' }}>
                            Belum Diuji</option>
                        <option value="Lulus" {{ request('status_kelulusan') === 'Lulus' ? 'selected' : '' }}>Lulus
                        </option>
                        <option value="Tidak Lulus"
                            {{ request('status_kelulusan') === 'Tidak Lulus' ? 'selected' : '' }}>
                            Tidak Lulus</option>
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-black"></i>
                    </div>
                </div>

                <!-- Input Search Keyword (Spacious & Prominent) -->
                <div class="relative w-full flex-1 h-10 group/search min-w-[280px]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                        <i class="bi bi-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Ketik Nomor Peserta / NISM / Nama Murid..." autocomplete="off"
                        class="m3-input-glass w-full !pl-9.5 !pr-9 text-xs md:text-sm font-semibold h-10 placeholder:text-zinc-400">
                    @if (request('search'))
                        <a href="{{ route('penilaian-ujian-alquran.index', array_filter(['tahun_id' => $tahunPelajaranId, 'ruangan_id' => request('ruangan_id'), 'status_kelulusan' => request('status_kelulusan')])) }}"
                            class="absolute inset-y-0 right-0 w-9 h-full flex items-center justify-center text-zinc-400 hover:text-rose-500 transition-colors"
                            title="Hapus Pencarian">
                            <i class="bi bi-x-circle-fill text-sm"></i>
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="m3-btn-primary w-full lg:w-auto px-6 h-10 text-xs md:text-sm font-black shadow-2xs flex items-center justify-center gap-1.5 shrink-0">
                    <i class="bi bi-search text-xs"></i> <span>Cari</span>
                </button>
            </form>
        </div>

        <!-- PETUNJUK BOBOT PENILAIAN -->
        <div
            class="p-3 mb-6 rounded-2xl bg-zinc-100/80 dark:bg-zinc-900/80 border border-zinc-200/80 dark:border-zinc-800 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300 font-semibold">
                <i class="bi bi-info-circle-fill text-emerald-600 dark:text-emerald-400 text-sm"></i>
                <span>Rumus Nilai: <strong>100 - (Jali &times; {{ (float) $ujian->bobot_jali }}) - (Khofi &times;
                        {{ (float) $ujian->bobot_khofi }})</strong> | KKM Kelulusan: <strong>&ge;
                        {{ (int) $ujian->kkm_kelulusan }}</strong></span>
            </div>
            <div class="text-[11px] text-zinc-500 font-medium">
                * Nilai tersimpan langsung dan otomatis mengupdate status kelulusan.
            </div>
        </div>

        <!-- TABEL INPUT NILAI PESERTA (BATCH & ROW-LEVEL) -->
        <div x-data="inputNilaiTable({
            ujianId: {{ $ujian->id }},
            bobotJali: {{ (float) $ujian->bobot_jali }},
            bobotKhofi: {{ (float) $ujian->bobot_khofi }},
            kkm: {{ (float) $ujian->kkm_kelulusan }},
            csrf: '{{ csrf_token() }}',
            saveUrl: '{{ route('penilaian-ujian-alquran.store') }}'
        })"
            class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 p-5 shadow-2xs relative">

            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800 mb-4 gap-3">
                <div>
                    <h3
                        class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-table text-emerald-500"></i>
                        <span>Daftar Input Nilai Murid</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Menampilkan {{ $pesertas->total() }} murid terdaftar. Masukkan jumlah kesalahan Jali dan Khofi
                        di bawah ini.
                    </p>
                </div>

                <!-- Tombol Simpan Semua (Batch) -->
                <button @click="saveAll()" :disabled="isSavingAll"
                    class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 disabled:opacity-50 cursor-pointer">
                    <i class="bi"
                        :class="isSavingAll ? 'bi-arrow-repeat animate-spin' : 'bi-check-all text-base'"></i>
                    <span x-text="isSavingAll ? 'Menyimpan Semua...' : 'Simpan Semua Nilai Halaman Ini'"></span>
                </button>
            </div>

            <form id="formBatchNilai" @submit.prevent="saveAll()">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="border-b border-zinc-200/80 dark:border-zinc-800 text-[10px] uppercase font-black tracking-wider text-zinc-400 bg-zinc-50/50 dark:bg-zinc-800/20">
                                <th class="py-3 px-3 w-12">No</th>
                                <th class="py-3 px-3 w-28">No. Peserta</th>
                                <th class="py-3 px-3 min-w-[180px]">Nama Lengkap Murid</th>
                                <th class="py-3 px-3 w-24">Ruangan</th>
                                <th class="py-3 px-3 text-center w-28">
                                    <span class="text-rose-600 dark:text-rose-400 font-bold">Khotho' Jali</span>
                                    <span
                                        class="block text-[8px] text-zinc-400 font-normal">(-{{ (float) $ujian->bobot_jali }}
                                        pt/err)</span>
                                </th>
                                <th class="py-3 px-3 text-center w-28">
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">Khotho' Khofi</span>
                                    <span
                                        class="block text-[8px] text-zinc-400 font-normal">(-{{ (float) $ujian->bobot_khofi }}
                                        pt/err)</span>
                                </th>
                                <th class="py-3 px-3 min-w-[160px]">Catatan Juri (Opsional)</th>
                                <th class="py-3 px-3 text-center w-24">Nilai Akhir</th>
                                <th class="py-3 px-3 text-center w-28">Status</th>
                                <th class="py-3 px-3 text-right w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                            @forelse ($pesertas as $idx => $p)
                                <tr id="row-peserta-{{ $p->id }}" x-data="inputNilaiRow({
                                    id: {{ $p->id }},
                                    jali: {{ (int) $p->jumlah_khoto_jali }},
                                    khofi: {{ (int) $p->jumlah_khoto_khofi }},
                                    catatan: '{{ addslashes($p->catatan_juri ?? '') }}',
                                    statusInitial: '{{ $p->status_kelulusan }}',
                                    bobotJali: {{ (float) $ujian->bobot_jali }},
                                    bobotKhofi: {{ (float) $ujian->bobot_khofi }},
                                    kkm: {{ (float) $ujian->kkm_kelulusan }},
                                    saveUrl: '{{ route('penilaian-ujian-alquran.store') }}',
                                    csrf: '{{ csrf_token() }}'
                                })"
                                    class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors"
                                    :class="{ 'bg-emerald-500/5 dark:bg-emerald-500/10': isSavedJustNow }">

                                    <td class="py-3 px-3 text-zinc-400 font-mono text-[11px]">
                                        {{ $pesertas->firstItem() + $idx }}
                                    </td>

                                    <td class="py-3 px-3 font-mono font-bold text-zinc-800 dark:text-zinc-200 text-xs">
                                        {{ $p->nomor_peserta ?? '-' }}
                                    </td>

                                    <td class="py-3 px-3">
                                        <span
                                            class="font-bold text-zinc-900 dark:text-white uppercase block text-xs md:text-sm">
                                            {{ $p->murid->nama_lengkap ?? '-' }}
                                        </span>
                                        <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 font-mono">
                                            NISM: {{ $p->murid->nism ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="py-3 px-3">
                                        <span
                                            class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                            {{ $p->ruangan->nama_ruangan ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- INPUT KHOTHO JALI -->
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <input type="number" min="0" max="20"
                                                x-model.number="jali" @input="recalculate()"
                                                class="w-16 h-8 text-center rounded-xl bg-white dark:bg-zinc-800 border border-rose-200 dark:border-rose-900/50 font-bold font-mono text-rose-600 dark:text-rose-400 focus:ring-2 focus:ring-rose-500 focus:outline-none text-xs">
                                        </div>
                                    </td>

                                    <!-- INPUT KHOTHO KHOFI -->
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <input type="number" min="0" max="35"
                                                x-model.number="khofi" @input="recalculate()"
                                                class="w-16 h-8 text-center rounded-xl bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-900/50 font-bold font-mono text-amber-600 dark:text-amber-400 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs">
                                        </div>
                                    </td>

                                    <!-- INPUT CATATAN JURI -->
                                    <td class="py-3 px-3">
                                        <input type="text" x-model="catatan" placeholder="Catatan juri..."
                                            class="w-full h-8 px-2.5 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[11px] font-medium text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-primary focus:outline-none">
                                    </td>

                                    <!-- NILAI AKHIR (REALTIME PREVIEW) -->
                                    <td class="py-3 px-3 text-center font-mono font-extrabold text-sm"
                                        :class="nilaiAkhir >= kkm ? 'text-emerald-600 dark:text-emerald-400' :
                                            'text-rose-600 dark:text-rose-400'">
                                        <span x-text="nilaiAkhir"></span>
                                    </td>

                                    <!-- STATUS KELULUSAN BADGE -->
                                    <td class="py-3 px-3 text-center">
                                        <template x-if="status === 'Lulus'">
                                            <span
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                                <i class="bi bi-check-circle-fill text-xs"></i> Lulus
                                            </span>
                                        </template>
                                        <template x-if="status === 'Tidak Lulus'">
                                            <span
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 inline-flex items-center gap-1">
                                                <i class="bi bi-x-circle-fill text-xs"></i> Tidak Lulus
                                            </span>
                                        </template>
                                        <template x-if="status === 'Belum Diuji'">
                                            <span
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 inline-flex items-center gap-1">
                                                Belum Diuji
                                            </span>
                                        </template>
                                    </td>

                                    <!-- TOMBOL AKSI SIMPAN BARIS -->
                                    <td class="py-3 px-3 text-right">
                                        <button type="button" @click="saveRow()" :disabled="isSaving"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all active:scale-95 flex items-center gap-1 ml-auto cursor-pointer"
                                            :class="isSavedJustNow ? 'bg-emerald-600 text-white' :
                                                'bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-600 hover:text-white text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'"
                                            title="Simpan Nilai Murid Ini">
                                            <i class="bi"
                                                :class="isSaving ? 'bi-arrow-repeat animate-spin' : (isSavedJustNow ?
                                                    'bi-check-lg text-sm' : 'bi-floppy')"></i>
                                            <span
                                                x-text="isSaving ? '...' : (isSavedJustNow ? 'Tersimpan' : 'Simpan')"></span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-10 text-center text-zinc-400 italic">
                                        <i class="bi bi-info-circle text-2xl block mb-2 opacity-50"></i>
                                        Belum ada peserta yang sesuai dengan filter. Silakan tarik peserta di menu
                                        "Peserta Murid".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @if ($pesertas->hasPages())
                <div class="pt-4 border-t border-zinc-200/60 dark:border-zinc-800">
                    {{ $pesertas->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- JAVASCRIPT LOGIC DENGAN ALPINE.JS -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('inputNilaiTable', (config) => ({
                ujianId: config.ujianId,
                csrf: config.csrf,
                saveUrl: config.saveUrl,
                isSavingAll: false,

                async saveAll() {
                    this.isSavingAll = true;
                    try {
                        const rows = document.querySelectorAll('[id^="row-peserta-"]');
                        const nilaiData = {};

                        rows.forEach(row => {
                            const alpineData = Alpine.$data(row);
                            if (alpineData && alpineData.id) {
                                nilaiData[alpineData.id] = {
                                    jumlah_khoto_jali: alpineData.jali || 0,
                                    jumlah_khoto_khofi: alpineData.khofi || 0,
                                    catatan_juri: alpineData.catatan || ''
                                };
                            }
                        });

                        const response = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                nilai: nilaiData
                            })
                        });

                        const res = await response.json();
                        if (res.success) {
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'success',
                                    title: res.message
                                });
                            } else if (typeof window.showToast === 'function') {
                                window.showToast(res.message, 'success');
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                            // Flash all rows saved
                            rows.forEach(row => {
                                const alpineData = Alpine.$data(row);
                                if (alpineData) {
                                    alpineData.isSavedJustNow = true;
                                    setTimeout(() => {
                                        alpineData.isSavedJustNow = false;
                                    }, 3000);
                                }
                            });
                        } else {
                            const errMsg = res.message || 'Gagal menyimpan nilai.';
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'error',
                                    title: errMsg
                                });
                            } else {
                                Swal.fire('Error!', errMsg, 'error');
                            }
                        }
                    } catch (e) {
                        const errMsg = 'Terjadi kesalahan saat menyimpan nilai: ' + e.message;
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({
                                icon: 'error',
                                title: errMsg
                            });
                        } else {
                            Swal.fire('Error!', errMsg, 'error');
                        }
                    } finally {
                        this.isSavingAll = false;
                    }
                }
            }));

            Alpine.data('inputNilaiRow', (data) => ({
                id: data.id,
                jali: data.jali,
                khofi: data.khofi,
                catatan: data.catatan,
                status: data.statusInitial,
                bobotJali: data.bobotJali,
                bobotKhofi: data.bobotKhofi,
                kkm: data.kkm,
                saveUrl: data.saveUrl,
                csrf: data.csrf,
                nilaiAkhir: 100,
                isSaving: false,
                isSavedJustNow: false,

                init() {
                    this.recalculate();
                },

                recalculate() {
                    const j = Number(this.jali) || 0;
                    const k = Number(this.khofi) || 0;
                    const minus = (j * this.bobotJali) + (k * this.bobotKhofi);
                    this.nilaiAkhir = Math.max(0, 100 - minus);

                    if (j === 0 && k === 0 && this.status === 'Belum Diuji') {
                        this.status = 'Belum Diuji';
                    } else {
                        this.status = this.nilaiAkhir >= this.kkm ? 'Lulus' : 'Tidak Lulus';
                    }
                },

                async saveRow() {
                    this.isSaving = true;
                    this.recalculate();
                    try {
                        const response = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                peserta_id: this.id,
                                jumlah_khoto_jali: this.jali || 0,
                                jumlah_khoto_khofi: this.khofi || 0,
                                catatan_juri: this.catatan || ''
                            })
                        });

                        const res = await response.json();
                        if (res.success) {
                            this.status = res.data.status_kelulusan;
                            this.nilaiAkhir = Math.round(res.data.nilai_akhir);
                            this.isSavedJustNow = true;
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'success',
                                    title: res.message
                                });
                            } else if (typeof window.showToast === 'function') {
                                window.showToast(res.message, 'success');
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Tersimpan!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                            setTimeout(() => {
                                this.isSavedJustNow = false;
                            }, 3000);
                        } else {
                            const errMsg = res.message || 'Gagal menyimpan nilai.';
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'error',
                                    title: errMsg
                                });
                            } else {
                                Swal.fire('Error!', errMsg, 'error');
                            }
                        }
                    } catch (e) {
                        const errMsg = 'Gagal menyimpan nilai: ' + e.message;
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({
                                icon: 'error',
                                title: errMsg
                            });
                        } else {
                            Swal.fire('Error!', errMsg, 'error');
                        }
                    } finally {
                        this.isSaving = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
