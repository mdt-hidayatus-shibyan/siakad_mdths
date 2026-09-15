<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Transaksi - {{ $transaksi->kode_transaksi }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #18181b;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .kwitansi-container {
            border: 2px solid #27272a;
            padding: 15px 20px;
            border-radius: 8px;
            position: relative;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #27272a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo {
            width: 55px;
            height: 55px;
            margin-right: 15px;
        }

        .header-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-title p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #52525b;
        }

        .doc-title {
            position: absolute;
            top: 15px;
            right: 20px;
            text-align: right;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            color: #047857;
        }

        .doc-title span {
            font-size: 11px;
            font-family: monospace;
            font-weight: bold;
            color: #3f3f46;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .content-table td {
            padding: 5px 4px;
            vertical-align: top;
        }

        .content-table td.label {
            width: 130px;
            font-weight: bold;
            color: #52525b;
        }

        .content-table td.dots {
            width: 10px;
        }

        .nominal-box {
            background: #f4f4f5;
            border-left: 4px solid #059669;
            padding: 8px 12px;
            font-size: 15px;
            font-weight: 900;
            font-family: monospace;
            display: inline-block;
            margin-top: 5px;
        }

        .terbilang-box {
            background: #fafafa;
            border: 1px dashed #d4d4d8;
            padding: 6px 10px;
            font-style: italic;
            font-size: 11px;
            color: #27272a;
            border-radius: 4px;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            text-align: center;
        }

        .signature-box {
            width: 180px;
        }

        .signature-space {
            height: 50px;
        }

        .signature-name {
            font-weight: bold;
            border-top: 1px solid #71717a;
            padding-top: 3px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 8px 16px; background: #059669; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Kwitansi
        </button>
    </div>

    <div class="kwitansi-container">
        <div class="header">
            <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo" class="logo">
            <div class="header-title">
                <h2>MDT HIDAYATUS SHIBYAN</h2>
                <p>Somorkoneng, Blega, Bangkalan, Jawa Timur | Telp/WA: 08123456789</p>
                <p>Tahun Pelajaran: {{ $transaksi->tahunPelajaran->nama_hijriyah ?? '-' }} /
                    {{ $transaksi->tahunPelajaran->nama_masehi ?? '-' }}</p>
            </div>
            <div class="doc-title">
                <h3>
                    {{ $transaksi->jenis_transaksi === 'pemasukan' ? 'BUKTI KAS MASUK' : ($transaksi->jenis_transaksi === 'pengeluaran' ? 'BUKTI KAS KELUAR' : 'BUKTI MUTASI KAS') }}
                </h3>
                <span>{{ $transaksi->kode_transaksi }}</span>
            </div>
        </div>

        <table class="content-table">
            <tr>
                <td class="label">Telah Terima Dari / Ke</td>
                <td class="dots">:</td>
                <td style="font-weight: bold;">
                    {{ $transaksi->nomor_referensi ? $transaksi->nomor_referensi : $transaksi->akunKeuangan->nama_akun ?? 'Kas Madrasah' }}
                </td>
            </tr>
            <tr>
                <td class="label">Pos Akun / Kategori</td>
                <td class="dots">:</td>
                <td>{{ $transaksi->akunKeuangan->nama_akun }}
                    ({{ $transaksi->kategoriKeuangan->nama_kategori ?? 'Umum' }})</td>
            </tr>
            <tr>
                <td class="label">Metode Pembayaran</td>
                <td class="dots">:</td>
                <td>{{ $transaksi->metode_pembayaran === 'transfer_bank' ? 'Transfer Bank (' . ($transaksi->bank->nama_bank ?? 'Bank') . ' - ' . ($transaksi->bank->nomor_rekening ?? '') . ')' : 'Tunai / Kas Fisik' }}
                </td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran / Uraian</td>
                <td class="dots">:</td>
                <td>{{ $transaksi->keterangan ?? 'Transaksi Keuangan Madrasah' }}</td>
            </tr>
            <tr>
                <td class="label">Terbilang</td>
                <td class="dots">:</td>
                <td>
                    <div class="terbilang-box">
                        # {{ ucwords(terbilang($transaksi->nominal)) }} Rupiah #
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Jumlah Uang</td>
                <td class="dots">:</td>
                <td>
                    <div class="nominal-box">
                        Rp {{ number_format($transaksi->nominal, 0, ',', '.') }},-
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-signatures">
            <div class="signature-box">
                <div>Penyetor / Penerima</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
            </div>
            <div class="signature-box">
                <div>Bangkalan, {{ $transaksi->tanggal_transaksi->translatedFormat('d F Y') }}</div>
                <div>Bendahara / Petugas Kasir</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $transaksi->user->name ?? 'Pengurus Madrasah' }}</div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Uncomment if auto-print is desired
            // window.print();
        };
    </script>
</body>

</html>
