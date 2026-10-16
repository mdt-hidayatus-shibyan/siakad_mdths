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
                Reg: #{{ $wali->no_registrasi }} • Dusun: {{ $wali->kampung?->nama_kampung ?? '-' }}
            </p>
        </div>
    </div>
    <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
        class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-400 hover:text-rose-500">
        <i class="bi bi-x-lg text-xs font-black"></i>
    </button>
</div>

<div class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1 bg-white dark:bg-[#0c0c0e]">
    <!-- Info Ringkas KK -->
    <div class="grid grid-cols-2 gap-2.5 text-xs">
        <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800">
            <p class="text-[9px] font-black uppercase text-zinc-400 mb-0.5">Zonasi Dusun</p>
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
            <i class="bi bi-people text-primary"></i> <span>Santri Terdaftar ({{ $wali->murids->count() }} Orang)</span>
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
                <p class="text-xs text-zinc-400 italic">Tidak ada data santri aktif terdaftar.</p>
            @endforelse
        </div>
    </div>

    <!-- Histori Tagihan & Pembayaran Ikhtibar -->
    <div>
        <h4
            class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
            <i class="bi bi-receipt text-primary"></i> <span>Status Tagihan Iuran Ikhtibar</span>
        </h4>
        <div class="space-y-3">
            @forelse ($wali->tagihanWaliMurids as $t)
                @php
                    $isLunas = $t->status_bayar === 'Lunas';
                    $isCicilan = $t->status_bayar === 'Cicilan';
                    $riwayat = $t->pembayaranIkhtibars->sortBy('termin_ke');
                @endphp
                <div class="p-4 rounded-2xl bg-zinc-50/70 dark:bg-zinc-900/40 border border-zinc-200/80 dark:border-zinc-800 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h5 class="text-xs font-black text-zinc-900 dark:text-white">{{ $t->nama_tagihan_spesifik }}</h5>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                Total Tagihan: <strong class="text-zinc-900 dark:text-white">Rp {{ number_format($t->nominal_tagihan, 0, ',', '.') }}</strong>
                                • Terbayar: <strong class="text-emerald-600">Rp {{ number_format($t->total_terbayar, 0, ',', '.') }}</strong>
                                • Sisa: <strong class="{{ $t->sisa_tagihan > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}</strong>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            @if ($isLunas)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <i class="bi bi-check-circle-fill mr-0.5"></i> LUNAS
                                </span>
                            @elseif ($isCicilan)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    <i class="bi bi-hourglass-split mr-0.5"></i> CICILAN ({{ $t->jumlah_cicilan }}/3)
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                    <i class="bi bi-x-circle-fill mr-0.5"></i> BELUM LUNAS
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Riwayat Transaksi Termin / Pembayaran Ikhtibar -->
                    @if ($riwayat->isNotEmpty())
                        <div class="pt-2 border-t border-zinc-200/80 dark:border-zinc-800">
                            <p class="text-[9px] font-black uppercase tracking-wider text-zinc-400 mb-2">Riwayat Pembayaran</p>
                            <div class="space-y-1.5">
                                @foreach ($riwayat as $p)
                                    <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-800/80 border border-zinc-200/70 dark:border-zinc-700/60 flex items-center justify-between text-xs gap-2">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="px-1.5 py-0.2 rounded text-[8.5px] font-black {{ $p->tipe_pembayaran === 'Lunas' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700' : 'bg-amber-100 dark:bg-amber-950 text-amber-700' }}">
                                                    Termin {{ $p->termin_ke }}
                                                </span>
                                                <span class="font-bold text-zinc-800 dark:text-zinc-200 text-[11px]">
                                                    Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}
                                                </span>
                                                <span class="text-[9.5px] text-zinc-400">• {{ date('d/m/Y', strtotime($p->tanggal_bayar)) }}</span>
                                                <span class="text-[9px] px-1.5 py-0.2 rounded font-semibold {{ in_array($p->metode_pembayaran, ['Potong Tabungan', 'Tabungan Murid']) ? 'bg-blue-50 dark:bg-blue-950 text-blue-600' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600' }}">
                                                    {{ $p->metode_pembayaran }}
                                                </span>
                                            </div>

                                            @if ($p->nomor_tabungan)
                                                <p class="text-[9px] text-zinc-500 dark:text-zinc-400 font-mono mt-0.5 truncate">
                                                    Tab: {{ $p->nomor_tabungan }} • a.n {{ $p->nama_nasabah }} ({{ $p->ruangan_nasabah }})
                                                    @if ($p->is_verified)
                                                        <span class="text-emerald-600 font-bold ml-1"><i class="bi bi-patch-check-fill"></i> Terverifikasi</span>
                                                    @else
                                                        <span class="text-amber-600 font-bold ml-1"><i class="bi bi-clock"></i> Pending Konfirmasi</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            @if (in_array($p->metode_pembayaran, ['Potong Tabungan', 'Potong Tabungan Manual', 'Tabungan Murid']))
                                                <button type="button"
                                                    onclick="toggleVerifikasiTabungan(this, '{{ route('tagihan-ikhtibar.toggle-verifikasi-tabungan', $p->id) }}')"
                                                    class="px-2 py-1 rounded-lg text-[9.5px] font-bold transition-all shadow-xs flex items-center gap-1 cursor-pointer {{ $p->is_verified ? 'bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800' : 'bg-amber-100 hover:bg-amber-200 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-800 animate-pulse' }}"
                                                    title="Klik untuk ubah verifikasi potongan tabungan">
                                                    <i class="bi {{ $p->is_verified ? 'bi-patch-check-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                                                    <span>{{ $p->is_verified ? 'Terkonfirmasi' : 'Konfirmasi' }}</span>
                                                </button>
                                            @endif
                                            <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi-cicilan', $p->id) }}" target="_blank"
                                                class="px-2 py-1 rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-200 text-[10px] font-bold flex items-center gap-1"
                                                title="Cetak Kwitansi Termin Ini">
                                                <i class="bi bi-printer"></i> <span>Kwitansi</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($isLunas && $t->pembayaranTagihan)
                        <!-- Fallback jika belum masuk ke pembayaran_tagihan_ikhtibars -->
                        <div class="pt-2 border-t border-zinc-200/80 dark:border-zinc-800 text-[11px] text-zinc-600 dark:text-zinc-400 flex items-center justify-between">
                            <span>Kwitansi: <strong class="font-mono">{{ $t->pembayaranTagihan->no_transaksi }}</strong> ({{ $t->pembayaranTagihan->metode_pembayaran }})</span>
                            <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $t->id) }}" target="_blank"
                                class="px-2 py-1 rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-[10px] font-bold flex items-center gap-1">
                                <i class="bi bi-printer"></i> <span>Kwitansi</span>
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-zinc-400 italic">Belum ada tagihan Ikhtibar terbit untuk keluarga ini.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-zinc-50 dark:bg-zinc-900/80 px-6 py-3.5 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
    <a href="{{ route('tagihan-ikhtibar.cetak-kartu', ['wali_id' => $wali->id]) }}" target="_blank"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors">
        <i class="bi bi-printer-fill"></i>
        <span>Cetak Kartu Tagihan KK Ini</span>
    </a>
    <button type="button" data-dismiss="modal" command="close" commandfor="dialog" class="m3-btn-secondary text-xs">
        Tutup
    </button>
</div>
