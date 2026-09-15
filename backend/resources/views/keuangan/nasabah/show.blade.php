@section('title', 'Profil Nasabah - ' . $nasabah->nama_lengkap)

<x-app-layout>
    <div class="space-y-6">
        <!-- 1. Header Navigation & Action -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ route('keuangan.nasabah.index') }}"
                    class="text-xs font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 inline-flex items-center gap-1.5 mb-1.5 transition-colors">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali ke Data Nasabah</span>
                </a>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                        {{ $nasabah->nama_lengkap }}
                    </h2>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                        {{ $nasabah->kode_nasabah }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('keuangan.pinjaman.create', ['nasabah_id' => $nasabah->id]) }}"
                    class="m3-btn-primary h-10 px-5 text-xs font-black shadow-md flex items-center justify-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Ajukan Pinjaman Baru</span>
                </a>
            </div>
        </div>

        <!-- 2. Profile & KYC Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Biodata Card -->
            <div
                class="m3-glass-card p-6 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="flex items-center gap-4">
                    <x-avatar :src="$nasabah->foto_nasabah ? asset('storage/' . $nasabah->foto_nasabah) : null" :name="$nasabah->nama_lengkap" size="lg" />
                    <div>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary/10 text-primary dark:text-primary-dark border border-primary/20 inline-block mb-1">
                            {{ str_replace('_', ' ', $nasabah->tipe_nasabah) }}
                        </span>
                        <h3 class="font-bold text-zinc-900 dark:text-white text-base">
                            {{ $nasabah->nama_lengkap }}
                        </h3>
                        <p class="text-xs text-zinc-400 font-mono">NIK: {{ mask_nik($nasabah->nik_ktp) }}</p>
                    </div>
                </div>

                <div class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 text-xs pt-2">
                    <div class="py-2 flex justify-between">
                        <span class="text-zinc-400 font-medium">WhatsApp / HP</span>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $nasabah->no_hp) }}" target="_blank"
                            class="font-bold text-emerald-600 hover:underline">
                            {{ $nasabah->no_hp }}
                        </a>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-zinc-400 font-medium">Pekerjaan</span>
                        <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $nasabah->pekerjaan ?? '-' }}</span>
                    </div>
                    <div class="py-2">
                        <span class="text-zinc-400 font-medium block mb-1">Alamat Domisili</span>
                        <p class="font-bold text-zinc-800 dark:text-zinc-200">{{ $nasabah->alamat }}</p>
                    </div>
                    @if ($nasabah->catatan)
                        <div class="py-2">
                            <span class="text-zinc-400 font-medium block mb-1">Catatan Khusus</span>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $nasabah->catatan }}</p>
                        </div>
                    @endif
                </div>

                <!-- Foto KTP Document Preview -->
                @if ($nasabah->foto_ktp)
                    <div class="pt-2">
                        <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block mb-2">Dokumen
                            KTP Fisik</span>
                        <a href="{{ asset('storage/' . $nasabah->foto_ktp) }}" target="_blank"
                            class="block rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 relative group">
                            <img src="{{ asset('storage/' . $nasabah->foto_ktp) }}" alt="Foto KTP"
                                class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div
                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">
                                <i class="bi bi-arrows-fullscreen mr-1.5"></i> Perbesar Foto
                            </div>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right: Loan History & Collaterals -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Metrics -->
                <div class="grid grid-cols-3 gap-3">
                    <div
                        class="m3-glass-card p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 text-center">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Total
                            Pengajuan</span>
                        <span
                            class="text-lg font-black text-zinc-900 dark:text-white">{{ $nasabah->pinjamans->count() }}
                            Pinjaman</span>
                    </div>
                    <div
                        class="m3-glass-card p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 text-center">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Pinjaman
                            Berjalan</span>
                        <span
                            class="text-lg font-black text-amber-600 dark:text-amber-400">{{ $nasabah->pinjamanAktif->count() }}
                            Aktif</span>
                    </div>
                    <div
                        class="m3-glass-card p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 text-center">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Sisa Hutang
                            Pokok</span>
                        <span class="text-lg font-black text-rose-600 dark:text-rose-400 font-mono">
                            Rp {{ number_format($nasabah->pinjamanAktif->sum('sisa_pinjaman'), 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Loan History Table -->
                <div
                    class="m3-glass-card rounded-3xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden shadow-sm">
                    <div
                        class="p-4 md:p-5 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider">
                            Riwayat Pinjaman Nasabah
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <th class="py-3 px-4">Kode & Tanggal</th>
                                    <th class="py-3 px-4">Nominal & Tenor</th>
                                    <th class="py-3 px-4">Agunan / Jaminan</th>
                                    <th class="py-3 px-4 text-right">Sisa Hutang</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                                @forelse($nasabah->pinjamans as $pj)
                                    <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-900/40">
                                        <td class="py-3 px-4">
                                            <span
                                                class="font-mono font-bold text-zinc-900 dark:text-white block">{{ $pj->kode_pinjaman }}</span>
                                            <span
                                                class="text-[10px] text-zinc-400">{{ $pj->tanggal_pengajuan->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-bold text-zinc-900 dark:text-white block font-mono">Rp
                                                {{ number_format($pj->nominal_pinjaman, 0, ',', '.') }}</span>
                                            <span class="text-[10px] text-zinc-400">{{ $pj->tenor_bulan }} Bulan (Rp
                                                {{ number_format($pj->nominal_angsuran_total, 0, ',', '.') }}/bln)</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($pj->jaminans->count() > 0)
                                                <span class="font-bold text-zinc-700 dark:text-zinc-300 block">
                                                    {{ $pj->jaminans->first()->nama_barang_jaminan }}
                                                </span>
                                                <span class="text-[10px] text-zinc-400 uppercase">
                                                    {{ str_replace('_', ' ', $pj->jaminans->first()->jenis_jaminan) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-400 italic">Tanpa Jaminan</span>
                                            @endif
                                        </td>
                                        <td
                                            class="py-3 px-4 text-right font-black font-mono {{ $pj->sisa_pinjaman > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            Rp {{ number_format($pj->sisa_pinjaman, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @php
                                                $statusBadge = match ($pj->status) {
                                                    'pengajuan' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                                    'disetujui' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                                                    'dicairkan'
                                                        => 'bg-purple-500/10 text-purple-600 border-purple-500/20',
                                                    'lunas'
                                                        => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                                    'ditolak' => 'bg-rose-500/10 text-rose-600 border-rose-500/20',
                                                    default => 'bg-zinc-500/10 text-zinc-600 border-zinc-500/20',
                                                };
                                            @endphp
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusBadge }}">
                                                {{ $pj->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ route('keuangan.pinjaman.show', $pj->id) }}"
                                                class="w-7 h-7 rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 inline-flex items-center justify-center text-zinc-700 dark:text-zinc-300"
                                                title="Lihat Detail Pinjaman">
                                                <i class="bi bi-chevron-right text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-zinc-400">
                                            Belum ada riwayat pinjaman untuk nasabah ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
