<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ Str::slug($surat->nomor_surat) }}_{{ Str::slug($surat->perihal) }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 12mm;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 2mm 4mm !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 278mm !important;
                min-height: 278mm !important;
                border: none !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }

            .page-sheet:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', Times, serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 10pt;
            line-height: 1.38;
        }

        .page-sheet {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            padding: 10mm 15mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* HEADER KOP SURAT */
        .kop-wrapper {
            display: block;
            width: 100%;
            text-align: left;
            padding-bottom: 4px;
            border-bottom: 2.5px solid #000;
            margin-bottom: 2px;
        }

        .kop-garis-bawah {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }

        .kop-logo {
            max-width: 100%;
            max-height: 85px;
            height: auto;
            object-fit: contain;
            display: inline-block;
        }

        .kop-instansi {
            font-size: 12pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .kop-nama-lembaga {
            font-size: 15pt;
            font-weight: 900;
            letter-spacing: 1px;
            color: #047857;
            text-transform: uppercase;
            margin: 2px 0;
        }

        .kop-alamat {
            font-size: 8pt;
            font-weight: 500;
            color: #475569;
            line-height: 1.25;
        }

        .tabel-identitas td {
            padding: 1.5px 3px;
            vertical-align: top;
            font-size: 9.5pt;
        }

        .tabel-data-box {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            background: #fafafa;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .tabel-data-box td {
            padding: 4px 6px;
            font-size: 9.5pt;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
</head>

<body>

    <!-- TOP TOOLBAR (NO PRINT) -->
    <div
        class="no-print bg-slate-900 text-white py-3 px-6 sticky top-0 z-50 shadow-md flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="font-extrabold text-xs tracking-wider uppercase bg-emerald-600 px-2.5 py-1 rounded-md">
                Pratinjau Cetak Surat
            </span>
            <span class="text-xs font-mono text-slate-300 font-bold">{{ $surat->nomor_surat }}</span>
            <span class="text-xs text-slate-400">• {{ $surat->nama_jenis }}</span>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()"
                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Simpan PDF (Ctrl+P)</span>
            </button>
            <a href="{{ route('surat-keluar.show', $surat->id) }}"
                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-all flex items-center gap-1.5 border border-slate-700">
                <span>Kembali</span>
            </a>
        </div>
    </div>

    @php
        $spesifik = $surat->isi_spesifik ?? [];
    @endphp

    <!-- LEMBAR SURAT RESMI -->
    <div class="page-sheet">
        <div>
            <!-- 1. KOP SURAT RESMI MDTHS -->
            <div class="kop-wrapper">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah" class="kop-logo" />
            </div>
            <div class="kop-garis-bawah"></div>

            <!-- 2. NOMOR, TANGGAL & PERIHAL SURAT -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 9.5pt;">
                <table style="line-height: 1.3;">
                    <tr>
                        <td style="width: 70px; font-weight: 600;">Nomor</td>
                        <td style="width: 8px;">:</td>
                        <td style="font-weight: 700; font-family: monospace;">{{ $surat->nomor_surat }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Sifat</td>
                        <td>:</td>
                        <td>{{ $surat->sifat_surat }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Lampiran</td>
                        <td>:</td>
                        <td>{{ $surat->lampiran ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Perihal</td>
                        <td>:</td>
                        <td style="font-weight: 700;">{{ $surat->perihal }}</td>
                    </tr>
                </table>

                <div style="text-align: right; line-height: 1.3;">
                    <div>{{ $surat->tempat_terbit }},
                        {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }} M</div>
                    @if ($surat->tanggal_hijriyah)
                        <div style="font-size: 9pt; color: #334155;">{{ $surat->tanggal_hijriyah }}</div>
                    @endif
                </div>
            </div>

            <!-- 3. TUJUAN KEPADA YTH -->
            <div style="margin-bottom: 8px; line-height: 1.3; font-size: 10pt;">
                <div>Kepada Yth.</div>
                <div style="font-weight: 800; font-size: 10.5pt;">{{ $surat->tujuan_surat }}</div>
                <div style="color: #334155;">{{ $surat->alamat_tujuan ?: 'Di Tempat' }}</div>
            </div>

            <!-- 4. ISI SURAT -->
            <div style="text-align: justify; line-height: 1.38; font-size: 10pt;">

                <!-- SALAM PEMBUKA & PENGANTAR -->
                <p style="margin-bottom: 3px;"><em>Assalamualaikum War. Wab.</em></p>
                <p style="margin-bottom: 3px;">Dengan hormat,</p>
                <p style="margin-bottom: 5px; text-indent: 18px;">
                    Puji syukur Alhamdulillah kami ucapkan kehadirat Allah SWT yang telah melimpahkan rahmat dan
                    hidayah-Nya kepada kita semua. Sholawat dan salam tercurah kepada Nabi Muhammad SAW serta keluarga
                    dan para sahabatnya.
                </p>

                <!-- KONTEN SPESIFIK SESUAI 7 TEMPLATE SURAT -->

                {{-- A. SURAT PANGGILAN --}}
                @if ($surat->jenis_surat === 'surat_panggilan')
                    <p style="margin-bottom: 8px;">
                        Sehubungan dengan keperluan
                        <strong>{{ $spesifik['alasan_panggilan'] ?? 'evaluasi perkembangan dan tata tertib murid' }}</strong>,
                        maka dengan ini kami mengharap kehadiran Bapak/Ibu/Wali Murid dari:
                    </p>

                    @if ($surat->murid)
                        <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 8px;">
                            <tr>
                                <td style="width: 150px; font-weight: 600;">Nama Murid</td>
                                <td style="width: 10px;">:</td>
                                <td style="font-weight: 700;">{{ $surat->murid->nama_lengkap }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">NISM</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nism ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Kelas / Ruangan</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Nama Orang Tua / Wali</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Dusun / Kampung</td>
                                <td>:</td>
                                <td>{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}
                                </td>
                            </tr>
                        </table>
                    @endif

                    <p style="margin-bottom: 6px;">Untuk hadir menghadap pada:</p>
                    <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 10px;">
                        <tr>
                            <td style="width: 140px; font-weight: 600;">Hari / Tanggal</td>
                            <td style="width: 10px;">:</td>
                            <td style="font-weight: 700;">
                                {{ $spesifik['hari_panggilan'] ?? '-' }},
                                {{ isset($spesifik['tanggal_panggilan']) && $spesifik['tanggal_panggilan'] ? \Carbon\Carbon::parse($spesifik['tanggal_panggilan'])->translatedFormat('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Waktu / Pukul</td>
                            <td>:</td>
                            <td>{{ $spesifik['waktu_panggilan'] ?? '14.00 WIB s/d Selesai' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Tempat</td>
                            <td>:</td>
                            <td>{{ $spesifik['tempat_menghadap'] ?? 'Kantor TU / Ruang Guru MDT Hidayatus Shibyan' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Menghadap Kepada</td>
                            <td>:</td>
                            <td style="font-weight: 700;">
                                {{ $spesifik['menghadap_kepada'] ?? 'Pengasuh & Tim Kesiswaan' }}</td>
                        </tr>
                    </table>

                    {{-- B. SURAT PERINGATAN (SP) --}}
                @elseif ($surat->jenis_surat === 'surat_peringatan')
                    <p style="margin-bottom: 8px;">
                        Berdasarkan hasil pemantauan kedisiplinan dan tata tertib madrasah, dengan ini Pengurus MDT
                        Hidayatus Shibyan menerbitkan
                        <strong>{{ $spesifik['tingkat_sp'] ?? 'Surat Peringatan' }}</strong> kepada murid:
                    </p>

                    @if ($surat->murid)
                        <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 8px;">
                            <tr>
                                <td style="width: 150px; font-weight: 600;">Nama Murid</td>
                                <td style="width: 10px;">:</td>
                                <td style="font-weight: 700;">{{ $surat->murid->nama_lengkap }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">NISM</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nism ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Kelas / Ruangan</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Nama Orang Tua / Wali</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Dusun / Kampung</td>
                                <td>:</td>
                                <td>{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}
                                </td>
                            </tr>
                        </table>
                    @endif

                    <p style="margin-bottom: 6px;">Surat Peringatan ini diberikan atas pertimbangan pelanggaran sebagai
                        berikut:</p>
                    <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 10px;">
                        <tr>
                            <td style="width: 140px; font-weight: 600;">Uraian Pelanggaran</td>
                            <td style="width: 10px;">:</td>
                            <td style="font-weight: 700; color: #b91c1c;">{{ $spesifik['bentuk_pelanggaran'] ?? '-' }}
                            </td>
                        </tr>
                        @if (!empty($spesifik['poin_tatib_dilanggar']))
                            <tr>
                                <td style="font-weight: 600;">Dasar Peraturan</td>
                                <td>:</td>
                                <td>{{ $spesifik['poin_tatib_dilanggar'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($spesifik['tindakan_pembinaan']))
                            <tr>
                                <td style="font-weight: 600;">Tindakan Pembinaan</td>
                                <td>:</td>
                                <td>{{ $spesifik['tindakan_pembinaan'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($spesifik['batas_waktu_pembinaan']))
                            <tr>
                                <td style="font-weight: 600;">Masa Pembinaan</td>
                                <td>:</td>
                                <td>{{ $spesifik['batas_waktu_pembinaan'] }}</td>
                            </tr>
                        @endif
                    </table>
                    <p style="margin-bottom: 8px;">
                        Kami menghimbau kepada murid bersangkutan dan orang tua/wali untuk segera melakukan pembinaan
                        dan perbaikan agar tidak berlanjut ke sanksi tingkat berikutnya.
                    </p>

                    {{-- C. SURAT PEMBERITAHUAN --}}
                @elseif ($surat->jenis_surat === 'surat_pemberitahuan')
                    <p style="margin-bottom: 8px;">
                        Bersama ini kami sampaikan pemberitahuan mengenai hal berikut :

                    </p>

                    @if ($surat->murid)
                        <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 8px;">
                            <tr>
                                <td style="width: 150px; font-weight: 600;">Nama Murid</td>
                                <td style="width: 10px;">:</td>
                                <td style="font-weight: 700;">{{ $surat->murid->nama_lengkap }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">NISM</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nism ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Kelas / Ruangan</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Nama Orang Tua / Wali</td>
                                <td>:</td>
                                <td>{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Dusun / Kampung</td>
                                <td>:</td>
                                <td>{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}
                                </td>
                            </tr>
                        </table>
                    @endif

                    <div
                        style="background: #f8fafc; border-left: 4px solid #047857; padding: 8px 12px; margin: 10px 0; border-radius: 4px;">

                        <div style="font-weight: 700; margin-bottom: 4px;">
                            {{ $spesifik['pokok_pemberitahuan'] ?? $surat->perihal }}</div>
                        @if (!empty($spesifik['jadwal_terkait']))
                            <div style="font-size: 10pt; color: #475569;"><strong>Jadwal Pelaksanaan:</strong>
                                {{ $spesifik['jadwal_terkait'] }}</div>
                        @endif
                    </div>

                    {{-- D. SURAT EDARAN --}}
                @elseif ($surat->jenis_surat === 'surat_edaran')
                    <div style="text-align: center; margin-bottom: 12px;">
                        <div style="font-size: 11pt; font-weight: 800; text-transform: uppercase;">
                            SURAT EDARAN RESMI MADRASAH
                        </div>
                        <div style="font-size: 10pt; color: #475569;">
                            {{ $spesifik['nomor_edaran_internal'] ?? $surat->nomor_surat }}
                        </div>
                        <div style="font-size: 10.5pt; font-weight: 700; margin-top: 4px;">
                            TENTANG: {{ strtoupper($spesifik['pokok_maklumat'] ?? $surat->perihal) }}
                        </div>
                    </div>

                    <p style="margin-bottom: 8px;">
                        Diberitahukan kepada seluruh Asatidz, Dewan Guru, Murid, dan Wali Murid MDT Hidayatus Shibyan
                        mengenai ketetapan bersama sebagai berikut:
                    </p>

                    <div style="margin: 8px 0 10px 10px; font-size: 10.5pt; line-height: 1.6;">
                        {!! nl2br(e($spesifik['instruksi_poin'] ?? '')) !!}
                    </div>

                    @if (!empty($spesifik['berlaku_mulai']))
                        <p style="margin-bottom: 8px;">
                            Ketetapan surat edaran ini berlaku sejak <strong>{{ $spesifik['berlaku_mulai'] }}</strong>
                            sampai dengan pemberitahuan resmi berikutnya.
                        </p>
                    @endif

                    {{-- E. SURAT PERMOHONAN IZIN --}}
                @elseif ($surat->jenis_surat === 'surat_permohonan_izin')
                    <p style="margin-bottom: 8px;">
                        Sehubungan dengan rencana pelaksanaan kegiatan
                        <strong>{{ $spesifik['nama_kegiatan'] ?? $surat->perihal }}</strong> oleh MDT Hidayatus Shibyan
                        yang insya Allah akan dilaksanakan pada:
                    </p>

                    <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 10px;">
                        <tr>
                            <td style="width: 140px; font-weight: 600;">Hari / Tanggal</td>
                            <td style="width: 10px;">:</td>
                            <td style="font-weight: 700;">
                                {{ $spesifik['hari_kegiatan'] ?? '-' }},
                                {{ isset($spesifik['tanggal_kegiatan']) && $spesifik['tanggal_kegiatan'] ? \Carbon\Carbon::parse($spesifik['tanggal_kegiatan'])->translatedFormat('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Waktu</td>
                            <td>:</td>
                            <td>{{ $spesifik['waktu_kegiatan'] ?? '08.00 WIB s/d Selesai' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Tempat Pelaksanaan</td>
                            <td>:</td>
                            <td>{{ $spesifik['tempat_kegiatan'] ?? '-' }}</td>
                        </tr>
                        @if (!empty($spesifik['penanggung_jawab']))
                            <tr>
                                <td style="font-weight: 600;">Penanggung Jawab</td>
                                <td>:</td>
                                <td>{{ $spesifik['penanggung_jawab'] }}</td>
                            </tr>
                        @endif
                    </table>

                    <p style="margin-bottom: 8px;">
                        Maka dengan ini kami mengajukan permohonan izin untuk
                        <strong>{{ $spesifik['fasilitas_dimohonkan'] ?? 'peminjaman fasilitas dan pelaksanaan kegiatan tersebut' }}</strong>.
                    </p>

                    {{-- F. SURAT DISPENSASI --}}
                @elseif ($surat->jenis_surat === 'surat_dispensasi')
                    @if ($surat->is_dispensasi_massal)
                        <p style="margin-bottom: 8px;">
                            Sehubungan dengan adanya
                            <strong>{{ $spesifik['nama_kegiatan_dispensasi'] ?? 'kegiatan resmi madrasah' }}</strong>
                            yang diadakan oleh MDT Hidayatus Shibyan, yang dilaksanakan pada:
                        </p>

                        <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 10px;">
                            @if (!empty($spesifik['hari_kegiatan_dispensasi']))
                                <tr>
                                    <td style="width: 140px; font-weight: 600;">Hari</td>
                                    <td style="width: 10px;">:</td>
                                    <td style="font-weight: 700;">{{ $spesifik['hari_kegiatan_dispensasi'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="width: 140px; font-weight: 600;">Tanggal</td>
                                <td style="width: 10px;">:</td>
                                <td style="font-weight: 700;">
                                    {{ isset($spesifik['tanggal_mulai_dispensasi']) && $spesifik['tanggal_mulai_dispensasi'] ? \Carbon\Carbon::parse($spesifik['tanggal_mulai_dispensasi'])->translatedFormat('d F Y') : '-' }}
                                    @if (
                                        !empty($spesifik['tanggal_selesai_dispensasi']) &&
                                            $spesifik['tanggal_selesai_dispensasi'] !== $spesifik['tanggal_mulai_dispensasi']
                                    )
                                        s.d.
                                        {{ \Carbon\Carbon::parse($spesifik['tanggal_selesai_dispensasi'])->translatedFormat('d F Y') }}
                                    @endif
                                </td>
                            </tr>
                            @if (!empty($spesifik['waktu_kegiatan_dispensasi']))
                                <tr>
                                    <td style="width: 140px; font-weight: 600;">Waktu</td>
                                    <td>:</td>
                                    <td>{{ $spesifik['waktu_kegiatan_dispensasi'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="font-weight: 600;">Tempat</td>
                                <td>:</td>
                                <td>{{ $spesifik['tempat_kegiatan_dispensasi'] ?? 'MDT Hidayatus Shibyan' }}</td>
                            </tr>
                        </table>

                        <p style="margin-bottom: 8px;">
                            Maka kami selaku Pengasuh MDT Hidayatus Shibyan Desa Somorkoneng Kecamatan Kwanyar Kabupaten
                            Bangkalan mengajukan permohonan dispensasi untuk
                            <strong>{{ $spesifik['permohonan_dispensasi_khusus'] ?? 'dipulangkan pukul 12.00 WIB agar Murid dapat mempersiapkan diri' }}</strong>.
                            Adapun Murid yang mengikuti
                            {{ $spesifik['nama_kegiatan_dispensasi'] ?? 'kegiatan tersebut' }} akan disebut sebagaimana
                            terlampir.
                        </p>
                    @else
                        {{-- DISPENSASI 1 MURID (TUNGGAL) --}}
                        <p style="margin-bottom: 8px;">
                            Yang bertanda tangan di bawah ini Pengasuh MDT Hidayatus Shibyan menerangkan bahwa murid di
                            bawah ini:
                        </p>

                        @if ($surat->murid)
                            <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 8px;">
                                <tr>
                                    <td style="width: 150px; font-weight: 600;">Nama Murid</td>
                                    <td style="width: 10px;">:</td>
                                    <td style="font-weight: 700;">{{ $surat->murid->nama_lengkap }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">NISM</td>
                                    <td>:</td>
                                    <td>{{ $surat->murid->nism ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Kelas / Ruangan</td>
                                    <td>:</td>
                                    <td>{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Nama Orang Tua / Wali</td>
                                    <td>:</td>
                                    <td>{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Dusun / Kampung</td>
                                    <td>:</td>
                                    <td>{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <p style="margin-bottom: 8px;">
                            Diberikan izin dan dispensasi kehadiran karena mengikuti kegiatan
                            <strong>{{ $spesifik['nama_kegiatan_dispensasi'] ?? 'kegiatan resmi madrasah' }}</strong>
                            pada:
                        </p>

                        <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 10px;">
                            <tr>
                                <td style="width: 140px; font-weight: 600;">Periode Dispensasi</td>
                                <td style="width: 10px;">:</td>
                                <td style="font-weight: 700;">
                                    {{ isset($spesifik['tanggal_mulai_dispensasi']) && $spesifik['tanggal_mulai_dispensasi'] ? \Carbon\Carbon::parse($spesifik['tanggal_mulai_dispensasi'])->translatedFormat('d F Y') : '-' }}
                                    @if (
                                        !empty($spesifik['tanggal_selesai_dispensasi']) &&
                                            $spesifik['tanggal_selesai_dispensasi'] !== $spesifik['tanggal_mulai_dispensasi']
                                    )
                                        s/d
                                        {{ \Carbon\Carbon::parse($spesifik['tanggal_selesai_dispensasi'])->translatedFormat('d F Y') }}
                                    @endif
                                    @if (!empty($spesifik['jumlah_hari']))
                                        ({{ $spesifik['jumlah_hari'] }})
                                    @endif
                                </td>
                            </tr>
                            @if (!empty($spesifik['alasan_kegiatan']))
                                <tr>
                                    <td style="font-weight: 600;">Keterangan</td>
                                    <td>:</td>
                                    <td>{{ $spesifik['alasan_kegiatan'] }}</td>
                                </tr>
                            @endif
                        </table>
                    @endif

                    {{-- G. SURAT UNDANGAN --}}
                @elseif ($surat->jenis_surat === 'surat_undangan')
                    <p style="margin-bottom: 8px;">
                        Mengharap dengan hormat kehadiran Bapak/Ibu/Saudara pada agenda
                        <strong>{{ $spesifik['nama_acara'] ?? $surat->perihal }}</strong> yang insya Allah akan
                        dilaksanakan pada:
                    </p>

                    <table class="tabel-identitas" style="margin-left: 15px; margin-bottom: 10px;">
                        <tr>
                            <td style="width: 140px; font-weight: 600;">Hari</td>
                            <td style="width: 10px;">:</td>
                            <td style="font-weight: 700;">{{ $spesifik['hari_acara'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Tanggal</td>
                            <td>:</td>
                            <td style="font-weight: 700;">
                                {{ isset($spesifik['tanggal_acara']) && $spesifik['tanggal_acara'] ? \Carbon\Carbon::parse($spesifik['tanggal_acara'])->translatedFormat('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Waktu / Pukul</td>
                            <td>:</td>
                            <td>{{ $spesifik['waktu_acara'] ?? '19.30 WIB (Ba\'da Isya) s/d Selesai' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Tempat Acara</td>
                            <td>:</td>
                            <td>{{ $spesifik['tempat_acara'] ?? 'Aula MDT Hidayatus Shibyan' }}</td>
                        </tr>
                        @if (!empty($spesifik['agenda_acara']))
                            <tr>
                                <td style="font-weight: 600;">Susunan Agenda</td>
                                <td>:</td>
                                <td>{{ $spesifik['agenda_acara'] }}</td>
                            </tr>
                        @endif
                    </table>
                @endif

                <!-- PARAGRAF TAMBAHAN JIKA DIISI -->
                @if ($surat->isi_surat)
                    <div style="margin: 8px 0; line-height: 1.5;">
                        {!! nl2br(e($surat->isi_surat)) !!}
                    </div>
                @endif

                <!-- PENUTUP SURAT -->
                <p style="margin-top: 10px; margin-bottom: 6px;">
                    Demikian surat ini kami susun. Atas perhatiannya dan kerja samanya kami menyampaikan terima kasih
                    dan mohon dimaklumi adanya.
                </p>
                <p><em>Wassalamualaikum War. Wab.</em></p>
            </div>
        </div>

        <!-- 5. TANDA TANGAN & PENGESAHAN DOKUMEN (FORMAT SK ARSIP DENGAN QR CODE) -->
        @php
            $activeSigners = $surat->penandatangan_aktif;
            $signerCount = count($activeSigners);
            $colWidth = $signerCount > 0 ? round(100 / $signerCount, 2) : 100;
        @endphp

        <div style="margin-top: 10px;">
            <table class="w-full text-center text-[9.5pt]" style="table-layout: fixed; width: 100%;">
                <tr>
                    @foreach ($activeSigners as $signer)
                        <td class="align-top" style="height: 42px; width: {{ $colWidth }}%;">
                            @if (!empty($signer['label_atas']))
                                <p class="mb-0" style="font-size: 8.5pt; color: #334155;">{!! nl2br(e($signer['label_atas'])) !!}
                                </p>
                            @else
                                <p class="mb-0" style="font-size: 8.5pt; color: #334155;">
                                    {{ $signer['key'] === 'pengasuh' ? 'Mengesahkan,' : ($signer['key'] === 'admin' ? $surat->tempat_terbit . ', ' . \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : 'Mengetahui,') }}
                                </p>
                            @endif
                            <p class="font-bold" style="font-size: 9.5pt;">{{ $signer['jabatan'] }}</p>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($activeSigners as $signer)
                        <td class="align-bottom pb-1 pt-1">
                            <div
                                style="display: flex; justify-content: center; align-items: center; margin: 1px auto;">
                                @if (!empty($signer['id_relasi']) && !empty($signer['tipe_relasi']))
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->generate(
                                        URL::signedRoute('profil.publik', ['tipe' => $signer['tipe_relasi'], 'id' => $signer['id_relasi']]),
                                    ) !!}
                                @else
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->generate($surat->qr_token ?: 'MDTHS-VERIFIED') !!}
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($activeSigners as $signer)
                        <td class="align-bottom">
                            <p class="font-bold underline mb-0" style="font-size: 9.5pt;">
                                {{ $signer['nama'] ?? '-' }}</p>
                        </td>
                    @endforeach
                </tr>
            </table>

            <!-- TEMBUSAN & VERIFIKASI FOOTER -->
            @if ($surat->tembusan)
                <div style="margin-top: 8px; font-size: 8.5pt; color: #334155;">
                    <span style="font-weight: 700; text-decoration: underline;">Tembusan:</span>
                    <div style="padding-left: 5px; margin-top: 1px;">
                        {!! nl2br(e($surat->tembusan)) !!}
                    </div>
                </div>
            @endif

            <!-- KETERANGAN PENGESAHAN ELEKTRONIK (FORMAT SEPERTI REFERENSI) -->
            <div
                style="margin-top: 10px; padding-top: 6px; border-top: 1px solid #cbd5e1; display: flex; align-items: center; gap: 8px;">
                <div
                    style="flex-shrink: 0; background: #fff; padding: 2px; border: 1px solid #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                    @php
                        $primary = !empty($activeSigners) ? $activeSigners[0] : null;
                    @endphp
                    @if (!empty($primary['id_relasi']) && !empty($primary['tipe_relasi']))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => $primary['tipe_relasi'], 'id' => $primary['id_relasi']])) !!}
                    @else
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->margin(0)->generate($surat->qr_token ?: url('/')) !!}
                    @endif
                </div>
                <div style="font-size: 7pt; color: #1e293b; line-height: 1.25; text-align: justify; flex: 1;">
                    Dokumen ini ditandatangani secara elektronik oleh Pejabat Berwenang MDT Hidayatus Shibyan dan
                    distempel digital resmi oleh Sistem Administrasi Persuratan MDTHS. Untuk verifikasi keabsahan,
                    kunjungi <span
                        style="text-decoration: underline; color: #059669; font-weight: 600;">{{ url('/') }}</span>
                    dan masukkan nomor surat, atau scan QRCode di samping.
                </div>
            </div>
            <div style="text-align: center; font-size: 7pt; color: #64748b; margin-top: 4px;">
                1 dari {{ $surat->total_halaman }}
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- HALAMAN 2: LEMBAR LAMPIRAN RESMI           --}}
    {{-- ========================================== --}}
    @if ($surat->has_lampiran)
        <div class="page-sheet">
            <div>
                <!-- KOP IDENTITAS LAMPIRAN STANDAR (POJOK KANAN ATAS) -->
                <div
                    style="display: flex; justify-content: flex-end; margin-bottom: 10px; font-size: 9pt; line-height: 1.3;">
                    <table style="width: auto; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: 600; padding-right: 8px; width: 80px;">Lampiran</td>
                            <td style="padding-right: 6px;">:</td>
                            <td style="font-weight: 700;">{{ $surat->lampiran_judul }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding-right: 8px;">Nomor</td>
                            <td style="padding-right: 6px;">:</td>
                            <td style="font-family: monospace;">{{ $surat->nomor_surat }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding-right: 8px;">Tanggal</td>
                            <td style="padding-right: 6px;">:</td>
                            <td>
                                {{ $surat->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : '-' }}
                                @if ($surat->tanggal_hijriyah)
                                    / {{ $surat->tanggal_hijriyah }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding-right: 8px;">Tentang</td>
                            <td style="padding-right: 6px;">:</td>
                            <td style="font-weight: 600;">{{ $surat->perihal }}</td>
                        </tr>
                    </table>
                </div>

                <div style="border-bottom: 2px solid #000; margin-bottom: 15px;"></div>

                <!-- JUDUL LAMPIRAN (CENTER, BOLD, UNDERLINE) -->
                <div style="text-align: center; margin-bottom: 18px;">
                    <h3
                        style="font-size: 11pt; font-weight: 800; text-decoration: underline; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">
                        {{ $surat->lampiran_judul }}
                    </h3>
                </div>

                <!-- ISI KONTEN LAMPIRAN -->
                @if ($surat->is_dispensasi_massal)
                    <!-- TABEL DAFTAR NAMA MURID & KELAS LEMBAGA TUJUAN -->
                    <table
                        style="width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 9.5pt; line-height: 1.35;">
                        <thead>
                            <tr style="background-color: #f8fafc;">
                                <th
                                    style="border: 1px solid #000; padding: 5px 8px; width: 45px; text-align: center; font-weight: 700;">
                                    No.</th>
                                <th
                                    style="border: 1px solid #000; padding: 5px 10px; text-align: left; font-weight: 700;">
                                    Nama Murid</th>
                                <th
                                    style="border: 1px solid #000; padding: 5px 10px; width: 160px; text-align: center; font-weight: 700;">
                                    Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($surat->murid_dispensasi_list as $idx => $m)
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">
                                        {{ $idx + 1 }}</td>
                                    <td
                                        style="border: 1px solid #000; padding: 4px 10px; font-weight: 700; text-transform: uppercase;">
                                        {{ $m['nama_lengkap'] ?? '-' }}</td>
                                    <td
                                        style="border: 1px solid #000; padding: 4px 10px; text-align: center; font-weight: 600;">
                                        {{ $m['kelas_lembaga'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <!-- KONTEN LAMPIRAN TEKS STANDAR -->
                    <div
                        style="font-size: 10pt; line-height: 1.5; text-align: justify; white-space: pre-line; margin-bottom: 20px; font-family: 'Plus Jakarta Sans', Times, serif;">
                        {!! nl2br(e($surat->lampiran_konten)) !!}
                    </div>
                @endif
            </div>

            <!-- BAGIAN BAWAH: TANDA TANGAN & FOOTER HALAMAN 2 -->
            <div>
                <!-- TANDA TANGAN PENGESAHAN LAMPIRAN -->
                <table class="w-full text-center" style="table-layout: fixed; margin-top: 10px; font-size: 9pt;">
                    <tr>
                        @foreach ($activeSigners as $signer)
                            <td class="align-top" style="padding-bottom: 4px;">
                                <p class="mb-0 text-slate-500" style="font-size: 8pt;">
                                    {{ $signer['label_atas'] ?? ($signer['key'] === 'pengasuh' ? 'Mengesahkan,' : 'Mengetahui,') }}
                                </p>
                                <p class="font-bold mb-0" style="font-size: 9pt;">{{ $signer['jabatan'] }}</p>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($activeSigners as $signer)
                            <td class="align-middle py-1">
                                <div
                                    style="display: flex; justify-content: center; align-items: center; margin: 1px auto;">
                                    @if (!empty($signer['id_relasi']) && !empty($signer['tipe_relasi']))
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->generate(
                                            URL::signedRoute('profil.publik', ['tipe' => $signer['tipe_relasi'], 'id' => $signer['id_relasi']]),
                                        ) !!}
                                    @else
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->generate($surat->qr_token ?: 'MDTHS-VERIFIED') !!}
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($activeSigners as $signer)
                            <td class="align-bottom">
                                <p class="font-bold underline mb-0" style="font-size: 9.5pt;">
                                    {{ $signer['nama'] ?? '-' }}</p>
                            </td>
                        @endforeach
                    </tr>
                </table>

                <!-- KETERANGAN PENGESAHAN ELEKTRONIK HALAMAN 2 -->
                <div
                    style="margin-top: 10px; padding-top: 6px; border-top: 1px solid #cbd5e1; display: flex; align-items: center; gap: 8px;">
                    <div
                        style="flex-shrink: 0; background: #fff; padding: 2px; border: 1px solid #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                        @if (!empty($primary['id_relasi']) && !empty($primary['tipe_relasi']))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(40)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => $primary['tipe_relasi'], 'id' => $primary['id_relasi']])) !!}
                        @else
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(40)->margin(0)->generate($surat->qr_token ?: url('/')) !!}
                        @endif
                    </div>
                    <div style="font-size: 7pt; color: #1e293b; line-height: 1.25; text-align: justify; flex: 1;">
                        Dokumen ini ditandatangani secara elektronik oleh Pejabat Berwenang MDT Hidayatus Shibyan dan
                        distempel digital resmi oleh Sistem Administrasi Persuratan MDTHS. Untuk verifikasi keabsahan,
                        kunjungi <span
                            style="text-decoration: underline; color: #059669; font-weight: 600;">{{ url('/') }}</span>
                        dan masukkan nomor surat, atau scan QRCode di samping.
                    </div>
                </div>
                <div style="text-align: center; font-size: 7pt; color: #64748b; margin-top: 4px;">
                    2 dari 2
                </div>
            </div>
        </div>
    @endif

</body>

</html>
