<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kwitansi Tagihan KK - {{ $tagihan->pembayaranTagihan->no_transaksi ?? 'KWITANSI' }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 5mm;
        }

        @media print {
            body {
                zoom: 78%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
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

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            margin-bottom: 15px;
            text-decoration: none;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 120px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            color: #334155;
            text-transform: uppercase;
        }

        .kop-right p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .info-box {
            width: 48%;
        }

        .info-title {
            font-weight: 900;
            color: #2563eb;
            font-size: 10px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2.5px 0;
            color: #475569;
            vertical-align: top;
            font-size: 10.5px;
        }

        .info-table td:first-child {
            width: 95px;
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
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .table-title span {
            color: #2563eb;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 9px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
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
            padding: 10px;
        }

        .notes-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #475569;
            line-height: 1.5;
        }

        .notes-box b {
            color: #1e293b;
        }

        .signature-area {
            text-align: center;
            font-size: 11px;
            page-break-inside: avoid;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
        }

        .signature-box {
            width: 40%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sign-spacer {
            height: 60px;
            margin: 6px 0;
        }

        .sign-name {
            font-weight: 900;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .sign-role {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    @php
        $adminNama = $administrator?->nama_lengkap ?? (auth()->user()->name ?? 'Administrator');
        $noKwitansi = $tagihan->pembayaranTagihan->no_transaksi ?? 'KW-KK-' . $tagihan->id;
        $wali = $tagihan->waliMurid;
        $dusun = $wali->kampung->nama_kampung ?? ($wali->alamat_detail ?? '-');
        $tanggalBayar = $tagihan->pembayaranTagihan
            ? \Carbon\Carbon::parse($tagihan->pembayaranTagihan->tanggal_bayar)->translatedFormat('d F Y')
            : \Carbon\Carbon::now()->translatedFormat('d F Y');
    @endphp

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                <path
                    d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z" />
            </svg>
            Cetak Kwitansi
        </button>
    </div>

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo', 'assets/LOGO MDT.png')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>TANDA BUKTI PELUNASAN TAGIHAN {{ strtoupper($tagihan->nama_tagihan_spesifik) }}</h1>
            <p>No. Kwitansi: <strong>{{ $noKwitansi }}</strong></p>
            <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <!-- INFORMASI PEMBAYAR & TRANSAKSI -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-title">DITERIMA DARI:</div>
            <table class="info-table">
                <tr>
                    <td>Nama Kepala KK</td>
                    <td class="val">: {{ strtoupper($wali->nama_kepala_keluarga) }}</td>
                </tr>
                <tr>
                    <td>No. Registrasi</td>
                    <td class="val">: #{{ $wali->no_registrasi }}</td>
                </tr>
                <tr>
                    <td>No. KK</td>
                    <td class="val">: {{ $wali->no_kk ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Zonasi Dusun</td>
                    <td class="val">: {{ strtoupper($dusun) }}</td>
                </tr>
                <tr>
                    <td>Tahun Pelajaran</td>
                    <td class="val">: {{ $tagihan->tahunPelajaran->nama_hijriyah }}
                        ({{ $tagihan->tahunPelajaran->nama_masehi }})</td>
                </tr>
            </table>
        </div>
        <div class="info-box">
            <div class="info-title" style="text-align: right;">RINCIAN TRANSAKSI:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Metode:</td>
                    <td class="val">
                        @php
                            $metodeBayar = $tagihan->pembayaranTagihan->metode_pembayaran ?? 'TUNAI';
                            $rekPotong = $tagihan->pembayaranTagihan->rekening_penerima ?? null;
                            $isTabungan = in_array($metodeBayar, [
                                'Tabungan Murid',
                                'Potong Tabungan Murid',
                                'Tabungan Santri',
                                'Potong Tabungan',
                            ]);
                        @endphp
                        @if ($isTabungan)
                            POTONG TABUNGAN
                            @if ($rekPotong)
                                <div style="font-size: 9.5px; color: #2563eb; font-weight: bold; margin-top: 1px;">
                                    No. Rek: {{ $rekPotong }}
                                </div>
                            @endif
                        @else
                            {{ strtoupper($metodeBayar) }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Lunas:</td>
                    <td class="val">{{ $tanggalBayar }}</td>
                </tr>
                <tr>
                    <td>Diterima Oleh:</td>
                    <td class="val" style="text-transform: uppercase;">{{ $adminNama }}</td>
                </tr>
                <tr>
                    <td>Total Pelunasan:</td>
                    <td class="val text-green">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- DETAIL TAGIHAN -->
    <div class="table-title">DETAIL PEMBAYARAN: <span>{{ strtoupper($tagihan->nama_tagihan_spesifik) }}</span></div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">NO</th>
                <th>DESKRIPSI TAGIHAN</th>
                <th>TANGGUNGAN MURID AKTIF</th>
                <th style="text-align: right; width: 120px;">NOMINAL DIBAYAR</th>
                <th style="text-align: center; width: 60px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td>
                    <strong>{{ $tagihan->nama_tagihan_spesifik }}</strong>
                    <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                        Kode: {{ $tagihan->pengaturanTagihan->kode_tagihan ?? 'WLI' }} • Tingkat Kepala Keluarga (KK
                        Aktif)
                    </div>
                </td>
                <td>
                    @if ($wali->murids->isNotEmpty())
                        @foreach ($wali->murids as $anak)
                            <div style="font-size: 9.5px; margin-bottom: 2px;">
                                &bull; {{ $anak->nama_lengkap }}
                                ({{ $anak->ruangans->first()?->nama_ruangan ?? '-' }})
                            </div>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: right; font-weight: bold; font-size: 11px;">
                    Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #16a34a; font-weight: bold;">LUNAS</td>
            </tr>

            <!-- Baris Total Keseluruhan -->
            <tr class="total-row" style="border-top: 2px solid #16a34a; background-color: #f0fdf4;">
                <td colspan="3" style="text-align: center; font-weight: 900;">TOTAL KESELURUHAN DIBAYAR</td>
                <td style="text-align: right; color: #16a34a; font-weight: 900; font-size: 13px;">
                    Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #16a34a; font-weight: 900;">LUNAS</td>
            </tr>
        </tbody>
    </table>

    <div class="notes-box">
        <b>Keterangan: LUNAS</b><br>
        Catatan: Kwitansi ini sebagai bukti pelunasan pembayaran tagihan <b>{{ $tagihan->nama_tagihan_spesifik }}</b>
        Tahun Pelajaran
        {{ $tagihan->tahunPelajaran->nama_hijriyah }} / {{ $tagihan->tahunPelajaran->nama_masehi }} di Madrasah
        Diniyah Takmiliyah Hidayatus Shibyan Somor
        Koneng Kec. Kwanyar Kab. Bangkalan. Kami ucapkan terima kasih!
        @if (!empty($tagihan->pembayaranTagihan?->catatan))
            <br><em>Catatan Transaksi: {{ $tagihan->pembayaranTagihan->catatan }}</em>
        @endif
    </div>

    <!-- TANDA TANGAN -->
    <div class="signature-area">
        Somor Koneng, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
        <b>Mengetahui,</b>
        <div class="footer-signatures">
            <!-- Kolom Pengasuh -->
            <div class="signature-box">
                <span>Pengasuh Madrasah</span>
                @if (!empty($pengasuh?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                    ) !!}
                @else
                    <div class="sign-spacer"></div>
                @endif
                <span class="sign-name">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'K.H. Pengasuh') }}
                </span>

            </div>

            <!-- Kolom Administrator / Penerima -->
            <div class="signature-box">
                <span>Administrator / Petugas Kasir</span>
                @if (!empty($administrator?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $administrator->id]),
                    ) !!}
                @else
                    <div class="sign-spacer"></div>
                @endif
                <span class="sign-name">
                    {{ $administrator?->nama_lengkap ?? strtoupper($adminNama) }}
                </span>

            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            window.print();
        }, 1200);
    </script>
</body>

</html>
