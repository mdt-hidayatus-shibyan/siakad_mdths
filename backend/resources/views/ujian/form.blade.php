<form action="{{ isset($ujian) ? route('ujian.update', $ujian->id) : route('ujian.store') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    @if (isset($ujian))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
            {{ isset($ujian) ? 'Edit Ujian' : 'Tambah Ujian Baru' }}
        </h3>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1">
        <div class="space-y-4">

            <!-- Baris 1: Tahun Pelajaran & Semester -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tahun Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            @foreach ($tahun_pelajarans as $tp)
                                @php
                                    $isSelected = isset($ujian)
                                        ? $ujian->tahun_pelajaran_id == $tp->id
                                        : $tp->is_active == true || old('tahun_pelajaran_id') == $tp->id;
                                @endphp
                                <option value="{{ $tp->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $tp->nama_hijriyah }} - {{ $tp->nama_masehi }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Semester <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="semester_id" id="semester_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                            <option value="">-- Pilih Semester --</option>
                            @foreach ($semesters as $smt)
                                <option value="{{ $smt->id }}"
                                    {{ (isset($ujian) && $ujian->semester_id == $smt->id) || old('semester_id') == $smt->id ? 'selected' : '' }}>
                                    {{ $smt->nama_semester ?? 'Semester ' . $smt->id }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Nama Ujian & Tipe Ujian -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Nama Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_ujian" value="{{ $ujian->nama_ujian ?? old('nama_ujian') }}"
                        placeholder="Contoh: Imtihan Dauri 1 (IMDA 1)" class="m3-input-glass w-full">
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tipe Ujian <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="tipe_ujian" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                            <option value="">-- Pilih Tipe Ujian --</option>
                            <option value="IMDA 1"
                                {{ (isset($ujian) && $ujian->tipe_ujian == 'IMDA 1') || old('tipe_ujian') == 'IMDA 1' ? 'selected' : '' }}>
                                IMDA 1 (Imtihan Dauri 1)</option>
                            <option value="IMDA 2"
                                {{ (isset($ujian) && $ujian->tipe_ujian == 'IMDA 2') || old('tipe_ujian') == 'IMDA 2' ? 'selected' : '' }}>
                                IMDA 2 (Imtihan Dauri 2)</option>
                            <option value="IMDA 3"
                                {{ (isset($ujian) && $ujian->tipe_ujian == 'IMDA 3') || old('tipe_ujian') == 'IMDA 3' ? 'selected' : '' }}>
                                IMDA 3 (Imtihan Dauri 3)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Baris 3: Sasaran Tingkat -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Sasaran Tingkat / Jenjang <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
                </label>
                <div class="relative group/select">
                    <select name="tingkat_id" id="tingkat_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9">
                        <option value="">-- Berlaku Semua Tingkat (Global / Serentak) --</option>
                        @if(isset($tingkats))
                            @foreach ($tingkats as $tkt)
                                <option value="{{ $tkt->id }}"
                                    {{ (isset($ujian) && $ujian->tingkat_id == $tkt->id) || old('tingkat_id') == $tkt->id ? 'selected' : '' }}>
                                    Khusus Tingkat {{ $tkt->nama_tingkat }} ({{ $tkt->kode_tingkat }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 ml-1 leading-relaxed">
                    <i class="bi bi-info-circle text-primary dark:text-primary-dark mr-0.5"></i>
                    Pilih tingkat khusus jika jadwal atau durasi ujian berbeda antar jenjang (contoh: IMDA TPQ terpisah dari IMDA Ibtidaiyah/Tsanawiyah). Biarkan global untuk ujian umum serentak seperti IMDA 1 & IMDA 2.
                </p>
            </div>

            <!-- Baris 4: Tanggal Mulai & Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tanggal Mulai
                    </label>
                    <input type="date" name="tanggal_mulai"
                        value="{{ isset($ujian) && $ujian->tanggal_mulai ? $ujian->tanggal_mulai->format('Y-m-d') : old('tanggal_mulai') }}"
                        class="m3-input-glass w-full">
                </div>
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tanggal Selesai
                    </label>
                    <input type="date" name="tanggal_selesai"
                        value="{{ isset($ujian) && $ujian->tanggal_selesai ? $ujian->tanggal_selesai->format('Y-m-d') : old('tanggal_selesai') }}"
                        class="m3-input-glass w-full">
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 sm:flex sm:flex-row-reverse gap-2.5 transition-colors duration-300">
        <button type="submit" class="m3-btn-primary w-full sm:w-auto px-5 py-2 group/btn">
            <i class="bi bi-save2-fill text-sm"></i>
            <span>{{ isset($ujian) ? 'Simpan Perubahan' : 'Simpan Ujian' }}</span>
        </button>
    </div>
</form>

