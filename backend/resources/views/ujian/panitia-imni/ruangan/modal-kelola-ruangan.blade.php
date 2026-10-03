<div class="relative z-10 flex flex-col max-h-[90vh]"
    x-data="kelolaRuanganImniModal({
        totalIbt: {{ $pesertaIbtCount }},
        totalTsa: {{ $pesertaTsaCount }},
        ruanganCount: {{ $daftarRuanganImni->count() }},
        csrf: '{{ csrf_token() }}',
        tahunId: {{ $selectedTahun->id }}
    })">

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/20">
                <i class="bi bi-door-open-fill"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">
                    Master Data Ruangan IMNI
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-0.5">
                    Kelola Master Ruangan Ujian (R1, R2, dst)
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
        
        <!-- 1. FORMULA & KALKULATOR KEBUTUHAN RUANGAN -->
        <div class="p-4 rounded-2xl bg-indigo-500/5 border border-indigo-500/20">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                    <i class="bi bi-calculator"></i>
                    Rumus Kebutuhan Ruangan IMNI (Gabungan 6 IBT & 3 TSA)
                </span>
                <span class="text-xs font-mono font-black text-indigo-600">
                    Total: {{ $totalGabungan }} Murid ({{ $pesertaIbtCount }} IBT + {{ $pesertaTsaCount }} TSA)
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center bg-white dark:bg-zinc-900/80 p-3 rounded-xl border border-zinc-200/60 dark:border-zinc-800">
                <div>
                    <label class="text-[10px] font-bold text-zinc-400 block mb-1">Standar Kapasitas / Ruang</label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model.number="targetKapasitas" min="5" max="50"
                            class="w-20 px-2.5 py-1.5 text-xs font-mono font-bold bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <span class="text-xs font-bold text-zinc-500">Murid / Ruang</span>
                    </div>
                </div>

                <div class="text-center md:border-x border-zinc-100 dark:border-zinc-800 px-2 py-1">
                    <span class="text-[10px] font-bold text-zinc-400 block">Rumus Kebutuhan:</span>
                    <span class="text-xs font-mono font-black text-zinc-700 dark:text-zinc-200">
                        ⌈ <span x-text="totalPeserta"></span> / <span x-text="targetKapasitas"></span> ⌉ =
                        <span class="text-indigo-600 dark:text-indigo-400 text-sm font-black" x-text="ruanganDibutuhkan"></span> Ruangan
                    </span>
                </div>

                <div class="text-right">
                    <span class="text-[10px] font-bold text-zinc-400 block">Estimasi Proporsi per Ruang:</span>
                    <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                        ~<span x-text="ibtPerRuang"></span> IBT + ~<span x-text="tsaPerRuang"></span> TSA / Ruangan
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. TOOLBAR TAMBAH RUANGAN & GENERATOR CEPAT -->
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <h4 class="text-xs font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                Daftar Ruangan Ujian IMNI ({{ $daftarRuanganImni->count() }} Ruangan)
            </h4>

            <div class="flex items-center gap-2">
                <a href="{{ route('ruangan-imni.modal-tambah-ruangan', ['tahun_id' => $selectedTahun->id]) }}"
                    class="action-modal px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                    <i class="bi bi-plus-lg"></i>
                    <span>+ Tambah Ruangan Manual</span>
                </a>

                @if($daftarRuanganImni->isNotEmpty())
                    <button type="button" @click="confirmResetRuangan()"
                        class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 text-xs font-bold flex items-center gap-1 transition-all cursor-pointer">
                        <i class="bi bi-trash3"></i>
                        <span>Reset Master Ruangan</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 3. TABEL DAFTAR RUANGAN IMNI -->
        <div class="border border-zinc-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-2xs">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">Urutan</th>
                        <th class="py-3 px-3">Nama Ruangan (IMNI)</th>
                        <th class="py-3 px-3">Relasi Ruangan Fisik</th>
                        <th class="py-3 px-3 text-center">Kapasitas</th>
                        <th class="py-3 px-3 text-center">Murid Ter-plot</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                    @forelse ($daftarRuanganImni as $rg)
                        @php
                            $terplotCount = $rg->pesertaRuangans->whereNull('tanggal_ujian')->count();
                        @endphp
                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors">
                            <td class="py-3 px-3 text-center font-mono font-bold text-zinc-400">
                                {{ $rg->urutan }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-black text-xs text-indigo-600 dark:text-indigo-400 font-mono">
                                    {{ $rg->nama_ruangan }}
                                </span>
                                @if($rg->kode_ruangan && $rg->kode_ruangan !== $rg->nama_ruangan)
                                    <span class="text-[10px] text-zinc-400 font-normal">({{ $rg->kode_ruangan }})</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($rg->ruanganFisik)
                                    <span class="font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ $rg->ruanganFisik->nama_ruangan }}
                                    </span>
                                    <span class="text-[10px] text-zinc-400 block">
                                        {{ $rg->ruanganFisik->level?->nama_level ?? '-' }} ({{ $rg->ruanganFisik->level?->tingkat?->kode_tingkat ?? 'MDT' }})
                                    </span>
                                @else
                                    <span class="text-zinc-400 italic text-[11px]">- Belum Ditautkan -</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                {{ $rg->kapasitas }} Kursi
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black font-mono {{ $terplotCount > 0 ? 'bg-indigo-500/10 text-indigo-600' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' }}">
                                    {{ $terplotCount }} Murid
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($rg->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-400">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('ruangan-imni.modal-edit-ruangan', $rg->id) }}"
                                        class="action-modal p-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-600 dark:text-zinc-300 text-xs transition-all cursor-pointer"
                                        title="Edit Ruangan">
                                        <i class="bi bi-pencil-fill text-[11px]"></i>
                                    </a>
                                    <button type="button" @click="deleteRuangan({{ $rg->id }}, '{{ $rg->nama_ruangan }}')"
                                        class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 text-xs transition-all cursor-pointer"
                                        title="Hapus Ruangan">
                                        <i class="bi bi-trash3-fill text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-zinc-400">
                                <i class="bi bi-door-closed text-2xl block mb-1 opacity-50"></i>
                                Belum ada ruangan ujian IMNI yang dibuat. Silakan klik tombol <strong>"+ Tambah Ruangan Manual"</strong> atau gunakan generator otomatis.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Tutup
        </button>
    </div>
</div>

<script>
    function kelolaRuanganImniModal(config) {
        return {
            totalIbt: config.totalIbt || 0,
            totalTsa: config.totalTsa || 0,
            targetKapasitas: 20,
            tahunId: config.tahunId,
            csrf: config.csrf,

            get totalPeserta() {
                return this.totalIbt + this.totalTsa;
            },

            get ruanganDibutuhkan() {
                if (this.targetKapasitas <= 0 || this.totalPeserta <= 0) return 1;
                return Math.ceil(this.totalPeserta / this.targetKapasitas);
            },

            get ibtPerRuang() {
                if (this.ruanganDibutuhkan <= 0) return 0;
                return Math.round(this.totalIbt / this.ruanganDibutuhkan);
            },

            get tsaPerRuang() {
                if (this.ruanganDibutuhkan <= 0) return 0;
                return Math.round(this.totalTsa / this.ruanganDibutuhkan);
            },

            deleteRuangan(id, nama) {
                if (!confirm(`Apakah Anda yakin ingin menghapus ruangan "${nama}"? Plotting murid di ruangan ini akan direset.`)) return;

                fetch(`{{ url('ruangan-imni') }}/${id}/destroy-ruangan`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (typeof showToast === 'function') showToast(res.message, 'success');
                        window.location.reload();
                    } else {
                        alert(res.message || 'Gagal menghapus ruangan');
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                });
            },

            confirmResetRuangan() {
                if (!confirm('PERINGATAN: Seluruh master ruangan IMNI dan plotting peserta tahun ini akan dikosongkan. Lanjutkan?')) return;

                fetch(`{{ route('ruangan-imni.reset-ruangan') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        tahun_pelajaran_id: this.tahunId
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (typeof showToast === 'function') showToast(res.message, 'success');
                        window.location.reload();
                    } else {
                        alert(res.message || 'Gagal reset ruangan');
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                });
            }
        };
    }
</script>
