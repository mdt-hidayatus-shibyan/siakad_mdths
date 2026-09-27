@section('title', 'Setoran Kas Ruangan')

<x-app-layout>

    <!-- HEADER & TOOLBAR SEJAJAR -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20 print:hidden">

        <!-- Sisi Kiri: Judul Halaman -->
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                    <i class="bi bi-wallet2 text-xs"></i>
                    <span>Kas Ruangan & Tabungan Madrasah</span>
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Setoran Kas ke Tabungan Madrasah
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Penyetoran kas ruangan oleh Wali Ruangan dan verifikasi saldo ke rekening Tabungan Madrasah.
            </p>
        </div>

        <!-- Sisi Kanan: Toolbar Filter -->
        <div class="w-full xl:w-auto shrink-0">
            <form action="{{ request()->url() }}" method="GET" id="formFilter"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">

                <!-- Filter Tahun Pelajaran -->
                <div class="relative w-full sm:w-[200px] h-10">
                    <select name="tahun_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full h-full !py-0 !pl-3.5 !pr-8 text-xs font-bold appearance-none cursor-pointer">
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[200px] h-10">
                    <select name="ruangan_id" onchange="document.getElementById('formFilter').submit()"
                        class="m3-input-glass w-full h-full !py-0 !pl-3.5 !pr-8 text-xs font-bold appearance-none cursor-pointer">
                        <option value="">-- Semua Ruangan --</option>
                        @foreach ($daftarRuangan as $ruangItem)
                            <option value="{{ $ruangItem->id }}"
                                {{ isset($ruanganId) && $ruanganId == $ruangItem->id ? 'selected' : '' }}>
                                {{ $ruangItem->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- STATISTIK GLOBAL (4 Kartu Atas) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4 mb-6 relative z-10">

        <!-- 1. Total Iuran Murid -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0 border border-indigo-500/20 shadow-2xs">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Total Iuran Murid</p>
                <h4 class="text-base md:text-xl font-black text-zinc-900 dark:text-white tracking-tight font-mono">
                    Rp {{ number_format($ruangans->sum('total_terkumpul'), 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 2. Masuk Tabungan Madrasah (Emerald) -->
        <div
            class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl md:rounded-3xl shadow-md border border-emerald-500/40 p-4 md:p-5 flex items-center gap-3.5 text-white relative overflow-hidden">
            <div
                class="w-11 h-11 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl shrink-0 border border-white/20 shadow-2xs backdrop-blur-md">
                <i class="bi bi-bank2"></i>
            </div>
            <div class="min-w-0 flex-1 relative z-10">
                <p class="text-[10px] font-black text-emerald-100 uppercase tracking-wider mb-0.5 truncate">Saldo
                    Tabungan
                </p>
                <h4 class="text-base md:text-xl font-black text-white tracking-tight font-mono">
                    Rp {{ number_format($tabungans->sum('saldo'), 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 3. Menunggu Verifikasi (Amber) -->
        <div
            class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs {{ $ruangans->sum('total_pending') > 0 ? 'border-amber-500/40 ring-2 ring-amber-500/10 bg-amber-500/5' : '' }}">
            <div
                class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 border border-amber-500/20 shadow-2xs relative">
                <i class="bi bi-clock-history"></i>
                @if ($totalMenungguVerifikasi > 0)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full animate-ping"></span>
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full"></span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <p
                        class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                        Menunggu Verifikasi</p>
                </div>
                <h4 class="text-base md:text-xl font-black text-amber-600 dark:text-amber-400 tracking-tight font-mono">
                    Rp {{ number_format($ruangans->sum('total_pending'), 0, ',', '.') }}
                </h4>
            </div>
        </div>

        <!-- 4. Uang Fisik di Wali -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl shrink-0 border border-sky-500/20 shadow-2xs">
                <i class="bi bi-person-fill-check"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 truncate">
                    Fisik di Wali</p>
                <h4 class="text-base md:text-xl font-black text-sky-600 dark:text-sky-400 tracking-tight font-mono">
                    Rp
                    {{ number_format(max(0, $ruangans->sum('total_terkumpul') + $tabungans->sum('total_tarik') - $ruangans->sum('total_disetor') - $ruangans->sum('total_pending')), 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>

    <!-- LIST RUANGAN -->
    <div class="flex flex-col gap-3.5 relative z-10">
        @forelse($ruangans as $ruang)
            @php
                $terkumpul = $ruang->total_terkumpul ?? 0;
                $disetor = $ruang->total_disetor ?? 0;
                $pending = $ruang->total_pending ?? 0;
                $tabungan = $tabungans[$ruang->id] ?? null;
                $ditarik = $tabungan ? (float) $tabungan->total_tarik : 0;
                $saldoTab = $tabungan ? (float) $tabungan->saldo : 0;
                $diWali = max(0, $terkumpul + $ditarik - $disetor - $pending);
            @endphp

            <div
                class="m3-glass-card p-4 md:p-5 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40 group">

                <!-- Info Ruangan & Rekening Tabungan -->
                <div class="flex items-start sm:items-center gap-3.5 min-w-[220px]">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0 shadow-2xs border border-emerald-500/20">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3
                                class="font-black text-base md:text-lg text-zinc-900 dark:text-white leading-tight tracking-tight">
                                {{ $ruang->nama_ruangan }}
                            </h3>
                            <span
                                class="inline-block px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-[10px] font-black rounded-lg uppercase tracking-wider border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                                {{ $ruang->level->nama_level ?? 'Kelas' }}
                            </span>
                        </div>

                        @if ($tabungan)
                            <div
                                class="flex items-center gap-1.5 mt-1 text-[11px] font-mono text-zinc-500 dark:text-zinc-400">
                                <i class="bi bi-credit-card-2-front text-emerald-500 text-xs"></i>
                                <span class="font-bold">{{ $tabungan->nomor_rekening }}</span>
                                <span>•</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">Saldo: Rp
                                    {{ number_format($tabungan->saldo, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 mt-1">
                                <i class="bi bi-info-circle mr-1"></i> Rekening tabungan otomatis dibuat saat setoran
                                pertama
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Grid Rincian Keuangan -->
                <div
                    class="grid grid-cols-2 sm:grid-cols-4 w-full lg:w-auto flex-1 bg-zinc-500/5 dark:bg-zinc-800/40 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60 divide-y sm:divide-y-0 sm:divide-x divide-zinc-200/80 dark:divide-zinc-700/60 shadow-2xs font-mono gap-y-2 sm:gap-y-0">
                    <div class="text-center px-2">
                        <p
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 font-sans">
                            Iuran Masuk</p>
                        <p class="text-xs md:text-sm font-black text-zinc-700 dark:text-zinc-300 tracking-tight">Rp
                            {{ number_format($terkumpul, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center px-2">
                        <p
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 font-sans">
                            Di Tabungan</p>
                        <p class="text-xs md:text-sm font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                            Rp
                            {{ number_format($saldoTab, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center px-2">
                        <p
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 font-sans">
                            Menunggu Verif</p>
                        <p class="text-xs md:text-sm font-black text-amber-600 dark:text-amber-400 tracking-tight">Rp
                            {{ number_format($pending, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center px-2">
                        <p
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5 font-sans">
                            Fisik di Wali</p>
                        <p
                            class="text-xs md:text-sm font-black tracking-tight {{ $diWali > 0 ? 'text-sky-600 dark:text-sky-400' : 'text-zinc-400' }}">
                            Rp {{ number_format($diWali, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="flex items-center gap-2 w-full lg:w-auto shrink-0">
                    @if ($diWali > 0)
                        <button type="button"
                            onclick="bukaModalSetor({{ $ruang->id }}, '{{ $ruang->nama_ruangan }}', {{ $diWali }})"
                            class="flex-1 lg:flex-none h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-2xs transition-all active:scale-95 flex items-center justify-center gap-1.5 outline-none">
                            <i class="bi bi-box-arrow-in-down text-sm"></i> Terima Setor
                        </button>
                    @endif

                    <a href="{{ route('setoran-kas-ruangan.riwayat', $ruang->id) }}"
                        class="flex-1 lg:flex-none h-10 px-4 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-700 font-black text-xs uppercase tracking-wider rounded-xl shadow-2xs transition-all flex items-center justify-center gap-1.5 active:scale-95 outline-none relative">
                        <i class="bi bi-clock-history text-xs"></i>
                        <span>Riwayat & Verifikasi</span>
                        @if ($pending > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        @endif
                    </a>
                </div>
            </div>
        @empty
            <x-empty-state icon="bi-inbox" title="Belum Ada Data"
                message="Tidak ada ruangan yang sesuai dengan filter." />
        @endforelse
    </div>

    <!-- MODAL TERIMA SETORAN MANUAL DARI ADMIN -->
    <div id="modalSetor"
        class="fixed inset-0 bg-black/60 z-[99] flex items-center justify-center hidden backdrop-blur-sm p-4 transition-all print:hidden">
        <div class="m3-glass-card !bg-white dark:!bg-[#0c0c0e] w-full max-w-sm p-6 rounded-3xl shadow-xl border border-zinc-200 dark:border-zinc-800 mx-auto relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="modalSetorContent">

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-1">
                    <div
                        class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
                        <i class="bi bi-box-arrow-in-down text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">Terima Setoran
                            Kas</h3>
                        <p id="teksNamaRuangan" class="text-xs font-bold text-zinc-500 dark:text-zinc-400"></p>
                    </div>
                </div>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-2">
                    Uang fisik kas yang diterima akan otomatis masuk ke rekening <b>Tabungan Madrasah</b> tipe Kas
                    Ruangan.
                </p>

                <form action="{{ route('setoran-kas-ruangan.simpan') }}" method="POST" class="space-y-4 mt-4">
                    @csrf
                    <input type="hidden" name="ruangan_id" id="inputSetorRuangan">

                    <!-- Tanggal Terima -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tanggal
                            Terima</label>
                        <input type="date" name="tanggal_setor" value="{{ date('Y-m-d') }}" required
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <!-- Jumlah Uang -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Jumlah
                            Uang Fisik (Rp)</label>
                        <div class="relative group">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-black text-zinc-400 pointer-events-none text-xs font-mono">Rp</span>
                            <input type="number" name="jumlah_setor" id="inputSetorJumlah" required min="1"
                                class="m3-input-glass w-full !pl-10 font-mono font-black text-base text-zinc-900 dark:text-white">
                        </div>
                        <p id="teksBatasSetor"
                            class="text-[10px] font-black text-amber-600 dark:text-amber-400 mt-1.5 ml-1 uppercase tracking-wider">
                        </p>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Catatan
                            (Opsional)</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Setoran tahap 1..."
                            class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2.5 pt-3 border-t border-zinc-200/80 dark:border-zinc-800">
                        <button type="button" onclick="tutupModalSetor()"
                            class="flex-1 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl shadow-2xs transition-all outline-none active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-2xs transition-all active:scale-95 outline-none">
                            Terima & Masukkan Tabungan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script Modal -->
    <script>
        function bukaModalSetor(id, nama, maxSetor) {
            document.getElementById('inputSetorRuangan').value = id;
            document.getElementById('teksNamaRuangan').innerText = "Ruangan: " + nama;

            const inputJumlah = document.getElementById('inputSetorJumlah');
            inputJumlah.value = maxSetor;
            inputJumlah.max = maxSetor;

            document.getElementById('teksBatasSetor').innerText = "* Batas uang di Wali: Rp " + maxSetor.toLocaleString(
                'id-ID');

            const modal = document.getElementById('modalSetor');
            const content = document.getElementById('modalSetorContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function tutupModalSetor() {
            const modal = document.getElementById('modalSetor');
            const content = document.getElementById('modalSetorContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</x-app-layout>
