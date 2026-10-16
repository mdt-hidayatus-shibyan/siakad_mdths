<form action="{{ isset($panitia) ? route('panitia-ikhtibar.update', $panitia->id) : route('panitia-ikhtibar.store', $kegiatan->id) }}"
    method="POST" enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[90vh]"
    data-modal-size="lg">
    @csrf
    @if (isset($panitia))
        @method('PUT')
    @endif

    <!-- Modal Header (Compact M3) -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 text-sm font-bold border border-primary/20">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-zinc-900 dark:text-white tracking-tight">
                    {{ isset($panitia) ? 'Edit Personel: ' . $panitia->nama_tampil : 'Tambah Personel Panitia' }}
                </h3>
                <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                    Kepanitiaan Ikhtibar MDT Hidayatus Shibyan
                </p>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-7 h-7 flex items-center justify-center rounded-lg bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body (Compact Space & Proportional Grid) -->
    <div class="p-4 sm:p-5 overflow-y-auto custom-scrollbar flex-1 space-y-3.5 text-xs" x-data="{
        tipe: '{{ $panitia->tipe_personel ?? 'ustadz' }}',
        kelompok: '{{ $panitia->kelompok_jabatan ?? 'BPH' }}'
    }">
        <!-- 1. Asal Personel Panitia (Ustadz vs Eksternal) -->
        <div class="space-y-1">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                Asal Personel Panitia <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="p-2 sm:p-2.5 rounded-xl border cursor-pointer flex items-center gap-2 transition-all"
                    :class="tipe === 'ustadz' ? 'border-primary bg-primary/5 text-primary shadow-xs' : 'border-zinc-200 dark:border-zinc-700/80 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/50'">
                    <input type="radio" name="tipe_personel" value="ustadz" x-model="tipe" class="sr-only">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                        :class="tipe === 'ustadz' ? 'bg-primary/10 text-primary' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'">
                        <i class="bi bi-person-check-fill text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-xs leading-tight truncate">Dewan Ustadz</div>
                        <div class="text-[9px] opacity-75 truncate">Internal MDT</div>
                    </div>
                </label>

                <label class="p-2 sm:p-2.5 rounded-xl border cursor-pointer flex items-center gap-2 transition-all"
                    :class="tipe === 'eksternal' ? 'border-primary bg-primary/5 text-primary shadow-xs' : 'border-zinc-200 dark:border-zinc-700/80 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/50'">
                    <input type="radio" name="tipe_personel" value="eksternal" x-model="tipe" class="sr-only">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                        :class="tipe === 'eksternal' ? 'bg-primary/10 text-primary' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'">
                        <i class="bi bi-person-plus-fill text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-xs leading-tight truncate">Eksternal</div>
                        <div class="text-[9px] opacity-75 truncate">Wali / Tokoh / Pemuda</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Bagian Input Tipe Ustadz -->
        <div x-show="tipe === 'ustadz'" x-transition class="space-y-1">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                Pilih Nama Ustadz / Guru <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
                <select name="ustadz_id" class="m3-input-glass w-full text-xs py-2 appearance-none cursor-pointer !pr-9">
                    <option value="">-- Pilih Nama Ustadz --</option>
                    @foreach ($ustadzs as $u)
                        <option value="{{ $u->id }}" {{ (isset($panitia) && $panitia->ustadz_id == $u->id) ? 'selected' : '' }}>
                            {{ $u->nama_lengkap }} (NIGM: {{ $u->nigm ?: '-' }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- Bagian Input Tipe Eksternal -->
        <div x-show="tipe === 'eksternal'" x-transition
            class="space-y-2.5 p-3 rounded-xl bg-zinc-50/80 dark:bg-zinc-800/30 border border-zinc-200/70 dark:border-zinc-700/60">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" value="{{ $panitia->nama_lengkap ?? '' }}"
                        placeholder="Contoh: H. Ahmad Subagio, S.Pd." class="m3-input-glass w-full text-xs py-1.5">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        No. HP / WhatsApp
                    </label>
                    <input type="text" name="no_hp" value="{{ $panitia->no_hp ?? '' }}"
                        placeholder="Contoh: 081234567890" class="m3-input-glass w-full text-xs py-1.5">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        NIK (Opsional)
                    </label>
                    <input type="text" name="nik" value="{{ $panitia->nik ?? '' }}"
                        placeholder="16 digit NIK" class="m3-input-glass w-full text-xs py-1.5 font-mono">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Jenis Kelamin
                    </label>
                    <select name="jenis_kelamin" class="m3-input-glass w-full text-xs py-1.5">
                        <option value="L" {{ (isset($panitia) && $panitia->jenis_kelamin === 'L') ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ (isset($panitia) && $panitia->jenis_kelamin === 'P') ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Unsur Asal
                    </label>
                    <input type="text" name="unsur_asal" value="{{ $panitia->unsur_asal ?? '' }}"
                        placeholder="Wali Murid / Pemuda" class="m3-input-glass w-full text-xs py-1.5">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-0.5">
                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Foto (Opsional)
                    </label>
                    <input type="file" name="foto" accept="image/*" class="text-[10px] text-zinc-500 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-primary/10 file:text-primary">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Spesimen TTD (Opsional)
                    </label>
                    <input type="file" name="tanda_tangan" accept="image/*" class="text-[10px] text-zinc-500 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-primary/10 file:text-primary">
                </div>
            </div>
        </div>

        <div class="border-t border-zinc-100 dark:border-zinc-800/80 pt-1"></div>

        <!-- 2. Kelompok Jabatan & Divisi Penugasan -->
        <div class="space-y-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 items-end">
                <div class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Kelompok Kepanitiaan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800/60 rounded-xl border border-zinc-200/80 dark:border-zinc-700/60">
                        <label class="py-1.5 px-2 rounded-lg cursor-pointer text-center font-bold text-xs transition-all flex items-center justify-center gap-1.5"
                            :class="kelompok === 'BPH' ? 'bg-amber-500 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'">
                            <input type="radio" name="kelompok_jabatan" value="BPH" x-model="kelompok" class="sr-only">
                            <i class="bi bi-star-fill text-[10px]"></i>
                            <span>BPH Inti</span>
                        </label>
                        <label class="py-1.5 px-2 rounded-lg cursor-pointer text-center font-bold text-xs transition-all flex items-center justify-center gap-1.5"
                            :class="kelompok === 'Divisi' ? 'bg-blue-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'">
                            <input type="radio" name="kelompok_jabatan" value="Divisi" x-model="kelompok" class="sr-only">
                            <i class="bi bi-grid-fill text-[10px]"></i>
                            <span>Divisi</span>
                        </label>
                    </div>
                </div>

                <!-- Divisi Penugasan (Muncul jika kelompok = Divisi) -->
                <div x-show="kelompok === 'Divisi'" x-transition class="space-y-1">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                        Divisi Penugasan <span class="text-rose-500">*</span>
                    </label>
                    <select name="divisi_ikhtibar_id" class="m3-input-glass w-full text-xs font-bold py-1.5">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach ($divisis as $d)
                            <option value="{{ $d->id }}" {{ (isset($panitia) && $panitia->divisi_ikhtibar_id == $d->id) ? 'selected' : '' }}>
                                {{ $d->nama_divisi }} ({{ $d->kode_divisi }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Info BPH (Muncul jika kelompok = BPH) -->
                <div x-show="kelompok === 'BPH'" x-transition class="py-2 px-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400 text-[11px] flex items-center gap-2">
                    <i class="bi bi-info-circle-fill text-xs shrink-0"></i>
                    <span class="leading-tight">Ketua, Sekretaris, Bendahara & Para Wakil</span>
                </div>
            </div>
        </div>

        <!-- 3. Jabatan Spesifik Dalam SK -->
        <div class="space-y-1">
            <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                Jabatan Spesifik Dalam SK <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <input type="text" name="jabatan" id="input_jabatan" value="{{ $panitia->jabatan ?? '' }}"
                    placeholder="Contoh: Ketua, Sekretaris, Bendahara, Koordinator Divisi, Anggota..."
                    class="m3-input-glass w-full text-xs font-bold py-1.5" list="daftar_saran_jabatan" required>
                <datalist id="daftar_saran_jabatan">
                    <option value="Ketua">
                    <option value="Wakil Ketua">
                    <option value="Sekretaris">
                    <option value="Wakil Sekretaris">
                    <option value="Bendahara">
                    <option value="Wakil Bendahara">
                    <option value="Koordinator Divisi">
                    <option value="Anggota Divisi">
                </datalist>
            </div>
        </div>

        <!-- 4. Keterangan Penugasan & Status Aktif -->
        <div class="space-y-2">
            <div class="space-y-1">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                    Keterangan Penugasan (Opsional)
                </label>
                <textarea name="keterangan" rows="2" placeholder="Catatan tambahan penugasan..."
                    class="m3-input-glass w-full text-xs py-1.5">{{ $panitia->keterangan ?? old('keterangan') }}</textarea>
            </div>

            <div class="pt-0.5">
                <x-toggle
                    name="is_active"
                    id="panitia_is_active"
                    value="1"
                    :checked="!isset($panitia) || $panitia->is_active"
                    label="Personel Aktif Dalam SK"
                />
            </div>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-4 py-3 flex items-center justify-end gap-2">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs py-1.5 px-3">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs py-1.5 px-3.5 flex items-center gap-1.5">
            <i class="bi bi-check2-circle text-xs"></i>
            <span>{{ isset($panitia) ? 'Simpan Perubahan' : 'Tambahkan Panitia' }}</span>
        </button>
    </div>
</form>
