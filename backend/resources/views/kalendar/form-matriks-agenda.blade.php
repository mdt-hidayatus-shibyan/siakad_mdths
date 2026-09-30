<form action="{{ route('kalendar-pendidikan.store') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh] bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl rounded-2xl md:rounded-3xl overflow-hidden border border-zinc-200/80 dark:border-zinc-800 shadow-2xl">

    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tp->id ?? '' }}">

    <!-- Modal Header -->
    <div
        class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/80 dark:bg-zinc-950/60 shrink-0">
        <div class="flex items-center gap-2.5">
            <div
                class="w-9 h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20 shrink-0 shadow-2xs">
                <i class="bi bi-calendar-plus text-base"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                    Tambah Agenda Matriks
                </h3>
                <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5">
                    Penjadwalan Tanggal Terpilih</p>
            </div>
        </div>

        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="min-w-9 min-h-9 flex items-center justify-center rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-400 transition-colors outline-none shrink-0"
            title="Tutup">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 overflow-y-auto custom-scrollbar flex-1">
        <div class="space-y-4">

            <!-- Jenis Agenda -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-primary dark:text-primary-dark uppercase tracking-wider mb-1 ml-0.5">
                    Jenis Agenda <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="jenis_agenda" id="jenisAgenda" onchange="ubahFormSesuaiJenis()"
                        class="m3-input-glass w-full !pr-9 font-bold text-xs cursor-pointer appearance-none bg-primary/5 dark:bg-primary-dark/10 border-primary/20 text-primary dark:text-primary-dark">
                        <option value="kegiatan" class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                            Kegiatan Umum (Pendidikan)</option>
                        <option value="libur" class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">Hari
                            Libur (Nasional/Madrasah)</option>
                        <option value="ujian" class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">Ujian
                            Akademik</option>
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-primary dark:text-primary-dark">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>

            <!-- Judul Agenda -->
            <div>
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                    Judul Agenda / Keterangan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_agenda" placeholder="Contoh: Ujian Kitab Lisan"
                    class="m3-input-glass w-full font-bold text-xs">
            </div>

            <!-- Grid Mulai & Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label
                        class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                        Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" id="inputMulaiMatriks" value="{{ $tanggal ?? '' }}"
                        class="m3-input-glass w-full font-bold text-xs bg-zinc-100 dark:bg-zinc-800/50 cursor-not-allowed text-zinc-500">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                        Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" id="inputSelesaiMatriks" value="{{ $tanggal ?? '' }}"
                        min="{{ $tanggal ?? '' }}"
                        class="m3-input-glass w-full font-bold text-xs cursor-pointer border-emerald-500/50 focus:border-emerald-500">
                </div>
            </div>

            <!-- Kolom Khusus: Kategori & Presensi Kegiatan -->
            <div id="kolom_kegiatan" class="block space-y-4">
                <div class="relative group/select">
                    <label
                        class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                        Kategori Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="kategori_kegiatan_id" id="inputKategori"
                            class="m3-input-glass w-full !pr-9 font-bold text-xs cursor-pointer appearance-none">
                            <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih Kategori --
                            </option>
                            @foreach ($kategoris ?? [] as $kat)
                                <option value="{{ $kat->id }}"
                                    class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                    {{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Presensi Event / Kegiatan -->
                <div
                    class="p-4 rounded-2xl bg-linear-to-br from-indigo-500/[0.04] via-indigo-500/[0.02] to-transparent dark:from-indigo-500/[0.08] dark:to-transparent border border-indigo-200/70 dark:border-indigo-800/50 space-y-3.5 shadow-xs">
                    <div
                        class="flex items-center justify-between gap-2 pb-2.5 border-b border-indigo-100 dark:border-indigo-900/40">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-7 h-7 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-500/20 shrink-0">
                                <i class="bi bi-person-check-fill text-xs"></i>
                            </div>
                            <div>
                                <span
                                    class="text-xs font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-wider block">
                                    Model Presensi Kegiatan
                                </span>
                                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block -mt-0.5">
                                    Atur apakah kegiatan ini mewajibkan presensi murid & ustadz
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Tipe Presensi (Radio Cards) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- Tanpa Presensi -->
                        <label
                            class="relative flex flex-col p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-indigo-400/60 has-[:checked]:border-indigo-500 dark:has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:bg-indigo-500/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/30 transition-all shadow-2xs group select-none">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-xs text-zinc-800 dark:text-zinc-200">Tanpa Presensi</span>
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="radio" name="tipe_presensi" value="tidak_ada"
                                        onchange="toggleSesiKegiatanMatriks()" class="peer sr-only" checked>
                                    <div
                                        class="w-4.5 h-4.5 rounded-full border-2 border-zinc-300 dark:border-zinc-600 peer-checked:border-indigo-600 dark:peer-checked:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                    </div>
                                    <div
                                        class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-500 absolute opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200 pointer-events-none">
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 leading-tight">Tidak ada presensi
                                khusus</span>
                        </label>

                        <!-- 1 Sesi Harian -->
                        <label
                            class="relative flex flex-col p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-indigo-400/60 has-[:checked]:border-indigo-500 dark:has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:bg-indigo-500/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/30 transition-all shadow-2xs group select-none">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-xs text-zinc-800 dark:text-zinc-200">1 Sesi Harian</span>
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="radio" name="tipe_presensi" value="harian"
                                        onchange="toggleSesiKegiatanMatriks()" class="peer sr-only">
                                    <div
                                        class="w-4.5 h-4.5 rounded-full border-2 border-zinc-300 dark:border-zinc-600 peer-checked:border-indigo-600 dark:peer-checked:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                    </div>
                                    <div
                                        class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-500 absolute opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200 pointer-events-none">
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 leading-tight">Hari Efektif
                                Non-KBM / Pembinaan</span>
                        </label>

                        <!-- Multi-Sesi -->
                        <label
                            class="relative flex flex-col p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-indigo-400/60 has-[:checked]:border-indigo-500 dark:has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:bg-indigo-500/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/30 transition-all shadow-2xs group select-none">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-black text-xs text-zinc-800 dark:text-zinc-200">Multi-Sesi</span>
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="radio" name="tipe_presensi" value="multi_sesi"
                                        onchange="toggleSesiKegiatanMatriks()" class="peer sr-only">
                                    <div
                                        class="w-4.5 h-4.5 rounded-full border-2 border-zinc-300 dark:border-zinc-600 peer-checked:border-indigo-600 dark:peer-checked:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                    </div>
                                    <div
                                        class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-500 absolute opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200 pointer-events-none">
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 leading-tight">Haflah Ikhtibar &
                                Lomba (Siang/Malam)</span>
                        </label>
                    </div>

                    <!-- Pilihan Checklist Sesi (Hanya muncul jika Multi-Sesi dipilih) -->
                    <div id="pilihan_multi_sesi_matriks"
                        class="hidden pt-2.5 border-t border-dashed border-indigo-200/80 dark:border-indigo-900/50 space-y-2">
                        <label
                            class="block text-[11px] font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-wider">
                            Pilih Sesi Presensi Harian:
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <!-- Sesi Siang -->
                            <label
                                class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-indigo-400/60 has-[:checked]:border-indigo-500 dark:has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:bg-indigo-500/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/30 transition-all shadow-2xs select-none">
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="checkbox" name="sesi_kegiatan[]" value="Siang"
                                        class="peer sr-only" checked>
                                    <div
                                        class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500 peer-checked:border-indigo-600 dark:peer-checked:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                    </div>
                                    <i
                                        class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                                </div>
                                <span class="font-bold text-xs text-zinc-800 dark:text-zinc-200">☀️ Sesi Siang
                                    (Lomba)</span>
                            </label>

                            <!-- Sesi Malam -->
                            <label
                                class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-indigo-400/60 has-[:checked]:border-indigo-500 dark:has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:bg-indigo-500/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/30 transition-all shadow-2xs select-none">
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="checkbox" name="sesi_kegiatan[]" value="Malam"
                                        class="peer sr-only" checked>
                                    <div
                                        class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500 peer-checked:border-indigo-600 dark:peer-checked:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                    </div>
                                    <i
                                        class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                                </div>
                                <span class="font-bold text-xs text-zinc-800 dark:text-zinc-200">🌙 Sesi Malam
                                    (Haflah)</span>
                            </label>

                            <!-- Sesi Pagi -->
                            <label
                                class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-indigo-400/60 has-[:checked]:border-indigo-500 dark:has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-500/10 dark:has-[:checked]:bg-indigo-500/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500/30 transition-all shadow-2xs select-none">
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="checkbox" name="sesi_kegiatan[]" value="Pagi"
                                        class="peer sr-only">
                                    <div
                                        class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500 peer-checked:border-indigo-600 dark:peer-checked:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                    </div>
                                    <i
                                        class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                                </div>
                                <span class="font-bold text-xs text-zinc-800 dark:text-zinc-200">🌅 Sesi Pagi</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Khusus: Ujian -->
            <div id="kolom_ujian"
                class="hidden p-4 rounded-xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-300/40 dark:border-amber-700/40">
                <div class="space-y-3.5">
                    <div class="relative group/select">
                        <label
                            class="block text-[11px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-1 ml-0.5">
                            Pilih Semester <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="semester_id" id="inputSemester"
                                class="m3-input-glass w-full !pr-9 font-bold text-xs cursor-pointer appearance-none border-amber-300/60 dark:border-amber-700/60 text-amber-900 dark:text-amber-100">
                                <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Pilih
                                    Semester
                                    --</option>
                                @foreach (\App\Models\Semester::where('tahun_pelajaran_id', $tp->id ?? null)->get() as $sem)
                                    <option value="{{ $sem->id }}"
                                        class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                        {{ $sem->nama_semester }}</option>
                                @endforeach
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-amber-600 dark:text-amber-400">
                                <i class="bi bi-chevron-down text-xs font-bold"></i>
                            </div>
                        </div>
                    </div>
                    <div class="relative group/select">
                        <label
                            class="block text-[11px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-1 ml-0.5">
                            Tipe Ujian <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="tipe_ujian" id="inputTipeUjian"
                                class="m3-input-glass w-full !pr-9 font-bold text-xs cursor-pointer appearance-none border-amber-300/60 dark:border-amber-700/60 text-amber-900 dark:text-amber-100">
                                <option value="IMDA 1"
                                    class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                    IMDA 1</option>
                                <option value="IMDA 2"
                                    class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                    IMDA 2</option>
                                <option value="IMDA 3"
                                    class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                    IMDA 3</option>
                                <option value="IMNI"
                                    class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                    IMNI</option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-amber-600 dark:text-amber-400">
                                <i class="bi bi-chevron-down text-xs font-bold"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Khusus: Hari Libur / Bebas KBM -->
            <div id="kolom_libur"
                class="hidden p-4 sm:p-5 rounded-2xl bg-linear-to-br from-rose-500/[0.04] via-rose-500/[0.02] to-transparent dark:from-rose-500/[0.08] dark:to-transparent border border-rose-200/70 dark:border-rose-800/50 space-y-4 shadow-xs">

                <!-- Header Cakupan Libur -->
                <div
                    class="flex items-center justify-between gap-2 pb-3 border-b border-rose-100 dark:border-rose-900/40">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-8 h-8 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-500/20 shrink-0 shadow-2xs">
                            <i class="bi bi-slash-circle-fill text-sm"></i>
                        </div>
                        <div>
                            <span
                                class="text-xs font-black text-rose-800 dark:text-rose-300 uppercase tracking-wider block">
                                Cakupan Bebas KBM / Libur <span class="text-rose-500">*</span>
                            </span>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block -mt-0.5">
                                Tentukan waktu & sasaran pembebasan presensi
                            </span>
                        </div>
                    </div>
                    <span
                        class="px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded-md bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/70">
                        Aturan KBM
                    </span>
                </div>

                <!-- Pilihan Tipe Libur (Radio Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <!-- Option 1: Seharian Penuh -->
                    <label
                        class="flex items-center gap-3 p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-rose-400/60 has-[:checked]:border-rose-500 dark:has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 dark:has-[:checked]:bg-rose-500/20 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30 transition-all shadow-2xs group select-none">
                        <div class="relative flex items-center justify-center shrink-0">
                            <input type="radio" name="tipe_libur" value="Seharian"
                                onchange="toggleJamBebasKbmMatriks()" class="peer sr-only" checked>
                            <div
                                class="w-4.5 h-4.5 rounded-full border-2 border-zinc-300 dark:border-zinc-600 peer-checked:border-rose-600 dark:peer-checked:border-rose-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                            </div>
                            <div
                                class="w-2 h-2 rounded-full bg-rose-600 dark:bg-rose-500 absolute opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200 pointer-events-none">
                            </div>
                        </div>
                        <div
                            class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200/50 dark:border-rose-800/50 shadow-2xs">
                            <i class="bi bi-calendar-x-fill text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-black text-xs text-zinc-900 dark:text-white block">Seharian Penuh</span>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block leading-tight">Semua
                                jam/sesi KBM ditiadakan</span>
                        </div>
                    </label>

                    <!-- Option 2: Sebagian Jam -->
                    <label
                        class="flex items-center gap-3 p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-rose-400/60 has-[:checked]:border-rose-500 dark:has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 dark:has-[:checked]:bg-rose-500/20 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30 transition-all shadow-2xs group select-none">
                        <div class="relative flex items-center justify-center shrink-0">
                            <input type="radio" name="tipe_libur" value="Sebagian Jam"
                                onchange="toggleJamBebasKbmMatriks()" class="peer sr-only">
                            <div
                                class="w-4.5 h-4.5 rounded-full border-2 border-zinc-300 dark:border-zinc-600 peer-checked:border-rose-600 dark:peer-checked:border-rose-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                            </div>
                            <div
                                class="w-2 h-2 rounded-full bg-rose-600 dark:bg-rose-500 absolute opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200 pointer-events-none">
                            </div>
                        </div>
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200/50 dark:border-amber-800/50 shadow-2xs">
                            <i class="bi bi-clock-history text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-black text-xs text-zinc-900 dark:text-white block">Sebagian Jam</span>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block leading-tight">Hanya jam
                                tertentu yang bebas KBM</span>
                        </div>
                    </label>
                </div>

                <!-- Pilihan Jam Tertentu (Checkboxes) -->
                <div id="pilihan_jam_bebas_matriks"
                    class="hidden pt-3 border-t border-dashed border-rose-200/80 dark:border-rose-900/50 space-y-2">
                    <div class="flex items-center justify-between">
                        <label
                            class="block text-[11px] font-black text-rose-800 dark:text-rose-300 uppercase tracking-wider">
                            Pilih Jam yang Bebas KBM <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-medium">Pilih satu atau lebih
                            jam</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <!-- Nadzoman -->
                        <label
                            class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-rose-400/60 has-[:checked]:border-rose-500 dark:has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 dark:has-[:checked]:bg-rose-500/20 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30 transition-all shadow-2xs select-none">
                            <div class="relative flex items-center justify-center shrink-0">
                                <input type="checkbox" name="jam_ke[]" value="Nadzoman" class="peer sr-only">
                                <div
                                    class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-rose-600 dark:peer-checked:bg-rose-500 peer-checked:border-rose-600 dark:peer-checked:border-rose-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                </div>
                                <i
                                    class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span
                                    class="font-black text-xs text-zinc-800 dark:text-zinc-200 leading-tight">Nadzoman</span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-mono">13:45-14:00</span>
                            </div>
                        </label>

                        <!-- Jam Ke-1 -->
                        <label
                            class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-rose-400/60 has-[:checked]:border-rose-500 dark:has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 dark:has-[:checked]:bg-rose-500/20 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30 transition-all shadow-2xs select-none">
                            <div class="relative flex items-center justify-center shrink-0">
                                <input type="checkbox" name="jam_ke[]" value="1" class="peer sr-only">
                                <div
                                    class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-rose-600 dark:peer-checked:bg-rose-500 peer-checked:border-rose-600 dark:peer-checked:border-rose-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                </div>
                                <i
                                    class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="font-black text-xs text-zinc-800 dark:text-zinc-200 leading-tight">Jam
                                    Ke-1</span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-mono">14:00-14:45</span>
                            </div>
                        </label>

                        <!-- Jam Ke-2 -->
                        <label
                            class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-rose-400/60 has-[:checked]:border-rose-500 dark:has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 dark:has-[:checked]:bg-rose-500/20 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30 transition-all shadow-2xs select-none">
                            <div class="relative flex items-center justify-center shrink-0">
                                <input type="checkbox" name="jam_ke[]" value="2" class="peer sr-only">
                                <div
                                    class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-rose-600 dark:peer-checked:bg-rose-500 peer-checked:border-rose-600 dark:peer-checked:border-rose-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                </div>
                                <i
                                    class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="font-black text-xs text-zinc-800 dark:text-zinc-200 leading-tight">Jam
                                    Ke-2</span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-mono">15:30-16:15</span>
                            </div>
                        </label>

                        <!-- Ekstra -->
                        <label
                            class="flex items-center gap-2.5 p-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 cursor-pointer hover:border-rose-400/60 has-[:checked]:border-rose-500 dark:has-[:checked]:border-rose-500 has-[:checked]:bg-rose-500/10 dark:has-[:checked]:bg-rose-500/20 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30 transition-all shadow-2xs select-none">
                            <div class="relative flex items-center justify-center shrink-0">
                                <input type="checkbox" name="jam_ke[]" value="Ekstra" class="peer sr-only">
                                <div
                                    class="w-4.5 h-4.5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 peer-checked:bg-rose-600 dark:peer-checked:bg-rose-500 peer-checked:border-rose-600 dark:peer-checked:border-rose-500 transition-all duration-200 bg-white dark:bg-zinc-950">
                                </div>
                                <i
                                    class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all duration-200 pointer-events-none"></i>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span
                                    class="font-black text-xs text-zinc-800 dark:text-zinc-200 leading-tight">Ekstra</span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-mono">20:00-21:00</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Pilihan Ruangan / Level Khusus (Opsional) -->
                <div class="pt-3 border-t border-dashed border-rose-200/80 dark:border-rose-900/50 space-y-2">
                    <div class="flex items-center justify-between">
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                            Pengkhususan Sasaran <span
                                class="text-zinc-400 dark:text-zinc-500 font-normal lowercase">(opsional)</span>
                        </label>
                        <span class="text-[10px] text-zinc-400 dark:text-zinc-500">Kosongkan bila berlaku global</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="relative group/select">
                            <label
                                class="block text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                                Target Ruangan
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-500/70">
                                    <i class="bi bi-door-open-fill text-xs"></i>
                                </div>
                                <select name="ruangan_id"
                                    class="m3-input-glass w-full !pl-9 !pr-9 font-bold text-xs cursor-pointer appearance-none bg-white dark:bg-zinc-900">
                                    <option value="">-- Berlaku Semua Ruangan --</option>
                                    @if (isset($ruangans))
                                        @foreach ($ruangans as $r)
                                            <option value="{{ $r->id }}"
                                                class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                                {{ $r->nama_ruangan }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                                </div>
                            </div>
                        </div>
                        <div class="relative group/select">
                            <label
                                class="block text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">
                                Target Level / Kelas
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-500/70">
                                    <i class="bi bi-mortarboard-fill text-xs"></i>
                                </div>
                                <select name="level_id"
                                    class="m3-input-glass w-full !pl-9 !pr-9 font-bold text-xs cursor-pointer appearance-none bg-white dark:bg-zinc-900">
                                    <option value="">-- Berlaku Semua Level --</option>
                                    @if (isset($levels))
                                        @foreach ($levels as $lvl)
                                            <option value="{{ $lvl->id }}"
                                                class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                                                Level {{ $lvl->nama_level }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Footer / Actions -->
    <div
        class="px-5 py-3.5 bg-zinc-50/80 dark:bg-zinc-950/60 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col-reverse sm:flex-row justify-end gap-2.5 shrink-0">
        <button type="button" data-dismiss="modal" class="m3-btn-secondary w-full sm:w-auto h-10 px-5">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-6 group/btn">
            <i class="bi bi-check-circle-fill text-xs"></i>
            <span>Simpan Agenda</span>
        </button>
    </div>

</form>

<script>
    function ubahFormSesuaiJenis() {
        const jenisSelect = document.getElementById('jenisAgenda');
        if (!jenisSelect) return;
        const jenis = jenisSelect.value;
        const kolomKegiatan = document.getElementById('kolom_kegiatan');
        const kolomUjian = document.getElementById('kolom_ujian');
        const kolomLibur = document.getElementById('kolom_libur');

        if (jenis === 'kegiatan') {
            kolomKegiatan?.classList.remove('hidden');
            kolomKegiatan?.classList.add('block');
            kolomUjian?.classList.remove('block');
            kolomUjian?.classList.add('hidden');
            kolomLibur?.classList.remove('block');
            kolomLibur?.classList.add('hidden');
        } else if (jenis === 'ujian') {
            kolomKegiatan?.classList.remove('block');
            kolomKegiatan?.classList.add('hidden');
            kolomUjian?.classList.remove('hidden');
            kolomUjian?.classList.add('block');
            kolomLibur?.classList.remove('block');
            kolomLibur?.classList.add('hidden');
        } else if (jenis === 'libur') {
            kolomKegiatan?.classList.remove('block');
            kolomKegiatan?.classList.add('hidden');
            kolomUjian?.classList.remove('block');
            kolomUjian?.classList.add('hidden');
            kolomLibur?.classList.remove('hidden');
            kolomLibur?.classList.add('block');
        }
    }

    function toggleJamBebasKbmMatriks() {
        const tipeLibur = document.querySelector('input[name="tipe_libur"]:checked')?.value;
        const containerJam = document.getElementById('pilihan_jam_bebas_matriks');
        if (!containerJam) return;

        if (tipeLibur === 'Sebagian Jam') {
            containerJam.classList.remove('hidden');
        } else {
            containerJam.classList.add('hidden');
        }
    }

    function toggleSesiKegiatanMatriks() {
        const tipePresensi = document.querySelector('input[name="tipe_presensi"]:checked')?.value;
        const containerSesi = document.getElementById('pilihan_multi_sesi_matriks');
        if (!containerSesi) return;

        if (tipePresensi === 'multi_sesi') {
            containerSesi.classList.remove('hidden');
        } else {
            containerSesi.classList.add('hidden');
        }
    }

    ubahFormSesuaiJenis();
    toggleJamBebasKbmMatriks();
    toggleSesiKegiatanMatriks();
</script>
