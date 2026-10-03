<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu_Peserta_IMNI_{{ $selectedTahun->nama_masehi }}</title>

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

            .card-peserta {
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

        .card-peserta {
            border: 1.5px solid #0f172a;
            border-radius: 12px;
            padding: 10px;
            background: #ffffff;
            position: relative;
            height: 100%;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR (NO-PRINT) -->
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
            <span>Cetak Kartu (Ctrl+P)</span>
        </button>
    </div>

    @php
        $chunks = $pesertas->chunk(4); // 4 kartu per halaman A4 (2x2)
    @endphp

    @forelse ($chunks as $pageIndex => $pagePesertas)
        <div class="sheet-page {{ !$loop->last ? 'page-break' : '' }}">
            <div class="grid grid-cols-2 gap-4">
                @foreach ($pagePesertas as $p)
                    <div class="card-peserta flex flex-col justify-between">
                        <div>
                            <!-- Header Kartu -->
                            <div class="flex items-center gap-2 border-b-2 border-zinc-900 pb-2 mb-2">
                                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}" alt="Logo" class="w-10 h-10 object-contain shrink-0">
                                <div class="text-center flex-1">
                                    <h4 class="text-[9px] font-black uppercase tracking-wider text-indigo-700 leading-tight">
                                        PANITIA IMTIHAN NIHA'I (IMNI)
                                    </h4>
                                    <h3 class="text-[11px] font-black uppercase tracking-tight text-zinc-900 leading-tight">
                                        {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                                    </h3>
                                    <p class="text-[8px] font-bold text-zinc-500 leading-none">
                                        Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                                    </p>
                                </div>
                            </div>

                            <!-- Judul Kartu -->
                            <div class="text-center mb-2.5">
                                <span class="inline-block px-3 py-0.5 rounded-full bg-zinc-900 text-white text-[9px] font-black uppercase tracking-wider">
                                    KARTU PESERTA UJIAN IMNI
                                </span>
                            </div>

                            <!-- Body Kartu: Foto & Biodata Murid -->
                            <div class="flex gap-2.5 items-start">
                                <!-- Foto Murid 3x4 -->
                                <div class="w-16 h-20 rounded-lg border-2 border-dashed border-zinc-400 bg-zinc-50 flex items-center justify-center shrink-0 overflow-hidden text-center">
                                    @if ($p->murid && $p->murid->foto_url)
                                        <img src="{{ $p->murid->foto_url }}" alt="Foto Murid" class="w-full h-full object-cover">
                                    @else
                                        <div class="p-1">
                                            <span class="text-[8px] font-bold text-zinc-400 block uppercase leading-tight">Foto 3x4</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Biodata Murid -->
                                <table class="text-[9.5px] leading-tight flex-1">
                                    <tr>
                                        <td class="font-bold text-zinc-500 w-16 py-0.5">No. Peserta</td>
                                        <td class="font-bold text-zinc-500 w-2 py-0.5">:</td>
                                        <td class="font-black font-mono text-zinc-900 py-0.5">{{ $p->nomor_peserta ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold text-zinc-500 py-0.5">Nama Murid</td>
                                        <td class="font-bold text-zinc-500 py-0.5">:</td>
                                        <td class="font-black text-zinc-900 py-0.5 uppercase truncate max-w-[120px]" title="{{ $p->murid?->nama_lengkap }}">{{ $p->murid?->nama_lengkap ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold text-zinc-500 py-0.5">NISM / NISN</td>
                                        <td class="font-bold text-zinc-500 py-0.5">:</td>
                                        <td class="font-semibold text-zinc-700 py-0.5">{{ $p->murid?->nism ?? '-' }} / {{ $p->murid?->nisn ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold text-zinc-500 py-0.5">Tingkat/Kelas</td>
                                        <td class="font-bold text-zinc-500 py-0.5">:</td>
                                        <td class="font-bold text-zinc-800 py-0.5">{{ $p->level?->nama_level ?? ($p->tingkat?->nama_tingkat ?? '-') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold text-zinc-500 py-0.5">Ruang Ujian</td>
                                        <td class="font-bold text-zinc-500 py-0.5">:</td>
                                        <td class="font-black text-emerald-700 py-0.5">{{ $p->ruanganUjian?->nama_ruangan ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Footer Kartu: Tanda Tangan & QR Code -->
                        <div class="mt-2.5 pt-2 border-t border-zinc-300 flex items-center justify-between text-[8.5px]">
                            <div class="text-[8px] text-zinc-400 italic">
                                *Harap dibawa saat ujian berlangsung
                            </div>
                            <div class="text-center flex items-center gap-2">
                                <div class="w-10 h-10 flex items-center justify-center">
                                    @if (!empty($ketuaPanitia?->ustadz_id))
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(38)->margin(0)->generate(
                                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])
                                        ) !!}
                                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(38)->margin(0)->generate(
                                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])
                                        ) !!}
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] font-bold text-zinc-500">Ketua Panitia IMNI,</p>
                                    <p class="text-[8.5px] font-black text-zinc-900 underline uppercase mt-0.5">
                                        {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                                    </p>
                                </div>
                            </div>
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
