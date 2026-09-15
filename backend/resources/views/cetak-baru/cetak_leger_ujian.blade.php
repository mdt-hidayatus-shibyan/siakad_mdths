<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Leger - {{ $ruanganTerpilih->nama_ruangan }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* Pengaturan Kertas Print (Landscape) */
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
            vertical-align: middle;
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

        .w-3 {
            width: 3%;
            white-space: nowrap;
        }

        /* Merah jika nilai < 60 */
        .nilai-merah {
            color: #dc2626 !important;
            font-weight: 800;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Tombol Aksi (Sembunyi saat diprint) -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Cetak Leger
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
            <h1>LEGER REKAPITULASI NILAI MURID</h1>
            <p>Tahun Pelajaran: <strong>{{ $ruanganTerpilih->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ruanganTerpilih->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Agenda Ujian: <strong>{{ strtoupper($ujianTerpilih->nama_ujian ?? '-') }}</strong></p>
            <p>Kelas / Ruang: <strong>{{ strtoupper($ruanganTerpilih->nama_ruangan) }}</strong></p>
            <p>Wali Kelas: <strong>{{ $waliUstadz?->nama_lengkap ?? '-' }}</strong></p>
        </div>
    </div>

    <!-- TABEL LEGER NILAI -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="w-1">No</th>
                <th class="w-3">NISM</th>
                <th style="text-align: left; padding-left: 10px;">Nama Murid</th>

                <!-- Looping Kolom Mata Pelajaran -->
                @foreach ($kolomMapel as $mapel)
                    <th>
                        {{ strlen($mapel) > 15 ? substr($mapel, 0, 12) . '..' : $mapel }}
                    </th>
                @endforeach

                <th class="w-1">Total</th>
                <th class="w-1">Rata²</th>
                <th class="w-1">Rank</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dataLeger as $index => $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->murid->nism ?? '-' }}</td>
                    <td class="text-left" style="padding-left: 10px;">
                        <div style="font-weight: 800; font-size: 12px; margin-bottom: 2px;">
                            {{ $row->murid->nama_lengkap }}</div>
                    </td>

                    @foreach ($kolomMapel as $jadwalId => $mapel)
                        @php
                            $itemNilai = $row->nilai_per_mapel[$jadwalId] ?? null;
                            $angka = $itemNilai['nilai'] ?? null;
                        @endphp

                        <td class="{{ $angka !== null && $angka < 60 ? 'nilai-merah' : '' }}"
                            style="font-weight: 800; font-size: 12px;">
                            {{ $angka ?? '-' }}
                        </td>
                    @endforeach

                    <td style="font-weight: 800; font-size: 12px;">{{ $row->total }}</td>
                    <td style="font-weight: 800; font-size: 12px; color: #2563eb;">{{ $row->rata_rata }}</td>
                    <td style="font-weight: 800; font-size: 12px;">{{ $index + 1 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($kolomMapel) + 6 }}"
                        style="padding: 20px; text-align: center; color: #64748b;">
                        Belum ada data Murid atau nilai pada kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- KETERANGAN BAWAH -->
    <div style="font-size: 10px; color: #475569; margin-top: -10px; font-style: italic;">
        *Catatan: Nilai berwarna merah menunjukkan perolehan Murid di bawah batas KKM (60).
    </div>

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 35px; page-break-inside: avoid;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; text-align: center; vertical-align: top; padding-bottom: 0;">
                <p style="margin: 0; font-size: 12px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 12px; font-weight: bold;">Pengasuh</p>
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

            <td style="width: 50%; border: none; text-align: center; vertical-align: top; padding-bottom: 0;">
                <p style="margin: 0; font-size: 12px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 12px; font-weight: bold;">Wali Ruangan
                    {{ strtoupper($ruanganTerpilih->nama_ruangan) }}</p>
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
