@section('title', 'Catatan & Keluhan Ustadz')

<x-app-layout>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-5 relative z-30">
        <div>
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark text-xs font-black uppercase tracking-wider mb-2 border border-primary/20 shadow-2xs">
                <i class="bi bi-journal-text text-sm"></i>
                <span>Aspirasi & Laporan Pendidik</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Catatan & Keluhan Ustadz
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Kumpulan observasi, laporan perkembangan murid, dan keluhan madrasah yang disampaikan oleh para asatidz
                <span class="font-bold text-amber-600 dark:text-amber-400">(Akses Khusus: Read-Only Admin)</span>.
            </p>
        </div>
    </div>

    <!-- Stat Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-6 md:mb-8">
        <!-- Total Catatan -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center text-xl shrink-0 border border-primary/20 shadow-2xs">
                <i class="bi bi-journal-check"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] md:text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">
                    Total Catatan</p>
                <h3
                    class="text-lg md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight leading-none mt-1">
                    {{ $totalSemua }}</h3>
            </div>
        </div>

        <!-- Keluhan Murid -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 border border-amber-500/20 shadow-2xs">
                <i class="bi bi-person-exclamation"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] md:text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">
                    Terkait Murid</p>
                <h3
                    class="text-lg md:text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight leading-none mt-1">
                    {{ $totalKeluhanMurid }}</h3>
            </div>
        </div>

        <!-- Terkait Madrasah -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shrink-0 border border-blue-500/20 shadow-2xs">
                <i class="bi bi-building"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] md:text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">
                    Madrasah & Fasilitas</p>
                <h3
                    class="text-lg md:text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight leading-none mt-1">
                    {{ $totalMadrasah }}</h3>
            </div>
        </div>

        <!-- Urgensi Tinggi -->
        <div class="m3-glass-card p-4 md:p-5 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shrink-0 border border-rose-500/20 shadow-2xs">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p
                    class="text-[10px] md:text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">
                    Urgensi Tinggi</p>
                <h3
                    class="text-lg md:text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight leading-none mt-1">
                    {{ $totalUrgensiTinggi }}</h3>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="m3-glass-card p-4 md:p-5 mb-6 relative z-20 shadow-2xs">
        <form action="{{ route('catatan-ustadz.index') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <!-- Filter Kategori -->
            <div class="relative group/select">
                <select name="kategori" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pr-9 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Kategori --</option>
                    @foreach ($kategoriList as $k)
                        <option value="{{ $k }}"
                            class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                            {{ $kategori == $k ? 'selected' : '' }}>
                            {{ $k }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>

            <!-- Filter Target -->
            <div class="relative group/select">
                <select name="target_tipe" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pr-9 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Sasaran --</option>
                    <option value="murid" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $targetTipe == 'murid' ? 'selected' : '' }}>Terkait Murid</option>
                    <option value="madrasah" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $targetTipe == 'madrasah' ? 'selected' : '' }}>Terkait Madrasah</option>
                    <option value="umum" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $targetTipe == 'umum' ? 'selected' : '' }}>Umum</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>

            <!-- Filter Ustadz -->
            <div class="relative group/select">
                <select name="ustadz_id" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pr-9 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Ustadz --</option>
                    @foreach ($daftarUstadz as $u)
                        <option value="{{ $u->id }}"
                            class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                            {{ $ustadzId == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_lengkap }} ({{ $u->kode_ustadz }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>

            <!-- Filter Urgensi -->
            <div class="relative group/select">
                <select name="urgensi" onchange="this.form.submit()"
                    class="m3-input-glass w-full !pr-9 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" class="bg-white dark:bg-zinc-900 text-zinc-500">-- Semua Urgensi --</option>
                    <option value="Rendah" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $urgensi == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                    <option value="Sedang" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $urgensi == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="Tinggi" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $urgensi == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="Penting / Mendesak" class="bg-white dark:bg-zinc-900 text-zinc-800 dark:text-white"
                        {{ $urgensi == 'Penting / Mendesak' ? 'selected' : '' }}>Penting / Mendesak</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>

            <!-- Search Input -->
            <div class="relative group/search">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark transition-colors">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari judul, murid, ustadz..."
                    class="m3-input-glass w-full !pl-9 !pr-9 text-xs font-bold">
                @if ($search || $kategori || $targetTipe || $ustadzId || $urgensi)
                    <a href="{{ route('catatan-ustadz.index') }}"
                        class="absolute inset-y-0 right-0 w-9 h-9 my-auto mr-1 flex items-center justify-center text-zinc-400 hover:text-red-600 dark:hover:text-red-400 rounded-full transition-colors outline-none"
                        title="Reset Filter">
                        <i class="bi bi-x-lg text-xs font-bold"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Data List Container -->
    <div id="catatan-list-container" class="space-y-4 relative z-10">
        @include('catatan-ustadz.list', ['catatans' => $catatans])
    </div>

    <!-- Modal Detail Catatan -->
    <div id="modalDetailCatatan"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div
            class="m3-glass-card w-full max-w-2xl p-6 relative overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center text-lg border border-primary/20 shadow-2xs">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white">Detail Catatan Ustadz</h3>
                        <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Pratinjau lengkap isi catatan
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailModal()"
                    class="w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 flex items-center justify-center text-zinc-500 dark:text-zinc-400 outline-none transition-all">
                    <i class="bi bi-x-lg text-xs font-bold"></i>
                </button>
            </div>

            <div id="modalDetailContent" class="space-y-4 max-h-[70vh] overflow-y-auto pr-1 custom-scrollbar">
                <!-- Diisi via AJAX / JS -->
                <div class="py-12 text-center text-zinc-400">
                    <i class="bi bi-arrow-repeat animate-spin text-2xl"></i>
                    <p class="text-xs font-bold mt-2">Memuat detail catatan...</p>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-zinc-200/80 dark:border-zinc-800 flex justify-end">
                <button type="button" onclick="closeDetailModal()"
                    class="m3-btn-secondary h-10 px-6 text-xs font-black">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            function showDetailModal(url) {
                const modal = document.getElementById('modalDetailCatatan');
                const content = document.getElementById('modalDetailContent');

                modal.classList.remove('hidden');
                modal.classList.add('flex');

                content.innerHTML = `
                <div class="py-12 text-center text-zinc-400">
                    <i class="bi bi-arrow-repeat animate-spin text-2xl inline-block"></i>
                    <p class="text-xs font-bold mt-2">Memuat detail catatan...</p>
                </div>
            `;

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.html) {
                            content.innerHTML = data.html;
                        } else {
                            content.innerHTML =
                                `<div class="p-4 text-center text-red-500 font-bold text-xs">Gagal memuat konten detail.</div>`;
                        }
                    })
                    .catch(err => {
                        content.innerHTML =
                            `<div class="p-4 text-center text-red-500 font-bold text-xs">Terjadi kesalahan koneksi saat memuat detail.</div>`;
                    });
            }

            function closeDetailModal() {
                const modal = document.getElementById('modalDetailCatatan');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        </script>
    @endpush

</x-app-layout>
