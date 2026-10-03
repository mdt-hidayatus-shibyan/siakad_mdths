<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leger_Nilai_IMNI_{{ $ruangan->nama_ruangan }}_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 landscape;
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

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.3;
        }

        .sheet-container {
            background: white;
            max-width: 29.7cm;
            margin: 5mm auto 15mm auto;
            padding: 0.8cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('nilai-imni.index', ['tahun_id' => $selectedTahun->id, 'ruangan_ujian_id' => $ruangan->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Leger (Ctrl+P)</span>
        </button>
    </div>

    <div class="sheet-container">
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
            <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 underline decoration-2 underline-offset-4">
                LEGER NILAI MATA PELAJARAN IMTIHAN NIHAI (IMNI)
            </h3>
            <div class="flex items-center justify-center gap-4 mt-1.5 text-[10.5px] font-semibold text-zinc-600">
                <span>Ruangan: <strong>{{ $ruangan->nama_ruangan }}</strong></span>
                <span>&bull;</span>
                <span>Jenjang/Tingkat: <strong>{{ $ruangan->level?->nama_level ?? 'Kelas Akhir' }}</strong></span>
                <span>&bull;</span>
                <span>Tahun Pelajaran: <strong>{{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M</strong></span>
            </div>
        </div>

        <!-- TABEL LEGER NILAI -->
        <table class="w-full border-collapse border border-zinc-800 text-[9.5px] mb-4">
            <thead>
                <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                    <th class="border border-zinc-800 py-2 px-1 w-7" rowspan="2">No</th>
                    <th class="border border-zinc-800 py-2 px-1 w-9" rowspan="2">Meja</th>
                    <th class="border border-zinc-800 py-2 px-2 w-20" rowspan="2">No. Peserta</th>
                    <th class="border border-zinc-800 py-2 px-2 text-left" rowspan="2">Nama Murid</th>
                    <th class="border border-zinc-800 py-1 px-2" colspan="{{ $jadwals->count() }}">Mata Pelajaran Ujian Teori / Tulis</th>
                    <th class="border border-zinc-800 py-2 px-1 w-12" rowspan="2">Jumlah Nilai</th>
                    <th class="border border-zinc-800 py-2 px-1 w-12" rowspan="2">Rata-Rata</th>
                    <th class="border border-zinc-800 py-2 px-1 w-16" rowspan="2">Keterangan</th>
                </tr>
                <tr class="bg-zinc-50 text-zinc-800 font-bold text-center">
                    @forelse ($jadwals as $j)
                        <th class="border border-zinc-800 py-1 px-1 min-w-[55px] text-[8.5px]">
                            {{ $j->nama_mapel }}
                        </th>
                    @empty
                        <th class="border border-zinc-800 py-1 px-2 text-zinc-400 italic">Belum Ada Mapel</th>
                    @endforelse
                </tr>
            </thead>
            <tbody>
                @php
                    $kkm = 65;
                    $mapelTotals = [];
                    foreach ($jadwals as $j) {
                        $mapelTotals[$j->id] = ['sum' => 0, 'count' => 0, 'max' => 0, 'min' => 100];
                    }
                @endphp

                @forelse ($pesertas as $index => $p)
                    @php
                        $totalNilaiMurid = 0;
                        $countMapelMurid = 0;
                    @endphp
                    <tr class="hover:bg-zinc-50">
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-mono font-bold">{{ $p->nomor_meja ?? '-' }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-mono font-bold text-center">{{ $p->nomor_peserta }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-bold uppercase">{{ $p->murid?->nama_lengkap }}</td>

                        @foreach ($jadwals as $j)
                            @php
                                $score = $matriksNilai[$p->murid_id][$j->id] ?? null;
                                if ($score !== null && is_numeric($score)) {
                                    $totalNilaiMurid += (float) $score;
                                    $countMapelMurid++;

                                    $mapelTotals[$j->id]['sum'] += (float) $score;
                                    $mapelTotals[$j->id]['count']++;
                                    $mapelTotals[$j->id]['max'] = max($mapelTotals[$j->id]['max'], (float) $score);
                                    $mapelTotals[$j->id]['min'] = min($mapelTotals[$j->id]['min'], (float) $score);
                                }
                            @endphp
                            <td class="border border-zinc-800 py-1.5 px-1 text-center font-mono {{ ($score !== null && $score < $kkm) ? 'text-rose-600 font-bold' : '' }}">
                                {{ $score !== null ? number_format($score, 1) : '-' }}
                            </td>
                        @endforeach

                        @php
                            $avgMurid = ($countMapelMurid > 0) ? round($totalNilaiMurid / $countMapelMurid, 1) : 0;
                            $isTuntas = ($avgMurid >= $kkm && $countMapelMurid == $jadwals->count());
                        @endphp
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-mono font-bold">{{ number_format($totalNilaiMurid, 1) }}</td>
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-mono font-black {{ ($avgMurid < $kkm && $countMapelMurid > 0) ? 'text-rose-600' : 'text-zinc-900' }}">
                            {{ $avgMurid > 0 ? number_format($avgMurid, 1) : '-' }}
                        </td>
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-bold text-[8.5px]">
                            @if ($countMapelMurid == 0)
                                <span class="text-zinc-400">Belum Ada</span>
                            @elseif ($isTuntas)
                                <span class="text-emerald-700">Tuntas</span>
                            @else
                                <span class="text-rose-700">Remidi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 7 + $jadwals->count() }}" class="border border-zinc-800 py-6 text-center text-zinc-400 italic">
                            Tidak ada peserta pada ruangan ini.
                        </td>
                    </tr>
                @endforelse

                <!-- BARIS STATISTIK REKAP MAPEL -->
                @if ($jadwals->isNotEmpty())
                    <tr class="bg-zinc-100 font-bold text-center border-t-2 border-zinc-800">
                        <td colspan="4" class="border border-zinc-800 py-1.5 px-2 text-right uppercase">Rata-Rata Nilai Mapel :</td>
                        @foreach ($jadwals as $j)
                            @php
                                $cnt = $mapelTotals[$j->id]['count'];
                                $avg = $cnt > 0 ? round($mapelTotals[$j->id]['sum'] / $cnt, 1) : 0;
                            @endphp
                            <td class="border border-zinc-800 py-1.5 px-1 font-mono font-black text-indigo-700">{{ $avg > 0 ? $avg : '-' }}</td>
                        @endforeach
                        <td colspan="3" class="border border-zinc-800 bg-zinc-200"></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- TANDA TANGAN PENGESAHAN (KETUA PANITIA IMNI) -->
        <div class="mt-6 flex justify-end text-center text-[10px] break-inside-avoid">
            <div class="w-64">
                <p class="font-bold text-zinc-500 mb-1">
                    {{ getSetting('kota_madrasah', 'Bangkalan') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </p>
                <p class="font-bold uppercase text-zinc-800">Ketua Panitia IMNI</p>
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
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">[Tanda Tangan]</div>
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

</body>

</html>
