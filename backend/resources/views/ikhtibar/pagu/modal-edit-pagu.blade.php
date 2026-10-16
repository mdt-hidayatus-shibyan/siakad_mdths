<form action="{{ route('pagu-divisi-ikhtibar.simpan-update-pagu', $divisi->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    @method('PUT')

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded border border-primary/20">
                Pagu Anggaran Ikhtibar
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Sesuaikan Pagu: {{ $divisi->nama_divisi }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs">
        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Total Tersalurkan Saat Ini:</span>
                <span class="font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($divisi->total_tersalurkan, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Realisasi SPJ Belanja:</span>
                <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($divisi->total_realisasi_spj, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Sisa Kas di Divisi:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($divisi->sisa_kas_di_divisi, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Nominal Pagu Anggaran (Rp) <span class="text-rose-500">*</span>
            </label>
            <input type="number" name="nominal_pagu" value="{{ $divisi->nominal_pagu }}" min="0" step="50000"
                class="m3-input-glass w-full font-black text-sm text-primary dark:text-primary-light" required>
            <p class="text-[10px] text-zinc-400">
                Pagu disesuaikan berdasarkan musyawarah BPH atau revisi anggaran kegiatan.
            </p>
        </div>

        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Status Pagu <span class="text-rose-500">*</span>
            </label>
            <select name="status_pagu" class="m3-input-glass w-full font-bold">
                <option value="Draft" {{ $divisi->status_pagu === 'Draft' ? 'selected' : '' }}>Draft (Pengajuan Awal)</option>
                <option value="Disetujui" {{ $divisi->status_pagu === 'Disetujui' ? 'selected' : '' }}>Disetujui (Dapat Dicairkan)</option>
                <option value="Dikunci" {{ $divisi->status_pagu === 'Dikunci' ? 'selected' : '' }}>Dikunci (Final / Selesai)</option>
            </select>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs">
            <i class="bi bi-check2-circle text-sm"></i>
            <span>Simpan Perubahan Pagu</span>
        </button>
    </div>
</form>

