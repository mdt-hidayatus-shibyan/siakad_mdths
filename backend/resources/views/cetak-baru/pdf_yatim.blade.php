<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Data Murid Yatim - {{ $tahunAktif->nama_hijriyah ?? '' }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        h2,
        h4,
        h5 {
            text-align: center;
            margin: 3px 0;
        }

        .desc-text {
            font-weight: bold;
            color: #dc2626;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 10px;
            font-size: 10.5px;
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
    </style>
</head>

<body>
    @php
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $bendahara =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Bendahara') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris');
    @endphp

    <h2>DATA MURID YATIM</h2>
    <h4>MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN</h4>
    <h5>Somorkoneng Kwanyar Bangkalan</h5>
    <h5>Tahun Pelajaran {{ $tahunAktif->nama_hijriyah ?? '-' }} H | {{ $tahunAktif->nama_masehi ?? '-' }} M</h5>

    <p class="desc-text">
        * Data seluruh Murid aktif dengan status ayah meninggal dan umur di bawah 16 tahun.
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th>Nama Ibu Kandung</th>
                <th style="width: 70px; text-align: center;">NISM</th>
                <th>Nama Lengkap</th>
                <th>Ruangan</th>
                <th style="width: 50px; text-align: center;">Usia</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp

            @foreach ($murids as $waliId => $anakAnak)
                @php $wali = $anakAnak->first()->waliMurid; @endphp

                <tr style="background-color: #f8fafc;">
                    <td colspan="6" style="font-weight: bold; color: #334155; padding: 6px 8px;">
                        Keluarga: {{ $wali->nama_kepala_keluarga ?? ($wali->nama_wali ?? '-') }}
                        (KK: {{ $wali->no_kk ?? 'Belum Diinput' }}) - Dusun:
                        {{ $wali->kampung->nama_kampung ?? ($wali->desa ?? '-') }}
                    </td>
                </tr>

                @foreach ($anakAnak as $murid)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>{{ $murid->nama_ibu ?? '-' }}</td>
                        <td style="text-align: center;">{{ $murid->nism }}</td>
                        <td><b>{{ $murid->nama_lengkap }}</b></td>
                        <td><b>{{ $murid->ruangans->first()->nama_ruangan ?? '-' }}</b></td>
                        <td style="text-align: center;">
                            {{ $murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->age : '-' }} Thn
                        </td>
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
                <div style="min-height: 55px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($pengasuh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                        ) !!}
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 4px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Pengasuh Belum Diatur') }}
                </p>
            </td>

            <td style="width: 50%;">
                <p style="margin: 0; font-size: 11px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Bendahara / Sekretaris</p>
                <div style="min-height: 55px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($bendahara?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $bendahara->id]),
                        ) !!}
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 4px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $bendahara?->anggota?->nama_lengkap ?? ($bendahara?->nama ?? 'Nama Bendahara Belum Diatur') }}
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
