@section('title', 'Penerbitan Tagihan Iuran Ikhtibar')

<x-app-layout>
    <div class="space-y-6">
        <!-- HEADER & TAB MENU -->
        <div class="flex flex-col xl:flex-row xl:items-start justify-between gap-4 relative z-10 print:hidden">
            <!-- Area Tab Menu -->
            <div class="w-full xl:w-auto shrink-0">
                @include('ikhtibar.tagihan.menu-kasir')
            </div>

            <!-- Header Info -->
            <div class="text-right hidden xl:block">
                <span class="text-[10px] font-black uppercase text-primary tracking-wider block">
                    {{ $kegiatan->nama_kegiatan }} ({{ $kegiatan->ikhtibar_ke ?: '52 Th' }})
                </span>
                <span class="text-xs font-bold text-zinc-400">
                    Manajemen Penerbitan Tagihan KK
                </span>
            </div>
        </div>

        <!-- ALERT FEEDBACK -->
        @if (session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
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
                class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between text-xs font-bold animate-in fade-in duration-200">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:opacity-70">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        @endif

        <!-- FILTER BAR -->
        <div
            class="m3-glass-card p-4 rounded-3xl space-y-3 bg-white/70 dark:bg-zinc-900/70 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
            <form method="GET" action="{{ route('tagihan-ikhtibar.terbitkan-index') }}" id="filterForm"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

                <!-- 1. Tahun Pelajaran -->
                <div class="lg:col-span-3">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Tahun Pelajaran
                    </label>
                    <div class="relative">
                        <select name="tahun_id" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            @foreach ($daftarTahun as $tp)
                                <option value="{{ $tp->id }}"
                                    {{ (string) $tahunPelajaranId === (string) $tp->id ? 'selected' : '' }}>
                                    {{ $tp->nama_hijriyah }} ({{ $tp->nama_masehi }})
                                    {{ $tp->is_active ? '★ Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- 2. Master Tagihan Ikhtibar -->
                <div class="lg:col-span-3">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Jenis Tagihan KK (Ikhtibar)
                    </label>
                    <div class="relative">
                        <select name="pengaturan_tagihan_id" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            @forelse ($masterTagihans as $mt)
                                <option value="{{ $mt->id }}"
                                    {{ (string) $selectedMasterId === (string) $mt->id ? 'selected' : '' }}>
                                    {{ $mt->nama_tagihan }} — Rp {{ number_format($mt->nominal, 0, ',', '.') }}
                                </option>
                            @empty
                                <option value="">(Belum ada master tarif sasaran Wali Murid)</option>
                            @endforelse
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- 3. Filter Zonasi Dusun -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Zonasi Dusun
                    </label>
                    <div class="relative">
                        <select name="kampung_id" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Dusun</option>
                            @foreach ($daftarKampung as $kp)
                                <option value="{{ $kp->id }}"
                                    {{ (string) $selectedKampungId === (string) $kp->id ? 'selected' : '' }}>
                                    {{ $kp->kode }} - {{ $kp->nama_kampung }}

                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- 4. Filter Status Terbit -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Status Terbit
                    </label>
                    <div class="relative">
                        <select name="status_terbit" onchange="this.form.submit()"
                            class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                            <option value="">Semua Status</option>
                            <option value="Belum Terbit"
                                {{ request('status_terbit') === 'Belum Terbit' ? 'selected' : '' }}>
                                Belum Terbit
                            </option>
                            <option value="Sudah Terbit"
                                {{ request('status_terbit') === 'Sudah Terbit' ? 'selected' : '' }}>
                                Sudah Terbit
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- 5. Pencarian -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Pencarian
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Ketik Nama KK / NIK..." class="m3-input-glass w-full !pr-8 text-xs font-bold">
                        <button type="submit"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-zinc-400 hover:text-primary">
                            <i class="bi bi-search text-xs"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- KPI STATUS CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Wali Murid Aktif
                </span>
                <h3 class="text-xl font-black text-zinc-900 dark:text-white">
                    {{ number_format($totalWaliAktif, 0, ',', '.') }} KK
                </h3>
                <span class="text-[10px] text-zinc-400">Total KK memiliki santri aktif</span>
            </div>

            <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Sudah Diterbitkan
                </span>
                <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                    {{ number_format($totalSudahTerbitCount, 0, ',', '.') }} KK
                </h3>
                <span class="text-[10px] text-zinc-400">Siap dilakukan penarikan</span>
            </div>

            <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Belum Diterbitkan
                </span>
                <h3 class="text-xl font-black text-amber-500">
                    {{ number_format($totalBelumTerbitCount, 0, ',', '.') }} KK
                </h3>
                <span class="text-[10px] text-zinc-400">Perlu proses penerbitan</span>
            </div>

            <div class="m3-glass-card p-4 rounded-3xl shadow-2xs">
                <span class="text-[10px] font-black text-zinc-400 uppercase tracking-wider block mb-1">
                    Tarif Tagihan Ikhtibar
                </span>
                <h3 class="text-xl font-black text-primary">
                    Rp {{ number_format($nominalTarif, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-zinc-400">Per KK / Wali Murid</span>
            </div>
        </div>

        @if ($selectedMaster)
            <!-- TABEL PENERBITAN & ACTION FORM -->
            <form action="{{ route('tagihan-ikhtibar.terbitkan') }}" method="POST" id="formPenerbitanMassal">
                @csrf
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">
                <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">

                <div class="m3-glass-card p-5 rounded-3xl shadow-2xs space-y-4">
                    <!-- Action Header Bar -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-zinc-700 dark:text-zinc-300">
                                Pilihan: <span id="countPilihTerbit" class="font-black text-primary">0</span> KK
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="submit" id="btnTerbitkanTerpilih" disabled
                                class="m3-btn-primary text-xs px-4 py-1.5 font-black disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-plus-circle"></i> <span>Terbitkan Terpilih</span>
                            </button>

                            <button type="button" onclick="submitTerbitkanSemua()"
                                class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-2xs transition-all">
                                <i class="bi bi-lightning-charge-fill"></i> <span>Terbitkan Semua KK Aktif</span>
                            </button>

                            <button type="button" onclick="submitCetakKartuSmart()"
                                class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-black shadow-2xs flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer"
                                title="Cetak kartu tagihan untuk KK yang dicentang atau dusun terpilih">
                                <i class="bi bi-person-vcard text-xs"></i>
                                <span>Cetak Kartu Tagihan</span>
                            </button>

                            <button type="button" onclick="submitHapusSemua()"
                                class="px-3.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-xs font-bold transition-all">
                                <i class="bi bi-trash"></i> <span>Hapus Tagihan Belum Lunas</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Daftar KK -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr
                                    class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 uppercase tracking-wider text-[10px] font-black">
                                    <th class="py-2.5 px-3 w-10 text-center">
                                        <input type="checkbox" id="checkAllTerbit"
                                            class="rounded border-zinc-300 text-primary focus:ring-primary h-4 w-4">
                                    </th>
                                    <th class="py-2.5 px-3">No. Reg / Kepala Keluarga</th>
                                    <th class="py-2.5 px-3">Dusun / Kampung</th>
                                    <th class="py-2.5 px-3">Santri Aktif</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                    <th class="py-2.5 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                @forelse ($walis as $wali)
                                    @php
                                        $tgh = $wali->current_tagihan;
                                        $isTerbit = (bool) $tgh;
                                        $isLunas = $tgh && $tgh->status_bayar === 'Lunas';
                                    @endphp
                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                        <td class="py-3 px-3 text-center">
                                            @if (!$isTerbit)
                                                <input type="checkbox" name="wali_ids[]" value="{{ $wali->id }}"
                                                    data-wali-id="{{ $wali->id }}"
                                                    class="cb-terbit rounded border-zinc-300 text-primary focus:ring-primary h-4 w-4">
                                            @elseif (!$isLunas)
                                                <input type="checkbox" name="tagihan_hapus_ids[]"
                                                    value="{{ $tgh->id }}" data-wali-id="{{ $wali->id }}"
                                                    class="cb-terbit-hapus rounded border-zinc-300 text-rose-500 focus:ring-rose-500 h-4 w-4">
                                            @else
                                                <input type="checkbox" data-wali-id="{{ $wali->id }}"
                                                    class="cb-terbit-lunas rounded border-zinc-300 text-emerald-500 focus:ring-emerald-500 h-4 w-4"
                                                    title="Centang untuk cetak kartu (Sudah Lunas)">
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="font-black text-zinc-900 dark:text-white block">
                                                {{ $wali->nama_kepala_keluarga }}
                                            </span>
                                            <span class="text-[10px] text-zinc-400 font-mono">
                                                Reg: {{ $wali->no_registrasi ?? '-' }} • WA: {{ $wali->no_hp ?: '-' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span
                                                class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                                {{ $wali->kampung?->nama_kampung ?? 'Dusun -' }}
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
                                            Rp {{ number_format($nominalTarif, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if ($isLunas)
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    LUNAS
                                                </span>
                                            @elseif ($isTerbit)
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                                                    SUDAH TERBIT
                                                </span>
                                            @else
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700">
                                                    BELUM TERBIT
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <!-- Cetak Kartu Tagihan KK -->
                                                <a href="{{ route('tagihan-ikhtibar.cetak-kartu', [$wali->id, $tahunPelajaranId]) }}"
                                                    target="_blank"
                                                    class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors"
                                                    title="Cetak Kartu Tagihan KK {{ $wali->nama_kepala_keluarga }}">
                                                    <i class="bi bi-person-vcard text-xs"></i>
                                                </a>

                                                @if (!$isTerbit)
                                                    <form action="{{ route('tagihan-ikhtibar.terbitkan') }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="tahun_id"
                                                            value="{{ $tahunPelajaranId }}">
                                                        <input type="hidden" name="pengaturan_tagihan_id"
                                                            value="{{ $selectedMaster->id }}">
                                                        <input type="hidden" name="wali_ids[]"
                                                            value="{{ $wali->id }}">
                                                        <button type="submit"
                                                            class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors"
                                                            title="Terbitkan Tagihan Ini">
                                                            <i class="bi bi-plus-circle text-xs"></i>
                                                        </button>
                                                    </form>
                                                @elseif (!$isLunas)
                                                    <form action="{{ route('tagihan-ikhtibar.destroy', $tgh->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Hapus tagihan yang belum lunas ini?');"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                            title="Hapus Tagihan">
                                                            <i class="bi bi-trash text-xs"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('tagihan-ikhtibar.cetak-kwitansi', $tgh->id) }}"
                                                        target="_blank"
                                                        class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors"
                                                        title="Cetak Kwitansi">
                                                        <i class="bi bi-printer text-xs"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-zinc-400 italic">
                                            Tidak ada data Wali Murid aktif yang sesuai dengan kriteria filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            <!-- FORM HAPUS SEMUA HIDDEN -->
            <form action="{{ route('tagihan-ikhtibar.hapus-semua') }}" method="POST" id="formHapusSemua"
                class="hidden">
                @csrf
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">
                <input type="hidden" name="pengaturan_tagihan_id" value="{{ $selectedMaster->id }}">
            </form>
        @else
            <div class="m3-glass-card p-8 rounded-3xl text-center space-y-3">
                <i class="bi bi-exclamation-circle text-3xl text-amber-500"></i>
                <h3 class="text-base font-black text-zinc-900 dark:text-white">
                    Belum Ada Master Tarif Tagihan Ikhtibar
                </h3>
                <p class="text-xs text-zinc-500 max-w-md mx-auto">
                    Silakan tautkan master pengaturan tagihan iuran ikhtibar terlebih dahulu pada modul Pengaturan
                    Tagihan / Master Ikhtibar.
                </p>
            </div>
        @endif
    </div>

    <!-- SCRIPT TERBITKAN SELECTION -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAllTerbit');
            const cbs = document.querySelectorAll('.cb-terbit');
            const countDisplay = document.getElementById('countPilihTerbit');
            const btnSubmit = document.getElementById('btnTerbitkanTerpilih');

            function syncCount() {
                let count = 0;
                cbs.forEach(cb => {
                    if (cb.checked) count++;
                });
                if (countDisplay) countDisplay.textContent = count;
                if (btnSubmit) btnSubmit.disabled = (count === 0);
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    cbs.forEach(cb => {
                        cb.checked = checkAll.checked;
                    });
                    syncCount();
                });
            }

            cbs.forEach(cb => {
                cb.addEventListener('change', syncCount);
            });

            syncCount();
        });

        function submitTerbitkanSemua() {
            if (confirm(
                    'Terbitkan tagihan Ikhtibar untuk SEMUA Wali Murid Aktif yang belum memiliki tagihan pada tahun ini?'
                    )) {
                const form = document.getElementById('formPenerbitanMassal');
                // Uncheck semua agar backend menerbitkan ke seluruh KK
                document.querySelectorAll('.cb-terbit').forEach(cb => cb.checked = false);
                form.submit();
            }
        }

        function submitHapusSemua() {
            if (confirm(
                    'PERINGATAN: Seluruh tagihan Ikhtibar yang BELUM LUNAS pada tahun pelajaran ini akan dihapus. Lanjutkan?'
                    )) {
                document.getElementById('formHapusSemua').submit();
            }
        }

        function submitCetakKartuSmart() {
            const checkedBoxes = Array.from(document.querySelectorAll(
                '.cb-terbit:checked, .cb-terbit-hapus:checked, .cb-terbit-lunas:checked'));
            let url = new URL("{{ route('tagihan-ikhtibar.cetak-kartu-massal') }}", window.location.origin);
            url.searchParams.append('tahun_id', "{{ $tahunPelajaranId }}");
            if ("{{ $selectedMaster->id ?? '' }}") {
                url.searchParams.append('pengaturan_tagihan_id', "{{ $selectedMaster->id }}");
            }
            if ("{{ $selectedKampungId }}") {
                url.searchParams.append('kampung_id', "{{ $selectedKampungId }}");
            }
            const statusTerbit = "{{ request('status_terbit') }}";
            if (statusTerbit) {
                url.searchParams.append('status_terbit', statusTerbit);
            }

            if (checkedBoxes.length > 0) {
                checkedBoxes.forEach(cb => {
                    const wid = cb.getAttribute('data-wali-id');
                    if (wid) {
                        url.searchParams.append('wali_ids[]', wid);
                    }
                });
            }

            window.open(url.toString(), '_blank');
        }
    </script>
</x-app-layout>
