@php
    // Normalisasi Data Dinamis Berdasarkan Tipe
    $namaLengkap = '';
    $fotoPath = null;
    $ttdPath = null;
    $keteranganJabatan = $roleName; // Fallback ke roleName dari Controller
    $keteranganTambahan = 'MDT Hidayatus Shibyan';

    if (strtolower($tipe) === 'pengurus') {
        $namaLengkap = $profil->anggota->nama_lengkap ?? 'Tanpa Nama';
        $fotoPath = $profil->anggota->foto_utama ?? null;
        $ttdPath = $profil->anggota->ttd_utama ?? null;
        $keteranganJabatan = $profil->jabatan->nama_jabatan ?? $roleName;
        if ($profil->periode) {
            $keteranganTambahan = 'Periode ' . $profil->periode->nama_periode;
        }
    } else {
        // Untuk Ustadz dan Administrator
        $namaLengkap = $profil->nama_lengkap ?? 'Tanpa Nama';
        $fotoPath = $profil->foto ?? null;
        $ttdPath = $profil->tanda_tangan ?? null;
    }

    // Setup Avatar Fallback
    $fotoUrl = $fotoPath
        ? asset('storage/' . $fotoPath)
        : 'https://ui-avatars.com/api/?name=' .
            urlencode($namaLengkap) .
            '&background=10b981&color=fff&size=256&bold=true';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased dark">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi Dokumen - {{ $namaLengkap }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans text-zinc-900 bg-[#09090b] text-zinc-100 min-h-screen selection:bg-emerald-500 selection:text-white flex items-center justify-center p-4 relative overflow-x-hidden">

    <!-- Ambient Glow Background -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Card -->
    <div
        class="w-full max-w-md bg-[#0c0c0e]/90 backdrop-blur-xl border border-zinc-800 rounded-3xl shadow-2xl overflow-hidden relative z-10">

        <!-- Cover / Header -->
        <div
            class="h-28 bg-gradient-to-br from-emerald-950/40 via-zinc-900 to-[#0c0c0e] border-b border-zinc-800/80 relative overflow-hidden flex justify-between items-start p-4">
            <div class="flex items-center gap-2">
                <div
                    class="w-7 h-7 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <span class="text-[10px] font-black tracking-wider uppercase text-zinc-400">Verifikasi Resmi</span>
            </div>

            <!-- Verified Badge -->
            <div
                class="bg-emerald-500/10 text-emerald-400 text-[10px] font-black px-2.5 py-1 rounded-xl border border-emerald-500/20 flex items-center gap-1.5 shadow-2xs uppercase tracking-wider">
                <i class="bi bi-shield-fill-check text-xs"></i>
                <span>TTE Valid</span>
            </div>
        </div>

        <!-- Profile Picture -->
        <div class="flex justify-center -mt-12 relative z-10 px-6">
            <div class="w-24 h-24 bg-[#0c0c0e] rounded-3xl p-1 shadow-2xs border-2 border-emerald-500/30">
                <div class="w-full h-full rounded-2xl overflow-hidden bg-zinc-800 flex items-center justify-center">
                    <img src="{{ $fotoUrl }}" alt="Foto {{ $namaLengkap }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>

        <!-- Profile Info -->
        <div class="text-center px-6 pt-3.5 pb-6">
            <h1 class="text-lg md:text-xl font-black text-white tracking-tight">
                {{ $namaLengkap }}
            </h1>
            <p class="text-emerald-400 font-bold text-xs uppercase tracking-wider mt-0.5">
                {{ $keteranganJabatan }}
            </p>

            <div class="mt-2.5">
                <span
                    class="text-zinc-300 text-[11px] font-bold bg-zinc-800/80 inline-block px-3 py-1 rounded-xl border border-zinc-700/60 shadow-2xs">
                    {{ $keteranganTambahan }}
                </span>
            </div>

            <div class="w-full h-px bg-zinc-800/80 my-5"></div>

            <!-- Signature Section -->
            <div class="text-left">
                <div class="flex items-center justify-between mb-2 px-1">
                    <p class="text-[10px] text-zinc-400 uppercase font-black tracking-wider">
                        Tanda Tangan Elektronik (TTE)
                    </p>
                    <span class="text-[9px] font-mono text-emerald-400 font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> TERDAFTAR
                    </span>
                </div>

                <div
                    class="bg-black/40 border border-zinc-800 rounded-2xl p-3.5 flex justify-center items-center h-28 relative overflow-hidden group">
                    <!-- Watermark -->
                    <div
                        class="absolute text-4xl text-white/5 font-black uppercase tracking-widest select-none -rotate-12 group-hover:scale-105 transition-transform">
                        SAH
                    </div>

                    <!-- Render Tanda Tangan -->
                    @if ($ttdPath)
                        <img src="{{ asset('storage/' . $ttdPath) }}"
                            class="max-h-full max-w-full object-contain relative z-10 filter invert brightness-200"
                            alt="Tanda Tangan">
                    @else
                        <span class="text-zinc-500 font-bold text-xs italic relative z-10">
                            Data tanda tangan belum diunggah
                        </span>
                    @endif
                </div>

                <p class="text-[11px] text-zinc-400 font-medium mt-3.5 leading-relaxed text-center px-1">
                    Dokumen ini telah ditandatangani secara digital. Jika Anda diarahkan ke halaman ini setelah men-scan
                    QR Code, maka dokumen tersebut adalah <strong class="text-emerald-400 font-black uppercase">ASLI &
                        SAH</strong> diakui oleh pihak Madrasah.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-black/50 border-t border-zinc-800 px-4 py-3 text-center">
            <p class="text-[10px] font-black text-zinc-500 uppercase tracking-wider">
                MDT Hidayatus Shibyan &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

</body>

</html>
