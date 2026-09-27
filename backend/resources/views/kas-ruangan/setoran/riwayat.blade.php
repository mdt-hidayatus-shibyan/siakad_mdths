@section('title', 'Riwayat Kas Ruangan')

<x-app-layout>

    <!-- HEADER (Struktur Sejajar) -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-20 print:hidden">

        <!-- Sisi Kiri: Tombol Back & Judul -->
        <div class="flex items-center gap-3">
            <a href="{{ route('setoran-kas-ruangan.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                        <i class="bi bi-door-open-fill text-xs"></i>
                        <span>{{ $ruangan->level->nama_level ?? 'Kelas' }}</span>
                    </span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Riwayat Setoran Kas {{ $ruangan->nama_ruangan }}
                </h2>
                <p class="text-xs md:text-[13px] text-zinc-500 dark:text-zinc-400 font-medium mt-0.5">
                    Histori penyetoran kas kelas dan verifikasi saldo ke rekening Tabungan Madrasah.
                </p>
            </div>
        </div>

        <!-- Sisi Kanan: Tombol Cetak -->
        <div class="w-full sm:w-auto shrink-0 flex items-center gap-2.5">
            <button type="button" onclick="window.print()"
                class="w-full sm:w-auto h-10 px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 flex items-center justify-center gap-2 shadow-2xs outline-none">
                <i class="bi bi-printer-fill text-xs"></i> Cetak PDF
            </button>
        </div>
    </div>

    <!-- INFO REKENING TABUNGAN & 4 METRIK -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6 relative z-10 print:hidden">

        <!-- 1. Iuran Masuk -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Iuran Masuk
                </p>
                <h4 class="text-lg font-black text-zinc-900 dark:text-white font-mono">
                    Rp {{ number_format($terkumpul, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 2. Masuk Tabungan -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs border-emerald-500/30 bg-emerald-500/5">
            <div
                class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-bank2"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Di
                    Tabungan</p>
                <h4 class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">
                    Rp {{ number_format($tabunganKas ? $tabunganKas->saldo : $disetor, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 3. Menunggu Verifikasi -->
        <div
            class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs {{ $pending > 0 ? 'border-amber-500/40 bg-amber-500/5' : '' }}">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider">Menunggu
                    Verif</p>
                <h4 class="text-lg font-black text-amber-600 dark:text-amber-400 font-mono">
                    Rp {{ number_format($pending, 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 4. Fisik di Wali -->
        <div class="m3-glass-card p-4 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-person-fill-check"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-sky-600 dark:text-sky-400 uppercase tracking-wider">Fisik di Wali
                </p>
                <h4 class="text-lg font-black text-sky-600 dark:text-sky-400 font-mono">
                    Rp {{ number_format($diWali, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>

    @if ($tabunganKas)
        <!-- BANNER REKENING TABUNGAN -->
        <div
            class="mb-6 m3-glass-card p-4.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-emerald-500/30 bg-gradient-to-r from-emerald-500/10 to-teal-500/5 print:hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-sm">
                    <i class="bi bi-credit-card-2-front"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                        Rekening Tabungan Kas Ruangan</h4>
                    <p class="text-sm font-black font-mono text-zinc-900 dark:text-white mt-0.5">
                        {{ $tabunganKas->nomor_rekening }} <span
                            class="font-normal text-xs text-zinc-500">({{ $tabunganKas->nama_rekening }})</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-right">
                <div>
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-wider">Saldo Tabungan Saat Ini</p>
                    <p class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        Rp {{ number_format($tabunganKas->saldo, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- AREA TABEL CETAK -->
    <div id="printArea"
        class="m3-glass-card relative z-10 p-5 md:p-6 print:p-0 print:border-none print:shadow-none print:bg-transparent shadow-2xs">

        <!-- Header Khusus Print -->
        <div class="hidden print:block mb-6 text-center text-black">
            <h1 class="text-2xl font-black uppercase tracking-widest mb-1">Riwayat Setoran Kas Ruangan</h1>
            <h2 class="text-lg font-bold">Ruangan: {{ $ruangan->nama_ruangan }}
                ({{ $ruangan->level->nama_level ?? 'Kelas' }})</h2>
            <p class="text-sm mt-1 text-gray-600">Dicetak pada: {{ date('d M Y H:i') }}</p>
            <hr class="my-4 border-gray-400 border-2">
        </div>

        <!-- Wrapper Tabel -->
        <div class="rounded-2xl overflow-hidden print:border-gray-400 print:shadow-none">
            <div class="overflow-x-auto w-full custom-scrollbar">
                <table class="m3-table w-full print:text-black">
                    <thead>
                        <tr>
                            <th class="text-center w-28">Tanggal</th>
                            <th class="text-left">Disetor Oleh</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-center">Status</th>
                            <th class="hidden md:table-cell text-left">Penerima / Verifikator</th>
                            <th class="hidden md:table-cell text-left">Catatan</th>
                            <th class="text-center w-36 print:hidden">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($setorans as $item)
                            @php
                                $status = $item->status ?? 'Menunggu Verifikasi';
                            @endphp
                            <tr
                                class="{{ $status === 'Menunggu Verifikasi' ? 'bg-amber-500/5 dark:bg-amber-500/5' : '' }}">
                                <td class="text-center font-mono font-bold text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d-m-Y') }}
                                </td>
                                <td class="font-bold text-xs text-zinc-800 dark:text-zinc-200">
                                    <div class="flex items-center gap-1.5">
                                        <i class="bi bi-person-fill text-zinc-400"></i>
                                        <span>{{ $item->penyetor?->ustadz?->nama_lengkap ?? ($item->penyetor?->name ?? 'Wali Ruangan') }}</span>
                                    </div>
                                </td>
                                <td class="text-right font-black font-mono text-zinc-900 dark:text-white text-base">
                                    Rp {{ number_format($item->jumlah_setor, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if ($status === 'Diterima')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <i class="bi bi-check-circle-fill text-xs"></i>
                                            <span>Diterima</span>
                                        </span>
                                    @elseif ($status === 'Menunggu Verifikasi')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                            <i class="bi bi-hourglass-split text-xs"></i>
                                            <span>Menunggu Verifikasi</span>
                                        </span>
                                    @elseif ($status === 'Ditolak')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            <i class="bi bi-x-circle-fill text-xs"></i>
                                            <span>Ditolak</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="font-medium text-xs text-zinc-700 dark:text-zinc-300 hidden md:table-cell">
                                    @if ($item->verifikator)
                                        <div class="text-[11px]">
                                            <span class="font-bold text-zinc-900 dark:text-white">Verif:
                                                {{ $item->verifikator?->ustadz?->nama_lengkap ?? $item->verifikator?->name }}</span>
                                            @if ($item->diverifikasi_pada)
                                                <span
                                                    class="text-zinc-400 text-[10px] block">{{ $item->diverifikasi_pada->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">Ditujukan:
                                            {{ $item->penerima?->ustadz?->nama_lengkap ?? ($item->penerima?->name ?? 'Petugas Tabungan') }}</span>
                                    @endif
                                </td>
                                <td
                                    class="text-zinc-500 dark:text-zinc-400 text-xs hidden md:table-cell italic max-w-xs truncate">
                                    @if ($item->catatan_verifikasi)
                                        <span class="text-amber-600 dark:text-amber-400 font-semibold">[Verif:
                                            {{ $item->catatan_verifikasi }}]</span>
                                    @endif
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                                <td class="text-center print:hidden">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if ($status === 'Menunggu Verifikasi')
                                            <!-- Tombol Verifikasi (Hanya jika pending) -->
                                            <button type="button"
                                                onclick="bukaModalVerifikasi({{ $item->id }}, '{{ number_format($item->jumlah_setor, 0, ',', '.') }}', '{{ addslashes($item->penyetor?->name ?? 'Wali Ruangan') }}')"
                                                class="h-8 px-2.5 flex items-center justify-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-2xs transition-all active:scale-95 outline-none"
                                                title="Verifikasi Setoran">
                                                <i class="bi bi-shield-check text-xs"></i>
                                                <span>Verifikasi</span>
                                            </button>
                                        @endif

                                        <button type="button"
                                            onclick="bukaModalEditSetor({{ $item->id }}, '{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('Y-m-d') }}', {{ $item->jumlah_setor }}, '{{ addslashes($item->keterangan ?? '') }}')"
                                            class="w-8 h-8 flex items-center justify-center bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded-xl transition-all shadow-2xs active:scale-90 outline-none"
                                            title="Koreksi">
                                            <i class="bi bi-pencil-fill text-xs"></i>
                                        </button>
                                        <button type="button"
                                            onclick="kembalikanSetoran({{ $item->id }}, '{{ number_format($item->jumlah_setor, 0, ',', '.') }}')"
                                            class="w-8 h-8 flex items-center justify-center bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 rounded-xl transition-all shadow-2xs active:scale-90 outline-none"
                                            title="Batalkan / Hapus">
                                            <i class="bi bi-trash3-fill text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center">
                                    <x-empty-state icon="bi-receipt" title="Belum Ada Catatan Setoran"
                                        message="Data setoran kas untuk ruangan ini belum tersedia." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL RIWAYAT PENARIKAN KAS -->
        <div class="mt-8 pt-6 border-t border-zinc-200/80 dark:border-zinc-800">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shrink-0">
                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                            Riwayat Penarikan Tunai dari Tabungan
                        </h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">
                            Dana yang ditarik dari rekening Tabungan Kas Ruangan dan dikembalikan ke fisik di tangan
                            Wali.
                        </p>
                    </div>
                </div>
                <span
                    class="px-2.5 py-1 rounded-full text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                    {{ $penarikans->count() }} Penarikan
                </span>
            </div>

            <div class="rounded-2xl overflow-hidden print:border-gray-400 print:shadow-none">
                <div class="overflow-x-auto w-full custom-scrollbar">
                    <table class="m3-table w-full print:text-black">
                        <thead>
                            <tr>
                                <th class="text-center w-28">Tanggal</th>
                                <th class="text-left w-36">Kode Trx</th>
                                <th class="text-right">Nominal Tarik</th>
                                <th class="text-left">Kategori</th>
                                <th class="text-left">Petugas / Pencair</th>
                                <th class="text-left">Keperluan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penarikans as $p)
                                <tr>
                                    <td
                                        class="text-center font-mono font-bold text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}
                                    </td>
                                    <td class="font-mono text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        {{ $p->kode_transaksi }}
                                    </td>
                                    <td
                                        class="text-right font-black font-mono text-rose-600 dark:text-rose-400 text-base">
                                        - Rp
                                        {{ number_format($p->nominal_bersih ?? ($p->nominal_kotor ?? $p->nominal), 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            {{ $p->kategoriPenarikan?->nama ?? 'Penarikan Kas' }}
                                        </span>
                                    </td>
                                    <td class="font-medium text-xs text-zinc-700 dark:text-zinc-300">
                                        <div class="flex items-center gap-1.5">
                                            <i class="bi bi-person-check text-zinc-400"></i>
                                            <span>{{ $p->petugas?->ustadz?->nama_lengkap ?? ($p->petugas?->name ?? 'Petugas Tabungan') }}</span>
                                        </div>
                                    </td>
                                    <td class="text-zinc-600 dark:text-zinc-400 text-xs italic">
                                        {{ $p->keterangan ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center">
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 italic">Belum ada transaksi
                                            penarikan tunai untuk kas ruangan ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL VERIFIKASI SETORAN -->
    <div id="modalVerifikasi"
        class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center hidden backdrop-blur-sm p-4 transition-all print:hidden">
        <div class="m3-glass-card !bg-white dark:!bg-[#0c0c0e] w-full max-w-md p-6 rounded-3xl shadow-xl border border-zinc-200 dark:border-zinc-800 mx-auto relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="modalVerifikasiContent">

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs shrink-0">
                        <i class="bi bi-shield-check text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                            Verifikasi Setoran Kas Ruangan
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">Validasi fisik uang kas dan
                            setujui masuk Tabungan Madrasah</p>
                    </div>
                </div>

                <div
                    class="p-3.5 rounded-2xl bg-zinc-100/70 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 mb-4 space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="text-zinc-500">Penyetor:</span>
                        <span class="font-bold text-zinc-900 dark:text-white" id="verifPenyetor"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-zinc-500">Nominal Diajukan:</span>
                        <span class="font-black font-mono text-emerald-600 dark:text-emerald-400 text-sm"
                            id="verifNominal"></span>
                    </div>
                </div>

                <form id="formVerifikasi" method="POST" class="space-y-4">
                    @csrf

                    <!-- Status Pilihan -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2">Keputusan
                            Verifikasi</label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="Diterima" checked class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 text-center transition-all">
                                    <i class="bi bi-check-circle-fill text-lg text-emerald-600 block mb-0.5"></i>
                                    <span class="text-xs font-black text-zinc-800 dark:text-zinc-200">Terima
                                        Setoran</span>
                                    <span class="text-[10px] text-zinc-500 block">Masuk ke Tabungan</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="Ditolak" class="peer sr-only">
                                <div
                                    class="p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 peer-checked:border-rose-500 peer-checked:bg-rose-500/10 text-center transition-all">
                                    <i class="bi bi-x-circle-fill text-lg text-rose-600 block mb-0.5"></i>
                                    <span class="text-xs font-black text-zinc-800 dark:text-zinc-200">Tolak
                                        Setoran</span>
                                    <span class="text-[10px] text-zinc-500 block">Kembalikan ke Wali</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Catatan Verifikasi -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Catatan
                            Verifikasi (Opsional)</label>
                        <input type="text" name="catatan_verifikasi"
                            placeholder="Misal: Uang fisik sesuai dan telah dihitung..."
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2.5 pt-3 border-t border-zinc-200/80 dark:border-zinc-800">
                        <button type="button" onclick="tutupModalVerifikasi()"
                            class="flex-1 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl shadow-2xs transition-all outline-none active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-2xs transition-all active:scale-95 outline-none">
                            Proses Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT SETORAN -->
    <div id="modalEditSetor"
        class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center hidden backdrop-blur-sm p-4 transition-all print:hidden">
        <div class="m3-glass-card !bg-white dark:!bg-[#0c0c0e] w-full max-w-sm p-6 rounded-3xl shadow-xl border border-zinc-200 dark:border-zinc-800 mx-auto relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="modalEditSetorContent">

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs shrink-0">
                        <i class="bi bi-pencil-fill text-base"></i>
                    </div>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                        Koreksi Berkas Setoran
                    </h3>
                </div>

                <form id="formEditSetor" method="POST" class="space-y-4">
                    @csrf @method('PUT')

                    <!-- Tanggal Bayar -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tanggal
                            Setor</label>
                        <input type="date" name="tanggal_setor" id="editTanggalSetor" required
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <!-- Jumlah Setor -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Jumlah
                            Setor (Rp)</label>
                        <div class="relative group">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-black text-zinc-400 pointer-events-none text-xs font-mono">Rp</span>
                            <input type="number" name="jumlah_setor" id="editJumlahSetor" required min="1"
                                class="m3-input-glass w-full !pl-10 font-mono font-black text-base text-zinc-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Catatan</label>
                        <input type="text" name="keterangan" id="editKeteranganSetor"
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2.5 pt-3 border-t border-zinc-200/80 dark:border-zinc-800">
                        <button type="button" onclick="tutupModalEditSetor()"
                            class="flex-1 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl shadow-2xs transition-all outline-none active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 h-10 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs rounded-xl shadow-2xs transition-all active:scale-95 outline-none">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Hidden untuk Hapus Riwayat -->
    <form id="formHapusSetor" method="POST" class="hidden print:hidden">
        @csrf @method('DELETE')
    </form>

    <script>
        const baseUrlSetoran = "{{ url('setoran-kas-ruangan') }}";

        function bukaModalVerifikasi(id, nominal, penyetor) {
            const form = document.getElementById('formVerifikasi');
            form.action = `${baseUrlSetoran}/${id}/verifikasi`;
            document.getElementById('verifPenyetor').innerText = penyetor;
            document.getElementById('verifNominal').innerText = 'Rp ' + nominal;

            const modal = document.getElementById('modalVerifikasi');
            const content = document.getElementById('modalVerifikasiContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function tutupModalVerifikasi() {
            const modal = document.getElementById('modalVerifikasi');
            const content = document.getElementById('modalVerifikasiContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function bukaModalEditSetor(id, tgl, jml, ket) {
            const form = document.getElementById('formEditSetor');
            form.action = `${baseUrlSetoran}/${id}`;
            document.getElementById('editTanggalSetor').value = tgl;
            document.getElementById('editJumlahSetor').value = jml;
            document.getElementById('editKeteranganSetor').value = ket;

            const modal = document.getElementById('modalEditSetor');
            const content = document.getElementById('modalEditSetorContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function tutupModalEditSetor() {
            const modal = document.getElementById('modalEditSetor');
            const content = document.getElementById('modalEditSetorContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function kembalikanSetoran(id, nominal) {
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: '<span class="text-base font-black tracking-tight">Hapus / Batalkan Setoran?</span>',
                html: `<p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">Anda akan menghapus berkas setoran sebesar <b>Rp ${nominal}</b>.<br>Jika sebelumnya sudah diterima, saldo Tabungan akan disesuaikan kembali.</p>`,
                icon: 'warning',
                showCancelButton: true,
                heightAuto: false,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#71717a',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                background: isDark ? '#0c0c0e' : '#ffffff',
                color: isDark ? '#f4f4f5' : '#18181b',
                customClass: {
                    popup: '!rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl p-6',
                    confirmButton: 'h-10 px-5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none',
                    cancelButton: 'h-10 px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '<span class="text-base font-black tracking-tight">Memproses...</span>',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        background: isDark ? '#0c0c0e' : '#ffffff',
                        color: isDark ? '#f4f4f5' : '#18181b',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    const form = document.getElementById('formHapusSetor');
                    form.action = `${baseUrlSetoran}/${id}`;
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
