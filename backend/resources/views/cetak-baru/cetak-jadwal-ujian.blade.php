<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal Ujian</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* Pengaturan Kertas Landscape dengan Margin Minimum */
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Lexend', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            background-color: #fff;
        }

        .page-break {
            page-break-after: always;
        }

        .page-break:last-child {
            page-break-after: auto;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 2px;
        }

        .kop-kiri {
            width: 60%;
            text-align: left;
        }

        .kop-kiri img {
            max-width: 100%;
            height: auto;
            max-height: 80px;
            object-fit: contain;
            display: block;
        }

        .judul-kanan {
            width: 40%;
            text-align: right;
            font-size: 14pt;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.4;
        }

        .tahun-pelajaran {
            font-size: 11pt;
            font-weight: 500;
            color: #475569;
            display: block;
            margin-top: 2px;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Lexend', sans-serif;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            font-size: 10pt;
            font-weight: 700;
        }

        td {
            font-size: 9pt;
            font-weight: 400;
        }

        .hari-title {
            text-align: left;
            background-color: #e0e0e0;
            font-weight: 700;
            padding-left: 10px;
            font-size: 9.5pt;
        }

        .mapel {
            font-weight: 700;
            font-size: 9.5pt;
            margin-bottom: 3px;
            color: #000;
        }

        .ustadz {
            font-size: 8pt;
            font-style: italic;
            font-weight: 300;
            color: #333;
        }

        .garis-pemisah-sel {
            border-top: 1px dotted #ccc;
            width: 80%;
            margin: 3px auto;
        }

        .kosong {
            color: #888;
            font-size: 8pt;
            font-weight: 300;
        }

        .signature-table {
            width: 100%;
            border: none;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-table td {
            border: none;
            padding: 0;
            text-align: center;
            vertical-align: top;
        }

        @media print {
            .btn-print-wrapper {
                display: none;
            }
        }

        .btn-print-wrapper {
            text-align: center;
            margin: 20px 0;
            background: #f8fafc;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn-print {
            padding: 10px 20px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Lexend', sans-serif;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>

<body>

    <div class="btn-print-wrapper">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen Sekarang</button>
        <p style="font-family: Arial; font-size: 12px; color: #64748b; margin-top: 8px;">(Pastikan pengaturan kertas di
            printer adalah A4 Landscape)</p>
    </div>

    @php
        $tingkats = $levels
            ->map(function ($l) {
                return $l->tingkat;
            })
            ->filter()
            ->unique('id')
            ->values();

        $tanggalList = array_keys($matrix);
        sort($tanggalList);

        $tahunAktif = \App\Models\TahunPelajaran::where('is_active', 1)->first();
        $namaTahun = $tahunAktif ? $tahunAktif->nama_hijriyah . ' H / ' . $tahunAktif->nama_masehi . ' M' : '-';
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $panitiaUjian =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Ujian') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kurikulum');
    @endphp

    @foreach ($tingkats as $tingkat)
        @php
            $levelsTingkat = $levels->filter(function ($l) use ($tingkat) {
                return $l->tingkat_id == $tingkat->id;
            });
        @endphp

        <div class="page-break">

            <!-- KOP SURAT & JUDUL -->
            <div class="header-wrapper">
                <div class="kop-kiri">
                    <img src="{{ asset(getSetting('kop_logo') ?? 'images/default-kop.png') }}" alt="Kop Surat Madrasah">
                </div>
                <div class="judul-kanan">
                    JADWAL PELAKSANAAN UJIAN<br>
                    TINGKAT: <span style="color: #2563eb;">{{ $tingkat->nama_tingkat ?? $tingkat->nama }}</span>
                    <span class="tahun-pelajaran">TAHUN PELAJARAN {{ $namaTahun }}</span>
                </div>
            </div>
            <div class="garis-ganda"></div>

            <!-- TABEL JADWAL -->
            <table>
                <thead>
                    <tr>
                        <th style="width: 120px;">TANGGAL</th>
                        <th style="width: 100px;">WAKTU</th>
                        @foreach ($levelsTingkat as $level)
                            <th>
                                <div style="margin-bottom: 2px;">{{ $level->nama_level }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tanggalList as $tanggal)
                        @php
                            $waktuTersedia = [];
                            foreach ($matrix[$tanggal] as $waktu => $dataLevel) {
                                $adaJadwal = false;
                                foreach ($levelsTingkat as $level) {
                                    if (isset($dataLevel[$level->id])) {
                                        $adaJadwal = true;
                                        break;
                                    }
                                }
                                if ($adaJadwal) {
                                    $waktuTersedia[] = $waktu;
                                }
                            }
                            sort($waktuTersedia);
                        @endphp

                        @if (count($waktuTersedia) > 0)
                            @foreach ($waktuTersedia as $indexWaktu => $waktu)
                                <tr>
                                    @if ($indexWaktu == 0)
                                        <td rowspan="{{ count($waktuTersedia) }}" class="hari-title">
                                            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d M Y') }}
                                        </td>
                                    @endif

                                    <td>{{ $waktu }}</td>

                                    @foreach ($levelsTingkat as $level)
                                        <td>
                                            @if (isset($matrix[$tanggal][$waktu][$level->id]))
                                                @php $jadwal = $matrix[$tanggal][$waktu][$level->id]; @endphp
                                                <div class="mapel">
                                                    {{ $jadwal->mataPelajaran->nama_mapel ?? $jadwal->nama_mata_pelajaran_custom }}
                                                </div>
                                            @else
                                                <div class="kosong">- Kosong -</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>

            <!-- AREA TANDA TANGAN -->
            <table class="signature-table">
                <tr>
                    <td style="width: 50%;">
                        <p style="margin: 0; font-size: 11px;">Mengetahui,</p>
                        <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Pengasuh Madrasah</p>
                        <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                            @if (!empty($pengasuh?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                ) !!}
                            @else
                                <div style="height: 50px;"></div>
                            @endif
                        </div>
                        <p
                            style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                            {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Pengasuh Belum Diatur') }}
                        </p>
                    </td>

                    <td style="width: 50%;">
                        <p style="margin: 0; font-size: 11px;">Somor Koneng,
                            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Panitia Ujian / Kurikulum
                        </p>
                        <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                            @if (!empty($panitiaUjian?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $panitiaUjian->id]),
                                ) !!}
                            @else
                                <div style="height: 50px;"></div>
                            @endif
                        </div>
                        <p
                            style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                            {{ $panitiaUjian?->anggota?->nama_lengkap ?? ($panitiaUjian?->nama ?? 'Nama Panitia Belum Diatur') }}
                        </p>
                    </td>
                </tr>
            </table>

        </div>
    @endforeach

</body>

</html>
