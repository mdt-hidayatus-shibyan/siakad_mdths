<form action="{{ route('tagihan-ikhtibar.proses-bayar', $tagihan->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                Loket Pembayaran Ikhtibar
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Penerimaan Tagihan KK: {{ $tagihan->waliMurid?->nama_kepala_keluarga }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs">
        <!-- Rincian KK & Anak -->
        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Kepala Keluarga:</span>
                <span class="font-bold text-zinc-900 dark:text-white">{{ $tagihan->waliMurid?->nama_kepala_keluarga }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Dusun / Kampung:</span>
                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $tagihan->waliMurid?->kampung?->nama_kampung ?: '-' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Santri / Anak Aktif:</span>
                <span class="font-bold text-zinc-800 dark:text-zinc-200 text-right">
                    {{ $tagihan->waliMurid?->murids->pluck('nama_lengkap')->implode(', ') ?: '-' }}
                </span>
            </div>
            <div class="border-t border-zinc-200/60 dark:border-zinc-700/60 pt-2 flex items-center justify-between">
                <span class="font-extrabold text-zinc-700 dark:text-zinc-300">Total Tarif Ikhtibar:</span>
                <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Metode Pembayaran -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Metode Pembayaran <span class="text-rose-500">*</span>
                </label>
                <select name="metode_pembayaran" class="m3-input-glass w-full font-bold" required>
                    <option value="Tunai">Tunai / Cash (Bendahara)</option>
                    <option value="Transfer">Transfer Bank / E-Wallet</option>
                    <option value="Lainnya">Lainnya / Titipan</option>
                </select>
            </div>

            <!-- Tanggal Bayar -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Tanggal Pembayaran <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}"
                    class="m3-input-glass w-full font-bold" required>
            </div>
        </div>

        <!-- Catatan Transaksi -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Catatan Penerimaan (Opsional)
            </label>
            <textarea name="catatan" rows="2" placeholder="Contoh: Diterima langsung oleh Bendahara di kantor madrasah..."
                class="m3-input-glass w-full text-xs"></textarea>
        </div>

        @if ($bendahara)
            <div class="p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[11px] flex items-center gap-2">
                <i class="bi bi-shield-check text-sm shrink-0"></i>
                <span>Petugas Penerima: <strong>{{ $bendahara->nama_tampil }}</strong> ({{ $bendahara->jabatan }})</span>
            </div>
        @endif
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs !bg-emerald-600 hover:!bg-emerald-700">
            <i class="bi bi-check2-circle text-sm"></i>
            <span>Simpan & Lunaskan Tagihan</span>
        </button>
    </div>
</form>

