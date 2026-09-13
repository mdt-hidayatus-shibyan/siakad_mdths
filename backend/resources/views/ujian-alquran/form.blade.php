<form action="{{ isset($ujian) ? route('ujian-alquran.update', $ujian->id) : route('ujian-alquran.store') }}"
    method="POST" class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-grid-container">
    @csrf
    @if (isset($ujian))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div
                class="w-9 h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i class="bi {{ isset($ujian) ? 'bi-pencil-square' : 'bi-book-half' }} text-base"></i>
            </div>
            <div>
                <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ isset($ujian) ? 'Edit Konfigurasi Agenda Ujian' : 'Buat Agenda Ujian Al-Qur\'an Baru' }}
                </h3>
                <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ isset($ujian) ? 'Perbarui jadwal pelaksanaan dan bobot penilaian ujian.' : 'Agenda ujian kemampuan baca Al-Qur\'an sebagai syarat kelulusan Ibtidaiyah.' }}
                </p>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1">
        <div class="space-y-4">

            <!-- Baris 1: Tahun Pelajaran & Nama Agenda -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tahun Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="tahun_pelajaran_id" id="tahun_pelajaran_id"
                            class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            @foreach ($daftarTahun as $tp)
                                @php
                                    $isSelected = isset($ujian)
                                        ? (string) $ujian->tahun_pelajaran_id === (string) $tp->id
                                        : (string) $tahunPelajaranId === (string) $tp->id;
                                @endphp
                                <option value="{{ $tp->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $tp->nama_hijriyah }} | {{ $tp->nama_masehi }}
                                    {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Status Pelaksanaan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group/select">
                        <select name="status"
                            class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                            <option value="Berjalan"
                                {{ (isset($ujian) && $ujian->status === 'Berjalan') || !isset($ujian) ? 'selected' : '' }}>
                                Berjalan (Aktif)
                            </option>
                            <option value="Draf" {{ isset($ujian) && $ujian->status === 'Draf' ? 'selected' : '' }}>
                                Draf
                            </option>
                            <option value="Selesai"
                                {{ isset($ujian) && $ujian->status === 'Selesai' ? 'selected' : '' }}>
                                Selesai
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                            <i class="bi bi-chevron-down text-xs font-bold"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nama Agenda Ujian -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Nama Agenda Ujian <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_ujian"
                    value="{{ $ujian->nama_ujian ?? 'Ujian Al-Qur\'an Tingkat Ibtidaiyah TP. ' . ($tahunAktif->nama_masehi ?? date('Y')) }}"
                    placeholder="Contoh: Ujian Al-Qur'an Tingkat Ibtidaiyah"
                    class="m3-input-glass w-full text-xs font-bold">
            </div>

            <!-- Baris 2: Tanggal Pelaksanaan & KKM -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Tanggal Pelaksanaan (1 Hari) <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_ujian"
                        value="{{ isset($ujian) && $ujian->tanggal_ujian ? \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') : date('Y-m-d') }}"
                        class="m3-input-glass w-full text-xs font-bold">
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Batas Minimal Lulus (KKM) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kkm_kelulusan" value="{{ $ujian->kkm_kelulusan ?? 55 }}" min="0"
                        max="100" class="m3-input-glass w-full text-xs font-bold">
                    <p class="text-[10px] text-zinc-400 ml-1">Nilai &gt; KKM dinyatakan Lulus</p>
                </div>
            </div>

            <!-- Baris 3: Bobot Minus Kesalahan Jali & Khofi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Bobot Minus Khotho' Jali <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="bobot_jali" value="{{ $ujian->bobot_jali ?? 5 }}" min="1"
                        max="50" class="m3-input-glass w-full text-xs font-bold">
                    <p class="text-[10px] text-zinc-400 ml-1">Setiap 1 kesalahan memotong poin ini</p>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                        Bobot Minus Khotho' Khofi <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="bobot_khofi" value="{{ $ujian->bobot_khofi ?? 3 }}" min="1"
                        max="50" class="m3-input-glass w-full text-xs font-bold">
                    <p class="text-[10px] text-zinc-400 ml-1">Setiap 1 kesalahan memotong poin ini</p>
                </div>
            </div>

            <!-- Keterangan / Catatan Ujian -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                    Keterangan / Catatan Ujian
                </label>
                <input type="text" name="keterangan" value="{{ $ujian->keterangan ?? '' }}"
                    placeholder="Contoh: Ujian kelulusan Al-Qur'an angkatan ke-10"
                    class="m3-input-glass w-full text-xs font-bold">
            </div>

        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 sm:flex sm:flex-row-reverse gap-2.5 transition-colors duration-300">
        <button type="submit"
            class="m3-btn-primary w-full sm:w-auto px-5 py-2 group/btn text-xs font-black shadow-2xs">
            <i class="bi bi-save2-fill text-sm"></i>
            <span>{{ isset($ujian) ? 'Simpan Perubahan' : 'Simpan & Buat Agenda' }}</span>
        </button>
        <button type="button" data-dismiss="modal" command="close"
            class="mt-2 sm:mt-0 w-full sm:w-auto px-4 py-2 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all">
            Batal
        </button>
    </div>
</form>
