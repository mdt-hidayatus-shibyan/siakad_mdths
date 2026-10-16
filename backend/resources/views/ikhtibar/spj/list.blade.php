<div class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
        <div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="bi bi-receipt-cutoff text-primary"></i>
                Daftar Surat Pertanggungjawaban (SPJ) Belanja
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Pengawasan bukti pengeluaran riil setiap divisi untuk penyusunan Laporan Pertanggungjawaban (LPJ).
            </p>
        </div>
        <div class="text-xs font-bold text-zinc-500">
            Total Belanja SPJ: <span class="font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($spjList->sum('nominal'), 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                    <th class="py-3 px-3 text-center w-12">No</th>
                    <th class="py-3 px-3">Kode & Nota</th>
                    <th class="py-3 px-3">Divisi & Vendor</th>
                    <th class="py-3 px-3">Judul Pengeluaran / Uraian</th>
                    <th class="py-3 px-3 text-right">Nominal (Rp)</th>
                    <th class="py-3 px-3 text-center">Tgl Nota</th>
                    <th class="py-3 px-3 text-center">Diinput Oleh</th>
                    <th class="py-3 px-3 text-center w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                @forelse ($spjList as $index => $spj)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="py-3 px-3 text-center font-bold text-zinc-400">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                @if ($spj->bukti_nota)
                                    <a href="{{ $spj->bukti_nota_url }}" target="_blank"
                                        class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-hidden shrink-0 flex items-center justify-center hover:opacity-80 transition-opacity"
                                        title="Klik untuk melihat foto nota asli">
                                        <img src="{{ $spj->bukti_nota_url }}" alt="Nota" class="w-full h-full object-cover">
                                    </a>
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center shrink-0 text-zinc-400"
                                        title="Tanpa foto nota">
                                        <i class="bi bi-file-earmark-x text-xs"></i>
                                    </div>
                                @endif
                                <div>
                                    <span class="font-mono font-bold text-zinc-900 dark:text-white block">
                                        {{ $spj->kode_spj }}
                                    </span>
                                    <span class="text-[10px] text-zinc-400">
                                        {{ $spj->metode_pembayaran }} • {{ $spj->nama_toko_penerima ?: 'Vendor Umum' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-zinc-900 dark:text-white">
                                {{ $spj->divisi?->nama_divisi ?? 'BPH Inti / Umum' }}
                            </div>
                            <div class="text-[10px] text-zinc-400">
                                {{ $spj->nama_toko_penerima ?: '-' }}
                            </div>
                        </td>
                        <td class="py-3 px-3 max-w-xs">
                            <div class="font-bold text-zinc-800 dark:text-zinc-200 truncate" title="{{ $spj->judul_pengeluaran }}">
                                {{ $spj->judul_pengeluaran }}
                            </div>
                            @if ($spj->rincian_item)
                                <div class="text-[10px] text-zinc-400 truncate" title="{{ $spj->rincian_item }}">
                                    {{ $spj->rincian_item }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right font-black text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($spj->nominal, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-center text-zinc-500 font-mono text-[11px]">
                            {{ $spj->tanggal_nota?->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-[11px] border border-zinc-200/60 dark:border-zinc-700/60">
                                <i class="bi bi-person-fill text-zinc-400"></i>
                                <span>{{ $spj->diinputOleh?->nama_tampil ?? 'Panitia' }}</span>
                            </div>
                            @if ($spj->diinputOleh?->jabatan)
                                <div class="text-[9px] text-zinc-400 mt-0.5">
                                    {{ $spj->diinputOleh->jabatan }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Edit SPJ -->
                                <a href="{{ route('spj-ikhtibar.edit', $spj->id) }}"
                                    class="action-modal p-1.5 rounded-lg text-zinc-500 hover:text-primary hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    title="Edit SPJ" data-modal-size="lg">
                                    <i class="bi bi-pencil text-xs"></i>
                                </a>

                                <!-- Hapus SPJ -->
                                <form action="{{ route('spj-ikhtibar.destroy', $spj->id) }}" method="POST"
                                    class="delete-ajax inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                        title="Hapus SPJ">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-zinc-400 text-xs">
                            Belum ada laporan pengeluaran (SPJ) yang dicatat. Klik <strong>"Input Bukti SPJ"</strong> untuk menambahkan belanja.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

