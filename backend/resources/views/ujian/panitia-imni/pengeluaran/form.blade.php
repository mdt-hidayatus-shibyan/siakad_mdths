<form
    action="{{ isset($pengeluaran) ? route('pengeluaran-imni.update', $pengeluaran->id) : route('pengeluaran-imni.store') }}"
    method="POST" enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[90vh]"
    data-refresh-target="#data-table-container">
    @csrf
    @if (isset($pengeluaran))
        @method('PUT')
    @endif

    <input type="hidden" name="tahun_pelajaran_id"
        value="{{ isset($pengeluaran) ? $pengeluaran->tahun_pelajaran_id : $selectedTahun->id }}">

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div
                class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-500/20">
                <i class="bi bi-wallet-fill"></i>
            </div>
            <div>
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                    Buku Kas IMNI
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    {{ isset($pengeluaran) ? 'Edit Pengeluaran Kas IMNI' : 'Catat Pengeluaran Baru' }}
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
        <!-- 1. Kategori & Judul Pengeluaran -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Kategori <span class="text-rose-500">*</span>
                </label>
                <div class="relative group/select">
                    <select name="kategori"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat }}"
                                {{ isset($pengeluaran) && $pengeluaran->kategori === $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2 space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Uraian / Judul Pengeluaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul_pengeluaran"
                    value="{{ $pengeluaran->judul_pengeluaran ?? old('judul_pengeluaran') }}" required
                    placeholder="Contoh: Honor Pengawas Ruang 01 Hari Ke-1"
                    class="m3-input-glass w-full text-xs font-bold">
            </div>
        </div>

        <!-- 2. Nominal & Tanggal -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Nominal Biaya (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs text-zinc-400">Rp</span>
                    <input type="number" name="nominal"
                        value="{{ isset($pengeluaran) ? (int) $pengeluaran->nominal : old('nominal') }}" min="1"
                        required placeholder="Contoh: 150000"
                        class="m3-input-glass w-full !pl-10 !pr-4 text-sm font-black font-mono">
                </div>
            </div>

            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Tanggal Pengeluaran <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_pengeluaran"
                    value="{{ isset($pengeluaran) ? ($pengeluaran->tanggal_pengeluaran ? $pengeluaran->tanggal_pengeluaran->format('Y-m-d') : date('Y-m-d')) : date('Y-m-d') }}"
                    required class="m3-input-glass w-full text-xs font-bold">
            </div>
        </div>

        <!-- 3. Penerima Dana & Metode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Penerima Dana / Pihak Ke-3
                </label>
                <input type="text" name="penerima_dana"
                    value="{{ $pengeluaran->penerima_dana ?? old('penerima_dana') }}"
                    placeholder="Nama ustadz / toko percetakan..." class="m3-input-glass w-full text-xs font-bold">
            </div>

            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Metode Pembayaran <span class="text-rose-500">*</span>
                </label>
                <div class="relative group/select">
                    <select name="metode_pembayaran"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="Tunai"
                            {{ isset($pengeluaran) && $pengeluaran->metode_pembayaran === 'Tunai' ? 'selected' : '' }}>
                            Tunai (Cash)</option>
                        <option value="Transfer"
                            {{ isset($pengeluaran) && $pengeluaran->metode_pembayaran === 'Transfer' ? 'selected' : '' }}>
                            Transfer Bank</option>
                        <option value="Lainnya"
                            {{ isset($pengeluaran) && $pengeluaran->metode_pembayaran === 'Lainnya' ? 'selected' : '' }}>
                            Lainnya</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Upload Bukti Nota / Kuitansi -->
        <div class="space-y-1.5">
            <label
                class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Bukti Nota / Kuitansi <span class="text-zinc-400 font-normal lowercase">(foto/pdf, maks 3MB)</span>
            </label>
            <input type="file" name="bukti_nota" accept="image/jpeg,image/png,image/jpg,application/pdf"
                class="m3-input-glass w-full text-xs file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
            @if (isset($pengeluaran) && $pengeluaran->bukti_nota)
                <p
                    class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-1">
                    <i class="bi bi-paperclip"></i>
                    <span>Telah terlampir file nota saat ini (upload baru untuk mengganti)</span>
                </p>
            @endif
        </div>

        <!-- 5. Keterangan Tambahan -->
        <div class="space-y-1.5">
            <label
                class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Keterangan Tambahan <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
            </label>
            <textarea name="keterangan" rows="2" placeholder="Catatan tambahan rincian pengeluaran..."
                class="m3-input-glass w-full text-xs font-medium">{{ $pengeluaran->keterangan ?? old('keterangan') }}</textarea>
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit"
            class="m3-btn-primary text-xs flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 text-white">
            <i class="bi bi-check-lg"></i>
            <span>{{ isset($pengeluaran) ? 'Simpan Perubahan' : 'Catat Pengeluaran' }}</span>
        </button>
    </div>
</form>
