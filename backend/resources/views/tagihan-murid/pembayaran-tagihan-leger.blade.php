@section('title', 'Pembayaran Leger')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-20 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('tagihan-murid.menu-pembayaran')
        </div>

        <!-- Filter Ruangan & Tagihan -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET" id="formFilterLeger"
                class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto m3-glass-card p-2 shadow-2xs">

                <!-- 1. Filter Ruangan -->
                <div class="relative w-full sm:w-[220px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-door-open-fill text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formFilterLeger').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value="" class="text-zinc-500">-- Pilih Ruangan --</option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}"
                                {{ isset($ruanganTerpilih) && $ruanganTerpilih->id == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-5 bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>

                <!-- 2. Filter Tagihan -->
                <div class="relative w-full sm:w-[240px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-tags-fill text-xs"></i>
                    </div>
                    <select name="pengaturan_tagihan_id" {{ !$ruanganTerpilih ? 'disabled' : '' }}
                        onchange="document.getElementById('formFilterLeger').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none disabled:opacity-50 disabled:cursor-not-allowed">
                        @if (!$ruanganTerpilih)
                            <option value="">-- Pilih Ruangan Dulu --</option>
                        @else
                            <option value="">-- Pilih Jenis Tagihan --</option>
                            @foreach ($masterBiayas as $biaya)
                                <option value="{{ $biaya->id }}"
                                    {{ isset($jenisTagihanTerpilih) && $jenisTagihanTerpilih->id == $biaya->id ? 'selected' : '' }}>
                                    {{ $biaya->nama_tagihan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

            </form>
        </div>

    </div>

    <!-- AREA MATRIKS LEGER -->
    @if ($ruanganTerpilih && $jenisTagihanTerpilih)
        <div class="relative z-10">

            <form action="{{ route('pembayaran-tagihan.leger.proses') }}" method="POST" id="formLeger"
                class="relative z-10 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="ruangan_id" value="{{ $ruanganTerpilih->id }}">
                <input type="hidden" name="pengaturan_tagihan_id" value="{{ $jenisTagihanTerpilih->id }}">

                <!-- 1. KARTU HEADER & ACTION -->
                <div
                    class="m3-glass-card px-5 py-3.5 flex flex-col lg:flex-row justify-between lg:items-center gap-3 relative z-10 shadow-2xs">

                    <!-- Info Kelas -->
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                            <i class="bi bi-grid-1x2-fill text-sm"></i>
                        </div>
                        <div>
                            <h3
                                class="font-black text-zinc-900 dark:text-white text-base uppercase tracking-tight leading-none mb-0.5">
                                Leger {{ $ruanganTerpilih->nama_ruangan }}
                            </h3>
                            <p
                                class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center">
                                <i class="bi bi-tags-fill mr-1 opacity-70"></i>
                                <span
                                    class="text-zinc-600 dark:text-zinc-400">{{ $jenisTagihanTerpilih->nama_tagihan }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions & Counter -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto shrink-0">
                        <!-- Tombol Check All Global -->
                        <button type="button" id="btnCentangGlobal" data-state="none"
                            onclick="toggleCentangSemuaLeger()"
                            class="h-9 px-4 w-full sm:w-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center justify-center gap-1.5 shadow-2xs outline-none">
                            <i class="bi bi-check-all text-sm"></i> <span>Centang Semua</span>
                        </button>

                        <!-- Total Terpilih -->
                        <div class="text-right hidden sm:flex flex-col justify-center px-2">
                            <span class="text-[9px] font-black text-zinc-400 uppercase tracking-wider">TOTAL
                                TERPILIH</span>
                            <span class="text-base font-black text-zinc-900 dark:text-white leading-none mt-0.5"
                                id="teksTotalLeger">Rp 0</span>
                        </div>

                        <!-- Eksekusi Bayar -->
                        <button type="button" id="btnProsesLeger" disabled onclick="eksekusiPembayaranLeger()"
                            class="m3-btn-primary h-9 px-5 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                            <i class="bi bi-wallet2 text-xs"></i> <span>Bayar Terpilih</span>
                        </button>
                    </div>
                </div>

                <!-- 2. GRID TABLE MATRIX LEGER -->
                <div id="containerTabelLeger" class="overflow-x-auto custom-scrollbar pb-4 select-none">
                    <table class="w-full text-left text-xs border-separate border-spacing-y-2 min-w-[1000px]">
                        <thead class="sticky top-0 z-30">
                            <tr
                                class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">

                                <!-- STICKY CHECKBOX HEADER -->
                                <th
                                    class="py-3 px-3 text-center sticky left-0 z-30 bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-l border-zinc-200/80 dark:border-zinc-800 rounded-l-xl w-[50px] min-w-[50px] shadow-2xs">
                                    <i class="bi bi-ui-checks text-xs" title="Centang Per Baris"></i>
                                </th>

                                <!-- STICKY NAMA HEADER -->
                                <th
                                    class="py-3 pl-4 sticky left-[50px] z-30 bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-zinc-200/80 dark:border-zinc-800 w-[240px] min-w-[240px] shadow-2xs">
                                    Nama Murid
                                </th>

                                <!-- KOLOM DINAMIS (BULANAN) -->
                                @if ($jenisTagihanTerpilih->tipe === 'bulanan')
                                    @foreach ($bulanHijriyah as $bln)
                                        <th class="py-2 text-center bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-zinc-200/80 dark:border-zinc-800"
                                            title="{{ $bln->nama_bulan }} {{ $bln->tahun_hijriyah }}">
                                            <div class="leading-none">{{ substr($bln->nama_bulan, 0, 3) }}</div>
                                            <div class="text-[8px] opacity-60 mt-0.5 tracking-tighter">
                                                {{ $bln->tahun_hijriyah }}</div>
                                        </th>
                                    @endforeach
                                @else
                                    <th
                                        class="py-3 px-5 text-center bg-zinc-100/90 dark:bg-zinc-800/90 backdrop-blur-md border-y border-r border-zinc-200/80 dark:border-zinc-800 rounded-r-xl">
                                        Status Tagihan
                                    </th>
                                @endif

                            </tr>
                        </thead>

                        <tbody class="bg-transparent">
                            @foreach ($murids as $murid)
                                @php
                                    $wali = $murid->waliMurid ?? $murid->wali_murid;
                                    $isAsatidz = $wali
                                        ? (bool) ($wali->is_asatidz ?? ($wali->is_ustadz ?? false))
                                        : false;
                                @endphp

                                <!-- BARIS KARTU MELAYANG -->
                                <tr class="group/row transition-all duration-200">

                                    <!-- STICKY ROW CHECKBOX CELL -->
                                    <td
                                        class="p-2 text-center sticky left-0 z-20 bg-white/90 dark:bg-zinc-900/90 backdrop-blur border-y border-l border-zinc-200/80 dark:border-zinc-800 group-hover/row:border-amber-500/40 rounded-l-xl align-middle w-[50px] min-w-[50px] shadow-2xs transition-colors">
                                        <label
                                            class="relative inline-flex items-center justify-center cursor-pointer group/label w-5 h-5">
                                            <input type="checkbox" class="sr-only peer chk-row-leger"
                                                data-row="{{ $murid->id }}"
                                                onchange="toggleCentangBarisLeger(this, '{{ $murid->id }}')">
                                            <div
                                                class="absolute inset-0 rounded-lg transition-all shadow-2xs bg-white/40 dark:bg-black/40 border border-zinc-300 dark:border-zinc-600 peer-checked:bg-amber-600 peer-checked:border-amber-700">
                                            </div>
                                            <i
                                                class="bi bi-check-lg absolute text-white opacity-0 peer-checked:opacity-100 transition-opacity text-xs leading-none pointer-events-none font-black"></i>
                                        </label>
                                    </td>

                                    <!-- STICKY NAMA CELL -->
                                    <td
                                        class="p-3 pl-4 sticky left-[50px] z-20 bg-white/90 dark:bg-zinc-900/90 backdrop-blur border-y border-zinc-200/80 dark:border-zinc-800 group-hover/row:border-amber-500/40 transition-colors w-[240px] min-w-[240px] shadow-2xs">
                                        <div class="font-black text-xs text-zinc-900 dark:text-white tracking-tight mb-0.5 truncate"
                                            title="{{ $murid->nama_lengkap }}">{{ $murid->nama_lengkap }}</div>
                                        <div
                                            class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider truncate">
                                            NISM: {{ $murid->nism ?? '-' }}
                                        </div>

                                        @if ($isAsatidz)
                                            <div class="mt-1">
                                                <span
                                                    class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[8px] font-black uppercase px-1.5 py-0.5 rounded-md tracking-wider">
                                                    ASATIDZ
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- KOLOM DINAMIS (BULANAN) -->
                                    @if ($jenisTagihanTerpilih->tipe === 'bulanan')
                                        @foreach ($bulanHijriyah as $bln)
                                            @php
                                                $tagihan = isset($tagihanExisting[$murid->id])
                                                    ? $tagihanExisting[$murid->id]->firstWhere(
                                                        'bulan_hijriyah_id',
                                                        $bln->id,
                                                    )
                                                    : null;

                                                $isLastCol = $loop->last;
                                            @endphp

                                            <td
                                                class="p-2 text-center bg-white/40 dark:bg-zinc-900/30 border-y {{ $isLastCol ? 'border-r rounded-r-xl' : '' }} border-zinc-200/80 dark:border-zinc-800 group-hover/row:border-amber-500/40 align-middle transition-colors shadow-2xs">

                                                @if ($tagihan)
                                                    @if ($tagihan->status_bayar === 'Lunas')
                                                        <!-- 🟢 SUDAH LUNAS -->
                                                        <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto shadow-2xs"
                                                            title="Lunas ({{ $tagihan->pembayaranTagihan->no_kwitansi ?? 'Lunas' }})">
                                                            <i
                                                                class="bi bi-check-lg text-xs font-black leading-none"></i>
                                                        </div>
                                                    @elseif($tagihan->status_bayar === 'Belum Lunas')
                                                        @php
                                                            $isDonatur = str_contains(
                                                                strtolower($tagihan->nama_tagihan_spesifik),
                                                                'donatur',
                                                            );
                                                        @endphp

                                                        @if ($isDonatur)
                                                            <!-- 🟣 JALUR DONATUR -->
                                                            <span
                                                                class="bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 text-[8px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider"
                                                                title="Ditanggung Donatur">
                                                                DNT
                                                            </span>
                                                        @else
                                                            <!-- 🔴 TUNGGAKAN: CHECKBOX UNTUK BAYAR -->
                                                            <label
                                                                class="relative inline-flex items-center justify-center cursor-pointer group/circle w-6 h-6"
                                                                title="Tunggakan: Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}">
                                                                <input type="checkbox" name="tagihan_ids[]"
                                                                    value="{{ $tagihan->id }}"
                                                                    data-nominal="{{ $tagihan->nominal_tagihan }}"
                                                                    class="sr-only peer chk-leger-item"
                                                                    data-row="{{ $murid->id }}"
                                                                    onchange="hitungTotalLeger()">
                                                                <div
                                                                    class="absolute inset-0 rounded-lg transition-all border bg-rose-500/10 border-rose-500/20 peer-checked:bg-amber-600 peer-checked:border-amber-700 group-hover/circle:scale-110 active:scale-95 shadow-2xs">
                                                                </div>
                                                                <i
                                                                    class="bi bi-dash absolute text-rose-500 opacity-100 peer-checked:opacity-0 transition-opacity text-sm leading-none pointer-events-none"></i>
                                                                <i
                                                                    class="bi bi-check-lg absolute text-white opacity-0 peer-checked:opacity-100 transition-opacity text-xs leading-none pointer-events-none font-black"></i>
                                                            </label>
                                                        @endif
                                                    @endif
                                                @else
                                                    <!-- ⚪ BELUM TERBIT -->
                                                    <span
                                                        class="text-zinc-300 dark:text-zinc-700 font-black select-none text-[10px]">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @else
                                        <!-- KOLOM DINAMIS (SEMESTER/LAINNYA) -->
                                        @php
                                            $tagihan = isset($tagihanExisting[$murid->id])
                                                ? $tagihanExisting[$murid->id]->first()
                                                : null;
                                        @endphp

                                        <td
                                            class="p-2 text-center bg-white/40 dark:bg-zinc-900/30 border-y border-r border-zinc-200/80 dark:border-zinc-800 group-hover/row:border-amber-500/40 rounded-r-xl align-middle transition-colors shadow-2xs">
                                            @if ($tagihan)
                                                @if ($tagihan->status_bayar === 'Lunas')
                                                    <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto shadow-2xs"
                                                        title="Lunas">
                                                        <i class="bi bi-check-lg text-xs font-black leading-none"></i>
                                                    </div>
                                                @else
                                                    <label
                                                        class="relative inline-flex items-center justify-center cursor-pointer group/circle w-6 h-6"
                                                        title="Tunggakan: Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}">
                                                        <input type="checkbox" name="tagihan_ids[]"
                                                            value="{{ $tagihan->id }}"
                                                            data-nominal="{{ $tagihan->nominal_tagihan }}"
                                                            class="sr-only peer chk-leger-item"
                                                            data-row="{{ $murid->id }}"
                                                            onchange="hitungTotalLeger()">
                                                        <div
                                                            class="absolute inset-0 rounded-lg transition-all border bg-rose-500/10 border-rose-500/20 peer-checked:bg-amber-600 peer-checked:border-amber-700 group-hover/circle:scale-110 active:scale-95 shadow-2xs">
                                                        </div>
                                                        <i
                                                            class="bi bi-dash absolute text-rose-500 opacity-100 peer-checked:opacity-0 transition-opacity text-sm leading-none pointer-events-none"></i>
                                                        <i
                                                            class="bi bi-check-lg absolute text-white opacity-0 peer-checked:opacity-100 transition-opacity text-xs leading-none pointer-events-none font-black"></i>
                                                    </label>
                                                @endif
                                            @else
                                                <span
                                                    class="text-zinc-300 dark:text-zinc-700 font-black select-none text-[10px]">—</span>
                                            @endif
                                        </td>
                                    @endif

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 3. FOOTER INFO & SHORTCUT -->
                <div
                    class="m3-glass-card p-3.5 md:px-5 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs text-xs">
                    <div
                        class="flex items-center gap-4 flex-wrap text-zinc-500 dark:text-zinc-400 text-[10px] font-black uppercase tracking-wider">
                        <span class="flex items-center gap-1.5">
                            <span
                                class="w-3 h-3 rounded-md bg-emerald-500/20 border border-emerald-500/40 inline-block"></span>
                            Lunas
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span
                                class="w-3 h-3 rounded-md bg-rose-500/20 border border-rose-500/40 inline-block"></span>
                            Tunggakan (Klik untuk Bayar)
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span
                                class="w-3 h-3 rounded-md bg-purple-500/20 border border-purple-500/40 inline-block"></span>
                            Jalur Donatur
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-[10px] font-black">
                            <i class="bi bi-arrows-move text-xs"></i> Mode Geser Aktif: Tahan & geser mouse / jari
                            untuk pilih banyak
                        </span>
                    </div>

                    <div
                        class="sm:hidden w-full flex items-center justify-between pt-2 border-t border-zinc-200/60 dark:border-zinc-800">
                        <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider">Total</span>
                        <span class="text-base font-black text-zinc-900 dark:text-white" id="teksTotalLegerMobile">Rp
                            0</span>
                    </div>
                </div>

            </form>
        </div>

        <!-- SCRIPT LOGIKA CHECKBOX LEGER, DRAG-TO-SELECT & SUBMISSION -->
        <script>
            // === 1. DRAG TO SELECT (GESER / SWIPE TO SELECT) ===
            let isDragging = false;
            let dragTargetState = true;
            let visitedBoxes = new Set();
            let suppressClickUntil = 0;

            function getCheckboxFromEventTarget(target) {
                if (!target) return null;
                if (target.closest('a, button, select, input[type="text"], input[type="search"]')) return null;

                const label = target.closest('label');
                if (label) {
                    const chk = label.querySelector('.chk-leger-item, .chk-row-leger');
                    if (chk && !chk.disabled) return chk;
                }
                const td = target.closest('td');
                if (td) {
                    const chk = td.querySelector('.chk-leger-item, .chk-row-leger');
                    if (chk && !chk.disabled) return chk;
                }
                return null;
            }

            function animateCheckboxFeedback(chk) {
                if (!chk) return;
                const label = chk.closest('label');
                const visualBox = label ? label.querySelector('div') : null;
                if (visualBox) {
                    visualBox.classList.remove('scale-125', 'ring-4', 'ring-amber-500/50');
                    void visualBox.offsetWidth;
                    visualBox.classList.add('scale-125', 'ring-4', 'ring-amber-500/50');
                    setTimeout(() => {
                        visualBox.classList.remove('scale-125', 'ring-4', 'ring-amber-500/50');
                    }, 180);
                }
            }

            function initDragToSelect() {
                const container = document.getElementById('containerTabelLeger') || document.querySelector('.overflow-x-auto');
                if (!container) return;

                // 1. Mouse Down
                container.addEventListener('mousedown', (e) => {
                    if (e.button !== 0) return;
                    const chk = getCheckboxFromEventTarget(e.target);
                    if (chk) {
                        isDragging = true;
                        dragTargetState = !chk.checked;
                        visitedBoxes.clear();
                        visitedBoxes.add(chk);
                        chk.checked = dragTargetState;

                        if (chk.classList.contains('chk-row-leger')) {
                            toggleCentangBarisLeger(chk, chk.dataset.row);
                        } else {
                            hitungTotalLeger();
                        }

                        animateCheckboxFeedback(chk);
                        document.body.classList.add('select-none');
                        suppressClickUntil = Date.now() + 300;
                        e.preventDefault();
                    }
                });

                // 2. Mouse Over / Move
                const handleMove = (e) => {
                    if (!isDragging) return;
                    const chk = getCheckboxFromEventTarget(e.target);
                    if (chk && !visitedBoxes.has(chk)) {
                        visitedBoxes.add(chk);
                        chk.checked = dragTargetState;

                        if (chk.classList.contains('chk-row-leger')) {
                            toggleCentangBarisLeger(chk, chk.dataset.row);
                        } else {
                            hitungTotalLeger();
                        }

                        animateCheckboxFeedback(chk);
                    }
                };

                container.addEventListener('mouseover', handleMove);
                container.addEventListener('mousemove', handleMove);

                // 3. Mouse Up (Global)
                document.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        visitedBoxes.clear();
                        document.body.classList.remove('select-none');
                    }
                });

                // 4. Suppress duplicate synthetic click
                container.addEventListener('click', (e) => {
                    if (Date.now() < suppressClickUntil) {
                        const chk = getCheckboxFromEventTarget(e.target);
                        if (chk) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }
                }, true);

                // 5. Touch Events (HP / Tablet)
                container.addEventListener('touchstart', (e) => {
                    if (e.touches.length !== 1) return;
                    const touch = e.touches[0];
                    const target = document.elementFromPoint(touch.clientX, touch.clientY);
                    const chk = getCheckboxFromEventTarget(target);
                    if (chk) {
                        isDragging = true;
                        dragTargetState = !chk.checked;
                        visitedBoxes.clear();
                        visitedBoxes.add(chk);
                        chk.checked = dragTargetState;

                        if (chk.classList.contains('chk-row-leger')) {
                            toggleCentangBarisLeger(chk, chk.dataset.row);
                        } else {
                            hitungTotalLeger();
                        }

                        animateCheckboxFeedback(chk);
                        suppressClickUntil = Date.now() + 400;
                    }
                }, {
                    passive: true
                });

                container.addEventListener('touchmove', (e) => {
                    if (!isDragging || e.touches.length !== 1) return;
                    const touch = e.touches[0];
                    const target = document.elementFromPoint(touch.clientX, touch.clientY);
                    const chk = getCheckboxFromEventTarget(target);
                    if (chk && !visitedBoxes.has(chk)) {
                        visitedBoxes.add(chk);
                        chk.checked = dragTargetState;

                        if (chk.classList.contains('chk-row-leger')) {
                            toggleCentangBarisLeger(chk, chk.dataset.row);
                        } else {
                            hitungTotalLeger();
                        }

                        animateCheckboxFeedback(chk);
                    }
                    if (isDragging && e.cancelable && chk) {
                        e.preventDefault();
                    }
                }, {
                    passive: false
                });

                container.addEventListener('touchend', () => {
                    if (isDragging) {
                        isDragging = false;
                        visitedBoxes.clear();
                    }
                });
            }

            // === 2. KALKULASI & SINKRONISASI LEGER ===
            function hitungTotalLeger() {
                let total = 0;
                let checkedCount = 0;
                const checkboxes = document.querySelectorAll('.chk-leger-item');
                const checkedBoxes = document.querySelectorAll('.chk-leger-item:checked');

                checkboxes.forEach(chk => {
                    if (chk.checked) {
                        total += parseInt(chk.dataset.nominal || 0);
                        checkedCount++;
                    }
                });

                document.getElementById('teksTotalLeger').innerText = 'Rp ' + total.toLocaleString('id-ID');
                const mobTotal = document.getElementById('teksTotalLegerMobile');
                if (mobTotal) mobTotal.innerText = 'Rp ' + total.toLocaleString('id-ID');

                const btn = document.getElementById('btnProsesLeger');
                if (btn) btn.disabled = (checkedCount === 0);

                // Sinkronisasi status tombol Centang Semua
                const btnGlobal = document.getElementById('btnCentangGlobal');
                if (btnGlobal && checkboxes.length > 0) {
                    const icon = btnGlobal.querySelector('i');
                    const text = btnGlobal.querySelector('span');

                    if (checkboxes.length === checkedBoxes.length) {
                        btnGlobal.dataset.state = 'all';
                        icon.className = 'bi bi-x-lg text-xs font-black';
                        text.innerText = 'Hapus Centang';
                        btnGlobal.classList.replace('text-zinc-700', 'text-rose-600');
                        btnGlobal.classList.replace('dark:text-zinc-300', 'dark:text-rose-400');
                    } else {
                        btnGlobal.dataset.state = 'none';
                        icon.className = 'bi bi-check-all text-sm';
                        text.innerText = 'Centang Semua';
                        btnGlobal.classList.replace('text-rose-600', 'text-zinc-700');
                        btnGlobal.classList.replace('dark:text-rose-400', 'dark:text-zinc-300');
                    }
                }

                // Sinkronisasi checkbox per baris
                document.querySelectorAll('.chk-row-leger').forEach(rowChk => {
                    const rowId = rowChk.dataset.row;
                    const rowCheckboxes = document.querySelectorAll(`.chk-leger-item[data-row="${rowId}"]`);
                    const rowChecked = document.querySelectorAll(`.chk-leger-item[data-row="${rowId}"]:checked`);

                    if (rowCheckboxes.length > 0) {
                        rowChk.checked = (rowCheckboxes.length === rowChecked.length);
                        rowChk.disabled = false;
                        rowChk.parentElement.classList.remove('opacity-30', 'cursor-not-allowed');
                    } else {
                        rowChk.disabled = true;
                        rowChk.parentElement.classList.add('opacity-30', 'cursor-not-allowed');
                    }
                });
            }

            function toggleCentangSemuaLeger() {
                const btn = document.getElementById('btnCentangGlobal');
                const isAll = btn.dataset.state === 'all';
                document.querySelectorAll('.chk-leger-item').forEach(chk => {
                    chk.checked = !isAll;
                });
                hitungTotalLeger();
            }

            function toggleCentangBarisLeger(obj, rowId) {
                const isChecked = obj.checked;
                document.querySelectorAll(`.chk-leger-item[data-row="${rowId}"]`).forEach(chk => {
                    chk.checked = isChecked;
                });
                hitungTotalLeger();
            }

            function eksekusiPembayaranLeger() {
                const checkedBoxes = document.querySelectorAll('.chk-leger-item:checked');
                if (checkedBoxes.length === 0) return;

                const isDark = document.documentElement.classList.contains('dark');
                const totalTeks = document.getElementById('teksTotalLeger').innerText;

                Swal.fire({
                    title: '<span class="text-base font-black tracking-tight">Proses Pelunasan Leger?</span>',
                    html: `<p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">Anda akan memproses <b>${checkedBoxes.length} item tagihan</b> dengan total nominal <b class="text-emerald-500">${totalTeks}</b>.<br>Kwitansi pelunasan massal akan otomatis dibuat.</p>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#71717a',
                    confirmButtonText: 'Ya, Bayar Sekarang',
                    cancelButtonText: 'Batal',
                    heightAuto: false,
                    background: isDark ? '#0c0c0e' : '#ffffff',
                    color: isDark ? '#f4f4f5' : '#18181b',
                    customClass: {
                        popup: '!rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl p-6',
                        confirmButton: "h-10 px-5 bg-amber-600 hover:bg-amber-700 text-white font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none",
                        cancelButton: "h-10 px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formLeger').submit();
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                hitungTotalLeger();
                initDragToSelect();
            });
        </script>
    @else
        <!-- STATE KOSONG / AWAL -->
        <div class="col-span-full">
            <x-empty-state icon="bi-grid-1x2" title="Menunggu Parameter Leger"
                message="Silakan pilih Ruangan dan Kriteria Tagihan pada filter di atas untuk menampilkan leger pembayaran." />
        </div>
    @endif

</x-app-layout>
