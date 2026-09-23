<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rincian & Audit Kas Tabungan - Tahun {{ $tahun }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 12mm 12mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
            background: #ffffff;
            margin: 0;
            padding: 10px;
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
            font-size: 9.5px;
            margin: 2px 0 0 0;
            color: #4b5563;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #0f766e;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 3px;
            margin: 12px 0 6px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }

        .info-card {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px 8px;
            background-color: #f9fafb;
        }

        .info-card .label {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #6b7280;
        }

        .info-card .val {
            font-size: 12px;
            font-weight: 800;
            color: #111827;
            margin-top: 2px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 10px;
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

        table.report tfoot td {
            background-color: #f9fafb;
            font-weight: 800;
            border-top: 2px solid #9ca3af;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 700;
        }

        .two-cols {
            display: flex;
            gap: 10px;
        }

        .two-cols>div {
            flex: 1;
        }

        .footer-sign {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
            width: 200px;
        }

        .sign-space {
            height: 50px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- Tombol Cetak Browser -->
    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 6px 14px; background: #0f766e; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            style="padding: 6px 14px; background: #6b7280; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 11px; margin-left: 5px;">
            Tutup
        </button>
    </div>

    <!-- Kop Header -->
    <div class="header">
        <h1>MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN</h1>
        <h2>LAPORAN RINCIAN, ARUS KAS & AUDIT KAS FISIK TABUNGAN</h2>
        <p>Tahun Rekapitulasi Masehi: <strong>{{ $tahun }}</strong>
            {{ $periodeTerpilih ? '• Periode: ' . $periodeTerpilih->nama_periode : '• Seluruh Periode' }} • Dicetak
            pada: {{ date('d F Y, H:i') }} WIB</p>
    </div>

    <!-- 1. KONTROL KAS & KEWAJIBAN MADRASAH -->
    <div class="section-title">1. Ringkasan Kontrol Kas & Kewajiban Madrasah</div>
    <div class="info-grid">
        <div class="info-card">
            <div class="label">Kas Fisik Tabungan</div>
            <div class="val" style="color: #0f766e;">Rp {{ number_format($totalKasFisik, 0, ',', '.') }}</div>
            <div style="font-size: 8.5px; color: #6b7280; margin-top: 2px;">{{ $totalRekeningAktif }} Rekening Aktif
            </div>
        </div>
        <div class="info-card">
            <div class="label">Hak Bersih Nasabah</div>
            <div class="val" style="color: #2563eb;">Rp {{ number_format($totalHakBersihNasabah, 0, ',', '.') }}
            </div>
            <div style="font-size: 8.5px; color: #6b7280; margin-top: 2px;">Kewajiban Belum Ditarik</div>
        </div>
        <div class="info-card">
            <div class="label">Potongan Madrasah</div>
            <div class="val" style="color: #d97706;">Rp {{ number_format($totalPotonganMadrasah, 0, ',', '.') }}
            </div>
            <div style="font-size: 8.5px; color: #6b7280; margin-top: 2px;">Hak Alokasi Madrasah</div>
        </div>
        <div class="info-card">
            <div class="label">Total Perputaran Kas</div>
            <div class="val" style="font-size: 10.5px;">+{{ number_format($totalSetorSemua, 0, ',', '.') }}</div>
            <div style="font-size: 8.5px; color: #e11d48; margin-top: 2px;">Tarik:
                -{{ number_format($totalTarikSemua, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- 2. REKAPITULASI ARUS KAS BULANAN MASEHI -->
    <div class="section-title">2. Perolehan Arus Kas Per Bulan Masehi (Januari - Desember {{ $tahun }})</div>
    <table class="report">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 18%; text-align: left;">Bulan Masehi</th>
                <th style="width: 10%;">Trx Setor</th>
                <th style="width: 18%; text-align: right;">Total Setor (Masuk)</th>
                <th style="width: 10%;">Trx Tarik</th>
                <th style="width: 18%; text-align: right;">Total Tarik (Keluar)</th>
                <th style="width: 22%; text-align: right;">Arus Kas Bersih (Net Flow)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapBulanan as $b)
                <tr>
                    <td class="text-center">{{ $b['bulan_angka'] }}</td>
                    <td><strong>{{ $b['nama_bulan'] }}</strong></td>
                    <td class="text-center">{{ $b['jumlah_setor'] > 0 ? $b['jumlah_setor'] . 'x' : '-' }}</td>
                    <td class="text-right" style="color: #0f766e;">
                        {{ $b['nominal_setor'] > 0 ? 'Rp ' . number_format($b['nominal_setor'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-center">{{ $b['jumlah_tarik'] > 0 ? $b['jumlah_tarik'] . 'x' : '-' }}</td>
                    <td class="text-right" style="color: #e11d48;">
                        {{ $b['nominal_tarik'] > 0 ? 'Rp ' . number_format($b['nominal_tarik'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-bold" style="color: {{ $b['net_flow'] >= 0 ? '#0f766e' : '#e11d48' }};">
                        {{ $b['net_flow'] != 0 ? ($b['net_flow'] > 0 ? '+' : '') . 'Rp ' . number_format($b['net_flow'], 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-center">TOTAL TAHUN {{ $tahun }}</td>
                <td class="text-center">{{ number_format($totalTrxSetorTahun) }}x</td>
                <td class="text-right" style="color: #0f766e;">Rp {{ number_format($totalSetorTahun, 0, ',', '.') }}
                </td>
                <td class="text-center">{{ number_format($totalTrxTarikTahun) }}x</td>
                <td class="text-right" style="color: #e11d48;">Rp {{ number_format($totalTarikTahun, 0, ',', '.') }}
                </td>
                <td class="text-right font-bold" style="color: {{ $totalNetFlowTahun >= 0 ? '#0f766e' : '#e11d48' }};">
                    {{ $totalNetFlowTahun >= 0 ? '+' : '' }}Rp {{ number_format($totalNetFlowTahun, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- 3. DUA TABEL: POTONGAN & KATEGORI PENARIKAN -->
    <div class="two-cols">
        <!-- Kolom Kiri: Potongan -->
        <div>
            <div class="section-title">3. Potongan per Jenis Nasabah</div>
            <table class="report">
                <thead>
                    <tr>
                        <th style="text-align: left;">Kategori</th>
                        <th>Jml</th>
                        <th style="text-align: right;">Total Setor</th>
                        <th>%</th>
                        <th style="text-align: right;">Potongan</th>
                        <th style="text-align: right;">Hak Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekapPotongan as $rp)
                        <tr>
                            <td><strong>{{ $rp['jenis_nasabah'] }}</strong></td>
                            <td class="text-center">{{ $rp['jumlah_rekening'] }}</td>
                            <td class="text-right">Rp {{ number_format($rp['total_setor'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ $rp['persentase_potongan'] }}%</td>
                            <td class="text-right font-bold" style="color: #d97706;">
                                Rp {{ number_format($rp['nominal_potongan'], 0, ',', '.') }}
                            </td>
                            <td class="text-right font-bold">
                                Rp {{ number_format($rp['sisa_hak_ditarik'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-center">TOTAL</td>
                        <td class="text-right">Rp {{ number_format($totalSetorSemua, 0, ',', '.') }}</td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold" style="color: #d97706;">
                            Rp {{ number_format($totalPotonganMadrasah, 0, ',', '.') }}
                        </td>
                        <td class="text-right font-bold">
                            Rp {{ number_format($totalHakBersihNasabah, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Kolom Kanan: Kategori Penarikan -->
        <div>
            <div class="section-title">4. Alokasi Penarikan per Kategori</div>
            <table class="report">
                <thead>
                    <tr>
                        <th style="text-align: left;">Kategori Penarikan</th>
                        <th>Tujuan</th>
                        <th>Trx</th>
                        <th style="text-align: right;">Total Tarik</th>
                        <th>Porsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapKategoriPenarikan as $rk)
                        <tr>
                            <td><strong>{{ $rk['nama_kategori'] }}</strong></td>
                            <td class="text-center">{{ $rk['jenis_tujuan'] }}</td>
                            <td class="text-center">{{ $rk['jumlah_transaksi'] }}x</td>
                            <td class="text-right font-bold" style="color: #e11d48;">
                                Rp {{ number_format($rk['total_nominal'], 0, ',', '.') }}
                            </td>
                            <td class="text-center">{{ $rk['persentase'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="color: #9ca3af;">Belum ada data penarikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-center">TOTAL</td>
                        <td class="text-center">
                            {{ number_format(collect($rekapKategoriPenarikan)->sum('jumlah_transaksi')) }}x</td>
                        <td class="text-right font-bold" style="color: #e11d48;">
                            Rp {{ number_format($totalNominalSemuaTarik, 0, ',', '.') }}
                        </td>
                        <td class="text-center">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- 4. LEMBAR BERITA ACARA UANG PECAHAN BRANKAS -->
    <div class="section-title">5. Rekonsiliasi & Kebutuhan Uang Pecahan Fisik (Hak Nasabah)</div>
    <div style="margin-bottom: 6px; font-size: 9px; color: #4b5563;">
        * Total Hak Bersih Nasabah: <strong>Rp
            {{ number_format($breakdownHakNasabah['nominal_bulat'], 0, ',', '.') }}</strong> (Potongan madrasah
        dibulatkan ke atas ke kelipatan Rp 100 terdekat sesuai ketersediaan pecahan uang fisik brankas).
    </div>
    <table class="report">
        <thead>
            <tr>
                <th style="width: 22%; text-align: left;">Pecahan Uang</th>
                <th style="width: 12%;">Jenis</th>
                <th style="width: 18%;">Rekomendasi Lembar/Koin</th>
                <th style="width: 20%; text-align: right;">Subtotal (Rp)</th>
                <th style="width: 16%;">Fisik Riil Brankas</th>
                <th style="width: 12%;">Paraf</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($denominasiPecahan as $dp)
                @php
                    $rekomLembar = $breakdownHakNasabah['counts'][$dp['key']] ?? 0;
                    $rekomSubtotal = $rekomLembar * $dp['nilai'];
                @endphp
                <tr>
                    <td><strong>{{ $dp['label'] }}</strong></td>
                    <td class="text-center">{{ $dp['tipe'] }}</td>
                    <td class="text-center font-bold">
                        {{ $rekomLembar > 0 ? $rekomLembar . ' ' . ($dp['tipe'] === 'Kertas' ? 'lbr' : 'koin') : '-' }}
                    </td>
                    <td class="text-right font-bold"
                        style="color: {{ $rekomSubtotal > 0 ? '#0f766e' : '#9ca3af' }};">
                        {{ $rekomSubtotal > 0 ? 'Rp ' . number_format($rekomSubtotal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-center" style="color: #9ca3af;">....................</td>
                    <td class="text-center" style="color: #9ca3af;">[ &nbsp;&nbsp;&nbsp;&nbsp; ]</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold">TOTAL KEBUTUHAN PECAHAN HAK NASABAH:</td>
                <td class="text-right font-bold" style="color: #0f766e;">Rp
                    {{ number_format($breakdownHakNasabah['nominal_bulat'], 0, ',', '.') }}</td>
                <td colspan="2" class="text-center" style="font-size: 8.5px;">Status: <strong>[ &nbsp; ] PAS
                        &nbsp; [ &nbsp; ] SELISIH</strong></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right font-bold">TOTAL SALDO KAS FISIK TABUNGAN BRANKAS:</td>
                <td class="text-right font-bold" style="color: #2563eb;">Rp
                    {{ number_format($totalKasFisik, 0, ',', '.') }}</td>
                <td colspan="2" class="text-center" style="font-size: 8.5px;">Potongan Madrasah: Rp
                    {{ number_format($totalPotonganMadrasah, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <div class="footer-sign">
        <div class="sign-box">
            <p>Mengetahui,<br><strong>Pengasuh Madrasah</strong></p>
            <div class="sign-space"></div>
            <p><strong>( ............................................ )</strong></p>
        </div>

        <div class="sign-box">
            <p>Dibuat & Diverifikasi Oleh,<br><strong>Bendahara Tabungan</strong></p>
            <div class="sign-space"></div>
            <p><strong>( {{ auth()->user()->name ?? 'Administrator' }} )</strong><br><span
                    style="font-size: 8.5px; color: #6b7280;">NIGM: {{ auth()->user()->ustadz->nigm ?? '-' }}</span>
            </p>
        </div>
    </div>

</body>

</html>
