@section('title', 'Peserta Ujian IMNI')

<x-app-layout>
    <div x-data="pesertaImniManager({
        selectedTahunId: {{ $selectedTahunId }},
        csrf: '{{ csrf_token() }}'
    })">
        <!-- 1. HEADER SECTION -->
        <div
            class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Peserta Ujian IMNI
                </h2>
                <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Pengelolaan peserta ujian murid kelas akhir (3 TPQ, 6 IBT, 3 TSA), nomor peserta ujian, dan dokumen
                    nominatif.
                </p>
            </div>

            <!-- Toolbar Aksi Utama -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('peserta-imni.index') }}" method="GET" id="formTahunPeserta" class="m-0">
                    <div class="relative">
                        <select name="tahun_id" onchange="document.getElementById('formTahunPeserta').submit()"
                            class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                    {{ $th->nama_hijriyah }} H ({{ $th->nama_masehi }} M)
                                    {{ $th->is_active ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                    </div>
                </form>

                <!-- Tombol Tarik Peserta (AJAX Action Modal) -->
                <a href="{{ route('peserta-imni.modal-tarik', ['tahun_id' => $selectedTahunId]) }}"
                    class="action-modal px-3.5 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <i class="bi bi-cloud-arrow-down-fill text-sm"></i>
                    <span>Tarik Murid Akhir</span>
                </a>

                <!-- Dropdown Cetak Dokumen -->
                <div class="relative" x-data="{ openCetak: false }">
                    <button type="button" @click="openCetak = !openCetak" @click.outside="openCetak = false"
                        class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                        <i class="bi bi-printer-fill text-sm"></i>
                        <span>Cetak Dokumen</span>
                        <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>

                    <div x-show="openCetak" x-transition
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200/80 dark:border-zinc-800 py-1.5 z-50">
                        <a href="{{ route('peserta-imni.cetak-kartu', ['tahun_id' => $selectedTahunId, 'tingkat_id' => request('tingkat_id'), 'ruangan_ujian_id' => request('ruangan_ujian_id')]) }}"
                            target="_blank"
                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-primary/10 hover:text-primary transition-colors">
                            <i class="bi bi-person-badge-fill text-primary"></i>
                            <span>Cetak Kartu Peserta</span>
                        </a>
                        <a href="{{ route('peserta-imni.cetak-daftar', ['tahun_id' => $selectedTahunId, 'tingkat_id' => request('tingkat_id'), 'ruangan_ujian_id' => request('ruangan_ujian_id')]) }}"
                            target="_blank"
                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-primary/10 hover:text-primary transition-colors">
                            <i class="bi bi-file-earmark-spreadsheet-fill text-emerald-600"></i>
                            <span>Daftar Nominatif (DNT)</span>
                        </a>
                    </div>
                </div>



            </div>
        </div>

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

        @if ($belumDitarik > 0)
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-l-4 border-l-primary bg-primary/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center shrink-0 text-lg border border-primary/20">
                        <i class="bi bi-person-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider">
                            Terdapat {{ $belumDitarik }} Murid Kelas Akhir Aktif Belum Ditarik ke IMNI
                        </h4>
                        <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Total potensi murid kelas akhir: <span
                                class="font-bold text-zinc-700 dark:text-zinc-300">{{ $kandidatTotal }} Murid</span>.
                            Klik tombol di samping untuk menarik otomatis.
                        </p>
                    </div>
                </div>
                <a href="{{ route('peserta-imni.modal-tarik', ['tahun_id' => $selectedTahunId]) }}"
                    class="action-modal px-3.5 py-1.5 rounded-xl bg-primary text-white text-xs font-black shrink-0 hover:bg-primary/90 transition-all cursor-pointer inline-flex items-center gap-1.5">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                    <span>Tarik {{ $belumDitarik }} Murid Sekarang</span>
                </a>
            </div>
        @endif

        <!-- 3. GRID STATISTIK METRIK PESERTA IMNI -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Peserta IMNI -->
            <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                        Total Peserta IMNI
                    </span>
                    <div
                        class="w-9 h-9 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-base shrink-0 border border-primary/20">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight font-mono">
                    {{ $totalTerdaftar }} <span class="text-xs font-bold text-zinc-400">/ {{ $kandidatTotal }} Murid</span>
                </h3>
                <p class="text-[11px] font-bold text-zinc-400 mt-2.5">
                    Total peserta terdaftar tahun ini
                </p>
            </div>

            <!-- Lunas Administrasi -->
            <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                        Lunas Administrasi
                    </span>
                    <div
                        class="w-9 h-9 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base shrink-0 border border-emerald-500/20">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">
                    {{ $totalLunas }} <span class="text-xs font-bold text-zinc-400">Murid</span>
                </h3>
                <p class="text-[11px] font-bold text-zinc-400 mt-2.5">
                    Syarat administrasi terpenuhi
                </p>
            </div>

            <!-- Dispensasi Panitia -->
            <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                        Dispensasi Panitia
                    </span>
                    <div
                        class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base shrink-0 border border-amber-500/20">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight font-mono">
                    {{ $totalDispensasi }} <span class="text-xs font-bold text-zinc-400">Murid</span>
                </h3>
                <p class="text-[11px] font-bold text-zinc-400 mt-2.5">
                    Izin dispensasi khusus ujian
                </p>
            </div>

            <!-- Belum Lunas (Terkunci) -->
            <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl relative overflow-hidden group shadow-2xs">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                        Belum Memenuhi Syarat
                    </span>
                    <div
                        class="w-9 h-9 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base shrink-0 border border-rose-500/20">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight font-mono">
                    {{ $totalBelumLunas }} <span class="text-xs font-bold text-zinc-400">Murid</span>
                </h3>
                <p class="text-[11px] font-bold text-zinc-400 mt-2.5">
                    Cetak kartu ujian terkunci
                </p>
            </div>
        </div>

        <!-- 4. FILTER TOOLBAR & PENCARIAN -->
        <div class="m3-glass-card p-4 rounded-2xl md:rounded-3xl mb-6 shadow-2xs">
            <form action="{{ route('peserta-imni.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <input type="hidden" name="tahun_id" value="{{ $selectedTahunId }}">

                <!-- Filter Tingkat -->
                <div class="lg:col-span-3">
                    <label class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block mb-1">Tingkat</label>
                    <select name="tingkat_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full text-xs font-bold">
                        <option value="">-- Semua Tingkat --</option>
                        @foreach ($daftarTingkat as $t)
                            <option value="{{ $t->id }}"
                                {{ request('tingkat_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_tingkat }} ({{ $t->kode_tingkat }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Ruangan Ujian -->
                <div class="lg:col-span-3">
                    <label class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block mb-1">Ruangan Ujian</label>
                    <select name="ruangan_ujian_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full text-xs font-bold">
                        <option value="">-- Semua Ruangan --</option>
                        @foreach ($daftarRuanganUjian as $ru)
                            <option value="{{ $ru->id }}"
                                {{ request('ruangan_ujian_id') == $ru->id ? 'selected' : '' }}>
                                {{ $ru->nama_ruangan }} ({{ $ru->level?->nama_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status Syarat / Administrasi -->
                <div class="lg:col-span-3">
                    <label class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block mb-1">Status Syarat</label>
                    <select name="status_administrasi" onchange="this.form.submit()"
                        class="m3-input-glass w-full text-xs font-bold">
                        <option value="">-- Semua Status --</option>
                        <option value="Lunas" {{ request('status_administrasi') == 'Lunas' ? 'selected' : '' }}>Lunas Tagihan</option>
                        <option value="Dispensasi" {{ request('status_administrasi') == 'Dispensasi' ? 'selected' : '' }}>Dispensasi Panitia</option>
                        <option value="Belum Lunas" {{ request('status_administrasi') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas (Terkunci)</option>
                    </select>
                </div>

                <!-- Input Pencarian -->
                <div class="lg:col-span-3">
                    <label class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block mb-1">Cari Murid / No. Peserta</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama murid, NISM..."
                            class="m3-input-glass w-full !pl-9 !pr-10 text-xs font-bold">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                        @if (request()->hasAny(['tingkat_id', 'ruangan_ujian_id', 'status_administrasi', 'search']))
                            <a href="{{ route('peserta-imni.index', ['tahun_id' => $selectedTahunId]) }}"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-rose-500 hover:text-rose-700 text-xs font-bold"
                                title="Reset Filter">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- 5. TABEL DAFTAR PESERTA IMNI -->
        <div id="data-table-container"
            class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
            <!-- Table Header Toolbar (Bulk Actions) -->
            <div
                class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <label
                        class="inline-flex items-center gap-2 cursor-pointer text-xs font-black text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected()"
                            class="w-4 h-4 rounded-lg text-primary border-zinc-300 dark:border-zinc-700 focus:ring-primary/20 cursor-pointer">
                        <span>Pilih Semua (<span x-text="selectedIds.length"></span>/{{ $pesertas->count() }})</span>
                    </label>

                    <template x-if="selectedIds.length > 0">
                        <div class="flex items-center gap-2">
                            <form action="{{ route('peserta-imni.destroy-bulk') }}" method="POST"
                                class="delete-ajax inline m-0 p-0" data-refresh-target="#data-table-container">
                                @csrf
                                <template x-for="id in selectedIds" :key="id">
                                    <input type="hidden" name="peserta_ids[]" :value="id">
                                </template>
                                <button type="submit"
                                    class="px-2.5 py-1 rounded-xl bg-rose-600 text-white text-[11px] font-black flex items-center gap-1 hover:bg-rose-700 transition-all">
                                    <i class="bi bi-trash-fill"></i>
                                    <span>Hapus (<span x-text="selectedIds.length"></span>)</span>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>

                <div class="text-xs font-bold text-zinc-400">
                    Menampilkan <span
                        class="font-black text-zinc-700 dark:text-zinc-200">{{ $pesertas->count() }}</span> Murid
                    Peserta
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60">
                        <tr>
                            <th class="py-3.5 pl-4 pr-2 w-10 text-center">#</th>
                            <th class="py-3.5 px-3">Nomor Peserta</th>
                            <th class="py-3.5 px-3">Data Murid</th>
                            <th class="py-3.5 px-3">Tingkat / Kelas</th>
                            <th class="py-3.5 px-3">Ruang Asal</th>
                            <th class="py-3.5 px-3">Ruangan Ujian</th>
                            <th class="py-3.5 px-3 text-center">Status Administrasi</th>
                            <th class="py-3.5 pl-3 pr-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse ($pesertas as $index => $p)
                            @php
                                $isLunas = $p->pembayaran && $p->pembayaran->status_pembayaran === 'Lunas';
                                $isDispensasi = $p->status_kelayakan === 'Dispensasi';
                                $isEligible = $isLunas || $isDispensasi;
                            @endphp
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors group">
                                <td class="py-3.5 pl-4 pr-2 text-center">
                                    <input type="checkbox" value="{{ $p->id }}" x-model="selectedIds"
                                        class="w-4 h-4 rounded-lg text-primary border-zinc-300 dark:border-zinc-700 focus:ring-primary/20 cursor-pointer">
                                </td>
                                <td
                                    class="py-3.5 px-3 font-mono font-black text-xs text-zinc-900 dark:text-white whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300">
                                        {{ $p->nomor_peserta ?? 'Belum Ada' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden border border-zinc-200/60 dark:border-zinc-800">
                                            @if ($p->murid && $p->murid->foto_url)
                                                <img src="{{ $p->murid->foto_url }}" alt="Foto"
                                                    class="w-full h-full object-cover">
                                            @else
                                                {{ substr($p->murid?->nama_lengkap ?? 'M', 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h5 class="font-black text-zinc-900 dark:text-white text-xs truncate"
                                                title="{{ $p->murid?->nama_lengkap }}">
                                                {{ $p->murid?->nama_lengkap ?? '-' }}
                                            </h5>
                                            <p class="text-[10px] font-mono text-zinc-400">
                                                NISM: {{ $p->murid?->nism ?? '-' }} •
                                                {{ $p->murid?->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-[10px] font-black
                                        @if ($p->tingkat_id == 1) bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                                        @elseif($p->tingkat_id == 2) bg-orange-500/10 text-orange-600 border border-orange-500/20
                                        @else bg-blue-500/10 text-blue-600 border border-blue-500/20 @endif">
                                        {{ $p->level?->nama_level ?? ($p->tingkat?->nama_tingkat ?? '-') }}
                                    </span>
                                </td>
                                <td
                                    class="py-3.5 px-3 text-zinc-600 dark:text-zinc-300 font-semibold whitespace-nowrap">
                                    {{ $p->ruanganAsal?->nama_ruangan ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 font-bold text-xs text-primary dark:text-primary-dark">
                                        <i class="bi bi-door-open text-[10px]"></i>
                                        {{ $p->ruanganUjian?->nama_ruangan ?? 'Belum Diplot' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    @if ($isLunas)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-black">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Lunas</span>
                                        </span>
                                    @elseif ($isDispensasi)
                                        <a href="{{ route('peserta-imni.modal-dispensasi', $p->id) }}" class="action-modal inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-black" title="{{ $p->catatan ? 'Alasan: '.$p->catatan : 'Dispensasi Panitia' }}">
                                            <i class="bi bi-shield-check"></i>
                                            <span>Dispensasi</span>
                                        </a>
                                    @else
                                        <a href="{{ route('peserta-imni.modal-dispensasi', $p->id) }}" class="action-modal inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px] font-black hover:bg-rose-100 dark:hover:bg-rose-900/60 transition-colors" title="Klik untuk beri dispensasi panitia">
                                            <i class="bi bi-lock-fill"></i>
                                            <span>Belum Lunas</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3.5 pl-3 pr-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Cetak Kartu Individual (Protected) -->
                                        @if ($isEligible)
                                            <a href="{{ route('peserta-imni.cetak-kartu', ['peserta_id' => $p->id, 'tahun_id' => $selectedTahunId]) }}"
                                                target="_blank"
                                                class="w-7 h-7 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-primary hover:text-white dark:hover:bg-primary flex items-center justify-center text-xs text-zinc-600 dark:text-zinc-300 transition-colors"
                                                title="Cetak Kartu Peserta">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('peserta-imni.modal-dispensasi', $p->id) }}"
                                                class="action-modal w-7 h-7 rounded-xl bg-rose-50 dark:bg-rose-950/30 text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center text-xs transition-colors"
                                                title="Terkunci: Belum Lunas (Klik untuk Dispensasi)">
                                                <i class="bi bi-lock-fill"></i>
                                            </a>
                                        @endif

                                        <!-- Tombol Modal Dispensasi Panitia -->
                                        <a href="{{ route('peserta-imni.modal-dispensasi', $p->id) }}"
                                            class="action-modal w-7 h-7 rounded-xl {{ $isDispensasi ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 hover:bg-amber-600 hover:text-white' : 'bg-zinc-100 dark:bg-zinc-800 hover:bg-amber-600 hover:text-white dark:hover:bg-amber-600 text-zinc-600 dark:text-zinc-300' }} flex items-center justify-center text-xs transition-colors"
                                            title="{{ $isDispensasi ? 'Kelola / Cabut Dispensasi' : 'Beri Dispensasi Panitia' }}">
                                            <i class="bi {{ $isDispensasi ? 'bi-shield-shaded' : 'bi-shield-plus' }}"></i>
                                        </a>

                                        <!-- Edit Modal Button (AJAX Action Modal) -->
                                        <a href="{{ route('peserta-imni.edit', $p->id) }}"
                                            class="action-modal w-7 h-7 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 flex items-center justify-center text-xs text-zinc-600 dark:text-zinc-300 transition-colors"
                                            title="Edit Peserta">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Hapus Button -->
                                        <form action="{{ route('peserta-imni.destroy', $p->id) }}" method="POST"
                                            class="delete-ajax inline m-0 p-0"
                                            data-refresh-target="#data-table-container">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-7 h-7 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 flex items-center justify-center text-xs text-zinc-600 dark:text-zinc-300 transition-colors"
                                                title="Hapus Peserta">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-zinc-400">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-2 text-xl text-zinc-400">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <h5 class="font-black text-zinc-700 dark:text-zinc-300 text-sm">Belum Ada Data
                                        Peserta IMNI</h5>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        Tarik data murid kelas akhir untuk mendaftarkan peserta ujian IMNI tahun ini.
                                    </p>
                                    <a href="{{ route('peserta-imni.modal-tarik', ['tahun_id' => $selectedTahunId]) }}"
                                        class="action-modal mt-3 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                                        <i class="bi bi-cloud-arrow-down-fill"></i>
                                        <span>Tarik Murid Kelas Akhir Sekarang</span>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Alpine.js Manager Script -->
    <script>
        function pesertaImniManager(config) {
            return {
                selectedIds: [],
                allIds: @json($pesertas->pluck('id')),

                init() {
                    $(document).on('dataGridRefreshed', () => {
                        this.selectedIds = [];
                    });
                },

                isAllSelected() {
                    return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
                },

                toggleSelectAll(e) {
                    if (e.target.checked) {
                        this.selectedIds = [...this.allIds];
                    } else {
                        this.selectedIds = [];
                    }
                }
            };
        }
    </script>
</x-app-layout>
