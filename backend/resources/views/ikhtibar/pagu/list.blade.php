<div class="space-y-6">
    <!-- TABEL 1: Monitoring Pagu & Sisa Kas Divisi -->
    <div class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                    <i class="bi bi-wallet2 text-primary"></i>
                    Alokasi Pagu & Saldo Kas per Divisi
                </h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Kelola pencairan anggaran bertahap serta penarikan sisa kas/pagu yang tidak terserap.
                </p>
            </div>
            <div class="text-xs font-bold text-zinc-500">
                Total Pagu: <span class="font-black text-primary dark:text-primary-light">Rp {{ number_format($divisis->sum('nominal_pagu'), 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3">Divisi & Koordinator</th>
                        <th class="py-3 px-3 text-right">Pagu Disetujui</th>
                        <th class="py-3 px-3 text-right">Tersalurkan</th>
                        <th class="py-3 px-3 text-right">SPJ Belanja</th>
                        <th class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">Sisa Kas Fisik</th>
                        <th class="py-3 px-3 text-right text-blue-600 dark:text-blue-400">Belum Cair</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center w-40">Aksi Dana</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                    @forelse ($divisis as $index => $divisi)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="py-3 px-3 text-center font-bold text-zinc-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-zinc-900 dark:text-white">
                                        {{ $divisi->nama_divisi }}
                                    </span>
                                    @if ($divisi->is_bph)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            BPH Inti
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-zinc-400">
                                    {{ $divisi->is_bph ? 'Penanggung Jawab: ' : 'Koordinator: ' }}
                                    <span class="text-zinc-600 dark:text-zinc-300 font-semibold">
                                        {{ $divisi->koordinator_tampil?->nama_tampil ?? 'Belum Ditentukan' }}
                                    </span>
                                    @if ($divisi->koordinator_tampil)
                                        <span class="text-zinc-400">({{ $divisi->koordinator_tampil->jabatan }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-right font-black text-zinc-900 dark:text-white">
                                Rp {{ number_format($divisi->nominal_pagu, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($divisi->total_tersalurkan, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($divisi->total_realisasi_spj, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($divisi->sisa_kas_di_divisi, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-zinc-500 dark:text-zinc-400">
                                Rp {{ number_format($divisi->sisa_pagu_belum_cair, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $divisi->status_pagu_badge_color }}">
                                    {{ $divisi->status_pagu }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Salurkan Dana -->
                                    <a href="{{ route('pagu-divisi-ikhtibar.modal-salur-dana', $divisi->id) }}"
                                        class="action-modal p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
                                        title="Salurkan Dana Pagu (Cairkan)" data-modal-size="lg">
                                        <i class="bi bi-box-arrow-up-right text-xs"></i>
                                    </a>

                                    <!-- Tombol Tarik Sisa Pagu / Kas -->
                                    <a href="{{ route('pagu-divisi-ikhtibar.modal-tarik-sisa', $divisi->id) }}"
                                        class="action-modal p-1.5 rounded-lg text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-950/40 transition-colors"
                                        title="Tarik Sisa Pagu / Sisa Kas Divisi" data-modal-size="lg">
                                        <i class="bi bi-arrow-down-left-circle-fill text-xs"></i>
                                    </a>

                                    <!-- Tombol Sesuaikan Pagu -->
                                    <a href="{{ route('pagu-divisi-ikhtibar.modal-update-pagu', $divisi->id) }}"
                                        class="action-modal p-1.5 rounded-lg text-zinc-500 hover:text-primary hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                        title="Sesuaikan Nominal Pagu" data-modal-size="md">
                                        <i class="bi bi-sliders text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-zinc-400 text-xs">
                                Belum ada divisi terdaftar. Masuk ke menu Master Ikhtibar untuk membuat divisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABEL 2: Riwayat Mutasi Penyaluran & Penarikan Dana -->
    <div class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    Buku Mutasi Penyaluran & Penarikan Dana Pagu
                </h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Riwayat serah terima fisik maupun digital antara BPH Bendahara dan Personel Divisi.
                </p>
            </div>
            <div class="text-xs font-bold text-zinc-500">
                Total Transaksi: <span class="font-black text-zinc-900 dark:text-white">{{ $riwayatTransaksi->count() }} Data</span>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3">Kode Transaksi</th>
                        <th class="py-3 px-3">Divisi</th>
                        <th class="py-3 px-3 text-center">Jenis Mutasi</th>
                        <th class="py-3 px-3 text-right">Nominal</th>
                        <th class="py-3 px-3">Penyalur / Penyerah</th>
                        <th class="py-3 px-3">Penerima Dana</th>
                        <th class="py-3 px-3 text-center">Tanggal</th>
                        <th class="py-3 px-3 text-center w-24">Cetak / Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                    @forelse ($riwayatTransaksi as $index => $trx)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="py-3 px-3 text-center font-bold text-zinc-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-zinc-900 dark:text-white">
                                {{ $trx->kode_penyaluran }}
                            </td>
                            <td class="py-3 px-3 font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $trx->divisi?->nama_divisi }}
                                @if ($trx->kategori_penarikan === 'Realokasi Antar Divisi' && $trx->divisiTujuanRealokasi)
                                    <div class="text-[10px] text-teal-600 font-normal">
                                        &rarr; Dialihkan ke: {{ $trx->divisiTujuanRealokasi->nama_divisi }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $trx->jenis_transaksi === 'Penyaluran Pagu' ? 'bg-blue-500/10 text-blue-600 border border-blue-500/20' : 'bg-teal-500/10 text-teal-600 border border-teal-500/20' }}">
                                    {{ $trx->kategori_penarikan ?: $trx->jenis_transaksi }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-black {{ $trx->jenis_transaksi === 'Penyaluran Pagu' ? 'text-blue-600 dark:text-blue-400' : 'text-teal-600 dark:text-teal-400' }}">
                                {{ $trx->jenis_transaksi === 'Penyaluran Pagu' ? '-' : '+' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-zinc-900 dark:text-white">{{ $trx->disalurkanOleh?->nama_tampil ?? '-' }}</div>
                                <div class="text-[10px] text-zinc-400">{{ $trx->disalurkanOleh?->jabatan }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-zinc-900 dark:text-white">{{ $trx->penerima?->nama_tampil ?? '-' }}</div>
                                <div class="text-[10px] text-zinc-400">{{ $trx->penerima?->jabatan }}</div>
                            </td>
                            <td class="py-3 px-3 text-center text-[11px] text-zinc-500">
                                {{ $trx->tanggal_penyaluran?->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Cetak Bukti -->
                                    @if ($trx->jenis_transaksi === 'Penyaluran Pagu')
                                        <a href="{{ route('pagu-divisi-ikhtibar.cetak-bukti-salur', $trx->id) }}" target="_blank"
                                            class="p-1.5 rounded-lg text-zinc-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
                                            title="Cetak Bukti Penyaluran">
                                            <i class="bi bi-printer text-xs"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('pagu-divisi-ikhtibar.cetak-bukti-tarik', $trx->id) }}" target="_blank"
                                            class="p-1.5 rounded-lg text-zinc-500 hover:text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-950/40 transition-colors"
                                            title="Cetak Bukti Penarikan / Berita Acara">
                                            <i class="bi bi-printer text-xs"></i>
                                        </a>
                                    @endif

                                    <!-- Hapus Transaksi -->
                                    <form action="{{ route('pagu-divisi-ikhtibar.destroy-transaksi', $trx->id) }}" method="POST"
                                        class="delete-ajax inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                            title="Hapus Transaksi">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-zinc-400 text-xs">
                                Belum ada riwayat mutasi penyaluran atau penarikan sisa pagu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

