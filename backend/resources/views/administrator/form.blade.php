@section('title', isset($administrator) ? 'Edit Data Administrator' : 'Tambah Administrator Baru')

<x-app-layout>
    <!-- Header Page (Compact M3) -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('administrator.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ isset($administrator) ? 'Edit Administrator' : 'Tambah Administrator' }}
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Lengkapi formulir di bawah ini dengan data yang valid.
                </p>
            </div>
        </div>
    </div>

    <!-- Solid M3 Form Card -->
    <form
        action="{{ isset($administrator) ? route('administrator.update', $administrator->id) : route('administrator.store') }}"
        method="POST" enctype="multipart/form-data" class="space-y-6 relative z-10">
        @csrf
        @if (isset($administrator))
            @method('PUT')
        @endif

        <!-- ================= CARD 1: INFORMASI PRIBADI & BERKAS ================= -->
        <div class="m3-glass-card p-5 sm:p-7 shadow-2xs">

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
                    <p class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                        Biodata diri dan dokumen tanda tangan digital
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
                            value="{{ old('nama_lengkap', $administrator->nama_lengkap ?? '') }}"
                            placeholder="Contoh: Ahmad, S.Pd.I"
                            class="m3-input-glass w-full text-xs font-bold {{ $errors->has('nama_lengkap') ? '!border-rose-500' : '' }}">
                        @error('nama_lengkap')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <!-- Grid Custom Radio Button M3 Dense -->
                        <div class="grid grid-cols-2 gap-3">
                            <label for="jk_l"
                                class="flex items-center gap-2.5 min-h-[40px] px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border {{ $errors->has('jenis_kelamin') ? 'border-rose-500' : 'border-zinc-200 dark:border-zinc-800' }} rounded-xl cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/80 has-[:checked]:border-primary dark:has-[:checked]:border-primary-dark has-[:checked]:bg-primary/5 dark:has-[:checked]:bg-primary-dark/10 transition-all shadow-2xs">
                                <input type="radio" name="jenis_kelamin" id="jk_l" value="L"
                                    class="w-4 h-4 accent-primary dark:accent-primary-dark cursor-pointer"
                                    {{ old('jenis_kelamin', $administrator->jenis_kelamin ?? '') == 'L' ? 'checked' : '' }}>
                                <span class="text-xs font-black text-zinc-800 dark:text-zinc-200">Laki-laki</span>
                            </label>

                            <label for="jk_p"
                                class="flex items-center gap-2.5 min-h-[40px] px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border {{ $errors->has('jenis_kelamin') ? 'border-rose-500' : 'border-zinc-200 dark:border-zinc-800' }} rounded-xl cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/80 has-[:checked]:border-primary dark:has-[:checked]:border-primary-dark has-[:checked]:bg-primary/5 dark:has-[:checked]:bg-primary-dark/10 transition-all shadow-2xs">
                                <input type="radio" name="jenis_kelamin" id="jk_p" value="P"
                                    class="w-4 h-4 accent-primary dark:accent-primary-dark cursor-pointer"
                                    {{ old('jenis_kelamin', $administrator->jenis_kelamin ?? '') == 'P' ? 'checked' : '' }}>
                                <span class="text-xs font-black text-zinc-800 dark:text-zinc-200">Perempuan</span>
                            </label>
                        </div>
                        @error('jenis_kelamin')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Row 2: NIK & No HP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            NIK (16 Digit)
                        </label>
                        <input type="text" name="nik" value="{{ old('nik', $administrator->nik ?? '') }}"
                            maxlength="16" placeholder="Nomor Induk Kependudukan"
                            class="m3-input-glass w-full text-xs font-bold font-mono {{ $errors->has('nik') ? '!border-rose-500' : '' }}">
                        @error('nik')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            No. HP / WhatsApp
                        </label>
                        <input type="text" name="no_hp" value="{{ old('no_hp', $administrator->no_hp ?? '') }}"
                            placeholder="08..."
                            class="m3-input-glass w-full text-xs font-bold font-mono {{ $errors->has('no_hp') ? '!border-rose-500' : '' }}">
                        @error('no_hp')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Row 3: Tempat & Tanggal Lahir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Tempat Lahir
                        </label>
                        <input type="text" name="tempat_lahir"
                            value="{{ old('tempat_lahir', $administrator->tempat_lahir ?? '') }}"
                            placeholder="Kota/Kabupaten"
                            class="m3-input-glass w-full text-xs font-bold uppercase {{ $errors->has('tempat_lahir') ? '!border-rose-500' : '' }}">
                        @error('tempat_lahir')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir"
                            value="{{ old('tanggal_lahir', isset($administrator->tanggal_lahir) ? \Carbon\Carbon::parse($administrator->tanggal_lahir)->format('Y-m-d') : '') }}"
                            class="m3-input-glass w-full text-xs font-bold uppercase cursor-pointer {{ $errors->has('tanggal_lahir') ? '!border-rose-500' : '' }}">
                        @error('tanggal_lahir')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Row 4: Alamat, Peran & Tingkat -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Alamat Domisili <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" rows="2" placeholder="Alamat lengkap saat ini..."
                            class="m3-input-glass w-full text-xs font-bold custom-scrollbar resize-none !p-3 {{ $errors->has('alamat') ? '!border-rose-500' : '' }}">{{ old('alamat', $administrator->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                Peran / Role Akses <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="role" id="role_select"
                                    class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer {{ $errors->has('role') ? '!border-rose-500' : '' }}">
                                    <option value="administrator"
                                        {{ old('role', $currentRole ?? 'administrator') === 'administrator' ? 'selected' : '' }}>
                                        Administrator (Akses Penuh / Semua Tingkat)
                                    </option>
                                    <option value="petugas-tabungan"
                                        {{ old('role', $currentRole ?? '') === 'petugas-tabungan' ? 'selected' : '' }}>
                                        Petugas Tabungan
                                    </option>
                                    <option value="petugas-koperasi"
                                        {{ old('role', $currentRole ?? '') === 'petugas-koperasi' ? 'selected' : '' }}>
                                        Petugas Koperasi
                                    </option>
                                    <option value="staff"
                                        {{ old('role', $currentRole ?? '') === 'staff' ? 'selected' : '' }}>
                                        Staff / Admin Tingkat (Perlu Pilih Tingkat)
                                    </option>
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                            @error('role')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Tingkat Wrapper (Kondisional: Muncul hanya jika Role = staff) -->
                        <div id="tingkat_wrapper"
                            class="{{ old('role', $currentRole ?? 'administrator') === 'staff' ? '' : 'hidden' }}">
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                                Tingkat <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="tingkat_id" id="tingkat_select"
                                    class="m3-input-glass w-full text-xs font-bold appearance-none cursor-pointer {{ $errors->has('tingkat_id') ? '!border-rose-500' : '' }}">
                                    <option value="" disabled
                                        {{ !old('tingkat_id', $administrator->tingkat_id ?? null) ? 'selected' : '' }}>
                                        -- Pilih Area Tingkat --
                                    </option>
                                    @foreach ($tingkats as $tingkat)
                                        <option value="{{ $tingkat->id }}"
                                            {{ old('tingkat_id', $administrator->tingkat_id ?? '') == $tingkat->id ? 'selected' : '' }}>
                                            {{ $tingkat->kode_tingkat }} - {{ $tingkat->nama_tingkat }}
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                            @error('tingkat_id')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Helper note info when administrator / petugas-tabungan selected -->
                        <div id="tingkat_info_note"
                            class="p-2.5 rounded-xl bg-zinc-100/80 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/60 {{ old('role', $currentRole ?? 'administrator') === 'staff' ? 'hidden' : '' }}">
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
                        class="p-4 rounded-2xl border {{ $errors->has('foto') ? 'border-rose-500' : 'border-zinc-200/80 dark:border-zinc-800' }} bg-zinc-50/50 dark:bg-zinc-900/40 flex flex-col justify-center shadow-2xs">
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2.5">
                            Upload Foto Profil
                        </label>
                        <div class="flex items-center gap-3">
                            <!-- Preview Box -->
                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center justify-center flex-shrink-0 shadow-2xs">
                                <img id="fotoPreview"
                                    src="{{ $administrator->foto ?? false ? asset('storage/' . $administrator->foto) : asset(old('jenis_kelamin', $administrator->jenis_kelamin ?? 'L') === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
                                    alt="Preview" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="foto" id="fotoInput"
                                    accept="image/png, image/jpeg, image/jpg" onchange="previewImage(this)"
                                    class="block w-full text-xs font-bold text-zinc-500 dark:text-zinc-400
                                    file:mr-3 file:py-1.5 file:px-3
                                    file:rounded-xl file:border-0
                                    file:text-[10px] file:font-black file:uppercase file:tracking-wider
                                    file:bg-primary/10 dark:file:bg-primary-dark/20 file:text-primary dark:file:text-primary-dark
                                    hover:file:bg-primary/20 dark:hover:file:bg-primary-dark/30 file:transition-colors file:cursor-pointer
                                    cursor-pointer m3-input-glass !p-1 shadow-2xs">
                                <p class="text-[10px] font-medium text-zinc-400 dark:text-zinc-500 mt-1 ml-1">
                                    Format: JPG/PNG. Maks: 2MB.
                                </p>
                            </div>
                        </div>
                        @error('foto')
                            <p class="text-[10px] font-bold text-rose-500 mt-1.5 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Upload Tanda Tangan -->
                    <div
                        class="p-4 rounded-2xl border {{ $errors->has('tanda_tangan') ? 'border-rose-500' : 'border-zinc-200/80 dark:border-zinc-800' }} bg-zinc-50/50 dark:bg-zinc-900/40 flex flex-col justify-center shadow-2xs">
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2.5">
                            Tanda Tangan Digital <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <!-- Preview Box -->
                            <div id="preview-ttd-container"
                                class="relative w-12 h-12 rounded-xl overflow-hidden border border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center justify-center flex-shrink-0 shadow-2xs">
                                <i class="bi bi-pen text-base text-zinc-300 dark:text-zinc-600 absolute z-0"
                                    id="ttd-placeholder-icon"></i>
                                <img id="preview-ttd"
                                    src="{{ isset($administrator) && $administrator->tanda_tangan ? asset('storage/' . $administrator->tanda_tangan) : '' }}"
                                    class="w-full h-full object-contain relative z-10 {{ isset($administrator) && $administrator->tanda_tangan ? '' : 'hidden' }} p-1"
                                    alt="Preview TTD">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="tanda_tangan" id="tanda_tangan"
                                    accept="image/png, image/jpeg, image/jpg" onchange="previewTtd(this)"
                                    class="block w-full text-xs font-bold text-zinc-500 dark:text-zinc-400
                                    file:mr-3 file:py-1.5 file:px-3
                                    file:rounded-xl file:border-0
                                    file:text-[10px] file:font-black file:uppercase file:tracking-wider
                                    file:bg-primary/10 dark:file:bg-primary-dark/20 file:text-primary dark:file:text-primary-dark
                                    hover:file:bg-primary/20 dark:hover:file:bg-primary-dark/30 file:transition-colors file:cursor-pointer
                                    cursor-pointer m3-input-glass !p-1 shadow-2xs">
                                <p class="text-[10px] font-medium text-zinc-400 dark:text-zinc-500 mt-1 ml-1">
                                    Wajib PNG Transparan. Maks: 2MB.
                                </p>
                            </div>
                        </div>
                        @error('tanda_tangan')
                            <p class="text-[10px] font-bold text-rose-500 mt-1.5 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <!-- ================= CARD 2: PENGATURAN AKUN & STATUS ================= -->
        <div class="m3-glass-card p-5 sm:p-7 shadow-2xs">

            <div class="flex items-center gap-3 mb-6 pb-3.5 border-b border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 shadow-2xs">
                    <i class="bi bi-shield-lock-fill text-lg"></i>
                </div>
                <div>
                    <h3
                        class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                        Pengaturan Akun & Status
                    </h3>
                    <p class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                        Kredensial login aplikasi dan status operasional
                    </p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start justify-between">

                <!-- Left Column: User & Pass -->
                <div class="w-full lg:w-2/3 space-y-4">
                    <!-- Username -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Username Login (Opsional)
                        </label>
                        <input type="text" name="username"
                            value="{{ old('username', $administrator->user?->username ?? '') }}"
                            placeholder="Contoh: ustadz123"
                            class="m3-input-glass w-full text-xs font-bold font-mono {{ $errors->has('username') ? '!border-rose-500' : '' }}">
                        @error('username')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Email Login (Opsional)
                        </label>
                        <input type="email" name="email"
                            value="{{ old('email', $administrator->user?->email ?? '') }}"
                            placeholder="Contoh: ustadz@madrasah.com"
                            class="m3-input-glass w-full text-xs font-bold font-mono {{ $errors->has('email') ? '!border-rose-500' : '' }}">
                        @error('email')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div
                        class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-start gap-3 shadow-2xs">
                        <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400 text-base mt-0.5"></i>
                        <div>
                            <p
                                class="text-[11px] font-black text-blue-800 dark:text-blue-300 uppercase tracking-wider mb-0.5">
                                Akses Login Opsional
                            </p>
                            <p class="text-[11px] font-medium text-blue-700 dark:text-blue-400/80 leading-relaxed">
                                Kosongkan <span class="font-bold text-blue-900 dark:text-blue-200">Username &
                                    Email</span> jika ustadz/administrator ini belum membutuhkan akses login. Default
                                Password jika diisi: <span
                                    class="font-mono font-bold text-blue-900 dark:text-blue-200 border-b border-blue-400/50 pb-0.5">madrasah123</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Toggle Status Aktif (Right Column) -->
                <div
                    class="w-full lg:w-1/3 flex flex-col justify-center bg-zinc-50/70 dark:bg-zinc-900/40 p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                    <div class="mb-3.5">
                        <span class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider block">
                            Status Akun
                        </span>
                        <span
                            class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-1 block leading-relaxed">
                            Pendidik tidak aktif tidak akan ditampilkan di jadwal.
                        </span>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer min-h-[40px] group">
                        <input type="checkbox" name="is_active" id="is_active_toggle" value="1"
                            class="sr-only peer"
                            {{ old('is_active', $administrator->is_active ?? true) ? 'checked' : '' }}>

                        <!-- Switch Track M3 OLED -->
                        <div
                            class="relative w-10 h-5 bg-zinc-200 dark:bg-zinc-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-zinc-400 after:rounded-full after:h-4 after:w-4 after:transition-all after:shadow-sm peer-checked:bg-primary dark:peer-checked:bg-primary-dark peer-checked:after:bg-white dark:peer-checked:after:bg-zinc-900 transition-colors">
                        </div>

                        <span class="ml-3 text-xs font-black text-zinc-700 dark:text-zinc-300">Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ================= TOMBOL AKSI ================= -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-2">
            <a href="{{ route('administrator.index') }}"
                class="h-10 w-full sm:w-auto px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl font-bold text-xs flex items-center justify-center transition-all shadow-2xs outline-none">
                Batal
            </a>
            <button type="submit" class="m3-btn-primary w-full sm:w-auto h-10 px-6 text-xs font-black shadow-2xs">
                <i class="bi bi-save2-fill mr-1.5"></i>
                {{ isset($administrator) ? 'Simpan Perubahan' : 'Simpan Data' }}
            </button>
        </div>
    </form>

    <!-- JS Logic Default Tetap Utuh -->
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

        // 4. Role & Tingkat Visibility Toggle
        const roleSelect = document.getElementById('role_select');
        const tingkatWrapper = document.getElementById('tingkat_wrapper');
        const tingkatSelect = document.getElementById('tingkat_select');
        const tingkatInfoNote = document.getElementById('tingkat_info_note');

        function updateTingkatVisibility() {
            if (!roleSelect) return;
            const role = roleSelect.value;
            if (role === 'staff') {
                tingkatWrapper?.classList.remove('hidden');
                tingkatInfoNote?.classList.add('hidden');
            } else {
                tingkatWrapper?.classList.add('hidden');
                tingkatInfoNote?.classList.remove('hidden');
                if (tingkatSelect) {
                    tingkatSelect.value = '';
                }
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', updateTingkatVisibility);
            // Run on initial load
            updateTingkatVisibility();
        }
    </script>
</x-app-layout>
