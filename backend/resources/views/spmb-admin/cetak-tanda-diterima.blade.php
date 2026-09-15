<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Dokumen_SPMB_{{ $pendaftaran->nism_diberikan ?? $pendaftaran->nomor_pendaftaran }}_{{ Str::slug($pendaftaran->nama_lengkap) }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .page-sheet {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                min-height: auto !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            .page-sheet:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }

        body {
            font-family: 'Lexend', Times, serif;
            background: #e2e8f0;
            color: #000;
        }

        .page-sheet {
            background: white;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 6mm auto;
            padding: 1.2cm 1.4cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            line-height: 1.4;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
            width: 60%;
            text-align: left;
        }

        .kop-kiri img {
            max-width: 75%;
            height: auto;
            max-height: 120px;
            display: inline-block;
            object-fit: contain;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 18px;
        }

        .judul-surat {
            text-align: center;
            margin-bottom: 16px;
        }

        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 16px 0;
        }

        .tabel-data td {
            padding: 3.5px 6px;
            vertical-align: top;
            font-size: 11pt;
        }

        .tabel-data td.label {
            width: 32%;
            font-weight: bold;
        }

        .tabel-data td.separator {
            width: 3%;
            text-align: center;
        }

        .tabel-data td.val {
            width: 65%;
        }

        .nism-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 10px;
            font-family: monospace;
            font-size: 12pt;
            font-weight: bold;
        }

        /* Styling Khusus Halaman 2: Kwitansi */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin: 12px 0 16px 0;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 10.5pt;
        }

        .info-box {
            width: 48%;
        }

        .info-title {
            font-weight: bold;
            font-family: 'Lexend', sans-serif;
            color: #047857;
            font-size: 9.5pt;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .tabel-kwitansi {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10.5pt;
        }

        .tabel-kwitansi th {
            background: #f1f5f9;
            border: 1px solid #334155;
            padding: 6px 10px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .tabel-kwitansi td {
            border: 1px solid #334155;
            padding: 6px 10px;
            vertical-align: middle;
        }

        .terbilang-box {
            background: #f8fafc;
            border: 1px dashed #64748b;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 10.5pt;
            margin: 10px 0;
        }

        .notes-box {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 6px 10px;
            border-radius: 0 6px 6px 0;
            font-size: 9.5pt;
            margin: 10px 0;
            color: #1e40af;
            line-height: 1.35;
        }
    </style>
</head>

<body>

    <!-- Tombol Kontrol Cetak -->
    <div class="no-print fixed top-5 right-5 flex gap-3 z-50">
        <button onclick="window.close()"
            class="px-4 py-2 bg-slate-600 text-white rounded font-bold shadow hover:bg-slate-700 text-sm">
            Tutup
        </button>
        <button onclick="window.print()"
            class="px-4 py-2 bg-[#0F3D36] text-white rounded font-bold shadow hover:bg-[#0a2e28] text-sm flex items-center gap-2">
            <span>🖨️ Cetak Dokumen (3 Halaman A4)</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 1: SURAT KETERANGAN PENERIMAAN MURID BARU                         -->
    <!-- ========================================================================= -->
    <div class="page-sheet text-[11pt] text-justify">
        <div>
            <!-- Kop Surat Resmi -->
            <div class="header-wrapper">
                <div class="kop-kiri">
                    <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
                </div>
            </div>
            <div class="garis-ganda"></div>

            <!-- Judul Surat Keterangan -->
            <div class="judul-surat">
                <h2 class="text-[13pt] font-bold underline uppercase mb-0.5">
                    SURAT KETERANGAN PENERIMAAN MURID BARU
                </h2>
                <p class="font-bold text-[11pt]">Nomor:
                    {{ $pendaftaran->nomor_pendaftaran }}/MDT-HS/SPMB/{{ date('Y') }}</p>
            </div>

            <p class="mb-3">
                Yang bertanda tangan di bawah ini, Pimpinan Madrasah Diniyah Takmiliyah Hidayatus Shibyan Somorkoneng
                menerangkan bahwa:
            </p>

            <!-- Tabel Identitas Murid -->
            <table class="tabel-data">
                <tr>
                    <td class="label">Nama Lengkap Murid</td>
                    <td class="separator">:</td>
                    <td class="val"><strong class="uppercase text-[11.5pt]">{{ $pendaftaran->nama_lengkap }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="label">Nomor Induk Murid (NISM)</td>
                    <td class="separator">:</td>
                    <td class="val"><span class="nism-badge">{{ $pendaftaran->nism_diberikan ?? '-' }}</span></td>
                </tr>
                <tr>
                    <td class="label">Nomor Induk Kependudukan (NIK)</td>
                    <td class="separator">:</td>
                    <td class="val">{{ $pendaftaran->nik ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tempat, Tanggal Lahir</td>
                    <td class="separator">:</td>
                    <td class="val">
                        {{ $pendaftaran->tempat_lahir ?: '-' }},
                        {{ $pendaftaran->tanggal_lahir ? \Carbon\Carbon::parse($pendaftaran->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td>
                    <td class="separator">:</td>
                    <td class="val">{{ $pendaftaran->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Kepala Keluarga / Wali</td>
                    <td class="separator">:</td>
                    <td class="val uppercase">{{ $pendaftaran->waliMurid->nama_kepala_keluarga ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Kartu Keluarga (KK)</td>
                    <td class="separator">:</td>
                    <td class="val font-mono">{{ $pendaftaran->waliMurid->no_kk ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat / Dusun Zonasi</td>
                    <td class="separator">:</td>
                    <td class="val">{{ $pendaftaran->waliMurid->kampung->nama_kampung ?? '-' }}</td>
                </tr>
            </table>

            <p class="mb-3">
                Berdasarkan hasil verifikasi berkas administrasi dan registrasi Penerimaan Murid Baru (SPMB), calon
                murid tersebut di atas dinyatakan:
            </p>

            <!-- Kotak Status Diterima -->
            <div class="text-center my-4 p-3 bg-emerald-50 border border-emerald-300 rounded">
                <strong class="text-[13pt] text-emerald-800 tracking-wider block uppercase">
                    DITERIMA SEBAGAI MURID BARU
                </strong>
                <span class="text-[11pt] text-zinc-800 block mt-1">
                    Jenjang: <strong>{{ $pendaftaran->level->tingkat->nama_tingkat ?? '' }} -
                        {{ $pendaftaran->level->nama_level ?? '-' }}</strong>
                </span>
                <span class="text-[10pt] text-zinc-600 block">
                    Tahun Pelajaran: {{ $pendaftaran->tahunPelajaran->nama_hijriyah ?? '-' }}
                    ({{ $pendaftaran->tahunPelajaran->nama_masehi ?? '-' }})
                </span>
            </div>

            <p class="mb-6">
                Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
            </p>
        </div>

        <!-- Tanda Tangan Halaman 1 (Pengasuh & Kepala Administrator) -->
        <table class="w-full text-center mt-6 text-[10.5pt]" style="table-layout: fixed;">
            <tr>
                <!-- Kiri: Pengasuh Madrasah -->
                <td class="w-1/2 align-top" style="height: 55px;">
                    <p class="mb-1">Mengesahkan,</p>
                    <p class="font-bold">Pengasuh Madrasah</p>
                </td>

                <!-- Kanan: Kepala Administrator -->
                <td class="w-1/2 align-top" style="height: 55px;">
                    <p class="mb-0">Ditetapkan di : Bangkalan</p>
                    <p class="mb-1">Pada Tanggal :
                        {{ $pendaftaran->tanggal_verifikasi ? \Carbon\Carbon::parse($pendaftaran->tanggal_verifikasi)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                    </p>
                    <p class="font-bold">Kepala Administrator</p>
                </td>
            </tr>

            <tr>
                <!-- QR / TTD 1: Pengasuh -->
                <td class="align-bottom pb-2 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 65px;"></div>
                        @endif
                    </div>
                </td>

                <!-- QR / TTD 2: Kepala Administrator -->
                <td class="align-bottom pb-2 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($kepalaAdmin?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $kepalaAdmin->id]),
                            ) !!}
                        @else
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                url('/spmb/bukti/' . $pendaftaran->nomor_pendaftaran),
                            ) !!}
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <!-- Nama Terang Pengasuh -->
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $pengasuh->anggota->nama_lengkap ?? 'KH. Pengasuh' }}</p>
                </td>

                <!-- Nama Terang Kepala Administrator -->
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $kepalaAdmin->nama_lengkap ?? 'Kepala Administrator' }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 2: BUKTI KWITANSI PEMBAYARAN UANG SPMB & REGISTRASI               -->
    <!-- ========================================================================= -->
    @php
        $totalNominal = 0;
        if ($tagihanList->isNotEmpty()) {
            $totalNominal = $tagihanList->sum(function ($item) {
                return $item->nominal_tagihan ?? ($item->nominal ?? 0);
            });
        }
        if ($totalNominal == 0 && ($pendaftaran->nominal_biaya ?? 0) > 0) {
            $totalNominal = $pendaftaran->nominal_biaya;
        }
        $statusBayar = $pendaftaran->status_pembayaran ?? 'Lunas';
    @endphp

    <div class="page-sheet text-[11pt] text-justify">
        <div>
            <!-- Kop Surat Resmi -->
            <div class="header-wrapper">
                <div class="kop-kiri">
                    <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
                </div>
            </div>
            <div class="garis-ganda"></div>

            <!-- Judul Kwitansi -->
            <div class="judul-surat">
                <h2 class="text-[13pt] font-bold underline uppercase mb-0.5">
                    KWITANSI PEMBAYARAN BIAYA SPMB & REGISTRASI
                </h2>
                <p class="font-bold text-[11pt]">No. Kwitansi: KW-SPMB-{{ $pendaftaran->nomor_pendaftaran }}</p>
            </div>

            <!-- Kotak Info Pembayar & Transaksi -->
            <div class="info-section">
                <div class="info-box">
                    <div class="info-title">Diterima Dari (Wali / Murid):</div>
                    <table class="w-full text-[10pt]">
                        <tr>
                            <td style="width: 100px; color: #475569;">Nama Murid</td>
                            <td>: <strong>{{ strtoupper($pendaftaran->nama_lengkap) }}</strong></td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">NISM Resmi</td>
                            <td>: <span class="font-mono font-bold">{{ $pendaftaran->nism_diberikan ?? '-' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Jenjang Masuk</td>
                            <td>: {{ $pendaftaran->level->tingkat->nama_tingkat ?? '' }} -
                                {{ $pendaftaran->level->nama_level ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Wali / KK</td>
                            <td>: {{ strtoupper($pendaftaran->waliMurid->nama_kepala_keluarga ?? '-') }}
                                ({{ $pendaftaran->waliMurid->no_kk ?? '-' }})</td>
                        </tr>
                    </table>
                </div>

                <div class="info-box" style="text-align: right;">
                    <div class="info-title">Rincian Pembayaran:</div>
                    <table class="w-full text-[10pt]">
                        <tr>
                            <td style="color: #475569; text-align: right;">Tgl Transaksi:</td>
                            <td style="text-align: right; font-weight: bold; width: 140px;">
                                {{ $pendaftaran->tanggal_verifikasi ? \Carbon\Carbon::parse($pendaftaran->tanggal_verifikasi)->translatedFormat('d F Y') : date('d F Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #475569; text-align: right;">Metode Bayar:</td>
                            <td style="text-align: right; font-weight: bold;">TUNAI / KASIR</td>
                        </tr>
                        <tr>
                            <td style="color: #475569; text-align: right;">Status:</td>
                            <td style="text-align: right;">
                                <span
                                    class="inline-block px-2.5 py-0.5 rounded text-[9pt] font-black uppercase tracking-wider
                                    {{ $statusBayar == 'Lunas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-400' : ($statusBayar == 'Gratis' ? 'bg-blue-100 text-blue-800 border border-blue-400' : 'bg-amber-100 text-amber-800 border border-amber-400') }}">
                                    {{ $statusBayar == 'Lunas' ? '✔ LUNAS / SAH' : ($statusBayar == 'Gratis' ? '★ GRATIS / BEBAS BIAYA' : 'BELUM LUNAS') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Tabel Rincian Biaya SPMB -->
            <table class="tabel-kwitansi">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="width: 50%;">Uraian Pembayaran / Tagihan SPMB</th>
                        <th style="width: 25%; text-align: right;">Nominal Biaya</th>
                        <th style="width: 20%; text-align: center;">Status Pelunasan</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($tagihanList->isNotEmpty())
                        @foreach ($tagihanList as $idx => $tagihan)
                            @php
                                $nominalItem = $tagihan->nominal_tagihan ?? ($tagihan->nominal ?? 0);
                                $namaItem =
                                    $tagihan->nama_tagihan_spesifik ??
                                    ($tagihan->nama_tagihan ?? 'Biaya Pendaftaran & SPMB');
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                                <td style="font-weight: 600;">{{ $namaItem }}</td>
                                <td style="text-align: right; font-weight: bold; font-family: monospace;">
                                    Rp {{ number_format($nominalItem, 0, ',', '.') }}
                                </td>
                                <td
                                    style="text-align: center; font-weight: bold; color: {{ $statusBayar == 'Lunas' ? '#15803d' : '#0369a1' }};">
                                    {{ strtoupper($statusBayar) }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td style="text-align: center; font-weight: bold;">1</td>
                            <td style="font-weight: 600;">Biaya Pendaftaran & Registrasi Murid Baru (SPMB)</td>
                            <td style="text-align: right; font-weight: bold; font-family: monospace;">
                                Rp {{ number_format($totalNominal, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #15803d;">
                                {{ strtoupper($statusBayar) }}
                            </td>
                        </tr>
                    @endif
                    <!-- Total Row -->
                    <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #000;">
                        <td colspan="2" style="text-align: center; font-weight: 900; font-size: 11pt;">
                            JUMLAH TOTAL PEMBAYARAN
                        </td>
                        <td
                            style="text-align: right; font-weight: 900; font-size: 11pt; font-family: monospace; color: #047857;">
                            Rp {{ number_format($totalNominal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-weight: 900; color: #047857;">
                            {{ strtoupper($statusBayar) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Terbilang Box -->
            <div class="terbilang-box">
                <strong>Terbilang:</strong> <em># {{ terbilang($totalNominal) }} Rupiah #</em>
            </div>

            <!-- Notes Box -->
            <div class="notes-box">
                <strong>Catatan:</strong> Kwitansi ini diterbitkan sah oleh sistem MDT Hidayatus Shibyan sebagai bukti
                pembayaran administrasi dan registrasi Penerimaan Murid Baru (SPMB). Harap disimpan dengan baik sebagai
                bukti pelunasan.
            </div>
        </div>

        <!-- Tanda Tangan Kwitansi (Pengasuh & Kepala Administrator) -->
        <table class="w-full text-center mt-6 text-[10.5pt]" style="table-layout: fixed;">
            <tr>
                <!-- Kiri: Pengasuh Madrasah -->
                <td class="w-1/2 align-top" style="height: 55px;">
                    <p class="mb-1">Mengetahui,</p>
                    <p class="font-bold">Pengasuh Madrasah</p>
                </td>

                <!-- Kanan: Kepala Administrator / Kasir -->
                <td class="w-1/2 align-top" style="height: 55px;">
                    <p class="mb-0">Diterima di : Bangkalan</p>
                    <p class="mb-1">Pada Tanggal :
                        {{ $pendaftaran->tanggal_verifikasi ? \Carbon\Carbon::parse($pendaftaran->tanggal_verifikasi)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                    </p>
                    <p class="font-bold">Kepala Administrator / Penerima</p>
                </td>
            </tr>

            <tr>
                <!-- QR / TTD 1: Pengasuh -->
                <td class="align-bottom pb-2 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 65px;"></div>
                        @endif
                    </div>
                </td>

                <!-- QR / TTD 2: Kepala Administrator -->
                <td class="align-bottom pb-2 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($kepalaAdmin?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $kepalaAdmin->id]),
                            ) !!}
                        @else
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                url('/spmb/bukti/' . $pendaftaran->nomor_pendaftaran),
                            ) !!}
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <!-- Nama Terang Pengasuh -->
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $pengasuh->anggota->nama_lengkap ?? 'KH. Pengasuh' }}</p>
                </td>

                <!-- Nama Terang Kepala Administrator -->
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $kepalaAdmin->nama_lengkap ?? 'Kepala Administrator' }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 3: SURAT PERNYATAAN WALI MURID                                    -->
    <!-- ========================================================================= -->
    <div class="page-sheet text-[11pt] text-justify font-sans" style="font-family: 'Lexend', Times, serif;">
        <div>
            <!-- Kop Surat Resmi -->
            <div class="header-wrapper">
                <div class="kop-kiri">
                    <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
                </div>
            </div>
            <div class="garis-ganda"></div>

            <!-- Judul Surat Pernyataan -->
            <div class="judul-surat">
                <h2 class="text-[13pt] font-bold underline uppercase mb-0.5">
                    SURAT PERNYATAAN
                </h2>
                <p class="font-bold text-[10pt]">WALI MURID BARU TAHUN PELAJARAN
                    {{ $pendaftaran->tahunPelajaran->nama_hijriyah ?? date('Y') }}</p>
            </div>

            <p class="mb-4 text-[11.5pt]">
                Saya yang bertanda tangan di bawah ini:
            </p>

            <!-- Tabel Data Isian Wali Murid -->
            <table class="w-full text-[11.5pt] mb-8" style="border-collapse: collapse;">
                <tr>
                    <td style="width: 170px; padding: 6px 0;">Nama</td>
                    <td style="width: 25px; text-align: center;">:</td>
                    <td style="padding: 6px 0; font-weight: bold; text-transform: uppercase;">
                        {{ $pendaftaran->waliMurid->nama_kepala_keluarga ?? '............................................................' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">Alamat</td>
                    <td style="text-align: center;">:</td>
                    <td style="padding: 6px 0;">
                        {{ $pendaftaran->waliMurid->kampung->nama_kampung ?? ($pendaftaran->waliMurid->alamat_detail ?? '............................................................') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">No. HP</td>
                    <td style="text-align: center;">:</td>
                    <td style="padding: 6px 0; font-family: monospace; font-weight: 600;">
                        {{ $pendaftaran->waliMurid->no_hp ?? '............................................................' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; vertical-align: top;">Orang tua/wali dari</td>
                    <td style="text-align: center; vertical-align: top;">:</td>
                    <td style="padding: 6px 0; font-weight: bold; text-transform: uppercase;">
                        {{ $pendaftaran->nama_lengkap }} (NISM: {{ $pendaftaran->nism_diberikan ?? '-' }})
                    </td>
                </tr>
            </table>

            <p class="mb-3 text-[11.5pt] font-medium">
                Dengan ini menyatakan bahwa :
            </p>

            <ul class="list-disc list-outside pl-6 space-y-4 mb-8 text-[11.5pt] text-justify leading-relaxed">
                <li>
                    Memasrahkan sepenuhnya kepada Pengasuh Madrasah, Kepengurusan Madrasah dan Wali Ruangan yang
                    bersangkutan selama menempuh Pendidikan di Madrasah Diniyah Takmiliyah Hidayatus Shibyan Somorkoneng
                    Kwanyar Bangkalan
                </li>
                <li>
                    Mentaati peraturan perundang-undangan atau tata tertib Madrasah Diniyah Takmiliyah Hidayatus Shibyan
                    Somorkoneng Kwanyar Bangkalan
                </li>
            </ul>

            <p class="mb-10 text-[11.5pt] text-justify leading-relaxed">
                Demikian surat ini saya buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.
            </p>
        </div>

        <!-- Tanda Tangan Wali Murid (Kanan Bawah) -->
        <div class="flex justify-end pb-6">
            <div class="w-[280px] text-left text-[11pt]">
                <p class="mb-0.5">Bangkalan,
                    {{ $pendaftaran->tanggal_verifikasi ? \Carbon\Carbon::parse($pendaftaran->tanggal_verifikasi)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                </p>
                <p class="mb-0.5">Mengetahui,</p>
                <p class="font-bold mb-6">Hormat Saya</p>

                <!-- Box Materai -->
                <div
                    class="my-4 py-2.5 px-4 border border-dashed border-zinc-300 text-zinc-400 text-[10pt] font-medium inline-block text-center rounded bg-zinc-50/50">
                    Materai 10.000
                </div>

                <div class="mt-4 pt-1">
                    <p class="border-b border-black w-52 mb-1"></p>
                    <p class="font-bold uppercase text-[11pt]">
                        ( {{ $pendaftaran->waliMurid->nama_kepala_keluarga ?? 'Wali Murid' }} )
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
