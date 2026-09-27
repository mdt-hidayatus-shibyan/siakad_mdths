@section('title', 'PengaturanKas Ruangan')

<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- HEADER & TOOLBAR SEJAJAR -->
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <!-- Sisi Kiri: Judul Halaman -->
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Pengaturan Kas Ruangan
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Konfigurasi besaran iuran kas per murid (Laki-laki & Perempuan) per ruangan.
            </p>
        </div>

        <!-- Sisi Kanan: Toolbar Filter -->
        <div class="w-full xl:w-auto shrink-0">
            <form action="{{ request()->url() }}" method="GET" id="formFilter"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">

                <!-- Badge Auto Save -->
                <div
                    class="hidden md:flex items-center gap-1.5 px-3 h-10 shrink-0 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl shadow-2xs text-[10px] font-black uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i class="bi bi-cloud-check-fill text-xs"></i> Auto-save
                </div>

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

    <!-- AREA GRID KONTEN (SOLID ZINC CARDS) -->
    <div class="flex-1 overflow-y-auto px-1 md:px-0 custom-scrollbar pb-8 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            @forelse($ruangans as $ruang)
                @php
                    $nomLaki = $ruang->pengaturanKas->nominal_laki ?? 0;
                    $nomPerempuan = $ruang->pengaturanKas->nominal_perempuan ?? 0;
                @endphp

                <div
                    class="m3-glass-card p-5 flex flex-col gap-4 transition-all shadow-2xs hover:border-primary/40 dark:hover:border-primary-dark/40 relative group">

                    <!-- Card Header -->
                    <div
                        class="flex justify-between items-start border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                        <div>
                            <h3
                                class="font-black text-base md:text-lg text-zinc-900 dark:text-white tracking-tight leading-tight mb-1">
                                {{ $ruang->nama_ruangan }}
                            </h3>
                            <div
                                class="text-[10px] font-black px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded border border-zinc-200 dark:border-zinc-700 uppercase tracking-wider inline-block shadow-2xs">
                                {{ $ruang->level->nama_level ?? 'Kelas' }}
                            </div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center shrink-0 border border-primary/20 shadow-2xs">
                            <i class="bi bi-wallet2 text-base"></i>
                        </div>
                    </div>

                    <!-- Card Inputs -->
                    <div class="flex flex-col gap-3.5">
                        <!-- Input Laki-laki -->
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                Nominal Murid Putra (L)
                            </label>
                            <div class="relative flex items-center group/input">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 font-mono text-xs font-black">
                                    Rp
                                </div>
                                <input type="number" value="{{ $nomLaki }}"
                                    onchange="updateKonfigKasPintar({{ $ruang->id }}, 'nominal_laki', this.value, this)"
                                    class="m3-input-glass w-full text-xs font-bold font-mono !pl-9">
                            </div>
                        </div>

                        <!-- Input Perempuan -->
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                Nominal Murid Putri (P)
                            </label>
                            <div class="relative flex items-center group/input">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 font-mono text-xs font-black">
                                    Rp
                                </div>
                                <input type="number" value="{{ $nomPerempuan }}"
                                    onchange="updateKonfigKasPintar({{ $ruang->id }}, 'nominal_perempuan', this.value, this)"
                                    class="m3-input-glass w-full text-xs font-bold font-mono !pl-9">
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- STATE KOSONG -->
                <div class="col-span-full">
                    <x-empty-state icon="bi-door-closed" title="Data Tidak Ditemukan"
                        message="Belum ada data ruangan untuk tahun ajaran yang dipilih." />
                </div>
            @endforelse

        </div>
    </div>

    <!-- TOAST NOTIFICATION (Material 3 Style) -->
    <div id="toast-notif"
        class="fixed bottom-6 md:top-6 md:bottom-auto right-6 transform translate-y-[150%] md:translate-y-0 md:translate-x-[150%] transition-transform duration-500 ease-out bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-5 py-3 rounded-2xl shadow-xl border border-zinc-800 dark:border-zinc-200 font-black text-xs flex items-center gap-2.5 z-50">
        <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
        <span id="toast-msg">Tersimpan</span>
    </div>

    <script>
        function updateKonfigKasPintar(ruanganId, fieldName, value, inputElement) {
            inputElement.classList.add('opacity-50', 'pointer-events-none');

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('pengaturan-kas-ruangan.auto-save') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ruangan_id: ruanganId,
                        field: fieldName,
                        value: value || 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    inputElement.classList.remove('opacity-50', 'pointer-events-none');
                    if (data.success) {
                        showToast('Berhasil Disimpan!');
                        inputElement.classList.add('!border-emerald-500', 'ring-2', 'ring-emerald-500/30');
                        setTimeout(() => inputElement.classList.remove('!border-emerald-500', 'ring-2',
                            'ring-emerald-500/30'), 1000);
                    }
                })
                .catch(error => {
                    inputElement.classList.remove('opacity-50', 'pointer-events-none');
                    showToast('Gagal menyimpan data!', true);
                    console.error('Error:', error);
                });
        }

        function showToast(message, isError = false) {
            const toast = document.getElementById('toast-notif');
            const icon = toast.querySelector('i');

            document.getElementById('toast-msg').innerText = message;

            if (isError) {
                icon.classList.replace('bi-check-circle-fill', 'bi-x-circle-fill');
                icon.classList.replace('text-emerald-500', 'text-rose-500');
            } else {
                icon.classList.replace('bi-x-circle-fill', 'bi-check-circle-fill');
                icon.classList.replace('text-rose-500', 'text-emerald-500');
            }

            toast.classList.remove('translate-y-[150%]', 'md:translate-x-[150%]');

            setTimeout(() => {
                toast.classList.add('translate-y-[150%]', 'md:translate-x-[150%]');
            }, 3000);
        }
    </script>
</x-app-layout>
