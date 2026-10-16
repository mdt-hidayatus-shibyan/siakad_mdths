@section('title', 'Susunan Kepanitiaan Ikhtibar')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light border border-primary/20">
                    {{ $kegiatan->ikhtibar_ke ?? 'Agenda Tahunan' }}
                </span>
                <span class="text-xs text-zinc-400">SK: {{ $kegiatan->no_sk_panitia ?: 'Belum Ditetapkan' }}</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Susunan Kepanitiaan Ikhtibar
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Kelola personel BPH (Ketua, Sekretaris, Bendahara) dan Divisi Kerja baik dari unsur Ustadz maupun Eksternal.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            <!-- Filter Form -->
            <form action="{{ route('panitia-ikhtibar.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <!-- Dropdown Tahun Pelajaran -->
                <div class="relative group/select w-full sm:w-44">
                    <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} H | {{ $tp->nama_masehi }} M
                                {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Kelompok -->
                <div class="relative group/select w-full sm:w-32">
                    <select name="kelompok_jabatan" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="">Semua</option>
                        <option value="BPH" {{ request('kelompok_jabatan') === 'BPH' ? 'selected' : '' }}>BPH Inti</option>
                        <option value="Divisi" {{ request('kelompok_jabatan') === 'Divisi' ? 'selected' : '' }}>Divisi</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Input Search -->
                <div class="relative w-full sm:w-44">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                        class="m3-input-glass w-full text-xs !pl-8">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Cetak SK -->
                <a href="{{ route('panitia-ikhtibar.cetak-sk', $kegiatan->id) }}" target="_blank"
                    class="m3-btn-secondary text-xs flex items-center gap-1.5 py-2 px-3 shrink-0" title="Cetak SK Kepanitiaan">
                    <i class="bi bi-printer-fill text-sm"></i>
                    <span class="hidden sm:inline">SK Panitia</span>
                </a>

                <!-- Cetak ID Card -->
                <a href="{{ route('panitia-ikhtibar.cetak-id-card', $kegiatan->id) }}" target="_blank"
                    class="m3-btn-secondary text-xs flex items-center gap-1.5 py-2 px-3 shrink-0" title="Cetak ID Card Semua Panitia">
                    <i class="bi bi-person-badge-fill text-sm"></i>
                    <span class="hidden sm:inline">ID Card</span>
                </a>

                <!-- Tambah Panitia -->
                <a href="{{ route('panitia-ikhtibar.create', $kegiatan->id) }}"
                    class="m3-btn-primary text-xs action-modal flex items-center gap-1.5 py-2 px-3 shrink-0"
                    data-modal-size="lg">
                    <i class="bi bi-person-plus-fill text-sm"></i>
                    <span>Tambah Panitia</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Panitia (4 Grid Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 relative z-10">
        <!-- 1. Ketua Panitia -->
        @php
            $ketua = $panitiaList->where('jabatan', 'Ketua')->first();
            $sekretaris = $panitiaList->where('jabatan', 'Sekretaris')->first();
            $bendahara = $panitiaList->where('jabatan', 'Bendahara')->first();
        @endphp
        <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold border border-amber-500/20 shrink-0">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">
                        Ketua Panitia
                    </span>
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate" title="{{ $ketua?->nama_tampil ?? 'Belum Ditentukan' }}">
                        {{ $ketua?->nama_tampil ?? 'Belum Ditentukan' }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $ketua?->identitas_tampil ?? 'Pengarah & Penanggung Jawab' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Sekretaris Panitia -->
        <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-blue-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-bold border border-blue-500/20 shrink-0">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-wider block">
                        Sekretaris Panitia
                    </span>
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate" title="{{ $sekretaris?->nama_tampil ?? 'Belum Ditentukan' }}">
                        {{ $sekretaris?->nama_tampil ?? 'Belum Ditentukan' }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $sekretaris?->identitas_tampil ?? 'Persuratan, SK & Dokumen' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Bendahara Panitia -->
        <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-emerald-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold border border-emerald-500/20 shrink-0">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">
                        Bendahara Panitia
                    </span>
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate" title="{{ $bendahara?->nama_tampil ?? 'Belum Ditentukan' }}">
                        {{ $bendahara?->nama_tampil ?? 'Belum Ditentukan' }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $bendahara?->identitas_tampil ?? 'Tagihan KK & Pagu Divisi' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Komposisi Personel -->
        <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-purple-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold border border-purple-500/20 shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-wider block">
                        Total Kepanitiaan
                    </span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white">
                        {{ $panitiaList->count() }} <span class="text-xs font-semibold text-zinc-500">Personel</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $ustadzCount }} Ustadz • {{ $eksternalCount }} Eksternal
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="relative z-10">
        @include('ikhtibar.panitia.list', ['panitiaList' => $panitiaList])
    </div>
</x-app-layout>

