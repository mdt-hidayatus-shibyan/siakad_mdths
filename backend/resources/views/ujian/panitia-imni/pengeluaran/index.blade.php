@section('title', 'Buku Kas & Pengeluaran IMNI')

<x-app-layout>
    <div>
        <!-- 1. HEADER SECTION -->
        <div
            class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Buku Kas & Pengeluaran IMNI
                </h2>
                <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Pencatatan pos pengeluaran operasional ujian, honorarium pengawas & panitia, serta laporan
                    pertanggungjawaban kas IMNI.
                </p>
            </div>

            <!-- Toolbar Aksi Utama -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('pengeluaran-imni.index') }}" method="GET" id="formTahunPengeluaran"
                    class="m-0">
                    <div class="relative">
                        <select name="tahun_id" onchange="document.getElementById('formTahunPengeluaran').submit()"
                            class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                    {{ $th->nama_hijriyah }} H | {{ $th->nama_masehi }} M
                                    {{ $th->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                    </div>
                </form>

                <!-- Tombol Tambah Pengeluaran (AJAX Action Modal) -->
                <a href="{{ route('pengeluaran-imni.create', ['tahun_id' => $selectedTahunId]) }}"
                    class="action-modal px-3.5 py-2 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <i class="bi bi-plus-circle-fill text-sm"></i>
                    <span>Catat Pengeluaran</span>
                </a>

                <!-- Tombol Cetak LPJ Kas IMNI -->
                <a href="{{ route('pengeluaran-imni.cetak-lpj', ['tahun_id' => $selectedTahunId]) }}" target="_blank"
                    class="px-3.5 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-sm text-amber-600"></i>
                    <span>Cetak LPJ Kas IMNI</span>
                </a>


            </div>
        </div>

        <!-- 2. ALERTS & NOTIFIKASI -->
        @if (session('success'))
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex items-center justify-between gap-3 border-l-4 border-l-emerald-500 bg-emerald-500/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-base border border-emerald-500/20">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        {{ session('success') }}
                    </p>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-6 p-4 m3-glass-card rounded-2xl md:rounded-3xl flex items-center justify-between gap-3 border-l-4 border-l-rose-500 bg-rose-500/5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 text-base border border-rose-500/20">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                    <p class="text-xs font-bold text-rose-700 dark:text-rose-300">
                        {{ session('error') }}
                    </p>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        <!-- 3. STATISTIK METRIK KEUANGAN (4 STAT CARDS) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 md:mb-8">
            <!-- Card 1: Total Pemasukan Kas IMNI -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Pemasukan Kasir IMNI
                        </p>
                        <h3 class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                            Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                        </h3>
                        <p class="text-[11px] font-semibold text-zinc-500 mt-1">
                            Realisasi setoran murid
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Pengeluaran Kas IMNI -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Total Pengeluaran
                        </p>
                        <h3 class="text-xl md:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </h3>
                        <p class="text-[11px] font-semibold text-zinc-500 mt-1">
                            {{ $pengeluarans->total() }} transaksi dicatat
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shrink-0 border border-rose-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3: Saldo Kas Bersih IMNI -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-primary/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Saldo Kas IMNI
                        </p>
                        <h3
                            class="text-xl md:text-2xl font-black {{ $saldoKas >= 0 ? 'text-primary dark:text-primary-dark' : 'text-rose-600' }} mt-1">
                            Rp {{ number_format($saldoKas, 0, ',', '.') }}
                        </h3>
                        <p class="text-[11px] font-semibold text-zinc-500 mt-1">
                            {{ $saldoKas >= 0 ? 'Surplus operasional' : 'Defisit kas' }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center text-xl shrink-0 border border-primary/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-piggy-bank"></i>
                    </div>
                </div>
            </div>

            <!-- Card 4: Penanggung Jawab Keuangan -->
            <div
                class="m3-glass-card rounded-2xl md:rounded-3xl p-5 relative overflow-hidden group hover:border-indigo-500/40 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            Bendahara Panitia
                        </p>
                        <h4 class="text-sm font-black text-zinc-900 dark:text-white mt-1 truncate">
                            {{ $bendaharaPanitia?->ustadz?->nama_lengkap ?? 'Belum Ditentukan' }}
                        </h4>
                        <p class="text-[11px] text-zinc-500 mt-1">
                            Ketua: {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? '-' }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0 border border-indigo-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. FILTER & PENCARIAN -->
        <div class="m3-glass-card rounded-2xl md:rounded-3xl p-4 md:p-5 mb-6">
            <form action="{{ route('pengeluaran-imni.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <input type="hidden" name="tahun_id" value="{{ $selectedTahunId }}">

                <!-- Filter Kategori -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Kategori Pengeluaran
                    </label>
                    <select name="kategori" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tanggal Mulai -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Dari Tanggal
                    </label>
                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Sampai Tanggal
                    </label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>

                <!-- Input Pencarian -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-1">
                        Cari Uraian / Penerima
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Uraian / transaksi / penerima..."
                            class="w-full pl-8 pr-8 py-2 text-xs bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                        @if (request('search') || request('kategori') || request('tanggal_mulai') || request('tanggal_akhir'))
                            <a href="{{ route('pengeluaran-imni.index', ['tahun_id' => $selectedTahunId]) }}"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-rose-500 text-xs"
                                title="Reset Filter">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- 5. TABEL DATA PENGELUARAN IMNI -->
        <div id="data-table-container" class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr
                            class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 font-black text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-3">Kode & Tanggal</th>
                            <th class="py-3.5 px-3">Kategori</th>
                            <th class="py-3.5 px-4">Uraian Pengeluaran</th>
                            <th class="py-3.5 px-3 text-right">Nominal (Rp)</th>
                            <th class="py-3.5 px-3">Penerima & Metode</th>
                            <th class="py-3.5 px-3 text-center">Bukti Nota</th>
                            <th class="py-3.5 px-3">Pencatat</th>
                            <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60 text-zinc-700 dark:text-zinc-300">
                        @forelse ($pengeluarans as $index => $item)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-900/40 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-400 text-[11px]">
                                    {{ $pengeluarans->firstItem() + $index }}
                                </td>

                                <!-- Kode & Tanggal -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <div class="font-mono font-bold text-zinc-900 dark:text-white text-[11px]">
                                        {{ $item->kode_transaksi }}
                                    </div>
                                    <div class="text-[10px] text-zinc-400">
                                        {{ $item->tanggal_pengeluaran ? $item->tanggal_pengeluaran->format('d/m/Y') : '-' }}
                                    </div>
                                </td>

                                <!-- Kategori Badge -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black
                                        @if ($item->kategori === 'Pra IMNI') bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20
                                        @elseif($item->kategori === 'Saat IMNI') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                        @elseif($item->kategori === 'Pasca IMNI') bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20
                                        @else bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border border-zinc-500/20 @endif">
                                        {{ $item->kategori }}
                                    </span>
                                </td>

                                <!-- Uraian & Keterangan -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-zinc-900 dark:text-white text-xs">
                                        {{ $item->judul_pengeluaran }}
                                    </div>
                                    @if ($item->keterangan)
                                        <div class="text-[10px] text-zinc-400 mt-0.5 italic">
                                            {{ $item->keterangan }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Nominal -->
                                <td
                                    class="py-3.5 px-3 text-right font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                    Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </td>

                                <!-- Penerima Dana & Metode -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <div class="font-bold text-zinc-900 dark:text-white text-[11px]">
                                        {{ $item->penerima_dana ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-zinc-400">
                                        {{ $item->metode_pembayaran }}
                                    </div>
                                </td>

                                <!-- Bukti Nota -->
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    @if ($item->bukti_nota)
                                        <a href="{{ asset('storage/' . $item->bukti_nota) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-[10px] transition-all">
                                            <i class="bi bi-paperclip"></i>
                                            <span>Lihat Nota</span>
                                        </a>
                                    @else
                                        <span class="text-zinc-400 italic text-[10px]">-</span>
                                    @endif
                                </td>

                                <!-- Pencatat -->
                                <td class="py-3.5 px-3 whitespace-nowrap text-[10px] text-zinc-500 dark:text-zinc-400">
                                    {{ $item->pencatat?->name ?? 'Panitia' }}
                                </td>

                                <!-- Aksi (AJAX Action Modal) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit -->
                                        <a href="{{ route('pengeluaran-imni.edit', $item->id) }}"
                                            class="action-modal p-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold transition-all cursor-pointer"
                                            title="Ubah Data Pengeluaran">
                                            <i class="bi bi-pencil-square text-xs"></i>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('pengeluaran-imni.destroy', $item->id) }}"
                                            method="POST" class="delete-ajax"
                                            data-refresh-target="#data-table-container">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold transition-all cursor-pointer"
                                                title="Hapus Pengeluaran">
                                                <i class="bi bi-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-zinc-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="bi bi-inbox text-3xl"></i>
                                        <p class="font-bold text-xs">Belum ada data pengeluaran operasional IMNI yang
                                            dicatat.</p>
                                        <p class="text-[11px] text-zinc-400">Klik tombol "+ Catat Pengeluaran" di atas
                                            untuk menambahkan buku kas keluar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($pengeluarans->hasPages())
                <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    {{ $pengeluarans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
