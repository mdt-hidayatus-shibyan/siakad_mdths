@section('title', 'Rekapitulasi Ujian Al-Qur\'an' . ($ujian ? ' - ' . $ujian->nama_ujian : ''))

<x-app-layout>
    <!-- HEADER TOOLBAR -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 print:hidden">
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="px-2.5 py-1 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 text-[11px] font-black uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Rekapitulasi Nilai</span>
                </span>
                <span class="text-xs text-zinc-400 font-mono">
                    {{ $ujian->tahunPelajaran->nama_hijriyah ?? ($daftarTahun->firstWhere('id', $tahunPelajaranId)?->nama_hijriyah ?? '') }}
                </span>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Rekap Nilai: {{ $ujian->nama_ujian ?? 'Ujian Al-Qur\'an' }}
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Leger hasil ujian, status kelulusan murid, dan penerbitan dokumen resmi (Ijazah / SK Kelulusan).
            </p>
        </div>

        <!-- Filter Tahun Pelajaran & Tombol Cetak Berita Acara / Rekap -->
        <div class="w-full md:w-auto flex flex-wrap items-center gap-2 md:justify-end">
            <!-- Filter Tahun Pelajaran -->
            <form action="{{ route('rekap-ujian-alquran.index') }}" method="GET" id="formTahunRekap"
                class="m-0 relative group h-10">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahunRekap').submit()"
                    class="m3-input-glass w-full md:w-56 !pl-9 !pr-8 text-xs md:text-sm font-semibold cursor-pointer appearance-none h-10">
                    @foreach ($daftarTahun as $tp)
                        <option value="{{ $tp->id }}"
                            {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }} {{ $tp->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-black"></i>
                </div>
            </form>

            @if ($ujian)
                <a href="{{ route('rekap-ujian-alquran.cetak-rekap', array_filter(['tahun_id' => $tahunPelajaranId, 'ruangan_id' => request('ruangan_id'), 'status_kelulusan' => request('status_kelulusan'), 'search' => request('search')])) }}"
                    target="_blank"
                    class="px-4 md:px-5 h-10 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-xs md:text-sm font-black flex items-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <i class="bi bi-printer-fill text-sm"></i>
                    <span>Cetak Rekap & Berita Acara (A4)</span>
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
        <!-- STATISTIK RINGKASAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4 mb-6">
            <div
                class="m3-glass-card p-4 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Total Murid Peserta</span>
                <h3 class="text-lg md:text-xl font-black text-zinc-900 dark:text-white mt-1">{{ $statistik->total }}
                    Murid</h3>
                <p class="text-[10px] text-zinc-400 mt-0.5">5 IBT & 6 IBT Mengulang</p>
            </div>

            <div
                class="m3-glass-card p-4 rounded-3xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 shadow-2xs">
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Lulus
                    Ujian</span>
                <h3 class="text-lg md:text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                    {{ $statistik->lulus }} Murid
                </h3>
                <p class="text-[10px] text-emerald-600/80 font-semibold mt-0.5">{{ $statistik->persen_lulus }}% Tingkat
                    Kelulusan (Nilai &ge; {{ (int) $ujian->kkm_kelulusan }})</p>
            </div>

            <div
                class="m3-glass-card p-4 rounded-3xl bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Tidak
                    Lulus / Remidi</span>
                <h3 class="text-lg md:text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    {{ $statistik->tidak_lulus }} Murid
                </h3>
                <p class="text-[10px] text-rose-600/80 font-semibold mt-0.5">Mengulang Tahun Depan di Kls 6</p>
            </div>

            <div
                class="m3-glass-card p-4 rounded-3xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 shadow-2xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Belum
                    Diuji</span>
                <h3 class="text-lg md:text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                    {{ $statistik->belum_diuji }} Murid
                </h3>
                <p class="text-[10px] text-amber-600/80 font-semibold mt-0.5">Menunggu Evaluasi Juri</p>
            </div>
        </div>

        <!-- PANEL FILTER & PENCARIAN -->
        <div class="m3-glass-card p-3 md:p-3.5 mb-6 shadow-2xs relative z-10 print:hidden">
            <form method="GET" action="{{ route('rekap-ujian-alquran.index') }}"
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
                                {{ (string) request('ruangan_id') === (string) $r->id ? 'selected' : '' }}>
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

                <!-- Keyword Search (Spacious & Prominent) -->
                <div class="relative w-full flex-1 h-10 group/search min-w-[280px]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                        <i class="bi bi-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Ketik Nomor Peserta / NISM / Nama Murid..." autocomplete="off"
                        class="m3-input-glass w-full !pl-9.5 !pr-9 text-xs md:text-sm font-semibold h-10 placeholder:text-zinc-400">
                    @if (request('search'))
                        <a href="{{ route('rekap-ujian-alquran.index', array_filter(['tahun_id' => $tahunPelajaranId, 'ruangan_id' => request('ruangan_id'), 'status_kelulusan' => request('status_kelulusan')])) }}"
                            class="absolute inset-y-0 right-0 w-9 h-full flex items-center justify-center text-zinc-400 hover:text-rose-500 transition-colors"
                            title="Hapus Pencarian">
                            <i class="bi bi-x-circle-fill text-sm"></i>
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="m3-btn-primary w-full lg:w-auto px-5 h-10 text-xs md:text-sm font-black shadow-2xs flex items-center justify-center gap-1.5 shrink-0">
                    <i class="bi bi-search"></i> <span>Cari</span>
                </button>
            </form>
        </div>

        <!-- TABEL HASIL REKAPITULASI -->
        <div
            class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 p-5 shadow-2xs">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800 mb-4">
                <div>
                    <h3
                        class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="bi bi-journal-check text-emerald-500"></i>
                        <span>Tabel Rekapitulasi Nilai & Dokumen Kelulusan</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Urutan nomor peserta murid berdasarkan hasil evaluasi Dewan Juri.
                    </p>
                </div>
                <span
                    class="px-3 py-1 rounded-xl text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    {{ $pesertas->total() }} Murid Ditampilkan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="border-b border-zinc-200/80 dark:border-zinc-800 text-[10px] uppercase font-black tracking-wider text-zinc-400 bg-zinc-50/50 dark:bg-zinc-800/20">
                            <th class="py-3 px-3 w-12">No</th>
                            <th class="py-3 px-3 w-28">No. Peserta</th>
                            <th class="py-3 px-3 min-w-[180px]">Nama Lengkap Murid</th>
                            <th class="py-3 px-3 w-24">Ruangan</th>
                            <th class="py-3 px-3 text-center w-20">
                                <span class="text-rose-600 dark:text-rose-400 font-bold">Jali</span>
                                <span
                                    class="block text-[8px] text-zinc-400 font-normal">(-{{ (int) $ujian->bobot_jali }}
                                    pt)</span>
                            </th>
                            <th class="py-3 px-3 text-center w-20">
                                <span class="text-amber-600 dark:text-amber-400 font-bold">Khofi</span>
                                <span
                                    class="block text-[8px] text-zinc-400 font-normal">(-{{ (int) $ujian->bobot_khofi }}
                                    pt)</span>
                            </th>
                            <th class="py-3 px-3 text-center w-20">Minus</th>
                            <th class="py-3 px-3 text-center w-24">Nilai Akhir</th>
                            <th class="py-3 px-3 text-center w-24">Predikat</th>
                            <th class="py-3 px-3 text-center w-28">Status</th>
                            <th class="py-3 px-3 text-right min-w-[150px]">Cetak Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                        @forelse ($pesertas as $idx => $p)
                            <tr
                                class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors {{ $p->status_kelulusan === 'Lulus' ? '' : ($p->status_kelulusan === 'Tidak Lulus' ? 'bg-rose-50/30 dark:bg-rose-950/10' : '') }}">
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
                                <td class="py-3 px-3 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                                    {{ $p->jumlah_khoto_jali }}
                                </td>
                                <td
                                    class="py-3 px-3 text-center font-mono font-bold text-amber-600 dark:text-amber-400">
                                    {{ $p->jumlah_khoto_khofi }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-rose-700 dark:text-rose-400">
                                    -{{ $p->total_pengurangan }}
                                </td>
                                <td
                                    class="py-3 px-3 text-center font-mono font-black text-sm {{ $p->status_kelulusan === 'Lulus' ? 'text-emerald-600 dark:text-emerald-400' : ($p->status_kelulusan === 'Tidak Lulus' ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-500') }}">
                                    {{ number_format($p->nilai_akhir, 0) }}
                                </td>
                                <td
                                    class="py-3 px-3 text-center text-[10px] font-bold text-zinc-700 dark:text-zinc-300">
                                    @if ($p->status_kelulusan === 'Lulus')
                                        <span class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800">
                                            {{ $p->predikat }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($p->status_kelulusan === 'Lulus')
                                        <span
                                            class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                            <i class="bi bi-check-circle-fill text-xs"></i> Lulus
                                        </span>
                                    @elseif ($p->status_kelulusan === 'Tidak Lulus')
                                        <span
                                            class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 inline-flex items-center gap-1">
                                            <i class="bi bi-x-circle-fill text-xs"></i> Tidak Lulus
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 inline-flex items-center gap-1">
                                            Belum Diuji
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($p->status_kelulusan === 'Lulus')
                                            <!-- Cetak Ijazah -->
                                            <a href="{{ route('rekap-ujian-alquran.cetak-ijazah', $p->id) }}"
                                                target="_blank"
                                                class="px-2.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95 cursor-pointer"
                                                title="Cetak Ijazah Kelulusan Al-Qur'an">
                                                <i class="bi bi-award-fill"></i>
                                                <span>Ijazah</span>
                                            </a>

                                            <!-- Cetak SK -->
                                            <a href="{{ route('rekap-ujian-alquran.cetak-sk', $p->id) }}"
                                                target="_blank"
                                                class="px-2.5 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white font-bold text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95 cursor-pointer"
                                                title="Cetak Surat Keputusan Penetapan Kelulusan">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                                <span>SK</span>
                                            </a>
                                        @elseif ($p->status_kelulusan === 'Tidak Lulus')
                                            <!-- Cetak SK Penetapan Hasil -->
                                            <a href="{{ route('rekap-ujian-alquran.cetak-sk', $p->id) }}"
                                                target="_blank"
                                                class="px-2.5 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white font-bold text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95 cursor-pointer"
                                                title="Cetak Surat Keputusan Hasil Ujian (Mengulang)">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                                <span>SK</span>
                                            </a>
                                        @else
                                            <!-- Link Input Nilai -->
                                            <a href="{{ route('penilaian-ujian-alquran.index', ['tahun_id' => $tahunPelajaranId, 'ruangan_id' => $p->ruangan_id, 'search' => $p->nomor_peserta]) }}"
                                                class="px-2.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs flex items-center gap-1 shadow-2xs transition-all active:scale-95"
                                                title="Input Nilai Murid Ini">
                                                <i class="bi bi-pencil-square"></i>
                                                <span>Nilai</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-zinc-400 italic">
                                    Belum ada data rekapitulasi ujian yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pesertas->hasPages())
                <div class="pt-4 border-t border-zinc-200/60 dark:border-zinc-800">
                    {{ $pesertas->links() }}
                </div>
            @endif
        </div>
    @endif
</x-app-layout>
