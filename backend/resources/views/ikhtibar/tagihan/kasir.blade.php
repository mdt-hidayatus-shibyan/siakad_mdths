@section('title', 'Kasir Pembayaran Iuran Ikhtibar')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div class="mb-6 flex flex-col xl:flex-row xl:items-start justify-between gap-4 relative z-10 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('ikhtibar.tagihan.menu-kasir')
        </div>

        <!-- Filter Tahun Pelajaran & Info Ikhtibar -->
        <div class="w-full xl:w-auto shrink-0 flex items-center gap-2.5">
            <form action="{{ request()->url() }}" method="GET" id="formTahun" class="m-0 relative group h-10">
                @if (request('search_wali'))
                    <input type="hidden" name="search_wali" value="{{ request('search_wali') }}">
                @endif
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formTahun').submit()"
                    class="m3-input-glass w-full xl:w-60 !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    @foreach ($daftarTahun as $tp)
                        <option value="{{ $tp->id }}"
                            {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- BANNER INFO KEGIATAN IKHTIBAR -->
    <div
        class="mb-6 m3-glass-card p-4 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-4 border border-primary/20 bg-primary/5 dark:bg-primary/10">
        <div class="flex items-center gap-3">
            @if ($kegiatan->logo_kegiatan)
                <img src="{{ asset('storage/' . $kegiatan->logo_kegiatan) }}" alt="Logo"
                    class="w-12 h-12 rounded-2xl object-cover border border-primary/30 shadow-2xs">
            @elseif ($kegiatan->kop_logo_ikhtibar)
                <img src="{{ asset('storage/' . $kegiatan->kop_logo_ikhtibar) }}" alt="Logo"
                    class="w-12 h-12 rounded-2xl object-cover border border-primary/30 shadow-2xs">
            @else
                <div
                    class="w-12 h-12 rounded-2xl bg-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-xl font-black">
                    <i class="bi bi-stars"></i>
                </div>
            @endif
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-primary text-white">
                        IKHTIBAR {{ $kegiatan->ikhtibar_ke ?: '52 Th' }}
                    </span>
                    <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">
                        {{ $kegiatan->tahunPelajaran?->nama_hijriyah ?? 'Tahun Berjalan' }}
                    </span>
                </div>
                <h2 class="text-base md:text-lg font-black text-zinc-900 dark:text-white mt-0.5">
                    {{ $kegiatan->nama_kegiatan }}
                </h2>
                @if ($kegiatan->tema_kegiatan)
                    <p class="text-xs text-zinc-500 italic">"{{ $kegiatan->tema_kegiatan }}"</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs font-bold">
            <div
                class="p-2.5 rounded-2xl bg-white/80 dark:bg-zinc-900/80 border border-zinc-200/80 dark:border-zinc-800 text-right">
                <span class="text-[10px] uppercase text-zinc-400 block font-black">Tarif Tagihan Per KK</span>
                <span class="text-primary font-black text-sm">
                    Rp {{ number_format($defaultMaster?->nominal ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- ALERT FEEDBACK -->
    @if (session('success'))
        <div
            class="p-4 mb-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:opacity-70">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div
            class="p-4 mb-6 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:opacity-70">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    <!-- PANEL PENCARIAN WALI MURID (BARCODE & KEYWORD) -->
    <div class="m3-glass-card p-3 md:p-3.5 mb-6 shadow-2xs relative z-10 print:hidden">
        <form action="{{ route('tagihan-ikhtibar.kasir') }}" method="GET"
            class="w-full flex flex-col sm:flex-row gap-2.5">
            <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

            <div class="relative w-full flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" name="search_wali" value="{{ request('search_wali') }}"
                    placeholder="Scan Barcode No. Registrasi / Ketik No. KK / NISM / Nama Kepala Keluarga..." required
                    autofocus autocomplete="off" class="m3-input-glass w-full !pl-9 text-xs font-bold">
                @if (request('search_wali'))
                    <a href="{{ route('tagihan-ikhtibar.kasir', ['tahun_id' => $tahunPelajaranId]) }}"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-rose-500"
                        title="Reset Pencarian">
                        <i class="bi bi-x-circle-fill text-sm"></i>
                    </a>
                @endif
            </div>

            <button type="submit"
                class="m3-btn-primary w-full sm:w-auto px-5 h-10 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5 shrink-0">
                <i class="bi bi-search"></i> <span>Cari</span>
            </button>
        </form>
    </div>

    @if ($waliTerpilih)
        <!-- AREA HASIL KASIR: PROFIL WALI MURID & TABEL TAGIHAN -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- KIRI: KARTU PROFIL WALI MURID / KK (COL-SPAN-4) -->
            <div class="lg:col-span-4 space-y-4">
                <div
                    class="m3-glass-card p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs sticky top-24">
                    <!-- Header Profil -->
                    <div class="flex items-center gap-3.5 pb-4 border-b border-zinc-200/60 dark:border-zinc-800">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl shrink-0 font-black shadow-2xs">
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span
                                class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                {{ $waliTerpilih->kampung?->nama_kampung ?? 'Dusun -' }}
                            </span>
                            <h3 class="text-base font-black text-zinc-900 dark:text-white truncate tracking-tight mt-1">
                                {{ $waliTerpilih->nama_kepala_keluarga }}
                            </h3>
                            <p class="text-[10px] text-zinc-400 font-mono">
                                Reg: <span
                                    class="font-bold text-zinc-700 dark:text-zinc-300">{{ $waliTerpilih->no_registrasi ?? '-' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Detail Data KK -->
                    <div class="py-3.5 space-y-2.5 text-xs border-b border-zinc-200/60 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400 text-[11px] font-bold">No. WhatsApp</span>
                            <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $waliTerpilih->no_hp ?: '-' }}
                            </span>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-zinc-400 text-[11px] font-bold shrink-0">Alamat</span>
                            <span class="font-semibold text-zinc-700 dark:text-zinc-300 text-right text-[11px]">
                                {{ $waliTerpilih->kampung->kode ?? '-' }} -
                                {{ $waliTerpilih->kampung->nama_kampung ?? '-' }}

                            </span>
                        </div>
                    </div>

                    <!-- Tanggungan Murid Aktif & Tabungan -->
                    <div class="pt-3.5 space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block mb-2">
                            Santri / Murid Terdaftar ({{ $waliTerpilih->murids->count() }} Orang)
                        </span>

                        @forelse ($waliTerpilih->murids as $anak)
                            <div
                                class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800/80 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-black text-zinc-800 dark:text-zinc-200 truncate">
                                        {{ $anak->nama_lengkap }}
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold shrink-0">
                                        {{ $anak->ruangans->first()?->nama_ruangan ?? 'Kelas -' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-zinc-400 mt-1">
                                    <span>NISM: {{ $anak->nism ?: '-' }}</span>
                                    @php
                                        $tabunganAnak = $anak->tabungans->first();
                                    @endphp
                                    @if ($tabunganAnak)
                                        <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                            Tab: Rp {{ number_format($tabunganAnak->saldo, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400 italic">Tidak ada santri aktif dalam KK ini.</p>
                        @endforelse
                    </div>

                    <!-- Tombol Cetak Kartu Tagihan KK -->
                    <div class="pt-3 border-t border-zinc-200/60 dark:border-zinc-800">
                        <a href="{{ route('tagihan-ikhtibar.cetak-kartu', [$waliTerpilih->id, $tahunPelajaranId]) }}"
                            target="_blank"
                            class="w-full py-2.5 px-3 rounded-2xl bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 hover:text-white dark:text-emerald-400 dark:hover:text-white border border-emerald-500/20 text-xs font-black flex items-center justify-center gap-2 transition-all shadow-2xs active:scale-95 cursor-pointer">
                            <i class="bi bi-person-vcard text-sm"></i>
                            <span>Cetak Kartu Tagihan KK</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- KANAN: DAFTAR TAGIHAN IKHTIBAR & FORM PEMBAYARAN (COL-SPAN-8) -->
            <div class="lg:col-span-8 space-y-6">
                <div
                    class="m3-glass-card p-5 md:p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 shadow-2xs space-y-5">
                        <div
                            class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                            <div>
                                <h3 class="text-base font-black text-zinc-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-wallet2 text-primary"></i>
                                    <span>Tagihan Iuran Ikhtibar Tahun Ini</span>
                                </h3>
                                <p class="text-xs text-zinc-500 mt-0.5">Pilih tagihan yang akan dibayarkan oleh Wali
                                    Murid.</p>
                            </div>
                        </div>

                        <!-- TABEL TAGIHAN KK -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr
                                        class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 uppercase tracking-wider text-[10px] font-black">
                                        <th class="py-2.5 px-3 w-10 text-center">Pilih</th>
                                        <th class="py-2.5 px-3">Uraian Tagihan</th>
                                        <th class="py-2.5 px-3 text-right">Tagihan</th>
                                        <th class="py-2.5 px-3 text-right">Terbayar</th>
                                        <th class="py-2.5 px-3 text-right">Sisa</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                        <th class="py-2.5 px-3 text-center">Aksi / Kwitansi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                    @forelse ($semuaTagihan as $tgh)
                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                            <td class="py-3 px-3 text-center">
                                                @if ($tgh->status_bayar !== 'Lunas')
                                                    <input type="checkbox" name="tagihan_ids[]"
                                                        value="{{ $tgh->id }}"
                                                        form="formBayarIkhtibar"
                                                        class="tagihan-checkbox rounded border-zinc-300 text-primary focus:ring-primary h-4 w-4 cursor-pointer"
                                                        data-nominal="{{ $tgh->sisa_tagihan > 0 ? $tgh->sisa_tagihan : $tgh->nominal_tagihan }}"
                                                        data-sisa="{{ $tgh->sisa_tagihan > 0 ? $tgh->sisa_tagihan : $tgh->nominal_tagihan }}"
                                                        data-termin="{{ $tgh->termin_berikutnya }}"
                                                        data-count="{{ $tgh->jumlah_cicilan }}" checked>
                                                @else
                                                    <i class="bi bi-check-circle-fill text-emerald-500 text-base"
                                                        title="Sudah Lunas"></i>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="font-bold text-zinc-900 dark:text-white block">
                                                    {{ $tgh->nama_tagihan_spesifik }}
                                                </span>
                                                <span class="text-[10px] text-zinc-400 font-mono">
                                                    Kode: {{ $tgh->pengaturanTagihan?->kode_tagihan ?? 'IKH' }}
                                                </span>

                                                {{-- Detail Riwayat Cicilan jika ada --}}
                                                @if ($tgh->pembayaranIkhtibars->isNotEmpty())
                                                    <div
                                                        class="mt-2 p-2.5 rounded-xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 text-[10px] space-y-1.5">
                                                        <div
                                                            class="flex items-center justify-between font-black text-amber-700 dark:text-amber-400">
                                                            <span class="flex items-center gap-1">
                                                                <i class="bi bi-clock-history"></i> Riwayat Cicilan
                                                                ({{ $tgh->pembayaranIkhtibars->count() }}/3 Termin)
                                                            </span>
                                                            <span class="font-mono">Terbayar: Rp
                                                                {{ number_format($tgh->total_terbayar, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div
                                                            class="space-y-1 divide-y divide-amber-200/40 dark:divide-amber-800/40">
                                                            @foreach ($tgh->pembayaranIkhtibars as $ccl)
                                                                <div
                                                                    class="flex items-center justify-between pt-1 text-zinc-700 dark:text-zinc-300">
                                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                                        <span
                                                                            class="font-black text-amber-600 dark:text-amber-400">Termin
                                                                            {{ $ccl->termin_ke }}:</span>
                                                                        <span class="font-mono font-bold">Rp
                                                                            {{ number_format($ccl->nominal_bayar, 0, ',', '.') }}</span>
                                                                        <span
                                                                            class="text-[9px] text-zinc-400 font-mono">({{ date('d/m/Y', strtotime($ccl->tanggal_bayar)) }}
                                                                            via {{ $ccl->metode_pembayaran }})</span>
                                                                        @if ($ccl->metode_pembayaran === 'Potong Tabungan')
                                                                            <button type="button"
                                                                                onclick="toggleVerifikasiTabungan(this, '{{ route('tagihan-ikhtibar.toggle-verifikasi-tabungan', $ccl->id) }}')"
                                                                                class="px-1.5 py-0.5 rounded text-[8.5px] font-bold cursor-pointer transition-colors {{ $ccl->is_verified ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse' }}"
                                                                                title="Klik untuk ubah status verifikasi potongan tabungan">
                                                                                <i
                                                                                    class="bi {{ $ccl->is_verified ? 'bi-patch-check-fill' : 'bi-exclamation-circle' }}"></i>
                                                                                <span>{{ $ccl->is_verified ? 'Terkonfirmasi' : 'Belum Konfirmasi' }}</span>
                                                                            </button>
                                                                            @if ($ccl->nomor_tabungan)
                                                                                <span
                                                                                    class="text-[8.5px] text-zinc-400 font-mono">
                                                                                    [{{ $ccl->nomor_tabungan }} •
                                                                                    {{ $ccl->nama_nasabah }}
                                                                                    ({{ $ccl->ruangan_nasabah }})
                                                                                    ]
                                                                                </span>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex items-center gap-1 shrink-0">
                                                                        <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi-cicilan', $ccl->id) }}"
                                                                            target="_blank"
                                                                            class="px-2 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 text-[9px] font-bold"
                                                                            title="Cetak Kwitansi Termin {{ $ccl->termin_ke }}">
                                                                            <i class="bi bi-printer"></i> Slip
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td
                                                class="py-3 px-3 text-right font-mono font-bold text-xs text-zinc-800 dark:text-zinc-200">
                                                Rp {{ number_format($tgh->nominal_tagihan, 0, ',', '.') }}
                                            </td>
                                            <td
                                                class="py-3 px-3 text-right font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($tgh->total_terbayar ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td
                                                class="py-3 px-3 text-right font-mono font-black text-xs text-rose-600 dark:text-rose-400">
                                                Rp {{ number_format($tgh->sisa_tagihan, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                @php
                                                    $hasPendingTabungan = $tgh->pembayaranIkhtibars->where('metode_pembayaran', 'Potong Tabungan')->where('is_verified', false)->isNotEmpty();
                                                @endphp
                                                @if ($hasPendingTabungan)
                                                    <span
                                                        class="px-2.5 py-1 rounded-full text-[9px] font-black bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 inline-flex items-center gap-1"
                                                        title="Potongan tabungan manual belum dikonfirmasi admin">
                                                        <i class="bi bi-exclamation-circle-fill"></i> MENUNGGU VERIFIKASI
                                                    </span>
                                                @elseif ($tgh->status_bayar === 'Lunas')
                                                    <span
                                                        class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                        LUNAS
                                                    </span>
                                                @elseif ($tgh->status_bayar === 'Cicilan')
                                                    <span
                                                        class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                        CICILAN ({{ $tgh->pembayaranIkhtibars->count() }}/3)
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                        BELUM LUNAS
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                @if ($tgh->status_bayar === 'Lunas')
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $tgh->id) }}"
                                                            target="_blank"
                                                            class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors"
                                                            title="Cetak Kwitansi Pelunasan">
                                                            <i class="bi bi-printer text-xs"></i>
                                                        </a>
                                                        <form action="{{ route('tagihan-ikhtibar.batal', $tgh->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pembayaran ini?');"
                                                            class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                                title="Batalkan Pembayaran">
                                                                <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif ($tgh->status_bayar === 'Cicilan')
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $tgh->id) }}"
                                                            target="_blank"
                                                            class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors"
                                                            title="Cetak Kwitansi Riwayat">
                                                            <i class="bi bi-printer text-xs"></i>
                                                        </a>
                                                        <form action="{{ route('tagihan-ikhtibar.batal', $tgh->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Batalkan cicilan terakhir (Termin {{ $tgh->pembayaranIkhtibars->count() }})?');"
                                                            class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors"
                                                                title="Batalkan Cicilan Terakhir">
                                                                <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-[11px] text-zinc-400 italic">Siap Bayar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-6 text-center text-zinc-400 italic">
                                                Belum ada tagihan Iuran Ikhtibar yang diterbitkan untuk Wali Murid ini
                                                pada tahun pelajaran ini.
                                                <div class="mt-2">
                                                    <a href="{{ route('tagihan-ikhtibar.terbitkan-index', ['tahun_id' => $tahunPelajaranId, 'search' => $waliTerpilih->nama_kepala_keluarga]) }}"
                                                        class="m3-btn-secondary text-xs px-3 py-1">
                                                        Terbitkan Sekarang
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @php
                            $adaBelumLunas = $semuaTagihan->where('status_bayar', '!=', 'Lunas')->isNotEmpty();
                        @endphp

                        @if ($adaBelumLunas)
                            <!-- FORM BAYAR -->
                            <form action="{{ route('tagihan-ikhtibar.bayar') }}" method="POST" id="formBayarIkhtibar">
                                @csrf
                                <div id="hiddenTagihanIdsContainer"></div>

                                <!-- PANEL METODE & AKSI BAYAR -->
                                <div
                                    class="p-4 sm:p-5 rounded-2xl bg-zinc-50/80 dark:bg-black/30 border border-zinc-200/80 dark:border-zinc-800/80 space-y-4">
                                <!-- Pilihan 3 Metode Pembayaran -->
                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase tracking-wider text-zinc-500 mb-2">
                                        Pilih Metode Pembayaran
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <!-- Option 1: Tunai (Cash) -->
                                        <label
                                            class="metode-card cursor-pointer p-3 rounded-2xl border transition-all flex items-center gap-2.5 bg-white dark:bg-zinc-900/60 border-primary shadow-xs ring-2 ring-primary/20"
                                            id="cardOptTunai">
                                            <input type="radio" name="metode_pembayaran" value="Tunai"
                                                class="text-primary focus:ring-primary h-4 w-4" checked>
                                            <div>
                                                <span class="text-xs font-black text-zinc-900 dark:text-white block">💵
                                                    Tunai (Cash)</span>
                                                <span class="text-[10px] text-zinc-400 block">Bayar lunas langsung ke
                                                    kasir</span>
                                            </div>
                                        </label>

                                        <!-- Option 2: Potong Tabungan (Manual) -->
                                        <label
                                            class="metode-card cursor-pointer p-3 rounded-2xl border transition-all flex items-center gap-2.5 bg-white dark:bg-zinc-900/60 border-zinc-200 dark:border-zinc-800 hover:border-emerald-500/40"
                                            id="cardOptTabungan">
                                            <input type="radio" name="metode_pembayaran" value="Potong Tabungan"
                                                class="text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                            <div>
                                                <span class="text-xs font-black text-zinc-900 dark:text-white block">🏦
                                                    Potong Tabungan</span>
                                                <span class="text-[10px] text-zinc-400 block">Input data nasabah
                                                    manual</span>
                                            </div>
                                        </label>

                                        <!-- Option 3: Cicilan (Maksimal 3x) -->
                                        <label
                                            class="metode-card cursor-pointer p-3 rounded-2xl border transition-all flex items-center gap-2.5 bg-white dark:bg-zinc-900/60 border-zinc-200 dark:border-zinc-800 hover:border-amber-500/40"
                                            id="cardOptCicilan">
                                            <input type="radio" name="metode_pembayaran" value="Cicilan"
                                                class="text-amber-600 focus:ring-amber-500 h-4 w-4">
                                            <div>
                                                <span class="text-xs font-black text-zinc-900 dark:text-white block">📑
                                                    Cicilan (Maks 3x)</span>
                                                <span class="text-[10px] text-zinc-400 block">Pembayaran
                                                    bertahap</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- PANEL KHUSUS: POTONG TABUNGAN MANUAL -->
                                <div id="panelPotongTabunganManual"
                                    class="hidden p-4 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 space-y-3">
                                    <div class="flex items-center justify-between pb-2 border-b border-emerald-500/20">
                                        <h4
                                            class="text-xs font-black text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                            <i class="bi bi-wallet2 text-emerald-600"></i> Data Pemotongan Tabungan
                                            Santri (Protokol Manual)
                                        </h4>
                                        <span
                                            class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950 px-2 py-0.5 rounded-full">
                                            Manual Verification
                                        </span>
                                    </div>

                                    @if (!empty($daftarAnakKk) && $daftarAnakKk->isNotEmpty())
                                        <!-- Selector Rekomendasi Cepat Santri KK -->
                                        <div>
                                            <label
                                                class="block text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-400 mb-1">
                                                Pilih Santri KK Ini (Isi Otomatis Data Nasabah)
                                            </label>
                                            <select id="selectQuickSantri"
                                                class="m3-input-glass w-full text-xs font-bold bg-white dark:bg-zinc-900 cursor-pointer">
                                                <option value="">-- Pilih Santri untuk Isi Otomatis --</option>
                                                @foreach ($daftarAnakKk as $ank)
                                                    <option value="{{ $ank['id'] }}"
                                                        data-nama="{{ $ank['nama'] }}"
                                                        data-ruangan="{{ $ank['ruangan'] }}"
                                                        data-tabungan="{{ $ank['nomor_tabungan'] }}">
                                                        {{ $ank['nama'] }} ({{ $ank['ruangan'] }}) • No. Tabungan:
                                                        {{ $ank['nomor_tabungan'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label
                                                class="block text-[10px] font-black uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                                Nomor Tabungan / Rekening <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="nomor_tabungan" id="inputNoTabungan"
                                                placeholder="Contoh: TAB-00123"
                                                class="m3-input-glass w-full text-xs font-bold font-mono">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[10px] font-black uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                                Nama Santri / Nasabah <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="nama_nasabah" id="inputNamaNasabah"
                                                placeholder="Nama lengkap santri"
                                                class="m3-input-glass w-full text-xs font-bold">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[10px] font-black uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                                Ruangan / Kelas Santri <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="ruangan_nasabah" id="inputRuanganNasabah"
                                                placeholder="Contoh: 3 Ula A"
                                                class="m3-input-glass w-full text-xs font-bold">
                                        </div>
                                    </div>

                                    <div class="pt-1 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="is_verified" value="1"
                                                id="checkIsVerified"
                                                class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 cursor-pointer">
                                            <label for="checkIsVerified"
                                                class="text-xs font-bold text-emerald-900 dark:text-emerald-200 cursor-pointer">
                                                Tandai Langsung Sebagai Terkonfirmasi / Disetujui Admin Tabungan
                                            </label>
                                        </div>
                                        <p class="text-[9.5px] text-zinc-500 dark:text-zinc-400 italic">
                                            *Jika dibiarkan kosong (tidak dicentang), transaksi berstatus
                                            <strong>Pending Konfirmasi</strong> dan <strong>tidak akan dihitung ke Kas
                                                Masuk di Dashboard</strong> sampai disetujui.
                                        </p>
                                    </div>
                                </div>

                                <!-- PANEL KHUSUS: CICILAN (MAKSIMAL 3x) -->
                                <div id="panelCicilanMaks3"
                                    class="hidden p-4 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 space-y-3.5">
                                    <div class="flex items-center justify-between pb-2 border-b border-amber-500/20">
                                        <h4
                                            class="text-xs font-black text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                            <i class="bi bi-pie-chart-fill text-amber-600"></i> Skema Pembayaran
                                            Cicilan (Maksimal 3x Termin)
                                        </h4>
                                        <span id="badgeTerminKe"
                                            class="text-[10px] font-black text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950 px-2.5 py-0.5 rounded-full">
                                            Termin 1 dari 3
                                        </span>
                                    </div>

                                    <!-- Indikator 3 Termin Visual -->
                                    <div class="grid grid-cols-3 gap-2 text-center text-[10px] font-bold">
                                        <div id="stepTermin1"
                                            class="p-2 rounded-xl border border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200">
                                            <span class="block font-black">Cicilan 1</span>
                                            <span class="text-[9px] text-zinc-500 dark:text-zinc-400"
                                                id="stepTermin1Text">Aktif</span>
                                        </div>
                                        <div id="stepTermin2"
                                            class="p-2 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-400">
                                            <span class="block font-black">Cicilan 2</span>
                                            <span class="text-[9px]" id="stepTermin2Text">Menunggu</span>
                                        </div>
                                        <div id="stepTermin3"
                                            class="p-2 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-400">
                                            <span class="block font-black">Cicilan 3 (Terakhir)</span>
                                            <span class="text-[9px]" id="stepTermin3Text">Pelunasan</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <!-- Sub-Metode Bayar untuk Cicilan Ini -->
                                        <div>
                                            <label
                                                class="block text-[10px] font-black uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                                Metode Setor Cicilan Ini
                                            </label>
                                            <select name="sub_metode_cicilan" id="selectSubMetodeCicilan"
                                                class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                                                <option value="Tunai">💵 Tunai (Cash ke Kasir)</option>
                                                <option value="Potong Tabungan">🏦 Potong Tabungan Santri (Manual)
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Input Nominal Cicilan -->
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label
                                                    class="text-[10px] font-black uppercase text-zinc-600 dark:text-zinc-400">
                                                    Nominal Cicilan Ini (Rp) <span class="text-rose-500">*</span>
                                                </label>
                                                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-mono"
                                                    id="labelSisaTagihan">Sisa: Rp 0</span>
                                            </div>
                                            <input type="number" name="nominal_cicilan" id="inputNominalCicilan"
                                                min="1000" step="1000"
                                                class="m3-input-glass w-full text-sm font-black font-mono">
                                            <!-- Preset Cepat Nominal -->
                                            <div class="flex items-center gap-1.5 mt-1.5">
                                                <button type="button" id="btnBagi3"
                                                    class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-zinc-200/60 dark:bg-zinc-800 hover:bg-zinc-300 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                                    Bagi 3
                                                </button>
                                                <button type="button" id="btnBagi2"
                                                    class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-zinc-200/60 dark:bg-zinc-800 hover:bg-zinc-300 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                                    Bagi 2
                                                </button>
                                                <button type="button" id="btnLunasiSemua"
                                                    class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 hover:bg-amber-500/30 cursor-pointer">
                                                    Lunasi Sisa
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <p id="alertTermin3"
                                        class="hidden text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-500/10 p-2.5 rounded-xl border border-amber-500/20">
                                        <i class="bi bi-info-circle-fill"></i> Karena ini adalah cicilan ke-3 (Termin
                                        Terakhir), nominal pembayaran otomatis dikunci sebesar sisa tagihan untuk
                                        melunasi tanggungan.
                                    </p>
                                </div>

                                <!-- Tanggal & Catatan -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-zinc-500 mb-1">
                                            Tanggal Pembayaran
                                        </label>
                                        <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-zinc-500 mb-1">
                                            Catatan Pembayaran (Opsional)
                                        </label>
                                        <input type="text" name="catatan"
                                            placeholder="Keterangan pembayaran / nama penyetor..."
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>

                                <!-- Footer Bayar -->
                                <div
                                    class="flex items-center justify-between pt-2 border-t border-zinc-200 dark:border-zinc-800">
                                    <div>
                                        <span class="text-[10px] uppercase text-zinc-400 block font-bold">Total yang
                                            Disetor</span>
                                        <span class="text-xl font-black text-primary" id="totalBayarDisplay">Rp
                                            0</span>
                                    </div>
                                    <button type="submit" id="btnSubmitBayar"
                                        class="m3-btn-primary px-6 py-2.5 text-xs font-black shadow-lg cursor-pointer">
                                        <i class="bi bi-check2-circle text-base"></i>
                                        <span id="btnSubmitText">Proses Pelunasan Tunai</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- RIWAYAT TRANSAKSI HARI INI -->
    <div class="mt-8 m3-glass-card p-5 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
        <div class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800 mb-4">
            <h3 class="text-sm font-black text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                <span>Riwayat Transaksi Iuran Ikhtibar Hari Ini</span>
            </h3>
            <span class="text-[10px] font-bold text-zinc-400 uppercase">
                {{ date('d F Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr
                        class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 uppercase tracking-wider text-[10px] font-black">
                        <th class="py-2.5 px-3">No. Transaksi</th>
                        <th class="py-2.5 px-3">Wali Murid</th>
                        <th class="py-2.5 px-3">Metode</th>
                        <th class="py-2.5 px-3 text-right">Nominal</th>
                        <th class="py-2.5 px-3">Catatan</th>
                        <th class="py-2.5 px-3 text-center">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                    @forelse ($riwayatHariIni as $trx)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="py-3 px-3 font-mono font-bold text-primary">
                                {{ $trx->no_transaksi }}
                            </td>
                            <td class="py-3 px-3 font-bold text-zinc-900 dark:text-white">
                                {{ $trx->nama_pembayar }}
                            </td>
                            <td class="py-3 px-3">
                                <span
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    {{ $trx->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-black text-zinc-900 dark:text-white">
                                Rp {{ number_format($trx->total_nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-zinc-500 text-[11px] truncate max-w-xs">
                                {{ $trx->catatan ?: '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @php
                                    $tagihanTerkait = \App\Models\TagihanWaliMurid::where(
                                        'pembayaran_tagihan_id',
                                        $trx->id,
                                    )->first();
                                @endphp
                                @if ($tagihanTerkait)
                                    <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $tagihanTerkait->id) }}"
                                        target="_blank"
                                        class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors inline-block"
                                        title="Cetak Kwitansi">
                                        <i class="bi bi-printer text-xs"></i>
                                    </a>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-zinc-400 italic">
                                Belum ada transaksi pembayaran iuran Ikhtibar yang tercatat hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SCRIPT HITUNG TOTAL & ALGORITMA 3 METODE PEMBAYARAN -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.tagihan-checkbox');
            const totalDisplay = document.getElementById('totalBayarDisplay');
            const btnSubmit = document.getElementById('btnSubmitBayar');
            const btnSubmitText = document.getElementById('btnSubmitText');

            // 3 Metode Elements
            const radioMetodes = document.querySelectorAll('input[name="metode_pembayaran"]');
            const cardTunai = document.getElementById('cardOptTunai');
            const cardTabungan = document.getElementById('cardOptTabungan');
            const cardCicilan = document.getElementById('cardOptCicilan');

            const panelTabunganManual = document.getElementById('panelPotongTabunganManual');
            const panelCicilan = document.getElementById('panelCicilanMaks3');

            // Tabungan Manual Fields
            const selectQuickSantri = document.getElementById('selectQuickSantri');
            const inputNoTabungan = document.getElementById('inputNoTabungan');
            const inputNamaNasabah = document.getElementById('inputNamaNasabah');
            const inputRuanganNasabah = document.getElementById('inputRuanganNasabah');

            // Cicilan Elements
            const selectSubMetodeCicilan = document.getElementById('selectSubMetodeCicilan');
            const inputNominalCicilan = document.getElementById('inputNominalCicilan');
            const labelSisaTagihan = document.getElementById('labelSisaTagihan');
            const badgeTerminKe = document.getElementById('badgeTerminKe');
            const alertTermin3 = document.getElementById('alertTermin3');
            const btnBagi3 = document.getElementById('btnBagi3');
            const btnBagi2 = document.getElementById('btnBagi2');
            const btnLunasiSemua = document.getElementById('btnLunasiSemua');

            const step1 = document.getElementById('stepTermin1');
            const step2 = document.getElementById('stepTermin2');
            const step3 = document.getElementById('stepTermin3');
            const step1Text = document.getElementById('stepTermin1Text');
            const step2Text = document.getElementById('stepTermin2Text');
            const step3Text = document.getElementById('stepTermin3Text');

            // Handle Quick Select Santri
            if (selectQuickSantri) {
                selectQuickSantri.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (opt && opt.value) {
                        if (inputNamaNasabah) inputNamaNasabah.value = opt.getAttribute('data-nama') || '';
                        if (inputRuanganNasabah) inputRuanganNasabah.value = opt.getAttribute(
                            'data-ruangan') || '';
                        if (inputNoTabungan) inputNoTabungan.value = opt.getAttribute('data-tabungan') ||
                            '';
                    }
                });
            }

            function getSelectedTagihanInfo() {
                let totalSisa = 0;
                let firstCheckbox = null;
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        totalSisa += parseFloat(cb.getAttribute('data-sisa') || 0);
                        if (!firstCheckbox) firstCheckbox = cb;
                    }
                });
                return {
                    totalSisa,
                    firstCheckbox
                };
            }

            function updateUI() {
                const selectedMetode = document.querySelector('input[name="metode_pembayaran"]:checked')?.value ||
                    'Tunai';
                const {
                    totalSisa,
                    firstCheckbox
                } = getSelectedTagihanInfo();

                // Reset Card Borders
                [cardTunai, cardTabungan, cardCicilan].forEach(card => {
                    if (card) {
                        card.classList.remove('border-primary', 'border-emerald-500', 'border-amber-500',
                            'ring-2', 'ring-primary/20', 'ring-emerald-500/20', 'ring-amber-500/20');
                        card.classList.add('border-zinc-200', 'dark:border-zinc-800');
                    }
                });

                // Tampilkan / Sembunyikan Panel sesuai metode
                if (selectedMetode === 'Tunai') {
                    if (cardTunai) {
                        cardTunai.classList.remove('border-zinc-200', 'dark:border-zinc-800');
                        cardTunai.classList.add('border-primary', 'ring-2', 'ring-primary/20');
                    }
                    if (panelTabunganManual) panelTabunganManual.classList.add('hidden');
                    if (panelCicilan) panelCicilan.classList.add('hidden');

                    if (totalDisplay) totalDisplay.textContent = 'Rp ' + totalSisa.toLocaleString('id-ID');
                    if (btnSubmitText) btnSubmitText.textContent = 'Proses Pelunasan Tunai';
                } else if (selectedMetode === 'Potong Tabungan') {
                    if (cardTabungan) {
                        cardTabungan.classList.remove('border-zinc-200', 'dark:border-zinc-800');
                        cardTabungan.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-500/20');
                    }
                    if (panelTabunganManual) panelTabunganManual.classList.remove('hidden');
                    if (panelCicilan) panelCicilan.classList.add('hidden');

                    if (totalDisplay) totalDisplay.textContent = 'Rp ' + totalSisa.toLocaleString('id-ID');
                    if (btnSubmitText) btnSubmitText.textContent = 'Proses Pelunasan Potong Tabungan';
                } else if (selectedMetode === 'Cicilan') {
                    if (cardCicilan) {
                        cardCicilan.classList.remove('border-zinc-200', 'dark:border-zinc-800');
                        cardCicilan.classList.add('border-amber-500', 'ring-2', 'ring-amber-500/20');
                    }
                    if (panelCicilan) panelCicilan.classList.remove('hidden');

                    // Cek Sub Metode Cicilan
                    const subMetode = selectSubMetodeCicilan ? selectSubMetodeCicilan.value : 'Tunai';
                    if (subMetode === 'Potong Tabungan') {
                        if (panelTabunganManual) panelTabunganManual.classList.remove('hidden');
                    } else {
                        if (panelTabunganManual) panelTabunganManual.classList.add('hidden');
                    }

                    // Tentukan termin ke berapa
                    const terminKe = firstCheckbox ? parseInt(firstCheckbox.getAttribute('data-termin') || 1) : 1;
                    const countLalu = firstCheckbox ? parseInt(firstCheckbox.getAttribute('data-count') || 0) : 0;

                    if (badgeTerminKe) {
                        badgeTerminKe.textContent = 'Termin ' + terminKe + ' dari Maksimal 3';
                    }
                    if (labelSisaTagihan) {
                        labelSisaTagihan.textContent = 'Sisa: Rp ' + totalSisa.toLocaleString('id-ID');
                    }

                    // Update Stepper Visual
                    [step1, step2, step3].forEach(st => {
                        if (st) {
                            st.className =
                                'p-2 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-400';
                        }
                    });

                    if (countLalu >= 1 && step1 && step1Text) {
                        step1.className =
                            'p-2 rounded-xl border border-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200';
                        step1Text.textContent = 'Lunas';
                    }
                    if (countLalu >= 2 && step2 && step2Text) {
                        step2.className =
                            'p-2 rounded-xl border border-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200';
                        step2Text.textContent = 'Lunas';
                    }

                    // Tandai step aktif
                    const activeStep = terminKe === 1 ? step1 : (terminKe === 2 ? step2 : step3);
                    const activeText = terminKe === 1 ? step1Text : (terminKe === 2 ? step2Text : step3Text);
                    if (activeStep && activeText) {
                        activeStep.className =
                            'p-2 rounded-xl border border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200';
                        activeText.textContent = 'Sedang Diproses';
                    }

                    // Jika Termin 3 (Terakhir), kunci ke sisa
                    if (terminKe >= 3) {
                        if (alertTermin3) alertTermin3.classList.remove('hidden');
                        if (inputNominalCicilan) {
                            inputNominalCicilan.value = totalSisa;
                            inputNominalCicilan.setAttribute('readonly', 'readonly');
                        }
                    } else {
                        if (alertTermin3) alertTermin3.classList.add('hidden');
                        if (inputNominalCicilan) {
                            inputNominalCicilan.removeAttribute('readonly');
                            if (!inputNominalCicilan.value || parseFloat(inputNominalCicilan.value) > totalSisa) {
                                inputNominalCicilan.value = Math.ceil(totalSisa / (4 - terminKe));
                            }
                        }
                    }

                    const nominalCicilanNow = parseFloat(inputNominalCicilan?.value || 0);
                    if (totalDisplay) totalDisplay.textContent = 'Rp ' + nominalCicilanNow.toLocaleString('id-ID');
                    if (btnSubmitText) btnSubmitText.textContent = 'Simpan Cicilan ke-' + terminKe;
                }
            }

            // Event Listeners
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateUI);
            });

            radioMetodes.forEach(radio => {
                radio.addEventListener('change', updateUI);
            });

            if (selectSubMetodeCicilan) {
                selectSubMetodeCicilan.addEventListener('change', updateUI);
            }

            if (inputNominalCicilan) {
                inputNominalCicilan.addEventListener('input', function() {
                    const val = parseFloat(this.value || 0);
                    if (totalDisplay) totalDisplay.textContent = 'Rp ' + val.toLocaleString('id-ID');
                });
            }

            // Preset Buttons
            if (btnBagi3) {
                btnBagi3.addEventListener('click', function() {
                    const {
                        totalSisa
                    } = getSelectedTagihanInfo();
                    if (inputNominalCicilan) {
                        inputNominalCicilan.value = Math.ceil(totalSisa / 3);
                        inputNominalCicilan.dispatchEvent(new Event('input'));
                    }
                });
            }
            if (btnBagi2) {
                btnBagi2.addEventListener('click', function() {
                    const {
                        totalSisa
                    } = getSelectedTagihanInfo();
                    if (inputNominalCicilan) {
                        inputNominalCicilan.value = Math.ceil(totalSisa / 2);
                        inputNominalCicilan.dispatchEvent(new Event('input'));
                    }
                });
            }
            if (btnLunasiSemua) {
                btnLunasiSemua.addEventListener('click', function() {
                    const {
                        totalSisa
                    } = getSelectedTagihanInfo();
                    if (inputNominalCicilan) {
                        inputNominalCicilan.value = totalSisa;
                        inputNominalCicilan.dispatchEvent(new Event('input'));
                    }
                });
            }

            const formBayar = document.getElementById('formBayarIkhtibar');
            if (formBayar) {
                formBayar.addEventListener('submit', function(e) {
                    const container = document.getElementById('hiddenTagihanIdsContainer');
                    if (container) {
                        container.innerHTML = '';
                        let hasChecked = false;
                        checkboxes.forEach(cb => {
                            if (cb.checked) {
                                hasChecked = true;
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'tagihan_ids[]';
                                hidden.value = cb.value;
                                container.appendChild(hidden);
                            }
                        });

                        if (!hasChecked) {
                            e.preventDefault();
                            alert('Silakan pilih minimal 1 tagihan untuk dibayarkan.');
                            return false;
                        }
                    }
                });
            }

            updateUI();
        });
    </script>
</x-app-layout>
