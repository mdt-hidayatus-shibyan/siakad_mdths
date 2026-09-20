<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Progres Input Nilai - {{ $ujianTerpilih->nama_ujian ?? 'Ujian' }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 0.8cm 1cm;
                size: A4 landscape;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            padding: 8px 12px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 10.5px;
            background: #fff;
            zoom: 92%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 25%;
        }

        .kop-logo {
            max-width: 100%;
            height: auto;
            max-height: 80px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
            width: 75%;
        }

        .kop-right h1 {
            margin: 0 0 3px 0;
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-right p {
            margin: 1px 0;
            font-size: 10.5px;
            color: #475569;
        }

        /* Metadata Banner */
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 14px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .info-card-item {
            display: flex;
            flex-direction: column;
        }

        .info-card-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .info-card-val {
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Summary Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }

        .stat-box {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 8px;
            text-align: center;
        }

        .stat-box.primary {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .stat-box.accent {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .stat-box.amber {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .stat-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .stat-val {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
        }

        /* Tabel Data */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }

        .data-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #334155;
            font-size: 9.5px;
            text-transform: uppercase;
            vertical-align: middle;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            text-align: center;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .data-table tbody tr.row-complete {
            background-color: #f0fdf4;
        }

        .data-table tfoot td {
            background: #e2e8f0;
            font-weight: 900;
            border: 1px solid #94a3b8;
            padding: 7px 6px;
            font-size: 10px;
            color: #0f172a;
        }

        /* Mini Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .badge-draft {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-process {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }

        .badge-empty {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .badge-mapel {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            margin: 1px;
            text-transform: uppercase;
        }

        /* Progress Bar in Table */
        .progress-bar-wrap {
            background: #e2e8f0;
            border-radius: 4px;
            height: 6px;
            width: 100%;
            overflow: hidden;
            margin-top: 2px;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .fill-success {
            background: #16a34a;
        }

        .fill-process {
            background: #2563eb;
        }

        .fill-draft {
            background: #d97706;
        }

        .fill-empty {
            background: #94a3b8;
        }
    </style>
</head>

<body onload="window.print()">

    <!-- Tombol Aksi (Sembunyi saat diprint) -->
    <div class="no-print"
        style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px;">
        <div style="font-weight: bold; font-size: 12px; color: #0f172a;">
            🖨️ Pratinjau Cetak: Rekapitulasi Progres Input Nilai Ujian
        </div>
        <div>
            <button onclick="window.print()"
                style="padding: 7px 16px; background: #16a34a; color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 11px; margin-right: 6px;">
                Cetak Dokumen (A4 Landscape)
            </button>
            <button onclick="window.close()"
                style="padding: 7px 14px; background: #64748b; color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 11px;">
                Tutup
            </button>
        </div>
    </div>

    <!-- KOP SURAT RESMI -->
    <div class="kop-surat">
        <div class="kop-left">
            <img class="kop-logo" src="{{ asset(getSetting('kop_logo', 'assets/LOGO MDT.png')) }}" alt="Logo Madrasah">
        </div>
        <div class="kop-right">
            <h1>{{ getSetting('nama_madrasah', 'MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN') }}</h1>
            <p><strong>NSDT:</strong> {{ getSetting('nsdt_madrasah', '121235280145') }} | <strong>Status:</strong>
                Terdaftar di Kemenag RI</p>
            <p>{{ getSetting('alamat_madrasah', 'Jl. Raya Pasir Putih No. 04 Somor Koneng') }} - Telp:
                {{ getSetting('nomor_telepon', '0852-3112-4411') }}</p>
            <p>Website: {{ getSetting('website', 'https://mdthidayatusshibyan.sch.id') }} | Email:
                {{ getSetting('email', 'info@mdthidayatusshibyan.sch.id') }}</p>
        </div>
    </div>

    <!-- METADATA AGENDA UJIAN -->
    <div class="info-card">
        <div class="info-card-item">
            <span class="info-card-label">Agenda Pelaksanaan</span>
            <span class="info-card-val">{{ $ujianTerpilih->nama_ujian }} ({{ $ujianTerpilih->tipe_ujian }})</span>
        </div>
        <div class="info-card-item">
            <span class="info-card-label">Semester & Tahun Pelajaran</span>
            <span
                class="info-card-val">{{ $ujianTerpilih->semester_relasi->nama_semester ?? ($ujianTerpilih->semester->nama_semester ?? 'Semester -') }}
                •
                {{ $ujianTerpilih->tahunPelajaran->nama_hijriyah ?? '-' }}|{{ $ujianTerpilih->tahunPelajaran->nama_masehi ?? '-' }}</span>
        </div>
        <div class="info-card-item">
            <span class="info-card-label">Waktu Rekap / Cetak</span>
            <span class="info-card-val">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y - H:i') }} WIB</span>
        </div>
    </div>

    @php
        $sumRuangan = $dataProgres->count();
        $sumMurid = $dataProgres->sum('jumlah_murid');
        $sumPeserta = $dataProgres->sum('jumlah_peserta');
        $sumTidakIkut = $dataProgres->sum('jumlah_tidak_ikut');
        $sumTarget = $dataProgres->sum('target_nilai');
        $sumDiinput = $dataProgres->sum('total_diinput');
        $sumDipublish = $dataProgres->sum('total_dipublish');
        $totalPersen = $sumTarget > 0 ? round(($sumDiinput / $sumTarget) * 100, 1) : 0;
        if ($totalPersen > 100) {
            $totalPersen = 100;
        }

        $ruanganSelesai = $dataProgres
            ->filter(fn($p) => $p->persentase == 100 && $p->total_diinput == $p->total_dipublish)
            ->count();
    @endphp

    <!-- STATS SUMMARY CARDS -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-title">Total Ruangan</div>
            <div class="stat-val">{{ $sumRuangan }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Murid Terdaftar</div>
            <div class="stat-val">{{ $sumMurid }} <span style="font-size: 9px; color: #64748b;">(Aktif)</span>
            </div>
        </div>
        <div class="stat-box accent">
            <div class="stat-title">Peserta Ujian</div>
            <div class="stat-val">{{ $sumPeserta }} <span
                    style="font-size: 8.5px; color: #2563eb;">({{ $sumTidakIkut }} Tidak Ikut)</span></div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Target Entri Nilai</div>
            <div class="stat-val">{{ number_format($sumTarget, 0, ',', '.') }}</div>
        </div>
        <div class="stat-box {{ $totalPersen == 100 ? 'primary' : 'amber' }}">
            <div class="stat-title">Nilai Terkumpul</div>
            <div class="stat-val">{{ number_format($sumDiinput, 0, ',', '.') }} <span
                    style="font-size: 9px; color: #64748b;">/ {{ number_format($sumDipublish, 0, ',', '.') }}
                    Pub</span></div>
        </div>
        <div class="stat-box {{ $totalPersen == 100 ? 'primary' : 'accent' }}">
            <div class="stat-title">Progres Global</div>
            <div class="stat-val" style="color: {{ $totalPersen == 100 ? '#16a34a' : '#2563eb' }};">
                {{ $totalPersen }}%
            </div>
        </div>
    </div>

    <!-- TABEL RINCIAN PROGRES PER RUANGAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">Ruangan</th>
                <th style="width: 8%;">Tingkat</th>
                <th style="width: 16%; text-align: left; padding-left: 6px;">Wali Ruangan</th>
                <th style="width: 5%;">Total Murid</th>
                <th style="width: 5%;">Peserta</th>
                <th style="width: 5%;">Tidak Ikut</th>
                <th style="width: 5%;">Jml Mapel</th>
                <th style="width: 7%;">Target Nilai</th>
                <th style="width: 7%;">Terkumpul</th>
                <th style="width: 6%;">Publish</th>
                <th style="width: 8%;">Progres</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 17%; text-align: left; padding-left: 6px;">Mapel Belum Lengkap</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dataProgres as $i => $progres)
                @php
                    $isComplete = $progres->persentase == 100 && $progres->total_diinput == $progres->total_dipublish;
                    $fillClass = 'fill-empty';
                    if ($progres->persentase == 100) {
                        $fillClass =
                            $progres->total_diinput == $progres->total_dipublish ? 'fill-success' : 'fill-draft';
                    } elseif ($progres->persentase > 0) {
                        $fillClass = 'fill-process';
                    }
                @endphp
                <tr class="{{ $isComplete ? 'row-complete' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight: 800; text-transform: uppercase;">
                        {{ $progres->ruangan->nama_ruangan }}
                    </td>
                    <td>{{ $progres->ruangan->level->nama_level ?? '-' }}</td>
                    <td style="text-align: left; padding-left: 6px;">
                        {{ $progres->ruangan->waliRuangan->nama ?? '-' }}
                    </td>
                    <td style="font-weight: 700;">{{ $progres->jumlah_murid }}</td>
                    <td style="font-weight: 700; color: #2563eb;">{{ $progres->jumlah_peserta }}</td>
                    <td>
                        @if ($progres->jumlah_tidak_ikut > 0)
                            <span style="color: #64748b; font-weight: bold;">{{ $progres->jumlah_tidak_ikut }}</span>
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td>{{ $progres->jumlah_mapel }}</td>
                    <td>{{ number_format($progres->target_nilai, 0, ',', '.') }}</td>
                    <td style="font-weight: 800;">{{ number_format($progres->total_diinput, 0, ',', '.') }}</td>
                    <td>{{ number_format($progres->total_dipublish, 0, ',', '.') }}</td>
                    <td>
                        <div style="font-weight: 800; font-size: 10px;">{{ $progres->persentase }}%</div>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill {{ $fillClass }}"
                                style="width: {{ $progres->persentase }}%;">
                            </div>
                        </div>
                    </td>
                    <td>
                        @if ($progres->target_nilai == 0)
                            <span class="badge badge-danger">Master Kosong</span>
                        @elseif ($progres->persentase == 0)
                            <span class="badge badge-empty">Belum Mulai</span>
                        @elseif ($progres->persentase < 100)
                            <span class="badge badge-process">Proses Input</span>
                        @else
                            @if ($progres->total_diinput == $progres->total_dipublish)
                                <span class="badge badge-success">✓ Selesai</span>
                            @else
                                <span class="badge badge-draft">Draft</span>
                            @endif
                        @endif
                    </td>
                    <td style="text-align: left; padding-left: 6px;">
                        @if (count($progres->mapel_kurang) > 0)
                            <div>
                                @foreach ($progres->mapel_kurang as $mk)
                                    <span class="badge-mapel">{{ $mk }}</span>
                                @endforeach
                            </div>
                        @else
                            <span style="color: #16a34a; font-weight: 700; font-size: 9px;">✓ Lengkap Seluruh
                                Mapel</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="padding: 20px; color: #64748b; text-align: center;">
                        Tidak ada data ruangan kelas yang ditemukan untuk agenda ujian ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; padding-right: 10px;">TOTAL KESELURUHAN:</td>
                <td>{{ $sumMurid }}</td>
                <td style="color: #1d4ed8;">{{ $sumPeserta }}</td>
                <td>{{ $sumTidakIkut }}</td>
                <td>-</td>
                <td>{{ number_format($sumTarget, 0, ',', '.') }}</td>
                <td>{{ number_format($sumDiinput, 0, ',', '.') }}</td>
                <td>{{ number_format($sumDipublish, 0, ',', '.') }}</td>
                <td>{{ $totalPersen }}%</td>
                <td colspan="2" style="text-align: left; padding-left: 8px;">
                    <span style="font-weight: 800; color: #16a34a;">{{ $ruanganSelesai }} / {{ $sumRuangan }}
                        Ruangan
                        Selesai</span>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- AREA TANDA TANGAN -->
    @php
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $adminObj = auth()->user()?->administrator ?? \App\Models\Administrator::getTandaTanganAdmin();
        $adminNama = $adminObj?->nama_lengkap ?? (auth()->user()?->name ?? 'Administrator');
    @endphp
    <table style="width: 100%; border: none; margin-top: 20px; page-break-inside: avoid;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0; font-size: 11px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Pengasuh</p>
                <div style="min-height: 55px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($pengasuh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                        ) !!}
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Kepala Belum Diatur') }}
                </p>
            </td>

            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0; font-size: 11px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Administrator</p>
                <div style="min-height: 55px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($adminObj?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $adminObj->id]),
                        ) !!}
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $adminNama }}
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
