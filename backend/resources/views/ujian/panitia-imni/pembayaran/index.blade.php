@section('title', 'Pembayaran & Kasir IMNI')

<x-app-layout>
    <div>
        <!-- 1. HEADER SECTION -->
        <div
            class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Loket Pembayaran IMNI
                </h2>
                <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Penerimaan pembayaran administrasi ujian akhir murid, penerbitan kwitansi tanda terima, dan
                    monitoring piutang IMNI.
                </p>
            </div>

            <!-- Toolbar Aksi Utama -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('pembayaran-imni.index') }}" method="GET" id="formTahunPembayaran" class="m-0">
                    <div class="relative">
                        <select name="tahun_id" onchange="document.getElementById('formTahunPembayaran').submit()"
                            class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                    {{ $th->nama_hijriyah }} H | {{ $th->nama_masehi }} M
                                    {{ $th->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                    </div>
                </form>



                <!-- Tombol Cetak Rekapitulasi -->
                <a href="{{ route('pembayaran-imni.cetak-rekap', ['tahun_id' => $selectedTahunId, 'tingkat_id' => $selectedTingkatId, 'ruangan_ujian_id' => request('ruangan_ujian_id'), 'status_pembayaran' => request('status_pembayaran')]) }}"
                    target="_blank"
                    class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                    <i class="bi bi-printer-fill text-sm text-emerald-600"></i>
                    <span>Cetak Rekap Kasir</span>
                </a>


            </div>
        </div>

        <!-- BANNER PENGATURAN TAGIHAN TERKONFIGURASI -->
        @if (isset($daftarPengaturanTagihan) && $daftarPengaturanTagihan->isNotEmpty())
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex flex-wrap items-center justify-between gap-3 border border-indigo-500/30 bg-indigo-500/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 text-base border border-indigo-500/20">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-indigo-900 dark:text-indigo-300">
                            Tarif Administrasi IMNI dari Pengaturan Tagihan:
                        </h4>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            @foreach ($daftarPengaturanTagihan as $pt)
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white dark:bg-zinc-800 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20 shadow-2xs">
                                    {{ $pt->level?->nama_level ?? 'Semua Level' }}: Rp
                                    {{ number_format($pt->nominal, 0, ',', '.') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form action="{{ route('pembayaran-imni.terapkan-pengaturan') }}" method="POST"
                        class="ajax-post m-0" data-refresh-target="#data-table-container">
                        @csrf
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunId }}">
                        <button type="submit"
                            class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black flex items-center gap-1.5 shadow-sm transition-all active:scale-95 cursor-pointer">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Sinkronkan ke Seluruh Peserta</span>
                        </button>
                    </form>
                    <a href="{{ route('pengaturan-tagihan.index', ['tahun_id' => $selectedTahunId]) }}" target="_blank"
                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold border border-zinc-200 dark:border-zinc-700 transition-all">
                        <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                        <span>Ubah di Pengaturan</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- 2. ALERTS & NOTIFIKASI -->
        @if (session('success'))
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex items-center justify-between gap-3 border-l-4 border-l-emerald-500 bg-emerald-500/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-base border border-emerald-500/20">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        {{ session('success') }}
                    </p>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex items-center justify-between gap-3 border-l-4 border-l-rose-500 bg-rose-500/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 text-base border border-rose-500/20">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                    <p class="text-xs font-bold text-rose-700 dark:text-rose-300">
                        {{ session('error') }}
                    </p>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex items-center justify-between gap-3 border-l-4 border-l-amber-500 bg-amber-500/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-base border border-amber-500/20">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                        {{ session('warning') }}
                    </p>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        <!-- 3. STATISTIK METRIK KEUANGAN (4 STAT CARDS) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 md:mb-8">
            <!-- Card 1: Total Tagihan Terdata -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-primary/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Total Tagihan IMNI
                        </p>
                        <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white mt-1">
                            Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                        </h3>
                        <p class="text-[11px] font-semibold text-zinc-500 mt-1 flex items-center gap-1">
                            <span>{{ $allPesertaCount }} murid terdaftar</span>
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shrink-0 border border-blue-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Pembayaran Masuk (Realisasi) -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Total Terbayar (Kasir)
                        </p>
                        <h3 class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                            Rp {{ number_format($totalTerbayar, 0, ',', '.') }}
                        </h3>
                        <div
                            class="flex items-center gap-1.5 mt-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                            <i class="bi bi-check2-all"></i>
                            <span>{{ $persenLunas }}% dari target tagihan</span>
                        </div>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3: Sisa Piutang Administrasi -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Sisa Belum Lunas
                        </p>
                        <h3 class="text-xl md:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                            Rp {{ number_format($totalSisa, 0, ',', '.') }}
                        </h3>
                        <p class="text-[11px] font-semibold text-zinc-500 mt-1">
                            {{ $countBelumLunas }} murid belum melunasi
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 border border-amber-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>

            <!-- Card 4: Rangkuman Status Murid -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-purple-500/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Status Kelunasan Murid
                        </p>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                {{ $countLunas }} Lunas
                            </span>
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                {{ $countBelumLunas }} Blm
                            </span>
                            @if ($countDispensasi > 0)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                    {{ $countDispensasi }} Disp
                                </span>
                            @endif
                            @if ($countBelumAdaTagihan > 0)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black bg-zinc-500/10 text-zinc-500 border border-zinc-500/20">
                                    {{ $countBelumAdaTagihan }} Kosong
                                </span>
                            @endif
                        </div>
                        <p class="text-[10px] text-zinc-400 mt-1.5 font-medium">
                            Bendahara: {{ $bendaharaPanitia?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 border border-purple-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. FILTER & PENCARIAN -->
        <div class="m3-glass-card rounded-2xl md:rounded-3xl p-4 md:p-5 mb-6">
            <form action="{{ route('pembayaran-imni.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <input type="hidden" name="tahun_id" value="{{ $selectedTahunId }}">

                <!-- Filter Tingkat -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Tingkat Madrasah
                    </label>
                    <select name="tingkat_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        @foreach ($daftarTingkat as $tk)
                            <option value="{{ $tk->id }}"
                                {{ $selectedTingkatId == $tk->id ? 'selected' : '' }}>
                                {{ $tk->nama_tingkat }} ({{ $tk->kode_tingkat }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Ruangan Ujian -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Ruangan Ujian IMNI
                    </label>
                    <select name="ruangan_ujian_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">-- Semua Ruang Ujian IMNI --</option>
                        @foreach ($daftarRuangan as $rg)
                            <option value="{{ $rg->id }}"
                                {{ request('ruangan_ujian_id') == $rg->id ? 'selected' : '' }}>
                                {{ $rg->nama_ruangan }} ({{ $rg->level?->nama_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status Pembayaran -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Status Pembayaran
                    </label>
                    <select name="status_pembayaran" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="Lunas" {{ request('status_pembayaran') == 'Lunas' ? 'selected' : '' }}>Lunas
                        </option>
                        <option value="Belum Lunas"
                            {{ request('status_pembayaran') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="Dispensasi"
                            {{ request('status_pembayaran') == 'Dispensasi' ? 'selected' : '' }}>Dispensasi</option>
                        <option value="Belum Ada Tagihan"
                            {{ request('status_pembayaran') == 'Belum Ada Tagihan' ? 'selected' : '' }}>Belum Ada
                            Tagihan</option>
                    </select>
                </div>

                <!-- Input Pencarian -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Cari Murid / No. Kwitansi
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama / NISM / Kwitansi..."
                            class="w-full pl-8 pr-8 py-2 text-xs bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                        @if (request('search') || request('ruangan_ujian_id') || request('status_pembayaran'))
                            <a href="{{ route('pembayaran-imni.index', ['tahun_id' => $selectedTahunId, 'tingkat_id' => $selectedTingkatId]) }}"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-rose-500 text-xs"
                                title="Reset Filter">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- 5. TABEL DATA PEMBAYARAN IMNI -->
        <div id="data-table-container" class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr
                            class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 font-black text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-3 w-28">No. Peserta</th>
                            <th class="py-3.5 px-4">Nama Murid</th>
                            <th class="py-3.5 px-3">Ruangan</th>
                            <th class="py-3.5 px-3 text-right">Tagihan</th>
                            <th class="py-3.5 px-3 text-right">Terbayar</th>
                            <th class="py-3.5 px-3 text-right">Sisa Tagihan</th>
                            <th class="py-3.5 px-3 text-center">Status</th>
                            <th class="py-3.5 px-3">No. Kwitansi</th>
                            <th class="py-3.5 px-4 text-center w-40">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 text-zinc-700 dark:text-zinc-300">
                        @forelse ($pesertas as $index => $p)
                            @php
                                $bayar = $p->pembayaran;
                                $tagihan = (float) ($bayar?->nominal_tagihan ?? 0);
                                $terbayar = (float) ($bayar?->nominal_bayar ?? 0);
                                $sisa = (float) ($bayar?->sisa_tagihan ?? 0);
                                $status = $bayar?->status_pembayaran ?? 'Belum Ada Tagihan';
                            @endphp
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-900/40 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-400 text-[11px]">
                                    {{ $pesertas->firstItem() + $index }}
                                </td>

                                <!-- No Peserta & Meja -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <div class="font-black font-mono text-zinc-900 dark:text-white text-[11px]">
                                        {{ $p->nomor_peserta ?? '-' }}
                                    </div>

                                </td>

                                <!-- Data Murid -->
                                <td class="py-3.5 px-4">
                                    <div
                                        class="font-bold text-zinc-900 dark:text-white uppercase flex items-center gap-1.5">
                                        <span>{{ $p->murid?->nama_lengkap ?? '-' }}</span>

                                    </div>
                                    <div
                                        class="text-[10px] text-zinc-500 dark:text-zinc-400 flex items-center gap-2 mt-0.5">
                                        <span>NISM: {{ $p->murid?->nism ?? '-' }}</span>
                                        <span>•</span>
                                        <span>Rombel: {{ $p->ruanganAsal?->nama_ruangan ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- Ruang Ujian -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                        <i class="bi bi-door-open"></i>
                                        {{ $p->ruanganUjian?->nama_ruangan ?? '-' }}
                                    </span>
                                </td>

                                <!-- Nominal Tagihan -->
                                <td class="py-3.5 px-3 text-right font-mono font-bold text-zinc-900 dark:text-white">
                                    @if ($bayar)
                                        Rp {{ number_format($tagihan, 0, ',', '.') }}
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">Belum di-set</span>
                                    @endif
                                </td>

                                <!-- Nominal Terbayar -->
                                <td
                                    class="py-3.5 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($terbayar, 0, ',', '.') }}
                                </td>

                                <!-- Sisa Tagihan -->
                                <td
                                    class="py-3.5 px-3 text-right font-mono font-bold {{ $sisa > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-400' }}">
                                    Rp {{ number_format($sisa, 0, ',', '.') }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    @if ($status === 'Lunas')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <i class="bi bi-check-circle-fill"></i>
                                            LUNAS
                                        </span>
                                    @elseif ($status === 'Dispensasi')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                            <i class="bi bi-shield-check"></i>
                                            DISPENSASI
                                        </span>
                                    @elseif ($status === 'Belum Lunas')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            <i class="bi bi-clock-history"></i>
                                            BELUM LUNAS
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-500/10 text-zinc-400 border border-zinc-500/20">
                                            BELUM DI-SET
                                        </span>
                                    @endif
                                </td>

                                <!-- No Kwitansi & Tanggal -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    @if ($bayar?->no_kwitansi)
                                        <div class="font-mono font-bold text-zinc-900 dark:text-white text-[10px]">
                                            {{ $bayar->no_kwitansi }}
                                        </div>
                                        <div class="text-[9px] text-zinc-400">
                                            {{ $bayar->tanggal_bayar ? $bayar->tanggal_bayar->format('d/m/Y') : '-' }}
                                            ({{ $bayar->metode_pembayaran }})
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic text-[10px]">-</span>
                                    @endif
                                </td>

                                <!-- Aksi Kasir (AJAX Action Modals) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Bayar / Kasir Modal (AJAX Action Modal) -->
                                        <a href="{{ route('pembayaran-imni.modal-bayar', $p->id) }}"
                                            class="action-modal px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] flex items-center gap-1 shadow-2xs transition-all active:scale-95 cursor-pointer"
                                            title="Buka Kasir Pembayaran">
                                            <i class="bi bi-wallet2"></i>
                                            <span>Bayar</span>
                                        </a>

                                        <!-- Tombol Edit Tagihan (AJAX Action Modal) -->
                                        <a href="{{ route('pembayaran-imni.modal-edit-tagihan', $p->id) }}"
                                            class="action-modal p-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold transition-all cursor-pointer"
                                            title="Ubah Nominal Tagihan Murid">
                                            <i class="bi bi-pencil-square text-xs"></i>
                                        </a>

                                        <!-- Tombol Cetak Kwitansi -->
                                        @if ($bayar && $bayar->id && $terbayar > 0)
                                            <a href="{{ route('pembayaran-imni.cetak-kwitansi', $bayar->id) }}"
                                                target="_blank"
                                                class="p-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-bold transition-all"
                                                title="Cetak Kwitansi Tanda Terima">
                                                <i class="bi bi-printer text-xs"></i>
                                            </a>

                                            <!-- Tombol Batal Transaksi -->
                                            <form action="{{ route('pembayaran-imni.batal', $bayar->id) }}"
                                                method="POST" class="delete-ajax inline m-0 p-0"
                                                data-refresh-target="#data-table-container">
                                                @csrf
                                                <button type="submit"
                                                    class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold transition-all cursor-pointer"
                                                    title="Batalkan Transaksi Pembayaran">
                                                    <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-zinc-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="bi bi-inbox text-3xl"></i>
                                        <p class="font-bold text-xs">Tidak ada data pembayaran peserta IMNI yang
                                            ditemukan.</p>
                                        <p class="text-[11px] text-zinc-400">Pastikan murid kelas akhir telah ditarik
                                            pada menu Peserta & Ruangan IMNI.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($pesertas->hasPages())
                <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    {{ $pesertas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
