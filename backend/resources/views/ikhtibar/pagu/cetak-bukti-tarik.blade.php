<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Penarikan Sisa Pagu - {{ $transaksi->kode_penyaluran }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
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

            .no-print {
                display: none !important;
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
            background: #f0fdfa;
            color: #0f766e;
            border: 1px solid #99f6e4;
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
            color: #0d9488;
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
            width: 115px;
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
            color: #0d9488 !important;
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
            color: #0d9488;
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
            background: #f0fdfa;
            border-left: 3px solid #0d9488;
            padding: 8px 12px;
            border-radius: 0 6px 6px 0;
            font-size: 9.5px;
            margin-bottom: 18px;
            color: #115e59;
            line-height: 1.4;
        }

        .signature-area {
            width: 100%;
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            text-align: center;
        }

        .signature-box {
            width: 31%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .signature-box span:first-child {
            font-weight: 700;
            color: #475569;
            font-size: 9px;
        }

        .sign-spacer {
            height: 55px;
            margin: 6px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .sign-name {
            font-weight: 900;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 9.5px;
        }
    </style>
</head>

<body>
    <!-- TOMBOL CETAK & AKSI -->
    <div class="no-print"
        style="position: fixed; top: 12px; right: 15px; background: #1f2937; padding: 8px 14px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.15); display: flex; gap: 8px;">
        <button onclick="window.print()"
            style="background: #0d9488; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            🖨️ Cetak Kwitansi Tarik
        </button>
        <button onclick="window.close()"
            style="background: #4b5563; color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            ✕ Tutup
        </button>
    </div>

    <!-- 1. KOP SURAT RESMI -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ $kegiatan->kop_logo_url }}" alt="Logo Panitia" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>KWITANSI PENARIKAN SISA PAGU</h1>
            <p><strong>No. Transaksi:</strong> {{ $transaksi->kode_penyaluran }}</p>
            <p><strong>Tanggal Mutasi:</strong>
                {{ \Carbon\Carbon::parse($transaksi->tanggal_penyaluran)->translatedFormat('d F Y') }}</p>
            <div>
                <span class="kwitansi-badge">&#10004; DITARIK / REFUND</span>
            </div>
        </div>
    </div>

    <!-- 2. INFORMASI DIVISI & MUTASI KAS -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-title">Pengembalian Dari Divisi:</div>
            <table class="info-table">
                <tr>
                    <td>Pos / Divisi Asal</td>
                    <td class="val">: {{ strtoupper($transaksi->divisi?->nama_divisi ?? 'Divisi') }}
                        ({{ $transaksi->divisi?->kode_divisi }})</td>
                </tr>
                <tr>
                    <td>Kategori Mutasi</td>
                    <td class="val">: <strong
                            style="color: #0d9488;">{{ $transaksi->kategori_penarikan ?: $transaksi->jenis_transaksi }}</strong>
                    </td>
                </tr>
                @if ($transaksi->kategori_penarikan === 'Realokasi Antar Divisi' && $transaksi->divisiTujuanRealokasi)
                    <tr>
                        <td>Tujuan Alokasi</td>
                        <td class="val">: {{ $transaksi->divisiTujuanRealokasi->nama_divisi }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Diserahkan Oleh</td>
                    <td class="val">: {{ strtoupper($transaksi->disalurkanOleh?->nama_tampil ?? '-') }}
                        ({{ $transaksi->disalurkanOleh?->jabatan ?? 'Koordinator' }})</td>
                </tr>
                <tr>
                    <td>Diterima Oleh (BPH)</td>
                    <td class="val">: {{ strtoupper($transaksi->penerima?->nama_tampil ?? 'Bendahara Panitia') }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="info-box">
            <div class="info-title" style="text-align: right;">Rincian Kas Divisi & Mutasi:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Metode Penyerahan:</td>
                    <td class="val" style="text-transform: uppercase;">
                        {{ $transaksi->metode_penyaluran ?? 'TUNAI' }}</td>
                </tr>
                <tr>
                    <td>Total Pagu Tersalurkan:</td>
                    <td class="val">Rp {{ number_format($transaksi->divisi?->total_tersalurkan ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Realisasi SPJ Divisi:</td>
                    <td class="val">Rp
                        {{ number_format($transaksi->divisi?->total_realisasi_spj ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Sisa Kas Sebelum Tarik:</td>
                    <td class="val" style="color: #64748b;">
                        Rp
                        {{ number_format(($transaksi->divisi?->sisa_kas_di_divisi ?? 0) + $transaksi->nominal, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Nominal Pengembalian:</td>
                    <td class="val text-green">Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. TABEL RINCIAN ITEM -->
    <div class="table-title">
        Rincian Pengembalian Sisa Kas: <span>{{ strtoupper($transaksi->divisi?->nama_divisi ?? 'Divisi') }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Uraian Mutasi Kas</th>
                <th style="width: 140px;">Divisi Asal</th>
                <th>Keterangan / Berita Acara</th>
                <th style="text-align: right; width: 130px;">Nominal (Rp)</th>
                <th style="text-align: center; width: 85px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td style="font-weight: 600;">
                    Pengembalian Sisa Pagu Anggaran Operasional Divisi
                    <span style="font-size: 9px; color: #64748b; font-weight: normal; display: block;">
                        Kode Mutasi: {{ $transaksi->kode_penyaluran }}
                    </span>
                </td>
                <td style="font-weight: bold; color: #334155;">
                    {{ $transaksi->divisi?->nama_divisi }}
                </td>
                <td style="color: #475569;">
                    {{ $transaksi->catatan ?: 'Pengembalian sisa saldo kas fisik operasional divisi ke kas umum kepanitiaan.' }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #0d9488; font-weight: 900;">
                    DITERIMA
                </td>
            </tr>

            <!-- Baris Total -->
            <tr style="background: #f0fdfa; border-top: 2px solid #0d9488;">
                <td colspan="4" style="text-align: center; font-weight: 900; font-size: 11px;">
                    TOTAL DANA DIKEMBALIKAN (REFUND KAS DIVISI)
                </td>
                <td style="text-align: right; color: #0d9488; font-weight: 900; font-size: 12px;">
                    Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #0d9488; font-weight: 900;">
                    SELESAI
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 4. TERBILANG -->
    <div class="terbilang-box">
        <strong>Terbilang:</strong> <span># {{ terbilang($transaksi->nominal) }} Rupiah #</span>
    </div>

    <!-- 5. CATATAN KETERANGAN -->
    <div class="notes-box">
        <strong>Catatan:</strong> Kwitansi dan berita acara ini diterbitkan sah oleh Panitia
        {{ $kegiatan->judul_lengkap ?? 'Ikhtibar & Haflah Akhirussanah' }} MDT Hidayatus Shibyan sebagai bukti
        pengembalian dan penyetoran sisa kas fisik operasional divisi ke kas umum kepanitiaan ikhtibar.
        @if ($transaksi->catatan)
            <br><em>Catatan Mutasi: {{ $transaksi->catatan }}</em>
        @endif
    </div>

    <!-- 6. TANDA TANGAN 3 PIHAK -->
    <div class="signature-area">
        Somor Koneng, {{ \Carbon\Carbon::parse($transaksi->tanggal_penyaluran)->translatedFormat('d F Y') }}<br>
        <strong>Mengesahkan / Serah Terima Sisa Kas,</strong>
        <div class="footer-signatures">
            <!-- Kolom Yang Menyerahkan (Divisi) -->
            <div class="signature-box">
                <span>Yang Menyerahkan (Divisi)</span>
                <div class="sign-spacer">
                    @if (!empty($transaksi->disalurkanOleh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $transaksi->disalurkanOleh->id]),
                        ) !!}
                    @elseif ($transaksi->disalurkanOleh && $transaksi->disalurkanOleh->ttd_utama)
                        <img src="{{ $transaksi->disalurkanOleh->ttd_utama }}" style="height: 50px;" alt="TTD">
                    @else
                        <div style="height: 50px;"></div>
                    @endif
                </div>
                <span class="sign-name">{{ $transaksi->disalurkanOleh?->nama_tampil ?? 'Koordinator Divisi' }}</span>
            </div>

            <!-- Kolom Mengetahui (Ketua Panitia) -->
            <div class="signature-box">
                <span>Mengetahui (Ketua Panitia)</span>
                <div class="sign-spacer">
                    @if (!empty($ketua?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $ketua->id]),
                        ) !!}
                    @elseif ($ketua && $ketua->ttd_utama)
                        <img src="{{ $ketua->ttd_utama }}" style="height: 50px;" alt="TTD Ketua">
                    @else
                        <div style="height: 50px;"></div>
                    @endif
                </div>
                <span class="sign-name">{{ !empty($ketua) ? $ketua->nama_tampil : 'Ketua Panitia' }}</span>
            </div>

            <!-- Kolom Yang Menerima (BPH) -->
            <div class="signature-box">
                <span>Yang Menerima (BPH)</span>
                <div class="sign-spacer">
                    @if (!empty($transaksi->penerima?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $transaksi->penerima->id]),
                        ) !!}
                    @elseif ($transaksi->penerima && $transaksi->penerima->ttd_utama)
                        <img src="{{ $transaksi->penerima->ttd_utama }}" style="height: 50px;" alt="TTD Penerima">
                    @else
                        <div style="height: 50px;"></div>
                    @endif
                </div>
                <span class="sign-name">{{ $transaksi->penerima?->nama_tampil ?? 'Bendahara Panitia' }}</span>
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
