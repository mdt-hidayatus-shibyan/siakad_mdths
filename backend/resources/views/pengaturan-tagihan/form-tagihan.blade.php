<form action="{{ isset($biaya) ? route('pengaturan-tagihan.update', $biaya->id) : route('pengaturan-tagihan.store') }}"
    method="POST" class="ajax-form" data-refresh-target="#data-grid-container">

    @csrf
    @if (isset($biaya))
        @method('PUT')
    @endif

    <!-- HEADER MODAL -->
    <div
        class="px-5 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between bg-white dark:bg-[#0c0c0e] rounded-t-3xl">
        <h3 class="font-black text-sm text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
            <div
                class="w-7 h-7 rounded-xl {{ isset($biaya) ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' }} flex items-center justify-center text-xs shrink-0">
                <i class="bi {{ isset($biaya) ? 'bi-pencil-square' : 'bi-plus-lg' }}"></i>
            </div>
            {{ isset($biaya) ? 'Koreksi Tarif Tagihan' : 'Tambah Tarif Tagihan' }}
        </h3>
        <button type="button" data-dismiss="modal"
            class="w-7 h-7 flex items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 hover:bg-rose-500/10 hover:text-rose-500 transition-colors outline-none">
            <i class="bi bi-x-lg text-xs font-black"></i>
        </button>
    </div>

    <!-- BODY MODAL -->
    <div class="p-5 space-y-4 bg-white dark:bg-[#0c0c0e]">
        <div>
            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                Kode Tagihan <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="kode_tagihan"
                value="{{ isset($biaya) ? $biaya->kode_tagihan : old('kode_tagihan') }}" maxlength="10"
                placeholder="Contoh: SPP/IMDA/IMNI/MLD" class="m3-input-glass w-full text-xs font-bold">
        </div>
        <div>
            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                Nama Tagihan <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_tagihan"
                value="{{ isset($biaya) ? $biaya->nama_tagihan : old('nama_tagihan') }}"
                placeholder="Contoh: SPP Syahriyah / Uang Gedung" class="m3-input-glass w-full text-xs font-bold">
        </div>

        <div class="grid grid-cols-2 gap-3.5">
            <div class="col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                    Peruntukan Kelas
                </label>
                <div class="relative">
                    <select name="level_id"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value=""
                            {{ (!isset($biaya) && !old('level_id')) || (isset($biaya) && is_null($biaya->level_id)) ? 'selected' : '' }}>
                            -- Berlaku Untuk Semua Kelas --
                        </option>
                        @foreach ($daftarLevel as $lvl)
                            <option value="{{ $lvl->id }}"
                                {{ (isset($biaya) && $biaya->level_id == $lvl->id) || old('level_id') == $lvl->id ? 'selected' : '' }}>
                                Kelas: {{ $lvl->nama_level }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <div class="col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                    Sasaran Penagihan <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="sasaran"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value="murid"
                            {{ (isset($biaya) && $biaya->sasaran == 'murid') || old('sasaran') == 'murid' ? 'selected' : '' }}>
                            Per Murid / Santri
                        </option>
                        <option value="wali_murid"
                            {{ (isset($biaya) && $biaya->sasaran == 'wali_murid') || old('sasaran') == 'wali_murid' ? 'selected' : '' }}>
                            Per Wali Murid / Kepala Keluarga (KK)
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <div class="col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                    Tipe Penagihan <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="tipe"
                        class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        <option value="bulanan"
                            {{ (isset($biaya) && $biaya->tipe == 'bulanan') || old('tipe') == 'bulanan' ? 'selected' : '' }}>
                            Bulanan (Syahriyah)
                        </option>
                        <option value="semester"
                            {{ (isset($biaya) && $biaya->tipe == 'semester') || old('tipe') == 'semester' ? 'selected' : '' }}>
                            Per Semester (IMDA/IMNI)
                        </option>
                        <option value="insidental"
                            {{ (isset($biaya) && $biaya->tipe == 'insidental') || old('tipe') == 'insidental' ? 'selected' : '' }}>
                            Sekali Bayar / Insidental
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </div>

            <div class="col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                    Nominal Tarif (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative flex items-center">
                    <span
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-zinc-400 pointer-events-none text-xs">Rp</span>
                    <input type="number" name="nominal" min="0"
                        value="{{ isset($biaya) ? $biaya->nominal : old('nominal') }}" placeholder="0"
                        class="m3-input-glass w-full !pl-9 text-xs font-bold text-zinc-900 dark:text-white">
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER MODAL -->
    <div
        class="px-5 py-3.5 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200/80 dark:border-zinc-800 flex justify-end gap-2.5 rounded-b-3xl">
        <button type="button" data-dismiss="modal"
            class="px-4 py-2 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors outline-none shadow-2xs">
            Batal
        </button>
        <button type="submit"
            class="{{ isset($biaya) ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'm3-btn-primary' }} px-4 py-2 rounded-xl text-xs font-black shadow-2xs transition-colors flex items-center gap-1.5">
            <i class="bi bi-save-fill text-xs"></i> <span>{{ isset($biaya) ? 'Update Tarif' : 'Simpan Tarif' }}</span>
        </button>
    </div>
</form>
