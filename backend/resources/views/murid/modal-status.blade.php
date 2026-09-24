@php
    $defaultFoto = $murid->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png';
    $fotoPath = $murid->foto ? $murid->foto : $defaultFoto;
@endphp

<!-- Modal Form Update Status Santri (AJAX M3 Glass) -->
<form action="{{ route('murid.updateStatus', $murid->id) }}" method="POST" class="ajax-form relative z-10 flex flex-col"
    data-refresh-target="#data-table-container">
    @csrf
    @method('PATCH')

    <!-- Header Modal -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-b border-zinc-200/80 dark:border-zinc-800 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-3 min-w-0">
            <div
                class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary dark:text-primary-dark flex items-center justify-center text-lg shrink-0 shadow-2xs">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight truncate">
                    Ubah Status Murid
                </h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 truncate">
                    Perbarui status keaktifan Murid di madrasah
                </p>
            </div>
        </div>

        <!-- Tombol Tutup -->
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xl bg-zinc-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-zinc-800 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 text-zinc-400 transition-colors duration-200 outline-none active:scale-95 shadow-2xs">
            <i class="bi bi-x-lg text-xs font-black"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 space-y-4 overflow-y-auto custom-scrollbar max-h-[72vh]">
        <!-- Info Santri (Card Highlight) -->
        <div
            class="p-4 rounded-2xl bg-zinc-50/80 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-13 h-13 rounded-xl overflow-hidden shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                <img src="{{ asset('storage/' . $fotoPath) }}" alt="{{ $murid->nama_lengkap }}"
                    class="w-48 h-48 object-cover rounded-lg">
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-0.5">
                    <span
                        class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $murid->jenis_kelamin == 'L' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20' }}">
                        {{ $murid->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                    <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 font-mono">
                        NISM: {{ $murid->nism ?? '-' }}
                    </span>
                </div>
                <h4 class="text-sm font-black text-zinc-900 dark:text-white truncate">
                    {{ $murid->nama_lengkap }}
                </h4>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Status Saat Ini:</span>
                    <span
                        class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider
                        {{ $murid->status == 'Aktif' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30' : '' }}
                        {{ $murid->status == 'Lulus' ? 'bg-blue-500/15 text-blue-700 dark:text-blue-400 border border-blue-500/30' : '' }}
                        {{ $murid->status == 'Pindah' ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30' : '' }}
                        {{ $murid->status == 'Berhenti' ? 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30' : '' }}
                        {{ $murid->status == 'Meninggal' ? 'bg-zinc-700 text-white border border-zinc-800' : '' }}
                    ">
                        {{ $murid->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Pilihan Status Baru -->
        <div class="space-y-2">
            <label class="block text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider ml-1">
                Pilih Status Baru <span class="text-rose-500">*</span>
            </label>

            <div class="space-y-2">
                @php
                    $statusOptions = [
                        [
                            'val' => 'Aktif',
                            'label' => 'Aktif',
                            'desc' => 'Santri aktif mengikuti KBM dan administrasi madrasah.',
                            'icon' => 'bi-check-circle-fill',
                            'color' => 'emerald',
                        ],
                        [
                            'val' => 'Lulus',
                            'label' => 'Lulus',
                            'desc' => 'Santri telah menyelesaikan seluruh jenjang pendidikan.',
                            'icon' => 'bi-mortarboard-fill',
                            'color' => 'blue',
                        ],
                        [
                            'val' => 'Pindah',
                            'label' => 'Pindah (Mutasi)',
                            'desc' => 'Santri pindah / mutasi ke madrasah atau sekolah lain.',
                            'icon' => 'bi-arrow-left-right',
                            'color' => 'amber',
                        ],
                        [
                            'val' => 'Berhenti',
                            'label' => 'Berhenti / Keluar',
                            'desc' => 'Santri mengundurkan diri / berhenti sebelum tamat.',
                            'icon' => 'bi-x-circle-fill',
                            'color' => 'rose',
                        ],
                        [
                            'val' => 'Meninggal',
                            'label' => 'Meninggal Dunia',
                            'desc' => 'Santri telah berpulang ke Rahmatullah.',
                            'icon' => 'bi-heartbreak-fill',
                            'color' => 'zinc',
                        ],
                    ];
                @endphp

                @foreach ($statusOptions as $opt)
                    <label
                        class="flex items-start gap-3 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/60 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 transition-all cursor-pointer shadow-2xs group">
                        <input type="radio" name="status" value="{{ $opt['val'] }}"
                            {{ $murid->status == $opt['val'] ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 text-primary focus:ring-primary/20 cursor-pointer">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <i
                                    class="bi {{ $opt['icon'] }} text-xs
                                    {{ $opt['color'] == 'emerald' ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                                    {{ $opt['color'] == 'blue' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                    {{ $opt['color'] == 'amber' ? 'text-amber-600 dark:text-amber-400' : '' }}
                                    {{ $opt['color'] == 'rose' ? 'text-rose-600 dark:text-rose-400' : '' }}
                                    {{ $opt['color'] == 'zinc' ? 'text-zinc-600 dark:text-zinc-400' : '' }}
                                "></i>
                                <span class="text-xs font-black text-zinc-900 dark:text-white">
                                    {{ $opt['label'] }}
                                </span>
                            </div>
                            <p class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                                {{ $opt['desc'] }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Catatan Info Sistem -->
        <div
            class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-xs flex items-start gap-2.5">
            <i class="bi bi-info-circle-fill text-sm shrink-0 mt-0.5 text-amber-600 dark:text-amber-400"></i>
            <div class="space-y-0.5 leading-relaxed">
                <p class="font-black text-[11px] uppercase tracking-wider">Pemberitahuan Sistem</p>
                <p class="text-[11px] font-medium text-amber-800/90 dark:text-amber-200/80">
                    Bila status diubah dari <strong>Aktif</strong> dan wali santri tidak memiliki anak aktif lainnya,
                    status akun wali santri akan dinonaktifkan secara otomatis.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-t border-zinc-200/80 dark:border-zinc-800 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="px-4 py-2 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
            Batal
        </button>
        <button type="submit"
            class="m3-btn-primary min-h-[38px] px-6 text-xs font-black shadow-md flex items-center gap-2">
            <i class="bi bi-check2-circle text-sm"></i>
            <span>Simpan Perubahan</span>
        </button>
    </div>
</form>
