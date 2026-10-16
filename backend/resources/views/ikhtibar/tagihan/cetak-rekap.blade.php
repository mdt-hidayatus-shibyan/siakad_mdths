<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Tagihan Iuran Ikhtibar - {{ $tahunPelajaran->nama_hijriyah }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        @media print {
            body {
                zoom: 85%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            font-size: 10px;
            background: #fff;
            margin: 0;
            padding: 5px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header-title h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: 900;
            text-transform: uppercase;
            color: #0f172a;
        }

        .header-title h3 {
            margin: 2px 0;
            font-size: 11pt;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }

        .header-title p {
            margin: 0;
            font-size: 9pt;
            color: #64748b;
        }

        .meta-bar {
            display: flex;
            justify-content: space-between;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 9pt;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #94a3b8;
            padding: 5px 7px;
            vertical-align: middle;
        }

        th {
            background-color: #e2e8f0;
            text-align: center;
            font-weight: 900;
            font-size: 8.5pt;
            text-transform: uppercase;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; font-weight: bold; }
        .badge-lunas { color: #16a34a; font-weight: bold; }
        .badge-belum { color: #e11d48; font-weight: bold; }

        .ttd-box {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .ttd-col {
            text-align: center;
            width: 220px;
        }

        .ttd-space {
            height: 55px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 7px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Cetak Lembar Rekapitulasi (A4)
        </button>
    </div>

    <!-- Header Dokumen -->
    <div class="header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ $kegiatan->kop_logo_url }}" alt="Logo Kop" style="height: 50px; width: auto; object-fit: contain;">
            <div class="header-title">
                <h2>PANITIA {{ strtoupper($kegiatan->nama_kegiatan) }} ({{ $kegiatan->ikhtibar_ke ?: '52 Th' }})</h2>
                <h3>MDT HIDAYATUS SHIBYAN SOMORKONENG</h3>
                <p>Lembar Rekapitulasi Pelunasan Iuran Ikhtibar Per Kepala Keluarga (KK) — TP. {{ $tahunPelajaran->nama_hijriyah }} / {{ $tahunPelajaran->nama_masehi }}</p>
            </div>
        </div>
        <div style="text-align: right; font-size: 8.5pt; color: #64748b;">
            Tanggal Cetak: {{ date('d/m/Y H:i') }}<br>
            Petugas: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <!-- Meta Bar -->
    <div class="meta-bar">
        <span>Zonasi: {{ $kampung ? 'Dusun ' . $kampung->nama_kampung : 'Semua Dusun / Wilayah' }}</span>
        <span>Tarif Tagihan: Rp {{ number_format($selectedMaster?->nominal ?? 0, 0, ',', '.') }}/KK</span>
        <span>Total Kepala Keluarga: {{ $walis->count() }} KK</span>
    </div>

    <!-- Tabel Data Rekapitulasi -->
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">No. Reg</th>
                <th>Nama Kepala Keluarga</th>
                <th>Dusun</th>
                <th>Santri Terdaftar di MDT</th>
                <th style="width: 90px;">Nominal</th>
                <th style="width: 85px;">Status</th>
                <th style="width: 140px;">No Kwitansi / Tgl</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalLunasCount = 0;
                $totalNominalMasuk = 0;
            @endphp
            @forelse ($walis as $idx => $w)
                @php
                    $tgh = $w->tagihanWaliMurids->first();
                    $isLunas = $tgh && $tgh->status_bayar === 'Lunas';
                    if ($isLunas) {
                        $totalLunasCount++;
                        $totalNominalMasuk += (float) $tgh->nominal_tagihan;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono">{{ $w->no_registrasi ?: '-' }}</td>
                    <td style="font-weight: bold;">{{ $w->nama_kepala_keluarga }}</td>
                    <td>{{ $w->kampung?->nama_kampung ?? '-' }}</td>
                    <td>
                        @foreach ($w->murids as $anak)
                            <span>{{ $anak->nama_lengkap }} ({{ $anak->ruangans->first()?->nama_ruangan ?? '-' }}){{ !$loop->last ? ', ' : '' }}</span>
                        @endforeach
                    </td>
                    <td class="text-right font-mono">
                        Rp {{ number_format($tgh ? $tgh->nominal_tagihan : ($selectedMaster?->nominal ?? 0), 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if ($isLunas)
                            <span class="badge-lunas">LUNAS</span>
                        @elseif ($tgh)
                            <span class="badge-belum">BELUM LUNAS</span>
                        @else
                            <span style="color: #64748b;">BELUM TERBIT</span>
                        @endif
                    </td>
                    <td style="font-size: 8.5pt;">
                        @if ($isLunas && $tgh->pembayaranTagihan)
                            <span class="font-mono">{{ $tgh->pembayaranTagihan->no_transaksi }}</span><br>
                            <span style="color: #64748b;">{{ date('d/m/Y', strtotime($tgh->pembayaranTagihan->tanggal_bayar)) }} ({{ $tgh->pembayaranTagihan->metode_pembayaran }})</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada data tagihan ikhtibar untuk kriteria filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL REALISASI PEMBAYARAN:</td>
                <td class="text-right font-mono">Rp {{ number_format($totalNominalMasuk, 0, ',', '.') }}</td>
                <td class="text-center">{{ $totalLunasCount }} / {{ $walis->count() }} Lunas</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Lembar Tanda Tangan -->
    <div class="ttd-box">
        <div class="ttd-col">
            <p>Mengetahui,<br><strong>Ketua Panitia Ikhtibar</strong></p>
            <div class="ttd-space"></div>
            <p><strong><u>{{ $ketua ? $ketua->nama_tampil : '( ................................... )' }}</u></strong><br>
            {{ $ketua && $ketua->tipe_personel === 'ustadz' ? 'NIGM: ' . ($ketua->ustadz?->nigm ?? '-') : 'Panitia Pelaksana' }}</p>
        </div>

        <div class="ttd-col">
            <p>Somorkoneng, {{ date('d F Y') }}<br><strong>Bendahara Ikhtibar</strong></p>
            <div class="ttd-space"></div>
            <p><strong><u>{{ $bendahara ? $bendahara->nama_tampil : '( ................................... )' }}</u></strong><br>
            {{ $bendahara && $bendahara->tipe_personel === 'ustadz' ? 'NIGM: ' . ($bendahara->ustadz?->nigm ?? '-') : 'Panitia Pelaksana' }}</p>
        </div>
    </div>
</body>
</html>

