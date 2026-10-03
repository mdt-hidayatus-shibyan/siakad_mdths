@section('title', 'Ruangan IMNI')

<x-app-layout>
    <div>
        <!-- 1. HEADER SECTION -->
        <div
            class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Ruangan IMNI
                </h2>
                <p class="text-xs md:text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Kelola master ruangan ujian IMNI (R1, R2, dst) dan relasinya ke master ruangan fisik madrasah.
                </p>
            </div>

            <!-- Toolbar Aksi -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tahun Pelajaran -->
                <form action="{{ route('ruangan-imni.index') }}" method="GET" id="formTahunRuangan" class="m-0">
                    <div class="relative">
                        <select name="tahun_id" onchange="document.getElementById('formTahunRuangan').submit()"
                            class="pl-9 pr-8 py-2 text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th->id }}" {{ $selectedTahunId == $th->id ? 'selected' : '' }}>
                                    {{ $th->nama_hijriyah }} H ({{ $th->nama_masehi }} M)
                                    {{ $th->is_active ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="bi bi-calendar-range absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs pointer-events-none"></i>
                    </div>
                </form>



                <!-- Tombol Tambah Ruangan IMNI -->
                <a href="{{ route('ruangan-imni.modal-tambah-ruangan', ['tahun_id' => $selectedTahunId]) }}"
                    class="action-modal px-4 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black flex items-center gap-1.5 shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <i class="bi bi-plus-lg text-sm"></i>
                    <span>Tambah Ruangan IMNI</span>
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

        <!-- 3. KOTAK INFORMASI RUMUS KEBUTUHAN RUANGAN -->
        <div
            class="mb-6 m3-glass-card p-4 rounded-2xl md:rounded-3xl border border-indigo-500/20 bg-indigo-500/5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <i class="bi bi-calculator-fill text-indigo-600"></i>
                    <h4 class="text-xs font-black uppercase tracking-wider text-indigo-900 dark:text-indigo-200">
                        Perhitungan Kebutuhan Ruangan (6 IBT & 3 TSA)
                    </h4>
                </div>
                <div class="flex items-center gap-3 text-xs font-bold text-zinc-600 dark:text-zinc-300 flex-wrap">
                    <span>6 IBT: <strong class="text-orange-600 font-mono">{{ $pesertaIbtCount }}</strong> Murid</span>
                    <span>•</span>
                    <span>3 TSA: <strong class="text-blue-600 font-mono">{{ $pesertaTsaCount }}</strong> Murid</span>
                    <span>•</span>
                    <span class="text-indigo-700 dark:text-indigo-300">Total Gabungan: <strong
                            class="font-mono">{{ $totalGabungan }}</strong> Murid</span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-mono font-bold text-zinc-600 dark:text-zinc-300">
                    Rumus: ⌈ {{ $totalGabungan }} / 20 ⌉ =
                </span>
                <span class="px-2.5 py-1 rounded-xl bg-indigo-600 text-white font-mono font-black text-xs shadow-xs">
                    {{ max(1, (int) ceil($totalGabungan / 20)) }} Ruangan
                    (R1..R{{ max(1, (int) ceil($totalGabungan / 20)) }})
                </span>
            </div>
        </div>

        <!-- 4. TABEL CRUD RUANGAN IMNI -->
        <div id="data-table-container"
            class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800">
            <!-- Table Header -->
            <div
                class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <h4 class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                        Daftar Ruangan IMNI
                    </h4>
                </div>

                <div class="text-xs font-bold text-zinc-400 font-mono">
                    Total: <strong class="text-zinc-700 dark:text-zinc-200">{{ $daftarRuanganImni->count() }}</strong>
                    Ruangan
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[10px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-14">No.</th>
                            <th class="py-3.5 px-4">Nama Ruangan IMNI</th>
                            <th class="py-3.5 px-4">Relasi Ruangan Fisik</th>
                            <th class="py-3.5 px-4">Penanggung Jawab (Panitia IMNI)</th>
                            <th class="py-3.5 px-4">Tahun Pelajaran</th>
                            <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse ($daftarRuanganImni as $index => $rg)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors">
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-zinc-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-black text-sm text-indigo-600 dark:text-indigo-400 font-mono">
                                        {{ $rg->nama_ruangan_imni ?: $rg->nama_ruangan }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($rg->ruanganFisik)
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-7 h-7 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs text-zinc-500">
                                                <i class="bi bi-building"></i>
                                            </span>
                                            <div>
                                                <h5 class="font-bold text-zinc-900 dark:text-white text-xs">
                                                    {{ $rg->ruanganFisik->nama_ruangan }}
                                                </h5>
                                                <p class="text-[10px] text-zinc-400">
                                                    Kelas: {{ $rg->ruanganFisik->level?->nama_level ?? '-' }}
                                                    ({{ $rg->ruanganFisik->level?->tingkat?->kode_tingkat ?? 'MDT' }})
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">- Belum Ditautkan -</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($rg->penanggungJawabRuangan)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-7 h-7 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black shrink-0 border border-indigo-500/20">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-zinc-900 dark:text-white text-xs">
                                                    {{ $rg->penanggungJawabRuangan->ustadz?->nama_lengkap ?? '-' }}
                                                </h5>
                                                <p class="text-[10px] text-zinc-400">
                                                    <span
                                                        class="inline-block px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-[9px] uppercase tracking-wider">
                                                        {{ $rg->penanggungJawabRuangan->jabatan }}
                                                    </span>
                                                    <span class="font-mono text-zinc-400 text-[10px] ml-1">NIGM:
                                                        {{ $rg->penanggungJawabRuangan->ustadz?->nigm ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">- Belum Ditentukan -</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-zinc-600 dark:text-zinc-300">
                                    {{ $rg->tahunPelajaran?->nama_hijriyah }} H
                                    ({{ $rg->tahunPelajaran?->nama_masehi }} M)
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Edit -->
                                        <a href="{{ route('ruangan-imni.modal-edit-ruangan', $rg->id) }}"
                                            class="action-modal px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all cursor-pointer flex items-center gap-1"
                                            title="Edit Ruangan">
                                            <i class="bi bi-pencil-square text-xs"></i>
                                            <span>Edit</span>
                                        </a>

                                        <!-- Tombol Hapus (delete-ajax) -->
                                        <form action="{{ route('ruangan-imni.destroy-ruangan', $rg->id) }}"
                                            method="POST" class="delete-ajax inline m-0 p-0"
                                            data-refresh-target="#data-table-container">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1 rounded-xl bg-rose-500/10 hover:bg-rose-600 hover:text-white text-rose-600 text-xs font-bold transition-all cursor-pointer flex items-center gap-1"
                                                title="Hapus Ruangan">
                                                <i class="bi bi-trash3 text-xs"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-zinc-400">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-2 text-xl text-zinc-400">
                                        <i class="bi bi-door-closed"></i>
                                    </div>
                                    <h5 class="font-black text-zinc-700 dark:text-zinc-300 text-sm">Belum Ada Ruangan
                                        IMNI</h5>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        Silakan klik tombol <strong>"+ Tambah Ruangan IMNI"</strong> untuk membuat
                                        ruangan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
