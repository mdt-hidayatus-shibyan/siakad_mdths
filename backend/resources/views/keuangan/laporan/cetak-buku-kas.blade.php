<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Buku Kas Umum</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #18181b;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #18181b;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .logo {
            width: 50px;
            height: 50px;
            margin-right: 12px;
        }

        .header-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .header-title p {
            margin: 1px 0 0;
            font-size: 9.5px;
            color: #52525b;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 15px;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .doc-title p {
            margin: 2px 0 0;
            font-size: 11px;
            color: #52525b;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #27272a;
            padding: 5px 6px;
            font-size: 10.5px;
        }

        .report-table th {
            background: #e4e4e7;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .report-table td.text-right {
            text-align: right;
            font-family: monospace;
        }

        .report-table td.text-center {
            text-align: center;
        }

        .summary-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .summary-table {
            border-collapse: collapse;
            font-size: 11px;
            width: 320px;
        }

        .summary-table td {
            padding: 4px 8px;
        }

        .summary-table td.label {
            font-weight: bold;
        }

        .summary-table td.val {
            text-align: right;
            font-family: monospace;
            font-weight: bold;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-space {
            height: 50px;
        }

        .signature-name {
            font-weight: bold;
            border-top: 1px solid #71717a;
            padding-top: 2px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 7px 15px; background: #059669; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Buku Kas
        </button>
    </div>

    <div class="header">
        <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo" class="logo">
        <div class="header-title">
            <h2>MDT HIDAYATUS SHIBYAN</h2>
            <p>Somorkoneng, Blega, Bangkalan, Jawa Timur | Telp/WA: 08123456789</p>
        </div>
    </div>

    <div class="doc-title">
        <h3>LAPORAN BUKU KAS UMUM & TRANSAKSI KEUANGAN</h3>
        <p>Periode: {{ Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d
            {{ Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
            {{ $selectedAkun ? '| Pos Akun: ' . $selectedAkun->nama_akun : '' }}</p>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Tanggal</th>
                <th style="width: 110px;">Kode Transaksi</th>
                <th>Pos Akun & Kategori</th>
                <th>Uraian / Keterangan</th>
                <th style="width: 70px;">Metode</th>
                <th style="width: 100px;">Penerimaan (Debet)</th>
                <th style="width: 100px;">Pengeluaran (Kredit)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningPemasukan = 0;
                $runningPengeluaran = 0;
            @endphp
            @forelse($transaksis as $idx => $trx)
                @php
                    $isMasuk = in_array($trx->jenis_transaksi, ['pemasukan', 'simpanan']);
                    $isKeluar = $trx->jenis_transaksi === 'pengeluaran';
                    if ($isMasuk) {
                        $runningPemasukan += $trx->nominal;
                    }
                    if ($isKeluar) {
                        $runningPengeluaran += $trx->nominal;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $trx->kode_transaksi }}</td>
                    <td>
                        <strong>{{ $trx->akunKeuangan->nama_akun }}</strong><br>
                        <span
                            style="font-size: 9.5px; color: #555;">{{ $trx->kategoriKeuangan->nama_kategori ?? 'Umum' }}</span>
                    </td>
                    <td>{{ $trx->keterangan ?? '-' }}</td>
                    <td class="text-center">{{ strtoupper($trx->metode_pembayaran) }}</td>
                    <td class="text-right" style="color: #047857;">
                        {{ $isMasuk ? 'Rp ' . number_format($trx->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right" style="color: #b91c1c;">
                        {{ $isKeluar ? 'Rp ' . number_format($trx->nominal, 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; font-style: italic;">
                        Tidak ada transaksi pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <table class="summary-table">
            <tr style="border-top: 1px solid #27272a;">
                <td class="label">Total Penerimaan (Masuk)</td>
                <td class="val" style="color: #047857;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Pengeluaran (Keluar)</td>
                <td class="val" style="color: #b91c1c;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-top: 2px solid #27272a; background: #f4f4f5;">
                <td class="label">Surplus / (Defisit) Bersih</td>
                <td class="val" style="font-size: 13px;">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div>Mengetahui,<br>Pengasuh MDT Hidayatus Shibyan</div>
            <div class="signature-space"></div>
            <div class="signature-name">( ........................................ )</div>
        </div>
        <div class="signature-box">
            <div>Bangkalan, {{ date('d F Y') }}<br>Bendahara Madrasah</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ auth()->user()->name ?? 'Pengurus Keuangan' }}</div>
        </div>
    </div>
</body>

</html>
