<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pembayaran Ujian{{ $selectedKode ? ' - ' . $selectedKode : '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                margin: 1cm;
                size: A4 portrait;
            }

            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .sheet {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                min-height: auto !important;
            }

            .data-table tr {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            background: #e2e8f0;
            color: #1e293b;
            font-size: 10.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            background: #fff;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 18px auto 30px auto;
            padding: 1cm 1.1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 3px;
            gap: 14px;
        }

        .garis-ganda {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 14px;
        }

        .kop-logo {
            max-width: 100%;
            max-height: 92px;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        .kop-right p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        .kpi-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 7px 9px;
            background: #f8fafc;
        }

        .kpi-box .label {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        .kpi-box .value {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin: 14px 0 6px 0;
            padding-left: 8px;
            border-left: 4px solid #059669;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 7px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
        }

        .data-table td {
            padding: 5.5px 7px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .data-table tfoot td {
            background: #e2e8f0;
            font-weight: 800;
            color: #0f172a;
            border: 1px solid #94a3b8;
        }

        .signature-table {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-3 border-b shadow-md">
        <div
            class="px-3 py-1.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 rounded-lg text-xs font-bold">
            📊 REKAP PEMBAYARAN UJIAN{{ $selectedKode ? ' (' . $selectedKode . ')' : ' (SEMUA KODE)' }}
        </div>
        <button onclick="window.print()"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition-all shadow-md cursor-pointer">
            ✕ Tutup
        </button>
    </div>

    <div class="sheet">
        <div class="kop-surat">
            <div style="width: 56%;">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
            </div>
            <div class="kop-right">
                <h1>LAPORAN PEMBAYARAN UJIAN</h1>
                <p>Kode Tagihan: <strong>{{ $selectedKode ? $selectedKode . ' (' . ($kodeTerpilihObj->nama_tagihan ?? '') . ')' : 'Semua Kode Tagihan' }}</strong></p>
                <p>Tahun Pelajaran: <strong>{{ $tahunPelajaran->nama_hijriyah ?? '-' }} H | {{ $tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
                @if ($ruanganTerpilih)
                    <p>Ruangan: <strong>{{ $ruanganTerpilih->nama_ruangan }}</strong></p>
                @endif
            </div>
        </div>
        <div class="garis-ganda"></div>

        <!-- KPI RINGKAS -->
        <div class="kpi-grid">
            <div class="kpi-box">
                <div class="label">Target Tagihan</div>
                <div class="value">Rp {{ number_format($summary->target_nominal, 0, ',', '.') }}</div>
                <div style="font-size: 8.5px; color: #64748b;">{{ $summary->total_count }} Tagihan</div>
            </div>
            <div class="kpi-box">
                <div class="label">Total Terbayar</div>
                <div class="value" style="color: #059669;">Rp {{ number_format($summary->terbayar_nominal, 0, ',', '.') }}</div>
                <div style="font-size: 8.5px; color: #059669;">{{ $summary->lunas_count }} Lunas ({{ $summary->persen_nominal }}%)</div>
            </div>
            <div class="kpi-box">
                <div class="label">Sisa Tunggakan</div>
                <div class="value" style="color: #e11d48;">Rp {{ number_format($summary->tunggakan_nominal, 0, ',', '.') }}</div>
                <div style="font-size: 8.5px; color: #e11d48;">{{ $summary->belum_lunas_count }} Belum Lunas</div>
            </div>
            <div class="kpi-box">
                <div class="label">Capaian Pelunasan</div>
                <div class="value">{{ $summary->persen_lunas }}%</div>
                <div style="font-size: 8.5px; color: #64748b;">{{ $summary->lunas_count }} / {{ $summary->total_count }} Murid</div>
            </div>
        </div>

        <!-- TABEL 1: REKAP PER KODE TAGIHAN -->
        <div class="section-title">A. Rekapitulasi Berdasarkan Kode Tagihan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28px; text-align: center;">No</th>
                    <th style="text-align: left; width: 75px;">Kode</th>
                    <th style="text-align: left;">Nama Tagihan</th>
                    <th style="width: 48px; text-align: center;">Total</th>
                    <th style="width: 48px; text-align: center;">Lunas</th>
                    <th style="width: 48px; text-align: center;">Belum</th>
                    <th style="width: 85px; text-align: right;">Target (Rp)</th>
                    <th style="width: 85px; text-align: right;">Terbayar (Rp)</th>
                    <th style="width: 85px; text-align: right;">Sisa (Rp)</th>
                    <th style="width: 46px; text-align: center;">%</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowsKode = $selectedKode ? $rekapPerKode->where('kode_tagihan', $selectedKode) : $rekapPerKode;
                @endphp
                @foreach ($rowsKode as $rk)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td><strong>{{ $rk->kode_tagihan }}</strong></td>
                        <td>{{ $rk->nama_tagihan }}</td>
                        <td style="text-align: center;">{{ $rk->total_count }}</td>
                        <td style="text-align: center; color: #059669; font-weight: 700;">{{ $rk->lunas_count }}</td>
                        <td style="text-align: center; color: #e11d48; font-weight: 700;">{{ $rk->belum_lunas_count }}</td>
                        <td style="text-align: right;">{{ number_format($rk->target_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #059669; font-weight: 700;">{{ number_format($rk->terbayar_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #e11d48;">{{ number_format($rk->tunggakan_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $rk->persen_lunas }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; text-transform: uppercase;">Total</td>
                    <td style="text-align: center;">{{ $summary->total_count }}</td>
                    <td style="text-align: center;">{{ $summary->lunas_count }}</td>
                    <td style="text-align: center;">{{ $summary->belum_lunas_count }}</td>
                    <td style="text-align: right;">{{ number_format($summary->target_nominal, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($summary->terbayar_nominal, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($summary->tunggakan_nominal, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $summary->persen_lunas }}%</td>
                </tr>
            </tfoot>
        </table>

        <!-- TABEL 2: RINCIAN PER RUANGAN -->
        <div class="section-title">B. Rincian Pembayaran Per Ruangan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28px; text-align: center;">No</th>
                    <th style="text-align: left;">Ruangan</th>
                    <th style="text-align: left; width: 75px;">Level</th>
                    <th style="width: 48px; text-align: center;">Total</th>
                    <th style="width: 48px; text-align: center;">Lunas</th>
                    <th style="width: 48px; text-align: center;">Belum</th>
                    <th style="width: 85px; text-align: right;">Target (Rp)</th>
                    <th style="width: 85px; text-align: right;">Terbayar (Rp)</th>
                    <th style="width: 85px; text-align: right;">Sisa (Rp)</th>
                    <th style="width: 46px; text-align: center;">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekapPerRuangan as $row)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td><strong>{{ $row->nama_ruangan }}</strong></td>
                        <td>{{ $row->nama_level }}</td>
                        <td style="text-align: center;">{{ $row->total_count }}</td>
                        <td style="text-align: center; color: #059669; font-weight: 700;">{{ $row->lunas_count }}</td>
                        <td style="text-align: center; color: #e11d48; font-weight: 700;">{{ $row->belum_lunas_count }}</td>
                        <td style="text-align: right;">{{ number_format($row->target_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #059669; font-weight: 700;">{{ number_format($row->terbayar_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #e11d48;">{{ number_format($row->tunggakan_nominal, 0, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $row->persen_lunas }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 12px; color: #64748b;">Tidak ada data tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TABEL 3: RINCIAN PER LEVEL -->
        @if ($rekapPerLevel->isNotEmpty())
            <div class="section-title">C. Rincian Pembayaran Per Level</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 28px; text-align: center;">No</th>
                        <th style="text-align: left;">Level</th>
                        <th style="text-align: left;">Tingkat</th>
                        <th style="width: 55px; text-align: center;">Ruang</th>
                        <th style="width: 48px; text-align: center;">Total</th>
                        <th style="width: 48px; text-align: center;">Lunas</th>
                        <th style="width: 48px; text-align: center;">Belum</th>
                        <th style="width: 85px; text-align: right;">Terbayar (Rp)</th>
                        <th style="width: 85px; text-align: right;">Sisa (Rp)</th>
                        <th style="width: 46px; text-align: center;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekapPerLevel as $lv)
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}</td>
                            <td><strong>{{ $lv->nama_level }}</strong></td>
                            <td>{{ $lv->nama_tingkat }}</td>
                            <td style="text-align: center;">{{ $lv->jumlah_ruangan }}</td>
                            <td style="text-align: center;">{{ $lv->total_count }}</td>
                            <td style="text-align: center; color: #059669; font-weight: 700;">{{ $lv->lunas_count }}</td>
                            <td style="text-align: center; color: #e11d48; font-weight: 700;">{{ $lv->belum_lunas_count }}</td>
                            <td style="text-align: right; color: #059669; font-weight: 700;">{{ number_format($lv->terbayar_nominal, 0, ',', '.') }}</td>
                            <td style="text-align: right; color: #e11d48;">{{ number_format($lv->tunggakan_nominal, 0, ',', '.') }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $lv->persen_lunas }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- TANDA TANGAN -->
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p style="margin: 0; font-size: 10px;">Mengetahui,</p>
                    <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">Pengasuh Madrasah</p>
                    <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(58)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 48px;"></div>
                        @endif
                    </div>
                    <p style="margin: 6px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                    </p>
                </td>
                <td style="width: 50%;">
                    <p style="margin: 0; font-size: 10px;">Somor Koneng, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">Bendahara Madrasah</p>
                    <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                        @if (!empty($bendahara?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(58)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $bendahara->id]),
                            ) !!}
                        @else
                            <div style="height: 48px;"></div>
                        @endif
                    </div>
                    <p style="margin: 6px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $bendahara?->anggota?->nama_lengkap ?? ($bendahara?->nama ?? (auth()->user()->name ?? 'Bendahara')) }}
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
