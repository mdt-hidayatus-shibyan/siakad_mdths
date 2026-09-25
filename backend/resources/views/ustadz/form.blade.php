@section('title', 'Tambah Ustadz Baru')

<x-app-layout>
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('ustadz.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none border border-zinc-200 dark:border-zinc-700"
                title="Kembali">
                <i class="bi bi-arrow-left text-base"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Tambah Ustadz
                    Baru</h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">Lengkapi formulir
                    di bawah ini dengan data yang valid.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('ustadz.store') }}" method="POST" enctype="multipart/form-data"
        class="space-y-6 relative z-10">
        @csrf

        <!-- CARD 1: PROFIL -->
        <div class="m3-glass-card p-5 sm:p-8 shadow-2xs">
            <div class="flex items-center gap-3 mb-6 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center shrink-0 border border-primary/20 shadow-2xs">
                    <i class="bi bi-person-vcard-fill text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">Informasi
                        Pribadi & Berkas</h3>
                    <p class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                        Data biodata identitas pendidik</p>
                </div>
            </div>

            <div class="space-y-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Nama
                            Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                            placeholder="Contoh: Ahmad, S.Pd.I"
                            class="m3-input-glass w-full text-xs font-bold uppercase">
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Jenis
                            Kelamin <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label for="jk_l"
                                class="flex items-center gap-3 h-10 px-4 bg-zinc-50/70 dark:bg-zinc-950/50 border border-zinc-200/80 dark:border-zinc-800 rounded-xl cursor-pointer hover:bg-zinc-100/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all shadow-2xs">
                                <input type="radio" name="jenis_kelamin" id="jk_l" value="L"
                                    class="w-4 h-4 accent-primary" {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }}>
                                <span class="text-xs font-black text-zinc-700 dark:text-zinc-300">Laki-laki</span>
                            </label>
                            <label for="jk_p"
                                class="flex items-center gap-3 h-10 px-4 bg-zinc-50/70 dark:bg-zinc-950/50 border border-zinc-200/80 dark:border-zinc-800 rounded-xl cursor-pointer hover:bg-zinc-100/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all shadow-2xs">
                                <input type="radio" name="jenis_kelamin" id="jk_p" value="P"
                                    class="w-4 h-4 accent-primary" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                <span class="text-xs font-black text-zinc-700 dark:text-zinc-300">Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">NIGM
                            (No. Induk Guru)</label>
                        <input type="text" name="nigm" value="{{ old('nigm') }}"
                            placeholder="Otomatis jika dikosongkan"
                            class="m3-input-glass w-full text-xs font-bold font-mono @error('nigm') !border-rose-500 @enderror">
                        @error('nigm')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">NIK
                            (16 Digit)</label>
                        <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16"
                            placeholder="Opsional 16 digit angka"
                            class="m3-input-glass w-full text-xs font-bold font-mono @error('nik') !border-rose-500 @enderror">
                        @error('nik')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">No.
                            HP / WhatsApp</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Cth: 08123456789"
                            class="m3-input-glass w-full text-xs font-bold font-mono @error('no_hp') !border-rose-500 @enderror">
                        @error('no_hp')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tempat
                            Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                            placeholder="Kota/Kabupaten kelahiran"
                            class="m3-input-glass w-full text-xs font-bold uppercase">
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tanggal
                            Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                            class="m3-input-glass w-full text-xs font-bold cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Alamat
                            Domisili</label>
                        <textarea name="alamat" rows="2" placeholder="Nama kampung, RT/RW, atau jalan..."
                            class="m3-input-glass w-full !p-3 text-xs font-bold custom-scrollbar resize-none">{{ old('alamat') }}</textarea>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Tahun
                            Mulai Mengajar</label>
                        <input type="number" name="tahun_mulai_mengajar" min="1950" max="{{ date('Y') }}"
                            placeholder="{{ date('Y') }}" value="{{ old('tahun_mulai_mengajar') }}"
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
                                    src="{{ asset(old('jenis_kelamin', 'L') === 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png') }}"
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
                            Tangan Digital </label>
                        <div class="flex items-center gap-3">
                            <div id="preview-ttd-container"
                                class="relative w-12 h-12 rounded-xl overflow-hidden border border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 flex items-center justify-center shadow-2xs shrink-0">
                                <i class="bi bi-pen text-base text-zinc-300 dark:text-zinc-600 absolute z-0"
                                    id="ttd-placeholder-icon"></i>
                                <img id="preview-ttd" src=""
                                    class="w-full h-full object-contain relative z-10 hidden p-1">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="tanda_tangan" accept="image/png, image/jpeg, image/jpg"
                                    onchange="previewTtd(this)"
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
        </div>

        <!-- CARD 2: AKUN -->
        <div class="m3-glass-card p-5 sm:p-8 shadow-2xs">
            <div class="flex items-center gap-3 mb-6 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20 shadow-2xs shrink-0">
                    <i class="bi bi-shield-lock-fill text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base md:text-lg font-black text-zinc-900 dark:text-white tracking-tight">Pengaturan
                        Akun & Status</h3>
                    <p class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">
                        Kredensial login aplikasi</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 items-start justify-between">
                <div class="w-full sm:w-2/3 space-y-4">
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Username
                            Login</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                            placeholder="Contoh: ustadz123" class="m3-input-glass w-full text-xs font-bold">
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">Email
                            Login</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="Contoh: ustadz@madrasah.com" class="m3-input-glass w-full text-xs font-bold">
                    </div>

                    <div
                        class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-start gap-3 shadow-2xs">
                        <i
                            class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <p
                                class="text-[10px] font-black text-blue-800 dark:text-blue-300 uppercase tracking-wider mb-0.5">
                                Akses Login Opsional</p>
                            <p class="text-xs font-bold text-blue-900 dark:text-blue-300/80 leading-relaxed">
                                Kosongkan <span class="font-mono">Username & Email</span> jika belum membutuhkan akses
                                login. Jika diisi, Default Password: <span
                                    class="font-mono underline">madrasah123</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="w-full sm:w-1/3 flex flex-col justify-center bg-zinc-50/70 dark:bg-zinc-950/50 p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                    <div class="mb-4">
                        <span
                            class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider block">Status
                            Akun</span>
                        <span class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 block">Pendidik
                            tidak aktif tidak akan ditampilkan di jadwal.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer min-h-[40px] group">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <div
                            class="relative w-11 h-6 bg-zinc-200 dark:bg-zinc-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary dark:border-zinc-600 shadow-2xs">
                        </div>
                        <span
                            class="ml-3 text-xs font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">
            <a href="{{ route('ustadz.index') }}"
                class="h-11 px-6 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl font-black text-xs flex items-center justify-center outline-none shadow-2xs">Batal</a>
            <button type="submit"
                class="m3-btn-primary w-full sm:w-auto h-11 px-8 text-xs font-black shadow-2xs group/btn">
                <i class="bi bi-save2-fill mr-1.5 text-sm"></i> Simpan Data Ustadz
            </button>
        </div>
    </form>

    <script>
        const defaultUstadz = "{{ asset('assets/laki-default.png') }}";
        const defaultUstadzah = "{{ asset('assets/perempuan-default.png') }}";
        const preview = document.getElementById('fotoPreview');
        const radiosJenisKelamin = document.querySelectorAll('input[name="jenis_kelamin"]');

        let isShowingCustomPhoto = false;

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    isShowingCustomPhoto = true;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                isShowingCustomPhoto = false;
                const checkedRadio = document.querySelector('input[name="jenis_kelamin"]:checked');
                const activeGender = checkedRadio ? checkedRadio.value : 'L';
                preview.src = activeGender === 'L' ? defaultUstadz : defaultUstadzah;
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
                document.getElementById('preview-ttd').classList.add('hidden');
                document.getElementById('ttd-placeholder-icon').classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
