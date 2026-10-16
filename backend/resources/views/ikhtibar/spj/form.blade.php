<form action="{{ isset($spj) ? route('spj-ikhtibar.update', $spj->id) : route('spj-ikhtibar.store', $kegiatan->id) }}"
    method="POST" enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    @if (isset($spj))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">
                Surat Pertanggungjawaban (SPJ)
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                {{ isset($spj) ? 'Edit Laporan Belanja: ' . $spj->kode_spj : 'Input Bukti Belanja (SPJ) Baru' }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Kode SPJ -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Kode SPJ <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode_spj" value="{{ $spj->kode_spj ?? ($kodeOtomatis ?? '') }}"
                    class="m3-input-glass w-full uppercase font-mono font-bold" readonly required>
            </div>

            <!-- Divisi Penanggung Jawab -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Divisi Pemegang Anggaran
                </label>
                <select name="divisi_ikhtibar_id" class="m3-input-glass w-full font-bold">
                    <option value="">-- BPH Inti / Umum --</option>
                    @foreach ($divisis as $d)
                        <option value="{{ $d->id }}" {{ (isset($spj) && $spj->divisi_ikhtibar_id == $d->id) ? 'selected' : '' }}>
                            {{ $d->nama_divisi }} (Sisa Kas: Rp {{ number_format($d->sisa_kas_di_divisi, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Judul Pengeluaran -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Judul / Nama Pengeluaran <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="judul_pengeluaran" value="{{ $spj->judul_pengeluaran ?? old('judul_pengeluaran') }}"
                placeholder="Contoh: Pembelian Piala & Sertifikat Juara Lomba Tahfidz"
                class="m3-input-glass w-full font-bold" required>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Nominal Belanja -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Total Nominal Nota (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="nominal" value="{{ $spj->nominal ?? '' }}" min="1" step="500"
                    placeholder="Contoh: 450000"
                    class="m3-input-glass w-full font-black text-rose-600 dark:text-rose-400" required>
            </div>

            <!-- Tanggal Nota -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Tanggal Nota / Kuitansi <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_nota" value="{{ isset($spj) && $spj->tanggal_nota ? $spj->tanggal_nota->format('Y-m-d') : date('Y-m-d') }}"
                    class="m3-input-glass w-full font-bold" required>
            </div>

            <!-- Metode Bayar -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Metode Pembayaran <span class="text-rose-500">*</span>
                </label>
                <select name="metode_pembayaran" class="m3-input-glass w-full font-bold">
                    <option value="Tunai" {{ (isset($spj) && $spj->metode_pembayaran === 'Tunai') ? 'selected' : '' }}>Tunai / Cash</option>
                    <option value="Transfer" {{ (isset($spj) && $spj->metode_pembayaran === 'Transfer') ? 'selected' : '' }}>Transfer Bank</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Nama Toko / Vendor -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Nama Toko / Penerima Pembayaran
                </label>
                <input type="text" name="nama_toko_penerima" value="{{ $spj->nama_toko_penerima ?? old('nama_toko_penerima') }}"
                    placeholder="Contoh: Toko Trophy Barokah / Percetakan Madinah"
                    class="m3-input-glass w-full">
            </div>

            <!-- Petugas Pembelanja / Penginput -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Personel Pembelanja
                </label>
                <select name="diinput_oleh_id" class="m3-input-glass w-full font-bold">
                    <option value="">-- Pilih Panitia --</option>
                    @foreach ($panitias as $p)
                        <option value="{{ $p->id }}" {{ (isset($spj) && $spj->diinput_oleh_id == $p->id) ? 'selected' : '' }}>
                            {{ $p->nama_tampil }} ({{ $p->jabatan }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Rincian Item Belanja -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Rincian Item / Uraian Belanja
            </label>
            <textarea name="rincian_item" rows="2" placeholder="Contoh: 1. Piala Juara 1-3 (3 set x Rp 100.000), 2. Kertas sertifikat (2 rim)..."
                class="m3-input-glass w-full text-xs">{{ $spj->rincian_item ?? old('rincian_item') }}</textarea>
        </div>

        <!-- Upload Foto Nota / Struk -->
        <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden shrink-0">
                @if (isset($spj) && $spj->bukti_nota)
                    <img src="{{ $spj->bukti_nota_url }}" alt="Nota" class="w-full h-full object-cover">
                @else
                    <i class="bi bi-receipt-cutoff text-zinc-400 text-lg"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 text-[11px]">
                    Foto Nota / Struk Pembelian Asli (JPG/PNG Max 3MB)
                </label>
                <input type="file" name="bukti_nota" accept="image/*" class="text-[11px] text-zinc-500 mt-1 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-rose-500/10 file:text-rose-600">
            </div>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs !bg-rose-600 hover:!bg-rose-700">
            <i class="bi bi-check2-circle text-sm"></i>
            <span>{{ isset($spj) ? 'Simpan Perubahan SPJ' : 'Simpan Bukti Belanja (SPJ)' }}</span>
        </button>
    </div>
</form>

