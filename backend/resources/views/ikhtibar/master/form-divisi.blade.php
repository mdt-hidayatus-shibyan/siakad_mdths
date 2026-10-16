<form action="{{ isset($divisi) ? route('master-ikhtibar.update-divisi', $divisi->id) : route('master-ikhtibar.store-divisi', $kegiatan->id) }}"
    method="POST" class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    @if (isset($divisi))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded border border-primary/20">
                Divisi Kepanitiaan Ikhtibar
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                {{ isset($divisi) ? 'Edit Divisi: ' . $divisi->nama_divisi : 'Tambah Divisi Ikhtibar Baru' }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs">
        <!-- Quick Preset Pos BPH / Divisi -->
        <div class="space-y-1.5 p-3 rounded-xl bg-amber-500/5 border border-amber-500/20">
            <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">
                Template Cepat Pos Pagu Anggaran BPH:
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" onclick="setPresetDivisi('BPH-KETUA', 'Pos Pimpinan / Ketua Panitia', 'Operasional pimpinan, kebijakan taktis, rapat koordinasi, & tali asih tamu', 2000000)"
                    class="px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white border border-amber-500/20 text-[11px] font-bold transition-all">
                    <i class="bi bi-star-fill text-[9px] mr-1"></i> Pos Ketua
                </button>
                <button type="button" onclick="setPresetDivisi('BPH-SEKRE', 'Pos Kesekretariatan / Sekretaris', 'Pengadaan ATK, cetak SK, map, penggandaan formulir, surat undangan, & ID card', 1500000)"
                    class="px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white border border-amber-500/20 text-[11px] font-bold transition-all">
                    <i class="bi bi-file-earmark-text-fill text-[9px] mr-1"></i> Pos Sekretaris
                </button>
                <button type="button" onclick="setPresetDivisi('BPH-BENDA', 'Pos Kebendaharaan / Bendahara', 'Operasional kasir penagihan, cetak kwitansi, buku kas, materai, & biaya perbankan', 1500000)"
                    class="px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-700 dark:text-amber-300 hover:text-white border border-amber-500/20 text-[11px] font-bold transition-all">
                    <i class="bi bi-cash-stack text-[9px] mr-1"></i> Pos Bendahara
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Kode Divisi -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Kode Pos / Divisi <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode_divisi" value="{{ $divisi->kode_divisi ?? old('kode_divisi') }}"
                    placeholder="Contoh: BPH-KETUA, LOMBA, ACARA"
                    class="m3-input-glass w-full uppercase font-mono font-bold" required>
            </div>

            <!-- Urutan Tampilan -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Nomor Urut <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="urutan" value="{{ $divisi->urutan ?? ($urutanBaru ?? 1) }}" min="1"
                    class="m3-input-glass w-full" required>
            </div>
        </div>

        <!-- Nama Divisi -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Nama Divisi / Bidang Kerja <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_divisi" value="{{ $divisi->nama_divisi ?? old('nama_divisi') }}"
                placeholder="Contoh: Divisi Perlombaan & Musabaqah"
                class="m3-input-glass w-full" required>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Nominal Pagu Anggaran -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Pagu Anggaran Awal (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="nominal_pagu" value="{{ $divisi->nominal_pagu ?? 0 }}" min="0" step="50000"
                    class="m3-input-glass w-full font-bold text-primary dark:text-primary-light" required>
            </div>

            <!-- Status Pagu (Hanya saat edit) -->
            @if (isset($divisi))
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Status Pagu Anggaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_pagu" class="m3-input-glass w-full">
                        <option value="Draft" {{ $divisi->status_pagu === 'Draft' ? 'selected' : '' }}>Draft (Pengajuan)</option>
                        <option value="Disetujui" {{ $divisi->status_pagu === 'Disetujui' ? 'selected' : '' }}>Disetujui BPH</option>
                        <option value="Dikunci" {{ $divisi->status_pagu === 'Dikunci' ? 'selected' : '' }}>Dikunci (Final)</option>
                    </select>
                </div>
            @endif
        </div>

        <!-- Deskripsi Tugas -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Deskripsi Tugas & Tanggung Jawab
            </label>
            <textarea name="deskripsi_tugas" rows="3" placeholder="Rincian lingkup tugas divisi..."
                class="m3-input-glass w-full text-xs">{{ $divisi->deskripsi_tugas ?? old('deskripsi_tugas') }}</textarea>
        </div>

        <!-- Status Aktif -->
        <div class="pt-2">
            <x-toggle
                name="is_active"
                id="is_active"
                value="1"
                :checked="!isset($divisi) || $divisi->is_active"
                label="Divisi Aktif Digunakan"
            />
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
            <span>{{ isset($divisi) ? 'Simpan Perubahan' : 'Tambahkan Divisi' }}</span>
        </button>
</form>

<script>
    function setPresetDivisi(kode, nama, desc, pagu) {
        let kodeInput = document.querySelector('input[name="kode_divisi"]');
        let namaInput = document.querySelector('input[name="nama_divisi"]');
        let descInput = document.querySelector('textarea[name="deskripsi_tugas"]');
        let paguInput = document.querySelector('input[name="nominal_pagu"]');
        
        if (kodeInput) kodeInput.value = kode;
        if (namaInput) namaInput.value = nama;
        if (descInput) descInput.value = desc;
        if (paguInput && (!paguInput.value || paguInput.value == 0)) {
            paguInput.value = pagu;
        }
    }
</script>

