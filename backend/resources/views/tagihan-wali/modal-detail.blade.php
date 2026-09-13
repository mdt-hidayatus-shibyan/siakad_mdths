<div
    class="px-6 py-4 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between shrink-0 bg-white dark:bg-[#0c0c0e]">
    <div class="flex items-center gap-2.5">
        <div
            class="w-8 h-8 rounded-xl bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 flex items-center justify-center text-sm">
            <i class="bi bi-houses-fill"></i>
        </div>
        <div>
            <h3 class="font-black text-sm text-zinc-900 dark:text-white leading-tight">
                {{ $wali->nama_kepala_keluarga }}
            </h3>
            <p class="text-[10px] text-zinc-400 font-mono mt-0.5">
                Reg: #{{ $wali->no_registrasi }} • KK: {{ $wali->no_kk ?: '-' }}
            </p>
        </div>
    </div>
    <button type="button" onclick="tutupModalDetail()"
        class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-400 hover:text-rose-500">
        <i class="bi bi-x-lg text-xs font-black"></i>
    </button>
</div>

<div class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1 bg-white dark:bg-[#0c0c0e]">
    <!-- Info Ringkas KK -->
    <div class="grid grid-cols-2 gap-2.5 text-xs">
        <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800">
            <p class="text-[9px] font-black uppercase text-zinc-400 mb-0.5">Zonasi Kampung</p>
            <p class="font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-1.5">
                <i class="bi bi-geo-alt text-zinc-400"></i> {{ $wali->kampung->nama_kampung ?? '-' }}
            </p>
        </div>
        <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800">
            <p class="text-[9px] font-black uppercase text-zinc-400 mb-0.5">No WhatsApp</p>
            <p class="font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-1.5 font-mono">
                <i class="bi bi-whatsapp text-emerald-500"></i> {{ $wali->no_hp ?: '-' }}
            </p>
        </div>
    </div>

    <!-- Tanggungan Murid Aktif -->
    <div>
        <h4
            class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
            <i class="bi bi-people text-primary"></i> <span>Tanggungan Murid Aktif
                ({{ $wali->murids->count() }})</span>
        </h4>
        <div class="space-y-2">
            @forelse ($wali->murids as $anak)
                <div
                    class="p-3 rounded-2xl bg-zinc-50/70 dark:bg-zinc-900/30 border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div
                            class="w-7 h-7 rounded-lg {{ $anak->jenis_kelamin === 'L' ? 'bg-blue-500/10 text-blue-500' : 'bg-pink-500/10 text-pink-500' }} flex items-center justify-center text-xs shrink-0">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="text-xs font-black text-zinc-800 dark:text-zinc-200 truncate">
                                {{ $anak->nama_lengkap }}</h5>
                            <p class="text-[9px] text-zinc-400 font-mono">NISM: {{ $anak->nism ?: '-' }}</p>
                        </div>
                    </div>
                    <span
                        class="px-2 py-0.5 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[9px] font-bold text-zinc-600 dark:text-zinc-400 shrink-0">
                        {{ $anak->ruangans->first()?->nama_ruangan ?? '-' }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-zinc-400 italic">Tidak ada data murid aktif terdaftar.</p>
            @endforelse
        </div>
    </div>

    <!-- Histori Tagihan Wali Murid -->
    <div>
        <h4
            class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
            <i class="bi bi-receipt text-primary"></i> <span>Histori Tagihan KK Tahun Ini</span>
        </h4>
        <div class="space-y-2">
            @forelse ($wali->tagihanWaliMurids as $t)
                <div
                    class="p-3.5 rounded-2xl bg-zinc-50/70 dark:bg-zinc-900/30 border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                    <div>
                        <h5 class="text-xs font-black text-zinc-900 dark:text-white">{{ $t->nama_tagihan_spesifik }}
                        </h5>
                        <p class="text-[10px] text-zinc-400 font-mono mt-0.5">
                            @if ($t->status_bayar === 'Lunas')
                                Kwitansi: {{ $t->pembayaranTagihan->no_transaksi ?? '-' }}
                                • Metode: {{ $t->pembayaranTagihan->metode_pembayaran ?? 'Tunai' }}
                                @if (!empty($t->pembayaranTagihan->rekening_penerima))
                                    (Rek: {{ $t->pembayaranTagihan->rekening_penerima }})
                                @endif
                                • Tgl:
                                {{ $t->pembayaranTagihan ? date('d/m/Y', strtotime($t->pembayaranTagihan->tanggal_bayar)) : '-' }}
                            @else
                                Belum Terbayar
                            @endif
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p
                            class="text-xs font-black {{ $t->status_bayar === 'Lunas' ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-900 dark:text-white' }}">
                            Rp {{ number_format($t->nominal_tagihan, 0, ',', '.') }}
                        </p>
                        <span
                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[8px] font-black uppercase {{ $t->status_bayar === 'Lunas' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }} mt-0.5">
                            {{ $t->status_bayar }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-zinc-400 italic">Belum ada tagihan yang diterbitkan untuk KK ini.</p>
            @endforelse
        </div>
    </div>
</div>

<div
    class="px-6 py-3.5 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200/80 dark:border-zinc-800 flex justify-between items-center shrink-0">
    <a href="{{ route('wali-murid.show', $wali->id) }}" target="_blank"
        class="text-xs font-bold text-primary dark:text-primary-dark hover:underline flex items-center gap-1">
        <i class="bi bi-box-arrow-up-right text-[10px]"></i> <span>Buka Profil KK Lengkap</span>
    </a>
    <button type="button" onclick="tutupModalDetail()"
        class="px-4 py-2 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs">
        Tutup
    </button>
</div>
