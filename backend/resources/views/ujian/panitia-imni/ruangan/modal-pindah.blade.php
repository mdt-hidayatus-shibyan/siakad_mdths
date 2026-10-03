<form action="{{ route('ruangan-imni.pindah-ruangan', $peserta->id) }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container"
    x-data="{
        cakupanHari: '{{ $selectedTanggal ? 'single' : 'all' }}',
        selectedTanggal: '{{ $selectedTanggal }}'
    }">
    @csrf
    @method('PUT')

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/20">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">
                    Plotting NISM Murid
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-0.5">
                    Pindah Ruangan Ujian Murid
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
        
        <!-- Info Murid Card -->
        <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-bold shrink-0 overflow-hidden">
                @if ($peserta->murid && $peserta->murid->foto_url)
                    <img src="{{ $peserta->murid->foto_url }}" alt="Foto" class="w-full h-full object-cover">
                @else
                    {{ substr($peserta->murid?->nama_lengkap ?? 'M', 0, 1) }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h4 class="font-black text-xs text-zinc-900 dark:text-white truncate">
                    {{ $peserta->murid?->nama_lengkap }}
                </h4>
                <p class="text-[11px] font-mono text-zinc-400">
                    NISM: <span class="font-bold text-zinc-600 dark:text-zinc-300">{{ $peserta->murid?->nism }}</span> • No. Peserta: <span class="text-indigo-600">{{ $peserta->nomor_peserta ?? '-' }}</span>
                </p>
                <div class="flex items-center gap-2 mt-1 text-[10px] font-bold">
                    <span class="text-emerald-600">{{ $peserta->level?->nama_level }}</span>
                    <span>•</span>
                    <span class="text-zinc-500">Kelas Asal: {{ $peserta->ruanganAsal?->nama_ruangan }}</span>
                </div>
            </div>
        </div>

        <!-- 1. Pilihan Ruangan Ujian Tujuan -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                Pilih Ruangan Ujian Baru <span class="text-rose-500">*</span>
            </label>
            <select name="ruangan_ujian_id" required class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                @foreach ($daftarRuangan as $rg)
                    <option value="{{ $rg->id }}" {{ $ruanganSaatIniId == $rg->id ? 'selected' : '' }}>
                        {{ $rg->nama_ruangan }} (Kapasitas: {{ $rg->kapasitas }} • {{ $rg->level?->nama_level ?? 'Umum' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- 2. Cakupan Hari Perpindahan -->
        <div class="space-y-2">
            <label class="block text-[11px] font-extrabold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                Cakupan Hari Perpindahan
            </label>
            <div class="space-y-2">
                <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 cursor-pointer">
                    <input type="radio" name="cakupan_hari" value="all" x-model="cakupanHari"
                        class="text-indigo-600 focus:ring-indigo-500/20">
                    <div>
                        <span class="font-black text-xs text-zinc-900 dark:text-white block">Semua Hari (Plotting Default)</span>
                        <span class="text-[10px] text-zinc-400">Pindahkan murid ini ke ruangan target secara permanen/seluruh hari</span>
                    </div>
                </label>

                @if($daftarTanggalUjian->isNotEmpty())
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 cursor-pointer">
                        <input type="radio" name="cakupan_hari" value="single" x-model="cakupanHari"
                            class="text-indigo-600 focus:ring-indigo-500/20">
                        <div>
                            <span class="font-black text-xs text-indigo-600 dark:text-indigo-400 block">Khusus Tanggal Tertentu</span>
                            <span class="text-[10px] text-zinc-400">Pindahkan murid hanya pada hari/tanggal ujian yang dipilih</span>
                        </div>
                    </label>
                @endif
            </div>
        </div>

        <!-- Dropdown Tanggal Ujian (Jika Single) -->
        <div x-show="cakupanHari === 'single'" x-transition class="space-y-1.5 p-3 rounded-2xl bg-indigo-500/5 border border-indigo-500/20">
            <label class="block text-[11px] font-extrabold text-indigo-900 dark:text-indigo-300 uppercase tracking-wider">
                Pilih Tanggal Ujian
            </label>
            <select name="tanggal_ujian" class="m3-input-glass w-full text-xs font-bold" x-model="selectedTanggal">
                @foreach ($daftarTanggalUjian as $idx => $tgl)
                    <option value="{{ $tgl['tanggal'] }}">
                        Hari {{ $idx + 1 }}: {{ $tgl['nama_hari'] }}, {{ $tgl['tanggal_format'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- 3. Nomor Meja (Opsional) -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                Nomor Meja / Urut (Opsional)
            </label>
            <input type="number" name="nomor_meja" value="{{ $nomorMejaSaatIni }}" min="1"
                class="m3-input-glass w-full text-xs font-bold" placeholder="Nomor meja urut...">
        </div>

    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white">
            <i class="bi bi-check2-circle"></i>
            <span>Simpan Perpindahan</span>
        </button>
    </div>
</form>
