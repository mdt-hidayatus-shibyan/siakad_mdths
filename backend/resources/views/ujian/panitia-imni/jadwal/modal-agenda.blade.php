<form action="{{ route('jadwal-imni.update-agenda') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#main-content">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunId }}">
    <input type="hidden" name="ujian_id" value="{{ $ujianImni->id }}">

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-lg border border-primary/20">
                <i class="bi bi-calendar-range-fill"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded border border-primary/20">
                    Master Jadwal
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-0.5">
                    Pengaturan Master Agenda IMNI
                </h3>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        <!-- Info Tahun Pelajaran -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between">
            <span class="text-xs font-bold text-zinc-500">Tahun Pelajaran:</span>
            <span class="text-xs font-black text-zinc-900 dark:text-white font-mono">
                TP. {{ $selectedTahun->nama_hijriyah }} H ({{ $selectedTahun->nama_masehi }} M)
            </span>
        </div>

        <!-- Nama Ujian -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Nama Agenda Ujian IMNI <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_ujian" value="{{ old('nama_ujian', $ujianImni->nama_ujian) }}" required
                placeholder="Contoh: Imtihan Niha'i (IMNI) TP. 1446-1447 H"
                class="m3-input-glass w-full text-xs font-bold">
        </div>

        <!-- Semester -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Semester Pelaksanaan <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
                <select name="semester_id" required class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $ujianImni->semester_id == $sem->id ? 'selected' : '' }}>
                            {{ $sem->nama_semester }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- Rentang Tanggal Pelaksanaan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Tanggal Mulai Ujian <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $ujianImni->tanggal_mulai ? \Carbon\Carbon::parse($ujianImni->tanggal_mulai)->format('Y-m-d') : '') }}" required
                    class="m3-input-glass w-full text-xs font-bold font-mono">
            </div>

            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Tanggal Selesai Ujian <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $ujianImni->tanggal_selesai ? \Carbon\Carbon::parse($ujianImni->tanggal_selesai)->format('Y-m-d') : '') }}" required
                    class="m3-input-glass w-full text-xs font-bold font-mono">
            </div>
        </div>

        <div class="p-3.5 rounded-2xl bg-primary/5 dark:bg-primary/10 border border-primary/20 text-[11px] font-medium text-zinc-600 dark:text-zinc-300 flex items-start gap-2">
            <i class="bi bi-info-circle-fill text-primary shrink-0 mt-0.5"></i>
            <span>Rentang tanggal ini akan otomatis menjadi hari-hari ujian pada <strong>Kertas Kerja Jadwal Ujian IMNI</strong> (hari Jum'at dilewati otomatis).</span>
        </div>

        <!-- Keterangan / Catatan -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Keterangan / Catatan Panitia <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
            </label>
            <textarea name="keterangan" rows="2.5" placeholder="Catatan khusus pelaksanaan ujian IMNI..."
                class="m3-input-glass w-full text-xs font-medium">{{ old('keterangan', $ujianImni->keterangan) }}</textarea>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>

        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5">
            <i class="bi bi-cloud-arrow-up-fill"></i>
            <span>Simpan Agenda Ujian</span>
        </button>
    </div>
</form>
