<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Mengajar Ustadz - {{ $bulanTerpilih->nama_bulan }}</title>
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
            max-height: 90px;
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
            font-size: 15px;
            font-weight: 900;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-right p {
            margin: 2px 0;
            font-size: 11px;
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

        .w-1 {
            width: 1%;
            white-space: nowrap;
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
        $wakaKurikulum =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kurikulum') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris');
    @endphp

    <!-- KOP SURAT BERLOGO -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>REKAPITULASI KEHADIRAN MENGAJAR ASATIDZ</h1>
            <p>Tahun Pelajaran:
                <strong>{{ $bulanTerpilih->semester->tahunPelajaran->nama_hijriyah ?? ($bulanTerpilih->tahunPelajaran->nama_hijriyah ?? '-') }}
                    H</strong>
            </p>
            <p>Semester: <strong>{{ strtoupper($bulanTerpilih->semester->nama_semester ?? '-') }}</strong></p>
            <p>Bulan: <strong>{{ strtoupper($bulanTerpilih->nama_bulan) }} {{ $bulanTerpilih->tahun_hijriyah }}</strong>
            </p>
        </div>
    </div>

    <!-- TABEL REKAP KEHADIRAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="w-1">No</th>
                <th rowspan="2" style="text-align: left; padding-left: 10px; width: 250px;">Nama Asatidz</th>
                <th colspan="{{ count($ruangans) }}">Ruang Kelas / Jam Mengajar</th>
                <th rowspan="2" class="w-1">Total<br>Jam</th>
            </tr>
            <tr>
                @foreach ($ruangans as $ruangan)
                    <th
                        style="font-size: 8px; max-width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $ruangan->nama_ruangan }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($rekap as $row)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left" style="padding-left: 10px;">
                        <div style="font-weight: 800; font-size: 11px;">{{ $row['nama'] }}</div>
                    </td>
                    @foreach ($ruangans as $ruangan)
                        <td
                            style="{{ ($row['ruangan'][$ruangan->id] ?? 0) > 0 ? 'font-weight: bold; color: #000;' : 'color: #cbd5e1;' }}">
                            {{ ($row['ruangan'][$ruangan->id] ?? 0) > 0 ? $row['ruangan'][$ruangan->id] : '-' }}
                        </td>
                    @endforeach
                    <td style="font-weight: 800; font-size: 12px;">
                        {{ ($row['total'] ?? 0) > 0 ? $row['total'] : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- KETERANGAN BAWAH -->
    <div style="font-size: 10px; color: #475569; margin-top: -10px; font-style: italic;">
        * Angka pada tabel menunjukkan total jam kehadiran mengajar (Hadir/Badal) asatidz di ruangan tersebut selama
        bulan {{ $bulanTerpilih->nama_bulan }}.
    </div>

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 35px; page-break-inside: avoid;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; text-align: center; vertical-align: top; padding-bottom: 0;">
                <p style="margin: 0; font-size: 11px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Kepala Madrasah / Pengasuh</p>
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
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Kepala Belum Diatur') }}
                </p>
            </td>

            <td style="width: 50%; border: none; text-align: center; vertical-align: top; padding-bottom: 0;">
                <p style="margin: 0; font-size: 11px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Waka Kurikulum / Administrasi</p>
                <div style="min-height: 65px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($wakaKurikulum?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $wakaKurikulum->id]),
                        ) !!}
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $wakaKurikulum?->anggota?->nama_lengkap ?? ($wakaKurikulum?->nama ?? 'Nama Waka Belum Diatur') }}
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
