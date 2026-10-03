<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leger_Yudisium_Kelulusan_IMNI_{{ $selectedTahun->nama_masehi }}</title>

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
            line-height: 1.35;
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
        <a href="{{ route('putusan-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Leger Yudisium (Ctrl+P)</span>
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
                LEGER HASIL SIDANG YUDISIUM & PUTUSAN KELULUSAN IMNI
            </h3>
            <div class="flex items-center justify-center gap-4 mt-1.5 text-[10.5px] font-semibold text-zinc-600">
                <span>Tahun Pelajaran: <strong>{{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M</strong></span>
                <span>&bull;</span>
                <span>Tanggal Sidang: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong></span>
            </div>
        </div>

        <!-- TABEL LEGER YUDISIUM -->
        <table class="w-full border-collapse border border-zinc-800 text-[9.5px] mb-4">
            <thead>
                <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                    <th class="border border-zinc-800 py-2 px-1 w-8">No</th>
                    <th class="border border-zinc-800 py-2 px-2 w-28">No. Peserta</th>
                    <th class="border border-zinc-800 py-2 px-2 text-left">Nama Murid</th>
                    <th class="border border-zinc-800 py-2 px-1 w-20">NISM</th>
                    <th class="border border-zinc-800 py-2 px-1 w-20">Tingkat</th>
                    <th class="border border-zinc-800 py-2 px-1 w-16">Skor Sem 1</th>
                    <th class="border border-zinc-800 py-2 px-1 w-16">Skor Sem 2</th>
                    <th class="border border-zinc-800 py-2 px-1 w-16">Nilai Akhir</th>
                    <th class="border border-zinc-800 py-2 px-2 w-24">Putusan</th>
                    <th class="border border-zinc-800 py-2 px-2 w-32">Nomor Ijazah</th>
                    <th class="border border-zinc-800 py-2 px-2 w-32">Nomor SK</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cLulus = 0;
                    $cTidakLulus = 0;
                    $cDitunda = 0;
                @endphp

                @forelse ($kelulusans as $index => $k)
                    @php
                        $isLulus = in_array($k->status_kelulusan, ['Lulus', 'Lulus Murni', 'Lulus Bersyarat']);
                        if ($isLulus) $cLulus++;
                        elseif ($k->status_kelulusan === 'Tidak Lulus') $cTidakLulus++;
                        else $cDitunda++;

                        $skor1 = $k->skor_sem1 ?? null;
                        $skor2 = $k->skor_sem2 ?? null;
                        $nilaiAkhir = $k->nilai_akhir ?? $k->rata_nilai_teori ?? 0;
                    @endphp
                    <tr class="hover:bg-zinc-50">
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-mono font-bold text-center text-violet-700">{{ $k->peserta?->nomor_peserta ?? '-' }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-bold uppercase">{{ $k->murid?->nama_lengkap }} ({{ $k->murid?->jenis_kelamin }})</td>
                        <td class="border border-zinc-800 py-1.5 px-1 font-mono text-center">{{ $k->murid?->nism ?? '-' }}</td>
                        <td class="border border-zinc-800 py-1.5 px-1 text-center">{{ $k->tingkat?->nama_tingkat }}</td>
                        <td class="border border-zinc-800 py-1.5 px-1 font-mono text-center font-semibold">
                            {{ $skor1 !== null ? number_format($skor1, 2) : '-' }}
                        </td>
                        <td class="border border-zinc-800 py-1.5 px-1 font-mono text-center font-semibold">
                            {{ $skor2 !== null ? number_format($skor2, 2) : '-' }}
                        </td>
                        <td class="border border-zinc-800 py-1.5 px-1 font-mono font-bold text-center {{ $nilaiAkhir > 55 ? 'text-zinc-900 font-black' : 'text-rose-600' }}">
                            {{ $nilaiAkhir > 0 ? number_format($nilaiAkhir, 2) : '-' }}
                        </td>
                        <td class="border border-zinc-800 py-1.5 px-2 text-center font-black text-[9.5px] {{ $isLulus ? 'text-emerald-800' : 'text-rose-700' }}">
                            {{ $isLulus ? 'LULUS' : $k->status_kelulusan }}
                        </td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-mono font-bold text-center text-teal-800 text-[9px]">{{ $k->nomor_ijazah ?? '-' }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-mono text-center text-zinc-600 text-[8.5px]">{{ $k->nomor_sk_lulus ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="border border-zinc-800 py-6 text-center text-zinc-400 italic">
                            Belum ada data putusan kelulusan IMNI.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- REKAPITULASI STATISTIK YUDISIUM -->
        <div class="grid grid-cols-4 gap-2 text-center text-[9.5px] p-2 bg-zinc-100 border border-zinc-800 rounded-lg mb-6">
            <div>
                <span class="text-zinc-500 font-semibold block">Total Peserta:</span>
                <span class="font-black text-zinc-900">{{ $kelulusans->count() }} Murid</span>
            </div>
            <div>
                <span class="text-emerald-700 font-semibold block">Lulus:</span>
                <span class="font-black text-emerald-800">{{ $cLulus }} Murid</span>
            </div>
            <div>
                <span class="text-rose-700 font-semibold block">Tidak Lulus:</span>
                <span class="font-black text-rose-800">{{ $cTidakLulus }} Murid</span>
            </div>
            <div>
                <span class="text-indigo-700 font-semibold block">Tingkat Kelulusan:</span>
                <span class="font-black text-indigo-800">
                    {{ $kelulusans->count() > 0 ? round(($cLulus / $kelulusans->count()) * 100, 1) : 0 }}%
                </span>
            </div>
        </div>

        <!-- TANDA TANGAN PENGESAHAN -->
        @php
            $kepalaMadrasah = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala Madrasah') ?? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala');
            $namaKepala = $kepalaMadrasah?->anggota?->nama_lengkap ?? getSetting('nama_kepala_madrasah', 'K.H. Ahmad Dahlan, S.Pd.I.');
        @endphp
        <div class="flex justify-between items-start text-center text-[10px] break-inside-avoid">
            <div class="w-64">
                <p class="font-bold text-zinc-500 mb-1">Mengetahui,</p>
                <p class="font-bold uppercase text-zinc-800">Kepala Madrasah</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($kepalaMadrasah?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kepalaMadrasah->id])
                        ) !!}
                    @elseif (!empty($kepalaMadrasah?->anggota_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $kepalaMadrasah->anggota_id])
                        ) !!}
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">[Tanda Tangan & Stempel]</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline">{{ $namaKepala }}</p>
                <p class="text-[9px] text-zinc-500">NIP: {{ getSetting('nip_kepala_madrasah', '-') }}</p>
            </div>

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
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">[Tanda Tangan]</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline">{{ $ketuaPanitia?->ustadz?->nama_lengkap ?? '...................................' }}</p>
                <p class="text-[9px] text-zinc-500">SK Panitia: {{ $ketuaPanitia?->nomor_sk ?? '-' }}</p>
            </div>
        </div>
    </div>

</body>

</html>
