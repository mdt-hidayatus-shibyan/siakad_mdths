@section('title', 'Rapor')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Cetak & Sahkan Rapor
            </h2>
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 uppercase tracking-wider">
                Verifikasi kelengkapan nilai, pratinjau, dan bekukan arsip rapor murid
            </p>
        </div>
    </div>

    <!-- PANEL FILTER -->
    <div class="m3-glass-card p-4 sm:p-5 mb-6 relative z-10 animate-[modalFadeIn_0.2s_ease-out]">
        <form action="{{ request()->url() }}" method="GET" id="formRapor"
            class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-3.5 items-end">

            <!-- 1. Tahun Pelajaran -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Tahun
                    Pelajaran</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-calendar-range text-sm"></i>
                    </div>
                    <select name="tahun_id" onchange="document.getElementById('formRapor').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer">
                        @foreach ($daftarTahun as $t)
                            <option value="{{ $t->id }}" {{ $tahunPelajaranId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_hijriyah }} H - {{ $t->nama_masehi }} M
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Pilih Kelas -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Pilih
                    Ruangan</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-door-open text-sm"></i>
                    </div>
                    <select name="ruangan_id" onchange="document.getElementById('formRapor').submit()"
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
            </div>

            <!-- 3. Pilih Ujian Semester -->
            <div class="relative group/select">
                <label
                    class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Agenda
                    Ujian</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                        <i class="bi bi-file-earmark-check text-sm"></i>
                    </div>
                    <select name="ujian_id" required {{ $daftarUjian->isEmpty() ? 'disabled' : '' }}
                        onchange="document.getElementById('formRapor').submit()"
                        class="m3-input-glass w-full !pl-9 !pr-9 appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="">-- Pilih Pelaksanaan Ujian --</option>
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
            </div>

        </form>
    </div>

    <!-- AREA TABEL CETAK RAPOR -->
    @if (request('ruangan_id') && request('ujian_id'))
        @php
            $isAkhirTahun = in_array($ujianTerpilih->tipe_ujian ?? '', ['IMDA 2', 'IMNI']);
        @endphp

        <div class="m3-glass-card overflow-hidden relative group animate-[modalFadeIn_0.2s_ease-out]">

            <!-- FORM BULK -->
            <form action="{{ route('rapor.arsipkan_bulk') }}" method="POST" id="formSahkanRapor"
                class="relative z-10 flex flex-col h-full">
                @csrf
                <input type="hidden" name="ujian_id" value="{{ $ujianTerpilih->id }}">

                <div class="overflow-x-auto relative z-10 custom-scrollbar p-0">
                    <table class="w-full text-left border-collapse text-xs min-w-[750px]">
                        <thead
                            class="bg-zinc-50/90 dark:bg-zinc-950/90 border-b border-zinc-200/80 dark:border-zinc-800 sticky top-0 z-20">
                            <tr
                                class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                <!-- Header Checkbox Semua -->
                                <th
                                    class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 text-center w-12 sticky left-0 z-30 bg-zinc-100/90 dark:bg-zinc-900/90 backdrop-blur-md">
                                    <input type="checkbox" id="checkAllRapor"
                                        class="rounded border-zinc-300 dark:border-zinc-700 text-primary dark:text-primary-dark focus:ring-primary dark:focus:ring-primary-dark dark:bg-zinc-800 cursor-pointer w-4 h-4">
                                </th>
                                <th
                                    class="py-3 px-3.5 border-r border-zinc-200/80 dark:border-zinc-800 w-28 text-center">
                                    NISM</th>
                                <th class="py-3 px-4 border-r border-zinc-200/80 dark:border-zinc-800">Nama Lengkap
                                    Murid
                                </th>
                                <th
                                    class="py-3 px-3.5 text-center border-r border-zinc-200/80 dark:border-zinc-800 w-36">
                                    Status Dokumen</th>
                                <th class="py-3 px-3.5 text-center w-40">Aksi & Pengesahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/80 dark:divide-zinc-800/80 bg-white dark:bg-zinc-900">
                            @foreach ($murids as $index => $murid)
                                @php
                                    $arsip = $arsipRapor->get($murid->id);
                                    $riwayatKenaikan = null;
                                    if ($isAkhirTahun && !$arsip) {
                                        $riwayatKenaikan = $riwayatKenaikans->get($murid->id);
                                    }
                                    $isTidakIkut = $pengecualianMurids->has($murid->id);
                                    $alasanTidakIkut = $isTidakIkut
                                        ? $pengecualianMurids->get($murid->id)->alasan ?? 'Tidak Mengikuti Ujian'
                                        : null;
                                    $totalNilaiPublish = $nilaiCountMap[$murid->id] ?? 0;
                                    $hasNilai = $totalNilaiPublish > 0;
                                @endphp

                                <tr
                                    class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group/row {{ $arsip ? 'bg-emerald-50/10 dark:bg-emerald-950/10' : ($isTidakIkut ? 'bg-zinc-100/30 dark:bg-zinc-800/20 opacity-75' : '') }}">

                                    <!-- Sel Checkbox -->
                                    <td
                                        class="py-2.5 px-3.5 text-center sticky left-0 z-20 bg-white dark:bg-zinc-900 group-hover/row:bg-zinc-50 dark:group-hover/row:bg-zinc-800/80 border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        @if ($arsip)
                                            <i class="bi bi-shield-lock-fill text-emerald-500 text-sm"
                                                title="Telah Diarsipkan"></i>
                                        @elseif ($isTidakIkut)
                                            <i class="bi bi-person-x-fill text-zinc-400 text-sm"
                                                title="Tidak Mengikuti Ujian ({{ $alasanTidakIkut }})"></i>
                                        @elseif (!$hasNilai)
                                            <i class="bi bi-exclamation-circle text-amber-400 text-sm"
                                                title="Belum Memiliki Nilai Rilis"></i>
                                        @elseif ($isAkhirTahun && !$riwayatKenaikan)
                                            <i class="bi bi-lock-fill text-zinc-400 text-sm"
                                                title="Terkunci (Butuh SK Kenaikan)"></i>
                                        @else
                                            <input type="checkbox" name="selected_murid[]" value="{{ $murid->id }}"
                                                class="row-checkbox rounded border-zinc-300 dark:border-zinc-700 text-primary dark:text-primary-dark focus:ring-primary dark:focus:ring-primary-dark dark:bg-zinc-800 cursor-pointer w-4 h-4">
                                        @endif
                                    </td>

                                    <!-- NISM -->
                                    <td
                                        class="py-2.5 px-3.5 text-center font-bold text-zinc-500 dark:text-zinc-400 tracking-wider text-xs border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        {{ $murid->nism ?? '-' }}
                                    </td>

                                    <!-- Nama Lengkap -->
                                    <td
                                        class="py-2.5 px-4 font-black {{ $isTidakIkut ? 'text-zinc-500 dark:text-zinc-400 line-through' : 'text-zinc-900 dark:text-zinc-100' }} text-xs tracking-tight border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        {{ $murid->nama_lengkap }}
                                    </td>

                                    <!-- Status Dokumen -->
                                    <td
                                        class="py-2.5 px-3.5 text-center border-r border-zinc-200/80 dark:border-zinc-800 align-middle">
                                        @if ($arsip)
                                            <span
                                                class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider">
                                                <i class="bi bi-shield-lock-fill text-xs"></i> Diarsipkan
                                            </span>
                                        @elseif ($isTidakIkut)
                                            <span
                                                class="inline-flex items-center gap-1 bg-zinc-200/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider"
                                                title="{{ $alasanTidakIkut }}">
                                                <i class="bi bi-person-x-fill text-xs text-rose-500"></i> Tidak Ikut
                                            </span>
                                        @elseif (!$hasNilai)
                                            <span
                                                class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider">
                                                <i class="bi bi-dash-circle text-xs"></i> Nilai Kosong
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200/80 dark:border-sky-800/40 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider">
                                                <i class="bi bi-file-earmark-text text-xs"></i> Siap Disahkan
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-2.5 px-3.5 align-middle">
                                        <div class="flex flex-col items-center justify-center gap-1">
                                            @if ($arsip)
                                                <!-- Tombol Cetak -->
                                                <a href="{{ route('arsip.cetak', $arsip->id) }}" target="_blank"
                                                    class="inline-flex items-center justify-center px-3 h-8 bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-600 text-white font-black text-[10px] uppercase tracking-wider rounded-lg transition-transform active:scale-95 shadow-2xs w-full max-w-[110px]">
                                                    <i class="bi bi-printer-fill mr-1 text-xs"></i> Cetak
                                                </a>
                                            @elseif ($isTidakIkut)
                                                <!-- Tidak Ikut Ujian -->
                                                <button type="button" disabled
                                                    class="inline-flex items-center justify-center px-3 h-8 bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 font-bold text-[10px] uppercase tracking-wider rounded-lg cursor-not-allowed shadow-2xs w-full max-w-[110px] border border-transparent">
                                                    <i class="bi bi-dash-circle mr-1 text-xs"></i> Tidak Ikut
                                                </button>
                                            @elseif (!$hasNilai)
                                                <!-- Nilai Belum Rilis -->
                                                <button type="button" disabled
                                                    class="inline-flex items-center justify-center px-3 h-8 bg-amber-50/50 dark:bg-amber-950/20 text-amber-500 dark:text-amber-400 font-bold text-[10px] uppercase tracking-wider rounded-lg cursor-not-allowed shadow-2xs w-full max-w-[110px] border border-amber-200/50">
                                                    <i class="bi bi-pencil mr-1 text-xs"></i> Belum Dinilai
                                                </button>
                                            @else
                                                @if ($isAkhirTahun && !$riwayatKenaikan)
                                                    <!-- Terkunci (Butuh SK Kenaikan Dulu) -->
                                                    <button type="button" onclick="peringatanSK()"
                                                        class="inline-flex items-center justify-center px-3 h-8 bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 font-bold text-[10px] uppercase tracking-wider rounded-lg cursor-not-allowed shadow-2xs w-full max-w-[110px] transition-colors border border-transparent">
                                                        <i class="bi bi-lock-fill mr-1 text-xs"></i> Terkunci
                                                    </button>
                                                    <span
                                                        class="text-[9px] font-black text-rose-500 tracking-wide text-center uppercase leading-tight">
                                                        *Sahkan SK Dulu
                                                    </span>
                                                @else
                                                    <!-- Tombol Sahkan Individu -->
                                                    <button type="button"
                                                        onclick="sahkanIndividuRapor({{ $murid->id }})"
                                                        class="m3-btn-primary h-8 px-3 text-[10px] w-full max-w-[110px] uppercase tracking-wider font-black">
                                                        <i class="bi bi-check-circle-fill text-xs"></i> Sahkan
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER / TOMBOL SIMPAN MASSAL -->
                @if (count($murids) > 0)
                    <div
                        class="px-5 py-3.5 border-t border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-950/60 flex justify-end shrink-0">
                        <button type="button" onclick="konfirmasiSahkanRapor('bulk')"
                            class="m3-btn-primary h-10 px-5 group/btn">
                            <i class="bi bi-check-all text-lg leading-none"></i>
                            <span>Sahkan Terpilih</span>
                        </button>
                    </div>
                @else
                    <div class="py-12 text-center text-zinc-500 dark:text-zinc-400 font-semibold text-xs">
                        Belum ada data murid di kelas ini.
                    </div>
                @endif
            </form>
        </div>

        <!-- SCRIPT ALERT & LOGIKA CHECKBOX -->
        <script>
            // Logika Check All
            document.getElementById('checkAllRapor')?.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });

            // Sahkan Individu
            function sahkanIndividuRapor(muridId) {
                // Hilangkan centang pada baris lain
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);

                // Centang khusus baris yang ditekan
                const targetCheckbox = document.querySelector(`.row-checkbox[value="${muridId}"]`);
                if (targetCheckbox) {
                    targetCheckbox.checked = true;
                }

                // Tampilkan Alert Konfirmasi
                konfirmasiSahkanRapor('individu');
            }

            // Peringatan SK Belum Ada
            function peringatanSK() {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    icon: 'error',
                    title: '<span class="text-base font-black text-zinc-900 dark:text-white">Pengesahan Terkunci!</span>',
                    html: '<p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">Untuk ujian akhir tahun <b>(IMDA 2 / IMNI)</b>, Anda wajib merumuskan dan mengesahkan keputusan status Kenaikan/Kelulusan Murid di menu <b>Kenaikan Kelas</b> terlebih dahulu.</p>',
                    confirmButtonColor: '#e11d48',
                    heightAuto: false,
                    background: isDark ? '#09090b' : '#ffffff',
                    confirmButtonText: '<i class="bi bi-x-circle-fill mr-1"></i> Mengerti',
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-xs'
                    }
                });
            }

            // Konfirmasi Form Bulk / Individu
            function konfirmasiSahkanRapor(mode = 'bulk') {
                const isDark = document.documentElement.classList.contains('dark');

                // Validasi Data Dicentang
                const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (selectedCount === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: '<span class="text-base font-black text-zinc-900 dark:text-white">Pilih Murid!</span>',
                        html: '<p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Silakan centang minimal satu murid yang ingin disahkan rapornya.</p>',
                        confirmButtonColor: '#059669',
                        heightAuto: false,
                        background: isDark ? '#09090b' : '#ffffff',
                        customClass: {
                            popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                            confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-xs'
                        }
                    });
                    return;
                }

                const titleText = mode === 'individu' ? 'Sahkan Rapor Murid Ini?' : `Sahkan ${selectedCount} Rapor Terpilih?`;

                Swal.fire({
                    title: `<span class="text-base font-black text-zinc-900 dark:text-white">${titleText}</span>`,
                    html: '<p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1 mb-2 leading-relaxed">Rapor yang disahkan akan dibekukan secara permanen sebagai arsip dan tidak akan berubah meskipun master nilai diubah di masa depan.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    heightAuto: false,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
                    confirmButtonText: '<i class="bi bi-shield-check mr-1"></i> Ya, Sahkan!',
                    cancelButtonText: '<span class="text-zinc-700 dark:text-zinc-300">Batal</span>',
                    background: isDark ? '#09090b' : '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-xs',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5 text-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: '<span class="text-sm font-bold text-zinc-900 dark:text-white">Menyimpan Arsip...</span>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            background: isDark ? '#09090b' : '#ffffff',
                            customClass: {
                                popup: 'rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl'
                            },
                            didOpen: () => Swal.showLoading()
                        });

                        document.getElementById('formSahkanRapor').submit();
                    }
                });
            }
        </script>
    @else
        <!-- State Awal -->
        <x-empty-state icon="bi-journal-text" title="Pengesahan Dokumen Rapor"
            message="Tentukan Ruangan dan Agenda Ujian pada filter di atas untuk memuat daftar murid, melakukan pratinjau, dan mengesahkan dokumen Rapor." />
    @endif

</x-app-layout>
