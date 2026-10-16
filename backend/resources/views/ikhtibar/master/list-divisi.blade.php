<div class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
        <div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                Daftar Divisi Kepanitiaan Ikhtibar
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Struktur divisi kerja dan penetapan pagu anggaran operasional masing-masing bidang.
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <span class="text-xs font-bold text-zinc-500">Total Pagu:</span>
            <span class="text-sm font-black text-primary dark:text-primary-light">
                Rp {{ number_format($divisis->sum('nominal_pagu'), 0, ',', '.') }}
            </span>
        </div>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                    <th class="py-3 px-3 text-center w-12">No</th>
                    <th class="py-3 px-3">Kode & Nama Divisi</th>
                    <th class="py-3 px-3">Deskripsi Tugas</th>
                    <th class="py-3 px-3 text-right">Pagu Awal</th>
                    <th class="py-3 px-3 text-center">Personel</th>
                    <th class="py-3 px-3 text-center">Status Pagu</th>
                    <th class="py-3 px-3 text-center">Status Aktif</th>
                    <th class="py-3 px-3 text-center w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                @forelse ($divisis as $index => $divisi)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="py-3 px-3 text-center font-bold text-zinc-400">
                            {{ $divisi->urutan ?? $index + 1 }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200/60 dark:border-zinc-700/60">
                                    {{ $divisi->kode_divisi }}
                                </span>
                                <span class="font-bold text-zinc-900 dark:text-white">
                                    {{ $divisi->nama_divisi }}
                                </span>
                                @if ($divisi->is_bph)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        BPH Inti
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-3 text-zinc-500 dark:text-zinc-400 max-w-xs truncate" title="{{ $divisi->deskripsi_tugas }}">
                            {{ $divisi->deskripsi_tugas ?: '-' }}
                        </td>
                        <td class="py-3 px-3 text-right font-black text-zinc-900 dark:text-white">
                            Rp {{ number_format($divisi->nominal_pagu, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if ($divisi->is_bph)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300">
                                    {{ $divisi->koordinator_tampil?->nama_tampil ?? 'BPH' }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">
                                    {{ $divisi->panitias_count ?? 0 }} Personel
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $divisi->status_pagu_badge_color }}">
                                {{ $divisi->status_pagu }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <x-toggle
                                :checked="$divisi->is_active"
                                :url="route('master-ikhtibar.toggle-status-divisi', $divisi->id)"
                                name="is_active"
                                ajax="true"
                                activeText="Aktif"
                                inactiveText="Nonaktif"
                            />
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('master-ikhtibar.edit-divisi', $divisi->id) }}"
                                    class="action-modal p-1.5 rounded-lg text-zinc-500 hover:text-primary hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    title="Edit Divisi" data-modal-size="lg">
                                    <i class="bi bi-pencil text-xs"></i>
                                </a>
                                <form action="{{ route('master-ikhtibar.destroy-divisi', $divisi->id) }}" method="POST"
                                    class="delete-ajax inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                        title="Hapus Divisi">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-zinc-400 text-xs">
                            Belum ada divisi yang dibuat. Klik tombol <strong>"Generate 7 Divisi Standar"</strong> untuk membuat susunan divisi secara otomatis!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

