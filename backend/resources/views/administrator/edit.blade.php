@section('title', 'Edit Data Administrator')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('administrator.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Edit Administrator
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Perbarui informasi profil atau pengaturan akun.
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- ================= FORM 1: PROFIL & BERKAS (Tabel Administrators) ================= -->
        <form action="{{ route('administrator.update', $administrator->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf @method('PUT')
            <!-- Penanda Form Profil -->
            <input type="hidden" name="form_type" value="profil">

            <div class="m3-glass-card p-5 sm:p-7 relative z-10 shadow-2xs">
                <div class="flex items-center gap-3 mb-6 pb-3.5 border-b border-zinc-200/80 dark:border-zinc-800">
                    <div
                        class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center shrink-0 border border-primary/20 shadow-2xs">
                        <i class="bi bi-person-vcard-fill text-lg"></i>
                    </div>
                    <div>
                        <h3
                            class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                            Informasi Pribadi & Berkas
                        </h3>
                        <p
                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                            Data personal dan dokumen tanda tangan
                        </p>
                    </div>
                </div>

                <div class="space-y-5">
                    <!-- Row 1: Nama Lengkap & Jenis Kelamin -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_lengkap"
                                value="{{ old('nama_lengkap', $administrator->nama_lengkap) }}"
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label for="jk_l"
                                    class="flex items-center gap-2.5 min-h-[40px] px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/80 has-[:checked]:border-primary dark:has-[:checked]:border-primary-dark has-[:checked]:bg-primary/5 dark:has-[:checked]:bg-primary-dark/10 transition-all shadow-2xs">
                                    <input type="radio" name="jenis_kelamin" id="jk_l" value="L"
                                        class="w-4 h-4 accent-primary dark:accent-primary-dark cursor-pointer"
                                        {{ old('jenis_kelamin', $administrator->jenis_kelamin) == 'L' ? 'checked' : '' }}>
                                    <span class="text-xs font-black text-zinc-800 dark:text-zinc-200">Laki-laki</span>
                                </label>
                                <label for="jk_p"
                                    class="flex items-center gap-2.5 min-h-[40px] px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/80 has-[:checked]:border-primary dark:has-[:checked]:border-primary-dark has-[:checked]:bg-primary/5 dark:has-[:checked]:bg-primary-dark/10 transition-all shadow-2xs">
                                    <input type="radio" name="jenis_kelamin" id="jk_p" value="P"
                                        class="w-4 h-4 accent-primary dark:accent-primary-dark cursor-pointer"
                                        {{ old('jenis_kelamin', $administrator->jenis_kelamin) == 'P' ? 'checked' : '' }}>
                                    <span class="text-xs font-black text-zinc-800 dark:text-zinc-200">Perempuan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: NIK & No HP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">NIK
                                (16 Digit)</label>
                            <input type="text" name="nik" value="{{ old('nik', $administrator->nik) }}"
                                maxlength="16" class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">No.
                                HP / WhatsApp</label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', $administrator->no_hp) }}"
                                class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                    </div>

                    <!-- Row 3: Tempat & Tanggal Lahir -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tempat
                                Lahir</label>
                            <input type="text" name="tempat_lahir"
                                value="{{ old('tempat_lahir', $administrator->tempat_lahir) }}"
                                class="m3-input-glass w-full text-xs font-bold uppercase">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tanggal
                                Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', $administrator->tanggal_lahir ? \Carbon\Carbon::parse($administrator->tanggal_lahir)->format('Y-m-d') : '') }}"
                                class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                        </div>
                    </div>

                    <!-- Row 4: Alamat, Peran & Tingkat -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Alamat
                                Domisili</label>
                            <textarea name="alamat" rows="2"
                                class="m3-input-glass w-full text-xs font-bold custom-scrollbar resize-none !p-3">{{ old('alamat', $administrator->alamat) }}</textarea>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                    Peran / Role Akses <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="role" id="role_select_edit"
                                        class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer">
                                        <option value="administrator"
                                            {{ old('role', $currentRole) === 'administrator' ? 'selected' : '' }}>
                                            Administrator (Akses Penuh / Semua Tingkat)
                                        </option>
                                        <option value="petugas-tabungan"
                                            {{ old('role', $currentRole) === 'petugas-tabungan' ? 'selected' : '' }}>
                                            Petugas Tabungan
                                        </option>
                                        <option value="petugas-koperasi"
                                            {{ old('role', $currentRole) === 'petugas-koperasi' ? 'selected' : '' }}>
                                            Petugas Koperasi
                                        </option>
                                        <option value="staff"
                                            {{ old('role', $currentRole) === 'staff' ? 'selected' : '' }}>
                                            Staff / Admin Tingkat (Perlu Pilih Tingkat)
                                        </option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                                        <i class="bi bi-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Tingkat Wrapper (Kondisional: Muncul hanya jika Role = staff) -->
                            <div id="tingkat_wrapper_edit"
                                class="{{ old('role', $currentRole) === 'staff' ? '' : 'hidden' }}">
                                <label
                                    class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tingkat
                                    <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="tingkat_id" id="tingkat_select_edit"
                                        class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer">
                                        <option value="" disabled
                                            {{ !old('tingkat_id', $administrator->tingkat_id) ? 'selected' : '' }}>
                                            -- Pilih Area Tingkat --
                                        </option>
                                        @foreach ($tingkats as $tingkat)
                                            <option value="{{ $tingkat->id }}"
                                                {{ old('tingkat_id', $administrator->tingkat_id) == $tingkat->id ? 'selected' : '' }}>
                                                {{ $tingkat->kode_tingkat }} - {{ $tingkat->nama_tingkat }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                                        <i class="bi bi-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Helper note info when administrator / petugas-tabungan selected -->
                            <div id="tingkat_info_note_edit"
                                class="p-2.5 rounded-xl bg-zinc-100/80 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/60 {{ old('role', $currentRole) === 'staff' ? 'hidden' : '' }}">
                                <p
                                    class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                                    <i class="bi bi-info-circle-fill text-primary dark:text-primary-dark"></i>
                                    <span>Peran ini memiliki hak akses global (seluruh tingkat madrasah).</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: Upload Foto & TTD -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <!-- Upload Foto -->
                        <div
                            class="p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/40 shadow-2xs">
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2.5">Upload
                                Foto Profil</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center justify-center flex-shrink-0 shadow-2xs">
                                    <img id="fotoPreview"
                                        src="{{ $administrator->foto ? asset('storage/' . $administrator->foto) : asset($administrator->jenis_kelamin === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
                                        class="w-full h-full object-cover rounded-lg">
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="foto" accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewImage(this)"
                                        class="block w-full text-xs font-bold text-zinc-500 dark:text-zinc-400
                                        file:mr-3 file:py-1.5 file:px-3
                                        file:rounded-xl file:border-0
                                        file:text-[10px] file:font-black file:uppercase file:tracking-wider
                                        file:bg-primary/10 dark:file:bg-primary-dark/20 file:text-primary dark:file:text-primary-dark
                                        hover:file:bg-primary/20 dark:hover:file:bg-primary-dark/30 file:transition-colors file:cursor-pointer
                                        cursor-pointer m3-input-glass !p-1 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- Upload TTD -->
                        <div
                            class="p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/40 shadow-2xs">
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2.5">Tanda
                                Tangan Digital</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="relative w-12 h-12 rounded-xl overflow-hidden border border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center justify-center flex-shrink-0 shadow-2xs">
                                    <i class="bi bi-pen text-base text-zinc-300 dark:text-zinc-600 absolute z-0"
                                        id="ttd-placeholder-icon"
                                        class="{{ $administrator->tanda_tangan ? 'hidden' : '' }}"></i>
                                    <img id="preview-ttd"
                                        src="{{ $administrator->tanda_tangan ? asset('storage/' . $administrator->tanda_tangan) : '' }}"
                                        class="w-full h-full object-contain relative z-10 {{ $administrator->tanda_tangan ? '' : 'hidden' }} p-1">
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="tanda_tangan"
                                        accept="image/png, image/jpeg, image/jpg" onchange="previewTtd(this)"
                                        class="block w-full text-xs font-bold text-zinc-500 dark:text-zinc-400
                                        file:mr-3 file:py-1.5 file:px-3
                                        file:rounded-xl file:border-0
                                        file:text-[10px] file:font-black file:uppercase file:tracking-wider
                                        file:bg-primary/10 dark:file:bg-primary-dark/20 file:text-primary dark:file:text-primary-dark
                                        hover:file:bg-primary/20 dark:hover:file:bg-primary-dark/30 file:transition-colors file:cursor-pointer
                                        cursor-pointer m3-input-glass !p-1 shadow-2xs">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit Profil -->
                <div class="flex justify-end pt-5 mt-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    <button type="submit" class="m3-btn-primary h-10 px-6 text-xs font-black shadow-2xs">
                        <i class="bi bi-person-check-fill mr-1.5"></i> Perbarui Profil
                    </button>
                </div>
            </div>
        </form>


        <!-- ================= FORM 2: PENGATURAN AKUN (Tabel Users) ================= -->
        <form action="{{ route('administrator.update', $administrator->id) }}" method="POST">
            @csrf @method('PUT')
            <!-- Penanda Form Akun -->
            <input type="hidden" name="form_type" value="akun">

            <div class="m3-glass-card p-5 sm:p-7 relative shadow-2xs">
                <div class="flex items-center gap-3 mb-6 pb-3.5 border-b border-zinc-200/80 dark:border-zinc-800">
                    <div
                        class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 shadow-2xs">
                        <i class="bi bi-shield-lock-fill text-lg"></i>
                    </div>
                    <div>
                        <h3
                            class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                            Pengaturan Akun & Akses Login
                        </h3>
                        <p
                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                            Kredensial akses aplikasi sistem
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-6 items-start justify-between">
                    <!-- Username & Email -->
                    <div class="w-full sm:w-2/3 space-y-4">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Username
                                Login</label>
                            <input type="text" name="username"
                                value="{{ old('username', $administrator->user?->username ?? '') }}"
                                class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Email
                                Login</label>
                            <input type="email" name="email"
                                value="{{ old('email', $administrator->user?->email ?? '') }}"
                                class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit Akun -->
                <div class="flex justify-end pt-5 mt-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    <button type="submit"
                        class="m3-btn-primary bg-amber-600 hover:bg-amber-700 border-amber-600 h-10 px-6 text-xs font-black shadow-2xs">
                        <i class="bi bi-shield-check mr-1.5"></i> Perbarui Akun
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="m3-glass-card p-5 sm:p-7 mt-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">

            <div class="flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-500/20 shrink-0 shadow-2xs">
                    <i class="bi bi-person-badge-fill text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">
                        Status Administrator
                    </h3>
                    <p class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        Administrator yang tidak aktif tidak akan ditampilkan di jadwal mengajar dan tidak dapat login
                        ke dalam sistem.
                    </p>
                </div>
            </div>

            <!-- Pemanggilan Komponen Toggle Component -->
            <div class="shrink-0 sm:pl-4 self-end sm:self-center">
                <x-toggle-status :is-active="$administrator->is_active" :url="route('administrator.toggle-status', $administrator->id)" />
            </div>

        </div>
    </div>

    <!-- Hapus Permanen Tetap Ada di Sini -->
    @can('delete administrator')
        <div
            class="mt-6 sm:mt-8 bg-rose-500/5 dark:bg-rose-950/20 rounded-2xl p-5 sm:p-7 border border-rose-500/20 relative overflow-hidden shadow-2xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 relative z-10">

                <!-- Teks Peringatan & Checkbox -->
                <div class="flex items-start gap-4 flex-1">
                    <div
                        class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-500/20 shadow-2xs">
                        <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base md:text-lg font-black text-rose-600 dark:text-rose-400 tracking-tight">
                            Hapus Data Administrator
                        </h3>
                        <p class="text-xs font-medium text-rose-700/80 dark:text-rose-300/70 mt-1 max-w-md mb-3">
                            Tindakan ini tidak dapat dibatalkan. Semua data terkait ustadz ini (termasuk akun login)
                            akan dihapus permanen dari sistem.
                        </p>

                        <!-- Checkbox Konfirmasi -->
                        <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" id="verify_delete" class="peer sr-only">
                                <!-- Kotak Checkbox -->
                                <div
                                    class="w-4 h-4 rounded border border-rose-500/40 peer-checked:bg-rose-600 peer-checked:border-rose-600 transition-all bg-white dark:bg-black/50 shadow-2xs">
                                </div>
                                <i
                                    class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all pointer-events-none"></i>
                            </div>
                            <span
                                class="text-[10px] font-black text-rose-700 dark:text-rose-400 uppercase tracking-wider select-none group-hover:text-rose-800 dark:group-hover:text-rose-300">
                                Saya mengerti dan ingin menghapus data ini
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Tombol Hapus -->
                <div class="w-full sm:w-auto flex-shrink-0 mt-2 sm:mt-0">
                    <form action="{{ route('administrator.destroy', $administrator->id) }}" method="POST"
                        class="delete-ajax inline m-0 p-0">
                        @csrf @method('DELETE')
                        <button type="submit" id="btn_delete_permanen" disabled
                            class="h-10 w-full sm:w-auto px-5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow-2xs transition-all active:scale-95 flex items-center justify-center gap-2 outline-none disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="bi bi-trash-fill text-xs"></i> Hapus Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <script>
        const defaultUstadz = "{{ asset('assets/laki-default.png') }}";
        const defaultUstadzah = "{{ asset('assets/perempuan-default.png') }}";
        const preview = document.getElementById('fotoPreview');
        const radiosJenisKelamin = document.querySelectorAll('input[name="jenis_kelamin"]');

        let isShowingCustomPhoto = {{ isset($administrator) && $administrator->foto ? 'true' : 'false' }};

        // 1. Preview Foto Profil
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    isShowingCustomPhoto = true;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                isShowingCustomPhoto = {{ isset($administrator) && $administrator->foto ? 'true' : 'false' }};
                if (!isShowingCustomPhoto) {
                    const checkedRadio = document.querySelector('input[name="jenis_kelamin"]:checked');
                    const activeGender = checkedRadio ? checkedRadio.value : 'L';
                    preview.src = activeGender === 'L' ? defaultUstadz : defaultUstadzah;
                } else {
                    preview.src =
                        "{{ isset($administrator) && $administrator->foto ? asset('storage/' . $administrator->foto) : '' }}";
                }
            }
        }

        // 2. Event Listener Dropdown Jenis Kelamin
        radiosJenisKelamin.forEach(radio => {
            radio.addEventListener('change', function() {
                if (!isShowingCustomPhoto) {
                    preview.style.opacity = '0';
                    setTimeout(() => {
                        preview.src = this.value === 'L' ? defaultUstadz : defaultUstadzah;
                        preview.style.opacity = '1';
                    }, 150);
                }
            });
        });

        // 3. Preview Tanda Tangan
        function previewTtd(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('ttd-placeholder-icon').classList.add('hidden');
                    const imgPreview = document.getElementById('preview-ttd');
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                const imgPreview = document.getElementById('preview-ttd');
                const hasOldTtd = {{ isset($administrator) && $administrator->tanda_tangan ? 'true' : 'false' }};

                if (hasOldTtd) {
                    imgPreview.src =
                        "{{ isset($administrator) && $administrator->tanda_tangan ? asset('storage/' . $administrator->tanda_tangan) : '' }}";
                } else {
                    imgPreview.classList.add('hidden');
                    document.getElementById('ttd-placeholder-icon').classList.remove('hidden');
                }
            }
        }

        const verifyDeleteCheckbox = document.getElementById('verify_delete');
        const btnDeletePermanen = document.getElementById('btn_delete_permanen');

        if (verifyDeleteCheckbox && btnDeletePermanen) {
            verifyDeleteCheckbox.addEventListener('change', function() {
                btnDeletePermanen.disabled = !this.checked;
            });
        }

        // 4. Role & Tingkat Visibility Toggle for Edit
        const roleSelectEdit = document.getElementById('role_select_edit');
        const tingkatWrapperEdit = document.getElementById('tingkat_wrapper_edit');
        const tingkatSelectEdit = document.getElementById('tingkat_select_edit');
        const tingkatInfoNoteEdit = document.getElementById('tingkat_info_note_edit');

        function updateTingkatVisibilityEdit() {
            if (!roleSelectEdit) return;
            const role = roleSelectEdit.value;
            if (role === 'staff') {
                tingkatWrapperEdit?.classList.remove('hidden');
                tingkatInfoNoteEdit?.classList.add('hidden');
            } else {
                tingkatWrapperEdit?.classList.add('hidden');
                tingkatInfoNoteEdit?.classList.remove('hidden');
                if (tingkatSelectEdit) {
                    tingkatSelectEdit.value = '';
                }
            }
        }

        if (roleSelectEdit) {
            roleSelectEdit.addEventListener('change', updateTingkatVisibilityEdit);
            updateTingkatVisibilityEdit();
        }
    </script>
</x-app-layout>
