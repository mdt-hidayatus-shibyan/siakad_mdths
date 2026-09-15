<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Mutasi Tabungan - {{ $tabungan->nomor_rekening }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #ffffff;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f766e;
        }

        .header h2 {
            font-size: 13px;
            margin: 3px 0 0 0;
            font-weight: 600;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0 0 0;
            color: #6b7280;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .info-table td {
            padding: 2px 4px;
        }

        .info-table td.label {
            width: 120px;
            color: #4b5563;
            font-weight: 500;
        }

        .info-table td.val {
            font-weight: 700;
            color: #111827;
        }

        table.trx {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }

        table.trx th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #d1d5db;
            padding: 6px 4px;
            text-align: center;
        }

        table.trx td {
            border: 1px solid #e5e7eb;
            padding: 5px 4px;
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

        .saldo-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            float: right;
            margin-bottom: 15px;
            text-align: right;
        }

        .saldo-box .lbl {
            font-size: 10px;
            color: #166534;
            font-weight: 600;
        }

        .saldo-box .val {
            font-size: 14px;
            color: #15803d;
            font-weight: 800;
            font-family: monospace;
        }

        .footer-sign {
            margin-top: 30px;
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }

        .sign-col {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .sign-space {
            height: 55px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()"
            style="background: #0f766e; color: white; border: none; padding: 7px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- KOP MADRASAH -->
    <div class="header">
        <h1>Madrasah Diniyah Takmiliyah Hidayatus Shibyan</h1>
        <h2>BUKU MUTASI TABUNGAN MADRASAH</h2>
        <p>Somorkoneng, Blega, Bangkalan, Jawa Timur &bull; Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- INFORMASI REKENING -->
    <div class="info-grid">
        <div class="info-col">
            <table class="info-table">
                <tr>
                    <td class="label">Nomor Rekening</td>
                    <td class="val">: {{ $tabungan->nomor_rekening }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Rekening</td>
                    <td class="val">: {{ $tabungan->nama_rekening }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Nasabah</td>
                    <td class="val">: {{ $tabungan->nama_nasabah }}</td>
                </tr>
                <tr>
                    <td class="label">Kategori Nasabah</td>
                    <td class="val">: {{ $tabungan->jenis_nasabah }}</td>
                </tr>
            </table>
        </div>
        <div class="info-col">
            <table class="info-table">
                <tr>
                    <td class="label">Identitas / NISM</td>
                    <td class="val">:
                        @if ($tabungan->jenis_nasabah == 'Murid')
                            {{ $tabungan->murid?->nism ?? '-' }}
                        @elseif ($tabungan->jenis_nasabah == 'Ustadz')
                            {{ $tabungan->ustadz?->nigm ?? '-' }}
                        @elseif ($tabungan->jenis_nasabah == 'Kas Ruangan')
                            {{ $tabungan->ruangan?->nama_ruangan ?? '-' }}
                        @else
                            {{ $tabungan->kontak_umum ?? '-' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Periode Program</td>
                    <td class="val">: {{ $tabungan->periodeTabungan?->nama_periode ?? 'Reguler' }}</td>
                </tr>
                <tr>
                    <td class="label">Status Rekening</td>
                    <td class="val">: {{ $tabungan->status }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Dibuka</td>
                    <td class="val">: {{ $tabungan->created_at->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- TABEL MUTASI -->
    <table class="trx">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 85px;">Kode Transaksi</th>
                <th style="width: 55px;">Jenis</th>
                <th>Keterangan</th>
                <th style="width: 75px;">Nominal Kotor</th>
                <th style="width: 60px;">Potongan</th>
                <th style="width: 75px;">Nominal Bersih</th>
                <th style="width: 80px;">Saldo</th>
                <th style="width: 55px;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $trx)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($trx->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-center font-mono">{{ $trx->kode_transaksi }}</td>
                    <td class="text-center font-bold">{{ $trx->jenis_transaksi }}</td>
                    <td>{{ $trx->keterangan ?? '-' }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($trx->nominal_kotor, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">
                        {{ $trx->nominal_potongan > 0 ? 'Rp ' . number_format($trx->nominal_potongan, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold">Rp
                        {{ number_format($trx->nominal_bersih, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold" style="background-color: #f9fafb;">Rp
                        {{ number_format($trx->saldo_setelah, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $trx->petugas?->name ?? 'Sistem' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #9ca3af;">Belum ada riwayat
                        transaksi.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="5" class="text-right" style="padding: 6px;">TOTAL SALDO AKHIR:</td>
                <td colspan="5" class="font-mono" style="padding: 6px; font-size: 11px; color: #0f766e;">
                    Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer-sign">
        <div class="sign-col">
            <div>Pemilik Rekening / Wali,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>{{ $tabungan->nama_nasabah }}</u></div>
        </div>
        <div class="sign-col">
            <div>Somorkoneng, {{ date('d F Y') }}<br>Bendahara Madrasah,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>{{ Auth::user()->name ?? 'Pengurus Tabungan' }}</u></div>
        </div>
    </div>

</body>

</html>
