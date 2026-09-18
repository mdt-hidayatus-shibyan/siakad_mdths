@section('title', 'Detail Keluarga & Murid')

<x-app-layout>

    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('wali-murid.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none border border-zinc-200 dark:border-zinc-700"
                title="Kembali">
                <i class="bi bi-arrow-left text-base"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Detail Anggota Keluarga
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Kelola murid yang berada di bawah tanggungan Kartu Keluarga ini.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 md:gap-6 relative z-10">

        <!-- ================= KOLOM KIRI (Info Keluarga & Form Link) ================= -->
        <div class="lg:col-span-1 space-y-5 md:space-y-6">

            <!-- Card Info Keluarga -->
            <div class="m3-glass-card p-5 md:p-6 shadow-2xs">
                <!-- Ikon Header -->
                <div
                    class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center text-xl mb-4 border border-primary/20 shadow-2xs">
                    <i class="bi bi-houses-fill"></i>
                </div>

                <!-- Info Utama -->
                <h3
                    class="text-lg md:text-xl font-black text-zinc-900 dark:text-white mb-2 tracking-tight flex items-center gap-2 flex-wrap">
                    Kel. {{ $wali->nama_kepala_keluarga }}
                    <span
                        class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                        {{ $wali->kepala_keluarga }}
                    </span>
                </h3>

                <!-- Baris ID (Reg & KK) -->
                <div class="flex flex-col gap-1 mb-5">
                    <p
                        class="text-xs font-black text-primary dark:text-primary-dark tracking-wider flex items-center uppercase font-mono">
                        <i class="bi bi-hash mr-1 text-sm"></i> {{ $wali->no_registrasi }}
                    </p>
                    <p
                        class="text-xs font-bold text-zinc-500 dark:text-zinc-400 tracking-wider flex items-center font-mono">
                        <i class="bi bi-credit-card-2-front mr-1.5 text-sm"></i>
                        {{ $wali->no_kk ? mask_kk($wali->no_kk) : 'No KK Belum Diinput' }}
                    </p>
                </div>

                <!-- Blok Detail (Zonasi & WA) -->
                <div class="space-y-2.5 pt-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    <div
                        class="bg-zinc-50/70 dark:bg-zinc-950/50 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                        <p class="text-[10px] uppercase font-black text-zinc-400 tracking-wider mb-0.5">
                            Zonasi Kampung</p>
                        <p class="text-xs font-bold text-zinc-900 dark:text-white flex items-center">
                            <i class="bi bi-geo-alt text-zinc-400 mr-1.5 text-xs"></i>
                            {{ $wali->kampung->nama_kampung }}
                        </p>
                    </div>

                    <div
                        class="bg-zinc-50/70 dark:bg-zinc-950/50 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                        <p class="text-[10px] uppercase font-black text-zinc-400 tracking-wider mb-0.5">
                            Kontak WhatsApp</p>
                        <p class="text-xs font-bold text-zinc-900 dark:text-white flex items-center font-mono">
                            <i class="bi bi-whatsapp text-emerald-500 mr-1.5 text-xs"></i>
                            {{ $wali->no_hp ?: '-' }}
                        </p>
                    </div>

                    <div
                        class="bg-zinc-50/70 dark:bg-zinc-950/50 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                        <p class="text-[10px] uppercase font-black text-zinc-400 tracking-wider mb-0.5">
                            Status Keluarga</p>
                        <p
                            class="text-xs font-bold flex items-center {{ $wali->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            <i class="bi bi-circle-fill mr-1.5 text-[8px]"></i>
                            {{ $wali->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KOLOM KANAN (Daftar Anak) ================= -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Card Tautkan Murid (Hanya untuk yang punya izin) -->
            @can('update wali-murid')
                <div
                    class="m3-glass-card p-5 bg-primary/5 dark:bg-primary-dark/5 border-primary/20 dark:border-primary-dark/20 relative overflow-hidden shadow-2xs">

                    <h3
                        class="text-base font-black text-primary dark:text-primary-dark tracking-tight mb-0.5 relative z-10">
                        Tautkan Murid Baru
                    </h3>
                    <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mb-4 relative z-10">
                        Masukkan NISM untuk menarik data murid ke dalam Kartu Keluarga ini.
                    </p>

                    <form action="{{ route('wali-murid.link-anak', $wali->id) }}" method="POST"
                        class="relative z-10 space-y-3">
                        @csrf
                        <div>
                            <div class="relative group/input">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/input:text-primary dark:group-focus-within/input:text-primary-dark transition-colors">
                                    <i class="bi bi-upc-scan text-xs"></i>
                                </div>
                                <input type="text" name="nism" required placeholder="Ketik NISM murid..."
                                    class="m3-input-glass w-full !pl-9 text-xs font-bold font-mono {{ $errors->has('nism') ? '!border-red-500 !ring-red-500/20' : '' }}">
                            </div>
                            @error('nism')
                                <p class="text-[11px] font-bold text-red-500 dark:text-red-400 mt-1.5 ml-1 flex items-center">
                                    <i class="bi bi-exclamation-triangle-fill mr-1.5"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <button type="submit" class="m3-btn-primary w-full h-10 text-xs font-black shadow-2xs">
                            <i class="bi bi-link-45deg mr-1.5 text-base"></i> Tautkan Sekarang
                        </button>
                    </form>
                </div>
            @endcan

            <div class="m3-glass-card p-5 sm:p-6 shadow-2xs">

                <h3
                    class="text-base font-black text-zinc-900 dark:text-white tracking-tight mb-4 flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                    <span>Daftar Tanggungan Murid</span>
                    <span
                        class="px-2.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 rounded-lg text-xs font-black border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                        {{ $wali->murids->count() }} Murid
                    </span>
                </h3>

                <!-- Grid Murid -->
                <div class="grid grid-cols-1 gap-3">
                    @forelse($wali->murids as $anak)
                        <div
                            class="p-3.5 rounded-2xl bg-zinc-50/70 dark:bg-zinc-950/50 border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5 group transition-all hover:border-primary/40 dark:hover:border-primary-dark/40 shadow-2xs {{ $anak->status != 'Aktif' ? 'opacity-70 grayscale' : '' }}">

                            @php
                                $defaultFoto =
                                    $anak->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png';
                                $fotoPath = $anak->foto ? $anak->foto : $defaultFoto;
                            @endphp

                            <!-- Foto Murid -->
                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                                <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto"
                                    class="w-full h-full object-cover rounded-lg">
                            </div>

                            <!-- Detail Murid -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs sm:text-sm font-black text-zinc-900 dark:text-white truncate mb-0.5"
                                    title="{{ $anak->nama_lengkap }}">
                                    {{ $anak->nama_lengkap }}
                                </h4>
                                <div
                                    class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[10px] font-bold text-zinc-500 dark:text-zinc-400 mb-1.5 font-mono">
                                    <span class="flex items-center tracking-wider uppercase">
                                        <i class="bi bi-upc-scan mr-1 text-[10px] text-zinc-400"></i> NISM:
                                        {{ $anak->nism ?: '-' }}
                                    </span>
                                    <span class="text-zinc-300 dark:text-zinc-700 font-sans">•</span>
                                    <span
                                        class="flex items-center tracking-wider uppercase text-zinc-700 dark:text-zinc-300">
                                        <i
                                            class="bi bi-person-vcard mr-1 text-[11px] text-primary dark:text-primary-dark"></i>
                                        NIK: {{ $anak->nik ?: '-' }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Badge L/P -->
                                    <span
                                        class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border shadow-2xs {{ $anak->jenis_kelamin == 'L' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20' : 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border-pink-500/20' }}">
                                        {{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>

                                    <!-- Badge Status -->
                                    <span
                                        class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border shadow-2xs {{ $anak->status == 'Aktif' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20' }}">
                                        {{ $anak->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Tombol Lepas (Unlink) -->
                            <div
                                class="pl-2 border-l border-zinc-200/80 dark:border-zinc-800 ml-auto flex items-center shrink-0">
                                @can('edit wali-murid')
                                    <button type="button"
                                        onclick="confirmUnlink('{{ $anak->id }}', '{{ addslashes($anak->nama_lengkap) }}')"
                                        class="w-8 h-8 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 transition-all hover:scale-105 active:scale-90 shadow-2xs outline-none"
                                        title="Lepas Murid dari KK">
                                        <i class="bi bi-person-x-fill text-xs"></i>
                                    </button>

                                    <!-- Form Rahasia Hapus -->
                                    <form id="form-unlink-{{ $anak->id }}"
                                        action="{{ route('wali-murid.unlink-anak', ['id' => $wali->id, 'murid_id' => $anak->id]) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endcan
                            </div>

                        </div>
                    @empty
                        <!-- Empty State Murid -->
                        <div
                            class="text-center py-10 bg-zinc-50/40 dark:bg-zinc-950/40 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                            <div
                                class="w-12 h-12 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-400 dark:text-zinc-500 flex items-center justify-center text-xl mx-auto mb-2 shadow-2xs">
                                <i class="bi bi-emoji-frown"></i>
                            </div>
                            <h4 class="text-xs font-black text-zinc-900 dark:text-white tracking-tight">Belum Ada
                                Tanggungan</h4>
                            <p class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-0.5">Gunakan form di
                                atas untuk menautkan NISM murid.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts untuk SweetAlert -->
    @push('script')
        <script>
            const swalCustomClass = {
                popup: 'rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl',
                title: 'text-lg font-black text-zinc-900 dark:text-white tracking-tight',
                htmlContainer: 'text-xs font-bold text-zinc-500 dark:text-zinc-400',
                actions: "gap-3 mt-4",
                confirmButton: "rounded-xl px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition-all shadow-2xs outline-none",
                cancelButton: "rounded-xl px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs transition-all outline-none border border-transparent dark:border-zinc-700 shadow-2xs"
            };

            // FUNGSI KONFIRMASI BATAL TAUTKAN
            function confirmUnlink(muridId, namaAnak) {
                Swal.fire({
                    title: 'Lepas Tautan?',
                    html: `Anda yakin ingin mengeluarkan <b class="text-rose-500">${namaAnak}</b> dari Kartu Keluarga ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    heightAuto: false,
                    buttonsStyling: false,
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                    customClass: swalCustomClass,
                    confirmButtonText: '<i class="bi bi-person-x-fill mr-1.5"></i> Ya, Lepaskan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-unlink-' + muridId).submit();
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
