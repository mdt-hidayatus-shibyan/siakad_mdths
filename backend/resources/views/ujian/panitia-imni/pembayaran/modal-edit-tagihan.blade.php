<form action="{{ route('pembayaran-imni.tarif-individual', $peserta->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-lg border border-primary/20">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-primary dark:text-primary-dark bg-primary/10 px-2 py-0.5 rounded border border-primary/20">
                    Penyesuaian Tagihan
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Ubah Tagihan Murid
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
        <!-- Info Ringkas Murid -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center font-black text-sm shrink-0 border border-primary/20">
                {{ substr($peserta->murid?->nama_lengkap ?? 'M', 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <h4 class="font-black text-zinc-900 dark:text-white text-xs truncate">
                    {{ $peserta->murid?->nama_lengkap ?? '-' }}
                </h4>
                <p class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 mt-0.5">
                    NISM: {{ $peserta->murid?->nism ?? '-' }} • Kelas: <b class="text-primary">{{ $peserta->level?->nama_level ?? '-' }}</b>
                </p>
            </div>
        </div>

        <!-- 1. Input Nominal Tagihan Baru -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Nominal Tagihan Baru <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs text-zinc-400">Rp</span>
                <input type="number" name="nominal_tagihan" value="{{ (int) $tagihan }}" min="0" step="1000" required
                    placeholder="Contoh: 150000"
                    class="m3-input-glass w-full !pl-10 !pr-4 text-sm font-black font-mono">
            </div>
            <p class="text-[11px] font-semibold text-zinc-400 mt-1">
                Sisa tagihan akan otomatis dikalkulasi ulang berdasarkan riwayat nominal yang sudah dibayarkan murid.
            </p>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5">
            <i class="bi bi-check-lg"></i>
            <span>Simpan Perubahan</span>
        </button>
    </div>
</form>
