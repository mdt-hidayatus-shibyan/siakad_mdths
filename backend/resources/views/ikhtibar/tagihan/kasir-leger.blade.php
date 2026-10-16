@section('title', 'Pembayaran Leger KK - Ikhtibar')

<x-app-layout>
    <!-- HEADER & TAB MENU -->
    <div
        class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-4 relative z-20 print:hidden">

        <!-- Area Tab Menu -->
        <div class="w-full xl:w-auto shrink-0">
            @include('ikhtibar.tagihan.menu-kasir')
        </div>

        <!-- Filter Dusun & Tagihan -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET" id="formFilterLeger"
                class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto m3-glass-card p-2 shadow-2xs">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- 1. Filter Dusun / Kampung -->
                <div class="relative w-full sm:w-[220px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-geo-alt-fill text-xs"></i>
                    </div>
                    <select name="kampung_id" onchange="document.getElementById('formFilterLeger').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @foreach ($daftarKampung as $kp)
                            <option value="{{ $kp->id }}"
                                {{ (string) $selectedKampungId === (string) $kp->id ? 'selected' : '' }}>
                                {{ $kp->kode }} - {{ $kp->nama_kampung }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-5 bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>

                <!-- 2. Filter Tagihan KK -->
                <div class="relative w-full sm:w-[240px] h-10">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-tags-fill text-xs"></i>
                    </div>
                    <select name="pengaturan_tagihan_id" onchange="document.getElementById('formFilterLeger').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                        @forelse ($masterTagihans as $m)
                            <option value="{{ $m->id }}"
                                {{ (string) $selectedMasterId === (string) $m->id ? 'selected' : '' }}>
                                {{ $m->nama_tagihan }}
                            </option>
                        @empty
                            <option value="">-- Belum Ada Tarif Tagihan Ikhtibar --</option>
                        @endforelse
                    </select>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400 z-10">
                        <i class="bi bi-chevron-down text-[10px] font-black"></i>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ALERT FEEDBACK -->
    @if (session('success'))
        <div
            class="p-4 mb-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:opacity-70">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div
            class="p-4 mb-6 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
            <div class="flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:opacity-70">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
    @endif

    @if ($selectedKampungId && $selectedMaster)
        <!-- MATRIKS LEGER: KPI & ACTION BAR -->
        <div class="space-y-4">
            <!-- 1. Ringkasan Status Dusun -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
                <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                        Dusun / Kampung
                    </span>
                    <h3 class="text-lg font-black text-zinc-900 dark:text-white truncate">
                        {{ $kampungTerpilih->nama_kampung ?? 'Dusun' }}
                    </h3>
                    <span class="text-[10px] text-zinc-400 font-bold">Total: {{ $walis->count() }} KK Aktif</span>
                </div>

                <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                        Sudah Lunas
                    </span>
                    <h3 class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                        {{ $totalLunasCount }} KK
                    </h3>
                    <span class="text-[10px] text-zinc-400 font-bold">
                        Rp {{ number_format($totalLunasCount * $nominalTarif, 0, ',', '.') }}
                    </span>
                </div>

                <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                        Belum Lunas
                    </span>
                    <h3 class="text-lg font-black text-rose-600 dark:text-rose-400">
                        {{ $totalBelumLunasCount }} KK
                    </h3>
                    <span class="text-[10px] text-zinc-400 font-bold">
                        Rp {{ number_format($totalBelumLunasCount * $nominalTarif, 0, ',', '.') }}
                    </span>
                </div>

                <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                        Tarif Ikhtibar
                    </span>
                    <h3 class="text-lg font-black text-primary">
                        Rp {{ number_format($nominalTarif, 0, ',', '.') }}
                    </h3>
                    <span class="text-[10px] text-zinc-400 font-bold">Per Kepala Keluarga</span>
                </div>
            </div>

            <!-- 2. FORM PELUNASAN MASSAL VIA LEGER -->
            <form action="{{ route('tagihan-ikhtibar.kasir-leger.proses') }}" method="POST" id="formProsesLeger">
                @csrf
                <div class="m3-glass-card p-5 rounded-3xl shadow-2xs space-y-4">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-all text-xl text-primary"></i>
                            <div>
                                <h3 class="text-sm font-black text-zinc-900 dark:text-white">
                                    Daftar Kendali Leger Tagihan Ikhtibar
                                </h3>
                                <p class="text-[11px] text-zinc-500">
                                    Pilih satu atau beberapa KK yang membayar langsung kepada petugas di kampung.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                            <a href="{{ route('tagihan-ikhtibar.cetak-kartu-massal', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $selectedKampungId]) }}"
                                target="_blank"
                                class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500 text-amber-700 hover:text-white dark:text-amber-400 dark:hover:text-white border border-amber-500/20 text-xs font-black flex items-center gap-1.5 transition-all shadow-2xs active:scale-95">
                                <i class="bi bi-person-vcard"></i>
                                <span>Cetak Kartu Dusun Ini</span>
                            </a>
                            <a href="{{ route('tagihan-ikhtibar.cetak-leger', ['tahun_id' => $tahunPelajaranId, 'pengaturan_tagihan_id' => $selectedMaster->id, 'kampung_id' => $selectedKampungId]) }}"
                                target="_blank" class="m3-btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5">
                                <i class="bi bi-printer"></i>
                                <span>Cetak Buku Leger Dusun</span>
                            </a>
                        </div>
                    </div>

                    <!-- BAR AKSI MASSAL (MUNCUL JIKA ADA CHECKBOX TERPILIH) -->
                    <div id="barAksiLeger"
                        class="p-3.5 rounded-2xl bg-primary/10 border border-primary/20 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300">
                                Terpilih: <span id="countTerpilih" class="font-black text-primary">0</span> KK
                            </span>
                            <span class="text-zinc-400">•</span>
                            <span class="font-bold text-zinc-700 dark:text-zinc-300">
                                Total: <span id="nominalTerpilih" class="font-black text-primary">Rp 0</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <select name="metode_pembayaran" class="m3-input-glass text-xs font-bold py-1 px-2.5">
                                <option value="Tunai">💵 Tunai (Titip Petugas)</option>
                                <option value="Transfer">💳 Transfer</option>
                            </select>

                            <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}"
                                class="m3-input-glass text-xs font-bold py-1 px-2">

                            <button type="submit" id="btnSubmitLeger" disabled
                                class="m3-btn-primary text-xs px-4 py-1.5 font-black disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-check2-circle"></i> <span>Lunasi Terpilih</span>
                            </button>
                        </div>
                    </div>

                    <!-- TABEL LEGER -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr
                                    class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 uppercase tracking-wider text-[10px] font-black">
                                    <th class="py-2.5 px-3 w-10 text-center">
                                        <input type="checkbox" id="checkAllLeger"
                                            class="rounded border-zinc-300 text-primary focus:ring-primary h-4 w-4">
                                    </th>
                                    <th class="py-2.5 px-3">No. Reg / Kepala Keluarga</th>
                                    <th class="py-2.5 px-3">Santri Terdaftar</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                    <th class="py-2.5 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                @forelse ($walis as $wali)
                                    @php
                                        $tgh = $wali->current_tagihan;
                                        $isLunas = $tgh && $tgh->status_bayar === 'Lunas';
                                    @endphp
                                    <tr
                                        class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors {{ $isLunas ? 'bg-emerald-500/5' : '' }}">
                                        <td class="py-3 px-3 text-center">
                                            @if ($tgh && !$isLunas)
                                                <input type="checkbox" name="tagihan_ids[]"
                                                    value="{{ $tgh->id }}"
                                                    class="cb-leger rounded border-zinc-300 text-primary focus:ring-primary h-4 w-4"
                                                    data-nominal="{{ $tgh->nominal_tagihan }}">
                                            @elseif ($isLunas)
                                                <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                                            @else
                                                <span class="text-zinc-300 dark:text-zinc-700 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="font-black text-zinc-900 dark:text-white block">
                                                {{ $wali->nama_kepala_keluarga }}
                                            </span>
                                            <span class="text-[12px] text-zinc-400 font-mono">
                                                {{ $wali->no_registrasi ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="space-y-0.5">
                                                @forelse ($wali->murids as $anak)
                                                    <span
                                                        class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300 block">
                                                        • {{ $anak->nama_lengkap }}
                                                        ({{ $anak->ruangans->first()?->nama_ruangan ?? 'Kelas -' }})
                                                    </span>
                                                @empty
                                                    <span class="text-[10px] text-zinc-400 italic">Tidak ada murid
                                                        aktif</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 px-3 text-right font-mono font-black text-zinc-900 dark:text-white">
                                            Rp
                                            {{ number_format($tgh ? $tgh->nominal_tagihan : $nominalTarif, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if ($isLunas)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 block">
                                                    LUNAS
                                                </span>
                                                <span class="text-[9px] text-zinc-400 font-mono block mt-0.5">
                                                    {{ $tgh->pembayaranTagihan ? date('d/m/y', strtotime($tgh->pembayaranTagihan->tanggal_bayar)) : '' }}
                                                </span>
                                            @elseif ($tgh)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 block">
                                                    BELUM LUNAS
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-400 block">
                                                    BELUM TERBIT
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                @if ($isLunas)
                                                    <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $tgh->id) }}"
                                                        target="_blank"
                                                        class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors"
                                                        title="Cetak Kwitansi">
                                                        <i class="bi bi-printer text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('tagihan-ikhtibar.batal', $tgh->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Batalkan pembayaran tagihan ini?');"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                            title="Batal Bayar">
                                                            <i class="bi bi-arrow-counterclockwise text-xs"></i>
                                                        </button>
                                                    </form>
                                                @elseif ($tgh)
                                                    <a href="{{ route('tagihan-ikhtibar.kasir', ['tahun_id' => $tahunPelajaranId, 'search_wali' => $wali->no_registrasi]) }}"
                                                        class="p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors"
                                                        title="Bayar di Kasir">
                                                        <i class="bi bi-wallet2 text-xs"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('tagihan-ikhtibar.terbitkan-index', ['tahun_id' => $tahunPelajaranId, 'search' => $wali->nama_kepala_keluarga]) }}"
                                                        class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors"
                                                        title="Terbitkan Tagihan">
                                                        <i class="bi bi-plus-circle text-xs"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-zinc-400 italic">
                                            Tidak ada data Wali Murid aktif pada dusun ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- SCRIPT LEGER SELECTION -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAllLeger');
            const cbs = document.querySelectorAll('.cb-leger');
            const countDisplay = document.getElementById('countTerpilih');
            const nominalDisplay = document.getElementById('nominalTerpilih');
            const btnSubmit = document.getElementById('btnSubmitLeger');

            function syncSelection() {
                let count = 0;
                let totalNominal = 0;
                cbs.forEach(cb => {
                    if (cb.checked) {
                        count++;
                        totalNominal += parseFloat(cb.getAttribute('data-nominal') || 0);
                    }
                });

                if (countDisplay) countDisplay.textContent = count;
                if (nominalDisplay) nominalDisplay.textContent = 'Rp ' + totalNominal.toLocaleString('id-ID');
                if (btnSubmit) {
                    btnSubmit.disabled = (count === 0);
                }
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    cbs.forEach(cb => {
                        cb.checked = checkAll.checked;
                    });
                    syncSelection();
                });
            }

            cbs.forEach(cb => {
                cb.addEventListener('change', syncSelection);
            });

            syncSelection();
        });
    </script>
</x-app-layout>
