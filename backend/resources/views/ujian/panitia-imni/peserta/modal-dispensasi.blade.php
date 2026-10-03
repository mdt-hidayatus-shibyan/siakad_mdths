<div class="relative z-10 flex flex-col max-h-[90vh]">
    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-500/20">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                    Dispensasi Ujian
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-0.5">
                    Dispensasi Administrasi IMNI
                </h3>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        <!-- Ringkasan Profil Murid & Keuangan -->
        <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/70 dark:border-zinc-800/70">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 flex items-center justify-center font-bold text-sm shrink-0 overflow-hidden border border-zinc-300 dark:border-zinc-700">
                    @if ($peserta->murid && $peserta->murid->foto_url)
                        <img src="{{ $peserta->murid->foto_url }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        {{ substr($peserta->murid?->nama_lengkap ?? 'M', 0, 1) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="font-black text-xs md:text-sm text-zinc-900 dark:text-white truncate">
                        {{ $peserta->murid?->nama_lengkap }}
                    </h4>
                    <p class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 mt-0.5">
                        NISM: {{ $peserta->murid?->nism ?? '-' }} • {{ $peserta->level?->nama_level ?? ($peserta->tingkat?->nama_tingkat ?? '-') }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black uppercase tracking-wider block text-zinc-400">Nomor Peserta</span>
                    <span class="text-xs font-mono font-black text-indigo-600 dark:text-indigo-400">
                        {{ $peserta->nomor_peserta ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-zinc-200/60 dark:border-zinc-800/60 text-center">
                <div class="p-2 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-[9px] font-black uppercase tracking-wider text-zinc-400 block">Total Tagihan</span>
                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                        Rp {{ number_format($peserta->pembayaran?->nominal_tagihan ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-2 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-[9px] font-black uppercase tracking-wider text-zinc-400 block">Terbayar</span>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($peserta->pembayaran?->nominal_bayar ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-2 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-[9px] font-black uppercase tracking-wider text-zinc-400 block">Sisa Tagihan</span>
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400">
                        Rp {{ number_format($peserta->pembayaran?->sisa_tagihan ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        @if ($peserta->status_kelayakan === 'Dispensasi')
            <!-- Status Dispensasi Aktif -->
            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3">
                <i class="bi bi-info-circle-fill text-amber-600 dark:text-amber-400 text-base shrink-0 mt-0.5"></i>
                <div class="flex-1 text-xs">
                    <p class="font-bold text-amber-800 dark:text-amber-300">
                        Status: <span class="font-black underline">Memiliki Dispensasi Panitia</span>
                    </p>
                    @if ($peserta->catatan)
                        <p class="text-[11px] text-amber-700/90 dark:text-amber-400/90 mt-1 italic">
                            "{{ $peserta->catatan }}"
                        </p>
                    @endif
                </div>
            </div>
        @else
            <!-- Form Beri Dispensasi Baru -->
            <form id="formBeriDispensasi" action="{{ route('peserta-imni.beri-dispensasi', $peserta->id) }}" method="POST"
                class="ajax-form space-y-3" data-refresh-target="#data-table-container">
                @csrf
                <div>
                    <label class="text-[11px] font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-300 block mb-1">
                        Alasan / Catatan Kebijakan Dispensasi Panitia <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="alasan_dispensasi" rows="3" required
                        placeholder="Contoh: Izin dispensasi dari Ketua Panitia karena ada komitmen pelunasan dari wali murid sebelum pengambilan ijazah."
                        class="m3-input-glass w-full text-xs font-medium"></textarea>
                    <p class="text-[10px] text-zinc-400 mt-1">
                        Dengan memberikan dispensasi, kartu peserta ujian murid ini dapat dicetak dan murid diizinkan mengikuti IMNI.
                    </p>
                </div>
            </form>
        @endif
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-between gap-2.5 transition-colors duration-300">
        @if ($peserta->status_kelayakan === 'Dispensasi')
            <!-- Form Cabut Dispensasi -->
            <form action="{{ route('peserta-imni.cabut-dispensasi', $peserta->id) }}" method="POST"
                class="ajax-form m-0" data-refresh-target="#data-table-container">
                @csrf
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                    <i class="bi bi-lock-fill"></i>
                    <span>Cabut Dispensasi (Kunci Kembali)</span>
                </button>
            </form>

            <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
                class="m3-btn-secondary text-xs">
                Tutup
            </button>
        @else
            <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
                class="m3-btn-secondary text-xs">
                Batal
            </button>

            <button type="submit" form="formBeriDispensasi"
                class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                <i class="bi bi-unlock-fill"></i>
                <span>Beri Dispensasi Panitia</span>
            </button>
        @endif
    </div>
</div>
