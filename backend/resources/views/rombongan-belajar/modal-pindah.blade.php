@php
    $defaultFoto = $murid->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png';
    $fotoPath = $murid->foto ? $murid->foto : $defaultFoto;
@endphp

<!-- Modal Form Mutasi / Pindah Ruangan (AJAX M3 Glass) -->
<form action="{{ route('rombongan-belajar.pindah-anggota', $ruangan->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col" data-refresh-target="#data-table-container">
    @csrf
    <input type="hidden" name="murid_id" value="{{ $murid->id }}">

    <!-- Header Modal -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-b border-zinc-200/80 dark:border-zinc-800 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-3 min-w-0">
            <div
                class="w-10 h-10 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight truncate">
                    Mutasi Ruangan Murid
                </h3>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 truncate">
                    Pindahkan Murid ke rombongan belajar lain
                </p>
            </div>
        </div>

        <!-- Tombol Tutup -->
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xl bg-zinc-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-zinc-800 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 text-zinc-400 transition-colors duration-200 outline-none active:scale-95 shadow-2xs">
            <i class="bi bi-x-lg text-xs font-black"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 space-y-4 overflow-y-auto custom-scrollbar max-h-[70vh]">
        <!-- Info Santri (Card Highlight) -->
        <div
            class="p-4 rounded-2xl bg-zinc-50/80 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-13 h-13 rounded-xl overflow-hidden shrink-0 bg-white dark:bg-zinc-900 p-0.5 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                <img src="{{ asset('storage/' . $fotoPath) }}" alt="{{ $murid->nama_lengkap }}"
                    class="w-48 h-48 object-cover rounded-lg">
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-0.5">
                    <span
                        class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $murid->jenis_kelamin == 'L' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20' }}">
                        {{ $murid->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                    <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 font-mono">
                        NISM: {{ $murid->nism ?? '-' }}
                    </span>
                </div>
                <h4 class="text-sm font-black text-zinc-900 dark:text-white truncate">
                    {{ $murid->nama_lengkap }}
                </h4>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 flex items-center gap-1.5">
                    <span>Ruangan Saat Ini:</span>
                    <span
                        class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-black text-[11px]">
                        {{ $ruangan->nama_ruangan }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Dropdown Ruangan Tujuan -->
        <div class="space-y-1.5">
            <label class="block text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider ml-1">
                Pilih Ruangan Tujuan <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-door-open-fill text-sm"></i>
                </div>
                <select name="ruangan_tujuan_id" required
                    class="m3-input-glass w-full !pl-10 !pr-9 text-xs font-bold cursor-pointer appearance-none">
                    <option value="" disabled selected class="text-zinc-400">-- Pilih Ruangan Tujuan --</option>
                    @foreach ($ruangansLain as $rLain)
                        @php
                            $terisi = $rLain->murids_count ?? 0;
                            $kapasitas = $rLain->kapasitas;
                            $isPenuh = $kapasitas && $terisi >= $kapasitas;
                        @endphp
                        <option value="{{ $rLain->id }}" {{ $isPenuh ? 'disabled class=text-rose-400' : '' }}>
                            {{ $rLain->nama_ruangan }}
                            ({{ $rLain->level->tingkat->nama_tingkat ?? '' }} {{ $rLain->level->nama_level ?? '' }})
                            - Kapasitas: {{ $terisi }}/{{ $kapasitas }} {{ $isPenuh ? '[PENUH]' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-black"></i>
                </div>
            </div>
            @if ($ruangansLain->isEmpty())
                <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-1">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i> Tidak ada ruangan lain yang tersedia pada tahun
                    ajaran ini.
                </p>
            @endif
        </div>

        <!-- Catatan Info Mutasi -->
        <div
            class="p-3.5 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-700 dark:text-blue-300 text-xs flex items-start gap-2.5">
            <i class="bi bi-info-circle-fill text-sm shrink-0 mt-0.5 text-blue-600 dark:text-blue-400"></i>
            <div class="space-y-0.5 leading-relaxed">
                <p class="font-black text-[11px] uppercase tracking-wider">Perhatian</p>
                <p class="text-[11px] font-medium text-blue-800/90 dark:text-blue-200/80">
                    Mutasi akan memindahkan data rombel santri pada tahun ajaran aktif ini ke ruangan yang dipilih
                    secara otomatis tanpa menghapus riwayat presensi lampau.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/90 dark:bg-zinc-900/90 border-t border-zinc-200/80 dark:border-zinc-800 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="px-4 py-2 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
            Batal
        </button>
        <button type="submit"
            class="m3-btn-primary min-h-[38px] px-5 text-xs font-black shadow-md flex items-center gap-2"
            {{ $ruangansLain->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-arrow-left-right text-xs"></i>
            <span>Pindahkan Santri</span>
        </button>
    </div>
</form>
