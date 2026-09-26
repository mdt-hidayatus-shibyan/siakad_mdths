<table class="m3-table">
    <thead>
        <tr>
            <th scope="col" class="text-center w-12">No</th>
            <th scope="col" class="w-16">NISM</th>
            <th scope="col">Nama Murid</th>
            <th scope="col" class="text-center w-12">L/P</th>
            <th scope="col">Umur</th>
            <th scope="col">Nama Ayah</th>
            <th scope="col">Nama Ibu</th>
            <th scope="col">Kampung</th>
            <th scope="col" class="text-center">Status</th>
            <th scope="col" class="text-center w-16">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($murids as $murid)
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

            <!-- Baris Tabel Murid -->
            <tr class="{{ $murid->status !== 'Aktif' ? 'opacity-65 grayscale-[35%]' : '' }} group/tr">

                <!-- KOLOM NO -->
                <td class="text-center">
                    <span
                        class="w-7 h-7 mx-auto flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-lg text-xs font-black shrink-0 border border-zinc-200/60 dark:border-zinc-700/60">
                        {{ $murids->firstItem() + $loop->index }}
                    </span>
                </td>

                <!-- KOLOM NISM -->
                <td>
                    <span
                        class="inline-flex items-center gap-1 font-mono text-xs font-black text-zinc-700 dark:text-zinc-300">
                        <i class="bi bi-upc-scan text-zinc-400"></i>
                        <span>{{ $murid->nism }}</span>
                    </span>
                </td>

                <!-- KOLOM PROFIL (Foto & Nama) -->
                <td>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl overflow-hidden shrink-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-2xs relative">
                            <img src="{{ asset('storage/' . $fotoPath) }}" alt="{{ $murid->nama_lengkap }}"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-zinc-900 dark:text-white tracking-tight truncate max-w-[220px]"
                                title="{{ $murid->nama_lengkap }}">
                                {{ $murid->nama_lengkap }}
                            </h4>
                            @if ($murid->nama_panggilan)
                                <span
                                    class="inline-block text-[9px] font-black text-primary dark:text-primary-dark bg-primary/10 dark:bg-primary-dark/15 px-1.5 py-0.2 rounded mt-0.5 uppercase tracking-wider">
                                    "{{ $murid->nama_panggilan }}"
                                </span>
                            @endif
                        </div>
                    </div>
                </td>

                <!-- KOLOM GENDER -->
                <td class="text-center">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-[10px] font-black {{ $murid->jenis_kelamin == 'L' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' }}">
                        {{ $murid->jenis_kelamin }}
                    </span>
                </td>

                <!-- KOLOM UMUR & STATUS YATIM -->
                <td>
                    <div class="flex flex-col items-start gap-1">
                        @if ($umur > 0)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border border-zinc-200/80 dark:border-zinc-700/80 bg-zinc-50 dark:bg-zinc-800/80 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider shadow-2xs">
                                <i class="bi bi-hourglass-split text-zinc-400"></i> {{ $umur }} Thn
                            </span>
                        @endif

                        @if ($isYatim && $isPiatu)
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-purple-500/15 border border-purple-500/30 text-purple-600 dark:text-purple-400">
                                <i class="bi bi-heartbreak-fill mr-1"></i> Yatim Piatu
                            </span>
                        @elseif($isYatim)
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/15 border border-amber-500/30 text-amber-600 dark:text-amber-400">
                                <i class="bi bi-heartbreak mr-1"></i> Yatim
                            </span>
                        @elseif($isPiatu)
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-orange-500/15 border border-orange-500/30 text-orange-600 dark:text-orange-400">
                                <i class="bi bi-heartbreak mr-1"></i> Piatu
                            </span>
                        @endif
                    </div>
                </td>

                <!-- KOLOM AYAH -->
                <td>
                    <div class="flex flex-col items-start gap-1">
                        @can('update wali-murid')
                            <a href="{{ route('wali-murid.edit', $murid->wali_murid_id) }}"
                                class="text-xs font-bold text-zinc-900 dark:text-white tracking-tight truncate max-w-[150px] hover:text-primary dark:hover:text-primary-dark transition-colors"
                                title="Lihat Profil Keluarga">
                                {{ $murid->nama_ayah ?: '-' }}
                            </a>
                        @else
                            <span
                                class="text-xs font-bold text-zinc-900 dark:text-white tracking-tight truncate max-w-[150px]">
                                {{ $murid->nama_ayah ?: '-' }}
                            </span>
                        @endcan
                        @if ($murid->status_ayah == 'Hidup')
                            <span
                                class="text-[9px] font-black text-emerald-700 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 rounded uppercase tracking-wider">
                                Hidup
                            </span>
                        @else
                            <span
                                class="text-[9px] font-black bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 px-1.5 py-0.2 rounded uppercase tracking-wider">
                                Meninggal
                            </span>
                        @endif
                    </div>
                </td>

                <!-- KOLOM IBU -->
                <td>
                    <div class="flex flex-col items-start gap-1">
                        <span
                            class="text-xs font-bold text-zinc-900 dark:text-white tracking-tight truncate max-w-[150px]">
                            {{ $murid->nama_ibu ?: '-' }}
                        </span>
                        @if ($murid->status_ibu == 'Hidup')
                            <span
                                class="text-[9px] font-black text-emerald-700 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 rounded uppercase tracking-wider">
                                Hidup
                            </span>
                        @else
                            <span
                                class="text-[9px] font-black bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 px-1.5 py-0.2 rounded uppercase tracking-wider">
                                Meninggal
                            </span>
                        @endif
                    </div>
                </td>

                <!-- KOLOM KAMPUNG -->
                <td>
                    <span class="text-xs font-bold text-zinc-600 dark:text-zinc-400">
                        {{ $murid->waliMurid->kampung->nama_kampung ?? '-' }}
                    </span>
                </td>

                <!-- KOLOM STATUS AKTIF -->
                <td class="text-center">
                    @if ($murid->status == 'Aktif')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                        </span>
                    @elseif($murid->status == 'Lulus')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400">
                            <i class="bi bi-mortarboard-fill mr-1"></i> Lulus
                        </span>
                    @elseif($murid->status == 'Pindah')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400">
                            <i class="bi bi-arrow-left-right mr-1"></i> Pindah
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400">
                            <i class="bi bi-x-circle-fill mr-1"></i> Berhenti
                        </span>
                    @endif
                </td>

                <!-- KOLOM AKSI -->
                <td class="text-center">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('kartu-pelajar.cetak-single', $murid->id) }}" target="_blank"
                            class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition-all border border-emerald-500/20 shadow-2xs outline-none"
                            title="Cetak Kartu Santri & QR Login Wali">
                            <i class="bi bi-person-badge text-[12px]"></i>
                        </a>
                        @can('update murid')
                            <a href="{{ route('murid.edit', $murid->id) }}"
                                class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-500/20 transition-all border border-blue-500/20 shadow-2xs outline-none"
                                title="Edit Murid">
                                <i class="bi bi-pencil-fill text-[11px]"></i>
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="10" class="px-5 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div
                            class="w-12 h-12 bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 rounded-2xl flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-3 shadow-2xs">
                            <i class="bi bi-person-badge text-2xl"></i>
                        </div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                            Tidak Ada Data Murid
                        </h3>
                        <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Belum ada Murid dengan status "<span
                                class="text-primary dark:text-primary-dark font-black">{{ $status }}</span>" atau
                            coba kata kunci lain.
                        </p>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
