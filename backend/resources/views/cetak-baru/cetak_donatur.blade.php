<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Kwitansi Donatur - {{ $pembayaran->nama_pembayar }}</title>
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
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .info-box {
            width: 48%;
        }

        .info-title {
            font-weight: 900;
            color: #0d9488;
            font-size: 10px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            color: #475569;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 80px;
        }

        .info-table .val {
            font-weight: bold;
            color: #0f172a;
            font-size: 11px;
        }

        .info-table.right-align td {
            text-align: right;
        }

        .info-table.right-align td:first-child {
            width: auto;
            padding-right: 10px;
        }

        .text-teal {
            color: #0d9488 !important;
            font-size: 14px;
        }

        .table-title {
            font-size: 11px;
            margin-bottom: 12px;
            color: #475569;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #cbd5e1;
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
            font-size: 12px;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            padding: 12px 10px;
        }

        .notes-box {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 25px;
            font-size: 10px;
            color: #475569;
            line-height: 1.6;
            text-align: center;
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
        $adminNama = auth()->user()->name ?? 'Admin Kasir';
        $tanggalCetak = \Carbon\Carbon::now()->translatedFormat('d F Y');
    @endphp

    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>TANDA BUKTI PEMBAYARAN SPP/SYAHRIYAH (DONATUR)</h1>
            <p>No. Transaksi: {{ $pembayaran->no_transaksi }}</p>
            <p>Tanggal Cetak: {{ $tanggalCetak }}</p>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-title">DITERIMA DARI DONATUR:</div>
            <table class="info-table">
                <tr>
                    <td>Nama/Instansi</td>
                    <td class="val">: {{ strtoupper($pembayaran->nama_pembayar) }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td class="val">: {{ strtoupper($pembayaran->alamat_pembayar ?? '-') }}</td>
                </tr>
            </table>
        </div>
        <div class="info-box">
            <div class="info-title" style="text-align: right;">RINCIAN PENERIMAAN:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Total Sasaran:</td>
                    <td class="val">{{ $pembayaran->tagihanMurids->count() }} Murid</td>
                </tr>
                <tr>
                    <td>Total Nominal:</td>
                    <td class="val text-teal">Rp {{ number_format($pembayaran->total_nominal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="table-title">
        Berikut nama-nama anak yang telah dilunasi <b>{{ strtoupper($bulanTagihan) }}</b> :
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">NO</th>
                <th style="text-align: center;">NIMM</th>
                <th>NAMA MURID</th>
                <th style="text-align: center;">L/P</th>
                <th style="text-align: center;">RUANGAN</th>
                <th style="text-align: right;">NOMINAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pembayaran->tagihanMurids as $index => $tagihan)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $tagihan->murid->nism ?? '-' }}</td>
                    <td>{{ strtoupper($tagihan->murid->nama_lengkap ?? 'Hamba Allah') }}</td>
                    <td style="text-align: center;">
                        {{ isset($tagihan->murid->jenis_kelamin) ? ($tagihan->murid->jenis_kelamin == 'Laki-laki' ? 'L' : 'P') : '-' }}
                    </td>
                    <td style="text-align: center;">{{ $tagihan->ruangan->nama_ruangan ?? '-' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">TOTAL DITERIMA:</td>
                <td style="text-align: right; color: #0d9488;">Rp
                    {{ number_format($pembayaran->total_nominal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="notes-box">
        <i>"Semoga Allah memberikan pahala atas apa yang engkau berikan, menjadikannya penyuci bagimu, dan melimpahkan
            berkah pada hartamu yang tersisa."</i><br>
        Terima kasih atas kebaikan Bapak/Ibu Donatur kepada Madrasah kami.
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
                        URL::signedRoute('profil.publik', [
                            'tipe' => 'pengurus',
                            'id' => $pengasuh->id,
                        ]),
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
                        URL::signedRoute('profil.publik', [
                            'tipe' => 'pengurus',
                            'id' => $sekretaris->id,
                        ]),
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
