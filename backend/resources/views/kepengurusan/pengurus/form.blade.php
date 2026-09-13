<form action="{{ isset($pengurus) ? route('pengurus.update', $pengurus->id) : route('pengurus.store') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]">
    @csrf
    @if (isset($pengurus))
        @method('PUT')
    @endif

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 px-5 py-4 flex items-center justify-between transition-colors">
        <div>
            <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                {{ isset($pengurus) ? 'Edit Penugasan Pengurus' : 'Tugaskan Pengurus Baru' }}
            </h3>
            <p class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                Struktur organisasi yayasan / madrasah
            </p>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-400 transition-colors outline-none shadow-2xs">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        <!-- Pilih Anggota -->
        <div>
            <label
                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                Pilih Anggota <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <select name="anggota_id" class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer"
                    required>
                    <option value="">-- Pilih Anggota Yayasan --</option>
                    @foreach ($anggota as $org)
                        <option value="{{ $org->id }}"
                            {{ (isset($pengurus) && $pengurus->anggota_id == $org->id) || old('anggota_id') == $org->id ? 'selected' : '' }}>
                            {{ $org->nama_lengkap }} ({{ $org->nik ?? 'No NIK' }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Pilih Jabatan -->
        <div>
            <label
                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                Jabatan <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <select name="jabatan_id" class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer"
                    required>
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach ($jabatan as $jab)
                        <option value="{{ $jab->id }}"
                            {{ (isset($pengurus) && $pengurus->jabatan_id == $jab->id) || old('jabatan_id') == $jab->id ? 'selected' : '' }}>
                            {{ $jab->nama_jabatan }} {{ $jab->level ? ' - ' . $jab->level : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Tingkat/Unit -->
        <div>
            <label
                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                Tingkat / Unit (Opsional)
            </label>
            <div class="relative">
                <select name="tingkat_id"
                    class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer">
                    <option value="">-- Umum / Pengurus Yayasan Pusat --</option>
                    @foreach ($tingkats as $tkt)
                        <option value="{{ $tkt->id }}"
                            {{ (isset($pengurus) && $pengurus->tingkat_id == $tkt->id) || old('tingkat_id') == $tkt->id ? 'selected' : '' }}>
                            {{ $tkt->nama_tingkat }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Pilih Periode -->
        <div>
            <label
                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                Periode Kepengurusan <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <select name="periode_id" class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer"
                    required>
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periode as $per)
                        <option value="{{ $per->id }}"
                            {{ (isset($pengurus) && $pengurus->periode_id == $per->id) || old('periode_id') == $per->id ? 'selected' : '' }}>
                            {{ $per->nama_periode }} {!! $per->is_seumur_hidup ? '&#8734; (Seumur Hidup)' : ($per->status_aktif ? '&#9989; (Aktif)' : '') !!}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Nomor SK -->
        <div>
            <label
                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                Nomor SK (Opsional)
            </label>
            <input type="text" name="no_sk" value="{{ $pengurus->no_sk ?? old('no_sk') }}"
                placeholder="Contoh: 01/SK/YYS/2024" class="m3-input-glass w-full text-xs font-bold font-mono">
        </div>
    </div>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/70 dark:bg-zinc-950/50 border-t border-zinc-200/80 dark:border-zinc-800 px-5 py-3.5 sm:flex sm:flex-row-reverse gap-2.5 transition-colors">
        <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-5 text-xs font-black shadow-2xs">
            <i class="bi bi-save2-fill mr-1.5"></i> {{ isset($pengurus) ? 'Simpan Perubahan' : 'Simpan Penugasan' }}
        </button>
    </div>
</form>
