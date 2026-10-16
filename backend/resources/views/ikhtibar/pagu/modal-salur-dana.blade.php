<form action="{{ route('pagu-divisi-ikhtibar.simpan-salur-dana', $divisi->id) }}" method="POST"
    enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between">
        <div>
            <span
                class="text-[10px] font-black uppercase tracking-wider text-blue-600 bg-blue-500/10 px-2 py-0.5 rounded border border-blue-500/20">
                Penyaluran / Pencairan Pagu
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Salur Dana: {{ $divisi->nama_divisi }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs">
        <!-- Rincian Pagu Divisi -->
        <div class="p-3.5 rounded-xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-500/20 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-zinc-600 dark:text-zinc-400">Total Pagu Disetujui:</span>
                <span class="font-bold text-zinc-900 dark:text-white">Rp
                    {{ number_format($divisi->nominal_pagu, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-600 dark:text-zinc-400">Sudah Dicairkan:</span>
                <span class="font-bold text-blue-600 dark:text-blue-400">Rp
                    {{ number_format($divisi->total_tersalurkan, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-blue-500/20 pt-1.5">
                <span class="font-extrabold text-blue-700 dark:text-blue-300">Sisa Pagu Belum Cair:</span>
                <span class="font-black text-blue-700 dark:text-blue-300">
                    Rp {{ number_format($divisi->sisa_pagu_belum_cair, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Kode Penyaluran -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Kode Transaksi <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode_penyaluran" value="{{ $kodeOtomatis }}"
                    class="m3-input-glass w-full uppercase font-mono font-bold" readonly>
            </div>

            <!-- Nominal Pencairan -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Nominal Pencairan (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="nominal" value="{{ min(1000000, $divisi->sisa_pagu_belum_cair) }}"
                    max="{{ max(1, $divisi->sisa_pagu_belum_cair) }}"
                    class="m3-input-glass w-full font-black text-sm text-blue-600 dark:text-blue-400" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tanggal Penyaluran -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Tanggal Penyaluran <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_penyaluran" value="{{ date('Y-m-d') }}"
                    class="m3-input-glass w-full font-bold" required>
            </div>

            <!-- Metode Penyaluran -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Metode Penyerahan <span class="text-rose-500">*</span>
                </label>
                <select name="metode_penyaluran" class="m3-input-glass w-full font-bold" required>
                    <option value="Tunai">Tunai / Uang Fisik</option>
                    <option value="Transfer">Transfer Bank / E-Wallet</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Disalurkan Oleh (BPH Bendahara) -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Disalurkan Oleh (BPH) <span class="text-rose-500">*</span>
                </label>
                <select name="disalurkan_oleh_id" class="m3-input-glass w-full font-bold" required>
                    @foreach ($bendaharas as $bph)
                        <option value="{{ $bph->id }}">
                            {{ $bph->nama_tampil }} ({{ $bph->jabatan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Penerima Dana (Ketua / Sekretaris / Bendahara / Divisi) -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Penerima Dana (Ketua / Sekretaris / Bendahara / Divisi) <span class="text-rose-500">*</span>
                </label>
                <select name="penerima_panitia_id" class="m3-input-glass w-full font-bold" required>
                    @forelse ($penerimas as $p)
                        <option value="{{ $p->id }}"
                            {{ ((isset($defaultPenerimaId) && $defaultPenerimaId == $p->id) || ($divisi->koordinator_tampil?->id == $p->id)) ? 'selected' : '' }}>
                            {{ $p->nama_tampil }} &mdash; {{ $p->jabatan }} ({{ $p->kelompok_jabatan === 'BPH' ? 'BPH Inti' : ($p->divisi?->nama_divisi ?? 'Divisi') }})
                        </option>
                    @empty
                        <option value="">-- Belum ada personel yang dapat menerima dana --</option>
                    @endforelse
                </select>
                <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                    Ketua, Sekretaris, Bendahara, maupun koordinator divisi dapat dipilih sebagai penerima dana pagu anggaran.
                </p>
            </div>
        </div>

        <!-- Bukti Serah Terima Fisik -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Foto Bukti Serah Terima / Kwitansi Fisik (Opsional)
            </label>
            <input type="file" name="bukti_serah_terima" accept="image/*"
                class="text-[11px] text-zinc-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-blue-500/10 file:text-blue-600">
        </div>

        <!-- Catatan -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Catatan Penyaluran (Termin / Keperluan)
            </label>
            <textarea name="catatan" rows="2"
                placeholder="Contoh: Pencairan termin ke-{{ $terminKe }} untuk uang muka perlengkapan..."
                class="m3-input-glass w-full text-xs">Pencairan termin ke-{{ $terminKe }} operasional divisi</textarea>
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs !bg-blue-600 hover:!bg-blue-700">
            <i class="bi bi-box-arrow-up-right text-sm"></i>
            <span>Salurkan Dana Pagu</span>
        </button>
    </div>
</form>
