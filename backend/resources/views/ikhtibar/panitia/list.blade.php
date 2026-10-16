<div class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
        <div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i>
                Daftar Personel Kepanitiaan Ikhtibar
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Personel kepengurusan dari unsur Dewan Ustadz/Guru dan Unsur Eksternal Masyarakat/Wali Murid.
            </p>
        </div>
        <div class="text-xs font-bold text-zinc-500">
            Total Personel: <span class="font-black text-zinc-900 dark:text-white">{{ $panitiaList->count() }} Orang</span>
        </div>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                    <th class="py-3 px-3 text-center w-12">No</th>
                    <th class="py-3 px-3">Nama & Identitas</th>
                    <th class="py-3 px-3">Kelompok & Jabatan</th>
                    <th class="py-3 px-3">Divisi / Bidang</th>
                    <th class="py-3 px-3">Unsur / Asal</th>
                    <th class="py-3 px-3">Kontak (WA)</th>
                    <th class="py-3 px-3 text-center">Status Aktif</th>
                    <th class="py-3 px-3 text-center w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                @forelse ($panitiaList as $index => $panitia)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="py-3 px-3 text-center font-bold text-zinc-400">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-700/60 overflow-hidden flex items-center justify-center shrink-0">
                                    @if ($panitia->foto_utama)
                                        <img src="{{ $panitia->foto_utama }}" alt="Foto" class="w-full h-full object-cover">
                                    @else
                                        <i class="bi bi-person-fill text-zinc-400 text-sm"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5 truncate">
                                        <span>{{ $panitia->nama_tampil }}</span>
                                        @if ($panitia->isBphInti())
                                            <i class="bi bi-star-fill text-amber-500 text-[10px]" title="Pimpinan Inti BPH"></i>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-zinc-400 font-normal">
                                        {{ $panitia->identitas_tampil }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $panitia->kelompok_jabatan === 'BPH' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' }}">
                                {{ $panitia->kelompok_jabatan }}
                            </span>
                            <div class="font-bold text-zinc-800 dark:text-zinc-200 mt-1">
                                {{ $panitia->jabatan }}
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            @if ($panitia->divisi)
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $panitia->divisi->nama_divisi }}
                                </div>
                                <div class="text-[10px] text-zinc-400">
                                    [{{ $panitia->divisi->kode_divisi }}]
                                </div>
                            @else
                                <span class="text-zinc-400 italic">Pimpinan BPH</span>
                            @endif
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $panitia->tipe_personel === 'ustadz' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400' }}">
                                {{ $panitia->tipe_personel === 'ustadz' ? 'Dewan Ustadz' : ($panitia->unsur_asal ?: 'Eksternal') }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono text-[11px] text-zinc-600 dark:text-zinc-400">
                            {{ $panitia->no_hp_tampil ?: '-' }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <x-toggle
                                :checked="$panitia->is_active"
                                :url="route('panitia-ikhtibar.toggle-status', $panitia->id)"
                                name="is_active"
                                ajax="true"
                                activeText="Aktif"
                                inactiveText="Nonaktif"
                            />
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('panitia-ikhtibar.edit', $panitia->id) }}"
                                    class="action-modal p-1.5 rounded-lg text-zinc-500 hover:text-primary hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    title="Edit Panitia" data-modal-size="lg">
                                    <i class="bi bi-pencil text-xs"></i>
                                </a>
                                <form action="{{ route('panitia-ikhtibar.destroy', $panitia->id) }}" method="POST"
                                    class="delete-ajax inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                        title="Hapus Personel">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-zinc-400 text-xs">
                            Belum ada panitia yang didaftarkan. Klik tombol <strong>"Tambah Panitia"</strong> untuk menambahkan BPH atau personel divisi!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

