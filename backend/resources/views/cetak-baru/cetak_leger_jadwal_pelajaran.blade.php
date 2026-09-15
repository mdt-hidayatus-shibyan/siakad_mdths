<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal Pelajaran</title>

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
            /* Menerapkan font Lexend ke seluruh dokumen */
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
            max-height: 100px;
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
            font-size: 10pt;
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
        $hariList = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];

        // Ambil Tingkat Unik
        $tingkats = $ruangans
            ->map(function ($r) {
                return $r->level ? $r->level->tingkat : null;
            })
            ->filter()
            ->unique('id')
            ->values();

        $tahunAktif = \App\Models\TahunPelajaran::where('is_active', 1)->first();
        $namaTahun = $tahunAktif ? $tahunAktif->nama_hijriyah . ' H / ' . $tahunAktif->nama_masehi . ' M' : '-';
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $wakaKurikulum =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kurikulum') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris');
    @endphp

    @foreach ($tingkats as $tingkat)
        @php
            $ruangansTingkat = $ruangans->filter(function ($r) use ($tingkat) {
                return $r->level && $r->level->tingkat_id == $tingkat->id;
            });
        @endphp

        <div class="page-break">

            <div class="header-wrapper">
                <div class="kop-kiri">
                    <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
                </div>
                <div class="judul-kanan">
                    JADWAL PELAJARAN<br>
                    TINGKAT: <span style="color: #2563eb;">{{ $tingkat->nama_tingkat ?? $tingkat->nama }}</span>
                    <span class="tahun-pelajaran">TAHUN PELAJARAN {{ $namaTahun }}</span>
                </div>
            </div>
            <div class="garis-ganda"></div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">HARI</th>
                        <th style="width: 60px;">JAM</th>
                        @foreach ($ruangansTingkat as $ruangan)
                            <th>
                                <div style="margin-bottom: 2px;">{{ $ruangan->nama_ruangan }}</div>
                                <div
                                    style="font-size: 7pt; font-weight: normal; font-style: italic; color: #555; margin-top: 3px; border-top: 1px dotted #ccc; padding-top: 2px;">
                                    {{ $ruangan->waliRuangan->nama_lengkap ?? '-' }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hariList as $hari)
                        @php
                            $adaNadzoman = false;
                            $adaEkstra = false;
                            foreach ($ruangansTingkat as $ruangan) {
                                if (isset($matrix[$hari]['Nadzoman'][$ruangan->id])) {
                                    $adaNadzoman = true;
                                    break;
                                }
                                if (isset($matrix[$hari]['Ekstra'][$ruangan->id])) {
                                    $adaEkstra = true;
                                    break;
                                }
                            }

                            $jamHarian = ['1', '2'];
                            if ($adaNadzoman) {
                                $jamHarian[] = 'Nadzoman';
                            }
                            if ($adaEkstra) {
                                $jamHarian[] = 'Ekstra';
                            }
                        @endphp

                        @foreach ($jamHarian as $indexJam => $jam)
                            <tr>
                                @if ($indexJam == 0)
                                    <td rowspan="{{ count($jamHarian) }}" class="hari-title">
                                        {{ strtoupper($hari) }}
                                    </td>
                                @endif

                                <td>{{ $jam }}</td>

                                @foreach ($ruangansTingkat as $ruangan)
                                    <td>
                                        @if (isset($matrix[$hari][$jam][$ruangan->id]))
                                            @php $jadwal = $matrix[$hari][$jam][$ruangan->id]; @endphp
                                            <div class="mapel">{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</div>
                                            <div class="garis-pemisah-sel"></div>
                                            <div class="ustadz">{{ $jadwal->ustadz->nama_lengkap ?? '-' }}</div>
                                        @else
                                            <div class="kosong">- Kosong -</div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
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
                        <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Kepala Administrasi</p>
                        <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                            @if (!empty($wakaKurikulum?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $wakaKurikulum->id]),
                                ) !!}
                            @else
                                <div style="height: 50px;"></div>
                            @endif
                        </div>
                        <p
                            style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                            {{ $wakaKurikulum?->anggota?->nama_lengkap }}
                        </p>
                    </td>
                </tr>
            </table>

        </div>
    @endforeach

</body>

</html>
