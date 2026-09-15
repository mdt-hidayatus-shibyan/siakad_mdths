<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap_Uang_Pecahan_Kas_Tabungan_{{ date('Ymd_His') }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 8mm 10mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9px;
            color: #1f2937;
            background: #ffffff;
            margin: 0;
            padding: 10px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #0f766e;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo {
            max-height: 45px;
            width: auto;
            object-fit: contain;
        }

        .header-title h1 {
            font-size: 13px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f766e;
            font-weight: 800;
        }

        .header-title h2 {
            font-size: 11px;
            margin: 2px 0 0 0;
            font-weight: 700;
            color: #111827;
        }

        .header-right {
            text-align: right;
            font-size: 8.5px;
            color: #4b5563;
        }

        .info-bar {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 10px;
            margin-bottom: 10px;
            font-size: 8.5px;
        }

        .info-bar span strong {
            color: #0f766e;
        }

        table.rekap-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5px;
        }

        table.rekap-grid th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 800;
            text-align: center;
            padding: 4px 2px;
            border: 1px solid #94a3b8;
        }

        table.rekap-grid td {
            border: 1px solid #cbd5e1;
            padding: 4px 2px;
            text-align: center;
            font-family: monospace;
            font-weight: bold;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 12px;
        }

        table.report th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            border: 1px solid #94a3b8;
            padding: 4px 3px;
        }

        table.report td {
            border: 1px solid #e2e8f0;
            padding: 3px 3px;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: monospace;
        }

        .font-bold {
            font-weight: 700;
        }

        .footer-sign {
            margin-top: 15px;
            display: table;
            width: 100%;
            page-break-inside: avoid;
            font-size: 8.5px;
        }

        .sign-col {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .sign-space {
            height: 40px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 10px; text-align: right;">
        <button onclick="window.print()"
            style="background: #0f766e; color: white; border: none; padding: 5px 14px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Cetak Rekapitulasi Kas (A4 Landscape)
        </button>
    </div>

    <!-- KOP MADRASAH -->
    <div class="header">
        <div class="header-left">
            @if (getSetting('kop_logo'))
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Kop" class="kop-logo">
            @endif
            <div class="header-title">
                <h1>Madrasah Diniyah Takmiliyah Hidayatus Shibyan</h1>
                <h2>REKAPITULASI KEBUTUHAN UANG PECAHAN KAS FISIK TABUNGAN</h2>
            </div>
        </div>
        <div class="header-right">
            <div>Dicetak: {{ date('d/m/Y H:i') }} WIB</div>
            <div>Periode: <strong>{{ $rekap['periode']?->nama_periode ?? 'Semua Periode' }}</strong></div>
        </div>
    </div>

    <!-- INFO BAR FILTER -->
    <div class="info-bar">
        <span>Kategori: <strong>{{ $filterInfo['nasabah'] }}</strong></span>
        <span>Tingkat/Level: <strong>{{ $filterInfo['level'] }}</strong></span>
        <span>Ruangan: <strong>{{ $filterInfo['ruangan'] }}</strong></span>
        <span>Total Rekening: <strong>{{ number_format($rekap['total_rekening'], 0, ',', '.') }} Amplop</strong></span>
        <span>Total Kas Bersih: <strong style="color: #0f766e; font-size: 10px;">Rp
                {{ number_format($rekap['total_hak_bersih'], 0, ',', '.') }}</strong></span>
    </div>

    <!-- 1. MATRIKS PECAHAN TOTAL KAS -->
    <table class="rekap-grid">
        <thead>
            <tr>
                <th colspan="10" style="background: #0f766e; color: white; font-size: 9px; padding: 3px;">
                    REKAPITULASI TOTAL LEMBAR & KOIN PECAHAN UANG KAS FISIK (UNTUK PENUKARAN BANK / BRANKAS)
                </th>
            </tr>
            <tr>
                <th>100.000</th>
                <th>50.000</th>
                <th>20.000</th>
                <th>10.000</th>
                <th>5.000</th>
                <th>2.000</th>
                <th>1.000</th>
                <th>500</th>
                <th>200</th>
                <th>100</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['100000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['50000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['20000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['10000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['5000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['2000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #0f766e;">
                    {{ number_format($rekap['total_pecahan_global']['1000']['count'], 0, ',', '.') }} lbr</td>
                <td style="color: #b45309;">
                    {{ number_format($rekap['total_pecahan_global']['500']['count'], 0, ',', '.') }} koin</td>
                <td style="color: #b45309;">
                    {{ number_format($rekap['total_pecahan_global']['200']['count'], 0, ',', '.') }} koin</td>
                <td style="color: #b45309;">
                    {{ number_format($rekap['total_pecahan_global']['100']['count'], 0, ',', '.') }} koin</td>
            </tr>
            <tr style="background: #f8fafc; font-size: 7.5px;">
                <td>Rp {{ number_format($rekap['total_pecahan_global']['100000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['50000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['20000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['10000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['5000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['2000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['1000']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['500']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['200']['total_nominal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($rekap['total_pecahan_global']['100']['total_nominal'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. TABEL RINCIAN PER MURID -->
    <table class="report">
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th style="width: 65px;">No. Rek</th>
                <th>Nama Nasabah / Murid</th>
                <th style="width: 75px;">Kelas / Ruang</th>
                <th style="width: 65px; text-align: right;">Saldo Kotor</th>
                <th style="width: 55px; text-align: right;">Potongan</th>
                <th style="width: 65px; text-align: right; background: #e0f2fe;">Hak Bersih</th>

                <!-- 10 Kolom Denominasi -->
                <th style="width: 25px; text-align: center;">100k</th>
                <th style="width: 25px; text-align: center;">50k</th>
                <th style="width: 25px; text-align: center;">20k</th>
                <th style="width: 25px; text-align: center;">10k</th>
                <th style="width: 25px; text-align: center;">5k</th>
                <th style="width: 25px; text-align: center;">2k</th>
                <th style="width: 25px; text-align: center;">1k</th>
                <th style="width: 20px; text-align: center;">500</th>
                <th style="width: 20px; text-align: center;">200</th>
                <th style="width: 20px; text-align: center;">100</th>
                <th style="width: 60px; text-align: center;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap['rekap_per_nasabah'] as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-mono text-center">{{ $item['nomor_rekening'] }}</td>
                    <td class="font-bold">{{ $item['nama_nasabah'] }}</td>
                    <td>{{ $item['nama_ruangan'] }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($item['saldo_kotor'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="color: #d97706;">
                        {{ $item['nominal_potongan'] > 0 ? number_format($item['nominal_potongan'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold" style="background: #f0fdf4; color: #15803d;">
                        Rp {{ number_format($item['hak_bersih'], 0, ',', '.') }}
                    </td>

                    <!-- 10 Denominasi -->
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['100000'] ?: '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['50000'] ?: '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['20000'] ?: '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['10000'] ?: '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['5000'] ?: '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['2000'] ?: '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['pecahan']['1000'] ?: '-' }}</td>
                    <td class="text-center font-mono">{{ $item['pecahan']['500'] ?: '-' }}</td>
                    <td class="text-center font-mono">{{ $item['pecahan']['200'] ?: '-' }}</td>
                    <td class="text-center font-mono">{{ $item['pecahan']['100'] ?: '-' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="18" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada data yang
                        sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right" style="padding: 4px;">TOTAL:</td>
                <td class="text-right font-mono">Rp {{ number_format($rekap['total_saldo_kotor'], 0, ',', '.') }}</td>
                <td class="text-right font-mono" style="color: #d97706;">Rp
                    {{ number_format($rekap['total_potongan'], 0, ',', '.') }}</td>
                <td class="text-right font-mono" style="background: #dcfce7; color: #15803d;">
                    Rp {{ number_format($rekap['total_hak_bersih'], 0, ',', '.') }}
                </td>

                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['100000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['50000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['20000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['10000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['5000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['2000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['1000']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['500']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['200']['count'] ?: '-' }}</td>
                <td class="text-center font-mono">{{ $rekap['total_pecahan_global']['100']['count'] ?: '-' }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer-sign">
        <div class="sign-col">
            <div>Mengetahui,<br>Bendahara / Pimpinan Madrasah,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>( .................................................. )</u></div>
        </div>
        <div class="sign-col">
            <div>Somorkoneng, {{ date('d F Y') }}<br>Petugas Tabungan / Kasir,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>{{ Auth::user()->name ?? 'Petugas Tabungan' }}</u></div>
        </div>
    </div>

</body>

</html>
