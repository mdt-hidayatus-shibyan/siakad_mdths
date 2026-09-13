<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Tagihan Per Wali Murid - {{ $tahunPelajaran->nama_hijriyah }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        @media print {
            body {
                zoom: 82%;
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
            font-size: 10.5px;
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
            margin-bottom: 14px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 100px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            color: #334155;
            text-transform: uppercase;
        }

        .kop-right p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10.5px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
        }

        .meta-table td {
            padding: 3px 5px;
            color: #475569;
        }

        .meta-table .val {
            font-weight: bold;
            color: #0f172a;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-lunas {
            background: #dcfce7;
            color: #166534;
        }

        .status-belum {
            background: #fee2e2;
            color: #991b1b;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .summary-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .signature-area {
            text-align: center;
            font-size: 11px;
            page-break-inside: avoid;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .signature-box {
            width: 30%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sign-spacer {
            height: 55px;
            margin: 5px 0;
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
    @endphp

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                <path
                    d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z" />
            </svg>
            Cetak Laporan Rekapitulasi
        </button>
    </div>

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo', 'assets/LOGO MDT.png')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>LEMBAR REKAPITULASI TAGIHAN {{ $selectedMaster->nama_tagihan ?? 'Semua Tagihan KK' }}</h1>
            <p>MDT HIDAYATUS SHIBYAN &bull; Somorkoneng, Kwanyar, Bangkalan</p>
            <p>Tahun Pelajaran: <strong>{{ $tahunPelajaran->nama_hijriyah }}
                    ({{ $tahunPelajaran->nama_masehi }})</strong></p>
            <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <!-- META INFO -->
    <table class="meta-table">
        <tr>
            <td style="width: 100px;">Jenis Tagihan</td>
            <td class="val">: {{ $selectedMaster->nama_tagihan ?? 'Semua Tagihan KK' }} (Rp
                {{ number_format($selectedMaster->nominal ?? 0, 0, ',', '.') }})</td>
            <td style="width: 100px; text-align: right;">Zonasi Dusun :</td>
            <td class="val" style="width: 180px;">{{ $kampung ? $kampung->nama_kampung : 'Semua Kampung / Dusun' }}
            </td>
        </tr>
        <tr>
            <td>Dicetak Oleh</td>
            <td class="val">: {{ strtoupper($adminNama) }}</td>
            <td style="text-align: right;">Total Tagihan Terbit :</td>
            <td class="val">{{ $walis->count() }} Kepala Keluarga</td>
        </tr>
    </table>

    <!-- DATA TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">NO</th>
                <th style="width: 65px;">NO REG</th>
                <th>NAMA KEPALA KELUARGA</th>
                <th style="width: 110px;">DUSUN</th>
                <th>TANGGUNGAN MURID AKTIF</th>
                <th style="width: 90px; text-align: right;">NOMINAL</th>
                <th style="width: 75px; text-align: center;">STATUS</th>
                <th style="width: 130px;">NO. KWITANSI / TGL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totTarget = 0;
                $totLunas = 0;
                $countLunas = 0;
            @endphp
            @forelse ($walis as $wali)
                @php
                    $t = $wali->tagihanWaliMurids->first();
                    $nominal = $t ? $t->nominal_tagihan : $selectedMaster->nominal ?? 0;
                    $isLunas = $t && $t->status_bayar === 'Lunas';
                    $totTarget += $nominal;
                    if ($isLunas) {
                        $totLunas += $nominal;
                        $countLunas++;
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="font-family: monospace; font-weight: bold;">#{{ $wali->no_registrasi }}</td>
                    <td style="font-weight: bold;">{{ $wali->nama_kepala_keluarga }}</td>
                    <td>{{ $wali->kampung->nama_kampung ?? '-' }}</td>
                    <td>
                        @foreach ($wali->murids as $anak)
                            <span style="font-size: 8.5px;">&bull; {{ $anak->nama_lengkap }}
                                ({{ $anak->ruangans->first()?->nama_ruangan ?? '-' }})
                            </span><br>
                        @endforeach
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        Rp {{ number_format($nominal, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        <span class="status-badge {{ $isLunas ? 'status-lunas' : 'status-belum' }}">
                            {{ $isLunas ? 'Lunas' : ($t ? 'Belum Lunas' : 'Belum Terbit') }}
                        </span>
                    </td>
                    <td style="font-family: monospace; font-size: 8.5px;">
                        @if ($isLunas)
                            <strong>{{ $t->pembayaranTagihan->no_transaksi ?? '-' }}</strong><br>
                            <span
                                style="color: #64748b;">{{ $t->pembayaranTagihan ? date('d M Y', strtotime($t->pembayaranTagihan->tanggal_bayar)) : '' }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data Tagihan Terbit yang sesuai filter.
                    </td>
                </tr>
            @endforelse

            <!-- Total Row -->
            <tr
                style="background-color: #f8fafc; font-weight: 900; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                <td colspan="5" style="text-align: center; font-size: 10px;">TOTAL KESELURUHAN
                    ({{ $walis->count() }} KK)</td>
                <td style="text-align: right; font-size: 10.5px; color: #0f172a;">Rp
                    {{ number_format($totTarget, 0, ',', '.') }}</td>
                <td style="text-align: center; color: #16a34a;">{{ $countLunas }} LUNAS</td>
                <td style="text-align: right; font-size: 10px; color: #16a34a;">Rp
                    {{ number_format($totLunas, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- RINGKASAN REKAP -->
    <div class="summary-grid">
        <div class="summary-box">
            <div style="font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase;">Total Target
                Tagihan</div>
            <div style="font-size: 13px; font-weight: 900; color: #0f172a; margin-top: 2px;">
                Rp {{ number_format($totTarget, 0, ',', '.') }}
            </div>
        </div>
        <div class="summary-box">
            <div style="font-size: 9px; font-weight: bold; color: #166534; text-transform: uppercase;">Total Terkumpul /
                Lunas</div>
            <div style="font-size: 13px; font-weight: 900; color: #15803d; margin-top: 2px;">
                Rp {{ number_format($totLunas, 0, ',', '.') }} ({{ $countLunas }} KK)
            </div>
        </div>
        <div class="summary-box">
            <div style="font-size: 9px; font-weight: bold; color: #991b1b; text-transform: uppercase;">Sisa Tunggakan
                Belum Lunas</div>
            <div style="font-size: 13px; font-weight: 900; color: #dc2626; margin-top: 2px;">
                Rp {{ number_format(max(0, $totTarget - $totLunas), 0, ',', '.') }}
                ({{ $walis->count() - $countLunas }} KK)
            </div>
        </div>
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
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                    ) !!}
                @else
                    <div class="sign-spacer"></div>
                @endif
                <span class="sign-name">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'K.H. Pengasuh') }}
                </span>
            </div>

            <!-- Kolom Administrator -->
            <div class="signature-box">
                <span>Administrator / Petugas Keuangan</span>
                @if (!empty($administrator?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
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
