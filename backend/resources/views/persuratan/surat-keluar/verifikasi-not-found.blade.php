<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased dark">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dokumen Tidak Ditemukan - {{ getSetting('app_name', 'MDTHS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>

<body
    class="font-sans text-zinc-900 bg-[#09090b] text-zinc-100 min-h-screen selection:bg-rose-500 selection:text-white flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden">

    <!-- Ambient Glow Background -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-rose-500/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-lg space-y-6">

        <!-- Card Not Found -->
        <div
            class="p-6 sm:p-8 bg-[#0c0c0e]/95 backdrop-blur-xl border-2 border-rose-500/30 rounded-3xl shadow-2xl relative overflow-hidden text-center">

            <div
                class="w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-500 mx-auto flex items-center justify-center text-3xl shadow-lg mb-4">
                <i class="bi bi-shield-x"></i>
            </div>

            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[11px] font-black uppercase tracking-wider mb-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Dokumen Tidak Terdaftar / Tidak Sah
            </span>

            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-2">
                Surat Tidak Ditemukan
            </h1>

            <p class="text-xs text-zinc-400 mt-2 max-w-sm mx-auto leading-relaxed">
                Nomor surat atau kode verifikasi <b class="text-rose-400 font-mono">"{{ $identifier }}"</b> tidak
                ditemukan dalam basis data resmi persuratan MDT Hidayatus Shibyan.
            </p>

            <div
                class="my-6 p-4 rounded-2xl bg-zinc-900/80 border border-zinc-800 text-left text-xs space-y-2 text-zinc-400">
                <p class="font-bold text-zinc-300">Kemungkinan penyebab:</p>
                <ul class="list-disc pl-4 space-y-1 text-[11px]">
                    <li>Terjadi kesalahan penulisan nomor surat atau kode QR.</li>
                    <li>Surat belum diterbitkan secara resmi atau masih berstatus draft.</li>
                    <li>Dokumen tidak diterbitkan oleh institusi MDT Hidayatus Shibyan.</li>
                </ul>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                <a href="{{ route('surat.verifikasi.index') }}"
                    class="w-full py-3 px-5 rounded-2xl bg-zinc-800 hover:bg-zinc-700 active:scale-[0.98] text-white text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-search"></i>
                    <span>Coba Cari Ulang</span>
                </a>
            </div>

            <div class="mt-6 pt-4 border-t border-zinc-800/60 text-[10px] text-zinc-500">
                Pemeriksaan dilakukan pada: {{ $waktuCek }}
            </div>
        </div>

        <div class="text-center text-[10px] text-zinc-600">
            &copy; {{ date('Y') }} {{ getSetting('app_name', 'MDT Hidayatus Shibyan') }}.
        </div>

    </div>

</body>

</html>
