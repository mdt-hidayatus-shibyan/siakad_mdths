<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Mutasi & Verifikasi - {{ $tabungan->nomor_rekening }} ({{ $tabungan->nama_nasabah }})</title>
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
            padding-bottom: 8px;
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
            font-size: 9px;
            margin: 2px 0 0 0;
            color: #4b5563;
        }

        .info-grid {
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 10px;
        }

        .info-grid table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid table td {
            padding: 2px 4px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 12px;
        }

        table.report th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
            border: 1px solid #d1d5db;
            padding: 4px 4px;
            text-align: center;
        }

        table.report td {
            border: 1px solid #e5e7eb;
            padding: 3.5px 4px;
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

        .badge-cocok {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .footer-sign {
            margin-top: 20px;
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
            height: 45px;
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
        <h2>LEMBAR MUTASI & BUKTI VERIFIKASI BUKU TABUNGAN</h2>
        <p>Somorkoneng, Blega, Bangkalan, Jawa Timur &bull; Dicetak: {{ date('d/m/Y H:i') }} WIB</p>
    </div>

    <!-- INFORMASI REKENING -->
    <div class="info-grid">
        <table>
            <tr>
                <td style="width: 110px; color: #6b7280; font-weight: 600;">Nomor Rekening</td>
                <td style="font-weight: 700; font-family: monospace;">: {{ $tabungan->nomor_rekening }}</td>
                <td style="width: 120px; color: #6b7280; font-weight: 600;">Status Verifikasi</td>
                <td>:
                    @if ($tabungan->status_verifikasi === 'Cocok')
                        <span class="badge-cocok">[✓] SUDAH COCOK / SIAP DIKEMBALIKAN</span>
                    @elseif ($tabungan->status_verifikasi === 'Selisih')
                        <strong style="color: #dc2626;">[!] ADA SELISIH</strong>
                    @else
                        <span style="color: #6b7280;">[-] Belum Diverifikasi</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="color: #6b7280; font-weight: 600;">Nama Nasabah</td>
                <td style="font-weight: 700;">: {{ $tabungan->nama_nasabah }} ({{ $tabungan->jenis_nasabah }})</td>
                <td style="color: #6b7280; font-weight: 600;">Diverifikasi Oleh</td>
                <td>: {{ $tabungan->diverifikasiOleh?->name ?? '-' }}
                    ({{ $tabungan->diverifikasi_pada ? $tabungan->diverifikasi_pada->format('d/m/Y H:i') : '-' }})</td>
            </tr>
            <tr>
                <td style="color: #6b7280; font-weight: 600;">Kelas / Identitas</td>
                <td>: {{ $tabungan->identitas_nasabah }}
                    {{ $tabungan->murid?->ruangans?->first() ? '• Kelas ' . $tabungan->murid->ruangans->first()->nama_ruangan : '' }}
                </td>
                <td style="color: #6b7280; font-weight: 600;">Program Periode</td>
                <td>: {{ $tabungan->periodeTabungan?->nama_periode ?? 'Tabungan Bebas' }}</td>
            </tr>
            <tr>
                <td style="color: #6b7280; font-weight: 600;">Saldo Tercatat</td>
                <td style="font-weight: 700; color: #0f766e;">: Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}
                </td>
                <td style="color: #6b7280; font-weight: 600;">Hak Bersih</td>
                <td style="font-weight: 700; color: #0f766e;">: Rp
                    {{ number_format($kalkulasi['saldo_bersih_total'] ?? $tabungan->saldo, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- 1. TABEL REKAP BULANAN -->
    <div class="section-title">1. Rekapitulasi Mutasi Per Bulan Masehi</div>
    <table class="report">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Bulan & Tahun Masehi</th>
                <th style="width: 100px; text-align: right;">Total Setoran</th>
                <th style="width: 100px; text-align: right;">Total Penarikan</th>
                <th style="width: 100px; text-align: right;">Net Mutasi</th>
                <th style="width: 110px; text-align: right;">Saldo Akhir Bulan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mutasi['rekap_bulanan'] as $b)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $b['label'] }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($b['total_setor'], 0, ',', '.') }}
                        ({{ $b['frekuensi_setor'] }}x)</td>
                    <td class="text-right font-mono" style="color: #dc2626;">Rp
                        {{ number_format($b['total_tarik'], 0, ',', '.') }} ({{ $b['frekuensi_tarik'] }}x)</td>
                    <td class="text-right font-mono font-bold">{{ $b['net_mutasi'] >= 0 ? '+' : '' }}Rp
                        {{ number_format($b['net_mutasi'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold" style="background-color: #f0fdf4;">Rp
                        {{ number_format($b['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 10px; color: #9ca3af;">Belum ada mutasi
                        bulanan tercatat.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="2" class="text-right" style="padding: 4px;">TOTAL:</td>
                <td class="text-right font-mono">Rp {{ number_format($mutasi['total_setoran'], 0, ',', '.') }}</td>
                <td class="text-right font-mono" style="color: #dc2626;">Rp
                    {{ number_format($mutasi['total_penarikan'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">{{ $mutasi['total_transaksi'] }} Trx</td>
                <td class="text-right font-mono" style="color: #0f766e; background-color: #dcfce7;">Rp
                    {{ number_format($mutasi['saldo_terakhir'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. TABEL RINCIAN TRANSAKSI -->
    <div class="section-title">2. Histori Transaksi Lengkap (Buku Tabungan)</div>
    <table class="report">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 110px;">Kode Trx</th>
                <th>Uraian / Keterangan</th>
                <th style="width: 75px; text-align: right;">Masuk (Kredit)</th>
                <th style="width: 75px; text-align: right;">Keluar (Debit)</th>
                <th style="width: 85px; text-align: right;">Saldo</th>
                <th style="width: 60px;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mutasi['daftar_transaksi_kronologis'] as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center font-mono">{{ $t['tanggal_formatted'] }}</td>
                    <td class="font-mono text-center">{{ $t['kode_transaksi'] }}</td>
                    <td>{{ $t['kategori'] }} {{ $t['keterangan'] ? '• ' . $t['keterangan'] : '' }}</td>
                    <td class="text-right font-mono">
                        {{ $t['masuk'] > 0 ? 'Rp ' . number_format($t['masuk'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono" style="color: #dc2626;">
                        {{ $t['keluar'] > 0 ? 'Rp ' . number_format($t['keluar'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($t['saldo_akhir'], 0, ',', '.') }}
                    </td>
                    <td class="text-center" style="font-size: 8.5px;">{{ $t['petugas'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 10px; color: #9ca3af;">Belum ada riwayat
                        transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer-sign">
        <div class="sign-col">
            <div>Mengetahui / Menyetujui,<br>Wali Murid / Nasabah,</div>
            <div class="sign-space"></div>
            <div class="font-bold"><u>{{ $tabungan->nama_nasabah }}</u></div>
        </div>
        <div class="sign-col">
            <div>Somorkoneng, {{ date('d F Y') }}<br>Petugas Verifikasi Tabungan,</div>
            <div class="sign-space"></div>
            <div class="font-bold">
                <u>{{ $tabungan->diverifikasiOleh?->name ?? (Auth::user()->name ?? 'Petugas Tabungan') }}</u></div>
        </div>
    </div>

</body>

</html>
