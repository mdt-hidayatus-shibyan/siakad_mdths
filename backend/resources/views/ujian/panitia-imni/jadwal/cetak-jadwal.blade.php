<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal_Pelaksanaan_IMNI_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.2cm 1.5cm;
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
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }

        .sheet-page {
            background: white;
            max-width: 21cm;
            margin: 10mm auto;
            padding: 1.2cm 1.5cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-border {
            border-bottom: 2.5px solid #000;
            position: relative;
            margin-bottom: 1.5mm;
        }

        .kop-border::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            right: 0;
            border-bottom: 1px solid #000;
        }

        table.bordered {
            width: 100%;
            border-collapse: collapse;
        }

        table.bordered th,
        table.bordered td {
            border: 1px solid #000000;
            padding: 6px 8px;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR (NO-PRINT) -->
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/95 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <button type="button" onclick="window.close()"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Tutup</span>
        </button>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Jadwal (Ctrl+P)</span>
        </button>
    </div>

    <!-- SHEET PAGE A4 -->
    <div class="sheet-page">

        <!-- KOP SURAT RESMI -->
        <div class="flex items-center gap-4 pb-2">
            <div class="w-16 h-16 shrink-0 flex items-center justify-center">
                <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo Madrasah"
                    class="max-w-full max-h-full object-contain">
            </div>
            <div class="flex-1 text-center pr-16">
                <h4 class="text-xs font-extrabold tracking-wider uppercase text-zinc-600">
                    {{ getSetting('nama_yayasan', 'YAYASAN PONDOK PESANTREN HIDAYATUS SHIBYAN') }}
                </h4>
                <h1 class="text-base font-black uppercase tracking-tight text-zinc-900 leading-tight">
                    {{ getSetting('nama_madrasah', 'MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN') }}
                </h1>
                <p class="text-[10px] text-zinc-600 mt-0.5 font-medium">
                    {{ getSetting('alamat_madrasah', 'Wanasaba Lor, Kec. Talun, Kabupaten Cirebon, Jawa Barat 45171') }}
                </p>
                <p class="text-[9.5px] text-zinc-500 font-mono">
                    Website/Email: {{ getSetting('email_madrasah', 'mdthidayatusshibyan@gmail.com') }}
                </p>
            </div>
        </div>
        <div class="kop-border"></div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center my-3">
            <h2 class="text-sm font-black uppercase tracking-tight text-zinc-900 underline decoration-1 underline-offset-4">
                JADWAL PELAKSANAAN IMTIHAN NIHA'I (IMNI)
            </h2>
            <p class="text-[11px] font-bold text-zinc-700 mt-0.5">
                TAHUN PELAJARAN {{ $selectedTahun->nama_hijriyah }} H / {{ $selectedTahun->nama_masehi }} M
            </p>
            <p class="text-[10px] text-zinc-500 font-medium">
                Khusus Peserta Ujian Tingkat Akhir (Kelas 3 TPQ, 6 Ibtidaiyah, dan 3 Tsanawiyah)
            </p>
        </div>

        <!-- TABEL JADWAL -->
        <table class="bordered my-3 text-center">
            <thead>
                <tr class="bg-zinc-100 font-black text-[10px] uppercase">
                    <th class="w-10">No</th>
                    <th class="w-36">Hari & Tanggal</th>
                    <th class="w-28">Waktu / Sesi</th>
                    @foreach ($levels as $lvl)
                        <th>
                            Kelas {{ $lvl->nama_level }}<br>
                            <span class="text-[9px] font-normal text-zinc-600">({{ $lvl->tingkat->nama_tingkat ?? '' }})</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $noUrut = 1; @endphp
                @forelse ($daftarTanggal as $tgl)
                    @php
                        $tglCarbon = \Carbon\Carbon::parse($tgl)->locale('id');
                        $sesiHariIni = array_keys($matrix[$tgl] ?? []);
                        sort($sesiHariIni);
                        $rowspan = count($sesiHariIni);
                    @endphp

                    @foreach ($sesiHariIni as $idxSesi => $waktu)
                        <tr>
                            @if ($idxSesi === 0)
                                <td rowspan="{{ $rowspan }}" class="font-bold">{{ $noUrut++ }}</td>
                                <td rowspan="{{ $rowspan }}" class="text-left font-bold pl-2 bg-zinc-50/50">
                                    <div class="font-black text-zinc-900">{{ $tglCarbon->translatedFormat('l') }}</div>
                                    <div class="text-[10px] text-zinc-600">{{ $tglCarbon->translatedFormat('d F Y') }}</div>
                                </td>
                            @endif

                            <td class="font-mono font-bold text-[10px] bg-zinc-50/30 whitespace-nowrap">
                                {{ $waktu }} WIB
                            </td>

                            @foreach ($levels as $lvl)
                                @php
                                    $itemJadwal = $matrix[$tgl][$waktu][$lvl->id] ?? null;
                                @endphp
                                <td class="text-center font-bold text-[10.5px]">
                                    @if ($itemJadwal)
                                        <div class="font-black text-zinc-900">
                                            {{ $itemJadwal->mataPelajaran->nama_mapel ?? $itemJadwal->nama_mata_pelajaran_custom }}
                                        </div>
                                        @if ($itemJadwal->pengawas)
                                            <div class="text-[9px] font-normal text-zinc-500 mt-0.5">
                                                Pengawas: {{ $itemJadwal->pengawas->nama_lengkap }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-zinc-300 font-mono">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($levels) }}" class="py-8 text-zinc-400 italic">
                            Jadwal pelaksanaan IMNI belum diatur.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN & PENGESAHAN -->
        <div class="mt-8 flex justify-between items-start text-xs">
            <div class="text-center w-56">
                <p class="text-[10.5px]">Mengetahui,</p>
                <p class="font-black text-zinc-900">Kepala Madrasah / Pengasuh</p>
                <div class="h-20 flex items-center justify-center">
                    @if ($pengasuh && $pengasuh->signature_url)
                        <img src="{{ asset($pengasuh->signature_url) }}" alt="TTD Pengasuh" class="h-16 object-contain">
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline underline-offset-2">
                    {{ $pengasuh->nama_lengkap ?? getSetting('nama_kepala_madrasah', 'K.H. Masrur Mubarok') }}
                </p>
                <p class="text-[9.5px] text-zinc-500 font-mono">NIY: {{ $pengasuh->niy ?? '-' }}</p>
            </div>

            <div class="text-center w-56">
                <p class="text-[10.5px]">
                    {{ getSetting('kota_madrasah', 'Cirebon') }}, {{ now()->locale('id')->translatedFormat('d F Y') }}
                </p>
                <p class="font-black text-zinc-900">Ketua Panitia IMNI</p>
                <div class="h-20 flex items-center justify-center">
                    @if ($ketuaPanitia && $ketuaPanitia->ustadz && $ketuaPanitia->ustadz->signature_url)
                        <img src="{{ asset($ketuaPanitia->ustadz->signature_url) }}" alt="TTD Ketua Panitia" class="h-16 object-contain">
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline underline-offset-2">
                    {{ $ketuaPanitia->ustadz->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                </p>
                <p class="text-[9.5px] text-zinc-500 font-mono">
                    {{ $ketuaPanitia->no_sk ? 'SK No: ' . $ketuaPanitia->no_sk : 'Panitia Pelaksana IMNI' }}
                </p>
            </div>
        </div>

    </div>

</body>

</html>
