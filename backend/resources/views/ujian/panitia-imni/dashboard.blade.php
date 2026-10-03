@section('title', 'Dashboard Kepanitiaan IMNI')
<x-app-layout>

    <!-- 1. Header Toolbar & Filter Tahun Pelajaran -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
        <div>
            <div class="flex items-center gap-2.5">
                <div
                    class="w-10 h-10 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-xl shrink-0 border border-primary/20">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                        Dashboard IMNI
                    </h2>
                    <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Pusat Kendali & Monitoring Terpadu Imtihan Nihai (IMNI) Murid Kelas Akhir
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Filter Form Tahun Pelajaran -->
            <form action="{{ route('dashboard-imni.index') }}" method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <select name="tahun_id" onchange="this.form.submit()"
                        class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                        @foreach ($daftarTahun as $th)
                            <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                Th. Pelajaran {{ $th->nama_hijriyah }} H ({{ $th->nama_masehi }} M)
                                {{ $th->is_active ? '★ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <i
                        class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                </div>
            </form>

            <x-button :href="route('panitia-imni.index')" variant="primary" size="sm" icon="bi-people-fill">
                <span>Susunan Panitia</span>
            </x-button>
        </div>
    </div>

    <!-- Alert Banner / Info Toast jika ada redirect placeholder -->
    @if (session('info'))
        <div
            class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex items-center justify-between gap-3 border-l-4 border-l-blue-500 bg-blue-500/5">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 text-base border border-blue-500/20">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <p class="text-xs font-bold text-blue-700 dark:text-blue-300">
                    {{ session('info') }}
                </p>
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- 2. SK & IDENTITAS KEPANITIAAN IMNI TAHUN INI -->
    <div class="mb-6">
        @if ($ketua || $bendahara || $anggota->isNotEmpty())
            <div
                class="m3-glass-card p-5 md:p-6 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-zinc-100 dark:border-zinc-800/80">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0 border border-amber-500/20">
                            <i class="bi bi-file-earmark-person-fill"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3
                                    class="text-sm md:text-base font-black text-zinc-900 dark:text-white uppercase tracking-wider">
                                    Struktur Kepanitiaan IMNI {{ $selectedTahun->nama_hijriyah }} H /
                                    {{ $selectedTahun->nama_masehi }} M
                                </h3>
                                <span
                                    class="px-2 py-0.5 text-[10px] font-black rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    {{ $totalPanitiaAktif }} Personel Aktif
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                                Surat Keputusan (SK) Pengangkatan Panitia Imtihan Nihai & Ujian Kelulusan MDT Hidayatus
                                Shibyan
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-button :href="route('panitia-imni.index')" variant="outline" size="sm" icon="bi-pencil-square">
                            <span>Kelola Personel</span>
                        </x-button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                    <!-- KETUA PANITIA -->
                    <div
                        class="p-4 rounded-2xl bg-zinc-50/80 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60 flex items-center gap-3.5">
                        <div
                            class="w-12 h-12 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-xl shrink-0 overflow-hidden border border-primary/20">
                            @if ($ketua && $ketua->ustadz && $ketua->ustadz->foto)
                                <img src="{{ asset('storage/' . $ketua->ustadz->foto) }}" alt="Foto Ketua"
                                    class="w-full h-full object-cover">
                            @else
                                <i class="bi bi-person-fill"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <span
                                class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-primary dark:text-primary-dark">
                                <i class="bi bi-star-fill text-[8px]"></i> Ketua Panitia
                            </span>
                            <h4 class="text-xs md:text-sm font-black text-zinc-900 dark:text-white truncate"
                                title="{{ $ketua?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}">
                                {{ $ketua?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}
                            </h4>
                            <p class="text-[11px] font-mono font-semibold text-zinc-500 dark:text-zinc-400 truncate">
                                {{ $ketua?->no_sk ?? 'NIGM: ' . ($ketua?->ustadz?->nigm ?? '-') }}
                            </p>
                        </div>
                    </div>

                    <!-- BENDAHARA PANITIA -->
                    <div
                        class="p-4 rounded-2xl bg-zinc-50/80 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60 flex items-center gap-3.5">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0 overflow-hidden border border-emerald-500/20">
                            @if ($bendahara && $bendahara->ustadz && $bendahara->ustadz->foto)
                                <img src="{{ asset('storage/' . $bendahara->ustadz->foto) }}" alt="Foto Bendahara"
                                    class="w-full h-full object-cover">
                            @else
                                <i class="bi bi-cash-stack"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <span
                                class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                <i class="bi bi-wallet-fill text-[8px]"></i> Bendahara Panitia
                            </span>
                            <h4 class="text-xs md:text-sm font-black text-zinc-900 dark:text-white truncate"
                                title="{{ $bendahara?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}">
                                {{ $bendahara?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}
                            </h4>
                            <p class="text-[11px] font-mono font-semibold text-zinc-500 dark:text-zinc-400 truncate">
                                {{ $bendahara?->no_sk ?? 'NIGM: ' . ($bendahara?->ustadz?->nigm ?? '-') }}
                            </p>
                        </div>
                    </div>

                    <!-- ANGGOTA PANITIA -->
                    <div
                        class="p-4 rounded-2xl bg-zinc-50/80 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60 flex items-center gap-3.5">
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shrink-0 border border-blue-500/20">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span
                                class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                <i class="bi bi-check-circle-fill text-[8px]"></i> Anggota Panitia
                            </span>
                            <h4 class="text-xs md:text-sm font-black text-zinc-900 dark:text-white">
                                {{ $anggota->count() }} Anggota Terdaftar
                            </h4>
                            <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 truncate"
                                title="{{ $anggota->pluck('ustadz.nama_lengkap')->implode(', ') ?: 'Belum ada anggota' }}">
                                {{ $anggota->pluck('ustadz.nama_lengkap')->implode(', ') ?: 'Belum ada anggota terdaftar' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Warning Banner jika belum ada SK -->
            <div
                class="p-5 md:p-6 m3-glass-card rounded-2xl md:rounded-3xl border-l-4 border-l-amber-500 bg-amber-500/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 border border-amber-500/20">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider">
                            Susunan Kepanitiaan IMNI Belum Ditetapkan
                        </h4>
                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Tahun Pelajaran {{ $selectedTahun->nama_hijriyah }} H ({{ $selectedTahun->nama_masehi }}
                            M) belum memiliki SK Kepanitiaan IMNI resmi.
                        </p>
                    </div>
                </div>
                <x-button :href="route('panitia-imni.create')" variant="primary" size="sm" icon="bi-plus-circle-fill">
                    <span>Tetapkan Panitia Sekarang</span>
                </x-button>
            </div>
        @endif
    </div>

    <!-- 3. GRID 4 STATISTIK UTAMA (METRICS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Kandidat Murid Kelas Akhir -->
        <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
            <div class="flex justify-between items-start mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                    Kandidat Peserta IMNI
                </span>
                <div
                    class="w-9 h-9 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-base shrink-0 border border-primary/20">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </div>
            <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight font-mono">
                {{ $totalKandidatImni }} <span class="text-xs font-bold text-zinc-400">Murid</span>
            </h3>
            <div
                class="flex items-center gap-1.5 mt-2.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400 flex-wrap">
                <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $countMuridTpq }} TPQ</span>
                <span>•</span>
                <span class="text-orange-500 font-extrabold">{{ $countMuridIbt }} IBT</span>
                <span>•</span>
                <span class="text-blue-500 font-extrabold">{{ $countMuridTsa }} TSA</span>
            </div>
        </div>

        <!-- Card 2: Peserta Terdaftar IMNI -->
        <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
            <div class="flex justify-between items-start mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                    Peserta Terdaftar IMNI
                </span>
                <div
                    class="w-9 h-9 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base shrink-0 border border-blue-500/20">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
            <h3 class="text-xl md:text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight font-mono">
                {{ $totalPesertaTerdaftar }} <span class="text-xs font-bold text-zinc-400">Peserta</span>
            </h3>
            <div class="flex items-center gap-2 mt-2.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $totalPesertaLayak }} Layak
                    Ujian</span>
                <span>•</span>
                <span>{{ $totalPesertaTerplotting }} Terplotting</span>
            </div>
        </div>

        <!-- Card 3: Pemasukan / Tagihan IMNI -->
        <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
            <div class="flex justify-between items-start mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                    Estimasi Kas Masuk
                </span>
                <div
                    class="w-9 h-9 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base shrink-0 border border-emerald-500/20">
                    <i class="bi bi-credit-card-fill"></i>
                </div>
            </div>
            <h3 class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">
                Rp {{ number_format($keuangan->total_pemasukan, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] font-bold text-zinc-400 mt-2.5">
                Pembayaran administrasi IMNI murid
            </p>
        </div>

        <!-- Card 4: Pengeluaran & Sisa Saldo Panitia -->
        <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
            <div class="flex justify-between items-start mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                    Sisa Saldo Kas Panitia
                </span>
                <div
                    class="w-9 h-9 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base shrink-0 border border-indigo-500/20">
                    <i class="bi bi-safe2-fill"></i>
                </div>
            </div>
            <h3 class="text-xl md:text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight font-mono">
                Rp {{ number_format($keuangan->saldo_kas, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] font-bold text-zinc-400 mt-2.5">
                Kas masuk dikurangi biaya operasional
            </p>
        </div>
    </div>

    <!-- 4. PINTASAN AKSI CEPAT / 7 MODUL OPERASIONAL IMNI -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-2">
                <i class="bi bi-grid-fill text-primary text-sm"></i>
                <h3 class="text-xs md:text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider">
                    Modul Operasional Kepanitiaan IMNI
                </h3>
            </div>
            <span class="text-[11px] font-bold text-zinc-400">
                {{ count($quickLinks) }} Modul Terintegrasi
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @foreach ($quickLinks as $link)
                <a href="{{ $link['route'] }}"
                    class="m3-glass-card p-4 rounded-2xl md:rounded-3xl relative overflow-hidden group hover:border-primary/50 transition-all duration-300 flex flex-col justify-between block shadow-2xs">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div
                                class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 flex items-center justify-center text-lg shrink-0 group-hover:bg-primary group-hover:text-white transition-colors">
                                <i class="bi {{ $link['icon'] }}"></i>
                            </div>
                            <span
                                class="px-2 py-0.5 text-[9px] font-black rounded-full uppercase tracking-wider
                                @if ($link['status'] === 'Selesai' || $link['status'] === 'Terintegrasi') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                @elseif($link['status'] === 'Aktif')
                                    bg-primary/10 text-primary dark:text-primary-dark border border-primary/20
                                @else
                                    bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border border-zinc-500/20 @endif">
                                {{ $link['status'] }}
                            </span>
                        </div>
                        <h4
                            class="text-xs md:text-sm font-black text-zinc-900 dark:text-white group-hover:text-primary transition-colors">
                            {{ $link['title'] }}
                        </h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2">
                            {{ $link['description'] }}
                        </p>
                    </div>
                    <div
                        class="pt-3 mt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-[11px] font-bold text-zinc-400 group-hover:text-primary">
                        <span class="truncate font-mono">{{ $link['badge'] }}</span>
                        <i class="bi bi-arrow-right transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 5. CONTENT DUA KOLOM: MONITORING UJIAN AL-QUR'AN & DISTRIBUSI KELAS AKHIR -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- KOLOM KIRI (2 SPAN): Monitoring Ujian Al-Qur'an & Susunan Panitia -->
        <!-- KOLOM KIRI (2 SPAN): Status Kesiapan Peserta & Personel Panitia -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Widget 1: Status Kesiapan Peserta & Ruangan IMNI -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="p-4 sm:p-5 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex justify-between items-center">
                    <span
                        class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="bi bi-mortarboard-fill text-primary text-sm"></i>
                        Status Kesiapan Peserta & Ruangan IMNI
                    </span>
                    <x-button :href="route('peserta-imni.index')" variant="text" size="sm" icon="bi-arrow-right"
                        icon-position="right">
                        Kelola Peserta
                    </x-button>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                        <div
                            class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60">
                            <span class="text-[10px] font-black uppercase text-zinc-400">Total Kandidat Kelas
                                Akhir</span>
                            <h5 class="text-xs md:text-sm font-black text-zinc-900 dark:text-white mt-0.5">
                                {{ $totalKandidatImni }} Murid
                            </h5>
                        </div>
                        <div
                            class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60">
                            <span class="text-[10px] font-black uppercase text-zinc-400">Peserta Siap Ujian</span>
                            <h5 class="text-xs md:text-sm font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                                {{ $totalPesertaLayak }} Murid Layak
                            </h5>
                        </div>
                        <div
                            class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60">
                            <span class="text-[10px] font-black uppercase text-zinc-400">Plotting Ruangan Ujian</span>
                            <h5 class="text-xs md:text-sm font-black text-blue-600 dark:text-blue-400 mt-0.5">
                                {{ $totalPesertaTerplotting }} / {{ $totalPesertaTerdaftar }} Terplotting
                            </h5>
                        </div>
                    </div>

                    <!-- Progress Bar Plotting Ruangan -->
                    @php
                        $persenPlotting =
                            $totalPesertaTerdaftar > 0
                                ? round(($totalPesertaTerplotting / $totalPesertaTerdaftar) * 100, 1)
                                : 0;
                    @endphp
                    <div class="mb-2">
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="text-zinc-600 dark:text-zinc-400">Progres Plotting Ruangan & Nomor
                                Peserta</span>
                            <span class="text-primary dark:text-primary-dark font-mono">{{ $persenPlotting }}%</span>
                        </div>
                        <div class="w-full h-3 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden flex">
                            <div class="bg-primary h-full transition-all duration-500"
                                style="width: {{ $persenPlotting }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-[11px] font-bold text-zinc-400">
                            <span>{{ $totalPesertaTerplotting }} Murid Mendapat Nomor Meja</span>
                            <span>{{ $totalPesertaTerdaftar - $totalPesertaTerplotting }} Belum Diplotting</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Personel Panitia IMNI Terdaftar -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="p-4 sm:p-5 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex justify-between items-center">
                    <span
                        class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="bi bi-people-fill text-primary text-sm"></i>
                        Daftar Personel Panitia IMNI
                    </span>
                    <x-button :href="route('panitia-imni.index')" variant="text" size="sm" icon="bi-arrow-right"
                        icon-position="right">
                        Lihat Semua
                    </x-button>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    @forelse ($panitiaAll as $panitia)
                        <div
                            class="p-4 flex items-center justify-between gap-3 hover:bg-zinc-50/50 dark:hover:bg-zinc-900/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-sm font-bold shrink-0 border border-zinc-200/60 dark:border-zinc-800">
                                    @if ($panitia->ustadz && $panitia->ustadz->foto)
                                        <img src="{{ asset('storage/' . $panitia->ustadz->foto) }}" alt="Foto"
                                            class="w-full h-full object-cover rounded-2xl">
                                    @else
                                        {{ substr($panitia->ustadz?->nama_lengkap ?? 'P', 0, 2) }}
                                    @endif
                                </div>
                                <div>
                                    <h5 class="text-xs md:text-sm font-black text-zinc-900 dark:text-white">
                                        {{ $panitia->ustadz?->nama_lengkap ?? '-' }}
                                    </h5>
                                    <p class="text-[11px] font-mono text-zinc-400">
                                        NIGM: {{ $panitia->ustadz?->nigm ?? '-' }} • No SK:
                                        {{ $panitia->no_sk ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2.5 py-1 text-[10px] font-black rounded-full uppercase tracking-wider
                                    @if ($panitia->jabatan === 'Ketua') bg-primary/10 text-primary dark:text-primary-dark border border-primary/20
                                    @elseif($panitia->jabatan === 'Bendahara')
                                        bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                    @else
                                        bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 @endif">
                                    {{ $panitia->jabatan }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-400 text-xs font-semibold">
                            Belum ada panitia terdaftar untuk tahun pelajaran ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (1 SPAN): Distribusi Ruangan Kelas Akhir & Alur Kerja -->
        <div class="space-y-6">

            <!-- Distribusi Murid Kelas Akhir -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="p-4 sm:p-5 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800">
                    <span
                        class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="bi bi-door-open-fill text-primary text-sm"></i>
                        Ruangan Murid Kelas Akhir
                    </span>
                </div>

                <div class="p-4 space-y-3">
                    @forelse ($ruangansAkhir as $ruangan)
                        <div
                            class="p-3 rounded-2xl bg-zinc-50/80 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60 flex items-center justify-between">
                            <div>
                                <span
                                    class="text-[10px] font-black uppercase text-primary dark:text-primary-dark tracking-wider block">
                                    {{ $ruangan->level?->nama_level ?? '-' }}
                                </span>
                                <h5 class="text-xs font-black text-zinc-900 dark:text-white">
                                    {{ $ruangan->nama_ruangan }}
                                </h5>
                                <p class="text-[10px] font-semibold text-zinc-400">
                                    Wali: {{ $ruangan->ustadz?->nama_lengkap ?? '-' }}
                                </p>
                            </div>
                            <span
                                class="px-2.5 py-1 text-[10px] font-bold rounded-xl bg-zinc-200/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-mono">
                                Kapasitas: {{ $ruangan->kapasitas }}
                            </span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-zinc-400 text-xs font-semibold">
                            Tidak ada ruangan kelas akhir di tahun ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Petunjuk Alur Kerja Panitia IMNI -->
            <div
                class="m3-glass-card p-5 rounded-2xl md:rounded-3xl shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
                <span
                    class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2 mb-3.5">
                    <i class="bi bi-diagram-3-fill text-primary text-sm"></i>
                    Alur Kerja Kepanitiaan IMNI
                </span>

                <ol class="relative border-l border-zinc-200 dark:border-zinc-800 ml-2.5 space-y-4">
                    <li class="ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border border-white dark:border-zinc-900 bg-emerald-500">
                        </div>
                        <h4 class="text-xs font-black text-zinc-900 dark:text-white">1. SK Panitia & Dashboard</h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Penetapan Ketua,
                            Bendahara & Anggota Panitia IMNI.</p>
                    </li>
                    <li class="ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border border-white dark:border-zinc-900 bg-blue-500">
                        </div>
                        <h4 class="text-xs font-black text-zinc-900 dark:text-white">2. Peserta & Ruangan</h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Penarikan murid akhir,
                            nomor peserta & kartu ujian.</p>
                    </li>
                    <li class="ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border border-white dark:border-zinc-900 bg-amber-500">
                        </div>
                        <h4 class="text-xs font-black text-zinc-900 dark:text-white">3. Pembayaran & Pengeluaran</h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Loket kasir pembayaran,
                            buku kas keluar & LPJ kas.</p>
                    </li>
                    <li class="ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border border-white dark:border-zinc-900 bg-indigo-500">
                        </div>
                        <h4 class="text-xs font-black text-zinc-900 dark:text-white">4. Presensi & Berita Acara</h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Presensi kehadiran
                            hari-H, DHPU & Berita Acara ujian.</p>
                    </li>
                    <li class="ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border border-white dark:border-zinc-900 bg-rose-500">
                        </div>
                        <h4 class="text-xs font-black text-zinc-900 dark:text-white">5. Input Nilai & Yudisium</h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Leger nilai mapel,
                            kalkulasi kelulusan & cetak Ijazah.</p>
                    </li>
                </ol>
            </div>

        </div>

    </div>

</x-app-layout>
