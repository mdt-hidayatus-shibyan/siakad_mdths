<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Pembelian - {{ $pembelian->nomor_faktur }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #111;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .logo-text h1 {
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .logo-text p {
            margin: 0;
            font-size: 11px;
            color: #555;
        }
        .faktur-title {
            text-align: right;
        }
        .faktur-title h2 {
            font-size: 16px;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #059669;
            text-transform: uppercase;
        }
        .faktur-title .nomor {
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 18px;
        }
        .info-grid td {
            vertical-align: top;
            padding: 2px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .items-table th {
            background-color: #f4f4f5;
            border-top: 1px solid #d4d4d8;
            border-bottom: 1px solid #d4d4d8;
            padding: 8px 6px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .items-table td {
            border-bottom: 1px solid #e4e4e7;
            padding: 8px 6px;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }
        
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .totals-table .grand-total {
            border-top: 2px solid #222;
            border-bottom: 2px solid #222;
            font-size: 13px;
            font-weight: bold;
        }
        
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sign-box {
            height: 60px;
        }
        
        @media print {
            .no-print { display: none; }
        }
        .print-btn {
            background: #059669;
            color: #fff;
            padding: 8px 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 15px;">
        <button class="print-btn" onclick="window.print()">Cetak Faktur Penerimaan (A4)</button>
    </div>

    <table class="header-table">
        <tr>
            <td class="logo-text">
                <h1>KOPERASI MDT HIDAYATUS SHIBYAN</h1>
                <p>Pengadaan Barang & Logistik Madrasah Diniyah Takmiliyah</p>
                <p>Somorkoneng, Kec. Kadur, Kab. Pamekasan</p>
            </td>
            <td class="faktur-title">
                <h2>FAKTUR PENERIMAAN BARANG</h2>
                <div class="nomor">{{ $pembelian->nomor_faktur }}</div>
                <div style="font-size: 10px; color: #666; margin-top: 3px;">
                    Status: <strong>{{ strtoupper($pembelian->status_pembayaran) }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td style="width: 50%;">
                <strong>SUPPLIER / DISTRIBUTOR:</strong><br>
                <span style="font-size: 13px; font-weight: bold;">{{ $pembelian->supplier }}</span><br>
                @if ($pembelian->nomor_faktur_supplier)
                    No. Faktur Fisik: #{{ $pembelian->nomor_faktur_supplier }}<br>
                @endif
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>TANGGAL PEMBELIAN:</strong><br>
                {{ $pembelian->tanggal->format('d/m/Y H:i') }} WIB<br>
                Metode Bayar: <strong>{{ str_replace('_', ' ', $pembelian->metode_pembayaran) }}</strong><br>
                Petugas Penerima: {{ $pembelian->petugas?->name ?? 'Petugas Koperasi' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th class="text-left">Kode & Nama Produk</th>
                <th class="text-center" style="width: 80px;">Qty Masuk</th>
                <th class="text-right" style="width: 100px;">Harga Satuan</th>
                <th class="text-right" style="width: 110px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pembelian->details as $idx => $d)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $d->nama_produk }}</strong>
                        <div style="font-size: 9px; color: #666;">Kode: {{ $d->kode_produk }}</div>
                    </td>
                    <td class="text-center font-bold">{{ $d->jumlah }} {{ $d->satuan }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($d->harga_beli_satuan, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Total Nilai Barang:</td>
            <td class="text-right font-mono">Rp {{ number_format($pembelian->total_nominal, 0, ',', '.') }}</td>
        </tr>
        @if ($pembelian->ongkir > 0)
            <tr>
                <td>Biaya Ongkir:</td>
                <td class="text-right font-mono">Rp {{ number_format($pembelian->ongkir, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if ($pembelian->diskon > 0)
            <tr>
                <td>Potongan Diskon:</td>
                <td class="text-right font-mono">- Rp {{ number_format($pembelian->diskon, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td>GRAND TOTAL:</td>
            <td class="text-right font-mono">Rp {{ number_format($pembelian->total_nominal + $pembelian->ongkir - $pembelian->diskon, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Nominal Dibayar:</td>
            <td class="text-right font-mono">Rp {{ number_format($pembelian->nominal_bayar, 0, ',', '.') }}</td>
        </tr>
        @if ($pembelian->sisa_hutang > 0)
            <tr style="font-weight: bold; color: #d97706;">
                <td>Sisa Hutang:</td>
                <td class="text-right font-mono">Rp {{ number_format($pembelian->sisa_hutang, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    @if ($pembelian->catatan)
        <div style="font-size: 10px; color: #555; margin-bottom: 20px;">
            <strong>Catatan:</strong> {{ $pembelian->catatan }}
        </div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                Pengirim / Toko Supplier,
                <div class="sign-box"></div>
                ( <strong>{{ $pembelian->supplier }}</strong> )
            </td>
            <td>
                Penerima / Petugas Koperasi,
                <div class="sign-box"></div>
                ( <strong>{{ $pembelian->petugas?->name ?? 'Petugas Koperasi' }}</strong> )
            </td>
        </tr>
    </table>

</body>
</html>
