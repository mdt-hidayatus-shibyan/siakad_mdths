<div class="overflow-x-auto custom-scrollbar">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr
                class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 text-[11px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                <th class="py-3.5 px-4">Nasabah / Peminjam</th>
                <th class="py-3.5 px-4">Kategori & NIK</th>
                <th class="py-3.5 px-4">No. HP / WA</th>
                <th class="py-3.5 px-4">Alamat & Pekerjaan</th>
                <th class="py-3.5 px-4 text-center">Pinjaman Aktif</th>
                <th class="py-3.5 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 text-xs">
            @forelse ($nasabahs as $nasabah)
                <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-900/40 transition-colors group">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <x-avatar :src="$nasabah->foto_nasabah ? asset('storage/' . $nasabah->foto_nasabah) : null" :name="$nasabah->nama_lengkap" size="md" />
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-zinc-900 dark:text-white text-sm truncate">
                                        {{ $nasabah->nama_lengkap }}
                                    </span>
                                </div>
                                <span class="text-[11px] font-mono text-zinc-400 dark:text-zinc-500 block">
                                    {{ $nasabah->kode_nasabah }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        @php
                            $tipeBadge = match ($nasabah->tipe_nasabah) {
                                'ustadz'
                                    => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                'pengurus'
                                    => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                'wali_murid' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                default => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
                            };
                        @endphp
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $tipeBadge }} mb-1 inline-block">
                            {{ str_replace('_', ' ', $nasabah->tipe_nasabah) }}
                        </span>
                        <div class="text-[11px] text-zinc-500 font-mono">
                            NIK: {{ mask_nik($nasabah->nik_ktp) }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $nasabah->no_hp) }}" target="_blank"
                            class="font-bold text-zinc-900 dark:text-white hover:text-primary flex items-center gap-1.5 transition-colors">
                            <i class="bi bi-whatsapp text-emerald-500"></i>
                            <span>{{ $nasabah->no_hp }}</span>
                        </a>
                    </td>
                    <td class="py-3.5 px-4 max-w-xs">
                        <p class="text-zinc-700 dark:text-zinc-300 truncate" title="{{ $nasabah->alamat }}">
                            {{ $nasabah->alamat }}
                        </p>
                        <span
                            class="text-[11px] text-zinc-400 block">{{ $nasabah->pekerjaan ?? 'Pekerjaan tidak dicantumkan' }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        @php
                            $pinjamanAktifCount = $nasabah->pinjamanAktif->count();
                        @endphp
                        @if ($pinjamanAktifCount > 0)
                            <span
                                class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                {{ $pinjamanAktifCount }} Pinjaman Berjalan
                            </span>
                        @else
                            <span
                                class="px-2.5 py-1 rounded-full text-[10px] font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                Tidak Ada Pinjaman
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('keuangan.nasabah.show', $nasabah->id) }}"
                                class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-500/20 flex items-center justify-center transition-colors"
                                title="Profil & Riwayat Pinjaman">
                                <i class="bi bi-person-lines-fill"></i>
                            </a>
                            <a href="{{ route('keuangan.pinjaman.create', ['nasabah_id' => $nasabah->id]) }}"
                                class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 flex items-center justify-center transition-colors"
                                title="Buat Pengajuan Pinjaman">
                                <i class="bi bi-cash-stack"></i>
                            </a>
                            <button type="button" onclick="openEditNasabahModal({{ $nasabah->id }})"
                                class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 flex items-center justify-center transition-colors"
                                title="Edit Data Nasabah">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button"
                                onclick="confirmDeleteNasabah({{ $nasabah->id }}, '{{ $nasabah->nama_lengkap }}')"
                                class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 flex items-center justify-center transition-colors"
                                title="Hapus Data Nasabah">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-zinc-400 dark:text-zinc-500">
                        <i class="bi bi-people text-3xl mb-2 block"></i>
                        <p class="font-bold">Belum ada nasabah terdaftar.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800">
    {{ $nasabahs->links('vendor.pagination.custom') }}
</div>
