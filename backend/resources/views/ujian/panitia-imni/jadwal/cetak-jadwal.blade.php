<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal_Pelaksanaan_IMNI_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
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

            .sheet-container {
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

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .sheet-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 0.8cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div
        class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-2.5 bg-white/95 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('jadwal-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-3.5 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>

        <!-- Filter Tingkat -->
        <div class="flex items-center gap-1 bg-zinc-100 p-1 rounded-xl border border-zinc-200/80">
            <a href="{{ route('jadwal-imni.cetak', ['tahun_id' => $selectedTahun->id]) }}"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ empty($selectedTingkatId) ? 'bg-white text-zinc-900 shadow-xs' : 'text-zinc-500 hover:text-zinc-800' }}">
                Semua
            </a>
            @foreach ($allTingkats as $tingkatItem)
                <a href="{{ route('jadwal-imni.cetak', ['tahun_id' => $selectedTahun->id, 'tingkat_id' => $tingkatItem->id]) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $selectedTingkatId == $tingkatItem->id ? 'bg-white text-zinc-900 shadow-xs' : 'text-zinc-500 hover:text-zinc-800' }}">
                    {{ $tingkatItem->nama_tingkat }}
                </a>
            @endforeach
        </div>

        <button type="button" onclick="window.print()"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Jadwal (Ctrl+P)</span>
        </button>
    </div>

    <!-- DAFTAR LEMBAR CETAK PER TINGKAT -->
    @forelse ($tingkatSchedules as $index => $scheduleData)
        <div class="sheet-container {{ !$loop->last ? 'page-break' : '' }}">
            <!-- KOP SURAT RESMI -->
            <div class="flex justify-between items-center border-b-[3px] border-double border-slate-800 pb-3 mb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                        alt="Logo Madrasah" class="h-[75px] max-h-[85px] w-auto object-contain">
                </div>
                <div class="text-right leading-snug">
                    <h1 class="m-0 text-sm md:text-base font-black text-indigo-700 underline uppercase tracking-wide">
                        PANITIA IMTIHAN NIHA'I (IMNI)
                    </h1>
                    <h2 class="m-0 text-xs md:text-sm font-extrabold uppercase tracking-wide text-zinc-900">
                        {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                    </h2>
                    <p class="mt-0.5 text-[10px] font-bold text-zinc-600 uppercase">
                        Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                    </p>
                    <p class="text-[9px] font-medium text-zinc-500">
                        {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                    </p>
                </div>
            </div>

            <!-- JUDUL DOKUMEN -->
            <div class="text-center mb-4">
                <h2 class="text-xs md:text-sm font-black uppercase tracking-wider underline">
                    JADWAL PELAKSANAAN IMTIHAN NIHA'I (IMNI)
                </h2>
                <div class="inline-block mt-1 px-3 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-900 font-black text-[11px] uppercase tracking-wide">
                    TINGKAT {{ $scheduleData['tingkat']->nama_tingkat }}
                </div>
                <p class="text-[10px] font-bold text-zinc-500 mt-1">
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                    @if ($scheduleData['levels']->isNotEmpty())
                        <span class="mx-1">•</span>
                        Kelas: {{ $scheduleData['levels']->pluck('nama_level')->implode(', ') }}
                    @endif
                </p>
            </div>

            <!-- TABEL JADWAL TINGKAT -->
            <div class="overflow-x-auto mb-6">
                <table class="w-full border-collapse border border-zinc-900 text-[10px]">
                    <thead>
                        <tr class="bg-zinc-100 text-zinc-900 font-black text-center uppercase tracking-wide">
                            <th class="border border-zinc-900 py-2 px-1 w-8">No</th>
                            <th class="border border-zinc-900 py-2 px-2 w-36">Hari & Tanggal</th>
                            <th class="border border-zinc-900 py-2 px-2 w-32">Waktu / Sesi</th>
                            @foreach ($scheduleData['levels'] as $lvl)
                                <th class="border border-zinc-900 py-2 px-2">
                                    Kelas {{ $lvl->nama_level }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $noUrut = 1; @endphp
                        @forelse ($scheduleData['daftarTanggal'] as $tgl)
                            @php
                                $tglCarbon = \Carbon\Carbon::parse($tgl)->locale('id');
                                $sesiHariIni = array_keys($scheduleData['matrix'][$tgl] ?? []);
                                sort($sesiHariIni);
                                $rowspan = count($sesiHariIni);
                            @endphp

                            @foreach ($sesiHariIni as $idxSesi => $waktu)
                                <tr class="hover:bg-zinc-50/50">
                                    @if ($idxSesi === 0)
                                        <td rowspan="{{ $rowspan }}" class="border border-zinc-900 py-1.5 px-1 text-center font-bold align-middle bg-zinc-50/30">
                                            {{ $noUrut++ }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="border border-zinc-900 py-1.5 px-2.5 text-left align-middle bg-zinc-50/30">
                                            <div class="font-black text-zinc-900 text-[10.5px]">{{ $tglCarbon->translatedFormat('l') }}</div>
                                            <div class="text-[9.5px] text-zinc-600 font-medium">{{ $tglCarbon->translatedFormat('d F Y') }}</div>
                                        </td>
                                    @endif

                                    <td class="border border-zinc-900 py-1.5 px-2 text-center font-mono font-bold text-[9.5px] whitespace-nowrap bg-zinc-50/20">
                                        {{ $waktu }} WIB
                                    </td>

                                    @foreach ($scheduleData['levels'] as $lvl)
                                        @php
                                            $itemJadwal = $scheduleData['matrix'][$tgl][$waktu][$lvl->id] ?? null;
                                        @endphp
                                        <td class="border border-zinc-900 py-1.5 px-2 text-center">
                                            @if ($itemJadwal)
                                                <div class="font-bold text-zinc-900 text-[10px]">
                                                    {{ $itemJadwal->mataPelajaran->nama_mapel ?? $itemJadwal->nama_mata_pelajaran_custom }}
                                                </div>
                                                @if ($itemJadwal->pengawas)
                                                    <div class="text-[8.5px] font-medium text-zinc-500 mt-0.5">
                                                        Pengawas: {{ $itemJadwal->pengawas->nama_lengkap }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-zinc-300 font-mono text-xs">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="{{ 3 + count($scheduleData['levels']) }}" class="border border-zinc-900 py-8 text-center text-zinc-400 font-bold italic">
                                    Belum ada data jadwal pelaksanaan IMNI untuk Tingkat {{ $scheduleData['tingkat']->nama_tingkat }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TANDA TANGAN KETUA PANITIA IMNI (SEBELAH KANAN) -->
            <div class="flex justify-end mt-6 break-inside-avoid">
                <div class="text-center w-64">
                    <p class="text-zinc-500 text-[10.5px] mb-0.5">
                        {{ getSetting('kota_madrasah', 'Bangkalan') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                    <p class="font-bold text-zinc-800 text-xs">Ketua Panitia IMNI</p>
                    <div class="min-h-[65px] flex items-center justify-center my-1.5">
                        @if (!empty($ketuaPanitia?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])
                            ) !!}
                        @elseif (!empty($ketuaPanitia?->ustadz?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])
                            ) !!}
                        @elseif ($ketuaPanitia && $ketuaPanitia->ustadz && $ketuaPanitia->ustadz->signature_url)
                            <img src="{{ asset($ketuaPanitia->ustadz->signature_url) }}" alt="TTD Ketua Panitia" class="h-14 object-contain">
                        @else
                            <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">(Tanda Tangan)</div>
                        @endif
                    </div>
                    <p class="font-black text-zinc-900 underline uppercase text-xs">
                        {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                    </p>
                    @if ($ketuaPanitia?->ustadz?->nip_ustadz)
                        <p class="text-[9px] text-zinc-500 font-mono">NIP: {{ $ketuaPanitia->ustadz->nip_ustadz }}</p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="sheet-container">
            <div class="py-12 text-center text-zinc-400 font-bold">
                Tidak ada data tingkat / level IMNI yang ditemukan.
            </div>
        </div>
    @endforelse

</body>

</html>
