@section('title', 'Pembayaran Ujian')

<x-app-layout>
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('pembayaran-ujian.menu-pembayaran')
        </div>

        <!-- Filter Ruangan & Tagihan -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET" id="formFilterUjian"
                class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto m3-glass-card p-2 shadow-2xs">

                @if (request('search_nism'))
                    <input type="hidden" name="search_nism" value="{{ request('search_nism') }}">
                @endif

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-[180px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-calendar-range text-xs"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formFilterUjian').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-5 bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[190px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-door-open-fill text-xs"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formFilterUjian').submit()"
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
                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-5 bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>

                <!-- Filter Tarif Tagihan -->
                <div class="relative w-full sm:w-[210px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-tags-fill text-xs"></i>
                    </div>
                    <select name="pengaturan_tagihan_id" {{ !$ruanganTerpilih ? 'disabled' : '' }}
                        onchange="document.getElementById('formFilterUjian').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none disabled:opacity-50 disabled:cursor-not-allowed">
                        @if (!$ruanganTerpilih)
                            <option value="">-- Pilih Ruangan Dulu --</option>
                        @else
                            <option value="">-- Jenis Tagihan --</option>
                            @foreach ($masterBiayas as $biaya)
                                <option value="{{ $biaya->id }}"
                                    {{ isset($jenisTagihanTerpilih) && $jenisTagihanTerpilih->id == $biaya->id ? 'selected' : '' }}>
                                    {{ $biaya->nama_tagihan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

            </form>
        </div>

    </div>

    <!-- AREA MATRIKS LEGER -->
    @if ($ruanganTerpilih && $jenisTagihanTerpilih)
        <div class="relative z-10 mb-6">
            <form action="{{ route('pembayaran-ujian.proses') }}" method="POST"
                class="flex flex-col relative min-h-[60vh]">
                @csrf

                <!-- TOP HEADER CARD -->
                <div
                    class="m3-glass-card p-4 md:p-5 mb-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 shadow-2xs">
                    <div>
                        <h3
                            class="font-black text-zinc-900 dark:text-white text-base md:text-lg uppercase tracking-tight mb-0.5">
                            {{ $ruanganTerpilih->nama_ruangan }}
                        </h3>
                        <p
                            class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center">
                            <i class="bi bi-tags-fill mr-1.5 opacity-70"></i> Penerimaan:
                            {{ $jenisTagihanTerpilih->nama_tagihan }} | Rp
                            {{ number_format($jenisTagihanTerpilih->nominal, 0, ',', '.') }}
                        </p>
                    </div>
                    <!-- Legend & Badge -->
                    <div class="flex items-center flex-wrap gap-2">
                        <!-- Mode Geser Info Badge -->
                        <span
                            class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider shadow-2xs">
                            <i class="bi bi-hand-index-thumb text-xs animate-pulse"></i> Mode Geser Aktif
                        </span>
                        <div
                            class="flex items-center gap-3 text-[10px] font-bold text-zinc-500 uppercase tracking-wider bg-zinc-100/50 dark:bg-zinc-800/50 px-3 py-1.5 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                            <span class="flex items-center gap-1"><span
                                    class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Tunggakan</span>
                            <span class="flex items-center gap-1"><span
                                    class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Lunas</span>
                        </div>
                    </div>
                </div>

                <!-- LIST CARDS (Daftar Murid) -->
                <div id="containerListUjian" class="flex flex-col gap-2.5 pb-28">
                    @foreach ($murids as $murid)
                        @php
                            $namaSpesifik = $jenisTagihanTerpilih->nama_tagihan;

                            if (
                                $jenisTagihanTerpilih->tipe === 'semester' &&
                                in_array($ruanganTerpilih->level->nama_level ?? '', ['3 TPQ', '6 IBT', '3 TSA']) &&
                                strtolower($namaSpesifik) === 'iuran imda 2'
                            ) {
                                $namaSpesifik = 'Iuran IMNI';
                            }

                            $tagihan = isset($tagihanExisting[$murid->id])
                                ? $tagihanExisting[$murid->id]->firstWhere('nama_tagihan_spesifik', $namaSpesifik)
                                : null;
                        @endphp

                        <!-- KARTU MURID -->
                        <div
                            class="group flex flex-row items-center gap-3 p-3 m3-glass-card rounded-2xl shadow-2xs hover:border-emerald-500/40 dark:hover:border-emerald-500/40 transition-all duration-200 relative overflow-hidden">

                            <!-- KIRI: Checkbox Row (Aksi) -->
                            <div class="flex-shrink-0">
                                <label
                                    class="relative inline-flex items-center justify-center cursor-pointer group/label w-5 h-5">
                                    <input type="checkbox" class="sr-only peer chk-row" data-row="{{ $murid->id }}"
                                        onchange="toggleCentangBaris(this, '{{ $murid->id }}')">
                                    <div
                                        class="absolute inset-0 rounded-lg transition-all duration-200 shadow-2xs bg-white/40 dark:bg-black/40 border border-zinc-300 dark:border-zinc-600 peer-checked:bg-emerald-600 peer-checked:border-emerald-600">
                                    </div>
                                    <i
                                        class="bi bi-check-lg absolute text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200 text-xs leading-none pointer-events-none font-black"></i>
                                </label>
                            </div>

                            <!-- Nomor Urut -->
                            <span
                                class="hidden sm:flex w-8 h-8 items-center justify-center bg-zinc-100/60 dark:bg-zinc-800/60 text-zinc-500 dark:text-zinc-400 rounded-xl text-[11px] font-black border border-zinc-200/50 dark:border-zinc-700/50 flex-shrink-0">
                                {{ $loop->iteration }}
                            </span>

                            <!-- TENGAH: Info Murid & Avatar -->
                            <div class="flex flex-1 items-center gap-3 min-w-0">

                                <!-- Foto Murid -->
                                <div
                                    class="w-9 h-9 rounded-xl overflow-hidden flex-shrink-0 border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-2xs">
                                    <img src="{{ isset($murid->foto) && $murid->foto
                                        ? asset('storage/' . $murid->foto)
                                        : asset($murid->jenis_kelamin === 'P' ? 'assets/perempuan-default.png' : 'assets/laki-default.png') }}"
                                        alt="Foto {{ $murid->nama_lengkap }}" class="w-full h-full object-cover">
                                </div>

                                <!-- Detail Teks Murid -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-black text-xs md:text-sm text-zinc-900 dark:text-white tracking-tight truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"
                                        title="{{ $murid->nama_lengkap }}">
                                        {{ $murid->nama_lengkap }}
                                    </h4>

                                    <div
                                        class="flex flex-wrap items-center gap-1.5 mt-0.5 text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                                        <span
                                            class="bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-1.5 py-0.5 rounded text-zinc-600 dark:text-zinc-300">
                                            NISM: {{ $murid->nism ?? '-' }}
                                        </span>
                                        <span
                                            class="bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-1.5 py-0.5 rounded text-zinc-600 dark:text-zinc-300">
                                            {{ $murid->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-Laki' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- KANAN: Status Tagihan & Action -->
                            <div class="flex-shrink-0 ml-2">
                                @if (!$tagihan)
                                    <!-- BELUM TERBIT -->
                                    <div class="w-8 h-8 rounded-xl border border-dashed flex items-center justify-center bg-zinc-100/50 text-zinc-300 border-zinc-200 dark:bg-zinc-800/30 dark:text-zinc-600 dark:border-zinc-700"
                                        title="Tagihan Belum Diterbitkan">
                                        <i class="bi bi-dash text-base leading-none"></i>
                                    </div>
                                @elseif($tagihan->status_bayar === 'Belum Lunas')
                                    <!-- TUNGGAKAN / INTERAKTIF -->
                                    <label
                                        class="relative inline-flex items-center justify-center cursor-pointer group/circle w-9 h-9"
                                        title="Belum Lunas">
                                        <input type="checkbox" name="tagihan_ids[]" value="{{ $tagihan->id }}"
                                            onchange="hitungCeklis()" class="sr-only peer chk-tunggakan"
                                            data-row="{{ $murid->id }}">
                                        <div
                                            class="absolute inset-0 rounded-xl transition-all duration-200 border border-amber-500/40 shadow-2xs group-hover/circle:scale-105 active:scale-95 bg-amber-500/10 peer-checked:bg-emerald-600 peer-checked:border-emerald-600">
                                        </div>
                                        <i
                                            class="bi bi-exclamation absolute opacity-100 peer-checked:opacity-0 transition-opacity duration-200 text-lg leading-none pointer-events-none text-amber-500 dark:text-amber-400 font-black"></i>
                                        <i
                                            class="bi bi-check-lg absolute text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200 text-base leading-none pointer-events-none font-black"></i>
                                    </label>
                                @else
                                    <!-- SUDAH LUNAS -->
                                    <div class="flex flex-col sm:items-end gap-1.5 w-full sm:w-auto">

                                        <!-- Badge Status Lunas & Harga Coret -->
                                        <div class="flex items-center justify-between sm:justify-end w-full gap-2">
                                            <span
                                                class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 line-through decoration-zinc-400/50">
                                                Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                                            </span>

                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400"
                                                title="Sudah Lunas">
                                                <i class="bi bi-check-all text-xs leading-none"></i>
                                                <span
                                                    class="text-[9px] font-black uppercase tracking-wider leading-none">Lunas</span>
                                            </div>
                                        </div>

                                        <!-- Tombol Aksi: Cetak & Batal -->
                                        <div class="flex items-center justify-end gap-1 w-full shrink-0">
                                            <!-- Tombol Cetak -->
                                            <a href="{{ route('pembayaran-ujian.cetak', $tagihan->pembayaran_tagihan_id) }}"
                                                target="_blank"
                                                class="flex-1 sm:flex-none flex items-center justify-center gap-1 h-6 px-2 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/20 text-sky-600 dark:text-sky-400 transition-colors shadow-2xs text-[9px] font-black uppercase tracking-wider"
                                                title="Cetak Kwitansi">
                                                <i class="bi bi-printer-fill text-[10px]"></i>
                                                <span class="sm:hidden">Cetak</span>
                                            </a>

                                            <!-- Tombol Batal -->
                                            <button type="button"
                                                onclick="konfirmasiBatal({{ $tagihan->id }}, '{{ addslashes($tagihan->nama_tagihan_spesifik) }}')"
                                                class="flex-1 sm:flex-none flex items-center justify-center gap-1 h-6 px-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors shadow-2xs outline-none text-[9px] font-black uppercase tracking-wider"
                                                title="Batalkan Pembayaran">
                                                <i class="bi bi-x-lg text-[10px] font-black"></i>
                                                <span class="sm:hidden">Batal</span>
                                            </button>
                                        </div>

                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- FLOATING ACTION CARD -->
                <div class="sticky bottom-6 z-40 mt-6 m3-glass-card rounded-2xl shadow-2xl p-3 flex flex-col sm:flex-row justify-between items-center gap-3 w-full"
                    style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">

                    <div class="text-[10px] font-black text-zinc-400 uppercase tracking-wider hidden sm:block">
                        Tindakan Massal
                    </div>

                    <div class="flex flex-row items-center gap-2 w-full sm:w-auto">

                        <!-- Tombol Check All Global -->
                        <button type="button" id="btnCentangGlobal" data-state="none"
                            onclick="toggleCentangSemua()"
                            class="w-1/2 sm:w-auto px-4 h-10 m3-glass-card text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center justify-center gap-1.5 shrink-0 shadow-2xs outline-none">
                            <i class="bi bi-check-all text-sm"></i>
                            <span class="truncate">Pilih Semua</span>
                        </button>

                        <!-- Tombol Proses -->
                        <button type="submit" id="btnProses" disabled
                            class="w-1/2 sm:w-auto px-5 h-10 m3-btn-primary text-xs font-black uppercase tracking-wider transition-all active:scale-95 outline-none shadow-2xs flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="bi bi-wallet2 text-xs"></i>
                            <span class="truncate">Lunasi (<span id="txtJumlah" class="ml-0.5">0</span>)</span>
                        </button>

                    </div>
                </div>

            </form>
        </div>

        <form id="formBatal" method="POST" class="hidden">
            @csrf
        </form>

        <!-- SCRIPT SINKRONISASI CHECKBOX -->
        <script>
            function hitungCeklis() {
                const checkboxes = document.querySelectorAll('.chk-tunggakan:checked');
                document.getElementById('txtJumlah').innerText = checkboxes.length;

                const btn = document.getElementById('btnProses');
                if (btn) btn.disabled = checkboxes.length === 0;

                updateGlobalButtonState();
            }

            function updateGlobalButtonState() {
                const checkboxes = document.querySelectorAll('.chk-tunggakan:not([disabled])');
                const checkedBoxes = document.querySelectorAll('.chk-tunggakan:not([disabled]):checked');
                const btn = document.getElementById('btnCentangGlobal');
                if (!btn) return;

                const icon = btn.querySelector('i');
                const text = btn.querySelector('span');

                if (checkboxes.length > 0 && checkboxes.length === checkedBoxes.length) {
                    btn.dataset.state = 'all';
                    icon.className = 'bi bi-x-lg text-xs';
                    text.innerText = 'Batal Pilih';
                    btn.classList.add('!text-rose-500', '!border-rose-500/30');
                } else {
                    btn.dataset.state = 'none';
                    icon.className = 'bi bi-check-all text-sm';
                    text.innerText = 'Centang Semua';
                    btn.classList.remove('!text-rose-500', '!border-rose-500/30');
                }

                document.querySelectorAll('.chk-row').forEach(rowChk => {
                    const rowId = rowChk.dataset.row;
                    const rowCheckboxes = document.querySelectorAll(
                        `.chk-tunggakan[data-row="${rowId}"]:not([disabled])`);
                    const rowChecked = document.querySelectorAll(
                        `.chk-tunggakan[data-row="${rowId}"]:not([disabled]):checked`);

                    if (rowCheckboxes.length > 0) {
                        rowChk.checked = (rowCheckboxes.length === rowChecked.length);
                        rowChk.disabled = false;
                        rowChk.parentElement.classList.remove('opacity-30', 'cursor-not-allowed', 'grayscale');
                    } else {
                        rowChk.disabled = true;
                        rowChk.parentElement.classList.add('opacity-30', 'cursor-not-allowed', 'grayscale');
                    }
                });
            }

            function toggleCentangSemua() {
                const btn = document.getElementById('btnCentangGlobal');
                const isAll = btn.dataset.state === 'all';

                document.querySelectorAll('.chk-tunggakan:not([disabled])').forEach(chk => {
                    chk.checked = !isAll;
                });
                hitungCeklis();
            }

            function toggleCentangBaris(obj, rowId) {
                const isChecked = obj.checked;
                document.querySelectorAll(`.chk-tunggakan[data-row="${rowId}"]:not([disabled])`).forEach(chk => {
                    chk.checked = isChecked;
                });
                hitungCeklis();
            }

            function konfirmasiBatal(id, nama) {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: '<span class="text-base font-black text-zinc-900 dark:text-white">Batalkan Pelunasan?</span>',
                    html: `<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1">Apakah Anda yakin ingin membatalkan kwitansi pelunasan <b class="text-rose-500">${nama}</b>?<br>Status tagihan akan dikembalikan menjadi Tunggakan.</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: '<span class="text-zinc-700 dark:text-zinc-300">Tutup</span>',
                    background: isDark ? '#09090b' : '#ffffff',
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl backdrop-blur-xl',
                        confirmButton: 'rounded-xl font-bold px-4 py-2 text-xs',
                        cancelButton: 'rounded-xl font-bold px-4 py-2 text-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('formBatal');
                        if (form) {
                            form.action = `/pembayaran-ujian/batal/${id}`;
                            form.submit();
                        } else {
                            window.location.href = `/pembayaran-ujian/batal/${id}`;
                        }
                    }
                });
            }

            // === DRAG TO SELECT (GESER / SWIPE TO SELECT) ===
            let isDragging = false;
            let dragTargetState = true;
            let visitedBoxes = new Set();
            let suppressClickUntil = 0;

            function getCheckboxFromEventTarget(target) {
                if (!target) return null;
                if (target.closest('a, button, select, input[type="text"], input[type="search"]')) return null;

                const label = target.closest('label');
                if (label) {
                    const chk = label.querySelector('.chk-tunggakan, .chk-row');
                    if (chk && !chk.disabled) return chk;
                }
                const card = target.closest('.group');
                if (card) {
                    const chk = card.querySelector('.chk-tunggakan:not([disabled]), .chk-row:not([disabled])');
                    if (chk) return chk;
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
                const container = document.getElementById('containerListUjian') || document.querySelector('.pb-28');
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

                        if (chk.classList.contains('chk-row')) {
                            toggleCentangBaris(chk, chk.dataset.row);
                        } else {
                            hitungCeklis();
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

                        if (chk.classList.contains('chk-row')) {
                            toggleCentangBaris(chk, chk.dataset.row);
                        } else {
                            hitungCeklis();
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

                        if (chk.classList.contains('chk-row')) {
                            toggleCentangBaris(chk, chk.dataset.row);
                        } else {
                            hitungCeklis();
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

                        if (chk.classList.contains('chk-row')) {
                            toggleCentangBaris(chk, chk.dataset.row);
                        } else {
                            hitungCeklis();
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

            document.addEventListener('DOMContentLoaded', () => {
                hitungCeklis();
                initDragToSelect();
            });
        </script>
    @else
        <!-- State Awal -->
        <div class="col-span-full">
            <x-empty-state icon="bi-grid-1x2" title="Menunggu Parameter Kasir Leger"
                message="Silakan pilih Ruangan dan Tagihan pada filter di atas untuk menampilkan buku leger pembayaran." />
        </div>
    @endif

</x-app-layout>
