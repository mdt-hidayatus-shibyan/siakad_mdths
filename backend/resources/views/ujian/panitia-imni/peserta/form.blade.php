<form action="{{ route('peserta-imni.update', $peserta->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf
    @method('PUT')

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded border border-blue-500/20">
                Imtihan Nihai (IMNI)
            </span>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Edit Data Peserta Ujian IMNI
            </h3>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        <!-- Info Ringkas Murid -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center font-black text-sm shrink-0 overflow-hidden border border-primary/20">
                @if ($peserta->murid && $peserta->murid->foto_url)
                    <img src="{{ $peserta->murid->foto_url }}" alt="Foto" class="w-full h-full object-cover">
                @else
                    {{ substr($peserta->murid?->nama_lengkap ?? 'M', 0, 1) }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h4 class="font-black text-zinc-900 dark:text-white text-xs truncate">
                    {{ $peserta->murid?->nama_lengkap ?? '-' }}
                </h4>
                <div class="flex items-center gap-2 mt-0.5 text-[11px] font-mono text-zinc-500 dark:text-zinc-400 flex-wrap">
                    <span>NISM: <b class="text-zinc-700 dark:text-zinc-300">{{ $peserta->murid?->nism ?? '-' }}</b></span>
                    <span>•</span>
                    <span>Kelas: <b class="text-primary">{{ $peserta->level?->nama_level ?? '-' }}</b></span>
                    <span>•</span>
                    <span>Ruang Asal: <b class="text-zinc-700 dark:text-zinc-300">{{ $peserta->ruanganAsal?->nama_ruangan ?? '-' }}</b></span>
                </div>
            </div>
        </div>

        <!-- 1. Nomor Peserta -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Nomor Peserta IMNI <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nomor_peserta" value="{{ $peserta->nomor_peserta ?? old('nomor_peserta') }}" required
                placeholder="Contoh: IMNI-4748-IBT-00123" class="m3-input-glass w-full font-mono font-bold text-xs">
        </div>

        <!-- 2. Ruangan Ujian -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Ruangan Ujian IMNI
            </label>
            <div class="relative group/select">
                <select name="ruangan_ujian_id" id="ruangan_ujian_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                    <option value="">-- Pilih Ruangan Ujian --</option>
                    @foreach ($daftarRuanganUjian as $ru)
                        <option value="{{ $ru->id }}" {{ $peserta->ruangan_ujian_id == $ru->id ? 'selected' : '' }}>
                            {{ $ru->nama_ruangan }} (Kapasitas: {{ $ru->kapasitas }} • {{ $ru->level?->nama_level }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- 3. Catatan Khusus -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Catatan Khusus <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
            </label>
            <textarea name="catatan" rows="2.5" placeholder="Tuliskan catatan khusus bila ada..."
                class="m3-input-glass w-full text-xs font-medium">{{ $peserta->catatan ?? old('catatan') }}</textarea>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5">
            <i class="bi bi-check-lg"></i>
            <span>Simpan Perubahan</span>
        </button>
    </div>
</form>
