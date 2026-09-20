@section('title', 'Versi & Rilis Aplikasi')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('pengaturan-versi.riwayat', ['app' => $appType]) }}"
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    <span>Riwayat Versi</span>
                </a>
                <span class="text-xs text-zinc-400">•</span>
                <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    {{ isset($isNew) && $isNew ? 'Tambah Versi Baru' : 'Editor Versi' }}
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                {{ isset($isNew) && $isNew ? 'Tambah Versi & Catatan Rilis' : 'Pengaturan Versi & Rilis' }}
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Kelola nomor versi, catatan rilis pembaruan (*changelog*), dan informasi pengembang aplikasi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('pengaturan-versi.riwayat', ['app' => $appType]) }}"
                class="px-4 py-2.5 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs transition-all flex items-center gap-2 border border-zinc-200/80 dark:border-zinc-700/80">
                <i class="bi bi-clock-history text-sm"></i>
                <span>Lihat Riwayat Versi</span>
            </a>

            <!-- Tab Pemilih Platform -->
            <div
                class="flex items-center gap-1.5 p-1 bg-zinc-200/60 dark:bg-zinc-800/80 rounded-2xl border border-zinc-300/50 dark:border-zinc-700/50">
                <a href="{{ route('pengaturan-versi.index', ['app' => 'ustadz']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 {{ $appType === 'ustadz' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Ustadz</span>
                </a>
                <a href="{{ route('pengaturan-versi.index', ['app' => 'murid']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 {{ $appType === 'murid' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Murid</span>
                </a>
                <a href="{{ route('pengaturan-versi.index', ['app' => 'web']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 {{ $appType === 'web' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    <i class="bi bi-globe2"></i>
                    <span>Web</span>
                </a>
            </div>
        </div>
    </div>

    <!-- NOTIFIKASI SUCCESS -->
    @if (session('success'))
        <div
            class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center gap-3 text-xs font-bold">
            <i class="bi bi-check-circle-fill text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('pengaturan-versi.update') }}" method="POST" class="relative z-10">
        @csrf
        <input type="hidden" name="app_type" value="{{ $appType }}">
        <input type="hidden" name="id" value="{{ $version->id ?? '' }}">

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 md:gap-6">

            <!-- KOLOM KIRI (LEBAR) -->
            <div class="xl:col-span-8 flex flex-col gap-5 md:gap-6">

                <!-- CARD 1: INFORMASI VERSI AKTIF -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight mb-5 flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mr-2.5 text-emerald-600 dark:text-emerald-400 text-sm shrink-0">
                                <i class="bi bi-phone"></i>
                            </div>
                            Identitas Versi & Rilis
                        </div>
                        <span
                            class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                            Aktif di Mobile
                        </span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nomor Versi (Semantic) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="version"
                                value="{{ old('version', $version->version ?? '1.0.0') }}" placeholder="Contoh: 1.0.0"
                                class="m3-input-glass w-full text-xs font-bold" required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Kode / Build Number <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="build_number"
                                value="{{ old('build_number', $version->build_number ?? '2026.09') }}"
                                placeholder="Contoh: 2026.09" class="m3-input-glass w-full text-xs font-bold" required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Tanggal / Periode Rilis <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="release_date"
                                value="{{ old('release_date', $version->release_date ?? 'September 2026') }}"
                                placeholder="Contoh: September 2026" class="m3-input-glass w-full text-xs font-bold"
                                required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Subtitle Rilis
                            </label>
                            <input type="text" name="release_subtitle"
                                value="{{ old('release_subtitle', $version->release_subtitle ?? 'Rilis Perdana • September 2026') }}"
                                placeholder="Contoh: Rilis Perdana • September 2026"
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Badge Status Rilis
                            </label>
                            <input type="text" name="release_badge"
                                value="{{ old('release_badge', $version->release_badge ?? 'Rilis Saat Ini') }}"
                                placeholder="Contoh: Rilis Saat Ini" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Status Badge Versi
                            </label>
                            <input type="text" name="status_badge"
                                value="{{ old('status_badge', $version->status_badge ?? 'Versi Terbaru') }}"
                                placeholder="Contoh: Versi Terbaru" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nama Header Aplikasi
                            </label>
                            <input type="text" name="app_title"
                                value="{{ old('app_title', $version->app_title ?? ($appType === 'ustadz' ? 'Ustadz - MDTHS' : 'Murid - MDTHS')) }}"
                                placeholder="Contoh: Ustadz - MDTHS" class="m3-input-glass w-full text-xs font-bold"
                                required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Subtitle Lembaga
                            </label>
                            <input type="text" name="app_subtitle"
                                value="{{ old('app_subtitle', $version->app_subtitle ?? 'MDT Hidayatus Shibyan') }}"
                                placeholder="MDT Hidayatus Shibyan" class="m3-input-glass w-full text-xs font-bold"
                                required>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: DAFTAR FITUR BARU -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <div
                        class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5 mb-5">
                        <h3 class="font-black text-zinc-900 dark:text-white text-base tracking-tight flex items-center">
                            <div
                                class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mr-2.5 text-emerald-600 dark:text-emerald-400 text-sm shrink-0">
                                <i class="bi bi-plus-circle-fill"></i>
                            </div>
                            Penambahan Fitur Baru
                        </h3>
                        <button type="button" onclick="addFeatureRow()"
                            class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-black transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="bi bi-plus-lg"></i>
                            Tambah Fitur
                        </button>
                    </div>

                    <div id="features-container" class="flex flex-col gap-3.5">
                        @php
                            $features = old('feature_titles')
                                ? array_map(
                                    fn($t, $d) => ['title' => $t, 'description' => $d],
                                    old('feature_titles', []),
                                    old('feature_descs', []),
                                )
                                : $version->new_features ?? [];
                        @endphp

                        @forelse($features as $feat)
                            <div
                                class="feature-item p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                        <i class="bi bi-dot text-lg"></i> Item Fitur Baru
                                    </span>
                                    <button type="button" onclick="this.closest('.feature-item').remove()"
                                        class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </div>
                                <input type="text" name="feature_titles[]" value="{{ $feat['title'] ?? '' }}"
                                    placeholder="Judul Fitur (contoh: Modul Catatan & Keluhan)"
                                    class="m3-input-glass w-full text-xs font-bold mb-2" required>
                                <textarea name="feature_descs[]" rows="2" placeholder="Deskripsi penjelasan fitur baru..."
                                    class="m3-input-glass w-full text-xs font-medium resize-none">{{ $feat['description'] ?? '' }}</textarea>
                            </div>
                        @empty
                            <div
                                class="feature-item p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                        <i class="bi bi-dot text-lg"></i> Item Fitur Baru
                                    </span>
                                    <button type="button" onclick="this.closest('.feature-item').remove()"
                                        class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </div>
                                <input type="text" name="feature_titles[]" value=""
                                    placeholder="Judul Fitur (contoh: Modul Catatan & Keluhan)"
                                    class="m3-input-glass w-full text-xs font-bold mb-2">
                                <textarea name="feature_descs[]" rows="2" placeholder="Deskripsi penjelasan fitur baru..."
                                    class="m3-input-glass w-full text-xs font-medium resize-none"></textarea>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- CARD 3: DAFTAR PERBAIKAN & PENINGKATAN -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <div
                        class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5 mb-5">
                        <h3
                            class="font-black text-zinc-900 dark:text-white text-base tracking-tight flex items-center">
                            <div
                                class="w-8 h-8 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center mr-2.5 text-blue-600 dark:text-blue-400 text-sm shrink-0">
                                <i class="bi bi-tools"></i>
                            </div>
                            Perbaikan & Peningkatan Sistem
                        </h3>
                        <button type="button" onclick="addImproveRow()"
                            class="px-3 py-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-xs font-black transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="bi bi-plus-lg"></i>
                            Tambah Perbaikan
                        </button>
                    </div>

                    <div id="improves-container" class="flex flex-col gap-3.5">
                        @php
                            $improves = old('improve_titles')
                                ? array_map(
                                    fn($t, $d) => ['title' => $t, 'description' => $d],
                                    old('improve_titles', []),
                                    old('improve_descs', []),
                                )
                                : $version->improvements ?? [];
                        @endphp

                        @forelse($improves as $imp)
                            <div
                                class="improve-item p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="text-[11px] font-black text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                                        <i class="bi bi-dot text-lg"></i> Item Perbaikan
                                    </span>
                                    <button type="button" onclick="this.closest('.improve-item').remove()"
                                        class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </div>
                                <input type="text" name="improve_titles[]" value="{{ $imp['title'] ?? '' }}"
                                    placeholder="Judul Perbaikan (contoh: Penyempurnaan Form Pemilihan Ruangan)"
                                    class="m3-input-glass w-full text-xs font-bold mb-2" required>
                                <textarea name="improve_descs[]" rows="2" placeholder="Deskripsi penjelasan perbaikan yang dilakukan..."
                                    class="m3-input-glass w-full text-xs font-medium resize-none">{{ $imp['description'] ?? '' }}</textarea>
                            </div>
                        @empty
                            <div
                                class="improve-item p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="text-[11px] font-black text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                                        <i class="bi bi-dot text-lg"></i> Item Perbaikan
                                    </span>
                                    <button type="button" onclick="this.closest('.improve-item').remove()"
                                        class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </div>
                                <input type="text" name="improve_titles[]" value=""
                                    placeholder="Judul Perbaikan (contoh: Penyempurnaan Form Pemilihan Ruangan)"
                                    class="m3-input-glass w-full text-xs font-bold mb-2">
                                <textarea name="improve_descs[]" rows="2" placeholder="Deskripsi penjelasan perbaikan yang dilakukan..."
                                    class="m3-input-glass w-full text-xs font-medium resize-none"></textarea>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN (SIDEBAR INFO PENGEMBANG & PUBLISH) -->
            <div class="xl:col-span-4 flex flex-col gap-5 md:gap-6">

                <!-- CARD 4: AKSI SIMPAN -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3 class="font-black text-zinc-900 dark:text-white text-sm tracking-tight mb-4 flex items-center">
                        <i class="bi bi-cloud-arrow-up-fill text-emerald-600 mr-2"></i>
                        Publikasikan Versi
                    </h3>

                    <div
                        class="mb-5 p-3.5 rounded-2xl bg-zinc-100/80 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_latest" value="1"
                                {{ old('is_latest', $version->is_latest ?? true) ? 'checked' : '' }}
                                class="rounded text-emerald-600 focus:ring-emerald-500 mt-0.5 w-4 h-4">
                            <div>
                                <span class="text-xs font-bold text-zinc-900 dark:text-white block">Jadikan Versi Utama
                                    / Aktif</span>
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 block mt-0.5">Versi ini akan
                                    otomatis dijadikan versi rujukan utama saat diakses client.</span>
                            </div>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-black text-xs tracking-wider uppercase transition-all shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="bi bi-check2-circle text-base"></i>
                        Simpan & Publikasikan Versi
                    </button>
                </div>

                <!-- CARD 5: INFORMASI PENGEMBANG -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-sm tracking-tight mb-4 border-b border-zinc-200/80 dark:border-zinc-800 pb-3 flex items-center">
                        <i class="bi bi-code-square text-purple-600 mr-2"></i>
                        Informasi Pengembang
                    </h3>

                    <div class="flex flex-col gap-3.5">
                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nama Pengembang (Lead Dev) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="dev_name"
                                value="{{ old('dev_name', $version->dev_name ?? 'Mikyal Adly Ghoffar Hasin') }}"
                                placeholder="Nama Pengembang" class="m3-input-glass w-full text-xs font-bold"
                                required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Role / Jabatan
                            </label>
                            <input type="text" name="dev_role"
                                value="{{ old('dev_role', $version->dev_role ?? 'Lead Developer & Tim IT') }}"
                                placeholder="Lead Developer & Tim IT" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Lembaga
                            </label>
                            <input type="text" name="dev_institution"
                                value="{{ old('dev_institution', $version->dev_institution ?? 'MDT Hidayatus Shibyan') }}"
                                placeholder="MDT Hidayatus Shibyan" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Deskripsi Pengembang
                            </label>
                            <textarea name="dev_description" rows="3" placeholder="Deskripsi dedikasi pengembang..."
                                class="m3-input-glass w-full text-xs font-medium resize-none">{{ old('dev_description', $version->dev_description ?? 'Aplikasi ini dirancang dan dikembangkan untuk mendukung digitalisasi tata kelola madrasah, presensi KBM, evaluasi catatan murid, serta transparansi pelaporan terpadu.') }}</textarea>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Tech Stacks (Pisahkan dengan koma)
                            </label>
                            @php
                                $stacksStr = is_array($version->tech_stacks ?? null)
                                    ? implode(', ', $version->tech_stacks)
                                    : 'Flutter, Dart, Laravel, REST API, MySQL, Provider';
                            @endphp
                            <input type="text" name="tech_stacks" value="{{ old('tech_stacks', $stacksStr) }}"
                                placeholder="Flutter, Dart, Laravel, REST API, MySQL, Provider"
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>

    <!-- JAVASCRIPT DINAMIS UNTUK TAMBAH / HAPUS FITUR & PERBAIKAN -->
    @push('scripts')
        <script>
            function addFeatureRow() {
                const container = document.getElementById('features-container');
                const div = document.createElement('div');
                div.className =
                    'feature-item p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative animate-fade-in';
                div.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <i class="bi bi-dot text-lg"></i> Item Fitur Baru
                    </span>
                    <button type="button" onclick="this.closest('.feature-item').remove()"
                        class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer">
                        <i class="bi bi-trash-fill"></i> Hapus
                    </button>
                </div>
                <input type="text" name="feature_titles[]" value=""
                    placeholder="Judul Fitur Baru..."
                    class="m3-input-glass w-full text-xs font-bold mb-2" required>
                <textarea name="feature_descs[]" rows="2"
                    placeholder="Deskripsi penjelasan fitur baru..."
                    class="m3-input-glass w-full text-xs font-medium resize-none"></textarea>
            `;
                container.appendChild(div);
            }

            function addImproveRow() {
                const container = document.getElementById('improves-container');
                const div = document.createElement('div');
                div.className =
                    'improve-item p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative animate-fade-in';
                div.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-black text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                        <i class="bi bi-dot text-lg"></i> Item Perbaikan
                    </span>
                    <button type="button" onclick="this.closest('.improve-item').remove()"
                        class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer">
                        <i class="bi bi-trash-fill"></i> Hapus
                    </button>
                </div>
                <input type="text" name="improve_titles[]" value=""
                    placeholder="Judul Perbaikan Sistem..."
                    class="m3-input-glass w-full text-xs font-bold mb-2" required>
                <textarea name="improve_descs[]" rows="2"
                    placeholder="Deskripsi penjelasan perbaikan yang dilakukan..."
                    class="m3-input-glass w-full text-xs font-medium resize-none"></textarea>
            `;
                container.appendChild(div);
            }
        </script>
    @endpush
</x-app-layout>
