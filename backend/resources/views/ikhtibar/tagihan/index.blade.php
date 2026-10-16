@section('title', 'Tagihan Ikhtibar Kepala Keluarga (KK)')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Sumber Dana Ikhtibar
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">
                    Sasaran: Wali Murid / Per Kepala Keluarga (KK)
                </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Loket Tagihan Ikhtibar (KK)
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Pengelolaan dan pencatatan pembayaran tagihan per KK aktif untuk mendanai pagu operasional kepanitiaan.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            <!-- Filter Dropdown & Search -->
            <form action="{{ route('tagihan-ikhtibar.index') }}" method="GET"
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

                <!-- Filter Kampung -->
                <div class="relative group/select w-full sm:w-36">
                    <select name="kampung_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="">Semua Dusun</option>
                        @foreach ($kampungs as $kp)
                            <option value="{{ $kp->id }}"
                                {{ request('kampung_id') == $kp->id ? 'selected' : '' }}>
                                ({{ $kp->kode }})
                                {{ $kp->nama_kampung }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Status Bayar -->
                <div class="relative group/select w-full sm:w-32">
                    <select name="status_bayar" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        <option value="">Semua Status</option>
                        <option value="Lunas" {{ request('status_bayar') === 'Lunas' ? 'selected' : '' }}>Lunas
                        </option>
                        <option value="Belum Lunas" {{ request('status_bayar') === 'Belum Lunas' ? 'selected' : '' }}>
                            Belum Lunas</option>
                        <option value="Belum Terbit"
                            {{ request('status_bayar') === 'Belum Terbit' ? 'selected' : '' }}>Belum Terbit</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Search KK -->
                <div class="relative w-full sm:w-40">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama KK..."
                        class="m3-input-glass w-full text-xs !pl-8">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Tombol Cetak Leger Tagihan -->
                <a href="{{ route('tagihan-ikhtibar.cetak-leger', array_merge(['kegiatanId' => $kegiatan->id], request()->all())) }}"
                    target="_blank" class="m3-btn-secondary text-xs flex items-center gap-1.5 py-2 px-3 shrink-0"
                    title="Cetak Leger Tagihan">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-sm"></i>
                    <span class="hidden sm:inline">Leger Tagihan</span>
                </a>

                <!-- Tombol Terbitkan Massal -->
                <button type="button" onclick="terbitkanMassal()"
                    class="m3-btn-primary text-xs flex items-center gap-1.5 py-2 px-3 shrink-0 !bg-emerald-600 hover:!bg-emerald-700">
                    <i class="bi bi-send-check-fill text-sm"></i>
                    <span>Terbitkan Massal</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Finansial Tagihan (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 relative z-10">
        <!-- 1. Master Tarif Tagihan KK -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl font-bold shrink-0">
                    <i class="bi bi-tag-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-black text-primary uppercase tracking-wider block">Tarif Per KK</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        Rp {{ number_format($pengaturanTagihan?->nominal ?? 0, 0, ',', '.') }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $pengaturanTagihan?->nama_tagihan ?? 'Belum Ditautkan di Master' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Tagihan Terbit -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-blue-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-bold border border-blue-500/20 shrink-0">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-wider block">Tagihan
                        Terbit</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        {{ $totalTerbit }} <span class="text-xs font-semibold text-zinc-500">KK</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Dari {{ $walis->count() }} KK Aktif
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Lunas & Terkumpul -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-emerald-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold border border-emerald-500/20 shrink-0">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Realisasi
                        Lunas</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        Rp {{ number_format($nominalLunas, 0, ',', '.') }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $totalLunas }} KK Lunas
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Belum Lunas & Piutang -->
        <div
            class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold border border-amber-500/20 shrink-0">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Sisa
                        Belum Lunas</span>
                    <h4 class="text-base font-black text-zinc-900 dark:text-white mt-0.5">
                        {{ $totalBelumLunas }} <span class="text-xs font-semibold text-zinc-500">KK</span>
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        Sisa Piutang: Rp
                        {{ number_format($totalBelumLunas * ($pengaturanTagihan?->nominal ?? 0), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Grid Container -->
    <div id="data-grid-container" class="relative z-10">
        @include('ikhtibar.tagihan.list', [
            'walis' => $walis,
            'kegiatan' => $kegiatan,
            'pengaturanTagihan' => $pengaturanTagihan,
        ])
    </div>

    @push('scripts')
        <script>
            function terbitkanMassal() {
                Swal.fire({
                    title: 'Terbitkan Tagihan Massal?',
                    text: 'Sistem akan membuat tagihan Ikhtibar otomatis bagi seluruh Kepala Keluarga (KK) yang santrinya aktif di tahun ajaran ini.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Terbitkan Massal!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Penerbitan...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch('{{ route('tagihan-ikhtibar.terbitkan-massal', $kegiatan->id) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    if (typeof window.refreshDataGrid === 'function') {
                                        window.refreshDataGrid();
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                                }
                            })
                            .catch(err => {
                                Swal.fire('Error', 'Gagal memproses penerbitan tagihan massal.', 'error');
                            });
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
