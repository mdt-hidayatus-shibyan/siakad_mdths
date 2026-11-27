<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai Per Mapel - {{ $levelTerpilih->nama_level ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.9cm;
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
            color: #0f172a;
            font-size: 10.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            background: #fff;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 16px auto 28px auto;
            padding: 0.9cm 1.1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 3px;
            gap: 14px;
        }

        .garis-ganda {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 12px;
        }

        .kop-logo {
            max-width: 100%;
            max-height: 86px;
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
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 14px 0 6px 0;
            padding-left: 8px;
            border-left: 4px solid #059669;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6.5px 6px;
            border: 1px solid #475569;
            font-size: 10px;
        }

        .data-table td {
            padding: 6px 6px;
            border: 1px solid #475569;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
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
            📊 REKAP NILAI PER MAPEL — {{ strtoupper($levelTerpilih->nama_level ?? '-') }}
            {{ $ruanganTerpilih ? '(' . $ruanganTerpilih->nama_ruangan . ')' : '(SEMUA RUANGAN)' }}
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
            <div style="width: 52%;">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
            </div>
            <div class="kop-right">
                <h1>REKAPITULASI NILAI PER MATA PELAJARAN</h1>
                <p>Agenda Ujian: <strong>{{ strtoupper($ujianTerpilih->nama_ujian ?? '-') }}</strong></p>
                <p>Tahun Pelajaran:
                    <strong>{{ $tahunPelajaran->nama_hijriyah ?? '-' }} H |
                        {{ $tahunPelajaran->nama_masehi ?? '-' }} M</strong>
                </p>
                <p>Level: <strong>{{ $levelTerpilih->nama_level ?? '-' }}</strong> |
                    Ruangan: <strong>{{ $ruanganTerpilih ? $ruanganTerpilih->nama_ruangan : 'Semua Ruangan (' . $targetRuangans->count() . ' Ruang)' }}</strong>
                </p>
                @if ($jadwalTerpilih)
                    <p>Mata Pelajaran: <strong>{{ strtoupper($jadwalTerpilih->nama_mapel) }}</strong></p>
                @endif
            </div>
        </div>
        <div class="garis-ganda"></div>

        <!-- KPI -->
        <div class="kpi-grid">
            <div class="kpi-box">
                <div class="label">Rata-Rata Nilai</div>
                <div class="value" style="color: #059669;">{{ number_format($summary->rata_rata, 2) }}</div>
            </div>
            <div class="kpi-box">
                <div class="label">Ketuntasan (KKM &ge; {{ $kkm }})</div>
                <div class="value">{{ $summary->persen_tuntas }}%</div>
                <div style="font-size: 8px; color: #64748b;">{{ $summary->tuntas_count }} Tuntas / {{ $summary->belum_tuntas_count }} Remidi</div>
            </div>
            <div class="kpi-box">
                <div class="label">Tertinggi / Terendah</div>
                <div class="value">
                    {{ $summary->tertinggi !== null ? $summary->tertinggi : '-' }} /
                    {{ $summary->terendah !== null ? $summary->terendah : '-' }}
                </div>
            </div>
            <div class="kpi-box">
                <div class="label">Kelengkapan Nilai</div>
                <div class="value">{{ $summary->persen_progres }}%</div>
                <div style="font-size: 8px; color: #64748b;">{{ $summary->total_nilai_masuk }}/{{ $summary->target_nilai }} ({{ $summary->jumlah_peserta }} Peserta)</div>
            </div>
        </div>

        <!-- TABEL REKAP PER MAPEL -->
        <div class="section-title">A. Rekapitulasi Statistik Per Mata Pelajaran Jadwal Ujian</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 26px; text-align: center;">No</th>
                    <th style="text-align: left;">Mata Pelajaran</th>
                    <th style="width: 60px; text-align: center;">Dinilai</th>
                    <th style="width: 58px; text-align: center;">Rata²</th>
                    <th style="width: 52px; text-align: center;">Max</th>
                    <th style="width: 52px; text-align: center;">Min</th>
                    <th style="width: 52px; text-align: center;">Tuntas</th>
                    <th style="width: 52px; text-align: center;">Remidi</th>
                    <th style="width: 54px; text-align: center;">% Tuntas</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowsPrint = $jadwalTerpilih ? $rekapPerMapel->where('jadwal_id', $jadwalTerpilih->id) : $rekapPerMapel;
                @endphp
                @foreach ($rowsPrint as $row)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td><strong>{{ $row->nama_mapel }}</strong></td>
                        <td style="text-align: center;">{{ $row->jumlah_dinilai }}/{{ $row->jumlah_peserta }}</td>
                        <td style="text-align: center; font-weight: 800; color: {{ $row->rata_rata >= $kkm ? '#059669' : '#dc2626' }};">
                            {{ $row->jumlah_dinilai > 0 ? number_format($row->rata_rata, 2) : '-' }}
                        </td>
                        <td style="text-align: center;">{{ $row->tertinggi !== null ? $row->tertinggi : '-' }}</td>
                        <td style="text-align: center;">{{ $row->terendah !== null ? $row->terendah : '-' }}</td>
                        <td style="text-align: center; color: #059669; font-weight: 700;">{{ $row->tuntas_count }}</td>
                        <td style="text-align: center; color: #dc2626; font-weight: 700;">{{ $row->belum_tuntas_count }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $row->persen_tuntas }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- MATRIKS ANTAR RUANGAN (BILA MODE PER LEVEL > 1 RUANGAN & SEMUA MAPEL) -->
        @if (!$ruanganTerpilih && $targetRuangans->count() > 1 && !$jadwalTerpilih)
            <div class="section-title">B. Perbandingan Rata-Rata Nilai Antar Ruangan</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Mata Pelajaran</th>
                        @foreach ($targetRuangans as $rng)
                            <th style="text-align: center;">{{ $rng->nama_ruangan }}</th>
                        @endforeach
                        <th style="text-align: center; background: #ecfdf5;">Rata² Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekapPerMapel as $row)
                        <tr>
                            <td><strong>{{ $row->nama_mapel }}</strong></td>
                            @foreach ($targetRuangans as $rng)
                                @php
                                    $cell = $row->per_ruangan[$rng->id] ?? null;
                                    $val = ($cell && $cell->rata_rata !== null) ? min(100, max(0, $cell->rata_rata)) : null;
                                    $barHex = $val !== null ? ($val >= 80 ? '#059669' : ($val >= $kkm ? '#0284c7' : '#dc2626')) : '#cbd5e1';
                                @endphp
                                <td style="text-align: center;">
                                    @if ($val !== null)
                                        <div style="font-weight: 800; color: {{ $val >= $kkm ? '#0f172a' : '#dc2626' }};">
                                            {{ number_format($cell->rata_rata, 2) }}
                                        </div>
                                        <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; position: relative; margin-top: 3px;">
                                            <div style="width: {{ round($val, 1) }}%; height: 100%; background: {{ $barHex }}; border-radius: 3px;"></div>
                                            <div style="position: absolute; top: 0; bottom: 0; left: {{ $kkm }}%; width: 1px; background: #dc2626;"></div>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach
                            @php
                                $lvlVal = $row->jumlah_dinilai > 0 ? min(100, max(0, $row->rata_rata)) : null;
                                $lvlHex = $lvlVal !== null ? ($lvlVal >= 80 ? '#059669' : ($lvlVal >= $kkm ? '#0284c7' : '#dc2626')) : '#cbd5e1';
                            @endphp
                            <td style="text-align: center; font-weight: 800; background: #ecfdf5;">
                                @if ($lvlVal !== null)
                                    <div style="color: {{ $lvlVal >= $kkm ? '#059669' : '#dc2626' }};">
                                        {{ number_format($row->rata_rata, 2) }}
                                    </div>
                                    <div style="width: 100%; height: 5px; background: #d1fae5; border-radius: 3px; overflow: hidden; position: relative; margin-top: 3px;">
                                        <div style="width: {{ round($lvlVal, 1) }}%; height: 100%; background: {{ $lvlHex }}; border-radius: 3px;"></div>
                                        <div style="position: absolute; top: 0; bottom: 0; left: {{ $kkm }}%; width: 1px; background: #dc2626;"></div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- DETAIL DAFTAR NILAI MURID (BILA 1 MAPEL DIPILIH) -->
        @if ($jadwalTerpilih)
            <div class="section-title">B. Daftar Perolehan Nilai Murid — {{ $jadwalTerpilih->nama_mapel }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">Rank</th>
                        <th style="width: 72px; text-align: center;">NISM</th>
                        <th style="text-align: left;">Nama Lengkap Murid</th>
                        <th style="width: 72px; text-align: center;">Ruangan</th>
                        <th style="width: 52px; text-align: center;">Nilai</th>
                        <th style="width: 48px; text-align: center;">Predikat</th>
                        <th style="width: 75px; text-align: center;">Ketuntasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detailNilaiMurid as $idx => $item)
                        <tr>
                            <td style="text-align: center;">{{ $item->nilai !== null ? $idx + 1 : '-' }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $item->murid->nism ?? '-' }}</td>
                            <td style="font-weight: 700; text-transform: uppercase;">{{ $item->murid->nama_lengkap ?? '-' }}</td>
                            <td style="text-align: center;">{{ $item->ruangan->nama_ruangan ?? '-' }}</td>
                            <td style="text-align: center; font-weight: 800; color: {{ $item->nilai !== null && !$item->tuntas ? '#dc2626' : '#0f172a' }};">
                                {{ $item->nilai !== null ? $item->nilai : '-' }}
                            </td>
                            <td style="text-align: center; font-weight: 700;">{{ $item->predikat }}</td>
                            <td style="text-align: center; font-weight: 700; color: {{ $item->nilai === null ? '#64748b' : ($item->tuntas ? '#059669' : '#dc2626') }};">
                                {{ $item->nilai === null ? 'Belum Dinilai' : ($item->tuntas ? 'Tuntas' : 'Remidi') }}
                            </td>
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
                    <div style="min-height: 58px; display: flex; justify-content: center; align-items: center;">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(56)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 48px;"></div>
                        @endif
                    </div>
                    <p style="margin: 5px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                    </p>
                </td>
                <td style="width: 50%;">
                    @php
                        $penandatanganKanan = $ruanganTerpilih?->waliRuangan;
                    @endphp
                    <p style="margin: 0; font-size: 10px;">Somor Koneng, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">
                        {{ $ruanganTerpilih ? 'Wali Ruangan ' . strtoupper($ruanganTerpilih->nama_ruangan) : 'Kepala Bidang Pendidikan' }}
                    </p>
                    <div style="min-height: 58px; display: flex; justify-content: center; align-items: center;">
                        @if ($ruanganTerpilih && !empty($penandatanganKanan?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(56)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $penandatanganKanan->id]),
                            ) !!}
                        @elseif (!$ruanganTerpilih && !empty($kabid?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(56)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kabid->id]),
                            ) !!}
                        @else
                            <div style="height: 48px;"></div>
                        @endif
                    </div>
                    <p style="margin: 5px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        @if ($ruanganTerpilih)
                            {{ $penandatanganKanan?->nama_lengkap ?? 'Wali Ruangan' }}
                        @else
                            {{ $kabid?->anggota?->nama_lengkap ?? ($kabid?->nama ?? 'Kepala Bidang') }}
                        @endif
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
