<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased dark">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Portal Verifikasi & Pengesahan Surat - {{ getSetting('app_name', 'MDTHS') }}</title>

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
    class="font-sans text-zinc-900 bg-[#09090b] text-zinc-100 min-h-screen selection:bg-emerald-500 selection:text-white flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden">

    <!-- Ambient Glow Background -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/15 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-lg space-y-6">

        <!-- Card Form Pencarian -->
        <div
            class="p-6 sm:p-8 bg-[#0c0c0e]/95 backdrop-blur-xl border border-zinc-800/80 rounded-3xl shadow-2xl relative overflow-hidden">

            <!-- Header Card -->
            <div class="text-center space-y-3 pb-6 border-b border-zinc-800/80">
                <div
                    class="w-16 h-16 rounded-2xl bg-zinc-900 border border-zinc-700/80 p-2.5 mx-auto flex items-center justify-center shadow-lg">
                    <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo MDTHS"
                        class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-[10px] font-extrabold tracking-widest text-emerald-400 uppercase block">
                        Portal Verifikasi Dokumen Resmi
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-white tracking-tight">
                        {{ getSetting('app_name', 'MDT HIDAYATUS SHIBYAN') }}
                    </h1>
                    <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                        Masukkan nomor surat resmi atau kode verifikasi QR untuk mengecek keaslian dan status pengesahan
                        dokumen.
                    </p>
                </div>
            </div>

            <!-- Form Cari -->
            <form action="{{ route('surat.verifikasi.cari') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="keyword" class="text-xs font-black text-zinc-300 uppercase tracking-wider block">
                        Nomor Surat / Token Verifikasi
                    </label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <i class="bi bi-search text-sm"></i>
                        </div>
                        <input type="text" id="keyword" name="keyword" required autofocus
                            placeholder="Contoh: 004/SPG/MDT-HS/IX/2026 atau MDTHS-..."
                            class="w-full bg-zinc-900/90 border border-zinc-700 rounded-2xl pl-10 pr-4 py-3 text-xs font-bold text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    @error('keyword')
                        <p class="text-xs text-rose-400 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-900/30 transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-shield-check text-sm"></i>
                    <span>Verifikasi Keabsahan Surat</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-zinc-800/60 text-center">
                <p class="text-[11px] text-zinc-500 leading-relaxed">
                    Setiap dokumen surat resmi yang dikeluarkan oleh MDT Hidayatus Shibyan dilengkapi QR Code dan nomor
                    unik yang tercatat di basis data terpusat.
                </p>
            </div>
        </div>

        <div class="text-center text-[10px] text-zinc-600">
            &copy; {{ date('Y') }} {{ getSetting('app_name', 'MDT Hidayatus Shibyan') }}. Seluruh Hak Cipta
            Dilindungi.
        </div>

    </div>

</body>

</html>
