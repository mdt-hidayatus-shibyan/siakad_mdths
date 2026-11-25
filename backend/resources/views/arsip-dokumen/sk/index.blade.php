@section('title', 'Arsip SK Keputusan')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-20">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="bi bi-file-earmark-check-fill text-emerald-500"></i> Arsip Surat Keputusan (SK)
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Kelola dan telusuri Surat Keputusan Kenaikan Kelas, Kelulusan, dan Ujian Al-Qur'an yang telah disahkan.
            </p>
        </div>
    </div>

    <!-- FILTER & PENCARIAN -->
    <div class="m3-glass-card p-4 sm:p-5 mb-5 relative z-10 shadow-2xs">
        <form action="{{ route('arsip-sk.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2.5">

            <!-- Pencarian Teks -->
            <div class="flex-1 relative min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari Nama Murid, NISM, atau Nomor SK..."
                    class="m3-input-glass w-full !pl-9 text-xs font-bold">
            </div>

            <!-- Filter Jenis SK -->
            <div class="w-full lg:w-52 relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="bi bi-funnel-fill text-xs"></i>
                </div>
                <select name="jenis" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="text-zinc-500">Semua Jenis SK</option>
                    <option value="sk_keputusan" {{ request('jenis') === 'sk_keputusan' ? 'selected' : '' }}>
                        SK Kenaikan / Kelulusan
                    </option>
                    <option value="sk_alquran" {{ request('jenis') === 'sk_alquran' ? 'selected' : '' }}>
                        SK Ujian Al-Qur'an
                    </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </div>

            <!-- Filter Tahun Pelajaran -->
            <div class="w-full lg:w-48 relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="bi bi-calendar-event-fill text-xs"></i>
                </div>
                <select name="tahun_pelajaran" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="text-zinc-500">Semua Tahun</option>
                    @foreach ($daftarTahun as $thn)
                        @php $namaThn = $thn->nama_hijriyah . ' H - ' . $thn->nama_masehi . ' M'; @endphp
                        <option value="{{ $namaThn }}"
                            {{ request('tahun_pelajaran') == $namaThn ? 'selected' : '' }}>
                            {{ $thn->nama_hijriyah }} H | {{ $thn->nama_masehi }} M
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </div>

            <!-- Filter Ruangan -->
            <div class="w-full lg:w-48 relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="bi bi-door-open-fill text-xs"></i>
                </div>
                <select name="ruangan" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pl-9 !pr-8 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="text-zinc-500">Semua Ruang</option>
                    @foreach ($daftarRuangan as $rng)
                        <option value="{{ $rng->nama_ruangan }}"
                            {{ request('ruangan') == $rng->nama_ruangan ? 'selected' : '' }}>
                            {{ $rng->nama_ruangan }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </div>

            <!-- Tombol Cari -->
            <button type="submit"
                class="hidden lg:flex h-10 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-2xs transition-all active:scale-95 items-center justify-center outline-none">
                Saring
            </button>
        </form>
    </div>

    <!-- TABEL DATA ARSIP -->
    <div class="m3-glass-card overflow-hidden relative z-10 shadow-2xs">
        <div class="overflow-x-auto relative custom-scrollbar p-0">
            <table class="w-full text-left border-collapse text-xs min-w-[850px]">
                <thead class="bg-zinc-100/70 dark:bg-zinc-800/50 border-b border-zinc-200/80 dark:border-zinc-800">
                    <tr class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <th class="py-3 px-4 text-center border-r border-zinc-200/60 dark:border-zinc-800/60 w-14">No</th>
                        <th class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60 w-44">Tanggal Disahkan</th>
                        <th class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60">Informasi Surat Keputusan</th>
                        <th class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60 w-40 text-center">Disahkan Oleh</th>
                        <th class="py-3 px-4 text-center w-32">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/60">
                    @forelse ($arsips as $index => $arsip)
                        @php $data = $arsip->snapshot_data; @endphp
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">

                            <!-- Nomor -->
                            <td class="py-3 px-4 text-center font-black text-zinc-400 border-r border-zinc-200/60 dark:border-zinc-800/60 align-top">
                                {{ $arsips->firstItem() + $index }}
                            </td>

                            @php
                                $isSkAlquran = ($data['tipe_sk'] ?? '') === 'sk_alquran' || $arsip->tipe_dokumen === 'sk_alquran';
                                $keputusanText = $data['status_kelulusan'] ?? ($data['status_keputusan'] ?? ($data['keputusan'] ?? ($data['keputusan_final'] ?? 'Lulus/Naik')));
                                $isNegatif = in_array($keputusanText, ['Tidak Lulus', 'Tinggal Kelas']);
                            @endphp

                            <!-- Tanggal -->
                            <td class="py-3 px-4 border-r border-zinc-200/60 dark:border-zinc-800/60 align-top">
                                <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mb-1">
                                    {{ \Carbon\Carbon::parse($arsip->created_at)->translatedFormat('d F Y, H:i') }}
                                </p>
                                @if ($isSkAlquran)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 shadow-2xs">
                                        SK UJIAN AL-QUR'AN
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-2xs">
                                        SK KENAIKAN / KELULUSAN
                                    </span>
                                @endif
                            </td>

                            <!-- Informasi Dokumen -->
                            <td class="py-3 px-5 border-r border-zinc-200/60 dark:border-zinc-800/60 align-top">
                                <p class="font-black text-sm text-zinc-900 dark:text-white mb-0.5 uppercase tracking-tight">
                                    {{ $data['nama_murid'] ?? 'Tidak Diketahui' }}
                                </p>
                                <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                                    NISM: {{ $data['nism'] ?? '-' }} &bull; Ruang: {{ $data['nama_ruangan'] ?? '-' }} &bull; Keputusan: <span
                                        class="{{ $isNegatif ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} font-black">{{ $keputusanText }}</span>
                                    @if ($isSkAlquran && isset($data['nilai_akhir']))
                                        &bull; Nilai: <span class="font-black text-zinc-700 dark:text-zinc-300">{{ number_format($data['nilai_akhir'], 0) }}</span>
                                    @endif
                                </p>
                                <p
                                    class="text-[10px] font-bold text-zinc-400 mt-1 bg-zinc-100 dark:bg-zinc-800 inline-block px-1.5 py-0.5 rounded-lg border border-zinc-200/60 dark:border-zinc-700 shadow-2xs">
                                    <i class="bi bi-upc-scan mr-1"></i>{{ $data['nomor_dokumen'] ?? 'Tanpa Nomor' }}
                                </p>
                            </td>

                            <!-- Disahkan Oleh -->
                            <td class="py-3 px-4 text-center border-r border-zinc-200/60 dark:border-zinc-800/60 align-top">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark mb-1 border border-primary/20 shadow-2xs">
                                    <i class="bi bi-person-badge-fill text-xs font-black"></i>
                                </div>
                                <p
                                    class="text-[10px] font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-wider line-clamp-2">
                                    {{ $arsip->pencetak->name ?? 'Admin / Sistem' }}
                                </p>
                            </td>

                            <!-- Aksi Cetak SK -->
                            <td class="py-3 px-4 text-center align-middle">
                                <a href="{{ route('arsip.cetak', $arsip->id) }}" target="_blank"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 h-8 bg-zinc-900 hover:bg-black dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-black font-black text-[10px] uppercase tracking-wider rounded-xl transition-all active:scale-95 shadow-2xs outline-none">
                                    <i class="bi bi-printer-fill"></i> Lihat SK
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <x-empty-state icon="bi-file-earmark-x" title="Arsip SK Kosong" message="Belum ada Surat Keputusan Kenaikan/Kelulusan yang disahkan untuk filter ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($arsips->hasPages())
            <div class="px-5 py-4 border-t border-zinc-200/80 dark:border-zinc-800">
                {{ $arsips->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</x-app-layout>

