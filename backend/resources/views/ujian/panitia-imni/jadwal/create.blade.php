@section('title', 'Kertas Kerja Jadwal Ujian IMNI')

<x-app-layout>
    <!-- HEADER -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('jadwal-imni.index', ['tahun_id' => $tahun_pelajaran_id]) }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    Kertas Kerja Jadwal Ujian IMNI
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Atur sesi, waktu, mata pelajaran, dan pengawas ujian IMNI per tingkatan kelas akhir (3 TPQ, 6 IBT, 3 TSA).
                </p>
            </div>
        </div>

        <div>
            <a href="{{ route('jadwal-imni.modal-agenda', ['tahun_id' => $tahun_pelajaran_id, 'level_id' => $level_id]) }}"
                class="action-modal h-10 inline-flex items-center justify-center px-4 rounded-xl bg-zinc-100/80 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200/80 dark:hover:bg-zinc-800 text-xs font-black transition-all hover:scale-[1.02] active:scale-95 outline-none border border-zinc-200/80 dark:border-zinc-800 shadow-2xs gap-1.5 cursor-pointer">
                <i class="bi bi-calendar2-range text-sm text-primary"></i>
                <span>Pengaturan Tanggal Ujian</span>
            </a>
        </div>
    </div>

    <!-- FILTER AREA -->
    <div class="m3-glass-card p-4 sm:p-5 mb-6 relative z-10">
        <form action="{{ route('jadwal-imni.create') }}" method="GET" id="filterForm"
            class="grid grid-cols-1 md:grid-cols-2 gap-3.5">

            <!-- Filter Tahun Pelajaran -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Tahun Pelajaran
                </label>
                <div class="relative group/select">
                    <select name="tahun_pelajaran_id" onchange="document.getElementById('filterForm').submit();"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        <option value="">-- Pilih Tahun Pelajaran --</option>
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $tahun_pelajaran_id == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }} {{ $tp->is_active ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Level / Kelas Akhir -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Level / Kelas Akhir IMNI
                </label>
                <div class="relative group/select">
                    <select name="level_id" onchange="document.getElementById('filterForm').submit();"
                        {{ !$tahun_pelajaran_id ? 'disabled' : '' }}
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="">-- Pilih Kelas Akhir --</option>
                        @foreach ($levels as $lvl)
                            <option value="{{ $lvl->id }}" {{ $level_id == $lvl->id ? 'selected' : '' }}>
                                Kelas {{ $lvl->nama_level }} ({{ $lvl->tingkat?->nama_tingkat }})
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

    <!-- AREA LEGER / SPREADSHEET -->
    @if ($tahun_pelajaran_id && $level_id && count($dates) > 0)
        <form action="{{ route('jadwal-imni.store') }}" method="POST" id="formLeger"
            class="m3-glass-card overflow-hidden relative z-10">
            @csrf
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahun_pelajaran_id }}">
            <input type="hidden" name="level_id" value="{{ $level_id }}">

            <!-- Header Tabel -->
            <div
                class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex justify-between items-center">
                <h3 class="font-black text-zinc-900 dark:text-white text-sm md:text-base flex items-center gap-2.5">
                    <div
                        class="w-7 h-7 rounded-lg bg-primary/10 text-primary dark:bg-primary-dark/20 dark:text-primary-dark border border-primary/20 dark:border-primary-dark/30 flex items-center justify-center shadow-2xs shrink-0">
                        <i class="bi bi-table text-xs"></i>
                    </div>
                    Kertas Kerja Jadwal IMNI ({{ count($dates) }} Hari Ujian)
                </h3>

                <button type="submit" class="m3-btn-primary px-5 py-2 group/btn cursor-pointer">
                    <i class="bi bi-cloud-arrow-up-fill text-sm"></i>
                    <span>Simpan Massal</span>
                </button>
            </div>

            <!-- Area Grid -->
            <div class="overflow-x-auto custom-scrollbar">
                <table class="m3-table w-full text-left whitespace-nowrap min-w-[900px]">
                    <thead>
                        <tr>
                            <th scope="col"
                                class="border-r border-zinc-200/80 dark:border-zinc-800 w-48 text-center">Hari / Tanggal
                            </th>
                            <th scope="col"
                                class="border-r border-zinc-200/80 dark:border-zinc-800 w-16 text-center">Sesi</th>
                            <th scope="col" class="border-r border-zinc-200/80 dark:border-zinc-800 w-[220px]">Waktu
                                (Mulai - Selesai)</th>
                            <th scope="col" class="border-r border-zinc-200/80 dark:border-zinc-800 min-w-[250px]">
                                Mata Pelajaran</th>
                            <th scope="col" class="w-48">Pengawas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dates as $dateIndex => $date)
                            @for ($sesi = 1; $sesi <= 2; $sesi++)
                                @php
                                    $jadwal =
                                        isset($existingJadwal[$date]) && isset($existingJadwal[$date][$sesi - 1])
                                            ? $existingJadwal[$date][$sesi - 1]
                                            : null;

                                    $defaultMulai = '';
                                    $defaultSelesai = '';

                                    if ($sesi == 1) {
                                        $defaultMulai = '13:45';
                                        $defaultSelesai = '14:30';
                                    } elseif ($sesi == 2) {
                                        $defaultMulai = '15:15';
                                        $defaultSelesai = '16:00';
                                    }

                                    $valMulai =
                                        $jadwal && $jadwal->waktu_mulai
                                            ? \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i')
                                            : $defaultMulai;

                                    $valSelesai =
                                        $jadwal && $jadwal->waktu_selesai
                                            ? \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i')
                                            : $defaultSelesai;

                                    $isSesiAktif = $sesi == 1 || $jadwal != null;
                                    $disabledAttr = $isSesiAktif ? '' : 'disabled';
                                    $rowId = "row-{$dateIndex}-{$sesi}";
                                @endphp

                                <tr id="{{ $rowId }}"
                                    class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors {{ !$isSesiAktif ? 'opacity-50 bg-zinc-50/30 dark:bg-black/30' : '' }}">

                                    @if ($sesi == 1)
                                        <td rowspan="2"
                                            class="py-3 px-5 border-r border-b border-zinc-200/80 dark:border-zinc-800 align-top bg-zinc-50/50 dark:bg-black/20 text-center">
                                            <div
                                                class="font-black text-xs text-zinc-900 dark:text-white tracking-tight leading-tight mb-1">
                                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}
                                            </div>
                                            <div
                                                class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 tracking-wider uppercase">
                                                {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                                            </div>
                                        </td>
                                    @endif

                                    <!-- KOLOM SESI & CHECKBOX -->
                                    <td
                                        class="py-2.5 px-3 border-r border-zinc-200/80 dark:border-zinc-800 text-center font-black text-zinc-400 dark:text-zinc-500 text-xs">
                                        <div class="flex flex-col items-center justify-center gap-1">
                                            <span
                                                class="w-6 h-6 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-black">{{ $sesi }}</span>
                                            @if ($sesi == 2)
                                                <input type="checkbox"
                                                    class="toggle-sesi w-4 h-4 rounded-md border-zinc-300 dark:border-zinc-600 text-primary focus:ring-primary cursor-pointer"
                                                    data-row="{{ $rowId }}" title="Aktifkan Sesi 2"
                                                    {{ $isSesiAktif ? 'checked' : '' }}>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- KOLOM WAKTU -->
                                    <td class="py-2.5 px-4 border-r border-zinc-200/80 dark:border-zinc-800">
                                        <div class="flex items-center gap-1.5">
                                            <input type="time"
                                                name="jadwal[{{ $date }}][{{ $sesi }}][waktu_mulai]"
                                                value="{{ $valMulai }}" {{ $disabledAttr }}
                                                class="input-target m3-input-glass w-full !h-8.5 !px-2 text-xs font-bold text-center disabled:cursor-not-allowed disabled:bg-zinc-100/50 dark:disabled:bg-zinc-900/50">

                                            <span class="font-black text-zinc-400">-</span>

                                            <input type="time"
                                                name="jadwal[{{ $date }}][{{ $sesi }}][waktu_selesai]"
                                                value="{{ $valSelesai }}" {{ $disabledAttr }}
                                                class="input-target m3-input-glass w-full !h-8.5 !px-2 text-xs font-bold text-center disabled:cursor-not-allowed disabled:bg-zinc-100/50 dark:disabled:bg-zinc-900/50">
                                        </div>
                                    </td>

                                    <!-- KOLOM MATA PELAJARAN -->
                                    <td class="py-2.5 px-4 border-r border-zinc-200/80 dark:border-zinc-800">
                                        <div class="flex flex-col gap-1.5">
                                            <select
                                                name="jadwal[{{ $date }}][{{ $sesi }}][mata_pelajaran_id]"
                                                {{ $disabledAttr }}
                                                class="input-target m3-input-glass w-full !h-8.5 !px-2 text-xs font-bold disabled:cursor-not-allowed disabled:bg-zinc-100/50 dark:disabled:bg-zinc-900/50">
                                                <option value="">- Pilih Mapel Database -</option>
                                                @foreach ($mapels as $m)
                                                    <option value="{{ $m->id }}"
                                                        {{ ($jadwal->mata_pelajaran_id ?? '') == $m->id ? 'selected' : '' }}>
                                                        {{ $m->nama_mapel }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input type="text"
                                                name="jadwal[{{ $date }}][{{ $sesi }}][nama_mata_pelajaran_custom]"
                                                value="{{ $jadwal->nama_mata_pelajaran_custom ?? '' }}"
                                                placeholder="Atau ketik mapel custom..." {{ $disabledAttr }}
                                                class="input-target m3-input-glass w-full !h-8.5 !px-2.5 text-xs font-bold placeholder-zinc-400 disabled:cursor-not-allowed disabled:bg-zinc-100/50 dark:disabled:bg-zinc-900/50">
                                        </div>
                                    </td>

                                    <!-- KOLOM PENGAWAS -->
                                    <td class="py-2.5 px-4">
                                        <select name="jadwal[{{ $date }}][{{ $sesi }}][ustadz_id]"
                                            {{ $disabledAttr }}
                                            class="input-target m3-input-glass w-full !h-8.5 !px-2 text-xs font-bold disabled:cursor-not-allowed disabled:bg-zinc-100/50 dark:disabled:bg-zinc-900/50">
                                            <option value="">- Pilih Pengawas -</option>
                                            @foreach ($pengawas as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ ($jadwal->ustadz_id ?? '') == $p->id ? 'selected' : '' }}>
                                                    {{ $p->nama_lengkap }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                </tr>
                            @endfor
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Script Toggle Sesi -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toggles = document.querySelectorAll('.toggle-sesi');

                    toggles.forEach(toggle => {
                        toggle.addEventListener('change', function() {
                            const targetRowId = this.getAttribute('data-row');
                            const targetRow = document.getElementById(targetRowId);

                            if (targetRow) {
                                const inputs = targetRow.querySelectorAll('.input-target');

                                inputs.forEach(input => {
                                    input.disabled = !this.checked;
                                });

                                if (this.checked) {
                                    targetRow.classList.remove('opacity-50', 'bg-zinc-50/30',
                                        'dark:bg-black/30');
                                } else {
                                    targetRow.classList.add('opacity-50', 'bg-zinc-50/30',
                                        'dark:bg-black/30');
                                }
                            }
                        });
                    });
                });
            </script>

            <div
                class="p-3.5 bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 text-xs font-semibold text-zinc-500 dark:text-zinc-400 flex items-center justify-center gap-2">
                <i class="bi bi-info-circle-fill text-primary"></i> Kosongkan jam dan mapel jika sesi tersebut tidak ada ujian.
            </div>
        </form>
    @elseif($tahun_pelajaran_id && count($dates) == 0 && $level_id)
        <!-- Data Ujian Rentang Tanggal Belum Ada -->
        <div class="m3-glass-card py-12 text-center border-rose-200/80 dark:border-rose-900/50">
            <div
                class="w-12 h-12 bg-rose-50 dark:bg-rose-950/40 text-rose-500 border border-rose-200/60 dark:border-rose-800/40 rounded-xl flex items-center justify-center text-xl mb-3 mx-auto shadow-2xs">
                <i class="bi bi-calendar2-range"></i>
            </div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-1">
                Rentang Tanggal Pelaksanaan Belum Ditetapkan
            </h3>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto mb-4">
                Atur tanggal mulai dan tanggal selesai ujian IMNI agar sistem dapat meng-generate hari pelaksanaan ujian secara otomatis.
            </p>
            <a href="{{ route('jadwal-imni.modal-agenda', ['tahun_id' => $tahun_pelajaran_id]) }}"
                class="action-modal px-4 py-2 rounded-2xl bg-primary text-white text-xs font-black inline-flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                <i class="bi bi-calendar2-plus-fill"></i>
                <span>Atur Agenda & Tanggal Ujian IMNI</span>
            </a>
        </div>
    @else
        <!-- State Awal -->
        <x-empty-state icon="bi-table" title="Kertas Kerja Belum Terbuka"
            message="Silakan pilih Tahun Pelajaran dan Level / Kelas Akhir di atas untuk mulai mengisi lembar jadwal ujian IMNI." />
    @endif

</x-app-layout>
