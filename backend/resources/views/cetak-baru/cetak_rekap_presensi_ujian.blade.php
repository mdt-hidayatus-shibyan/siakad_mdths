<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi Ujian - {{ $ruangan->nama_ruangan }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        @media print {
            @page {
                margin: 1cm;
                size: A4 landscape;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 11px;
            background: #fff;
            zoom: 90%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 30%;
        }

        .kop-logo {
            max-width: 100%;
            height: auto;
            max-height: 100px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.5;
            width: 70%;
        }

        .kop-right h1 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: 900;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-right p {
            margin: 2px 0;
            font-size: 12px;
            color: #475569;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .data-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 800;
            text-align: center;
            padding: 8px 5px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .data-table td {
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            text-align: center;
        }

        .data-table td.text-left {
            text-align: left;
        }

        .w-1 {
            width: 1%;
            white-space: nowrap;
        }

        .w-3 {
            width: 3%;
            white-space: nowrap;
        }

        .badge-h {
            color: #059669;
            font-weight: 800;
        }

        .badge-s {
            color: #2563eb;
            font-weight: 800;
        }

        .badge-i {
            color: #d97706;
            font-weight: 800;
        }

        .badge-a {
            color: #dc2626;
            font-weight: 800;
        }

        .badge-d {
            color: #7c3aed;
            font-weight: 800;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Tombol Aksi -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Cetak Rekap Presensi
        </button>
        <button onclick="window.close()"
            style="padding: 8px 15px; background: #64748b; color: #fff; border: none; cursor: pointer; border-radius: 4px; margin-left: 5px; font-weight: bold;">
            Tutup Tab
        </button>
    </div>

    @php
        $pengasuh =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala');
        $waliUstadz = $ruangan->waliRuangan ?? null;
    @endphp

    <!-- KOP SURAT BERLOGO -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>REKAPITULASI PRESENSI PESERTA UJIAN</h1>
            <p>Tahun Pelajaran: <strong>{{ $ujian->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ujian->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Agenda Ujian: <strong>{{ strtoupper($ujian->nama_ujian) }}</strong></p>
            <p>Ruangan: <strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong> | Wali Kelas:
                <strong>{{ $waliUstadz?->nama_lengkap ?? '-' }}</strong>
            </p>
        </div>
    </div>

    <!-- TABEL REKAP MATRIKS -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="w-1">No</th>
                <th class="w-3">NISM</th>
                <th style="text-align: left; padding-left: 10px;">Nama Murid</th>

                @foreach ($jadwals as $jdw)
                    @php
                        $mapelNama = $jdw->mata_pelajaran_id
                            ? $jdw->mataPelajaran->nama_mapel ?? '-'
                            : $jdw->nama_mata_pelajaran_custom;
                        $tgl = \Carbon\Carbon::parse($jdw->tanggal_ujian)->format('d/m');
                    @endphp
                    <th>
                        <div style="font-size: 8px; color: #64748b;">{{ $tgl }}</div>
                        <div>{{ strlen($mapelNama) > 12 ? substr($mapelNama, 0, 10) . '..' : $mapelNama }}</div>
                    </th>
                @endforeach

                <th class="w-1" style="background: #ecfdf5; color: #065f46;">H</th>
                <th class="w-1" style="background: #eff6ff; color: #1e40af;">S</th>
                <th class="w-1" style="background: #fffbeb; color: #92400e;">I</th>
                <th class="w-1" style="background: #fef2f2; color: #991b1b;">A</th>
                <th class="w-1" style="background: #faf5ff; color: #6b21a8;">D</th>
                <th class="w-1">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dataRekap as $index => $row)
                <tr style="{{ $row->is_tidak_ikut ? 'background-color: #f8fafc;' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->murid->nism ?? '-' }}</td>
                    <td class="text-left" style="padding-left: 10px;">
                        <div style="font-weight: 800; font-size: 12px;">
                            {{ $row->murid->nama_lengkap }}
                            @if ($row->is_tidak_ikut)
                                <span
                                    style="font-size: 9px; color: #dc2626; font-weight: normal; margin-left: 4px;">(Tidak
                                    Ikut Ujian)</span>
                            @endif
                        </div>
                    </td>

                    @foreach ($jadwals as $jdw)
                        @php
                            $st = $row->detail_per_mapel[$jdw->id]['status'] ?? '-';
                        @endphp
                        <td>
                            @if ($st === 'Hadir')
                                <span class="badge-h">H</span>
                            @elseif ($st === 'Sakit')
                                <span class="badge-s">S</span>
                            @elseif ($st === 'Izin')
                                <span class="badge-i">I</span>
                            @elseif ($st === 'Alpha')
                                <span class="badge-a">A</span>
                            @elseif ($st === 'Dispensasi')
                                <span class="badge-d">D</span>
                            @elseif ($st === 'Tidak Ikut')
                                <span style="color: #94a3b8; font-weight: bold; font-size: 9px;">-</span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                    @endforeach

                    <td style="font-weight: 800; background: #f0fdf4;">{{ $row->hadir }}</td>
                    <td style="font-weight: 800; background: #f0f9ff;">{{ $row->sakit }}</td>
                    <td style="font-weight: 800; background: #fffdf0;">{{ $row->izin }}</td>
                    <td
                        style="font-weight: 800; background: #fef2f2; color: {{ $row->alpha > 0 ? '#dc2626' : 'inherit' }}">
                        {{ $row->alpha }}</td>
                    <td style="font-weight: 800; background: #faf5ff;">{{ $row->dispensasi }}</td>
                    <td
                        style="font-weight: 800; color: {{ $row->persentase_kehadiran >= 80 ? '#059669' : ($row->is_tidak_ikut ? '#94a3b8' : '#dc2626') }};">
                        {{ $row->is_tidak_ikut ? '-' : $row->persentase_kehadiran . '%' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($jadwals) + 9 }}" style="padding: 20px; text-align: center; color: #64748b;">
                        Belum ada murid atau presensi tercatat di kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 10px; color: #475569; margin-top: -10px; font-style: italic;">
        *Keterangan: (H) Hadir, (S) Sakit, (I) Izin, (A) Alpha, (D) Dispensasi
    </div>

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 35px; page-break-inside: avoid;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0; font-size: 12px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 12px; font-weight: bold;">Kepala Madrasah / Pengasuh</p>
                <div style="min-height: 65px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($pengasuh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                        ) !!}
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 12px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Kepala Belum Diatur') }}
                </p>
            </td>

            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0; font-size: 12px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 12px; font-weight: bold;">Wali Kelas / Ruangan</p>
                <div style="min-height: 65px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($waliUstadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $waliUstadz->id]),
                        ) !!}
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 12px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $waliUstadz?->nama_lengkap ?? 'Nama Wali Kelas Belum Diatur' }}
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
