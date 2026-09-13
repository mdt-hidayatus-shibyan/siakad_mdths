@section('title', 'Pengaturan Tagihan')

<x-app-layout>
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Pengaturan Tagihan
            </h2>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Kelola data master dan tarif tagihan pembayaran.
            </p>
        </div>
        <div class="w-full md:w-auto shrink-0">
            <form action="{{ request()->url() }}" method="GET" class="m-0 relative m3-glass-card p-1.5 shadow-2xs">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-calendar-range text-xs"></i>
                </div>
                <select name="tahun_id" onchange="this.form.submit()"
                    class="m3-input-glass w-full sm:w-64 !pl-9 !pr-8 cursor-pointer appearance-none text-xs font-bold">
                    @foreach ($daftarTahun as $tahun)
                        <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                            {{ $tahun->nama_hijriyah }} | {{ $tahun->nama_masehi }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN GRID LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-10">

        <!-- KIRI: FORM TAMBAH -->
        <div class="lg:col-span-5 xl:col-span-4">
            <div class="m3-glass-card rounded-3xl p-5 lg:p-6 sticky top-24 shadow-2xs">

                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-zinc-200/80 dark:border-zinc-800">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0 text-emerald-600 dark:text-emerald-400">
                        <i class="bi bi-plus-lg text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-zinc-900 dark:text-white text-sm tracking-tight leading-tight">
                            Tambah Tarif
                        </h3>
                        <p class="text-[10px] font-semibold text-zinc-400 mt-0.5">Buat kriteria tagihan baru.</p>
                    </div>
                </div>

                <form action="{{ route('pengaturan-tagihan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranId }}">

                    <!-- Kode Tagihan -->
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Kode Tagihan
                        </label>
                        <input type="text" name="kode_tagihan" placeholder="Contoh: SPP, IMDA, IMNI, MLD"
                            value="{{ old('kode_tagihan') }}" maxlength="10"
                            class="m3-input-glass w-full text-xs font-bold @error('kode_tagihan') !border-rose-500 @enderror">
                        <p class="text-[9px] font-semibold text-zinc-400 mt-1 ml-1 flex items-start gap-1">
                            <i class="bi bi-info-circle text-zinc-400"></i>
                            Isi SPP, IMDA('1/2'), IMNI(1/2/3), MLD.
                        </p>
                        @error('kode_tagihan')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Nama Tagihan -->
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Nama Tagihan
                        </label>
                        <input type="text" name="nama_tagihan" placeholder="Contoh: SPP, Iuran Ujian..."
                            value="{{ old('nama_tagihan') }}"
                            class="m3-input-glass w-full text-xs font-bold @error('nama_tagihan') !border-rose-500 @enderror">

                        @error('nama_tagihan')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Pilihan Level / Kelas -->
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Peruntukan Kelas
                        </label>
                        <div
                            class="bg-white/40 dark:bg-black/40 border {{ $errors->has('level_ids') ? 'border-rose-500' : 'border-zinc-200/80 dark:border-zinc-800' }} rounded-2xl p-2 max-h-44 overflow-y-auto custom-scrollbar flex flex-col gap-1">
                            @foreach ($daftarLevel as $lvl)
                                <label
                                    class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors group">
                                    <input type="checkbox" name="level_ids[]" value="{{ $lvl->id }}"
                                        {{ is_array(old('level_ids')) && in_array($lvl->id, old('level_ids')) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded-lg border-zinc-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500/30 bg-white/50 dark:bg-black/50 cursor-pointer transition-colors">
                                    <span
                                        class="text-xs font-bold text-zinc-700 dark:text-zinc-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ $lvl->nama_level }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('level_ids')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @else
                            <p class="text-[9px] font-semibold text-zinc-400 mt-1 ml-1 flex items-start gap-1">
                                <i class="bi bi-info-circle text-zinc-400"></i>
                                Biarkan kosong jika tarif ini berlaku untuk Semua Jenjang.
                            </p>
                        @enderror
                    </div>

                    <!-- Sasaran Tagihan -->
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                            Sasaran Penagihan
                        </label>
                        <div class="relative">
                            <select name="sasaran"
                                class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none">
                                <option value="murid" {{ old('sasaran') == 'murid' ? 'selected' : '' }}>Per Murid /
                                    Santri</option>
                                <option value="wali_murid" {{ old('sasaran') == 'wali_murid' ? 'selected' : '' }}>Per
                                    Wali Murid (KK)</option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                <i class="bi bi-chevron-down text-[10px] font-black"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Tipe Tagihan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Tipe Penagihan
                            </label>
                            <div class="relative">
                                <select name="tipe"
                                    class="m3-input-glass w-full !pr-8 text-xs font-bold cursor-pointer appearance-none @error('tipe') !border-rose-500 @enderror">
                                    <option value="bulanan" {{ old('tipe') == 'bulanan' ? 'selected' : '' }}>Bulanan
                                        (Syahriyah)</option>
                                    <option value="semester" {{ old('tipe') == 'semester' ? 'selected' : '' }}>Per
                                        Semester (IMDA/IMNI)</option>
                                    <option value="insidental" {{ old('tipe') == 'insidental' ? 'selected' : '' }}>
                                        Sekali Bayar / Insidental</option>
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-[10px] font-black"></i>
                                </div>
                            </div>

                            @error('tipe')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Nominal -->
                        <div class="col-span-2">
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nominal (Rp)
                            </label>
                            <div class="relative flex items-center">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-zinc-400 pointer-events-none text-xs">Rp</span>
                                <input type="number" name="nominal" placeholder="0" min="0"
                                    value="{{ old('nominal') }}"
                                    class="m3-input-glass w-full !pl-9 text-xs font-bold text-zinc-900 dark:text-white @error('nominal') !border-rose-500 @enderror">
                            </div>

                            @error('nominal')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="m3-btn-primary w-full h-10 text-xs font-black shadow-2xs flex items-center justify-center gap-1.5">
                            <i class="bi bi-save2-fill text-xs"></i> <span>Simpan Tarif</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KANAN: DAFTAR TARIF -->
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-5">

            @php
                $groupedTagihan = collect($jenisTagihans)->groupBy('tipe');
                $labels = [
                    'bulanan' => [
                        'icon' => 'bi-calendar3-event',
                        'title' => 'Tagihan Rutin (Syahriyah/SPP)',
                        'theme' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                    ],
                    'semester' => [
                        'icon' => 'bi-book-half',
                        'title' => 'Tagihan Ujian Semester',
                        'theme' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                    ],
                    'insidental' => [
                        'icon' => 'bi-pin-angle-fill',
                        'title' => 'Tagihan Insidental / Sekali Bayar',
                        'theme' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                    ],
                ];
            @endphp

            @forelse($groupedTagihan as $tipe => $biayasTipe)
                @php
                    $conf = $labels[$tipe] ?? [
                        'icon' => 'bi-tag-fill',
                        'title' => 'Tipe Lainnya',
                        'theme' => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
                    ];

                    $groupedByLevel = $biayasTipe->groupBy(function ($item) {
                        return $item->level ? 'Khusus ' . $item->level->nama_level : 'Berlaku Semua Jenjang (Umum)';
                    });
                @endphp

                <div class="m3-glass-card rounded-3xl p-5 lg:p-6 shadow-2xs">

                    <!-- Header Group -->
                    <div class="flex items-center gap-3.5 mb-5 pb-3.5 border-b border-zinc-200/80 dark:border-zinc-800">
                        <div
                            class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 border {{ $conf['theme'] }}">
                            <i class="bi {{ $conf['icon'] }} text-base"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-zinc-900 dark:text-white text-base tracking-tight">
                                {{ $conf['title'] }}
                            </h3>
                            <p class="text-[10px] font-black text-zinc-400 uppercase tracking-wider mt-0.5">
                                Total: {{ count($biayasTipe) }} Item Tagihan
                            </p>
                        </div>
                    </div>

                    <!-- Daftar Item Tagihan per Level -->
                    <div class="flex flex-col gap-4">
                        @foreach ($groupedByLevel as $levelName => $biayas)
                            <div class="flex flex-col gap-2.5">

                                <!-- Label Level -->
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-[9px] font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-300 flex items-center shadow-2xs">
                                        <i class="bi bi-layers-half mr-1.5 opacity-60"></i> {{ $levelName }}
                                    </span>
                                    <div class="h-px flex-1 bg-zinc-200/80 dark:border-zinc-800"></div>
                                </div>

                                <!-- Cards -->
                                <div class="grid grid-cols-1 gap-2.5">
                                    @foreach ($biayas as $biaya)
                                        <div
                                            class="m3-glass-card p-3.5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs hover:border-emerald-500/30 transition-all duration-200">

                                            <!-- Info Tagihan -->
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <div
                                                    class="w-9 h-9 rounded-xl bg-white/50 dark:bg-black/50 border border-zinc-200 dark:border-zinc-700 text-zinc-500 flex items-center justify-center shrink-0 shadow-2xs">
                                                    <i class="bi bi-receipt-cutoff text-xs"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <h4
                                                        class="font-black text-zinc-900 dark:text-white text-xs truncate mb-0.5">
                                                        {{ $biaya->nama_tagihan }}
                                                    </h4>
                                                    <div class="flex items-center gap-1">
                                                        <span
                                                            class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400">
                                                            Kode : {{ ucwords($biaya->kode_tagihan) }} | Tarif
                                                            {{ ucwords($tipe) }}
                                                        </span>
                                                        @if ($biaya->sasaran === 'wali_murid')
                                                            <span
                                                                class="text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                                                Per KK / Wali
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Nominal & Aksi -->
                                            <div
                                                class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto pt-2.5 border-t sm:border-t-0 sm:pt-0 border-zinc-200/60 dark:border-zinc-800">
                                                <div class="text-left sm:text-right">
                                                    <p
                                                        class="text-[9px] font-black text-zinc-400 uppercase tracking-wider mb-0.5">
                                                        Nominal</p>
                                                    <p
                                                        class="font-black text-sm text-zinc-900 dark:text-white leading-none">
                                                        <span
                                                            class="text-[10px] font-bold text-zinc-400 mr-0.5">Rp</span>{{ number_format($biaya->nominal, 0, ',', '.') }}
                                                    </p>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div
                                                    class="flex items-center gap-1 shrink-0 bg-white/40 dark:bg-black/40 border border-zinc-200 dark:border-zinc-700 p-1 rounded-xl shadow-2xs">

                                                    @can('update pengaturan-tagihan')
                                                        <a href="{{ route('pengaturan-tagihan.edit', $biaya->id) }}"
                                                            class="action-modal w-7 h-7 rounded-lg text-amber-500 hover:bg-amber-500/10 flex items-center justify-center transition-colors outline-none"
                                                            title="Koreksi Tarif">
                                                            <i class="bi bi-pencil-fill text-[10px]"></i>
                                                        </a>
                                                    @endcan

                                                    @can('delete pengaturan-tagihan')
                                                        <form
                                                            action="{{ route('pengaturan-tagihan.destroy', $biaya->id) }}"
                                                            method="POST" class="m-0 delete-ajax">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="w-7 h-7 rounded-lg text-rose-500 hover:bg-rose-500/10 flex items-center justify-center transition-colors outline-none"
                                                                title="Hapus Tarif">
                                                                <i class="bi bi-trash3-fill text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                    @endcan

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state icon="bi-tags" title="Belum Ada Data Tarif"
                        message="Anda belum mengatur kriteria tarif biaya pendidikan untuk tahun ajaran yang dipilih." />
                </div>
            @endforelse
        </div>
    </div>

</x-app-layout>
