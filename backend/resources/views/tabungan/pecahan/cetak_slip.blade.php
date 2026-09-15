<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip_Pecahan_{{ $tabungan->nomor_rekening }}</title>

    <style>
        @page {
            size: A6 portrait;
            margin: 5mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 8.5px;
            color: #111827;
            background: #ffffff;
            margin: 0;
            padding: 4px;
        }

        .slip-box {
            border: 1.5px solid #0f766e;
            border-radius: 4px;
            padding: 6px;
        }

        .header {
            text-align: center;
            border-bottom: 1.5px dashed #0f766e;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .header h1 {
            font-size: 10px;
            margin: 0;
            text-transform: uppercase;
            color: #0f766e;
            font-weight: 800;
        }

        .header p {
            margin: 1px 0 0 0;
            font-size: 7.5px;
            color: #4b5563;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 6px;
        }

        table.info td {
            padding: 1px 2px;
        }

        table.pecahan {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 6px;
        }

        table.pecahan th {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 2.5px;
            text-align: center;
            font-size: 7.5px;
        }

        table.pecahan td {
            border: 1px solid #e2e8f0;
            padding: 2.5px 3px;
        }

        .hak-box {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 3px;
            padding: 4px 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            font-size: 8.5px;
            margin-bottom: 6px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
            text-align: center;
            margin-top: 4px;
        }

        .sign-space {
            height: 25px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 8px; text-align: right;">
        <button onclick="window.print()"
            style="background: #0f766e; color: white; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; font-weight: bold; font-size: 10px;">
            🖨️ Cetak Slip
        </button>
    </div>

    <div class="slip-box">
        <div class="header">
            <h1>MDT HIDAYATUS SHIBYAN</h1>
            <p>SLIP RINCIAN PECAHAN UANG BUKU TABUNGAN</p>
        </div>

        <table class="info">
            <tr>
                <td style="width: 55px; color: #6b7280;">No. Rekening</td>
                <td>: <strong>{{ $tabungan->nomor_rekening }}</strong></td>
                <td style="width: 45px; color: #6b7280;">Ruangan</td>
                <td>:
                    <strong>{{ $tabungan->murid?->ruangans?->first()?->nama_ruangan ?? ($tabungan->ruangan?->nama_ruangan ?? $tabungan->jenis_nasabah) }}</strong>
                </td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Nama Nasabah</td>
                <td colspan="3">: <strong>{{ $tabungan->nama_nasabah }}</strong></td>
            </tr>
        </table>

        <!-- Hak Bersih -->
        <div class="hak-box">
            <span>HAK BERSIH TUNAI:</span>
            <span style="font-family: monospace; font-size: 10px; color: #047857;">
                Rp {{ number_format($kalkulasi['saldo_bersih_total'] ?? $tabungan->saldo, 0, ',', '.') }}
            </span>
        </div>

        <!-- Tabel Pecahan -->
        <table class="pecahan">
            <thead>
                <tr>
                    <th>Pecahan</th>
                    <th>Jml</th>
                    <th>Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $hasPecahan = false; @endphp
                @foreach ($denominasiList as $k => $info)
                    @if ($pecahan[$k] > 0)
                        @php $hasPecahan = true; @endphp
                        <tr>
                            <td>{{ $info['label'] }}</td>
                            <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                {{ $pecahan[$k] }} {{ $info['tipe'] === 'Lembar' ? 'lbr' : 'koin' }}</td>
                            <td style="text-align: right; font-family: monospace;">
                                {{ number_format($pecahan[$k] * $info['nilai'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach
                @if (!$hasPecahan)
                    <tr>
                        <td colspan="3" style="text-align: center; color: #9ca3af; padding: 4px;">Tidak ada nominal
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="footer">
            <div style="width: 45%;">
                <div>Diterima Oleh,</div>
                <div class="sign-space"></div>
                <div>( {{ $tabungan->nama_nasabah }} )</div>
            </div>
            <div style="width: 45%;">
                <div>Petugas Kasir,</div>
                <div class="sign-space"></div>
                <div>( {{ Auth::user()->name ?? 'Petugas' }} )</div>
            </div>
        </div>
    </div>

</body>

</html>
