@section('title', 'Buat Surat Keluar Baru')

@php
    $allMuridsData = $murids
        ->map(function ($m) {
            $wali = $m->waliMurid;
            $namaWali =
                $m->nama_ayah ?: ($wali?->nama_kepala_keluarga ?: ($m->nama_ibu ?: ($wali?->nama_lengkap ?: '-')));
            $kampung = $wali?->kampung?->nama_kampung ?? ($m->alamat ?? 'Somorkoneng');
            $activeRuangan = $m->ruangans->first() ?? $m->ruanganMasuk;
            return [
                'id' => $m->id,
                'nama_lengkap' => $m->nama_lengkap,
                'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                'nism' => $m->nism ?? '-',
                'ruangan_id' => $activeRuangan?->id ?? null,
                'ruangan_nama' => $activeRuangan?->nama_ruangan ?? ($m->nama_ruangan_aktif ?? '-'),
                'nama_wali' => $namaWali,
                'alamat' => $kampung,
            ];
        })
        ->values();
@endphp

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat-keluar.index') }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white"
                title="Kembali ke Daftar Surat">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    Buat Surat Resmi Baru
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Pilih jenis surat, tentukan sasaran penerima, dan pantau live preview dokumen secara realtime.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('surat-keluar.index') }}"
                class="px-4 py-2 bg-white/80 dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm flex items-center gap-2 active:scale-95">
                <i class="bi bi-x-lg"></i>
                <span>Batal</span>
            </a>
        </div>
    </div>

    <!-- NOTIFIKASI DUPLIKASI SURAT -->
    @if ($duplicateSurat)
        <div
            class="mb-6 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 flex items-center justify-center text-base shrink-0">
                    <i class="bi bi-copy"></i>
                </div>
                <div>
                    <div class="font-extrabold">Mode Duplikasi Surat:</div>
                    <div class="text-[11px] text-amber-700 dark:text-amber-400">
                        Menyalin seluruh isi dan perihal dari surat nomor
                        <strong>{{ $duplicateSurat->nomor_surat }}</strong>.
                        Nomor surat baru telah digenerate otomatis.
                    </div>
                </div>
            </div>
            <a href="{{ route('surat-keluar.create') }}"
                class="px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-900 dark:text-amber-200 font-bold text-[11px] transition-all">
                Reset Form Baru
            </a>
        </div>
    @endif

    <!-- ERROR VALIDASI -->
    @if (isset($errors) && $errors->any())
        <div
            class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold shadow-sm">
            <div class="flex items-center gap-2 mb-1.5 text-sm">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>Terdapat kesalahan pengisian formulir:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-2 font-medium">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="formSurat()" x-init="init()" class="space-y-6">

        <!-- TAB SWITCHER MOBILE: FORM vs PREVIEW -->
        <div class="lg:hidden flex items-center gap-2 p-1.5 bg-zinc-200/60 dark:bg-zinc-800/80 rounded-2xl">
            <button type="button" @click="activeMobileTab = 'form'"
                :class="activeMobileTab === 'form' ? 'bg-emerald-600 text-white shadow-sm' :
                    'text-zinc-600 dark:text-zinc-400'"
                class="flex-1 py-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                <i class="bi bi-pencil-square"></i>
                <span>Formulir Input</span>
            </button>
            <button type="button" @click="activeMobileTab = 'preview'"
                :class="activeMobileTab === 'preview' ? 'bg-emerald-600 text-white shadow-sm' :
                    'text-zinc-600 dark:text-zinc-400'"
                class="flex-1 py-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                <i class="bi bi-eye-fill"></i>
                <span>Pratinjau Live Surat</span>
            </button>
        </div>

        <!-- 2-COLUMN LAYOUT: FORM INPUT (KIRI) & LIVE PREVIEW (KANAN) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- ==================================================== -->
            <!-- KOLOM KIRI: FORMULIR INPUT (7 COLUMNS)               -->
            <!-- ==================================================== -->
            <div class="lg:col-span-7 space-y-6" x-show="activeMobileTab === 'form' || window.innerWidth >= 1024">
                <form id="formCreateSurat" action="{{ route('surat-keluar.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="jenis_surat" :value="jenis">
                    <input type="hidden" name="target_mode" :value="targetMode">
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif->id ?? '' }}">
                    <input type="hidden" name="nomor_agenda" :value="nomorAgenda">
                    <input type="hidden" name="kategori_penerima" :value="kategoriPenerima">

                    <!-- Hidden Inputs Murid IDs untuk Mode Massal / Kolektif Lintas Ruangan -->
                    <template x-if="targetMode === 'bulk'">
                        <div>
                            <template x-for="id in bulkMuridIds" :key="'hidden-murid-' + id">
                                <input type="hidden" name="murid_ids[]" :value="id">
                            </template>
                        </div>
                    </template>

                    <!-- 1. TEMPLATE, KEPALA & NOMOR SURAT -->
                    <div class="m3-glass-card p-5 space-y-4">
                        <div
                            class="flex items-center justify-between pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <h3
                                class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="bi bi-card-heading text-emerald-500"></i> 1. Template, Kepala & Nomor Surat
                            </h3>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400"
                                    x-text="currentJenisNama"></span>
                            </div>
                        </div>

                        <!-- Pilihan Template Surat (Interactive Modern Grid) -->
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300">
                                    Pilih Template Surat Resmi <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10.5px] text-zinc-400 dark:text-zinc-500 font-medium">
                                    7 Format Resmi
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2">
                                @foreach ($daftarJenis as $key => $val)
                                    <button type="button" @click="setJenis('{{ $key }}')"
                                        :class="jenis === '{{ $key }}' ?
                                            'bg-emerald-50/90 dark:bg-emerald-950/40 border-emerald-500 ring-2 ring-emerald-500/25 text-emerald-950 dark:text-emerald-100 shadow-sm' :
                                            'bg-zinc-50/70 hover:bg-zinc-100/90 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/80 border-zinc-200/80 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700'"
                                        class="group p-2.5 rounded-2xl border text-left transition-all flex flex-col justify-between cursor-pointer min-h-[74px] relative overflow-hidden">

                                        <!-- Header Kartu: Icon & Kode -->
                                        <div class="flex items-center justify-between gap-1 mb-1.5">
                                            <div class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                                :class="jenis === '{{ $key }}' ?
                                                    'bg-emerald-600 text-white shadow-xs' :
                                                    'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400'">
                                                <i class="bi {{ $val['icon'] }} text-xs"></i>
                                            </div>
                                            <span
                                                class="text-[9px] font-mono font-black uppercase px-1.5 py-0.5 rounded-md transition-colors"
                                                :class="jenis === '{{ $key }}' ?
                                                    'bg-emerald-600 text-white font-bold' :
                                                    'bg-zinc-200/80 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400'">
                                                {{ $val['kode'] }}
                                            </span>
                                        </div>

                                        <!-- Body Kartu: Nama & Target Penerima -->
                                        <div>
                                            <span class="block text-xs font-bold leading-tight"
                                                :class="jenis === '{{ $key }}' ?
                                                    'text-emerald-900 dark:text-emerald-200 font-extrabold' :
                                                    'text-zinc-800 dark:text-zinc-200'">
                                                {{ $val['nama'] }}
                                            </span>
                                            <span
                                                class="block text-[9.5px] text-zinc-400 dark:text-zinc-500 truncate mt-0.5">
                                                {{ $val['target'] }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-3 border-t border-zinc-200/60 dark:border-zinc-800/60">
                            <!-- Nomor Surat (Hanya wajib diisi untuk Single / Kolektif, pada Bulk SP digenerate berurutan otomatis) -->
                            <div class="sm:col-span-2">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300">
                                        <span x-show="targetMode === 'single' || jenis === 'surat_dispensasi'">Nomor
                                            Surat Resmi</span>
                                        <span x-show="targetMode === 'bulk' && jenis !== 'surat_dispensasi'">Nomor Surat
                                            Awal (Mulai Dari)</span>
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <button type="button" @click="generateNomor()" title="Buat Ulang Nomor Otomatis"
                                        class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 cursor-pointer">
                                        <i class="bi bi-arrow-repeat"></i> Auto-Generate
                                    </button>
                                </div>
                                <input type="text" name="nomor_surat" x-model="nomorSurat" required
                                    class="m3-input-glass w-full font-mono text-xs font-bold tracking-wide">
                                <p x-show="targetMode === 'bulk' && jenis !== 'surat_dispensasi'"
                                    class="text-[10px] text-zinc-400 mt-1">
                                    * Nomor surat akan digenerate berurutan otomatis untuk setiap murid terpilih.
                                </p>
                                <p x-show="targetMode === 'bulk' && jenis === 'surat_dispensasi'"
                                    class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                                    * 1 nomor surat resmi ini digunakan bersama untuk seluruh murid yang terlampir pada
                                    dispensasi kolektif.
                                </p>
                            </div>

                            <!-- Sifat Surat -->
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Sifat Surat
                                </label>
                                <select name="sifat_surat" x-model="sifatSurat"
                                    class="m3-input-glass w-full text-xs font-bold">
                                    <option value="Biasa">Biasa</option>
                                    <option value="Penting">Penting</option>
                                    <option value="Segera">Segera</option>
                                    <option value="Rahasia">Rahasia</option>
                                </select>
                            </div>

                            <!-- Tanggal Masehi -->
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tanggal Terbit (Masehi) <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal_surat" x-model="tanggalSurat"
                                    @change="generateNomor()" required
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>

                            <!-- Tanggal Hijriyah -->
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tanggal Terbit (Hijriyah)
                                </label>
                                <input type="text" name="tanggal_hijriyah" x-model="tanggalHijriyah"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="{{ $todayInfo['hijri'] ?? '09 Rabi\'ul Awwal 1448 H' }}">
                            </div>

                            <!-- Lampiran -->
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Lampiran
                                </label>
                                <input type="text" name="lampiran" x-model="lampiran"
                                    class="m3-input-glass w-full text-xs font-bold" placeholder="-">
                            </div>

                            <!-- Perihal -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Perihal Surat <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="perihal" x-model="perihal" required
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Contoh: Surat Panggilan Wali Murid Terkait Evaluasi KBM">
                            </div>
                        </div>
                    </div>

                    <!-- 2. TARGET PENERIMA & PEMILIH MURID / USTADZ (SINGLE VS BULK) -->
                    <div class="m3-glass-card p-5">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <h3
                                class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="bi bi-person-lines-fill text-emerald-500"></i> 2. Penerima & Target Murid
                            </h3>

                            <!-- Switcher Mode Tunggal vs Massal jika terkait murid -->
                            <div x-show="isMuridRelated"
                                class="inline-flex p-1 bg-zinc-200/60 dark:bg-zinc-800/80 rounded-xl">
                                <button type="button" @click="setTargetMode('single')"
                                    :class="targetMode === 'single' ? 'bg-emerald-600 text-white shadow-sm' :
                                        'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'"
                                    class="px-3 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="bi bi-person-fill"></i> 1 Murid (Tunggal)
                                </button>
                                <button type="button" @click="setTargetMode('bulk')"
                                    :class="targetMode === 'bulk' ? 'bg-emerald-600 text-white shadow-sm' :
                                        'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'"
                                    class="px-3 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="bi bi-people-fill"></i>
                                    <span x-show="jenis !== 'surat_dispensasi'">Banyak Murid (Massal)</span>
                                    <span x-show="jenis === 'surat_dispensasi'">Banyak Murid (1 Surat Kolektif)</span>
                                </button>
                            </div>
                        </div>

                        <!-- A. MODE TUNGGAL (1 MURID) -->
                        <div x-show="isMuridRelated && targetMode === 'single'"
                            class="mb-4 p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label
                                        class="text-[11px] font-black text-zinc-800 dark:text-zinc-200 flex items-center gap-1.5">
                                        <i class="bi bi-mortarboard-fill text-emerald-500"></i> Pilih Murid (Tunggal)
                                    </label>
                                    <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                                        Filter kelas & cari berdasarkan NISM / Nama / Wali untuk autofill data surat.
                                    </p>
                                </div>
                                <template x-if="muridId">
                                    <button type="button" @click="clearSingleMurid()"
                                        class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 font-bold text-[10px] transition-all flex items-center gap-1 cursor-pointer">
                                        <i class="bi bi-x-circle"></i> Batal Pilih Murid
                                    </button>
                                </template>
                            </div>

                            <!-- Filter & Search Murid Tunggal -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <select x-model="singleRuanganFilter"
                                        class="m3-input-glass w-full text-xs font-bold">
                                        <option value="">-- Semua Kelas / Ruangan --</option>
                                        @foreach ($ruangans as $r)
                                            <option value="{{ $r->id }}">{{ $r->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="text" x-model="singleSearch"
                                        placeholder="Cari murid / NISM / wali..."
                                        class="m3-input-glass w-full text-xs font-medium">
                                </div>
                            </div>

                            <!-- List Pilihan Murid Tunggal -->
                            <div
                                class="max-h-52 overflow-y-auto space-y-1.5 pr-1 border border-zinc-200 dark:border-zinc-800 rounded-xl p-2 bg-white/70 dark:bg-zinc-900/70">

                                <!-- Panduan Jika Belum Filter -->
                                <div x-show="!hasSingleFilter" class="text-center py-6 px-4">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto mb-2 text-base">
                                        <i class="bi bi-funnel"></i>
                                    </div>
                                    <div class="font-bold text-xs text-zinc-600 dark:text-zinc-300">Pilih Ruangan atau
                                        Ketik Pencarian</div>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Pilih kelas/ruangan atau ketik
                                        NISM/Nama murid untuk memuat daftar.</p>
                                </div>

                                <template x-if="hasSingleFilter">
                                    <template x-for="m in filteredSingleMurids" :key="m.id">
                                        <label
                                            class="flex items-center gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none"
                                            :class="String(muridId) === String(m.id) ?
                                                'border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 ring-2 ring-emerald-500/20' :
                                                'border-zinc-200/70 dark:border-zinc-800/70 bg-white/50 dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200'">
                                            <input type="radio" name="murid_id_radio" :value="m.id"
                                                :checked="String(muridId) === String(m.id)"
                                                @change="selectSingleMurid(m.id)"
                                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                            <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
                                                <div class="truncate flex-1">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="font-extrabold text-xs"
                                                            x-text="m.nama_lengkap"></span>
                                                        <span
                                                            :class="m.jenis_kelamin === 'L' ?
                                                                'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' :
                                                                'bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300'"
                                                            class="px-1.5 py-0.2 rounded text-[9px] font-black"
                                                            x-text="m.jenis_kelamin"></span>
                                                        <span class="text-[10px] text-zinc-500 font-mono"
                                                            x-text="'(NISM: ' + (m.nism || '-') + ')'"></span>
                                                    </div>
                                                    <span class="text-[10px] text-zinc-400 block truncate"
                                                        x-text="'Wali: ' + m.nama_wali + ' • ' + m.alamat"></span>
                                                </div>
                                                <div class="text-[10px] font-semibold text-zinc-400 shrink-0"
                                                    x-text="m.ruangan_nama"></div>
                                            </div>
                                        </label>
                                    </template>
                                </template>

                                <div x-show="hasSingleFilter && filteredSingleMurids.length === 0"
                                    class="text-center py-6 px-4 text-xs text-zinc-400">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center mx-auto mb-2 text-base">
                                        <i class="bi bi-search"></i>
                                    </div>
                                    <div class="font-bold text-xs text-zinc-600 dark:text-zinc-300">Data Murid Tidak
                                        Ditemukan</div>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Tidak ada murid yang cocok dengan
                                        filter kelas atau kata kunci pencarian.</p>
                                </div>
                            </div>

                            <!-- Input Hidden untuk Form Submission -->
                            <input type="hidden" name="murid_id" :value="muridId">

                            <!-- Info Murid Terpilih -->
                            <div x-show="muridDetail"
                                class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="font-extrabold text-emerald-700 dark:text-emerald-300"
                                        x-text="muridDetail ? muridDetail.nama_lengkap : ''"></div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">
                                        Murid Terpilih
                                    </span>
                                </div>
                                <div class="text-[10.5px] text-zinc-600 dark:text-zinc-400 mt-1"
                                    x-text="muridDetail ? 'TTL: ' + muridDetail.ttl + ' • Ruangan: ' + muridDetail.ruangan + ' • Wali: ' + (muridDetail.nama_ayah && muridDetail.nama_ayah !== '-' ? muridDetail.nama_ayah : muridDetail.nama_wali) + ' (' + muridDetail.alamat + ')' : ''">
                                </div>
                            </div>
                        </div>

                        <!-- B. MODE MASSAL (BANYAK MURID SEKALIGUS) -->
                        <div x-show="isMuridRelated && targetMode === 'bulk'"
                            class="mb-4 p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-500/30 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <label
                                        class="text-[11px] font-black text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                                        <i class="bi bi-check2-all text-emerald-600"></i>
                                        <span x-show="jenis !== 'surat_dispensasi'">Pilih Murid Target (Massal)</span>
                                        <span x-show="jenis === 'surat_dispensasi'">Pilih Murid Target (Bisa Lintas
                                            Ruangan / 1 Surat Kolektif)</span>
                                    </label>
                                    <p class="text-[10px] text-zinc-500 dark:text-zinc-400"
                                        x-show="jenis !== 'surat_dispensasi'">
                                        Surat akan diterbitkan otomatis untuk setiap murid yang Anda centang (1 nomor
                                        surat per murid).
                                    </p>
                                    <p class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium"
                                        x-show="jenis === 'surat_dispensasi'">
                                        Seluruh murid yang dicentang (bisa dari ruangan/kelas mana saja) akan
                                        digabungkan ke dalam <strong>1 Surat Permohonan Kolektif (1 Nomor
                                            Surat)</strong> dengan lampiran tabel daftar murid.
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectAllFiltered()" :disabled="!hasBulkFilter"
                                        :class="!hasBulkFilter ? 'opacity-50 cursor-not-allowed bg-zinc-300 text-zinc-500' :
                                            'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer'"
                                        class="px-2.5 py-1 rounded-lg font-bold text-[10px] transition-all flex items-center gap-1">
                                        <i class="bi bi-check-square"></i> Centang Semua (<span
                                            x-text="filteredMurids.length"></span>)
                                    </button>
                                    <button type="button" @click="clearBulkSelection()"
                                        class="px-2.5 py-1 rounded-lg bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-[10px] transition-all cursor-pointer">
                                        Batal Pilih
                                    </button>
                                </div>
                            </div>

                            <!-- Filter & Search Murid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <select x-model="bulkRuanganFilter"
                                        class="m3-input-glass w-full text-xs font-bold">
                                        <option value="">-- Semua Kelas / Ruangan --</option>
                                        @foreach ($ruangans as $r)
                                            <option value="{{ $r->id }}">{{ $r->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="text" x-model="bulkSearch"
                                        placeholder="Cari murid / NISM / wali..."
                                        class="m3-input-glass w-full text-xs font-medium">
                                </div>
                            </div>

                            <!-- Checkbox List Murid -->
                            <div
                                class="max-h-60 overflow-y-auto space-y-1.5 pr-1 border border-zinc-200 dark:border-zinc-800 rounded-xl p-2 bg-white/70 dark:bg-zinc-900/70">

                                <!-- Panduan Jika Belum Filter & Belum Ada Murid Terpilih -->
                                <div x-show="!hasBulkFilter && bulkMuridIds.length === 0"
                                    class="text-center py-6 px-4">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-2 text-base">
                                        <i class="bi bi-funnel"></i>
                                    </div>
                                    <div class="font-bold text-xs text-zinc-600 dark:text-zinc-300">Pilih Ruangan
                                        Target / Ketik Nama Murid</div>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Pilih kelas di atas untuk menampilkan
                                        murid dalam ruangan tersebut. Anda bisa berpindah ruangan untuk memilih murid
                                        lain.</p>
                                </div>

                                <template x-if="hasBulkFilter || bulkMuridIds.length > 0">
                                    <template x-for="m in filteredMurids" :key="m.id">
                                        <label
                                            class="flex items-center gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none"
                                            :class="bulkMuridIds.includes(m.id) ?
                                                'border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200' :
                                                'border-zinc-200/70 dark:border-zinc-800/70 bg-white/50 dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200'">
                                            <input type="checkbox" :value="m.id"
                                                :checked="bulkMuridIds.includes(m.id)" @change="toggleMuridBulk(m.id)"
                                                class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                            <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
                                                <div class="truncate flex-1">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="font-extrabold text-xs"
                                                            x-text="m.nama_lengkap"></span>
                                                        <span
                                                            :class="m.jenis_kelamin === 'L' ?
                                                                'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' :
                                                                'bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300'"
                                                            class="px-1.5 py-0.2 rounded text-[9px] font-black"
                                                            x-text="m.jenis_kelamin"></span>
                                                        <span class="text-[10px] text-zinc-500 font-mono"
                                                            x-text="'(' + m.nism + ')'"></span>
                                                    </div>
                                                    <span class="text-[10px] text-zinc-400 block truncate"
                                                        x-text="'Wali: ' + m.nama_wali + ' • ' + m.alamat"></span>
                                                </div>
                                                <div class="text-[10px] font-semibold text-zinc-400 shrink-0"
                                                    x-text="m.ruangan_nama"></div>
                                            </div>
                                        </label>
                                    </template>
                                </template>

                                <div x-show="hasBulkFilter && filteredMurids.length === 0"
                                    class="text-center py-6 px-4 text-xs text-zinc-400">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center mx-auto mb-2 text-base">
                                        <i class="bi bi-search"></i>
                                    </div>
                                    <div class="font-bold text-xs text-zinc-600 dark:text-zinc-300">Data Murid Tidak
                                        Ditemukan</div>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Tidak ada murid yang cocok dengan
                                        filter kelas atau kata kunci pencarian.</p>
                                </div>
                            </div>

                            <!-- Counter Terpilih -->
                            <div class="flex items-center justify-between text-xs pt-1">
                                <div
                                    class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Murid Terpilih: <strong x-text="bulkMuridIds.length"></strong> Murid
                                        <template x-if="selectedRoomsCount > 1">
                                            <span
                                                class="text-[10.5px] font-semibold text-emerald-700 dark:text-emerald-300"
                                                x-text="'(dari ' + selectedRoomsCount + ' Ruangan Berbeda)'"></span>
                                        </template>
                                    </span>
                                </div>
                                <span class="text-[10px] text-zinc-400"
                                    x-show="bulkMuridIds.length > 0 && jenis !== 'surat_dispensasi'">
                                    Setiap murid akan mendapatkan nomor surat berurutan & QR Code unik
                                </span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold"
                                    x-show="bulkMuridIds.length > 0 && jenis === 'surat_dispensasi'">
                                    Diterbitkan dalam 1 Surat Permohonan Kolektif (1 Nomor Surat)
                                </span>
                            </div>
                        </div>



                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5"
                            x-show="targetMode === 'single' || !isMuridRelated">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Ditujukan Kepada (Yth.) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="tujuan_surat" x-model="tujuanSurat"
                                    :required="targetMode === 'single'"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Contoh: Bpk/Ibu Wali Murid dari Ahmad Fauzi">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Alamat / Lokasi Tujuan
                                </label>
                                <input type="text" name="alamat_tujuan" x-model="alamatTujuan"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Contoh: Dsn. Somorkoneng / Di Tempat">
                            </div>
                        </div>
                    </div>

                    <!-- 3. KONTEN SPESIFIK BERDASARKAN JENIS SURAT -->
                    <div class="m3-glass-card p-5">
                        <h3
                            class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2 mb-4 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <i class="bi bi-file-text-fill text-emerald-500"></i> 3. Isi & Rincian Surat
                        </h3>

                        <!-- INFORMASI IDENTITAS MURID TERKAIT (NAMA, NISM, RUANGAN, WALI, KAMPUNG) -->
                        <div x-show="isMuridRelated" class="mb-4">
                            <!-- MODE TUNGGAL: KARTU RINCIAN IDENTITAS LENGKAP -->
                            <template x-if="targetMode === 'single'">
                                <div>
                                    <template x-if="activePreviewMurid">
                                        <div
                                            class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 dark:bg-emerald-950/30 shadow-xs">
                                            <div
                                                class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-emerald-500/20">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shadow-xs">
                                                        <i class="bi bi-person-badge-fill"></i>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="text-xs font-black text-emerald-950 dark:text-emerald-200">
                                                            Informasi Murid / Anak Terkait
                                                        </span>
                                                        <span
                                                            class="text-[10px] text-emerald-700 dark:text-emerald-400 ml-1 font-medium hidden sm:inline">
                                                            (Otomatis terhubung ke format surat)
                                                        </span>
                                                    </div>
                                                </div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold bg-emerald-600 text-white shadow-xs">
                                                    Murid Terpilih
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 text-xs">
                                                <!-- 1. Nama Lengkap -->
                                                <div
                                                    class="p-2 rounded-xl bg-white/80 dark:bg-zinc-900/80 border border-emerald-500/20">
                                                    <span
                                                        class="text-[9.5px] text-zinc-500 dark:text-zinc-400 block font-medium">Nama
                                                        Lengkap</span>
                                                    <strong
                                                        class="text-zinc-900 dark:text-white block truncate text-[11px]"
                                                        x-text="activePreviewMurid.nama_lengkap"></strong>
                                                </div>
                                                <!-- 2. NISM -->
                                                <div
                                                    class="p-2 rounded-xl bg-white/80 dark:bg-zinc-900/80 border border-emerald-500/20">
                                                    <span
                                                        class="text-[9.5px] text-zinc-500 dark:text-zinc-400 block font-medium">NISM</span>
                                                    <span
                                                        class="font-bold text-zinc-800 dark:text-zinc-200 block font-mono text-[11px]"
                                                        x-text="activePreviewMurid.nism"></span>
                                                </div>
                                                <!-- 3. Ruangan / Kelas -->
                                                <div
                                                    class="p-2 rounded-xl bg-white/80 dark:bg-zinc-900/80 border border-emerald-500/20">
                                                    <span
                                                        class="text-[9.5px] text-zinc-500 dark:text-zinc-400 block font-medium">Ruangan
                                                        / Kelas</span>
                                                    <span
                                                        class="font-bold text-emerald-700 dark:text-emerald-400 block text-[11px]"
                                                        x-text="activePreviewMurid.ruangan_nama"></span>
                                                </div>
                                                <!-- 4. Orang Tua / Wali -->
                                                <div
                                                    class="p-2 rounded-xl bg-white/80 dark:bg-zinc-900/80 border border-emerald-500/20">
                                                    <span
                                                        class="text-[9.5px] text-zinc-500 dark:text-zinc-400 block font-medium">Orang
                                                        Tua / Wali</span>
                                                    <span
                                                        class="font-bold text-zinc-800 dark:text-zinc-200 block truncate text-[11px]"
                                                        x-text="activePreviewMurid.nama_wali"></span>
                                                </div>
                                                <!-- 5. Dusun / Kampung -->
                                                <div
                                                    class="p-2 rounded-xl bg-white/80 dark:bg-zinc-900/80 border border-emerald-500/20 col-span-2 sm:col-span-1">
                                                    <span
                                                        class="text-[9.5px] text-zinc-500 dark:text-zinc-400 block font-medium">Dusun
                                                        / Kampung</span>
                                                    <span
                                                        class="font-bold text-zinc-800 dark:text-zinc-200 block truncate text-[11px]"
                                                        x-text="activePreviewMurid.alamat"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!activePreviewMurid">
                                        <div
                                            class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-2.5">
                                            <i class="bi bi-info-circle-fill text-amber-500 text-sm shrink-0"></i>
                                            <span>Silakan pilih murid pada <strong>Bagian 3 (Penerima & Target
                                                    Murid)</strong> untuk memuat data identitas anak secara
                                                otomatis.</span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- MODE MASSAL: RINGKASAN DATA MURID -->
                            <template x-if="targetMode === 'bulk'">
                                <div
                                    class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 dark:bg-emerald-950/30 space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shadow-xs">
                                                <i class="bi bi-people-fill"></i>
                                            </div>
                                            <div>
                                                <span
                                                    class="text-xs font-black text-emerald-950 dark:text-emerald-200">
                                                    Murid Terpilih: <strong x-text="bulkMuridIds.length"></strong>
                                                    Murid
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400 hidden sm:inline">
                                            Identitas anak (Nama, NISM, Ruangan, Wali, Kampung) dicetak lengkap per
                                            surat
                                        </span>
                                    </div>

                                    <div x-show="bulkMuridIds.length > 0"
                                        class="max-h-64 overflow-y-auto space-y-2 pr-1">
                                        <template x-for="id in bulkMuridIds" :key="id">
                                            <div
                                                class="p-2.5 rounded-xl bg-white/90 dark:bg-zinc-900/90 border border-emerald-500/20 text-[11px] space-y-2">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <i class="bi bi-person-fill text-emerald-600 shrink-0"></i>
                                                        <strong class="text-zinc-900 dark:text-white truncate"
                                                            x-text="getMuridInfo(id).nama_lengkap"></strong>
                                                        <span class="text-[10px] text-zinc-400 shrink-0"
                                                            x-text="'(NISM: ' + getMuridInfo(id).nism + ')'"></span>
                                                    </div>
                                                    <div
                                                        class="flex items-center gap-2 text-[10px] text-zinc-600 dark:text-zinc-400 shrink-0">
                                                        <span
                                                            class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-bold"
                                                            x-text="'Kelas: ' + getMuridInfo(id).ruangan_nama"></span>
                                                        <span x-text="'Wali: ' + getMuridInfo(id).nama_wali"></span>
                                                        <button type="button" @click="toggleMuridBulk(id)"
                                                            title="Batal pilih murid ini"
                                                            class="px-1.5 py-0.5 rounded bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 font-bold text-[9.5px] transition-all flex items-center gap-0.5 cursor-pointer">
                                                            <i class="bi bi-x"></i> Hapus
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Input Khusus Kelas di Lembaga Tujuan jika Surat Dispensasi Massal -->
                                                <div x-show="jenis === 'surat_dispensasi'"
                                                    class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                                                    <label
                                                        class="text-[10.5px] font-bold text-emerald-800 dark:text-emerald-300 shrink-0">
                                                        Kelas di Lembaga Tujuan:
                                                    </label>
                                                    <input type="text" :name="'kelas_lembaga[' + id + ']'"
                                                        x-model="kelasLembaga[id]"
                                                        placeholder="Contoh: X PHT 1 / X TKJ 2 / VII-A"
                                                        class="m3-input-glass w-full text-xs font-bold py-1 px-2.5">
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="bulkMuridIds.length === 0"
                                        class="text-xs text-amber-700 dark:text-amber-400 italic p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center gap-2">
                                        <i class="bi bi-exclamation-circle text-amber-500"></i>
                                        <span>Belum ada murid yang dicentang pada Bagian 3.</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- A. TEMPLATE: SURAT PANGGILAN -->
                        <div x-show="jenis === 'surat_panggilan'" class="space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Hari Menghadap <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="hari_panggilan" x-model="hariPanggilan"
                                        class="m3-input-glass w-full text-xs font-bold">
                                        <option value="">-- Pilih Hari --</option>
                                        <option value="Ahad">Ahad</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jum'at">Jum'at</option>
                                        <option value="Sabtu">Sabtu</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Tanggal Menghadap <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" name="tanggal_panggilan" x-model="tanggalPanggilan"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Waktu / Jam
                                    </label>
                                    <input type="text" name="waktu_panggilan" x-model="waktuPanggilan"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="14.00 WIB s/d Selesai">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Tempat Menghadap
                                    </label>
                                    <input type="text" name="tempat_menghadap" x-model="tempatMenghadap"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Kantor TU / Ruang Guru MDT Hidayatus Shibyan">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Menghadap Kepada
                                </label>
                                <input type="text" name="menghadap_kepada" x-model="menghadapKepada"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Kepala Madrasah & Tim Kesiswaan">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Keperluan / Alasan Pemanggilan <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="alasan_panggilan" x-model="alasanPanggilan" rows="2"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Contoh: Evaluasi perkembangan ketertiban dan absensi kehadiran belajar murid"></textarea>
                            </div>
                        </div>

                        <!-- B. TEMPLATE: SURAT PERINGATAN (SP) -->
                        <div x-show="jenis === 'surat_peringatan'" class="space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tingkat Surat Peringatan (SP) <span class="text-rose-500">*</span>
                                </label>
                                <select name="tingkat_sp" x-model="tingkatSp"
                                    class="m3-input-glass w-full text-xs font-bold">
                                    <option value="Surat Peringatan I (SP 1)">Surat Peringatan I (SP 1)</option>
                                    <option value="Surat Peringatan II (SP 2)">Surat Peringatan II (SP 2)</option>
                                    <option value="Surat Peringatan III (SP 3)">Surat Peringatan III (SP 3)</option>
                                    <option value="Surat Peringatan Keras / Terakhir">Surat Peringatan Keras / Terakhir
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Bentuk Pelanggaran <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="bentuk_pelanggaran" x-model="bentukPelanggaran" rows="2"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Contoh: Tidak mengikuti kegiatan belajar mengajar tanpa keterangan selama 7 hari berturut-turut"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Pasal / Poin Tata Tertib Dilanggar
                                    </label>
                                    <input type="text" name="poin_tatib_dilanggar" x-model="poinTatib"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Pasal 4 Ayat 2 (Ketidakhadiran)">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Masa Batas Pembinaan
                                    </label>
                                    <input type="text" name="batas_waktu_pembinaan" x-model="batasWaktuPembinaan"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="14 Hari Kerja sejak surat ini diterbitkan">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Bentuk Sanksi / Tindakan Pembinaan
                                </label>
                                <input type="text" name="tindakan_pembinaan" x-model="tindakanPembinaan"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Wajib setor hafalan dan pembinaan khusus bersama wali kelas">
                            </div>
                        </div>

                        <!-- C. TEMPLATE: SURAT PEMBERITAHUAN -->
                        <div x-show="jenis === 'surat_pemberitahuan'" class="space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Kategori Pemberitahuan
                                    </label>
                                    <select name="kategori_pemberitahuan" x-model="kategoriPemberitahuan"
                                        class="m3-input-glass w-full text-xs font-bold">
                                        <option value="Akademik & KBM">Akademik & KBM</option>
                                        <option value="Pembayaran / Keuangan Syahriyah">Pembayaran / Keuangan Syahriyah
                                        </option>
                                        <option value="Libur & Kalender Madrasah">Libur & Kalender Madrasah</option>
                                        <option value="Kegiatan / Acara Murid">Kegiatan / Acara Murid</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Jadwal Terkait (Opsional)
                                    </label>
                                    <input type="text" name="jadwal_terkait" x-model="jadwalTerkait"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Mulai 25 September 2026">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Pokok Uraian Pemberitahuan <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="pokok_pemberitahuan" x-model="pokokPemberitahuan" rows="3"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Tuliskan pokok informasi yang ingin disampaikan kepada wali murid..."></textarea>
                            </div>
                        </div>

                        <!-- D. TEMPLATE: SURAT EDARAN -->
                        <div x-show="jenis === 'surat_edaran'" class="space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Nomor Edaran Internal (Opsional)
                                    </label>
                                    <input type="text" name="nomor_edaran_internal" x-model="nomorEdaranInternal"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="SE/01/MDTHS/2026">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Berlaku Mulai
                                    </label>
                                    <input type="text" name="berlaku_mulai" x-model="berlakuMulai"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Pokok Maklumat / Ketetapan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="pokok_maklumat" x-model="pokokMaklumat"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Pelaksanaan Ujian Tengah Semester & Libur Maulid">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Poin-Poin Instruksi / Ketetapan <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="instruksi_poin" x-model="instruksiPoin" rows="4"
                                    class="m3-input-glass w-full text-xs font-mono"
                                    placeholder="1. KBM semester ganjil diliburkan...&#10;2. Seluruh murid diwajibkan masuk kembali pada..."></textarea>
                            </div>
                        </div>

                        <!-- E. TEMPLATE: SURAT PERMOHONAN IZIN -->
                        <div x-show="jenis === 'surat_permohonan_izin'" class="space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Nama Kegiatan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="nama_kegiatan" x-model="namaKegiatan"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Peringatan Hari Besar Islam (PHBI) Isra' Mi'raj">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Hari
                                        Kegiatan</label>
                                    <input type="text" name="hari_kegiatan" x-model="hariKegiatan"
                                        class="m3-input-glass w-full text-xs font-bold" placeholder="Ahad Malam">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal</label>
                                    <input type="date" name="tanggal_kegiatan" x-model="tanggalKegiatan"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Waktu</label>
                                    <input type="text" name="waktu_kegiatan" x-model="waktuKegiatan"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="19.30 WIB s/d Selesai">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tempat
                                        / Lokasi</label>
                                    <input type="text" name="tempat_kegiatan" x-model="tempatKegiatan"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Halaman Masjid Nurul Huda">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Penanggung
                                        Jawab</label>
                                    <input type="text" name="penanggung_jawab" x-model="penanggungJawab"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Panitia PHBI MDT Hidayatus Shibyan">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Fasilitas
                                    / Permohonan Izin</label>
                                <input type="text" name="fasilitas_dimohonkan" x-model="fasilitasDimohonkan"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Izin penggunaan panggung aula dan sound system madrasah">
                            </div>
                        </div>

                        <!-- F. TEMPLATE: SURAT DISPENSASI -->
                        <div x-show="jenis === 'surat_dispensasi'" class="space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Sekolah
                                        / Lembaga Tujuan <span class="text-rose-500">*</span></label>
                                    <input type="text" name="nama_sekolah_tujuan" x-model="namaSekolahTujuan"
                                        @input="tujuanSurat = namaSekolahTujuan"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Kepala UPTD SMK Negeri 1 Kwanyar">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama
                                        Kegiatan / Agenda <span class="text-rose-500">*</span></label>
                                    <input type="text" name="nama_kegiatan_dispensasi"
                                        x-model="namaKegiatanDispensasi"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Imtihan Dauri 1 / PORSADIN">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Hari
                                        Pelaksanaan</label>
                                    <input type="text" name="hari_kegiatan_dispensasi"
                                        x-model="hariKegiatanDispensasi"
                                        class="m3-input-glass w-full text-xs font-bold" placeholder="Kamis">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Mulai
                                        Tanggal <span class="text-rose-500">*</span></label>
                                    <input type="date" name="tanggal_mulai_dispensasi"
                                        x-model="tanggalMulaiDispensasi"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Selesai
                                        Tanggal (Opsional)</label>
                                    <input type="date" name="tanggal_selesai_dispensasi"
                                        x-model="tanggalSelesaiDispensasi"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Waktu
                                        Pelaksanaan</label>
                                    <input type="text" name="waktu_kegiatan_dispensasi"
                                        x-model="waktuKegiatanDispensasi"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="13.30 WIB s/d Selesai">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tempat
                                        Pelaksanaan</label>
                                    <input type="text" name="tempat_kegiatan_dispensasi"
                                        x-model="tempatKegiatanDispensasi"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="MDT Hidayatus Shibyan">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Bentuk Permohonan Dispensasi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="permohonan_dispensasi_khusus"
                                    x-model="permohonanDispensasiKhusus"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="dipulangkan pukul 12.00 WIB agar Murid dapat mempersiapkan diri">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Alasan
                                    / Catatan Tambahan (Opsional)</label>
                                <textarea name="alasan_kegiatan" x-model="alasanDispensasi" rows="2"
                                    class="m3-input-glass w-full text-xs font-medium" placeholder="Keterangan tambahan jika diperlukan..."></textarea>
                            </div>
                        </div>

                        <!-- G. TEMPLATE: SURAT UNDANGAN -->
                        <div x-show="jenis === 'surat_undangan'" class="space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Nama Acara / Agenda <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="nama_acara" x-model="namaAcara"
                                    class="m3-input-glass w-full text-xs font-bold"
                                    placeholder="Rapat Koordinasi Evaluasi Pembelajaran & Keuangan Semester Ganjil">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Hari
                                        Acara <span class="text-rose-500">*</span></label>
                                    <input type="text" name="hari_acara" x-model="hariAcara"
                                        class="m3-input-glass w-full text-xs font-bold" placeholder="Ahad Malam">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal
                                        Acara <span class="text-rose-500">*</span></label>
                                    <input type="date" name="tanggal_acara" x-model="tanggalAcara"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Waktu
                                        / Pukul</label>
                                    <input type="text" name="waktu_acara" x-model="waktuAcara"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="19.30 WIB (Ba'da Isya)">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tempat
                                        Acara</label>
                                    <input type="text" name="tempat_acara" x-model="tempatAcara"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Aula MDT Hidayatus Shibyan">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Pakaian
                                        / Dresscode</label>
                                    <input type="text" name="pakaian_dresscode" x-model="pakaianDresscode"
                                        class="m3-input-glass w-full text-xs font-bold"
                                        placeholder="Busana Muslim Rapi & Berpeci">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Susunan
                                    Agenda / Catatan Khusus</label>
                                <textarea name="agenda_acara" x-model="agendaAcara" rows="2" class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Contoh: 1. Pembukaan, 2. Laporan Keuangan, 3. Evaluasi KBM"></textarea>
                            </div>
                        </div>

                        <!-- Narasi Paragraf Tambahan (Opsional untuk semua jenis) -->
                        <div class="pt-3 border-t border-zinc-200/60 dark:border-zinc-800/60 mt-3">
                            <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Narasi / Catatan Paragraf Tambahan (Opsional)
                            </label>
                            <textarea name="isi_surat" x-model="isiSurat" rows="2" class="m3-input-glass w-full text-xs font-medium"
                                placeholder="Tambahkan teks kustom atau pesan khusus jika diperlukan..."></textarea>
                        </div>
                    </div>

                    <!-- 4. LEMBAR LAMPIRAN RESMI (HALAMAN 2 - OPSIONAL) -->
                    <div class="m3-glass-card p-5 space-y-3">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <div>
                                <h3
                                    class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                    <i class="bi bi-paperclip text-emerald-500"></i> 4. Lembar Lampiran Resmi (Halaman
                                    2)
                                </h3>
                                <p class="text-[10.5px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    Aktifkan jika surat membutuhkan lembar lampiran resmi tercetak (misal: susunan
                                    acara, tata tertib, rincian biaya).
                                </p>
                            </div>

                            <label
                                class="inline-flex items-center gap-2 cursor-pointer select-none bg-zinc-100 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <input type="checkbox" name="has_lampiran" x-model="hasLampiran" value="1"
                                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-zinc-300 dark:border-zinc-700 cursor-pointer">
                                <span class="text-xs font-bold"
                                    :class="hasLampiran ? 'text-emerald-600 dark:text-emerald-400' :
                                        'text-zinc-600 dark:text-zinc-400'">
                                    Sertakan Lampiran Resmi
                                </span>
                            </label>
                        </div>

                        <div x-show="hasLampiran" x-collapse class="space-y-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Judul Lampiran <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="lampiran_judul" x-model="lampiranJudul"
                                    class="m3-input-glass w-full text-xs font-bold uppercase"
                                    placeholder="Contoh: SUSUNAN ACARA & JADWAL KEGIATAN">
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center justify-between gap-1 mb-1">
                                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300">
                                        Isi / Rincian Konten Lampiran <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="insertLampiranTemplate('agenda')"
                                            class="text-[10px] px-2 py-0.5 rounded-lg bg-zinc-200/80 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 font-bold text-zinc-700 dark:text-zinc-300 transition-all cursor-pointer">
                                            + Susunan Acara
                                        </button>
                                        <button type="button" @click="insertLampiranTemplate('poin')"
                                            class="text-[10px] px-2 py-0.5 rounded-lg bg-zinc-200/80 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 font-bold text-zinc-700 dark:text-zinc-300 transition-all cursor-pointer">
                                            + Tata Tertib & Ketentuan
                                        </button>
                                    </div>
                                </div>
                                <textarea name="lampiran_konten" x-model="lampiranKonten" rows="5"
                                    class="m3-input-glass w-full text-xs font-mono leading-relaxed"
                                    placeholder="Tuliskan susunan agenda, rincian jadwal, atau poin-poin ketentuan lampiran di sini..."></textarea>
                                <p class="text-[10px] text-zinc-400 mt-1">
                                    💡 Rincian ini otomatis dicetak di Halaman 2 lengkap dengan identitas nomor surat
                                    dan pengesahan resmi.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 5. PEJABAT PENANDATANGAN SURAT (MAX 4 ORANG) -->
                    <div class="m3-glass-card p-5 space-y-4">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <div>
                                <h3
                                    class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                    <i class="bi bi-pen-fill text-emerald-500"></i> 5. Pejabat Penandatangan Surat
                                    (Maksimal 4 Orang)
                                </h3>
                                <p class="text-[10.5px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    Pilih dan sesuaikan siapa saja yang bertanda tangan. Format tanda tangan dicetak
                                    menggunakan <strong>QR Code</strong> verifikasi profil publik (format SK Arsip).
                                </p>
                            </div>
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold border border-emerald-200/60 dark:border-emerald-800/60 shrink-0">
                                <i class="bi bi-qr-code"></i>
                                <span x-text="activeSigners.length + ' Pejabat Aktif'"></span>
                            </div>
                        </div>

                        <!-- 4 Kartu Slot Penandatangan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <template x-for="(s, idx) in signers" :key="s.key">
                                <div class="rounded-2xl border transition-all p-3.5 space-y-3"
                                    :class="s.is_active ?
                                        'bg-white/80 dark:bg-zinc-900/80 border-emerald-500/40 ring-1 ring-emerald-500/20 shadow-sm' :
                                        'bg-zinc-50/60 dark:bg-zinc-900/40 border-zinc-200/70 dark:border-zinc-800/70 opacity-70 hover:opacity-100'">

                                    <!-- Header Kartu: Toggle & Judul Role -->
                                    <div class="flex items-center justify-between">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" x-model="s.is_active"
                                                class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                            <span class="font-black text-xs text-zinc-900 dark:text-white"
                                                x-text="(idx + 1) + '. ' + s.title"></span>
                                        </label>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold"
                                            :class="s.is_active ?
                                                'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' :
                                                'bg-zinc-200 dark:bg-zinc-800 text-zinc-500'">
                                            <span
                                                x-text="s.is_active ? 'Aktif Bertanda Tangan' : 'Tidak Ditampilkan'"></span>
                                        </span>
                                    </div>

                                    <!-- Quick Select Presets Khusus Kabid & Admin -->
                                    <template x-if="s.key === 'kabid' && kabidOptions.length > 0">
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-500 mb-1">Pilih Cepat
                                                Kepala Bidang:</label>
                                            <select @change="setKabidPreset($event.target.value)"
                                                class="m3-input-glass w-full text-[11px] font-semibold">
                                                <option value="">-- Pilih dari Daftar Kabid --</option>
                                                @foreach ($penandatanganConfig['kabid_options'] as $k)
                                                    <option value="{{ $k['id'] }}">
                                                        {{ $k['nama'] }}
                                                        ({{ $k['jabatan'] }}{{ $k['tingkat'] ? ' - ' . $k['tingkat'] : '' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </template>

                                    <template x-if="s.key === 'admin' && adminOptions.length > 0">
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-500 mb-1">Pilih Cepat
                                                Administrator:</label>
                                            <select @change="setAdminPreset($event.target.value)"
                                                class="m3-input-glass w-full text-[11px] font-semibold">
                                                <option value="">-- Pilih dari Daftar Admin --</option>
                                                @foreach ($penandatanganConfig['admin_options'] as $a)
                                                    <option value="{{ $a['id'] }}">
                                                        {{ $a['nama'] }} ({{ $a['jabatan'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </template>

                                    <!-- Input Detail Penandatangan -->
                                    <div class="space-y-2 text-xs">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-zinc-600 dark:text-zinc-400 mb-0.5">
                                                    Keterangan / Label Atas
                                                </label>
                                                <input type="text" x-model="s.label_atas"
                                                    placeholder="Contoh: Mengesahkan,"
                                                    class="m3-input-glass w-full text-[11px] font-medium">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-zinc-600 dark:text-zinc-400 mb-0.5">
                                                    Jabatan Resmi
                                                </label>
                                                <input type="text" x-model="s.jabatan"
                                                    class="m3-input-glass w-full text-[11px] font-bold">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-zinc-600 dark:text-zinc-400 mb-0.5">
                                                    Nama Terang Pejabat
                                                </label>
                                                <input type="text" x-model="s.nama"
                                                    class="m3-input-glass w-full text-[11px] font-extrabold">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-zinc-600 dark:text-zinc-400 mb-0.5">
                                                    NIP / NIU (Opsional)
                                                </label>
                                                <input type="text" x-model="s.nip"
                                                    class="m3-input-glass w-full text-[11px] font-mono"
                                                    placeholder="-">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Hidden Inputs Penandatangan Form Submission -->
                        <input type="hidden" name="penandatangan_list" :value="JSON.stringify(signers)">
                        <input type="hidden" name="penandatangan_nama" :value="primarySigner.nama">
                        <input type="hidden" name="penandatangan_jabatan" :value="primarySigner.jabatan">
                        <input type="hidden" name="penandatangan_nip" :value="primarySigner.nip">

                        <!-- Pengaturan Tempat & Status Surat -->
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 border-t border-zinc-200/60 dark:border-zinc-800/60">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tempat Diterbitkan
                                </label>
                                <input type="text" name="tempat_terbit" x-model="tempatTerbit"
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Status Surat
                                </label>
                                <select name="status" class="m3-input-glass w-full text-xs font-bold">
                                    <option value="terbit">Telah Terbit (Siap Cetak)</option>
                                    <option value="draft">Konsep (Draft)</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tembusan (Opsional)
                                </label>
                                <input type="text" name="tembusan" x-model="tembusan"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Contoh: 1. Pengasuh MDTHS, 2. Arsip">
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI SUBMIT -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('surat-keluar.index') }}"
                            class="px-5 py-2.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs transition-all cursor-pointer">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition-all shadow-lg shadow-emerald-600/30 flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-printer-fill"></i>
                            <span x-show="targetMode === 'single'">Terbitkan & Siapkan Cetak</span>
                            <span x-show="targetMode === 'bulk' && jenis !== 'surat_dispensasi'">Terbitkan <span
                                    x-text="bulkMuridIds.length || 0"></span> Surat Massal & Siapkan Cetak</span>
                            <span x-show="targetMode === 'bulk' && jenis === 'surat_dispensasi'">Terbitkan Surat
                                Dispensasi Kolektif (<span x-text="bulkMuridIds.length || 0"></span> Murid) & Siapkan
                                Cetak</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ==================================================== -->
            <!-- KOLOM KANAN: LIVE INTERACTIVE PREVIEW (5 COLUMNS)    -->
            <!-- ==================================================== -->
            <div class="lg:col-span-5 sticky-preview-column custom-scrollbar"
                x-show="activeMobileTab === 'preview' || window.innerWidth >= 1024">
                <div class="space-y-4 lg:pr-1">

                    <!-- HEADER PREVIEW BAR -->
                    <div
                        class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl bg-zinc-900 text-white shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs font-extrabold uppercase tracking-wider">Live Preview Surat</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-zinc-800 text-emerald-400 font-bold"
                            x-text="currentJenisNama"></span>
                    </div>

                    <!-- SELECTOR MURID UNTUK SIMULASI MASSAL (NON-DISPENSASI) -->
                    <div x-show="targetMode === 'bulk' && bulkMuridIds.length > 0 && jenis !== 'surat_dispensasi'"
                        class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                        <div
                            class="flex items-center justify-between text-[10px] font-bold text-emerald-800 dark:text-emerald-300 mb-2">
                            <span>Pilih Simulasi Pratinjau Murid (<span x-text="bulkMuridIds.length"></span>
                                Surat):</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                            <template x-for="(mId, idx) in bulkMuridIds" :key="mId">
                                <button type="button" @click="previewBulkActiveId = mId"
                                    :class="previewBulkActiveId === mId ?
                                        'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-500/30' :
                                        'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-emerald-100 dark:hover:bg-zinc-700'"
                                    class="px-2 py-1 rounded-lg text-[9.5px] font-bold transition-all truncate max-w-[150px] cursor-pointer"
                                    x-text="(idx+1) + '. ' + getMuridName(mId)">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- INFORMASI SURAT KOLEKTIF UNTUK DISPENSASI MASSAL -->
                    <div x-show="targetMode === 'bulk' && bulkMuridIds.length > 0 && jenis === 'surat_dispensasi'"
                        class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                        <div
                            class="flex items-center justify-between text-[10px] font-bold text-emerald-800 dark:text-emerald-300">
                            <span class="flex items-center gap-1.5">
                                <i class="bi bi-file-earmark-ruled-fill text-emerald-600"></i>
                                Pratinjau Surat Kolektif (<strong x-text="bulkMuridIds.length"></strong> Murid
                                Terlampir)
                            </span>
                            <span class="text-[9.5px] px-2 py-0.5 rounded-full bg-emerald-600 text-white font-bold">1
                                Nomor
                                Surat</span>
                        </div>
                    </div>

                    <!-- LEMBAR SIMULASI KERTAS SURAT RESMI (A4 PREVIEW) -->
                    <div
                        class="bg-white text-zinc-900 rounded-2xl p-5 sm:p-6 shadow-xl border border-zinc-200/80 text-[10.5px] leading-relaxed font-sans overflow-hidden select-none">

                        <!-- KOP SURAT PREVIEW -->
                        <div class="pb-1 border-b-2 border-black text-left">
                            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="object-contain"
                                style="max-width: 100%;
            max-height: 120px;
            height: auto;">
                        </div>
                        <div class="border-b border-black mt-0.5 mb-2.5"></div>

                        <!-- KEPALA SURAT PREVIEW -->
                        <div class="flex justify-between items-start mb-3 text-[9.5px]">
                            <table class="leading-tight">
                                <tr>
                                    <td class="w-14 text-zinc-500">Nomor</td>
                                    <td class="w-2">:</td>
                                    <td class="font-bold font-mono text-zinc-900"
                                        x-text="nomorSurat || '.../MDT-HS/...'">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-zinc-500">Sifat</td>
                                    <td>:</td>
                                    <td x-text="sifatSurat"></td>
                                </tr>
                                <tr>
                                    <td class="text-zinc-500">Lampiran</td>
                                    <td>:</td>
                                    <td x-text="lampiran"></td>
                                </tr>
                                <tr>
                                    <td class="text-zinc-500">Perihal</td>
                                    <td>:</td>
                                    <td class="font-bold text-zinc-900" x-text="perihal || 'Perihal Surat'"></td>
                                </tr>
                            </table>
                            <div class="text-right leading-tight">
                                <div class="font-semibold text-zinc-700"
                                    x-text="tempatTerbit + ', ' + formatTanggalMasehi(tanggalSurat)"></div>
                                <div class="text-[8.5px] text-emerald-700 font-medium mt-0.5"
                                    x-text="tanggalHijriyah">
                                </div>
                            </div>
                        </div>

                        <!-- KEPADA YTH -->
                        <div class="mb-3 text-[9.5px] leading-tight">
                            <div class="text-zinc-500">Kepada Yth.</div>
                            <div class="font-extrabold text-[10.5px] text-zinc-900"
                                x-text="currentPreviewTujuan || '(Nama Penerima / Wali Murid)'"></div>
                            <div class="text-zinc-500" x-text="currentPreviewAlamat || 'Di Tempat'"></div>
                        </div>

                        <!-- SALAM PEMBUKA & PENGANTAR -->
                        <p class="mb-1 text-[9.5px]"><em>Assalamualaikum War. Wab.</em></p>
                        <p class="mb-1 text-[9.5px]">Dengan hormat,</p>
                        <p class="mb-1.5 text-[9.5px]" style="text-indent: 15px;">
                            Puji syukur Alhamdulillah kami ucapkan kehadirat Allah SWT yang telah melimpahkan rahmat dan
                            hidayah-Nya kepada kita semua. Sholawat dan salam tercurah kepada Nabi Muhammad SAW serta
                            keluarga dan para sahabatnya.
                        </p>

                        <!-- ISI DINAMIS REALTIME SESUAI TEMPLATE -->
                        <div class="space-y-2 text-[9.5px] text-justify leading-relaxed">

                            <!-- 1. PANGGILAN -->
                            <template x-if="jenis === 'surat_panggilan'">
                                <div>
                                    <p>Sehubungan dengan keperluan <strong
                                            x-text="alasanPanggilan || 'evaluasi perkembangan dan tata tertib murid'"></strong>,
                                        kami mengharap kehadiran Bapak/Ibu/Wali murid dari:</p>

                                    <template x-if="activePreviewMurid">
                                        <div
                                            class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                            <div class="flex"><span class="w-28 text-zinc-500">Nama
                                                    Murid:</span><strong
                                                    x-text="activePreviewMurid.nama_lengkap"></strong>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">NISM:</span><span
                                                    class="font-mono" x-text="activePreviewMurid.nism"></span></div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Kelas /
                                                    Ruangan:</span><span
                                                    x-text="activePreviewMurid.ruangan_nama"></span>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Orang Tua /
                                                    Wali:</span><span x-text="activePreviewMurid.nama_wali"></span>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Dusun /
                                                    Kampung:</span><span x-text="activePreviewMurid.alamat"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <p class="mt-1">Untuk hadir menghadap pada:</p>
                                    <div
                                        class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                        <div class="flex"><span class="w-24 text-zinc-500">Hari / Tgl:</span><span
                                                class="font-bold"
                                                x-text="(hariPanggilan || '-') + ', ' + formatTanggalMasehi(tanggalPanggilan)"></span>
                                        </div>
                                        <div class="flex"><span class="w-24 text-zinc-500">Waktu:</span><span
                                                x-text="waktuPanggilan || '-'"></span></div>
                                        <div class="flex"><span class="w-24 text-zinc-500">Tempat:</span><span
                                                x-text="tempatMenghadap || '-'"></span></div>
                                        <div class="flex"><span class="w-24 text-zinc-500">Menghadap:</span><span
                                                class="font-semibold" x-text="menghadapKepada || '-'"></span></div>
                                    </div>
                                </div>
                            </template>

                            <!-- 2. PERINGATAN (SP) -->
                            <template x-if="jenis === 'surat_peringatan'">
                                <div>
                                    <p>Berdasarkan hasil evaluasi kedisiplinan madrasah, dengan ini kami menerbitkan
                                        <strong class="text-rose-700" x-text="tingkatSp"></strong> kepada:
                                    </p>

                                    <template x-if="activePreviewMurid">
                                        <div
                                            class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                            <div class="flex"><span class="w-28 text-zinc-500">Nama
                                                    Murid:</span><strong
                                                    x-text="activePreviewMurid.nama_lengkap"></strong>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">NISM:</span><span
                                                    class="font-mono" x-text="activePreviewMurid.nism"></span></div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Kelas /
                                                    Ruangan:</span><span
                                                    x-text="activePreviewMurid.ruangan_nama"></span>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Orang Tua /
                                                    Wali:</span><span x-text="activePreviewMurid.nama_wali"></span>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Dusun /
                                                    Kampung:</span><span x-text="activePreviewMurid.alamat"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <p class="mt-1">Atas pelanggaran:</p>
                                    <div
                                        class="bg-rose-50 p-2 rounded-lg my-1.5 border border-rose-200 text-[9px] space-y-0.5">
                                        <div><strong class="text-rose-700"
                                                x-text="bentukPelanggaran || '(Bentuk uraian pelanggaran...)'"></strong>
                                        </div>
                                        <div class="text-[8.5px] text-zinc-600" x-show="poinTatib"
                                            x-text="'Poin Tatib: ' + poinTatib"></div>
                                        <div class="text-[8.5px] text-zinc-600" x-show="tindakanPembinaan"
                                            x-text="'Tindakan: ' + tindakanPembinaan"></div>
                                        <div class="text-[8.5px] text-zinc-600" x-show="batasWaktuPembinaan"
                                            x-text="'Masa Pembinaan: ' + batasWaktuPembinaan"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- 3. PEMBERITAHUAN -->
                            <template x-if="jenis === 'surat_pemberitahuan'">
                                <div>
                                    <p>Bersama ini kami sampaikan pemberitahuan mengenai hal berikut:</p>

                                    <template x-if="activePreviewMurid">
                                        <div
                                            class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                            <div class="flex"><span class="w-28 text-zinc-500">Nama
                                                    Murid:</span><strong
                                                    x-text="activePreviewMurid.nama_lengkap"></strong>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">NISM:</span><span
                                                    class="font-mono" x-text="activePreviewMurid.nism"></span></div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Kelas /
                                                    Ruangan:</span><span
                                                    x-text="activePreviewMurid.ruangan_nama"></span>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Orang Tua /
                                                    Wali:</span><span x-text="activePreviewMurid.nama_wali"></span>
                                            </div>
                                            <div class="flex"><span class="w-28 text-zinc-500">Dusun /
                                                    Kampung:</span><span x-text="activePreviewMurid.alamat"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px]">
                                        <div class="font-bold text-zinc-800"
                                            x-text="pokokPemberitahuan || '(Pokok uraian pemberitahuan...)'"></div>
                                        <div class="text-zinc-500 mt-0.5" x-show="jadwalTerkait"
                                            x-text="'Jadwal: ' + jadwalTerkait"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- 4. EDARAN -->
                            <template x-if="jenis === 'surat_edaran'">
                                <div>
                                    <div class="text-center font-bold uppercase my-1 text-[10px]"
                                        x-text="pokokMaklumat || 'SURAT EDARAN RESMI'"></div>
                                    <div class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[8.5px] font-mono whitespace-pre-line"
                                        x-text="instruksiPoin || '(Poin-poin instruksi...)'"></div>
                                </div>
                            </template>

                            <!-- 5. PERMOHONAN IZIN -->
                            <template x-if="jenis === 'surat_permohonan_izin'">
                                <div>
                                    <p>Sehubungan dengan pelaksanaan kegiatan <strong
                                            x-text="namaKegiatan || '(Nama Kegiatan)'"></strong>, kami mengajukan
                                        permohonan izin fasilitas pada:</p>
                                    <div
                                        class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                        <div><strong
                                                x-text="(hariKegiatan || '-') + ', ' + formatTanggalMasehi(tanggalKegiatan)"></strong>
                                            (<span x-text="waktuKegiatan"></span>)</div>
                                        <div x-text="'Tempat: ' + (tempatKegiatan || '-')"></div>
                                        <div x-text="'Permohonan: ' + (fasilitasDimohonkan || '-')"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- 6. DISPENSASI -->
                            <template x-if="jenis === 'surat_dispensasi'">
                                <div>
                                    <template x-if="isDispensasiMassal">
                                        <div>
                                            <p>Sehubungan dengan adanya <strong
                                                    x-text="namaKegiatanDispensasi || 'kegiatan resmi madrasah'"></strong>
                                                yang diadakan oleh MDT Hidayatus Shibyan, yang dilaksanakan pada:</p>

                                            <div
                                                class="bg-zinc-50 dark:bg-zinc-800/40 p-2.5 rounded-lg my-2 border border-zinc-200 dark:border-zinc-700 text-[9.5px] space-y-1">
                                                <div class="flex" x-show="hariKegiatanDispensasi">
                                                    <span class="w-20 text-zinc-500">Hari:</span>
                                                    <strong x-text="hariKegiatanDispensasi"></strong>
                                                </div>
                                                <div class="flex">
                                                    <span class="w-20 text-zinc-500">Tanggal:</span>
                                                    <strong
                                                        x-text="formatTanggalMasehi(tanggalMulaiDispensasi) + (tanggalSelesaiDispensasi && tanggalSelesaiDispensasi !== tanggalMulaiDispensasi ? ' s.d. ' + formatTanggalMasehi(tanggalSelesaiDispensasi) : '')"></strong>
                                                </div>
                                                <div class="flex" x-show="waktuKegiatanDispensasi">
                                                    <span class="w-20 text-zinc-500">Waktu:</span>
                                                    <span x-text="waktuKegiatanDispensasi"></span>
                                                </div>
                                                <div class="flex">
                                                    <span class="w-20 text-zinc-500">Tempat:</span>
                                                    <span
                                                        x-text="tempatKegiatanDispensasi || 'MDT Hidayatus Shibyan'"></span>
                                                </div>
                                            </div>

                                            <p class="mt-2">
                                                Maka kami selaku Pengasuh MDT Hidayatus Shibyan Desa Somorkoneng
                                                Kecamatan
                                                Kwanyar Kabupaten Bangkalan mengajukan permohonan dispensasi untuk
                                                <strong
                                                    x-text="permohonanDispensasiKhusus || 'dipulangkan lebih awal agar Murid dapat mempersiapkan diri'"></strong>.
                                                Adapun Murid yang mengikuti <span
                                                    x-text="namaKegiatanDispensasi || 'kegiatan tersebut'"></span>
                                                akan
                                                disebut sebagaimana terlampir.
                                            </p>
                                        </div>
                                    </template>

                                    <template x-if="!isDispensasiMassal">
                                        <div>
                                            <p>Yang bertanda tangan di bawah ini Pengasuh MDT Hidayatus Shibyan
                                                menerangkan
                                                bahwa murid di bawah ini:</p>

                                            <template x-if="activePreviewMurid">
                                                <div
                                                    class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                                    <div class="flex"><span class="w-28 text-zinc-500">Nama
                                                            Murid:</span><strong
                                                            x-text="activePreviewMurid.nama_lengkap"></strong>
                                                    </div>
                                                    <div class="flex"><span
                                                            class="w-28 text-zinc-500">NISM:</span><span
                                                            class="font-mono"
                                                            x-text="activePreviewMurid.nism"></span>
                                                    </div>
                                                    <div class="flex"><span class="w-28 text-zinc-500">Kelas /
                                                            Ruangan:</span><span
                                                            x-text="activePreviewMurid.ruangan_nama"></span>
                                                    </div>
                                                    <div class="flex"><span class="w-28 text-zinc-500">Orang Tua /
                                                            Wali:</span><span
                                                            x-text="activePreviewMurid.nama_wali"></span>
                                                    </div>
                                                    <div class="flex"><span class="w-28 text-zinc-500">Dusun /
                                                            Kampung:</span><span
                                                            x-text="activePreviewMurid.alamat"></span>
                                                    </div>
                                                </div>
                                            </template>

                                            <p class="mt-2">Diberikan izin dan dispensasi kehadiran karena mengikuti
                                                kegiatan <strong
                                                    x-text="namaKegiatanDispensasi || '(Nama Kegiatan)'"></strong>
                                                pada:
                                            </p>

                                            <div
                                                class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px]">
                                                <div class="font-bold"
                                                    x-text="formatTanggalMasehi(tanggalMulaiDispensasi) + (tanggalSelesaiDispensasi && tanggalSelesaiDispensasi !== tanggalMulaiDispensasi ? ' s/d ' + formatTanggalMasehi(tanggalSelesaiDispensasi) : '') + (jumlahHari ? ' ('+jumlahHari+')' : '')">
                                                </div>
                                                <div class="text-zinc-600 mt-0.5" x-show="alasanDispensasi"
                                                    x-text="alasanDispensasi"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- 7. UNDANGAN -->
                            <template x-if="jenis === 'surat_undangan'">
                                <div>
                                    <p>Mengharap dengan hormat kehadiran Bapak/Ibu/Saudara pada kegiatan <strong
                                            x-text="namaAcara || '(Nama Agenda)'"></strong> pada:</p>
                                    <div
                                        class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                        <div><strong
                                                x-text="(hariAcara || '-') + ', ' + formatTanggalMasehi(tanggalAcara)"></strong>
                                            • <span x-text="waktuAcara"></span></div>
                                        <div x-text="'Tempat: ' + (tempatAcara || '-')"></div>
                                        <div x-text="'Pakaian: ' + (pakaianDresscode || '-')"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- Narasi Tambahan -->
                            <p x-show="isiSurat" class="text-zinc-700 italic" x-text="isiSurat"></p>

                            <p>Demikian surat ini kami susun. Atas perhatiannya dan kerja samanya kami menyampaikan
                                terima
                                kasih dan mohon dimaklumi adanya.</p>
                            <p><em>Wassalamualaikum War. Wab.</em></p>
                        </div>

                        <!-- TANDA TANGAN PREVIEW (FORMAT QR CODE SK ARSIP) -->
                        <div class="mt-4 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <div x-show="tembusan" class="text-[8px] text-zinc-500 mb-2">
                                <strong>Tembusan:</strong> <span x-text="tembusan"></span>
                            </div>

                            <div x-show="activeSigners.length === 0"
                                class="text-center py-3 text-[10px] text-amber-600 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/40">
                                ⚠️ Pilih minimal 1 pejabat penandatangan aktif di formulir.
                            </div>

                            <!-- Multi-column Signature Table Layout (Matching sk_arsip) -->
                            <div class="grid gap-2 text-center"
                                :class="{
                                    'grid-cols-1 max-w-[200px] ml-auto': activeSigners.length === 1,
                                    'grid-cols-2': activeSigners.length === 2,
                                    'grid-cols-3': activeSigners.length === 3,
                                    'grid-cols-4': activeSigners.length === 4
                                }">
                                <template x-for="s in activeSigners" :key="s.key">
                                    <div
                                        class="flex flex-col items-center justify-between p-2 rounded-xl bg-zinc-50/70 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 min-h-[135px]">
                                        <div class="text-[8.5px] leading-tight">
                                            <div class="text-zinc-400"
                                                x-text="s.label_atas || (s.key === 'pengasuh' ? 'Mengesahkan,' : 'Mengetahui,')">
                                            </div>
                                            <div class="font-bold text-zinc-800 dark:text-zinc-200 mt-0.5"
                                                x-text="s.jabatan"></div>
                                        </div>

                                        <!-- Simulated QR Code -->
                                        <div
                                            class="my-1.5 p-1 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded shadow-2xs flex flex-col items-center">
                                            <i
                                                class="bi bi-qr-code text-xl text-emerald-600 dark:text-emerald-400"></i>
                                            <span class="text-[6.5px] font-mono text-zinc-400 tracking-tighter">QR
                                                VERIFIED</span>
                                        </div>

                                        <div class="text-[8.5px] leading-tight">
                                            <div class="font-extrabold underline text-zinc-900 dark:text-white"
                                                x-text="s.nama"></div>
                                            <div class="text-[7.5px] text-zinc-500 mt-0.5"
                                                x-show="s.nip && s.nip !== '-'" x-text="'NIP. ' + s.nip"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- KETERANGAN PENGESAHAN ELEKTRONIK (FORMAT SEPERTI REFERENSI) -->
                            <div
                                class="mt-4 pt-2.5 border-t border-zinc-200 dark:border-zinc-700 flex items-center gap-2.5">
                                <div
                                    class="shrink-0 bg-white dark:bg-zinc-800 p-1 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-2xs flex flex-col items-center">
                                    <i class="bi bi-qr-code text-base text-emerald-600 dark:text-emerald-400"></i>
                                </div>
                                <div
                                    class="text-[7.5px] text-zinc-500 dark:text-zinc-400 leading-tight text-justify flex-1">
                                    Dokumen ini ditandatangani secara elektronik oleh Pejabat Berwenang MDT Hidayatus
                                    Shibyan dan distempel digital resmi oleh Sistem Informasi Persuratan MDTHS. Untuk
                                    verifikasi, kunjungi <span
                                        class="underline text-emerald-600 dark:text-emerald-400 font-semibold">{{ url('/') }}</span>
                                    dan masukkan nomor surat, atau scan QRCode di samping.
                                </div>
                            </div>
                            <div class="text-center text-[7.5px] text-zinc-400 mt-1 font-medium">
                                <span x-text="(hasLampiran || isDispensasiMassal) ? '1 dari 2' : '1 dari 1'"></span>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- PREVIEW HALAMAN 2: LEMBAR LAMPIRAN RESMI   -->
                        <!-- ========================================== -->
                        <template x-if="hasLampiran || isDispensasiMassal">
                            <div class="mt-4 pt-3 border-t-2 border-dashed border-emerald-500/40">
                                <div class="flex items-center justify-between mb-2 px-1">
                                    <span
                                        class="text-[10px] font-black uppercase text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                        <i class="bi bi-file-earmark-ruled-fill"></i> Halaman 2: Lembar Lampiran Resmi
                                    </span>
                                    <span
                                        class="text-[9px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold">
                                        Lampiran Cetak
                                    </span>
                                </div>

                                <div
                                    class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm text-zinc-800 dark:text-zinc-200 text-[10px] leading-relaxed space-y-3 font-serif">

                                    <!-- Header Identitas Lampiran Resmi (Kanan Atas) -->
                                    <div class="flex justify-end text-[8.5px] leading-tight font-sans">
                                        <table class="text-left">
                                            <tr>
                                                <td class="font-bold pr-2 text-zinc-500">Lampiran</td>
                                                <td class="pr-1">:</td>
                                                <td class="font-semibold text-zinc-800 dark:text-zinc-200"
                                                    x-text="computedLampiranJudul">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="font-bold pr-2 text-zinc-500">Nomor</td>
                                                <td class="pr-1">:</td>
                                                <td class="font-mono" x-text="nomorSurat"></td>
                                            </tr>
                                            <tr>
                                                <td class="font-bold pr-2 text-zinc-500">Tanggal</td>
                                                <td class="pr-1">:</td>
                                                <td x-text="formatTanggalMasehi(tanggalSurat)"></td>
                                            </tr>
                                            <tr>
                                                <td class="font-bold pr-2 text-zinc-500">Tentang</td>
                                                <td class="pr-1">:</td>
                                                <td class="font-semibold" x-text="perihal"></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="border-b-2 border-zinc-900 dark:border-zinc-100 my-2.5"></div>

                                    <!-- Judul Lampiran -->
                                    <div class="text-center my-3 font-sans">
                                        <h4 class="font-black text-[11px] underline uppercase tracking-wide text-zinc-900 dark:text-white"
                                            x-text="computedLampiranJudul">
                                        </h4>
                                    </div>

                                    <template x-if="isDispensasiMassal">
                                        <div>
                                            <!-- TABEL DAFTAR NAMA MURID & KELAS LEMBAGA TUJUAN -->
                                            <div class="overflow-x-auto my-3 font-sans">
                                                <table
                                                    class="w-full text-left text-[9.5px] border-collapse border border-zinc-900 dark:border-zinc-100">
                                                    <thead>
                                                        <tr
                                                            class="bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white">
                                                            <th
                                                                class="border border-zinc-900 dark:border-zinc-100 px-2 py-1 text-center w-10 font-bold">
                                                                No.</th>
                                                            <th
                                                                class="border border-zinc-900 dark:border-zinc-100 px-3 py-1.5 font-bold">
                                                                Nama Murid</th>
                                                            <th
                                                                class="border border-zinc-900 dark:border-zinc-100 px-3 py-1.5 text-center w-36 font-bold">
                                                                Kelas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-zinc-900 dark:divide-zinc-100">
                                                        <template x-for="(id, idx) in bulkMuridIds"
                                                            :key="'table-preview-' + id">
                                                            <tr>
                                                                <td class="border border-zinc-900 dark:border-zinc-100 px-2 py-1 text-center"
                                                                    x-text="idx + 1"></td>
                                                                <td class="border border-zinc-900 dark:border-zinc-100 px-3 py-1.5 font-bold uppercase text-zinc-900 dark:text-zinc-100"
                                                                    x-text="getMuridInfo(id).nama_lengkap"></td>
                                                                <td class="border border-zinc-900 dark:border-zinc-100 px-3 py-1.5 text-center font-semibold text-zinc-800 dark:text-zinc-200"
                                                                    x-text="kelasLembaga[id] || '-'"></td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!isDispensasiMassal">
                                        <div>
                                            <!-- Isi Konten Lampiran -->
                                            <div class="bg-zinc-50 dark:bg-zinc-800/40 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-700/80 text-[9px] font-mono whitespace-pre-line leading-relaxed min-h-[100px] text-zinc-800 dark:text-zinc-200"
                                                x-text="lampiranKonten || '(Belum ada rincian konten lampiran yang ditulis...)'">
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Tanda Tangan Pengesahan Lampiran -->
                                    <div class="pt-3">
                                        <div class="grid gap-2 text-center"
                                            :class="{
                                                'grid-cols-1 max-w-[180px] ml-auto': activeSigners.length === 1,
                                                'grid-cols-2': activeSigners.length === 2,
                                                'grid-cols-3': activeSigners.length === 3,
                                                'grid-cols-4': activeSigners.length === 4
                                            }">
                                            <template x-for="s in activeSigners" :key="'lampiran-' + s.key">
                                                <div
                                                    class="flex flex-col items-center justify-between p-1.5 rounded-lg bg-zinc-50/70 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 min-h-[110px]">
                                                    <div class="text-[8px] leading-tight">
                                                        <div class="text-zinc-400"
                                                            x-text="s.label_atas || (s.key === 'pengasuh' ? 'Mengesahkan,' : 'Mengetahui,')">
                                                        </div>
                                                        <div class="font-bold text-zinc-800 dark:text-zinc-200 mt-0.5"
                                                            x-text="s.jabatan"></div>
                                                    </div>
                                                    <!-- Simulated QR Code -->
                                                    <div
                                                        class="my-1 p-0.5 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded flex flex-col items-center">
                                                        <i
                                                            class="bi bi-qr-code text-sm text-emerald-600 dark:text-emerald-400"></i>
                                                    </div>
                                                    <div class="text-[8px] leading-tight font-bold underline text-zinc-900 dark:text-white"
                                                        x-text="s.nama"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Footer Pengesahan Elektronik Halaman 2 -->
                                    <div
                                        class="mt-4 pt-2 border-t border-zinc-200 dark:border-zinc-700 flex items-center gap-2">
                                        <div
                                            class="shrink-0 bg-white dark:bg-zinc-800 p-1 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-2xs flex flex-col items-center">
                                            <i
                                                class="bi bi-qr-code text-base text-emerald-600 dark:text-emerald-400"></i>
                                        </div>
                                        <div
                                            class="text-[7.5px] text-zinc-500 dark:text-zinc-400 leading-tight text-justify flex-1">
                                            Dokumen ini ditandatangani secara elektronik oleh Pejabat Berwenang MDT
                                            Hidayatus Shibyan dan distempel digital resmi oleh Sistem Informasi
                                            Persuratan
                                            MDTHS. Untuk verifikasi, kunjungi <span
                                                class="underline text-emerald-600 dark:text-emerald-400 font-semibold">{{ url('/') }}</span>
                                            dan masukkan nomor surat, atau scan QRCode di samping.
                                        </div>
                                    </div>
                                    <div class="text-center text-[7.5px] text-zinc-400 mt-1 font-medium">
                                        2 dari 2
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ALPINE.JS LOGIC -->
    <script>
        function formSurat() {
            return {
                activeMobileTab: 'form',
                targetMode: 'single',
                jenis: '{{ $selectedJenis }}',
                nomorSurat: '{{ $nomorInfo['nomor_surat'] }}',
                nomorAgenda: '{{ $nomorInfo['nomor_agenda'] }}',
                tanggalSurat: '{{ date('Y-m-d') }}',
                tanggalHijriyah: '{{ $todayInfo['hijri'] ?? '' }}',
                tempatTerbit: '{{ $duplicateSurat->tempat_terbit ?? 'Bangkalan' }}',
                perihal: '{{ addslashes($duplicateSurat->perihal ?? '') }}',
                tujuanSurat: '{{ addslashes($duplicateSurat->tujuan_surat ?? '') }}',
                alamatTujuan: '{{ addslashes($duplicateSurat->alamat_tujuan ?? 'Di Tempat') }}',
                sifatSurat: '{{ $duplicateSurat->sifat_surat ?? 'Biasa' }}',
                lampiran: '{{ $duplicateSurat->lampiran ?? '-' }}',
                hasLampiran: {{ !empty($duplicateSurat->isi_spesifik['has_lampiran']) ? 'true' : 'false' }},
                lampiranJudul: '{{ addslashes($duplicateSurat->isi_spesifik['lampiran_judul'] ?? 'SUSUNAN ACARA & JADWAL KEGIATAN') }}',
                lampiranKonten: `{!! addslashes($duplicateSurat->isi_spesifik['lampiran_konten'] ?? '') !!}`,
                muridId: '{{ $duplicateSurat->murid_id ?? '' }}',
                muridDetail: null,

                // Konfigurasi 4 Pejabat Penandatangan Kustom (Pengasuh, Sekjen, Kabid, Admin)
                signers: @json($penandatanganConfig['signers'] ?? []),
                kabidOptions: @json($penandatanganConfig['kabid_options'] ?? []),
                adminOptions: @json($penandatanganConfig['admin_options'] ?? []),

                get activeSigners() {
                    return this.signers.filter(s => s.is_active);
                },

                get primarySigner() {
                    return this.activeSigners[0] || this.signers[0] || {
                        nama: 'Mikyal Adly',
                        jabatan: 'Pengasuh MDT Hidayatus Shibyan',
                        nip: '-'
                    };
                },

                // Specific Fields
                hariPanggilan: '{{ addslashes($duplicateSurat->isi_spesifik['hari_panggilan'] ?? '') }}',
                tanggalPanggilan: '{{ $duplicateSurat->isi_spesifik['tanggal_panggilan'] ?? '' }}',
                waktuPanggilan: '{{ addslashes($duplicateSurat->isi_spesifik['waktu_panggilan'] ?? '14.00 WIB s/d Selesai') }}',
                tempatMenghadap: '{{ addslashes($duplicateSurat->isi_spesifik['tempat_menghadap'] ?? 'Kantor TU MDT Hidayatus Shibyan') }}',
                menghadapKepada: '{{ addslashes($duplicateSurat->isi_spesifik['menghadap_kepada'] ?? 'Kepala Madrasah & Tim Kesiswaan') }}',
                alasanPanggilan: '{{ addslashes($duplicateSurat->isi_spesifik['alasan_panggilan'] ?? '') }}',

                tingkatSp: '{{ addslashes($duplicateSurat->isi_spesifik['tingkat_sp'] ?? 'Surat Peringatan I (SP 1)') }}',
                poinTatib: '{{ addslashes($duplicateSurat->isi_spesifik['poin_tatib_dilanggar'] ?? '') }}',
                bentukPelanggaran: '{{ addslashes($duplicateSurat->isi_spesifik['bentuk_pelanggaran'] ?? ($duplicateSurat->isi_spesifik['alasan_sp'] ?? '')) }}',
                tindakanPembinaan: '{{ addslashes($duplicateSurat->isi_spesifik['tindakan_pembinaan'] ?? '') }}',
                batasWaktuPembinaan: '{{ addslashes($duplicateSurat->isi_spesifik['batas_waktu_pembinaan'] ?? '') }}',

                kategoriPemberitahuan: '{{ addslashes($duplicateSurat->isi_spesifik['kategori_pemberitahuan'] ?? 'Akademik & KBM') }}',
                jadwalTerkait: '{{ addslashes($duplicateSurat->isi_spesifik['jadwal_terkait'] ?? '') }}',
                pokokPemberitahuan: '{{ addslashes($duplicateSurat->isi_spesifik['pokok_pemberitahuan'] ?? '') }}',

                nomorEdaranInternal: '{{ addslashes($duplicateSurat->isi_spesifik['nomor_edaran_internal'] ?? '') }}',
                berlakuMulai: '{{ addslashes($duplicateSurat->isi_spesifik['berlaku_mulai'] ?? 'Sejak tanggal ditetapkan') }}',
                pokokMaklumat: '{{ addslashes($duplicateSurat->isi_spesifik['pokok_maklumat'] ?? '') }}',
                instruksiPoin: '{{ addslashes($duplicateSurat->isi_spesifik['instruksi_poin'] ?? '') }}',

                namaKegiatan: '{{ addslashes($duplicateSurat->isi_spesifik['nama_kegiatan'] ?? '') }}',
                hariKegiatan: '{{ addslashes($duplicateSurat->isi_spesifik['hari_kegiatan'] ?? '') }}',
                tanggalKegiatan: '{{ $duplicateSurat->isi_spesifik['tanggal_kegiatan'] ?? '' }}',
                waktuKegiatan: '{{ addslashes($duplicateSurat->isi_spesifik['waktu_kegiatan'] ?? '08.00 WIB s/d Selesai') }}',
                tempatKegiatan: '{{ addslashes($duplicateSurat->isi_spesifik['tempat_kegiatan'] ?? '') }}',
                penanggungJawab: '{{ addslashes($duplicateSurat->isi_spesifik['penanggung_jawab'] ?? '') }}',
                fasilitasDimohonkan: '{{ addslashes($duplicateSurat->isi_spesifik['fasilitas_dimohonkan'] ?? '') }}',

                namaSekolahTujuan: '{{ addslashes($duplicateSurat->isi_spesifik['nama_sekolah_tujuan'] ?? '') }}',
                namaKegiatanDispensasi: '{{ addslashes($duplicateSurat->isi_spesifik['nama_kegiatan_dispensasi'] ?? '') }}',
                hariKegiatanDispensasi: '{{ addslashes($duplicateSurat->isi_spesifik['hari_kegiatan_dispensasi'] ?? '') }}',
                tanggalMulaiDispensasi: '{{ $duplicateSurat->isi_spesifik['tanggal_mulai_dispensasi'] ?? '' }}',
                tanggalSelesaiDispensasi: '{{ $duplicateSurat->isi_spesifik['tanggal_selesai_dispensasi'] ?? '' }}',
                waktuKegiatanDispensasi: '{{ addslashes($duplicateSurat->isi_spesifik['waktu_kegiatan_dispensasi'] ?? '13.30 WIB s/d Selesai') }}',
                tempatKegiatanDispensasi: '{{ addslashes($duplicateSurat->isi_spesifik['tempat_kegiatan_dispensasi'] ?? 'MDT Hidayatus Shibyan') }}',
                permohonanDispensasiKhusus: '{{ addslashes($duplicateSurat->isi_spesifik['permohonan_dispensasi_khusus'] ?? 'dipulangkan pukul 12.00 WIB agar Murid dapat mempersiapkan diri') }}',
                jumlahHari: '{{ addslashes($duplicateSurat->isi_spesifik['jumlah_hari'] ?? '') }}',
                alasanDispensasi: '{{ addslashes($duplicateSurat->isi_spesifik['alasan_kegiatan'] ?? '') }}',
                kelasLembaga: {},

                namaAcara: '{{ addslashes($duplicateSurat->isi_spesifik['nama_acara'] ?? '') }}',
                hariAcara: '{{ addslashes($duplicateSurat->isi_spesifik['hari_acara'] ?? '') }}',
                tanggalAcara: '{{ $duplicateSurat->isi_spesifik['tanggal_acara'] ?? '' }}',
                waktuAcara: '{{ addslashes($duplicateSurat->isi_spesifik['waktu_acara'] ?? '19.30 WIB (Ba\'da Isya) s/d Selesai') }}',
                tempatAcara: '{{ addslashes($duplicateSurat->isi_spesifik['tempat_acara'] ?? 'Aula MDT Hidayatus Shibyan') }}',
                pakaianDresscode: '{{ addslashes($duplicateSurat->isi_spesifik['pakaian_dresscode'] ?? 'Busana Muslim Rapi & Berpeci') }}',
                agendaAcara: '{{ addslashes($duplicateSurat->isi_spesifik['agenda_acara'] ?? '') }}',

                isiSurat: '{{ addslashes($duplicateSurat->isi_surat ?? '') }}',
                tembusan: '{{ addslashes($duplicateSurat->tembusan ?? '') }}',

                // Multi-Murid (Bulk) & Single Murid Filter Logic
                allMurids: @json($allMuridsData),
                singleRuanganFilter: '',
                singleSearch: '',
                bulkRuanganFilter: '',
                bulkSearch: '',
                bulkMuridIds: [],
                previewBulkActiveId: null,

                init() {
                    if (!this.perihal) {
                        this.updateDefaults();
                    }
                    if (this.muridId) {
                        this.onMuridChange();
                    }

                    this.$watch('hasLampiran', (val) => {
                        if (val && (this.lampiran === '-' || !this.lampiran)) {
                            this.lampiran = '1 Lembar';
                        } else if (!val && this.lampiran === '1 Lembar') {
                            this.lampiran = '-';
                        }
                    });

                    this.$watch('namaKegiatanDispensasi', (val) => {
                        if (this.jenis === 'surat_dispensasi') {
                            this.lampiranJudul = 'NAMA-NAMA MURID YANG MENGIKUTI ' + (val ? val.toUpperCase()
                                .trim() : 'KEGIATAN');
                        }
                    });

                    this.$watch('jenis', (val) => {
                        if (val === 'surat_dispensasi') {
                            this.lampiranJudul = 'NAMA-NAMA MURID YANG MENGIKUTI ' + (this.namaKegiatanDispensasi ?
                                this.namaKegiatanDispensasi.toUpperCase().trim() : 'KEGIATAN');
                        }
                    });
                },

                insertLampiranTemplate(type) {
                    if (type === 'agenda') {
                        this.lampiranJudul = 'SUSUNAN ACARA & JADWAL KEGIATAN';
                        this.lampiranKonten =
                            "1. Pembukaan & Pembacaan Tahlil / Sholawat\n2. Sambutan Pengasuh / Kepala MDTHS\n3. Laporan Akademik & Perkembangan KBM\n4. Musyawarah & Tanya Jawab Bersama Wali Murid\n5. Doa Penutup & Ramah Tamah";
                    } else if (type === 'poin') {
                        this.lampiranJudul = 'KETENTUAN & TATA TERTIB RESMI';
                        this.lampiranKonten =
                            "A. KETENTUAN UMUM\n1. Seluruh murid dan wali murid dimohon hadir 15 menit sebelum acara dimulai.\n2. Mengenakan busana muslim rapi, sopan, dan berpeci.\n\nB. KEWAJIBAN & PEMBINAAN\n1. Mengikuti seluruh rangkaian kegiatan dengan tertib dan khidmat.\n2. Menjaga kebersihan dan keamanan sarana prasarana madrasah.";
                    }
                },

                get computedLampiranJudul() {
                    if (this.isDispensasiMassal || this.jenis === 'surat_dispensasi') {
                        const keg = this.namaKegiatanDispensasi ? this.namaKegiatanDispensasi.toUpperCase().trim() :
                            'KEGIATAN';
                        return 'NAMA-NAMA MURID YANG MENGIKUTI ' + keg;
                    }
                    return this.lampiranJudul || 'RINCIAN LAMPIRAN SURAT';
                },

                get isMuridRelated() {
                    return ['surat_panggilan', 'surat_peringatan', 'surat_pemberitahuan', 'surat_dispensasi',
                        'surat_undangan'
                    ].includes(
                        this.jenis);
                },

                get isDispensasiMassal() {
                    return this.jenis === 'surat_dispensasi' && this.targetMode === 'bulk' && this.bulkMuridIds.length >
                        0;
                },

                get kategoriPenerima() {
                    if (this.jenis === 'surat_panggilan' || this.jenis === 'surat_pemberitahuan') return 'wali_murid';
                    if (this.jenis === 'surat_peringatan') return 'murid';
                    if (this.jenis === 'surat_dispensasi' || this.jenis === 'surat_permohonan_izin')
                        return 'lembaga_luar';
                    return 'umum';
                },

                get currentJenisNama() {
                    const map = {
                        'surat_panggilan': 'Surat Panggilan Murid/Wali',
                        'surat_peringatan': 'Surat Peringatan (SP)',
                        'surat_pemberitahuan': 'Surat Pemberitahuan Wali',
                        'surat_edaran': 'Surat Edaran Resmi',
                        'surat_permohonan_izin': 'Surat Permohonan Izin',
                        'surat_dispensasi': 'Surat Dispensasi Antar Lembaga',
                        'surat_undangan': 'Surat Undangan Resmi',
                    };
                    return map[this.jenis] || 'Surat';
                },

                get hasSingleFilter() {
                    return !!(this.singleRuanganFilter || this.singleSearch.trim());
                },

                get hasBulkFilter() {
                    return !!(this.bulkRuanganFilter || this.bulkSearch.trim());
                },

                get filteredSingleMurids() {
                    if (!this.hasSingleFilter) {
                        return [];
                    }
                    let list = this.allMurids;
                    if (this.singleRuanganFilter) {
                        list = list.filter(m => String(m.ruangan_id) === String(this.singleRuanganFilter));
                    }
                    if (this.singleSearch.trim()) {
                        const q = this.singleSearch.toLowerCase();
                        list = list.filter(m =>
                            (m.nama_lengkap && m.nama_lengkap.toLowerCase().includes(q)) ||
                            (m.nism && m.nism.toLowerCase().includes(q)) ||
                            (m.nama_wali && m.nama_wali.toLowerCase().includes(q)) ||
                            (m.ruangan_nama && m.ruangan_nama.toLowerCase().includes(q))
                        );
                    }
                    return list;
                },

                get filteredMurids() {
                    if (!this.hasBulkFilter) {
                        if (this.bulkMuridIds.length > 0) {
                            return this.allMurids.filter(m => this.bulkMuridIds.includes(m.id));
                        }
                        return [];
                    }
                    let list = this.allMurids;
                    if (this.bulkRuanganFilter) {
                        list = list.filter(m => String(m.ruangan_id) === String(this.bulkRuanganFilter));
                    }
                    if (this.bulkSearch.trim()) {
                        const q = this.bulkSearch.toLowerCase();
                        list = list.filter(m =>
                            (m.nama_lengkap && m.nama_lengkap.toLowerCase().includes(q)) ||
                            (m.nism && m.nism.toLowerCase().includes(q)) ||
                            (m.nama_wali && m.nama_wali.toLowerCase().includes(q)) ||
                            (m.ruangan_nama && m.ruangan_nama.toLowerCase().includes(q))
                        );
                    }
                    return list;
                },

                get activePreviewMurid() {
                    if (this.targetMode === 'bulk') {
                        const activeId = this.previewBulkActiveId || (this.bulkMuridIds.length > 0 ? this.bulkMuridIds[
                            0] : null);
                        if (activeId) {
                            return this.allMurids.find(m => m.id === activeId) || null;
                        }
                        return null;
                    }
                    return this.muridDetail ? {
                        id: this.muridId,
                        nama_lengkap: this.muridDetail.nama_lengkap,
                        nism: this.muridDetail.nism,
                        ruangan_nama: this.muridDetail.ruangan,
                        nama_wali: this.muridDetail.nama_wali,
                        alamat: this.muridDetail.alamat
                    } : null;
                },

                get currentPreviewTujuan() {
                    if (this.jenis === 'surat_dispensasi') {
                        return this.namaSekolahTujuan || this.tujuanSurat || 'Kepala Sekolah / Lembaga Terkait';
                    }
                    if (this.targetMode === 'bulk' && this.activePreviewMurid) {
                        const m = this.activePreviewMurid;
                        if (this.jenis === 'surat_panggilan' || this.jenis === 'surat_pemberitahuan' || this.jenis ===
                            'surat_undangan') {
                            return m.nama_wali && m.nama_wali !== '-' ?
                                `Bpk/Ibu/Wali dari ${m.nama_lengkap} (${m.nama_wali})` :
                                `Bpk/Ibu/Wali dari ${m.nama_lengkap}`;
                        } else if (this.jenis === 'surat_peringatan') {
                            return `Murid: ${m.nama_lengkap} (NISM: ${m.nism})`;
                        }
                    }
                    return this.tujuanSurat;
                },

                get currentPreviewAlamat() {
                    if (this.jenis === 'surat_dispensasi') {
                        return this.alamatTujuan || 'Di Tempat';
                    }
                    if (this.targetMode === 'bulk' && this.activePreviewMurid) {
                        return this.activePreviewMurid.alamat ? `Dsn. ${this.activePreviewMurid.alamat}` : 'Di Tempat';
                    }
                    return this.alamatTujuan;
                },

                get selectedRoomsCount() {
                    if (!this.bulkMuridIds || this.bulkMuridIds.length === 0) return 0;
                    const selectedMurids = this.allMurids.filter(m => this.bulkMuridIds.includes(m.id));
                    const uniqueRooms = new Set(selectedMurids.map(m => m.ruangan_id).filter(Boolean));
                    return uniqueRooms.size;
                },

                setTargetMode(mode) {
                    this.targetMode = mode;
                    if (mode === 'bulk' && this.bulkMuridIds.length > 0) {
                        this.previewBulkActiveId = this.bulkMuridIds[0];
                    }
                },

                toggleMuridBulk(id) {
                    if (this.bulkMuridIds.includes(id)) {
                        this.bulkMuridIds = this.bulkMuridIds.filter(x => x !== id);
                        if (this.previewBulkActiveId === id) {
                            this.previewBulkActiveId = this.bulkMuridIds.length > 0 ? this.bulkMuridIds[0] : null;
                        }
                    } else {
                        this.bulkMuridIds.push(id);
                        if (!this.previewBulkActiveId) {
                            this.previewBulkActiveId = id;
                        }
                    }
                },

                selectAllFiltered() {
                    const idsToAdd = this.filteredMurids.map(m => m.id);
                    const merged = Array.from(new Set([...this.bulkMuridIds, ...idsToAdd]));
                    this.bulkMuridIds = merged;
                    if (!this.previewBulkActiveId && this.bulkMuridIds.length > 0) {
                        this.previewBulkActiveId = this.bulkMuridIds[0];
                    }
                },

                clearBulkSelection() {
                    this.bulkMuridIds = [];
                    this.previewBulkActiveId = null;
                },

                getMuridName(id) {
                    const m = this.allMurids.find(x => x.id === id);
                    return m ? m.nama_lengkap : 'Murid #' + id;
                },

                getMuridInfo(id) {
                    const m = this.allMurids.find(x => x.id === id);
                    return m || {
                        id: id,
                        nama_lengkap: 'Murid #' + id,
                        nism: '-',
                        ruangan_nama: '-',
                        nama_wali: '-',
                        alamat: '-'
                    };
                },

                setJenis(val) {
                    this.jenis = val;
                    this.generateNomor();
                    this.updateDefaults();
                },

                updateDefaults() {
                    switch (this.jenis) {
                        case 'surat_panggilan':
                            this.perihal = 'Surat Panggilan Wali Murid';
                            this.sifatSurat = 'Penting';
                            break;
                        case 'surat_peringatan':
                            this.perihal = 'Surat Peringatan (SP) Murid';
                            this.sifatSurat = 'Penting';
                            break;
                        case 'surat_pemberitahuan':
                            this.perihal = 'Surat Pemberitahuan Wali Murid';
                            this.sifatSurat = 'Biasa';
                            break;
                        case 'surat_edaran':
                            this.perihal = 'Surat Edaran Kebijakan & Ketetapan Madrasah';
                            this.sifatSurat = 'Penting';
                            this.tujuanSurat = 'Seluruh Asatidz, Murid & Wali Murid MDTHS';
                            break;
                        case 'surat_permohonan_izin':
                            this.perihal = 'Permohonan Izin Tempat & Fasilitas';
                            this.sifatSurat = 'Biasa';
                            break;
                        case 'surat_dispensasi':
                            this.perihal = 'Permohonan Dispensasi Kehadiran Murid';
                            this.sifatSurat = 'Penting';
                            break;
                        case 'surat_undangan':
                            this.perihal = 'Undangan Kegiatan & Rapat Madrasah';
                            this.sifatSurat = 'Penting';
                            break;
                    }
                },

                async generateNomor() {
                    try {
                        const res = await fetch(
                            `{{ route('surat-keluar.api-generate-nomor') }}?jenis=${this.jenis}&tanggal=${this.tanggalSurat}`
                        );
                        const data = await res.json();
                        if (data.nomor_surat) {
                            this.nomorSurat = data.nomor_surat;
                            this.nomorAgenda = data.nomor_agenda;
                        }
                    } catch (e) {
                        console.error('Gagal generate nomor surat', e);
                    }
                },

                selectSingleMurid(id) {
                    this.muridId = id;
                    this.onMuridChange();
                },

                clearSingleMurid() {
                    this.muridId = '';
                    this.muridDetail = null;
                },

                async onMuridChange() {
                    if (!this.muridId) {
                        this.muridDetail = null;
                        return;
                    }
                    try {
                        const res = await fetch(`{{ url('persuratan/surat-keluar/api/murid') }}/${this.muridId}`);
                        const json = await res.json();
                        if (json.success) {
                            this.muridDetail = json.data;
                            const namaAyahWali = (json.data.nama_ayah && json.data.nama_ayah !== '-') ? json.data
                                .nama_ayah : json.data.nama_wali;
                            if (this.jenis === 'surat_panggilan' || this.jenis === 'surat_pemberitahuan' || this
                                .jenis === 'surat_undangan') {
                                this.tujuanSurat = namaAyahWali && namaAyahWali !== '-' ?
                                    `Bpk/Ibu/Wali dari ${json.data.nama_lengkap} (${namaAyahWali})` :
                                    `Bpk/Ibu/Wali dari ${json.data.nama_lengkap}`;
                                this.alamatTujuan = json.data.alamat ? `Dsn. ${json.data.alamat}` : 'Di Tempat';
                            } else if (this.jenis === 'surat_peringatan') {
                                this.tujuanSurat = `Murid: ${json.data.nama_lengkap} (NISM: ${json.data.nism})`;
                            }
                        }
                    } catch (e) {
                        console.error('Gagal load detail murid', e);
                    }
                },



                toggleSigner(key) {
                    const s = this.signers.find(x => x.key === key);
                    if (s) s.is_active = !s.is_active;
                },

                setKabidPreset(kabidId) {
                    const found = this.kabidOptions.find(k => String(k.id) === String(kabidId));
                    const kabidSigner = this.signers.find(s => s.key === 'kabid');
                    if (found && kabidSigner) {
                        kabidSigner.id_relasi = found.id;
                        kabidSigner.nama = found.nama;
                        kabidSigner.jabatan = found.jabatan + (found.tingkat ? ' Tingkat ' + found.tingkat : '');
                        kabidSigner.nip = found.nip;
                        kabidSigner.is_active = true;
                    }
                },

                setAdminPreset(adminId) {
                    const found = this.adminOptions.find(a => String(a.id) === String(adminId));
                    const adminSigner = this.signers.find(s => s.key === 'admin');
                    if (found && adminSigner) {
                        adminSigner.id_relasi = found.id;
                        adminSigner.nama = found.nama;
                        adminSigner.jabatan = found.jabatan;
                        adminSigner.nip = found.nip;
                        adminSigner.is_active = true;
                    }
                },

                formatTanggalMasehi(tglStr) {
                    if (!tglStr) return '-';
                    try {
                        const d = new Date(tglStr);
                        if (isNaN(d.getTime())) return tglStr;
                        const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                            'September', 'Oktober', 'November', 'Desember'
                        ];
                        return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
                    } catch (e) {
                        return tglStr;
                    }
                }
            };
        }
    </script>
</x-app-layout>
