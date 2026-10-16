<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEGER TAGIHAN IKHTIBAR - {{ strtoupper($kampung ? $kampung->nama_kampung : 'SEMUA DUSUN') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 11pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8.5pt;
            color: #444;
        }
        .info-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 9pt;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        .status-lunas {
            color: #059669;
            font-weight: bold;
        }
        .status-belum {
            color: #d97706;
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 15px; right: 15px; background: #333; padding: 10px 15px; border-radius: 8px; z-index: 9999;">
        <button onclick="window.print()" style="background: #4f46e5; color: #fff; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">
            Cetak Leger Tagihan
        </button>
    </div>

    <div class="header">
        <h2>PANITIA {{ strtoupper($kegiatan->judul_lengkap) }}</h2>
        <h3>MDT HIDAYATUS SHIBYAN SOMORKONENG</h3>
        <p>Buku Kendali & Leger Pembayaran Tagihan Kepala Keluarga (KK) - TP. {{ $kegiatan->tahunPelajaran?->nama_hijriyah }} H / {{ $kegiatan->tahunPelajaran?->nama_masehi }} M</p>
    </div>

    <div class="info-bar">
        <span>Wilayah / Dusun: {{ $kampung ? $kampung->nama_kampung : 'Semua Dusun / Wilayah' }}</span>
        <span>Total Terdaftar: {{ $walis->count() }} KK</span>
        <span>Dicetak: {{ date('d/m/Y H:i') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 180px;">Nama Kepala Keluarga</th>
                <th style="width: 100px;">No. KK</th>
                <th style="width: 110px;">Dusun</th>
                <th>Nama Santri / Anak Aktif</th>
                <th style="width: 100px;">Nominal (Rp)</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 90px;">Tgl Bayar</th>
                <th style="width: 90px;">Paraf / TTD</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalNominal = 0;
                $totalLunas = 0;
            @endphp
            @forelse ($walis as $index => $w)
                @php
                    $t = $w->current_tagihan;
                    $nominal = $t ? (float) $t->nominal_tagihan : ($kegiatan->pengaturanTagihan?->nominal ?? 0);
                    $totalNominal += $nominal;
                    if ($t && $t->status_bayar === 'Lunas') {
                        $totalLunas += $nominal;
                    }
                @endphp
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td><strong>{{ $w->nama_kepala_keluarga }}</strong></td>
                    <td align="center">{{ $w->no_kk ?: '-' }}</td>
                    <td>{{ $w->kampung?->nama_kampung ?: '-' }}</td>
                    <td>{{ $w->murids->pluck('nama_lengkap')->implode(', ') ?: '-' }}</td>
                    <td align="right">{{ number_format($nominal, 0, ',', '.') }}</td>
                    <td align="center">
                        @if ($t && $t->status_bayar === 'Lunas')
                            <span class="status-lunas">LUNAS</span>
                        @else
                            <span class="status-belum">BELUM</span>
                        @endif
                    </td>
                    <td align="center">
                        {{ $t && $t->status_bayar === 'Lunas' ? $t->updated_at->format('d/m/Y') : '-' }}
                    </td>
                    <td align="center">
                        @if ($t && $t->status_bayar === 'Lunas')
                            <small>✓ Kasir</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" align="center" style="padding: 20px;">Tidak ada data tagihan KK.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="5" align="right">TOTAL POTENSI PENERIMAAN:</td>
                <td align="right">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td colspan="3" align="left">
                    Realisasi Lunas: <strong>Rp {{ number_format($totalLunas, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

