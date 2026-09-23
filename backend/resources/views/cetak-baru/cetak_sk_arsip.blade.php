<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip_SK_{{ $data['status_kelulusan'] ?? 'Lulus' }}_{{ Str::slug($data['nama_murid'] ?? 'Murid') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4;
                margin: 1mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .sk-container {
                margin: 0 !important;
                padding: 1cm !important;
                box-shadow: none !important;
                min-height: auto !important;
            }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #e2e8f0;
            color: #000;
        }

        .sk-container {
            background: white;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 5mm auto;
            padding: 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            line-height: 1.35;
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
            max-height: 130px;
            display: inline-block;
            object-fit: contain;
        }

        .judul-kanan {
            width: 40%;
            text-align: right;
            font-size: 13pt;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .tahun-pelajaran {
            font-size: 10pt;
            font-weight: 500;
            color: #475569;
            display: block;
            margin-top: 2px;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }

        .judul-sk {
            text-align: center;
            margin-bottom: 15px;
        }

        .tabel-konsideran td {
            vertical-align: top;
            padding-bottom: 6px;
        }

        .tabel-konsideran td:nth-child(1) {
            width: 90px;
            font-weight: bold;
        }

        .tabel-konsideran td:nth-child(2) {
            width: 15px;
            text-align: center;
        }

        .tabel-identitas {
            margin: 10px 0 10px 40px;
        }

        .tabel-identitas td {
            padding-bottom: 4px;
            font-size: 11pt;
        }

        .tabel-identitas td:nth-child(1) {
            width: 140px;
            font-weight: bold;
        }

        .tabel-identitas td:nth-child(2) {
            width: 15px;
        }
    </style>
</head>

<body>

    <!-- TOMBOL AKSI & INDIKATOR -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-3 border-b mb-6 shadow-md">
        <div
            class="px-3 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-lg text-xs font-bold flex items-center shadow-2xs">
            🛡️ DOKUMEN BEKU (ARSIP RESMI)
        </div>
        <button onclick="window.print()"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="sk-container text-[11pt] text-justify">

        <div class="header-wrapper">
            <div class="kop-kiri">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
            </div>
        </div>
        <div class="garis-ganda"></div>

        <div class="judul-sk">
            <h2 class="text-[12pt] font-bold underline uppercase mb-1">KEPUTUSAN PERADMINISTRASIAN TINGKAT
                {{ strtoupper($data['lulus_dari_tingkat'] ?? 'MADRASAH') }}</h2>
            <p class="font-bold">Nomor: {{ $data['nomor_dokumen'] }}</p>
            <p class="font-bold mt-2 uppercase text-[11pt]">TENTANG<br>EVALUASI AKADEMIK & KENAIKAN KELAS</p>
        </div>

        <p class="mb-3">Pengasuh Madrasah Diniyah Takmiliyah Hidayatus Shibyan,</p>

        <table class="w-full tabel-konsideran mb-2">
            <tr>
                <td>Menimbang</td>
                <td>:</td>
                <td>Bahwa untuk menentukan capaian hasil belajar dan kelanjutan studi murid pada Tahun Pelajaran
                    {{ $data['tahun_pelajaran'] }}, perlu ditetapkan Keputusan Administrator
                    Tingkat/Kepala Administrator, disahkan oleh Pengasuh Madrasah dan Kepala Bidang Tingkat.</td>
            </tr>
            <tr>
                <td>Mengingat</td>
                <td>:</td>
                <td>Pedoman Kurikulum dan Peraturan Akademik MDT Hidayatus Shibyan.</td>
            </tr>
            <tr>
                <td>Memperhatikan</td>
                <td>:</td>
                <td>
                    <ol class="list-decimal pl-4 m-0">
                        <li>Hasil Rapat Pengasuh, Kepala Bidang, Administrator Tingkat dan Wali Ruangan pada akhir Tahun
                            Pelajaran;</li>
                        <li>Akumulasi Nilai Akhir (Nilai Ujian, Presensi, dan Pelanggaran)</li>
                    </ol>
                </td>
            </tr>
        </table>

        <div class="text-center font-bold text-[11pt] mb-3 mt-3">MEMUTUSKAN</div>

        <table class="w-full tabel-konsideran mb-0">
            <tr>
                <td>Menetapkan</td>
                <td>:</td>
                <td></td>
            </tr>
            <tr>
                <td>Pertama</td>
                <td>:</td>
                <td>Menyatakan bahwa murid di bawah ini:</td>
            </tr>
        </table>

        <table class="tabel-identitas">
            <tr>
                <td>Nama Lengkap</td>
                <td>:</td>
                <td class="font-bold uppercase">{{ $data['nama_murid'] }}</td>
            </tr>
            <tr>
                <td>NISM</td>
                <td>:</td>
                <td>{{ $data['nism'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Ruangan</td>
                <td>:</td>
                <td>{{ $data['nama_ruangan'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nilai Final Total</td>
                <td>:</td>
                <td class="font-bold">{{ $data['nilai_akumulasi'] ?? '-' }}</td>
            </tr>
        </table>

        <table class="w-full tabel-konsideran">
            <tr>
                <td></td>
                <td></td>
                <td>Berdasarkan hasil evaluasi kognitif, presensi, dan kedisiplinan, dinyatakan secara resmi:</td>
            </tr>
        </table>

        <div class="text-center my-4">
            <span
                class="inline-block px-6 py-1.5 border-2 border-black font-black text-[13pt] uppercase tracking-widest bg-slate-100">
                --- {{ $data['status_keputusan'] ?? 'LULUS' }} ---
            </span>
        </div>

        <table class="w-full tabel-konsideran mb-4">
            <tr>
                <td>Kedua</td>
                <td>:</td>
                <td>Keputusan ini bersifat mutlak, mengikat, dan mulai berlaku sejak tanggal ditetapkan. Apabila
                    terdapat kekeliruan di kemudian hari, akan diadakan perbaikan sebagaimana mestinya.</td>
            </tr>
        </table>

        <table class="w-full text-center mt-6 text-[10pt]" style="table-layout: fixed;">
            <tr>
                <td class="w-1/3 align-top" style="height: 70px;">
                    <br>
                    <p class="mb-1">Mengesahkan,</p>
                    <p class="font-bold">Pengasuh Madrasah</p>
                </td>
                <td class="w-1/3 align-top" style="height: 70px;">
                    <br>
                    <p class="mb-1">Mengetahui,</p>
                    <p class="font-bold">Kepala Bidang Tingkat
                        {{ strtoupper($data['lulus_dari_tingkat'] ?? 'MADRASAH') }}</p>
                </td>
                <td class="w-1/3 align-top" style="height: 70px;">
                    <p class="mb-0">Ditetapkan di : Bangkalan</p>
                    <p class="mb-1">Pada Tanggal :
                        {{ \Carbon\Carbon::parse($data['tanggal_disahkan'])->translatedFormat('d F Y') }}</p>
                    <p class="font-bold">{{ strtoupper($data['admin_jabatan'] ?? 'ADMINISTRATOR') }}</p>
                </td>
            </tr>
            <tr>
                <!-- TTD 1: Pengasuh -->
                <td class="align-bottom pb-2 pt-2">
                    <div class="flex justify-center">
                        @if (!empty($data['pengasuh_id']))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $data['pengasuh_id']]),
                            ) !!}
                        @endif
                    </div>
                </td>

                <!-- TTD 2: Kabid -->
                <td class="align-bottom pb-2 pt-2">
                    <div class="flex justify-center">
                        @if (!empty($data['kabid_id']))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $data['kabid_id']]),
                            ) !!}
                        @endif
                    </div>
                </td>

                <!-- TTD 3: Admin -->
                <td class="align-bottom pb-2 pt-2">
                    <div class="flex justify-center">
                        @if (!empty($data['admin_id']))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $data['admin_id']]),
                            ) !!}
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $data['pengasuh_nama'] ?? '-' }}</p>
                </td>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $data['kabid_nama'] ?? '-' }}</p>
                </td>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $data['admin_nama'] ?? '-' }}</p>
                </td>
            </tr>
        </table>
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
