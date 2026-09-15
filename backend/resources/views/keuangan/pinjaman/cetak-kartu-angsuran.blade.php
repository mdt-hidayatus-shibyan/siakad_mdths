<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Kontrol Angsuran - {{ $pinjaman->kode_pinjaman }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #18181b;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 11.5px;
            line-height: 1.4;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #18181b;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .logo {
            width: 50px;
            height: 50px;
            margin-right: 12px;
        }

        .header-title h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .header-title p {
            margin: 1px 0 0;
            font-size: 9.5px;
            color: #52525b;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 15px;
            margin-bottom: 12px;
            background: #f4f4f5;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e4e4e7;
        }

        .info-item {
            display: flex;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
            color: #52525b;
        }

        .info-dots {
            width: 8px;
        }

        .info-val {
            font-weight: bold;
            color: #18181b;
        }

        .angsuran-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .angsuran-table th,
        .angsuran-table td {
            border: 1px solid #27272a;
            padding: 5px 6px;
            font-size: 10.5px;
        }

        .angsuran-table th {
            background: #e4e4e7;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .angsuran-table td.text-right {
            text-align: right;
            font-family: monospace;
        }

        .angsuran-table td.text-center {
            text-align: center;
        }

        .status-lunas {
            font-weight: bold;
            color: #059669;
        }

        .status-belum {
            color: #d97706;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 180px;
            text-align: center;
        }

        .signature-space {
            height: 45px;
        }

        .signature-name {
            font-weight: bold;
            border-top: 1px solid #71717a;
            padding-top: 2px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 7px 15px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Kartu Angsuran
        </button>
    </div>

    <div class="header">
        <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo" class="logo">
        <div class="header-title">
            <h2>MDT HIDAYATUS SHIBYAN</h2>
            <p>Somorkoneng, Blega, Bangkalan, Jawa Timur | Telp/WA: 08123456789</p>
            <p>Tahun Pelajaran: {{ $pinjaman->tahunPelajaran->nama_hijriyah ?? '-' }} /
                {{ $pinjaman->tahunPelajaran->nama_masehi ?? '-' }}</p>
        </div>
    </div>

    <div class="doc-title">
        <h3>KARTU KONTROL ANGSURAN PINJAMAN MADRASAH</h3>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Kode Pinjaman</span>
            <span class="info-dots">:</span>
            <span class="info-val" style="font-family: monospace;">{{ $pinjaman->kode_pinjaman }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Nominal Pinjaman</span>
            <span class="info-dots">:</span>
            <span class="info-val">Rp {{ number_format($pinjaman->nominal_pinjaman, 0, ',', '.') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Nama Nasabah</span>
            <span class="info-dots">:</span>
            <span class="info-val">{{ $pinjaman->nasabah->nama_lengkap }}
                ({{ $pinjaman->nasabah->kode_nasabah }})</span>
        </div>
        <div class="info-item">
            <span class="info-label">Tenor / Cicilan</span>
            <span class="info-dots">:</span>
            <span class="info-val">{{ $pinjaman->tenor_bulan }} Bulan (Rp
                {{ number_format($pinjaman->nominal_angsuran_total, 0, ',', '.') }}/bln)</span>
        </div>
        <div class="info-item">
            <span class="info-label">No. Telepon / HP</span>
            <span class="info-dots">:</span>
            <span class="info-val">{{ $pinjaman->nasabah->no_hp }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Sisa Hutang Pokok</span>
            <span class="info-dots">:</span>
            <span class="info-val" style="color: #dc2626;">Rp
                {{ number_format($pinjaman->sisa_pinjaman, 0, ',', '.') }}</span>
        </div>
    </div>

    <table class="angsuran-table">
        <thead>
            <tr>
                <th style="width: 25px;">Ke</th>
                <th>Jatuh Tempo</th>
                <th>Pokok</th>
                <th>Infaq / Margin</th>
                <th>Total Bayar</th>
                <th>Tgl Bayar</th>
                <th>No. Kuitansi</th>
                <th>Status</th>
                <th style="width: 70px;">Paraf Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pinjaman->angsurans as $angsuran)
                <tr>
                    <td class="text-center" style="font-weight: bold;">#{{ $angsuran->angsuran_ke }}</td>
                    <td class="text-center">{{ $angsuran->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                    <td class="text-right">Rp {{ number_format($angsuran->nominal_pokok, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($angsuran->nominal_infaq_margin, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp
                        {{ number_format($angsuran->total_bayar, 0, ',', '.') }}</td>
                    <td class="text-center">
                        {{ $angsuran->tanggal_bayar ? $angsuran->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                    <td class="text-center" style="font-family: monospace; font-size: 9.5px;">
                        {{ $angsuran->nomor_bukti_bayar ?? '-' }}</td>
                    <td class="text-center">
                        @if ($angsuran->status === 'lunas')
                            <span class="status-lunas">LUNAS</span>
                        @else
                            <span class="status-belum">BELUM</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 9px;">
                        {{ $angsuran->diterimaOleh ? $angsuran->diterimaOleh->name : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-box">
            <div>Nasabah Peminjam</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ $pinjaman->nasabah->nama_lengkap }}</div>
        </div>
        <div class="signature-box">
            <div>Bangkalan, {{ date('d F Y') }}</div>
            <div>Bendahara MDT</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ $pinjaman->disetujuiOleh->name ?? 'Pengurus Madrasah' }}</div>
        </div>
    </div>
</body>

</html>
