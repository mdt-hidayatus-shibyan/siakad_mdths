<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased dark">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pengesahan Surat Resmi - {{ $surat->nomor_surat }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (CDN Fallback & Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: '#10b981',
                        'primary-dark': '#34d399',
                    }
                }
            }
        }
    </script>
</head>

<body
    class="font-sans text-zinc-900 bg-[#09090b] text-zinc-100 min-h-screen selection:bg-emerald-500 selection:text-white flex flex-col items-center justify-start p-4 sm:p-6 lg:p-10 relative overflow-x-hidden">

    <!-- Ambient Glow Background -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-40 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-emerald-700/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Container Utama -->
    <div class="w-full max-w-3xl space-y-6">

        <!-- HEADER BRANDING MADRASAH -->
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 bg-[#0c0c0e]/90 backdrop-blur-xl border border-zinc-800/80 rounded-3xl shadow-2xl">
            <div class="flex items-center gap-3.5 text-center sm:text-left">
                <div
                    class="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-700/80 p-2 flex items-center justify-center shrink-0 shadow-sm">
                    <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo MDTHS"
                        class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-[10px] font-extrabold tracking-widest text-emerald-400 uppercase block">
                        Sistem Administrasi Persuratan & E-Signature
                    </span>
                    <h1 class="text-base sm:text-lg font-black text-white tracking-tight leading-snug">
                        {{ getSetting('app_name', 'MDT HIDAYATUS SHIBYAN') }}
                    </h1>
                    <p class="text-[11px] font-medium text-zinc-400 leading-tight">
                        {{ getSetting('app_address', 'Jl. Raya Pendidikan, Somorkoneng, Kec. Kwanyar, Kab. Bangkalan') }}
                    </p>
                </div>
            </div>

            <!-- Tombol Cek Dokumen Lain -->
            <a href="{{ route('surat.verifikasi.index') }}"
                class="px-4 py-2 bg-zinc-800/80 hover:bg-zinc-700 text-zinc-300 hover:text-white rounded-xl text-xs font-bold border border-zinc-700 transition-all flex items-center gap-1.5 shrink-0 active:scale-95">
                <i class="bi bi-search text-xs"></i>
                <span>Cek Nomor Lain</span>
            </a>
        </div>

        <!-- HERO STATUS PENGESAHAN DOKUMEN -->
        <div
            class="p-6 sm:p-8 bg-gradient-to-br from-emerald-950/40 via-zinc-900 to-[#0c0c0e] border-2 border-emerald-500/40 rounded-3xl shadow-2xl relative overflow-hidden">
            <!-- Watermark Shield Icon -->
            <i
                class="bi bi-shield-check absolute -right-6 -bottom-8 text-9xl text-emerald-500/5 pointer-events-none"></i>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-black uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <i class="bi bi-patch-check-fill text-sm"></i>
                        <span>Dokumen Resmi Terverifikasi & Sah</span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        {{ $surat->perihal }}
                    </h2>

                    <p class="text-xs font-mono font-bold text-emerald-300 flex items-center gap-2">
                        <i class="bi bi-file-earmark-text text-sm"></i>
                        <span>No. Surat: <b>{{ $surat->nomor_surat }}</b></span>
                    </p>
                </div>

                <!-- Box QR Code Token -->
                <div
                    class="flex flex-col items-center justify-center p-3 bg-white rounded-2xl shadow-xl shrink-0 border-2 border-emerald-500/50">
                    <div class="w-24 h-24 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(96)->margin(0)->generate(url()->current()) !!}
                    </div>
                    <span
                        class="text-[8px] font-black text-zinc-700 font-mono mt-1.5 uppercase tracking-widest text-center">
                        {{ $surat->qr_token }}
                    </span>
                </div>
            </div>

            <!-- Timestamp Bar -->
            <div
                class="mt-6 pt-4 border-t border-zinc-800/80 flex flex-wrap items-center justify-between gap-2 text-[11px] font-medium text-zinc-400">
                <div class="flex items-center gap-1.5">
                    <i class="bi bi-clock-history text-emerald-400"></i>
                    <span>Diverifikasi pada: <b class="text-zinc-200">{{ $waktuCek }}</b></span>
                </div>
                <div class="flex items-center gap-1.5 text-emerald-400 font-bold">
                    <i class="bi bi-lock-fill"></i>
                    <span>Tanda Tangan Elektronik Bersertifikat MDTHS</span>
                </div>
            </div>
        </div>

        <!-- DETAIL INFORMASI SURAT -->
        <div
            class="p-6 sm:p-8 bg-[#0c0c0e]/90 backdrop-blur-xl border border-zinc-800/80 rounded-3xl shadow-xl space-y-6">

            <h3
                class="text-sm font-black uppercase tracking-wider text-emerald-400 flex items-center gap-2 border-b border-zinc-800 pb-3">
                <i class="bi bi-info-circle-fill"></i> Rincian Informasi Dokumen
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">

                <!-- Jenis Surat -->
                <div class="p-4 rounded-2xl bg-zinc-900/60 border border-zinc-800/60 space-y-1">
                    <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">Kategori / Jenis
                        Surat</span>
                    <span class="font-extrabold text-white text-sm flex items-center gap-1.5">
                        <i class="bi {{ $metaJenis['icon'] ?? 'bi-file-earmark-text' }} text-emerald-400"></i>
                        {{ $metaJenis['nama'] ?? 'Surat Resmi' }}
                    </span>
                </div>

                <!-- Tanggal Terbit -->
                <div class="p-4 rounded-2xl bg-zinc-900/60 border border-zinc-800/60 space-y-1">
                    <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">Tanggal
                        Surat</span>
                    <span class="font-bold text-white text-xs block">
                        {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('l, d F Y') }}
                    </span>
                    @if ($surat->tanggal_hijriyah)
                        <span class="text-[11px] font-semibold text-emerald-400 block">
                            {{ $surat->tanggal_hijriyah }}
                        </span>
                    @endif
                </div>

                <!-- Penerima / Tujuan -->
                <div class="p-4 rounded-2xl bg-zinc-900/60 border border-zinc-800/60 space-y-1">
                    <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">Tujuan /
                        Penerima</span>
                    <span class="font-extrabold text-white text-sm block">
                        {{ $surat->tujuan_surat }}
                    </span>
                    @if ($surat->alamat_tujuan)
                        <span class="text-[11px] text-zinc-400 block">
                            {{ $surat->alamat_tujuan }}
                        </span>
                    @endif
                </div>

                <!-- Sifat & Lampiran -->
                <div class="p-4 rounded-2xl bg-zinc-900/60 border border-zinc-800/60 space-y-1">
                    <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">Sifat &
                        Lampiran</span>
                    <span class="font-bold text-white text-xs block">
                        Sifat: <span
                            class="text-emerald-400 font-extrabold">{{ $surat->sifat_surat ?? 'Biasa' }}</span>
                    </span>
                    <span class="text-[11px] text-zinc-400 block">
                        Lampiran: {{ $surat->lampiran ?? '-' }}
                    </span>
                </div>

            </div>

            <!-- RINGKASAN SPESIFIK JIKA ADA -->
            @php $isi = $surat->isi_spesifik ?? []; @endphp
            @if (
                !empty($isi['hari_panggilan']) ||
                    !empty($isi['nama_kegiatan']) ||
                    !empty($isi['pokok_pemberitahuan']) ||
                    !empty($isi['tingkat_sp']))
                <div class="p-4 rounded-2xl bg-emerald-950/20 border border-emerald-800/30 space-y-2 text-xs">
                    <span
                        class="text-emerald-400 font-black text-[11px] uppercase tracking-wider block flex items-center gap-1.5">
                        <i class="bi bi-card-text"></i> Pokok Keterangan Dokumen
                    </span>

                    @if (!empty($isi['tingkat_sp']))
                        <div class="text-zinc-300">
                            <b>Tingkat SP:</b> <span class="text-rose-400 font-bold">{{ $isi['tingkat_sp'] }}</span>
                        </div>
                    @endif

                    @if (!empty($isi['hari_panggilan']))
                        <div class="text-zinc-300">
                            <b>Jadwal Menghadap:</b> {{ $isi['hari_panggilan'] }},
                            {{ $isi['tanggal_panggilan'] ?? '-' }} (Pukul: {{ $isi['waktu_panggilan'] ?? '-' }}) di
                            {{ $isi['tempat_menghadap'] ?? 'Ruang Administrasi' }}
                        </div>
                    @endif

                    @if (!empty($isi['nama_kegiatan']))
                        <div class="text-zinc-300">
                            <b>Kegiatan:</b> {{ $isi['nama_kegiatan'] }} ({{ $isi['hari_kegiatan'] ?? '' }},
                            {{ $isi['tanggal_kegiatan'] ?? '' }})
                        </div>
                    @endif

                    @if (!empty($isi['pokok_pemberitahuan']))
                        <div class="text-zinc-300 leading-relaxed">
                            <b>Keterangan:</b> {{ $isi['pokok_pemberitahuan'] }}
                        </div>
                    @endif
                </div>
            @endif

        </div>

        <!-- DAFTAR PENANDATANGAN ELEKTRONIK (TTE) -->
        <div
            class="p-6 sm:p-8 bg-[#0c0c0e]/90 backdrop-blur-xl border border-zinc-800/80 rounded-3xl shadow-xl space-y-5">

            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                    <i class="bi bi-vector-pen"></i> Pejabat Pengesah / Penandatangan Resmi (TTE)
                </h3>
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                    {{ count($signers) }} Penandatangan
                </span>
            </div>

            <div class="grid grid-cols-1 {{ count($signers) > 1 ? 'sm:grid-cols-2' : '' }} gap-4">
                @foreach ($signers as $signer)
                    <div
                        class="p-4 rounded-2xl bg-zinc-900/80 border border-zinc-800 hover:border-emerald-500/40 transition-colors flex items-start gap-3.5">

                        <!-- Badge / Icon Pejabat -->
                        <div
                            class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg shrink-0">
                            <i class="bi bi-person-check-fill"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-wider block">
                                {{ $signer['jabatan'] ?? 'Pejabat Madrasah' }}
                            </span>
                            <h4 class="text-sm font-black text-white truncate" title="{{ $signer['nama'] ?? '-' }}">
                                {{ $signer['nama'] ?? '-' }}
                            </h4>
                            @if (!empty($signer['nip']) && $signer['nip'] !== '-')
                                <p class="text-[10px] font-mono text-zinc-400">
                                    NIP/NIGM: {{ $signer['nip'] }}
                                </p>
                            @endif

                            <div class="mt-2.5 flex items-center gap-1.5 text-[10px] font-bold text-emerald-400">
                                <i class="bi bi-shield-fill-check text-xs"></i>
                                <span>Tanda Tangan Digital Terverifikasi</span>
                            </div>

                            @if (!empty($signer['id_relasi']) && !empty($signer['tipe_relasi']))
                                <a href="{{ URL::signedRoute('profil.publik', ['tipe' => $signer['tipe_relasi'], 'id' => $signer['id_relasi']]) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-[10px] text-zinc-400 hover:text-emerald-300 font-semibold mt-1 transition-colors underline">
                                    <span>Lihat Profil Pengesah</span>
                                    <i class="bi bi-box-arrow-up-right text-[8px]"></i>
                                </a>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

        <!-- FOOTER & LEGAL DISCLAIMER -->
        <div class="text-center text-[11px] text-zinc-500 space-y-2 py-4">
            <p class="leading-relaxed max-w-xl mx-auto">
                Halaman ini adalah bukti pengesahan digital resmi yang diterbitkan oleh <b>Sistem Administrasi
                    Persuratan MDT Hidayatus Shibyan</b>. Dokumen ini diakui secara sah dan dapat dipertanggungjawabkan
                keasliannya.
            </p>
            <p class="text-[10px] text-zinc-600">
                &copy; {{ date('Y') }} {{ getSetting('app_name', 'MDT Hidayatus Shibyan') }}. Seluruh Hak Cipta
                Dilindungi.
            </p>
        </div>

    </div>

</body>

</html>
