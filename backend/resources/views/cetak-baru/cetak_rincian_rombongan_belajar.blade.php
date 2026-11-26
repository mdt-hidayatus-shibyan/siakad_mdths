<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Jumlah Murid Aktif - {{ $tahunPelajaran->nama_masehi ?? date('Y') }}</title>
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
                padding: 0 !important;
                margin: 0 !important;
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

            .section-block {
                page-break-inside: auto;
            }

            .data-table tr {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            background: #e2e8f0;
            color: #1e293b;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            background: #fff;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 18px auto 30px auto;
            padding: 1cm 1.2cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 3px;
            gap: 16px;
        }

        .garis-ganda {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 14px;
        }

        .kop-logo {
            max-width: 100%;
            max-height: 95px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
            min-width: 220px;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .kop-right p {
            margin: 2px 0 0 0;
            font-size: 10.5px;
            color: #475569;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .summary-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            background: #f8fafc;
        }

        .summary-box .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.4px;
        }

        .summary-box .value {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        .section-title {
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 14px 0 6px 0;
            padding-left: 8px;
            border-left: 4px solid #059669;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            text-transform: uppercase;
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 6px 8px;
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
            border: none;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-table td {
            border: none;
            padding: 0;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <!-- TOOLBAR CETAK (HILANG SAAT PRINT) -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-2.5 border-b shadow-md">
        <div
            class="px-3 py-1.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 rounded-lg text-xs font-bold">
            📊 RINCIAN JUMLAH MURID AKTIF
        </div>

        <div class="flex items-center gap-1 bg-slate-800 p-1 rounded-lg border border-slate-700">
            <a href="{{ route('rombongan-belajar.cetak-rincian', ['tahun_pelajaran_id' => $filterTp, 'mode' => 'semua']) }}"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition-all {{ $mode === 'semua' ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                Lengkap
            </a>
            <a href="{{ route('rombongan-belajar.cetak-rincian', ['tahun_pelajaran_id' => $filterTp, 'mode' => 'ruangan']) }}"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition-all {{ $mode === 'ruangan' ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                Per Ruangan
            </a>
            <a href="{{ route('rombongan-belajar.cetak-rincian', ['tahun_pelajaran_id' => $filterTp, 'mode' => 'level']) }}"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition-all {{ $mode === 'level' ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                Per Level
            </a>
        </div>

        <button onclick="window.print()"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition-all shadow-md cursor-pointer">
            ✕ Tutup
        </button>
    </div>

    <div class="sheet">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div style="width: 58%;">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
            </div>
            <div class="kop-right">
                <h1>
                    @if ($mode === 'ruangan')
                        RINCIAN MURID AKTIF PER RUANGAN
                    @elseif ($mode === 'level')
                        RINCIAN MURID AKTIF PER LEVEL
                    @else
                        RINCIAN JUMLAH MURID AKTIF
                    @endif
                </h1>
                <p>Tahun Pelajaran:
                    <strong>{{ $tahunPelajaran->nama_hijriyah ?? '-' }} H |
                        {{ $tahunPelajaran->nama_masehi ?? '-' }} M</strong>
                </p>
                <p>Dicetak: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</strong></p>
            </div>
        </div>
        <div class="garis-ganda"></div>

        <!-- RINGKASAN UTAMA -->
        <div class="summary-grid">
            <div class="summary-box">
                <div class="label">Total Murid Aktif</div>
                <div class="value" style="color: #059669;">{{ number_format($grandTotal->total) }}</div>
            </div>
            <div class="summary-box">
                <div class="label">Laki-Laki (L)</div>
                <div class="value" style="color: #0284c7;">{{ number_format($grandTotal->laki) }}</div>
            </div>
            <div class="summary-box">
                <div class="label">Perempuan (P)</div>
                <div class="value" style="color: #db2777;">{{ number_format($grandTotal->perempuan) }}</div>
            </div>
            <div class="summary-box">
                <div class="label">Ruangan / Level</div>
                <div class="value">{{ $grandTotal->jumlah_ruangan }} <span
                        style="font-size: 11px; font-weight: 600; color: #64748b;">/ {{ $grandTotal->jumlah_level }}
                        Level</span></div>
            </div>
        </div>

        <!-- TABEL 1: RINCIAN BERDASARKAN RUANGAN -->
        @if ($mode === 'semua' || $mode === 'ruangan')
            <div class="section-block">
                <div class="section-title">
                    {{ $mode === 'semua' ? 'A. ' : '' }}Rincian Jumlah Murid Aktif Berdasarkan Ruangan
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 32px; text-align: center;">No</th>
                            <th style="text-align: left;">Nama Ruangan</th>
                            <th style="text-align: left; width: 95px;">Level</th>
                            <th style="text-align: left; width: 105px;">Tingkat</th>
                            <th style="text-align: left;">Wali Ruangan</th>
                            <th style="width: 55px; text-align: center;">L</th>
                            <th style="width: 55px; text-align: center;">P</th>
                            <th style="width: 65px; text-align: center;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rincianRuangan as $row)
                            <tr>
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td><strong>{{ $row->nama_ruangan }}</strong></td>
                                <td>{{ $row->nama_level }}</td>
                                <td>{{ $row->nama_tingkat }}</td>
                                <td>{{ $row->wali_ruangan }}</td>
                                <td style="text-align: center;">{{ $row->laki }}</td>
                                <td style="text-align: center;">{{ $row->perempuan }}</td>
                                <td style="text-align: center; font-weight: 800; color: #059669;">{{ $row->total }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 14px; color: #64748b;">
                                    Belum ada data ruangan pada tahun pelajaran ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rincianRuangan->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right; text-transform: uppercase;">
                                    Total Keseluruhan ({{ $grandTotal->jumlah_ruangan }} Ruangan)
                                </td>
                                <td style="text-align: center;">{{ $grandTotal->laki }}</td>
                                <td style="text-align: center;">{{ $grandTotal->perempuan }}</td>
                                <td style="text-align: center;">{{ $grandTotal->total }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif

        <!-- TABEL 2: RINCIAN BERDASARKAN LEVEL -->
        @if ($mode === 'semua' || $mode === 'level')
            <div class="section-block">
                <div class="section-title">
                    {{ $mode === 'semua' ? 'B. ' : '' }}Rincian Jumlah Murid Aktif Berdasarkan Level
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 32px; text-align: center;">No</th>
                            <th style="text-align: left; width: 110px;">Level / Kelas</th>
                            <th style="text-align: left; width: 115px;">Tingkat</th>
                            <th style="width: 70px; text-align: center;">Jml Ruang</th>
                            <th style="text-align: left;">Daftar Ruangan</th>
                            <th style="width: 55px; text-align: center;">L</th>
                            <th style="width: 55px; text-align: center;">P</th>
                            <th style="width: 65px; text-align: center;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rincianLevel as $row)
                            <tr>
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td><strong>{{ $row->nama_level }}</strong></td>
                                <td>{{ $row->nama_tingkat }}</td>
                                <td style="text-align: center;">{{ $row->jumlah_ruangan }}</td>
                                <td>{{ $row->daftar_ruangan }}</td>
                                <td style="text-align: center;">{{ $row->laki }}</td>
                                <td style="text-align: center;">{{ $row->perempuan }}</td>
                                <td style="text-align: center; font-weight: 800; color: #059669;">{{ $row->total }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 14px; color: #64748b;">
                                    Belum ada data level pada tahun pelajaran ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rincianLevel->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; text-transform: uppercase;">
                                    Total Keseluruhan ({{ $grandTotal->jumlah_level }} Level)
                                </td>
                                <td style="text-align: center;">{{ $grandTotal->jumlah_ruangan }}</td>
                                <td></td>
                                <td style="text-align: center;">{{ $grandTotal->laki }}</td>
                                <td style="text-align: center;">{{ $grandTotal->perempuan }}</td>
                                <td style="text-align: center;">{{ $grandTotal->total }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif

        <!-- REKAP RINGKAS PER TINGKAT -->
        @if ($rincianTingkat->count() > 1)
            <div class="section-block">
                <div class="section-title">
                    {{ $mode === 'semua' ? 'C. ' : '' }}Ringkasan Berdasarkan Tingkat Madrasah
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 32px; text-align: center;">No</th>
                            <th style="text-align: left;">Tingkat Madrasah</th>
                            <th style="width: 80px; text-align: center;">Jml Level</th>
                            <th style="width: 85px; text-align: center;">Jml Ruangan</th>
                            <th style="width: 60px; text-align: center;">L</th>
                            <th style="width: 60px; text-align: center;">P</th>
                            <th style="width: 75px; text-align: center;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rincianTingkat as $row)
                            <tr>
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td><strong>{{ $row->nama_tingkat }}</strong></td>
                                <td style="text-align: center;">{{ $row->jumlah_level }}</td>
                                <td style="text-align: center;">{{ $row->jumlah_ruangan }}</td>
                                <td style="text-align: center;">{{ $row->laki }}</td>
                                <td style="text-align: center;">{{ $row->perempuan }}</td>
                                <td style="text-align: center; font-weight: 800; color: #059669;">{{ $row->total }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="text-align: right; text-transform: uppercase;">Total Madrasah</td>
                            <td style="text-align: center;">{{ $grandTotal->jumlah_level }}</td>
                            <td style="text-align: center;">{{ $grandTotal->jumlah_ruangan }}</td>
                            <td style="text-align: center;">{{ $grandTotal->laki }}</td>
                            <td style="text-align: center;">{{ $grandTotal->perempuan }}</td>
                            <td style="text-align: center;">{{ $grandTotal->total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- AREA TANDA TANGAN -->
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p style="margin: 0; font-size: 10.5px;">Mengetahui,</p>
                    <p style="margin: 2px 0 6px 0; font-size: 10.5px; font-weight: bold;">Pengasuh Madrasah</p>
                    <div style="min-height: 62px; display: flex; justify-content: center; align-items: center;">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                    </div>
                    <p
                        style="margin: 6px 0 0 0; font-size: 10.5px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                    </p>
                </td>

                <td style="width: 50%;">
                    <p style="margin: 0; font-size: 10.5px;">Somor Koneng,
                        {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 2px 0 6px 0; font-size: 10.5px; font-weight: bold;">Administrator Madrasah</p>
                    <div style="min-height: 62px; display: flex; justify-content: center; align-items: center;">
                        @if (!empty($administrator?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $administrator->id]),
                            ) !!}
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                    </div>
                    <p
                        style="margin: 6px 0 0 0; font-size: 10.5px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $administrator?->nama_lengkap ?? (auth()->user()->name ?? 'Administrator') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
