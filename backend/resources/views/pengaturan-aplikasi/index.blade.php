@section('title', 'Pengaturan Aplikasi')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Pengaturan Aplikasi
            </h2>
            <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                Kelola informasi madrasah, branding, dan sosial media.
            </p>
        </div>
    </div>

    <!-- FORM DENGAN ENCTYPE MULTIPART -->
    <form action="{{ route('pengaturan-aplikasi.update') }}" method="POST" enctype="multipart/form-data"
        class="relative z-10">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 md:gap-6">

            <!-- KOLOM KIRI (Lebar) -->
            <div class="xl:col-span-8 flex flex-col gap-5 md:gap-6">

                <!-- CARD 1: IDENTITAS INSTANSI -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight mb-5 flex items-center border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mr-2.5 text-emerald-600 dark:text-emerald-400 text-sm shrink-0">
                            <i class="bi bi-building"></i>
                        </div>
                        Identitas Madrasah
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nama Madrasah / Sekolah
                            </label>
                            <input type="text" name="app_name" value="{{ $settings['app_name'] ?? '' }}"
                                placeholder="Contoh: MDT Hidayatus Shibyan..."
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                NSM / NPSN
                            </label>
                            <input type="text" name="app_nsm" value="{{ $settings['app_nsm'] ?? '' }}"
                                placeholder="Nomor Statistik Madrasah" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nomor Telepon / WA
                            </label>
                            <input type="text" name="app_phone" value="{{ $settings['app_phone'] ?? '' }}"
                                placeholder="0812-3456-7890" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Alamat Email
                            </label>
                            <input type="email" name="app_email" value="{{ $settings['app_email'] ?? '' }}"
                                placeholder="email@madrasah.com" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Website
                            </label>
                            <input type="text" name="app_website" value="{{ $settings['app_website'] ?? '' }}"
                                placeholder="https://www.madrasah.com" class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Alamat Lengkap
                            </label>
                            <textarea name="app_address" rows="3" placeholder="Jalan, RT/RW, Desa, Kecamatan, Kab/Kota, Provinsi, Kode Pos"
                                class="m3-input-glass w-full text-xs font-bold resize-none custom-scrollbar">{{ $settings['app_address'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: BRANDING & LOGO -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight mb-5 flex items-center border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                        <div
                            class="w-8 h-8 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center mr-2.5 text-purple-600 dark:text-purple-400 text-sm shrink-0">
                            <i class="bi bi-image"></i>
                        </div>
                        Branding & Logo
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Logo Utama Aplikasi -->
                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-2 ml-1">
                                Logo Aplikasi Utama
                            </label>
                            <div class="flex items-center gap-3.5 mb-2">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-white/40 dark:bg-black/40 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                    @if (isset($settings['app_logo']))
                                        <img src="{{ asset($settings['app_logo']) }}" alt="Logo App"
                                            class="w-full h-full object-contain p-2">
                                    @else
                                        <i class="bi bi-image text-zinc-400 text-xl"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="app_logo" accept="image/*"
                                        class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-500/10 file:text-emerald-600 dark:file:text-emerald-400 hover:file:bg-emerald-500/20 transition-all cursor-pointer outline-none">
                                </div>
                            </div>
                            <p class="text-[9px] font-semibold text-zinc-400">Format: PNG transparan (Rasio 1:1, Maks
                                1MB).</p>
                        </div>

                        <!-- Logo Kop Surat -->
                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-2 ml-1">
                                Logo Kop Surat / Laporan
                            </label>
                            <div class="flex items-center gap-3.5 mb-2">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-white/40 dark:bg-black/40 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                    @if (isset($settings['kop_logo']))
                                        <img src="{{ asset($settings['kop_logo']) }}" alt="Logo Kop"
                                            class="w-full h-full object-contain p-2">
                                    @else
                                        <i class="bi bi-file-earmark-richtext text-zinc-400 text-xl"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="kop_logo" accept="image/*"
                                        class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-500/10 file:text-emerald-600 dark:file:text-emerald-400 hover:file:bg-emerald-500/20 transition-all cursor-pointer outline-none">
                                </div>
                            </div>
                            <p class="text-[9px] font-semibold text-zinc-400">Logo formal yang akan digunakan pada
                                dokumen PDF atau cetak.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (Sempit) -->
            <div class="xl:col-span-4 flex flex-col gap-5 md:gap-6">

                <!-- CARD 3: SOSIAL MEDIA -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight mb-5 flex items-center border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                        <div
                            class="w-8 h-8 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center mr-2.5 text-sky-600 dark:text-sky-400 text-sm shrink-0">
                            <i class="bi bi-share-fill"></i>
                        </div>
                        Sosial Media
                    </h3>

                    <div class="flex flex-col gap-3.5">
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#1877F2] z-10">
                                <i class="bi bi-facebook text-sm"></i>
                            </div>
                            <input type="text" name="socmed_fb" value="{{ $settings['socmed_fb'] ?? '' }}"
                                placeholder="Username / Link FB" class="m3-input-glass w-full !pl-9 text-xs font-bold">
                        </div>

                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#E4405F] z-10">
                                <i class="bi bi-instagram text-sm"></i>
                            </div>
                            <input type="text" name="socmed_ig" value="{{ $settings['socmed_ig'] ?? '' }}"
                                placeholder="Username Instagram"
                                class="m3-input-glass w-full !pl-9 text-xs font-bold">
                        </div>

                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-800 dark:text-white z-10">
                                <i class="bi bi-tiktok text-sm"></i>
                            </div>
                            <input type="text" name="socmed_tiktok"
                                value="{{ $settings['socmed_tiktok'] ?? '' }}" placeholder="Username TikTok"
                                class="m3-input-glass w-full !pl-9 text-xs font-bold">
                        </div>

                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#FF0000] z-10">
                                <i class="bi bi-youtube text-sm"></i>
                            </div>
                            <input type="text" name="socmed_youtube"
                                value="{{ $settings['socmed_youtube'] ?? '' }}" placeholder="Channel YouTube"
                                class="m3-input-glass w-full !pl-9 text-xs font-bold">
                        </div>
                    </div>
                </div>

                <!-- CARD 4: SISTEM & PIMPINAN -->
                <div class="m3-glass-card p-5 sm:p-6 rounded-3xl shadow-2xs">
                    <h3
                        class="font-black text-zinc-900 dark:text-white text-base tracking-tight mb-5 flex items-center border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mr-2.5 text-amber-600 dark:text-amber-400 text-sm shrink-0">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        Data Pimpinan
                    </h3>

                    <div class="flex flex-col gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                Nama Pengasuh Madrasah
                            </label>
                            <input type="text" name="headmaster_name"
                                value="{{ $settings['headmaster_name'] ?? '' }}"
                                placeholder="Nama Pengasuh beserta gelar"
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1.5 ml-1">
                                NIY / NIGM Pengasuh (Opsional)
                            </label>
                            <input type="text" name="headmaster_nip"
                                value="{{ $settings['headmaster_nip'] ?? '' }}"
                                placeholder="Nomor Induk Guru / Yayasan"
                                class="m3-input-glass w-full text-xs font-bold">
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-zinc-200/80 dark:border-zinc-800">
                        <button type="submit"
                            class="m3-btn-primary w-full h-11 text-xs font-black shadow-2xs flex items-center justify-center gap-2">
                            <i class="bi bi-save-fill text-xs"></i>
                            <span>Simpan Pengaturan</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>

</x-app-layout>
