<form action="{{ isset($ujian) ? route('master-imni.update', $ujian->id) : route('master-imni.store') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-grid-container">
    @csrf
    @if (isset($ujian))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
            {{ isset($ujian) ? 'Edit Master Agenda IMNI' : 'Tambah Agenda IMNI Baru' }}
        </h3>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1">
        <div class="space-y-4">

            <!-- Baris 1: Tahun Pelajaran & Semester -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tahun Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            @foreach ($tahun_pelajarans as $tp)
                                @php
                                    $isSelected = isset($ujian)
                                        ? $ujian->tahun_pelajaran_id == $tp->id
                                        : $tp->is_active == true || old('tahun_pelajaran_id') == $tp->id;
                                @endphp
                                <option value="{{ $tp->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $tp->nama_hijriyah }} - {{ $tp->nama_masehi }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Semester <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="semester_id" id="semester_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                            @foreach ($semesters as $smt)
                                <option value="{{ $smt->id }}"
                                    {{ (isset($ujian) && $ujian->semester_id == $smt->id) || old('semester_id') == $smt->id ? 'selected' : '' }}>
                                    {{ $smt->nama_semester ?? 'Semester ' . $smt->id }} (Semester Akhir)
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Nama Ujian & Tipe Ujian -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Nama Agenda Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_ujian" value="{{ $ujian->nama_ujian ?? old('nama_ujian', 'Imtihan Niha\'i (IMNI)') }}"
                        placeholder="Contoh: Imtihan Niha'i (IMNI)" class="m3-input-glass w-full font-bold">
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tipe Ujian
                    </label>
                    <input type="text" value="IMNI (Imtihan Niha'i)" readonly
                        class="m3-input-glass w-full bg-zinc-100 dark:bg-zinc-800/60 text-zinc-600 dark:text-zinc-300 font-black cursor-not-allowed">
                </div>
            </div>

            <!-- Baris 3: Sasaran Tingkat / Jenjang -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Sasaran Tingkat / Jenjang <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
                </label>
                <div class="relative group/select">
                    <select name="tingkat_id" id="tingkat_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        <option value="">-- Berlaku Semua Tingkat (3 TPQ, 6 IBT, 3 TSA) --</option>
                        @if (isset($tingkats))
                            @foreach ($tingkats as $tkt)
                                @php
                                    $labelKelas = match(strtoupper($tkt->kode_tingkat)) {
                                        'TPQ' => 'Kelas 3 TPQ',
                                        'IBT' => 'Kelas 6 Ibtidaiyah (IBT)',
                                        'TSA' => 'Kelas 3 Tsanawiyah (TSA)',
                                        default => 'Kelas Akhir ' . $tkt->nama_tingkat
                                    };
                                @endphp
                                <option value="{{ $tkt->id }}"
                                    {{ (isset($ujian) && $ujian->tingkat_id == $tkt->id) || old('tingkat_id') == $tkt->id ? 'selected' : '' }}>
                                    Khusus Tingkat {{ $tkt->nama_tingkat }} ({{ $labelKelas }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 ml-1 leading-relaxed">
                    <i class="bi bi-info-circle text-primary mr-0.5"></i>
                    Pilih tingkat spesifik jika pelaksanaan IMNI dibuat terpisah antar jenjang. Biarkan <strong>"Semua Tingkat"</strong> jika jadwal agenda berlaku serentak untuk seluruh kelas akhir (3 TPQ, 6 IBT, 3 TSA).
                </p>
            </div>

            <!-- Baris 4: Sasaran Kelas Akhir Info Banner -->
            <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/60 dark:border-zinc-800/60 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="text-xs">
                    <p class="font-black text-zinc-900 dark:text-white">Sasaran Peserta Ujian IMNI:</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">Khusus Kelas Akhir: 3 TPQ, 6 Ibtidaiyah (IBT), dan 3 Tsanawiyah (TSA).</p>
                </div>
            </div>

            <!-- Baris 4: Tanggal Mulai & Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tanggal Mulai Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" required
                        value="{{ isset($ujian) && $ujian->tanggal_mulai ? $ujian->tanggal_mulai->format('Y-m-d') : old('tanggal_mulai') }}"
                        class="m3-input-glass w-full font-mono font-bold">
                </div>
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tanggal Selesai Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" required
                        value="{{ isset($ujian) && $ujian->tanggal_selesai ? $ujian->tanggal_selesai->format('Y-m-d') : old('tanggal_selesai') }}"
                        class="m3-input-glass w-full font-mono font-bold">
                </div>
            </div>

            <!-- Baris 5: Keterangan / Catatan -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Keterangan / Catatan Pelaksanaan (Opsional)
                </label>
                <textarea name="keterangan" rows="2" placeholder="Catatan tambahan pelaksanaan ujian IMNI..."
                    class="m3-input-glass w-full text-xs font-medium">{{ $ujian->keterangan ?? old('keterangan') }}</textarea>
            </div>

        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 sm:flex sm:flex-row-reverse gap-2.5 transition-colors duration-300">
        <button type="submit" class="m3-btn-primary w-full sm:w-auto px-5 py-2 group/btn cursor-pointer">
            <i class="bi bi-save2-fill text-sm"></i>
            <span>{{ isset($ujian) ? 'Simpan Perubahan' : 'Simpan Agenda IMNI' }}</span>
        </button>
    </div>
</form>
