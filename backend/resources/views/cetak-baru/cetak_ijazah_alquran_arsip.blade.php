<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip_Ijazah_Alquran_{{ Str::slug($data['nama_murid'] ?? 'Murid') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
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
            margin-bottom: 14px;
        }

        .logo-box img {
            height: 78px;
            width: auto;
            object-fit: contain;
        }

        .arabic-image img {
            height: 72px;
            width: auto;
            object-fit: contain;
        }

        /* JUDUL */
        .title-section {
            text-align: center;
            margin-bottom: 16px;
        }

        .title-ijazah {
            font-size: 18pt;
            font-weight: 800;
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
            font-size: 13pt;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .title-alamat {
            font-size: 10.5pt;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .title-tahun {
            font-size: 9.5pt;
            font-weight: 600;
            color: #4b5563;
        }

        /* KONTEN */
        .content-section {
            font-size: 11pt;
            line-height: 1.48;
            text-align: justify;
        }

        .tabel-data {
            width: 95%;
            margin: 8px auto 12px auto;
        }

        .tabel-data td {
            padding-bottom: 3.5px;
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
            font-weight: 700;
            font-size: 11.5pt;
            border-bottom: 1px dotted #9ca3af;
        }

        .lulus-text {
            text-align: center;
            font-size: 18pt;
            font-weight: 800;
            letter-spacing: 12px;
            margin: 12px 0;
            padding-left: 12px;
        }

        /* FOOTER (FOTO & 2 TANDA TANGAN) */
        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 14px;
            padding: 0 0.5cm;
        }

        .pas-foto {
            width: 3cm !important;
            height: 3.9cm !important;
            min-width: 3cm;
            min-height: 3.9cm;
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
            font-size: 10pt;
            line-height: 1.3;
        }

        .qr-space {
            height: 1.8cm;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 3px 0;
        }

        .ttd-nama {
            font-weight: 800;
            text-transform: uppercase;
            text-decoration: underline;
            text-underline-offset: 3px;
            font-size: 10.5pt;
        }

        /* NOMOR IJAZAH (DI KIRI BAWAH) */
        .nomor-bawah {
            position: absolute;
            bottom: 0.35cm;
            left: 1.2cm;
            font-size: 9pt;
            font-weight: 700;
            color: #000000;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <!-- TOMBOL AKSI ATAS (HILANG SAAT DICETAK) -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-3 border-b mb-6 shadow-md">
        <div
            class="px-3 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-lg text-xs font-bold flex items-center shadow-2xs">
            🛡️ DOKUMEN BEKU (ARSIP IJAZAH AL-QUR'AN)
        </div>
        <button onclick="window.print()"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition-all shadow-md cursor-pointer">
            ✕ Tutup
        </button>
    </div>

    <!-- KERTAS IJAZAH -->
    <div class="ijazah-wrapper">

        <!-- HEADER (LOGO & LAFADZ) -->
        <div class="header-section">
            <div class="logo-box">
                <img src="{{ asset('storage/logo_hitam.png') }}" alt="Logo Madrasah">
            </div>
            <div class="arabic-image">
                <img src="{{ asset('storage/lafadz.png') }}" alt="Kaligrafi Ayat">
            </div>
        </div>

        <!-- JUDUL & IDENTITAS INSTANSI -->
        <div class="title-section">
            <div class="title-ijazah">IJAZAH</div>
            <div class="title-tingkat">SYARAT KELULUSAN TINGKAT IBTIDAIYAH</div>
            <div class="title-madrasah">MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN</div>
            <div class="title-alamat">SOMORKONENG KWANYAR BANGKALAN</div>
            <div class="title-tahun">Tahun Pelajaran {{ $data['tahun_masehi'] ?? '-' }} M
                / {{ $data['tahun_hijriyah'] ?? '-' }} H</div>
        </div>

        <!-- KONTEN INTI -->
        <div class="content-section">
            <p>
                Yang bertanda tangan dibawah ini, Pengasuh Madrasah serta Dewan Penguji Ujian Al-Qur'an Madrasah Diniyah
                Takmiliyah Hidayatus Shibyan Somorkoneng Kwanyar Bangkalan menerangkan bahwa:
            </p>

            <table class="tabel-data">
                <tr>
                    <td class="label-col">Nama</td>
                    <td class="separator-col">:</td>
                    <td class="value-col uppercase">{{ $data['nama_murid'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Tempat, Tanggal Lahir</td>
                    <td class="separator-col">:</td>
                    <td class="value-col">
                        {{ $data['tempat_tgl_lahir'] ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Wali</td>
                    <td class="separator-col">:</td>
                    <td class="value-col">{{ $data['nama_wali'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Nomor Induk / NISM</td>
                    <td class="separator-col">:</td>
                    <td class="value-col tracking-widest">{{ $data['nism'] ?? '-' }}</td>
                </tr>
            </table>

            <div class="lulus-text">L U L U S</div>

            <p>
                dalam <strong>Ujian Membaca Kitab Suci Al-Qur'an</strong> sebagai Syarat Kelulusan Tingkat Ibtidaiyah
                yang diselenggarakan pada tanggal
                <strong>{{ !empty($data['tanggal_ujian']) ? \Carbon\Carbon::parse($data['tanggal_ujian'])->translatedFormat('d F Y') : (!empty($data['tanggal_pelaksanaan']) ? \Carbon\Carbon::parse($data['tanggal_pelaksanaan'])->translatedFormat('d F Y') : (!empty($data['tanggal_lulus']) ? \Carbon\Carbon::parse($data['tanggal_lulus'])->translatedFormat('d F Y') : (!empty($data['tanggal_disahkan']) ? \Carbon\Carbon::parse($data['tanggal_disahkan'])->translatedFormat('d F Y') : '-'))) }}</strong>.
            </p>

            <p class="mt-3">
                Ijazah ini diberikan sebagai bukti kelulusan dan dorongan bagi yang bersangkutan agar senantiasa
                mencintai, mengamalkan, dan mensyiarkan Al-Qur'an demi mengharap ridha Allah <i>Subhanahu wa Ta'ala</i>.
            </p>

            <!-- TANGGAL CETAK -->
            <div class="text-right mt-4" style="padding-right: 1.5cm;">
                Bangkalan,
                {{ !empty($data['tanggal_disahkan']) ? \Carbon\Carbon::parse($data['tanggal_disahkan'])->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
            </div>
        </div>

        <!-- AREA TANDA TANGAN & FOTO (2 TANDA TANGAN DENGAN FORMAT RESMI) -->
        <div class="footer-section">

            <!-- KIRI: PAS FOTO -->
            <div class="pas-foto-wrapper">
                <div class="pas-foto">
                    Pas Foto<br>Murid<br>3x4
                </div>
            </div>

            <!-- TENGAH: DEWAN PENGUJI / PENANGGUNG JAWAB -->
            <div class="ttd-box">
                <p>Dewan Penguji</p>
                <p>Ujian Al-Qur'an</p>
                <p>Hidayatus Shibyan</p>

                <div class="qr-space">
                    @if (!empty($data['juri_pj_id']))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $data['juri_pj_id']]),
                        ) !!}
                    @endif
                </div>

                <p class="ttd-nama">{{ $data['juri_pj_nama'] ?? 'Dewan Penguji' }}</p>
            </div>

            <!-- KANAN: PENGASUH -->
            <div class="ttd-box">
                <p>Pengasuh</p>
                <p>Madrasah Diniyah</p>
                <p>Hidayatus Shibyan</p>

                <div class="qr-space">
                    @if (!empty($data['pengasuh_id']))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $data['pengasuh_id']]),
                        ) !!}
                    @endif
                </div>

                <p class="ttd-nama">{{ $data['pengasuh_nama'] ?? 'Pengasuh' }}</p>
            </div>

        </div>

        <!-- NOMOR IJAZAH (POJOK KIRI BAWAH) -->
        <div class="nomor-bawah">
            No Ijazah : {{ $data['nomor_ijazah_alquran'] ?? ($data['nomor_dokumen'] ?? '-') }}
        </div>

    </div>

    @if (request()->has('print') || request()->has('auto_print') || request()->has('download'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 600);
            });
        </script>
    @endif

</body>

</html>
