@section('title', 'Peserta Ujian Al-Qur\'an' . ($ujian ? ' - ' . $ujian->nama_ujian : ''))

<x-app-layout>
    <div x-data="pilihPesertaPerRuangan({
        ujianId: {{ $ujian ? $ujian->id : 'null' }},
        tahunId: {{ $tahunPelajaranId }},
        kandidatUrl: '{{ route('peserta-ujian-alquran.kandidat') }}',
        saveUrl: '{{ route('peserta-ujian-alquran.store') }}',
        csrf: '{{ csrf_token() }}'
    })">
        <!-- HEADER TOOLBAR -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="px-2.5 py-1 rounded-xl bg-primary/10 text-primary text-[11px] font-black uppercase tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-people-fill"></i>
                        <span>Peserta Ujian</span>
                    </span>
                    <span class="text-xs text-zinc-400 font-mono">
                        {{ $ujian->tahunPelajaran->nama_hijriyah ?? ($daftarTahun->firstWhere('id', $tahunPelajaranId)?->nama_hijriyah ?? '') }}
                    </span>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Peserta Murid: {{ $ujian->nama_ujian ?? 'Ujian Al-Qur\'an' }}
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Daftar murid peserta ujian kemampuan baca Al-Qur'an (Murid Kelas 5 IBT aktif & Kelas 6 IBT yang
                    belum lulus).
                </p>
            </div>

            <!-- Filter Tahun Pelajaran & Tombol Aksi Tambah Peserta -->
            <div class="w-full md:w-auto flex flex-wrap items-center gap-2 md:justify-end">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('peserta-ujian-alquran.index') }}" method="GET" id="formTahunPeserta"
                    class="m-0 relative group h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-calendar-range text-xs"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formTahunPeserta').submit()"
                        class="m3-input-glass w-full md:w-56 !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarTahun as $tp)
                            <option value="{{ $tp->id }}"
                                {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }}
                                {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </form>

                @if ($ujian)
                    <!-- Tombol Buka Modal Pilih Peserta per Ruangan -->
                    <button type="button" @click="openModal()"
                        class="px-4 h-10 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black flex items-center gap-2 shadow-2xs transition-all active:scale-95 cursor-pointer">
                        <i class="bi bi-person-plus-fill text-sm"></i>
                        <span>Pilih Peserta per Ruangan</span>
                    </button>
                @endif
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

        @if (!$ujian)
            <div
                class="m3-glass-card p-8 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 text-center space-y-4 my-6">
                <div
                    class="w-16 h-16 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto text-2xl">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3 class="text-lg font-black text-zinc-800 dark:text-zinc-200">Belum Ada Agenda Ujian Al-Qur'an di
                    Tahun
                    Pelajaran Ini</h3>
                <p class="text-xs text-zinc-500 max-w-md mx-auto">
                    Silakan buat Master Agenda Ujian Al-Qur'an terlebih dahulu pada tahun pelajaran ini sebelum
                    mendaftarkan peserta murid.
                </p>
                <div>
                    <a href="{{ route('ujian-alquran.create', ['tahun_id' => $tahunPelajaranId]) }}"
                        class="m3-btn-primary px-5 py-2.5 rounded-2xl text-xs font-black inline-flex items-center gap-2">
                        <i class="bi bi-plus-lg"></i> Buat Agenda Ujian Sekarang
                    </a>
                </div>
            </div>
        @else
            <!-- STATISTIK KARTU KECIL -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div
                    class="m3-glass-card p-4 rounded-3xl bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Total Murid
                        Terdaftar</span>
                    <h3 class="text-lg md:text-xl font-black text-zinc-900 dark:text-white mt-1">
                        {{ $statistik->total }}
                        Murid</h3>
                </div>
                <div
                    class="m3-glass-card p-4 rounded-3xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 shadow-2xs">
                    <span
                        class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Lulus
                        Ujian</span>
                    <h3 class="text-lg md:text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $statistik->lulus }} Murid ({{ $statistik->persen_lulus }}%)
                    </h3>
                </div>
                <div
                    class="m3-glass-card p-4 rounded-3xl bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20 shadow-2xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Tidak
                        Lulus / Remidi</span>
                    <h3 class="text-lg md:text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ $statistik->tidak_lulus }} Murid
                    </h3>
                </div>
                <div
                    class="m3-glass-card p-4 rounded-3xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 shadow-2xs">
                    <span
                        class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Belum
                        Diuji</span>
                    <h3 class="text-lg md:text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ $statistik->belum_diuji }} Murid
                    </h3>
                </div>
            </div>

            <!-- PANEL PENCARIAN & FILTER -->
            <div class="m3-glass-card p-3 md:p-3.5 mb-6 shadow-2xs relative z-10 print:hidden">
                <form action="{{ route('peserta-ujian-alquran.index') }}" method="GET"
                    class="w-full flex flex-col md:flex-row gap-2.5 items-center">
                    <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                    <!-- Filter Ruangan (Dimulai dari Level 5 IBT) -->
                    <div class="relative w-full md:w-64 h-10">
                        <div
                            class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                            <i class="bi bi-door-open-fill text-sm"></i>
                        </div>
                        <select name="ruangan_id" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pl-9 !pr-8 text-xs md:text-sm font-semibold cursor-pointer appearance-none h-10">
                            <option value="">Semua Ruangan</option>
                            @foreach ($daftarRuangan as $r)
                                <option value="{{ $r->id }}"
                                    {{ (string) $selectedRuanganId === (string) $r->id ? 'selected' : '' }}>
                                    {{ $r->nama_ruangan }} ({{ $r->level->nama_level ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-black"></i>
                        </div>
                    </div>

                    <!-- Input Search Keyword -->
                    <div class="relative w-full flex-1 h-10 group/search">
                        <div
                            class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                            <i class="bi bi-search text-sm"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Ketik Nomor Peserta / NISM / Nama Murid..." autocomplete="off"
                            class="m3-input-glass w-full !pl-9.5 !pr-9 text-xs md:text-sm font-semibold h-10">
                        @if (request('search'))
                            <a href="{{ route('peserta-ujian-alquran.index', array_filter(['tahun_id' => $tahunPelajaranId, 'ruangan_id' => request('ruangan_id')])) }}"
                                class="absolute inset-y-0 right-0 w-9 h-full flex items-center justify-center text-zinc-400 hover:text-rose-500 transition-colors"
                                title="Hapus Pencarian">
                                <i class="bi bi-x-circle-fill text-sm"></i>
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="m3-btn-primary w-full md:w-auto px-5 h-10 text-xs md:text-sm font-black shadow-2xs flex items-center justify-center gap-1.5 shrink-0">
                        <i class="bi bi-search text-xs"></i> <span>Cari</span>
                    </button>
                </form>
            </div>

            <!-- TABEL DAFTAR PESERTA -->
            <div id="data-table-container"
                class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/70 p-5 shadow-2xs">
                <div
                    class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800 mb-4">
                    <div>
                        <h3
                            class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="bi bi-people-fill text-emerald-500"></i>
                            <span>Daftar Murid Peserta Ujian Al-Qur'an</span>
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            Menampilkan {{ $pesertas->total() }} murid terdaftar.
                        </p>
                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="border-b border-zinc-200/80 dark:border-zinc-800 text-[10px] uppercase font-black tracking-wider text-zinc-400 bg-zinc-50/50 dark:bg-zinc-800/20">
                                <th class="py-3 px-3">No</th>
                                <th class="py-3 px-3">No. Peserta</th>
                                <th class="py-3 px-3">Nama Lengkap Murid</th>
                                <th class="py-3 px-3">NISM</th>
                                <th class="py-3 px-3">Ruangan</th>
                                <th class="py-3 px-3">Dusun / Wali</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 font-medium">
                            @forelse ($pesertas as $idx => $p)
                                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3 px-3 text-zinc-400 font-mono text-[11px]">
                                        {{ $pesertas->firstItem() + $idx }}
                                    </td>
                                    <td class="py-3 px-3 font-mono font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ $p->nomor_peserta ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-zinc-900 dark:text-white uppercase block">
                                            {{ $p->murid->nama_lengkap ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-zinc-500">
                                        {{ $p->murid->nism ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span
                                            class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                            {{ $p->ruangan->nama_ruangan ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-[11px] text-zinc-600 dark:text-zinc-400">
                                        <div>{{ $p->murid->nama_ayah ?? '-' }}</div>
                                        <div class="text-[10px] text-zinc-400 truncate max-w-[140px]">
                                            {{ $p->murid->waliMurid->kampung->nama_kampung ?? 'Dusun -' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Tombol Hapus Peserta (AJAX SweetAlert) -->
                                            <form action="{{ route('peserta-ujian-alquran.destroy', $p->id) }}"
                                                method="POST" class="m-0 delete-ajax"
                                                data-refresh-target="#data-table-container">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-600 border border-rose-500/20 text-xs font-bold transition-colors cursor-pointer"
                                                    title="Hapus dari Daftar Ujian">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-zinc-400 italic">
                                        <i class="bi bi-info-circle text-2xl block mb-2 opacity-50"></i>
                                        Belum ada peserta terdaftar pada ujian ini. Silakan klik tombol "+ Pilih Peserta
                                        per Ruangan" di atas.
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

        @if ($ujian)
            <!-- MODAL / DIALOG PILIH PESERTA PER RUANGAN -->
            <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <!-- Backdrop overlay -->
                <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                    @click="closeModal()"></div>

                <!-- Modal Center Dialog -->
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-3xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800 text-left shadow-2xl transition-all w-full max-w-2xl flex flex-col max-h-[90vh]">

                        <!-- Modal Header -->
                        <div
                            class="px-6 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between shrink-0 bg-zinc-50/50 dark:bg-zinc-800/30">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-lg">
                                    <i class="bi bi-person-plus-fill"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                                        Pilih Peserta Ujian per Ruangan
                                    </h3>
                                    <p class="text-xs text-zinc-400 mt-0.5">
                                        Pilih ruangan untuk menampilkan murid yang belum terdaftar dan belum lulus.
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="closeModal()"
                                class="w-8 h-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 flex items-center justify-center transition-all cursor-pointer">
                                <i class="bi bi-x-lg text-xs font-bold"></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1">
                            <!-- Dropdown Filter Ruangan -->
                            <div>
                                <label class="block text-xs font-black text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Pilih Ruangan Murid
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                                        <i class="bi bi-door-open-fill text-xs"></i>
                                    </div>
                                    <select x-model="selectedRuangan" @change="fetchKandidat()"
                                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                                        <option value="">-- Pilih Ruangan (5 IBT / 6 IBT) --</option>
                                        @foreach ($daftarRuangan as $r)
                                            <option value="{{ $r->id }}">
                                                {{ $r->nama_ruangan }} ({{ $r->level->nama_level ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                                    </div>
                                </div>
                                <p class="text-[11px] text-zinc-400 mt-1">
                                    * Murid yang sudah terdaftar di ujian ini atau sudah lulus ujian Al-Qur'an tidak
                                    akan ditampilkan.
                                </p>
                            </div>

                            <!-- State: Belum Pilih Ruangan -->
                            <template x-if="!selectedRuangan">
                                <div
                                    class="py-10 text-center text-zinc-400 text-xs border border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl">
                                    <i class="bi bi-door-open text-3xl block mb-2 opacity-50"></i>
                                    Silakan pilih ruangan di atas untuk memuat daftar murid yang belum terdaftar /
                                    lulus.
                                </div>
                            </template>

                            <!-- State: Loading -->
                            <template x-if="isLoading">
                                <div class="py-10 text-center text-zinc-500 text-xs space-y-2">
                                    <i class="bi bi-arrow-repeat text-2xl animate-spin inline-block text-primary"></i>
                                    <p class="font-bold">Memeriksa data murid di ruangan...</p>
                                </div>
                            </template>

                            <!-- State: Sudah Pilih Ruangan & Data Tersedia -->
                            <template x-if="selectedRuangan && !isLoading">
                                <div class="space-y-3">
                                    <!-- Toolbar Filter Pencarian & Select All -->
                                    <template x-if="kandidats.length > 0">
                                        <div class="space-y-2.5">
                                            <div class="flex items-center justify-between gap-3">
                                                <!-- Search Input -->
                                                <div class="relative flex-1">
                                                    <div
                                                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                                                        <i class="bi bi-search text-sm"></i>
                                                    </div>
                                                    <input type="text" x-model="searchQuery"
                                                        placeholder="Cari nama murid / NISM..."
                                                        class="m3-input-glass w-full !pl-9.5 !pr-3 text-sm font-semibold h-10">
                                                </div>

                                                <!-- Counter Badge -->
                                                <span
                                                    class="px-3 py-1.5 rounded-xl bg-primary/10 text-primary text-xs font-black shrink-0">
                                                    <span x-text="selectedMuridIds.length"></span> / <span
                                                        x-text="kandidats.length"></span> Murid Terpilih
                                                </span>
                                            </div>

                                            <!-- Checkbox Pilih Semua -->
                                            <div
                                                class="flex items-center justify-between px-3 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800">
                                                <label
                                                    class="flex items-center gap-2.5 cursor-pointer text-xs font-black text-zinc-800 dark:text-zinc-200 select-none">
                                                    <input type="checkbox" :checked="isAllSelected"
                                                        @change="toggleSelectAll()"
                                                        class="w-4 h-4 rounded text-primary focus:ring-primary border-zinc-300 dark:border-zinc-700">
                                                    <span>Pilih Semua Murid (<span
                                                            x-text="filteredKandidats.length"></span>)</span>
                                                </label>

                                                <span class="text-[11px] text-zinc-400">
                                                    Klik baris murid untuk memilih
                                                </span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- List Murid -->
                                    <template x-if="kandidats.length > 0">
                                        <div class="space-y-1.5 max-h-60 overflow-y-auto custom-scrollbar pr-1">
                                            <template x-for="m in filteredKandidats" :key="m.id">
                                                <div @click="toggleMurid(m.id)"
                                                    class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 select-none"
                                                    :class="selectedMuridIds.includes(m.id) ?
                                                        'bg-primary/5 dark:bg-primary-dark/10 border-primary/40 dark:border-primary-dark/40 shadow-2xs' :
                                                        'bg-zinc-50/70 dark:bg-zinc-800/30 border-zinc-200/60 dark:border-zinc-800 hover:bg-zinc-100/70 dark:hover:bg-zinc-800/60'">

                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <input type="checkbox" :value="m.id"
                                                            :checked="selectedMuridIds.includes(m.id)"
                                                            @click.stop="toggleMurid(m.id)"
                                                            class="w-4 h-4 rounded text-primary focus:ring-primary border-zinc-300 dark:border-zinc-700 pointer-events-none">

                                                        <div class="min-w-0">
                                                            <h4 class="text-xs font-black text-zinc-900 dark:text-white truncate"
                                                                x-text="m.nama_lengkap"></h4>
                                                            <div class="flex items-center gap-2 mt-0.5">
                                                                <span class="text-[10px] text-zinc-400 font-mono">NISM:
                                                                    <span x-text="m.nism"></span></span>
                                                                <span
                                                                    class="text-[10px] font-bold uppercase px-1.5 py-0.2 rounded"
                                                                    :class="m.jenis_kelamin === 'L' ?
                                                                        'bg-sky-500/10 text-sky-600' :
                                                                        'bg-rose-500/10 text-rose-600'"
                                                                    x-text="m.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'">
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <i class="bi"
                                                        :class="selectedMuridIds.includes(m.id) ?
                                                            'bi-check-circle-fill text-primary text-base' :
                                                            'bi-circle text-zinc-300 dark:text-zinc-600'"></i>
                                                </div>
                                            </template>

                                            <template x-if="filteredKandidats.length === 0 && searchQuery">
                                                <div class="py-6 text-center text-zinc-400 text-xs italic">
                                                    Tidak ada murid yang cocok dengan pencarian "<span
                                                        x-text="searchQuery"></span>"
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Empty State: Semua Sudah Terdaftar / Lulus -->
                                    <template x-if="kandidats.length === 0">
                                        <div
                                            class="py-8 px-4 text-center bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 rounded-2xl space-y-2">
                                            <div
                                                class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-xl">
                                                <i class="bi bi-check2-all"></i>
                                            </div>
                                            <h4 class="text-xs font-black text-emerald-700 dark:text-emerald-400">
                                                Semua Murid di Ruangan Ini Sudah Terdaftar / Sudah Lulus
                                            </h4>
                                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                                                Seluruh murid aktif pada ruangan ini sudah terdaftar sebagai peserta
                                                pada ujian ini atau telah lulus di ujian Al-Qur'an.
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Modal Footer -->
                        <div
                            class="px-6 py-4 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between shrink-0 bg-zinc-50/50 dark:bg-zinc-800/30">
                            <button type="button" @click="closeModal()"
                                class="px-4 py-2.5 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs transition-all active:scale-95 cursor-pointer">
                                Batal
                            </button>

                            <button type="button" @click="submitSimpan()"
                                :disabled="isSubmitting || selectedMuridIds.length === 0"
                                class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-2 shadow-2xs transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                <i class="bi"
                                    :class="isSubmitting ? 'bi-arrow-repeat animate-spin' : 'bi-check-lg text-base'"></i>
                                <span
                                    x-text="isSubmitting ? 'Mendaftarkan...' : ('Daftarkan ' + selectedMuridIds.length + ' Murid Terpilih')"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function pilihPesertaPerRuangan(config) {
                return {
                    isOpen: false,
                    ujianId: config.ujianId,
                    tahunId: config.tahunId,
                    kandidatUrl: config.kandidatUrl,
                    saveUrl: config.saveUrl,
                    csrf: config.csrf,
                    selectedRuangan: '',
                    kandidats: [],
                    selectedMuridIds: [],
                    searchQuery: '',
                    isLoading: false,
                    isSubmitting: false,

                    openModal() {
                        this.isOpen = true;
                        this.selectedRuangan = '';
                        this.kandidats = [];
                        this.selectedMuridIds = [];
                        this.searchQuery = '';
                    },

                    closeModal() {
                        if (this.isSubmitting) return;
                        this.isOpen = false;
                    },

                    get filteredKandidats() {
                        if (!this.searchQuery) return this.kandidats;
                        const q = this.searchQuery.toLowerCase();
                        return this.kandidats.filter(m =>
                            (m.nama_lengkap && m.nama_lengkap.toLowerCase().includes(q)) ||
                            (m.nism && m.nism.toLowerCase().includes(q))
                        );
                    },

                    get isAllSelected() {
                        if (this.filteredKandidats.length === 0) return false;
                        return this.filteredKandidats.every(m => this.selectedMuridIds.includes(m.id));
                    },

                    toggleSelectAll() {
                        if (this.isAllSelected) {
                            const filteredIds = this.filteredKandidats.map(m => m.id);
                            this.selectedMuridIds = this.selectedMuridIds.filter(id => !filteredIds.includes(id));
                        } else {
                            const filteredIds = this.filteredKandidats.map(m => m.id);
                            this.selectedMuridIds = Array.from(new Set([...this.selectedMuridIds, ...filteredIds]));
                        }
                    },

                    toggleMurid(id) {
                        if (this.selectedMuridIds.includes(id)) {
                            this.selectedMuridIds = this.selectedMuridIds.filter(x => x !== id);
                        } else {
                            this.selectedMuridIds.push(id);
                        }
                    },

                    async fetchKandidat() {
                        if (!this.selectedRuangan) {
                            this.kandidats = [];
                            this.selectedMuridIds = [];
                            return;
                        }

                        this.isLoading = true;
                        this.kandidats = [];
                        this.selectedMuridIds = [];
                        this.searchQuery = '';

                        try {
                            const url = new URL(this.kandidatUrl, window.location.origin);
                            url.searchParams.set('ujian_id', this.ujianId);
                            url.searchParams.set('ruangan_id', this.selectedRuangan);
                            url.searchParams.set('tahun_id', this.tahunId);

                            const response = await fetch(url.toString(), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await response.json();
                            if (data.success) {
                                this.kandidats = data.data || [];
                                // Default: centang semua murid kandidat yang ditemukan
                                this.selectedMuridIds = this.kandidats.map(m => m.id);
                            } else {
                                alert(data.message || 'Gagal memuat daftar murid');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Terjadi kesalahan koneksi saat memuat murid.');
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async submitSimpan() {
                        if (this.selectedMuridIds.length === 0 || !this.selectedRuangan) return;

                        this.isSubmitting = true;

                        try {
                            const response = await fetch(this.saveUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    ujian_id: this.ujianId,
                                    ruangan_id: this.selectedRuangan,
                                    murid_ids: this.selectedMuridIds
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                // Arahkan ke filter ruangan yang baru saja didaftarkan
                                window.location.href =
                                    `{{ route('peserta-ujian-alquran.index') }}?tahun_id=${this.tahunId}&ruangan_id=${this.selectedRuangan}`;
                            } else {
                                alert(data.message || 'Gagal menyimpan peserta.');
                                this.isSubmitting = false;
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Terjadi kesalahan koneksi saat menyimpan peserta.');
                            this.isSubmitting = false;
                        }
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
