<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Kehadiran - {{ $ruanganTerpilih->nama_ruangan }}
        {{ $jadwalTerpilih ? '(' . ($jadwalTerpilih->mataPelajaran->nama_mapel ?? 'Jadwal') . ' - ' . $jadwalTerpilih->hari . ' Jam ' . $jadwalTerpilih->jam_ke . ')' : '' }}
    </title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        /* Pengaturan Kertas Print */
        @media print {
            @page {
                margin: 1cm;
                size: A4 portrait;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Base Body */
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

        /* Kop Surat */
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

        /* Tabel Data */
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
        }

        .data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            text-align: center;
        }

        .data-table td.text-left {
            text-align: left;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .w-1 {
            width: 1%;
            white-space: nowrap;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mt-4 {
            margin-top: 20px;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Tombol Aksi (Sembunyi saat diprint) -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Cetak Laporan
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
        $waliUstadz = $ruanganTerpilih->waliRuangan ?? null;
    @endphp

    <!-- KOP SURAT BERLOGO -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>{{ $jadwalTerpilih ? 'REKAPITULASI PRESENSI JADWAL PELAJARAN' : 'REKAPITULASI PRESENSI MURID' }}</h1>
            <p>Tahun Pelajaran: <strong>{{ $semesterTerpilih->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $semesterTerpilih->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Semester: <strong>{{ strtoupper($semesterTerpilih->nama_semester) }}</strong></p>
            <p>Ruangan: <strong>{{ strtoupper($ruanganTerpilih->nama_ruangan) }}</strong></p>
            @if ($jadwalTerpilih)
                <p>Mata Pelajaran: <strong>{{ strtoupper($jadwalTerpilih->mataPelajaran->nama_mapel ?? '-') }}</strong>
                </p>
                <p>Jadwal Pertemuan: <strong>{{ $jadwalTerpilih->hari }} (Jam
                        Ke-{{ $jadwalTerpilih->jam_ke }})</strong></p>
                <p>Guru Pengampu: <strong>{{ $jadwalTerpilih->ustadz->nama ?? '-' }}</strong></p>
            @else
                <p>Jadwal Pelajaran: <strong>SEMUA JADWAL PELAJARAN (AKUMULASI)</strong></p>
            @endif
            <p>Bulan:
                <strong>{{ $bulanTerpilih ? 'Bulan ' . $bulanTerpilih->nama_bulan : 'Satu Semester Penuh' }}</strong>
            </p>
        </div>
    </div>

    <!-- TABEL REKAP KEHADIRAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="w-1">No</th>
                <th rowspan="2" class="w-3">NISM</th>
                <th rowspan="2" style="text-align: left; padding-left: 10px;">Nama Murid</th>
                <th rowspan="2" class="w-1">L/P</th>
                <th rowspan="2" class="w-1">Sesi</th>
                <th colspan="5">Kehadiran</th>
                <th rowspan="2" class="w-1">% Hadir</th>
                <th rowspan="2" class="w-1">Poin</th>
            </tr>
            <tr>
                <th class="w-2">H</th>
                <th class="w-2">S</th>
                <th class="w-2">I</th>
                <th class="w-2">A</th>
                <th class="w-2">D</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($murids as $murid)
                @php
                    $data = $rekap[$murid->id] ?? [
                        'H' => 0,
                        'S' => 0,
                        'I' => 0,
                        'A' => 0,
                        'D' => 0,
                        'total_pertemuan' => 0,
                        'persen_hadir' => 0,
                        'akumulasi_poin' => 0,
                    ];
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $murid->nism ?? '-' }}</td>
                    <td class="text-left" style="padding-left: 10px;">
                        <div style="font-weight: 800; font-size: 12px; margin-bottom: 2px;">{{ $murid->nama_lengkap }}
                        </div>
                    </td>
                    <td>{{ $murid->jenis_kelamin }}</td>
                    <td>{{ $data['total_pertemuan'] > 0 ? $data['total_pertemuan'] : '-' }}</td>
                    <td>{{ $data['H'] > 0 ? $data['H'] : '-' }}</td>
                    <td>{{ $data['S'] > 0 ? $data['S'] : '-' }}</td>
                    <td>{{ $data['I'] > 0 ? $data['I'] : '-' }}</td>
                    <td>{{ $data['A'] > 0 ? $data['A'] : '-' }}</td>
                    <td>{{ $data['D'] > 0 ? $data['D'] : '-' }}</td>
                    <td style="font-weight: 700;">
                        {{ $data['total_pertemuan'] > 0 ? $data['persen_hadir'] . '%' : '-' }}
                    </td>
                    <td
                        style="font-weight: 800; font-size: 12px; {{ ($data['akumulasi_poin'] ?? 0) >= 5 ? 'color: #dc2626;' : '' }}">
                        {{ ($data['akumulasi_poin'] ?? 0) > 0 ? number_format($data['akumulasi_poin'], 1) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="padding: 20px; text-align: center; color: #64748b;">
                        Tidak ada data murid di ruangan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- KETERANGAN BAWAH -->
    <div style="font-size: 10px; color: #475569; margin-top: -10px; font-style: italic;">
        *Keterangan: (H) Hadir, (S) Sakit, (I) Izin, (A) Alpha, (D) Dispensasi
    </div>

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 35px; page-break-inside: avoid;">
        <tr style="border: none;">
            <!-- Kolom TTD Kiri (Kepala Madrasah / Pengasuh) -->
            <td style="width: 50%; border: none; text-align: center; vertical-align: top; padding-bottom: 0;">
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

            <!-- Kolom TTD Kanan (Guru Pengampu / Wali Ruangan) -->
            <td style="width: 50%; border: none; text-align: center; vertical-align: top; padding-bottom: 0;">
                <p style="margin: 0; font-size: 12px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 12px; font-weight: bold;">
                    {{ $jadwalTerpilih ? 'Guru / Ustadz Pengampu' : 'Wali Kelas / Ruangan' }}
                </p>
                <div style="min-height: 65px; display: flex; justify-content: center; align-items: center;">
                    @if ($jadwalTerpilih && !empty($jadwalTerpilih->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $jadwalTerpilih->ustadz->id]),
                        ) !!}
                    @elseif (!empty($waliUstadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $waliUstadz->id]),
                        ) !!}
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 12px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $jadwalTerpilih ? $jadwalTerpilih->ustadz->nama ?? 'Nama Guru Belum Diatur' : $waliUstadz?->nama_lengkap ?? 'Nama Wali Kelas Belum Diatur' }}
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
