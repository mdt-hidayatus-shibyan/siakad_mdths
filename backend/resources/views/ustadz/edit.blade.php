@section('title', 'Edit Data Ustadz')

<x-app-layout>
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('ustadz.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none border border-zinc-200 dark:border-zinc-700"
                title="Kembali">
                <i class="bi bi-arrow-left text-base"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Edit Data Ustadz
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">Perbarui profil,
                    berkas, atau akses login pendidik.</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- ================= FORM 1: PROFIL & BERKAS ================= -->
        <form action="{{ route('ustadz.update', $ustadz->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="profil">

            <div class="m3-glass-card p-5 sm:p-8 relative z-10 shadow-2xs">
                <div class="flex items-center gap-3 mb-6 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                    <div
                        class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center shrink-0 border border-primary/20 shadow-2xs">
                        <i class="bi bi-person-vcard-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                            Informasi Pribadi & Berkas</h3>
                        <p
                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                            Biodata resmi dan dokumen pendidik</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Nama
                                Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_lengkap" required
                                value="{{ old('nama_lengkap', $ustadz->nama_lengkap) }}"
                                class="m3-input-glass w-full text-xs font-bold uppercase">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Jenis
                                Kelamin <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="flex items-center gap-3 h-10 px-4 bg-zinc-50/70 dark:bg-zinc-950/50 border border-zinc-200/80 dark:border-zinc-800 rounded-xl cursor-pointer hover:bg-zinc-100/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all shadow-2xs">
                                    <input type="radio" name="jenis_kelamin" value="L"
                                        class="w-4 h-4 accent-primary"
                                        {{ old('jenis_kelamin', $ustadz->jenis_kelamin) == 'L' ? 'checked' : '' }}>
                                    <span class="text-xs font-black text-zinc-700 dark:text-zinc-300">Laki-laki</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 h-10 px-4 bg-zinc-50/70 dark:bg-zinc-950/50 border border-zinc-200/80 dark:border-zinc-800 rounded-xl cursor-pointer hover:bg-zinc-100/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all shadow-2xs">
                                    <input type="radio" name="jenis_kelamin" value="P"
                                        class="w-4 h-4 accent-primary"
                                        {{ old('jenis_kelamin', $ustadz->jenis_kelamin) == 'P' ? 'checked' : '' }}>
                                    <span class="text-xs font-black text-zinc-700 dark:text-zinc-300">Perempuan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">NIK
                                (16 Digit)</label>
                            <input type="text" name="nik" value="{{ old('nik', $ustadz->nik) }}" maxlength="16"
                                placeholder="16 digit angka" class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">No.
                                HP / WhatsApp</label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', $ustadz->no_hp) }}"
                                placeholder="Cth: 08123456789"
                                class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tempat
                                Lahir</label>
                            <input type="text" name="tempat_lahir"
                                value="{{ old('tempat_lahir', $ustadz->tempat_lahir) }}"
                                placeholder="Kota/Kabupaten kelahiran"
                                class="m3-input-glass w-full text-xs font-bold uppercase">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tanggal
                                Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', $ustadz->tanggal_lahir ? \Carbon\Carbon::parse($ustadz->tanggal_lahir)->format('Y-m-d') : '') }}"
                                class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Alamat
                                Domisili</label>
                            <textarea name="alamat" rows="2" placeholder="Nama kampung, RT/RW, atau jalan..."
                                class="m3-input-glass w-full !p-3 text-xs font-bold custom-scrollbar resize-none">{{ old('alamat', $ustadz->alamat) }}</textarea>
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tahun
                                Mulai Mengajar</label>
                            <input type="number" name="tahun_mulai_mengajar" min="1950" max="{{ date('Y') }}"
                                placeholder="{{ date('Y') }}"
                                value="{{ old('tahun_mulai_mengajar', $ustadz->tahun_mulai_mengajar) }}"
                                class="m3-input-glass w-full text-xs font-bold font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-2">
                        <div
                            class="p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-950/50 shadow-2xs">
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-3 ml-1">Upload
                                Foto Profil</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 flex items-center justify-center p-0.5 shadow-2xs shrink-0">
                                    <img id="fotoPreview"
                                        src="{{ $ustadz->foto ? asset('storage/' . $ustadz->foto) : asset($ustadz->jenis_kelamin === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
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

                        <div
                            class="p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-950/50 shadow-2xs">
                            <label
                                class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-3 ml-1">Tanda
                                Tangan Digital</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="relative w-12 h-12 rounded-xl overflow-hidden border border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 flex items-center justify-center shadow-2xs shrink-0">
                                    <i class="bi bi-pen text-base text-zinc-300 dark:text-zinc-600 absolute z-0 {{ $ustadz->tanda_tangan ? 'hidden' : '' }}"
                                        id="ttd-placeholder-icon"></i>
                                    <img id="preview-ttd"
                                        src="{{ $ustadz->tanda_tangan ? asset('storage/' . $ustadz->tanda_tangan) : '' }}"
                                        class="w-full h-full object-contain relative z-10 {{ $ustadz->tanda_tangan ? '' : 'hidden' }} p-1">
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

                <div class="flex justify-end pt-5 mt-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    <button type="submit"
                        class="m3-btn-primary w-full sm:w-auto h-11 px-8 text-xs font-black shadow-2xs group/btn">
                        <i class="bi bi-person-check-fill mr-1.5 text-sm"></i> Perbarui Profil
                    </button>
                </div>
            </div>
        </form>

        <!-- ================= FORM 2: PENGATURAN AKUN ================= -->
        <form action="{{ route('ustadz.update', $ustadz->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="akun">

            <div class="m3-glass-card p-5 sm:p-8 relative shadow-2xs">
                <div class="flex items-center gap-3 mb-6 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                    <div
                        class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20 shadow-2xs shrink-0">
                        <i class="bi bi-shield-lock-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                            Pengaturan Akun & Akses Login</h3>
                        <p
                            class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                            Kredensial akun sistem</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Username
                            Login</label>
                        <input type="text" name="username"
                            value="{{ old('username', $ustadz->user?->username ?? '') }}"
                            placeholder="Contoh: ustadz123"
                            class="m3-input-glass w-full text-xs font-bold @error('username') !border-rose-500 @enderror">
                        @error('username')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Email
                            Login</label>
                        <input type="email" name="email" value="{{ old('email', $ustadz->user?->email ?? '') }}"
                            placeholder="Contoh: ustadz@madrasah.com"
                            class="m3-input-glass w-full text-xs font-bold @error('email') !border-rose-500 @enderror">
                        @error('email')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-5 mt-5 border-t border-zinc-200/80 dark:border-zinc-800">
                    <button type="submit"
                        class="m3-btn-primary bg-amber-600 hover:bg-amber-700 border-amber-600 h-11 px-8 text-xs font-black shadow-2xs">
                        <i class="bi bi-shield-check mr-1.5 text-sm"></i> Perbarui Akun
                    </button>
                </div>
            </div>
        </form>
        <!-- ================= CARD 3: STATUS KEAKTIFAN ================= -->
        <div class="m3-glass-card p-5 sm:p-8 mt-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">

                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-500/20 shrink-0 shadow-2xs">
                        <i class="bi bi-person-badge-fill text-lg"></i>
                    </div>
                    <div>
                        <h3
                            class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight mb-0.5">
                            Status Ustadz/Guru
                        </h3>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 leading-relaxed">
                            Ustadz/Guru yang tidak aktif tidak akan ditampilkan di jadwal mengajar dan tidak dapat
                            login.
                        </p>
                    </div>
                </div>

                <!-- Pemanggilan Komponen Toggle Component -->
                <div class="shrink-0 sm:pl-4 self-end sm:self-center">
                    <x-toggle-status :is-active="$ustadz->is_active" :url="route('ustadz.toggle-status', $ustadz->id)" />
                </div>

            </div>
        </div>
    </div>

    <!-- CARD 4: DANGER ZONE -->
    @can('delete ustadz')
        <div
            class="mt-6 sm:mt-8 bg-rose-500/5 dark:bg-rose-950/20 rounded-2xl p-5 sm:p-7 border border-rose-500/20 relative overflow-hidden shadow-2xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 relative z-10">
                <div class="flex items-start gap-3.5 flex-1">
                    <div
                        class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-500/20 shadow-2xs">
                        <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-black text-rose-700 dark:text-rose-400 tracking-tight">Hapus Data Ustadz
                        </h3>
                        <p class="text-xs font-bold text-rose-800/80 dark:text-rose-300/80 mt-1 max-w-md mb-3">Tindakan ini
                            tidak dapat dibatalkan. Semua data terkait ustadz ini (termasuk akun login) akan dihapus
                            permanen.</p>
                        <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" id="verify_delete" class="peer sr-only">
                                <div
                                    class="w-4 h-4 rounded border-2 border-rose-400/50 peer-checked:bg-rose-500 peer-checked:border-rose-500 transition-all bg-white dark:bg-zinc-900 shadow-2xs">
                                </div>
                                <i
                                    class="bi bi-check-lg absolute text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 text-xs font-black transition-all pointer-events-none"></i>
                            </div>
                            <span
                                class="text-[11px] font-black text-rose-700 dark:text-rose-400 uppercase tracking-wider select-none">Saya
                                mengerti dan ingin menghapus data ini</span>
                        </label>
                    </div>
                </div>
                <div class="w-full sm:w-auto flex-shrink-0 mt-2 sm:mt-0">
                    <form action="{{ route('ustadz.destroy', $ustadz->id) }}" method="POST"
                        class="delete-ajax inline m-0 p-0">
                        @csrf @method('DELETE')
                        <button type="submit" id="btn_delete_permanen" disabled
                            class="h-10 w-full sm:w-auto px-6 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow-2xs transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
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

        let isShowingCustomPhoto = {{ $ustadz->foto ? 'true' : 'false' }};

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    isShowingCustomPhoto = true;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                isShowingCustomPhoto = {{ $ustadz->foto ? 'true' : 'false' }};
                if (!isShowingCustomPhoto) {
                    const checkedRadio = document.querySelector('input[name="jenis_kelamin"]:checked');
                    const activeGender = checkedRadio ? checkedRadio.value : 'L';
                    preview.src = activeGender === 'L' ? defaultUstadz : defaultUstadzah;
                } else {
                    preview.src = "{{ $ustadz->foto ? asset('storage/' . $ustadz->foto) : '' }}";
                }
            }
        }

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
                const hasOldTtd = {{ $ustadz->tanda_tangan ? 'true' : 'false' }};

                if (hasOldTtd) {
                    imgPreview.src = "{{ $ustadz->tanda_tangan ? asset('storage/' . $ustadz->tanda_tangan) : '' }}";
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
    </script>
</x-app-layout>
