<form action="{{ route('ruangan-imni.update-ruangan', $ruanganImni->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf
    @method('PUT')

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/20">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">
                    Master Ruangan IMNI
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-0.5">
                    Edit Ruangan IMNI: {{ $ruanganImni->nama_ruangan_imni ?: $ruanganImni->nama_ruangan }}
                </h3>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        
        <!-- 1. Nama Ruangan IMNI (nama_ruangan_imni) -->
        <div>
            <label class="block text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1">
                Nama Ruangan IMNI <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_ruangan_imni" value="{{ $ruanganImni->nama_ruangan_imni ?: $ruanganImni->nama_ruangan }}" required placeholder="Contoh: R1, R2, R3..."
                class="m3-input-glass w-full text-xs font-black font-mono text-indigo-600 dark:text-indigo-400">
            <span class="text-[11px] text-zinc-400 mt-1 block">Nama ruangan ujian yang akan digunakan (misal: R1, R2, R3).</span>
        </div>

        <!-- 2. Relasi Ruangan Fisik (ruangan_id) -->
        <div>
            <label class="block text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1">
                Relasi Ruangan Fisik Madrasah <span class="text-rose-500">*</span>
            </label>
            <select name="ruangan_id" required class="m3-input-glass w-full text-xs font-bold">
                <option value="">-- Pilih Ruangan Fisik Madrasah --</option>
                @foreach ($daftarRuanganFisik as $rf)
                    <option value="{{ $rf->id }}" {{ $ruanganImni->ruangan_id == $rf->id ? 'selected' : '' }}>
                        {{ $rf->nama_ruangan }} (Kelas: {{ $rf->level?->nama_level ?? '-' }} • Kapasitas: {{ $rf->kapasitas ?: 20 }})
                    </option>
                @endforeach
            </select>
            <span class="text-[11px] text-zinc-400 mt-1 block">Pilih kelas fisik tempat ruangan IMNI ini bertempat.</span>
        </div>

        <!-- 3. Penanggung Jawab Ruangan (penanggung_jawab_ruangan_id) -->
        <div>
            <label class="block text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1">
                Penanggung Jawab Ruangan (Panitia IMNI)
            </label>
            <select name="penanggung_jawab_ruangan_id" class="m3-input-glass w-full text-xs font-bold">
                <option value="">-- Pilih Penanggung Jawab (Opsional) --</option>
                @foreach ($daftarPanitia as $p)
                    <option value="{{ $p->id }}" {{ $ruanganImni->penanggung_jawab_ruangan_id == $p->id ? 'selected' : '' }}>
                        {{ $p->ustadz?->nama_lengkap }} ({{ $p->jabatan }} • NIGM: {{ $p->ustadz?->nigm ?? '-' }})
                    </option>
                @endforeach
            </select>
            <span class="text-[11px] text-zinc-400 mt-1 block">Pilih panitia IMNI yang ditugaskan sebagai penanggung jawab ruangan ini.</span>
        </div>

    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit"
            class="m3-btn-primary text-xs flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white">
            <i class="bi bi-check2"></i>
            <span>Simpan Perubahan</span>
        </button>
    </div>
</form>
