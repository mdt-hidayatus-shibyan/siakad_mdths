@php
    $isEdit = isset($jadwal);
@endphp

<form action="{{ $isEdit ? route('jadwal-imni.update', $jadwal->id) : route('jadwal-imni.store') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-jadwal-container">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @else
        <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunId }}">
    @endif

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary dark:text-primary-dark flex items-center justify-center font-bold text-sm border border-primary/20">
                <i class="bi bi-calendar2-plus"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ $isEdit ? 'Edit Jadwal Sesi IMNI' : 'Tambah Jadwal Sesi Ujian IMNI' }}
                </h3>
                <p class="text-[11px] text-zinc-400">
                    Khusus tingkat kelas akhir (3 TPQ, 6 IBT, 3 TSA)
                </p>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">

        <!-- Baris 1: Level / Kelas Akhir -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Tingkat / Level Kelas Akhir <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <select name="level_id" id="modal_level_id" required
                    class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer !pr-9">
                    @foreach ($levels as $lvl)
                        <option value="{{ $lvl->id }}"
                            {{ ($isEdit ? $jadwal->level_id == $lvl->id : (isset($selectedLevel) && $selectedLevel->id == $lvl->id)) ? 'selected' : '' }}>
                            Kelas {{ $lvl->nama_level }} ({{ $lvl->tingkat->nama_tingkat ?? 'Jenjang Akhir' }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- Baris 2: Tanggal & Sesi Waktu -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1.5 sm:col-span-1">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Tanggal Ujian <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_ujian" required
                    value="{{ $isEdit && $jadwal->tanggal_ujian ? Carbon\Carbon::parse($jadwal->tanggal_ujian)->format('Y-m-d') : now()->format('Y-m-d') }}"
                    class="m3-input-glass w-full text-xs font-bold">
            </div>

            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Jam Mulai <span class="text-rose-500">*</span>
                </label>
                <input type="time" name="waktu_mulai" required
                    value="{{ $isEdit && $jadwal->waktu_mulai ? Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') : '07:30' }}"
                    class="m3-input-glass w-full text-xs font-mono font-bold">
            </div>

            <div class="space-y-1.5">
                <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Jam Selesai
                </label>
                <input type="time" name="waktu_selesai"
                    value="{{ $isEdit && $jadwal->waktu_selesai ? Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i') : '09:00' }}"
                    class="m3-input-glass w-full text-xs font-mono font-bold">
            </div>
        </div>

        <!-- Baris 3: Mata Pelajaran -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Pilih Mata Pelajaran Terdaftar <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <select name="mata_pelajaran_id" id="modal_mapel_id"
                    class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer !pr-9">
                    <option value="">-- Pilih Mapel dari Kurikulum Kelas --</option>
                    @foreach ($mapels as $m)
                        <option value="{{ $m->id }}" {{ $isEdit && $jadwal->mata_pelajaran_id == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_mapel }} ({{ $m->kode_mapel ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

        <!-- Baris 4: Nama Mapel Kustom (Opsional) -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Atau Nama Mapel Custom <span class="text-zinc-400 font-normal lowercase">(jika mapel khusus IMNI di luar KBM reguler)</span>
            </label>
            <input type="text" name="nama_mata_pelajaran_custom"
                value="{{ $isEdit ? $jadwal->nama_mata_pelajaran_custom : old('nama_mata_pelajaran_custom') }}"
                placeholder="Contoh: Imtihan Tahriri / Nahwu Nihai"
                class="m3-input-glass w-full text-xs font-bold">
        </div>

        <!-- Baris 5: Ustadz Pengawas (Opsional) -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Ustadz Pengawas <span class="text-zinc-400 font-normal lowercase">(opsional / default)</span>
            </label>
            <div class="relative">
                <select name="ustadz_id"
                    class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer !pr-9">
                    <option value="">-- Pengawas Diatur Harian di Ruangan --</option>
                    @foreach ($daftarUstadz as $u)
                        <option value="{{ $u->id }}" {{ $isEdit && $jadwal->ustadz_id == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_lengkap }} ({{ $u->niy ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 sm:flex sm:flex-row-reverse gap-2.5 transition-colors duration-300">
        <button type="submit" class="m3-btn-primary w-full sm:w-auto px-5 py-2 group/btn">
            <i class="bi bi-save2-fill text-sm"></i>
            <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Jadwal Sesi' }}</span>
        </button>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-full sm:w-auto px-4 py-2 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all">
            Batal
        </button>
    </div>
</form>
