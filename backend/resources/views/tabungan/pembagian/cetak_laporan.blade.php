<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembagian Tabungan - {{ $simulasi['periode']->nama_periode }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 12mm 12mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #ffffff;
            margin: 0;
            padding: 15px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f766e;
        }

        .header h2 {
            font-size: 12px;
            margin: 2px 0 0 0;
            font-weight: 700;
        }

        .header p {
            font-size: 9.5px;
            margin: 2px 0 0 0;
            color: #4b5563;
        }

        .info-grid {
            margin-bottom: 12px;
            font-size: 10px;
        }

        .info-grid table td {
            padding: 2px 4px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 15px;
        }

        table.report th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            text-align: center;
        }

        table.report td {
            border: 1px solid #e5e7eb;
            padding: 4px 4px;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: monospace;
        }

        .font-bold {
            font-weight: 700;
        }

        .footer-sign {
            margin-top: 25px;
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }

        .sign-col {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .sign-space {
            height: 50px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()"
            style="background: #0f766e; color: white; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- KOP MADRASAH -->
    <div class="header">
        <h1>Madrasah Diniyah Takmiliyah Hidayatus Shibyan</h1>
        <h2>BERITA ACARA & REKAPITULASI PEMBAGIAN TABUNGAN {{ $jenisNasabah ? strtoupper($jenisNasabah) : 'MADRASAH' }}
        </h2>
        <p>Somorkoneng, Blega, Bangkalan, Jawa Timur &bull; Dicetak: {{ date('d/m/Y H:i') }} WIB</p>
    </div>

    <div class="info-grid">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 130px; font-weight: 600; color: #4b5563;">Periode Program</td>
                <td style="font-weight: 700;">: {{ $simulasi['periode']->nama_periode }}</td>
                <td style="width: 130px; font-weight: 600; color: #4b5563;">Kategori Nasabah</td>
                <td style="font-weight: 700;">: {{ $jenisNasabah ?: 'Semua Kategori Nasabah' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #4b5563;">Kelas / Ruangan</td>
                <td style="font-weight: 700;">:
                    {{ $ruangan ? $ruangan->nama_ruangan : ($jenisNasabah === 'Murid' ? 'Semua Ruangan / Seluruh Murid' : '-') }}
                </td>
                <td style="font-weight: 600; color: #4b5563;">Tanggal Pembagian</td>
                <td style="font-weight: 700;">:
                    {{ \Carbon\Carbon::parse($simulasi['periode']->tanggal_pembagian)->format('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- TABEL REKAP -->
    <table class="report">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 80px;">Identitas / NISM</th>
                <th>Nama Nasabah</th>
                <th style="width: 70px;">Kategori</th>
                <th style="width: 75px;">No. Rekening</th>
                <th style="width: 80px; text-align: right;">Tabungan Kotor</th>
                <th style="width: 75px; text-align: right;">Potongan</th>
                <th style="width: 85px; text-align: right;">Bersih Diterima</th>
                <th style="width: 65px;">Verifikasi</th>
                <th style="width: 80px;">Paraf Penerima</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($simulasi['rincian'] as $r)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center font-mono">{{ $r['identitas_nasabah'] }}</td>
                    <td class="font-bold">{{ $r['nama_nasabah'] }}</td>
                    <td class="text-center">{{ $r['jenis_nasabah'] }}
                        {{ $r['nama_ruangan'] !== '-' ? '(' . $r['nama_ruangan'] . ')' : '' }}</td>
                    <td class="text-center font-mono">{{ $r['nomor_rekening'] }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($r['saldo_kotor'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="color: #d97706;">
                        Rp {{ number_format($r['nominal_potongan'], 0, ',', '.') }}
                        <span style="font-size: 8px; color: #9ca3af;">({{ $r['persentase_potongan'] }}%)</span>
                    </td>
                    <td class="text-right font-mono font-bold" style="background-color: #f0fdf4; color: #0f766e;">
                        Rp {{ number_format($r['nominal_bersih'], 0, ',', '.') }}
                    </td>
                    <td class="text-center" style="font-size: 8.5px;">
                        @if ($r['status_verifikasi'] === 'Cocok')
                            <strong style="color: #0f766e;">[✓] Cocok</strong>
                        @elseif ($r['status_verifikasi'] === 'Selisih')
                            <strong style="color: #dc2626;">[!] Selisih</strong>
                        @else
                            <span style="color: #9ca3af;">[-] Belum</span>
                        @endif
                    </td>
                    <td style="font-size: 8px; color: #9ca3af; padding-left: 6px;">{{ $loop->iteration }}.
                        .................</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #9ca3af;">
                        Tidak ada data nasabah yang memiliki saldo pada filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="5" class="text-right" style="padding: 6px;">TOTAL KESELURUHAN
                    ({{ $simulasi['total_rekening'] }} Rekening):</td>
                <td class="text-right font-mono" style="padding: 6px;">Rp
                    {{ number_format($simulasi['total_saldo_kotor'], 0, ',', '.') }}</td>
                <td class="text-right font-mono" style="padding: 6px; color: #d97706;">Rp
                    {{ number_format($simulasi['total_potongan'], 0, ',', '.') }}</td>
                <td class="text-right font-mono"
                    style="padding: 6px; font-size: 11px; color: #0f766e; background-color: #dcfce7;">Rp
                    {{ number_format($simulasi['total_saldo_bersih'], 0, ',', '.') }}</td>
                <td colspan="2" class="text-center" style="font-size: 8.5px;">
                    {{ $simulasi['total_diverifikasi'] }} Cocok / {{ $simulasi['total_rekening'] }} Total
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer-sign">
        <div class="sign-col">
            <div>Mengetahui,<br>Kepala Madrasah Diniyah,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>Pengurus MDT Hidayatus Shibyan</u></div>
        </div>
        <div class="sign-col">
            <div>Somorkoneng, {{ date('d F Y') }}<br>Bendahara Tabungan,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>{{ Auth::user()->name ?? 'Bendahara Tabungan' }}</u></div>
        </div>
    </div>

</body>

</html>
