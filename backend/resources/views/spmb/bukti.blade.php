@section('title', 'Bukti Pendaftaran SPMB - ' . $pendaftaran->nomor_pendaftaran)

<x-auth-layout maxWidth="max-w-2xl">
    <div class="space-y-6">
        <!-- Header / Success Badge -->
        <div class="text-center">
            <div
                class="inline-flex items-center justify-center w-14 h-14 rounded-3xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/30 mb-2">
                <i class="bi bi-check2-circle text-2xl font-black"></i>
            </div>
            <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                Bukti Pendaftaran Murid Baru
            </h2>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Simpan atau cetak kartu ini untuk verifikasi langsung ke Panitia/Admin Madrasah
            </p>
        </div>

        @if (session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center gap-2">
                <i class="bi bi-patch-check-fill text-lg shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Kartu Bukti Pendaftaran (Printable Area) -->
        <div class="p-6 rounded-3xl bg-zinc-50/80 dark:bg-zinc-900/80 border border-zinc-200/80 dark:border-zinc-800 space-y-5 shadow-sm relative overflow-hidden"
            id="printableCard">
            <!-- Decorative Accent -->
            <div class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-4">
                <div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-primary dark:text-primary-dark">
                        MDT HIDAYATUS SHIBYAN
                    </span>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white">
                        KARTU REGISTRASI SPMB
                    </h3>
                    <p class="text-[11px] font-bold text-zinc-500">
                        TP. {{ $pendaftaran->tahunPelajaran->nama_hijriyah ?? '-' }}
                        ({{ $pendaftaran->tahunPelajaran->nama_masehi ?? '-' }})
                    </p>
                </div>

                <div class="text-right">
                    <span
                        class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                        {{ $pendaftaran->status_pendaftaran == 'Diterima' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30' : ($pendaftaran->status_pendaftaran == 'Ditolak' ? 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30' : 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30') }}">
                        {{ $pendaftaran->status_pendaftaran }}
                    </span>
                    <p class="text-[10px] font-mono text-zinc-400 mt-1">
                        {{ $pendaftaran->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>

            <!-- QR Code & No Pendaftaran -->
            <div
                class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-2xl bg-white dark:bg-zinc-950 border border-zinc-200/80 dark:border-zinc-800">
                <div
                    class="p-2 bg-white rounded-xl shadow-xs shrink-0 flex items-center justify-center border border-zinc-100">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($pendaftaran->nomor_pendaftaran) !!}
                </div>

                <div class="space-y-1.5 text-center sm:text-left min-w-0 flex-1">
                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                        NOMOR PENDAFTARAN RESMI
                    </span>
                    <h4
                        class="text-xl font-mono font-black text-primary dark:text-primary-dark tracking-wider select-all">
                        {{ $pendaftaran->nomor_pendaftaran }}
                    </h4>
                    <p class="text-xs text-zinc-600 dark:text-zinc-300 font-semibold">
                        Tunjukkan QR Code ini kepada Admin Madrasah untuk scan & validasi verifikasi penerimaan.
                    </p>
                    @if ($pendaftaran->nism_diberikan)
                        <div
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-black mt-1">
                            <i class="bi bi-person-badge"></i>
                            <span>NISM Resmi: {{ $pendaftaran->nism_diberikan }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Detail Data Calon Murid & Wali -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Kolom Murid -->
                <div
                    class="p-4 rounded-2xl bg-white/60 dark:bg-zinc-900/60 border border-zinc-200/70 dark:border-zinc-800 space-y-2">
                    <h5
                        class="text-[10px] font-black text-zinc-400 uppercase tracking-wider border-b border-zinc-100 dark:border-zinc-800 pb-1">
                        Data Calon Murid
                    </h5>
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold">Nama Lengkap:</span>
                        <span
                            class="font-black text-zinc-900 dark:text-white uppercase">{{ $pendaftaran->nama_lengkap }}</span>
                    </div>
                    <div class="flex justify-between">
                        <div>
                            <span class="text-[10px] text-zinc-400 block font-bold">Jenis Kelamin:</span>
                            <span
                                class="font-bold text-zinc-800 dark:text-zinc-200">{{ $pendaftaran->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-400 block font-bold">Pilihan Kelas:</span>
                            <span
                                class="font-black text-primary dark:text-primary-dark">{{ $pendaftaran->level->nama_level ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold">NIK / TTL:</span>
                        <span
                            class="font-medium text-zinc-700 dark:text-zinc-300 font-mono">{{ mask_nik($pendaftaran->nik) }}
                            • {{ $pendaftaran->tempat_lahir }},
                            {{ $pendaftaran->tanggal_lahir ? $pendaftaran->tanggal_lahir->format('d/m/Y') : '-' }}</span>
                    </div>
                </div>

                <!-- Kolom Wali / Keluarga -->
                <div
                    class="p-4 rounded-2xl bg-white/60 dark:bg-zinc-900/60 border border-zinc-200/70 dark:border-zinc-800 space-y-2">
                    <h5
                        class="text-[10px] font-black text-zinc-400 uppercase tracking-wider border-b border-zinc-100 dark:border-zinc-800 pb-1">
                        Data Keluarga & Wali
                    </h5>
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold">Kepala Keluarga:</span>
                        <span
                            class="font-black text-zinc-900 dark:text-white uppercase">{{ $pendaftaran->waliMurid->nama_kepala_keluarga ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold">No. Kartu Keluarga (KK):</span>
                        <span
                            class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $pendaftaran->waliMurid->no_kk ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-bold">No. HP / Zonasi:</span>
                        <span
                            class="font-medium text-zinc-700 dark:text-zinc-300">{{ $pendaftaran->waliMurid->no_hp ?? '-' }}
                            • {{ $pendaftaran->waliMurid->kampung->nama_kampung ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Langkah Verifikasi -->
            <div class="p-4 rounded-2xl bg-primary/5 border border-primary/20 text-xs space-y-2">
                <h5
                    class="font-black text-primary dark:text-primary-dark flex items-center gap-1.5 uppercase text-[10px] tracking-wider">
                    <i class="bi bi-info-circle-fill"></i> Langkah Selanjutnya
                </h5>
                <ol
                    class="list-decimal list-inside space-y-1 text-zinc-600 dark:text-zinc-300 text-[11px] font-semibold">
                    <li>Bawa kartu bukti pendaftaran ini (cetak fisik atau simpan di HP) ke kantor MDT Hidayatus
                        Shibyan.</li>
                    <li>Tunjukkan QR Code / Barcode di atas kepada petugas Admin / Panitia SPMB.</li>
                    <li>Bawa Fotocopy Kartu Keluarga/Akta Kelahiran Calon Murid Baru.</li>
                    <li>Setelah verifikasi disetujui, murid akan secara resmi mendapatkan <strong>NISM (Nomor Induk
                            Murid Madrasah)</strong> dan terdaftar aktif.</li>
                </ol>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <a href="{{ route('spmb.cetak-bukti', $pendaftaran->nomor_pendaftaran) }}" target="_blank"
                class="m3-btn-primary w-full sm:w-auto flex-1 h-11 text-xs font-black shadow-md flex items-center justify-center gap-2">
                <i class="bi bi-printer-fill text-sm"></i>
                <span>Cetak / Download PDF Bukti Pendaftaran</span>
            </a>

            <a href="{{ route('spmb.form') }}"
                class="m3-btn-secondary w-full sm:w-auto h-11 text-xs font-black flex items-center justify-center gap-2 px-5">
                <i class="bi bi-person-plus-fill"></i>
                <span>Daftar Lagi</span>
            </a>
        </div>
    </div>
</x-auth-layout>
