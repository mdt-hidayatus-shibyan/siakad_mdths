<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Kwitansi Lunas SPP - {{ $murid->nama_lengkap }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 5mm;
        }

        @media print {
            body {
                zoom: 75%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 11px;
            background: #fff;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 130px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            color: #334155;
        }

        .kop-right p {
            margin: 4px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 11px;
        }

        .info-box {
            width: 48%;
        }

        .info-title {
            font-weight: 900;
            color: #2563eb;
            font-size: 10px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            color: #475569;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 90px;
        }

        .info-table .val {
            font-weight: bold;
            color: #0f172a;
        }

        .info-table.right-align td {
            text-align: right;
        }

        .info-table.right-align td:first-child {
            width: auto;
            padding-right: 10px;
        }

        .text-green {
            color: #16a34a !important;
            font-size: 12px;
        }

        .table-title {
            font-weight: 900;
            color: #1e293b;
            font-size: 11px;
            text-align: center;
            margin-bottom: 12px;
        }

        .table-title span {
            color: #2563eb;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .data-table .total-row td {
            background: #f8fafc;
            font-weight: 900;
            font-size: 11px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 12px 10px;
        }

        .notes-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 25px;
            font-size: 10px;
            color: #475569;
            line-height: 1.6;
        }

        .notes-box b {
            color: #1e293b;
        }

        .signature-area {
            text-align: center;
            font-size: 11px;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .signature-box {
            width: 35%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qr-code {
            width: 65px;
            height: 65px;
            margin: 10px 0;
        }

        .sign-name {
            font-weight: 900;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    @php
        // Persiapan Variabel Aman
        $adminNama = auth()->user()->name ?? 'Administrator';
        $noKwitansi = $tagihanLunas->last()->pembayaranTagihan->no_transaksi ?? 'REKAP-' . $murid->nism;
        $namaWali = $murid->waliMurid->nama_wali ?? ($murid->nama_ayah ?? '-');
        $dusun = $murid->dusun ?? ($murid->waliMurid->kampung->nama_kampung ?? ($murid->waliMurid->desa ?? '-'));
        $ruangan = $tagihanLunas->first()->ruangan->nama_ruangan ?? '-';
    @endphp

    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>TANDA BUKTI PELUNASAN SPP/SYAHRIYAH</h1>
            <p>No. Kwitansi: {{ $noKwitansi }}</p>
            <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-title">DITERIMA DARI:</div>
            <table class="info-table">
                <tr>
                    <td>Nama Murid</td>
                    <td class="val">: {{ strtoupper($murid->nama_lengkap) }}</td>
                </tr>
                <tr>
                    <td>NIMM/ID</td>
                    <td class="val">: {{ $murid->nism }}</td>
                </tr>
                <tr>
                    <td>Wali</td>
                    <td class="val">: {{ strtoupper($namaWali) }}</td>
                </tr>
                <tr>
                    <td>Dusun</td>
                    <td class="val">: {{ strtoupper($dusun) }}</td>
                </tr>
                <tr>
                    <td>Ruangan</td>
                    <td class="val">: {{ $ruangan }}</td>
                </tr>
            </table>
        </div>
        <div class="info-box">
            <div class="info-title" style="text-align: right;">RINCIAN TRANSAKSI:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Metode:</td>
                    <td class="val">TUNAI</td>
                </tr>
                <tr>
                    <td>Diterima Oleh:</td>
                    <td class="val" style="text-transform: uppercase;">{{ $adminNama }}</td>
                </tr>
                <tr>
                    <td>Total Pelunasan:</td>
                    <td class="val text-green">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="table-title">DETAIL TAGIHAN: <span>SPP/SYAHRIYAH</span></div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">NO</th>
                <th>PERIODE BULAN</th>
                <th style="text-align: center;">TANGGAL LUNAS</th>
                <th style="text-align: right;">NOMINAL DIBAYAR</th>
                <th style="text-align: center; width: 60px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @php
                // 1. Mengelompokkan data berdasarkan Nomor Transaksi
                $groupedTagihan = $tagihanLunas->groupBy(function ($item) {
                    return $item->pembayaranTagihan->no_transaksi ?? 'Tanpa Transaksi';
                });

                $index = 1;
            @endphp

            @foreach ($groupedTagihan as $no_transaksi => $items)
                @php
                    // Ambil data transaksi dari item pertama di dalam grup ini
                    $transaksi = $items->first()->pembayaranTagihan;
                    $tanggal = $transaksi
                        ? \Carbon\Carbon::parse($transaksi->tanggal_bayar)->translatedFormat('d M Y')
                        : '-';
                    $subtotal = $items->sum('nominal_tagihan');
                @endphp

                <!-- Baris Header Grup Transaksi -->
                <tr style="background-color: #f8fafc;">
                    <td colspan="5"
                        style="padding: 8px 12px; border-top: 2px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; text-align: left;">
                        <span style="font-weight: bold; font-size: 12px; color: #475569;">No. Transaksi : </span>
                        <span style="font-weight: 900; color: #2563eb;">{{ $no_transaksi }}</span>

                        <!-- Menampilkan subtotal nominal per kuitansi di sebelah kanan -->
                        <span style="float: right; font-size: 12px; font-weight: bold; color: #475569;">
                            Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>

                <!-- Looping Rincian Item Tagihan di dalam Kuitansi tersebut -->
                @foreach ($items as $tagihan)
                    <tr>
                        <td style="text-align: center;">{{ $index++ }}</td>
                        <td style="text-transform: uppercase; padding-left: 20px;">
                            &bull; {{ $tagihan->nama_tagihan_spesifik }}
                        </td>
                        <td style="text-align: center;">
                            {{ $tanggal }}
                        </td>
                        <td style="text-align: right;">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center; color: #16a34a; font-weight: bold;">LUNAS</td>
                    </tr>
                @endforeach
            @endforeach

            <!-- Baris Total Keseluruhan -->
            <tr class="total-row" style="border-top: 2px solid #16a34a; background-color: #f0fdf4;">
                <td colspan="3" style="text-align: center; font-weight: 900;">TOTAL KESELURUHAN DIBAYAR</td>
                <td style="text-align: right; color: #16a34a; font-weight: 900; font-size: 14px;">
                    Rp {{ number_format($totalDibayar, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="notes-box">
        <b>Keterangan: LUNAS</b><br>
        Catatan: Kwitansi ini sebagai bukti pelunasan pembayaran <b>SPP/SYAHRIYAH</b> Tahun Pelajaran
        {{ $tahun->nama_hijriyah }} / {{ $tahun->nama_masehi }} di Madrasah Diniyah Takmiliyah Hidayatus Shibyan Somor
        Koneng Kec. Kwanyar Kab. Bangkalan. Kami ucapkan terima kasih!
    </div>

    <div class="signature-area">
        Somor Koneng, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
        <b>Mengetahui,</b>
        <div class="footer-signatures">

            <!-- Kolom Pengasuh -->
            <div class="signature-box">
                <span>Pengasuh Madrasah</span>

                @if (!empty($pengasuh?->id))
                    <!-- QR Code Validasi -->
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                    ) !!}
                @else
                    <!-- Ruang kosong (spacer) jika QR tidak dirender -->
                    <div style="height: 70px; margin: 10px 0;"></div>
                @endif

                <!-- Pemanggilan nama lewat relasi anggota dengan Null Safe (?->) -->
                <span class="sign-name">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Pengasuh Belum Diatur') }}
                </span>
            </div>

            <!-- Kolom Sekretaris -->
            <div class="signature-box">
                <span>Sekretaris</span>

                @if (!empty($sekretaris?->id))
                    <!-- QR Code Validasi -->
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $sekretaris->id]),
                    ) !!}
                @else
                    <!-- Ruang kosong (spacer) jika QR tidak dirender -->
                    <div style="height: 70px; margin: 10px 0;"></div>
                @endif

                <!-- Pemanggilan nama lewat relasi anggota dengan Null Safe (?->) -->
                <span class="sign-name">
                    {{ $sekretaris?->anggota?->nama_lengkap ?? ($sekretaris?->nama ?? 'Nama Sekretaris Belum Diatur') }}
                </span>
            </div>

        </div>
    </div>

    <script>
        setTimeout(function() {
            window.print();
        }, 1500);
    </script>
</body>

</html>
