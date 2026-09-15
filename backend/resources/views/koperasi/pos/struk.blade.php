<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian #{{ $penjualan->nomor_nota }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0mm;
        }

        @media print {
            body {
                width: 58mm;
                margin: 0;
                padding: 4mm 3mm;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            width: 58mm;
            margin: 0 auto;
            padding: 5mm 3mm;
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

        .border-top {
            border-top: 1px dashed #000;
        }

        .border-bottom {
            border-bottom: 1px dashed #000;
        }

        .my-1 {
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .my-2 {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .btn-print {
            display: block;
            width: 100%;
            background: #059669;
            color: white;
            border: none;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="padding-bottom: 8px;">
        <button class="btn-print" onclick="window.print()">🖨️ CETAK STRUK</button>
    </div>

    <!-- HEADER STRUK -->
    <div class="text-center">
        <div class="font-bold" style="font-size: 13px;">KOPERASI MADRASAH</div>
        <div class="font-bold" style="font-size: 11px;">MDT HIDAYATUS SHIBYAN</div>
        <div style="font-size: 9px;">Somorkoneng - Bangkalan</div>
        <div style="font-size: 9px;">Telp / WA: +62 852-3151-5085</div>
    </div>

    <div class="border-top my-1"></div>

    <!-- INFO TRANSAKSI -->
    <table class="table" style="font-size: 10px;">
        <tr>
            <td>No. Nota</td>
            <td>:</td>
            <td class="font-bold">{{ $penjualan->nomor_nota }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $penjualan->tanggal->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $penjualan->petugas?->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td class="font-bold">
                {{ $penjualan->nama_pelanggan }}
                @if ($penjualan->murid && $penjualan->murid->ruangans->first())
                    ({{ $penjualan->murid->ruangans->first()->nama_ruangan }})
                @endif
            </td>
        </tr>
    </table>

    <div class="border-top my-1"></div>

    <!-- ITEM BELANJA -->
    <table class="table">
        @foreach ($penjualan->details as $d)
            <tr>
                <td colspan="3" class="font-bold">
                    {{ $d->nama_item }}
                    @if ($d->tipe_item === 'Paket_Bundling')
                        <span style="font-size: 8px; font-weight: normal;">[PAKET]</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="width: 50%; font-size: 10px;">
                    {{ $d->jumlah }} x {{ number_format($d->harga_jual, 0, ',', '.') }}
                </td>
                <td class="text-right font-bold" style="width: 50%;">
                    {{ number_format($d->subtotal, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </table>

    <div class="border-top my-1"></div>

    <!-- TOTAL KALKULASI -->
    <table class="table">
        <tr>
            <td class="text-left">Subtotal</td>
            <td class="text-right font-bold">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if ($penjualan->diskon > 0)
            <tr>
                <td class="text-left">Diskon</td>
                <td class="text-right font-bold">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr style="font-size: 12px;">
            <td class="text-left font-bold">TOTAL</td>
            <td class="text-right font-bold">Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Metode</td>
            <td class="text-right font-bold">
                {{ $penjualan->metode_pembayaran === 'Hutang' ? 'BAYAR NANTI / HUTANG' : str_replace('_', ' ', $penjualan->metode_pembayaran) }}
            </td>
        </tr>
        @if ($penjualan->metode_pembayaran === 'Hutang')
            @if ($penjualan->status_pembayaran === 'Belum_Lunas')
                <tr>
                    <td class="text-left font-bold">Status</td>
                    <td class="text-right font-bold" style="color: #000;">BELUM LUNAS</td>
                </tr>
                @if ($penjualan->catatan)
                    <tr>
                        <td colspan="2" class="text-left" style="font-size: 8px; font-style: italic;">
                            Ket: {{ $penjualan->catatan }}
                        </td>
                    </tr>
                @endif
            @else
                <tr>
                    <td class="text-left font-bold">Status</td>
                    <td class="text-right font-bold">*** LUNAS ***</td>
                </tr>
                <tr>
                    <td class="text-left" style="font-size: 9px;">Dilunasi</td>
                    <td class="text-right font-bold" style="font-size: 9px;">
                        {{ $penjualan->tanggal_pelunasan ? $penjualan->tanggal_pelunasan->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left" style="font-size: 9px;">Via</td>
                    <td class="text-right font-bold" style="font-size: 9px;">
                        {{ str_replace('_', ' ', $penjualan->metode_pelunasan ?? 'Tunai') }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left">Bayar</td>
                    <td class="text-right">Rp {{ number_format($penjualan->nominal_bayar, 0, ',', '.') }}</td>
                </tr>
                @if ($penjualan->kembalian > 0)
                    <tr>
                        <td class="text-left font-bold">Kembalian</td>
                        <td class="text-right font-bold">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
        @else
            <tr>
                <td class="text-left">Bayar</td>
                <td class="text-right">Rp {{ number_format($penjualan->nominal_bayar, 0, ',', '.') }}</td>
            </tr>
            @if ($penjualan->metode_pembayaran === 'Tunai')
                <tr>
                    <td class="text-left font-bold">Kembalian</td>
                    <td class="text-right font-bold">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endif
        @if (
            $penjualan->tabungan &&
                ($penjualan->metode_pembayaran === 'Potong_Tabungan' || $penjualan->metode_pelunasan === 'Potong_Tabungan'))
            <tr>
                <td class="text-left" style="font-size: 9px;">Sisa Tabungan</td>
                <td class="text-right font-bold" style="font-size: 9px;">Rp
                    {{ number_format($penjualan->tabungan->saldo, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <div class="border-top my-2"></div>

    <!-- FOOTER STRUK -->
    <div class="text-center" style="font-size: 9px;">
        <div>Terima kasih telah berbelanja di</div>
        <div class="font-bold">Koperasi MDT Hidayatus Shibyan</div>
        <div style="margin-top: 4px;">Barang yang sudah dibeli dapat ditukar jika ada cacat/rusak cetak kitab.</div>
        <div style="margin-top: 6px;">*** ALHAMDULILLAH ***</div>
    </div>

</body>

</html>
