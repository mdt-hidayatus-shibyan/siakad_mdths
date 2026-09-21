@section('title', 'Edit Surat: ' . $surat->nomor_surat)

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat-keluar.show', $surat->id) }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white"
                title="Kembali ke Detail Surat">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    Edit Surat Keluar
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Nomor: <span
                        class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ $surat->nomor_surat }}</span> •
                    {{ $surat->nama_jenis }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('surat-keluar.show', $surat->id) }}"
                class="px-4 py-2 bg-white/80 dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm flex items-center gap-2 active:scale-95">
                <i class="bi bi-eye-fill text-zinc-500"></i>
                <span>Lihat Detail</span>
            </a>
            <a href="{{ route('surat-keluar.cetak', $surat->id) }}" target="_blank"
                class="m3-btn-primary h-10 px-4.5 group/btn shrink-0">
                <i class="bi bi-printer-fill text-sm"></i>
                <span>Cetak Surat</span>
            </a>
        </div>
    </div>

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

    @php
        $spesifik = $surat->isi_spesifik ?? [];

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

        $currentMurid = $surat->murid;
        $currentMuridData = $currentMurid
            ? [
                'id' => $currentMurid->id,
                'nama_lengkap' => $currentMurid->nama_lengkap,
                'nism' => $currentMurid->nism ?? '-',
                'ruangan_id' => $currentMurid->ruangan_masuk ?? ($currentMurid->ruangans->first()->id ?? null),
                'ruangan_nama' => $currentMurid->nama_ruangan_aktif ?? '-',
                'nama_wali' =>
                    $currentMurid->nama_ayah ?:
                    ($currentMurid->waliMurid?->nama_kepala_keluarga ?:
                    ($currentMurid->nama_ibu ?:
                    ($currentMurid->waliMurid?->nama_lengkap ?:
                    '-'))),
                'alamat' =>
                    $currentMurid->waliMurid?->kampung?->nama_kampung ?? ($currentMurid->alamat ?? 'Somorkoneng'),
            ]
            : null;
    @endphp

    <div x-data="formEditSurat()" x-init="init()" class="space-y-6">

        <!-- TAB SWITCHER MOBILE -->
        <div class="lg:hidden flex items-center gap-2 p-1.5 bg-zinc-200/60 dark:bg-zinc-800/80 rounded-2xl">
            <button type="button" @click="activeMobileTab = 'form'"
                :class="activeMobileTab === 'form' ? 'bg-amber-600 text-white shadow-sm' :
                    'text-zinc-600 dark:text-zinc-400'"
                class="flex-1 py-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                <i class="bi bi-pencil-square"></i>
                <span>Formulir Edit</span>
            </button>
            <button type="button" @click="activeMobileTab = 'preview'"
                :class="activeMobileTab === 'preview' ? 'bg-amber-600 text-white shadow-sm' :
                    'text-zinc-600 dark:text-zinc-400'"
                class="flex-1 py-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                <i class="bi bi-eye-fill"></i>
                <span>Pratinjau Live Surat</span>
            </button>
        </div>

        <!-- 2-COLUMN LAYOUT: FORM EDIT & LIVE PREVIEW -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- KOLOM KIRI: FORMULIR (7 COLUMNS) -->
            <div class="lg:col-span-7 space-y-6" x-show="activeMobileTab === 'form' || window.innerWidth >= 1024">
                <form action="{{ route('surat-keluar.update', $surat->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- INFORMASI NOMOR & KEPALA -->
                    <div class="m3-glass-card p-5">
                        <h3
                            class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2 mb-4 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <i class="bi bi-card-heading text-emerald-500"></i> Informasi Nomor & Kepala Surat
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Nomor Surat Resmi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="nomor_surat" x-model="nomorSurat" required
                                    class="m3-input-glass w-full font-mono text-xs font-bold">
                            </div>

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

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tanggal Terbit (Masehi) <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal_surat" x-model="tanggalSurat" required
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tanggal Terbit (Hijriyah)
                                </label>
                                <input type="text" name="tanggal_hijriyah" x-model="tanggalHijriyah"
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Lampiran
                                </label>
                                <input type="text" name="lampiran" x-model="lampiran"
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Perihal Surat <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="perihal" x-model="perihal" required
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <!-- PENERIMA / TUJUAN -->
                    <div class="m3-glass-card p-5">
                        <h3
                            class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2 mb-4 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <i class="bi bi-person-lines-fill text-emerald-500"></i> Penerima / Tujuan Surat
                        </h3>

                        @if (in_array($surat->jenis_surat, ['surat_panggilan', 'surat_peringatan', 'surat_pemberitahuan', 'surat_dispensasi']) &&
                                !$surat->is_dispensasi_massal)
                            <div
                                class="mb-4 p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label
                                            class="text-[11px] font-black text-zinc-800 dark:text-zinc-200 flex items-center gap-1.5">
                                            <i class="bi bi-mortarboard-fill text-emerald-500"></i> Relasi Data Murid
                                        </label>
                                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                                            Filter kelas & cari berdasarkan NISM / Nama / Wali untuk mengubah relasi
                                            murid.
                                        </p>
                                    </div>
                                    <template x-if="muridId">
                                        <button type="button" @click="clearSingleMurid()"
                                            class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 font-bold text-[10px] transition-all flex items-center gap-1 cursor-pointer">
                                            <i class="bi bi-x-circle"></i> Lepas Relasi Murid
                                        </button>
                                    </template>
                                </div>

                                <!-- Filter & Search Murid -->
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

                                <!-- List Pilihan Murid -->
                                <div
                                    class="max-h-52 overflow-y-auto space-y-1.5 pr-1 border border-zinc-200 dark:border-zinc-800 rounded-xl p-2 bg-white/70 dark:bg-zinc-900/70">

                                    <!-- Panduan Jika Belum Filter -->
                                    <div x-show="!hasSingleFilter" class="text-center py-6 px-4">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto mb-2 text-base">
                                            <i class="bi bi-funnel"></i>
                                        </div>
                                        <div class="font-bold text-xs text-zinc-600 dark:text-zinc-300">Pilih Ruangan
                                            atau Ketik Pencarian</div>
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
                                        <div class="font-bold text-xs text-zinc-600 dark:text-zinc-300">Data Murid
                                            Tidak Ditemukan</div>
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
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">
                                            Murid Terpilih
                                        </span>
                                    </div>
                                    <div class="text-[10.5px] text-zinc-600 dark:text-zinc-400 mt-1"
                                        x-text="muridDetail ? 'NISM: ' + (muridDetail.nism || '-') + ' • Kelas: ' + (muridDetail.ruangan_nama || muridDetail.ruangan || '-') + ' • Wali: ' + (muridDetail.nama_ayah && muridDetail.nama_ayah !== '-' ? muridDetail.nama_ayah : (muridDetail.nama_wali || '-')) + ' (' + (muridDetail.alamat || '-') + ')' : ''">
                                    </div>
                                </div>
                            </div>
                        @endif



                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Ditujukan Kepada (Yth.) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="tujuan_surat" x-model="tujuanSurat" required
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Alamat / Tempat Tujuan
                                </label>
                                <input type="text" name="alamat_tujuan" x-model="alamatTujuan"
                                    class="m3-input-glass w-full text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <!-- FORM SPESIFIK SESUAI JENIS SURAT -->
                    <div class="m3-glass-card p-5">
                        <h3
                            class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2 mb-4 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <i class="bi bi-sliders text-emerald-500"></i> Rincian Konten: <span
                                class="text-amber-600 dark:text-amber-400">{{ $surat->nama_jenis }}</span>
                        </h3>

                        <!-- INFORMASI IDENTITAS MURID TERKAIT (NAMA, NISM, RUANGAN, WALI, KAMPUNG) -->
                        <div x-show="isMuridRelated" class="mb-4">
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
                                            <strong class="text-zinc-900 dark:text-white block truncate text-[11px]"
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
                                    <span>Pilih murid pada bagian relasi penerima di atas untuk memuat data identitas
                                        anak secara otomatis.</span>
                                </div>
                            </template>
                        </div>

                        @if ($surat->jenis_surat === 'surat_panggilan')
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Hari
                                            Menghadap</label>
                                        <input type="text" name="hari_panggilan" x-model="hariPanggilan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal
                                            Menghadap</label>
                                        <input type="date" name="tanggal_panggilan" x-model="tanggalPanggilan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Waktu
                                            / Jam</label>
                                        <input type="text" name="waktu_panggilan" x-model="waktuPanggilan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tempat
                                            Menghadap</label>
                                        <input type="text" name="tempat_menghadap" x-model="tempatMenghadap"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Menghadap
                                            Kepada</label>
                                        <input type="text" name="menghadap_kepada" x-model="menghadapKepada"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Alasan
                                        / Keperluan Pemanggilan</label>
                                    <textarea name="alasan_panggilan" x-model="alasanPanggilan" rows="3"
                                        class="m3-input-glass w-full text-xs font-medium"></textarea>
                                </div>
                            </div>
                        @elseif ($surat->jenis_surat === 'surat_peringatan')
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tingkat
                                            SP</label>
                                        <select name="tingkat_sp" x-model="tingkatSp"
                                            class="m3-input-glass w-full text-xs font-bold">
                                            <option value="Surat Peringatan I (SP 1)">Surat Peringatan I (SP 1)
                                            </option>
                                            <option value="Surat Peringatan II (SP 2)">Surat Peringatan II (SP 2)
                                            </option>
                                            <option value="Surat Peringatan III (SP 3)">Surat Peringatan III (SP 3)
                                            </option>
                                            <option value="Surat Peringatan Keras / Terakhir">Surat Peringatan Keras /
                                                Terakhir</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Poin
                                            / Pasal Tatib</label>
                                        <input type="text" name="poin_tatib_dilanggar" x-model="poinTatib"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Uraian
                                        & Bentuk Pelanggaran</label>
                                    <textarea name="bentuk_pelanggaran" x-model="bentukPelanggaran" rows="3"
                                        class="m3-input-glass w-full text-xs font-medium"></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tindakan
                                            Pembinaan</label>
                                        <input type="text" name="tindakan_pembinaan" x-model="tindakanPembinaan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Batas
                                            Waktu Pembinaan</label>
                                        <input type="text" name="batas_waktu_pembinaan"
                                            x-model="batasWaktuPembinaan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                            </div>
                        @elseif ($surat->jenis_surat === 'surat_pemberitahuan')
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Kategori
                                            Pemberitahuan</label>
                                        <input type="text" name="kategori_pemberitahuan"
                                            x-model="kategoriPemberitahuan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Jadwal
                                            Terkait</label>
                                        <input type="text" name="jadwal_terkait" x-model="jadwalTerkait"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Pokok
                                        / Uraian Pemberitahuan</label>
                                    <textarea name="pokok_pemberitahuan" x-model="pokokPemberitahuan" rows="4"
                                        class="m3-input-glass w-full text-xs font-medium"></textarea>
                                </div>
                            </div>
                        @elseif ($surat->jenis_surat === 'surat_edaran')
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nomor
                                            Edaran Internal</label>
                                        <input type="text" name="nomor_edaran_internal"
                                            x-model="nomorEdaranInternal"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Berlaku
                                            Mulai</label>
                                        <input type="text" name="berlaku_mulai" x-model="berlakuMulai"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Pokok
                                        Maklumat</label>
                                    <input type="text" name="pokok_maklumat" x-model="pokokMaklumat"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Poin-Poin
                                        Instruksi / Ketetapan</label>
                                    <textarea name="instruksi_poin" x-model="instruksiPoin" rows="5"
                                        class="m3-input-glass w-full text-xs font-medium font-mono"></textarea>
                                </div>
                            </div>
                        @elseif ($surat->jenis_surat === 'surat_permohonan_izin')
                            <div class="space-y-3.5">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama
                                        Kegiatan</label>
                                    <input type="text" name="nama_kegiatan" x-model="namaKegiatan"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Hari</label>
                                        <input type="text" name="hari_kegiatan" x-model="hariKegiatan"
                                            class="m3-input-glass w-full text-xs font-bold">
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
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tempat</label>
                                        <input type="text" name="tempat_kegiatan" x-model="tempatKegiatan"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Penanggung
                                            Jawab</label>
                                        <input type="text" name="penanggung_jawab" x-model="penanggungJawab"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Rincian
                                        Fasilitas Dimohonkan</label>
                                    <textarea name="fasilitas_dimohonkan" x-model="fasilitasDimohonkan" rows="3"
                                        class="m3-input-glass w-full text-xs font-medium"></textarea>
                                </div>
                            </div>
                        @elseif ($surat->jenis_surat === 'surat_dispensasi')
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Sekolah
                                            / Lembaga Tujuan <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_sekolah_tujuan" x-model="namaSekolahTujuan"
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
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Alasan
                                        / Catatan Tambahan (Opsional)</label>
                                    <textarea name="alasan_kegiatan" x-model="alasanDispensasi" rows="2"
                                        class="m3-input-glass w-full text-xs font-medium" placeholder="Keterangan tambahan jika diperlukan..."></textarea>
                                </div>

                                @if ($surat->is_dispensasi_massal)
                                    <!-- TABEL DAFTAR MURID & KELAS DI LEMBAGA TUJUAN -->
                                    <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4
                                                class="font-extrabold text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                                <i class="bi bi-people-fill text-emerald-500"></i> Daftar Murid & Kelas
                                                di Lembaga Tujuan (Lampiran)
                                            </h4>
                                            <span
                                                class="text-[10.5px] px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-bold">
                                                {{ count($surat->murid_dispensasi_list) }} Murid
                                            </span>
                                        </div>

                                        <div
                                            class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                                            <table class="w-full text-left text-xs">
                                                <thead
                                                    class="bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                                                    <tr>
                                                        <th class="px-3 py-2 text-center w-12 font-bold">No.</th>
                                                        <th class="px-3 py-2 font-bold">Nama Murid</th>
                                                        <th class="px-3 py-2 font-bold w-48">Kelas di Lembaga Dituju
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                                    @foreach ($surat->murid_dispensasi_list as $idx => $m)
                                                        @php
                                                            $mId = $m['murid_id'] ?? ($m['id'] ?? $idx);
                                                        @endphp
                                                        <tr>
                                                            <td class="px-3 py-2 text-center text-zinc-500">
                                                                {{ $idx + 1 }}</td>
                                                            <td
                                                                class="px-3 py-2 font-bold text-zinc-900 dark:text-zinc-100">
                                                                {{ $m['nama_lengkap'] ?? '-' }}
                                                                <input type="hidden" name="murid_ids[]"
                                                                    value="{{ $mId }}">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="text"
                                                                    name="kelas_lembaga[{{ $mId }}]"
                                                                    value="{{ $m['kelas_lembaga'] ?? '' }}"
                                                                    placeholder="Contoh: X PHT 1"
                                                                    class="m3-input-glass w-full text-xs font-bold py-1 px-2">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif ($surat->jenis_surat === 'surat_undangan')
                            <div class="space-y-3.5">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama
                                        Acara / Agenda</label>
                                    <input type="text" name="nama_acara" x-model="namaAcara"
                                        class="m3-input-glass w-full text-xs font-bold">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Hari
                                            Acara</label>
                                        <input type="text" name="hari_acara" x-model="hariAcara"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal
                                            Acara</label>
                                        <input type="date" name="tanggal_acara" x-model="tanggalAcara"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Waktu
                                            / Pukul</label>
                                        <input type="text" name="waktu_acara" x-model="waktuAcara"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tempat
                                            Acara</label>
                                        <input type="text" name="tempat_acara" x-model="tempatAcara"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Dresscode
                                            / Pakaian</label>
                                        <input type="text" name="pakaian_dresscode" x-model="pakaianDresscode"
                                            class="m3-input-glass w-full text-xs font-bold">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">Susunan
                                        Agenda Pembahasan</label>
                                    <textarea name="agenda_acara" x-model="agendaAcara" rows="3"
                                        class="m3-input-glass w-full text-xs font-medium"></textarea>
                                </div>
                            </div>
                        @endif

                        <!-- Narasi Tambahan -->
                        <div class="mt-4 pt-3.5 border-t border-zinc-200/60 dark:border-zinc-800/60">
                            <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                Narasi / Catatan Tambahan Khusus (Opsional)
                            </label>
                            <textarea name="isi_surat" x-model="isiSurat" rows="2" class="m3-input-glass w-full text-xs font-medium"></textarea>
                        </div>
                    </div>

                    <!-- 5. LEMBAR LAMPIRAN RESMI (HALAMAN 2 - OPSIONAL) -->
                    <div class="m3-glass-card p-5 space-y-3">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <div>
                                <h3
                                    class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                    <i class="bi bi-paperclip text-emerald-500"></i> 5. Lembar Lampiran Resmi (Halaman
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

                    <!-- 6. PEJABAT PENANDATANGAN SURAT (MAX 4 ORANG) -->
                    <div class="m3-glass-card p-5 space-y-4">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-zinc-200/60 dark:border-zinc-800/60">
                            <div>
                                <h3
                                    class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                    <i class="bi bi-pen-fill text-emerald-500"></i> 6. Pejabat Penandatangan Surat
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

                        <!-- Pengaturan Tempat, Status & Tembusan -->
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
                                    <option value="terbit" {{ $surat->status === 'terbit' ? 'selected' : '' }}>Telah
                                        Terbit</option>
                                    <option value="draft" {{ $surat->status === 'draft' ? 'selected' : '' }}>Konsep
                                        (Draft)</option>
                                    <option value="arsip" {{ $surat->status === 'arsip' ? 'selected' : '' }}>
                                        Diarsipkan</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tembusan
                                </label>
                                <input type="text" name="tembusan" x-model="tembusan"
                                    class="m3-input-glass w-full text-xs font-medium"
                                    placeholder="Contoh: 1. Pengasuh MDTHS, 2. Arsip">
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL SIMPAN -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('surat-keluar.show', $surat->id) }}"
                            class="px-5 py-2.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs transition-all">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition-all shadow-lg shadow-emerald-600/30 flex items-center gap-2">
                            <i class="bi bi-check2-circle"></i>
                            <span>Simpan Perubahan</span>
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
                        class="flex items-center justify-between px-3 py-2 rounded-2xl bg-zinc-900 text-white shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span class="text-xs font-extrabold uppercase tracking-wider">Live Preview Surat</span>
                        </div>
                        <span
                            class="text-[10px] px-2 py-0.5 rounded-full bg-zinc-800 text-amber-400 font-bold">{{ $surat->nama_jenis }}</span>
                    </div>

                    <!-- LEMBAR SIMULASI KERTAS SURAT RESMI -->
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
                                    <td class="font-bold font-mono text-zinc-900" x-text="nomorSurat"></td>
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
                                    <td class="font-bold text-zinc-900" x-text="perihal"></td>
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
                            <div class="font-extrabold text-[10.5px] text-zinc-900" x-text="tujuanSurat"></div>
                            <div class="text-zinc-500" x-text="alamatTujuan"></div>
                        </div>

                        <!-- SALAM PEMBUKA & PENGANTAR -->
                        <p class="mb-1 text-[9.5px]"><em>Assalamualaikum War. Wab.</em></p>
                        <p class="mb-1 text-[9.5px]">Dengan hormat,</p>
                        <p class="mb-1.5 text-[9.5px]" style="text-indent: 15px;">
                            Puji syukur Alhamdulillah kami ucapkan kehadirat Allah SWT yang telah melimpahkan rahmat dan
                            hidayah-Nya kepada kita semua. Sholawat dan salam tercurah kepada Nabi Muhammad SAW serta
                            keluarga dan para sahabatnya.
                        </p>

                        <!-- ISI DINAMIS REALTIME -->
                        <div class="space-y-2 text-[9.5px] text-justify leading-relaxed">

                            <!-- 1. PANGGILAN -->
                            @if ($surat->jenis_surat === 'surat_panggilan')
                                <div>
                                    <p>Sehubungan dengan keperluan <strong x-text="alasanPanggilan"></strong>, kami
                                        mengharap kehadiran Bapak/Ibu/Wali murid dari:</p>

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
                                                x-text="hariPanggilan + ', ' + formatTanggalMasehi(tanggalPanggilan)"></span>
                                        </div>
                                        <div class="flex"><span class="w-24 text-zinc-500">Waktu:</span><span
                                                x-text="waktuPanggilan"></span></div>
                                        <div class="flex"><span class="w-24 text-zinc-500">Tempat:</span><span
                                                x-text="tempatMenghadap"></span></div>
                                        <div class="flex"><span class="w-24 text-zinc-500">Menghadap:</span><span
                                                class="font-semibold" x-text="menghadapKepada"></span></div>
                                    </div>
                                </div>
                            @endif

                            <!-- 2. PERINGATAN (SP) -->
                            @if ($surat->jenis_surat === 'surat_peringatan')
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
                                        <div class="font-bold text-rose-800" x-text="bentukPelanggaran"></div>
                                        <div class="text-zinc-600" x-show="poinTatib" x-text="'Dasar: ' + poinTatib">
                                        </div>
                                        <div class="text-zinc-600" x-show="tindakanPembinaan"
                                            x-text="'Pembinaan: ' + tindakanPembinaan"></div>
                                    </div>
                                </div>
                            @endif

                            <!-- 3. PEMBERITAHUAN -->
                            @if ($surat->jenis_surat === 'surat_pemberitahuan')
                                <div>
                                    <p>Bersama ini kami sampaikan pemberitahuan resmi mengenai hal berikut<template x-if="activePreviewMurid"><span> terkait murid kami:</span></template><template x-if="!activePreviewMurid"><span>:</span></template></p>

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
                                        <div class="flex items-center gap-1.5 mb-1" x-show="kategoriPemberitahuan">
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-semibold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="kategoriPemberitahuan"></span>
                                        </div>
                                        <div class="font-bold text-zinc-800" x-text="pokokPemberitahuan"></div>
                                        <div class="text-zinc-500 mt-0.5" x-show="jadwalTerkait"
                                            x-text="'Jadwal: ' + jadwalTerkait"></div>
                                    </div>
                                </div>
                            @endif

                            <!-- 4. EDARAN -->
                            @if ($surat->jenis_surat === 'surat_edaran')
                                <div>
                                    <div class="text-center font-bold uppercase my-1 text-[10px]"
                                        x-text="pokokMaklumat">
                                    </div>
                                    <div class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[8.5px] font-mono whitespace-pre-line"
                                        x-text="instruksiPoin"></div>
                                </div>
                            @endif

                            <!-- 5. PERMOHONAN IZIN -->
                            @if ($surat->jenis_surat === 'surat_permohonan_izin')
                                <div>
                                    <p>Sehubungan dengan pelaksanaan kegiatan <strong x-text="namaKegiatan"></strong>,
                                        kami
                                        mengajukan permohonan izin fasilitas pada:</p>
                                    <div
                                        class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                        <div><strong
                                                x-text="hariKegiatan + ', ' + formatTanggalMasehi(tanggalKegiatan)"></strong>
                                            (<span x-text="waktuKegiatan"></span>)</div>
                                        <div x-text="'Tempat: ' + tempatKegiatan"></div>
                                        <div x-text="'Permohonan: ' + fasilitasDimohonkan"></div>
                                    </div>
                                </div>
                            @endif

                            <!-- 6. DISPENSASI -->
                            @if ($surat->jenis_surat === 'surat_dispensasi')
                                @if ($surat->is_dispensasi_massal)
                                    <div>
                                        <p>Sehubungan dengan adanya <strong
                                                x-text="namaKegiatanDispensasi || 'kegiatan resmi madrasah'"></strong>
                                            yang
                                            diadakan oleh MDT Hidayatus Shibyan, yang dilaksanakan pada:</p>

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
                                            Maka kami selaku Kepala MDT Hidayatus Shibyan Desa Somorkoneng Kecamatan
                                            Kwanyar
                                            Kabupaten Bangkalan mengajukan permohonan dispensasi untuk <strong
                                                x-text="permohonanDispensasiKhusus || 'dipulangkan lebih awal agar Murid dapat mempersiapkan diri'"></strong>.
                                            Adapun Murid yang mengikuti <span
                                                x-text="namaKegiatanDispensasi || 'kegiatan tersebut'"></span> akan
                                            disebut
                                            sebagaimana terlampir.
                                        </p>
                                    </div>
                                @else
                                    <div>
                                        <p>Yang bertanda tangan di bawah ini Kepala MDT Hidayatus Shibyan menerangkan
                                            bahwa
                                            murid di bawah ini:</p>

                                        <template x-if="activePreviewMurid">
                                            <div
                                                class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                                <div class="flex"><span class="w-28 text-zinc-500">Nama
                                                        Murid:</span><strong
                                                        x-text="activePreviewMurid.nama_lengkap"></strong>
                                                </div>
                                                <div class="flex"><span class="w-28 text-zinc-500">NISM:</span><span
                                                        class="font-mono" x-text="activePreviewMurid.nism"></span>
                                                </div>
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

                                        <p class="mt-2">Diberikan izin dan dispensasi kehadiran karena mengikuti
                                            kegiatan
                                            <strong x-text="namaKegiatanDispensasi || '(Nama Kegiatan)'"></strong>
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
                                @endif
                            @endif

                            <!-- 7. UNDANGAN -->
                            @if ($surat->jenis_surat === 'surat_undangan')
                                <div>
                                    <p>Mengharap dengan hormat kehadiran Bapak/Ibu/Saudara pada kegiatan <strong
                                            x-text="namaAcara"></strong> pada:</p>
                                    <div
                                        class="bg-zinc-50 p-2 rounded-lg my-1.5 border border-zinc-200 text-[9px] space-y-0.5">
                                        <div><strong
                                                x-text="hariAcara + ', ' + formatTanggalMasehi(tanggalAcara)"></strong>
                                            •
                                            <span x-text="waktuAcara"></span>
                                        </div>
                                        <div x-text="'Tempat: ' + tempatAcara"></div>
                                        <div x-text="'Pakaian: ' + pakaianDresscode"></div>
                                    </div>
                                </div>
                            @endif

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
                                <span x-text="hasLampiran ? '1 dari 2' : '1 dari 1'"></span>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- PREVIEW HALAMAN 2: LEMBAR LAMPIRAN RESMI   -->
                        <!-- ========================================== -->
                        <template x-if="hasLampiran">
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

                                    @if ($surat->is_dispensasi_massal)
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
                                                    @foreach ($surat->murid_dispensasi_list as $idx => $m)
                                                        <tr>
                                                            <td
                                                                class="border border-zinc-900 dark:border-zinc-100 px-2 py-1 text-center">
                                                                {{ $idx + 1 }}</td>
                                                            <td
                                                                class="border border-zinc-900 dark:border-zinc-100 px-3 py-1.5 font-bold uppercase text-zinc-900 dark:text-zinc-100">
                                                                {{ $m['nama_lengkap'] ?? '-' }}</td>
                                                            <td
                                                                class="border border-zinc-900 dark:border-zinc-100 px-3 py-1.5 text-center font-semibold text-zinc-800 dark:text-zinc-200">
                                                                {{ $m['kelas_lembaga'] ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <!-- Isi Konten Lampiran -->
                                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-700/80 text-[9px] font-mono whitespace-pre-line leading-relaxed min-h-[100px] text-zinc-800 dark:text-zinc-200"
                                            x-text="lampiranKonten || '(Belum ada rincian konten lampiran yang ditulis...)'">
                                        </div>
                                    @endif

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
        function formEditSurat() {
            return {
                activeMobileTab: 'form',
                nomorSurat: '{{ addslashes($surat->nomor_surat) }}',
                tanggalSurat: '{{ $surat->tanggal_surat?->format('Y-m-d') }}',
                tanggalHijriyah: '{{ addslashes($surat->tanggal_hijriyah ?? '') }}',
                tempatTerbit: '{{ addslashes($surat->tempat_terbit ?? 'Bangkalan') }}',
                perihal: '{{ addslashes($surat->perihal) }}',
                tujuanSurat: '{{ addslashes($surat->tujuan_surat) }}',
                alamatTujuan: '{{ addslashes($surat->alamat_tujuan ?? '') }}',
                sifatSurat: '{{ $surat->sifat_surat }}',
                lampiran: '{{ addslashes($surat->lampiran ?? '-') }}',
                hasLampiran: {{ $surat->has_lampiran ? 'true' : 'false' }},
                lampiranJudul: '{{ addslashes($surat->lampiran_judul) }}',
                lampiranKonten: `{!! addslashes($surat->lampiran_konten ?? '') !!}`,
                muridId: '{{ $surat->murid_id ?? ($surat->murid_ids[0] ?? '') }}',

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

                allMurids: @json($allMuridsData),
                muridDetail: @json($currentMuridData),
                singleRuanganFilter: '',
                singleSearch: '',

                // Specific Fields
                hariPanggilan: '{{ addslashes($spesifik['hari_panggilan'] ?? '') }}',
                tanggalPanggilan: '{{ $spesifik['tanggal_panggilan'] ?? '' }}',
                waktuPanggilan: '{{ addslashes($spesifik['waktu_panggilan'] ?? '14.00 WIB s/d Selesai') }}',
                tempatMenghadap: '{{ addslashes($spesifik['tempat_menghadap'] ?? 'Kantor TU MDTHS') }}',
                menghadapKepada: '{{ addslashes($spesifik['menghadap_kepada'] ?? 'Kepala Madrasah & Tim Kesiswaan') }}',
                alasanPanggilan: '{{ addslashes($spesifik['alasan_panggilan'] ?? '') }}',

                tingkatSp: '{{ addslashes($spesifik['tingkat_sp'] ?? 'Surat Peringatan I (SP 1)') }}',
                poinTatib: '{{ addslashes($spesifik['poin_tatib_dilanggar'] ?? '') }}',
                bentukPelanggaran: '{{ addslashes($spesifik['bentuk_pelanggaran'] ?? '') }}',
                tindakanPembinaan: '{{ addslashes($spesifik['tindakan_pembinaan'] ?? '') }}',
                batasWaktuPembinaan: '{{ addslashes($spesifik['batas_waktu_pembinaan'] ?? '') }}',

                kategoriPemberitahuan: '{{ addslashes($spesifik['kategori_pemberitahuan'] ?? 'Akademik & KBM') }}',
                jadwalTerkait: '{{ addslashes($spesifik['jadwal_terkait'] ?? '') }}',
                pokokPemberitahuan: '{{ addslashes($spesifik['pokok_pemberitahuan'] ?? '') }}',

                nomorEdaranInternal: '{{ addslashes($spesifik['nomor_edaran_internal'] ?? '') }}',
                berlakuMulai: '{{ addslashes($spesifik['berlaku_mulai'] ?? 'Sejak tanggal ditetapkan') }}',
                pokokMaklumat: '{{ addslashes($spesifik['pokok_maklumat'] ?? '') }}',
                instruksiPoin: `{!! addslashes($spesifik['instruksi_poin'] ?? '') !!}`,

                namaKegiatan: '{{ addslashes($spesifik['nama_kegiatan'] ?? '') }}',
                hariKegiatan: '{{ addslashes($spesifik['hari_kegiatan'] ?? '') }}',
                tanggalKegiatan: '{{ $spesifik['tanggal_kegiatan'] ?? '' }}',
                waktuKegiatan: '{{ addslashes($spesifik['waktu_kegiatan'] ?? '08.00 WIB') }}',
                tempatKegiatan: '{{ addslashes($spesifik['tempat_kegiatan'] ?? '') }}',
                penanggungJawab: '{{ addslashes($spesifik['penanggung_jawab'] ?? '') }}',
                fasilitasDimohonkan: '{{ addslashes($spesifik['fasilitas_dimohonkan'] ?? '') }}',

                namaSekolahTujuan: '{{ addslashes($spesifik['nama_sekolah_tujuan'] ?? '') }}',
                namaKegiatanDispensasi: '{{ addslashes($spesifik['nama_kegiatan_dispensasi'] ?? '') }}',
                hariKegiatanDispensasi: '{{ addslashes($spesifik['hari_kegiatan_dispensasi'] ?? '') }}',
                tanggalMulaiDispensasi: '{{ $spesifik['tanggal_mulai_dispensasi'] ?? '' }}',
                tanggalSelesaiDispensasi: '{{ $spesifik['tanggal_selesai_dispensasi'] ?? '' }}',
                waktuKegiatanDispensasi: '{{ addslashes($spesifik['waktu_kegiatan_dispensasi'] ?? '13.30 WIB s/d Selesai') }}',
                tempatKegiatanDispensasi: '{{ addslashes($spesifik['tempat_kegiatan_dispensasi'] ?? 'MDT Hidayatus Shibyan') }}',
                permohonanDispensasiKhusus: '{{ addslashes($spesifik['permohonan_dispensasi_khusus'] ?? 'dipulangkan pukul 12.00 WIB agar Murid dapat mempersiapkan diri') }}',
                jumlahHari: '{{ addslashes($spesifik['jumlah_hari'] ?? '') }}',
                alasanDispensasi: '{{ addslashes($spesifik['alasan_kegiatan'] ?? '') }}',

                namaAcara: '{{ addslashes($spesifik['nama_acara'] ?? '') }}',
                hariAcara: '{{ addslashes($spesifik['hari_acara'] ?? '') }}',
                tanggalAcara: '{{ $spesifik['tanggal_acara'] ?? '' }}',
                waktuAcara: '{{ addslashes($spesifik['waktu_acara'] ?? '19.30 WIB') }}',
                tempatAcara: '{{ addslashes($spesifik['tempat_acara'] ?? 'Aula MDTHS') }}',
                pakaianDresscode: '{{ addslashes($spesifik['pakaian_dresscode'] ?? 'Busana Muslim') }}',
                agendaAcara: '{{ addslashes($spesifik['agenda_acara'] ?? '') }}',

                init() {
                    this.$watch('hasLampiran', (val) => {
                        if (val && (this.lampiran === '-' || !this.lampiran)) {
                            this.lampiran = '1 Lembar';
                        } else if (!val && this.lampiran === '1 Lembar') {
                            this.lampiran = '-';
                        }
                    });

                    this.$watch('namaKegiatanDispensasi', (val) => {
                        if ('{{ $surat->jenis_surat }}' === 'surat_dispensasi') {
                            this.lampiranJudul = 'NAMA-NAMA MURID YANG MENGIKUTI ' + (val ? val.toUpperCase()
                                .trim() : 'KEGIATAN');
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

                get isDispensasiMassal() {
                    return '{{ $surat->is_dispensasi_massal ? 'true' : 'false' }}' === 'true';
                },

                get computedLampiranJudul() {
                    if (this.isDispensasiMassal || '{{ $surat->jenis_surat }}' === 'surat_dispensasi') {
                        const keg = this.namaKegiatanDispensasi ? this.namaKegiatanDispensasi.toUpperCase().trim() :
                            'KEGIATAN';
                        return 'NAMA-NAMA MURID YANG MENGIKUTI ' + keg;
                    }
                    return this.lampiranJudul || 'RINCIAN LAMPIRAN SURAT';
                },

                get isMuridRelated() {
                    return ['surat_panggilan', 'surat_peringatan', 'surat_pemberitahuan', 'surat_dispensasi'].includes(
                        '{{ $surat->jenis_surat }}');
                },

                get hasSingleFilter() {
                    return !!(this.singleRuanganFilter || this.singleSearch.trim());
                },

                get filteredSingleMurids() {
                    if (!this.hasSingleFilter) return [];
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

                get activePreviewMurid() {
                    if (this.muridDetail) return this.muridDetail;
                    if (this.muridId) {
                        return this.allMurids.find(m => String(m.id) === String(this.muridId)) || null;
                    }
                    return null;
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
                    // Instant update from local memory
                    const local = this.allMurids.find(m => String(m.id) === String(this.muridId));
                    if (local) {
                        this.muridDetail = local;
                    }
                    try {
                        const res = await fetch(`{{ url('persuratan/surat-keluar/api/murid') }}/${this.muridId}`);
                        const json = await res.json();
                        if (json.success) {
                            this.muridDetail = {
                                id: json.data.id,
                                nama_lengkap: json.data.nama_lengkap,
                                nism: json.data.nism,
                                ruangan_nama: json.data.ruangan,
                                nama_wali: json.data.nama_wali,
                                nama_ayah: json.data.nama_ayah,
                                alamat: json.data.alamat
                            };
                            const namaAyahWali = (json.data.nama_ayah && json.data.nama_ayah !== '-') ? json.data
                                .nama_ayah : json.data.nama_wali;
                            if ('{{ $surat->jenis_surat }}' === 'surat_panggilan' || '{{ $surat->jenis_surat }}' ===
                                'surat_pemberitahuan') {
                                this.tujuanSurat = namaAyahWali && namaAyahWali !== '-' ?
                                    `Bpk/Ibu/Wali dari ${json.data.nama_lengkap} (${namaAyahWali})` :
                                    `Bpk/Ibu/Wali dari ${json.data.nama_lengkap}`;
                                this.alamatTujuan = json.data.alamat ? `Dsn. ${json.data.alamat}` : 'Di Tempat';
                            } else if ('{{ $surat->jenis_surat }}' === 'surat_peringatan') {
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
