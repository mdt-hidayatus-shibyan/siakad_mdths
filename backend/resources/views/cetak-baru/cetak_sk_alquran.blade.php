<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        SK_Kelulusan_Alquran_{{ $peserta->status_kelulusan ?? 'Hasil' }}_{{ Str::slug($peserta->murid->nama_lengkap ?? 'Murid') }}
    </title>

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
                padding: 0.8cm 1cm !important;
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
            padding: 0.8cm 1.2cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            line-height: 1.35;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 6px;
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
            max-height: 115px;
            display: inline-block;
            object-fit: contain;
        }

        .judul-kanan {
            width: 40%;
            text-align: right;
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 12px;
        }

        .judul-sk {
            text-align: center;
            margin-bottom: 12px;
        }

        .tabel-konsideran td {
            vertical-align: top;
            padding-bottom: 4px;
        }

        .tabel-konsideran td:nth-child(1) {
            width: 95px;
            font-weight: bold;
        }

        .tabel-konsideran td:nth-child(2) {
            width: 15px;
            text-align: center;
        }

        .tabel-identitas {
            margin: 6px 0 6px 35px;
            width: calc(100% - 35px);
        }

        .tabel-identitas td {
            padding-bottom: 3px;
            font-size: 10.5pt;
        }

        .tabel-identitas td:nth-child(1) {
            width: 160px;
            font-weight: bold;
        }

        .tabel-identitas td:nth-child(2) {
            width: 15px;
        }

        .tabel-rincian {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
            font-size: 10pt;
        }

        .tabel-rincian th,
        .tabel-rincian td {
            border: 1px solid #000;
            padding: 4px 8px;
            text-align: center;
        }

        .tabel-rincian th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- TOMBOL AKSI & INDIKATOR -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-3 border-b mb-6 shadow-md">
        <div
            class="px-3 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-lg text-xs font-bold flex items-center shadow-2xs">
            🛡️ SURAT KEPUTUSAN RESMI UJIAN AL-QUR'AN
        </div>
        <button onclick="window.print()"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition-all shadow-md">
            ✕ Tutup
        </button>
    </div>

    <div class="sk-container text-[10.5pt] text-justify">

        <div class="header-wrapper">
            <div class="kop-kiri">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
            </div>

        </div>
        <div class="garis-ganda"></div>

        <div class="judul-sk">
            <h2 class="text-[11.5pt] font-bold underline uppercase mb-0.5">SURAT KEPUTUSAN KELULUSAN UJIAN AL-QUR'AN
            </h2>
            <p class="font-bold text-[10pt]">Nomor:
                {{ $peserta->nomor_sk_alquran ?? ($peserta->ujianAlquran->nomor_surat_keputusan ?? 'SK/UAQ/' . date('Y')) }}
            </p>
            <p class="font-bold mt-1 uppercase text-[10pt]">TENTANG<br>PENETAPAN HASIL UJIAN BACA KITAB SUCI AL-QUR'AN
                SEBAGAI SYARAT KELULUSAN TINGKAT IBTIDAIYAH</p>
        </div>

        <p class="mb-2">Kepala Madrasah Diniyah Takmiliyah Hidayatus Shibyan,</p>

        <table class="w-full tabel-konsideran mb-1">
            <tr>
                <td>Menimbang</td>
                <td>:</td>
                <td>
                    <ol class="list-decimal pl-4 m-0">
                        <li>Bahwa kemampuan membaca Kitab Suci Al-Qur'an secara tartil, fasih, dan sesuai kaidah tajwid
                            merupakan salah satu syarat mutlak kelulusan murid dari tingkat Ibtidaiyah di MDT Hidayatus
                            Shibyan;</li>
                        <li>Bahwa berdasarkan hasil evaluasi Dewan Juri pada Ujian Al-Qur'an Tahun Pelajaran
                            {{ $peserta->ujianAlquran->tahunPelajaran->nama_masehi ?? '-' }}, murid yang bersangkutan
                            telah menempuh ujian dan perlu ditetapkan status kelulusannya melalui Surat Keputusan resmi.
                        </li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td>Mengingat</td>
                <td>:</td>
                <td>
                    <ol class="list-decimal pl-4 m-0">
                        <li>Pedoman Kurikulum dan Peraturan Akademik MDT Hidayatus Shibyan;</li>
                        <li>Standar Operasional Prosedur (SOP) Penyelenggaraan Ujian Al-Qur'an Tingkat Ibtidaiyah;</li>
                        <li>Ketetapan Batas Minimal Nilai Kelulusan (KKM > 55).</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td>Memperhatikan</td>
                <td>:</td>
                <td>
                    Hasil rekapitulasi penilaian Dewan Juri Khotho' Jali dan Dewan Juri Khotho' Khofi pada tanggal
                    <strong>{{ \Carbon\Carbon::parse($peserta->ujianAlquran->tanggal_ujian ?? ($peserta->ujianAlquran->tanggal_pelaksanaan ?? ($peserta->tanggal_lulus ?? now())))->translatedFormat('d F Y') }}</strong>.
                </td>
            </tr>
        </table>

        <div class="text-center font-bold text-[10.5pt] my-2">MEMUTUSKAN</div>

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
                <td>Nama Lengkap Murid</td>
                <td>:</td>
                <td class="font-bold uppercase">{{ $peserta->murid->nama_lengkap ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nomor Induk / NISM</td>
                <td>:</td>
                <td>{{ $peserta->murid->nism ?? '-' }}</td>
            </tr>
            <tr>
                <td>Ruangan / Kelas</td>
                <td>:</td>
                <td>{{ $peserta->ruangan->nama_ruangan ?? '-' }} (Level:
                    {{ $peserta->ruangan->level->nama_level ?? '-' }})</td>
            </tr>
            <tr>
                <td>Nomor Peserta</td>
                <td>:</td>
                <td>{{ $peserta->nomor_peserta ?? '-' }}</td>
            </tr>
        </table>

        <!-- RINCIAN HASIL EVALUASI -->
        <table class="tabel-rincian">
            <thead>
                <tr>
                    <th>Khotho' Jali (-5/err)</th>
                    <th>Khotho' Khofi (-3/err)</th>
                    <th>Total Pengurangan</th>
                    <th>Nilai Akhir</th>
                    <th>Predikat</th>
                </tr>
            </thead>
            <tbody>
                <tr class="font-semibold">
                    <td>{{ $peserta->jumlah_khoto_jali }} x 5 = -{{ $peserta->poin_pengurangan_jali }}</td>
                    <td>{{ $peserta->jumlah_khoto_khofi }} x 3 = -{{ $peserta->poin_pengurangan_khofi }}</td>
                    <td class="text-rose-700 font-bold">-{{ $peserta->total_pengurangan }}</td>
                    <td class="font-black text-[11pt]">{{ number_format($peserta->nilai_akhir, 0) }} / 100</td>
                    <td>{{ $peserta->predikat }} ({{ $peserta->predikat_arab }})</td>
                </tr>
            </tbody>
        </table>

        <table class="w-full tabel-konsideran">
            <tr>
                <td></td>
                <td></td>
                <td>Berdasarkan hasil kalkulasi dewan juri, murid tersebut dinyatakan:</td>
            </tr>
        </table>

        <div class="text-center my-2">
            <span
                class="inline-block px-8 py-1 border-2 border-black font-black text-[12pt] uppercase tracking-widest {{ $peserta->status_kelulusan === 'Lulus' ? 'bg-slate-100' : 'bg-rose-100 text-rose-800' }}">
                --- {{ strtoupper($peserta->status_kelulusan) }} ---
            </span>
        </div>

        <table class="w-full tabel-konsideran mb-2">
            <tr>
                <td>Kedua</td>
                <td>:</td>
                <td>
                    @if ($peserta->status_kelulusan === 'Lulus')
                        Murid yang bersangkutan dinyatakan <strong>berhak memperoleh Ijazah Kelulusan Al-Qur'an</strong>
                        dan telah <strong>memenuhi syarat wajib kelulusan tingkat Ibtidaiyah</strong>.
                    @else
                        Murid yang bersangkutan dinyatakan <strong>BELUM LULUS</strong> dan diwajibkan untuk mengikuti
                        <strong>Ujian Al-Qur'an susulan / remidi pada Tahun Pelajaran berikutnya di Kelas 6
                            Ibtidaiyah</strong>.
                    @endif
                </td>
            </tr>
            <tr>
                <td>Ketiga</td>
                <td>:</td>
                <td>Keputusan ini bersifat mutlak, mengikat, dan mulai berlaku sejak tanggal ditetapkan.</td>
            </tr>
        </table>

        <!-- TANDA TANGAN -->
        <table class="w-full text-center mt-3 text-[9.5pt]" style="table-layout: fixed;">
            <tr>
                <td class="w-1/3 align-top" style="height: 55px;">
                    <p class="mb-0">Mengesahkan,</p>
                    <p class="font-bold">Pengasuh Madrasah</p>
                </td>
                <td class="w-1/3 align-top" style="height: 55px;">
                    <p class="mb-0">Mengetahui,</p>
                    <p class="font-bold">Kepala Bidang Ibtidaiyah</p>
                </td>
                <td class="w-1/3 align-top" style="height: 55px;">
                    <p class="mb-0">Ditetapkan di : Bangkalan</p>
                    <p class="mb-0">Tanggal :
                        {{ \Carbon\Carbon::parse($peserta->ujianAlquran->tanggal_ujian ?? ($peserta->ujianAlquran->tanggal_pelaksanaan ?? ($peserta->tanggal_lulus ?? now())))->translatedFormat('d F Y') }}
                    </p>
                    <p class="font-bold">Ketua Dewan Penguji</p>
                </td>
            </tr>
            <tr>
                <!-- TTD 1: Pengasuh -->
                <td class="align-bottom pb-1 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                    </div>
                </td>

                <!-- TTD 2: Kabid -->
                <td class="align-bottom pb-1 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($kabid?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kabid->id]),
                            ) !!}
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                    </div>
                </td>

                <!-- TTD 3: Juri Penanggung Jawab / Dewan Penguji -->
                <td class="align-bottom pb-1 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($juriPj?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $juriPj->ustadz_id]),
                            ) !!}
                        @elseif(!empty($administrator?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $administrator->id]),
                            ) !!}
                        @else
                            <div style="height: 50px;"></div>
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">
                        {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}</p>
                </td>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">
                        {{ $kabid?->anggota?->nama_lengkap ?? ($kabid?->nama ?? 'Kepala Bidang') }}</p>
                </td>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">
                        {{ $juriPj?->ustadz?->nama_lengkap ?? ($administrator?->nama_lengkap ?? 'Dewan Penguji') }}
                    </p>
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
