<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Koperasi - {{ $judulPeriode }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .header p {
            margin: 0;
            font-size: 10px;
            color: #444;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #999;
            padding: 5px 7px;
            font-size: 10px;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 10px;
        }

        .summary-card {
            flex: 1;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 4px;
            background: #fafafa;
        }

        .summary-card h4 {
            margin: 0 0 4px 0;
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 13px;
            font-weight: bold;
            color: #000;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-space {
            height: 60px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 6px 14px; background: #059669; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ CETAK / SIMPAN PDF
        </button>
    </div>

    <!-- KOP LAPORAN -->
    <div class="header">
        <h1>KOPERASI MADRASAH DINIYAH TAKMILIYAH</h1>
        <h2>MDT HIDAYATUS SHIBYAN SOMORKONENG</h2>
        <p>Jl. Raya Somorkoneng, Kec. Kwanyar, Kab. Bangkalan, Jawa Timur • Kontak: +62 852-3151-5085</p>
    </div>

    <div style="text-align: center; margin-bottom: 12px;">
        <strong style="font-size: 13px; text-transform: uppercase;">LAPORAN REKAPITULASI PENJUALAN & ARUS KAS</strong>
        <div style="font-size: 11px; color: #555;">Periode: {{ $judulPeriode }}</div>
    </div>

    <!-- 4 KARTU RINGKASAN -->
    <div style="margin-bottom: 12px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 25%; border: 1px solid #ccc; padding: 6px; background: #fafafa;">
                    <div style="font-size: 8px; color: #666; font-weight: bold;">TOTAL OMZET</div>
                    <div style="font-size: 12px; font-weight: bold;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                    </div>
                </td>
                <td style="width: 25%; border: 1px solid #ccc; padding: 6px; background: #fafafa;">
                    <div style="font-size: 8px; color: #666; font-weight: bold;">TOTAL MODAL (HPP)</div>
                    <div style="font-size: 12px; font-weight: bold;">Rp {{ number_format($totalHpp, 0, ',', '.') }}
                    </div>
                </td>
                <td style="width: 25%; border: 1px solid #ccc; padding: 6px; background: #e6f4ea;">
                    <div style="font-size: 8px; color: #137333; font-weight: bold;">KEUNTUNGAN KOTOR</div>
                    <div style="font-size: 12px; font-weight: bold; color: #137333;">Rp
                        {{ number_format($totalLabaKotor, 0, ',', '.') }}</div>
                </td>
                <td style="width: 25%; border: 1px solid #ccc; padding: 6px; background: #fafafa;">
                    <div style="font-size: 8px; color: #666; font-weight: bold;">TOTAL TRANSAKSI</div>
                    <div style="font-size: 12px; font-weight: bold;">{{ $totalTrx }} Nota ({{ $totalItemTerjual }}
                        Item)</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TABEL DAFTAR TRANSAKSI -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>No. Nota</th>
                <th>Waktu</th>
                <th>Nama Pelanggan</th>
                <th>Kategori</th>
                <th>Metode</th>
                <th class="text-right">Omzet</th>
                <th class="text-right">HPP</th>
                <th class="text-right">Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penjualans as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $p->nomor_nota }}</td>
                    <td>{{ $p->tanggal->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->nama_pelanggan }}</td>
                    <td class="text-center">{{ $p->jenis_pelanggan }}</td>
                    <td class="text-center">{{ str_replace('_', ' ', $p->metode_pembayaran) }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($p->total_akhir, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p->total_hpp, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #137333;">Rp
                        {{ number_format($p->laba_kotor, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px;">Tidak ada data transaksi pada periode
                        ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="6" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #137333;">Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div style="margin-top: 30px; display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; text-align: center;">
            <div>Mengetahui,</div>
            <div style="font-weight: bold;">Kepala MDT Hidayatus Shibyan</div>
            <div style="height: 50px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">Ust. Abdul Malik, S.Pd.I</div>
        </div>
        <div style="display: table-cell; width: 50%; text-align: center;">
            <div>Somorkoneng, {{ date('d F Y') }}</div>
            <div style="font-weight: bold;">Pengurus Koperasi Madrasah</div>
            <div style="height: 50px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ Auth::user()->name }}</div>
        </div>
    </div>

</body>

</html>
