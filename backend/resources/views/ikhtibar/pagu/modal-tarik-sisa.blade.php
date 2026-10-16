<form action="{{ route('pagu-divisi-ikhtibar.simpan-tarik-sisa', $divisi->id) }}" method="POST"
    enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 bg-teal-500/10 px-2 py-0.5 rounded border border-teal-500/20">
                Fitur Penarikan Sisa Pagu / Refund
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Tarik Sisa Pagu: {{ $divisi->nama_divisi }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs" x-data="{
        kategori: 'Sisa Kas Fisik',
        maxNominal: {{ $sisaKasFisik }},
        sisaKas: {{ $sisaKasFisik }},
        sisaAlokasi: {{ $sisaPaguBelumCair }}
    }">
        <!-- Ringkasan Posisi Kas & Alokasi Divisi -->
        <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-500/20">
            <div>
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase font-bold block">Sisa Kas Fisik di Divisi</span>
                <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($sisaKasFisik, 0, ',', '.') }}
                </span>
                <div class="text-[10px] text-zinc-400 mt-0.5">(Tersalurkan − SPJ Belanja)</div>
            </div>
            <div>
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase font-bold block">Sisa Alokasi Belum Cair</span>
                <span class="text-base font-black text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($sisaPaguBelumCair, 0, ',', '.') }}
                </span>
                <div class="text-[10px] text-zinc-400 mt-0.5">(Pagu − Total Tersalurkan)</div>
            </div>
        </div>

        <!-- 1. Kategori Penarikan (3 Opsi Interaktif) -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Skema / Kategori Penarikan <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <!-- Opsi 1: Sisa Kas Fisik -->
                <label class="p-3 rounded-xl border cursor-pointer flex flex-col justify-between transition-all"
                    :class="kategori === 'Sisa Kas Fisik' ? 'border-teal-500 bg-teal-500/10 text-teal-800 dark:text-teal-300' : 'border-zinc-200 dark:border-zinc-700 text-zinc-600'">
                    <input type="radio" name="kategori_penarikan" value="Sisa Kas Fisik" x-model="kategori" class="sr-only">
                    <div class="flex items-center gap-1.5 font-bold text-xs">
                        <i class="bi bi-cash-coin text-teal-600"></i>
                        <span>Sisa Kas Fisik</span>
                    </div>
                    <p class="text-[10px] mt-1 opacity-80">Pengembalian uang tunai sisa belanja divisi ke BPH.</p>
                </label>

                <!-- Opsi 2: Tarik Sisa Alokasi -->
                <label class="p-3 rounded-xl border cursor-pointer flex flex-col justify-between transition-all"
                    :class="kategori === 'Tarik Sisa Alokasi' ? 'border-teal-500 bg-teal-500/10 text-teal-800 dark:text-teal-300' : 'border-zinc-200 dark:border-zinc-700 text-zinc-600'">
                    <input type="radio" name="kategori_penarikan" value="Tarik Sisa Alokasi" x-model="kategori" class="sr-only">
                    <div class="flex items-center gap-1.5 font-bold text-xs">
                        <i class="bi bi-scissors text-teal-600"></i>
                        <span>Tarik Alokasi</span>
                    </div>
                    <p class="text-[10px] mt-1 opacity-80">Budget sweep alokasi yang belum dicairkan.</p>
                </label>

                <!-- Opsi 3: Realokasi Antar Divisi -->
                <label class="p-3 rounded-xl border cursor-pointer flex flex-col justify-between transition-all"
                    :class="kategori === 'Realokasi Antar Divisi' ? 'border-teal-500 bg-teal-500/10 text-teal-800 dark:text-teal-300' : 'border-zinc-200 dark:border-zinc-700 text-zinc-600'">
                    <input type="radio" name="kategori_penarikan" value="Realokasi Antar Divisi" x-model="kategori" class="sr-only">
                    <div class="flex items-center gap-1.5 font-bold text-xs">
                        <i class="bi bi-arrow-left-right text-teal-600"></i>
                        <span>Realokasi Divisi</span>
                    </div>
                    <p class="text-[10px] mt-1 opacity-80">Alihkan sisa pagu ke divisi lain yang butuh dana.</p>
                </label>
            </div>
        </div>

        <!-- Pilihan Divisi Tujuan (Muncul jika Realokasi) -->
        <div x-show="kategori === 'Realokasi Antar Divisi'" class="space-y-1.5 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Divisi Tujuan Penerima Realokasi Dana <span class="text-rose-500">*</span>
            </label>
            <select name="divisi_tujuan_realokasi_id" class="m3-input-glass w-full font-bold">
                <option value="">-- Pilih Divisi Penerima Alihan Dana --</option>
                @foreach ($divisiLain as $dl)
                    <option value="{{ $dl->id }}">
                        {{ $dl->nama_divisi }} (Pagu Saat Ini: Rp {{ number_format($dl->nominal_pagu, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Nominal Penarikan -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Nominal Penarikan / Pengembalian (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="nominal"
                    x-bind:value="kategori === 'Sisa Kas Fisik' ? sisaKas : sisaAlokasi"
                    min="1" step="1000"
                    class="m3-input-glass w-full font-black text-sm text-teal-600 dark:text-teal-400" required>
            </div>

            <!-- Tanggal Penarikan -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Tanggal Penarikan <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_penyaluran" value="{{ date('Y-m-d') }}"
                    class="m3-input-glass w-full font-bold" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Diserahkan Oleh (Ketua / Sekretaris / Bendahara / Divisi) -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Diserahkan Oleh (Ketua / Sekretaris / Bendahara / Divisi) <span class="text-rose-500">*</span>
                </label>
                <select name="disalurkan_oleh_id" class="m3-input-glass w-full font-bold" required>
                    @forelse ($penyerahs as $penyerah)
                        <option value="{{ $penyerah->id }}"
                            {{ ((isset($defaultPenyerahId) && $defaultPenyerahId == $penyerah->id) || ($divisi->koordinator_tampil?->id == $penyerah->id)) ? 'selected' : '' }}>
                            {{ $penyerah->nama_tampil }} &mdash; {{ $penyerah->jabatan }} ({{ $penyerah->kelompok_jabatan === 'BPH' ? 'BPH Inti' : ($penyerah->divisi?->nama_divisi ?? 'Divisi') }})
                        </option>
                    @empty
                        <option value="">-- Belum ada personel yang dapat menyerahkan dana --</option>
                    @endforelse
                </select>
            </div>

            <!-- Diterima Kembali Oleh (BPH Bendahara) -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Diterima Kembali Oleh (BPH) <span class="text-rose-500">*</span>
                </label>
                <select name="penerima_panitia_id" class="m3-input-glass w-full font-bold" required>
                    @foreach ($bendaharas as $bph)
                        <option value="{{ $bph->id }}">
                            {{ $bph->nama_tampil }} ({{ $bph->jabatan }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Metode Penyerahan -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Metode Pengembalian <span class="text-rose-500">*</span>
                </label>
                <select name="metode_penyaluran" class="m3-input-glass w-full font-bold" required>
                    <option value="Tunai">Tunai / Fisik ke Bendahara</option>
                    <option value="Transfer">Transfer ke Rekening BPH</option>
                </select>
            </div>

            <!-- Upload Berkas Bukti -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Bukti Penyerahan / Tanda Terima (Opsional)
                </label>
                <input type="file" name="bukti_serah_terima" accept="image/*"
                    class="text-[11px] text-zinc-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-teal-500/10 file:text-teal-600">
            </div>
        </div>

        <!-- Catatan -->
        <div class="space-y-1.5">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                Catatan Penarikan / Berita Acara
            </label>
            <textarea name="catatan" rows="2" placeholder="Contoh: Pengembalian sisa saldo kas operasional setelah seluruh kegiatan divisi selesai..."
                class="m3-input-glass w-full text-xs">Penarikan sisa kas/pagu operasional divisi {{ $divisi->nama_divisi }}</textarea>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs !bg-teal-600 hover:!bg-teal-700">
            <i class="bi bi-arrow-down-left-circle-fill text-sm"></i>
            <span>Proses Penarikan Sisa</span>
        </button>
    </div>
</form>

