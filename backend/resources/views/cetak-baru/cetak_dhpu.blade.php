<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHPU - {{ $jadwal->mataPelajaran->nama_mapel ?? $jadwal->nama_mata_pelajaran_custom }} -
        {{ $ruangan->nama_ruangan }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
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

        /* Detail Ujian Box */
        .info-grid {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 3px 0;
            vertical-align: top;
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

        .w-1 {
            width: 1%;
            white-space: nowrap;
        }

        .w-3 {
            width: 3%;
            white-space: nowrap;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Tombol Aksi (Sembunyi saat diprint) -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Cetak DHPU
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
        $mapelNama = $jadwal->mata_pelajaran_id
            ? $jadwal->mataPelajaran->nama_mapel ?? '-'
            : $jadwal->nama_mata_pelajaran_custom;
        $pengawasAktif =
            $presensiPengawas && $presensiPengawas->status === 'Digantikan' && $presensiPengawas->ustadzPengganti
                ? $presensiPengawas->ustadzPengganti
                : $jadwal->pengawas;
    @endphp

    <!-- KOP SURAT BERLOGO -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>DAFTAR HADIR PESERTA UJIAN (DHPU)</h1>
            <p>Tahun Pelajaran: <strong>{{ $ujian->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ujian->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Agenda Ujian: <strong>{{ strtoupper($ujian->nama_ujian) }}</strong></p>
            <p>Ruangan: <strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong> | Tingkat / Kelas:
                <strong>{{ $ruangan->level->nama_level ?? '-' }}</strong></p>
        </div>
    </div>

    <!-- INFORMASI MATA PELAJARAN & WAKTU -->
    <table class="info-grid">
        <tr>
            <td style="width: 15%; font-weight: bold;">Mata Pelajaran</td>
            <td style="width: 2%;">:</td>
            <td style="width: 38%; font-weight: 800; text-transform: uppercase;">{{ $mapelNama }}</td>
            <td style="width: 15%; font-weight: bold;">Hari / Tanggal</td>
            <td style="width: 2%;">:</td>
            <td style="width: 28%;">{{ \Carbon\Carbon::parse($jadwal->tanggal_ujian)->translatedFormat('l, d F Y') }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Pengawas Ujian</td>
            <td>:</td>
            <td style="font-weight: 800;">
                {{ $pengawasAktif?->nama_lengkap ?? '............................................' }}
                @if ($presensiPengawas && $presensiPengawas->status === 'Digantikan')
                    <span style="font-size: 9px; color: #475569;">(Badal)</span>
                @endif
            </td>
            <td style="font-weight: bold;">Waktu / Sesi</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i') }} WIB</td>
        </tr>
    </table>

    <!-- TABEL DAFTAR HADIR -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="w-1">No</th>
                <th class="w-3">NISM</th>
                <th style="text-align: left; padding-left: 10px;">Nama Murid</th>
                <th class="w-1">L/P</th>
                @if ($mode === 'kosong')
                    <th style="width: 30%;">Tanda Tangan</th>
                    <th style="width: 15%;">Ket</th>
                @else
                    <th style="width: 18%;">Status Kehadiran</th>
                    <th style="width: 20%;">Catatan</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($murids as $index => $murid)
                @php
                    $p = $presensiTersimpan->get($murid->id);
                    $status = $p ? $p->status : 'Hadir';
                    $catatan = $p ? $p->catatan : '';
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $murid->nism ?? '-' }}</td>
                    <td class="text-left" style="padding-left: 10px;">
                        <div style="font-weight: 800; font-size: 12px; margin-bottom: 2px;">{{ $murid->nama_lengkap }}
                        </div>
                    </td>
                    <td>{{ $murid->jenis_kelamin }}</td>

                    @if ($mode === 'kosong')
                        <!-- TANDA TANGAN ZIGZAG -->
                        <td
                            style="text-align: {{ $loop->iteration % 2 == 1 ? 'left' : 'right' }}; padding-left: 15px; padding-right: 15px; height: 32px; vertical-align: bottom;">
                            <span
                                style="font-size: 9px; color: #94a3b8; margin-right: 5px;">{{ $loop->iteration }}.</span>
                            ....................
                        </td>
                        <td></td>
                    @else
                        <!-- STATUS TERISI -->
                        <td>
                            @if ($status === 'Hadir')
                                <strong style="color: #059669;">HADIR</strong>
                            @elseif ($status === 'Sakit')
                                <strong style="color: #2563eb;">SAKIT</strong>
                            @elseif ($status === 'Izin')
                                <strong style="color: #d97706;">IZIN</strong>
                            @elseif ($status === 'Alpha')
                                <strong style="color: #dc2626;">ALPHA</strong>
                            @elseif ($status === 'Dispensasi')
                                <strong style="color: #7c3aed;">DISPENSASI</strong>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td class="text-left">{{ $catatan ?: '-' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #64748b;">
                        Belum ada murid terdaftar di kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 30px; page-break-inside: avoid;">
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
                    {{ \Carbon\Carbon::parse($jadwal->tanggal_ujian)->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 12px; font-weight: bold;">Pengawas Ujian</p>
                <div style="min-height: 65px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($pengawasAktif?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $pengawasAktif->id]),
                        ) !!}
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 12px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengawasAktif?->nama_lengkap ?? '............................................' }}
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
