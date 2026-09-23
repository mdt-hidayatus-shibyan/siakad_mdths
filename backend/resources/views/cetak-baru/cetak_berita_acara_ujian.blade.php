<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara - {{ $jadwal->mataPelajaran->nama_mapel ?? $jadwal->nama_mata_pelajaran_custom }} -
        {{ $ruangan->nama_ruangan }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 1.2cm;
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
            font-size: 12px;
            line-height: 1.6;
            background: #fff;
            zoom: 92%;
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

        .title-doc {
            text-align: center;
            margin: 20px 0;
        }

        .title-doc h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .table-data td {
            padding: 5px 8px;
            vertical-align: top;
        }

        .table-stat {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 20px 0;
        }

        .table-stat th,
        .table-stat td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
        }

        .table-stat th {
            background-color: #f8fafc;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
        }

        .catatan-box {
            border: 1px dashed #cbd5e1;
            background-color: #fcfdfe;
            border-radius: 6px;
            padding: 12px 15px;
            min-height: 80px;
            margin: 10px 0 20px 0;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Tombol Aksi -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Cetak Berita Acara
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

        $totalMurid = $murids->count();
        $totalL = $murids->where('jenis_kelamin', 'L')->count();
        $totalP = $murids->where('jenis_kelamin', 'P')->count();

        $hadir = 0;
        $sakit = 0;
        $izin = 0;
        $alpha = 0;
        $dispensasi = 0;
        $muridTidakHadir = [];

        foreach ($murids as $m) {
            $p = $presensiTersimpan->get($m->id);
            $st = $p ? $p->status : 'Hadir';
            if ($st === 'Hadir') {
                $hadir++;
            } elseif ($st === 'Sakit') {
                $sakit++;
                $muridTidakHadir[] = [
                    'nama' => $m->nama_lengkap,
                    'alasan' => 'Sakit ' . ($p->catatan ? "({$p->catatan})" : ''),
                ];
            } elseif ($st === 'Izin') {
                $izin++;
                $muridTidakHadir[] = [
                    'nama' => $m->nama_lengkap,
                    'alasan' => 'Izin ' . ($p->catatan ? "({$p->catatan})" : ''),
                ];
            } elseif ($st === 'Alpha') {
                $alpha++;
                $muridTidakHadir[] = ['nama' => $m->nama_lengkap, 'alasan' => 'Tanpa Keterangan'];
            } elseif ($st === 'Dispensasi') {
                $dispensasi++;
                $muridTidakHadir[] = ['nama' => $m->nama_lengkap, 'alasan' => 'Dispensasi'];
            }
        }

        $totalTidakHadir = $sakit + $izin + $alpha + $dispensasi;
        $catatanBeritaAcara =
            $presensiPengawas?->catatan_berita_acara ?:
            'Pelaksanaan ujian berjalan dengan tertib, aman, dan lancar tanpa kendala berarti.';
    @endphp

    <!-- KOP SURAT BERLOGO -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN</h1>
            <p>Tahun Pelajaran: <strong>{{ $ujian->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ujian->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Agenda Ujian: <strong>{{ strtoupper($ujian->nama_ujian) }}</strong></p>
            <p>Alamat: Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan</p>
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="title-doc">
        <h2>BERITA ACARA PELAKSANAAN UJIAN</h2>
    </div>

    <!-- NARASI PEMBUKA -->
    <p style="text-align: justify; margin-bottom: 12px;">
        Pada hari ini <strong>{{ \Carbon\Carbon::parse($jadwal->tanggal_ujian)->translatedFormat('l') }}</strong>,
        tanggal <strong>{{ \Carbon\Carbon::parse($jadwal->tanggal_ujian)->translatedFormat('d F Y') }}</strong>, di
        Madrasah Diniyah Takmiliyah Hidayatus Shibyan telah diselenggarakan pelaksanaan ujian dengan rincian kegiatan
        sebagai berikut:
    </p>

    <table class="table-data" style="margin-left: 10px;">
        <tr>
            <td style="width: 25%; font-weight: bold;">Agenda Ujian</td>
            <td style="width: 3%;">:</td>
            <td style="width: 72%; font-weight: bold;">{{ strtoupper($ujian->nama_ujian) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Mata Pelajaran</td>
            <td>:</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $mapelNama }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Ruangan / Kelas</td>
            <td>:</td>
            <td><strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong> (Level:
                {{ $ruangan->level->nama_level ?? '-' }})</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Waktu / Sesi</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Pengawas Ujian</td>
            <td>:</td>
            <td>
                <strong>{{ $pengawasAktif?->nama_lengkap ?? '............................................' }}</strong>
                @if ($presensiPengawas && $presensiPengawas->status === 'Digantikan')
                    <span style="font-size: 11px; color: #475569;">(Menggantikan Ustadz
                        {{ $jadwal->pengawas?->nama_lengkap ?? '-' }})</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- STATISTIK KEHADIRAN -->
    <p style="font-weight: bold; margin-bottom: 6px;">I. Rekapitulasi Kehadiran Peserta Ujian:</p>
    <table class="table-stat">
        <thead>
            <tr>
                <th rowspan="2" style="vertical-align: middle;">Jumlah Peserta Terdaftar</th>
                <th rowspan="2" style="vertical-align: middle;">Hadir</th>
                <th colspan="4">Tidak Hadir</th>
                <th rowspan="2" style="vertical-align: middle;">Total Tidak Hadir</th>
            </tr>
            <tr>
                <th style="font-size: 10px;">Sakit</th>
                <th style="font-size: 10px;">Izin</th>
                <th style="font-size: 10px;">Alpha</th>
                <th style="font-size: 10px;">Dispen</th>
            </tr>
        </thead>
        <tbody>
            <tr style="font-weight: bold;">
                <td>{{ $totalMurid }} (L: {{ $totalL }}, P: {{ $totalP }})</td>
                <td style="color: #059669; font-size: 13px;">{{ $hadir }}</td>
                <td>{{ $sakit }}</td>
                <td>{{ $izin }}</td>
                <td>{{ $alpha }}</td>
                <td>{{ $dispensasi }}</td>
                <td style="color: {{ $totalTidakHadir > 0 ? '#dc2626' : '#475569' }}; font-size: 13px;">
                    {{ $totalTidakHadir }}</td>
            </tr>
        </tbody>
    </table>

    @if (count($muridTidakHadir) > 0)
        <p style="font-size: 11px; margin-top: -10px; margin-bottom: 15px;">
            <strong>Murid yang Tidak Hadir:</strong>
            @foreach ($muridTidakHadir as $sth)
                {{ $sth['nama'] }} <em>({{ $sth['alasan'] }})</em>{{ !$loop->last ? ', ' : '.' }}
            @endforeach
        </p>
    @endif

    <!-- CATATAN KEJADIAN / BERITA ACARA -->
    <p style="font-weight: bold; margin-bottom: 6px;">II. Catatan Khusus Kejadian Selama Ujian Berlangsung:</p>
    <div class="catatan-box">
        {{ $catatanBeritaAcara }}
    </div>

    <p style="margin-top: 15px;">
        Demikian berita acara pelaksanaan ujian ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana
        mestinya.
    </p>

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 30px; page-break-inside: avoid;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
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
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Pengasuh Belum Diatur') }}
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
