<form action="{{ isset($juri) ? route('juri-ujian-alquran.update', $juri->id) : route('juri-ujian-alquran.store') }}"
    method="POST" class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf
    @if (isset($juri))
        @method('PUT')
    @endif

    <input type="hidden" name="ujian_id" value="{{ $ujian->id }}">

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div
                class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i class="bi {{ isset($juri) ? 'bi-pencil-square' : 'bi-person-badge-fill' }} text-base"></i>
            </div>
            <div>
                <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ isset($juri) ? 'Edit Penugasan Dewan Juri' : 'Tetapkan Dewan Juri Baru' }}
                </h3>
                <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ isset($juri) ? 'Perbarui peran atau kategori bidang ustadz penguji.' : 'Pilih ustadz penguji untuk penilaian Khotho\' Jali dan Khotho\' Khofi.' }}
                </p>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none cursor-pointer">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">

        <!-- 1. Pilih Ustadz Penguji -->
        <div class="space-y-1.5">
            <label
                class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Ustadz / Dewan Guru Penguji <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
                <select name="ustadz_id" id="ustadz_id" required
                    class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs md:text-sm font-semibold h-10">
                    <option value="">-- Pilih Ustadz Aktif --</option>
                    @if (isset($juri) && $juri->ustadz)
                        <option value="{{ $juri->ustadz_id }}" selected>
                            {{ $juri->ustadz->nama_lengkap }} (NIGM: {{ $juri->ustadz->nigm ?? '-' }})
                        </option>
                    @endif
                    @foreach ($availableUstadz as $u)
                        @if (!isset($juri) || (string) $juri->ustadz_id !== (string) $u->id)
                            <option value="{{ $u->id }}">
                                {{ $u->nama_lengkap }} (NIGM: {{ $u->nigm ?? '-' }})
                            </option>
                        @endif
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- 2. Kategori Bidang & Peran Juri (2 Kolom) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Kategori Bidang Juri (Hanya 2) -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Kategori Bidang Juri <span class="text-rose-500">*</span>
                </label>
                <div class="relative group/select">
                    <select name="kategori_juri" id="kategori_juri" required
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs md:text-sm font-semibold h-10">
                        <option value="Khotho Jali"
                            {{ (isset($juri) && $juri->kategori_juri === 'Khotho Jali') || !isset($juri) ? 'selected' : '' }}>
                            Khotho' Jali (Tajwid Berat)
                        </option>
                        <option value="Khotho Khofi"
                            {{ isset($juri) && $juri->kategori_juri === 'Khotho Khofi' ? 'selected' : '' }}>
                            Khotho' Khofi (Tajwid Samar)
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
                <p class="text-[10px] text-zinc-400 ml-1">
                    * Pengurangan poin: Jali (-{{ (int) $ujian->bobot_jali }}), Khofi
                    (-{{ (int) $ujian->bobot_khofi }})
                </p>
            </div>

            <!-- Peran Juri (Hanya 2: Juri 1 & Juri 2) -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Posisi / Peran Juri <span class="text-rose-500">*</span>
                </label>
                <div class="relative group/select">
                    <select name="peran_juri" id="peran_juri" required
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs md:text-sm font-semibold h-10">
                        <option value="Juri 1"
                            {{ (isset($juri) && $juri->peran_juri === 'Juri 1') || !isset($juri) ? 'selected' : '' }}>
                            Juri 1
                        </option>
                        <option value="Juri 2" {{ isset($juri) && $juri->peran_juri === 'Juri 2' ? 'selected' : '' }}>
                            Juri 2
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
                <p class="text-[10px] text-zinc-400 ml-1">
                    * Urutan tanda tangan dokumen ijazah & SK
                </p>
            </div>
        </div>

        <!-- 3. Opsi Penanggung Jawab Ujian -->
        <div
            class="p-3.5 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 flex items-start gap-3">
            <div class="pt-0.5">
                <input type="checkbox" name="is_penanggung_jawab" id="is_penanggung_jawab" value="1"
                    {{ (isset($juri) && $juri->is_penanggung_jawab) || (!isset($juri) && !$ujian->juris()->where('is_penanggung_jawab', true)->exists()) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-zinc-300 dark:border-zinc-700 cursor-pointer">
            </div>
            <label for="is_penanggung_jawab" class="cursor-pointer select-none">
                <span class="block text-xs font-black text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                    <i class="bi bi-patch-check-fill text-amber-500"></i>
                    Tetapkan sebagai Penanggung Jawab Ujian (Tanda Tangan SK & Ijazah)
                </span>
                <span class="block text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                    Nama dan tanda tangan digital (QR Code) ustadz ini akan tercetak resmi sebagai Dewan Penguji pada
                    Surat Keputusan (SK) dan Ijazah Al-Qur'an.
                </span>
            </label>
        </div>

        <!-- 4. Catatan / Keterangan (Opsional) -->
        <div class="space-y-1.5">
            <label
                class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Catatan / Keterangan (Opsional)
            </label>
            <input type="text" name="keterangan" value="{{ $juri->keterangan ?? old('keterangan') }}"
                placeholder="Contoh: Penguji Ruangan 5-A & 5-B"
                class="m3-input-glass w-full text-xs md:text-sm font-semibold h-10">
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="px-4 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all active:scale-95 cursor-pointer">
            Batal
        </button>
        <button type="submit"
            class="m3-btn-primary px-5 py-2 rounded-2xl text-xs font-black shadow-2xs flex items-center gap-1.5 cursor-pointer">
            <i class="bi bi-check-lg text-sm"></i>
            <span>{{ isset($juri) ? 'Simpan Perubahan' : 'Tetapkan Juri' }}</span>
        </button>
    </div>
</form>
