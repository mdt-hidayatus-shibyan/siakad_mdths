<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Registrasi SPMB - {{ $pendaftaran->nomor_pendaftaran }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #18181b;
            line-height: 1.4;
            font-size: 12px;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            border: 2px solid #047857;
            border-radius: 12px;
            padding: 20px;
            box-sizing: border-box;
        }

        .kop {
            text-align: center;
            border-bottom: 2px double #047857;
            padding-bottom: 12px;
            margin-bottom: 15px;
            position: relative;
        }

        .kop h1 {
            font-size: 18px;
            margin: 0;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kop h2 {
            font-size: 14px;
            margin: 3px 0 0 0;
            color: #1f2937;
        }

        .kop p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #4b5563;
        }

        .judul-kartu {
            text-align: center;
            margin: 12px 0;
            background: #ecfdf5;
            border: 1px dashed #059669;
            padding: 8px;
            border-radius: 6px;
        }

        .judul-kartu h3 {
            margin: 0;
            font-size: 14px;
            color: #047857;
            text-transform: uppercase;
        }

        .judul-kartu span {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
        }

        .qr-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .qr-box {
            text-align: center;
        }

        .no-reg {
            font-size: 18px;
            font-family: monospace;
            font-weight: bold;
            color: #047857;
            margin-top: 4px;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table-data td {
            padding: 5px 6px;
            vertical-align: top;
            font-size: 11px;
        }

        .table-data td.label {
            width: 32%;
            font-weight: bold;
            color: #4b5563;
        }

        .table-data td.separator {
            width: 3%;
            text-align: center;
        }

        .table-data td.val {
            width: 65%;
            font-weight: bold;
            color: #111827;
        }

        .section-header {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f3f4f6;
            padding: 4px 8px;
            margin: 10px 0 6px 0;
            border-left: 3px solid #059669;
        }

        .petunjuk {
            font-size: 10px;
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 8px 12px;
            border-radius: 6px;
            color: #92400e;
            margin-top: 15px;
        }

        .petunjuk ol {
            margin: 4px 0 0 0;
            padding-left: 18px;
        }

        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .sig-box {
            width: 40%;
        }

        .sig-space {
            height: 50px;
        }

        @media print {
            body {
                padding: 0;
            }

            .container {
                border: 1px solid #047857;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="text-align: center; padding: 12px; background: #f3f4f6; margin-bottom: 15px;">
        <button onclick="window.print()"
            style="padding: 8px 20px; background: #047857; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Print Kartu
        </button>
    </div>

    <div class="container">
        <!-- Kop Surat -->
        <div class="kop">
            <h1>MADRASAH DINIYAH TAKMILIYAH</h1>
            <h1>HIDAYATUS SHIBYAN</h1>
            <p>Somorkoneng, Kec. Kwanyar, Kab. Bangkalan, Jawa Timur • Kode Pos 69163</p>
        </div>

        <!-- Judul Kartu -->
        <div class="judul-kartu">
            <h3>TANDA BUKTI PENDAFTARAN MURID BARU (SPMB)</h3>
            <span>Tahun Pelajaran {{ $pendaftaran->tahunPelajaran->nama_hijriyah ?? '-' }}
                ({{ $pendaftaran->tahunPelajaran->nama_masehi ?? '-' }})</span>
        </div>

        <!-- QR Code & No Registrasi -->
        <div class="qr-section">
            <div>
                <span style="font-size: 10px; text-transform: uppercase; font-weight: bold; color: #6b7280;">Nomor
                    Pendaftaran:</span>
                <div class="no-reg">{{ $pendaftaran->nomor_pendaftaran }}</div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                    Tgl Daftar: {{ $pendaftaran->created_at->format('d/m/Y H:i') }} WIB • Status:
                    <strong>{{ $pendaftaran->status_pendaftaran }}</strong>
                </div>
            </div>
            <div class="qr-box">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(90)->generate($pendaftaran->nomor_pendaftaran) !!}
            </div>
        </div>

        <!-- Section 1: Data Murid -->
        <div class="section-header">A. DATA CALON MURID</div>
        <table class="table-data">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="label">Nama Panggilan</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->nama_panggilan ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td class="label">NIK Murid</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tanggal Lahir</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->tempat_lahir ?: '-' }},
                    {{ $pendaftaran->tanggal_lahir ? $pendaftaran->tanggal_lahir->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pilihan Kelas Masuk</td>
                <td class="separator">:</td>
                <td class="val" style="color: #047857;">{{ $pendaftaran->level->tingkat->nama_tingkat ?? '' }} -
                    {{ $pendaftaran->level->nama_level ?? '-' }}</td>
            </tr>
        </table>

        <!-- Section 2: Data Keluarga -->
        <div class="section-header">B. DATA KELUARGA & WALI</div>
        <table class="table-data">
            <tr>
                <td class="label">Nomor Kartu Keluarga (KK)</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->waliMurid->no_kk ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Kepala Keluarga</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->waliMurid->nama_kepala_keluarga ?? '-' }}
                    ({{ $pendaftaran->waliMurid->kepala_keluarga ?? 'Ayah' }})</td>
            </tr>
            <tr>
                <td class="label">Nama Ayah / Ibu Kandung</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->nama_ayah ?: '-' }} / {{ $pendaftaran->nama_ibu ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Zonasi Dusun / Kampung</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->waliMurid->kampung->nama_kampung ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nomor HP / WhatsApp</td>
                <td class="separator">:</td>
                <td class="val">{{ $pendaftaran->waliMurid->no_hp ?? '-' }}</td>
            </tr>
        </table>

        <!-- Petunjuk -->
        <div class="petunjuk">
            <strong>Petunjuk Verifikasi:</strong>
            <ol>
                <li>Bawa lembar bukti pendaftaran ini beserta fotokopi Kartu Keluarga (KK) ke Sekretariat Panitia SPMB
                    MDT Hidayatus Shibyan.</li>
                <li>Petugas akan memindai QR Code di atas untuk memvalidasi berkas pendaftaran dan menerbitkan Nomor
                    Induk Murid Madrasah (NISM).</li>
            </ol>
        </div>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <div class="sig-box">
                <p>Wali Murid / Pendaftar,</p>
                <div class="sig-space"></div>
                <p><strong>( {{ $pendaftaran->waliMurid->nama_kepala_keluarga ?? '..........................' }}
                        )</strong></p>
            </div>
            <div class="sig-box">
                <p>Bangkalan, {{ date('d F Y') }}<br>Panitia SPMB,</p>
                <div class="sig-space"></div>
                <p><strong>( Panitia SPMB MDT )</strong></p>
            </div>
        </div>
    </div>

</body>

</html>
