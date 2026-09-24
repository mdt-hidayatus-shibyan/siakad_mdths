@section('title', 'Daftar Anggota ' . $ruangan->nama_ruangan)

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('rombongan-belajar.index') }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none"
                title="Kembali ke Rombongan Belajar">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    {{ $ruangan->nama_ruangan }}
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Daftar anggota kelas. Kapasitas:
                    <strong
                        class="px-2 py-0.5 rounded-lg bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 dark:border-primary-dark/30 ml-1 text-xs font-black">
                        {{ $ruangan->murids->count() }} / {{ $ruangan->kapasitas }} Murid
                    </strong>
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
            @can('create rombongan-belajar')
                <!-- Tombol Plotting -->
                <a href="{{ route('rombongan-belajar.plotting-kenaikan', $ruangan->id) }}"
                    class="h-10 inline-flex items-center justify-center px-4 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 border border-sky-200/80 dark:border-sky-800/40 text-xs font-black transition-all hover:scale-[1.02] active:scale-95 outline-none w-full sm:w-auto shrink-0">
                    <i class="bi bi-arrow-up-right-circle-fill mr-1.5 text-sm"></i>
                    <span>Plotting Kenaikan</span>
                </a>

                <!-- Tombol Tambah Murid -->
                <button onclick="openDrawer()" class="m3-btn-primary px-4 w-full sm:w-auto group/btn shrink-0">
                    <i class="bi bi-person-plus-fill text-sm"></i>
                    <span>Tambah Murid</span>
                </button>
                <div class="relative inline-block text-left dropdown-container z-10">
                    <button type="button" data-dropdown-toggle="dropdownOpsiData"
                        class="w-full sm:w-auto h-10 inline-flex items-center justify-center px-3.5 rounded-xl bg-zinc-100/80 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200/80 dark:hover:bg-zinc-800 text-xs font-bold transition-all outline-none border border-zinc-200/80 dark:border-zinc-800 shadow-sm active:scale-95">
                        <i class="bi bi-three-dots text-base"></i>
                    </button>
                    <div id="dropdownOpsiData" class="m3-dropdown-menu hidden right-0 left-0 sm:left-auto">

                        <a href="{{ route('rombongan-belajar.export-anggota', $ruangan->id) }}"
                            class="m3-dropdown-item hover:!text-emerald-600 dark:hover:!text-emerald-400">
                            <i class="bi bi-file-earmark-excel mr-2.5 text-base"></i> Export Excel
                        </a>

                        <a href="{{ route('rombongan-belajar.print-anggota', $ruangan->id) }}" target="_blank"
                            class="m3-dropdown-item hover:!text-blue-600 dark:hover:!text-blue-400">
                            <i class="bi bi-printer mr-2.5 text-base"></i> Print Data
                        </a>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    <!-- Table Container -->
    <div id="data-table-container" class="m3-glass-card overflow-hidden flex flex-col relative z-10">
        <div
            class="p-4 sm:p-4.5 border-b border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-black/30 flex flex-col sm:flex-row justify-between items-center gap-3 transition-colors duration-300">

            <div class="w-full sm:w-auto">
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary dark:text-primary-dark"></i> Daftar Anggota
                </h3>
            </div>

            <form action="{{ route('rombongan-belajar.anggota', $ruangan->id) }}" method="GET"
                class="flex flex-col sm:flex-row gap-2.5 w-full sm:w-auto group/search">
                <!-- Input Pencarian -->
                <div class="relative w-full sm:w-64 group/search">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-300 text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark">
                        <i class="bi bi-search text-sm"></i>
                    </div>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari NISM, Nama..." class="m3-input-glass w-full !pl-10 !pr-10">

                    @if (request('search'))
                        <a href="{{ route('rombongan-belajar.anggota', $ruangan->id) }}"
                            class="absolute inset-y-0 right-0 w-10 h-10 flex items-center justify-center text-zinc-400 hover:text-red-600 dark:text-zinc-500 dark:hover:text-red-400 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 rounded-xl transition-colors duration-200 outline-none"
                            title="Reset Pencarian">
                            <i class="bi bi-x-lg text-xs font-bold"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>


        <div class="overflow-x-auto custom-scrollbar relative z-10">
            <table class="m3-table">
                <thead>
                    <tr>
                        <th scope="col" class="text-center w-12">No</th>
                        <th scope="col" class="w-50">Nama Murid</th>
                        <th scope="col" class="text-center w-12">L/P</th>
                        <th scope="col">Umur</th>
                        <th scope="col">Orang Tua</th>
                        <th scope="col">Kampung</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ruangan->murids as $murid)
                        @php
                            $defaultFoto = $murid->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png';
                            $fotoPath = $murid->foto ? $murid->foto : $defaultFoto;

                            $umur = 0;
                            if ($murid->tanggal_lahir) {
                                $umur = \Carbon\Carbon::parse($murid->tanggal_lahir)->age;
                            }

                            $isYatim = $murid->status_ayah == 'Meninggal' && $umur < 15;
                            $isPiatu = $murid->status_ibu == 'Meninggal' && $umur < 15;
                        @endphp

                        <!-- Jika status tidak aktif, tambahkan class opacity/grayscale di baris ini -->
                        <tr class="{{ $murid->status !== 'Aktif' ? 'opacity-70 grayscale-[30%]' : '' }}">

                            <!-- KOLOM NO -->
                            <td class="text-center">
                                <span
                                    class="w-9 h-9 mx-auto flex items-center justify-center bg-zinc-50 dark:bg-black text-primary dark:text-primary-dark rounded-xl text-[13px] font-bold border border-zinc-200 dark:border-zinc-800 flex-shrink-0">
                                    {{ $loop->iteration }}
                                </span>

                            </td>

                            <!-- KOLOM PROFIL (Foto & Nama) -->
                            <td>
                                <div class="flex items-center gap-3">
                                    @can('update murid')
                                        <a href="{{ route('rombongan-belajar.uploadFoto', [$ruangan->id, $murid->id]) }}"
                                            class="action-modal w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-sm relative cursor-pointer group/avatar block"
                                            title="Ubah Foto {{ $murid->nama_panggilan }}">
                                            <img src="{{ asset('storage/' . $fotoPath) }}"
                                                alt="Foto {{ $murid->nama_panggilan }}"
                                                class="w-full h-full object-cover rounded-lg">
                                            <!-- Overlay Ubah Foto -->
                                            <div
                                                class="absolute inset-0.5 rounded-lg bg-black/60 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-300">
                                                <i class="bi bi-camera-fill text-white text-base"></i>
                                            </div>
                                        </a>
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-sm relative">
                                            <img src="{{ asset('storage/' . $fotoPath) }}"
                                                alt="Foto {{ $murid->nama_panggilan }}"
                                                class="w-full h-full object-cover rounded-lg">
                                        </div>
                                    @endcan
                                    <div>
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white tracking-tight truncate max-w-[200px]"
                                                title="{{ $murid->nama_lengkap }}">
                                                {{ $murid->nama_lengkap }}
                                            </h4>
                                        </div>
                                        <span
                                            class=" text-sm font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
                                            <i class="bi bi-upc-scan"></i> <span
                                                class="text-zinc-800 dark:text-zinc-300">{{ $murid->nism }}</span>
                                        </span>

                                    </div>
                                </div>
                            </td>

                            <!-- KOLOM GENDER -->
                            <td class="text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-[11px] font-bold {{ $murid->jenis_kelamin == 'L' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30' : 'bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400 border border-pink-200 dark:border-pink-800/30' }}">
                                    {{ $murid->jenis_kelamin }}
                                </span>
                            </td>

                            <!-- KOLOM UMUR & STATUS YATIM -->
                            <td>
                                <div class="flex flex-col items-start gap-1.5">
                                    @if ($umur > 0)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-black text-[9px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest shadow-sm">
                                            <i class="bi bi-hourglass-split"></i> {{ $umur }} Thn
                                        </span>
                                    @endif

                                    @if ($isYatim && $isPiatu)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-widest bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800/30 text-purple-600 dark:text-purple-400">
                                            <i class="bi bi-heartbreak-fill mr-1"></i> Yatim Piatu
                                        </span>
                                    @elseif($isYatim)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-widest bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800/30 text-orange-600 dark:text-orange-400">
                                            <i class="bi bi-heartbreak mr-1"></i> Yatim
                                        </span>
                                    @elseif($isPiatu)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-widest bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 text-amber-600 dark:text-amber-400">
                                            <i class="bi bi-heartbreak mr-1"></i> Piatu
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- KOLOM AYAH -->
                            <td>
                                <div class="flex flex-col items-start gap-1.5">

                                    <span
                                        class="text-[13px] font-bold text-zinc-900 dark:text-white tracking-tight truncate max-w-[150px] hover:text-primary transition-colors"
                                        title="Lihat Profil Keluarga">
                                        {{ $murid->nama_ayah ?: '-' }}
                                    </span>

                                    <span
                                        class="text-[13px] font-bold text-zinc-900 dark:text-white tracking-tight truncate max-w-[150px]">
                                        {{ $murid->nama_ibu ?: '-' }}
                                    </span>
                                </div>
                            </td>



                            <!-- KOLOM KAMPUNG -->
                            <td>
                                <span class="text-[13px] font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $murid->waliMurid->kampung->nama_kampung ?? '-' }}
                                </span>
                            </td>

                            <!-- KOLOM STATUS AKTIF -->
                            <td class="text-center">
                                @can('update murid')
                                    <a href="{{ route('murid.modalStatus', $murid->id) }}"
                                        class="action-modal inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest min-w-[70px] justify-center hover:scale-105 active:scale-95 transition-all outline-none cursor-pointer shadow-2xs
                                        {{ $murid->status == 'Aktif' ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 text-emerald-600 dark:text-emerald-400' : '' }}
                                        {{ $murid->status == 'Lulus' ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 text-blue-600 dark:text-blue-400' : '' }}
                                        {{ $murid->status == 'Pindah' ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 text-amber-600 dark:text-amber-400' : '' }}
                                        {{ $murid->status == 'Berhenti' ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 text-red-600 dark:text-red-400' : '' }}
                                        {{ $murid->status == 'Meninggal' ? 'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300' : '' }}
                                        "
                                        title="Ubah Status {{ $murid->nama_lengkap }}">
                                        @if ($murid->status == 'Aktif')
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                                        @elseif($murid->status == 'Lulus')
                                            <i class="bi bi-mortarboard-fill mr-1.5"></i> Lulus
                                        @elseif($murid->status == 'Pindah')
                                            <i class="bi bi-arrow-left-right mr-1.5"></i> Pindah
                                        @elseif($murid->status == 'Berhenti')
                                            <i class="bi bi-x-circle-fill mr-1.5"></i> Berhenti
                                        @else
                                            <i class="bi bi-heartbreak-fill mr-1.5"></i> Meninggal
                                        @endif
                                    </a>
                                @else
                                    <!-- Tampilan SPAN biasa untuk user yang tidak punya akses edit -->
                                    @if ($murid->status == 'Aktif')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 text-emerald-600 dark:text-emerald-400 min-w-[70px] justify-center">
                                            <span class="w-1.5 h-1.5 rounded-xl bg-emerald-500 mr-1.5"></span> Aktif
                                        </span>
                                    @elseif($murid->status == 'Lulus')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 text-blue-600 dark:text-blue-400 min-w-[70px] justify-center">
                                            <i class="bi bi-mortarboard-fill mr-1.5"></i> Lulus
                                        </span>
                                    @elseif($murid->status == 'Pindah')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 text-amber-600 dark:text-amber-400 min-w-[70px] justify-center">
                                            <i class="bi bi-arrow-left-right mr-1.5"></i> Pindah
                                        </span>
                                    @elseif($murid->status == 'Berhenti')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 text-red-600 dark:text-red-400 min-w-[70px] justify-center">
                                            <i class="bi bi-x-circle-fill mr-1.5"></i> Berhenti
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-black-50 dark:bg-black-900/20 border border-black-200 dark:border-black-800/30 text-white-600 dark:text-white-400 min-w-[70px] justify-center">
                                            <i class="bi bi-x-circle-fill mr-1.5"></i> Meninggal
                                        </span>
                                    @endif
                                @endcan
                            </td>

                            <!-- KOLOM AKSI -->
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @can('update rombongan-belajar')
                                        <!-- Tombol Pindah (AJAX Modal M3) -->
                                        <a href="{{ route('rombongan-belajar.modalPindah', [$ruangan->id, $murid->id]) }}"
                                            class="action-modal inline-flex w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all active:scale-95 outline-none shadow-2xs"
                                            title="Pindah Ruangan">
                                            <i class="bi bi-arrow-left-right text-[13px]"></i>
                                        </a>
                                    @endcan

                                    @can('delete rombongan-belajar')
                                        <form id="form-detach-{{ $murid->id }}"
                                            action="{{ route('rombongan-belajar.detach-anggota', ['id' => $ruangan->id, 'murid_id' => $murid->id]) }}"
                                            method="POST" class="m-0">
                                            @csrf
                                            <button type="button"
                                                onclick="confirmDetach('{{ $murid->id }}', '{{ addslashes($murid->nama_lengkap) }}')"
                                                class="inline-flex w-8 h-8 rounded-xl bg-red-50 dark:bg-red-900/20 border border-transparent dark:border-red-800/30 text-red-600 dark:text-red-400 items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/40 transition-color outline-none"
                                                title="Keluarkan dari Ruangan">
                                                <i class="bi bi-x-lg text-[13px] font-black"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12">
                                <div
                                    class="text-center flex flex-col items-center justify-center relative overflow-hidden">
                                    <div
                                        class="w-16 h-16 bg-zinc-50 dark:bg-black border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4 transition-colors duration-300">
                                        <i class="bi bi-person-badge text-3xl"></i>
                                    </div>
                                    <h3
                                        class="text-lg md:text-xl font-bold text-zinc-900 dark:text-white tracking-tight">
                                        Tidak Ada Data Murid
                                    </h3>
                                    <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-1">
                                        Belum ada Murid dengan status "<span
                                            class="text-primary dark:text-primary-dark font-bold"></span>"
                                        atau
                                        coba kata kunci lain.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- DRAWER TAMBAH MURID -->
    <!-- ============================================== -->
    <div id="drawerTambahMurid" class="fixed inset-0 z-[100] hidden justify-end">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-zinc-900/60 dark:bg-black/80 backdrop-blur-sm transition-opacity"
            onclick="closeDrawer()"></div>

        <!-- Drawer Panel -->
        <div
            class="relative w-full max-w-md h-full bg-white dark:bg-[#0c0c0e] shadow-2xl border-l border-zinc-200 dark:border-zinc-800 flex flex-col animate-[slideLeft_0.3s_cubic-bezier(0.16,1,0.3,1)]">

            <form action="{{ route('rombongan-belajar.attach-anggota', $ruangan->id) }}" method="POST"
                class="flex flex-col h-full w-full relative z-10">
                @csrf

                <!-- Header Drawer -->
                <div
                    class="shrink-0 px-5 sm:px-6 py-4.5 border-b border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/80 dark:bg-black/40">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight">Pilih Murid
                            </h3>
                            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">Pilih dari
                                antrean kelas kosong.</p>
                        </div>
                        <button type="button" onclick="closeDrawer()"
                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-100/80 dark:bg-zinc-800 text-zinc-500 hover:text-red-500 transition-all outline-none shrink-0">
                            <i class="bi bi-x-lg text-xs font-bold"></i>
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative group/search">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 group-focus-within/search:text-primary dark:group-focus-within/search:text-primary-dark">
                            <i class="bi bi-search text-xs font-bold"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari nama atau NISM..."
                            class="m3-input-glass w-full !pl-10">
                    </div>
                </div>

                <!-- List Area -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-5 bg-transparent custom-scrollbar">
                    <div class="flex flex-col gap-2" id="muridList">
                        @forelse($muridsTersedia as $murid)
                            <label class="murid-item cursor-pointer block group/check"
                                data-nama="{{ strtolower($murid->nama_lengkap) }}"
                                data-nism="{{ strtolower($murid->nism) }}">

                                <input type="checkbox" name="murid_ids[]" value="{{ $murid->id }}"
                                    class="peer sr-only">

                                <!-- List Item M3 -->
                                <div
                                    class="p-3 rounded-xl bg-zinc-50/80 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800/80
                                    peer-checked:bg-primary/5 dark:peer-checked:bg-primary-dark/10
                                    peer-checked:border-primary/40 dark:peer-checked:border-primary-dark/40
                                    peer-checked:[&_.kotak-centang]:bg-primary peer-checked:[&_.kotak-centang]:border-primary dark:peer-checked:[&_.kotak-centang]:bg-primary-dark dark:peer-checked:[&_.kotak-centang]:border-primary-dark
                                    peer-checked:[&_.ikon-centang]:opacity-100 peer-checked:[&_.ikon-centang]:scale-100
                                    transition-colors duration-200 flex items-center gap-3 hover:border-zinc-300 dark:hover:border-zinc-700 relative overflow-hidden">

                                    <!-- Custom Checkbox -->
                                    <div
                                        class="kotak-centang w-5 h-5 rounded-md flex items-center justify-center border-2 border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 transition-colors duration-200 shrink-0">
                                        <i
                                            class="ikon-centang bi bi-check-lg opacity-0 scale-50 transition-all duration-200 font-black text-white dark:text-zinc-900 text-xs"></i>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-xs sm:text-sm font-black text-zinc-900 dark:text-white truncate tracking-tight mb-0.5">
                                            {{ $murid->nama_lengkap }}
                                        </h4>
                                        <p
                                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            NISM: {{ $murid->nism }}
                                        </p>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div
                                class="py-12 flex flex-col items-center justify-center text-center bg-zinc-50/50 dark:bg-zinc-900/20 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                <div
                                    class="w-12 h-12 bg-white dark:bg-black border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-center justify-center mb-3">
                                    <i class="bi bi-person-check-fill text-xl text-zinc-400 dark:text-zinc-500"></i>
                                </div>
                                <h4 class="text-sm font-bold text-zinc-900 dark:text-white tracking-tight">Semua Masuk
                                    Kelas</h4>
                                <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1 px-4">Seluruh
                                    murid aktif telah terdaftar ke dalam rombongan belajar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Footer Drawer -->
                <div
                    class="shrink-0 p-4 border-t border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/80 dark:bg-black/40">
                    <button type="submit" class="m3-btn-primary w-full py-3 group/btn outline-none">
                        <i class="bi bi-person-plus-fill text-sm"></i>
                        <span>Tambahkan Terpilih</span>
                    </button>
                    <button type="button" onclick="closeDrawer()"
                        class="w-full mt-2 py-2.5 rounded-xl font-extrabold text-xs text-zinc-600 dark:text-zinc-400 bg-zinc-100/80 hover:bg-zinc-200/80 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors outline-none">
                        Tutup Panel
                    </button>
                </div>
            </form>
        </div>
    </div>










    <!-- Styling Animations -->
    <style>
        @keyframes slideLeft {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>

    <!-- Scripts Data -->
    @push('script')
        <script>
            // === KONFIGURASI SWEETALERT M3 ===
            const swalCustomClass = {
                popup: 'rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl',
                title: 'text-xl font-bold text-zinc-900 dark:text-white tracking-tight',
                htmlContainer: 'text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-2',
                actions: "gap-3 mt-5",
                confirmButton: "rounded-xl px-6 py-2.5 bg-primary dark:bg-primary-dark hover:bg-primary/90 dark:hover:bg-primary-dark/90 text-white dark:text-black font-bold text-sm transition-all outline-none",
                cancelButton: "rounded-xl px-6 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-sm transition-all outline-none border border-transparent dark:border-zinc-700"
            };
            const swalColor = document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b';

            // === FUNGSI LACI TAMBAH MURID ===
            function openDrawer() {
                const drawer = document.getElementById('drawerTambahMurid');
                drawer.classList.remove('hidden');
                drawer.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                const drawer = document.getElementById('drawerTambahMurid');
                drawer.classList.add('hidden');
                drawer.classList.remove('flex');
                document.body.style.overflow = '';
            }

            // === PENCARIAN REALTIME (DRAWER) ===
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const items = document.querySelectorAll('.murid-item');

                if (searchInput) {
                    searchInput.addEventListener('input', function(e) {
                        const term = e.target.value.toLowerCase();
                        items.forEach(item => {
                            const nama = item.getAttribute('data-nama');
                            const nism = item.getAttribute('data-nism');
                            item.style.display = (nama.includes(term) || nism.includes(term)) ?
                                'block' : 'none';
                        });
                    });
                }
            });





            // === FUNGSI KONFIRMASI HAPUS ANGGOTA (SWEETALERT) ===
            function confirmDetach(muridId, namaMurid) {
                Swal.fire({
                    title: 'Keluarkan Murid?',
                    html: `Anda yakin ingin mengeluarkan <b class="text-red-500">${namaMurid}</b> dari ruangan ini?<br><span class="text-xs text-zinc-400 mt-2 block">Murid akan dikembalikan ke daftar antrean kelas kosong.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    buttonsStyling: false,
                    heightAuto: false,
                    color: swalColor,
                    customClass: {
                        ...swalCustomClass,
                        confirmButton: swalCustomClass.confirmButton.replace('bg-primary', 'bg-red-600 text-white')
                            .replace('dark:bg-primary-dark', '')
                    },
                    confirmButtonText: '<i class="bi bi-box-arrow-right mr-1.5"></i> Ya, Keluarkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-detach-' + muridId).submit();
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
