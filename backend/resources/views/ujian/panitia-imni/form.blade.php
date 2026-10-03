<form action="{{ isset($panitia) ? route('panitia-imni.update', $panitia->id) : route('panitia-imni.store') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    @if (isset($panitia))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                Imtihan Nihai (IMNI)
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                {{ isset($panitia) ? 'Edit Susunan Panitia IMNI' : 'Tambah Panitia IMNI Baru' }}
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        <!-- 1. Tahun Pelajaran -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Tahun Pelajaran <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
                <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                    <option value="">-- Pilih Tahun Pelajaran --</option>
                    @foreach ($tahun_pelajarans as $tp)
                        @php
                            $isSelected = isset($panitia)
                                ? $panitia->tahun_pelajaran_id == $tp->id
                                : (isset($selectedTahunId) ? $selectedTahunId == $tp->id : $tp->is_active);
                        @endphp
                        <option value="{{ $tp->id }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} H - {{ $tp->nama_masehi }} M {{ $tp->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- 2. Pilih Ustadz -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Pilih Ustadz / Guru <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
                <select name="ustadz_id" id="ustadz_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                    <option value="">-- Pilih Ustadz --</option>
                    @foreach ($ustadzs as $u)
                        @php
                            $isAssigned = isset($assignedUstadzIds) && in_array($u->id, $assignedUstadzIds) && (!isset($panitia) || $panitia->ustadz_id != $u->id);
                        @endphp
                        <option value="{{ $u->id }}" {{ (isset($panitia) && $panitia->ustadz_id == $u->id) ? 'selected' : '' }} {{ $isAssigned ? 'disabled' : '' }}>
                            {{ $u->nama_lengkap }} (NIGM: {{ $u->nigm ?? '-' }}) {{ $isAssigned ? '- [Sudah Ada di Panitia]' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- 3. Pilihan Jabatan (Ketua, Bendahara, Anggota) -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Jabatan Kepanitiaan <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <!-- Pilihan: Ketua -->
                <label class="cursor-pointer relative">
                    <input type="radio" name="jabatan" value="Ketua" class="peer sr-only"
                        {{ (isset($panitia) && $panitia->jabatan == 'Ketua') ? 'checked' : '' }}>
                    <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 transition-all flex flex-col items-center text-center">
                        <i class="bi bi-award-fill text-amber-500 text-lg mb-1"></i>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Ketua</span>
                        <span class="text-[10px] text-zinc-400 mt-0.5">Jadwal & Pengawas</span>
                    </div>
                </label>

                <!-- Pilihan: Bendahara -->
                <label class="cursor-pointer relative">
                    <input type="radio" name="jabatan" value="Bendahara" class="peer sr-only"
                        {{ (isset($panitia) && $panitia->jabatan == 'Bendahara') ? 'checked' : '' }}>
                    <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 transition-all flex flex-col items-center text-center">
                        <i class="bi bi-wallet2 text-emerald-500 text-lg mb-1"></i>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Bendahara</span>
                        <span class="text-[10px] text-zinc-400 mt-0.5">Tagihan & Kas IMNI</span>
                    </div>
                </label>

                <!-- Pilihan: Anggota -->
                <label class="cursor-pointer relative">
                    <input type="radio" name="jabatan" value="Anggota" class="peer sr-only"
                        {{ (!isset($panitia) || $panitia->jabatan == 'Anggota') ? 'checked' : '' }}>
                    <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 peer-checked:border-sky-500 peer-checked:bg-sky-500/10 transition-all flex flex-col items-center text-center">
                        <i class="bi bi-people-fill text-sky-500 text-lg mb-1"></i>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Anggota</span>
                        <span class="text-[10px] text-zinc-400 mt-0.5">Nilai & Pelaksanaan</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 4. Nomor SK (Opsional) -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Nomor SK Kepanitiaan <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
            </label>
            <input type="text" name="no_sk" value="{{ $panitia->no_sk ?? old('no_sk') }}"
                placeholder="Contoh: SK/IMNI/MDT/2026/001" class="m3-input-glass w-full">
        </div>

        <!-- 5. Keterangan / Tugas Khusus -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Keterangan / Tugas Khusus <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
            </label>
            <input type="text" name="keterangan" value="{{ $panitia->keterangan ?? old('keterangan') }}"
                placeholder="Contoh: Koordinator Ruang 01 & 02" class="m3-input-glass w-full">
        </div>

        <!-- 6. Status Aktif -->
        <div class="pt-1">
            <x-toggle
                name="is_active"
                :checked="!isset($panitia) || $panitia->is_active"
                label="Status Kepanitiaan Aktif"
            />
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
            <span>{{ isset($panitia) ? 'Simpan Perubahan' : 'Tetapkan Panitia' }}</span>
        </button>
    </div>
</form>
