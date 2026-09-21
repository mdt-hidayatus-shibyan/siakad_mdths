@section('title', 'Persuratan & Surat Keluar')

<x-app-layout>
    <!-- Header Halaman -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
        <div>
            <h2
                class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                Persuratan & Surat Keluar
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                Pembuatan, pengarsipan, duplikasi, dan pencetakan surat dinas resmi MDT Hidayatus Shibyan.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            @can('create surat-keluar.index')
                <a href="{{ route('surat-keluar.create') }}" class="m3-btn-primary h-10 px-4.5 group/btn shrink-0">
                    <i class="bi bi-plus-lg text-base transition-transform duration-300 group-hover/btn:scale-110"></i>
                    <span>Buat Surat Baru</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- NOTIFIKASI SUCCESS & ERROR -->
    @if (session('success'))
        <div
            class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center gap-3 text-xs font-bold shadow-sm">
            <i class="bi bi-check-circle-fill text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div
            class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center gap-3 text-xs font-bold shadow-sm">
            <i class="bi bi-exclamation-triangle-fill text-base shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if (session('info'))
        <div
            class="mb-6 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 flex items-center gap-3 text-xs font-bold shadow-sm">
            <i class="bi bi-info-circle-fill text-base shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- QUICK PROMPT CETAK MASSAL YANG BARU DIBUAT -->
    @if (session('bulk_created_ids'))
        <div
            class="mb-6 p-4 rounded-3xl bg-emerald-500/10 border-2 border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-md">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                    <i class="bi bi-printer-fill"></i>
                </div>
                <div>
                    <div class="font-black text-sm text-emerald-950 dark:text-emerald-200">Surat Massal Siap Dicetak!
                    </div>
                    <div class="text-[11.5px] text-zinc-600 dark:text-zinc-400 mt-0.5">
                        Sebanyak <strong>{{ count(session('bulk_created_ids')) }} lembar surat baru</strong> telah
                        berhasil digenerate berurutan.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('surat-keluar.cetak-massal', ['ids' => implode(',', session('bulk_created_ids'))]) }}"
                    target="_blank"
                    class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition-all flex items-center gap-2 shadow-lg shadow-emerald-600/30 cursor-pointer">
                    <i class="bi bi-printer-fill"></i>
                    <span>🖨️ Cetak Semua ({{ count(session('bulk_created_ids')) }}) Sekaligus</span>
                </a>
            </div>
        </div>
    @endif

    <!-- KARTU STATISTIK RINGKAS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 mb-6">
        <!-- Total Surat -->
        <div class="m3-glass-card p-4 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Total Surat</span>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white mt-1">{{ number_format($totalSurat) }}
                    </h3>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl">
                    <i class="bi bi-folder-symlink-fill"></i>
                </div>
            </div>
            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold mt-2 block">
                <i class="bi bi-calendar-check mr-1"></i> {{ $bulanIniCount }} surat bulan ini
            </span>
        </div>

        <!-- Surat Panggilan -->
        <div class="m3-glass-card p-4 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider block">Panggilan
                        Murid/Wali</span>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white mt-1">
                        {{ number_format($panggilanCount) }}</h3>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl">
                    <i class="bi bi-envelope-exclamation-fill"></i>
                </div>
            </div>
            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold mt-2 block">
                Sidang / Pemanggilan murid
            </span>
        </div>

        <!-- Surat Peringatan (SP) -->
        <div class="m3-glass-card p-4 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-rose-500 uppercase tracking-wider block">Surat Peringatan
                        (SP)</span>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white mt-1">{{ number_format($spCount) }}
                    </h3>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
            </div>
            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold mt-2 block">
                Disiplin SP 1 / SP 2 / SP 3
            </span>
        </div>

        <!-- Edaran & Undangan -->
        <div class="m3-glass-card p-4 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-indigo-500 uppercase tracking-wider block">Edaran &
                        Undangan</span>
                    <h3 class="text-2xl font-black text-zinc-900 dark:text-white mt-1">
                        {{ number_format($edaranCount + $undanganCount) }}</h3>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
            </div>
            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold mt-2 block">
                {{ $undanganCount }} Undangan • {{ $edaranCount }} Edaran
            </span>
        </div>
    </div>

    <!-- QUICK TABS FILTER 7 JENIS SURAT -->
    <div
        class="flex items-center gap-1.5 p-1.5 bg-zinc-200/50 dark:bg-zinc-800/60 rounded-2xl mb-5 overflow-x-auto custom-scrollbar">
        <a href="{{ route('surat-keluar.index', ['jenis' => 'all', 'status' => $statusFilter, 'q' => $search]) }}"
            class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $jenisFilter === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
            <i class="bi bi-grid-fill"></i>
            <span>Semua Jenis ({{ $totalSurat }})</span>
        </a>
        @foreach ($daftarJenis as $key => $val)
            @php
                $count = match ($key) {
                    'surat_panggilan' => $panggilanCount,
                    'surat_peringatan' => $spCount,
                    'surat_pemberitahuan' => $pemberitahuanCount,
                    'surat_edaran' => $edaranCount,
                    'surat_permohonan_izin' => $izinCount,
                    'surat_dispensasi' => $dispensasiCount,
                    'surat_undangan' => $undanganCount,
                    default => 0,
                };
            @endphp
            <a href="{{ route('surat-keluar.index', ['jenis' => $key, 'status' => $statusFilter, 'q' => $search]) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $jenisFilter === $key ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                <i class="bi {{ $val['icon'] }}"></i>
                <span>{{ $val['singkatan'] }}</span>
                <span
                    class="text-[10px] px-1.5 py-0.2 rounded-full {{ $jenisFilter === $key ? 'bg-emerald-700 text-white' : 'bg-zinc-300 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }}">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <!-- FILTER & PENCARIAN -->
    <div class="m3-glass-card p-4 sm:p-5 mb-5 relative z-10 shadow-2xs">
        <form action="{{ route('surat-keluar.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2.5">
            <input type="hidden" name="jenis" value="{{ $jenisFilter }}">

            <!-- Pencarian Teks -->
            <div class="flex-1 relative min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Cari Nomor Surat, Perihal, Penerima, atau Nama Murid..."
                    class="m3-input-glass w-full !pl-9 text-xs font-bold">
            </div>

            <!-- Filter Status -->
            <div class="w-full lg:w-44 relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="bi bi-flag-fill text-xs"></i>
                </div>
                <select name="status" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="terbit" {{ $statusFilter === 'terbit' ? 'selected' : '' }}>Telah Terbit</option>
                    <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Konsep (Draft)</option>
                    <option value="arsip" {{ $statusFilter === 'arsip' ? 'selected' : '' }}>Diarsipkan</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </div>

            <!-- Filter Tahun Pelajaran -->
            <div class="w-full lg:w-48 relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="bi bi-calendar-event-fill text-xs"></i>
                </div>
                <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    <option value="">Semua Tahun Pelajaran</option>
                    @foreach ($tahunPelajarans as $thn)
                        <option value="{{ $thn->id }}" {{ $tahunId == $thn->id ? 'selected' : '' }}>
                            {{ $thn->nama_hijriyah }} H / {{ $thn->nama_masehi }} M
                            {{ $thn->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </div>

            <!-- Tombol Reset / Submit -->
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="px-4 py-2.5 rounded-2xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-bold text-xs hover:opacity-90 transition-all flex items-center gap-1.5">
                    <i class="bi bi-filter"></i>
                    <span>Filter</span>
                </button>
                @if ($search || $jenisFilter !== 'all' || $statusFilter !== 'all' || $tahunId)
                    <a href="{{ route('surat-keluar.index') }}" title="Reset Filter"
                        class="p-2.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 transition-all text-xs">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL DATA DENGAN FITUR BATCH ACTIONS (ALPINE.JS) -->
    <div x-data="suratTable()" id="data-grid-container" class="space-y-4">

        <!-- BATCH ACTION BAR (MUNCUL JIKA ADA ITEM DICENTANG) -->
        <div x-show="selectedIds.length > 0"
            class="p-3 bg-zinc-900 text-white rounded-2xl shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all animate-fade-in">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span class="text-xs font-bold">
                    <strong x-text="selectedIds.length"></strong> surat terpilih
                </span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Cetak Massal Form -->
                @can('read surat-keluar.index')
                    <form action="{{ route('surat-keluar.cetak-massal') }}" method="POST" target="_blank"
                        class="inline">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit"
                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition-all flex items-center gap-1.5 shadow-md shadow-emerald-600/30 cursor-pointer">
                            <i class="bi bi-printer-fill"></i>
                            <span>Cetak Terpilih (<span x-text="selectedIds.length"></span>)</span>
                        </button>
                    </form>
                @endcan

                <!-- Hapus Massal Form -->
                @can('delete surat-keluar.index')
                    <form action="{{ route('surat-keluar.destroy-massal') }}" method="POST"
                        class="delete-ajax inline m-0 p-0" data-refresh-target="#data-grid-container">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit"
                            class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="bi bi-trash3-fill"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                @endcan

                <button type="button" @click="selectedIds = []"
                    class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs transition-all cursor-pointer">
                    Batal
                </button>
            </div>
        </div>

        <!-- TABEL DAFTAR SURAT KELUAR -->
        <div class="m3-glass-card overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-zinc-50/80 dark:bg-zinc-900/80 border-b border-zinc-200/60 dark:border-zinc-800/60 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[11px]">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" @change="toggleSelectAll($event)"
                                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </th>
                            <th class="py-3 px-2 w-10 text-center">No</th>
                            <th class="py-3 px-4">Nomor & Tanggal Surat</th>
                            <th class="py-3 px-4">Jenis Surat</th>
                            <th class="py-3 px-4">Perihal & Sifat</th>
                            <th class="py-3 px-4">Penerima / Tujuan</th>
                            <th class="py-3 px-4">Penandatangan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 text-zinc-700 dark:text-zinc-300 font-medium">
                        @forelse ($surats as $index => $surat)
                            @php
                                $meta = $surat->meta_jenis;
                                $badgeStatus = $surat->badge_status;
                            @endphp
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors"
                                :class="selectedIds.includes({{ $surat->id }}) ? 'bg-emerald-500/5' : ''">

                                <!-- Checkbox Seleksi -->
                                <td class="py-3.5 px-3 text-center">
                                    <input type="checkbox" :value="{{ $surat->id }}" x-model="selectedIds"
                                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                </td>

                                <!-- Nomor Urut -->
                                <td class="py-3.5 px-2 text-center font-bold text-zinc-400">
                                    {{ $surats->firstItem() + $index }}
                                </td>

                                <!-- Nomor & Tanggal Surat -->
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-zinc-900 dark:text-white font-mono text-[12px]">
                                        {{ $surat->nomor_surat }}
                                    </div>
                                    <div
                                        class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 flex items-center gap-1.5">
                                        <i class="bi bi-calendar3 text-[10px]"></i>
                                        <span>{{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d M Y') }}</span>
                                        @if ($surat->tanggal_hijriyah)
                                            <span class="text-zinc-400">• {{ $surat->tanggal_hijriyah }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Jenis Surat -->
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-extrabold border {{ $meta['badge_color'] }}">
                                        <i class="bi {{ $meta['icon'] }} text-xs"></i>
                                        <span>{{ $meta['nama'] }}</span>
                                    </span>
                                </td>

                                <!-- Perihal & Sifat -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 line-clamp-1"
                                        title="{{ $surat->perihal }}">
                                        {{ $surat->perihal }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span
                                            class="text-[10px] font-extrabold px-1.5 py-0.2 rounded-md {{ $surat->sifat_surat === 'Penting' || $surat->sifat_surat === 'Segera' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400' }}">
                                            {{ $surat->sifat_surat }}
                                        </span>
                                        @if ($surat->lampiran && $surat->lampiran !== '-')
                                            <span class="text-[10px] text-zinc-400 flex items-center gap-0.5">
                                                <i class="bi bi-paperclip"></i> {{ $surat->lampiran }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Penerima / Tujuan -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                        <i class="bi bi-person-fill text-zinc-400"></i>
                                        <span>{{ $surat->tujuan_surat }}</span>
                                    </div>
                                    @if ($surat->is_dispensasi_massal || (is_array($surat->murid_ids) && count($surat->murid_ids) > 1))
                                        <div
                                            class="text-[11px] text-teal-600 dark:text-teal-400 mt-0.5 font-semibold flex items-center gap-1">
                                            <i class="bi bi-people-fill"></i>
                                            <span>{{ count($surat->murid_ids ?: $surat->murid_dispensasi_list) }} Murid
                                                Terlampir</span>
                                        </div>
                                    @elseif ($surat->murid)
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            Murid: <span
                                                class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $surat->murid->nama_lengkap }}</span>
                                            ({{ $surat->murid->nism }})
                                        </div>
                                    @endif
                                </td>

                                <!-- Penandatangan -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $surat->penandatangan_nama }}
                                    </div>
                                    <div class="text-[10.5px] text-zinc-500 dark:text-zinc-400">
                                        {{ $surat->penandatangan_jabatan }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $badgeStatus['class'] }}">
                                        <i class="bi {{ $badgeStatus['icon'] }}"></i>
                                        <span>{{ $badgeStatus['label'] }}</span>
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Cetak -->
                                        @can('read surat-keluar.index')
                                            <a href="{{ route('surat-keluar.cetak', $surat->id) }}" target="_blank"
                                                title="Cetak Dokumen Resmi (PDF)"
                                                class="p-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 transition-colors">
                                                <i class="bi bi-printer-fill text-xs"></i>
                                            </a>
                                        @endcan

                                        <!-- Duplikasi / Salin -->
                                        @can('create surat-keluar.index')
                                            <a href="{{ route('surat-keluar.duplicate', $surat->id) }}"
                                                title="Duplikasi / Salin Surat Ini"
                                                class="p-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 transition-colors">
                                                <i class="bi bi-copy text-xs"></i>
                                            </a>
                                        @endcan

                                        <!-- Detail / Preview -->
                                        @can('read surat-keluar.index')
                                            <a href="{{ route('surat-keluar.show', $surat->id) }}"
                                                title="Lihat Detail & Pratinjau"
                                                class="p-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 transition-colors">
                                                <i class="bi bi-eye-fill text-xs"></i>
                                            </a>
                                        @endcan

                                        <!-- Edit -->
                                        @can('update surat-keluar.index')
                                            <a href="{{ route('surat-keluar.edit', $surat->id) }}" title="Edit Surat"
                                                class="p-1.5 rounded-xl bg-zinc-500/10 hover:bg-zinc-500/20 text-zinc-600 dark:text-zinc-400 transition-colors">
                                                <i class="bi bi-pencil-fill text-xs"></i>
                                            </a>
                                        @endcan

                                        <!-- Hapus -->
                                        @can('delete surat-keluar.index')
                                            <form action="{{ route('surat-keluar.destroy', $surat->id) }}" method="POST"
                                                class="delete-ajax inline m-0 p-0"
                                                data-refresh-target="#data-grid-container">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Surat"
                                                    class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer">
                                                    <i class="bi bi-trash3-fill text-xs"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 text-2xl">
                                            <i class="bi bi-folder2-open"></i>
                                        </div>
                                        <span class="font-bold text-sm">Belum Ada Data Surat Keluar</span>
                                        <p class="text-xs text-zinc-400 max-w-sm">
                                            Tidak ditemukan surat dengan kriteria filter saat ini. Klik tombol di bawah
                                            untuk membuat surat baru.
                                        </p>
                                        @can('create surat-keluar.index')
                                            <a href="{{ route('surat-keluar.create') }}"
                                                class="mt-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all flex items-center gap-2">
                                                <i class="bi bi-plus-lg"></i>
                                                <span>Buat Surat Pertama</span>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if ($surats->hasPages())
                <div class="p-4 border-t border-zinc-200/60 dark:border-zinc-800/60">
                    {{ $surats->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ALPINE TABLE SCRIPT -->
    <script>
        function suratTable() {
            return {
                selectedIds: [],
                allPageIds: @json($surats->pluck('id')->toArray()),

                toggleSelectAll(e) {
                    if (e.target.checked) {
                        this.selectedIds = [...this.allPageIds];
                    } else {
                        this.selectedIds = [];
                    }
                }
            };
        }
    </script>
</x-app-layout>
