@section('title', 'Persyaratan Ujian')
<x-app-layout>
    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 relative z-20">

        <!-- Area Header -->
        <div class="w-full xl:w-auto shrink-0">
            <h2
                class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                Persyaratan Ujian
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                Verifikasi kelayakan, status kepesertaan, dan dispensasi administrasi murid sebelum ujian.
            </p>
        </div>

        <!-- Area Form Pencarian Toolbar -->
        <div class="w-full xl:w-auto flex-1 flex xl:justify-end">
            <form action="{{ request()->url() }}" method="GET" id="formSelector"
                class="flex flex-col sm:flex-row items-center gap-2.5 w-full xl:w-auto">
                <input type="hidden" name="tahun_id" value="{{ $tahunPelajaranId }}">

                <!-- Filter Ruangan -->
                <div class="relative w-full sm:w-[170px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-sm"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formSelector').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach ($daftarRuangan as $r)
                            <option value="{{ $r->id }}" {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Filter Ujian -->
                <div class="relative w-full sm:w-[170px] group/select">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-file-earmark-check text-sm"></i>
                    </div>
                    <select name="ujian_id" {{ $daftarUjian->isEmpty() ? 'disabled' : '' }}
                        onchange="document.getElementById('formSelector').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer disabled:opacity-50">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach ($daftarUjian as $uj)
                            <option value="{{ $uj->id }}" {{ request('ujian_id') == $uj->id ? 'selected' : '' }}>
                                {{ $uj->nama_ujian }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-4 group/btn">
                        <i class="bi bi-search text-sm"></i>
                        <span class="sm:hidden xl:inline">Cari</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- AREA KARTU PERSYARATAN -->
    @if (request('ruangan_id') && request('ujian_id'))
        @php
            $totalSemua = count($muridsWithStatus);
            $totalTidakIkut = $muridsWithStatus->where('tidak_ikut_ujian', true)->count();
            $totalPeserta = $totalSemua - $totalTidakIkut;
            $totalTerpenuhi = $muridsWithStatus->where('tidak_ikut_ujian', false)->where('is_locked', false)->count();
            $totalTerkunci = $muridsWithStatus->where('tidak_ikut_ujian', false)->where('is_locked', true)->count();
        @endphp

        <div class="relative z-10 animate-[modalFadeIn_0.2s_ease-out] flex flex-col gap-4">
            <!-- 1. KARTU HEADER & REKAP KEPESERTAAN -->
            <div class="m3-glass-card px-5 py-4 flex flex-col md:flex-row justify-between md:items-center gap-3">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200/80 dark:border-sky-800/40 flex items-center justify-center shrink-0 hidden sm:flex shadow-2xs">
                        <i class="bi bi-clipboard2-check-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-snug">
                            Status Verifikasi Persyaratan & Kepesertaan Ujian
                        </h3>
                        <p
                            class="text-xs font-bold text-zinc-500 dark:text-zinc-400 flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-primary dark:text-primary-dark font-extrabold">{{ $totalSemua }}
                                Murid</span>
                            <span class="text-zinc-300 dark:text-zinc-700">•</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $totalTerpenuhi }}
                                Lunas/Dispensasi</span>
                            @if ($totalTerkunci > 0)
                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                <span class="text-amber-600 dark:text-amber-400 font-extrabold">{{ $totalTerkunci }}
                                    Menunggak</span>
                            @endif
                            @if ($totalTidakIkut > 0)
                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                <span class="text-rose-600 dark:text-rose-400 font-extrabold">{{ $totalTidakIkut }}
                                    Tidak Ikut Ujian</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. AREA LIST FLOATING CARDS -->
            <div class="flex flex-col gap-2.5">
                @foreach ($muridsWithStatus as $murid)
                    @php
                        $isTidakIkut = $murid->tidak_ikut_ujian ?? false;
                        $isLocked = $murid->is_locked;
                    @endphp

                    <!-- CARD ITEM -->
                    <div
                        class="m3-glass-card p-3.5 sm:p-4 transition-all duration-200 {{ $isTidakIkut ? 'border-zinc-300 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/30 opacity-90' : ($isLocked ? 'border-rose-200/80 dark:border-rose-900/50 bg-rose-50/30 dark:bg-rose-950/20' : 'hover:border-primary/40 dark:hover:border-primary-dark/40') }} flex flex-col md:flex-row items-start md:items-center justify-between gap-3 md:gap-5">

                        <!-- Bagian Kiri: Info Murid (No, Nama, NISM) -->
                        <div class="flex items-center gap-3 w-full md:w-auto md:flex-1 shrink-0">
                            <!-- Badge Nomor -->
                            <div
                                class="w-9 h-9 rounded-xl {{ $isTidakIkut ? 'bg-zinc-200/80 dark:bg-zinc-800 text-zinc-500' : 'bg-zinc-100/80 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400' }} border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-center text-xs font-black shrink-0">
                                {{ $loop->iteration }}
                            </div>

                            <!-- Info Data -->
                            <div class="flex flex-col">
                                <h4
                                    class="font-black text-sm {{ $isTidakIkut ? 'text-zinc-600 dark:text-zinc-400 line-through' : 'text-zinc-900 dark:text-white' }} tracking-tight leading-tight">
                                    {{ $murid->nama_lengkap }}
                                </h4>
                                <div
                                    class="inline-flex items-center gap-1.5 text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mt-0.5">
                                    <i class="bi bi-person-badge text-xs"></i> NISM: {{ $murid->nism ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <!-- Bagian Tengah: Syarat & Alasan -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full md:flex-1 border-t md:border-t-0 md:border-l border-zinc-200/80 dark:border-zinc-800 pt-2.5 md:pt-0 md:pl-5">

                            <!-- Status Terkunci / Terbuka / Tidak Ikut -->
                            <div class="shrink-0">
                                @if ($isTidakIkut)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-zinc-200/80 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider">
                                        <i class="bi bi-person-x-fill text-xs text-rose-500"></i> Tidak Ikut Ujian
                                    </span>
                                @elseif ($isLocked)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/40 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider">
                                        <i class="bi bi-lock-fill text-xs"></i> Belum Terpenuhi
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider">
                                        <i class="bi bi-unlock-fill text-xs"></i> Terpenuhi
                                    </span>
                                @endif
                            </div>

                            <!-- Alasan Keterangan -->
                            <div class="flex-1">
                                @if ($isTidakIkut)
                                    <div class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 leading-snug">
                                        {{ $murid->alasan_tidak_ikut ?? 'Tidak mengikuti ujian' }}
                                    </div>
                                @elseif ($isLocked)
                                    <div class="text-xs font-semibold text-rose-600 dark:text-rose-400 leading-snug line-clamp-2"
                                        title="{{ $murid->lock_reason }}">
                                        {{ $murid->lock_reason ?? 'Belum melunasi administrasi' }}
                                    </div>
                                @else
                                    <div
                                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 leading-snug">
                                        Siap mengikuti ujian
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Bagian Kanan: Aksi (Dispensasi / Tandai Tidak Ikut / Batalkan) -->
                        <div
                            class="w-full md:w-auto shrink-0 flex flex-wrap items-center justify-end gap-2 border-t md:border-t-0 border-zinc-200/80 dark:border-zinc-800 pt-2.5 md:pt-0">
                            @if ($isTidakIkut)
                                <!-- Tombol Batalkan Tidak Ikut (Kembalikan jadi peserta) -->
                                <button type="button"
                                    onclick="pemicuBatalkanTidakIkut({{ request('ujian_id') }}, {{ $murid->id }}, '{{ addslashes($murid->nama_lengkap) }}')"
                                    class="px-3 py-1.5 bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/30 font-black text-xs uppercase tracking-wider rounded-xl transition-all active:scale-95 outline-none shadow-2xs flex items-center justify-center gap-1.5 group/btn">
                                    <i
                                        class="bi bi-arrow-counterclockwise text-sm group-hover/btn:rotate-180 transition-transform"></i>
                                    <span>Ikutkan Ujian</span>
                                </button>
                            @else
                                @if ($isLocked)
                                    <!-- Tombol Beri Dispensasi -->
                                    <button type="button"
                                        onclick="pemicuDispensasi({{ request('ujian_id') }}, {{ $murid->id }}, '{{ addslashes($murid->nama_lengkap) }}')"
                                        class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 font-black text-xs uppercase tracking-wider rounded-xl transition-all active:scale-95 outline-none shadow-2xs flex items-center justify-center gap-1.5 group/btn">
                                        <i
                                            class="bi bi-journal-check text-sm group-hover/btn:scale-110 transition-transform"></i>
                                        <span>Izin Wali</span>
                                    </button>
                                @endif

                                <!-- Tombol Tandai Tidak Ikut -->
                                <button type="button"
                                    onclick="pemicuTandaiTidakIkut({{ request('ujian_id') }}, {{ $murid->id }}, '{{ addslashes($murid->nama_lengkap) }}')"
                                    class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 font-black text-xs uppercase tracking-wider rounded-xl transition-all active:scale-95 outline-none shadow-2xs flex items-center justify-center gap-1.5 group/btn"
                                    title="Tandai murid tidak mengikuti ujian ini agar tidak diwajibkan dalam target nilai">
                                    <i
                                        class="bi bi-person-x text-sm group-hover/btn:scale-110 transition-transform"></i>
                                    <span>Tidak Ikut</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- FORM TERSEMBUNYI DISPENSASI -->
        <form id="formDispensasiTersembunyi" action="{{ route('persyaratan-ujian.dispensasi') }}" method="POST"
            class="hidden">
            @csrf
            <input type="hidden" name="ujian_id" id="hidDispensasiUjianId">
            <input type="hidden" name="murid_id" id="hidDispensasiMuridId">
            <input type="hidden" name="alasan_izin" id="hidDispensasiAlasan">
        </form>

        <!-- FORM TERSEMBUNYI TANDAI TIDAK IKUT -->
        <form id="formTandaiTidakIkutTersembunyi" action="{{ route('persyaratan-ujian.tandai-tidak-ikut') }}"
            method="POST" class="hidden">
            @csrf
            <input type="hidden" name="ujian_id" id="hidTidakIkutUjianId">
            <input type="hidden" name="murid_id" id="hidTidakIkutMuridId">
            <input type="hidden" name="alasan" id="hidTidakIkutAlasan">
        </form>

        <!-- FORM TERSEMBUNYI BATALKAN TIDAK IKUT -->
        <form id="formBatalkanTidakIkutTersembunyi" action="{{ route('persyaratan-ujian.batalkan-tidak-ikut') }}"
            method="POST" class="hidden">
            @csrf
            <input type="hidden" name="ujian_id" id="hidBatalUjianId">
            <input type="hidden" name="murid_id" id="hidBatalMuridId">
        </form>

        <script>
            // 1. SCRIPT DISPENSASI
            function pemicuDispensasi(ujianId, muridId, namaMurid) {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: '<span class="text-base font-black text-zinc-900 dark:text-white">Buka Izin Akses?</span>',
                    html: `<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-3">Ketikkan alasan pemberian dispensasi darurat input nilai untuk murid <b class="text-amber-500">${namaMurid}</b>:</p>`,
                    input: 'text',
                    inputPlaceholder: 'Contoh: Wali murid minta tempo...',
                    icon: 'question',
                    showCancelButton: true,
                    heightAuto: false,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
                    confirmButtonText: '<i class="bi bi-unlock-fill mr-1"></i> Beri Akses',
                    cancelButtonText: '<span class="text-zinc-700 dark:text-zinc-300">Batal</span>',
                    background: isDark ? '#09090b' : '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2 text-xs',
                        cancelButton: 'rounded-xl font-bold px-5 py-2 text-xs',
                        input: 'rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 font-bold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 text-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('hidDispensasiUjianId').value = ujianId;
                        document.getElementById('hidDispensasiMuridId').value = muridId;
                        document.getElementById('hidDispensasiAlasan').value = result.value ||
                            'Izin Orang Tua Keadaan Tidak Mampu';

                        Swal.fire({
                            title: '<span class="text-sm font-bold text-zinc-900 dark:text-white">Membuka Akses...</span>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            background: isDark ? '#09090b' : '#ffffff',
                            heightAuto: false,
                            customClass: {
                                popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl'
                            },
                            didOpen: () => Swal.showLoading()
                        });

                        document.getElementById('formDispensasiTersembunyi').submit();
                    }
                });
            }

            // 2. SCRIPT TANDAI TIDAK IKUT UJIAN
            function pemicuTandaiTidakIkut(ujianId, muridId, namaMurid) {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: '<span class="text-base font-black text-zinc-900 dark:text-white">Tandai Tidak Ikut Ujian?</span>',
                    html: `<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-3">Murid <b class="text-rose-500">${namaMurid}</b> akan dikecualikan dari target nilai ujian dan rapor pada agenda ini.<br><br>Ketikkan alasan (opsional):</p>`,
                    input: 'text',
                    inputPlaceholder: 'Contoh: Sakit / Cuti / Berhalangan...',
                    icon: 'warning',
                    showCancelButton: true,
                    heightAuto: false,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
                    confirmButtonText: '<i class="bi bi-person-x-fill mr-1"></i> Tandai Tidak Ikut',
                    cancelButtonText: '<span class="text-zinc-700 dark:text-zinc-300">Batal</span>',
                    background: isDark ? '#09090b' : '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2 text-xs',
                        cancelButton: 'rounded-xl font-bold px-5 py-2 text-xs',
                        input: 'rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 font-bold outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('hidTidakIkutUjianId').value = ujianId;
                        document.getElementById('hidTidakIkutMuridId').value = muridId;
                        document.getElementById('hidTidakIkutAlasan').value = result.value || 'Tidak Mengikuti Ujian';

                        Swal.fire({
                            title: '<span class="text-sm font-bold text-zinc-900 dark:text-white">Memproses...</span>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            background: isDark ? '#09090b' : '#ffffff',
                            heightAuto: false,
                            customClass: {
                                popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl'
                            },
                            didOpen: () => Swal.showLoading()
                        });

                        document.getElementById('formTandaiTidakIkutTersembunyi').submit();
                    }
                });
            }

            // 3. SCRIPT BATALKAN TIDAK IKUT UJIAN
            function pemicuBatalkanTidakIkut(ujianId, muridId, namaMurid) {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: '<span class="text-base font-black text-zinc-900 dark:text-white">Ikutkan Ujian Kembali?</span>',
                    html: `<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Kembalikan murid <b class="text-sky-500">${namaMurid}</b> menjadi peserta ujian pada agenda ini?</p>`,
                    icon: 'question',
                    showCancelButton: true,
                    heightAuto: false,
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
                    confirmButtonText: '<i class="bi bi-arrow-counterclockwise mr-1"></i> Ya, Ikutkan Ujian',
                    cancelButtonText: '<span class="text-zinc-700 dark:text-zinc-300">Batal</span>',
                    background: isDark ? '#09090b' : '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2 text-xs',
                        cancelButton: 'rounded-xl font-bold px-5 py-2 text-xs',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('hidBatalUjianId').value = ujianId;
                        document.getElementById('hidBatalMuridId').value = muridId;

                        Swal.fire({
                            title: '<span class="text-sm font-bold text-zinc-900 dark:text-white">Mengembalikan Status...</span>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            background: isDark ? '#09090b' : '#ffffff',
                            heightAuto: false,
                            customClass: {
                                popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl'
                            },
                            didOpen: () => Swal.showLoading()
                        });

                        document.getElementById('formBatalkanTidakIkutTersembunyi').submit();
                    }
                });
            }
        </script>
    @else
        <!-- STATE AWAL / KOSONG -->
        <x-empty-state icon="bi-people" title="Pilih Ruangan/Kelas dan Tipe Ujian"
            message="Tentukan ruangan dan ujian pada filter di atas untuk memunculkan data persyaratan murid." />
    @endif

</x-app-layout>
