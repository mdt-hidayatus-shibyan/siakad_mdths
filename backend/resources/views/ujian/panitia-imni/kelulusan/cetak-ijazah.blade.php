<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ijazah_IMNI_{{ Str::slug($peserta->murid?->nama_lengkap ?? 'murid') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: white;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .ijazah-wrapper {
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 21cm !important;
                height: 29.7cm !important;
                max-height: 29.7cm !important;
                overflow: hidden !important;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #000000;
        }

        /* WADAH UTAMA KERTAS */
        .ijazah-wrapper {
            background-color: white;
            width: 21cm;
            height: 29.7cm;
            max-height: 29.7cm;
            margin: 0 auto;
            position: relative;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;

            background-image: url('{{ asset('storage/ijazah-wrapper.png') }}');
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;

            padding: 2.75cm 2.7cm 1.6cm 2.7cm;
        }

        /* HEADER & LOGO */
        .header-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .logo-box img {
            height: 75px;
            width: auto;
            object-fit: contain;
        }

        .arabic-image img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }

        /* JUDUL */
        .title-section {
            text-align: center;
            margin-bottom: 14px;
        }

        .title-ijazah {
            font-size: 18pt;
            font-weight: 900;
            letter-spacing: 4px;
            margin-bottom: 2px;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .title-tingkat {
            font-size: 11pt;
            font-weight: 700;
            color: #000000;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .title-madrasah {
            font-size: 12.5pt;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .title-alamat {
            font-size: 9.5pt;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .title-tahun {
            font-size: 9.5pt;
            font-weight: 600;
            color: #374151;
        }

        /* KONTEN */
        .content-section {
            font-size: 10.5pt;
            line-height: 1.45;
            text-align: justify;
        }

        .tabel-data {
            width: 95%;
            margin: 8px auto 10px auto;
        }

        .tabel-data td {
            padding-bottom: 3px;
            vertical-align: bottom;
        }

        .tabel-data .label-col {
            width: 170px;
            font-weight: 500;
        }

        .tabel-data .separator-col {
            width: 20px;
            text-align: center;
        }

        .tabel-data .value-col {
            font-weight: 800;
            font-size: 11pt;
            border-bottom: 1px dotted #9ca3af;
        }

        .lulus-text {
            text-align: center;
            font-size: 18pt;
            font-weight: 900;
            letter-spacing: 12px;
            margin: 10px 0;
            padding-left: 12px;
        }

        /* FOOTER (FOTO & 2 TANDA TANGAN) */
        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 12px;
            padding: 0 0.5cm;
        }

        .pas-foto {
            width: 3cm !important;
            height: 3.8cm !important;
            min-width: 3cm;
            min-height: 3.8cm;
            border: 2px solid #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8.5pt;
            font-weight: 600;
            color: #9ca3af;
            background: #f9fafb;
            text-align: center;
        }

        .ttd-box {
            text-align: center;
            width: 5.6cm;
        }

        .ttd-box p {
            margin: 0;
            font-size: 9.5pt;
            line-height: 1.3;
        }

        .qr-space {
            height: 1.7cm;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 2px 0;
        }

        .ttd-nama {
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 2px;
            font-size: 10pt;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('putusan-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Ijazah (Ctrl+P)</span>
        </button>
    </div>

    <!-- WADAH UTAMA IJAZAH -->
    <div class="ijazah-wrapper">
        <!-- HEADER & LOGO -->
        <div class="header-section">
            <div class="logo-box">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}" alt="Logo Madrasah">
            </div>
            <div class="arabic-image">
                <img src="{{ asset('storage/bismillah.png') }}" alt="Bismillah"
                    onerror="this.style.display='none'">
            </div>
        </div>

        <!-- JUDUL IJAZAH -->
        <div class="title-section">
            <div class="title-ijazah">IJAZAH / SYAHADAH</div>
            <div class="title-tingkat">IMTIHAN NIHAI (IMNI) &bull; {{ $peserta->tingkat?->nama_tingkat ?? 'MADRASAH DINIYAH TAKMILIYAH' }}</div>
            <div class="title-madrasah">{{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}</div>
            <div class="title-alamat">{{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}</div>
            <div class="title-tahun">
                Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M
            </div>
        </div>

        <!-- KONTEN TEKS & BIODATA MURID -->
        <div class="content-section">
            <p>
                Kepala Madrasah Diniyah Takmiliyah Hidayatus Shibyan beserta Panitia Pelaksana Imtihan Nihai (IMNI), menerangkan bahwa :
            </p>

            <table class="tabel-data">
                <tr>
                    <td class="label-col">Nama Lengkap Murid</td>
                    <td class="separator-col">:</td>
                    <td class="value-col uppercase">{{ $peserta->murid?->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="label-col">Nomor Induk Murid (NISM)</td>
                    <td class="separator-col">:</td>
                    <td class="value-col font-mono">{{ $peserta->murid?->nism ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Nomor Peserta IMNI</td>
                    <td class="separator-col">:</td>
                    <td class="value-col font-mono text-violet-800">{{ $peserta->nomor_peserta }}</td>
                </tr>
                <tr>
                    <td class="label-col">Tempat & Tanggal Lahir</td>
                    <td class="separator-col">:</td>
                    <td class="value-col">
                        {{ $peserta->murid?->tempat_lahir ?? '-' }},
                        {{ $peserta->murid?->tanggal_lahir ? \Carbon\Carbon::parse($peserta->murid->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Nama Orang Tua / Wali</td>
                    <td class="separator-col">:</td>
                    <td class="value-col">{{ $peserta->murid?->waliMurid?->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Nomor Ijazah Resmi</td>
                    <td class="separator-col">:</td>
                    <td class="value-col font-mono text-emerald-800">
                        {{ $peserta->kelulusan?->nomor_ijazah ?? ('IJZ-IMNI/' . date('Y') . '/' . $peserta->nomor_peserta) }}
                    </td>
                </tr>
            </table>

            <div class="lulus-text">L U L U S</div>

            <p>
                Dalam menempuh seluruh mata pelajaran evaluasi teori/tulis dan Ujian Praktik Al-Qur'an pada Imtihan Nihai (IMNI) yang diselenggarakan pada Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M.
            </p>
        </div>

        <!-- FOOTER (TTD KETUA PANITIA, PAS FOTO 3X4, TTD KEPALA MADRASAH) -->
        @php
            $kepalaMadrasah = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala Madrasah') ?? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala');
            $namaKepala = $kepalaMadrasah?->anggota?->nama_lengkap ?? getSetting('nama_kepala_madrasah', 'K.H. Ahmad Dahlan, S.Pd.I.');
        @endphp
        <div class="footer-section">
            <!-- Tanda Tangan Kiri: Ketua Panitia IMNI -->
            <div class="ttd-box">
                <p class="font-semibold text-zinc-500">Mengetahui,</p>
                <p class="font-bold uppercase">Ketua Panitia IMNI</p>
                <div class="qr-space flex items-center justify-center my-1">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])
                        ) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])
                        ) !!}
                    @else
                        <div class="h-12 flex items-center justify-center text-zinc-300 italic text-[9px]">[Tanda Tangan]</div>
                    @endif
                </div>
                <p class="ttd-nama">{{ $ketuaPanitia?->ustadz?->nama_lengkap ?? '...................................' }}</p>
                <p class="text-[8.5pt] text-zinc-600">NIP/ID: {{ $ketuaPanitia?->ustadz?->nip_ustadz ?? '-' }}</p>
            </div>

            <!-- Pas Foto 3x4 Murid -->
            <div class="pas-foto">
                @if ($peserta->murid?->foto)
                    <img src="{{ asset('storage/' . $peserta->murid->foto) }}" alt="Foto Murid"
                        class="w-full h-full object-cover">
                @else
                    <span>PAS FOTO<br>3 X 4</span>
                @endif
            </div>

            <!-- Tanda Tangan Kanan: Kepala Madrasah -->
            <div class="ttd-box">
                <p>{{ getSetting('kota_madrasah', 'Bangkalan') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold uppercase">Kepala Madrasah</p>
                <div class="qr-space flex items-center justify-center my-1">
                    @if (!empty($kepalaMadrasah?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kepalaMadrasah->id])
                        ) !!}
                    @elseif (!empty($kepalaMadrasah?->anggota_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $kepalaMadrasah->anggota_id])
                        ) !!}
                    @else
                        <div class="h-12 flex items-center justify-center text-zinc-300 italic text-[9px]">[Tanda Tangan & Stempel]</div>
                    @endif
                </div>
                <p class="ttd-nama">{{ $namaKepala }}</p>
                <p class="text-[8.5pt] text-zinc-600">NIP: {{ getSetting('nip_kepala_madrasah', '-') }}</p>
            </div>
        </div>
    </div>

</body>

</html>
