<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SURAT KEPUTUSAN KEPANITIAAN - {{ strtoupper($kegiatan->judul_lengkap) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 2px;
            font-family: 'Lexend', sans-serif;
        }

        .kop-kiri {
            width: 100%;
            text-align: left;
        }

        .kop-kiri img {
            max-width: 100%;
            height: auto;
            max-height: 125px;
            display: inline-block;
            object-fit: contain;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }

        .title-sk {
            text-align: center;
            margin: 15px 0;
        }

        .title-sk h3 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .title-sk p {
            font-size: 11pt;
            margin: 3px 0 0 0;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10pt;
        }

        .content-table th,
        .content-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        .content-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .ttd-box {
            margin-top: 25px;
            float: right;
            width: 250px;
            text-align: center;
            font-size: 10.5pt;
        }

        .ttd-space {
            height: 65px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    @php
        $pengasuh =
            $pengasuh ??
            (class_exists(\App\Models\Kepengurusan\Pengurus::class)
                ? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh')
                : null);
        $namaPengasuh = $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'ABDUL FATTAH');
    @endphp

    <!-- Tombol Cetak Browser -->
    <div class="no-print"
        style="position: fixed; top: 15px; right: 15px; background: #1e293b; padding: 8px 14px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 9999; display: flex; gap: 8px;">
        <button onclick="window.print()"
            style="background: #059669; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            style="background: #475569; color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            ✕ Tutup
        </button>
    </div>

    <!-- Halaman 1: SK Pengangkatan -->
    <div class="header-wrapper">
        <div class="kop-kiri">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
        </div>
    </div>
    <div class="garis-ganda"></div>

    <div class="title-sk">
        <h3>SURAT KEPUTUSAN PENGASUH MADRASAH</h3>
        <p>Nomor: {{ $kegiatan->no_sk_panitia ?: '.../MDT-HS/SK-IKH/' . date('Y') }}</p>
        <p style="font-weight: bold; margin-top: 6px;">TENTANG</p>
        <p style="font-weight: bold; text-transform: uppercase;">
            SUSUNAN PANITIA PELAKSANA {{ $kegiatan->judul_lengkap }}<br>
            TAHUN PELAJARAN {{ $kegiatan->tahunPelajaran?->nama_hijriyah }} H /
            {{ $kegiatan->tahunPelajaran?->nama_masehi }} M
        </p>
    </div>

    <div style="margin-top: 15px; text-align: justify;">
        <table style="width: 100%; border: none; font-size: 10.5pt;">
            <tr>
                <td style="width: 120px; vertical-align: top; font-weight: bold;">Menimbang</td>
                <td style="width: 15px; vertical-align: top;">:</td>
                <td style="vertical-align: top;">
                    Bahwa demi kelancaran, ketertiban, dan suksesnya penyelenggaraan kegiatan tahunan
                    {{ $kegiatan->judul_lengkap }} MDT Hidayatus Shibyan, maka dipandang perlu untuk membentuk dan
                    menetapkan Susunan Panitia Pelaksana.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold; padding-top: 8px;">Mengingat</td>
                <td style="vertical-align: top; padding-top: 8px;">:</td>
                <td style="vertical-align: top; padding-top: 8px;">
                    1. Anggaran Dasar dan Anggaran Rumah Tangga (AD/ART) MDT Hidayatus Shibyan.<br>
                    2. Kalender Pendidikan Madrasah Tahun Pelajaran {{ $kegiatan->tahunPelajaran?->nama_hijriyah }} H /
                    {{ $kegiatan->tahunPelajaran?->nama_masehi }} M.<br>
                    3. Hasil Musyawarah Dewan Pengasuh, Kepala Madrasah, dan Dewan Ustadz.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold; padding-top: 10px;" colspan="3" align="center">
                    MEMUTUSKAN:
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold; padding-top: 8px;">Menetapkan</td>
                <td style="vertical-align: top; padding-top: 8px;">:</td>
                <td style="vertical-align: top; padding-top: 8px;">
                    <strong>PERTAMA:</strong> Mengangkat dan mengesahkan nama-nama terlampir sebagai Susunan Panitia
                    Pelaksana {{ $kegiatan->judul_lengkap }}.<br><br>
                    <strong>KEDUA:</strong> Panitia bertugas merencanakan, melaksanakan, dan mempertanggungjawabkan
                    seluruh rangkaian kegiatan dan anggaran yang telah dialokasikan.<br><br>
                    <strong>KETIGA:</strong> Keputusan ini berlaku sejak tanggal ditetapkan hingga berakhirnya masa
                    pelaporan pertanggungjawaban (LPJ).
                </td>
            </tr>
        </table>
    </div>

    <div class="ttd-box">
        <p>Ditetapkan di: Somorkoneng<br>Pada tanggal:
            {{ $kegiatan->tanggal_penetapan_sk ? $kegiatan->tanggal_penetapan_sk->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
        </p>
        <p style="font-weight: bold;">Pengasuh MDT Hidayatus Shibyan,</p>
        <div class="ttd-space">
            @if (!empty($pengasuh?->id))
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                ) !!}
            @endif

        </div>
        <p style="font-weight: bold; text-decoration: underline;">{{ $namaPengasuh }}</p>
    </div>

    <div style="clear: both;"></div>

    <!-- Halaman 2: Lampiran Susunan Panitia -->
    <div class="page-break"></div>

    <div style="text-align: right; font-size: 9pt; margin-bottom: 10px;">
        <em>Lampiran SK Pengasuh MDT Hidayatus Shibyan<br>Nomor:
            {{ $kegiatan->no_sk_panitia ?: '.../MDT-HS/SK-IKH/' . date('Y') }}</em>
    </div>

    <div style="text-align: center; margin-bottom: 15px;">
        <h4 style="font-size: 11pt; font-weight: bold; margin: 0; text-transform: uppercase;">
            SUSUNAN PANITIA PELAKSANA {{ strtoupper($kegiatan->judul_lengkap) }}<br>
            TAHUN PELAJARAN {{ $kegiatan->tahunPelajaran?->nama_hijriyah }} H /
            {{ $kegiatan->tahunPelajaran?->nama_masehi }} M
        </h4>
    </div>

    <!-- 1. BPH Inti -->
    <h5 style="margin: 10px 0 5px 0; font-size: 10.5pt; font-weight: bold;">I. BADAN PENGURUS HARIAN (BPH INTI)</h5>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 200px;">Jabatan</th>
                <th>Nama Lengkap</th>
                <th style="width: 140px;">Unsur / Asal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bph as $idx => $person)
                <tr>
                    <td align="center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $person->jabatan }}</strong></td>
                    <td>{{ $person->nama_tampil }}</td>
                    <td>{{ $person->tipe_personel === 'ustadz' ? 'Dewan Ustadz' : ($person->unsur_asal ?: 'Eksternal') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" align="center">Belum ada data BPH Inti.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. Divisi-Divisi Pelaksana -->
    <h5 style="margin: 20px 0 5px 0; font-size: 10.5pt; font-weight: bold;">II. KOORDINATOR & ANGGOTA DIVISI</h5>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 180px;">Divisi / Bidang</th>
                <th>Personel / Anggota</th>
                <th style="width: 120px;">Pagu Alokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($divisis as $idx => $div)
                <tr>
                    <td align="center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $div->nama_divisi }}</strong><br>
                        <small style="color: #444;">{{ $div->deskripsi_tugas }}</small>
                    </td>
                    <td>
                        @if ($div->panitias->isEmpty())
                            <span style="color: #888; font-style: italic;">Belum ada anggota ditetapkan</span>
                        @else
                            <ol style="margin: 0; padding-left: 18px;">
                                @foreach ($div->panitias as $p)
                                    <li>
                                        <strong>{{ $p->nama_tampil }}</strong>
                                        <span style="font-size: 8.5pt; color: #555;">({{ $p->jabatan }})</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </td>
                    <td align="right" style="font-weight: bold;">
                        Rp {{ number_format($div->nominal_pagu, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" align="center">Belum ada divisi terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-box">
        <p style="font-weight: bold;">Pengasuh MDT Hidayatus Shibyan,</p>
        <div class="ttd-space">
            @if (!empty($pengasuh?->id))
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                ) !!}
            @endif

        </div>
        <p style="font-weight: bold; text-decoration: underline;">{{ $namaPengasuh }}</p>
    </div>
</body>

</html>
