<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran - {{ $tagihan->waliMurid?->nama_kepala_keluarga }}</title>
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
            width: 42%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .signature-box span:first-child {
            font-weight: 700;
            color: #475569;
            font-size: 9.5px;
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
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        $trx = $pembayaranIkhtibar ?? $tagihan->pembayaranIkhtibars->last();
        $isCicilan = $tagihan->status_bayar === 'Cicilan' || ($trx && $trx->tipe_pembayaran === 'Cicilan');
        $nominalCetak = $trx ? $trx->nominal_bayar : ($tagihan->total_terbayar ?: $tagihan->nominal_tagihan);
        $sisaCetak = $trx ? $trx->sisa_tagihan : $tagihan->sisa_tagihan;
        $noKwitansiCetak = $trx
            ? $trx->no_transaksi
            : $tagihan->pembayaranTagihan->no_transaksi ?? 'KW-IKH/' . str_pad($tagihan->id, 5, '0', STR_PAD_LEFT);
        $tglBayarCetak = $trx ? $trx->tanggal_bayar : $tagihan->pembayaranTagihan->tanggal_bayar ?? now();
        $metodeCetak = $trx ? $trx->metode_pembayaran : $tagihan->pembayaranTagihan->metode_pembayaran ?? 'Tunai';

        if ($tagihan->status_bayar === 'Lunas') {
            $judulDokumen = 'KWITANSI PELUNASAN IURAN IKHTIBAR';
        } elseif ($isCicilan) {
            $judulDokumen = 'KWITANSI PEMBAYARAN CICILAN IKHTIBAR';
        } else {
            $judulDokumen = 'KWITANSI PEMBAYARAN IURAN IKHTIBAR';
        }
    @endphp

    <!-- TOMBOL CETAK & AKSI -->
    <div class="no-print"
        style="position: fixed; top: 12px; right: 15px; background: #1f2937; padding: 8px 14px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.15); display: flex; gap: 8px;">
        <button onclick="window.print()"
            style="background: #059669; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            🖨️ Cetak Kwitansi
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
            <h1>{{ $judulDokumen }}</h1>
            <p><strong>No. Kwitansi:</strong> {{ $noKwitansiCetak }}</p>
            <p><strong>Tanggal Bayar:</strong>
                {{ \Carbon\Carbon::parse($tglBayarCetak)->translatedFormat('d F Y') }}</p>
            <div>
                @if ($tagihan->status_bayar === 'Lunas')
                    <span class="kwitansi-badge">&#10004; LUNAS / SAH</span>
                @elseif ($isCicilan)
                    <span class="kwitansi-badge" style="background: #fffbeb; color: #b45309; border-color: #fde68a;">
                        &#9203; CICILAN TERMIN T-{{ $trx?->termin_ke ?? $tagihan->jumlah_cicilan }}
                    </span>
                @else
                    <span class="kwitansi-badge" style="background: #fef2f2; color: #b91c1c; border-color: #fecaca;">
                        BELUM LUNAS
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. INFORMASI PEMBAYAR & TRANSAKSI -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-title">Diterima Dari:</div>
            <table class="info-table">
                <tr>
                    <td>Kepala Keluarga</td>
                    <td class="val">: {{ strtoupper($tagihan->waliMurid?->nama_kepala_keluarga ?? 'Wali Murid') }}
                    </td>
                </tr>
                <tr>
                    <td>No. Registrasi</td>
                    <td class="val">: {{ $tagihan->waliMurid?->no_registrasi ?: '-' }}</td>
                </tr>
                <tr>
                    <td>No. Kartu Keluarga</td>
                    <td class="val">: {{ $tagihan->waliMurid?->no_kk ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Dusun / Alamat</td>
                    <td class="val">: Dusun
                        {{ strtoupper($tagihan->waliMurid?->kampung?->nama_kampung ?? ($tagihan->waliMurid?->desa ?? '-')) }}
                        (Kode: {{ $tagihan->waliMurid?->kampung?->kode ?? 'A1' }})</td>
                </tr>
                <tr>
                    <td>Nama Anak</td>
                    <td class="val">:
                        {{ $tagihan->waliMurid?->murids->pluck('nama_lengkap')->implode(', ') ?: '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="info-box">
            <div class="info-title" style="text-align: right;">Rincian Transaksi:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Metode Transaksi:</td>
                    <td class="val" style="text-transform: uppercase;">{{ $metodeCetak }}</td>
                </tr>
                @if ($trx && $trx->nomor_tabungan)
                    <tr>
                        <td>Rek. Tabungan:</td>
                        <td class="val">{{ $trx->nomor_tabungan }} ({{ $trx->nama_nasabah }})</td>
                    </tr>
                    <tr>
                        <td>Status Konfirmasi:</td>
                        <td class="val" style="color: {{ $trx->is_verified ? '#16a34a' : '#d97706' }};">
                            {{ $trx->is_verified ? 'TERKONFIRMASI' : 'PENDING KONFIRMASI' }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>Diterima Oleh:</td>
                    <td class="val" style="text-transform: uppercase;">
                        {{ $trx?->diterimaOleh?->name ?? ($bendahara ? $bendahara->nama_tampil : 'Petugas Kasir') }}
                    </td>
                </tr>
                <tr>
                    <td>Total Tagihan:</td>
                    <td class="val">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pembayaran Saat Ini:</td>
                    <td class="val text-green">Rp {{ number_format($nominalCetak, 0, ',', '.') }}</td>
                </tr>
                @if ($sisaCetak > 0)
                    <tr>
                        <td>Sisa Tagihan:</td>
                        <td class="val" style="color: #dc2626;">Rp {{ number_format($sisaCetak, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <!-- 3. TABEL RINCIAN ITEM TAGIHAN -->
    <div class="table-title">
        Rincian Item Pembayaran:
        <span>{{ $tagihan->nama_tagihan_spesifik ?: 'Iuran Ikhtibar ' . ($kegiatan->ikhtibar_ke ?: '') }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Deskripsi Tagihan</th>
                <th style="text-align: center; width: 130px;">Tahun / Termin</th>
                <th style="text-align: right; width: 120px;">Nominal (Rp)</th>
                <th style="text-align: center; width: 85px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td style="font-weight: 600;">
                    {{ $tagihan->nama_tagihan_spesifik ?: 'Iuran Ikhtibar & Haflah Akhirussanah' }}
                    <span style="font-size: 9px; color: #64748b; font-weight: normal; display: block;">
                        Kepanitiaan Ikhtibar ({{ $kegiatan->ikhtibar_ke ?: '52 Th' }})
                    </span>
                </td>
                <td style="text-align: center; font-weight: bold; color: #475569;">
                    @if ($trx && $trx->termin_ke)
                        Cicilan Ke-{{ $trx->termin_ke }}
                    @else
                        {{ $kegiatan->tahunPelajaran?->nama_hijriyah ?? date('Y') }} H
                    @endif
                </td>
                <td style="text-align: right; font-weight: bold;">
                    Rp {{ number_format($nominalCetak, 0, ',', '.') }}
                </td>
                <td
                    style="text-align: center; color: {{ $tagihan->status_bayar === 'Lunas' ? '#16a34a' : '#d97706' }}; font-weight: 900;">
                    {{ strtoupper($trx ? $trx->tipe_pembayaran : $tagihan->status_bayar) }}
                </td>
            </tr>

            <!-- Baris Total Pembayaran -->
            <tr style="background: #f0fdf4; border-top: 2px solid #16a34a;">
                <td colspan="3" style="text-align: center; font-weight: 900; font-size: 11px;">
                    TOTAL DITERIMA (PEMBAYARAN SAAT INI)
                </td>
                <td style="text-align: right; color: #16a34a; font-weight: 900; font-size: 12px;">
                    Rp {{ number_format($nominalCetak, 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #16a34a; font-weight: 900;">
                    {{ strtoupper($tagihan->status_bayar) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Riwayat Termin Cicilan (Jika Ada Lebih Dari 1 Transaksi) -->
    @if ($tagihan->pembayaranIkhtibars->count() > 1)
        <div
            style="font-weight: 800; font-size: 9.5px; color: #475569; margin: 4px 0 6px 0; text-transform: uppercase;">
            Riwayat Transaksi Cicilan KK:
        </div>
        <table class="data-table" style="margin-bottom: 12px;">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>No. Transaksi / Termin</th>
                    <th style="text-align: center;">Tanggal</th>
                    <th>Metode</th>
                    <th style="text-align: right;">Nominal</th>
                    <th style="text-align: right;">Sisa</th>
                    <th style="text-align: center;">Penerima</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tagihan->pembayaranIkhtibars->sortBy('termin_ke') as $idx => $p)
                    <tr
                        style="{{ $trx && $trx->id === $p->id ? 'background-color: #ecfdf5; font-weight: bold;' : '' }}">
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>Termin {{ $p->termin_ke }} ({{ $p->no_transaksi }})</td>
                        <td style="text-align: center;">{{ date('d/m/Y', strtotime($p->tanggal_bayar)) }}</td>
                        <td>{{ $p->metode_pembayaran }} {{ $p->nomor_tabungan ? '(' . $p->nomor_tabungan . ')' : '' }}
                        </td>
                        <td style="text-align: right;">Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($p->sisa_tagihan, 0, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $p->diterimaOleh?->name ?? 'Kasir' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 4. TERBILANG -->
    <div class="terbilang-box">
        <strong>Terbilang:</strong> <span># {{ terbilang($nominalCetak) }} Rupiah #</span>
    </div>

    <!-- 5. CATATAN KETERANGAN -->
    <div class="notes-box">
        <strong>Catatan:</strong> Kwitansi ini diterbitkan sah oleh Panitia
        {{ $kegiatan->judul_lengkap ?? 'Ikhtibar & Haflah Akhirussanah' }} MDT Hidayatus Shibyan sebagai bukti
        pembayaran yang sah. Harap disimpan dengan baik sebagai bukti pelunasan.
        @if ($trx?->catatan)
            <br><em>Catatan Kasir: {{ $trx->catatan }}</em>
        @endif
    </div>

    <!-- 6. TANDA TANGAN -->
    <div class="signature-area">
        Somor Koneng, {{ \Carbon\Carbon::parse($tglBayarCetak)->translatedFormat('d F Y') }}<br>
        <strong>Mengesahkan / Diterima Oleh,</strong>
        <div class="footer-signatures">
            <!-- Kolom Ketua Panitia -->
            <div class="signature-box">
                <span>Ketua Panitia Ikhtibar</span>
                <div class="sign-spacer">
                    @if (!empty($ketua?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $ketua->id]),
                        ) !!}
                    @elseif ($ketua && $ketua->ttd_utama)
                        <img src="{{ $ketua->ttd_utama }}" style="height: 55px;" alt="TTD Ketua">
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </div>
                <span class="sign-name">{{ $ketua?->nama_tampil ?? 'Ketua Panitia' }}</span>
            </div>

            <!-- Kolom Bendahara / Kasir -->
            <div class="signature-box">
                <span>Penerima</span>
                <div class="sign-spacer">
                    @if (!empty($bendahara?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $bendahara->id]),
                        ) !!}
                    @elseif ($bendahara && $bendahara->ttd_utama)
                        <img src="{{ $bendahara->ttd_utama }}" style="height: 55px;" alt="TTD Bendahara">
                    @else
                        <div style="height: 55px;"></div>
                    @endif


                </div>
                <span
                    class="sign-name">{{ $trx?->diterimaOleh?->name ?? ($bendahara?->nama_tampil ?? 'Bendahara Ikhtibar') }}</span>
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
