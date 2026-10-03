<form action="{{ route('peserta-imni.auto-plotting') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container"
    x-data="autoPlottingModal({
        totalIbt: {{ $pesertaIbtCount }},
        totalTsa: {{ $pesertaTsaCount }},
        recommendedRoomIds: @json($daftarRuangan->filter(fn($r) => in_array($r->level?->urutan_level, [9, 12]) || str_contains($r->nama_ruangan, '6') || str_contains($r->nama_ruangan, 'TSA'))->pluck('id'))
    })">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahun->id }}">

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/20">
                <i class="bi bi-shuffle"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">
                    Algoritma Plotting IMNI
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-0.5">
                    Distribusi / Auto-Plotting Ruangan
                </h3>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">
        
        <!-- 1. RINGKASAN PESERTA IMNI -->
        <div class="grid grid-cols-3 gap-2.5">
            <!-- 3 TPQ -->
            <div class="p-3 rounded-2xl bg-emerald-500/5 border border-emerald-500/20">
                <span class="text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">
                    3 TPQ (IMDA 2)
                </span>
                <h4 class="text-lg font-black text-emerald-700 dark:text-emerald-300 font-mono mt-0.5">
                    {{ $pesertaTpqCount }} <span class="text-[10px] font-bold text-zinc-400">Murid</span>
                </h4>
                <p class="text-[10px] font-semibold text-emerald-600/80 dark:text-emerald-400/80 mt-1">
                    Plot mandiri ke kelas asal
                </p>
            </div>

            <!-- 6 IBT -->
            <div class="p-3 rounded-2xl bg-orange-500/5 border border-orange-500/20">
                <span class="text-[9px] font-black uppercase tracking-wider text-orange-600 dark:text-orange-400 block">
                    6 IBT
                </span>
                <h4 class="text-lg font-black text-orange-700 dark:text-orange-300 font-mono mt-0.5">
                    {{ $pesertaIbtCount }} <span class="text-[10px] font-bold text-zinc-400">Murid</span>
                </h4>
                <p class="text-[10px] font-semibold text-orange-600/80 dark:text-orange-400/80 mt-1">
                    Peserta gabungan
                </p>
            </div>

            <!-- 3 TSA -->
            <div class="p-3 rounded-2xl bg-blue-500/5 border border-blue-500/20">
                <span class="text-[9px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 block">
                    3 TSA
                </span>
                <h4 class="text-lg font-black text-blue-700 dark:text-blue-300 font-mono mt-0.5">
                    {{ $pesertaTsaCount }} <span class="text-[10px] font-bold text-zinc-400">Murid</span>
                </h4>
                <p class="text-[10px] font-semibold text-blue-600/80 dark:text-blue-400/80 mt-1">
                    Peserta gabungan
                </p>
            </div>
        </div>

        <!-- 2. PEMILIHAN RUANGAN UJIAN TARGET (6 IBT & 3 TSA) -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-[11px] font-extrabold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                        Pilih Ruangan Ujian Target (6 IBT & 3 TSA) <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-[11px] font-semibold text-zinc-400">
                        Pilih ruangan mana saja yang disiapkan untuk diisi campuran peserta 6 IBT & 3 TSA.
                    </p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" @click="selectRecommended()"
                        class="px-2 py-1 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-[10px] font-black transition-all">
                        Ruangan Kelas Akhir
                    </button>
                    <button type="button" @click="clearAll()"
                        class="px-2 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-500 text-[10px] font-bold transition-all">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Grid Checkbox Ruangan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-1 custom-scrollbar border border-zinc-200/60 dark:border-zinc-800 rounded-2xl">
                @forelse ($daftarRuangan as $rg)
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border transition-all cursor-pointer select-none"
                        :class="selectedRooms.includes({{ $rg->id }}) 
                            ? 'bg-indigo-500/10 border-indigo-500/40 text-indigo-900 dark:text-indigo-100 shadow-xs' 
                            : 'bg-zinc-50/50 dark:bg-zinc-900/50 border-zinc-200/50 dark:border-zinc-800 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60 text-zinc-700 dark:text-zinc-300'">
                        <input type="checkbox" name="target_ruangan_ids[]" value="{{ $rg->id }}"
                            x-model="selectedRooms" :value="{{ $rg->id }}"
                            class="w-4 h-4 rounded text-indigo-600 border-zinc-300 dark:border-zinc-700 focus:ring-indigo-500/20 cursor-pointer">
                        <div class="min-w-0 flex-1">
                            <div class="font-black text-xs truncate flex items-center justify-between">
                                <span>{{ $rg->nama_ruangan }}</span>
                                <span class="text-[10px] font-mono text-zinc-400">Kap: {{ $rg->kapasitas }}</span>
                            </div>
                            <p class="text-[10px] text-zinc-400 truncate">
                                {{ $rg->level?->nama_level ?? 'Umum' }} ({{ $rg->level?->tingkat?->kode_tingkat ?? 'MDT' }})
                            </p>
                        </div>
                    </label>
                @empty
                    <div class="col-span-2 py-4 text-center text-xs text-zinc-400 font-semibold">
                        Belum ada ruangan yang dikonfigurasi untuk tahun pelajaran ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. LIVE PREVIEW KALKULASI PROPORSI RUANGAN -->
        <div class="p-4 rounded-2xl transition-all"
            :class="selectedRooms.length > 0 ? 'bg-indigo-500/10 border border-indigo-500/20' : 'bg-zinc-100 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60'">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-base shrink-0"
                    :class="selectedRooms.length > 0 ? 'bg-indigo-600 text-white' : 'bg-zinc-300 dark:bg-zinc-700 text-zinc-500'">
                    <i class="bi bi-calculator"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <template x-if="selectedRooms.length > 0">
                        <div>
                            <h5 class="font-black text-xs text-indigo-950 dark:text-indigo-200">
                                Simulasi Alokasi (<span x-text="selectedRooms.length"></span> Ruangan Terpilih):
                            </h5>
                            <p class="text-xs text-indigo-900 dark:text-indigo-300 mt-1 font-semibold leading-relaxed">
                                Setiap ruangan akan diisi rata-rata: 
                                <span class="font-black underline" x-text="ibtPerRoom"></span> Murid 6 IBT + 
                                <span class="font-black underline" x-text="tsaPerRoom"></span> Murid 3 TSA = 
                                <span class="font-black bg-indigo-600 text-white px-1.5 py-0.5 rounded text-[11px]" x-text="totalPerRoom"></span> Murid / Ruangan.
                            </p>
                        </div>
                    </template>
                    <template x-if="selectedRooms.length === 0">
                        <div>
                            <h5 class="font-bold text-xs text-zinc-700 dark:text-zinc-300">
                                Belum Ada Ruangan Dipilih
                            </h5>
                            <p class="text-[11px] text-zinc-400 mt-0.5">
                                Silakan centang minimal 1 ruangan di atas untuk menghitung kalkulasi distribusi otomatis.
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 4. METODE DISTRIBUSI & OPSI -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Metode Distribusi -->
            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                    Metode Alokasi
                </label>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-2 p-2 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-xs font-bold cursor-pointer">
                        <input type="radio" name="metode_distribusi" value="round_robin" checked
                            class="text-indigo-600 focus:ring-indigo-500/20">
                        <div>
                            <span>Seling / Campur Merata (Round-Robin)</span>
                            <span class="block text-[10px] text-zinc-400 font-normal">Diseling antar ruangan secara bergantian</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-xs font-bold cursor-pointer">
                        <input type="radio" name="metode_distribusi" value="chunk"
                            class="text-indigo-600 focus:ring-indigo-500/20">
                        <div>
                            <span>Blok Kelas (Urut Kelas Asal)</span>
                            <span class="block text-[10px] text-zinc-400 font-normal">Dibagi per blok jumlah yang sama</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Opsi Tambahan -->
            <div class="space-y-2">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                    Opsi Tambahan
                </label>
                <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 space-y-2.5">
                    <!-- Checkbox TPQ Mandiri -->
                    <label class="flex items-start gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        <input type="checkbox" name="plot_tpq_mandiri" value="1" checked
                            class="w-4 h-4 rounded text-emerald-600 border-zinc-300 dark:border-zinc-700 focus:ring-emerald-500/20 mt-0.5">
                        <span class="text-[11px] leading-tight">
                            Plot murid <span class="text-emerald-600 font-black">3 TPQ</span> ke ruang kelas asalnya (Skema IMDA 2)
                        </span>
                    </label>

                    <!-- Checkbox Reset Nomor Meja -->
                    <label class="flex items-start gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        <input type="checkbox" name="reset_nomor_meja" value="1" checked
                            class="w-4 h-4 rounded text-indigo-600 border-zinc-300 dark:border-zinc-700 focus:ring-indigo-500/20 mt-0.5">
                        <span class="text-[11px] leading-tight">
                            Set nomor urut presensi/meja per ruangan (1..N)
                        </span>
                    </label>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" :disabled="selectedRooms.length === 0"
            class="m3-btn-primary text-xs flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="bi bi-shuffle"></i>
            <span>Eksekusi Distribusi Ruangan</span>
        </button>
    </div>
</form>

<script>
    function autoPlottingModal(config) {
        return {
            totalIbt: config.totalIbt || 0,
            totalTsa: config.totalTsa || 0,
            recommendedRoomIds: config.recommendedRoomIds || [],
            selectedRooms: [],

            init() {
                // Default pilih ruangan rekomendasi jika ada
                if (this.recommendedRoomIds.length > 0) {
                    this.selectedRooms = [...this.recommendedRoomIds];
                }
            },

            selectRecommended() {
                this.selectedRooms = [...this.recommendedRoomIds];
            },

            clearAll() {
                this.selectedRooms = [];
            },

            get ibtPerRoom() {
                if (this.selectedRooms.length === 0) return 0;
                return Math.round(this.totalIbt / this.selectedRooms.length);
            },

            get tsaPerRoom() {
                if (this.selectedRooms.length === 0) return 0;
                return Math.round(this.totalTsa / this.selectedRooms.length);
            },

            get totalPerRoom() {
                return this.ibtPerRoom + this.tsaPerRoom;
            }
        };
    }
</script>
