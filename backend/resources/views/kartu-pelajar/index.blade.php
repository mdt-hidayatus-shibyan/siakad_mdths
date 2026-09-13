@section('title', 'Kartu Pelajar')

<x-app-layout>
    <!-- HEADER & TOOLBAR SEJAJAR -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <!-- Sisi Kiri: Judul Halaman -->
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Cetak Kartu Pelajar
            </h2>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 uppercase tracking-wider">
                Generate ID Card murid berstandar CR80 dengan QR Code terverifikasi
            </p>
        </div>

        <!-- Sisi Kanan: Toolbar Filter -->
        <div class="w-full xl:w-auto shrink-0">
            <form action="{{ request()->url() }}" method="GET" id="formFilter"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto m3-glass-card p-1.5 shadow-2xs">

                <div
                    class="hidden sm:flex items-center justify-center w-10 h-10 shrink-0 bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark rounded-xl border border-primary/20 ml-0.5">
                    <i class="bi bi-funnel-fill text-sm"></i>
                </div>

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-56 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-range text-xs"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-56 group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($daftarRuangan as $ruangItem)
                            <option value="{{ $ruangItem->id }}"
                                {{ request('ruangan_id') == $ruangItem->id ? 'selected' : '' }}>
                                {{ $ruangItem->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- AREA KONTEN -->
    @if ($ruanganTerpilih && $murids->count() > 0)

        <form action="{{ route('kartu-pelajar.cetak') }}" method="POST" target="_blank" id="data-table-container"
            class="m3-glass-card overflow-hidden relative z-10 flex flex-col">
            @csrf
            <input type="hidden" name="ruangan_id_cetak" value="{{ $ruanganTerpilih->id }}">

            <!-- Tabel Header & Bulk Action -->
            <div
                class="bg-zinc-50/70 dark:bg-zinc-950/50 px-5 py-3.5 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" id="checkAll" class="peer sr-only">
                        <div
                            class="w-4.5 h-4.5 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 peer-checked:bg-primary dark:peer-checked:bg-primary-dark peer-checked:border-primary transition-all flex items-center justify-center shadow-2xs">
                            <i
                                class="bi bi-check text-white dark:text-zinc-900 opacity-0 peer-checked:opacity-100 text-sm font-black leading-none"></i>
                        </div>
                    </label>
                    <span class="text-xs font-black text-zinc-700 dark:text-zinc-300">Pilih Semua Murid</span>
                </div>

                <button type="submit" id="btnCetak" disabled
                    class="m3-btn-primary h-10 px-5 text-xs group/btn disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="bi bi-printer-fill text-xs mr-1"></i>
                    <span>Cetak Terpilih (<span id="countSelected">0</span>)</span>
                </button>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto custom-scrollbar p-0">
                <table class="m3-table text-xs min-w-[650px]">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-3 text-center w-14">No</th>
                            <th class="py-3 px-4 w-32 text-center">NISM</th>
                            <th class="py-3 px-5">Nama Lengkap & Foto Murid</th>
                            <th class="py-3 px-4 text-center w-40">Status Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($murids as $index => $murid)
                            @php
                                $defaultFoto =
                                    $murid->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png';
                                $fotoPath = $murid->foto ? $murid->foto : $defaultFoto;
                            @endphp
                            <tr class="group/tr">
                                <td class="py-2.5 px-4 text-center align-middle">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="murid_ids[]" value="{{ $murid->id }}"
                                            class="check-item peer sr-only">
                                        <div
                                            class="w-4.5 h-4.5 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 peer-checked:bg-primary dark:peer-checked:bg-primary-dark peer-checked:border-primary transition-all flex items-center justify-center shadow-2xs">
                                            <i
                                                class="bi bi-check text-white dark:text-zinc-900 opacity-0 peer-checked:opacity-100 text-sm font-black leading-none"></i>
                                        </div>
                                    </label>
                                </td>
                                <td class="py-2.5 px-3 text-center align-middle">
                                    <span
                                        class="w-6 h-6 mx-auto flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-md text-[11px] font-black">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td
                                    class="py-2.5 px-4 text-center font-mono font-bold text-zinc-600 dark:text-zinc-400 text-xs align-middle">
                                    {{ $murid->nism ?? '-' }}
                                </td>
                                <td class="py-2.5 px-5 align-middle">
                                    <div class="flex items-center gap-3">
                                        @can('update murid')
                                            <a href="{{ route('kartu-pelajar.uploadFoto', [$ruanganTerpilih->id, $murid->id]) }}"
                                                class="action-modal w-10 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-sm relative cursor-pointer group/avatar block"
                                                title="Ubah Foto (3x4 cm) {{ $murid->nama_panggilan ?: $murid->nama_lengkap }}">
                                                <img src="{{ asset('storage/' . $fotoPath) }}"
                                                    alt="Foto {{ $murid->nama_panggilan ?: $murid->nama_lengkap }}"
                                                    class="w-full h-full object-cover rounded-lg">
                                                <!-- Overlay Ubah Foto -->
                                                <div
                                                    class="absolute inset-0.5 rounded-lg bg-black/60 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-200">
                                                    <i class="bi bi-camera-fill text-white text-sm"></i>
                                                </div>
                                            </a>
                                        @else
                                            <div
                                                class="w-10 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-sm relative">
                                                <img src="{{ asset('storage/' . $fotoPath) }}"
                                                    alt="Foto {{ $murid->nama_panggilan ?: $murid->nama_lengkap }}"
                                                    class="w-full h-full object-cover rounded-lg">
                                            </div>
                                        @endcan
                                        <div>
                                            <div
                                                class="font-black text-zinc-900 dark:text-white text-xs tracking-tight">
                                                {{ $murid->nama_lengkap }}
                                            </div>
                                            <div
                                                class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider flex items-center gap-1.5 mt-0.5">
                                                <span>{{ $murid->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                                @if ($murid->nama_panggilan)
                                                    <span>&bull;</span>
                                                    <span>({{ $murid->nama_panggilan }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-4 text-center align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        @if ($murid->foto)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                <i class="bi bi-image mr-1"></i> Ada Foto
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                <i class="bi bi-x-circle mr-1"></i> Kosong
                                            </span>
                                        @endif

                                        @can('update murid')
                                            <a href="{{ route('kartu-pelajar.uploadFoto', [$ruanganTerpilih->id, $murid->id]) }}"
                                                class="action-modal inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-primary/10 hover:bg-primary/20 text-primary dark:text-primary-dark border border-primary/20 transition-all hover:scale-105 active:scale-95"
                                                title="Upload & Potong Pasfoto (3x4 cm)">
                                                <i class="bi bi-camera-fill text-[11px]"></i>
                                                <span>{{ $murid->foto ? 'Ubah' : 'Upload' }}</span>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    @elseif($ruanganTerpilih && $murids->count() === 0)
        <!-- Kelas Kosong -->
        <div class="py-16 text-center m3-glass-card relative z-10">
            <div
                class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 rounded-2xl flex items-center justify-center text-zinc-400 dark:text-zinc-500 text-2xl mb-3 mx-auto shadow-2xs">
                <i class="bi bi-people-fill"></i>
            </div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Kelas Masih Kosong</h3>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">Belum ada data murid yang
                terdaftar di kelas ini.</p>
        </div>
    @else
        <!-- State Awal -->
        <div class="py-16 text-center m3-glass-card relative z-10">
            <div
                class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 rounded-2xl flex items-center justify-center text-zinc-400 dark:text-zinc-500 text-2xl mb-3 mx-auto shadow-2xs">
                <i class="bi bi-person-badge"></i>
            </div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">Pilih Ruangan Kelas
            </h3>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                Tentukan Tahun Pelajaran dan Ruangan Kelas pada filter di atas untuk menampilkan daftar murid dan
                mencetak Kartu Pelajar.
            </p>
        </div>
    @endif

    <script>
        (function() {
            function bindCheckboxEvents() {
                const checkAll = document.getElementById('checkAll');
                const checkItems = document.querySelectorAll('.check-item');
                const btnCetak = document.getElementById('btnCetak');
                const countSelected = document.getElementById('countSelected');

                function updateBtnStatus() {
                    const checkedCount = document.querySelectorAll('.check-item:checked').length;
                    if (countSelected) countSelected.innerText = checkedCount;
                    if (btnCetak) btnCetak.disabled = checkedCount === 0;

                    if (checkAll) {
                        checkAll.checked = checkedCount === checkItems.length && checkItems.length > 0;
                    }
                }

                if (checkAll) {
                    checkAll.onchange = function() {
                        document.querySelectorAll('.check-item').forEach(item => {
                            item.checked = checkAll.checked;
                        });
                        updateBtnStatus();
                    };
                }

                document.querySelectorAll('.check-item').forEach(item => {
                    item.onchange = updateBtnStatus;
                });
            }

            document.addEventListener('DOMContentLoaded', bindCheckboxEvents);
            $(document).on('dataGridRefreshed', bindCheckboxEvents);
        })();
    </script>
</x-app-layout>
