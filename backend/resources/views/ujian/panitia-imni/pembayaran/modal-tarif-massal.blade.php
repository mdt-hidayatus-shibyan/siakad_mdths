<form action="{{ route('pembayaran-imni.tarif-massal') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahun->id }}">

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/20">
                <i class="bi bi-tag-fill"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                    Tarif Administrasi
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Set Tarif Manual Massal
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
        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 text-xs flex justify-between items-center">
            <span class="font-bold text-zinc-600 dark:text-zinc-400">Tahun Pelajaran:</span>
            <span class="text-zinc-900 dark:text-white font-black">{{ $selectedTahun->nama_hijriyah }} H ({{ $selectedTahun->nama_masehi }} M)</span>
        </div>

        <!-- 1. Pilihan Tingkat Sasaran -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Target Tingkat Murid <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
                <select name="tingkat_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                    <option value="">Semua Tingkat (Seluruh Peserta IMNI)</option>
                    @foreach ($daftarTingkat as $tk)
                        <option value="{{ $tk->id }}">{{ $tk->nama_tingkat }} ({{ $tk->kode_tingkat }})</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-zinc-400 mt-1">
                Pilih tingkat tertentu jika tarif biaya antar jenjang berbeda.
            </p>
        </div>

        <!-- 2. Input Nominal Tarif -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Nominal Tarif Administrasi <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs text-zinc-400">Rp</span>
                <input type="number" name="nominal_tagihan" min="0" step="1000" required placeholder="Contoh: 150000"
                    class="m3-input-glass w-full !pl-10 !pr-4 text-sm font-black font-mono">
            </div>
        </div>

        <!-- Notifikasi Keterangan -->
        <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-700 dark:text-amber-300 flex items-start gap-2.5">
            <i class="bi bi-exclamation-triangle-fill text-amber-500 text-sm mt-0.5 shrink-0"></i>
            <p class="text-[11px] leading-relaxed">
                Menetapkan tarif massal akan memperbarui nominal tagihan dan mengkalkulasi ulang sisa piutang untuk seluruh murid pada tingkat yang dipilih.
            </p>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white">
            <i class="bi bi-check2-circle"></i>
            <span>Terapkan Tarif</span>
        </button>
    </div>
</form>
