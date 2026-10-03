<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label_Nomor_Meja_IMNI_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm;
            }

            body {
                padding: 0;
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .sheet-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                border: none !important;
            }

            .page-break {
                page-break-after: always;
                break-after: page;
            }

            .label-meja {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.35;
        }

        .sheet-page {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 0.8cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .label-meja {
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 8px 12px;
            background: #ffffff;
            position: relative;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('peserta-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Label Meja (Ctrl+P)</span>
        </button>
    </div>

    @php
        $chunks = $pesertas->chunk(8); // 8 label meja per halaman A4 (2 kolom x 4 baris)
    @endphp

    @forelse ($chunks as $pageIndex => $pagePesertas)
        <div class="sheet-page {{ !$loop->last ? 'page-break' : '' }}">
            <div class="grid grid-cols-2 gap-3.5">
                @foreach ($pagePesertas as $p)
                    <div class="label-meja flex flex-col justify-between">
                        <!-- Kop Label -->
                        <div class="flex items-center justify-between border-b border-zinc-200 pb-1 mb-1.5">
                            <div class="flex items-center gap-1.5">
                                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}" alt="Logo" class="w-5 h-5 object-contain">
                                <span class="text-[8px] font-black uppercase text-indigo-700 tracking-wider">PANITIA IMNI {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}</span>
                            </div>
                            <span class="text-[7.5px] font-bold text-zinc-500 font-mono">{{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M</span>
                        </div>

                        <!-- Tengah: Nomor Meja & Data Murid -->
                        <div class="flex items-center justify-between gap-2 my-1">
                            <div class="min-w-0 flex-1">
                                <span class="text-[8.5px] font-bold font-mono text-zinc-400 block">No. Peserta:</span>
                                <h4 class="text-xs font-black font-mono text-indigo-700 tracking-tight leading-none mb-1">
                                    {{ $p->nomor_peserta ?? '-' }}
                                </h4>
                                <h3 class="text-xs font-black text-zinc-900 leading-tight uppercase truncate" title="{{ $p->murid?->nama_lengkap }}">
                                    {{ $p->murid?->nama_lengkap ?? '-' }}
                                </h3>
                                <p class="text-[8px] font-bold text-zinc-500 truncate">
                                    {{ $p->level?->nama_level }} • {{ $p->ruanganUjian?->nama_ruangan ?? '-' }}
                                </p>
                            </div>

                            <!-- Kotak Nomor Meja Besar -->
                            <div class="w-14 h-14 rounded-xl bg-zinc-900 text-white flex flex-col items-center justify-center shrink-0">
                                <span class="text-[7.5px] font-black uppercase tracking-wider text-zinc-400">MEJA</span>
                                <span class="text-xl font-black font-mono leading-none">
                                    {{ $p->nomor_meja ? str_pad($p->nomor_meja, 2, '0', STR_PAD_LEFT) : '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer Label -->
                        <div class="border-t border-zinc-200 pt-1 mt-1 flex justify-between text-[7px] text-zinc-400 font-semibold italic">
                            <span>*Ditempel di sudut kanan atas meja</span>
                            <span>Panitia IMNI {{ $selectedTahun->nama_hijriyah ?? '-' }} H</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="sheet-page text-center py-12">
            <h3 class="text-sm font-black text-zinc-800">Tidak ada data peserta untuk dicetak.</h3>
            <p class="text-xs text-zinc-500 mt-1">Silakan tarik data murid kelas akhir terlebih dahulu pada halaman Peserta IMNI.</p>
        </div>
    @endforelse

</body>

</html>
