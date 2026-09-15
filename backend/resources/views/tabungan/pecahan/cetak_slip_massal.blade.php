<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak_Slip_Pecahan_Massal_{{ date('Ymd_His') }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 8px;
            color: #111827;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .slips-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6mm;
        }

        .slip-item {
            border: 1px dashed #0f766e;
            border-radius: 4px;
            padding: 5px 6px;
            box-sizing: border-box;
            background: #fff;
            page-break-inside: avoid;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .header h1 {
            font-size: 9px;
            margin: 0;
            text-transform: uppercase;
            color: #0f766e;
            font-weight: 800;
        }

        .header p {
            margin: 0;
            font-size: 7px;
            color: #4b5563;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 4px;
        }

        table.info td {
            padding: 0.5px 1px;
        }

        .hak-box {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 3px;
            padding: 2.5px 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            font-size: 8px;
            margin-bottom: 4px;
        }

        table.pecahan {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 4px;
        }

        table.pecahan th {
            background: #f1f5f9;
            color: #334155;
            border: 0.5px solid #cbd5e1;
            padding: 1.5px;
            text-align: center;
            font-size: 7px;
        }

        table.pecahan td {
            border: 0.5px solid #e2e8f0;
            padding: 1.5px 2px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            font-size: 6.5px;
            text-align: center;
            margin-top: 2px;
        }

        .sign-space {
            height: 18px;
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

    <div class="no-print" style="margin-bottom: 10px; text-align: right; padding: 6px;">
        <button onclick="window.print()"
            style="background: #0f766e; color: white; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Cetak Semua Slip Massal (A4 Grid)
        </button>
    </div>

    <div class="slips-grid">
        @forelse ($rekap['rekap_per_nasabah'] as $item)
            <div class="slip-item">
                <div class="header">
                    <h1>MDT HIDAYATUS SHIBYAN</h1>
                    <p>SLIP PECAHAN UANG BUKU TABUNGAN</p>
                </div>

                <table class="info">
                    <tr>
                        <td style="width: 45px; color: #6b7280;">No. Rek</td>
                        <td>: <strong style="font-family: monospace;">{{ $item['nomor_rekening'] }}</strong></td>
                        <td style="width: 40px; color: #6b7280;">Ruang</td>
                        <td>: <strong>{{ $item['nama_ruangan'] }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280;">Nasabah</td>
                        <td colspan="3">: <strong style="font-size: 8px;">{{ $item['nama_nasabah'] }}</strong></td>
                    </tr>
                </table>

                <div class="hak-box">
                    <span>HAK BERSIH TUNAI:</span>
                    <span style="font-family: monospace; font-size: 9px; color: #047857;">
                        Rp {{ number_format($item['hak_bersih'], 0, ',', '.') }}
                    </span>
                </div>

                <table class="pecahan">
                    <thead>
                        <tr>
                            <th>Pecahan</th>
                            <th>Jml</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasPecahan = false; @endphp
                        @foreach ($rekap['denominasi_list'] as $k => $info)
                            @if (($item['pecahan'][$k] ?? 0) > 0)
                                @php $hasPecahan = true; @endphp
                                <tr>
                                    <td>{{ $info['label'] }}</td>
                                    <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                        {{ $item['pecahan'][$k] }} {{ $info['tipe'] === 'Lembar' ? 'lbr' : 'koin' }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace;">
                                        {{ number_format($item['pecahan'][$k] * $info['nilai'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        @if (!$hasPecahan)
                            <tr>
                                <td colspan="3" style="text-align: center; color: #9ca3af; padding: 2px;">Tidak ada
                                    saldo</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="footer">
                    <div style="width: 45%;">
                        <div>Diterima Oleh,</div>
                        <div class="sign-space"></div>
                        <div>( {{ $item['nama_nasabah'] }} )</div>
                    </div>
                    <div style="width: 45%;">
                        <div>Petugas Kasir,</div>
                        <div class="sign-space"></div>
                        <div>( {{ Auth::user()->name ?? 'Petugas' }} )</div>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: span 2; text-align: center; padding: 20px; color: #9ca3af;">
                Tidak ada data rekening pada filter ini.
            </div>
        @endforelse
    </div>

</body>

</html>
