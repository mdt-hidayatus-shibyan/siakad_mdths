<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Print Anggota Ruangan - {{ $ruangan->nama_ruangan }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 1cm;
                size: A4 portrait;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 11px;
            background: #fff;
            zoom: 90%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 130px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            color: #334155;
        }

        .kop-right p {
            margin: 4px 0 0 0;
            font-size: 12px;
            color: #475569;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .signature-table {
            width: 100%;
            border: none;
            margin-top: 35px;
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

<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 15px; background: #000; color: #fff; border: none; cursor: pointer;">Cetak
            Sekarang</button>
        <button onclick="window.close()"
            style="padding: 8px 15px; background: #ccc; border: none; cursor: pointer;">Tutup</button>
    </div>

    @php
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $waliUstadz = $ruangan->waliRuangan ?? null;
    @endphp

    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>DATA ANGGOTA RUANGAN</h1>
            <p>Tahun Pelajaran: <strong>{{ $ruangan->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ruangan->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Kelas/Ruang: <strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong></p>
            <p>Wali Ruangan: <strong>{{ strtoupper($waliUstadz?->nama_lengkap ?? '-') }}</strong></p>
            <p>Total Murid: {{ $ruangan->murids->count() }} Anak</p>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="w-1 text-center" style="width: 30px;">No</th>
                <th class="w-1 text-center" style="width: 70px;">NISM</th>
                <th>Nama Lengkap</th>
                <th class="w-1 text-center" style="width: 30px;">L/P</th>
                <th>Tempat, Tanggal Lahir</th>
                <th>Nama Orang Tua</th>
                <th>Zonasi / Kampung</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ruangan->murids as $murid)
                <tr style="{{ $murid->status !== 'Aktif' ? 'color: #dc2626;' : '' }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $murid->nism }}</td>
                    <td><strong>{{ $murid->nama_lengkap }}</strong></td>
                    <td class="text-center">{{ $murid->jenis_kelamin }}</td>
                    <td>{{ $murid->tempat_lahir ?? '-' }},
                        {{ $murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $murid->nama_ayah ?: '-' }} <br> {{ $murid->nama_ibu ?: '-' }}</td>
                    <td>{{ $murid->waliMurid->kampung->nama_kampung ?? '-' }}</td>
                    <td>{{ $murid->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada anggota di ruangan ini.</td>
                </tr>
            @endforelse
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
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Wali Ruangan</p>
                <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($waliUstadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $waliUstadz->id]),
                        ) !!}
                    @else
                        <div style="height: 50px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $waliUstadz?->nama_lengkap ?? 'Nama Wali Ruangan Belum Diatur' }}
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
