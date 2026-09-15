<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Buku Pembayaran SPP - {{ $ruangan->nama_ruangan }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        /* Pengaturan Cetak Halaman Landscape */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.8cm;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 11px;
            background: #fff;
            zoom: 85%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Kop Surat */
        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 100px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            color: #334155;
            text-transform: uppercase;
        }

        .kop-right p {
            margin: 3px 0 0 0;
            font-size: 11px;
            color: #475569;
        }

        /* Tabel Presensi / Pembayaran Bulanan */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .data-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 800;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            height: 22px;
        }

        .text-center {
            text-align: center;
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

<body onload="window.print()">
    <!-- Tombol Aksi Toolbar Cetak -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 16px; background: #0f172a; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Cetak Format Pembayaran
        </button>
        <button onclick="window.close()"
            style="padding: 8px 16px; background: #94a3b8; color: #fff; border: none; cursor: pointer; border-radius: 4px; margin-left: 5px;">
            Tutup
        </button>
    </div>

    @php
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $bendahara =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Bendahara') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris');
        $waliUstadz = $ruangan->waliRuangan ?? null;
    @endphp

    <!-- Kop Surat Berlogo -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>LEMBAR KONTROL PEMBAYARAN SYAHRIYAH (SPP)</h1>
            <p>Tahun Pelajaran: <strong>{{ $ruangan->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ruangan->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Kelas / Ruang: <strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong></p>
            <p>Wali Ruangan: <strong>{{ strtoupper($waliUstadz?->nama_lengkap ?? '-') }}</strong></p>
        </div>
    </div>

    <!-- Tabel Buku Pembayaran (12 Bulan Hijriyah) -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" style="width: 55px;">NISM</th>
                <th rowspan="2" style="width: 170px; text-align: left; padding-left: 8px;">Nama Lengkap Murid</th>
                <th rowspan="2" style="width: 25px;">L/P</th>
                <th colspan="12">Bulan Hijriyah</th>
            </tr>
            <tr>
                <th style="width: 45px;">Muh</th>
                <th style="width: 45px;">Shaf</th>
                <th style="width: 45px;">R.Awal</th>
                <th style="width: 45px;">R.Akhir</th>
                <th style="width: 45px;">J.Awal</th>
                <th style="width: 45px;">J.Akhir</th>
                <th style="width: 45px;">Rajab</th>
                <th style="width: 45px;">Sya'ban</th>
                <th style="width: 45px;">Ram</th>
                <th style="width: 45px;">Syaw</th>
                <th style="width: 45px;">Dzul.Q</th>
                <th style="width: 45px;">Dzul.H</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ruangan->murids as $murid)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $murid->nism }}</td>
                    <td><strong>{{ $murid->nama_lengkap }}</strong></td>
                    <td class="text-center">{{ $murid->jenis_kelamin }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center" style="padding: 20px;">Tidak ada data murid di ruangan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Keterangan & Catatan Singkat -->
    <div style="font-size: 9px; color: #64748b; font-style: italic; margin-top: -5px;">
        *Catatan: Berikan paraf / tanggal pembayaran dan nominal pada kolom bulan yang bersangkutan saat murid
        melakukan pelunasan.
    </div>

    <!-- AREA TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p style="margin: 0; font-size: 11px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Pengasuh / Bendahara</p>
                <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($bendahara?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $bendahara->id]),
                        ) !!}
                    @elseif (!empty($pengasuh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                        ) !!}
                    @else
                        <div style="height: 50px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $bendahara?->anggota?->nama_lengkap ?? ($pengasuh?->anggota?->nama_lengkap ?? 'Nama Bendahara Belum Diatur') }}
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
