<form action="{{ route('pembayaran-imni.bayar') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf
    <input type="hidden" name="peserta_imni_id" value="{{ $peserta->id }}">

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/20">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                    Kasir IMNI
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Kasir Pembayaran IMNI
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
        <!-- Info Ringkas Murid & Status Keuangan -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center font-black text-sm shrink-0 border border-primary/20">
                    {{ substr($peserta->murid?->nama_lengkap ?? 'M', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="font-black text-zinc-900 dark:text-white text-xs truncate">
                        {{ $peserta->murid?->nama_lengkap ?? '-' }}
                    </h4>
                    <p class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 mt-0.5">
                        NISM: {{ $peserta->murid?->nism ?? '-' }} • No. Peserta: <b class="text-zinc-700 dark:text-zinc-300">{{ $peserta->nomor_peserta ?? '-' }}</b>
                    </p>
                </div>
            </div>

            <!-- Kartu Status 3 Kolom -->
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-zinc-200/60 dark:border-zinc-700/60 text-center">
                <div class="p-2 rounded-xl bg-white dark:bg-zinc-900/80 border border-zinc-200/60 dark:border-zinc-800">
                    <p class="text-[9px] text-zinc-400 uppercase font-black tracking-wider">Total Tagihan</p>
                    <p class="text-xs font-black font-mono text-zinc-900 dark:text-white mt-0.5">
                        Rp {{ number_format($tagihan, 0, ',', '.') }}
                    </p>
                </div>
                <div class="p-2 rounded-xl bg-white dark:bg-zinc-900/80 border border-zinc-200/60 dark:border-zinc-800">
                    <p class="text-[9px] text-zinc-400 uppercase font-black tracking-wider">Sudah Bayar</p>
                    <p class="text-xs font-black font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                        Rp {{ number_format($terbayar, 0, ',', '.') }}
                    </p>
                </div>
                <div class="p-2 rounded-xl bg-white dark:bg-zinc-900/80 border border-zinc-200/60 dark:border-zinc-800">
                    <p class="text-[9px] text-zinc-400 uppercase font-black tracking-wider">Sisa Tagihan</p>
                    <p class="text-xs font-black font-mono text-amber-600 dark:text-amber-400 mt-0.5">
                        Rp {{ number_format($sisa, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 1. Input Nominal Pembayaran -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between ml-1">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                    Nominal Pembayaran Sekarang <span class="text-rose-500">*</span>
                </label>
                @if ($sisa > 0)
                    <button type="button" onclick="document.getElementById('nominal_bayar_input').value = {{ $sisa }}"
                        class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">
                        Bayar Lunas (Pas: Rp {{ number_format($sisa, 0, ',', '.') }})
                    </button>
                @endif
            </div>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs text-zinc-400">Rp</span>
                <input type="number" name="nominal_bayar" id="nominal_bayar_input" value="{{ $sisa > 0 ? $sisa : '' }}" min="0" step="1000" required
                    placeholder="Masukkan jumlah yang disetorkan..."
                    class="m3-input-glass w-full !pl-10 !pr-4 text-sm font-black font-mono">
            </div>
        </div>

        <!-- 2. Tanggal Bayar & Metode Pembayaran -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Tanggal Pembayaran <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" required
                    class="m3-input-glass w-full text-xs font-bold">
            </div>

            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Metode Pembayaran <span class="text-rose-500">*</span>
                </label>
                <div class="relative group/select">
                    <select name="metode_pembayaran" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="Tunai" {{ ($pembayaran?->metode_pembayaran ?? 'Tunai') === 'Tunai' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="Transfer" {{ ($pembayaran?->metode_pembayaran ?? '') === 'Transfer' ? 'selected' : '' }}>Transfer Bank / E-Wallet</option>
                        <option value="Lainnya" {{ ($pembayaran?->metode_pembayaran ?? '') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Nama Penyetor & Override Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Nama Penyetor / Wali
                </label>
                <input type="text" name="nama_penyetor" value="{{ $pembayaran?->nama_penyetor ?? $peserta->murid?->nama_lengkap }}" placeholder="Nama wali/penyetor..."
                    class="m3-input-glass w-full text-xs font-bold">
            </div>

            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Override Status Kelunasan
                </label>
                <div class="relative group/select">
                    <select name="status_override" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="Auto">Auto (Sesuai Sisa Tagihan)</option>
                        <option value="Dispensasi" {{ ($pembayaran?->status_pembayaran ?? '') === 'Dispensasi' ? 'selected' : '' }}>Dispensasi Panitia</option>
                        <option value="Lunas" {{ ($pembayaran?->status_pembayaran ?? '') === 'Lunas' ? 'selected' : '' }}>Paksa Status Lunas</option>
                        <option value="Belum Lunas">Belum Lunas</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Catatan / Keterangan Pembayaran -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Catatan / Keterangan <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
            </label>
            <input type="text" name="keterangan" value="{{ $pembayaran?->keterangan }}" placeholder="Contoh: Titip lewat wali kelas, pembayaran tahap 1..."
                class="m3-input-glass w-full text-xs font-medium">
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white">
            <i class="bi bi-check-circle-fill"></i>
            <span>Simpan Pembayaran</span>
        </button>
    </div>
</form>
