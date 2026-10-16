<div
    class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
        <div>
            <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="bi bi-person-lines-fill text-primary"></i>
                Daftar Tagihan Kepala Keluarga (KK)
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                Monitoring pembayaran iuran kegiatan Ikhtibar berbasis per Kepala Keluarga / Wali Murid.
            </p>
        </div>
        <div class="text-xs font-bold text-zinc-500">
            Total Ditampilkan: <span class="font-black text-zinc-900 dark:text-white">{{ $walis->count() }} KK</span>
        </div>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr
                    class="border-b border-zinc-200/80 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[10px]">
                    <th class="py-3 px-3 text-center w-12">No</th>
                    <th class="py-3 px-3">Kepala Keluarga (KK)</th>
                    <th class="py-3 px-3">Dusun / Kampung</th>
                    <th class="py-3 px-3">Murid Terdaftar</th>
                    <th class="py-3 px-3 text-right">Nominal Tagihan</th>
                    <th class="py-3 px-3 text-center">Status Bayar</th>
                    <th class="py-3 px-3">Keterangan / Pelunasan</th>
                    <th class="py-3 px-3 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody
                class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-medium text-zinc-800 dark:text-zinc-200">
                @forelse ($walis as $index => $wali)
                    @php
                        $tagihan = $wali->current_tagihan;
                    @endphp
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="py-3 px-3 text-center font-bold text-zinc-400">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-zinc-900 dark:text-white">
                                {{ $wali->nama_kepala_keluarga }}
                            </div>
                            <div class="text-[10px] text-zinc-400">
                                {{ $wali->no_registrasi ?: '-' }}
                            </div>
                        </td>
                        <td class="py-3 px-3 text-zinc-600 dark:text-zinc-300">
                            {{ $wali->kampung?->nama_kampung ?: '-' }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="text-[11px] font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $wali->murids->pluck('nama_lengkap')->implode(', ') ?: 'Tidak ada anak aktif' }}
                            </div>
                            <div class="text-[10px] text-zinc-400">
                                ({{ $wali->murids->count() }} Anak Aktif)
                            </div>
                        </td>
                        <td class="py-3 px-3 text-right font-black text-zinc-900 dark:text-white">
                            @if ($tagihan)
                                Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                            @else
                                <span class="text-zinc-400 font-normal">Rp
                                    {{ number_format($pengaturanTagihan?->nominal ?? 0, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if ($tagihan)
                                @if ($tagihan->status_bayar === 'Lunas')
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        <i class="bi bi-check-circle-fill"></i> Lunas
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        <i class="bi bi-clock-fill"></i> Belum Lunas
                                    </span>
                                @endif
                            @else
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700">
                                    Belum Terbit
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-[11px] text-zinc-500 dark:text-zinc-400 max-w-xs truncate"
                            title="{{ $tagihan?->keterangan }}">
                            {{ $tagihan?->keterangan ?: '-' }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if ($tagihan)
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($tagihan->status_bayar !== 'Lunas')
                                        <a href="{{ route('tagihan-ikhtibar.modal-bayar', $tagihan->id) }}"
                                            class="action-modal px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1"
                                            title="Bayar Tagihan" data-modal-size="lg">
                                            <i class="bi bi-cash"></i>
                                            <span>Bayar</span>
                                        </a>
                                    @else
                                        <!-- Tombol Kwitansi -->
                                        <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $tagihan->id) }}"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-zinc-500 hover:text-primary hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                            title="Cetak Kwitansi Pembayaran">
                                            <i class="bi bi-printer text-xs"></i>
                                        </a>
                                        <!-- Tombol Batalkan Pembayaran -->
                                        <button type="button" onclick="batalBayar({{ $tagihan->id }})"
                                            class="p-1.5 rounded-lg text-zinc-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors"
                                            title="Batalkan Pelunasan">
                                            <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="text-[10px] text-zinc-400 italic">Terbitkan massal</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-zinc-400 text-xs">
                            Tidak ada data Kepala Keluarga yang cocok dengan filter saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function batalBayar(tagihanId) {
        Swal.fire({
            title: 'Batalkan Pembayaran?',
            text: 'Status tagihan KK ini akan dikembalikan menjadi Belum Lunas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/tagihan-ikhtibar/batal-bayar/${tagihanId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Toast.fire({
                                icon: 'success',
                                title: data.message
                            });
                            if (typeof window.refreshDataGrid === 'function') {
                                window.refreshDataGrid();
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    });
            }
        });
    }
</script>
