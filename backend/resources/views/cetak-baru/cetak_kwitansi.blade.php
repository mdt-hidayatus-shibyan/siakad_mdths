<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $pembayaran->no_transaksi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
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
            font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif;
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
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 110px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.35;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.3px;
        }

        .kop-right p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .kwitansi-badge {
            display: inline-block;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 11px;
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .info-box {
            width: 48%;
        }

        .info-title {
            font-weight: 900;
            color: #15803d;
            font-size: 9.5px;
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
            width: 85px;
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
            padding-right: 8px;
        }

        .text-green {
            color: #16a34a !important;
            font-size: 12px;
        }

        .table-title {
            font-weight: 900;
            color: #1e293b;
            font-size: 10.5px;
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-title span {
            color: #15803d;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }

        .terbilang-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 10px;
            margin-bottom: 14px;
            color: #334155;
        }

        .terbilang-box span {
            font-style: italic;
            font-weight: 700;
            color: #0f172a;
            text-transform: capitalize;
        }

        .notes-box {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 8px 12px;
            border-radius: 0 6px 6px 0;
            font-size: 9.5px;
            margin-bottom: 18px;
            color: #1e40af;
            line-height: 1.4;
        }

        .signature-area {
            width: 100%;
            margin-top: 10px;
            text-align: right;
            font-size: 10px;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            text-align: center;
        }

        .signature-box {
            width: 42%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-box span:first-child {
            font-weight: 700;
            color: #475569;
            font-size: 9.5px;
        }

        .sign-spacer {
            height: 55px;
            margin: 6px 0;
        }

        .sign-name {
            font-weight: 900;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        $adminNama = $administrator?->nama_lengkap ?? (auth()->user()->name ?? 'Petugas Kasir');
        $tagihanList = $pembayaran->tagihanMurids;
        $firstTagihan = $tagihanList->first();
        $murid = $firstTagihan?->murid;
        $ruangan = $firstTagihan?->ruangan;
        $tahun = $ruangan?->tahunPelajaran ?? \App\Models\TahunPelajaran::where('is_active', true)->first();

        $namaWali = $murid?->waliMurid?->nama_wali ?? ($murid?->nama_ayah ?? ($pembayaran->nama_pembayar ?? '-'));
        $dusun = $murid?->dusun ?? ($murid?->waliMurid?->kampung?->nama_kampung ?? ($murid?->waliMurid?->desa ?? '-'));

        $isSpp = false;
        foreach ($tagihanList as $t) {
            $namaLower = strtolower($t->nama_tagihan_spesifik);
            if (str_contains($namaLower, 'spp') || str_contains($namaLower, 'syahriyah')) {
                $isSpp = true;
                break;
            }
        }
        $judulDokumen = $isSpp ? 'KWITANSI PELUNASAN SPP / SYAHRIYAH' : 'KWITANSI PEMBAYARAN RESMI';
    @endphp

    <!-- 1. KOP SURAT RESMI -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>{{ $judulDokumen }}</h1>
            <p><strong>No. Kwitansi:</strong> {{ $pembayaran->no_transaksi }}</p>
            <p><strong>Tanggal Bayar:</strong>
                {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->translatedFormat('d F Y') }}</p>
            <div><span class="kwitansi-badge">&#10004; LUNAS / SAH</span></div>
        </div>
    </div>

    <!-- 2. INFORMASI PEMBAYAR & TRANSAKSI -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-title">Diterima Dari:</div>
            <table class="info-table">
                @if ($murid)
                    <tr>
                        <td>Nama Murid</td>
                        <td class="val">: {{ strtoupper($murid->nama_lengkap) }}</td>
                    </tr>
                    <tr>
                        <td>NISM / ID</td>
                        <td class="val">: {{ $murid->nism }}</td>
                    </tr>
                    <tr>
                        <td>Kelas / Ruangan</td>
                        <td class="val">: {{ $ruangan->nama_ruangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Wali Murid</td>
                        <td class="val">: {{ strtoupper($namaWali) }}</td>
                    </tr>
                    <tr>
                        <td>Dusun / Alamat</td>
                        <td class="val">: {{ strtoupper($dusun) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Nama Pembayar</td>
                        <td class="val">: {{ strtoupper($pembayaran->nama_pembayar ?? 'Wali Murid') }}</td>
                    </tr>
                    <tr>
                        <td>Tipe Pembayar</td>
                        <td class="val">: {{ $pembayaran->tipe_pembayar ?? 'Umum' }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="info-box">
            <div class="info-title" style="text-align: right;">Rincian Transaksi:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Metode Pembayaran:</td>
                    <td class="val" style="text-transform: uppercase;">
                        {{ $pembayaran->metode_pembayaran ?? 'TUNAI' }}</td>
                </tr>
                @if ($pembayaran->rekening_penerima)
                    <tr>
                        <td>Rekening Tujuan:</td>
                        <td class="val">{{ $pembayaran->rekening_penerima }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Diterima Oleh:</td>
                    <td class="val" style="text-transform: uppercase;">{{ $adminNama }}</td>
                </tr>
                <tr>
                    <td>Total Pembayaran:</td>
                    <td class="val text-green">Rp {{ number_format($pembayaran->total_nominal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. TABEL RINCIAN ITEM TAGIHAN -->
    <div class="table-title">Rincian Item Pembayaran: <span>{{ $isSpp ? 'SPP / Syahriyah' : 'Tagihan Terkait' }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Deskripsi Tagihan</th>
                <th style="text-align: center; width: 110px;">Periode / Bulan</th>
                <th style="text-align: right; width: 120px;">Nominal (Rp)</th>
                <th style="text-align: center; width: 65px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tagihanList as $index => $item)
                @php
                    $periode = '-';
                    if ($item->bulanHijriyah) {
                        $periode = $item->bulanHijriyah->nama_bulan;
                    } elseif ($item->semester) {
                        $periode = 'Semester ' . $item->semester->nama_semester;
                    }
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">
                        {{ $item->nama_tagihan_spesifik }}
                        @if ($murid && $item->murid_id !== $murid->id)
                            <span
                                style="font-size: 9px; color: #64748b; font-weight: normal;">({{ $item->murid->nama_lengkap ?? '' }})</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold; color: #475569;">
                        {{ $periode }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        Rp {{ number_format($item->nominal_tagihan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; color: #16a34a; font-weight: 900;">
                        LUNAS
                    </td>
                </tr>
            @endforeach
            <!-- Baris Total -->
            <tr style="background: #f0fdf4; border-top: 2px solid #16a34a;">
                <td colspan="3" style="text-align: center; font-weight: 900; font-size: 11px;">TOTAL PEMBAYARAN</td>
                <td style="text-align: right; color: #16a34a; font-weight: 900; font-size: 12px;">
                    Rp {{ number_format($pembayaran->total_nominal, 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #16a34a; font-weight: 900;">LUNAS</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. TERBILANG -->
    <div class="terbilang-box">
        <strong>Terbilang:</strong> <span># {{ terbilang($pembayaran->total_nominal) }} Rupiah #</span>
    </div>

    <!-- 5. CATATAN KETERANGAN -->
    <div class="notes-box">
        <strong>Catatan:</strong> Kwitansi ini diterbitkan sah oleh sistem MDT Hidayatus Shibyan sebagai bukti
        pembayaran yang sah. Harap disimpan dengan baik sebagai bukti pelunasan.
        @if ($pembayaran->catatan)
            <br><em>Catatan Kasir: {{ $pembayaran->catatan }}</em>
        @endif
    </div>

    <!-- 6. TANDA TANGAN -->
    <div class="signature-area">
        Somor Koneng, {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->translatedFormat('d F Y') }}<br>
        <strong>Mengetahui / Mengesahkan,</strong>
        <div class="footer-signatures">
            <!-- Kolom Pengasuh -->
            <div class="signature-box">
                <span>Pengasuh Madrasah</span>
                @if (!empty($pengasuh?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                    ) !!}
                @else
                    <div class="sign-spacer"></div>
                @endif

            </div>

            <!-- Kolom Administrator / Penerima -->
            <div class="signature-box">
                <span>Penerima / Administrator</span>
                @if (!empty($administrator?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $administrator->id]),
                    ) !!}
                @elseif (!empty($bendahara?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $bendahara->id]),
                    ) !!}
                @else
                    <div class="sign-spacer"></div>
                @endif

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
